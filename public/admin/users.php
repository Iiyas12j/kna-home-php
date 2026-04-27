<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/helpers.php';
require_admin();

function admin_role_label(string $role): string
{
    return match (normalize_member_role($role)) {
        'admin' => 'ผู้ดูแลระบบ',
        'doctor' => 'แพทย์',
        default => 'สมาชิก',
    };
}

function admin_role_badge_class(string $role): string
{
    return match (normalize_member_role($role)) {
        'admin' => 'style="background:#ede9fe;color:#5b21b6;"',
        'doctor' => 'style="background:#dbeafe;color:#1d4ed8;"',
        default => 'style="background:#ecfeff;color:#155e75;"',
    };
}

function active_admin_count(PDO $pdo): int
{
    $stmt = $pdo->query("SELECT COUNT(*) FROM admin_users WHERE role = 'admin' AND is_active = 1");
    return (int) $stmt->fetchColumn();
}

$page_title = 'Users';
$active_nav = 'users';
$db_ready = $pdo instanceof PDO;
$errors = [];
$notice = '';
$roleOptions = ['member', 'doctor', 'admin'];

$item = [
    'id' => 0,
    'email' => '',
    'name' => '',
    'role' => 'member',
    'is_active' => 1,
];
$editing = false;
$current_admin_id = (int) ($_SESSION['admin_id'] ?? 0);

if ($db_ready && isset($_GET['quick'], $_GET['id'])) {
    $id = (int) $_GET['id'];
    $quick = trim((string) $_GET['quick']);

    $stmt = $pdo->prepare('SELECT id, name, email, role, is_active FROM admin_users WHERE id = ?');
    $stmt->execute([$id]);
    $target = $stmt->fetch();

    if ($target) {
        $targetRole = normalize_member_role($target['role'] ?? 'member');
        $activeAdmins = active_admin_count($pdo);

        if ($quick === 'approve_doctor' && $targetRole === 'member') {
            $pdo->prepare("UPDATE admin_users SET role = 'doctor', is_active = 1 WHERE id = ?")->execute([$id]);
            header('Location: /admin/users.php?notice=doctor-approved');
            exit;
        }

        if ($quick === 'set_member' && $targetRole === 'doctor') {
            if ($id === $current_admin_id && $targetRole === 'admin' && $activeAdmins <= 1) {
                $errors[] = 'ไม่สามารถลดสิทธิ์ admin คนสุดท้ายได้';
            } else {
                $pdo->prepare("UPDATE admin_users SET role = 'member' WHERE id = ?")->execute([$id]);
                header('Location: /admin/users.php?notice=member-set');
                exit;
            }
        }

        if ($quick === 'toggle_active') {
            $nextActive = (int) ($target['is_active'] ?? 0) === 1 ? 0 : 1;

            if ($id === $current_admin_id && $targetRole === 'admin' && $nextActive === 0 && $activeAdmins <= 1) {
                $errors[] = 'ไม่สามารถปิดใช้งาน admin คนสุดท้ายได้';
            } else {
                $pdo->prepare('UPDATE admin_users SET is_active = ? WHERE id = ?')->execute([$nextActive, $id]);
                header('Location: /admin/users.php?notice=status-updated');
                exit;
            }
        }
    }
}

