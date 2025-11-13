<?php
// إعداد قاعدة البيانات
$host = "localhost";
$username = "root";
$password = "";
$database = "aljabri_store";

// الاتصال بقاعدة البيانات
$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("❌ فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .info { background: #cce5ff; color: #004085; padding: 15px; border-radius: 5px; margin: 10px 0; }
</style>";

echo "<h2>🚀 الإعداد الكامل لنظام متجر الجابري</h2>";

// الخطوة 1: إنشاء الجداول إذا لم تكن موجودة
$tables = [
    // جدول إعدادات المتجر
    "CREATE TABLE IF NOT EXISTS store_settings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        store_name VARCHAR(255) NOT NULL DEFAULT 'متجر الجابري',
        primary_color VARCHAR(7) DEFAULT '#3498db',
        secondary_color VARCHAR(7) DEFAULT '#2c3e50',
        announcement_text TEXT,
        welcome_message TEXT,
        store_address TEXT,
        store_phone VARCHAR(20),
        store_whatsapp VARCHAR(20),
        store_logo VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // جدول المنتجات
    "CREATE TABLE IF NOT EXISTS products (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price_yer DECIMAL(10,2) NOT NULL DEFAULT 0,
        price_sar DECIMAL(10,2) DEFAULT 0,
        price_usd DECIMAL(10,2) DEFAULT 0,
        main_image VARCHAR(255),
        featured TINYINT(1) DEFAULT 0,
        stock_quantity INT DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // جدول الطلبات
    "CREATE TABLE IF NOT EXISTS orders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_number VARCHAR(50) UNIQUE,
        customer_name VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(20) NOT NULL,
        customer_email VARCHAR(255),
        customer_address TEXT,
        customer_city VARCHAR(100),
        customer_area VARCHAR(100),
        customer_street VARCHAR(200),
        customer_building VARCHAR(50),
        customer_apartment VARCHAR(50),
        customer_notes TEXT,
        total DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) DEFAULT 'cash',
        status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // جدول عناصر الطلبات
    "CREATE TABLE IF NOT EXISTS order_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // جدول المستخدمين (للمسؤولين)
    "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($tables as $table_sql) {
    if (mysqli_query($conn, $table_sql)) {
        echo "<div class='success'>✅ تم إنشاء الجدول بنجاح</div>";
    } else {
        echo "<div class='error'>❌ خطأ في إنشاء الجدول: " . mysqli_error($conn) . "</div>";
    }
}

// الخطوة 2: إدخال إعدادات افتراضية للمتجر
$check_settings = "SELECT COUNT(*) as count FROM store_settings";
$result = mysqli_query($conn, $check_settings);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    $settings_sql = "INSERT INTO store_settings (store_name, primary_color, secondary_color, announcement_text, welcome_message, store_address, store_phone, store_whatsapp) 
                     VALUES ('متجر الجابري', '#3498db', '#2c3e50', 'مرحباً بكم في متجرنا، شحن مجاني للطلبات فوق 50000 ريال', 'أهلاً وسهلاً بكم في متجر الجابري لأفضل المنتجات', 'صنعاء، اليمن', '+967123456789', '+967123456789')";

    if (mysqli_query($conn, $settings_sql)) {
        echo "<div class='success'>✅ تم إضافة الإعدادات الافتراضية بنجاح</div>";
    } else {
        echo "<div class='error'>❌ خطأ في إضافة الإعدادات: " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "<div class='info'>ℹ️ الإعدادات موجودة مسبقاً</div>";
}

// الخطوة 3: إدخال مستخدم مسؤول افتراضي
$check_admin = "SELECT COUNT(*) as count FROM users WHERE username = 'admin'";
$result = mysqli_query($conn, $check_admin);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $admin_sql = "INSERT INTO users (username, password, name, role) 
                  VALUES ('admin', '$admin_password', 'مدير النظام', 'admin')";

    if (mysqli_query($conn, $admin_sql)) {
        echo "<div class='success'>✅ تم إنشاء المستخدم المسؤول بنجاح</div>";
        echo "<div class='info'>🔑 اسم المستخدم: admin<br>🔑 كلمة المرور: admin123</div>";
    } else {
        echo "<div class='error'>❌ خطأ في إنشاء المستخدم: " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "<div class='info'>ℹ️ المستخدم المسؤول موجود مسبقاً</div>";
}

// الخطوة 4: إضافة بعض المنتجات الافتراضية
$check_products = "SELECT COUNT(*) as count FROM products";
$result = mysqli_query($conn, $check_products);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    $products_sql = "INSERT INTO products (name, description, price_yer, price_sar, price_usd, stock_quantity, featured) VALUES
        ('ساعة ذكية', 'ساعة ذكية متطورة مع شاشة تعمل باللمس', 25000, 400, 100, 50, 1),
        ('هاتف محمول', 'هاتف ذكي بشاشة 6.5 بوصة وكاميرا مزدوجة', 45000, 720, 180, 30, 1),
        ('سماعات لاسلكية', 'سماعات بلوتوث عالية الجودة', 15000, 240, 60, 100, 0),
        ('لوحة مفاتيح', 'لوحة مفاتيح ميكانيكية بإضاءة RGB', 20000, 320, 80, 25, 1),
        ('ماوس لاسلكي', 'ماوس لاسلكي دقيق وسريع', 12000, 192, 48, 75, 0)";

    if (mysqli_query($conn, $products_sql)) {
        echo "<div class='success'>✅ تم إضافة المنتجات الافتراضية بنجاح</div>";
    } else {
        echo "<div class='error'>❌ خطأ في إضافة المنتجات: " . mysqli_error($conn) . "</div>";
    }
} else {
    echo "<div class='info'>ℹ️ المنتجات موجودة مسبقاً</div>";
}

// الخطوة 5: التحقق من أن كل شيء يعمل
echo "<h3>🔍 التحقق النهائي</h3>";

$tables_to_check = ['store_settings', 'products', 'orders', 'order_items', 'users'];
foreach ($tables_to_check as $table) {
    $check_sql = "SHOW TABLES LIKE '$table'";
    $result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<div class='success'>✅ جدول $table موجود ويعمل</div>";
    } else {
        echo "<div class='error'>❌ جدول $table غير موجود</div>";
    }
}

echo "<h3>🎉 تم الإعداد الكامل بنجاح!</h3>";
echo "<div class='info'>
    <p>✅ قاعدة البيانات: aljabri_store</p>
    <p>✅ الجداول: store_settings, products, orders, order_items, users</p>
    <p>✅ الإعدادات: إعدادات المتجر الافتراضية</p>
    <p>✅ المستخدمين: admin / admin123</p>
    <p>✅ المنتجات: 5 منتجات افتراضية</p>
</div>";

echo "<div style='margin-top: 20px;'>
    <a href='index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🏪 زيارة المتجر</a>
    <a href='admin/login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>⚙️ لوحة التحكم</a>
</div>";

mysqli_close($conn);
?>