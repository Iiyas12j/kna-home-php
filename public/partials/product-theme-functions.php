<?php
// Per-brand color identity, matched against the KNA product line-up.
// Falls back to the house purple for any product outside the four core brands.
// Shared by product.php (Catalogue grid) and single-product.php (Split Studio detail).
if (!function_exists('product_brand_theme')) {
    function product_brand_theme(string $name, string $text = ''): array
    {
        $src = mb_strtolower($name . ' ' . $text);
        if (str_contains($src, 'neofilera')) {
            return ['primary' => '#C9971F', 'dark' => '#8A6A15', 'soft' => '#FBF3DC'];
        }
        if (str_contains($src, 'hyabell')) {
            return ['primary' => '#9C7A2D', 'dark' => '#6B531D', 'soft' => '#F5EEDD'];
        }
        if (str_contains($src, 'variofill')) {
            return ['primary' => '#E6007E', 'dark' => '#A30058', 'soft' => '#FDE6F1'];
        }
        if (str_contains($src, 'meteora')) {
            return ['primary' => '#1C74C4', 'dark' => '#123E66', 'soft' => '#EAF3FB'];
        }
        return ['primary' => '#4B4899', 'dark' => '#332F73', 'soft' => '#EFEEFA'];
    }
}

// Some products (Hyabell today) bundle several SKUs into one short_description,
// each announced by a bare "<NAME> <VARIANT>" line in caps (e.g. "HYABELL MESO").
// Splits those into per-variant summary/highlights/paragraphs so the detail page
// can show them as distinct options instead of one flattened blob. Returns []
// when the product has no such headers (the normal, single-SKU case).
if (!function_exists('product_parse_variants')) {
    function product_parse_variants(string $name, string $rawText): array
    {
        $name = trim($name);
        if ($name === '' || trim($rawText) === '') {
            return [];
        }

        $text = str_replace(["\r\n", "\r"], "\n", $rawText);
        $pattern = '/^' . preg_quote(mb_strtoupper($name), '/') . '\s+([A-Z][A-Z \-]*[A-Z])\s*$/mu';

        if (!preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE) || count($matches[0]) < 2) {
            return [];
        }

        $variants = [];
        $count = count($matches[0]);
        for ($i = 0; $i < $count; $i++) {
            $label = trim($matches[1][$i][0]);
            $bodyStart = $matches[0][$i][1] + strlen($matches[0][$i][0]);
            $bodyEnd = $i + 1 < $count ? $matches[0][$i + 1][1] : strlen($text);
            $body = substr($text, $bodyStart, $bodyEnd - $bodyStart);

            $variantName = $name . ' ' . ucfirst(mb_strtolower($label));
            $paragraphs = product_description_paragraphs($body);
            $lines = product_description_lines($body);
            $summary = product_summary($paragraphs, $lines, $variantName);
            $highlights = product_highlights($lines, $variantName);

            $ha = '';
            if (preg_match('/HA\s*([\d.]+)\s*mg\s*\/?\s*ml/iu', $body, $m)) {
                $ha = $m[1] . ' mg/ml';
            }

            $duration = '';
            if (preg_match('/((?:มากกว่า\s*)?\d+\s*(?:[–\-]\s*\d+)?\s*เดือน)/u', $body, $m)) {
                $duration = trim($m[1]);
            }

            $variants[] = [
                'label'      => $label,
                'name'       => $variantName,
                'summary'    => $summary,
                'highlights' => $highlights,
                'paragraphs' => $paragraphs,
                'ha'         => $ha,
                'duration'   => $duration,
                'image'      => product_variant_image($name, $label),
            ];
        }

        return $variants;
    }
}

