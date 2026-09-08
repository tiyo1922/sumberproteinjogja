@php
    $site = $site ?? config('site');
    $cleanOrderWa = preg_replace('/[^0-9]/', '', $site['contact']['order_whatsapp'] ?? '6281234567891');
@endphp

<!-- Cart State Store & Logic -->
<script>
    document.addEventListener('alpine:init', () => {
        // Internal Price Configuration (Prepared for future extension)
        const SHOW_PRICE_IN_CART = false;
        const SHOW_PRICE_IN_WHATSAPP = false;

        Alpine.store('cart', {
            items: [],
            modalOpen: false,
            lastAddedId: null,
            step: 'cart', // 'cart' | 'confirmation'

            // Transient Customer Data (In-memory only, NEVER saved to localStorage/DB)
            customer: {
                name: '',
                phone: '',
                address: '',
                note: ''
            },

            // Validation Errors
            errors: {
                name: '',
                phone: '',
                address: ''
            },

            init() {
                try {
                    const stored = localStorage.getItem('sumber_protein_cart');
                    if (stored) {
                        const parsed = JSON.parse(stored);
                        if (Array.isArray(parsed)) {
                            this.items = parsed;
                        }
                    }
                } catch (e) {
                    console.error('Failed to load cart from localStorage', e);
                    this.items = [];
                }
            },

            get totalCount() {
                return this.items.reduce((sum, item) => sum + (item.quantity || 0), 0);
            },

            addItem(id, name, price = 0) {
                const existing = this.items.find(i => String(i.id) === String(id));
                if (existing) {
                    existing.quantity += 1;
                } else {
                    this.items.push({
                        id: String(id),
                        name: name,
                        quantity: 1,
                        price: price
                    });
                }

                // Visual feedback trigger
                this.lastAddedId = String(id);
                setTimeout(() => {
                    if (this.lastAddedId === String(id)) {
                        this.lastAddedId = null;
                    }
                }, 800);

                this.save();
            },

            increment(id) {
                const item = this.items.find(i => String(i.id) === String(id));
                if (item) {
                    item.quantity += 1;
                    this.save();
                }
            },

            decrement(id) {
                const item = this.items.find(i => String(i.id) === String(id));
                if (item) {
                    if (item.quantity > 1) {
                        item.quantity -= 1;
                    } else {
                        this.items = this.items.filter(i => String(i.id) !== String(id));
                    }
                    this.save();
                    if (this.items.length === 0) {
                        this.closeModal();
                    }
                }
            },

            removeItem(id) {
                this.items = this.items.filter(i => String(i.id) !== String(id));
                this.save();
                if (this.items.length === 0) {
                    this.closeModal();
                }
            },

            clearCart() {
                this.items = [];
                this.save();
                this.closeModal();
            },

            closeModal() {
                this.modalOpen = false;
                this.step = 'cart';
                this.customer = {
                    name: '',
                    phone: '',
                    address: '',
                    note: ''
                };
                this.clearErrors();
            },

            clearErrors() {
                this.errors = {
                    name: '',
                    phone: '',
                    address: ''
                };
            },

            save() {
                try {
                    if (this.items.length > 0) {
                        localStorage.setItem('sumber_protein_cart', JSON.stringify(this.items));
                    } else {
                        localStorage.removeItem('sumber_protein_cart');
                    }
                } catch (e) {
                    console.error('Failed to save cart to localStorage', e);
                }
            },

            validateForm() {
                let valid = true;
                this.clearErrors();

                const trimmedName = (this.customer.name || '').trim();
                if (!trimmedName) {
                    this.errors.name = 'Nama pelanggan wajib diisi.';
                    valid = false;
                }

                const rawPhone = (this.customer.phone || '').trim();
                if (!rawPhone) {
                    this.errors.phone = 'Nomor WhatsApp wajib diisi.';
                    valid = false;
                } else {
                    const cleanDigits = rawPhone.replace(/\D/g, '');
                    const plausiblePhoneRegex = /^[\+]?[0-9\s\-\(\)\.]{8,22}$/;
                    if (!plausiblePhoneRegex.test(rawPhone) || cleanDigits.length < 9 || cleanDigits.length > 16) {
                        this.errors.phone = 'Masukkan nomor WhatsApp yang valid.';
                        valid = false;
                    }
                }

                const trimmedAddress = (this.customer.address || '').trim();
                if (!trimmedAddress) {
                    this.errors.address = 'Alamat pengiriman wajib diisi.';
                    valid = false;
                }

                return valid;
            },

            submitToConfirmation() {
                if (this.items.length === 0) return;
                if (!this.validateForm()) return;
                this.step = 'confirmation';
            },

            backToCart() {
                this.step = 'cart';
            },

            confirmAndSendWhatsApp() {
                if (this.items.length === 0) return;
                if (!this.validateForm()) {
                    this.step = 'cart';
                    return;
                }

                // 1. Conversion Analytics Tracking (Triggered ONLY on final action)
                if (typeof window.trackTrafficEvent === 'function') {
                    window.trackTrafficEvent('pesan_order_wa');
                }

                // 2. Build Formatted WhatsApp Message
                let message = `Halo Sumber Protein Jogja, saya ${this.customer.name.trim()} ingin memesan:\n\n`;
                this.items.forEach(item => {
                    message += `• ${item.name} x${item.quantity}\n`;
                });
                message += `\nAlamat pengiriman:\n${this.customer.address.trim()}\n\nNomor HP: ${this.customer.phone.trim()}\n\nMohon dibantu cek ketersediaan barang dan estimasi pengiriman/ongkirnya ya.\n\nTerima kasih 🙏`;

                const trimmedNote = (this.customer.note || '').trim();
                if (trimmedNote) {
                    message += `\n\nCatatan:\n${trimmedNote}`;
                }

                // 3. Open WhatsApp Destination
                const phone = "{{ $cleanOrderWa }}";
                const url = "https://wa.me/" + phone + "?text=" + encodeURIComponent(message);
                window.open(url, '_blank');

                // 4. Modal cleanup (transient reset)
                this.closeModal();
            }
        });
    });
