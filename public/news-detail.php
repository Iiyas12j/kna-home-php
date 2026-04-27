<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

$id = (int) ($_GET['id'] ?? 0);
$item = null;

if ($pdo instanceof PDO && $id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM news_events WHERE id = ? AND is_active = 1');
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if ($item) {
        $pdo->prepare('UPDATE news_events SET views = views + 1 WHERE id = ?')->execute([$id]);
    }
}
$siteHeaderActive = 'news';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $item ? h($item['title']) : 'News'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Kanit', sans-serif; }</style>
</head>
<body class="bg-gray-50">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>
<div class="container mx-auto px-6 py-10">
        <?php if (!$item): ?>
            <div class="text-center text-gray-500">News not found.</div>
        <?php else: ?>
            <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow p-6">
                <?php if (!empty($item['hero_image'])): ?>
                    <img src="/uploads/news/<?php echo h($item['hero_image']); ?>" alt="" class="w-full rounded-xl mb-6">
                <?php endif; ?>
                <div class="text-sm text-blue-600 font-semibold uppercase"><?php echo h($item['category']); ?></div>
                <h1 class="text-3xl font-bold mt-2"><?php echo h($item['title']); ?></h1>
                <div class="text-sm text-gray-500 mt-2"><?php echo h($item['published_at'] ?? $item['created_at']); ?></div>
                <p class="text-gray-700 mt-4 whitespace-pre-line"><?php echo h($item['summary']); ?></p>
            </div>
        <?php endif; ?>
    </div>
    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
</body>
</html>
