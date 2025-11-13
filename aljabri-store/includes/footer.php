    </main>

    <!-- معلومات المتجر -->
    <section class="store-info">
        <div class="container">
            <div class="info-grid">
                <div class="info-item">
                    <h3>عنوان المتجر</h3>
                    <p><?php echo $settings['store_address'] ?? 'اليمن - صنعاء - الصياح - امم محطة براش'; ?></p>
                </div>
                <div class="info-item">
                    <h3>طرق الدفع</h3>
                    <div class="payment-methods">
                        <span>كريمي جوال</span>
                        <span>جيب</span>
                        <span>ون كاش</span>
                        <span>فلوسك</span>
                        <span>جوالي</span>
                        <span>كاش</span>
                    </div>
                </div>
                <div class="info-item">
                    <h3>اتصل بنا</h3>
                    <p>هاتف: <?php echo $settings['store_phone'] ?? '+967782090454'; ?></p>
                    <p>واتساب: <?php echo $settings['store_whatsapp'] ?? '+967782090454'; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- الفوتر -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo $settings['store_name'] ?? 'الجابري ستور'; ?></h3>
                    <p><?php echo $settings['welcome_message'] ?? 'أفضل المنتجات بأفضل الأسعار مع خدمة التوصيل لجميع أنحاء اليمن'; ?></p>
                </div>
                <div class="footer-section">
                    <h3>روابط سريعة</h3>
                    <ul>
                        <li><a href="index.php">الرئيسية</a></li>
                        <li><a href="products.php">المنتجات</a></li>
                        <li><a href="cart.php">عربة التسوق</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>معلومات الاتصال</h3>
                    <p><?php echo $settings['store_phone'] ?? '+967782090454'; ?></p>
                    <p><?php echo $settings['store_address'] ?? 'اليمن - صنعاء - الصياح - امم محطة براش'; ?></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo $settings['store_name'] ?? 'الجابري ستور'; ?>. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>