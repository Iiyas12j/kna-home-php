<?php
// Shared "back to all products" chip for the four cinematic product pages.
// Deliberately unscoped (no .nf-/.hy-/.vf-/.mt- prefix) so one copy serves all
// four, each of which ships its own otherwise-isolated stylesheet.
?>
<style>
    .product-back {
        position: sticky;
        top: calc(var(--header-height, 84px) + 12px);
        z-index: 30;
        /* sticky needs a block in normal flow, but this chip must not reserve a
           strip of its own across the page — so the wrapper is zero-height and
           the chip floats out of it. */
        height: 0;
        margin: 0 auto;
        padding: 0 4.5vw;
        max-width: 1360px;
        pointer-events: none;
    }

    /* Deliberately quiet: these pages are cinematic, so the chip should read as a
       faint utility mark and only firm up on hover/focus rather than competing
       with the artwork.
       Two classes (0,2,0) are required: every product page ships
       `.xx-page a { font: inherit; color: inherit; }` at (0,1,1), which otherwise
       wins and renders this chip at the inherited 16px solid white. */
    .product-back .product-back__link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.3rem 0.66rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(10, 8, 14, 0.28);
        backdrop-filter: blur(10px);
        color: rgba(255, 255, 255, 0.55);
        font-family: "IBM Plex Sans Thai", "Manrope", sans-serif;
        font-size: 0.68rem;
        font-weight: 400;
        line-height: 1;
        letter-spacing: 0.01em;
        white-space: nowrap;
        pointer-events: auto;
        transition: background 240ms ease, border-color 240ms ease, color 240ms ease;
    }

    .product-back .product-back__link:hover,
    .product-back .product-back__link:focus-visible {
        background: rgba(10, 8, 14, 0.62);
        border-color: rgba(255, 255, 255, 0.32);
        color: rgba(255, 255, 255, 0.95);
    }

    .product-back .product-back__link i { font-size: 0.6rem; opacity: 0.8; }

    @media (max-width: 820px) {
        .product-back { top: calc(var(--header-height, 66px) + 8px); padding: 0 6vw; }
        .product-back .product-back__link { padding: 0.28rem 0.58rem; font-size: 0.64rem; }
    }

    @media (prefers-reduced-motion: reduce) {
        .product-back .product-back__link { transition: none; }
    }
</style>

<div class="product-back">
    <a class="product-back__link" href="/product.php">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
        กลับหน้ารวมสินค้า
    </a>
</div>
