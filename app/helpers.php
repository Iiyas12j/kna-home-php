<?php

require_once __DIR__ . '/config.php';

function h($value): string
{
    if ($value === null) {
        return '';
    }

    if (is_bool($value)) {
        $value = $value ? '1' : '0';
    }

    if (!is_scalar($value)) {
        return '';
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function asset_url(string $path): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function save_uploaded_image(array $file, string $destDir, string $prefix = 'img'): ?string
{
    if (!isset($file['tmp_name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed, true)) {
        throw new RuntimeException('Invalid file type');
    }

    if (!is_dir($destDir)) {
        mkdir($destDir, 0775, true);
    }

    $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $target = rtrim($destDir, '/') . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Upload failed');
    }

    return $filename;
}

function normalize_video_access_level(?string $value): string
{
    $value = strtolower(trim((string) $value));
    $allowed = ['public', 'member', 'doctor'];

    return in_array($value, $allowed, true) ? $value : 'public';
}

function video_access_label(?string $value): string
{
    return match (normalize_video_access_level($value)) {
        'member' => 'สมาชิก',
        'doctor' => 'แพทย์',
        default => 'สาธารณะ',
    };
}

function ensure_videos_access_level_column(PDO $pdo): void
{
    static $checked = false;

    if ($checked) {
        return;
    }

    $checked = true;

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'videos'
          AND COLUMN_NAME = 'access_level'
    ");

    if ((int) $stmt->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE videos ADD COLUMN access_level VARCHAR(20) NOT NULL DEFAULT 'public' AFTER platform");
    }

    $pdo->exec("UPDATE videos SET access_level = 'public' WHERE access_level IS NULL OR access_level = ''");
}

function ensure_videos_detail_columns(PDO $pdo): void
{
    static $checked = false;

    if ($checked) {
        return;
    }

    $checked = true;

    $columns = [
        'detail_summary' => "ALTER TABLE videos ADD COLUMN detail_summary TEXT NULL AFTER description",
        'detail_content' => "ALTER TABLE videos ADD COLUMN detail_content LONGTEXT NULL AFTER detail_summary",
    ];

    foreach ($columns as $columnName => $sql) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'videos'
              AND COLUMN_NAME = ?
        ");
        $stmt->execute([$columnName]);

        if ((int) $stmt->fetchColumn() === 0) {
            $pdo->exec($sql);
        }
    }
}

function ensure_admin_user_registration_columns(PDO $pdo): void
{
    static $checked = false;

    if ($checked) {
        return;
    }

    $checked = true;

    $columns = [
        'requested_role' => "ALTER TABLE admin_users ADD COLUMN requested_role VARCHAR(40) NOT NULL DEFAULT 'member' AFTER role",
        'doctor_license_no' => "ALTER TABLE admin_users ADD COLUMN doctor_license_no VARCHAR(120) NULL AFTER requested_role",
    ];

    foreach ($columns as $columnName => $sql) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'admin_users'
              AND COLUMN_NAME = ?
        ");
        $stmt->execute([$columnName]);

        if ((int) $stmt->fetchColumn() === 0) {
            $pdo->exec($sql);
        }
    }

    $pdo->exec("
        UPDATE admin_users
        SET requested_role = CASE
            WHEN LOWER(COALESCE(role, 'member')) = 'doctor' THEN 'doctor'
            ELSE 'member'
        END
        WHERE requested_role IS NULL OR requested_role = ''
    ");
}

// ── CSRF Protection ───────────────────────────────────────────────────────────

function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . h(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $token    = (string) ($_POST['_csrf_token'] ?? '');
    $expected = (string) ($_SESSION['_csrf_token'] ?? '');
    if ($expected === '' || !hash_equals($expected, $token)) {
        return false;
    }
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    return true;
}

// ── Rate Limiting (file-based, IP-aware) ─────────────────────────────────────

function _rl_file(string $key): string
{
    return sys_get_temp_dir() . '/kna_rl_' . md5($key) . '.json';
}

function rate_limit_check(string $key, int $maxAttempts = 5, int $lockSeconds = 900): bool
{
    $file = _rl_file($key);
    if (!file_exists($file)) {
        return true;
    }
    $data = json_decode((string) file_get_contents($file), true) ?: [];
    $lockedUntil = (int) ($data['locked_until'] ?? 0);
    return $lockedUntil <= time();
}

function rate_limit_hit(string $key, int $maxAttempts = 5, int $lockSeconds = 900): void
{
    $file = _rl_file($key);
    $data = file_exists($file)
        ? (json_decode((string) file_get_contents($file), true) ?: [])
        : [];

    $lockedUntil = (int) ($data['locked_until'] ?? 0);
    if ($lockedUntil > 0 && $lockedUntil <= time()) {
        $data = ['attempts' => 0, 'locked_until' => 0];
    }

    $data['attempts'] = (int) ($data['attempts'] ?? 0) + 1;
    if ($data['attempts'] >= $maxAttempts) {
        $data['locked_until'] = time() + $lockSeconds;
        $data['attempts']     = 0;
    }

    file_put_contents($file, json_encode($data), LOCK_EX);
}

function rate_limit_clear(string $key): void
{
    $file = _rl_file($key);
    if (file_exists($file)) {
        @unlink($file);
    }
}

function rate_limit_wait_seconds(string $key): int
{
    $file = _rl_file($key);
    if (!file_exists($file)) {
        return 0;
    }
    $data = json_decode((string) file_get_contents($file), true) ?: [];
    $lockedUntil = (int) ($data['locked_until'] ?? 0);
    return $lockedUntil > time() ? $lockedUntil - time() : 0;
}
