<?php

namespace Tests\Feature;

use App\Services\License\LicenseClientService;
use App\Services\License\LicenseStateService;
use App\Services\License\ValueObjects\TokenClaims;
use App\Services\License\Exceptions\LicenseNetworkException;
use App\Services\License\Exceptions\LicenseVerificationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\LicenseTestHelper;
use Tests\TestCase;

/**
 * License Gate — Rolling Token Refresh Tests (Scenarios A–H)
 *
 * Tests the proactive refresh window, token recovery, grace period,
 * and lifecycle blocking behaviour of EnsureValidLicense middleware.
 */
class LicenseGateRollingRefreshTest extends TestCase
{
    use RefreshDatabase, LicenseTestHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initLicenseKeys();

        // Ensure fresh state service singleton per test
        $this->app->singleton(LicenseStateService::class, function () {
            return new LicenseStateService();
        });
    }

    // =========================================================================
    // Scenario A: Token far from expiry → verifyOnline NOT called
    // =========================================================================

    public function test_scenario_a_token_far_from_expiry_does_not_trigger_refresh(): void
    {
        $now = time();

        // Token issued just now, expires in 7 days → 100% remaining TTL — far outside 30% window
        $token = $this->createSignedToken([
            'dom' => 'localhost',
            'iat' => $now - 60,
            'nbf' => $now - 60,
            'exp' => $now + 604800,     // 7 days
            'lic_exp' => $now + 31536000,
        ]);

        $claims = $this->makeClaimsFromToken($token, $now);
        app(LicenseStateService::class)->saveActivationState($claims, $token);

        // Expect NO outbound HTTP — verifyOnline must NOT be called
        Http::fake([]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Assert no HTTP was actually made (fake would record it if called)
        Http::assertNothingSent();
    }

    // =========================================================================
    // Scenario B: Token within 30% refresh window → verifyOnline IS called
    // =========================================================================

    public function test_scenario_b_token_in_refresh_window_triggers_verify_online(): void
    {
        $now = time();

        // Token with 7-day TTL, only 10% remaining (≈ 16.8 hrs left)
        // 30% of 604800 = 181440 seconds; we set remaining = 50000 (< 30%) → in window
        $totalTtl = 604800;
        $iat = $now - ($totalTtl - 50000); // issued so that 50000s remain
        $exp = $iat + $totalTtl;

        $token = $this->createSignedToken([
            'dom' => 'localhost',
            'iat' => $iat,
            'nbf' => $iat,
            'exp' => $exp,
            'lic_exp' => $now + 31536000,
        ]);

        $claims = $this->makeClaimsFromToken($token, $now);
        app(LicenseStateService::class)->saveActivationState($claims, $token);

        // Mock verifyOnline response — server returns valid (no refresh)
        $verifyResponse = [
            'success' => true,
            'data' => [
                'valid' => true,
                'refreshed' => false,
                'token_id' => $claims->jti,
                'expires_at' => null,
                'server_time' => gmdate('Y-m-d\TH:i:s\Z'),
            ],
        ];

        Http::fake([
            '*/api/v1/license/verify' => Http::response($verifyResponse, 200),
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/v1/license/verify');
        });
    }

    // =========================================================================
    // Scenario C: Server responds refreshed=true → new token saved, request allowed
    // =========================================================================

    public function test_scenario_c_refreshed_true_saves_new_token_and_allows_request(): void
    {
        $now = time();

        // Old token within refresh window
        $totalTtl = 604800;
        $iat = $now - ($totalTtl - 50000);
        $exp = $iat + $totalTtl;

        $oldToken = $this->createSignedToken([
            'dom' => 'localhost',
            'iat' => $iat,
            'nbf' => $iat,
            'exp' => $exp,
            'lic_exp' => $now + 31536000,
        ]);

        $oldClaims = $this->makeClaimsFromToken($oldToken, $now);
        app(LicenseStateService::class)->saveActivationState($oldClaims, $oldToken);

        // New token issued by server
        $newToken = $this->createSignedToken([
            'dom' => 'localhost',
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + 604800,
            'lic_exp' => $now + 31536000,
        ]);

        $refreshResponse = [
            'success' => true,
            'data' => [
                'valid' => true,
                'refreshed' => true,
                'token' => $newToken,
                'token_id' => 'tok_new_generation',
                'token_expires_at' => gmdate('Y-m-d\TH:i:s\Z', $now + 604800),
                'expires_at' => null,
                'server_time' => gmdate('Y-m-d\TH:i:s\Z'),
            ],
        ];

        Http::fake([
            '*/api/v1/license/verify' => Http::response($refreshResponse, 200),
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Token in persistent state must have been updated to the new generation
        $freshState = app(LicenseStateService::class)->reloadStateFromDb();
        $this->assertNotNull($freshState);
        $this->assertSame($newToken, $freshState['token']);
        $this->assertSame('ACTIVE', $freshState['status']);
    }

    // =========================================================================
    // Scenario D: Network error during refresh → state unchanged, ACTIVE, request allowed
    // =========================================================================

    public function test_scenario_d_network_error_during_refresh_keeps_state_and_allows_request(): void
    {
        $now = time();

        // Token within refresh window
        $totalTtl = 604800;
        $iat = $now - ($totalTtl - 50000);
        $exp = $iat + $totalTtl;

        $token = $this->createSignedToken([
            'dom' => 'localhost',
            'iat' => $iat,
            'nbf' => $iat,
            'exp' => $exp,
            'lic_exp' => $now + 31536000,
        ]);

        $claims = $this->makeClaimsFromToken($token, $now);
        app(LicenseStateService::class)->saveActivationState($claims, $token);

        // Network completely unreachable
        Http::fake([
            '*/api/v1/license/verify' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
            },
        ]);

        $response = $this->get('/');
        // Token is still locally valid → request MUST be allowed
        $response->assertStatus(200);

        // State must remain ACTIVE with original token intact
        $freshState = app(LicenseStateService::class)->reloadStateFromDb();
        $this->assertNotNull($freshState);
        $this->assertSame('ACTIVE', $freshState['status']);
        $this->assertSame($token, $freshState['token']);
    }

    // =========================================================================
    // Scenario E: Server returns LICENSE_SUSPENDED → state SUSPENDED → blocked
    // =========================================================================

    public function test_scenario_e_license_suspended_response_blocks_request(): void
    {
        $now = time();

        // Token within refresh window so verifyOnline() gets triggered
        $totalTtl = 604800;
        $iat = $now - ($totalTtl - 50000);
        $exp = $iat + $totalTtl;

        $token = $this->createSignedToken([
            'dom' => 'localhost',
            'iat' => $iat,
            'nbf' => $iat,
            'exp' => $exp,
            'lic_exp' => $now + 31536000,
        ]);

        $claims = $this->makeClaimsFromToken($token, $now);
        app(LicenseStateService::class)->saveActivationState($claims, $token);

        Http::fake([
            '*/api/v1/license/verify' => Http::response([
                'success' => false,
                'error' => ['code' => 'LICENSE_SUSPENDED', 'message' => 'License suspended.'],
            ], 403),
        ]);

        // First request: refresh is attempted, SUSPENDED state is written
        $this->get('/');

        // Second request: now persistent state is SUSPENDED → blocked immediately
        $response = $this->get('/');
        $response->assertStatus(503);

        $freshState = app(LicenseStateService::class)->reloadStateFromDb();
        $this->assertSame('SUSPENDED', $freshState['status'] ?? null);
    }

    // =========================================================================
    // Scenario F: Server returns LICENSE_REVOKED → state REVOKED → blocked
    // =========================================================================

    public function test_scenario_f_license_revoked_response_blocks_request(): void
    {
        $now = time();

        $totalTtl = 604800;
        $iat = $now - ($totalTtl - 50000);
        $exp = $iat + $totalTtl;

        $token = $this->createSignedToken([
            'dom' => 'localhost',
            'iat' => $iat,
            'nbf' => $iat,
            'exp' => $exp,
            'lic_exp' => $now + 31536000,
        ]);

        $claims = $this->makeClaimsFromToken($token, $now);
        app(LicenseStateService::class)->saveActivationState($claims, $token);

        Http::fake([
            '*/api/v1/license/verify' => Http::response([
                'success' => false,
                'error' => ['code' => 'LICENSE_REVOKED', 'message' => 'License revoked.'],
            ], 403),
        ]);

        // First request triggers refresh → REVOKED is written
        $this->get('/');

        // Second request: terminal state → blocked immediately
        $response = $this->get('/');
        $response->assertStatus(503);

        $freshState = app(LicenseStateService::class)->reloadStateFromDb();
        $this->assertSame('REVOKED', $freshState['status'] ?? null);
    }

    // =========================================================================
    // Scenario G: Server returns LICENSE_EXPIRED → state EXPIRED → blocked
    // =========================================================================

    public function test_scenario_g_license_expired_response_blocks_request(): void
    {
        $now = time();

        $totalTtl = 604800;
        $iat = $now - ($totalTtl - 50000);
        $exp = $iat + $totalTtl;

        $token = $this->createSignedToken([
            'dom' => 'localhost',
            'iat' => $iat,
            'nbf' => $iat,
            'exp' => $exp,
            'lic_exp' => $now + 31536000,
        ]);

        $claims = $this->makeClaimsFromToken($token, $now);
        app(LicenseStateService::class)->saveActivationState($claims, $token);

        Http::fake([
            '*/api/v1/license/verify' => Http::response([
                'success' => false,
                'error' => ['code' => 'LICENSE_EXPIRED', 'message' => 'License expired.'],
            ], 403),
        ]);

        // First request triggers refresh → EXPIRED is written
        $this->get('/');

        // Second request: terminal state → blocked immediately
        $response = $this->get('/');
        $response->assertStatus(503);

        $freshState = app(LicenseStateService::class)->reloadStateFromDb();
        $this->assertSame('EXPIRED', $freshState['status'] ?? null);
    }

    // =========================================================================
    // Scenario H: TokenExpiredException while licExp still active
    //   → MUST NOT markExpired()
    //   → Recovery via verifyOnline() → new token → allowed
    //   → Network error during recovery → grace period → allowed
    // =========================================================================

    public function test_scenario_h_token_expired_with_active_license_does_not_mark_expired(): void
    {
        $now = time();

        // Token expired 60 seconds ago, but licExp is 1 year away
        $expiredToken = $this->createSignedToken([
            'dom' => 'localhost',
            'iat' => $now - 7200,
            'nbf' => $now - 7200,
            'exp' => $now - 60,          // token expired 60s ago
            'lic_exp' => $now + 31536000, // license valid for another year
        ]);

        // Persist expired-token state with token_expires_at in the past
        $state = [
            'status' => 'ACTIVE',
            'domain' => 'localhost',
            'key_masked' => 'SPJ22-****-****-****',
            'token' => $expiredToken,
            'jti' => 'tok_expired_h',
            'token_expires_at' => $now - 60,
            'license_expires_at' => $now + 31536000,
            'customer' => null,
            'activated_at' => gmdate('Y-m-d\TH:i:s\Z', $now - 7200),
            'last_verified_at' => gmdate('Y-m-d\TH:i:s\Z', $now - 7200),
        ];

        \App\Models\SiteSetting::set('license_state', $state);
        // Reload service so it picks up the raw state we set directly
        $this->app->singleton(LicenseStateService::class, fn() => new LicenseStateService());

        // Server will issue a fresh token for recovery
        $freshToken = $this->createSignedToken([
            'dom' => 'localhost',
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + 604800,
            'lic_exp' => $now + 31536000,
        ]);

        Http::fake([
            '*/api/v1/license/verify' => Http::response([
                'success' => true,
                'data' => [
                    'valid' => true,
                    'refreshed' => true,
                    'token' => $freshToken,
                    'token_id' => 'tok_recovered',
                    'token_expires_at' => gmdate('Y-m-d\TH:i:s\Z', $now + 604800),
                    'expires_at' => null,
                    'server_time' => gmdate('Y-m-d\TH:i:s\Z'),
                ],
            ], 200),
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // CRITICAL: status must NOT have been changed to EXPIRED
        $freshState = app(LicenseStateService::class)->reloadStateFromDb();
        $this->assertNotSame('EXPIRED', $freshState['status'] ?? 'EXPIRED',
            'TokenExpiredException must NEVER trigger markExpired() when licExp is still valid.');
        $this->assertSame('ACTIVE', $freshState['status']);
    }

    public function test_scenario_h_token_expired_network_error_within_grace_period_allows_request(): void
    {
        $now = time();

        config(['license.token_expired_grace_period_seconds' => 86400]); // 24h grace

        // Token expired 60 seconds ago (well within 24h grace)
        $expiredToken = $this->createSignedToken([
            'dom' => 'localhost',
            'iat' => $now - 7200,
            'nbf' => $now - 7200,
            'exp' => $now - 60,
            'lic_exp' => $now + 31536000,
        ]);

        $state = [
            'status' => 'ACTIVE',
            'domain' => 'localhost',
            'key_masked' => 'SPJ22-****-****-****',
            'token' => $expiredToken,
            'jti' => 'tok_grace_h',
            'token_expires_at' => $now - 60,     // 60s expired
            'license_expires_at' => $now + 31536000,
            'customer' => null,
            'activated_at' => gmdate('Y-m-d\TH:i:s\Z', $now - 7200),
            'last_verified_at' => gmdate('Y-m-d\TH:i:s\Z', $now - 7200),
        ];

        \App\Models\SiteSetting::set('license_state', $state);
        $this->app->singleton(LicenseStateService::class, fn() => new LicenseStateService());

        // Network completely down during recovery
        Http::fake([
            '*/api/v1/license/verify' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Unreachable');
            },
        ]);

        $response = $this->get('/');
        // Within grace period → request MUST be allowed
        $response->assertStatus(200);

        // State must remain ACTIVE, not EXPIRED
        $freshState = app(LicenseStateService::class)->reloadStateFromDb();
        $this->assertSame('ACTIVE', $freshState['status'] ?? null);
    }

    public function test_scenario_h_token_expired_network_error_beyond_grace_period_blocks_request(): void
    {
        $now = time();

        config(['license.token_expired_grace_period_seconds' => 3600]); // 1h grace

        // Token expired 7200 seconds ago (2h > 1h grace)
        $expiredToken = $this->createSignedToken([
            'dom' => 'localhost',
            'iat' => $now - 14400,
            'nbf' => $now - 14400,
            'exp' => $now - 7200,        // expired 2h ago
            'lic_exp' => $now + 31536000,
        ]);

        $state = [
            'status' => 'ACTIVE',
            'domain' => 'localhost',
            'key_masked' => 'SPJ22-****-****-****',
            'token' => $expiredToken,
            'jti' => 'tok_grace_expired_h',
            'token_expires_at' => $now - 7200,   // 7200s expired — beyond 1h grace
            'license_expires_at' => $now + 31536000,
            'customer' => null,
            'activated_at' => gmdate('Y-m-d\TH:i:s\Z', $now - 14400),
            'last_verified_at' => gmdate('Y-m-d\TH:i:s\Z', $now - 14400),
        ];

        \App\Models\SiteSetting::set('license_state', $state);
        $this->app->singleton(LicenseStateService::class, fn() => new LicenseStateService());

        Http::fake([
            '*/api/v1/license/verify' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Unreachable');
            },
        ]);

        $response = $this->get('/');
        // Beyond grace period → request MUST be blocked
        $response->assertStatus(503);
    }

    // =========================================================================
    // Internal helper
    // =========================================================================

    /**
     * Build TokenClaims from a token string (requires valid crypto — uses test verifier).
     */
    private function makeClaimsFromToken(string $tokenString, int $now): TokenClaims
    {
        // Parse payload manually (verifier will confirm later during handle())
        $segments = explode('.', $tokenString);
        $payloadBytes = base64_decode(str_pad(
            strtr($segments[1], '-_', '+/'),
            strlen($segments[1]) + (4 - strlen($segments[1]) % 4) % 4,
            '='
        ));
        $payload = json_decode($payloadBytes, true);

        return TokenClaims::fromArray($payload);
    }
}
