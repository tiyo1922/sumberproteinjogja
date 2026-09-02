@php
    $activeCategories = is_array($categories) ? array_values(array_filter($categories, function($c) {
        $raw = (int) ($c['is_active'] ?? 0);
        $st = $c['status'] ?? '';
        return ($raw === 1) || ($st === 'active_landing');
    })) : $categories->filter(function($c) {
        $raw = (int) ($c->is_active ?? ($c['is_active'] ?? 0));
        $st = $c->status ?? ($c['status'] ?? '');
        return ($raw === 1) || ($st === 'active_landing');
    })->values();
    $totalCategories = count($activeCategories);
@endphp

<section id="kategori" 
         class="py-16 sm:py-24 bg-brand-cream/60 relative overflow-hidden"
         x-data="{
             total: {{ $totalCategories }},
             currentIndex: 0,
             visibleCards: 4,
             touchStartX: 0,
             touchEndX: 0,

             init() {
                 this.updateVisibleCards();
                 window.addEventListener('resize', () => {
                     this.updateVisibleCards();
                 });
             },

             updateVisibleCards() {
                 const width = window.innerWidth;
                 if (width >= 1024) {
                     this.visibleCards = 4;
                 } else if (width >= 640) {
                     this.visibleCards = 3;
                 } else {
                     this.visibleCards = 2;
                 }
                 if (this.currentIndex > this.maxIndex) {
                     this.currentIndex = this.maxIndex;
                 }
             },

             get maxIndex() {
                 return Math.max(0, this.total - this.visibleCards);
             },

             get showNavigation() {
                 return this.total > this.visibleCards;
             },

             get canPrev() {
                 return this.currentIndex > 0;
             },

             get canNext() {
                 return this.currentIndex < this.maxIndex;
             },

             get transformStyle() {
                 if (this.currentIndex <= 0) {
                     return 'transform: translateX(0px);';
                 }
                 if (this.visibleCards === 2) {
                     return `transform: translateX(calc(-${this.currentIndex} * (100% + 12px) / 2));`;
                 }
                 if (this.visibleCards === 3) {
                     return `transform: translateX(calc(-${this.currentIndex} * (100% + 16px) / 3));`;
                 }
                 return `transform: translateX(calc(-${this.currentIndex} * (100% + 24px) / 4));`;
             },

             prev() {
                 if (this.canPrev) {
                     this.currentIndex--;
                 }
             },

             next() {
                 if (this.canNext) {
                     this.currentIndex++;
                 }
             },

             handleTouchStart(e) {
                 if (e.changedTouches && e.changedTouches.length > 0) {
                     this.touchStartX = e.changedTouches[0].screenX;
                 }
             },

             handleTouchEnd(e) {
                 if (e.changedTouches && e.changedTouches.length > 0) {
                     this.touchEndX = e.changedTouches[0].screenX;
                     const diff = this.touchStartX - this.touchEndX;
                     if (Math.abs(diff) > 40) {
                         if (diff > 0) {
                             this.next();
                         } else {
                             this.prev();
                         }
                     }
                 }
             }
         }">
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header (Centered) -->
        <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-12">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-3">
                {{ $categorySection['label'] ?? 'Kategori Utama' }}
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-brand-dark tracking-tight mb-3">
                {{ $categorySection['title'] ?? 'Mau Masak Apa Hari Ini?' }}
            </h2>
            <p class="text-sm sm:text-base text-gray-600 font-normal">
                {{ $categorySection['subtitle'] ?? 'Pilih bahan masak sesuai kebutuhanmu. Dari potongan daging segar, ayam bumbu, ikan laut, hingga sayuran siap cemplung.' }}
            </p>
        </div>

        @if($totalCategories > 0)
            <!-- Carousel Outer Viewport Window -->
            <div class="w-full overflow-hidden relative py-1 -my-1"
                 @touchstart.passive="handleTouchStart($event)"
                 @touchend.passive="handleTouchEnd($event)">
                
                <!-- Sliding Flex Track (items-stretch for equal card heights) -->
                <div class="flex items-stretch transition-transform duration-500 ease-out gap-3 sm:gap-4 lg:gap-6"
                     :style="transformStyle">
                    
                    @foreach($activeCategories as $cat)
                        <div class="shrink-0 grow-0 min-w-0 flex flex-col h-full w-[calc((100%-12px)/2)] sm:w-[calc((100%-32px)/3)] lg:w-[calc((100%-72px)/4)]">
                            @include('components.category-card-item', ['cat' => $cat, 'isLivePreview' => false])
                        </div>
                    @endforeach

                </div>
            </div>

            <!-- Bottom Navigation Bar (<  dots  >) -->
            <template x-if="showNavigation">
                <div class="flex items-center justify-center gap-3 mt-6 sm:mt-8">
                    <!-- Previous Button -->
                    <button type="button"
                            @click="prev()"
                            :disabled="!canPrev"
                            aria-label="Kategori Sebelumnya"
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-full border border-gray-200 bg-white shadow-2xs flex items-center justify-center text-brand-dark transition-all duration-200 cursor-pointer"
                            :class="!canPrev ? 'opacity-30 cursor-not-allowed bg-gray-50 text-gray-400' : 'hover:bg-brand-primary hover:text-white hover:border-brand-primary active:scale-95'">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    <!-- Pagination Indicator Dots -->
                    <div class="flex items-center gap-1.5 px-2">
                        <template x-for="i in (maxIndex + 1)" :key="i">
                            <button type="button" 
                                    @click="currentIndex = (i - 1)" 
                                    :aria-label="'Ke slide ' + i"
                                    class="h-2 rounded-full transition-all duration-300 cursor-pointer"
                                    :class="currentIndex === (i - 1) ? 'w-6 bg-brand-primary' : 'w-2 bg-gray-300 hover:bg-gray-400'">
                            </button>
                        </template>
                    </div>

                    <!-- Next Button -->
                    <button type="button"
                            @click="next()"
                            :disabled="!canNext"
                            aria-label="Kategori Berikutnya"
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-full border border-gray-200 bg-white shadow-2xs flex items-center justify-center text-brand-dark transition-all duration-200 cursor-pointer"
                            :class="!canNext ? 'opacity-30 cursor-not-allowed bg-gray-50 text-gray-400' : 'hover:bg-brand-primary hover:text-white hover:border-brand-primary active:scale-95'">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </template>
        @else
            <!-- Graceful Empty State -->
            <div class="py-12 text-center text-gray-500 bg-white/50 rounded-modern border border-dashed border-gray-200">
                <p class="text-sm font-medium">Belum ada kategori yang ditampilkan saat ini.</p>
            </div>
        @endif

    </div>
</section>
