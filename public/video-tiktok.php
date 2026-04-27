<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

function video_thumbnail_url(?string $thumbnail): string
{
    $thumbnail = trim((string) $thumbnail);
    if ($thumbnail === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $thumbnail) || str_starts_with($thumbnail, '/')) {
        return $thumbnail;
    }

    return '/uploads/videos/' . rawurlencode($thumbnail);
}

function tiktok_video_id(?string $url): ?string
{
    $url = trim((string) $url);
    if ($url === '') {
        return null;
    }

    if (preg_match('#/video/(\d+)#', $url, $matches)) {
        return $matches[1];
    }

    return null;
}

function youtube_embed_url(?string $url): ?string
{
    $url = trim((string) $url);
    if ($url === '') {
        return null;
    }

    if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})#', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }

    if (preg_match('#youtube\.com/embed/([A-Za-z0-9_-]{6,})#', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }

    return null;
}

$dbReady = $pdo instanceof PDO;
$dbError = '';
$member = current_member();
$isDoctor = member_is_doctor();
$loginRedirect = '/login.php?redirect=' . urlencode('/video-tiktok.php#learning-videos');
$registerRedirect = '/register.php?redirect=' . urlencode('/video-tiktok.php#learning-videos');

$publicTikTokVideos = [];
$learningVideos = [];
$hasTikTokEmbeds = false;

if ($dbReady) {
    try {
        ensure_videos_access_level_column($pdo);

        $publicTikTokVideos = $pdo->query(
            "SELECT *
             FROM videos
             WHERE is_active = 1
               AND platform = 'tiktok'
             ORDER BY sort_order ASC, id DESC"
        )->fetchAll();

        $candidateLearningVideos = $pdo->query(
            "SELECT *
             FROM videos
             WHERE is_active = 1
               AND (
                   platform <> 'tiktok'
                   OR access_level <> 'public'
               )
             ORDER BY sort_order ASC, id DESC"
        )->fetchAll();

        foreach ($candidateLearningVideos as $video) {
            if (member_can_access_video($video['access_level'] ?? 'public')) {
                $learningVideos[] = $video;
            }
        }
    } catch (Exception $e) {
        $dbError = $e->getMessage();
    }
}

foreach (array_merge($publicTikTokVideos, $learningVideos) as $video) {
    if (tiktok_video_id($video['video_url'] ?? '') !== null) {
        $hasTikTokEmbeds = true;
        break;
    }
}

