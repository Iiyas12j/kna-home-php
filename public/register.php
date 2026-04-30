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

$db_ready = $pdo instanceof PDO;
$errors = [];
$name = '';
$email = '';
$requested_role = 'member';
$doctor_license_no = '';
$redirect = frontend_register_redirect($_REQUEST['redirect'] ?? '/video-tiktok.php');

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

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $requested_role = member_registration_role($_POST['requested_role'] ?? 'member');
    $doctor_license_no = trim((string) ($_POST['doctor_license_no'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $password_confirm = (string) ($_POST['password_confirm'] ?? '');

    if (!$db_ready) {
        $errors[] = 'ระบบฐานข้อมูลยังไม่พร้อมใช้งาน';
    }

    if ($name === '') {
        $errors[] = 'กรุณากรอกชื่อ';
    }

    if ($email === '') {
        $errors[] = 'กรุณากรอกอีเมล';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
    }

    if ($requested_role === 'doctor' && $doctor_license_no === '') {
        $errors[] = 'กรุณากรอกเลข อว. สำหรับการสมัครแพทย์';
    }

    if ($password === '') {
        $errors[] = 'กรุณากรอกรหัสผ่าน';
    } elseif (strlen($password) < 6) {
        $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
    }

    if ($password !== $password_confirm) {
        $errors[] = 'ยืนยันรหัสผ่านไม่ตรงกัน';
    }

    if ($errors === []) {
        $registerError = null;
        if (member_register(
            $pdo,
            $name,
            $email,
            $password,
            [
                'requested_role' => $requested_role,
                'doctor_license_no' => $doctor_license_no,
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
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>สมัครสมาชิก - KNA Interpharma</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #081b2a;
            --panel: #091a28;
            --line: rgba(129, 140, 248, 0.88);
            --text: #ffffff;
            --muted: rgba(226, 232, 240, 0.78);
            --input: rgba(255, 255, 255, 0.9);
            --danger-bg: rgba(127, 29, 29, 0.3);
            --danger-line: rgba(252, 165, 165, 0.35);
            --danger-text: #fecaca;
            --glow: 0 0 24px rgba(129, 140, 248, 0.28), 0 0 58px rgba(124, 58, 237, 0.16);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Kanit', sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 50% 50%, rgba(79, 70, 229, 0.16), transparent 28%),
                radial-gradient(circle at top right, rgba(124, 58, 237, 0.16), transparent 24%),
                linear-gradient(180deg, #081524 0%, var(--bg) 100%);
        }

        .register-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 28px 16px;
        }

        .register-frame {
            position: relative;
            width: min(1180px, 100%);
            min-height: 700px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(360px, 0.82fr);
            background: rgba(8, 20, 33, 0.96);
            border: 4px solid var(--line);
            box-shadow: var(--glow);
            overflow: hidden;
        }

        .register-form-side {
            position: relative;
            z-index: 2;
            padding: 42px 52px 40px;
            background: linear-gradient(180deg, rgba(8, 24, 38, 0.98) 0%, rgba(8, 24, 38, 0.98) 100%);
        }

        .register-brand img {
            width: 168px;
            height: auto;
            display: block;
            margin-bottom: 24px;
        }

        .register-title {
            margin: 0;
            font-size: clamp(36px, 4vw, 52px);
            line-height: 1.08;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .register-copy {
            margin: 14px 0 0;
            max-width: 470px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.75;
        }

        .register-errors {
            margin-top: 22px;
            padding: 14px 16px;
            border: 1px solid var(--danger-line);
            background: var(--danger-bg);
            color: var(--danger-text);
        }

        .register-errors ul {
            margin: 0;
            padding-left: 20px;
            line-height: 1.7;
        }

        .register-form {
            margin-top: 28px;
            max-width: 430px;
        }

        .register-grid {
            display: grid;
            gap: 20px;
        }

        .register-field label,
        .register-role__label {
            display: block;
            margin-bottom: 10px;
            color: rgba(255, 255, 255, 0.92);
            font-size: 15px;
            font-weight: 500;
        }

        .register-input {
            width: 100%;
            padding: 10px 0 12px;
            border: 0;
            border-bottom: 2px solid rgba(255, 255, 255, 0.72);
            background: transparent;
            color: var(--input);
            font: inherit;
            font-size: 18px;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .register-input::placeholder {
            color: rgba(255, 255, 255, 0.34);
        }

        .register-input:focus {
            border-bottom-color: var(--line);
            box-shadow: 0 8px 0 -6px rgba(129, 140, 248, 0.55);
        }

        .register-role__options {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .register-role__option input {
            display: none;
        }

        .register-role__card {
            display: block;
            padding: 14px 16px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.04);
            cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease;
        }

        .register-role__card strong {
            display: block;
            font-size: 16px;
        }

        .register-role__card span {
            display: block;
            margin-top: 4px;
            color: rgba(226, 232, 240, 0.72);
            font-size: 13px;
            line-height: 1.5;
        }

        .register-role__option input:checked + .register-role__card {
            border-color: rgba(129, 140, 248, 0.92);
            background: rgba(99, 102, 241, 0.12);
            transform: translateY(-1px);
        }

        .doctor-license {
            display: none;
        }

        .doctor-license.is-visible {
            display: block;
        }

        .register-note {
            margin-top: 22px;
            color: rgba(255, 255, 255, 0.56);
            font-size: 13px;
            line-height: 1.8;
            max-width: 430px;
        }

        .register-button {
            width: 100%;
            margin-top: 8px;
            min-height: 58px;
            border: 0;
            border-radius: 999px;
            background: linear-gradient(90deg, #4338ca 0%, #5b4df0 54%, #7c3aed 100%);
            color: #fff;
            font: inherit;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, filter 0.2s ease;
        }

        .register-button:hover {
            transform: translateY(-1px);
            filter: brightness(1.04);
        }

        .register-links {
            margin-top: 24px;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.9;
            font-size: 15px;
        }

        .register-links a {
            color: #a5b4fc;
            text-decoration: none;
            font-weight: 700;
        }

        .register-links a:hover {
            color: #c4b5fd;
        }

        .register-visual {
            position: relative;
            overflow: hidden;
        }

        .register-visual__shape {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #4338ca 0%, #6366f1 46%, #9333ea 100%);
            clip-path: polygon(18% 0, 100% 0, 100% 100%, 70% 100%);
            box-shadow: inset 0 0 24px rgba(255, 255, 255, 0.14);
        }

        .register-visual__content {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 48px;
            text-align: center;
        }

        .register-visual__logo {
            width: min(240px, 56%);
            opacity: 0.16;
            filter: brightness(0) invert(1);
            margin-bottom: 24px;
        }

        .register-visual__title {
            margin: 0;
            font-size: clamp(40px, 4.8vw, 68px);
            line-height: 1;
            font-weight: 800;
        }

        .register-visual__text {
            margin: 16px 0 0;
            max-width: 340px;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.7;
            font-size: 16px;
        }

        @media (max-width: 960px) {
            .register-frame {
                grid-template-columns: 1fr;
            }

            .register-visual {
                min-height: 220px;
                order: -1;
            }

            .register-visual__shape {
                clip-path: polygon(0 0, 100% 0, 100% 100%, 18% 100%);
            }
        }

        @media (max-width: 560px) {
            .register-page {
                padding: 0;
            }

            .register-frame {
                width: 100%;
                min-height: 100vh;
                border-width: 0;
                box-shadow: none;
            }

            .register-form-side {
                padding: 30px 22px 28px;
            }

            .register-role__options {
                grid-template-columns: 1fr;
            }

            .register-brand img {
                width: 144px;
            }
        }
    </style>
</head>
<body>
    <main class="register-page">
        <section class="register-frame">
            <div class="register-form-side">
                <div class="register-brand">
                    <img src="/uploads/logo-kna.png" alt="KNA Interpharma">
                </div>

                <h1 class="register-title">สมัครสมาชิก</h1>
                <p class="register-copy">
                    สร้างบัญชีเพื่อเข้าถึงพื้นที่เรียนรู้ของ KNA Interpharma หากคุณเป็นแพทย์
                    สามารถแจ้งความประสงค์พร้อมกรอกเลข อว. เพื่อให้ทีมงานตรวจสอบและอนุมัติสิทธิ์เพิ่มเติมได้
                </p>

                <?php if ($errors !== []): ?>
                    <div class="register-errors">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo h($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form class="register-form" method="post" action="/register.php">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="redirect" value="<?php echo h($redirect); ?>">

                    <div class="register-grid">
                        <div class="register-field">
                            <label for="name">ชื่อ - นามสกุล</label>
                            <input class="register-input" id="name" name="name" type="text" value="<?php echo h($name); ?>" required>
                        </div>

                        <div class="register-field">
                            <label for="email">อีเมล</label>
                            <input class="register-input" id="email" name="email" type="email" value="<?php echo h($email); ?>" autocomplete="email" required>
                        </div>

                        <div class="register-role">
                            <div class="register-role__label">ประเภทการสมัคร</div>
                            <div class="register-role__options">
                                <label class="register-role__option">
                                    <input type="radio" name="requested_role" value="member" <?php echo $requested_role !== 'doctor' ? 'checked' : ''; ?>>
                                    <span class="register-role__card">
                                        <strong>สมาชิกทั่วไป</strong>
                                        <span>สำหรับผู้ใช้งานที่ต้องการเข้าถึงวิดีโอและเนื้อหาระดับสมาชิก</span>
                                    </span>
                                </label>
                                <label class="register-role__option">
                                    <input type="radio" name="requested_role" value="doctor" <?php echo $requested_role === 'doctor' ? 'checked' : ''; ?>>
                                    <span class="register-role__card">
                                        <strong>แพทย์</strong>
                                        <span>ระบุเลข อว. เพื่อส่งคำขอให้ทีมงานตรวจสอบและอนุมัติสิทธิ์ doctor</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="register-field doctor-license <?php echo $requested_role === 'doctor' ? 'is-visible' : ''; ?>" id="doctorLicenseField">
                            <label for="doctor_license_no">เลข อว.</label>
                            <input class="register-input" id="doctor_license_no" name="doctor_license_no" type="text" value="<?php echo h($doctor_license_no); ?>" placeholder="กรอกเลข อว. ของแพทย์">
                        </div>

                        <div class="register-field">
                            <label for="password">รหัสผ่าน</label>
                            <input class="register-input" id="password" name="password" type="password" minlength="6" autocomplete="new-password" required>
                        </div>

                        <div class="register-field">
                            <label for="password_confirm">ยืนยันรหัสผ่าน</label>
                            <input class="register-input" id="password_confirm" name="password_confirm" type="password" minlength="6" autocomplete="new-password" required>
                        </div>
                    </div>

                    <button class="register-button" type="submit">สร้างบัญชีสมาชิก</button>
                </form>

                <div class="register-links">
                    มีบัญชีอยู่แล้ว? <a href="/login.php?redirect=<?php echo urlencode($redirect); ?>">เข้าสู่ระบบ</a><br>
                    กลับไปยัง <a href="<?php echo h($redirect); ?>">หน้าก่อนหน้า</a>
                </div>

                <div class="register-note">
                    บัญชีที่สมัครใหม่จะถูกสร้างเป็น member ก่อนเสมอ หากสมัครในฐานะแพทย์ ระบบจะบันทึกเลข อว.
                    และคำขอสิทธิ์ไว้เพื่อให้ผู้ดูแลตรวจสอบก่อนอนุมัติเป็น doctor
                </div>
            </div>

            <div class="register-visual">
                <div class="register-visual__shape"></div>
                <div class="register-visual__content">
                    <img class="register-visual__logo" src="/uploads/logo-kna.png" alt="">
                    <h2 class="register-visual__title">Create</h2>
                    <p class="register-visual__text">
                        Register with a clean clinical experience aligned with the KNA website.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <script>
        (function () {
            const roleInputs = document.querySelectorAll('input[name="requested_role"]');
            const doctorField = document.getElementById('doctorLicenseField');
            const doctorInput = document.getElementById('doctor_license_no');

            if (!roleInputs.length || !doctorField || !doctorInput) {
                return;
            }

            const syncDoctorField = () => {
                const isDoctor = Array.from(roleInputs).some((input) => input.checked && input.value === 'doctor');
                doctorField.classList.toggle('is-visible', isDoctor);
                doctorInput.required = isDoctor;
                if (!isDoctor) {
                    doctorInput.value = '';
                }
            };

            roleInputs.forEach((input) => input.addEventListener('change', syncDoctorField));
            syncDoctorField();
        })();
    </script>
</body>
</html>
