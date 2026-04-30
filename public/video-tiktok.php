<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

function video_thumbnail_url(?string $thumbnail): string
{
    $thumbnail = trim((string) $thumbnail);
    if ($thumbnail === '') return '';
    if (preg_match('#^https?://#i', $thumbnail) || str_starts_with($thumbnail, '/')) return $thumbnail;
    return '/uploads/videos/' . rawurlencode($thumbnail);
}

function tiktok_video_id(?string $url): ?string
{
    $url = trim((string) $url);
    if ($url === '') return null;
    if (preg_match('#/video/(\d+)#', $url, $matches)) return $matches[1];
    return null;
}

$dbReady         = $pdo instanceof PDO;
$dbError         = '';
$hasTikTokEmbeds = false;
$publicVideos    = [];

if ($dbReady) {
    try {
        ensure_videos_access_level_column($pdo);
        $publicVideos = $pdo->query(
            "SELECT * FROM videos
             WHERE is_active = 1 AND platform = 'tiktok' AND access_level = 'public'
             ORDER BY sort_order ASC, id DESC"
        )->fetchAll();

        foreach ($publicVideos as $v) {
            if (tiktok_video_id($v['video_url'] ?? '') !== null) {
                $hasTikTokEmbeds = true;
                break;
            }
        }
    } catch (Exception $e) {
        $dbError = $e->getMessage();
    }
}

$siteHeaderActive = 'vdo';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TikTok Channel - KNA Interpharma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }
        .tiktok-gallery {
            display: grid;
            gap: 16px;
        }
        @media (min-width: 640px)  { .tiktok-gallery { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1024px) { .tiktok-gallery { grid-template-columns: repeat(3, 1fr); gap: 20px; } }
        @media (min-width: 1536px) { .tiktok-gallery { grid-template-columns: repeat(4, 1fr); } }
        .tiktok-tile { overflow: hidden; border-radius: 20px; background: #fff; border: 1px solid #e2e8f0; }
        .tiktok-tile .tiktok-embed { margin: 0 !important; }
    </style>
</head>
<body class="bg-white text-slate-900">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <!-- Hero -->
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.18),_transparent_32%),linear-gradient(135deg,#0f172a_0%,#312e81_52%,#7c3aed_100%)]"></div>
        <div class="absolute right-[-80px] top-10 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="absolute left-[-60px] bottom-0 h-52 w-52 rounded-full bg-cyan-300/10 blur-3xl"></div>
        <div class="relative mx-auto max-w-7xl px-4 pb-14 pt-12 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white/90 backdrop-blur">
                    <i class="fa-brands fa-tiktok"></i> TikTok Channel
                </div>
                <h1 class="mt-5 text-4xl font-extrabold leading-tight text-white sm:text-5xl">
                    คลิป TikTok<br class="hidden sm:block">KNA Interpharma
                </h1>
                <p class="mt-5 text-base leading-8 text-slate-300">
                    คอนเทนต์สาธารณะจาก KNA Interpharma เปิดให้ทุกคนรับชมได้โดยไม่ต้องล็อกอิน
                </p>
            </div>
        </div>
    </section>

    <?php if ($dbError !== ''): ?>
        <div class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-yellow-200 bg-yellow-50 px-5 py-4 text-sm text-yellow-900">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i>ไม่สามารถโหลดข้อมูลวิดีโอได้
            </div>
        </div>
    <?php endif; ?>

    <!-- TikTok Videos -->
    <section class="bg-white py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <?php if (empty($publicVideos)): ?>
                <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-14 text-center">
                    <i class="fa-brands fa-tiktok text-5xl text-slate-300"></i>
                    <p class="mt-4 text-sm text-slate-400">ยังไม่มีวิดีโอ TikTok ในขณะนี้</p>
                </div>
            <?php else: ?>
                <div class="tiktok-gallery">
                    <?php foreach ($publicVideos as $video): ?>
                        <?php
                        $videoUrl = trim((string) ($video['video_url'] ?? ''));
                        $tiktokId = tiktok_video_id($videoUrl);
                        $thumbUrl = video_thumbnail_url($video['thumbnail'] ?? '');
                        ?>
                        <article class="tiktok-tile">
                            <div class="<?php echo $tiktokId !== null ? 'aspect-[9/16]' : 'aspect-video'; ?> bg-slate-100">
                                <?php if ($tiktokId !== null): ?>
                                    <blockquote class="tiktok-embed"
                                        cite="<?php echo h($videoUrl); ?>"
                                        data-video-id="<?php echo h($tiktokId); ?>"
                                        style="max-width:100%;min-width:100%;height:100%;margin:0;">
                                        <section></section>
                                    </blockquote>
                                <?php elseif ($thumbUrl !== ''): ?>
                                    <img src="<?php echo h($thumbUrl); ?>"
                                         alt="<?php echo h($video['title'] ?? 'TikTok'); ?>"
                                         class="h-full w-full object-cover">
                                <?php else: ?>
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-900 to-slate-700 text-5xl text-white">
                                        <i class="fa-brands fa-tiktok"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($video['title'])): ?>
                                <div class="border-t border-slate-100 px-4 py-3">
                                    <p class="text-sm font-semibold text-slate-800"><?php echo h($video['title']); ?></p>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Banner ลิงก์ไปหน้า Learning -->
            <div class="mt-16 overflow-hidden rounded-3xl border border-indigo-100 bg-gradient-to-r from-indigo-50 to-slate-50">
                <div class="flex flex-col items-center justify-between gap-6 px-8 py-8 sm:flex-row sm:px-10">
                    <div class="flex items-center gap-5">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700 text-xl">
                            <i class="fa-solid fa-user-doctor"></i>
                        </div>
                        <div>
                            <div class="font-extrabold text-slate-900">วิดีโอการเรียนการสอนเฉพาะแพทย์</div>
                            <p class="mt-1 text-sm text-slate-500">องค์ความรู้ทางวิชาการ เฉพาะบุคลากรทางการแพทย์ที่ผ่านการยืนยัน</p>
                        </div>
                    </div>
                    <a href="/video-learning.php"
                       class="inline-flex shrink-0 items-center gap-2 rounded-2xl bg-indigo-700 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800">
                        เข้าสู่ห้องเรียน <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>

        </div>
    </section>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
    <?php if ($hasTikTokEmbeds): ?>
        <script async src="https://www.tiktok.com/embed.js"></script>
    <?php endif; ?>
</body>
</html>
