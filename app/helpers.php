<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/s3.php';

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

function upload_url(string $value, string $folder = ''): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
        return $value;
    }
    $folder = trim($folder, '/');
    return $folder !== ''
        ? '/uploads/' . $folder . '/' . rawurlencode($value)
        : '/uploads/' . rawurlencode($value);
}

function save_uploaded_image(array $file, string $destDir, string $prefix = 'img', int $maxBytes = 5242880): ?string
{
    if (!isset($file['tmp_name'], $file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        throw new RuntimeException('ไฟล์ใหญ่เกินขีดจำกัดของเซิร์ฟเวอร์ กรุณาใช้ไฟล์ขนาดเล็กลง');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('อัปโหลดไฟล์ไม่สำเร็จ (error code ' . (int) $file['error'] . ') กรุณาลองใหม่');
    }

    $tmpFile = (string) $file['tmp_name'];
    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > $maxBytes || !is_uploaded_file($tmpFile)) {
        throw new RuntimeException('ไฟล์รูปต้องมีขนาดไม่เกิน 5 MB');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($tmpFile);

    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        $head = (string) file_get_contents($tmpFile, false, null, 0, 4096);
        if (stripos($head, '<svg') !== false
            && in_array($mime, ['image/svg+xml', 'image/svg', 'text/xml', 'application/xml', 'text/plain', 'text/html'], true)) {
            $mime = 'image/svg+xml';
        }
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('รองรับเฉพาะไฟล์รูป JPG, PNG, WebP และ SVG');
    }

    if ($mime === 'image/svg+xml') {
        if (!svg_is_safe((string) file_get_contents($tmpFile))) {
            throw new RuntimeException('ไฟล์ SVG มีเนื้อหาที่ไม่ปลอดภัย ไม่อนุญาตให้อัปโหลด');
        }
    } elseif (@getimagesize($tmpFile) === false) {
        throw new RuntimeException('ไฟล์รูปไม่ถูกต้องหรือเสียหาย');
    }

    $ext = $allowed[$mime];
    $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

    if (s3_configured()) {
        $subfolder = basename(rtrim($destDir, '/'));
        return s3_upload($tmpFile, 'uploads/' . $subfolder . '/' . $filename, $mime);
    }

    if (!is_dir($destDir)) {
        mkdir($destDir, 0775, true);
    }

    $target = rtrim($destDir, '/') . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Upload failed');
    }

    return $filename;
}

function normalize_uploaded_files(array $files): array
{
    if (!isset($files['name'])) {
        return [];
    }
    if (!is_array($files['name'])) {
        return [$files];
    }

    $out = [];
    foreach ($files['name'] as $i => $name) {
        $out[] = [
            'name' => $name,
            'type' => $files['type'][$i] ?? '',
            'tmp_name' => $files['tmp_name'][$i] ?? '',
            'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$i] ?? 0,
        ];
    }
    return $out;
}

function svg_is_safe(string $svg): bool
{
    // Reject DOCTYPE/entities outright (XXE / entity-encoding tricks).
    if (preg_match('/<!DOCTYPE|<!ENTITY/i', $svg)) {
        return false;
    }

    $prev = libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $loaded = $doc->loadXML($svg, LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);
    if (!$loaded) {
        return false;
    }

    $blockedTags = ['script', 'foreignobject', 'iframe', 'embed', 'object', 'use', 'animate', 'set', 'handler'];
    $xpath = new DOMXPath($doc);
    foreach ($xpath->query('//*') as $node) {
        if (in_array(strtolower($node->localName ?? ''), $blockedTags, true)) {
            return false;
        }
        if (!$node->hasAttributes()) {
            continue;
        }
        foreach ($node->attributes as $attr) {
            $name = strtolower($attr->localName ?? $attr->nodeName);
            $value = strtolower(trim((string) $attr->nodeValue));
            if (str_starts_with($name, 'on')) {
                return false;
            }
            if (in_array($name, ['href', 'xlink:href', 'src'], true)
                && $value !== '' && !str_starts_with($value, '#')
                && !str_starts_with($value, 'data:image/')
                && !preg_match('#^https?://#', $value)) {
                return false;
            }
            if (str_contains($value, 'javascript:') || str_contains($value, 'data:text')) {
                return false;
            }
        }
    }

    return true;
}

function delete_uploaded_file(?string $value, string $destDir, string $folder): void
{
    $value = trim((string) $value);
    if ($value === '') {
        return;
    }

    if (preg_match('#^https?://#i', $value)) {
        $path = parse_url($value, PHP_URL_PATH);
        $key = ltrim(rawurldecode((string) $path), '/');
        if (s3_configured() && str_starts_with($key, 'uploads/')) {
            s3_delete($key);
        }
        return;
    }

    $filename = basename($value);
    $localPath = rtrim($destDir, '/') . '/' . $filename;
    if (is_file($localPath)) {
        unlink($localPath);
        return;
    }

    if (s3_configured()) {
        s3_delete('uploads/' . trim($folder, '/') . '/' . $filename);
    }
}

function require_valid_csrf(): void
{
    // When the request body exceeds post_max_size, PHP drops $_POST/$_FILES
    // entirely — surface that as a clear message instead of a CSRF failure.
    $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
    if ($contentLength > 0 && empty($_POST) && empty($_FILES)) {
        http_response_code(413);
        throw new RuntimeException('ไฟล์ที่อัปโหลดใหญ่เกินไป เซิร์ฟเวอร์ไม่รับข้อมูล กรุณาใช้ไฟล์ขนาดเล็กลง');
    }

    if (!csrf_verify()) {
        http_response_code(403);
        throw new RuntimeException('คำขอไม่ถูกต้อง กรุณาลองใหม่');
    }
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
    // In local development a long lockout only gets in the way of testing.
    if (defined('APP_ENV') && APP_ENV === 'development') {
        $lockSeconds = min($lockSeconds, 60);
    }

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

function ensure_videos_access_level_column(PDO $pdo): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $stmt = $pdo->query("SHOW COLUMNS FROM videos LIKE 'access_level'");
    if ($stmt->fetch() === false) {
        $pdo->exec("ALTER TABLE videos ADD COLUMN access_level VARCHAR(20) NOT NULL DEFAULT 'public'");
    }
}

function ensure_videos_detail_columns(PDO $pdo): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $columns = [
        'detail_summary' => 'TEXT NULL',
        'detail_content' => 'LONGTEXT NULL',
    ];
    foreach ($columns as $name => $definition) {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM videos LIKE ?");
        $stmt->execute([$name]);
        if ($stmt->fetch() === false) {
            $pdo->exec("ALTER TABLE videos ADD COLUMN $name $definition");
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
        'requested_role'    => "VARCHAR(20) NULL",
        'doctor_license_no' => "VARCHAR(50) NULL",
        'last_name'         => "VARCHAR(190) NULL",
        'hospital_clinic'   => "VARCHAR(190) NULL",
        'province'          => "VARCHAR(120) NULL",
        'phone'             => "VARCHAR(50) NULL",
        'line_id'           => "VARCHAR(120) NULL",
    ];
    foreach ($columns as $name => $definition) {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM admin_users LIKE ?");
        $stmt->execute([$name]);
        if ($stmt->fetch() === false) {
            $pdo->exec("ALTER TABLE admin_users ADD COLUMN $name $definition");
        }
    }
}
