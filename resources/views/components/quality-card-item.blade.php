@php
    $isLive = $isLivePreview ?? false;
@endphp

<div class="bg-white p-5 sm:p-8 rounded-modern-lg border border-gray-100 shadow-sm hover:shadow-card transition-all duration-300 flex flex-col justify-between">
    <div>
        <div class="flex items-center justify-between mb-3.5 sm:mb-4 gap-2 flex-wrap">
            <h3 class="text-base sm:text-lg lg:text-xl font-bold text-brand-dark"
                @if($isLive) x-text="pk.name" @endif>
                {{ $pk['name'] ?? '' }}
            </h3>
            <span class="px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full text-[10px] sm:text-xs font-semibold bg-brand-soft-green text-brand-primary"
                  @if($isLive) x-text="pk.tag" @endif>
                {{ $pk['tag'] ?? '' }}
            </span>
        </div>

        <p class="text-xs sm:text-sm text-gray-600 leading-relaxed mb-5 sm:mb-6"
           @if($isLive) x-text="pk.desc" @endif>
            {{ $pk['desc'] ?? '' }}
        </p>

        <!-- Features Checklist -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-2.5 mb-5 sm:mb-6">
            @if($isLive)
                <template x-for="(feat, fIdx) in pk.features" :key="fIdx">
                    <div class="flex items-center gap-2" x-show="feat && feat.trim()">
                        <svg class="w-4 h-4 text-brand-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-xs font-medium text-brand-dark" x-text="feat"></span>
                    </div>
                </template>
            @else
                @foreach($pk['features'] ?? [] as $feat)
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-xs font-medium text-brand-dark">{{ $feat }}</span>
                </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- CTA Link -->
    <div class="pt-3.5 sm:pt-4 border-t border-gray-100 flex items-center justify-between">
        @if($isLive)
            <a :href="'https://wa.me/6281234567890?text=Halo%20Sumber%20Protein%20Jogja,%20saya%20ingin%20tanya%20detail%20kategori%20' + encodeURIComponent(pk.name)" 
               target="_blank" 
               rel="noopener noreferrer"
               class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-brand-primary hover:text-brand-primary-dark transition-colors">
                <span x-text="'Konsultasi Produk ' + pk.name"></span>
                <span>→</span>
            </a>
        @else
            <a href="https://wa.me/6281234567890?text=Halo%20Sumber%20Protein%20Jogja,%20saya%20ingin%20tanya%20detail%20kategori%20{{ urlencode($pk['name'] ?? '') }}" 
               target="_blank" 
               rel="noopener noreferrer"
               class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-brand-primary hover:text-brand-primary-dark transition-colors">
                <span>Konsultasi Produk {{ $pk['name'] ?? '' }}</span>
                <span>→</span>
            </a>
        @endif
    </div>
</div>
