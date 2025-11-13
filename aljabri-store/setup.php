<?php
// ملف إعداد قاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aljabri_store";

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// إنشاء قاعدة البيانات
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "✅ تم إنشاء قاعدة البيانات بنجاح<br>";
} else {
    echo "❌ خطأ في إنشاء قاعدة البيانات: " . $conn->error . "<br>";
}

// استخدام قاعدة البيانات
$conn->select_db($dbname);

// إنشاء الجداول
$tables = [
    "store_settings" => "CREATE TABLE IF NOT EXISTS store_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(255) UNIQUE NOT NULL,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    "products" => "CREATE TABLE IF NOT EXISTS products (
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
    )",
    
    "product_images" => "CREATE TABLE IF NOT EXISTS product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT,
        image_path VARCHAR(255),
        is_main BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )",
    
    "orders" => "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(20) NOT NULL,
        customer_address TEXT NOT NULL,
        payment_method ENUM('كريمي جوال', 'جيب', 'ون كاش', 'فلوسك', 'جوالي', 'كاش') NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "order_items" => "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_id INT,
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id)
    )",
    
    "admin_users" => "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "categories" => "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach($tables as $table_name => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "✅ تم إنشاء جدول $table_name بنجاح<br>";
    } else {
        echo "❌ خطأ في إنشاء جدول $table_name: " . $conn->error . "<br>";
    }
}

// إضافة البيانات الافتراضية
$default_data = [
    "store_settings" => "INSERT IGNORE INTO store_settings (setting_key, setting_value) VALUES
        ('store_name', 'الجابري ستور'),
        ('primary_color', '#FF6B35'),
        ('secondary_color', '#2C3E50'),
        ('announcement_text', '🔥 عروض خاصة - تخفيضات تصل إلى 50% 🔥'),
        ('store_address', 'اليمن - صنعاء - الصياح - امم محطة براش'),
        ('store_phone', '+967782090454'),
        ('store_whatsapp', '+967782090454'),
        ('welcome_message', 'مرحباً بكم في متجر الجابري ستور - أفضل المنتجات بأفضل الأسعار')",
    
    "admin_users" => "INSERT IGNORE INTO admin_users (username, password_hash, full_name) VALUES
        ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدير النظام')",
    
    "categories" => "INSERT IGNORE INTO categories (name, description) VALUES
        ('إلكترونيات', 'الأجهزة الإلكترونية والكهربائية'),
        ('ملابس', 'ملابس رجالية ونسائية وأطفال'),
        ('منتجات منزلية', 'أدوات ومنتجات للاستخدام المنزلي')",
    
    "products" => "INSERT IGNORE INTO products (name, description, price_yer, price_sar, price_usd, featured, stock_quantity, category_id) VALUES
        ('هاتف ذكي جديد', 'هاتف ذكي بمواصفات عالية وكاميرا متميزة', 150000, 600, 160, 1, 10, 1),
        ('ساعة ذكية', 'ساعة ذكية بتقنيات متطورة وتتبع للصحة', 80000, 320, 85, 1, 15, 1),
        ('قلم رقمي', 'قلم رقمي للرسم والكتابة على الأجهزة اللوحية', 45000, 180, 48, 0, 20, 1)"
];

foreach($default_data as $table => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "✅ تم إضافة البيانات الافتراضية لجدول $table بنجاح<br>";
    } else {
        echo "❌ خطأ في إضافة البيانات لجدول $table: " . $conn->error . "<br>";
    }
}

echo "<h2>✅ تم الانتهاء من إعداد قاعدة البيانات بنجاح!</h2>";
echo "<a href='index.php'>الذهاب إلى المتجر</a> | <a href='admin/login.php'>الدخول إلى لوحة التحكم</a>";

$conn->close();
?>