<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Channel - TikTok Videos</title>
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
    .video-card {
        transition: all 0.3s ease;
    }
    .video-card:hover {
        transform: translateY(-5px);
    }
    .tiktok-embed {
        border-radius: 12px;
        overflow: hidden;
    }
</style>

<!-- Hero Section -->
<section class="relative bg-gradient-to-r from-[#4B4899] to-[#7B2DFF] text-white py-20">
    <div class="container mx-auto px-6">
        <div class="text-center">
            <div class="mb-6 flex justify-center">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                    <i class="fab fa-tiktok text-5xl"></i>
                </div>
            </div>
            <h1 class="text-5xl font-bold mb-4">VIDEO CHANNEL</h1>
            <p class="text-xl opacity-90 max-w-2xl mx-auto">
                ติดตามเนื้อหาและความรู้ด้านสุขภาพ ผลิตภัณฑ์ยา และข้อมูลที่น่าสนใจจากเรา
            </p>
            <div class="mt-6 flex items-center justify-center gap-4">
                <span class="px-4 py-2 bg-white/20 rounded-full text-sm backdrop-blur-sm">
                    <i class="fas fa-video mr-2"></i>31 วิดีโอ
                </span>
                <a href="https://www.tiktok.com/@knainterpharma" target="_blank" class="px-6 py-2 bg-white text-[#4B4899] rounded-full font-semibold hover:bg-gray-100 transition">
                    <i class="fab fa-tiktok mr-2"></i>ติดตามเรา
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Videos Grid Section -->
<section class="py-16">
    <div class="container mx-auto px-6">
                <!-- Videos Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="0">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7558419796153388309?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7558419796153388309" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7558419796153388309?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #1                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        ใครมีปัญหา #สะโพกบุ๋ม #ก้นแบน บ้าง? 🙋‍♀️ จบปัญหานี้ด้วย VARIOFILL® ฟิลเลอร์ปั้นก้นจากเยอรมนี 🇩🇪✨ ปั้นทรงสวยเป๊ะ เนียนเป็นธรรมชาติ ไม่ต้องพักฟื้นนาน! 🍑                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7558419796153388309?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="50">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7551288279899966728?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7551288279899966728" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7551288279899966728?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #2                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        Time to glow up! ✨ คืนผิวเด็ก อิ่มฟู เปล่งปลั่ง ด้วย NeoFilera กระตุ้นคอลลาเจน ด้วยกลไกทางกายภาพให้ผิวคุณดูดีขึ้นจากภายใน                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7551288279899966728?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="100">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7540155590882921735?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7540155590882921735" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7540155590882921735?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #3                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        ✨ ท้าพิสูจน์! ผิวคอสวยเป๊ะ ไม่ต้องง้อฟิลเตอร์ ✨ #Neofilera เปลี่ยนผิวคอให้ปัง ลดริ้วรอย กระชับขึ้น จนใครๆ ก็ทัก!                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7540155590882921735?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="150">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7533120698097700117?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7533120698097700117" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7533120698097700117?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #4                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        &quot;เพราะปัญหาใต้ตา...ต้องการความใส่ใจเป็นพิเศษ&quot; . K.N.A. Inter Pharma ขอขอบคุณคลินิกพันธมิตร ที่เล็งเห็นถึงคุณภาพและเลือกใช้ NeoFilera ในการแก้ปัญหาใต้ตาอย่างตรงจุด เพื่อมอบผลลัพธ์ที่ดีที่สุดให้กับคนไข้ทุกคน .                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7533120698097700117?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="200">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7530482673714859271?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7530482673714859271" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7530482673714859271?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #5                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        ผิวแก่เพราะฮอร์โมน? 😱 แก้ได้! คุณหมอบอกว่าผิวก็เหมือนต้นไม้ อยากสวยนานๆ ต้องเตรียมดิน (ปรับฮอร์โมน) + ใส่ปุ๋ย (Biostimulator) 🌱✨ ตัวช่วยกระตุ้นคอลลาเจนผิวเด็ก! #ผิวสวยยั่งยืน                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7530482673714859271?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="250">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7527970088998423816?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7527970088998423816" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7527970088998423816?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #6                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        ถอดรหัสผิวเด็ก! 🧬 ฮอร์โมน vs Biostimulator อะไรคือคำตอบ? #ถามไวตอบไวหมอจั๊กจั่น มีคำตอบ!                     </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7527970088998423816?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="300">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7524597067713350920?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7524597067713350920" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7524597067713350920?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #7                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        Filler vs Biostimulator ต่างกันยังไง? 👩‍⚕️✨ หมอป่านมาตอบให้แล้ว!                     </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7524597067713350920?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="350">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7520489734775328018?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7520489734775328018" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7520489734775328018?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #8                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        ใต้ตาคล้ำเพราะนอนน้อย? มาฟังคำตอบจากหมอจิ๊ก!                     </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7520489734775328018?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="400">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7519813148887584020?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7519813148887584020" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7519813148887584020?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #9                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        สรุปให้แล้ว! Q&amp;A ปัญหาใต้ตาแบบติดจรวด 🚀                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7519813148887584020?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="450">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7517888919153822977?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7517888919153822977" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7517888919153822977?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #10                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        หน้าพังเพราะแดด ☀️➡️ หน้าปังด้วย Biostimulator ✨ ดูวิธีเปลี่ยนผิวกับหมอก้อง!                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7517888919153822977?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="500">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7516488698549128469?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7516488698549128469" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7516488698549128469?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #11                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        แดดแรงแค่ไหนก็ไม่กลัว! 🌞 ใครมีปัญหาผิวช่วงหน้าร้อน ทั้งหมองคล้ำ ไม่สดใส เชิญทางนี้!                     </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7516488698549128469?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="550">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7516488698549128469?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7516488698549128469" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7516488698549128469?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #12                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        แดดแรงแค่ไหนก็ไม่กลัว! 🌞 ใครมีปัญหาผิวช่วงหน้าร้อน ทั้งหมองคล้ำ ไม่สดใส เชิญทางนี้!                     </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7516488698549128469?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="600">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7509338189195218184?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7509338189195218184" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7509338189195218184?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #13                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        Biostimulators แค่กระแส หรือเปลี่ยนผิวผู้ชายได้จริง? 🤔 หมอเก้ามาตอบทุกคำถามแบบไวๆ ในคลิปนี้!                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7509338189195218184?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="650">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7507570077965896978?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7507570077965896978" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7507570077965896978?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #14                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        🤔 Biostimulators: เป็นแค่เทรนด์ฮิตที่มาแล้วก็ไป หรือคือนวัตกรรมที่จะเปลี่ยนชีวิตผิวผู้ชายได้จริง?                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7507570077965896978?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="700">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7506788728111762696?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7506788728111762696" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7506788728111762696?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #15                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        ถามไวตอบไว! 💬 #หมอพีช ไขเคล็ดลับผิวสวยใสอ่อนกว่าวัย ด้วย &quot;นอนดี + PDLLA&quot; ✨ ดูเลย!                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7506788728111762696?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="750">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7504899697694313735?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7504899697694313735" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7504899697694313735?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #16                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        ✨ นอนดีมีชัยไปกว่าครึ่ง! . รู้หรือไม่? การนอนหลับที่มีคุณภาพคือสุดยอดการฟื้นฟูผิวตามธรรมชาติ และเมื่อเสริมทัพด้วยนวัตกรรม PDLLA                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7504899697694313735?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="800">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7502334454829010183?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7502334454829010183" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7502334454829010183?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #17                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        ปัญหาเรื่องสะโพก... กำลังลดทอนความสุขและความมั่นใจ ของคุณแม่ยุคใหม่โดยไม่รู้ตัวหรือเปล่า? 🤔                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7502334454829010183?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="850">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7489349578198682888?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7489349578198682888" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7489349578198682888?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #18                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        เปิดเคล็ดลับการปรับรูปหน้าให้สวยเป๊ะ แบบไม่ต้องพึ่งมีดหมอ! 🤫 ใครอยากหน้าเรียว กรอบหน้าชัด ผิวใสเด้ง                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7489349578198682888?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="900">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7484212768023825671?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7484212768023825671" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7484212768023825671?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #19                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        &quot;รู้หรือไม่? 🧐 การมีรูปร่างที่ดีไม่ได้หมายถึงแค่ผอม!&quot;                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7484212768023825671?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="950">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7480008003643624725?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7480008003643624725" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7480008003643624725?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #20                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        &quot;หน้าดวงจันทร์&quot; กวนใจ ทำไงดี? 🤔  . คุณหมอมาไขข้อสงสัยทุกเรื่องหลุมสิว!                     </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7480008003643624725?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="1000">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7478558421587922196?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7478558421587922196" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7478558421587922196?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #21                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        ผิวสวยสู้ฝุ่น! 💪 #HyabellMeso . ปกป้องผิวจาก PM2.5 พร้อมบำรุงล้ำลึก ให้ผิวแข็งแรงจากภายใน 💖                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7478558421587922196?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="1050">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7478176707300592914?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7478176707300592914" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7478176707300592914?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #22                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        อัปเลเวลความสวย!  ยกกระชับใบหน้าด้วย #MeteoraThread  สวยเป๊ะปังระดับ #Oscars ในแบบคุณ!                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7478176707300592914?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="1100">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7476331447138127122?is_from_webapp=1&amp;sender_device=pc" 
                                    data-video-id="7476331447138127122" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7476331447138127122?is_from_webapp=1&amp;sender_device=pc">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #23                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        ฟิลเลอร์ 101: ปัญหาฟิลเลอร์ ก้อน ไหล จริงหรือ? 🤔                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7476331447138127122?is_from_webapp=1&amp;sender_device=pc" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="1150">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7460351364858744081?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7593816803298838034" 
                                    data-video-id="7460351364858744081" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7460351364858744081?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7593816803298838034">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #24                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        หน้าแบบไหนถึงจะเรียกว่าโหงวเฮ้งดี? 🤔 . ปรับรูปหน้ายังไงให้รับทรัพย์ปังๆ ตลอดปี 2026? ✨                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7460351364858744081?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7593816803298838034" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="1200">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7584296100891200786?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7565201294287177224" 
                                    data-video-id="7584296100891200786" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7584296100891200786?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7565201294287177224">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #25                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        Invest in yourself, it pays the best interest. 💎 ส่งท้ายปีด้วยการลงทุนกับตัวเอง... เพื่อผลลัพธ์ที่คุ้มค่าที่สุด                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            30/12/2025                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7584296100891200786?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7565201294287177224" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="1250">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7587637788179402004?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7565201294287177224" 
                                    data-video-id="7587637788179402004" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7587637788179402004?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7565201294287177224">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #26                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                         ของขวัญชิ้นสำคัญ... คือความมั่นใจในแบบที่เป็นคุณ . เทศกาลแห่งความสุขปีนี้ นอกจากมอบของขวัญให้คนรอบข้างแล้ว อย่าลืมมอบ &quot;ความใส่ใจ&quot; ให้กับตัวเอง                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            30/12/2025                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7587637788179402004?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7565201294287177224" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="1300">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7581757318925225217?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7565201294287177224" 
                                    data-video-id="7581757318925225217" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7581757318925225217?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7565201294287177224">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #27                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        🛋️ หน้ายุบเป็น &quot;โซฟาเก่า&quot; หรือเปล่า? มาซ่อมสปริงด่วน! . เคยสงสัยมั้ย? ทำไมทาครีมเท่าไหร่หน้าก็ไม่ฟูสักที? 🤔                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            30/12/2025                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7581757318925225217?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7565201294287177224" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="1350">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7561679367613549844?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7565201294287177224" 
                                    data-video-id="7561679367613549844" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7561679367613549844?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7565201294287177224">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #28                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        ☁️ The Feather-Light Lift is Real. ☁️ . #MeteoraThread ไหมยกกระชับที่เรากล้าท้าให้ลอง! ไม่ใช่แค่การยก แต่ช่วยสร้างมิติที่ละเอียดอ่อนที่สุดบนใบหน้า                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            30/12/2025                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7561679367613549844?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7565201294287177224" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="1400">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7474807114347629844?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7593816803298838034" 
                                    data-video-id="7474807114347629844" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7474807114347629844?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7593816803298838034">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #29                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        เข้าสู่วัย 40+ ใครว่าฝ้า กระ จุดด่างดำแก้ยาก?                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            23/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7474807114347629844?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7593816803298838034" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="1450">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7522794012605025543?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7593816803298838034" 
                                    data-video-id="7522794012605025543" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7522794012605025543?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7593816803298838034">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #30                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        จุดเริ่มต้นผิว 'Young' ไม่ใช่แค่ทาครีม! 🤫                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            15/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7522794012605025543?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7593816803298838034" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                        <div class="video-card bg-white rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up" data-aos-delay="1500">
                <!-- Video Embed -->
                <div class="aspect-[9/16] bg-gray-100 relative">
                                            <blockquote class="tiktok-embed" 
                                    cite="https://www.tiktok.com/@knainterpharma/video/7504140060984429831?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7593816803298838034" 
                                    data-video-id="7504140060984429831" 
                                    style="max-width: 100%; min-width: 100%; height: 100%;">
                            <section>
                                <a target="_blank" 
                                   title="@knainterpharma" 
                                   href="https://www.tiktok.com/@knainterpharma/video/7504140060984429831?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7593816803298838034">
                                   @knainterpharma
                                </a>
                            </section>
                        </blockquote>
                                        
                    <!-- Video Number Badge -->
                    <div class="absolute top-3 left-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                        #31                    </div>
                </div>
                
                <!-- Video Info -->
                                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
                        คุณแม่ยุคใหม่ฟังทางนี้! หมอออมมาตอบทุกคำถามเรื่องสะโพกแล้ว 🍑                    </h3>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="far fa-clock mr-1"></i>
                            15/01/2026                        </span>
                        <a href="https://www.tiktok.com/@knainterpharma/video/7504140060984429831?is_from_webapp=1&amp;sender_device=pc&amp;web_id=7593816803298838034" 
                           target="_blank" 
                           class="text-[#4B4899] hover:text-[#7B2DFF] font-medium">
                            ดูเพิ่มเติม <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
                            </div>
                    </div>

        <!-- Load More Button (if needed) -->
                <div class="text-center mt-12">
            <button class="px-8 py-3 bg-[#4B4899] text-white rounded-full font-semibold hover:bg-[#7B2DFF] transition">
                <i class="fas fa-chevron-down mr-2"></i>โหลดเพิ่มเติม
            </button>
        </div>
        
            </div>
</section>

<!-- AOS Animation Library -->
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css" />
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });
</script>

<!-- TikTok Embed Script -->
<script async src="https://www.tiktok.com/embed.js"></script>

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

    
    <script>
        // Refresh TikTok embeds after page load
        window.addEventListener("load", function() {
            if (window.tiktok && window.tiktok.embed) {
                window.tiktok.embed.refresh();
            }
        });
    </script>
</body>
</html>
