@extends('layouts.admin', [
    'title' => 'Knowledge & Tips',
    'pageTitle' => 'Knowledge & Tips'
])

@section('content')
<div class="space-y-6"
     x-data="knowledgeManager({
         csrfToken: '{{ csrf_token() }}',
         articles: {{ json_encode($articles) }},
         categories: {{ json_encode($knowledgeCategories) }},
         mediaLibrary: {{ json_encode($mediaLibrary) }},
         knowledgeSection: {{ json_encode($knowledgeSection ?? [
             'label' => 'Edukasi & Inspirasi Dapur',
             'title' => 'Dapur & Knowledge',
             'subtitle' => 'Panduan praktis seputar penanganan daging, thawing, penyimpanan frozen food, hingga tips memasak harian keluarga di Yogyakarta.'
         ]) }}
     })">
    
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
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Artikel</span>
                </button>
                <button x-show="activeMainTab === 'categories'"
                        @click="openCreateCategoryModal()" 
                        type="button"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern font-bold text-xs sm:text-sm text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
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
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-modern text-xs font-bold text-brand-primary bg-brand-soft-green hover:bg-emerald-100 transition-colors cursor-pointer">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span>Edit</span>
                            </button>
                            <button @click="togglePublish(art)" 
                                    type="button"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-modern text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer"
                                    :title="art.status === 'Published' ? 'Jadikan Draft' : 'Terbitkan'">
                                <span class="w-2 h-2 rounded-full shrink-0"
                                      :class="art.status === 'Published' ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                                <span class="text-[10px]" x-text="art.status === 'Published' ? 'Live' : 'Draft'"></span>
                            </button>
                            <button @click="openDelete(art)" 
                                    type="button"
                                    class="p-1.5 rounded-modern text-rose-500 hover:bg-rose-50 hover:text-rose-600 transition-colors cursor-pointer inline-flex items-center justify-center" 
                                    title="Hapus Artikel">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
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
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm cursor-pointer">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Kategori</span>
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
                            <div class="text-[11px] text-gray-500 flex items-center gap-1.5">
                                <span>Status:</span>
                                <span class="inline-flex items-center gap-1 font-semibold"
                                      :class="cat.status === 'Aktif' ? 'text-emerald-700' : 'text-gray-500'">
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                          :class="cat.status === 'Aktif' ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                                    <span x-text="cat.status"></span>
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button @click="openEditCategoryModal(cat)" 
                                    type="button" 
                                    class="p-1.5 rounded-modern text-xs font-bold text-brand-primary bg-brand-soft-green hover:bg-emerald-100 transition-colors cursor-pointer inline-flex items-center justify-center"
                                    title="Edit Kategori">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button @click="toggleCategoryStatus(cat)" 
                                    type="button" 
                                    class="inline-flex items-center gap-1 px-2 py-1.5 rounded-modern text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer"
                                    :title="cat.status === 'Aktif' ? 'Nonaktifkan' : 'Aktifkan'">
                                <span class="w-2 h-2 rounded-full shrink-0"
                                      :class="cat.status === 'Aktif' ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                                <span class="text-[10px]" x-text="cat.status === 'Aktif' ? 'On' : 'Off'"></span>
                            </button>
                            <button @click="openDeleteCategory(cat)" 
                                    type="button" 
                                    class="p-1.5 rounded-modern text-xs text-rose-500 hover:bg-rose-50 hover:text-rose-600 transition-colors cursor-pointer inline-flex items-center justify-center"
                                    title="Hapus Kategori">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

    <!-- ======================================================= -->
    <!-- 5. MODAL EDITOR DOKUMEN ARTIKEL (B1-R DOCUMENT UX)      -->
    <!-- ======================================================= -->
    <div x-show="editorModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center"
         :class="isFocusMode ? 'p-0' : 'p-2 sm:p-4'"
         role="dialog" 
         aria-modal="true">
        
        <!-- Backdrop (hidden in focus mode) -->
        <div x-show="!isFocusMode" class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="closeEditorModal()"></div>

        <!-- Modal Window (Strict 100dvh in focus mode, 92vh in normal mode) -->
        <div :class="isFocusMode ? 'fixed inset-0 w-screen h-[100dvh] max-h-[100dvh] rounded-none m-0 border-0' : 'relative bg-white rounded-modern-xl max-w-7xl w-full mx-auto my-auto shadow-2xl border border-gray-200 h-[92vh] max-h-[92vh]'"
             class="bg-white flex flex-col overflow-hidden transition-all duration-200 z-10">
            
            <!-- 1. MODAL TOP HEADER BAR -->
            <div class="px-5 py-3 border-b border-gray-200 bg-white flex items-center justify-between gap-4 shrink-0">
                <!-- Left: Title & Subtitle -->
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-modern bg-brand-soft-green text-brand-primary flex items-center justify-center text-base">✍️</span>
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-brand-dark leading-snug"
                            x-text="isEditing ? 'Edit Artikel: ' + (form.title || 'Tanpa Judul') : 'Tambah Artikel Baru'">
                        </h3>
                        <p class="text-[11px] text-gray-500 hidden sm:block">Editor Dokumen Penulisan Artikel Knowledge & Edukasi Dapur</p>
                    </div>
                </div>

                <!-- Middle: Navigation Tabs ([ Konten ] [ Informasi ] [ Preview ]) -->
                <div class="flex items-center bg-gray-100/90 p-1 rounded-modern border border-gray-200/80 text-xs">
                    <button @click="editorTab = 'content'" 
                            type="button" 
                            :class="editorTab === 'content' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-600 hover:text-brand-dark'"
                            class="px-3.5 py-1.5 rounded-modern transition-all cursor-pointer flex items-center gap-1.5">
                        <span>✍️</span>
                        <span>Konten</span>
                    </button>
                    <button @click="editorTab = 'info'" 
                            type="button" 
                            :class="editorTab === 'info' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-600 hover:text-brand-dark'"
                            class="px-3.5 py-1.5 rounded-modern transition-all cursor-pointer flex items-center gap-1.5">
                        <span>📋</span>
                        <span>Informasi</span>
                    </button>
                    <button @click="editorTab = 'preview'" 
                            type="button" 
                            :class="editorTab === 'preview' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-600 hover:text-brand-dark'"
                            class="px-3.5 py-1.5 rounded-modern transition-all cursor-pointer flex items-center gap-1.5">
                        <span>👁️</span>
                        <span>Preview</span>
                    </button>
                </div>

                <!-- Right: Focus Mode + Close Button -->
                <div class="flex items-center gap-2 shrink-0">
                    <button @click="isFocusMode = !isFocusMode" 
                            type="button"
                            :class="isFocusMode ? 'bg-brand-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-3 py-1.5 rounded-modern text-xs font-semibold transition-all cursor-pointer inline-flex items-center gap-1.5 shrink-0"
                            title="Toggle Mode Fokus / Fullscreen">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                        <span class="hidden sm:inline whitespace-nowrap" x-text="isFocusMode ? 'Keluar Fokus' : 'Mode Fokus'"></span>
                    </button>
                    <button @click="closeEditorModal()" 
                            type="button" 
                            class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer shrink-0"
                            title="Tutup Editor"
                            aria-label="Tutup modal editor artikel">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- 2. TAB 1: KONTEN (WRITING WORKSPACE) -->
            <div x-show="editorTab === 'content'" class="flex-1 flex flex-col min-h-0 overflow-hidden">
                
                <!-- STICKY DOCUMENT TOOLBAR -->
                <div class="bg-gray-50/95 backdrop-blur-xs border-b border-gray-200 py-2 flex items-center justify-between gap-2.5 shrink-0 select-none overflow-x-auto sm:overflow-x-visible transition-all duration-200"
                     :class="isFocusMode ? 'px-6 sm:px-8 md:px-10' : 'px-4 sm:px-6'">
                    
                    <!-- Left Tools (Undo, Redo, Heading, Font Size, Formatting, Align, Lists, Quick Callouts) -->
                    <div class="flex items-center flex-wrap gap-1 sm:gap-1.5 text-xs min-w-0">
                        <!-- Undo / Redo -->
                        <button @click="docUndo()" type="button" title="Undo (Ctrl+Z)" class="p-1.5 rounded hover:bg-gray-200 text-gray-700 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v2m0 0l-4-4m4 4l4-4M3 10l4-4m-4 4l4 4" /></svg>
                        </button>
                        <button @click="docRedo()" type="button" title="Redo (Ctrl+Y)" class="p-1.5 rounded hover:bg-gray-200 text-gray-700 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 10H11a5 5 0 00-5 5v2m0 0l4-4m-4 4l-4-4m20-4l-4-4m4 4l-4 4" /></svg>
                        </button>

                        <div class="w-px h-5 bg-gray-300 mx-0.5"></div>

                        <!-- Block Style Selector (Normal, H2, H3) -->
                        <select :value="activeFormats.blockStyle"
                                @change="applyBlockStyle($event.target.value)" 
                                title="Format Gaya Blok"
                                class="text-xs font-semibold rounded-modern border border-gray-300 py-1 px-2 bg-white text-gray-700 cursor-pointer hover:border-gray-400">
                            <option value="p">¶ Normal</option>
                            <option value="h2">H2 Sub-Judul</option>
                            <option value="h3">H3 Sub-Poin</option>
                        </select>

                        <!-- Font Size Selector (12 to 32px + Mixed) -->
                        <select :value="activeFormats.fontSize"
                                @change="setFontSize($event.target.value)" 
                                title="Ukuran Huruf (Font Size)"
                                class="text-xs font-semibold rounded-modern border border-gray-300 py-1 px-1.5 bg-white text-gray-700 cursor-pointer hover:border-gray-400">
                            <option value="mixed" x-show="activeFormats.fontSize === 'mixed'" disabled>— (Mixed)</option>
                            <option value="12">12px</option>
                            <option value="14">14px</option>
                            <option value="16">16px (Normal)</option>
                            <option value="18">18px</option>
                            <option value="20">20px</option>
                            <option value="24">24px</option>
                            <option value="28">28px</option>
                            <option value="32">32px</option>
                        </select>

                        <div class="w-px h-5 bg-gray-300 mx-0.5"></div>

                        <!-- Bold, Italic, Underline, Strikethrough, Link -->
                        <button @click="formatDoc('bold')" 
                                type="button" 
                                title="Tebal / Bold (Ctrl+B)" 
                                :class="activeFormats.bold ? 'bg-gray-300 text-brand-dark font-black' : 'text-gray-700 hover:bg-gray-200'"
                                class="p-1 px-2 rounded font-black cursor-pointer text-xs">
                            B
                        </button>
                        <button @click="formatDoc('italic')" 
                                type="button" 
                                title="Miring / Italic (Ctrl+I)" 
                                :class="activeFormats.italic ? 'bg-gray-300 text-brand-dark' : 'text-gray-700 hover:bg-gray-200'"
                                class="p-1 px-2 rounded italic font-serif cursor-pointer text-xs">
                            I
                        </button>
                        <button @click="formatDoc('underline')" 
                                type="button" 
                                title="Garis Bawah / Underline (Ctrl+U)" 
                                :class="activeFormats.underline ? 'bg-gray-300 text-brand-dark underline font-bold' : 'text-gray-700 hover:bg-gray-200 underline'"
                                class="p-1 px-2 rounded cursor-pointer text-xs">
                            U
                        </button>
                        <button @click="formatDoc('strikeThrough')" 
                                type="button" 
                                title="Coret / Strikethrough" 
                                :class="activeFormats.strikethrough ? 'bg-gray-300 text-brand-dark line-through font-bold' : 'text-gray-700 hover:bg-gray-200 line-through'"
                                class="p-1 px-2 rounded cursor-pointer text-xs">
                            S
                        </button>
                        <button @click="openLinkModal()" type="button" title="Sisipkan Tautan / Link (Ctrl+K)" class="p-1.5 rounded hover:bg-gray-200 text-gray-700 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                        </button>

                        <div class="w-px h-5 bg-gray-300 mx-0.5"></div>

                        <!-- Alignment Dropdown / Controls -->
                        <div class="inline-flex items-center rounded-modern border border-gray-300 bg-white p-0.5 shadow-2xs">
                            <button @click="setAlignment('left')" 
                                    type="button" 
                                    title="Rata Kiri" 
                                    :class="activeFormats.align === 'left' ? 'bg-gray-200 text-brand-dark' : 'text-gray-600 hover:bg-gray-100'"
                                    class="p-1 rounded cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h14" /></svg>
                            </button>
                            <button @click="setAlignment('center')" 
                                    type="button" 
                                    title="Rata Tengah" 
                                    :class="activeFormats.align === 'center' ? 'bg-gray-200 text-brand-dark' : 'text-gray-600 hover:bg-gray-100'"
                                    class="p-1 rounded cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M7 12h10M5 18h14" /></svg>
                            </button>
                            <button @click="setAlignment('right')" 
                                    type="button" 
                                    title="Rata Kanan" 
                                    :class="activeFormats.align === 'right' ? 'bg-gray-200 text-brand-dark' : 'text-gray-600 hover:bg-gray-100'"
                                    class="p-1 rounded cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M10 12h10M6 18h14" /></svg>
                            </button>
                            <button @click="setAlignment('justify')" 
                                    type="button" 
                                    title="Rata Kiri-Kanan (Justify)" 
                                    :class="activeFormats.align === 'justify' ? 'bg-gray-200 text-brand-dark' : 'text-gray-600 hover:bg-gray-100'"
                                    class="p-1 rounded cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                            </button>
                        </div>

                        <!-- Line Spacing Dropdown -->
                        <select :value="activeFormats.lineHeight"
                                @change="setLineSpacing($event.target.value)" 
                                title="Spasi Baris (Line Spacing)"
                                class="text-xs font-semibold rounded-modern border border-gray-300 py-1 px-1 bg-white text-gray-700 cursor-pointer hover:border-gray-400">
                            <option value="1.4">Spasi Rapat (1.4)</option>
                            <option value="1.75">Spasi Normal (1.75)</option>
                            <option value="2.0">Spasi Lega (2.0)</option>
                        </select>

                        <div class="w-px h-5 bg-gray-300 mx-0.5"></div>

                        <!-- Bulleted & Numbered Lists & Indentation -->
                        <button @click="formatDoc('insertUnorderedList')" 
                                type="button" 
                                title="Daftar Poin (Bullet List)" 
                                :class="activeFormats.isUnorderedList ? 'bg-gray-300 text-brand-dark' : 'hover:bg-gray-200 text-gray-700'"
                                class="p-1.5 rounded cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16M2 6h.01M2 12h.01M2 18h.01" /></svg>
                        </button>
                        <button @click="formatDoc('insertOrderedList')" 
                                type="button" 
                                title="Daftar Nomor (Numbered List)" 
                                :class="activeFormats.isOrderedList ? 'bg-gray-300 text-brand-dark' : 'hover:bg-gray-200 text-gray-700'"
                                class="p-1.5 rounded cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 6h13M7 12h13M7 18h13M3 6h1v4M3 18h2" /></svg>
                        </button>
                        <button @click="formatDoc('outdent')" 
                                type="button" 
                                title="Kurangi Indent / Level List (Shift+Tab)" 
                                class="p-1.5 rounded hover:bg-gray-200 text-gray-700 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
                        </button>
                        <button @click="formatDoc('indent')" 
                                type="button" 
                                title="Tambah Indent / Sub-Level List (Tab)" 
                                class="p-1.5 rounded hover:bg-gray-200 text-gray-700 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" /></svg>
                        </button>

                        <div class="w-px h-5 bg-gray-300 mx-0.5"></div>

                        <!-- Quick Callout Insert -->
                        <button @click="insertCallout('tips')" type="button" title="Sisipkan Tips Box (Hijau)" class="px-2 py-1 rounded bg-emerald-50 text-emerald-800 border border-emerald-200/80 hover:bg-emerald-100 font-bold inline-flex items-center gap-1 cursor-pointer">
                            <span>💡</span>
                            <span class="hidden sm:inline">Tips</span>
                        </button>
                        <button @click="insertCallout('info')" type="button" title="Sisipkan Info Box (Krem)" class="px-2 py-1 rounded bg-amber-50 text-amber-800 border border-amber-200/80 hover:bg-amber-100 font-bold inline-flex items-center gap-1 cursor-pointer">
                            <span>📋</span>
                            <span class="hidden sm:inline">Info</span>
                        </button>
                        <button @click="openMediaPicker('inline')" type="button" title="Sisipkan Gambar dari Media Picker" class="px-2 py-1 rounded bg-gray-100 text-gray-700 hover:bg-gray-200 font-semibold inline-flex items-center gap-1 cursor-pointer">
                            <span>🖼️</span>
                            <span class="hidden sm:inline">Gambar</span>
                        </button>
                    </div>

                    <!-- Right View Toggles (Panel Sisipkan) -->
                    <div class="flex items-center gap-2 text-xs shrink-0 pl-3 sm:pl-5" :class="isFocusMode ? 'pr-2 sm:pr-4' : ''">
                        <button @click="showInsertPanel = !showInsertPanel" 
                                type="button"
                                :class="showInsertPanel ? 'bg-brand-soft-green text-brand-primary border-brand-soft-green-border font-bold' : 'bg-white text-gray-700 border-gray-300 font-medium'"
                                class="px-3.5 py-1.5 rounded-modern border cursor-pointer transition-all inline-flex items-center gap-1.5 shrink-0 shadow-2xs hover:bg-gray-100"
                                title="Buka/Tutup Panel Sisipkan">
                            <span class="text-sm">◫</span>
                            <span class="font-semibold text-xs whitespace-nowrap">Sisipkan</span>
                        </button>
                    </div>

                </div>

                <!-- WORKSPACE 3-COLUMN LAYOUT -->
                <div class="flex-1 flex min-h-0 bg-[#F4F6F4] overflow-hidden">
                    
                    <!-- LEFT COLUMN: PANEL SISIPKAN (Collapsible, works in both Normal & Focus Mode) -->
                    <div x-show="showInsertPanel" 
                         x-transition
                         class="w-56 shrink-0 bg-white border-r border-gray-200 p-4 overflow-y-auto space-y-5 hidden md:block">
                        
                        <div>
                            <h4 class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Konten Khusus</h4>
                            <div class="space-y-2">
                                <!-- Tips Penting -->
                                <button @click="insertCallout('tips')" 
                                        type="button" 
                                        class="w-full text-left p-2.5 rounded-modern bg-brand-soft-green/50 hover:bg-brand-soft-green border border-brand-soft-green-border transition-colors cursor-pointer group">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">💡</span>
                                        <div>
                                            <p class="text-xs font-bold text-brand-primary">Tips Penting</p>
                                            <p class="text-[10px] text-emerald-800/80">Highlight tips praktis dapur</p>
                                        </div>
                                    </div>
                                </button>

                                <!-- Info Penting -->
                                <button @click="insertCallout('info')" 
                                        type="button" 
                                        class="w-full text-left p-2.5 rounded-modern bg-amber-50 hover:bg-amber-100/80 border border-amber-200/80 transition-colors cursor-pointer group">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">📋</span>
                                        <div>
                                            <p class="text-xs font-bold text-amber-900">Info Penting</p>
                                            <p class="text-[10px] text-amber-800/80">Catatan & edukasi penting</p>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Elemen Dokumen</h4>
                            <div class="space-y-1.5 text-xs">
                                <button @click="openMediaPicker('inline')" type="button" class="w-full text-left px-3 py-2 rounded-modern hover:bg-gray-100 font-medium text-gray-700 flex items-center gap-2 cursor-pointer">
                                    <span>🖼️</span>
                                    <span>Gambar Artikel</span>
                                </button>
                                <button @click="insertQuote()" type="button" class="w-full text-left px-3 py-2 rounded-modern hover:bg-gray-100 font-medium text-gray-700 flex items-center gap-2 cursor-pointer">
                                    <span>❝</span>
                                    <span>Kutipan (Quote)</span>
                                </button>
                                <button @click="insertDivider()" type="button" class="w-full text-left px-3 py-2 rounded-modern hover:bg-gray-100 font-medium text-gray-700 flex items-center gap-2 cursor-pointer">
                                    <span>➖</span>
                                    <span>Garis Pemisah (HR)</span>
                                </button>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-100 text-[11px] text-gray-400 space-y-1">
                            <p class="font-bold text-gray-500">Shortcut Keyboard:</p>
                            <p>• <strong>Ctrl+B</strong> Tebal</p>
                            <p>• <strong>Ctrl+I</strong> Miring</p>
                            <p>• <strong>Ctrl+K</strong> Tautan</p>
                            <p>• <strong>Ctrl+S</strong> Simpan</p>
                        </div>

                    </div>

                    <!-- CENTER COLUMN: THE BLANK CANVAS (Paper-like Document Workspace) -->
                    <div class="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-8 md:py-10 flex justify-center items-start bg-[#F4F6F4]" 
                         @click="if ($event.target === $el) { document.getElementById('documentCanvas')?.focus() }">
                        <div class="w-full max-w-3xl bg-white rounded-modern-xl shadow-md border border-gray-200/90 p-8 sm:p-14 mb-16 shrink-0 min-h-[720px] h-auto">
                            
                            <!-- Title Field in Document -->
                            <div class="space-y-2 mb-6 border-b border-gray-100 pb-4">
                                <textarea x-model="form.title" 
                                          @input="autoSlug(); updateDocStats(); $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'" 
                                          x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px' })"
                                          rows="1"
                                          placeholder="Ketik Judul Artikel di Sini..." 
                                          class="w-full text-2xl sm:text-3xl font-extrabold text-brand-dark tracking-tight leading-snug border-0 p-0 focus:ring-0 focus:outline-hidden placeholder:text-gray-300 resize-none overflow-hidden bg-transparent block"></textarea>
                                <div class="flex items-center gap-2 text-xs text-gray-400 font-medium pt-1">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border shadow-2xs"
                                          :class="getColorClass(getCategoryColor(form.category))"
                                          x-text="form.category"></span>
                                    <span>•</span>
                                    <span x-text="form.published_at"></span>
                                </div>
                            </div>

                            <!-- Contenteditable Canvas -->
                            <div id="documentCanvas" 
                                 contenteditable="true"
                                 @input="onCanvasInput()"
                                 @keydown="onCanvasKeydown($event)"
                                 @keyup="updateDocStats()"
                                 @mouseup="updateDocStats()"
                                 class="w-full text-gray-800 text-[16px] leading-relaxed focus:outline-hidden min-h-[480px]">
                            </div>

                        </div>
                    </div>

                    <!-- RIGHT COLUMN: REALTIME PREVIEW BACA (Collapsible) -->
                    <div x-show="showPreviewPanel && !isFocusMode" 
                         x-transition
                         class="w-80 shrink-0 bg-white border-l border-gray-200 p-4 overflow-y-auto space-y-4 hidden lg:block">
                        
                        <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                            <label class="block text-xs font-extrabold text-brand-dark uppercase tracking-wider">
                                Preview Baca
                            </label>
                            <div class="flex items-center bg-gray-100 p-0.5 rounded text-[10px]">
                                <button @click="previewIsExpanded = false" type="button" 
                                        :class="!previewIsExpanded ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500'"
                                        class="px-2 py-0.5 rounded cursor-pointer">Card</button>
                                <button @click="previewIsExpanded = true" type="button" 
                                        :class="previewIsExpanded ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500'"
                                        class="px-2 py-0.5 rounded cursor-pointer">Reader</button>
                            </div>
                        </div>

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
                            <div class="p-3.5 space-y-2">
                                <span class="text-[10px] text-gray-400 font-medium" x-text="form.published_at"></span>
                                <h4 class="font-extrabold text-brand-dark text-xs leading-snug line-clamp-2" x-text="form.title || 'Judul Artikel'"></h4>
                                <p class="text-[11px] text-gray-500 line-clamp-3 leading-relaxed" x-text="form.excerpt || 'Ringkasan singkat...'"></p>
                                <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-brand-primary">
                                    <span>Baca Selengkapnya</span>
                                    <span>▾</span>
                                </div>
                            </div>
                        </div>

                        <!-- Inline Reader Mode -->
                        <div x-show="previewIsExpanded" class="bg-gray-50 p-3.5 rounded-modern-lg border border-gray-200 shadow-sm space-y-3">
                            <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                                      :class="getColorClass(getCategoryColor(form.category))"
                                      x-text="form.category"></span>
                                <span class="text-[10px] text-gray-400" x-text="form.published_at"></span>
                            </div>
                            <h3 class="font-black text-brand-dark text-xs leading-tight" x-text="form.title || 'Judul Artikel'"></h3>
                            <div class="aspect-[3/2] w-full rounded overflow-hidden bg-brand-dark">
                                <img :src="getImageUrl(form.image)" :alt="form.title" class="w-full h-full object-cover">
                            </div>
                            <div class="prose prose-sm max-w-none text-xs text-gray-700 leading-relaxed max-h-96 overflow-y-auto space-y-3 bg-white p-3 rounded border border-gray-200" 
                                 x-html="canvasHtml"></div>
                        </div>

                        <p class="text-[11px] text-gray-400 text-center leading-relaxed">
                            Tampilan otomatis diperbarui secara live sesuai dengan teks di Canvas.
                        </p>

                    </div>

                </div>

            </div>

            <!-- 3. TAB 2: INFORMASI (METADATA WORKSPACE) -->
            <div x-show="editorTab === 'info'" class="flex-1 p-6 sm:p-8 overflow-y-auto space-y-6 max-w-4xl mx-auto w-full">
                
                <div>
                    <h3 class="text-base font-extrabold text-brand-dark">Informasi & Metadata Artikel</h3>
                    <p class="text-xs text-gray-500">Kelola kategori, status publikasi, excerpt ringkas, dan gambar thumbnail.</p>
                </div>

                <div class="space-y-4">
                    <!-- Judul & Slug -->
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

                    <!-- Kategori, Status, Tanggal -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">
                                Kategori Artikel
                            </label>
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
                                    class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium">
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
                                  rows="3" 
                                  required
                                  placeholder="Ringkasan 1-2 kalimat pengantar artikel yang akan tampil pada kartu artikel Landing Page..."
                                  class="w-full text-xs sm:text-sm rounded-modern border border-gray-300 p-2.5 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary"></textarea>
                    </div>

                    <!-- Thumbnail via Global Media Picker -->
                    <div class="p-4 rounded-modern-lg bg-gray-50 border border-gray-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-brand-dark">
                                Gambar Thumbnail Kartu (Rasio 3:2)
                            </label>
                            <span class="text-[11px] font-semibold text-emerald-700">Global Media Picker</span>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="w-28 aspect-[3/2] rounded-modern overflow-hidden bg-brand-dark shrink-0 border border-gray-300 shadow-2xs">
                                <img :src="getImageUrl(form.image)" alt="Article Thumbnail" class="w-full h-full object-cover">
                            </div>
                            <div class="space-y-2">
                                <button @click="openMediaPicker('thumbnail')" 
                                        type="button" 
                                        class="px-4 py-2 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark shadow-2xs transition-all cursor-pointer inline-flex items-center gap-1.5">
                                    <span>🖼️</span>
                                    <span>Pilih dari Media Picker</span>
                                </button>
                                <p class="text-[11px] text-gray-500 font-mono truncate max-w-xs" x-text="form.image"></p>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-200 text-[11px] text-gray-500 space-y-1">
                            <p class="font-bold text-gray-700">Rekomendasi Gambar Thumbnail:</p>
                            <p>1200 × 800 px • Rasio 3:2 • JPG / WebP • Disarankan ≤ 300 KB</p>
                        </div>
                    </div>

                </div>

            </div>

            <!-- 4. TAB 3: PREVIEW (FULL READING PREVIEW) -->
            <div x-show="editorTab === 'preview'" class="flex-1 p-6 sm:p-8 overflow-y-auto bg-gray-100 flex flex-col items-center">
                
                <!-- Viewport Simulator Controls -->
                <div class="mb-6 bg-white p-1 rounded-modern border border-gray-200 shadow-2xs flex items-center gap-1 text-xs">
                    <button @click="previewDevice = 'desktop'" type="button" 
                            :class="previewDevice === 'desktop' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'text-gray-600 hover:bg-gray-100'"
                            class="px-3 py-1.5 rounded-modern transition-all cursor-pointer">Desktop (100%)</button>
                    <button @click="previewDevice = 'tablet'" type="button" 
                            :class="previewDevice === 'tablet' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'text-gray-600 hover:bg-gray-100'"
                            class="px-3 py-1.5 rounded-modern transition-all cursor-pointer">Tablet (768px)</button>
                    <button @click="previewDevice = 'mobile'" type="button" 
                            :class="previewDevice === 'mobile' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'text-gray-600 hover:bg-gray-100'"
                            class="px-3 py-1.5 rounded-modern transition-all cursor-pointer">Mobile (425px)</button>
                </div>

                <!-- Reading Container -->
                <div :class="{
                    'max-w-3xl w-full': previewDevice === 'desktop',
                    'max-w-xl w-full': previewDevice === 'tablet',
                    'max-w-sm w-full': previewDevice === 'mobile'
                }" class="bg-white rounded-modern-xl shadow-lg border border-gray-200 p-6 sm:p-10 space-y-6 transition-all duration-300">
                    
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <span class="px-3 py-1 rounded-full text-xs font-bold border shadow-xs"
                              :class="getColorClass(getCategoryColor(form.category))"
                              x-text="form.category"></span>
                        <span class="text-xs text-gray-400 font-medium" x-text="form.published_at"></span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black text-brand-dark tracking-tight leading-tight" x-text="form.title || 'Judul Artikel'"></h1>

                    <div class="aspect-[3/2] w-full rounded-modern overflow-hidden bg-brand-dark shadow-xs">
                        <img :src="getImageUrl(form.image)" :alt="form.title" class="w-full h-full object-cover">
                    </div>

                    <div class="prose prose-sm sm:prose-base max-w-none text-gray-800 leading-relaxed space-y-4" 
                         x-html="canvasHtml"></div>

                </div>

            </div>

            <!-- 5. BOTTOM STATUS & SAVE BAR -->
            <div class="px-6 py-3.5 border-t border-gray-200 bg-white flex items-center justify-between gap-4 shrink-0">
                <!-- Left: Word and Char Count -->
                <div class="text-xs text-gray-500 font-medium flex items-center gap-2">
                    <span class="font-bold text-gray-700" x-text="wordCount + ' kata'"></span>
                    <span>•</span>
                    <span x-text="charCount + ' karakter'"></span>
                </div>

                <!-- Middle: Save Status Badge -->
                <div class="hidden sm:flex items-center gap-2">
                    <span :class="isDirty() ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-emerald-700 bg-emerald-50 border-emerald-200'"
                          class="px-2.5 py-1 rounded-full text-xs font-semibold border flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full" :class="isDirty() ? 'bg-amber-500' : 'bg-emerald-500'"></span>
                        <span x-text="isDirty() ? 'Perubahan belum disimpan' : 'Semua perubahan tersimpan'"></span>
                    </span>
                </div>

                <!-- Right: Actions -->
                <div class="flex items-center gap-3">
                    <button @click="closeEditorModal()" 
                            type="button" 
                            class="px-4 py-2.5 rounded-modern text-xs font-semibold text-gray-600 hover:bg-gray-100 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button @click="saveArticle()" 
                            type="button" 
                            class="px-6 py-2.5 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm transition-all cursor-pointer inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Artikel</span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- ======================================================= -->
    <!-- LINK INSERTION MODAL (DOCUMENT EDITOR)                  -->
    <!-- ======================================================= -->
    <div x-show="linkModalOpen" 
         x-cloak
         class="fixed inset-0 z-[75] overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-xs" @click="linkModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white rounded-modern-xl max-w-sm w-full p-5 shadow-2xl border border-gray-200 space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                    <h3 class="text-sm font-extrabold text-brand-dark flex items-center gap-2">
                        <span>🔗</span>
                        <span>Sisipkan Tautan (Link)</span>
                    </h3>
                    <button @click="linkModalOpen = false" type="button" class="p-1 text-gray-400 hover:text-gray-700 rounded-lg cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">URL Target Web</label>
                    <input type="url" 
                           x-model="linkInputUrl" 
                           placeholder="https://..."
                           @keydown.enter.prevent="applyLink()"
                           class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white focus:ring-2 focus:ring-brand-primary/30 font-mono">
                </div>
                <div class="pt-2 flex items-center justify-end gap-2 text-xs">
                    <button @click="linkModalOpen = false" type="button" class="px-3.5 py-2 rounded-modern font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 cursor-pointer">Batal</button>
                    <button @click="applyLink()" type="button" class="px-4 py-2 rounded-modern font-bold text-white bg-brand-primary hover:bg-brand-primary-dark cursor-pointer">Terapkan Link</button>
                </div>
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
                    <button @click="categoryModalOpen = false" 
                            type="button"
                            class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
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
                                 class="group relative aspect-[3/2] rounded-modern overflow-hidden border-2 transition-all cursor-pointer bg-brand-dark"
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
                        <div class="space-y-2 flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full bg-brand-soft-green text-brand-primary flex items-center justify-center shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
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
                    <button @click="previewModalOpen = false" 
                            type="button" 
                            class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-3">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-brand-dark leading-tight" x-text="selectedArticle?.title"></h2>
                    <div class="text-xs text-gray-400 font-medium">Dipublikasikan: <span class="text-gray-600 font-semibold" x-text="selectedArticle?.published_at"></span></div>
                </div>
                <div class="aspect-[3/2] w-full rounded-modern overflow-hidden bg-brand-dark">
                    <img :src="getImageUrl(selectedArticle?.image)" :alt="selectedArticle?.title" class="w-full h-full object-cover">
                </div>
                <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed text-xs sm:text-sm space-y-3" 
                     x-html="window.KnowledgeArticleParser ? window.KnowledgeArticleParser.renderBlocksToHtml(selectedArticle?.content) : (selectedArticle?.content || '')"></div>
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



    <!-- 11. Unsaved Changes Warning Modal -->
    <div x-show="unsavedChangesModalOpen" 
         x-cloak
         class="fixed inset-0 z-[60] overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-xs" @click="unsavedChangesModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white rounded-modern-xl max-w-sm w-full p-6 shadow-xl border border-gray-200 text-center space-y-4">
                
                <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <div class="space-y-1">
                    <h3 class="text-base font-bold text-brand-dark">Perubahan Belum Disimpan</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">Anda memiliki perubahan pada artikel yang belum disimpan. Yakin ingin keluar?</p>
                </div>

                <div class="pt-3 flex items-center justify-center gap-3">
                    <button @click="unsavedChangesModalOpen = false" 
                            type="button" 
                            class="px-4 py-2 rounded-modern text-xs font-bold text-brand-primary bg-brand-soft-green hover:bg-emerald-100 transition-colors cursor-pointer">
                        Tetap Edit
                    </button>
                    <button @click="forceCloseEditorModal()" 
                            type="button" 
                            class="px-4 py-2 rounded-modern text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer">
                        Keluar Tanpa Simpan
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- 12. Toast Notification -->
    <div x-show="toastVisible" 
         x-cloak
         x-transition
         class="fixed bottom-6 right-6 z-50 bg-brand-dark text-white px-4 py-3 rounded-modern-lg shadow-xl border border-white/10 flex items-center gap-2.5 text-xs font-semibold">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        <span x-text="toastMessage"></span>
    </div>

</div>
@endsection
