<?php
include 'includes/config.php';

echo "<h1>فحص مشكلة الصور</h1>";

// فحص المجلدات
$folders = [
    'assets/uploads',
    'assets/images',
    'assets/css'
];

foreach($folders as $folder) {
    if(is_dir($folder)) {
        echo "<p style='color: green;'>✔ مجلد <strong>$folder</strong> موجود</p>";
    } else {
        echo "<p style='color: red;'>✗ مجلد <strong>$folder</strong> غير موجود</p>";
    }
}

// فحص الصور البديلة
$placeholder = "assets/images/placeholder.jpg";
if(file_exists($placeholder)) {
    echo "<p style='color: green;'>✔ صورة البديل موجودة</p>";
    echo "<img src='$placeholder' style='width: 200px; border: 1px solid #ccc;'>";
} else {
    echo "<p style='color: red;'>✗ صورة البديل غير موجودة</p>";
}

// فحص منتجات من قاعدة البيانات
$sql = "SELECT id, name, main_image FROM products LIMIT 3";
$result = mysqli_query($conn, $sql);

echo "<h2>فحص منتجات العينة:</h2>";
if($result && mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $image_path = "assets/uploads/" . $row['main_image'];
        $exists = (!empty($row['main_image']) && file_exists($image_path));
        
        echo "<div style='margin: 20px; padding: 10px; border: 1px solid #ddd;'>";
        echo "<h3>منتج: {$row['name']}</h3>";
        echo "<p>اسم الصورة: " . ($row['main_image'] ?: 'لا توجد صورة') . "</p>";
        echo "<p>حالة الصورة: " . ($exists ? "<span style='color:green;'>موجودة</span>" : "<span style='color:red;'>غير موجودة</span>") . "</p>";
        
        if($exists) {
            echo "<img src='$image_path' style='width: 150px;'>";
        } else {
            echo "<div style='width:150px; height:150px; background:#f0f0f0; display:flex; align-items:center; justify-content:center;'>لا توجد صورة</div>";
        }
        echo "</div>";
    }
} else {
    echo "<p>لا توجد منتجات في قاعدة البيانات</p>";
}
?>