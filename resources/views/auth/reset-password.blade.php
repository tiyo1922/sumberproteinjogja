@php
    $site = $site ?? app(\App\Repositories\Contracts\SiteSettingRepositoryInterface::class)->get('site', config('site', []));
    $rawLoginLogo = trim($site['brand']['logo_url'] ?? '');
    $hasLoginCustomLogo = !empty($rawLoginLogo) 
        && !str_contains($rawLoginLogo, 'hero-1.jpg') 
        && !str_starts_with($rawLoginLogo, 'blob:')
        && (str_starts_with($rawLoginLogo, 'http') || file_exists(public_path($rawLoginLogo)) || file_exists(public_path('storage/' . ltrim($rawLoginLogo, '/'))));
    $loginCustomLogoUrl = $hasLoginCustomLogo
        ? (str_starts_with($rawLoginLogo, 'http') ? $rawLoginLogo : (file_exists(public_path($rawLoginLogo)) ? asset($rawLoginLogo) : asset('storage/' . ltrim($rawLoginLogo, '/'))))
        : null;
    $loginBrandName = $site['brand']['name'] ?? 'Sumber Protein Jogja';
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F4F6F4]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi — Admin Panel {{ $loginBrandName }}</title>
    
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    
    <!-- Local Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            border: none;
            outline: none;
        }
    </style>
