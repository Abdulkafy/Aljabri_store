<?php
session_start();
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $order_id = $input['order_id'] ?? 0;
    $customer_name = $input['customer_name'] ?? '';
    $customer_phone = $input['customer_phone'] ?? '';
    $total = $input['total'] ?? 0;
    $payment_method = $input['payment_method'] ?? '';
    
    if ($order_id > 0) {
        // إرسال إشعار الواتساب
        $admin_phone = "967775577773";
        $store_name = "متجر الجابري";
        
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
        
        $encoded_message = urlencode($message);
        $whatsapp_url = "https://wa.me/$admin_phone?text=$encoded_message";
        
        // إرسال باستخدام cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $whatsapp_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        curl_close($ch);
        
        echo json_encode(['success' => true, 'message' => 'تم إرسال الإشعار']);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'بيانات غير كافية']);
?>