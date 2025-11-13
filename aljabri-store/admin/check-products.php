<?php
// فحص المنتجات في قاعدة البيانات
$conn = mysqli_connect('localhost', 'root', '', 'aljabri_store');

echo "<h2>فحص المنتجات في قاعدة البيانات</h2>";

if (!$conn) {
    die("فشل الاتصال بقاعدة البيانات");
}

// فحص جدول products
$result = mysqli_query($conn, "SELECT * FROM products");
if($result && mysqli_num_rows($result) > 0){
    echo "<p style='color: green;'>✅ تم العثور على " . mysqli_num_rows($result) . " منتج</p>";
    
    echo "<table border='1' style='width: 100%; border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th>ID</th><th>الاسم</th><th>السعر (ريال)</th><th>المخزون</th><th>مميز</th><th>الفئة</th></tr>";
    
    while($row = mysqli_fetch_assoc($result)){
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td><strong>{$row['name']}</strong></td>";
        echo "<td>" . number_format($row['price_yer']) . "</td>";
        echo "<td>{$row['stock_quantity']}</td>";
        echo "<td>" . ($row['featured'] ? '✅ نعم' : '❌ لا') . "</td>";
        echo "<td>{$row['category_id']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ لا توجد منتجات في قاعدة البيانات</p>";
    echo "<p><a href='product-edit.php'>انقر هنا لإضافة منتج جديد</a></p>";
}

// فحص جدول categories
echo "<h3>الفئات المتاحة:</h3>";
$result = mysqli_query($conn, "SELECT * FROM categories");
if($result && mysqli_num_rows($result) > 0){
    echo "<ul>";
    while($row = mysqli_fetch_assoc($result)){
        echo "<li><strong>{$row['name']}</strong> - {$row['description']}</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ لا توجد فئات</p>";
}

mysqli_close($conn);

echo "<br>";
echo "<a href='fix-database.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>إصلاح قاعدة البيانات</a>";
echo "<a href='product-edit.php' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>إضافة منتج جديد</a>";
?>