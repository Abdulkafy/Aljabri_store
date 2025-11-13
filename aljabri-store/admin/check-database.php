<?php
// فحص حالة قاعدة البيانات
$conn = mysqli_connect('localhost', 'root', '', 'aljabri_store');

echo "<h2>فحص قاعدة البيانات</h2>";

if (!$conn) {
    die("<p style='color: red;'>❌ فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error() . "</p>");
}

echo "<p style='color: green;'>✅ الاتصال بقاعدة البيانات ناجح</p>";

// فحص الجداول المهمة
$tables = ['products', 'store_settings', 'admin_users', 'categories', 'orders'];
$all_tables_exist = true;

foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color: green;'>✅ جدول $table موجود</p>";
        
        // عرض عدد السجلات
        $count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM $table");
        $count = mysqli_fetch_assoc($count_result)['count'];
        echo "<p style='margin-right: 20px;'>عدد السجلات: $count</p>";
    } else {
        echo "<p style='color: red;'>❌ جدول $table غير موجود</p>";
        $all_tables_exist = false;
    }
}

// فحص بيانات المسؤول
echo "<h3>فحص بيانات المسؤول:</h3>";
$result = mysqli_query($conn, "SELECT * FROM admin_users");
if ($result && mysqli_num_rows($result) > 0) {
    $admin = mysqli_fetch_assoc($result);
    echo "<p style='color: green;'>✅ حساب المسؤول موجود</p>";
    echo "<p>اسم المستخدم: " . $admin['username'] . "</p>";
    echo "<p>الاسم: " . $admin['full_name'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ لا توجد حسابات مسؤول</p>";
}

mysqli_close($conn);

echo "<br>";
echo "<a href='create-admin.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>إنشاء حساب مسؤول</a>";
echo "<a href='login.php' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>تجربة الدخول</a>";
?>