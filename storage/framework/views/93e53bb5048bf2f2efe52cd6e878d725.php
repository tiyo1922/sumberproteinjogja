<section id="produk" 
         class="py-14 sm:py-20 lg:py-24 bg-white relative"
         x-data="{
             activeFilter: 'all',
             setFilter(f) {
                 this.activeFilter = f;
             },
             matches(cat, typeCat) {
                 if (this.activeFilter === 'all') return true;
                 if (this.activeFilter === 'ready-to-cook') return typeCat === 'ready-to-cook';
                 if (this.activeFilter === 'curah') return typeCat === 'curah';
                 return cat === this.activeFilter;
             }
         }"
         @filter-category.window="activeFilter = $event.detail.category">
    
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

        <!-- Sticky Category Navigation Tabs -->
        <div class="sticky top-[60px] sm:top-[68px] z-30 bg-white/95 backdrop-blur-md py-2.5 sm:py-3 -mx-4 px-4 sm:mx-0 sm:px-0 border-y border-gray-100/90 shadow-xs mb-6 sm:mb-8 transition-all">
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5" role="tablist" aria-label="Filter Kategori Produk">
                
                <button @click="setFilter('all')" 
                        type="button" 
                        role="tab"
                        :aria-selected="activeFilter === 'all'"
                        class="px-3.5 sm:px-5 py-2 rounded-modern text-xs sm:text-sm font-semibold whitespace-nowrap transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 shrink-0"
                        :class="activeFilter === 'all' 
                            ? 'bg-brand-primary text-white shadow-sm' 
                            : 'bg-brand-cream text-brand-dark hover:bg-brand-soft-green border border-gray-200/60'">
                    Semua Produk
                </button>

                <button @click="setFilter('daging')" 
                        type="button" 
                        role="tab"
                        :aria-selected="activeFilter === 'daging'"
                        class="px-3.5 sm:px-5 py-2 rounded-modern text-xs sm:text-sm font-semibold whitespace-nowrap transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 shrink-0"
                        :class="activeFilter === 'daging' 
                            ? 'bg-brand-primary text-white shadow-sm' 
                            : 'bg-brand-cream text-brand-dark hover:bg-brand-soft-green border border-gray-200/60'">
                    🥩 Daging Sapi
                </button>

                <button @click="setFilter('ayam')" 
                        type="button" 
                        role="tab"
                        :aria-selected="activeFilter === 'ayam'"
                        class="px-3.5 sm:px-5 py-2 rounded-modern text-xs sm:text-sm font-semibold whitespace-nowrap transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 shrink-0"
                        :class="activeFilter === 'ayam' 
                            ? 'bg-brand-primary text-white shadow-sm' 
                            : 'bg-brand-cream text-brand-dark hover:bg-brand-soft-green border border-gray-200/60'">
                    🍗 Ayam Segar
                </button>

                <button @click="setFilter('ikan')" 
                        type="button" 
                        role="tab"
                        :aria-selected="activeFilter === 'ikan'"
                        class="px-3.5 sm:px-5 py-2 rounded-modern text-xs sm:text-sm font-semibold whitespace-nowrap transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 shrink-0"
                        :class="activeFilter === 'ikan' 
                            ? 'bg-brand-primary text-white shadow-sm' 
                            : 'bg-brand-cream text-brand-dark hover:bg-brand-soft-green border border-gray-200/60'">
                    🐟 Ikan & Seafood
                </button>

                <button @click="setFilter('sayur')" 
                        type="button" 
                        role="tab"
                        :aria-selected="activeFilter === 'sayur'"
                        class="px-3.5 sm:px-5 py-2 rounded-modern text-xs sm:text-sm font-semibold whitespace-nowrap transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 shrink-0"
                        :class="activeFilter === 'sayur' 
                            ? 'bg-brand-primary text-white shadow-sm' 
                            : 'bg-brand-cream text-brand-dark hover:bg-brand-soft-green border border-gray-200/60'">
                    🥦 Sayuran Siap Olah
                </button>

                <button @click="setFilter('ready-to-cook')" 
                        type="button" 
                        role="tab"
                        :aria-selected="activeFilter === 'ready-to-cook'"
                        class="px-3.5 sm:px-5 py-2 rounded-modern text-xs sm:text-sm font-semibold whitespace-nowrap transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 shrink-0"
                        :class="activeFilter === 'ready-to-cook' 
                            ? 'bg-brand-primary text-white shadow-sm' 
                            : 'bg-brand-cream text-brand-dark hover:bg-brand-soft-green border border-gray-200/60'">
                    🍳 Ready to Cook
                </button>

                <button @click="setFilter('curah')" 
                        type="button" 
                        role="tab"
                        :aria-selected="activeFilter === 'curah'"
                        class="px-3.5 sm:px-5 py-2 rounded-modern text-xs sm:text-sm font-semibold whitespace-nowrap transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 shrink-0"
                        :class="activeFilter === 'curah' 
                            ? 'bg-brand-primary text-white shadow-sm' 
                            : 'bg-brand-cream text-brand-dark hover:bg-brand-soft-green border border-gray-200/60'">
                    📦 Pembelian Curah (Bulk)
                </button>
            </div>
        </div>

        <!-- Product Grid: Mobile 2 Cols (grid-cols-2), Tablet 2 Cols, Desktop 4 Cols -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5 lg:gap-6">
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div x-show="matches('<?php echo e($prod['category']); ?>', '<?php echo e($prod['type_category']); ?>')"
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="group bg-white rounded-modern border border-gray-100/90 shadow-sm hover:shadow-card-hover transition-all duration-300 flex flex-col justify-between overflow-hidden">
                
                <!-- Card Top: Image & Simplified Badges -->
                <div class="relative">
                    <div class="aspect-[4/3] w-full overflow-hidden bg-gray-50">
                        <img src="<?php echo e(asset($prod['image'])); ?>" 
                             alt="<?php echo e($prod['name']); ?>" 
                             width="400"
                             height="300"
                             loading="lazy"
                             class="w-full h-full object-cover object-center group-hover:scale-106 transition-transform duration-500 ease-out">
                    </div>
                    
                    <!-- Type Badges Container: 1-2 primary badges -->
                    <div class="absolute top-2 left-2 sm:top-2.5 sm:left-2.5 flex flex-wrap gap-1 max-w-[85%]">
                        <?php if(isset($prod['type_badges'][0])): ?>
                            <span class="inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded text-[9px] sm:text-[10px] font-semibold <?php echo e($prod['type_badges'][0]['class']); ?> shadow-2xs">
                                <?php echo e($prod['type_badges'][0]['text']); ?>

                            </span>
                        <?php endif; ?>
                        <?php if(isset($prod['type_badges'][1])): ?>
                            <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold <?php echo e($prod['type_badges'][1]['class']); ?> shadow-2xs">
                                <?php echo e($prod['type_badges'][1]['text']); ?>

                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Weight Pill -->
                    <div class="absolute bottom-2 right-2">
                        <span class="inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded-md text-[9px] sm:text-[10px] font-semibold bg-black/70 backdrop-blur-xs text-white">
                            <?php echo e($prod['weight']); ?>

                        </span>
                    </div>
                </div>

                <!-- Card Body: Name, Weight, Price, Cart Action Button -->
                <div class="p-3 sm:p-4 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs sm:text-sm md:text-base font-bold text-brand-dark group-hover:text-brand-primary transition-colors line-clamp-2 mb-1 leading-snug">
                            <?php echo e($prod['name']); ?>

                        </h3>
                        <p class="hidden sm:block text-xs text-gray-500 line-clamp-2 leading-relaxed mb-3">
                            <?php echo e($prod['description']); ?>

                        </p>
                    </div>

                    <!-- Bottom Row: Price on Left, Cart Button on Right -->
                    <div class="pt-2.5 sm:pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
                        <div class="flex flex-col">
                            <span class="text-[9px] sm:text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Harga</span>
                            <span class="text-xs sm:text-base lg:text-lg font-extrabold text-brand-primary leading-tight">
                                <?php echo e($prod['price_formatted']); ?>

                            </span>
                        </div>

                        <!-- Add to Cart Action Button -->
                        <button @click="$store.cart.addItem('<?php echo e($prod['id']); ?>', '<?php echo e(addslashes($prod['name'])); ?>', <?php echo e($prod['price']); ?>)" 
                                type="button" 
                                aria-label="Tambahkan <?php echo e($prod['name']); ?> ke pesanan"
                                title="Tambahkan ke pesanan"
                                class="relative w-8 h-8 sm:w-9 sm:h-9 min-w-[32px] min-h-[32px] sm:min-w-[36px] sm:min-h-[36px] rounded-modern-sm flex items-center justify-center text-white bg-brand-primary hover:bg-brand-primary-dark active:scale-90 transition-all duration-200 shadow-2xs hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-primary/40 shrink-0 cursor-pointer after:absolute after:-inset-1.5 after:content-['']"
                                :class="$store.cart.lastAddedId === '<?php echo e($prod['id']); ?>' ? 'scale-110 bg-emerald-600 ring-2 ring-emerald-400' : ''">
                            
                            <!-- Clean Shopping Cart SVG Icon -->
                            <svg x-show="$store.cart.lastAddedId !== '<?php echo e($prod['id']); ?>'" class="w-3.5 h-3.5 sm:w-4 sm:h-4 fill-none stroke-current stroke-2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>

                            <!-- Brief Check Feedback Icon -->
                            <svg x-show="$store.cart.lastAddedId === '<?php echo e($prod['id']); ?>'" x-cloak class="w-3.5 h-3.5 sm:w-4 sm:h-4 fill-none stroke-current stroke-2 text-white animate-bounce" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>

            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

    </div>
</section>
<?php /**PATH C:\xampp\htdocs\sumberproteinjogja\resources\views/components/product-card.blade.php ENDPATH**/ ?>