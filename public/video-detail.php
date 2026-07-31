<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

function vd_thumbnail_url(?string $thumbnail): string
{
    $thumbnail = trim((string) $thumbnail);
    if ($thumbnail === '') return '';
    if (preg_match('#^https?://#i', $thumbnail) || str_starts_with($thumbnail, '/')) return $thumbnail;
    return '/uploads/videos/' . rawurlencode($thumbnail);
}

function vd_tiktok_id(?string $url): ?string
{
    $url = trim((string) $url);
    if ($url === '') return null;
    if (preg_match('#/video/(\d+)#', $url, $matches)) return $matches[1];
    return null;
}

function vd_youtube_embed(?string $url): ?string
{
    $url = trim((string) $url);
    if ($url === '') return null;
    if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})#', $url, $m))
        return 'https://www.youtube.com/embed/' . $m[1];
    if (preg_match('#youtube\.com/embed/([A-Za-z0-9_-]{6,})#', $url, $m))
        return 'https://www.youtube.com/embed/' . $m[1];
    return null;
}

function vd_platform_label(?string $platform): string
{
    return match (strtolower(trim((string) $platform))) {
        'youtube'  => 'YouTube',
        'tiktok'   => 'TikTok',
        'facebook' => 'Facebook',
        default    => 'VIDEO',
    };
}

function vd_clean(?string $text): string
{
    $text = str_replace(["\r\n", "\r"], "\n", (string) $text);
    return trim(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
}

// ── Data ──────────────────────────────────────────────────────────────────────
$dbReady  = $pdo instanceof PDO;
$dbError  = '';
$video    = null;
$member   = current_member();
$isDoctor = member_is_doctor();
$id       = (int) ($_GET['id'] ?? 0);

$loginRedirect = '/login.php?redirect=' . urlencode('/video-detail.php?id=' . $id);
$backUrl       = '/video-learning.php';
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
            $video = ['_access_denied' => true] + $candidate;
        }

        if ($video && empty($video['_access_denied'])) {
            $relatedStmt = $pdo->prepare(
                "SELECT * FROM videos
                 WHERE is_active = 1
                   AND id <> ?
                   AND (platform <> 'tiktok' OR access_level <> 'public')
                 ORDER BY sort_order ASC, id DESC
                 LIMIT 9"
            );
            $relatedStmt->execute([$id]);
            foreach ($relatedStmt->fetchAll() as $row) {
                if (member_can_access_video($row['access_level'] ?? 'public')) {
                    $relatedVideos[] = $row;
                }
                if (count($relatedVideos) === 3) break;
            }
        }
    } catch (Exception $e) {
        $dbError = $e->getMessage();
    }
}

$videoUrl     = trim((string) ($video['video_url'] ?? ''));
$thumbUrl     = vd_thumbnail_url($video['thumbnail'] ?? '');
$youtubeEmbed = vd_youtube_embed($videoUrl);
$tiktokId     = vd_tiktok_id($videoUrl);
$accessLevel  = normalize_video_access_level($video['access_level'] ?? 'public');
$platform     = vd_platform_label($video['platform'] ?? '');
$description  = vd_clean($video['description'] ?? '');
$detailSum    = vd_clean($video['detail_summary'] ?? '');
$detailBody   = vd_clean($video['detail_content'] ?? '');
$bodyText     = $detailBody !== '' ? $detailBody : ($description !== '' ? $description : '');

