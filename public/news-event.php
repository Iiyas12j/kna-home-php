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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News & Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>
<section class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-14">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-4xl font-bold">News & Events</h1>
            <p class="mt-3 text-white/90">Latest updates from KNA</p>
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
                                    <img src="/uploads/news/<?php echo h($row['hero_image']); ?>" alt="" class="w-full h-full object-cover">
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
