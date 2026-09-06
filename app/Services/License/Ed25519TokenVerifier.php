<?php

namespace App\Services\License;

use App\Services\License\Contracts\ClockInterface;
use App\Services\License\Exceptions\AudienceMismatchException;
use App\Services\License\Exceptions\DomainMismatchException;
use App\Services\License\Exceptions\InvalidHeaderException;
use App\Services\License\Exceptions\InvalidSignatureException;
use App\Services\License\Exceptions\IssuerMismatchException;
use App\Services\License\Exceptions\LicenseExpiredException;
use App\Services\License\Exceptions\MalformedTokenException;
use App\Services\License\Exceptions\TokenExpiredException;
use App\Services\License\Exceptions\TokenNotYetValidException;
use App\Services\License\Exceptions\UnknownKeyIdException;
use App\Services\License\ValueObjects\TokenClaims;
use App\Services\License\ValueObjects\TokenHeader;

class Ed25519TokenVerifier
{
    private ClockInterface $clock;
    private string $expectedIssuer;
    private string $expectedAudience;
    /** @var array<string, string> */
    private array $trustedPublicKeys = [];

    /**
     * @param array<string, string>|string|null $trustedPublicKeys Array of [kid => base64PublicKey] or single base64 string
     * @param string|null $trustedKeyId Single key ID if single public key passed
     * @param string|null $expectedIssuer Expected token issuer
     * @param string|null $expectedAudience Expected token audience (app_code)
     * @param ClockInterface|null $clock Clock implementation
     */
    public function __construct(
        array|string|null $trustedPublicKeys = null,
        ?string $trustedKeyId = null,
        ?string $expectedIssuer = null,
        ?string $expectedAudience = null,
        ?ClockInterface $clock = null,
        ?string $trustedPublicKeyBase64 = null
    ) {
        $keys = $trustedPublicKeys ?? $trustedPublicKeyBase64;
        if (is_array($keys)) {
            $this->trustedPublicKeys = $keys;
        } elseif (is_string($keys) && $keys !== '') {
            $kid = $trustedKeyId ?? (string) config('license.server_key_id', 'cls-ed25519-2026-v1');
            $this->trustedPublicKeys[$kid] = $keys;
        } else {
            // Load from config
            $configuredKeys = config('license.trusted_public_keys');
            if (is_array($configuredKeys) && !empty($configuredKeys)) {
                $this->trustedPublicKeys = $configuredKeys;
            } else {
                $singleKey = (string) config('license.server_public_key', '');
                $singleKid = (string) config('license.server_key_id', 'cls-ed25519-2026-v1');
                if ($singleKey !== '') {
                    $this->trustedPublicKeys[$singleKid] = $singleKey;
                }
            }
        }

        $this->expectedIssuer = $expectedIssuer ?? (string) config('license.server_url', 'https://license.katresnanku.com');
        $this->expectedAudience = $expectedAudience ?? (string) config('license.app_code', 'SPJ22');
        $this->clock = $clock ?? new SystemClock();
    }

