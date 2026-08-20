<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

function learning_thumbnail_url(?string $thumbnail): string
{
    $thumbnail = trim((string) $thumbnail);
    if ($thumbnail === '') return '';
    if (preg_match('#^https?://#i', $thumbnail) || str_starts_with($thumbnail, '/')) return $thumbnail;
    return '/uploads/videos/' . rawurlencode($thumbnail);
}

function learning_youtube_id(?string $url): ?string
{
    $url = trim((string) $url);
    if ($url === '') return null;
    if (preg_match('#youtu\.be/([A-Za-z0-9_-]{6,})#', $url, $m)) return $m[1];
    if (preg_match('#youtube\.com/(?:watch\?.*v=|embed/|shorts/|live/)([A-Za-z0-9_-]{6,})#', $url, $m)) return $m[1];
    return null;
}

function learning_embed_url(?string $url): ?string
{
    $url = trim((string) $url);
    if ($url === '') return null;
    $youtubeId = learning_youtube_id($url);
    if ($youtubeId !== null) {
        return 'https://www.youtube-nocookie.com/embed/' . rawurlencode($youtubeId) . '?rel=0';
    }
    if (preg_match('#facebook\.com/.+/videos/|fb\.watch/#i', $url)) {
        return 'https://www.facebook.com/plugins/video.php?show_text=false&href=' . rawurlencode($url);
    }
    return null;
}

$dbReady  = $pdo instanceof PDO;
$dbError  = '';
$member   = current_member();
$isDoctor = member_is_doctor();

$loginRedirect    = '/login.php?redirect='    . urlencode('/video-learning.php');
$registerRedirect = '/register.php?redirect=' . urlencode('/video-learning.php');

$learningVideos = [];

