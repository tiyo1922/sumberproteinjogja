@extends('layouts.app')

@section('content')

    <!-- 1. HERO SECTION (Full Viewport 3-Slideshow) -->
    @include('components.hero')

    <!-- 2. PRODUCT CATEGORIES ("Mau Masak Apa Hari Ini?") -->
    @include('components.category-card')

    <!-- 3. PRODUCT CATALOG ("Produk Pilihan" with Interactive Tabs) -->
    @include('components.product-card')

    <!-- 4. WHY US ("Lebih Praktis, Lebih Siap") -->
    @include('components.benefit-card')

    <!-- 5. KNOWLEDGE & BLOG ("Dapur & Knowledge") -->
    @include('components.knowledge-card')

    <!-- 6. PRODUCT KNOWLEDGE & QUALITY STANDARDS -->
    @include('components.product-knowledge')

    <!-- 7. TESTIMONIALS & GOOGLE REVIEWS ("Apa Kata Mereka?") -->
    @include('components.review-card')

    <!-- 8. LOCATION & STORE INFO (Google Maps & Outlet Yogyakarta) -->
    @include('components.location-section')

@endsection
