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
    'logo_image' => '',
    'is_active' => 1,
];
$editing = false;
$uploadDir = __DIR__ . '/../uploads/products';

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? 'save') === 'delete') {
    try {
        require_valid_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT hero_image, logo_image FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
            delete_uploaded_file($row['hero_image'] ?? '', $uploadDir, 'products');
            delete_uploaded_file($row['logo_image'] ?? '', $uploadDir, 'products');

            $galleryStmt = $pdo->prepare('SELECT image FROM product_images WHERE product_id = ?');
            $galleryStmt->execute([$id]);
            foreach ($galleryStmt->fetchAll() as $galleryRow) {
                delete_uploaded_file($galleryRow['image'] ?? '', $uploadDir, 'products');
            }
            $pdo->prepare('DELETE FROM product_images WHERE product_id = ?')->execute([$id]);
        }
        header('Location: /admin/products.php');
        exit;
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_image') {
    try {
        require_valid_csrf();
        $imageId = (int) ($_POST['image_id'] ?? 0);
        $productId = (int) ($_POST['product_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT image FROM product_images WHERE id = ?');
        $stmt->execute([$imageId]);
        $row = $stmt->fetch();
        if ($row) {
            $pdo->prepare('DELETE FROM product_images WHERE id = ?')->execute([$imageId]);
            delete_uploaded_file($row['image'] ?? '', $uploadDir, 'products');
        }
        header('Location: /admin/products.php?edit=' . $productId);
        exit;
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? 'save') === 'save') {
    try {
        require_valid_csrf();
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $short = trim($_POST['short_description'] ?? '');
    $is_active = (int) ($_POST['is_active'] ?? 0) === 1 ? 1 : 0;
    $current_image = $_POST['current_image'] ?? '';
    $current_logo = $_POST['current_logo'] ?? '';

    if ($name === '') {
        $errors[] = 'กรุณากรอกชื่อสินค้า';
    }

    $newImage = null;
    $newLogo = null;

    try {
        $newImage = save_uploaded_image($_FILES['hero_image'] ?? [], $uploadDir, 'product');
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }

    try {
        $newLogo = save_uploaded_image($_FILES['logo_image'] ?? [], $uploadDir, 'logo');
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }

    if (!$errors) {
        $heroImage = $newImage ?: $current_image;
        $logoImage = $newLogo ?: $current_logo;
        try {
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE products SET name = ?, short_description = ?, hero_image = ?, logo_image = ?, is_active = ?, updated_at = NOW() WHERE id = ?');
                $stmt->execute([$name, $short, $heroImage, $logoImage, $is_active, $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO products (name, short_description, hero_image, logo_image, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
                $stmt->execute([$name, $short, $heroImage, $logoImage, $is_active]);
            }

            if ($newImage && $current_image && $current_image !== $newImage) {
                delete_uploaded_file($current_image, $uploadDir, 'products');
            }
            if ($newLogo && $current_logo && $current_logo !== $newLogo) {
                delete_uploaded_file($current_logo, $uploadDir, 'products');
            }

            $savedId = $id > 0 ? $id : (int) $pdo->lastInsertId();
            $galleryUploaded = false;
            foreach (normalize_uploaded_files($_FILES['gallery_images'] ?? []) as $galleryFile) {
                try {
                    $galleryName = save_uploaded_image($galleryFile, $uploadDir, 'gallery');
                    if ($galleryName) {
                        $pdo->prepare('INSERT INTO product_images (product_id, image) VALUES (?, ?)')
                            ->execute([$savedId, $galleryName]);
                        $galleryUploaded = true;
                    }
                } catch (Exception $e) {
                    $errors[] = 'แกลเลอรี: ' . $e->getMessage();
                }
            }

            if (!$errors) {
                header('Location: /admin/products.php' . ($galleryUploaded ? '?edit=' . $savedId : ''));
                exit;
            }
            $id = $savedId;
        } catch (Exception $e) {
            $db_error = $e->getMessage();
        }
    }

    $item = array_merge($item, [
        'id' => $id,
        'name' => $name,
        'short_description' => $short,
        'hero_image' => $current_image,
        'logo_image' => $current_logo,
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

$galleryImages = [];
if ($db_ready && (int) $item['id'] > 0) {
    try {
        $stmt = $pdo->prepare('SELECT id, image FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC');
        $stmt->execute([(int) $item['id']]);
        $galleryImages = $stmt->fetchAll();
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
                <th>โลโก้</th>
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
                        <?php if (!empty($row['logo_image'])): ?>
                            <img src="<?php echo h(upload_url($row['logo_image'], 'products')); ?>" alt="" style="width:48px; height:48px; border-radius:8px; object-fit:contain; background:repeating-conic-gradient(#6b7280 0% 25%, #9ca3af 0% 50%) 50% / 12px 12px; border:1px solid #e5e7eb;">
                        <?php else: ?>
                            <div style="width:48px; height:48px; border-radius:8px; background:#f3f4f6; border:1px dashed #d1d5db; display:flex; align-items:center; justify-content:center; color:#9ca3af; font-size:12px;">No Logo</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($row['hero_image'])): ?>
                            <img src="<?php echo h(upload_url($row['hero_image'], 'products')); ?>" alt="" style="width:62px; height:62px; border-radius:8px; object-fit:cover;">
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
                            <form method="post" onsubmit="return confirm('ลบสินค้านี้?');">
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
                <tr><td colspan="6" class="muted">ยังไม่มีข้อมูลสินค้า</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal <?php echo ($editing || $errors) ? 'is-open' : ''; ?>" id="productModal">
    <div class="modal__dialog" style="max-width:760px;">
        <div class="modal__head">
            <h2 class="modal__title"><?php echo $editing ? 'แก้ไขสินค้า' : 'เพิ่มสินค้า'; ?></h2>
            <button class="closeBtn" data-modal-close="productModal"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="post" enctype="multipart/form-data">
            <?php foreach ($errors as $e): ?>
                <div class="error"><?php echo h($e); ?></div>
            <?php endforeach; ?>
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
            <input type="hidden" name="current_image" value="<?php echo h($item['hero_image']); ?>">
            <input type="hidden" name="current_logo" value="<?php echo h($item['logo_image']); ?>">

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
                    <label>รูปโลโก้</label>
                    <?php if (!empty($item['logo_image'])): ?>
                        <div style="margin-bottom:8px;">
                            <img src="<?php echo h(upload_url($item['logo_image'], 'products')); ?>" alt="Logo" style="height:48px; object-fit:contain; border:1px solid #e5e7eb; border-radius:4px; padding:4px; background:repeating-conic-gradient(#6b7280 0% 25%, #9ca3af 0% 50%) 50% / 12px 12px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="logo_image" accept=".jpg,.jpeg,.png,.webp,.svg">
                </div>
                <div class="field">
                    <label>รูปสินค้าหลัก</label>
                    <?php if (!empty($item['hero_image'])): ?>
                        <div style="margin-bottom:8px;">
                            <img src="<?php echo h(upload_url($item['hero_image'], 'products')); ?>" alt="Hero" style="height:48px; object-fit:cover; border-radius:4px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="hero_image" accept=".jpg,.jpeg,.png,.webp,.svg">
                </div>
            </div>
            <div class="field">
                <label>เพิ่มรูปแกลเลอรี (เลือกได้หลายรูปพร้อมกัน)</label>
                <input type="file" name="gallery_images[]" accept=".jpg,.jpeg,.png,.webp,.svg" multiple>
            </div>
            <div class="field">
                <label>สถานะ</label>
                <select name="is_active">
                    <option value="1" <?php echo (int) $item['is_active'] === 1 ? 'selected' : ''; ?>>แสดง</option>
                    <option value="0" <?php echo (int) $item['is_active'] === 0 ? 'selected' : ''; ?>>ซ่อน</option>
                </select>
            </div>

            <div class="actions" style="margin-top:8px;">
                <button class="btn btn--primary" type="button" onclick="if (this.form.reportValidity()) { this.disabled = true; this.textContent = 'กำลังบันทึก...'; this.form.submit(); }"><i class="fa-solid fa-floppy-disk"></i>บันทึก</button>
                <button class="btn btn--muted" type="button" data-modal-close="productModal">ยกเลิก</button>
            </div>
        </form>

        <?php if ($editing && !empty($galleryImages)): ?>
            <div style="margin-top:20px; border-top:1px solid #e5e7eb; padding-top:14px;">
                <label style="font-weight:600; display:block; margin-bottom:10px;">
                    แกลเลอรีรูปภาพ (<?php echo count($galleryImages); ?> รูป)
                </label>
                <div style="display:flex; flex-wrap:wrap; gap:12px;">
                    <?php foreach ($galleryImages as $galleryImage): ?>
                        <div style="position:relative;">
                            <img src="<?php echo h(upload_url($galleryImage['image'], 'products')); ?>" alt=""
                                 style="width:90px; height:90px; object-fit:cover; border-radius:10px; border:1px solid #e5e7eb; background:#fff;">
                            <form method="post" onsubmit="return confirm('ลบรูปนี้ออกจากแกลเลอรี?');"
                                  style="position:absolute; top:-8px; right:-8px; margin:0;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete_image">
                                <input type="hidden" name="image_id" value="<?php echo (int) $galleryImage['id']; ?>">
                                <input type="hidden" name="product_id" value="<?php echo (int) $item['id']; ?>">
                                <button class="iconBtn iconBtn--danger" type="submit"
                                        style="width:24px; height:24px; border-radius:50%; padding:0; font-size:11px; line-height:1;">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
