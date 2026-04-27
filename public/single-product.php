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

function product_clean_text(?string $value): string
{
    $text = str_replace(["\r\n", "\r"], "\n", (string) $value);
    $text = preg_replace('/\[img[^\]]*\].*?\[\/img\]/is', '', $text) ?? $text;
    $text = preg_replace('/\[url[^\]]*\](.*?)\[\/url\]/is', '$1', $text) ?? $text;
    $text = preg_replace('/\[(\/)?[a-z]+[^\]]*\]/i', '', $text) ?? $text;
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

    return trim($text);
}

function product_description_lines(string $text): array
{
    $clean = product_clean_text($text);
    $lines = preg_split("/\n+/", $clean) ?: [];
    $result = [];

    foreach ($lines as $line) {
        $line = trim(preg_replace('/\s+/u', ' ', $line) ?? $line);
        if ($line === '') {
            continue;
        }
        $result[] = $line;
    }

    return array_values(array_unique($result));
}

function product_description_paragraphs(string $text): array
{
    $clean = product_clean_text($text);
    if ($clean === '') {
        return [];
    }

    $blocks = preg_split("/\n{2,}/", $clean) ?: [];
    $paragraphs = [];

    foreach ($blocks as $block) {
        $lines = preg_split("/\n+/", trim($block)) ?: [];
        $lines = array_values(array_filter(array_map(
            static fn ($line) => trim(preg_replace('/\s+/u', ' ', (string) $line) ?? (string) $line),
            $lines
        ), static fn ($line) => $line !== ''));

        if (empty($lines)) {
            continue;
        }

        $paragraphs[] = implode(' ', $lines);
    }

    return $paragraphs;
}

function product_shorten_text(string $text, int $limit = 220): string
{
    $text = trim($text);
    if ($text === '') {
        return '';
    }

    if (mb_strlen($text) <= $limit) {
        return $text;
    }

    return rtrim(mb_substr($text, 0, $limit - 1)) . '…';
}

function product_summary(array $paragraphs, array $lines, string $name): string
{
    foreach ($paragraphs as $paragraph) {
        $candidate = trim($paragraph);
        if ($candidate === '' || mb_strtolower($candidate) === mb_strtolower($name)) {
            continue;
        }

        return product_shorten_text($candidate, 240);
    }

    foreach ($lines as $line) {
        $candidate = trim($line);
        if ($candidate === '' || mb_strtolower($candidate) === mb_strtolower($name)) {
            continue;
        }

        return product_shorten_text($candidate, 240);
    }

    return 'ผลิตภัณฑ์ที่คัดสรรโดย KNA Interpharma สำหรับการนำเสนอในบริบทคลินิกและความงามอย่างมืออาชีพ';
}

function product_highlights(array $lines, string $name): array
{
    $highlights = [];

    foreach ($lines as $line) {
        $candidate = trim($line);
        if ($candidate === '' || mb_strtolower($candidate) === mb_strtolower($name)) {
            continue;
        }

        $length = mb_strlen($candidate);
        if ($length < 18 || $length > 140) {
            continue;
        }

        if (preg_match('/^[A-Z0-9 .,&\-\/]+$/', $candidate)) {
            continue;
        }

        $highlights[] = $candidate;
        if (count($highlights) === 4) {
            break;
        }
    }

    if (empty($highlights)) {
        $highlights = [
            'ออกแบบเพื่อการใช้งานในบริบทคลินิกและความงามอย่างมืออาชีพ',
            'นำเสนอจุดเด่นของผลิตภัณฑ์ผ่านภาพลักษณ์ที่สะอาด น่าเชื่อถือ และอ่านง่าย',
            'เหมาะสำหรับใช้เป็นหน้ารายละเอียดสินค้าที่พาไปสู่การติดต่อหรือการนัดหมายต่อ',
        ];
    }

    return $highlights;
}

function product_detect_category(string $name, string $text): string
{
    $source = mb_strtolower($name . ' ' . $text);

    if (str_contains($source, 'thread') || str_contains($source, 'pdo') || str_contains($source, 'ไหม')) {
        return 'Thread Lift';
    }

    if (str_contains($source, 'pdlla') || str_contains($source, 'biostimulator')) {
        return 'Biostimulator';
    }

    if (str_contains($source, 'filler') || str_contains($source, 'ฟิลเลอร์') || str_contains($source, 'hyaluronic') || str_contains($source, 'ha ')) {
        return 'Dermal Filler';
    }

    return 'Clinical Product';
}

