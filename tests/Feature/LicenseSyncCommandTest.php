<?php

namespace Tests\Feature;

use App\Services\License\LicenseStateService;
use App\Services\License\ValueObjects\TokenClaims;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\LicenseTestHelper;
use Tests\TestCase;

class LicenseSyncCommandTest extends TestCase
{
    use RefreshDatabase, LicenseTestHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initLicenseKeys();
    }

    public function test_artisan_sync_successful(): void
    {
        $token = $this->createSignedToken(['dom' => 'localhost']);
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_cmd_1',
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

        Http::fake([
            'https://license.katresnanku.com/api/v1/license/verify' => Http::response([
                'success' => true,
                'data' => [
                    'valid' => true,
                    'status' => 'ACTIVE',
                    'token' => $token,
                    'token_id' => 'tok_cmd_1',
                    'refreshed' => false,
                ],
            ], 200),
        ]);

        $this->artisan('license:sync')
            ->expectsOutputToContain('[License Sync] Starting operational license synchronization...')
            ->expectsOutputToContain('[License Sync] SUCCESS: License verified online.')
            ->assertExitCode(0);
    }

    public function test_artisan_sync_refreshed_token(): void
    {
        $oldToken = $this->createSignedToken(['dom' => 'localhost', 'jti' => 'tok_old']);
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_old',
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

        app(LicenseStateService::class)->saveActivationState($claims, $oldToken);

        $newToken = $this->createSignedToken([
            'dom' => 'localhost',
            'jti' => 'tok_new_refresh',
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
                    'token' => $newToken,
                    'token_id' => 'tok_new_refresh',
                    'refreshed' => true,
                ],
            ], 200),
        ]);

        $this->artisan('license:sync')
            ->expectsOutputToContain('[License Sync] SUCCESS: License verified and rolling token refreshed. (JTI: tok_new_refresh)')
            ->assertExitCode(0);
    }

    public function test_artisan_sync_handles_network_warning_gracefully(): void
    {
        $token = $this->createSignedToken(['dom' => 'localhost']);
        $claims = TokenClaims::fromArray([
            'jti' => 'tok_net_warn',
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

        Http::fake([
            'https://license.katresnanku.com/api/v1/license/verify' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection reset');
            },
        ]);

        $this->artisan('license:sync')
            ->expectsOutputToContain('[License Sync] NETWORK WARNING:')
            ->expectsOutputToContain('Local license state preserved.')
            ->assertExitCode(0);
    }

    public function test_artisan_sync_when_unactivated(): void
    {
        $this->artisan('license:sync')
            ->expectsOutputToContain('[License Sync] No active license found on this installation.')
            ->assertExitCode(0);
    }
}