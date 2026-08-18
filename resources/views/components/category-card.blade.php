<section id="kategori" class="py-16 sm:py-24 bg-brand-cream/60 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-16">
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

        <!-- Categories Grid: Desktop 4 cols, Tablet 2x2, Mobile 2x2 -->
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 lg:gap-8">
            @foreach($categories as $cat)
                @if(($cat['status'] ?? '') === 'active_landing' || ($cat['status'] ?? '') === 'Aktif')
                    @include('components.category-card-item', ['cat' => $cat, 'isLivePreview' => false])
                @endif
            @endforeach
        </div>

    </div>
</section>
