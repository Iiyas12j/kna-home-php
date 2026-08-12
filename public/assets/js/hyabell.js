// HYABELL cinematic family page behaviour. Scoped to .hy-page — no globals
// besides the one IIFE below. Progressive enhancement: the DOM is fully
// readable/usable with JS disabled (real text, real links, real <img> tiles).
(function () {
    'use strict';

    var root = document.querySelector('.hy-page');
    if (!root) return;

    document.documentElement.classList.remove('no-js');

    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    // ── Ambient hero video ───────────────────────────────────────────────
    (function initHeroVideo() {
        var heroVideo = root.querySelector('.hy-hero-video');
        if (!heroVideo) return;
        if (reducedMotion) { heroVideo.pause(); heroVideo.removeAttribute('autoplay'); return; }

        var hero = root.querySelector('.hy-hero');
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) heroVideo.play().catch(function () {});
                else heroVideo.pause();
            });
        }, { threshold: 0 });
        if (hero) io.observe(hero);
    })();

    // ── Variant cards: staggered reveal + cursor-tilt on the product image ──
    (function initVariantCards() {
        var cards = Array.prototype.slice.call(root.querySelectorAll('.hy-variant'));
        if (!cards.length) return;

        if (!reducedMotion) {
            cards.forEach(function (card) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(36px)';
            });

            var io = new IntersectionObserver(function (entries, obs) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        obs.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.2 });
            cards.forEach(function (card) { io.observe(card); });
        }

        cards.forEach(function (card) {
            var stage = card.querySelector('.hy-variant-stage');
            if (!stage || reducedMotion) return;
            stage.addEventListener('pointermove', function (e) {
                if (e.pointerType && e.pointerType !== 'mouse' && e.pointerType !== 'pen') return;
                var rect = stage.getBoundingClientRect();
                var nx = (e.clientX - rect.left) / rect.width - 0.5;
                var ny = (e.clientY - rect.top) / rect.height - 0.5;
                stage.style.transform = 'perspective(900px) rotateX(' + (-ny * 8).toFixed(2) + 'deg) rotateY(' + (nx * 8).toFixed(2) + 'deg)';
            }, { passive: true });
            stage.addEventListener('pointerleave', function () {
                stage.style.transform = '';
            });
        });
    })();

    // ── Cursor light + hero-ready reveal ─────────────────────────────────
    // Checked per-event via e.pointerType rather than a static
    // matchMedia('(pointer: fine)') gate — that upfront check can evaluate
    // inconsistently across browsers on the same machine. Reading the actual
    // event's pointerType is authoritative and can't get stuck off.
    window.addEventListener('pointermove', function (e) {
        if (e.pointerType && e.pointerType !== 'mouse' && e.pointerType !== 'pen') return;
        root.style.setProperty('--hy-mouse-x', e.clientX + 'px');
        root.style.setProperty('--hy-mouse-y', e.clientY + 'px');
    }, { passive: true });

    window.requestAnimationFrame(function () {
        window.requestAnimationFrame(function () { root.classList.add('hy-is-ready'); });
    });

    // ── Canvas particle field ────────────────────────────────────────────
    (function initParticles() {
        var canvas = root.querySelector('.hy-particles');
        if (!canvas || reducedMotion) return;
        var ctx = canvas.getContext('2d');
        if (!ctx) return;

        var dpr = Math.min(window.devicePixelRatio || 1, 1.75);
        var particles = [];
        var w = 0, h = 0;

        function resize() {
            w = window.innerWidth;
            h = window.innerHeight;
            canvas.width = Math.floor(w * dpr);
            canvas.height = Math.floor(h * dpr);
            canvas.style.width = w + 'px';
            canvas.style.height = h + 'px';
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

            var count = window.innerWidth < 700 ? 26 : 52;
            particles = Array.from({ length: count }, function () {
                return {
                    x: Math.random() * w,
                    y: Math.random() * h,
                    r: Math.random() * 1.1 + 0.3,
                    speed: Math.random() * 0.2 + 0.05,
                    drift: Math.random() * 0.16 - 0.08,
                    alpha: Math.random() * 0.5 + 0.12,
                    phase: Math.random() * Math.PI * 2,
                };
            });
        }

        function frame(time) {
            ctx.clearRect(0, 0, w, h);
            particles.forEach(function (p) {
                p.y -= p.speed;
                p.x += p.drift;
                if (p.y < -10) p.y = h + 10;
                if (p.x < -10) p.x = w + 10;
                if (p.x > w + 10) p.x = -10;
                var pulse = 0.55 + Math.sin(time * 0.001 + p.phase) * 0.35;
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(180, 210, 255, ' + (p.alpha * pulse) + ')';
                ctx.fill();
            });
            window.requestAnimationFrame(frame);
        }

        resize();
        window.requestAnimationFrame(frame);
        window.addEventListener('resize', resize, { passive: true });
    })();

    // ── Generic scroll reveal ────────────────────────────────────────────
    var revealTargets = root.querySelectorAll('.hy-reveal, .hy-results-list article');
    if (revealTargets.length && !reducedMotion) {
        var io2 = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.14 });
        revealTargets.forEach(function (el) { io2.observe(el); });
    } else {
        revealTargets.forEach(function (el) { el.classList.add('is-visible'); });
    }

    // ── Trust marquee driven by CSS animation only — no JS needed ──────────

    // ── Lightbox (images + full-video triggers) ─────────────────────────────
    (function initLightbox() {
        var imageTriggers = Array.prototype.slice.call(root.querySelectorAll('[data-lightbox-src]'));
        var videoTriggers = Array.prototype.slice.call(root.querySelectorAll('[data-lightbox-video]'));
        var lightbox = root.querySelector('.hy-lightbox');
        if ((!imageTriggers.length && !videoTriggers.length) || !lightbox) return;

        var img = lightbox.querySelector('img');
        var video = lightbox.querySelector('video');
        var closeBtn = lightbox.querySelector('.hy-lightbox__close');
        var lastFocused = null;

        function resetVideo() {
            if (!video) return;
            video.pause();
            video.removeAttribute('src');
            video.load();
        }

        function openImage(src, alt) {
            lastFocused = document.activeElement;
            img.src = src;
            img.alt = alt || '';
            img.hidden = false;
            if (video) { video.hidden = true; resetVideo(); }
            lightbox.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            closeBtn.focus();
            document.addEventListener('keydown', onKeydown);
        }

        function openVideo(src, alt) {
            if (!video) return;
            lastFocused = document.activeElement;
            img.hidden = true;
            video.hidden = false;
            video.src = src;
            video.setAttribute('aria-label', alt || '');
            lightbox.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            closeBtn.focus();
            document.addEventListener('keydown', onKeydown);
            video.play().catch(function () {});
        }

        function close() {
            lightbox.classList.remove('is-open');
            document.body.style.overflow = '';
            document.removeEventListener('keydown', onKeydown);
            resetVideo();
            if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
        }

        function onKeydown(e) {
            if (e.key === 'Escape') close();
        }

        imageTriggers.forEach(function (trigger) {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                openImage(trigger.getAttribute('data-lightbox-src'), trigger.getAttribute('data-lightbox-alt'));
            });
        });

        videoTriggers.forEach(function (trigger) {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                openVideo(trigger.getAttribute('data-lightbox-video'), trigger.getAttribute('data-lightbox-alt'));
            });
        });

        closeBtn && closeBtn.addEventListener('click', close);
        lightbox.addEventListener('click', function (e) { if (e.target === lightbox) close(); });
    })();
})();
