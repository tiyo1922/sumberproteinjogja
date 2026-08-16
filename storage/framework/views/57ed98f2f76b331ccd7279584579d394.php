<section id="knowledge" class="py-16 sm:py-24 bg-white relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-3">
                Edukasi & Inspirasi
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-brand-dark tracking-tight mb-3">
                Dapur & Knowledge
            </h2>
            <p class="text-sm sm:text-base text-gray-600 font-normal">
                Tips memilih bahan, menyimpan frozen food, sampai inspirasi masakan sehari-hari untuk keluarga Anda.
            </p>
        </div>

        <!-- Knowledge Articles Grid: Desktop 3 cols, Tablet 2 cols, Mobile 1 col -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <?php $__currentLoopData = $knowledgeArticles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $art): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <article class="group bg-white rounded-modern-lg overflow-hidden border border-gray-100 shadow-sm hover:shadow-card-hover transition-all duration-300 flex flex-col justify-between">
                
                <!-- Article Image Container -->
                <div class="relative aspect-[16/9] w-full overflow-hidden bg-gray-100">
                    <img src="<?php echo e(asset($art['image'])); ?>" 
                         alt="<?php echo e($art['title']); ?>" 
                         loading="lazy"
                         class="w-full h-full object-cover object-center group-hover:scale-106 transition-transform duration-500 ease-out">
                    
                    <div class="absolute top-3 left-3">
                        <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-semibold <?php echo e($art['badge_class']); ?> shadow-sm">
                            <?php echo e($art['category']); ?>

                        </span>
                    </div>

                    <div class="absolute bottom-3 right-3">
                        <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-medium bg-black/60 backdrop-blur-md text-white">
                            <?php echo e($art['read_time']); ?>

                        </span>
                    </div>
                </div>

                <!-- Article Content -->
                <div class="p-5 sm:p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-brand-dark group-hover:text-brand-primary transition-colors line-clamp-2 mb-2 leading-snug">
                            <?php echo e($art['title']); ?>

                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 line-clamp-3 leading-relaxed mb-4">
                            <?php echo e($art['excerpt']); ?>

                        </p>
                    </div>

                    <!-- Read More Link -->
                    <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs sm:text-sm font-semibold text-brand-primary group-hover:text-brand-primary-dark">
                        <span>Baca Selengkapnya</span>
                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                    </div>
                </div>

                <!-- Clickable Layer -->
                <a href="#knowledge" 
                   class="absolute inset-0 z-10 focus:outline-none focus:ring-2 focus:ring-brand-primary rounded-modern-lg" 
                   aria-label="Baca artikel <?php echo e($art['title']); ?>"></a>
            </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

    </div>
</section>
<?php /**PATH C:\xampp\htdocs\sumberproteinjogja\resources\views/components/knowledge-card.blade.php ENDPATH**/ ?>