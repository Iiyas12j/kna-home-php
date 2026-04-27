<?php
require_once __DIR__ . '/../app/auth.php';

function frontend_logout_redirect(?string $value): string
{
    $value = trim((string) $value);

    if ($value === '' || $value[0] !== '/' || str_starts_with($value, '//')) {
        return '/video-tiktok.php';
    }

    return $value;
}

$redirect = frontend_logout_redirect($_GET['redirect'] ?? '/video-tiktok.php');
member_logout();
header('Location: ' . $redirect);
exit;
