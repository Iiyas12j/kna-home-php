<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

require_member();

$memberId = (int) ($_SESSION['member_id'] ?? 0);
$member   = null;
$error    = '';
$success  = '';

if ($pdo instanceof PDO) {
    $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE id = ? LIMIT 1');
    $stmt->execute([$memberId]);
    $member = $stmt->fetch();
}

if (!$member) {
    member_logout();
    header('Location: /login.php');
    exit;
}

$name           = (string) $member['name'];
$last_name      = (string) ($member['last_name'] ?? '');
$phone          = (string) ($member['phone'] ?? '');
$line_id        = (string) ($member['line_id'] ?? '');
$hospital_clinic = (string) ($member['hospital_clinic'] ?? '');
$province       = (string) ($member['province'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo instanceof PDO) {
    if (!csrf_verify()) {
        $error = 'คำขอไม่ถูกต้อง กรุณาลองใหม่';
    } else {
        $name            = trim((string) ($_POST['name'] ?? ''));
        $last_name       = trim((string) ($_POST['last_name'] ?? ''));
        $phone           = trim((string) ($_POST['phone'] ?? ''));
        $line_id         = trim((string) ($_POST['line_id'] ?? ''));
        $hospital_clinic = trim((string) ($_POST['hospital_clinic'] ?? ''));
        $province        = trim((string) ($_POST['province'] ?? ''));

        if ($name === '') {
            $error = 'กรุณากรอกชื่อ';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE admin_users
                 SET name = ?, last_name = ?, phone = ?, line_id = ?, hospital_clinic = ?, province = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $name,
                $last_name !== '' ? $last_name : null,
                $phone !== '' ? $phone : null,
                $line_id !== '' ? $line_id : null,
                $hospital_clinic !== '' ? $hospital_clinic : null,
                $province !== '' ? $province : null,
                $memberId,
            ]);

            $_SESSION['member_name'] = $name;
            $success = 'บันทึกข้อมูลเรียบร้อยแล้ว';
        }
    }
}

$role       = normalize_member_role($member['role'] ?? 'member');
$roleLabel  = member_role_label($role);
$memberSince = !empty($member['created_at']) ? date('d M Y', strtotime($member['created_at'])) : '-';
$lastLogin   = !empty($member['last_login_at']) ? date('d M Y H:i', strtotime($member['last_login_at'])) : 'ครั้งแรก';
$initial     = mb_strtoupper(mb_substr($name !== '' ? $name : $member['email'], 0, 1));

$siteHeaderActive = 'profile';
$page_title = 'โปรไฟล์ของฉัน - KNA Interpharma';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <?php require_once __DIR__ . '/partials/site-head.php'; ?>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Header card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 mb-6 flex items-center gap-5">
            <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold text-white shrink-0" style="background:#4B4899;">
                <?php echo h($initial); ?>
            </div>
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 truncate"><?php echo h($name !== '' ? $name . ' ' . $last_name : $member['email']); ?></h1>
                <div class="flex flex-wrap items-center gap-2 mt-1.5">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold" style="background:#EFEEFA; color:#332F73;">
                        <?php echo h($roleLabel); ?>
                    </span>
                    <span class="text-sm text-gray-400"><?php echo h($member['email']); ?></span>
                </div>
            </div>
        </div>

        <!-- Meta info -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-100 p-4">
                <p class="text-xs text-gray-400 mb-1">เป็นสมาชิกตั้งแต่</p>
                <p class="font-semibold text-gray-800"><?php echo h($memberSince); ?></p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4">
                <p class="text-xs text-gray-400 mb-1">เข้าสู่ระบบล่าสุด</p>
                <p class="font-semibold text-gray-800"><?php echo h($lastLogin); ?></p>
            </div>
        </div>

        <!-- Edit form -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <h2 class="text-lg font-bold text-gray-900 mb-6">ข้อมูลส่วนตัว</h2>

            <?php if ($error !== ''): ?>
                <div class="mb-6 flex items-start gap-3 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm">
                    <i class="fa-solid fa-circle-exclamation mt-0.5 shrink-0"></i> <?php echo h($error); ?>
                </div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="mb-6 flex items-start gap-3 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-700 text-sm">
                    <i class="fa-solid fa-circle-check mt-0.5 shrink-0"></i> <?php echo h($success); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/profile.php" class="space-y-5">
                <?php echo csrf_field(); ?>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">ชื่อ</label>
                        <input class="kna-input w-full px-4 py-2.5 border border-gray-300 rounded-xl" type="text" name="name" value="<?php echo h($name); ?>" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">นามสกุล</label>
                        <input class="kna-input w-full px-4 py-2.5 border border-gray-300 rounded-xl" type="text" name="last_name" value="<?php echo h($last_name); ?>">
                    </div>
                </div>

                <?php if ($role === 'doctor'): ?>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">เลข อว. (แก้ไขไม่ได้)</label>
                    <input class="w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 text-gray-500" type="text" value="<?php echo h($member['doctor_license_no'] ?? ''); ?>" disabled>
                </div>
                <?php endif; ?>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">โรงพยาบาล / คลินิกที่สังกัด</label>
                    <input class="kna-input w-full px-4 py-2.5 border border-gray-300 rounded-xl" type="text" name="hospital_clinic" value="<?php echo h($hospital_clinic); ?>" placeholder="ชื่อสถานพยาบาลที่สังกัด">
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">จังหวัด</label>
                        <input class="kna-input w-full px-4 py-2.5 border border-gray-300 rounded-xl" type="text" name="province" value="<?php echo h($province); ?>" placeholder="เช่น กรุงเทพมหานคร">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">เบอร์โทร</label>
                        <input class="kna-input w-full px-4 py-2.5 border border-gray-300 rounded-xl" type="tel" name="phone" value="<?php echo h($phone); ?>" placeholder="08X-XXX-XXXX">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Line ID</label>
                    <input class="kna-input w-full px-4 py-2.5 border border-gray-300 rounded-xl" type="text" name="line_id" value="<?php echo h($line_id); ?>" placeholder="@lineid (ถ้ามี)">
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <button type="submit" class="px-6 py-3 rounded-xl text-white font-bold text-sm transition hover:opacity-90" style="background:#4B4899;">
                        บันทึกข้อมูล
                    </button>
                    <a href="/logout.php?redirect=/index.php" class="px-6 py-3 rounded-xl font-bold text-sm text-gray-600 border border-gray-300 hover:bg-gray-50 transition">
                        ออกจากระบบ
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>

    <style>
        .kna-input:focus { outline: none; border-color: #4B4899; box-shadow: 0 0 0 3px rgba(75,72,153,0.15); }
    </style>
</body>
</html>