function product_detect_focus(string $text): string
{
    $source = mb_strtolower($text);

    if (str_contains($source, 'สะโพก')) {
        return 'เพิ่มวอลลุ่มและปรับรูปทรง';
    }

    if (str_contains($source, 'ยกกระชับ') || str_contains($source, 'lifting') || str_contains($source, 'thread')) {
        return 'ยกกระชับและฟื้นฟูโครงสร้างผิว';
    }

    if (str_contains($source, 'ชุ่มชื้น') || str_contains($source, 'hydrat')) {
        return 'เติมความชุ่มชื้นและฟื้นฟูผิว';
    }

    if (str_contains($source, 'face & body') || str_contains($source, 'ร่างกาย')) {
        return 'รองรับการฟื้นฟูทั้งใบหน้าและร่างกาย';
    }

    return 'เน้นผลลัพธ์ที่ดูเป็นธรรมชาติและน่าเชื่อถือ';
}

function product_detect_origin(string $text): string
{
    $source = mb_strtolower($text);

    if (str_contains($source, 'เยอรมนี') || str_contains($source, 'germany')) {
        return 'เยอรมนี';
    }

    if (str_contains($source, 'ไต้หวัน') || str_contains($source, 'taiwan')) {
        return 'ไต้หวัน';
    }

    if (str_contains($source, 'ไทย') || str_contains($source, 'thailand')) {
        return 'ประเทศไทย';
    }

    return 'ข้อมูลประเทศผู้ผลิตเพิ่มเติม';
}

function product_badges(string $name, string $text): array
{
    $source = mb_strtolower($name . ' ' . $text);
    $badges = [];

    if (str_contains($source, 'อย.')) {
        $badges[] = 'มีการกล่าวถึง อย.';
    }

    if (str_contains($source, 'เยอรมนี') || str_contains($source, 'germany')) {
        $badges[] = 'ผลิตภัณฑ์จากเยอรมนี';
    }

    if (str_contains($source, 'ไต้หวัน') || str_contains($source, 'taiwan')) {
        $badges[] = 'ผลิตภัณฑ์จากไต้หวัน';
    }

    if (str_contains($source, 'face & body')) {
        $badges[] = 'รองรับ FACE & BODY';
    }

    if (str_contains($source, 'ปลอดภัย') || str_contains($source, 'safe') || str_contains($source, 'safety')) {
        $badges[] = 'เน้นความปลอดภัย';
    }

    $badges[] = 'เหมาะสำหรับคลินิกและผู้เชี่ยวชาญ';

    return array_slice(array_values(array_unique($badges)), 0, 4);
}

$id = (int) ($_GET['id'] ?? 0);
$item = null;
$relatedItems = [];
$dbError = '';

if ($pdo instanceof PDO && $id > 0) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? AND is_active = 1');
        $stmt->execute([$id]);
        $item = $stmt->fetch();

        if ($item) {
            $relatedStmt = $pdo->prepare(
                'SELECT id, name, short_description, hero_image
                 FROM products
                 WHERE is_active = 1
                   AND id <> ?
                 ORDER BY id DESC
                 LIMIT 3'
            );
            $relatedStmt->execute([$id]);
            $relatedItems = $relatedStmt->fetchAll();
        }
    } catch (Exception $e) {
        $dbError = $e->getMessage();
    }
}

