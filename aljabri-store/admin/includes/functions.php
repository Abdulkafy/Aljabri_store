<?php
// includes/functions.php

function generateOrderNumber($conn) {
    $prefix = "JAB";
    $year = date('Y');
    $month = date('m');
    
    $sql = "SELECT order_number FROM orders WHERE order_number LIKE ? ORDER BY id DESC LIMIT 1";
    $search_pattern = $prefix . $year . $month . '%';
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $search_pattern);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $last_order = mysqli_fetch_assoc($result);
    
    if ($last_order) {
        $last_number = intval(substr($last_order['order_number'], -4));
        $new_number = $last_number + 1;
    } else {
        $new_number = 1;
    }
    
    return $prefix . $year . $month . str_pad($new_number, 4, '0', STR_PAD_LEFT);
}

// دوال أخرى...
?>