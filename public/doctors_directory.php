<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

function doctor_photo_url(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $value) || str_starts_with($value, '/')) {
        return $value;
    }

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
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }
        $sql .= ' ORDER BY id DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();
    } catch (Exception $e) {
        $dbError = $e->getMessage();
    }
}

$displayCount = $q !== '' ? count($items) : $totalDoctors;
$heroCountLabel = $q !== ''
    ? 'พบแพทย์ ' . number_format($displayCount) . ' ท่าน'
    : 'มีแพทย์ทั้งหมด ' . number_format($totalDoctors) . ' ท่าน';
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
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }

        .doctor-hero {
            background:
                radial-gradient(circle at 12% 26%, rgba(255, 255, 255, 0.94) 0, rgba(255, 255, 255, 0.72) 16%, rgba(255, 255, 255, 0) 36%),
                radial-gradient(circle at 30% 22%, rgba(255, 255, 255, 0.85) 0, rgba(255, 255, 255, 0.5) 11%, rgba(255, 255, 255, 0) 24%),
                radial-gradient(circle at 64% 36%, rgba(255, 255, 255, 0.48) 0, rgba(255, 255, 255, 0.18) 13%, rgba(255, 255, 255, 0) 26%),
                radial-gradient(circle at 84% 24%, rgba(34, 211, 238, 0.34) 0, rgba(34, 211, 238, 0.1) 14%, rgba(34, 211, 238, 0) 34%),
                linear-gradient(105deg, rgba(234, 244, 255, 0.94) 0%, rgba(214, 237, 255, 0.84) 35%, rgba(145, 194, 229, 0.52) 58%, rgba(48, 110, 165, 0.34) 100%),
                linear-gradient(135deg, #ddefff 0%, #eff7ff 35%, #a9d7ef 60%, #57add3 100%);
        }

        .doctor-hero::before,
        .doctor-hero::after {
            content: '';
            position: absolute;
            inset: auto;
            border-radius: 999px;
            pointer-events: none;
        }

        .doctor-hero::before {
            width: 360px;
            height: 360px;
            right: -48px;
            top: -72px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.28) 0%, rgba(255, 255, 255, 0.06) 45%, rgba(255, 255, 255, 0) 72%);
        }

        .doctor-hero::after {
            width: 420px;
            height: 420px;
            left: -140px;
            bottom: -180px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.62) 0%, rgba(255, 255, 255, 0.18) 34%, rgba(255, 255, 255, 0) 70%);
        }

        .doctor-hero__mesh {
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.14) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.14) 1px, transparent 1px);
            background-size: 44px 44px;
            mask-image: linear-gradient(90deg, transparent 0%, rgba(0, 0, 0, 0.65) 35%, rgba(0, 0, 0, 0.85) 100%);
            -webkit-mask-image: linear-gradient(90deg, transparent 0%, rgba(0, 0, 0, 0.65) 35%, rgba(0, 0, 0, 0.85) 100%);
        }
    </style>
