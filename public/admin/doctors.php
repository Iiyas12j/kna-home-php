<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/helpers.php';
require_admin();

$page_title = 'ทำเนียบแพทย์';
$active_nav = 'doctors';
$db_ready = $pdo instanceof PDO;
$db_error = '';

$q = trim($_GET['q'] ?? '');

if ($db_ready && isset($_GET['delete'])) {
    try {
        $id = (int) $_GET['delete'];
        $stmt = $pdo->prepare('SELECT photo FROM doctors WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $pdo->prepare('DELETE FROM doctors WHERE id = ?')->execute([$id]);
            if (!empty($row['photo'])) {
                $file = __DIR__ . '/../uploads/doctors/' . $row['photo'];
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
    header('Location: /admin/doctors.php');
    exit;
}

$rows = [];
if ($db_ready) {
    try {
        $sql = 'SELECT * FROM doctors WHERE 1=1';
        $params = [];
        if ($q !== '') {
            $sql .= ' AND (name_th LIKE ? OR name_en LIKE ? OR clinic_name LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }
        $sql .= ' ORDER BY id DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

require_once __DIR__ . '/partials/header.php';
?>

<h1 class="pageTitle">ทำเนียบแพทย์</h1>

<?php if (!$db_ready || $db_error !== ''): ?>
    <div class="notice">Database not ready: <?php echo h($db_error ?: 'connection failed'); ?></div>
<?php endif; ?>

<div class="toolbar">
    <form method="get" class="actions" style="flex:1; min-width:320px;">
        <input type="text" name="q" value="<?php echo h($q); ?>" placeholder="ค้นหาชื่อแพทย์/คลินิก" style="width:100%; max-width:460px; padding:11px 12px; border:1px solid #d6dbe3; border-radius:10px;">
        <button class="btn" type="submit"><i class="fa-solid fa-magnifying-glass"></i>ค้นหา</button>
    </form>
    <a class="btn btn--primary" href="/admin/add-doctors-directory.php"><i class="fa-solid fa-plus"></i>เพิ่มแพทย์ใหม่</a>
</div>

<div class="tableWrap">
    <table class="table">
        <thead>
            <tr>
                <th>รูปโปรไฟล์</th>
                <th>ชื่อแพทย์ (ไทย)</th>
                <th>ชื่อแพทย์ (EN)</th>
                <th>เฉพาะทาง</th>
                <th>สถานที่ทำงาน</th>
                <th>สถานะ</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <?php if (!empty($row['photo'])): ?>
                            <img src="/uploads/doctors/<?php echo h($row['photo']); ?>" alt="" style="width:46px; height:46px; border-radius:50%; object-fit:cover;">
                        <?php else: ?>
                            <div style="width:46px; height:46px; border-radius:50%; background:#e5e7eb;"></div>
                        <?php endif; ?>
                    </td>
                    <td><?php echo h($row['name_th']); ?></td>
                    <td><?php echo h($row['name_en']); ?></td>
                    <td><?php echo h($row['specialty']); ?></td>
                    <td><?php echo h($row['clinic_name']); ?></td>
                    <td>
                        <span class="status <?php echo (int) $row['is_active'] === 1 ? 'status--on' : 'status--off'; ?>"><?php echo (int) $row['is_active'] === 1 ? 'แสดง' : 'ซ่อน'; ?></span>
                    </td>
                    <td>
                        <div class="actions">
                            <a class="iconBtn" href="/admin/add-doctors-directory.php?id=<?php echo (int) $row['id']; ?>"><i class="fa-solid fa-pen"></i></a>
                            <a class="iconBtn iconBtn--danger" href="/admin/doctors.php?delete=<?php echo (int) $row['id']; ?>" onclick="return confirm('ลบแพทย์รายการนี้?');"><i class="fa-solid fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr><td colspan="7" class="muted">ยังไม่มีข้อมูลแพทย์</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
