<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

function frontend_redirect_target(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '' || $value[0] !== '/' || str_starts_with($value, '//')) {
        return '/video-tiktok.php';
    }
    return $value;
}

$db_ready = $pdo instanceof PDO;
$error    = '';
$email    = '';
$redirect = frontend_redirect_target($_REQUEST['redirect'] ?? '/video-tiktok.php');

if (is_member_logged_in()) {
    header('Location: ' . $redirect);
    exit;
}

$rl_key = member_login_rl_key();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!rate_limit_check($rl_key)) {
        $wait  = (int) ceil(rate_limit_wait_seconds($rl_key) / 60);
        $error = "พยายาม login มากเกินไป กรุณารอ {$wait} นาทีแล้วลองใหม่";
    } elseif (!csrf_verify()) {
        $error = 'คำขอไม่ถูกต้อง กรุณาลองใหม่';
    } elseif (!$db_ready) {
        $error = 'ระบบฐานข้อมูลยังไม่พร้อมใช้งาน';
    } elseif ($email === '' || $password === '') {
        $error = 'กรุณากรอกอีเมลและรหัสผ่าน';
    } elseif (member_login($pdo, $email, $password)) {
        rate_limit_clear($rl_key);
        header('Location: ' . $redirect);
        exit;
    } else {
        rate_limit_hit($rl_key);
        $error = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - KNA Interpharma</title>
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
        <a href="/index.php" class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition">
            <i class="fa-solid fa-arrow-left"></i>
            กลับหน้าแรก
        </a>
    </div>

    <!-- Main Content -->
    <div class="flex items-center justify-center py-16 px-4">
        <div class="w-full max-w-md">

            <!-- Welcome Text -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">ยินดีต้อนรับกลับมา!</h1>
                <p class="text-gray-500">เข้าสู่ระบบเพื่อเข้าถึงเนื้อหาพิเศษของ KNA Interpharma</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">

                <?php if ($error !== ''): ?>
                    <div class="mb-6 flex items-start gap-3 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm">
                        <i class="fa-solid fa-circle-exclamation mt-0.5 shrink-0"></i>
                        <?php echo h($error); ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="/login.php" class="space-y-5">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="redirect" value="<?php echo h($redirect); ?>">

                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            ชื่อผู้ใช้ หรือ อีเมล
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <input
                                id="email" name="email" type="email"
                                value="<?php echo h($email); ?>"
                                autocomplete="email" required
                                class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:border-transparent transition"
                                style="--tw-ring-color: #4B4899;"
                                onfocus="this.style.borderColor='#4B4899'; this.style.boxShadow='0 0 0 3px rgba(75,72,153,0.15)';"
                                onblur="this.style.borderColor=''; this.style.boxShadow='';"
                                placeholder="email@example.com">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-sm font-semibold text-gray-700">
                                รหัสผ่าน
                            </label>
                            <a href="/forgot-password.php" class="text-xs font-medium hover:underline" style="color:#4B4899;">
                                ลืมรหัสผ่าน?
                            </a>
                        </div>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input
                                id="password" name="password" type="password"
                                autocomplete="current-password" required
                                class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none transition"
                                onfocus="this.style.borderColor='#4B4899'; this.style.boxShadow='0 0 0 3px rgba(75,72,153,0.15)';"
                                onblur="this.style.borderColor=''; this.style.boxShadow='';"
                                placeholder="รหัสผ่านของคุณ">
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="remember" name="remember"
                            class="w-4 h-4 rounded border-gray-300 cursor-pointer"
                            style="accent-color:#4B4899;">
                        <label for="remember" class="text-sm text-gray-600 cursor-pointer select-none">
                            จดจำฉันไว้
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full py-3 rounded-xl text-white font-bold text-base transition hover:opacity-90 active:scale-95"
                        style="background:#4B4899;">
                        เข้าสู่ระบบ
                    </button>
                </form>

                <div class="mt-6 text-center text-sm text-gray-500">
                    ยังไม่มีบัญชี?
                    <a href="/register.php?redirect=<?php echo urlencode($redirect); ?>"
                       class="font-bold hover:underline" style="color:#4B4899;">
                        สมัครสมาชิก
                    </a>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
