@extends('layouts.admin', [
    'title' => 'Knowledge & Tips',
    'pageTitle' => 'Knowledge & Tips'
])

@section('content')
<div class="space-y-6"
     x-data="{
         articles: {{ json_encode($articles) }},
         categories: {{ json_encode($knowledgeCategories) }},
         mediaLibrary: {{ json_encode($mediaLibrary) }},
         knowledgeSection: {{ json_encode($knowledgeSection ?? [
             'label' => 'Edukasi & Inspirasi Dapur',
             'title' => 'Dapur & Knowledge',
             'subtitle' => 'Panduan praktis seputar penanganan daging, thawing, penyimpanan frozen food, hingga tips memasak harian keluarga di Yogyakarta.'
         ]) }},
         activeMainTab: 'articles', // 'articles' | 'categories'
         editorModalOpen: false,
         categoryModalOpen: false,
         mediaPickerOpen: false,
         previewModalOpen: false,
         deleteModalOpen: false,
         deleteCategoryModalOpen: false,
         isEditing: false,
         isEditingCategory: false,
         toastMessage: '',
         toastVisible: false,
         searchQuery: '',
         selectedCategoryFilter: 'all',
         previewDevice: 'desktop', // 'desktop' | 'tablet' | 'mobile'
         mediaTab: 'library', // 'library' | 'upload'
         selectedMedia: null,
         uploadedFile: null,
         uploadedPreviewUrl: null,
         previewIsExpanded: false,
         
         colorOptions: [
             { id: 'blue', name: 'Biru (Edukasi)', class: 'bg-blue-100 text-blue-800 border-blue-300' },
             { id: 'green', name: 'Hijau (Tips/Fresh)', class: 'bg-emerald-100 text-emerald-800 border-emerald-300' },
             { id: 'purple', name: 'Ungu (Produk)', class: 'bg-purple-100 text-purple-800 border-purple-300' },
             { id: 'orange', name: 'Oranye (Resep)', class: 'bg-orange-100 text-orange-800 border-orange-300' },
             { id: 'yellow', name: 'Kuning (Belanja)', class: 'bg-yellow-100 text-yellow-800 border-yellow-300' },
             { id: 'red', name: 'Merah (Protein)', class: 'bg-rose-100 text-rose-800 border-rose-300' },
             { id: 'teal', name: 'Teal (Higienis)', class: 'bg-teal-100 text-teal-800 border-teal-300' }
         ],
         
         form: {
             id: null,
             title: '',
             slug: '',
             category: 'Tips Penyimpanan',
             status: 'Published',
             published_at: '17 Agustus 2026',
             image: 'images/know-thawing.jpg',
             excerpt: '',
             content: '',
         },
         
         categoryForm: {
             id: null,
             name: '',
             color: 'blue',
             status: 'Aktif',
             articles_count: 0
         },
         
         selectedArticle: null,
         selectedCategoryItem: null,
         
         showToast(msg) {
             this.toastMessage = msg;
             this.toastVisible = true;
             setTimeout(() => { this.toastVisible = false; }, 3000);
         },
         
         getColorClass(color) {
             const map = {
                 orange: 'bg-orange-50 text-orange-800 border-orange-200',
                 yellow: 'bg-yellow-50 text-yellow-900 border-yellow-200',
                 blue: 'bg-blue-50 text-blue-800 border-blue-200',
                 green: 'bg-emerald-50 text-emerald-800 border-emerald-200',
                 purple: 'bg-purple-50 text-purple-800 border-purple-200',
                 red: 'bg-rose-50 text-rose-800 border-rose-200',
                 teal: 'bg-teal-50 text-teal-800 border-teal-200'
             };
             return map[color] || map.green;
         },
         
         getCategoryColor(catName) {
             const found = this.categories.find(c => c.name === catName);
             return found ? found.color : 'green';
         },
         
         get activeCategories() {
             return this.categories.filter(c => c.status === 'Aktif');
         },
         
         get filteredArticles() {
             return this.articles.filter(a => {
                 const matchCat = this.selectedCategoryFilter === 'all' || a.category === this.selectedCategoryFilter;
                 const matchSearch = !this.searchQuery.trim() || 
                     a.title.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                     a.category.toLowerCase().includes(this.searchQuery.toLowerCase());
                 return matchCat && matchSearch;
             });
         },
         
         openCreateModal() {
             this.isEditing = false;
             this.previewIsExpanded = false;
             const defaultCat = this.activeCategories[0]?.name || 'Tips Penyimpanan';
             this.form = {
                 id: Date.now(),
                 title: '',
                 slug: '',
                 category: defaultCat,
                 status: 'Published',
                 published_at: '17 Agustus 2026',
                 image: 'images/know-thawing.jpg',
                 excerpt: 'Ringkasan singkat artikel edukasi dapur untuk pembaca sebelum membuka isi lengkap...',
                 content: 'Tulis isi artikel lengkap di sini dengan paragraf terstruktur dan tips bermanfaat...',
             };
             this.editorModalOpen = true;
         },
         
         openEditModal(a) {
             this.isEditing = true;
             this.previewIsExpanded = false;
             this.form = JSON.parse(JSON.stringify(a));
             this.editorModalOpen = true;
         },
         
         openCreateCategoryModal() {
             this.isEditingCategory = false;
             this.categoryForm = {
                 id: Date.now(),
                 name: '',
                 color: 'blue',
                 status: 'Aktif',
                 articles_count: 0
             };
             this.categoryModalOpen = true;
         },
         
         openEditCategoryModal(cat) {
             this.isEditingCategory = true;
             this.categoryForm = JSON.parse(JSON.stringify(cat));
             this.categoryModalOpen = true;
         },
         
         saveCategory() {
             if (!this.categoryForm.name.trim()) {
                 alert('Nama kategori artikel wajib diisi.');
                 return;
             }
             if (this.isEditingCategory) {
                 const idx = this.categories.findIndex(c => c.id === this.categoryForm.id);
                 if (idx !== -1) {
                     const oldName = this.categories[idx].name;
                     const newName = this.categoryForm.name;
                     this.categories[idx] = JSON.parse(JSON.stringify(this.categoryForm));
                     // Update associated articles category name
                     this.articles.forEach(a => {
                         if (a.category === oldName) a.category = newName;
                     });
                 }
                 this.showToast('Kategori ' + this.categoryForm.name + ' berhasil diperbarui!');
             } else {
                 this.categories.push(JSON.parse(JSON.stringify(this.categoryForm)));
                 this.showToast('Kategori baru ' + this.categoryForm.name + ' berhasil ditambahkan!');
             }
             this.categoryModalOpen = false;
         },
         
         toggleCategoryStatus(cat) {
             cat.status = cat.status === 'Aktif' ? 'Nonaktif' : 'Aktif';
             this.showToast('Status kategori ' + cat.name + ' diubah menjadi ' + cat.status);
         },
         
         openDeleteCategory(cat) {
             this.selectedCategoryItem = cat;
             this.deleteCategoryModalOpen = true;
         },
         
         confirmDeleteCategory() {
             if (this.selectedCategoryItem) {
                 this.categories = this.categories.filter(c => c.id !== this.selectedCategoryItem.id);
                 this.deleteCategoryModalOpen = false;
                 this.showToast('Kategori ' + this.selectedCategoryItem.name + ' telah dihapus.');
                 this.selectedCategoryItem = null;
             }
         },
         
         openPreview(a) {
             this.selectedArticle = a;
             this.previewModalOpen = true;
         },
         
         autoSlug() {
             this.form.slug = this.form.title.toLowerCase()
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
                 this.showToast('Gambar artikel dipilih dari Media Library!');
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
         
         saveArticle() {
             if (!this.form.title.trim()) {
                 alert('Judul artikel wajib diisi.');
                 return;
             }
             if (this.isEditing) {
                 const idx = this.articles.findIndex(a => a.id === this.form.id);
                 if (idx !== -1) {
                     this.articles[idx] = JSON.parse(JSON.stringify(this.form));
                 }
                 this.showToast('Artikel berhasil diperbarui!');
             } else {
                 this.articles.unshift(JSON.parse(JSON.stringify(this.form)));
                 this.showToast('Artikel baru berhasil ditambahkan!');
             }
             this.editorModalOpen = false;
         },
         
         togglePublish(a) {
             a.status = a.status === 'Published' ? 'Draft' : 'Published';
             this.showToast('Status artikel diubah menjadi ' + a.status);
         },
         
         openDelete(a) {
             this.selectedArticle = a;
             this.deleteModalOpen = true;
         },
         
         confirmDelete() {
             if (this.selectedArticle) {
                 this.articles = this.articles.filter(a => a.id !== this.selectedArticle.id);
                 this.deleteModalOpen = false;
                 this.showToast('Artikel telah dihapus.');
                 this.selectedArticle = null;
             }
         },
         
         getImageUrl(path) {
             if (!path) return '/images/know-thawing.jpg';
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
                        Knowledge & Tips
                    </h2>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        <span>DYNAMIC CONTENT & CATEGORY MANAGER</span>
                    </span>
                    <span class="text-xs text-gray-500 font-medium">
                        • Edukasi Dapur & Kategori Artikel Otomatis
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 leading-relaxed max-w-3xl">
                    Kelola artikel panduan memasak dan kategori artikel terpusat dengan <strong>Automatic Color Pairing</strong>. Dropdown kategori artikel di editor otomatis mengikuti <strong>Knowledge Category Manager</strong>.
                </p>
            </div>

            <!-- Action Button depending on Active Tab -->
            <div class="flex items-center gap-3 shrink-0">
                <button x-show="activeMainTab === 'articles'"
                        @click="openCreateModal()" 
                        type="button"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern font-bold text-xs sm:text-sm text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer">
                    <span class="text-base leading-none">＋</span>
                    <span>Tambah Artikel</span>
                </button>
                <button x-show="activeMainTab === 'categories'"
                        @click="openCreateCategoryModal()" 
                        type="button"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern font-bold text-xs sm:text-sm text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer">
                    <span class="text-base leading-none">＋</span>
                    <span>Tambah Kategori Artikel</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 2. PENGATURAN SECTION KNOWLEDGE & TIPS                 -->
    <!-- ======================================================= -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-7 shadow-2xs space-y-6">
        
        <!-- PENGATURAN SECTION KNOWLEDGE & TIPS -->
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-4 border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <span class="text-base shrink-0">⚙️</span>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-xs sm:text-sm font-extrabold text-brand-dark uppercase tracking-wider truncate sm:whitespace-normal">
                            Pengaturan Section Knowledge & Tips
                        </h3>
                        <p class="text-[11px] text-gray-500 leading-relaxed">
                            Kelola label badge, judul utama (headline), dan deskripsi pengantar pada section knowledge & tips (<code>&lt;section id="knowledge"&gt;</code>) Landing Page.
                        </p>
                    </div>
                </div>

                <button @click="showToast('Header Section Knowledge & Tips berhasil diperbarui!')" 
                        type="button" 
                        class="px-4 py-2 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark shadow-2xs transition-all cursor-pointer shrink-0 whitespace-nowrap">
                    Simpan Header
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <!-- Label Badge Section -->
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-brand-dark mb-1">
                        Label Badge Section
                    </label>
                    <input type="text" 
                           x-model="knowledgeSection.label" 
                           placeholder="Contoh: Edukasi & Inspirasi Dapur"
                           class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-semibold text-brand-primary focus:ring-2 focus:ring-brand-primary/30">
                </div>

                <!-- Judul Utama / Heading -->
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-brand-dark mb-1">
                        Judul Utama / Heading
                    </label>
                    <input type="text" 
                           x-model="knowledgeSection.title" 
                           placeholder="Contoh: Dapur & Knowledge"
                           class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-extrabold text-brand-dark focus:ring-2 focus:ring-brand-primary/30">
                </div>

                <!-- Deskripsi Pengantar -->
                <div class="md:col-span-5">
                    <label class="block text-xs font-bold text-brand-dark mb-1">
                        Deskripsi Pengantar
                    </label>
                    <textarea x-model="knowledgeSection.subtitle" 
                              rows="2" 
                              placeholder="Panduan praktis seputar penanganan daging, thawing, penyimpanan frozen food, hingga tips memasak harian keluarga di Yogyakarta."
                              class="w-full text-xs rounded-modern border border-gray-300 p-2 bg-white leading-relaxed focus:ring-2 focus:ring-brand-primary/30"></textarea>
                </div>
            </div>

            <!-- Small Header Section Realtime Preview -->
            <div class="pt-1">
                <div class="bg-brand-cream/60 rounded-modern-xl border border-dashed border-gray-300 p-4 sm:p-5 text-center max-w-xl mx-auto shadow-2xs">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-brand-soft-green text-brand-primary mb-2 shadow-2xs transition-all"
                          x-text="knowledgeSection.label || 'Edukasi & Inspirasi Dapur'">
                    </span>
                    <h4 class="text-lg sm:text-xl font-extrabold text-brand-dark tracking-tight mb-1.5 transition-all"
                        x-text="knowledgeSection.title || 'Dapur & Knowledge'">
                    </h4>
                    <p class="text-xs text-gray-600 font-normal leading-relaxed max-w-md mx-auto transition-all"
                       x-text="knowledgeSection.subtitle || 'Panduan praktis seputar penanganan daging, thawing, penyimpanan frozen food, hingga tips memasak harian keluarga di Yogyakarta.'">
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- 3. Main Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-gray-200 pb-2">
        <button @click="activeMainTab = 'articles'" 
                type="button"
                :class="activeMainTab === 'articles' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer">
            📰 Daftar Artikel (<span x-text="articles.length"></span>)
        </button>
        <button @click="activeMainTab = 'categories'" 
                type="button"
                :class="activeMainTab === 'categories' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer">
            🏷️ Kelola Kategori Artikel (<span x-text="categories.length"></span>)
        </button>
    </div>

    <!-- ======================================================= -->
    <!-- 3. TAB 1: DAFTAR ARTIKEL                                -->
    <!-- ======================================================= -->
    <div x-show="activeMainTab === 'articles'" class="space-y-6">
        
        <!-- Filters & Search Toolbar -->
        <div class="bg-white rounded-modern-xl border border-gray-200/80 p-4 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                <div class="relative w-full sm:w-72">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" 
                           x-model="searchQuery"
                           placeholder="Cari judul artikel..." 
                           class="w-full pl-9 pr-4 py-2 rounded-modern text-xs border border-gray-300 focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary bg-gray-50/50">
                </div>

                <select x-model="selectedCategoryFilter" 
                        class="w-full sm:w-48 py-2 px-3 rounded-modern text-xs border border-gray-300 bg-white font-medium text-brand-dark focus:ring-2 focus:ring-brand-primary/30">
                    <option value="all">Semua Kategori</option>
                    <template x-for="cat in activeCategories" :key="cat.id">
                        <option :value="cat.name" x-text="cat.name"></option>
                    </template>
                </select>
            </div>

            <div class="text-xs text-gray-500 font-medium self-end sm:self-center">
                Total: <span class="font-bold text-brand-dark" x-text="filteredArticles.length"></span> Artikel
            </div>
        </div>

        <!-- Article Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <template x-for="(art, idx) in filteredArticles" :key="art.id">
                <div class="bg-white rounded-modern-xl border border-gray-200/80 overflow-hidden shadow-2xs hover:shadow-card transition-all flex flex-col justify-between">
                    
                    <div>
                        <!-- 3:2 Aspect Ratio Thumbnail -->
                        <div class="relative aspect-[3/2] w-full bg-brand-dark overflow-hidden">
                            <img :src="getImageUrl(art.image)" :alt="art.title" class="w-full h-full object-cover">
                            
                            <!-- Category Badge Overlay (Automatic Color Pairing) -->
                            <div class="absolute top-3 left-3">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border shadow-xs"
                                      :class="getColorClass(getCategoryColor(art.category))"
                                      x-text="art.category">
                                </span>
                            </div>

                            <!-- Status Badge Overlay -->
                            <div class="absolute top-3 right-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border shadow-xs"
                                      :class="art.status === 'Published' 
                                          ? 'bg-emerald-50 text-emerald-800 border-emerald-300' 
                                          : 'bg-amber-50 text-amber-800 border-amber-300'"
                                      x-text="art.status">
                                </span>
                            </div>
                        </div>

                        <!-- Article Body -->
                        <div class="p-5 space-y-2">
                            <div class="text-[11px] text-gray-400 font-medium" x-text="art.published_at"></div>
                            <h4 class="font-extrabold text-brand-dark text-sm sm:text-base leading-snug line-clamp-2" x-text="art.title"></h4>
                            <p class="text-xs text-gray-500 line-clamp-3 leading-relaxed" x-text="art.excerpt"></p>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="p-4 bg-gray-50/70 border-t border-gray-100 flex items-center justify-between gap-2">
                        <button @click="openPreview(art)" 
                                type="button"
                                class="px-3 py-1.5 rounded-modern text-xs font-semibold text-gray-600 hover:text-brand-dark hover:bg-white border border-gray-200 transition-colors cursor-pointer">
                            Preview Baca
                        </button>

                        <div class="flex items-center gap-1.5">
                            <button @click="openEditModal(art)" 
                                    type="button"
                                    class="px-3 py-1.5 rounded-modern text-xs font-bold text-brand-primary bg-brand-soft-green hover:bg-emerald-100 transition-colors cursor-pointer">
                                Edit
                            </button>
                            <button @click="togglePublish(art)" 
                                    type="button"
                                    class="p-1.5 rounded-modern text-xs font-semibold text-gray-500 hover:bg-gray-200 transition-colors cursor-pointer"
                                    :title="art.status === 'Published' ? 'Jadikan Draft' : 'Terbitkan'">
                                <span x-text="art.status === 'Published' ? '👁' : '✓'"></span>
                            </button>
                            <button @click="openDelete(art)" 
                                    type="button"
                                    class="p-1.5 rounded-modern text-rose-500 hover:bg-rose-50 transition-colors cursor-pointer" 
                                    title="Hapus Artikel">
                                🗑
                            </button>
                        </div>
                    </div>

                </div>
            </template>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 4. TAB 2: KELOLA KATEGORI ARTIKEL                       -->
    <!-- ======================================================= -->
    <div x-show="activeMainTab === 'categories'" class="space-y-6">
        
        <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                        Master Kategori Artikel Knowledge
                    </h3>
                    <p class="text-xs text-gray-500">Kategori di bawah ini otomatis menjadi opsi dropdown pada form pembuatan/pengeditan artikel.</p>
                </div>
                <button @click="openCreateCategoryModal()" 
                        type="button"
                        class="px-4 py-2 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm cursor-pointer">
                    ＋ Tambah Kategori
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="cat in categories" :key="cat.id">
                    <div class="p-4 rounded-modern-lg border border-gray-200 bg-gray-50/60 flex items-center justify-between gap-3 shadow-2xs"
                         :class="{'opacity-60 bg-gray-100': cat.status === 'Nonaktif'}">
                        <div class="space-y-1.5">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold border shadow-xs inline-block"
                                  :class="getColorClass(cat.color)"
                                  x-text="cat.name">
                            </span>
                            <div class="text-[11px] text-gray-500">
                                Status: <strong :class="cat.status === 'Aktif' ? 'text-emerald-700' : 'text-gray-500'" x-text="cat.status"></strong>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button @click="openEditCategoryModal(cat)" 
                                    type="button" 
                                    class="p-1.5 rounded-modern text-xs font-bold text-brand-primary bg-brand-soft-green hover:bg-emerald-100 cursor-pointer">
                                ✏️
                            </button>
                            <button @click="toggleCategoryStatus(cat)" 
                                    type="button" 
                                    class="p-1.5 rounded-modern text-xs font-semibold text-gray-500 hover:bg-gray-200 cursor-pointer"
                                    :title="cat.status === 'Aktif' ? 'Nonaktifkan' : 'Aktifkan'">
                                <span x-text="cat.status === 'Aktif' ? '👁' : '✓'"></span>
                            </button>
                            <button @click="openDeleteCategory(cat)" 
                                    type="button" 
                                    class="p-1.5 rounded-modern text-xs text-rose-500 hover:bg-rose-50 cursor-pointer"
                                    title="Hapus Kategori">
                                🗑
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

    <!-- ======================================================= -->
    <!-- 5. MODAL EDITOR ARTIKEL (Form + REAL LIVE PREVIEW)      -->
    <!-- ======================================================= -->
    <div x-show="editorModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="editorModalOpen = false"></div>

        <div class="min-h-full flex items-center justify-center p-3 sm:p-6">
            <div class="relative bg-white rounded-modern-xl max-w-5xl w-full p-6 sm:p-8 shadow-2xl border border-gray-200 overflow-hidden my-6">
                
                <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-100">
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold text-brand-dark"
                            x-text="isEditing ? 'Edit Artikel: ' + form.title : 'Tambah Artikel Baru'">
                        </h3>
                        <p class="text-xs text-gray-500">Pilihan kategori terhubung langsung dengan Knowledge Category Manager.</p>
                    </div>
                    <button @click="editorModalOpen = false" 
                            type="button" 
                            class="p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
                        ✕
                    </button>
                </div>

                <form @submit.prevent="saveArticle()" class="space-y-6">
                    
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        
                        <!-- Left Form (7 cols on lg) -->
                        <div class="lg:col-span-7 space-y-4">
                            
                            <!-- Judul Artikel -->
                            <div>
                                <label class="block text-xs font-bold text-brand-dark mb-1">
                                    Judul Artikel <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="form.title" 
                                       @input="autoSlug()"
                                       required
                                       placeholder="Contoh: 5 Tips Menyimpan Daging Beku Agar Tetap Segar"
                                       class="w-full text-xs sm:text-sm rounded-modern border border-gray-300 p-2.5 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                            </div>

                            <!-- Kategori (FROM KNOWLEDGE CATEGORY MANAGER) -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-bold text-brand-dark">
                                            Kategori Artikel
                                        </label>
                                    </div>
                                    <select x-model="form.category" 
                                            class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium">
                                        <template x-for="cat in activeCategories" :key="cat.id">
                                            <option :value="cat.name" x-text="cat.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-brand-dark mb-1">
                                        Status Publikasi
                                    </label>
                                    <select x-model="form.status" 
                                            class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white">
                                        <option value="Published">Published (Tayang)</option>
                                        <option value="Draft">Draft (Disimpan)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-brand-dark mb-1">
                                        Tanggal Publikasi
                                    </label>
                                    <input type="text" 
                                           x-model="form.published_at" 
                                           placeholder="17 Agustus 2026"
                                           class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white">
                                </div>
                            </div>

                            <!-- Excerpt / Ringkasan -->
                            <div>
                                <label class="block text-xs font-bold text-brand-dark mb-1">
                                    Excerpt / Ringkasan Singkat (Tampil di Kartu) <span class="text-rose-500">*</span>
                                </label>
                                <textarea x-model="form.excerpt" 
                                          rows="2" 
                                          required
                                          placeholder="Ringkasan 1-2 kalimat pengantar artikel..."
                                          class="w-full text-xs sm:text-sm rounded-modern border border-gray-300 p-2.5 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary"></textarea>
                            </div>

                            <!-- Isi Lengkap Artikel -->
                            <div>
                                <label class="block text-xs font-bold text-brand-dark mb-1">
                                    Isi Lengkap Artikel (Tampil saat Expand "Baca Selengkapnya") <span class="text-rose-500">*</span>
                                </label>
                                <textarea x-model="form.content" 
                                          rows="6" 
                                          required
                                          placeholder="Tuliskan isi artikel lengkap dengan paragraf panjang, poin-poin penjelasan, dan tips..."
                                          class="w-full text-xs sm:text-sm font-sans rounded-modern border border-gray-300 p-3 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary leading-relaxed"></textarea>
                            </div>

                            <!-- Thumbnail via Global Media Picker -->
                            <div class="p-4 rounded-modern bg-gray-50 border border-gray-200 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-brand-dark">
                                        Gambar Thumbnail (Rasio 3:2)
                                    </label>
                                    <span class="text-[11px] font-semibold text-emerald-700">Global Media Picker</span>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="w-24 aspect-[3/2] rounded-modern overflow-hidden bg-brand-dark shrink-0 border border-gray-300 shadow-2xs">
                                        <img :src="getImageUrl(form.image)" alt="Article Thumbnail" class="w-full h-full object-cover">
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
                                    <p class="font-bold text-gray-700">Rekomendasi Gambar Artikel:</p>
                                    <p>1200 × 800 px • Rasio 3:2 • JPG / WebP • Disarankan ≤ 300 KB</p>
                                </div>
                            </div>

                        </div>

                        <!-- Right: REAL LANDING PAGE KNOWLEDGE CARD & READER PREVIEW (5 cols on lg) -->
                        <div class="lg:col-span-5 space-y-3 sticky top-4">
                            
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-extrabold text-brand-dark uppercase tracking-wider">
                                    Real Landing Page Preview
                                </label>
                                <div class="flex items-center bg-gray-100 p-0.5 rounded text-[10px]">
                                    <button @click="previewIsExpanded = false" type="button" 
                                            :class="!previewIsExpanded ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500'"
                                            class="px-2 py-0.5 rounded cursor-pointer">Card</button>
                                    <button @click="previewIsExpanded = true" type="button" 
                                            :class="previewIsExpanded ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500'"
                                            class="px-2 py-0.5 rounded cursor-pointer">Inline Reader</button>
                                </div>
                            </div>

                            <!-- Real Knowledge Card Component Replica -->
                            <div class="bg-gray-50 p-4 rounded-modern-xl border border-gray-200">
                                
                                <!-- Card Mode -->
                                <div x-show="!previewIsExpanded" class="bg-white rounded-modern-lg border border-gray-200 shadow-sm overflow-hidden space-y-0">
                                    <div class="relative aspect-[3/2] w-full bg-brand-dark overflow-hidden">
                                        <img :src="getImageUrl(form.image)" :alt="form.title" class="w-full h-full object-cover">
                                        <div class="absolute top-2.5 left-2.5">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border shadow-xs"
                                                  :class="getColorClass(getCategoryColor(form.category))"
                                                  x-text="form.category"></span>
                                        </div>
                                    </div>
                                    <div class="p-4 space-y-2">
                                        <span class="text-[10px] text-gray-400 font-medium" x-text="form.published_at"></span>
                                        <h4 class="font-extrabold text-brand-dark text-sm leading-snug line-clamp-2" x-text="form.title || 'Judul Artikel'"></h4>
                                        <p class="text-xs text-gray-500 line-clamp-3 leading-relaxed" x-text="form.excerpt || 'Ringkasan singkat...'"></p>
                                        <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-brand-primary">
                                            <span>Baca Selengkapnya</span>
                                            <span>▾</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Inline Reader Mode -->
                                <div x-show="previewIsExpanded" class="bg-white rounded-modern-lg border border-gray-200 shadow-md p-4 space-y-3">
                                    <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                                              :class="getColorClass(getCategoryColor(form.category))"
                                              x-text="form.category"></span>
                                        <span class="text-[10px] text-gray-400" x-text="form.published_at"></span>
                                    </div>
                                    <h3 class="font-black text-brand-dark text-sm leading-tight" x-text="form.title"></h3>
                                    <div class="aspect-[3/2] w-full rounded overflow-hidden bg-brand-dark">
                                        <img :src="getImageUrl(form.image)" :alt="form.title" class="w-full h-full object-cover">
                                    </div>
                                    <div class="text-xs text-gray-700 leading-relaxed whitespace-pre-line max-h-48 overflow-y-auto" x-text="form.content"></div>
                                </div>

                            </div>

                            <p class="text-[11px] text-gray-400 text-center">
                                Preview di atas 100% sama dengan komponen artikel Knowledge pada Landing Page.
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
                            Simpan Artikel
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 6. MODAL EDITOR KATEGORI ARTIKEL                        -->
    <!-- ======================================================= -->
    <div x-show="categoryModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="categoryModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white rounded-modern-xl max-w-md w-full p-6 shadow-2xl border border-gray-200 overflow-hidden space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h3 class="text-base font-extrabold text-brand-dark" x-text="isEditingCategory ? 'Edit Kategori' : 'Tambah Kategori Artikel'"></h3>
                    <button @click="categoryModalOpen = false" class="text-gray-400 hover:text-gray-700">✕</button>
                </div>
                <form @submit.prevent="saveCategory()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Nama Kategori <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="categoryForm.name" required placeholder="Contoh: Tips Penyimpanan" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white">
                    </div>

                    <!-- Automatic Color Pairing Theme Selector -->
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Tema Warna Badge</label>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="opt in colorOptions" :key="opt.id">
                                <button type="button" 
                                        @click="categoryForm.color = opt.id"
                                        class="p-2 rounded border text-xs font-bold transition-all text-center"
                                        :class="categoryForm.color === opt.id ? 'ring-2 ring-brand-primary ' + opt.class : 'bg-white text-gray-600 border-gray-200'">
                                    <span class="capitalize" x-text="opt.id"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Live Badge Preview -->
                    <div class="p-3 bg-gray-50 rounded border border-gray-200 text-center space-y-1">
                        <span class="text-[10px] text-gray-400 block font-semibold">Pratinjau Badge Artikel:</span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold border inline-block shadow-2xs"
                              :class="getColorClass(categoryForm.color)"
                              x-text="categoryForm.name || 'Nama Kategori'"></span>
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex justify-end gap-2">
                        <button @click="categoryModalOpen = false" type="button" class="px-4 py-2 rounded-modern text-xs font-semibold text-gray-600 bg-gray-100">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 7. GLOBAL MEDIA PICKER MODAL                            -->
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
                            <p class="text-xs text-gray-500">Pilih dari pustaka media atau unggah gambar artikel baru.</p>
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
                                 class="group relative aspect-[3/2] rounded-modern overflow-hidden border-2 transition-all cursor-pointer bg-brand-dark"
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
                            Pilih Gambar Artikel
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
                            <p class="text-[11px] text-gray-400">Mendukung JPG, PNG, WebP (Rekomendasi 1200 × 800 px ≤ 300 KB)</p>
                        </div>
                    </label>

                    <template x-if="uploadedPreviewUrl">
                        <div class="p-3 bg-emerald-50/50 rounded-modern border border-emerald-200 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-14 aspect-[3/2] rounded overflow-hidden bg-brand-dark border border-gray-200">
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

    <!-- 8. Modal Preview Baca Lengkap -->
    <div x-show="previewModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="previewModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-3 sm:p-6">
            <div class="relative bg-white rounded-modern-xl max-w-3xl w-full p-6 sm:p-8 shadow-2xl border border-gray-200 overflow-hidden my-6 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <span class="px-3 py-1 rounded-full text-xs font-bold border shadow-xs"
                          :class="getColorClass(getCategoryColor(selectedArticle?.category))"
                          x-text="selectedArticle?.category"></span>
                    <button @click="previewModalOpen = false" type="button" class="p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 cursor-pointer">✕</button>
                </div>
                <div class="space-y-3">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-brand-dark leading-tight" x-text="selectedArticle?.title"></h2>
                    <div class="text-xs text-gray-400 font-medium">Dipublikasikan: <span class="text-gray-600 font-semibold" x-text="selectedArticle?.published_at"></span></div>
                </div>
                <div class="aspect-[3/2] w-full rounded-modern overflow-hidden bg-brand-dark">
                    <img :src="getImageUrl(selectedArticle?.image)" :alt="selectedArticle?.title" class="w-full h-full object-cover">
                </div>
                <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed whitespace-pre-line text-xs sm:text-sm" x-text="selectedArticle?.content"></div>
                <div class="pt-4 border-t border-gray-100 flex justify-end">
                    <button @click="previewModalOpen = false" type="button" class="px-5 py-2 rounded-modern text-xs font-bold text-white bg-brand-dark hover:bg-black cursor-pointer">Tutup Preview</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 9. Delete Confirmation Modals -->
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
                    <h3 class="text-base font-bold text-brand-dark">Hapus Artikel ini?</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">Artikel <strong class="text-brand-dark" x-text="selectedArticle?.title"></strong> akan dihapus dari modul Knowledge.</p>
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
                        Hapus Artikel
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div x-show="deleteCategoryModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-xs" @click="deleteCategoryModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white rounded-modern-xl max-w-sm w-full p-6 shadow-xl border border-gray-200 text-center space-y-4">
                
                <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>

                <div class="space-y-1">
                    <h3 class="text-base font-bold text-brand-dark">Hapus Kategori Artikel ini?</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">Kategori <strong class="text-brand-dark" x-text="selectedCategoryItem?.name"></strong> akan dihapus dari daftar master kategori.</p>
                </div>

                <div class="pt-3 flex items-center justify-center gap-3">
                    <button @click="deleteCategoryModalOpen = false" 
                            type="button" 
                            class="px-4 py-2 rounded-modern text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button @click="confirmDeleteCategory()" 
                            type="button" 
                            class="px-4 py-2 rounded-modern text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 transition-colors cursor-pointer">
                        Hapus Kategori
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- 10. Toast Notification -->
    <div x-show="toastVisible" 
         x-cloak
         x-transition
         class="fixed bottom-6 right-6 z-50 bg-brand-dark text-white px-4 py-3 rounded-modern-lg shadow-xl border border-white/10 flex items-center gap-2.5 text-xs font-semibold">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        <span x-text="toastMessage"></span>
    </div>

</div>
@endsection
