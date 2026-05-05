<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

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
$id = (int) ($_GET['id'] ?? 0);
$item = null;
$relatedItems = [];

if ($pdo instanceof PDO && $id > 0) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? AND is_active = 1');
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        if ($item) {
            $relatedStmt = $pdo->prepare(
                'SELECT id, name, short_description, hero_image
                 FROM products WHERE is_active = 1 AND id <> ? ORDER BY id DESC LIMIT 3'
            );
            $relatedStmt->execute([$id]);
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
        .kna-purple { color: #4B4899; }
        .kna-bg { background: #4B4899; }
        .kna-border { border-color: #4B4899; }
        .line-clamp-3 {
            display: -webkit-box; -webkit-box-orient: vertical;
            -webkit-line-clamp: 3; overflow: hidden;
        }
        .product-img-wrap {
            background: linear-gradient(135deg, #f0f0fa 0%, #e8e8f5 100%);
        }
        .highlight-dot::before {
            content: '';
            display: inline-block;
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #4B4899;
            margin-right: 10px;
            flex-shrink: 0;
            margin-top: 8px;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <?php if (!$item): ?>
    <!-- Not Found -->
    <div class="flex flex-col items-center justify-center py-32 px-4 text-center">
        <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mb-6 text-gray-400">
            <i class="fa-solid fa-box-open text-3xl"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-3">ไม่พบสินค้าที่ต้องการ</h1>
        <p class="text-gray-500 mb-8">รายการสินค้านี้อาจถูกปิดการแสดงผลหรือไม่มีอยู่ในระบบ</p>
        <a href="/product.php"
           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-semibold kna-bg hover:opacity-90 transition">
            <i class="fa-solid fa-arrow-left"></i> กลับไปหน้าผลิตภัณฑ์
        </a>
    </div>

    <?php else: ?>

    <div class="max-w-6xl mx-auto px-4 py-8 sm:px-6 lg:px-8">

        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm text-gray-400 mb-8">
            <a href="/index.php" class="hover:text-gray-600 transition">หน้าแรก</a>
            <i class="fa-solid fa-chevron-right text-xs"></i>
            <a href="/product.php" class="hover:text-gray-600 transition">ผลิตภัณฑ์</a>
            <i class="fa-solid fa-chevron-right text-xs"></i>
            <span class="text-gray-700 font-medium"><?php echo h($name); ?></span>
        </nav>

        <!-- Hero: Image + Info -->
        <div class="grid lg:grid-cols-2 gap-10 mb-12">

            <!-- Image -->
            <div class="product-img-wrap rounded-2xl overflow-hidden flex items-center justify-center aspect-square max-h-[500px]">
                <?php if ($heroImage !== ''): ?>
                    <img src="<?php echo h($heroImage); ?>" alt="<?php echo h($name); ?>"
                         class="w-full h-full object-contain p-6">
                <?php else: ?>
                    <div class="flex flex-col items-center justify-center text-gray-300 py-20">
                        <i class="fa-solid fa-box-open text-8xl mb-4"></i>
                        <span class="text-sm">ยังไม่มีรูปภาพ</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="flex flex-col justify-center">
                <!-- Category Badge -->
                <div class="mb-4">
                    <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold text-white kna-bg">
                        <?php echo h($category); ?>
                    </span>
                </div>

                <h1 class="text-4xl font-bold text-gray-900 mb-4 leading-tight">
                    <?php echo h($name); ?>
                </h1>

                <p class="text-gray-600 text-lg leading-relaxed mb-6">
                    <?php echo h($summary); ?>
                </p>

                <!-- Meta Tags -->
                <div class="flex flex-wrap gap-3 mb-8">
                    <div class="flex items-center gap-2 bg-white rounded-xl px-4 py-2 border border-gray-200 text-sm">
                        <i class="fa-solid fa-globe text-gray-400"></i>
                        <span class="text-gray-600"><?php echo h($origin); ?></span>
                    </div>
                    <div class="flex items-center gap-2 bg-white rounded-xl px-4 py-2 border border-gray-200 text-sm">
                        <i class="fa-solid fa-tag text-gray-400"></i>
                        <span class="text-gray-600"><?php echo h($category); ?></span>
                    </div>
                    <div class="flex items-center gap-2 bg-white rounded-xl px-4 py-2 border border-gray-200 text-sm">
                        <i class="fa-solid fa-shield-heart text-gray-400"></i>
                        <span class="text-gray-600">สำหรับผู้เชี่ยวชาญ</span>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="flex flex-wrap gap-3">
                    <a href="/contact.php"
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white font-semibold kna-bg hover:opacity-90 transition shadow-md">
                        <i class="fa-solid fa-envelope"></i>
                        สอบถามข้อมูลสินค้า
                    </a>
                    <a href="/searchpage.php"
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold border-2 kna-border kna-purple bg-white hover:bg-purple-50 transition">
                        <i class="fa-solid fa-location-dot"></i>
                        ค้นหาคลินิก
                    </a>
                </div>
            </div>
        </div>

        <!-- Highlights -->
        <?php if (!empty($highlights)): ?>
        <div class="bg-white rounded-2xl border border-gray-200 p-7 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-5 flex items-center gap-2">
                <span class="w-1 h-6 rounded-full kna-bg inline-block"></span>
                จุดเด่นของผลิตภัณฑ์
            </h2>
            <ul class="space-y-3">
                <?php foreach ($highlights as $hl): ?>
                    <li class="flex items-start gap-3 text-gray-700">
                        <i class="fa-solid fa-circle-check mt-1 shrink-0" style="color:#4B4899;"></i>
                        <span><?php echo h($hl); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Full Description -->
        <?php if (!empty($paragraphs)): ?>
        <div class="bg-white rounded-2xl border border-gray-200 p-7 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-5 flex items-center gap-2">
                <span class="w-1 h-6 rounded-full kna-bg inline-block"></span>
                รายละเอียดผลิตภัณฑ์
            </h2>
            <div class="space-y-4 text-gray-600 leading-8">
                <?php foreach (array_slice($paragraphs, 0, 8) as $p): ?>
                    <p><?php echo h($p); ?></p>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- CTA Banner -->
        <div class="rounded-2xl p-8 mb-12 text-white flex flex-col sm:flex-row items-center justify-between gap-6 kna-bg">
            <div>
                <h3 class="text-2xl font-bold mb-1">ต้องการข้อมูลเพิ่มเติม?</h3>
                <p class="text-white/80">ทีมงาน KNA Interpharma พร้อมให้คำปรึกษาโดยตรง</p>
            </div>
            <div class="flex flex-wrap gap-3 shrink-0">
                <a href="/contact.php"
                   class="inline-flex items-center gap-2 bg-white px-5 py-3 rounded-xl font-semibold kna-purple hover:bg-gray-100 transition">
                    <i class="fa-solid fa-headset"></i> ติดต่อเรา
                </a>
                <a href="tel:056200890"
                   class="inline-flex items-center gap-2 bg-white/10 border border-white/30 px-5 py-3 rounded-xl font-semibold text-white hover:bg-white/20 transition">
                    <i class="fa-solid fa-phone"></i> 056-200890
                </a>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($relatedItems)): ?>
        <div>
            <div class="flex items-end justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">ผลิตภัณฑ์อื่นที่น่าสนใจ</h2>
                <a href="/product.php" class="text-sm font-semibold kna-purple hover:underline">ดูทั้งหมด →</a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($relatedItems as $rel): ?>
                <?php
                $relImg  = product_media_url($rel['hero_image'] ?? '', '');
                $relDesc = product_shorten_text(product_clean_text((string) ($rel['short_description'] ?? '')), 100);
                ?>
                <a href="/single-product.php?id=<?php echo (int) ($rel['id'] ?? 0); ?>"
                   class="group bg-white rounded-2xl border border-gray-200 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-200">
                    <div class="product-img-wrap h-48 flex items-center justify-center overflow-hidden">
                        <?php if ($relImg !== ''): ?>
                            <img src="<?php echo h($relImg); ?>" alt="<?php echo h($rel['name'] ?? ''); ?>"
                                 class="w-full h-full object-contain p-4 group-hover:scale-105 transition duration-300">
                        <?php else: ?>
                            <i class="fa-solid fa-box-open text-4xl text-gray-300"></i>
                        <?php endif; ?>
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-gray-900 mb-2"><?php echo h($rel['name'] ?? ''); ?></h3>
                        <p class="text-sm text-gray-500 line-clamp-3"><?php echo h($relDesc !== '' ? $relDesc : 'ดูรายละเอียดเพิ่มเติม'); ?></p>
                        <span class="inline-flex items-center gap-1 mt-3 text-sm font-semibold kna-purple group-hover:underline">
                            ดูรายละเอียด <i class="fa-solid fa-arrow-right text-xs"></i>
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
    <?php endif; ?>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
</body>
</html>
