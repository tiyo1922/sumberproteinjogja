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
         previewDevice: 'desktop', // 'desktop' | 'mobile'
         toastMessage: '',
         toastVisible: false,
         
         showToast(msg) {
             this.toastMessage = msg;
             this.toastVisible = true;
             setTimeout(() => { this.toastVisible = false; }, 3000);
         },
         
         saveSection() {
             this.showToast('Pengaturan Keunggulan & Standar Mutu berhasil disimpan!');
         }
     }">
    
    <!-- 1. Header Card -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-8 shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-brand-dark tracking-tight">
                        Keunggulan & Standar Mutu
                    </h2>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                        <span>FIXED SECTION STRUCTURE</span>
                    </span>
                    <span class="text-xs text-gray-500 font-medium">
                        • Section 'Kenapa Memilih Kami' & 'Standar Mutu' Landing Page
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
        <button @click="activeTab = 'benefits'" 
                type="button"
                :class="activeTab === 'benefits' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer">
            ⭐ A. Kenapa Memilih Kami (4 Poin)
        </button>
        <button @click="activeTab = 'quality'" 
                type="button"
                :class="activeTab === 'quality' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer">
            🛡️ B. Standar Mutu Kami (4 Pilar)
        </button>
    </div>

    <!-- 3. Tab Contents Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Form Fields (7 cols on lg) -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Tab A: Kenapa Memilih Kami -->
            <div x-show="activeTab === 'benefits'" class="space-y-5">
                
                <!-- Section Header Config -->
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-4">
                    <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider border-b border-gray-100 pb-2">
                        Header Section 'Kenapa Memilih Kami'
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Badge Tag</label>
                            <input type="text" x-model="benefits.section_badge" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Judul Utama (Headline)</label>
                            <input type="text" x-model="benefits.section_title" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-extrabold text-brand-dark">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Deskripsi Pengantar</label>
                        <textarea x-model="benefits.section_subtitle" rows="2" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white"></textarea>
                    </div>
                </div>

                <!-- 4 Fixed Benefit Items -->
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-4">
                    <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider border-b border-gray-100 pb-2">
                        4 Poin Keunggulan (Layout Locked 4-Grid)
                    </h3>

                    <div class="space-y-4">
                        <template x-for="(item, idx) in benefits.items" :key="item.id">
                            <div class="p-4 rounded-modern border border-gray-200 bg-gray-50/60 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-black text-brand-primary" x-text="'Poin Keunggulan ' + (idx + 1)"></span>
                                    <span class="text-[10px] text-gray-400 font-mono" x-text="'Icon: ' + item.icon"></span>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Judul Poin</label>
                                    <input type="text" x-model="item.title" class="w-full text-xs rounded border border-gray-300 p-2 bg-white font-bold text-brand-dark">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Deskripsi Penjelasan</label>
                                    <textarea x-model="item.desc" rows="2" class="w-full text-xs rounded border border-gray-300 p-2 bg-white"></textarea>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <!-- Tab B: Standar Mutu -->
            <div x-show="activeTab === 'quality'" class="space-y-5">
                
                <!-- Section Header Config -->
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-4">
                    <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider border-b border-gray-100 pb-2">
                        Header Section 'Standar Mutu'
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Badge Tag</label>
                            <input type="text" x-model="quality.section_badge" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Judul Utama</label>
                            <input type="text" x-model="quality.section_title" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-extrabold text-brand-dark">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Deskripsi Pengantar</label>
                        <textarea x-model="quality.section_subtitle" rows="2" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white"></textarea>
                    </div>
                </div>

                <!-- 4 Quality Standard Items -->
                <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-4">
                    <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider border-b border-gray-100 pb-2">
                        4 Pilar Standar Mutu (Layout Locked 2x2 Grid)
                    </h3>

                    <div class="space-y-4">
                        <template x-for="(pk, idx) in quality.items" :key="pk.id">
                            <div class="p-4 rounded-modern border border-gray-200 bg-gray-50/60 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-black text-brand-primary" x-text="'Pilar ' + (idx + 1) + ': ' + pk.name"></span>
                                    <input type="text" x-model="pk.tag" placeholder="Tag Pill" class="text-[11px] font-bold text-brand-primary bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full w-36 text-center">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Nama Kategori Mutu</label>
                                        <input type="text" x-model="pk.name" class="w-full text-xs rounded border border-gray-300 p-2 bg-white font-bold">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Deskripsi Singkat</label>
                                        <textarea x-model="pk.desc" rows="2" class="w-full text-xs rounded border border-gray-300 p-2 bg-white"></textarea>
                                    </div>
                                </div>

                                <!-- Features Checklist (4 items) -->
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Poin Checklist Keunggulan:</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <template x-for="(feat, fIdx) in pk.features" :key="fIdx">
                                            <input type="text" x-model="pk.features[fIdx]" class="w-full text-xs rounded border border-gray-300 p-1.5 bg-white">
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

        </div>

        <!-- Right: REAL LANDING PAGE SECTION PREVIEW (5 cols on lg) -->
        <div class="lg:col-span-5 space-y-4 sticky top-4">
            
            <div class="flex items-center justify-between">
                <label class="block text-xs font-extrabold text-brand-dark uppercase tracking-wider">
                    Real Landing Page Preview
                </label>
                <div class="flex items-center bg-gray-100 p-0.5 rounded text-[10px]">
                    <button @click="previewDevice = 'desktop'" type="button" 
                            :class="previewDevice === 'desktop' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500'"
                            class="px-2 py-0.5 rounded cursor-pointer">💻 Desk</button>
                    <button @click="previewDevice = 'mobile'" type="button" 
                            :class="previewDevice === 'mobile' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500'"
                            class="px-2 py-0.5 rounded cursor-pointer">📱 Mob</button>
                </div>
            </div>

            <!-- PREVIEW TAB A: KENAPA MEMILIH KAMI -->
            <div x-show="activeTab === 'benefits'" 
                 class="bg-brand-cream/80 p-5 rounded-modern-xl border border-gray-200/80 shadow-md space-y-4 overflow-hidden"
                 :class="previewDevice === 'mobile' ? 'max-w-[280px] mx-auto text-center' : 'w-full'">
                
                <div class="text-center space-y-1">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-soft-green text-brand-primary"
                          x-text="benefits.section_badge"></span>
                    <h3 class="font-extrabold text-brand-dark text-base tracking-tight" x-text="benefits.section_title"></h3>
                    <p class="text-[11px] text-gray-600 line-clamp-2" x-text="benefits.section_subtitle"></p>
                </div>

                <div class="grid gap-3" :class="previewDevice === 'mobile' ? 'grid-cols-1' : 'grid-cols-2'">
                    <template x-for="item in benefits.items" :key="item.id">
                        <div class="bg-white p-3.5 rounded-modern-lg border border-gray-100 shadow-2xs text-left space-y-1">
                            <div class="w-7 h-7 rounded bg-brand-soft-green text-brand-primary flex items-center justify-center text-xs font-bold mb-1">
                                ✓
                            </div>
                            <h4 class="font-bold text-brand-dark text-xs" x-text="item.title"></h4>
                            <p class="text-[10px] text-gray-500 leading-relaxed line-clamp-2" x-text="item.desc"></p>
                        </div>
                    </template>
                </div>

            </div>

            <!-- PREVIEW TAB B: STANDAR MUTU -->
            <div x-show="activeTab === 'quality'" 
                 class="bg-brand-cream/70 p-5 rounded-modern-xl border border-gray-200/80 shadow-md space-y-4 overflow-hidden"
                 :class="previewDevice === 'mobile' ? 'max-w-[280px] mx-auto text-center' : 'w-full'">
                
                <div class="text-center space-y-1">
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-soft-green text-brand-primary"
                          x-text="quality.section_badge"></span>
                    <h3 class="font-extrabold text-brand-dark text-base tracking-tight" x-text="quality.section_title"></h3>
                    <p class="text-[11px] text-gray-600 line-clamp-2" x-text="quality.section_subtitle"></p>
                </div>

                <div class="space-y-3">
                    <template x-for="pk in quality.items" :key="pk.id">
                        <div class="bg-white p-3.5 rounded-modern-lg border border-gray-100 shadow-2xs text-left space-y-1.5">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-brand-dark text-xs" x-text="pk.name"></h4>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-brand-soft-green text-brand-primary" x-text="pk.tag"></span>
                            </div>
                            <p class="text-[10px] text-gray-500 leading-relaxed" x-text="pk.desc"></p>
                            <div class="grid grid-cols-2 gap-1 pt-1 border-t border-gray-100">
                                <template x-for="feat in pk.features" :key="feat">
                                    <div class="text-[9px] text-gray-700 flex items-center gap-1 truncate">
                                        <span class="text-brand-primary font-bold">✓</span>
                                        <span x-text="feat" class="truncate"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

            </div>

            <p class="text-[11px] text-gray-400 text-center">
                Preview di atas 100% sama dengan komponen section Keunggulan & Standar Mutu pada Landing Page.
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
