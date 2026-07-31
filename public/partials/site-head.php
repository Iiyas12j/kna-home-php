<?php
require_once __DIR__ . '/../../app/helpers.php';
$page_title = $page_title ?? 'KNA Interpharma';
if (!str_contains($page_title, 'KNA Interpharma') && !str_contains($page_title, 'Knainterphama')) {
    $page_title .= ' - KNA Interpharma';
}
$page_description = $page_description ?? 'K.N.A. Inter Pharma ผู้นำเข้าและจัดจำหน่ายผลิตภัณฑ์ความงามและสุขภาพระดับพรีเมียมจากต่างประเทศ สำหรับคลินิกและแพทย์ทั่วประเทศไทย';
$base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
$page_og_image = $page_og_image ?? '/uploads/logo-kna.png';
if (!preg_match('#^https?://#i', $page_og_image)) {
    $page_og_image = $base . '/' . ltrim($page_og_image, '/');
}
$canonical_url = $base . ($_SERVER['REQUEST_URI'] ?? '/');
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo h($page_title); ?></title>
<meta name="description" content="<?php echo h($page_description); ?>">
<link rel="canonical" href="<?php echo h($canonical_url); ?>">
<link rel="icon" type="image/png" href="/uploads/logo-kna.png">

<meta property="og:type" content="website">
<meta property="og:site_name" content="KNA Interpharma">
<meta property="og:locale" content="th_TH">
<meta property="og:title" content="<?php echo h($page_title); ?>">
<meta property="og:description" content="<?php echo h($page_description); ?>">
<meta property="og:image" content="<?php echo h($page_og_image); ?>">
<meta property="og:url" content="<?php echo h($canonical_url); ?>">
<meta name="twitter:card" content="summary_large_image">

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    brand: { DEFAULT: '#4B4899', dark: '#3d3a7a', soft: '#EFEEFA' },
                },
            },
        },
    };
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Kanit', sans-serif; }
</style>
