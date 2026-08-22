<section id="testimoni" 
         class="py-14 sm:py-20 lg:py-24 bg-white relative"
         x-data="{
             activeSlide: 0,
             totalSlides: {{ count($testimonials) }},
             scrollToIndex(index) {
                 this.activeSlide = index;
                 const container = this.$refs.sliderContainer;
                 if (container) {
                     const card = container.children[index];
                     if (card) {
                         card.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                     }
                 }
             },
             updateActiveOnScroll() {
                 const container = this.$refs.sliderContainer;
                 if (!container) return;
                 const scrollLeft = container.scrollLeft;
                 const cardWidth = container.children[0]?.offsetWidth || 1;
                 this.activeSlide = Math.round(scrollLeft / (cardWidth + 16));
             }
         }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 sm:mb-12 gap-6">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-2.5">
                    Ulasan Pelanggan
                </span>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-brand-dark tracking-tight mb-2">
                    Apa Kata Mereka?
                </h2>
                <p class="text-xs sm:text-sm md:text-base text-gray-600 font-normal max-w-xl">
                    Pengalaman nyata dari ibu rumah tangga, chef rumahan, hingga pemilik kedai kuliner di Yogyakarta.
                </p>
            </div>

            <!-- Google Review Aggregate Rating Badge -->
            <div class="flex items-center gap-3 bg-brand-cream p-3 sm:p-3.5 rounded-modern border border-gray-200/80 shadow-xs shrink-0 self-start md:self-auto">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white flex items-center justify-center shadow-2xs border border-gray-100 shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-1">
                        <span class="text-sm sm:text-base font-extrabold text-brand-dark">4.9 / 5.0</span>
                        <div class="flex text-amber-400 gap-0.5" aria-label="4.9 dari 5 bintang">
                            @for($i=0; $i<5; $i++)
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 fill-current" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                    </div>
                    <span class="text-[11px] sm:text-xs text-gray-500 font-medium">Berdasarkan 180+ Google Reviews</span>
                </div>
            </div>
        </div>

        <!-- Testimonial Cards: Horizontal Snap Slider on Mobile, 3-Card Grid on Desktop -->
        <div x-ref="sliderContainer"
             @scroll.debounce.50ms="updateActiveOnScroll()"
             class="flex md:grid md:grid-cols-3 gap-4 sm:gap-6 lg:gap-8 overflow-x-auto md:overflow-visible snap-x snap-mandatory no-scrollbar -mx-4 px-4 sm:mx-0 sm:px-0 py-2">
            
            @foreach($testimonials as $idx => $rev)
            <div class="w-[85vw] max-w-[340px] sm:max-w-none md:w-auto shrink-0 snap-center bg-brand-cream/40 p-5 sm:p-7 rounded-modern border border-gray-200/70 shadow-sm hover:shadow-card transition-all duration-300 flex flex-col justify-between">
                <div>
                    <!-- Star Rating & Google Tag -->
                    <div class="flex items-center justify-between mb-3.5">
                        <div class="flex text-amber-400 gap-0.5" aria-label="Rating {{ $rev['rating'] }} dari 5 bintang">
                            @for($i=0; $i<$rev['rating']; $i++)
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                        <span class="inline-flex items-center gap-1 text-[10px] sm:text-[11px] font-semibold text-gray-500 bg-white px-2 py-0.5 rounded-full border border-gray-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Google Review
                        </span>
                    </div>

                    <!-- Review Body -->
                    <p class="text-xs sm:text-sm text-gray-700 leading-relaxed italic mb-5">
                        "{{ $rev['review'] }}"
                    </p>
                </div>

                <!-- Reviewer Identity -->
                <div class="pt-3.5 border-t border-gray-200/60 flex items-center gap-3">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-brand-primary text-white font-bold text-xs flex items-center justify-center shadow-2xs shrink-0">
                        {{ $rev['avatar'] }}
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-brand-dark leading-tight">
                            {{ $rev['name'] }}
                        </h4>
                        <p class="text-[10px] sm:text-[11px] text-gray-500 mt-0.5">
                            {{ $rev['role'] }} • <span class="text-gray-400">{{ $rev['date'] }}</span>
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Mobile Indicator Dots -->
        <div class="flex md:hidden items-center justify-center gap-2 mt-4" aria-label="Navigasi Review Slider">
            @foreach($testimonials as $idx => $rev)
            <button @click="scrollToIndex({{ $idx }})" 
                    type="button"
                    aria-label="Lihat ulasan {{ $idx + 1 }}"
                    class="h-1.5 rounded-full transition-all duration-300 focus:outline-none"
                    :class="activeSlide === {{ $idx }} ? 'w-6 bg-brand-primary' : 'w-2 bg-gray-300 hover:bg-gray-400'"></button>
            @endforeach
        </div>

    </div>
</section>
