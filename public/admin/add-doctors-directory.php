<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/helpers.php';
require_admin();

$page_title = 'เพิ่มแพทย์ใหม่';
$active_nav = 'doctors';
$db_ready = $pdo instanceof PDO;
$db_error = '';
$editing = false;

$item = [
    'id' => 0,
    'name_th' => '',
    'name_en' => '',
    'specialty' => '',
    'clinic_name' => '',
    'phone' => '',
    'email' => '',
    'bio' => '',
    'photo' => '',
    'is_active' => 1,
];
$errors = [];
$uploadDir = __DIR__ . '/../uploads/doctors';

if ($db_ready && isset($_GET['id'])) {
    try {
        $id = (int) $_GET['id'];
        $stmt = $pdo->prepare('SELECT * FROM doctors WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $item = array_merge($item, $row);
            $editing = true;
            $page_title = 'แก้ไขแพทย์';
        }
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $name_th = trim($_POST['name_th'] ?? '');
    $name_en = trim($_POST['name_en'] ?? '');
    $specialty = trim($_POST['specialty'] ?? '');
    $clinic_name = trim($_POST['clinic_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $current_photo = $_POST['current_photo'] ?? '';

    if ($name_th === '' || $specialty === '' || $clinic_name === '') {
        $errors[] = 'กรุณากรอกข้อมูลที่จำเป็น';
    }

    try {
        $newPhoto = save_uploaded_image($_FILES['photo'] ?? [], $uploadDir, 'doctor');
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
        $newPhoto = null;
    }

    if (!$errors) {
        $photo = $newPhoto ?: $current_photo;
        try {
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE doctors SET name_th = ?, name_en = ?, specialty = ?, clinic_name = ?, phone = ?, email = ?, bio = ?, photo = ?, is_active = ?, updated_at = NOW() WHERE id = ?');
                $stmt->execute([$name_th, $name_en, $specialty, $clinic_name, $phone, $email, $bio, $photo, $is_active, $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO doctors (name_th, name_en, specialty, clinic_name, phone, email, bio, photo, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
                $stmt->execute([$name_th, $name_en, $specialty, $clinic_name, $phone, $email, $bio, $photo, $is_active]);
            }

            if ($newPhoto && $current_photo && $current_photo !== $newPhoto) {
                $old = $uploadDir . '/' . $current_photo;
                if (is_file($old)) {
                    unlink($old);
                }
            }

            header('Location: /admin/doctors.php');
            exit;
        } catch (Exception $e) {
            $db_error = $e->getMessage();
        }
    }

    $item = array_merge($item, [
        'id' => $id,
        'name_th' => $name_th,
        'name_en' => $name_en,
        'specialty' => $specialty,
        'clinic_name' => $clinic_name,
        'phone' => $phone,
        'email' => $email,
        'bio' => $bio,
        'photo' => $current_photo,
        'is_active' => $is_active,
    ]);
}

require_once __DIR__ . '/partials/header.php';
?>

<h1 class="pageTitle"><?php echo $editing ? 'แก้ไขแพทย์' : 'เพิ่มแพทย์ใหม่'; ?></h1>

<?php if (!$db_ready || $db_error !== ''): ?>
    <div class="notice">Database not ready: <?php echo h($db_error ?: 'connection failed'); ?></div>
<?php endif; ?>
<?php foreach ($errors as $e): ?>
    <div class="error"><?php echo h($e); ?></div>
<?php endforeach; ?>

<div class="card" style="max-width:950px;">
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
        <input type="hidden" name="current_photo" value="<?php echo h($item['photo']); ?>">

        <div class="row">
            <div class="field">
                <label>ชื่อแพทย์ (ไทย) *</label>
                <input type="text" name="name_th" value="<?php echo h($item['name_th']); ?>" required>
            </div>
            <div class="field">
                <label>ชื่อแพทย์ (English)</label>
                <input type="text" name="name_en" value="<?php echo h($item['name_en']); ?>">
            </div>
        </div>

        <div class="row">
            <div class="field">
                <label>เฉพาะทาง *</label>
                <input type="text" name="specialty" value="<?php echo h($item['specialty']); ?>" required>
            </div>
            <div class="field">
                <label>สถานที่ทำงาน (Hospital / Clinic) *</label>
                <input type="text" name="clinic_name" value="<?php echo h($item['clinic_name']); ?>" required>
            </div>
        </div>

        <div class="field">
            <label>รูปโปรไฟล์</label>
            <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp">
            <?php if (!empty($item['photo'])): ?>
                <div class="muted" style="font-size:12px;">ไฟล์ปัจจุบัน: <?php echo h($item['photo']); ?></div>
            <?php endif; ?>
        </div>

        <div class="field">
            <label>ข้อมูลอื่นๆ</label>
            <textarea name="bio" rows="4"><?php echo h($item['bio']); ?></textarea>
        </div>

        <div class="row">
            <div class="field">
                <label>เบอร์ติดต่อ</label>
                <input type="text" name="phone" value="<?php echo h($item['phone']); ?>">
            </div>
            <div class="field">
                <label>อีเมล</label>
                <input type="email" name="email" value="<?php echo h($item['email']); ?>">
            </div>
        </div>

        <label><input type="checkbox" name="is_active" <?php echo ($item['is_active'] ?? 1) ? 'checked' : ''; ?>> แสดงบนเว็บไซต์</label>

        <div class="actions" style="margin-top:14px;">
            <button class="btn btn--primary" type="submit"><i class="fa-solid fa-floppy-disk"></i>บันทึก</button>
            <a class="btn btn--muted" href="/admin/doctors.php">ยกเลิก</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
