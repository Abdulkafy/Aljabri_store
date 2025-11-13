<?php
// clear_orders.php - تفريغ الطلبات (لأغراض التطوير فقط)
include 'includes/config.php';

echo "<h2>🗑️ تفريغ جداول الطلبات</h2>";

if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    // تفريغ الجداول
    mysqli_query($conn, "DELETE FROM order_items");
    mysqli_query($conn, "DELETE FROM orders");
    
    echo "<p style='color: green;'>✅ تم تفريغ جميع الطلبات والعناصر</p>";
    echo "<a href='update_tables.php'>العودة لصفحة التحديث</a>";
} else {
    echo "<p style='color: red;'>⚠️ هل أنت متأكد من أنك تريد حذف جميع الطلبات؟</p>";
    echo "<p>سيتم حذف جميع بيانات الطلبات ولا يمكن استرجاعها!</p>";
    echo "<a href='clear_orders.php?confirm=yes' style='color: red; font-weight: bold;'>نعم، احذف جميع الطلبات</a>";
    echo " | ";
    echo "<a href='update_tables.php'>لا، العودة</a>";
}

mysqli_close($conn);
?>