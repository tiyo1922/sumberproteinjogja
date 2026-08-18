@extends('layouts.admin', [
    'title' => 'Dashboard',
    'pageTitle' => 'Dashboard Overview'
])

@section('content')
<div class="space-y-8">
    
    <!-- 1. Header Banner -->
    <div class="bg-gradient-to-r from-brand-dark via-brand-dark-soft to-brand-primary rounded-modern-xl p-6 sm:p-8 text-white relative overflow-hidden shadow-sm">
        <div class="relative z-10 max-w-2xl">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-emerald-300 backdrop-blur-xs mb-3 border border-white/10">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Content Management System</span>
            </span>
            <h2 class="text-xl sm:text-3xl font-extrabold tracking-tight text-white mb-2 leading-tight">
                Selamat datang di Sumber Protein CMS 👋
            </h2>
            <p class="text-xs sm:text-sm text-gray-200 leading-relaxed font-normal">
                Kelola konten landing page dari satu tempat secara fleksibel dengan layout visual yang tetap terjaga.
            </p>
        </div>

        <!-- Decorative background shape -->
        <div class="absolute -right-8 -bottom-10 w-64 h-64 rounded-full bg-emerald-500/10 blur-2xl pointer-events-none"></div>
    </div>

    <!-- 2. Content Summary KPI Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        
        <!-- Card 1: Hero Aktif -->
        <a href="{{ route('admin.hero') }}" class="group bg-white p-5 rounded-modern-lg border border-gray-200/80 shadow-2xs hover:shadow-card hover:border-brand-primary/40 transition-all duration-200 flex items-center justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Hero Aktif</p>
                </div>
                <p class="text-2xl lg:text-3xl font-extrabold text-brand-dark group-hover:text-brand-primary transition-colors">
                    {{ $stats['hero_active_count'] }} <span class="text-xs font-normal text-gray-400">/ 3 Draft</span>
                </p>
                <p class="text-[11px] text-emerald-700 font-medium">● Hero Draft 01 Aktif</p>
            </div>
            <div class="w-12 h-12 rounded-modern bg-sky-50 text-sky-700 flex items-center justify-center group-hover:scale-105 transition-transform shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </a>

        <!-- Card 2: Kategori Produk -->
        <a href="{{ route('admin.kategori') }}" class="group bg-white p-5 rounded-modern-lg border border-gray-200/80 shadow-2xs hover:shadow-card hover:border-brand-primary/40 transition-all duration-200 flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kategori Produk</p>
                <p class="text-2xl lg:text-3xl font-extrabold text-brand-dark group-hover:text-brand-primary transition-colors">
                    {{ $stats['categories_count'] }}
                </p>
                <p class="text-[11px] text-gray-400">1 Sistem + 5 Kategori</p>
            </div>
            <div class="w-12 h-12 rounded-modern bg-purple-50 text-purple-700 flex items-center justify-center group-hover:scale-105 transition-transform shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
        </a>

        <!-- Card 3: Produk Aktif -->
        <a href="{{ route('admin.produk') }}" class="group bg-white p-5 rounded-modern-lg border border-gray-200/80 shadow-2xs hover:shadow-card hover:border-brand-primary/40 transition-all duration-200 flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Produk Aktif</p>
                <p class="text-2xl lg:text-3xl font-extrabold text-brand-dark group-hover:text-brand-primary transition-colors">
                    {{ $stats['products_active_count'] }}
                </p>
                <p class="text-[11px] text-gray-400">Daging, Ayam, Ikan, Sayur</p>
            </div>
            <div class="w-12 h-12 rounded-modern bg-brand-soft-green text-brand-primary flex items-center justify-center group-hover:scale-105 transition-transform shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
        </a>

        <!-- Card 4: Artikel Knowledge -->
        <a href="{{ route('admin.knowledge') }}" class="group bg-white p-5 rounded-modern-lg border border-gray-200/80 shadow-2xs hover:shadow-card hover:border-brand-primary/40 transition-all duration-200 flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Artikel Knowledge</p>
                <p class="text-2xl lg:text-3xl font-extrabold text-brand-dark group-hover:text-brand-primary transition-colors">
                    {{ $stats['knowledge_count'] }}
                </p>
                <p class="text-[11px] text-gray-400">15 Published • 3 Draft</p>
            </div>
            <div class="w-12 h-12 rounded-modern bg-amber-50 text-amber-700 flex items-center justify-center group-hover:scale-105 transition-transform shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
        </a>

    </div>

    <!-- 3. Middle Row: Quick Overview Cards & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Status Landing Page Overview (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-extrabold text-brand-dark">Status Landing Page</h3>
                        <p class="text-xs text-gray-500">Kondisi publikasi section utama pada halaman customer</p>
                    </div>
                    <a href="{{ route('home') }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-primary hover:text-brand-primary-dark transition-colors">
                        <span>Lihat Website</span>
                        <span>↗</span>
                    </a>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach($landingStatus as $item)
                    <div class="py-3 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0 shadow-2xs"></div>
                            <div>
                                <h4 class="text-xs font-bold text-brand-dark">{{ $item['section'] }}</h4>
                                <p class="text-[11px] text-gray-500">{{ $item['detail'] }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $item['badge'] }}">
                                {{ $item['status'] }}
                            </span>
                            <a href="{{ route($item['route']) }}" class="text-xs font-semibold text-gray-400 hover:text-brand-primary transition-colors">
                                Kelola →
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <span>Seluruh section landing page beroperasi normal</span>
                <span class="text-emerald-700 font-semibold">● Live di https://sumberproteinjogja.com</span>
            </div>
        </div>

        <!-- Right: Quick Actions & Hero Summary (1 col) -->
        <div class="space-y-6">
            
            <!-- Quick Actions -->
            <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs">
                <div class="pb-3 mb-4 border-b border-gray-100">
                    <h3 class="text-base font-extrabold text-brand-dark">Aksi Cepat</h3>
                    <p class="text-xs text-gray-500">Pintasan navigasi untuk update konten</p>
                </div>

                <div class="space-y-2.5">
                    <a href="{{ route('admin.hero') }}" 
                       class="w-full flex items-center justify-between px-4 py-3 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark shadow-2xs transition-all">
                        <div class="flex items-center gap-2">
                            <span>✏️</span>
                            <span>Edit Hero Slider</span>
                        </div>
                        <span class="text-[11px] font-normal text-emerald-200">1 Aktif</span>
                    </a>

                    <a href="{{ route('admin.produk') }}" 
                       class="w-full flex items-center justify-between px-4 py-3 rounded-modern font-bold text-xs text-brand-primary bg-brand-soft-green hover:bg-emerald-100/70 border border-brand-soft-green-border transition-all">
                        <div class="flex items-center gap-2">
                            <span class="text-sm leading-none">＋</span>
                            <span>Tambah Produk</span>
                        </div>
                        <span class="text-[11px] font-semibold text-brand-primary">24 Item</span>
                    </a>

                    <a href="{{ route('admin.knowledge') }}" 
                       class="w-full flex items-center justify-between px-4 py-3 rounded-modern font-bold text-xs text-brand-dark bg-gray-50 hover:bg-gray-100 border border-gray-200 transition-all">
                        <div class="flex items-center gap-2">
                            <span class="text-sm leading-none">＋</span>
                            <span>Tambah Artikel</span>
                        </div>
                        <span class="text-[11px] font-medium text-gray-500">18 Artikel</span>
                    </a>
                </div>
            </div>

            <!-- Hero Quick Overview Card -->
            <div class="bg-brand-dark text-white rounded-modern-xl p-5 shadow-2xs border border-white/10 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-400">Hero Aktif Saat Ini</span>
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">● AKTIF</span>
                </div>
                <div>
                    <h4 class="text-sm font-extrabold text-white">{{ $heroOverview['name'] }}</h4>
                    <p class="text-xs text-gray-300 mt-0.5">"{{ $heroOverview['headline'] }}"</p>
                </div>
                <div class="pt-2 border-t border-white/10 flex items-center justify-between text-[11px] text-gray-400">
                    <span>{{ $heroOverview['images_count'] }} Slideshow Foto</span>
                    <a href="{{ route('admin.hero') }}" class="text-emerald-400 hover:text-emerald-300 font-bold">Edit →</a>
                </div>
            </div>

        </div>

    </div>

    <!-- 4. Bottom Row: Histori Pembaruan Terakhir -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs">
        <div class="pb-4 mb-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold text-brand-dark">Aktivitas Pembaruan Terakhir</h3>
                <p class="text-xs text-gray-500">Histori 5 aktivitas modifikasi konten terbaru pada sistem</p>
            </div>
            <span class="text-xs font-semibold text-gray-400">Log Aktivitas</span>
        </div>

        <div class="divide-y divide-gray-100">
            @foreach($recentUpdates as $up)
            <div class="py-3 flex items-start sm:items-center justify-between gap-3">
                <div class="space-y-1">
                    <h4 class="text-xs font-bold text-brand-dark hover:text-brand-primary transition-colors">
                        {{ $up['title'] }}
                    </h4>
                    <div class="flex items-center gap-2">
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold border {{ $up['badge_type'] }}">
                            {{ $up['type'] }}
                        </span>
                        <span class="text-[11px] text-gray-400">• oleh {{ $up['author'] }}</span>
                    </div>
                </div>
                <span class="text-[11px] font-medium text-gray-400 whitespace-nowrap shrink-0">
                    {{ $up['time'] }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
