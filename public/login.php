<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

function frontend_redirect_target(?string $value): string
{
    $value = trim((string) $value);

    if ($value === '' || $value[0] !== '/' || str_starts_with($value, '//')) {
        return '/video-tiktok.php';
    }

    return $value;
}

$db_ready = $pdo instanceof PDO;
$error = '';
$email = '';
$redirect = frontend_redirect_target($_REQUEST['redirect'] ?? '/video-tiktok.php');

if (is_member_logged_in()) {
    header('Location: ' . $redirect);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!$db_ready) {
        $error = 'ระบบฐานข้อมูลยังไม่พร้อมใช้งาน';
    } elseif ($email === '' || $password === '') {
        $error = 'กรุณากรอกอีเมลและรหัสผ่าน';
    } elseif (member_login($pdo, $email, $password)) {
        header('Location: ' . $redirect);
        exit;
    } else {
        $error = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
    }
}
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>เข้าสู่ระบบสมาชิก - KNA Interpharma</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #081b2a;
            --panel: #091a28;
            --line: rgba(129, 140, 248, 0.88);
            --line-soft: rgba(129, 140, 248, 0.18);
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

        .login-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 28px 16px;
        }

        .login-frame {
            position: relative;
            width: min(1100px, 100%);
            min-height: 650px;
            display: grid;
            grid-template-columns: minmax(0, 0.93fr) minmax(340px, 0.87fr);
            background: rgba(8, 20, 33, 0.96);
            border: 4px solid var(--line);
            box-shadow: var(--glow);
            overflow: hidden;
        }

        .login-form-side {
            position: relative;
            z-index: 2;
            padding: 48px 56px 44px;
            background: linear-gradient(180deg, rgba(8, 24, 38, 0.98) 0%, rgba(8, 24, 38, 0.98) 100%);
        }

        .login-brand {
            display: inline-flex;
            align-items: center;
            margin-bottom: 26px;
        }

        .login-brand img {
            width: 168px;
            height: auto;
            display: block;
        }

        .login-title {
            margin: 0;
            font-size: clamp(38px, 4vw, 54px);
            line-height: 1.08;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .login-copy {
            margin: 14px 0 0;
            max-width: 420px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.8;
        }

        .login-error {
            margin-top: 24px;
            padding: 14px 16px;
            border: 1px solid var(--danger-line);
            background: var(--danger-bg);
            color: var(--danger-text);
            line-height: 1.6;
        }

        .login-form {
            margin-top: 34px;
            max-width: 380px;
        }

        .login-field {
            margin-bottom: 28px;
        }

        .login-label {
            display: block;
            margin-bottom: 10px;
            color: rgba(255, 255, 255, 0.92);
            font-size: 15px;
            font-weight: 500;
        }

        .login-input {
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

        .login-input::placeholder {
            color: rgba(255, 255, 255, 0.34);
        }

        .login-input:focus {
            border-bottom-color: var(--line);
            box-shadow: 0 8px 0 -6px rgba(46, 255, 247, 0.55);
        }

        .login-button {
            width: 100%;
            margin-top: 6px;
            min-height: 58px;
            border: 0;
            border-radius: 999px;
            background: linear-gradient(90deg, #4338ca 0%, #5b4df0 54%, #7c3aed 100%);
            color: #ffffff;
            font: inherit;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.01em;
            cursor: pointer;
            box-shadow: 0 14px 28px rgba(99, 102, 241, 0.18);
            transition: transform 0.2s ease, filter 0.2s ease;
        }

        .login-button:hover {
            transform: translateY(-1px);
            filter: brightness(1.04);
        }

        .login-links {
            margin-top: 28px;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.9;
            font-size: 15px;
        }

        .login-links a {
            color: #a5b4fc;
            text-decoration: none;
            font-weight: 700;
        }

        .login-links a:hover {
            color: #c4b5fd;
        }

        .login-note {
            margin-top: 22px;
            max-width: 420px;
            color: rgba(255, 255, 255, 0.54);
            font-size: 13px;
            line-height: 1.8;
        }

        .login-visual {
            position: relative;
            overflow: hidden;
            background: transparent;
        }

        .login-visual::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(9, 26, 40, 0) 0%, rgba(9, 26, 40, 0.18) 100%);
        }

        .login-visual__shape {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #4338ca 0%, #6366f1 46%, #9333ea 100%);
            clip-path: polygon(18% 0, 100% 0, 100% 100%, 70% 100%);
            box-shadow: inset 0 0 24px rgba(255, 255, 255, 0.14);
        }

        .login-visual__content {
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

        .login-visual__logo {
            width: min(240px, 56%);
            opacity: 0.16;
            filter: brightness(0) invert(1);
            margin-bottom: 28px;
        }

        .login-visual__title {
            margin: 0;
            font-size: clamp(42px, 5vw, 72px);
            line-height: 1;
            font-weight: 800;
            color: #ffffff;
            text-shadow: 0 10px 36px rgba(67, 56, 202, 0.22);
        }

        .login-visual__text {
            margin: 18px 0 0;
            max-width: 340px;
            color: rgba(255, 255, 255, 0.88);
            font-size: 16px;
            line-height: 1.7;
        }

        @media (max-width: 920px) {
            .login-frame {
                grid-template-columns: 1fr;
            }

            .login-visual {
                min-height: 250px;
                order: -1;
            }

            .login-visual__shape {
                clip-path: polygon(0 0, 100% 0, 100% 100%, 18% 100%);
            }

            .login-form-side {
                padding: 34px 24px 30px;
            }
        }

        @media (max-width: 560px) {
            .login-page {
                padding: 0;
            }

            .login-frame {
                min-height: 100vh;
                border-width: 0;
                width: 100%;
                box-shadow: none;
            }

            .login-visual {
                min-height: 190px;
            }

            .login-visual__content {
                padding: 24px;
            }

            .login-visual__title {
                font-size: 38px;
            }

            .login-brand img {
                width: 144px;
            }

            .login-title {
                font-size: 34px;
            }
        }
    </style>
</head>
<body>
    <main class="login-page">
        <section class="login-frame">
            <div class="login-form-side">
                <div class="login-brand">
                    <img src="/uploads/logo-kna.png" alt="KNA Interpharma">
                </div>

                <h1 class="login-title">เข้าสู่ระบบ</h1>
                <p class="login-copy">
                    เข้าถึงพื้นที่สมาชิกของ KNA Interpharma เพื่อรับชมวิดีโอความรู้และเนื้อหาที่จัดระดับสิทธิ์สำหรับสมาชิกและแพทย์
                </p>

                <?php if ($error !== ''): ?>
                    <div class="login-error"><?php echo h($error); ?></div>
                <?php endif; ?>

                <form class="login-form" method="post" action="/login.php">
                    <input type="hidden" name="redirect" value="<?php echo h($redirect); ?>">

                    <div class="login-field">
                        <label class="login-label" for="email">อีเมล</label>
                        <input class="login-input" id="email" name="email" type="email" value="<?php echo h($email); ?>" autocomplete="email" required>
                    </div>

                    <div class="login-field">
                        <label class="login-label" for="password">รหัสผ่าน</label>
                        <input class="login-input" id="password" name="password" type="password" autocomplete="current-password" required>
                    </div>

                    <button class="login-button" type="submit">เข้าสู่ระบบ</button>
                </form>

                <div class="login-links">
                    ยังไม่มีบัญชี? <a href="/register.php?redirect=<?php echo urlencode($redirect); ?>">สมัครสมาชิก</a><br>
                    กลับไปยัง <a href="<?php echo h($redirect); ?>">หน้าก่อนหน้า</a>
                </div>

                <div class="login-note">
                    บัญชีสมาชิกของหน้าเว็บนี้ใช้ข้อมูลจากระบบหลังบ้าน โดยรองรับบทบาทหลักคือ member และ doctor
                </div>
            </div>

            <div class="login-visual">
                <div class="login-visual__shape"></div>
                <div class="login-visual__content">
                    <img class="login-visual__logo" src="/uploads/logo-kna.png" alt="">
                    <h2 class="login-visual__title">KNA Access</h2>
                    <p class="login-visual__text">
                        Simple, elegant, and aligned with the KNA Interpharma digital experience.
                    </p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
