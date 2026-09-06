<?php

namespace Tests\Feature;

use App\Services\License\LicenseStateService;
use App\Services\License\ValueObjects\TokenClaims;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\LicenseTestHelper;
use Tests\TestCase;

class LicenseGateLandingPageTest extends TestCase
{
    use RefreshDatabase, LicenseTestHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initLicenseKeys();
    }

    public function test_unactivated_landing_page_renders_branded_unactivated_view(): void
    {
        $response = $this->get('/');

        $response->assertStatus(503);
        $response->assertSee('WEBSITE BELUM DIAKTIVASI');
        $response->assertSee('Sumber Protein Jogja');
        $response->assertSee('Konfigurasi Sistem');
    }

    public function test_unactivated_view_has_no_activation_cta_or_form(): void
    {
        $response = $this->get('/');

        $response->assertStatus(503);
        $response->assertDontSee('/activate');
        $response->assertDontSee('Aktivasi Lisensi');
        $response->assertDontSee('Kode Lisensi');
        $response->assertDontSee('name="license_code"', false);
        $response->assertDontSee('license.katresnanku.com');
        $response->assertDontSee('test_api_secret_xyz');
    }

    public function test_unactivated_storefront_subpages_render_unactivated_view(): void
    {
        $routes = ['/produk', '/knowledge', '/tentang-kami', '/kontak'];
        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(503);
            $response->assertSee('WEBSITE BELUM DIAKTIVASI');
        }
    }

    public function test_activated_valid_renders_normal_landing_page(): void
    {
        $validToken = $this->createSignedToken(['dom' => 'localhost']);
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_valid_lp',
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
        $response->assertDontSee('WEBSITE BELUM DIAKTIVASI');
    }

    public function test_unactivated_json_request_returns_json_error(): void
    {
        $response = $this->getJson('/');
        $response->assertStatus(503);
        $response->assertJson([
            'success' => false,
            'error' => [
                'code' => 'LICENSE_UNACTIVATED',
                'message' => 'Website belum diaktivasi.',
            ],
        ]);
    }
}