if ($db_ready && isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare('SELECT id, role, is_active FROM admin_users WHERE id = ?');
    $stmt->execute([$id]);
    $target = $stmt->fetch();

    if ($target) {
        $targetRole = normalize_member_role($target['role'] ?? 'member');
        $activeAdmins = active_admin_count($pdo);

        if ($id === $current_admin_id) {
            $errors[] = 'ไม่สามารถลบบัญชีที่กำลังใช้งานอยู่ได้';
        } elseif ($targetRole === 'admin' && (int) ($target['is_active'] ?? 0) === 1 && $activeAdmins <= 1) {
            $errors[] = 'ไม่สามารถลบ admin คนสุดท้ายได้';
        } else {
            $pdo->prepare('DELETE FROM admin_users WHERE id = ?')->execute([$id]);
            header('Location: /admin/users.php?notice=deleted');
            exit;
        }
    }
}

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $email = trim((string) ($_POST['email'] ?? ''));
    $name = trim((string) ($_POST['name'] ?? ''));
    $role = normalize_member_role($_POST['role'] ?? 'member');
    if (!in_array($role, $roleOptions, true)) {
        $role = 'member';
    }
    $is_active = (int) ($_POST['is_active'] ?? 1) === 1 ? 1 : 0;
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $name === '') {
        $errors[] = 'กรุณากรอกชื่อและอีเมล';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
    }

    $dupStmt = $pdo->prepare('SELECT id FROM admin_users WHERE email = ? AND id <> ? LIMIT 1');
    $dupStmt->execute([$email, $id]);
    if ($dupStmt->fetch()) {
        $errors[] = 'อีเมลนี้ถูกใช้งานแล้ว';
    }

    $activeAdmins = active_admin_count($pdo);
    if ($id > 0) {
        $oldStmt = $pdo->prepare('SELECT role, is_active FROM admin_users WHERE id = ?');
        $oldStmt->execute([$id]);
        $existing = $oldStmt->fetch();

        if ($existing) {
            $oldRole = normalize_member_role($existing['role'] ?? 'member');
            $oldActive = (int) ($existing['is_active'] ?? 0);
            $willLoseAdmin = $oldRole === 'admin' && $oldActive === 1 && ($role !== 'admin' || $is_active !== 1);

            if ($id === $current_admin_id && $willLoseAdmin && $activeAdmins <= 1) {
                $errors[] = 'ไม่สามารถลดสิทธิ์หรือปิดใช้งาน admin คนสุดท้ายได้';
            }
        }
    }

    if ($errors === []) {
        if ($id > 0) {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE admin_users SET email = ?, name = ?, role = ?, is_active = ?, password_hash = ? WHERE id = ?');
                $stmt->execute([$email, $name, $role, $is_active, $hash, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE admin_users SET email = ?, name = ?, role = ?, is_active = ? WHERE id = ?');
                $stmt->execute([$email, $name, $role, $is_active, $id]);
            }

            header('Location: /admin/users.php?notice=updated');
            exit;
        }

        if ($password === '') {
            $errors[] = 'กรุณากำหนดรหัสผ่านสำหรับผู้ใช้ใหม่';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO admin_users (email, password_hash, name, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
            $stmt->execute([$email, $hash, $name, $role, $is_active]);
            header('Location: /admin/users.php?notice=created');
            exit;
        }
    }

    $item = [
        'id' => $id,
        'email' => $email,
        'name' => $name,
        'role' => $role,
        'is_active' => $is_active,
    ];
    $editing = $id > 0;
}

if ($db_ready && isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        $item = $row;
        $editing = true;
    }
}

$noticeMap = [
    'created' => 'สร้างผู้ใช้ใหม่เรียบร้อย',
    'updated' => 'อัปเดตข้อมูลผู้ใช้เรียบร้อย',
    'deleted' => 'ลบผู้ใช้เรียบร้อย',
    'doctor-approved' => 'อนุมัติสิทธิ์แพทย์เรียบร้อย',
    'member-set' => 'ปรับสิทธิ์กลับเป็นสมาชิกเรียบร้อย',
    'status-updated' => 'อัปเดตสถานะการใช้งานเรียบร้อย',
];
$notice = $noticeMap[$_GET['notice'] ?? ''] ?? '';

$items = [];
$summary = [
    'all' => 0,
    'member' => 0,
    'doctor' => 0,
    'admin' => 0,
    'inactive' => 0,
];

if ($db_ready) {
    $items = $pdo->query('SELECT * FROM admin_users ORDER BY id DESC')->fetchAll();

    foreach ($items as $row) {
        $summary['all']++;
        $role = normalize_member_role($row['role'] ?? 'member');
        if (isset($summary[$role])) {
            $summary[$role]++;
        }
        if ((int) ($row['is_active'] ?? 0) !== 1) {
            $summary['inactive']++;
        }
    }
}

require_once __DIR__ . '/partials/header.php';
?>

<?php if (!$db_ready): ?>
    <div class="notice">Database not ready. Create tables before using Users.</div>
<?php endif; ?>

<?php if ($notice !== ''): ?>
    <div class="notice"><?php echo h($notice); ?></div>
<?php endif; ?>

<div class="grid grid--3" style="margin-bottom:16px;">
    <div class="card">
        <div class="kpi">
            <div>
                <div class="kpi__label">ผู้ใช้ทั้งหมด</div>
                <div class="kpi__value"><?php echo (int) $summary['all']; ?></div>
            </div>
            <div class="kpi__icon"><i class="fa-solid fa-users"></i></div>
        </div>
    </div>
    <div class="card">
        <div class="kpi">
            <div>
                <div class="kpi__label">แพทย์ / สมาชิก</div>
                <div class="kpi__value"><?php echo (int) $summary['doctor']; ?> / <?php echo (int) $summary['member']; ?></div>
            </div>
            <div class="kpi__icon"><i class="fa-solid fa-user-doctor"></i></div>
        </div>
    </div>
    <div class="card">
        <div class="kpi">
            <div>
                <div class="kpi__label">ปิดใช้งาน / Admin</div>
                <div class="kpi__value"><?php echo (int) $summary['inactive']; ?> / <?php echo (int) $summary['admin']; ?></div>
            </div>
            <div class="kpi__icon"><i class="fa-solid fa-shield-halved"></i></div>
        </div>
    </div>
