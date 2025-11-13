<?php
session_start();
include 'includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $product_id = intval($input['product_id']);
    $quantity = intval($input['quantity']);
    
    // جلب معلومات المنتج والمخزون
    $sql = "SELECT name, stock_quantity FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($stmt);
    mysqli_stmt_close($stmt);
    
    if (!$product) {
        echo json_encode([
            'available' => false,
            'message' => 'المنتج غير موجود'
        ]);
        exit;
    }
    
    if ($product['stock_quantity'] <= 0) {
        echo json_encode([
            'available' => false,
            'message' => 'المنتج غير متوفر حالياً'
        ]);
        exit;
    }
    
    if ($product['stock_quantity'] < $quantity) {
        echo json_encode([
            'available' => false,
            'message' => 'لا يوجد مخزون كافٍ. المتوفر: ' . $product['stock_quantity'] . ' قطع'
        ]);
        exit;
    }
    
    echo json_encode([
        'available' => true,
        'message' => 'المخزون متوفر',
        'stock_quantity' => $product['stock_quantity']
    ]);
    
} else {
    echo json_encode([
        'available' => false,
        'message' => 'طريقة طلب غير صحيحة'
    ]);
}
?>