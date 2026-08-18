@extends('layouts.admin', [
    'title' => 'Hero Slider',
    'pageTitle' => 'Hero Slider'
])

@section('content')
<div class="space-y-6"
     x-data="{
         drafts: {{ json_encode($drafts) }},
         mediaLibrary: {{ json_encode($mediaLibrary) }},
         
         // Modals & Panels State
         editorModalOpen: false,
         mediaPickerOpen: false,
         activateModalOpen: false,
         deleteModalOpen: false,
         
         // Media Picker Sub-state
         mediaPickerTab: 'library', // 'library' | 'upload'
         selectedMediaItem: null,
         mediaPickerTargetIndex: null, // null = append new image, number = replace at index
         
         // Upload Simulation State (HTML5 File API + URL.createObjectURL)
         uploadedMockImage: null,
         isDragging: false,
         
         // Preview & Notification State
         previewDevice: 'desktop', // 'desktop' | 'tablet' | 'mobile'
         activePreviewImageIndex: 0,
         toastMessage: '',
         toastVisible: false,
         isEditingDraft: false,
         
         // Helper for Image URLs (handles relative paths, blob URLs, and external links)
         getImageUrl(path) {
             if (!path) return '/images/hero-1.jpg';
             if (path.startsWith('blob:') || path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) {
                 return path;
             }
             return path.startsWith('/') ? path : '/' + path;
         },
         
         // Form Model for 1 Draft Hero
         draftForm: {
             id: null,
             name: '',
             badge: 'Penyedia Bahan Segar & Frozen Food Terpercaya di Jogja',
             headline_prefix: 'Bahan Masak',
             highlight: 'Siap Olah',
             headline_suffix: ', Tinggal Masak.',
             description: 'Daging, ayam, ikan, dan sayuran pilihan dalam bentuk frozen dan ready to cook untuk kebutuhan rumah tangga maupun pembelian curah.',
             primary_cta_text: 'Belanja Sekarang',
             primary_cta_link: '#produk',
             secondary_cta_text: 'Lihat Produk',
             secondary_cta_link: '#kategori',
             images: ['images/hero-1.jpg', 'images/hero-2.jpg', 'images/hero-3.jpg', 'images/cat-daging.jpg'],
             trust_items: [
                 { id: 1, text: '100% Halal', active: true },
                 { id: 2, text: 'Cold Chain', active: true },
                 { id: 3, text: 'Kirim Se-Jogja', active: true }
             ],
             status: 'Nonaktif',
             updated_at: 'Baru saja'
         },
         
         selectedDraft: null,
         
         showToast(msg) {
             this.toastMessage = msg;
             this.toastVisible = true;
             setTimeout(() => { this.toastVisible = false; }, 3000);
         },
         
         openCreateDraftModal() {
             if (this.drafts.length >= 3) {
                 this.showToast('Maksimal 3 draft Hero.');
                 return;
             }
             this.isEditingDraft = false;
             this.previewDevice = 'desktop';
             this.activePreviewImageIndex = 0;
             const nextNum = this.drafts.length + 1;
             this.draftForm = {
                 id: Date.now(),
                 name: 'Hero Draft 0' + nextNum,
                 badge: 'Protein Segar & Higienis Pilihan Keluarga',
                 headline_prefix: 'Bahan Masak',
                 highlight: 'Kualitas Premium',
                 headline_suffix: ' Siap Olah.',
                 description: 'Pilihan daging sapi slice, ayam segar, fillet ikan, dan sayuran higienis untuk kebutuhan harian.',
                 primary_cta_text: 'Belanja Sekarang',
                 primary_cta_link: '#produk',
                 secondary_cta_text: 'Lihat Produk',
                 secondary_cta_link: '#kategori',
                 images: ['images/hero-1.jpg', 'images/hero-2.jpg'],
                 trust_items: [
                     { id: 1, text: '100% Halal', active: true },
                     { id: 2, text: 'Cold Chain', active: true },
                     { id: 3, text: 'Kirim Se-Jogja', active: true }
                 ],
                 status: 'Nonaktif',
                 updated_at: 'Baru saja'
             };
             this.editorModalOpen = true;
         },
         
         openEditDraftModal(draft) {
             this.isEditingDraft = true;
             this.previewDevice = 'desktop';
             this.activePreviewImageIndex = 0;
             this.draftForm = JSON.parse(JSON.stringify(draft));
             // Ensure trust_items has 3 items
             if (!this.draftForm.trust_items || this.draftForm.trust_items.length === 0) {
                 this.draftForm.trust_items = [
                     { id: 1, text: '100% Halal', active: true },
                     { id: 2, text: 'Cold Chain', active: true },
                     { id: 3, text: 'Kirim Se-Jogja', active: true }
                 ];
             }
             this.editorModalOpen = true;
         },
         
         saveDraft() {
             if (this.draftForm.images.length === 0) {
                 alert('Minimal harus ada 1 gambar latar untuk slideshow.');
                 return;
             }
             if (this.isEditingDraft) {
                 const idx = this.drafts.findIndex(d => d.id === this.draftForm.id);
                 if (idx !== -1) {
                     this.draftForm.updated_at = '17 Agustus 2026, ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
                     this.drafts[idx] = JSON.parse(JSON.stringify(this.draftForm));
                 }
                 this.showToast('Draft Hero berhasil diperbarui!');
             } else {
                 this.draftForm.updated_at = '17 Agustus 2026, ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
                 this.drafts.push(JSON.parse(JSON.stringify(this.draftForm)));
                 this.showToast('Draft Hero baru berhasil dibuat!');
             }
             this.editorModalOpen = false;
         },
         
         // Media Picker Management
         openMediaPickerForAdd() {
             if (this.draftForm.images.length >= 4) {
                 this.showToast('Maksimal 4 gambar per Hero.');
                 return;
             }
             this.mediaPickerTargetIndex = null; // append mode
             this.selectedMediaItem = this.mediaLibrary[0];
             this.mediaPickerTab = 'library';
             this.uploadedMockImage = null;
             this.mediaPickerOpen = true;
         },
         
         openMediaPickerForReplace(index) {
             this.mediaPickerTargetIndex = index; // replace mode
             const currentPath = this.draftForm.images[index];
             const found = this.mediaLibrary.find(m => m.path === currentPath);
             this.selectedMediaItem = found || this.mediaLibrary[0];
             this.mediaPickerTab = 'library';
             this.uploadedMockImage = null;
             this.mediaPickerOpen = true;
         },
         
         handleFileSelected(file) {
             if (!file) return;
             if (!file.type.match('image.*')) {
                 alert('Silakan pilih file gambar (JPG, PNG, atau WebP).');
                 return;
             }
             const previewUrl = URL.createObjectURL(file);
             const sizeKb = Math.round(file.size / 1024);
             
             this.uploadedMockImage = {
                 filename: file.name,
                 path: previewUrl,
                 isBlob: true,
                 title: file.name,
                 resolution: '1920 × 1080 px',
                 ratio: '16:9',
                 size: sizeKb + ' KB',
                 is_recommended: true
             };
             this.showToast('Gambar ' + file.name + ' siap digunakan.');
         },
         
         applySelectedMedia() {
             let imagePath = null;
             if (this.mediaPickerTab === 'library') {
                 if (!this.selectedMediaItem) {
                     alert('Silakan pilih salah satu gambar dari Media Library.');
                     return;
                 }
                 imagePath = this.selectedMediaItem.path;
             } else if (this.mediaPickerTab === 'upload') {
                 if (!this.uploadedMockImage) {
                     alert('Silakan upload gambar terlebih dahulu.');
                     return;
                 }
                 imagePath = this.uploadedMockImage.path;
             }
             
             if (!imagePath) {
                 alert('Silakan pilih gambar terlebih dahulu.');
                 return;
             }
             
             // Duplicate Check: Check if image is already used in this draft (only when adding new or picking different image)
             if (this.mediaPickerTargetIndex === null && this.draftForm.images.includes(imagePath)) {
                 alert('Gambar ini sudah digunakan pada slideshow.');
                 return;
             }
             
             if (this.mediaPickerTargetIndex !== null && this.draftForm.images.includes(imagePath) && this.draftForm.images[this.mediaPickerTargetIndex] !== imagePath) {
                 alert('Gambar ini sudah digunakan pada slide lain dalam slideshow.');
                 return;
             }
             
             if (this.mediaPickerTargetIndex !== null) {
                 // Replace at index
                 this.draftForm.images[this.mediaPickerTargetIndex] = imagePath;
                 this.showToast('Gambar slide ' + (this.mediaPickerTargetIndex + 1) + ' berhasil diganti.');
             } else {
                 // Append new image
                 if (this.draftForm.images.length < 4) {
                     this.draftForm.images.push(imagePath);
                     this.showToast('Gambar berhasil ditambahkan ke slideshow.');
                 }
             }
             
             this.mediaPickerOpen = false;
         },
         
         removeImageFromDraft(imgIndex) {
             if (this.draftForm.images.length <= 1) {
                 alert('Minimal 1 gambar latar diperlukan.');
                 return;
             }
             this.draftForm.images.splice(imgIndex, 1);
             if (this.activePreviewImageIndex >= this.draftForm.images.length) {
                 this.activePreviewImageIndex = 0;
             }
             this.showToast('Gambar dihapus dari slideshow.');
         },
         
         moveImageUp(imgIndex) {
             if (imgIndex > 0) {
                 const item = this.draftForm.images.splice(imgIndex, 1)[0];
                 this.draftForm.images.splice(imgIndex - 1, 0, item);
             }
         },
         
         moveImageDown(imgIndex) {
             if (imgIndex < this.draftForm.images.length - 1) {
                 const item = this.draftForm.images.splice(imgIndex, 1)[0];
                 this.draftForm.images.splice(imgIndex + 1, 0, item);
             }
         },
         
         // Activation & Deletion Handlers
         openActivateModal(draft) {
             this.selectedDraft = draft;
             this.activateModalOpen = true;
         },
         
         confirmActivate() {
             if (this.selectedDraft) {
                 this.drafts.forEach(d => {
                     d.status = (d.id === this.selectedDraft.id) ? 'Aktif' : 'Nonaktif';
                 });
                 this.showToast('Hero ' + this.selectedDraft.name + ' sekarang AKTIF di website!');
                 this.activateModalOpen = false;
                 this.selectedDraft = null;
             }
         },
         
         duplicateDraft(draft) {
             if (this.drafts.length >= 3) {
                 this.showToast('Tidak dapat menduplikat. Maksimal 3 draft Hero.');
                 return;
             }
             const copy = JSON.parse(JSON.stringify(draft));
             copy.id = Date.now();
             copy.name = copy.name + ' (Salinan)';
             copy.status = 'Nonaktif';
             copy.updated_at = 'Baru saja';
             this.drafts.push(copy);
             this.showToast('Draft berhasil diduplikat.');
         },
         
         openDeleteModal(draft) {
             if (draft.status === 'Aktif' && this.drafts.length > 1) {
                 alert('Tidak dapat menghapus Hero yang sedang AKTIF. Silakan aktifkan draft lain terlebih dahulu.');
                 return;
             }
             this.selectedDraft = draft;
             this.deleteModalOpen = true;
         },
         
         confirmDelete() {
             if (this.selectedDraft) {
                 this.drafts = this.drafts.filter(d => d.id !== this.selectedDraft.id);
                 this.deleteModalOpen = false;
                 this.selectedDraft = null;
                 this.showToast('Draft Hero berhasil dihapus.');
             }
         }
     }">
    
    <!-- 1. Module Header & Draft Overview Card -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-8 shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-brand-dark tracking-tight">
                        Daftar Draft Hero
                    </h2>
                    
                    <!-- HYBRID Classification Badge -->
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                        <span>HYBRID</span>
                    </span>
                    <span class="text-xs text-gray-500 font-medium">
                        • Layout Locked — Maksimal 3 Draft (1 Aktif di Website)
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 leading-relaxed max-w-3xl">
                    Kelola variasi tampilan Hero. Setiap draft terdiri dari <strong>1 konfigurasi teks</strong>, <strong>maksimal 4 foto slideshow latar</strong>, dan <strong>3 trust checklist keunggulan</strong>.
                </p>
            </div>

            <!-- Create Draft Action Button -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 shrink-0">
                <button @click="openCreateDraftModal()" 
                        :disabled="drafts.length >= 3"
                        type="button"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern font-bold text-xs sm:text-sm text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow disabled:opacity-40 disabled:cursor-not-allowed transition-all cursor-pointer">
                    <span class="text-base leading-none">＋</span>
                    <span>Buat Draft Hero</span>
                </button>
                <template x-if="drafts.length >= 3">
                    <span class="text-[11px] font-medium text-amber-700 bg-amber-50 px-2.5 py-1 rounded border border-amber-200">
                        Maksimal 3 draft Hero
                    </span>
                </template>
            </div>
        </div>
    </div>

    <!-- 2. Draft Hero Cards List (Max 3 Drafts) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between text-xs font-bold text-gray-500 uppercase tracking-wider px-1">
            <span>Draft Hero Tersedia (<span x-text="drafts.length"></span> / 3 Draft)</span>
            <span>Status & Aksi</span>
        </div>

        <template x-for="(draft, index) in drafts" :key="draft.id">
            <div class="bg-white rounded-modern-xl border p-5 sm:p-6 shadow-2xs transition-all duration-200 flex flex-col lg:flex-row gap-5 lg:gap-6 items-start lg:items-center justify-between"
                 :class="draft.status === 'Aktif' 
                     ? 'border-brand-primary/50 shadow-md ring-1 ring-brand-primary/20' 
                     : 'border-gray-200/80 hover:border-gray-300'">
                
                <!-- Left: 16:9 First Image Thumbnail -->
                <div class="relative aspect-[16/9] w-full sm:w-64 lg:w-72 rounded-modern-lg overflow-hidden bg-brand-dark shrink-0 border border-gray-200 shadow-2xs">
                    <img :src="getImageUrl(draft.images[0])" 
                         :alt="draft.name" 
                         class="w-full h-full object-cover object-center">
                    
                    <!-- Dark Gradient Overlay Simulation -->
                    <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/90 via-brand-dark/40 to-transparent"></div>

                    <!-- Draft Name Pill -->
                    <div class="absolute top-2.5 left-2.5">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-brand-dark/90 text-white backdrop-blur-xs border border-white/20 shadow-sm"
                              x-text="draft.name">
                        </span>
                    </div>

                    <!-- Images Count Indicator -->
                    <div class="absolute bottom-2.5 right-2.5">
                        <span class="px-2 py-0.5 rounded text-[9px] font-semibold bg-black/75 text-white backdrop-blur-xs">
                            <span x-text="draft.images.length"></span> / 4 Foto
                        </span>
                    </div>
                </div>

                <!-- Middle: Draft Details & Structured Headline Preview -->
                <div class="flex-1 space-y-2 min-w-0">
                    
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <!-- Active / Draft Status Badge -->
                        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-[11px] font-extrabold border"
                              :class="draft.status === 'Aktif' 
                                  ? 'bg-emerald-50 text-emerald-800 border-emerald-300 ring-2 ring-emerald-400/20' 
                                  : 'bg-gray-100 text-gray-500 border-gray-200'">
                            <span class="w-2 h-2 rounded-full"
                                  :class="draft.status === 'Aktif' ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400'"></span>
                            <span x-text="draft.status === 'Aktif' ? 'AKTIF DI WEBSITE' : 'DRAFT (NONAKTIF)'"></span>
                        </span>

                        <span class="text-xs text-gray-400">• Terakhir diubah: <span class="font-medium text-gray-600" x-text="draft.updated_at"></span></span>
                    </div>

                    <!-- Structured Headline with Highlight -->
                    <h3 class="text-base sm:text-lg font-extrabold text-brand-dark leading-snug">
                        <span x-text="draft.headline_prefix"></span> 
                        <span class="text-emerald-600 underline decoration-amber-500 decoration-2 underline-offset-4 font-black"
                              x-text="draft.highlight"></span><span x-text="draft.headline_suffix"></span>
                    </h3>

                    <!-- Description preview -->
                    <p class="text-xs text-gray-600 leading-relaxed line-clamp-2"
                       x-text="draft.description">
                    </p>

                    <!-- Trust Checklist Tags Preview -->
                    <div class="pt-1 flex items-center gap-2 flex-wrap text-[11px] font-semibold text-gray-600">
                        <span class="text-[10px] text-gray-400 uppercase tracking-wider font-bold">Trust:</span>
                        <template x-for="(item, tIdx) in (draft.trust_items || [])" :key="tIdx">
                            <span x-show="item.active" 
                                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span>✓</span>
                                <span x-text="item.text"></span>
                            </span>
                        </template>
                    </div>
                </div>

                <!-- Right: Action Buttons -->
                <div class="flex items-center gap-2 w-full lg:w-auto pt-3 lg:pt-0 border-t lg:border-t-0 border-gray-100 shrink-0 justify-end">
                    
                    <!-- Edit Button -->
                    <button @click="openEditDraftModal(draft)" 
                            type="button"
                            class="px-4 py-2 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark transition-colors cursor-pointer shadow-2xs">
                        Edit
                    </button>

                    <!-- Jadikan Aktif Button (Only for inactive drafts) -->
                    <template x-if="draft.status !== 'Aktif'">
                        <button @click="openActivateModal(draft)" 
                                type="button"
                                class="px-3.5 py-2 rounded-modern text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 transition-colors cursor-pointer">
                            Jadikan Aktif
                        </button>
                    </template>

                    <!-- Dropdown Options (⋮) -->
                    <div class="relative" x-data="{ menuOpen: false }">
                        <button @click="menuOpen = !menuOpen" 
                                @click.away="menuOpen = false"
                                type="button"
                                class="p-2 rounded-modern text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu items -->
                        <div x-show="menuOpen" 
                             x-cloak
                             x-transition
                             class="absolute right-0 mt-1 w-36 bg-white rounded-modern border border-gray-200 shadow-lg py-1 z-20 text-xs font-medium text-gray-700">
                            <button @click="duplicateDraft(draft); menuOpen = false" 
                                    type="button" 
                                    class="w-full text-left px-3.5 py-2 hover:bg-gray-50 flex items-center gap-2 cursor-pointer">
                                <span>📋 Duplikat</span>
                            </button>
                            <button @click="openDeleteModal(draft); menuOpen = false" 
                                    type="button" 
                                    class="w-full text-left px-3.5 py-2 hover:bg-rose-50 text-rose-600 flex items-center gap-2 cursor-pointer">
                                <span>🗑 Hapus</span>
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </template>
    </div>

    <!-- ======================================================= -->
    <!-- 3. DRAFT HERO EDITOR MODAL (Left Form + Right Preview)  -->
    <!-- ======================================================= -->
    <div x-show="editorModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        
        <!-- Backdrop -->
        <div x-show="editorModalOpen"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 backdrop-blur-xs"
             @click="editorModalOpen = false">
        </div>

        <!-- Modal Dialog Container -->
        <div class="min-h-full flex items-center justify-center p-3 sm:p-6">
            <div x-show="editorModalOpen"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-modern-xl max-w-7xl w-full p-5 sm:p-8 shadow-2xl border border-gray-200 overflow-hidden my-6">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-100">
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h3 class="text-lg font-extrabold text-brand-dark"
                                x-text="isEditingDraft ? 'Edit ' + draftForm.name : 'Buat Draft Hero Baru'">
                            </h3>
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                HYBRID • 1 Teks + Maks 4 Foto
                            </span>
                        </div>
                        <p class="text-xs text-gray-500">Teks Hero bersifat tetap di atas slideshow, latar berganti otomatis sesuai foto yang diupload.</p>
                    </div>
                    <button @click="editorModalOpen = false" 
                            type="button" 
                            class="p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form Grid: Left Inputs, Right Live Source-of-Truth Preview -->
                <form @submit.prevent="saveDraft()" class="space-y-6">
                    
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        
                        <!-- Left Column: Form Fields (6 cols on lg) -->
                        <div class="lg:col-span-6 space-y-6">
                            
                            <!-- 1. Background Slideshow Images (Media Picker Trigger) -->
                            <div class="p-5 rounded-modern-xl bg-gray-50 border border-gray-200 space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="block text-xs font-extrabold text-brand-dark">
                                            1. Hero Background Slideshow (<span x-text="draftForm.images.length"></span> / 4 Gambar)
                                        </label>
                                        <p class="text-[11px] text-gray-500">Kelola foto latar dengan Media Picker (Maksimal 4 gambar slideshow).</p>
                                    </div>
                                    <button @click="openMediaPickerForAdd()" 
                                            :disabled="draftForm.images.length >= 4"
                                            type="button"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-modern text-xs font-bold text-brand-primary bg-brand-soft-green hover:bg-emerald-100 border border-brand-soft-green-border disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer shadow-2xs">
                                        <span class="text-sm leading-none">＋</span>
                                        <span>Tambah Gambar</span>
                                    </button>
                                </div>

                                <!-- Image List -->
                                <div class="space-y-2.5">
                                    <template x-for="(img, imgIdx) in draftForm.images" :key="imgIdx">
                                        <div class="bg-white p-3 rounded-modern border border-gray-200 flex items-center justify-between gap-3 shadow-2xs hover:border-gray-300 transition-colors">
                                            
                                            <!-- Preview & Number -->
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="relative w-20 aspect-[16/9] rounded overflow-hidden bg-brand-dark shrink-0 border border-gray-200 shadow-2xs">
                                                    <img :src="getImageUrl(img)" alt="Slide" class="w-full h-full object-cover">
                                                </div>
                                                <div class="space-y-0.5 min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs font-bold text-brand-dark" x-text="'Slide Foto ' + (imgIdx + 1)"></span>
                                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-gray-100 text-gray-600 font-mono">1920 × 1080 px</span>
                                                    </div>
                                                    <p class="text-[10px] text-gray-400 truncate font-mono" x-text="img"></p>
                                                </div>
                                            </div>

                                            <!-- Controls: Replace, Reorder & Delete -->
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <button @click="openMediaPickerForReplace(imgIdx)" 
                                                        type="button" 
                                                        class="px-2.5 py-1 text-[11px] font-semibold text-gray-600 hover:text-brand-dark bg-gray-100 hover:bg-gray-200 rounded border border-gray-200 transition-colors cursor-pointer">
                                                    Ganti
                                                </button>
                                                <button @click="moveImageUp(imgIdx)" 
                                                        :disabled="imgIdx === 0"
                                                        type="button" 
                                                        title="Naikkan urutan"
                                                        class="p-1 text-gray-500 hover:text-brand-dark disabled:opacity-20 cursor-pointer">
                                                    ↑
                                                </button>
                                                <button @click="moveImageDown(imgIdx)" 
                                                        :disabled="imgIdx === draftForm.images.length - 1"
                                                        type="button" 
                                                        title="Turunkan urutan"
                                                        class="p-1 text-gray-500 hover:text-brand-dark disabled:opacity-20 cursor-pointer">
                                                    ↓
                                                </button>
                                                <button @click="removeImageFromDraft(imgIdx)" 
                                                        :disabled="draftForm.images.length <= 1"
                                                        type="button" 
                                                        title="Hapus foto"
                                                        class="p-1 text-rose-500 hover:text-rose-700 disabled:opacity-20 cursor-pointer">
                                                    ✕
                                                </button>
                                            </div>

                                        </div>
                                    </template>
                                </div>

                                <!-- Image Guidance Summary -->
                                <div class="pt-3 border-t border-gray-200/80 flex items-center justify-between text-[10px] text-gray-500">
                                    <span>Rekomendasi: 1920 × 1080 px • 16:9 • JPG/WebP ≤ 500 KB</span>
                                    <template x-if="draftForm.images.length >= 4">
                                        <span class="text-amber-700 font-bold bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                            Maksimal 4 gambar slideshow
                                        </span>
                                    </template>
                                    <template x-if="draftForm.images.length < 4">
                                        <span class="text-brand-primary font-semibold">Media Library & Upload Tersedia</span>
                                    </template>
                                </div>
                            </div>

                            <!-- 2. Hero Content -->
                            <div class="p-5 rounded-modern-xl bg-gray-50 border border-gray-200 space-y-4">
                                <div class="border-b border-gray-200 pb-2">
                                    <h4 class="text-xs font-extrabold text-brand-dark uppercase tracking-wider">
                                        2. Hero Content (Teks & Headline)
                                    </h4>
                                </div>

                                <!-- Draft Name -->
                                <div>
                                    <label class="block text-xs font-bold text-brand-dark mb-1">
                                        Nama Draft Hero <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" 
                                           x-model="draftForm.name" 
                                           required
                                           placeholder="Hero Draft 01"
                                           class="w-full text-xs sm:text-sm rounded-modern border border-gray-300 p-2.5 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                                </div>

                                <!-- Badge Text -->
                                <div>
                                    <label class="block text-xs font-bold text-brand-dark mb-1">
                                        Teks Badge Atas <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" 
                                           x-model="draftForm.badge" 
                                           required
                                           placeholder="Penyedia Bahan Segar & Frozen Food Terpercaya di Jogja"
                                           class="w-full text-xs sm:text-sm rounded-modern border border-gray-300 p-2.5 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                                </div>

                                <!-- Structured Headline with Highlight Text -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-xs font-bold text-brand-dark">
                                            Struktur Headline & Highlight Teks
                                        </label>
                                        <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                            Auto-Styled
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">
                                                Awal Headline
                                            </label>
                                            <input type="text" 
                                                   x-model="draftForm.headline_prefix" 
                                                   required
                                                   placeholder="Bahan Masak"
                                                   class="w-full text-xs rounded-modern border border-gray-300 p-2 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-emerald-700 mb-1">
                                                ★ Highlight Teks
                                            </label>
                                            <input type="text" 
                                                   x-model="draftForm.highlight" 
                                                   required
                                                   placeholder="Siap Olah"
                                                   class="w-full text-xs rounded-modern border-2 border-emerald-500 p-2 bg-emerald-50/50 font-bold text-emerald-900 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">
                                                Lanjutan Headline
                                            </label>
                                            <input type="text" 
                                                   x-model="draftForm.headline_suffix" 
                                                   placeholder=", Tinggal Masak."
                                                   class="w-full text-xs rounded-modern border border-gray-300 p-2 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-gray-500">
                                        Kata <strong>Highlight Teks</strong> otomatis diberi aksen hijau emerald dan garis bawah emas tanpa perlu menulis HTML.
                                    </p>
                                </div>

                                <!-- Description Paragraph -->
                                <div>
                                    <label class="block text-xs font-bold text-brand-dark mb-1">
                                        Paragraf Deskripsi <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea x-model="draftForm.description" 
                                              rows="3" 
                                              required
                                              placeholder="Daging, ayam, ikan, dan sayuran pilihan dalam bentuk frozen dan ready to cook..."
                                              class="w-full text-xs sm:text-sm rounded-modern border border-gray-300 p-2.5 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary"></textarea>
                                </div>
                            </div>

                            <!-- 3. Call To Action -->
                            <div class="p-5 rounded-modern-xl bg-gray-50 border border-gray-200 space-y-4">
                                <div class="border-b border-gray-200 pb-2">
                                    <h4 class="text-xs font-extrabold text-brand-dark uppercase tracking-wider">
                                        3. Call To Action (Tombol Aksi)
                                    </h4>
                                </div>

                                <!-- Primary CTA -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-brand-dark mb-1">
                                            Primary CTA (Text)
                                        </label>
                                        <input type="text" 
                                               x-model="draftForm.primary_cta_text" 
                                               required
                                               placeholder="Belanja Sekarang"
                                               class="w-full text-xs rounded-modern border border-gray-300 p-2 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-brand-dark mb-1">
                                            Primary CTA (Link)
                                        </label>
                                        <input type="text" 
                                               x-model="draftForm.primary_cta_link" 
                                               required
                                               placeholder="#produk"
                                               class="w-full text-xs rounded-modern border border-gray-300 p-2 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                                    </div>
                                </div>

                                <!-- Secondary CTA -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-brand-dark mb-1">
                                            Secondary CTA (Text)
                                        </label>
                                        <input type="text" 
                                               x-model="draftForm.secondary_cta_text" 
                                               required
                                               placeholder="Lihat Produk"
                                               class="w-full text-xs rounded-modern border border-gray-300 p-2 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-brand-dark mb-1">
                                            Secondary CTA (Link)
                                        </label>
                                        <input type="text" 
                                               x-model="draftForm.secondary_cta_link" 
                                               required
                                               placeholder="#kategori"
                                               class="w-full text-xs rounded-modern border border-gray-300 p-2 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Trust Checklist (LOCKED 3 ITEMS) -->
                            <div class="p-5 rounded-modern-xl bg-gray-50 border border-gray-200 space-y-4">
                                <div class="border-b border-gray-200 pb-2 flex items-center justify-between">
                                    <div>
                                        <h4 class="text-xs font-extrabold text-brand-dark uppercase tracking-wider">
                                            4. Trust Checklist (Keunggulan Hero)
                                        </h4>
                                        <p class="text-[11px] text-gray-500">Keunggulan yang tampil di bagian bawah Hero (LOCKED 3 Slot).</p>
                                    </div>
                                    <span class="text-[10px] font-bold text-gray-500 bg-gray-200 px-2 py-0.5 rounded">
                                        3 Item Locked
                                    </span>
                                </div>

                                <div class="space-y-3">
                                    <!-- Trust Item 1 -->
                                    <div class="bg-white p-3.5 rounded-modern border border-gray-200 shadow-2xs space-y-2">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">✓</span>
                                                <span class="text-xs font-bold text-brand-dark">Trust Item 1</span>
                                            </div>
                                            <label class="flex items-center gap-1.5 cursor-pointer text-xs font-semibold">
                                                <input type="checkbox" x-model="draftForm.trust_items[0].active" class="rounded text-brand-primary focus:ring-brand-primary">
                                                <span :class="draftForm.trust_items[0].active ? 'text-emerald-700 font-bold' : 'text-gray-400'">
                                                    <span x-text="draftForm.trust_items[0].active ? 'ON' : 'OFF'"></span>
                                                </span>
                                            </label>
                                        </div>
                                        <input type="text" 
                                               x-model="draftForm.trust_items[0].text" 
                                               placeholder="100% Halal"
                                               class="w-full text-xs rounded-modern border border-gray-300 p-2 focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                                    </div>

                                    <!-- Trust Item 2 -->
                                    <div class="bg-white p-3.5 rounded-modern border border-gray-200 shadow-2xs space-y-2">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">✓</span>
                                                <span class="text-xs font-bold text-brand-dark">Trust Item 2</span>
                                            </div>
                                            <label class="flex items-center gap-1.5 cursor-pointer text-xs font-semibold">
                                                <input type="checkbox" x-model="draftForm.trust_items[1].active" class="rounded text-brand-primary focus:ring-brand-primary">
                                                <span :class="draftForm.trust_items[1].active ? 'text-emerald-700 font-bold' : 'text-gray-400'">
                                                    <span x-text="draftForm.trust_items[1].active ? 'ON' : 'OFF'"></span>
                                                </span>
                                            </label>
                                        </div>
                                        <input type="text" 
                                               x-model="draftForm.trust_items[1].text" 
                                               placeholder="Cold Chain"
                                               class="w-full text-xs rounded-modern border border-gray-300 p-2 focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                                    </div>

                                    <!-- Trust Item 3 -->
                                    <div class="bg-white p-3.5 rounded-modern border border-gray-200 shadow-2xs space-y-2">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">✓</span>
                                                <span class="text-xs font-bold text-brand-dark">Trust Item 3</span>
                                            </div>
                                            <label class="flex items-center gap-1.5 cursor-pointer text-xs font-semibold">
                                                <input type="checkbox" x-model="draftForm.trust_items[2].active" class="rounded text-brand-primary focus:ring-brand-primary">
                                                <span :class="draftForm.trust_items[2].active ? 'text-emerald-700 font-bold' : 'text-gray-400'">
                                                    <span x-text="draftForm.trust_items[2].active ? 'ON' : 'OFF'"></span>
                                                </span>
                                            </label>
                                        </div>
                                        <input type="text" 
                                               x-model="draftForm.trust_items[2].text" 
                                               placeholder="Kirim Se-Jogja"
                                               class="w-full text-xs rounded-modern border border-gray-300 p-2 focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                                    </div>
                                </div>

                                <p class="text-[11px] text-gray-500">
                                    💡 <em>Gunakan teks singkat agar tetap nyaman ditampilkan pada desktop, tablet, dan mobile. Ikon checklist terkunci secara standar.</em>
                                </p>
                            </div>

                        </div>

                        <!-- Right Column: Live Source-of-Truth Hero Preview (6 cols on lg) -->
                        <div class="lg:col-span-6 space-y-3 sticky top-4">
                            
                            <!-- Preview Device Toggle Bar -->
                            <div class="flex items-center justify-between pb-1">
                                <label class="block text-xs font-extrabold text-brand-dark">
                                    5. Live Hero Preview
                                </label>
                                
                                <!-- Device Simulator Switch -->
                                <div class="flex items-center bg-gray-100 p-0.5 rounded-modern border border-gray-200 text-xs">
                                    <button @click="previewDevice = 'desktop'" 
                                            type="button"
                                            :class="previewDevice === 'desktop' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500 hover:text-brand-dark'"
                                            class="px-2.5 py-1 rounded transition-all cursor-pointer flex items-center gap-1 text-[11px]">
                                        <span>💻 Desktop</span>
                                    </button>
                                    <button @click="previewDevice = 'tablet'" 
                                            type="button"
                                            :class="previewDevice === 'tablet' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500 hover:text-brand-dark'"
                                            class="px-2.5 py-1 rounded transition-all cursor-pointer flex items-center gap-1 text-[11px]">
                                        <span>📱 Tablet</span>
                                    </button>
                                    <button @click="previewDevice = 'mobile'" 
                                            type="button"
                                            :class="previewDevice === 'mobile' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500 hover:text-brand-dark'"
                                            class="px-2.5 py-1 rounded transition-all cursor-pointer flex items-center gap-1 text-[11px]">
                                        <span>📱 Mobile</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Live Hero Simulation Container -->
                            <div class="bg-gray-900 rounded-modern-xl p-3 flex justify-center items-center overflow-hidden border border-gray-800 shadow-inner">
                                
                                <div class="relative rounded-modern-lg overflow-hidden bg-brand-dark text-white p-5 sm:p-7 flex flex-col justify-between shadow-2xl transition-all duration-300 min-h-[420px]"
                                     :class="{
                                         'w-full': previewDevice === 'desktop',
                                         'w-[460px]': previewDevice === 'tablet',
                                         'w-[320px]': previewDevice === 'mobile'
                                     }">
                                    
                                    <!-- Background Image based on activePreviewImageIndex -->
                                    <img :src="getImageUrl(draftForm.images[activePreviewImageIndex] || draftForm.images[0] || 'images/hero-1.jpg')" 
                                         alt="Live Preview Background" 
                                         class="absolute inset-0 w-full h-full object-cover object-center">
                                    
                                    <!-- Multi-layer Gradient Overlay (Exact Source of Truth from Hero) -->
                                    <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/95 via-brand-dark/80 to-brand-dark/60 md:bg-gradient-to-r md:from-brand-dark/95 md:via-brand-dark/70 md:to-brand-dark/35"></div>
                                    <div class="absolute inset-0 bg-black/20"></div>

                                    <!-- Content Mockup -->
                                    <div class="relative z-10 space-y-3">
                                        
                                        <!-- Category Tag Pill -->
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-primary/30 border border-brand-primary/40 backdrop-blur-md text-brand-soft-green text-[10px] font-semibold shadow-xs max-w-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                                            <span class="truncate" x-text="draftForm.badge || 'Badge Teks Hero'"></span>
                                        </div>

                                        <!-- Main Heading with Highlight & Accent Underline -->
                                        <h1 class="text-lg sm:text-xl font-extrabold text-white tracking-tight leading-tight drop-shadow-sm">
                                            <span x-text="draftForm.headline_prefix || 'Headline'"></span> 
                                            <span class="text-emerald-400 underline decoration-amber-500 decoration-2 underline-offset-4"
                                                  x-text="draftForm.highlight || 'Highlight'"></span><span x-text="draftForm.headline_suffix || '.'"></span>
                                        </h1>

                                        <!-- Subheadline Description -->
                                        <p class="text-[11px] sm:text-xs text-gray-200 font-normal leading-relaxed line-clamp-3 text-shadow"
                                           x-text="draftForm.description || 'Deskripsi hero...'"></p>

                                        <!-- Call to Actions -->
                                        <div class="flex items-center gap-2 pt-1 flex-wrap">
                                            <!-- Primary CTA -->
                                            <div class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-modern text-xs font-bold text-white bg-brand-primary shadow-md">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                                </svg>
                                                <span x-text="draftForm.primary_cta_text || 'Belanja Sekarang'"></span>
                                            </div>

                                            <!-- Secondary CTA -->
                                            <div class="inline-flex items-center gap-1 px-3 py-2 rounded-modern text-xs font-semibold text-white bg-white/10 border border-white/25 backdrop-blur-md">
                                                <span x-text="draftForm.secondary_cta_text || 'Lihat Produk'"></span>
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Trust Checklist Section in Preview -->
                                        <div class="pt-3 border-t border-white/15 grid grid-cols-3 gap-2 text-white/90 text-[9px]">
                                            <template x-for="(item, tIdx) in draftForm.trust_items" :key="tIdx">
                                                <div x-show="item.active" class="flex items-center gap-1">
                                                    <div class="w-4 h-4 rounded-full bg-white/10 flex items-center justify-center text-emerald-400 shrink-0">✓</div>
                                                    <span class="truncate" x-text="item.text"></span>
                                                </div>
                                            </template>
                                        </div>

                                    </div>

                                    <!-- Slideshow Interactive Indicator Dots in Preview -->
                                    <div class="relative z-10 self-end mt-2 bg-black/40 backdrop-blur-md px-2.5 py-1 rounded-full border border-white/10 flex items-center gap-1.5">
                                        <template x-for="(img, dotIdx) in draftForm.images" :key="dotIdx">
                                            <button @click="activePreviewImageIndex = dotIdx"
                                                    type="button"
                                                    class="rounded-full transition-all cursor-pointer"
                                                    :class="activePreviewImageIndex === dotIdx ? 'w-4 h-1.5 bg-amber-400' : 'w-1.5 h-1.5 bg-white/50 hover:bg-white/80'"></button>
                                        </template>
                                    </div>

                                </div>

                            </div>

                            <div class="p-3 rounded-modern bg-gray-50 border border-gray-200 text-[11px] text-gray-500 leading-relaxed">
                                💡 <strong>Layout Locked:</strong> Struktur section hero pada website bersifat paten. Pergantian gambar slideshow tidak mempengaruhi tata letak teks dan tombol.
                            </div>

                        </div>

                    </div>

                    <!-- Modal Actions Footer -->
                    <div class="pt-5 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button @click="editorModalOpen = false" 
                                type="button" 
                                class="px-4 py-2.5 rounded-modern text-xs font-semibold text-gray-600 hover:bg-gray-100 transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-6 py-2.5 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer">
                            Simpan Draft Hero
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 4. MEDIA PICKER MODAL (z-[70] Above Editor Modal)       -->
    <!-- ======================================================= -->
    <div x-show="mediaPickerOpen" 
         x-cloak
         class="fixed inset-0 z-[70] overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        
        <!-- Backdrop -->
        <div x-show="mediaPickerOpen"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/70 backdrop-blur-xs" 
             @click="mediaPickerOpen = false"></div>

        <!-- Modal Dialog Container -->
        <div class="min-h-full flex items-center justify-center p-3 sm:p-6">
            <div x-show="mediaPickerOpen"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-modern-xl max-w-4xl w-full p-5 sm:p-7 shadow-2xl border border-gray-200 overflow-hidden my-6">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold text-brand-dark">
                            Pilih Gambar Hero
                        </h3>
                        <p class="text-xs text-gray-500">
                            <span x-text="mediaPickerTargetIndex !== null ? 'Mengganti foto slide ke-' + (mediaPickerTargetIndex + 1) : 'Menambahkan foto baru ke background slideshow.'"></span>
                        </p>
                    </div>
                    <button @click="mediaPickerOpen = false" 
                            type="button" 
                            class="p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Tabs: Media Library vs Upload -->
                <div class="flex items-center gap-2 border-b border-gray-200 pb-3 mb-5">
                    <button @click="mediaPickerTab = 'library'" 
                            type="button"
                            :class="mediaPickerTab === 'library' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer">
                        🖼 Media Library (<span x-text="mediaLibrary.length"></span> Gambar)
                    </button>
                    <button @click="mediaPickerTab = 'upload'" 
                            type="button"
                            :class="mediaPickerTab === 'upload' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer">
                        ☁ Upload Gambar
                    </button>
                </div>

                <!-- Tab 1: Media Library Grid -->
                <div x-show="mediaPickerTab === 'library'" class="space-y-4">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 max-h-[380px] overflow-y-auto p-1">
                        <template x-for="item in mediaLibrary" :key="item.id">
                            <div @click="selectedMediaItem = item" 
                                 class="group relative rounded-modern overflow-hidden border-2 cursor-pointer transition-all duration-150 p-1.5 bg-gray-50 flex flex-col justify-between"
                                 :class="selectedMediaItem?.id === item.id 
                                     ? 'border-brand-primary bg-emerald-50/40 ring-2 ring-brand-primary/30' 
                                     : 'border-gray-200 hover:border-gray-300'">
                                
                                <!-- Image Preview -->
                                <div class="relative aspect-[16/9] w-full rounded overflow-hidden bg-brand-dark mb-2">
                                    <img :src="getImageUrl(item.path)" :alt="item.title" class="w-full h-full object-cover">
                                    
                                    <!-- Selected Checkmark Overlay -->
                                    <template x-if="selectedMediaItem?.id === item.id">
                                        <div class="absolute inset-0 bg-brand-primary/40 backdrop-blur-xs flex items-center justify-center text-white">
                                            <div class="w-7 h-7 rounded-full bg-brand-primary text-white flex items-center justify-center font-bold text-sm shadow-md">
                                                ✓
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Metadata -->
                                <div class="space-y-0.5 text-left">
                                    <h5 class="text-[11px] font-bold text-brand-dark truncate" x-text="item.filename"></h5>
                                    <div class="flex items-center justify-between text-[10px] text-gray-500 font-mono">
                                        <span x-text="item.resolution"></span>
                                        <span x-text="item.size"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-modern border border-gray-200 text-xs text-gray-500 flex items-center justify-between">
                        <span>Pilih gambar beresolusi 1920 × 1080 px (16:9) untuk hasil optimal.</span>
                        <template x-if="selectedMediaItem">
                            <span class="font-bold text-brand-primary" x-text="'Terpilih: ' + selectedMediaItem.filename"></span>
                        </template>
                    </div>
                </div>

                <!-- Tab 2: Upload Dropzone (HTML5 File API + URL.createObjectURL) -->
                <div x-show="mediaPickerTab === 'upload'" class="space-y-4">
                    
                    <!-- Drag & Drop Area -->
                    <div @dragover.prevent="isDragging = true" 
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="isDragging = false; if ($event.dataTransfer.files.length > 0) handleFileSelected($event.dataTransfer.files[0])"
                         @click="$refs.nativeFileInput.click()"
                         class="relative rounded-modern-xl border-2 border-dashed p-8 text-center cursor-pointer transition-all flex flex-col items-center justify-center space-y-3"
                         :class="isDragging ? 'border-brand-primary bg-emerald-50/50 scale-[0.99]' : 'border-gray-300 hover:border-brand-primary/60 bg-gray-50/60'">
                        
                        <input type="file" 
                               x-ref="nativeFileInput" 
                               @change="if ($event.target.files.length > 0) handleFileSelected($event.target.files[0])"
                               class="hidden" 
                               accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        
                        <div class="w-12 h-12 rounded-full bg-brand-soft-green text-brand-primary flex items-center justify-center shadow-xs">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>

                        <div>
                            <h4 class="text-xs sm:text-sm font-bold text-brand-dark">
                                Drag & Drop gambar di sini atau klik untuk memilih
                            </h4>
                            <p class="text-[11px] text-gray-500 mt-0.5">
                                Mendukung format JPG, PNG, dan WebP (Maksimal 500 KB disarankan)
                            </p>
                        </div>
                    </div>

                    <!-- Uploaded Mock Preview Box -->
                    <template x-if="uploadedMockImage">
                        <div class="p-3.5 rounded-modern bg-emerald-50/70 border border-emerald-300 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-16 aspect-[16/9] rounded overflow-hidden bg-brand-dark shrink-0 border border-gray-200">
                                    <img :src="uploadedMockImage.path" alt="Uploaded" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h5 class="text-xs font-bold text-brand-dark" x-text="uploadedMockImage.filename"></h5>
                                    <p class="text-[10px] text-gray-500 font-mono" x-text="uploadedMockImage.resolution + ' • 16:9 • ' + uploadedMockImage.size"></p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                ✓ Siap Digunakan
                            </span>
                        </div>
                    </template>

                    <!-- Guidance & Visual Safe Area -->
                    <div class="p-4 bg-gray-50 rounded-modern border border-gray-200 text-xs text-gray-600 space-y-1.5">
                        <div class="flex items-center gap-1.5 font-bold text-brand-dark">
                            <span>💡 Panduan Crop & Safe Area:</span>
                        </div>
                        <p class="text-[11px] leading-relaxed">
                            Gunakan foto landscape dengan objek utama tidak terlalu menempel pada tepi gambar agar tetap aman saat crop desktop, tablet, dan mobile.
                        </p>
                    </div>

                </div>

                <!-- Media Picker Actions -->
                <div class="pt-4 mt-5 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button @click="mediaPickerOpen = false" 
                            type="button" 
                            class="px-4 py-2 rounded-modern text-xs font-semibold text-gray-600 hover:bg-gray-100 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button @click="applySelectedMedia()" 
                            type="button" 
                            class="px-5 py-2 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm transition-colors cursor-pointer">
                        <span x-text="mediaPickerTab === 'upload' ? 'Gunakan Gambar' : 'Pilih Gambar'"></span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 5. ACTIVATE CONFIRMATION MODAL                          -->
    <!-- ======================================================= -->
    <div x-show="activateModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        
        <div class="fixed inset-0 bg-black/50 backdrop-blur-xs" @click="activateModalOpen = false"></div>

        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white rounded-modern-xl max-w-sm w-full p-6 shadow-xl border border-gray-200 text-center space-y-4">
                
                <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <div class="space-y-1">
                    <h3 class="text-base font-bold text-brand-dark">Gunakan Hero ini di website?</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Hero yang sedang aktif sebelumnya akan digantikan oleh <strong class="text-brand-dark" x-text="selectedDraft?.name"></strong>.
                    </p>
                </div>

                <div class="pt-3 flex items-center justify-center gap-3">
                    <button @click="activateModalOpen = false" 
                            type="button" 
                            class="px-4 py-2 rounded-modern text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button @click="confirmActivate()" 
                            type="button" 
                            class="px-4 py-2 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark transition-colors cursor-pointer shadow-sm">
                        Jadikan Aktif
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 6. DELETE CONFIRMATION MODAL                            -->
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
                    <h3 class="text-base font-bold text-brand-dark">Hapus Draft Hero ini?</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Draft <strong class="text-brand-dark" x-text="selectedDraft?.name"></strong> akan dihapus. Anda dapat membuat draft baru setelah slot tersedia.
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
                        Hapus Draft
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 7. TOAST NOTIFICATION                                   -->
    <!-- ======================================================= -->
    <div x-show="toastVisible" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 bg-brand-dark text-white px-4 py-3 rounded-modern-lg shadow-xl border border-white/10 flex items-center gap-2.5 text-xs font-semibold">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        <span x-text="toastMessage"></span>
    </div>

</div>
@endsection