</script>

<!-- Floating Cart Action Button (Only visible when cartCount > 0) -->
<div x-data
     x-show="$store.cart.totalCount > 0"
     x-cloak
     x-transition:enter="transition ease-out duration-300 transform"
     x-transition:enter-start="opacity-0 translate-y-6 scale-90"
     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
     x-transition:leave="transition ease-in duration-200 transform"
     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
     x-transition:leave-end="opacity-0 translate-y-6 scale-90"
     class="fixed bottom-20 right-4 sm:bottom-6 sm:right-6 z-40">

    <button @click="$store.cart.modalOpen = true"
            type="button"
            aria-label="Lihat pesanan"
            class="group relative inline-flex items-center justify-center bg-brand-primary hover:bg-brand-primary-dark text-white p-3 rounded-full shadow-floating hover:shadow-2xl active:scale-95 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-brand-primary/40 cursor-pointer">

        <!-- Cart Icon -->
        <svg class="w-6 h-6 fill-none stroke-current stroke-2 shrink-0 group-hover:scale-110 transition-transform duration-200" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>

        <!-- Item Count Badge -->
        <span class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1.5 rounded-full bg-rose-500 text-white text-[10px] font-extrabold flex items-center justify-center ring-2 ring-white shadow-xs z-10"
              x-text="$store.cart.totalCount > 99 ? '99+' : $store.cart.totalCount">
        </span>
    </button>
</div>

