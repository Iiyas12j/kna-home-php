// NeoFilera cinematic page behaviour. Scoped to .nf-page — no globals besides
// the one IIFE below. Progressive enhancement: the DOM is fully readable/usable
// with JS disabled (real text, real links, real <img> gallery tiles).
(function () {
    'use strict';

    var root = document.querySelector('.nf-page');
    if (!root) return;

    document.documentElement.classList.remove('no-js');

    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ── Ambient hero video ───────────────────────────────────────────────
    (function initHeroVideo() {
        var heroVideo = root.querySelector('.nf-hero-video');
        if (!heroVideo) return;
        if (reducedMotion) { heroVideo.pause(); heroVideo.removeAttribute('autoplay'); return; }

        var hero = root.querySelector('.nf-hero');
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) heroVideo.play().catch(function () {});
                else heroVideo.pause();
            });
        }, { threshold: 0 });
        if (hero) io.observe(hero);
    })();

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    // ── Scroll-scrubbed scene progress ───────────────────────────────────
    var progressBar = root.querySelector('.nf-progress i');
    var techScene = root.querySelector('.nf-tech');
    var frameRequested = false;

    function sceneProgress(el) {
        if (!el) return 0;
        var rect = el.getBoundingClientRect();
        var travel = Math.max(1, rect.height - window.innerHeight);
        return clamp(-rect.top / travel, 0, 1);
    }

    // ── Scroll-scrubbed Technology section, driven by a preloaded image
    // sequence instead of a <video> — video.currentTime scrubbing turned out
    // unreliable in Chrome across two fix attempts (raw <video> repaint, then
    // canvas + decoder priming), so this sidesteps video decoding entirely.
    // 150 frames are preloaded as in-memory Image objects and painted onto a
    // <canvas> with drawImage() — that's a direct pixel blit with no DOM
    // src-swap/decode overhead, which is what was still causing the
    // stutter when frames were shown via a plain <img>. ─────────────────────
    var techCanvas = root.querySelector('.nf-tech-frame');
    var techCtx = techCanvas ? techCanvas.getContext('2d') : null;
    var techFrames = [];
    var techFramesLoaded = 0;
    var techFramesReady = false;
    var techLastIndex = -1;

    if (techCanvas && techCtx) {
        var techFrameBase = techCanvas.getAttribute('data-frame-base');
        var techFrameCount = parseInt(techCanvas.getAttribute('data-frame-count'), 10) || 0;
        for (var fi = 1; fi <= techFrameCount; fi++) {
            var src = techFrameBase + String(fi).padStart(3, '0') + '.jpg';
            var im = new Image();
            im.onload = function () {
                techFramesLoaded++;
                if (this === techFrames[0]) {
                    techCanvas.width = this.naturalWidth;
                    techCanvas.height = this.naturalHeight;
                    techCtx.drawImage(this, 0, 0);
                }
                if (techFramesLoaded >= techFrameCount) {
                    techFramesReady = true;
                    scrubTechVideo(sceneProgress(techScene));
                }
            };
            im.onerror = function () { techFramesLoaded++; };
            im.src = src;
            techFrames.push(im);
        }
    }

    function scrubTechVideo(progress) {
        if (!techCtx || !techFramesReady || !techFrames.length) return;
        var index = Math.round(clamp(progress, 0, 1) * (techFrames.length - 1));
        if (index === techLastIndex) return;
        techLastIndex = index;
        techCtx.drawImage(techFrames[index], 0, 0, techCanvas.width, techCanvas.height);
    }

    // ── Statement: scroll-track progress drives the word-lighting instead of
    // the words' own (pinned, unchanging) position once the section is sticky.
    var statementScene = root.querySelector('.nf-statement');
    var statementProgress = 0;

    // ── Atmosphere: crossfade vial → box as you scroll through the section ──
    var atmosphereScene = root.querySelector('.nf-atmosphere');
    var vialImg = root.querySelector('.nf-product-img--vial');
    var boxImg = root.querySelector('.nf-product-img--box');

    function scrubAtmosphereImages(progress) {
        if (!vialImg || !boxImg) return;
        var p = clamp(progress, 0, 1);
        vialImg.style.opacity = String(clamp(1 - p * 1.6, 0, 1));
        boxImg.style.opacity = String(clamp(p * 1.6 - 0.3, 0, 1));
    }

    function updateScrollState() {
        var scrollY = window.scrollY;
        var maxScroll = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
        if (progressBar) progressBar.style.transform = 'scaleY(' + clamp(scrollY / maxScroll, 0, 1) + ')';

        if (techScene) {
            scrubTechVideo(sceneProgress(techScene));
        }

        if (atmosphereScene) {
            scrubAtmosphereImages(sceneProgress(atmosphereScene));
        }

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

    // ── Statement word-lighting ──────────────────────────────────────────
    function splitWords() {
        root.querySelectorAll('.nf-words').forEach(function (el) {
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
        root.querySelectorAll('.nf-words').forEach(function (el) {
            var words = el.querySelectorAll('span');
            var litCount = Math.ceil(statementProgress * words.length);
            words.forEach(function (word, i) { word.classList.toggle('is-lit', i < litCount); });
        });
    }

    splitWords();

    // ── Canvas particle field ────────────────────────────────────────────
    (function initParticles() {
        var canvas = root.querySelector('.nf-particles');
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

            var count = window.innerWidth < 700 ? 28 : 58;
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
                ctx.fillStyle = 'rgba(255, 205, 100, ' + (p.alpha * pulse) + ')';
                ctx.fill();
            });
            window.requestAnimationFrame(frame);
        }

        resize();
        window.requestAnimationFrame(frame);
        window.addEventListener('resize', resize, { passive: true });
    })();

    // ── Cursor light + hero-ready reveal ─────────────────────────────────
    var finePointer = window.matchMedia('(pointer: fine)').matches;
    var productStageEl = root.querySelector('.nf-product-stage');
    if (finePointer) {
        window.addEventListener('pointermove', function (e) {
            root.style.setProperty('--nf-mouse-x', e.clientX + 'px');
            root.style.setProperty('--nf-mouse-y', e.clientY + 'px');

            if (productStageEl && !reducedMotion) {
                var nx = (e.clientX / window.innerWidth - 0.5) * 2;
                var ny = (e.clientY / window.innerHeight - 0.5) * 2;
                productStageEl.style.transform = 'translate(' + (nx * 14) + 'px, ' + (ny * 10) + 'px)';
            }
        }, { passive: true });
    }

    window.requestAnimationFrame(function () {
        window.requestAnimationFrame(function () { root.classList.add('nf-is-ready'); });
    });

    // ── Atmosphere stage: gradual reveal-in once scrolled into view ────────
    var heroStageEl = root.querySelector('.nf-hero-stage');
    if (heroStageEl && !reducedMotion) {
        var stageIo = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    heroStageEl.classList.add('nf-is-revealed');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });
        stageIo.observe(heroStageEl);
    } else if (heroStageEl) {
        heroStageEl.classList.add('nf-is-revealed');
    }

    // ── Generic scroll reveal ────────────────────────────────────────────
    var revealTargets = root.querySelectorAll('.nf-reveal');
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

    // ── Lightbox (images + full-video triggers) ─────────────────────────────
    (function initLightbox() {
        var imageTriggers = Array.prototype.slice.call(root.querySelectorAll('[data-lightbox-src]'));
        var videoTriggers = Array.prototype.slice.call(root.querySelectorAll('[data-lightbox-video]'));
        var lightbox = root.querySelector('.nf-lightbox');
        if ((!imageTriggers.length && !videoTriggers.length) || !lightbox) return;

        var img = lightbox.querySelector('img');
        var video = lightbox.querySelector('video');
        var closeBtn = lightbox.querySelector('.nf-lightbox__close');
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

    requestScrollUpdate();
})();
