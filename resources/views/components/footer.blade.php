@php
    $location = $location ?? config('location');
    $site = $site ?? config('site');
    $footer = $footer ?? [];
    $cleanAdminWa = preg_replace('/[^0-9]/', '', $site['contact']['admin_whatsapp'] ?? '6281234567890');

    $brandTitle = !empty($footer['brand_title']) ? $footer['brand_title'] : ($site['brand']['short_name'] ?? 'Sumber Protein');
    $brandDesc = !empty($footer['brand_desc']) ? $footer['brand_desc'] : ($site['brand']['description'] ?? 'Penyedia bahan makanan mentah, frozen food, dan olahan ready-to-cook berkualitas di Yogyakarta. Melayani kebutuhan konsumsi harian keluarga dan suplai horeka/curah.');
    $copyrightText = !empty($footer['copyright']) ? $footer['copyright'] : ($site['website']['copyright'] ?? 'Sumber Protein Jogja. Hak Cipta Dilindungi.');
    $outletAddress = !empty($footer['outlet_address']) ? $footer['outlet_address'] : ($location['address']['full'] ?? 'Jl. Kaliurang Km. 8.5 No. 42, Sinduharjo, Ngaglik, Sleman, D.I. Yogyakarta 55581');
    $outletHours = !empty($footer['outlet_hours']) ? $footer['outlet_hours'] : ($location['operational_hours']['display'] ?? 'Senin – Minggu, 07.00 – 19.00 WIB');
    $outletPhone = !empty($footer['outlet_phone']) ? $footer['outlet_phone'] : ($site['contact']['phone'] ?? '+62 812-3456-7890');
    $navTitle = !empty($footer['nav_title']) ? $footer['nav_title'] : 'Navigasi Cepat';
    $navLinks = !empty($footer['nav_links']) ? $footer['nav_links'] : [
        ['title' => 'Beranda', 'url' => '#hero'],
        ['title' => 'Kategori Produk', 'url' => '#kategori'],
        ['title' => 'Katalog Pilihan', 'url' => '#produk'],
        ['title' => 'Keunggulan Kami', 'url' => '#keunggulan'],
        ['title' => 'Dapur & Knowledge', 'url' => '#knowledge'],
        ['title' => 'Ulasan Pelanggan', 'url' => '#testimoni'],
    ];
    $categoryTitle = !empty($footer['category_title']) ? $footer['category_title'] : 'Kategori Pangan';
    $categoryLinks = !empty($footer['category_links']) ? $footer['category_links'] : [
        ['title' => 'Daging Sapi Slice & Sengkel', 'url' => '#produk'],
        ['title' => 'Ayam Ungkep Bumbu Kuning', 'url' => '#produk'],
        ['title' => 'Dada Ayam Fillet Boneless', 'url' => '#produk'],
        ['title' => 'Fillet Gurame & Salmon', 'url' => '#produk'],
        ['title' => 'Paket Sayur Siap Masak', 'url' => '#produk'],
        ['title' => 'Ayam & Daging Curah (Bulk)', 'url' => '#produk'],
    ];
    $socialLinks = !empty($footer['social_links']) ? $footer['social_links'] : [];
