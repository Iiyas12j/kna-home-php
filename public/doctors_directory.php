<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

function doctor_photo_url(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') return '';
    if (preg_match('#^https?://#i', $value) || str_starts_with($value, '/')) return $value;
    return '/uploads/doctors/' . rawurlencode($value);
}

$q = trim((string) ($_GET['q'] ?? ''));
$items = [];
$dbError = '';
$totalDoctors = 0;

if ($pdo instanceof PDO) {
    try {
        $totalDoctors = (int) $pdo->query('SELECT COUNT(*) FROM doctors WHERE is_active = 1')->fetchColumn();

        $sql = 'SELECT * FROM doctors WHERE is_active = 1';
        $params = [];
        if ($q !== '') {
            $sql .= ' AND (name_th LIKE ? OR name_en LIKE ? OR specialty LIKE ? OR clinic_name LIKE ?)';
            $params = array_fill(0, 4, '%' . $q . '%');
        }
        $sql .= ' ORDER BY id ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();
    } catch (Exception $e) {
        $dbError = $e->getMessage();
    }
}

$siteHeaderActive = 'trainer';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ทำเนียบแพทย์ - KNA Interpharma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }

        .hero-doctor {
            position: relative;
            background-color: #1e293b;
            overflow: hidden;
        }

        .doctor-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            transition: box-shadow .2s, transform .2s;
        }
        .doctor-card:hover {
            box-shadow: 0 8px 28px rgba(0,0,0,0.10);
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-50" style="font-family:'Kanit',sans-serif;">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <!-- Hero Banner -->
    <section class="hero-doctor text-white">
        <img src="/uploads/website/doctors-diretory-imge/bg-doctor.jpg"
             alt="ทำเนียบแพทย์"
             class="absolute inset-0 w-full h-full object-cover object-center">
        <div class="absolute inset-0 bg-black/40"></div>
        <div class="relative z-10 mx-auto max-w-7xl px-4 pb-20 pt-16 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto text-center">
                <h1 class="text-4xl font-extrabold leading-tight text-white sm:text-5xl">
                    ทำเนียบแพทย์<br class="hidden sm:block">KNA Interpharma
                </h1>
                <div class="mt-5 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white/90 backdrop-blur">
                    <i class="fa-solid fa-stethoscope"></i> มีแพทย์ทั้งหมด <?php echo number_format($totalDoctors); ?> ท่าน
                </div>
            </div>
        </div>
    </section>

    <!-- Search -->
    <div class="container mx-auto px-4 max-w-7xl mt-8 mb-2">
        <form method="get" class="flex gap-3">
            <input type="text" name="q" value="<?php echo h($q); ?>"
                   placeholder="ค้นหาชื่อแพทย์, คลินิก, ความเชี่ยวชาญ..."
                   class="flex-1 border border-gray-300 rounded-xl px-4 py-3 text-gray-800 focus:outline-none focus:border-[#4B4899] focus:ring-2 focus:ring-[#4B4899]/20">
            <button type="submit"
                    class="px-6 py-3 rounded-xl text-white font-semibold transition hover:opacity-90"
                    style="background:#4B4899;">
                <i class="fa-solid fa-search mr-2"></i>ค้นหา
            </button>
            <?php if ($q !== ''): ?>
            <a href="/doctors_directory.php"
               class="px-5 py-3 rounded-xl border border-gray-300 text-gray-600 font-semibold hover:bg-gray-100 transition">
                ล้าง
            </a>
            <?php endif; ?>
        </form>
        <?php if ($q !== ''): ?>
        <p class="text-sm text-gray-500 mt-3">
            ผลการค้นหา "<strong class="text-gray-700"><?php echo h($q); ?></strong>" — พบ <?php echo count($items); ?> ท่าน
        </p>
        <?php endif; ?>
    </div>

    <!-- Doctor Grid -->
    <div class="container mx-auto px-4 max-w-7xl py-8">

        <?php if ($dbError !== ''): ?>
            <div class="mb-6 rounded-xl bg-yellow-50 border border-yellow-300 px-5 py-4 text-yellow-800 text-sm">
                ไม่สามารถโหลดข้อมูลแพทย์ได้: <?php echo h($dbError); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <div class="text-center py-20 text-gray-400">
                <i class="fa-solid fa-user-doctor text-5xl mb-4 block"></i>
                ยังไม่มีข้อมูลแพทย์ที่พร้อมแสดงผล
            </div>
        <?php else: ?>

        <div class="grid lg:grid-cols-2 gap-8">
            <?php foreach ($items as $doctor):
                $photoUrl    = doctor_photo_url($doctor['photo'] ?? '');
                $nameTh      = trim((string) ($doctor['name_th'] ?? ''));
                $nameEn      = trim((string) ($doctor['name_en'] ?? ''));
                $specialty   = trim((string) ($doctor['specialty'] ?? ''));
                $clinicName  = trim((string) ($doctor['clinic_name'] ?? ''));
            ?>
            <div class="doctor-card flex flex-col sm:flex-row gap-6 sm:gap-8 p-6 sm:p-8">

                <!-- Photo -->
                <div class="shrink-0 w-full sm:w-[260px]">
                    <?php if ($photoUrl !== ''): ?>
                        <img src="<?php echo h($photoUrl); ?>"
                             alt="<?php echo h($nameTh); ?>"
                             class="w-full sm:w-[260px] h-[300px] object-cover object-top rounded-2xl shadow-sm">
                    <?php else: ?>
                        <div class="w-full sm:w-[260px] h-[300px] rounded-2xl flex items-center justify-center shadow-sm"
                             style="background:#eef0fa;">
                            <i class="fa-solid fa-user-doctor text-7xl" style="color:#4B4899; opacity:.35;"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0 py-2 flex flex-col justify-center">
                    <h2 class="text-2xl font-bold text-gray-900 leading-snug"><?php echo h($nameTh); ?></h2>

                    <?php if ($nameEn !== ''): ?>
                        <p class="text-base font-medium text-gray-400 uppercase tracking-wide mt-1.5">
                            <?php echo h($nameEn); ?>
                        </p>
                    <?php endif; ?>

                    <div class="mt-6 space-y-3 text-lg text-gray-700">
                        <p>
                            <span class="font-semibold text-gray-800">เฉพาะทาง</span>
                            <span class="text-gray-500 mx-1">:</span>
                            <?php echo h($specialty !== '' ? $specialty : '-'); ?>
                        </p>
                        <p>
                            <span class="font-semibold text-gray-800 uppercase">CLINIC</span>
                            <span class="text-gray-500 mx-1">:</span>
                            <?php echo h($clinicName !== '' ? $clinicName : '-'); ?>
                        </p>
                    </div>

                    <?php if (!empty($doctor['phone']) || !empty($doctor['email'])): ?>
                    <div class="flex flex-wrap gap-4 mt-6 text-base">
                        <?php if (!empty($doctor['phone'])): ?>
                            <a href="tel:<?php echo h($doctor['phone']); ?>"
                               class="flex items-center gap-2 text-gray-500 hover:text-[#4B4899] transition bg-gray-50 px-4 py-2 rounded-lg">
                                <i class="fa-solid fa-phone text-sm"></i>
                                <?php echo h($doctor['phone']); ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($doctor['email'])): ?>
                            <a href="mailto:<?php echo h($doctor['email']); ?>"
                               class="flex items-center gap-2 text-gray-500 hover:text-[#4B4899] transition bg-gray-50 px-4 py-2 rounded-lg">
                                <i class="fa-solid fa-envelope text-sm"></i>
                                <?php echo h($doctor['email']); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>
    </div>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
</body>
</html>
