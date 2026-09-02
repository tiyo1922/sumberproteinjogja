@extends('layouts.admin', [
    'title' => 'Katalog Produk',
    'pageTitle' => 'Katalog Produk'
])

@section('content')
<script>
window.adminProductManager = function(initialPayload) {
    const payload = initialPayload || {};
    return {
        activeTab: 'catalog', // 'catalog' | 'flash_sale'
        products: payload.products || [],
        categories: payload.categories || [],
        contactSettings: payload.contactSettings || {},
        mediaLibrary: payload.mediaLibrary || [],
        catalogSection: payload.catalogSection || {
            label: 'Katalog Lengkap',
            title: 'Produk Pilihan',
            subtitle: 'Pilih bahan masak sesuai kebutuhanmu. Tersedia skala retail rumah tangga maupun pembelian curah.'
        },
        flashSale: payload.flashSaleSetting || {
            enabled: false,
            end_at: null,
            title: 'Flash Sale Terbatas!',
            subtitle: 'Dapatkan potongan harga spesial untuk produk protein pilihan hari ini. Stok terbatas!'
        },
        csrfToken: payload.csrfToken || '',

        // Modals State
        editorModalOpen: false,
        mediaPickerOpen: false,
        deleteModalOpen: false,
        flashSaleModalOpen: false,
        charModalOpen: false,
        charDeleteModalOpen: false,
        isEditing: false,
        isEditingChar: false,

        // Filters & Search State
        searchQuery: '',
        selectedCategoryFilter: 'all',
        selectedTypeFilter: 'all',
        previewDevice: 'desktop', // 'desktop' | 'mobile'
        mediaTab: 'library', // 'library' | 'upload'
        mediaSearchQuery: '',
        mediaDeleteRoute: '{{ route('admin.media.delete') }}',
        mediaUploadRoute: '{{ route('admin.media.upload') }}',
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
                    if (this.selectedMedia && this.selectedMedia.path === media.path) {
                        this.selectedMedia = null;
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

        // Toast State
        toastMessage: '',
        toastVisible: false,

        // Catalog Reordering Drag & Drop State
        isReorderingCatalog: false,
        isSavingCatalogOrder: false,
        confirmReorderModalOpen: false,
        catalogOrderSnapshot: null,
        draggedProductId: null,
        dragOverProductId: null,

        // Flash Sale State & Persistence Flags
        isSavingFlashSaleSettings: false,
        isTogglingFlashSale: false,
        isAssigningFlashSale: false,
        isRemovingFlashSaleId: null,
        flashSaleForm: {
            product_id: '',
            discount_type: 'percentage',
            discount_value: 20,
            sort_order: 1,
        },

        // Master Badges State
        availableCharacteristics: [
            { id: 'Frozen', label: 'Frozen', name: 'Frozen', desc: 'Dibekukan cold-chain standar', color: '#059669', status: 'Aktif' },
            { id: 'Ready to Cook', label: 'Ready to Cook', name: 'Ready to Cook', desc: 'Sudah dipotong / marinasi praktis', color: '#d97706', status: 'Aktif' },
            { id: 'Plain', label: 'Plain', name: 'Plain', desc: 'Tanpa bumbu / murni alami', color: '#475569', status: 'Aktif' },
            { id: 'Berbumbu', label: 'Berbumbu', name: 'Berbumbu', desc: 'Sudah diungkep / marinasi rempah', color: '#ea580c', status: 'Aktif' },
            { id: 'Curah', label: 'Curah', name: 'Curah', desc: 'Tersedia kemasan grosir / horeka', color: '#7c3aed', status: 'Aktif' },
            { id: 'Fresh', label: 'Fresh', name: 'Fresh', desc: 'Potong harian subuh / segar dingin', color: '#0284c7', status: 'Aktif' },
        ],
        selectedChar: null,
        charDeleteWarningCount: 0,
        charForm: {
            id: null,
            name: '',
            label: '',
            desc: '',
            color: '#059669',
            status: 'Aktif'
        },

        // Product Form State & Persistence Flags
        isSaving: false,
        isSavingHeader: false,
        deletingProduct: null,
        form: {
            id: null,
            name: '',
            category_ids: [1],
            category_names: ['Daging Sapi'],
            category: 'Daging Sapi',
            types: ['Frozen', 'Plain'],
            weight: '500g',
            weight_value: 500,
            unit: 'gram',
            price: 50000,
            status: 'Aktif',
            image: 'images/prod-beef-slice.jpg',
            description: '',
        },

        // ==========================================
        // COMPUTED HELPERS
        // ==========================================
        get activeCategories() {
            return this.categories.filter(c => c.status === 'active_landing' || c.status === 'active_catalog' || c.status === 'Aktif' || c.is_active >= 1);
        },

        get filteredProducts() {
            if (this.isReorderingCatalog) {
                return this.products;
            }
            return this.products.filter(p => {
                const matchCat = this.selectedCategoryFilter === 'all' || 
                    (p.category_ids && p.category_ids.map(Number).includes(Number(this.selectedCategoryFilter))) ||
                    p.category_id == this.selectedCategoryFilter || 
                    p.category === this.selectedCategoryFilter;
                const matchType = this.selectedTypeFilter === 'all' || (p.types && p.types.includes(this.selectedTypeFilter));
                const matchSearch = !this.searchQuery.trim() || 
                    p.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                    (p.category && p.category.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
                    (p.category_names && p.category_names.some(cn => cn.toLowerCase().includes(this.searchQuery.toLowerCase())));
                return matchCat && matchType && matchSearch;
            });
        },

        get flashSaleProductsList() {
            return this.products.filter(p => p.is_flash_sale).sort((a, b) => {
                const orderA = Number(a.flash_sale_sort_order) || 0;
                const orderB = Number(b.flash_sale_sort_order) || 0;
                if (orderA !== orderB) return orderA - orderB;
                return (a.id || 0) - (b.id || 0);
            });
        },

        get availableFlashSaleProducts() {
            return this.products.filter(p => !p.is_flash_sale && (p.status === 'Aktif' || p.is_active === true || p.is_active === 1));
        },

        getCalculatedFlashPrice() {
            const selected = this.products.find(p => p.id == this.flashSaleForm.product_id);
            if (!selected) return 0;
            const normal = Number(selected.normal_price || selected.price || 0);
            const val = Number(this.flashSaleForm.discount_value || 0);
            if (this.flashSaleForm.discount_type === 'percentage') {
                const disc = Math.round(normal * (Math.min(100, Math.max(0, val)) / 100));
                return Math.max(0, normal - disc);
            } else {
                return Math.max(0, normal - val);
            }
        },

        getCalculatedSavings() {
            const selected = this.products.find(p => p.id == this.flashSaleForm.product_id);
            if (!selected) return 0;
            const normal = Number(selected.normal_price || selected.price || 0);
            const val = Number(this.flashSaleForm.discount_value || 0);
            if (this.flashSaleForm.discount_type === 'percentage') {
                return Math.round(normal * (Math.min(100, Math.max(0, val)) / 100));
            } else {
                return Math.min(normal, Math.max(0, val));
            }
        },

        get isFlashSaleExpired() {
            if (!this.flashSale.end_at) return false;
            return new Date(this.flashSale.end_at).getTime() <= Date.now();
        },

        get flashSaleStatusBadge() {
            if (!this.flashSale.enabled) {
                return { text: 'STATUS: NONAKTIF', class: 'bg-gray-100 text-gray-600 border border-gray-200' };
            }
            if (this.isFlashSaleExpired) {
                return { text: 'STATUS: KADALUARSA (EXPIRED)', class: 'bg-amber-100 text-amber-800 border border-amber-300 font-bold' };
            }
            return { text: 'STATUS: AKTIF', class: 'bg-red-100 text-red-700 border border-red-200 font-extrabold' };
        },

        // ==========================================
        // TOAST & GENERAL HELPERS
        // ==========================================
        showToast(msg) {
            this.toastMessage = msg;
            this.toastVisible = true;
            setTimeout(() => { this.toastVisible = false; }, 3000);
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        },

        getImageUrl(path) {
            if (!path) return '/images/prod-beef-slice.jpg';
            if (path.startsWith('blob:') || path.startsWith('http')) return path;
            return path.startsWith('/') ? path : '/' + path;
        },

        hexToBadgeStyle(hex) {
            if (!hex) hex = '#059669';
            var h = hex.replace('#', '');
            if (h.length === 3) h = h.split('').map(function(x) { return x + x; }).join('');
            var num = parseInt(h, 16);
            var r = (num >> 16) & 255;
            var g = (num >> 8) & 255;
            var b = num & 255;
            return 'color: rgb(' + r + ', ' + g + ', ' + b + '); background-color: rgba(' + r + ', ' + g + ', ' + b + ', 0.12); border-color: rgba(' + r + ', ' + g + ', ' + b + ', 0.25);';
        },

        getBadgeStyle(typeIdOrName) {
            var char = this.availableCharacteristics.find(function(c) {
                return c.id === typeIdOrName || c.name === typeIdOrName || c.label === typeIdOrName;
            });
            var color = char ? (char.color || '#059669') : '#475569';
            return this.hexToBadgeStyle(color);
        },

        getCharUsageCount(char) {
            if (!char) return 0;
            var target = char.name || char.id;
            return this.products.filter(function(p) {
                return p.types && (p.types.includes(target) || p.types.includes(char.id) || p.types.includes(char.label));
            }).length;
        },

        // ==========================================
        // 1. SECTION HEADER SETTINGS SAVE
        // ==========================================
        async saveSectionSettings() {
            if (this.isSavingHeader) return;
            this.isSavingHeader = true;

            try {
                const res = await fetch('{{ route('admin.produk.section.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify(this.catalogSection)
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.showToast(data.message || 'Header katalog berhasil disimpan.');
                } else {
                    this.showToast('Gagal menyimpan: ' + (data.message || 'Terjadi kesalahan'));
                }
            } catch (err) {
                console.error(err);
                this.showToast('Terjadi kesalahan jaringan saat menyimpan header.');
            } finally {
                this.isSavingHeader = false;
            }
        },

        // ==========================================
        // 2. MASTER BADGES METHODS
        // ==========================================
        openCreateCharModal() {
            this.isEditingChar = false;
            this.charForm = {
                id: 'char_' + Date.now(),
                name: '',
                label: '',
                desc: '',
                color: '#059669',
                status: 'Aktif'
            };
            this.charModalOpen = true;
        },

        openEditCharModal(char) {
            this.isEditingChar = true;
            this.charForm = JSON.parse(JSON.stringify(char));
            this.charModalOpen = true;
        },

        saveChar() {
            if (!this.charForm.name.trim()) {
                alert('Nama karakteristik badge wajib diisi.');
                return;
            }
            this.charForm.label = this.charForm.name;
            if (this.isEditingChar) {
                const idx = this.availableCharacteristics.findIndex(c => c.id === this.charForm.id);
                if (idx !== -1) {
                    this.availableCharacteristics[idx] = JSON.parse(JSON.stringify(this.charForm));
                    this.showToast('Karakteristik ' + this.charForm.name + ' berhasil diperbarui!');
                }
            } else {
                const exists = this.availableCharacteristics.some(c => c.name.toLowerCase() === this.charForm.name.toLowerCase());
                if (exists) {
                    alert('Karakteristik dengan nama tersebut sudah ada.');
                    return;
                }
                this.charForm.id = this.charForm.name;
                this.availableCharacteristics.push(JSON.parse(JSON.stringify(this.charForm)));
                this.showToast('Karakteristik ' + this.charForm.name + ' berhasil ditambahkan!');
            }
            this.charModalOpen = false;
        },

        openDeleteCharFromEdit() {
            this.selectedChar = JSON.parse(JSON.stringify(this.charForm));
            this.charDeleteWarningCount = this.getCharUsageCount(this.selectedChar);
            this.charDeleteModalOpen = true;
        },

        confirmDeleteChar() {
            if (this.selectedChar) {
                var charName = this.selectedChar.name || this.selectedChar.id;
                var charId = this.selectedChar.id;
                this.products.forEach(function(p) {
                    if (p.types) {
                        p.types = p.types.filter(function(t) { return t !== charName && t !== charId; });
                    }
                });
                this.availableCharacteristics = this.availableCharacteristics.filter(function(c) {
                    return c.id !== charId && c.name !== charName;
                });
                this.charDeleteModalOpen = false;
                this.charModalOpen = false;
                this.showToast('Karakteristik ' + charName + ' telah dihapus.');
                this.selectedChar = null;
            }
        },

        getCharUsageCount(char) {
            if (!char) return 0;
            const name = char.name || char.id;
            return this.products.filter(p => p.types && p.types.includes(name)).length;
        },

        // ==========================================
        // MULTI-CATEGORY HELPERS
        // ==========================================
        toggleCategorySelection(catId) {
            catId = Number(catId);
            if (!this.form.category_ids || !Array.isArray(this.form.category_ids)) {
                this.form.category_ids = [];
            }
            
            // Normalize to unique integer array
            this.form.category_ids = Array.from(new Set(this.form.category_ids.map(Number)));
            
            const idx = this.form.category_ids.indexOf(catId);
            if (idx > -1) {
                if (this.form.category_ids.length > 1) {
                    this.form.category_ids.splice(idx, 1);
                } else {
                    this.showToast('Produk harus memiliki minimal 1 kategori.');
                    return;
                }
            } else {
                this.form.category_ids.push(catId);
            }
            
            this.form.category_ids.sort((a, b) => a - b);
            this.updateCategoryNames();
        },

        updateCategoryNames() {
            const names = [];
            (this.form.category_ids || []).forEach(id => {
                const found = this.categories.find(c => c.id == id);
                if (found) names.push(found.name);
            });
            this.form.category_names = names;
            this.form.category = names.length > 0 ? names.join(', ') : 'Daging Sapi';
        },

        getSelectedCategoriesSummary() {
            if (!this.form.category_ids || this.form.category_ids.length === 0) {
                return 'Pilih minimal 1 kategori...';
            }
            const names = [];
            this.form.category_ids.forEach(id => {
                const found = this.categories.find(c => c.id == id);
                if (found) names.push(found.name);
            });
            if (names.length === 0) return 'Pilih minimal 1 kategori...';
            if (names.length === 1) return names[0];
            if (names.length === 2) return names[0] + ', ' + names[1];
            return names[0] + ', ' + names[1] + ' +' + (names.length - 2) + ' lainnya';
        },

        // ==========================================
        // PRODUCT CRUD ACTIONS
        // ==========================================
        openCreateModal() {
            this.deleteModalOpen = false;
            this.deletingProduct = null;
            this.isEditing = false;
            const defaultCat = this.activeCategories[0] || { id: 1, name: 'Daging Sapi' };
            const defaultTypes = this.availableCharacteristics.filter(c => c.status === 'Aktif').slice(0, 2).map(c => c.name || c.id);
            this.form = {
                id: null,
                name: '',
                category_ids: [Number(defaultCat.id)],
                category_names: [defaultCat.name],
                category: defaultCat.name,
                types: defaultTypes.length > 0 ? defaultTypes : ['Frozen', 'Plain'],
                weight: '500g',
                weight_value: 500,
                unit: 'gram',
                price: 45000,
                status: 'Aktif',
                image: 'images/prod-beef-slice.jpg',
                description: '',
            };
            this.editorModalOpen = true;
        },

        openEditModal(p) {
            this.deleteModalOpen = false;
            this.deletingProduct = null;
            this.isEditing = true;
            this.form = JSON.parse(JSON.stringify(p));
            
            // Strictly extract category_ids from pivot relation or product object
            if (p.category_ids && Array.isArray(p.category_ids) && p.category_ids.length > 0) {
                this.form.category_ids = Array.from(new Set(p.category_ids.map(Number))).sort((a, b) => a - b);
            } else if (p.categories && Array.isArray(p.categories) && p.categories.length > 0) {
                this.form.category_ids = Array.from(new Set(p.categories.map(c => Number(c.id)))).sort((a, b) => a - b);
            } else if (p.category_id) {
                this.form.category_ids = [Number(p.category_id)];
            } else {
                const matched = this.categories.find(c => c.name === p.category);
                this.form.category_ids = matched ? [Number(matched.id)] : [1];
            }

            this.updateCategoryNames();
            this.form.types = (p.types && Array.isArray(p.types) && p.types.length > 0) ? [...p.types] : ['Frozen'];
            this.form.price = Number(p.price || p.normal_price || 0);
            this.form.normal_price = Number(p.normal_price || p.price || 0);
            this.form.status = (p.status === 'Aktif' || p.is_active === true || p.is_active === 1) ? 'Aktif' : 'Nonaktif';
            
            // Parse weight_value and unit cleanly
            if ((this.form.weight_value === undefined || this.form.weight_value === null) && this.form.weight) {
                const match = String(this.form.weight).match(/^(\d+)\s*(g|gram|kg|pcs|pack)?/i);
                if (match) {
                    this.form.weight_value = Number(match[1]);
                    const u = (match[2] || 'g').toLowerCase();
                    this.form.unit = (u === 'g' || u === 'gram') ? 'gram' : u;
                } else {
                    this.form.weight_value = 500;
                    this.form.unit = 'gram';
                }
            } else if (!this.form.unit) {
                this.form.unit = 'gram';
            }
            this.editorModalOpen = true;
        },

        toggleTypeSelection(typeId) {
            const idx = this.form.types.indexOf(typeId);
            if (idx > -1) {
                this.form.types.splice(idx, 1);
            } else {
                this.form.types.push(typeId);
            }
        },

        async saveProduct() {
            if (this.isSaving) return;

            if (!this.form.name || !this.form.name.trim()) {
                alert('Nama produk wajib diisi.');
                return;
            }
            if (!this.form.category_ids || this.form.category_ids.length === 0) {
                alert('Pilih minimal satu kategori produk.');
                return;
            }
            if (!this.form.types || this.form.types.length === 0) {
                alert('Pilih minimal satu karakteristik produk.');
                return;
            }

            this.isSaving = true;

            const normalizedCatIds = Array.from(new Set(this.form.category_ids.map(Number))).sort((a, b) => a - b);

            const payload = {
                name: this.form.name.trim(),
                slug: this.form.slug || '',
                category_ids: normalizedCatIds,
                description: this.form.description || '',
                image: this.form.image || 'images/prod-beef-slice.jpg',
                types: this.form.types || ['Fresh'],
                weight_value: parseFloat(this.form.weight_value || 500),
                unit: this.form.unit || 'gram',
                normal_price: parseFloat(this.form.price || this.form.normal_price || 0),
                discount_type: this.form.discount_type || null,
                discount_value: this.form.discount_value ? parseFloat(this.form.discount_value) : null,
                stock_status: this.form.stock_status || 'READY_STOCK',
                is_active: this.form.status === 'Aktif' || this.form.is_active === true,
                sort_order: parseInt(this.form.sort_order || 1),
            };

            try {
                const url = this.isEditing ? `/admin/produk/${this.form.id}` : '/admin/produk';
                const method = this.isEditing ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify(payload),
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    let errMsg = result.message || 'Gagal menyimpan produk.';
                    if (result.errors) {
                        const errs = Object.values(result.errors).flat();
                        if (errs.length > 0) errMsg = errs.join('\n');
                    }
                    alert(errMsg);
                    return;
                }

                // Extract verified server data
                const persisted = result.product || {};
                const persistedCatIds = (persisted.category_ids && persisted.category_ids.length > 0)
                    ? persisted.category_ids.map(Number)
                    : ((persisted.categories && persisted.categories.length > 0)
                        ? persisted.categories.map(c => Number(c.id))
                        : payload.category_ids);

                const catNames = [];
                persistedCatIds.forEach(id => {
                    const c = this.categories.find(cat => cat.id == id);
                    if (c) catNames.push(c.name);
                });
                const catDisplay = catNames.length > 0 ? catNames.join(', ') : 'Daging Sapi';

                const updatedProductData = {
                    ...(this.isEditing ? this.products.find(p => p.id === this.form.id) : {}),
                    ...persisted,
                    ...payload,
                    id: persisted.id || this.form.id || result.id,
                    category_id: persistedCatIds[0],
                    category_ids: persistedCatIds,
                    category_names: catNames,
                    category: catDisplay,
                    price: payload.normal_price,
                    normal_price: payload.normal_price,
                    status: payload.is_active ? 'Aktif' : 'Nonaktif',
                };

                if (this.isEditing) {
                    const idx = this.products.findIndex(p => p.id === this.form.id);
                    if (idx !== -1) {
                        this.products[idx] = updatedProductData;
                    }
                    this.showToast(result.message || `Produk ${this.form.name} berhasil diperbarui!`);
                } else {
                    this.products.unshift(updatedProductData);
                    this.showToast(result.message || 'Produk baru berhasil ditambahkan ke katalog!');
                }

                this.editorModalOpen = false;
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat menghubungi server.');
            } finally {
                this.isSaving = false;
            }
        },

        async toggleStatus(p) {
            try {
                const response = await fetch(`/admin/produk/${p.id}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    alert(result.message || 'Gagal mengubah status produk.');
                    return;
                }

                p.is_active = result.is_active;
                p.status = result.is_active ? 'Aktif' : 'Nonaktif';
                this.showToast(result.message || `Status ${p.name} diubah menjadi ${p.status}`);
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat mengubah status produk.');
            }
        },

        openDelete(p) {
            this.editorModalOpen = false;
            this.deletingProduct = p;
            this.deleteModalOpen = true;
        },

        closeDeleteModal() {
            this.deleteModalOpen = false;
            this.deletingProduct = null;
        },

        async confirmDelete() {
            if (!this.deletingProduct) return;

            try {
                const prodId = this.deletingProduct.id;
                const prodName = this.deletingProduct.name;

                const response = await fetch(`/admin/produk/${prodId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    alert(result.message || 'Gagal menghapus produk.');
                    return;
                }

                this.products = this.products.filter(p => p.id !== prodId);
                this.closeDeleteModal();
                this.showToast(result.message || `Produk ${prodName} berhasil dihapus.`);
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat menghapus produk.');
            }
        },

        // ==========================================
        // 4. MEDIA PICKER METHODS
        // ==========================================
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
                this.showToast('Gambar produk dipilih dari Media Library!');
            } else if (this.mediaTab === 'upload' && this.uploadedFile && this.uploadedFile.path) {
                this.form.image = this.uploadedFile.path;
                this.mediaPickerOpen = false;
                this.showToast('Gambar hasil upload berhasil digunakan!');
            } else if (this.selectedMedia) {
                this.form.image = this.selectedMedia.path;
                this.mediaPickerOpen = false;
                this.showToast('Gambar produk dipilih dari Media Library!');
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

        // ==========================================
        // 5. FLASH SALE METHODS
        // ==========================================
        async toggleFlashSale(enable) {
            if (this.isTogglingFlashSale) return;
            this.isTogglingFlashSale = true;

            try {
                const response = await fetch('/admin/flash-sale/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({ 
                        enabled: enable,
                        end_at: this.flashSale.end_at 
                    }),
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    this.showToast('Gagal: ' + (result.message || 'Gagal mengubah status Flash Sale.'));
                    return;
                }

                this.flashSale = result.flash_sale;
                this.showToast(result.message || (enable ? 'Flash Sale berhasil diaktifkan!' : 'Flash Sale berhasil dinonaktifkan.'));
            } catch (err) {
                console.error(err);
                this.showToast('Terjadi kesalahan jaringan saat mengubah status Flash Sale.');
            } finally {
                this.isTogglingFlashSale = false;
            }
        },

        async saveFlashSaleSettings() {
            if (this.isSavingFlashSaleSettings) return;
            this.isSavingFlashSaleSettings = true;

            try {
                const response = await fetch('/admin/flash-sale/settings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        title: this.flashSale.title,
                        subtitle: this.flashSale.subtitle,
                        end_at: this.flashSale.end_at,
                    }),
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    this.showToast('Gagal menyimpan: ' + (result.message || 'Terjadi kesalahan'));
                    return;
                }

                this.flashSale = result.flash_sale;
                this.showToast(result.message || 'Pengaturan Flash Sale berhasil disimpan!');
            } catch (err) {
                console.error(err);
                this.showToast('Terjadi kesalahan jaringan saat menyimpan pengaturan Flash Sale.');
            } finally {
                this.isSavingFlashSaleSettings = false;
            }
        },

        openAssignFlashSaleModal() {
            const unassigned = this.availableFlashSaleProducts;
            if (unassigned.length === 0) {
                this.showToast('Semua produk aktif sudah terdaftar dalam Flash Sale.');
                return;
            }

            this.flashSaleForm = {
                product_id: unassigned[0].id,
                discount_type: 'percentage',
                discount_value: 20,
                sort_order: this.flashSaleProductsList.length + 1,
            };
            this.flashSaleModalOpen = true;
        },

        async assignProductToFlashSale() {
            if (this.isAssigningFlashSale) return;

            if (!this.flashSaleForm.product_id) {
                this.showToast('Pilih produk yang akan dimasukkan ke Flash Sale.');
                return;
            }

            this.isAssigningFlashSale = true;

            try {
                const response = await fetch('/admin/flash-sale/assign', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify(this.flashSaleForm),
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    this.showToast('Gagal: ' + (result.message || 'Gagal menambahkan produk ke Flash Sale.'));
                    return;
                }

                const idx = this.products.findIndex(p => p.id == this.flashSaleForm.product_id);
                if (idx !== -1) {
                    this.products[idx].is_flash_sale = true;
                    this.products[idx].flash_sale_discount_type = this.flashSaleForm.discount_type;
                    this.products[idx].flash_sale_discount_value = parseFloat(this.flashSaleForm.discount_value);
                    this.products[idx].flash_sale_sort_order = parseInt(this.flashSaleForm.sort_order);
                }

                this.flashSaleModalOpen = false;
                this.showToast(result.message || 'Produk berhasil ditambahkan ke Flash Sale!');
            } catch (err) {
                console.error(err);
                this.showToast('Terjadi kesalahan jaringan saat menambahkan produk ke Flash Sale.');
            } finally {
                this.isAssigningFlashSale = false;
            }
        },

        async removeProductFromFlashSale(p) {
            if (!confirm(`Hapus "${p.name}" dari Flash Sale? (Harga regular produk tidak akan berubah)`)) {
                return;
            }

            this.isRemovingFlashSaleId = p.id;

            try {
                const response = await fetch(`/admin/flash-sale/remove/${p.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    this.showToast('Gagal: ' + (result.message || 'Gagal menghapus produk dari Flash Sale.'));
                    return;
                }

                p.is_flash_sale = false;
                this.showToast(result.message || 'Produk berhasil dihapus dari Flash Sale.');
            } catch (err) {
                console.error(err);
                this.showToast('Terjadi kesalahan jaringan saat menghapus produk dari Flash Sale.');
            } finally {
                this.isRemovingFlashSaleId = null;
            }
        },

        async moveFlashSaleOrder(p, direction) {
            const list = this.flashSaleProductsList;
            const currentIndex = list.findIndex(item => item.id === p.id);
            if (currentIndex === -1) return;

            const targetIndex = direction === 'up' ? currentIndex - 1 : currentIndex + 1;
            if (targetIndex < 0 || targetIndex >= list.length) return;

            const currentItem = list[currentIndex];
            const targetItem = list[targetIndex];

            // Reconstruct ordered array with swapped positions
            const newList = [...list];
            newList[currentIndex] = targetItem;
            newList[targetIndex] = currentItem;

            // Build orders map with 1-based sequential ranks
            const orders = {};
            newList.forEach((item, idx) => {
                const newOrder = idx + 1;
                item.flash_sale_sort_order = newOrder;
                orders[item.id] = newOrder;

                // Sync with this.products array
                const prod = this.products.find(prodItem => prodItem.id === item.id);
                if (prod) {
                    prod.flash_sale_sort_order = newOrder;
                }
            });

            // Persist order to server via POST /admin/flash-sale/reorder
            try {
                const response = await fetch('/admin/flash-sale/reorder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({ orders: orders }),
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    this.showToast('Gagal menyimpan urutan: ' + (result.message || 'Error'));
                    return;
                }

                this.showToast(`Urutan Flash Sale diperbarui: "${p.name}" menjadi urutan #${p.flash_sale_sort_order}`);
            } catch (err) {
                console.error(err);
                this.showToast('Terjadi kesalahan jaringan saat memperbarui urutan.');
            }
        },

        // ==========================================
        // CATALOG DRAG & DROP REORDERING METHODS (LIVE INSERTION & SPACE SHIFTING)
        // ==========================================
        startCatalogReorder() {
            this.catalogOrderSnapshot = JSON.parse(JSON.stringify(this.products));
            this.isReorderingCatalog = true;
            this.searchQuery = '';
            this.selectedCategoryFilter = 'all';
            this.selectedTypeFilter = 'all';
            this.showToast('Mode Edit Posisi Aktif: Drag & drop kartu produk untuk mengatur urutan tampilan.');
        },

        cancelCatalogReorder() {
            if (this.catalogOrderSnapshot) {
                this.products = JSON.parse(JSON.stringify(this.catalogOrderSnapshot));
            }
            this.catalogOrderSnapshot = null;
            this.isReorderingCatalog = false;
            this.confirmReorderModalOpen = false;
            this.draggedProductId = null;
            this.dragOverProductId = null;
            this.showToast('Pengaturan posisi produk dibatalkan.');
        },

        openConfirmReorderModal() {
            this.confirmReorderModalOpen = true;
        },

        async saveCatalogReorder() {
            if (this.isSavingCatalogOrder) return;
            this.isSavingCatalogOrder = true;

            const orders = {};
            this.products.forEach((p, idx) => {
                const rank = idx + 1;
                p.sort_order = rank;
                orders[p.id] = rank;
            });

            try {
                const response = await fetch('/admin/produk/reorder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({ orders: orders }),
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    this.confirmReorderModalOpen = false;
                    this.showToast('Gagal menyimpan urutan: ' + (result.message || 'Error'));
                    return;
                }

                this.confirmReorderModalOpen = false;
                this.isReorderingCatalog = false;
                this.catalogOrderSnapshot = null;
                this.showToast('Urutan produk katalog berhasil disimpan!');
            } catch (err) {
                console.error(err);
                this.confirmReorderModalOpen = false;
                this.showToast('Terjadi kesalahan jaringan saat menyimpan urutan.');
            } finally {
                this.isSavingCatalogOrder = false;
            }
        },

        // Live Insertion Drag & Drop Handlers
        onDragStart(event, prod) {
            if (!this.isReorderingCatalog) return;
            this.draggedProductId = prod.id;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', String(prod.id));
        },

        onDragOver(event, targetProd) {
            if (!this.isReorderingCatalog || !this.draggedProductId) return;
            event.preventDefault();
            if (event.dataTransfer) {
                event.dataTransfer.dropEffect = 'move';
            }

            if (this.draggedProductId === targetProd.id) return;

            const fromIndex = this.products.findIndex(p => p.id === this.draggedProductId);
            const toIndex = this.products.findIndex(p => p.id === targetProd.id);

            if (fromIndex !== -1 && toIndex !== -1 && fromIndex !== toIndex) {
                // Live shift: remove from previous position and insert at target position (giving empty space)
                const movedItem = this.products.splice(fromIndex, 1)[0];
                this.products.splice(toIndex, 0, movedItem);

                // Update sequential sort_orders in real-time
                this.products.forEach((p, idx) => {
                    p.sort_order = idx + 1;
                });
            }
        },

        onDrop(event) {
            if (!this.isReorderingCatalog) return;
            event.preventDefault();
            this.draggedProductId = null;
            this.dragOverProductId = null;
        },

        onDragEnd(event) {
            this.draggedProductId = null;
            this.dragOverProductId = null;
        }
    };
};

