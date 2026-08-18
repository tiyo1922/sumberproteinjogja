@extends('layouts.admin', [
    'title' => 'Kategori Produk',
    'pageTitle' => 'Master Kategori Produk'
])

@section('content')
<div class="space-y-8" 
     x-data="{
         categorySection: {{ json_encode($categorySection) }},
         categories: {{ json_encode($categories) }},
         products: {{ json_encode($products) }},
         mediaLibrary: {{ json_encode($mediaLibrary) }},
         editorModalOpen: false,
         mediaPickerOpen: false,
         deleteModalOpen: false,
         deleteBlocked: false,
         deleteBlockMessage: '',
         isEditing: false,
         toastMessage: '',
         toastVisible: false,
         previewDevice: 'desktop', // 'desktop' | 'mobile'
         mediaTab: 'library', // 'library' | 'upload'
         selectedMedia: null,
         uploadedFile: null,
         uploadedPreviewUrl: null,
         
         colorOptions: [
             { id: 'orange', name: 'Oranye (Warm)', class: 'bg-orange-100 text-orange-800 border-orange-300' },
             { id: 'yellow', name: 'Kuning (Gold)', class: 'bg-yellow-100 text-yellow-800 border-yellow-300' },
             { id: 'blue', name: 'Biru (Fresh Ocean)', class: 'bg-blue-100 text-blue-800 border-blue-300' },
             { id: 'green', name: 'Hijau (Organik)', class: 'bg-emerald-100 text-emerald-800 border-emerald-300' },
             { id: 'purple', name: 'Ungu (Premium)', class: 'bg-purple-100 text-purple-800 border-purple-300' },
             { id: 'red', name: 'Merah (Bold)', class: 'bg-rose-100 text-rose-800 border-rose-300' },
             { id: 'teal', name: 'Teal (Clean)', class: 'bg-teal-100 text-teal-800 border-teal-300' }
         ],
         
         form: {
             id: null,
             name: '',
             slug: '',
             subtitle: '',
             badge: 'Sertifikasi Halal',
             color: 'orange',
             image: 'images/cat-daging.jpg',
             description: '',
             order: 1,
             status: 'active_landing', // 'active_landing' | 'active_catalog' | 'inactive'
             is_system: false,
         },
         
         selectedCategory: null,
         
         showToast(msg) {
             this.toastMessage = msg;
             this.toastVisible = true;
             setTimeout(() => { this.toastVisible = false; }, 3000);
         },
         
         getStatusLabel(status) {
             if (status === 'active_landing' || status === 'Aktif') return 'Aktif (LP & Katalog)';
             if (status === 'active_catalog') return 'Aktif (Hanya Katalog)';
             return 'Nonaktif (Disembunyikan)';
         },
         
         getActiveProductCount(categoryId) {
             if (!categoryId) return 0;
             return this.products.filter(p => p.category_id == categoryId && p.status === 'Aktif').length;
         },
         
         getTotalProductCount(categoryId) {
             if (!categoryId) return 0;
             return this.products.filter(p => p.category_id == categoryId).length;
         },
         
         saveSectionSettings() {
             this.showToast('Pengaturan Header Section Kategori berhasil disimpan!');
         },
         
         openCreateModal() {
             this.isEditing = false;
             const newId = this.categories.length > 0 ? Math.max(...this.categories.map(c => c.id)) + 1 : 1;
             this.form = {
                 id: newId,
                 name: '',
                 slug: '',
                 subtitle: '',
                 badge: 'Sertifikasi Halal',
                 color: 'orange',
                 image: 'images/cat-daging.jpg',
                 description: '',
                 order: this.categories.length + 1,
                 status: 'active_landing',
                 is_system: false,
             };
             this.editorModalOpen = true;
         },
         
         openEditModal(cat) {
             this.isEditing = true;
             this.form = JSON.parse(JSON.stringify(cat));
             if (!this.form.badge) this.form.badge = 'Sertifikasi Halal';
             if (!this.form.status) this.form.status = 'active_landing';
             this.editorModalOpen = true;
         },
         
         autoSlug() {
             if (this.form.is_system) return;
             this.form.slug = this.form.name.toLowerCase()
                 .replace(/[^a-z0-9\s-]/g, '')
                 .trim()
                 .replace(/\s+/g, '-');
         },
         
         openMediaPicker() {
             this.mediaTab = 'library';
             this.selectedMedia = this.mediaLibrary.find(m => m.path === this.form.image) || this.mediaLibrary[0] || null;
             this.uploadedFile = null;
             this.uploadedPreviewUrl = null;
             this.mediaPickerOpen = true;
         },
         
         selectMedia(media) {
             this.selectedMedia = media;
         },
         
         confirmMediaSelection() {
             if (this.mediaTab === 'library' && this.selectedMedia) {
                 this.form.image = this.selectedMedia.path;
                 this.mediaPickerOpen = false;
                 this.showToast('Gambar dipilih dari Media Library!');
             } else if (this.mediaTab === 'upload' && this.uploadedPreviewUrl) {
                 this.form.image = this.uploadedPreviewUrl;
                 this.mediaPickerOpen = false;
                 this.showToast('Gambar hasil upload berhasil digunakan!');
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
         
         saveCategory() {
             if (!this.form.name.trim()) {
                 alert('Nama kategori wajib diisi.');
                 return;
             }
             if (!this.form.slug.trim()) {
                 this.autoSlug();
             }
             
             if (this.isEditing) {
                 const idx = this.categories.findIndex(c => c.id === this.form.id);
                 if (idx !== -1) {
                     this.categories[idx] = JSON.parse(JSON.stringify(this.form));
                 }
                 // Sync category name in products array
                 this.products.forEach(p => {
                     if (p.category_id === this.form.id) {
                         p.category = this.form.name;
                     }
                 });
                 this.showToast('Kategori ' + this.form.name + ' berhasil diperbarui!');
             } else {
                 this.categories.push(JSON.parse(JSON.stringify(this.form)));
                 this.showToast('Kategori baru ' + this.form.name + ' berhasil ditambahkan!');
             }
             this.editorModalOpen = false;
         },
         
         toggleStatus(cat) {
             if (cat.is_system) return;
             if (cat.status === 'active_landing' || cat.status === 'Aktif') {
                 cat.status = 'active_catalog';
             } else if (cat.status === 'active_catalog') {
                 cat.status = 'inactive';
             } else {
                 cat.status = 'active_landing';
             }
             this.showToast('Status ' + cat.name + ' diubah menjadi ' + this.getStatusLabel(cat.status));
         },
         
         openDelete(cat) {
             if (cat.is_system) return;
             this.selectedCategory = cat;
             const usedCount = this.getTotalProductCount(cat.id);
             
             if (usedCount > 0) {
                 this.deleteBlocked = true;
                 this.deleteBlockMessage = 'Kategori ini masih digunakan oleh ' + usedCount + ' produk. Silakan pindahkan produk ke kategori lain terlebih dahulu sebelum menghapus kategori.';
             } else {
                 this.deleteBlocked = false;
                 this.deleteBlockMessage = '';
             }
             this.deleteModalOpen = true;
         },
         
         confirmDelete() {
             if (this.selectedCategory && !this.deleteBlocked) {
                 this.categories = this.categories.filter(c => c.id !== this.selectedCategory.id);
                 this.deleteModalOpen = false;
                 this.showToast('Kategori ' + this.selectedCategory.name + ' telah dihapus.');
                 this.selectedCategory = null;
             }
         },
         
         getImageUrl(path) {
             if (!path) return '/images/cat-daging.jpg';
             if (path.startsWith('blob:') || path.startsWith('http')) return path;
             return path.startsWith('/') ? path : '/' + path;
         }
     }">
    
    <!-- ======================================================= -->
    <!-- 1. HEADER & INTRO CARD                                  -->
    <!-- ======================================================= -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-8 shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-brand-dark tracking-tight">
                        Master Kategori Produk
                    </h2>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                        <span>MASTER DATA &amp; STABLE RELATION</span>
                    </span>
                    <span class="text-xs text-gray-500 font-medium">
                        • 3 Pilihan Status: LP &amp; Katalog / Hanya Katalog / Nonaktif
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 leading-relaxed max-w-3xl">
                    Kategori adalah Master Data dengan identity stabil (<code class="text-emerald-700 font-bold font-mono">category_id</code>). Tab pada Katalog Lengkap, filter produk, dan tombol <em>Lihat Varian</em> otomatis terhubung ke master data ini.
                </p>
            </div>

            <!-- Create Action Button -->
            <div class="flex items-center gap-3 shrink-0">
                <button @click="openCreateModal()" 
                        type="button"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern font-bold text-xs sm:text-sm text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer">
                    <span class="text-base leading-none">＋</span>
                    <span>Tambah Kategori Baru</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 2. SECTION KATEGORI SETTINGS (DYNAMIC SECTION HEADER)   -->
    <!-- ======================================================= -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-7 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div class="flex items-center gap-2">
                <span class="text-lg">⚙️</span>
                <div>
                    <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                        Pengaturan Section Kategori (Landing Page Header)
                    </h3>
                    <p class="text-xs text-gray-500">
                        Kelola label badge, judul utama (headline), dan deskripsi pengantar pada section <code>&lt;section id="kategori"&gt;</code>.
                    </p>
                </div>
            </div>

            <button @click="saveSectionSettings()" 
                    type="button" 
                    class="px-4 py-2 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark shadow-2xs transition-all cursor-pointer">
                Simpan Header Section
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <!-- Label Badge -->
            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-brand-dark mb-1">
                    Label Badge Section
                </label>
                <input type="text" 
                       x-model="categorySection.label" 
                       placeholder="Contoh: Kategori Utama"
                       class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-semibold text-brand-primary">
            </div>

            <!-- Judul Utama (Heading) -->
            <div class="md:col-span-4">
                <label class="block text-xs font-bold text-brand-dark mb-1">
                    Judul Utama (Heading)
                </label>
                <input type="text" 
                       x-model="categorySection.title" 
                       placeholder="Contoh: Mau Masak Apa Hari Ini?"
                       class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-extrabold text-brand-dark">
            </div>

            <!-- Deskripsi Pengantar -->
            <div class="md:col-span-5">
                <label class="block text-xs font-bold text-brand-dark mb-1">
                    Deskripsi Pengantar
                </label>
                <textarea x-model="categorySection.subtitle" 
                          rows="2" 
                          placeholder="Pilih bahan masak sesuai kebutuhanmu..."
                          class="w-full text-xs rounded-modern border border-gray-300 p-2 bg-white leading-relaxed"></textarea>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 3. DAFTAR MASTER KATEGORI GRID                          -->
    <!-- ======================================================= -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                Daftar Master Kategori (<span x-text="categories.length"></span> Record)
            </h3>
            <span class="text-xs text-gray-500 font-medium">
                Variation Count dihitung otomatis dari total produk aktif per kategori.
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="(cat, idx) in categories" :key="cat.id">
                <div class="bg-white rounded-modern-xl border border-gray-200/90 overflow-hidden shadow-sm hover:shadow-card transition-all flex flex-col justify-between"
                     :class="{'opacity-75 bg-gray-50/70': cat.status === 'inactive' || cat.status === 'Nonaktif'}">
                    
                    <div>
                        <!-- 4:3 Image Area -->
                        <div class="relative aspect-[4/3] w-full bg-gray-100 overflow-hidden">
                            <img :src="getImageUrl(cat.image)" :alt="cat.name" class="w-full h-full object-cover">
                            
                            <!-- Top-Left Badge -->
                            <div class="absolute top-3 left-3 z-10">
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold bg-white/95 backdrop-blur-md text-brand-dark shadow-sm border border-black/5"
                                      x-text="cat.badge || 'Sertifikasi Halal'">
                                </span>
                            </div>

                            <!-- Bottom-Right Variation Count Pill (DYNAMIC FROM ACTIVE PRODUCTS) -->
                            <div class="absolute bottom-3 right-3 z-10">
                                <span class="inline-block px-2.5 py-0.5 rounded-md text-xs font-bold bg-brand-primary text-white shadow-sm"
                                      x-text="getActiveProductCount(cat.id) + '+ Variasi'">
                                </span>
                            </div>
                        </div>

                        <!-- Card Body Content -->
                        <div class="p-5 space-y-1.5 text-left">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-mono text-[10px] font-bold" x-text="'ID: ' + cat.id"></span>
                                    <h3 class="text-base sm:text-lg font-extrabold text-brand-dark leading-snug" x-text="cat.name"></h3>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border shrink-0"
                                      :class="{
                                          'bg-emerald-50 text-emerald-800 border-emerald-300': cat.status === 'active_landing' || cat.status === 'Aktif',
                                          'bg-sky-50 text-sky-800 border-sky-300': cat.status === 'active_catalog',
                                          'bg-gray-100 text-gray-600 border-gray-300': cat.status === 'inactive' || cat.status === 'Nonaktif'
                                      }"
                                      x-text="getStatusLabel(cat.status)">
                                </span>
                            </div>

                            <!-- Subtitle in Brand Green -->
                            <p class="text-xs sm:text-sm font-medium text-brand-primary line-clamp-1"
                               x-text="cat.subtitle || 'Slice, Sengkel, Ribeye & Giling'">
                            </p>

                            <!-- Description in Gray -->
                            <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed"
                               x-text="cat.description || 'Daging sapi segar & frozen potongan higienis tanpa pengawet.'">
                            </p>

                            <!-- Product Relation Counter Info -->
                            <div class="pt-2 flex items-center gap-2 text-[11px] text-gray-400">
                                <span>📦 Terhubung: <strong class="text-brand-dark" x-text="getTotalProductCount(cat.id) + ' Produk'"></strong></span>
                                <span>•</span>
                                <span class="text-emerald-700 font-medium" x-text="getActiveProductCount(cat.id) + ' Aktif di LP'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="p-4 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between gap-2">
                        <div class="text-[11px] text-gray-400 font-mono">
                            Slug: <span class="text-gray-600 font-medium" x-text="cat.slug"></span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button @click="openEditModal(cat)" 
                                    type="button"
                                    class="px-3 py-1.5 rounded-modern text-xs font-bold text-brand-primary bg-brand-soft-green hover:bg-emerald-100 transition-colors cursor-pointer">
                                Edit
                            </button>

                            <button @click="toggleStatus(cat)" 
                                    type="button"
                                    class="p-1.5 rounded-modern text-xs font-semibold text-gray-500 hover:bg-gray-200 transition-colors cursor-pointer"
                                    :title="'Ubah Status: ' + getStatusLabel(cat.status)">
                                <span x-text="cat.status === 'active_landing' || cat.status === 'Aktif' ? '🌐' : (cat.status === 'active_catalog' ? '📑' : '👁️‍🗨️')"></span>
                            </button>

                            <button @click="openDelete(cat)" 
                                    type="button"
                                    class="p-1.5 rounded-modern text-rose-500 hover:bg-rose-50 transition-colors cursor-pointer" 
                                    title="Hapus Kategori">
                                🗑
                            </button>
                        </div>
                    </div>

                </div>
            </template>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 4. MODAL EDITOR KATEGORI (Form + SHARED COMPONENT PREVIEW) -->
    <!-- ======================================================= -->
    <div x-show="editorModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="editorModalOpen = false"></div>

        <div class="min-h-full flex items-center justify-center p-3 sm:p-6">
            <div class="relative bg-white rounded-modern-xl max-w-4xl w-full p-6 sm:p-8 shadow-2xl border border-gray-200 overflow-hidden my-6">
                
                <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 font-mono text-xs font-bold" x-text="'ID: ' + form.id"></span>
                        <div>
                            <h3 class="text-base sm:text-lg font-extrabold text-brand-dark"
                                x-text="isEditing ? 'Edit Kategori: ' + form.name : 'Tambah Kategori Baru'">
                            </h3>
                            <p class="text-xs text-gray-500">Master entity dengan relational ID stabil untuk Landing Page &amp; Katalog Produk.</p>
                        </div>
                    </div>
                    <button @click="editorModalOpen = false" 
                            type="button" 
                            class="p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                        ✕
                    </button>
                </div>

                <form @submit.prevent="saveCategory()" class="space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
                        
                        <!-- Left Form (7 cols on md) -->
                        <div class="md:col-span-7 space-y-4">
                            
                            <!-- Nama Kategori -->
                            <div>
                                <label class="block text-xs font-bold text-brand-dark mb-1">
                                    Nama Kategori <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="form.name" 
                                       @input="autoSlug()"
                                       required
                                       placeholder="Contoh: Daging Sapi"
                                       class="w-full text-xs sm:text-sm rounded-modern border border-gray-300 p-2.5 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary font-medium">
                            </div>

                            <!-- Slug URL (Auto Generated) -->
                            <div>
                                <label class="block text-xs font-bold text-brand-dark mb-1">
                                    Slug Kategori (Otomatis dari Nama)
                                </label>
                                <input type="text" 
                                       x-model="form.slug" 
                                       placeholder="daging-sapi"
                                       class="w-full text-xs rounded-modern border border-gray-300 p-2 bg-gray-50 font-mono text-gray-600">
                            </div>

                            <!-- Subtitle (Teks Hijau) & Badge Teks -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-brand-dark mb-1">
                                        Subtitle (Teks Hijau di Kartu)
                                    </label>
                                    <input type="text" 
                                           x-model="form.subtitle" 
                                           placeholder="Contoh: Slice, Sengkel, Ribeye & Giling"
                                           class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-brand-dark mb-1">
                                        Teks Badge (Top-Left Image)
                                    </label>
                                    <input type="text" 
                                           x-model="form.badge" 
                                           placeholder="Contoh: Sertifikasi Halal"
                                           class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white">
                                </div>
                            </div>

                            <!-- Status Kategori (3 PILIHAN SESUAI SPESIFIKASI PROMPT REVISI) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-brand-dark mb-1">
                                        Status Kategori <span class="text-rose-500">*</span>
                                    </label>
                                    <select x-model="form.status" 
                                            class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-semibold">
                                        <option value="active_landing">Aktif (Tampil di Landing Page &amp; Tab Katalog)</option>
                                        <option value="active_catalog">Aktif (Hanya Tampil di Tab Katalog)</option>
                                        <option value="inactive">Nonaktif (Disembunyikan)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-brand-dark mb-1">
                                        Jumlah Produk Terhubung (Otomatis)
                                    </label>
                                    <div class="w-full text-xs rounded-modern border border-gray-200 p-2.5 bg-gray-50 text-gray-700 font-bold flex items-center justify-between">
                                        <span x-text="getActiveProductCount(form.id) + ' Produk Aktif'"></span>
                                        <span class="text-[10px] text-gray-400 font-normal">Dynamic count</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Deskripsi Singkat -->
                            <div>
                                <label class="block text-xs font-bold text-brand-dark mb-1">
                                    Deskripsi Singkat <span class="text-rose-500">*</span>
                                </label>
                                <textarea x-model="form.description" 
                                          rows="2" 
                                          required
                                          placeholder="Contoh: Daging sapi segar & frozen potongan higienis tanpa pengawet."
                                          class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white leading-relaxed"></textarea>
                            </div>

                            <!-- Gambar Cover via Global Media Picker -->
                            <div class="p-4 rounded-modern bg-gray-50 border border-gray-200 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-brand-dark">
                                        Foto Cover Kategori (Rasio 4:3)
                                    </label>
                                    <span class="text-[11px] font-semibold text-emerald-700">Global Media Picker</span>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="w-20 aspect-[4/3] rounded-modern overflow-hidden bg-gray-100 shrink-0 border border-gray-300 shadow-2xs">
                                        <img :src="getImageUrl(form.image)" alt="Category Cover" class="w-full h-full object-cover">
                                    </div>
                                    <div class="space-y-2">
                                        <button @click="openMediaPicker()" 
                                                type="button" 
                                                class="px-4 py-2 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark shadow-2xs transition-all cursor-pointer inline-flex items-center gap-1.5">
                                            <span>🖼️</span>
                                            <span>Pilih dari Media Picker</span>
                                        </button>
                                        <p class="text-[11px] text-gray-500 font-mono truncate max-w-xs" x-text="form.image"></p>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-gray-200 text-[11px] text-gray-500 space-y-1">
                                    <p class="font-bold text-gray-700">Rekomendasi Gambar Kategori:</p>
                                    <p>800 × 600 px • Rasio 4:3 • JPG / WebP • Disarankan ≤ 300 KB</p>
                                </div>
                            </div>

                        </div>

                        <!-- Right: SHARED COMPONENT REAL LANDING PAGE PREVIEW (5 cols on md) -->
                        <div class="md:col-span-5 space-y-3 sticky top-4">
                            
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

                            <!-- Section Header Context Preview -->
                            <div class="p-3 bg-brand-cream/60 rounded-modern border border-gray-200 text-center space-y-0.5">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-semibold bg-brand-soft-green text-brand-primary"
                                      x-text="categorySection.label || 'Kategori Utama'"></span>
                                <h4 class="font-bold text-xs text-brand-dark" x-text="categorySection.title || 'Mau Masak Apa Hari Ini?'"></h4>
                            </div>

                            <!-- Status Notice In Real Preview -->
                            <div x-show="form.status === 'active_catalog'" class="p-2.5 rounded-modern bg-sky-50 border border-sky-200 text-sky-800 text-[11px] font-medium text-center">
                                ℹ️ <strong>Mode Tab Katalog Saja:</strong> Kategori ini hanya tampil sebagai Tab di Katalog Lengkap (tidak tampil sebagai card pada section Landing Page).
                            </div>
                            <div x-show="form.status === 'inactive'" class="p-2.5 rounded-modern bg-gray-100 border border-gray-300 text-gray-600 text-[11px] font-medium text-center">
                                🚫 <strong>Disembunyikan:</strong> Kategori tidak tampil pada Landing Page maupun Tab Katalog Lengkap.
                            </div>

                            <!-- Real Shared Category Card Component Container -->
                            <div class="bg-gray-100/70 p-4 sm:p-5 rounded-modern-xl border border-gray-200 flex justify-center">
                                <div class="w-full transition-all duration-200"
                                     :class="previewDevice === 'mobile' ? 'max-w-[240px]' : 'max-w-[300px]'">
                                    
                                    <!-- SHARED COMPONENT INCLUSION (100% Shared Markup with Landing Page) -->
                                    @include('components.category-card-item', ['isLivePreview' => true])

                                </div>
                            </div>

                            <p class="text-[11px] text-gray-400 text-center">
                                Preview merender shared component <code class="font-mono text-emerald-700 font-bold">components.category-card-item</code> dengan variasi count dinamis.
                            </p>

                        </div>

                    </div>

                    <!-- Actions Footer -->
                    <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button @click="editorModalOpen = false" 
                                type="button" 
                                class="px-4 py-2.5 rounded-modern text-xs font-semibold text-gray-600 hover:bg-gray-100 transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-6 py-2.5 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm transition-all cursor-pointer">
                            Simpan Kategori
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 5. GLOBAL MEDIA PICKER MODAL                            -->
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
                            <h3 class="text-base font-extrabold text-brand-dark">Pilih Gambar dari Media Picker</h3>
                            <p class="text-xs text-gray-500">Pilih dari pustaka media atau unggah gambar cover baru.</p>
                        </div>
                    </div>
                    <button @click="mediaPickerOpen = false" type="button" class="p-1.5 text-gray-400 hover:text-gray-700 rounded-lg cursor-pointer">✕</button>
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
                                 class="group relative aspect-[4/3] rounded-modern overflow-hidden border-2 transition-all cursor-pointer bg-brand-dark"
                                 :class="selectedMedia?.id === media.id ? 'border-brand-primary ring-2 ring-emerald-400' : 'border-gray-200 hover:border-gray-400'">
                                <img :src="getImageUrl(media.path)" :alt="media.title" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent p-2 flex flex-col justify-between">
                                    <div class="self-end" x-show="selectedMedia?.id === media.id">
                                        <span class="w-5 h-5 rounded-full bg-brand-primary text-white flex items-center justify-center text-xs font-bold shadow-sm">✓</span>
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
                            Pilih Gambar Kategori
                        </button>
                    </div>
                </div>

                <!-- Tab 2: Upload -->
                <div x-show="mediaTab === 'upload'" class="space-y-4">
                    <label class="block border-2 border-dashed border-gray-300 rounded-modern-xl p-8 text-center hover:border-brand-primary hover:bg-brand-soft-green/30 transition-all cursor-pointer"
                           @dragover.prevent="" 
                           @drop.prevent="handleFileUpload($event)">
                        <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="handleFileUpload($event)">
                        <div class="space-y-2">
                            <span class="text-3xl">📤</span>
                            <p class="text-xs font-bold text-brand-dark">Tarik & Lepaskan gambar ke sini, atau klik untuk memilih file</p>
                            <p class="text-[11px] text-gray-400">Mendukung JPG, PNG, WebP (Rekomendasi 800 × 600 px ≤ 300 KB)</p>
                        </div>
                    </label>

                    <template x-if="uploadedPreviewUrl">
                        <div class="p-3 bg-emerald-50/50 rounded-modern border border-emerald-200 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-14 aspect-[4/3] rounded overflow-hidden bg-brand-dark border border-gray-200">
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

    <!-- ======================================================= -->
    <!-- 6. DELETE CONFIRMATION & SAFETY VALIDATION MODAL        -->
    <!-- ======================================================= -->
    <div x-show="deleteModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-xs" @click="deleteModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white rounded-modern-xl max-w-md w-full p-6 shadow-xl border border-gray-200 text-center space-y-4">
                
                <template x-if="deleteBlocked">
                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mx-auto text-xl font-bold">⚠️</div>
                        <div class="space-y-1">
                            <h3 class="text-base font-bold text-brand-dark">Kategori Tidak Dapat Dihapus</h3>
                            <p class="text-xs text-gray-600 leading-relaxed" x-text="deleteBlockMessage"></p>
                        </div>
                        <div class="pt-3">
                            <button @click="deleteModalOpen = false" type="button" class="w-full px-4 py-2.5 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark cursor-pointer">
                                Mengerti &amp; Kembali
                            </button>
                        </div>
                    </div>
                </template>

                <template x-if="!deleteBlocked">
                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto text-xl">🗑</div>
                        <div class="space-y-1">
                            <h3 class="text-base font-bold text-brand-dark">Hapus Kategori?</h3>
                            <p class="text-xs text-gray-500 leading-relaxed">
                                Kategori <strong class="text-brand-dark" x-text="selectedCategory?.name"></strong> (tidak memiliki produk terkait) akan dihapus dari daftar master kategori.
                            </p>
                        </div>
                        <div class="pt-3 flex items-center justify-center gap-3">
                            <button @click="deleteModalOpen = false" type="button" class="px-4 py-2 rounded-modern text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 cursor-pointer">Batal</button>
                            <button @click="confirmDelete()" type="button" class="px-4 py-2 rounded-modern text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 cursor-pointer">Hapus Kategori</button>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </div>

    <!-- 7. Toast Notification -->
    <div x-show="toastVisible" 
         x-cloak
         x-transition
         class="fixed bottom-6 right-6 z-50 bg-brand-dark text-white px-4 py-3 rounded-modern-lg shadow-xl border border-white/10 flex items-center gap-2.5 text-xs font-semibold">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        <span x-text="toastMessage"></span>
    </div>

</div>
@endsection
