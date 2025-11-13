<?php
// ملف الإعداد النهائي - مع معالجة قيود المفاتيح الخارجية
$conn = mysqli_connect('localhost', 'root', '', 'aljabri_store');

if(!$conn) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; direction: rtl; }
    .success { color: green; margin: 10px 0; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; }
    .error { color: red; margin: 10px 0; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; }
    .warning { color: #856404; margin: 10px 0; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; }
    .container { background: white; padding: 30px; border-radius: 10px; max-width: 900px; margin: 0 auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .step { margin: 20px 0; padding: 15px; border-right: 4px solid #007bff; background: #f8f9fa; }
</style>";

echo "<div class='container'>";
echo "<h1 style='color: #2C3E50; text-align: center;'>🛠️ الإعداد الشامل لمتجر الجابري ستور</h1>";

// الخطوة 1: إزالة قيود المفاتيح الخارجية أولاً
echo "<div class='step'>";
echo "<h3>الخطوة 1: إعداد قاعدة البيانات</h3>";

// تعطيل فحص المفاتيح الخارجية مؤقتاً
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");

// حذف الجداول بالترتيب الصحيح (الجداول التابعة أولاً)
$tables_to_drop = [
    'order_items',
    'orders', 
    'product_images',
    'products',
    'categories',
    'store_settings',
    'admin_users'
];

foreach($tables_to_drop as $table) {
    $sql = "DROP TABLE IF EXISTS $table";
    if(mysqli_query($conn, $sql)) {
        echo "<div class='success'>✅ تم حذف جدول $table (إذا كان موجوداً)</div>";
    } else {
        echo "<div class='warning'>⚠️ لم يتم حذف جدول $table: " . mysqli_error($conn) . "</div>";
    }
}

// إعادة تفعيل فحص المفاتيح الخارجية
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

echo "</div>";

// الخطوة 2: إنشاء جدول products
echo "<div class='step'>";
echo "<h3>الخطوة 2: إنشاء جدول المنتجات</h3>";

$create_products = "CREATE TABLE products (
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
    echo "<div class='success'>✅ تم إنشاء جدول products بنجاح بجميع الأعمدة المطلوبة</div>";
    
    // عرض هيكل الجدول
    $result = mysqli_query($conn, "DESCRIBE products");
    echo "<div style='background: #e9ecef; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>هيكل الجدول:</strong><br>";
    while($row = mysqli_fetch_assoc($result)) {
        echo "• {$row['Field']} ({$row['Type']})<br>";
    }
    echo "</div>";
} else {
    echo "<div class='error'>❌ فشل في إنشاء جدول products: " . mysqli_error($conn) . "</div>";
    mysqli_close($conn);
    exit;
}
echo "</div>";

// الخطوة 3: إضافة المنتجات النموذجية
echo "<div class='step'>";
echo "<h3>الخطوة 3: إضافة المنتجات النموذجية</h3>";

$sample_products = [
    "('هاتف سامسونج جالاكسي', 'هاتف ذكي بشاشة 6.7 بوصة وكاميرا رباعية، أداء قوي وتصميم أنيق', 350000, 1400, 373, 'samsung-galaxy.jpg', 1, 1, 15)",
    "('لابتوب ديل للأعمال', 'لابتوب مثالي للأعمال بمعالج Core i7 وذاكرة 16GB، شاشة 15 بوصة', 800000, 3200, 853, 'dell-laptop.jpg', 1, 1, 8)",
    "('سماعات ابل اللاسلكية', 'سماعات لاسلكية بتقنية إلغاء الضوضاء النشط، صوت نقى وجودة عالية', 180000, 720, 192, 'airpods.jpg', 1, 1, 25)",
    "('ساعة ابل الذكية', 'ساعة ذكية بميزات متقدمة لتتبع الصحة واللياقة البدنية، مقاومة للماء', 250000, 1000, 267, 'apple-watch.jpg', 0, 1, 12)",
    "('كاميرا كانون الاحترافية', 'كاميرا احترافية بدقة 24 ميجابكسل، مثالية للتصوير الفوتوغرافي', 450000, 1800, 480, 'canon-camera.jpg', 0, 1, 6)",
    "('جهاز تابلت هواوي', 'تابلت بشاشة 10 بوصة، أداء سريع وبطارية طويلة الأمد', 200000, 800, 213, 'huawei-tablet.jpg', 0, 1, 10)",
    "('قلم رقمي للرسم', 'قلم رقمي دقيق للرسم والكتابة على الأجهزة اللوحية', 50000, 200, 53, 'digital-pen.jpg', 0, 1, 30)",
    "('شاحن متنقل سريع', 'شاحن متنقل سعة 10000 مللي أمبير بشحن سريع', 40000, 160, 43, 'power-bank.jpg', 0, 1, 20)"
];

$success_count = 0;
foreach($sample_products as $product) {
    $sql = "INSERT INTO products (name, description, price_yer, price_sar, price_usd, main_image, featured, category_id, stock_quantity) VALUES $product";
    if(mysqli_query($conn, $sql)) {
        $success_count++;
    }
}

echo "<div class='success'>✅ تم إضافة $success_count منتج نموذجي بنجاح</div>";

// عرض بعض المنتجات المضافة
$result = mysqli_query($conn, "SELECT name, price_yer, price_sar, price_usd FROM products LIMIT 3");
echo "<div style='background: #e9ecef; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>عينة من المنتجات المضافة:</strong><br>";
while($row = mysqli_fetch_assoc($result)) {
    echo "• {$row['name']} - {$row['price_yer']} ريال يمني - {$row['price_sar']} ريال سعودي - \${$row['price_usd']}<br>";
}
echo "</div>";
echo "</div>";

// الخطوة 4: إنشاء وإعداد الجداول الأخرى
echo "<div class='step'>";
echo "<h3>الخطوة 4: إنشاء الجداول المساعدة</h3>";

// جدول store_settings
$create_settings = "CREATE TABLE store_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(255) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if(mysqli_query($conn, $create_settings)) {
    echo "<div class='success'>✅ تم إنشاء جدول store_settings</div>";
    
    // إضافة الإعدادات الافتراضية
    $default_settings = [
        ['store_name', 'الجابري ستور'],
        ['primary_color', '#FF6B35'],
        ['secondary_color', '#2C3E50'],
        ['announcement_text', '🔥 عروض خاصة - تخفيضات تصل إلى 50% 🔥, 🚚 توصيل مجاني لطلبات فوق 50,000 ريال, ⭐ منتجات أصلية بضمان المتجر'],
        ['store_address', 'اليمن - صنعاء - الصياح - امم محطة براش'],
        ['store_phone', '+967782090454'],
        ['store_whatsapp', '+967782090454'],
        ['welcome_message', 'مرحباً بكم في متجر الجابري ستور - أفضل المنتجات بأفضل الأسعار مع خدمة التوصيل لجميع أنحاء اليمن']
    ];
    
    foreach($default_settings as $setting) {
        mysqli_query($conn, "INSERT INTO store_settings (setting_key, setting_value) VALUES ('$setting[0]', '$setting[1]')");
    }
    echo "<div class='success'>✅ تم إضافة الإعدادات الافتراضية</div>";
}

// جدول admin_users
$create_admin = "CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if(mysqli_query($conn, $create_admin)) {
    echo "<div class='success'>✅ تم إنشاء جدول admin_users</div>";
    
    // إضافة مسؤول النظام
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO admin_users (username, password_hash, full_name) VALUES ('admin', '$admin_password', 'مدير النظام')");
    echo "<div class='success'>✅ تم إضافة مسؤول النظام</div>";
}

