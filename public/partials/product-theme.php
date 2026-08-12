<?php
/* Hallmark · genre: editorial · macrostructure: Catalogue / Split Studio · theme: custom
 * vibe: "clinical luxury, quiet confidence, doctor-trusted" · paper: oklch(98% 0.010 282) · accent: oklch(44.8% 0.129 282)
 * display: Cormorant Garamond (outlier, ≤2 slots) · body: Kanit (sitewide, unchanged) · axes: light / roman-serif / cool
 * studied: no · context: explicit (Apple AirPods Pro scroll reference + Bottega Veneta / Aesop research) · v1
 */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
<style>
    :root {
        /* Paper (light, violet-tinted toward the brand hue) */
        --pt-paper:      oklch(98% 0.010 282);
        --pt-paper-2:    oklch(95% 0.012 282);
        --pt-paper-3:    oklch(92% 0.014 282);

        /* Ink */
        --pt-ink:        oklch(18% 0.012 282);
        --pt-ink-2:      oklch(38% 0.012 282);

        /* Supporting greys */
        --pt-rule:       oklch(78% 0.010 282);
        --pt-rule-2:     oklch(84% 0.008 282);
        --pt-muted:      oklch(48% 0.010 282);

        /* Accent = KNA brand purple, converted to OKLCH (#4B4899) */
        --pt-accent:      oklch(44.8% 0.129 282);
        --pt-accent-dark: oklch(34% 0.120 282);
        --pt-accent-ink:  oklch(98% 0.010 282);
        --pt-focus:       oklch(46% 0.20 282);

        /* Fonts */
        --pt-display: "Cormorant Garamond", Georgia, serif;

        /* Spacing (4pt scale) */
        --pt-space-3xs: 0.125rem;
        --pt-space-2xs: 0.25rem;
        --pt-space-xs:  0.5rem;
        --pt-space-sm:  0.75rem;
        --pt-space-md:  1rem;
        --pt-space-lg:  1.5rem;
        --pt-space-xl:  2.5rem;
        --pt-space-2xl: 4rem;
        --pt-space-3xl: 6rem;
        --pt-space-4xl: 9rem;

        /* Motion */
        --pt-ease-out: cubic-bezier(0.16, 1, 0.3, 1);
        --pt-ease-in:  cubic-bezier(0.7, 0, 0.84, 0);
        --pt-dur-micro: 120ms;
        --pt-dur-short: 220ms;
        --pt-dur-long:  420ms;
    }

    .pt-display {
        font-family: var(--pt-display);
        font-style: normal;
        letter-spacing: -0.01em;
    }

    .pt-reveal {
        opacity: 0;
        transform: translateY(10px);
        animation: pt-reveal var(--pt-dur-long) var(--pt-ease-out) forwards;
        animation-delay: calc(var(--i, 0) * 70ms);
    }
    @keyframes pt-reveal {
        to { opacity: 1; transform: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .pt-reveal {
            animation-duration: 150ms !important;
            animation-delay: 0ms !important;
        }
        .pt-scrub-frame { transition: opacity 150ms linear !important; }
    }

    html, body { overflow-x: clip; }
</style>
