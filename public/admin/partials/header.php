<?php
if (!isset($page_title)) {
    $page_title = 'Admin';
}
$active_nav = $active_nav ?? '';
$nav_items = [
    'dashboard' => ['label' => 'Dashboard', 'href' => '/admin/dashboard.php', 'icon' => 'fa-gauge-high'],
    'website' => ['label' => 'จัดการเว็บไซต์', 'href' => '/admin/website.php', 'icon' => 'fa-image'],
    'clinics' => ['label' => 'จัดการคลินิก', 'href' => '/admin/clinics.php', 'icon' => 'fa-clinic-medical'],
    'doctors' => ['label' => 'ทำเนียบแพทย์', 'href' => '/admin/doctors.php', 'icon' => 'fa-user-doctor'],
    'news' => ['label' => 'ข่าวสาร & กิจกรรม', 'href' => '/admin/news.php', 'icon' => 'fa-newspaper'],
    'videos' => ['label' => 'จัดการวิดีโอ', 'href' => '/admin/videos.php', 'icon' => 'fa-music'],
    'users' => ['label' => 'Users', 'href' => '/admin/users.php', 'icon' => 'fa-users'],
];
$admin_name = $_SESSION['admin_email'] ?? 'admin';
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo h($page_title); ?> - KNA Backend</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --bg: #eceef2;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --primary: #2f65dc;
            --primary-soft: #e9f0ff;
            --danger: #ef4444;
            --nav: #0b1730;
            --nav-muted: #c7d2fe;
            --border: #e5e7eb;
            --shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
            --radius: 14px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
        }
        a { color: inherit; text-decoration: none; }
        .admin {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }
        .sidebar {
            background: var(--nav);
            color: #fff;
            padding: 14px 14px 18px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px 18px;
        }
        .brand img { height: 45px; width: auto; }
        .brand .backend { font-size: 38px; letter-spacing: 0.6px; }
        .brand span {
            font-size: 36px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }
        .nav {
            display: grid;
            gap: 6px;
            margin-top: 4px;
        }
        .nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 12px;
            border-radius: 10px;
            color: var(--nav-muted);
            font-weight: 600;
            transition: .18s ease;
        }
        .nav a i {
            width: 18px;
            text-align: center;
        }
        .nav a.active,
        .nav a:hover {
            background: var(--primary);
            color: #fff;
        }
        .main {
            display: grid;
            grid-template-rows: auto 1fr;
        }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 12px 24px;
        }
        .topbar__left {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }
        .topbar__actions {
            display: flex;
            align-items: center;
            gap: 14px;
            color: var(--muted);
            font-weight: 600;
        }
        .topbar__link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #374151;
        }
        .user {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #111827;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: #fff;
            color: #111827;
            font-weight: 700;
            cursor: pointer;
        }
        .btn--primary {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }
        .btn--danger {
            background: var(--danger);
            color: #fff;
            border-color: var(--danger);
        }
        .btn--muted {
            background: #9ca3af;
            color: #fff;
            border-color: #9ca3af;
        }
        .content {
            padding: 24px;
        }
        .pageTitle {
            margin: 0 0 16px;
            font-size: 46px;
            font-weight: 800;
            letter-spacing: .2px;
        }
        .card {
            background: var(--card);
            border-radius: var(--radius);
            padding: 16px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }
        .grid { display: grid; gap: 16px; }
        .grid--2 { grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); }
        .grid--3 { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
        .kpi {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .kpi__label { color: var(--muted); font-size: 13px; font-weight: 600; }
        .kpi__value { font-size: 40px; font-weight: 800; margin-top: 6px; }
        .kpi__icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 20px;
        }
        .tableWrap {
            background: #fff;
            border-radius: 14px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: auto;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }
        .table th, .table td {
            text-align: left;
            padding: 13px 12px;
            border-bottom: 1px solid #edf1f5;
            font-size: 14px;
            vertical-align: middle;
        }
        .table th {
            font-size: 13px;
            color: #4b5563;
            font-weight: 700;
            background: #fafbfe;
        }
        .status {
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
        }
        .status--on { background: #dcfce7; color: #166534; }
        .status--off { background: #fee2e2; color: #991b1b; }
        .field {
            display: grid;
            gap: 6px;
            margin-bottom: 12px;
        }
        .field label {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
        }
        .field input,
        .field select,
        .field textarea {
            width: 100%;
            border: 1px solid #d6dbe3;
            border-radius: 10px;
            padding: 11px 12px;
            background: #fff;
            font-size: 14px;
        }
        .field textarea { min-height: 84px; resize: vertical; }
        .row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }
        .chip {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            background: #dbeafe;
            color: #1d4ed8;
            font-weight: 700;
        }
        .actions {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .iconBtn {
            border: 0;
            background: transparent;
            color: #2563eb;
            cursor: pointer;
            font-size: 18px;
            padding: 4px;
        }
        .iconBtn--danger { color: #dc2626; }
        .notice {
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 12px;
            background: #fef3c7;
            border: 1px solid #fde68a;
            color: #92400e;
        }
        .error {
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 8px;
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .modal {
            position: fixed;
            inset: 0;
            z-index: 40;
            display: none;
            background: rgba(0, 0, 0, 0.48);
            padding: 20px;
            overflow: auto;
        }
        .modal.is-open { display: block; }
        .modal__dialog {
            max-width: 900px;
            margin: 24px auto;
            background: #fff;
            border-radius: 14px;
            padding: 18px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
        }
        .modal__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .modal__title { font-size: 28px; font-weight: 800; margin: 0; }
        .closeBtn {
            border: 0;
            background: transparent;
            font-size: 22px;
            cursor: pointer;
            color: #6b7280;
        }
        @media (max-width: 960px) {
            .admin { grid-template-columns: 1fr; }
            .sidebar { position: sticky; top: 0; z-index: 20; }
            .topbar { padding: 12px 14px; }
            .content { padding: 14px; }
            .pageTitle { font-size: 28px; }
        }
    </style>
</head>
<body>
    <div class="admin">
        <aside class="sidebar">
            <div class="brand">
                <img src="/uploads/logo-kna.png" alt="KNA" />
                <span>Backend</span>
            </div>
            <nav class="nav">
                <?php foreach ($nav_items as $key => $nav_item): ?>
                    <a class="<?php echo $active_nav === $key ? 'active' : ''; ?>" href="<?php echo h($nav_item['href']); ?>">
                        <i class="fa-solid <?php echo h($nav_item['icon']); ?>"></i>
                        <?php echo h($nav_item['label']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>

        <main class="main">
            <div class="topbar">
                <div class="topbar__left"><?php echo h($page_title); ?></div>
                <div class="topbar__actions">
                    <a class="topbar__link" href="/" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square"></i>ดูหน้าเว็บ</a>
                    <span class="user"><i class="fa-solid fa-user-shield"></i><?php echo h($admin_name); ?></span>
                    <a class="btn btn--danger" href="/admin/logout.php"><i class="fa-solid fa-right-from-bracket"></i>ออกจากระบบ</a>
                </div>
            </div>
            <div class="content">
