<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สินค้า - Knainterphama</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
        }
    </style>
    </head>
<body class="bg-gray-50">
    <!-- Header / Navigation -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-3">
                    <img src="/uploads/kna-logo.png" alt="Knainterphama Logo" class="h-14">
                </div>
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="/index.php" class="text-gray-700 font-medium transition" onmouseover="this.style.color='#4B4899'" onmouseout="this.style.color='#374151'">Home</a>
                    <a href="/searchpage.php" class="text-gray-700 font-medium transition" onmouseover="this.style.color='#4B4899'" onmouseout="this.style.color='#374151'">Find A Clinic</a>
                    <a href="/doctors_directory.php" class="text-gray-700 font-medium transition" onmouseover="this.style.color='#4B4899'" onmouseout="this.style.color='#374151'">Trainer</a>
                    <a href="/about-us.php" class="text-gray-700 font-medium transition" onmouseover="this.style.color='#4B4899'" onmouseout="this.style.color='#374151'">About Us</a>
                    <a href="/product.php" class="text-gray-700 font-medium transition" onmouseover="this.style.color='#4B4899'" onmouseout="this.style.color='#374151'">Product</a>
                    <a href="/news-event.php" class="text-gray-700 font-medium transition" onmouseover="this.style.color='#4B4899'" onmouseout="this.style.color='#374151'">News & Event</a>
                    <a href="/video-tiktok.php" class="text-gray-700 font-medium transition" onmouseover="this.style.color='#4B4899'" onmouseout="this.style.color='#374151'">VDO</a>
                    <a href="/contact.php" class="text-gray-700 font-medium transition" onmouseover="this.style.color='#4B4899'" onmouseout="this.style.color='#374151'">Contact Us</a>
                                            <a href="/login.php" class="text-white px-6 py-2 rounded-full font-medium transition" style="background-color: #4B4899;" onmouseover="this.style.backgroundColor='#3d3a7a'" onmouseout="this.style.backgroundColor='#4B4899'">
                            <i class="fas fa-user mr-1"></i>เข้าสู่ระบบ
                        </a>
                                    </nav>
                <button id="mobileMenuBtn" class="md:hidden text-gray-700">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
            
            <!-- Mobile Menu -->
            <div id="mobileMenu" class="hidden md:hidden pb-4">
                <nav class="flex flex-col space-y-2">
                    <a href="/index.php" class="text-gray-700 font-medium py-2 px-4 hover:bg-gray-100 rounded">Home</a>
                    <a href="/searchpage.php" class="text-gray-700 font-medium py-2 px-4 hover:bg-gray-100 rounded">Find A Clinic</a>
                    <a href="/doctors_directory.php" class="text-gray-700 font-medium py-2 px-4 hover:bg-gray-100 rounded">Trainer</a>
                    <a href="/about-us.php" class="text-gray-700 font-medium py-2 px-4 hover:bg-gray-100 rounded">About Us</a>
                    <a href="/product.php" class="text-gray-700 font-medium py-2 px-4 hover:bg-gray-100 rounded">Product</a>
                    <a href="/news-event.php" class="text-gray-700 font-medium py-2 px-4 hover:bg-gray-100 rounded">News & Event</a>
                    <a href="/video-tiktok.php" class="text-gray-700 font-medium py-2 px-4 hover:bg-gray-100 rounded">VDO</a>
                    <a href="/contact.php" class="text-gray-700 font-medium py-2 px-4 hover:bg-gray-100 rounded">Contact Us</a>
                                            <a href="/login.php" class="text-white py-2 px-4 rounded font-medium" style="background-color: #4B4899;">
                            <i class="fas fa-user mr-1"></i>เข้าสู่ระบบ
                        </a>
                                    </nav>
            </div>
        </div>
    </header>
    
    <script>
        // Mobile Menu Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            
            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                    
                    // Toggle icon
                    const icon = this.querySelector('i');
                    if (mobileMenu.classList.contains('hidden')) {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    } else {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-times');
                    }
                });
            }
        });
    </script>

