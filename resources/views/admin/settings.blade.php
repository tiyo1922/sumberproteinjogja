@extends('layouts.admin', [
    'title' => 'Site Settings',
    'pageTitle' => 'Site & Contact Settings'
])

@section('content')
<script>
window.__initialSettingsData = {!! json_encode($settingsData) !!};
window.__initialMediaLibrary = {!! json_encode($mediaLibrary) !!};

window.adminSettingsManager = function(initialPayload) {
    const payload = initialPayload || {};
    return {
        csrfToken: payload.csrfToken || '',
        updateRoute: payload.updateRoute || '',
        avatarUpdateRoute: payload.avatarUpdateRoute || '',
        nameUpdateRoute: payload.nameUpdateRoute || '',
        mediaUploadRoute: payload.mediaUploadRoute || '{{ route('admin.media.upload') }}',
        isSaving: false,
        isSavingAvatar: false,
        avatarSuccessMsg: '',
        avatarErrorMsg: '',
        isSavingName: false,
        nameSuccessMsg: '',
        nameErrorMsg: '',
        adminName: (window.__initialSettingsData?.admin_user?.name) || '{{ addslashes(auth()->user()?->name ?? '') }}',
        isSavingEmail: false,
        emailSuccessMsg: '',
        emailErrorMsg: '',
        adminEmail: (window.__initialSettingsData?.admin_user?.email) || '{{ addslashes(auth()->user()?->email ?? '') }}',
        emailUpdateRoute: payload.emailUpdateRoute || '',
        isSavingPassword: false,
        passwordSuccessMsg: '',
        passwordErrorMsg: '',
        passwordForm: {
            current_password: '',
            password: '',
            password_confirmation: '',
        },
        showPasswords: {
            current: false,
            new: false,
            confirm: false,
        },
        passwordUpdateRoute: payload.passwordUpdateRoute || '',
        isUploadingMedia: false,
        settings: window.__initialSettingsData || {},
        mediaLibrary: window.__initialMediaLibrary || [],
        mediaPickerOpen: false,
        targetField: 'logo', // 'logo' | 'favicon' | 'avatar'
        activeTab: 'contact', // 'contact' | 'website' | 'profile'
        toastMessage: '',
        toastVisible: false,
        mediaTab: 'library', // 'library' | 'upload'
        mediaSearchQuery: '',
        mediaDeleteRoute: '{{ route('admin.media.delete') }}',
        isDeletingMedia: false,
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

        async deleteMedia(media) {
            if (!confirm('Apakah Anda yakin ingin menghapus file "' + media.filename + '" secara permanen dari server?')) {
                return;
            }
            this.isDeletingMedia = true;
            try {
                const response = await fetch(this.mediaDeleteRoute || '{{ route('admin.media.delete') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ path: media.path })
                });
                const result = await response.json();
                if (response.ok && result.success) {
                    this.mediaLibrary = this.mediaLibrary.filter(m => m.id !== media.id && m.path !== media.path);
                    if (this.selectedMedia && this.selectedMedia.path === media.path) {
                        this.selectedMedia = null;
                    }
                    this.showToast(result.message || 'File media berhasil dihapus dari server!');
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

        // Contact Registry State
        contactModalOpen: false,
        contactEditingIndex: null,
        contactForm: {
            id: '',
            key: '',
            name: '',
            division: '',
            type: 'whatsapp',
            value: '',
            description: '',
            active: true,
            is_system: false,
        },
        
        showToast(msg) {
            this.toastMessage = msg;
            this.toastVisible = true;
            setTimeout(() => { this.toastVisible = false; }, 3000);
        },

        openCreateContactModal() {
            this.contactEditingIndex = null;
            this.contactForm = {
                id: 'contact_' + Date.now(),
                key: '',
                name: '',
                division: '',
                type: 'whatsapp',
                value: '',
                description: '',
                active: true,
                is_system: false,
            };
            this.contactModalOpen = true;
        },

        openEditContactModal(index) {
            this.contactEditingIndex = index;
            const item = this.settings.contacts[index];
            this.contactForm = {
                id: item.id || ('contact_' + Date.now()),
                key: item.key || '',
                name: item.name || '',
                division: item.division || '',
                type: item.type || 'whatsapp',
                value: item.value || '',
                description: item.description || '',
                active: item.active !== false,
                is_system: item.is_system || false,
            };
            this.contactModalOpen = true;
        },

        saveContactFromModal() {
            if (!this.contactForm.name.trim()) {
                alert('Nama kontak wajib diisi!');
                return;
            }
            if (!this.contactForm.value.trim()) {
                alert('Nomor / nilai kontak wajib diisi!');
                return;
            }
            if (!this.contactForm.key.trim()) {
                this.contactForm.key = this.contactForm.name.toLowerCase().replace(/[^a-z0-9_]/g, '_');
            }

            if (!this.settings.contacts) this.settings.contacts = [];

            if (this.contactEditingIndex !== null) {
                this.settings.contacts[this.contactEditingIndex] = { ...this.contactForm };
                this.showToast('Kontak berhasil diperbarui.');
            } else {
                this.settings.contacts.push({ ...this.contactForm });
                this.showToast('Kontak baru berhasil ditambahkan.');
            }
            this.contactModalOpen = false;
        },

        deleteContact(index) {
            const item = this.settings.contacts[index];
            if (item.is_system) {
                alert('Kontak sistem default tidak dapat dihapus, namun dapat dinonaktifkan.');
                return;
            }
            if (confirm('Hapus kontak "' + item.name + '" dari registry?')) {
                this.settings.contacts.splice(index, 1);
                this.showToast('Kontak berhasil dihapus.');
            }
        },

        toggleContactActive(index) {
            if (this.settings.contacts[index]) {
                this.settings.contacts[index].active = !this.settings.contacts[index].active;
                this.showToast('Status kontak diubah menjadi ' + (this.settings.contacts[index].active ? 'Aktif' : 'Nonaktif'));
            }
        },
        
        openMediaPicker(field) {
            this.targetField = field;
            this.mediaTab = 'library';
            let currentPath = '';
            if (field === 'logo') currentPath = this.settings.brand ? this.settings.brand.logo_url : '';
            else if (field === 'favicon') currentPath = this.settings.brand ? this.settings.brand.favicon_url : '';
            else if (field === 'avatar') currentPath = this.settings.admin_user ? this.settings.admin_user.avatar_image : '';
            
            this.selectedMedia = this.mediaLibrary.find(m => m.path === currentPath) || this.mediaLibrary[0] || null;
            this.uploadedFile = null;
            this.uploadedPreviewUrl = null;
            this.isUploadingMedia = false;
            this.mediaPickerOpen = true;
        },
        
        selectMedia(media) {
            this.selectedMedia = media;
        },
        
        confirmMediaSelection() {
            let chosenUrl = '';
            if (this.mediaTab === 'library' && this.selectedMedia) {
                chosenUrl = this.selectedMedia.path;
            } else if (this.mediaTab === 'upload' && this.uploadedFile && this.uploadedFile.path) {
                chosenUrl = this.uploadedFile.path;
            } else if (this.selectedMedia) {
                chosenUrl = this.selectedMedia.path;
            }

            if (!chosenUrl) {
                this.showToast('Silakan pilih atau tunggu proses unggah file selesai.');
                return;
            }
            
            if (this.targetField === 'logo') {
                if (!this.settings.brand) this.settings.brand = {};
                this.settings.brand.logo_url = chosenUrl;
                this.showToast('Logo website berhasil dipilih!');
            } else if (this.targetField === 'favicon') {
                if (!this.settings.brand) this.settings.brand = {};
                this.settings.brand.favicon_url = chosenUrl;
                this.showToast('Favicon website berhasil dipilih!');
            } else if (this.targetField === 'avatar') {
                if (!this.settings.admin_user) this.settings.admin_user = {};
                this.settings.admin_user.avatar_image = chosenUrl;
                this.showToast('Avatar admin berhasil dipilih!');
            }
            this.mediaPickerOpen = false;
        },
        
        async handleFileUpload(e) {
            const file = e.target.files ? e.target.files[0] : (e.dataTransfer ? e.dataTransfer.files[0] : null);
            if (!file) return;

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
                        'X-CSRF-TOKEN': this.csrfToken || '{{ csrf_token() }}',
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
        
        async saveSettings() {
            if (this.isSaving) return;
            this.isSaving = true;

            try {
                const response = await fetch(payload.updateRoute || '{{ route('admin.settings.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        site: this.settings
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
                        : (result.message || 'Gagal menyimpan pengaturan Site & Contact.');
                    alert(errorMsg);
                    return;
                }

                if (result.settings) {
                    this.settings = { ...this.settings, ...result.settings };
                }

                this.showToast(result.message || 'Pengaturan Site & Contact berhasil disimpan ke database.');
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat menyimpan pengaturan Site & Contact.');
            } finally {
                this.isSaving = false;
            }
        },

        async saveAvatar() {
            if (this.isSavingAvatar) return;
            this.isSavingAvatar = true;
            this.avatarSuccessMsg = '';
            this.avatarErrorMsg = '';

            try {
                const avatarPath = this.settings.admin_user?.avatar_image || '';
                const response = await fetch(payload.avatarUpdateRoute || '{{ route('admin.profile.avatar.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        avatar: avatarPath
                    }),
                });

                const result = await response.json().catch(() => ({}));

                if (response.ok && result.success) {
                    this.avatarSuccessMsg = result.message || 'Avatar profil administrator berhasil disimpan!';
                    this.showToast(this.avatarSuccessMsg);
                    // Update global sidebar/topbar avatar elements in DOM if present
                    const avatarImgs = document.querySelectorAll('.admin-user-avatar-img');
                    avatarImgs.forEach(img => {
                        if (result.avatar_url) img.src = result.avatar_url;
                    });
                } else {
                    this.avatarErrorMsg = result.message || 'Gagal menyimpan avatar.';
                    this.showToast(this.avatarErrorMsg);
                }
            } catch (err) {
                console.error(err);
                this.avatarErrorMsg = 'Terjadi kesalahan jaringan saat menyimpan avatar.';
                this.showToast(this.avatarErrorMsg);
            } finally {
                this.isSavingAvatar = false;
            }
        },

        async saveName() {
            if (this.isSavingName) return;
            const trimmedName = (this.adminName || '').trim();
            if (trimmedName.length < 2) {
                this.nameErrorMsg = 'Nama administrator minimal 2 karakter.';
                this.showToast(this.nameErrorMsg);
                return;
            }
            this.isSavingName = true;
            this.nameSuccessMsg = '';
            this.nameErrorMsg = '';

            try {
                const response = await fetch(payload.nameUpdateRoute || '{{ route('admin.profile.name.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        name: trimmedName
                    }),
                });

                const result = await response.json().catch(() => ({}));

                if (response.ok && result.success) {
                    this.nameSuccessMsg = result.message || 'Nama administrator berhasil diperbarui!';
                    this.showToast(this.nameSuccessMsg);
                    this.adminName = result.name;
                    if (this.settings.admin_user) {
                        this.settings.admin_user.name = result.name;
                    }
                    // Update sidebar admin name dynamically
                    const sidebarNames = document.querySelectorAll('.admin-user-name-text');
                    sidebarNames.forEach(el => {
                        el.innerText = result.name;
                    });
                } else {
                    const err = result.errors 
                        ? Object.values(result.errors).flat().join('\n') 
                        : (result.message || 'Gagal menyimpan nama administrator.');
                    this.nameErrorMsg = err;
                    this.showToast(err);
                }
            } catch (err) {
                console.error(err);
                this.nameErrorMsg = 'Terjadi kesalahan jaringan saat menyimpan nama.';
                this.showToast(this.nameErrorMsg);
            } finally {
                this.isSavingName = false;
            }
        },

        async saveEmail() {
            if (this.isSavingEmail) return;
            const trimmedEmail = (this.adminEmail || '').trim().toLowerCase();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!trimmedEmail || !emailRegex.test(trimmedEmail)) {
                this.emailErrorMsg = 'Format email administrator tidak valid.';
                this.showToast(this.emailErrorMsg);
                return;
            }
            this.isSavingEmail = true;
            this.emailSuccessMsg = '';
            this.emailErrorMsg = '';

            try {
                const response = await fetch(payload.emailUpdateRoute || '{{ route('admin.profile.email.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        email: trimmedEmail
                    }),
                });

                const result = await response.json().catch(() => ({}));

                if (response.ok && result.success) {
                    this.emailSuccessMsg = result.message || 'Email login administrator berhasil diperbarui!';
                    this.showToast(this.emailSuccessMsg);
                    this.adminEmail = result.email;
                    if (this.settings.admin_user) {
                        this.settings.admin_user.email = result.email;
                    }
                } else {
                    const err = result.errors 
                        ? Object.values(result.errors).flat().join('\n') 
                        : (result.message || 'Gagal menyimpan email administrator.');
                    this.emailErrorMsg = err;
                    this.showToast(err);
                }
            } catch (err) {
                console.error(err);
                this.emailErrorMsg = 'Terjadi kesalahan jaringan saat menyimpan email.';
                this.showToast(this.emailErrorMsg);
            } finally {
                this.isSavingEmail = false;
            }
        },

        async savePassword() {
            if (this.isSavingPassword) return;
            if (!this.passwordForm.current_password) {
                this.passwordErrorMsg = 'Password lama wajib diisi.';
                this.showToast(this.passwordErrorMsg);
                return;
            }
            if (!this.passwordForm.password) {
                this.passwordErrorMsg = 'Password baru wajib diisi.';
                this.showToast(this.passwordErrorMsg);
                return;
            }
            if (this.passwordForm.password.length < 8) {
                this.passwordErrorMsg = 'Password baru minimal 8 karakter.';
                this.showToast(this.passwordErrorMsg);
                return;
            }
            if (this.passwordForm.password !== this.passwordForm.password_confirmation) {
                this.passwordErrorMsg = 'Konfirmasi password tidak sesuai.';
                this.showToast(this.passwordErrorMsg);
                return;
            }

            this.isSavingPassword = true;
            this.passwordSuccessMsg = '';
            this.passwordErrorMsg = '';

            try {
                const response = await fetch(payload.passwordUpdateRoute || '{{ route('admin.profile.password.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify(this.passwordForm),
                });

                const result = await response.json().catch(() => ({}));

                if (response.ok && result.success) {
                    this.passwordSuccessMsg = result.message || 'Sandi berhasil diperbarui.';
                    this.showToast(this.passwordSuccessMsg);
                    // Reset input fields on success
                    this.passwordForm.current_password = '';
                    this.passwordForm.password = '';
                    this.passwordForm.password_confirmation = '';
                } else {
                    const err = result.errors 
                        ? Object.values(result.errors).flat().join('\n') 
                        : (result.message || 'Gagal memperbarui sandi administrator.');
                    this.passwordErrorMsg = err;
                    this.showToast(err);
                }
            } catch (err) {
                console.error(err);
                this.passwordErrorMsg = 'Terjadi kesalahan jaringan saat memperbarui sandi.';
                this.showToast(this.passwordErrorMsg);
            } finally {
                this.isSavingPassword = false;
            }
        },
        
        getImageUrl(path) {
            if (!path) return '/images/hero-1.jpg';
            if (path.startsWith('blob:') || path.startsWith('http')) return path;
            return path.startsWith('/') ? path : '/' + path;
        }
    };
};
</script>

