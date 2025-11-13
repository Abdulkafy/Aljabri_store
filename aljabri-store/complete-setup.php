<?php
// ملف الإعداد الشامل لقاعدة البيانات - الإصدار المصحح
$conn = mysqli_connect('localhost', 'root', '', 'aljabri_store');

if(!$conn) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .success { color: green; margin: 5px 0; }
    .error { color: red; margin: 5px 0; }
    .warning { color: orange; margin: 5px 0; }
    .container { background: white; padding: 20px; border-radius: 10px; max-width: 800px; margin: 0 auto; }
</style>";

echo "<div class='container'>";
echo "<h2>بدء الإعداد الشامل لقاعدة البيانات...</h2>";

// 1. التحقق من وجود جدول products وإنشائه بالكامل إذا لم يكن موجوداً
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'products'");
if(mysqli_num_rows($check_table) == 0) {
    // إنشاء الجدول بالكامل
    $create_products_table = "CREATE TABLE products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price_yer DECIMAL(10,2) NOT NULL,
        price_sar DECIMAL(10,2) NOT NULL,
        price_usd DECIMAL(10,2) NOT NULL,
        main_image VARCHAR(255),
        featured BOOLEAN DEFAULT FALSE,
        category_id INT DEFAULT 1,
        stock_quantity INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if(mysqli_query($conn, $create_products_table)) {
        echo "<div class='success'>✅ تم إنشاء جدول products بنجاح</div>";
    } else {
        echo "<div class='error'>❌ خطأ في إنشاء جدول products: " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "<div class='success'>✅ جدول products موجود بالفعل</div>";
    
    // إضافة الأعمدة المفقودة واحداً تلو الآخر
    $columns_to_add = [
        'price_usd' => "ALTER TABLE products ADD COLUMN price_usd DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER price_sar",
        'main_image' => "ALTER TABLE products ADD COLUMN main_image VARCHAR(255) AFTER price_usd",
        'featured' => "ALTER TABLE products ADD COLUMN featured BOOLEAN DEFAULT FALSE AFTER main_image",
        'category_id' => "ALTER TABLE products ADD COLUMN category_id INT DEFAULT 1 AFTER featured",
        'stock_quantity' => "ALTER TABLE products ADD COLUMN stock_quantity INT DEFAULT 0 AFTER category_id",
        'created_at' => "ALTER TABLE products ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
        'updated_at' => "ALTER TABLE products ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    ];
    
    foreach($columns_to_add as $column_name => $sql) {
        // التحقق إذا كان العمود موجوداً بالفعل
        $check_column = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE '$column_name'");
        if(mysqli_num_rows($check_column) == 0) {
            if(mysqli_query($conn, $sql)) {
                echo "<div class='success'>✅ تم إضافة عمود $column_name</div>";
            } else {
                echo "<div class='error'>❌ خطأ في إضافة $column_name: " . mysqli_error($conn) . "</div>";
            }
        } else {
            echo "<div class='success'>✅ عمود $column_name موجود بالفعل</div>";
        }
    }
}

// 2. التحقق من البيانات الأساسية في products
$check_data = mysqli_query($conn, "SELECT COUNT(*) as count FROM products");
$row = mysqli_fetch_assoc($check_data);
if($row['count'] == 0) {
    echo "<div class='warning'>📝 إضافة بيانات اختبارية للمنتجات...</div>";
    
    $sample_products = [
        "('هاتف ذكي متطور', 'هاتف ذكي بشاشة 6.7 بوصة وكاميرا رباعية', 250000, 1000, 267, 'phone.jpg', 1, 1, 15)",
        "('لابتوب للألعاب', 'لابتوب مخصص للألعاب بمعالج قوي وكارت شاشة متميز', 800000, 3200, 853, 'laptop.jpg', 1, 1, 8)",
        "('سماعات لاسلكية', 'سماعات لاسلكية بتقنية إلغاء الضوضاء النشط', 120000, 480, 128, 'headphones.jpg', 0, 1, 25)",
        "('ساعة ذكية رياضية', 'ساعة ذكية بميزات تتبع اللياقة البدنية والصحة', 90000, 360, 96, 'watch.jpg', 1, 1, 12)",
        "('كاميرا رقمية', 'كاميرا رقمية بدقة 24 ميجابكسل للتصوير الاحترافي', 350000, 1400, 373, 'camera.jpg', 0, 1, 6)"
    ];
    
    $success_count = 0;
    foreach($sample_products as $product) {
        $sql = "INSERT INTO products (name, description, price_yer, price_sar, price_usd, main_image, featured, category_id, stock_quantity) VALUES $product";
        if(mysqli_query($conn, $sql)) {
            $success_count++;
        }
    }
    echo "<div class='success'>✅ تم إضافة $success_count منتج نموذجي</div>";
} else {
    echo "<div class='success'>✅ يوجد " . $row['count'] . " منتج في قاعدة البيانات</div>";
}

// 3. إنشاء وإعداد جدول store_settings
$check_settings_table = mysqli_query($conn, "SHOW TABLES LIKE 'store_settings'");
if(mysqli_num_rows($check_settings_table) == 0) {
    $create_settings_table = "CREATE TABLE store_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(255) UNIQUE NOT NULL,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if(mysqli_query($conn, $create_settings_table)) {
        echo "<div class='success'>✅ تم إنشاء جدول store_settings</div>";
    } else {
        echo "<div class='error'>❌ خطأ في إنشاء store_settings: " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "<div class='success'>✅ جدول store_settings موجود بالفعل</div>";
}

// إضافة الإعدادات الافتراضية
$default_settings = [
    "store_name" => "الجابري ستور",
    "primary_color" => "#FF6B35", 
    "secondary_color" => "#2C3E50",
    "announcement_text" => "🔥 عروض خاصة - تخفيضات تصل إلى 50% 🔥",
    "store_address" => "اليمن - صنعاء - الصياح - امم محطة براش",
    "store_phone" => "+967782090454",
    "store_whatsapp" => "+967782090454",
    "welcome_message" => "مرحباً بكم في متجر الجابري ستور - أفضل المنتجات بأفضل الأسعار"
];

foreach($default_settings as $key => $value) {
    $sql = "INSERT IGNORE INTO store_settings (setting_key, setting_value) VALUES ('$key', '$value')";
    mysqli_query($conn, $sql);
}
echo "<div class='success'>✅ تم إضافة الإعدادات الافتراضية</div>";

// 4. إنشاء وإعداد جدول admin_users
$check_admin_table = mysqli_query($conn, "SHOW TABLES LIKE 'admin_users'");
if(mysqli_num_rows($check_admin_table) == 0) {
    $create_admin_table = "CREATE TABLE admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if(mysqli_query($conn, $create_admin_table)) {
        echo "<div class='success'>✅ تم إنشاء جدول admin_users</div>";
    }
}

// إضافة مسؤول النظام
$admin_sql = "INSERT IGNORE INTO admin_users (username, password_hash, full_name) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدير النظام')";
if(mysqli_query($conn, $admin_sql)) {
    echo "<div class='success'>✅ تم إضافة مسؤول النظام</div>";
}

// 5. إنشاء الجداول الأخرى
$other_tables = [
    "product_images" => "CREATE TABLE IF NOT EXISTS product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT,
        image_path VARCHAR(255),
        is_main BOOLEAN DEFAULT FALSE
    )",
    
    "orders" => "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(20) NOT NULL,
        customer_address TEXT NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "order_items" => "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_id INT,
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL
    )",
    
    "categories" => "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach($other_tables as $table_name => $sql) {
    if(mysqli_query($conn, $sql)) {
        echo "<div class='success'>✅ تم إنشاء/التأكد من جدول $table_name</div>";
    }
}