if ($dbReady && $member !== null) {
    try {
        ensure_videos_access_level_column($pdo);
        ensure_videos_detail_columns($pdo);

        $candidates = $pdo->query(
            "SELECT * FROM videos
             WHERE is_active = 1
               AND (platform <> 'tiktok' OR access_level <> 'public')
             ORDER BY sort_order ASC, id DESC"
        )->fetchAll();

        foreach ($candidates as $video) {
            if (member_can_access_video($video['access_level'] ?? 'public')) {
                $learningVideos[] = $video;
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
    <?php $page_title = 'วิดีโอการเรียนการสอน - KNA Interpharma'; require_once __DIR__ . '/partials/site-head.php'; ?>
    <style>
        .line-clamp-3 {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
        }
        .gate-card {
            overflow: hidden;
            border-radius: 28px;
            border: 1px solid #e2e8f0;
            background: #fff;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
        }
        .gate-top-bar {
            height: 4px;
            background: linear-gradient(90deg, #4338ca 0%, #7c3aed 50%, #4338ca 100%);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <!-- Hero -->
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.18),_transparent_32%),linear-gradient(135deg,#0f172a_0%,#1e3a5f_52%,#312e81_100%)]"></div>
        <div class="absolute right-[-80px] top-10 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="absolute left-[-60px] bottom-0 h-52 w-52 rounded-full bg-indigo-300/10 blur-3xl"></div>
        <div class="relative mx-auto max-w-7xl px-4 pb-20 pt-16 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-10 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white/90 backdrop-blur">
                        <i class="fa-solid fa-user-doctor"></i> Medical Learning
                    </div>
                    <h1 class="mt-5 text-4xl font-extrabold leading-tight text-white sm:text-5xl">
                        วิดีโอการเรียนการสอน<br class="hidden sm:block">เฉพาะแพทย์
                    </h1>
                    <p class="mt-5 text-base leading-8 text-slate-300">
                        องค์ความรู้ทางวิชาการและข้อมูลผลิตภัณฑ์เชิงลึก จัดทำโดย K.N.A. Inter pharma เฉพาะบุคลากรทางการแพทย์ที่ผ่านการยืนยันแล้ว
                    </p>
                </div>

                <!-- Auth status -->
                <?php if ($member !== null): ?>
                    <div class="shrink-0 rounded-3xl border border-white/20 bg-white/10 px-6 py-5 backdrop-blur">
                        <div class="text-xs font-bold uppercase tracking-widest text-white/60 mb-3">สถานะบัญชี</div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl <?php echo $isDoctor ? 'bg-emerald-400/20' : 'bg-amber-400/20'; ?>">
                                <i class="fa-solid <?php echo $isDoctor ? 'fa-circle-check text-emerald-400' : 'fa-circle-exclamation text-amber-400'; ?>"></i>
                            </div>
                            <div>
                                <div class="font-bold text-white text-sm"><?php echo h($member['name'] ?? $member['email']); ?></div>
                                <div class="text-xs text-white/60 mt-0.5"><?php echo $isDoctor ? 'สิทธิ์แพทย์ยืนยันแล้ว' : 'สมาชิกทั่วไป'; ?></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
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

    <!-- Content -->
    <section class="py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <?php if ($member === null): ?>
            <!-- ── Gate: ยังไม่ล็อกอิน ─────────────────────────────────────────── -->
            <div class="gate-card">
                <div class="gate-top-bar"></div>
                <div class="px-8 py-16 sm:px-14 md:py-24">
                    <div class="mx-auto max-w-xl text-center">
                        <div class="mx-auto mb-7 flex h-20 w-20 items-center justify-center rounded-3xl border border-indigo-100 bg-indigo-50">
                            <i class="fa-solid fa-user-doctor text-3xl text-indigo-600"></i>
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-indigo-700">
                            <i class="fa-solid fa-lock text-xs"></i> Restricted Access
                        </div>
                        <h2 class="mt-5 text-2xl font-extrabold text-slate-900 sm:text-3xl">
                            เนื้อหาสำหรับบุคลากรทางการแพทย์
                        </h2>
                        <p class="mt-4 text-base leading-7 text-slate-500">
                            วิดีโอการเรียนการสอนในส่วนนี้จัดทำขึ้นเฉพาะสำหรับแพทย์และบุคลากรทางการแพทย์ที่ผ่านการยืนยันตัวตนกับ KNA Interpharma เท่านั้น
                        </p>

                        <div class="mt-8 grid gap-3 text-left sm:grid-cols-2">
                            <div class="flex gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                                    <i class="fa-solid fa-user-plus text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-slate-900">สมัครบัญชีสมาชิก</div>
                                    <div class="mt-0.5 text-xs leading-5 text-slate-500">ลงทะเบียนด้วยอีเมลและข้อมูลส่วนตัว</div>
                                </div>
                            </div>
                            <div class="flex gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                                    <i class="fa-solid fa-id-card text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-slate-900">ยืนยันเลข อว.</div>
                                    <div class="mt-0.5 text-xs leading-5 text-slate-500">ใบอนุญาตประกอบวิชาชีพเวชกรรม</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-wrap justify-center gap-3">
                            <a href="<?php echo h($loginRedirect); ?>"
                               class="inline-flex items-center gap-2 rounded-2xl bg-indigo-700 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800">
                                <i class="fa-solid fa-right-to-bracket"></i> เข้าสู่ระบบ
                            </a>
                            <a href="<?php echo h($registerRedirect); ?>"
                               class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-indigo-300 hover:text-indigo-700">
                                <i class="fa-solid fa-user-plus"></i> สมัครสมาชิกแพทย์
                            </a>
                        </div>
                        <p class="mt-8 text-xs text-slate-400">
                            <i class="fa-solid fa-shield-halved mr-1"></i>
                            ข้อมูลบัญชีของคุณปลอดภัยและเป็นความลับ
                        </p>
                    </div>
                </div>
            </div>

            <?php elseif (!$isDoctor): ?>
            <!-- ── Gate: ล็อกอินแต่ไม่ใช่แพทย์ ───────────────────────────────── -->
            <div class="overflow-hidden rounded-3xl border border-amber-200 bg-white shadow-sm">
                <div class="h-1 bg-gradient-to-r from-amber-400 to-orange-400"></div>
                <div class="p-8 sm:p-12">
                    <div class="flex flex-col items-center gap-6 text-center sm:flex-row sm:text-left">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-3xl border border-amber-100 bg-amber-50 text-amber-500 text-2xl">
                            <i class="fa-solid fa-stethoscope"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs font-bold uppercase tracking-widest text-amber-600 mb-1.5">Doctor Access Required</div>
                            <h3 class="text-xl font-extrabold text-slate-900">บัญชีนี้ยังไม่ได้รับสิทธิ์เข้าถึงเนื้อหาแพทย์</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                คุณล็อกอินในฐานะ <strong>สมาชิกทั่วไป</strong> หากคุณเป็นแพทย์ กรุณาติดต่อทีมงาน KNA Interpharma เพื่อยืนยันเลข อว. และรับสิทธิ์เข้าถึงเนื้อหาเฉพาะแพทย์
                            </p>
                            <div class="mt-5 flex flex-wrap justify-center gap-3 sm:justify-start">
                                <a href="/contact.php"
                                   class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-700">
                                    <i class="fa-solid fa-envelope"></i> ติดต่อทีมงาน
                                </a>
                                <a href="/logout.php"
                                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                    <i class="fa-solid fa-right-from-bracket"></i> ออกจากระบบ
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php elseif (empty($learningVideos)): ?>
            <!-- ── ยังไม่มีวิดีโอ ───────────────────────────────────────────────── -->
            <div class="rounded-3xl border border-dashed border-slate-200 bg-white p-14 text-center shadow-sm">
                <i class="fa-solid fa-film text-5xl text-slate-300"></i>
                <p class="mt-4 text-sm text-slate-400">ยังไม่มีวิดีโอการเรียนการสอนในขณะนี้</p>
            </div>

            <?php else: ?>
            <!-- ── แสดงวิดีโอ ───────────────────────────────────────────────────── -->
            <div class="grid gap-8 sm:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($learningVideos as $video): ?>
                    <?php
                    $thumbUrl    = learning_thumbnail_url($video['thumbnail'] ?? '');
                    $accessLevel = normalize_video_access_level($video['access_level'] ?? 'public');
                    $embedUrl    = learning_embed_url($video['video_url'] ?? '');
                    $detailUrl   = '/video-detail.php?id=' . (int) ($video['id'] ?? 0);
                    $isYouTube   = $embedUrl !== null && str_contains($embedUrl, 'youtube');
                    $youtubeId   = learning_youtube_id($video['video_url'] ?? '');
                    if ($thumbUrl === '' && $youtubeId !== null) {
                        $thumbUrl = 'https://img.youtube.com/vi/' . rawurlencode($youtubeId) . '/hqdefault.jpg';
                    }
                    ?>
                    <article class="group flex flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg">
                        <a href="<?php echo h($detailUrl); ?>" class="relative block aspect-video overflow-hidden bg-slate-900">
                            <?php if ($thumbUrl !== ''): ?>
                                <img src="<?php echo h($thumbUrl); ?>"
                                     alt="<?php echo h($video['title'] ?? 'Video'); ?>"
                                     class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            <?php else: ?>
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-indigo-900 via-indigo-700 to-indigo-500"></div>
                            <?php endif; ?>
                            <div class="absolute inset-0 flex items-center justify-center bg-black/20 transition group-hover:bg-black/35">
                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/90 text-2xl text-indigo-700 shadow-lg transition group-hover:scale-110">
                                    <i class="fa-solid fa-play ml-1"></i>
                                </div>
                            </div>
                        </a>
                        <div class="flex flex-1 flex-col p-5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-bold uppercase tracking-widest text-indigo-600">
                                    <?php echo $isYouTube ? 'YouTube' : h($video['platform'] ?? 'video'); ?>
                                </span>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                    <?php echo $accessLevel === 'doctor' ? 'bg-indigo-50 text-indigo-700' : 'bg-emerald-50 text-emerald-700'; ?>">
                                    <?php echo $accessLevel === 'doctor' ? 'Doctor Only' : 'Member'; ?>
                                </span>
                            </div>
                            <h3 class="mt-3 text-base font-bold leading-snug text-slate-900">
                                <?php echo h($video['title'] ?? ''); ?>
                            </h3>
                            <?php if (!empty($video['description'])): ?>
                                <p class="line-clamp-3 mt-2 text-sm leading-6 text-slate-500">
                                    <?php echo h($video['description']); ?>
                                </p>
                            <?php endif; ?>
                            <a href="<?php echo h($detailUrl); ?>"
                               class="mt-auto inline-flex items-center gap-2 pt-4 text-sm font-semibold text-indigo-700 transition-all hover:gap-3">
                                <span>ดูรายละเอียด</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </section>

    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
</body>
</html>
