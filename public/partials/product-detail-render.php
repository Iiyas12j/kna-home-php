<?php
// Shared product-detail renderer. Each of the four dedicated entry files
// (neofilera.php, hyabell.php, variofill.php, meteora.php) sets $productName
// to the product's exact DB `name` value, then requires this file. Looking the
// product up by name (not id) keeps this stable across environments where
// numeric ids differ (e.g. production vs. this dev database).
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_once __DIR__ . '/product-theme-functions.php';

if (!isset($productName) || trim((string) $productName) === '') {
    http_response_code(500);
    exit('product-detail-render.php requires $productName to be set before include.');
}

function product_media_url(?string $value, string $fallback = ''): string
{
    $value = trim((string) $value);
    if ($value === '') return $fallback;
    if (preg_match('#^https?://#i', $value) || str_starts_with($value, '/')) return $value;
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

function product_description_paragraphs(string $text): array
{
    $clean = product_clean_text($text);
    if ($clean === '') return [];
    $blocks = preg_split("/\n{2,}/", $clean) ?: [];
    $paragraphs = [];
    foreach ($blocks as $block) {
        $lines = preg_split("/\n+/", trim($block)) ?: [];
        $lines = array_values(array_filter(array_map(
            static fn($l) => trim(preg_replace('/\s+/u', ' ', (string) $l) ?? (string) $l),
            $lines
        ), static fn($l) => $l !== ''));
        if (!empty($lines)) $paragraphs[] = implode(' ', $lines);
    }
    return $paragraphs;
}

function product_description_lines(string $text): array
{
    $clean = product_clean_text($text);
    $lines = preg_split("/\n+/", $clean) ?: [];
    $result = [];
    foreach ($lines as $line) {
        $line = trim(preg_replace('/\s+/u', ' ', $line) ?? $line);
        if ($line !== '') $result[] = $line;
    }
    return array_values(array_unique($result));
}

function product_shorten_text(string $text, int $limit = 220): string
{
    $text = trim($text);
    if ($text === '') return '';
    if (mb_strlen($text) <= $limit) return $text;
    return rtrim(mb_substr($text, 0, $limit - 1)) . '…';
}

function product_summary(array $paragraphs, array $lines, string $name): string
{
    foreach ($paragraphs as $p) {
        $c = trim($p);
        if ($c !== '' && mb_strtolower($c) !== mb_strtolower($name))
            return product_shorten_text($c, 260);
    }
    foreach ($lines as $l) {
        $c = trim($l);
        if ($c !== '' && mb_strtolower($c) !== mb_strtolower($name))
            return product_shorten_text($c, 260);
    }
    return 'ผลิตภัณฑ์คุณภาพพรีเมียมคัดสรรโดย KNA Interpharma';
}

function product_highlights(array $lines, string $name): array
{
    $highlights = [];
    foreach ($lines as $line) {
        $c = trim($line);
        $len = mb_strlen($c);
        if ($c === '' || mb_strtolower($c) === mb_strtolower($name)) continue;
        if ($len < 15 || $len > 160) continue;
        if (preg_match('/^[A-Z0-9 .,&\-\/]+$/', $c)) continue;
        $highlights[] = $c;
        if (count($highlights) === 5) break;
    }
    return $highlights;
}

function product_detect_category(string $name, string $text): string
{
    $src = mb_strtolower($name . ' ' . $text);
    if (str_contains($src, 'thread') || str_contains($src, 'pdo') || str_contains($src, 'ไหม')) return 'Thread Lift';
    if (str_contains($src, 'pdlla') || str_contains($src, 'biostimulator')) return 'Biostimulator';
    if (str_contains($src, 'filler') || str_contains($src, 'ฟิลเลอร์') || str_contains($src, 'hyaluronic')) return 'Dermal Filler';
    return 'Clinical Product';
}

function product_detect_origin(string $text): string
{
    $src = mb_strtolower($text);
    if (str_contains($src, 'เยอรมนี') || str_contains($src, 'germany')) return 'Germany 🇩🇪';
    if (str_contains($src, 'ไต้หวัน') || str_contains($src, 'taiwan')) return 'Taiwan 🇹🇼';
    if (str_contains($src, 'เกาหลี') || str_contains($src, 'korea')) return 'Korea 🇰🇷';
    return 'Imported';
}

// ── DB ────────────────────────────────────────────────────────────────────────
$item = null;
$relatedItems = [];
$galleryImages = [];

if ($pdo instanceof PDO) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM products WHERE name = ? AND is_active = 1');
        $stmt->execute([$productName]);
        $item = $stmt->fetch();
        if ($item) {
            $productId = (int) $item['id'];

            $galleryStmt = $pdo->prepare(
                'SELECT image FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC'
            );
            $galleryStmt->execute([$productId]);
            foreach ($galleryStmt->fetchAll() as $galleryRow) {
                $galleryUrl = product_media_url($galleryRow['image'] ?? '');
                if ($galleryUrl !== '') {
                    $galleryImages[] = $galleryUrl;
                }
            }

            $relatedStmt = $pdo->prepare(
                'SELECT id, name, short_description, hero_image
                 FROM products WHERE is_active = 1 AND id <> ? ORDER BY id DESC LIMIT 3'
            );
            $relatedStmt->execute([$productId]);
            $relatedItems = $relatedStmt->fetchAll();
        }
    } catch (Exception $e) {}
}

