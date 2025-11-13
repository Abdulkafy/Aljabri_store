<?php
// ملف إعادة تعيين كلمة مرور المسؤول
$conn = mysqli_connect('localhost', 'root', '', 'aljabri_store');

if (!$conn) {
    die('فشل الاتصال بقاعدة البيانات: ' . mysqli_connect_error());
}

echo "<h2>إعادة تعيين كلمة مرور المسؤول</h2>";

// كلمة المرور الجديدة
$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// تحديث كلمة المرور في قاعدة البيانات
$sql = "UPDATE admin_users SET password_hash = '$hashed_password' WHERE username = 'admin'";

if (mysqli_query($conn, $sql)) {
    echo "<p style='color: green;'>✅ تم إعادة تعيين كلمة المرور بنجاح!</p>";
    echo "<p><strong>اسم المستخدم:</strong> admin</p>";
    echo "<p><strong>كلمة المرور الجديدة:</strong> $new_password</p>";
} else {
    echo "<p style='color: red;'>❌ فشل في إعادة تعيين كلمة المرور: " . mysqli_error($conn) . "</p>";
}

// التحقق من وجود المسؤول
$sql = "SELECT * FROM admin_users WHERE username = 'admin'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    echo "<p style='color: green;'>✅ حساب المسؤول موجود</p>";
} else {
    echo "<p style='color: red;'>❌ حساب المسؤول غير موجود، سيتم إنشاؤه...</p>";
    
    // إنشاء حساب المسؤول
    $sql = "INSERT INTO admin_users (username, password_hash, full_name) VALUES ('admin', '$hashed_password', 'مدير النظام')";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ تم إنشاء حساب المسؤول بنجاح</p>";
    }
}

mysqli_close($conn);

echo "<br><a href='login.php'>تجربة الدخول الآن</a>";
?>