// إضافة فئات افتراضية
$categories_sql = "INSERT IGNORE INTO categories (name, description) VALUES 
    ('إلكترونيات', 'الأجهزة الإلكترونية والكهربائية'),
    ('ملابس', 'ملابس رجالية ونسائية وأطفال'),
    ('منتجات منزلية', 'أدوات ومنتجات للاستخدام المنزلي')";
    
if(mysqli_query($conn, $categories_sql)) {
    echo "<div class='success'>✅ تم إضافة الفئات الافتراضية</div>";
}

echo "<h3 style='color: green;'>🎉 تم الانتهاء من الإعداد الشامل بنجاح!</h3>";

echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>روابط مهمة:</h4>";
echo "<ul>";
echo "<li><a href='index.php' style='color: #FF6B35; font-weight: bold; text-decoration: none;'>🏠 زيارة المتجر الرئيسي</a></li>";
echo "<li><a href='products.php' style='color: #2C3E50; font-weight: bold; text-decoration: none;'>🛍️ عرض جميع المنتجات</a></li>";
echo "<li><a href='admin/login.php' style='color: #FF6B35; font-weight: bold; text-decoration: none;'>⚙️ الدخول إلى لوحة التحكم</a></li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
echo "<h4>بيانات الدخول إلى لوحة التحكم:</h4>";
echo "<p><strong>اسم المستخدم:</strong> admin</p>";
echo "<p><strong>كلمة المرور:</strong> admin123</p>";
echo "</div>";

echo "</div>"; // إغلاق container

mysqli_close($conn);
?>