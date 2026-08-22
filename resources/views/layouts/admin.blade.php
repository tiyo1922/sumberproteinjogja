<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F4F6F4]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} — Admin Panel Sumber Protein Jogja</title>
    
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    
    <!-- Local Vite Assets (Tailwind CSS + Alpine.js) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-[#F4F6F4] text-brand-dark font-sans antialiased selection:bg-brand-primary selection:text-white"
      x-data="{ sidebarOpen: false }">

    <!-- ======================================================= -->
    <!-- 1. MOBILE ACCESS BLOCK SCREEN (< 768px Viewport)        -->
    <!-- Strictly blocks mobile admin usage as required          -->
    <!-- ======================================================= -->
    <div class="block md:hidden min-h-screen bg-white p-6 flex flex-col justify-center items-center text-center">
        <div class="max-w-sm mx-auto flex flex-col items-center">
            
            <!-- Computer / Tablet Device Icon -->
            <div class="w-20 h-20 rounded-2xl bg-brand-soft-green border border-brand-soft-green-border flex items-center justify-center text-brand-primary mb-6 shadow-xs">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <rect x="2" y="3" width="20" height="14" rx="2" stroke-linecap="round" stroke-linejoin="round" />
                    <line x1="8" y1="21" x2="16" y2="21" stroke-linecap="round" stroke-linejoin="round" />
                    <line x1="12" y1="17" x2="12" y2="21" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>

            <!-- Header Badge -->
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-brand-soft-green text-brand-primary mb-4 border border-brand-soft-green-border">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-primary"></span>
                <span>Panel Admin</span>
            </span>

            <h1 class="text-xl font-extrabold text-brand-dark tracking-tight mb-3">
                Akses Desktop & Tablet Saja
            </h1>

            <p class="text-xs sm:text-sm text-gray-700 font-medium mb-3 leading-relaxed">
                Panel pengelolaan website dirancang untuk digunakan melalui komputer atau tablet.
            </p>

            <p class="text-xs text-gray-500 mb-8 leading-relaxed">
                Silakan buka panel melalui komputer atau tablet untuk mendapatkan tampilan dan kontrol yang optimal.
            </p>

            <!-- Back to Website CTA -->
            <a href="{{ route('home') }}" 
               class="inline-flex items-center justify-center gap-2 w-full py-3 px-6 rounded-modern font-bold text-xs sm:text-sm text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all duration-200">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Website</span>
            </a>

            <div class="mt-8 pt-6 border-t border-gray-100 w-full text-[11px] text-gray-400">
                Sumber Protein Jogja © {{ date('Y') }}
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 2. ADMIN WORKSPACE (>= 768px: Tablet & Desktop)         -->
    <!-- ======================================================= -->
    <div class="hidden md:flex min-h-screen flex-row w-full relative">

        <!-- Backdrop for Tablet Drawer -->
        <div x-show="sidebarOpen" 
             x-cloak
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black/40 backdrop-blur-xs lg:hidden">
        </div>

        <!-- Sidebar Navigation (FIXED on Desktop with independent scroll, Drawer on Tablet) -->
        <aside class="fixed lg:fixed inset-y-0 left-0 z-50 w-64 lg:w-72 h-screen bg-brand-dark text-white flex flex-col justify-between transition-transform duration-300 ease-in-out shrink-0 shadow-xl lg:shadow-none overflow-y-auto"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            
            <!-- Top Sidebar Area -->
            <div class="flex flex-col flex-1">
                
                <!-- Brand Header -->
                <div class="px-6 pt-3 pb-3.5 flex items-center justify-between border-b border-white/10 shrink-0 sticky top-0 bg-brand-dark z-10">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                        <div class="w-9 h-9 rounded-modern bg-brand-primary flex items-center justify-center text-white shadow-md shadow-brand-primary/30 group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2a5 5 0 0 1 5 5v1a5 5 0 0 1-5 5 5 5 0 0 1-5-5V7a5 5 0 0 1 5-5z" fill="currentColor" fill-opacity="0.2"/>
                                <path d="M12 13v9"/>
                                <path d="M7 17l5 5 5-5"/>
                                <circle cx="12" cy="7" r="3"/>
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-extrabold tracking-tight text-white leading-tight">
                                Sumber Protein
                            </span>
                            <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-1">
                                <span>CMS Panel</span>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            </span>
                        </div>
                    </a>

                    <!-- Close button for tablet drawer -->
                    <button @click="sidebarOpen = false" 
                            type="button"
                            aria-label="Tutup menu sidebar"
                            class="lg:hidden p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-white/10 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links (LOCKED FINAL STRUCTURE) -->
                <nav class="p-4 space-y-6 flex-1 text-xs">
                    
                    <!-- Dashboard Main -->
                    <div class="space-y-1">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-modern font-semibold transition-all duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-brand-primary text-white shadow-xs font-bold' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                <rect x="14" y="14" width="7" height="7" rx="1.5" />
                                <rect x="3" y="14" width="7" height="7" rx="1.5" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </div>

                    <!-- KONTEN LANDING PAGE Group -->
                    <div class="space-y-1">
                        <div class="px-3.5 pb-2 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            Konten Landing Page
                        </div>

                        <!-- 1. Hero Slider -->
                        <a href="{{ route('admin.hero') }}" 
                           class="flex items-center justify-between px-3.5 py-2 rounded-modern font-medium transition-all {{ request()->routeIs('admin.hero') ? 'bg-brand-primary text-white font-bold shadow-xs' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Hero Slider</span>
                            </div>
                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-white/10 text-gray-300">3 Draft</span>
                        </a>

                        <!-- 2. Kategori Produk -->
                        <a href="{{ route('admin.kategori') }}" 
                           class="flex items-center justify-between px-3.5 py-2 rounded-modern font-medium transition-all {{ request()->routeIs('admin.kategori') ? 'bg-brand-primary text-white font-bold shadow-xs' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <span>Kategori Produk</span>
                            </div>
                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-white/10 text-gray-300">6 Kategori</span>
                        </a>

                        <!-- 3. Katalog Produk -->
                        <a href="{{ route('admin.produk') }}" 
                           class="flex items-center justify-between px-3.5 py-2 rounded-modern font-medium transition-all {{ request()->routeIs('admin.produk') ? 'bg-brand-primary text-white font-bold shadow-xs' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span>Katalog Produk</span>
                            </div>
                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-white/10 text-gray-300">24</span>
                        </a>

                        <!-- 4. Keunggulan & Mutu (Kenapa Memilih Kami & Standar Mutu) -->
                        <a href="{{ route('admin.keunggulan') }}" 
                           class="flex items-center justify-between px-3.5 py-2 rounded-modern font-medium transition-all {{ request()->routeIs('admin.keunggulan') ? 'bg-brand-primary text-white font-bold shadow-xs' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>Keunggulan & Mutu</span>
                            </div>
                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-white/10 text-gray-300">2 Section</span>
                        </a>

                        <!-- 5. Knowledge & Tips -->
                        <a href="{{ route('admin.knowledge') }}" 
                           class="flex items-center justify-between px-3.5 py-2 rounded-modern font-medium transition-all {{ request()->routeIs('admin.knowledge') ? 'bg-brand-primary text-white font-bold shadow-xs' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span>Knowledge & Tips</span>
                            </div>
                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-white/10 text-gray-300">18</span>
                        </a>
                    </div>

                    <!-- PENGATURAN Group -->
                    <div class="space-y-1">
                        <div class="px-3.5 pb-2 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                            Pengaturan
                        </div>

                        <!-- 6. Footer (Google Reviews & Lokasi Maps) -->
                        <a href="{{ route('admin.footer') }}" 
                           class="flex items-center justify-between px-3.5 py-2 rounded-modern font-medium transition-all {{ request()->routeIs('admin.footer') ? 'bg-brand-primary text-white font-bold shadow-xs' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span>Footer</span>
                            </div>
                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-white/10 text-gray-300">3 Section</span>
                        </a>

                        <!-- 7. SEO & Meta -->
                        <a href="{{ route('admin.seo') }}" 
                           class="flex items-center gap-3 px-3.5 py-2 rounded-modern font-medium transition-all {{ request()->routeIs('admin.seo') ? 'bg-brand-primary text-white font-bold shadow-xs' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span>SEO & Meta</span>
                        </a>

                        <!-- 8. Site & Contact Settings -->
                        <a href="{{ route('admin.settings') }}" 
                           class="flex items-center justify-between px-3.5 py-2 rounded-modern font-medium transition-all {{ request()->routeIs('admin.settings') ? 'bg-brand-primary text-white font-bold shadow-xs' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <span>Site & Contact</span>
                            </div>
                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300">WA</span>
                        </a>
                    </div>

                </nav>
            </div>

            <!-- Bottom Sidebar Area: Admin Profile -->
            <div class="p-4 border-t border-white/10 bg-black/20 shrink-0 sticky bottom-0 z-10">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-full bg-brand-primary text-white flex items-center justify-center text-xs font-bold shrink-0 ring-2 ring-emerald-400/40">
                            SP
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-bold text-white truncate leading-tight">Admin SP Jogja</span>
                            <span class="text-[10px] text-gray-400 truncate">Super Admin</span>
                        </div>
                    </div>
                    
                    <!-- Dummy Logout -->
                    <a href="{{ route('home') }}" 
                       title="Keluar ke Website" 
                       class="p-1.5 text-gray-400 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </a>
                </div>
            </div>

        </aside>

        <!-- Main Content Area (Offset by Sidebar width on desktop with independent scroll) -->
        <div class="flex-1 flex flex-col min-w-0 lg:pl-72">
            
            <!-- Topbar (Sticky Header) -->
            <header class="h-18 bg-white border-b border-gray-200/80 px-6 lg:px-8 flex items-center justify-between sticky top-0 z-30 shadow-2xs">
                
                <!-- Left: Hamburger (Tablet) & Breadcrumb -->
                <div class="flex items-center gap-4">
                    <!-- Hamburger Button on Tablet (< 1024px) -->
                    <button @click="sidebarOpen = !sidebarOpen" 
                            type="button"
                            aria-label="Toggle navigasi sidebar"
                            class="lg:hidden p-2 rounded-modern border border-gray-200 text-gray-600 hover:text-brand-dark hover:bg-gray-50 focus:outline-none transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="flex flex-col">
                        <h1 class="text-base sm:text-lg font-extrabold text-brand-dark tracking-tight leading-tight">
                            {{ $pageTitle ?? 'Dashboard' }}
                        </h1>
                        <div class="flex items-center gap-1.5 text-[11px] text-gray-500 font-medium">
                            <span>Admin</span>
                            <span>/</span>
                            <span class="text-brand-primary font-semibold">{{ $pageTitle ?? 'Dashboard' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Right: External Link & Quick Profile -->
                <div class="flex items-center gap-3 sm:gap-4">
                    
                    <!-- View Website Link -->
                    <a href="{{ route('home') }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-modern text-xs font-semibold text-brand-primary bg-brand-soft-green hover:bg-emerald-100/80 border border-brand-soft-green-border transition-colors">
                        <span>Lihat Website</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>

                    <!-- Status Pill -->
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1 rounded-full bg-gray-100 text-[11px] font-medium text-gray-600 border border-gray-200/60">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Mode CMS Aktif</span>
                    </div>

                    <!-- Profile Avatar Icon -->
                    <div class="w-8 h-8 rounded-full bg-brand-dark text-white flex items-center justify-center text-xs font-bold shadow-2xs">
                        SP
                    </div>

                </div>

            </header>

            <!-- Page Main Content Body (Independent Scroll) -->
            <main class="flex-1 p-6 lg:p-8 max-w-7xl w-full mx-auto">
                @yield('content')
            </main>

            <!-- Admin Footer -->
            <footer class="px-6 lg:px-8 py-4 border-t border-gray-200/80 bg-white text-xs text-gray-400 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>
                    Sumber Protein Jogja — <span class="font-semibold text-gray-600">Content Management System v1.0</span>
                </div>
                <div class="text-[11px] text-gray-400">
                    Layout Locked • Content Flexible
                </div>
            </footer>

        </div>

    </div>

</body>
</html>
