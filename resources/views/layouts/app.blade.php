@php
    $seo = $seo ?? config('seo');
    $location = $location ?? config('location');
    $site = $site ?? config('site');
    $ogImageUrl = (!empty($seo['og_image']) && (str_starts_with($seo['og_image'], 'http') || str_starts_with($seo['og_image'], 'blob:'))) 
        ? $seo['og_image'] 
        : asset($seo['og_image'] ?? 'images/hero-1.jpg');

    // Build structured Schema.org opening hours array from location config days
    $schemaOpeningHours = [];
    $dayNameMap = [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday'
    ];
    if (!empty($location['operational_hours']['days'])) {
        foreach ($location['operational_hours']['days'] as $dayKey => $hours) {
            $schemaOpeningHours[] = [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => $dayNameMap[strtolower($dayKey)] ?? ucfirst($dayKey),
                'opens' => $hours['open'] ?? '07:00',
                'closes' => $hours['close'] ?? '19:00',
            ];
        }
    }

    $schemaSameAs = array_values(array_filter([
        $site['social']['instagram'] ?? null,
        $site['social']['tiktok'] ?? null,
        $site['social']['facebook'] ?? null,
    ]));

    $schemaLocalBusiness = [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $location['outlet']['name'] ?? ($site['brand']['name'] ?? 'Sumber Protein Jogja'),
        'image' => $ogImageUrl,
        'telephone' => $site['contact']['phone'] ?? '+62 812-3456-7890',
        'email' => $site['contact']['email'] ?? 'halo@sumberproteinjogja.id',
        'url' => $site['website']['url'] ?? 'https://sumberproteinjogja.com',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $location['address']['street'] ?? 'Jl. Kaliurang Km. 8.5 No. 42',
            'addressLocality' => $location['address']['district'] ?? 'Ngaglik',
            'addressRegion' => $location['address']['province'] ?? 'D.I. Yogyakarta',
            'postalCode' => $location['address']['postal_code'] ?? '55581',
            'addressCountry' => $location['address']['country_code'] ?? 'ID',
        ],
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => (float) ($location['coordinates']['latitude'] ?? -7.748906392269989),
            'longitude' => (float) ($location['coordinates']['longitude'] ?? 110.38623737593888),
        ],
        'openingHoursSpecification' => $schemaOpeningHours,
        'sameAs' => $schemaSameAs,
        'priceRange' => 'Rp 14.000 - Rp 495.000',
    ];
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <!-- Primary SEO Meta Tags (Single Source of Truth) -->
    <title>{{ $seo['meta_title'] ?? 'Sumber Protein Jogja - Bahan Masak Siap Olah, Frozen & Segar Yogyakarta' }}</title>
    <meta name="description" content="{{ $seo['meta_description'] ?? '' }}">
    <meta name="keywords" content="{{ $seo['meta_keywords'] ?? '' }}">
    <meta name="author" content="{{ $seo['author'] ?? ($site['brand']['name'] ?? 'Sumber Protein Jogja') }}">
    <meta name="robots" content="{{ $seo['robots'] ?? 'index, follow' }}">
    <link rel="canonical" href="{{ $seo['canonical_url'] ?? url()->current() }}">
    @if(!empty($seo['google']['site_verification']))
    <meta name="google-site-verification" content="{{ $seo['google']['site_verification'] }}">
    @endif
    
    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $seo['og_title'] ?? $seo['meta_title'] }}">
    <meta property="og:description" content="{{ $seo['og_description'] ?? $seo['meta_description'] }}">
    <meta property="og:url" content="{{ $seo['canonical_url'] ?? url()->current() }}">
    <meta property="og:image" content="{{ $ogImageUrl }}">
    <meta property="og:locale" content="id_ID">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="{{ $seo['twitter_card'] ?? 'summary_large_image' }}">
    <meta name="twitter:title" content="{{ $seo['og_title'] ?? $seo['meta_title'] }}">
    <meta name="twitter:description" content="{{ $seo['og_description'] ?? $seo['meta_description'] }}">
    <meta name="twitter:image" content="{{ $ogImageUrl }}">
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🥩</text></svg>">
    
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    
    <!-- Vite Assets (Tailwind CSS + Alpine.js) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <!-- Schema.org JSON-LD (Single Source of Truth) -->
    <script type="application/ld+json">
    {!! json_encode($schemaLocalBusiness, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <!-- Google Analytics 4 (GA4) -->
    @if(!empty($seo['google']['ga4_measurement_id']))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $seo['google']['ga4_measurement_id'] }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', '{{ $seo['google']['ga4_measurement_id'] }}');
    </script>
    @endif
</head>
<body class="bg-white text-brand-dark antialiased overflow-x-hidden selection:bg-brand-soft-green selection:text-brand-primary" x-data="{ mobileMenuOpen: false, scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)">

    <!-- Navigation Bar -->
    @include('components.navbar')

    <!-- Main Content Area -->
    <main id="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('components.footer')

    <!-- Floating Cart & Confirmation Modal -->
    @include('components.floating-cart')

</body>
</html>