// Looks for an ambient hero background video at uploads/products/{product-slug}-hero.mp4
// (and an optional matching -hero-poster.jpg first-frame still). Returns '' when none
// exists, so the hero falls back to a plain dark gradient instead of a video layer.
if (!function_exists('product_hero_video')) {
    function product_hero_video(string $name): array
    {
        $slug = preg_replace('/[^a-z0-9]+/', '', mb_strtolower($name));
        if ($slug === '') {
            return ['video' => '', 'poster' => ''];
        }

        $dir = __DIR__ . '/../uploads/products/';
        $video = is_file($dir . $slug . '-hero.mp4') ? '/uploads/products/' . $slug . '-hero.mp4' : '';
        $poster = is_file($dir . $slug . '-hero-poster.jpg') ? '/uploads/products/' . $slug . '-hero-poster.jpg' : '';

        return ['video' => $video, 'poster' => $poster];
    }
}

// Looks for a high-res "SOLO" studio photo of the gel itself at
// uploads/products/{product-slug}-variants/{NAME}_{LABEL}_GEL/ — a folder convention
// used for Hyabell's professional product photography (with/without reflection).
// Prefers a reflection shot (skipping any "no reflection" negatives) in .png/.jpg,
// since .tif isn't renderable in a browser. Returns '' when the folder doesn't exist,
// so the hero falls back to the plain gradient backdrop.
if (!function_exists('product_variant_hero_photo')) {
    function product_variant_hero_photo(string $name, string $label): string
    {
        $slug = preg_replace('/[^a-z0-9]+/', '', mb_strtolower($name));
        if ($slug === '' || trim($label) === '') {
            return '';
        }

        $folderName = mb_strtoupper($name) . '_' . mb_strtoupper($label) . '_GEL';
        $dir = __DIR__ . '/../uploads/products/' . $slug . '-variants/' . $folderName . '/';
        if (!is_dir($dir)) {
            return '';
        }

        $candidates = [];
        foreach (scandir($dir) ?: [] as $file) {
            if (preg_match('/\.(jpe?g|png)$/i', $file)) {
                $candidates[] = $file;
            }
        }
        if (empty($candidates)) {
            return '';
        }

        $withReflection = array_values(array_filter($candidates, static function ($f) {
            $lower = mb_strtolower($f);
            foreach (['no_reflection', 'no-reflection', 'no reflection', 'sin-reflejo', 'sin reflejo'] as $negative) {
                if (str_contains($lower, $negative)) {
                    return false;
                }
            }
            return str_contains($lower, 'reflection');
        }));

        $pick = $withReflection[0] ?? $candidates[0];
        foreach ($withReflection as $f) {
            if (str_ends_with(mb_strtolower($f), '.png')) {
                $pick = $f;
                break;
            }
        }

        return '/uploads/products/' . rawurlencode($slug . '-variants') . '/' . rawurlencode($folderName) . '/' . rawurlencode($pick);
    }
}

// Looks for a full watchable brand/launch video at uploads/products/{product-slug}-full.mp4
// (and an optional -full-poster.jpg still for the play button). This is a real, complete
// marketing video meant to be watched with sound via a "watch video" trigger — separate
// from product_hero_video()'s silent ambient background loop. Returns '' when none exists,
// so the caller can skip rendering the "watch video" button entirely.
if (!function_exists('product_full_video')) {
    function product_full_video(string $name): array
    {
        $slug = preg_replace('/[^a-z0-9]+/', '', mb_strtolower($name));
        if ($slug === '') {
            return ['video' => '', 'poster' => ''];
        }

        $dir = __DIR__ . '/../uploads/products/';
        $video = is_file($dir . $slug . '-full.mp4') ? '/uploads/products/' . $slug . '-full.mp4' : '';
        $poster = is_file($dir . $slug . '-full-poster.jpg') ? '/uploads/products/' . $slug . '-full-poster.jpg' : '';

        return ['video' => $video, 'poster' => $poster];
    }
}

