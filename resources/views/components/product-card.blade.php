<section id="produk" 
         class="py-14 sm:py-20 lg:py-24 bg-white relative"
         x-data="{
             activeFilter: 'all',
             setFilter(catId) {
                 this.activeFilter = catId;
             },
             matches(prodCatId) {
                 if (this.activeFilter === 'all') return true;
                 return prodCatId == this.activeFilter;
             }
         }"
         @filter-category.window="activeFilter = $event.detail.category_id">
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-6 sm:mb-8 gap-4">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-2.5">
                    Katalog Lengkap
                </span>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-brand-dark tracking-tight mb-2">
                    Produk Pilihan
                </h2>
                <p class="text-xs sm:text-sm md:text-base text-gray-600 font-normal">
                    Pilih bahan masak sesuai kebutuhanmu. Tersedia skala retail rumah tangga maupun pembelian curah.
                </p>
            </div>

            <!-- WhatsApp Direct Help -->
            <div class="hidden sm:flex items-center gap-2 text-xs text-gray-500 bg-brand-cream px-3.5 py-2 rounded-modern border border-gray-200/60 shrink-0">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Butuh potongan khusus / partai besar? </span>
                <a href="https://wa.me/6281234567890?text=Halo%20Sumber%20Protein%20Jogja,%20saya%20mau%20konsultasi%20pesanan%20khusus" 
                   target="_blank" 
                   class="font-semibold text-brand-primary hover:underline">
                    Chat Admin
                </a>
            </div>
        </div>

        <!-- Sticky Category Navigation Tabs (Dynamic from Master Categories) -->
        <div class="sticky top-[60px] sm:top-[68px] z-30 bg-white/95 backdrop-blur-md py-2.5 sm:py-3 -mx-4 px-4 sm:mx-0 sm:px-0 border-y border-gray-100/90 shadow-xs mb-6 sm:mb-8 transition-all">
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5" role="tablist" aria-label="Filter Kategori Produk">
                
                <!-- Tab Semua Produk -->
                <button @click="setFilter('all')" 
                        type="button" 
                        role="tab"
                        :aria-selected="activeFilter === 'all'"
                        class="px-3.5 sm:px-5 py-2 rounded-modern text-xs sm:text-sm font-semibold whitespace-nowrap transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 shrink-0 cursor-pointer"
                        :class="activeFilter === 'all' 
                            ? 'bg-brand-primary text-white shadow-sm' 
                            : 'bg-brand-cream text-brand-dark hover:bg-brand-soft-green border border-gray-200/60'">
                    Semua Produk
                </button>

                <!-- Dynamic Category Tabs from Master Categories -->
                @foreach($categories as $cat)
                    @if(in_array(($cat['status'] ?? ''), ['active_landing', 'active_catalog', 'Aktif']))
                    <button @click="setFilter({{ $cat['id'] }})" 
                            type="button" 
                            role="tab"
                            :aria-selected="activeFilter == {{ $cat['id'] }}"
                            class="px-3.5 sm:px-5 py-2 rounded-modern text-xs sm:text-sm font-semibold whitespace-nowrap transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 shrink-0 cursor-pointer"
                            :class="activeFilter == {{ $cat['id'] }} 
                                ? 'bg-brand-primary text-white shadow-sm' 
                                : 'bg-brand-cream text-brand-dark hover:bg-brand-soft-green border border-gray-200/60'">
                        {{ $cat['name'] }}
                    </button>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Product Grid: Mobile 2 Cols (grid-cols-2), Tablet 2 Cols, Desktop 4 Cols -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5 lg:gap-6">
            @foreach($products as $prod)
            <div x-show="matches({{ $prod['category_id'] ?? 1 }})"
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                @include('components.product-card-item', ['prod' => $prod, 'isLivePreview' => false])
            </div>
            @endforeach
        </div>

    </div>
</section>
