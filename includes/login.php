<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>เข้าสู่ระบบสมาชิก - KNA</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap');
    :root {
      --bg: #f3f5ff;
      --card: #ffffff;
      --primary: #4b4796;
      --primary-dark: #3f3b86;
      --text: #1f2233;
      --muted: #6b7280;
      --line: #e6e8f0;
      --shadow: 0 20px 40px rgba(63, 70, 140, 0.18);
      --radius: 18px;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: 'Sarabun', system-ui, -apple-system, sans-serif;
      color: var(--text);
      background: radial-gradient(1200px 600px at 10% 0%, #eef0ff 0%, var(--bg) 50%, #f9faff 100%);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 18px 32px;
    }
    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 700;
      letter-spacing: 0.2px;
      color: var(--primary);
    }
    .brand .logo {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      background: linear-gradient(135deg, #6d6ad7, #4b4796);
      position: relative;
    }
    .brand .logo::after {
      content: "";
      position: absolute;
      inset: 10px;
      border: 2px solid #fff;
      border-top-color: transparent;
      border-left-color: transparent;
      transform: rotate(45deg);
      border-radius: 4px;
      opacity: 0.9;
    }
    .home-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: #4b5563;
      text-decoration: none;
      font-weight: 500;
    }
    .home-link svg { width: 18px; height: 18px; }

    .wrap {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }
    .card {
      width: 420px;
      max-width: 92vw;
      background: var(--card);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 34px 32px 28px;
    }
    .avatar {
      width: 64px;
      height: 64px;
      margin: 0 auto 10px;
      border-radius: 999px;
      background: #f0ecff;
      display: grid;
      place-items: center;
      color: var(--primary);
    }
    .title {
      text-align: center;
      font-size: 24px;
      font-weight: 700;
      margin: 6px 0 4px;
    }
    .subtitle {
      text-align: center;
      color: var(--muted);
      margin: 0 0 24px;
      font-weight: 400;
    }
    .field { margin-bottom: 16px; }
    .label {
      display: block;
      font-size: 14px;
      color: #4b5563;
      margin-bottom: 8px;
      font-weight: 600;
    }
    .input {
      display: flex;
      align-items: center;
      gap: 10px;
      border: 1px solid var(--line);
      border-radius: 12px;
      padding: 12px 14px;
      background: #fff;
    }
    .input svg { width: 18px; height: 18px; color: #9aa1b1; }
    .input input {
      border: none;
      outline: none;
      font-size: 15px;
      width: 100%;
      font-family: inherit;
    }
    .row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 13px;
      margin: 4px 0 18px;
    }
    .row a { color: var(--primary); text-decoration: none; font-weight: 600; }
    .btn {
      width: 100%;
      border: none;
      background: var(--primary);
      color: #fff;
      font-size: 16px;
      padding: 12px 16px;
      border-radius: 12px;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      cursor: pointer;
      box-shadow: 0 10px 18px rgba(75, 71, 150, 0.25);
    }
    .btn:hover { background: var(--primary-dark); }
    .divider {
      display: flex;
      align-items: center;
      gap: 12px;
      color: #9aa1b1;
      font-size: 13px;
      margin: 18px 0 10px;
    }
    .divider::before,
    .divider::after {
      content: "";
      height: 1px;
      background: #e4e7f0;
      flex: 1;
    }
    .foot {
      text-align: center;
      font-size: 13px;
      color: #6b7280;
    }
    .foot a { color: var(--primary); text-decoration: none; font-weight: 600; }
    @media (max-width: 520px) {
      .topbar { padding: 14px 18px; }
      .card { padding: 26px 22px; }
    }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <div class="logo" aria-hidden="true"></div>
      <div>
        <div>KNA</div>
        <div style="font-size:12px; letter-spacing:1.4px; color:#7b81b8;">INTERPHARMA</div>
      </div>
    </div>
    <a class="home-link" href="#">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5L12 3l9 7.5"></path><path d="M5 10v10h14V10"></path></svg>
      กลับหน้าแรก
    </a>
  </header>

  <main class="wrap">
    <section class="card">
      <div class="avatar">
        <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor" aria-hidden="true"><path d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm0 2c-3.33 0-10 1.67-10 5v3h20v-3c0-3.33-6.67-5-10-5z"/></svg>
      </div>
      <h1 class="title">เข้าสู่ระบบสมาชิก</h1>
      <p class="subtitle">ยินดีต้อนรับกลับมา!</p>

      <form>
        <div class="field">
          <label class="label">ชื่อผู้ใช้ หรือ อีเมล</label>
          <div class="input">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <input type="text" placeholder="username หรือ email@example.com" />
          </div>
        </div>

        <div class="field">
          <label class="label">รหัสผ่าน</label>
          <div class="input">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input type="password" placeholder="••••••••" />
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
          </div>
        </div>

        <div class="row">
          <label><input type="checkbox" /> จดจำฉันไว้</label>
          <a href="#">ลืมรหัสผ่าน?</a>
        </div>

        <button class="btn" type="submit">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3h7v7"/><path d="M21 3l-9 9"/><path d="M5 12v8h8"/></svg>
          เข้าสู่ระบบ
        </button>
      </form>

      <div class="divider">หรือ</div>
      <div class="foot">
        ยังไม่มีบัญชี? <a href="#">สมัครสมาชิก</a>
      </div>
    </section>
  </main>
</body>
</html>
