<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ค้นหาคลินิก - Knainterphama</title>
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
</header>

    <!-- Search Section -->
    <section class="py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-3xl font-bold text-center mb-8" style="color: #4B4899;">ค้นหาคลินิกใกล้คุณ</h1>
                
                <form method="GET" class="space-y-6">
                    <!-- Search Input -->
                    <div>
                        <input type="text" name="search" value="" placeholder="ค้นหาด้วยชื่อคลินิก" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>

                    <!-- Product Checkboxes -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="products[]" value="Hyabell"  class="w-4 h-4 rounded" style="color: #4B4899; accent-color: #4B4899;">
                            <span class="text-gray-700">Hyabell</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="products[]" value="Variofill"  class="w-4 h-4 rounded" style="color: #4B4899; accent-color: #4B4899;">
                            <span class="text-gray-700">Variofill</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="products[]" value="Meteora Thread"  class="w-4 h-4 rounded" style="color: #4B4899; accent-color: #4B4899;">
                            <span class="text-gray-700">Meteora Thread</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="products[]" value="NeoFilera"  class="w-4 h-4 rounded" style="color: #4B4899; accent-color: #4B4899;">
                            <span class="text-gray-700">NeoFilera</span>
                        </label>
                    </div>

                    <!-- Province Dropdown -->
                    <div>
                        <select name="province" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent text-white font-medium" style="background-color: #4B4899;">
                            <option value="">เลือกจังหวัด</option>
                                                            <option value="กรุงเทพมหานคร" >กรุงเทพมหานคร</option>
                                                            <option value="กระบี่" >กระบี่</option>
                                                            <option value="กาญจนบุรี" >กาญจนบุรี</option>
                                                            <option value="กาฬสินธุ์" >กาฬสินธุ์</option>
                                                            <option value="กำแพงเพชร" >กำแพงเพชร</option>
                                                            <option value="ขอนแก่น" >ขอนแก่น</option>
                                                            <option value="จันทบุรี" >จันทบุรี</option>
                                                            <option value="ฉะเชิงเทรา" >ฉะเชิงเทรา</option>
                                                            <option value="ชลบุรี" >ชลบุรี</option>
                                                            <option value="ชัยนาท" >ชัยนาท</option>
                                                            <option value="ชัยภูมิ" >ชัยภูมิ</option>
                                                            <option value="ชุมพร" >ชุมพร</option>
                                                            <option value="เชียงราย" >เชียงราย</option>
                                                            <option value="เชียงใหม่" >เชียงใหม่</option>
                                                            <option value="ตรัง" >ตรัง</option>
                                                            <option value="ตราด" >ตราด</option>
                                                            <option value="ตาก" >ตาก</option>
                                                            <option value="นครนายก" >นครนายก</option>
                                                            <option value="นครปฐม" >นครปฐม</option>
                                                            <option value="นครพนม" >นครพนม</option>
                                                            <option value="นครราชสีมา" >นครราชสีมา</option>
                                                            <option value="นครศรีธรรมราช" >นครศรีธรรมราช</option>
                                                            <option value="นครสวรรค์" >นครสวรรค์</option>
                                                            <option value="นนทบุรี" >นนทบุรี</option>
                                                            <option value="นราธิวาส" >นราธิวาส</option>
                                                            <option value="น่าน" >น่าน</option>
                                                            <option value="บึงกาฬ" >บึงกาฬ</option>
                                                            <option value="บุรีรัมย์" >บุรีรัมย์</option>
                                                            <option value="ปทุมธานี" >ปทุมธานี</option>
                                                            <option value="ประจวบคีรีขันธ์" >ประจวบคีรีขันธ์</option>
                                                            <option value="ปราจีนบุรี" >ปราจีนบุรี</option>
                                                            <option value="ปัตตานี" >ปัตตานี</option>
                                                            <option value="พระนครศรีอยุธยา" >พระนครศรีอยุธยา</option>
                                                            <option value="พะเยา" >พะเยา</option>
                                                            <option value="พังงา" >พังงา</option>
                                                            <option value="พัทลุง" >พัทลุง</option>
                                                            <option value="พิจิตร" >พิจิตร</option>
                                                            <option value="พิษณุโลก" >พิษณุโลก</option>
                                                            <option value="เพชรบุรี" >เพชรบุรี</option>
                                                            <option value="เพชรบูรณ์" >เพชรบูรณ์</option>
                                                            <option value="แพร่" >แพร่</option>
                                                            <option value="ภูเก็ต" >ภูเก็ต</option>
                                                            <option value="มหาสารคาม" >มหาสารคาม</option>
                                                            <option value="มุกดาหาร" >มุกดาหาร</option>
                                                            <option value="แม่ฮ่องสอน" >แม่ฮ่องสอน</option>
                                                            <option value="ยโสธร" >ยโสธร</option>
                                                            <option value="ยะลา" >ยะลา</option>
                                                            <option value="ร้อยเอ็ด" >ร้อยเอ็ด</option>
                                                            <option value="ระนอง" >ระนอง</option>
                                                            <option value="ระยอง" >ระยอง</option>
                                                            <option value="ราชบุรี" >ราชบุรี</option>
                                                            <option value="ลพบุรี" >ลพบุรี</option>
                                                            <option value="ลำปาง" >ลำปาง</option>
                                                            <option value="ลำพูน" >ลำพูน</option>
                                                            <option value="เลย" >เลย</option>
                                                            <option value="ศรีสะเกษ" >ศรีสะเกษ</option>
                                                            <option value="สกลนคร" >สกลนคร</option>
                                                            <option value="สงขลา" >สงขลา</option>
                                                            <option value="สตูล" >สตูล</option>
                                                            <option value="สมุทรปราการ" >สมุทรปราการ</option>
                                                            <option value="สมุทรสงคราม" >สมุทรสงคราม</option>
                                                            <option value="สมุทรสาคร" >สมุทรสาคร</option>
                                                            <option value="สระแก้ว" >สระแก้ว</option>
                                                            <option value="สระบุรี" >สระบุรี</option>
                                                            <option value="สิงห์บุรี" >สิงห์บุรี</option>
                                                            <option value="สุโขทัย" >สุโขทัย</option>
                                                            <option value="สุพรรณบุรี" >สุพรรณบุรี</option>
                                                            <option value="สุราษฎร์ธานี" >สุราษฎร์ธานี</option>
                                                            <option value="สุรินทร์" >สุรินทร์</option>
                                                            <option value="หนองคาย" >หนองคาย</option>
                                                            <option value="หนองบัวลำภู" >หนองบัวลำภู</option>
                                                            <option value="อ่างทอง" >อ่างทอง</option>
                                                            <option value="อำนาจเจริญ" >อำนาจเจริญ</option>
                                                            <option value="อุดรธานี" >อุดรธานี</option>
                                                            <option value="อุตรดิตถ์" >อุตรดิตถ์</option>
                                                            <option value="อุทัยธานี" >อุทัยธานี</option>
                                                            <option value="อุบลราชธานี" >อุบลราชธานี</option>
                                                    </select>
                    </div>

                    <!-- Buttons -->
                    <div class="grid grid-cols-2 gap-4">
                        <button type="reset" onclick="window.location.href='searchpage.php'" class="w-full py-3 border-2 border-blue-500 text-blue-500 rounded-lg font-medium hover:bg-blue-50 transition">ค้นคา</button>
                        <button type="submit" class="w-full py-3 bg-blue-500 text-white rounded-lg font-medium hover:bg-blue-600 transition">ค้นหา</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Results Section -->
    
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
