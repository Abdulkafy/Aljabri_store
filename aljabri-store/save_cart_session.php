<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['cart']) && isset($input['currency'])) {
        $_SESSION['cart'] = $input['cart'];
        $_SESSION['currency'] = $input['currency'];
        $_SESSION['checkout_data'] = $input;
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'بيانات غير مكتملة']);
    }
}
?>