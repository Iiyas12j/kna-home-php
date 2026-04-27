<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

$page_title = 'Contact Us - KNA Interpharma';
$errors = [];
$success_message = '';
$db_ready = $pdo instanceof PDO;

$form = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'subject' => '',
    'message' => '',
];

$company = [
    'name' => 'K.N.A. Inter Pharma Co., Ltd.',
    'address' => "175-175/1 ถ.สวรรค์วิถี ต.ปากน้ำโพ\nอ.เมืองนครสวรรค์ จ.นครสวรรค์ 60000",
    'phone' => '056-200890',
    'email' => 'info@knainterpharma.co.th',
    'website' => 'https://knainterpharma.co.th',
    'hours' => 'วันจันทร์ - ศุกร์ 09:00 - 18:00 น.',
];

$featured_clinics = [];
if ($db_ready) {
    try {
        $featured_clinics = $pdo->query(
            'SELECT name, province, district, phone, map_url
             FROM clinics
             WHERE is_active = 1
             ORDER BY id DESC
             LIMIT 3'
        )->fetchAll();
    } catch (Exception $e) {
        $featured_clinics = [];
    }
}

if ($db_ready) {
    try {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(190) NOT NULL,
                email VARCHAR(190) NOT NULL,
                phone VARCHAR(50) NULL,
                subject VARCHAR(220) NOT NULL,
                message TEXT NOT NULL,
                status VARCHAR(40) NOT NULL DEFAULT "new",
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    } catch (Exception $e) {
        $errors[] = 'ไม่สามารถเตรียมระบบรับข้อความติดต่อได้';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($form as $field => $value) {
        $form[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    if ($form['name'] === '') {
        $errors[] = 'กรุณากรอกชื่อผู้ติดต่อ';
    }
    if ($form['email'] === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'กรุณากรอกอีเมลให้ถูกต้อง';
    }
    if ($form['subject'] === '') {
        $errors[] = 'กรุณากรอกหัวข้อ';
    }
    if ($form['message'] === '') {
        $errors[] = 'กรุณากรอกรายละเอียดข้อความ';
    }

    if (!$db_ready) {
        $errors[] = 'ระบบฐานข้อมูลยังไม่พร้อมสำหรับรับข้อความ';
    }

    if (empty($errors) && $db_ready) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at)
                 VALUES (?, ?, ?, ?, ?, "new", NOW())'
            );
            $stmt->execute([
                $form['name'],
                $form['email'],
                $form['phone'] !== '' ? $form['phone'] : null,
                $form['subject'],
                $form['message'],
            ]);

            $success_message = 'ส่งข้อความเรียบร้อยแล้ว ทีมงานจะติดต่อกลับโดยเร็วที่สุด';
            foreach ($form as $field => $value) {
                $form[$field] = '';
            }
        } catch (Exception $e) {
            $errors[] = 'ไม่สามารถบันทึกข้อความได้ กรุณาลองใหม่อีกครั้ง';
        }
    }
}

$siteHeaderActive = 'contact';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($page_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(79, 70, 229, 0.10), transparent 30%),
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.12), transparent 24%),
                linear-gradient(180deg, #f8fbff 0%, #eef4ff 34%, #ffffff 100%);
        }
    </style>
