<?php

namespace Tests\Unit;

use App\Services\License\Contracts\ClockInterface;
use App\Services\License\Ed25519TokenVerifier;
use App\Services\License\Exceptions\AudienceMismatchException;
use App\Services\License\Exceptions\DomainMismatchException;
use App\Services\License\Exceptions\InvalidClaimException;
use App\Services\License\Exceptions\InvalidHeaderException;
use App\Services\License\Exceptions\InvalidSignatureException;
use App\Services\License\Exceptions\IssuerMismatchException;
use App\Services\License\Exceptions\LicenseExpiredException;
use App\Services\License\Exceptions\MalformedTokenException;
use App\Services\License\Exceptions\TokenExpiredException;
use App\Services\License\Exceptions\TokenNotYetValidException;
use App\Services\License\Exceptions\UnknownKeyIdException;
use PHPUnit\Framework\TestCase;

class Ed25519TokenVerifierTest extends TestCase
{
    private string $keypair;
    private string $secretKey;
    private string $publicKey;
    private string $publicKeyBase64;
    private string $keyId = 'cls-ed25519-2026-v1';
    private string $issuer = 'https://license.katresnanku.com';
    private string $audience = 'SPJ22';
    private int $fixedNow = 1757160000;

    protected function setUp(): void
    {
        parent::setUp();
        // Generate a deterministic testing keypair (strictly local fixture)
        $this->keypair = sodium_crypto_sign_keypair();
        $this->secretKey = sodium_crypto_sign_secretkey($this->keypair);
        $this->publicKey = sodium_crypto_sign_publickey($this->keypair);
        $this->publicKeyBase64 = base64_encode($this->publicKey);
    }

