@php
    $isLive = $isLivePreview ?? false;
    $heroData = $hero ?? [
        'badge' => 'Penyedia Bahan Segar & Frozen Food Terpercaya di Jogja',
        'headline_prefix' => 'Bahan Masak',
        'highlight' => 'Siap Olah',
        'headline_suffix' => ', Tinggal Masak.',
        'description' => 'Daging, ayam, ikan, dan sayuran pilihan dalam bentuk frozen dan ready to cook untuk kebutuhan rumah tangga maupun pembelian curah.',
        'primary_cta_text' => 'Belanja Sekarang',
        'primary_cta_link' => '#produk',
        'secondary_cta_text' => 'Lihat Produk',
        'secondary_cta_link' => '#kategori',
        'images' => [
            'storage/media/hero_meat_poultry_1786889302143.jpg',
            'storage/media/hero_seafood_fish_1786889522926.jpg',
            'storage/media/hero_ready_cook_1786889537358.jpg',
        ],
        'trust_items' => [
            ['id' => 1, 'text' => '100% Halal', 'active' => true],
            ['id' => 2, 'text' => 'Cold Chain', 'active' => true],
            ['id' => 3, 'text' => 'Kirim Se-Jogja', 'active' => true],
        ],
    ];
@endphp

<section @if(!$isLive) id="hero" @endif
         class="relative {{ $isLive ? 'min-h-full rounded-modern-xl' : 'min-h-[100svh]' }} w-full flex items-center justify-center overflow-hidden bg-brand-dark"
         @if(!$isLive)
         x-data="{
             currentSlide: 0,
             totalSlides: {{ count($heroData['images'] ?? [1, 2, 3]) }},
             autoplayTimer: null,
             init() {
                 this.startAutoplay();
             },
             startAutoplay() {
                 if (this.totalSlides <= 1) return;
                 this.autoplayTimer = setInterval(() => {
                     this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
                 }, 5500);
             },
             stopAutoplay() {
                 if (this.autoplayTimer) clearInterval(this.autoplayTimer);
             },
             goToSlide(index) {
                 this.stopAutoplay();
                 this.currentSlide = index;
                 this.startAutoplay();
             },
             nextSlide() {
                 this.stopAutoplay();
                 this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
                 this.startAutoplay();
             },
             prevSlide() {
                 this.stopAutoplay();
                 this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
                 this.startAutoplay();
             }
         }"
         @mouseenter="stopAutoplay()"
         @mouseleave="startAutoplay()"
         @else
         @mouseenter="stopAutoplay()"
         @mouseleave="startAutoplay()"
         @endif>

    <!-- Slides Background Container -->
    <div class="absolute inset-0 w-full h-full">
        @if($isLive)
            <template x-for="(img, sIdx) in (draftForm.images || [])" :key="sIdx">
                <div class="absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out"
                     :class="currentSlide === sIdx ? 'opacity-100 scale-100 z-10' : 'opacity-0 scale-105 pointer-events-none z-0'">
                    <img :src="getImageUrl(img)"
                         :alt="'Slide ' + (sIdx + 1)"
                         class="w-full h-full object-cover object-center transform transition-transform duration-7000 ease-out"
                         :class="currentSlide === sIdx ? 'scale-105' : 'scale-100'">
                    <!-- Multi-layer Gradient: Stronger on mobile for flawless text contrast -->
                    <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/95 via-brand-dark/80 to-brand-dark/60 md:bg-gradient-to-r md:from-brand-dark/95 md:via-brand-dark/70 md:to-brand-dark/35"></div>
                    <div class="absolute inset-0 bg-black/20"></div>
                </div>
            </template>
        @else
            @foreach(($heroData['images'] ?? ['storage/media/hero_meat_poultry_1786889302143.jpg', 'storage/media/hero_seafood_fish_1786889522926.jpg', 'storage/media/hero_ready_cook_1786889537358.jpg']) as $sIdx => $img)
                <div class="absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out"
                     :class="currentSlide === {{ $sIdx }} ? 'opacity-100 scale-100 z-10' : 'opacity-0 scale-105 pointer-events-none z-0'">
                    <img src="{{ asset($img) }}"
                         alt="Slide {{ $sIdx + 1 }} - Sumber Protein Jogja"
                         width="1920"
                         height="1080"
                         @if($sIdx === 0) fetchpriority="high" @else loading="lazy" @endif
                         class="w-full h-full object-cover object-center transform transition-transform duration-7000 ease-out"
                         :class="currentSlide === {{ $sIdx }} ? 'scale-105' : 'scale-100'">
                    <!-- Multi-layer Gradient: Stronger on mobile for flawless text contrast -->
                    <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/95 via-brand-dark/80 to-brand-dark/60 md:bg-gradient-to-r md:from-brand-dark/95 md:via-brand-dark/70 md:to-brand-dark/35"></div>
                    <div class="absolute inset-0 bg-black/20"></div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Decorative Top Subtle Light -->
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-brand-primary/20 rounded-full blur-3xl pointer-events-none z-10"></div>

    <!-- Hero Content Container -->
    <div class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 {{ $isLive ? 'pt-6 pb-10 sm:pt-14 sm:pb-16 min-h-full' : 'pt-24 pb-20 sm:pt-32 sm:pb-28 min-h-[100svh]' }} flex flex-col justify-center">
        <div class="max-w-3xl">

            <!-- Category Tag Pill -->
            <div class="inline-flex items-center gap-2 px-3 sm:px-3.5 py-1.5 rounded-full bg-brand-primary/30 border border-brand-primary/40 backdrop-blur-md text-brand-soft-green text-[11px] sm:text-xs md:text-sm font-semibold mb-4 sm:mb-6 shadow-sm max-w-full">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                <span class="truncate sm:whitespace-normal" @if($isLive) x-text="draftForm.badge || 'Penyedia Bahan Segar & Frozen Food Terpercaya di Jogja'" @endif>
                    {{ $heroData['badge'] ?? 'Penyedia Bahan Segar & Frozen Food Terpercaya di Jogja' }}
                </span>
            </div>

            <!-- Main Heading (Single H1 on page for SEO) -->
            <h1 class="text-2xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-[1.2] sm:leading-[1.15] mb-4 sm:mb-6 drop-shadow-sm">
                <span @if($isLive) x-text="draftForm.headline_prefix || 'Bahan Masak'" @endif>{{ $heroData['headline_prefix'] ?? 'Bahan Masak' }}</span>
                <span class="text-emerald-400 underline decoration-brand-accent decoration-2 sm:decoration-4 underline-offset-4 sm:underline-offset-8"
                      @if($isLive) x-text="draftForm.highlight || 'Siap Olah'" @endif>{{ $heroData['highlight'] ?? 'Siap Olah' }}</span> <span @if($isLive) x-text="draftForm.headline_suffix !== undefined ? draftForm.headline_suffix : ', Tinggal Masak.'" @endif>{{ $heroData['headline_suffix'] ?? ', Tinggal Masak.' }}</span>
            </h1>

            <!-- Subheadline -->
            <p class="text-xs sm:text-base md:text-lg lg:text-xl text-gray-200 font-normal leading-relaxed mb-6 sm:mb-8 md:mb-10 max-w-2xl text-shadow"
               @if($isLive) x-text="draftForm.description || 'Daging, ayam, ikan, dan sayuran pilihan dalam bentuk frozen dan ready to cook untuk kebutuhan rumah tangga maupun pembelian curah.'" @endif>
                {{ $heroData['description'] ?? 'Daging, ayam, ikan, dan sayuran pilihan dalam bentuk frozen dan ready to cook untuk kebutuhan rumah tangga maupun pembelian curah.' }}
            </p>

            @php
                $cleanHeroPrimary = trim($heroData['primary_cta_link'] ?? '#produk');
                if (preg_match('/^(javascript|vbscript|data|file|blob|about):/i', $cleanHeroPrimary) || str_starts_with($cleanHeroPrimary, '//')) {
                    $cleanHeroPrimary = '#produk';
                }
                $cleanHeroSecondary = trim($heroData['secondary_cta_link'] ?? '#kategori');
                if (preg_match('/^(javascript|vbscript|data|file|blob|about):/i', $cleanHeroSecondary) || str_starts_with($cleanHeroSecondary, '//')) {
                    $cleanHeroSecondary = '#kategori';
                }
            @endphp
            <!-- Call to Actions -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4 md:gap-5">
                <a @if($isLive) :href="draftForm.primary_cta_link || '#produk'" @else href="{{ $cleanHeroPrimary }}" @endif
                   class="inline-flex items-center justify-center gap-2 px-6 sm:px-8 py-3.5 sm:py-4 rounded-modern text-sm sm:text-base font-bold text-white bg-brand-primary-light hover:bg-brand-primary-dark active:scale-[0.98] transition-all duration-200 shadow-lg shadow-brand-primary/40 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span @if($isLive) x-text="draftForm.primary_cta_text || 'Belanja Sekarang'" @endif>{{ $heroData['primary_cta_text'] ?? 'Belanja Sekarang' }}</span>
                </a>

                <a @if($isLive) :href="draftForm.secondary_cta_link || '#kategori'" @else href="{{ $cleanHeroSecondary }}" @endif
                   class="inline-flex items-center justify-center gap-2 px-6 sm:px-7 py-3.5 sm:py-4 rounded-modern text-sm sm:text-base font-semibold text-white bg-white/10 hover:bg-white/20 border border-white/25 backdrop-blur-md active:scale-[0.98] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white/40">
                    <span @if($isLive) x-text="draftForm.secondary_cta_text || 'Lihat Produk'" @endif>{{ $heroData['secondary_cta_text'] ?? 'Lihat Produk' }}</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </a>
            </div>

            <!-- Quick Trust Badges (Fixed 3 Items) -->
            <div class="mt-8 sm:mt-12 pt-5 sm:pt-6 border-t border-white/15 grid grid-cols-3 gap-2 sm:gap-4 text-white/90">
                @if($isLive)
                    <template x-for="(item, tIdx) in (draftForm.trust_items || [])" :key="tIdx">
                        <div x-show="item.active !== false && item.is_active !== false" class="flex items-center gap-1.5 sm:gap-2.5 min-w-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-white/10 flex items-center justify-center text-emerald-400 shrink-0">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-[10px] sm:text-xs md:text-sm font-medium leading-tight truncate sm:whitespace-normal" x-text="item.text"></span>
                        </div>
                    </template>
                @else
                    @php
                        $trustList = $heroData['trust_items'] ?? [
                            ['id' => 1, 'text' => '100% Halal & Higienis', 'active' => true, 'is_active' => true],
                            ['id' => 2, 'text' => 'Standar Rantai Dingin (Cold Chain)', 'active' => true, 'is_active' => true],
                            ['id' => 3, 'text' => 'Pengiriman Cepat Se-Jogja', 'active' => true, 'is_active' => true]
                        ];
                    @endphp
                    @foreach($trustList as $item)
                        @if($item['active'] ?? ($item['is_active'] ?? true))
                            <div class="flex items-center gap-1.5 sm:gap-2.5 min-w-0">
                                <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-white/10 flex items-center justify-center text-emerald-400 shrink-0">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="text-[10px] sm:text-xs md:text-sm font-medium leading-tight truncate sm:whitespace-normal">{{ $item['text'] }}</span>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            <!-- Telah dipercaya oleh (Mitra) -->
            @php
                $partnerData = $heroData['partners'] ?? [
                    'badge' => 'Kepercayaan Mitra',
                    'title' => 'Telah Dipercaya Restoran, Cafe, Catering & Rumah Tangga di Jogja',
                    'partners' => []
                ];
                $rawPartners = $partnerData['partners'] ?? [];
                $activePartners = is_array($rawPartners) ? array_filter($rawPartners, function($p) {
                    $raw = $p['is_active'] ?? ($p['active'] ?? true);
                    if (is_bool($raw)) {
                        return $raw;
                    }
                    if (is_numeric($raw)) {
                        return (int) $raw === 1;
                    }
                    if (is_string($raw)) {
                        $lower = strtolower(trim($raw));
                        if (in_array($lower, ['false', '0', 'nonaktif', 'nonaktif (sembunyi)', 'inactive', 'off', 'hide', 'hidden', ''])) {
                            return false;
                        }
                        if (in_array($lower, ['true', '1', 'aktif', 'aktif (tampil)', 'active', 'on', 'show', 'visible'])) {
                            return true;
                        }
                        return false;
                    }
                    return (bool) $raw;
                }) : [];
            @endphp
            @if(!empty($activePartners))
            <div class="mt-6 sm:mt-8 pt-4 sm:pt-5 border-t border-white/10">
                <p class="text-[11px] sm:text-xs text-gray-300 font-medium mb-3 flex items-center gap-1.5"
                   @if($isLive) x-text="(draftForm.partners && draftForm.partners.title) ? draftForm.partners.title : 'Telah Dipercaya Restoran, Cafe, Catering & Rumah Tangga di Jogja'" @endif>
                    <span>🤝</span>
                    <span>{{ $partnerData['title'] ?? 'Telah Dipercaya Restoran, Cafe, Catering & Rumah Tangga di Jogja' }}</span>
                </p>
                <div class="flex items-center flex-wrap gap-4 sm:gap-6 md:gap-8">
                    @foreach($activePartners as $partner)
                        @php
                            $pLogo = trim($partner['logo'] ?? '');
                            $hasValidLogo = !empty($pLogo) && !str_contains($pLogo, 'mitra-placeholder');
                            $logoUrl = $hasValidLogo
                                ? (str_starts_with($pLogo, 'http') ? $pLogo : asset(ltrim($pLogo, '/')))
                                : null;
                        @endphp
                        @if($logoUrl)
                        <div class="inline-flex items-center justify-center group"
                             title="{{ $partner['name'] ?? 'Mitra' }}">
                            <img src="{{ $logoUrl }}"
                                 alt="{{ $partner['name'] ?? 'Logo Mitra' }}"
                                 class="h-7 sm:h-9 md:h-10 max-w-[120px] sm:max-w-[150px] object-contain opacity-85 hover:opacity-100 transition-opacity duration-200" />
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>

    <!-- Slideshow Navigation Controls (Centered on Mobile, Bottom-Right on Desktop to avoid WhatsApp Button Collision) -->
    <div class="absolute bottom-5 sm:bottom-10 left-1/2 -translate-x-1/2 sm:left-auto sm:right-10 sm:translate-x-0 z-30 flex items-center gap-3 bg-black/30 backdrop-blur-md px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-full border border-white/10"
         @if($isLive) x-show="(draftForm.images || []).length > 1" @endif>

        <!-- Prev Slide Button -->
        <button @click="prevSlide()"
                type="button"
                class="text-white/70 hover:text-white p-1 focus:outline-none focus:ring-1 focus:ring-white/50 transition-colors cursor-pointer"
                aria-label="Slide Sebelumnya">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Slide Indicator Dots -->
        <div class="flex items-center gap-1.5 sm:gap-2">
            @if($isLive)
                <template x-for="(img, dotIdx) in (draftForm.images || [])" :key="dotIdx">
                    <button @click="goToSlide(dotIdx)"
                            type="button"
                            class="h-1.5 sm:h-2 rounded-full transition-all duration-300 focus:outline-none cursor-pointer"
                            :class="currentSlide === dotIdx ? 'w-5 sm:w-6 bg-brand-accent' : 'w-1.5 sm:w-2 bg-white/50 hover:bg-white/80'"
                            :aria-label="'Slide ' + (dotIdx + 1)"></button>
                </template>
            @else
                @foreach(($heroData['images'] ?? ['storage/media/hero_meat_poultry_1786889302143.jpg', 'storage/media/hero_seafood_fish_1786889522926.jpg', 'storage/media/hero_ready_cook_1786889537358.jpg']) as $dotIdx => $img)
                    <button @click="goToSlide({{ $dotIdx }})"
                            type="button"
                            class="h-1.5 sm:h-2 rounded-full transition-all duration-300 focus:outline-none cursor-pointer"
                            :class="currentSlide === {{ $dotIdx }} ? 'w-5 sm:w-6 bg-brand-accent' : 'w-1.5 sm:w-2 bg-white/50 hover:bg-white/80'"
                            aria-label="Slide {{ $dotIdx + 1 }}"></button>
                @endforeach
            @endif
        </div>

        <!-- Next Slide Button -->
        <button @click="nextSlide()"
                type="button"
                class="text-white/70 hover:text-white p-1 focus:outline-none focus:ring-1 focus:ring-white/50 transition-colors cursor-pointer"
                aria-label="Slide Berikutnya">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

</section>