window.initialProductPayload = {
    products: @json($products),
    categories: @json($categories),
    contactSettings: @json($contactSettings),
    mediaLibrary: @json($mediaLibrary),
    catalogSection: @json($catalogSection),
    flashSaleSetting: @json($flashSaleSetting ?? ['enabled' => false, 'end_at' => null]),
    csrfToken: '{{ csrf_token() }}'
};
</script>

<div class="space-y-6"
     x-data="adminProductManager(window.initialProductPayload)">
    
    <!-- ======================================================= -->
    <!-- 1. HEADER HERO CARD                                     -->
    <!-- ======================================================= -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-8 shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-brand-dark tracking-tight">
                        Katalog Produk
                    </h2>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        <span>DYNAMIC CATALOG</span>
                    </span>
                    <span class="text-xs text-gray-500 font-medium">
                        • Single Source of Truth Kategori &amp; Master Badges
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 leading-relaxed max-w-3xl">
                    Kelola seluruh produk segar dan frozen. Pilihan kategori terhubung langsung dengan <strong>Category Manager</strong>, WhatsApp Destination terpusat ke <strong>Contact Settings</strong>, dan multi-badge karakteristik terintegrasi.
                </p>
            </div>

            <!-- Create Action Button -->
            <div class="flex items-center gap-2.5 shrink-0">
                <!-- Tambah Produk Button -->
                <button @click="openCreateModal()" 
                        type="button"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Produk</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 2. TAB NAVIGATION (KATALOG PRODUK vs FLASH SALE)        -->
    <!-- ======================================================= -->
    <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
        <button type="button" 
                @click="activeTab = 'catalog'"
                class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-modern font-extrabold text-xs transition-all cursor-pointer shadow-2xs"
                :class="activeTab === 'catalog' 
                    ? 'bg-brand-primary text-white shadow-sm ring-2 ring-brand-primary/30' 
                    : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'">
            <span class="text-base">📦</span>
            <span>Katalog Produk</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                  :class="activeTab === 'catalog' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700'"
                  x-text="products.length + ' Produk'"></span>
        </button>

        <button type="button" 
                @click="activeTab = 'flash_sale'"
                class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-modern font-extrabold text-xs transition-all cursor-pointer shadow-2xs"
                :class="activeTab === 'flash_sale' 
                    ? 'bg-red-600 text-white shadow-sm ring-2 ring-red-600/30' 
                    : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'">
            <span class="text-base">⚡</span>
            <span>Flash Sale &amp; Countdown</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                  :class="activeTab === 'flash_sale' ? 'bg-white/20 text-white' : (flashSale.enabled ? 'bg-red-100 text-red-700 border border-red-300' : 'bg-gray-100 text-gray-600')"
                  x-text="flashSale.enabled ? 'AKTIF (' + flashSaleProductsList.length + ')' : 'NONAKTIF'"></span>
        </button>
    </div>

    <!-- ======================================================= -->
    <!-- TAB 1: KATALOG PRODUK CONTENT                           -->
    <!-- ======================================================= -->
    <div x-show="activeTab === 'catalog'" class="space-y-6">
        
        <!-- PENGATURAN SECTION KATALOG & MASTER BADGE -->
        <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-7 shadow-2xs space-y-6">
            
            <!-- PENGATURAN SECTION KATALOG PRODUK -->
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        <span class="text-base shrink-0">⚙️</span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-xs sm:text-sm font-extrabold text-brand-dark uppercase tracking-wider truncate sm:whitespace-normal">
                                Pengaturan Section Header Katalog Produk
                            </h3>
                            <p class="text-[11px] text-gray-500 leading-relaxed">
                                Kelola label badge, judul utama (headline), dan deskripsi pengantar pada section katalog produk (<code>&lt;section id="produk"&gt;</code>) Landing Page.
                            </p>
                        </div>
                    </div>

                    <button @click="saveSectionSettings()" 
                            type="button" 
                            :disabled="isSavingHeader"
                            class="px-4 py-2 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark shadow-2xs transition-all cursor-pointer shrink-0 whitespace-nowrap inline-flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="isSavingHeader" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isSavingHeader ? 'Menyimpan...' : 'Simpan Header'"></span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Label Badge -->
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Label Badge Section
                        </label>
                        <input type="text" 
                               x-model="catalogSection.label" 
                               placeholder="Contoh: Katalog Lengkap"
                               class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-semibold text-brand-primary focus:ring-2 focus:ring-brand-primary/30">
                    </div>

                    <!-- Judul Utama -->
                    <div class="md:col-span-4">
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Judul Utama / Heading
                        </label>
                        <input type="text" 
                               x-model="catalogSection.title" 
                               placeholder="Contoh: Produk Pilihan"
                               class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-extrabold text-brand-dark focus:ring-2 focus:ring-brand-primary/30">
                    </div>

                    <!-- Deskripsi Pengantar -->
                    <div class="md:col-span-5">
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Deskripsi Pengantar
                        </label>
                        <textarea x-model="catalogSection.subtitle" 
                                  rows="2" 
                                  placeholder="Pilih bahan masak sesuai kebutuhanmu. Tersedia skala retail rumah tangga maupun pembelian curah."
                                  class="w-full text-xs rounded-modern border border-gray-300 p-2 bg-white leading-relaxed focus:ring-2 focus:ring-brand-primary/30"></textarea>
                    </div>
                </div>

                <!-- Small Header Section Realtime Preview -->
                <div class="pt-1">
                    <div class="bg-brand-cream/60 rounded-modern-xl border border-dashed border-gray-300 p-4 sm:p-5 text-center max-w-xl mx-auto shadow-2xs">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-brand-soft-green text-brand-primary mb-2 shadow-2xs transition-all"
                              x-text="catalogSection.label || 'Katalog Lengkap'">
                        </span>
                        <h4 class="text-lg sm:text-xl font-extrabold text-brand-dark tracking-tight mb-1.5 transition-all"
                            x-text="catalogSection.title || 'Produk Pilihan'">
                        </h4>
                        <p class="text-xs text-gray-600 font-normal leading-relaxed max-w-md mx-auto transition-all"
                           x-text="catalogSection.subtitle || 'Pilih bahan masak sesuai kebutuhanmu. Tersedia skala retail rumah tangga maupun pembelian curah.'">
                        </p>
                    </div>
                </div>
            </div>

            <!-- MASTER BADGE KARAKTERISTIK PRODUK -->
            <div class="pt-4 border-t border-gray-100 space-y-3">
                <div>
                    <h3 class="text-xs sm:text-sm font-extrabold text-brand-dark uppercase tracking-wider flex items-center gap-1.5">
                        <span>🏷️</span>
                        <span>Karakteristik Produk (Master Badges)</span>
                    </h3>
                    <p class="text-[11px] text-gray-500">
                        Klik badge untuk mengedit warna/nama/status/hapus. Klik <strong>+ Tambah Badge</strong> untuk membuat badge baru.
                    </p>
                </div>

                <!-- Badges Grid -->
                <div class="flex items-center gap-2.5 flex-wrap pt-1">
                    <template x-for="char in availableCharacteristics" :key="char.id">
                        <button type="button" 
                                @click="openEditCharModal(char)"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold border shadow-2xs hover:scale-105 active:scale-95 transition-all cursor-pointer"
                                :style="hexToBadgeStyle(char.color)"
                                :class="char.status === 'Nonaktif' ? 'opacity-50 line-through' : ''"
                                :title="'Klik untuk edit: ' + (char.name || char.label) + (char.status === 'Nonaktif' ? ' (Nonaktif)' : '')">
                            <span x-text="char.name || char.label"></span>
                            <span x-show="char.status === 'Nonaktif'" class="text-[9px] no-underline font-normal text-gray-500">(off)</span>
                        </button>
                    </template>

                    <!-- + Tambah Badge Item -->
                    <button type="button" 
                            @click="openCreateCharModal()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold border-2 border-dashed border-gray-300 text-gray-600 bg-gray-50 hover:bg-gray-100 hover:border-brand-primary hover:text-brand-primary transition-all cursor-pointer">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Badge</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- ======================================================= -->
        <!-- SECTION 1: FILTER TOOLBAR                               -->
        <!-- ======================================================= -->
        <div x-show="!isReorderingCatalog" class="bg-white rounded-modern-xl border border-gray-200/80 p-4 shadow-2xs">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <!-- Search Box -->
                <div class="relative flex-1 min-w-[200px]">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" 
                           x-model="searchQuery"
                           placeholder="Cari nama produk..." 
                           class="w-full pl-9 pr-4 py-2 rounded-modern text-xs border border-gray-300 focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary bg-gray-50/50">
                </div>

                <!-- Category Filter -->
                <div class="w-full sm:w-48 shrink-0">
                    <select x-model="selectedCategoryFilter" 
                            class="w-full py-2 px-3 rounded-modern text-xs border border-gray-300 bg-white font-medium text-brand-dark focus:ring-2 focus:ring-brand-primary/30">
                        <option value="all">Semua Kategori</option>
                        <template x-for="cat in activeCategories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Characteristic Filter -->
                <div class="w-full sm:w-48 shrink-0">
                    <select x-model="selectedTypeFilter" 
                            class="w-full py-2 px-3 rounded-modern text-xs border border-gray-300 bg-white font-medium text-brand-dark focus:ring-2 focus:ring-brand-primary/30">
                        <option value="all">Semua Karakteristik</option>
                        <template x-for="char in availableCharacteristics" :key="char.id">
                            <option :value="char.name || char.id" x-text="(char.name || char.label) + (char.status !== 'Aktif' ? ' (Nonaktif)' : '')"></option>
                        </template>
                    </select>
                </div>
            </div>
        </div>

        <!-- ======================================================= -->
        <!-- SECTION 2: ACTION BAR KATALOG (BARU)                     -->
        <!-- ======================================================= -->
        <!-- Normal Mode Action Bar -->
        <div x-show="!isReorderingCatalog" class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 px-1">
            <!-- Left: Product Count -->
            <div class="text-xs sm:text-sm text-gray-600 font-medium whitespace-nowrap">
                Menampilkan: <span class="font-bold text-brand-dark" x-text="filteredProducts.length"></span> dari <span class="font-bold text-brand-dark" x-text="products.length"></span> Produk
            </div>

            <!-- Right: Edit Posisi Button -->
            <button type="button" 
                    @click="startCatalogReorder()" 
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-modern font-bold text-xs text-brand-dark bg-amber-50 hover:bg-amber-100 border border-amber-300 shadow-2xs hover:shadow transition-all cursor-pointer whitespace-nowrap self-start sm:self-auto">
                <span class="text-sm leading-none">⇅</span>
                <span>Edit Posisi</span>
            </button>
        </div>

        <!-- Reorder Mode Active Bar -->
        <div x-show="isReorderingCatalog" class="bg-amber-50 border border-amber-300 rounded-modern-xl p-3.5 sm:p-4 shadow-2xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-[11px] font-normal text-amber-900">
                <span class="text-sm animate-pulse shrink-0 font-bold">⇅</span>
                <span>Mode Edit Posisi Aktif: Drag & drop kartu produk untuk mengatur urutan tampilan katalog.</span>
            </div>
            <div class="flex items-center gap-2.5 shrink-0 self-end sm:self-auto">
                <button type="button" 
                        @click="cancelCatalogReorder()" 
                        class="px-4 py-2 rounded-modern font-bold text-xs text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 shadow-2xs transition-colors cursor-pointer whitespace-nowrap">
                    Batal
                </button>
                <button type="button" 
                        @click="openConfirmReorderModal()" 
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan Posisi</span>
                </button>
            </div>
        </div>

        <!-- ======================================================= -->
        <!-- SECTION 3: PRODUCTS GRID                                -->
        <!-- ======================================================= -->

        <!-- PRODUCTS GRID -->
        <template x-if="filteredProducts.length === 0">
            <div class="bg-white rounded-modern-xl border border-dashed border-gray-300 p-12 text-center space-y-3 shadow-2xs">
                <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto text-xl">
                    🔍
                </div>
                <h4 class="text-sm font-bold text-brand-dark">Tidak ada produk yang cocok</h4>
                <p class="text-xs text-gray-500 max-w-sm mx-auto">
                    Coba sesuaikan kata kunci pencarian atau reset filter kategori / karakteristik di atas.
                </p>
                <button type="button" 
                        @click="searchQuery = ''; selectedCategoryFilter = 'all'; selectedTypeFilter = 'all';"
                        class="px-4 py-2 rounded-modern text-xs font-bold text-brand-primary bg-brand-soft-green hover:bg-emerald-100 transition-colors cursor-pointer">
                    Reset Filter
                </button>
            </div>
        </template>

        <div x-show="filteredProducts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-4 sm:gap-5">
            <template x-for="(prod, idx) in filteredProducts" :key="prod.id">
                <div :draggable="isReorderingCatalog"
                     @dragstart="onDragStart($event, prod)"
                     @dragover.prevent="onDragOver($event, prod)"
                     @drop.prevent="onDrop($event)"
                     @dragend="onDragEnd($event)"
                     class="transition-all duration-150 relative">
                    
                    <!-- 1. EMPTY SPACE / PLACEHOLDER DROPZONE (Tampil sebagai Ruang Kosong pada posisi yang sedang di-drag) -->
                    <div x-show="isReorderingCatalog && draggedProductId === prod.id" 
                         class="h-full min-h-[380px] rounded-modern-xl border-2 border-dashed border-brand-primary bg-emerald-50/60 p-6 flex flex-col items-center justify-center text-center space-y-3 shadow-inner select-none pointer-events-none transition-all">
                        <div class="w-12 h-12 rounded-full bg-brand-primary/10 border border-brand-primary/30 text-brand-primary flex items-center justify-center font-black text-base shadow-xs">
                            <span x-text="'#' + (idx + 1)"></span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-xs font-black uppercase tracking-wider text-brand-primary block">
                                Posisi Baru <span x-text="'#' + (idx + 1)"></span>
                            </span>
                            <span class="text-[11px] text-gray-500 font-medium">
                                Ruang kosong untuk kartu
                            </span>
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white text-[11px] font-bold text-brand-dark shadow-2xs border border-gray-200">
                            <span class="truncate max-w-[150px]" x-text="prod.name"></span>
                        </div>
                    </div>

                    <!-- 2. REGULAR PRODUCT CARD (Tampil saat tidak sedang aktif di-drag) -->
                    <div x-show="!isReorderingCatalog || draggedProductId !== prod.id"
                         class="bg-white rounded-modern-xl border overflow-hidden transition-all flex flex-col justify-between relative group h-full"
                         :class="{
                             'opacity-70 bg-gray-50/80': prod.status === 'Nonaktif' && !isReorderingCatalog,
                             'cursor-grab active:cursor-grabbing hover:shadow-xl border-brand-primary/40 ring-1 ring-brand-primary/20': isReorderingCatalog,
                             'border-gray-200/80 shadow-2xs hover:shadow-card': !isReorderingCatalog
                         }">
                        
                        <div>
                            <!-- 4:3 Aspect Ratio Thumbnail Container -->
                            <div class="relative aspect-[4/3] w-full bg-brand-dark overflow-hidden">
                                <img :src="getImageUrl(prod.image)" :alt="prod.name" class="w-full h-full object-cover pointer-events-none">
                                
                                <!-- Reorder Mode Position Badge & Drag Grip Handle -->
                                <div x-show="isReorderingCatalog" class="absolute top-2.5 right-2.5 z-30 flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-brand-dark/90 text-white backdrop-blur-xs text-xs font-black shadow-md border border-white/20">
                                    <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="8" cy="6" r="2"></circle>
                                        <circle cx="16" cy="6" r="2"></circle>
                                        <circle cx="8" cy="12" r="2"></circle>
                                        <circle cx="16" cy="12" r="2"></circle>
                                        <circle cx="8" cy="18" r="2"></circle>
                                        <circle cx="16" cy="18" r="2"></circle>
                                    </svg>
                                    <span x-text="'#' + (idx + 1)"></span>
                                </div>

                                <!-- Top Left: Characteristic Badges (Multi-Badge Display from Master) -->
                                <div x-show="!isReorderingCatalog" class="absolute top-2.5 left-2.5 flex flex-wrap gap-1 max-w-[70%]">
                                    <template x-for="t in (prod.types || [])" :key="t">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold border shadow-2xs transition-all"
                                              :style="getBadgeStyle(t)"
                                              x-text="t">
                                        </span>
                                    </template>
                                </div>

                                <!-- Top Right: Weight Pill (Hidden during Reorder Mode) -->
                                <div x-show="!isReorderingCatalog" class="absolute top-2.5 right-2.5">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-black/60 text-white backdrop-blur-xs shadow-2xs"
                                          x-text="prod.weight">
                                    </span>
                                </div>

                                <!-- Bottom Category Tag -->
                                <div class="absolute bottom-2 left-2.5 max-w-[85%]">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-white/90 text-brand-dark backdrop-blur-xs shadow-2xs truncate block"
                                          x-text="prod.category">
                                    </span>
                                </div>
                            </div>

                            <!-- Product Body -->
                            <div class="p-4 space-y-2">
                                <h4 class="font-extrabold text-brand-dark text-xs sm:text-sm line-clamp-2 leading-snug" x-text="prod.name"></h4>
                                <p class="text-[11px] text-gray-500 line-clamp-2 leading-relaxed" x-text="prod.description || 'Bahan masakan bermutu tinggi dan higienis.'"></p>
                                
                                <div class="pt-2 flex items-center justify-between border-t border-gray-100">
                                    <div>
                                        <span class="text-[10px] text-gray-400 block leading-tight">Harga:</span>
                                        <span class="text-xs sm:text-sm font-black text-brand-primary" x-text="formatRupiah(prod.price || prod.normal_price)"></span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[9px] text-gray-400 block leading-tight">Status Stok:</span>
                                        <span class="text-[10px] font-bold uppercase" 
                                              :class="prod.stock_status === 'OUT_OF_STOCK' ? 'text-rose-600' : (prod.stock_status === 'PRE_ORDER' ? 'text-amber-600' : 'text-emerald-700')"
                                              x-text="prod.stock_status === 'OUT_OF_STOCK' ? 'HABIS' : (prod.stock_status === 'PRE_ORDER' ? 'PRE-ORDER' : 'READY')"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="p-3 bg-gray-50/80 border-t border-gray-100">
                            <!-- Normal Mode Actions -->
                            <div x-show="!isReorderingCatalog" class="flex items-center justify-between gap-1.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border inline-flex items-center gap-1.5"
                                      :class="prod.status === 'Aktif' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-gray-100 text-gray-600 border-gray-300'">
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                          :class="prod.status === 'Aktif' ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400'"></span>
                                    <span x-text="prod.status"></span>
                                </span>

                                <div class="flex items-center gap-1">
                                    <button @click.stop="openEditModal(prod)" 
                                            type="button"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-modern text-xs font-bold text-brand-primary bg-brand-soft-green hover:bg-emerald-100 transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span>Edit</span>
                                    </button>
                                    <button @click.stop="toggleStatus(prod)" 
                                            type="button"
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-modern text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer"
                                            :title="prod.status === 'Aktif' ? 'Nonaktifkan Produk' : 'Aktifkan Produk'">
                                        <span class="w-2 h-2 rounded-full shrink-0"
                                              :class="prod.status === 'Aktif' ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                                        <span class="text-[10px]" x-text="prod.status === 'Aktif' ? 'On' : 'Off'"></span>
                                    </button>
                                    <button @click.stop="openDelete(prod)" 
                                            type="button"
                                            class="p-1.5 rounded-modern text-rose-500 hover:bg-rose-50 hover:text-rose-600 transition-colors cursor-pointer inline-flex items-center justify-center" 
                                            title="Hapus Produk">
                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Reorder Mode Drag Guide -->
                            <div x-show="isReorderingCatalog" class="text-center py-0.5 text-[11px] font-bold text-brand-primary flex items-center justify-center gap-1.5 select-none">
                                <span class="text-sm">⠿</span>
                                <span>Tarik untuk geser posisi</span>
                            </div>
                        </div>

                    </div>

                </div>
            </template>
        </div>

    </div>

    <!-- ======================================================= -->
    <!-- TAB 2: FLASH SALE & COUNTDOWN CONTENT                   -->
    <!-- ======================================================= -->
    <div x-show="activeTab === 'flash_sale'" class="space-y-6">
        
        <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-7 shadow-2xs space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100 pb-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xl">⚡</span>
                        <h3 class="text-sm sm:text-base font-extrabold text-brand-dark uppercase tracking-wider">
                            Program Flash Sale &amp; Countdown
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold"
                              :class="flashSaleStatusBadge.class"
                              x-text="flashSaleStatusBadge.text"></span>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed max-w-2xl">
                        Tampilkan section promo kilat dengan timer hitung mundur di Landing Page. Flash Sale menggunakan produk katalog dengan diskon promo independen.
                    </p>
                </div>

                <!-- Global ON/OFF Toggle Button with Loading State -->
                <div class="flex items-center gap-3 shrink-0">
                    <button type="button" 
                            @click="toggleFlashSale(!flashSale.enabled)"
                            :disabled="isTogglingFlashSale"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern font-extrabold text-xs text-white transition-all cursor-pointer shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="flashSale.enabled ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700'">
                        <svg x-show="isTogglingFlashSale" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isTogglingFlashSale ? 'Memproses...' : (flashSale.enabled ? 'Matikan Flash Sale' : 'Aktifkan Flash Sale')"></span>
                    </button>
                </div>
            </div>

            <!-- Expired Alert Notice if Flash Sale enabled but end_at is past -->
            <template x-if="flashSale.enabled && isFlashSaleExpired">
                <div class="p-3.5 bg-amber-50 rounded-modern border border-amber-300 text-xs text-amber-900 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="text-lg shrink-0">⚠️</span>
                        <span>Waktu hitung mundur Flash Sale telah berakhir (expired). Perbarui <strong>Waktu Berakhir</strong> di bawah ke masa depan agar promo tampil kembali di Landing Page.</span>
                    </div>
                </div>
            </template>

            <!-- Settings Form: Title, Subtitle, End At -->
            <div class="bg-gray-50/70 p-5 rounded-modern-lg border border-gray-200/60 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-200/60 pb-2">
                    <h4 class="text-xs font-extrabold text-brand-dark uppercase tracking-wider">
                        Pengaturan Teks &amp; Jadwal Flash Sale
                    </h4>
                    <span class="text-[11px] text-gray-500">Tersinkronisasi otomatis dengan Landing Page</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Judul Flash Sale -->
                    <div class="md:col-span-7">
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Judul Flash Sale <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               x-model="flashSale.title" 
                               placeholder="Flash Sale Terbatas!" 
                               class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-bold text-brand-dark focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary">
                    </div>

                    <!-- Waktu Berakhir (Countdown End) -->
                    <div class="md:col-span-5">
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Waktu Berakhir (Countdown End) <span class="text-rose-500">*</span>
                        </label>
                        <input type="datetime-local" 
                               x-model="flashSale.end_at" 
                               class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-mono font-bold text-red-600 focus:ring-2 focus:ring-red-500/30 focus:border-red-500">
                    </div>

                    <!-- Subtitle Flash Sale -->
                    <div class="md:col-span-12">
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Subtitle / Deskripsi Pengantar Flash Sale
                        </label>
                        <textarea x-model="flashSale.subtitle" 
                                  rows="2" 
                                  placeholder="Dapatkan potongan harga spesial untuk produk protein pilihan hari ini. Stok terbatas!" 
                                  class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium text-brand-dark focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-2">
                    <button type="button" 
                            @click="saveFlashSaleSettings()" 
                            :disabled="isSavingFlashSaleSettings"
                            class="px-5 py-2.5 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark transition-all cursor-pointer shadow-2xs inline-flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="isSavingFlashSaleSettings" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isSavingFlashSaleSettings ? 'Menyimpan...' : 'Simpan Pengaturan'"></span>
                    </button>
                </div>
            </div>

            <!-- Assigned Products Table in Flash Sale -->
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-4">
                    <h4 class="text-xs font-extrabold text-brand-dark uppercase tracking-wider">
                        Daftar Produk Flash Sale (<span x-text="flashSaleProductsList.length"></span>)
                    </h4>
                    <button type="button" 
                            @click="openAssignFlashSaleModal()" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-modern font-bold text-xs text-brand-primary bg-brand-primary/10 hover:bg-brand-primary/20 transition-all cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Produk ke Flash Sale</span>
                    </button>
                </div>

                <template x-if="flashSaleProductsList.length === 0">
                    <div class="p-6 text-center bg-gray-50 rounded-modern border border-dashed border-gray-300 text-xs text-gray-500">
                        Belum ada produk yang dimasukkan ke program Flash Sale. Klik tombol di atas untuk memilih produk dari katalog.
                    </div>
                </template>

                <div x-show="flashSaleProductsList.length > 0" class="overflow-x-auto rounded-modern border border-gray-200">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-100/80 font-bold text-brand-dark border-b border-gray-200">
                            <tr>
                                <th class="p-3">Urutan</th>
                                <th class="p-3">Produk Promo</th>
                                <th class="p-3">Kategori</th>
                                <th class="p-3">Harga Normal</th>
                                <th class="p-3">Diskon Flash Sale</th>
                                <th class="p-3">Harga Flash Sale</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(p, index) in flashSaleProductsList" :key="p.id">
                                <tr class="hover:bg-gray-50/50 transition-colors" :class="index === 0 ? 'bg-amber-50/30' : ''">
                                    <!-- Urutan & Up/Down Controls -->
                                    <td class="p-3">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full flex items-center justify-center font-mono font-black text-xs shrink-0"
                                                  :class="index === 0 ? 'bg-red-600 text-white shadow-2xs' : 'bg-gray-200 text-gray-700'"
                                                  x-text="'#' + (index + 1)"></span>
                                            
                                            <div class="inline-flex items-center gap-1.5">
                                                <!-- Move Up Button -->
                                                <button type="button" 
                                                        @click="moveFlashSaleOrder(p, 'up')" 
                                                        :disabled="index === 0"
                                                        class="w-7 h-7 rounded-modern-sm border border-gray-200 flex items-center justify-center transition-all cursor-pointer"
                                                        :class="index === 0 ? 'opacity-30 cursor-not-allowed bg-gray-50 text-gray-400' : 'bg-white hover:bg-brand-primary/10 text-brand-dark hover:text-brand-primary hover:border-brand-primary/30 shadow-2xs'"
                                                        title="Geser Naik (Urutan Sebelumnya)">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                                    </svg>
                                                </button>

                                                <!-- Move Down Button -->
                                                <button type="button" 
                                                        @click="moveFlashSaleOrder(p, 'down')" 
                                                        :disabled="index === flashSaleProductsList.length - 1"
                                                        class="w-7 h-7 rounded-modern-sm border border-gray-200 flex items-center justify-center transition-all cursor-pointer"
                                                        :class="index === flashSaleProductsList.length - 1 ? 'opacity-30 cursor-not-allowed bg-gray-50 text-gray-400' : 'bg-white hover:bg-brand-primary/10 text-brand-dark hover:text-brand-primary hover:border-brand-primary/30 shadow-2xs'"
                                                        title="Geser Turun (Urutan Berikutnya)">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Produk Promo Details with Featured Badge -->
                                    <td class="p-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-9 h-7 rounded overflow-hidden bg-gray-100 shrink-0 border border-gray-200">
                                                <img :src="getImageUrl(p.image)" alt="thumb" class="w-full h-full object-cover">
                                            </div>
                                            <div>
                                                <div class="font-bold text-brand-dark flex items-center gap-1.5">
                                                    <span x-text="p.name"></span>
                                                    <span x-show="index === 0" 
                                                          class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black bg-red-600 text-white tracking-wider uppercase shadow-2xs">
                                                        ⭐ FEATURED HERO
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="p-3 text-gray-500" x-text="p.category"></td>
                                    <td class="p-3 text-gray-400 line-through" x-text="formatRupiah(p.normal_price)"></td>
                                    <td class="p-3 font-bold text-red-600" x-text="p.flash_sale_discount_type === 'percentage' ? (p.flash_sale_discount_value + '%') : formatRupiah(p.flash_sale_discount_value)"></td>
                                    <td class="p-3 font-extrabold text-red-600" x-text="formatRupiah(p.flash_sale_discount_type === 'percentage' ? (p.normal_price - (p.normal_price * p.flash_sale_discount_value / 100)) : (p.normal_price - p.flash_sale_discount_value))"></td>

                                    <!-- Delete Action -->
                                    <td class="p-3 text-right">
                                        <button type="button" 
                                                @click="removeProductFromFlashSale(p)" 
                                                :disabled="isRemovingFlashSaleId === p.id"
                                                class="inline-flex items-center gap-1 text-xs font-bold text-red-600 hover:text-red-800 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                            <svg x-show="isRemovingFlashSaleId === p.id" class="animate-spin h-3 w-3 text-red-600" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span x-text="isRemovingFlashSaleId === p.id ? 'Menghapus...' : 'Hapus dari Promo'"></span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- ======================================================= -->
    <!-- 5. MODAL TAMBAH / EDIT BADGE KARAKTERISTIK              -->
    <!-- ======================================================= -->
    <div x-show="charModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="charModalOpen = false"></div>

        <div class="min-h-full flex items-center justify-center p-3 sm:p-6">
            <div class="relative bg-white rounded-modern-xl max-w-md w-full p-6 sm:p-7 shadow-2xl border border-gray-200 overflow-hidden my-6 space-y-5">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-extrabold text-brand-dark"
                            x-text="isEditingChar ? 'Edit Karakteristik Produk' : 'Tambah Karakteristik Produk'">
                        </h3>
                        <p class="text-xs text-gray-500">Badge informasi visual yang tampil pada kartu produk.</p>
                    </div>
                    <button @click="charModalOpen = false" 
                            type="button" 
                            class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="saveChar()" class="space-y-4">
                    
                    <!-- Nama Badge -->
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Nama Badge <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               x-model="charForm.name" 
                               required
                               placeholder="Contoh: Frozen, Ready to Cook, Organik..."
                               class="w-full text-xs sm:text-sm rounded-modern border border-gray-300 p-2.5 bg-white font-semibold text-brand-dark focus:ring-2 focus:ring-brand-primary/30">
                    </div>

                    <!-- Deskripsi Singkat -->
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Deskripsi Singkat / Informasi
                        </label>
                        <textarea x-model="charForm.desc" 
                                  rows="2" 
                                  placeholder="Contoh: Dibekukan cepat standar cold-chain..."
                                  class="w-full text-xs rounded-modern border border-gray-300 p-2 bg-white leading-relaxed"></textarea>
                    </div>

                    <!-- COLOR PICKER (Native Input + Hex + Auto Background & Contrast) -->
                    <div class="p-3.5 rounded-modern bg-gray-50 border border-gray-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-brand-dark">
                                Warna Badge (Color Picker) <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[10px] text-emerald-700 font-bold">Auto Background Tint</span>
                        </div>
                        <p class="text-[11px] text-gray-500">
                            Pilih warna badge. Sistem otomatis menghitung warna latar dan kontras yang serasi.
                        </p>

                        <!-- Color Input & Hex Code -->
                        <div class="flex items-center gap-3">
                            <input type="color" 
                                   x-model="charForm.color" 
                                   class="w-10 h-10 rounded-modern border border-gray-300 p-0.5 cursor-pointer shrink-0 bg-white">
                            <input type="text" 
                                   x-model="charForm.color" 
                                   placeholder="#059669"
                                   maxlength="7"
                                   class="w-28 text-xs font-mono font-bold uppercase rounded-modern border border-gray-300 p-2 bg-white">
                            <span class="text-xs text-gray-400">HEX Code</span>
                        </div>

                        <!-- Real-time Live Badge Preview -->
                        <div class="pt-2 border-t border-gray-200/80 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-bold text-gray-500 block mb-1">Preview Badge:</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border shadow-2xs transition-all"
                                      :style="hexToBadgeStyle(charForm.color)"
                                      x-text="charForm.name || 'Nama Badge'">
                                </span>
                            </div>
                            <div class="text-[11px] text-gray-400 font-mono" x-text="charForm.color"></div>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Status Badge
                        </label>
                        <select x-model="charForm.status" 
                                class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-semibold">
                            <option value="Aktif">Aktif (Dapat Dipilih pada Produk)</option>
                            <option value="Nonaktif">Nonaktif (Disembunyikan dari Pilihan)</option>
                        </select>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-4 border-t border-gray-100 flex items-center justify-between gap-2">
                        <!-- Hapus Button (Only in Edit Mode) -->
                        <div>
                            <template x-if="isEditingChar">
                                <button type="button" 
                                        @click="openDeleteCharFromEdit()"
                                        class="px-3 py-2 rounded-modern text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                    Hapus Badge
                                </button>
                            </template>
                        </div>

                        <div class="flex items-center gap-2">
                            <button @click="charModalOpen = false" 
                                    type="button" 
                                    class="px-4 py-2 rounded-modern text-xs font-semibold text-gray-600 hover:bg-gray-100 cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="px-5 py-2 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm cursor-pointer"
                                    x-text="isEditingChar ? 'Simpan Perubahan' : 'Tambah Badge'">
                            </button>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 6. MODAL DELETE BADGE CONFIRMATION                      -->
    <!-- ======================================================= -->
    <div x-show="charDeleteModalOpen" 
         x-cloak
         class="fixed inset-0 z-[60] overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-xs" @click="charDeleteModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white rounded-modern-xl max-w-sm w-full p-6 shadow-xl border border-gray-200 space-y-4 text-center">
                
                <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-brand-dark">Hapus Karakteristik ini?</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Karakteristik <strong class="text-brand-dark" x-text="selectedChar?.name || selectedChar?.label"></strong> akan dihapus.
                    </p>

                    <!-- Warning if badge is currently used by products -->
                    <template x-if="charDeleteWarningCount > 0">
                        <div class="mt-2 p-2.5 rounded-modern bg-amber-50 border border-amber-200 text-amber-900 text-[11px] text-left leading-relaxed">
                            <span class="font-bold">⚠️ Perhatian:</span>
                            Karakteristik ini masih digunakan oleh <strong x-text="charDeleteWarningCount"></strong> produk. Jika dihapus, karakteristik akan dilepas dari produk tersebut.
                        </div>
                    </template>
                </div>

                <div class="pt-3 flex items-center justify-center gap-3">
                    <button @click="charDeleteModalOpen = false" 
                            type="button" 
                            class="px-4 py-2 rounded-modern text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button @click="confirmDeleteChar()" 
                            type="button" 
                            class="px-4 py-2 rounded-modern text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 transition-colors cursor-pointer">
                        Tetap Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 7. MODAL EDITOR PRODUK (Form + PREVIEW)                 -->
    <!-- ======================================================= -->
    <div x-show="editorModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="editorModalOpen = false"></div>

        <div class="min-h-full flex items-center justify-center p-3 sm:p-6">
            <div class="relative bg-white rounded-modern-xl max-w-5xl w-full flex flex-col shadow-2xl border border-gray-200 max-h-[90vh] my-6 overflow-hidden">
                
                <!-- 1. MODAL HEADER (Fixed Top) -->
                <div class="flex items-center justify-between px-6 sm:px-8 py-4 border-b border-gray-100 shrink-0 bg-white">
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold text-brand-dark"
                            x-text="isEditing ? 'Edit Produk: ' + form.name : 'Tambah Produk Baru'">
                        </h3>
                        <p class="text-xs text-gray-500">Kategori produk terhubung dengan Category Manager sebagai Single Source of Truth.</p>
                    </div>
                    <button @click="editorModalOpen = false" 
                            type="button" 
                            class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- 2. MODAL CONTENT (Scrollable 2-Column Grid) -->
                <form id="productEditForm" @submit.prevent="saveProduct()" class="flex-1 overflow-y-auto p-6 sm:p-8">
                    
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        
                        <!-- Left Form (7 cols on lg) -->
                        <div class="lg:col-span-7 space-y-4">
                            
                            <!-- Nama Produk -->
                            <div>
                                <label class="block text-xs font-bold text-brand-dark mb-1">
                                    Nama Produk <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="form.name" 
                                       required
                                       placeholder="Contoh: Daging Sapi Shortplate Slice Premium"
                                       class="w-full text-xs sm:text-sm rounded-modern border border-gray-300 p-2.5 bg-white focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary font-medium">
                            </div>

                            <!-- Kategori (MULTI-SELECT CHECKLIST DROPDOWN) & Status -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Multi-Category Checklist Dropdown -->
                                <div class="relative" x-data="{ openCatDropdown: false }" @click.outside="openCatDropdown = false">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-bold text-brand-dark">
                                            Kategori Produk (Pilih Minimal 1) <span class="text-rose-500">*</span>
                                        </label>
                                        <span class="text-[10px] text-emerald-600 font-semibold" 
                                              x-text="(form.category_ids || []).length + ' Terpilih'"></span>
                                    </div>

                                    <!-- Trigger Button / Summary Display -->
                                    <button type="button" 
                                            @click="openCatDropdown = !openCatDropdown"
                                            class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium text-left flex items-center justify-between shadow-2xs focus:ring-2 focus:ring-brand-primary/30 cursor-pointer">
                                        <span class="truncate text-brand-dark font-semibold" 
                                              x-text="getSelectedCategoriesSummary()"></span>
                                        <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform" 
                                             :class="openCatDropdown ? 'rotate-180' : ''" 
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <!-- Checklist Dropdown Panel -->
                                    <div x-show="openCatDropdown" 
                                         x-cloak
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="absolute z-40 mt-1 w-full bg-white rounded-modern-lg shadow-xl border border-gray-200 p-2 space-y-1 max-h-56 overflow-y-auto">
                                        <template x-for="cat in activeCategories" :key="cat.id">
                                            <button type="button" 
                                                    @click.stop="toggleCategorySelection(cat.id)"
                                                    class="w-full flex items-center justify-between p-2 rounded-modern border text-xs font-semibold transition-all cursor-pointer text-left select-none"
                                                    :class="(form.category_ids || []).includes(Number(cat.id)) 
                                                        ? 'bg-brand-soft-green text-brand-primary border-brand-primary/30 ring-1 ring-brand-primary/20 font-bold' 
                                                        : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <span class="w-4 h-4 rounded flex items-center justify-center text-[10px] font-black border shrink-0 transition-colors"
                                                          :class="(form.category_ids || []).includes(Number(cat.id)) 
                                                              ? 'bg-brand-primary text-white border-transparent' 
                                                              : 'bg-gray-100 text-transparent border-gray-300'">
                                                        ✓
                                                    </span>
                                                    <span class="truncate" x-text="cat.name"></span>
                                                </div>
                                                <span class="text-[10px] text-emerald-700 font-bold shrink-0 ml-2" 
                                                      x-show="(form.category_ids || []).includes(Number(cat.id))">
                                                    Terpilih
                                                </span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-brand-dark mb-1">
                                        Status Produk
                                    </label>
                                    <select x-model="form.status" 
                                            class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-semibold">
                                        <option value="Aktif">Aktif (Tampil di Landing Page)</option>
                                        <option value="Nonaktif">Nonaktif (Disembunyikan)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- UNLIMITED MULTI-SELECT CHARACTERISTICS FROM MASTER BADGES -->
                            <div class="p-3.5 rounded-modern bg-gray-50 border border-gray-200 space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-brand-dark">
                                        Karakteristik Produk (Pilih Semua yang Sesuai) <span class="text-rose-500">*</span>
                                    </label>
                                    <span class="text-[10px] text-emerald-700 font-bold" x-text="form.types.length + ' Karakteristik Terpilih'"></span>
                                </div>
                                <p class="text-[11px] text-gray-500">
                                    Tidak ada batasan jumlah karakteristik. Seluruh badge yang dipilih akan tampil di kartu produk Landing Page.
                                </p>

                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 pt-1">
                                    <template x-for="char in availableCharacteristics.filter(c => c.status === 'Aktif' || form.types.includes(c.id) || form.types.includes(c.name))" :key="char.id">
                                        <button type="button" 
                                                @click="toggleTypeSelection(char.name || char.id)"
                                                class="flex items-center gap-2 p-2 rounded-modern border text-xs font-bold transition-all cursor-pointer text-left"
                                                :style="form.types.includes(char.name || char.id) ? hexToBadgeStyle(char.color) : ''"
                                                :class="form.types.includes(char.name || char.id) 
                                                    ? 'ring-1 ring-black/10' 
                                                    : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-100'">
                                            <span class="w-4 h-4 rounded flex items-center justify-center text-[10px] font-black border shrink-0"
                                                  :class="form.types.includes(char.name || char.id) ? 'bg-current text-white border-transparent' : 'bg-gray-100 text-transparent border-gray-300'">
                                                ✓
                                            </span>
                                            <span class="truncate" x-text="char.name || char.label"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Harga & Berat Kemasan -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-brand-dark mb-1">
                                        Harga Satuan (Rupiah) <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-gray-400">Rp</span>
                                        <input type="number" 
                                               x-model.number="form.price" 
                                               step="500"
                                               required
                                               class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm rounded-modern border border-gray-300 bg-white font-bold text-brand-primary">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-brand-dark mb-1">
                                        Berat Kemasan Default
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input type="number" 
                                               x-model.number="form.weight_value" 
                                               @input="form.weight = form.weight_value + (form.unit === 'gram' ? 'g' : (form.unit === 'kg' ? 'kg' : ' ' + form.unit))"
                                               class="w-2/3 p-2 text-xs rounded-modern border border-gray-300 bg-white font-medium">
                                        <select x-model="form.unit" 
                                                @change="form.weight = form.weight_value + (form.unit === 'gram' ? 'g' : (form.unit === 'kg' ? 'kg' : ' ' + form.unit))"
                                                class="w-1/3 p-2 text-xs rounded-modern border border-gray-300 bg-white">
                                            <option value="gram">gram (g)</option>
                                            <option value="kg">kg</option>
                                            <option value="pcs">pcs</option>
                                            <option value="pack">pack</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Deskripsi Singkat Produk -->
                            <div>
                                <label class="block text-xs font-bold text-brand-dark mb-1">
                                    Deskripsi Produk
                                </label>
                                <textarea x-model="form.description" 
                                          rows="2" 
                                          placeholder="Penjelasan potongan daging, saran masakan, atau resep..."
                                          class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white"></textarea>
                            </div>

                            <!-- Gambar Produk via Global Media Picker -->
                            <div class="p-4 rounded-modern bg-gray-50 border border-gray-200 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-brand-dark">
                                        Foto Produk (Rasio 4:3)
                                    </label>
                                    <span class="text-[11px] font-semibold text-emerald-700">Global Media Picker</span>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="w-20 aspect-[4/3] rounded-modern overflow-hidden bg-brand-dark shrink-0 border border-gray-300 shadow-2xs">
                                        <img :src="getImageUrl(form.image)" alt="Product Thumbnail" class="w-full h-full object-cover">
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
                                    <p class="font-bold text-gray-700">Rekomendasi Gambar Produk:</p>
                                    <p>1200 × 900 px • Rasio 4:3 • JPG / WebP • Disarankan ≤ 300 KB</p>
                                </div>
                            </div>

                        </div>

                        <!-- Right: PRODUCT CARD PREVIEW (5 cols on lg) -->
                        <div class="lg:col-span-5 space-y-3 lg:sticky lg:top-0">
                            
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-extrabold text-brand-dark uppercase tracking-wider">
                                    Preview
                                </label>
                                <div class="flex items-center bg-gray-100 p-0.5 rounded text-[10px]">
                                    <button @click="previewDevice = 'desktop'" type="button" 
                                            :class="previewDevice === 'desktop' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500 hover:text-brand-dark'"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded cursor-pointer transition-all">
                                        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span>Desk</span>
                                    </button>
                                    <button @click="previewDevice = 'mobile'" type="button" 
                                            :class="previewDevice === 'mobile' ? 'bg-white font-bold text-brand-dark shadow-2xs' : 'text-gray-500 hover:text-brand-dark'"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded cursor-pointer transition-all">
                                        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <span>Mob</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Real Shared Product Card Component Inclusion -->
                            <div class="bg-gray-50 p-4 rounded-modern-xl border border-gray-200 flex flex-col items-center">
                                <div class="w-full transition-all duration-200"
                                     :class="previewDevice === 'mobile' ? 'max-w-[220px]' : 'max-w-[280px]'">
                                    
                                    <!-- SHARED COMPONENT (100% Shared Markup with Landing Page) -->
                                    @include('components.product-card-item', ['isLivePreview' => true])

                                </div>
                            </div>

                            <!-- Text explanation strictly below card -->
                            <p class="text-[11px] text-gray-400 text-center leading-relaxed">
                                Preview di atas 100% merefleksikan seluruh badge dan styling kartu produk Landing Page.
                            </p>

                        </div>

                    </div>

                </form>

                <!-- 3. MODAL FOOTER (Fixed Bottom, Right Aligned) -->
                <div class="px-6 sm:px-8 py-4 border-t border-gray-100 bg-gray-50/90 shrink-0 flex items-center justify-end gap-3">
                    <button @click="editorModalOpen = false" 
                            type="button" 
                            :disabled="isSaving"
                            class="px-4 py-2.5 rounded-modern text-xs font-semibold text-gray-600 hover:bg-gray-200/70 transition-colors cursor-pointer disabled:opacity-50">
                        Batal
                    </button>
                    <button form="productEditForm"
                            type="submit" 
                            :disabled="isSaving"
                            class="px-6 py-2.5 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-2">
                        <svg x-show="isSaving" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Produk'"></span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 8. GLOBAL MEDIA PICKER MODAL                            -->
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
                            <p class="text-xs text-gray-500">Pilih dari pustaka media atau unggah gambar produk baru.</p>
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
                            Pilih Gambar Produk
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
                            <p class="text-xs font-bold text-brand-dark">Tarik &amp; Lepaskan gambar ke sini, atau klik untuk memilih file</p>
                            <p class="text-[11px] text-gray-400">Mendukung JPG, PNG, WebP (Rekomendasi 1200 × 900 px ≤ 300 KB)</p>
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
    <!-- 9. DELETE PRODUCT CONFIRMATION MODAL                    -->
    <!-- ======================================================= -->
    <div x-show="deleteModalOpen" 
         x-cloak
         class="fixed inset-0 z-[60] overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-xs" @click="closeDeleteModal()"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white rounded-modern-xl max-w-sm w-full p-6 shadow-xl border border-gray-200 text-center space-y-4">
                
                <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>

                <div class="space-y-1">
                    <h3 class="text-base font-bold text-brand-dark">Hapus Produk ini?</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">Produk <strong class="text-brand-dark" x-text="deletingProduct?.name"></strong> akan dihapus dari katalog produk.</p>
                </div>

                <div class="pt-3 flex items-center justify-center gap-3">
                    <button @click="closeDeleteModal()" 
                            type="button" 
                            class="px-4 py-2 rounded-modern text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button @click="confirmDelete()" 
                            type="button" 
                            class="px-4 py-2 rounded-modern text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 transition-colors cursor-pointer">
                        Hapus Produk
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- FLASH SALE ASSIGN PRODUCT MODAL                         -->
    <!-- ======================================================= -->
    <div x-show="flashSaleModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-xs" @click="flashSaleModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white rounded-modern-xl max-w-md w-full p-6 shadow-xl border border-gray-200 space-y-4">
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-base">⚡</span>
                        <h3 class="text-sm font-extrabold text-brand-dark">Tambah Produk ke Flash Sale</h3>
                    </div>
                    <button @click="flashSaleModalOpen = false" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Select Product (Browser-safe Custom Interactive Dropdown) -->
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">
                            Pilih Produk Katalog <span class="text-rose-500">*</span>
                        </label>
                        
                        <div class="relative" x-data="{ openProdDropdown: false }" @click.outside="openProdDropdown = false">
                            <!-- Trigger Button -->
                            <button type="button" 
                                    @click="openProdDropdown = !openProdDropdown"
                                    class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium text-left flex items-center justify-between shadow-2xs focus:ring-2 focus:ring-brand-primary/30 cursor-pointer">
                                <span class="truncate font-bold text-brand-dark" 
                                      x-text="products.find(p => p.id == flashSaleForm.product_id)?.name 
                                          ? (products.find(p => p.id == flashSaleForm.product_id).name + ' (' + formatRupiah(products.find(p => p.id == flashSaleForm.product_id).normal_price || products.find(p => p.id == flashSaleForm.product_id).price) + ')') 
                                          : '-- Pilih Produk Katalog --'"></span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform" 
                                     :class="openProdDropdown ? 'rotate-180' : ''" 
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Dropdown List -->
                            <div x-show="openProdDropdown" 
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute z-50 mt-1 w-full bg-white rounded-modern-lg shadow-xl border border-gray-200 p-2 space-y-1 max-h-56 overflow-y-auto">
                                <template x-for="p in availableFlashSaleProducts" :key="p.id">
                                    <button type="button" 
                                            @click="flashSaleForm.product_id = p.id; openProdDropdown = false;"
                                            class="w-full flex items-center justify-between p-2 rounded-modern border text-xs font-semibold transition-all cursor-pointer text-left select-none"
                                            :class="flashSaleForm.product_id == p.id 
                                                ? 'bg-red-50 text-red-700 border-red-300 ring-1 ring-red-200 font-bold' 
                                                : 'bg-white text-gray-700 border-gray-100 hover:bg-gray-50'">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-8 h-6 rounded overflow-hidden bg-brand-dark shrink-0 border border-gray-200">
                                                <img :src="getImageUrl(p.image)" alt="prod" class="w-full h-full object-cover">
                                            </div>
                                            <div class="truncate">
                                                <span class="truncate block font-bold text-brand-dark" x-text="p.name"></span>
                                                <span class="text-[10px] text-gray-500 font-normal" x-text="(p.category || 'Kategori') + ' • ' + formatRupiah(p.normal_price || p.price)"></span>
                                            </div>
                                        </div>
                                        <span class="text-[10px] text-red-700 font-bold shrink-0 ml-2" 
                                              x-show="flashSaleForm.product_id == p.id">
                                            ✓ Terpilih
                                        </span>
                                    </button>
                                </template>
                                <template x-if="availableFlashSaleProducts.length === 0">
                                    <div class="p-3 text-center text-xs text-gray-500">
                                        Semua produk aktif sudah terdaftar di Flash Sale.
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Discount Type & Value -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Tipe Diskon</label>
                            <select x-model="flashSaleForm.discount_type" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-semibold text-brand-dark">
                                <option value="percentage">Persentase (%)</option>
                                <option value="fixed">Nominal (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Nilai Diskon</label>
                            <input type="number" x-model.number="flashSaleForm.discount_value" min="0" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-bold text-red-600 focus:ring-2 focus:ring-red-500/30 focus:border-red-500">
                        </div>
                    </div>

                    <!-- Promo Price Live Preview -->
                    <div class="p-3.5 bg-red-50/80 rounded-modern border border-red-200/80 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase tracking-wider font-extrabold text-red-800 block">
                                Preview Harga Flash Sale
                            </span>
                            <span class="text-sm sm:text-base font-extrabold text-red-600" x-text="formatRupiah(getCalculatedFlashPrice())"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-gray-500 block">Potongan Diskon</span>
                            <span class="text-xs font-bold text-emerald-700" x-text="'- ' + formatRupiah(getCalculatedSavings())"></span>
                        </div>
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Urutan Promo (Sort Order)</label>
                        <input type="number" x-model.number="flashSaleForm.sort_order" min="1" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-mono">
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button @click="flashSaleModalOpen = false" 
                            type="button" 
                            class="px-4 py-2.5 rounded-modern text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button @click="assignProductToFlashSale()" 
                            type="button" 
                            :disabled="isAssigningFlashSale || !flashSaleForm.product_id"
                            class="px-5 py-2.5 rounded-modern text-xs font-bold text-white bg-red-600 hover:bg-red-700 transition-colors cursor-pointer shadow-sm disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-2">
                        <svg x-show="isAssigningFlashSale" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isAssigningFlashSale ? 'Menambahkan...' : 'Tambahkan ke Promo'"></span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 9B. MODAL KONFIRMASI PERUBAHAN POSISI KATALOG           -->
    <!-- ======================================================= -->
    <div x-show="confirmReorderModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" 
         aria-modal="true">
        
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="confirmReorderModalOpen = false"></div>

        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white rounded-modern-xl max-w-md w-full p-6 shadow-2xl border border-gray-200 space-y-4">
                
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-brand-primary/10 text-brand-primary flex items-center justify-center text-lg font-bold shrink-0">
                        ⇅
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-brand-dark">
                            Konfirmasi Perubahan Posisi
                        </h3>
                        <p class="text-xs text-gray-500">
                            Urutan produk katalog akan diubah dan disimpan. Perubahan ini akan langsung diterapkan pada Landing Page.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                    <button type="button" 
                            @click="confirmReorderModalOpen = false" 
                            :disabled="isSavingCatalogOrder"
                            class="px-4 py-2 rounded-modern text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer disabled:opacity-50">
                        Batal
                    </button>
                    <button type="button" 
                            @click="saveCatalogReorder()" 
                            :disabled="isSavingCatalogOrder"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark transition-colors cursor-pointer shadow-2xs disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="isSavingCatalogOrder" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isSavingCatalogOrder ? 'Menyimpan...' : 'Simpan Posisi'"></span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 10. TOAST NOTIFICATION                                  -->
    <!-- ======================================================= -->
    <div x-show="toastVisible" 
         x-cloak
         x-transition
         class="fixed bottom-6 right-6 z-50 bg-brand-dark text-white px-4 py-3 rounded-modern-lg shadow-xl border border-white/10 flex items-center gap-2.5 text-xs font-semibold">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        <span x-text="toastMessage"></span>
    </div>

</div>
@endsection
