<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/helpers.php';
require_admin();

$page_title = 'จัดการสินค้า';
$active_nav = 'products';
$db_ready = $pdo instanceof PDO;
$db_error = '';
$errors = [];

$item = [
    'id' => 0,
    'name' => '',
    'short_description' => '',
    'hero_image' => '',
    'is_active' => 1,
];
$editing = false;
$uploadDir = __DIR__ . '/../uploads/products';

if ($db_ready && isset($_GET['delete'])) {
    try {
        $id = (int) $_GET['delete'];
        $stmt = $pdo->prepare('SELECT hero_image FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
            if (!empty($row['hero_image'])) {
                $file = $uploadDir . '/' . $row['hero_image'];
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        header('Location: /admin/products.php');
        exit;
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $short = trim($_POST['short_description'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $current_image = $_POST['current_image'] ?? '';

    if ($name === '') {
        $errors[] = 'กรุณากรอกชื่อสินค้า';
    }

    try {
        $newImage = save_uploaded_image($_FILES['hero_image'] ?? [], $uploadDir, 'product');
    } catch (Exception $e) {
        $newImage = null;
        $errors[] = $e->getMessage();
    }

    if (!$errors) {
        $heroImage = $newImage ?: $current_image;
        try {
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE products SET name = ?, short_description = ?, hero_image = ?, is_active = ?, updated_at = NOW() WHERE id = ?');
                $stmt->execute([$name, $short, $heroImage, $is_active, $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO products (name, short_description, hero_image, is_active, created_at) VALUES (?, ?, ?, ?, NOW())');
                $stmt->execute([$name, $short, $heroImage, $is_active]);
            }

            if ($newImage && $current_image && $current_image !== $newImage) {
                $old = $uploadDir . '/' . $current_image;
                if (is_file($old)) {
                    unlink($old);
                }
            }

            header('Location: /admin/products.php');
            exit;
        } catch (Exception $e) {
            $db_error = $e->getMessage();
        }
    }

    $item = array_merge($item, [
        'id' => $id,
        'name' => $name,
        'short_description' => $short,
        'hero_image' => $current_image,
        'is_active' => $is_active,
    ]);
    $editing = $id > 0;
}

if ($db_ready && isset($_GET['edit'])) {
    try {
        $id = (int) $_GET['edit'];
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $item = array_merge($item, $row);
            $editing = true;
        }
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

$rows = [];
if ($db_ready) {
    try {
        $rows = $pdo->query('SELECT * FROM products ORDER BY id DESC')->fetchAll();
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

require_once __DIR__ . '/partials/header.php';
?>

<h1 class="pageTitle">จัดการสินค้า</h1>

<?php if (!$db_ready || $db_error !== ''): ?>
    <div class="notice">Database not ready: <?php echo h($db_error ?: 'connection failed'); ?></div>
<?php endif; ?>
<?php foreach ($errors as $e): ?>
    <div class="error"><?php echo h($e); ?></div>
<?php endforeach; ?>

<div class="toolbar">
    <div></div>
    <button class="btn btn--primary" data-modal-open="productModal"><i class="fa-solid fa-plus"></i>เพิ่มสินค้า</button>
</div>

<div class="tableWrap">
    <table class="table">
        <thead>
            <tr>
                <th>รูปภาพ</th>
                <th>ชื่อสินค้า</th>
                <th>คำอธิบาย</th>
                <th>สถานะ</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <?php if (!empty($row['hero_image'])): ?>
                            <img src="/uploads/products/<?php echo h($row['hero_image']); ?>" alt="" style="width:62px; height:62px; border-radius:8px; object-fit:cover;">
                        <?php else: ?>
                            <div style="width:62px; height:62px; border-radius:8px; background:#e5e7eb;"></div>
                        <?php endif; ?>
                    </td>
                    <td><?php echo h($row['name']); ?></td>
                    <td class="muted"><?php echo h(substr((string) $row['short_description'], 0, 80)); ?></td>
                    <td><span class="status <?php echo (int) $row['is_active'] === 1 ? 'status--on' : 'status--off'; ?>"><?php echo (int) $row['is_active'] === 1 ? 'แสดง' : 'ซ่อน'; ?></span></td>
                    <td>
                        <div class="actions">
                            <a class="iconBtn" href="/admin/products.php?edit=<?php echo (int) $row['id']; ?>"><i class="fa-solid fa-pen-to-square"></i></a>
                            <a class="iconBtn iconBtn--danger" href="/admin/products.php?delete=<?php echo (int) $row['id']; ?>" onclick="return confirm('ลบสินค้านี้?');"><i class="fa-solid fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr><td colspan="5" class="muted">ยังไม่มีข้อมูลสินค้า</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal <?php echo $editing ? 'is-open' : ''; ?>" id="productModal">
    <div class="modal__dialog" style="max-width:760px;">
        <div class="modal__head">
            <h2 class="modal__title"><?php echo $editing ? 'แก้ไขสินค้า' : 'เพิ่มสินค้า'; ?></h2>
            <button class="closeBtn" data-modal-close="productModal"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
            <input type="hidden" name="current_image" value="<?php echo h($item['hero_image']); ?>">

            <div class="field">
                <label>ชื่อสินค้า *</label>
                <input type="text" name="name" value="<?php echo h($item['name']); ?>" required>
            </div>
            <div class="field">
                <label>คำอธิบาย</label>
                <textarea name="short_description" rows="5"><?php echo h($item['short_description']); ?></textarea>
            </div>
            <div class="row">
                <div class="field">
                    <label>รูปสินค้า</label>
                    <input type="file" name="hero_image" accept=".jpg,.jpeg,.png,.webp">
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
                <button class="btn btn--muted" type="button" data-modal-close="productModal">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
