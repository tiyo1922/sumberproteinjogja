@php
    $isLive = $isLivePreview ?? false;
@endphp

<div class="group relative bg-white rounded-modern-lg overflow-hidden border border-gray-100 shadow-sm hover:shadow-card-hover transition-all duration-300 flex flex-col justify-between h-full">

    <!-- Category Image Container (4:3 Aspect Ratio) -->
    <div class="relative aspect-[4/3] w-full shrink-0 overflow-hidden bg-gray-100">
        <img @if($isLive) :src="getImageUrl(form.image)" :alt="form.name" @else src="{{ asset($cat['image'] ?? 'storage/media/cat_daging_1786889601901.jpg') }}" alt="{{ ($cat['name'] ?? 'Kategori') }} - Sumber Protein Jogja" loading="lazy" @endif
             class="w-full h-full object-cover object-center group-hover:scale-108 transition-transform duration-500 ease-out">

        <!-- Official Gradient Overlay from Landing Page Component -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-60 group-hover:opacity-40 transition-opacity"></div>

        <!-- Top Left Badge (e.g. Sertifikasi Halal) -->
        <div class="absolute top-2.5 left-2.5 sm:top-3 sm:left-3 z-10">
            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] sm:text-xs font-semibold bg-white/90 backdrop-blur-md text-brand-dark shadow-sm"
                  @if($isLive) x-text="form.badge || 'Sertifikasi Halal'" @endif>
                {{ $cat['badge'] ?? 'Sertifikasi Halal' }}
            </span>
        </div>

        <!-- Bottom Right Variation Count Badge (Derived from active product count) -->
        <div class="absolute bottom-2.5 right-2.5 sm:bottom-3 sm:right-3 z-10">
            <span class="inline-block px-2 py-0.5 rounded-md text-[10px] sm:text-xs font-semibold bg-brand-primary text-white shadow-sm"
                  @if($isLive) x-text="getActiveProductCount(form.id) + '+ Variasi'" @endif>
                {{ $cat['count'] ?? '12+ Variasi' }}
            </span>
        </div>
    </div>

    <!-- Category Content Body -->
    <div class="p-3.5 sm:p-5 flex-1 flex flex-col justify-between text-left">
        <div class="flex-1 flex flex-col">
            <!-- Category Title (Priority #1: Full Name, never truncated, reserved 2-line height) -->
            <h3 class="text-sm sm:text-base lg:text-lg font-bold text-brand-dark group-hover:text-brand-primary transition-colors mb-1 leading-snug min-h-[2.5rem] sm:min-h-[2.75rem] flex items-start"
                @if($isLive) x-text="form.name || 'Daging Sapi'" @endif>
                {{ $cat['name'] ?? 'Daging Sapi' }}
            </h3>

            <!-- Category Subtitle (Brand Green Accent, consistent min height) -->
            <p class="text-[11px] sm:text-xs md:text-sm font-medium text-brand-primary-light mb-1.5 line-clamp-1 min-h-[1.125rem] sm:min-h-[1.25rem]"
               @if($isLive) x-text="form.subtitle || 'Slice, Sengkel, Ribeye & Giling'" @endif>
                {{ $cat['subtitle'] ?? 'Slice, Sengkel, Ribeye & Giling' }}
            </p>

            <!-- Category Description (Secondary, line-clamp allowed) -->
            <p class="text-[11px] sm:text-xs text-gray-500 line-clamp-2 leading-relaxed mb-3 flex-1"
               @if($isLive) x-text="form.description || 'Daging sapi segar & frozen potongan higienis tanpa pengawet.'" @endif>
                {{ $cat['description'] ?? 'Daging sapi segar & frozen potongan higienis tanpa pengawet.' }}
            </p>
        </div>

        <!-- Action Link with Divider (Always pinned to bottom with mt-auto) -->
        <div class="mt-auto pt-2.5 sm:pt-3 border-t border-gray-100 flex items-center justify-between text-xs sm:text-sm font-semibold text-brand-primary group-hover:text-brand-primary-dark">
            <span>Lihat Varian</span>
            <span class="group-hover:translate-x-1 transition-transform">→</span>
        </div>
    </div>

    <!-- Clickable Overlay Link to filter catalog (only on landing page) -->
    @if(!$isLive)
    <a href="#produk"
       @click="$dispatch('filter-category', { category_id: {{ $cat['id'] ?? 1 }} })"
       class="absolute inset-0 z-10 focus:outline-none focus:ring-2 focus:ring-brand-primary rounded-modern-lg"
       aria-label="Lihat produk kategori {{ $cat['name'] ?? '' }}">
    </a>
    @endif
</div>
