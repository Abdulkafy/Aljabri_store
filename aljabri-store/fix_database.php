<?php
// إعداد قاعدة البيانات
$host = "localhost";
$username = "root";
$password = "";
$database = "aljabri_store";

// الاتصال بقاعدة البيانات
$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

echo "<h3>🔧 إصلاح قاعدة البيانات</h3>";

// إسقاط الجداول القديمة وإعادة إنشائها بشكل صحيح
$tables_to_drop = ['store_settings', 'products', 'orders', 'order_items', 'users'];

foreach ($tables_to_drop as $table) {
    $drop_sql = "DROP TABLE IF EXISTS $table";
    if (mysqli_query($conn, $drop_sql)) {
        echo "✅ تم حذف الجدول $table<br>";
    }
}

// إنشاء الجداول بشكل صحيح
$tables = [
    // جدول إعدادات المتجر
    "CREATE TABLE store_settings (
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
    "CREATE TABLE products (
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
    "CREATE TABLE orders (
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
    "CREATE TABLE order_items (
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
    "CREATE TABLE users (
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
        echo "✅ تم إنشاء الجدول بنجاح<br>";
    } else {
        echo "❌ خطأ في إنشاء الجدول: " . mysqli_error($conn) . "<br>";
    }
}

// إدخال إعدادات افتراضية للمتجر
$settings_sql = "INSERT INTO store_settings (store_name, primary_color, secondary_color, announcement_text, welcome_message, store_address, store_phone, store_whatsapp) 
                 VALUES ('متجر الجابري', '#3498db', '#2c3e50', 'مرحباً بكم في متجرنا، شحن مجاني للطلبات فوق 50000 ريال', 'أهلاً وسهلاً بكم في متجر الجابري لأفضل المنتجات', 'صنعاء، اليمن', '+967123456789', '+967123456789')";

if (mysqli_query($conn, $settings_sql)) {
    echo "✅ تم إضافة الإعدادات الافتراضية بنجاح<br>";
} else {
    echo "❌ خطأ في إضافة الإعدادات: " . mysqli_error($conn) . "<br>";
}

// إدخال مستخدم مسؤول افتراضي
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$admin_sql = "INSERT INTO users (username, password, name, role) 
              VALUES ('admin', '$admin_password', 'مدير النظام', 'admin')";

if (mysqli_query($conn, $admin_sql)) {
    echo "✅ تم إنشاء المستخدم المسؤول بنجاح<br>";
    echo "🔑 اسم المستخدم: admin<br>";
    echo "🔑 كلمة المرور: admin123<br>";
} else {
    echo "❌ خطأ في إنشاء المستخدم: " . mysqli_error($conn) . "<br>";
}

// إضافة بعض المنتجات الافتراضية
$products_sql = "INSERT INTO products (name, description, price_yer, price_sar, price_usd, stock_quantity, featured) VALUES
    ('ساعة ذكية', 'ساعة ذكية متطورة مع شاشة تعمل باللمس', 25000, 400, 100, 50, 1),
    ('هاتف محمول', 'هاتف ذكي بشاشة 6.5 بوصة وكاميرا مزدوجة', 45000, 720, 180, 30, 1),
    ('سماعات لاسلكية', 'سماعات بلوتوث عالية الجودة', 15000, 240, 60, 100, 0),
    ('لوحة مفاتيح', 'لوحة مفاتيح ميكانيكية بإضاءة RGB', 20000, 320, 80, 25, 1),
    ('ماوس لاسلكي', 'ماوس لاسلكي دقيق وسريع', 12000, 192, 48, 75, 0)";

if (mysqli_query($conn, $products_sql)) {
    echo "✅ تم إضافة المنتجات الافتراضية بنجاح<br>";
} else {
    echo "❌ خطأ في إضافة المنتجات: " . mysqli_error($conn) . "<br>";
}

echo "<h3>🎉 تم إصلاح قاعدة البيانات بنجاح!</h3>";
echo "<p>يمكنك الآن <a href='index.php'>زيارة المتجر</a></p>";

mysqli_close($conn);
?>