</head>
<body class="min-h-screen bg-[#F4F6F4] text-brand-dark font-sans antialiased selection:bg-brand-primary selection:text-white flex items-center justify-center p-4 sm:p-6">

    <!-- Centered Fixed-Width Container (420px - 460px) -->
    <div class="w-full max-w-[460px] mx-auto flex flex-col items-center">
        
        <!-- Brand Header Area (Compact) -->
        <div class="flex flex-col items-center text-center mb-5">
            <div class="w-12 h-12 flex items-center justify-center mb-2.5 transition-transform hover:scale-105 shrink-0">
                @if($hasLoginCustomLogo)
                    <img src="{{ $loginCustomLogoUrl }}" alt="{{ $loginBrandName }}" class="w-full h-full object-contain">
                @else
                    <svg class="w-10 h-10 text-brand-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2a5 5 0 0 1 5 5v1a5 5 0 0 1-5 5 5 5 0 0 1-5-5V7a5 5 0 0 1 5-5z" fill="currentColor" fill-opacity="0.2"/>
                        <path d="M12 13v9"/>
                        <path d="M7 17l5 5 5-5"/>
                        <circle cx="12" cy="7" r="3"/>
                    </svg>
                @endif
            </div>
            <h1 class="text-xl font-extrabold text-brand-dark tracking-tight leading-tight">
                {{ $loginBrandName }}
            </h1>
            <p class="text-[10px] sm:text-[11px] font-bold text-emerald-700 uppercase tracking-wider mt-0.5 flex items-center gap-1.5">
                <span>CMS Management Panel</span>
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            </p>
        </div>

        <!-- Reset Password Card Container -->
        <div class="w-full bg-white py-7 sm:py-8 px-6 sm:px-8 shadow-xl shadow-gray-200/50 rounded-2xl border border-gray-100"
             x-data="{ 
                 showNewPassword: false, 
                 showConfirmPassword: false,
                 isSubmitting: false 
             }">
            
            <!-- Card Header -->
            <div class="mb-5 text-left">
                <h2 class="text-[22px] font-bold text-gray-900 tracking-tight leading-tight">
                    Atur Ulang Kata Sandi
                </h2>
                <p class="text-xs sm:text-[13px] text-gray-500 mt-1 font-normal">
                    Buat kata sandi baru yang aman untuk akun administrator Anda.
                </p>
            </div>

            <!-- Error Banner -->
            @if (isset($errors) && $errors->any())
                <div class="mb-4 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm space-y-1" role="alert">
                    <div class="font-bold flex items-center gap-1.5 text-xs">
                        <svg class="w-4 h-4 shrink-0 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span>Gagal Mengubah Kata Sandi</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px] sm:text-xs text-red-700 pl-1 font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Reset Password Form -->
            <form action="{{ route('password.update') }}" 
                  method="POST" 
                  @submit="isSubmitting = true" 
                  class="space-y-4">
                @csrf

                <!-- Hidden Reset Token -->
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-700 mb-1.5">
                        Email Administrator
                    </label>
                    <input id="email" 
                           name="email" 
                           type="email" 
                           autocomplete="email" 
                           required 
                           value="{{ old('email', $email ?? '') }}" 
                           placeholder="nama@sumberproteinjogja.com"
                           class="w-full h-11 px-3.5 rounded-xl border border-gray-200 text-sm text-gray-900 bg-white placeholder-gray-400 focus:outline-hidden focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-all">
                </div>

                <!-- Password Input with Eye Visibility Toggle -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-700 mb-1.5">
                        Password Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input id="password" 
                               name="password" 
                               :type="showNewPassword ? 'text' : 'password'" 
                               autocomplete="new-password" 
                               required 
                               placeholder="Min. 8 karakter"
                               class="w-full h-11 pl-3.5 pr-11 rounded-xl border border-gray-200 text-sm text-gray-900 bg-white placeholder-gray-400 focus:outline-hidden focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-all">
                        
                        <!-- Eye Visibility Toggle Button -->
                        <button type="button" 
                                @click="showNewPassword = !showNewPassword"
                                :aria-label="showNewPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                                class="absolute inset-y-0 right-0 w-11 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-hidden focus:text-brand-primary cursor-pointer transition-colors"
                                tabindex="0">
                            <!-- Eye Open Icon (When hidden) -->
                            <svg x-show="!showNewPassword" class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <!-- Eye Slash Icon (When visible) -->
                            <svg x-show="showNewPassword" x-cloak class="w-4 h-4 sm:w-4.5 sm:h-4.5 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Password Confirmation Input with Eye Visibility Toggle -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-gray-700 mb-1.5">
                        Konfirmasi Password Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input id="password_confirmation" 
                               name="password_confirmation" 
                               :type="showConfirmPassword ? 'text' : 'password'" 
                               autocomplete="new-password" 
                               required 
                               placeholder="Ulangi password baru"
                               class="w-full h-11 pl-3.5 pr-11 rounded-xl border border-gray-200 text-sm text-gray-900 bg-white placeholder-gray-400 focus:outline-hidden focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-all">
                        
                        <!-- Eye Visibility Toggle Button -->
                        <button type="button" 
                                @click="showConfirmPassword = !showConfirmPassword"
                                :aria-label="showConfirmPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                                class="absolute inset-y-0 right-0 w-11 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-hidden focus:text-brand-primary cursor-pointer transition-colors"
                                tabindex="0">
                            <!-- Eye Open Icon (When hidden) -->
                            <svg x-show="!showConfirmPassword" class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <!-- Eye Slash Icon (When visible) -->
                            <svg x-show="showConfirmPassword" x-cloak class="w-4 h-4 sm:w-4.5 sm:h-4.5 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Primary Submit Button -->
                <div class="pt-1">
                    <button type="submit" 
                            :disabled="isSubmitting"
                            class="w-full h-11 sm:h-12 bg-brand-primary hover:bg-brand-primary/90 active:scale-[0.99] text-white font-semibold text-xs rounded-xl shadow-md shadow-brand-primary/20 hover:shadow-lg hover:shadow-brand-primary/30 transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="isSubmitting" x-cloak class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isSubmitting ? 'Memperbarui...' : 'Perbarui Kata Sandi'">Perbarui Kata Sandi</span>
                    </button>
                </div>
            </form>

            <!-- Back to Login Link -->
            <div class="mt-5 pt-4 border-t border-gray-100 flex flex-col items-center gap-1 text-center">
                <a href="{{ route('login') }}" class="text-xs text-brand-primary hover:text-brand-primary/80 font-semibold inline-flex items-center gap-1.5 transition-colors group">
                    <svg class="w-3.5 h-3.5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali ke Halaman Masuk</span>
                </a>
            </div>

        </div>

        <!-- Minimal Footer -->
        <p class="mt-5 text-center text-xs text-gray-400 font-normal">
            Sumber Protein Jogja &copy; {{ date('Y') }}
        </p>

    </div>

</body>
</html>
