<?php
// NeoFilera — dedicated cinematic detail page. Static by design: no database
// queries. All copy below is transcribed from NeoFilera's real product record
// (short_description) and the company's real contact details already used
// elsewhere on this site — nothing here is invented.
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/db.php';

$siteHeaderActive = 'product';
$page_title = 'NeoFilera - KNA Interpharma';
$page_description = 'NeoFilera — The 1st Universal Biostimulator for Face & Body. Advanced Hybrid PLA ประกอบด้วย PDLLA 150 mg และ CMC 50 mg โดย KNA Interpharma';
$page_og_image = '/uploads/products/product_bf0374b5ad2dce7a.jpg';

// Extra gallery photos are admin-managed via /admin/products.php (NeoFilera
// = product id 1) — anything uploaded there lands in product_images and is
// appended below after the fixed video/brand/wordmark/3D tiles.
$nfGalleryImages = [];
if ($pdo instanceof PDO) {
    $stmt = $pdo->prepare('SELECT image FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([1]);
    $nfGalleryImages = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="th" class="no-js">
<head>
    <?php require_once __DIR__ . '/partials/site-head.php'; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&family=IBM+Plex+Sans+Thai:wght@300;400;500;600&family=Manrope:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/neofilera.css?v=<?php echo (int) @filemtime(__DIR__ . '/assets/css/neofilera.css'); ?>">
</head>
<body class="product-detail-page">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <main class="nf-page">
        <canvas class="nf-particles" aria-hidden="true"></canvas>
        <div class="nf-cursor-light" aria-hidden="true"></div>
        <div class="nf-progress" aria-hidden="true"><i></i></div>

        <!-- Hero -->
        <section class="nf-scene nf-hero" id="nfHero" aria-label="NeoFilera hero">
            <video class="nf-hero-video" autoplay muted loop playsinline aria-hidden="true" poster="/uploads/products/neofilera-full-poster.jpg">
                <source src="/uploads/products/neofilera-hero-loop.mp4" type="video/mp4">
            </video>

            <div class="nf-hero-inner">
                <div class="nf-hero-copy">
                    <p class="nf-kicker">The 1st Universal Biostimulator</p>
                    <p class="nf-lead">สาร PDLLA (Poly-D, L-lactic-acid) ที่ช่วยเติมเต็มผิวให้เรียบเนียน ลดเลือนริ้วรอย ซ่อมแซมผิวได้อย่างล้ำลึก ปรับสีผิวให้กระจ่างใส อิ่มฟู ดูอ่อนเยาว์อย่างเป็นธรรมชาติ สำหรับใบหน้าและร่างกาย</p>
                    <div class="nf-hero-actions">
                        <a href="/contact.php" class="nf-btn nf-btn--solid"><i class="fa-solid fa-envelope" aria-hidden="true"></i> สอบถามข้อมูลสินค้า</a>
                        <a href="/searchpage.php" class="nf-btn nf-btn--glass"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> ค้นหาคลินิก</a>
                        <button type="button" class="nf-btn nf-btn--glass" data-lightbox-video="/uploads/products/neofilera-full.mp4" data-lightbox-alt="วิดีโอแนะนำ NeoFilera">
                            <i class="fa-solid fa-circle-play" aria-hidden="true"></i> ดูวิดีโอเต็ม
                        </button>
                    </div>
                </div>
            </div>

            <div class="nf-hero-hint" aria-hidden="true"><span>เลื่อนลงเพื่อดูรายละเอียด</span> <i class="fa-solid fa-chevron-down"></i></div>
        </section>

        <!-- Atmosphere interlude — pinned full screen: grid/halo/beads center on the
             product stage itself (not the section), so they stay aligned regardless
             of the title above. Section scroll-holds while the vial→box crossfade runs. -->
        <section class="nf-scene nf-atmosphere" aria-label="NeoFilera 3D model">
            <div class="nf-atmosphere-sticky">
                <div class="nf-atmosphere-inner nf-d4">
                    <h1 class="nf-title nf-title--center">
                        <span>Neo</span><span class="nf-title-accent">Filera</span>
                    </h1>

                    <div class="nf-hero-stage">
                        <div class="nf-layer nf-d0 nf-hero-grid" aria-hidden="true"></div>
                        <div class="nf-layer nf-d1 nf-hero-atmosphere" aria-hidden="true">
                            <div class="nf-halo"></div>
                            <div class="nf-halo nf-halo--inner"></div>
                        </div>

                        <div class="nf-bead-ring nf-bead-ring--outer" aria-hidden="true">
                            <div class="nf-bead-orbit" style="--a:0deg"><span class="nf-bead" style="--s:1.3"></span></div>
                            <div class="nf-bead-orbit" style="--a:60deg"><span class="nf-bead" style="--s:0.8"></span></div>
                            <div class="nf-bead-orbit" style="--a:120deg"><span class="nf-bead" style="--s:1.1"></span></div>
                            <div class="nf-bead-orbit" style="--a:180deg"><span class="nf-bead" style="--s:0.7"></span></div>
                            <div class="nf-bead-orbit" style="--a:240deg"><span class="nf-bead" style="--s:1.4"></span></div>
                            <div class="nf-bead-orbit" style="--a:300deg"><span class="nf-bead" style="--s:0.9"></span></div>
                        </div>
                        <div class="nf-bead-ring nf-bead-ring--inner" aria-hidden="true">
                            <div class="nf-bead-orbit" style="--a:30deg"><span class="nf-bead" style="--s:1.2"></span></div>
                            <div class="nf-bead-orbit" style="--a:150deg"><span class="nf-bead" style="--s:0.75"></span></div>
                            <div class="nf-bead-orbit" style="--a:270deg"><span class="nf-bead" style="--s:1"></span></div>
                        </div>

                        <div class="nf-product-stage" id="nfProductStage">
                            <img src="/uploads/products/neofilera-vial.png" alt="ขวด NeoFilera" class="nf-product-img nf-product-img--vial" id="nfProductVial">
                            <img src="/uploads/products/neofilera-box.png" alt="กล่องและขวด NeoFilera" class="nf-product-img nf-product-img--box" id="nfProductBox">
                        </div>

                        <div class="nf-chip nf-chip--a">
                            <span>องค์ประกอบ</span>
                            <strong>PDLLA 150 mg</strong>
                            <small>Poly-D, L-Lactic Acid</small>
                        </div>
                        <div class="nf-chip nf-chip--b">
                            <span>องค์ประกอบ</span>
                            <strong>CMC 50 mg</strong>
                            <small>Carboxymethyl Cellulose</small>
                        </div>
                        <div class="nf-chip nf-chip--c">
                            <span>การรับรอง</span>
                            <strong>อย. ไทย + ไต้หวัน</strong>
                            <small>Biodegradable Polymer</small>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statement — scroll-holds while the words light up, then continues -->
        <section class="nf-scene nf-statement" aria-labelledby="nfStatementTitle">
            <div class="nf-statement-sticky">
                <div class="nf-layer nf-d0 nf-statement-field" aria-hidden="true"></div>
                <div class="nf-statement-inner nf-d4">
                    <p class="nf-chapter"><span>01</span> Universal Biostimulator</p>
                    <h2 id="nfStatementTitle" class="nf-words">เทคโนโลยีความงามตัวใหม่ที่ช่วยเติมเต็มผิวให้เรียบเนียน ลดเลือนริ้วรอย ซ่อมแซมผิวได้อย่างล้ำลึก พร้อมปรับสีผิวให้กระจ่างใสอย่างเป็นธรรมชาติ</h2>
                </div>
            </div>
        </section>

        <!-- Technology / composition -->
        <section class="nf-scene nf-tech" aria-label="องค์ประกอบของ NeoFilera">
            <div class="nf-tech-sticky">
                <canvas class="nf-tech-frame" id="nfTechFrame" data-frame-base="/uploads/products/neofilera-tech-frames/frame-" data-frame-count="150" aria-hidden="true"></canvas>
                <div class="nf-tech-heading nf-d4">
                    <p class="nf-chapter"><span>02</span> Advanced Hybrid PLA</p>
                    <h2>องค์ประกอบสอง ส่วน ระบบเดียว</h2>
                </div>
                <div class="nf-visually-hidden">
                    <article data-step="1">
                        <small>01 / COMPOSITION</small>
                        <strong>PDLLA</strong>
                        <em>150 mg</em>
                        <p>ทรงกลมไม่มีรูพรุน ช่วยกระตุ้นการสร้างคอลลาเจน (Collagen biostimulator) ไม่เป็นก้อนหรือตกค้างภายในร่างกาย</p>
                    </article>
                    <article data-step="2">
                        <small>02 / HYDRATION</small>
                        <strong>CMC</strong>
                        <em>50 mg</em>
                        <p>สารละลายธรรมชาติ เพิ่มความชุ่มชื้นและการอุ้มน้ำ เห็นผลลัพธ์ได้ทันทีหลังทำ ปลอดภัยเข้ากันได้ดีกับร่างกาย</p>
                    </article>
                    <article data-step="3">
                        <small>03 / STRUCTURE</small>
                        <strong>Compact Beads</strong>
                        <em>Particle Design</em>
                        <p>อนุภาคทรงกลมแบบ Compact beads กระจายตัวสม่ำเสมอ ป้องกันการเกาะตัว ลดโอกาสเกิดก้อนแข็งหลังฉีด (Granuloma)</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- Trust / safety — infinite marquee, matches the ticker style on the other product pages -->
        <div class="nf-trust" aria-label="ความปลอดภัยของ NeoFilera">
            <div class="nf-trust-track">
                <span class="nf-trust-item"><i class="fa-solid fa-leaf"></i> Biodegradable Polymer</span>
                <span class="nf-trust-item"><i class="fa-solid fa-stamp"></i> อย. ไทย + ไต้หวัน</span>
                <span class="nf-trust-item"><i class="fa-solid fa-vial-circle-check"></i> Cytotoxicity Test</span>
                <span class="nf-trust-item"><i class="fa-solid fa-hand-dots"></i> Skin Sensitization Test</span>
                <span class="nf-trust-item"><i class="fa-solid fa-shield-heart"></i> Irritation Test</span>
                <span class="nf-trust-item" aria-hidden="true"><i class="fa-solid fa-leaf"></i> Biodegradable Polymer</span>
                <span class="nf-trust-item" aria-hidden="true"><i class="fa-solid fa-stamp"></i> อย. ไทย + ไต้หวัน</span>
                <span class="nf-trust-item" aria-hidden="true"><i class="fa-solid fa-vial-circle-check"></i> Cytotoxicity Test</span>
                <span class="nf-trust-item" aria-hidden="true"><i class="fa-solid fa-hand-dots"></i> Skin Sensitization Test</span>
                <span class="nf-trust-item" aria-hidden="true"><i class="fa-solid fa-shield-heart"></i> Irritation Test</span>
            </div>
        </div>

        <!-- Gallery -->
        <section class="nf-gallery" aria-labelledby="nfGalleryTitle">
            <div class="nf-gallery-heading">
                <p class="nf-chapter"><span>03</span> Product Visual</p>
                <h2 id="nfGalleryTitle">ภาพผลิตภัณฑ์</h2>
            </div>
            <div class="nf-gallery-grid">
                <button type="button" class="nf-gallery-tile nf-gallery-tile--video" data-lightbox-video="/uploads/products/neofilera-full.mp4" data-lightbox-alt="วิดีโอแนะนำ NeoFilera" aria-label="เปิดวิดีโอแนะนำ NeoFilera">
                    <img src="/uploads/products/neofilera-full-poster.jpg" alt="วิดีโอแนะนำ NeoFilera" loading="lazy" decoding="async">
                    <span class="nf-gallery-play" aria-hidden="true"><i class="fa-solid fa-play"></i></span>
                    <span>วิดีโอแนะนำ</span>
                </button>
                <button type="button" class="nf-gallery-tile" data-lightbox-src="/uploads/products/product_bf0374b5ad2dce7a.jpg" data-lightbox-alt="NeoFilera brand visual" aria-label="เปิดภาพ NeoFilera แบบเต็มจอ">
                    <img src="/uploads/products/product_bf0374b5ad2dce7a.jpg" alt="NeoFilera brand visual" loading="lazy" decoding="async">
                    <span>Brand Visual</span>
                </button>
                <button type="button" class="nf-gallery-tile" style="background:#0b0a08;" data-lightbox-src="/uploads/products/logo_83e5d3e1f1dc02dd.png" data-lightbox-alt="NeoFilera wordmark" aria-label="เปิดโลโก้ NeoFilera แบบเต็มจอ">
                    <img src="/uploads/products/logo_83e5d3e1f1dc02dd.png" alt="NeoFilera wordmark" loading="lazy" decoding="async" style="object-fit:contain; padding:2.4rem; background:#0b0a08;">
                    <span>Wordmark</span>
                </button>
                <a href="#nfHero" class="nf-gallery-tile nf-gallery-tile--3d" aria-label="เลื่อนกลับไปดูโมเดล 3 มิติที่ Hero">
                    <i class="fa-solid fa-cube" aria-hidden="true"></i>
                    <p>ชมโมเดล 3 มิติ</p>
                </a>
                <?php foreach ($nfGalleryImages as $i => $img): ?>
                <button type="button" class="nf-gallery-tile" data-lightbox-src="/uploads/products/<?php echo h($img); ?>" data-lightbox-alt="NeoFilera — ภาพผลิตภัณฑ์ <?php echo $i + 1; ?>" aria-label="เปิดภาพผลิตภัณฑ์แบบเต็มจอ">
                    <img src="/uploads/products/<?php echo h($img); ?>" alt="NeoFilera — ภาพผลิตภัณฑ์ <?php echo $i + 1; ?>" loading="lazy" decoding="async">
                    <span>ภาพผลิตภัณฑ์ <?php echo $i + 1; ?></span>
                </button>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Lightbox -->
        <div class="nf-lightbox" role="dialog" aria-modal="true" aria-label="ภาพ/วิดีโอสินค้าแบบเต็มจอ">
            <img src="" alt="">
            <video controls playsinline hidden></video>
            <button type="button" class="nf-lightbox__close" aria-label="ปิด"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>
    </main>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
    <script src="/assets/js/neofilera.js?v=<?php echo (int) @filemtime(__DIR__ . '/assets/js/neofilera.js'); ?>" defer></script>
</body>
</html>
