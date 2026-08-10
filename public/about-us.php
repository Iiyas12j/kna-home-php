<?php
$siteHeaderActive = 'about';
$page_title = 'About Us - KNA Interpharma';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <?php require_once __DIR__ . '/partials/site-head.php'; ?>
</head>
<body class="bg-gray-50">
    <?php require_once __DIR__ . '/partials/site-header.php'; ?>
<!-- Hero Banner -->
    <section class="relative overflow-hidden">
        <img src="/uploads/about/about-banner-1-1024x382.jpg" alt="About Us Banner" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>
        <div class="relative mx-auto max-w-7xl px-4 pb-20 pt-16 sm:px-6 lg:px-8">
            <div class="max-w-2xl ml-auto text-right">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white/90 backdrop-blur">
                    <i class="fa-solid fa-building"></i> About Us
                </div>
                <h1 class="mt-5 text-4xl font-extrabold leading-tight text-white sm:text-5xl">
                    เกี่ยวกับเรา<br class="hidden sm:block">KNA Interpharma
                </h1>
            </div>
        </div>
    </section>

    <!-- Section 1: English Content -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div class="rounded-2xl shadow-lg bg-white flex items-center justify-center p-4">
                        <img src="/uploads/logo-kna.png" alt="KNA Interpharma logo" class="w-full object-contain">
                    </div>
                    <div>
                        <h2 class="text-4xl font-bold mb-6" style="color: #4B4899;">K.N.A INTER PHARMA Co.,Ltd</h2>
                        <p class="text-lg text-gray-700 leading-relaxed">
                            KNA INTERPHAMA is a company founded by Akibam Oils Co., Ltd and related ,the company group that working in management and distributor of fuel in Thailand, and Anda Group which is a leading aesthetics practice in Thailand. Although they specialize in different field but aiming the same target to expand aesthetics practice and business in Thailand and worldwide.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 2: Thai Content -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div class="order-2 md:order-1">
                        <h2 class="text-4xl font-bold mb-6" style="color: #4B4899;">K.N.A. Inter Pharma</h2>
                        <p class="text-lg text-gray-700 leading-relaxed mb-4">
                            ผู้นำเข้าและจัดจำหน่ายผลิตภัณฑ์ความงามและสุขภาพระดับพรีเมียมจากต่างประเทศ มุ่งเน้นตอบโจทย์ทุกความต้องการของวงการความงาม ด้วยสินค้าคุณภาพ อาทิ ฟิลเลอร์แท้จากเยอรมัน <strong>HYABELL FILLER</strong>, <strong>VARIOFILL Filler</strong> สำหรับการเติมเต็มสะโพก และ <strong>ไหมขนนก ไหมสำหรับการยกกระชับ</strong> ที่ให้ความรู้สึกเบาสบาย กับ <strong>METEORA THREAD</strong>
                        </p>
                        <p class="text-lg text-gray-700 leading-relaxed">
                            รวมไปถึง ตัวกระตุ้นคอลลาเจนน้องใหม่ล่าสุด <strong>'NeoFilera'</strong> 1st Universal Biostimulator for Face and Body ที่จะช่วยคืนความอ่อนเยาว์ทั่วเรือนร่าง นวัตกรรมระดับสูงจากไต้หวัน
                        </p>
                    </div>
                    <div class="order-1 md:order-2">
                        <img src="/uploads/product2.jpg" alt="KNA Products" class="rounded-2xl shadow-lg w-full">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Gallery -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-4xl font-bold text-center mb-12" style="color: #4B4899;">Our Products</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <a href="/hyabell.php" class="text-center block">
                        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                            <img src="/uploads/about/Hyabell.png" alt="Hyabell" class="h-32 mx-auto mb-4 object-contain">
                            <h3 class="font-bold text-gray-800">HYABELL</h3>
                        </div>
                    </a>
                    <a href="/variofill.php" class="text-center block">
                        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                            <img src="/uploads/about/Variofill.png" alt="Variofill" class="h-32 mx-auto mb-4 object-contain">
                            <h3 class="font-bold text-gray-800">VARIOFILL</h3>
                        </div>
                    </a>
                    <a href="/meteora.php" class="text-center block">
                        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                            <img src="/uploads/about/Meteora-Thread.png" alt="Meteora Thread" class="h-32 mx-auto mb-4 object-contain">
                            <h3 class="font-bold text-gray-800">METEORA THREAD</h3>
                        </div>
                    </a>
                    <a href="/neofilera.php" class="text-center block">
                        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                            <img src="/uploads/about/NeoFilera.png" alt="NeoFilera" class="h-32 mx-auto mb-4 object-contain">
                            <h3 class="font-bold text-gray-800">NEOFILERA</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php require_once __DIR__ . '/partials/site-footer.php'; ?>

    </body>
</html>
