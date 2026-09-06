@php
    try {
        $site = \App\Models\SiteSetting::get('site', config('site', []));
    } catch (\Throwable $e) {
        $site = config('site', []);
    }

    $rawBrandName = trim($site['brand']['name'] ?? 'Sumber Protein Jogja');
    if ($rawBrandName === '') {
        $rawBrandName = 'Sumber Protein Jogja';
    }
    $brandName = $rawBrandName;
    $brandWords = preg_split('/\s+/', $rawBrandName);
    if (count($brandWords) > 1) {
        $brandHighlight = array_pop($brandWords);
        $brandMain = implode(' ', $brandWords);
    } else {
        $brandMain = $rawBrandName;
        $brandHighlight = '';
    }

    $brandTagline = !empty($site['brand']['tagline']) ? $site['brand']['tagline'] : 'Fresh & Frozen Food';

    $rawLogo = trim($site['brand']['logo_url'] ?? '');
    $hasCustomLogo = !empty($rawLogo)
        && !str_contains($rawLogo, 'hero-1.jpg')
        && !str_starts_with($rawLogo, 'blob:')
        && (str_starts_with($rawLogo, 'http') || file_exists(public_path($rawLogo)) || file_exists(public_path('storage/' . ltrim($rawLogo, '/'))));
    $customLogoUrl = $hasCustomLogo
        ? (str_starts_with($rawLogo, 'http') ? $rawLogo : (file_exists(public_path($rawLogo)) ? asset($rawLogo) : asset('storage/' . ltrim($rawLogo, '/'))))
        : null;
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Belum Diaktivasi | {{ $brandName }}</title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-white text-brand-dark font-sans antialiased flex flex-col justify-between selection:bg-brand-soft-green selection:text-brand-primary">

    <!-- Top Minimal Brand Bar -->
    <header class="w-full py-3.5 sm:py-4 px-4 sm:px-8 md:px-10 border-b border-gray-100/80 bg-white/95 backdrop-blur-md">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2.5 sm:gap-3">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-modern bg-brand-soft-green flex items-center justify-center text-brand-primary shrink-0 overflow-hidden">
                    @if($hasCustomLogo)
                        <img src="{{ $customLogoUrl }}" alt="{{ $brandName }}" class="w-full h-full object-contain p-0.5">
                    @else
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2a5 5 0 0 1 5 5v1a5 5 0 0 1-5 5 5 5 0 0 1-5-5V7a5 5 0 0 1 5-5z" fill="currentColor" fill-opacity="0.2"/>
                            <path d="M12 13v9"/>
                            <path d="M7 17l5 5 5-5"/>
                            <circle cx="12" cy="7" r="3"/>
                        </svg>
                    @endif
                </div>
                <div class="flex flex-col">
                    <span class="font-extrabold text-sm sm:text-base md:text-lg tracking-tight text-brand-dark leading-none">
                        {{ $brandMain }} @if($brandHighlight)<span class="text-brand-primary">{{ $brandHighlight }}</span>@endif
                    </span>
                    <span class="text-[10px] sm:text-[11px] text-gray-400 font-medium tracking-wide uppercase mt-1 leading-none">
                        {{ $brandTagline }}
                    </span>
                </div>
            </div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-[11px] font-semibold text-amber-700 border border-amber-200/60">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                <span>Konfigurasi Sistem</span>
            </div>
        </div>
    </header>

    <!-- Center Content -->
    <main class="flex-1 flex items-center justify-center px-4 sm:px-6 py-10 sm:py-14">
        <div class="max-w-md w-full text-center">

            <!-- Graphic Illustration Badge -->
            <div class="relative inline-flex items-center justify-center mb-5 sm:mb-6">
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-brand-soft-green/70 flex items-center justify-center text-brand-primary">
                    <svg class="w-10 h-10 sm:w-12 sm:h-12 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>

            <!-- Main Heading -->
            <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-brand-dark tracking-tight mb-2.5 sm:mb-3">
                WEBSITE BELUM DIAKTIVASI
            </h1>

            <!-- Explanation Copy -->
            <p class="text-xs sm:text-sm text-gray-600 leading-relaxed max-w-sm mx-auto mb-6">
                Situs web ini sedang dalam proses konfigurasi atau aktivasi sistem. Silakan hubungi pengelola website atau kunjungi kembali beberapa saat lagi.
            </p>

            <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200/80 text-[11px] text-gray-500 max-w-xs mx-auto">
                Layanan storefront akan aktif secara otomatis setelah aktivasi diselesaikan oleh administrator.
            </div>

        </div>
    </main>

    <!-- Bottom Minimal Footer -->
    <footer class="w-full py-4 px-6 text-center text-[11px] text-gray-400 border-t border-gray-100">
        <p>&copy; {{ date('Y') }} {{ $brandName }}. Seluruh hak cipta dilindungi.</p>
    </footer>

</body>
</html>