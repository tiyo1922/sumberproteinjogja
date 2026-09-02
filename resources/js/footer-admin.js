/**
 * Footer CMS & Sub-sections Manager (Alpine.js Component)
 * Manages:
 * 1. Ulasan Pelanggan (Manual & Google Reviews CRUD, Switch Mode, Google Config)
 * 2. Kunjungi Outlet (Location, Structured Address, Maps Embed, WhatsApp)
 * 3. Footer Actual (Brand Identity, Social Media Links, Navigation, Copyright)
 * 4. Real Landing Page Preview (Desktop 14", Tablet, Mobile)
 */

export function createFooterManager(config = {}) {
    const contacts = config.contacts || config.footer?.contacts || [];
    const footer = config.footer || {
        reviews: {
            status: 'Live DB Data',
            source_name: 'Manual Database',
            last_updated: 'Belum ada sync Google',
            place_name: 'Sumber Protein Jogja',
            section_badge: 'Ulasan Pelanggan',
            section_title: 'Apa Kata Mereka?',
            section_subtitle: 'Pengalaman nyata dari ibu rumah tangga, chef rumahan, hingga pemilik kedai kuliner di Yogyakarta.',
            rating: 5,
            total_reviews: '6',
            displayed_count: 5,
            google_place_url: null,
            review_mode: 'manual',
            items: [],
        },
        location: {},
        actual_footer: {},
    };

    // Auto-resolve initial location contact_key if not set
    if (footer.location && !footer.location.contact_key) {
        const existingPhone = footer.location.phone || footer.actual_footer?.outlet_phone || '';
        const cleanExisting = (existingPhone || '').replace(/[^0-9]/g, '');
        const matchedContact = (contacts || []).find(c => {
            const cleanC = (c.value || '').replace(/[^0-9]/g, '');
            return cleanC && cleanExisting && cleanC === cleanExisting;
        });
        if (matchedContact) {
            footer.location.contact_key = matchedContact.key || matchedContact.id;
        } else if (contacts && contacts.length > 0) {
            const defaultContact = contacts.find(c => c.key === 'order_wa' || c.key === 'main_phone') || contacts[0];
            footer.location.contact_key = defaultContact.key || defaultContact.id;
        }
    }

    return {
        csrfToken: config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        contacts: contacts,
        footer: footer,
        activeTab: 'reviews', // 'reviews' | 'location' | 'footer'

        getContactByKey(key) {
            if (!key || !this.contacts) return null;
            return this.contacts.find(c => (c.key === key || c.id === key)) || null;
        },

        getLocationContactDisplay() {
            const key = this.footer.location?.contact_key || this.footer.location?.contact_id;
            if (key) {
                const c = this.getContactByKey(key);
                if (c && c.value) return c.value;
            }
            return this.footer.location?.phone || this.footer.actual_footer?.outlet_phone || '+62 812-3456-7890';
        },

        onLocationContactChange() {
            const key = this.footer.location?.contact_key;
            if (key) {
                const c = this.getContactByKey(key);
                if (c) {
                    this.footer.location.phone = c.value;
                    if (!this.footer.actual_footer) this.footer.actual_footer = {};
                    this.footer.actual_footer.outlet_phone = c.value;
                }
            }
        },
        
        // Preview Device & Virtual Viewport State
        previewDevice: 'desktop', // 'desktop' | 'tablet' | 'mobile'
        previewBoxWidth: 1000,
        previewBoxHeight: 550,
        previewObserver: null,
        toastMessage: '',
        toastVisible: false,
        isSyncing: false,
        isSaving: false,
        
        // Reference Viewport Dimensions (Laptop 14-inch 1366x768, Tablet 1024x768, Mobile 393x852)
        virtualDimensions: {
            desktop: { width: 1366, height: 768 },
            tablet:  { width: 1024, height: 768 },
            mobile:  { width: 393,  height: 852 }
        },
        
        get currentVirtualWidth() {
            return this.virtualDimensions[this.previewDevice]?.width || (this.previewDevice === 'mobile' ? 393 : (this.previewDevice === 'tablet' ? 1024 : 1366));
        },
        
        get currentVirtualHeight() {
            return this.virtualDimensions[this.previewDevice]?.height || (this.previewDevice === 'mobile' ? 852 : 768);
        },
        
        // Scale dynamically fits BOTH available width AND fixed available height of previewBoxWrapper
        get currentScale() {
            const availableW = Math.max(200, (this.previewBoxWidth || 1000) - 24);
            const availableH = Math.max(200, (this.previewBoxHeight || 550) - 24);
            const scaleX = availableW / this.currentVirtualWidth;
            const scaleY = availableH / this.currentVirtualHeight;
            return Math.min(scaleX, scaleY);
        },
        
        get currentFrameWidth() {
            return Math.round(this.currentVirtualWidth * this.currentScale);
        },
        
        get currentFrameHeight() {
            return Math.round(this.currentVirtualHeight * this.currentScale);
        },

        get activeReviews() {
            return (this.footer.reviews?.items || []).filter(i => i.is_active).slice(0, this.footer.reviews?.displayed_count || 3);
        },

        // Computed formatted display address from structured address fields
        get computedLocationAddress() {
            const addr = this.footer.location?.address;
            if (!addr) return '';
            const parts = [addr.street, addr.district, addr.city, addr.province, addr.postal_code].filter(Boolean);
            return parts.length > 0 ? parts.join(', ') : (addr.full || '');
        },
        
        // Dynamic Social Media URL & Platform Detector
        getSocialPlatform(url) {
            if (!url || typeof url !== 'string' || url.trim() === '') {
                return { key: 'generic', name: 'Link Web', badgeClass: 'bg-gray-100 text-gray-700 border-gray-200', hoverClass: 'hover:bg-brand-primary' };
            }
            const u = url.toLowerCase().trim();
            if (u.includes('instagram.com') || u.includes('instagr.am')) {
                return { key: 'instagram', name: 'Instagram', badgeClass: 'bg-pink-50 text-pink-700 border-pink-200', hoverClass: 'hover:bg-[#E1306C]' };
            }
            if (u.includes('tiktok.com')) {
                return { key: 'tiktok', name: 'TikTok', badgeClass: 'bg-gray-900 text-white border-black', hoverClass: 'hover:bg-black' };
            }
            if (u.includes('wa.me') || u.includes('whatsapp.com')) {
                return { key: 'whatsapp', name: 'WhatsApp', badgeClass: 'bg-emerald-50 text-emerald-800 border-emerald-300', hoverClass: 'hover:bg-[#25D366]' };
            }
            if (u.includes('facebook.com') || u.includes('fb.com') || u.includes('fb.watch')) {
                return { key: 'facebook', name: 'Facebook', badgeClass: 'bg-blue-50 text-blue-700 border-blue-200', hoverClass: 'hover:bg-[#1877F2]' };
            }
            if (u.includes('youtube.com') || u.includes('youtu.be')) {
                return { key: 'youtube', name: 'YouTube', badgeClass: 'bg-red-50 text-red-700 border-red-200', hoverClass: 'hover:bg-[#FF0000]' };
            }
            if (u.includes('x.com') || u.includes('twitter.com')) {
                return { key: 'twitter', name: 'X / Twitter', badgeClass: 'bg-gray-800 text-white border-black', hoverClass: 'hover:bg-black' };
            }
            if (u.includes('shopee.')) {
                return { key: 'shopee', name: 'Shopee', badgeClass: 'bg-orange-50 text-orange-700 border-orange-200', hoverClass: 'hover:bg-[#EE4D2D]' };
            }
            if (u.includes('tokopedia.')) {
                return { key: 'tokopedia', name: 'Tokopedia', badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200', hoverClass: 'hover:bg-[#03AC0E]' };
            }
            return { key: 'generic', name: 'Link Web', badgeClass: 'bg-gray-100 text-gray-700 border-gray-200', hoverClass: 'hover:bg-brand-primary' };
        },
        
        addSocialLink() {
            if (!this.footer.actual_footer.social_links) {
                this.footer.actual_footer.social_links = [];
            }
            this.footer.actual_footer.social_links.push({
                id: Date.now(),
                url: ''
            });
            this.showToast('Baris media sosial baru ditambahkan.');
        },
        
        removeSocialLink(index) {
            this.footer.actual_footer.social_links.splice(index, 1);
            this.showToast('Media sosial telah dihapus.');
        },
        
        initPreviewObserver() {
            this.$nextTick(() => {
                if (this.$refs.previewBoxWrapper) {
                    const rect = this.$refs.previewBoxWrapper.getBoundingClientRect();
                    if (rect.width > 50) {
                        this.previewBoxWidth = rect.width;
                        this.previewBoxHeight = rect.height;
                    }
                    if (!this.previewObserver && window.ResizeObserver) {
                        this.previewObserver = new ResizeObserver((entries) => {
                            for (let entry of entries) {
                                const w = entry.contentRect.width;
                                const h = entry.contentRect.height;
                                if (w > 50) this.previewBoxWidth = w;
                                if (h > 50) this.previewBoxHeight = h;
                            }
                        });
                        this.previewObserver.observe(this.$refs.previewBoxWrapper);
                    }
                }
            });
        },
        
        reviewModalOpen: false,
        isEditingReview: false,
        selectedReview: null,
        reviewDeleteModalOpen: false,
        reviewFilterSource: 'all',
        reviewForm: {
            id: null,
            reviewer_name: '',
            reviewer_title: '',
            reviewer_location: '',
            review_text: '',
            rating: 5,
            reviewed_at: '',
            source: 'manual',
            is_active: true,
        },

        openCreateReviewModal() {
            this.isEditingReview = false;
            this.reviewForm = {
                id: null,
                reviewer_name: '',
                reviewer_title: '',
                reviewer_location: '',
                review_text: '',
                rating: 5,
                reviewed_at: new Date().toISOString().split('T')[0],
                source: this.footer.reviews?.review_mode === 'google' ? 'google' : 'manual',
                is_active: true,
            };
            this.reviewModalOpen = true;
        },

        openEditReviewModal(item) {
            this.isEditingReview = true;
            this.reviewForm = {
                id: item.id,
                reviewer_name: item.name,
                reviewer_title: item.role || '',
                reviewer_location: item.location || '',
                review_text: item.review_text || item.comment || '',
                rating: item.rating || 5,
                reviewed_at: item.reviewed_at || '',
                source: item.source === 'Google Review' ? 'google' : 'manual',
                is_active: item.is_active,
            };
            this.reviewModalOpen = true;
        },

        handleResponseError(response, result) {
            if (response.status === 401 || (result && result.message === 'Unauthenticated.')) {
                alert('Sesi login Anda telah berakhir. Anda akan dialihkan ke halaman login.');
                window.location.href = '/login';
                return true;
            }
            if (response.status === 419) {
                alert('Sesi token kedaluwarsa. Halaman akan dimuat ulang.');
                window.location.reload();
                return true;
            }
            return false;
        },

        async saveReview() {
            if (!this.reviewForm.reviewer_name || !this.reviewForm.review_text) {
                alert('Nama reviewer dan isi ulasan wajib diisi.');
                return;
            }

            try {
                const url = this.isEditingReview ? `/admin/reviews/${this.reviewForm.id}` : '/admin/reviews';
                const method = this.isEditingReview ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify(this.reviewForm),
                });

                const result = await response.json().catch(() => ({}));

                if (this.handleResponseError(response, result)) return;

                if (!response.ok || !result.success) {
                    alert(result.message || 'Gagal menyimpan ulasan.');
                    return;
                }

                if (this.isEditingReview) {
                    const idx = this.footer.reviews.items.findIndex(i => i.id === this.reviewForm.id);
                    if (idx !== -1) {
                        this.footer.reviews.items[idx].name = this.reviewForm.reviewer_name;
                        this.footer.reviews.items[idx].role = this.reviewForm.reviewer_title;
                        this.footer.reviews.items[idx].location = this.reviewForm.reviewer_location;
                        this.footer.reviews.items[idx].comment = this.reviewForm.review_text;
                        this.footer.reviews.items[idx].review_text = this.reviewForm.review_text;
                        this.footer.reviews.items[idx].rating = this.reviewForm.rating;
                        this.footer.reviews.items[idx].source = this.reviewForm.source === 'google' ? 'Google Review' : 'Manual Review';
                        this.footer.reviews.items[idx].is_active = this.reviewForm.is_active;
                    }
                } else {
                    const newRev = {
                        id: result.review.id,
                        name: result.review.reviewer_name,
                        role: result.review.reviewer_title,
                        location: result.review.reviewer_location,
                        comment: result.review.review_text,
                        review_text: result.review.review_text,
                        rating: result.review.rating,
                        time: 'Baru saja',
                        source: result.review.source === 'google' ? 'Google Review' : 'Manual Review',
                        is_active: result.review.is_active,
                    };
                    this.footer.reviews.items.unshift(newRev);
                }

                this.reviewModalOpen = false;
                this.showToast(result.message);
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat menyimpan ulasan.');
            }
        },

        async toggleReviewStatus(item) {
            try {
                const response = await fetch(`/admin/reviews/${item.id}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });

                const result = await response.json().catch(() => ({}));

                if (this.handleResponseError(response, result)) return;

                if (!response.ok || !result.success) {
                    alert(result.message || 'Gagal mengubah status ulasan.');
                    return;
                }

                item.is_active = result.is_active;
                this.showToast(result.message);
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat mengubah status ulasan.');
            }
        },

        async deleteReview(item) {
            if (!confirm(`Hapus ulasan dari "${item.name}"?`)) return;

            try {
                const response = await fetch(`/admin/reviews/${item.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });

                const result = await response.json().catch(() => ({}));

                if (this.handleResponseError(response, result)) return;

                if (!response.ok || !result.success) {
                    alert(result.message || 'Gagal menghapus ulasan.');
                    return;
                }

                this.footer.reviews.items = this.footer.reviews.items.filter(i => i.id !== item.id);
                this.showToast(result.message);
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat menghapus ulasan.');
            }
        },

        async toggleReviewMode(targetMode) {
            if (this.footer.reviews.review_mode === targetMode) return;

            try {
                const response = await fetch('/admin/reviews/mode', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({ mode: targetMode }),
                });

                const result = await response.json().catch(() => ({}));

                if (this.handleResponseError(response, result)) return;

                if (!response.ok || !result.success) {
                    alert(result.message || 'Gagal mengubah mode ulasan.');
                    return;
                }

                this.footer.reviews.review_mode = targetMode;
                this.showToast(result.message);
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat mengubah mode ulasan.');
            }
        },

        async saveGoogleConfig() {
            try {
                const placeId = (this.footer.reviews.google_place_id || '').trim();
                const response = await fetch('/admin/reviews/google-config', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        google_place_id: placeId || null,
                        google_rating: this.footer.reviews.rating,
                        google_total_reviews: parseInt(this.footer.reviews.total_reviews) || 0,
                    }),
                });

                const result = await response.json().catch(() => ({}));

                if (this.handleResponseError(response, result)) return;

                if (!response.ok || !result.success) {
                    alert(result.message || 'Gagal menyimpan konfigurasi Google.');
                    return;
                }

                this.footer.reviews.google_place_id = placeId;
                this.footer.reviews.google_place_url = placeId ? `https://search.google.com/local/writereview?placeid=${placeId}` : null;

                this.showToast(result.message || 'Konfigurasi Google Review berhasil disimpan.');
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat menyimpan konfigurasi Google.');
            }
        },
        
        showToast(msg) {
            this.toastMessage = msg;
            this.toastVisible = true;
            setTimeout(() => { this.toastVisible = false; }, 3000);
        },
        
        async syncGoogleReviews() {
            if (this.isSyncing) return;
            this.isSyncing = true;
            try {
                const response = await fetch('/admin/reviews/sync-google', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        google_place_id: this.footer.reviews.google_place_id,
                    }),
                });

                const result = await response.json().catch(() => ({}));

                if (this.handleResponseError(response, result)) return;

                if (result.success) {
                    if (result.reviews) {
                        this.footer.reviews.items = result.reviews;
                    }
                    if (result.settings) {
                        if (result.settings.google_rating) this.footer.reviews.rating = result.settings.google_rating;
                        if (result.settings.google_total_reviews) this.footer.reviews.total_reviews = result.settings.google_total_reviews;
                        if (result.settings.last_synced_at) this.footer.reviews.last_updated = result.settings.last_synced_at;
                    }
                    this.showToast(result.message || 'Data Google Reviews berhasil disinkronkan.');
                } else {
                    alert(result.message || 'Gagal menyinkronkan data dari Google.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat menyinkronkan data ulasan Google.');
            } finally {
                this.isSyncing = false;
            }
        },
        
        async saveFooter() {
            if (this.isSaving) return;
            this.isSaving = true;

            try {
                const response = await fetch('/admin/footer', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        location: this.footer.location,
                        actual_footer: this.footer.actual_footer,
                    })
                });

                const result = await response.json().catch(() => ({}));

                if (this.handleResponseError(response, result)) return;

                if (response.ok && result.success) {
                    this.showToast(result.message || 'Pengaturan Lokasi & Footer berhasil disimpan ke database!');
                } else {
                    alert(result.message || 'Gagal menyimpan pengaturan footer.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat menyimpan pengaturan footer.');
            } finally {
                this.isSaving = false;
            }
        }
    };
}
