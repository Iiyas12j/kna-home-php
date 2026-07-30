<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/helpers.php';
require_admin();

$page_title = 'จัดการวิดีโอ';
$active_nav = 'videos';
$db_ready = $pdo instanceof PDO;
$db_error = '';
$errors = [];

$item = [
    'id' => 0,
    'title' => '',
    'description' => '',
    'detail_summary' => '',
    'detail_content' => '',
    'video_url' => '',
    'thumbnail' => '',
    'platform' => 'tiktok',
    'access_level' => 'public',
    'sort_order' => 0,
    'is_active' => 1,
];
$editing = false;
$uploadDir = __DIR__ . '/../uploads/videos';
$platforms = ['tiktok', 'youtube', 'facebook', 'other'];
$accessLevels = ['public', 'member', 'doctor'];

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? 'save') === 'delete') {
    try {
        require_valid_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('SELECT thumbnail FROM videos WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $pdo->prepare('DELETE FROM videos WHERE id = ?')->execute([$id]);
            delete_uploaded_file($row['thumbnail'] ?? '', $uploadDir, 'videos');
        }
        header('Location: /admin/videos.php');
        exit;
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? 'save') !== 'delete') {
    try {
        require_valid_csrf();
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
    $id = (int) ($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $detail_summary = trim($_POST['detail_summary'] ?? '');
    $detail_content = trim($_POST['detail_content'] ?? '');
    $video_url = trim($_POST['video_url'] ?? '');
    $platform = $_POST['platform'] ?? 'tiktok';
    $access_level = normalize_video_access_level($_POST['access_level'] ?? 'public');
    $sort_order = (int) ($_POST['sort_order'] ?? 0);
    $is_active = (int) ($_POST['is_active'] ?? 0) === 1 ? 1 : 0;
    $current_thumb = $_POST['current_thumbnail'] ?? '';

    if ($title === '' || $video_url === '') {
        $errors[] = 'กรุณากรอกชื่อและ URL วิดีโอ';
    }
    if (!in_array($platform, $platforms, true)) {
        $platform = 'other';
    }

    try {
        $newThumb = save_uploaded_image($_FILES['thumbnail'] ?? [], $uploadDir, 'video');
    } catch (Exception $e) {
        $newThumb = null;
        $errors[] = $e->getMessage();
    }

    if (!$errors) {
        $thumb = $newThumb ?: $current_thumb;
        try {
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE videos SET title = ?, description = ?, detail_summary = ?, detail_content = ?, video_url = ?, thumbnail = ?, platform = ?, access_level = ?, sort_order = ?, is_active = ?, updated_at = NOW() WHERE id = ?');
                $stmt->execute([$title, $description, $detail_summary, $detail_content, $video_url, $thumb, $platform, $access_level, $sort_order, $is_active, $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO videos (title, description, detail_summary, detail_content, video_url, thumbnail, platform, access_level, sort_order, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
                $stmt->execute([$title, $description, $detail_summary, $detail_content, $video_url, $thumb, $platform, $access_level, $sort_order, $is_active]);
            }

            if ($newThumb && $current_thumb && $newThumb !== $current_thumb) {
                delete_uploaded_file($current_thumb, $uploadDir, 'videos');
            }

            header('Location: /admin/videos.php');
            exit;
        } catch (Exception $e) {
            $db_error = $e->getMessage();
        }
    }

    $item = array_merge($item, [
        'id' => $id,
        'title' => $title,
        'description' => $description,
        'detail_summary' => $detail_summary,
        'detail_content' => $detail_content,
        'video_url' => $video_url,
        'thumbnail' => $current_thumb,
        'platform' => $platform,
        'access_level' => $access_level,
        'sort_order' => $sort_order,
        'is_active' => $is_active,
    ]);
    $editing = $id > 0;
}

if ($db_ready && isset($_GET['edit'])) {
    try {
        $id = (int) $_GET['edit'];
        $stmt = $pdo->prepare('SELECT * FROM videos WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $item = array_merge($item, $row);
            $editing = true;
        }
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

$rows = [];
if ($db_ready) {
    try {
        $rows = $pdo->query('SELECT * FROM videos ORDER BY sort_order ASC, id DESC')->fetchAll();
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

$publicRows = [];
$learningRows = [];
foreach ($rows as $row) {
    $accessLevel = normalize_video_access_level($row['access_level'] ?? 'public');
    if ($accessLevel === 'public') {
        $publicRows[] = $row;
    } else {
        $learningRows[] = $row;
    }
}

$currentMode = normalize_video_access_level($item['access_level'] ?? 'public') === 'public' ? 'public' : 'learning';

function admin_video_embed_url(?string $url, string $platform): ?string
{
    $url = trim((string) $url);
    if ($url === '') {
        return null;
    }
    if (preg_match('#tiktok\.com/.*/video/(\d+)#i', $url, $m)) {
        return 'https://www.tiktok.com/player/v1/' . $m[1] . '?music_info=0&description=0&rel=0';
    }
    if (preg_match('#(?:youtube\.com/(?:watch\?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{6,20})#i', $url, $m)) {
        return 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?rel=0';
    }
    if (preg_match('#^https?://(www\.)?(facebook\.com|fb\.watch)/#i', $url)) {
        return 'https://www.facebook.com/plugins/video.php?href=' . rawurlencode($url) . '&show_text=false';
    }
    return null;
}

require_once __DIR__ . '/partials/header.php';
?>

<style>
.v-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 40px;
    gap: 16px;
    flex-wrap: wrap;
}
.v-header-content h1 {
    font-family: 'Prompt', sans-serif;
    font-size: 32px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 6px 0;
    line-height: 1.2;
    letter-spacing: -0.5px;
}
.v-header-content p {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}
.v-header-actions {
    display: flex;
    gap: 12px;
}

.v-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 48px;
}
.v-stat-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    height: 110px;
    padding: 24px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: box-shadow 0.2s ease;
}
.v-stat-card:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
}
.v-stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 28px;
}
.v-stat-content {
    flex: 1;
}
.v-stat-label {
    font-size: 13px;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.v-stat-value {
    font-size: 40px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1;
}

.v-section {
    margin-bottom: 48px;
}
.v-section-header {
    margin-bottom: 24px;
}
.v-section-header h2 {
    font-size: 24px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 6px 0;
    letter-spacing: -0.3px;
}
.v-section-header p {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}

.v-grid {
    display: grid;
    gap: 24px;
    grid-template-columns: repeat(1, 1fr);
}
@media (min-width: 640px) { .v-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .v-grid { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 1280px) { .v-grid { grid-template-columns: repeat(4, 1fr); } }

.v-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
}
.v-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px -4px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
}
.v-card-thumb {
    position: relative;
    aspect-ratio: 16/9;
    background: #f8fafc;
    overflow: hidden;
}
.v-card-thumb--tall {
    aspect-ratio: 9/16;
}
.v-card-thumb iframe {
    display: block;
    width: 100%;
    height: 100%;
    border: 0;
}
.v-card-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.v-card-thumb-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
    font-size: 48px;
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
}
.v-card-play {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 56px;
    height: 56px;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #2563eb;
    font-size: 20px;
    padding-left: 4px;
    opacity: 0;
    transition: opacity 0.2s ease, transform 0.2s ease;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}
.v-card:hover .v-card-play {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1.05);
}
.v-card-body {
    padding: 24px;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.v-card-badges {
    display: flex;
    gap: 8px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}
.v-badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.v-badge-blue { background: #eff6ff; color: #2563eb; }
.v-badge-green { background: #f0fdf4; color: #16a34a; }
.v-badge-red { background: #fef2f2; color: #dc2626; }
.v-badge-purple { background: #faf5ff; color: #9333ea; }
.v-badge-gray { background: #f1f5f9; color: #475569; }
.v-badge-indigo { background: #eef2ff; color: #4f46e5; }

.v-card-title {
    font-size: 18px;
    font-weight: 600;
    color: #0f172a;
    line-height: 1.4;
    margin: 0 0 8px 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.v-card-desc {
    font-size: 14px;
    color: #64748b;
    line-height: 1.5;
    margin: 0 0 16px 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}
.v-card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    color: #64748b;
    margin-bottom: 16px;
    padding-top: 16px;
    border-top: 1px solid #f1f5f9;
}
.v-card-actions {
    display: flex;
    gap: 8px;
}
.v-card-btn {
    flex: 1;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
    padding: 10px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}
.v-card-btn-edit {
    background: #f8fafc;
    color: #475569;
    border-color: #e2e8f0;
}
.v-card-btn-edit:hover { background: #f1f5f9; color: #0f172a; border-color: #cbd5e1; }
.v-card-btn-del {
    background: #fff1f2;
    color: #e11d48;
}
.v-card-btn-del:hover { background: #ffe4e6; color: #be123c; }

@media (max-width: 768px) {
    .v-header { flex-direction: column; align-items: flex-start; gap: 16px; }
    .v-stats { grid-template-columns: 1fr; }
}

/* Modal specific style updates */
.video-modal-switch {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 16px;
}
.video-modal-switch__button {
    border: 1px solid #d1d5db;
    border-radius: 12px;
    padding: 16px;
    background: #fff;
    color: #1f2937;
    text-align: left;
    cursor: pointer;
    transition: all 0.2s ease;
}
.video-modal-switch__button:hover {
    border-color: #9ca3af;
    background: #f9fafb;
}
.video-modal-switch__button strong {
    display: block;
    font-size: 15px;
    margin-bottom: 4px;
    color: #111827;
}
.video-modal-switch__button span {
    display: block;
    font-size: 13px;
    color: #64748b;
    line-height: 1.5;
}
.video-modal-switch__button.is-active {
    border-color: #2563eb;
    background: #eff6ff;
    box-shadow: 0 0 0 1px #2563eb;
}
.video-modal-note {
    display: none;
    margin: 0 0 16px;
    padding: 12px 16px;
    border-radius: 10px;
    background: #eff6ff;
    color: #1d4ed8;
    line-height: 1.6;
    font-size: 14px;
}
.video-modal-note strong {
    color: #1e3a8a;
}
@media (max-width: 720px) {
    .video-modal-switch {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Header Section -->
<div class="v-header">
    <div class="v-header-content">
        <h1>จัดการวิดีโอ</h1>
        <p>จัดการคลิปสาธารณะและคลิปความรู้สำหรับเว็บไซต์ KNA</p>
    </div>
    <div class="v-header-actions">
        <button class="btn" data-modal-open="videoModal" data-video-mode="public"><i class="fa-solid fa-earth-asia"></i> เพิ่มวิดีโอสาธารณะ</button>
        <button class="btn btn--primary" data-modal-open="videoModal" data-video-mode="learning"><i class="fa-solid fa-book-medical"></i> เพิ่มวิดีโอความรู้</button>
    </div>
</div>

<?php if (!$db_ready || $db_error !== ''): ?>
    <div class="notice">Database not ready: <?php echo h($db_error ?: 'connection failed'); ?></div>
<?php endif; ?>
<?php foreach ($errors as $e): ?>
    <div class="error"><?php echo h($e); ?></div>
<?php endforeach; ?>

<!-- Stats Section -->
<div class="v-stats">
    <div class="v-stat-card">
        <div class="v-stat-icon" style="color: #2563eb; background: #eff6ff;"><i class="fa-solid fa-video"></i></div>
        <div class="v-stat-content">
            <div class="v-stat-label">วิดีโอทั้งหมด</div>
            <div class="v-stat-value"><?php echo count($rows); ?></div>
        </div>
    </div>
    <div class="v-stat-card">
        <div class="v-stat-icon" style="color: #16a34a; background: #f0fdf4;"><i class="fa-solid fa-earth-asia"></i></div>
        <div class="v-stat-content">
            <div class="v-stat-label">วิดีโอสาธารณะ</div>
            <div class="v-stat-value"><?php echo count($publicRows); ?></div>
        </div>
    </div>
    <div class="v-stat-card">
        <div class="v-stat-icon" style="color: #9333ea; background: #faf5ff;"><i class="fa-solid fa-book-medical"></i></div>
        <div class="v-stat-content">
            <div class="v-stat-label">วิดีโอความรู้</div>
            <div class="v-stat-value"><?php echo count($learningRows); ?></div>
        </div>
    </div>
</div>

<!-- Public Videos Section -->
<section class="v-section">
    <div class="v-section-header">
        <h2>วิดีโอสาธารณะ</h2>
        <p>กลุ่มนี้เหมาะกับคลิปสั้นหรือคลิปโปรโมตที่ต้องการให้หน้าเว็บเล่นหรือแสดงตัวอย่างได้ทันที</p>
    </div>

    <?php if ($publicRows): ?>
        <div class="v-grid">
            <?php foreach ($publicRows as $row): ?>
                <?php
                $rowPlatform = (string) ($row['platform'] ?? 'other');
                $embedUrl = admin_video_embed_url($row['video_url'] ?? '', $rowPlatform);
                ?>
                <div class="v-card">
                    <div class="v-card-thumb<?php echo ($embedUrl && str_contains($embedUrl, 'tiktok.com/player')) ? ' v-card-thumb--tall' : ''; ?>">
                        <?php if ($embedUrl): ?>
                            <iframe src="<?php echo h($embedUrl); ?>" title="<?php echo h($row['title'] ?? 'Video'); ?>" allow="fullscreen" allowfullscreen loading="lazy"></iframe>
                        <?php elseif (!empty($row['thumbnail'])): ?>
                            <img src="<?php echo h(upload_url($row['thumbnail'], 'videos')); ?>" alt="">
                            <div class="v-card-play"><i class="fa-solid fa-play"></i></div>
                        <?php else: ?>
                            <div class="v-card-thumb-placeholder"><i class="fa-brands fa-tiktok"></i></div>
                            <div class="v-card-play"><i class="fa-solid fa-play"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="v-card-body">
                        <div class="v-card-badges">
                            <span class="v-badge v-badge-blue"><i class="fa-solid fa-earth-asia"></i> สาธารณะ</span>
                            <span class="v-badge v-badge-gray"><?php echo h(strtoupper((string) ($row['platform'] ?? 'other'))); ?></span>
                            <?php if((int) $row['is_active'] === 1): ?>
                                <span class="v-badge v-badge-green">แสดง</span>
                            <?php else: ?>
                                <span class="v-badge v-badge-red">ซ่อน</span>
                            <?php endif; ?>
                        </div>
                        <h3 class="v-card-title"><?php echo h($row['title'] ?: 'ไม่มีชื่อ'); ?></h3>
                        <div class="v-card-desc"><?php echo h(trim((string) ($row['description'] ?? '')) ?: 'ไม่มีคำอธิบาย'); ?></div>
                        
                        <div class="v-card-meta">
                            <span>จัดลำดับ: <?php echo (int) $row['sort_order']; ?></span>
                            <a href="<?php echo h($row['video_url']); ?>" target="_blank" rel="noopener" style="color: #2563eb; font-weight: 500;"><i class="fa-solid fa-link"></i> เปิดลิงก์</a>
                        </div>
                        
                        <div class="v-card-actions">
                            <a class="v-card-btn v-card-btn-edit" href="/admin/videos.php?edit=<?php echo (int) $row['id']; ?>"><i class="fa-solid fa-pen-to-square"></i> แก้ไข</a>
                            <form method="post" onsubmit="return confirm('ลบวิดีโอนี้?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                <button class="v-card-btn v-card-btn-del" type="submit"><i class="fa-solid fa-trash"></i> ลบ</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card video-admin-empty" style="padding: 48px; text-align: center; color: #64748b; background: #ffffff; border-radius: 20px; border: 1px dashed #cbd5e1; box-shadow: none;">
            <i class="fa-solid fa-video-slash" style="font-size: 32px; color: #94a3b8; margin-bottom: 16px;"></i>
            <div style="font-size: 16px; font-weight: 500; color: #1e293b; margin-bottom: 8px;">ยังไม่มีวิดีโอสาธารณะ</div>
            <p style="margin: 0; font-size: 14px;">เพิ่มคลิปสำหรับหน้าแรกได้จากปุ่ม "เพิ่มวิดีโอสาธารณะ" ด้านบน</p>
        </div>
    <?php endif; ?>
</section>

<!-- Learning Videos Section -->
<section class="v-section">
    <div class="v-section-header">
        <h2>วิดีโอความรู้</h2>
        <p>กลุ่มนี้ใช้กับคลิปสำหรับสมาชิกหรือแพทย์ที่ต้องมีรายละเอียดมากขึ้น เช่น สรุปสั้น เนื้อหาเต็ม</p>
    </div>

    <?php if ($learningRows): ?>
        <div class="v-grid">
            <?php foreach ($learningRows as $row): ?>
                <?php
                $rowAccess = normalize_video_access_level($row['access_level'] ?? 'public');
                $rowPlatform = (string) ($row['platform'] ?? 'other');
                $embedUrl = admin_video_embed_url($row['video_url'] ?? '', $rowPlatform);
                ?>
                <div class="v-card">
                    <div class="v-card-thumb<?php echo ($embedUrl && str_contains($embedUrl, 'tiktok.com/player')) ? ' v-card-thumb--tall' : ''; ?>">
                        <?php if ($embedUrl): ?>
                            <iframe src="<?php echo h($embedUrl); ?>" title="<?php echo h($row['title'] ?? 'Video'); ?>" allow="fullscreen" allowfullscreen loading="lazy"></iframe>
                        <?php elseif (!empty($row['thumbnail'])): ?>
                            <img src="<?php echo h(upload_url($row['thumbnail'], 'videos')); ?>" alt="">
                            <div class="v-card-play"><i class="fa-solid fa-play"></i></div>
                        <?php else: ?>
                            <div class="v-card-thumb-placeholder"><i class="fa-solid fa-book-medical"></i></div>
                            <div class="v-card-play"><i class="fa-solid fa-play"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="v-card-body">
                        <div class="v-card-badges">
                            <span class="v-badge v-badge-purple"><i class="fa-solid fa-book-open-reader"></i> ความรู้</span>
                            <?php if ($rowAccess === 'doctor'): ?>
                                <span class="v-badge v-badge-red"><?php echo h(video_access_label($rowAccess)); ?></span>
                            <?php else: ?>
                                <span class="v-badge v-badge-indigo"><?php echo h(video_access_label($rowAccess)); ?></span>
                            <?php endif; ?>
                            <?php if((int) $row['is_active'] === 1): ?>
                                <span class="v-badge v-badge-green">แสดง</span>
                            <?php else: ?>
                                <span class="v-badge v-badge-red">ซ่อน</span>
                            <?php endif; ?>
                        </div>
                        <h3 class="v-card-title"><?php echo h($row['title'] ?: 'ไม่มีชื่อ'); ?></h3>
                        <div class="v-card-desc"><?php echo h(trim((string) ($row['detail_summary'] ?? '')) ?: trim((string) ($row['description'] ?? '')) ?: 'ไม่มีคำอธิบาย'); ?></div>
                        
                        <div class="v-card-meta">
                            <span>จัดลำดับ: <?php echo (int) $row['sort_order']; ?></span>
                            <?php if(trim((string) ($row['detail_content'] ?? '')) !== ''): ?>
                                <span style="color:#16a34a; font-weight: 500;"><i class="fa-solid fa-file-lines"></i> มีเนื้อหาเต็ม</span>
                            <?php else: ?>
                                <span style="color:#94a3b8;"><i class="fa-solid fa-file-excel"></i> ไม่มีเนื้อหา</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="v-card-actions">
                            <a class="v-card-btn v-card-btn-edit" href="/admin/videos.php?edit=<?php echo (int) $row['id']; ?>"><i class="fa-solid fa-pen-to-square"></i> แก้ไข</a>
                            <form method="post" onsubmit="return confirm('ลบวิดีโอนี้?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                <button class="v-card-btn v-card-btn-del" type="submit"><i class="fa-solid fa-trash"></i> ลบ</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card video-admin-empty" style="padding: 48px; text-align: center; color: #64748b; background: #ffffff; border-radius: 20px; border: 1px dashed #cbd5e1; box-shadow: none;">
            <i class="fa-solid fa-book-medical" style="font-size: 32px; color: #94a3b8; margin-bottom: 16px;"></i>
            <div style="font-size: 16px; font-weight: 500; color: #1e293b; margin-bottom: 8px;">ยังไม่มีวิดีโอความรู้</div>
            <p style="margin: 0; font-size: 14px;">เพิ่มคลิปสำหรับสมาชิกหรือแพทย์ได้จากปุ่ม "เพิ่มวิดีโอความรู้" ด้านบน</p>
        </div>
    <?php endif; ?>
</section>

<!-- Modal -->
<div class="modal <?php echo $editing ? 'is-open' : ''; ?>" id="videoModal">
    <div class="modal__dialog" style="max-width:760px;">
        <div class="modal__head">
            <h2 class="modal__title"><?php echo $editing ? 'แก้ไขวิดีโอ' : 'เพิ่มวิดีโอ'; ?></h2>
            <button class="closeBtn" data-modal-close="videoModal"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="post" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
            <input type="hidden" name="current_thumbnail" value="<?php echo h($item['thumbnail']); ?>">
            <input type="hidden" name="video_mode" id="videoMode" value="<?php echo h($currentMode); ?>">

            <div class="video-modal-switch" id="videoModeSwitch">
                <button class="video-modal-switch__button" type="button" data-video-type="public">
                    <strong>วิดีโอสาธารณะ</strong>
                    <span>ใช้กับคลิปที่ผู้ใช้ทั่วไปเข้าชมได้ทันที และมักเน้นการแสดงผลแบบกระชับบนหน้าเว็บ</span>
                </button>
                <button class="video-modal-switch__button" type="button" data-video-type="learning">
                    <strong>วิดีโอความรู้</strong>
                    <span>ใช้กับคลิปที่ต้องมีรายละเอียดเพิ่มเติมสำหรับสมาชิกหรือแพทย์ พร้อมข้อมูลหน้า detail</span>
                </button>
            </div>

            <div class="video-modal-note" id="publicVideoNote">
                <strong>โหมดสาธารณะ:</strong> ระบบจะตั้งสิทธิ์เป็นสาธารณะอัตโนมัติ และซ่อนฟิลด์รายละเอียดเชิงลึกที่ไม่จำเป็น
            </div>

            <div class="field">
                <label>ชื่อวิดีโอ *</label>
                <input type="text" name="title" value="<?php echo h($item['title']); ?>" required>
            </div>
            <div class="field">
                <label>Video URL *</label>
                <input type="text" name="video_url" value="<?php echo h($item['video_url']); ?>" required>
            </div>
            <div class="field">
                <label id="descriptionLabel">คำอธิบาย</label>
                <textarea name="description" rows="4"><?php echo h($item['description']); ?></textarea>
            </div>

            <div id="learningVideoFields" style="margin-top:18px; border:1px solid #dbe2f0; border-radius:12px; padding:16px; background:#f8fbff;">
                <div style="font-weight:700; color:#1e3a8a; margin-bottom:6px;">ข้อมูลสำหรับหน้ารายละเอียดวิดีโอความรู้</div>
                <div class="muted" style="margin-bottom:14px; font-size:13px;">ใช้กับวิดีโอที่มีสิทธิ์ <strong>member</strong> หรือ <strong>doctor</strong> เพื่อแสดงรายละเอียดมากกว่าวิดีโอสาธารณะ</div>

                <div class="field">
                    <label>สรุปสั้นบนหน้า detail</label>
                    <textarea name="detail_summary" rows="3"><?php echo h($item['detail_summary']); ?></textarea>
                </div>
                <div class="field">
                    <label>เนื้อหาเต็มสำหรับหน้า detail</label>
                    <textarea name="detail_content" rows="8"><?php echo h($item['detail_content']); ?></textarea>
                </div>
            </div>

            <div class="row">
                <div class="field">
                    <label>Thumbnail</label>
                    <input type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp,.svg">
                </div>
                <div class="field">
                    <label>Platform</label>
                    <select name="platform">
                        <?php foreach ($platforms as $platform): ?>
                            <option value="<?php echo h($platform); ?>" <?php echo $item['platform'] === $platform ? 'selected' : ''; ?>><?php echo h($platform); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="field" id="accessFieldWrap">
                    <label>สิทธิ์การเข้าถึง</label>
                    <select name="access_level">
                        <?php foreach ($accessLevels as $accessLevel): ?>
                            <option value="<?php echo h($accessLevel); ?>" <?php echo normalize_video_access_level($item['access_level'] ?? 'public') === $accessLevel ? 'selected' : ''; ?>>
                                <?php echo h($accessLevel . ' - ' . video_access_label($accessLevel)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>ลำดับ</label>
                    <input type="number" name="sort_order" value="<?php echo (int) $item['sort_order']; ?>">
                </div>
            </div>

            <div class="row">
                <div class="field">
                    <label>สถานะ</label>
                    <select name="is_active">
                        <option value="1" <?php echo (int) $item['is_active'] === 1 ? 'selected' : ''; ?>>แสดง</option>
                        <option value="0" <?php echo (int) $item['is_active'] === 0 ? 'selected' : ''; ?>>ซ่อน</option>
                    </select>
                </div>
            </div>

            <div class="actions" style="margin-top:16px;">
                <button class="btn btn--primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> บันทึก</button>
                <button class="btn btn--muted" type="button" data-modal-close="videoModal">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
<script>
    (function () {
        const modeInput = document.getElementById('videoMode');
        const modeButtons = document.querySelectorAll('[data-video-type]');
        const accessSelect = document.querySelector('select[name="access_level"]');
        const detailFields = document.getElementById('learningVideoFields');
        const publicNote = document.getElementById('publicVideoNote');
        const accessFieldWrap = document.getElementById('accessFieldWrap');
        const descriptionLabel = document.getElementById('descriptionLabel');

        if (!accessSelect || !detailFields || !modeInput) {
            return;
        }

        const setActiveButton = (mode) => {
            modeButtons.forEach((button) => {
                button.classList.toggle('is-active', button.dataset.videoType === mode);
            });
        };

        const applyMode = (mode, syncAccess) => {
            const nextMode = mode === 'public' ? 'public' : 'learning';

            modeInput.value = nextMode;
            setActiveButton(nextMode);
            detailFields.style.display = nextMode === 'learning' ? 'block' : 'none';
            publicNote.style.display = nextMode === 'public' ? 'block' : 'none';
            accessFieldWrap.style.display = nextMode === 'learning' ? 'block' : 'none';
            descriptionLabel.textContent = nextMode === 'learning' ? 'คำอธิบายสั้นสำหรับการ์ด/รายการ' : 'คำอธิบาย';

            if (syncAccess) {
                if (nextMode === 'public') {
                    accessSelect.value = 'public';
                } else if (accessSelect.value === 'public') {
                    accessSelect.value = 'member';
                }
            }
        };

        modeButtons.forEach((button) => {
            button.addEventListener('click', () => {
                applyMode(button.dataset.videoType, true);
            });
        });

        accessSelect.addEventListener('change', () => {
            applyMode(accessSelect.value === 'public' ? 'public' : 'learning', false);
        });

        document.querySelectorAll('[data-video-mode]').forEach((button) => {
            button.addEventListener('click', () => {
                applyMode(button.dataset.videoMode, true);
            });
        });

        applyMode(modeInput.value || '<?php echo h($currentMode); ?>', true);
    })();
</script>
