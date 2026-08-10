<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

function clinic_media_url(?string $value, string $folder = 'clinics', string $fallback = ''): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return $fallback;
    }

    if (preg_match('#^https?://#i', $value) || str_starts_with($value, '/')) {
        return $value;
    }

    return '/uploads/' . trim($folder, '/') . '/' . rawurlencode($value);
}

$search = trim((string) ($_GET['search'] ?? $_GET['q'] ?? ''));
$province = trim((string) ($_GET['province'] ?? ''));
$searchSubmitted = isset($_GET['submitted']) && $_GET['submitted'] === '1';
$selectedProductInputs = array_values(array_filter(array_map(
    static fn ($value) => trim((string) $value),
    (array) ($_GET['products'] ?? [])
), static fn ($value) => $value !== ''));
$selectedProductIds = [];

$legacyProductId = (int) ($_GET['product_id'] ?? 0);
if ($legacyProductId > 0 && !in_array($legacyProductId, $selectedProductIds, true)) {
    $selectedProductIds[] = $legacyProductId;
}

$dbReady = $pdo instanceof PDO;
$dbError = '';
$products = [];
$provinces = [];
$clinics = [];
$clinicProducts = [];

if ($dbReady) {
    try {
        $products = $pdo->query(
            'SELECT id, name
             FROM products
             WHERE is_active = 1
             ORDER BY name ASC'
        )->fetchAll();

        $productNameMap = [];
        foreach ($products as $product) {
            $productId = (int) ($product['id'] ?? 0);
            $productName = trim((string) ($product['name'] ?? ''));
            if ($productId <= 0 || $productName === '') {
                continue;
            }

            $productNameMap[strtolower($productName)] = $productId;
        }

        foreach ($selectedProductInputs as $productInput) {
            if (ctype_digit($productInput)) {
                $productId = (int) $productInput;
                if ($productId > 0) {
                    $selectedProductIds[] = $productId;
                }
                continue;
            }

            $lookupKey = strtolower($productInput);
            if (isset($productNameMap[$lookupKey])) {
                $selectedProductIds[] = $productNameMap[$lookupKey];
            }
        }

        $selectedProductIds = array_values(array_unique(array_filter($selectedProductIds, static fn ($id) => $id > 0)));

        $provinces = $pdo->query(
            "SELECT DISTINCT province
             FROM clinics
             WHERE is_active = 1
               AND province IS NOT NULL
               AND province <> ''
             ORDER BY province ASC"
        )->fetchAll(PDO::FETCH_COLUMN);

        if ($searchSubmitted) {
            $params = [];
            $joinClause = '';
            $where = ['c.is_active = 1'];

            if ($search !== '') {
                $where[] = '(c.name LIKE ? OR c.address LIKE ? OR c.district LIKE ?)';
                $searchLike = '%' . $search . '%';
                $params[] = $searchLike;
                $params[] = $searchLike;
                $params[] = $searchLike;
            }

            if ($province !== '') {
                $where[] = 'c.province = ?';
                $params[] = $province;
            }

            if (!empty($selectedProductIds)) {
                $placeholders = implode(',', array_fill(0, count($selectedProductIds), '?'));
                $joinClause .= ' INNER JOIN clinic_products cp_filter ON cp_filter.clinic_id = c.id';
                $where[] = 'cp_filter.product_id IN (' . $placeholders . ')';
                foreach ($selectedProductIds as $productId) {
                    $params[] = $productId;
                }
            }

            $sql = '
                SELECT DISTINCT c.*
                FROM clinics c
                ' . $joinClause . '
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY c.name ASC, c.id DESC
            ';

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $clinics = $stmt->fetchAll();
        }

        if (!empty($clinics)) {
            $clinicIds = array_column($clinics, 'id');
            $placeholders = implode(',', array_fill(0, count($clinicIds), '?'));

            $stmt = $pdo->prepare(
                'SELECT cp.clinic_id, p.name, p.logo_image
                 FROM clinic_products cp
                 INNER JOIN products p ON p.id = cp.product_id
                 WHERE cp.clinic_id IN (' . $placeholders . ')
                   AND p.is_active = 1
                 ORDER BY p.name ASC'
            );
            $stmt->execute($clinicIds);

            foreach ($stmt->fetchAll() as $row) {
                $clinicId = (int) ($row['clinic_id'] ?? 0);
                if (!isset($clinicProducts[$clinicId])) {
                    $clinicProducts[$clinicId] = [];
                }
                $clinicProducts[$clinicId][] = [
                    'name' => (string) ($row['name'] ?? ''),
                    'logo' => clinic_media_url($row['logo_image'] ?? '', 'products'),
                ];
            }
        }
    } catch (Exception $e) {
        $dbError = $e->getMessage();
    }
}

