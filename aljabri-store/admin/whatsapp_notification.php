<?php
session_start();

// التحقق من أن المستخدم مسؤول
if (!isset($_SESSION['admin_logged_in'])) {
    die('غير مصرح بالوصول');
}

// الاتصال بقاعدة البيانات مباشرة
$conn = mysqli_connect('localhost', 'root', '', 'aljabri_store');

if (!$conn) {
    die('فشل الاتصال بقاعدة البيانات: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    
    // جلب بيانات الطلب باستخدام استعلام مباشر (بدون prepared statements)
    $sql = "SELECT o.*, 
                   GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, '×)') SEPARATOR '\n') as products 
            FROM orders o 
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            LEFT JOIN products p ON oi.product_id = p.id 
            WHERE o.id = $order_id 
            GROUP BY o.id";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $order = mysqli_fetch_assoc($result);
        
        // إرسال إشعار الواتساب
        $admin_phone = "967775577773";
        $store_name = "متجر الجابري";
        
        // بناء العنوان
        $address_parts = [];
        if (!empty($order['customer_city'])) $address_parts[] = $order['customer_city'];
        if (!empty($order['customer_area'])) $address_parts[] = $order['customer_area'];
        if (!empty($order['customer_street'])) $address_parts[] = $order['customer_street'];
        if (!empty($order['customer_building'])) $address_parts[] = 'مبنى ' . $order['customer_building'];
        if (!empty($order['customer_apartment'])) $address_parts[] = 'شقة ' . $order['customer_apartment'];
        $full_address = implode('، ', $address_parts);
        
        $message = "🛒 *تفاصيل الطلب - $store_name*
        
📋 *معلومات الطلب:*
• رقم الطلب: {$order['order_number']}
• التاريخ: " . date('Y-m-d H:i', strtotime($order['created_at'])) . "

👤 *معلومات العميل:*
• الاسم: {$order['customer_name']}
• الهاتف: {$order['customer_phone']}
• المدينة: {$order['customer_city']}
• المنطقة: {$order['customer_area']}

💰 *المعلومات المالية:*
• المجموع: " . number_format($order['total']) . " ريال
• طريقة الدفع: {$order['payment_method']}

📦 *المنتجات:*
{$order['products']}

📍 *العنوان:*
{$full_address}

📝 *ملاحظات:*
{$order['customer_notes']}";
    
        $encoded_message = urlencode($message);
        $whatsapp_url = "https://wa.me/$admin_phone?text=$encoded_message";
        
        // توجيه إلى رابط الواتساب
        header("Location: $whatsapp_url");
        exit;
    }
}

// إذا لم يتم العثور على الطلب
echo "<script>alert('لم يتم العثور على الطلب'); window.history.back();</script>";
?>