// جدول categories
$create_categories = "CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if(mysqli_query($conn, $create_categories)) {
    echo "<div class='success'>✅ تم إنشاء جدول categories</div>";
    
    // إضافة الفئات
    $categories = [
        ['إلكترونيات', 'الأجهزة الإلكترونية والكهربائية'],
        ['ملابس', 'ملابس رجالية ونسائية وأطفال'],
        ['منتجات منزلية', 'أدوات ومنتجات للاستخدام المنزلي'],
        ['هواتف وأجهزة لوحية', 'الهواتف الذكية والأجهزة اللوحية وملحقاتها'],
        ['أجهزة الكمبيوتر', 'لابتوبات، أجهزة مكتبية، وملحقاتها']
    ];
    
    foreach($categories as $category) {
        mysqli_query($conn, "INSERT INTO categories (name, description) VALUES ('$category[0]', '$category[1]')");
    }
    echo "<div class='success'>✅ تم إضافة الفئات</div>";
}

// الجداول الأخرى (بدون مفاتيح خارجية في البداية)
$other_tables = [
    'product_images' => "CREATE TABLE product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT,
        image_path VARCHAR(255),
        is_main BOOLEAN DEFAULT FALSE
    )",
    
    'orders' => "CREATE TABLE orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(20) NOT NULL,
        customer_address TEXT NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    'order_items' => "CREATE TABLE order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_id INT,
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL
    )"
];

