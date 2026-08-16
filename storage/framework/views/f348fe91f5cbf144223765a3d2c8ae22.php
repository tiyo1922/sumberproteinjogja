<section id="kategori" class="py-16 sm:py-24 bg-brand-cream/60 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-16">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-3">
                Kategori Utama
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-brand-dark tracking-tight mb-3">
                Mau Masak Apa Hari Ini?
            </h2>
            <p class="text-sm sm:text-base text-gray-600 font-normal">
                Pilih bahan masak sesuai kebutuhanmu. Dari potongan daging segar, ayam bumbu, ikan laut, hingga sayuran siap cemplung.
            </p>
        </div>

        <!-- Categories Grid: Desktop 4 cols, Tablet 2x2, Mobile 2x2 -->
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 lg:gap-8">
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="group relative bg-white rounded-modern-lg overflow-hidden border border-gray-100 shadow-sm hover:shadow-card-hover transition-all duration-300 flex flex-col justify-between">
                
                <!-- Category Image Container -->
                <div class="relative aspect-[4/3] w-full overflow-hidden bg-gray-100">
                    <img src="<?php echo e(asset($cat['image'])); ?>" 
                         alt="<?php echo e($cat['name']); ?> - Sumber Protein Jogja" 
                         loading="lazy"
                         class="w-full h-full object-cover object-center group-hover:scale-108 transition-transform duration-500 ease-out">
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-60 group-hover:opacity-40 transition-opacity"></div>
                    
                    <!-- Top Badge -->
                    <div class="absolute top-2.5 left-2.5 sm:top-3 sm:left-3">
                        <span class="inline-block px-2.5 py-1 rounded-full text-[10px] sm:text-xs font-semibold bg-white/90 backdrop-blur-md text-brand-dark shadow-sm">
                            <?php echo e($cat['badge']); ?>

                        </span>
                    </div>

                    <!-- Bottom Count Badge -->
                    <div class="absolute bottom-2.5 right-2.5 sm:bottom-3 sm:right-3">
                        <span class="inline-block px-2 py-0.5 rounded-md text-[10px] sm:text-xs font-semibold bg-brand-primary text-white shadow-sm">
                            <?php echo e($cat['count']); ?>

                        </span>
                    </div>
                </div>

                <!-- Category Content -->
                <div class="p-3.5 sm:p-5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm sm:text-base lg:text-lg font-bold text-brand-dark group-hover:text-brand-primary transition-colors mb-1 leading-snug">
                            <?php echo e($cat['name']); ?>

                        </h3>
                        <p class="text-[11px] sm:text-xs md:text-sm font-medium text-brand-primary-light mb-1.5 line-clamp-1">
                            <?php echo e($cat['subtitle']); ?>

                        </p>
                        <p class="text-[11px] sm:text-xs text-gray-500 line-clamp-2 leading-relaxed">
                            <?php echo e($cat['description']); ?>

                        </p>
                    </div>

                    <!-- Action Link -->
                    <div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3 border-t border-gray-100 flex items-center justify-between text-xs sm:text-sm font-semibold text-brand-primary group-hover:text-brand-primary-dark">
                        <span>Lihat Varian</span>
                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                    </div>
                </div>

                <!-- Clickable Overlay Link to filter catalog -->
                <a href="#produk" 
                   @click="$dispatch('filter-category', { category: '<?php echo e($cat['id']); ?>' })"
                   class="absolute inset-0 z-10 focus:outline-none focus:ring-2 focus:ring-brand-primary rounded-modern-lg"
                   aria-label="Lihat produk kategori <?php echo e($cat['name']); ?>">
                </a>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

    </div>
</section>
<?php /**PATH C:\xampp\htdocs\sumberproteinjogja\resources\views/components/category-card.blade.php ENDPATH**/ ?>