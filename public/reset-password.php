<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

$db_ready = $pdo instanceof PDO;
$error    = '';
$success  = false;
$token    = trim((string) ($_REQUEST['token'] ?? ''));
$validToken = false;
$resetRow = null;

if ($db_ready && $token !== '' && preg_match('/^[a-f0-9]{64}$/', $token)) {
    $stmt = $pdo->prepare(
        'SELECT pr.id, pr.user_id, u.email
         FROM password_resets pr
         INNER JOIN admin_users u ON u.id = pr.user_id AND u.is_active = 1
         WHERE pr.token_hash = ? AND pr.used_at IS NULL AND pr.expires_at > NOW()
         LIMIT 1'
    );
    $stmt->execute([hash('sha256', $token)]);
    $resetRow = $stmt->fetch();
    $validToken = (bool) $resetRow;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['password_confirm'] ?? '');

    if (!csrf_verify()) {
        $error = 'คำขอไม่ถูกต้อง กรุณาลองใหม่';
    } elseif (strlen($password) < 8) {
        $error = 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร';
    } elseif ($password !== $confirm) {
        $error = 'รหัสผ่านทั้งสองช่องไม่ตรงกัน';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')
            ->execute([$hash, $resetRow['user_id']]);
        $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')
            ->execute([$resetRow['id']]);
        $pdo->prepare('DELETE FROM password_resets WHERE user_id = ? AND used_at IS NULL')
            ->execute([$resetRow['user_id']]);
        $success = true;
        $validToken = false;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <?php $page_title = 'ตั้งรหัสผ่านใหม่ - KNA Interpharma'; require_once __DIR__ . '/partials/site-head.php'; ?>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Top Bar -->
    <div class="bg-white border-b border-gray-200 py-3 px-6 flex items-center justify-between">
        <a href="/index.php">
            <img src="/uploads/kna-logo.png" alt="KNA Interpharma" class="h-10 w-auto"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <span class="hidden font-bold text-xl" style="color:#4B4899;">KNA Interpharma</span>
        </a>
        <a href="/login.php" class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition">
            <i class="fa-solid fa-arrow-left"></i>
            กลับหน้าเข้าสู่ระบบ
        </a>
    </div>

    <!-- Main Content -->
    <div class="flex items-center justify-center py-16 px-4">
        <div class="w-full max-w-md">

            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4" style="background:rgba(75,72,153,0.1);">
                    <i class="fa-solid fa-lock text-2xl" style="color:#4B4899;"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">ตั้งรหัสผ่านใหม่</h1>
                <?php if ($validToken): ?>
                    <p class="text-gray-500">สำหรับบัญชี <span class="font-semibold"><?php echo h($resetRow['email']); ?></span></p>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">

                <?php if ($error !== ''): ?>
                    <div class="mb-6 flex items-start gap-3 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm">
                        <i class="fa-solid fa-circle-exclamation mt-0.5 shrink-0"></i>
                        <?php echo h($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="mb-6 flex items-start gap-3 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-700 text-sm">
                        <i class="fa-solid fa-circle-check mt-0.5 shrink-0"></i>
                        <span>ตั้งรหัสผ่านใหม่เรียบร้อยแล้ว! ใช้รหัสผ่านใหม่เข้าสู่ระบบได้เลย</span>
                    </div>
                    <a href="/login.php" class="block w-full py-3 rounded-xl text-white font-bold text-base text-center transition hover:opacity-90" style="background:#4B4899;">
                        เข้าสู่ระบบ
                    </a>
                <?php elseif (!$validToken): ?>
                    <div class="mb-6 flex items-start gap-3 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-amber-800 text-sm">
                        <i class="fa-solid fa-triangle-exclamation mt-0.5 shrink-0"></i>
                        <span>ลิงก์รีเซ็ตรหัสผ่านไม่ถูกต้อง หมดอายุ หรือถูกใช้ไปแล้ว กรุณาขอลิงก์ใหม่</span>
                    </div>
                    <a href="/forgot-password.php" class="block w-full py-3 rounded-xl text-white font-bold text-base text-center transition hover:opacity-90" style="background:#4B4899;">
                        ขอลิงก์รีเซ็ตใหม่
                    </a>
                <?php else: ?>
                    <form method="post" action="/reset-password.php" class="space-y-5">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="token" value="<?php echo h($token); ?>">

                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">รหัสผ่านใหม่</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                <input
                                    id="password" name="password" type="password"
                                    autocomplete="new-password" required minlength="8"
                                    class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none transition"
                                    onfocus="this.style.borderColor='#4B4899'; this.style.boxShadow='0 0 0 3px rgba(75,72,153,0.15)';"
                                    onblur="this.style.borderColor=''; this.style.boxShadow='';"
                                    placeholder="อย่างน้อย 8 ตัวอักษร">
                            </div>
                        </div>

                        <div>
                            <label for="password_confirm" class="block text-sm font-semibold text-gray-700 mb-2">ยืนยันรหัสผ่านใหม่</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                <input
                                    id="password_confirm" name="password_confirm" type="password"
                                    autocomplete="new-password" required minlength="8"
                                    class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none transition"
                                    onfocus="this.style.borderColor='#4B4899'; this.style.boxShadow='0 0 0 3px rgba(75,72,153,0.15)';"
                                    onblur="this.style.borderColor=''; this.style.boxShadow='';"
                                    placeholder="พิมพ์รหัสผ่านใหม่อีกครั้ง">
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full py-3 rounded-xl text-white font-bold text-base transition hover:opacity-90 active:scale-95"
                            style="background:#4B4899;">
                            ตั้งรหัสผ่านใหม่
                        </button>
                    </form>
                <?php endif; ?>
            </div>

        </div>
    </div>

</body>
</html>
