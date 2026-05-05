<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/helpers.php';

$siteHeaderActive = $siteHeaderActive ?? '';
$siteHeaderRequestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
if ($siteHeaderRequestUri === '' || $siteHeaderRequestUri[0] !== '/' || str_starts_with($siteHeaderRequestUri, '//')) {
    $siteHeaderRequestUri = '/';
}

$siteHeaderMember = current_member();
$siteHeaderLoginUrl = '/login.php?redirect=' . urlencode($siteHeaderRequestUri);
$siteHeaderLogoutUrl = '/logout.php?redirect=' . urlencode($siteHeaderRequestUri);
$siteHeaderLinks = [
    ['key' => 'home', 'label' => 'Home', 'href' => '/index.php'],
    ['key' => 'clinic', 'label' => 'Find A Clinic', 'href' => '/searchpage.php'],
    ['key' => 'trainer', 'label' => 'Trainer', 'href' => '/doctors_directory.php'],
    ['key' => 'about', 'label' => 'About Us', 'href' => '/about-us.php'],
    ['key' => 'product', 'label' => 'Product', 'href' => '/product.php'],
    ['key' => 'news', 'label' => 'News & Event', 'href' => '/news-event.php'],
    ['key' => 'vdo', 'label' => 'VDO', 'href' => '/video-tiktok.php'],
    ['key' => 'contact', 'label' => 'Contact Us', 'href' => '/contact.php'],
];

