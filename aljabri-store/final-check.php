<?php
$conn = mysqli_connect('localhost', 'root', '', 'aljabri_store');
echo "<h3>التحقق النهائي من قاعدة البيانات</h3>";

// فحص الجداول والأعمدة
$tables = ['products', 'store_settings', 'admin_users'];
foreach($tables as $table) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM $table");
    echo "<h4>جدول: $table</h4>";
    while($col = mysqli_fetch_assoc($result)) {
        echo "• {$col['Field']} ({$col['Type']})<br>";
    }
    echo "<br>";
}

// فحص البيانات
$products = mysqli_query($conn, "SELECT COUNT(*) as count FROM products");
echo "عدد المنتجات: " . mysqli_fetch_assoc($products)['count'] . "<br>";

$settings = mysqli_query($conn, "SELECT COUNT(*) as count FROM store_settings"); 
echo "عدد الإعدادات: " . mysqli_fetch_assoc($settings)['count'] . "<br>";

echo "<h3 style='color: green;'>✅ التحقق مكتمل</h3>";
?>