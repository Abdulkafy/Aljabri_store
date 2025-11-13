<?php
session_start();

// دالة لتفريغ سلة التسوق
function clearCart() {
    // مسح السلة من localStorage (سيتم تنفيذها بواسطة JavaScript)
    // مسح السلة من الجلسة
    unset($_SESSION['cart']);
    unset($_SESSION['checkout_data']);
    
    return true;
}

// إذا كان الطلب POST، قم بتفريغ السلة
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = clearCart();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => $success]);
    exit;
}
?>