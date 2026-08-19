<section id="keunggulan" class="py-16 sm:py-24 bg-brand-cream/80 border-y border-gray-200/50 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-16">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-3">
                {{ $benefitsSection['badge'] ?? 'Kenapa Memilih Kami' }}
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-brand-dark tracking-tight mb-3">
                {{ $benefitsSection['title'] ?? 'Lebih Praktis, Lebih Siap' }}
            </h2>
            <p class="text-sm sm:text-base text-gray-600 font-normal">
                {{ $benefitsSection['subtitle'] ?? 'Komitmen kami menghadirkan bahan makanan segar dan frozen bermutu tinggi untuk memudahkan dapur rumah tangga dan operasional usaha Anda di Yogyakarta.' }}
            </p>
        </div>

        <!-- 4 Benefits Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
            @foreach($benefits as $item)
                @include('components.benefit-card-item', ['item' => $item, 'isLivePreview' => false])
            @endforeach
        </div>

    </div>
</section>
