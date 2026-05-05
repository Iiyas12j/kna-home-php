<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function admin_login(PDO $pdo, string $email, string $password): bool
{
    ensure_admin_user_registration_columns($pdo);

    $stmt = $pdo->prepare('SELECT id, password_hash, role, is_active FROM admin_users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if (!$admin) {
        return false;
    }

    if (isset($admin['is_active']) && (int) $admin['is_active'] !== 1) {
        return false;
    }

    if (normalize_member_role($admin['role'] ?? '') !== 'admin') {
        return false;
    }

    if (!password_verify($password, $admin['password_hash'])) {
        return false;
    }

    $pdo->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?')->execute([$admin['id']]);

    $_SESSION['admin_id']    = $admin['id'];
    $_SESSION['admin_email'] = $email;
    return true;
}

function admin_login_rl_key(): string
{
    return 'admin_login:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

function admin_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function normalize_member_role(?string $role): string
{
    $role = strtolower(trim((string) $role));

    return match ($role) {
        'doctor', 'dr', 'แพทย์', 'หมอ' => 'doctor',
        'member', 'user', 'customer', 'client', 'member_user' => 'member',
        'admin' => 'admin',
        default => $role !== '' ? $role : 'member',
    };
}

function member_registration_role(?string $role): string
{
    return normalize_member_role($role) === 'doctor' ? 'doctor' : 'member';
}

function is_member_logged_in(): bool
{
    return !empty($_SESSION['member_id']);
}

function current_member(): ?array
{
    if (!is_member_logged_in()) {
        return null;
    }

    return [
        'id' => (int) ($_SESSION['member_id'] ?? 0),
        'email' => (string) ($_SESSION['member_email'] ?? ''),
        'name' => (string) ($_SESSION['member_name'] ?? ''),
        'role' => normalize_member_role($_SESSION['member_role'] ?? ''),
    ];
}

function member_role(): string
{
    return normalize_member_role($_SESSION['member_role'] ?? '');
}

function member_is_doctor(): bool
{
    return member_role() === 'doctor';
}

function member_role_label(?string $role): string
{
    return match (normalize_member_role($role)) {
        'doctor' => 'แพทย์',
        'admin' => 'ผู้ดูแลระบบ',
        default => 'สมาชิก',
    };
}

function member_can_access_video(string $accessLevel): bool
{
    $accessLevel = normalize_video_access_level($accessLevel);

    if ($accessLevel === 'public') {
        return true;
    }

    if (!is_member_logged_in()) {
        return false;
    }

    if ($accessLevel === 'member') {
        return true;
    }

    return member_is_doctor();
}

function member_login(PDO $pdo, string $email, string $password): bool
{
    ensure_admin_user_registration_columns($pdo);

    $stmt = $pdo->prepare('SELECT id, email, name, role, password_hash, is_active FROM admin_users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $member = $stmt->fetch();

    if (!$member) {
        return false;
    }

    if (isset($member['is_active']) && (int) $member['is_active'] !== 1) {
        return false;
    }

    if (!password_verify($password, $member['password_hash'])) {
        return false;
    }

    $pdo->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?')->execute([$member['id']]);

    $_SESSION['member_id']    = (int) $member['id'];
    $_SESSION['member_email'] = (string) $member['email'];
    $_SESSION['member_name']  = (string) ($member['name'] ?? '');
    $_SESSION['member_role']  = normalize_member_role($member['role'] ?? '');

    return true;
}

function member_login_rl_key(): string
{
    return 'member_login:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

function member_register(PDO $pdo, string $name, string $email, string $password, array $options = [], ?string &$error = null): bool
{
    ensure_admin_user_registration_columns($pdo);

    $name = trim($name);
    $email = trim($email);
    $requestedRole   = member_registration_role($options['requested_role'] ?? 'member');
    $doctorLicenseNo = trim((string) ($options['doctor_license_no'] ?? ''));
    $lastName        = trim((string) ($options['last_name'] ?? ''));
    $hospitalClinic  = trim((string) ($options['hospital_clinic'] ?? ''));
    $province        = trim((string) ($options['province'] ?? ''));
    $phone           = trim((string) ($options['phone'] ?? ''));
    $lineId          = trim((string) ($options['line_id'] ?? ''));

    if ($name === '' || $email === '' || $password === '') {
        $error = 'กรุณากรอกข้อมูลให้ครบ';
        return false;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'รูปแบบอีเมลไม่ถูกต้อง';
        return false;
    }

    if ($requestedRole === 'doctor' && $doctorLicenseNo === '') {
        $error = 'กรุณากรอกเลข อว. สำหรับการสมัครแพทย์';
        return false;
    }

    $stmt = $pdo->prepare('SELECT id FROM admin_users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $error = 'อีเมลนี้ถูกใช้งานแล้ว';
        return false;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO admin_users
            (email, password_hash, name, last_name, role, requested_role, doctor_license_no,
             hospital_clinic, province, phone, line_id, is_active, created_at)
        VALUES (?, ?, ?, ?, 'member', ?, ?, ?, ?, ?, ?, 1, NOW())
    ");
    $stmt->execute([
        $email,
        $hash,
        $name,
        $lastName !== '' ? $lastName : null,
        $requestedRole,
        $doctorLicenseNo !== '' ? $doctorLicenseNo : null,
        $hospitalClinic !== '' ? $hospitalClinic : null,
        $province !== '' ? $province : null,
        $phone !== '' ? $phone : null,
        $lineId !== '' ? $lineId : null,
    ]);

    $_SESSION['member_id'] = (int) $pdo->lastInsertId();
    $_SESSION['member_email'] = $email;
    $_SESSION['member_name'] = $name;
    $_SESSION['member_role'] = 'member';

    return true;
}

function member_logout(): void
{
    unset(
        $_SESSION['member_id'],
        $_SESSION['member_email'],
        $_SESSION['member_name'],
        $_SESSION['member_role']
    );
}
