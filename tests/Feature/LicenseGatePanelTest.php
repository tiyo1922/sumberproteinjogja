<?php

namespace Tests\Feature;

use App\Services\License\LicenseStateService;
use App\Services\License\ValueObjects\TokenClaims;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\LicenseTestHelper;
use Tests\TestCase;

class LicenseGatePanelTest extends TestCase
{
    use RefreshDatabase, LicenseTestHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initLicenseKeys();
    }

    public function test_unactivated_login_redirects_to_activate(): void
    {
        $response = $this->get('/login');
        $response->assertRedirect('/activate');
    }

    public function test_unactivated_admin_dashboard_redirects_to_activate(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/activate');
    }

    public function test_unactivated_admin_subroutes_redirect_to_activate(): void
    {
        $routes = ['/admin/kategori', '/admin/hero', '/admin/settings', '/admin/seo'];
        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/activate');
        }
    }

    public function test_unactivated_password_reset_routes_redirect_to_activate(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertRedirect('/activate');
    }

    public function test_activated_valid_allows_login_screen(): void
    {
        $validToken = $this->createSignedToken(['dom' => 'localhost']);
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_valid',
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

        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Masuk');
    }

    public function test_expired_token_cannot_bypass_panel_gate(): void
    {
        $expiredToken = $this->createSignedToken([
            'dom' => 'localhost',
            'exp' => time() - 3600, // Expired 1 hour ago
        ]);

        $claims = TokenClaims::fromArray([
            'jti' => 'tok_exp',
            'iss' => $this->testIssuer,
            'aud' => $this->testAudience,
            'sub' => 'SPJ22-****-****-****',
            'dom' => 'localhost',
            'iat' => time() - 7200,
            'nbf' => time() - 7200,
            'exp' => time() - 3600,
            'lic_exp' => time() + 31536000,
            'customer' => null,
        ]);

        app(LicenseStateService::class)->saveActivationState($claims, $expiredToken);

        $response = $this->get('/login');
        $response->assertRedirect('/activate');
    }

    public function test_expired_license_cannot_bypass_panel_gate(): void
    {
        $expiredToken = $this->createSignedToken([
            'dom' => 'localhost',
            'iat' => time() - 7200,
            'nbf' => time() - 7200,
            'exp' => time() - 3600,
            'lic_exp' => time() - 3600, // Authoritative license expired
        ]);

        $claims = TokenClaims::fromArray([
            'jti' => 'tok_lic_exp',
            'iss' => $this->testIssuer,
            'aud' => $this->testAudience,
            'sub' => 'SPJ22-****-****-****',
            'dom' => 'localhost',
            'iat' => time() - 7200,
            'nbf' => time() - 7200,
            'exp' => time() - 3600,
            'lic_exp' => time() - 3600,
            'customer' => null,
        ]);

        app(LicenseStateService::class)->saveActivationState($claims, $expiredToken);

        $response = $this->get('/login');
        $response->assertRedirect('/activate');

        // Verify local state was marked EXPIRED
        $state = app(LicenseStateService::class)->getState();
        $this->assertSame('EXPIRED', $state['status']);
    }

    public function test_domain_mismatch_cannot_bypass_panel_gate(): void
    {
        $wrongDomainToken = $this->createSignedToken([
            'dom' => 'otherdomain.com',
        ]);

        $claims = TokenClaims::fromArray([
            'jti' => 'tok_wrong_dom',
            'iss' => $this->testIssuer,
            'aud' => $this->testAudience,
            'sub' => 'SPJ22-****-****-****',
            'dom' => 'otherdomain.com',
            'iat' => time() - 60,
            'nbf' => time() - 60,
            'exp' => time() + 604800,
            'lic_exp' => time() + 31536000,
            'customer' => null,
        ]);

        app(LicenseStateService::class)->saveActivationState($claims, $wrongDomainToken);

        $response = $this->get('/login');
        $response->assertRedirect('/activate');
    }
}