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

/**
 * Renumber every doctor to 10, 20, 30 ... in the order the site shows them.
 * Rows added before sort_order existed (or via a bare INSERT) sit at 0 and would
 * otherwise tie; spacing them out keeps sort_order unique so a swap is unambiguous.
 */
function normalize_doctor_order(PDO $pdo): void
{
    $ids = $pdo->query('SELECT id FROM doctors ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_COLUMN);
    $stmt = $pdo->prepare('UPDATE doctors SET sort_order = ? WHERE id = ?');
    foreach ($ids as $i => $id) {
        $stmt->execute([($i + 1) * 10, $id]);
    }
}

/** Swap one doctor with its neighbour in the global order. $dir is 'up' or 'down'. */
function move_doctor(PDO $pdo, int $id, string $dir): void
{
    normalize_doctor_order($pdo);

    $stmt = $pdo->prepare('SELECT sort_order FROM doctors WHERE id = ?');
    $stmt->execute([$id]);
    $current = $stmt->fetchColumn();
    if ($current === false) {
        return;
    }

    $neighbourSql = $dir === 'up'
        ? 'SELECT id, sort_order FROM doctors WHERE sort_order < ? ORDER BY sort_order DESC LIMIT 1'
        : 'SELECT id, sort_order FROM doctors WHERE sort_order > ? ORDER BY sort_order ASC LIMIT 1';
    $stmt = $pdo->prepare($neighbourSql);
    $stmt->execute([$current]);
    $neighbour = $stmt->fetch();
    if (!$neighbour) {
        return; // already first / last
    }

    $swap = $pdo->prepare('UPDATE doctors SET sort_order = ? WHERE id = ?');
    $swap->execute([(int) $neighbour['sort_order'], $id]);
    $swap->execute([(int) $current, (int) $neighbour['id']]);
}

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_valid_csrf();
        $action = (string) ($_POST['action'] ?? 'delete');
        $id = (int) ($_POST['id'] ?? 0);

        if ($action === 'move_up' || $action === 'move_down') {
            move_doctor($pdo, $id, $action === 'move_up' ? 'up' : 'down');
        } elseif ($action === 'save_order') {
            $stmt = $pdo->prepare('UPDATE doctors SET sort_order = ? WHERE id = ?');
            foreach ((array) ($_POST['order'] ?? []) as $rowId => $value) {
                $stmt->execute([(int) $value, (int) $rowId]);
            }
            normalize_doctor_order($pdo);
        } else {
            $stmt = $pdo->prepare('SELECT photo FROM doctors WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row) {
                $pdo->prepare('DELETE FROM doctors WHERE id = ?')->execute([$id]);
                if (!empty($row['photo'])) {
                    delete_uploaded_file($row['photo'], __DIR__ . '/../uploads/doctors', 'doctors');
                }
            }
        }
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
    header('Location: /admin/doctors.php?' . http_build_query(array_filter([
        'q' => trim($_GET['q'] ?? ''),
        'page' => (int) ($_GET['page'] ?? 1) > 1 ? (int) $_GET['page'] : null,
    ])));
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
        // Same order as the public directory, so the row order here is literally
        // what visitors see.
        $sql .= ' ORDER BY sort_order ASC, id ASC LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage);
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

<?php
// Declared outside the table so the number inputs can join it via form="..."
// instead of nesting a <form> inside the per-row delete/move forms.
$canReorder = $q === '';
?>
<form id="doctorOrderForm" method="post">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="action" value="save_order">
</form>

<?php if (!$canReorder): ?>
    <div class="notice">กำลังกรองด้วยคำค้น — ปุ่มเลื่อนลำดับปิดไว้ เพราะแถวที่ซ่อนอยู่จะทำให้สลับผิดตัว <a href="/admin/doctors.php">ล้างคำค้นเพื่อจัดลำดับ</a></div>
<?php endif; ?>

<div class="tableWrap">
    <table class="table">
        <thead>
            <tr>
                <th style="width:132px;">ลำดับ</th>
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
            <?php foreach ($rows as $i => $row): ?>
                <?php
                $globalIndex = ($page - 1) * $perPage + $i;
                $isFirst = $globalIndex === 0;
                $isLast = $globalIndex === $total - 1;
                ?>
                <tr>
                    <td>
                        <div class="actions" style="gap:4px; flex-wrap:nowrap; align-items:center;">
                            <form method="post" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="move_up">
                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                <button class="iconBtn" type="submit" title="เลื่อนขึ้น" <?php echo (!$canReorder || $isFirst) ? 'disabled style="opacity:.35; cursor:not-allowed;"' : ''; ?>><i class="fa-solid fa-arrow-up"></i></button>
                            </form>
                            <form method="post" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="move_down">
                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                <button class="iconBtn" type="submit" title="เลื่อนลง" <?php echo (!$canReorder || $isLast) ? 'disabled style="opacity:.35; cursor:not-allowed;"' : ''; ?>><i class="fa-solid fa-arrow-down"></i></button>
                            </form>
                            <input form="doctorOrderForm" type="number" name="order[<?php echo (int) $row['id']; ?>]"
                                   value="<?php echo (int) $row['sort_order']; ?>" min="0" step="1"
                                   style="width:62px; padding:6px 8px; border:1px solid #d6dbe3; border-radius:8px; text-align:center;">
                        </div>
                    </td>
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
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                <button class="iconBtn iconBtn--danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr><td colspan="8" class="muted">ยังไม่มีข้อมูลแพทย์</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($canReorder && !empty($rows)): ?>
    <div class="actions" style="margin-top:14px; align-items:center; gap:12px;">
        <button class="btn btn--primary" type="submit" form="doctorOrderForm"><i class="fa-solid fa-floppy-disk"></i>บันทึกลำดับ</button>
        <span class="muted">เลขน้อยขึ้นก่อน — แก้หลายช่องพร้อมกันได้ แล้วกดบันทึกครั้งเดียว ระบบจะเรียงเลขใหม่เป็น 10, 20, 30&hellip; ให้เอง</span>
    </div>
<?php endif; ?>

<?php if ($totalPages > 1): ?>
    <nav class="actions" style="justify-content:center; margin-top:16px;">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a class="btn <?php echo $p === $page ? 'btn--primary' : ''; ?>" href="/admin/doctors.php?q=<?php echo urlencode($q); ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
