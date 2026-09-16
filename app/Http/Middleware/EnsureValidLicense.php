<?php

namespace App\Http\Middleware;

use App\Services\License\Ed25519TokenVerifier;
use App\Services\License\Exceptions\LicenseExpiredException;
use App\Services\License\Exceptions\LicenseNetworkException;
use App\Services\License\Exceptions\LicenseVerificationException;
use App\Services\License\Exceptions\TokenExpiredException;
use App\Services\License\LicenseClientService;
use App\Services\License\LicenseStateService;
use App\Services\License\ValueObjects\TokenClaims;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsureValidLicense
{
    /** Terminal lifecycle statuses that block immediately without recovery. */
    private const TERMINAL_STATUSES = ['REVOKED', 'SUSPENDED', 'EXPIRED'];

    public function __construct(
        private LicenseStateService $stateService,
        private Ed25519TokenVerifier $verifier,
        private LicenseClientService $clientService
    ) {}

    /**
     * Handle an incoming request through the License Gate.
     *
     * Flow:
     * 1. Whitelisted routes pass through unconditionally.
     * 2. Terminal states (REVOKED/SUSPENDED/EXPIRED) block immediately.
     * 3. ACTIVE + token: local Ed25519 verification.
     *    a. LicenseExpiredException (licExp reached) → markExpired() → block.
     *    b. TokenExpiredException (token only, licExp still valid) → attemptTokenRecovery().
     *    c. Other crypto failures → block as UNACTIVATED.
     * 4. Local verify succeeds → check refresh window → attemptOnlineRefresh() if due.
     * 5. Pass to next handler.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Always allow whitelisted routes (Activation & Healthcheck)
        if ($request->is('activate', 'activate/*', 'up')) {
            return $next($request);
        }

        // 2. Read current persistent state
        $state = $this->stateService->getState();
        $status = $state['status'] ?? null;

        // Block terminal lifecycle states immediately — no recovery attempted
        if (in_array($status, self::TERMINAL_STATUSES, true)) {
            return $this->handleUnauthorizedAccess($request, $status);
        }

        // 3. Cryptographic local verification (ACTIVE state with stored token)
        $isValid = false;
        $claims = null;

        if ($state !== null && $status === 'ACTIVE' && !empty($state['token'])) {
            try {
                $claims = $this->verifier->verify($state['token'], $request->getHost());
                $isValid = true;
            } catch (LicenseExpiredException) {
                // Authoritative license expiration: licExp has been reached.
                // Mark local state EXPIRED — this is a true license lifecycle event.
                $this->stateService->markExpired();
                return $this->handleUnauthorizedAccess($request, 'EXPIRED');
            } catch (TokenExpiredException) {
                // Short-lived token has expired, but license lifetime (licExp) is still valid.
                // NEVER markExpired() here — attempt online recovery instead.
                return $this->attemptTokenRecovery($request, $next, $state);
            } catch (Throwable) {
                // Other crypto failures (bad signature, domain mismatch, unknown key, etc.)
                $isValid = false;
            }
        }

        // 4. Local verification passed — check proactive refresh window
        if ($isValid && $claims !== null) {
            if ($this->shouldAttemptOnlineRefresh($claims, $state ?? [])) {
                // Stamp the timestamp BEFORE the network call to prevent
                // concurrent requests all hammering the server simultaneously.
                $this->stateService->markRefreshCheckAttempted();
                $this->attemptOnlineRefresh();
            }

            return $next($request);
        }

        // 5. All recovery paths exhausted
        return $this->handleUnauthorizedAccess($request, $status ?? 'UNACTIVATED');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Determine whether an online refresh should be attempted right now.
     *
     * Conditions (BOTH must be true):
     *   A) Token is within the configured refresh window (remaining TTL ≤ threshold%).
     *   B) At least `refresh_check_interval_seconds` have elapsed since the last attempt.
     *
     * @param TokenClaims $claims Verified claims from the current token.
     * @param array<string, mixed> $state Full license state array.
     */
    private function shouldAttemptOnlineRefresh(TokenClaims $claims, array $state): bool
    {
        $now = time();

        // A) Refresh window check
        $totalTtl = $claims->exp - $claims->iat;
        if ($totalTtl <= 0) {
            return false;
        }

        $remainingTtl = $claims->exp - $now;
        $threshold = (float) config('license.refresh_threshold_percentage', 0.30);

        $inRefreshWindow = ($remainingTtl <= ($totalTtl * $threshold));

        if (!$inRefreshWindow) {
            return false;
        }

        // B) Interval gate: don't hammer the server on every request
        $intervalSeconds = (int) config('license.refresh_check_interval_seconds', 3600);
        $lastCheckAt = $state['last_refresh_check_at'] ?? null;

        if ($lastCheckAt !== null) {
            $lastCheckTimestamp = strtotime($lastCheckAt);
            if ($lastCheckTimestamp !== false && ($now - $lastCheckTimestamp) < $intervalSeconds) {
                return false;
            }
        }

        return true;
    }

    /**
     * Perform a non-blocking online refresh attempt.
     *
     * Called only when the token has passed local verification and is within
     * the refresh window. Any failure is silently swallowed — the request
     * continues with the existing valid token.
     *
     * Lifecycle failures (SUSPENDED/REVOKED/EXPIRED) are handled on the NEXT
     * request: verifyOnline() already updated the persistent state, so the
     * terminal check at the top of handle() will catch them.
     *
     * MUST NOT call LicenseClientService::activate().
     * MUST NOT produce ACTIVATION_SUCCESS.
     */
    private function attemptOnlineRefresh(): void
    {
        try {
            $this->clientService->verifyOnline();
            // If refreshed=true, verifyOnline() already called updateVerificationState().
            // No further action needed here.
        } catch (LicenseNetworkException) {
            // Network unreachable / server 5xx → do nothing.
            // Local state is untouched; existing valid token continues to serve.
        } catch (LicenseVerificationException) {
            // Lifecycle transition (SUSPENDED/REVOKED/EXPIRED) → state already updated
            // by LicenseClientService. Silently continue; next request will block.
        } catch (Throwable) {
            // Unexpected exception — absorb silently, request must not be disrupted.
        }
    }

    /**
     * Recovery flow for an expired short-lived token with an active license lifetime.
     *
     * Called ONLY when TokenExpiredException is caught (never for LicenseExpiredException).
     *
     * Flow:
     *   1. Call verifyOnline() — server may issue a new token generation.
     *   2. If success with refreshed=true → new token saved → verify locally → allow.
     *   3. If success without refresh → token already stored is current → allow (odd edge case).
     *   4. If network failure:
     *        a. Within grace period (now - token_expires_at ≤ grace) → allow.
     *        b. Beyond grace period → block.
     *   5. Lifecycle failure (SUSPENDED/REVOKED/EXPIRED) → block by new status.
     *   6. Other error → block as UNACTIVATED.
     *
     * MUST NOT call LicenseClientService::activate().
     * MUST NOT call stateService->markExpired().
     */
    private function attemptTokenRecovery(Request $request, Closure $next, array $state): Response
    {
        try {
            $this->clientService->verifyOnline();

            // Online verification succeeded; verifyOnline() has already persisted
            // the refreshed/verified token. Reload and re-verify locally.
            $freshState = $this->stateService->reloadStateFromDb();
            if ($freshState !== null && !empty($freshState['token'])) {
                try {
                    $this->verifier->verify($freshState['token'], $request->getHost());
                    return $next($request);
                } catch (Throwable) {
                    // Newly issued token failed local crypto — should not happen,
                    // but fail safe.
                }
            }

            return $this->handleUnauthorizedAccess($request, 'UNACTIVATED');

        } catch (LicenseNetworkException) {
            // Central server unreachable — check grace period to avoid
            // hard-blocking a valid license customer due to transient outage.
            return $this->handleNetworkErrorGracePeriod($request, $next, $state);

        } catch (LicenseVerificationException $e) {
            // Server returned an authoritative lifecycle response, OR a token
            // generation conflict (TOKEN_SUPERSEDED) caused by a concurrent refresh.
            $freshState = $this->stateService->reloadStateFromDb();

            // TOKEN_SUPERSEDED: a concurrent request has already refreshed the token.
            // The license is still ACTIVE — serve this request using the latest
            // token generation already persisted to DB by the concurrent refresh.
            if ($e->getErrorCode() === 'TOKEN_SUPERSEDED'
                && $freshState !== null
                && ($freshState['status'] ?? '') === 'ACTIVE'
                && !empty($freshState['token'])
            ) {
                try {
                    $this->verifier->verify($freshState['token'], $request->getHost());
                    // Latest token generation is cryptographically valid — allow.
                    return $next($request);
                } catch (Throwable) {
                    // Fresh token failed local crypto verification — fail safe and block.
                }
            }

            // Authoritative lifecycle failure (SUSPENDED / REVOKED / EXPIRED) or
            // any other verification error: block by the authoritative status.
            $newStatus = $freshState['status'] ?? $state['status'] ?? 'UNACTIVATED';
            return $this->handleUnauthorizedAccess($request, $newStatus);

        } catch (Throwable) {
            return $this->handleUnauthorizedAccess($request, 'UNACTIVATED');
        }
    }

    /**
     * Grace period fallback: allow the request when:
     *   - The central server is unreachable (LicenseNetworkException), AND
     *   - The token has expired within the configured grace window.
     *
     * The grace period only applies to TOKEN expiration, never to LICENSE (licExp) expiration.
     * If gracePeriodSeconds is 0, grace is disabled and we block immediately.
     *
     * @param array<string, mixed> $state The license state array containing token_expires_at.
     */
    private function handleNetworkErrorGracePeriod(Request $request, Closure $next, array $state): Response
    {
        $gracePeriodSeconds = (int) config('license.token_expired_grace_period_seconds', 86400);

        if ($gracePeriodSeconds <= 0) {
            return $this->handleUnauthorizedAccess($request, 'UNACTIVATED');
        }

        $tokenExpiresAt = $state['token_expires_at'] ?? null;
        if (!is_int($tokenExpiresAt) || $tokenExpiresAt <= 0) {
            // Cannot determine expiry from state — fail safe.
            return $this->handleUnauthorizedAccess($request, 'UNACTIVATED');
        }

        $now = time();
        $secondsExpired = $now - $tokenExpiresAt;

        if ($secondsExpired <= $gracePeriodSeconds) {
            // Still within grace period — allow request to continue.
            // License is presumed ACTIVE (server unreachable, not authoritative failure).
            return $next($request);
        }

        // Grace period exhausted — block until server is reachable.
        return $this->handleUnauthorizedAccess($request, 'UNACTIVATED');
    }

    /**
     * Handle unauthorized access when license is not valid.
     *
     * Routing:
     *   A. Panel / Admin / Auth routes → Redirect to /activate
     *   B. API / JSON requests → JSON error response (503)
     *   C. Landing Page & Public Storefront → Branded unactivated page (503)
     */
    private function handleUnauthorizedAccess(Request $request, string $status): Response
    {
        // A. Panel / Admin / Auth routes → Redirect to /activate
        if ($request->is(
            'login',
            'login/*',
            'admin',
            'admin/*',
            'forgot-password',
            'forgot-password/*',
            'reset-password',
            'reset-password/*',
            'logout',
            'logout/*'
        )) {
            return redirect()->route('license.activate');
        }

        // B. API / JSON requests → Return JSON error
        if ($request->expectsJson() || $request->is('api/*')) {
            $errorCode = match ($status) {
                'SUSPENDED' => 'LICENSE_SUSPENDED',
                'REVOKED' => 'LICENSE_REVOKED',
                'EXPIRED' => 'LICENSE_EXPIRED',
                default => 'LICENSE_UNACTIVATED',
            };

            $errorMessage = match ($status) {
                'SUSPENDED' => 'Lisensi sedang ditangguhkan.',
                'REVOKED' => 'Lisensi telah dicabut permanen.',
                'EXPIRED' => 'Masa berlaku lisensi telah berakhir.',
                default => 'Website belum diaktivasi.',
            };

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $errorCode,
                    'message' => $errorMessage,
                ],
            ], 503);
        }

        // C. Landing Page & Public Storefront → Render branded unactivated page (503)
        return response()->view('errors.unactivated', [], 503);
    }
}