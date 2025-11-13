<?php
// إصلاح شامل لقاعدة البيانات
$conn = mysqli_connect('localhost', 'root', '', 'aljabri_store');

if (!$conn) {
    die('فشل الاتصال بقاعدة البيانات: ' . mysqli_connect_error());
}

echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; direction: rtl; }
    .success { color: green; margin: 10px 0; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; }
    .error { color: red; margin: 10px 0; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; }
    .container { background: white; padding: 30px; border-radius: 10px; max-width: 800px; margin: 0 auto; }
</style>";

echo "<div class='container'>";
echo "<h2>إصلاح قاعدة البيانات - الجابري ستور</h2>";

// تعطيل فحص المفاتيح الخارجية مؤقتاً
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");

// 1. إنشاء جدول categories
echo "<h3>1. إنشاء جدول الفئات (categories)</h3>";
$create_categories = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if(mysqli_query($conn, $create_categories)) {
    echo "<div class='success'>✅ تم إنشاء جدول categories بنجاح</div>";
    
    // إضافة فئات افتراضية
    $categories = [
        ['إلكترونيات', 'الأجهزة الإلكترونية والكهربائية'],
        ['ملابس', 'ملابس رجالية ونسائية وأطفال'],
        ['منتجات منزلية', 'أدوات ومنتجات للاستخدام المنزلي'],
        ['هواتف وأجهزة لوحية', 'الهواتف الذكية والأجهزة اللوحية وملحقاتها'],
        ['أجهزة الكمبيوتر', 'لابتوبات، أجهزة مكتبية، وملحقاتها']
    ];
    
    foreach($categories as $category) {
        $name = mysqli_real_escape_string($conn, $category[0]);
        $description = mysqli_real_escape_string($conn, $category[1]);
        mysqli_query($conn, "INSERT IGNORE INTO categories (name, description) VALUES ('$name', '$description')");
    }
    echo "<div class='success'>✅ تم إضافة الفئات الافتراضية</div>";
} else {
    echo "<div class='error'>❌ فشل في إنشاء جدول categories: " . mysqli_error($conn) . "</div>";
}

// 2. إنشاء جدول products إذا لم يكن موجوداً
echo "<h3>2. التحقق من جدول المنتجات (products)</h3>";
$create_products = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price_yer DECIMAL(10,2) NOT NULL DEFAULT 0,
    price_sar DECIMAL(10,2) NOT NULL DEFAULT 0,
    price_usd DECIMAL(10,2) NOT NULL DEFAULT 0,
    main_image VARCHAR(255),
    featured BOOLEAN DEFAULT FALSE,
    category_id INT DEFAULT 1,
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if(mysqli_query($conn, $create_products)) {
    echo "<div class='success'>✅ تم إنشاء/التأكد من جدول products</div>";
    
    // إضافة بعض المنتجات النموذجية إذا لم تكن موجودة
    $check_products = mysqli_query($conn, "SELECT COUNT(*) as count FROM products");
    $count = mysqli_fetch_assoc($check_products)['count'];
    
    if($count == 0) {
        $sample_products = [
            "('هاتف سامسونج جالاكسي', 'هاتف ذكي بشاشة 6.7 بوصة وكاميرا رباعية', 350000, 1400, 373, 'phone.jpg', 1, 1, 15)",
            "('لابتوب ديل للأعمال', 'لابتوب مثالي للأعمال بمعالج Core i7', 800000, 3200, 853, 'laptop.jpg', 1, 1, 8)",
            "('سماعات ابل اللاسلكية', 'سماعات لاسلكية بتقنية إلغاء الضوضاء', 180000, 720, 192, 'headphones.jpg', 0, 1, 25)"
        ];
        
        foreach($sample_products as $product) {
            mysqli_query($conn, "INSERT INTO products (name, description, price_yer, price_sar, price_usd, main_image, featured, category_id, stock_quantity) VALUES $product");
        }
        echo "<div class='success'>✅ تم إضافة منتجات نموذجية</div>";
    } else {
        echo "<div class='success'>✅ يوجد $count منتج في قاعدة البيانات</div>";
    }
}

