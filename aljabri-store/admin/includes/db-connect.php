<?php
// ملف الاتصال بقاعدة البيانات الخاص بلوحة التحكم
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        // محاولة الاتصال بقاعدة البيانات
        $conn = mysqli_connect('localhost', 'root', '', 'aljabri_store');
        
        if (!$conn) {
            die('فشل الاتصال بقاعدة البيانات: ' . mysqli_connect_error());
        }
        
        mysqli_set_charset($conn, "utf8");
    }
    
    return $conn;
}

// دالة لتنفيذ الاستعلامات بشكل آمن
function db_query($sql) {
    $conn = getDBConnection();
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        die('خطأ في الاستعلام: ' . mysqli_error($conn));
    }
    
    return $result;
}

// دالة لجلب إعدادات المتجر
function get_admin_settings() {
    $conn = getDBConnection();
    $settings = [];
    
    // التحقق من وجود جدول الإعدادات
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'store_settings'");
    if (mysqli_num_rows($table_check) > 0) {
        $result = mysqli_query($conn, "SELECT setting_key, setting_value FROM store_settings");
        while ($row = mysqli_fetch_assoc($result)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } else {
        // إعدادات افتراضية
        $settings = [
            'store_name' => 'الجابري ستور',
            'primary_color' => '#FF6B35',
            'secondary_color' => '#2C3E50',
            'announcement_text' => '🔥 عروض خاصة - تخفيضات تصل إلى 50% 🔥',
            'store_address' => 'اليمن - صنعاء - الصياح - امم محطة براش',
            'store_phone' => '+967782090454',
            'store_whatsapp' => '+967782090454',
            'welcome_message' => 'مرحباً بكم في متجر الجابري ستور'
        ];
    }
    
    return $settings;
}
?>