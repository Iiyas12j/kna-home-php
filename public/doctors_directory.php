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
    <?php $page_title = 'ทำเนียบแพทย์ - KNA Interpharma'; require_once __DIR__ . '/partials/site-head.php'; ?>
    <style>
        .hero-doctor {
            position: relative;
            background-color: #1e293b;
            overflow: hidden;
        }

        .doctor-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: box-shadow .25s ease, transform .25s ease, border-color .25s ease;
        }
        .doctor-card:hover {
            box-shadow: 0 20px 40px rgba(15,23,42,0.12);
            transform: translateY(-4px);
            border-color: #c7c5ea;
        }
        .doctor-card__photo {
            position: relative;
            aspect-ratio: 4 / 5;
            background: #eef0fa;
            overflow: hidden;
        }
        .doctor-card__photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: 50% 18%;
            transition: transform .5s ease;
        }
        .doctor-card:hover .doctor-card__photo img {
            transform: scale(1.045);
        }
        .doctor-card__placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4B4899;
            opacity: .3;
            font-size: 3.5rem;
        }
        .doctor-card__body {
            padding: 1.35rem 1.35rem 1.5rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        .doctor-card__name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #111827;
            line-height: 1.35;
        }
        .doctor-card__nameEn {
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #9ca3af;
            margin-top: .3rem;
        }
        .doctor-card__meta {
            margin-top: 1rem;
            padding-top: .9rem;
            border-top: 1px solid #f1f0f7;
            display: flex;
            flex-direction: column;
            gap: .45rem;
            font-size: .86rem;
            color: #4b5563;
        }
        .doctor-card__meta .label {
            display: inline-block;
            min-width: 64px;
            font-weight: 600;
            color: #374151;
        }
        .doctor-card__contact {
            margin-top: auto;
            padding-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }
        .doctor-card__contact a {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .76rem;
            color: #6b7280;
            background: #f8f8fc;
            padding: .4rem .65rem;
            border-radius: 999px;
            transition: color .2s, background .2s;
        }
        .doctor-card__contact a:hover {
            color: #4B4899;
            background: #eef0fa;
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

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8">
            <?php foreach ($items as $doctor):
                $photoUrl    = doctor_photo_url($doctor['photo'] ?? '');
                $nameTh      = trim((string) ($doctor['name_th'] ?? ''));
                $nameEn      = trim((string) ($doctor['name_en'] ?? ''));
                $specialty   = trim((string) ($doctor['specialty'] ?? ''));
                $clinicName  = trim((string) ($doctor['clinic_name'] ?? ''));
            ?>
            <div class="doctor-card">

                <!-- Photo -->
                <div class="doctor-card__photo">
                    <?php if ($photoUrl !== ''): ?>
                        <img src="<?php echo h($photoUrl); ?>"
                             alt="<?php echo h($nameTh); ?>"
                             loading="lazy" decoding="async">
                    <?php else: ?>
                        <div class="doctor-card__placeholder">
                            <i class="fa-solid fa-user-doctor"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="doctor-card__body">
                    <h2 class="doctor-card__name"><?php echo h($nameTh); ?></h2>

                    <?php if ($nameEn !== ''): ?>
                        <p class="doctor-card__nameEn"><?php echo h($nameEn); ?></p>
                    <?php endif; ?>

                    <div class="doctor-card__meta">
                        <p>
                            <span class="label">เฉพาะทาง</span>
                            <?php echo h($specialty !== '' ? $specialty : '-'); ?>
                        </p>
                        <p>
                            <span class="label">Clinic</span>
                            <?php echo h($clinicName !== '' ? $clinicName : '-'); ?>
                        </p>
                    </div>

                    <?php if (!empty($doctor['phone']) || !empty($doctor['email'])): ?>
                    <div class="doctor-card__contact">
                        <?php if (!empty($doctor['phone'])): ?>
                            <a href="tel:<?php echo h($doctor['phone']); ?>">
                                <i class="fa-solid fa-phone text-xs"></i>
                                <?php echo h($doctor['phone']); ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($doctor['email'])): ?>
                            <a href="mailto:<?php echo h($doctor['email']); ?>">
                                <i class="fa-solid fa-envelope text-xs"></i>
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
