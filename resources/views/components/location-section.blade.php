@php
    $location = $location ?? config('location');
    $site = $site ?? config('site');
@endphp
<section id="lokasi" class="py-16 sm:py-24 bg-brand-cream/80 border-t border-gray-200/60 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-3">
                {{ $location['section']['badge'] ?? 'Kunjungi Outlet' }}
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-brand-dark tracking-tight mb-3">
                {{ $location['section']['title'] ?? 'Lokasi & Jam Operasional' }}
            </h2>
            <p class="text-sm sm:text-base text-gray-600 font-normal">
                {{ $location['section']['subtitle'] ?? 'Bisa datang langsung memilih daging segar atau pesan online untuk pengiriman instan ke seluruh area D.I. Yogyakarta.' }}
            </p>
        </div>

        <!-- 2-Column Grid: Desktop 50/50, Mobile stacked -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            
            <!-- Left Column: Store Information (5 cols) -->
            <div class="lg:col-span-5 bg-white p-6 sm:p-8 rounded-modern-lg border border-gray-100 shadow-sm flex flex-col justify-between">
                <div class="space-y-6">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 mb-3 border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            <span>{{ $location['outlet']['status_badge'] ?? 'Buka Hari Ini (07.00 - 19.00 WIB)' }}</span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-extrabold text-brand-dark tracking-tight">
                            {{ $location['outlet']['name'] ?? ($site['brand']['name'] ?? 'Sumber Protein Jogja') }}
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 font-medium mt-1">
                            {{ $location['outlet']['tagline'] ?? 'Outlet & Cold Storage Yogyakarta' }}
                        </p>
                    </div>

                    <!-- Details List -->
                    <div class="space-y-4 pt-2 border-t border-gray-100">
                        <div class="flex items-start gap-3.5">
                            <div class="w-9 h-9 rounded-modern bg-brand-soft-green text-brand-primary flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Alamat Lengkap</h4>
                                <p class="text-xs sm:text-sm text-brand-dark font-medium leading-relaxed mt-0.5">
                                    {{ $location['address']['full'] ?? 'Jl. Kaliurang Km. 8.5 No. 42, Sinduharjo, Ngaglik, Sleman, D.I. Yogyakarta 55581' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5">
                            <div class="w-9 h-9 rounded-modern bg-brand-soft-green text-brand-primary flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Jam Operasional</h4>
                                <p class="text-xs sm:text-sm text-brand-dark font-medium mt-0.5">
                                    {{ $location['operational_hours']['display'] ?? 'Senin – Minggu, 07.00 – 19.00 WIB' }}
                                </p>
                                <p class="text-[11px] text-emerald-700 font-semibold mt-0.5">
                                    {{ $location['delivery_note'] ?? 'Pengiriman instant GrabExpress / Gosend siap tiap hari' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5">
                            <div class="w-9 h-9 rounded-modern bg-brand-soft-green text-brand-primary flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Customer Care & Pemesanan</h4>
                                <p class="text-xs sm:text-sm text-brand-dark font-semibold mt-0.5">
                                    {{ $site['contact']['phone'] ?? ($storeInfo['phone'] ?? '+62 812-3456-7890') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action CTA -->
                <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col sm:flex-row gap-3">
                    <a href="{{ $location['maps']['link'] ?? ($storeInfo['maps_url'] ?? '#') }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-modern text-xs sm:text-sm font-semibold text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <span>{{ $location['maps']['button_text'] ?? 'Petunjuk Lokasi Google Maps' }}</span>
                    </a>
                </div>
            </div>

            <!-- Right Column: Google Maps Lazy Load Container (7 cols) -->
            <div class="lg:col-span-7 bg-white rounded-modern-lg overflow-hidden border border-gray-100 shadow-sm relative min-h-[320px] lg:min-h-[420px] flex flex-col">
                <!-- Map Header Bar -->
                <div class="bg-gray-50 px-4 py-2.5 border-b border-gray-100 flex items-center justify-between text-xs text-gray-500">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                        <span class="font-medium text-brand-dark">{{ $location['maps']['map_title'] ?? 'Peta Lokasi Toko & Rute' }}</span>
                    </div>
                    <span>{{ $location['maps']['map_location_tag'] ?? 'Jl. Kaliurang Km. 8.5, Sleman' }}</span>
                </div>

                <!-- Google Maps iframe with Lazy Loading -->
                <div class="w-full flex-1 relative bg-gray-100">
                    <iframe 
                        title="Google Maps Lokasi Sumber Protein Jogja"
                        src="{{ $location['maps']['embed'] }}" 
                        class="absolute inset-0 w-full h-full border-0" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

        </div>

    </div>
</section>
