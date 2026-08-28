@if(!empty($flashSaleSetting['is_active']) && isset($flashSaleProducts) && count($flashSaleProducts) > 0)
<section id="flash-sale" 
         x-data="{
             targetTime: new Date('{{ $flashSaleSetting['end_at'] }}').getTime(),
             hours: '00',
             minutes: '00',
             seconds: '00',
             expired: false,
             updateCountdown() {
                 const now = new Date().getTime();
                 const diff = this.targetTime - now;
                 if (diff <= 0) {
                     this.hours = '00';
                     this.minutes = '00';
                     this.seconds = '00';
                     this.expired = true;
                     return;
                 }
                 const h = Math.floor(diff / (1000 * 60 * 60));
                 const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                 const s = Math.floor((diff % (1000 * 60)) / 1000);
                 this.hours = String(h).padStart(2, '0');
                 this.minutes = String(m).padStart(2, '0');
                 this.seconds = String(s).padStart(2, '0');
             },
             init() {
                 this.updateCountdown();
                 setInterval(() => { this.updateCountdown(); }, 1000);
             }
         }"
         x-show="!expired"
         class="py-12 sm:py-16 bg-gradient-to-b from-red-50/60 via-amber-50/30 to-white relative overflow-hidden border-y border-red-100/60">
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Flash Sale Header Container -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8 sm:mb-10">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold uppercase tracking-wider mb-3">
                    <span class="animate-pulse text-base">⚡</span>
                    <span>Promo Terbatas</span>
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-brand-dark tracking-tight">
                    {{ $flashSaleSetting['title'] ?? 'Flash Sale Spesial Hari Ini' }}
                </h2>
                <p class="mt-2 text-sm sm:text-base text-gray-600">
                    {{ $flashSaleSetting['subtitle'] ?? 'Dapatkan potongan harga spesial untuk produk protein pilihan hari ini. Stok terbatas!' }}
                </p>
            </div>

            <!-- Dynamic Countdown Timer -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0 bg-white/90 backdrop-blur-xs px-4 py-3 sm:px-5 sm:py-3.5 rounded-modern-xl border border-red-200/80 shadow-sm">
                <span class="text-xs sm:text-sm font-bold text-gray-700 mr-1">Berakhir dalam:</span>
                
                <div class="flex items-center gap-1.5">
                    <div class="bg-red-600 text-white font-mono font-extrabold text-sm sm:text-base px-2.5 py-1.5 rounded-modern-sm shadow-2xs" x-text="hours">00</div>
                    <span class="font-bold text-red-600">:</span>
                    <div class="bg-red-600 text-white font-mono font-extrabold text-sm sm:text-base px-2.5 py-1.5 rounded-modern-sm shadow-2xs" x-text="minutes">00</div>
                    <span class="font-bold text-red-600">:</span>
                    <div class="bg-red-600 text-white font-mono font-extrabold text-sm sm:text-base px-2.5 py-1.5 rounded-modern-sm shadow-2xs" x-text="seconds">00</div>
                </div>
            </div>
        </div>

        <!-- Flash Sale Product Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($flashSaleProducts as $prod)
            <div class="group bg-white rounded-modern-lg border border-red-100 shadow-sm hover:shadow-card-hover transition-all duration-300 flex flex-col justify-between overflow-hidden text-left relative">
                
                <!-- Discount Badge Top Left -->
                <div class="absolute top-2 left-2 z-20">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-extrabold bg-red-600 text-white shadow-sm">
                        @if($prod->flash_sale_discount_type === 'percentage')
                            {{ (int) $prod->flash_sale_discount_value }}% OFF
                        @elseif($prod->flash_sale_discount_type === 'fixed')
                            HEMAT Rp {{ number_format($prod->flash_sale_discount_value, 0, ',', '.') }}
                        @else
                            FLASH SALE
                        @endif
                    </span>
                </div>

                <!-- Product Image -->
                <div class="relative aspect-[4/3] w-full overflow-hidden bg-gray-50">
                    <img src="{{ asset($prod->image ?? 'images/prod-beef-slice.jpg') }}" 
                         alt="{{ $prod->name }} - Flash Sale Sumber Protein Jogja" 
                         width="400" 
                         height="300" 
                         loading="lazy" 
                         class="w-full h-full object-cover object-center group-hover:scale-106 transition-transform duration-500 ease-out">
                    
                    <!-- Weight Pill -->
                    <div class="absolute bottom-2 right-2 z-10">
                        <span class="inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded-md text-[9px] sm:text-[10px] font-semibold bg-black/70 backdrop-blur-xs text-white">
                            {{ $prod->weight_value ? ($prod->weight_value . ($prod->unit === 'gram' ? 'g' : ' ' . $prod->unit)) : ($prod->weight ?? '500g') }}
                        </span>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="p-3 sm:p-4 flex-1 flex flex-col justify-between">
                    <div>
                        <!-- Category Badge -->
                        @if($prod->category)
                        <span class="text-[10px] sm:text-xs font-semibold text-brand-primary uppercase tracking-wider block mb-1">
                            {{ $prod->category->name }}
                        </span>
                        @endif

                        <h3 class="text-xs sm:text-sm md:text-base font-bold text-brand-dark group-hover:text-brand-primary transition-colors line-clamp-2 mb-1.5 leading-snug">
                            {{ $prod->name }}
                        </h3>

                        <!-- Stock Status Badge if not Ready Stock -->
                        @if($prod->stock_status === 'OUT_OF_STOCK')
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600 mb-2">Stok Habis</span>
                        @elseif($prod->stock_status === 'PRE_ORDER')
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 mb-2">Pre-Order</span>
                        @endif
                    </div>

                    <!-- Price Row & Cart Action -->
                    <div class="pt-2.5 border-t border-gray-100 flex items-center justify-between gap-2 mt-2">
                        <div class="flex flex-col">
                            <del class="text-[10px] sm:text-xs text-gray-400 font-medium line-through">
                                Rp {{ number_format($prod->normal_price, 0, ',', '.') }}
                            </del>
                            <span class="text-sm sm:text-base lg:text-lg font-extrabold text-red-600 leading-tight">
                                Rp {{ number_format($prod->flash_sale_effective_price, 0, ',', '.') }}
                            </span>
                        </div>

                        <!-- Add to Cart CTA -->
                        @if($prod->stock_status !== 'OUT_OF_STOCK')
                        <button @click="$store.cart.addItem('{{ $prod->id }}', '{{ addslashes($prod->name) }} (Flash Sale)', {{ $prod->flash_sale_effective_price }})"
                                type="button" 
                                aria-label="Beli produk Flash Sale"
                                title="Beli Flash Sale"
                                class="relative w-8 h-8 sm:w-9 sm:h-9 min-w-[32px] min-h-[32px] sm:min-w-[36px] sm:min-h-[36px] rounded-modern-sm flex items-center justify-center text-white bg-red-600 hover:bg-red-700 active:scale-90 transition-all duration-200 shadow-2xs hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-red-400/50 shrink-0 cursor-pointer">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 fill-none stroke-current stroke-2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                        </button>
                        @else
                        <button disabled 
                                type="button" 
                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-modern-sm flex items-center justify-center text-gray-400 bg-gray-100 cursor-not-allowed">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 fill-none stroke-current stroke-2" viewBox="0 0 24 24">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                        </button>
                        @endif
                    </div>
                </div>

            </div>
            @endforeach
        </div>

    </div>
</section>
@endif