foreach($other_tables as $table_name => $sql) {
    if(mysqli_query($conn, $sql)) {
        echo "<div class='success'>✅ تم إنشاء جدول $table_name</div>";
    } else {
        echo "<div class='warning'>⚠️ لم يتم إنشاء جدول $table_name: " . mysqli_error($conn) . "</div>";
    }
}

echo "</div>";

// الخطوة 5: التحقق النهائي
echo "<div class='step'>";
echo "<h3>الخطوة 5: التحقق النهائي</h3>";

$tables_to_check = ['products', 'store_settings', 'admin_users', 'categories'];
foreach($tables_to_check as $table) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM $table");
    if($result) {
        $count = mysqli_fetch_assoc($result)['count'];
        echo "<div class='success'>✅ جدول $table: $count سجل</div>";
    } else {
        echo "<div class='error'>❌ خطأ في جدول $table: " . mysqli_error($conn) . "</div>";
    }
}

echo "</div>";

// رسالة النجاح النهائية
echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; text-align: center; margin: 20px 0;'>";
echo "<h2 style='color: #155724;'>🎉 تم الانتهاء من الإعداد بنجاح!</h2>";
echo "<p style='font-size: 18px;'>تم إنشاء متجر الجابري ستور بالكامل وجميع البيانات المطلوبة</p>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3 style='color: #856404;'>🔗 الروابط المهمة:</h3>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 15px 0;'>";
echo "<a href='index.php' style='display: block; padding: 15px; background: #FF6B35; color: white; text-decoration: none; border-radius: 5px; text-align: center; font-weight: bold;'>🏠 زيارة المتجر الرئيسي</a>";
echo "<a href='products.php' style='display: block; padding: 15px; background: #2C3E50; color: white; text-decoration: none; border-radius: 5px; text-align: center; font-weight: bold;'>🛍️ عرض جميع المنتجات</a>";
echo "<a href='admin/login.php' style='display: block; padding: 15px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; text-align: center; font-weight: bold;'>⚙️ الدخول إلى لوحة التحكم</a>";
echo "</div>";
echo "</div>";

echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 10px;'>";
echo "<h3 style='color: #0c5460;'>🔐 بيانات الدخول إلى لوحة التحكم:</h3>";
echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<p><strong>رابط الدخول:</strong> <a href='admin/login.php'>admin/login.php</a></p>";
echo "<p><strong>اسم المستخدم:</strong> admin</p>";
echo "<p><strong>كلمة المرور:</strong> admin123</p>";
echo "</div>";
echo "</div>";

echo "</div>"; // إغلاق container

mysqli_close($conn);
?>