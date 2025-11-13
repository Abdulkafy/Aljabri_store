<?php
// إصلاح سريع لقاعدة البيانات
$conn = mysqli_connect('localhost', 'root', '', 'aljabri_store');

echo "<h3>بدء الإصلاح السريع...</h3>";

// 1. إضافة الأعمدة الأساسية المفقودة
$basic_columns = [
    "price_usd DECIMAL(10,2) NOT NULL DEFAULT 0",
    "main_image VARCHAR(255)",
    "featured BOOLEAN DEFAULT FALSE", 
    "category_id INT DEFAULT 1",
    "stock_quantity INT DEFAULT 0"
];

foreach($basic_columns as $column_def) {
    $column_name = explode(' ', $column_def)[0];
    $check = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE '$column_name'");
    if(mysqli_num_rows($check) == 0) {
        $sql = "ALTER TABLE products ADD COLUMN $column_def";
        if(mysqli_query($conn, $sql)) {
            echo "✅ تم إضافة $column_name<br>";
        } else {
            echo "❌ فشل إضافة $column_name: " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "✅ $column_name موجود<br>";
    }
}

// 2. تحديث بعض المنتجات لتكون مميزة
mysqli_query($conn, "UPDATE products SET featured = 1 WHERE id IN (1,2,4)");
echo "✅ تم تحديث المنتجات المميزة<br>";

echo "<h3>✅ الإصلاح السريع مكتمل</h3>";
echo "<a href='index.php'>الذهاب إلى المتجر</a>";
?>