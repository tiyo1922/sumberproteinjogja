@extends('layouts.admin', [
    'title' => 'Keunggulan & Standar Mutu',
    'pageTitle' => 'Keunggulan & Standar Mutu'
])

@section('content')
<div class="space-y-8"
     x-data="{
         benefits: {{ json_encode($benefitsData) }},
         quality: {{ json_encode($qualityStandardsData) }},
         activeTab: 'benefits', // 'benefits' | 'quality'
         
         // Preview Device & Virtual Viewport State
         previewDevice: 'desktop', // 'desktop' | 'tablet' | 'mobile'
         previewBoxWidth: 1000,
         previewBoxHeight: 550,
         previewObserver: null,
         toastMessage: '',
         toastVisible: false,
         
         iconOptions: [
             { id: 'grid', label: 'Grid / Kotak (Pilihan Lengkap)' },
             { id: 'shield', label: 'Shield / Perisai (Higienis & Cold Chain)' },
             { id: 'clock', label: 'Clock / Jam (Ready to Cook Praktis)' },
             { id: 'truck', label: 'Truck / Truk (Pengiriman & Curah)' },
         ],
         
         // Reference Viewport Dimensions (Laptop 14-inch 1366x768, Tablet 1024x768, Mobile 393x852)
         virtualDimensions: {
             desktop: { width: 1366, height: 768 },
             tablet:  { width: 1024, height: 768 },
             mobile:  { width: 393,  height: 852 }
         },
         
         get currentVirtualWidth() {
             return this.virtualDimensions[this.previewDevice]?.width || (this.previewDevice === 'mobile' ? 393 : (this.previewDevice === 'tablet' ? 1024 : 1366));
         },
         
         get currentVirtualHeight() {
             return this.virtualDimensions[this.previewDevice]?.height || (this.previewDevice === 'mobile' ? 852 : 768);
         },
         
         // Scale dynamically fits BOTH available width AND fixed available height of previewBoxWrapper
         get currentScale() {
             const availableW = Math.max(200, (this.previewBoxWidth || 1000) - 24);
             const availableH = Math.max(200, (this.previewBoxHeight || 550) - 24);
             const scaleX = availableW / this.currentVirtualWidth;
             const scaleY = availableH / this.currentVirtualHeight;
             return Math.min(scaleX, scaleY);
         },
         
         get currentFrameWidth() {
             return Math.round(this.currentVirtualWidth * this.currentScale);
         },
         
         get currentFrameHeight() {
             return Math.round(this.currentVirtualHeight * this.currentScale);
         },

         // =========================================================
         // Content Validation Helpers (Empty Item Filtering)
         // =========================================================
         hasBenefitContent(item) {
             if (!item) return false;
             const title = (item.title || '').trim();
             const desc = (item.desc || item.subtitle || '').trim();
             return title.length > 0 || desc.length > 0;
         },

         hasQualityContent(pk) {
             if (!pk) return false;
             const name = (pk.name || '').trim();
             const tag = (pk.tag || '').trim();
             const desc = (pk.desc || '').trim();
             const hasFeat = Array.isArray(pk.features) && pk.features.some(f => (f || '').trim().length > 0);
             return name.length > 0 || tag.length > 0 || desc.length > 0 || hasFeat;
         },

         get validBenefitsItems() {
             return (this.benefits.items || []).filter(item => this.hasBenefitContent(item));
         },

         get validQualityItems() {
             return (this.quality.items || []).filter(pk => this.hasQualityContent(pk));
         },

         // =========================================================
         // Add & Delete Item Handlers
         // =========================================================
         deleteModalOpen: false,
         deleteTargetType: null, // 'benefit' | 'quality'
         selectedItemIndex: null,
         selectedItemName: '',

         addBenefitItem() {
             const nextId = (this.benefits.items && this.benefits.items.length > 0) 
                 ? Math.max(...this.benefits.items.map(i => parseInt(i.id) || 0)) + 1 
                 : 1;
             this.benefits.items.push({
                 id: nextId,
                 title: 'Keunggulan Baru',
                 desc: 'Deskripsi poin keunggulan komitmen layanan dan mutu produk.',
                 icon: 'shield'
             });
             this.showToast('Poin keunggulan baru berhasil ditambahkan!');
         },

         openDeleteBenefit(idx, item) {
             if (this.benefits.items.length <= 1) {
                 this.showToast('Minimal harus ada 1 poin keunggulan.');
                 return;
             }
             this.deleteTargetType = 'benefit';
             this.selectedItemIndex = idx;
             this.selectedItemName = item.title || ('Keunggulan ' + (idx + 1));
             this.deleteModalOpen = true;
         },

         removeBenefitItem(idx) {
             const item = this.benefits.items[idx];
             this.openDeleteBenefit(idx, item || {});
         },

         addQualityItem() {
             const nextId = (this.quality.items && this.quality.items.length > 0) 
                 ? Math.max(...this.quality.items.map(i => parseInt(i.id) || 0)) + 1 
                 : 1;
             this.quality.items.push({
                 id: nextId,
                 tag: 'Standar Mutu',
                 name: 'Pilar Baru',
                 desc: 'Penjelasan standar jaminan kualitas dan keamanan pangan.',
                 features: [
                     'Proses seleksi ketat',
                     'Kontrol higienitas berkala',
                     'Cold chain terpadu'
                 ]
             });
             this.showToast('Pilar standar mutu baru berhasil ditambahkan!');
         },

         openDeleteQuality(idx, pk) {
             if (this.quality.items.length <= 1) {
                 this.showToast('Minimal harus ada 1 pilar standar mutu.');
                 return;
             }
             this.deleteTargetType = 'quality';
             this.selectedItemIndex = idx;
             this.selectedItemName = pk.name || ('Pilar ' + (idx + 1));
             this.deleteModalOpen = true;
         },

         removeQualityItem(idx) {
             const pk = this.quality.items[idx];
             this.openDeleteQuality(idx, pk || {});
         },

         confirmDelete() {
             if (this.deleteTargetType === 'benefit' && this.selectedItemIndex !== null) {
                 this.benefits.items.splice(this.selectedItemIndex, 1);
                 this.showToast('Poin keunggulan berhasil dihapus.');
             } else if (this.deleteTargetType === 'quality' && this.selectedItemIndex !== null) {
                 this.quality.items.splice(this.selectedItemIndex, 1);
                 this.showToast('Pilar standar mutu berhasil dihapus.');
             }
             this.deleteModalOpen = false;
             this.deleteTargetType = null;
             this.selectedItemIndex = null;
             this.selectedItemName = '';
         },
         
         initPreviewObserver() {
             this.$nextTick(() => {
                 if (this.$refs.previewBoxWrapper) {
                     const rect = this.$refs.previewBoxWrapper.getBoundingClientRect();
                     if (rect.width > 50) {
                         this.previewBoxWidth = rect.width;
                         this.previewBoxHeight = rect.height;
                     }
                     if (!this.previewObserver && window.ResizeObserver) {
                         this.previewObserver = new ResizeObserver((entries) => {
                             for (let entry of entries) {
                                 const w = entry.contentRect.width;
                                 const h = entry.contentRect.height;
                                 if (w > 50) this.previewBoxWidth = w;
                                 if (h > 50) this.previewBoxHeight = h;
                             }
                         });
                         this.previewObserver.observe(this.$refs.previewBoxWrapper);
                     }
                 }
             });
         },
         
         showToast(msg) {
             this.toastMessage = msg;
             this.toastVisible = true;
             setTimeout(() => { this.toastVisible = false; }, 3000);
         },
         
         saveSection() {
             this.showToast('Pengaturan Keunggulan & Standar Mutu berhasil disimpan!');
         }
     }"
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
            background-color: rgba(0, 0, 0, 0.25);
            border-radius: 9999px;
        }
    </style>
    
    <!-- ======================================================= -->
    <!-- 1. HEADER CARD & ACTIONS                                -->
    <!-- ======================================================= -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-8 shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-brand-dark tracking-tight">
                        Keunggulan &amp; Standar Mutu
                    </h2>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                        <span>REAL PREVIEW &bull; DYNAMIC ITEM MANAGEMENT</span>
                    </span>
                    <span class="text-xs text-gray-500 font-medium">
                        • Desktop (1366&times;768), Tablet (1024&times;768) &amp; Mobile (393&times;852)
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 leading-relaxed max-w-3xl">
                    Kelola narasi nilai keunggulan, poin kepraktisan belanja, dan pilar standar jaminan mutu produk. Mendukung tambah/hapus item secara dinamis dan filtering card kosong otomatis.
                </p>
            </div>

            <!-- Save Action Button -->
            <div class="flex items-center gap-3 shrink-0">
                <button @click="saveSection()" 
                        type="button"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-modern font-bold text-xs sm:text-sm text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer">
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 2. NAVIGATION TABS (TAB A / TAB B)                       -->
    <!-- ======================================================= -->
    <div class="flex items-center gap-2 border-b border-gray-200 pb-2">
        <button @click="activeTab = 'benefits'" 
                type="button" 
                :class="activeTab === 'benefits' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer flex items-center gap-1.5">
            <span>⭐</span>
            <span>A. Kenapa Memilih Kami (<span x-text="benefits.items.length"></span> Poin)</span>
        </button>
        <button @click="activeTab = 'quality'" 
                type="button" 
                :class="activeTab === 'quality' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer flex items-center gap-1.5">
            <span>🛡️</span>
            <span>B. Standar Mutu (<span x-text="quality.items.length"></span> Pilar)</span>
        </button>
    </div>

    <!-- ======================================================= -->
    <!-- 3. FULL WIDTH FIELD EDIT CONTENT                        -->
    <!-- ======================================================= -->
    <div class="space-y-6">

        <!-- TAB A: KENAPA MEMILIH KAMI -->
        <div x-show="activeTab === 'benefits'" class="space-y-6">
            
            <!-- Section Header Config Card -->
            <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-7 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-primary"></span>
                        <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">Header Section: Kenapa Memilih Kami</h3>
                    </div>
                    <span class="text-[11px] text-gray-400 font-mono">Dynamic Section Header</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-4">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Badge Tag</label>
                        <input type="text" 
                               x-model="benefits.section_badge" 
                               class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2.5 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary font-medium text-brand-dark"
                               placeholder="Contoh: Kenapa Memilih Kami">
                    </div>

                    <div class="md:col-span-8">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Judul Section (H2)</label>
                        <input type="text" 
                               x-model="benefits.section_title" 
                               class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2.5 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary font-extrabold text-brand-dark"
                               placeholder="Contoh: Lebih Praktis, Lebih Siap">
                    </div>

                    <div class="md:col-span-12">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Subjudul / Deskripsi Singkat</label>
                        <textarea x-model="benefits.section_subtitle" 
                                  rows="2"
                                  class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary text-gray-700 leading-relaxed"
                                  placeholder="Penjelasan ringkas komitmen mutu..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Poin Keunggulan Cards Editor (Dynamic Items with Add & Delete) -->
            <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-7 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                            Kartu Poin Keunggulan (<span x-text="benefits.items.length"></span> Item)
                        </h3>
                    </div>
                    
                    <!-- Add Item Button -->
                    <button @click="addBenefitItem()" 
                            type="button"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-modern text-xs font-bold text-brand-primary bg-brand-soft-green hover:bg-emerald-100 border border-brand-soft-green-border transition-colors cursor-pointer shadow-2xs">
                        <span class="text-sm leading-none font-bold">＋</span>
                        <span>Tambah Keunggulan</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <template x-for="(item, idx) in benefits.items" :key="item.id">
                        <div class="border border-gray-200 rounded-modern-lg p-4 bg-gray-50/50 hover:bg-white transition-all duration-200 space-y-3 relative group">
                            
                            <!-- Card Header: Number, Title, Empty Indicator & Delete Action -->
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-5 h-5 rounded-full bg-brand-primary/10 text-brand-primary text-[11px] font-extrabold flex items-center justify-center shrink-0" x-text="idx + 1"></span>
                                    <span class="text-xs font-bold text-brand-dark truncate" x-text="item.title || ('Keunggulan ' + (idx + 1))"></span>
                                    <template x-if="!hasBenefitContent(item)">
                                        <span class="text-[10px] text-amber-700 bg-amber-50 px-2 py-0.5 rounded font-semibold border border-amber-200 shrink-0">
                                            Kosong (Tidak Tampil di Preview)
                                        </span>
                                    </template>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-[11px] text-gray-400 font-mono" x-text="'ID: ' + item.id"></span>
                                    <!-- Delete Button -->
                                    <button @click="openDeleteBenefit(idx, item)" 
                                            :disabled="benefits.items.length <= 1"
                                            type="button" 
                                            :title="benefits.items.length <= 1 ? 'Minimal harus ada 1 poin' : 'Hapus poin keunggulan ini'"
                                            class="p-1.5 rounded-modern text-gray-400 hover:text-rose-600 hover:bg-rose-50 disabled:opacity-30 disabled:hover:text-gray-400 disabled:hover:bg-transparent transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                <!-- Title -->
                                <div class="sm:col-span-8">
                                    <label class="block text-[11px] font-semibold text-gray-600 mb-1">Judul Poin</label>
                                    <input type="text" 
                                           x-model="item.title"
                                           class="w-full text-xs rounded-modern border border-gray-300 px-2.5 py-1.5 focus:ring-1 focus:ring-brand-primary font-bold text-brand-dark"
                                           placeholder="Judul poin keunggulan...">
                                </div>

                                <!-- Icon Picker -->
                                <div class="sm:col-span-4">
                                    <label class="block text-[11px] font-semibold text-gray-600 mb-1">Ikon Representasi</label>
                                    <select x-model="item.icon" 
                                            class="w-full text-xs rounded-modern border border-gray-300 px-2 py-1.5 focus:ring-1 focus:ring-brand-primary font-medium text-brand-dark bg-white">
                                        <template x-for="opt in iconOptions" :key="opt.id">
                                            <option :value="opt.id" x-text="opt.label"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Description -->
                                <div class="sm:col-span-12">
                                    <label class="block text-[11px] font-semibold text-gray-600 mb-1">Deskripsi Poin</label>
                                    <textarea x-model="item.desc" 
                                              rows="2"
                                              class="w-full text-xs rounded-modern border border-gray-300 px-2.5 py-1.5 focus:ring-1 focus:ring-brand-primary text-gray-700 leading-relaxed"
                                              placeholder="Uraian komitmen atau keunggulan..."></textarea>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>

        <!-- TAB B: 4 PILAR STANDAR MUTU -->
        <div x-show="activeTab === 'quality'" class="space-y-6">
            
            <!-- Section Header Config Card -->
            <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-7 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-primary"></span>
                        <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">Header Section: Standar Mutu</h3>
                    </div>
                    <span class="text-[11px] text-gray-400 font-mono">Dynamic Section Header</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-4">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Badge Tag</label>
                        <input type="text" 
                               x-model="quality.section_badge" 
                               class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2.5 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary font-medium text-brand-dark"
                               placeholder="Contoh: Standar Mutu">
                    </div>

                    <div class="md:col-span-8">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Judul Section (H2)</label>
                        <input type="text" 
                               x-model="quality.section_title" 
                               class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2.5 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary font-extrabold text-brand-dark"
                               placeholder="Contoh: Mengenal Standar Produk Kami">
                    </div>

                    <div class="md:col-span-12">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Subjudul / Deskripsi Singkat</label>
                        <textarea x-model="quality.section_subtitle" 
                                  rows="2"
                                  class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary text-gray-700 leading-relaxed"
                                  placeholder="Penjelasan ringkas standar jaminan kualitas..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Pilar Standar Mutu Cards Editor (Dynamic Items with Add & Delete) -->
            <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-7 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                            Pilar Standar Jaminan Mutu (<span x-text="quality.items.length"></span> Pilar)
                        </h3>
                    </div>

                    <!-- Add Pillar Button -->
                    <button @click="addQualityItem()" 
                            type="button"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-modern text-xs font-bold text-brand-primary bg-brand-soft-green hover:bg-emerald-100 border border-brand-soft-green-border transition-colors cursor-pointer shadow-2xs">
                        <span class="text-sm leading-none font-bold">＋</span>
                        <span>Tambah Pilar Mutu</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <template x-for="(pk, idx) in quality.items" :key="pk.id">
                        <div class="border border-gray-200 rounded-modern-lg p-4 bg-gray-50/50 hover:bg-white transition-all duration-200 space-y-3 relative group">
                            
                            <!-- Card Header: Number, Title, Empty Indicator & Delete Action -->
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-5 h-5 rounded-full bg-brand-primary/10 text-brand-primary text-[11px] font-extrabold flex items-center justify-center shrink-0" x-text="idx + 1"></span>
                                    <span class="text-xs font-bold text-brand-dark truncate" x-text="pk.name || ('Pilar ' + (idx + 1))"></span>
                                    <template x-if="!hasQualityContent(pk)">
                                        <span class="text-[10px] text-amber-700 bg-amber-50 px-2 py-0.5 rounded font-semibold border border-amber-200 shrink-0">
                                            Kosong (Tidak Tampil di Preview)
                                        </span>
                                    </template>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-[11px] text-gray-400 font-mono" x-text="'Tag: ' + pk.tag"></span>
                                    <!-- Delete Button -->
                                    <button @click="openDeleteQuality(idx, pk)" 
                                            :disabled="quality.items.length <= 1"
                                            type="button" 
                                            :title="quality.items.length <= 1 ? 'Minimal harus ada 1 pilar' : 'Hapus pilar standar mutu ini'"
                                            class="p-1.5 rounded-modern text-gray-400 hover:text-rose-600 hover:bg-rose-50 disabled:opacity-30 disabled:hover:text-gray-400 disabled:hover:bg-transparent transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                <!-- Tag Badge -->
                                <div class="sm:col-span-4">
                                    <label class="block text-[11px] font-semibold text-gray-600 mb-1">Badge Tag</label>
                                    <input type="text" 
                                           x-model="pk.tag"
                                           class="w-full text-xs rounded-modern border border-gray-300 px-2.5 py-1.5 focus:ring-1 focus:ring-brand-primary font-bold text-brand-dark"
                                           placeholder="Contoh: Sertifikasi">
                                </div>

                                <!-- Title -->
                                <div class="sm:col-span-8">
                                    <label class="block text-[11px] font-semibold text-gray-600 mb-1">Judul Pilar</label>
                                    <input type="text" 
                                           x-model="pk.name"
                                           class="w-full text-xs rounded-modern border border-gray-300 px-2.5 py-1.5 focus:ring-1 focus:ring-brand-primary font-bold text-brand-dark"
                                           placeholder="Nama pilar standar mutu...">
                                </div>

                                <!-- Description -->
                                <div class="sm:col-span-12">
                                    <label class="block text-[11px] font-semibold text-gray-600 mb-1">Deskripsi Pilar Mutu</label>
                                    <textarea x-model="pk.desc" 
                                              rows="2"
                                              class="w-full text-xs rounded-modern border border-gray-300 px-2.5 py-1.5 focus:ring-1 focus:ring-brand-primary text-gray-700 leading-relaxed"
                                              placeholder="Uraian jaminan mutu produk..."></textarea>
                                </div>

                                <!-- Checklist Points (Array of strings) -->
                                <div class="sm:col-span-12 space-y-1.5">
                                    <label class="block text-[11px] font-semibold text-gray-600">Poin Verifikasi Checklist</label>
                                    <div class="space-y-1.5">
                                        <template x-for="(point, pIdx) in pk.features" :key="pIdx">
                                            <div class="flex items-center gap-2">
                                                <span class="text-emerald-500 font-bold text-xs">✓</span>
                                                <input type="text" 
                                                       x-model="pk.features[pIdx]"
                                                       class="w-full text-xs rounded-modern border border-gray-300 px-2 py-1 focus:ring-1 focus:ring-brand-primary text-gray-800"
                                                       placeholder="Poin checklist mutu...">
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>

        <!-- Section Bottom Action Bar -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-white rounded-modern-xl border border-gray-200/80 p-4 sm:p-5 shadow-2xs">
            <div class="text-xs text-gray-500 text-center sm:text-left">
                💡 Setiap perubahan data, penambahan, penghapusan, atau pengosongan card langsung tercermin secara realtime pada <strong>Real Landing Page Preview</strong> di bawah.
            </div>
            <button @click="saveSection()" 
                    type="button"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-modern font-bold text-xs sm:text-sm text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer shrink-0">
                <span>Simpan Perubahan</span>
            </button>
        </div>

    </div>

    <!-- =============================================================== -->
    <!-- 4. REAL LANDING PAGE PREVIEW SECTION (FULL WIDTH AT THE BOTTOM) -->
    <!-- =============================================================== -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-8 shadow-2xs space-y-5">
        
        <!-- Header Preview Bar & Device Switcher -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="text-lg">👁️</span>
                    <h3 class="text-base sm:text-lg font-extrabold text-brand-dark tracking-tight uppercase">
                        REAL LANDING PAGE PREVIEW
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-brand-soft-green text-brand-primary border border-brand-soft-green-border">
                        1:1 LIVE RENDER
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
                    <span>💻 Desktop</span>
                </button>
                <button @click="previewDevice = 'tablet'" 
                        type="button" 
                        :class="previewDevice === 'tablet' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500 hover:text-brand-dark'"
                        class="px-3 py-1.5 rounded transition-all cursor-pointer flex items-center gap-1.5 text-xs">
                    <span>📱 Tablet</span>
                </button>
                <button @click="previewDevice = 'mobile'" 
                        type="button" 
                        :class="previewDevice === 'mobile' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500 hover:text-brand-dark'"
                        class="px-3 py-1.5 rounded transition-all cursor-pointer flex items-center gap-1.5 text-xs">
                    <span>📱 Mobile</span>
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
                <div class="laptop-desktop-viewport absolute top-0 left-0 bg-brand-cream overflow-y-auto overflow-x-hidden text-left"
                     :style="{
                         width: '1366px',
                         height: '768px',
                         transformOrigin: '0 0',
                         transform: 'scale(' + currentScale + ')'
                     }"
                     style="scroll-behavior: smooth;">
                    
                    <!-- Desktop Viewport: Tab A (Kenapa Memilih Kami) -->
                    <div x-show="activeTab === 'benefits'" class="w-[1366px] min-h-[768px] h-auto bg-brand-cream/80">
                        <section class="py-14 px-12 bg-brand-cream/80 w-full min-h-[768px] text-left flex flex-col justify-center">
                            <div class="max-w-7xl mx-auto w-full">
                                <div class="text-center max-w-2xl mx-auto mb-10">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-3 shadow-2xs"
                                          x-text="benefits.section_badge || 'Kenapa Memilih Kami'">
                                    </span>
                                    <h2 class="text-3xl font-extrabold text-brand-dark tracking-tight mb-3"
                                        x-text="benefits.section_title || 'Lebih Praktis, Lebih Siap'">
                                    </h2>
                                    <p class="text-sm text-gray-600 font-normal leading-relaxed"
                                       x-text="benefits.section_subtitle || 'Komitmen kami menghadirkan bahan makanan segar dan frozen bermutu tinggi...'">
                                    </p>
                                </div>

                                <!-- Dynamic Reflow Equal-Height Benefit Cards Desktop Grid -->
                                <div class="grid gap-6 items-stretch"
                                     :class="{
                                         'grid-cols-4': validBenefitsItems.length >= 4,
                                         'grid-cols-3 max-w-5xl mx-auto': validBenefitsItems.length === 3,
                                         'grid-cols-2 max-w-3xl mx-auto': validBenefitsItems.length === 2,
                                         'grid-cols-1 max-w-md mx-auto': validBenefitsItems.length === 1
                                     }">
                                    <template x-for="item in validBenefitsItems" :key="item.id">
                                        <div class="h-full flex flex-col [&>div]:h-full [&>div]:flex-1">
                                            @include('components.benefit-card-item', ['isLivePreview' => true])
                                        </div>
                                    </template>
                                </div>

                                <!-- Fallback if 0 valid items -->
                                <template x-if="validBenefitsItems.length === 0">
                                    <div class="text-center py-12 px-4 bg-white/60 rounded-modern-xl border border-dashed border-gray-300 max-w-md mx-auto">
                                        <p class="text-xs text-gray-500 font-medium">Belum ada poin keunggulan dengan isi konten. Silakan isi form editor di atas.</p>
                                    </div>
                                </template>
                            </div>
                        </section>
                    </div>

                    <!-- Desktop Viewport: Tab B (Standar Mutu) -->
                    <div x-show="activeTab === 'quality'" class="w-[1366px] min-h-[768px] h-auto bg-brand-cream/60">
                        <section class="py-14 px-12 bg-brand-cream/60 w-full min-h-[768px] text-left flex flex-col justify-center">
                            <div class="max-w-7xl mx-auto w-full">
                                <div class="text-center max-w-3xl mx-auto mb-10">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-3 shadow-2xs"
                                          x-text="quality.section_badge || 'Standar Mutu'">
                                    </span>
                                    <h2 class="text-3xl font-extrabold text-brand-dark tracking-tight mb-3"
                                        x-text="quality.section_title || 'Mengenal Standar Produk Kami'">
                                    </h2>
                                    <p class="text-sm text-gray-600 font-normal leading-relaxed"
                                       x-text="quality.section_subtitle || 'Setiap produk yang keluar dari fasilitas penyimpanan...'">
                                    </p>
                                </div>

                                <!-- Dynamic Reflow Equal-Height Quality Pillars Desktop Grid -->
                                <div class="grid gap-6 items-stretch"
                                     :class="{
                                         'grid-cols-2': validQualityItems.length >= 2,
                                         'grid-cols-1 max-w-xl mx-auto': validQualityItems.length === 1
                                     }">
                                    <template x-for="pk in validQualityItems" :key="pk.id">
                                        <div class="h-full flex flex-col [&>div]:h-full [&>div]:flex-1">
                                            @include('components.quality-card-item', ['isLivePreview' => true])
                                        </div>
                                    </template>
                                </div>

                                <!-- Fallback if 0 valid items -->
                                <template x-if="validQualityItems.length === 0">
                                    <div class="text-center py-12 px-4 bg-white/60 rounded-modern-xl border border-dashed border-gray-300 max-w-md mx-auto">
                                        <p class="text-xs text-gray-500 font-medium">Belum ada pilar standar mutu dengan isi konten. Silakan isi form editor di atas.</p>
                                    </div>
                                </template>
                            </div>
                        </section>
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
                <div class="tablet-viewport absolute top-0 left-0 bg-brand-cream overflow-y-auto overflow-x-hidden text-left"
                     :style="{
                         width: '1024px',
                         height: '768px',
                         transformOrigin: '0 0',
                         transform: 'scale(' + currentScale + ')'
                     }"
                     style="scroll-behavior: smooth;">
                    
                    <!-- Tablet Viewport: Tab A (Kenapa Memilih Kami) -->
                    <div x-show="activeTab === 'benefits'" class="w-[1024px] min-h-[768px] h-auto bg-brand-cream/80">
                        <section class="py-12 px-8 bg-brand-cream/80 w-full min-h-[768px] text-left flex flex-col justify-center">
                            <div class="max-w-4xl mx-auto w-full">
                                <div class="text-center max-w-2xl mx-auto mb-8">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-2.5 shadow-2xs"
                                          x-text="benefits.section_badge || 'Kenapa Memilih Kami'">
                                    </span>
                                    <h2 class="text-2xl font-extrabold text-brand-dark tracking-tight mb-2.5"
                                        x-text="benefits.section_title || 'Lebih Praktis, Lebih Siap'">
                                    </h2>
                                    <p class="text-xs text-gray-600 font-normal leading-relaxed"
                                       x-text="benefits.section_subtitle || 'Komitmen kami menghadirkan bahan makanan segar dan frozen bermutu tinggi...'">
                                    </p>
                                </div>

                                <div class="grid gap-5 items-stretch"
                                     :class="{
                                         'grid-cols-2': validBenefitsItems.length >= 2,
                                         'grid-cols-1 max-w-md mx-auto': validBenefitsItems.length === 1
                                     }">
                                    <template x-for="item in validBenefitsItems" :key="item.id">
                                        <div class="h-full flex flex-col [&>div]:h-full [&>div]:flex-1">
                                            @include('components.benefit-card-item', ['isLivePreview' => true])
                                        </div>
                                    </template>
                                </div>

                                <template x-if="validBenefitsItems.length === 0">
                                    <div class="text-center py-12 px-4 bg-white/60 rounded-modern-xl border border-dashed border-gray-300 max-w-md mx-auto">
                                        <p class="text-xs text-gray-500 font-medium">Belum ada poin keunggulan dengan isi konten.</p>
                                    </div>
                                </template>
                            </div>
                        </section>
                    </div>

                    <!-- Tablet Viewport: Tab B (Standar Mutu) -->
                    <div x-show="activeTab === 'quality'" class="w-[1024px] min-h-[768px] h-auto bg-brand-cream/60">
                        <section class="py-12 px-8 bg-brand-cream/60 w-full min-h-[768px] text-left flex flex-col justify-center">
                            <div class="max-w-4xl mx-auto w-full">
                                <div class="text-center max-w-2xl mx-auto mb-8">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-2.5 shadow-2xs"
                                          x-text="quality.section_badge || 'Standar Mutu'">
                                    </span>
                                    <h2 class="text-2xl font-extrabold text-brand-dark tracking-tight mb-2.5"
                                        x-text="quality.section_title || 'Mengenal Standar Produk Kami'">
                                    </h2>
                                    <p class="text-xs text-gray-600 font-normal leading-relaxed"
                                        x-text="quality.section_subtitle || 'Setiap produk yang keluar dari fasilitas penyimpanan...'">
                                    </p>
                                </div>

                                <div class="grid gap-5 items-stretch"
                                     :class="{
                                         'grid-cols-2': validQualityItems.length >= 2,
                                         'grid-cols-1 max-w-xl mx-auto': validQualityItems.length === 1
                                     }">
                                    <template x-for="pk in validQualityItems" :key="pk.id">
                                        <div class="h-full flex flex-col [&>div]:h-full [&>div]:flex-1">
                                            @include('components.quality-card-item', ['isLivePreview' => true])
                                        </div>
                                    </template>
                                </div>

                                <template x-if="validQualityItems.length === 0">
                                    <div class="text-center py-12 px-4 bg-white/60 rounded-modern-xl border border-dashed border-gray-300 max-w-md mx-auto">
                                        <p class="text-xs text-gray-500 font-medium">Belum ada pilar standar mutu dengan isi konten.</p>
                                    </div>
                                </template>
                            </div>
                        </section>
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
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>

                    <!-- Mobile Scrollable Screen (Viewport 393×852) -->
                    <div class="mobile-viewport w-full h-full overflow-y-auto overflow-x-hidden text-left bg-brand-cream"
                         style="scroll-behavior: smooth;">
                        
                        <!-- Tab A: Kenapa Memilih Kami (Mobile Natural 393px Stack) -->
                        <div x-show="activeTab === 'benefits'" class="bg-brand-cream/80 min-h-full py-10 px-4 space-y-6">
                            
                            <!-- Mobile Section Header -->
                            <div class="text-center max-w-xs mx-auto pt-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-2.5 shadow-2xs"
                                      x-text="benefits.section_badge || 'Kenapa Memilih Kami'">
                                </span>
                                <h2 class="text-2xl font-extrabold text-brand-dark tracking-tight mb-2 leading-tight"
                                    x-text="benefits.section_title || 'Lebih Praktis, Lebih Siap'">
                                </h2>
                                <p class="text-xs text-gray-600 font-normal leading-relaxed"
                                   x-text="benefits.section_subtitle || 'Komitmen kami menghadirkan bahan makanan segar dan frozen...'"></p>
                            </div>

                            <!-- Benefit Cards (Only Valid Non-Empty Items) -->
                            <div class="grid grid-cols-1 gap-4 pb-4">
                                <template x-for="item in validBenefitsItems" :key="item.id">
                                    <div>
                                        @include('components.benefit-card-item', ['isLivePreview' => true])
                                    </div>
                                </template>
                            </div>

                            <template x-if="validBenefitsItems.length === 0">
                                <div class="text-center py-8 px-3 bg-white/60 rounded-modern-xl border border-dashed border-gray-300">
                                    <p class="text-xs text-gray-500 font-medium">Belum ada poin keunggulan dengan isi konten.</p>
                                </div>
                            </template>

                            <!-- Bottom Indicator -->
                            <div class="pt-2 text-center" x-show="validBenefitsItems.length > 0">
                                <span class="inline-flex items-center gap-1 text-[10px] text-gray-400 font-medium">
                                    <span>↕</span>
                                    <span>Scroll untuk melihat seluruh poin</span>
                                </span>
                            </div>

                        </div>

                        <!-- Tab B: Standar Mutu (Mobile Natural 393px Stack) -->
                        <div x-show="activeTab === 'quality'" class="bg-brand-cream/60 min-h-full py-10 px-4 space-y-6">
                            
                            <!-- Mobile Section Header -->
                            <div class="text-center max-w-xs mx-auto pt-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-2.5 shadow-2xs"
                                      x-text="quality.section_badge || 'Standar Mutu'">
                                </span>
                                <h2 class="text-2xl font-extrabold text-brand-dark tracking-tight mb-2 leading-tight"
                                    x-text="quality.section_title || 'Mengenal Standar Produk Kami'">
                                </h2>
                                <p class="text-xs text-gray-600 font-normal leading-relaxed"
                                   x-text="quality.section_subtitle || 'Setiap produk melewati proses seleksi ketat...'"></p>
                            </div>

                            <!-- Quality Pillars (Only Valid Non-Empty Items) -->
                            <div class="grid grid-cols-1 gap-4 pb-4">
                                <template x-for="pk in validQualityItems" :key="pk.id">
                                    <div>
                                        @include('components.quality-card-item', ['isLivePreview' => true])
                                    </div>
                                </template>
                            </div>

                            <template x-if="validQualityItems.length === 0">
                                <div class="text-center py-8 px-3 bg-white/60 rounded-modern-xl border border-dashed border-gray-300">
                                    <p class="text-xs text-gray-500 font-medium">Belum ada pilar standar mutu dengan isi konten.</p>
                                </div>
                            </template>

                            <!-- Bottom Indicator -->
                            <div class="pt-2 text-center" x-show="validQualityItems.length > 0">
                                <span class="inline-flex items-center gap-1 text-[10px] text-gray-400 font-medium">
                                    <span>↕</span>
                                    <span>Scroll untuk melihat seluruh pilar mutu</span>
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
    <!-- DELETE CONFIRMATION MODAL (HERO SLIDER STANDARD)        -->
    <!-- ======================================================= -->
    <div x-show="deleteModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        
        <div class="fixed inset-0 bg-black/50 backdrop-blur-xs" @click="deleteModalOpen = false"></div>

        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white rounded-modern-xl max-w-sm w-full p-6 shadow-xl border border-gray-200 text-center space-y-4">
                
                <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>

                <div class="space-y-1">
                    <h3 class="text-base font-bold text-brand-dark" 
                        x-text="deleteTargetType === 'benefit' ? 'Hapus Poin Keunggulan ini?' : 'Hapus Pilar Standar Mutu ini?'">
                    </h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Item <strong class="text-brand-dark" x-text="selectedItemName"></strong> akan dihapus dari daftar.
                    </p>
                </div>

                <div class="pt-3 flex items-center justify-center gap-3">
                    <button @click="deleteModalOpen = false" 
                            type="button" 
                            class="px-4 py-2 rounded-modern text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button @click="confirmDelete()" 
                            type="button" 
                            class="px-4 py-2 rounded-modern text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 transition-colors cursor-pointer">
                        Hapus
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
