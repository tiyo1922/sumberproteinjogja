@extends('layouts.admin', [
    'title' => 'SEO & Meta',
    'pageTitle' => 'SEO & Meta Settings'
])

@section('content')
<script>
window.adminSeoManager = function(initialPayload) {
    const payload = initialPayload || {};
    return {
        csrfToken: payload.csrfToken || '{{ csrf_token() }}',
        isSaving: false,
        seo: payload.seo || {},
        mediaLibrary: payload.mediaLibrary || [],
        mediaPickerOpen: false,
        toastMessage: '',
        toastVisible: false,
        previewTab: 'google', // 'google' | 'social'
        googleDevice: 'desktop', // 'desktop' | 'mobile'
        mediaTab: 'library', // 'library' | 'upload'
        mediaSearchQuery: '',
        mediaDeleteRoute: payload.mediaDeleteRoute || '{{ route('admin.media.delete') }}',
        mediaUploadRoute: payload.mediaUploadRoute || '{{ route('admin.media.upload') }}',
        isDeletingMedia: false,
        isUploadingMedia: false,
        selectedMedia: null,
        uploadedFile: null,
        uploadedPreviewUrl: null,

        get filteredMediaLibrary() {
            if (!this.mediaSearchQuery || !this.mediaSearchQuery.trim()) {
                return this.mediaLibrary;
            }
            const q = this.mediaSearchQuery.toLowerCase().trim();
            return this.mediaLibrary.filter(m =>
                (m.filename && m.filename.toLowerCase().includes(q)) ||
                (m.title && m.title.toLowerCase().includes(q)) ||
                (m.path && m.path.toLowerCase().includes(q))
            );
        },

        mediaDeleteConfirmModalOpen: false,
        mediaToDelete: null,
        isDeletingMedia: false,

        openDeleteMediaModal(media) {
            this.mediaToDelete = media;
            this.mediaDeleteConfirmModalOpen = true;
        },

        async executeDeleteMedia() {
            if (!this.mediaToDelete) return;
            this.isDeletingMedia = true;
            try {
                const response = await fetch(this.mediaDeleteRoute || '{{ route('admin.media.delete') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ path: this.mediaToDelete.path })
                });
                const result = await response.json();
                if (response.ok && result.success) {
                    const deletedPath = this.mediaToDelete.path;
                    const deletedId = this.mediaToDelete.id;
                    this.mediaLibrary = this.mediaLibrary.filter(m => m.id !== deletedId && m.path !== deletedPath);
                    if (this.selectedMedia && (this.selectedMedia.path === deletedPath || this.selectedMedia.id === deletedId)) {
                        this.selectedMedia = null;
                    }
                    this.mediaDeleteConfirmModalOpen = false;
                    this.mediaToDelete = null;
                    this.showToast(result.message || 'File media berhasil dihapus!');
                } else {
                    alert(result.message || 'Gagal menghapus file media.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan koneksi saat menghapus file media.');
            } finally {
                this.isDeletingMedia = false;
            }
        },

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
            } else if (this.mediaTab === 'upload' && this.uploadedFile && this.uploadedFile.path) {
                this.seo.og_image = this.uploadedFile.path;
                this.mediaPickerOpen = false;
                this.showToast('OG Image hasil upload berhasil digunakan!');
            } else if (this.selectedMedia) {
                this.seo.og_image = this.selectedMedia.path;
                this.mediaPickerOpen = false;
                this.showToast('OG Image dipilih dari Media Library!');
            }
        },

        async handleFileUpload(e) {
            const file = e.target.files ? e.target.files[0] : (e.dataTransfer ? e.dataTransfer.files[0] : null);
            if (!file) return;
            if (!['image/jpeg', 'image/png', 'image/webp', 'image/jpg'].includes(file.type)) {
                alert('Format file tidak didukung. Gunakan JPG, PNG, atau WebP.');
                return;
            }
            this.isUploadingMedia = true;
            this.uploadedFile = {
                name: file.name,
                size: (file.size / 1024).toFixed(0) + ' KB',
                type: file.type,
                path: ''
            };
            this.uploadedPreviewUrl = URL.createObjectURL(file);

            try {
                const formData = new FormData();
                formData.append('image', file);

                const response = await fetch(this.mediaUploadRoute || '{{ route('admin.media.upload') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await response.json();
                if (response.ok && result.success && result.media) {
                    this.mediaLibrary.unshift(result.media);
                    this.selectedMedia = result.media;
                    this.uploadedPreviewUrl = result.media.url;
                    this.uploadedFile.path = result.media.path;
                    this.showToast('File media berhasil diunggah ke storage server!');
                } else {
                    this.showToast(result.message || 'Gagal mengunggah file media.');
                }
            } catch (err) {
                console.error(err);
                this.showToast('Terjadi kesalahan koneksi saat mengunggah file.');
            } finally {
                this.isUploadingMedia = false;
            }
        },

        async saveSeo() {
            if (this.isSaving) return;
            this.isSaving = true;

            try {
                const response = await fetch('{{ route('admin.seo.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        seo: {
                            meta_title: this.seo.meta_title,
                            meta_description: this.seo.meta_description,
                            canonical_url: this.seo.canonical_url,
                            robots: this.seo.robots,
                            meta_keywords: this.seo.meta_keywords,
                            og_title: this.seo.og_title,
                            og_description: this.seo.og_description,
                            og_image: this.seo.og_image,
                        }
                    }),
                });

                const result = await response.json().catch(() => ({}));

                if (response.status === 401 || (result && result.message === 'Unauthenticated.')) {
                    alert('Sesi login Anda telah berakhir. Anda akan dialihkan ke halaman login.');
                    window.location.href = '/login';
                    return;
                }

                if (response.status === 419) {
                    alert('Sesi token kedaluwarsa. Halaman akan dimuat ulang.');
                    window.location.reload();
                    return;
                }

                if (!response.ok || !result.success) {
                    const errorMsg = result.errors
                        ? Object.values(result.errors).flat().join('\n')
                        : (result.message || 'Gagal menyimpan pengaturan SEO.');
                    alert(errorMsg);
                    return;
                }

                if (result.seo) {
                    this.seo = { ...this.seo, ...result.seo };
                }

                this.showToast(result.message || 'Pengaturan SEO & Meta berhasil disimpan ke database.');
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat menyimpan pengaturan SEO.');
            } finally {
                this.isSaving = false;
            }
        },

        getImageUrl(path) {
            if (!path) return '/storage/media/hero_meat_poultry_1786889302143.jpg';
            if (path.startsWith('blob:') || path.startsWith('http')) return path;
            return path.startsWith('/') ? path : '/' + path;
        }
    };
};

