@extends('layouts.admin', [
    'title' => 'Footer',
    'pageTitle' => 'Footer (Ulasan, Lokasi & Footer)'
])

@section('content')
<script>
    window.__initialFooterData = {!! json_encode($footerData) !!};
    window.__initialContactsData = {!! json_encode($contacts ?? ($footerData['contacts'] ?? [])) !!};
</script>

<div class="space-y-8"
     x-data="footerManager({
         csrfToken: '{{ csrf_token() }}',
         footer: window.__initialFooterData,
         contacts: window.__initialContactsData
     })"
     x-init="initPreviewObserver()">

    <style>
        [x-cloak] {
            display: none !important;
        }
        .laptop-desktop-viewport::-webkit-scrollbar {
            width: 6px;
        }
        .laptop-desktop-viewport::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.25);
            border-radius: 9999px;
        }
        .tablet-viewport::-webkit-scrollbar {
            width: 6px;
        }
        .tablet-viewport::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.25);
            border-radius: 9999px;
        }
        .mobile-viewport::-webkit-scrollbar {
            width: 4px;
        }
        .mobile-viewport::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 9999px;
        }
    </style>

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
                        <span>3 SUB-SECTION FOOTER</span>
                    </span>
                    <span class="text-xs text-gray-500 font-medium">
                        • Ulasan Pelanggan ⬩ Kunjungi Outlet ⬩ Footer Actual
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 leading-relaxed max-w-3xl">
                    Struktur konfigurasi untuk 3 section paling bawah di Landing Page dengan <strong>Preview</strong> berskala presisi di bagian bawah.
                </p>
            </div>

            <!-- Save Action Button -->
            <div class="flex items-center gap-3 shrink-0">
                <button @click="saveFooter()"
                        type="button"
                        :disabled="isSaving"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                    <svg x-show="isSaving" class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Pengaturan'"></span>
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

    <!-- =============================================================== -->
    <!-- 3. TAB EDITORS AREA                                             -->
    <!-- =======================================================    <!-- --------------------------------------------------------------- -->
    <!-- TAB 1: ULASAN PELANGGAN (MANUAL & GOOGLE REVIEWS MANAGER)       -->
    <!-- --------------------------------------------------------------- -->
    <div x-show="activeTab === 'reviews'" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column (5 cols): Mode Switch & Google Config -->
        <div class="lg:col-span-5 space-y-6">

            <!-- 1. DUAL REVIEW SOURCE MODE SWITCH CARD -->
            <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🔄</span>
                        <h3 class="text-xs sm:text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                            Sumber Ulasan (Review Mode)
                        </h3>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                          :class="footer.reviews.review_mode === 'google' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800'"
                          x-text="footer.reviews.review_mode === 'google' ? 'MODE: GOOGLE REVIEW' : 'MODE: MANUAL REVIEW'"></span>
                </div>

                <p class="text-xs text-gray-500 leading-relaxed">
                    Tentukan sumber ulasan yang aktif ditampilkan di Landing Page publik. Beralih mode tidak akan menghapus data ulasan yang tersimpan.
                </p>

                <!-- Dual Mode Toggle Selector -->
                <div class="grid grid-cols-2 gap-2 bg-gray-100/80 p-1.5 rounded-modern-lg">
                    <button type="button"
                            @click="toggleReviewMode('manual')"
                            class="py-2 px-3 rounded-modern text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5"
                            :class="footer.reviews.review_mode === 'manual' ? 'bg-white text-emerald-700 shadow-sm border border-emerald-200' : 'text-gray-600 hover:text-brand-dark'">
                        <span>⭐ Ulasan Manual</span>
                    </button>
                    <button type="button"
                            @click="toggleReviewMode('google')"
                            class="py-2 px-3 rounded-modern text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5"
                            :class="footer.reviews.review_mode === 'google' ? 'bg-white text-blue-700 shadow-sm border border-blue-200' : 'text-gray-600 hover:text-brand-dark'">
                        <span>🗺️ Google Review</span>
                    </button>
                </div>
            </div>

            <!-- 2. GOOGLE REVIEWS CONFIGURATION CARD -->
            <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        <h3 class="text-xs sm:text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                            Konfigurasi Google Place
                        </h3>
                    </div>
                    <!-- Dynamic Status Indicator -->
                    <template x-if="footer.reviews.last_updated && footer.reviews.last_updated !== 'Belum ada sync Google'">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                            <span>Tersinkronisasi Google</span>
                        </span>
                    </template>
                    <template x-if="!footer.reviews.last_updated || footer.reviews.last_updated === 'Belum ada sync Google'">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                              :class="footer.reviews.google_place_id ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-gray-100 text-gray-600 border border-gray-200'">
                            <span class="w-1.5 h-1.5 rounded-full" :class="footer.reviews.google_place_id ? 'bg-blue-600' : 'bg-gray-400'"></span>
                            <span x-text="footer.reviews.google_place_id ? 'Place ID Terpasang' : 'Belum Terhubung'"></span>
                        </span>
                    </template>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Google Place ID</label>
                        <input type="text"
                               x-model="footer.reviews.google_place_id"
                               class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white font-mono text-brand-dark"
                               placeholder="ChIJN1t_tDeuEmsRUsoyG83frY4">
                        <p class="text-[10px] text-gray-400 mt-1">Kode pengenal unik lokasi Google Maps toko Anda.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Rating Google</label>
                            <input type="number"
                                   step="0.1"
                                   min="1"
                                   max="5"
                                   x-model="footer.reviews.rating"
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white font-bold text-amber-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Total Ulasan Google</label>
                            <input type="number"
                                   min="0"
                                   x-model="footer.reviews.total_reviews"
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white font-mono">
                        </div>
                    </div>

                    <!-- Sync status line -->
                    <div class="p-2.5 rounded-modern bg-gray-50 border border-gray-200/80 text-[11px] text-gray-600 flex items-center justify-between">
                        <span class="font-medium">Status Sinkronisasi:</span>
                        <span class="font-semibold text-brand-dark" x-text="footer.reviews.last_updated || 'Belum ada sync Google'"></span>
                    </div>

                    <div class="pt-2 space-y-2">
                        <button @click="saveGoogleConfig()"
                                type="button"
                                class="w-full px-4 py-2.5 rounded-modern font-bold text-xs text-white bg-blue-600 hover:bg-blue-700 transition-all cursor-pointer shadow-2xs flex items-center justify-center gap-1.5">
                            <span>Simpan Konfigurasi Google</span>
                        </button>

                        <div class="grid grid-cols-2 gap-2">
                            <template x-if="footer.reviews.google_place_id && footer.reviews.google_place_id.trim() !== ''">
                                <a :href="'https://search.google.com/local/writereview?placeid=' + footer.reviews.google_place_id.trim()"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="w-full px-3 py-2 rounded-modern font-bold text-xs text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 transition-all flex items-center justify-center gap-1 cursor-pointer text-center">
                                    <span>🌐 Link Ulasan ↗</span>
                                </a>
                            </template>
                            <button @click="syncGoogleReviews()"
                                    type="button"
                                    :disabled="isSyncing || !footer.reviews.google_place_id"
                                    class="w-full px-3 py-2 rounded-modern font-bold text-xs text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-all flex items-center justify-center gap-1 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed text-center">
                                <svg x-show="isSyncing" class="animate-spin h-3.5 w-3.5 text-emerald-700" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span x-text="isSyncing ? 'Menyinkronkan...' : '🔄 Tarik Ulasan API'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Info notice -->
                <div class="p-3 rounded-modern bg-blue-50/60 border border-blue-100 text-[11px] text-blue-900 flex items-start gap-2.5">
                    <span class="text-blue-600 text-sm mt-0.5">ℹ️</span>
                    <p class="leading-relaxed">
                        Jika Anda belum memiliki <strong>Google Cloud API Key</strong>, Anda dapat memasukkan ulasan terbaik dari Google Maps secara manual melalui tombol <strong>+ Tambah Ulasan</strong> dengan memilih Sumber: <em>Google Review</em>.
                    </p>
                </div>
            </div>

        </div>

        <!-- Right Column (7 cols): REVIEW MANAGEMENT (CRUD & LIST) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- REVIEW LIST CARD -->
            <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="text-xs sm:text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                            Daftar Ulasan (<span x-text="footer.reviews.items.length"></span>)
                        </h3>
                    </div>

                    <button type="button"
                            @click="openCreateReviewModal()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark transition-all cursor-pointer shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Ulasan</span>
                    </button>
                </div>

                <!-- Scrollable Review List -->
                <div class="space-y-3 max-h-[600px] overflow-y-auto pr-1">
                    <template x-for="(item, idx) in footer.reviews.items" :key="item.id">
                        <div class="p-4 rounded-modern-lg border transition-all duration-200 space-y-3"
                             :class="item.is_active ? 'border-gray-200 bg-gray-50/70 hover:bg-white' : 'border-dashed border-gray-300 bg-gray-100/60 opacity-60'">

                            <!-- Top Row: Reviewer Meta, Rating & Actions -->
                            <div class="flex items-center justify-between flex-wrap gap-2">

                                <!-- Left: Avatar & Details -->
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-full bg-brand-primary text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs"
                                         x-text="item.name.split(' ').map(n=>n[0]).join('').substring(0,2)">
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-xs font-extrabold text-brand-dark truncate" x-text="item.name"></h4>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold shrink-0"
                                                  :class="item.source === 'Google Review' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'">
                                                <span x-text="item.source || 'Manual Review'"></span>
                                            </span>
                                        </div>
                                        <p class="text-[10px] text-gray-500 mt-0.5 truncate"
                                           x-text="(item.role ? item.role + (item.location ? ', ' + item.location : '') : item.location || '') + ' • ' + (item.time || 'Baru saja')"></p>
                                    </div>
                                </div>

                                <!-- Right: Rating, Toggle & Action Buttons -->
                                <div class="flex items-center gap-2 shrink-0">
                                    <!-- Stars -->
                                    <div class="flex items-center gap-0.5 text-amber-400" :title="item.rating + ' Bintang'">
                                        <template x-for="i in item.rating" :key="i">
                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        </template>
                                    </div>

                                    <!-- Active Toggle Button -->
                                    <button type="button"
                                            @click="toggleReviewStatus(item)"
                                            class="px-2 py-1 rounded text-[10px] font-bold cursor-pointer transition-all border"
                                            :class="item.is_active ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-gray-200 text-gray-600 border-gray-300'"
                                            x-text="item.is_active ? 'Aktif' : 'Nonaktif'">
                                    </button>

                                    <!-- Edit Button -->
                                    <button type="button"
                                            @click="openEditReviewModal(item)"
                                            class="p-1 rounded text-gray-500 hover:text-brand-primary hover:bg-gray-100 cursor-pointer"
                                            title="Edit ulasan">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <button type="button"
                                            @click="deleteReview(item)"
                                            class="p-1 rounded text-rose-500 hover:bg-rose-50 hover:text-rose-600 transition-colors cursor-pointer"
                                            title="Hapus ulasan">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Review Comment Box -->
                            <div class="bg-white p-3 rounded-modern border border-gray-200/80 text-xs text-gray-700 leading-relaxed italic">
                                <span class="text-gray-400 font-serif mr-1">&ldquo;</span><span x-text="item.review_text || item.comment"></span><span class="text-gray-400 font-serif ml-1">&rdquo;</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>

    </div>

    <!-- --------------------------------------------------------------- -->
    <!-- TAB 2: KUNJUNGI OUTLET (LOKASI & MAPS)                          -->
    <!-- --------------------------------------------------------------- -->
    <div x-show="activeTab === 'location'" class="space-y-6">

        <!-- ROW 1: HEADER SECTION (Full Width Card) -->
        <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-7 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-primary"></span>
                    <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                        1. Pengaturan Header Section
                    </h3>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Badge Tag</label>
                    <input type="text"
                           x-model="footer.location.section.badge"
                           class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2.5 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary font-medium text-brand-dark bg-white"
                           placeholder="Kunjungi Outlet">
                </div>

                <div class="md:col-span-8">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Judul Section (H2)</label>
                    <input type="text"
                           x-model="footer.location.section.title"
                           class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2.5 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary font-extrabold text-brand-dark bg-white"
                           placeholder="Lokasi & Jam Operasional">
                </div>

                <div class="md:col-span-12">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Deskripsi Pengantar</label>
                    <textarea x-model="footer.location.section.subtitle"
                              rows="2"
                              class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary text-gray-700 leading-relaxed bg-white"
                              placeholder="Bisa datang langsung memilih daging segar atau pesan online..."></textarea>
                </div>
            </div>
        </div>

        <!-- ROW 2: STORE INFO & ADDRESS & MAPS SETTINGS (2 Columns) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- Left Column (6 cols): Identitas Outlet & Alamat Terstruktur -->
            <div class="lg:col-span-6 space-y-6">

                <!-- 2. IDENTITAS OUTLET & STATUS OPERASIONAL -->
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-7 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                                2. Identitas Outlet &amp; Status
                            </h3>
                        </div>
                    </div>

                    <div class="space-y-3.5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Status Operasional (Badge Hijau)
                            </label>
                            <input type="text"
                                   x-model="footer.location.outlet.status_badge"
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white font-medium text-emerald-800 focus:ring-1 focus:ring-brand-primary"
                                   placeholder="Buka Hari Ini (07.00 - 19.00 WIB)">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nama Toko / Outlet</label>
                                <input type="text"
                                       x-model="footer.location.outlet.name"
                                       class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white font-bold text-brand-dark focus:ring-1 focus:ring-brand-primary"
                                       placeholder="Sumber Protein Jogja">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Tagline / Sub-judul Toko</label>
                                <input type="text"
                                       x-model="footer.location.outlet.tagline"
                                       class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white font-medium text-brand-primary focus:ring-1 focus:ring-brand-primary"
                                       placeholder="Outlet & Cold Storage Yogyakarta">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Pengiriman (Delivery Note)</label>
                            <input type="text"
                                   x-model="footer.location.delivery_note"
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white text-gray-600 focus:ring-1 focus:ring-brand-primary"
                                   placeholder="Pengiriman instant GrabExpress / Gosend siap tiap hari">
                        </div>
                    </div>
                </div>

                <!-- 3. CUSTOMER CARE & KONTAK PEMESANAN (DROPDOWN SITE & CONTACT) -->
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-7 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                            <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                                3. Customer Care &amp; Kontak Pemesanan
                            </h3>
                        </div>
                    </div>

                    <div class="space-y-3.5">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-gray-700">Pilih Kontak Registry Terdaftar</label>
                                <a href="{{ route('admin.settings') }}" target="_blank" class="text-[11px] text-brand-primary hover:underline font-semibold flex items-center gap-1">
                                    <span>Kelola di Site &amp; Contact</span>
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>
                            <select x-model="footer.location.contact_key"
                                    @change="onLocationContactChange()"
                                    class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2.5 bg-white font-semibold text-brand-dark focus:ring-1 focus:ring-brand-primary focus:border-brand-primary">
                                <option value="">-- Pilih Kontak dari Registry --</option>
                                <template x-for="c in (contacts || [])" :key="c.key || c.id">
                                    <option :value="c.key || c.id"
                                            :selected="(footer.location.contact_key || '') === (c.key || c.id)"
                                            x-text="c.name + ' (' + (c.division || c.type) + ') — ' + c.value"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Read-Only Info Card Preview (Saat Kontak Registry Terpilih) -->
                        <template x-if="footer.location.contact_key && getContactByKey(footer.location.contact_key)">
                            <div class="p-3.5 bg-emerald-50/70 border border-emerald-200 rounded-modern text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span class="font-bold text-emerald-950" x-text="getContactByKey(footer.location.contact_key)?.name"></span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-white text-emerald-800 border border-emerald-300" x-text="'Key: ' + footer.location.contact_key"></span>
                                </div>
                                <div class="flex flex-wrap items-center gap-3 text-emerald-800 text-[11px] pt-1 border-t border-emerald-200/60">
                                    <span class="font-medium" x-text="'Divisi: ' + (getContactByKey(footer.location.contact_key)?.division || '-')"></span>
                                    <span class="text-emerald-400">•</span>
                                    <span class="font-bold uppercase tracking-wider text-[10px]" x-text="'Tipe: ' + (getContactByKey(footer.location.contact_key)?.type || 'phone')"></span>
                                    <span class="text-emerald-400">•</span>
                                    <span class="font-mono font-bold text-emerald-950" x-text="'Nomor: ' + getContactByKey(footer.location.contact_key)?.value"></span>
                                </div>
                                <p class="text-[10px] text-emerald-700 italic">
                                    ✓ Kontak ini otomatis tersinkronkan pada kartu Customer Care di Outlet &amp; section Lokasi landing page.
                                </p>
                            </div>
                        </template>

                        <!-- Fallback / Custom override bila tidak memilih registry -->
                        <div x-show="!footer.location.contact_key">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nomor Kontak Manual (Fallback)</label>
                            <input type="text"
                                   x-model="footer.location.phone"
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white text-gray-800 font-medium focus:ring-1 focus:ring-brand-primary"
                                   placeholder="+62 812-3456-7890">
                        </div>
                    </div>
                </div>

                <!-- 4. ALAMAT TERSTRUKTUR & KOORDINAT GEO -->
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-7 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                            <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                                4. Alamat Fisik &amp; Koordinat Geo
                            </h3>
                        </div>
                    </div>

                    <div class="space-y-3.5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Jalan &amp; Nomor Gedung</label>
                            <input type="text"
                                   x-model="footer.location.address.street"
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white text-gray-800 font-medium focus:ring-1 focus:ring-brand-primary"
                                   placeholder="Jl. Kaliurang Km. 8.5 No. 42">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Kecamatan (District)</label>
                                <input type="text"
                                       x-model="footer.location.address.district"
                                       class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white text-gray-800 focus:ring-1 focus:ring-brand-primary"
                                       placeholder="Ngaglik">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Kabupaten / Kota (City)</label>
                                <input type="text"
                                       x-model="footer.location.address.city"
                                       class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white text-gray-800 focus:ring-1 focus:ring-brand-primary"
                                       placeholder="Sleman">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Provinsi (Province)</label>
                                <input type="text"
                                       x-model="footer.location.address.province"
                                       class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white text-gray-800 focus:ring-1 focus:ring-brand-primary"
                                       placeholder="D.I. Yogyakarta">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Kode Pos</label>
                                <input type="text"
                                       x-model="footer.location.address.postal_code"
                                       class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white text-gray-800 focus:ring-1 focus:ring-brand-primary"
                                       placeholder="55581">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Kode Negara</label>
                                <input type="text"
                                       x-model="footer.location.address.country_code"
                                       class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white text-gray-800 focus:ring-1 focus:ring-brand-primary"
                                       placeholder="ID">
                            </div>
                        </div>

                        <!-- Full Address Display Preview -->
                        <div class="bg-gray-50 rounded-modern p-3 border border-gray-200/70">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Display Alamat Lengkap (Otomatis):</span>
                            <p class="text-xs text-brand-dark font-medium leading-relaxed" x-text="computedLocationAddress"></p>
                        </div>

                        <!-- Geo Coordinates -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Latitude (Decimal)</label>
                                <input type="number"
                                       step="any"
                                       x-model.number="footer.location.coordinates.latitude"
                                       class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white font-mono text-gray-700 focus:ring-1 focus:ring-brand-primary"
                                       placeholder="-7.748906392269989">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Longitude (Decimal)</label>
                                <input type="number"
                                       step="any"
                                       x-model.number="footer.location.coordinates.longitude"
                                       class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white font-mono text-gray-700 focus:ring-1 focus:ring-brand-primary"
                                       placeholder="110.38623737593888">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column (6 cols): Jam Operasional & Integrasi Google Maps -->
            <div class="lg:col-span-6 space-y-6">

                <!-- 5. JAM OPERASIONAL TOKO -->
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-7 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                                5. Jam Operasional Outlet
                            </h3>
                        </div>
                    </div>

                    <div class="space-y-3.5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">
                                Teks Ringkasan Display Jam
                            </label>
                            <input type="text"
                                   x-model="footer.location.operational_hours.display"
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white font-medium text-brand-dark focus:ring-1 focus:ring-brand-primary"
                                   placeholder="Senin – Minggu, 07.00 – 19.00 WIB">
                        </div>

                        <!-- Weekly Schedule Days (7 Days) -->
                        <div class="space-y-2 pt-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-gray-700">Jadwal Jam Buka Harian:</span>
                                <span class="text-[10px] text-gray-400 font-mono" x-text="'Zona: ' + (footer.location.operational_hours.timezone || 'Asia/Jakarta')"></span>
                            </div>

                            <div class="bg-gray-50 p-3 rounded-modern border border-gray-200/70 space-y-2">
                                <template x-for="(dayObj, dayKey) in footer.location.operational_hours.days" :key="dayKey">
                                    <div class="flex items-center justify-between text-xs py-1 border-b border-gray-200/40 last:border-0">
                                        <span class="font-bold text-gray-600 capitalize w-28" x-text="dayKey"></span>
                                        <div class="flex items-center gap-2">
                                            <input type="text"
                                                   x-model="dayObj.open"
                                                   class="w-18 text-center text-xs rounded-modern border border-gray-300 px-1.5 py-1 bg-white font-mono"
                                                   placeholder="07:00">
                                            <span class="text-gray-400 text-xs">-</span>
                                            <input type="text"
                                                   x-model="dayObj.close"
                                                   class="w-18 text-center text-xs rounded-modern border border-gray-300 px-1.5 py-1 bg-white font-mono"
                                                   placeholder="19:00">
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. INTEGRASI PETA GOOGLE MAPS EMBED & CTA -->
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-7 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                            <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                                6. Integrasi Google Maps &amp; CTA
                            </h3>
                        </div>
                    </div>

                    <div class="space-y-3.5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Teks Tombol CTA</label>
                                <input type="text"
                                       x-model="footer.location.maps.button_text"
                                       class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white font-medium text-brand-dark focus:ring-1 focus:ring-brand-primary"
                                       placeholder="Petunjuk Lokasi Google Maps">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Link URL Google Maps</label>
                                <input type="text"
                                       x-model="footer.location.maps.link"
                                       class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white font-mono text-[11px] text-gray-600 focus:ring-1 focus:ring-brand-primary"
                                       placeholder="https://maps.google.com/?q=...">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Judul Bar Header Peta</label>
                                <input type="text"
                                       x-model="footer.location.maps.map_title"
                                       class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white font-medium text-brand-dark focus:ring-1 focus:ring-brand-primary"
                                       placeholder="Peta Lokasi Toko & Rute">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Tag Lokasi Ringkas</label>
                                <input type="text"
                                       x-model="footer.location.maps.map_location_tag"
                                       class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white text-gray-600 focus:ring-1 focus:ring-brand-primary"
                                       placeholder="Jl. Kaliurang Km. 8.5, Sleman">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Google Maps Iframe Embed URL</label>
                            <input type="text"
                                   x-model="footer.location.maps.embed"
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white font-mono text-[11px] text-gray-600 focus:ring-1 focus:ring-brand-primary"
                                   placeholder="https://www.google.com/maps/embed?pb=...">
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- --------------------------------------------------------------- -->
    <!-- TAB 3: ACTUAL FOOTER (INFORMASI BRAND, LINK & HAK CIPTA)        -->
    <!-- --------------------------------------------------------------- -->
    <div x-show="activeTab === 'footer'" class="space-y-6">

        <!-- ROW 1: BRAND IDENTITAS & MEDIA SOSIAL -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- Left Column (6 cols): Brand & Hak Cipta -->
            <div class="lg:col-span-6 space-y-6">
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-7 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-primary"></span>
                            <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                                1. Identitas Brand &amp; Hak Cipta
                            </h3>
                        </div>
                    </div>

                    <div class="space-y-3.5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Brand Footer</label>
                            <input type="text"
                                   x-model="footer.actual_footer.brand_title"
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2.5 bg-white font-extrabold text-brand-dark focus:ring-1 focus:ring-brand-primary"
                                   placeholder="Sumber Protein Jogja">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Deskripsi Singkat Footer</label>
                            <textarea x-model="footer.actual_footer.brand_desc"
                                      rows="3"
                                      class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white text-gray-700 leading-relaxed focus:ring-1 focus:ring-brand-primary"
                                      placeholder="Penyedia bahan makanan mentah, frozen food..."></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Teks Hak Cipta (Copyright)</label>
                            <input type="text"
                                   x-model="footer.actual_footer.copyright"
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white text-gray-700 font-medium focus:ring-1 focus:ring-brand-primary"
                                   placeholder="Sumber Protein Jogja. Hak Cipta Dilindungi.">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column (6 cols): Media Sosial Dinamis & Outlet Footer -->
            <div class="lg:col-span-6 space-y-6">

                <!-- 2. MEDIA SOSIAL DINAMIS DENGAN DETEKSI OTOMATIS -->
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-7 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                                2. Tautan Media Sosial Dinamis
                            </h3>
                        </div>

                        <!-- Add Social Media Button -->
                        <button @click="addSocialLink()"
                                type="button"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-modern text-xs font-bold text-brand-primary bg-brand-soft-green hover:bg-emerald-100 border border-brand-soft-green-border transition-all cursor-pointer shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>+ Tambah Sosmed</span>
                        </button>
                    </div>

                    <!-- Dynamic List of Social Media Links -->
                    <div class="space-y-3">
                        <template x-for="(soc, idx) in footer.actual_footer.social_links" :key="soc.id || idx">
                            <div class="flex items-center gap-2 bg-gray-50 p-2 rounded-modern border border-gray-200">

                                <!-- Platform Badge Preview (Automatic from URL) -->
                                <span class="px-2.5 py-1 rounded text-[11px] font-bold shrink-0 border flex items-center gap-1 min-w-[90px] justify-center transition-all"
                                      :class="getSocialPlatform(soc.url).badgeClass">
                                    <span x-text="getSocialPlatform(soc.url).name"></span>
                                </span>

                                <!-- URL Input -->
                                <input type="text"
                                       x-model="soc.url"
                                       class="flex-1 text-xs rounded border border-gray-300 px-3 py-1.5 bg-white text-gray-800 font-mono text-[11px] focus:ring-1 focus:ring-brand-primary"
                                       placeholder="https://instagram.com/..., https://tiktok.com/..., https://wa.me/...">

                                <!-- Delete Button -->
                                <button @click="removeSocialLink(idx)"
                                        type="button"
                                        class="p-1.5 text-rose-500 hover:bg-rose-50 hover:text-rose-600 rounded transition-colors cursor-pointer"
                                        title="Hapus media sosial">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <div class="p-2.5 rounded-modern bg-brand-soft-green/40 border border-brand-soft-green-border text-[11px] text-brand-dark flex items-start gap-2">
                            <span class="text-brand-primary text-xs mt-0.5">ℹ️</span>
                            <p class="leading-relaxed">
                                Logo dan platform media sosial akan <strong>otomatis terdeteksi</strong> secara real-time dari URL yang dimasukkan (Instagram, TikTok, WhatsApp, Facebook, YouTube, Twitter/X, Shopee, Tokopedia, dll).
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 3. OUTLET & JAM DI FOOTER -->
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-7 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                                3. Ringkasan Outlet &amp; Jam di Footer
                            </h3>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Judul Kolom Outlet</label>
                            <input type="text"
                                   x-model="footer.actual_footer.outlet_title"
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white font-bold text-brand-dark focus:ring-1 focus:ring-brand-primary"
                                   placeholder="Outlet Yogyakarta">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Alamat Ringkas</label>
                            <input type="text"
                                   x-model="footer.actual_footer.outlet_address"
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white text-gray-700 focus:ring-1 focus:ring-brand-primary"
                                   placeholder="Jl. Kaliurang Km. 8.5 No. 42, Sleman...">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Jam Operasional</label>
                            <input type="text"
                                   x-model="footer.actual_footer.outlet_hours"
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white text-gray-700 focus:ring-1 focus:ring-brand-primary"
                                   placeholder="Senin – Minggu (07.00 – 19.00 WIB)">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Hotline Pemesanan</label>
                            <input type="text"
                                   x-model="footer.actual_footer.outlet_phone"
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white font-semibold text-emerald-700 focus:ring-1 focus:ring-brand-primary"
                                   placeholder="+62 812-3456-7890">
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- ROW 2: KOLOM NAVIGASI & KATEGORI PANGAN (2 Columns) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- Left Column (6 cols): Navigasi Cepat List -->
            <div class="lg:col-span-6 space-y-6">
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-7 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                                4. Menu Navigasi Cepat
                            </h3>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Judul Kolom</label>
                            <input type="text"
                                   x-model="footer.actual_footer.nav_title"
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white font-bold text-brand-dark focus:ring-1 focus:ring-brand-primary"
                                   placeholder="Navigasi Cepat">
                        </div>

                        <div class="space-y-2 pt-1">
                            <label class="block text-[11px] font-bold text-gray-600">Daftar Link Navigasi</label>
                            <template x-for="(nav, idx) in footer.actual_footer.nav_links" :key="idx">
                                <div class="flex items-center gap-2">
                                    <input type="text"
                                           x-model="nav.title"
                                           class="w-1/2 text-xs rounded border border-gray-300 px-2.5 py-1.5 bg-white text-gray-800 font-medium focus:ring-1 focus:ring-brand-primary"
                                           placeholder="Label Menu">
                                    <input type="text"
                                           x-model="nav.url"
                                           class="w-1/2 text-xs rounded border border-gray-300 px-2.5 py-1.5 bg-white font-mono text-[11px] text-gray-600 focus:ring-1 focus:ring-brand-primary"
                                           placeholder="#anchor">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column (6 cols): Kategori Pangan List -->
            <div class="lg:col-span-6 space-y-6">
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-7 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                            <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                                5. Menu Kategori Pangan
                            </h3>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Judul Kolom</label>
                            <input type="text"
                                   x-model="footer.actual_footer.category_title"
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 bg-white font-bold text-brand-dark focus:ring-1 focus:ring-brand-primary"
                                   placeholder="Kategori Pangan">
                        </div>

                        <div class="space-y-2 pt-1">
                            <label class="block text-[11px] font-bold text-gray-600">Daftar Kategori Pangan</label>
                            <template x-for="(cat, idx) in footer.actual_footer.category_links" :key="idx">
                                <div class="flex items-center gap-2">
                                    <input type="text"
                                           x-model="cat.title"
                                           class="w-1/2 text-xs rounded border border-gray-300 px-2.5 py-1.5 bg-white text-gray-800 font-medium focus:ring-1 focus:ring-brand-primary"
                                           placeholder="Nama Kategori">
                                    <input type="text"
                                           x-model="cat.url"
                                           class="w-1/2 text-xs rounded border border-gray-300 px-2.5 py-1.5 bg-white font-mono text-[11px] text-gray-600 focus:ring-1 focus:ring-brand-primary"
                                           placeholder="#anchor">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- =============================================================== -->
    <!-- 4. PREVIEW SECTION (FULL WIDTH AT THE BOTTOM)                   -->
    <!-- =============================================================== -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-8 shadow-2xs space-y-5">

        <!-- Header Preview Bar & Device Switcher -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="text-lg">👁️</span>
                    <h3 class="text-base sm:text-lg font-extrabold text-brand-dark tracking-tight uppercase">
                        PREVIEW
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-brand-soft-green text-brand-primary border border-brand-soft-green-border">
                        LIVE RENDER
                    </span>
                </div>
                <p class="text-xs text-gray-500 font-mono">
                    <span x-show="previewDevice === 'desktop'">💻 Desktop (1366&times;768, 16:9) &bull; Scale <span x-text="Math.round(currentScale * 100)"></span>% &bull; Scrollable jika konten melebihi viewport</span>
                    <span x-show="previewDevice === 'tablet'">📱 Tablet (1024&times;768, 4:3) &bull; Scale <span x-text="Math.round(currentScale * 100)"></span>% &bull; Scrollable jika konten melebihi viewport</span>
                    <span x-show="previewDevice === 'mobile'">📱 Mobile (393&times;852, 9:16) &bull; Scale <span x-text="Math.round(currentScale * 100)"></span>% &bull; Scrollable di dalam viewport</span>
                </p>
            </div>

            <!-- Responsive Device Switcher (Pure Reactive Alpine Binding) -->
            <div class="flex items-center bg-gray-100 p-1 rounded-modern border border-gray-200 text-xs shrink-0 self-start sm:self-auto">
                <button @click="previewDevice = 'desktop'"
                        type="button"
                        :class="previewDevice === 'desktop' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500 hover:text-brand-dark'"
                        class="px-3 py-1.5 rounded transition-all cursor-pointer flex items-center gap-1.5 text-xs">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Desktop</span>
                </button>
                <button @click="previewDevice = 'tablet'"
                        type="button"
                        :class="previewDevice === 'tablet' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500 hover:text-brand-dark'"
                        class="px-3 py-1.5 rounded transition-all cursor-pointer flex items-center gap-1.5 text-xs">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Tablet</span>
                </button>
                <button @click="previewDevice = 'mobile'"
                        type="button"
                        :class="previewDevice === 'mobile' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500 hover:text-brand-dark'"
                        class="px-3 py-1.5 rounded transition-all cursor-pointer flex items-center gap-1.5 text-xs">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Mobile</span>
                </button>
            </div>
        </div>

        <!-- Preview Frame Container (STABLE FIXED HEIGHT - NO JUMPING ACROSS DEVICES) -->
        <div x-ref="previewBoxWrapper"
             class="bg-gray-950 rounded-modern-xl p-3 sm:p-6 flex justify-center items-center overflow-hidden border border-gray-800 shadow-inner h-[560px] sm:h-[600px] relative">

            <!-- =================================================== -->
            <!-- A. DESKTOP VIEWPORT PREVIEW (Laptop 14" 1366x768)  -->
            <!-- =================================================== -->
            <div x-show="previewDevice === 'desktop'"
                 class="relative overflow-hidden rounded-modern-lg shadow-2xl transition-all duration-300 mx-auto select-none bg-gray-900"
                 :style="{
                     width: currentFrameWidth + 'px',
                     height: currentFrameHeight + 'px'
                 }">

                <!-- Scaled Laptop 14" Screen Shell (1366px × 768px Virtual Viewport) -->
                <div class="laptop-desktop-viewport absolute top-0 left-0 bg-white overflow-y-auto overflow-x-hidden text-left"
                     :style="{
                         width: '1366px',
                         height: '768px',
                         transformOrigin: '0 0',
                         transform: 'scale(' + currentScale + ')'
                     }"
                     style="scroll-behavior: smooth;">

                    <!-- Desktop Viewport: Tab 1 (Ulasan Pelanggan) - Exact LP Representation -->
                    <div x-show="activeTab === 'reviews'" class="w-[1366px] min-h-[768px] h-auto bg-white">
                        <section class="py-16 px-12 bg-white w-full min-h-[768px] text-left flex flex-col justify-center">
                            <div class="max-w-7xl mx-auto w-full">

                                <!-- Section Header (LP Exact) -->
                                <div class="flex items-end justify-between mb-10 gap-6">
                                    <div>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-2.5"
                                              x-text="footer.reviews.section_badge || 'Ulasan Pelanggan'"></span>
                                        <h2 class="text-3xl lg:text-4xl font-extrabold text-brand-dark tracking-tight mb-2"
                                            x-text="footer.reviews.section_title || 'Apa Kata Mereka?'"></h2>
                                        <p class="text-sm md:text-base text-gray-600 font-normal max-w-xl"
                                           x-text="footer.reviews.section_subtitle || 'Pengalaman nyata dari ibu rumah tangga, chef rumahan, hingga pemilik kedai kuliner di Yogyakarta.'"></p>
                                    </div>

                                    <!-- Google Review Aggregate Rating Badge (LP Exact) -->
                                    <div class="flex items-center gap-3 bg-brand-cream p-3.5 rounded-modern border border-gray-200/80 shadow-xs shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-2xs border border-gray-100 shrink-0">
                                            <svg class="w-6 h-6" viewBox="0 0 24 24">
                                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1">
                                                <span class="text-base font-extrabold text-brand-dark" x-text="footer.reviews.rating + ' / 5.0'"></span>
                                                <div class="flex text-amber-400 gap-0.5">
                                                    <template x-for="i in 5" :key="i">
                                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                        </svg>
                                                    </template>
                                                </div>
                                            </div>
                                            <span class="text-xs text-gray-500 font-medium" x-text="'Berdasarkan ' + footer.reviews.total_reviews + ' Google Reviews'"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cards Grid (LP Exact) -->
                                <div class="grid gap-6 lg:gap-8 items-stretch"
                                     :class="{
                                         'grid-cols-3': activeReviews.length >= 3,
                                         'grid-cols-2 max-w-4xl mx-auto': activeReviews.length === 2,
                                         'grid-cols-1 max-w-md mx-auto': activeReviews.length === 1
                                     }">
                                    <template x-for="item in activeReviews" :key="item.id">
                                        <div class="bg-brand-cream/40 p-6 rounded-modern border border-gray-200/70 shadow-sm hover:shadow-card transition-all duration-300 flex flex-col justify-between">
                                            <div>
                                                <!-- Star Rating & Google Tag -->
                                                <div class="flex items-center justify-between mb-3.5">
                                                    <div class="flex text-amber-400 gap-0.5">
                                                        <template x-for="i in item.rating" :key="i">
                                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                            </svg>
                                                        </template>
                                                    </div>
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-gray-500 bg-white px-2 py-0.5 rounded-full border border-gray-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        <span x-text="item.source || 'Google Review'"></span>
                                                    </span>
                                                </div>

                                                <!-- Review Body -->
                                                <p class="text-sm text-gray-700 leading-relaxed italic mb-5"
                                                   x-text="'&ldquo;' + item.comment + '&rdquo;'"></p>
                                            </div>

                                            <!-- Reviewer Identity -->
                                            <div class="pt-3.5 border-t border-gray-200/60 flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-brand-primary text-white font-bold text-xs flex items-center justify-center shadow-2xs shrink-0"
                                                     x-text="item.name.split(' ').map(n=>n[0]).join('').substring(0,2)">
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-bold text-brand-dark leading-tight" x-text="item.name"></h4>
                                                    <p class="text-[11px] text-gray-500 mt-0.5">
                                                        <span x-text="item.role + (item.location ? ', ' + item.location : '')"></span> • <span class="text-gray-400" x-text="item.time"></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Fallback if 0 active reviews -->
                                <template x-if="activeReviews.length === 0">
                                    <div class="text-center py-12 px-4 bg-gray-50 rounded-modern-xl border border-dashed border-gray-300 max-w-md mx-auto">
                                        <p class="text-xs text-gray-500 font-medium">Semua ulasan sedang disembunyikan. Aktifkan minimal 1 ulasan pada panel manager di atas.</p>
                                    </div>
                                </template>

                            </div>
                        </section>
                    </div>

                    <!-- Desktop Viewport: Tab 2 (Kunjungi Outlet) - EXACT 100% LP REPLICA -->
                    <div x-show="activeTab === 'location'" class="w-[1366px] min-h-[768px] h-auto bg-brand-cream/80">
                        <section class="py-16 px-12 bg-brand-cream/80 border-t border-gray-200/60 relative w-full min-h-[768px] flex flex-col justify-center">
                            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">

                                <!-- Section Header (LP Exact) -->
                                <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-3"
                                          x-text="footer.location.section.badge"></span>
                                    <h2 class="text-2xl sm:text-4xl font-extrabold text-brand-dark tracking-tight mb-3"
                                        x-text="footer.location.section.title"></h2>
                                    <p class="text-sm sm:text-base text-gray-600 font-normal"
                                       x-text="footer.location.section.subtitle"></p>
                                </div>

                                <!-- 2-Column Grid: Left Store Info, Right Google Maps (LP Exact) -->
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">

                                    <!-- Left Column: Store Information (5 cols) -->
                                    <div class="lg:col-span-5 bg-white p-6 sm:p-8 rounded-modern-lg border border-gray-100 shadow-sm flex flex-col justify-between">
                                        <div class="space-y-6">
                                            <div>
                                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 mb-3 border border-emerald-200">
                                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                                    <span x-text="footer.location.outlet.status_badge"></span>
                                                </div>
                                                <h3 class="text-xl sm:text-2xl font-extrabold text-brand-dark"
                                                    x-text="footer.location.outlet.name"></h3>
                                                <p class="text-xs sm:text-sm text-brand-primary font-medium mt-1"
                                                   x-text="footer.location.outlet.tagline"></p>
                                            </div>

                                            <!-- Address & Details -->
                                            <div class="space-y-4 pt-4 border-t border-gray-100">
                                                <div class="flex items-start gap-3.5">
                                                    <div class="w-9 h-9 rounded-modern bg-brand-soft-green text-brand-primary flex items-center justify-center shrink-0 mt-0.5">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Alamat</h4>
                                                        <p class="text-xs sm:text-sm text-brand-dark font-medium mt-0.5 leading-relaxed"
                                                           x-text="computedLocationAddress"></p>
                                                    </div>
                                                </div>

                                                <div class="flex items-start gap-3.5">
                                                    <div class="w-9 h-9 rounded-modern bg-brand-soft-green text-brand-primary flex items-center justify-center shrink-0 mt-0.5">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Jam Buka</h4>
                                                        <p class="text-xs sm:text-sm text-brand-dark font-medium mt-0.5"
                                                           x-text="footer.location.operational_hours.display"></p>
                                                        <span class="text-[11px] text-gray-500" x-text="footer.location.delivery_note"></span>
                                                    </div>
                                                </div>

                                                <div class="flex items-start gap-3.5">
                                                    <div class="w-9 h-9 rounded-modern bg-brand-soft-green text-brand-primary flex items-center justify-center shrink-0 mt-0.5">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Customer Care &amp; Pemesanan</h4>
                                                        <p class="text-xs sm:text-sm text-brand-dark font-semibold mt-0.5"
                                                           x-text="getLocationContactDisplay()"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Action CTA -->
                                        <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col sm:flex-row gap-3">
                                            <a :href="footer.location.maps.link"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-modern text-xs sm:text-sm font-semibold text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow-md transition-all">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                                </svg>
                                                <span x-text="footer.location.maps.button_text"></span>
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Right Column: Google Maps Container (7 cols) -->
                                    <div class="lg:col-span-7 bg-white rounded-modern-lg overflow-hidden border border-gray-100 shadow-sm relative min-h-[320px] lg:min-h-[420px] flex flex-col">
                                        <!-- Map Header Bar -->
                                        <div class="bg-gray-50 px-4 py-2.5 border-b border-gray-100 flex items-center justify-between text-xs text-gray-500">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                                                <span class="font-medium text-brand-dark" x-text="footer.location.maps.map_title"></span>
                                            </div>
                                            <span x-text="footer.location.maps.map_location_tag"></span>
                                        </div>

                                        <!-- Google Maps iframe -->
                                        <div class="w-full flex-1 relative bg-gray-100 min-h-[340px]">
                                            <iframe
                                                title="Google Maps Lokasi Sumber Protein Jogja"
                                                :src="footer.location.maps.embed"
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
                    </div>

                    <!-- Desktop Viewport: Tab 3 (Footer Actual) - EXACT 100% LP REPLICA -->
                    <div x-show="activeTab === 'footer'" class="w-[1366px] min-h-[768px] h-auto bg-brand-dark text-white">
                        <footer class="bg-brand-dark text-white pt-16 pb-12 border-t border-brand-dark-soft w-full min-h-[768px] flex flex-col justify-end">
                            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full space-y-12">

                                <!-- Top Footer Grid (5 cols LP Exact) -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 lg:gap-8 pb-12 border-b border-white/10">

                                    <!-- Col 1: Brand Info (2 cols wide) -->
                                    <div class="lg:col-span-2 space-y-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-modern bg-brand-primary flex items-center justify-center text-white shadow-md">
                                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 2a5 5 0 0 1 5 5v1a5 5 0 0 1-5 5 5 5 0 0 1-5-5V7a5 5 0 0 1 5-5z" fill="currentColor" fill-opacity="0.2"/>
                                                    <path d="M12 13v9"/>
                                                    <path d="M7 17l5 5 5-5"/>
                                                    <circle cx="12" cy="7" r="3"/>
                                                </svg>
                                            </div>
                                            <span class="text-xl font-extrabold tracking-tight text-white" x-text="footer.actual_footer.brand_title"></span>
                                        </div>

                                        <p class="text-xs sm:text-sm text-gray-300 font-normal leading-relaxed max-w-sm"
                                           x-text="footer.actual_footer.brand_desc"></p>

                                        <!-- Dynamic Social Icons based on URL -->
                                        <div class="flex items-center gap-3 pt-2 flex-wrap">
                                            <template x-for="soc in footer.actual_footer.social_links" :key="soc.id">
                                                <a :href="soc.url || '#'"
                                                   target="_blank"
                                                   rel="noopener noreferrer"
                                                   class="w-9 h-9 rounded-full bg-white/10 text-gray-300 hover:text-white flex items-center justify-center transition-all duration-200"
                                                   :class="getSocialPlatform(soc.url).hoverClass"
                                                   :title="getSocialPlatform(soc.url).name">

                                                    <!-- Instagram SVG -->
                                                    <template x-if="getSocialPlatform(soc.url).key === 'instagram'">
                                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                                    </template>

                                                    <!-- TikTok SVG -->
                                                    <template x-if="getSocialPlatform(soc.url).key === 'tiktok'">
                                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.298-.002.595.042.88.13V9.4a6.33 6.33 0 0 0-1-.08A6.34 6.34 0 0 0 3 15.66a6.34 6.34 0 0 0 10.82 4.47v-7.79a8.27 8.27 0 0 0 5.77 2.27V11a4.84 4.84 0 0 1-3.77-1.72 4.82 4.82 0 0 1-1.23-2.59z"/></svg>
                                                    </template>

                                                    <!-- WhatsApp SVG -->
                                                    <template x-if="getSocialPlatform(soc.url).key === 'whatsapp'">
                                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                                    </template>

                                                    <!-- Facebook SVG -->
                                                    <template x-if="getSocialPlatform(soc.url).key === 'facebook'">
                                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                                    </template>

                                                    <!-- YouTube SVG -->
                                                    <template x-if="getSocialPlatform(soc.url).key === 'youtube'">
                                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                                    </template>

                                                    <!-- X / Twitter SVG -->
                                                    <template x-if="getSocialPlatform(soc.url).key === 'twitter'">
                                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                                    </template>

                                                    <!-- Shopee SVG -->
                                                    <template x-if="getSocialPlatform(soc.url).key === 'shopee'">
                                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19.34 8.71a.84.84 0 0 0-.84-.84h-2.17V5.55A3.55 3.55 0 0 0 12.78 2h-1.56a3.55 3.55 0 0 0-3.55 3.55v2.32H5.5a.84.84 0 0 0-.84.84L3.5 20.37A1.68 1.68 0 0 0 5.18 22h13.64a1.68 1.68 0 0 0 1.68-1.63zM9.17 5.55A2.05 2.05 0 0 1 11.22 3.5h1.56a2.05 2.05 0 0 1 2.05 2.05v2.32H9.17z"/></svg>
                                                    </template>

                                                    <!-- Tokopedia SVG -->
                                                    <template x-if="getSocialPlatform(soc.url).key === 'tokopedia'">
                                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14.93V18c0 .55-.45 1-1 1s-1-.45-1-1v-1.07c-2.31-.38-4-2.37-4-4.93 0-.55.45-1 1-1s1 .45 1 1c0 1.65 1.35 3 3 3s3-1.35 3-3-1.35-3-3-3c-2.76 0-5-2.24-5-5 0-2.56 1.69-4.55 4-4.93V3c0-.55.45-1 1-1s1 .45 1 1v1.07c2.31.38 4 2.37 4 4.93 0 .55-.45 1-1 1s-1-.45-1-1c0-1.65-1.35-3-3-3s-3 1.35-3 3 1.35 3 3 3c2.76 0 5 2.24 5 5 0 2.56-1.69 4.55-4 4.93z"/></svg>
                                                    </template>

                                                    <!-- Generic Link SVG -->
                                                    <template x-if="getSocialPlatform(soc.url).key === 'generic'">
                                                        <svg class="w-4 h-4 fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                                    </template>
                                                </a>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Col 2: Navigation Links -->
                                    <div class="space-y-3">
                                        <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wider" x-text="footer.actual_footer.nav_title || 'Navigasi Cepat'"></h4>
                                        <ul class="space-y-2 text-xs sm:text-sm text-gray-300">
                                            <template x-for="(nav, idx) in footer.actual_footer.nav_links" :key="idx">
                                                <li><a :href="nav.url" class="hover:text-white transition-colors" x-text="nav.title"></a></li>
                                            </template>
                                        </ul>
                                    </div>

                                    <!-- Col 3: Product Types -->
                                    <div class="space-y-3">
                                        <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wider" x-text="footer.actual_footer.category_title || 'Kategori Pangan'"></h4>
                                        <ul class="space-y-2 text-xs sm:text-sm text-gray-300">
                                            <template x-for="(cat, idx) in footer.actual_footer.category_links" :key="idx">
                                                <li><a :href="cat.url" class="hover:text-white transition-colors" x-text="cat.title"></a></li>
                                            </template>
                                        </ul>
                                    </div>

                                    <!-- Col 4: Store & Hours -->
                                    <div class="space-y-3">
                                        <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wider" x-text="footer.actual_footer.outlet_title || 'Outlet Yogyakarta'"></h4>
                                        <p class="text-xs text-gray-300 leading-relaxed" x-text="footer.actual_footer.outlet_address"></p>
                                        <div class="pt-1">
                                            <p class="text-[11px] text-gray-400" x-text="footer.actual_footer.outlet_hours_label || 'Jam Operasional:'"></p>
                                            <p class="text-xs text-white font-medium" x-text="footer.actual_footer.outlet_hours"></p>
                                        </div>
                                        <div class="pt-1">
                                            <p class="text-[11px] text-gray-400" x-text="footer.actual_footer.outlet_phone_label || 'Hotline Pemesanan:'"></p>
                                            <p class="text-xs text-emerald-400 font-semibold" x-text="footer.actual_footer.outlet_phone"></p>
                                        </div>
                                    </div>

                                </div>

                                <!-- Bottom Copyright (LP Exact) -->
                                <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-400 gap-4">
                                    <p x-text="'© ' + new Date().getFullYear() + ' ' + footer.actual_footer.copyright"></p>
                                    <div class="flex items-center gap-6">
                                        <template x-for="(legal, idx) in footer.actual_footer.legal_links" :key="idx">
                                            <span class="hover:text-gray-200 transition-colors cursor-pointer" x-text="legal.title"></span>
                                        </template>
                                    </div>
                                </div>

                            </div>
                        </footer>
                    </div>

                </div>

            </div>

            <!-- =================================================== -->
            <!-- B. TABLET VIEWPORT PREVIEW (1024×768)               -->
            <!-- =================================================== -->
            <div x-show="previewDevice === 'tablet'"
                 class="relative overflow-hidden rounded-modern-lg shadow-2xl transition-all duration-300 mx-auto select-none bg-gray-900"
                 :style="{
                     width: currentFrameWidth + 'px',
                     height: currentFrameHeight + 'px'
                 }">

                <!-- Scaled Tablet Screen Shell (1024px × 768px Virtual Viewport) -->
                <div class="tablet-viewport absolute top-0 left-0 bg-white overflow-y-auto overflow-x-hidden text-left"
                     :style="{
                         width: '1024px',
                         height: '768px',
                         transformOrigin: '0 0',
                         transform: 'scale(' + currentScale + ')'
                     }"
                     style="scroll-behavior: smooth;">

                    <!-- Tablet Viewport: Tab 1 (Ulasan Pelanggan) -->
                    <div x-show="activeTab === 'reviews'" class="w-[1024px] min-h-[768px] h-auto bg-white">
                        <section class="py-14 px-8 bg-white w-full min-h-[768px] text-left flex flex-col justify-center">
                            <div class="max-w-4xl mx-auto w-full">

                                <div class="flex items-end justify-between mb-8 gap-4">
                                    <div>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-2"
                                              x-text="footer.reviews.section_badge || 'Ulasan Pelanggan'"></span>
                                        <h2 class="text-2xl sm:text-3xl font-extrabold text-brand-dark tracking-tight mb-2"
                                            x-text="footer.reviews.section_title || 'Apa Kata Mereka?'"></h2>
                                        <p class="text-xs sm:text-sm text-gray-600 font-normal max-w-lg"
                                           x-text="footer.reviews.section_subtitle"></p>
                                    </div>

                                    <div class="flex items-center gap-2.5 bg-brand-cream p-3 rounded-modern border border-gray-200/80 shadow-xs shrink-0">
                                        <div class="w-9 h-9 rounded-full bg-white flex items-center justify-center shadow-2xs border border-gray-100 shrink-0">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1">
                                                <span class="text-sm font-extrabold text-brand-dark" x-text="footer.reviews.rating + ' / 5.0'"></span>
                                                <div class="flex text-amber-400 gap-0.5">
                                                    <template x-for="i in 5" :key="i">
                                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                        </svg>
                                                    </template>
                                                </div>
                                            </div>
                                            <span class="text-[11px] text-gray-500 font-medium" x-text="'Berdasarkan ' + footer.reviews.total_reviews + ' Reviews'"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 lg:grid-cols-3 gap-5 items-stretch">
                                    <template x-for="item in activeReviews" :key="item.id">
                                        <div class="bg-brand-cream/40 p-5 rounded-modern border border-gray-200/70 shadow-sm flex flex-col justify-between">
                                            <div>
                                                <div class="flex items-center justify-between mb-2.5">
                                                    <div class="flex text-amber-400 gap-0.5">
                                                        <template x-for="i in item.rating" :key="i">
                                                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                            </svg>
                                                        </template>
                                                    </div>
                                                    <span class="text-[10px] font-semibold text-gray-500 bg-white px-2 py-0.5 rounded-full border border-gray-200">Google Review</span>
                                                </div>
                                                <p class="text-xs text-gray-700 leading-relaxed italic mb-4" x-text="'&ldquo;' + item.comment + '&rdquo;'"></p>
                                            </div>

                                            <div class="pt-3 border-t border-gray-200/60 flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-full bg-brand-primary text-white font-bold text-xs flex items-center justify-center shrink-0"
                                                     x-text="item.name.split(' ').map(n=>n[0]).join('').substring(0,2)">
                                                </div>
                                                <div>
                                                    <h4 class="text-xs font-bold text-brand-dark" x-text="item.name"></h4>
                                                    <p class="text-[10px] text-gray-500" x-text="item.role + ' • ' + item.time"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                            </div>
                        </section>
                    </div>

                    <!-- Tablet Viewport: Tab 2 (Kunjungi Outlet) - EXACT 100% LP REPLICA -->
                    <div x-show="activeTab === 'location'" class="w-[1024px] min-h-[768px] h-auto bg-brand-cream/80">
                        <section class="py-14 px-8 bg-brand-cream/80 border-t border-gray-200/60 relative w-full min-h-[768px] flex flex-col justify-center">
                            <div class="max-w-4xl mx-auto w-full">
                                <div class="text-center mb-10">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-2.5"
                                          x-text="footer.location.section.badge"></span>
                                    <h2 class="text-3xl font-extrabold text-brand-dark tracking-tight mb-2"
                                        x-text="footer.location.section.title"></h2>
                                    <p class="text-xs sm:text-sm text-gray-600 font-normal max-w-xl mx-auto"
                                        x-text="footer.location.section.subtitle"></p>
                                </div>

                                <div class="grid grid-cols-12 gap-6 items-stretch">
                                    <div class="col-span-5 bg-white p-6 rounded-modern-lg border border-gray-100 shadow-sm flex flex-col justify-between">
                                        <div class="space-y-4">
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span x-text="footer.location.outlet.status_badge"></span>
                                            </div>
                                            <h3 class="text-xl font-extrabold text-brand-dark" x-text="footer.location.outlet.name"></h3>
                                            <p class="text-xs text-brand-primary font-medium" x-text="footer.location.outlet.tagline"></p>
                                            <div class="pt-3 border-t border-gray-100 text-xs text-gray-700 space-y-2">
                                                <p><strong class="text-gray-400 block text-[10px] uppercase">Alamat</strong><span x-text="computedLocationAddress"></span></p>
                                                <p><strong class="text-gray-400 block text-[10px] uppercase">Jam Buka</strong><span x-text="footer.location.operational_hours.display"></span></p>
                                                <p><strong class="text-gray-400 block text-[10px] uppercase">Customer Care</strong><span x-text="getLocationContactDisplay()"></span></p>
                                            </div>
                                        </div>
                                        <div class="pt-4 border-t border-gray-100">
                                            <a :href="footer.location.maps.link" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-modern text-xs font-semibold text-white bg-brand-primary">
                                                <span x-text="footer.location.maps.button_text"></span>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-span-7 bg-white rounded-modern-lg overflow-hidden border border-gray-100 shadow-sm flex flex-col min-h-[360px]">
                                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-100 flex items-center justify-between text-xs text-gray-500">
                                            <span class="font-medium text-brand-dark" x-text="footer.location.maps.map_title"></span>
                                            <span x-text="footer.location.maps.map_location_tag"></span>
                                        </div>
                                        <div class="w-full flex-1 relative bg-gray-100 min-h-[300px]">
                                            <iframe :src="footer.location.maps.embed" class="absolute inset-0 w-full h-full border-0" allowfullscreen="" loading="lazy"></iframe>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Tablet Viewport: Tab 3 (Footer Actual) - EXACT 100% LP REPLICA -->
                    <div x-show="activeTab === 'footer'" class="w-[1024px] min-h-[768px] h-auto bg-brand-dark text-white">
                        <footer class="bg-brand-dark text-white pt-12 pb-10 border-t border-brand-dark-soft w-full min-h-[768px] flex flex-col justify-end p-8">
                            <div class="max-w-4xl mx-auto w-full space-y-8">
                                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 pb-8 border-b border-white/10">
                                    <div class="col-span-2 space-y-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-modern bg-brand-primary flex items-center justify-center text-white font-bold text-xs">SP</div>
                                            <span class="text-base font-extrabold text-white" x-text="footer.actual_footer.brand_title"></span>
                                        </div>
                                        <p class="text-xs text-gray-300 max-w-sm" x-text="footer.actual_footer.brand_desc"></p>

                                        <!-- Social Icons Tablet -->
                                        <div class="flex items-center gap-2.5 pt-2 flex-wrap">
                                            <template x-for="soc in footer.actual_footer.social_links" :key="soc.id">
                                                <a :href="soc.url || '#'"
                                                   target="_blank"
                                                   rel="noopener noreferrer"
                                                   class="w-8 h-8 rounded-full bg-white/10 text-gray-300 hover:text-white flex items-center justify-center transition-all duration-200"
                                                   :class="getSocialPlatform(soc.url).hoverClass"
                                                   :title="getSocialPlatform(soc.url).name">

                                                    <!-- Instagram SVG -->
                                                    <template x-if="getSocialPlatform(soc.url).key === 'instagram'">
                                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                                    </template>

                                                    <!-- TikTok SVG -->
                                                    <template x-if="getSocialPlatform(soc.url).key === 'tiktok'">
                                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.298-.002.595.042.88.13V9.4a6.33 6.33 0 0 0-1-.08A6.34 6.34 0 0 0 3 15.66a6.34 6.34 0 0 0 10.82 4.47v-7.79a8.27 8.27 0 0 0 5.77 2.27V11a4.84 4.84 0 0 1-3.77-1.72 4.82 4.82 0 0 1-1.23-2.59z"/></svg>
                                                    </template>

                                                    <!-- WhatsApp SVG -->
                                                    <template x-if="getSocialPlatform(soc.url).key === 'whatsapp'">
                                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                                    </template>

                                                    <!-- Facebook SVG -->
                                                    <template x-if="getSocialPlatform(soc.url).key === 'facebook'">
                                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                                    </template>

                                                    <!-- YouTube SVG -->
                                                    <template x-if="getSocialPlatform(soc.url).key === 'youtube'">
                                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                                    </template>

                                                    <!-- X / Twitter SVG -->
                                                    <template x-if="getSocialPlatform(soc.url).key === 'twitter'">
                                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                                    </template>

                                                    <!-- Shopee SVG -->
                                                    <template x-if="getSocialPlatform(soc.url).key === 'shopee'">
                                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M19.34 8.71a.84.84 0 0 0-.84-.84h-2.17V5.55A3.55 3.55 0 0 0 12.78 2h-1.56a3.55 3.55 0 0 0-3.55 3.55v2.32H5.5a.84.84 0 0 0-.84.84L3.5 20.37A1.68 1.68 0 0 0 5.18 22h13.64a1.68 1.68 0 0 0 1.68-1.63zM9.17 5.55A2.05 2.05 0 0 1 11.22 3.5h1.56a2.05 2.05 0 0 1 2.05 2.05v2.32H9.17z"/></svg>
                                                    </template>

                                                    <!-- Tokopedia SVG -->
                                                    <template x-if="getSocialPlatform(soc.url).key === 'tokopedia'">
                                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14.93V18c0 .55-.45 1-1 1s-1-.45-1-1v-1.07c-2.31-.38-4-2.37-4-4.93 0-.55.45-1 1-1s1 .45 1 1c0 1.65 1.35 3 3 3s3-1.35 3-3-1.35-3-3-3c-2.76 0-5-2.24-5-5 0-2.56 1.69-4.55 4-4.93V3c0-.55.45-1 1-1s1 .45 1 1v1.07c2.31.38 4 2.37 4 4.93 0 .55-.45 1-1 1s-1-.45-1-1c0-1.65-1.35-3-3-3s-3 1.35-3 3 1.35 3 3 3c2.76 0 5 2.24 5 5 0 2.56-1.69 4.55-4 4.93z"/></svg>
                                                    </template>

                                                    <!-- Generic Link SVG -->
                                                    <template x-if="getSocialPlatform(soc.url).key === 'generic'">
                                                        <svg class="w-3.5 h-3.5 fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                                    </template>
                                                </a>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wider" x-text="footer.actual_footer.nav_title || 'Navigasi Cepat'"></h4>
                                        <ul class="space-y-1.5 text-xs text-gray-300">
                                            <template x-for="(nav, idx) in footer.actual_footer.nav_links" :key="idx">
                                                <li x-text="nav.title"></li>
                                            </template>
                                        </ul>
                                    </div>
                                    <div class="space-y-2">
                                        <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wider" x-text="footer.actual_footer.outlet_title || 'Outlet Yogyakarta'"></h4>
                                        <p class="text-xs text-gray-300" x-text="footer.actual_footer.outlet_address"></p>
                                        <p class="text-xs text-emerald-400 font-semibold" x-text="footer.actual_footer.outlet_phone"></p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-xs text-gray-400 pt-4">
                                    <p x-text="'© ' + new Date().getFullYear() + ' ' + footer.actual_footer.copyright"></p>
                                    <div class="flex gap-4">
                                        <template x-for="(legal, idx) in footer.actual_footer.legal_links" :key="idx">
                                            <span x-text="legal.title"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </footer>
                    </div>

                </div>

            </div>

            <!-- =================================================== -->
            <!-- C. MOBILE DEVICE SIMULATOR (393×852 SCROLLABLE)     -->
            <!-- =================================================== -->
            <div x-show="previewDevice === 'mobile'"
                 class="relative overflow-hidden transition-all duration-300 mx-auto select-none"
                 :style="{
                     width: currentFrameWidth + 'px',
                     height: currentFrameHeight + 'px'
                 }">

                <!-- Mobile Outer Scaled Shell (393px × 852px scaled) -->
                <div class="absolute top-0 left-0 rounded-[44px] border-[4px] border-slate-700 bg-slate-950 shadow-2xl overflow-hidden flex flex-col"
                     :style="{
                         width: '393px',
                         height: '852px',
                         transformOrigin: '0 0',
                         transform: 'scale(' + currentScale + ')'
                     }">

                    <!-- Dynamic Island Overlay (Top Center) -->
                    <div class="absolute top-2.5 left-1/2 -translate-x-1/2 w-28 h-6 bg-black rounded-full z-30 pointer-events-none shadow-md flex items-center justify-between px-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-900 border border-slate-800"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>

                    <!-- Mobile Scrollable Screen (Viewport 393×852) -->
                    <div class="mobile-viewport w-full h-full overflow-y-auto overflow-x-hidden text-left bg-white"
                         style="scroll-behavior: smooth;">

                        <!-- Mobile Viewport: Tab 1 (Ulasan Pelanggan) - Exact LP Representation -->
                        <div x-show="activeTab === 'reviews'" class="bg-white min-h-full py-10 px-4 space-y-6">

                            <!-- Mobile Section Header -->
                            <div class="text-center max-w-xs mx-auto pt-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-2.5 shadow-2xs"
                                      x-text="footer.reviews.section_badge || 'Ulasan Pelanggan'"></span>
                                <h2 class="text-2xl font-extrabold text-brand-dark tracking-tight mb-2 leading-tight"
                                    x-text="footer.reviews.section_title || 'Apa Kata Mereka?'"></h2>
                                <p class="text-xs text-gray-600 font-normal leading-relaxed"
                                   x-text="footer.reviews.section_subtitle"></p>
                            </div>

                            <!-- Google Review Aggregate Rating Box -->
                            <div class="flex items-center gap-3 bg-brand-cream p-3.5 rounded-modern border border-gray-200/80 shadow-xs">
                                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-2xs border border-gray-100 shrink-0">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24">
                                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-sm font-extrabold text-brand-dark" x-text="footer.reviews.rating + ' / 5.0'"></span>
                                        <div class="flex text-amber-400 gap-0.5">
                                            <template x-for="i in 5" :key="i">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            </template>
                                        </div>
                                    </div>
                                    <span class="text-[10px] text-gray-500 font-medium" x-text="'Berdasarkan ' + footer.reviews.total_reviews + ' Reviews Google'"></span>
                                </div>
                            </div>

                            <!-- Mobile Cards Stack -->
                            <div class="space-y-4">
                                <template x-for="item in activeReviews" :key="item.id">
                                    <div class="bg-brand-cream/40 p-4 rounded-modern border border-gray-200/70 shadow-sm space-y-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex text-amber-400 gap-0.5">
                                                <template x-for="i in item.rating" :key="i">
                                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                </template>
                                            </div>
                                            <span class="text-[9px] font-semibold text-gray-500 bg-white px-2 py-0.5 rounded-full border border-gray-200">Google Review</span>
                                        </div>

                                        <p class="text-xs text-gray-700 leading-relaxed italic" x-text="'&ldquo;' + item.comment + '&rdquo;'"></p>

                                        <div class="pt-2.5 border-t border-gray-200/60 flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full bg-brand-primary text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs"
                                                 x-text="item.name.split(' ').map(n=>n[0]).join('').substring(0,2)">
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-bold text-brand-dark" x-text="item.name"></h4>
                                                <p class="text-[10px] text-gray-400" x-text="item.role + ' • ' + item.time"></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Bottom Indicator -->
                            <div class="pt-2 text-center" x-show="activeReviews.length > 0">
                                <span class="inline-flex items-center gap-1 text-[10px] text-gray-400 font-medium">
                                    <span>↕</span>
                                    <span>Scroll untuk melihat seluruh ulasan</span>
                                </span>
                            </div>

                        </div>

                        <!-- Mobile Viewport: Tab 2 (Kunjungi Outlet) - EXACT 100% LP REPLICA -->
                        <div x-show="activeTab === 'location'" class="bg-brand-cream/80 min-h-full py-10 px-4 space-y-6">

                            <!-- Header (Mobile LP Exact) -->
                            <div class="text-center max-w-xs mx-auto pt-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-2.5 shadow-2xs"
                                      x-text="footer.location.section.badge"></span>
                                <h2 class="text-2xl font-extrabold text-brand-dark tracking-tight mb-2 leading-tight"
                                    x-text="footer.location.section.title"></h2>
                                <p class="text-xs text-gray-600 font-normal leading-relaxed"
                                   x-text="footer.location.section.subtitle"></p>
                            </div>

                            <!-- Mobile Store Info Card -->
                            <div class="bg-white p-5 rounded-modern-lg border border-gray-100 shadow-sm space-y-5">
                                <div>
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-800 mb-2 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                        <span x-text="footer.location.outlet.status_badge"></span>
                                    </div>
                                    <h3 class="text-lg font-extrabold text-brand-dark" x-text="footer.location.outlet.name"></h3>
                                    <p class="text-xs text-brand-primary font-medium mt-0.5" x-text="footer.location.outlet.tagline"></p>
                                </div>

                                <div class="space-y-3 pt-3 border-t border-gray-100 text-xs text-gray-700">
                                    <div class="flex items-start gap-2.5">
                                        <span class="text-brand-primary text-sm">📍</span>
                                        <div>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Alamat</span>
                                            <p class="leading-relaxed" x-text="computedLocationAddress"></p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2.5">
                                        <span class="text-brand-primary text-sm">🕒</span>
                                        <div>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Jam Buka</span>
                                            <p x-text="footer.location.operational_hours.display"></p>
                                            <span class="text-[10px] text-gray-500" x-text="footer.location.delivery_note"></span>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2.5">
                                        <span class="text-brand-primary text-sm">📞</span>
                                        <div>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Customer Care</span>
                                            <p class="font-bold text-brand-dark" x-text="getLocationContactDisplay()"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-gray-100">
                                    <a :href="footer.location.maps.link" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-modern text-xs font-semibold text-white bg-brand-primary shadow-xs">
                                        <span x-text="footer.location.maps.button_text"></span>
                                    </a>
                                </div>
                            </div>

                            <!-- Mobile Google Maps Card -->
                            <div class="bg-white rounded-modern-lg overflow-hidden border border-gray-100 shadow-sm flex flex-col">
                                <div class="bg-gray-50 px-3.5 py-2 border-b border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
                                    <span class="font-medium text-brand-dark" x-text="footer.location.maps.map_title"></span>
                                    <span x-text="footer.location.maps.map_location_tag"></span>
                                </div>
                                <div class="w-full h-52 relative bg-gray-100">
                                    <iframe :src="footer.location.maps.embed" class="absolute inset-0 w-full h-full border-0" allowfullscreen="" loading="lazy"></iframe>
                                </div>
                            </div>

                            <!-- Bottom Indicator -->
                            <div class="pt-2 text-center">
                                <span class="inline-flex items-center gap-1 text-[10px] text-gray-400 font-medium">
                                    <span>↕</span>
                                    <span>Scroll untuk melihat detail lokasi & peta</span>
                                </span>
                            </div>

                        </div>

                        <!-- Mobile Viewport: Tab 3 (Footer Actual) - EXACT 100% LP REPLICA -->
                        <div x-show="activeTab === 'footer'" class="bg-brand-dark min-h-full py-10 px-4 space-y-6 text-white">

                            <!-- Brand & Intro -->
                            <div class="space-y-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-modern bg-brand-primary flex items-center justify-center text-white font-bold text-xs shadow-md">
                                        SP
                                    </div>
                                    <span class="text-base font-extrabold text-white" x-text="footer.actual_footer.brand_title"></span>
                                </div>
                                <p class="text-xs text-gray-300 leading-relaxed" x-text="footer.actual_footer.brand_desc"></p>

                                <!-- Dynamic Social Icons Mobile -->
                                <div class="flex items-center gap-2.5 pt-1 flex-wrap">
                                    <template x-for="soc in footer.actual_footer.social_links" :key="soc.id">
                                        <a :href="soc.url || '#'"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="w-8 h-8 rounded-full bg-white/10 text-gray-300 hover:text-white flex items-center justify-center transition-all duration-200"
                                           :class="getSocialPlatform(soc.url).hoverClass"
                                           :title="getSocialPlatform(soc.url).name">

                                            <!-- Instagram SVG -->
                                            <template x-if="getSocialPlatform(soc.url).key === 'instagram'">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                            </template>

                                            <!-- TikTok SVG -->
                                            <template x-if="getSocialPlatform(soc.url).key === 'tiktok'">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.298-.002.595.042.88.13V9.4a6.33 6.33 0 0 0-1-.08A6.34 6.34 0 0 0 3 15.66a6.34 6.34 0 0 0 10.82 4.47v-7.79a8.27 8.27 0 0 0 5.77 2.27V11a4.84 4.84 0 0 1-3.77-1.72 4.82 4.82 0 0 1-1.23-2.59z"/></svg>
                                            </template>

                                            <!-- WhatsApp SVG -->
                                            <template x-if="getSocialPlatform(soc.url).key === 'whatsapp'">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                            </template>

                                            <!-- Facebook SVG -->
                                            <template x-if="getSocialPlatform(soc.url).key === 'facebook'">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                            </template>

                                            <!-- YouTube SVG -->
                                            <template x-if="getSocialPlatform(soc.url).key === 'youtube'">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                            </template>

                                            <!-- X / Twitter SVG -->
                                            <template x-if="getSocialPlatform(soc.url).key === 'twitter'">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                            </template>

                                            <!-- Shopee SVG -->
                                            <template x-if="getSocialPlatform(soc.url).key === 'shopee'">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M19.34 8.71a.84.84 0 0 0-.84-.84h-2.17V5.55A3.55 3.55 0 0 0 12.78 2h-1.56a3.55 3.55 0 0 0-3.55 3.55v2.32H5.5a.84.84 0 0 0-.84.84L3.5 20.37A1.68 1.68 0 0 0 5.18 22h13.64a1.68 1.68 0 0 0 1.68-1.63zM9.17 5.55A2.05 2.05 0 0 1 11.22 3.5h1.56a2.05 2.05 0 0 1 2.05 2.05v2.32H9.17z"/></svg>
                                            </template>

                                            <!-- Tokopedia SVG -->
                                            <template x-if="getSocialPlatform(soc.url).key === 'tokopedia'">
                                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14.93V18c0 .55-.45 1-1 1s-1-.45-1-1v-1.07c-2.31-.38-4-2.37-4-4.93 0-.55.45-1 1-1s1 .45 1 1c0 1.65 1.35 3 3 3s3-1.35 3-3-1.35-3-3-3c-2.76 0-5-2.24-5-5 0-2.56 1.69-4.55 4-4.93V3c0-.55.45-1 1-1s1 .45 1 1v1.07c2.31.38 4 2.37 4 4.93 0 .55-.45 1-1 1s-1-.45-1-1c0-1.65-1.35-3-3-3s-3 1.35-3 3 1.35 3 3 3c2.76 0 5 2.24 5 5 0 2.56-1.69 4.55-4 4.93z"/></svg>
                                            </template>

                                            <!-- Generic Link SVG -->
                                            <template x-if="getSocialPlatform(soc.url).key === 'generic'">
                                                <svg class="w-3.5 h-3.5 fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                            </template>
                                        </a>
                                    </template>
                                </div>
                            </div>

                            <!-- Outlet Yogyakarta Section -->
                            <div class="space-y-2 pt-3 border-t border-white/10">
                                <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wider" x-text="footer.actual_footer.outlet_title || 'Outlet Yogyakarta'"></h4>
                                <p class="text-xs text-gray-300 leading-relaxed" x-text="footer.actual_footer.outlet_address"></p>
                                <p class="text-xs text-white font-medium" x-text="footer.actual_footer.outlet_hours"></p>
                                <p class="text-xs text-emerald-400 font-semibold" x-text="footer.actual_footer.outlet_phone"></p>
                            </div>

                            <!-- Quick Navigation Links -->
                            <div class="space-y-2 pt-3 border-t border-white/10">
                                <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wider" x-text="footer.actual_footer.nav_title || 'Navigasi Cepat'"></h4>
                                <ul class="grid grid-cols-2 gap-1.5 text-xs text-gray-300">
                                    <template x-for="(nav, idx) in footer.actual_footer.nav_links" :key="idx">
                                        <li x-text="nav.title"></li>
                                    </template>
                                </ul>
                            </div>

                            <!-- Food Categories Links -->
                            <div class="space-y-2 pt-3 border-t border-white/10">
                                <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wider" x-text="footer.actual_footer.category_title || 'Kategori Pangan'"></h4>
                                <ul class="grid grid-cols-2 gap-1.5 text-xs text-gray-300">
                                    <template x-for="(cat, idx) in footer.actual_footer.category_links" :key="idx">
                                        <li x-text="cat.title"></li>
                                    </template>
                                </ul>
                            </div>

                            <!-- Bottom Copyright & Legal Links -->
                            <div class="pt-4 border-t border-white/10 text-xs text-gray-400 space-y-2">
                                <p x-text="'© ' + new Date().getFullYear() + ' ' + footer.actual_footer.copyright"></p>
                                <div class="flex flex-wrap gap-3 text-[11px]">
                                    <template x-for="(legal, idx) in footer.actual_footer.legal_links" :key="idx">
                                        <span x-text="legal.title"></span>
                                    </template>
                                </div>
                            </div>

                            <!-- Bottom Indicator -->
                            <div class="pt-2 text-center">
                                <span class="inline-flex items-center gap-1 text-[10px] text-gray-500 font-medium">
                                    <span>↕</span>
                                    <span>Scroll untuk melihat seluruh informasi footer</span>
                                </span>
                            </div>

                        </div>

                    </div>

                    <!-- Mobile Bottom Home Bar -->
                    <div class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-32 h-1 bg-black/40 rounded-full z-30 pointer-events-none"></div>

                </div>

            </div>

        </div>

        <!-- Bottom Viewport Information Caption -->
        <p class="text-[11px] text-gray-400 text-center">
            <span x-show="previewDevice === 'desktop'">💻 Virtual Desktop 14" (1366&times;768, 16:9) &bull; Viewport berskala seragam memenuhi frame stabil &bull; Scrollable jika konten melebihi tinggi layar.</span>
            <span x-show="previewDevice === 'tablet'">📱 Virtual Tablet (1024&times;768, 4:3) &bull; Viewport berskala seragam memenuhi frame stabil &bull; Scrollable jika konten melebihi tinggi layar.</span>
            <span x-show="previewDevice === 'mobile'">📱 Virtual Mobile (393&times;852, 9:16) &bull; Arahkan kursor &amp; scroll di dalam layar mobile (natural height).</span>
        </p>

    </div>

    <!-- ======================================================= -->
    <!-- REVIEW CREATE / EDIT MODAL                              -->
    <!-- ======================================================= -->
    <div x-show="reviewModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-xs" @click="reviewModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white rounded-modern-xl max-w-md w-full p-6 shadow-xl border border-gray-200 space-y-4">

                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-base">⭐</span>
                        <h3 class="text-sm font-extrabold text-brand-dark" x-text="isEditingReview ? 'Edit Ulasan Pelanggan' : 'Tambah Ulasan Pelanggan'"></h3>
                    </div>
                    <button @click="reviewModalOpen = false" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-3">
                    <!-- Reviewer Name -->
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Nama Reviewer <span class="text-red-500">*</span></label>
                        <input type="text" x-model="reviewForm.reviewer_name" placeholder="Contoh: Ibu Ratna Dewi" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-bold text-brand-dark">
                    </div>

                    <!-- Role / Title & Location -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Profesi / Peran</label>
                            <input type="text" x-model="reviewForm.reviewer_title" placeholder="Ibu Rumah Tangga" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Lokasi</label>
                            <input type="text" x-model="reviewForm.reviewer_location" placeholder="Sleman, Yogyakarta" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white">
                        </div>
                    </div>

                    <!-- Rating, Source & Active -->
                    <div class="grid grid-cols-3 gap-2.5">
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Rating</label>
                            <select x-model.number="reviewForm.rating" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-bold text-amber-500">
                                <option value="5">⭐⭐⭐⭐⭐ 5</option>
                                <option value="4">⭐⭐⭐⭐ 4</option>
                                <option value="3">⭐⭐⭐ 3</option>
                                <option value="2">⭐⭐ 2</option>
                                <option value="1">⭐ 1</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Sumber</label>
                            <select x-model="reviewForm.source" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-bold">
                                <option value="manual">⭐ Manual</option>
                                <option value="google">🗺️ Google</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Status</label>
                            <select x-model="reviewForm.is_active" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-bold">
                                <option :value="true">Aktif</option>
                                <option :value="false">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <!-- Review Text -->
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Isi Ulasan <span class="text-red-500">*</span></label>
                        <textarea x-model="reviewForm.review_text" rows="3" placeholder="Tuliskan pengalaman pelanggan..." class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white leading-relaxed"></textarea>
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button @click="reviewModalOpen = false" type="button" class="px-4 py-2 rounded-modern text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button @click="saveReview()" type="button" class="px-4 py-2 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark transition-colors cursor-pointer">
                        Simpan Ulasan
                    </button>
                </div>

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
