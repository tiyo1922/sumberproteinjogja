<?php

namespace Tests\Feature;

use App\Services\License\Ed25519TokenVerifier;
use App\Services\License\Exceptions\UnknownKeyIdException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\LicenseTestHelper;
use Tests\TestCase;

class LicenseConfigAndPreflightTest extends TestCase
{
    use RefreshDatabase, LicenseTestHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initLicenseKeys();
    }

    public function test_license_config_defaults_to_production_kid(): void
    {
        $defaultKid = config('license.server_key_id');
        $this->assertSame('cls-ed25519-2026-v1', $defaultKid);

        $appCode = config('license.app_code');
        $this->assertSame('SPJ22', $appCode);
    }

    public function test_ed25519_verifier_verifies_with_production_kid_cls_ed25519_2026_v1(): void
    {
        $verifier = new Ed25519TokenVerifier(
            trustedPublicKeys: [
                'cls-ed25519-2026-v1' => $this->testPublicKeyBase64,
            ],
            expectedIssuer: $this->testIssuer,
            expectedAudience: $this->testAudience
        );

        $token = $this->createSignedToken([
            'dom' => 'localhost',
        ], [
            'kid' => 'cls-ed25519-2026-v1',
        ]);

        $claims = $verifier->verify($token, 'localhost', time());
        $this->assertSame('SPJ22', $claims->aud);
        $this->assertSame('localhost', $claims->dom);
    }

    public function test_ed25519_verifier_multi_kid_rotation_support(): void
    {
        $keypair2 = sodium_crypto_sign_keypair();
        $secretKey2 = sodium_crypto_sign_secretkey($keypair2);
        $pubKey2Base64 = base64_encode(sodium_crypto_sign_publickey($keypair2));

        $verifier = new Ed25519TokenVerifier(
            trustedPublicKeys: [
                'cls-ed25519-2026-v1' => $this->testPublicKeyBase64,
                'cls-ed25519-2026-v2' => $pubKey2Base64,
            ],
            expectedIssuer: $this->testIssuer,
            expectedAudience: $this->testAudience
        );

        // V1 Token
        $tokenV1 = $this->createSignedToken(['dom' => 'localhost'], ['kid' => 'cls-ed25519-2026-v1']);
        $claimsV1 = $verifier->verify($tokenV1, 'localhost', time());
        $this->assertSame('localhost', $claimsV1->dom);

        // V2 Token (signed with keypair2)
        $tokenV2 = $this->createSignedToken(['dom' => 'localhost'], ['kid' => 'cls-ed25519-2026-v2'], $secretKey2);
        $claimsV2 = $verifier->verify($tokenV2, 'localhost', time());
        $this->assertSame('localhost', $claimsV2->dom);

        // Unknown kid throws
        $this->expectException(UnknownKeyIdException::class);
        $tokenUnknown = $this->createSignedToken(['dom' => 'localhost'], ['kid' => 'cls-unknown-v99']);
        $verifier->verify($tokenUnknown, 'localhost', time());
    }

    public function test_preflight_artisan_command_executes_locally_without_network(): void
    {
        config([
            'license.server_url' => 'https://license.katresnanku.com',
            'license.app_code' => 'SPJ22',
            'license.api_key_id' => 'ak_test_preflight_key_12345',
            'license.api_secret' => 'sec_test_secret_sample_never_expose_12345',
            'license.trusted_public_keys' => [
                'cls-ed25519-2026-v1' => $this->testPublicKeyBase64,
            ],
        ]);

        $this->artisan('license:check')
            ->expectsOutputToContain('CENTRAL LICENSE CLIENT (SPJ22) - LOCAL PREFLIGHT DIAGNOSTICS')
            ->expectsOutputToContain('PHP Version')
            ->expectsOutputToContain('Sodium Extension (Ed25519)')
            ->expectsOutputToContain('SQLite3 & PDO Driver')
            ->expectsOutputToContain('License Server URL')
            ->expectsOutputToContain('Application Code')
            ->assertExitCode(0);
    }

    public function test_preflight_command_never_prints_api_secret(): void
    {
        $secretValue = 'sec_super_secret_string_xyz_998877';
        config([
            'license.server_url' => 'https://license.katresnanku.com',
            'license.app_code' => 'SPJ22',
            'license.api_key_id' => 'ak_sample_id_123',
            'license.api_secret' => $secretValue,
            'license.trusted_public_keys' => [
                'cls-ed25519-2026-v1' => $this->testPublicKeyBase64,
            ],
        ]);

        $this->artisan('license:check')
            ->doesntExpectOutputToContain($secretValue)
            ->expectsOutputToContain('Present (Hidden)')
            ->assertExitCode(0);
    }
}
