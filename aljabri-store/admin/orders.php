<?php
// بداية الجلسة
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// الاتصال بقاعدة البيانات
$conn = mysqli_connect('localhost', 'root', '', 'aljabri_store');

if (!$conn) {
    die('فشل الاتصال بقاعدة البيانات: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

// تحديث حالة الطلب - التصحيح
if(isset($_GET['update_status'])){
    $order_id = intval($_GET['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_GET['status']);
    
    $update_sql = "UPDATE orders SET status = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, 'si', $new_status, $order_id);
    
    if(mysqli_stmt_execute($update_stmt)) {
        header('Location: orders.php?success=تم تحديث حالة الطلب بنجاح');
        exit;
    } else {
        $error = "خطأ في تحديث حالة الطلب: " . mysqli_error($conn);
    }
}

// حذف الطلب عند الإلغاء
if(isset($_GET['cancel_order'])){
    $order_id = intval($_GET['cancel_order']);
    
    // بداية transaction
    mysqli_begin_transaction($conn);
    
    try {
        // 1. حذف العناصر المرتبطة في order_items أولاً
        $delete_order_items = "DELETE FROM order_items WHERE order_id = $order_id";
        if(!mysqli_query($conn, $delete_order_items)) {
            throw new Exception("خطأ في حذف العناصر المرتبطة: " . mysqli_error($conn));
        }
        
        // 2. حذف الطلب
        $delete_order = "DELETE FROM orders WHERE id = $order_id";
        if(!mysqli_query($conn, $delete_order)) {
            throw new Exception("خطأ في حذف الطلب: " . mysqli_error($conn));
        }
        
        // تأكيد العملية
        mysqli_commit($conn);
        header('Location: orders.php?success=تم إلغاء وحذف الطلب بنجاح');
        exit;
        
    } catch (Exception $e) {
        // التراجع عن العملية في حالة الخطأ
        mysqli_rollback($conn);
        $error = $e->getMessage();
    }
}

// جلب جميع الطلبات مع البيانات الكاملة
$sql = "SELECT o.*, 
               COUNT(oi.id) as items_count,
               SUM(oi.quantity) as total_quantity,
               SUM(oi.total_price) as order_total
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        GROUP BY o.id 
        ORDER BY 
            CASE 
                WHEN o.status = 'pending' THEN 1
                WHEN o.status = 'confirmed' THEN 2
                WHEN o.status = 'shipped' THEN 3
                WHEN o.status = 'delivered' THEN 4
                ELSE 5
            END,
            o.created_at DESC";
$result = mysqli_query($conn, $sql);
$orders = [];

if($result && mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        $orders[] = $row;
    }
}

// جلب إحصائيات الطلبات
$total_orders = count($orders);
$pending_orders = array_filter($orders, function($order) {
    return $order['status'] == 'pending';
});
$confirmed_orders = array_filter($orders, function($order) {
    return $order['status'] == 'confirmed';
});
$shipped_orders = array_filter($orders, function($order) {
    return $order['status'] == 'shipped';
});
$delivered_orders = array_filter($orders, function($order) {
    return $order['status'] == 'delivered';
});

// إعدادات ثابتة للمتجر
$store_name = "متجر الجابري";
$primary_color = "#FF6B35";
$secondary_color = "#2C3E50";

// محاولة جلب الإعدادات من قاعدة البيانات
$settings_sql = "SELECT * FROM store_settings LIMIT 1";
$settings_result = mysqli_query($conn, $settings_sql);
if($settings_result && mysqli_num_rows($settings_result) > 0) {
    $settings = mysqli_fetch_assoc($settings_result);
    $store_name = $settings['store_name'] ?? "متجر الجابري";
    $primary_color = $settings['primary_color'] ?? "#FF6B35";
    $secondary_color = $settings['secondary_color'] ?? "#2C3E50";
} else {
    $settings = [
        'store_name' => $store_name,
        'primary_color' => $primary_color,
        'secondary_color' => $secondary_color
    ];
}

// مصفوفة طرق الدفع
$payment_methods = [
    'kareemi' => ['name' => 'كريمي جوال', 'icon' => 'fas fa-mobile-alt', 'color' => '#25D366'],
    'jeeb' => ['name' => 'جيب', 'icon' => 'fas fa-wallet', 'color' => '#FF6B35'],
    'onecash' => ['name' => 'ون كاش', 'icon' => 'fas fa-money-bill-wave', 'color' => '#007BFF'],
    'fulousk' => ['name' => 'فلوسك', 'icon' => 'fas fa-coins', 'color' => '#28A745'],
    'jawwali' => ['name' => 'جوالي', 'icon' => 'fas fa-sim-card', 'color' => '#FFC107'],
    'cash' => ['name' => 'الدفع عند الاستلام', 'icon' => 'fas fa-hand-holding-usd', 'color' => '#6C757D']
];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الطلبات - <?php echo htmlspecialchars($store_name); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: <?php echo $primary_color; ?>;
            --secondary-color: <?php echo $secondary_color; ?>;
        }
        
        body {
            font-family: 'Cairo', 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            direction: rtl;
            line-height: 1.6;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            background: var(--secondary-color);
            color: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            background: rgba(0,0,0,0.1);
        }
        
        .sidebar-header h2 {
            color: var(--primary-color);
            margin: 0 0 5px 0;
            font-size: 1.5rem;
        }
        
        .sidebar-header p {
            margin: 0;
            opacity: 0.8;
            font-size: 0.9rem;
        }
        
        .sidebar-nav ul {
            list-style: none;
            padding: 20px 0;
            margin: 0;
        }
        
        .sidebar-nav li {
            margin-bottom: 2px;
        }
        
        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            border-right: 4px solid transparent;
            font-size: 0.95rem;
        }
        
        .sidebar-nav a i {
            margin-left: 10px;
            width: 20px;
            text-align: center;
        }
        
        .sidebar-nav a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-right-color: var(--primary-color);
        }
        
        .sidebar-nav a.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-right-color: var(--primary-color);
            font-weight: bold;
        }
        
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-right: 280px;
        }
        
        .top-header {
            background: white;
            padding: 20px 30px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .header-left h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.5rem;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-name {
            color: #495057;
            font-weight: 500;
        }
        
        .view-store {
            background: var(--primary-color);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .view-store:hover {
            background: #e55a2b;
            transform: translateY(-1px);
        }
        
        .page-content {
            padding: 30px;
            flex: 1;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            text-align: center;
            border-top: 4px solid var(--primary-color);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .orders-tabs {
            display: flex;
            background: white;
            border-radius: 12px 12px 0 0;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            margin-bottom: 0;
        }
        
        .tab {
            flex: 1;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            font-weight: 600;
            color: #6c757d;
        }
        
        .tab:hover {
            background: #f8f9fa;
            color: var(--primary-color);
        }
        
        .tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
            background: #f8f9fa;
        }
        
        .tab-badge {
            background: var(--primary-color);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-right: 8px;
        }
        
        .orders-content {
            background: white;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .tab-content {
            display: none;
            padding: 0;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background: #f8f9fa;
            color: #495057;
            font-weight: 600;
            padding: 15px 20px;
            text-align: right;
            border-bottom: 2px solid #e9ecef;
            font-size: 0.9rem;
        }
        
        .data-table td {
            padding: 15px 20px;
            text-align: right;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }
        
        .data-table tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-confirmed {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-shipped {
            background: #d4edda;
            color: #155724;
        }
        
        .status-delivered {
            background: #28a745;
            color: white;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-view {
            background: #17a2b8;
            color: white;
        }
        
        .btn-view:hover {
            background: #138496;
            transform: translateY(-1px);
        }
        
        .btn-confirm {
            background: #28a745;
            color: white;
        }
        
        .btn-confirm:hover {
            background: #218838;
            transform: translateY(-1px);
        }
        
        .btn-ship {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-ship:hover {
            background: #e0a800;
            transform: translateY(-1px);
        }
        
        .btn-deliver {
            background: #20c997;
            color: white;
        }
        
        .btn-deliver:hover {
            background: #1ba87e;
            transform: translateY(-1px);
        }
        
        .btn-cancel {
            background: #dc3545;
            color: white;
        }
        
        .btn-cancel:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        
        .btn-whatsapp {
            background: #25D366;
            color: white;
        }
        
        .btn-whatsapp:hover {
            background: #1da851;
            transform: translateY(-1px);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid transparent;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        .no-orders {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .no-orders i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .no-orders h3 {
            margin: 0 0 10px 0;
            color: #495057;
        }
        
        .payment-method {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: #f8f9fa;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #495057;
            border: 1px solid #e9ecef;
        }
        
        .payment-method i {
            font-size: 0.9rem;
        }
        
        .order-number {
            font-weight: 600;
            color: var(--primary-color);
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }
        
        .customer-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .customer-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .customer-phone {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .order-actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .shipping-address {
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 6px;
            border-right: 3px solid var(--primary-color);
            margin-top: 5px;
            font-size: 0.8rem;
            color: #495057;
            max-width: 200px;
        }
        
        .address-icon {
            color: var(--primary-color);
            margin-left: 5px;
        }
        
        .no-address {
            background: #fff3cd;
            border-right-color: #ffc107;
            color: #856404;
        }
        
        .address-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .address-modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .address-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .address-modal-header h3 {
            margin: 0;
            color: #2c3e50;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6c757d;
        }
        
        .address-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .address-field {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .address-field:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .address-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
            display: block;
        }
        
        .address-value {
            color: #6c757d;
        }
        
        .show-address-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
            margin-top: 5px;
            transition: all 0.3s ease;
        }
        
        .show-address-btn:hover {
            background: #e55a2b;
            transform: translateY(-1px);
        }
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-right: 0;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .orders-tabs {
                flex-direction: column;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .data-table {
                min-width: 1200px;
            }
            
            .order-actions {
                flex-direction: column;
            }
            
            .address-modal-content {
                margin: 20px;
                width: calc(100% - 40px);
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- الشريط الجانبي -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo htmlspecialchars($store_name); ?></h2>
                <p>لوحة التحكم</p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="index.php">
                            <i class="fas fa-chart-bar"></i>
                            الإحصائيات
                        </a>
                    </li>
                    <li>
                        <a href="products.php">
                            <i class="fas fa-box"></i>
                            المنتجات
                        </a>
                    </li>
                    <li>
                        <a href="orders.php" class="active">
                            <i class="fas fa-shopping-cart"></i>
                            الطلبات
                        </a>
                    </li>
                    <li>
                        <a href="customers.php">
                            <i class="fas fa-users"></i>
                            العملاء
                        </a>
                    </li>
                    <li>
                        <a href="settings.php">
                            <i class="fas fa-cog"></i>
                            الإعدادات
                        </a>
                    </li>
                    <li>
                        <a href="logout.php" style="color: #e74c3c;">
                            <i class="fas fa-sign-out-alt"></i>
                            تسجيل الخروج
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- المحتوى الرئيسي -->
        <main class="main-content">
            <!-- الهيدر العلوي -->
            <header class="top-header">
                <div class="header-left">
                    <h1><i class="fas fa-shopping-cart"></i> إدارة الطلبات</h1>
                </div>
                <div class="header-right">
                    <span class="admin-name"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'مدير النظام'); ?></span>
                    <a href="../index.php" target="_blank" class="view-store">
                        <i class="fas fa-external-link-alt"></i> عرض المتجر
                    </a>
                </div>
            </header>

            <!-- محتوى الصفحة -->
            <div class="page-content">
                <!-- بطاقات الإحصائيات -->
                <div class="stats-cards">
                    <div class="stat-card" onclick="openTab('all')">
                        <div class="stat-number"><?php echo $total_orders; ?></div>
                        <div class="stat-label">إجمالي الطلبات</div>
                    </div>
                    <div class="stat-card" onclick="openTab('pending')">
                        <div class="stat-number"><?php echo count($pending_orders); ?></div>
                        <div class="stat-label">طلبات قيد الانتظار</div>
                    </div>
                    <div class="stat-card" onclick="openTab('confirmed')">
                        <div class="stat-number"><?php echo count($confirmed_orders); ?></div>
                        <div class="stat-label">طلبات مؤكدة</div>
                    </div>
                    <div class="stat-card" onclick="openTab('delivered')">
                        <div class="stat-number"><?php echo count($delivered_orders); ?></div>
                        <div class="stat-label">طلبات مكتملة</div>
                    </div>
                </div>

                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- تبويبات الطلبات -->
                <div class="orders-tabs">
                    <div class="tab active" onclick="openTab('all')">
                        <span class="tab-badge"><?php echo $total_orders; ?></span>
                        جميع الطلبات
                    </div>
                    <div class="tab" onclick="openTab('pending')">
                        <span class="tab-badge"><?php echo count($pending_orders); ?></span>
                        قيد الانتظار
                    </div>
                    <div class="tab" onclick="openTab('confirmed')">
                        <span class="tab-badge"><?php echo count($confirmed_orders); ?></span>
                        مؤكدة
                    </div>
                    <div class="tab" onclick="openTab('shipped')">
                        <span class="tab-badge"><?php echo count($shipped_orders); ?></span>
                        تم الشحن
                    </div>
                    <div class="tab" onclick="openTab('delivered')">
                        <span class="tab-badge"><?php echo count($delivered_orders); ?></span>
                        مكتملة
                    </div>
                </div>

                <!-- محتوى التبويبات -->
                <div class="orders-content">
                    <!-- تبويب جميع الطلبات -->
                    <div id="tab-all" class="tab-content active">
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>رقم الطلب</th>
                                        <th>العميل</th>
                                        <th>الهاتف</th>
                                        <th>عنوان الشحن</th>
                                        <th>المبلغ</th>
                                        <th>طريقة الدفع</th>
                                        <th>الحالة</th>
                                        <th>التاريخ</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($orders)): ?>
                                        <?php foreach($orders as $order): 
                                            $payment_info = $payment_methods[$order['payment_method']] ?? 
                                                          ['name' => $order['payment_method'], 'icon' => 'fas fa-credit-card', 'color' => '#6C757D'];
                                            
                                            // بناء عنوان الشحن
                                            $address_parts = [];
                                            if (!empty($order['customer_city'])) $address_parts[] = $order['customer_city'];
                                            if (!empty($order['customer_area'])) $address_parts[] = $order['customer_area'];
                                            if (!empty($order['customer_street'])) $address_parts[] = $order['customer_street'];
                                            if (!empty($order['customer_building'])) $address_parts[] = 'مبنى ' . $order['customer_building'];
                                            if (!empty($order['customer_apartment'])) $address_parts[] = 'شقة ' . $order['customer_apartment'];
                                            
                                            $full_address = implode('، ', $address_parts);
                                            $short_address = !empty($full_address) ? 
                                                (mb_strlen($full_address) > 50 ? mb_substr($full_address, 0, 50) . '...' : $full_address) : 
                                                'لم يتم تحديد عنوان';
                                            $has_address = !empty($full_address);
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="order-number"><?php echo htmlspecialchars($order['order_number'] ?? 'ORD-' . $order['id']); ?></span>
                                            </td>
                                            <td>
                                                <div class="customer-info">
                                                    <span class="customer-name"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                                            <td>
                                                <div class="shipping-address <?php echo !$has_address ? 'no-address' : ''; ?>">
                                                    <i class="fas fa-map-marker-alt address-icon"></i>
                                                    <?php echo htmlspecialchars($short_address); ?>
                                                </div>
                                                <?php if($has_address): ?>
                                                <button class="show-address-btn" onclick="showAddressModal(<?php echo $order['id']; ?>, '<?php echo addslashes($order['customer_name']); ?>', '<?php echo addslashes($full_address); ?>', '<?php echo addslashes($order['customer_phone']); ?>', '<?php echo addslashes($order['customer_city'] ?? ''); ?>', '<?php echo addslashes($order['customer_area'] ?? ''); ?>', '<?php echo addslashes($order['customer_street'] ?? ''); ?>', '<?php echo addslashes($order['customer_building'] ?? ''); ?>', '<?php echo addslashes($order['customer_apartment'] ?? ''); ?>', '<?php echo addslashes($order['customer_notes'] ?? ''); ?>')">
                                                    <i class="fas fa-expand"></i> عرض العنوان
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong style="color: var(--primary-color);">
                                                    <?php echo number_format($order['total'] ?? $order['order_total'] ?? 0); ?> ريال
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="payment-method" style="border-left: 3px solid <?php echo $payment_info['color']; ?>">
                                                    <i class="<?php echo $payment_info['icon']; ?>" style="color: <?php echo $payment_info['color']; ?>"></i>
                                                    <?php echo $payment_info['name']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                                    <?php 
                                                        $status_names = [
                                                            'pending' => 'قيد الانتظار',
                                                            'confirmed' => 'مؤكد',
                                                            'shipped' => 'تم الشحن',
                                                            'delivered' => 'مكتمل',
                                                            'cancelled' => 'ملغي'
                                                        ];
                                                        echo $status_names[$order['status']] ?? $order['status'];
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo date('Y-m-d', strtotime($order['created_at'])); ?>
                                                <br>
                                                <small style="color: #6c757d;">
                                                    <?php echo date('H:i', strtotime($order['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="order-actions">
                                                    <?php if($order['status'] == 'pending'): ?>
                                                        <a href="orders.php?order_id=<?php echo $order['id']; ?>&status=confirmed&update_status=1" class="btn btn-confirm" onclick="return confirm('هل أنت متأكد من تأكيد هذا الطلب؟')">
                                                            <i class="fas fa-check"></i> تأكيد
                                                        </a>
                                                        <a href="orders.php?cancel_order=<?php echo $order['id']; ?>" class="btn btn-cancel" onclick="return confirm('هل أنت متأكد من إلغاء هذا الطلب؟ سيتم حذفه نهائياً.')">
                                                            <i class="fas fa-times"></i> إلغاء
                                                        </a>
                                                    <?php elseif($order['status'] == 'confirmed'): ?>
                                                        <a href="orders.php?order_id=<?php echo $order['id']; ?>&status=shipped&update_status=1" class="btn btn-ship" onclick="return confirm('هل أنت متأكد من تحديث حالة الطلب إلى تم الشحن؟')">
                                                            <i class="fas fa-shipping-fast"></i> شحن
                                                        </a>
                                                    <?php elseif($order['status'] == 'shipped'): ?>
                                                        <a href="orders.php?order_id=<?php echo $order['id']; ?>&status=delivered&update_status=1" class="btn btn-deliver" onclick="return confirm('هل أنت متأكد من تسليم هذا الطلب؟')">
                                                            <i class="fas fa-check-double"></i> تسليم
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <!-- زر إشعار الواتساب -->
                                                    <a href="whatsapp_notification.php?order_id=<?php echo $order['id']; ?>" 
                                                       target="_blank" 
                                                       class="btn btn-whatsapp">
                                                       <i class="fab fa-whatsapp"></i> واتساب
                                                    </a>
                                                    
                                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-view">
                                                        <i class="fas fa-eye"></i> عرض
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9">
                                                <div class="no-orders">
                                                    <i class="fas fa-shopping-cart"></i>
                                                    <h3>لا توجد طلبات حالياً</h3>
                                                    <p>سيظهر هنا جميع طلبات العملاء عند توفرها</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- تبويب الطلبات قيد الانتظار -->
                    <div id="tab-pending" class="tab-content">
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>رقم الطلب</th>
                                        <th>العميل</th>
                                        <th>الهاتف</th>
                                        <th>عنوان الشحن</th>
                                        <th>المبلغ</th>
                                        <th>طريقة الدفع</th>
                                        <th>التاريخ</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($pending_orders)): ?>
                                        <?php foreach($pending_orders as $order): 
                                            $payment_info = $payment_methods[$order['payment_method']] ?? 
                                                          ['name' => $order['payment_method'], 'icon' => 'fas fa-credit-card', 'color' => '#6C757D'];
                                            
                                            // بناء عنوان الشحن
                                            $address_parts = [];
                                            if (!empty($order['customer_city'])) $address_parts[] = $order['customer_city'];
                                            if (!empty($order['customer_area'])) $address_parts[] = $order['customer_area'];
                                            if (!empty($order['customer_street'])) $address_parts[] = $order['customer_street'];
                                            if (!empty($order['customer_building'])) $address_parts[] = 'مبنى ' . $order['customer_building'];
                                            if (!empty($order['customer_apartment'])) $address_parts[] = 'شقة ' . $order['customer_apartment'];
                                            
                                            $full_address = implode('، ', $address_parts);
                                            $short_address = !empty($full_address) ? 
                                                (mb_strlen($full_address) > 50 ? mb_substr($full_address, 0, 50) . '...' : $full_address) : 
                                                'لم يتم تحديد عنوان';
                                            $has_address = !empty($full_address);
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="order-number"><?php echo htmlspecialchars($order['order_number'] ?? 'ORD-' . $order['id']); ?></span>
                                            </td>
                                            <td>
                                                <div class="customer-info">
                                                    <span class="customer-name"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                                            <td>
                                                <div class="shipping-address <?php echo !$has_address ? 'no-address' : ''; ?>">
                                                    <i class="fas fa-map-marker-alt address-icon"></i>
                                                    <?php echo htmlspecialchars($short_address); ?>
                                                </div>
                                                <?php if($has_address): ?>
                                                <button class="show-address-btn" onclick="showAddressModal(<?php echo $order['id']; ?>, '<?php echo addslashes($order['customer_name']); ?>', '<?php echo addslashes($full_address); ?>', '<?php echo addslashes($order['customer_phone']); ?>', '<?php echo addslashes($order['customer_city'] ?? ''); ?>', '<?php echo addslashes($order['customer_area'] ?? ''); ?>', '<?php echo addslashes($order['customer_street'] ?? ''); ?>', '<?php echo addslashes($order['customer_building'] ?? ''); ?>', '<?php echo addslashes($order['customer_apartment'] ?? ''); ?>', '<?php echo addslashes($order['customer_notes'] ?? ''); ?>')">
                                                    <i class="fas fa-expand"></i> عرض العنوان
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong style="color: var(--primary-color);">
                                                    <?php echo number_format($order['total'] ?? $order['order_total'] ?? 0); ?> ريال
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="payment-method" style="border-left: 3px solid <?php echo $payment_info['color']; ?>">
                                                    <i class="<?php echo $payment_info['icon']; ?>" style="color: <?php echo $payment_info['color']; ?>"></i>
                                                    <?php echo $payment_info['name']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo date('Y-m-d', strtotime($order['created_at'])); ?>
                                                <br>
                                                <small style="color: #6c757d;">
                                                    <?php echo date('H:i', strtotime($order['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="order-actions">
                                                    <a href="orders.php?order_id=<?php echo $order['id']; ?>&status=confirmed&update_status=1" class="btn btn-confirm" onclick="return confirm('هل أنت متأكد من تأكيد هذا الطلب؟')">
                                                        <i class="fas fa-check"></i> تأكيد الطلب
                                                    </a>
                                                    <a href="orders.php?cancel_order=<?php echo $order['id']; ?>" class="btn btn-cancel" onclick="return confirm('هل أنت متأكد من إلغاء هذا الطلب؟ سيتم حذفه نهائياً.')">
                                                        <i class="fas fa-times"></i> إلغاء الطلب
                                                    </a>
                                                    
                                                    <!-- زر إشعار الواتساب -->
                                                    <a href="whatsapp_notification.php?order_id=<?php echo $order['id']; ?>" 
                                                       target="_blank" 
                                                       class="btn btn-whatsapp">
                                                       <i class="fab fa-whatsapp"></i> واتساب
                                                    </a>
                                                    
                                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-view">
                                                        <i class="fas fa-eye"></i> عرض
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8">
                                                <div class="no-orders">
                                                    <i class="fas fa-clock"></i>
                                                    <h3>لا توجد طلبات قيد الانتظار</h3>
                                                    <p>جميع الطلبات تمت معالجتها</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- تبويب الطلبات المؤكدة -->
                    <div id="tab-confirmed" class="tab-content">
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>رقم الطلب</th>
                                        <th>العميل</th>
                                        <th>الهاتف</th>
                                        <th>عنوان الشحن</th>
                                        <th>المبلغ</th>
                                        <th>طريقة الدفع</th>
                                        <th>التاريخ</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($confirmed_orders)): ?>
                                        <?php foreach($confirmed_orders as $order): 
                                            $payment_info = $payment_methods[$order['payment_method']] ?? 
                                                          ['name' => $order['payment_method'], 'icon' => 'fas fa-credit-card', 'color' => '#6C757D'];
                                            
                                            // بناء عنوان الشحن
                                            $address_parts = [];
                                            if (!empty($order['customer_city'])) $address_parts[] = $order['customer_city'];
                                            if (!empty($order['customer_area'])) $address_parts[] = $order['customer_area'];
                                            if (!empty($order['customer_street'])) $address_parts[] = $order['customer_street'];
                                            if (!empty($order['customer_building'])) $address_parts[] = 'مبنى ' . $order['customer_building'];
                                            if (!empty($order['customer_apartment'])) $address_parts[] = 'شقة ' . $order['customer_apartment'];
                                            
                                            $full_address = implode('، ', $address_parts);
                                            $short_address = !empty($full_address) ? 
                                                (mb_strlen($full_address) > 50 ? mb_substr($full_address, 0, 50) . '...' : $full_address) : 
                                                'لم يتم تحديد عنوان';
                                            $has_address = !empty($full_address);
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="order-number"><?php echo htmlspecialchars($order['order_number'] ?? 'ORD-' . $order['id']); ?></span>
                                            </td>
                                            <td>
                                                <div class="customer-info">
                                                    <span class="customer-name"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                                            <td>
                                                <div class="shipping-address <?php echo !$has_address ? 'no-address' : ''; ?>">
                                                    <i class="fas fa-map-marker-alt address-icon"></i>
                                                    <?php echo htmlspecialchars($short_address); ?>
                                                </div>
                                                <?php if($has_address): ?>
                                                <button class="show-address-btn" onclick="showAddressModal(<?php echo $order['id']; ?>, '<?php echo addslashes($order['customer_name']); ?>', '<?php echo addslashes($full_address); ?>', '<?php echo addslashes($order['customer_phone']); ?>', '<?php echo addslashes($order['customer_city'] ?? ''); ?>', '<?php echo addslashes($order['customer_area'] ?? ''); ?>', '<?php echo addslashes($order['customer_street'] ?? ''); ?>', '<?php echo addslashes($order['customer_building'] ?? ''); ?>', '<?php echo addslashes($order['customer_apartment'] ?? ''); ?>', '<?php echo addslashes($order['customer_notes'] ?? ''); ?>')">
                                                    <i class="fas fa-expand"></i> عرض العنوان
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong style="color: var(--primary-color);">
                                                    <?php echo number_format($order['total'] ?? $order['order_total'] ?? 0); ?> ريال
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="payment-method" style="border-left: 3px solid <?php echo $payment_info['color']; ?>">
                                                    <i class="<?php echo $payment_info['icon']; ?>" style="color: <?php echo $payment_info['color']; ?>"></i>
                                                    <?php echo $payment_info['name']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo date('Y-m-d', strtotime($order['created_at'])); ?>
                                                <br>
                                                <small style="color: #6c757d;">
                                                    <?php echo date('H:i', strtotime($order['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="order-actions">
                                                    <a href="orders.php?order_id=<?php echo $order['id']; ?>&status=shipped&update_status=1" class="btn btn-ship" onclick="return confirm('هل أنت متأكد من تحديث حالة الطلب إلى تم الشحن؟')">
                                                        <i class="fas fa-shipping-fast"></i> تم الشحن
                                                    </a>
                                                    
                                                    <!-- زر إشعار الواتساب -->
                                                    <a href="whatsapp_notification.php?order_id=<?php echo $order['id']; ?>" 
                                                       target="_blank" 
                                                       class="btn btn-whatsapp">
                                                       <i class="fab fa-whatsapp"></i> واتساب
                                                    </a>
                                                    
                                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-view">
                                                        <i class="fas fa-eye"></i> عرض
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8">
                                                <div class="no-orders">
                                                    <i class="fas fa-check-circle"></i>
                                                    <h3>لا توجد طلبات مؤكدة</h3>
                                                    <p>الطلبات المؤكدة ستظهر هنا</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- تبويب الطلبات المشحونة -->
                    <div id="tab-shipped" class="tab-content">
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>رقم الطلب</th>
                                        <th>العميل</th>
                                        <th>الهاتف</th>
                                        <th>عنوان الشحن</th>
                                        <th>المبلغ</th>
                                        <th>طريقة الدفع</th>
                                        <th>التاريخ</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($shipped_orders)): ?>
                                        <?php foreach($shipped_orders as $order): 
                                            $payment_info = $payment_methods[$order['payment_method']] ?? 
                                                          ['name' => $order['payment_method'], 'icon' => 'fas fa-credit-card', 'color' => '#6C757D'];
                                            
                                            // بناء عنوان الشحن
                                            $address_parts = [];
                                            if (!empty($order['customer_city'])) $address_parts[] = $order['customer_city'];
                                            if (!empty($order['customer_area'])) $address_parts[] = $order['customer_area'];
                                            if (!empty($order['customer_street'])) $address_parts[] = $order['customer_street'];
                                            if (!empty($order['customer_building'])) $address_parts[] = 'مبنى ' . $order['customer_building'];
                                            if (!empty($order['customer_apartment'])) $address_parts[] = 'شقة ' . $order['customer_apartment'];
                                            
                                            $full_address = implode('، ', $address_parts);
                                            $short_address = !empty($full_address) ? 
                                                (mb_strlen($full_address) > 50 ? mb_substr($full_address, 0, 50) . '...' : $full_address) : 
                                                'لم يتم تحديد عنوان';
                                            $has_address = !empty($full_address);
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="order-number"><?php echo htmlspecialchars($order['order_number'] ?? 'ORD-' . $order['id']); ?></span>
                                            </td>
                                            <td>
                                                <div class="customer-info">
                                                    <span class="customer-name"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                                            <td>
                                                <div class="shipping-address <?php echo !$has_address ? 'no-address' : ''; ?>">
                                                    <i class="fas fa-map-marker-alt address-icon"></i>
                                                    <?php echo htmlspecialchars($short_address); ?>
                                                </div>
                                                <?php if($has_address): ?>
                                                <button class="show-address-btn" onclick="showAddressModal(<?php echo $order['id']; ?>, '<?php echo addslashes($order['customer_name']); ?>', '<?php echo addslashes($full_address); ?>', '<?php echo addslashes($order['customer_phone']); ?>', '<?php echo addslashes($order['customer_city'] ?? ''); ?>', '<?php echo addslashes($order['customer_area'] ?? ''); ?>', '<?php echo addslashes($order['customer_street'] ?? ''); ?>', '<?php echo addslashes($order['customer_building'] ?? ''); ?>', '<?php echo addslashes($order['customer_apartment'] ?? ''); ?>', '<?php echo addslashes($order['customer_notes'] ?? ''); ?>')">
                                                    <i class="fas fa-expand"></i> عرض العنوان
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong style="color: var(--primary-color);">
                                                    <?php echo number_format($order['total'] ?? $order['order_total'] ?? 0); ?> ريال
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="payment-method" style="border-left: 3px solid <?php echo $payment_info['color']; ?>">
                                                    <i class="<?php echo $payment_info['icon']; ?>" style="color: <?php echo $payment_info['color']; ?>"></i>
                                                    <?php echo $payment_info['name']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo date('Y-m-d', strtotime($order['created_at'])); ?>
                                                <br>
                                                <small style="color: #6c757d;">
                                                    <?php echo date('H:i', strtotime($order['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="order-actions">
                                                    <a href="orders.php?order_id=<?php echo $order['id']; ?>&status=delivered&update_status=1" class="btn btn-deliver" onclick="return confirm('هل أنت متأكد من تسليم هذا الطلب؟')">
                                                        <i class="fas fa-check-double"></i> تم التسليم
                                                    </a>
                                                    
                                                    <!-- زر إشعار الواتساب -->
                                                    <a href="whatsapp_notification.php?order_id=<?php echo $order['id']; ?>" 
                                                       target="_blank" 
                                                       class="btn btn-whatsapp">
                                                       <i class="fab fa-whatsapp"></i> واتساب
                                                    </a>
                                                    
                                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-view">
                                                        <i class="fas fa-eye"></i> عرض
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8">
                                                <div class="no-orders">
                                                    <i class="fas fa-shipping-fast"></i>
                                                    <h3>لا توجد طلبات مشحونة</h3>
                                                    <p>الطلبات المشحونة ستظهر هنا</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- تبويب الطلبات المكتملة -->
                    <div id="tab-delivered" class="tab-content">
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>رقم الطلب</th>
                                        <th>العميل</th>
                                        <th>الهاتف</th>
                                        <th>عنوان الشحن</th>
                                        <th>المبلغ</th>
                                        <th>طريقة الدفع</th>
                                        <th>تاريخ التسليم</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($delivered_orders)): ?>
                                        <?php foreach($delivered_orders as $order): 
                                            $payment_info = $payment_methods[$order['payment_method']] ?? 
                                                          ['name' => $order['payment_method'], 'icon' => 'fas fa-credit-card', 'color' => '#6C757D'];
                                            
                                            // بناء عنوان الشحن
                                            $address_parts = [];
                                            if (!empty($order['customer_city'])) $address_parts[] = $order['customer_city'];
                                            if (!empty($order['customer_area'])) $address_parts[] = $order['customer_area'];
                                            if (!empty($order['customer_street'])) $address_parts[] = $order['customer_street'];
                                            if (!empty($order['customer_building'])) $address_parts[] = 'مبنى ' . $order['customer_building'];
                                            if (!empty($order['customer_apartment'])) $address_parts[] = 'شقة ' . $order['customer_apartment'];
                                            
                                            $full_address = implode('، ', $address_parts);
                                            $short_address = !empty($full_address) ? 
                                                (mb_strlen($full_address) > 50 ? mb_substr($full_address, 0, 50) . '...' : $full_address) : 
                                                'لم يتم تحديد عنوان';
                                            $has_address = !empty($full_address);
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="order-number"><?php echo htmlspecialchars($order['order_number'] ?? 'ORD-' . $order['id']); ?></span>
                                            </td>
                                            <td>
                                                <div class="customer-info">
                                                    <span class="customer-name"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                                            <td>
                                                <div class="shipping-address <?php echo !$has_address ? 'no-address' : ''; ?>">
                                                    <i class="fas fa-map-marker-alt address-icon"></i>
                                                    <?php echo htmlspecialchars($short_address); ?>
                                                </div>
                                                <?php if($has_address): ?>
                                                <button class="show-address-btn" onclick="showAddressModal(<?php echo $order['id']; ?>, '<?php echo addslashes($order['customer_name']); ?>', '<?php echo addslashes($full_address); ?>', '<?php echo addslashes($order['customer_phone']); ?>', '<?php echo addslashes($order['customer_city'] ?? ''); ?>', '<?php echo addslashes($order['customer_area'] ?? ''); ?>', '<?php echo addslashes($order['customer_street'] ?? ''); ?>', '<?php echo addslashes($order['customer_building'] ?? ''); ?>', '<?php echo addslashes($order['customer_apartment'] ?? ''); ?>', '<?php echo addslashes($order['customer_notes'] ?? ''); ?>')">
                                                    <i class="fas fa-expand"></i> عرض العنوان
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong style="color: var(--primary-color);">
                                                    <?php echo number_format($order['total'] ?? $order['order_total'] ?? 0); ?> ريال
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="payment-method" style="border-left: 3px solid <?php echo $payment_info['color']; ?>">
                                                    <i class="<?php echo $payment_info['icon']; ?>" style="color: <?php echo $payment_info['color']; ?>"></i>
                                                    <?php echo $payment_info['name']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo date('Y-m-d', strtotime($order['created_at'])); ?>
                                                <br>
                                                <small style="color: #6c757d;">
                                                    <?php echo date('H:i', strtotime($order['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="order-actions">
                                                    <!-- زر إشعار الواتساب -->
                                                    <a href="whatsapp_notification.php?order_id=<?php echo $order['id']; ?>" 
                                                       target="_blank" 
                                                       class="btn btn-whatsapp">
                                                       <i class="fab fa-whatsapp"></i> واتساب
                                                    </a>
                                                    
                                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-view">
                                                        <i class="fas fa-eye"></i> عرض التفاصيل
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8">
                                                <div class="no-orders">
                                                    <i class="fas fa-check-double"></i>
                                                    <h3>لا توجد طلبات مكتملة</h3>
                                                    <p>الطلبات المكتملة ستظهر هنا</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- نافذة عرض العنوان -->
    <div id="addressModal" class="address-modal">
        <div class="address-modal-content">
            <div class="address-modal-header">
                <h3><i class="fas fa-map-marker-alt"></i> عنوان التوصيل</h3>
                <button class="close-modal" onclick="closeAddressModal()">&times;</button>
            </div>
            <div class="address-details" id="addressDetails">
                <!-- سيتم تعبئة البيانات هنا بواسطة JavaScript -->
            </div>
            <div style="text-align: left;">
                <button class="btn btn-secondary" onclick="closeAddressModal()">
                    <i class="fas fa-times"></i> إغلاق
                </button>
            </div>
        </div>
    </div>

    <script>
        // فتح التبويبات
        function openTab(tabName) {
            // إخفاء جميع محتويات التبويبات
            const tabContents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }
            
            // إلغاء تنشيط جميع التبويبات
            const tabs = document.getElementsByClassName('tab');
            for (let i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove('active');
            }
            
            // إظهار التبويب المحدد
            document.getElementById('tab-' + tabName).classList.add('active');
            
            // تنشيط التبويب المحدد
            const activeTab = Array.from(tabs).find(tab => tab.textContent.includes(getTabText(tabName)));
            if (activeTab) {
                activeTab.classList.add('active');
            }
        }
        
        function getTabText(tabName) {
            const tabMap = {
                'all': 'جميع الطلبات',
                'pending': 'قيد الانتظار',
                'confirmed': 'مؤكدة',
                'shipped': 'تم الشحن',
                'delivered': 'مكتملة'
            };
            return tabMap[tabName] || tabName;
        }
        
        // عرض نافذة العنوان
        function showAddressModal(orderId, customerName, fullAddress, phone, city, area, street, building, apartment, notes) {
            const modal = document.getElementById('addressModal');
            const details = document.getElementById('addressDetails');
            
            let addressHtml = `
                <div class="address-field">
                    <span class="address-label">العميل:</span>
                    <div class="address-value">${customerName}</div>
                </div>
                <div class="address-field">
                    <span class="address-label">رقم الهاتف:</span>
                    <div class="address-value">${phone}</div>
                </div>
            `;
            
            if (city) {
                addressHtml += `
                    <div class="address-field">
                        <span class="address-label">المدينة:</span>
                        <div class="address-value">${city}</div>
                    </div>
                `;
            }
            
            if (area) {
                addressHtml += `
                    <div class="address-field">
                        <span class="address-label">المنطقة:</span>
                        <div class="address-value">${area}</div>
                    </div>
                `;
            }
            
            if (street) {
                addressHtml += `
                    <div class="address-field">
                        <span class="address-label">الشارع:</span>
                        <div class="address-value">${street}</div>
                    </div>
                `;
            }
            
            if (building) {
                addressHtml += `
                    <div class="address-field">
                        <span class="address-label">المبنى:</span>
                        <div class="address-value">${building}</div>
                    </div>
                `;
            }
            
            if (apartment) {
                addressHtml += `
                    <div class="address-field">
                        <span class="address-label">الشقة:</span>
                        <div class="address-value">${apartment}</div>
                    </div>
                `;
            }
            
            if (notes) {
                addressHtml += `
                    <div class="address-field">
                        <span class="address-label">ملاحظات التوصيل:</span>
                        <div class="address-value" style="background: #fff3cd; padding: 10px; border-radius: 4px; border-right: 3px solid #ffc107;">${notes}</div>
                    </div>
                `;
            }
            
            addressHtml += `
                <div class="address-field">
                    <span class="address-label">العنوان الكامل:</span>
                    <div class="address-value" style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #e9ecef;">${fullAddress}</div>
                </div>
            `;
            
            details.innerHTML = addressHtml;
            modal.style.display = 'flex';
        }

        // إغلاق نافذة العنوان
        function closeAddressModal() {
            const modal = document.getElementById('addressModal');
            modal.style.display = 'none';
        }

        // إغلاق النافذة عند النقر خارجها
        document.getElementById('addressModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddressModal();
            }
        });

        // تأثيرات تفاعلية
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.data-table tr');
            rows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(-5px)';
                    this.style.transition = 'transform 0.2s ease';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                });
            });
        });
    </script>
</body>
</html>