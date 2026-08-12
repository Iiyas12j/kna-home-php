// METEORA cinematic page behaviour. Scoped to .mt-page — no globals besides
// the one IIFE below. Progressive enhancement: the DOM is fully readable/usable
// with JS disabled (real text, real links, real <img> gallery tiles).
(function () {
    'use strict';

    var root = document.querySelector('.mt-page');
    if (!root) return;

    document.documentElement.classList.remove('no-js');

    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ── Ambient hero video ───────────────────────────────────────────────
    (function initHeroVideo() {
        var heroVideo = root.querySelector('.mt-hero-video');
        if (!heroVideo) return;
        if (reducedMotion) { heroVideo.pause(); heroVideo.removeAttribute('autoplay'); return; }

        var hero = root.querySelector('.mt-hero');
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

    // ── Spec cards: staggered one-by-one reveal + cursor-tilt. Self-contained
    // and placed early so it runs even if something later in this file throws.
    (function initSpecCards() {
        var list = root.querySelector('.mt-specs-list');
        if (!list) return;
        var cards = Array.prototype.slice.call(list.children);
        if (!cards.length) return;

        if (!reducedMotion) {
            cards.forEach(function (card) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(28px)';
            });

            var revealed = false;
            function revealCards() {
                if (revealed) return;
                revealed = true;
                cards.forEach(function (card, i) {
                    setTimeout(function () {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, i * 140);
                });
            }

            if ('IntersectionObserver' in window) {
                var specIo = new IntersectionObserver(function (entries, obs) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            revealCards();
                            obs.disconnect();
                        }
                    });
                }, { threshold: 0.16 });
                specIo.observe(list);
            } else {
                revealCards();
            }
        }

        if (!reducedMotion) {
            cards.forEach(function (card) {
                card.addEventListener('pointermove', function (e) {
                    if (e.pointerType && e.pointerType !== 'mouse' && e.pointerType !== 'pen') return;
                    var rect = card.getBoundingClientRect();
                    var nx = (e.clientX - rect.left) / rect.width - 0.5;
                    var ny = (e.clientY - rect.top) / rect.height - 0.5;
                    card.style.transform = 'perspective(800px) rotateX(' + (-ny * 9).toFixed(2) + 'deg) rotateY(' + (nx * 9).toFixed(2) + 'deg) translateY(-4px)';
                }, { passive: true });
                card.addEventListener('pointerleave', function () {
                    card.style.transform = 'translateY(0)';
                });
            });
        }
    })();

    // ── Scroll-scrubbed scene progress ───────────────────────────────────
    var progressBar = root.querySelector('.mt-progress i');
    var techScene = root.querySelector('.mt-tech');
    var frameRequested = false;

    function sceneProgress(el) {
        if (!el) return 0;
        var rect = el.getBoundingClientRect();
        var travel = Math.max(1, rect.height - window.innerHeight);
        return clamp(-rect.top / travel, 0, 1);
    }

    // ── Scroll-scrubbed Technology video (plays frame-by-frame as you scroll) ─
    var techVideo = root.querySelector('.mt-tech-video');
    var techVideoDuration = 0;
    var techVideoReady = false;

    function markTechVideoReady() {
        if (techVideoReady) return;
        techVideoDuration = techVideo.duration || 0;
        if (!(techVideoDuration > 0) || !isFinite(techVideoDuration)) return;
        techVideoReady = true;
        // Prime the decoder (some browsers won't render seeked frames until
        // playback has started at least once) — play a beat, then pause.
        var primePlay = techVideo.play();
        if (primePlay && typeof primePlay.then === 'function') {
            primePlay.then(function () { techVideo.pause(); }).catch(function () {});
        } else {
            techVideo.pause();
        }
        scrubTechVideo(sceneProgress(techScene));
    }

    if (techVideo) {
        techVideo.addEventListener('loadedmetadata', markTechVideoReady);
        techVideo.addEventListener('durationchange', markTechVideoReady);
        techVideo.addEventListener('canplay', markTechVideoReady);
        // Metadata may already be cached/loaded before these listeners attach.
        if (techVideo.readyState >= 1) markTechVideoReady();
    }

    function scrubTechVideo(progress) {
        if (!techVideo || !techVideoReady) return;
        var target = Math.min(clamp(progress, 0, 1) * techVideoDuration, techVideoDuration - 0.05);
        if (Math.abs(techVideo.currentTime - target) > 0.03) {
            techVideo.currentTime = target;
        }
    }

    // ── Statement: scroll-track progress drives the word-lighting instead of
    // the words' own (pinned, unchanging) position once the section is sticky.
    var statementScene = root.querySelector('.mt-statement');
    var statementProgress = 0;

    // ── Atmosphere: thread slides up from below, then detail cards assemble
    // one at a time (alternating left/right) as the pinned scene scrolls.
    var atmosphereScene = root.querySelector('.mt-atmosphere');
    var detailEls = Array.prototype.slice.call(root.querySelectorAll('.mt-detail'));
    var DETAIL_BANDS = [
        [0.16, 0.36],
        [0.36, 0.56],
        [0.56, 0.76],
        [0.76, 0.96]
    ];

    function updateScrollState() {
        var scrollY = window.scrollY;
        var maxScroll = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
        if (progressBar) progressBar.style.transform = 'scaleY(' + clamp(scrollY / maxScroll, 0, 1) + ')';

        if (techScene) {
            var progress = sceneProgress(techScene);
            root.style.setProperty('--mt-tech-progress', progress.toFixed(4));
            scrubTechVideo(progress);
        }

        if (atmosphereScene) {
            var atmosphereProgress = sceneProgress(atmosphereScene);
            var threadT = clamp(atmosphereProgress / 0.16, 0, 1);
            root.style.setProperty('--mt-thread-opacity', threadT.toFixed(3));
            root.style.setProperty('--mt-thread-y', ((1 - threadT) * 70).toFixed(1) + 'px');

            detailEls.forEach(function (el, i) {
                var band = DETAIL_BANDS[i] || DETAIL_BANDS[DETAIL_BANDS.length - 1];
                var t = clamp((atmosphereProgress - band[0]) / (band[1] - band[0]), 0, 1);
                el.style.setProperty('--mt-d', t.toFixed(3));
            });
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
        root.querySelectorAll('.mt-words').forEach(function (el) {
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
        root.querySelectorAll('.mt-words').forEach(function (el) {
            var words = el.querySelectorAll('span');
            var litCount = Math.ceil(statementProgress * words.length);
            words.forEach(function (word, i) { word.classList.toggle('is-lit', i < litCount); });
        });
    }

    splitWords();

    // ── Canvas particle field ────────────────────────────────────────────
    (function initParticles() {
        var canvas = root.querySelector('.mt-particles');
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
                ctx.fillStyle = 'rgba(169, 180, 255, ' + (p.alpha * pulse) + ')';
                ctx.fill();
            });
            window.requestAnimationFrame(frame);
        }

        resize();
        window.requestAnimationFrame(frame);
        window.addEventListener('resize', resize, { passive: true });
    })();

    // ── Cursor light + hero-ready reveal ─────────────────────────────────
    // Checked per-event via e.pointerType rather than a static
    // matchMedia('(pointer: fine)') gate — that upfront check has been seen to
    // evaluate inconsistently across browsers on the same machine (works in
    // Safari, silently never attaches in Chrome on some setups). Reading the
    // actual event's pointerType is authoritative and can't get stuck off.
    var productStageEl = root.querySelector('.mt-product-stage');
    window.addEventListener('pointermove', function (e) {
        if (e.pointerType && e.pointerType !== 'mouse' && e.pointerType !== 'pen') return;
        root.style.setProperty('--mt-mouse-x', e.clientX + 'px');
        root.style.setProperty('--mt-mouse-y', e.clientY + 'px');

        if (productStageEl && !reducedMotion) {
            var nx = (e.clientX / window.innerWidth - 0.5) * 2;
            var ny = (e.clientY / window.innerHeight - 0.5) * 2;
            productStageEl.style.transform = 'translate(' + (nx * 14) + 'px, ' + (ny * 10) + 'px)';
        }
    }, { passive: true });

    window.requestAnimationFrame(function () {
        window.requestAnimationFrame(function () { root.classList.add('mt-is-ready'); });
    });

    // ── Atmosphere stage: gradual reveal-in once scrolled into view ────────
    var heroStageEl = root.querySelector('.mt-hero-stage');
    if (heroStageEl && !reducedMotion) {
        var stageIo = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    heroStageEl.classList.add('mt-is-revealed');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });
        stageIo.observe(heroStageEl);
    } else if (heroStageEl) {
        heroStageEl.classList.add('mt-is-revealed');
    }

    // ── Generic scroll reveal ────────────────────────────────────────────
    var revealTargets = root.querySelectorAll('.mt-reveal, .mt-results-list article, .mt-specs-list article');
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
        var lightbox = root.querySelector('.mt-lightbox');
        if ((!imageTriggers.length && !videoTriggers.length) || !lightbox) return;

        var img = lightbox.querySelector('img');
        var video = lightbox.querySelector('video');
        var closeBtn = lightbox.querySelector('.mt-lightbox__close');
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
