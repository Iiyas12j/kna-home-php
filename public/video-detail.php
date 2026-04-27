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

function video_clean_text(?string $text): string
{
    $text = str_replace(["\r\n", "\r"], "\n", (string) $text);
    $text = trim(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));

    return $text;
}

function video_summary(array $video): string
{
    $detailSummary = video_clean_text($video['detail_summary'] ?? '');
    if ($detailSummary !== '') {
        $summary = preg_split("/\n+/", $detailSummary)[0] ?? $detailSummary;
        $summary = trim($summary);
        if (mb_strlen($summary) > 220) {
            $summary = rtrim(mb_substr($summary, 0, 219)) . '…';
        }

        return $summary;
    }

    $description = video_clean_text($video['description'] ?? '');
    if ($description !== '') {
        $summary = preg_split("/\n+/", $description)[0] ?? $description;
        $summary = trim($summary);
        if (mb_strlen($summary) > 220) {
            $summary = rtrim(mb_substr($summary, 0, 219)) . '…';
        }

        return $summary;
    }

    $title = trim((string) ($video['title'] ?? ''));
    if ($title !== '') {
        return 'วิดีโอความรู้ในหัวข้อ ' . $title . ' พร้อมสำหรับการรับชมแบบเต็มและใช้งานต่อในบริบทการเรียนรู้ของ KNA Interpharma';
    }

    return 'วิดีโอความรู้สำหรับการรับชมแบบเต็ม พร้อมโครงสร้างการนำเสนอที่ชัดเจนและน่าเชื่อถือ';
}

function video_pillars(array $video): array
{
    $platform = strtolower(trim((string) ($video['platform'] ?? 'video')));
    $access = normalize_video_access_level($video['access_level'] ?? 'public');
    $items = [];

    $items[] = $platform === 'youtube' ? 'เหมาะกับการรับชมเนื้อหาแบบยาวและกดดูต่อได้ทันที' : 'นำเสนอเนื้อหาในรูปแบบวิดีโอที่เข้าถึงง่ายและกดเล่นได้ทันที';
    $items[] = $access === 'doctor'
        ? 'จำกัดสิทธิ์สำหรับแพทย์ เพื่อคงบริบทการใช้งานเฉพาะทาง'
        : ($access === 'member'
            ? 'ออกแบบสำหรับสมาชิกที่เข้าสู่ระบบแล้ว'
            : 'เนื้อหาสาธารณะที่สามารถใช้เป็นจุดเริ่มต้นในการทำความรู้จักแบรนด์');

    if ($platform === 'youtube') {
        $items[] = 'มีลิงก์ YouTube ตรงสำหรับการเปิดดูแบบเต็มหน้าหรือแชร์ต่อได้สะดวก';
    } elseif ($platform === 'tiktok') {
        $items[] = 'รองรับการรับชมผ่าน TikTok embed ภายในหน้าโดยไม่ต้องออกนอกเว็บไซต์';
    } else {
        $items[] = 'สามารถต่อยอดเป็นฐานความรู้และเอกสารประกอบการนำเสนอได้';
    }

    return $items;
}

function video_platform_label(?string $platform): string
{
    $platform = strtolower(trim((string) $platform));

    return match ($platform) {
        'youtube' => 'YouTube',
        'tiktok' => 'TikTok',
        'facebook' => 'Facebook',
        default => strtoupper($platform !== '' ? $platform : 'VIDEO'),
    };
}

$dbReady = $pdo instanceof PDO;
$dbError = '';
$video = null;
$member = current_member();
$id = (int) ($_GET['id'] ?? 0);
$loginRedirect = '/login.php?redirect=' . urlencode('/video-detail.php?id=' . $id);
$siteHeaderActive = 'vdo';
$relatedVideos = [];