$siteHeaderActive = 'vdo';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Channel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }

        .learning-video-card__description {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            line-clamp: 3;
        }

        .tiktok-gallery {
            display: grid;
            gap: 16px;
        }

        @media (min-width: 640px) {
            .tiktok-gallery {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .tiktok-gallery {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 18px;
            }
        }

        @media (min-width: 1536px) {
            .tiktok-gallery {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .tiktok-tile {
            overflow: hidden;
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
        }

        .tiktok-tile .tiktok-embed {
            margin: 0 !important;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <section class="relative overflow-hidden bg-gradient-to-r from-indigo-700 via-violet-600 to-fuchsia-600 text-white">
        <div class="absolute inset-0 opacity-30" style="background-image:radial-gradient(circle at top left, white 0, transparent 35%), radial-gradient(circle at bottom right, white 0, transparent 28%);"></div>
        <div class="container mx-auto px-6 py-16 relative">
            <div class="max-w-4xl mx-auto text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-sm font-medium">
                    <i class="fa-solid fa-play"></i> Video Channel
                </div>
                <h1 class="text-4xl md:text-5xl font-bold">คลังวิดีโอ KNA Interpharma</h1>
                <p class="mt-4 text-lg text-white/90">
                    วิดีโอทั้งหมดที่แสดงในหน้านี้สามารถเล่นได้ทันทีบนหน้าเว็บ โดยแยกเป็น TikTok สาธารณะ และวิดีโอความรู้ตามสิทธิ์การเข้าถึง
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="#tiktok-videos" class="rounded-full bg-white px-5 py-3 font-semibold text-indigo-700 hover:bg-indigo-50">TikTok สาธารณะ</a>
                    <a href="#learning-videos" class="rounded-full border border-white/40 px-5 py-3 font-semibold hover:bg-white/10">วิดีโอความรู้</a>
                </div>
            </div>
        </div>
    </section>

    <?php if ($dbError !== ''): ?>
        <div class="container mx-auto px-6 pt-8">
            <div class="rounded-2xl border border-yellow-300 bg-yellow-50 px-5 py-4 text-yellow-900">
                ไม่สามารถโหลดข้อมูลวิดีโอได้: <?php echo h($dbError); ?>
            </div>
        </div>
    <?php endif; ?>

    <section id="tiktok-videos" class="py-14">
        <div class="container mx-auto px-6">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900">TikTok ที่ทุกคนเข้าถึงได้</h2>
                    <p class="mt-2 text-slate-500">แสดงคลิปแบบมินิมอล กดเล่นได้ทันที และจัดวางให้ชิดกันมากขึ้นเพื่อดูเป็น gallery เดียว</p>
                </div>
                <div class="rounded-2xl bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                    เปิดดูได้โดยไม่ต้องล็อกอิน
                </div>
            </div>

            <?php if (empty($publicTikTokVideos)): ?>
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                    ยังไม่มีวิดีโอ TikTok สาธารณะในขณะนี้
                </div>
            <?php else: ?>
                <div class="tiktok-gallery">
                    <?php foreach ($publicTikTokVideos as $video): ?>
                        <?php
                        $videoUrl = trim((string) ($video['video_url'] ?? ''));
                        $tiktokId = tiktok_video_id($videoUrl);
                        $thumbnailUrl = video_thumbnail_url($video['thumbnail'] ?? '');
                        ?>
                        <article class="tiktok-tile">
                            <div class="<?php echo $tiktokId !== null ? 'aspect-[9/16]' : 'aspect-video'; ?> bg-slate-100">
                                <?php if ($tiktokId !== null): ?>
                                    <blockquote class="tiktok-embed" cite="<?php echo h($videoUrl); ?>" data-video-id="<?php echo h($tiktokId); ?>" style="max-width:100%;min-width:100%;height:100%;margin:0;">
                                        <section></section>
                                    </blockquote>
                                <?php elseif ($thumbnailUrl !== ''): ?>
                                    <img src="<?php echo h($thumbnailUrl); ?>" alt="<?php echo h($video['title'] ?? 'TikTok'); ?>" class="h-full w-full object-cover">
                                <?php else: ?>
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-900 via-slate-700 to-slate-500 text-5xl text-white">
                                        <i class="fa-brands fa-tiktok"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if ($tiktokId === null): ?>
                                <div class="border-t border-slate-200 px-4 py-3">
                                    <a class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-700 hover:text-indigo-900" href="<?php echo h($videoUrl !== '' ? $videoUrl : '#'); ?>" target="_blank" rel="noopener noreferrer">
                                        เปิดใน TikTok <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section id="learning-videos" class="border-t border-slate-200 bg-white py-14">
        <div class="container mx-auto px-6">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900">วิดีโอความรู้สำหรับผู้ใช้หรือคุณหมอ</h2>
                    <p class="mt-2 text-slate-500">
                        วิดีโอส่วนนี้แสดงตามสิทธิ์ของบัญชีที่ล็อกอินอยู่ และสามารถเล่นบนหน้าได้ทันทีเช่นกัน
                    </p>
                </div>

                <?php if ($member !== null): ?>
                    <div class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        กำลังใช้งานในสิทธิ์ <?php echo h(member_role_label($member['role'] ?? 'member')); ?>
                    </div>
                <?php else: ?>
                    <div class="rounded-2xl bg-slate-100 px-4 py-3 text-sm text-slate-600">
                        ล็อกอินเพื่อดูคลังวิดีโอความรู้
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($member === null): ?>
                <div class="rounded-3xl border border-indigo-200 bg-gradient-to-r from-indigo-50 to-white p-8 md:p-10">
                    <div class="max-w-3xl">
                        <div class="inline-flex items-center gap-2 rounded-full bg-indigo-100 px-4 py-2 text-sm font-semibold text-indigo-700">
                            <i class="fa-solid fa-lock"></i> Learning Access
                        </div>
                        <h3 class="mt-5 text-2xl font-bold text-slate-900">ส่วนวิดีโอความรู้ต้องเข้าสู่ระบบก่อน</h3>
                        <p class="mt-3 leading-7 text-slate-600">
                            สมาชิกทั่วไปจะเห็นวิดีโอสำหรับผู้ใช้ และบัญชีที่มี role เป็น <strong>doctor</strong> จะเห็นวิดีโอเฉพาะแพทย์เพิ่มเติม
                        </p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="<?php echo h($loginRedirect); ?>" class="inline-flex items-center gap-2 rounded-full bg-indigo-700 px-5 py-3 font-semibold text-white hover:bg-indigo-800">
                                <i class="fa-solid fa-right-to-bracket"></i> เข้าสู่ระบบ
                            </a>
                            <a href="<?php echo h($registerRedirect); ?>" class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:border-indigo-300 hover:text-indigo-700">
                                <i class="fa-solid fa-user-plus"></i> สมัครสมาชิก
                            </a>
                        </div>
                    </div>
                </div>
            <?php elseif (empty($learningVideos)): ?>
                <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center text-slate-500">
                    ยังไม่มีวิดีโอความรู้ที่ตรงกับสิทธิ์ของบัญชีนี้
                </div>
            <?php else: ?>
                <?php if (!$isDoctor): ?>
                    <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-800">
                        บัญชีนี้เห็นเฉพาะวิดีโอสำหรับสมาชิกทั่วไป ส่วนวิดีโอที่ตั้งสิทธิ์เป็น <strong>doctor</strong> จะถูกซ่อนไว้
                    </div>
                <?php endif; ?>

                <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">
                    <?php foreach ($learningVideos as $video): ?>
                        <?php
                        $videoUrl = trim((string) ($video['video_url'] ?? ''));
                        $thumbnailUrl = video_thumbnail_url($video['thumbnail'] ?? '');
                        $accessLevel = normalize_video_access_level($video['access_level'] ?? 'public');
                        ?>
                        <a href="/video-detail.php?id=<?php echo (int) ($video['id'] ?? 0); ?>" class="group block overflow-hidden rounded-3xl border border-slate-200 bg-slate-50 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                            <div class="aspect-video bg-slate-100">
                                <?php if ($thumbnailUrl !== ''): ?>
                                    <img src="<?php echo h($thumbnailUrl); ?>" alt="<?php echo h($video['title'] ?? 'Video'); ?>" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                <?php else: ?>
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-indigo-800 via-indigo-600 to-cyan-500 text-4xl text-white">
                                        <i class="fa-solid fa-circle-play"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-6">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-indigo-600"><?php echo h($video['platform'] ?? 'video'); ?></div>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold <?php echo $accessLevel === 'doctor' ? 'bg-rose-100 text-rose-700' : ($accessLevel === 'member' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700'); ?>">
                                        <?php echo $accessLevel === 'doctor' ? 'Doctor Only' : ($accessLevel === 'member' ? 'Member' : 'Public'); ?>
                                    </span>
                                </div>
                                <h3 class="mt-3 text-xl font-bold text-slate-900"><?php echo h($video['title'] ?? ''); ?></h3>
                                <?php if (!empty($video['description'])): ?>
                                    <p class="learning-video-card__description mt-3 text-sm leading-6 text-slate-600"><?php echo h($video['description']); ?></p>
                                <?php else: ?>
                                    <p class="mt-3 text-sm leading-6 text-slate-500">กดเพื่อดูรายละเอียดวิดีโอและลิงก์รับชมแบบเต็ม</p>
                                <?php endif; ?>
                                <span class="mt-5 inline-flex items-center gap-2 font-semibold text-indigo-700 transition group-hover:text-indigo-900">
                                    ดูรายละเอียดวิดีโอ
                                    <i class="fa-solid fa-arrow-right text-sm"></i>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
    <?php if ($hasTikTokEmbeds): ?>
        <script async src="https://www.tiktok.com/embed.js"></script>
    <?php endif; ?>
</body>
</html>
