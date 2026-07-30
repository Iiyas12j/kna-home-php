<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/helpers.php';
require_admin();

$page_title = 'ทำเนียบแพทย์';
$active_nav = 'doctors';
$db_ready = $pdo instanceof PDO;
$db_error = '';

$q = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$total = 0;
$totalPages = 1;

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_valid_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT photo FROM doctors WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $pdo->prepare('DELETE FROM doctors WHERE id = ?')->execute([$id]);
            if (!empty($row['photo'])) {
                delete_uploaded_file($row['photo'], __DIR__ . '/../uploads/doctors', 'doctors');
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
        $countSql = str_replace('SELECT *', 'SELECT COUNT(*)', $sql);
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $sql .= ' ORDER BY id DESC LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage);
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
                            <img src="<?php echo h(upload_url($row['photo'], 'doctors')); ?>" alt="" style="width:46px; height:46px; border-radius:50%; object-fit:cover;">
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
                            <form method="post" onsubmit="return confirm('ลบแพทย์รายการนี้?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                <button class="iconBtn iconBtn--danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                            </form>
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

<?php if ($totalPages > 1): ?>
    <nav class="actions" style="justify-content:center; margin-top:16px;">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a class="btn <?php echo $p === $page ? 'btn--primary' : ''; ?>" href="/admin/doctors.php?q=<?php echo urlencode($q); ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
