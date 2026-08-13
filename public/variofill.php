<?php
// VARIOFILL — dedicated cinematic detail page. Static by design: no database
// queries. All copy below is transcribed from Variofill's real product record
// (short_description) — nothing here is invented. No ad video exists for this
// product yet, so the hero uses the real product cover photo instead.
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/db.php';

$siteHeaderActive = 'product';
$page_title = 'Variofill - KNA Interpharma';
$page_description = 'VARIOFILL for Gluteal Augmentation — ฟิลเลอร์สำหรับฉีดสะโพกจากเยอรมนี เพียงหนึ่งเดียวที่ผ่าน อย. ในประเทศไทย ความเข้มข้น HA 33 mg/ml โดย KNA Interpharma';
$page_og_image = '/uploads/products/product_0804341d31d48a0b.jpg';

// Extra gallery photos are admin-managed via /admin/products.php (Variofill
// = product id 3) — anything uploaded there lands in product_images and is
// appended below after the fixed brand/pack/wordmark tiles.
$vfGalleryImages = [];
if ($pdo instanceof PDO) {
    $stmt = $pdo->prepare('SELECT image FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([3]);
    $vfGalleryImages = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="th" class="no-js">
<head>
    <?php require_once __DIR__ . '/partials/site-head.php'; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&family=IBM+Plex+Sans+Thai:wght@300;400;500;600&family=Manrope:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/variofill.css?v=<?php echo (int) @filemtime(__DIR__ . '/assets/css/variofill.css'); ?>">
</head>
<body class="product-detail-page">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <main class="vf-page">
        <canvas class="vf-particles" aria-hidden="true"></canvas>
        <div class="vf-cursor-light" aria-hidden="true"></div>

        <!-- Hero -->
        <section class="vf-hero" id="vfHero" aria-label="Variofill hero">
            <img class="vf-hero-image" src="/uploads/products/variofill-cover.jpg" alt="" aria-hidden="true">

            <div class="vf-hero-inner">
                <div class="vf-hero-copy">
                    <p class="vf-kicker">Gluteal Augmentation Filler</p>
                    <h1 class="vf-title">Variofill</h1>
                    <p class="vf-lead">ฟิลเลอร์สำหรับฉีดสะโพกจากประเทศเยอรมนี เพียงหนึ่งเดียวที่ผ่าน อย. ในประเทศไทย ความเข้มข้นของกรดไฮยาลูโรนิกสูงถึง 33 มก./มล. เติมเต็มบั้นท้ายให้กลมเด้งเป็นธรรมชาติ โดยไม่ต้องผ่าตัด</p>
                    <div class="vf-hero-actions">
                        <a href="/contact.php" class="vf-btn vf-btn--solid"><i class="fa-solid fa-envelope" aria-hidden="true"></i> สอบถามข้อมูลสินค้า</a>
                        <a href="/searchpage.php" class="vf-btn vf-btn--glass"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> ค้นหาคลินิก</a>
                    </div>
                </div>
            </div>

            <div class="vf-hero-hint" aria-hidden="true"><span>เลื่อนลงเพื่อดูรายละเอียด</span> <i class="fa-solid fa-chevron-down"></i></div>
        </section>

        <!-- Statement — scroll-holds while the words light up, then continues -->
        <section class="vf-statement" aria-labelledby="vfStatementTitle">
            <div class="vf-statement-sticky">
                <div class="vf-statement-field" aria-hidden="true"></div>
                <div class="vf-statement-inner">
                    <p class="vf-chapter"><span>01</span> Sculptural Volume</p>
                    <h2 id="vfStatementTitle" class="vf-words">สารเติมเต็ม Hyaluronic Acid ความเข้มข้นสูง ออกแบบมาเพื่อสร้างรูปทรงสะโพกที่กลมและเป็นธรรมชาติ โดยไม่ต้องผ่าตัด</h2>
                </div>
            </div>
        </section>

        <!-- Key stats -->
        <section class="vf-stats" aria-labelledby="vfStatsTitle">
            <div class="vf-stats-intro">
                <p class="vf-chapter"><span>02</span> Why Variofill</p>
                <h2 id="vfStatsTitle">ฟิลเลอร์ Variofill ดีอย่างไร</h2>
            </div>
            <div class="vf-stats-list">
                <div class="vf-stat">
                    <span>ความเข้มข้น</span>
                    <strong>33 mg/ml</strong>
                    <p>ความเข้มข้นของ Hyaluronic Acid สูงกว่าฟิลเลอร์ฉีดหน้าทั่วไป ออกแบบมาเฉพาะสำหรับโครงสร้างเนื้อเยื่อสะโพกโดยเฉพาะ</p>
                </div>
                <div class="vf-stat">
                    <span>หัตถการ</span>
                    <strong>Non-Surgical</strong>
                    <p>ไม่ต้องผ่าตัด ไม่มีการดมยาสลบ รอยแผลเล็กที่สุด ใช้เวลาทำหัตถการน้อย ไม่ต้องพักฟื้นนาน</p>
                </div>
                <div class="vf-stat">
                    <span>ระยะเวลาคงอยู่</span>
                    <strong>สูงสุด 2 ปี</strong>
                    <p>หลังจาก 2 ปี ฟิลเลอร์สลายได้เองโดยไม่มีสารตกค้างในร่างกาย และสามารถกลับมาฉีดซ้ำได้</p>
                </div>
            </div>
        </section>

        <!-- Product detail grid -->
        <section class="vf-detail" aria-labelledby="vfDetailTitle">
            <div class="vf-detail-head">
                <div>
                    <p class="vf-chapter"><span>03</span> Product Detail</p>
                    <h2 id="vfDetailTitle">Volume without<br>interruption</h2>
                </div>
                <p>ออกแบบสำหรับผู้ที่ต้องการปรับรูปทรงหรือเพิ่มความกลมนูนของสะโพก เป็นทางเลือกที่ไม่ต้องผ่าตัดและใช้เวลาพักฟื้นน้อย</p>
            </div>
            <div class="vf-detail-list">
                <article>
                    <span>01</span>
                    <h3>High Elasticity</h3>
                    <p>ความยืดหยุ่นและความเหนียวหนืดสูง ช่วยป้องกันไม่ให้เกิดการเคลื่อนย้ายของฟิลเลอร์หลังฉีด</p>
                </article>
                <article>
                    <span>02</span>
                    <h3>Minimal Downtime</h3>
                    <p>ใช้เวลาในการทำหัตถการน้อยมาก ไม่ต้องพักฟื้นนาน กลับไปใช้ชีวิตประจำวันได้ทันที</p>
                </article>
                <article>
                    <span>03</span>
                    <h3>Natural Contour</h3>
                    <p>ผลลัพธ์สะโพกกลม นูนเป็นธรรมชาติ ไร้รอยต่อ ไม่มีผลต่อการตรวจด้วยคลื่นเสียง</p>
                </article>
                <article>
                    <span>04</span>
                    <h3>อย. Registered</h3>
                    <p>ฟิลเลอร์สำหรับฉีดสะโพกเพียงหนึ่งเดียวในประเทศไทยที่ผ่าน อย. ไม่ต้องเสี่ยงกับสารแปลกปลอม</p>
                </article>
            </div>
        </section>

        <!-- Who it's for -->
        <section class="vf-candidates" aria-labelledby="vfCandidatesTitle">
            <div class="vf-candidates-inner">
                <p class="vf-chapter"><span>04</span> Who It's For</p>
                <h2 id="vfCandidatesTitle">หัตถการนี้เหมาะกับใครบ้าง</h2>
                <ul class="vf-candidate-list">
                    <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> ผู้ที่ต้องการปรับรูปร่างหรือสร้างเสริมสะโพกให้ดูมีทรงมากขึ้น</li>
                    <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> ทำได้ทั้งผู้หญิงและผู้ชาย</li>
                    <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> ผู้ที่ไม่มีเวลาพักฟื้น หรือมีเวลาพักฟื้นน้อย</li>
                    <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> ผู้ที่มีเนื้อสะโพกด้านข้างน้อย สะโพกบุ๋ม ไม่ผาย (Hip Dips)</li>
                    <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> ผู้ที่ไม่ต้องการเติมไขมันหรือใส่ซิลิโคนสะโพก</li>
                    <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> ผู้ที่กลัวการผ่าตัด หรือไม่ต้องการรอยแผลเป็น</li>
                </ul>
            </div>
        </section>

        <!-- Filler vs fat injection -->
        <section class="vf-compare" aria-labelledby="vfCompareTitle">
            <div class="vf-compare-inner">
                <p class="vf-chapter"><span>05</span> Filler vs. Fat Transfer</p>
                <h2 id="vfCompareTitle">ฟิลเลอร์สะโพกต่างจากการฉีดไขมันสะโพกอย่างไร</h2>
                <div class="vf-compare-grid">
                    <div class="vf-compare-col vf-compare-col--accent">
                        <h3>Variofill</h3>
                        <ul>
                            <li>ไม่ต้องดูดไขมันจากส่วนอื่นของร่างกาย</li>
                            <li>มีแผลจากการฉีดเพียงตำแหน่งเดียว</li>
                            <li>พักฟื้นสั้น กลับไปใช้ชีวิตได้เร็ว</li>
                            <li>เห็นผลลัพธ์ชัดเจนทันทีหลังฉีด</li>
                        </ul>
                    </div>
                    <div class="vf-compare-col">
                        <h3>การฉีดไขมันสะโพก</h3>
                        <ul>
                            <li>ต้องดูดไขมันจากส่วนอื่นของร่างกายก่อน</li>
                            <li>มีแผลอย่างน้อย 2 ตำแหน่ง (จุดดูดและจุดฉีด)</li>
                            <li>ระยะพักฟื้นนานกว่า ดูแลหลังทำยากกว่า</li>
                            <li>ไขมันอาจติดไม่แน่นอน เพียง 40–70% ของปริมาณที่ฉีด</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Trust / safety — infinite marquee, matches the ticker style on the other product pages -->
        <div class="vf-trust" aria-label="ความปลอดภัยของ Variofill">
            <div class="vf-trust-track">
                <span class="vf-trust-item"><i class="fa-solid fa-industry"></i> ฟิลเลอร์สะโพกจากเยอรมนี</span>
                <span class="vf-trust-item"><i class="fa-solid fa-stamp"></i> อย. ไทย — หนึ่งเดียวสำหรับฉีดสะโพก</span>
                <span class="vf-trust-item"><i class="fa-solid fa-leaf"></i> Hyaluronic Acid 33 mg/ml</span>
                <span class="vf-trust-item"><i class="fa-solid fa-shield-heart"></i> ไม่ต้องผ่าตัด ไม่ดมยาสลบ</span>
                <span class="vf-trust-item"><i class="fa-solid fa-clock-rotate-left"></i> คงอยู่นานสูงสุด 2 ปี</span>
                <span class="vf-trust-item" aria-hidden="true"><i class="fa-solid fa-industry"></i> ฟิลเลอร์สะโพกจากเยอรมนี</span>
                <span class="vf-trust-item" aria-hidden="true"><i class="fa-solid fa-stamp"></i> อย. ไทย — หนึ่งเดียวสำหรับฉีดสะโพก</span>
                <span class="vf-trust-item" aria-hidden="true"><i class="fa-solid fa-leaf"></i> Hyaluronic Acid 33 mg/ml</span>
                <span class="vf-trust-item" aria-hidden="true"><i class="fa-solid fa-shield-heart"></i> ไม่ต้องผ่าตัด ไม่ดมยาสลบ</span>
                <span class="vf-trust-item" aria-hidden="true"><i class="fa-solid fa-clock-rotate-left"></i> คงอยู่นานสูงสุด 2 ปี</span>
            </div>
        </div>

        <!-- Gallery -->
        <section class="vf-gallery" aria-labelledby="vfGalleryTitle">
            <div class="vf-gallery-heading">
                <p class="vf-chapter"><span>06</span> Product Visual</p>
                <h2 id="vfGalleryTitle">ภาพผลิตภัณฑ์</h2>
            </div>
            <div class="vf-gallery-grid">
                <button type="button" class="vf-gallery-tile" data-lightbox-src="/uploads/products/product_0804341d31d48a0b.jpg" data-lightbox-alt="Variofill brand visual" aria-label="เปิดภาพ Variofill แบบเต็มจอ">
                    <img src="/uploads/products/product_0804341d31d48a0b.jpg" alt="Variofill brand visual" loading="lazy" decoding="async">
                    <span>Brand Visual</span>
                </button>
                <button type="button" class="vf-gallery-tile" data-lightbox-src="/uploads/products/variofill-cover.jpg" data-lightbox-alt="Variofill product pack" aria-label="เปิดภาพกล่องผลิตภัณฑ์แบบเต็มจอ">
                    <img src="/uploads/products/variofill-cover.jpg" alt="Variofill product pack" loading="lazy" decoding="async">
                    <span>Product Pack</span>
                </button>
                <button type="button" class="vf-gallery-tile" style="background:#150a12;" data-lightbox-src="/uploads/products/logo_0265c86b4c258082.png" data-lightbox-alt="Variofill wordmark" aria-label="เปิดโลโก้ Variofill แบบเต็มจอ">
                    <img src="/uploads/products/logo_0265c86b4c258082.png" alt="Variofill wordmark" loading="lazy" decoding="async" style="object-fit:contain; padding:2.4rem; background:#150a12;">
                    <span>Wordmark</span>
                </button>
                <?php foreach ($vfGalleryImages as $i => $img): ?>
                <button type="button" class="vf-gallery-tile" data-lightbox-src="/uploads/products/<?php echo h($img); ?>" data-lightbox-alt="Variofill — ภาพผลิตภัณฑ์ <?php echo $i + 1; ?>" aria-label="เปิดภาพผลิตภัณฑ์แบบเต็มจอ">
                    <img src="/uploads/products/<?php echo h($img); ?>" alt="Variofill — ภาพผลิตภัณฑ์ <?php echo $i + 1; ?>" loading="lazy" decoding="async">
                    <span>ภาพผลิตภัณฑ์ <?php echo $i + 1; ?></span>
                </button>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- CTA -->
        <div class="vf-cta">
            <div>
                <h3>ต้องการข้อมูลเพิ่มเติม?</h3>
                <p>ทีมงาน KNA Interpharma พร้อมให้คำปรึกษาโดยตรง</p>
            </div>
            <div class="vf-cta-actions">
                <a href="/contact.php" class="vf-btn vf-btn--solid"><i class="fa-solid fa-headset" aria-hidden="true"></i> ติดต่อเรา</a>
                <a href="tel:056200890" class="vf-btn vf-btn--glass"><i class="fa-solid fa-phone" aria-hidden="true"></i> 056-200890</a>
            </div>
        </div>

        <!-- Lightbox -->
        <div class="vf-lightbox" role="dialog" aria-modal="true" aria-label="ภาพสินค้าแบบเต็มจอ">
            <img src="" alt="">
            <button type="button" class="vf-lightbox__close" aria-label="ปิด"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>
    </main>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
    <script src="/assets/js/variofill.js?v=<?php echo (int) @filemtime(__DIR__ . '/assets/js/variofill.js'); ?>" defer></script>
</body>
</html>
