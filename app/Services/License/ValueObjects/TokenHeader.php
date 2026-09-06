<?php

namespace App\Services\License\ValueObjects;

use App\Services\License\Exceptions\InvalidHeaderException;

final readonly class TokenHeader
{
    public function __construct(
        public string $typ,
        public string $alg,
        public string $kid,
    ) {
        if ($this->typ !== 'CLS-LIC-V1') {
            throw new InvalidHeaderException("Unsupported token type [{$this->typ}]. Expected [CLS-LIC-V1].");
        }

        if ($this->alg !== 'Ed25519') {
            throw new InvalidHeaderException("Unsupported algorithm [{$this->alg}]. Expected [Ed25519].");
        }

        if (trim($this->kid) === '') {
            throw new InvalidHeaderException('Token key ID (kid) cannot be empty.');
        }
    }

    /**
     * Create a validated TokenHeader from decoded raw array.
     *
     * @param array<string, mixed> $data
     * @throws InvalidHeaderException
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['typ']) || !is_string($data['typ'])) {
            throw new InvalidHeaderException('Missing or invalid [typ] in token header.');
        }

        if (!isset($data['alg']) || !is_string($data['alg'])) {
            throw new InvalidHeaderException('Missing or invalid [alg] in token header.');
        }

        if (!isset($data['kid']) || !is_string($data['kid'])) {
            throw new InvalidHeaderException('Missing or invalid [kid] in token header.');
        }

        return new self(
            typ: $data['typ'],
            alg: $data['alg'],
            kid: $data['kid']
        );
    }
}
