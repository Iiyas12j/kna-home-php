<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

$items = [];
if ($pdo instanceof PDO) {
    $stmt = $pdo->query('SELECT * FROM news_events WHERE is_active = 1 ORDER BY COALESCE(published_at, created_at) DESC, id DESC');
    $items = $stmt->fetchAll();
}
$siteHeaderActive = 'news';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <?php $page_title = 'News & Events - KNA Interpharma'; require_once __DIR__ . '/partials/site-head.php'; ?>
</head>
<body class="bg-gray-50">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>
    <section class="relative overflow-hidden">
        <img src="/uploads/website/bg-contact-us/2-1.png" alt="News Banner" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>
        <div class="relative mx-auto max-w-7xl px-4 pb-20 pt-16 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white/90 backdrop-blur">
                    <i class="fa-regular fa-newspaper"></i> News & Events
                </div>
                <h1 class="mt-5 text-4xl font-extrabold leading-tight text-white sm:text-5xl">
                    ข่าวสารและกิจกรรม<br class="hidden sm:block">KNA Interpharma
                </h1>
            </div>
        </div>
    </section>

    <section class="py-12">
        <div class="container mx-auto px-6">
            <?php if (empty($items)): ?>
                <div class="text-center text-gray-500">No news available yet.</div>
            <?php else: ?>
                <div class="grid md:grid-cols-3 gap-8">
                    <?php foreach ($items as $row): ?>
                        <a href="/news-detail.php?id=<?php echo (int) $row['id']; ?>" class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden">
                            <div class="aspect-video bg-gray-100">
                                <?php if (!empty($row['hero_image'])): ?>
                                    <img src="<?php echo h(upload_url($row['hero_image'], 'news')); ?>" alt="" class="w-full h-full object-cover">
                                <?php endif; ?>
                            </div>
                            <div class="p-5">
                                <div class="text-xs uppercase text-blue-600 font-semibold"><?php echo h($row['category']); ?></div>
                                <h3 class="text-lg font-bold text-gray-900 mt-2 line-clamp-2"><?php echo h($row['title']); ?></h3>
                                <p class="text-sm text-gray-600 mt-2 line-clamp-3"><?php echo h($row['summary']); ?></p>
                                <div class="text-xs text-gray-500 mt-3">
                                    <?php echo h($row['published_at'] ?? $row['created_at']); ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
</body>
</html>