window.initialSeoPayload = {
    csrfToken: '{{ csrf_token() }}',
    seo: @json($seoData),
    mediaLibrary: @json($mediaLibrary)
};
</script>

<div class="space-y-6"
     x-data="adminSeoManager(window.initialSeoPayload)">

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
                        :disabled="isSaving"
                        type="button"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                    <svg x-show="isSaving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Pengaturan SEO'"></span>
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

        <!-- Right: PREVIEWS (5 cols on lg) -->
        <div class="lg:col-span-5 space-y-6 sticky top-4">

            <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                    <h3 class="text-xs font-extrabold text-brand-dark uppercase tracking-wider">
                        Preview
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
                <div x-show="mediaTab === 'library'" class="space-y-3">
                    <!-- Search Bar -->
                    <div class="flex items-center gap-3">
                        <div class="relative flex-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 text-xs">
                                🔍
                            </span>
                            <input type="text"
                                   x-model="mediaSearchQuery"
                                   placeholder="Cari gambar berdasarkan nama file..."
                                   class="w-full pl-8 pr-8 py-2 text-xs rounded-modern border border-gray-200 bg-gray-50 focus:bg-white focus:border-brand-primary focus:outline-none transition-all">
                            <button x-show="mediaSearchQuery"
                                    @click="mediaSearchQuery = ''"
                                    type="button"
                                    class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 text-xs cursor-pointer">
                                ✕
                            </button>
                        </div>
                        <span class="text-[11px] text-gray-500 font-medium whitespace-nowrap">
                            <span x-text="filteredMediaLibrary.length"></span> dari <span x-text="mediaLibrary.length"></span> gambar
                        </span>
                    </div>

                    <!-- Media Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 max-h-72 overflow-y-auto p-1 overscroll-contain no-scrollbar">
                        <template x-for="media in filteredMediaLibrary" :key="media.id">
                            <div @click="selectMedia(media)"
                                 class="group relative aspect-[1.91/1] rounded-modern overflow-hidden border-2 transition-all cursor-pointer bg-brand-dark"
                                 :class="selectedMedia?.id === media.id ? 'border-brand-primary ring-2 ring-emerald-400' : 'border-gray-200 hover:border-gray-400'">
                                <img :src="getImageUrl(media.path)" :alt="media.title" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent p-2 flex flex-col justify-between">
                                    <div class="flex items-center justify-between w-full">
                                        <div>
                                            <button @click.stop="openDeleteMediaModal(media)"
                                                    type="button"
                                                    :title="media.is_in_use ? 'Media sedang digunakan (Klik untuk opsi hapus)' : 'Hapus media dari server'"
                                                    class="p-1 rounded bg-rose-600/90 text-white hover:bg-rose-700 hover:scale-110 shadow-xs transition-all cursor-pointer flex items-center justify-center">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <template x-if="media.is_in_use">
                                                <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-amber-500/90 text-white shadow-xs" :title="'Digunakan di: ' + (media.usage_locations || []).join(', ')">
                                                    Pakai (<span x-text="media.usage_count"></span>)
                                                </span>
                                            </template>
                                            <div x-show="selectedMedia?.id === media.id">
                                                <span class="w-5 h-5 rounded-full bg-brand-primary text-white flex items-center justify-center text-xs font-bold shadow-sm">
                                                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-white truncate" x-text="media.filename"></p>
                                        <p class="text-[9px] text-gray-300" x-text="media.resolution + ' • ' + media.size"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Empty Search State -->
                    <div x-show="filteredMediaLibrary.length === 0" class="p-8 text-center bg-gray-50 rounded-modern border border-dashed border-gray-200 text-xs text-gray-400">
                        Tidak ada gambar yang cocok dengan kata kunci "<span class="font-bold text-gray-600" x-text="mediaSearchQuery"></span>".
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

    <!-- Modal Konfirmasi / Warning Hapus Media -->
    <div x-show="mediaDeleteConfirmModalOpen"
         x-cloak
         class="fixed inset-0 z-[120] overflow-y-auto"
         role="dialog"
         aria-modal="true">

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"
             @click="if (!isDeletingMedia) { mediaDeleteConfirmModalOpen = false; mediaToDelete = null; }"></div>

        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white rounded-modern-lg shadow-2xl border border-gray-100 w-full max-w-md overflow-hidden p-6 space-y-4 text-left">

                <!-- Header Warning / Info -->
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                         :class="mediaToDelete?.is_in_use ? 'bg-amber-100 text-amber-600' : 'bg-rose-100 text-rose-600'">
                        <template x-if="mediaToDelete?.is_in_use">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </template>
                        <template x-if="!mediaToDelete?.is_in_use">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </template>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-gray-900"
                            x-text="mediaToDelete?.is_in_use ? 'Media Sedang Digunakan' : 'Hapus Media?'"></h4>
                        <p class="text-xs text-gray-500 break-all font-mono bg-gray-50 px-2 py-1 rounded border border-gray-200"
                           x-text="mediaToDelete?.filename"></p>
                    </div>
                </div>

                <!-- Body Section: In-Use Details or Unused Info -->
                <template x-if="mediaToDelete?.is_in_use">
                    <div class="space-y-3">
                        <div class="bg-amber-50 border border-amber-200 rounded-modern p-3 text-xs text-amber-900 space-y-1.5">
                            <p class="font-semibold text-amber-950">Media ini sedang aktif digunakan oleh:</p>
                            <ul class="max-h-28 overflow-y-auto space-y-1 pl-1 text-[11px] no-scrollbar">
                                <template x-for="loc in (mediaToDelete?.usage_locations || [])" :key="loc">
                                    <li class="flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                        <span x-text="loc"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <p class="text-[11px] text-rose-600 font-medium leading-relaxed">
                            ⚠️ <strong>Peringatan Risiko:</strong> Jika media ini dihapus, gambar pada bagian terkait di atas tidak akan dapat ditampilkan lagi dan dapat menyebabkan <strong>BROKEN IMAGE</strong>. Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                </template>

                <template x-if="!mediaToDelete?.is_in_use">
                    <p class="text-xs text-gray-600 leading-relaxed">
                        File ini tidak sedang digunakan oleh produk, kategori, artikel, maupun pengaturan situs. Tindakan ini tidak dapat dibatalkan.
                    </p>
                </template>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button @click="mediaDeleteConfirmModalOpen = false; mediaToDelete = null;"
                            type="button"
                            :disabled="isDeletingMedia"
                            class="px-3.5 py-1.5 rounded-modern border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-all cursor-pointer">
                        Batal
                    </button>
                    <button @click="executeDeleteMedia()"
                            type="button"
                            :disabled="isDeletingMedia"
                            class="px-4 py-1.5 rounded-modern bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                        <template x-if="isDeletingMedia">
                            <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="isDeletingMedia ? 'Menghapus...' : (mediaToDelete?.is_in_use ? 'Ya, Hapus Media' : 'Ya, Hapus')"></span>
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
