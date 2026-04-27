<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผลิตภัณฑ์ - KNA Interpharma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }

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

    <section class="px-4 py-10 md:px-6 lg:px-8">
        <div class="product-hero relative overflow-hidden rounded-[36px] border border-slate-200/80">
            <div class="mx-auto grid max-w-7xl gap-10 px-6 py-12 md:px-10 lg:grid-cols-[1.15fr_0.85fr] lg:items-center lg:px-14 lg:py-16">
                <div class="relative z-10 max-w-2xl">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-sm font-semibold text-indigo-700 shadow-sm ring-1 ring-indigo-100">
                        <i class="fa-solid fa-sparkles"></i>
                        กลุ่มผลิตภัณฑ์ KNA Interpharma
                    </span>

                    <h1 class="mt-5 text-4xl font-extrabold leading-tight text-slate-900 md:text-5xl">
                        ผลิตภัณฑ์ความงามและสุขภาพที่คัดสรรเพื่อคลินิกและผู้เชี่ยวชาญ
                    </h1>
                    <p class="mt-5 text-lg leading-8 text-slate-600">
                        รวมผลิตภัณฑ์เด่นของ KNA Interpharma พร้อมรายละเอียดเบื้องต้นเพื่อช่วยให้คุณเลือกดูได้ง่ายขึ้นในหน้าเดียว
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <span class="inline-flex items-center gap-3 rounded-2xl bg-indigo-600 px-5 py-3 text-base font-semibold text-white shadow-lg shadow-indigo-600/20">
                            <i class="fa-solid fa-box-open"></i>
                            มีผลิตภัณฑ์ทั้งหมด <?php echo number_format($productCount); ?> รายการ
                        </span>
                        <a href="#product-grid" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 py-3 font-semibold text-slate-700 transition hover:border-indigo-300 hover:text-indigo-700">
                            ดูรายการสินค้า
                            <i class="fa-solid fa-arrow-down"></i>
                        </a>
                    </div>
                </div>

                <div class="relative z-10">
                    <div class="overflow-hidden rounded-[32px] bg-white/70 p-4 shadow-[0_24px_60px_rgba(79,70,229,0.12)] ring-1 ring-white/70 backdrop-blur-sm">
                        <?php if ($heroImage !== ''): ?>
                            <img src="<?php echo h($heroImage); ?>" alt="ผลิตภัณฑ์เด่น" class="h-[280px] w-full rounded-[24px] object-cover md:h-[360px]">
                        <?php else: ?>
                            <div class="flex h-[280px] items-center justify-center rounded-[24px] bg-gradient-to-br from-indigo-100 via-white to-sky-100 text-indigo-400 md:h-[360px]">
                                <i class="fa-solid fa-box-open text-7xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="product-grid" class="px-4 pb-14 md:px-6 lg:px-8 lg:pb-20">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-indigo-600">Product Collection</p>
                    <h2 class="mt-2 text-3xl font-bold text-slate-900 md:text-4xl">รายการผลิตภัณฑ์ทั้งหมด</h2>
                    <p class="mt-3 text-slate-600">เลือกดูรายละเอียดสินค้าแต่ละรายการได้จากการ์ดด้านล่าง</p>
                </div>
            </div>

            <?php if ($dbError !== ''): ?>
                <div class="mb-8 rounded-2xl border border-yellow-300 bg-yellow-50 px-5 py-4 text-yellow-900">
                    ไม่สามารถโหลดข้อมูลสินค้าได้: <?php echo h($dbError); ?>
                </div>
            <?php endif; ?>

            <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($items as $row): ?>
                    <?php
                    $imageUrl = product_media_url($row['hero_image'] ?? '', '');
                    $name = trim((string) ($row['name'] ?? ''));
                    $description = trim((string) ($row['short_description'] ?? ''));
                    if ($description === '') {
                        $description = 'ดูรายละเอียดเพิ่มเติมเกี่ยวกับผลิตภัณฑ์นี้ได้ในหน้าสินค้าแบบเต็ม';
                    }
                    ?>
                    <article class="group flex h-full flex-col overflow-hidden rounded-[28px] bg-white shadow-[0_16px_40px_rgba(15,23,42,0.08)] ring-1 ring-slate-200/70 transition hover:-translate-y-1 hover:shadow-[0_24px_54px_rgba(15,23,42,0.12)]">
                        <div class="relative aspect-[16/11] overflow-hidden bg-slate-100">
                            <?php if ($imageUrl !== ''): ?>
                                <img src="<?php echo h($imageUrl); ?>" alt="<?php echo h($name); ?>" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            <?php else: ?>
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-100 via-white to-slate-200 text-slate-400">
                                    <i class="fa-solid fa-box-open text-6xl"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex flex-1 flex-col p-6">
                            <h3 class="text-2xl font-bold text-slate-900"><?php echo h($name); ?></h3>
                            <p class="product-card__description mt-3 text-base leading-7 text-slate-600">
                                <?php echo h($description); ?>
                            </p>

                            <div class="mt-6 pt-2">
                                <a
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-700 to-violet-600 px-5 py-3 text-base font-semibold text-white shadow-lg shadow-indigo-600/20 transition hover:translate-y-[-1px] hover:shadow-xl hover:shadow-indigo-600/25"
                                    href="/single-product.php?id=<?php echo (int) ($row['id'] ?? 0); ?>"
                                >
                                    ดูรายละเอียด
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>

                <?php if (empty($items)): ?>
                    <div class="md:col-span-2 xl:col-span-3">
                        <div class="rounded-[28px] border border-dashed border-slate-300 bg-white px-8 py-16 text-center text-slate-500">
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
