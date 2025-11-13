<?php
// إعدادات قاعدة البيانات
$host = "localhost";
$username = "root";
$password = "";
$database = "aljabri_store";

// الاتصال بقاعدة البيانات
$conn = mysqli_connect($host, $username, $password, $database);

// إذا فشل الاتصال، عرض رسالة خطأ
if (!$conn) {
    die("<div style='text-align: center; padding: 50px; font-family: Arial;'>
        <h2>❌ خطأ في الاتصال بقاعدة البيانات</h2>
        <p>تعذر الاتصال بقاعدة البيانات. يرجى تشغيل ملف الإصلاح أولاً:</p>
        <p><a href='fix_tables.php' style='background: #3498db; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 18px;'>تشغيل إصلاح الجداول</a></p>
    </div>");
}

// تعيين encoding
mysqli_set_charset($conn, "utf8mb4");

// دالة جلب إعدادات المتجر - مع التحقق من عدم وجودها مسبقاً
if (!function_exists('getStoreSettings')) {
    function getStoreSettings($connection) {
        $settings = [];
        
        // التحقق من وجود جدول store_settings أولاً
        $check_table = "SHOW TABLES LIKE 'store_settings'";
        $table_result = mysqli_query($connection, $check_table);
        
        if ($table_result && mysqli_num_rows($table_result) > 0) {
            // إذا كان الجدول موجوداً، جلب الإعدادات
            $sql = "SELECT * FROM store_settings LIMIT 1";
            $result = mysqli_query($connection, $sql);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $settings = mysqli_fetch_assoc($result);
            }
        }
        
        // دمج مع الإعدادات الافتراضية
        $default_settings = [
            'store_name' => 'متجر الجابري',
            'primary_color' => '#3498db',
            'secondary_color' => '#2c3e50',
            'announcement_text' => 'مرحباً بكم في متجرنا، شحن مجاني للطلبات فوق 50000 ريال',
            'welcome_message' => 'أهلاً وسهلاً بكم في متجر الجابري لأفضل المنتجات',
            'store_address' => 'صنعاء، اليمن',
            'store_phone' => '+967123456789',
            'store_whatsapp' => '+967123456789',
            'store_logo' => '',
            'store_email' => '',
            'product_image_width' => '300',
            'product_image_height' => '300',
            'image_quality' => '85'
        ];
        
        return array_merge($default_settings, $settings);
    }
}

// دالة إنشاء رقم طلب فريد - مع التحقق من عدم وجودها مسبقاً
if (!function_exists('generateOrderNumber')) {
    function generateOrderNumber($connection) {
        $prefix = "JAB";
        $year = date('Y');
        $month = date('m');
        
        // التحقق من وجود جدول orders أولاً
        $check_table = "SHOW TABLES LIKE 'orders'";
        $table_result = mysqli_query($connection, $check_table);
        
        if (!$table_result || mysqli_num_rows($table_result) == 0) {
            return $prefix . $year . $month . '0001';
        }
        
        // الحصول على آخر رقم طلب لهذا الشهر
        $sql = "SELECT order_number FROM orders WHERE order_number LIKE ? ORDER BY id DESC LIMIT 1";
        $search_pattern = $prefix . $year . $month . '%';
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, 's', $search_pattern);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $last_order = mysqli_fetch_assoc($result);
        
        if ($last_order) {
            // استخراج الرقم التسلسلي من آخر طلب
            $last_number = intval(substr($last_order['order_number'], -4));
            $new_number = $last_number + 1;
        } else {
            $new_number = 1;
        }
        
        // تنسيق الرقم: JAB2024120001
        return $prefix . $year . $month . str_pad($new_number, 4, '0', STR_PAD_LEFT);
    }
}

// دالة التحقق من تسجيل الدخول - مع التحقق من عدم وجودها مسبقاً
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']) || isset($_SESSION['admin_logged_in']);
    }
}

// دالة الحصول على معلومات المستخدم - مع التحقق من عدم وجودها مسبقاً
if (!function_exists('getUserInfo')) {
    function getUserInfo($connection, $user_id) {
        // التحقق من وجود جدول users أولاً
        $check_table = "SHOW TABLES LIKE 'users'";
        $table_result = mysqli_query($connection, $check_table);
        
        if (!$table_result || mysqli_num_rows($table_result) == 0) {
            return null;
        }
        
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
}

// دالة تنظيف البيانات - مع التحقق من عدم وجودها مسبقاً
if (!function_exists('cleanData')) {
    function cleanData($data) {
        if (is_array($data)) {
            return array_map('cleanData', $data);
        }
        return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

// دالة لإنشاء جدول الإعدادات إذا لم يكن موجوداً - مع التحقق من عدم وجودها مسبقاً
if (!function_exists('createSettingsTable')) {
    function createSettingsTable($connection) {
        $sql = "CREATE TABLE IF NOT EXISTS store_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            store_name VARCHAR(255) DEFAULT 'متجر الجابري',
            primary_color VARCHAR(7) DEFAULT '#3498db',
            secondary_color VARCHAR(7) DEFAULT '#2c3e50',
            announcement_text TEXT,
            welcome_message TEXT,
            store_address TEXT,
            store_phone VARCHAR(20),
            store_whatsapp VARCHAR(20),
            store_email VARCHAR(255),
            store_logo VARCHAR(255),
            product_image_width INT DEFAULT 300,
            product_image_height INT DEFAULT 300,
            image_quality INT DEFAULT 85,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        mysqli_query($connection, $sql);
        
        // التحقق مما إذا كان هناك سجلات في الجدول
        $check_sql = "SELECT COUNT(*) as count FROM store_settings";
        $result = mysqli_query($connection, $check_sql);
        $row = mysqli_fetch_assoc($result);
        
        if ($row['count'] == 0) {
            // إدخال سجل افتراضي
            $insert_sql = "INSERT INTO store_settings (store_name) VALUES ('متجر الجابري')";
            mysqli_query($connection, $insert_sql);
        }
    }
}

// استدعاء إنشاء الجداول عند تحميل config
createSettingsTable($conn);
?>