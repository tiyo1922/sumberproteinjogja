<section id="hero" 
         class="relative min-h-[100svh] w-full flex items-center justify-center overflow-hidden bg-brand-dark"
         x-data="{
             currentSlide: 0,
             totalSlides: 3,
             autoplayTimer: null,
             init() {
                 this.startAutoplay();
             },
             startAutoplay() {
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
         @mouseleave="startAutoplay()">

    <!-- Slides Background Container -->
    <div class="absolute inset-0 w-full h-full">
        
        <!-- Slide 1: Meat & Chicken (LCP Candidate: fetchpriority high, no lazy) -->
        <div class="absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out"
             :class="currentSlide === 0 ? 'opacity-100 scale-100 z-10' : 'opacity-0 scale-105 pointer-events-none z-0'">
            <img src="{{ asset('images/hero-1.jpg') }}" 
                 alt="Daging Sapi Slice dan Ayam Segar Pilihan Jogja" 
                 width="1920"
                 height="1080"
                 fetchpriority="high"
                 class="w-full h-full object-cover object-center transform transition-transform duration-7000 ease-out"
                 :class="currentSlide === 0 ? 'scale-105' : 'scale-100'">
            <!-- Multi-layer Gradient: Stronger on mobile for flawless text contrast -->
            <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/95 via-brand-dark/80 to-brand-dark/60 md:bg-gradient-to-r md:from-brand-dark/95 md:via-brand-dark/70 md:to-brand-dark/35"></div>
            <div class="absolute inset-0 bg-black/20"></div>
        </div>

        <!-- Slide 2: Seafood & Fish (Deferred / Lazy) -->
        <div class="absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out"
             :class="currentSlide === 1 ? 'opacity-100 scale-100 z-10' : 'opacity-0 scale-105 pointer-events-none z-0'">
            <img src="{{ asset('images/hero-2.jpg') }}" 
                 alt="Ikan Segar Salmon dan Gurame Beku Kapal" 
                 width="1920"
                 height="1080"
                 loading="lazy"
                 class="w-full h-full object-cover object-center transform transition-transform duration-7000 ease-out"
                 :class="currentSlide === 1 ? 'scale-105' : 'scale-100'">
            <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/95 via-brand-dark/80 to-brand-dark/60 md:bg-gradient-to-r md:from-brand-dark/95 md:via-brand-dark/70 md:to-brand-dark/35"></div>
            <div class="absolute inset-0 bg-black/20"></div>
        </div>

        <!-- Slide 3: Ready to Cook & Fresh Veggies (Deferred / Lazy) -->
        <div class="absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out"
             :class="currentSlide === 2 ? 'opacity-100 scale-100 z-10' : 'opacity-0 scale-105 pointer-events-none z-0'">
            <img src="{{ asset('images/hero-3.jpg') }}" 
                 alt="Bahan Masak Siap Olah Berbumbu dan Sayuran Segar" 
                 width="1920"
                 height="1080"
                 loading="lazy"
                 class="w-full h-full object-cover object-center transform transition-transform duration-7000 ease-out"
                 :class="currentSlide === 2 ? 'scale-105' : 'scale-100'">
            <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/95 via-brand-dark/80 to-brand-dark/60 md:bg-gradient-to-r md:from-brand-dark/95 md:via-brand-dark/70 md:to-brand-dark/35"></div>
            <div class="absolute inset-0 bg-black/20"></div>
        </div>
    </div>

    <!-- Decorative Top Subtle Light -->
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-brand-primary/20 rounded-full blur-3xl pointer-events-none z-10"></div>

    <!-- Hero Content Container -->
    <div class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-20 sm:pt-32 sm:pb-28 flex flex-col justify-center min-h-[100svh]">
        <div class="max-w-3xl">
            
            <!-- Category Tag Pill -->
            <div class="inline-flex items-center gap-2 px-3 sm:px-3.5 py-1.5 rounded-full bg-brand-primary/30 border border-brand-primary/40 backdrop-blur-md text-brand-soft-green text-[11px] sm:text-xs md:text-sm font-semibold mb-4 sm:mb-6 shadow-sm max-w-full">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                <span class="truncate sm:whitespace-normal">Penyedia Bahan Segar & Frozen Food Terpercaya di Jogja</span>
            </div>

            <!-- Main Heading (Single H1 on page for SEO) -->
            <h1 class="text-2xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-[1.2] sm:leading-[1.15] mb-4 sm:mb-6 drop-shadow-sm">
                Bahan Masak <span class="text-emerald-400 underline decoration-brand-accent decoration-2 sm:decoration-4 underline-offset-4 sm:underline-offset-8">Siap Olah</span>, Tinggal Masak.
            </h1>

            <!-- Subheadline -->
            <p class="text-xs sm:text-base md:text-lg lg:text-xl text-gray-200 font-normal leading-relaxed mb-6 sm:mb-8 md:mb-10 max-w-2xl text-shadow">
                Daging, ayam, ikan, dan sayuran pilihan dalam bentuk frozen dan ready to cook untuk kebutuhan rumah tangga maupun pembelian curah.
            </p>

            <!-- Call to Actions -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4 md:gap-5">
                <a href="#produk" 
                   class="inline-flex items-center justify-center gap-2 px-6 sm:px-8 py-3.5 sm:py-4 rounded-modern text-sm sm:text-base font-bold text-white bg-brand-primary hover:bg-brand-primary-dark active:scale-[0.98] transition-all duration-200 shadow-lg shadow-brand-primary/40 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span>Belanja Sekarang</span>
                </a>
                
                <a href="#kategori" 
                   class="inline-flex items-center justify-center gap-2 px-6 sm:px-7 py-3.5 sm:py-4 rounded-modern text-sm sm:text-base font-semibold text-white bg-white/10 hover:bg-white/20 border border-white/25 backdrop-blur-md active:scale-[0.98] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white/40">
                    <span>Lihat Produk</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </a>
            </div>

            <!-- Quick Trust Badges -->
            <div class="mt-8 sm:mt-12 pt-5 sm:pt-6 border-t border-white/15 grid grid-cols-3 gap-2 sm:gap-4 text-white/90">
                <div class="flex items-center gap-1.5 sm:gap-2.5">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-white/10 flex items-center justify-center text-emerald-400 shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-[10px] sm:text-xs md:text-sm font-medium leading-tight">100% Halal</span>
                </div>
                <div class="flex items-center gap-1.5 sm:gap-2.5">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-white/10 flex items-center justify-center text-emerald-400 shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-[10px] sm:text-xs md:text-sm font-medium leading-tight">Cold Chain</span>
                </div>
                <div class="flex items-center gap-1.5 sm:gap-2.5">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-white/10 flex items-center justify-center text-emerald-400 shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-[10px] sm:text-xs md:text-sm font-medium leading-tight">Kirim Se-Jogja</span>
                </div>
            </div>

        </div>
    </div>

    <!-- Slideshow Navigation Controls (Centered on Mobile, Bottom-Right on Desktop to avoid WhatsApp Button Collision) -->
    <div class="absolute bottom-5 sm:bottom-10 left-1/2 -translate-x-1/2 sm:left-auto sm:right-10 sm:translate-x-0 z-30 flex items-center gap-3 bg-black/30 backdrop-blur-md px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-full border border-white/10">
        
        <!-- Prev Slide Button -->
        <button @click="prevSlide()" 
                type="button" 
                class="text-white/70 hover:text-white p-1 focus:outline-none focus:ring-1 focus:ring-white/50 transition-colors" 
                aria-label="Slide Sebelumnya">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Slide Indicator Dots -->
        <div class="flex items-center gap-1.5 sm:gap-2">
            <button @click="goToSlide(0)" 
                    class="h-1.5 sm:h-2 rounded-full transition-all duration-300 focus:outline-none"
                    :class="currentSlide === 0 ? 'w-5 sm:w-6 bg-brand-accent' : 'w-1.5 sm:w-2 bg-white/50 hover:bg-white/80'"
                    aria-label="Slide 1: Daging & Ayam"></button>
            <button @click="goToSlide(1)" 
                    class="h-1.5 sm:h-2 rounded-full transition-all duration-300 focus:outline-none"
                    :class="currentSlide === 1 ? 'w-5 sm:w-6 bg-brand-accent' : 'w-1.5 sm:w-2 bg-white/50 hover:bg-white/80'"
                    aria-label="Slide 2: Ikan & Seafood"></button>
            <button @click="goToSlide(2)" 
                    class="h-1.5 sm:h-2 rounded-full transition-all duration-300 focus:outline-none"
                    :class="currentSlide === 2 ? 'w-5 sm:w-6 bg-brand-accent' : 'w-1.5 sm:w-2 bg-white/50 hover:bg-white/80'"
                    aria-label="Slide 3: Ready to Cook & Sayur"></button>
        </div>

        <!-- Next Slide Button -->
        <button @click="nextSlide()" 
                type="button" 
                class="text-white/70 hover:text-white p-1 focus:outline-none focus:ring-1 focus:ring-white/50 transition-colors" 
                aria-label="Slide Berikutnya">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

</section>
