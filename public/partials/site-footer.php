<!-- Footer -->
    <footer class="bg-gray-200 py-12 mt-16">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <!-- Column 1: Company Info -->
                <div>
                    <div class="mb-4">
                        <img src="/uploads/logo-kna.png" style="height:3.5rem;width:auto;" alt="Knainterphama Logo" class="header__logoImg">
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
                <div id="newsletter">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800">กรอกข้อมูลเพื่อรับข่าวสารและโปรโมชั่นดีๆจากเรา</h4>
                    <?php $subscribed = (string) ($_GET['subscribed'] ?? ''); ?>
                    <?php if ($subscribed === 'ok'): ?>
                        <p class="mb-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2">สมัครรับข่าวสารเรียบร้อยแล้ว ขอบคุณครับ</p>
                    <?php elseif ($subscribed === 'invalid'): ?>
                        <p class="mb-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2">กรุณากรอกอีเมลให้ถูกต้อง</p>
                    <?php elseif ($subscribed === 'error'): ?>
                        <p class="mb-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2">ไม่สามารถสมัครได้ กรุณาลองใหม่</p>
                    <?php endif; ?>
                    <form class="space-y-3" method="post" action="/newsletter-subscribe.php">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="redirect" value="<?php echo h(strtok((string) ($_SERVER['REQUEST_URI'] ?? '/'), '?')); ?>">
                        <input type="email" name="email" placeholder="Email Address" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-[#4B4899]">
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