$siteHeaderActive = 'vdo';
$page_title = ($video && empty($video['_access_denied']))
    ? ($video['title'] ?? 'วิดีโอ') . ' - KNA Interpharma'
    : 'วิดีโอ - KNA Interpharma';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <?php require_once __DIR__ . '/partials/site-head.php'; ?>
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }
        .line-clamp-3 {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <div class="mx-auto max-w-7xl px-4 pb-20 pt-8 sm:px-6 lg:px-8">

        <!-- Breadcrumb -->
        <nav class="mb-8 flex items-center gap-2 text-sm text-slate-500">
            <a href="/index.php" class="hover:text-indigo-600">หน้าแรก</a>
            <i class="fa-solid fa-chevron-right text-xs text-slate-300"></i>
            <a href="/video-learning.php" class="hover:text-indigo-600">วิดีโอการเรียนการสอน</a>
            <?php if ($video && empty($video['_access_denied'])): ?>
                <i class="fa-solid fa-chevron-right text-xs text-slate-300"></i>
                <span class="line-clamp-2 max-w-xs text-slate-700"><?php echo h($video['title'] ?? ''); ?></span>
            <?php endif; ?>
        </nav>


        <?php if ($dbError !== ''): ?>
        <!-- ── Error ── -->
        <div class="rounded-3xl border border-yellow-200 bg-yellow-50 px-6 py-5 text-sm text-yellow-900">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i>ไม่สามารถโหลดข้อมูลวิดีโอได้
        </div>


        <?php elseif (!$video): ?>
        <!-- ── Not found ── -->
        <div class="rounded-3xl border border-slate-200 bg-white px-8 py-20 text-center shadow-sm">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-slate-100 text-slate-400">
                <i class="fa-solid fa-circle-play text-3xl"></i>
            </div>
            <h1 class="mt-6 text-2xl font-extrabold text-slate-900">ไม่พบวิดีโอที่ต้องการ</h1>
            <p class="mt-3 text-slate-500">รายการนี้อาจถูกปิดการแสดงผลหรือไม่มีอยู่ในระบบแล้ว</p>
            <a href="<?php echo h($backUrl); ?>"
               class="mt-8 inline-flex items-center gap-2 rounded-2xl bg-indigo-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-indigo-800">
                <i class="fa-solid fa-arrow-left"></i> กลับไปคลังวิดีโอ
            </a>
        </div>


        <?php elseif (!empty($video['_access_denied'])): ?>
        <!-- ── Access denied ── -->
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="h-1 bg-gradient-to-r from-indigo-600 to-violet-600"></div>
            <div class="px-8 py-16 text-center sm:px-14 md:py-24">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl border border-indigo-100 bg-indigo-50">
                    <i class="fa-solid fa-lock text-3xl text-indigo-600"></i>
                </div>
                <div class="mt-5 inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-indigo-700">
                    Restricted Access
                </div>
                <h1 class="mt-5 text-2xl font-extrabold text-slate-900">วิดีโอนี้จำกัดสิทธิ์การเข้าถึง</h1>
                <p class="mx-auto mt-3 max-w-md text-slate-500">
                    กรุณาเข้าสู่ระบบด้วยบัญชีแพทย์ที่ผ่านการยืนยันแล้วเพื่อรับชมวิดีโอนี้
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="<?php echo h($loginRedirect); ?>"
                       class="inline-flex items-center gap-2 rounded-2xl bg-indigo-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-indigo-800">
                        <i class="fa-solid fa-right-to-bracket"></i> เข้าสู่ระบบ
                    </a>
                    <a href="<?php echo h($backUrl); ?>"
                       class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-indigo-300 hover:text-indigo-700">
                        <i class="fa-solid fa-arrow-left"></i> กลับไปคลังวิดีโอ
                    </a>
                </div>
            </div>
        </div>


        <?php else: ?>
        <!-- ══ Main Content ══════════════════════════════════════════════════════ -->

        <!-- ── Video Player (full width) ── -->
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-black shadow-sm">
            <?php if ($youtubeEmbed !== null): ?>
                <div class="aspect-video">
                    <iframe src="<?php echo h($youtubeEmbed); ?>"
                        title="<?php echo h($video['title'] ?? 'Video'); ?>"
                        class="h-full w-full"
                        loading="lazy"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
                </div>
            <?php elseif ($tiktokId !== null): ?>
                <div class="flex justify-center bg-black py-6">
                    <div class="w-full max-w-md" style="aspect-ratio:9/16;">
                        <blockquote class="tiktok-embed"
                            cite="<?php echo h($videoUrl); ?>"
                            data-video-id="<?php echo h($tiktokId); ?>"
                            style="max-width:100%;min-width:100%;height:100%;margin:0;">
                            <section></section>
                        </blockquote>
                    </div>
                </div>
            <?php elseif ($thumbUrl !== ''): ?>
                <div class="aspect-video">
                    <img src="<?php echo h($thumbUrl); ?>"
                         alt="<?php echo h($video['title'] ?? 'Video'); ?>"
                         class="h-full w-full object-cover">
                </div>
            <?php else: ?>
                <div class="flex aspect-video items-center justify-center bg-gradient-to-br from-indigo-900 via-indigo-700 to-indigo-500 text-white">
                    <i class="fa-solid fa-circle-play text-9xl opacity-50"></i>
                </div>
            <?php endif; ?>

            <?php if ($videoUrl !== ''): ?>
                <div class="border-t border-white/10 bg-black px-5 py-3">
                    <a href="<?php echo h($videoUrl); ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 text-sm font-semibold text-white/70 transition hover:text-white">
                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                        เปิดในแอป <?php echo h($platform); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Info Panel (below video) ── -->
        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_auto]">

            <!-- Left: Title + Summary -->
            <div class="flex flex-col gap-4">
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold uppercase tracking-widest text-slate-600 shadow-sm">
                        <?php echo h($platform); ?>
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold uppercase tracking-widest shadow-sm
                        <?php echo $accessLevel === 'doctor'
                            ? 'bg-indigo-50 border border-indigo-200 text-indigo-700'
                            : ($accessLevel === 'member'
                                ? 'bg-emerald-50 border border-emerald-200 text-emerald-700'
                                : 'bg-slate-50 border border-slate-200 text-slate-600'); ?>">
                        <i class="fa-solid <?php echo $accessLevel === 'doctor' ? 'fa-user-doctor' : ($accessLevel === 'member' ? 'fa-users' : 'fa-globe'); ?> text-[10px]"></i>
                        <?php echo h(video_access_label($accessLevel)); ?>
                    </span>
                    <?php if ($member !== null): ?>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-500 shadow-sm">
                            <i class="fa-solid fa-circle-check text-emerald-500 text-[10px]"></i>
                            <?php echo h(member_role_label($member['role'] ?? 'member')); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <h1 class="text-2xl font-extrabold leading-snug text-slate-900 sm:text-3xl">
                    <?php echo h($video['title'] ?? ''); ?>
                </h1>

                <?php if ($detailSum !== '' || $description !== ''): ?>
                    <p class="text-base leading-7 text-slate-600">
                        <?php echo h($detailSum !== '' ? $detailSum : $description); ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Right: CTA -->
            <div class="flex shrink-0 flex-row items-start gap-3 lg:flex-col lg:items-end">
                <?php if ($videoUrl !== ''): ?>
                    <a href="<?php echo h($videoUrl); ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 rounded-2xl bg-indigo-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800">
                        <i class="fa-solid fa-play text-xs"></i> เปิดดูวิดีโอ
                    </a>
                <?php endif; ?>
                <a href="<?php echo h($backUrl); ?>"
                   class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-indigo-200 hover:text-indigo-700">
                    <i class="fa-solid fa-arrow-left text-xs"></i> กลับ
                </a>
            </div>
        </div>


        <!-- ── Description ── -->
        <?php if ($bodyText !== ''): ?>
        <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-7 py-5 sm:px-8">
                <h2 class="text-lg font-extrabold text-slate-900">รายละเอียดวิดีโอ</h2>
            </div>
            <div class="px-7 py-6 sm:px-8">
                <div class="whitespace-pre-line text-base leading-8 text-slate-600">
                    <?php echo h($bodyText); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>


        <!-- ── Related Videos ── -->
        <?php if (!empty($relatedVideos)): ?>
        <div class="mt-12">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-xl font-extrabold text-slate-900">วิดีโออื่นที่น่าสนใจ</h2>
                <a href="<?php echo h($backUrl); ?>"
                   class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                    ดูทั้งหมด <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($relatedVideos as $rel): ?>
                    <?php
                    $relThumb  = vd_thumbnail_url($rel['thumbnail'] ?? '');
                    $relAccess = normalize_video_access_level($rel['access_level'] ?? 'public');
                    ?>
                    <a href="/video-detail.php?id=<?php echo (int) ($rel['id'] ?? 0); ?>"
                       class="group block overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg">
                        <div class="aspect-video overflow-hidden bg-slate-100">
                            <?php if ($relThumb !== ''): ?>
                                <img src="<?php echo h($relThumb); ?>"
                                     alt="<?php echo h($rel['title'] ?? 'Video'); ?>"
                                     class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            <?php else: ?>
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-indigo-900 via-indigo-700 to-indigo-500 text-white">
                                    <i class="fa-solid fa-circle-play text-5xl opacity-60"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-bold uppercase tracking-widest text-indigo-600">
                                    <?php echo h(vd_platform_label($rel['platform'] ?? '')); ?>
                                </span>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                    <?php echo $relAccess === 'doctor'
                                        ? 'bg-indigo-50 text-indigo-700'
                                        : ($relAccess === 'member'
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-slate-100 text-slate-600'); ?>">
                                    <?php echo h(video_access_label($relAccess)); ?>
                                </span>
                            </div>
                            <h3 class="mt-3 text-base font-bold leading-snug text-slate-900">
                                <?php echo h($rel['title'] ?? ''); ?>
                            </h3>
                            <?php if (!empty($rel['description'])): ?>
                                <p class="line-clamp-3 mt-2 text-sm leading-6 text-slate-500">
                                    <?php echo h(vd_clean($rel['description'])); ?>
                                </p>
                            <?php endif; ?>
                            <div class="mt-4 flex items-center gap-2 text-sm font-semibold text-indigo-700 transition-all group-hover:gap-3">
                                <span>ดูรายละเอียด</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
    <?php if ($tiktokId !== null && !empty($video) && empty($video['_access_denied'])): ?>
        <script async src="https://www.tiktok.com/embed.js"></script>
    <?php endif; ?>
</body>
</html>
