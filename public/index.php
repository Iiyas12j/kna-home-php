<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

function media_url($value, $folder = '', $fallback = '')
{
    $value = is_string($value) ? trim($value) : '';
    if ($value === '') {
        return $fallback;
    }

    if (preg_match('#^https?://#i', $value) || str_starts_with($value, '/')) {
        return $value;
    }

    $folder = trim($folder, '/');
    if ($folder !== '') {
        return '/uploads/' . $folder . '/' . rawurlencode($value);
    }

    return '/uploads/' . rawurlencode($value);
}

function tiktok_video_id($url)
{
    if (!is_string($url) || $url === '') {
        return null;
    }

    if (preg_match('#/video/(\d+)#', $url, $m)) {
        return $m[1];
    }

    return null;
}

$slides = [];
$products = [];
$galleryItems = [];
$groupItems = [];
$newsItems = [];
$videoItems = [];
$provinces = [];
$dbError = '';

if ($pdo instanceof PDO) {
    try {
        $slides = $pdo->query('SELECT id, title, desktop_image, mobile_image, sort_order FROM home_slides WHERE is_active = 1 ORDER BY sort_order ASC, id ASC')->fetchAll();
        $products = $pdo->query('SELECT id, name, short_description, hero_image FROM products WHERE is_active = 1 ORDER BY id DESC')->fetchAll();
        $galleryItems = $pdo->query('SELECT id, title, image_path, sort_order FROM website_product_gallery WHERE is_active = 1 ORDER BY sort_order ASC, id ASC LIMIT 8')->fetchAll();
        $groupItems = $pdo->query('SELECT id, title, image_path, sort_order FROM website_product_groups WHERE is_active = 1 ORDER BY sort_order ASC, id ASC LIMIT 8')->fetchAll();
        $newsItems = $pdo->query('SELECT id, title, category, summary, hero_image, published_at, created_at FROM news_events WHERE is_active = 1 ORDER BY COALESCE(published_at, created_at) DESC, id DESC LIMIT 6')->fetchAll();
        $videoItems = $pdo->query(
            "SELECT id, title, video_url, thumbnail, sort_order
             FROM videos
             WHERE is_active = 1
               AND platform = 'tiktok'
               AND access_level = 'public'
             ORDER BY sort_order ASC, id DESC
             LIMIT 8"
        )->fetchAll();
        $provinces = $pdo->query('SELECT DISTINCT province FROM clinics WHERE is_active = 1 AND province IS NOT NULL AND province <> "" ORDER BY province ASC')->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        $dbError = $e->getMessage();
    }
}

if (empty($slides)) {
    $slides = [
        ['title' => 'Slide 1', 'desktop_image' => 'slide-1.jpg', 'mobile_image' => 'slide-m-1.jpg'],
        ['title' => 'Slide 2', 'desktop_image' => 'slide-2.jpg', 'mobile_image' => 'slide-m-2.jpg'],
        ['title' => 'Slide 3', 'desktop_image' => 'slide-3.jpg', 'mobile_image' => 'slide-m-3.jpg'],
    ];
}

