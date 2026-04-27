<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/helpers.php';
require_admin();

$page_title = 'จัดการคลินิก';
$active_nav = 'clinics';
$db_ready = $pdo instanceof PDO;
$db_error = '';
$errors = [];

$uploadDir = __DIR__ . '/../uploads/clinics';
$item = [
    'id' => 0,
    'name' => '',
    'address' => '',
    'province' => '',
    'district' => '',
    'phone' => '',
    'hero_image' => '',
    'logo_image' => '',
    'map_url' => '',
    'website_url' => '',
    'facebook_url' => '',
    'instagram_url' => '',
    'tiktok_url' => '',
    'line_id' => '',
    'open_time' => '',
    'close_time' => '',
    'is_active' => 1,
];
$selected_products = [];
$editing = false;

$products = [];
if ($db_ready) {
    try {
        $products = $pdo->query('SELECT id, name FROM products ORDER BY name ASC')->fetchAll();
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

if ($db_ready && isset($_GET['delete'])) {
    try {
        $id = (int) $_GET['delete'];
        $stmt = $pdo->prepare('SELECT hero_image, logo_image FROM clinics WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $pdo->prepare('DELETE FROM clinic_products WHERE clinic_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM clinics WHERE id = ?')->execute([$id]);
            foreach (['hero_image', 'logo_image'] as $col) {
                if (!empty($row[$col])) {
                    $file = $uploadDir . '/' . $row[$col];
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }
        header('Location: /admin/clinics.php');
        exit;
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $map_url = trim($_POST['map_url'] ?? '');
    $website_url = trim($_POST['website_url'] ?? '');
    $facebook_url = trim($_POST['facebook_url'] ?? '');
    $instagram_url = trim($_POST['instagram_url'] ?? '');
    $tiktok_url = trim($_POST['tiktok_url'] ?? '');
    $line_id = trim($_POST['line_id'] ?? '');
    $open_time = trim($_POST['open_time'] ?? '');
    $close_time = trim($_POST['close_time'] ?? '');
    $is_active = (int) ($_POST['is_active'] ?? 0) === 1 ? 1 : 0;
    $selected_products = array_map('intval', $_POST['products'] ?? []);

    $current_hero = $_POST['current_hero'] ?? '';
    $current_logo = $_POST['current_logo'] ?? '';

    if ($name === '') {
        $errors[] = 'กรุณากรอกชื่อคลินิก';
    }

    try {
        $newHero = save_uploaded_image($_FILES['hero_image'] ?? [], $uploadDir, 'clinic');
    } catch (Exception $e) {
        $newHero = null;
        $errors[] = $e->getMessage();
    }

    try {
        $newLogo = save_uploaded_image($_FILES['logo_image'] ?? [], $uploadDir, 'logo');
    } catch (Exception $e) {
        $newLogo = null;
        $errors[] = $e->getMessage();
    }

    if (!$errors) {
        $hero_image = $newHero ?: $current_hero;
        $logo_image = $newLogo ?: $current_logo;

        try {
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE clinics SET name = ?, address = ?, province = ?, district = ?, phone = ?, hero_image = ?, logo_image = ?, map_url = ?, website_url = ?, facebook_url = ?, instagram_url = ?, tiktok_url = ?, line_id = ?, open_time = ?, close_time = ?, is_active = ?, updated_at = NOW() WHERE id = ?');
                $stmt->execute([$name, $address, $province, $district, $phone, $hero_image, $logo_image, $map_url, $website_url, $facebook_url, $instagram_url, $tiktok_url, $line_id, $open_time ?: null, $close_time ?: null, $is_active, $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO clinics (name, address, province, district, phone, hero_image, logo_image, map_url, website_url, facebook_url, instagram_url, tiktok_url, line_id, open_time, close_time, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
                $stmt->execute([$name, $address, $province, $district, $phone, $hero_image, $logo_image, $map_url, $website_url, $facebook_url, $instagram_url, $tiktok_url, $line_id, $open_time ?: null, $close_time ?: null, $is_active]);
                $id = (int) $pdo->lastInsertId();
            }

            $pdo->prepare('DELETE FROM clinic_products WHERE clinic_id = ?')->execute([$id]);
            if (!empty($selected_products)) {
                $ins = $pdo->prepare('INSERT INTO clinic_products (clinic_id, product_id) VALUES (?, ?)');
                foreach ($selected_products as $pid) {
                    $ins->execute([$id, $pid]);
                }
            }

            if ($newHero && $current_hero && $newHero !== $current_hero) {
                $old = $uploadDir . '/' . $current_hero;
                if (is_file($old)) {
                    unlink($old);
                }
            }
            if ($newLogo && $current_logo && $newLogo !== $current_logo) {
                $old = $uploadDir . '/' . $current_logo;
                if (is_file($old)) {
                    unlink($old);
                }
            }

            header('Location: /admin/clinics.php');
            exit;
        } catch (Exception $e) {
            $db_error = $e->getMessage();
        }
    }

    $item = array_merge($item, [
        'id' => $id,
        'name' => $name,
        'address' => $address,
        'province' => $province,
        'district' => $district,
        'phone' => $phone,
        'hero_image' => $current_hero,
        'logo_image' => $current_logo,
        'map_url' => $map_url,
        'website_url' => $website_url,
        'facebook_url' => $facebook_url,
        'instagram_url' => $instagram_url,
        'tiktok_url' => $tiktok_url,
        'line_id' => $line_id,
        'open_time' => $open_time,
        'close_time' => $close_time,
        'is_active' => $is_active,
    ]);
    $editing = $id > 0;
}

if ($db_ready && isset($_GET['edit'])) {
    try {
        $id = (int) $_GET['edit'];
        $stmt = $pdo->prepare('SELECT * FROM clinics WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $item = array_merge($item, $row);
            if (!empty($item['open_time'])) {
                $item['open_time'] = substr((string) $item['open_time'], 0, 5);
            }
            if (!empty($item['close_time'])) {
                $item['close_time'] = substr((string) $item['close_time'], 0, 5);
            }
            $editing = true;

            $stmt = $pdo->prepare('SELECT product_id FROM clinic_products WHERE clinic_id = ?');
            $stmt->execute([$id]);
            $selected_products = array_map('intval', array_column($stmt->fetchAll(), 'product_id'));
        }
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

$filter_q = trim($_GET['q'] ?? '');
$filter_province = trim($_GET['province'] ?? '');
$filter_product = (int) ($_GET['product_id'] ?? 0);
$rows = [];
$total = 0;

if ($db_ready) {
    try {
        $sql = 'SELECT c.* FROM clinics c';
        $params = [];
        if ($filter_product > 0) {
            $sql .= ' INNER JOIN clinic_products cp ON cp.clinic_id = c.id AND cp.product_id = ?';
            $params[] = $filter_product;
        }
        $sql .= ' WHERE 1=1';

        if ($filter_q !== '') {
            $sql .= ' AND (c.name LIKE ? OR c.address LIKE ? OR c.phone LIKE ?)';
            $params[] = '%' . $filter_q . '%';
            $params[] = '%' . $filter_q . '%';
            $params[] = '%' . $filter_q . '%';
        }
        if ($filter_province !== '') {
            $sql .= ' AND c.province = ?';
            $params[] = $filter_province;
        }
        $sql .= ' ORDER BY c.id DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        $total = count($rows);
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

$province_list = [];
if ($db_ready) {
    try {
        $province_rows = $pdo->query("SELECT DISTINCT province FROM clinics WHERE province IS NOT NULL AND province <> '' ORDER BY province ASC")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($province_rows as $province_name) {
            $province_list[$province_name] = true;
        }
    } catch (Exception $e) {
        if ($db_error === '') {
            $db_error = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/partials/header.php';
?>

<h1 class="pageTitle">จัดการคลินิก</h1>

<?php if (!$db_ready || $db_error !== ''): ?>
    <div class="notice">Database not ready: <?php echo h($db_error ?: 'connection failed'); ?></div>
<?php endif; ?>
<?php foreach ($errors as $e): ?>
    <div class="error"><?php echo h($e); ?></div>
<?php endforeach; ?>

<div class="toolbar">
    <form method="get" class="row" style="flex:1; min-width:780px;">
        <div class="field" style="margin:0;">
            <label>ค้นหา</label>
            <input type="text" name="q" value="<?php echo h($filter_q); ?>" placeholder="ชื่อคลินิก, ที่อยู่, หรือเบอร์โทร">
        </div>
        <div class="field" style="margin:0;">
            <label>จังหวัด</label>
            <select name="province">
                <option value="">-- ทุกจังหวัด --</option>
                <?php foreach (array_keys($province_list) as $province): ?>
                    <option value="<?php echo h($province); ?>" <?php echo $filter_province === $province ? 'selected' : ''; ?>><?php echo h($province); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field" style="margin:0;">
            <label>สินค้า</label>
            <select name="product_id">
                <option value="0">-- ทุกสินค้า --</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?php echo (int) $product['id']; ?>" <?php echo $filter_product === (int) $product['id'] ? 'selected' : ''; ?>><?php echo h($product['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="actions" style="align-items:flex-end;">
            <button class="btn btn--primary" type="submit"><i class="fa-solid fa-filter"></i>กรองข้อมูล</button>
        </div>
    </form>
    <button class="btn btn--primary" data-modal-open="clinicModal"><i class="fa-solid fa-plus"></i>เพิ่มคลินิกใหม่</button>
</div>

<div style="margin-bottom:10px; font-weight:700;">ทั้งหมด <?php echo (int) $total; ?> คลินิก</div>

<div class="tableWrap">
    <table class="table">
        <thead>
            <tr>
                <th>ชื่อคลินิก</th>
                <th>จังหวัด</th>
                <th>โทรศัพท์</th>
                <th>สถานะ</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo h($row['name']); ?></td>
                    <td><?php echo h($row['province']); ?></td>
                    <td><?php echo h($row['phone']); ?></td>
                    <td><span class="status <?php echo (int) $row['is_active'] === 1 ? 'status--on' : 'status--off'; ?>"><?php echo (int) $row['is_active'] === 1 ? 'เปิดทำการ' : 'ปิด'; ?></span></td>
                    <td>
                        <div class="actions">
                            <a class="iconBtn" href="/admin/clinics.php?edit=<?php echo (int) $row['id']; ?>"><i class="fa-solid fa-pen-to-square"></i></a>
                            <a class="iconBtn iconBtn--danger" href="/admin/clinics.php?delete=<?php echo (int) $row['id']; ?>" onclick="return confirm('ลบคลินิกนี้?');"><i class="fa-solid fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr><td colspan="5" class="muted">ยังไม่มีข้อมูลคลินิก</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal <?php echo $editing ? 'is-open' : ''; ?>" id="clinicModal">
    <div class="modal__dialog">
        <div class="modal__head">
            <h2 class="modal__title"><?php echo $editing ? 'แก้ไขคลินิก' : 'เพิ่มคลินิกใหม่'; ?></h2>
            <button class="closeBtn" data-modal-close="clinicModal"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
            <input type="hidden" name="current_hero" value="<?php echo h($item['hero_image']); ?>">
            <input type="hidden" name="current_logo" value="<?php echo h($item['logo_image']); ?>">

            <div class="field">
                <label>ชื่อคลินิก *</label>
                <input type="text" name="name" value="<?php echo h($item['name']); ?>" required>
            </div>

            <div class="row">
                <div class="field">
                    <label>Logo คลินิก</label>
                    <input type="file" name="logo_image" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <div class="field">
                    <label>ภาพหลักคลินิก</label>
                    <input type="file" name="hero_image" accept=".jpg,.jpeg,.png,.webp">
                </div>
            </div>

            <div class="field">
                <label>ที่อยู่ *</label>
                <textarea name="address"><?php echo h($item['address']); ?></textarea>
            </div>

            <div class="row">
                <div class="field">
                    <label>จังหวัด *</label>
                    <input type="text" name="province" value="<?php echo h($item['province']); ?>">
                </div>
                <div class="field">
                    <label>อำเภอ/เขต</label>
                    <input type="text" name="district" value="<?php echo h($item['district']); ?>">
                </div>
                <div class="field">
                    <label>เบอร์ติดต่อ *</label>
                    <input type="text" name="phone" value="<?php echo h($item['phone']); ?>" placeholder="0X-XXXX-XXXX">
                </div>
            </div>

            <div class="field">
                <label>สินค้าที่คลินิกมี</label>
                <div class="row" style="border:1px solid #d6dbe3; border-radius:10px; padding:10px;">
                    <?php foreach ($products as $product): ?>
                        <label><input type="checkbox" name="products[]" value="<?php echo (int) $product['id']; ?>" <?php echo in_array((int) $product['id'], $selected_products, true) ? 'checked' : ''; ?>> <?php echo h($product['name']); ?></label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="field">
                <label>Google Maps (URL)</label>
                <input type="text" name="map_url" value="<?php echo h($item['map_url']); ?>" placeholder="https://maps.google.com/...">
            </div>

            <div style="font-weight:700; margin:8px 0;">Social Media</div>
            <div class="row">
                <div class="field">
                    <label>Website</label>
                    <input type="text" name="website_url" value="<?php echo h($item['website_url']); ?>" placeholder="https://example.com">
                </div>
                <div class="field">
                    <label>Facebook</label>
                    <input type="text" name="facebook_url" value="<?php echo h($item['facebook_url']); ?>" placeholder="https://facebook.com/...">
                </div>
                <div class="field">
                    <label>Instagram</label>
                    <input type="text" name="instagram_url" value="<?php echo h($item['instagram_url']); ?>" placeholder="https://instagram.com/...">
                </div>
                <div class="field">
                    <label>TikTok</label>
                    <input type="text" name="tiktok_url" value="<?php echo h($item['tiktok_url']); ?>" placeholder="https://tiktok.com/@...">
                </div>
                <div class="field">
                    <label>LINE</label>
                    <input type="text" name="line_id" value="<?php echo h($item['line_id']); ?>" placeholder="@lineid หรือ URL">
                </div>
                <div class="field">
                    <label>เวลาเปิด</label>
                    <input type="time" name="open_time" value="<?php echo h($item['open_time']); ?>">
                </div>
                <div class="field">
                    <label>เวลาปิด</label>
                    <input type="time" name="close_time" value="<?php echo h($item['close_time']); ?>">
                </div>
                <div class="field">
                    <label>สถานะ</label>
                    <select name="is_active">
                        <option value="1" <?php echo (int) $item['is_active'] === 1 ? 'selected' : ''; ?>>เปิดทำการ</option>
                        <option value="0" <?php echo (int) $item['is_active'] === 0 ? 'selected' : ''; ?>>ปิด</option>
                    </select>
                </div>
            </div>

            <div class="actions" style="margin-top:12px;">
                <button class="btn btn--primary" type="submit"><i class="fa-solid fa-floppy-disk"></i>บันทึก</button>
                <button class="btn btn--muted" type="button" data-modal-close="clinicModal">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
