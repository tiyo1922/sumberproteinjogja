<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <!-- SEO Meta Tags -->
    <title>Sumber Protein Jogja - Bahan Masak Siap Olah, Frozen & Segar Yogyakarta</title>
    <meta name="description" content="Penyedia daging sapi, ayam, ikan, dan sayuran pilihan dalam bentuk frozen, ready to cook, plain, dan berbumbu di Yogyakarta. Melayani kebutuhan rumah tangga & pembelian curah.">
    <meta name="keywords" content="sumber protein jogja, frozen food jogja, daging sapi slice jogja, ayam ungkep jogja, salmon jogja, supplier horeka jogja, ready to cook jogja">
    <meta name="author" content="Sumber Protein Jogja">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Sumber Protein Jogja - Bahan Masak Siap Olah, Tinggal Masak.">
    <meta property="og:description" content="Daging, ayam, ikan, dan sayuran pilihan dalam bentuk frozen dan ready to cook untuk kebutuhan rumah tangga maupun pembelian curah di Yogyakarta.">
    <meta property="og:image" content="{{ asset('images/hero-1.jpg') }}">
    <meta property="og:locale" content="id_ID">
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🥩</text></svg>">
    
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS with Custom Brand Config -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            primary: '#1F6B45',      // Primary Green
                            'primary-dark': '#165034', // Darker Green
                            'primary-light': '#2a8b5b',
                            dark: '#17231D',         // Dark for headings & dark sections
                            'dark-soft': '#22322a',
                            cream: '#F7F5EF',        // Warm Cream background
                            'cream-light': '#FCFBF8',
                            white: '#FFFFFF',
                            accent: '#E7A93B',       // Accent Gold
                            'accent-hover': '#cf942e',
                            'soft-green': '#EAF3ED', // Soft green pill
                            'soft-green-border': '#c8e0d0',
                        }
                    },
                    fontFamily: {
                        sans: ['Poppins', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    borderRadius: {
                        'modern-sm': '10px',
                        'modern': '14px',
                        'modern-lg': '18px',
                        'modern-xl': '24px',
                    },
                    boxShadow: {
                        'subtle': '0 2px 10px rgba(23, 35, 29, 0.04)',
                        'card': '0 8px 24px -4px rgba(23, 35, 29, 0.06), 0 2px 6px -2px rgba(23, 35, 29, 0.04)',
                        'card-hover': '0 20px 32px -8px rgba(23, 35, 29, 0.12), 0 8px 16px -4px rgba(31, 107, 69, 0.08)',
                        'floating': '0 12px 36px rgba(31, 107, 69, 0.3)',
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #FFFFFF;
            color: #17231D;
        }
        
        .glass-nav {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        
        .glass-nav-scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 4px 20px -2px rgba(23, 35, 29, 0.08);
        }

        .badge-frozen {
            background-color: #f0f9ff;
            color: #0369a1;
            border: 1px solid rgba(186, 230, 253, 0.8);
        }
        
        .badge-ready {
            background-color: #fffbeb;
            color: #92400e;
            border: 1px solid rgba(253, 230, 138, 0.8);
        }
        
        .badge-curah {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid rgba(167, 243, 208, 0.8);
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .snap-x {
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
        }
        .snap-center {
            scroll-snap-align: center;
        }
    </style>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.7/dist/cdn.min.js"></script>

    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "LocalBusiness",
      "name": "Sumber Protein Jogja",
      "image": "images/hero-1.jpg",
      "telephone": "+6281234567890",
      "email": "halo@sumberproteinjogja.id",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Jl. Kaliurang Km. 8.5 No. 42, Sinduharjo, Ngaglik",
        "addressLocality": "Sleman",
        "addressRegion": "D.I. Yogyakarta",
        "postalCode": "55581",
        "addressCountry": "ID"
      },
      "openingHoursSpecification": {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
        "opens": "07:00",
        "closes": "19:00"
      },
      "priceRange": "Rp 14.000 - Rp 495.000"
    }
    </script>
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
