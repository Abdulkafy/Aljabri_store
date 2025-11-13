<?php
// إنشاء حساب مسؤول جديد
$conn = mysqli_connect('localhost', 'root', '', 'aljabri_store');

if (!$conn) {
    die('فشل الاتصال بقاعدة البيانات: ' . mysqli_connect_error());
}

echo "<h2>إنشاء حساب مسؤول جديد</h2>";

// بيانات المسؤول
$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$full_name = 'مدير النظام';

// التحقق أولاً إذا كان الجدول موجوداً
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'admin_users'");

if (mysqli_num_rows($table_check) == 0) {
    // إنشاء جدول admin_users إذا لم يكن موجوداً
    $create_table = "CREATE TABLE admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (mysqli_query($conn, $create_table)) {
        echo "<p style='color: green;'>✅ تم إنشاء جدول admin_users</p>";
    } else {
        echo "<p style='color: red;'>❌ فشل في إنشاء الجدول: " . mysqli_error($conn) . "</p>";
    }
}

// التحقق إذا كان المسؤول موجوداً
$check_sql = "SELECT * FROM admin_users WHERE username = '$username'";
$result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($result) > 0) {
    // تحديث كلمة المرور إذا كان المسؤول موجوداً
    $update_sql = "UPDATE admin_users SET password_hash = '$hashed_password' WHERE username = '$username'";
    if (mysqli_query($conn, $update_sql)) {
        echo "<p style='color: green;'>✅ تم تحديث كلمة مرور المسؤول</p>";
    }
} else {
    // إنشاء مسؤول جديد
    $insert_sql = "INSERT INTO admin_users (username, password_hash, full_name) VALUES ('$username', '$hashed_password', '$full_name')";
    if (mysqli_query($conn, $insert_sql)) {
        echo "<p style='color: green;'>✅ تم إنشاء حساب المسؤول بنجاح</p>";
    } else {
        echo "<p style='color: red;'>❌ فشل في إنشاء الحساب: " . mysqli_error($conn) . "</p>";
    }
}

echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>بيانات الدخول:</h3>";
echo "<p><strong>اسم المستخدم:</strong> $username</p>";
echo "<p><strong>كلمة المرور:</strong> $password</p>";
echo "</div>";

mysqli_close($conn);

echo "<br><a href='login.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>تجربة الدخول</a>";
?>