<!-- Checkout & Confirmation Modal -->
<div x-data
     x-show="$store.cart.modalOpen"
     x-cloak
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-cart-title"
     @keydown.escape.window="$store.cart.closeModal()"
     class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 md:p-6">

    <!-- Backdrop Overlay -->
    <div x-show="$store.cart.modalOpen"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="$store.cart.closeModal()"
         class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"></div>

    <!-- Modal Dialog Content -->
    <div x-show="$store.cart.modalOpen"
         x-transition:enter="transition ease-out duration-250 transform"
         x-transition:enter-start="opacity-0 scale-95 translate-y-3"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150 transform"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-3"
         @click.away="$store.cart.closeModal()"
         class="relative bg-white w-full max-w-lg rounded-modern-lg shadow-2xl overflow-hidden flex flex-col max-h-[90vh] z-10 border border-gray-100">

        <!-- ========================================== -->
        <!-- STATE 1: CART + CUSTOMER FORM HEADER      -->
        <!-- ========================================== -->
        <template x-if="$store.cart.step === 'cart'">
            <div class="px-5 py-4 sm:px-6 sm:py-4.5 border-b border-gray-100 flex items-center justify-between bg-brand-cream/40 shrink-0">
                <div>
                    <h3 id="modal-cart-title" class="text-base sm:text-lg font-extrabold text-brand-dark">
                        Pesanan Anda
                    </h3>
                    <p class="text-xs text-gray-500 font-medium mt-0.5" x-text="`${$store.cart.totalCount} item dipilih`"></p>
                </div>

                <button @click="$store.cart.closeModal()"
                        type="button"
                        aria-label="Tutup"
                        class="p-2 rounded-full text-gray-400 hover:text-brand-dark hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-primary/30 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </template>

        <!-- ========================================== -->
        <!-- STATE 2: CONFIRMATION HEADER              -->
        <!-- ========================================== -->
        <template x-if="$store.cart.step === 'confirmation'">
            <div class="px-5 py-4 sm:px-6 sm:py-4.5 border-b border-gray-100 flex items-center justify-between bg-brand-soft-green/30 shrink-0">
                <div>
                    <h3 id="modal-cart-title" class="text-base sm:text-lg font-extrabold text-brand-dark flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Konfirmasi Pesanan</span>
                    </h3>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">
                        Periksa pesanan dan data pengiriman Anda sebelum melanjutkan.
                    </p>
                </div>

                <button @click="$store.cart.closeModal()"
                        type="button"
                        aria-label="Tutup"
                        class="p-2 rounded-full text-gray-400 hover:text-brand-dark hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-primary/30 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </template>

        <!-- ========================================== -->
        <!-- MODAL BODY: SCROLLABLE CONTENT            -->
        <!-- ========================================== -->
        <div class="p-5 sm:p-6 overflow-y-auto max-h-[calc(90vh-145px)] space-y-6">

            <!-- -------------------------------------- -->
            <!-- STATE 1 BODY: CART ITEMS + FORM        -->
            <!-- -------------------------------------- -->
            <div x-show="$store.cart.step === 'cart'" class="space-y-6">

                <!-- Section: Item List -->
                <div>
                    <div class="flex items-center justify-between mb-2.5">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Daftar Produk</span>
                        <button @click="if(confirm('Kosongkan semua pesanan?')) $store.cart.clearCart()"
                                type="button"
                                class="text-xs font-semibold text-red-500 hover:text-red-700 transition-colors focus:outline-none cursor-pointer">
                            Kosongkan
                        </button>
                    </div>

                    <div class="bg-gray-50/70 rounded-modern border border-gray-100 p-3 sm:p-4 divide-y divide-gray-100 space-y-2.5">
                        <template x-for="item in $store.cart.items" :key="item.id">
                            <div class="pt-2.5 first:pt-0 flex items-center justify-between gap-3">
                                <div class="flex-1 pr-2">
                                    <p class="text-xs sm:text-sm font-bold text-brand-dark leading-snug" x-text="item.name"></p>
                                </div>

                                <!-- Quantity Adjusters -->
                                <div class="flex items-center gap-1.5 shrink-0 bg-white p-1 rounded-modern border border-gray-200/70 shadow-2xs">
                                    <button @click="$store.cart.decrement(item.id)"
                                            type="button"
                                            aria-label="Kurangi jumlah"
                                            class="w-6 h-6 rounded bg-gray-100 hover:bg-gray-200 text-brand-dark flex items-center justify-center font-bold text-xs transition-colors cursor-pointer">
                                        −
                                    </button>

                                    <span class="font-extrabold text-xs sm:text-sm text-brand-dark min-w-[24px] text-center px-1" x-text="`x${item.quantity}`"></span>

                                    <button @click="$store.cart.increment(item.id)"
                                            type="button"
                                            aria-label="Tambah jumlah"
                                            class="w-6 h-6 rounded bg-brand-soft-green hover:bg-brand-primary hover:text-white text-brand-primary flex items-center justify-center font-bold text-xs transition-colors cursor-pointer">
                                        +
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Section: Customer Form -->
                <div class="pt-2 border-t border-gray-100 space-y-3.5">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 block">Data Pemesan & Pengiriman</span>

                    <!-- Nama Pelanggan -->
                    <div>
                        <label for="cart-customer-name" class="block text-xs font-bold text-brand-dark mb-1">
                            Nama Pelanggan <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="cart-customer-name"
                               x-model="$store.cart.customer.name"
                               @input="$store.cart.errors.name = ''"
                               placeholder="Contoh: Budi Santoso"
                               class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-modern border bg-white focus:outline-none focus:ring-2 transition-colors"
                               :class="$store.cart.errors.name ? 'border-red-400 focus:ring-red-400/30 focus:border-red-500' : 'border-gray-200 focus:ring-brand-primary/30 focus:border-brand-primary'">
                        <p x-show="$store.cart.errors.name"
                           x-text="$store.cart.errors.name"
                           class="text-[11px] text-red-500 font-medium mt-1"></p>
                    </div>

                    <!-- Nomor WhatsApp -->
                    <div>
                        <label for="cart-customer-phone" class="block text-xs font-bold text-brand-dark mb-1">
                            Nomor WhatsApp <span class="text-red-500">*</span>
                        </label>
                        <input type="tel"
                               id="cart-customer-phone"
                               x-model="$store.cart.customer.phone"
                               @input="$store.cart.errors.phone = ''"
                               placeholder="Contoh: 081234567890"
                               class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-modern border bg-white focus:outline-none focus:ring-2 transition-colors"
                               :class="$store.cart.errors.phone ? 'border-red-400 focus:ring-red-400/30 focus:border-red-500' : 'border-gray-200 focus:ring-brand-primary/30 focus:border-brand-primary'">
                        <p x-show="$store.cart.errors.phone"
                           x-text="$store.cart.errors.phone"
                           class="text-[11px] text-red-500 font-medium mt-1"></p>
                    </div>

                    <!-- Alamat Pengiriman Lengkap -->
                    <div>
                        <label for="cart-customer-address" class="block text-xs font-bold text-brand-dark mb-1">
                            Alamat Pengiriman Lengkap <span class="text-red-500">*</span>
                        </label>
                        <textarea id="cart-customer-address"
                                  rows="3"
                                  x-model="$store.cart.customer.address"
                                  @input="$store.cart.errors.address = ''"
                                  placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, patokan lokasi..."
                                  class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-modern border bg-white focus:outline-none focus:ring-2 transition-colors resize-y min-h-[70px]"
                                  :class="$store.cart.errors.address ? 'border-red-400 focus:ring-red-400/30 focus:border-red-500' : 'border-gray-200 focus:ring-brand-primary/30 focus:border-brand-primary'"></textarea>
                        <p x-show="$store.cart.errors.address"
                           x-text="$store.cart.errors.address"
                           class="text-[11px] text-red-500 font-medium mt-1"></p>
                    </div>

                    <!-- Catatan Pelanggan (Optional) -->
                    <div>
                        <label for="cart-customer-note" class="block text-xs font-bold text-brand-dark mb-1">
                            Catatan Pelanggan <span class="text-gray-400 font-normal">(Opsional)</span>
                        </label>
                        <textarea id="cart-customer-note"
                                  rows="2"
                                  x-model="$store.cart.customer.note"
                                  placeholder="Contoh: Potong 8 bagian, kirim sebelum jam 11 siang..."
                                  class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-modern border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary transition-colors resize-y min-h-[55px]"></textarea>
                    </div>

                </div>

            </div>

            <!-- -------------------------------------- -->
            <!-- STATE 2 BODY: CONFIRMATION REVIEW      -->
            <!-- -------------------------------------- -->
            <div x-show="$store.cart.step === 'confirmation'" class="space-y-5">

                <!-- SECTION: PESANAN -->
                <div class="bg-gray-50/80 rounded-modern border border-gray-200/70 p-4">
                    <div class="flex items-center justify-between border-b border-gray-200/60 pb-2 mb-3">
                        <span class="text-xs font-extrabold text-brand-dark tracking-wider uppercase">PESANAN</span>
                        <span class="text-xs font-semibold text-gray-500" x-text="`${$store.cart.totalCount} item`"></span>
                    </div>

                    <div class="space-y-2">
                        <template x-for="item in $store.cart.items" :key="item.id">
                            <div class="flex items-start justify-between gap-3 text-xs sm:text-sm">
                                <span class="font-bold text-brand-dark" x-text="item.name"></span>
                                <span class="font-extrabold text-brand-primary shrink-0 ml-2" x-text="`x${item.quantity}`"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- SECTION: DATA PENGIRIMAN -->
                <div class="bg-gray-50/80 rounded-modern border border-gray-200/70 p-4 space-y-3.5">
                    <div class="border-b border-gray-200/60 pb-2">
                        <span class="text-xs font-extrabold text-brand-dark tracking-wider uppercase">DATA PENGIRIMAN</span>
                    </div>

                    <!-- Nama -->
                    <div>
                        <span class="text-[11px] text-gray-400 font-bold uppercase tracking-wider block">Nama</span>
                        <p class="text-xs sm:text-sm font-bold text-brand-dark mt-0.5" x-text="$store.cart.customer.name"></p>
                    </div>

                    <!-- Nomor WhatsApp -->
                    <div>
                        <span class="text-[11px] text-gray-400 font-bold uppercase tracking-wider block">Nomor WhatsApp</span>
                        <p class="text-xs sm:text-sm font-bold text-brand-dark mt-0.5" x-text="$store.cart.customer.phone"></p>
                    </div>

                    <!-- Alamat Pengiriman -->
                    <div>
                        <span class="text-[11px] text-gray-400 font-bold uppercase tracking-wider block">Alamat Pengiriman</span>
                        <p class="text-xs sm:text-sm font-semibold text-brand-dark mt-0.5 whitespace-pre-wrap leading-relaxed" x-text="$store.cart.customer.address"></p>
                    </div>

                    <!-- Catatan (Only shown if non-empty) -->
                    <div x-show="$store.cart.customer.note && $store.cart.customer.note.trim() !== ''">
                        <span class="text-[11px] text-gray-400 font-bold uppercase tracking-wider block">Catatan</span>
                        <p class="text-xs sm:text-sm font-semibold text-brand-dark mt-0.5 whitespace-pre-wrap leading-relaxed" x-text="$store.cart.customer.note"></p>
                    </div>
                </div>

            </div>

        </div>

        <!-- ========================================== -->
        <!-- MODAL FOOTER ACTIONS                       -->
        <!-- ========================================== -->
        <div class="px-5 py-4 sm:px-6 sm:py-4 bg-gray-50/90 border-t border-gray-100 flex items-center justify-between gap-3 shrink-0">

            <!-- STATE 1 FOOTER: Clear & Kirim -->
            <template x-if="$store.cart.step === 'cart'">
                <div class="w-full flex items-center justify-between gap-3">
                    <button @click="$store.cart.closeModal()"
                            type="button"
                            class="text-xs sm:text-sm font-semibold text-gray-500 hover:text-brand-dark px-3 py-2.5 rounded-modern transition-colors focus:outline-none focus:ring-2 focus:ring-gray-300 cursor-pointer">
                        Lanjut Belanja
                    </button>

                    <button @click="$store.cart.submitToConfirmation()"
                            type="button"
                            class="inline-flex items-center justify-center gap-1.5 px-6 py-2.5 sm:py-3 rounded-modern font-bold text-xs sm:text-sm text-white bg-brand-primary hover:bg-brand-primary-dark active:scale-95 shadow-md shadow-brand-primary/20 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-primary/40 cursor-pointer">
                        <span>Kirim</span>
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </template>

            <!-- STATE 2 FOOTER: Ubah Pesanan & Pesan Sekarang (WhatsApp) -->
            <template x-if="$store.cart.step === 'confirmation'">
                <div class="w-full flex items-center justify-between gap-3">
                    <button @click="$store.cart.backToCart()"
                            type="button"
                            class="inline-flex items-center gap-1 text-xs sm:text-sm font-semibold text-gray-600 hover:text-brand-dark px-3 py-2.5 rounded-modern hover:bg-gray-200/60 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-300 cursor-pointer">
                        <span>← Ubah Pesanan</span>
                    </button>

                    <button @click="$store.cart.confirmAndSendWhatsApp()"
                            type="button"
                            class="inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 rounded-modern font-bold text-xs sm:text-sm text-white bg-[#25D366] hover:bg-[#1EBE5D] active:scale-95 shadow-md shadow-emerald-500/20 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#25D366]/40 cursor-pointer">
                        <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        <span>Pesan Sekarang</span>
                    </button>
                </div>
            </template>

        </div>

    </div>
</div>
