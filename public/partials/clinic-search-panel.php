<?php
$clinicSearchTitleTag = $clinicSearchTitleTag ?? 'h2';
$search = $search ?? '';
$province = $province ?? '';
$selectedProductIds = $selectedProductIds ?? [];
$products = $products ?? [];
$provinces = $provinces ?? [];
$dbError = $dbError ?? '';
?>
<div class="max-w-2xl mx-auto">
    <?php if ($clinicSearchTitleTag === 'h1'): ?>
        <h1 class="text-2xl md:text-3xl font-bold text-center mb-6 text-indigo-800">ค้นหาคลินิกใกล้คุณ</h1>
    <?php else: ?>
        <h2 class="text-2xl md:text-3xl font-bold text-center mb-6 text-indigo-800">ค้นหาคลินิกใกล้คุณ</h2>
    <?php endif; ?>

    <?php if ($dbError !== ''): ?>
        <div class="mb-4 rounded-xl border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-900">
            ไม่สามารถเชื่อมต่อฐานข้อมูลได้: <?php echo h($dbError); ?>
        </div>
    <?php endif; ?>

    <form action="/searchpage.php" method="GET" class="space-y-4">
        <input type="hidden" name="submitted" value="1">
        <div>
            <label for="search" class="block text-sm font-medium text-slate-700 mb-1.5">ชื่อคลินิก</label>
            <input
                id="search"
                type="text"
                name="search"
                value="<?php echo h($search); ?>"
                placeholder="ค้นหาชื่อคลินิก เขต หรือที่อยู่"
                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >
        </div>

        <div>
            <div class="block text-sm font-medium text-slate-700 mb-2">โปรดัก</div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                <?php foreach ($products as $product): ?>
                    <?php $productId = (int) ($product['id'] ?? 0); ?>
                    <label class="flex items-center gap-2 text-sm rounded-lg border border-slate-200 px-3 py-2 cursor-pointer hover:border-indigo-300 hover:bg-indigo-50">
                        <input
                            type="checkbox"
                            name="products[]"
                            value="<?php echo $productId; ?>"
                            class="w-3.5 h-3.5 rounded"
                            style="accent-color:#4B4899;"
                            <?php echo in_array($productId, $selectedProductIds, true) ? 'checked' : ''; ?>
                        >
                        <span><?php echo h($product['name'] ?? ''); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <label for="province" class="block text-sm font-medium text-slate-700 mb-1.5">จังหวัด</label>
            <select
                id="province"
                name="province"
                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg text-white"
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

        <div class="grid grid-cols-2 gap-3">
            <button
                type="button"
                onclick="window.location.href='/searchpage.php'"
                class="w-full py-2.5 text-sm border-2 border-indigo-600 text-indigo-600 rounded-lg font-medium hover:bg-indigo-50 transition"
            >
                คืนค่า
            </button>
            <button
                type="submit"
                class="w-full py-2.5 text-sm bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition"
            >
                ค้นหา
            </button>
        </div>
    </form>
</div>
