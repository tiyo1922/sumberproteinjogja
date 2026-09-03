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
    <title>404 - Halaman Tidak Ditemukan | {{ $brandName }}</title>
    <meta name="robots" content="noindex, follow">

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
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 sm:gap-3 group focus:outline-none focus:ring-2 focus:ring-brand-primary/30 rounded-lg p-1">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-modern bg-brand-soft-green flex items-center justify-center text-brand-primary group-hover:scale-105 transition-transform duration-200 shrink-0 overflow-hidden">
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
                    <span class="font-extrabold text-sm sm:text-base md:text-lg tracking-tight text-brand-dark leading-none group-hover:text-brand-primary transition-colors">
                        {{ $brandMain }} @if($brandHighlight)<span class="text-brand-primary">{{ $brandHighlight }}</span>@endif
                    </span>
                    <span class="text-[10px] sm:text-[11px] text-gray-400 font-medium tracking-wide uppercase mt-1 leading-none">
                        {{ $brandTagline }}
                    </span>
                </div>
            </a>
            <a href="{{ url('/') }}" class="text-xs sm:text-sm font-semibold text-brand-primary-light hover:text-brand-primary transition-colors flex items-center gap-1 px-3 py-1.5 rounded-modern hover:bg-brand-soft-green/50">
                <span>Beranda</span>
                <span>→</span>
            </a>
        </div>
    </header>

    <!-- Center Content -->
    <main class="flex-1 flex items-center justify-center px-4 sm:px-6 py-10 sm:py-14">
        <div class="max-w-md w-full text-center">

            <!-- Graphic Illustration Badge -->
            <div class="relative inline-flex items-center justify-center mb-5 sm:mb-6">
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-brand-soft-green/70 flex items-center justify-center text-brand-primary">
                    <svg class="w-10 h-10 sm:w-12 sm:h-12 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="absolute -top-1 -right-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-brand-accent text-white shadow-sm tracking-wide">
                    404
                </span>
            </div>

            <!-- Error Heading -->
            <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-brand-dark tracking-tight mb-2.5 sm:mb-3">
                Halaman Tidak Ditemukan
            </h1>

            <!-- Explanation Copy -->
            <p class="text-xs sm:text-sm text-gray-600 leading-relaxed max-w-sm mx-auto mb-6 sm:mb-8">
                Maaf, halaman yang kamu cari tidak tersedia atau mungkin tautannya sudah dipindahkan.
            </p>

            <!-- Call to Actions -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-2.5 max-w-md mx-auto">
                <a href="{{ url('/') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-3.5 sm:px-4 py-2 rounded-modern font-semibold text-[11px] sm:text-xs text-white bg-brand-primary hover:bg-brand-primary-dark active:scale-[0.98] transition-all duration-200 shadow-sm hover:shadow focus:outline-none focus:ring-2 focus:ring-brand-primary/40 whitespace-nowrap shrink-0">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="whitespace-nowrap">Kembali ke Beranda</span>
                </a>

                <a href="{{ url('/produk') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-3.5 sm:px-4 py-2 rounded-modern font-semibold text-[11px] sm:text-xs text-brand-dark bg-white hover:bg-brand-cream active:scale-[0.98] transition-all duration-200 border border-gray-200 hover:border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-primary/20 whitespace-nowrap shrink-0">
                    <svg class="w-3.5 h-3.5 text-brand-primary-light shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span class="whitespace-nowrap">Lihat Katalog Produk</span>
                </a>
            </div>

        </div>
    </main>

    <!-- Bottom Minimal Footer -->
    <footer class="w-full py-4 px-6 text-center text-[11px] text-gray-400 border-t border-gray-100">
        <p>&copy; {{ date('Y') }} {{ $brandName }}. Seluruh hak cipta dilindungi.</p>
    </footer>

</body>
</html>
