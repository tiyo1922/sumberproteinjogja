<?php

namespace App\Http\Controllers;

use App\Services\License\DomainCanonicalizer;
use App\Services\License\Ed25519TokenVerifier;
use App\Services\License\Exceptions\LicenseActivationException;
use App\Services\License\LicenseClientService;
use App\Services\License\LicenseStateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Throwable;

class LicenseActivationController extends Controller
{
    public function __construct(
        private LicenseClientService $clientService,
        private LicenseStateService $stateService,
        private Ed25519TokenVerifier $verifier
    ) {}

    /**
     * Display the pre-login license activation form.
     */
    public function show(Request $request): View|RedirectResponse
    {
        // If already activated and locally valid for current domain, redirect to login
        if ($this->stateService->isActivated()) {
            $state = $this->stateService->getState();
            if (!empty($state['token'])) {
                try {
                    $this->verifier->verify($state['token'], $request->getHost());
                    return redirect()->route('login');
                } catch (Throwable) {
                    // Token expired or invalid; allow user to activate with a new key
                }
            }
        }

        $detectedDomain = DomainCanonicalizer::canonicalize($request->getHost());

        return view('auth.activate', [
            'detectedDomain' => $detectedDomain,
        ]);
    }

    /**
     * Process the server-side license activation submission.
     */
    public function activate(Request $request): RedirectResponse
    {
        $limiterKey = 'license-activate:' . $request->ip();

        if (RateLimiter::tooManyAttempts($limiterKey, 5)) {
            $seconds = RateLimiter::availableIn($limiterKey);
            return back()->withErrors([
                'license_code' => "Terlalu banyak percobaan aktivasi. Silakan tunggu {$seconds} detik sebelum mencoba lagi.",
            ])->withInput();
        }

        $validated = $request->validate([
            'license_code' => ['required', 'string', 'min:8', 'max:100'],
        ], [
            'license_code.required' => 'Kode aktivasi lisensi wajib diisi.',
            'license_code.string' => 'Kode aktivasi lisensi harus berupa teks.',
            'license_code.min' => 'Format kode lisensi terlalu pendek.',
            'license_code.max' => 'Format kode lisensi terlalu panjang.',
        ]);

        try {
            $this->clientService->activate($validated['license_code'], $request->getHost());

            RateLimiter::clear($limiterKey);
            $request->session()->regenerate();

            return redirect()->route('login')->with('success', 'Aktivasi lisensi berhasil! Silakan masuk ke panel admin.');
        } catch (LicenseActivationException $e) {
            RateLimiter::hit($limiterKey, 300);

            return back()->withErrors([
                'license_code' => $e->getMessage(),
            ])->withInput();
        } catch (Throwable) {
            RateLimiter::hit($limiterKey, 300);

            return back()->withErrors([
               'license_code' => 'Terjadi kesalahan sistem saat memproses aktivasi lisensi.',
            ])->withInput();
        }
    }
}