</div>

<div class="card">
    <?php foreach ($errors as $e): ?>
        <div class="error"><?php echo h($e); ?></div>
    <?php endforeach; ?>

    <form method="post">
        <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">

        <div class="row">
            <div class="field">
                <label>Name</label>
                <input type="text" name="name" value="<?php echo h($item['name']); ?>" required>
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo h($item['email']); ?>" required>
            </div>
        </div>

        <div class="row">
            <div class="field">
                <label>Role</label>
                <select name="role">
                    <?php foreach ($roleOptions as $roleOption): ?>
                        <option value="<?php echo h($roleOption); ?>" <?php echo normalize_member_role($item['role'] ?? 'member') === $roleOption ? 'selected' : ''; ?>>
                            <?php echo h($roleOption . ' - ' . admin_role_label($roleOption)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Status</label>
                <select name="is_active">
                    <option value="1" <?php echo (int) ($item['is_active'] ?? 1) === 1 ? 'selected' : ''; ?>>เปิดใช้งาน</option>
                    <option value="0" <?php echo (int) ($item['is_active'] ?? 1) === 0 ? 'selected' : ''; ?>>ปิดใช้งาน</option>
                </select>
            </div>
            <div class="field">
                <label>Password <?php echo $editing ? '(leave blank to keep)' : ''; ?></label>
                <input type="password" name="password">
            </div>
        </div>

        <div class="notice" style="margin-bottom:0;">
            ผู้ใช้ที่สมัครผ่านหน้าเว็บจะเข้ามาเป็น <strong>member</strong> อัตโนมัติ
            ถ้าต้องการอนุมัติให้ดูวิดีโอเฉพาะแพทย์ ให้เปลี่ยน role เป็น <strong>doctor</strong>
        </div>

        <div class="actions" style="margin-top:12px;">
            <button class="btn btn--primary" type="submit"><?php echo $editing ? 'Update user' : 'Create user'; ?></button>
            <a class="btn" href="/admin/users.php">Reset</a>
        </div>
    </form>
</div>

<div class="tableWrap" style="margin-top:16px;">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Quick approve</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $row): ?>
            <?php
                $rowRole = normalize_member_role($row['role'] ?? 'member');
                $isActive = (int) ($row['is_active'] ?? 0) === 1;
                $isCurrentAdmin = (int) $row['id'] === $current_admin_id;
            ?>
            <tr>
                <td><?php echo (int) $row['id']; ?></td>
                <td>
                    <div style="font-weight:700;"><?php echo h($row['name']); ?></div>
                    <?php if ($isCurrentAdmin): ?>
                        <div class="muted" style="font-size:12px;">บัญชีที่กำลังใช้งาน</div>
                    <?php endif; ?>
                </td>
                <td><?php echo h($row['email']); ?></td>
                <td>
                    <span class="status" <?php echo admin_role_badge_class($rowRole); ?>>
                        <?php echo h(admin_role_label($rowRole)); ?>
                    </span>
                </td>
                <td>
                    <span class="status <?php echo $isActive ? 'status--on' : 'status--off'; ?>">
                        <?php echo $isActive ? 'Active' : 'Inactive'; ?>
                    </span>
                </td>
                <td class="actions" style="gap:8px; flex-wrap:wrap;">
                    <?php if ($rowRole === 'member'): ?>
                        <a class="btn btn--primary" href="/admin/users.php?quick=approve_doctor&id=<?php echo (int) $row['id']; ?>">อนุมัติแพทย์</a>
                    <?php endif; ?>
                    <?php if ($rowRole === 'doctor'): ?>
                        <a class="btn" href="/admin/users.php?quick=set_member&id=<?php echo (int) $row['id']; ?>">ตั้งเป็นสมาชิก</a>
                    <?php endif; ?>
                    <a class="btn <?php echo $isActive ? 'btn--muted' : 'btn--primary'; ?>" href="/admin/users.php?quick=toggle_active&id=<?php echo (int) $row['id']; ?>">
                        <?php echo $isActive ? 'ปิดใช้งาน' : 'เปิดใช้งาน'; ?>
                    </a>
                </td>
                <td class="actions">
                    <a class="btn" href="/admin/users.php?edit=<?php echo (int) $row['id']; ?>">Edit</a>
                    <a class="btn btn--danger" href="/admin/users.php?delete=<?php echo (int) $row['id']; ?>" onclick="return confirm('Delete this user?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
            <tr>
                <td colspan="7" class="muted">ยังไม่มีผู้ใช้ในระบบ</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