<div class="space-y-6"
     x-data="adminSettingsManager({
         csrfToken: '{{ csrf_token() }}',
         updateRoute: '{{ route('admin.settings.update') }}',
         avatarUpdateRoute: '{{ route('admin.profile.avatar.update') }}',
         nameUpdateRoute: '{{ route('admin.profile.name.update') }}',
         emailUpdateRoute: '{{ route('admin.profile.email.update') }}',
         passwordUpdateRoute: '{{ route('admin.profile.password.update') }}',
         mediaUploadRoute: '{{ route('admin.media.upload') }}'
     })">
    
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
                </div>
                <div class="text-xs text-gray-500 font-medium">
                    • Pusat Nomor WhatsApp, Brand & Profil Admin
                </div>
                <p class="text-xs sm:text-sm text-gray-500 leading-relaxed max-w-3xl">
                    Kelola pusat nomor WhatsApp pemesanan dan komunikasi admin, identitas brand utama, logo, favicon, serta akun administrator dengan <strong>Global Media Picker</strong>.
                </p>
            </div>

            <!-- Save Action Button (Hidden on Profile Tab as each section has its own save button) -->
            <div x-show="activeTab !== 'profile'" class="flex items-center gap-3 shrink-0">
                <button @click="saveSettings()" 
                        :disabled="isSaving"
                        type="button"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                    <svg x-show="isSaving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Pengaturan'">Simpan Pengaturan</span>
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
            🌐 Identitas Brand
        </button>
        <button @click="activeTab = 'profile'" 
                type="button"
                :class="activeTab === 'profile' ? 'bg-brand-primary text-white font-bold shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                class="px-4 py-2 rounded-modern text-xs transition-all cursor-pointer">
            👤 Profil Administrator
        </button>
    </div>

    <!-- 3. Tab Contents Grid (For Contact & Brand Website) -->
    <div x-show="activeTab !== 'profile'" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Form Fields (7 cols on lg) -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Tab 1: CONTACT REGISTRY (CENTRALIZED MASTER SOURCE) -->
            <div x-show="activeTab === 'contact'" class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-6">
                
                <!-- Contact Registry Section Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-base">📇</span>
                            <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                                Master Contact Registry
                            </h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                Single Source of Truth
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                            Definisikan nomor & kontak satu kali di sini. Section lain (Hero, Knowledge, Mutu, dsb) cukup memilih nama divisi melalui dropdown reference.
                        </p>
                    </div>
                    
                    <button @click="openCreateContactModal()"
                            type="button"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-modern text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs hover:shadow transition-all cursor-pointer shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>+ Tambah Kontak</span>
                    </button>
                </div>

                <!-- Contact Registry Cards List -->
                <div class="space-y-3">
                    <template x-for="(contact, cIdx) in (settings.contacts || [])" :key="contact.id || contact.key">
                        <div class="p-4 rounded-modern border transition-all duration-150"
                             :class="contact.active === false ? 'bg-gray-50/80 border-gray-200 opacity-75' : 'bg-white border-gray-200 hover:border-emerald-300 shadow-2xs'">
                            
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                
                                <!-- Contact Details Left -->
                                <div class="space-y-1 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-sm">
                                            <span x-show="contact.type === 'whatsapp'">💬</span>
                                            <span x-show="contact.type === 'phone'">📞</span>
                                            <span x-show="contact.type === 'email'">✉️</span>
                                        </span>
                                        <span class="font-bold text-xs text-brand-dark" x-text="contact.name || 'Kontak Tanpa Nama'"></span>
                                        
                                        <!-- Channel Type Badge -->
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider"
                                              :class="{
                                                  'bg-emerald-50 text-emerald-700 border border-emerald-200': contact.type === 'whatsapp',
                                                  'bg-blue-50 text-blue-700 border border-blue-200': contact.type === 'phone',
                                                  'bg-purple-50 text-purple-700 border border-purple-200': contact.type === 'email',
                                                  'bg-gray-100 text-gray-700 border border-gray-200': !['whatsapp','phone','email'].includes(contact.type)
                                              }"
                                              x-text="contact.type"></span>
                                        
                                        <!-- Core Key Marker (System Sync) -->
                                        <template x-if="contact.key">
                                            <span class="px-1.5 py-0.2 text-[9px] font-mono text-amber-800 bg-amber-50 border border-amber-200 rounded"
                                                  title="Sinkronisasi Kunci Inti Sistem"
                                                  x-text="'key: ' + contact.key"></span>
                                        </template>

                                        <!-- Division Marker -->
                                        <template x-if="contact.division">
                                            <span class="text-[10px] text-gray-500 font-medium" x-text="'• Divisi ' + contact.division"></span>
                                        </template>
                                    </div>
                                    
                                    <!-- Value / Number with formatted copy -->
                                    <div class="flex items-center gap-2 pt-0.5">
                                        <span class="font-mono text-xs font-semibold text-gray-800 bg-gray-50 px-2 py-0.5 rounded border border-gray-200/80" 
                                              x-text="contact.value"></span>
                                        <template x-if="contact.note">
                                            <span class="text-[10px] text-gray-400 italic" x-text="'— ' + contact.note"></span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Action Buttons Right -->
                                <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-center">
                                    <!-- Toggle Active -->
                                    <button @click="toggleContactActive(cIdx)" 
                                            type="button" 
                                            :class="contact.active === false ? 'text-gray-500 bg-gray-100 hover:bg-gray-200 border-gray-300' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border-emerald-200'"
                                            class="px-2.5 py-1 rounded text-xs font-bold border shadow-2xs transition-all cursor-pointer">
                                        <span x-text="contact.active === false ? 'Nonaktif' : 'Aktif'"></span>
                                    </button>

                                    <!-- Edit Button -->
                                    <button @click="openEditContactModal(contact, cIdx)" 
                                            type="button" 
                                            class="px-2.5 py-1 rounded text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 shadow-2xs transition-all cursor-pointer">
                                        Edit
                                    </button>

                                    <!-- Delete Button -->
                                    <button @click="deleteContact(cIdx)" 
                                            type="button" 
                                            :disabled="contact.is_system"
                                            :class="contact.is_system ? 'opacity-30 cursor-not-allowed text-gray-400 border-gray-200' : 'text-rose-600 hover:bg-rose-50 border-rose-200 hover:border-rose-300 cursor-pointer'"
                                            class="px-2.5 py-1 rounded text-xs font-bold bg-white border shadow-2xs transition-all">
                                        Hapus
                                    </button>
                                </div>

                            </div>
                        </div>
                    </template>
                </div>

                <!-- Info Box: Synchronized Channels -->
                <div class="p-4 rounded-modern bg-amber-50/70 border border-amber-200/80 space-y-2">
                    <div class="flex items-center gap-2 text-xs font-bold text-amber-900">
                        <span>⚡</span>
                        <span>Auto-Synchronized Core Channels</span>
                    </div>
                    <p class="text-[11px] text-amber-800 leading-relaxed">
                        Nilai kontak utama (<code class="font-bold">order_wa</code>, <code class="font-bold">admin_wa</code>, <code class="font-bold">main_phone</code>, <code class="font-bold">official_email</code>) di dalam Contact Registry otomatis terintegrasi dengan field transaksi & footer landing page secara instan.
                    </p>
                </div>

            </div>

            <!-- Tab 2: Brand Identity -->
            <div x-show="activeTab === 'website'" class="bg-white rounded-modern-xl border border-gray-200/80 p-6 shadow-2xs space-y-5">
                <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider border-b border-gray-100 pb-2">
                    Identitas Brand Utama
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Nama Brand Utama <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               x-model="settings.brand.name" 
                               placeholder="Sumber Protein Jogja" 
                               class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Tagline / Slogan Brand
                        </label>
                        <input type="text" 
                               x-model="settings.brand.tagline" 
                               placeholder="Bahan Masak Siap Olah, Tinggal Masak." 
                               class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium">
                    </div>
                </div>

                <!-- Logo & Favicon with Global Media Picker -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                    <div class="p-3 bg-gray-50 rounded-modern border border-gray-200 space-y-2">
                        <label class="block text-xs font-bold text-brand-dark">
                            Logo Brand Website
                        </label>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded bg-brand-dark flex items-center justify-center overflow-hidden border border-gray-300 shrink-0">
                                <img :src="getImageUrl(settings.brand.logo_url)" alt="Logo" class="w-full h-full object-cover">
                            </div>
                            <button @click="openMediaPicker('logo')" 
                                    type="button" 
                                    class="px-3 py-1.5 rounded-modern text-xs font-bold text-brand-dark bg-white border border-gray-300 hover:bg-gray-100 shadow-2xs transition-all cursor-pointer">
                                🖼️ Ganti Logo
                            </button>
                        </div>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-modern border border-gray-200 space-y-2">
                        <label class="block text-xs font-bold text-brand-dark">
                            Favicon Tab Browser
                        </label>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded bg-white flex items-center justify-center overflow-hidden border border-gray-300 shrink-0 shadow-2xs">
                                <img :src="getImageUrl(settings.brand.favicon_url)" alt="Favicon" class="w-7 h-7 object-contain">
                            </div>
                            <button @click="openMediaPicker('favicon')" 
                                    type="button" 
                                    class="px-3 py-1.5 rounded-modern text-xs font-bold text-brand-dark bg-white border border-gray-300 hover:bg-gray-100 shadow-2xs transition-all cursor-pointer">
                                🖼️ Ganti Favicon
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Right: PREVIEWS (5 cols on lg) for Contact & Brand -->
        <div class="lg:col-span-5 space-y-4 sticky top-4">
            <label class="block text-xs font-extrabold text-brand-dark uppercase tracking-wider">
                Preview
            </label>

            <!-- Brand Summary Card Replica -->
            <div class="bg-white rounded-modern-xl border border-gray-200/80 p-5 shadow-2xs space-y-4">
                
                <!-- Mock Header Branding -->
                <div class="p-4 rounded-modern bg-brand-dark text-white flex items-center gap-3">
                    <div class="w-10 h-10 flex items-center justify-center shrink-0">
                        <img :src="getImageUrl(settings.brand.logo_url)" alt="Logo" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-white" x-text="settings.brand.name || 'Sumber Protein Jogja'"></h4>
                        <p class="text-[10px] text-emerald-400 font-semibold" x-text="settings.brand.tagline"></p>
                    </div>
                </div>

                <!-- Contact Registry Live List Overview -->
                <div class="p-3.5 bg-gray-50 rounded-modern border border-gray-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] uppercase font-bold text-gray-500">Kanal Terdaftar di Registry:</span>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200"
                              x-text="(settings.contacts ? settings.contacts.filter(c => c.active !== false).length : 0) + ' Aktif'"></span>
                    </div>

                    <div class="space-y-1.5 text-xs max-h-60 overflow-y-auto pr-1">
                        <template x-for="contact in (settings.contacts || [])" :key="contact.id || contact.key">
                            <div class="flex items-center justify-between p-2 rounded bg-white border border-gray-200"
                                 :class="contact.active === false ? 'opacity-50' : ''">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs">
                                        <span x-show="contact.type === 'whatsapp'">💬</span>
                                        <span x-show="contact.type === 'phone'">📞</span>
                                        <span x-show="contact.type === 'email'">✉️</span>
                                    </span>
                                    <div>
                                        <p class="text-[11px] font-bold text-brand-dark" x-text="contact.name"></p>
                                        <p class="text-[9px] text-gray-400 font-mono" x-text="contact.division || 'Umum'"></p>
                                    </div>
                                </div>
                                <span class="font-mono font-bold text-[11px]" 
                                      :class="contact.type === 'whatsapp' ? 'text-emerald-700' : (contact.type === 'phone' ? 'text-blue-700' : 'text-purple-700')"
                                      x-text="contact.value"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Mock Browser Tab -->
                <div class="p-3 bg-gray-100 rounded-modern border border-gray-200 space-y-1">
                    <span class="text-[10px] uppercase font-bold text-gray-400">Pratinjau Tab Browser:</span>
                    <div class="bg-white px-3 py-1.5 rounded border border-gray-300 flex items-center gap-2 text-xs font-medium text-gray-700 shadow-2xs">
                        <img :src="getImageUrl(settings.brand.favicon_url)" alt="Favicon" class="w-4 h-4 rounded-xs object-cover">
                        <span class="truncate" x-text="(settings.brand.name || 'Sumber Protein Jogja') + ' — Beranda'"></span>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Tab 3: Administrator Profile (Dedicated Single-Column Container) -->
    <div x-show="activeTab === 'profile'" class="max-w-4xl space-y-6">
        <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-8 shadow-2xs space-y-6">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h3 class="text-sm font-extrabold text-brand-dark uppercase tracking-wider">
                        Profil Administrator Utama
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Kelola identitas foto profil, nama lengkap, email login, dan kata sandi akun administrator.
                    </p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-brand-soft-green text-brand-primary border border-brand-soft-green-border">
                    Identitas Akun
                </span>
            </div>

            <!-- Avatar Success / Error Feedback Alerts -->
            <template x-if="avatarSuccessMsg">
                <div class="p-3.5 rounded-modern bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                    <span>✓</span>
                    <span x-text="avatarSuccessMsg"></span>
                </div>
            </template>

            <template x-if="avatarErrorMsg">
                <div class="p-3.5 rounded-modern bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                    <span>✕</span>
                    <span x-text="avatarErrorMsg"></span>
                </div>
            </template>

            <!-- Name Success / Error Feedback Alerts -->
            <template x-if="nameSuccessMsg">
                <div class="p-3.5 rounded-modern bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                    <span>✓</span>
                    <span x-text="nameSuccessMsg"></span>
                </div>
            </template>

            <template x-if="nameErrorMsg">
                <div class="p-3.5 rounded-modern bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                    <span>✕</span>
                    <span x-text="nameErrorMsg"></span>
                </div>
            </template>

            <!-- Email Success / Error Feedback Alerts -->
            <template x-if="emailSuccessMsg">
                <div class="p-3.5 rounded-modern bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                    <span>✓</span>
                    <span x-text="emailSuccessMsg"></span>
                </div>
            </template>

            <template x-if="emailErrorMsg">
                <div class="p-3.5 rounded-modern bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                    <span>✕</span>
                    <span x-text="emailErrorMsg"></span>
                </div>
            </template>

            <!-- Password Success / Error Feedback Alerts -->
            <template x-if="passwordSuccessMsg">
                <div class="p-3.5 rounded-modern bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                    <span>✓</span>
                    <span x-text="passwordSuccessMsg"></span>
                </div>
            </template>

            <template x-if="passwordErrorMsg">
                <div class="p-3.5 rounded-modern bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                    <span>✕</span>
                    <span x-text="passwordErrorMsg"></span>
                </div>
            </template>

            <!-- 1. Admin Avatar Section -->
            <div class="p-4 bg-gray-50 rounded-modern border border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-brand-dark border-2 border-brand-primary flex items-center justify-center overflow-hidden shrink-0 shadow-md">
                        <img :src="getImageUrl(settings.admin_user.avatar_image)" alt="Avatar" class="w-full h-full object-cover">
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-xs font-bold text-brand-dark">Foto Profil Super Admin</h4>
                        <p class="text-[11px] text-gray-500">Pilih atau unggah foto avatar baru untuk identitas administrator.</p>
                        <div class="flex items-center gap-2 pt-1">
                            <button @click="openMediaPicker('avatar')" 
                                    type="button" 
                                    class="px-3 py-1.5 rounded-modern text-xs font-bold text-brand-dark bg-white border border-gray-300 hover:bg-gray-100 shadow-2xs transition-all cursor-pointer">
                                🖼️ Pilih Foto Avatar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Dedicated Save Avatar Button -->
                <div class="shrink-0 self-end sm:self-auto">
                    <button @click="saveAvatar()" 
                            type="button" 
                            :disabled="isSavingAvatar"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="isSavingAvatar" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isSavingAvatar ? 'Menyimpan...' : 'Simpan Avatar'">Simpan Avatar</span>
                    </button>
                </div>
            </div>

            <!-- 2. Admin Name Section (Single Source of Truth: users.name) -->
            <div class="p-4 bg-gray-50 rounded-modern border border-gray-200 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
                    <div class="flex-1 space-y-1">
                        <label class="block text-xs font-bold text-brand-dark">
                            Nama Lengkap Administrator <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-[11px] text-gray-500">Nama ini disimpan langsung ke tabel akun (<code>users.name</code>) dan ditampilkan pada Sidebar panel admin.</p>
                        <input type="text" 
                               x-model="adminName" 
                               placeholder="Contoh: Admin Sumber Protein"
                               class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                    </div>
                    <div class="shrink-0 self-end sm:self-auto">
                        <button @click="saveName()" 
                                type="button" 
                                :disabled="isSavingName"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg x-show="isSavingName" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="isSavingName ? 'Menyimpan...' : 'Simpan Nama'">Simpan Nama</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3. Admin Email Section (Single Source of Truth: users.email) -->
            <div class="p-4 bg-gray-50 rounded-modern border border-gray-200 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
                    <div class="flex-1 space-y-1">
                        <label class="block text-xs font-bold text-brand-dark">
                            Email Login Administrator <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-[11px] text-gray-500">Email ini digunakan sebagai kredensial login utama (<code>users.email</code>) ke panel admin.</p>
                        <input type="email" 
                               x-model="adminEmail" 
                               placeholder="admin@sumberproteinjogja.com"
                               class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                    </div>
                    <div class="shrink-0 self-end sm:self-auto">
                        <button @click="saveEmail()" 
                                type="button" 
                                :disabled="isSavingEmail"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg x-show="isSavingEmail" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="isSavingEmail ? 'Menyimpan...' : 'Simpan Email'">Simpan Email</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 4. Admin Password Section (Single Source of Truth: users.password) -->
            <div class="p-4 bg-gray-50 rounded-modern border border-gray-200 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-200/60 pb-2">
                    <div>
                        <h4 class="text-xs font-bold text-brand-dark">Ubah Sandi / Password Akun</h4>
                        <p class="text-[11px] text-gray-500">Gunakan kombinasi minimal 8 karakter yang aman untuk melindungi akses panel administrator.</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-200/70 text-gray-700">
                        Bcrypt Hash
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Current Password -->
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Password Lama <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showPasswords.current ? 'text' : 'password'" 
                                   x-model="passwordForm.current_password" 
                                   placeholder="Password saat ini"
                                   autocomplete="current-password"
                                   class="w-full text-xs rounded-modern border border-gray-300 p-2.5 pr-9 bg-white font-medium focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                            <button type="button" 
                                    @click="showPasswords.current = !showPasswords.current"
                                    :aria-label="showPasswords.current ? 'Sembunyikan password' : 'Tampilkan password'"
                                    class="absolute inset-y-0 right-0 w-9 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-hidden focus:text-brand-primary cursor-pointer transition-colors">
                                <svg x-show="!showPasswords.current" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPasswords.current" x-cloak class="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Password Baru <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showPasswords.new ? 'text' : 'password'" 
                                   x-model="passwordForm.password" 
                                   placeholder="Min. 8 karakter"
                                   autocomplete="new-password"
                                   class="w-full text-xs rounded-modern border border-gray-300 p-2.5 pr-9 bg-white font-medium focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                            <button type="button" 
                                    @click="showPasswords.new = !showPasswords.new"
                                    :aria-label="showPasswords.new ? 'Sembunyikan password' : 'Tampilkan password'"
                                    class="absolute inset-y-0 right-0 w-9 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-hidden focus:text-brand-primary cursor-pointer transition-colors">
                                <svg x-show="!showPasswords.new" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPasswords.new" x-cloak class="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Konfirmasi Password Baru <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showPasswords.confirm ? 'text' : 'password'" 
                                   x-model="passwordForm.password_confirmation" 
                                   placeholder="Ulangi password baru"
                                   autocomplete="new-password"
                                   class="w-full text-xs rounded-modern border border-gray-300 p-2.5 pr-9 bg-white font-medium focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                            <button type="button" 
                                    @click="showPasswords.confirm = !showPasswords.confirm"
                                    :aria-label="showPasswords.confirm ? 'Sembunyikan password' : 'Tampilkan password'"
                                    class="absolute inset-y-0 right-0 w-9 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-hidden focus:text-brand-primary cursor-pointer transition-colors">
                                <svg x-show="!showPasswords.confirm" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPasswords.confirm" x-cloak class="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-1">
                    <button @click="savePassword()" 
                            type="button" 
                            :disabled="isSavingPassword"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="isSavingPassword" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isSavingPassword ? 'Menyimpan...' : 'Simpan Sandi'">Simpan Sandi</span>
                    </button>
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
                        Upload File Baru
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
                                 class="group relative aspect-[4/3] rounded-modern overflow-hidden border-2 transition-all cursor-pointer bg-brand-dark"
                                 :class="selectedMedia?.id === media.id ? 'border-brand-primary ring-2 ring-emerald-400' : 'border-gray-200 hover:border-gray-400'">
                                <img :src="getImageUrl(media.path)" :alt="media.title" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent p-2 flex flex-col justify-between">
                                    <div class="flex items-center justify-between w-full">
                                        <div>
                                            <template x-if="media.is_deletable">
                                                <button @click.stop="deleteMedia(media)" 
                                                        type="button" 
                                                        title="Hapus media dari server" 
                                                        class="p-1 rounded bg-rose-600/90 text-white hover:bg-rose-700 hover:scale-110 shadow-xs transition-all cursor-pointer flex items-center justify-center">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </template>
                                        </div>
                                        <div x-show="selectedMedia?.id === media.id">
                                            <span class="w-5 h-5 rounded-full bg-brand-primary text-white flex items-center justify-center text-xs font-bold shadow-sm">
                                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </span>
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
                        <div class="space-y-2 flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full bg-brand-soft-green text-brand-primary flex items-center justify-center shadow-xs">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <p class="text-xs font-bold text-brand-dark">Tarik & Lepaskan file ke sini, atau klik untuk memilih file</p>
                            <p class="text-[11px] text-gray-400">Mendukung PNG, SVG, JPG, WebP, ICO</p>
                        </div>
                    </label>

                    <template x-if="isUploadingMedia">
                        <div class="p-3 bg-blue-50 rounded-modern border border-blue-200 flex items-center gap-3 text-xs text-blue-700 font-semibold animate-pulse">
                            <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Sedang mengunggah file ke storage server...</span>
                        </div>
                    </template>

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

    <!-- ======================================================= -->
    <!-- 5. CONTACT REGISTRY ADD / EDIT MODAL                     -->
    <!-- ======================================================= -->
    <div x-show="contactModalOpen" 
         x-cloak
         class="fixed inset-0 z-[80] overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        
        <div class="fixed inset-0 bg-black/75 backdrop-blur-xs" @click="contactModalOpen = false"></div>

        <div class="min-h-full flex items-center justify-center p-3 sm:p-6">
            <div class="relative bg-white rounded-modern-xl max-w-lg w-full p-6 shadow-2xl border border-gray-200 overflow-hidden my-6 space-y-5">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">📇</span>
                        <div>
                            <h3 class="text-base font-extrabold text-brand-dark" x-text="contactEditingIndex !== null ? 'Edit Kontak Registry' : 'Tambah Kontak Baru'"></h3>
                            <p class="text-xs text-gray-500">Kelola rincian channel komunikasi dan pemilik divisi.</p>
                        </div>
                    </div>
                    <button @click="contactModalOpen = false" 
                            type="button" 
                            class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body Form -->
                <div class="space-y-4 text-xs">
                    
                    <!-- Label / Nama Kontak -->
                    <div>
                        <label class="block font-bold text-brand-dark mb-1">
                            Nama / Label Kontak <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               x-model="contactForm.name" 
                               placeholder="Contoh: Customer Service & Konsultasi Admin" 
                               class="w-full rounded-modern border border-gray-300 p-2.5 bg-white font-medium focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                    </div>

                    <!-- Divisi & Key Referensi -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-brand-dark mb-1">
                                Divisi / Pemilik Kontak <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   x-model="contactForm.division" 
                                   placeholder="Contoh: Customer Care / Pemesanan" 
                                   class="w-full rounded-modern border border-gray-300 p-2.5 bg-white font-medium focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                        </div>
                        <div>
                            <label class="block font-bold text-brand-dark mb-1">
                                Reference Key (ID) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   x-model="contactForm.key" 
                                   :disabled="contactForm.is_system"
                                   placeholder="customer_service" 
                                   class="w-full rounded-modern border border-gray-300 p-2.5 font-mono font-bold text-brand-dark focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary"
                                   :class="contactForm.is_system ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white'">
                        </div>
                    </div>

                    <!-- Tipe Kontak & Nilai Kontak -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-brand-dark mb-1">
                                Tipe Saluran <span class="text-rose-500">*</span>
                            </label>
                            <select x-model="contactForm.type" 
                                    class="w-full rounded-modern border border-gray-300 p-2.5 bg-white font-medium focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                                <option value="whatsapp">💬 WhatsApp</option>
                                <option value="phone">📞 Telepon / Hotline</option>
                                <option value="email">✉️ Email</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-brand-dark mb-1">
                                Nilai Kontak / Nomor / Email <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   x-model="contactForm.value" 
                                   placeholder="6281234567890" 
                                   class="w-full rounded-modern border border-gray-300 p-2.5 bg-white font-mono font-bold text-brand-dark focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                        </div>
                    </div>

                    <!-- Keterangan / Deskripsi -->
                    <div>
                        <label class="block font-bold text-brand-dark mb-1">
                            Keterangan / Deskripsi Penggunaan
                        </label>
                        <input type="text" 
                               x-model="contactForm.description" 
                               placeholder="Contoh: Kanal WhatsApp konsultasi produk dan tanya stok." 
                               class="w-full rounded-modern border border-gray-300 p-2.5 bg-white">
                    </div>

                    <!-- Status Aktif -->
                    <div class="pt-2 flex items-center justify-between p-3 rounded-modern bg-gray-50 border border-gray-200">
                        <div>
                            <span class="font-bold text-brand-dark block">Status Aktif Kontak</span>
                            <span class="text-[11px] text-gray-500">Kontak aktif dapat dipilih oleh section lain di Landing Page.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="contactForm.active" class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                </div>

                <!-- Modal Footer Actions -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                    <button @click="contactModalOpen = false" 
                            type="button" 
                            class="px-4 py-2 rounded-modern font-bold text-xs text-gray-600 hover:bg-gray-100 transition-all cursor-pointer">
                        Batal
                    </button>
                    <button @click="saveContactFromModal()" 
                            type="button" 
                            class="px-5 py-2 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer">
                        Simpan Kontak
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