</head>
<body class="text-slate-800">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <section class="relative overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-[520px] bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.18),_transparent_32%),linear-gradient(135deg,#0f172a_0%,#312e81_52%,#7c3aed_100%)]"></div>
        <div class="absolute right-[-120px] top-16 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute left-[-80px] bottom-0 h-60 w-60 rounded-full bg-cyan-300/20 blur-3xl"></div>

        <div class="relative mx-auto max-w-7xl px-4 pb-16 pt-14 sm:px-6 lg:px-8 lg:pb-24">
            <div class="grid gap-8 lg:grid-cols-[1.08fr_0.92fr] lg:items-center">
                <div class="text-white">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-medium text-white/90 backdrop-blur">
                        <i class="fa-solid fa-envelope-open-text"></i>
                        Premium Contact Experience
                    </div>
                    <h1 class="mt-6 max-w-3xl text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl">
                        ติดต่อทีม KNA Interpharma
                        อย่างมั่นใจและตรงประเด็น
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-200">
                        สำหรับข้อมูลผลิตภัณฑ์ ความร่วมมือกับคลินิก การสอบถามบริการ และการติดต่อทีมงานโดยตรง
                        หน้านี้ถูกออกแบบให้ชัดเจน ใช้งานง่าย และสะท้อนภาพลักษณ์แบรนด์ในโทน Premium Clinical
                    </p>

                    <div class="mt-8 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-3xl border border-white/12 bg-white/10 p-5 backdrop-blur">
                            <div class="text-sm uppercase tracking-[0.2em] text-slate-300">Response</div>
                            <div class="mt-3 text-2xl font-bold">รวดเร็ว</div>
                            <div class="mt-2 text-sm leading-6 text-slate-200">ข้อความถูกส่งเข้าระบบโดยตรงเพื่อให้ทีมงานตรวจสอบได้ทันที</div>
                        </div>
                        <div class="rounded-3xl border border-white/12 bg-white/10 p-5 backdrop-blur">
                            <div class="text-sm uppercase tracking-[0.2em] text-slate-300">Channels</div>
                            <div class="mt-3 text-2xl font-bold">ครบถ้วน</div>
                            <div class="mt-2 text-sm leading-6 text-slate-200">โทร อีเมล และแผนที่บริษัทพร้อมใช้งานจากหน้าเดียว</div>
                        </div>
                        <div class="rounded-3xl border border-white/12 bg-white/10 p-5 backdrop-blur">
                            <div class="text-sm uppercase tracking-[0.2em] text-slate-300">Trust</div>
                            <div class="mt-3 text-2xl font-bold">น่าเชื่อถือ</div>
                            <div class="mt-2 text-sm leading-6 text-slate-200">จัดวางข้อมูลสำคัญแบบมืออาชีพ เหมาะกับแบรนด์ทางการแพทย์และความงาม</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-[32px] border border-white/30 bg-white/92 p-6 shadow-[0_30px_80px_rgba(15,23,42,0.18)] backdrop-blur sm:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold uppercase tracking-[0.22em] text-indigo-600">Direct Line</div>
                            <h2 class="mt-3 text-3xl font-extrabold text-slate-950">ข้อมูลติดต่อหลัก</h2>
                            <p class="mt-3 text-base leading-7 text-slate-600">
                                หากคุณต้องการพูดคุยกับทีมงานโดยตรง สามารถเริ่มจากช่องทางที่สะดวกที่สุดด้านล่างได้ทันที
                            </p>
                        </div>
                        <div class="hidden h-16 w-16 items-center justify-center rounded-3xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/25 sm:flex">
                            <i class="fa-solid fa-headset text-xl"></i>
                        </div>
                    </div>

                    <div class="mt-8 space-y-4">
                        <a href="tel:<?php echo h($company['phone']); ?>" class="group flex items-start gap-4 rounded-3xl border border-slate-200 bg-slate-50/80 px-5 py-4 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-white hover:shadow-lg">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                                <i class="fa-solid fa-phone-volume"></i>
                            </div>
                            <div>
                                <div class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Phone</div>
                                <div class="mt-1 text-2xl font-bold text-slate-950"><?php echo h($company['phone']); ?></div>
                                <div class="mt-1 text-sm text-slate-500">โทรหาเราในเวลาทำการเพื่อรับคำแนะนำเบื้องต้น</div>
                            </div>
                        </a>

                        <a href="mailto:<?php echo h($company['email']); ?>" class="group flex items-start gap-4 rounded-3xl border border-slate-200 bg-slate-50/80 px-5 py-4 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-white hover:shadow-lg">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <div class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Email</div>
                                <div class="mt-1 text-xl font-bold text-slate-950 break-all"><?php echo h($company['email']); ?></div>
                                <div class="mt-1 text-sm text-slate-500">เหมาะสำหรับการส่งรายละเอียดและการติดต่อเชิงธุรกิจ</div>
                            </div>
                        </a>

                        <div class="flex items-start gap-4 rounded-3xl border border-slate-200 bg-slate-50/80 px-5 py-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <div class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Office</div>
                                <div class="mt-1 whitespace-pre-line text-base font-medium leading-7 text-slate-800"><?php echo h($company['address']); ?></div>
                                <a href="<?php echo h($company['website']); ?>" target="_blank" rel="noreferrer" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-indigo-700 hover:text-indigo-900">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>เว็บไซต์บริษัท
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="relative z-10 -mt-8 pb-8">
        <div class="mx-auto grid max-w-7xl gap-4 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="rounded-[28px] border border-white/70 bg-white/95 px-6 py-5 shadow-[0_20px_50px_rgba(15,23,42,0.08)] backdrop-blur">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <div class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Working Hours</div>
                        <div class="mt-1 text-lg font-bold text-slate-950"><?php echo h($company['hours']); ?></div>
                    </div>
                </div>
            </div>
            <div class="rounded-[28px] border border-white/70 bg-white/95 px-6 py-5 shadow-[0_20px_50px_rgba(15,23,42,0.08)] backdrop-blur">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-700">
                        <i class="fa-solid fa-user-group"></i>
                    </div>
                    <div>
                        <div class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Support Flow</div>
                        <div class="mt-1 text-lg font-bold text-slate-950">ฝ่ายขาย ฝ่ายประสานงาน และข้อมูลคลินิก</div>
                    </div>
                </div>
            </div>
            <div class="rounded-[28px] border border-white/70 bg-white/95 px-6 py-5 shadow-[0_20px_50px_rgba(15,23,42,0.08)] backdrop-blur">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-100 text-violet-700">
                        <i class="fa-solid fa-shield-heart"></i>
                    </div>
                    <div>
                        <div class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">Brand Tone</div>
                        <div class="mt-1 text-lg font-bold text-slate-950">Professional, Premium, Clinical</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="pb-20 pt-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-[1.05fr_0.95fr]">
                <div class="rounded-[36px] border border-slate-200 bg-white/95 p-7 shadow-[0_24px_70px_rgba(15,23,42,0.08)] sm:p-10">
                    <div class="flex flex-wrap items-start justify-between gap-5">
                        <div>
                            <div class="text-sm font-semibold uppercase tracking-[0.24em] text-indigo-600">Message Form</div>
                            <h2 class="mt-3 text-3xl font-extrabold text-slate-950 sm:text-4xl">ส่งข้อความถึงเรา</h2>
                            <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">
                                กรอกข้อมูลให้ครบเพื่อให้ทีมงานของเราเข้าใจความต้องการของคุณได้ชัดเจนขึ้น
                                และสามารถติดต่อกลับได้อย่างรวดเร็วและตรงจุด
                            </p>
                        </div>
                        <div class="hidden h-16 w-16 items-center justify-center rounded-3xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-xl shadow-indigo-500/20 md:flex">
                            <i class="fa-solid fa-paper-plane text-xl"></i>
                        </div>
                    </div>

                    <?php if ($success_message !== ''): ?>
                        <div class="mt-8 rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-700">
                            <?php echo h($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="mt-8 rounded-3xl border border-rose-200 bg-rose-50 px-5 py-4 text-rose-700">
                            <ul class="list-disc space-y-1 pl-5">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo h($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="mt-8 space-y-6">
                        <div class="grid gap-5 md:grid-cols-2">
                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-slate-700">ชื่อผู้ติดต่อ</span>
                                <input type="text" name="name" value="<?php echo h($form['name']); ?>" class="w-full rounded-2xl border border-slate-300 bg-slate-50/60 px-4 py-3.5 text-slate-900 outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100" required>
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-slate-700">อีเมล</span>
                                <input type="email" name="email" value="<?php echo h($form['email']); ?>" class="w-full rounded-2xl border border-slate-300 bg-slate-50/60 px-4 py-3.5 text-slate-900 outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100" required>
                            </label>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-slate-700">เบอร์โทรศัพท์</span>
                                <input type="text" name="phone" value="<?php echo h($form['phone']); ?>" class="w-full rounded-2xl border border-slate-300 bg-slate-50/60 px-4 py-3.5 text-slate-900 outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100" placeholder="08X-XXX-XXXX">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-slate-700">หัวข้อ</span>
                                <input type="text" name="subject" value="<?php echo h($form['subject']); ?>" class="w-full rounded-2xl border border-slate-300 bg-slate-50/60 px-4 py-3.5 text-slate-900 outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100" required>
                            </label>
                        </div>

                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold text-slate-700">รายละเอียดข้อความ</span>
                            <textarea name="message" rows="8" class="w-full rounded-[24px] border border-slate-300 bg-slate-50/60 px-4 py-4 text-slate-900 outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100" required><?php echo h($form['message']); ?></textarea>
                        </label>

                        <div class="flex flex-wrap items-center gap-4">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 px-7 py-3.5 text-base font-semibold text-white shadow-lg shadow-indigo-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                                <i class="fa-solid fa-paper-plane"></i>
                                ส่งข้อความ
                            </button>
                            <a href="tel:<?php echo h($company['phone']); ?>" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-7 py-3.5 text-base font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                                <i class="fa-solid fa-phone"></i>
                                โทรหาเรา
                            </a>
                        </div>
                    </form>
                </div>

                <div class="space-y-8">
                    <div class="rounded-[36px] border border-slate-200 bg-white/95 p-7 shadow-[0_24px_70px_rgba(15,23,42,0.08)] sm:p-8">
                        <div class="text-sm font-semibold uppercase tracking-[0.24em] text-indigo-600">Contact Details</div>
                        <h2 class="mt-3 text-3xl font-extrabold text-slate-950">ข้อมูลติดต่อ</h2>
                        <div class="mt-8 space-y-5">
                            <div class="flex gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-950">ที่อยู่บริษัท</div>
                                    <div class="mt-1 whitespace-pre-line text-slate-600"><?php echo h($company['address']); ?></div>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                                    <i class="fa-solid fa-phone-volume"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-950">โทรศัพท์</div>
                                    <a href="tel:<?php echo h($company['phone']); ?>" class="mt-1 inline-block text-slate-600 transition hover:text-indigo-700"><?php echo h($company['phone']); ?></a>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                                    <i class="fa-solid fa-envelope"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-950">อีเมล</div>
                                    <a href="mailto:<?php echo h($company['email']); ?>" class="mt-1 inline-block break-all text-slate-600 transition hover:text-indigo-700"><?php echo h($company['email']); ?></a>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                                    <i class="fa-solid fa-clock"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-950">เวลาทำการ</div>
                                    <div class="mt-1 text-slate-600"><?php echo h($company['hours']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[36px] border border-slate-200 bg-white/95 p-7 shadow-[0_24px_70px_rgba(15,23,42,0.08)] sm:p-8">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-sm font-semibold uppercase tracking-[0.24em] text-indigo-600">Clinic Network</div>
                                <h2 class="mt-3 text-3xl font-extrabold text-slate-950">คลินิกพาร์ตเนอร์ล่าสุด</h2>
                            </div>
                            <div class="hidden h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-700 sm:flex">
                                <i class="fa-solid fa-hospital-user"></i>
                            </div>
                        </div>

                        <?php if (empty($featured_clinics)): ?>
                            <div class="mt-6 rounded-3xl border border-dashed border-slate-300 px-5 py-6 text-slate-500">
                                ยังไม่มีข้อมูลคลินิกแสดงในขณะนี้
                            </div>
                        <?php else: ?>
                            <div class="mt-6 space-y-4">
                                <?php foreach ($featured_clinics as $clinic): ?>
                                    <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 transition hover:border-indigo-200 hover:bg-white">
                                        <div class="text-lg font-bold text-slate-950"><?php echo h($clinic['name'] ?? ''); ?></div>
                                        <div class="mt-1 text-sm text-slate-500">
                                            <?php echo h(trim(($clinic['district'] ?? '') . ' ' . ($clinic['province'] ?? ''))); ?>
                                        </div>
                                        <div class="mt-4 flex flex-wrap gap-4 text-sm">
                                            <?php if (!empty($clinic['phone'])): ?>
                                                <a href="tel:<?php echo h($clinic['phone']); ?>" class="inline-flex items-center gap-2 font-medium text-indigo-700 hover:text-indigo-900">
                                                    <i class="fa-solid fa-phone"></i><?php echo h($clinic['phone']); ?>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($clinic['map_url'])): ?>
                                                <a href="<?php echo h($clinic['map_url']); ?>" target="_blank" rel="noreferrer" class="inline-flex items-center gap-2 font-medium text-slate-600 hover:text-indigo-700">
                                                    <i class="fa-solid fa-map-location-dot"></i>เปิดแผนที่
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mt-8 overflow-hidden rounded-[36px] border border-slate-200 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.08)]">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 px-6 py-5 sm:px-8">
                    <div>
                        <div class="text-sm font-semibold uppercase tracking-[0.24em] text-indigo-600">Office Map</div>
                        <h2 class="mt-2 text-2xl font-extrabold text-slate-950">แผนที่บริษัท</h2>
                    </div>
                    <a href="https://www.google.com/maps?q=175-175/1%20%E0%B8%96.%E0%B8%AA%E0%B8%A7%E0%B8%A3%E0%B8%A3%E0%B8%84%E0%B9%8C%E0%B8%A7%E0%B8%B4%E0%B8%96%E0%B8%B5%20%E0%B8%95.%E0%B8%9B%E0%B8%B2%E0%B8%81%E0%B8%99%E0%B9%89%E0%B8%B3%E0%B9%82%E0%B8%9E%20%E0%B8%AD.%E0%B9%80%E0%B8%A1%E0%B8%B7%E0%B8%AD%E0%B8%87%E0%B8%99%E0%B8%84%E0%B8%A3%E0%B8%AA%E0%B8%A7%E0%B8%A3%E0%B8%A3%E0%B8%84%E0%B9%8C" target="_blank" rel="noreferrer" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>เปิดบน Google Maps
                    </a>
                </div>
                <iframe
                    title="KNA Interpharma Map"
                    src="https://www.google.com/maps?q=175-175/1%20%E0%B8%96.%E0%B8%AA%E0%B8%A7%E0%B8%A3%E0%B8%A3%E0%B8%84%E0%B9%8C%E0%B8%A7%E0%B8%B4%E0%B8%96%E0%B8%B5%20%E0%B8%95.%E0%B8%9B%E0%B8%B2%E0%B8%81%E0%B8%99%E0%B9%89%E0%B8%B3%E0%B9%82%E0%B8%9E%20%E0%B8%AD.%E0%B9%80%E0%B8%A1%E0%B8%B7%E0%B8%AD%E0%B8%87%E0%B8%99%E0%B8%84%E0%B8%A3%E0%B8%AA%E0%B8%A7%E0%B8%A3%E0%B8%A3%E0%B8%84%E0%B9%8C&output=embed"
                    class="h-[420px] w-full border-0"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </section>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
</body>
</html>
