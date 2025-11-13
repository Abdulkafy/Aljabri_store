<?php
session_start();
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // جلب بيانات الطلب من الجلسة
    $checkout_data = $_SESSION['checkout_data'] ?? [];
    $cart_items = $checkout_data['cart_items'] ?? [];
    
    if (empty($cart_items)) {
        die('خطأ: سلة التسوق فارغة');
    }
    
    // جلب البيانات من النموذج
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $customer_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
    $customer_email = mysqli_real_escape_string($conn, $_POST['customer_email'] ?? '');
    $customer_city = mysqli_real_escape_string($conn, $_POST['customer_city']);
    $customer_area = mysqli_real_escape_string($conn, $_POST['customer_area']);
    $customer_street = mysqli_real_escape_string($conn, $_POST['customer_street']);
    $customer_building = mysqli_real_escape_string($conn, $_POST['customer_building'] ?? '');
    $customer_apartment = mysqli_real_escape_string($conn, $_POST['customer_apartment'] ?? '');
    $customer_notes = mysqli_real_escape_string($conn, $_POST['customer_notes'] ?? '');
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? 'cash');
    
    $subtotal = $checkout_data['subtotal'] ?? 0;
    $shipping = $checkout_data['shipping'] ?? 0;
    $total = $checkout_data['total'] ?? 0;
    
    // إنشاء رقم طلب فريد
    $order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
    
    // استخدام استعلام مباشر
    $sql = "INSERT INTO orders SET
        order_number = '$order_number',
        customer_name = '$customer_name',
        customer_phone = '$customer_phone',
        customer_email = '$customer_email',
        customer_city = '$customer_city',
        customer_area = '$customer_area',
        customer_street = '$customer_street',
        customer_building = '$customer_building',
        customer_apartment = '$customer_apartment',
        customer_notes = '$customer_notes',
        subtotal = $subtotal,
        shipping = $shipping,
        total = $total,
        payment_method = '$payment_method',
        status = 'pending',
        created_at = NOW()";
    
    if (mysqli_query($conn, $sql)) {
        $order_id = mysqli_insert_id($conn);
        
        // إدخال عناصر الطلب
        foreach ($cart_items as $item) {
            $product_id = intval($item['id']);
            $quantity = intval($item['quantity']);
            $unit_price = floatval($item['price_yer']);
            $total_price = $unit_price * $quantity;
            
            $item_sql = "INSERT INTO order_items SET
                order_id = $order_id,
                product_id = $product_id,
                quantity = $quantity,
                unit_price = $unit_price,
                total_price = $total_price";
            
            mysqli_query($conn, $item_sql);
        }
        
        // إرسال إشعار الواتساب إلى مسؤول المتجر
        sendWhatsAppNotification($order_id, $customer_name, $customer_phone, $total, $payment_method);
        
        // توجيه إلى صفحة النجاح
        header("Location: order_success.php?order_id=" . $order_id);
        exit;
        
    } else {
        die('خطأ في حفظ الطلب: ' . mysqli_error($conn));
    }
} else {
    header("Location: checkout.php");
    exit;
}

// دالة إرسال إشعار الواتساب
function sendWhatsAppNotification($order_id, $customer_name, $customer_phone, $total, $payment_method) {
    // رقم مسؤول المتجر
    $admin_phone = "967775577773"; // بدون علامة +
    
    // بيانات المتجر
    $store_name = "متجر الجابري";
    
    // نص الرسالة
    $message = "🛒 *طلب جديد - $store_name*
    
📋 *تفاصيل الطلب:*
• رقم الطلب: #$order_id
• اسم العميل: $customer_name
• هاتف العميل: $customer_phone
• المبلغ: " . number_format($total) . " ريال يمني
• طريقة الدفع: $payment_method

📦 *ملاحظة:*
تم استلام طلب جديد يرجى مراجعة لوحة التحكم للمزيد من التفاصيل والمتابعة.

⏰ الوقت: " . date('Y-m-d H:i:s');
    
    // ترميز الرسالة للرابط
    $encoded_message = urlencode($message);
    
    // إنشاء رابط الواتساب
    $whatsapp_url = "https://wa.me/$admin_phone?text=$encoded_message";
    
    // محاولة إرسال الرسالة باستخدام cURL
    sendWhatsAppMessage($whatsapp_url);
    
    // أيضًا نعيد الرابط ليتم استخدامه في الواجهة
    return $whatsapp_url;
}

// دالة إرسال رسالة الواتساب باستخدام cURL
function sendWhatsAppMessage($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // تسجيل محاولة الإرسال (اختياري)
    error_log("WhatsApp notification sent to admin. HTTP Code: $http_code");
    
    return $http_code == 200;
}
?>