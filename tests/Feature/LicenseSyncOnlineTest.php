<?php

namespace Tests\Feature;

use App\Services\License\Ed25519TokenVerifier;
use App\Services\License\Exceptions\LicenseNetworkException;
use App\Services\License\Exceptions\LicenseVerificationException;
use App\Services\License\LicenseClientService;
use App\Services\License\LicenseStateService;
use App\Services\License\ValueObjects\TokenClaims;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\LicenseTestHelper;
use Tests\TestCase;

class LicenseSyncOnlineTest extends TestCase
{
    use RefreshDatabase, LicenseTestHelper;

    private LicenseClientService $clientService;
    private LicenseStateService $stateService;
    private Ed25519TokenVerifier $verifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initLicenseKeys();

        $this->verifier = new Ed25519TokenVerifier(
            trustedPublicKeyBase64: $this->testPublicKeyBase64,
            trustedKeyId: $this->testKeyId,
            expectedIssuer: $this->testIssuer,
            expectedAudience: $this->testAudience
        );

        $this->stateService = new LicenseStateService();

        $this->clientService = new LicenseClientService(
            verifier: $this->verifier,
            stateService: $this->stateService,
            serverUrl: $this->testIssuer,
            apiKeyId: 'test_key_id_123',
            apiSecret: 'test_api_secret_xyz',
            timeout: 5,
            retry: 0
        );
    }

    public function test_successful_online_verification_without_refresh(): void
    {
        $token = $this->createSignedToken(['dom' => 'localhost']);
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_orig_123',
            'iss' => $this->testIssuer,
            'aud' => $this->testAudience,
            'sub' => 'SPJ22-****-****-****',
            'dom' => 'localhost',
            'iat' => time() - 60,
            'nbf' => time() - 60,
            'exp' => time() + 604800,
            'lic_exp' => time() + 31536000,
            'customer' => null,
        ]);

        $this->stateService->saveActivationState($claims, $token, '2026-09-01T00:00:00Z');

        Http::fake([
            'https://license.katresnanku.com/api/v1/license/verify' => Http::response([
                'success' => true,
                'data' => [
                    'valid' => true,
                    'status' => 'ACTIVE',
                    'canonical_domain' => 'localhost',
                    'token' => $token,
                    'token_id' => 'tok_orig_123',
                    'token_expires_at' => '2026-09-13T00:00:00Z',
                    'refreshed' => false,
                    'server_time' => '2026-09-06T12:00:00Z',
                ],
            ], 200),
        ]);

        $result = $this->clientService->verifyOnline();

        $this->assertTrue($result['success']);
        $this->assertSame('ACTIVE', $result['status']);
        $this->assertFalse($result['refreshed']);
        $this->assertSame('tok_orig_123', $result['token_id']);

        $state = $this->stateService->getState();
        $this->assertSame($token, $state['token']);
        $this->assertSame('tok_orig_123', $state['jti']);
    }

    public function test_successful_online_verification_with_rolling_refresh(): void
    {
        $oldToken = $this->createSignedToken(['dom' => 'localhost', 'jti' => 'tok_old_gen1']);
        $oldClaims = TokenClaims::fromArray([
            'jti' => 'tok_old_gen1',
            'iss' => $this->testIssuer,
            'aud' => $this->testAudience,
            'sub' => 'SPJ22-****-****-****',
            'dom' => 'localhost',
            'iat' => time() - 500000,
            'nbf' => time() - 500000,
            'exp' => time() + 104800,
            'lic_exp' => time() + 31536000,
            'customer' => null,
        ]);

        $this->stateService->saveActivationState($oldClaims, $oldToken, '2026-09-01T00:00:00Z');

        // New refreshed token generation
        $newToken = $this->createSignedToken([
            'dom' => 'localhost',
            'jti' => 'tok_new_gen2',
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + 604800,
        ]);

        Http::fake([
            'https://license.katresnanku.com/api/v1/license/verify' => Http::response([
                'success' => true,
                'data' => [
                    'valid' => true,
                    'status' => 'ACTIVE',
                    'canonical_domain' => 'localhost',
                    'token' => $newToken,
                    'token_id' => 'tok_new_gen2',
                    'token_expires_at' => gmdate('Y-m-d\TH:i:s\Z', time() + 604800),
                    'refreshed' => true,
                    'server_time' => gmdate('Y-m-d\TH:i:s\Z'),
                ],
            ], 200),
        ]);

        $result = $this->clientService->verifyOnline();

        $this->assertTrue($result['success']);
        $this->assertTrue($result['refreshed']);
        $this->assertSame('tok_new_gen2', $result['token_id']);

        $state = $this->stateService->getState();
        $this->assertSame($newToken, $state['token']);
        $this->assertSame('tok_new_gen2', $state['jti']);
        $this->assertSame(time() + 604800, $state['token_expires_at']);
    }

    public function test_refreshed_token_with_invalid_signature_fails_and_does_not_mutate_state(): void
    {
        $oldToken = $this->createSignedToken(['dom' => 'localhost', 'jti' => 'tok_valid_old']);
        $oldClaims = TokenClaims::fromArray([
            'jti' => 'tok_valid_old',
            'iss' => $this->testIssuer,
            'aud' => $this->testAudience,
            'sub' => 'SPJ22-****-****-****',
            'dom' => 'localhost',
            'iat' => time() - 60,
            'nbf' => time() - 60,
            'exp' => time() + 604800,
            'lic_exp' => time() + 31536000,
            'customer' => null,
        ]);

        $this->stateService->saveActivationState($oldClaims, $oldToken);

        // Create forged token with untrusted key
        $rogueKeypair = sodium_crypto_sign_keypair();
        $rogueSecretKey = sodium_crypto_sign_secretkey($rogueKeypair);
        $forgedToken = $this->createSignedToken(
            claimsOverride: ['dom' => 'localhost', 'jti' => 'tok_forged'],
            signSecretKey: $rogueSecretKey
        );

        Http::fake([
            'https://license.katresnanku.com/api/v1/license/verify' => Http::response([
                'success' => true,
                'data' => [
                    'valid' => true,
                    'status' => 'ACTIVE',
                    'token' => $forgedToken,
                    'token_id' => 'tok_forged',
                    'refreshed' => true,
                ],
            ], 200),
        ]);

        try {
            $this->clientService->verifyOnline();
            $this->fail('Expected crypto verification failure');
        } catch (LicenseVerificationException $e) {
            $this->assertSame('CRYPTO_VERIFICATION_FAILED', $e->getErrorCode());

            // State must retain OLD valid token
            $state = $this->stateService->getState();
            $this->assertSame($oldToken, $state['token']);
            $this->assertSame('tok_valid_old', $state['jti']);
        }
    }

    public function test_central_suspended_response_marks_state_suspended(): void
    {
        $token = $this->createSignedToken(['dom' => 'localhost']);
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_sus',
            'iss' => $this->testIssuer,
            'aud' => $this->testAudience,
            'sub' => 'SPJ22-****-****-****',
            'dom' => 'localhost',
            'iat' => time() - 60,
            'nbf' => time() - 60,
            'exp' => time() + 604800,
            'lic_exp' => time() + 31536000,
            'customer' => null,
        ]);

        $this->stateService->saveActivationState($claims, $token);

        Http::fake([
            'https://license.katresnanku.com/api/v1/license/verify' => Http::response([
                'success' => false,
                'error' => [
                    'code' => 'LICENSE_SUSPENDED',
                    'message' => 'This license is currently suspended.',
                ],
            ], 403),
        ]);

        try {
            $this->clientService->verifyOnline();
            $this->fail('Expected suspension exception');
        } catch (LicenseVerificationException $e) {
            $this->assertSame(403, $e->getHttpStatus());
            $this->assertSame('LICENSE_SUSPENDED', $e->getErrorCode());
            $this->assertTrue($this->stateService->isSuspended());
        }
    }

    public function test_central_revoked_response_marks_state_revoked(): void
    {
        $token = $this->createSignedToken(['dom' => 'localhost']);
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_rev',
            'iss' => $this->testIssuer,
            'aud' => $this->testAudience,
            'sub' => 'SPJ22-****-****-****',
            'dom' => 'localhost',
            'iat' => time() - 60,
            'nbf' => time() - 60,
            'exp' => time() + 604800,
            'lic_exp' => time() + 31536000,
            'customer' => null,
        ]);

        $this->stateService->saveActivationState($claims, $token);

        Http::fake([
            'https://license.katresnanku.com/api/v1/license/verify' => Http::response([
                'success' => false,
                'error' => [
                    'code' => 'LICENSE_REVOKED',
                    'message' => 'This license has been revoked.',
                ],
            ], 403),
        ]);

        try {
            $this->clientService->verifyOnline();
            $this->fail('Expected revocation exception');
        } catch (LicenseVerificationException $e) {
            $this->assertSame(403, $e->getHttpStatus());
            $this->assertSame('LICENSE_REVOKED', $e->getErrorCode());
            $this->assertTrue($this->stateService->isRevoked());
        }
    }

    public function test_central_expired_response_marks_state_expired(): void
    {
        $token = $this->createSignedToken(['dom' => 'localhost']);
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_exp',
            'iss' => $this->testIssuer,
            'aud' => $this->testAudience,
            'sub' => 'SPJ22-****-****-****',
            'dom' => 'localhost',
            'iat' => time() - 60,
            'nbf' => time() - 60,
            'exp' => time() + 604800,
            'lic_exp' => time() + 31536000,
            'customer' => null,
        ]);

        $this->stateService->saveActivationState($claims, $token);

        Http::fake([
            'https://license.katresnanku.com/api/v1/license/verify' => Http::response([
                'success' => false,
                'error' => [
                    'code' => 'LICENSE_EXPIRED',
                    'message' => 'This license has expired.',
                ],
            ], 403),
        ]);

        try {
            $this->clientService->verifyOnline();
            $this->fail('Expected expiration exception');
        } catch (LicenseVerificationException $e) {
            $this->assertSame(403, $e->getHttpStatus());
            $this->assertSame('LICENSE_EXPIRED', $e->getErrorCode());
            $this->assertTrue($this->stateService->isExpired());
        }
    }

    public function test_network_failure_preserves_local_state(): void
    {
        $token = $this->createSignedToken(['dom' => 'localhost', 'jti' => 'tok_preserve_me']);
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_preserve_me',
            'iss' => $this->testIssuer,
            'aud' => $this->testAudience,
            'sub' => 'SPJ22-****-****-****',
            'dom' => 'localhost',
            'iat' => time() - 60,
            'nbf' => time() - 60,
            'exp' => time() + 604800,
            'lic_exp' => time() + 31536000,
            'customer' => null,
        ]);

        $this->stateService->saveActivationState($claims, $token);

        Http::fake([
            'https://license.katresnanku.com/api/v1/license/verify' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Server unreachable');
            },
        ]);

        try {
            $this->clientService->verifyOnline();
            $this->fail('Expected network exception');
        } catch (LicenseNetworkException $e) {
            $this->assertSame(503, $e->getCode());

            // State must REMAIN ACTIVE and intact
            $this->assertTrue($this->stateService->isActive());
            $state = $this->stateService->getState();
            $this->assertSame($token, $state['token']);
            $this->assertSame('tok_preserve_me', $state['jti']);
        }
    }

    public function test_unactivated_installation_throws_exception(): void
    {
        $this->expectException(LicenseVerificationException::class);
        $this->expectExceptionMessage('Lisensi belum diaktivasi pada instalasi ini.');

        $this->clientService->verifyOnline();
    }
}