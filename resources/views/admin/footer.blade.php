@extends('layouts.admin', [
    'title' => 'Footer',
    'pageTitle' => 'Footer (Ulasan, Lokasi & Footer)'
])

@section('content')
<div class="space-y-6"
     x-data="{
         footer: {{ json_encode($footerData) }},
         activeTab: 'reviews', // 'reviews' | 'location' | 'footer'
         previewDevice: 'desktop', // 'desktop' | 'mobile'
         toastMessage: '',
         toastVisible: false,
         
         showToast(msg) {
             this.toastMessage = msg;
             this.toastVisible = true;
             setTimeout(() => { this.toastVisible = false; }, 3000);
         },
         
         saveFooter() {
             this.showToast('Pengaturan Footer berhasil disimpan!');
         }
     }">
    
    <!-- 1. Header Card -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-8 shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-brand-dark tracking-tight">
                        Footer & Bagian Bawah
                    </h2>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                        <span>3 SEPARATE EDITORS & PREVIEWS</span>
                    </span>
                    <span class="text-xs text-gray-500 font-medium">
                        • Ulasan Pelanggan ⬩ Kunjungi Outlet ⬩ Footer Actual
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 leading-relaxed max-w-3xl">
                    Struktur terpisah untuk 3 section paling bawah di Landing Page. Masing-masing memiliki form konfigurasi dan <strong>Real Landing Page Preview</strong> tersendiri.
                </p>
            </div>

            <!-- Save Action Button -->
            <div class="flex items-center gap-3 shrink-0">
                <button @click="saveFooter()" 
                        type="button"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-modern font-bold text-xs sm:text-sm text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer">
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 2. Navigation Tabs for 3 Distinct Footer Sub-sections -->
    <div class="flex items-center gap-2 border-b border-gray-200 pb-2">
        <button @click="activeTab = 'reviews'" 
                type="button"
                :class="activeTab === 'reviews' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer">
            ⭐ 1. Ulasan Pelanggan (Google Reviews)
        </button>
        <button @click="activeTab = 'location'" 
                type="button"
                :class="activeTab === 'location' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer">
            📍 2. Kunjungi Outlet (Lokasi & Maps)
        </button>
        <button @click="activeTab = 'footer'" 
                type="button"
                :class="activeTab === 'footer' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer">
            📄 3. Footer (Informasi Brand & Hak Cipta)
        </button>
    </div>

    <!-- 3. Tab Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Configuration Form (7 cols on lg) -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- =================================================== -->
            <!-- EDITOR 1: ULASAN PELANGGAN                          -->
            <!-- =================================================== -->
            <div x-show="activeTab === 'reviews'" class="space-y-5">
                
                <!-- Rating Overview Card -->
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-4">
                    <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider border-b border-gray-100 pb-2">
                        Konfigurasi Rating Google Reviews
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Badge Tag</label>
                            <input type="text" x-model="footer.reviews.section_badge" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Judul Section</label>
                            <input type="text" x-model="footer.reviews.section_title" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Deskripsi Pengantar</label>
                        <textarea x-model="footer.reviews.section_subtitle" rows="2" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Rating (Skala 5.0)</label>
                            <input type="number" x-model.number="footer.reviews.rating" step="0.1" min="1" max="5" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-black text-brand-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Total Review</label>
                            <input type="text" x-model="footer.reviews.total_reviews" placeholder="180+" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Review Ditampilkan</label>
                            <input type="number" x-model.number="footer.reviews.displayed_count" min="1" max="10" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Google Place URL / Link Review</label>
                        <input type="text" x-model="footer.reviews.google_place_url" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-mono text-[11px]">
                    </div>
                </div>

                <!-- Featured Reviews List -->
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                        <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                            Daftar Ulasan Pelanggan Terverifikasi
                        </h3>
                        <span class="text-xs text-gray-500 font-semibold" x-text="footer.reviews.items.length + ' Ulasan'"></span>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(item, idx) in footer.reviews.items" :key="item.id">
                            <div class="p-4 rounded-modern border border-gray-200 space-y-2 bg-gray-50/50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-brand-primary text-white flex items-center justify-center font-bold text-xs">
                                            <span x-text="item.name.charAt(0)"></span>
                                        </div>
                                        <div>
                                            <input type="text" x-model="item.name" class="font-bold text-brand-dark text-xs bg-transparent border-b border-transparent hover:border-gray-300 focus:border-brand-primary p-0">
                                            <p class="text-[10px] text-gray-400" x-text="item.role + ' • ' + item.date"></p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <div class="flex items-center gap-0.5 text-amber-400">
                                            <template x-for="i in 5" :key="i">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            </template>
                                        </div>
                                        <label class="flex items-center gap-1 text-[11px] font-semibold text-gray-600 cursor-pointer">
                                            <input type="checkbox" x-model="item.is_active" class="rounded text-brand-primary">
                                            <span>Tampil</span>
                                        </label>
                                    </div>
                                </div>

                                <textarea x-model="item.comment" rows="2" class="w-full text-xs rounded border border-gray-300 p-2 bg-white"></textarea>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <!-- =================================================== -->
            <!-- EDITOR 2: KUNJUNGI OUTLET                           -->
            <!-- =================================================== -->
            <div x-show="activeTab === 'location'" class="space-y-5">
                
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-4">
                    <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider border-b border-gray-100 pb-2">
                        Section Header & Informasi Toko
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Badge Tag</label>
                            <input type="text" x-model="footer.location.section_badge" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Judul Section</label>
                            <input type="text" x-model="footer.location.section_title" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Nama Outlet Resmi</label>
                        <input type="text" x-model="footer.location.store_name" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Alamat Lengkap Outlet</label>
                        <textarea x-model="footer.location.address" rows="2" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Jam Operasional</label>
                            <input type="text" x-model="footer.location.operational_hours" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Area Jangkauan Pengiriman</label>
                            <input type="text" x-model="footer.location.delivery_coverage" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">WhatsApp Outlet</label>
                            <input type="text" x-model="footer.location.whatsapp_contact" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Telepon Kantor</label>
                            <input type="text" x-model="footer.location.phone_contact" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Google Maps Embed Iframe URL</label>
                        <input type="text" x-model="footer.location.google_maps_embed" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-mono text-[11px]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Direct Google Maps Navigation Link</label>
                        <input type="text" x-model="footer.location.google_maps_link" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-mono text-[11px]">
                    </div>
                </div>

            </div>

            <!-- =================================================== -->
            <!-- EDITOR 3: ACTUAL FOOTER                             -->
            <!-- =================================================== -->
            <div x-show="activeTab === 'footer'" class="space-y-5">
                
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-4">
                    <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider border-b border-gray-100 pb-2">
                        Footer Actual (Brand & Copyright)
                    </h3>

                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Nama Brand Footer</label>
                        <input type="text" x-model="footer.actual_footer.brand_title" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Deskripsi Singkat Footer</label>
                        <textarea x-model="footer.actual_footer.brand_desc" rows="3" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white leading-relaxed"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Teks Hak Cipta (Copyright)</label>
                        <input type="text" x-model="footer.actual_footer.copyright" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium">
                    </div>

                    <div class="pt-2 border-t border-gray-100 space-y-3">
                        <h4 class="text-xs font-bold text-brand-dark">Tautan Media Sosial</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-600 mb-1">Instagram URL</label>
                                <input type="text" x-model="footer.actual_footer.social_links.instagram" class="w-full text-xs rounded border border-gray-300 p-2 bg-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-600 mb-1">TikTok URL</label>
                                <input type="text" x-model="footer.actual_footer.social_links.tiktok" class="w-full text-xs rounded border border-gray-300 p-2 bg-white">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Right: 3 DISTINCT REAL LANDING PAGE PREVIEWS (5 cols on lg) -->
        <div class="lg:col-span-5 space-y-4 sticky top-4">
            
            <div class="flex items-center justify-between">
                <label class="block text-xs font-extrabold text-brand-dark uppercase tracking-wider">
                    Real Landing Page Preview
                </label>
                <div class="flex items-center bg-gray-100 p-0.5 rounded text-[10px]">
                    <button @click="previewDevice = 'desktop'" type="button" 
                            :class="previewDevice === 'desktop' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500 hover:text-brand-dark'"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded cursor-pointer transition-all">
                        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>Desk</span>
                    </button>
                    <button @click="previewDevice = 'mobile'" type="button" 
                            :class="previewDevice === 'mobile' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500 hover:text-brand-dark'"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded cursor-pointer transition-all">
                        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span>Mob</span>
                    </button>
                </div>
            </div>

            <!-- PREVIEW 1: ULASAN PELANGGAN (Matching review-card.blade.php) -->
            <div x-show="activeTab === 'reviews'" 
                 class="bg-white p-5 rounded-modern-xl border border-gray-200 shadow-md space-y-4"
                 :class="previewDevice === 'mobile' ? 'max-w-[280px] mx-auto' : 'w-full'">
                
                <div class="space-y-1">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-brand-soft-green text-brand-primary"
                          x-text="footer.reviews.section_badge"></span>
                    <h3 class="text-base font-black text-brand-dark leading-tight" x-text="footer.reviews.section_title"></h3>
                    <p class="text-[11px] text-gray-500" x-text="footer.reviews.section_subtitle"></p>
                </div>

                <!-- Google Aggregate Rating Badge Replica -->
                <div class="flex items-center gap-3 bg-brand-cream p-3 rounded-modern border border-gray-200">
                    <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-2xs text-base">
                        G
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm font-extrabold text-brand-dark" x-text="footer.reviews.rating + ' / 5.0'"></span>
                            <div class="flex items-center gap-0.5 text-amber-400">
                                <template x-for="i in 5" :key="i">
                                    <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </template>
                            </div>
                        </div>
                        <span class="text-[10px] text-gray-500" x-text="'Berdasarkan ' + footer.reviews.total_reviews + ' Google Reviews'"></span>
                    </div>
                </div>

                <!-- Review Card Replica -->
                <div class="space-y-2 max-h-56 overflow-y-auto">
                    <template x-for="item in footer.reviews.items.filter(i => i.is_active).slice(0, 2)" :key="item.id">
                        <div class="p-3 bg-gray-50 rounded-modern border border-gray-200 text-left space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-brand-dark text-xs" x-text="item.name"></span>
                                <div class="flex items-center gap-0.5 text-amber-400">
                                    <template x-for="i in 5" :key="i">
                                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </template>
                                </div>
                            </div>
                            <p class="text-[10px] text-gray-400" x-text="item.role + ' • ' + item.date"></p>
                            <p class="text-[11px] italic text-gray-600 leading-relaxed" x-text="'&ldquo;' + item.comment + '&rdquo;'"></p>
                        </div>
                    </template>
                </div>

                <p class="text-[10px] text-gray-400 text-center">Preview section Ulasan Pelanggan Landing Page.</p>
            </div>

            <!-- PREVIEW 2: KUNJUNGI OUTLET (Matching location-section.blade.php) -->
            <div x-show="activeTab === 'location'" 
                 class="bg-brand-cream/80 p-5 rounded-modern-xl border border-gray-200 shadow-md space-y-4"
                 :class="previewDevice === 'mobile' ? 'max-w-[280px] mx-auto' : 'w-full'">
                
                <div class="text-center space-y-1">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-brand-soft-green text-brand-primary"
                          x-text="footer.location.section_badge"></span>
                    <h3 class="text-base font-black text-brand-dark" x-text="footer.location.section_title"></h3>
                </div>

                <div class="bg-white p-4 rounded-modern border border-gray-200 space-y-3 text-left">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span x-text="footer.location.operational_hours"></span>
                    </div>

                    <h4 class="font-extrabold text-brand-dark text-sm" x-text="footer.location.store_name"></h4>
                    <p class="text-xs text-gray-600 leading-relaxed" x-text="footer.location.address"></p>

                    <div class="pt-2 border-t border-gray-100 text-xs text-gray-500 space-y-1">
                        <div>📞 <strong class="text-brand-dark" x-text="footer.location.phone_contact"></strong></div>
                        <div>💬 WA: <strong class="text-emerald-700 font-mono" x-text="footer.location.whatsapp_contact"></strong></div>
                    </div>
                </div>

                <p class="text-[10px] text-gray-400 text-center">Preview section Kunjungi Outlet Landing Page.</p>
            </div>

            <!-- PREVIEW 3: ACTUAL FOOTER (Matching components/footer.blade.php) -->
            <div x-show="activeTab === 'footer'" 
                 class="bg-brand-dark text-white p-5 rounded-modern-xl border border-white/10 shadow-xl space-y-4"
                 :class="previewDevice === 'mobile' ? 'max-w-[280px] mx-auto text-center' : 'w-full'">
                
                <div class="space-y-2 text-left">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded bg-brand-primary flex items-center justify-center text-xs font-bold text-white">SP</div>
                        <h4 class="font-black text-white text-sm" x-text="footer.actual_footer.brand_title"></h4>
                    </div>
                    <p class="text-xs text-gray-300 leading-relaxed line-clamp-3" x-text="footer.actual_footer.brand_desc"></p>
                </div>

                <div class="pt-3 border-t border-white/10 flex items-center justify-between text-[10px] text-gray-400">
                    <span x-text="footer.actual_footer.copyright"></span>
                    <span class="text-emerald-400 font-semibold" x-text="footer.actual_footer.social_links.instagram"></span>
                </div>

                <p class="text-[10px] text-gray-400 text-center">Preview actual bottom footer Landing Page.</p>
            </div>

        </div>

    </div>

    <!-- Toast Notification -->
    <div x-show="toastVisible" 
         x-cloak
         x-transition
         class="fixed bottom-6 right-6 z-50 bg-brand-dark text-white px-4 py-3 rounded-modern-lg shadow-xl border border-white/10 flex items-center gap-2.5 text-xs font-semibold">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        <span x-text="toastMessage"></span>
    </div>

</div>
@endsection
