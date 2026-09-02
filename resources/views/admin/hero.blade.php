@extends('layouts.admin', [
    'title' => 'Hero Slider',
    'pageTitle' => 'Hero Slider'
])

@section('content')
<script>
window.adminHeroManager = function(initialPayload) {
    const payload = initialPayload || {};
    return {
        drafts: payload.drafts || [],
        mediaLibrary: payload.mediaLibrary || [],
        partnerMediaLibrary: payload.partnerMediaLibrary || [],
        heroPartners: payload.heroPartners || {},
        contacts: payload.contacts || [],
        csrfToken: payload.csrfToken || '',
        updateRoute: payload.updateRoute || '',
        partnerUploadRoute: payload.partnerUploadRoute || '',
        partnerDeleteRoute: payload.partnerDeleteRoute || '',
        
        getContactByKey(key) {
            if (!key || !this.contacts) return null;
            return this.contacts.find(c => c.key === key || c.id === key) || null;
        },
        
        // Modals & Panels State
        editorModalOpen: false,
        mediaPickerOpen: false,
        partnerMediaPickerOpen: false,
        partnerUploading: false,
        isDraggingPartner: false,
        activateModalOpen: false,
        deleteModalOpen: false,
        
        // Media Picker Sub-state
        mediaPickerTab: 'library', // 'library' | 'upload'
        mediaSearchQuery: '',
        mediaDeleteRoute: '{{ route('admin.media.delete') }}',
        mediaUploadRoute: '{{ route('admin.media.upload') }}',
        isDeletingMedia: false,
        isUploadingMedia: false,
        selectedMediaItem: null,
        mediaPickerTargetIndex: null, // null = append new image, number = replace at index
        csrfToken: '{{ csrf_token() }}',

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
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ path: media.path })
                });
                const result = await response.json();
                if (response.ok && result.success) {
                    this.mediaLibrary = this.mediaLibrary.filter(m => m.id !== media.id && m.path !== media.path);
                    if (this.selectedMediaItem && this.selectedMediaItem.path === media.path) {
                        this.selectedMediaItem = null;
                    }
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
        
        // Upload Simulation State (HTML5 File API + URL.createObjectURL)
        uploadedMockImage: null,
        isDragging: false,
        
        // Preview & Notification State
        previewDevice: 'desktop', // 'desktop' | 'tablet' | 'mobile'
        previewBoxWidth: 640,
        previewObserver: null,
        currentSlide: 0,
        autoplayTimer: null,
        toastMessage: '',
        toastVisible: false,
        isEditingDraft: false,
        
        // Reference Viewport Dimensions (Landing Page Standard & iPhone 15)
        virtualDimensions: {
            desktop: { width: 1280, height: 720 },
            tablet:  { width: 1024, height: 768 },
            mobile:  { width: 393,  height: 852 }
        },
        
        get currentVirtualWidth() {
            return this.virtualDimensions[this.previewDevice]?.width || (this.previewDevice === 'mobile' ? 393 : 1280);
        },
        
        get currentVirtualHeight() {
            return this.virtualDimensions[this.previewDevice]?.height || (this.previewDevice === 'mobile' ? 852 : 720);
        },
        
        get currentFrameWidth() {
            const available = Math.max(300, this.previewBoxWidth || 640);
            if (this.previewDevice === 'desktop') {
                return available;
            } else if (this.previewDevice === 'tablet') {
                return Math.min(available, 540);
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
        
        startAutoplay() {
            this.stopAutoplay();
            if (!this.draftForm.images || this.draftForm.images.length <= 1) return;
            this.autoplayTimer = setInterval(() => {
                this.currentSlide = (this.currentSlide + 1) % this.draftForm.images.length;
            }, 5500);
        },
        
        stopAutoplay() {
            if (this.autoplayTimer) {
                clearInterval(this.autoplayTimer);
                this.autoplayTimer = null;
            }
        },
        
        goToSlide(index) {
            this.stopAutoplay();
            this.currentSlide = index;
            this.startAutoplay();
        },
        
        nextSlide() {
            this.stopAutoplay();
            if (!this.draftForm.images || this.draftForm.images.length <= 1) return;
            this.currentSlide = (this.currentSlide + 1) % this.draftForm.images.length;
            this.startAutoplay();
        },
        
        prevSlide() {
            this.stopAutoplay();
            if (!this.draftForm.images || this.draftForm.images.length <= 1) return;
            this.currentSlide = (this.currentSlide - 1 + this.draftForm.images.length) % this.draftForm.images.length;
            this.startAutoplay();
        },
        
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
            primary_cta_contact: 'order_wa',
            secondary_cta_text: 'Lihat Produk',
            secondary_cta_link: '#kategori',
            secondary_cta_contact: '',
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
            this.currentSlide = 0;
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
                primary_cta_contact: 'order_wa',
                secondary_cta_text: 'Lihat Produk',
                secondary_cta_link: '#kategori',
                secondary_cta_contact: '',
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
            this.startAutoplay();
            this.initPreviewObserver();
        },
        
        openEditDraftModal(draft) {
            this.isEditingDraft = true;
            this.previewDevice = 'desktop';
            this.currentSlide = 0;
            this.draftForm = JSON.parse(JSON.stringify(draft));
            if (!this.draftForm.primary_cta_contact) {
                if (this.draftForm.primary_cta_link && this.contacts.some(c => c.key === this.draftForm.primary_cta_link)) {
                    this.draftForm.primary_cta_contact = this.draftForm.primary_cta_link;
                } else if (this.draftForm.primary_cta_link && this.draftForm.primary_cta_link.startsWith('#')) {
                    this.draftForm.primary_cta_contact = '';
                } else {
                    this.draftForm.primary_cta_contact = 'order_wa';
                }
            }
            if (!this.draftForm.secondary_cta_contact) {
                this.draftForm.secondary_cta_contact = '';
            }
            // Ensure trust_items has 3 items
            if (!this.draftForm.trust_items || this.draftForm.trust_items.length === 0) {
                this.draftForm.trust_items = [
                    { id: 1, text: '100% Halal', active: true },
                    { id: 2, text: 'Cold Chain', active: true },
                    { id: 3, text: 'Kirim Se-Jogja', active: true }
                ];
            }
            this.editorModalOpen = true;
            this.startAutoplay();
            this.initPreviewObserver();
        },
        
        partnerModalOpen: false,
        isEditingPartner: false,
        partnerForm: {
            id: null,
            name: '',
            logo: '',
            is_active: true,
            sort_order: 1,
        },

        async saveHeroToDatabase(draftToSave) {
            try {
                const draft = draftToSave || this.draftForm;
                const response = await fetch(this.updateRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        hero: {
                            badge: draft.badge,
                            headline_prefix: draft.headline_prefix,
                            highlight: draft.highlight,
                            headline_suffix: draft.headline_suffix,
                            title: (draft.headline_prefix || '') + ' ' + (draft.highlight || '') + (draft.headline_suffix || ''),
                            subtitle: draft.description,
                            description: draft.description,
                            primary_cta_text: draft.primary_cta_text,
                            primary_cta_link: draft.primary_cta_link,
                            secondary_cta_text: draft.secondary_cta_text,
                            secondary_cta_link: draft.secondary_cta_link,
                            images: draft.images,
                        },
                        trust_items: (draft.trust_items || []).map((t, idx) => ({
                            id: t.id || (idx + 1),
                            text: t.text,
                            is_active: t.active !== false,
                            sort_order: idx + 1,
                        })),
                        partners: this.heroPartners,
                    }),
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    alert(result.message || 'Gagal menyimpan ke database.');
                    return;
                }
                this.showToast(result.message || 'Hero berhasil disimpan ke database!');
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat menyimpan Hero.');
            }
        },

        async savePartnersToDatabase() {
            try {
                const response = await fetch(this.updateRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        partners: this.heroPartners,
                    }),
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    alert(result.message || 'Gagal menyimpan mitra ke database.');
                    return;
                }
                this.showToast(result.message || 'Pengaturan Mitra berhasil disimpan!');
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat menyimpan Mitra.');
            }
        },

        openCreatePartnerModal() {
            this.isEditingPartner = false;
            this.partnerForm = {
                id: Date.now(),
                name: '',
                logo: '',
                is_active: true,
                sort_order: (this.heroPartners.partners || []).length + 1,
            };
            this.partnerModalOpen = true;
        },

        openEditPartnerModal(p) {
            this.isEditingPartner = true;
            this.partnerForm = JSON.parse(JSON.stringify(p));
            this.partnerModalOpen = true;
        },

        reindexPartners() {
            if (!this.heroPartners || !Array.isArray(this.heroPartners.partners)) return;
            this.heroPartners.partners.forEach((p, index) => {
                p.sort_order = index + 1;
            });
        },

        movePartnerUp(index) {
            if (index <= 0 || !this.heroPartners || !this.heroPartners.partners) return;
            const item = this.heroPartners.partners.splice(index, 1)[0];
            this.heroPartners.partners.splice(index - 1, 0, item);
            this.reindexPartners();
            this.savePartnersToDatabase();
            this.showToast(`Urutan mitra "${item.name}" dinaikkan ke #${index}`);
        },

        movePartnerDown(index) {
            if (!this.heroPartners || !this.heroPartners.partners || index >= this.heroPartners.partners.length - 1) return;
            const item = this.heroPartners.partners.splice(index, 1)[0];
            this.heroPartners.partners.splice(index + 1, 0, item);
            this.reindexPartners();
            this.savePartnersToDatabase();
            this.showToast(`Urutan mitra "${item.name}" diturunkan ke #${index + 2}`);
        },

        savePartner() {
            if (!this.partnerForm.name || !this.partnerForm.name.trim()) {
                alert('Nama mitra wajib diisi.');
                return;
            }
            if (!this.heroPartners.partners) {
                this.heroPartners.partners = [];
            }
            if (this.isEditingPartner) {
                const idx = this.heroPartners.partners.findIndex(p => p.id === this.partnerForm.id);
                if (idx !== -1) {
                    this.heroPartners.partners[idx] = JSON.parse(JSON.stringify(this.partnerForm));
                }
            } else {
                this.heroPartners.partners.push(JSON.parse(JSON.stringify(this.partnerForm)));
            }

            // Sort array by sort_order then reindex to ensure continuous 1, 2, 3...
            this.heroPartners.partners.sort((a, b) => (Number(a.sort_order) || 0) - (Number(b.sort_order) || 0));
            this.reindexPartners();

            this.partnerModalOpen = false;
            this.savePartnersToDatabase();
            this.showToast('Data mitra berhasil disimpan.');
        },

        deletePartner(p) {
            if (!confirm(`Hapus mitra "${p.name}"?`)) return;
            this.heroPartners.partners = this.heroPartners.partners.filter(item => item.id !== p.id);
            this.reindexPartners();
            this.savePartnersToDatabase();
            this.showToast(`Mitra "${p.name}" berhasil dihapus dan urutan diperbarui.`);
        },

        openPartnerMediaPicker() {
            this.partnerMediaPickerOpen = true;
        },

        selectPartnerMedia(item) {
            this.partnerForm.logo = item.path;
            this.partnerMediaPickerOpen = false;
            this.showToast('Logo mitra dipilih: ' + (item.title || item.filename));
        },

        isPartnerLogoUsed(path) {
            if (!path || !this.heroPartners || !this.heroPartners.partners) return false;
            return this.heroPartners.partners.some(p => p.logo === path || p.logo === 'storage/' + path || path === 'storage/' + p.logo);
        },

        usePartnerMedia(item) {
            this.partnerForm = {
                id: Date.now(),
                name: (item.title || item.filename || 'Mitra Baru').replace(/^partner_\d+_[a-zA-Z0-9]+_?|\.[^.]+$/g, '').replace(/[-_]/g, ' ').trim() || 'Mitra Baru',
                logo: item.path,
                is_active: true,
                sort_order: (this.heroPartners.partners || []).length + 1,
            };
            this.isEditingPartner = false;
            this.partnerModalOpen = true;
        },

        async uploadPartnerLogo(file) {
            if (!file) return;
            return this.uploadMultiplePartnerFiles([file]);
        },

        async uploadMultiplePartnerFiles(files) {
            if (!files || files.length === 0) return;
            this.partnerUploading = true;
            let uploadedCount = 0;
            try {
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    if (!file.type.match('image.*')) {
                        alert(`File "${file.name}" bukan file gambar yang valid.`);
                        continue;
                    }
                    if (file.size > 2048 * 1024) {
                        alert(`File "${file.name}" melebihi ukuran maksimal 2 MB.`);
                        continue;
                    }
                    const formData = new FormData();
                    formData.append('image', file);

                    const response = await fetch(this.partnerUploadRoute, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    const result = await response.json();
                    if (response.ok && result.success) {
                        this.partnerMediaLibrary.unshift(result.media);
                        this.partnerForm.logo = result.media.path;
                        uploadedCount++;
                    } else {
                        alert(result.message || `Gagal mengunggah ${file.name}`);
                    }
                }
                if (uploadedCount > 0) {
                    this.showToast(`${uploadedCount} logo mitra berhasil diunggah!`);
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat mengunggah logo mitra.');
            } finally {
                this.partnerUploading = false;
            }
        },

        async deletePartnerMedia(item) {
            if (!item || !item.path) return;
            if (!confirm(`Apakah Anda yakin ingin menghapus file logo "${item.title || item.filename}" dari storage mitra?`)) {
                return;
            }

            try {
                const response = await fetch(this.partnerDeleteRoute, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        path: item.path,
                    }),
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    alert(result.message || 'Gagal menghapus logo.');
                    return;
                }

                // Remove from local list
                this.partnerMediaLibrary = this.partnerMediaLibrary.filter(m => m.path !== item.path && m.filename !== item.filename);

                // If currently selected in partnerForm, reset
                if (this.partnerForm.logo === item.path) {
                    this.partnerForm.logo = '';
                }

                this.showToast(result.message || 'Logo mitra berhasil dihapus.');
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat menghapus logo.');
            }
        },

        saveDraft() {
            if (this.draftForm.images.length === 0) {
                alert('Minimal harus ada 1 gambar latar untuk slideshow.');
                return;
            }
            if (this.isEditingDraft) {
                const idx = this.drafts.findIndex(d => d.id === this.draftForm.id);
                if (idx !== -1) {
                    this.draftForm.updated_at = new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                    this.drafts[idx] = JSON.parse(JSON.stringify(this.draftForm));
                }
            } else {
                this.draftForm.updated_at = new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                this.drafts.push(JSON.parse(JSON.stringify(this.draftForm)));
            }
            this.saveHeroToDatabase(this.draftForm);
            this.closeEditorModal();
        },

        closeEditorModal() {
            this.stopAutoplay();
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

        async handleFileSelected(file) {
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

            this.isUploadingMedia = true;
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
                    this.selectedMediaItem = result.media;
                    this.uploadedMockImage.path = result.media.path;
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
                    alert('Silakan unggah gambar terlebih dahulu.');
                    return;
                }
                imagePath = this.uploadedMockImage.path;
            }

            if (this.mediaPickerTargetIndex === null) {
                this.draftForm.images.push(imagePath);
                this.showToast('Gambar berhasil ditambahkan ke slideshow.');
            } else {
                this.draftForm.images[this.mediaPickerTargetIndex] = imagePath;
                this.showToast('Gambar slide ' + (this.mediaPickerTargetIndex + 1) + ' berhasil diganti.');
            }

            this.mediaPickerOpen = false;
            this.startAutoplay();
        },

        removeImage(imgIndex) {
            if (this.draftForm.images.length <= 1) {
                alert('Minimal harus tersisa 1 gambar latar untuk Hero.');
                return;
            }
            this.draftForm.images.splice(imgIndex, 1);
            if (this.currentSlide >= this.draftForm.images.length) {
                this.currentSlide = 0;
            }
            this.startAutoplay();
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
                this.saveHeroToDatabase(this.selectedDraft);
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
    };
};
</script>

