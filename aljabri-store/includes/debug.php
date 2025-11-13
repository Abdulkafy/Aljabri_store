<?php
$conn = mysqli_connect('localhost', 'root', '', 'aljabri_store');

if($conn) {
    echo "✅ الاتصال بنجاح<br>";
    
    // التحقق من الجداول
    $result = mysqli_query($conn, "SHOW TABLES");
    $tables = [];
    while($row = mysqli_fetch_array($result)) {
        $tables[] = $row[0];
    }
    
    echo "الجداول الموجودة: " . implode(', ', $tables) . "<br>";
    
    // التحقق من بيانات store_settings
    if(in_array('store_settings', $tables)) {
        $result = mysqli_query($conn, "SELECT * FROM store_settings");
        echo "عدد السجلات في store_settings: " . mysqli_num_rows($result) . "<br>";
    }
    
} else {
    echo "❌ فشل الاتصال: " . mysqli_connect_error();
}
?>