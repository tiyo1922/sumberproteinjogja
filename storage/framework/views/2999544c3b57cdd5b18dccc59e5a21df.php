<?php $__env->startSection('content'); ?>

    <!-- 1. HERO SECTION (Full Viewport 3-Slideshow) -->
    <?php echo $__env->make('components.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- 2. PRODUCT CATEGORIES ("Mau Masak Apa Hari Ini?") -->
    <?php echo $__env->make('components.category-card', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- 3. PRODUCT CATALOG ("Produk Pilihan" with Interactive Tabs) -->
    <?php echo $__env->make('components.product-card', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- 4. WHY US ("Lebih Praktis, Lebih Siap") -->
    <?php echo $__env->make('components.benefit-card', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- 5. KNOWLEDGE & BLOG ("Dapur & Knowledge") -->
    <?php echo $__env->make('components.knowledge-card', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- 6. PRODUCT KNOWLEDGE & QUALITY STANDARDS -->
    <?php echo $__env->make('components.product-knowledge', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- 7. TESTIMONIALS & GOOGLE REVIEWS ("Apa Kata Mereka?") -->
    <?php echo $__env->make('components.review-card', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- 8. LOCATION & STORE INFO (Google Maps & Outlet Yogyakarta) -->
    <?php echo $__env->make('components.location-section', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\sumberproteinjogja\resources\views/landing.blade.php ENDPATH**/ ?>