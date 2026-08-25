@php
    $site = $site ?? config('site');
    $cleanAdminWa = preg_replace('/[^0-9]/', '', $site['contact']['admin_whatsapp'] ?? '6281234567890');
    $brandName = $site['brand']['name'] ?? 'Sumber Protein Jogja';
@endphp
<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
        :class="scrolled ? 'glass-nav-scrolled py-3' : 'glass-nav py-4 border-b border-gray-100/60'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            
            <!-- Brand Logo -->
            <a href="#hero" class="flex items-center gap-3 group focus:outline-none focus:ring-2 focus:ring-brand-primary/30 rounded-lg p-1">
                <div class="w-10 h-10 rounded-modern bg-brand-primary flex items-center justify-center text-white shadow-md shadow-brand-primary/20 group-hover:scale-105 transition-transform duration-200">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <!-- Custom Fresh Protein Icon (Meat & Fresh Leaf) -->
                        <path d="M12 2a5 5 0 0 1 5 5v1a5 5 0 0 1-5 5 5 5 0 0 1-5-5V7a5 5 0 0 1 5-5z" fill="currentColor" fill-opacity="0.2"/>
                        <path d="M12 13v9"/>
                        <path d="M7 17l5 5 5-5"/>
                        <circle cx="12" cy="7" r="3"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-lg sm:text-xl font-extrabold tracking-tight text-brand-dark leading-none group-hover:text-brand-primary transition-colors">
                        {{ $site['brand']['short_name'] ?? 'Sumber Protein' }} <span class="text-brand-primary">Jogja</span>
                    </span>
                    <span class="text-[11px] font-medium text-gray-500 tracking-wider uppercase mt-0.5">
                        Fresh & Frozen Food
                    </span>
                </div>
            </a>

            <!-- Desktop Horizontal Navigation -->
            <nav class="hidden md:flex items-center gap-8" aria-label="Navigasi Utama">
                <a href="#kategori" class="text-sm font-semibold text-brand-dark hover:text-brand-primary transition-colors py-1 relative after:absolute after:bottom-0 after:left-0 after:w-0 hover:after:w-full after:h-0.5 after:bg-brand-primary after:transition-all">
                    Kategori
                </a>
                <a href="#produk" class="text-sm font-semibold text-brand-dark hover:text-brand-primary transition-colors py-1 relative after:absolute after:bottom-0 after:left-0 after:w-0 hover:after:w-full after:h-0.5 after:bg-brand-primary after:transition-all">
                    Produk Pilihan
                </a>
                <a href="#keunggulan" class="text-sm font-semibold text-brand-dark hover:text-brand-primary transition-colors py-1 relative after:absolute after:bottom-0 after:left-0 after:w-0 hover:after:w-full after:h-0.5 after:bg-brand-primary after:transition-all">
                    Keunggulan
                </a>
                <a href="#knowledge" class="text-sm font-semibold text-brand-dark hover:text-brand-primary transition-colors py-1 relative after:absolute after:bottom-0 after:left-0 after:w-0 hover:after:w-full after:h-0.5 after:bg-brand-primary after:transition-all">
                    Knowledge
                </a>
                <a href="#tentang" class="text-sm font-semibold text-brand-dark hover:text-brand-primary transition-colors py-1 relative after:absolute after:bottom-0 after:left-0 after:w-0 hover:after:w-full after:h-0.5 after:bg-brand-primary after:transition-all">
                    Tentang Kami
                </a>
                <a href="#lokasi" class="text-sm font-semibold text-brand-dark hover:text-brand-primary transition-colors py-1 relative after:absolute after:bottom-0 after:left-0 after:w-0 hover:after:w-full after:h-0.5 after:bg-brand-primary after:transition-all">
                    Lokasi Toko
                </a>
            </nav>

            <!-- Desktop CTA WhatsApp Button -->
            <div class="hidden md:flex items-center gap-3">
                <a href="https://wa.me/{{ $cleanAdminWa }}?text=Halo%20{{ urlencode($brandName) }},%20saya%20ingin%20tanya%20produk%20dan%20pemesanan" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern text-sm font-semibold text-white bg-brand-primary hover:bg-brand-primary-dark transition-all duration-200 shadow-md shadow-brand-primary/20 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-brand-primary/40 active:scale-95">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                    <span>Hubungi WA</span>
                </a>
            </div>

            <!-- Mobile Hamburger Button -->
            <div class="flex items-center gap-2 md:hidden">
                <a href="https://wa.me/{{ $cleanAdminWa }}?text=Halo%20{{ urlencode($brandName) }}" 
                   target="_blank"
                   class="p-2 rounded-modern bg-brand-soft-green text-brand-primary hover:bg-brand-primary hover:text-white transition-colors"
                   aria-label="Pesan via WhatsApp">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                    </svg>
                </a>
                
                <button @click="mobileMenuOpen = !mobileMenuOpen" 
                        type="button" 
                        class="p-2.5 rounded-modern text-brand-dark hover:text-brand-primary hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 transition-colors"
                        aria-expanded="false"
                        :aria-expanded="mobileMenuOpen"
                        aria-label="Buka Menu Navigasi">
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer Navigation -->
    <div x-show="mobileMenuOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         @click.away="mobileMenuOpen = false"
         @keydown.escape.window="mobileMenuOpen = false"
         class="md:hidden bg-white/95 backdrop-blur-md border-b border-gray-200 shadow-xl px-4 pt-3 pb-6 space-y-3 mt-3">
        <div class="flex flex-col space-y-1">
            <a @click="mobileMenuOpen = false" href="#kategori" class="px-4 py-3 rounded-modern font-semibold text-brand-dark hover:bg-brand-soft-green hover:text-brand-primary transition-colors flex items-center justify-between">
                <span>Kategori Bahan Masak</span>
                <span class="text-xs text-gray-400">→</span>
            </a>
            <a @click="mobileMenuOpen = false" href="#produk" class="px-4 py-3 rounded-modern font-semibold text-brand-dark hover:bg-brand-soft-green hover:text-brand-primary transition-colors flex items-center justify-between">
                <span>Produk Pilihan</span>
                <span class="text-xs text-gray-400">→</span>
            </a>
            <a @click="mobileMenuOpen = false" href="#keunggulan" class="px-4 py-3 rounded-modern font-semibold text-brand-dark hover:bg-brand-soft-green hover:text-brand-primary transition-colors flex items-center justify-between">
                <span>Keunggulan Kami</span>
                <span class="text-xs text-gray-400">→</span>
            </a>
            <a @click="mobileMenuOpen = false" href="#knowledge" class="px-4 py-3 rounded-modern font-semibold text-brand-dark hover:bg-brand-soft-green hover:text-brand-primary transition-colors flex items-center justify-between">
                <span>Dapur & Knowledge</span>
                <span class="text-xs text-gray-400">→</span>
            </a>
            <a @click="mobileMenuOpen = false" href="#tentang" class="px-4 py-3 rounded-modern font-semibold text-brand-dark hover:bg-brand-soft-green hover:text-brand-primary transition-colors flex items-center justify-between">
                <span>Tentang Kami</span>
                <span class="text-xs text-gray-400">→</span>
            </a>
            <a @click="mobileMenuOpen = false" href="#lokasi" class="px-4 py-3 rounded-modern font-semibold text-brand-dark hover:bg-brand-soft-green hover:text-brand-primary transition-colors flex items-center justify-between">
                <span>Lokasi & Kontak</span>
                <span class="text-xs text-gray-400">→</span>
            </a>
        </div>
        <div class="pt-2 border-t border-gray-100">
            <a href="https://wa.me/{{ $cleanAdminWa }}?text=Halo%20{{ urlencode($brandName) }},%20saya%20ingin%20tanya%20produk%20dan%20pemesanan" 
               target="_blank"
               class="w-full inline-flex items-center justify-center gap-2.5 px-5 py-3.5 rounded-modern font-semibold text-white bg-brand-primary hover:bg-brand-primary-dark shadow-md">
                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                </svg>
                <span>Pesan Cepat via WhatsApp</span>
            </a>
        </div>
    </div>
</header>
