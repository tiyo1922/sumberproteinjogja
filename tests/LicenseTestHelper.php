<?php

namespace Tests;

use App\Services\License\Ed25519TokenVerifier;
use App\Services\License\LicenseClientService;

trait LicenseTestHelper
{
    protected string $testKeypair;
    protected string $testSecretKey;
    protected string $testPublicKey;
    protected string $testPublicKeyBase64;
    protected string $testKeyId = 'cls-ed25519-2026-v1';
    protected string $testIssuer = 'https://license.katresnanku.com';
    protected string $testAudience = 'SPJ22';

    protected function initLicenseKeys(): void
    {
        $this->testKeypair = sodium_crypto_sign_keypair();
        $this->testSecretKey = sodium_crypto_sign_secretkey($this->testKeypair);
        $this->testPublicKey = sodium_crypto_sign_publickey($this->testKeypair);
        $this->testPublicKeyBase64 = base64_encode($this->testPublicKey);

        config([
            'app.url' => 'http://localhost',
            'license.server_url' => $this->testIssuer,
            'license.app_code' => $this->testAudience,
            'license.api_key_id' => 'test_key_id_123',
            'license.api_secret' => 'test_api_secret_xyz',
            'license.server_public_key' => $this->testPublicKeyBase64,
            'license.server_key_id' => $this->testKeyId,
            'license.timeout' => 5,
            'license.retry' => 0,
        ]);

        $this->app->bind(Ed25519TokenVerifier::class, function () {
            return new Ed25519TokenVerifier(
                trustedPublicKeyBase64: $this->testPublicKeyBase64,
                trustedKeyId: $this->testKeyId,
                expectedIssuer: $this->testIssuer,
                expectedAudience: $this->testAudience
            );
        });

        $this->app->singleton(\App\Services\License\LicenseStateService::class, function () {
            return new \App\Services\License\LicenseStateService();
        });

        $this->app->bind(LicenseClientService::class, function ($app) {
            return new LicenseClientService(
                verifier: $app->make(Ed25519TokenVerifier::class),
                stateService: $app->make(\App\Services\License\LicenseStateService::class),
                serverUrl: $this->testIssuer,
                apiKeyId: 'test_key_id_123',
                apiSecret: 'test_api_secret_xyz',
                timeout: 5,
                retry: 0
            );
        });
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function createSignedToken(
        array $claimsOverride = [],
        array $headerOverride = [],
        ?string $signSecretKey = null
    ): string {
        $now = time();

        $header = array_merge([
            'alg' => 'Ed25519',
            'typ' => 'CLS-LIC-V1',
            'kid' => $this->testKeyId,
        ], $headerOverride);

        $payload = array_merge([
            'jti' => 'tok_' . bin2hex(random_bytes(16)),
            'iss' => $this->testIssuer,
            'aud' => $this->testAudience,
            'sub' => 'SPJ22-****-****-****',
            'dom' => 'localhost',
            'iat' => $now - 60,
            'nbf' => $now - 60,
            'exp' => $now + 604800,
            'lic_exp' => $now + 31536000,
            'customer' => [
                'name' => 'Test Customer',
                'email' => 'test@example.com',
            ],
        ], $claimsOverride);

        $encodedHeader = $this->base64UrlEncode((string) json_encode($header, JSON_UNESCAPED_SLASHES));
        $encodedPayload = $this->base64UrlEncode((string) json_encode($payload, JSON_UNESCAPED_SLASHES));
        $message = "{$encodedHeader}.{$encodedPayload}";

        $sk = $signSecretKey ?? $this->testSecretKey;
        $signature = sodium_crypto_sign_detached($message, $sk);
        $encodedSignature = $this->base64UrlEncode($signature);

        return "{$encodedHeader}.{$encodedPayload}.{$encodedSignature}";
    }
}