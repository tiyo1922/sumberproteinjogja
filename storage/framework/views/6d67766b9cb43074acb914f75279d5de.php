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
    <meta property="og:image" content="<?php echo e(asset('images/hero-1.jpg')); ?>">
    <meta property="og:locale" content="id_ID">
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🥩</text></svg>">
    
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    
    <!-- Vite Assets (Tailwind CSS + Alpine.js) -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <style>
        [x-cloak] { display: none !important; }
    </style>

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
    <?php echo $__env->make('components.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Main Content Area -->
    <main id="main-content">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Footer -->
    <?php echo $__env->make('components.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Floating Cart & Confirmation Modal -->
    <?php echo $__env->make('components.floating-cart', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\sumberproteinjogja\resources\views/layouts/app.blade.php ENDPATH**/ ?>