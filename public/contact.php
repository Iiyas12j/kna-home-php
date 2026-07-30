<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

$page_title = 'Contact Us - KNA Interpharma';
$errors = [];
$success_message = '';
$db_ready = $pdo instanceof PDO;

$form = [
    'name'    => '',
    'email'   => '',
    'phone'   => '',
    'subject' => '',
    'message' => '',
];

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
    } catch (Exception $e) {}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'คำขอไม่ถูกต้อง กรุณาลองใหม่';
    }

    foreach ($form as $field => $_) {
        $form[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    if ($form['name'] === '')   $errors[] = 'กรุณากรอกชื่อผู้ติดต่อ';
    if ($form['email'] === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL))
        $errors[] = 'กรุณากรอกอีเมลให้ถูกต้อง';
    if ($form['subject'] === '') $errors[] = 'กรุณากรอกหัวข้อ';
    if ($form['message'] === '') $errors[] = 'กรุณากรอกรายละเอียดข้อความ';
    if (!$db_ready)              $errors[] = 'ระบบฐานข้อมูลยังไม่พร้อม';

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
            foreach ($form as $field => $_) { $form[$field] = ''; }
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
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }
        .branch-card { transition: box-shadow .2s; }
        .branch-card:hover { box-shadow: 0 8px 32px rgba(75,72,153,0.13); }
    </style>
