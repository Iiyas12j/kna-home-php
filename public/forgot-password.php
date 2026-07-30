<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

$db_ready = $pdo instanceof PDO;
$error    = '';
$success  = false;
$email    = '';
$devResetLink = '';

$rl_key = 'forgot_password:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));

    if (!rate_limit_check($rl_key)) {
        $wait  = (int) ceil(rate_limit_wait_seconds($rl_key) / 60);
        $error = "ส่งคำขอมากเกินไป กรุณารอ {$wait} นาทีแล้วลองใหม่";
    } elseif (!csrf_verify()) {
        $error = 'คำขอไม่ถูกต้อง กรุณาลองใหม่';
    } elseif (!$db_ready) {
        $error = 'ระบบฐานข้อมูลยังไม่พร้อมใช้งาน';
    } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'กรุณากรอกอีเมลให้ถูกต้อง';
    } else {
        rate_limit_hit($rl_key);

        $stmt = $pdo->prepare('SELECT id, email, name FROM admin_users WHERE email = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Always report success so attackers cannot probe which emails exist.
        $success = true;

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);

            $pdo->prepare('DELETE FROM password_resets WHERE user_id = ? AND used_at IS NULL')->execute([$user['id']]);
            $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))')
                ->execute([$user['id'], $tokenHash]);

            $resetLink = rtrim(BASE_URL, '/') . '/reset-password.php?token=' . $token;

            if (APP_ENV === 'development') {
                $devResetLink = $resetLink;
            } else {
                $subject = 'รีเซ็ตรหัสผ่าน - KNA Interpharma';
                $body = "สวัสดีคุณ {$user['name']}\n\n"
                    . "มีคำขอรีเซ็ตรหัสผ่านสำหรับบัญชีของคุณ กดลิงก์ด้านล่างเพื่อตั้งรหัสผ่านใหม่ (ลิงก์หมดอายุใน 30 นาที):\n\n"
                    . $resetLink . "\n\n"
                    . "หากคุณไม่ได้ขอรีเซ็ตรหัสผ่าน กรุณาเพิกเฉยต่ออีเมลนี้\n\nKNA Interpharma";
                $headers = "From: no-reply@knainterpharma.co.th\r\n"
                    . "Content-Type: text/plain; charset=UTF-8\r\n";
                @mail($user['email'], '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน - KNA Interpharma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }
    </style>
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
                    <i class="fa-solid fa-key text-2xl" style="color:#4B4899;"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">ลืมรหัสผ่าน?</h1>
                <p class="text-gray-500">กรอกอีเมลที่ใช้สมัครสมาชิก เราจะส่งลิงก์สำหรับตั้งรหัสผ่านใหม่ให้</p>
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
                        <span>หากอีเมลนี้มีอยู่ในระบบ เราได้ส่งลิงก์รีเซ็ตรหัสผ่านไปแล้ว (ลิงก์หมดอายุใน 30 นาที)</span>
                    </div>

                    <?php if ($devResetLink !== ''): ?>
                        <div class="mb-6 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-amber-800 text-sm break-all">
                            <p class="font-semibold mb-1"><i class="fa-solid fa-flask mr-1"></i>โหมดพัฒนา — ลิงก์รีเซ็ต:</p>
                            <a href="<?php echo h($devResetLink); ?>" class="underline"><?php echo h($devResetLink); ?></a>
                        </div>
                    <?php endif; ?>

                    <a href="/login.php" class="block w-full py-3 rounded-xl text-white font-bold text-base text-center transition hover:opacity-90" style="background:#4B4899;">
                        กลับหน้าเข้าสู่ระบบ
                    </a>
                <?php else: ?>
                    <form method="post" action="/forgot-password.php" class="space-y-5">
                        <?php echo csrf_field(); ?>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">อีเมล</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fa-solid fa-envelope"></i>
                                </span>
                                <input
                                    id="email" name="email" type="email"
                                    value="<?php echo h($email); ?>"
                                    autocomplete="email" required
                                    class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none transition"
                                    onfocus="this.style.borderColor='#4B4899'; this.style.boxShadow='0 0 0 3px rgba(75,72,153,0.15)';"
                                    onblur="this.style.borderColor=''; this.style.boxShadow='';"
                                    placeholder="email@example.com">
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full py-3 rounded-xl text-white font-bold text-base transition hover:opacity-90 active:scale-95"
                            style="background:#4B4899;">
                            ส่งลิงก์รีเซ็ตรหัสผ่าน
                        </button>
                    </form>

                    <div class="mt-6 text-center text-sm text-gray-500">
                        นึกรหัสผ่านออกแล้ว?
                        <a href="/login.php" class="font-bold hover:underline" style="color:#4B4899;">เข้าสู่ระบบ</a>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

</body>
</html>