<div class="space-y-6"
     x-data="adminHeroManager({
         drafts: {{ json_encode($drafts) }},
         mediaLibrary: {{ json_encode($mediaLibrary) }},
         partnerMediaLibrary: {{ json_encode($partnerMediaLibrary) }},
         heroPartners: {{ json_encode($heroPartners) }},
         contacts: {{ json_encode($contacts) }},
         csrfToken: '{{ csrf_token() }}',
         updateRoute: '{{ route('admin.hero.update') }}',
         partnerUploadRoute: '{{ route('admin.hero.partner.upload') }}',
         partnerDeleteRoute: '{{ route('admin.hero.partner.delete') }}'
     })">
    
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
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow disabled:opacity-40 disabled:cursor-not-allowed transition-all cursor-pointer">
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
                                    class="w-full text-left px-3.5 py-2 hover:bg-gray-50 flex items-center gap-2 cursor-pointer text-gray-700">
                                <svg class="w-3.5 h-3.5 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                                </svg>
                                <span>Duplikat</span>
                            </button>
                            <button @click="openDeleteModal(draft); menuOpen = false" 
                                    type="button" 
                                    class="w-full text-left px-3.5 py-2 hover:bg-rose-50 text-rose-600 flex items-center gap-2 cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span>Hapus</span>
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </template>
    </div>

    <!-- ======================================================= -->
    <!-- 2B. PENGATURAN MITRA & PARTNER LOGOS                    -->
    <!-- ======================================================= -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-7 shadow-2xs space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100 pb-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🤝</span>
                    <h3 class="text-sm sm:text-base font-extrabold text-brand-dark uppercase tracking-wider">
                        Kepercayaan Mitra &amp; Partner Logos
                    </h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        <span>HERO FOOTER</span>
                    </span>
                </div>
                <p class="text-xs text-gray-500 leading-relaxed max-w-2xl">
                    Kelola judul section mitra (contoh: <em>"Telah Dipercaya Restoran, Cafe, Catering &amp; Rumah Tangga di Jogja"</em> atau <em>"Telah dipercaya oleh (Mitra) :"</em>) dan daftar logo mitra di bawah Trust Checklist Hero.
                </p>
            </div>

            <!-- Save Action Button -->
            <div class="flex items-center gap-3 shrink-0">
                <button type="button" 
                        @click="savePartnersToDatabase()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark transition-all cursor-pointer shadow-sm">
                    <span>Simpan Pengaturan Mitra</span>
                </button>
            </div>
        </div>

        <!-- Section Title Input -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 bg-gray-50/70 p-4 rounded-modern-lg border border-gray-200/60">
            <div class="md:col-span-8">
                <label class="block text-xs font-bold text-brand-dark mb-1">
                    Judul Section Mitra (Partner Title)
                </label>
                <input type="text" 
                       x-model="heroPartners.title" 
                       placeholder="Telah Dipercaya Restoran, Cafe, Catering & Rumah Tangga di Jogja" 
                       class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-bold text-brand-dark focus:ring-2 focus:ring-brand-primary/30">
            </div>
            <div class="md:col-span-4 flex items-end">
                <button type="button" 
                        @click="openCreatePartnerModal()" 
                        class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-modern font-bold text-xs text-brand-primary bg-brand-primary/10 hover:bg-brand-primary/20 border border-brand-primary/30 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Mitra Baru</span>
                </button>
            </div>
        </div>

        <!-- Integrated Partner Drag & Drop Upload Zone -->
        <div class="p-5 bg-gray-50/80 rounded-modern-lg border-2 border-dashed text-center transition-all cursor-pointer"
             :class="isDraggingPartner ? 'border-brand-primary bg-brand-primary/10 ring-2 ring-brand-primary/30 scale-[1.005]' : 'border-gray-300 hover:border-brand-primary/50'"
             @dragover.prevent="isDraggingPartner = true"
             @dragleave.prevent="isDraggingPartner = false"
             @drop.prevent="isDraggingPartner = false; if ($event.dataTransfer && $event.dataTransfer.files.length > 0) { uploadMultiplePartnerFiles($event.dataTransfer.files); }">
            <div class="flex flex-col items-center justify-center gap-2 max-w-md mx-auto">
                <div class="w-12 h-12 rounded-full flex items-center justify-center transition-colors"
                     :class="isDraggingPartner ? 'bg-brand-primary text-white scale-110' : 'bg-brand-primary/10 text-brand-primary'">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                </div>
                <div class="space-y-1">
                    <p class="text-xs font-bold text-brand-dark">
                        <span>Drag &amp; Drop logo partner ke area ini</span>
                    </p>
                    <div class="text-xs text-gray-500">
                        <span>atau</span>
                        <label class="font-bold text-brand-primary hover:underline cursor-pointer ml-1">
                            <span>[ Pilih File ]</span>
                            <input type="file" accept="image/png,image/jpeg,image/jpg,image/webp" multiple class="hidden" @change="uploadMultiplePartnerFiles($event.target.files)" :disabled="partnerUploading">
                        </label>
                    </div>
                </div>
                <p class="text-[11px] text-gray-400 font-medium tracking-wide">
                    JPG • JPEG • PNG • WEBP &nbsp;•&nbsp; Maks. 2 MB
                </p>
                <template x-if="partnerUploading">
                    <div class="inline-flex items-center gap-2 text-xs font-bold text-brand-primary mt-1">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Mengunggah logo ke storage/partners/...</span>
                    </div>
                </template>
            </div>
        </div>

        <!-- MEDIA PARTNER (Isolated Storage: storage/app/public/partners/) -->
        <div class="space-y-3 bg-gray-50/50 p-4 rounded-modern-lg border border-gray-200/70">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-sm">🖼️</span>
                    <h4 class="text-xs font-bold text-brand-dark uppercase tracking-wider">
                        Media Partner <span class="font-mono text-[11px] font-normal text-gray-500">(storage/partners/)</span>
                    </h4>
                </div>
                <span class="text-[11px] text-gray-400 font-bold" x-text="(partnerMediaLibrary || []).length + ' Logo Tersedia'"></span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3 max-h-56 overflow-y-auto p-1">
                <template x-for="item in partnerMediaLibrary" :key="item.id || item.path">
                    <div class="group relative rounded-modern border p-2.5 bg-white hover:border-brand-primary hover:shadow-xs transition-all flex flex-col items-center justify-between text-center"
                         :class="isPartnerLogoUsed(item.path) ? 'border-emerald-300 bg-emerald-50/30' : 'border-gray-200'">
                        
                        <!-- Top Controls: Used Badge & Delete Button -->
                        <div class="absolute top-1.5 right-1.5 flex items-center gap-1">
                            <template x-if="isPartnerLogoUsed(item.path)">
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    Digunakan
                                </span>
                            </template>
                        </div>
                        <button type="button" 
                                @click.stop="deletePartnerMedia(item)"
                                class="absolute top-1.5 left-1.5 w-5 h-5 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 flex items-center justify-center transition-all cursor-pointer opacity-70 hover:opacity-100 shadow-2xs"
                                title="Hapus Logo">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>

                        <!-- Thumbnail Preview -->
                        <div class="w-14 h-14 rounded-modern bg-gray-50 border border-gray-100 p-1 flex items-center justify-center my-1 overflow-hidden">
                            <img :src="getImageUrl(item.path)" :alt="item.title" class="max-w-full max-h-full object-contain">
                        </div>

                        <!-- Filename -->
                        <span class="text-[10px] font-bold text-brand-dark truncate w-full" x-text="item.title || item.filename"></span>
                        <span class="text-[9px] text-gray-400 font-mono" x-text="item.size || ''"></span>

                        <!-- Action Gunakan -->
                        <button type="button" 
                                @click="usePartnerMedia(item)"
                                class="mt-2 w-full py-1 rounded-modern-sm text-[10px] font-bold transition-all cursor-pointer"
                                :class="isPartnerLogoUsed(item.path) ? 'text-gray-600 bg-gray-100 hover:bg-gray-200' : 'text-brand-primary bg-brand-primary/10 hover:bg-brand-primary/20 border border-brand-primary/30'">
                            <span>Gunakan</span>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Partners Table / Grid -->
        <div class="overflow-x-auto rounded-modern border border-gray-200">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-100/80 font-bold text-brand-dark border-b border-gray-200">
                    <tr>
                        <th class="p-3 w-16 text-center">Urutan</th>
                        <th class="p-3">Nama Mitra / Kategori</th>
                        <th class="p-3">Logo Reference</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Atur Posisi & Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="(p, pIdx) in (heroPartners.partners || [])" :key="p.id || pIdx">
                        <tr class="hover:bg-gray-50/50">
                            <!-- Urutan Badge -->
                            <td class="p-3 text-center">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-brand-dark font-bold font-mono text-xs border border-gray-200 shadow-2xs"
                                      x-text="'#' + (p.sort_order || (pIdx + 1))">
                                </span>
                            </td>

                            <!-- Nama & Logo Preview -->
                            <td class="p-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 shrink-0 rounded-modern-sm border border-gray-200 bg-white p-0.5 flex items-center justify-center overflow-hidden">
                                        <template x-if="p.logo">
                                            <img :src="getImageUrl(p.logo)" :alt="p.name" class="max-w-full max-h-full object-contain">
                                        </template>
                                        <template x-if="!p.logo">
                                            <span class="text-xs text-gray-400 font-bold">🤝</span>
                                        </template>
                                    </div>
                                    <span class="font-bold text-brand-dark" x-text="p.name"></span>
                                </div>
                            </td>

                            <!-- Logo Path -->
                            <td class="p-3 font-mono text-[11px] text-gray-500 truncate max-w-[180px]" x-text="p.logo || '-'"></td>

                            <!-- Status -->
                            <td class="p-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold"
                                      :class="(p.is_active !== false && p.active !== false) ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'"
                                      x-text="(p.is_active !== false && p.active !== false) ? 'Aktif' : 'Nonaktif'"></span>
                            </td>

                            <!-- Reordering Controls & Actions -->
                            <td class="p-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Move Up Button -->
                                    <button type="button" 
                                            @click="movePartnerUp(pIdx)" 
                                            :disabled="pIdx === 0"
                                            class="w-7 h-7 rounded-modern-sm border border-gray-200 flex items-center justify-center transition-all cursor-pointer"
                                            :class="pIdx === 0 ? 'opacity-30 cursor-not-allowed bg-gray-50 text-gray-400' : 'bg-white hover:bg-brand-primary/10 text-brand-dark hover:text-brand-primary hover:border-brand-primary/30 shadow-2xs'"
                                            title="Geser Naik (Urutan Sebelumnya)">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                                    </button>

                                    <!-- Move Down Button -->
                                    <button type="button" 
                                            @click="movePartnerDown(pIdx)" 
                                            :disabled="pIdx === (heroPartners.partners || []).length - 1"
                                            class="w-7 h-7 rounded-modern-sm border border-gray-200 flex items-center justify-center transition-all cursor-pointer"
                                            :class="pIdx === (heroPartners.partners || []).length - 1 ? 'opacity-30 cursor-not-allowed bg-gray-50 text-gray-400' : 'bg-white hover:bg-brand-primary/10 text-brand-dark hover:text-brand-primary hover:border-brand-primary/30 shadow-2xs'"
                                            title="Geser Turun (Urutan Berikutnya)">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                    </button>

                                    <span class="text-gray-300 mx-0.5">|</span>

                                    <!-- Edit Button -->
                                    <button type="button" 
                                            @click="openEditPartnerModal(p)" 
                                            class="px-2 py-1 rounded-modern-sm text-xs font-bold text-brand-primary hover:bg-brand-primary/10 transition-colors cursor-pointer">
                                        Edit
                                    </button>

                                    <!-- Delete Button -->
                                    <button type="button" 
                                            @click="deletePartner(p)" 
                                            class="px-2 py-1 rounded-modern-sm text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
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
             @click="closeEditorModal()">
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
                 class="relative bg-white rounded-modern-xl max-w-7xl w-full p-5 sm:p-8 shadow-2xl border border-gray-200 my-6">
                
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
                    <button @click="closeEditorModal()" 
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
                        
                        <!-- Left Column: Form Fields (5 cols on lg) -->
                        <div class="lg:col-span-5 space-y-6">
                            
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
                                        3. Call To Action (Tombol Aksi & Saluran Kontak)
                                    </h4>
                                </div>

                                <!-- Primary CTA Destination & Contact Reference -->
                                <div class="space-y-3 p-3.5 rounded-modern bg-white border border-gray-200">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-bold text-brand-dark mb-1">
                                                Teks Tombol Utama (Primary Text) <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="text" 
                                                   x-model="draftForm.primary_cta_text" 
                                                   required
                                                   placeholder="Belanja Sekarang"
                                                   class="w-full text-xs rounded-modern border border-gray-300 p-2 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary font-bold text-brand-dark">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-brand-dark mb-1">
                                                Tujuan Kontak / Referensi Saluran <span class="text-rose-500">*</span>
                                            </label>
                                            <select x-model="draftForm.primary_cta_contact"
                                                    @change="if (draftForm.primary_cta_contact) { draftForm.primary_cta_link = draftForm.primary_cta_contact; } else if (!draftForm.primary_cta_link || !draftForm.primary_cta_link.startsWith('#')) { draftForm.primary_cta_link = '#produk'; }"
                                                    class="w-full text-xs rounded-modern border border-gray-300 p-2 bg-white font-medium focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                                                <option value="">-- Tautan Kustom / Anchor (#produk) --</option>
                                                <template x-for="c in (contacts ? contacts.filter(item => item.active !== false && item.type === 'whatsapp') : [])" :key="c.key || c.id">
                                                    <option :value="c.key || c.id" x-text="c.name + ' (' + (c.division || 'Umum') + ')'"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Read-Only Contact Value Preview (When Contact Reference Selected) -->
                                    <template x-if="draftForm.primary_cta_contact && getContactByKey(draftForm.primary_cta_contact)">
                                        <div class="p-3 rounded-modern bg-emerald-50/80 border border-emerald-200 text-xs space-y-1.5">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[10px] font-extrabold uppercase text-emerald-800 tracking-wider">
                                                    Informasi Kontak Terpilih (Read-Only)
                                                </span>
                                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-white text-emerald-700 border border-emerald-300" x-text="'Key: ' + draftForm.primary_cta_contact"></span>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1 border-t border-emerald-200/60">
                                                <div>
                                                    <span class="text-[10px] text-gray-500 block">Divisi / Pemilik:</span>
                                                    <strong class="text-brand-dark text-xs" x-text="getContactByKey(draftForm.primary_cta_contact)?.division || '-'"></strong>
                                                </div>
                                                <div>
                                                    <span class="text-[10px] text-gray-500 block">Tipe Saluran:</span>
                                                    <span class="inline-flex items-center gap-1 font-bold text-emerald-700">
                                                        <span>💬 WhatsApp</span>
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="text-[10px] text-gray-500 block">Nomor WhatsApp:</span>
                                                    <span class="font-mono font-bold text-xs text-emerald-900" x-text="'+' + getContactByKey(draftForm.primary_cta_contact)?.value"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Manual Custom Link (When No Contact Reference Selected) -->
                                    <div x-show="!draftForm.primary_cta_contact">
                                        <label class="block text-xs font-bold text-brand-dark mb-1">
                                            Target URL / Anchor Link
                                        </label>
                                        <input type="text" 
                                               x-model="draftForm.primary_cta_link" 
                                               placeholder="#produk"
                                               class="w-full text-xs rounded-modern border border-gray-300 p-2 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary font-mono">
                                    </div>
                                </div>

                                <!-- Secondary CTA -->
                                <div class="grid grid-cols-2 gap-3 p-3.5 rounded-modern bg-white border border-gray-200">
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
                                               class="w-full text-xs rounded-modern border border-gray-300 p-2 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary font-mono">
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

                        <!-- Right Column: Live Source-of-Truth Hero Preview (7 cols on lg) -->
                        <div class="lg:col-span-7 space-y-3 sticky top-6 self-start">
                            
                            <style>
                                /* Desktop & Tablet Full Viewport */
                                .virtual-viewport-desktop > section {
                                    width: 100% !important;
                                    height: 100% !important;
                                    min-height: 100% !important;
                                }
                                .virtual-viewport-desktop > section > div.relative.z-20 {
                                    width: 100% !important;
                                    height: 100% !important;
                                    min-height: 100% !important;
                                }

                                /* ========================================================================= */
                                /* ISOLATED MOBILE RESPONSIVE CONTEXT FOR iPHONE 15 SIMULATOR (393px)        */
                                /* Overrides desktop browser media queries to simulate true mobile viewport  */
                                /* ========================================================================= */
                                
                                /* Section & Container */
                                .preview-device-mobile section {
                                    width: 100% !important;
                                    min-height: 852px !important;
                                    height: 100% !important;
                                }
                                .preview-device-mobile section > div.relative.z-20 {
                                    width: 100% !important;
                                    max-width: 100% !important;
                                    padding-top: 5rem !important;     /* pt-20 = 80px */
                                    padding-bottom: 5rem !important;  /* pb-20 = 80px */
                                    padding-left: 1rem !important;    /* px-4 = 16px */
                                    padding-right: 1rem !important;   /* px-4 = 16px */
                                }

                                /* Category Tag Badge */
                                .preview-device-mobile div.inline-flex {
                                    font-size: 11px !important;
                                    line-height: 1rem !important;
                                    padding: 0.375rem 0.875rem !important; /* py-1.5 px-3.5 */
                                    margin-bottom: 1rem !important;        /* mb-4 */
                                    gap: 0.5rem !important;
                                }
                                .preview-device-mobile div.inline-flex span.w-2 {
                                    width: 0.5rem !important;  /* 8px */
                                    height: 0.5rem !important; /* 8px */
                                }

                                /* Headline H1 (text-2xl = 24px, line-height 1.2) */
                                .preview-device-mobile h1 {
                                    font-size: 1.5rem !important;          /* 24px */
                                    line-height: 1.2 !important;
                                    margin-bottom: 1rem !important;        /* mb-4 */
                                    letter-spacing: -0.025em !important;
                                    font-weight: 800 !important;
                                }
                                .preview-device-mobile h1 span.underline {
                                    text-decoration-thickness: 2px !important;
                                    text-underline-offset: 4px !important;
                                }

                                /* Subheadline Description (text-xs = 12px, line-height 1.625) */
                                .preview-device-mobile p {
                                    font-size: 0.75rem !important;         /* 12px */
                                    line-height: 1.625 !important;
                                    margin-bottom: 1.5rem !important;      /* mb-6 */
                                    max-width: 100% !important;
                                }

                                /* CTA Buttons (Stack vertically, full width, text-sm = 14px) */
                                .preview-device-mobile div.max-w-3xl > div.flex {
                                    flex-direction: column !important;
                                    align-items: stretch !important;
                                    gap: 0.75rem !important;               /* gap-3 = 12px */
                                    width: 100% !important;
                                }
                                .preview-device-mobile div.max-w-3xl > div.flex > a {
                                    width: 100% !important;
                                    padding: 0.875rem 1.5rem !important;  /* py-3.5 px-6 */
                                    font-size: 0.875rem !important;        /* text-sm = 14px */
                                    gap: 0.5rem !important;
                                }
                                .preview-device-mobile div.max-w-3xl > div.flex > a svg {
                                    width: 1rem !important;                /* 16px */
                                    height: 1rem !important;
                                }

                                /* Quick Trust Badges (Grid 3 columns, text-[10px]) */
                                .preview-device-mobile div.border-t {
                                    margin-top: 2rem !important;           /* mt-8 = 32px */
                                    padding-top: 1.25rem !important;       /* pt-5 = 20px */
                                    display: grid !important;
                                    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                                    gap: 0.5rem !important;                /* gap-2 = 8px */
                                }
                                .preview-device-mobile div.border-t div.rounded-full {
                                    width: 1.5rem !important;              /* w-6 = 24px */
                                    height: 1.5rem !important;
                                }
                                .preview-device-mobile div.border-t svg {
                                    width: 0.875rem !important;            /* 14px */
                                    height: 0.875rem !important;
                                }
                                .preview-device-mobile div.border-t span {
                                    font-size: 10px !important;            /* text-[10px] */
                                    line-height: 1.25 !important;
                                }

                                /* Slideshow Navigation Controls (Bottom Centered Pill) */
                                .preview-device-mobile div.z-30.absolute {
                                    bottom: 1.25rem !important;            /* bottom-5 = 20px */
                                    left: 50% !important;
                                    right: auto !important;
                                    transform: translateX(-50%) !important;
                                    padding: 0.375rem 0.875rem !important; /* py-1.5 px-3.5 */
                                    gap: 0.75rem !important;               /* gap-3 = 12px */
                                }

                                /* Scrollbar styling */
                                .hero-iphone-viewport::-webkit-scrollbar {
                                    width: 4px;
                                }
                                .hero-iphone-viewport::-webkit-scrollbar-thumb {
                                    background-color: rgba(255, 255, 255, 0.25);
                                    border-radius: 9999px;
                                }
                            </style>
                            
                            <!-- Preview Device Toggle Bar -->
                            <div class="flex items-center justify-between pb-1">
                                <div>
                                    <label class="block text-xs font-extrabold text-brand-dark">
                                        5. Live Hero Preview (Shared Component)
                                    </label>
                                    <p class="text-[11px] text-gray-500 font-mono flex items-center gap-1.5 flex-wrap">
                                        <span x-show="previewDevice === 'desktop'" class="inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            <span>Desktop (1280×720) • Scale <span x-text="Math.round(currentScale * 100)"></span>%</span>
                                        </span>
                                        <span x-show="previewDevice === 'tablet'" class="inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <span>Tablet (1024×768) • Scale <span x-text="Math.round(currentScale * 100)"></span>%</span>
                                        </span>
                                        <span x-show="previewDevice === 'mobile'" class="inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <span>Mobile (393×852) • Scale <span x-text="Math.round(currentScale * 100)"></span>% • Scrollable</span>
                                        </span>
                                    </p>
                                </div>
                                
                                <!-- Device Simulator Switch -->
                                <div class="flex items-center bg-gray-100 p-0.5 rounded-modern border border-gray-200 text-xs">
                                    <button @click="previewDevice = 'desktop'" 
                                            type="button"
                                            :class="previewDevice === 'desktop' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500 hover:text-brand-dark'"
                                            class="px-2.5 py-1 rounded transition-all cursor-pointer flex items-center gap-1.5 text-[11px]">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span>Desktop</span>
                                    </button>
                                    <button @click="previewDevice = 'tablet'" 
                                            type="button"
                                            :class="previewDevice === 'tablet' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500 hover:text-brand-dark'"
                                            class="px-2.5 py-1 rounded transition-all cursor-pointer flex items-center gap-1.5 text-[11px]">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <span>Tablet</span>
                                    </button>
                                    <button @click="previewDevice = 'mobile'" 
                                            type="button"
                                            :class="previewDevice === 'mobile' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500 hover:text-brand-dark'"
                                            class="px-2.5 py-1 rounded transition-all cursor-pointer flex items-center gap-1.5 text-[11px]">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <span>Mobile</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Live Hero Simulation Container -->
                            <div x-ref="previewBoxWrapper"
                                 class="bg-gray-950 rounded-modern-xl p-3 sm:p-4 flex justify-center items-start overflow-hidden border border-gray-800 shadow-inner min-h-[380px]">
                                
                                <!-- =================================================== -->
                                <!-- A. DESKTOP & TABLET VIEWPORT PREVIEW (FROZEN)       -->
                                <!-- =================================================== -->
                                <template x-if="previewDevice !== 'mobile'">
                                    <div class="relative overflow-hidden rounded-modern-lg shadow-2xl transition-all duration-300 bg-brand-dark mx-auto"
                                         :style="{
                                             width: currentFrameWidth + 'px',
                                             height: currentFrameHeight + 'px'
                                         }">
                                        
                                        <!-- Virtual Viewport (Reference Resolution: 1280x720 or 1024x768) -->
                                        <div class="virtual-viewport-desktop absolute top-0 left-0 bg-brand-dark overflow-hidden"
                                             :style="{
                                                 width: currentVirtualWidth + 'px',
                                                 height: currentVirtualHeight + 'px',
                                                 transformOrigin: '0 0',
                                                 transform: 'scale(' + currentScale + ')'
                                             }">
                                            
                                            <!-- SHARED HERO COMPONENT (SOURCE OF TRUTH) -->
                                            @include('components.hero', ['isLivePreview' => true])

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
                                            <div class="absolute top-2.5 left-1/2 -translate-x-1/2 w-28 h-6 bg-black rounded-full z-40 pointer-events-none shadow-md flex items-center justify-between px-2.5">
                                                <span class="w-2.5 h-2.5 rounded-full bg-slate-900 border border-slate-800"></span>
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            </div>

                                            <!-- Mobile Scrollable Screen (Isolated 393×852 Mobile Responsive Context) -->
                                            <div class="preview-device-mobile hero-iphone-viewport w-full h-full overflow-y-auto overflow-x-hidden text-left relative bg-brand-dark"
                                                 style="scroll-behavior: smooth;">
                                                
                                                <div class="w-[393px] min-h-[852px] h-full relative">
                                                    <!-- SHARED HERO COMPONENT (RENDERED WITH TRUE 1:1 MOBILE VIEWPORT RULES) -->
                                                    @include('components.hero', ['isLivePreview' => true])
                                                </div>

                                            </div>

                                            <!-- iPhone 15 Bottom Home Bar -->
                                            <div class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-32 h-1 bg-white/40 rounded-full z-40 pointer-events-none"></div>

                                        </div>

                                    </div>
                                </template>

                            </div>

                            <div class="p-3 rounded-modern bg-gray-50 border border-gray-200 text-[11px] text-gray-500 leading-relaxed">
                                <span x-show="previewDevice === 'desktop'">💻 <strong>Desktop 1280px:</strong> Seluruh elemen Hero di-scale secara proporsional dan presisi dari resolusi Desktop 16:9.</span>
                                <span x-show="previewDevice === 'tablet'">📱 <strong>Tablet 1024px:</strong> Seluruh elemen Hero di-scale secara proporsional dan presisi dari resolusi Tablet 4:3.</span>
                                <span x-show="previewDevice === 'mobile'">📱 <strong>Mobile (393&times;852):</strong> Viewport 393&times;852 dengan isolasi context responsive mobile murni (1:1 Landing Page Mobile).</span>
                            </div>

                        </div>

                    </div>

                    <!-- Modal Actions Footer -->
                    <div class="pt-5 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button @click="closeEditorModal()" 
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
                <div x-show="mediaPickerTab === 'library'" class="space-y-3">
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
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 max-h-[380px] overflow-y-auto p-1 overscroll-contain no-scrollbar">
                        <template x-for="item in filteredMediaLibrary" :key="item.id">
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
                                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Delete Button on Card -->
                                    <template x-if="item.is_deletable">
                                        <button @click.stop="deleteMedia(item)" 
                                                type="button" 
                                                title="Hapus media dari server" 
                                                class="absolute top-1.5 left-1.5 p-1 rounded bg-rose-600/90 text-white hover:bg-rose-700 hover:scale-110 shadow-xs transition-all cursor-pointer flex items-center justify-center z-10">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
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

                    <!-- Empty Search State -->
                    <div x-show="filteredMediaLibrary.length === 0" class="p-8 text-center bg-gray-50 rounded-modern border border-dashed border-gray-200 text-xs text-gray-400">
                        Tidak ada gambar yang cocok dengan kata kunci "<span class="font-bold text-gray-600" x-text="mediaSearchQuery"></span>".
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
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <svg class="w-3 h-3 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Siap Digunakan</span>
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
    <!-- 6B. PARTNER CREATE / EDIT MODAL                         -->
    <!-- ======================================================= -->
    <div x-show="partnerModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="partnerModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white rounded-modern-xl max-w-md w-full p-6 shadow-xl border border-gray-200 space-y-4">
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🤝</span>
                        <h3 class="text-sm font-extrabold text-brand-dark" x-text="isEditingPartner ? 'Edit Mitra' : 'Tambah Mitra Baru'"></h3>
                    </div>
                    <button @click="partnerModalOpen = false" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-3">
                    <!-- Partner Name -->
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Nama Mitra / Kategori <span class="text-red-500">*</span></label>
                        <input type="text" x-model="partnerForm.name" placeholder="Contoh: Restoran & Cafe Jogja" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-bold text-brand-dark">
                    </div>

                    <!-- Logo Reference & Isolated Partner Media Picker -->
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Logo / Image Mitra</label>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-modern border border-gray-200 transition-all"
                             @dragover.prevent="isDraggingPartner = true"
                             @dragleave.prevent="isDraggingPartner = false"
                             @drop.prevent="isDraggingPartner = false; if ($event.dataTransfer && $event.dataTransfer.files.length > 0) { uploadPartnerLogo($event.dataTransfer.files[0]); }"
                             :class="isDraggingPartner ? 'border-brand-primary bg-brand-primary/10 ring-2 ring-brand-primary/30' : ''">
                            <div class="w-14 h-14 shrink-0 rounded-modern border border-gray-200 bg-white p-1 flex items-center justify-center overflow-hidden">
                                <template x-if="partnerForm.logo">
                                    <img :src="getImageUrl(partnerForm.logo)" 
                                         alt="Preview Logo" 
                                         class="max-w-full max-h-full object-contain">
                                </template>
                                <template x-if="!partnerForm.logo">
                                    <span class="text-xl text-gray-300">🤝</span>
                                </template>
                            </div>
                            <div class="space-y-1.5 flex-1 min-w-0">
                                <div class="text-[11px] font-mono text-gray-500 truncate" x-text="partnerForm.logo || 'Belum memilih logo'"></div>
                                <div class="flex items-center flex-wrap gap-2">
                                    <button type="button" 
                                            @click="openPartnerMediaPicker()" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-modern-sm text-xs font-bold text-brand-primary bg-brand-primary/10 hover:bg-brand-primary/20 border border-brand-primary/30 transition-all cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>Pilih dari Media Mitra</span>
                                    </button>
                                    <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-modern-sm text-xs font-bold text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 transition-all cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                        <span x-text="partnerUploading ? 'Mengunggah...' : 'Upload / Drag Logo'"></span>
                                        <input type="file" accept="image/*" class="hidden" @change="uploadPartnerLogo($event.target.files[0])" :disabled="partnerUploading">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sort Order & Status -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Urutan (Sort Order)</label>
                            <input type="number" x-model.number="partnerForm.sort_order" min="1" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Status Visibilitas</label>
                            <select x-model="partnerForm.is_active" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-bold">
                                <option :value="true">Aktif (Tampil)</option>
                                <option :value="false">Nonaktif (Sembunyi)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button @click="partnerModalOpen = false" type="button" class="px-4 py-2 rounded-modern text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button @click="savePartner()" type="button" class="px-4 py-2 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark transition-colors cursor-pointer">
                        Simpan Mitra
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 6C. ISOLATED PARTNER MEDIA PICKER MODAL                 -->
    <!-- ======================================================= -->
    <div x-show="partnerMediaPickerOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="partnerMediaPickerOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white rounded-modern-xl max-w-2xl w-full p-6 shadow-2xl border border-gray-200 space-y-5">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-base">🤝</span>
                            <h3 class="text-sm font-extrabold text-brand-dark">Media Storage Khusus Mitra (Partners)</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                ISOLATED: storage/partners/
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Pilih logo mitra dari storage terisolasi atau unggah logo mitra baru (tidak bercampur dengan gambar Hero).
                        </p>
                    </div>
                    <button @click="partnerMediaPickerOpen = false" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Upload Zone with Drag & Drop -->
                <div class="p-5 rounded-modern-lg border-2 border-dashed text-center transition-all cursor-pointer"
                     :class="isDraggingPartner ? 'border-brand-primary bg-brand-primary/10 ring-2 ring-brand-primary/30 scale-[1.01]' : 'border-gray-300 hover:border-brand-primary/50 bg-gray-50'"
                     @dragover.prevent="isDraggingPartner = true"
                     @dragleave.prevent="isDraggingPartner = false"
                     @drop.prevent="isDraggingPartner = false; if ($event.dataTransfer && $event.dataTransfer.files.length > 0) { uploadPartnerLogo($event.dataTransfer.files[0]); }">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center transition-colors"
                             :class="isDraggingPartner ? 'bg-brand-primary text-white scale-110' : 'bg-brand-primary/10 text-brand-primary'">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        </div>
                        <div class="text-xs">
                            <label class="font-bold text-brand-primary hover:underline cursor-pointer">
                                <span>Pilih file logo dari komputer</span>
                                <input type="file" accept="image/*" class="hidden" @change="uploadPartnerLogo($event.target.files[0])" :disabled="partnerUploading">
                            </label>
                            <span class="text-gray-500 font-medium"> atau tarik &amp; lepas (drag &amp; drop) file ke sini</span>
                        </div>
                        <p class="text-[11px] text-gray-400">Format yang didukung: PNG, JPG, WebP (Maksimal 2 MB)</p>
                        <template x-if="partnerUploading">
                            <div class="inline-flex items-center gap-2 text-xs font-bold text-brand-primary mt-1">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>Mengunggah logo ke storage/partners/...</span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Isolated Partner Media Grid -->
                <div class="space-y-2">
                    <h4 class="text-xs font-bold text-brand-dark uppercase tracking-wider">Koleksi Logo Mitra</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-h-64 overflow-y-auto p-1">
                        <template x-for="item in partnerMediaLibrary" :key="item.id || item.path">
                            <div @click="selectPartnerMedia(item)"
                                 class="group relative rounded-modern border p-2.5 bg-white hover:border-brand-primary hover:shadow-md transition-all cursor-pointer flex flex-col items-center justify-center text-center"
                                 :class="partnerForm.logo === item.path ? 'ring-2 ring-brand-primary border-brand-primary bg-brand-primary/5' : 'border-gray-200'">
                                
                                <!-- Delete Button -->
                                <button type="button" 
                                        @click.stop="deletePartnerMedia(item)"
                                        class="absolute top-1.5 left-1.5 w-5 h-5 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 flex items-center justify-center transition-all cursor-pointer opacity-70 hover:opacity-100 shadow-2xs"
                                        title="Hapus Logo">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>

                                <div class="w-16 h-16 rounded-modern bg-gray-50 border border-gray-100 p-1 flex items-center justify-center mb-2 overflow-hidden">
                                    <img :src="getImageUrl(item.path)" :alt="item.title" class="max-w-full max-h-full object-contain">
                                </div>
                                <span class="text-[11px] font-bold text-brand-dark truncate w-full" x-text="item.title || item.filename"></span>
                                <span class="text-[10px] text-gray-400" x-text="item.size || ''"></span>
                                <div x-show="partnerForm.logo === item.path" class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full bg-brand-primary text-white flex items-center justify-center text-[10px] font-bold">
                                    ✓
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="pt-3 flex items-center justify-end border-t border-gray-100">
                    <button @click="partnerMediaPickerOpen = false" type="button" class="px-4 py-2 rounded-modern text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer">
                        Tutup
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
