<section id="tentang" class="py-16 sm:py-24 bg-brand-cream/60 border-t border-gray-200/60 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-3">
                {{ $qualitySection['badge'] ?? 'Standar Mutu' }}
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-brand-dark tracking-tight mb-3">
                {{ $qualitySection['title'] ?? 'Mengenal Standar Produk Kami' }}
            </h2>
            <p class="text-sm sm:text-base text-gray-600 font-normal">
                {{ $qualitySection['subtitle'] ?? 'Setiap produk yang keluar dari fasilitas penyimpanan Sumber Protein Jogja melewati proses seleksi ketat untuk menjamin keamanan pangan keluarga Anda.' }}
            </p>
        </div>

        <!-- 4 Categories In-depth Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
            @foreach($productKnowledge as $pk)
                @include('components.quality-card-item', ['pk' => $pk, 'isLivePreview' => false])
            @endforeach
        </div>

    </div>
</section>