// 3. إنشاء جدول store_settings
echo "<h3>3. التحقق من جدول الإعدادات (store_settings)</h3>";
$create_settings = "CREATE TABLE IF NOT EXISTS store_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(255) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if(mysqli_query($conn, $create_settings)) {
    echo "<div class='success'>✅ تم إنشاء/التأكد من جدول store_settings</div>";
    
    // إضافة الإعدادات الافتراضية
    $default_settings = [
        ['store_name', 'الجابري ستور'],
        ['primary_color', '#FF6B35'],
        ['secondary_color', '#2C3E50'],
        ['announcement_text', '🔥 عروض خاصة - تخفيضات تصل إلى 50% 🔥'],
        ['store_address', 'اليمن - صنعاء - الصياح - امم محطة براش'],
        ['store_phone', '+967782090454'],
        ['store_whatsapp', '+967782090454'],
        ['welcome_message', 'مرحباً بكم في متجر الجابري ستور - أفضل المنتجات بأفضل الأسعار']
    ];
    
    foreach($default_settings as $setting) {
        mysqli_query($conn, "INSERT IGNORE INTO store_settings (setting_key, setting_value) VALUES ('$setting[0]', '$setting[1]')");
    }
    echo "<div class='success'>✅ تم إضافة الإعدادات الافتراضية</div>";
}

// 4. إنشاء جدول admin_users
echo "<h3>4. التحقق من جدول المسؤولين (admin_users)</h3>";
$create_admin = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if(mysqli_query($conn, $create_admin)) {
    echo "<div class='success'>✅ تم إنشاء/التأكد من جدول admin_users</div>";
    
    // إنشاء أو تحديث حساب المسؤول
    $username = 'admin';
    $password = 'admin123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // حذف أي حساب موجود بنفس الاسم أولاً
    mysqli_query($conn, "DELETE FROM admin_users WHERE username = '$username'");
    
    // إضافة الحساب الجديد
    $insert_sql = "INSERT INTO admin_users (username, password_hash, full_name) VALUES ('$username', '$hashed_password', 'مدير النظام')";
    if(mysqli_query($conn, $insert_sql)) {
        echo "<div class='success'>✅ تم إنشاء حساب المسؤول بنجاح</div>";
    }
}

// 5. إنشاء الجداول الأخرى
echo "<h3>5. إنشاء الجداول المساعدة</h3>";

$other_tables = [
    'product_images' => "CREATE TABLE IF NOT EXISTS product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT,
        image_path VARCHAR(255),
        is_main BOOLEAN DEFAULT FALSE
    )",
    
    'orders' => "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(20) NOT NULL,
        customer_address TEXT NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    'order_items' => "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_id INT,
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL
    )"
];

foreach($other_tables as $table_name => $sql) {
    if(mysqli_query($conn, $sql)) {
        echo "<div class='success'>✅ تم إنشاء/التأكد من جدول $table_name</div>";
    } else {
        echo "<div class='error'>❌ فشل في إنشاء جدول $table_name: " . mysqli_error($conn) . "</div>";
    }
}

// إعادة تفعيل فحص المفاتيح الخارجية
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

// عرض ملخص
echo "<h3>🎉 تم الانتهاء من الإصلاح</h3>";
echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px;'>";
echo "<h4>بيانات الدخول إلى لوحة التحكم:</h4>";
echo "<p><strong>اسم المستخدم:</strong> admin</p>";
echo "<p><strong>كلمة المرور:</strong> admin123</p>";
echo "</div>";

echo "<div style='margin-top: 20px;'>";
echo "<a href='login.php' style='display: inline-block; padding: 12px 24px; background: #FF6B35; color: white; text-decoration: none; border-radius: 5px; margin: 5px; font-weight: bold;'>🚀 الدخول إلى لوحة التحكم</a>";
echo "<a href='../index.php' style='display: inline-block; padding: 12px 24px; background: #2C3E50; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>🏠 عرض المتجر</a>";
echo "</div>";

echo "</div>";

mysqli_close($conn);
?>