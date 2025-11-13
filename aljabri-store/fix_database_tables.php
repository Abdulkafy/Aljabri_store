<?php
// fix_database_tables.php - إصلاح هيكل الجداول
include 'includes/config.php';

echo "<h2>🔧 إصلاح هيكل جداول قاعدة البيانات</h2>";

// استعلامات إصلاح الجداول
$queries = [
    // إضافة عمود order_number إذا لم يكن موجوداً
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS order_number VARCHAR(20) AFTER id",
    
    // إضافة الأعمدة المفقودة في جدول orders
    "ALTER TABLE orders 
     ADD COLUMN IF NOT EXISTS customer_email VARCHAR(100) AFTER customer_phone,
     ADD COLUMN IF NOT EXISTS order_notes TEXT AFTER customer_address,
     ADD COLUMN IF NOT EXISTS subtotal DECIMAL(10,2) NOT NULL AFTER order_notes,
     ADD COLUMN IF NOT EXISTS shipping DECIMAL(10,2) NOT NULL AFTER subtotal,
     ADD COLUMN IF NOT EXISTS total DECIMAL(10,2) NOT NULL AFTER shipping,
     ADD COLUMN IF NOT EXISTS currency VARCHAR(10) DEFAULT 'YER' AFTER total,
     ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'pending' AFTER currency,
     ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NOT NULL AFTER status,
     ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
    
    // إنشاء جدول order_items إذا لم يكن موجوداً
    "CREATE TABLE IF NOT EXISTS order_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        product_price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )"
];

foreach($queries as $query) {
    echo "<p>جاري تنفيذ: <code>" . substr($query, 0, 100) . "...</code></p>";
    
    if(mysqli_query($conn, $query)) {
        echo "<p style='color: green;'>✅ تم التنفيذ بنجاح</p>";
    } else {
        echo "<p style='color: red;'>❌ خطأ: " . mysqli_error($conn) . "</p>";
    }
    
    echo "<hr>";
}

// تحديث الطلبات القديمة بأرقام طلبات
echo "<h3>تحديث الطلبات القديمة:</h3>";
$update_orders = "UPDATE orders SET order_number = CONCAT('ORD', DATE_FORMAT(created_at, '%Y%m%d'), LPAD(id, 4, '0')) WHERE order_number IS NULL OR order_number = ''";
if(mysqli_query($conn, $update_orders)) {
    $affected = mysqli_affected_rows($conn);
    echo "<p style='color: green;'>✅ تم تحديث $affected طلب برقم طلب</p>";
} else {
    echo "<p style='color: red;'>❌ خطأ في تحديث الطلبات: " . mysqli_error($conn) . "</p>";
}

// عرض حالة الجداول
echo "<h3>حالة الجداول:</h3>";
$tables = ['orders', 'order_items'];
foreach($tables as $table) {
    $result = mysqli_query($conn, "DESCRIBE $table");
    if($result) {
        echo "<p><strong>جدول $table:</strong></p>";
        echo "<table border='1' style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>الحقل</th><th>النوع</th><th>NULL</th><th>المفتاح</th><th>الافتراضي</th></tr>";
        while($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td>{$row['Field']}</td>
                    <td>{$row['Type']}</td>
                    <td>{$row['Null']}</td>
                    <td>{$row['Key']}</td>
                    <td>{$row['Default']}</td>
                  </tr>";
        }
        echo "</table>";
    }
}

echo "<h3 style='color: green;'>🎉 اكتمل إصلاح قاعدة البيانات!</h3>";
echo "<p><a href='process_order.php'>اختبار عملية الطلب</a> | <a href='index.php'>العودة للرئيسية</a></p>";

mysqli_close($conn);
?>