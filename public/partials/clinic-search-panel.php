<?php
$clinicSearchTitleTag = $clinicSearchTitleTag ?? 'h2';
$search = $search ?? '';
$province = $province ?? '';
$selectedProductIds = $selectedProductIds ?? [];
$products = $products ?? [];
$provinces = $provinces ?? [];
$dbError = $dbError ?? '';
?>
<div class="max-w-4xl mx-auto">
    <?php if ($clinicSearchTitleTag === 'h1'): ?>
        <h1 class="text-3xl md:text-4xl font-bold text-center mb-4 text-indigo-800">ค้นหาคลินิกใกล้คุณ</h1>
    <?php else: ?>
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-4 text-indigo-800">ค้นหาคลินิกใกล้คุณ</h2>
    <?php endif; ?>

    <p class="text-center text-slate-500 mb-8">กรองข้อมูลจากฐานข้อมูลตามชื่อคลินิก จังหวัด และโปรดักที่คลินิกให้บริการ</p>

    <?php if ($dbError !== ''): ?>
        <div class="mb-6 rounded-2xl border border-yellow-300 bg-yellow-50 px-5 py-4 text-yellow-900">
            ไม่สามารถเชื่อมต่อฐานข้อมูลได้: <?php echo h($dbError); ?>
        </div>
    <?php endif; ?>

    <form action="/searchpage.php" method="GET" class="space-y-6 bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
        <input type="hidden" name="submitted" value="1">
        <div>
            <label for="search" class="block text-sm font-medium text-slate-700 mb-2">ชื่อคลินิก</label>
            <input
                id="search"
                type="text"
                name="search"
                value="<?php echo h($search); ?>"
                placeholder="ค้นหาชื่อคลินิก เขต หรือที่อยู่"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >
        </div>

        <div>
            <div class="block text-sm font-medium text-slate-700 mb-3">โปรดัก</div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <?php foreach ($products as $product): ?>
                    <?php $productId = (int) ($product['id'] ?? 0); ?>
                    <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-3 cursor-pointer hover:border-indigo-300 hover:bg-indigo-50">
                        <input
                            type="checkbox"
                            name="products[]"
                            value="<?php echo $productId; ?>"
                            class="w-4 h-4 rounded"
                            style="accent-color:#4B4899;"
                            <?php echo in_array($productId, $selectedProductIds, true) ? 'checked' : ''; ?>
                        >
                        <span><?php echo h($product['name'] ?? ''); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <label for="province" class="block text-sm font-medium text-slate-700 mb-2">จังหวัด</label>
            <select
                id="province"
                name="province"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl text-white"
                style="background-color:#4b4899;"
            >
                <option value="">เลือกจังหวัดทั้งหมด</option>
                <?php foreach ($provinces as $provinceName): ?>
                    <option value="<?php echo h($provinceName); ?>" <?php echo $province === $provinceName ? 'selected' : ''; ?>>
                        <?php echo h($provinceName); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <button
                type="button"
                onclick="window.location.href='/searchpage.php'"
                class="w-full py-3 border-2 border-indigo-600 text-indigo-600 rounded-xl font-medium hover:bg-indigo-50 transition"
            >
                ล้างค่า
            </button>
            <button
                type="submit"
                class="w-full py-3 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition"
            >
                ค้นหา
            </button>
        </div>
    </form>
</div>