// Immersive product-detail theme, keyed by product NAME (not id) — ids differ between
// this dev database and production, so name matching is the only stable key across
// environments. Any product outside the two named brands gets the same system with a
// neutral accent, so no product is left with a blank/broken section.
if (!function_exists('product_immersive_theme')) {
    function product_immersive_theme(string $name): array
    {
        $src = mb_strtolower($name);
        if (str_contains($src, 'neofilera')) {
            return [
                'key' => 'neofilera',
                'accent' => '#f4b833',
                'accent_rgb' => '244, 184, 51',
                'hyabell_family' => false,
            ];
        }
        if (str_contains($src, 'hyabell')) {
            return [
                'key' => 'hyabell',
                'accent' => '#9567ff',
                'accent_rgb' => '149, 103, 255',
                'hyabell_family' => true,
            ];
        }
        return [
            'key' => 'default',
            'accent' => '#4B4899',
            'accent_rgb' => '75, 72, 153',
            'hyabell_family' => false,
        ];
    }
}

// Maps a product NAME to its own dedicated detail page. Each of the four core
// products now lives at its own URL/file instead of a shared ?id= query string.
// Falls back to the catalogue grid for anything unrecognised.
if (!function_exists('product_detail_url')) {
    function product_detail_url(string $name): string
    {
        $src = mb_strtolower($name);
        if (str_contains($src, 'neofilera')) return '/neofilera.php';
        if (str_contains($src, 'hyabell')) return '/hyabell.php';
        if (str_contains($src, 'variofill')) return '/variofill.php';
        if (str_contains($src, 'meteora')) return '/meteora.php';
        return '/product.php';
    }
}

// Per-variant accent used on the Hyabell family showcase cards. Falls back to the
// product's main accent (purple) for a label that doesn't match a known SKU.
if (!function_exists('product_hyabell_variant_accent')) {
    function product_hyabell_variant_accent(string $label): string
    {
        $key = mb_strtolower(trim($label));
        if (str_contains($key, 'basic')) return '#f2994a';
        if (str_contains($key, 'deep'))  return '#2f9fd8';
        if (str_contains($key, 'lips'))  return '#e5484d';
        if (str_contains($key, 'ultra')) return '#9567ff';
        if (str_contains($key, 'meso'))  return '#3ea66b';
        return '#9567ff';
    }
}

// Supplementary real photography for the Hyabell family showcase (group shot, syringe,
// gel macro) at uploads/products/hyabell-variants/{family.jpg,syringe.png,gel.png}.
// Each key is '' when the file isn't present, so the caller can skip that block cleanly.
if (!function_exists('product_hyabell_family_assets')) {
    function product_hyabell_family_assets(): array
    {
        $dir = __DIR__ . '/../uploads/products/hyabell-variants/';
        $base = '/uploads/products/hyabell-variants/';
        return [
            'family'  => is_file($dir . 'family.jpg')  ? $base . 'family.jpg'  : '',
            'syringe' => is_file($dir . 'syringe.png') ? $base . 'syringe.png' : '',
            'gel'     => is_file($dir . 'gel.png')     ? $base . 'gel.png'     : '',
        ];
    }
}

// Looks for a per-variant photo at uploads/products/{product-slug}-variants/{label}.{ext}
// (e.g. uploads/products/hyabell-variants/meso.jpg). Returns '' when none exists, so
// callers fall back to the product's regular hero image.
if (!function_exists('product_variant_image')) {
    function product_variant_image(string $name, string $label): string
    {
        $slug = preg_replace('/[^a-z0-9]+/', '', mb_strtolower($name));
        $labelSlug = preg_replace('/[^a-z0-9]+/', '', mb_strtolower($label));
        if ($slug === '' || $labelSlug === '') {
            return '';
        }

        $dir = __DIR__ . '/../uploads/products/' . $slug . '-variants/';
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            if (is_file($dir . $labelSlug . '.' . $ext)) {
                return '/uploads/products/' . $slug . '-variants/' . $labelSlug . '.' . $ext;
            }
        }

        return '';
    }
}
