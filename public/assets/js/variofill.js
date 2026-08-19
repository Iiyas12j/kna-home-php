// VARIOFILL cinematic page behaviour. Scoped to .vf-page — no globals besides
// the one IIFE below. Progressive enhancement: the DOM is fully readable/usable
// with JS disabled (real text, real links, real <img> gallery tiles).
(function () {
    'use strict';

    var root = document.querySelector('.vf-page');
    if (!root) return;

    document.documentElement.classList.remove('no-js');

    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    // ── Scroll-scrubbed scene progress ───────────────────────────────────
    function sceneProgress(el) {
        if (!el) return 0;
        var rect = el.getBoundingClientRect();
        var travel = Math.max(1, rect.height - window.innerHeight);
        return clamp(-rect.top / travel, 0, 1);
    }

    // ── Statement: scroll-track progress drives the word-lighting instead of
    // the words' own (pinned, unchanging) position once the section is sticky.
    var statementScene = root.querySelector('.vf-statement');
    var statementProgress = 0;
    var frameRequested = false;

    function updateScrollState() {
        statementProgress = statementScene ? sceneProgress(statementScene) : 1;
        updateWordLighting();
        frameRequested = false;
    }

    function requestScrollUpdate() {
        if (frameRequested) return;
        frameRequested = true;
        window.requestAnimationFrame(updateScrollState);
    }

    window.addEventListener('scroll', requestScrollUpdate, { passive: true });
    window.addEventListener('resize', requestScrollUpdate, { passive: true });

    function splitWords() {
        root.querySelectorAll('.vf-words').forEach(function (el) {
            var text = el.textContent.trim().replace(/\s+/g, ' ');
            el.setAttribute('aria-label', text);
            el.textContent = '';
            var words = text.split(' ');
            words.forEach(function (word, i) {
                var span = document.createElement('span');
                span.textContent = word;
                span.setAttribute('aria-hidden', 'true');
                el.append(span);
                if (i < words.length - 1) el.append(' ');
            });
        });
    }

    function updateWordLighting() {
        root.querySelectorAll('.vf-words').forEach(function (el) {
            var words = el.querySelectorAll('span');
            var litCount = Math.ceil(statementProgress * words.length);
            words.forEach(function (word, i) { word.classList.toggle('is-lit', i < litCount); });
        });
    }

    splitWords();

    // ── Hero: slow ambient zoom handled entirely in CSS; just mark ready ────
    window.requestAnimationFrame(function () {
        window.requestAnimationFrame(function () { root.classList.add('vf-is-ready'); });
    });

    // ── Canvas particle field ────────────────────────────────────────────
    (function initParticles() {
        var canvas = root.querySelector('.vf-particles');
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
                ctx.fillStyle = 'rgba(255, 150, 200, ' + (p.alpha * pulse) + ')';
                ctx.fill();
            });
            window.requestAnimationFrame(frame);
        }

        resize();
        window.requestAnimationFrame(frame);
        window.addEventListener('resize', resize, { passive: true });
    })();

    // ── Cursor light ──────────────────────────────────────────────────────
    // Checked per-event via e.pointerType rather than a static
    // matchMedia('(pointer: fine)') gate — that upfront check can evaluate
    // inconsistently across browsers on the same machine. Reading the actual
    // event's pointerType is authoritative and can't get stuck off.
    window.addEventListener('pointermove', function (e) {
        if (e.pointerType && e.pointerType !== 'mouse' && e.pointerType !== 'pen') return;
        root.style.setProperty('--vf-mouse-x', e.clientX + 'px');
        root.style.setProperty('--vf-mouse-y', e.clientY + 'px');
    }, { passive: true });

    // ── Stat cards + detail cards: cursor-tilt ───────────────────────────
    (function initTiltCards() {
        var cards = Array.prototype.slice.call(root.querySelectorAll('.vf-stat, .vf-detail-list article, .vf-stage'));
        if (!cards.length || reducedMotion) return;
        cards.forEach(function (card) {
            card.addEventListener('pointermove', function (e) {
                if (e.pointerType && e.pointerType !== 'mouse' && e.pointerType !== 'pen') return;
                var rect = card.getBoundingClientRect();
                var nx = (e.clientX - rect.left) / rect.width - 0.5;
                var ny = (e.clientY - rect.top) / rect.height - 0.5;
                card.style.transform = 'perspective(800px) rotateX(' + (-ny * 7).toFixed(2) + 'deg) rotateY(' + (nx * 7).toFixed(2) + 'deg) translateY(-3px)';
            }, { passive: true });
            card.addEventListener('pointerleave', function () {
                card.style.transform = '';
            });
        });
    })();

    // ── Generic scroll reveal ────────────────────────────────────────────
    var revealTargets = root.querySelectorAll('.vf-reveal, .vf-stat, .vf-detail-list article, .vf-candidate-list li');
    if (revealTargets.length && !reducedMotion) {
        var io = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.14 });
        revealTargets.forEach(function (el) { io.observe(el); });
    } else {
        revealTargets.forEach(function (el) { el.classList.add('is-visible'); });
    }

    // ── Lightbox (images only — no video source for this product) ───────────
    (function initLightbox() {
        var imageTriggers = Array.prototype.slice.call(root.querySelectorAll('[data-lightbox-src]'));
        var lightbox = root.querySelector('.vf-lightbox');
        if (!imageTriggers.length || !lightbox) return;

        var img = lightbox.querySelector('img');
        var closeBtn = lightbox.querySelector('.vf-lightbox__close');
        var lastFocused = null;

        function open(src, alt) {
            lastFocused = document.activeElement;
            img.src = src;
            img.alt = alt || '';
            lightbox.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            closeBtn.focus();
            document.addEventListener('keydown', onKeydown);
        }

        function close() {
            lightbox.classList.remove('is-open');
            document.body.style.overflow = '';
            document.removeEventListener('keydown', onKeydown);
            if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
        }

        function onKeydown(e) {
            if (e.key === 'Escape') close();
        }

        imageTriggers.forEach(function (trigger) {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                open(trigger.getAttribute('data-lightbox-src'), trigger.getAttribute('data-lightbox-alt'));
            });
        });

        closeBtn && closeBtn.addEventListener('click', close);
        lightbox.addEventListener('click', function (e) { if (e.target === lightbox) close(); });
    })();

    requestScrollUpdate();
})();
