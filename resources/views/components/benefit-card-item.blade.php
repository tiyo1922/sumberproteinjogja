@php
    $isLive = $isLivePreview ?? false;
@endphp

<div class="bg-white p-5 sm:p-7 rounded-modern-lg border border-gray-100/80 shadow-sm hover:shadow-card transition-all duration-300 flex flex-col justify-between group">
    <div>
        <!-- Benefit Icon Container with Hover Animation -->
        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-modern bg-brand-soft-green text-brand-primary flex items-center justify-center mb-4 sm:mb-5 group-hover:scale-110 group-hover:bg-brand-primary group-hover:text-white transition-all duration-300 shadow-sm">
            @if($isLive)
                <!-- Live Icon Switcher via Alpine.js -->
                <template x-if="item.icon === 'grid'">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="3" width="7" height="7" rx="1.5" />
                        <rect x="14" y="3" width="7" height="7" rx="1.5" />
                        <rect x="14" y="14" width="7" height="7" rx="1.5" />
                        <rect x="3" y="14" width="7" height="7" rx="1.5" />
                    </svg>
                </template>
                <template x-if="item.icon === 'shield'">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </template>
                <template x-if="item.icon === 'clock'">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3" />
                    </svg>
                </template>
                <template x-if="item.icon === 'truck'">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 17a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 17h1.5a3 3 0 005 0H15a3 3 0 005 0h1a1 1 0 001-1V9a1 1 0 00-1-1h-3.5L15 4H4a1 1 0 00-1 1v11a1 1 0 001 1z" />
                    </svg>
                </template>
            @else
                @if(($item['icon'] ?? 'grid') === 'grid')
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="3" width="7" height="7" rx="1.5" />
                        <rect x="14" y="3" width="7" height="7" rx="1.5" />
                        <rect x="14" y="14" width="7" height="7" rx="1.5" />
                        <rect x="3" y="14" width="7" height="7" rx="1.5" />
                    </svg>
                @elseif(($item['icon'] ?? '') === 'shield')
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                @elseif(($item['icon'] ?? '') === 'clock')
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3" />
                    </svg>
                @elseif(($item['icon'] ?? '') === 'truck')
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 17a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 17h1.5a3 3 0 005 0H15a3 3 0 005 0h1a1 1 0 001-1V9a1 1 0 00-1-1h-3.5L15 4H4a1 1 0 00-1 1v11a1 1 0 001 1z" />
                    </svg>
                @else
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="3" width="7" height="7" rx="1.5" />
                        <rect x="14" y="3" width="7" height="7" rx="1.5" />
                        <rect x="14" y="14" width="7" height="7" rx="1.5" />
                        <rect x="3" y="14" width="7" height="7" rx="1.5" />
                    </svg>
                @endif
            @endif
        </div>

        <!-- Benefit Title -->
        <h3 class="text-sm sm:text-base lg:text-lg font-bold text-brand-dark mb-1.5 sm:mb-2 group-hover:text-brand-primary transition-colors"
            @if($isLive) x-text="item.title" @endif>
            {{ $item['title'] ?? '' }}
        </h3>

        <!-- Benefit Description -->
        <p class="text-xs sm:text-sm text-gray-500 leading-relaxed"
           @if($isLive) x-text="item.desc || item.subtitle" @endif>
            {{ $item['desc'] ?? ($item['subtitle'] ?? '') }}
        </p>
    </div>
</div>
