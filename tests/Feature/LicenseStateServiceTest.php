<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Services\License\LicenseStateService;
use App\Services\License\ValueObjects\TokenClaims;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseStateServiceTest extends TestCase
{
    use RefreshDatabase;

    private LicenseStateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LicenseStateService();
    }

    public function test_empty_state_returns_null(): void
    {
        $this->assertNull($this->service->getState());
        $this->assertFalse($this->service->isActivated());
    }

    public function test_save_activation_state_stores_proper_fields(): void
    {
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_1234567890abcdef',
            'iss' => 'https://license.katresnanku.com',
            'aud' => 'SPJ22',
            'sub' => 'SPJ22-****-****-****',
            'dom' => 'localhost',
            'iat' => 1700000000,
            'nbf' => 1700000000,
            'exp' => 1700604800,
            'lic_exp' => 1731536000,
            'customer' => ['name' => 'PT Sumber Protein', 'email' => 'admin@spj.co.id'],
        ]);

        $tokenString = 'header.payload.signature';
        $activatedAt = '2026-09-06T12:00:00Z';

        $this->service->saveActivationState($claims, $tokenString, $activatedAt);

        $this->assertTrue($this->service->isActivated());

        $state = $this->service->getState();
        $this->assertNotNull($state);
        $this->assertSame('ACTIVE', $state['status']);
        $this->assertSame('localhost', $state['domain']);
        $this->assertSame('SPJ22-****-****-****', $state['key_masked']);
        $this->assertSame($tokenString, $state['token']);
        $this->assertSame('tok_1234567890abcdef', $state['jti']);
        $this->assertSame(1700604800, $state['token_expires_at']);
        $this->assertSame(1731536000, $state['license_expires_at']);
        $this->assertSame(['name' => 'PT Sumber Protein', 'email' => 'admin@spj.co.id'], $state['customer']);
        $this->assertSame($activatedAt, $state['activated_at']);
        $this->assertSame($activatedAt, $state['last_verified_at']);

        // Verify in database
        $dbRecord = SiteSetting::get('license_state');
        $this->assertSame('ACTIVE', $dbRecord['status']);
        $this->assertSame($tokenString, $dbRecord['token']);
    }

    public function test_update_verification_state(): void
    {
        $initialClaims = TokenClaims::fromArray([
            'jti' => 'tok_initial',
            'iss' => 'https://license.katresnanku.com',
            'aud' => 'SPJ22',
            'sub' => 'SPJ22-****-****-****',
            'dom' => 'localhost',
            'iat' => 1700000000,
            'nbf' => 1700000000,
            'exp' => 1700604800,
            'lic_exp' => 1731536000,
            'customer' => null,
        ]);

        $this->service->saveActivationState($initialClaims, 'initial.token.sig', '2026-09-01T00:00:00Z');

        $refreshedClaims = TokenClaims::fromArray([
            'jti' => 'tok_refreshed',
            'iss' => 'https://license.katresnanku.com',
            'aud' => 'SPJ22',
            'sub' => 'SPJ22-****-****-****',
            'dom' => 'localhost',
            'iat' => 1700500000,
            'nbf' => 1700500000,
            'exp' => 1701104800,
            'lic_exp' => 1731536000,
            'customer' => null,
        ]);

        $this->service->updateVerificationState($refreshedClaims, 'refreshed.token.sig');

        $state = $this->service->getState();
        $this->assertSame('refreshed.token.sig', $state['token']);
        $this->assertSame('tok_refreshed', $state['jti']);
        $this->assertSame(1701104800, $state['token_expires_at']);
        $this->assertSame('2026-09-01T00:00:00Z', $state['activated_at']);
        $this->assertNotEmpty($state['last_verified_at']);
    }

    public function test_mark_revoked(): void
    {
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_rev',
            'iss' => 'https://license.katresnanku.com',
            'aud' => 'SPJ22',
            'sub' => 'SPJ22-****-****-****',
            'dom' => 'localhost',
            'iat' => 1700000000,
            'nbf' => 1700000000,
            'exp' => 1700604800,
            'lic_exp' => null,
            'customer' => null,
        ]);

        $this->service->saveActivationState($claims, 'tok.rev.sig');
        $this->service->markRevoked();

        $state = $this->service->getState();
        $this->assertSame('REVOKED', $state['status']);
    }

    public function test_mark_expired(): void
    {
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_exp',
            'iss' => 'https://license.katresnanku.com',
            'aud' => 'SPJ22',
            'sub' => 'SPJ22-****-****-****',
            'dom' => 'localhost',
            'iat' => 1700000000,
            'nbf' => 1700000000,
            'exp' => 1700604800,
            'lic_exp' => null,
            'customer' => null,
        ]);

        $this->service->saveActivationState($claims, 'tok.exp.sig');
        $this->service->markExpired();

        $state = $this->service->getState();
        $this->assertSame('EXPIRED', $state['status']);
    }

    public function test_clear_removes_state_from_database_and_cache(): void
    {
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_clear',
            'iss' => 'https://license.katresnanku.com',
            'aud' => 'SPJ22',
            'sub' => 'SPJ22-****-****-****',
            'dom' => 'localhost',
            'iat' => 1700000000,
            'nbf' => 1700000000,
            'exp' => 1700604800,
            'lic_exp' => null,
            'customer' => null,
        ]);

        $this->service->saveActivationState($claims, 'tok.clear.sig');
        $this->assertTrue($this->service->isActivated());

        $this->service->clear();

        $this->assertNull($this->service->getState());
        $this->assertFalse($this->service->isActivated());
        $this->assertNull(SiteSetting::get('license_state'));
    }

    public function test_malformed_state_handling(): void
    {
        // Malformed or empty token in DB
        SiteSetting::set('license_state', ['status' => 'ACTIVE']);
        $this->assertNull($this->service->getState());
        $this->assertFalse($this->service->isActivated());
    }

    public function test_no_secret_or_private_key_persistence(): void
    {
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_sec',
            'iss' => 'https://license.katresnanku.com',
            'aud' => 'SPJ22',
            'sub' => 'SPJ22-****-****-****',
            'dom' => 'localhost',
            'iat' => 1700000000,
            'nbf' => 1700000000,
            'exp' => 1700604800,
            'lic_exp' => null,
            'customer' => null,
        ]);

        $this->service->saveActivationState($claims, 'tok.sec.sig');
        $state = $this->service->getState();

        $this->assertArrayNotHasKey('api_secret', $state);
        $this->assertArrayNotHasKey('private_key', $state);
        $this->assertArrayNotHasKey('secret_key', $state);
        $this->assertArrayNotHasKey('license_key', $state);
    }
}