@endphp
<footer class="bg-brand-dark text-white pt-16 pb-12 border-t border-brand-dark-soft">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Top Footer Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 lg:gap-8 pb-12 border-b border-white/10">
            
            <!-- Col 1: Brand Info (2 cols wide on desktop) -->
            <div class="lg:col-span-2 space-y-4">
                <a href="#hero" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-modern bg-brand-primary flex items-center justify-center text-white shadow-md">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2a5 5 0 0 1 5 5v1a5 5 0 0 1-5 5 5 5 0 0 1-5-5V7a5 5 0 0 1 5-5z" fill="currentColor" fill-opacity="0.2"/>
                            <path d="M12 13v9"/>
                            <path d="M7 17l5 5 5-5"/>
                            <circle cx="12" cy="7" r="3"/>
                        </svg>
                    </div>
                    <span class="text-xl font-extrabold tracking-tight text-white">
                        {{ $brandTitle }} <span class="text-emerald-400">Jogja</span>
                    </span>
                </a>
                
                <p class="text-xs sm:text-sm text-gray-300 font-normal leading-relaxed max-w-sm">
                    {{ $brandDesc }}
                </p>

                <!-- Social Icons -->
                <div class="flex items-center gap-3 pt-2">
                    @if(!empty($socialLinks))
                        @foreach($socialLinks as $soc)
                            @php
                                $u = $soc['url'] ?? '';
                                if (empty($u)) continue;
                                $lower = strtolower($u);
                                $label = 'Social Link';
                                if (str_contains($lower, 'instagram.com')) $label = 'Instagram';
                                elseif (str_contains($lower, 'tiktok.com')) $label = 'TikTok';
                                elseif (str_contains($lower, 'wa.me') || str_contains($lower, 'whatsapp.com')) $label = 'WhatsApp';
                                elseif (str_contains($lower, 'facebook.com')) $label = 'Facebook';
                                elseif (str_contains($lower, 'youtube.com')) $label = 'YouTube';
                            @endphp
                            <a href="{{ $u }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full bg-white/10 hover:bg-brand-primary text-gray-300 hover:text-white flex items-center justify-center transition-colors" aria-label="{{ $label }}">
                                @if(str_contains($lower, 'instagram.com'))
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                @elseif(str_contains($lower, 'tiktok.com'))
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.298-.002.595.042.88.13V9.4a6.33 6.33 0 0 0-1-.08A6.34 6.34 0 0 0 3 15.66a6.34 6.34 0 0 0 10.82 4.47v-7.79a8.27 8.27 0 0 0 5.77 2.27V11a4.84 4.84 0 0 1-3.77-1.72 4.82 4.82 0 0 1-1.23-2.59z"/></svg>
                                @elseif(str_contains($lower, 'wa.me') || str_contains($lower, 'whatsapp.com'))
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                @elseif(str_contains($lower, 'facebook.com'))
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                @else
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                                @endif
                            </a>
                        @endforeach
                    @else
                        <a href="{{ $site['social']['instagram'] ?? 'https://instagram.com' }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full bg-white/10 hover:bg-brand-primary text-gray-300 hover:text-white flex items-center justify-center transition-colors" aria-label="Instagram">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="{{ $site['social']['tiktok'] ?? 'https://tiktok.com' }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full bg-white/10 hover:bg-brand-primary text-gray-300 hover:text-white flex items-center justify-center transition-colors" aria-label="TikTok">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.298-.002.595.042.88.13V9.4a6.33 6.33 0 0 0-1-.08A6.34 6.34 0 0 0 3 15.66a6.34 6.34 0 0 0 10.82 4.47v-7.79a8.27 8.27 0 0 0 5.77 2.27V11a4.84 4.84 0 0 1-3.77-1.72 4.82 4.82 0 0 1-1.23-2.59z"/></svg>
                        </a>
                        <a href="https://wa.me/{{ $cleanAdminWa }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full bg-white/10 hover:bg-[#25D366] text-gray-300 hover:text-white flex items-center justify-center transition-colors" aria-label="WhatsApp">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Col 2: Navigation Links -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wider">{{ $navTitle }}</h4>
                <ul class="space-y-2 text-xs sm:text-sm text-gray-300">
                    @foreach($navLinks as $nav)
                        <li><a href="{{ $nav['url'] ?? '#' }}" class="hover:text-white transition-colors">{{ $nav['title'] ?? '' }}</a></li>
                    @endforeach
                </ul>
            </div>

            <!-- Col 3: Product Types -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wider">{{ $categoryTitle }}</h4>
                <ul class="space-y-2 text-xs sm:text-sm text-gray-300">
                    @foreach($categoryLinks as $catLink)
                        <li><a href="{{ $catLink['url'] ?? '#produk' }}" class="hover:text-white transition-colors">{{ $catLink['title'] ?? '' }}</a></li>
                    @endforeach
                </ul>
            </div>

            <!-- Col 4: Store & Hours -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wider">{{ $footer['outlet_title'] ?? 'Outlet Yogyakarta' }}</h4>
                <p class="text-xs text-gray-300 leading-relaxed">
                    {{ $outletAddress }}
                </p>
                <div class="pt-1">
                    <p class="text-[11px] text-gray-400">{{ $footer['outlet_hours_label'] ?? 'Jam Operasional:' }}</p>
                    <p class="text-xs text-white font-medium">{{ $outletHours }}</p>
                </div>
                <div class="pt-1">
                    <p class="text-[11px] text-gray-400">{{ $footer['outlet_phone_label'] ?? 'Hotline Pemesanan:' }}</p>
                    <p class="text-xs text-emerald-400 font-semibold">{{ $outletPhone }}</p>
                </div>
            </div>

        </div>

        <!-- Bottom Copyright -->
        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-400 gap-4">
            <p>© {{ date('Y') }} {{ $copyrightText }}</p>
            <div class="flex items-center gap-6">
                @if(!empty($footer['legal_links']))
                    @foreach($footer['legal_links'] as $legal)
                        <a href="{{ $legal['url'] ?? '#' }}" class="hover:text-gray-200 transition-colors">{{ $legal['title'] ?? '' }}</a>
                    @endforeach
                @else
                    <span class="hover:text-gray-200 transition-colors">Syarat & Ketentuan</span>
                    <span class="hover:text-gray-200 transition-colors">Kebijakan Privasi</span>
                    <span class="hover:text-gray-200 transition-colors">Sertifikasi Halal</span>
                @endif
            </div>
        </div>

    </div>
</footer>
