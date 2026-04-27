<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/helpers.php';
require_admin();

$page_title = 'Dashboard';
$active_nav = 'dashboard';
$db_ok = $pdo instanceof PDO;
$db_error = '';

$counts = [
    'clinics' => 0,
    'news' => 0,
    'products' => 0,
    'videos' => 0,
];
$latest_clinics = [];
$latest_news = [];

if ($db_ok) {
    try {
        $counts['clinics'] = (int) $pdo->query('SELECT COUNT(*) FROM clinics')->fetchColumn();
        $counts['news'] = (int) $pdo->query('SELECT COUNT(*) FROM news_events')->fetchColumn();
        $counts['products'] = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
        $counts['videos'] = (int) $pdo->query('SELECT COUNT(*) FROM videos')->fetchColumn();

        $latest_clinics = $pdo->query('SELECT name, province, phone, is_active FROM clinics ORDER BY id DESC LIMIT 6')->fetchAll();
        $latest_news = $pdo->query('SELECT title, category, COALESCE(published_at, created_at) AS published_at FROM news_events ORDER BY id DESC LIMIT 6')->fetchAll();
    } catch (Exception $e) {
        $db_ok = false;
        $db_error = $e->getMessage();
    }
}

require_once __DIR__ . '/partials/header.php';
?>

<h1 class="pageTitle">Dashboard</h1>

<?php if (!$db_ok): ?>
    <div class="notice">Database not ready: <?php echo h($db_error ?: 'connection failed'); ?></div>
<?php endif; ?>

<div class="grid grid--3" style="margin-bottom:16px;">
    <div class="card kpi">
        <div>
            <div class="kpi__label">คลินิกทั้งหมด</div>
            <div class="kpi__value"><?php echo (int) $counts['clinics']; ?></div>
        </div>
        <div class="kpi__icon"><i class="fa-solid fa-clinic-medical"></i></div>
    </div>
    <div class="card kpi">
        <div>
            <div class="kpi__label">ข่าวสาร & กิจกรรม</div>
            <div class="kpi__value"><?php echo (int) $counts['news']; ?></div>
        </div>
        <div class="kpi__icon"><i class="fa-solid fa-newspaper"></i></div>
    </div>
    <div class="card kpi">
        <div>
            <div class="kpi__label">สินค้า</div>
            <div class="kpi__value"><?php echo (int) $counts['products']; ?></div>
        </div>
        <div class="kpi__icon"><i class="fa-solid fa-box-open"></i></div>
    </div>
</div>

<div class="grid grid--2">
    <div class="card">
        <div class="toolbar">
            <strong>คลินิกล่าสุด</strong>
            <a class="btn" href="/admin/clinics.php">จัดการคลินิก</a>
        </div>
        <div>
            <?php foreach ($latest_clinics as $row): ?>
                <div style="display:flex; justify-content:space-between; gap:12px; padding:10px 0; border-bottom:1px solid #eef1f5;">
                    <div>
                        <div style="font-weight:700;"><?php echo h($row['name']); ?></div>
                        <div class="muted" style="font-size:13px;"><?php echo h($row['province'] ?: '-'); ?> • <?php echo h($row['phone'] ?: '-'); ?></div>
                    </div>
                    <div>
                        <span class="status <?php echo (int) $row['is_active'] === 1 ? 'status--on' : 'status--off'; ?>"><?php echo (int) $row['is_active'] === 1 ? 'เปิดทำการ' : 'ปิด'; ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($latest_clinics)): ?>
                <div class="muted">ยังไม่มีข้อมูลคลินิก</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="toolbar">
            <strong>ข่าวสารล่าสุด</strong>
            <a class="btn" href="/admin/news.php">จัดการข่าวสาร</a>
        </div>
        <div>
            <?php foreach ($latest_news as $row): ?>
                <div style="display:flex; justify-content:space-between; gap:12px; padding:10px 0; border-bottom:1px solid #eef1f5;">
                    <div>
                        <div style="font-weight:700;"><?php echo h($row['title']); ?></div>
                        <div class="muted" style="font-size:13px;"><?php echo h(date('d/m/Y', strtotime((string) $row['published_at']))); ?></div>
                    </div>
                    <div>
                        <span class="chip"><?php echo h($row['category']); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($latest_news)): ?>
                <div class="muted">ยังไม่มีข่าวสาร</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