    /**
     * Parse and verify a CLS-LIC-V1 compact signed token against expected domain.
     *
     * @param string $tokenString Compact 3-part base64url token
     * @param string $expectedDomain Raw or canonical expected client domain
     * @param int|null $referenceTimestamp Optional explicit timestamp for deterministic testing
     * @return TokenClaims
     *
     * @throws MalformedTokenException
     * @throws InvalidHeaderException
     * @throws UnknownKeyIdException
     * @throws InvalidSignatureException
     * @throws IssuerMismatchException
     * @throws AudienceMismatchException
     * @throws DomainMismatchException
     * @throws TokenNotYetValidException
     * @throws TokenExpiredException
     * @throws LicenseExpiredException
     */
    public function verify(string $tokenString, string $expectedDomain, ?int $referenceTimestamp = null): TokenClaims
    {
        $canonicalDomain = DomainCanonicalizer::canonicalize($expectedDomain);

        // 1. Token must contain exactly 3 segments separated by dots
        $segments = explode('.', trim($tokenString));
        if (count($segments) !== 3) {
            throw new MalformedTokenException('Token must contain exactly three Base64URL-encoded segments.');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $segments;

        if ($encodedHeader === '' || $encodedPayload === '' || $encodedSignature === '') {
            throw new MalformedTokenException('Token segments cannot be empty.');
        }

        // 2. Decode Header
        $headerBytes = $this->base64UrlDecode($encodedHeader);
        $headerData = json_decode($headerBytes, true);
        if (!is_array($headerData) || json_last_error() !== JSON_ERROR_NONE) {
            throw new MalformedTokenException('Token header is not valid JSON.');
        }

        $header = TokenHeader::fromArray($headerData);

        // 3. Resolve Trusted Public Key by Key ID (kid)
        if (!array_key_exists($header->kid, $this->trustedPublicKeys)) {
            throw new UnknownKeyIdException("Token key ID [{$header->kid}] is not in the trusted public keys registry.");
        }

        $trustedPublicKeyBase64 = $this->trustedPublicKeys[$header->kid];

        // 4. Verify Ed25519 Cryptographic Signature FIRST before parsing payload claims
        $signatureBytes = $this->base64UrlDecode($encodedSignature);
        if (strlen($signatureBytes) !== SODIUM_CRYPTO_SIGN_BYTES) {
            throw new InvalidSignatureException('Token signature length is invalid for Ed25519.');
        }

        $publicKeyBytes = base64_decode($trustedPublicKeyBase64, true);
        if ($publicKeyBytes === false || strlen($publicKeyBytes) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            throw new InvalidSignatureException('Trusted server public key is missing or invalid 32-byte Ed25519 key.');
        }

        $signedMessage = "{$encodedHeader}.{$encodedPayload}";

        $isValidSignature = sodium_crypto_sign_verify_detached($signatureBytes, $signedMessage, $publicKeyBytes);
        if (!$isValidSignature) {
            throw new InvalidSignatureException('Ed25519 cryptographic signature verification failed.');
        }

        // 5. Decode Payload (now cryptographically authenticated)
        $payloadBytes = $this->base64UrlDecode($encodedPayload);
        $payloadData = json_decode($payloadBytes, true);
        if (!is_array($payloadData) || json_last_error() !== JSON_ERROR_NONE) {
            throw new MalformedTokenException('Token payload is not valid JSON.');
        }

        $claims = TokenClaims::fromArray($payloadData);

        // 6. Validate Issuer
        if ($claims->iss !== $this->expectedIssuer) {
            throw new IssuerMismatchException("Token issuer [{$claims->iss}] does not match expected issuer [{$this->expectedIssuer}].");
        }

        // 7. Validate Audience
        if ($claims->aud !== $this->expectedAudience) {
            throw new AudienceMismatchException("Token audience [{$claims->aud}] does not match application code [{$this->expectedAudience}].");
        }

        // 8. Validate Domain Binding
        $tokenDomain = DomainCanonicalizer::canonicalize($claims->dom);
        if ($tokenDomain !== $canonicalDomain) {
            throw new DomainMismatchException("Token domain [{$tokenDomain}] does not match current host domain [{$canonicalDomain}].");
        }

        // 9. Validate Temporal Claims
        $now = $referenceTimestamp ?? $this->clock->now();

        if ($claims->isNotYetValid($now)) {
            throw new TokenNotYetValidException("Token is not valid until timestamp [{$claims->nbf}] (current time: [{$now}]).");
        }

        if ($claims->isExpired($now)) {
            if ($claims->licExp !== null && $now >= $claims->licExp) {
                throw new LicenseExpiredException("Authoritative license has expired at timestamp [{$claims->licExp}] (current time: [{$now}]).");
            }

            throw new TokenExpiredException("Token has expired at timestamp [{$claims->exp}] (current time: [{$now}]).");
        }

        return $claims;
    }

    /**
     * Strict Base64URL decoder.
     *
     * @param string $input
     * @return string
     * @throws MalformedTokenException
     */
    private function base64UrlDecode(string $input): string
    {
        if (preg_match('/[^A-Za-z0-9\-_]/', $input)) {
            throw new MalformedTokenException('Invalid characters in Base64URL string.');
        }

        $len = strlen($input);
        if ($len % 4 === 1) {
            throw new MalformedTokenException('Invalid Base64URL string length (mod 4 == 1).');
        }

        $remainder = $len % 4;
        if ($remainder !== 0) {
            $input .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($input, '-_', '+/'), true);
        if ($decoded === false) {
            throw new MalformedTokenException('Base64URL decode failed.');
        }

        return $decoded;
    }
}