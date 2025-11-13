<?php
// التحقق من المصادقة في لوحة التحكم
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    header('Location: login.php');
    exit;
}

// تضمين ملف الإعدادات
$config_path = __DIR__ . '/../../includes/config.php';
if (file_exists($config_path)) {
    include $config_path;
} else {
    die('خطأ: لم يتم العثور على ملف الإعدادات');
}
?>