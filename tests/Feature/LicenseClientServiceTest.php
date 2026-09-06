<?php

namespace Tests\Feature;

use App\Services\License\Ed25519TokenVerifier;
use App\Services\License\Exceptions\LicenseActivationException;
use App\Services\License\LicenseClientService;
use App\Services\License\LicenseStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\LicenseTestHelper;
use Tests\TestCase;

class LicenseClientServiceTest extends TestCase
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

    public function test_successful_activation_response(): void
    {
        $validToken = $this->createSignedToken([
            'dom' => 'localhost',
            'sub' => 'SPJ22-ABCD-1234-EFGH',
        ]);

        Http::fake([
            'https://license.katresnanku.com/api/v1/license/activate' => Http::response([
                'success' => true,
                'data' => [
                    'activated' => true,
                    'license_key_masked' => 'SPJ22-ABCD-1234-EFGH',
                    'canonical_domain' => 'localhost',
                    'status' => 'ACTIVE',
                    'token' => $validToken,
                    'token_id' => 'tok_123',
                    'token_expires_at' => '2026-09-13T00:00:00Z',
                ],
            ], 200),
        ]);

        $result = $this->clientService->activate('SPJ22-ABCD-1234-EFGH', 'localhost:8000');

        $this->assertTrue($result['success']);
        $this->assertSame('ACTIVE', $result['status']);
        $this->assertSame('localhost', $result['domain']);
        $this->assertSame('SPJ22-ABCD-1234-EFGH', $result['key_masked']);
        $this->assertTrue($this->stateService->isActivated());

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Api-Key-Id', 'test_key_id_123')
                && $request->hasHeader('X-Api-Secret', 'test_api_secret_xyz')
                && $request['license_key'] === 'SPJ22-ABCD-1234-EFGH'
                && $request['domain'] === 'localhost';
        });
    }

    public function test_empty_license_key_throws_exception_without_network_call(): void
    {
        Http::fake();

        $this->expectException(LicenseActivationException::class);
        $this->expectExceptionMessage('Kode lisensi tidak boleh kosong.');

        $this->clientService->activate('', 'localhost');
        Http::assertNothingSent();
    }

    public function test_central_401_unauthorized_throws_safe_exception(): void
    {
        Http::fake([
            'https://license.katresnanku.com/api/v1/license/activate' => Http::response([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_API_KEY',
                    'message' => 'The provided API Key ID does not exist.',
                ],
            ], 401),
        ]);

        try {
            $this->clientService->activate('SPJ22-TEST-KEY', 'localhost');
            $this->fail('Expected LicenseActivationException was not thrown.');
        } catch (LicenseActivationException $e) {
            $this->assertSame(401, $e->getHttpStatus());
            $this->assertSame('Autentikasi integrasi lisensi gagal. Periksa konfigurasi API server.', $e->getMessage());
            $this->assertFalse($this->stateService->isActivated());
        }
    }

    public function test_central_409_domain_mismatch_throws_safe_exception(): void
    {
        Http::fake([
            'https://license.katresnanku.com/api/v1/license/activate' => Http::response([
                'success' => false,
                'error' => [
                    'code' => 'LICENSE_ALREADY_BOUND',
                    'message' => 'This license is already bound to another domain.',
                ],
            ], 409),
        ]);

        try {
            $this->clientService->activate('SPJ22-TEST-KEY', 'localhost');
            $this->fail('Expected LicenseActivationException was not thrown.');
        } catch (LicenseActivationException $e) {
            $this->assertSame(409, $e->getHttpStatus());
            $this->assertSame('Lisensi ini sudah terikat pada domain lain.', $e->getMessage());
            $this->assertFalse($this->stateService->isActivated());
        }
    }

    public function test_central_422_status_exceptions(): void
    {
        $cases = [
            'LICENSE_NOT_FOUND' => 'Kode lisensi tidak valid atau tidak ditemukan.',
            'LICENSE_SUSPENDED' => 'Lisensi ini sedang ditangguhkan. Silakan hubungi administrator.',
            'LICENSE_REVOKED' => 'Lisensi ini telah dicabut permanen.',
            'LICENSE_EXPIRED' => 'Masa berlaku lisensi ini telah berakhir.',
        ];

        $currentCode = null;
        Http::fake([
            'https://license.katresnanku.com/api/v1/license/activate' => function () use (&$currentCode) {
                return Http::response([
                    'success' => false,
                    'error' => [
                        'code' => $currentCode,
                        'message' => 'Server raw message',
                    ],
                ], 422);
            },
        ]);

        foreach ($cases as $code => $expectedMessage) {
            $currentCode = $code;
            try {
                $this->clientService->activate('SPJ22-TEST-KEY', 'localhost');
                $this->fail("Expected exception for {$code}");
            } catch (LicenseActivationException $e) {
                $this->assertSame($expectedMessage, $e->getMessage());
                $this->assertSame($code, $e->getErrorCode());
            }
        }
    }

    public function test_central_429_rate_limited_throws_safe_exception(): void
    {
        Http::fake([
            'https://license.katresnanku.com/api/v1/license/activate' => Http::response([
                'success' => false,
                'error' => [
                    'code' => 'RATE_LIMITED',
                    'message' => 'Too many requests.',
                ],
            ], 429),
        ]);

        try {
            $this->clientService->activate('SPJ22-TEST-KEY', 'localhost');
            $this->fail('Expected rate limit exception');
        } catch (LicenseActivationException $e) {
            $this->assertSame(429, $e->getHttpStatus());
            $this->assertSame('Terlalu banyak percobaan aktivasi. Silakan tunggu beberapa saat sebelum mencoba kembali.', $e->getMessage());
        }
    }

    public function test_central_500_server_error_throws_safe_exception(): void
    {
        Http::fake([
            'https://license.katresnanku.com/api/v1/license/activate' => Http::response('Internal Server Error', 500),
        ]);

        try {
            $this->clientService->activate('SPJ22-TEST-KEY', 'localhost');
            $this->fail('Expected server error exception');
        } catch (LicenseActivationException $e) {
            $this->assertSame(500, $e->getHttpStatus());
            $this->assertSame('Central License Server sedang mengalami gangguan. Silakan coba kembali sesaat lagi.', $e->getMessage());
        }
    }

    public function test_network_timeout_throws_safe_exception(): void
    {
        Http::fake([
            'https://license.katresnanku.com/api/v1/license/activate' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
            },
        ]);

        try {
            $this->clientService->activate('SPJ22-TEST-KEY', 'localhost');
            $this->fail('Expected network exception');
        } catch (LicenseActivationException $e) {
            $this->assertSame(503, $e->getHttpStatus());
            $this->assertSame('NETWORK_ERROR', $e->getErrorCode());
            $this->assertStringContainsString('Gagal terhubung ke Central License Server', $e->getMessage());
        }
    }

    public function test_malformed_response_payload_throws_exception(): void
    {
        Http::fake([
            'https://license.katresnanku.com/api/v1/license/activate' => Http::response([
                'success' => true,
                'data' => [], // Missing token
            ], 200),
        ]);

        try {
            $this->clientService->activate('SPJ22-TEST-KEY', 'localhost');
            $this->fail('Expected malformed response exception');
        } catch (LicenseActivationException $e) {
            $this->assertSame(502, $e->getHttpStatus());
            $this->assertSame('MALFORMED_SERVER_RESPONSE', $e->getErrorCode());
            $this->assertFalse($this->stateService->isActivated());
        }
    }

    public function test_tampered_token_from_server_fails_crypto_and_does_not_persist(): void
    {
        // Sign token with a different secret key
        $rogueKeypair = sodium_crypto_sign_keypair();
        $rogueSecretKey = sodium_crypto_sign_secretkey($rogueKeypair);

        $forgedToken = $this->createSignedToken(
            claimsOverride: ['dom' => 'localhost'],
            signSecretKey: $rogueSecretKey
        );

        Http::fake([
            'https://license.katresnanku.com/api/v1/license/activate' => Http::response([
                'success' => true,
                'data' => [
                    'activated' => true,
                    'token' => $forgedToken,
                ],
            ], 200),
        ]);

        try {
            $this->clientService->activate('SPJ22-TEST-KEY', 'localhost');
            $this->fail('Expected crypto verification failure');
        } catch (LicenseActivationException $e) {
            $this->assertSame('CRYPTO_VERIFICATION_FAILED', $e->getErrorCode());
            $this->assertFalse($this->stateService->isActivated());
        }
    }
}