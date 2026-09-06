@php
    try {
        $site = \App\Models\SiteSetting::get('site', config('site', []));
    } catch (\Throwable $e) {
        $site = config('site', []);
    }
    $rawLoginLogo = trim($site['brand']['logo_url'] ?? '');
    $hasLoginCustomLogo = !empty($rawLoginLogo) 
        && !str_contains($rawLoginLogo, 'hero-1.jpg') 
        && !str_starts_with($rawLoginLogo, 'blob:')
        && (str_starts_with($rawLoginLogo, 'http') || file_exists(public_path($rawLoginLogo)) || file_exists(public_path('storage/' . ltrim($rawLoginLogo, '/'))));
    $loginCustomLogoUrl = $hasLoginCustomLogo
        ? (str_starts_with($rawLoginLogo, 'http') ? $rawLoginLogo : (file_exists(public_path($rawLoginLogo)) ? asset($rawLoginLogo) : asset('storage/' . ltrim($rawLoginLogo, '/'))))
        : null;
    $loginBrandName = $site['brand']['name'] ?? 'Sumber Protein Jogja';
    $errors = $errors ?? session('errors', new \Illuminate\Support\ViewErrorBag());
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F4F6F4]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Lisensi — Admin Panel {{ $loginBrandName }}</title>
    <meta name="robots" content="noindex, nofollow">
    
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
<body class="min-h-screen bg-[#F4F6F4] text-brand-dark font-sans antialiased selection:bg-brand-primary selection:text-white flex items-center justify-center p-4 sm:p-6 relative overflow-x-hidden">

    <!-- Subtle Decorative Brand Accents (Background Ambient) -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0" aria-hidden="true">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-emerald-100/40 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full bg-brand-soft-green/50 blur-3xl"></div>
    </div>

    <!-- Centered Fixed-Width Container (420px - 480px) -->
    <div class="w-full max-w-[460px] mx-auto flex flex-col items-center relative z-10">

        <!-- 1. BRAND / LOGO AREA (Top Header) -->
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

        <!-- 2. ACTIVATION CARD (Focal Point) -->
        <div class="w-full bg-white py-7 sm:py-8 px-6 sm:px-8 shadow-xl shadow-gray-200/60 rounded-2xl border border-gray-100">

            <!-- Card Header: Icon, Title, Subtitle, Domain Badge -->
            <div class="flex flex-col items-center text-center">
                <!-- Security / License Icon -->
                <div class="w-11 h-11 rounded-xl bg-brand-soft-green text-brand-primary flex items-center justify-center mb-3 shadow-xs">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>

                <h2 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">
                    Aktivasi Sistem
                </h2>
                <p class="text-xs text-gray-500 mt-1.5 font-normal max-w-xs leading-relaxed">
                    Masukkan kode lisensi resmi untuk mengaktifkan instalasi panel pada domain ini.
                </p>

                <!-- Domain Info Badge (Secondary & Compact) -->
                <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gray-100/80 text-[11px] font-medium text-gray-600 border border-gray-200/70">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
                    </svg>
                    <span>Domain: <strong class="text-gray-800 font-semibold">{{ $detectedDomain ?? request()->getHost() }}</strong></span>
                </div>
            </div>

            <!-- Error Banner (Safe User Feedback) -->
            @if ($errors->any())
                <div class="mt-5 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-start gap-2.5" role="alert">
                    <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="flex-1 leading-relaxed">
                        {{ $errors->first() }}
                    </div>
                </div>
            @endif

            <!-- 3. FORM (Left-aligned Inputs, Full Width Button inside Card) -->
            <form action="{{ route('license.activate.submit') }}" method="POST" class="mt-5 space-y-4">
                @csrf

                <!-- License Code Field -->
                <div>
                    <label for="license_code" class="block text-xs font-semibold text-gray-700 mb-1.5 text-left">
                        Kode Lisensi
                    </label>
                    <input id="license_code" 
                           name="license_code" 
                           type="text" 
                           required 
                           autofocus
                           value="{{ old('license_code') }}" 
                           placeholder="Masukkan kode lisensi resmi"
                           class="w-full h-11 px-3.5 rounded-xl border border-gray-200 text-sm font-mono text-gray-900 bg-gray-50/40 focus:bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-brand-primary transition-all">
                </div>

                <!-- Submit Button -->
                <div class="pt-1">
                    <button type="submit" 
                            class="w-full h-11 bg-brand-primary hover:bg-brand-primary-dark active:scale-[0.99] text-white font-semibold text-xs rounded-xl shadow-md shadow-brand-primary/20 hover:shadow-lg hover:shadow-brand-primary/30 transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer">
                        <span>Aktivasi Lisensi</span>
                        <span aria-hidden="true">&rarr;</span>
                    </button>
                </div>
            </form>

            <!-- 4. SUPPORTING SECURITY NOTE (Divider + Safe Description) -->
            <div class="mt-6 pt-4 border-t border-gray-100 text-center">
                <p class="text-[11px] text-gray-400 font-normal leading-relaxed">
                    Kode lisensi diterbitkan oleh pengelola sistem pusat. Hubungi tim teknis jika belum memiliki kode lisensi.
                </p>
            </div>

        </div>

        <!-- 5. MINIMAL FOOTER -->
        <p class="mt-5 text-center text-xs text-gray-400 font-normal">
            {{ $loginBrandName }} &copy; {{ date('Y') }}
        </p>

    </div>

</body>
</html>
