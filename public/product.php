<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/partials/product-theme-functions.php';

function product_media_url(?string $value, string $fallback = ''): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return $fallback;
    }

    if (preg_match('#^https?://#i', $value) || str_starts_with($value, '/')) {
        return $value;
    }

    return '/uploads/products/' . rawurlencode($value);
}

$items = [];
$dbError = '';

if ($pdo instanceof PDO) {
    try {
        $items = $pdo->query(
            'SELECT id, name, short_description, hero_image
             FROM products
             WHERE is_active = 1
             ORDER BY id DESC'
        )->fetchAll();
    } catch (Exception $e) {
        $dbError = $e->getMessage();
    }
}

$productCount = count($items);
$heroImage = '';
if (!empty($items)) {
    $heroImage = product_media_url($items[0]['hero_image'] ?? '', '');
}

$siteHeaderActive = 'product';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <?php $page_title = 'ผลิตภัณฑ์ - KNA Interpharma'; require_once __DIR__ . '/partials/site-head.php'; ?>
    <style>
        .product-hero {
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.42) 24%, rgba(255, 255, 255, 0) 48%),
                linear-gradient(135deg, rgba(238, 245, 255, 0.98) 0%, rgba(225, 236, 252, 0.96) 38%, rgba(212, 225, 255, 0.9) 100%);
        }

        .product-hero::after {
            content: '';
            position: absolute;
            right: -120px;
            bottom: -140px;
            width: 340px;
            height: 340px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.18) 0%, rgba(79, 70, 229, 0.06) 38%, rgba(79, 70, 229, 0) 70%);
            pointer-events: none;
        }

        .product-card__description {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            line-clamp: 3;
            min-height: 5.1rem;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <section class="relative overflow-hidden">
        <img src="/uploads/website/bg-contact-us/1-2.png" alt="Product Banner" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>
        <div class="relative mx-auto max-w-7xl px-4 pb-20 pt-16 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white/90 backdrop-blur">
                    <i class="fa-solid fa-box-open"></i> Our Products
                </div>
                <h1 class="mt-5 text-4xl font-extrabold leading-tight text-white sm:text-5xl">
                    ผลิตภัณฑ์<br class="hidden sm:block">KNA Interpharma
                </h1>
            </div>
        </div>
    </section>

    <section id="product-grid" class="px-4 pb-14 pt-10 md:px-6 lg:px-8 lg:pb-20">
        <div class="mx-auto max-w-7xl">
            <div class="mb-12 text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.32em] text-indigo-500">Product Collection</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">รายการผลิตภัณฑ์ทั้งหมด</h2>
                <div class="mx-auto mt-6 h-px w-16 bg-indigo-400/50"></div>
            </div>

            <?php if ($dbError !== ''): ?>
                <div class="mb-8 rounded-2xl border border-yellow-300 bg-yellow-50 px-5 py-4 text-yellow-900">
                    ไม่สามารถโหลดข้อมูลสินค้าได้: <?php echo h($dbError); ?>
                </div>
            <?php endif; ?>

            <div class="grid gap-10 sm:grid-cols-2">
                <?php foreach ($items as $row): ?>
                    <?php
                    $imageUrl = product_media_url($row['hero_image'] ?? '', '');
                    $name = trim((string) ($row['name'] ?? ''));
                    ?>
                    <a href="<?php echo h(product_detail_url($name)); ?>" class="group block">
                        <div class="relative aspect-[12/5] overflow-hidden rounded-3xl bg-gradient-to-br from-[#f3f3fa] to-[#e8e8f4] ring-1 ring-slate-200/60 transition duration-300 group-hover:ring-indigo-200 group-hover:shadow-[0_20px_48px_rgba(75,72,153,0.14)]">
                            <?php if ($imageUrl !== ''): ?>
                                <img src="<?php echo h($imageUrl); ?>" alt="<?php echo h($name); ?>"
                                     class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-[1.05]">
                            <?php else: ?>
                                <div class="flex h-full w-full items-center justify-center text-slate-300">
                                    <i class="fa-solid fa-box-open text-6xl"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-5 flex items-center justify-between px-1">
                            <h3 class="text-xl font-semibold tracking-tight text-slate-900 md:text-2xl"><?php echo h($name); ?></h3>
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-200 text-slate-400 transition duration-300 group-hover:border-[#4B4899] group-hover:bg-[#4B4899] group-hover:text-white">
                                <i class="fa-solid fa-arrow-right text-sm"></i>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>

                <?php if (empty($items)): ?>
                    <div class="sm:col-span-2 lg:col-span-4">
                        <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-8 py-16 text-center text-slate-500">
                            ยังไม่มีสินค้าเปิดแสดงในขณะนี้
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
</body>
</html>
