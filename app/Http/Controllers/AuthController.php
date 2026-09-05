<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the application login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email')->setStatusCode(429);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'Email atau password tidak valid.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show the forgot password request form.
     */
    public function showForgotPassword()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.forgot-password');
    }

    /**
     * Send a password reset link to the single administrator account.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $throttleKey = 'forgot-password|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            return back()->withErrors([
                'rate_limit' => "Terlalu banyak permintaan reset kata sandi. Silakan tunggu {$seconds} detik.",
            ])->setStatusCode(429);
        }

        RateLimiter::hit($throttleKey, 60);

        // Send reset link via Laravel Password Broker for single admin
        try {
            $admin = User::first();

            if ($admin && !empty($admin->email)) {
                Password::sendResetLink(['email' => $admin->email]);
            }
        } catch (\Throwable $e) {
            // Silently handle error or mail failure to maintain enumeration safety and fault tolerance
        }

        // Generic response for email enumeration protection
        return back()->with('status', 'Tautan pemulihan kata sandi telah dikirim ke email administrator.');
    }

    /**
     * Show the reset password form for the given token.
     */
    public function showResetPassword(Request $request, string $token)
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.reset-password', [
            'token' => $token,
        ]);
    }

    /**
     * Reset the single administrator's password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'token.required' => 'Token reset kata sandi tidak valid.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        try {
            $admin = User::first();

            if (!$admin || empty($admin->email)) {
                return back()->withErrors([
                    'password' => 'Akun administrator tidak ditemukan.',
                ]);
            }

            $credentials = [
                'email' => $admin->email,
                'password' => $request->input('password'),
                'password_confirmation' => $request->input('password_confirmation'),
                'token' => $request->input('token'),
            ];

            $status = Password::reset(
                $credentials,
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->setRememberToken(Str::random(60));

                    $user->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return redirect()->route('login')->with('status', 'Kata sandi Anda berhasil diperbarui! Silakan masuk menggunakan kata sandi baru.');
            }

            return back()->withErrors([
                'password' => 'Tautan pemulihan kata sandi tidak valid atau telah kedaluwarsa. Silakan ajukan permohonan baru.',
            ]);
        } catch (\Throwable $e) {
            return back()->withErrors([
                'password' => 'Gagal memproses pemulihan kata sandi. Silakan coba beberapa saat lagi.',
            ]);
        }
    }
}
