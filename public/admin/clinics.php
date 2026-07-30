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

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? 'save') === 'delete') {
    try {
        require_valid_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT hero_image, logo_image FROM clinics WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $pdo->prepare('DELETE FROM clinic_products WHERE clinic_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM clinics WHERE id = ?')->execute([$id]);
            foreach (['hero_image', 'logo_image'] as $col) {
                if (!empty($row[$col])) {
                    delete_uploaded_file($row[$col], $uploadDir, 'clinics');
                }
            }
        }
        header('Location: /admin/clinics.php');
        exit;
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

function clinic_import_normalize_phone(string $value): string
{
    $value = trim($value);
    if ($value === '' || $value === '0') {
        return '';
    }
    // Excel exports sometimes turn phone numbers into floats (924325498.0).
    if (preg_match('/^\d+(\.0)?$/', $value)) {
        $digits = preg_replace('/\.0$/', '', $value);
        if ($digits[0] !== '0') {
            $digits = '0' . $digits;
        }
        if (strlen($digits) === 10) {
            return substr($digits, 0, 3) . '-' . substr($digits, 3, 3) . '-' . substr($digits, 6);
        }
        if (strlen($digits) === 9) {
            return substr($digits, 0, 2) . '-' . substr($digits, 2, 3) . '-' . substr($digits, 5);
        }
        return $digits;
    }
    return $value;
}

function clinic_import_normalize_time(string $value): ?string
{
    $value = trim($value);
    if ($value === '' || $value === '-' || preg_match('/^0{1,2}:00(:00)?$/', $value)) {
        return null;
    }
    return preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value) ? $value : null;
}

