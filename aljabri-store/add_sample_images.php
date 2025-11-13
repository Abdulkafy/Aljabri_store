<?php
// add_sample_images.php - إضافة صور افتراضية للمنتجات
include 'includes/config.php';

echo "<h2>إضافة صور افتراضية للمنتجات</h2>";

// جلب جميع المنتجات بدون صور
$sql = "SELECT id, name FROM products WHERE main_image IS NULL OR main_image = ''";
$result = mysqli_query($conn, $sql);

if($result && mysqli_num_rows($result) > 0) {
    echo "<p>عدد المنتجات بدون صور: " . mysqli_num_rows($result) . "</p>";
    
    while($row = mysqli_fetch_assoc($result)) {
        // إنشاء اسم صورة افتراضي
        $image_name = "product_" . $row['id'] . ".jpg";
        
        // تحديث قاعدة البيانات
        $update_sql = "UPDATE products SET main_image = '$image_name' WHERE id = " . $row['id'];
        
        if(mysqli_query($conn, $update_sql)) {
            echo "<p style='color: green;'>✓ تم تحديث المنتج: {$row['name']} - الصورة: $image_name</p>";
        } else {
            echo "<p style='color: red;'>✗ خطأ في تحديث المنتج: {$row['name']}</p>";
        }
    }
    
    echo "<h3 style='color: green;'>✅ تم تحديث جميع المنتجات بنجاح!</h3>";
} else {
    echo "<p>لا توجد منتجات بدون صور</p>";
}

echo "<br><a href='test_images.php'>فحص الصور</a> | <a href='index.php'>الرئيسية</a>";
?>