if ($dbReady && $id > 0) {
    try {
        ensure_videos_access_level_column($pdo);
        ensure_videos_detail_columns($pdo);

        $stmt = $pdo->prepare('SELECT * FROM videos WHERE id = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$id]);
        $candidate = $stmt->fetch();

        if ($candidate && member_can_access_video($candidate['access_level'] ?? 'public')) {
            $video = $candidate;
        } elseif ($candidate) {
            $video = ['access_denied' => true] + $candidate;
        }

        if ($video && empty($video['access_denied'])) {
            $relatedStmt = $pdo->prepare(
                'SELECT *
                 FROM videos
                 WHERE is_active = 1
                   AND id <> ?
                 ORDER BY sort_order ASC, id DESC
                 LIMIT 6'
            );
            $relatedStmt->execute([$id]);

            foreach ($relatedStmt->fetchAll() as $row) {
                if (member_can_access_video($row['access_level'] ?? 'public')) {
                    $relatedVideos[] = $row;
                }
                if (count($relatedVideos) === 3) {
                    break;
                }
            }
        }
    } catch (Exception $e) {
        $dbError = $e->getMessage();
    }
}

$videoUrl = trim((string) ($video['video_url'] ?? ''));
$thumbnailUrl = video_thumbnail_url($video['thumbnail'] ?? '');
$youtubeUrl = youtube_embed_url($videoUrl);
$tiktokId = tiktok_video_id($videoUrl);
$accessLevel = normalize_video_access_level($video['access_level'] ?? 'public');
$platformLabel = video_platform_label($video['platform'] ?? 'video');
$summary = video_summary($video ?? []);
$pillars = video_pillars($video ?? []);
$detailContent = video_clean_text($video['detail_content'] ?? '');
$fallbackDescription = video_clean_text($video['description'] ?? '');
$hasDescription = $detailContent !== '' || $fallbackDescription !== '';
$contentBody = $detailContent !== '' ? $detailContent : $fallbackDescription;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $video && empty($video['access_denied']) ? h(($video['title'] ?? 'Video') . ' - KNA Interpharma') : 'Video Detail'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }

        .video-detail-hero {
            background:
                radial-gradient(circle at 12% 18%, rgba(255, 255, 255, 0.92) 0%, rgba(255, 255, 255, 0.48) 20%, rgba(255, 255, 255, 0) 42%),
                linear-gradient(135deg, rgba(236, 244, 255, 0.98) 0%, rgba(229, 236, 255, 0.96) 42%, rgba(218, 230, 255, 0.94) 100%);
        }

        .video-detail-hero::after {
            content: '';
            position: absolute;
            right: -120px;
            top: -80px;
            width: 360px;
            height: 360px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.18) 0%, rgba(79, 70, 229, 0.05) 45%, rgba(79, 70, 229, 0) 72%);
            pointer-events: none;
        }

        .clinical-grid {
            background-image:
                linear-gradient(rgba(99, 102, 241, 0.08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99, 102, 241, 0.08) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.95) 0%, rgba(0, 0, 0, 0.2) 100%);
            -webkit-mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.95) 0%, rgba(0, 0, 0, 0.2) 100%);
        }

        .related-video__description {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            line-clamp: 3;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <main class="px-4 pb-16 pt-8 md:px-6 lg:px-8 lg:pb-20">
        <div class="mx-auto max-w-7xl">
            <div class="mb-5 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                <a href="/index.php" class="hover:text-indigo-600">หน้าแรก</a>
                <span>/</span>
                <a href="/video-tiktok.php" class="hover:text-indigo-600">Video Channel</a>
                <span>/</span>
                <span class="text-slate-700"><?php echo h($video['title'] ?? 'รายละเอียดวิดีโอ'); ?></span>
            </div>

            <?php if ($dbError !== ''): ?>
                <div class="rounded-2xl border border-yellow-300 bg-yellow-50 px-5 py-4 text-yellow-900">
                    ไม่สามารถโหลดข้อมูลวิดีโอได้: <?php echo h($dbError); ?>
                </div>
            <?php elseif (!$video): ?>
                <div class="rounded-[32px] border border-slate-200 bg-white px-8 py-16 text-center shadow-sm">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <i class="fa-solid fa-circle-play text-3xl"></i>
                    </div>
                    <h1 class="mt-6 text-3xl font-bold text-slate-900">ไม่พบวิดีโอที่ต้องการ</h1>
                    <p class="mt-4 text-slate-600">รายการนี้อาจถูกปิดการแสดงผลหรือไม่มีอยู่ในระบบแล้ว</p>
                    <a href="/video-tiktok.php" class="mt-8 inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">
                        <i class="fa-solid fa-arrow-left"></i> กลับไปหน้า Video Channel
                    </a>
                </div>
            <?php elseif (!empty($video['access_denied'])): ?>
                <div class="rounded-[32px] border border-amber-200 bg-white px-8 py-16 text-center shadow-sm">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-amber-50 text-amber-500">
                        <i class="fa-solid fa-lock text-3xl"></i>
                    </div>
                    <h1 class="mt-6 text-3xl font-bold text-slate-900">วิดีโอนี้จำกัดสิทธิ์การเข้าถึง</h1>
                    <p class="mx-auto mt-4 max-w-2xl text-slate-600">
                        ต้องเข้าสู่ระบบด้วยสิทธิ์ที่เหมาะสมก่อนจึงจะสามารถดูรายละเอียดของวิดีโอนี้ได้
                    </p>
                    <div class="mt-8 flex flex-wrap justify-center gap-3">
                        <a href="<?php echo h($loginRedirect); ?>" class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">
                            <i class="fa-solid fa-right-to-bracket"></i> เข้าสู่ระบบ
                        </a>
                        <a href="/video-tiktok.php#learning-videos" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:border-indigo-300 hover:text-indigo-700">
                            กลับไปส่วนวิดีโอความรู้
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <section class="video-detail-hero relative isolate overflow-hidden rounded-[36px] border border-slate-200/70">
                    <div class="clinical-grid absolute inset-y-0 right-0 hidden w-[38%] opacity-60 lg:block"></div>

                    <div class="relative mx-auto grid max-w-7xl gap-10 px-6 py-10 md:px-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:px-14 lg:py-14">
                        <div class="relative z-10">
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center rounded-full bg-white/85 px-4 py-2 text-sm font-semibold text-indigo-700 shadow-sm ring-1 ring-indigo-100">
                                    <?php echo h($platformLabel); ?>
                                </span>
                                <span class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold <?php echo $accessLevel === 'doctor' ? 'bg-rose-100 text-rose-700' : ($accessLevel === 'member' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700'); ?>">
                                    <?php echo h(video_access_label($accessLevel)); ?>
                                </span>
                                <?php if ($member !== null): ?>
                                    <span class="inline-flex items-center rounded-full bg-white/85 px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm ring-1 ring-slate-200">
                                        สิทธิ์ปัจจุบัน <?php echo h(member_role_label($member['role'] ?? 'member')); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h1 class="mt-6 text-4xl font-extrabold leading-tight text-slate-900 md:text-5xl lg:text-6xl">
                                <?php echo h($video['title'] ?? ''); ?>
                            </h1>
                            <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600 md:text-xl">
                                <?php echo h($summary); ?>
                            </p>

                            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                                <div class="rounded-3xl bg-white/90 px-5 py-4 shadow-sm ring-1 ring-slate-200/80 backdrop-blur-sm">
                                    <div class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Platform</div>
                                    <div class="mt-2 text-lg font-bold text-slate-900"><?php echo h($platformLabel); ?></div>
                                </div>
                                <div class="rounded-3xl bg-white/90 px-5 py-4 shadow-sm ring-1 ring-slate-200/80 backdrop-blur-sm">
                                    <div class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Access</div>
                                    <div class="mt-2 text-lg font-bold text-slate-900"><?php echo h(video_access_label($accessLevel)); ?></div>
                                </div>
                                <div class="rounded-3xl bg-white/90 px-5 py-4 shadow-sm ring-1 ring-slate-200/80 backdrop-blur-sm">
                                    <div class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Viewing</div>
                                    <div class="mt-2 text-lg font-bold text-slate-900"><?php echo $youtubeUrl !== null ? 'รายละเอียดพร้อมลิงก์ YouTube' : ($tiktokId !== null ? 'เล่นผ่าน TikTok Embed' : 'ลิงก์ภายนอก'); ?></div>
                                </div>
                            </div>

                            <div class="mt-8 flex flex-wrap gap-4">
                                <?php if ($videoUrl !== ''): ?>
                                    <a href="<?php echo h($videoUrl); ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-700 to-violet-600 px-6 py-3 font-semibold text-white shadow-lg shadow-indigo-600/20 transition hover:translate-y-[-1px] hover:shadow-xl">
                                        <?php echo $youtubeUrl !== null ? 'ดูบน YouTube' : 'เปิดวิดีโอ'; ?>
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="/video-tiktok.php#learning-videos" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-700 transition hover:border-indigo-300 hover:text-indigo-700">
                                    กลับไปส่วนวิดีโอความรู้
                                    <i class="fa-solid fa-arrow-left"></i>
                                </a>
                            </div>
                        </div>

                        <div class="relative z-10">
                            <div class="overflow-hidden rounded-[34px] bg-white/80 p-4 shadow-[0_24px_60px_rgba(79,70,229,0.14)] ring-1 ring-white/80 backdrop-blur-sm">
                                <?php if ($youtubeUrl !== null): ?>
                                    <div class="aspect-video overflow-hidden rounded-[26px] bg-slate-100">
                                        <iframe
                                            src="<?php echo h($youtubeUrl); ?>"
                                            title="<?php echo h($video['title'] ?? 'Video'); ?>"
                                            class="h-full w-full"
                                            loading="lazy"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            referrerpolicy="strict-origin-when-cross-origin"
                                            allowfullscreen
                                        ></iframe>
                                    </div>
                                <?php elseif ($tiktokId !== null): ?>
                                    <div class="mx-auto aspect-[9/16] max-w-[420px] overflow-hidden rounded-[26px] bg-slate-100">
                                        <blockquote class="tiktok-embed" cite="<?php echo h($videoUrl); ?>" data-video-id="<?php echo h($tiktokId); ?>" style="max-width:100%;min-width:100%;height:100%;margin:0;">
                                            <section></section>
                                        </blockquote>
                                    </div>
                                <?php elseif ($thumbnailUrl !== ''): ?>
                                    <img src="<?php echo h($thumbnailUrl); ?>" alt="<?php echo h($video['title'] ?? 'Video'); ?>" class="h-[320px] w-full rounded-[26px] object-cover md:h-[420px]">
                                <?php else: ?>
                                    <div class="flex h-[320px] items-center justify-center rounded-[26px] bg-gradient-to-br from-indigo-800 via-indigo-600 to-cyan-500 text-white md:h-[420px]">
                                        <i class="fa-solid fa-circle-play text-8xl"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mx-auto max-w-7xl pt-10">
                    <div class="mb-8">
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-indigo-600">Highlights</p>
                        <h2 class="mt-2 text-3xl font-bold text-slate-900 md:text-4xl">จุดเด่นของการนำเสนอวิดีโอนี้</h2>
                    </div>

                    <div class="grid gap-5 md:grid-cols-3">
                        <?php foreach ($pillars as $pillar): ?>
                            <div class="rounded-[28px] bg-white p-6 shadow-[0_16px_40px_rgba(15,23,42,0.08)] ring-1 ring-slate-200/70">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                                    <i class="fa-solid fa-star text-lg"></i>
                                </div>
                                <div class="mt-5 text-lg font-semibold leading-8 text-slate-800">
                                    <?php echo h($pillar); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="mx-auto grid max-w-7xl gap-8 pt-12 lg:grid-cols-[1.2fr_0.8fr]">
                    <div class="rounded-[32px] bg-white p-7 shadow-[0_16px_40px_rgba(15,23,42,0.08)] ring-1 ring-slate-200/70 md:p-10">
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-indigo-600">Content Overview</p>
                        <h2 class="mt-2 text-3xl font-bold text-slate-900">รายละเอียดและบริบทของวิดีโอ</h2>

                        <div class="mt-8 space-y-6 text-lg leading-8 text-slate-600">
                            <?php if ($hasDescription): ?>
                                <div class="whitespace-pre-line"><?php echo h($contentBody); ?></div>
                            <?php else: ?>
                                <p>วิดีโอนี้ถูกจัดวางให้อยู่ในคลังความรู้ของ KNA Interpharma เพื่อใช้เป็นเนื้อหาประกอบการเรียนรู้และการสื่อสารกับผู้ชมตามสิทธิ์ที่กำหนด</p>
                                <p>หากต้องการรับชมต่อแบบเต็ม สามารถกดปุ่มด้านข้างเพื่อเปิดแหล่งวิดีโอต้นทาง และใช้หน้ารายละเอียดนี้เป็นจุดอ้างอิงก่อนเข้าสู่การรับชมจริง</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-[32px] bg-slate-900 p-7 text-white shadow-[0_18px_46px_rgba(15,23,42,0.16)] md:p-8">
                            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-cyan-200">Trust Layer</p>
                            <h2 class="mt-2 text-3xl font-bold">เหตุผลที่หน้า detail นี้ดูน่าเชื่อถือขึ้น</h2>
                            <div class="mt-6 space-y-4 text-base leading-7 text-slate-200">
                                <p>ใช้โครงสร้างแบบพรีเมียมที่ให้ทั้งภาพรวมของวิดีโอ บริบทการเข้าถึง และทางไปต่อในหน้าเดียว</p>
                                <p>แยกข้อมูลเป็น hero, highlights, content และ CTA เพื่อให้ผู้ชมเข้าใจวิดีโอได้เร็วโดยไม่รู้สึกว่าหน้าเป็นเพียงลิงก์เปล่า</p>
                            </div>
                        </div>

                        <div class="rounded-[32px] bg-white p-7 shadow-[0_16px_40px_rgba(15,23,42,0.08)] ring-1 ring-slate-200/70 md:p-8">
                            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-indigo-600">Next Step</p>
                            <h2 class="mt-2 text-3xl font-bold text-slate-900">ไปต่อจากวิดีโอนี้</h2>
                            <div class="mt-6 space-y-4 text-base leading-7 text-slate-600">
                                <p>ถ้าต้องการเรียนรู้ต่อ ให้กดเปิดวิดีโอจากแหล่งต้นทางเพื่อรับชมแบบเต็ม</p>
                                <p>ถ้าต้องการดูวิดีโอในหัวข้อใกล้เคียง ให้กลับไปหน้า Video Channel เพื่อเลือกจากคลังวิดีโอชุดอื่น</p>
                            </div>
                            <div class="mt-6 flex flex-wrap gap-3">
                                <?php if ($videoUrl !== ''): ?>
                                    <a href="<?php echo h($videoUrl); ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-2xl bg-red-600 px-5 py-3 font-semibold text-white hover:bg-red-700">
                                        <i class="fa-brands fa-youtube"></i>
                                        <?php echo $youtubeUrl !== null ? 'ดูบน YouTube' : 'เปิดวิดีโอ'; ?>
                                    </a>
                                <?php endif; ?>
                                <a href="/video-tiktok.php#learning-videos" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:border-indigo-300 hover:text-indigo-700">
                                    ดูวิดีโออื่น
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <?php if (!empty($relatedVideos)): ?>
                    <section class="mx-auto max-w-7xl pt-12">
                        <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-indigo-600">More Videos</p>
                                <h2 class="mt-2 text-3xl font-bold text-slate-900 md:text-4xl">วิดีโออื่นที่น่าสนใจ</h2>
                            </div>
                            <a href="/video-tiktok.php#learning-videos" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">กลับไปคลังวิดีโอ</a>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                            <?php foreach ($relatedVideos as $related): ?>
                                <?php
                                $relatedThumb = video_thumbnail_url($related['thumbnail'] ?? '');
                                $relatedSummary = video_summary($related);
                                $relatedAccess = normalize_video_access_level($related['access_level'] ?? 'public');
                                ?>
                                <a href="/video-detail.php?id=<?php echo (int) ($related['id'] ?? 0); ?>" class="group block overflow-hidden rounded-[28px] bg-white shadow-[0_16px_40px_rgba(15,23,42,0.08)] ring-1 ring-slate-200/70 transition hover:-translate-y-1 hover:shadow-[0_24px_54px_rgba(15,23,42,0.12)]">
                                    <div class="aspect-video overflow-hidden bg-slate-100">
                                        <?php if ($relatedThumb !== ''): ?>
                                            <img src="<?php echo h($relatedThumb); ?>" alt="<?php echo h($related['title'] ?? 'Video'); ?>" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                        <?php else: ?>
                                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-indigo-800 via-indigo-600 to-cyan-500 text-white">
                                                <i class="fa-solid fa-circle-play text-5xl"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="p-6">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="text-xs font-semibold uppercase tracking-wide text-indigo-600"><?php echo h(video_platform_label($related['platform'] ?? 'video')); ?></div>
                                            <span class="rounded-full px-3 py-1 text-xs font-semibold <?php echo $relatedAccess === 'doctor' ? 'bg-rose-100 text-rose-700' : ($relatedAccess === 'member' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700'); ?>">
                                                <?php echo h(video_access_label($relatedAccess)); ?>
                                            </span>
                                        </div>
                                        <h3 class="mt-3 text-xl font-bold text-slate-900"><?php echo h($related['title'] ?? ''); ?></h3>
                                        <p class="related-video__description mt-3 text-sm leading-6 text-slate-600"><?php echo h($relatedSummary); ?></p>
                                        <span class="mt-5 inline-flex items-center gap-2 font-semibold text-indigo-700 transition group-hover:text-indigo-900">
                                            ดูรายละเอียดวิดีโอ
                                            <i class="fa-solid fa-arrow-right text-sm"></i>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
    <?php if ($tiktokId !== null && !empty($video) && empty($video['access_denied'])): ?>
        <script async src="https://www.tiktok.com/embed.js"></script>
    <?php endif; ?>
</body>
</html>