$name        = trim((string) ($item['name'] ?? ''));
$rawDesc     = (string) ($item['short_description'] ?? '');
$paragraphs  = product_description_paragraphs($rawDesc);
$lines       = product_description_lines($rawDesc);
$summary     = product_summary($paragraphs, $lines, $name);
$highlights  = product_highlights($lines, $name);
$category    = product_detect_category($name, product_clean_text($rawDesc));
$origin      = product_detect_origin(product_clean_text($rawDesc));
$heroImage   = product_media_url($item['hero_image'] ?? '', '');
$logoImage   = product_media_url($item['logo_image'] ?? '', '');
$brand       = product_brand_theme($name, product_clean_text($rawDesc));
$heroVideo   = product_hero_video($name);
$fullVideo   = product_full_video($name);
$piTheme     = product_immersive_theme($name);
$hyabellAssets = $piTheme['hyabell_family'] ? product_hyabell_family_assets() : ['family' => '', 'syringe' => '', 'gel' => ''];

// Some products (Hyabell) bundle several SKUs into one description field.
// When that's detected, the first variant becomes the default view and a
// selector lets doctors switch between SKUs instead of reading one blob.
$variants = product_parse_variants($name, product_clean_text($rawDesc));
if (!empty($variants)) {
    $summary    = $variants[0]['summary'];
    $highlights = $variants[0]['highlights'];
    $paragraphs = $variants[0]['paragraphs'];
    foreach ($variants as &$v) {
        $v['heroPhoto'] = product_variant_hero_photo($name, $v['label']);
    }
    unset($v);
}
$defaultHeroPhoto = !empty($variants) ? $variants[0]['heroPhoto'] : '';

// All displayable images: hero first, then gallery (deduplicated).
$allImages = array_values(array_unique(array_filter(
    array_merge($heroImage !== '' ? [$heroImage] : [], $galleryImages)
)));
$siteHeaderActive = 'product';
$page_title = $item ? ($name . ' - KNA Interpharma') : 'ไม่พบสินค้า - KNA Interpharma';
if ($item && $summary !== '') {
    $page_description = mb_substr($summary, 0, 160);
}
if ($item && $heroImage !== '') {
    $page_og_image = $heroImage;
}
?>
<!DOCTYPE html>
<html lang="th" class="no-js">
<head>
    <?php require_once __DIR__ . '/site-head.php'; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/product-immersive.css">
    <?php if (!empty($variants)): ?>
    <script type="importmap">
    {
        "imports": {
            "three": "https://unpkg.com/three@0.169.0/build/three.module.js",
            "three/addons/": "https://unpkg.com/three@0.169.0/examples/jsm/"
        }
    }
    </script>
    <?php endif; ?>
    <style>
        .product-immersive { --pi-accent: <?php echo h($piTheme['accent']); ?>; --pi-accent-rgb: <?php echo h($piTheme['accent_rgb']); ?>; }
    </style>
