<?php
// HYABELL — dedicated cinematic family page. Static by design: no database
// queries. All copy below is transcribed from Hyabell's real product record
// (short_description) and the real box photography/ad footage already
// available under uploads/products/hyabell-variants/ — nothing here is invented.
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/db.php';

$siteHeaderActive = 'product';
$page_title = 'Hyabell - KNA Interpharma';
$page_description = 'HYABELL — ตระกูลฟิลเลอร์กรดไฮยาลูโรนิก (HA) เกรดพรีเมี่ยม เทคโนโลยี MPT จากเยอรมนี ครบ 5 สูตร Basic / Deep / Ultra / Lips / Meso โดย KNA Interpharma';
$page_og_image = '/uploads/products/product_33f85ba823296811.jpg';

// Extra gallery photos are admin-managed via /admin/products.php (Hyabell =
// product id 4) — anything uploaded there lands in product_images and is
// appended below after the fixed video/family/ribbons/wordmark tiles.
$hyGalleryImages = [];
if ($pdo instanceof PDO) {
    $stmt = $pdo->prepare('SELECT image FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([4]);
    $hyGalleryImages = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$hyVariants = [
    [
        'key' => 'meso',
        'accent' => '#3ea66b',
        'no' => '01',
        'name' => 'Meso',
        'tagline' => 'Pure Hyaluronic Acid to boost your skin.',
        'image' => '/uploads/products/hyabell-variants/meso.png',
        'bg' => '/uploads/products/hyabell-variants/meso-gel-bg.jpg',
        'specs' => [
            'วัสดุ' => 'Non-crosslinked HA บริสุทธิ์',
            'ระยะเวลาคงอยู่' => 'มากกว่า 3 เดือน',
            'ความพึงพอใจ' => '100% (เดือนที่ 1)',
            'คลินิกอ้างอิง' => 'ADODERM GmbH',
        ],
        'desc' => 'ผลิตภัณฑ์ฟิลเลอร์กรดไฮยาลูโรนิกบริสุทธิ์แบบ non-crosslinked ออกแบบมาเพื่อรักษาริ้วรอย เพิ่มความชุ่มชื้น คืนความอ่อนเยาว์ กระตุ้นการสร้างคอลลาเจน จากเอกสารทางคลินิกของ ADODERM GmbH (Post Market Surveillance) หลังฉีด 1 เดือนผู้เข้ารับการรักษาพึงพอใจ 100% และหลังฉีด 3 เดือนพึงพอใจ 60%',
        'areas' => [
            'ใช้ได้ในทุกบริเวณ เช่น หน้าผาก ใบหน้า ใต้ตา ลำคอ และหลังมือ',
            'เพิ่มความชุ่มชื้น ผิวฉ่ำวาว มีน้ำมีนวล กระชับรูขุมขน',
            'เพิ่มความแข็งแรงและความยืดหยุ่นให้ผิวด้วยการกระตุ้นคอลลาเจน',
            'ลดเลือนริ้วรอย ลดรอยดำ ลดหลุมสิว',
        ],
    ],
    [
        'key' => 'lips',
        'accent' => '#e5484d',
        'no' => '02',
        'name' => 'Lips',
        'tagline' => 'Elegance and Harmony at its most.',
        'image' => '/uploads/products/hyabell-variants/lips.png',
        'bg' => '/uploads/products/hyabell-variants/lips-gel-bg.jpg',
        'specs' => [
            'วัสดุ' => 'Crosslinked HA',
            'ความเข้มข้น' => '12 mg/ml',
            'ยาชา' => 'Lidocaine 0.3%',
            'ระยะเวลาคงอยู่' => '6–9 เดือน',
        ],
        'desc' => 'เทคโนโลยีการผลิต MPT ให้คุณสมบัติทาง Rheology ที่โดดเด่น ขนาดโมเลกุล 200–350 ไมโครเมตร เนื้อฟิลเลอร์ละเอียด ให้ความเป็นธรรมชาติ ดันยาง่าย กำหนดปริมาณได้แม่นยำ เหมาะสำหรับเติมเต็มในผิวชั้นตื้นถึงระดับกลาง',
        'areas' => [
            'เติมเต็มใต้ตา ริ้วรอยหางตา',
            'เพิ่มความชุ่มชื้นให้ผิว ทำให้ผิวกระจ่างใสฉ่ำฟู',
            'แก้ปัญหาริ้วรอยร่องตื้น หลุมสิว',
            'เพิ่มโวลลุ่มให้กับริมฝีปาก',
        ],
    ],
    [
        'key' => 'basic',
        'accent' => '#f2994a',
        'no' => '03',
        'name' => 'Basic',
        'tagline' => 'For lips volume and first signs of age.',
        'image' => '/uploads/products/hyabell-variants/basic.png',
        'bg' => '/uploads/products/hyabell-variants/basic-gel-bg.jpg',
        'specs' => [
            'วัสดุ' => 'Crosslinked HA',
            'ความเข้มข้น' => '16.0 mg/ml',
            'ยาชา' => 'Lidocaine 0.3%',
            'ระยะเวลาคงอยู่' => '9–12 เดือน',
        ],
        'desc' => 'ผลิตด้วยเทคโนโลยี MPT ได้ฟิลเลอร์เกรดพรีเมี่ยม โดดเด่นเรื่องคุณสมบัติ Rheology ขนาดโมเลกุล 200–350 ไมโครเมตร ดันยาง่าย ความคงตัวสูง เป็นธรรมชาติ เหมาะสำหรับการเติมเต็มในชั้นผิวระดับกลาง เกลี่ยง่าย ไม่เป็นก้อน',
        'areas' => [
            'เติมเต็มบริเวณใต้ตา ร่องแก้ม',
            'เพิ่มโวลลุ่มเพื่อความอวบอิ่มและมีชีวิตชีวาให้กับริมฝีปาก',
            'จัดรูปทรงริมฝีปากเพื่อริมฝีปากที่สวยมีเสน่ห์ คงรูปเป็นธรรมชาติ',
        ],
    ],
    [
        'key' => 'deep',
        'accent' => '#2f9fd8',
        'no' => '04',
        'name' => 'Deep',
        'tagline' => 'Ideal for those who look for volume and first time patients.',
        'image' => '/uploads/products/hyabell-variants/deep.png',
        'bg' => '/uploads/products/hyabell-variants/deep-gel-bg.jpg',
        'specs' => [
            'วัสดุ' => 'Crosslinked HA',
            'ความเข้มข้น' => '20 mg/ml',
            'ยาชา' => 'Lidocaine 0.3%',
            'ระยะเวลาคงอยู่' => '9–12 เดือน',
        ],
        'desc' => 'ผ่านกระบวนการผลิตด้วยเทคโนโลยี MPT ขนาดโมเลกุล 200–350 ไมโครเมตร ดันยาง่าย ฉีดง่าย ควบคุมปริมาณได้แม่นยำ ความแข็งแรงของพันธะสูง เพิ่มโวลลุ่มและยกกระชับได้ดีเยี่ยม เกลี่ยง่าย กลืนกับผิวได้ดี ไม่เป็นก้อน',
        'areas' => [
            'เติมเต็มบริเวณขมับ ร่องแก้มชั้นลึก',
            'ยกกระชับบริเวณใบหน้าส่วนกลาง',
            'เติมเต็มและจัดรูปทรงบริเวณคางเพื่อรูปทรงที่เนียนสวยเป็นธรรมชาติ',
            'ปรับโครงรูปกรอบหน้า',
        ],
    ],
    [
        'key' => 'ultra',
        'accent' => '#9567ff',
        'no' => '05',
        'name' => 'Ultra',
        'tagline' => 'High needs of volume and face contouring.',
        'image' => '/uploads/products/hyabell-variants/ultra.png',
        'bg' => '/uploads/products/hyabell-variants/ultra-gel-bg.jpg',
        'specs' => [
            'วัสดุ' => 'Crosslinked HA เกรดพรีเมี่ยม',
            'ความเข้มข้น' => '24 mg/ml (สูงสุด)',
            'ยาชา' => 'Lidocaine 0.3%',
            'ระยะเวลาคงอยู่' => '12–18 เดือน',
        ],
        'desc' => 'ความเข้มข้นของ HA สูงที่สุดในตระกูล การจัดเรียงตัวของโมเลกุลเป็นระเบียบ ความแข็งแรงของพันธะสูงที่สุด ส่งผลให้ความสามารถในการยกกระชับดีเยี่ยม คุณสมบัติทาง Rheology ดีที่สุด ดันยาง่าย ฉีดง่าย เหมาะสำหรับฉีดในระดับชั้นชิดกระดูกเพื่อประสิทธิภาพการยกกระชับสูงสุด',
        'areas' => [
            'เติมเต็มบริเวณขมับ ยกกระชับใบหน้าส่วนกลาง (Mid-face, Zygomatic)',
            'เพิ่มโวลลุ่มบริเวณคางเพื่อความพุ่งสวย (Projection)',
            'เสริมรูปและปรับโครงสร้างกรอบหน้าบริเวณกราม',
        ],
    ],
];
?>
<!DOCTYPE html>
<html lang="th" class="no-js">
<head>
    <?php require_once __DIR__ . '/partials/site-head.php'; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&family=IBM+Plex+Sans+Thai:wght@300;400;500;600&family=Manrope:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/hyabell.css?v=<?php echo (int) @filemtime(__DIR__ . '/assets/css/hyabell.css'); ?>">
</head>
<body class="product-detail-page">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <main class="hy-page">
        <canvas class="hy-particles" aria-hidden="true"></canvas>
        <div class="hy-cursor-light" aria-hidden="true"></div>

        <!-- Hero -->
        <section class="hy-hero" id="hyHero" aria-label="Hyabell hero">
            <video class="hy-hero-video" autoplay muted loop playsinline aria-hidden="true" poster="/uploads/products/hyabell-hero-poster.jpg">
                <source src="/uploads/products/hyabell-hero-loop.mp4" type="video/mp4">
            </video>

            <div class="hy-hero-inner">
                <div class="hy-hero-copy">
                    <p class="hy-kicker">The Soft-Tissue Filler Family</p>
                    <h1 class="hy-title"><span>Hyabell</span></h1>
                    <p class="hy-lead">Hyabell ตระกูลฟิลเลอร์กรดไฮยาลูโรนิก (Hyaluronic Acid) เกรดพรีเมี่ยม ผลิตด้วยเทคโนโลยี MPT จากประเทศเยอรมนี ครบทั้ง 5 สูตรในตระกูลเดียว ตั้งแต่เติมเต็มริมฝีปากไปจนถึงปรับโครงหน้าเชิงลึก และ Meso สำหรับเพิ่มความชุ่มชื้นให้ผิว</p>
                    <div class="hy-hero-actions">
                        <a href="/contact.php" class="hy-btn hy-btn--solid"><i class="fa-solid fa-envelope" aria-hidden="true"></i> สอบถามข้อมูลสินค้า</a>
                        <a href="/searchpage.php" class="hy-btn hy-btn--glass"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> ค้นหาคลินิก</a>
                        <button type="button" class="hy-btn hy-btn--glass" data-lightbox-video="/uploads/products/hyabell-full.mp4" data-lightbox-alt="วิดีโอแนะนำ Hyabell">
                            <i class="fa-solid fa-circle-play" aria-hidden="true"></i> ดูวิดีโอเต็ม
                        </button>
                    </div>
                </div>
            </div>

            <div class="hy-hero-hint" aria-hidden="true"><span>เลื่อนลงเพื่อดูรายละเอียด</span> <i class="fa-solid fa-chevron-down"></i></div>
        </section>

        <!-- Family intro -->
        <section class="hy-intro" aria-labelledby="hyIntroTitle">
            <div class="hy-intro-inner">
                <p class="hy-chapter"><span>00</span> One Family, Five Formulations</p>
                <h2 id="hyIntroTitle">เลือกความเข้มข้นของ HA ให้เหมาะกับแต่ละชั้นผิว<br>ตั้งแต่ริมฝีปากไปจนถึงโครงหน้าเชิงลึก</h2>
                <p class="hy-intro-lead">ทุกสูตรผลิตด้วยเทคโนโลยี MPT (Micro Particle Technology) จากประเทศเยอรมนี ผสมยาชา Lidocaine เพื่อลดความเจ็บขณะฉีด บรรจุแบบ 1 ซองปลอดเชื้อต่อ 1 syringe</p>
                <div class="hy-intro-swatches">
                    <?php foreach ($hyVariants as $v): ?>
                    <span class="hy-swatch" style="--hy-swatch-color: <?php echo h($v['accent']); ?>"><i></i> Hyabell <?php echo h($v['name']); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Variant sections -->
        <?php foreach ($hyVariants as $i => $v): ?>
        <section class="hy-variant hy-reveal<?php echo $i % 2 === 1 ? ' hy-variant--right' : ''; ?>" style="--hy-v-accent: <?php echo h($v['accent']); ?>" aria-labelledby="hyVariant<?php echo h($v['key']); ?>Title">
            <?php if (!empty($v['bg'])): ?>
            <img class="hy-variant-bgphoto" src="<?php echo h($v['bg']); ?>?v=<?php echo (int) @filemtime(__DIR__ . $v['bg']); ?>" alt="" aria-hidden="true" loading="lazy" decoding="async">
            <?php endif; ?>
            <div class="hy-variant-inner">
                <div class="hy-variant-visual">
                    <div class="hy-variant-stage">
                        <div class="hy-variant-panel" aria-hidden="true"></div>
                        <div class="hy-variant-glow" aria-hidden="true"></div>
                        <img class="hy-variant-img" src="<?php echo h($v['image']); ?>" alt="Hyabell <?php echo h($v['name']); ?>" loading="lazy" decoding="async">
                        <div class="hy-variant-floor" aria-hidden="true"></div>
                    </div>
                </div>
                <div class="hy-variant-content">
                    <span class="hy-variant-no"><?php echo h($v['no']); ?> / 05</span>
                    <h2 class="hy-variant-name" id="hyVariant<?php echo h($v['key']); ?>Title">Hyabell <span><?php echo h($v['name']); ?></span></h2>
                    <p class="hy-variant-tag">&ldquo;<?php echo h($v['tagline']); ?>&rdquo;</p>
                    <ul class="hy-variant-specs">
                        <?php foreach ($v['specs'] as $label => $value): ?>
                        <li><span><?php echo h($label); ?></span><strong><?php echo h($value); ?></strong></li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="hy-variant-desc"><?php echo h($v['desc']); ?></p>
                    <ul class="hy-variant-areas">
                        <?php foreach ($v['areas'] as $area): ?>
                        <li><?php echo h($area); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </section>
        <?php endforeach; ?>

        <!-- Trust / safety — infinite marquee, matches the ticker style on the other product pages -->
        <div class="hy-trust" aria-label="ความปลอดภัยของ Hyabell">
            <div class="hy-trust-track">
                <span class="hy-trust-item"><i class="fa-solid fa-leaf"></i> Hyaluronic Acid เกรดพรีเมี่ยม</span>
                <span class="hy-trust-item"><i class="fa-solid fa-flask"></i> เทคโนโลยี MPT</span>
                <span class="hy-trust-item"><i class="fa-solid fa-industry"></i> Made in Germany — ADODERM GmbH</span>
                <span class="hy-trust-item"><i class="fa-solid fa-syringe"></i> ผสม Lidocaine ลดความเจ็บ</span>
                <span class="hy-trust-item"><i class="fa-solid fa-shield-heart"></i> Sterile Single-Use Syringe</span>
                <span class="hy-trust-item" aria-hidden="true"><i class="fa-solid fa-leaf"></i> Hyaluronic Acid เกรดพรีเมี่ยม</span>
                <span class="hy-trust-item" aria-hidden="true"><i class="fa-solid fa-flask"></i> เทคโนโลยี MPT</span>
                <span class="hy-trust-item" aria-hidden="true"><i class="fa-solid fa-industry"></i> Made in Germany — ADODERM GmbH</span>
                <span class="hy-trust-item" aria-hidden="true"><i class="fa-solid fa-syringe"></i> ผสม Lidocaine ลดความเจ็บ</span>
                <span class="hy-trust-item" aria-hidden="true"><i class="fa-solid fa-shield-heart"></i> Sterile Single-Use Syringe</span>
            </div>
        </div>

        <!-- Gallery -->
        <section class="hy-gallery" aria-labelledby="hyGalleryTitle">
            <div class="hy-gallery-heading">
                <p class="hy-chapter"><span>06</span> Product Visual</p>
                <h2 id="hyGalleryTitle">ภาพผลิตภัณฑ์</h2>
            </div>
            <div class="hy-gallery-grid">
                <button type="button" class="hy-gallery-tile hy-gallery-tile--video" data-lightbox-video="/uploads/products/hyabell-full.mp4" data-lightbox-alt="วิดีโอแนะนำ Hyabell" aria-label="เปิดวิดีโอแนะนำ Hyabell">
                    <img src="/uploads/products/hyabell-full-poster.jpg" alt="วิดีโอแนะนำ Hyabell" loading="lazy" decoding="async">
                    <span class="hy-gallery-play" aria-hidden="true"><i class="fa-solid fa-play"></i></span>
                    <span>วิดีโอแนะนำ</span>
                </button>
                <button type="button" class="hy-gallery-tile" data-lightbox-src="/uploads/products/hyabell-family-dark.jpg" data-lightbox-alt="Hyabell family — Basic, Deep, Ultra, Lips, Meso" aria-label="เปิดภาพ Hyabell family แบบเต็มจอ">
                    <img src="/uploads/products/hyabell-family-dark.jpg" alt="Hyabell family — Basic, Deep, Ultra, Lips, Meso" loading="lazy" decoding="async">
                    <span>Family — 5 Formulations</span>
                </button>
                <button type="button" class="hy-gallery-tile" data-lightbox-src="/uploads/products/hyabell-ribbons.jpg" data-lightbox-alt="Hyabell each variant box" aria-label="เปิดภาพกล่องสินค้าแบบเต็มจอ">
                    <img src="/uploads/products/hyabell-ribbons.jpg" alt="Hyabell each variant box" loading="lazy" decoding="async">
                    <span>Product Line</span>
                </button>
                <button type="button" class="hy-gallery-tile" style="background:#0b0d1f;" data-lightbox-src="/uploads/products/logo_4308458c4fc28eba.png" data-lightbox-alt="Hyabell wordmark" aria-label="เปิดโลโก้ Hyabell แบบเต็มจอ">
                    <img src="/uploads/products/logo_4308458c4fc28eba.png" alt="Hyabell wordmark" loading="lazy" decoding="async" style="object-fit:contain; padding:2.4rem; background:#0b0d1f;">
                    <span>Wordmark</span>
                </button>
                <?php foreach ($hyGalleryImages as $i => $img): ?>
                <button type="button" class="hy-gallery-tile" data-lightbox-src="/uploads/products/<?php echo h($img); ?>" data-lightbox-alt="Hyabell — ภาพผลิตภัณฑ์ <?php echo $i + 1; ?>" aria-label="เปิดภาพผลิตภัณฑ์แบบเต็มจอ">
                    <img src="/uploads/products/<?php echo h($img); ?>" alt="Hyabell — ภาพผลิตภัณฑ์ <?php echo $i + 1; ?>" loading="lazy" decoding="async">
                    <span>ภาพผลิตภัณฑ์ <?php echo $i + 1; ?></span>
                </button>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Lightbox -->
        <div class="hy-lightbox" role="dialog" aria-modal="true" aria-label="ภาพ/วิดีโอสินค้าแบบเต็มจอ">
            <img src="" alt="">
            <video controls playsinline hidden></video>
            <button type="button" class="hy-lightbox__close" aria-label="ปิด"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>
    </main>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
    <script src="/assets/js/hyabell.js?v=<?php echo (int) @filemtime(__DIR__ . '/assets/js/hyabell.js'); ?>" defer></script>
</body>
</html>
