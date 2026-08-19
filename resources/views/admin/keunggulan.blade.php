@extends('layouts.admin', [
    'title' => 'Keunggulan & Standar Mutu',
    'pageTitle' => 'Keunggulan & Standar Mutu'
])

@section('content')
<div class="space-y-6"
     x-data="{
         benefits: {{ json_encode($benefitsData) }},
         quality: {{ json_encode($qualityStandardsData) }},
         activeTab: 'benefits', // 'benefits' | 'quality'
         
         // Preview Device & Virtual Viewport State
         previewDevice: 'desktop', // 'desktop' | 'mobile'
         previewBoxWidth: 540,
         previewObserver: null,
         toastMessage: '',
         toastVisible: false,
         
         iconOptions: [
             { id: 'grid', label: 'Grid / Kotak (Pilihan Lengkap)' },
             { id: 'shield', label: 'Shield / Perisai (Higienis & Cold Chain)' },
             { id: 'clock', label: 'Clock / Jam (Ready to Cook Praktis)' },
             { id: 'truck', label: 'Truck / Truk (Pengiriman & Curah)' },
         ],
         
         // Reference Viewport Dimensions (Laptop 14-inch 1366x768 & iPhone 15 393x852)
         virtualDimensions: {
             benefits: {
                 desktop: { width: 1366, height: 768 },
                 mobile:  { width: 393,  height: 852 }
             },
             quality: {
                 desktop: { width: 1366, height: 768 },
                 mobile:  { width: 393,  height: 852 }
             }
         },
         
         get currentVirtualWidth() {
             return this.previewDevice === 'mobile' ? 393 : 1366;
         },
         
         get currentVirtualHeight() {
             return this.previewDevice === 'mobile' ? 852 : 768;
         },
         
         get currentFrameWidth() {
             const available = Math.max(260, this.previewBoxWidth || 540);
             if (this.previewDevice === 'desktop') {
                 return available;
             } else { // mobile iPhone 15
                 return Math.min(available, 330);
             }
         },
         
         get currentFrameHeight() {
             return Math.round(this.currentFrameWidth * (this.currentVirtualHeight / this.currentVirtualWidth));
         },
         
         get currentScale() {
             return this.currentFrameWidth / this.currentVirtualWidth;
         },
         
         initPreviewObserver() {
             this.$nextTick(() => {
                 if (this.$refs.previewBoxWrapper) {
                     const rect = this.$refs.previewBoxWrapper.getBoundingClientRect();
                     if (rect.width > 50) {
                         this.previewBoxWidth = rect.width;
                     }
                     if (!this.previewObserver && window.ResizeObserver) {
                         this.previewObserver = new ResizeObserver((entries) => {
                             for (let entry of entries) {
                                 const width = entry.contentRect.width;
                                 if (width > 50) {
                                     this.previewBoxWidth = width;
                                 }
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
        .laptop-desktop-viewport::-webkit-scrollbar {
            width: 6px;
        }
        .laptop-desktop-viewport::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.25);
            border-radius: 9999px;
        }
        .iphone-viewport::-webkit-scrollbar {
            width: 4px;
        }
        .iphone-viewport::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.25);
            border-radius: 9999px;
        }
    </style>
    
    <!-- 1. Header Card -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-8 shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-brand-dark tracking-tight">
                        Keunggulan &amp; Standar Mutu
                    </h2>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                        <span>REAL PREVIEW &bull; DESKTOP &amp; MOBILE</span>
                    </span>
                    <span class="text-xs text-gray-500 font-medium">
                        • Desktop (1366&times;768) &amp; Mobile (393&times;852 Scrollable)
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 leading-relaxed max-w-3xl">
                    Kelola narasi nilai keunggulan, 4 poin kepraktisan belanja, dan 4 pilar standar jaminan mutu produk dengan <strong>Real Landing Page Previews</strong>.
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

    <!-- 2. Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-gray-200 pb-2">
        <button @click="activeTab = 'benefits'; initPreviewObserver()" 
                type="button" 
                :class="activeTab === 'benefits' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer flex items-center gap-1.5">
            <span>⭐</span>
            <span>A. Kenapa Memilih Kami (4 Poin)</span>
        </button>
        <button @click="activeTab = 'quality'; initPreviewObserver()" 
                type="button" 
                :class="activeTab === 'quality' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer flex items-center gap-1.5">
            <span>🛡️</span>
            <span>B. 4 Pilar Standar Mutu</span>
        </button>
    </div>

    <!-- 3. Main Workspace: Two Columns (7 cols editor, 5 cols preview) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left: Accordion Editor Form (7 cols on lg) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- TAB A: KENAPA MEMILIH KAMI (4 Poin) -->
            <div x-show="activeTab === 'benefits'" class="space-y-4">
                
                <!-- Section Header Config Card -->
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-6 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-primary"></span>
                            <h3 class="text-sm font-extrabold text-brand-dark">Header Section: Kenapa Memilih Kami</h3>
                        </div>
                        <span class="text-[11px] text-gray-400 font-mono">Dynamic Section Header</span>
                    </div>

                    <div class="grid grid-cols-1 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Badge Tag</label>
                            <input type="text" 
                                   x-model="benefits.section_badge" 
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary font-medium text-brand-dark"
                                   placeholder="Contoh: Kenapa Memilih Kami">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Judul Section (H2)</label>
                            <input type="text" 
                                   x-model="benefits.section_title" 
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary font-extrabold text-brand-dark"
                                   placeholder="Contoh: Lebih Praktis, Lebih Siap">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Subjudul / Deskripsi Singkat</label>
                            <textarea x-model="benefits.section_subtitle" 
                                      rows="2"
                                      class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary text-gray-700 leading-relaxed"
                                      placeholder="Penjelasan ringkas komitmen mutu..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- 4 Poin Keunggulan Cards Editor (Fixed 4 Items) -->
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-6 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h3 class="text-sm font-extrabold text-brand-dark">4 Kartu Poin Keunggulan</h3>
                        </div>
                        <span class="text-[11px] text-gray-400 font-mono">4 Fixed Cards</span>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(item, idx) in benefits.items" :key="item.id">
                            <div class="border border-gray-200 rounded-modern-lg p-4 bg-gray-50/50 hover:bg-white transition-all duration-200 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-brand-primary/10 text-brand-primary text-[11px] font-extrabold flex items-center justify-center" x-text="idx + 1"></span>
                                        <span class="text-xs font-bold text-brand-dark" x-text="item.title || ('Keunggulan ' + (idx + 1))"></span>
                                    </div>
                                    <span class="text-[11px] text-gray-400 font-mono" x-text="'ID: ' + item.id"></span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                    <!-- Title -->
                                    <div class="sm:col-span-8">
                                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Judul Poin</label>
                                        <input type="text" 
                                               x-model="item.title"
                                               class="w-full text-xs rounded-modern border border-gray-300 px-2.5 py-1.5 focus:ring-1 focus:ring-brand-primary font-bold text-brand-dark">
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
                                                  class="w-full text-xs rounded-modern border border-gray-300 px-2.5 py-1.5 focus:ring-1 focus:ring-brand-primary text-gray-700 leading-relaxed"></textarea>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <!-- TAB B: 4 PILAR STANDAR MUTU -->
            <div x-show="activeTab === 'quality'" class="space-y-4">
                
                <!-- Section Header Config Card -->
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-6 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-primary"></span>
                            <h3 class="text-sm font-extrabold text-brand-dark">Header Section: 4 Pilar Standar Mutu</h3>
                        </div>
                        <span class="text-[11px] text-gray-400 font-mono">Dynamic Section Header</span>
                    </div>

                    <div class="grid grid-cols-1 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Badge Tag</label>
                            <input type="text" 
                                   x-model="quality.section_badge" 
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary font-medium text-brand-dark"
                                   placeholder="Contoh: Standar Mutu">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Judul Section (H2)</label>
                            <input type="text" 
                                   x-model="quality.section_title" 
                                   class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary font-extrabold text-brand-dark"
                                   placeholder="Contoh: Mengenal Standar Produk Kami">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Subjudul / Deskripsi Singkat</label>
                            <textarea x-model="quality.section_subtitle" 
                                      rows="2"
                                      class="w-full text-xs rounded-modern border border-gray-300 px-3 py-2 focus:ring-1 focus:ring-brand-primary focus:border-brand-primary text-gray-700 leading-relaxed"
                                      placeholder="Penjelasan ringkas standar jaminan kualitas..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- 4 Pilar Standar Mutu Cards Editor -->
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 sm:p-6 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h3 class="text-sm font-extrabold text-brand-dark">4 Pilar Standar Jaminan Mutu</h3>
                        </div>
                        <span class="text-[11px] text-gray-400 font-mono">4 Fixed Pillars</span>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(pk, idx) in quality.items" :key="pk.id">
                            <div class="border border-gray-200 rounded-modern-lg p-4 bg-gray-50/50 hover:bg-white transition-all duration-200 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-brand-primary/10 text-brand-primary text-[11px] font-extrabold flex items-center justify-center" x-text="idx + 1"></span>
                                        <span class="text-xs font-bold text-brand-dark" x-text="pk.name || ('Pilar ' + (idx + 1))"></span>
                                    </div>
                                    <span class="text-[11px] text-gray-400 font-mono" x-text="'Tag: ' + pk.tag"></span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                    <!-- Tag Badge -->
                                    <div class="sm:col-span-4">
                                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Badge Tag</label>
                                        <input type="text" 
                                               x-model="pk.tag"
                                               class="w-full text-xs rounded-modern border border-gray-300 px-2.5 py-1.5 focus:ring-1 focus:ring-brand-primary font-bold text-brand-dark">
                                    </div>

                                    <!-- Title -->
                                    <div class="sm:col-span-8">
                                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Judul Pilar</label>
                                        <input type="text" 
                                               x-model="pk.name"
                                               class="w-full text-xs rounded-modern border border-gray-300 px-2.5 py-1.5 focus:ring-1 focus:ring-brand-primary font-bold text-brand-dark">
                                    </div>

                                    <!-- Description -->
                                    <div class="sm:col-span-12">
                                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Deskripsi Pilar Mutu</label>
                                        <textarea x-model="pk.desc" 
                                                  rows="2"
                                                  class="w-full text-xs rounded-modern border border-gray-300 px-2.5 py-1.5 focus:ring-1 focus:ring-brand-primary text-gray-700 leading-relaxed"></textarea>
                                    </div>

                                    <!-- Checklist Points (Array of strings) -->
                                    <div class="sm:col-span-12 space-y-1.5">
                                        <label class="block text-[11px] font-semibold text-gray-600">3 Poin Verifikasi Checklist</label>
                                        <div class="space-y-1.5">
                                            <template x-for="(point, pIdx) in pk.features" :key="pIdx">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-emerald-500 font-bold text-xs">✓</span>
                                                    <input type="text" 
                                                           x-model="pk.features[pIdx]"
                                                           class="w-full text-xs rounded-modern border border-gray-300 px-2 py-1 focus:ring-1 focus:ring-brand-primary text-gray-800">
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

        </div>

        <!-- ======================================================= -->
        <!-- Right: VIRTUAL VIEWPORT 1:1 REAL PREVIEW (5 cols on lg) -->
        <!-- ======================================================= -->
        <div class="lg:col-span-5 space-y-3 sticky top-4">
            
            <!-- Device Toggle Bar & Scale Indicator -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1">
                <div>
                    <label class="block text-xs font-extrabold text-brand-dark uppercase tracking-wider">
                        Real Landing Page Preview
                    </label>
                    <p class="text-[11px] text-gray-500 font-mono">
                        <span x-show="previewDevice === 'desktop'">💻 Desktop (1366&times;768) &bull; Scale <span x-text="Math.round(currentScale * 100)"></span>% &bull; Scrollable</span>
                        <span x-show="previewDevice === 'mobile'">📱 Mobile (393&times;852) &bull; Scale <span x-text="Math.round(currentScale * 100)"></span>% &bull; Scrollable</span>
                    </p>
                </div>

                <!-- Device Simulator Switch -->
                <div class="flex items-center bg-gray-100 p-0.5 rounded-modern border border-gray-200 text-xs shrink-0 self-start sm:self-auto">
                    <button @click="previewDevice = 'desktop'; initPreviewObserver()" 
                            type="button" 
                            :class="previewDevice === 'desktop' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500 hover:text-brand-dark'"
                            class="px-2.5 py-1 rounded transition-all cursor-pointer flex items-center gap-1 text-[11px]">
                        <span>💻 Desktop</span>
                    </button>
                    <button @click="previewDevice = 'mobile'; initPreviewObserver()" 
                            type="button" 
                            :class="previewDevice === 'mobile' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500 hover:text-brand-dark'"
                            class="px-2.5 py-1 rounded transition-all cursor-pointer flex items-center gap-1 text-[11px]">
                        <span>📱 Mobile</span>
                    </button>
                </div>
            </div>

            <!-- Preview Frame Wrapper (Observes width & centers frame) -->
            <div x-ref="previewBoxWrapper"
                 class="bg-gray-950 rounded-modern-xl p-3 sm:p-4 flex justify-center items-start overflow-hidden border border-gray-800 shadow-inner min-h-[360px]">
                
                <!-- =================================================== -->
                <!-- A. DESKTOP VIEWPORT PREVIEW (Laptop 14" 1366x768)  -->
                <!-- =================================================== -->
                <template x-if="previewDevice === 'desktop'">
                    <div class="relative overflow-hidden rounded-modern-lg shadow-2xl transition-all duration-300 mx-auto select-none bg-gray-900"
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

                                        <div class="grid grid-cols-4 gap-6">
                                            <template x-for="item in benefits.items" :key="item.id">
                                                <div>
                                                    @include('components.benefit-card-item', ['isLivePreview' => true])
                                                </div>
                                            </template>
                                        </div>
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

                                        <div class="grid grid-cols-2 gap-6">
                                            <template x-for="pk in quality.items" :key="pk.id">
                                                <div>
                                                    @include('components.quality-card-item', ['isLivePreview' => true])
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </section>
                            </div>

                        </div>

                    </div>
                </template>

                <!-- =================================================== -->
                <!-- B. iPHONE 15 DEVICE SIMULATOR (393×852 SCROLLABLE) -->
                <!-- =================================================== -->
                <template x-if="previewDevice === 'mobile'">
                    <div class="relative overflow-hidden transition-all duration-300 mx-auto select-none"
                         :style="{
                             width: currentFrameWidth + 'px',
                             height: currentFrameHeight + 'px'
                         }">
                        
                        <!-- iPhone 15 Outer Scaled Shell (393px × 852px scaled) -->
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

                            <!-- Mobile Scrollable Screen (iPhone 15 Viewport 393×852) -->
                            <div class="iphone-viewport w-full h-full overflow-y-auto overflow-x-hidden text-left bg-brand-cream"
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

                                    <!-- 4 Benefit Cards (Natural Mobile Stack - Scroll to View All) -->
                                    <div class="grid grid-cols-1 gap-4 pb-4">
                                        <template x-for="item in benefits.items" :key="item.id">
                                            <div>
                                                @include('components.benefit-card-item', ['isLivePreview' => true])
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Bottom Indicator -->
                                    <div class="pt-2 text-center">
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

                                    <!-- 4 Quality Pillars (Natural Mobile Stack - Scroll to View All) -->
                                    <div class="grid grid-cols-1 gap-4 pb-4">
                                        <template x-for="pk in quality.items" :key="pk.id">
                                            <div>
                                                @include('components.quality-card-item', ['isLivePreview' => true])
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Bottom Indicator -->
                                    <div class="pt-2 text-center">
                                        <span class="inline-flex items-center gap-1 text-[10px] text-gray-400 font-medium">
                                            <span>↕</span>
                                            <span>Scroll untuk melihat seluruh pilar mutu</span>
                                        </span>
                                    </div>

                                </div>

                            </div>

                            <!-- iPhone 15 Bottom Home Bar -->
                            <div class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-32 h-1 bg-black/40 rounded-full z-30 pointer-events-none"></div>

                        </div>

                    </div>
                </template>

            </div>

            <p class="text-[11px] text-gray-400 text-center">
                <span x-show="previewDevice === 'desktop'">💻 Virtual Desktop 14" (1366&times;768, 16:9) &bull; Scrollable jika konten melebihi tinggi layar.</span>
                <span x-show="previewDevice === 'mobile'">📱 Virtual Mobile (393&times;852, 9:16) &bull; Arahkan kursor &amp; scroll untuk melihat seluruh kartu.</span>
            </p>

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