</head>
<body class="bg-[#eef5ff] text-slate-800">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <section class="px-4 pt-0 md:px-6 lg:px-8">
        <div class="doctor-hero relative isolate overflow-hidden">
            <div class="doctor-hero__mesh absolute inset-y-0 right-0 w-[44%] opacity-55"></div>
            <div class="absolute right-[10%] top-10 hidden h-40 w-40 rounded-full border border-white/35 bg-white/10 backdrop-blur-sm lg:block"></div>
            <div class="absolute right-[18%] bottom-10 hidden h-24 w-24 rounded-full border border-white/30 bg-cyan-200/20 backdrop-blur-sm lg:block"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-white/5 via-transparent to-sky-900/18"></div>

            <div class="relative mx-auto flex min-h-[320px] max-w-7xl items-center justify-center px-6 py-14 text-center md:px-10">
                <div class="max-w-4xl text-white drop-shadow-[0_4px_20px_rgba(15,23,42,0.18)]">
                    <h1 class="text-4xl font-extrabold text-white md:text-5xl lg:text-6xl">ทำเนียบแพทย์</h1>
                    <p class="mx-auto mt-4 max-w-3xl text-lg font-medium leading-relaxed text-white/95 md:text-2xl">
                        ทีมแพทย์ผู้เชี่ยวชาญที่พร้อมให้บริการด้วยความเอาใจใส่และประสบการณ์ระดับมืออาชีพ
                    </p>
                    <div class="mt-8 inline-flex items-center gap-3 rounded-full border border-white/35 bg-white/18 px-7 py-3 text-lg font-semibold text-white shadow-[0_18px_40px_rgba(255,255,255,0.08)] backdrop-blur-md">
                        <i class="fa-solid fa-stethoscope text-xl"></i>
                        <span><?php echo h($heroCountLabel); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="px-4 py-12 md:px-6 lg:px-8 lg:py-14">
        <div class="mx-auto max-w-7xl">
            <?php if ($q !== ''): ?>
                <div class="mb-6 flex items-center justify-between gap-4 rounded-2xl bg-white/90 px-5 py-4 text-slate-700 shadow-sm ring-1 ring-slate-200">
                    <div>
                        ผลการค้นหาสำหรับ <strong><?php echo h($q); ?></strong>
                    </div>
                    <a href="/doctors_directory.php" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">ล้างการค้นหา</a>
                </div>
            <?php endif; ?>

            <?php if ($dbError !== ''): ?>
                <div class="mb-6 rounded-2xl border border-yellow-300 bg-yellow-50 px-5 py-4 text-yellow-900">
                    ไม่สามารถโหลดข้อมูลแพทย์ได้: <?php echo h($dbError); ?>
                </div>
            <?php endif; ?>

            <div class="grid gap-8 lg:grid-cols-2">
                <?php foreach ($items as $doctor): ?>
                    <?php
                    $photoUrl = doctor_photo_url($doctor['photo'] ?? '');
                    $specialty = trim((string) ($doctor['specialty'] ?? ''));
                    $clinicName = trim((string) ($doctor['clinic_name'] ?? ''));
                    $doctorNameTh = trim((string) ($doctor['name_th'] ?? ''));
                    $doctorNameEn = trim((string) ($doctor['name_en'] ?? ''));
                    ?>
                    <article class="rounded-[28px] bg-white p-6 shadow-[0_16px_40px_rgba(15,23,42,0.08)] ring-1 ring-slate-200/70 transition hover:-translate-y-1 hover:shadow-[0_24px_52px_rgba(15,23,42,0.12)]">
                        <div class="flex flex-col gap-6 md:flex-row md:items-start">
                            <div class="w-full shrink-0 md:w-[200px]">
                                <div class="aspect-[4/5] overflow-hidden rounded-[22px] bg-slate-100">
                                    <?php if ($photoUrl !== ''): ?>
                                        <img src="<?php echo h($photoUrl); ?>" alt="<?php echo h($doctorNameTh !== '' ? $doctorNameTh : 'Doctor'); ?>" class="h-full w-full object-cover object-top">
                                    <?php else: ?>
                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 text-slate-400">
                                            <i class="fa-solid fa-user-doctor text-5xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="min-w-0 flex-1 pt-1">
                                <h2 class="text-2xl font-bold leading-tight text-slate-800 lg:text-[2rem]"><?php echo h($doctorNameTh); ?></h2>
                                <?php if ($doctorNameEn !== ''): ?>
                                    <p class="mt-2 text-lg font-medium uppercase tracking-wide text-slate-500"><?php echo h($doctorNameEn); ?></p>
                                <?php endif; ?>

                                <div class="mt-6 space-y-4 text-lg leading-relaxed text-slate-600 lg:text-xl">
                                    <p><span class="font-semibold text-slate-800">เฉพาะทาง:</span> <?php echo h($specialty !== '' ? $specialty : '-'); ?></p>
                                    <p><span class="font-semibold uppercase text-slate-800">CLINIC :</span> <?php echo h($clinicName !== '' ? $clinicName : '-'); ?></p>
                                </div>

                                <?php if (!empty($doctor['phone']) || !empty($doctor['email'])): ?>
                                    <div class="mt-6 flex flex-wrap gap-3 text-sm text-slate-600">
                                        <?php if (!empty($doctor['phone'])): ?>
                                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2">
                                                <i class="fa-solid fa-phone text-slate-500"></i>
                                                <?php echo h($doctor['phone']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($doctor['email'])): ?>
                                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2">
                                                <i class="fa-solid fa-envelope text-slate-500"></i>
                                                <?php echo h($doctor['email']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>

                <?php if (empty($items)): ?>
                    <div class="lg:col-span-2">
                        <div class="rounded-[28px] border border-dashed border-slate-300 bg-white/80 px-8 py-16 text-center text-slate-500">
                            ยังไม่มีข้อมูลแพทย์ที่พร้อมแสดงผล
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
</body>
</html>
