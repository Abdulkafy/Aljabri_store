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

if (!$conn) {
    die('فشل الاتصال بقاعدة البيانات: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

// إنشاء جدول العملاء إذا لم يكن موجوداً
$create_customers_table = "
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";
mysqli_query($conn, $create_customers_table);

// معالجة حذف العميل
if(isset($_GET['delete'])){
    $customer_id = intval($_GET['delete']);
    $delete_sql = "DELETE FROM customers WHERE id = $customer_id";
    if(mysqli_query($conn, $delete_sql)) {
        header('Location: customers.php?success=تم حذف العميل بنجاح');
        exit;
    } else {
        $error = "خطأ في حذف العميل: " . mysqli_error($conn);
    }
}

// جلب جميع العملاء
$sql = "SELECT * FROM customers ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
$customers = [];

if($result && mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        $customers[] = $row;
    }
}

// إعدادات ثابتة للمتجر
$settings = [
    'store_name' => 'الجابري ستور',
    'primary_color' => '#FF6B35',
    'secondary_color' => '#2C3E50'
];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة العملاء - <?php echo $settings['store_name']; ?></title>
    <style>
        body {
            font-family: 'Cairo', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
            direction: rtl;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: <?php echo $settings['secondary_color']; ?>;
            color: white;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #34495E;
            text-align: center;
        }
        
        .sidebar-header h2 {
            color: <?php echo $settings['primary_color']; ?>;
            margin-bottom: 5px;
        }
        
        .sidebar-nav ul {
            list-style: none;
            padding: 20px 0;
        }
        
        .sidebar-nav li {
            margin-bottom: 5px;
        }
        
        .sidebar-nav a {
            display: block;
            padding: 12px 20px;
            color: #ECF0F1;
            text-decoration: none;
            transition: all 0.3s;
            border-right: 3px solid transparent;
        }
        
        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: #34495E;
            color: white;
            border-right-color: <?php echo $settings['primary_color']; ?>;
        }
        
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .top-header {
            background: white;
            padding: 15px 25px;
            border-bottom: 1px solid #ECF0F1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-name {
            color: #2C3E50;
            font-weight: bold;
        }
        
        .view-store {
            background: <?php echo $settings['primary_color']; ?>;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
        }
        
        .page-content {
            padding: 25px;
            flex: 1;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: <?php echo $settings['primary_color']; ?>;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: <?php echo $settings['primary_color']; ?>;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th,
        .data-table td {
            padding: 15px;
            text-align: right;
            border-bottom: 1px solid #ECF0F1;
        }
        
        .data-table th {
            background: #ECF0F1;
            color: #2C3E50;
            font-weight: bold;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn-edit {
            background: #3498db;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
        }
        
        .btn-view {
            background: #2ecc71;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .no-customers {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .customer-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: <?php echo $settings['primary_color']; ?>;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .customer-stats {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
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
                        <a href="index.php">
                            📊 الإحصائيات
                        </a>
                    </li>
                    <li>
                        <a href="products.php">
                            🛍️ المنتجات
                        </a>
                    </li>
                    <li>
                        <a href="orders.php">
                            📦 الطلبات
                        </a>
                    </li>
                    <li>
                        <a href="customers.php" class="active">
                            👥 العملاء
                        </a>
                    </li>
                    <li>
                        <a href="settings.php">
                            ⚙️ الإعدادات
                        </a>
                    </li>
                    <li>
                        <a href="logout.php" style="color: #e74c3c;">
                            🚪 تسجيل الخروج
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
                    <h1>إدارة العملاء</h1>
                </div>
                <div class="header-right">
                    <span class="admin-name">مرحباً، <?php echo $_SESSION['admin_name'] ?? 'مدير النظام'; ?></span>
                    <a href="../index.php" target="_blank" class="view-store">عرض المتجر</a>
                </div>
            </header>

            <!-- محتوى الصفحة -->
            <div class="page-content">
                <!-- إحصائيات العملاء -->
                <div class="customer-stats">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($customers); ?></div>
                        <div class="stat-label">إجمالي العملاء</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count(array_filter($customers, function($c) { return !empty($c['email']); })); ?></div>
                        <div class="stat-label">عملاء مسجلين</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count(array_filter($customers, function($c) { return !empty($c['phone']); })); ?></div>
                        <div class="stat-label">عملاء بهاتف</div>
                    </div>
                </div>

                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
                <?php endif; ?>

                <?php if(isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="table-container">
                    <?php if(!empty($customers)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>اسم العميل</th>
                                <th>البريد الإلكتروني</th>
                                <th>رقم الهاتف</th>
                                <th>العنوان</th>
                                <th>تاريخ التسجيل</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($customers as $customer): ?>
                            <tr>
                                <td><?php echo $customer['id']; ?></td>
                                <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                <td><?php echo htmlspecialchars($customer['email'] ?? 'لا يوجد'); ?></td>
                                <td><?php echo htmlspecialchars($customer['phone'] ?? 'لا يوجد'); ?></td>
                                <td><?php echo htmlspecialchars($customer['address'] ?? 'لا يوجد'); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($customer['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="customer-view.php?id=<?php echo $customer['id']; ?>" class="btn-view">عرض</a>
                                        <a href="customer-edit.php?id=<?php echo $customer['id']; ?>" class="btn-edit">تعديل</a>
                                        <a href="customers.php?delete=<?php echo $customer['id']; ?>" class="btn-delete" onclick="return confirm('هل أنت متأكد من حذف هذا العميل؟')">حذف</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-customers">
                        <p>لا توجد عملاء مسجلين حتى الآن.</p>
                        <p>سيظهر العملاء هنا عند تسجيلهم في المتجر.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // تأكيد الحذف
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function(e) {
                if(!confirm('هل أنت متأكد من حذف هذا العميل؟ لا يمكن التراجع عن هذا الإجراء.')) {
                    e.preventDefault();
                }
            });
        });
    </script>

    <?php
    // إغلاق الاتصال بقاعدة البيانات
    mysqli_close($conn);
    ?>
</body>
</html>