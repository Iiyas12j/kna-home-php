<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/helpers.php';
require_admin();

$page_title = 'ข้อความติดต่อ';
$active_nav = 'messages';
$db_ready = $pdo instanceof PDO;
$db_error = '';

$statusFilter = trim($_GET['status'] ?? '');
$q = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$total = 0;
$totalPages = 1;
$counts = ['new' => 0, 'read' => 0, 'replied' => 0];

$nlPage = max(1, (int) ($_GET['nl_page'] ?? 1));
$nlPerPage = 20;
$nlTotal = 0;
$nlTotalPages = 1;

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_valid_csrf();
        $entity = (string) ($_POST['entity'] ?? 'contact');
        $action = (string) ($_POST['action'] ?? '');
        $id = (int) ($_POST['id'] ?? 0);

        if ($entity === 'newsletter') {
            if ($action === 'delete') {
                $pdo->prepare('DELETE FROM newsletter_subscribers WHERE id = ?')->execute([$id]);
            }
        } else {
            if ($action === 'mark_read') {
                $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?")->execute([$id]);
            } elseif ($action === 'mark_replied') {
                $pdo->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = ?")->execute([$id]);
            } elseif ($action === 'delete') {
                $pdo->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([$id]);
            }
        }
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
    header('Location: /admin/messages.php?' . http_build_query(array_filter([
        'status' => $statusFilter,
        'q' => $q,
        'page' => $page > 1 ? $page : null,
        'nl_page' => $nlPage > 1 ? $nlPage : null,
    ])) . '#' . ($entity === 'newsletter' ? 'newsletter' : 'contact'));
    exit;
}