$name = trim((string) ($item['name'] ?? ''));
$description = product_clean_text((string) ($item['short_description'] ?? ''));
$descriptionLines = product_description_lines($description);
$descriptionParagraphs = product_description_paragraphs($description);
$summary = product_summary($descriptionParagraphs, $descriptionLines, $name);
$highlights = product_highlights($descriptionLines, $name);
$category = product_detect_category($name, $description);
$focus = product_detect_focus($description);
$origin = product_detect_origin($description);
$badges = product_badges($name, $description);
$heroImage = product_media_url($item['hero_image'] ?? '', '');
$contentParagraphs = array_slice($descriptionParagraphs, 0, 6);
$siteHeaderActive = 'product';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $item ? h($name . ' - KNA Interpharma') : 'ไม่พบสินค้า - KNA Interpharma'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }

        .product-detail-hero {
            background:
                radial-gradient(circle at 12% 18%, rgba(255, 255, 255, 0.92) 0%, rgba(255, 255, 255, 0.5) 18%, rgba(255, 255, 255, 0) 42%),
                linear-gradient(135deg, rgba(236, 244, 255, 0.98) 0%, rgba(230, 238, 255, 0.96) 42%, rgba(220, 230, 255, 0.94) 100%);
        }

        .product-detail-hero::after {
            content: '';
            position: absolute;
            right: -120px;
            top: -80px;
            width: 360px;
            height: 360px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.18) 0%, rgba(79, 70, 229, 0.05) 45%, rgba(79, 70, 229, 0) 72%);
            pointer-events: none;
        }

        .clinical-grid {
            background-image:
                linear-gradient(rgba(99, 102, 241, 0.08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99, 102, 241, 0.08) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.95) 0%, rgba(0, 0, 0, 0.2) 100%);
            -webkit-mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.95) 0%, rgba(0, 0, 0, 0.2) 100%);
        }

        .product-related__description {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            line-clamp: 3;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <main class="px-4 pb-16 md:px-6 lg:px-8 lg:pb-20">
        <?php if (!$item): ?>
            <section class="mx-auto max-w-4xl py-14">
                <div class="rounded-[32px] border border-slate-200 bg-white px-8 py-16 text-center shadow-sm">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <i class="fa-solid fa-box-open text-3xl"></i>
                    </div>
                    <h1 class="mt-6 text-3xl font-bold text-slate-900">ไม่พบสินค้าที่ต้องการ</h1>
                    <p class="mx-auto mt-4 max-w-xl text-slate-600">
                        รายการสินค้านี้อาจถูกปิดการแสดงผลหรือไม่มีอยู่ในระบบแล้ว
                    </p>
                    <div class="mt-8 flex flex-wrap justify-center gap-4">
                        <a href="/product.php" class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-5 py-3 font-semibold text-white transition hover:bg-indigo-700">
                            <i class="fa-solid fa-arrow-left"></i>
                            กลับไปหน้าผลิตภัณฑ์
                        </a>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <section class="mx-auto max-w-7xl pt-8">
                <div class="mb-5 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                    <a href="/index.php" class="hover:text-indigo-600">หน้าแรก</a>
                    <span>/</span>
                    <a href="/product.php" class="hover:text-indigo-600">ผลิตภัณฑ์</a>
                    <span>/</span>
                    <span class="text-slate-700"><?php echo h($name); ?></span>
                </div>

                <?php if ($dbError !== ''): ?>
                    <div class="mb-6 rounded-2xl border border-yellow-300 bg-yellow-50 px-5 py-4 text-yellow-900">
                        ไม่สามารถโหลดข้อมูลสินค้าได้: <?php echo h($dbError); ?>
                    </div>
                <?php endif; ?>

                <section class="product-detail-hero relative isolate overflow-hidden rounded-[36px] border border-slate-200/70">
                    <div class="clinical-grid absolute inset-y-0 right-0 hidden w-[38%] opacity-60 lg:block"></div>

                    <div class="relative mx-auto grid max-w-7xl gap-10 px-6 py-10 md:px-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:px-14 lg:py-14">
                        <div class="relative z-10">
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($badges as $badge): ?>
                                    <span class="inline-flex items-center rounded-full bg-white/85 px-4 py-2 text-sm font-semibold text-indigo-700 shadow-sm ring-1 ring-indigo-100">
                                        <?php echo h($badge); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>

                            <h1 class="mt-6 text-4xl font-extrabold leading-tight text-slate-900 md:text-5xl lg:text-6xl">
                                <?php echo h($name); ?>
                            </h1>
                            <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600 md:text-xl">
                                <?php echo h($summary); ?>
                            </p>

                            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                                <div class="rounded-3xl bg-white/90 px-5 py-4 shadow-sm ring-1 ring-slate-200/80 backdrop-blur-sm">
                                    <div class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">ประเภท</div>
                                    <div class="mt-2 text-lg font-bold text-slate-900"><?php echo h($category); ?></div>
                                </div>
                                <div class="rounded-3xl bg-white/90 px-5 py-4 shadow-sm ring-1 ring-slate-200/80 backdrop-blur-sm">
                                    <div class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">จุดโฟกัส</div>
                                    <div class="mt-2 text-lg font-bold text-slate-900"><?php echo h($focus); ?></div>
                                </div>
                                <div class="rounded-3xl bg-white/90 px-5 py-4 shadow-sm ring-1 ring-slate-200/80 backdrop-blur-sm">
                                    <div class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">แหล่งอ้างอิง</div>
                                    <div class="mt-2 text-lg font-bold text-slate-900"><?php echo h($origin); ?></div>
                                </div>
                            </div>

                            <div class="mt-8 flex flex-wrap gap-4">
                                <a href="/contact.php" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-700 to-violet-600 px-6 py-3 font-semibold text-white shadow-lg shadow-indigo-600/20 transition hover:translate-y-[-1px] hover:shadow-xl">
                                    สอบถามข้อมูลสินค้า
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                                <a href="/searchpage.php" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-700 transition hover:border-indigo-300 hover:text-indigo-700">
                                    ค้นหาคลินิก
                                    <i class="fa-solid fa-location-dot"></i>
                                </a>
                            </div>
                        </div>

                        <div class="relative z-10">
                            <div class="overflow-hidden rounded-[34px] bg-white/80 p-4 shadow-[0_24px_60px_rgba(79,70,229,0.14)] ring-1 ring-white/80 backdrop-blur-sm">
                                <?php if ($heroImage !== ''): ?>
                                    <img src="<?php echo h($heroImage); ?>" alt="<?php echo h($name); ?>" class="h-[320px] w-full rounded-[26px] object-cover md:h-[420px]">
                                <?php else: ?>
                                    <div class="flex h-[320px] items-center justify-center rounded-[26px] bg-gradient-to-br from-indigo-100 via-white to-sky-100 text-indigo-400 md:h-[420px]">
                                        <i class="fa-solid fa-box-open text-8xl"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </section>
            </section>

            <section class="mx-auto max-w-7xl pt-10">
                <div class="mb-8">
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-indigo-600">Highlights</p>
                    <h2 class="mt-2 text-3xl font-bold text-slate-900 md:text-4xl">จุดเด่นที่ควรรู้ก่อนตัดสินใจ</h2>
                </div>

                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <?php foreach ($highlights as $index => $highlight): ?>
                        <div class="rounded-[28px] bg-white p-6 shadow-[0_16px_40px_rgba(15,23,42,0.08)] ring-1 ring-slate-200/70">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                                <i class="fa-solid fa-star text-lg"></i>
                            </div>
                            <div class="mt-5 text-lg font-semibold leading-8 text-slate-800">
                                <?php echo h($highlight); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="mx-auto grid max-w-7xl gap-8 pt-12 lg:grid-cols-[1.2fr_0.8fr]">
                <div class="rounded-[32px] bg-white p-7 shadow-[0_16px_40px_rgba(15,23,42,0.08)] ring-1 ring-slate-200/70 md:p-10">
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-indigo-600">Clinical Overview</p>
                    <h2 class="mt-2 text-3xl font-bold text-slate-900">รายละเอียดผลิตภัณฑ์</h2>

                    <div class="mt-8 space-y-6 text-lg leading-8 text-slate-600">
                        <?php if (!empty($contentParagraphs)): ?>
                            <?php foreach ($contentParagraphs as $paragraph): ?>
                                <p><?php echo h($paragraph); ?></p>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>ยังไม่มีรายละเอียดเชิงลึกของผลิตภัณฑ์นี้ในระบบ</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-[32px] bg-slate-900 p-7 text-white shadow-[0_18px_46px_rgba(15,23,42,0.16)] md:p-8">
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-cyan-200">Why It Feels Premium</p>
                        <h2 class="mt-2 text-3xl font-bold">เหตุผลที่หน้านี้ควรขายได้</h2>
                        <div class="mt-6 space-y-4 text-base leading-7 text-slate-200">
                            <p>โครงหน้านี้เน้นการสร้างความน่าเชื่อถือผ่านภาพลักษณ์สะอาด ข้อมูลชัด และจุดขายที่อ่านง่ายตั้งแต่ช่วงแรกของหน้า</p>
                            <p>การแบ่งข้อมูลเป็น hero, highlights, รายละเอียด และ CTA ช่วยให้ผู้ชมเข้าใจทั้งตัวสินค้าและทางไปต่อได้เร็วขึ้น</p>
                        </div>
                    </div>

                    <div class="rounded-[32px] bg-white p-7 shadow-[0_16px_40px_rgba(15,23,42,0.08)] ring-1 ring-slate-200/70 md:p-8">
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-indigo-600">Trust Layer</p>
                        <h2 class="mt-2 text-3xl font-bold text-slate-900">ชั้นข้อมูลที่ช่วยเพิ่มความเชื่อถือ</h2>
                        <ul class="mt-6 space-y-4 text-base leading-7 text-slate-600">
                            <li class="flex gap-3">
                                <i class="fa-solid fa-circle-check mt-1 text-indigo-600"></i>
                                <span>ใช้ภาพ packshot ขนาดใหญ่เพื่อให้สินค้าเป็นพระเอกของหน้าอย่างชัดเจน</span>
                            </li>
                            <li class="flex gap-3">
                                <i class="fa-solid fa-circle-check mt-1 text-indigo-600"></i>
                                <span>ดึงคีย์เวิร์ดจากข้อมูลสินค้าเพื่อแสดงประเภท จุดโฟกัส และบริบทการใช้งานแบบอ่านง่าย</span>
                            </li>
                            <li class="flex gap-3">
                                <i class="fa-solid fa-circle-check mt-1 text-indigo-600"></i>
                                <span>เพิ่ม CTA ที่เชื่อมไปยังการสอบถามข้อมูลและการค้นหาคลินิกต่อได้ทันที</span>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-[32px] bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-700 p-7 text-white shadow-[0_18px_46px_rgba(79,70,229,0.24)] md:p-8">
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-indigo-100">Next Step</p>
                        <h2 class="mt-2 text-3xl font-bold">พร้อมคุยต่อในเชิงคลินิกหรือการใช้งานจริง</h2>
                        <p class="mt-5 text-base leading-7 text-indigo-50">
                            หากต้องการข้อมูลเชิงลึกเพิ่มเติมเกี่ยวกับสินค้า การใช้งาน หรือการประยุกต์ในคลินิก สามารถติดต่อทีมงานเพื่อรับข้อมูลเพิ่มเติมได้
                        </p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="/contact.php" class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 font-semibold text-indigo-700 transition hover:bg-indigo-50">
                                ติดต่อทีมงาน
                                <i class="fa-solid fa-headset"></i>
                            </a>
                            <a href="/searchpage.php" class="inline-flex items-center gap-2 rounded-2xl border border-white/30 px-5 py-3 font-semibold text-white transition hover:bg-white/10">
                                ดูคลินิกที่เกี่ยวข้อง
                                <i class="fa-solid fa-location-dot"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <?php if (!empty($relatedItems)): ?>
                <section class="mx-auto max-w-7xl pt-12">
                    <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-indigo-600">More Products</p>
                            <h2 class="mt-2 text-3xl font-bold text-slate-900 md:text-4xl">ผลิตภัณฑ์อื่นที่น่าสนใจ</h2>
                        </div>
                        <a href="/product.php" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">ดูสินค้าทั้งหมด</a>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        <?php foreach ($relatedItems as $related): ?>
                            <?php
                            $relatedImage = product_media_url($related['hero_image'] ?? '', '');
                            $relatedDescription = product_shorten_text(product_clean_text((string) ($related['short_description'] ?? '')), 150);
                            ?>
                            <article class="group overflow-hidden rounded-[28px] bg-white shadow-[0_16px_40px_rgba(15,23,42,0.08)] ring-1 ring-slate-200/70">
                                <div class="aspect-[16/11] overflow-hidden bg-slate-100">
                                    <?php if ($relatedImage !== ''): ?>
                                        <img src="<?php echo h($relatedImage); ?>" alt="<?php echo h($related['name'] ?? ''); ?>" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                    <?php else: ?>
                                        <div class="flex h-full w-full items-center justify-center text-slate-400">
                                            <i class="fa-solid fa-box-open text-6xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="p-6">
                                    <h3 class="text-2xl font-bold text-slate-900"><?php echo h($related['name'] ?? ''); ?></h3>
                                    <p class="product-related__description mt-3 text-base leading-7 text-slate-600">
                                        <?php echo h($relatedDescription !== '' ? $relatedDescription : 'ดูรายละเอียดเพิ่มเติมของผลิตภัณฑ์นี้ได้ในหน้าสินค้า'); ?>
                                    </p>
                                    <a href="/single-product.php?id=<?php echo (int) ($related['id'] ?? 0); ?>" class="mt-5 inline-flex items-center gap-2 font-semibold text-indigo-600 transition hover:text-indigo-700">
                                        ดูรายละเอียด
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
</body>
</html>
