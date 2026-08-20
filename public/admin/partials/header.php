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
    'products' => ['label' => 'จัดการสินค้า', 'href' => '/admin/products.php', 'icon' => 'fa-box-open'],
    'news' => ['label' => 'ข่าวสาร & กิจกรรม', 'href' => '/admin/news.php', 'icon' => 'fa-newspaper'],
    'messages' => ['label' => 'ข้อความติดต่อ', 'href' => '/admin/messages.php', 'icon' => 'fa-envelope'],
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --bg: #f3f4f6;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --primary: #2563eb;
            --primary-soft: #dbeafe;
            --primary-hover: #1d4ed8;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --nav: #111827;
            --nav-muted: #9ca3af;
            --border: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 12px;
            --radius-sm: 8px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Prompt', "Segoe UI", Tahoma, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; transition: color 0.2s ease; }
        .admin {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }
        .sidebar {
            background: var(--nav);
            color: #fff;
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 8px rgba(0,0,0,0.05);
            z-index: 10;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 16px;
        }
        .brand img { height: 42px; width: auto; }
        .brand span {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #fff;
        }
        .nav {
            display: grid;
            gap: 8px;
        }
        .nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            color: var(--nav-muted);
            font-weight: 500;
            font-size: 15px;
            transition: all 0.2s ease;
        }
        .nav a i {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }
        .nav a.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
            font-weight: 600;
        }
        .nav a:hover:not(.active) {
            background: rgba(255,255,255,0.05);
            color: #f3f4f6;
        }
        .main {
            display: grid;
            grid-template-rows: auto 1fr;
            min-width: 0;
        }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 14px 32px;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 20;
        }
        .topbar__left {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
        }
        .topbar__actions {
            display: flex;
            align-items: center;
            gap: 20px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 500;
        }
        .topbar__link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #4b5563;
        }
        .topbar__link:hover { color: var(--primary); }
        .user {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #374151;
            padding: 6px 12px;
            background: #f3f4f6;
            border-radius: 20px;
            font-weight: 600;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            background: #fff;
            color: #374151;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
            font-family: inherit;
        }
        .btn:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }
        .btn--primary {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }
        .btn--primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
            color: #fff;
        }
        .btn--danger {
            background: var(--danger);
            color: #fff;
            border-color: var(--danger);
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
        }
        .btn--danger:hover {
            background: var(--danger-hover);
            border-color: var(--danger-hover);
            color: #fff;
        }
        .btn--muted {
            background: #f3f4f6;
            color: #4b5563;
            border-color: #d1d5db;
            box-shadow: none;
        }
        .btn--muted:hover {
            background: #e5e7eb;
            color: #1f2937;
        }
        .content {
            padding: 32px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }
        .pageTitle {
            margin: 0 0 24px;
            font-size: 32px;
            font-weight: 700;
            color: #111827;
            letter-spacing: -0.5px;
        }
        .card {
            background: var(--card);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            transition: box-shadow 0.3s ease;
        }
        .card:hover { box-shadow: var(--shadow-lg); }
        .grid { display: grid; gap: 24px; }
        .grid--2 { grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); }
        .grid--3 { grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
        .kpi {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .kpi__label { color: var(--muted); font-size: 14px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
        .kpi__value { font-size: 42px; font-weight: 700; margin-top: 8px; color: #111827; line-height: 1; }
        .kpi__icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 28px;
        }
        .tableWrap {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow-x: auto;
            margin-top: 16px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
            white-space: nowrap;
        }
        .table th, .table td {
            text-align: left;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
            vertical-align: middle;
        }
        .table th {
            font-size: 13px;
            color: #4b5563;
            font-weight: 600;
            background: #f9fafb;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        .table tbody tr { transition: background-color 0.15s ease; }
        .table tbody tr:hover { background-color: #f9fafb; }
        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            letter-spacing: 0.3px;
        }
        .status--on { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .status--off { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .muted { color: var(--muted); }
        .field {
            display: grid;
            gap: 8px;
            margin-bottom: 16px;
        }
        .field label {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }
        .field input,
        .field select,
        .field textarea {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            background: #fff;
            font-size: 14px;
            color: #1f2937;
            font-family: inherit;
            transition: all 0.2s ease;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
        }
        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        .field textarea { min-height: 100px; resize: vertical; }
        .row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
        }
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            background: #fff;
            padding: 16px 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
        }
        .chip {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            background: var(--primary-soft);
            color: var(--primary-hover);
            font-weight: 600;
            display: inline-block;
        }
        .actions {
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }
        .iconBtn {
            border: 0;
            background: #f3f4f6;
            color: #4b5563;
            cursor: pointer;
            font-size: 16px;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-grid;
            place-items: center;
            transition: all 0.2s ease;
        }
        .iconBtn:hover { background: #e5e7eb; color: var(--primary); }
        .iconBtn--danger:hover { background: #fee2e2; color: var(--danger); }
        .notice {
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            margin-bottom: 16px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .error {
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            margin-bottom: 12px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .modal {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: none;
            background: rgba(17, 24, 39, 0.6);
            backdrop-filter: blur(4px);
            padding: 24px;
            overflow: auto;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .modal.is-open { display: block; opacity: 1; }
        .modal__dialog {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            border-radius: var(--radius);
            padding: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        }
        .modal.is-open .modal__dialog { transform: translateY(0); }
        .modal__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }
        .modal__title { font-size: 24px; font-weight: 700; margin: 0; color: #111827; }
        .closeBtn {
            border: 0;
            background: transparent;
            font-size: 24px;
            cursor: pointer;
            color: #9ca3af;
            transition: color 0.2s;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: grid;
            place-items: center;
        }
        .closeBtn:hover { color: #111827; background: #f3f4f6; }
        @media (max-width: 960px) {
            .admin { grid-template-columns: 1fr; }
            .sidebar { position: sticky; top: 0; z-index: 30; padding: 12px; }
            .nav { display: flex; overflow-x: auto; gap: 8px; padding-bottom: 8px; }
            .nav a { white-space: nowrap; padding: 8px 12px; }
            .topbar { padding: 12px 16px; }
            .content { padding: 16px; }
            .pageTitle { font-size: 24px; }
            .grid { gap: 16px; }
            .card { padding: 16px; }
            .toolbar { padding: 12px; }
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
