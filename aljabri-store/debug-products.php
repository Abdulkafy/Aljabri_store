<?php
include 'includes/config.php';

echo "<h2>فحص المنتجات في قاعدة البيانات</h2>";

$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);

if($result && mysqli_num_rows($result) > 0){
    echo "<p style='color: green;'>✅ تم العثور على " . mysqli_num_rows($result) . " منتج</p>";
    
    echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>الاسم</th><th>السعر (ريال يمني)</th><th>المخزون</th><th>مميز</th></tr>";
    
    while($row = mysqli_fetch_assoc($result)){
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>" . number_format($row['price_yer']) . "</td>";
        echo "<td>{$row['stock_quantity']}</td>";
        echo "<td>" . ($row['featured'] ? 'نعم' : 'لا') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ لا توجد منتجات في قاعدة البيانات</p>";
}

echo "<br><a href='index.php'>العودة إلى المتجر</a>";
?>