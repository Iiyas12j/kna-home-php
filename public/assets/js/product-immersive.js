// Product Detail — immersive presentation layer behaviour.
// Scope: only touches elements inside .product-immersive. No globals beyond this IIFE.
// Progressive enhancement: every feature here is additive — with JS disabled the
// underlying markup (family detail, gallery images, CTAs) is already visible/usable
// because the PHP template renders it in a no-JS-safe default state.
(function () {
    'use strict';

    var root = document.querySelector('.product-immersive');
    if (!root) return;

    document.documentElement.classList.remove('no-js');

    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ── Canvas particle field ──────────────────────────────────────────────
    (function initParticles() {
        var canvas = root.querySelector('.pi-hero__canvas');
        var hero = root.querySelector('.pi-hero');
        if (!canvas || !hero || reducedMotion) return;

        var ctx = canvas.getContext('2d');
        if (!ctx) return;

        var dpr = Math.min(window.devicePixelRatio || 1, 2);
        var particles = [];
        var running = false;
        var rafId = null;
        var accent = getComputedStyle(root).getPropertyValue('--pi-accent-rgb').trim() || '149, 103, 255';

        var isSmall = window.innerWidth < 700;
        var count = isSmall ? 26 : 60;

        function resize() {
            var w = hero.clientWidth, h = hero.clientHeight;
            canvas.width = w * dpr;
            canvas.height = h * dpr;
            canvas.style.width = w + 'px';
            canvas.style.height = h + 'px';
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        }

        function seed() {
            var w = hero.clientWidth, h = hero.clientHeight;
            particles = [];
            for (var i = 0; i < count; i++) {
                particles.push({
                    x: Math.random() * w,
                    y: Math.random() * h,
                    r: 0.6 + Math.random() * 1.6,
                    vx: (Math.random() - 0.5) * 0.12,
                    vy: (Math.random() - 0.5) * 0.12,
                    a: 0.15 + Math.random() * 0.35,
                });
            }
        }

        function frame() {
            if (!running) return;
            var w = hero.clientWidth, h = hero.clientHeight;
            ctx.clearRect(0, 0, w, h);
            for (var i = 0; i < particles.length; i++) {
                var p = particles[i];
                p.x += p.vx;
                p.y += p.vy;
                if (p.x < 0) p.x = w; if (p.x > w) p.x = 0;
                if (p.y < 0) p.y = h; if (p.y > h) p.y = 0;
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(' + accent + ', ' + p.a + ')';
                ctx.fill();
            }
            rafId = window.requestAnimationFrame(frame);
        }

        function start() {
            if (running) return;
            running = true;
            rafId = window.requestAnimationFrame(frame);
        }
        function stop() {
            running = false;
            if (rafId) window.cancelAnimationFrame(rafId);
        }

        resize();
        seed();

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) start(); else stop();
            });
        }, { threshold: 0 });
        io.observe(hero);

        var resizeTimer = null;
        window.addEventListener('resize', function () {
            window.clearTimeout(resizeTimer);
            resizeTimer = window.setTimeout(function () { resize(); seed(); }, 200);
        }, { passive: true });
    })();

    // ── Hyabell family showcase ─────────────────────────────────────────────
    (function initFamily() {
        var cards = root.querySelectorAll('.pi-family__card');
        if (!cards.length) return;

        var detail = root.querySelector('.pi-family__detail');

        function select(card) {
            cards.forEach(function (c) { c.classList.remove('is-active'); c.setAttribute('aria-selected', 'false'); });
            card.classList.add('is-active');
            card.setAttribute('aria-selected', 'true');

            if (!detail) return;
            var accent = card.getAttribute('data-accent');
            var stats = JSON.parse(card.getAttribute('data-stats') || '[]');
            var summary = card.getAttribute('data-summary') || '';

            if (accent) detail.style.setProperty('--pi-card-accent', accent);
            var statsEl = detail.querySelector('.pi-family__detail-stats');
            var textEl = detail.querySelector('p');
            if (statsEl) {
                statsEl.innerHTML = '';
                stats.forEach(function (s) {
                    var span = document.createElement('span');
                    span.className = 'pi-family__detail-stat';
                    span.textContent = s;
                    statsEl.appendChild(span);
                });
            }
            if (textEl) textEl.textContent = summary;
            detail.classList.add('is-visible');
        }

        cards.forEach(function (card, i) {
            card.setAttribute('role', 'tab');
            card.setAttribute('aria-selected', i === 0 ? 'true' : 'false');
            card.addEventListener('click', function () { select(card); });
        });

        if (cards[0]) select(cards[0]);
    })();

    // ── Lightbox (gallery images + full-video triggers) ──────────────────────
    (function initLightbox() {
        var imageTriggers = Array.prototype.slice.call(root.querySelectorAll('[data-lightbox-src]'));
        var videoTriggers = Array.prototype.slice.call(root.querySelectorAll('[data-lightbox-video]'));
        var lightbox = root.querySelector('.pi-lightbox');
        if ((!imageTriggers.length && !videoTriggers.length) || !lightbox) return;

        var img = lightbox.querySelector('img');
        var video = lightbox.querySelector('video');
        var closeBtn = lightbox.querySelector('.pi-lightbox__close');
        var prevBtn = lightbox.querySelector('.pi-lightbox__prev');
        var nextBtn = lightbox.querySelector('.pi-lightbox__next');
        var currentIndex = -1;
        var lastFocused = null;
        var mode = 'image';

        function resetVideo() {
            if (!video) return;
            video.pause();
            video.removeAttribute('src');
            video.load();
        }

        function showImage(index) {
            if (!imageTriggers.length) return;
            mode = 'image';
            currentIndex = (index + imageTriggers.length) % imageTriggers.length;
            var trigger = imageTriggers[currentIndex];
            img.src = trigger.getAttribute('data-lightbox-src');
            img.alt = trigger.getAttribute('data-lightbox-alt') || '';
            img.hidden = false;
            if (video) { video.hidden = true; resetVideo(); }
            if (prevBtn) prevBtn.hidden = imageTriggers.length < 2;
            if (nextBtn) nextBtn.hidden = imageTriggers.length < 2;
        }

        function showVideo(trigger) {
            if (!video) return;
            mode = 'video';
            img.hidden = true;
            video.hidden = false;
            video.src = trigger.getAttribute('data-lightbox-video');
            video.setAttribute('aria-label', trigger.getAttribute('data-lightbox-alt') || '');
            if (prevBtn) prevBtn.hidden = true;
            if (nextBtn) nextBtn.hidden = true;
            video.play().catch(function () {});
        }

        function open(setup) {
            lastFocused = document.activeElement;
            setup();
            lightbox.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            closeBtn.focus();
            document.addEventListener('keydown', onKeydown);
        }

        function close() {
            lightbox.classList.remove('is-open');
            document.body.style.overflow = '';
            document.removeEventListener('keydown', onKeydown);
            resetVideo();
            if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
        }

        function onKeydown(e) {
            if (e.key === 'Escape') { close(); return; }
            if (mode === 'image') {
                if (e.key === 'ArrowRight') { showImage(currentIndex + 1); return; }
                if (e.key === 'ArrowLeft') { showImage(currentIndex - 1); return; }
            }
            if (e.key === 'Tab') {
                var focusable = [closeBtn, prevBtn, nextBtn].filter(function (el) { return el && !el.hidden; });
                var idx = focusable.indexOf(document.activeElement);
                e.preventDefault();
                var nextIdx = e.shiftKey ? (idx <= 0 ? focusable.length - 1 : idx - 1) : (idx === focusable.length - 1 ? 0 : idx + 1);
                focusable[nextIdx].focus();
            }
        }

        imageTriggers.forEach(function (trigger, i) {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                open(function () { showImage(i); });
            });
        });

        videoTriggers.forEach(function (trigger) {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                open(function () { showVideo(trigger); });
            });
        });

        closeBtn && closeBtn.addEventListener('click', close);
        prevBtn && prevBtn.addEventListener('click', function () { showImage(currentIndex - 1); });
        nextBtn && nextBtn.addEventListener('click', function () { showImage(currentIndex + 1); });
        lightbox.addEventListener('click', function (e) { if (e.target === lightbox) close(); });
    })();

    // ── Scroll reveal ────────────────────────────────────────────────────────
    (function initReveal() {
        var targets = root.querySelectorAll('.pi-section__inner');
        if (!targets.length || reducedMotion) return;

        targets.forEach(function (el) { el.style.opacity = '0'; el.style.transform = 'translateY(16px)'; el.style.transition = 'opacity 600ms cubic-bezier(0.22,1,0.36,1), transform 600ms cubic-bezier(0.22,1,0.36,1)'; });

        var io = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });

        targets.forEach(function (el) { io.observe(el); });
    })();
})();
