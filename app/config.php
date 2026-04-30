<?php

// Load .env file if present
$_envPath = __DIR__ . '/../.env';
if (file_exists($_envPath)) {
    foreach (file($_envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_envLine) {
        if (str_starts_with(trim($_envLine), '#') || !str_contains($_envLine, '=')) {
            continue;
        }
        [$_envKey, $_envVal] = explode('=', $_envLine, 2);
        $_envKey = trim($_envKey);
        $_envVal = trim($_envVal);
        if ($_envKey !== '' && !array_key_exists($_envKey, $_ENV)) {
            $_ENV[$_envKey] = $_envVal;
            putenv("$_envKey=$_envVal");
        }
    }
    unset($_envPath, $_envLine, $_envKey, $_envVal);
} else {
    unset($_envPath);
}

define('APP_NAME', $_ENV['APP_NAME'] ?? 'KNA Interpharma');
define('BASE_URL',  $_ENV['BASE_URL']  ?? 'http://localhost:8000');
define('APP_ENV',   $_ENV['APP_ENV']   ?? 'production');

define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'kna_site');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// Error reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// Secure session settings (must be before session_start)
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');
if (APP_ENV !== 'development') {
    ini_set('session.cookie_secure', '1');
}

// HTTPS redirect (production only)
if (APP_ENV !== 'development' && !headers_sent()) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['SERVER_PORT'] ?? 80) == 443
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

    if (!$isHttps) {
        header('Location: https://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
        exit;
    }
}

// Security headers
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');

    if (APP_ENV !== 'development') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    header("Content-Security-Policy: "
        . "default-src 'self'; "
        . "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://www.tiktok.com https://www.youtube.com; "
        . "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://fonts.googleapis.com; "
        . "font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; "
        . "img-src 'self' data: https:; "
        . "frame-src https://www.tiktok.com https://www.youtube.com https://www.google.com; "
        . "connect-src 'self';"
    );
}
