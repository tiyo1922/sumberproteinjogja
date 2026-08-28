@extends('layouts.admin', [
    'title' => 'Katalog Produk',
    'pageTitle' => 'Katalog Produk'
])

@section('content')
<div class="space-y-6"
     x-data="{
         products: {{ json_encode($products) }},
         categories: {{ json_encode($categories) }},
         contactSettings: {{ json_encode($contactSettings) }},
         mediaLibrary: {{ json_encode($mediaLibrary) }},
         editorModalOpen: false,
         mediaPickerOpen: false,
         deleteModalOpen: false,
         isEditing: false,
         toastMessage: '',
         toastVisible: false,
         searchQuery: '',
         selectedCategoryFilter: 'all',
         selectedTypeFilter: 'all',
         previewDevice: 'desktop', // 'desktop' | 'mobile'
         mediaTab: 'library', // 'library' | 'upload'
         selectedMedia: null,
         uploadedFile: null,
         uploadedPreviewUrl: null,
         csrfToken: '{{ csrf_token() }}',
         flashSale: {{ json_encode($flashSaleSetting ?? ['enabled' => false, 'end_at' => null]) }},
         flashSaleModalOpen: false,
         flashSaleForm: {
             product_id: '',
             discount_type: 'percentage',
             discount_value: 20,
             sort_order: 1,
         },
         
         // 1. SECTION HEADER KATALOG PRODUK STATE
         productSection: {
             label: 'Katalog Lengkap',
             title: 'Produk Pilihan',
             subtitle: 'Pilih bahan masak sesuai kebutuhanmu. Tersedia skala retail rumah tangga maupun pembelian curah.'
         },
         
         // 2. MASTER BADGE KARAKTERISTIK PRODUK STATE
         availableCharacteristics: [
             { id: 'Frozen', label: 'Frozen', name: 'Frozen', desc: 'Dibekukan cold-chain standar', color: '#059669', status: 'Aktif' },
             { id: 'Ready to Cook', label: 'Ready to Cook', name: 'Ready to Cook', desc: 'Sudah dipotong / marinasi praktis', color: '#d97706', status: 'Aktif' },
             { id: 'Plain', label: 'Plain', name: 'Plain', desc: 'Tanpa bumbu / murni alami', color: '#475569', status: 'Aktif' },
             { id: 'Berbumbu', label: 'Berbumbu', name: 'Berbumbu', desc: 'Sudah diungkep / marinasi rempah', color: '#ea580c', status: 'Aktif' },
             { id: 'Curah', label: 'Curah', name: 'Curah', desc: 'Tersedia kemasan grosir / horeka', color: '#7c3aed', status: 'Aktif' },
             { id: 'Fresh', label: 'Fresh', name: 'Fresh', desc: 'Potong harian subuh / segar dingin', color: '#0284c7', status: 'Aktif' },
         ],
         charModalOpen: false,
         charDeleteModalOpen: false,
         isEditingChar: false,
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
         
         // Color Badge Algorithm (Font Color -> Auto Background, Border & Contrast)
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
             if (!this.charForm.name) this.charForm.name = this.charForm.label || this.charForm.id;
             if (!this.charForm.color) this.charForm.color = '#059669';
             if (!this.charForm.status) this.charForm.status = 'Aktif';
             this.charModalOpen = true;
         },
         
         saveChar() {
             if (!this.charForm.name.trim()) {
                 alert('Nama karakteristik wajib diisi.');
                 return;
             }
             this.charForm.label = this.charForm.name.trim();
             this.charForm.name = this.charForm.name.trim();
             
             if (this.isEditingChar) {
                 var self = this;
                 var idx = this.availableCharacteristics.findIndex(function(c) { return c.id === self.charForm.id; });
                 if (idx !== -1) {
                     var oldKey = this.availableCharacteristics[idx].id;
                     var newKey = this.charForm.name;
                     this.availableCharacteristics[idx] = JSON.parse(JSON.stringify(this.charForm));
                     
                     // Cascade name change to products
                     if (oldKey !== newKey) {
                         this.products.forEach(function(p) {
                             if (p.types && p.types.includes(oldKey)) {
                                 var tIdx = p.types.indexOf(oldKey);
                                 p.types[tIdx] = newKey;
                             }
                         });
                     }
                 }
                 this.showToast('Karakteristik ' + this.charForm.name + ' berhasil diperbarui!');
             } else {
                 var self = this;
                 var exists = this.availableCharacteristics.some(function(c) {
                     return (c.name || c.id).toLowerCase() === self.charForm.name.toLowerCase();
                 });
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
         
         // 3. PRODUCT FORM STATE
         form: {
             id: null,
             name: '',
             category_id: 1,
             category: 'Daging Sapi',
             types: ['Frozen', 'Plain'],
             weight: '500g',
             weight_value: 500,
             unit: 'gram',
             price: 50000,
             status: 'Aktif',
             image: 'images/prod-beef-slice.jpg',
             description: '',
             whatsapp_destination: 'admin', // 'admin' | 'order'
         },
         
         selectedProduct: null,
         
         showToast(msg) {
             this.toastMessage = msg;
             this.toastVisible = true;
             setTimeout(() => { this.toastVisible = false; }, 3000);
         },
         
         get activeCategories() {
             return this.categories.filter(c => c.status === 'active_landing' || c.status === 'active_catalog' || c.status === 'Aktif');
         },
         
         get filteredProducts() {
             return this.products.filter(p => {
                 const matchCat = this.selectedCategoryFilter === 'all' || p.category_id == this.selectedCategoryFilter || p.category === this.selectedCategoryFilter;
                 const matchType = this.selectedTypeFilter === 'all' || (p.types && p.types.includes(this.selectedTypeFilter));
                 const matchSearch = !this.searchQuery.trim() || 
                     p.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                     (p.category && p.category.toLowerCase().includes(this.searchQuery.toLowerCase()));
                 return matchCat && matchType && matchSearch;
             });
         },
         
         openCreateModal() {
             this.isEditing = false;
             const defaultCat = this.activeCategories[0] || { id: 1, name: 'Daging Sapi' };
             const defaultTypes = this.availableCharacteristics.filter(c => c.status === 'Aktif').slice(0, 2).map(c => c.name || c.id);
             this.form = {
                 id: Date.now(),
                 name: '',
                 category_id: defaultCat.id,
                 category: defaultCat.name,
                 types: defaultTypes.length > 0 ? defaultTypes : ['Frozen', 'Plain'],
                 weight: '500g',
                 weight_value: 500,
                 unit: 'gram',
                 price: 45000,
                 status: 'Aktif',
                 image: 'images/prod-beef-slice.jpg',
                 description: '',
                 whatsapp_destination: 'admin',
             };
             this.editorModalOpen = true;
         },
         
         openEditModal(p) {
             this.isEditing = true;
             this.form = JSON.parse(JSON.stringify(p));
             if (!this.form.category_id) {
                 const matched = this.categories.find(c => c.name === this.form.category);
                 this.form.category_id = matched ? matched.id : 1;
             }
             if (!this.form.types) this.form.types = ['Frozen'];
             if (!this.form.whatsapp_destination) this.form.whatsapp_destination = 'admin';
             
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
             this.uploadedPreviewUrl = URL.createObjectURL(file);
         },
          
         async saveProduct() {
            if (!this.form.name.trim()) {
                alert('Nama produk wajib diisi.');
                return;
            }
            if (!this.form.types || this.form.types.length === 0) {
                alert('Pilih minimal satu karakteristik produk.');
                return;
            }

            const payload = {
                name: this.form.name,
                slug: this.form.slug || '',
                category_id: parseInt(this.form.category_id || 1),
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
                whatsapp_destination: this.form.whatsapp_destination || 'admin',
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
                    alert(result.message || 'Gagal menyimpan produk.');
                    return;
                }

                const matchedCat = this.categories.find(c => c.id == payload.category_id);
                const catName = matchedCat ? matchedCat.name : (result.product?.category?.name || 'Daging Sapi');

                if (this.isEditing) {
                    const idx = this.products.findIndex(p => p.id === this.form.id);
                    if (idx !== -1) {
                        this.products[idx] = {
                            ...this.products[idx],
                            ...this.form,
                            ...payload,
                            category: catName,
                            price: payload.normal_price,
                            normal_price: payload.normal_price,
                            status: payload.is_active ? 'Aktif' : 'Nonaktif',
                        };
                    }
                    this.showToast(result.message || `Produk ${this.form.name} berhasil diperbarui!`);
                } else {
                    const newProd = {
                        ...this.form,
                        ...payload,
                        id: result.product.id,
                        category: catName,
                        price: payload.normal_price,
                        normal_price: payload.normal_price,
                        status: payload.is_active ? 'Aktif' : 'Nonaktif',
                    };
                    this.products.unshift(newProd);
                    this.showToast(result.message || 'Produk baru berhasil ditambahkan ke katalog!');
                }
                this.editorModalOpen = false;
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat menyimpan produk.');
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
            this.selectedProduct = p;
            this.deleteModalOpen = true;
        },
         
        async confirmDelete() {
            if (!this.selectedProduct) return;

            try {
                const prodId = this.selectedProduct.id;
                const prodName = this.selectedProduct.name;

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
                this.deleteModalOpen = false;
                this.showToast(result.message || `Produk ${prodName} telah dihapus.`);
                this.selectedProduct = null;
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat menghapus produk.');
            }
        },
          
        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        },
        getWaNumber(dest) {
            return dest === 'order' ? this.contactSettings.order_whatsapp : this.contactSettings.admin_whatsapp;
        },

        get flashSaleProductsList() {
            return this.products.filter(p => p.is_flash_sale);
        },

        get activeFlashSaleCount() {
            return this.products.filter(p => p.is_flash_sale && (p.status === 'Aktif' || p.is_active)).length;
        },

        async toggleFlashSale(targetState) {
            try {
                const response = await fetch('/admin/flash-sale/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        enabled: targetState,
                        end_at: this.flashSale.end_at,
                    }),
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    alert(result.message || 'Gagal mengubah status Flash Sale.');
                    return;
                }

                this.flashSale = result.flash_sale;
                this.showToast(result.message);
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat mengubah status Flash Sale.');
            }
        },

        async saveFlashSaleSettings() {
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
                    alert(result.message || 'Gagal menyimpan pengaturan Flash Sale.');
                    return;
                }

                this.flashSale = result.flash_sale;
                this.showToast(result.message || 'Pengaturan Flash Sale berhasil disimpan!');
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat menyimpan pengaturan Flash Sale.');
            }
        },

        openAssignFlashSaleModal() {
            const unassigned = this.products.filter(p => !p.is_flash_sale);
            if (unassigned.length === 0) {
                alert('Semua produk sudah terdaftar dalam Flash Sale.');
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
            if (!this.flashSaleForm.product_id) {
                alert('Pilih produk yang akan dimasukkan ke Flash Sale.');
                return;
            }

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
                    alert(result.message || 'Gagal menambahkan produk ke Flash Sale.');
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
                alert('Terjadi kesalahan saat menambahkan produk ke Flash Sale.');
            }
        },

        async removeProductFromFlashSale(p) {
            if (!confirm(`Hapus "${p.name}" dari Flash Sale? (Harga regular produk tidak akan berubah)`)) {
                return;
            }

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
                    alert(result.message || 'Gagal menghapus produk dari Flash Sale.');
                    return;
                }

                p.is_flash_sale = false;
                this.showToast(result.message || 'Produk berhasil dihapus dari Flash Sale.');
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat menghapus produk dari Flash Sale.');
            }
        },
          
         getImageUrl(path) {
             if (!path) return '/images/prod-beef-slice.jpg';
             if (path.startsWith('blob:') || path.startsWith('http')) return path;
             return path.startsWith('/') ? path : '/' + path;
         }
     }">
    
    <!-- ======================================================= -->
    <!-- 1. HEADER CARD                                          -->
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
            <div class="flex items-center gap-3 shrink-0">
                <button @click="openCreateModal()" 
                        type="button"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern font-bold text-xs sm:text-sm text-white bg-brand-primary hover:bg-brand-primary-dark shadow-sm hover:shadow transition-all cursor-pointer">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Produk</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 2. PENGATURAN SECTION KATEGORI & MASTER BADGE           -->
    <!-- ======================================================= -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-7 shadow-2xs space-y-6">
        
        <!-- PENGATURAN SECTION KATALOG PRODUK -->
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-4 border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <span class="text-base shrink-0">⚙️</span>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-xs sm:text-sm font-extrabold text-brand-dark uppercase tracking-wider truncate sm:whitespace-normal">
                            Pengaturan Section Katalog Produk
                        </h3>
                        <p class="text-[11px] text-gray-500 leading-relaxed">
                            Kelola label badge, judul utama (headline), dan deskripsi pengantar pada section katalog produk (<code>&lt;section id="produk"&gt;</code>) Landing Page.
                        </p>
                    </div>
                </div>

                <button @click="showToast('Header Section Katalog Produk berhasil diperbarui!')" 
                        type="button" 
                        class="px-4 py-2 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark shadow-2xs transition-all cursor-pointer shrink-0 whitespace-nowrap">
                    Simpan Header
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <!-- Label Badge -->
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-brand-dark mb-1">
                        Label Badge Section
                    </label>
                    <input type="text" 
                           x-model="productSection.label" 
                           placeholder="Contoh: Katalog Lengkap"
                           class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-semibold text-brand-primary focus:ring-2 focus:ring-brand-primary/30">
                </div>

                <!-- Judul Utama -->
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-brand-dark mb-1">
                        Judul Utama / Heading
                    </label>
                    <input type="text" 
                           x-model="productSection.title" 
                           placeholder="Contoh: Produk Pilihan"
                           class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-extrabold text-brand-dark focus:ring-2 focus:ring-brand-primary/30">
                </div>

                <!-- Deskripsi Pengantar -->
                <div class="md:col-span-5">
                    <label class="block text-xs font-bold text-brand-dark mb-1">
                        Deskripsi Pengantar
                    </label>
                    <textarea x-model="productSection.subtitle" 
                              rows="2" 
                              placeholder="Pilih bahan masak sesuai kebutuhanmu. Tersedia skala retail rumah tangga maupun pembelian curah."
                              class="w-full text-xs rounded-modern border border-gray-300 p-2 bg-white leading-relaxed focus:ring-2 focus:ring-brand-primary/30"></textarea>
                </div>
            </div>

            <!-- Small Header Section Realtime Preview -->
            <div class="pt-1">
                <div class="bg-brand-cream/60 rounded-modern-xl border border-dashed border-gray-300 p-4 sm:p-5 text-center max-w-xl mx-auto shadow-2xs">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-brand-soft-green text-brand-primary mb-2 shadow-2xs transition-all"
                          x-text="productSection.label || 'Katalog Lengkap'">
                    </span>
                    <h4 class="text-lg sm:text-xl font-extrabold text-brand-dark tracking-tight mb-1.5 transition-all"
                        x-text="productSection.title || 'Produk Pilihan'">
                    </h4>
                    <p class="text-xs text-gray-600 font-normal leading-relaxed max-w-md mx-auto transition-all"
                       x-text="productSection.subtitle || 'Pilih bahan masak sesuai kebutuhanmu. Tersedia skala retail rumah tangga maupun pembelian curah.'">
                    </p>
                </div>
            </div>
        </div>

        <!-- MASTER BADGE KARAKTERISTIK PRODUK -->
        <div class="pt-4 border-t border-gray-100 space-y-3">
            <div>
                <h3 class="text-xs sm:text-sm font-extrabold text-brand-dark uppercase tracking-wider flex items-center gap-1.5">
                    <span>🏷️</span>
                    <span>Karakteristik Produk</span>
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
    <!-- FLASH SALE PROMOTION CAMPAIGN MANAGER                   -->
    <!-- ======================================================= -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-6 sm:p-7 shadow-2xs space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100 pb-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <span class="text-xl">⚡</span>
                    <h3 class="text-sm sm:text-base font-extrabold text-brand-dark uppercase tracking-wider">
                        Program Flash Sale &amp; Countdown
                    </h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold"
                          :class="flashSale.enabled ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'"
                          x-text="flashSale.enabled ? 'STATUS: AKTIF' : 'STATUS: NONAKTIF'"></span>
                </div>
                <p class="text-xs text-gray-500 leading-relaxed max-w-2xl">
                    Tampilkan section promo kilat dengan timer hitung mundur di Landing Page. Flash Sale menggunakan produk katalog dengan diskon promo independen.
                </p>
            </div>

            <!-- Global ON/OFF Toggle Button -->
            <div class="flex items-center gap-3 shrink-0">
                <button type="button" 
                        @click="toggleFlashSale(!flashSale.enabled)"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern font-extrabold text-xs text-white transition-all cursor-pointer shadow-sm"
                        :class="flashSale.enabled ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700'">
                    <span x-text="flashSale.enabled ? 'Matikan Flash Sale' : 'Aktifkan Flash Sale'"></span>
                </button>
            </div>
        </div>

        <!-- Settings Row: Title, Subtitle, End At -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 bg-gray-50/70 p-4 rounded-modern-lg border border-gray-200/60">
            <div class="md:col-span-4">
                <label class="block text-xs font-bold text-brand-dark mb-1">Judul Flash Sale</label>
                <input type="text" x-model="flashSale.title" placeholder="Flash Sale Terbatas!" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-bold text-brand-dark">
            </div>
            <div class="md:col-span-5">
                <label class="block text-xs font-bold text-brand-dark mb-1">Waktu Berakhir (Countdown End)</label>
                <input type="datetime-local" x-model="flashSale.end_at" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-mono font-bold text-red-600">
            </div>
            <div class="md:col-span-3 flex items-end">
                <button type="button" @click="saveFlashSaleSettings()" class="w-full px-4 py-2.5 rounded-modern font-bold text-xs text-white bg-brand-primary hover:bg-brand-primary-dark transition-all cursor-pointer">
                    Simpan Pengaturan
                </button>
            </div>
        </div>

        <!-- Assigned Products Table in Flash Sale -->
        <div class="space-y-3">
            <div class="flex items-center justify-between gap-4">
                <h4 class="text-xs font-extrabold text-brand-dark uppercase tracking-wider">
                    Daftar Produk Flash Sale (<span x-text="flashSaleProductsList.length"></span>)
                </h4>
                <button type="button" @click="openAssignFlashSaleModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-modern font-bold text-xs text-brand-primary bg-brand-primary/10 hover:bg-brand-primary/20 transition-all cursor-pointer">
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
                            <th class="p-3">Produk</th>
                            <th class="p-3">Kategori</th>
                            <th class="p-3">Harga Normal</th>
                            <th class="p-3">Diskon Flash Sale</th>
                            <th class="p-3">Harga Flash Sale</th>
                            <th class="p-3">Urutan</th>
                            <th class="p-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="p in flashSaleProductsList" :key="p.id">
                            <tr class="hover:bg-gray-50/50">
                                <td class="p-3 font-bold text-brand-dark" x-text="p.name"></td>
                                <td class="p-3 text-gray-500" x-text="p.category"></td>
                                <td class="p-3 text-gray-400 line-through" x-text="formatRupiah(p.normal_price)"></td>
                                <td class="p-3 font-bold text-red-600" x-text="p.flash_sale_discount_type === 'percentage' ? (p.flash_sale_discount_value + '%') : formatRupiah(p.flash_sale_discount_value)"></td>
                                <td class="p-3 font-extrabold text-red-600" x-text="formatRupiah(p.flash_sale_discount_type === 'percentage' ? (p.normal_price - (p.normal_price * p.flash_sale_discount_value / 100)) : (p.normal_price - p.flash_sale_discount_value))"></td>
                                <td class="p-3 font-mono" x-text="p.flash_sale_sort_order"></td>
                                <td class="p-3 text-right">
                                    <button type="button" @click="removeProductFromFlashSale(p)" class="text-xs font-bold text-red-600 hover:text-red-800 cursor-pointer">
                                        Hapus dari Promo
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 3. FILTERS & SEARCH TOOLBAR                             -->
    <!-- ======================================================= -->
    <div class="bg-white rounded-modern-xl border border-gray-200/80 p-4 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
            
            <!-- Search Box -->
            <div class="relative w-full sm:w-64">
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

            <!-- Category Filter (From Active Categories) -->
            <select x-model="selectedCategoryFilter" 
                    class="w-full sm:w-44 py-2 px-3 rounded-modern text-xs border border-gray-300 bg-white font-medium text-brand-dark focus:ring-2 focus:ring-brand-primary/30">
                <option value="all">Semua Kategori</option>
                <template x-for="cat in activeCategories" :key="cat.id">
                    <option :value="cat.id" x-text="cat.name"></option>
                </template>
            </select>

            <!-- Characteristic Filter (From Master Badges) -->
            <select x-model="selectedTypeFilter" 
                    class="w-full sm:w-44 py-2 px-3 rounded-modern text-xs border border-gray-300 bg-white font-medium text-brand-dark focus:ring-2 focus:ring-brand-primary/30">
                <option value="all">Semua Karakteristik</option>
                <template x-for="char in availableCharacteristics" :key="char.id">
                    <option :value="char.name || char.id" x-text="(char.name || char.label) + (char.status !== 'Aktif' ? ' (Nonaktif)' : '')"></option>
                </template>
            </select>
        </div>

        <div class="text-xs text-gray-500 font-medium self-end sm:self-center">
            Menampilkan: <span class="font-bold text-brand-dark" x-text="filteredProducts.length"></span> dari <span x-text="products.length"></span> Produk
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 4. PRODUCTS GRID                                        -->
    <!-- ======================================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <template x-for="(prod, idx) in filteredProducts" :key="prod.id">
            <div class="bg-white rounded-modern-xl border border-gray-200/80 overflow-hidden shadow-2xs hover:shadow-card transition-all flex flex-col justify-between"
                 :class="{'opacity-70 bg-gray-50/80': prod.status === 'Nonaktif'}">
                
                <div>
                    <!-- 4:3 Aspect Ratio Thumbnail Container -->
                    <div class="relative aspect-[4/3] w-full bg-brand-dark overflow-hidden">
                        <img :src="getImageUrl(prod.image)" :alt="prod.name" class="w-full h-full object-cover">
                        
                        <!-- Top Left: Characteristic Badges (Multi-Badge Display from Master) -->
                        <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1 max-w-[70%]">
                            <template x-for="t in (prod.types || [])" :key="t">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold border shadow-2xs transition-all"
                                      :style="getBadgeStyle(t)"
                                      x-text="t">
                                </span>
                            </template>
                        </div>

                        <!-- Top Right: Weight Pill -->
                        <div class="absolute top-2.5 right-2.5">
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-black/60 text-white backdrop-blur-xs shadow-2xs"
                                  x-text="prod.weight">
                            </span>
                        </div>

                        <!-- Bottom Category Tag -->
                        <div class="absolute bottom-2 left-2.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-white/90 text-brand-dark backdrop-blur-xs shadow-2xs"
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
                                <span class="text-xs sm:text-sm font-black text-brand-primary" x-text="formatRupiah(prod.price)"></span>
                            </div>
                            <div class="text-right">
                                <span class="text-[9px] text-gray-400 block leading-tight">WA Dest:</span>
                                <span class="text-[10px] font-mono font-bold text-gray-700 uppercase" x-text="prod.whatsapp_destination || 'admin'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="p-3 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between gap-1.5">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border inline-flex items-center gap-1.5"
                          :class="prod.status === 'Aktif' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-gray-100 text-gray-600 border-gray-300'">
                        <span class="w-1.5 h-1.5 rounded-full shrink-0"
                              :class="prod.status === 'Aktif' ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400'"></span>
                        <span x-text="prod.status"></span>
                    </span>

                    <div class="flex items-center gap-1">
                        <button @click="openEditModal(prod)" 
                                type="button"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-modern text-xs font-bold text-brand-primary bg-brand-soft-green hover:bg-emerald-100 transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Edit</span>
                        </button>
                        <button @click="toggleStatus(prod)" 
                                type="button"
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-modern text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer"
                                :title="prod.status === 'Aktif' ? 'Nonaktifkan Produk' : 'Aktifkan Produk'">
                            <span class="w-2 h-2 rounded-full shrink-0"
                                  :class="prod.status === 'Aktif' ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                            <span class="text-[10px]" x-text="prod.status === 'Aktif' ? 'On' : 'Off'"></span>
                        </button>
                        <button @click="openDelete(prod)" 
                                type="button"
                                class="p-1.5 rounded-modern text-rose-500 hover:bg-rose-50 hover:text-rose-600 transition-colors cursor-pointer inline-flex items-center justify-center" 
                                title="Hapus Produk">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>

            </div>
        </template>
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
    <!-- 7. MODAL EDITOR PRODUK (Form + REAL LIVE PREVIEW)       -->
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

                <form @submit.prevent="saveProduct()" class="space-y-6">
                    
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

                            <!-- Kategori (SINGLE SOURCE OF TRUTH) & Status -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-bold text-brand-dark">
                                            Kategori Produk <span class="text-rose-500">*</span>
                                        </label>
                                        <span class="text-[10px] text-emerald-600 font-semibold">Single Source of Truth</span>
                                    </div>
                                    <select x-model.number="form.category_id" 
                                            @change="const c = categories.find(cat => cat.id == form.category_id); if (c) form.category = c.name;"
                                            class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium">
                                        <template x-for="cat in activeCategories" :key="cat.id">
                                            <option :value="cat.id" x-text="cat.name"></option>
                                        </template>
                                    </select>
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

                            <!-- CENTRALIZED WHATSAPP DESTINATION -->
                            <div class="p-3.5 rounded-modern bg-gray-50 border border-gray-200 space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-brand-dark">
                                        WhatsApp Destination (Tujuan Kontak)
                                    </label>
                                    <span class="text-[10px] text-emerald-700 font-semibold">Terkoneksi Contact Settings</span>
                                </div>
                                <p class="text-[11px] text-gray-500">
                                    Admin tidak perlu memasukkan nomor manual. Sistem otomatis mengambil nomor dari pengaturan kontak pusat.
                                </p>
                                <select x-model="form.whatsapp_destination" 
                                        class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-semibold">
                                    <option value="admin">Chat Admin (Default: +62 812-3456-7890)</option>
                                    <option value="order">Nomor Pemesanan (+62 812-3456-7891)</option>
                                </select>
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

                        <!-- Right: REAL LANDING PAGE PRODUCT CARD PREVIEW (5 cols on lg) -->
                        <div class="lg:col-span-5 space-y-3 sticky top-4">
                            
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-extrabold text-brand-dark uppercase tracking-wider">
                                    Real Landing Page Preview
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
                            <div class="bg-gray-50 p-4 rounded-modern-xl border border-gray-200 flex justify-center">
                                <div class="w-full transition-all duration-200"
                                     :class="previewDevice === 'mobile' ? 'max-w-[220px]' : 'max-w-[280px]'">
                                    
                                    <!-- SHARED COMPONENT (100% Shared Markup with Landing Page) -->
                                    @include('components.product-card-item', ['isLivePreview' => true])

                                </div>
                            </div>

                            <p class="text-[11px] text-gray-400 text-center">
                                Preview di atas 100% merefleksikan seluruh badge dan styling kartu produk Landing Page.
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
                            Simpan Produk
                        </button>
                    </div>

                </form>

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
                <div x-show="mediaTab === 'library'" class="space-y-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 max-h-72 overflow-y-auto p-1">
                        <template x-for="media in mediaLibrary" :key="media.id">
                            <div @click="selectMedia(media)"
                                 class="group relative aspect-[4/3] rounded-modern overflow-hidden border-2 transition-all cursor-pointer bg-brand-dark"
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
                            <p class="text-xs font-bold text-brand-dark">Tarik & Lepaskan gambar ke sini, atau klik untuk memilih file</p>
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
                    <h3 class="text-base font-bold text-brand-dark">Hapus Produk ini?</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">Produk <strong class="text-brand-dark" x-text="selectedProduct?.name"></strong> akan dihapus dari katalog produk.</p>
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

                <div class="space-y-3">
                    <!-- Select Product -->
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Pilih Produk Katalog</label>
                        <select x-model="flashSaleForm.product_id" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium text-brand-dark">
                            <template x-for="p in products.filter(item => !item.is_flash_sale)" :key="p.id">
                                <option :value="p.id" x-text="p.name + ' (Rp ' + Number(p.normal_price).toLocaleString('id-ID') + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Discount Type -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Tipe Diskon</label>
                            <select x-model="flashSaleForm.discount_type" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-medium text-brand-dark">
                                <option value="percentage">Persentase (%)</option>
                                <option value="fixed">Nominal (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-brand-dark mb-1">Nilai Diskon</label>
                            <input type="number" x-model="flashSaleForm.discount_value" min="0" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-bold text-red-600">
                        </div>
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <label class="block text-xs font-bold text-brand-dark mb-1">Urutan Flash Sale (Sort Order)</label>
                        <input type="number" x-model="flashSaleForm.sort_order" min="1" class="w-full text-xs rounded-modern border border-gray-300 p-2.5 bg-white font-mono">
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button @click="flashSaleModalOpen = false" type="button" class="px-4 py-2 rounded-modern text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button @click="assignProductToFlashSale()" type="button" class="px-4 py-2 rounded-modern text-xs font-bold text-white bg-red-600 hover:bg-red-700 transition-colors cursor-pointer">
                        Tambahkan ke Promo
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
