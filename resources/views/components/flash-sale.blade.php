@if(!empty($flashSaleSetting['is_active']) && isset($flashSaleProducts) && count($flashSaleProducts) > 0)
@php
    $featuredProduct = $flashSaleProducts->first();
    $supportingProducts = $flashSaleProducts->slice(1);
@endphp
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
         class="py-8 sm:py-10 lg:py-12 bg-gradient-to-b from-orange-50/40 via-amber-50/15 to-white relative overflow-hidden">
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        
        <!-- PROMOTIONAL CONTENT HOOK COMPOSITION -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 xl:gap-16 items-center">
            
            <!-- ========================================================= -->
            <!-- 1. LEFT / PROMO STORY & COUNTDOWN URGENCY                 -->
            <!-- ========================================================= -->
            <div class="lg:col-span-5 space-y-6 sm:space-y-7">
                
                <!-- Promo Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-orange-600 text-white text-xs font-black uppercase tracking-wider shadow-sm">
                    <span class="animate-pulse text-sm">⚡</span>
                    <span>SALE SEKARANG!</span>
                </div>

                <!-- Main Campaign Headline & Subtitle -->
                <div class="space-y-3">
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-brand-dark tracking-tight leading-[1.12]">
                        {{ $flashSaleSetting['title'] ?? 'Flash Sale Terbatas!' }}
                    </h2>
                    <p class="text-sm sm:text-base text-gray-600 leading-relaxed max-w-lg">
                        {{ $flashSaleSetting['subtitle'] ?? 'Dapatkan potongan harga spesial untuk produk protein pilihan hari ini. Stok terbatas, pesan sekarang sebelum kehabisan!' }}
                    </p>
                </div>

                <!-- Urgency Countdown Box (Clean, High Contrast, Number-Focused) -->
                <div class="p-5 rounded-2xl bg-white border border-orange-200/90 shadow-sm space-y-3 max-w-md">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                        <span class="text-xs sm:text-sm font-black text-brand-dark uppercase tracking-wider flex items-center gap-1.5">
                            <span class="text-orange-500 text-base">⏰</span> BERAKHIR DALAM:
                        </span>
                        <span class="text-[10px] font-extrabold text-orange-700 bg-orange-100/80 px-2.5 py-0.5 rounded-full">
                            STOK TERBATAS
                        </span>
                    </div>

                    <!-- Digital Block Countdown Numbers -->
                    <div class="flex items-center justify-center gap-2 sm:gap-3 pt-1">
                        <!-- Jam -->
                        <div class="flex flex-col items-center">
                            <div class="w-14 sm:w-16 h-13 sm:h-15 bg-[#064e3b] text-white font-mono font-black text-2xl sm:text-3xl rounded-xl flex items-center justify-center shadow-xs" x-text="hours">00</div>
                            <span class="text-[10px] font-bold text-gray-500 mt-1 uppercase tracking-wider">Jam</span>
                        </div>
                        
                        <span class="font-black text-2xl sm:text-3xl text-orange-500 -mt-4">:</span>
                        
                        <!-- Menit -->
                        <div class="flex flex-col items-center">
                            <div class="w-14 sm:w-16 h-13 sm:h-15 bg-[#064e3b] text-white font-mono font-black text-2xl sm:text-3xl rounded-xl flex items-center justify-center shadow-xs" x-text="minutes">00</div>
                            <span class="text-[10px] font-bold text-gray-500 mt-1 uppercase tracking-wider">Menit</span>
                        </div>
                        
                        <span class="font-black text-2xl sm:text-3xl text-orange-500 -mt-4">:</span>
                        
                        <!-- Detik -->
                        <div class="flex flex-col items-center">
                            <div class="w-14 sm:w-16 h-13 sm:h-15 bg-[#064e3b] text-white font-mono font-black text-2xl sm:text-3xl rounded-xl flex items-center justify-center shadow-xs" x-text="seconds">00</div>
                            <span class="text-[10px] font-bold text-gray-500 mt-1 uppercase tracking-wider">Detik</span>
                        </div>
                    </div>
                </div>

                <!-- Proof & Value Badges -->
                <div class="flex flex-wrap items-center gap-4 pt-1 text-xs text-gray-600 font-semibold">
                    <div class="inline-flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-[11px] font-black shrink-0">✓</span>
                        <span>100% Halal &amp; Higienis</span>
                    </div>
                    <div class="inline-flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-[11px] font-black shrink-0">✓</span>
                        <span>Kirim Cepat Area Jogja</span>
                    </div>
                </div>

            </div>

            <!-- ========================================================= -->
            <!-- 2. RIGHT / GRAND HERO PRODUCT & SUPPORTING OFFERS         -->
            <!-- ========================================================= -->
            <div class="lg:col-span-7 space-y-6">
                
                <!-- FEATURED GRAND HERO PRODUCT CARD -->
                @if($featuredProduct)
                <div class="bg-white rounded-3xl p-5 sm:p-7 shadow-xl border border-gray-100/90 relative overflow-hidden group hover:shadow-2xl transition-all duration-300">
                    
                    <!-- Top Section: Large Appetizing Product Image with Floating Discount Badge -->
                    <div class="relative w-full aspect-[4/3] sm:aspect-[16/10] rounded-2xl overflow-hidden bg-gray-50 mb-5">
                        <!-- Floating Discount Badge Top Left -->
                        <div class="absolute top-3.5 left-3.5 z-20">
                            <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs sm:text-sm font-black bg-red-600 text-white shadow-md">
                                @if($featuredProduct->flash_sale_discount_type === 'percentage')
                                    {{ (int) $featuredProduct->flash_sale_discount_value }}% OFF
                                @elseif($featuredProduct->flash_sale_discount_type === 'fixed')
                                    HEMAT Rp {{ number_format($featuredProduct->flash_sale_discount_value, 0, ',', '.') }}
                                @else
                                    FLASH SALE
                                @endif
                            </span>
                        </div>

                        <!-- Weight Badge Top Right -->
                        <div class="absolute top-3.5 right-3.5 z-20">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] sm:text-xs font-bold bg-black/70 backdrop-blur-xs text-white">
                                {{ $featuredProduct->weight_value ? ($featuredProduct->weight_value . ($featuredProduct->unit === 'gram' ? 'g' : ' ' . $featuredProduct->unit)) : ($featuredProduct->weight ?? '500g') }}
                            </span>
                        </div>

                        <img src="{{ asset($featuredProduct->image ?? 'images/prod-beef-slice.jpg') }}" 
                             alt="{{ $featuredProduct->name }} - Flash Sale Sumber Protein Jogja" 
                             width="600" 
                             height="400" 
                             loading="lazy" 
                             class="w-full h-full object-cover object-center group-hover:scale-104 transition-transform duration-500 ease-out">
                    </div>

                    <!-- Bottom Section: Product Info, Big Price, and Direct CTA -->
                    <div class="space-y-4">
                        <div>
                            @if($featuredProduct->category)
                            <span class="text-[11px] font-black text-orange-600 uppercase tracking-wider block mb-1">
                                {{ $featuredProduct->category->name }}
                            </span>
                            @endif

                            <h3 class="text-xl sm:text-2xl font-black text-brand-dark group-hover:text-brand-primary transition-colors leading-snug">
                                {{ $featuredProduct->name }}
                            </h3>

                            @if($featuredProduct->description)
                            <p class="text-xs sm:text-sm text-gray-500 line-clamp-2 mt-1 leading-relaxed">
                                {{ $featuredProduct->description }}
                            </p>
                            @endif
                        </div>

                        <!-- Price Row & Full Primary CTA -->
                        <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <!-- Price Block -->
                            <div>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Harga Promo Kilat:</span>
                                <div class="flex items-baseline gap-2.5 mt-0.5">
                                    <span class="text-2xl sm:text-3xl lg:text-4xl font-black text-red-600 tracking-tight">
                                        Rp {{ number_format($featuredProduct->flash_sale_effective_price, 0, ',', '.') }}
                                    </span>
                                    <del class="text-xs sm:text-sm text-gray-400 font-semibold line-through">
                                        Rp {{ number_format($featuredProduct->normal_price, 0, ',', '.') }}
                                    </del>
                                </div>
                            </div>

                            <!-- Primary CTA Button -->
                            @if($featuredProduct->stock_status !== 'OUT_OF_STOCK')
                            <button @click="$store.cart.addItem('{{ $featuredProduct->id }}', '{{ addslashes($featuredProduct->name) }} (Flash Sale)', {{ $featuredProduct->flash_sale_effective_price }})"
                                    type="button" 
                                    class="sm:w-auto px-7 py-3.5 rounded-xl font-black text-sm text-white bg-brand-primary hover:bg-brand-primary-dark active:scale-98 transition-all cursor-pointer shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 fill-none stroke-current stroke-2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                                <span>Beli Sekarang</span>
                            </button>
                            @else
                            <button disabled 
                                    type="button" 
                                    class="sm:w-auto px-7 py-3.5 rounded-xl font-bold text-sm text-gray-400 bg-gray-100 cursor-not-allowed text-center">
                                Stok Promo Habis
                            </button>
                            @endif
                        </div>
                    </div>

                </div>
                @endif

                <!-- SUPPORTING OFFERS (Lightweight, Secondary, Clean Mini-Cards) -->
                @if($supportingProducts->count() > 0)
                <div class="space-y-3 pt-2">
                    <div class="flex items-center justify-between px-1">
                        <h4 class="text-xs font-black text-brand-dark uppercase tracking-wider flex items-center gap-1.5">
                            <span class="text-orange-500">🔥</span> Promo Kilat Lainnya
                        </h4>
                        <span class="text-[11px] font-semibold text-gray-500">
                            {{ $supportingProducts->count() }} Penawaran Tambahan
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($supportingProducts as $supProd)
                        <div class="bg-white/90 hover:bg-white rounded-2xl p-3 border border-gray-200/80 shadow-xs hover:shadow-md transition-all flex items-center justify-between gap-3 group">
                            
                            <div class="flex items-center gap-3 min-w-0">
                                <!-- Mini Thumbnail with Badge -->
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl overflow-hidden bg-gray-50 shrink-0 relative border border-gray-100">
                                    <img src="{{ asset($supProd->image ?? 'images/prod-beef-slice.jpg') }}" 
                                         alt="{{ $supProd->name }}" 
                                         class="w-full h-full object-cover group-hover:scale-106 transition-transform duration-300">
                                    
                                    <!-- Mini Discount Pill -->
                                    <div class="absolute bottom-0 left-0 right-0 bg-red-600 text-white text-[8px] font-black text-center py-0.5 leading-none">
                                        @if($supProd->flash_sale_discount_type === 'percentage')
                                            {{ (int) $supProd->flash_sale_discount_value }}% OFF
                                        @else
                                            PROMO
                                        @endif
                                    </div>
                                </div>

                                <!-- Product Info & Price -->
                                <div class="min-w-0">
                                    <h5 class="text-xs font-black text-brand-dark truncate group-hover:text-brand-primary transition-colors">
                                        {{ $supProd->name }}
                                    </h5>
                                    <div class="flex items-baseline gap-1.5 mt-0.5">
                                        <span class="text-xs sm:text-sm font-black text-red-600">
                                            Rp {{ number_format($supProd->flash_sale_effective_price, 0, ',', '.') }}
                                        </span>
                                        <del class="text-[10px] text-gray-400 font-semibold line-through">
                                            Rp {{ number_format($supProd->normal_price, 0, ',', '.') }}
                                        </del>
                                    </div>
                                </div>
                            </div>

                            <!-- Mini Add to Cart Button -->
                            @if($supProd->stock_status !== 'OUT_OF_STOCK')
                            <button @click="$store.cart.addItem('{{ $supProd->id }}', '{{ addslashes($supProd->name) }} (Flash Sale)', {{ $supProd->flash_sale_effective_price }})"
                                    type="button" 
                                    aria-label="Beli {{ $supProd->name }}"
                                    title="Tambah ke Keranjang"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-white bg-brand-primary hover:bg-brand-primary-dark active:scale-90 transition-all shadow-2xs shrink-0 cursor-pointer">
                                <svg class="w-3.5 h-3.5 fill-none stroke-current stroke-2" viewBox="0 0 24 24">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                            </button>
                            @else
                            <span class="text-[9px] font-bold text-gray-400 px-1.5 py-0.5 rounded bg-gray-100 shrink-0">Habis</span>
                            @endif

                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

        </div>

    </div>
</section>
@endif
