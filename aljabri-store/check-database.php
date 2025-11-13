<?php
$conn = mysqli_connect('localhost', 'root', '', 'aljabri_store');

echo "<h2>فحص حالة قاعدة البيانات</h2>";

if($conn) {
    echo "✅ الاتصال بنجاح<br>";
    
    // فحص الجداول
    $result = mysqli_query($conn, "SHOW TABLES");
    echo "<h3>الجداول الموجودة:</h3>";
    while($row = mysqli_fetch_array($result)) {
        echo "📊 " . $row[0] . "<br>";
        
        // فحص أعمدة كل جدول
        $columns = mysqli_query($conn, "SHOW COLUMNS FROM " . $row[0]);
        while($col = mysqli_fetch_assoc($columns)) {
            echo "&nbsp;&nbsp;📝 " . $col['Field'] . " (" . $col['Type'] . ")<br>";
        }
        echo "<br>";
    }
    
    // فحص المنتجات
    $products = mysqli_query($conn, "SELECT COUNT(*) as count FROM products");
    $p_count = mysqli_fetch_assoc($products)['count'];
    echo "🛍️ عدد المنتجات: " . $p_count . "<br>";
    
} else {
    echo "❌ فشل الاتصال: " . mysqli_connect_error();
}
?>