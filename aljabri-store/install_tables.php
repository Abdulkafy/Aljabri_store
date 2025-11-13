<?php
// install_tables.php - إنشاء جداول نظام الطلبات
include 'includes/config.php';

echo "<h2>🔧 تثبيت جداول نظام الطلبات</h2>";

// استعلامات إنشاء الجداول
$queries = [
    "CREATE TABLE orders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_number VARCHAR(20) UNIQUE NOT NULL,
        customer_name VARCHAR(100) NOT NULL,
        customer_phone VARCHAR(20) NOT NULL,
        customer_email VARCHAR(100),
        customer_address TEXT NOT NULL,
        order_notes TEXT,
        subtotal DECIMAL(10,2) NOT NULL,
        shipping DECIMAL(10,2) NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        currency VARCHAR(10) DEFAULT 'YER',
        status VARCHAR(20) DEFAULT 'pending',
        payment_method VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )" => "جدول الطلبات",
    
    "CREATE TABLE order_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        product_price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )" => "جدول عناصر الطلبات"
];

// تنفيذ الاستعلامات
foreach($queries as $query => $table_name) {
    echo "<p>جاري إنشاء: <strong>$table_name</strong>...</p>";
    
    if(mysqli_query($conn, $query)) {
        echo "<p style='color: green;'>✅ تم إنشاء $table_name بنجاح</p>";
    } else {
        echo "<p style='color: red;'>❌ خطأ في إنشاء $table_name: " . mysqli_error($conn) . "</p>";
    }
    
    echo "<hr>";
}

// التحقق من الجداول
echo "<h3>التحقق من الجداول المنشأة:</h3>";
$tables_result = mysqli_query($conn, "SHOW TABLES");
$tables = [];

while($row = mysqli_fetch_array($tables_result)) {
    $tables[] = $row[0];
    echo "<p style='color: blue;'>📊 جدول: <strong>{$row[0]}</strong></p>";
}

echo "<h3 style='color: green;'>🎉 اكتمل التثبيت بنجاح!</h3>";
echo "<p>يمكنك الآن <a href='cart.php'>اختبار نظام الطلبات</a></p>";

mysqli_close($conn);
?>