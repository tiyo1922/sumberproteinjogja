<section id="knowledge" 
         class="py-16 sm:py-24 bg-white relative scroll-mt-20 [overflow-anchor:none]"
         style="overflow-anchor: none;"
         x-data="{
             activeKnowledgeId: null,
             currentPage: 1,
             pageSize: 6,
             articles: {{ json_encode($knowledgeArticles) }},
             
             get totalArticles() {
                 return this.articles.length;
             },
             
             get totalPages() {
                 return Math.ceil(this.articles.length / this.pageSize);
             },
             
             get pagedArticles() {
                 const start = (this.currentPage - 1) * this.pageSize;
                 return this.articles.slice(start, start + this.pageSize);
             },
             
             getActiveArticle() {
                 return this.articles.find(a => a.id === this.activeKnowledgeId) || null;
             },
             
             toggleArticle(id) {
                 if (this.activeKnowledgeId === id) {
                     // Menutup artikel aktif -> collapse tanpa auto-scroll liar
                     this.activeKnowledgeId = null;
                 } else {
                     // Membuka artikel baru (baik dari collapsed maupun switch dari artikel lain)
                     this.activeKnowledgeId = id;
                     this.scrollToArticle(id);
                 }
             },
             
             scrollToArticle(id) {
                 // Gunakan double requestAnimationFrame agar browser Safari iOS menyelesaikan reflow & collapse artikel lama sebelum mengukur koordinat
                 requestAnimationFrame(() => {
                     requestAnimationFrame(() => {
                         const target = document.getElementById('knowledge-card-' + id);
                         if (!target) return;
                         
                         // Hitung tinggi sticky header secara dinamis + breathing room
                         const header = document.querySelector('header');
                         const headerHeight = header ? header.offsetHeight : (window.innerWidth < 640 ? 64 : 76);
                         const breathingRoom = window.innerWidth < 640 ? 14 : 20;
                         const totalOffset = headerHeight + breathingRoom;
                         
                         // Hitung posisi absolut target di document saat layout sudah 100% stabil
                         const elementPosition = target.getBoundingClientRect().top + window.scrollY;
                         const targetScrollY = Math.max(0, Math.round(elementPosition - totalOffset));
                         
                         window.scrollTo({
                             top: targetScrollY,
                             behavior: 'smooth'
                         });
                     });
                 });
             },
             
             isTabletRowActive(endIndex) {
                 const startIndex = endIndex - (endIndex % 2);
                 const rowIds = this.pagedArticles.slice(startIndex, endIndex + 1).map(a => a.id);
                 return rowIds.includes(this.activeKnowledgeId);
             },
             
             isDesktopRowActive(endIndex) {
                 const startIndex = endIndex - (endIndex % 3);
                 const rowIds = this.pagedArticles.slice(startIndex, endIndex + 1).map(a => a.id);
                 return rowIds.includes(this.activeKnowledgeId);
             },
             
             setPage(page) {
                 if (page >= 1 && page <= this.totalPages) {
                     this.activeKnowledgeId = null;
                     this.currentPage = page;
                     const el = document.getElementById('knowledge');
                     if (el) {
                         el.scrollIntoView({ behavior: 'smooth' });
                     }
                 }
             }
         }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-3">
                {{ $knowledgeSection['label'] ?? 'Edukasi & Inspirasi Dapur' }}
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-brand-dark tracking-tight mb-3">
                {{ $knowledgeSection['title'] ?? 'Dapur & Knowledge' }}
            </h2>
            <p class="text-sm sm:text-base text-gray-600 font-normal leading-relaxed">
                {{ $knowledgeSection['subtitle'] ?? 'Panduan praktis seputar penanganan daging, thawing, penyimpanan frozen food, hingga tips memasak harian keluarga di Yogyakarta.' }}
            </p>
        </div>

        <!-- Knowledge Grid: Desktop 3 cols, Tablet/Landscape 2 cols, Mobile Portrait 1 col -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 items-start">
            
            <template x-for="(art, index) in pagedArticles" :key="art.id">
                <div class="contents">
                    
                    <!-- 1. Collapsed Article Card -->
                    <article :id="'knowledge-card-' + art.id"
                             class="group bg-white rounded-modern-lg overflow-hidden border transition-all duration-300 flex flex-col justify-between h-full scroll-mt-24"
                             :class="activeKnowledgeId === art.id 
                                 ? 'ring-2 ring-brand-primary border-brand-primary/60 shadow-md bg-brand-cream/30' 
                                 : 'border-gray-100 shadow-sm hover:shadow-card-hover hover:border-gray-200'">
                        
                        <!-- Thumbnail Image -->
                        <div class="relative aspect-[16/9] w-full overflow-hidden bg-gray-100">
                            <img :src="art.image" 
                                 :alt="art.title" 
                                 loading="lazy"
                                 class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500 ease-out">
                            
                            <div class="absolute top-3 left-3">
                                <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-semibold shadow-xs"
                                      :class="art.badge_class"
                                      x-text="art.category">
                                </span>
                            </div>

                            <div class="absolute bottom-3 right-3">
                                <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-medium bg-black/60 backdrop-blur-xs text-white"
                                      x-text="art.read_time">
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5 sm:p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-brand-dark group-hover:text-brand-primary transition-colors line-clamp-2 mb-2 leading-snug"
                                    x-text="art.title">
                                </h3>
                                <p class="text-xs sm:text-sm text-gray-500 line-clamp-3 leading-relaxed mb-4"
                                   x-text="art.excerpt">
                                </p>
                            </div>

                            <!-- Toggle Button ("Baca Selengkapnya" / "Tutup") -->
                            <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                                <button @click="toggleArticle(art.id)"
                                        type="button"
                                        :aria-expanded="activeKnowledgeId === art.id"
                                        :aria-label="activeKnowledgeId === art.id ? 'Tutup artikel ' + art.title : 'Baca selengkapnya ' + art.title"
                                        class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-primary/40 rounded-sm"
                                        :class="activeKnowledgeId === art.id ? 'text-brand-primary-dark font-bold' : 'text-brand-primary hover:text-brand-primary-dark'">
                                    <span x-text="activeKnowledgeId === art.id ? 'Tutup Artikel' : 'Baca Selengkapnya'"></span>
                                    <svg class="w-4 h-4 transition-transform duration-200"
                                         :class="activeKnowledgeId === art.id ? 'rotate-180 text-brand-primary' : 'group-hover:translate-x-1'"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                
                                <span x-show="activeKnowledgeId === art.id" x-cloak class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Sedang Dibuka</span>
                                </span>
                            </div>
                        </div>
                    </article>

                    <!-- 2. Mobile Portrait Expanded Reader (1 Column - Immediately after clicked card) -->
                    <div x-show="activeKnowledgeId === art.id"
                         x-cloak
                         x-transition:enter="transition ease-out duration-250 transform"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-[0.99]"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="col-span-1 block sm:hidden w-full my-2">
                        
                        <div class="bg-[#FCFBF8] border-2 border-brand-primary/30 rounded-modern-lg p-5 sm:p-6 shadow-md relative overflow-hidden">
                            <!-- Top Accent Line -->
                            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-brand-primary via-emerald-500 to-brand-accent"></div>
                            
                            <!-- Header Info -->
                            <div class="flex items-center justify-between gap-3 pt-2 mb-3 pb-3 border-b border-gray-200/80">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                          :class="getActiveArticle()?.badge_class"
                                          x-text="getActiveArticle()?.category"></span>
                                    <span class="text-[10px] text-gray-500 font-medium" x-text="getActiveArticle()?.read_time"></span>
                                </div>
                                <button @click="activeKnowledgeId = null" 
                                        type="button" 
                                        aria-label="Tutup artikel"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 transition-colors cursor-pointer">
                                    <span>Tutup</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Title -->
                            <h3 class="text-lg font-extrabold text-brand-dark tracking-tight mb-4 leading-snug"
                                x-text="getActiveArticle()?.title"></h3>

                            <!-- Article HTML Body -->
                            <div class="text-xs text-gray-700 leading-relaxed space-y-3"
                                 x-html="getActiveArticle()?.content"></div>

                            <!-- Reader Bottom Actions -->
                            <div class="mt-6 pt-4 border-t border-gray-200 flex items-center justify-between gap-3">
                                <button @click="activeKnowledgeId = null" 
                                        type="button"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-modern text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer">
                                    <span>Tutup Artikel</span>
                                </button>
                                <a href="https://wa.me/6281234567890?text=Halo%20Sumber%20Protein%20Jogja,%20saya%20mau%20tanya%20seputar%20tips%20bahan%20makanan" 
                                   target="_blank" 
                                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-modern text-xs font-bold text-white bg-brand-primary hover:bg-brand-primary-dark transition-colors">
                                    <span>Tanya Tim Kami</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Tablet / Mobile Landscape Expanded Reader (2 Columns - Appears after 2-item row) -->
                    <template x-if="(index % 2 === 1) || (index === pagedArticles.length - 1 && pagedArticles.length % 2 !== 0)">
                        <div x-show="isTabletRowActive(index)"
                             x-cloak
                             x-transition:enter="transition ease-out duration-250 transform"
                             x-transition:enter-start="opacity-0 -translate-y-2 scale-[0.99]"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             class="col-span-1 sm:col-span-2 hidden sm:block lg:hidden w-full my-3">
                            
                            <div class="bg-[#FCFBF8] border-2 border-brand-primary/30 rounded-modern-xl p-6 sm:p-8 shadow-lg shadow-brand-dark/5 relative overflow-hidden">
                                <!-- Top Accent Line -->
                                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-brand-primary via-emerald-500 to-brand-accent"></div>
                                
                                <!-- Header Info -->
                                <div class="flex items-center justify-between gap-4 pt-2 mb-4 pb-3 border-b border-gray-200/80">
                                    <div class="flex items-center gap-2.5">
                                        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold shadow-xs"
                                              :class="getActiveArticle()?.badge_class"
                                              x-text="getActiveArticle()?.category"></span>
                                        <span class="text-xs text-gray-500 font-medium" x-text="getActiveArticle()?.read_time"></span>
                                    </div>
                                    <button @click="activeKnowledgeId = null" 
                                            type="button" 
                                            aria-label="Tutup artikel"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 shadow-2xs transition-colors cursor-pointer">
                                        <span>Tutup Artikel</span>
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <!-- Title -->
                                <h3 class="text-xl sm:text-2xl font-extrabold text-brand-dark tracking-tight mb-5 leading-tight"
                                    x-text="getActiveArticle()?.title"></h3>

                                <!-- Article HTML Body -->
                                <div class="text-sm text-gray-700 leading-relaxed space-y-4"
                                     x-html="getActiveArticle()?.content"></div>

                                <!-- Reader Bottom Actions -->
                                <div class="mt-8 pt-5 border-t border-gray-200 flex flex-row items-center justify-between gap-4">
                                    <button @click="activeKnowledgeId = null" 
                                            type="button"
                                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern text-xs sm:text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer">
                                        <span>Tutup Artikel</span>
                                    </button>
                                    <a href="https://wa.me/6281234567890?text=Halo%20Sumber%20Protein%20Jogja,%20saya%20mau%20tanya%20seputar%20tips%20bahan%20makanan" 
                                       target="_blank" 
                                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern text-xs sm:text-sm font-bold text-white bg-brand-primary hover:bg-brand-primary-dark transition-colors shadow-sm">
                                        <span>Konsultasi Produk via WhatsApp</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 4. Desktop Expanded Reader (3 Columns - Appears after 3-item row) -->
                    <template x-if="(index % 3 === 2) || (index === pagedArticles.length - 1 && pagedArticles.length % 3 !== 0)">
                        <div x-show="isDesktopRowActive(index)"
                             x-cloak
                             x-transition:enter="transition ease-out duration-250 transform"
                             x-transition:enter-start="opacity-0 -translate-y-2 scale-[0.99]"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             class="col-span-1 sm:col-span-2 lg:col-span-3 hidden lg:block w-full my-4">
                            
                            <div class="bg-[#FCFBF8] border-2 border-brand-primary/30 rounded-modern-xl p-8 lg:p-10 shadow-lg shadow-brand-dark/5 relative overflow-hidden">
                                <!-- Top Accent Line -->
                                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-brand-primary via-emerald-500 to-brand-accent"></div>
                                
                                <!-- Header Info -->
                                <div class="flex items-center justify-between gap-4 pt-2 mb-4 pb-4 border-b border-gray-200/80">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-block px-3.5 py-1 rounded-full text-xs font-bold shadow-xs"
                                              :class="getActiveArticle()?.badge_class"
                                              x-text="getActiveArticle()?.category"></span>
                                        <span class="text-xs text-gray-500 font-medium" x-text="getActiveArticle()?.read_time"></span>
                                        <span class="inline-flex items-center gap-1 text-xs text-emerald-700 font-semibold bg-emerald-50 px-2.5 py-0.5 rounded-md border border-emerald-200/60">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <span>Artikel Lengkap</span>
                                        </span>
                                    </div>
                                    <button @click="activeKnowledgeId = null" 
                                            type="button" 
                                            aria-label="Tutup artikel"
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 shadow-2xs transition-colors cursor-pointer">
                                        <span>Tutup Artikel</span>
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <!-- Title -->
                                <h3 class="text-2xl lg:text-3xl font-extrabold text-brand-dark tracking-tight mb-6 leading-tight"
                                    x-text="getActiveArticle()?.title"></h3>

                                <!-- Article HTML Body -->
                                <div class="text-sm sm:text-base text-gray-700 leading-relaxed space-y-4 max-w-4xl"
                                     x-html="getActiveArticle()?.content"></div>

                                <!-- Reader Bottom Actions -->
                                <div class="mt-8 pt-6 border-t border-gray-200 flex flex-row items-center justify-between gap-4">
                                    <button @click="activeKnowledgeId = null" 
                                            type="button"
                                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-modern text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer">
                                        <span>Tutup Artikel</span>
                                    </button>
                                    <div class="flex items-center gap-3 text-xs sm:text-sm text-gray-500">
                                        <span>Butuh rekomendasi bahan untuk resep di atas?</span>
                                        <a href="https://wa.me/6281234567890?text=Halo%20Sumber%20Protein%20Jogja,%20saya%20mau%20konsultasi%20bahan%20masak" 
                                           target="_blank" 
                                           class="inline-flex items-center gap-2 px-4 py-2 rounded-modern font-bold text-white bg-[#25D366] hover:bg-[#1EBE5D] transition-colors shadow-xs">
                                            <span>Chat WhatsApp</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                </div>
            </template>

        </div>

        <!-- 5. Client-Side Pagination (Visible when totalArticles > pageSize) -->
        <div x-show="totalPages > 1" class="mt-12 sm:mt-16 pt-8 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <!-- Counter Info -->
            <p class="text-xs sm:text-sm text-gray-500 font-medium">
                Menampilkan <span class="font-bold text-brand-dark" x-text="((currentPage - 1) * pageSize) + 1"></span>–<span class="font-bold text-brand-dark" x-text="Math.min(currentPage * pageSize, totalArticles)"></span> dari <span class="font-bold text-brand-dark" x-text="totalArticles"></span> artikel edukasi
            </p>

            <!-- Page Buttons -->
            <div class="flex items-center gap-2">
                <!-- Prev Page -->
                <button @click="setPage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        type="button"
                        aria-label="Halaman sebelumnya"
                        class="p-2 rounded-modern border text-sm font-semibold transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
                        :class="currentPage === 1 ? 'border-gray-200 text-gray-400' : 'border-gray-300 text-brand-dark hover:bg-gray-50'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>

                <!-- Numbered Pages -->
                <template x-for="p in totalPages" :key="p">
                    <button @click="setPage(p)"
                            type="button"
                            :aria-label="'Halaman ' + p"
                            :aria-current="currentPage === p ? 'page' : false"
                            class="w-9 h-9 rounded-modern text-xs sm:text-sm font-bold transition-all cursor-pointer flex items-center justify-center"
                            :class="currentPage === p 
                                ? 'bg-brand-primary text-white shadow-xs' 
                                : 'bg-white border border-gray-200 text-brand-dark hover:bg-gray-50 hover:border-gray-300'">
                        <span x-text="p"></span>
                    </button>
                </template>

                <!-- Next Page -->
                <button @click="setPage(currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        type="button"
                        aria-label="Halaman berikutnya"
                        class="p-2 rounded-modern border text-sm font-semibold transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
                        :class="currentPage === totalPages ? 'border-gray-200 text-gray-400' : 'border-gray-300 text-brand-dark hover:bg-gray-50'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

    </div>
</section>
