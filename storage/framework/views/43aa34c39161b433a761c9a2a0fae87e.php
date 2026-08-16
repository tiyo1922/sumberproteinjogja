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
                        this.modalOpen = false;
                    }
                }
            },

            removeItem(id) {
                this.items = this.items.filter(i => String(i.id) !== String(id));
                this.save();
                if (this.items.length === 0) {
                    this.modalOpen = false;
                }
            },

            clearCart() {
                this.items = [];
                this.save();
                this.modalOpen = false;
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

            checkoutWhatsApp() {
                if (this.items.length === 0) return;

                let message = "Halo, saya ingin memesan:\n\n";
                this.items.forEach(item => {
                    message += `- ${item.name} x${item.quantity}\n`;
                });
                message += "\nMohon informasi lebih lanjut mengenai ketersediaan produk.\n\nTerima kasih.";

                const phone = "6281234567890";
                const url = "https://wa.me/" + phone + "?text=" + encodeURIComponent(message);
                window.open(url, '_blank');
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
     class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-40">
    
    <button @click="$store.cart.modalOpen = true"
            type="button"
            aria-label="Lihat pesanan"
            class="group relative inline-flex items-center gap-2.5 bg-brand-primary hover:bg-brand-primary-dark text-white px-4 py-3 sm:px-5 sm:py-3.5 rounded-full shadow-lg shadow-brand-primary/30 hover:shadow-xl hover:shadow-brand-primary/40 active:scale-95 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-brand-primary/30 cursor-pointer">
        
        <!-- Cart Icon -->
        <svg class="w-5 h-5 fill-none stroke-current stroke-2 shrink-0 group-hover:scale-110 transition-transform duration-200" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>

        <!-- Count Text -->
        <span class="font-bold text-xs sm:text-sm tracking-wide" x-text="`${$store.cart.totalCount} item`"></span>
    </button>
</div>

<!-- Checkout Confirmation Modal ("Pesanan Anda") -->
<div x-data
     x-show="$store.cart.modalOpen"
     x-cloak
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-cart-title"
     @keydown.escape.window="$store.cart.modalOpen = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
    
    <!-- Backdrop Overlay -->
    <div x-show="$store.cart.modalOpen"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="$store.cart.modalOpen = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"></div>

    <!-- Modal Dialog Content -->
    <div x-show="$store.cart.modalOpen"
         x-transition:enter="transition ease-out duration-250 transform"
         x-transition:enter-start="opacity-0 scale-95 translate-y-3"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150 transform"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-3"
         @click.away="$store.cart.modalOpen = false"
         class="relative bg-white w-full max-w-md rounded-modern-lg shadow-2xl overflow-hidden flex flex-col max-h-[85vh] z-10 border border-gray-100">
        
        <!-- Modal Header -->
        <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-100 flex items-center justify-between bg-brand-cream/40">
            <div>
                <h3 id="modal-cart-title" class="text-base sm:text-lg font-extrabold text-brand-dark">
                    Pesanan Anda
                </h3>
                <p class="text-xs text-gray-500 font-medium mt-0.5" x-text="`${$store.cart.totalCount} item dipilih`"></p>
            </div>
            
            <button @click="$store.cart.modalOpen = false" 
                    type="button"
                    aria-label="Tutup"
                    class="p-2 rounded-full text-gray-400 hover:text-brand-dark hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-primary/30 cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Body: Scrollable Item List -->
        <div class="p-5 sm:p-6 overflow-y-auto max-h-[50vh] divide-y divide-gray-100 space-y-3">
            <template x-for="item in $store.cart.items" :key="item.id">
                <div class="pt-3 first:pt-0 flex items-center justify-between gap-3">
                    <div class="flex-1 pr-2">
                        <p class="text-xs sm:text-sm font-bold text-brand-dark leading-snug" x-text="item.name"></p>
                    </div>

                    <!-- Quantity Adjusters -->
                    <div class="flex items-center gap-1.5 shrink-0 bg-gray-50 p-1 rounded-modern border border-gray-200/60">
                        <button @click="$store.cart.decrement(item.id)" 
                                type="button"
                                aria-label="Kurangi jumlah"
                                class="w-6 h-6 rounded bg-white hover:bg-gray-200 text-brand-dark flex items-center justify-center font-bold text-xs shadow-2xs transition-colors cursor-pointer">
                            −
                        </button>
                        
                        <span class="font-extrabold text-xs sm:text-sm text-brand-dark min-w-[24px] text-center px-1" x-text="`x${item.quantity}`"></span>
                        
                        <button @click="$store.cart.increment(item.id)" 
                                type="button"
                                aria-label="Tambah jumlah"
                                class="w-6 h-6 rounded bg-brand-soft-green hover:bg-brand-primary hover:text-white text-brand-primary flex items-center justify-center font-bold text-xs shadow-2xs transition-colors cursor-pointer">
                            +
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Modal Footer: Clear & Order Action Buttons -->
        <div class="px-5 py-4 sm:px-6 sm:py-4 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between gap-3">
            <!-- Clear Button -->
            <button @click="if(confirm('Kosongkan semua pesanan?')) $store.cart.clearCart()"
                    type="button"
                    class="text-xs sm:text-sm font-semibold text-gray-500 hover:text-red-600 px-3 py-2.5 rounded-modern transition-colors focus:outline-none focus:ring-2 focus:ring-red-400/30 cursor-pointer">
                Kosongkan
            </button>

            <!-- Checkout Order via WhatsApp Button -->
            <button @click="$store.cart.checkoutWhatsApp()"
                    type="button"
                    class="inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 rounded-modern font-bold text-xs sm:text-sm text-white bg-[#25D366] hover:bg-[#1EBE5D] active:scale-95 shadow-md shadow-emerald-500/20 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#25D366]/40 cursor-pointer">
                <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                </svg>
                <span>Pesan</span>
            </button>
        </div>

    </div>
</div>
<?php /**PATH C:\xampp\htdocs\sumberproteinjogja\resources\views/components/floating-cart.blade.php ENDPATH**/ ?>