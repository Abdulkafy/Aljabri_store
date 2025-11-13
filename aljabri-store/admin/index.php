<?php
// بداية الجلسة
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// الاتصال المباشر بقاعدة البيانات
$conn = mysqli_connect('localhost', 'root', '', 'aljabri_store');

// التحقق من الاتصال
if (!$conn) {
    die('فشل الاتصال بقاعدة البيانات: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

// جلب الإحصائيات
$products_count = 0;
$orders_count = 0;
$pending_orders = 0;
$total_revenue = 0;

// عدد المنتجات - بدون شرط status
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM products");
if ($result) {
    $products_count = mysqli_fetch_assoc($result)['count'];
}

// عدد الطلبات (إذا كان الجدول موجوداً)
$result = mysqli_query($conn, "SHOW TABLES LIKE 'orders'");
if ($result && mysqli_num_rows($result) > 0) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders");
    if ($result) {
        $orders_count = mysqli_fetch_assoc($result)['count'];
    }
    
    // التحقق من وجود عمود status قبل استخدامه
    $check_status_column = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'status'");
    if ($check_status_column && mysqli_num_rows($check_status_column) > 0) {
        $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
        if ($result) {
            $pending_orders = mysqli_fetch_assoc($result)['count'];
        }
        
        // إجمالي المبيعات - استخدام total بدلاً من total_amount
        $result = mysqli_query($conn, "SELECT SUM(total) as total FROM orders WHERE status = 'delivered'");
        if ($result) {
            $revenue_data = mysqli_fetch_assoc($result);
            $total_revenue = $revenue_data['total'] ?? 0;
        }
    }
}

// جلب إعدادات المتجر - التعديل على الاستعلام
$settings = [];
$result = mysqli_query($conn, "SELECT * FROM store_settings LIMIT 1");
if ($result && mysqli_num_rows($result) > 0) {
    $settings = mysqli_fetch_assoc($result);
} else {
    // إعدادات افتراضية
    $settings = [
        'store_name' => 'الجابري ستور',
        'primary_color' => '#FF6B35',
        'secondary_color' => '#2C3E50'
    ];
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - <?php echo $settings['store_name']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: <?php echo $settings['primary_color']; ?>;
            --secondary-color: <?php echo $settings['secondary_color']; ?>;
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
            border-top: 4px solid var(--primary-color);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 1.8rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
            display: block;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .table-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .table-container h3 {
            margin: 0 0 20px 0;
            color: #2c3e50;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .action-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .action-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            text-decoration: none;
            color: inherit;
        }
        
        .action-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 1.5rem;
        }
        
        .action-card h4 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        
        .action-card p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .no-data i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- الشريط الجانبي -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo $settings['store_name']; ?></h2>
                <p>لوحة التحكم</p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="index.php" class="active">
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
                        <a href="orders.php">
                            <i class="fas fa-shopping-cart"></i>
                            الطلبات
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
                    <h1><i class="fas fa-tachometer-alt"></i> لوحة التحكم</h1>
                </div>
                <div class="header-right">
                    <span class="admin-name"><i class="fas fa-user-circle"></i> <?php echo $_SESSION['admin_name'] ?? 'مدير النظام'; ?></span>
                    <a href="../index.php" target="_blank" class="view-store">
                        <i class="fas fa-external-link-alt"></i> عرض المتجر
                    </a>
                </div>
            </header>

            <!-- محتوى الصفحة -->
            <div class="page-content">
                <!-- بطاقات الإحصائيات -->
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <span class="stat-number"><?php echo number_format($products_count); ?></span>
                        <div class="stat-label">إجمالي المنتجات</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <span class="stat-number"><?php echo number_format($orders_count); ?></span>
                        <div class="stat-label">إجمالي الطلبات</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <span class="stat-number"><?php echo number_format($pending_orders); ?></span>
                        <div class="stat-label">طلبات قيد الانتظار</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span class="stat-number"><?php echo number_format($total_revenue); ?></span>
                        <div class="stat-label">إجمالي المبيعات (ريال)</div>
                    </div>
                </div>

                <!-- المنتجات الأخيرة -->
                <div class="table-container">
                    <h3><i class="fas fa-boxes"></i> أحدث المنتجات</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>اسم المنتج</th>
                                <th>السعر (ريال يمني)</th>
                                <th>المخزون</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC LIMIT 5");
                            
                            if($result && mysqli_num_rows($result) > 0){
                                while($row = mysqli_fetch_assoc($result)){
                                    $status = ($row['stock_quantity'] ?? 0) > 0 ? 'متوفر' : 'نفذت الكمية';
                                    $status_class = ($row['stock_quantity'] ?? 0) > 0 ? 'status-available' : 'status-out-of-stock';
                                    
                                    echo '
                                    <tr>
                                        <td><strong>' . htmlspecialchars($row['name']) . '</strong></td>
                                        <td>' . number_format($row['price_yer'] ?? 0) . '</td>
                                        <td>' . ($row['stock_quantity'] ?? 0) . '</td>
                                        <td><span class="' . $status_class . '">' . $status . '</span></td>
                                        <td>' . date('Y-m-d', strtotime($row['created_at'])) . '</td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="5" style="text-align: center; padding: 40px; color: #6c757d;">
                                        <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5; display: block;"></i>
                                        لا توجد منتجات
                                    </td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- الإجراءات السريعة -->
                <div class="quick-actions">
                    <a href="products.php?action=add" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h4>إضافة منتج جديد</h4>
                        <p>إضافة منتج جديد إلى المتجر</p>
                    </a>
                    
                    <a href="products.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <h4>عرض جميع المنتجات</h4>
                        <p>إدارة منتجات المتجر</p>
                    </a>
                    
                    <a href="orders.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h4>إدارة الطلبات</h4>
                        <p>عرض وإدارة طلبات العملاء</p>
                    </a>
                    
                    <a href="settings.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h4>إعدادات المتجر</h4>
                        <p>تعديل إعدادات المتجر</p>
                    </a>
                </div>
            </div>
        </main>
    </div>

    <style>
        .status-available {
            background: #d4edda;
            color: #155724;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-out-of-stock {
            background: #f8d7da;
            color: #721c24;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>

    <script>
        // إضافة تأثيرات تفاعلية
        document.addEventListener('DOMContentLoaded', function() {
            // تأثير عند التمرير على البطاقات
            const cards = document.querySelectorAll('.stat-card, .action-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>

    <?php
    // إغلاق الاتصال بقاعدة البيانات
    mysqli_close($conn);
    ?>
</body>
</html>