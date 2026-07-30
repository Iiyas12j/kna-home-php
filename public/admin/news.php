<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/helpers.php';
require_admin();

$page_title = 'จัดการข่าวสาร & กิจกรรม';
$active_nav = 'news';
$db_ready = $pdo instanceof PDO;
$db_error = '';
$errors = [];

$categories = [
    'news' => 'ข่าวสาร',
    'event' => 'กิจกรรม',
    'promotion' => 'promotion',
    'health_tip' => 'health_tip',
];

$item = [
    'id' => 0,
    'title' => '',
    'summary' => '',
    'category' => 'news',
    'hero_image' => '',
    'published_at' => '',
    'is_active' => 1,
];
$editing = false;
$uploadDir = __DIR__ . '/../uploads/news';

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? 'save') === 'delete') {
    try {
        require_valid_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT hero_image FROM news_events WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $pdo->prepare('DELETE FROM news_events WHERE id = ?')->execute([$id]);
            delete_uploaded_file($row['hero_image'] ?? '', $uploadDir, 'news');
        }
        header('Location: /admin/news.php');
        exit;
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? 'save') !== 'delete') {
    try {
        require_valid_csrf();
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
    $id = (int) ($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $category = $_POST['category'] ?? 'news';
    $published_at = trim($_POST['published_at'] ?? '');
    $is_active = (int) ($_POST['is_active'] ?? 0) === 1 ? 1 : 0;
    $current_image = $_POST['current_image'] ?? '';

    if ($title === '' || $summary === '') {
        $errors[] = 'กรุณากรอกหัวข้อและเนื้อหา';
    }
    if (!isset($categories[$category])) {
        $category = 'news';
    }

    try {
        $newImage = save_uploaded_image($_FILES['hero_image'] ?? [], $uploadDir, 'news');
    } catch (Exception $e) {
        $newImage = null;
        $errors[] = $e->getMessage();
    }

    if (!$errors) {
        $hero = $newImage ?: $current_image;
        try {
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE news_events SET title = ?, summary = ?, category = ?, hero_image = ?, published_at = ?, is_active = ?, updated_at = NOW() WHERE id = ?');
                $stmt->execute([$title, $summary, $category, $hero, $published_at ?: null, $is_active, $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO news_events (title, summary, category, hero_image, published_at, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
                $stmt->execute([$title, $summary, $category, $hero, $published_at ?: null, $is_active]);
            }

            if ($newImage && $current_image && $newImage !== $current_image) {
                delete_uploaded_file($current_image, $uploadDir, 'news');
            }

            header('Location: /admin/news.php');
            exit;
        } catch (Exception $e) {
            $db_error = $e->getMessage();
        }
    }

    $item = array_merge($item, [
        'id' => $id,
        'title' => $title,
        'summary' => $summary,
        'category' => $category,
        'hero_image' => $current_image,
        'published_at' => $published_at,
        'is_active' => $is_active,
    ]);
    $editing = $id > 0;
}

if ($db_ready && isset($_GET['edit'])) {
    try {
        $id = (int) $_GET['edit'];
        $stmt = $pdo->prepare('SELECT * FROM news_events WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $item = array_merge($item, $row);
            if (!empty($item['published_at'])) {
                $item['published_at'] = str_replace(' ', 'T', substr((string) $item['published_at'], 0, 16));
            }
            $editing = true;
        }
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

$rows = [];
if ($db_ready) {
    try {
        $rows = $pdo->query('SELECT * FROM news_events ORDER BY id DESC')->fetchAll();
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

require_once __DIR__ . '/partials/header.php';
?>

<h1 class="pageTitle">จัดการข่าวสาร & กิจกรรม</h1>

<?php if (!$db_ready || $db_error !== ''): ?>
    <div class="notice">Database not ready: <?php echo h($db_error ?: 'connection failed'); ?></div>
<?php endif; ?>
<?php foreach ($errors as $e): ?>
    <div class="error"><?php echo h($e); ?></div>
<?php endforeach; ?>

<div class="toolbar">
    <div></div>
    <button class="btn btn--primary" data-modal-open="newsModal"><i class="fa-solid fa-plus"></i>เพิ่มข่าว</button>
</div>

<div class="tableWrap">
    <table class="table">
        <thead>
            <tr>
                <th>รูปภาพ</th>
                <th>หัวข้อ</th>
                <th>หมวดหมู่</th>
                <th>วันที่</th>
                <th>สถานะ</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <?php if (!empty($row['hero_image'])): ?>
                            <img src="<?php echo h(upload_url($row['hero_image'], 'news')); ?>" alt="" style="width:58px; height:58px; border-radius:8px; object-fit:cover; background:#e5e7eb;">
                        <?php else: ?>
                            <div style="width:58px; height:58px; border-radius:8px; background:#e5e7eb; display:grid; place-items:center; color:#9ca3af;"><i class="fa-regular fa-image"></i></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-weight:700;"><?php echo h($row['title']); ?></div>
                        <?php $preview = (string) $row['summary']; ?>
                        <div class="muted" style="font-size:13px;"><?php echo h(strlen($preview) > 80 ? substr($preview, 0, 80) . '...' : $preview); ?></div>
                    </td>
                    <td><span class="chip"><?php echo h($categories[$row['category']] ?? $row['category']); ?></span></td>
                    <td><?php echo h(date('d/m/Y', strtotime((string) ($row['published_at'] ?: $row['created_at'])))); ?></td>
                    <td><span class="status <?php echo (int) $row['is_active'] === 1 ? 'status--on' : 'status--off'; ?>"><?php echo (int) $row['is_active'] === 1 ? 'แสดง' : 'ซ่อน'; ?></span></td>
                    <td>
                        <div class="actions">
                            <a class="iconBtn" href="/admin/news.php?edit=<?php echo (int) $row['id']; ?>"><i class="fa-solid fa-pen-to-square"></i></a>
                            <form method="post" onsubmit="return confirm('ลบข่าวนี้?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                <button class="iconBtn iconBtn--danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="muted">ยังไม่มีข้อมูลข่าว</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal <?php echo $editing ? 'is-open' : ''; ?>" id="newsModal">
    <div class="modal__dialog" style="max-width:760px;">
        <div class="modal__head">
            <h2 class="modal__title"><?php echo $editing ? 'แก้ไขข่าว' : 'เพิ่มข่าว'; ?></h2>
            <button class="closeBtn" data-modal-close="newsModal"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="post" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
            <input type="hidden" name="current_image" value="<?php echo h($item['hero_image']); ?>">

            <div class="field">
                <label>หัวข้อข่าว *</label>
                <input type="text" name="title" value="<?php echo h($item['title']); ?>" required>
            </div>

            <div class="field">
                <label>เนื้อหา *</label>
                <textarea name="summary" rows="6" required><?php echo h($item['summary']); ?></textarea>
            </div>

            <div class="row">
                <div class="field">
                    <label>หมวดหมู่ *</label>
                    <select name="category">
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?php echo h($key); ?>" <?php echo $item['category'] === $key ? 'selected' : ''; ?>><?php echo h($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>วันที่เผยแพร่</label>
                    <input type="datetime-local" name="published_at" value="<?php echo h($item['published_at']); ?>">
                </div>
            </div>

            <div class="row">
                <div class="field">
                    <label>รูปภาพ</label>
                    <input type="file" name="hero_image" accept=".jpg,.jpeg,.png,.webp,.svg">
                </div>
                <div class="field">
                    <label>สถานะ</label>
                    <select name="is_active">
                        <option value="1" <?php echo (int) $item['is_active'] === 1 ? 'selected' : ''; ?>>แสดง</option>
                        <option value="0" <?php echo (int) $item['is_active'] === 0 ? 'selected' : ''; ?>>ซ่อน</option>
                    </select>
                </div>
            </div>

            <div class="actions" style="margin-top:8px;">
                <button class="btn btn--primary" type="submit"><i class="fa-solid fa-floppy-disk"></i>บันทึก</button>
                <button class="btn btn--muted" type="button" data-modal-close="newsModal">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