$importReport = null;
if (isset($_GET['imported'])) {
    $importReport = [
        'updated' => (int) ($_GET['upd'] ?? 0),
        'inserted' => (int) ($_GET['ins'] ?? 0),
        'skipped' => (int) ($_GET['skip'] ?? 0),
        'links' => (int) ($_GET['links'] ?? 0),
    ];
}

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'import_csv') {
    try {
        require_valid_csrf();

        $file = $_FILES['csv_file'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('กรุณาเลือกไฟล์ CSV');
        }
        if (!preg_match('/\.csv$/i', (string) $file['name'])) {
            throw new RuntimeException('รองรับเฉพาะไฟล์ .csv');
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            throw new RuntimeException('เปิดไฟล์ไม่สำเร็จ');
        }

        $header = fgetcsv($handle, null, ',', '"', '\\');
        if (!$header) {
            throw new RuntimeException('ไฟล์ว่างเปล่า');
        }
        // Strip UTF-8 BOM and normalize header names.
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
        $header = array_map(static fn ($col) => strtolower(trim((string) $col)), $header);

        // CSV column -> DB column
        $columnMap = [
            'name' => 'name', 'logo_url' => 'logo_image', 'address' => 'address',
            'province' => 'province', 'district' => 'district', 'phone' => 'phone',
            'map_url' => 'map_url', 'website' => 'website_url', 'facebook' => 'facebook_url',
            'instagram' => 'instagram_url', 'tiktok' => 'tiktok_url', 'line' => 'line_id',
            'opening_time' => 'open_time', 'closing_time' => 'close_time',
        ];
        if (!in_array('name', $header, true)) {
            throw new RuntimeException('ไม่พบคอลัมน์ name ในไฟล์ — ตรวจสอบหัวตาราง CSV');
        }

        // product keyword map: "HYABELL ULTRA" etc. all resolve to the Hyabell product row
        $productKeywords = [];
        foreach ($products as $product) {
            $keyword = strtolower(strtok(trim((string) $product['name']), ' '));
            if ($keyword !== '') {
                $productKeywords[$keyword] = (int) $product['id'];
            }
        }

        $updated = $inserted = $skipped = $linkUpdates = 0;
        $pdo->beginTransaction();

        $existsStmt = $pdo->prepare('SELECT id FROM clinics WHERE id = ?');
        $byNameStmt = $pdo->prepare('SELECT id FROM clinics WHERE name = ? LIMIT 1');
        $delLinks = $pdo->prepare('DELETE FROM clinic_products WHERE clinic_id = ?');
        $insLink = $pdo->prepare('INSERT INTO clinic_products (clinic_id, product_id) VALUES (?, ?)');

        while (($row = fgetcsv($handle, null, ',', '"', '\\')) !== false) {
            if (count($row) === 1 && trim((string) $row[0]) === '') {
                continue;
            }
            $data = [];
            foreach ($header as $i => $col) {
                $data[$col] = trim((string) ($row[$i] ?? ''));
            }
            if (($data['name'] ?? '') === '') {
                $skipped++;
                continue;
            }

            // Build the column => value list from non-empty CSV cells only,
            // so blank cells never wipe existing data.
            $fields = [];
            foreach ($columnMap as $csvCol => $dbCol) {
                if (!array_key_exists($csvCol, $data)) {
                    continue;
                }
                $value = $data[$csvCol];
                if ($value === '' || $value === '-') {
                    continue;
                }
                if ($dbCol === 'phone') {
                    $value = clinic_import_normalize_phone($value);
                    if ($value === '') {
                        continue;
                    }
                } elseif ($dbCol === 'open_time' || $dbCol === 'close_time') {
                    $value = clinic_import_normalize_time($value);
                    if ($value === null) {
                        continue;
                    }
                }
                $fields[$dbCol] = $value;
            }
            if (array_key_exists('status', $data) && $data['status'] !== '') {
                $fields['is_active'] = strtolower($data['status']) === 'open' ? 1 : 0;
            }

            // Resolve target clinic: by id first, then by exact name.
            $csvId = (int) ($data['id'] ?? 0);
            $targetId = 0;
            if ($csvId > 0) {
                $existsStmt->execute([$csvId]);
                $targetId = $existsStmt->fetch() ? $csvId : 0;
            }
            if ($targetId === 0) {
                $byNameStmt->execute([$data['name']]);
                $found = $byNameStmt->fetch();
                if ($found) {
                    $targetId = (int) $found['id'];
                }
            }

            if ($targetId > 0) {
                if ($fields) {
                    $set = implode(', ', array_map(static fn ($col) => "$col = ?", array_keys($fields)));
                    $stmt = $pdo->prepare("UPDATE clinics SET $set, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([...array_values($fields), $targetId]);
                }
                $updated++;
            } else {
                if ($csvId > 0) {
                    $fields['id'] = $csvId;
                }
                $cols = implode(', ', array_keys($fields));
                $marks = implode(', ', array_fill(0, count($fields), '?'));
                $stmt = $pdo->prepare("INSERT INTO clinics ($cols, created_at) VALUES ($marks, NOW())");
                $stmt->execute(array_values($fields));
                $targetId = $csvId > 0 ? $csvId : (int) $pdo->lastInsertId();
                $inserted++;
            }

            // Product links: only touch them when the products cell is present & non-empty.
            if (array_key_exists('products', $data) && $data['products'] !== '') {
                $productIds = [];
                if (strtoupper($data['products']) !== 'FALSE') {
                    foreach (preg_split('/[,\/]/', $data['products']) as $entry) {
                        $entry = strtolower(trim($entry));
                        if ($entry === '') {
                            continue;
                        }
                        foreach ($productKeywords as $keyword => $productId) {
                            if (str_contains($entry, $keyword)) {
                                $productIds[$productId] = true;
                                break;
                            }
                        }
                    }
                }
                $delLinks->execute([$targetId]);
                foreach (array_keys($productIds) as $productId) {
                    $insLink->execute([$targetId, $productId]);
                }
                $linkUpdates++;
            }
        }
        fclose($handle);
        $pdo->commit();

        header('Location: /admin/clinics.php?imported=1&upd=' . $updated . '&ins=' . $inserted . '&skip=' . $skipped . '&links=' . $linkUpdates);
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errors[] = 'นำเข้า CSV ไม่สำเร็จ (ไม่มีข้อมูลถูกแก้ไข): ' . $e->getMessage();
    }
}

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($_POST['action'] ?? 'save', ['delete', 'import_csv'], true)) {
    require_valid_csrf();
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
                delete_uploaded_file($current_hero, $uploadDir, 'clinics');
            }
            if ($newLogo && $current_logo && $newLogo !== $current_logo) {
                delete_uploaded_file($current_logo, $uploadDir, 'clinics');
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
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$totalPages = 1;

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
        $countSql = 'SELECT COUNT(DISTINCT c.id) FROM clinics c';
        if ($filter_product > 0) {
            $countSql .= ' INNER JOIN clinic_products cp ON cp.clinic_id = c.id AND cp.product_id = ?';
        }
        $countSql .= substr($sql, strpos($sql, ' WHERE '));
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $sql .= ' ORDER BY c.id DESC LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
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

<?php if ($importReport !== null): ?>
    <div class="notice" style="background:#ecfdf5; border-color:#a7f3d0; color:#065f46;">
        <i class="fa-solid fa-circle-check"></i>
        นำเข้า CSV สำเร็จ — อัปเดต <?php echo $importReport['updated']; ?> คลินิก,
        เพิ่มใหม่ <?php echo $importReport['inserted']; ?> คลินิก,
        อัปเดตสินค้าประจำคลินิก <?php echo $importReport['links']; ?> รายการ<?php if ($importReport['skipped'] > 0): ?>,
        ข้ามแถวไม่มีชื่อ <?php echo $importReport['skipped']; ?> แถว<?php endif; ?>
    </div>
<?php endif; ?>

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
    <button class="btn btn--muted" data-modal-open="importModal"><i class="fa-solid fa-file-csv"></i>นำเข้า CSV</button>
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
                            <form method="post" onsubmit="return confirm('ลบคลินิกนี้?');">
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
                <tr><td colspan="5" class="muted">ยังไม่มีข้อมูลคลินิก</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
    <nav class="actions" style="justify-content:center; margin-top:16px;">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a class="btn <?php echo $p === $page ? 'btn--primary' : ''; ?>" href="/admin/clinics.php?q=<?php echo urlencode($filter_q); ?>&province=<?php echo urlencode($filter_province); ?>&product_id=<?php echo $filter_product; ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>

<div class="modal <?php echo $editing ? 'is-open' : ''; ?>" id="clinicModal">
    <div class="modal__dialog">
        <div class="modal__head">
            <h2 class="modal__title"><?php echo $editing ? 'แก้ไขคลินิก' : 'เพิ่มคลินิกใหม่'; ?></h2>
            <button class="closeBtn" data-modal-close="clinicModal"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="post" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="save">
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
                    <input type="file" name="logo_image" accept=".jpg,.jpeg,.png,.webp,.svg">
                </div>
                <div class="field">
                    <label>ภาพหลักคลินิก</label>
                    <input type="file" name="hero_image" accept=".jpg,.jpeg,.png,.webp,.svg">
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

<div class="modal" id="importModal">
    <div class="modal__dialog" style="max-width:640px;">
        <div class="modal__head">
            <h2 class="modal__title">นำเข้า / อัปเดตคลินิกจากไฟล์ CSV</h2>
            <button class="closeBtn" data-modal-close="importModal"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="post" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="import_csv">

            <div class="field">
                <label>ไฟล์ CSV</label>
                <input type="file" name="csv_file" accept=".csv" required>
            </div>

            <div style="font-size:13px; color:#475569; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px; margin-bottom:12px; line-height:1.8;">
                <div style="font-weight:700; margin-bottom:4px;"><i class="fa-solid fa-circle-info"></i> กติกาการนำเข้า</div>
                • หัวตารางที่รองรับ: <code>id, name, logo_url, address, province, district, phone, products, map_url, website, facebook, instagram, tiktok, line, opening_time, closing_time, status</code><br>
                • มี <b>id</b> ตรงกับในระบบ → อัปเดตข้อมูลคลินิกนั้น / ไม่มี id → จับคู่จากชื่อ / ไม่เจอเลย → เพิ่มเป็นคลินิกใหม่<br>
                • <b>ช่องว่างจะไม่ทับข้อมูลเดิม</b> — กรอกเฉพาะช่องที่ต้องการอัปเดตได้<br>
                • เบอร์โทรที่เพี้ยนจาก Excel (เช่น 924325498.0) จะถูกแก้ให้อัตโนมัติ<br>
                • ช่อง products ใส่ชื่อสินค้าคั่นด้วยจุลภาค เช่น <code>Hyabell,NeoFilera</code> (ใส่ <code>FALSE</code> = ล้างสินค้าออก)<br>
                • ถ้าเกิดข้อผิดพลาดระหว่างนำเข้า ระบบจะยกเลิกทั้งหมด ข้อมูลเดิมไม่เสียหาย
            </div>

            <div class="actions">
                <button class="btn btn--primary" type="submit" onclick="this.disabled=true; this.textContent='กำลังนำเข้า...'; this.form.submit();"><i class="fa-solid fa-upload"></i>นำเข้าข้อมูล</button>
                <button class="btn btn--muted" type="button" data-modal-close="importModal">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
