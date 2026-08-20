<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

function frontend_register_redirect(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '' || $value[0] !== '/' || str_starts_with($value, '//')) {
        return '/video-tiktok.php';
    }
    return $value;
}

$db_ready         = $pdo instanceof PDO;
$errors           = [];
$first_name       = '';
$last_name        = '';
$email            = '';
$phone            = '';
$line_id          = '';
$hospital_clinic  = '';
$province         = '';
$requested_role   = 'member';
$doctor_license_no = '';
$redirect         = frontend_register_redirect($_REQUEST['redirect'] ?? '/video-tiktok.php');

if ($db_ready) {
    try {
        ensure_admin_user_registration_columns($pdo);
    } catch (Exception $e) {
        $db_ready = false;
        $errors[] = 'ระบบสมาชิกยังไม่พร้อมใช้งาน';
    }
}

if (is_member_logged_in()) {
    header('Location: ' . $redirect);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'คำขอไม่ถูกต้อง กรุณาลองใหม่';
    }

    $first_name       = trim((string) ($_POST['first_name'] ?? ''));
    $last_name        = trim((string) ($_POST['last_name'] ?? ''));
    $email            = trim((string) ($_POST['email'] ?? ''));
    $phone            = trim((string) ($_POST['phone'] ?? ''));
    $line_id          = trim((string) ($_POST['line_id'] ?? ''));
    $hospital_clinic  = trim((string) ($_POST['hospital_clinic'] ?? ''));
    $province         = trim((string) ($_POST['province'] ?? ''));
    $requested_role   = member_registration_role($_POST['requested_role'] ?? 'member');
    $doctor_license_no = trim((string) ($_POST['doctor_license_no'] ?? ''));
    $password         = (string) ($_POST['password'] ?? '');
    $password_confirm = (string) ($_POST['password_confirm'] ?? '');

    if (!$db_ready)  $errors[] = 'ระบบฐานข้อมูลยังไม่พร้อมใช้งาน';
    if ($first_name === '') $errors[] = 'กรุณากรอกชื่อ';
    if ($last_name === '')  $errors[] = 'กรุณากรอกนามสกุล';

    if ($email === '') {
        $errors[] = 'กรุณากรอกอีเมล';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
    }

    if ($requested_role === 'doctor' && $doctor_license_no === '') {
        $errors[] = 'กรุณากรอกเลขใบประกอบวิชาชีพแพทย์';
    }

    if ($password === '') {
        $errors[] = 'กรุณากรอกรหัสผ่าน';
    } elseif (strlen($password) < 6) {
        $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
    }

    if ($password !== $password_confirm) $errors[] = 'ยืนยันรหัสผ่านไม่ตรงกัน';

    if (empty($_POST['terms'])) $errors[] = 'กรุณายอมรับเงื่อนไขการใช้งาน';

    if ($errors === []) {
        $registerError = null;
        $fullName = $first_name . ' ' . $last_name;
        if (member_register(
            $pdo,
            $first_name,
            $email,
            $password,
            [
                'requested_role'   => $requested_role,
                'doctor_license_no'=> $doctor_license_no,
                'last_name'        => $last_name,
                'hospital_clinic'  => $hospital_clinic,
                'province'         => $province,
                'phone'            => $phone,
                'line_id'          => $line_id,
            ],
            $registerError
        )) {
            header('Location: ' . $redirect);
            exit;
        }
        $errors[] = $registerError ?: 'สมัครสมาชิกไม่สำเร็จ';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <?php $page_title = 'สมัครสมาชิก - KNA Interpharma'; require_once __DIR__ . '/partials/site-head.php'; ?>
    <style>
        body { font-family: 'Kanit', sans-serif; }
        .kna-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-family: 'Kanit', sans-serif;
            font-size: 15px;
            color: #1f2937;
            background: #f9fafb;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .kna-input:focus {
            border-color: #4B4899;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(75,72,153,0.12);
        }
        .kna-input::placeholder { color: #9ca3af; }
        .kna-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .kna-label .req { color: #ef4444; }
        .role-card { cursor: pointer; }
        .role-card input:checked ~ .role-body {
            border-color: #4B4899;
            background: #eef0fa;
        }
        .role-body {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 16px;
            transition: border-color .2s, background .2s;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Top Bar -->
    <div class="bg-white border-b border-gray-200 py-3 px-6 flex items-center justify-between">
        <a href="/index.php">
            <img src="/uploads/logo-kna.png" alt="KNA Interpharma" class="h-10 w-auto"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <span class="hidden font-bold text-xl" style="color:#4B4899;">KNA Interpharma</span>
        </a>
        <a href="/index.php" class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition">
            <i class="fa-solid fa-arrow-left"></i>
            กลับหน้าแรก
        </a>
    </div>

    <!-- Main Content -->
    <div class="flex justify-center py-12 px-4">
        <div class="w-full max-w-2xl">

            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">สมัครสมาชิก</h1>
                <p class="text-gray-500">กรอกข้อมูลเพื่อสร้างบัญชีกับ KNA Interpharma</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">

                <?php if (!empty($errors)): ?>
                    <div class="mb-6 rounded-xl bg-red-50 border border-red-200 px-5 py-4">
                        <ul class="list-disc pl-5 space-y-1 text-sm text-red-700">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo h($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="/register.php" class="space-y-5">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="redirect" value="<?php echo h($redirect); ?>">

                    <!-- Name row -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="kna-label">ชื่อ <span class="req">*</span></label>
                            <input class="kna-input" type="text" name="first_name"
                                value="<?php echo h($first_name); ?>" required placeholder="ชื่อ">
                        </div>
                        <div>
                            <label class="kna-label">นามสกุล <span class="req">*</span></label>
                            <input class="kna-input" type="text" name="last_name"
                                value="<?php echo h($last_name); ?>" required placeholder="นามสกุล">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="kna-label">อีเมล <span class="req">*</span></label>
                        <input class="kna-input" type="email" name="email"
                            value="<?php echo h($email); ?>" autocomplete="email" required placeholder="email@example.com">
                    </div>

                    <!-- Doctor License -->
                    <div>
                        <label class="kna-label">เลขใบประกอบวิชาชีพแพทย์</label>
                        <input class="kna-input" type="text" name="doctor_license_no"
                            value="<?php echo h($doctor_license_no); ?>" id="doctorLicenseInput"
                            placeholder="กรอกเฉพาะแพทย์ (ถ้ามี)">
                    </div>

                    <!-- Hospital / Clinic -->
                    <div>
                        <label class="kna-label">โรงพยาบาล / คลินิก</label>
                        <input class="kna-input" type="text" name="hospital_clinic"
                            value="<?php echo h($hospital_clinic); ?>" placeholder="ชื่อสถานพยาบาลที่สังกัด">
                    </div>

                    <!-- Province & Phone row -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="kna-label">เขต / จังหวัด</label>
                            <input class="kna-input" type="text" name="province"
                                value="<?php echo h($province); ?>" placeholder="เช่น กรุงเทพมหานคร">
                        </div>
                        <div>
                            <label class="kna-label">เบอร์โทรศัพท์</label>
                            <input class="kna-input" type="tel" name="phone"
                                value="<?php echo h($phone); ?>" placeholder="08X-XXX-XXXX">
                        </div>
                    </div>

                    <!-- Line ID -->
                    <div>
                        <label class="kna-label">ไลน์ไอดี (Line ID)</label>
                        <input class="kna-input" type="text" name="line_id"
                            value="<?php echo h($line_id); ?>" placeholder="@lineid (ถ้ามี)">
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="kna-label">ประเภทการสมัคร <span class="req">*</span></label>
                        <div class="grid grid-cols-2 gap-3 mt-1">
                            <label class="role-card">
                                <input type="radio" name="requested_role" value="member"
                                    class="hidden" <?php echo $requested_role !== 'doctor' ? 'checked' : ''; ?>>
                                <div class="role-body">
                                    <div class="font-semibold text-gray-800 text-sm">สมาชิกทั่วไป</div>
                                    <div class="text-xs text-gray-500 mt-1">เข้าถึงวิดีโอและเนื้อหาระดับสมาชิก</div>
                                </div>
                            </label>
                            <label class="role-card">
                                <input type="radio" name="requested_role" value="doctor"
                                    class="hidden" <?php echo $requested_role === 'doctor' ? 'checked' : ''; ?>>
                                <div class="role-body">
                                    <div class="font-semibold text-gray-800 text-sm">แพทย์</div>
                                    <div class="text-xs text-gray-500 mt-1">ระบุเลข อว. เพื่อขอสิทธิ์พิเศษ</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Password row -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="kna-label">รหัสผ่าน <span class="req">*</span></label>
                            <input class="kna-input" type="password" name="password"
                                minlength="6" autocomplete="new-password" required placeholder="อย่างน้อย 6 ตัวอักษร">
                        </div>
                        <div>
                            <label class="kna-label">ยืนยันรหัสผ่าน <span class="req">*</span></label>
                            <input class="kna-input" type="password" name="password_confirm"
                                minlength="6" autocomplete="new-password" required placeholder="กรอกรหัสผ่านอีกครั้ง">
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="flex items-start gap-3 pt-1">
                        <input type="checkbox" id="terms" name="terms" required
                            class="mt-1 w-4 h-4 rounded border-gray-300 cursor-pointer shrink-0"
                            style="accent-color:#4B4899;">
                        <label for="terms" class="text-sm text-gray-600 cursor-pointer">
                            ฉันยอมรับ <a href="#" class="font-semibold hover:underline" style="color:#4B4899;">เงื่อนไขการใช้งาน</a>
                            และ <a href="#" class="font-semibold hover:underline" style="color:#4B4899;">นโยบายความเป็นส่วนตัว</a>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full py-3 rounded-xl text-white font-bold text-base transition hover:opacity-90 active:scale-95 mt-2"
                        style="background:#4B4899;">
                        สมัครสมาชิก
                    </button>
                </form>

                <div class="mt-6 text-center text-sm text-gray-500">
                    มีบัญชีอยู่แล้ว?
                    <a href="/login.php?redirect=<?php echo urlencode($redirect); ?>"
                       class="font-bold hover:underline" style="color:#4B4899;">
                        เข้าสู่ระบบ
                    </a>
                </div>
            </div>

        </div>
    </div>

    <script>
    (function () {
        const roleInputs = document.querySelectorAll('input[name="requested_role"]');
        const licenseInput = document.getElementById('doctorLicenseInput');
        if (!roleInputs.length || !licenseInput) return;

        const sync = () => {
            const isDoctor = Array.from(roleInputs).some(r => r.checked && r.value === 'doctor');
            licenseInput.required = isDoctor;
            licenseInput.closest('div').style.opacity = isDoctor ? '1' : '0.6';
        };

        roleInputs.forEach(r => r.addEventListener('change', sync));
        sync();
    })();
    </script>
</body>
</html>
