<?php
// fix_all_tables.php - إصلاح جميع جداول النظام
include 'includes/config.php';

echo "<h2>🔧 إصلاح شامل لجداول النظام</h2>";

// استعلامات إصلاح الجداول
$queries = [
    // إصلاح جدول orders
    "CREATE TABLE IF NOT EXISTS orders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        customer_name VARCHAR(100) NOT NULL,
        customer_phone VARCHAR(20) NOT NULL,
        customer_email VARCHAR(100),
        customer_address TEXT NOT NULL,
        order_notes TEXT,
        subtotal DECIMAL(10,2) NOT NULL,
        shipping DECIMAL(10,2) NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )" => "إنشاء/إصلاح جدول الطلبات",

    // إصلاح جدول order_items
    "CREATE TABLE IF NOT EXISTS order_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        product_price DECIMAL(10,2) NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )" => "إنشاء/إصلاح جدول عناصر الطلبات",

    // إضافة الأعمدة المفقودة لجدول orders
    "ALTER TABLE orders 
     ADD COLUMN IF NOT EXISTS customer_email VARCHAR(100)" => "إضافة بريد العميل",
    
    "ALTER TABLE orders 
     ADD COLUMN IF NOT EXISTS order_notes TEXT" => "إضافة ملاحظات الطلب",
    
    "ALTER TABLE orders 
     ADD COLUMN IF NOT EXISTS subtotal DECIMAL(10,2) NOT NULL DEFAULT 0" => "إضافة المجموع الجزئي",
    
    "ALTER TABLE orders 
     ADD COLUMN IF NOT EXISTS shipping DECIMAL(10,2) NOT NULL DEFAULT 0" => "إضافة رسوم الشحن",
    
    "ALTER TABLE orders 
     ADD COLUMN IF NOT EXISTS total DECIMAL(10,2) NOT NULL DEFAULT 0" => "إضافة المجموع الكلي",
    
    "ALTER TABLE orders 
     ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NOT NULL DEFAULT 'cash'" => "إضافة طريقة الدفع",
    
    "ALTER TABLE orders 
     ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'pending'" => "إضافة حالة الطلب",
    
    "ALTER TABLE orders 
     ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP" => "إضافة تاريخ الإنشاء",

    // إضافة الأعمدة المفقودة لجدول order_items
    "ALTER TABLE order_items 
     ADD COLUMN IF NOT EXISTS product_price DECIMAL(10,2) NOT NULL DEFAULT 0" => "إضافة سعر المنتج",
    
    "ALTER TABLE order_items 
     ADD COLUMN IF NOT EXISTS total_price DECIMAL(10,2) NOT NULL DEFAULT 0" => "إضافة السعر الإجمالي",
    
    "ALTER TABLE order_items 
     ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP" => "إضافة تاريخ الإنشاء"
];

foreach($queries as $query => $description) {
    echo "<p><strong>$description:</strong></p>";
    echo "<p><code>" . htmlspecialchars($query) . "</code></p>";
    
    if(mysqli_query($conn, $query)) {
        echo "<p style='color: green;'>✅ تم التنفيذ بنجاح</p>";
    } else {
        echo "<p style='color: red;'>❌ خطأ: " . mysqli_error($conn) . "</p>";
    }
    
    echo "<hr>";
}

// التحقق من الجداول النهائية
echo "<h3>✅ الجداول النهائية:</h3>";
$tables = ['orders', 'order_items'];
foreach($tables as $table) {
    $result = mysqli_query($conn, "DESCRIBE $table");
    if($result) {
        echo "<p><strong>هيكل جدول $table:</strong></p>";
        echo "<table border='1' style='width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 12px;'>";
        echo "<tr style='background: #f0f0f0;'>
                <th>الحقل</th>
                <th>النوع</th>
                <th>NULL</th>
                <th>المفتاح</th>
                <th>الافتراضي</th>
              </tr>";
        
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
    } else {
        echo "<p style='color: red;'>❌ لا يمكن الوصول إلى جدول $table</p>";
    }
}

echo "<h3 style='color: green;'>🎉 اكتمل إصلاح جميع الجداول!</h3>";
echo "<p><a href='process_order.php'>اختبار عملية الطلب</a> | <a href='test_order_system.php'>اختبار النظام</a> | <a href='index.php'>العودة للرئيسية</a></p>";

mysqli_close($conn);
?>