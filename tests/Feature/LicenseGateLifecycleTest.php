<?php

namespace Tests\Feature;

use App\Services\License\LicenseStateService;
use App\Services\License\ValueObjects\TokenClaims;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\LicenseTestHelper;
use Tests\TestCase;

class LicenseGateLifecycleTest extends TestCase
{
    use RefreshDatabase, LicenseTestHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initLicenseKeys();
    }

    public function test_suspended_state_blocks_panel_access(): void
    {
        $token = $this->createSignedToken(['dom' => 'localhost']);
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_sus_gate',
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

        app(LicenseStateService::class)->saveActivationState($claims, $token);
        app(LicenseStateService::class)->markSuspended();

        $response = $this->get('/login');
        $response->assertRedirect('/activate');

        $adminResponse = $this->get('/admin');
        $adminResponse->assertRedirect('/activate');
    }

    public function test_suspended_state_blocks_landing_page(): void
    {
        $token = $this->createSignedToken(['dom' => 'localhost']);
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_sus_lp',
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

        app(LicenseStateService::class)->saveActivationState($claims, $token);
        app(LicenseStateService::class)->markSuspended();

        $response = $this->get('/');
        $response->assertStatus(503);
        $response->assertSee('WEBSITE BELUM DIAKTIVASI');
    }

    public function test_revoked_state_blocks_panel_access(): void
    {
        $token = $this->createSignedToken(['dom' => 'localhost']);
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_rev_gate',
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

        app(LicenseStateService::class)->saveActivationState($claims, $token);
        app(LicenseStateService::class)->markRevoked();

        $response = $this->get('/login');
        $response->assertRedirect('/activate');
    }

    public function test_revoked_state_blocks_landing_page(): void
    {
        $token = $this->createSignedToken(['dom' => 'localhost']);
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_rev_lp',
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

        app(LicenseStateService::class)->saveActivationState($claims, $token);
        app(LicenseStateService::class)->markRevoked();

        $response = $this->get('/');
        $response->assertStatus(503);
        $response->assertSee('WEBSITE BELUM DIAKTIVASI');
    }

    public function test_api_requests_receive_correct_json_error_code(): void
    {
        $token = $this->createSignedToken(['dom' => 'localhost']);
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_api_json',
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

        app(LicenseStateService::class)->saveActivationState($claims, $token);

        // Test Suspended JSON
        app(LicenseStateService::class)->markSuspended();
        $response = $this->getJson('/');
        $response->assertStatus(503);
        $response->assertJson([
            'success' => false,
            'error' => ['code' => 'LICENSE_SUSPENDED'],
        ]);

        // Test Revoked JSON
        app(LicenseStateService::class)->markRevoked();
        $response = $this->getJson('/');
        $response->assertStatus(503);
        $response->assertJson([
            'success' => false,
            'error' => ['code' => 'LICENSE_REVOKED'],
        ]);
    }
}