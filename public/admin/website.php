<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/helpers.php';
require_admin();

$page_title = 'จัดการเว็บไซต์';
$active_nav = 'website';
$db_ready = $pdo instanceof PDO;
$db_error = '';

$tab = $_GET['tab'] ?? 'slides';
$tabs = [
    'slides' => ['label' => 'Hero Slider', 'table' => 'home_slides'],
    'gallery' => ['label' => 'แกลเลอรี่สินค้า', 'table' => 'website_product_gallery'],
    'groups' => ['label' => 'กลุ่มผลิตภัณฑ์', 'table' => 'website_product_groups'],
];
if (!isset($tabs[$tab])) {
    $tab = 'slides';
}

$uploadDir = __DIR__ . '/../uploads/website';
$errors = [];
$editing = false;
$open_modal = false;

$item = [
    'id' => 0,
    'title' => '',
    'sort_order' => 0,
    'is_active' => 1,
    'desktop_image' => '',
    'mobile_image' => '',
    'image_path' => '',
];

$table = $tabs[$tab]['table'];

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? 'save') === 'delete') {
    try {
        require_valid_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $pdo->prepare("DELETE FROM {$table} WHERE id = ?")->execute([$id]);
            foreach (['image_path', 'desktop_image', 'mobile_image'] as $col) {
                if (!empty($row[$col])) {
                    delete_uploaded_file($row[$col], $uploadDir, 'website');
                }
            }
        }
        header('Location: /admin/website.php?tab=' . urlencode($tab));
        exit;
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? 'save') !== 'delete') {
    try {
        require_valid_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $sort_order = (int) ($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($tab === 'slides') {
            $current_desktop = $_POST['current_desktop'] ?? '';
            $current_mobile = $_POST['current_mobile'] ?? '';
            $newDesktop = save_uploaded_image($_FILES['desktop_image'] ?? [], $uploadDir, 'slide');
            $newMobile = save_uploaded_image($_FILES['mobile_image'] ?? [], $uploadDir, 'slidem');
            $desktop = $newDesktop ?: $current_desktop;
            $mobile = $newMobile ?: $current_mobile;

            if ($desktop === '') {
                $errors[] = 'กรุณาอัปโหลดภาพ desktop';
            }

            if (!$errors) {
                if ($id > 0) {
                    $stmt = $pdo->prepare('UPDATE home_slides SET title = ?, desktop_image = ?, mobile_image = ?, sort_order = ?, is_active = ? WHERE id = ?');
                    $stmt->execute([$title, $desktop, $mobile, $sort_order, $is_active, $id]);
                } else {
                    $stmt = $pdo->prepare('INSERT INTO home_slides (title, desktop_image, mobile_image, sort_order, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
                    $stmt->execute([$title, $desktop, $mobile, $sort_order, $is_active]);
                }
            }
        } else {
            $current_image = $_POST['current_image'] ?? '';
            $newImage = save_uploaded_image($_FILES['image_path'] ?? [], $uploadDir, 'web');
            $image = $newImage ?: $current_image;
            if ($image === '') {
                $errors[] = 'กรุณาอัปโหลดรูปภาพ';
            }

            if (!$errors) {
                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE {$table} SET title = ?, image_path = ?, sort_order = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$title, $image, $sort_order, $is_active, $id]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO {$table} (title, image_path, sort_order, is_active, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->execute([$title, $image, $sort_order, $is_active]);
                }
            }
        }

        if (!$errors) {
            header('Location: /admin/website.php?tab=' . urlencode($tab));
            exit;
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

if ($db_ready && isset($_GET['edit'])) {
    try {
        $id = (int) $_GET['edit'];
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $item = array_merge($item, $row);
            $editing = true;
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

if ($editing || isset($_GET['create']) || ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($errors))) {
    $open_modal = true;
}

$items = [];
if ($db_ready) {
    try {
        $items = $pdo->query("SELECT * FROM {$table} ORDER BY sort_order ASC, id ASC")->fetchAll();
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

require_once __DIR__ . '/partials/header.php';
?>

<h1 class="pageTitle">จัดการเว็บไซต์</h1>

<?php if (!$db_ready || $db_error !== ''): ?>
    <div class="notice">Database not ready: <?php echo h($db_error ?: 'connection failed'); ?></div>
<?php endif; ?>
<?php foreach ($errors as $e): ?>
    <div class="error"><?php echo h($e); ?></div>
<?php endforeach; ?>

<div class="toolbar">
    <div class="actions">
        <?php foreach ($tabs as $key => $config): ?>
            <a class="btn <?php echo $tab === $key ? 'btn--primary' : ''; ?>" href="/admin/website.php?tab=<?php echo h($key); ?>"><?php echo h($config['label']); ?></a>
        <?php endforeach; ?>
    </div>
    <a class="btn btn--primary" href="/admin/website.php?tab=<?php echo h($tab); ?>&create=1" data-modal-open="websiteModal"><i class="fa-solid fa-plus"></i>เพิ่มรูปภาพใหม่</a>
</div>

<div class="grid grid--3">
    <?php foreach ($items as $row): ?>
        <div class="card" style="padding:0; overflow:hidden;">
            <div style="position:relative;">
                <?php if ($tab === 'slides'): ?>
                    <img src="<?php echo h(upload_url((string)($row['desktop_image'] ?? ''), 'website')); ?>" alt="" style="width:100%; height:190px; object-fit:cover; background:#eef2f7;">
                <?php else: ?>
                    <img src="<?php echo h(upload_url((string)($row['image_path'] ?? ''), 'website')); ?>" alt="" style="width:100%; height:190px; object-fit:cover; background:#eef2f7;">
                <?php endif; ?>
                <span style="position:absolute; top:10px; left:10px;" class="chip">#<?php echo (int) ($row['sort_order'] ?? 0); ?></span>
                <span style="position:absolute; top:10px; right:10px;" class="status <?php echo (int) ($row['is_active'] ?? 0) === 1 ? 'status--on' : 'status--off'; ?>"><?php echo (int) ($row['is_active'] ?? 0) === 1 ? 'ใช้งาน' : 'ซ่อน'; ?></span>
            </div>
            <div style="padding:12px 12px 14px;">
                <div style="font-weight:700; margin-bottom:10px;"><?php echo h(($row['title'] ?? '') !== '' ? $row['title'] : 'ไม่มีชื่อ'); ?></div>
                <div class="actions">
                    <a class="btn" href="/admin/website.php?tab=<?php echo h($tab); ?>&edit=<?php echo (int) ($row['id'] ?? 0); ?>"><i class="fa-solid fa-pen-to-square"></i>แก้ไข</a>
                    <form method="post" onsubmit="return confirm('ลบรายการนี้?');">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo (int) ($row['id'] ?? 0); ?>">
                        <button class="btn btn--danger" type="submit"><i class="fa-solid fa-trash"></i>ลบ</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
        <div class="card">ยังไม่มีรายการ</div>
    <?php endif; ?>
</div>

<div class="modal <?php echo $open_modal ? 'is-open' : ''; ?>" id="websiteModal">
    <div class="modal__dialog">
        <div class="modal__head">
            <h2 class="modal__title"><?php echo $editing ? 'แก้ไขรายการ' : 'เพิ่มรายการ'; ?></h2>
            <button class="closeBtn" data-modal-close="websiteModal"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="post" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?php echo (int) ($item['id'] ?? 0); ?>">

            <div class="row">
                <div class="field">
                    <label>ชื่อรายการ</label>
                    <input type="text" name="title" value="<?php echo h($item['title'] ?? ''); ?>" placeholder="ระบุชื่อภาพ">
                </div>
                <div class="field">
                    <label>ลำดับ</label>
                    <input type="number" name="sort_order" value="<?php echo (int) ($item['sort_order'] ?? 0); ?>">
                </div>
            </div>

            <?php if ($tab === 'slides'): ?>
                <input type="hidden" name="current_desktop" value="<?php echo h($item['desktop_image'] ?? ''); ?>">
                <input type="hidden" name="current_mobile" value="<?php echo h($item['mobile_image'] ?? ''); ?>">

                <div class="row">
                    <div class="field">
                        <label>Desktop Image</label>
                        <input type="file" name="desktop_image" accept=".jpg,.jpeg,.png,.webp,.svg">
                    </div>
                    <div class="field">
                        <label>Mobile Image</label>
                        <input type="file" name="mobile_image" accept=".jpg,.jpeg,.png,.webp,.svg">
                    </div>
                </div>
            <?php else: ?>
                <input type="hidden" name="current_image" value="<?php echo h($item['image_path'] ?? ''); ?>">
                <div class="field">
                    <label>รูปภาพ</label>
                    <input type="file" name="image_path" accept=".jpg,.jpeg,.png,.webp,.svg">
                </div>
            <?php endif; ?>

            <div class="field">
                <label><input type="checkbox" name="is_active" <?php echo ($item['is_active'] ?? 1) ? 'checked' : ''; ?>> เปิดใช้งาน</label>
            </div>

            <div class="actions" style="margin-top:10px;">
                <button class="btn btn--primary" type="submit"><i class="fa-solid fa-floppy-disk"></i>บันทึก</button>
                <button class="btn btn--muted" type="button" data-modal-close="websiteModal">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
