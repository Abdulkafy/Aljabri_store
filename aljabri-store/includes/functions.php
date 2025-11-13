<?php
// دالة إنشاء رقم طلب فريد
function generateOrderNumber($conn) {
    $prefix = "ORD";
    $year = date('Y');
    $month = date('m');
    
    // الحصول على آخر رقم طلب لهذا الشهر
    $sql = "SELECT order_number FROM orders WHERE order_number LIKE ? ORDER BY id DESC LIMIT 1";
    $search_pattern = $prefix . $year . $month . '%';
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $search_pattern);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $last_order = mysqli_fetch_assoc($result);
    
    if ($last_order) {
        // استخراج الرقم التسلسلي من آخر طلب
        $last_number = intval(substr($last_order['order_number'], -4));
        $new_number = $last_number + 1;
    } else {
        $new_number = 1;
    }
    
    // تنسيق الرقم: ORD2024120001
    return $prefix . $year . $month . str_pad($new_number, 4, '0', STR_PAD_LEFT);
}

// بديل: دالة أبسط لإنشاء رقم طلب عشوائي
function generateSimpleOrderNumber() {
    $prefix = "JAB";
    $timestamp = time();
    $random = mt_rand(1000, 9999);
    return $prefix . $timestamp . $random;
}

// بديل: دالة باستخدام uniqid
function generateUniqueOrderNumber() {
    return "ORDER_" . uniqid() . "_" . mt_rand(1000, 9999);
}
?>