$hasFilters = $search !== '' || $province !== '' || !empty($selectedProductInputs) || $legacyProductId > 0;
$siteHeaderActive = 'clinic';
?>
<!doctype html>
<html lang="th">
<head>
    <?php $page_title = 'KNA Interpharma - Find A Clinic'; require_once __DIR__ . '/partials/site-head.php'; ?>
</head>
<body class="bg-gray-50 text-gray-800">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>
<section class="py-16 bg-gradient-to-b from-blue-50 to-white">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <?php $clinicSearchTitleTag = 'h1'; ?>
            <?php require __DIR__ . '/partials/clinic-search-panel.php'; ?>
            <?php if (false): ?>
            <div class="max-w-4xl mx-auto">
                <h1 class="text-3xl md:text-4xl font-bold text-center mb-4 text-indigo-800">ค้นหาคลินิกใกล้คุณ</h1>
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
            <?php endif; ?>

            <div class="mt-10">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">ผลการค้นหา</h2>
                        <p class="text-slate-500">
                            <?php if (!$dbReady || $dbError !== ''): ?>
                                กรุณาตรวจสอบการเชื่อมต่อฐานข้อมูล
                            <?php elseif ($searchSubmitted): ?>
                                พบ <?php echo number_format(count($clinics)); ?> คลินิก
                            <?php else: ?>
                                กดค้นหาเพื่อแสดงรายการคลินิก
                            <?php endif; ?>
                        </p>
                    </div>

                    <?php if ($searchSubmitted && $hasFilters): ?>
                        <div class="flex flex-wrap gap-2">
                            <?php if ($search !== ''): ?>
                                <span class="rounded-full bg-indigo-50 text-indigo-700 px-3 py-1 text-sm">ชื่อ: <?php echo h($search); ?></span>
                            <?php endif; ?>
                            <?php if ($province !== ''): ?>
                                <span class="rounded-full bg-indigo-50 text-indigo-700 px-3 py-1 text-sm">จังหวัด: <?php echo h($province); ?></span>
                            <?php endif; ?>
                            <?php foreach ($products as $product): ?>
                                <?php if (in_array((int) $product['id'], $selectedProductIds, true)): ?>
                                    <span class="rounded-full bg-slate-100 text-slate-700 px-3 py-1 text-sm"><?php echo h($product['name'] ?? ''); ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($dbReady && $dbError === '' && !$searchSubmitted): ?>
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                        ยังไม่ได้แสดงผลคลินิก กรุณาเลือกเงื่อนไขแล้วกดค้นหา
                    </div>
                <?php endif; ?>

                <?php if ($dbReady && $dbError === '' && $searchSubmitted && empty($clinics)): ?>
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                        ไม่พบคลินิกที่ตรงกับเงื่อนไขที่เลือก
                    </div>
                <?php endif; ?>

                <?php if ($searchSubmitted && !empty($clinics)): ?>
                    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        <?php foreach ($clinics as $clinic): ?>
                            <?php
                            $clinicId = (int) ($clinic['id'] ?? 0);
                            $clinicProductList = $clinicProducts[$clinicId] ?? [];
                            $logoImage = clinic_media_url($clinic['logo_image'] ?? '', 'clinics');
                            ?>
                            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                                <div class="p-6">
                                    <div class="flex items-start gap-4 mb-4">
                                        <?php if ($logoImage !== ''): ?>
                                            <img src="<?php echo h($logoImage); ?>" alt="<?php echo h($clinic['name'] ?? 'Clinic Logo'); ?>" class="h-20 w-20 shrink-0 rounded-2xl object-cover border border-slate-200">
                                        <?php else: ?>
                                            <div class="h-20 w-20 shrink-0 rounded-2xl bg-indigo-50 text-indigo-700 flex items-center justify-center border border-indigo-100">
                                                <i class="fa-solid fa-hospital text-2xl"></i>
                                            </div>
                                        <?php endif; ?>

                                        <div class="min-w-0">
                                            <h3 class="text-xl font-semibold text-slate-900"><?php echo h($clinic['name'] ?? ''); ?></h3>
                                            <p class="text-sm text-slate-500">
                                                <?php echo h(trim(($clinic['district'] ?? '') . ' ' . ($clinic['province'] ?? ''))); ?>
                                            </p>
                                        </div>
                                    </div>

                                    <?php if (!empty($clinicProductList)): ?>
                                        <div class="flex flex-wrap gap-2 mb-4">
                                            <?php foreach ($clinicProductList as $clinicProduct): ?>
                                                <?php if (!empty($clinicProduct['logo'])): ?>
                                                    <span class="inline-flex items-center justify-center h-11 w-32 rounded-xl bg-white border border-slate-200 shadow-sm px-2" title="<?php echo h($clinicProduct['name']); ?>">
                                                        <img src="<?php echo h($clinicProduct['logo']); ?>" alt="<?php echo h($clinicProduct['name']); ?>" class="max-h-8 max-w-full object-contain">
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center justify-center h-11 w-32 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 px-2 text-sm font-medium truncate"><?php echo h($clinicProduct['name']); ?></span>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="space-y-3 text-sm text-slate-600">
                                        <?php if (!empty($clinic['address'])): ?>
                                            <p class="flex items-start gap-3">
                                                <i class="fa-solid fa-location-dot mt-1 text-indigo-600"></i>
                                                <span><?php echo nl2br(h($clinic['address'])); ?></span>
                                            </p>
                                        <?php endif; ?>

                                        <?php if (!empty($clinic['phone'])): ?>
                                            <p class="flex items-center gap-3">
                                                <i class="fa-solid fa-phone text-indigo-600"></i>
                                                <a href="tel:<?php echo h($clinic['phone']); ?>" class="hover:text-indigo-700"><?php echo h($clinic['phone']); ?></a>
                                            </p>
                                        <?php endif; ?>

                                        <?php if (!empty($clinic['open_time']) || !empty($clinic['close_time'])): ?>
                                            <p class="flex items-center gap-3">
                                                <i class="fa-regular fa-clock text-indigo-600"></i>
                                                <span>
                                                    <?php echo h(substr((string) ($clinic['open_time'] ?? ''), 0, 5)); ?>
                                                    <?php if (!empty($clinic['open_time']) && !empty($clinic['close_time'])): ?> - <?php endif; ?>
                                                    <?php echo h(substr((string) ($clinic['close_time'] ?? ''), 0, 5)); ?>
                                                </span>
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mt-6 flex flex-wrap items-center gap-3">
                                        <?php if (!empty($clinic['map_url'])): ?>
                                            <a href="<?php echo h($clinic['map_url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="ดูแผนที่" title="ดูแผนที่" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-white hover:bg-indigo-700">
                                                <i class="fa-solid fa-map-location-dot"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($clinic['website_url'])): ?>
                                            <a href="<?php echo h($clinic['website_url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="เว็บไซต์" title="เว็บไซต์" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 text-slate-500 hover:border-indigo-600 hover:text-indigo-600">
                                                <i class="fa-solid fa-globe"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($clinic['facebook_url'])): ?>
                                            <a href="<?php echo h($clinic['facebook_url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook" title="Facebook" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 text-slate-500 hover:border-[#1877F2] hover:text-[#1877F2]">
                                                <i class="fa-brands fa-facebook-f"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($clinic['instagram_url'])): ?>
                                            <a href="<?php echo h($clinic['instagram_url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram" title="Instagram" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 text-slate-500 hover:border-[#E1306C] hover:text-[#E1306C]">
                                                <i class="fa-brands fa-instagram"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($clinic['tiktok_url'])): ?>
                                            <a href="<?php echo h($clinic['tiktok_url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="TikTok" title="TikTok" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 text-slate-500 hover:border-slate-900 hover:text-slate-900">
                                                <i class="fa-brands fa-tiktok"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($clinic['line_id'])): ?>
                                            <a href="<?php echo h($clinic['line_id']); ?>" target="_blank" rel="noopener noreferrer" aria-label="LINE" title="LINE" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 text-slate-500 hover:border-[#06C755] hover:text-[#06C755]">
                                                <i class="fa-brands fa-line"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>

<script>
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
</script>

</body>
</html>
