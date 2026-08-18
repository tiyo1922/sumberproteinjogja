@extends('layouts.admin', [
    'title' => 'Site Settings',
    'pageTitle' => 'Site & Contact Settings'
])

@section('content')
<div class="space-y-6"
     x-data="{
         settings: {{ json_encode($settingsData) }},
         mediaLibrary: {{ json_encode($mediaLibrary) }},
         mediaPickerOpen: false,
         targetField: 'logo', // 'logo' | 'favicon' | 'avatar'
         activeTab: 'contact', // 'website' | 'contact' | 'panel' | 'profile'
         toastMessage: '',
         toastVisible: false,
         mediaTab: 'library', // 'library' | 'upload'
         selectedMedia: null,
         uploadedFile: null,
         uploadedPreviewUrl: null,
         
         showToast(msg) {
             this.toastMessage = msg;
             this.toastVisible = true;
             setTimeout(() => { this.toastVisible = false; }, 3000);
         },
         
         openMediaPicker(field) {
             this.targetField = field;
             this.mediaTab = 'library';
             let currentPath = '';
             if (field === 'logo') currentPath = this.settings.website.logo_url;
             else if (field === 'favicon') currentPath = this.settings.website.favicon_url;
             else if (field === 'avatar') currentPath = this.settings.admin_user.avatar_image;
             
             this.selectedMedia = this.mediaLibrary.find(m => m.path === currentPath) || this.mediaLibrary[0] || null;
             this.uploadedFile = null;
             this.uploadedPreviewUrl = null;
             this.mediaPickerOpen = true;
         },
         
         selectMedia(media) {
             this.selectedMedia = media;
         },
         
         confirmMediaSelection() {
             const chosenUrl = this.mediaTab === 'library' && this.selectedMedia ? this.selectedMedia.path : this.uploadedPreviewUrl;
             if (!chosenUrl) return;
             
             if (this.targetField === 'logo') {
                 this.settings.website.logo_url = chosenUrl;
                 this.showToast('Logo website berhasil diperbarui!');
             } else if (this.targetField === 'favicon') {
                 this.settings.website.favicon_url = chosenUrl;
                 this.showToast('Favicon website berhasil diperbarui!');
             } else if (this.targetField === 'avatar') {
                 this.settings.admin_user.avatar_image = chosenUrl;
                 this.showToast('Avatar admin berhasil diperbarui!');
             }
             this.mediaPickerOpen = false;
         },
         
         handleFileUpload(e) {
             const file = e.target.files ? e.target.files[0] : (e.dataTransfer ? e.dataTransfer.files[0] : null);
             if (!file) return;
             this.uploadedFile = {
                 name: file.name,
                 size: (file.size / 1024).toFixed(0) + ' KB',
                 type: file.type,
             };
             this.uploadedPreviewUrl = URL.createObjectURL(file);
         },
         
         saveSettings() {
             this.showToast('Pengaturan Kontak & Brand berhasil disimpan!');
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
                        Site & Contact Settings
                    </h2>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                        <span>CENTRALIZED CONTACT & IDENTITY</span>
                    </span>
                    <span class="text-xs text-gray-500 font-medium">
                        • Pusat Nomor WhatsApp, Brand & Profil Admin
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 leading-relaxed max-w-3xl">
                    Kelola pusat nomor WhatsApp pemesanan dan komunikasi admin, identitas brand, logo, favicon, serta akun administrator dengan <strong>Global Media Picker</strong>.
                </p>
            </div>

            <!-- Save Action Button -->
            <div class="flex items-center gap-3 shrink-0">
                <button @click="saveSettings()" 
                        type="button"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-modern font-bold text-xs sm:text-sm text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer">
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 2. Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-gray-200 pb-2">
        <button @click="activeTab = 'contact'" 
                type="button"
                :class="activeTab === 'contact' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer">
            💬 Pusat WhatsApp & Kontak
        </button>
        <button @click="activeTab = 'website'" 
                type="button"
                :class="activeTab === 'website' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer">
            🌐 Identitas Website & Brand
        </button>
        <button @click="activeTab = 'panel'" 
                type="button"
                :class="activeTab === 'panel' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer">
            ⚙️ Panel Admin CMS
        </button>
        <button @click="activeTab = 'profile'" 
                type="button"
                :class="activeTab === 'profile' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer">
            👤 Profil Administrator
        </button>
    </div>

    <!-- 3. Tab Contents Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Form Fields (7 cols on lg) -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Tab 1: CONTACT / WHATSAPP SETTINGS (CENTRALIZED) -->
            <div x-show="activeTab === 'contact'" class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-5">
                <div class="border-b border-gray-100 pb-2">
                    <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                        Pusat Pengaturan WhatsApp & Kontak Bisnis
                    </h3>
                    <p class="text-xs text-gray-500">
                        Nomor di bawah ini menjadi referensi tunggal bagi seluruh tombol CTA produk, konsultasi kategori, dan floating cart.
                    </p>
                </div>

                <!-- Nomor Pemesanan -->
                <div class="p-4 rounded-modern bg-gray-50 border border-gray-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-brand-dark">
                            A. Nomor WhatsApp Pemesanan (Order) <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                            ● Status: Aktif
                        </span>
                    </div>
                    <div class="relative">
                        <input type="text" 
                               x-model="settings.contact.order_whatsapp" 
                               placeholder="6281234567891"
                               class="w-full text-xs sm:text-sm rounded-modern border border-gray-300 p-2.5 bg-white font-mono font-bold text-brand-dark">
                    </div>
                    <p class="text-[11px] text-gray-500">
                        Keterangan: Nomor WhatsApp yang digunakan untuk menerima checkout keranjang belanja dan pemesanan produk bertipe <em>Order</em>.
                    </p>
                </div>

                <!-- Nomor Admin -->
                <div class="p-4 rounded-modern bg-gray-50 border border-gray-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-brand-dark">
                            B. Nomor WhatsApp Admin (Default Product Destination) <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                            ● Status: Aktif
                        </span>
                    </div>
                    <div class="relative">
                        <input type="text" 
                               x-model="settings.contact.admin_whatsapp" 
                               placeholder="6281234567890"
                               class="w-full text-xs sm:text-sm rounded-modern border border-gray-300 p-2.5 bg-white font-mono font-bold text-brand-dark">
                    </div>
                    <p class="text-[11px] text-gray-500">
                        Keterangan: Nomor WhatsApp untuk komunikasi langsung, tanya stok, konsultasi resep, dan tujuan default seluruh produk (<em>Chat Admin</em>).
                    </p>
                </div>

                <!-- Nomor CS & Telepon Kantor -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            C. WhatsApp Customer Care / CS
                        </label>
                        <input type="text" 
                               x-model="settings.contact.cs_whatsapp" 
                               placeholder="6281234567892"
                               class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Telepon Kantor / Outlet
                        </label>
                        <input type="text" 
                               x-model="settings.contact.phone" 
                               placeholder="(0274) 889977"
                               class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-mono">
                    </div>
                </div>

            </div>

            <!-- Tab 2: Website Identity -->
            <div x-show="activeTab === 'website'" class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-5">
                <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider border-b border-gray-100 pb-2">
                    Identitas & Logo Website
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Nama Website
                        </label>
                        <input type="text" 
                               x-model="settings.website.site_name" 
                               class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Nama Brand Utama
                        </label>
                        <input type="text" 
                               x-model="settings.website.brand_name" 
                               class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-brand-dark mb-1">
                        Tagline / Slogan Brand
                    </label>
                    <input type="text" 
                           x-model="settings.website.tagline" 
                           class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-brand-dark mb-1">
                        Pola Judul Tab Browser (Tab Title Pattern)
                    </label>
                    <input type="text" 
                           x-model="settings.website.tab_title_pattern" 
                           class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-mono">
                </div>

                <!-- Logo & Favicon via Global Media Picker -->
                <div class="p-4 rounded-modern bg-gray-50 border border-gray-200 space-y-4">
                    <h4 class="text-xs font-bold text-brand-dark">Aset Logo & Favicon</h4>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <!-- Logo Website -->
                        <div class="p-3 bg-white rounded-modern border border-gray-200 space-y-2">
                            <label class="block text-xs font-bold text-brand-dark">Logo Header Website</label>
                            <div class="h-16 rounded bg-brand-dark flex items-center justify-center p-2 border border-gray-200">
                                <img :src="getImageUrl(settings.website.logo_url)" alt="Logo" class="max-h-full max-w-full object-contain">
                            </div>
                            <button @click="openMediaPicker('logo')" type="button" 
                                    class="w-full py-1.5 rounded text-[11px] font-bold text-brand-primary bg-brand-soft-green hover:bg-emerald-100 transition-colors cursor-pointer">
                                Ganti Logo via Media Picker
                            </button>
                            <p class="text-[10px] text-gray-400">400 × 120 px • PNG Transparan / SVG</p>
                        </div>

                        <!-- Favicon -->
                        <div class="p-3 bg-white rounded-modern border border-gray-200 space-y-2">
                            <label class="block text-xs font-bold text-brand-dark">Favicon Browser Tab</label>
                            <div class="h-16 rounded bg-gray-100 flex items-center justify-center p-2 border border-gray-200">
                                <img :src="getImageUrl(settings.website.favicon_url)" alt="Favicon" class="w-8 h-8 object-cover rounded">
                            </div>
                            <button @click="openMediaPicker('favicon')" type="button" 
                                    class="w-full py-1.5 rounded text-[11px] font-bold text-brand-primary bg-brand-soft-green hover:bg-emerald-100 transition-colors cursor-pointer">
                                Ganti Favicon via Media Picker
                            </button>
                            <p class="text-[10px] text-gray-400">64 × 64 px • PNG / ICO</p>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Tab 3: Admin Panel CMS -->
            <div x-show="activeTab === 'panel'" class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-5">
                <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider border-b border-gray-100 pb-2">
                    Konfigurasi Tampilan Panel Admin
                </h3>

                <div>
                    <label class="block text-xs font-bold text-brand-dark mb-1">
                        Nama Panel Admin
                    </label>
                    <input type="text" 
                           x-model="settings.admin_panel.panel_name" 
                           class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-brand-dark mb-1">
                        Badge Tag Sidebar
                    </label>
                    <input type="text" 
                           x-model="settings.admin_panel.badge_tag" 
                           class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-brand-dark mb-1">
                        Catatan Footer Panel Admin
                    </label>
                    <input type="text" 
                           x-model="settings.admin_panel.footer_note" 
                           class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-mono text-[11px]">
                </div>
            </div>

            <!-- Tab 4: Admin Profile -->
            <div x-show="activeTab === 'profile'" class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-5">
                <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider border-b border-gray-100 pb-2">
                    Profil Akun Administrator
                </h3>

                <div class="flex items-center gap-4 pb-2">
                    <div class="w-16 h-16 rounded-full bg-brand-dark text-white flex items-center justify-center font-bold text-lg ring-4 ring-emerald-500/20 shadow-md overflow-hidden relative group">
                        <img :src="getImageUrl(settings.admin_user.avatar_image)" alt="Avatar" class="w-full h-full object-cover">
                    </div>
                    <div class="space-y-1">
                        <button @click="openMediaPicker('avatar')" type="button" 
                                class="px-3 py-1.5 rounded-modern text-xs font-bold text-brand-primary bg-brand-soft-green hover:bg-emerald-100 cursor-pointer">
                            Ganti Foto Avatar
                        </button>
                        <p class="text-[10px] text-gray-400">Rasio 1:1 • JPG/PNG ≤ 200 KB</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Nama Lengkap Admin
                        </label>
                        <input type="text" 
                               x-model="settings.admin_user.name" 
                               class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Peran / Role
                        </label>
                        <input type="text" 
                               x-model="settings.admin_user.role" 
                               disabled 
                               class="w-full text-xs rounded-modern border border-gray-200 p-2.5 bg-gray-100 text-gray-500 font-semibold">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Email Login
                        </label>
                        <input type="email" 
                               x-model="settings.admin_user.email" 
                               class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Nomor Telepon
                        </label>
                        <input type="text" 
                               x-model="settings.admin_user.phone" 
                               class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-mono">
                    </div>
                </div>
            </div>

        </div>

        <!-- Right: REAL LIVE BRAND PREVIEWS (5 cols on lg) -->
        <div class="lg:col-span-5 space-y-4 sticky top-4">
            <label class="block text-xs font-extrabold text-brand-dark uppercase tracking-wider">
                Real Live Brand & Contact Preview
            </label>

            <!-- Brand Summary Card Replica -->
            <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 shadow-2xs space-y-4">
                
                <!-- Mock Header Branding -->
                <div class="p-4 rounded-modern bg-brand-dark text-white flex items-center gap-3">
                    <div class="w-10 h-10 rounded-modern bg-brand-primary flex items-center justify-center font-black text-sm shadow-md overflow-hidden">
                        <img :src="getImageUrl(settings.website.logo_url)" alt="Logo" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-white" x-text="settings.website.site_name"></h4>
                        <p class="text-[10px] text-emerald-400 font-semibold" x-text="settings.website.tagline"></p>
                    </div>
                </div>

                <!-- Contact Destination Overview -->
                <div class="p-3.5 bg-gray-50 rounded-modern border border-gray-200 space-y-2">
                    <span class="text-[10px] uppercase font-bold text-gray-400">Pusat Kontak WhatsApp:</span>
                    <div class="space-y-1.5 text-xs">
                        <div class="flex items-center justify-between p-2 rounded bg-white border border-gray-200">
                            <span class="text-gray-600 font-medium">Order WhatsApp:</span>
                            <span class="font-mono font-bold text-emerald-700" x-text="'+' + settings.contact.order_whatsapp"></span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded bg-white border border-gray-200">
                            <span class="text-gray-600 font-medium">Admin WhatsApp:</span>
                            <span class="font-mono font-bold text-brand-primary" x-text="'+' + settings.contact.admin_whatsapp"></span>
                        </div>
                    </div>
                </div>

                <!-- Mock Browser Tab -->
                <div class="p-3 bg-gray-100 rounded-modern border border-gray-200 space-y-1">
                    <span class="text-[10px] uppercase font-bold text-gray-400">Pratinjau Tab Browser:</span>
                    <div class="bg-white px-3 py-1.5 rounded border border-gray-300 flex items-center gap-2 text-xs font-medium text-gray-700 shadow-2xs">
                        <img :src="getImageUrl(settings.website.favicon_url)" alt="Favicon" class="w-4 h-4 rounded-xs object-cover">
                        <span class="truncate" x-text="settings.website.tab_title_pattern.replace('{page_title}', 'Beranda')"></span>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- ======================================================= -->
    <!-- 4. GLOBAL MEDIA PICKER MODAL                            -->
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
                            <h3 class="text-base font-extrabold text-brand-dark">Pilih Aset dari Media Picker</h3>
                            <p class="text-xs text-gray-500">Pilih dari pustaka media atau unggah file logo/ikon baru.</p>
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
                        Upload File Baru
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
                            Pilih Aset Ini
                        </button>
                    </div>
                </div>

                <!-- Tab 2: Upload -->
                <div x-show="mediaTab === 'upload'" class="space-y-4">
                    <label class="block border-2 border-dashed border-gray-300 rounded-modern-xl p-8 text-center hover:border-brand-primary hover:bg-brand-soft-green/30 transition-all cursor-pointer"
                           @dragover.prevent="" 
                           @drop.prevent="handleFileUpload($event)">
                        <input type="file" accept="image/jpeg,image/png,image/webp,image/svg+xml,image/x-icon" class="hidden" @change="handleFileUpload($event)">
                        <div class="space-y-2">
                            <span class="text-3xl">📤</span>
                            <p class="text-xs font-bold text-brand-dark">Tarik & Lepaskan file ke sini, atau klik untuk memilih file</p>
                            <p class="text-[11px] text-gray-400">Mendukung PNG, SVG, JPG, WebP, ICO</p>
                        </div>
                    </label>

                    <template x-if="uploadedPreviewUrl">
                        <div class="p-3 bg-emerald-50/50 rounded-modern border border-emerald-200 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-14 aspect-square rounded overflow-hidden bg-brand-dark border border-gray-200">
                                    <img :src="uploadedPreviewUrl" alt="Uploaded Preview" class="w-full h-full object-contain">
                                </div>
                                <div class="text-xs space-y-0.5">
                                    <p class="font-bold text-brand-dark" x-text="uploadedFile?.name"></p>
                                    <p class="text-[10px] text-gray-500" x-text="uploadedFile?.size + ' • ' + uploadedFile?.type"></p>
                                </div>
                            </div>
                            <button @click="confirmMediaSelection()" 
                                    type="button" 
                                    class="px-5 py-2 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm cursor-pointer">
                                Gunakan File Ini
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