    private function createVerifier(?ClockInterface $clock = null, ?string $pubKey = null, ?string $kid = null): Ed25519TokenVerifier
    {
        $mockClock = $clock ?? new class($this->fixedNow) implements ClockInterface {
            public function __construct(private int $now) {}
            public function now(): int { return $this->now; }
        };

        return new Ed25519TokenVerifier(
            trustedPublicKeyBase64: $pubKey ?? $this->publicKeyBase64,
            trustedKeyId: $kid ?? $this->keyId,
            expectedIssuer: $this->issuer,
            expectedAudience: $this->audience,
            clock: $mockClock
        );
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Helper to forge test CLS-LIC-V1 tokens using test keypair.
     */
    private function createToken(array $headerOverrides = [], array $payloadOverrides = [], ?string $signingSecretKey = null): string
    {
        $header = array_merge([
            'typ' => 'CLS-LIC-V1',
            'alg' => 'Ed25519',
            'kid' => $this->keyId,
        ], $headerOverrides);

        $payload = array_merge([
            'jti' => 'tok_fixture12345678901234',
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'sub' => 'SPJ22-****-****-****-****-9ABC',
            'dom' => 'sumberproteinjogja.com',
            'iat' => $this->fixedNow - 60,
            'nbf' => $this->fixedNow - 60,
            'exp' => $this->fixedNow + 604800, // 7 days TTL
            'lic_exp' => null,
            'customer' => 'Sumber Protein Jogja - Test Client',
        ], $payloadOverrides);

        $encodedHeader = $this->base64UrlEncode((string) json_encode($header, JSON_UNESCAPED_SLASHES));
        $encodedPayload = $this->base64UrlEncode((string) json_encode($payload, JSON_UNESCAPED_SLASHES));
        $message = "{$encodedHeader}.{$encodedPayload}";

        $sk = $signingSecretKey ?? $this->secretKey;
        $signature = sodium_crypto_sign_detached($message, $sk);
        $encodedSignature = $this->base64UrlEncode($signature);

        return "{$encodedHeader}.{$encodedPayload}.{$encodedSignature}";
    }

    /**
     * 1. Valid token passes verification seamlessly and returns typed claims.
     */
    public function test_valid_cls_lic_v1_token_verifies_successfully(): void
    {
        $verifier = $this->createVerifier();
        $token = $this->createToken();

        $claims = $verifier->verify($token, 'sumberproteinjogja.com', $this->fixedNow);

        $this->assertSame('tok_fixture12345678901234', $claims->jti);
        $this->assertSame($this->issuer, $claims->iss);
        $this->assertSame('SPJ22', $claims->aud);
        $this->assertSame('sumberproteinjogja.com', $claims->dom);
        $this->assertSame('SPJ22-****-****-****-****-9ABC', $claims->sub);
        $this->assertSame('Sumber Protein Jogja - Test Client', $claims->customer);
        $this->assertFalse($claims->isExpired($this->fixedNow));
        $this->assertFalse($claims->isNotYetValid($this->fixedNow));
    }

    /**
     * 2. Rejects malformed segment count.
     */
    public function test_rejects_invalid_segment_counts(): void
    {
        $verifier = $this->createVerifier();

        $this->expectException(MalformedTokenException::class);
        $verifier->verify('segment1.segment2', 'sumberproteinjogja.com');
    }

    public function test_rejects_four_segments(): void
    {
        $verifier = $this->createVerifier();

        $this->expectException(MalformedTokenException::class);
        $verifier->verify('seg1.seg2.seg3.seg4', 'sumberproteinjogja.com');
    }

    public function test_rejects_empty_segments(): void
    {
        $verifier = $this->createVerifier();

        $this->expectException(MalformedTokenException::class);
        $verifier->verify('..', 'sumberproteinjogja.com');
    }

    /**
     * 3. Rejects non-Base64URL characters.
     */
    public function test_rejects_invalid_base64url_characters(): void
    {
        $verifier = $this->createVerifier();

        $this->expectException(MalformedTokenException::class);
        $verifier->verify('header+with+plus.payload/with/slash.signature==', 'sumberproteinjogja.com');
    }

    /**
     * 4. Header validation: typ, alg, kid.
     */
    public function test_rejects_non_cls_lic_v1_type(): void
    {
        $verifier = $this->createVerifier();
        $token = $this->createToken(['typ' => 'JWT']); // Must reject generic JWT

        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage('Unsupported token type [JWT]. Expected [CLS-LIC-V1].');
        $verifier->verify($token, 'sumberproteinjogja.com', $this->fixedNow);
    }

    public function test_rejects_eddsa_and_non_ed25519_algorithms(): void
    {
        $verifier = $this->createVerifier();

        // Must reject EdDSA explicitly as requested in locked contract
        $tokenEdDSA = $this->createToken(['alg' => 'EdDSA']);
        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage('Unsupported algorithm [EdDSA]. Expected [Ed25519].');
        $verifier->verify($tokenEdDSA, 'sumberproteinjogja.com', $this->fixedNow);
    }

    public function test_rejects_unknown_key_id(): void
    {
        $verifier = $this->createVerifier();
        $token = $this->createToken(['kid' => 'untrusted-key-id-99']);

        $this->expectException(UnknownKeyIdException::class);
        $verifier->verify($token, 'sumberproteinjogja.com', $this->fixedNow);
    }

    /**
     * 5. Cryptographic signature verification.
     */
    public function test_rejects_tampered_payload(): void
    {
        $verifier = $this->createVerifier();
        $token = $this->createToken();

        [$hdr, $pld, $sig] = explode('.', $token);
        // Tamper payload
        $tamperedPld = $this->base64UrlEncode((string) json_encode(['aud' => 'HACKED'], JSON_UNESCAPED_SLASHES));
        $tamperedToken = "{$hdr}.{$tamperedPld}.{$sig}";

        $this->expectException(InvalidSignatureException::class);
        $verifier->verify($tamperedToken, 'sumberproteinjogja.com', $this->fixedNow);
    }

    public function test_rejects_wrong_key_signature(): void
    {
        $verifier = $this->createVerifier();

        // Sign with another arbitrary keypair
        $otherKeypair = sodium_crypto_sign_keypair();
        $otherSecretKey = sodium_crypto_sign_secretkey($otherKeypair);
        $tokenSignedByOtherKey = $this->createToken([], [], $otherSecretKey);

        $this->expectException(InvalidSignatureException::class);
        $verifier->verify($tokenSignedByOtherKey, 'sumberproteinjogja.com', $this->fixedNow);
    }

    /**
     * 6. Claim mismatches.
     */
    public function test_rejects_issuer_mismatch(): void
    {
        $verifier = $this->createVerifier();
        $token = $this->createToken([], ['iss' => 'https://rogue-license-server.local']);

        $this->expectException(IssuerMismatchException::class);
        $verifier->verify($token, 'sumberproteinjogja.com', $this->fixedNow);
    }

    public function test_rejects_audience_mismatch(): void
    {
        $verifier = $this->createVerifier();
        $token = $this->createToken([], ['aud' => 'OTHERAPP']);

        $this->expectException(AudienceMismatchException::class);
        $verifier->verify($token, 'sumberproteinjogja.com', $this->fixedNow);
    }

    public function test_rejects_domain_mismatch(): void
    {
        $verifier = $this->createVerifier();
        $token = $this->createToken([], ['dom' => 'unauthorized-mirror.com']);

        $this->expectException(DomainMismatchException::class);
        $verifier->verify($token, 'sumberproteinjogja.com', $this->fixedNow);
    }

    /**
     * 7. Temporal claim validations (nbf, exp, lic_exp) - Zero Grace Period.
     */
    public function test_rejects_future_nbf_token(): void
    {
        $verifier = $this->createVerifier();
        $token = $this->createToken([], [
            'nbf' => $this->fixedNow + 3600, // Valid 1 hour in future
        ]);

        $this->expectException(TokenNotYetValidException::class);
        $verifier->verify($token, 'sumberproteinjogja.com', $this->fixedNow);
    }

    public function test_rejects_expired_token_without_grace_period(): void
    {
        $verifier = $this->createVerifier();
        $token = $this->createToken([], [
            'exp' => $this->fixedNow, // exp == current_time is expired
        ]);

        $this->expectException(TokenExpiredException::class);
        $verifier->verify($token, 'sumberproteinjogja.com', $this->fixedNow);
    }

    public function test_rejects_authoritative_license_expiration(): void
    {
        $verifier = $this->createVerifier();
        $token = $this->createToken([], [
            'exp' => $this->fixedNow - 10,
            'lic_exp' => $this->fixedNow - 10, // License already expired authoritatively
        ]);

        $this->expectException(LicenseExpiredException::class);
        $verifier->verify($token, 'sumberproteinjogja.com', $this->fixedNow);
    }

    public function test_rejects_token_exp_exceeding_lic_exp_ceiling(): void
    {
        $verifier = $this->createVerifier();

        $token = $this->createToken([], [
            'exp' => $this->fixedNow + 604800, // 7 days
            'lic_exp' => $this->fixedNow + 86400, // License expires in 1 day
        ]);

        $this->expectException(InvalidClaimException::class);
        $this->expectExceptionMessage('Claim [exp] cannot exceed authoritative license expiration [lic_exp].');
        $verifier->verify($token, 'sumberproteinjogja.com', $this->fixedNow);
    }

    /**
     * 8. Missing required claims & type validations.
     */
    public function test_rejects_missing_required_claims(): void
    {
        $verifier = $this->createVerifier();

        $requiredFields = ['jti', 'iss', 'aud', 'sub', 'dom', 'iat', 'nbf', 'exp'];
        foreach ($requiredFields as $field) {
            $payload = [
                'jti' => 'tok_1', 'iss' => $this->issuer, 'aud' => $this->audience,
                'sub' => 'SPJ22-****', 'dom' => 'sumberproteinjogja.com',
                'iat' => $this->fixedNow - 10, 'nbf' => $this->fixedNow - 10, 'exp' => $this->fixedNow + 1000
            ];
            unset($payload[$field]);

            $encodedHeader = $this->base64UrlEncode((string) json_encode(['typ' => 'CLS-LIC-V1', 'alg' => 'Ed25519', 'kid' => $this->keyId]));
            $encodedPayload = $this->base64UrlEncode((string) json_encode($payload));
            $sig = $this->base64UrlEncode(sodium_crypto_sign_detached("{$encodedHeader}.{$encodedPayload}", $this->secretKey));
            $token = "{$encodedHeader}.{$encodedPayload}.{$sig}";

            try {
                $verifier->verify($token, 'sumberproteinjogja.com', $this->fixedNow);
                $this->fail("Expected InvalidClaimException for missing field [{$field}]");
            } catch (InvalidClaimException $e) {
                $this->assertNotEmpty($e->getMessage());
            }
        }
    }

    /**
     * Test rejection of Base64URL string whose length mod 4 is 1 (mathematically invalid base64).
     */
    public function test_rejects_base64url_length_mod_4_equals_1(): void
    {
        $this->expectException(MalformedTokenException::class);
        $this->expectExceptionMessage('mod 4 == 1');

        $verifier = $this->createVerifier();
        // A single character segment has length 1 (1 % 4 === 1)
        $verifier->verify("a.b.c", 'localhost');
    }

    /**
     * Test trusted public keys registry with multiple key IDs (KID rotation support).
     */
    public function test_supports_trusted_keys_registry_with_kid_rotation(): void
    {
        // Generate second keypair for rotation
        $keypair2 = sodium_crypto_sign_keypair();
        $secretKey2 = sodium_crypto_sign_secretkey($keypair2);
        $publicKey2Base64 = base64_encode(sodium_crypto_sign_publickey($keypair2));

        $registry = [
            'cls-ed25519-2026-v1' => $this->publicKeyBase64,
            'cls-ed25519-2026-v2' => $publicKey2Base64,
        ];

        $verifier = new Ed25519TokenVerifier(
            trustedPublicKeys: $registry,
            expectedIssuer: $this->issuer,
            expectedAudience: $this->audience
        );

        // Token signed with v1 key
        $tokenV1 = $this->createToken(['kid' => 'cls-ed25519-2026-v1'], ['dom' => 'localhost']);
        $claimsV1 = $verifier->verify($tokenV1, 'localhost', $this->fixedNow);
        $this->assertSame('localhost', $claimsV1->dom);

        // Token signed with v2 key
        $tokenV2 = $this->createToken(['kid' => 'cls-ed25519-2026-v2'], ['dom' => 'localhost'], $secretKey2);
        $claimsV2 = $verifier->verify($tokenV2, 'localhost', $this->fixedNow);
        $this->assertSame('localhost', $claimsV2->dom);

        // Token signed with unknown kid
        $this->expectException(UnknownKeyIdException::class);
        $tokenV3 = $this->createToken(['kid' => 'unknown-v3'], ['dom' => 'localhost']);
        $verifier->verify($tokenV3, 'localhost', $this->fixedNow);
    }
}