<style>
    .product-card {
        transition: all 0.3s ease;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }
    .product-image {
        width: 100%;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    .product-card:hover .product-image {
        transform: scale(1.05);
    }
</style>

<!-- Breadcrumb -->
<div class="bg-white border-b">
    <div class="container mx-auto px-6 py-4">
        <nav class="flex items-center space-x-2 text-sm text-gray-600">
            <a href="index.php" class="hover:text-purple-600 transition">
                <i class="fas fa-home"></i> หน้าแรก
            </a>
            <span>/</span>
            <span class="text-gray-900 font-medium">สินค้า</span>
        </nav>
    </div>
</div>

<!-- Page Header -->
<section class="bg-purple-600 text-white py-12">
    <div class="container mx-auto px-6">
        <div class="text-center">
            <h1 class="text-4xl font-bold mb-4">
                <i class="fas fa-box-open mr-3"></i>Products
            </h1>
        </div>
    </div>
</section>

<!-- Products Grid -->
<section class="py-12">
    <div class="container mx-auto px-6">
                    <div class="grid md:grid-cols-2 gap-8">
                                    <div class="product-card bg-white rounded-2xl shadow-lg overflow-hidden">
                        <!-- Product Image -->
                        <div class="overflow-hidden">
                            <a href="single-product.php?id=6">
                                                                    <img src="/uploads/products/69806308a2b9f_1770021640.jpg" 
                                         alt="NeoFilera" 
                                         class="product-image w-full">
                                                            </a>
                        </div>

                        <!-- Product Content -->
                        <div class="p-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-3">
                                <a href="single-product.php?id=6" 
                                   class="hover:text-purple-600 transition">
                                    NeoFilera                                </a>
                            </h3>

                            <div class="text-gray-600 mb-4 line-clamp-3">
                                NeoFilera                            </div>

                            <a href="single-product.php?id=6" 
                               class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition">
                                <span>ดูรายละเอียด</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                                    <div class="product-card bg-white rounded-2xl shadow-lg overflow-hidden">
                        <!-- Product Image -->
                        <div class="overflow-hidden">
                            <a href="single-product.php?id=5">
                                                                    <img src="/uploads/products/698062586712c_1770021464.jpg" 
                                         alt="METEORA" 
                                         class="product-image w-full">
                                                            </a>
                        </div>

                        <!-- Product Content -->
                        <div class="p-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-3">
                                <a href="single-product.php?id=5" 
                                   class="hover:text-purple-600 transition">
                                    METEORA                                </a>
                            </h3>

                            <div class="text-gray-600 mb-4 line-clamp-3">
                                METEORA                            </div>

                            <a href="single-product.php?id=5" 
                               class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition">
                                <span>ดูรายละเอียด</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                                    <div class="product-card bg-white rounded-2xl shadow-lg overflow-hidden">
                        <!-- Product Image -->
                        <div class="overflow-hidden">
                            <a href="single-product.php?id=4">
                                                                    <img src="/uploads/products/698062617b6e2_1770021473.jpg" 
                                         alt="Variofill" 
                                         class="product-image w-full">
                                                            </a>
                        </div>

                        <!-- Product Content -->
                        <div class="p-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-3">
                                <a href="single-product.php?id=4" 
                                   class="hover:text-purple-600 transition">
                                    Variofill                                </a>
                            </h3>

                            <div class="text-gray-600 mb-4 line-clamp-3">
                                Variofill                            </div>

                            <a href="single-product.php?id=4" 
                               class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition">
                                <span>ดูรายละเอียด</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                                    <div class="product-card bg-white rounded-2xl shadow-lg overflow-hidden">
                        <!-- Product Image -->
                        <div class="overflow-hidden">
                            <a href="single-product.php?id=3">
                                                                    <img src="/uploads/products/698063388b2d4_1770021688.jpg" 
                                         alt="Hyabell" 
                                         class="product-image w-full">
                                                            </a>
                        </div>

                        <!-- Product Content -->
                        <div class="p-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-3">
                                <a href="single-product.php?id=3" 
                                   class="hover:text-purple-600 transition">
                                    Hyabell                                </a>
                            </h3>

                            <div class="text-gray-600 mb-4 line-clamp-3">
                                Hyabell                            </div>

                            <a href="single-product.php?id=3" 
                               class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition">
                                <span>ดูรายละเอียด</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                            </div>
            </div>
</section>

    <!-- Footer -->
    <footer class="bg-gray-100 py-12 mt-16">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <!-- Column 1: Company Info -->
                <div>
                    <div class="mb-4">
                        <img src="/uploads/kna-logo.png" alt="KNA Inter Pharma" class="h-16 mb-4">
                    </div>
                    <p class="text-gray-600 leading-relaxed">
                        K.N.A. Inter Pharma ผู้นำเข้าและจัดจำหน่ายผลิตภัณฑ์ความงามและสุขภาพระดับพรีเมียมจากต่างประเทศ มุ่งเน้นตอบทุกโจทย์ความต้องการของผู้ใช้ พร้อมส่งเสริมการเรียนรู้นวัตกรรมทางการแพทย์รูปแบบใหม่ให้เกิดกับวงการแพทย์ไทย
                    </p>
                </div>

                <!-- Column 2: Contact Info -->
                <div>
                    <h4 class="text-lg font-semibold mb-4 text-gray-800">บริษัท เค.เอ็น.เอ อินเตอร์ฟาร์มา จำกัด</h4>
                    <div class="space-y-2 text-gray-600">
                        <p>175-175/1 ถ.สวรรค์วิถี ต.ปากน้ำโพ อ.เมืองนครสวรรค์</p>
                        <p>จ.นครสวรรค์ 60000</p>
                        <p>โทร. 056-200890</p>
                    </div>
                </div>

                <!-- Column 3: Newsletter -->
                <div>
                    <h4 class="text-lg font-semibold mb-4 text-gray-800">กรอกข้อมูลเพื่อรับข่าวสารและโปรโมชั่นดีๆจากเรา</h4>
                    <form class="space-y-3">
                        <input type="email" placeholder="Email Address" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-[#4B4899]">
                        <button type="submit" class="w-full bg-[#4B4899] text-white px-6 py-3 rounded-lg hover:bg-[#3d3a7a] transition">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-gray-300 pt-6 text-center text-gray-600">
                <p>© All rights Reserved.</p>
            </div>
        </div>
    </footer>

    </body>
</html>
