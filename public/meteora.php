<?php
// METEORA — dedicated cinematic detail page. Static by design: no database
// queries. All copy below is transcribed from METEORA's real product record
// (short_description) and the company's real contact details already used
// elsewhere on this site — nothing here is invented.
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/db.php';

$siteHeaderActive = 'product';
$page_title = 'METEORA - KNA Interpharma';
$page_description = 'METEORA THREAD — ไหมขนนกจากไต้หวัน ยกกระชับ เบาสบาย ผลิตโดย Diamond Biotechnology วัสดุ PDO ปลอดภัยสูง โดย KNA Interpharma';
$page_og_image = '/uploads/products/product_445da58afe7ebb7d.jpg';

// Extra gallery photos are admin-managed via /admin/products.php (METEORA =
// product id 2) — anything uploaded there lands in product_images and is
// appended below after the fixed video/brand/zones/wordmark tiles.
$mtGalleryImages = [];
if ($pdo instanceof PDO) {
    $stmt = $pdo->prepare('SELECT image FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([2]);
    $mtGalleryImages = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="th" class="no-js">
<head>
    <?php require_once __DIR__ . '/partials/site-head.php'; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&family=IBM+Plex+Sans+Thai:wght@300;400;500;600&family=Manrope:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/meteora.css?v=<?php echo (int) @filemtime(__DIR__ . '/assets/css/meteora.css'); ?>">
</head>
<body class="product-detail-page">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <main class="mt-page">
        <canvas class="mt-particles" aria-hidden="true"></canvas>
        <div class="mt-cursor-light" aria-hidden="true"></div>
        <div class="mt-progress" aria-hidden="true"><i></i></div>

        <!-- Hero -->
        <section class="mt-scene mt-hero" id="mtHero" aria-label="METEORA hero">
            <h1 class="mt-visually-hidden">METEORA — Miracle Lifting Thread</h1>
            <video class="mt-hero-video" autoplay muted loop playsinline aria-hidden="true" poster="/uploads/products/meteora-full-poster.jpg">
                <source src="/uploads/products/meteora-hero-loop.mp4" type="video/mp4">
            </video>

            <div class="mt-hero-inner">
                <div class="mt-hero-copy">
                    <p class="mt-kicker">Miracle Lifting Thread</p>
                    <p class="mt-lead">METEORA THREAD นวัตกรรมไหมขนนก คิดค้นและผลิตโดย Diamond Biotechnology มาตรฐานระดับโลกจากไต้หวัน ออกแบบมาจากวัสดุปลอดภัยสูง สลายได้เองตามธรรมชาติ มีเงี่ยงแบบพิเศษเพิ่มประสิทธิภาพการยกกระชับ ผลลัพธ์อยู่ได้ยาวนานถึง 1 ปี</p>
                    <div class="mt-hero-actions">
                        <a href="/contact.php" class="mt-btn mt-btn--solid"><i class="fa-solid fa-envelope" aria-hidden="true"></i> สอบถามข้อมูลสินค้า</a>
                        <a href="/searchpage.php" class="mt-btn mt-btn--glass"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> ค้นหาคลินิก</a>
                        <button type="button" class="mt-btn mt-btn--glass" data-lightbox-video="/uploads/products/meteora-full.mp4" data-lightbox-alt="วิดีโอแนะนำ METEORA">
                            <i class="fa-solid fa-circle-play" aria-hidden="true"></i> ดูวิดีโอเต็ม
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-hero-hint" aria-hidden="true"><span>เลื่อนลงเพื่อดูรายละเอียด</span> <i class="fa-solid fa-chevron-down"></i></div>
        </section>

        <!-- Atmosphere interlude — pinned full screen, ring + orbiting barbs center on the product stage -->
        <section class="mt-scene mt-atmosphere" aria-label="METEORA product">
            <div class="mt-atmosphere-sticky">
                <div class="mt-atmosphere-inner mt-d4">
                    <div class="mt-hero-stage">
                        <div class="mt-layer mt-d0 mt-hero-ring-field" aria-hidden="true"></div>

                        <div class="mt-product-stage mt-product-stage--thread" id="mtProductStage">
                            <img src="/uploads/products/meteora-thread.png" alt="เส้นไหม METEORA เงี่ยง Zigzag Barb" class="mt-product-img mt-product-img--thread">
                        </div>

                        <div class="mt-detail mt-detail--left mt-detail--1">
                            <span class="mt-detail-line" aria-hidden="true"></span>
                            <span>วัสดุ</span>
                            <strong>PDO Thread</strong>
                            <p>ผลิตด้วยวัสดุ Polydioxanone ปลอดภัยสูง มีงานวิจัยรองรับว่ากระตุ้นคอลลาเจนได้นานถึง 1 ปีหลังร้อยไหม</p>
                        </div>
                        <div class="mt-detail mt-detail--right mt-detail--2">
                            <span class="mt-detail-line" aria-hidden="true"></span>
                            <span>โครงสร้างเงี่ยง</span>
                            <strong>Zigzag Barb</strong>
                            <p>เงี่ยงไหมหนาและกว้างขึ้น เพิ่มความแข็งแรงในการยึดเกาะ ทำให้ยกกระชับเนื้อเยื่อผิวได้ดี</p>
                        </div>
                        <div class="mt-detail mt-detail--left mt-detail--3">
                            <span class="mt-detail-line" aria-hidden="true"></span>
                            <span>ทิศทางเงี่ยง</span>
                            <strong>Bi-directional 360°</strong>
                            <p>เงี่ยงแบบ 2 ทิศทาง ล้อมรอบเส้นไหมแบบ 360 องศา เพิ่มการยึดเกาะเนื้อเยื่อได้รอบทิศทาง</p>
                        </div>
                        <div class="mt-detail mt-detail--right mt-detail--4">
                            <span class="mt-detail-line" aria-hidden="true"></span>
                            <span>เทคนิคขึ้นรูป</span>
                            <strong>Molding &amp; Cutting</strong>
                            <p>ผสานลักษณะแบบหล่อเพื่อความแข็งแรงในการยกกระชับ และแบบบากเพื่อเพิ่มพื้นที่เกี่ยวเนื้อเยื่อ</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Product specifications — icon spec grid -->
        <section class="mt-specs" aria-labelledby="mtSpecsTitle">
            <div class="mt-specs-intro">
                <p class="mt-chapter"><span>01</span> Product Specifications</p>
                <h2 id="mtSpecsTitle">ข้อมูลผลิตภัณฑ์<br>METEORA</h2>
            </div>
            <div class="mt-specs-list">
                <article>
                    <div class="mt-spec-icon" aria-hidden="true"><i class="fa-solid fa-award"></i></div>
                    <h3>การจดสิทธิบัตรการออกแบบเงี่ยงไหม</h3>
                    <p>เงี่ยงไหมได้รับการออกแบบเฉพาะและจดสิทธิบัตรอย่างถูกต้อง โดยมีลักษณะเป็นไหมสีน้ำเงิน ช่วยให้แพทย์สามารถมองเห็นและควบคุมทิศทางการทำหัตถการได้อย่างแม่นยำ</p>
                </article>
                <article>
                    <div class="mt-spec-icon" aria-hidden="true"><i class="fa-solid fa-ruler-horizontal"></i></div>
                    <h3>ขนาดของไหม</h3>
                    <p>ผลิตภัณฑ์มีให้เลือก 2 ขนาด ได้แก่ ขนาด 18G และขนาด 21G</p>
                </article>
                <article>
                    <div class="mt-spec-icon" aria-hidden="true"><i class="fa-solid fa-hourglass-half"></i></div>
                    <h3>อายุการใช้งานของผลิตภัณฑ์</h3>
                    <p>จัดเก็บได้ 2 ปี ในสภาพบรรจุภัณฑ์ที่ยังไม่ถูกเปิดใช้งาน หลังทำหัตถการไหมจะค่อยๆ สลายตัวภายในระยะเวลาประมาณ 6-8 เดือน ผลลัพธ์จากการรักษาคงอยู่ได้นานสูงสุดถึง 12 เดือน</p>
                </article>
                <article>
                    <div class="mt-spec-icon" aria-hidden="true"><i class="fa-solid fa-box"></i></div>
                    <h3>บรรจุภัณฑ์</h3>
                    <p>ออกแบบเพื่ออำนวยความสะดวกในการใช้งาน คงไว้ซึ่งมาตรฐานการปราศจากเชื้อ ภายในกล่องบรรจุไหมจำนวน 10 เส้น แต่ละเส้นบรรจุแยกเป็นรายชิ้น (1 เส้น / 1 ซอง)</p>
                </article>
                <article>
                    <div class="mt-spec-icon" aria-hidden="true"><i class="fa-solid fa-syringe"></i></div>
                    <h3>เข็มปลายทู่แบบตัด (L-type)</h3>
                    <p>ปลายเข็มตัดตรง ไม่มีความคมเทียบเท่าเข็มปลายแหลม แต่มีความคมมากกว่าเข็มปลายทู่แบบมน (W-type) ช่วยเพิ่มประสิทธิภาพในการเคลื่อนผ่านชั้นเนื้อเยื่อได้อย่างเหมาะสม</p>
                </article>
            </div>
        </section>

        <!-- Technology / thread architecture -->
        <section class="mt-scene mt-tech" aria-label="โครงสร้างเงี่ยงไหมของ METEORA">
            <div class="mt-tech-sticky">
                <video class="mt-tech-video" id="mtTechVideo" autoplay muted loop playsinline preload="auto" aria-hidden="true">
                    <source src="/uploads/products/meteora-tech-loop.mp4" type="video/mp4">
                </video>
                <div class="mt-tech-heading mt-d4">
                    <p class="mt-chapter"><span>02</span> Thread Architecture</p>
                </div>
                <div class="mt-visually-hidden">
                    <article data-step="1">
                        <small>01 / MATERIAL</small>
                        <strong>PDO</strong>
                        <em>Polydioxanone</em>
                        <p>คุณภาพดี ปลอดภัยสูง สามารถย่อยสลายหรือละลายได้เองตามธรรมชาติ ไม่มีสารตกค้างในผิวหนัง</p>
                    </article>
                    <article data-step="2">
                        <small>02 / BARB DESIGN</small>
                        <strong>Zigzag Barb</strong>
                        <em>Molding + Cutting</em>
                        <p>ผสานเงี่ยงแบบ Molding และ Cutting เพื่อเพิ่มความแข็งแรงและพื้นที่ยึดเกาะเนื้อเยื่อ</p>
                    </article>
                    <article data-step="3">
                        <small>03 / DIRECTION</small>
                        <strong>360°</strong>
                        <em>Bi-directional</em>
                        <p>เงี่ยงแบบ Bi-directional ล้อมรอบเส้นไหมแบบ 360 องศา เพิ่มการยึดเกาะเนื้อเยื่อได้รอบทิศทาง</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- Trust / safety — infinite marquee, matches the ticker style on the other product pages -->
        <div class="mt-trust" aria-label="ความปลอดภัยของ METEORA">
            <div class="mt-trust-track">
                <span class="mt-trust-item"><i class="fa-solid fa-leaf"></i> PDO Polydioxanone</span>
                <span class="mt-trust-item"><i class="fa-solid fa-stamp"></i> อย. ไทย + ไต้หวัน</span>
                <span class="mt-trust-item"><i class="fa-solid fa-industry"></i> Diamond Biotechnology Taiwan</span>
                <span class="mt-trust-item"><i class="fa-solid fa-shield-heart"></i> Sterile Single-Use Pouch</span>
                <span class="mt-trust-item" aria-hidden="true"><i class="fa-solid fa-leaf"></i> PDO Polydioxanone</span>
                <span class="mt-trust-item" aria-hidden="true"><i class="fa-solid fa-stamp"></i> อย. ไทย + ไต้หวัน</span>
                <span class="mt-trust-item" aria-hidden="true"><i class="fa-solid fa-industry"></i> Diamond Biotechnology Taiwan</span>
                <span class="mt-trust-item" aria-hidden="true"><i class="fa-solid fa-shield-heart"></i> Sterile Single-Use Pouch</span>
            </div>
        </div>

        <!-- Gallery -->
        <section class="mt-gallery" aria-labelledby="mtGalleryTitle">
            <div class="mt-gallery-heading">
                <p class="mt-chapter"><span>03</span> Product Visual</p>
                <h2 id="mtGalleryTitle">ภาพผลิตภัณฑ์</h2>
            </div>
            <div class="mt-gallery-grid">
                <button type="button" class="mt-gallery-tile mt-gallery-tile--video" data-lightbox-video="/uploads/products/meteora-full.mp4" data-lightbox-alt="วิดีโอแนะนำ METEORA" aria-label="เปิดวิดีโอแนะนำ METEORA">
                    <img src="/uploads/products/meteora-full-poster.jpg" alt="วิดีโอแนะนำ METEORA" loading="lazy" decoding="async">
                    <span class="mt-gallery-play" aria-hidden="true"><i class="fa-solid fa-play"></i></span>
                    <span>วิดีโอแนะนำ</span>
                </button>
                <button type="button" class="mt-gallery-tile" data-lightbox-src="/uploads/products/product_445da58afe7ebb7d.jpg" data-lightbox-alt="METEORA brand visual" aria-label="เปิดภาพ METEORA แบบเต็มจอ">
                    <img src="/uploads/products/product_445da58afe7ebb7d.jpg" alt="METEORA brand visual" loading="lazy" decoding="async">
                    <span>Brand Visual</span>
                </button>
                <button type="button" class="mt-gallery-tile" data-lightbox-src="/uploads/products/meteora-zones.jpg" data-lightbox-alt="ตำแหน่งยกกระชับด้วย METEORA" aria-label="เปิดภาพตำแหน่งยกกระชับแบบเต็มจอ">
                    <img src="/uploads/products/meteora-zones.jpg" alt="ตำแหน่งยกกระชับด้วย METEORA" loading="lazy" decoding="async">
                    <span>Treatment Zones</span>
                </button>
                <button type="button" class="mt-gallery-tile" style="background:#0b0d1f;" data-lightbox-src="/uploads/products/logo_b8a022812ee848c2.png" data-lightbox-alt="METEORA wordmark" aria-label="เปิดโลโก้ METEORA แบบเต็มจอ">
                    <img src="/uploads/products/logo_b8a022812ee848c2.png" alt="METEORA wordmark" loading="lazy" decoding="async" style="object-fit:contain; padding:2.4rem; background:#0b0d1f;">
                    <span>Wordmark</span>
                </button>
                <?php foreach ($mtGalleryImages as $i => $img): ?>
                <button type="button" class="mt-gallery-tile" data-lightbox-src="/uploads/products/<?php echo h($img); ?>" data-lightbox-alt="METEORA — ภาพผลิตภัณฑ์ <?php echo $i + 1; ?>" aria-label="เปิดภาพผลิตภัณฑ์แบบเต็มจอ">
                    <img src="/uploads/products/<?php echo h($img); ?>" alt="METEORA — ภาพผลิตภัณฑ์ <?php echo $i + 1; ?>" loading="lazy" decoding="async">
                    <span>ภาพผลิตภัณฑ์ <?php echo $i + 1; ?></span>
                </button>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- CTA -->
        <div class="mt-cta">
            <div>
                <h3>ต้องการข้อมูลเพิ่มเติม?</h3>
                <p>ทีมงาน KNA Interpharma พร้อมให้คำปรึกษาโดยตรง</p>
            </div>
            <div class="mt-cta-actions">
                <a href="/contact.php" class="mt-btn mt-btn--solid"><i class="fa-solid fa-headset" aria-hidden="true"></i> ติดต่อเรา</a>
                <a href="tel:056200890" class="mt-btn mt-btn--glass"><i class="fa-solid fa-phone" aria-hidden="true"></i> 056-200890</a>
            </div>
        </div>

        <!-- Lightbox -->
        <div class="mt-lightbox" role="dialog" aria-modal="true" aria-label="ภาพ/วิดีโอสินค้าแบบเต็มจอ">
            <img src="" alt="">
            <video controls playsinline hidden></video>
            <button type="button" class="mt-lightbox__close" aria-label="ปิด"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>
    </main>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
    <script src="/assets/js/meteora.js?v=<?php echo (int) @filemtime(__DIR__ . '/assets/js/meteora.js'); ?>" defer></script>
</body>
</html>