</head>
<body class="bg-gray-50">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <!-- Hero Banner -->
    <section class="relative bg-gradient-to-r from-[#4B4899] to-[#6B63C8] overflow-hidden">
        <img src="/uploads/website/bg-contact-us/bg-contact-us.jpg" alt="Contact Us" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>
        <div class="relative mx-auto max-w-7xl px-4 pb-20 pt-16 sm:px-6 lg:px-8">
            <div class="max-w-2xl ml-auto text-right">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white/90 backdrop-blur">
                    <i class="fa-solid fa-headset"></i> Contact Us
                </div>
                <h1 class="mt-5 text-4xl font-extrabold leading-tight text-white sm:text-5xl">
                    ติดต่อเรา<br class="hidden sm:block">KNA Interpharma
                </h1>
            </div>
        </div>
    </section>

    <!-- Two Branch Sections -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4 max-w-6xl">
            <div class="grid md:grid-cols-2 gap-8">

                <!-- Bangkok Branch -->
                <div class="branch-card bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-100" style="background: linear-gradient(135deg,#4B4899 0%,#6B63C8 100%);">
                        <div class="flex items-center gap-3 text-white">
                            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                                <i class="fa-solid fa-building text-lg"></i>
                            </div>
                            <div>
                                <p class="text-white/70 text-xs uppercase tracking-widest">Branch</p>
                                <h2 class="text-xl font-bold">สาขากรุงเทพมหานคร</h2>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex gap-3">
                            <i class="fa-solid fa-location-dot text-[#4B4899] mt-1 w-5 text-center shrink-0"></i>
                            <p class="text-gray-700 leading-relaxed">
                                877/10 nirvana@work ถ.พระราม 9<br>
                                เขตสวนหลวง กรุงเทพมหานคร 10250
                            </p>
                        </div>
                        <a href="https://maps.app.goo.gl/jEueCJzFEz2pAtBr8" target="_blank" rel="noreferrer"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white"
                           style="background:#4B4899;">
                            <i class="fa-solid fa-map-location-dot"></i>
                            เปิดแผนที่ Google Maps
                        </a>
                    </div>
                    <div class="h-64 w-full">
                        <iframe
                            title="KNA สาขากรุงเทพมหานคร"
                            src="https://www.google.com/maps?q=877/10+nirvana@work+Rama+9+Suan+Luang+Bangkok+10250&output=embed"
                            class="w-full h-full border-0"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>

                <!-- Nakhon Sawan Branch -->
                <div class="branch-card bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-100" style="background: linear-gradient(135deg,#4B4899 0%,#6B63C8 100%);">
                        <div class="flex items-center gap-3 text-white">
                            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                                <i class="fa-solid fa-hospital text-lg"></i>
                            </div>
                            <div>
                                <p class="text-white/70 text-xs uppercase tracking-widest">Head Office</p>
                                <h2 class="text-xl font-bold">สาขานครสวรรค์</h2>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex gap-3">
                            <i class="fa-solid fa-location-dot text-[#4B4899] mt-1 w-5 text-center shrink-0"></i>
                            <p class="text-gray-700 leading-relaxed">
                                175-175/1 ถ.สวรรค์วิถี ต.ปากน้ำโพ<br>
                                อ.เมืองนครสวรรค์ จ.นครสวรรค์ 60000
                            </p>
                        </div>
                        <div class="flex gap-3">
                            <i class="fa-solid fa-phone text-[#4B4899] mt-1 w-5 text-center shrink-0"></i>
                            <a href="tel:056200890" class="text-gray-700 hover:text-[#4B4899] font-medium">056-200890</a>
                        </div>
                        <a href="https://maps.app.goo.gl/esPWwrKxv9BUowuXA" target="_blank" rel="noreferrer"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white"
                           style="background:#4B4899;">
                            <i class="fa-solid fa-map-location-dot"></i>
                            เปิดแผนที่ Google Maps
                        </a>
                    </div>
                    <div class="h-64 w-full">
                        <iframe
                            title="KNA สาขานครสวรรค์"
                            src="https://www.google.com/maps?q=175-175/1+%E0%B8%96.%E0%B8%AA%E0%B8%A7%E0%B8%A3%E0%B8%A3%E0%B8%84%E0%B9%8C%E0%B8%A7%E0%B8%B4%E0%B8%96%E0%B8%B5+%E0%B8%95.%E0%B8%9B%E0%B8%B2%E0%B8%81%E0%B8%99%E0%B9%89%E0%B8%B3%E0%B9%82%E0%B8%9E+%E0%B8%AD.%E0%B9%80%E0%B8%A1%E0%B8%B7%E0%B8%AD%E0%B8%87%E0%B8%99%E0%B8%84%E0%B8%A3%E0%B8%AA%E0%B8%A7%E0%B8%A3%E0%B8%A3%E0%B8%84%E0%B9%8C&output=embed"
                            class="w-full h-full border-0"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Additional Contact Channels -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4 max-w-6xl">
            <h2 class="text-3xl font-bold text-center mb-12" style="color:#4B4899;">ช่องทางการติดต่อเพิ่มเติม</h2>
            <div class="grid sm:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl p-6 text-center border border-gray-100 shadow-sm">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background:#EEF0FA;">
                        <i class="fa-solid fa-envelope text-2xl" style="color:#4B4899;"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">อีเมล</h3>
                    <a href="mailto:info@knainterpharma.co.th" class="text-sm text-gray-600 hover:text-[#4B4899] break-all">
                        info@knainterpharma.co.th
                    </a>
                </div>
                <div class="bg-white rounded-2xl p-6 text-center border border-gray-100 shadow-sm">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background:#EEF0FA;">
                        <i class="fa-solid fa-clock text-2xl" style="color:#4B4899;"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">เวลาทำการ</h3>
                    <p class="text-sm text-gray-600">จันทร์ - ศุกร์<br>09:00 - 17:00 น.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 text-center border border-gray-100 shadow-sm">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background:#EEF0FA;">
                        <i class="fa-solid fa-share-nodes text-2xl" style="color:#4B4899;"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 mb-2">โซเชียลมีเดีย</h3>
                    <div class="flex justify-center gap-4 mt-2">
                        <a href="#" class="text-gray-400 hover:text-[#4B4899] text-xl transition"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-black text-xl transition"><i class="fa-brands fa-tiktok"></i></a>
                        <a href="#" class="text-gray-400 hover:text-pink-500 text-xl transition"><i class="fa-brands fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA: We Are Ready to Assist You -->
    <section class="py-20" style="background: linear-gradient(135deg,#4B4899 0%,#6B63C8 100%);">
        <div class="container mx-auto px-4 text-center text-white">
            <h2 class="text-4xl font-bold mb-4">We Are Ready to Assist You</h2>
            <p class="text-white/80 text-lg mb-10 max-w-xl mx-auto">
                ทีมงาน KNA พร้อมให้คำปรึกษาและตอบทุกคำถามของคุณ ติดต่อเราได้ทันที
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="tel:056200890"
                   class="inline-flex items-center gap-2 px-8 py-4 rounded-xl font-bold text-base bg-white transition hover:bg-gray-100"
                   style="color:#4B4899;">
                    <i class="fa-solid fa-phone"></i>
                    โทรติดต่อเรา
                </a>
                <a href="/searchpage.php"
                   class="inline-flex items-center gap-2 px-8 py-4 rounded-xl font-bold text-base border-2 border-white text-white transition hover:bg-white/10">
                    <i class="fa-solid fa-magnifying-glass-location"></i>
                    ค้นหาคลินิก
                </a>
            </div>

            <!-- Contact Form -->
            <div class="mt-14 max-w-2xl mx-auto bg-white/10 backdrop-blur rounded-3xl p-8 text-left border border-white/20">
                <h3 class="text-2xl font-bold text-white mb-6 text-center">ส่งข้อความถึงเรา</h3>

                <?php if ($success_message !== ''): ?>
                    <div class="mb-6 rounded-xl bg-green-500/20 border border-green-400/30 px-5 py-4 text-green-100">
                        <?php echo h($success_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="mb-6 rounded-xl bg-red-500/20 border border-red-400/30 px-5 py-4 text-red-100">
                        <ul class="list-disc pl-5 space-y-1">
                            <?php foreach ($errors as $e): ?>
                                <li><?php echo h($e); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-white/80 mb-1">ชื่อผู้ติดต่อ *</label>
                            <input type="text" name="name" value="<?php echo h($form['name']); ?>" required
                                class="w-full rounded-xl bg-white/10 border border-white/20 px-4 py-3 text-white placeholder-white/40 focus:outline-none focus:border-white/60"
                                placeholder="ชื่อของคุณ">
                        </div>
                        <div>
                            <label class="block text-sm text-white/80 mb-1">อีเมล *</label>
                            <input type="email" name="email" value="<?php echo h($form['email']); ?>" required
                                class="w-full rounded-xl bg-white/10 border border-white/20 px-4 py-3 text-white placeholder-white/40 focus:outline-none focus:border-white/60"
                                placeholder="email@example.com">
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-white/80 mb-1">เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" value="<?php echo h($form['phone']); ?>"
                                class="w-full rounded-xl bg-white/10 border border-white/20 px-4 py-3 text-white placeholder-white/40 focus:outline-none focus:border-white/60"
                                placeholder="08X-XXX-XXXX">
                        </div>
                        <div>
                            <label class="block text-sm text-white/80 mb-1">หัวข้อ *</label>
                            <input type="text" name="subject" value="<?php echo h($form['subject']); ?>" required
                                class="w-full rounded-xl bg-white/10 border border-white/20 px-4 py-3 text-white placeholder-white/40 focus:outline-none focus:border-white/60"
                                placeholder="หัวข้อการติดต่อ">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-white/80 mb-1">รายละเอียดข้อความ *</label>
                        <textarea name="message" rows="5" required
                            class="w-full rounded-xl bg-white/10 border border-white/20 px-4 py-3 text-white placeholder-white/40 focus:outline-none focus:border-white/60"
                            placeholder="รายละเอียดที่ต้องการติดต่อ..."><?php echo h($form['message']); ?></textarea>
                    </div>
                    <div class="text-center">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-8 py-3 rounded-xl bg-white font-bold text-base transition hover:bg-gray-100"
                            style="color:#4B4899;">
                            <i class="fa-solid fa-paper-plane"></i>
                            ส่งข้อความ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
</body>
</html>