$homeProducts = array_slice($products, 0, 4);
$homeNews = array_slice($newsItems, 0, 4);
$homeVideos = array_slice($videoItems, 0, 4);
$search = '';
$province = '';
$selectedProductIds = [];
$siteHeaderActive = 'home';
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KNA Interpharma - Pharmacy & Healthcare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }
        .hero-slider { position: relative; height: 420px; overflow: hidden; }
        @media (min-width: 768px) { .hero-slider { height: 560px; } }
        @media (min-width: 1024px) { .hero-slider { height: 740px; } }
        .slide { position: absolute; inset: 0; opacity: 0; transition: opacity .8s ease; }
        .slide.active { opacity: 1; z-index: 1; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

<?php if ($dbError !== ''): ?>
    <div class="container mx-auto px-4 mt-4">
        <div class="bg-yellow-100 border border-yellow-300 text-yellow-900 rounded-xl p-3 text-sm">
            Database warning: <?php echo h($dbError); ?>
        </div>
    </div>
<?php endif; ?>

<section id="home" class="hero-slider">
    <?php foreach ($slides as $idx => $slide): ?>
        <?php
        $desktop = media_url($slide['desktop_image'] ?? '', 'website', '/uploads/slide-' . (($idx % 5) + 1) . '.jpg');
        $mobile = media_url($slide['mobile_image'] ?? '', 'website', $desktop);
        ?>
        <div class="slide <?php echo $idx === 0 ? 'active' : ''; ?>">
            <img src="<?php echo h($desktop); ?>" alt="<?php echo h($slide['title'] ?? 'Slide'); ?>" class="hidden md:block w-full h-full object-cover">
            <img src="<?php echo h($mobile); ?>" alt="<?php echo h($slide['title'] ?? 'Slide'); ?>" class="block md:hidden w-full h-full object-cover">
        </div>
    <?php endforeach; ?>

    <?php if (count($slides) > 1): ?>
        <button onclick="changeSlide(-1)" class="absolute left-3 md:left-5 top-1/2 -translate-y-1/2 bg-white/70 hover:bg-white w-10 h-10 md:w-12 md:h-12 rounded-full text-gray-700 z-10">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button onclick="changeSlide(1)" class="absolute right-3 md:right-5 top-1/2 -translate-y-1/2 bg-white/70 hover:bg-white w-10 h-10 md:w-12 md:h-12 rounded-full text-gray-700 z-10">
            <i class="fas fa-chevron-right"></i>
        </button>
    <?php endif; ?>
</section>

<section id="clinics" class="py-16 bg-gradient-to-b from-blue-50 to-white">
    <div class="container mx-auto px-4">
        <?php $clinicSearchTitleTag = 'h2'; ?>
        <?php require __DIR__ . '/partials/clinic-search-panel.php'; ?>
        <?php if (false): ?>
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-8 text-indigo-800">ค้นหาคลินิกใกล้คุณ</h2>
            <form action="searchpage.php" method="GET" class="space-y-6 bg-white p-6 rounded-2xl shadow-sm">
                <input type="text" name="search" placeholder="ค้นหาชื่อคลินิก" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php foreach (array_slice($products, 0, 8) as $p): ?>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="products[]" value="<?php echo h($p['name'] ?? ''); ?>" class="w-4 h-4 rounded" style="accent-color:#4B4899;">
                            <span><?php echo h($p['name'] ?? ''); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <select name="province" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                    <option value="">เลือกจังหวัด</option>
                        <option value="">เลือกจังหวัด</option>
                            <option value="กรุงเทพมหานคร">กรุงเทพมหานคร</option>
                            <option value="กระบี่">กระบี่</option>
                            <option value="กาญจนบุรี">กาญจนบุรี</option>
                            <option value="กาฬสินธุ์">กาฬสินธุ์</option>
                            <option value="กำแพงเพชร">กำแพงเพชร</option>
                            <option value="ขอนแก่น">ขอนแก่น</option>
                            <option value="จันทบุรี">จันทบุรี</option>
                            <option value="ฉะเชิงเทรา">ฉะเชิงเทรา</option>
                            <option value="ชลบุรี">ชลบุรี</option>
                            <option value="ชัยนาท">ชัยนาท</option>
                            <option value="ชัยภูมิ">ชัยภูมิ</option>
                            <option value="ชุมพร">ชุมพร</option>
                            <option value="เชียงราย">เชียงราย</option>
                            <option value="เชียงใหม่">เชียงใหม่</option>
                            <option value="ตรัง">ตรัง</option>
                            <option value="ตราด">ตราด</option>
                            <option value="ตาก">ตาก</option>
                            <option value="นครนายก">นครนายก</option>
                            <option value="นครปฐม">นครปฐม</option>
                            <option value="นครพนม">นครพนม</option>
                            <option value="นครราชสีมา">นครราชสีมา</option>
                            <option value="นครศรีธรรมราช">นครศรีธรรมราช</option>
                            <option value="นครสวรรค์">นครสวรรค์</option>
                            <option value="นนทบุรี">นนทบุรี</option>
                            <option value="นราธิวาส">นราธิวาส</option>
                            <option value="น่าน">น่าน</option>
                            <option value="บึงกาฬ">บึงกาฬ</option>
                            <option value="บุรีรัมย์">บุรีรัมย์</option>
                            <option value="ปทุมธานี">ปทุมธานี</option>
                            <option value="ประจวบคีรีขันธ์">ประจวบคีรีขันธ์</option>
                            <option value="ปราจีนบุรี">ปราจีนบุรี</option>
                            <option value="ปัตตานี">ปัตตานี</option>
                            <option value="พระนครศรีอยุธยา">พระนครศรีอยุธยา</option>
                            <option value="พะเยา">พะเยา</option>
                            <option value="พังงา">พังงา</option>
                            <option value="พัทลุง">พัทลุง</option>
                            <option value="พิจิตร">พิจิตร</option>
                            <option value="พิษณุโลก">พิษณุโลก</option>
                            <option value="เพชรบุรี">เพชรบุรี</option>
                            <option value="เพชรบูรณ์">เพชรบูรณ์</option>
                            <option value="แพร่">แพร่</option>
                            <option value="ภูเก็ต">ภูเก็ต</option>
                            <option value="มหาสารคาม">มหาสารคาม</option>
                            <option value="มุกดาหาร">มุกดาหาร</option>
                            <option value="แม่ฮ่องสอน">แม่ฮ่องสอน</option>
                            <option value="ยโสธร">ยโสธร</option>
                            <option value="ยะลา">ยะลา</option>
                            <option value="ร้อยเอ็ด">ร้อยเอ็ด</option>
                            <option value="ระนอง">ระนอง</option>
                            <option value="ระยอง">ระยอง</option>
                            <option value="ราชบุรี">ราชบุรี</option>
                            <option value="ลพบุรี">ลพบุรี</option>
                            <option value="ลำปาง">ลำปาง</option>
                            <option value="ลำพูน">ลำพูน</option>
                            <option value="เลย">เลย</option>
                            <option value="ศรีสะเกษ">ศรีสะเกษ</option>
                            <option value="สกลนคร">สกลนคร</option>
                            <option value="สงขลา">สงขลา</option>
                            <option value="สตูล">สตูล</option>
                            <option value="สมุทรปราการ">สมุทรปราการ</option>
                            <option value="สมุทรสงคราม">สมุทรสงคราม</option>
                            <option value="สมุทรสาคร">สมุทรสาคร</option>
                            <option value="สระแก้ว">สระแก้ว</option>
                            <option value="สระบุรี">สระบุรี</option>
                            <option value="สิงห์บุรี">สิงห์บุรี</option>
                            <option value="สุโขทัย">สุโขทัย</option>
                            <option value="สุพรรณบุรี">สุพรรณบุรี</option>
                            <option value="สุราษฎร์ธานี">สุราษฎร์ธานี</option>
                            <option value="สุรินทร์">สุรินทร์</option>
                            <option value="หนองคาย">หนองคาย</option>
                            <option value="หนองบัวลำภู">หนองบัวลำภู</option>
                            <option value="อ่างทอง">อ่างทอง</option>
                            <option value="อำนาจเจริญ">อำนาจเจริญ</option>
                            <option value="อุดรธานี">อุดรธานี</option>
                            <option value="อุตรดิตถ์">อุตรดิตถ์</option>
                            <option value="อุทัยธานี">อุทัยธานี</option>
                            <option value="อุบลราชธานี">อุบลราชธานี</option>
                        </select>
                    <?php foreach ($provinces as $province): ?>
                        <option value="<?php echo h($province); ?>"><?php echo h($province); ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="grid grid-cols-2 gap-4">
                    <button type="reset" class="w-full py-3 border-2 border-indigo-600 text-indigo-600 rounded-lg font-medium hover:bg-indigo-50 transition">ล้างค่า</button>
                    <button type="submit" class="w-full py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">ค้นหา</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</section>

<section id="groups" class="py-14 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8 text-center">กลุ่มผลิตภัณฑ์</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-7xl mx-auto">
            <?php
            // Match group titles (which may be misspelled) to real products for detail links.
            $groupProductLink = static function (string $title) use ($products): string {
                $normalize = static fn (string $v) => preg_replace('/[^a-z]/', '', strtolower($v));
                $needle = $normalize($title);
                if ($needle === '') {
                    return '/product.php';
                }
                $bestId = 0;
                $bestDistance = PHP_INT_MAX;
                foreach ($products as $product) {
                    $candidate = $normalize((string) ($product['name'] ?? ''));
                    if ($candidate === '') {
                        continue;
                    }
                    $distance = levenshtein($needle, $candidate);
                    if (str_contains($needle, $candidate) || str_contains($candidate, $needle)) {
                        $distance = min($distance, 1);
                    }
                    if ($distance < $bestDistance) {
                        $bestDistance = $distance;
                        $bestId = (int) $product['id'];
                    }
                }
                return ($bestId > 0 && $bestDistance <= 3) ? '/single-product.php?id=' . $bestId : '/product.php';
            };
            ?>
            <?php foreach (array_slice($groupItems, 0, 4) as $item): ?>
                <?php $img = media_url($item['image_path'] ?? '', 'website', '/uploads/p2.jpg'); ?>
                <?php $itemTitle = (string) ($item['title'] ?? ''); ?>
                <a href="<?php echo h($groupProductLink($itemTitle)); ?>" class="group block">
                    <div class="relative overflow-hidden rounded-xl shadow-md h-72">
                        <img src="<?php echo h($img); ?>" alt="<?php echo h($item['title'] ?? 'Group'); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-4 text-white font-semibold flex items-center justify-between">
                            <span><?php echo h($item['title'] ?? 'ไม่มีชื่อ'); ?></span>
                            <i class="fa-solid fa-arrow-right opacity-0 -translate-x-2 transition duration-300 group-hover:opacity-100 group-hover:translate-x-0"></i>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php if (empty($groupItems)): ?>
                <div class="col-span-full text-center text-gray-500">ยังไม่มีกลุ่มผลิตภัณฑ์</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section id="news" class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-800 mb-4">NEWS & EVENTS</h2>
            <p class="text-gray-600 text-lg">ข่าวสารและกิจกรรมล่าสุด</p>
        </div>
        <div class="grid md:grid-cols-2 gap-6 max-w-7xl mx-auto">
            <?php foreach ($homeNews as $news): ?>
                <?php
                $img = media_url($news['hero_image'] ?? '', 'news', '/uploads/news/1.jpg');
                $dateRaw = $news['published_at'] ?: ($news['created_at'] ?? '');
                $dateText = $dateRaw ? date('d M Y', strtotime($dateRaw)) : '-';
                ?>
                <a href="/news-detail.php?id=<?php echo (int) ($news['id'] ?? 0); ?>" class="group block">
                    <div class="relative overflow-hidden rounded-xl shadow-lg h-72">
                        <img src="<?php echo h($img); ?>" alt="<?php echo h($news['title'] ?? 'News'); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent flex items-end">
                            <div class="p-6 text-white">
                                <span class="bg-blue-600 px-2 py-1 rounded-full text-xs font-medium mb-2 inline-block"><?php echo h($news['category'] ?? 'news'); ?></span>
                                <h4 class="text-xl font-bold mb-1 line-clamp-2"><?php echo h($news['title'] ?? '-'); ?></h4>
                                <p class="text-sm text-gray-200"><?php echo h($dateText); ?></p>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php if (empty($homeNews)): ?>
                <div class="col-span-full text-center text-gray-500">ยังไม่มีข่าวที่เผยแพร่</div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-8">
            <a href="/news-event.php" class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-full font-semibold hover:shadow-xl transition-all duration-300">
                <span>ดูทั้งหมด</span><i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>

<section id="vdo" class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold mb-4">VIDEO CHANNEL</h2>
        </div>
        <div class="grid md:grid-cols-4 gap-6 max-w-7xl mx-auto">
            <?php foreach ($homeVideos as $video): ?>
                <?php
                $videoUrl = (string) ($video['video_url'] ?? '');
                $videoId = tiktok_video_id($videoUrl);
                $thumb = media_url($video['thumbnail'] ?? '', 'videos', '/uploads/p4.png');
                ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
                    <div class="aspect-[9/16] bg-gray-100">
                        <?php if ($videoId): ?>
                            <iframe
                                src="https://www.tiktok.com/player/v1/<?php echo h($videoId); ?>?music_info=0&description=0&rel=0"
                                title="<?php echo h($video['title'] ?? 'TikTok'); ?>"
                                allow="fullscreen"
                                loading="lazy"
                                style="display:block;width:100%;height:100%;border:0;"></iframe>
                        <?php else: ?>
                            <a href="<?php echo h($videoUrl !== '' ? $videoUrl : '#'); ?>" target="_blank" rel="noopener">
                                <img src="<?php echo h($thumb); ?>" alt="<?php echo h($video['title'] ?? 'Video'); ?>" class="w-full h-full object-cover">
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-800 line-clamp-3"><?php echo h($video['title'] ?? 'ไม่มีชื่อวิดีโอ'); ?></h3>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($homeVideos)): ?>
                <div class="col-span-full text-center text-gray-500">ยังไม่มีวิดีโอที่เผยแพร่</div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-8">
            <a href="/video-tiktok.php" class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-indigo-700 to-purple-600 text-white rounded-full font-semibold hover:shadow-lg transition">
                <i class="fas fa-video mr-2"></i>ดูทั้งหมด (<?php echo (int) count($videoItems); ?> วิดีโอ)<i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/partials/site-footer.php'; ?>

<script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.hero-slider .slide');

    function showSlide(index) {
        slides.forEach(function (slide) { slide.classList.remove('active'); });
        if (slides[index]) {
            slides[index].classList.add('active');
        }
    }

    function changeSlide(step) {
        if (slides.length === 0) {
            return;
        }
        currentSlide = (currentSlide + step + slides.length) % slides.length;
        showSlide(currentSlide);
    }

    if (slides.length > 1) {
        setInterval(function () { changeSlide(1); }, 4500);
    }
</script>

</body>
</html>
