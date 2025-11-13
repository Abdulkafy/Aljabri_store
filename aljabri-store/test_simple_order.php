<?php
// test_simple_order.php - اختبار مبسط للطلب
include 'includes/config.php';

echo "<h2>🧪 اختبار عملية الطلب</h2>";

// محاكاة بيانات طلب
$test_data = [
    'customer_name' => 'اختبار العميل',
    'customer_phone' => '771234567',
    'customer_email' => 'test@example.com',
    'customer_address' => 'عنوان اختباري',
    'order_notes' => 'ملاحظات اختبار',
    'payment_method' => 'cash',
    'subtotal' => 50000,
    'shipping' => 5000,
    'total' => 55000
];

// محاولة إدخال طلب تجريبي
$sql = "INSERT INTO orders (customer_name, customer_phone, customer_email, customer_address, order_notes, subtotal, shipping, total, payment_method) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'sssssddds', 
        $test_data['customer_name'],
        $test_data['customer_phone'], 
        $test_data['customer_email'],
        $test_data['customer_address'],
        $test_data['order_notes'],
        $test_data['subtotal'],
        $test_data['shipping'],
        $test_data['total'],
        $test_data['payment_method']
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $order_id = mysqli_insert_id($conn);
        echo "<p style='color: green;'>✅ تم إنشاء طلب تجريبي برقم: $order_id</p>";
        
        // عرض الطلب المضاف
        $show_sql = "SELECT * FROM orders WHERE id = $order_id";
        $result = mysqli_query($conn, $show_sql);
        $order = mysqli_fetch_assoc($result);
        
        echo "<h3>بيانات الطلب المضاف:</h3>";
        echo "<pre>" . print_r($order, true) . "</pre>";
        
    } else {
        echo "<p style='color: red;'>❌ فشل في إنشاء الطلب: " . mysqli_error($conn) . "</p>";
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "<p style='color: red;'>❌ خطأ في إعداد الاستعلام: " . mysqli_error($conn) . "</p>";
}

echo "<p><a href='process_order.php'>اختبار process_order.php</a></p>";

mysqli_close($conn);
?>