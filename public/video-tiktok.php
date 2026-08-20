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

function is_direct_video(?string $url): bool
{
    $url = strtolower(trim((string) $url));
    return str_ends_with($url, '.mp4') || str_ends_with($url, '.webm');
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
    <?php $page_title = 'TikTok Channel - KNA Interpharma'; require_once __DIR__ . '/partials/site-head.php'; ?>
    <style>
        .tiktok-gallery {
            display: grid;
            grid-template-columns: minmax(0, 340px);
            gap: 24px;
            justify-content: center;
        }
        @media (min-width: 640px)  { .tiktok-gallery { grid-template-columns: repeat(2, minmax(0, 340px)); } }
        @media (min-width: 1024px) { .tiktok-gallery { grid-template-columns: repeat(3, minmax(0, 340px)); } }
        @media (min-width: 1280px) { .tiktok-gallery { grid-template-columns: repeat(4, minmax(0, 320px)); gap: 28px; } }
        .tiktok-tile {
            overflow: hidden;
            border-radius: 20px;
            background: #fff;
            border: 1px solid #e2e8f0;
            width: 100%;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .tiktok-tile:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 32px -12px rgba(15, 23, 42, .18);
        }
        .tiktok-tile iframe { display: block; width: 100%; height: 100%; border: 0; }
    </style>
</head>
<body class="bg-white text-slate-900">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <!-- Hero -->
    <section class="relative overflow-hidden">
        <img src="/uploads/website/bg-contact-us/ChatGPT%20Image%2017%20มิ.ย.%202569%2022_04_11.png" alt="About Us Banner" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>
        <div class="relative mx-auto max-w-7xl px-4 pb-20 pt-16 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white/90 backdrop-blur">
                    <i class="fa-brands fa-tiktok"></i> TikTok Channel
                </div>
                <h1 class="mt-5 text-4xl font-extrabold leading-tight text-white sm:text-5xl">
                    คลิป TikTok<br class="hidden sm:block">K.N.A. Inter pharma
                </h1>

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

    <!-- Banner ลิงก์ไปหน้า Learning -->
    <div class="mx-auto max-w-7xl px-4 pt-10 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-3xl border border-indigo-100 bg-gradient-to-r from-indigo-50 to-slate-50">
            <div class="flex flex-col items-center justify-between gap-7 px-9 py-9 sm:flex-row sm:px-12 sm:py-10">
                <div class="flex items-center gap-6">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700 text-2xl">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    <div>
                        <div class="text-lg font-extrabold text-slate-900">วิดีโอการเรียนการสอนเฉพาะแพทย์</div>
                        <p class="mt-1.5 text-base text-slate-500">องค์ความรู้ทางวิชาการ เฉพาะบุคลากรทางการแพทย์ที่ผ่านการยืนยัน</p>
                    </div>
                </div>
                <a href="/video-learning.php"
                   class="inline-flex shrink-0 items-center gap-2 rounded-2xl bg-indigo-700 px-7 py-3.5 text-base font-semibold text-white shadow-sm transition hover:bg-indigo-800">
                    เข้าสู่ห้องเรียน <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </div>

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
                        $isDirect = is_direct_video($videoUrl);
                        ?>
                        <article class="tiktok-tile">
                            <div class="<?php echo ($tiktokId !== null || $isDirect) ? 'aspect-[9/16]' : 'aspect-video'; ?> bg-slate-100">
                                <?php if ($isDirect): ?>
                                    <!-- กรณีเป็นไฟล์วิดีโอตรง จะสามารถ Autoplay และ Loop ได้ -->
                                    <video src="<?php echo h($videoUrl); ?>" 
                                           autoplay="autoplay" loop="loop" muted="muted" playsinline="playsinline" 
                                           class="h-full w-full object-cover">
                                    </video>
                                <?php elseif ($tiktokId !== null): ?>
                                    <iframe
                                        class="tiktok-autoplay-frame"
                                        data-src="https://www.tiktok.com/player/v1/<?php echo h($tiktokId); ?>?music_info=0&description=0&rel=0&autoplay=1&loop=1&muted=1"
                                        title="<?php echo h($video['title'] ?? 'TikTok video'); ?>"
                                        allow="fullscreen; autoplay; encrypted-media; picture-in-picture"
                                        allowfullscreen></iframe>
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
                            <?php $videoTitle = trim((string) ($video['title'] ?? '')); ?>
                            <?php if ($videoTitle !== '' && !preg_match('#^https?://#i', $videoTitle)): ?>
                                <div class="border-t border-slate-100 px-4 py-3">
                                    <p class="text-sm font-semibold text-slate-800 line-clamp-2"><?php echo h($videoTitle); ?></p>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>

    <script>
        (function () {
            var frames = document.querySelectorAll('.tiktok-autoplay-frame[data-src]');
            if (!frames.length) {
                return;
            }

            function mount(frame) {
                frame.src = frame.dataset.src;
                frame.removeAttribute('data-src');
            }

            if (!('IntersectionObserver' in window)) {
                frames.forEach(mount);
                return;
            }

            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        mount(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { rootMargin: '200px 0px' });

            frames.forEach(function (frame) { observer.observe(frame); });
        })();
    </script>
</body>
</html>
