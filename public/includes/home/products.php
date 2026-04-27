<section id="gallery" class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-800 mb-4">Our Products</h2>
        </div>

        <?php if (!empty($products)): ?>
            <div class="grid grid-cols-2 md:grid-cols-2 gap-4 md:gap-8 max-w-[90rem] mx-auto">
                <?php foreach ($products as $p): ?>
                    <?php $img = !empty($p['hero_image']) ? '/uploads/' . $p['hero_image'] : '/uploads/product1.jpg'; ?>
                    <div class="group cursor-pointer">
                        <div class="relative overflow-hidden rounded-2xl shadow-lg">
                            <img src="<?php echo h($img); ?>" alt="<?php echo h($p['name'] ?? ''); ?>" class="w-full h-48 md:h-120 object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute inset-0 flex items-end">
                                <div class="p-3 md:p-6 text-white drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)]">
                                    <h3 class="text-base md:text-2xl font-bold mb-1 md:mb-2"><?php echo h($p['name'] ?? ''); ?></h3>
                                    <p class="text-xs md:text-sm hidden md:block"><?php echo h($p['short_description'] ?? ''); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center text-gray-500">ยังไม่มีสินค้าในระบบ</div>
        <?php endif; ?>
    </div>
</section>
