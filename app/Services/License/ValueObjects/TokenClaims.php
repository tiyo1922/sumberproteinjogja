<?php

namespace App\Services\License\ValueObjects;

use App\Services\License\Exceptions\InvalidClaimException;

final readonly class TokenClaims
{
    /**
     * @param array<string, mixed> $rawClaims
     */
    public function __construct(
        public string $jti,
        public string $iss,
        public string $aud,
        public string $sub,
        public string $dom,
        public int $iat,
        public int $nbf,
        public int $exp,
        public ?int $licExp,
        public string|array|null $customer,
        public array $rawClaims = [],
    ) {
        if (trim($this->jti) === '') {
            throw new InvalidClaimException('Claim [jti] must be a non-empty string.');
        }

        if (trim($this->iss) === '') {
            throw new InvalidClaimException('Claim [iss] must be a non-empty string.');
        }

        if (trim($this->aud) === '') {
            throw new InvalidClaimException('Claim [aud] must be a non-empty string.');
        }

        if (trim($this->sub) === '') {
            throw new InvalidClaimException('Claim [sub] must be a non-empty string.');
        }

        if (trim($this->dom) === '') {
            throw new InvalidClaimException('Claim [dom] must be a non-empty string.');
        }

        if ($this->iat <= 0 || $this->nbf <= 0 || $this->exp <= 0) {
            throw new InvalidClaimException('Claims [iat], [nbf], and [exp] must be positive integer timestamps.');
        }

        if ($this->exp < $this->nbf) {
            throw new InvalidClaimException('Claim [exp] cannot be earlier than [nbf].');
        }

        if ($this->licExp !== null && $this->licExp < 0) {
            throw new InvalidClaimException('Claim [lic_exp] must be a non-negative timestamp if present.');
        }

        if ($this->licExp !== null && $this->exp > $this->licExp) {
            throw new InvalidClaimException('Claim [exp] cannot exceed authoritative license expiration [lic_exp].');
        }
    }

    /**
     * Build and validate TokenClaims from raw decoded payload.
     *
     * @param array<string, mixed> $data
     * @throws InvalidClaimException
     */
    public static function fromArray(array $data): self
    {
        $requiredStringClaims = ['jti', 'iss', 'aud', 'sub', 'dom'];
        foreach ($requiredStringClaims as $claim) {
            if (!isset($data[$claim]) || !is_string($data[$claim])) {
                throw new InvalidClaimException("Missing or invalid string claim [{$claim}].");
            }
        }

        $requiredIntClaims = ['iat', 'nbf', 'exp'];
        foreach ($requiredIntClaims as $claim) {
            if (!isset($data[$claim]) || !is_int($data[$claim])) {
                throw new InvalidClaimException("Missing or invalid integer timestamp claim [{$claim}].");
            }
        }

        $licExp = null;
        if (array_key_exists('lic_exp', $data) && $data['lic_exp'] !== null) {
            if (!is_int($data['lic_exp'])) {
                throw new InvalidClaimException('Claim [lic_exp] must be an integer timestamp or null.');
            }
            $licExp = $data['lic_exp'];
        }

        $customer = null;
        if (isset($data['customer'])) {
            if (is_string($data['customer'])) {
                $customer = trim($data['customer']);
            } elseif (is_array($data['customer'])) {
                $customer = $data['customer'];
            }
        }

        return new self(
            jti: $data['jti'],
            iss: $data['iss'],
            aud: $data['aud'],
            sub: $data['sub'],
            dom: $data['dom'],
            iat: $data['iat'],
            nbf: $data['nbf'],
            exp: $data['exp'],
            licExp: $licExp,
            customer: $customer,
            rawClaims: $data
        );
    }

    /**
     * Check if token is expired at given reference timestamp.
     */
    public function isExpired(int $referenceTime): bool
    {
        if ($referenceTime >= $this->exp) {
            return true;
        }

        if ($this->licExp !== null && $referenceTime >= $this->licExp) {
            return true;
        }

        return false;
    }

    /**
     * Check if token is not yet valid at given reference timestamp.
     */
    public function isNotYetValid(int $referenceTime): bool
    {
        return $referenceTime < $this->nbf;
    }

    /**
     * Convert claims back to associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'jti' => $this->jti,
            'iss' => $this->iss,
            'aud' => $this->aud,
            'sub' => $this->sub,
            'dom' => $this->dom,
            'iat' => $this->iat,
            'nbf' => $this->nbf,
            'exp' => $this->exp,
            'lic_exp' => $this->licExp,
            'customer' => $this->customer,
        ];
    }
}