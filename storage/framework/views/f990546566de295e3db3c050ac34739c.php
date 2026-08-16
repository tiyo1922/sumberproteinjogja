<section id="tentang" class="py-16 sm:py-24 bg-brand-cream/60 border-t border-gray-200/60 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary mb-3">
                Standar Mutu
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-brand-dark tracking-tight mb-3">
                Mengenal Standar Produk Kami
            </h2>
            <p class="text-sm sm:text-base text-gray-600 font-normal">
                Setiap produk yang keluar dari fasilitas penyimpanan Sumber Protein Jogja melewati proses seleksi ketat untuk menjamin keamanan pangan keluarga Anda.
            </p>
        </div>

        <!-- 4 Categories In-depth Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
            <?php $__currentLoopData = $productKnowledge; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white p-6 sm:p-8 rounded-modern-lg border border-gray-100 shadow-sm hover:shadow-card transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg sm:text-xl font-bold text-brand-dark">
                            <?php echo e($pk['name']); ?>

                        </h3>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-brand-soft-green text-brand-primary">
                            <?php echo e($pk['tag']); ?>

                        </span>
                    </div>

                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed mb-6">
                        <?php echo e($pk['desc']); ?>

                    </p>

                    <!-- Features Checklist -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-2.5 mb-6">
                        <?php $__currentLoopData = $pk['features']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-xs font-medium text-brand-dark"><?php echo e($feat); ?></span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <!-- CTA Link -->
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                    <a href="https://wa.me/6281234567890?text=Halo%20Sumber%20Protein%20Jogja,%20saya%20ingin%20tanya%20detail%20kategori%20<?php echo e(urlencode($pk['name'])); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-brand-primary hover:text-brand-primary-dark transition-colors">
                        <span>Konsultasi Produk <?php echo e($pk['name']); ?></span>
                        <span>→</span>
                    </a>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

    </div>
</section>
<?php /**PATH C:\xampp\htdocs\sumberproteinjogja\resources\views/components/product-knowledge.blade.php ENDPATH**/ ?>