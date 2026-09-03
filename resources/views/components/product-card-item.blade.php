@php
    $isLive = $isLivePreview ?? false;
@endphp

<div class="h-full group bg-white rounded-modern border border-gray-100/90 shadow-sm hover:shadow-card-hover transition-all duration-300 flex flex-col justify-between overflow-hidden text-left">

    <!-- Card Top: Image & Badges Container (4:3 Aspect Ratio) -->
    <div class="relative">
        <div class="aspect-[4/3] w-full overflow-hidden bg-gray-50">
            <img @if($isLive) :src="getImageUrl(form.image)" :alt="form.name" @else src="{{ asset($prod['image'] ?? 'storage/media/prod_beef_slice_1786890263309.jpg') }}" alt="{{ $prod['name'] ?? 'Produk' }} - Sumber Protein Jogja" width="400" height="300" loading="lazy" @endif
                 class="w-full h-full object-cover object-center group-hover:scale-106 transition-transform duration-500 ease-out">
        </div>

        <!-- Type Badges Container: 1-2 primary badges on LP / all selected badges in Live Preview -->
        <div class="absolute top-2 left-2 sm:top-2.5 sm:left-2.5 flex flex-wrap gap-1 max-w-[85%] z-10">
            @if($isLive)
                <template x-for="t in (form.types || [])" :key="t">
                    <span class="inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded text-[9px] sm:text-[10px] font-semibold shadow-2xs"
                          :style="typeof getBadgeStyle === 'function' ? getBadgeStyle(t) : ''"
                          :class="{
                              'badge-frozen': t === 'Frozen',
                              'badge-ready': t === 'Ready to Cook',
                              'badge-fresh': t === 'Fresh',
                              'badge-accent': t === 'Berbumbu',
                              'badge-bulk': t === 'Curah',
                              'badge-primary': t === 'Plain'
                          }"
                          x-text="t">
                    </span>
                </template>
            @else
                @if(isset($prod['type_badges'][0]))
                    <span class="inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded text-[9px] sm:text-[10px] font-semibold {{ $prod['type_badges'][0]['class'] }} shadow-2xs">
                        {{ $prod['type_badges'][0]['text'] }}
                    </span>
                @endif
                @if(isset($prod['type_badges'][1]))
                    <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $prod['type_badges'][1]['class'] }} shadow-2xs">
                        {{ $prod['type_badges'][1]['text'] }}
                    </span>
                @endif
            @endif
        </div>

        <!-- Weight Pill -->
        <div class="absolute bottom-2 right-2 z-10">
            <span class="inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded-md text-[9px] sm:text-[10px] font-semibold bg-black/70 backdrop-blur-xs text-white"
                  @if($isLive) x-text="(form.weight_value !== undefined && form.weight_value !== null && form.weight_value !== '') ? (form.weight_value + (form.unit === 'gram' ? 'g' : (form.unit === 'kg' ? 'kg' : ' ' + form.unit))) : (form.weight || '500g')" @endif>
                {{ $prod['weight'] ?? '500g' }}
            </span>
        </div>
    </div>

    <!-- Card Body: Name, Description, Price, Cart Action Button -->
    <div class="p-3 sm:p-4 flex-1 flex flex-col justify-between">
        <div>
            <!-- Product Title -->
            <h3 class="text-xs sm:text-sm md:text-base font-bold text-brand-dark group-hover:text-brand-primary transition-colors line-clamp-2 mb-1 leading-snug"
                @if($isLive) x-text="form.name || 'Nama Produk Pilihan'" @endif>
                {{ $prod['name'] ?? 'Nama Produk Pilihan' }}
            </h3>

            <!-- Product Description (Hidden on small mobile for clean aesthetic) -->
            <p class="hidden sm:block text-xs text-gray-500 line-clamp-2 leading-relaxed mb-3"
               @if($isLive) x-text="form.description || 'Deskripsi potongan daging segar, higienis dan siap masak.'" @endif>
                {{ $prod['description'] ?? 'Deskripsi potongan daging segar, higienis dan siap masak.' }}
            </p>
        </div>

        <!-- Bottom Row: Price on Left, Cart Button on Right -->
        <div class="pt-2.5 sm:pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
            <div class="flex flex-col">
                <span class="text-[9px] sm:text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Harga</span>
                <span class="text-xs sm:text-base lg:text-lg font-extrabold text-brand-primary leading-tight"
                      @if($isLive) x-text="'Rp ' + Number(form.price || 0).toLocaleString('id-ID')" @endif>
                    {{ $prod['price_formatted'] ?? ('Rp ' . number_format($prod['effective_price'] ?? ($prod['price'] ?? 0), 0, ',', '.')) }}
                </span>
            </div>

            @php
                $stockStatus = $prod['stock_status'] ?? 'READY_STOCK';
            @endphp

            @if($stockStatus === 'OUT_OF_STOCK')
                <!-- Locked Out of Stock Button -->
                <button type="button"
                        disabled
                        aria-label="Stok Habis"
                        title="Stok Habis"
                        class="px-2.5 py-1 rounded-modern-sm text-[10px] font-bold text-gray-400 bg-gray-100 cursor-not-allowed border border-gray-200 shrink-0">
                    Stok Habis
                </button>
            @elseif($stockStatus === 'PRE_ORDER')
                <!-- Pre-Order Action Button -->
                <button @if(!$isLive) @click="$store.cart.addItem('{{ $prod['id'] }}', '{{ addslashes($prod['name']) }}', {{ $prod['effective_price'] ?? ($prod['price'] ?? 0) }}, 'PRE_ORDER')" @endif
                        type="button"
                        aria-label="Pesan Pre-Order"
                        title="Pesan Pre-Order"
                        class="px-2.5 py-1 rounded-modern-sm text-[10px] font-bold text-amber-900 bg-amber-100 hover:bg-amber-200 border border-amber-300 transition-colors shrink-0 cursor-pointer shadow-2xs">
                    Pre-Order
                </button>
            @else
                <!-- Standard Add to Cart Action Button -->
                <button @if(!$isLive) @click="$store.cart.addItem('{{ $prod['id'] }}', '{{ addslashes($prod['name']) }}', {{ $prod['effective_price'] ?? ($prod['price'] ?? 0) }})" @endif
                        type="button"
                        aria-label="Tambahkan ke pesanan"
                        title="Tambahkan ke pesanan"
                        class="relative w-8 h-8 sm:w-9 sm:h-9 min-w-[32px] min-h-[32px] sm:min-w-[36px] sm:min-h-[36px] rounded-modern-sm flex items-center justify-center text-white bg-brand-primary hover:bg-brand-primary-dark active:scale-90 transition-all duration-200 shadow-2xs hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-primary/40 shrink-0 cursor-pointer after:absolute after:-inset-1.5 after:content-['']"
                        @if(!$isLive) :class="$store.cart.lastAddedId === '{{ $prod['id'] }}' ? 'scale-110 bg-emerald-600 ring-2 ring-emerald-400' : ''" @endif>

                    <!-- Clean Shopping Cart SVG Icon -->
                    <svg @if(!$isLive) x-show="$store.cart.lastAddedId !== '{{ $prod['id'] }}'" @endif
                         class="w-3.5 h-3.5 sm:w-4 sm:h-4 fill-none stroke-current stroke-2"
                         viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>

                    @if(!$isLive)
                    <!-- Brief Check Feedback Icon -->
                    <svg x-show="$store.cart.lastAddedId === '{{ $prod['id'] }}'" x-cloak
                         class="w-3.5 h-3.5 sm:w-4 sm:h-4 fill-none stroke-current stroke-2 text-white animate-bounce"
                         viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    @endif
                </button>
            @endif
        </div>
    </div>

</div>
