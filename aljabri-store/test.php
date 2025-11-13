<?php
// اختبار الاتصال بقاعدة البيانات
$conn = mysqli_connect('localhost', 'root', '', 'aljabri_store');

if($conn) {
    echo "✅ الاتصال بقاعدة البيانات ناجح<br>";
    
    // اختبار استعلام بسيط
    $result = mysqli_query($conn, "SHOW TABLES");
    $table_count = mysqli_num_rows($result);
    echo "✅ عدد الجداول في قاعدة البيانات: " . $table_count . "<br>";
    
    if($table_count > 0) {
        echo "✅ الجداول الموجودة:<br>";
        while($row = mysqli_fetch_array($result)) {
            echo "- " . $row[0] . "<br>";
        }
    }
    
} else {
    echo "❌ فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error();
}
?>