<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/helpers.php';

if (is_admin_logged_in()) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';
$db_ready = $pdo instanceof PDO;
$email = '';

$rl_key = admin_login_rl_key();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!rate_limit_check($rl_key)) {
        $wait  = (int) ceil(rate_limit_wait_seconds($rl_key) / 60);
        $error = "พยายาม login มากเกินไป กรุณารอ {$wait} นาทีแล้วลองใหม่";
    } elseif (!csrf_verify()) {
        $error = 'คำขอไม่ถูกต้อง กรุณาลองใหม่';
    } elseif (!$db_ready) {
        $error = 'Database not ready.';
    } elseif ($email === '' || $password === '') {
        $error = 'Please enter email and password';
    } elseif (admin_login($pdo, $email, $password)) {
        rate_limit_clear($rl_key);
        header('Location: /admin/dashboard.php');
        exit;
    } else {
        rate_limit_hit($rl_key);
        $error = 'Invalid email or password';
    }
}
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - KNA</title>
    <style>
        body { font-family: "Segoe UI", Tahoma, Arial, sans-serif; background:#f5f6fb; margin:0; }
        .wrap { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }
        .card { width:380px; background:#fff; border-radius:14px; box-shadow:0 14px 26px rgba(15, 23, 42, 0.12); padding:24px; }
        .title { font-size:20px; font-weight:700; margin-bottom:8px; }
        .muted { color:#6b7280; margin-bottom:16px; }
        .field { margin-bottom:12px; }
        label { display:block; font-size:14px; margin-bottom:6px; }
        input { width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:10px; box-sizing:border-box; }
        button { width:100%; padding:10px 12px; border:0; border-radius:10px; background:#2f6fed; color:#fff; font-weight:600; }
        .error { background:#fee2e2; color:#991b1b; padding:8px 10px; border-radius:10px; margin-bottom:12px; font-size:13px; }
        .hint { margin-top:12px; font-size:12px; color:#6b7280; }
    </style>
</head>
<body>
    <div class="wrap">
        <form class="card" method="post" action="/admin/login.php">
            <?php echo csrf_field(); ?>
            <div class="title">Admin Login</div>
            <div class="muted">Sign in to manage the website.</div>
            <?php if ($error !== ''): ?>
                <div class="error"><?php echo h($error); ?></div>
            <?php endif; ?>
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="<?php echo h($email); ?>" required>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
            </div>
            <button type="submit">Login</button>
            <div class="hint">Admin default: <strong>admin@example.com</strong></div>
        </form>
    </div>
</body>
</html>
