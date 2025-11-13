<?php
session_start();
include 'includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['product_ids']) && is_array($input['product_ids'])) {
        $product_ids = array_map('intval', $input['product_ids']);
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        
        $sql = "SELECT id, name, price_yer, price_sar, price_usd, main_image, stock_quantity 
                FROM products 
                WHERE id IN ($placeholders) AND stock_quantity > 0";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, str_repeat('i', count($product_ids)), ...$product_ids);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $products = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'products' => $products
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'بيانات غير صحيحة'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'طريقة طلب غير صحيحة'
    ]);
}
?>