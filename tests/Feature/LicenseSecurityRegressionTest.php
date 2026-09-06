<?php

namespace Tests\Feature;

use App\Services\License\LicenseStateService;
use App\Services\License\ValueObjects\TokenClaims;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\LicenseTestHelper;
use Tests\TestCase;

class LicenseSecurityRegressionTest extends TestCase
{
    use RefreshDatabase, LicenseTestHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initLicenseKeys();
    }

    public function test_api_secret_never_leaked_in_html(): void
    {
        $pages = ['/', '/activate', '/produk', '/login'];
        foreach ($pages as $page) {
            $response = $this->get($page);
            $content = $response->getContent();
            $this->assertStringNotContainsString('test_api_secret_xyz', $content);
            $this->assertStringNotContainsString('LICENSE_API_SECRET', $content);
        }
    }

    public function test_api_secret_never_stored_in_session(): void
    {
        $validToken = $this->createSignedToken(['dom' => 'localhost']);
        Http::fake([
            'https://license.katresnanku.com/api/v1/license/activate' => Http::response([
                'success' => true,
                'data' => ['activated' => true, 'token' => $validToken],
            ], 200),
        ]);

        $this->post('/activate', ['license_code' => 'SPJ22-SECRET-TEST-KEY']);

        $sessionData = session()->all();
        $sessionString = json_encode($sessionData);

        $this->assertStringNotContainsString('test_api_secret_xyz', $sessionString);
        $this->assertStringNotContainsString('SPJ22-SECRET-TEST-KEY', $sessionString);
    }

    public function test_token_not_exposed_in_public_html(): void
    {
        $validToken = $this->createSignedToken(['dom' => 'localhost']);
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_sec_check',
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

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee($validToken);
    }

    public function test_remote_error_traces_never_leaked_to_user(): void
    {
        Http::fake([
            'https://license.katresnanku.com/api/v1/license/activate' => Http::response([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'SQLSTATE[HY000]: General error: database disk is full at /var/www/internal.php:123',
                ],
            ], 500),
        ]);

        $response = $this->from('/activate')->post('/activate', [
            'license_code' => 'SPJ22-TEST-LEAK-KEY',
        ]);

        $response->assertRedirect('/activate');
        $response->assertSessionHasErrors('license_code');
        $error = session('errors')->first('license_code');

        $this->assertStringNotContainsString('SQLSTATE', $error);
        $this->assertStringNotContainsString('/var/www', $error);
        $this->assertSame('Central License Server sedang mengalami gangguan. Silakan coba kembali sesaat lagi.', $error);
    }
}