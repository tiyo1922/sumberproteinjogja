@extends('layouts.admin', [
    'title' => 'SEO & Meta',
    'pageTitle' => 'SEO & Meta Settings'
])

@section('content')
<div class="space-y-6"
     x-data="{
         seo: {{ json_encode($seoData) }},
         mediaLibrary: {{ json_encode($mediaLibrary) }},
         mediaPickerOpen: false,
         toastMessage: '',
         toastVisible: false,
         previewTab: 'google', // 'google' | 'social'
         googleDevice: 'desktop', // 'desktop' | 'mobile'
         mediaTab: 'library', // 'library' | 'upload'
         selectedMedia: null,
         uploadedFile: null,
         uploadedPreviewUrl: null,
         
         showToast(msg) {
             this.toastMessage = msg;
             this.toastVisible = true;
             setTimeout(() => { this.toastVisible = false; }, 3000);
         },
         
         openMediaPicker() {
             this.mediaTab = 'library';
             this.selectedMedia = this.mediaLibrary.find(m => m.path === this.seo.og_image) || this.mediaLibrary[0] || null;
             this.uploadedFile = null;
             this.uploadedPreviewUrl = null;
             this.mediaPickerOpen = true;
         },
         
         selectMedia(media) {
             this.selectedMedia = media;
         },
         
         confirmMediaSelection() {
             if (this.mediaTab === 'library' && this.selectedMedia) {
                 this.seo.og_image = this.selectedMedia.path;
                 this.mediaPickerOpen = false;
                 this.showToast('OG Image dipilih dari Media Library!');
             } else if (this.mediaTab === 'upload' && this.uploadedPreviewUrl) {
                 this.seo.og_image = this.uploadedPreviewUrl;
                 this.mediaPickerOpen = false;
                 this.showToast('OG Image hasil upload berhasil digunakan!');
             }
         },
         
         handleFileUpload(e) {
             const file = e.target.files ? e.target.files[0] : (e.dataTransfer ? e.dataTransfer.files[0] : null);
             if (!file) return;
             if (!['image/jpeg', 'image/png', 'image/webp', 'image/jpg'].includes(file.type)) {
                 alert('Format file tidak didukung. Gunakan JPG, PNG, atau WebP.');
                 return;
             }
             this.uploadedFile = {
                 name: file.name,
                 size: (file.size / 1024).toFixed(0) + ' KB',
                 type: file.type,
             };
             this.uploadedPreviewUrl = URL.createObjectURL(file);
         },
         
         saveSeo() {
             this.showToast('Mode demo: Pengaturan SEO siap diterapkan (In-Memory).');
         },
         
         getImageUrl(path) {
             if (!path) return '/images/hero-1.jpg';
             if (path.startsWith('blob:') || path.startsWith('http')) return path;
             return path.startsWith('/') ? path : '/' + path;
         }
     }">
    
    <!-- 1. Header Card -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-8 shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-brand-dark tracking-tight">
                        SEO & Meta Tags
                    </h2>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                        <span>FIXED SETTINGS</span>
                    </span>
                    <span class="text-xs text-gray-500 font-medium">
                        • Optimasi Mesin Pencari & Pratinjau Share Sosial
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 leading-relaxed max-w-3xl">
                    Kelola meta title, deskripsi pencarian Google, tag OpenGraph dengan <strong>Global Media Picker</strong>, dan pratinjau SERP / Sosial secara real-time.
                </p>
            </div>

            <!-- Save Action Button -->
            <div class="flex items-center gap-3 shrink-0">
                <button @click="saveSeo()" 
                        type="button"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-modern font-bold text-xs sm:text-sm text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer">
                    <span>Simpan Pengaturan SEO</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 2. Form & Live Previews Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Form Fields (7 cols on lg) -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Standard Meta Tags Card -->
            <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-4">
                <div class="border-b border-gray-100 pb-2">
                    <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                        1. Google Search Metadata
                    </h3>
                </div>

                <!-- Meta Title -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-brand-dark">
                            Meta Title Tag <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[11px] text-gray-400" x-text="(seo.meta_title?.length || 0) + ' / 60 Karakter Disarankan'"></span>
                    </div>
                    <input type="text" 
                           x-model="seo.meta_title" 
                           placeholder="Sumber Protein Jogja | Bahan Masak Siap Olah, Daging Segar & Frozen Food"
                           class="w-full text-xs sm:text-sm rounded-modern border border-gray-300 p-2.5 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary font-medium">
                </div>

                <!-- Meta Description -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-brand-dark">
                            Meta Description <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[11px] text-gray-400" x-text="(seo.meta_description?.length || 0) + ' / 160 Karakter Disarankan'"></span>
                    </div>
                    <textarea x-model="seo.meta_description" 
                              rows="3" 
                              placeholder="Penyedia bahan masakan siap olah, daging sapi slice, ayam segar..."
                              class="w-full text-xs sm:text-sm rounded-modern border border-gray-300 p-2.5 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary leading-relaxed"></textarea>
                </div>

                <!-- Canonical URL & Robots -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Canonical URL
                        </label>
                        <input type="text" 
                               x-model="seo.canonical_url" 
                               placeholder="https://sumberproteinjogja.com/"
                               class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Robots Indexing
                        </label>
                        <select x-model="seo.robots" 
                                class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-semibold">
                            <option value="index, follow">index, follow (Direkomendasikan)</option>
                            <option value="noindex, nofollow">noindex, nofollow (Sembunyikan)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-brand-dark mb-1">
                        Meta Keywords (Opsional)
                    </label>
                    <input type="text" 
                           x-model="seo.meta_keywords" 
                           placeholder="daging sapi jogja, frozen food sleman, ayam segar"
                           class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white">
                </div>
            </div>

            <!-- OpenGraph Social Sharing Card -->
            <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-4">
                <div class="border-b border-gray-100 pb-2">
                    <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                        2. OpenGraph Social Share Card (WhatsApp, Facebook, Twitter)
                    </h3>
                </div>

                <div>
                    <label class="block text-xs font-bold text-brand-dark mb-1">
                        OG Title
                    </label>
                    <input type="text" 
                           x-model="seo.og_title" 
                           placeholder="Sumber Protein Jogja — Fresh & Frozen Food"
                           class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-brand-dark mb-1">
                        OG Description
                    </label>
                    <textarea x-model="seo.og_description" 
                              rows="2" 
                              placeholder="Fresh & Frozen Food untuk kebutuhan sehari-hari..."
                              class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white"></textarea>
                </div>

                <!-- OG Image with Global Media Picker -->
                <div class="p-4 rounded-modern bg-gray-50 border border-gray-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-brand-dark">
                            OG Image (Gambar Share Link)
                        </label>
                        <span class="text-[11px] font-semibold text-emerald-700">Global Media Picker</span>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-28 aspect-[1.91/1] rounded-modern overflow-hidden bg-brand-dark shrink-0 border border-gray-300 shadow-2xs">
                            <img :src="getImageUrl(seo.og_image)" alt="OG Image Preview" class="w-full h-full object-cover">
                        </div>
                        <div class="space-y-2">
                            <button @click="openMediaPicker()" 
                                    type="button" 
                                    class="px-4 py-2 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark shadow-2xs transition-all cursor-pointer inline-flex items-center gap-1.5">
                                <span>🖼️</span>
                                <span>Pilih dari Media Picker</span>
                            </button>
                            <p class="text-[11px] text-gray-500 font-mono truncate max-w-xs" x-text="seo.og_image"></p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-gray-200 text-[11px] text-gray-500 space-y-1">
                        <p class="font-bold text-gray-700">Rekomendasi OG Image:</p>
                        <p>1200 × 630 px • Rasio 1.91:1 • JPG / WebP • Disarankan ≤ 300 KB</p>
                        <p class="italic text-[10px]">"Gambar ini otomatis muncul saat link website dibagikan di WhatsApp, Telegram, Facebook, dan media sosial."</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right: REAL LIVE PREVIEWS (5 cols on lg) -->
        <div class="lg:col-span-5 space-y-6 sticky top-4">
            
            <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                    <h3 class="text-xs font-extrabold text-brand-dark uppercase tracking-wider">
                        Real Live Preview
                    </h3>
                    <div class="flex items-center bg-gray-100 p-0.5 rounded text-[11px]">
                        <button @click="previewTab = 'google'" 
                                type="button" 
                                :class="previewTab === 'google' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500'"
                                class="px-2.5 py-1 rounded transition-all cursor-pointer">
                            🔍 Google SERP
                        </button>
                        <button @click="previewTab = 'social'" 
                                type="button" 
                                :class="previewTab === 'social' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500'"
                                class="px-2.5 py-1 rounded transition-all cursor-pointer">
                            📱 Social Card
                        </button>
                    </div>
                </div>

                <!-- Google SERP Simulation -->
                <div x-show="previewTab === 'google'" class="p-4 rounded-modern border border-gray-200 bg-white space-y-2 shadow-2xs">
                    <div class="flex items-center gap-2 text-xs text-gray-700">
                        <div class="w-6 h-6 rounded-full bg-brand-primary text-white flex items-center justify-center font-bold text-[11px]">
                            SP
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[12px] font-medium text-gray-900">Sumber Protein Jogja</span>
                            <span class="text-[10px] text-gray-500 font-mono leading-none" x-text="seo.canonical_url"></span>
                        </div>
                    </div>

                    <h4 class="text-base font-medium text-[#1a0dab] hover:underline cursor-pointer leading-snug pt-0.5"
                        x-text="seo.meta_title || 'Judul Halaman Google'"></h4>

                    <p class="text-xs text-[#4d5156] leading-relaxed line-clamp-3"
                       x-text="seo.meta_description || 'Deskripsi meta halaman website yang tampil pada hasil pencarian mesin pencari Google.'"></p>
                </div>

                <!-- Social Card Simulation (WhatsApp / Facebook) -->
                <div x-show="previewTab === 'social'" class="rounded-modern border border-gray-200 bg-gray-50 overflow-hidden shadow-2xs space-y-0">
                    <div class="aspect-[1.91/1] w-full bg-brand-dark overflow-hidden">
                        <img :src="getImageUrl(seo.og_image)" alt="OG Preview" class="w-full h-full object-cover">
                    </div>
                    <div class="p-3.5 bg-white space-y-1">
                        <span class="text-[10px] uppercase font-bold text-gray-400">sumberproteinjogja.com</span>
                        <h4 class="text-xs font-extrabold text-brand-dark leading-snug" x-text="seo.og_title || 'OG Title'"></h4>
                        <p class="text-[11px] text-gray-500 line-clamp-2" x-text="seo.og_description || 'OG Description...'"></p>
                    </div>
                </div>

                <p class="text-[11px] text-gray-400 text-center">
                    Preview di atas merefleksikan perubahan teks dan gambar secara real-time.
                </p>
            </div>

        </div>

    </div>

    <!-- ======================================================= -->
    <!-- 3. GLOBAL MEDIA PICKER MODAL                            -->
    <!-- ======================================================= -->
    <div x-show="mediaPickerOpen" 
         x-cloak
         class="fixed inset-0 z-[80] overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        
        <div class="fixed inset-0 bg-black/75 backdrop-blur-xs" @click="mediaPickerOpen = false"></div>

        <div class="min-h-full flex items-center justify-center p-3 sm:p-6">
            <div class="relative bg-white rounded-modern-xl max-w-3xl w-full p-6 shadow-2xl border border-gray-200 overflow-hidden my-6 space-y-5">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🖼️</span>
                        <div>
                            <h3 class="text-base font-extrabold text-brand-dark">Pilih OG Image dari Media Picker</h3>
                            <p class="text-xs text-gray-500">Pilih dari pustaka media atau unggah gambar cover tautan baru.</p>
                        </div>
                    </div>
                    <button @click="mediaPickerOpen = false" 
                            type="button" 
                            class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex items-center gap-2 border-b border-gray-200 pb-2">
                    <button @click="mediaTab = 'library'" type="button" 
                            :class="mediaTab === 'library' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer">
                        Media Library (<span x-text="mediaLibrary.length"></span>)
                    </button>
                    <button @click="mediaTab = 'upload'" type="button" 
                            :class="mediaTab === 'upload' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer">
                        Upload Gambar Baru
                    </button>
                </div>

                <!-- Tab 1: Library -->
                <div x-show="mediaTab === 'library'" class="space-y-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 max-h-72 overflow-y-auto p-1">
                        <template x-for="media in mediaLibrary" :key="media.id">
                            <div @click="selectMedia(media)"
                                 class="group relative aspect-[1.91/1] rounded-modern overflow-hidden border-2 transition-all cursor-pointer bg-brand-dark"
                                 :class="selectedMedia?.id === media.id ? 'border-brand-primary ring-2 ring-emerald-400' : 'border-gray-200 hover:border-gray-400'">
                                <img :src="getImageUrl(media.path)" :alt="media.title" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent p-2 flex flex-col justify-between">
                                    <div class="self-end" x-show="selectedMedia?.id === media.id">
                                        <span class="w-5 h-5 rounded-full bg-brand-primary text-white flex items-center justify-center text-xs font-bold shadow-sm">
                                            <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-white truncate" x-text="media.filename"></p>
                                        <p class="text-[9px] text-gray-300" x-text="media.resolution + ' • ' + media.size"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-modern border border-gray-200 flex items-center justify-between text-xs">
                        <div>
                            <span class="text-gray-500">Terpilih: </span>
                            <strong class="text-brand-dark" x-text="selectedMedia ? selectedMedia.filename : 'Belum ada'"></strong>
                        </div>
                        <button @click="confirmMediaSelection()" 
                                :disabled="!selectedMedia"
                                type="button" 
                                class="px-5 py-2 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark disabled:opacity-40 transition-all cursor-pointer">
                            Pilih OG Image
                        </button>
                    </div>
                </div>

                <!-- Tab 2: Upload -->
                <div x-show="mediaTab === 'upload'" class="space-y-4">
                    <label class="block border-2 border-dashed border-gray-300 rounded-modern-xl p-8 text-center hover:border-brand-primary hover:bg-brand-soft-green/30 transition-all cursor-pointer"
                           @dragover.prevent="" 
                           @drop.prevent="handleFileUpload($event)">
                        <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="handleFileUpload($event)">
                        <div class="space-y-2 flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full bg-brand-soft-green text-brand-primary flex items-center justify-center shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <p class="text-xs font-bold text-brand-dark">Tarik & Lepaskan gambar ke sini, atau klik untuk memilih file</p>
                            <p class="text-[11px] text-gray-400">Mendukung JPG, PNG, WebP (Rekomendasi 1200 × 630 px ≤ 300 KB)</p>
                        </div>
                    </label>

                    <template x-if="uploadedPreviewUrl">
                        <div class="p-3 bg-emerald-50/50 rounded-modern border border-emerald-200 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-20 aspect-[1.91/1] rounded overflow-hidden bg-brand-dark border border-gray-200">
                                    <img :src="uploadedPreviewUrl" alt="Uploaded Preview" class="w-full h-full object-cover">
                                </div>
                                <div class="text-xs space-y-0.5">
                                    <p class="font-bold text-brand-dark" x-text="uploadedFile?.name"></p>
                                    <p class="text-[10px] text-gray-500" x-text="uploadedFile?.size + ' • ' + uploadedFile?.type"></p>
                                </div>
                            </div>
                            <button @click="confirmMediaSelection()" 
                                    type="button" 
                                    class="px-5 py-2 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm cursor-pointer">
                                Gunakan Gambar Ini
                            </button>
                        </div>
                    </template>
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
