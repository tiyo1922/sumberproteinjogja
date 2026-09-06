<?php

namespace App\Http\Middleware;

use App\Services\License\Ed25519TokenVerifier;
use App\Services\License\Exceptions\LicenseExpiredException;
use App\Services\License\LicenseStateService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsureValidLicense
{
    public function __construct(
        private LicenseStateService $stateService,
        private Ed25519TokenVerifier $verifier
    ) {}

    /**
     * Handle an incoming request through the License Gate.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Always allow whitelisted routes (Activation & Healthcheck)
        if ($request->is('activate', 'activate/*', 'up')) {
            return $next($request);
        }

        // 2. Verify local license state and cryptographic token
        $isValid = false;
        $state = $this->stateService->getState();
        $status = $state['status'] ?? null;

        // Block explicit terminal/restricted states immediately
        if ($status === 'REVOKED' || $status === 'SUSPENDED' || $status === 'EXPIRED') {
            return $this->handleUnauthorizedAccess($request, $status);
        }

        if ($state !== null && $status === 'ACTIVE' && !empty($state['token'])) {
            try {
                $this->verifier->verify($state['token'], $request->getHost());
                $isValid = true;
            } catch (LicenseExpiredException) {
                $this->stateService->markExpired();
                $isValid = false;
                $status = 'EXPIRED';
            } catch (Throwable) {
                $isValid = false;
            }
        }

        // 3. If license is valid, proceed to requested resource
        if ($isValid) {
            return $next($request);
        }

        return $this->handleUnauthorizedAccess($request, $status ?? 'UNACTIVATED');
    }

    /**
     * Handle unauthorized access when license is not valid.
     */
    private function handleUnauthorizedAccess(Request $request, string $status): Response
    {
        // A. Panel / Admin / Auth routes --> Redirect to /activate
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

        // B. API / JSON requests --> Return JSON error
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

        // C. Landing Page & Public Storefront --> Render branded unactivated page (503)
        return response()->view('errors.unactivated', [], 503);
    }
}