<?php
// test_order_system.php - اختبار نظام الطلبات
include 'includes/config.php';

echo "<h2>🧪 اختبار نظام الطلبات</h2>";

// 1. التحقق من الاتصال بقاعدة البيانات
echo "<h3>1. التحقق من الاتصال:</h3>";
if ($conn) {
    echo "<p style='color: green;'>✅ الاتصال بقاعدة البيانات ناجح</p>";
} else {
    echo "<p style='color: red;'>❌ فشل الاتصال بقاعدة البيانات</p>";
    exit;
}

// 2. التحقق من الجداول
echo "<h3>2. التحقق من الجداول:</h3>";
$tables = ['orders', 'order_items', 'products'];
foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color: green;'>✅ جدول $table موجود</p>";
    } else {
        echo "<p style='color: red;'>❌ جدول $table غير موجود</p>";
    }
}

// 3. التحقق من وجود منتجات للطلب
echo "<h3>3. التحقق من المنتجات:</h3>";
$products_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM products");
$products_row = mysqli_fetch_assoc($products_result);
echo "<p>عدد المنتجات في المتجر: <strong>{$products_row['count']}</strong></p>";

if ($products_row['count'] > 0) {
    echo "<p style='color: green;'>✅ يوجد منتجات للطلب</p>";
    
    // عرض بعض المنتجات
    $sample_products = mysqli_query($conn, "SELECT id, name, price_yer FROM products LIMIT 3");
    echo "<h4>عينة من المنتجات:</h4>";
    while($product = mysqli_fetch_assoc($sample_products)) {
        echo "<p>📦 {$product['name']} - {$product['price_yer']} ريال</p>";
    }
} else {
    echo "<p style='color: red;'>❌ لا توجد منتجات في المتجر</p>";
}

// 4. رابط لاختبار عملية الشراء
echo "<h3>4. اختبار عملية الشراء:</h3>";
echo "<p>لاختبار النظام، يمكنك:</p>";
echo "<ol>
        <li><a href='products.php'>اختيار منتجات من المتجر</a></li>
        <li>إضافتها إلى سلة التسوق</li>
        <li>الذهاب إلى <a href='cart.php'>سلة التسوق</a></li>
        <li>إتمام عملية الشراء من <a href='checkout.php'>صفحة الدفع</a></li>
      </ol>";

// 5. عرض الطلبات الحالية إن وجدت
echo "<h3>5. الطلبات الحالية:</h3>";
$orders_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders");
$orders_row = mysqli_fetch_assoc($orders_result);

if ($orders_row['count'] > 0) {
    echo "<p style='color: green;'>✅ يوجد {$orders_row['count']} طلب في النظام</p>";
    
    $recent_orders = mysqli_query($conn, "SELECT * FROM orders ORDER BY created_at DESC LIMIT 3");
    echo "<table border='1' style='width: 100%; border-collapse: collapse; margin: 10px 0;'>
            <tr style='background: #f0f0f0;'>
                <th>رقم الطلب</th>
                <th>العميل</th>
                <th>المجموع</th>
                <th>الحالة</th>
                <th>التاريخ</th>
            </tr>";
    
    while($order = mysqli_fetch_assoc($recent_orders)) {
        echo "<tr>
                <td>{$order['order_number']}</td>
                <td>{$order['customer_name']}</td>
                <td>{$order['total']} ريال</td>
                <td>{$order['status']}</td>
                <td>{$order['created_at']}</td>
              </tr>";
    }
    echo "</table>";
    
    echo "<p><a href='admin/orders.php'>عرض جميع الطلبات في لوحة التحكم</a></p>";
} else {
    echo "<p style='color: orange;'>⚠️ لا توجد طلبات حالياً</p>";
    echo "<p>بعد إتمام أول عملية شراء، ستظهر الطلبات هنا وفي لوحة التحكم</p>";
}

echo "<hr>";
echo "<h3 style='color: green;'>🎉 نظام الطلبات جاهز للاستخدام!</h3>";
echo "<p><a href='index.php'>العودة للرئيسية</a> | <a href='products.php'>بدء التسوق</a></p>";

mysqli_close($conn);
?>