$rows = [];
$nlRows = [];
if ($db_ready) {
    try {
        foreach (array_keys($counts) as $s) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM contact_messages WHERE status = ?');
            $stmt->execute([$s]);
            $counts[$s] = (int) $stmt->fetchColumn();
        }

        $sql = 'SELECT * FROM contact_messages WHERE 1=1';
        $params = [];
        if ($statusFilter !== '' && in_array($statusFilter, ['new', 'read', 'replied'], true)) {
            $sql .= ' AND status = ?';
            $params[] = $statusFilter;
        }
        if ($q !== '') {
            $sql .= ' AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)';
            $params = array_merge($params, array_fill(0, 4, '%' . $q . '%'));
        }
        $countSql = str_replace('SELECT *', 'SELECT COUNT(*)', $sql);
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $sql .= ' ORDER BY created_at DESC LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // newsletter_subscribers may not exist yet on a fresh install — the
        // subscribe endpoint creates it lazily on first real submission.
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS newsletter_subscribers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(190) NOT NULL UNIQUE,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
        $nlTotal = (int) $pdo->query('SELECT COUNT(*) FROM newsletter_subscribers')->fetchColumn();
        $nlTotalPages = max(1, (int) ceil($nlTotal / $nlPerPage));
        $nlPage = min($nlPage, $nlTotalPages);
        $nlStmt = $pdo->prepare('SELECT * FROM newsletter_subscribers ORDER BY created_at DESC LIMIT ' . $nlPerPage . ' OFFSET ' . (($nlPage - 1) * $nlPerPage));
        $nlStmt->execute();
        $nlRows = $nlStmt->fetchAll();
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

function status_label(string $status): string
{
    return match ($status) {
        'new' => 'ใหม่',
        'read' => 'อ่านแล้ว',
        'replied' => 'ตอบแล้ว',
        default => $status,
    };
}

function status_class(string $status): string
{
    return match ($status) {
        'new' => 'status--off',
        'replied' => 'status--on',
        default => '',
    };
}

require_once __DIR__ . '/partials/header.php';
?>

<h1 class="pageTitle">ข้อความติดต่อ</h1>

<?php if (!$db_ready || $db_error !== ''): ?>
    <div class="notice">Database not ready: <?php echo h($db_error ?: 'connection failed'); ?></div>
<?php endif; ?>

<!-- ── Section 1: Contact form messages ─────────────────────────────────── -->
<h2 id="contact" style="font-size:20px; font-weight:700; margin:0 0 12px; color:#111827;"><i class="fa-solid fa-envelope" style="color:var(--primary); margin-right:8px;"></i>ข้อความจากฟอร์มติดต่อ</h2>

<div class="toolbar">
    <form method="get" class="actions" style="flex:1; min-width:320px; flex-wrap:wrap;">
        <input type="text" name="q" value="<?php echo h($q); ?>" placeholder="ค้นหาชื่อ/อีเมล/หัวข้อ/ข้อความ" style="width:100%; max-width:340px; padding:11px 12px; border:1px solid #d6dbe3; border-radius:10px;">
        <?php if ($statusFilter !== ''): ?>
            <input type="hidden" name="status" value="<?php echo h($statusFilter); ?>">
        <?php endif; ?>
        <button class="btn" type="submit"><i class="fa-solid fa-magnifying-glass"></i>ค้นหา</button>
    </form>
    <div class="actions">
        <a class="btn <?php echo $statusFilter === '' ? 'btn--primary' : ''; ?>" href="/admin/messages.php#contact">ทั้งหมด</a>
        <a class="btn <?php echo $statusFilter === 'new' ? 'btn--primary' : ''; ?>" href="/admin/messages.php?status=new#contact">ใหม่ <span class="chip"><?php echo $counts['new']; ?></span></a>
        <a class="btn <?php echo $statusFilter === 'read' ? 'btn--primary' : ''; ?>" href="/admin/messages.php?status=read#contact">อ่านแล้ว <span class="chip"><?php echo $counts['read']; ?></span></a>
        <a class="btn <?php echo $statusFilter === 'replied' ? 'btn--primary' : ''; ?>" href="/admin/messages.php?status=replied#contact">ตอบแล้ว <span class="chip"><?php echo $counts['replied']; ?></span></a>
    </div>
</div>

<div class="tableWrap">
    <table class="table">
        <thead>
            <tr>
                <th style="width:150px;">วันที่</th>
                <th>ผู้ติดต่อ</th>
                <th>หัวข้อ / ข้อความ</th>
                <th style="width:110px;">สถานะ</th>
                <th style="width:140px;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td style="white-space:normal;"><?php echo h(date('d/m/Y H:i', strtotime($row['created_at']))); ?></td>
                    <td style="white-space:normal;">
                        <div style="font-weight:600;"><?php echo h($row['name']); ?></div>
                        <div class="muted" style="font-size:13px;"><?php echo h($row['email']); ?></div>
                        <?php if (!empty($row['phone'])): ?>
                            <div class="muted" style="font-size:13px;"><?php echo h($row['phone']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:normal; max-width:420px;">
                        <div style="font-weight:600; margin-bottom:4px;"><?php echo h($row['subject']); ?></div>
                        <div class="muted" style="font-size:13px; white-space:pre-wrap;"><?php echo h($row['message']); ?></div>
                    </td>
                    <td>
                        <span class="status <?php echo status_class($row['status']); ?>"><?php echo h(status_label($row['status'])); ?></span>
                    </td>
                    <td>
                        <div class="actions">
                            <?php if ($row['status'] !== 'read'): ?>
                            <form method="post" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="entity" value="contact">
                                <input type="hidden" name="action" value="mark_read">
                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                <button class="iconBtn" type="submit" title="ทำเครื่องหมายว่าอ่านแล้ว"><i class="fa-solid fa-envelope-open"></i></button>
                            </form>
                            <?php endif; ?>
                            <?php if ($row['status'] !== 'replied'): ?>
                            <form method="post" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="entity" value="contact">
                                <input type="hidden" name="action" value="mark_replied">
                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                <button class="iconBtn" type="submit" title="ทำเครื่องหมายว่าตอบแล้ว"><i class="fa-solid fa-check"></i></button>
                            </form>
                            <?php endif; ?>
                            <a class="iconBtn" href="mailto:<?php echo h($row['email']); ?>" title="ตอบกลับทางอีเมล"><i class="fa-solid fa-reply"></i></a>
                            <form method="post" onsubmit="return confirm('ลบข้อความนี้?');" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="entity" value="contact">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                <button class="iconBtn iconBtn--danger" type="submit" title="ลบ"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr><td colspan="5" class="muted">ยังไม่มีข้อความติดต่อ</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
    <nav class="actions" style="justify-content:center; margin-top:16px;">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a class="btn <?php echo $p === $page ? 'btn--primary' : ''; ?>" href="/admin/messages.php?<?php echo http_build_query(array_filter(['status' => $statusFilter, 'q' => $q, 'page' => $p])); ?>#contact"><?php echo $p; ?></a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>

<!-- ── Section 2: Newsletter subscribers ────────────────────────────────── -->
<h2 id="newsletter" style="font-size:20px; font-weight:700; margin:40px 0 12px; color:#111827;"><i class="fa-solid fa-paper-plane" style="color:var(--primary); margin-right:8px;"></i>รายชื่อสมัครรับข่าวสาร (Newsletter) <span class="chip"><?php echo $nlTotal; ?></span></h2>

<div class="tableWrap">
    <table class="table">
        <thead>
            <tr>
                <th style="width:200px;">วันที่สมัคร</th>
                <th>อีเมล</th>
                <th style="width:100px;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($nlRows as $row): ?>
                <tr>
                    <td style="white-space:normal;"><?php echo h(date('d/m/Y H:i', strtotime($row['created_at']))); ?></td>
                    <td style="white-space:normal;"><?php echo h($row['email']); ?></td>
                    <td>
                        <div class="actions">
                            <a class="iconBtn" href="mailto:<?php echo h($row['email']); ?>" title="ส่งอีเมล"><i class="fa-solid fa-reply"></i></a>
                            <form method="post" onsubmit="return confirm('ลบรายชื่อนี้?');" style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="entity" value="newsletter">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                <button class="iconBtn iconBtn--danger" type="submit" title="ลบ"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($nlRows)): ?>
                <tr><td colspan="3" class="muted">ยังไม่มีคนสมัครรับข่าวสาร</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($nlTotalPages > 1): ?>
    <nav class="actions" style="justify-content:center; margin-top:16px;">
        <?php for ($p = 1; $p <= $nlTotalPages; $p++): ?>
            <a class="btn <?php echo $p === $nlPage ? 'btn--primary' : ''; ?>" href="/admin/messages.php?<?php echo http_build_query(array_filter(['nl_page' => $p])); ?>#newsletter"><?php echo $p; ?></a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
