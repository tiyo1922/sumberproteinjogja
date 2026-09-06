<?php

namespace Tests\Feature;

use App\Services\License\LicenseStateService;
use App\Services\License\ValueObjects\TokenClaims;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\LicenseTestHelper;
use Tests\TestCase;

class LicenseActivationFlowTest extends TestCase
{
    use RefreshDatabase, LicenseTestHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initLicenseKeys();
        RateLimiter::clear('license-activate:127.0.0.1');
    }

    public function test_get_activate_renders_activation_screen(): void
    {
        $response = $this->get('/activate');

        $response->assertStatus(200);
        $response->assertSee('Aktivasi Sistem');
        $response->assertSee('Kode Lisensi');
        $response->assertSee('Aktivasi Lisensi');
        $response->assertSee('Domain:');
        $response->assertDontSee('LICENSE_API_SECRET');
        $response->assertDontSee('test_api_secret_xyz');
    }

    public function test_get_activate_when_already_activated_and_valid_redirects_to_login(): void
    {
        $validToken = $this->createSignedToken(['dom' => 'localhost']);
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_test',
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

        app(LicenseStateService::class)->saveActivationState($claims, $validToken);

        $response = $this->get('/activate');
        $response->assertRedirect('/login');
    }

    public function test_post_activate_validation_fails_on_empty_or_invalid_code(): void
    {
        $response = $this->post('/activate', [
            'license_code' => '',
        ]);

        $response->assertSessionHasErrors('license_code');
    }

    public function test_post_activate_successful_activation_redirects_to_login(): void
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
                    'token' => $validToken,
                ],
            ], 200),
        ]);

        $response = $this->post('/activate', [
            'license_code' => 'SPJ22-ABCD-1234-EFGH',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success', 'Aktivasi lisensi berhasil! Silakan masuk ke panel admin.');

        $this->assertTrue(app(LicenseStateService::class)->isActivated());
    }

    public function test_post_activate_failed_activation_returns_safe_error(): void
    {
        Http::fake([
            'https://license.katresnanku.com/api/v1/license/activate' => Http::response([
                'success' => false,
                'error' => [
                    'code' => 'LICENSE_NOT_FOUND',
                    'message' => 'License not found.',
                ],
            ], 422),
        ]);

        $response = $this->from('/activate')->post('/activate', [
            'license_code' => 'SPJ22-INVALID-KEY-123',
        ]);

        $response->assertRedirect('/activate');
        $response->assertSessionHasErrors(['license_code' => 'Kode lisensi tidak valid atau tidak ditemukan.']);
        $this->assertFalse(app(LicenseStateService::class)->isActivated());
    }

    public function test_post_activate_rate_limiter_blocks_abuse(): void
    {
        Http::fake([
            'https://license.katresnanku.com/api/v1/license/activate' => Http::response([
                'success' => false,
                'error' => ['code' => 'INVALID_LICENSE', 'message' => 'Invalid'],
            ], 422),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/activate', ['license_code' => "SPJ22-WRONG-KEY-{$i}"]);
        }

        // 6th attempt should be blocked by rate limiter
        $response = $this->post('/activate', ['license_code' => 'SPJ22-WRONG-KEY-6']);
        $response->assertSessionHasErrors('license_code');
        $error = session('errors')->first('license_code');
        $this->assertStringContainsString('Terlalu banyak percobaan aktivasi', $error);
    }
}