</head>
<body class="product-detail-page">
    <?php require_once __DIR__ . '/site-header.php'; ?>

    <?php if (!$item): ?>
    <!-- Not Found -->
    <div class="flex flex-col items-center justify-center py-32 px-4 text-center">
        <div class="w-20 h-20 rounded-full flex items-center justify-center mb-6" style="background:#f2f1f6; color:#8a8894;">
            <i class="fa-solid fa-box-open text-3xl"></i>
        </div>
        <h1 class="text-3xl font-bold mb-3" style="color:#16151a;">ไม่พบสินค้าที่ต้องการ</h1>
        <p class="mb-8" style="color:#8a8894;">รายการสินค้านี้อาจถูกปิดการแสดงผลหรือไม่มีอยู่ในระบบ</p>
        <a href="/product.php" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold text-white" style="background:#4B4899;">
            <i class="fa-solid fa-arrow-left"></i> กลับไปหน้าผลิตภัณฑ์
        </a>
    </div>

    <?php else: ?>

    <main class="product-immersive" data-product-id="<?php echo (int) $item['id']; ?>">

        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-6" style="color:#8a8894;" aria-label="Breadcrumb">
            <a href="/index.php" class="hover:underline">หน้าแรก</a>
            <i class="fa-solid fa-chevron-right text-xs"></i>
            <a href="/product.php" class="hover:underline">ผลิตภัณฑ์</a>
            <i class="fa-solid fa-chevron-right text-xs"></i>
            <span class="font-medium" style="color:#16151a;"><?php echo h($name); ?></span>
        </nav>

        <!-- Hero -->
        <section class="pi-hero" id="scrubHero" aria-label="<?php echo h($name); ?> product view">
            <canvas class="pi-hero__canvas" aria-hidden="true"></canvas>
            <div class="pi-hero__scrim" aria-hidden="true"></div>

            <div class="pi-hero__inner">
                <div class="pi-hero__copy">
                    <span class="pi-hero__eyebrow"><i class="fa-solid fa-globe" aria-hidden="true"></i> <?php echo h($origin); ?> · <?php echo h($category); ?></span>
                    <h1 class="pi-hero__name"><?php echo h($name); ?></h1>
                    <p class="pi-hero__lead"><?php echo h(product_shorten_text($summary, 180)); ?></p>
                    <div class="pi-hero__actions">
                        <a href="/contact.php" class="pi-btn pi-btn--solid"><i class="fa-solid fa-envelope"></i> สอบถามข้อมูลสินค้า</a>
                        <a href="/searchpage.php" class="pi-btn pi-btn--glass"><i class="fa-solid fa-location-dot"></i> ค้นหาคลินิก</a>
                        <?php if ($fullVideo['video'] !== ''): ?>
                        <button type="button" class="pi-btn pi-btn--glass" data-lightbox-video="<?php echo h($fullVideo['video']); ?>" data-lightbox-alt="วิดีโอแนะนำ <?php echo h($name); ?>">
                            <i class="fa-solid fa-circle-play"></i> ดูวิดีโอเต็ม
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="pi-hero__stage">
                    <?php if (!empty($variants)): ?>
                        <div class="pi-hero__stage-3d" id="p3dStage" aria-hidden="true"></div>
                    <?php elseif (!empty($allImages)): ?>
                        <div class="pi-hero__stage-img">
                            <img id="scrubImage" src="<?php echo h($allImages[0]); ?>" alt="<?php echo h($name); ?>" width="480" height="480" loading="eager" decoding="async">
                        </div>
                    <?php endif; ?>

                    <div class="pi-hero__chip pi-hero__chip--a">
                        <span>แหล่งผลิต</span>
                        <strong><?php echo h($origin); ?></strong>
                        <small><?php echo h($category); ?></small>
                    </div>
                    <?php if (!empty($variants) && ($variants[0]['ha'] !== '' || $variants[0]['duration'] !== '')): ?>
                    <div class="pi-hero__chip pi-hero__chip--b">
                        <span>ข้อมูลคลินิก</span>
                        <?php if ($variants[0]['ha'] !== ''): ?><strong>HA <?php echo h($variants[0]['ha']); ?></strong><?php endif; ?>
                        <?php if ($variants[0]['duration'] !== ''): ?><small>คงอยู่ <?php echo h($variants[0]['duration']); ?></small><?php endif; ?>
                    </div>
                    <?php elseif (!empty($highlights)): ?>
                    <div class="pi-hero__chip pi-hero__chip--b">
                        <span>จุดเด่น</span>
                        <small><?php echo h(product_shorten_text($highlights[0], 60)); ?></small>
                    </div>
                    <?php endif; ?>
                    <div class="pi-hero__chip pi-hero__chip--c">
                        <span>สถานะ</span>
                        <strong>สำหรับผู้เชี่ยวชาญ</strong>
                        <small>Clinical use only</small>
                    </div>
                </div>
            </div>

            <div class="pi-hero__hint"><span>เลื่อนลงเพื่อดูรายละเอียด</span> <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></div>
        </section>

        <?php if (!empty($variants)): ?>
        <script type="module">
            import { mountProductStage } from '/assets/js/product-3d-box.js';
            var stageEl = document.getElementById('p3dStage');
            var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (stageEl) {
                var controller = mountProductStage(stageEl, <?php echo json_encode($variants[0]['label']); ?>);
                if (reduced) { controller.setAutoRotate && controller.setAutoRotate(false); }
                window.p3dSetVariant = function (label) { controller.setVariant(label); };
            }
        </script>
        <?php endif; ?>

        <!-- Highlight ticker — real product facts only, never invented -->
        <?php
        $tickerFacts = array_values(array_filter(array_merge(
            [$category, $origin],
            !empty($variants) ? array_map(static fn($v) => $v['label'] !== '' ? $name . ' ' . ucfirst(mb_strtolower($v['label'])) : '', $variants) : [],
            $highlights
        )));
        ?>
        <?php if (!empty($tickerFacts)): ?>
        <div class="pi-ticker" aria-hidden="true">
            <div class="pi-ticker__track">
                <?php foreach (array_merge($tickerFacts, $tickerFacts) as $fact): ?>
                    <span><?php echo h($fact); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Hyabell family showcase — only for the product whose theme flags it, never shown on other products -->
        <?php if ($piTheme['hyabell_family'] && !empty($variants)): ?>
        <section class="pi-section pi-family" aria-label="Hyabell product family">
            <div class="pi-section__inner">
                <span class="pi-eyebrow">Hyabell Family</span>
                <h2>เลือกรุ่นที่เหมาะกับการรักษา</h2>
                <p class="pi-dek">Hyabell แต่ละรุ่นมีความเข้มข้นของ HA และระยะเวลาคงอยู่ต่างกัน กดเพื่อดูข้อมูลแต่ละรุ่น</p>

                <div class="pi-family__grid" role="tablist" aria-label="Hyabell variants">
                    <?php foreach ($variants as $vIdx => $v): ?>
                        <?php
                        $vAccent = product_hyabell_variant_accent($v['label']);
                        $vStats = array_values(array_filter([
                            $v['ha'] !== '' ? 'HA ' . $v['ha'] : '',
                            $v['duration'] !== '' ? 'คงอยู่ ' . $v['duration'] : '',
                        ]));
                        $vImg = $v['image'] !== '' ? $v['image'] : ($heroImage !== '' ? $heroImage : '');
                        ?>
                        <button type="button" class="pi-family__card<?php echo $vIdx === 0 ? ' is-active' : ''; ?>"
                                style="--pi-card-accent: <?php echo h($vAccent); ?>;"
                                data-accent="<?php echo h($vAccent); ?>"
                                data-stats='<?php echo h(json_encode($vStats, JSON_UNESCAPED_UNICODE)); ?>'
                                data-summary="<?php echo h(product_shorten_text($v['summary'], 200)); ?>">
                            <?php if ($vImg !== ''): ?>
                                <img src="<?php echo h($vImg); ?>" alt="Hyabell <?php echo h($v['label']); ?>" loading="lazy" decoding="async">
                            <?php endif; ?>
                            <span class="pi-dot" aria-hidden="true"></span>
                            <span><?php echo h($v['label']); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="pi-family__detail">
                    <div class="pi-family__detail-stats"></div>
                    <p></p>
                </div>

                <?php if ($hyabellAssets['family'] !== '' || $hyabellAssets['syringe'] !== '' || $hyabellAssets['gel'] !== ''): ?>
                <div class="pi-family__extras">
                    <?php if ($hyabellAssets['family'] !== ''): ?>
                    <button type="button" class="pi-family__extra" data-lightbox-src="<?php echo h($hyabellAssets['family']); ?>" data-lightbox-alt="Hyabell product family — Basic, Deep, Lips, Ultra">
                        <img src="<?php echo h($hyabellAssets['family']); ?>" alt="Hyabell product family — Basic, Deep, Lips, Ultra" loading="lazy" decoding="async">
                    </button>
                    <?php endif; ?>
                    <?php if ($hyabellAssets['syringe'] !== ''): ?>
                    <button type="button" class="pi-family__extra" data-lightbox-src="<?php echo h($hyabellAssets['syringe']); ?>" data-lightbox-alt="Hyabell syringe">
                        <img src="<?php echo h($hyabellAssets['syringe']); ?>" alt="Hyabell syringe" loading="lazy" decoding="async">
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Composition — real per-variant clinical data, updates with the variant selector -->
        <section class="pi-section" aria-label="ข้อมูลจำเพาะของผลิตภัณฑ์">
            <div class="pi-section__inner">
                <span class="pi-eyebrow">Composition</span>
                <h2>ข้อมูลจำเพาะของผลิตภัณฑ์</h2>

                <?php if (!empty($variants)): ?>
                    <?php foreach ($variants as $vIdx => $v): ?>
                        <div class="variant-panel pi-cards" data-variant-index="<?php echo $vIdx; ?>" <?php echo $vIdx !== 0 ? 'hidden' : ''; ?>>
                            <?php if ($v['ha'] !== ''): ?>
                            <div class="pi-card">
                                <span class="pi-card__index">01</span>
                                <h3>ความเข้มข้นของ HA</h3>
                                <p>Crosslinked Hyaluronic Acid เข้มข้น <?php echo h($v['ha']); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if ($v['duration'] !== ''): ?>
                            <div class="pi-card">
                                <span class="pi-card__index">02</span>
                                <h3>ระยะเวลาคงอยู่</h3>
                                <p>คงอยู่ <?php echo h($v['duration']); ?> หลังการฉีด</p>
                            </div>
                            <?php endif; ?>
                            <div class="pi-card">
                                <span class="pi-card__index">03</span>
                                <h3>เหมาะสำหรับ</h3>
                                <p><?php echo h(product_shorten_text($v['summary'], 160)); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="pi-cards">
                        <div class="pi-card">
                            <span class="pi-card__index">01</span>
                            <h3>แหล่งผลิต</h3>
                            <p><?php echo h($origin); ?> · <?php echo h($category); ?></p>
                        </div>
                        <div class="pi-card">
                            <span class="pi-card__index">02</span>
                            <h3>เหมาะสำหรับ</h3>
                            <p><?php echo h(product_shorten_text($summary, 160)); ?></p>
                        </div>
                        <?php if (!empty($highlights)): ?>
                        <div class="pi-card">
                            <span class="pi-card__index">03</span>
                            <h3>จุดเด่น</h3>
                            <p><?php echo h(product_shorten_text($highlights[0], 160)); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($variants)): ?>
                <div class="flex flex-wrap gap-2 mt-8" role="tablist" aria-label="รุ่นของ <?php echo h($name); ?>">
                    <?php foreach ($variants as $vIdx => $v): ?>
                        <button type="button" class="pi-variant-chip<?php echo $vIdx === 0 ? ' is-active' : ''; ?>"
                                onclick="spSelectVariant(<?php echo $vIdx; ?>)" role="tab"
                                aria-selected="<?php echo $vIdx === 0 ? 'true' : 'false'; ?>">
                            <?php echo h($v['label']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <script>
                    var spVariantHeroPhotos = <?php echo json_encode(array_map(static fn($v) => $v['heroPhoto'], $variants)); ?>;
                    var spVariantLabels = <?php echo json_encode(array_map(static fn($v) => $v['label'], $variants)); ?>;

                    function spSelectVariant(idx) {
                        document.querySelectorAll('.variant-panel').forEach(function (el) {
                            el.hidden = el.getAttribute('data-variant-index') !== String(idx);
                        });
                        document.querySelectorAll('.pi-variant-chip').forEach(function (el, i) {
                            var active = i === idx;
                            el.classList.toggle('is-active', active);
                            el.setAttribute('aria-selected', active ? 'true' : 'false');
                        });
                        if (window.p3dSetVariant && spVariantLabels[idx]) {
                            window.p3dSetVariant(spVariantLabels[idx]);
                        }
                    }
                </script>
                <?php endif; ?>
            </div>
        </section>

        <!-- Structure — illustrative composition scene; caption is the real product summary, nothing invented -->
        <section class="pi-section pi-structure" aria-label="โครงสร้างผลิตภัณฑ์">
            <div class="pi-section__inner pi-structure__layout">
                <div class="pi-orbit" aria-hidden="true">
                    <div class="pi-orbit__ring"></div>
                    <div class="pi-orbit__ring pi-orbit__ring--inner"></div>
                    <div class="pi-orbit__core"></div>
                    <div class="pi-orbit__bead pi-orbit__bead--1"></div>
                    <div class="pi-orbit__bead pi-orbit__bead--2"></div>
                    <div class="pi-orbit__bead pi-orbit__bead--3"></div>
                </div>
                <div>
                    <span class="pi-eyebrow">Product Composition</span>
                    <h2><?php echo h($name); ?></h2>
                    <p class="pi-dek"><?php echo h(product_shorten_text($summary, 260)); ?></p>
                </div>
            </div>
        </section>

        <!-- Results / highlights — real $highlights data only -->
        <?php
        $anyHighlights = !empty($variants)
            ? array_reduce($variants, static fn($c, $v) => $c || !empty($v['highlights']), false)
            : !empty($highlights);
        ?>
        <?php if ($anyHighlights): ?>
        <section class="pi-section" aria-label="จุดเด่นของผลิตภัณฑ์">
            <div class="pi-section__inner">
                <span class="pi-eyebrow">Highlights</span>
                <h2>จุดเด่นของผลิตภัณฑ์</h2>

                <?php if (!empty($variants)): ?>
                    <?php foreach ($variants as $vIdx => $v): ?>
                        <div class="variant-panel pi-results__track" data-variant-index="<?php echo $vIdx; ?>" <?php echo $vIdx !== 0 ? 'hidden' : ''; ?>>
                            <?php foreach ($v['highlights'] as $hl): ?>
                                <div class="pi-results__item">
                                    <span class="pi-dot-check"><i class="fa-solid fa-check"></i></span>
                                    <span><?php echo h($hl); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="pi-results__track">
                        <?php foreach ($highlights as $hl): ?>
                            <div class="pi-results__item">
                                <span class="pi-dot-check"><i class="fa-solid fa-check"></i></span>
                                <span><?php echo h($hl); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Full description — real $paragraphs data only -->
        <?php
        $anyParagraphs = !empty($variants)
            ? array_reduce($variants, static fn($c, $v) => $c || !empty($v['paragraphs']), false)
            : !empty($paragraphs);
        ?>
        <?php if ($anyParagraphs): ?>
        <section class="pi-section" aria-label="รายละเอียดผลิตภัณฑ์" style="border-top:1px solid #eeedf3;">
            <div class="pi-section__inner pi-prose">
                <h2>รายละเอียดผลิตภัณฑ์</h2>
                <?php if (!empty($variants)): ?>
                    <?php foreach ($variants as $vIdx => $v): ?>
                        <div class="variant-panel" data-variant-index="<?php echo $vIdx; ?>" <?php echo $vIdx !== 0 ? 'hidden' : ''; ?>>
                            <?php foreach (array_slice($v['paragraphs'], 0, 8) as $p): ?>
                                <p><?php echo h($p); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach (array_slice($paragraphs, 0, 8) as $p): ?>
                        <p><?php echo h($p); ?></p>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Gallery + accessible lightbox — real $allImages only -->
        <?php if (!empty($allImages)): ?>
        <section class="pi-section" aria-label="แกลเลอรีสินค้า" style="border-top:1px solid #eeedf3;">
            <div class="pi-section__inner">
                <span class="pi-eyebrow">Gallery</span>
                <h2>ภาพผลิตภัณฑ์</h2>
                <div class="pi-gallery__grid">
                    <?php if ($fullVideo['video'] !== ''): ?>
                        <button type="button" class="pi-gallery__item pi-gallery__item--video"
                                data-lightbox-video="<?php echo h($fullVideo['video']); ?>"
                                data-lightbox-alt="วิดีโอแนะนำ <?php echo h($name); ?>"
                                aria-label="เปิดวิดีโอแนะนำ <?php echo h($name); ?>">
                            <?php if ($fullVideo['poster'] !== ''): ?>
                                <img src="<?php echo h($fullVideo['poster']); ?>" alt="วิดีโอแนะนำ <?php echo h($name); ?>" loading="lazy" decoding="async">
                            <?php endif; ?>
                            <span class="pi-gallery__play" aria-hidden="true"><i class="fa-solid fa-play"></i></span>
                        </button>
                    <?php endif; ?>
                    <?php foreach (array_slice($allImages, 0, 12) as $idx => $imageUrl): ?>
                        <button type="button" class="pi-gallery__item"
                                data-lightbox-src="<?php echo h($imageUrl); ?>"
                                data-lightbox-alt="<?php echo h($name); ?> รูปที่ <?php echo $idx + 1; ?>"
                                aria-label="เปิดรูปที่ <?php echo $idx + 1; ?> แบบเต็มจอ">
                            <img src="<?php echo h($imageUrl); ?>" alt="<?php echo h($name); ?> รูปที่ <?php echo $idx + 1; ?>" loading="lazy" decoding="async">
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- CTA band -->
        <div class="pi-cta">
            <div>
                <h3>ต้องการข้อมูลเพิ่มเติม?</h3>
                <p>ทีมงาน KNA Interpharma พร้อมให้คำปรึกษาโดยตรง</p>
            </div>
            <div class="flex flex-wrap gap-3 shrink-0">
                <a href="/contact.php" class="pi-btn pi-btn--solid"><i class="fa-solid fa-headset"></i> ติดต่อเรา</a>
                <a href="tel:056200890" class="pi-btn pi-btn--glass"><i class="fa-solid fa-phone"></i> 056-200890</a>
            </div>
        </div>

        <!-- Related products — real $relatedItems only -->
        <?php if (!empty($relatedItems)): ?>
        <section class="pi-section" style="padding-top:0;" aria-label="ผลิตภัณฑ์อื่นที่น่าสนใจ">
            <div class="pi-section__inner">
                <div class="flex items-end justify-between mb-2 flex-wrap gap-3">
                    <h2 style="margin:0;">ผลิตภัณฑ์อื่นที่น่าสนใจ</h2>
                    <a href="/product.php" class="text-sm font-semibold hover:underline" style="color:var(--pi-accent);">ดูทั้งหมด →</a>
                </div>
                <div class="pi-related__grid">
                    <?php foreach ($relatedItems as $rel): ?>
                    <?php
                    $relImg   = product_media_url($rel['hero_image'] ?? '', '');
                    $relDesc  = product_shorten_text(product_clean_text((string) ($rel['short_description'] ?? '')), 100);
                    $relTheme = product_brand_theme((string) ($rel['name'] ?? ''), product_clean_text((string) ($rel['short_description'] ?? '')));
                    $relUrl   = product_detail_url((string) ($rel['name'] ?? ''));
                    ?>
                    <a href="<?php echo h($relUrl); ?>" class="pi-related__card block">
                        <div class="pi-related__thumb" style="background:linear-gradient(135deg, <?php echo h($relTheme['soft']); ?> 0%, #ffffff 75%);">
                            <?php if ($relImg !== ''): ?>
                                <img src="<?php echo h($relImg); ?>" alt="<?php echo h($rel['name'] ?? ''); ?>" loading="lazy" decoding="async">
                            <?php else: ?>
                                <i class="fa-solid fa-box-open text-4xl" style="color:#d7d5e0;"></i>
                            <?php endif; ?>
                        </div>
                        <div class="pi-related__body">
                            <h3 class="pi-related__name"><?php echo h($rel['name'] ?? ''); ?></h3>
                            <p class="pi-related__desc"><?php echo h($relDesc !== '' ? $relDesc : 'ดูรายละเอียดเพิ่มเติม'); ?></p>
                            <span class="pi-related__link" style="color:<?php echo h($relTheme['primary']); ?>">ดูรายละเอียด <i class="fa-solid fa-arrow-right text-xs"></i></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Lightbox modal (progressive enhancement — see JS init; images remain directly viewable via their own <img> if JS never runs) -->
        <div class="pi-lightbox" role="dialog" aria-modal="true" aria-label="ภาพสินค้าแบบเต็มจอ">
            <button type="button" class="pi-lightbox__btn pi-lightbox__prev" aria-label="รูปก่อนหน้า"><i class="fa-solid fa-chevron-left"></i></button>
            <img src="" alt="">
            <video controls playsinline hidden></video>
            <button type="button" class="pi-lightbox__btn pi-lightbox__next" aria-label="รูปถัดไป"><i class="fa-solid fa-chevron-right"></i></button>
            <button type="button" class="pi-lightbox__btn pi-lightbox__close" aria-label="ปิด"><i class="fa-solid fa-xmark"></i></button>
        </div>

    </main>
    <?php endif; ?>

    <?php require_once __DIR__ . '/site-footer.php'; ?>
    <script src="/assets/js/product-immersive.js" defer></script>
</body>
</html>
