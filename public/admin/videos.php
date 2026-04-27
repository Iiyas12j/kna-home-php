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

if ($db_ready) {
    try {
        ensure_videos_access_level_column($pdo);
        ensure_videos_detail_columns($pdo);
    } catch (Exception $e) {
        $db_error = $e->getMessage();
        $db_ready = false;
    }
}

if ($db_ready && isset($_GET['delete'])) {
    try {
        $id = (int) $_GET['delete'];
        $stmt = $pdo->prepare('SELECT thumbnail FROM videos WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $pdo->prepare('DELETE FROM videos WHERE id = ?')->execute([$id]);
            if (!empty($row['thumbnail'])) {
                $file = $uploadDir . '/' . $row['thumbnail'];
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        header('Location: /admin/videos.php');
        exit;
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}

if ($db_ready && $_SERVER['REQUEST_METHOD'] === 'POST') {
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
                $old = $uploadDir . '/' . $current_thumb;
                if (is_file($old)) {
                    unlink($old);
                }
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

require_once __DIR__ . '/partials/header.php';
?>

<style>
    .video-admin-kpis {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }
    .video-admin-kpi__label {
        color: #6b7280;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }
    .video-admin-kpi__value {
        margin-top: 8px;
        font-size: 34px;
        font-weight: 800;
        color: #111827;
    }
    .video-admin-kpi__note {
        margin-top: 6px;
        color: #64748b;
        font-size: 14px;
        line-height: 1.5;
    }
    .video-admin-toolbar {
        align-items: stretch;
        margin-bottom: 20px;
    }
    .video-admin-toolbar__copy {
        max-width: 680px;
    }
    .video-admin-toolbar__copy p {
        margin: 8px 0 0;
        color: #64748b;
        line-height: 1.6;
    }
    .video-admin-toolbar__actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .video-admin-section {
        margin-top: 22px;
    }
    .video-admin-section__head {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-end;
        margin-bottom: 14px;
    }
    .video-admin-section__title {
        margin: 0;
        font-size: 26px;
        font-weight: 800;
        color: #111827;
    }
    .video-admin-section__desc {
        margin: 6px 0 0;
        color: #64748b;
        line-height: 1.6;
        max-width: 760px;
    }
    .video-admin-count {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        background: #eef2ff;
        color: #3730a3;
        font-weight: 800;
        white-space: nowrap;
    }
    .video-admin-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
    }
    .video-admin-card {
        padding: 14px;
    }
    .video-admin-card__thumb {
        height: 260px;
        border-radius: 16px;
        background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
        overflow: hidden;
        display: grid;
        place-items: center;
    }
    .video-admin-card__thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .video-admin-card__thumb i {
        font-size: 52px;
        color: #94a3b8;
    }
    .video-admin-card__meta {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 12px;
    }
    .video-admin-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        background: #eff6ff;
        color: #1d4ed8;
    }
    .video-admin-pill--learning {
        background: #eef2ff;
        color: #4338ca;
    }
    .video-admin-pill--doctor {
        background: #fee2e2;
        color: #be123c;
    }
    .video-admin-pill--member {
        background: #ede9fe;
        color: #6d28d9;
    }
    .video-admin-card__title {
        margin-top: 12px;
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .video-admin-card__url {
        display: block;
        margin-top: 6px;
        font-size: 13px;
        color: #2563eb;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .video-admin-card__summary {
        margin-top: 10px;
        color: #475569;
        line-height: 1.6;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 4.8em;
    }
    .video-admin-card__details {
        margin-top: 12px;
        padding: 12px;
        border-radius: 12px;
        background: #f8fafc;
        color: #334155;
        font-size: 14px;
        line-height: 1.6;
    }
    .video-admin-card__details strong {
        color: #1e3a8a;
    }
    .video-admin-empty {
        padding: 28px;
        text-align: center;
        color: #64748b;
        line-height: 1.7;
    }
    .video-modal-switch {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }
    .video-modal-switch__button {
        border: 1px solid #d6dbe3;
        border-radius: 14px;
        padding: 14px 16px;
        background: #fff;
        color: #1f2937;
        text-align: left;
        cursor: pointer;
    }
    .video-modal-switch__button strong {
        display: block;
        font-size: 15px;
        margin-bottom: 4px;
    }
    .video-modal-switch__button span {
        display: block;
        font-size: 13px;
        color: #64748b;
        line-height: 1.5;
    }
    .video-modal-switch__button.is-active {
        border-color: #2f65dc;
        background: #eef4ff;
        box-shadow: inset 0 0 0 1px rgba(47, 101, 220, 0.18);
    }
    .video-modal-note {
        display: none;
        margin: 0 0 14px;
        padding: 12px 14px;
        border-radius: 12px;
        background: #eff6ff;
        color: #1d4ed8;
        line-height: 1.6;
        font-size: 14px;
    }
    .video-modal-note strong {
        color: #1e3a8a;
    }
    @media (max-width: 720px) {
        .video-admin-section__head {
            align-items: flex-start;
            flex-direction: column;
        }
        .video-modal-switch {
            grid-template-columns: 1fr;
        }
    }
</style>

<h1 class="pageTitle">จัดการวิดีโอ</h1>

<?php if (!$db_ready || $db_error !== ''): ?>
    <div class="notice">Database not ready: <?php echo h($db_error ?: 'connection failed'); ?></div>
<?php endif; ?>
<?php foreach ($errors as $e): ?>
    <div class="error"><?php echo h($e); ?></div>
<?php endforeach; ?>

<div class="video-admin-kpis">
    <div class="card">
        <div class="video-admin-kpi__label">วิดีโอทั้งหมด</div>
        <div class="video-admin-kpi__value"><?php echo count($rows); ?></div>
        <div class="video-admin-kpi__note">รวมคลิปสาธารณะและคลิปความรู้ที่ใช้ในหน้าเว็บและหน้ารายละเอียด</div>
    </div>
    <div class="card">
        <div class="video-admin-kpi__label">วิดีโอสาธารณะ</div>
        <div class="video-admin-kpi__value"><?php echo count($publicRows); ?></div>
        <div class="video-admin-kpi__note">เหมาะกับ TikTok หรือคลิปที่เปิดให้ผู้ใช้ทั่วไปเข้าชมได้ทันที</div>
    </div>
    <div class="card">
        <div class="video-admin-kpi__label">วิดีโอความรู้</div>
        <div class="video-admin-kpi__value"><?php echo count($learningRows); ?></div>
        <div class="video-admin-kpi__note">ใช้กับคลิปสมาชิกหรือแพทย์ ที่ต้องมี summary และเนื้อหาเต็มในหน้า detail</div>
    </div>
</div>

<div class="toolbar video-admin-toolbar">
    <div class="video-admin-toolbar__copy">
        <strong>จัดการแยกตามประเภทการใช้งาน</strong>
        <p>วิดีโอสาธารณะเน้นแสดงบนหน้าเว็บแบบดูได้ทันที ส่วนวิดีโอความรู้จะมีข้อมูลเชิงลึกมากกว่าและใช้กับหน้า detail สำหรับสมาชิกหรือแพทย์</p>
    </div>
    <div class="video-admin-toolbar__actions">
        <button class="btn" data-modal-open="videoModal" data-video-mode="public"><i class="fa-solid fa-earth-asia"></i>เพิ่มวิดีโอสาธารณะ</button>
        <button class="btn btn--primary" data-modal-open="videoModal" data-video-mode="learning"><i class="fa-solid fa-user-doctor"></i>เพิ่มวิดีโอความรู้</button>
    </div>
</div>

<section class="video-admin-section">
    <div class="video-admin-section__head">
        <div>
            <h2 class="video-admin-section__title">วิดีโอสาธารณะ</h2>
            <p class="video-admin-section__desc">กลุ่มนี้เหมาะกับคลิปสั้นหรือคลิปโปรโมตที่ต้องการให้หน้าเว็บเล่นหรือแสดงตัวอย่างได้ทันที โดยไม่ต้องมีข้อมูลเชิงลึกมาก</p>
        </div>
        <div class="video-admin-count"><i class="fa-solid fa-clapperboard"></i><?php echo count($publicRows); ?> รายการ</div>
    </div>

    <?php if ($publicRows): ?>
        <div class="video-admin-grid">
            <?php foreach ($publicRows as $row): ?>
                <div class="card video-admin-card">
                    <div class="video-admin-card__thumb">
                        <?php if (!empty($row['thumbnail'])): ?>
                            <img src="/uploads/videos/<?php echo h($row['thumbnail']); ?>" alt="">
                        <?php else: ?>
                            <i class="fa-brands fa-tiktok"></i>
                        <?php endif; ?>
                    </div>
                    <div class="video-admin-card__meta">
                        <span class="video-admin-pill"><i class="fa-solid fa-earth-asia"></i>สาธารณะ</span>
                        <span class="video-admin-pill"><?php echo h(strtoupper((string) ($row['platform'] ?? 'other'))); ?></span>
                        <span class="status <?php echo (int) $row['is_active'] === 1 ? 'status--on' : 'status--off'; ?>"><?php echo (int) $row['is_active'] === 1 ? 'แสดง' : 'ซ่อน'; ?></span>
                    </div>
                    <div class="video-admin-card__title"><?php echo h($row['title'] ?: 'ไม่มีชื่อ'); ?></div>
                    <a class="video-admin-card__url" href="<?php echo h($row['video_url']); ?>" target="_blank" rel="noopener"><?php echo h($row['video_url']); ?></a>
                    <div class="video-admin-card__summary"><?php echo h(trim((string) ($row['description'] ?? '')) ?: 'ใช้สำหรับคลิปสาธารณะที่ต้องการแสดงบนหน้าเว็บแบบเข้าถึงง่าย'); ?></div>
                    <div class="video-admin-card__details">
                        <strong>ลำดับการแสดง:</strong> <?php echo (int) $row['sort_order']; ?>
                    </div>
                    <div class="actions" style="margin-top:12px;">
                        <a class="btn btn--primary" style="flex:1;" href="/admin/videos.php?edit=<?php echo (int) $row['id']; ?>"><i class="fa-solid fa-pen-to-square"></i>แก้ไข</a>
                        <a class="btn btn--danger" style="flex:1;" href="/admin/videos.php?delete=<?php echo (int) $row['id']; ?>" onclick="return confirm('ลบวิดีโอนี้?');"><i class="fa-solid fa-trash"></i>ลบ</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card video-admin-empty">ยังไม่มีวิดีโอสาธารณะ เพิ่มคลิปสำหรับหน้าแรกหรือหน้า Video Channel ได้จากปุ่มด้านบน</div>
    <?php endif; ?>
</section>

<section class="video-admin-section">
    <div class="video-admin-section__head">
        <div>
            <h2 class="video-admin-section__title">วิดีโอความรู้</h2>
            <p class="video-admin-section__desc">กลุ่มนี้ใช้กับคลิปสำหรับสมาชิกหรือแพทย์ที่ต้องมีรายละเอียดมากขึ้น เช่น สรุปสั้น เนื้อหาเต็ม และหน้า detail ที่พร้อมส่งต่อไปยัง YouTube หรือแพลตฟอร์มต้นทาง</p>
        </div>
        <div class="video-admin-count"><i class="fa-solid fa-book-medical"></i><?php echo count($learningRows); ?> รายการ</div>
    </div>

    <?php if ($learningRows): ?>
        <div class="video-admin-grid">
            <?php foreach ($learningRows as $row): ?>
                <?php $rowAccess = normalize_video_access_level($row['access_level'] ?? 'public'); ?>
                <div class="card video-admin-card">
                    <div class="video-admin-card__thumb">
                        <?php if (!empty($row['thumbnail'])): ?>
                            <img src="/uploads/videos/<?php echo h($row['thumbnail']); ?>" alt="">
                        <?php else: ?>
                            <i class="fa-solid fa-circle-play"></i>
                        <?php endif; ?>
                    </div>
                    <div class="video-admin-card__meta">
                        <span class="video-admin-pill video-admin-pill--learning"><i class="fa-solid fa-book-open-reader"></i>วิดีโอความรู้</span>
                        <span class="video-admin-pill <?php echo $rowAccess === 'doctor' ? 'video-admin-pill--doctor' : 'video-admin-pill--member'; ?>">
                            <?php echo h(video_access_label($rowAccess)); ?>
                        </span>
                        <span class="video-admin-pill"><?php echo h(strtoupper((string) ($row['platform'] ?? 'other'))); ?></span>
                        <span class="status <?php echo (int) $row['is_active'] === 1 ? 'status--on' : 'status--off'; ?>"><?php echo (int) $row['is_active'] === 1 ? 'แสดง' : 'ซ่อน'; ?></span>
                    </div>
                    <div class="video-admin-card__title"><?php echo h($row['title'] ?: 'ไม่มีชื่อ'); ?></div>
                    <a class="video-admin-card__url" href="<?php echo h($row['video_url']); ?>" target="_blank" rel="noopener"><?php echo h($row['video_url']); ?></a>
                    <div class="video-admin-card__summary"><?php echo h(trim((string) ($row['detail_summary'] ?? '')) ?: trim((string) ($row['description'] ?? '')) ?: 'วิดีโอความรู้รายการนี้ควรมีสรุปสั้นและเนื้อหาเต็มเพื่อใช้ในหน้ารายละเอียด'); ?></div>
                    <div class="video-admin-card__details">
                        <strong>ข้อมูล detail:</strong>
                        <?php echo trim((string) ($row['detail_content'] ?? '')) !== '' ? 'มีเนื้อหาเต็มพร้อมใช้' : 'ยังไม่ได้เพิ่มเนื้อหาเต็ม'; ?>
                        <br>
                        <strong>ลำดับการแสดง:</strong> <?php echo (int) $row['sort_order']; ?>
                    </div>
                    <div class="actions" style="margin-top:12px;">
                        <a class="btn btn--primary" style="flex:1;" href="/admin/videos.php?edit=<?php echo (int) $row['id']; ?>"><i class="fa-solid fa-pen-to-square"></i>แก้ไข</a>
                        <a class="btn btn--danger" style="flex:1;" href="/admin/videos.php?delete=<?php echo (int) $row['id']; ?>" onclick="return confirm('ลบวิดีโอนี้?');"><i class="fa-solid fa-trash"></i>ลบ</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card video-admin-empty">ยังไม่มีวิดีโอความรู้ เพิ่มคลิปสำหรับสมาชิกหรือแพทย์พร้อมข้อมูลหน้ารายละเอียดได้จากปุ่ม “เพิ่มวิดีโอความรู้”</div>
    <?php endif; ?>
</section>

<div class="modal <?php echo $editing ? 'is-open' : ''; ?>" id="videoModal">
    <div class="modal__dialog" style="max-width:760px;">
        <div class="modal__head">
            <h2 class="modal__title"><?php echo $editing ? 'แก้ไขวิดีโอ' : 'เพิ่มวิดีโอ'; ?></h2>
            <button class="closeBtn" data-modal-close="videoModal"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="post" enctype="multipart/form-data">
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

            <div id="learningVideoFields" style="margin-top:18px; border:1px solid #dbe2f0; border-radius:16px; padding:16px; background:#f8fbff;">
                <div style="font-weight:800; color:#1e3a8a; margin-bottom:6px;">ข้อมูลสำหรับหน้ารายละเอียดวิดีโอความรู้</div>
                <div class="muted" style="margin-bottom:14px;">ใช้กับวิดีโอที่มีสิทธิ์ <strong>member</strong> หรือ <strong>doctor</strong> เพื่อแสดงรายละเอียดมากกว่าวิดีโอสาธารณะ</div>

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
                    <input type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp">
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

            <div class="actions" style="margin-top:8px;">
                <button class="btn btn--primary" type="submit"><i class="fa-solid fa-floppy-disk"></i>บันทึก</button>
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