if (!function_exists('site_header_link_classes')) {
    function site_header_link_classes(string $key, string $active, bool $mobile = false): string
    {
        $classes = $mobile ? 'kna-site-header__mobile-link' : 'kna-site-header__link';
        if ($key === $active) {
            $classes .= ' is-active';
        }

        return $classes;
    }
}
?>
<style>
    .kna-site-header {
        position: sticky;
        top: 0;
        z-index: 50;
        background: #fff;
        border-bottom: 1px solid rgba(226, 232, 240, 0.95);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
    }

    .kna-site-header__inner {
        max-width: 1280px;
        margin: 0 auto;
        padding: 18px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
    }

    .kna-site-header__brand {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
    }

    .kna-site-header__brand img {
        display: block;
        width: auto;
        height: 52px;
        max-width: min(100%, 210px);
    }

    .kna-site-header__nav {
        flex: 1 1 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        min-width: 0;
    }

    .kna-site-header__link {
        color: #334155;
        text-decoration: none;
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.2;
        padding: 8px 6px;
        white-space: nowrap;
        transition: color 0.2s ease;
    }

    .kna-site-header__link:hover,
    .kna-site-header__link.is-active {
        color: #4338ca;
    }

    .kna-site-header__actions {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
    }

    .kna-site-header__account {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .kna-site-header__account-text {
        text-align: right;
        line-height: 1.2;
    }

    .kna-site-header__account-text strong,
    .kna-site-header__account-text span {
        display: block;
    }

    .kna-site-header__account-text strong {
        color: #0f172a;
        font-size: 0.94rem;
        font-weight: 700;
    }

    .kna-site-header__account-text span {
        color: #64748b;
        font-size: 0.76rem;
    }

    .kna-site-header__button,
    .kna-site-header__logout {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        text-decoration: none;
        font-size: 0.98rem;
        font-weight: 700;
        padding: 12px 20px;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
    }

    .kna-site-header__button {
        color: #fff;
        background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
        box-shadow: 0 12px 24px rgba(79, 70, 229, 0.22);
    }

    .kna-site-header__button:hover,
    .kna-site-header__logout:hover {
        transform: translateY(-1px);
    }

    .kna-site-header__logout {
        color: #334155;
        background: #fff;
        border: 1px solid #cbd5e1;
    }

    .kna-site-header__logout:hover {
        color: #4338ca;
        border-color: #a5b4fc;
    }

    .kna-site-header__icon {
        display: inline-flex;
        width: 18px;
        height: 18px;
    }

    .kna-site-header__toggle {
        display: none;
        width: 48px;
        height: 48px;
        padding: 0;
        border: 0;
        border-radius: 14px;
        background: #f8fafc;
        color: #1f2937;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .kna-site-header__toggle:hover {
        background: #eef2ff;
        color: #4338ca;
    }

    .kna-site-header__toggle svg {
        width: 22px;
        height: 22px;
    }

    .kna-site-header__mobile {
        display: none;
        padding: 0 24px 22px;
        border-top: 1px solid #e2e8f0;
        background: #fff;
    }

    .kna-site-header__mobile.is-open {
        display: block;
    }

    .kna-site-header__mobile-nav {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding-top: 16px;
    }

    .kna-site-header__mobile-link {
        color: #334155;
        text-decoration: none;
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.2;
        padding: 12px 14px;
        border-radius: 14px;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .kna-site-header__mobile-link:hover,
    .kna-site-header__mobile-link.is-active {
        color: #4338ca;
        background: #eef2ff;
    }

    .kna-site-header__mobile-account {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 10px 0 0;
    }

    .kna-site-header__mobile-account .kna-site-header__account-text {
        text-align: left;
    }

    @media (max-width: 1180px) {
        .kna-site-header__nav {
            gap: 10px;
        }

        .kna-site-header__link {
            font-size: 0.94rem;
        }
    }

    @media (max-width: 980px) {
        .kna-site-header__nav,
        .kna-site-header__actions {
            display: none;
        }

        .kna-site-header__toggle {
            display: inline-flex;
        }
    }

    @media (max-width: 640px) {
        .kna-site-header__inner {
            padding: 14px 16px;
        }

        .kna-site-header__mobile {
            padding: 0 16px 18px;
        }

        .kna-site-header__brand img {
            height: 44px;
        }
    }
</style>
<header class="kna-site-header" role="banner">
    <div class="kna-site-header__inner">
        <a class="kna-site-header__brand" href="/index.php" aria-label="KNA Interpharma Home">
            <img src="/uploads/logo-kna.png" alt="KNA Interpharma">
        </a>

        <nav class="kna-site-header__nav" aria-label="Primary navigation">
            <?php foreach ($siteHeaderLinks as $siteHeaderLink): ?>
                <a
                    href="<?php echo h($siteHeaderLink['href']); ?>"
                    class="<?php echo h(site_header_link_classes($siteHeaderLink['key'], $siteHeaderActive)); ?>"
                >
                    <?php echo h($siteHeaderLink['label']); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="kna-site-header__actions">
            <?php if ($siteHeaderMember !== null): ?>
                <div class="kna-site-header__account">
                    <div class="kna-site-header__account-text">
                        <strong><?php echo h($siteHeaderMember['name'] ?: $siteHeaderMember['email']); ?></strong>
                        <span><?php echo h(member_role_label($siteHeaderMember['role'] ?? 'member')); ?></span>
                    </div>
                    <a class="kna-site-header__logout" href="<?php echo h($siteHeaderLogoutUrl); ?>">
                        <span class="kna-site-header__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <path d="M16 17l5-5-5-5"></path>
                                <path d="M21 12H9"></path>
                            </svg>
                        </span>
                        <span>ออกจากระบบ</span>
                    </a>
                </div>
            <?php else: ?>
                <a class="kna-site-header__button" href="<?php echo h($siteHeaderLoginUrl); ?>">
                    <span class="kna-site-header__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"></path>
                        </svg>
                    </span>
                    <span>เข้าสู่ระบบ</span>
                </a>
            <?php endif; ?>
        </div>

        <button
            class="kna-site-header__toggle"
            type="button"
            aria-expanded="false"
            aria-controls="kna-site-header-mobile-menu"
            data-kna-site-header-toggle
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 6h18"></path>
                <path d="M3 12h18"></path>
                <path d="M3 18h18"></path>
            </svg>
        </button>
    </div>

    <div class="kna-site-header__mobile" id="kna-site-header-mobile-menu" data-kna-site-header-menu>
        <nav class="kna-site-header__mobile-nav" aria-label="Mobile navigation">
            <?php foreach ($siteHeaderLinks as $siteHeaderLink): ?>
                <a
                    href="<?php echo h($siteHeaderLink['href']); ?>"
                    class="<?php echo h(site_header_link_classes($siteHeaderLink['key'], $siteHeaderActive, true)); ?>"
                >
                    <?php echo h($siteHeaderLink['label']); ?>
                </a>
            <?php endforeach; ?>

            <div class="kna-site-header__mobile-account">
                <?php if ($siteHeaderMember !== null): ?>
                    <div class="kna-site-header__account-text">
                        <strong><?php echo h($siteHeaderMember['name'] ?: $siteHeaderMember['email']); ?></strong>
                        <span><?php echo h(member_role_label($siteHeaderMember['role'] ?? 'member')); ?></span>
                    </div>
                    <a class="kna-site-header__logout" href="<?php echo h($siteHeaderLogoutUrl); ?>">ออกจากระบบ</a>
                <?php else: ?>
                    <a class="kna-site-header__button" href="<?php echo h($siteHeaderLoginUrl); ?>">เข้าสู่ระบบ</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>
<script>
    (function () {
        var toggle = document.querySelector('[data-kna-site-header-toggle]');
        var menu = document.querySelector('[data-kna-site-header-menu]');

        if (!toggle || !menu || toggle.dataset.bound === '1') {
            return;
        }

        toggle.dataset.bound = '1';
        toggle.addEventListener('click', function () {
            var isOpen = menu.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    })();
</script>
