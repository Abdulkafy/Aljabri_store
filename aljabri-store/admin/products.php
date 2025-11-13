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

// معالجة حذف المنتج
if(isset($_GET['delete'])){
    $product_id = intval($_GET['delete']);
    
    // بداية transaction
    mysqli_begin_transaction($conn);
    
    try {
        // 1. حذف العناصر المرتبطة في order_items أولاً
        $delete_order_items = "DELETE FROM order_items WHERE product_id = $product_id";
        if(!mysqli_query($conn, $delete_order_items)) {
            throw new Exception("خطأ في حذف العناصر المرتبطة: " . mysqli_error($conn));
        }
        
        // 2. حذف صور المنتج من قاعدة البيانات
        $delete_images = "DELETE FROM product_images WHERE product_id = $product_id";
        mysqli_query($conn, $delete_images);
        
        // 3. حذف الملفات الفعلية للصور من السيرفر
        $result = mysqli_query($conn, "SELECT image_path FROM product_images WHERE product_id = $product_id");
        if($result && mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $file_path = "../../assets/uploads/" . $row['image_path'];
                if(file_exists($file_path)) {
                    unlink($file_path);
                }
            }
        }
        
        // 4. الآن يمكن حذف المنتج
        $delete_product = "DELETE FROM products WHERE id = $product_id";
        if(!mysqli_query($conn, $delete_product)) {
            throw new Exception("خطأ في حذف المنتج: " . mysqli_error($conn));
        }
        
        // تأكيد العملية
        mysqli_commit($conn);
        header('Location: products.php?success=تم حذف المنتج وجميع البيانات المرتبطة به بنجاح');
        exit;
        
    } catch (Exception $e) {
        // التراجع عن العملية في حالة الخطأ
        mysqli_rollback($conn);
        $error = $e->getMessage();
    }
}

// جلب جميع المنتجات
$sql = "SELECT * FROM products ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
$products = [];

if($result && mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        $products[] = $row;
    }
}

// إعدادات ثابتة للمتجر (بدون الاعتماد على قاعدة البيانات)
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
    <title>إدارة المنتجات - <?php echo $settings['store_name']; ?></title>
    <style>
        /* نفس الستايل السابق... */
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
        
        .no-products {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .no-products a {
            color: <?php echo $settings['primary_color']; ?>;
            text-decoration: none;
            font-weight: bold;
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
                        <a href="products.php" class="active">
                            🛍️ المنتجات
                        </a>
                    </li>
                    <li>
                        <a href="orders.php">
                            📦 الطلبات
                        </a>
                    </li>
                    <li>
                        <a href="customers.php">
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
                    <h1>إدارة المنتجات</h1>
                </div>
                <div class="header-right">
                    <span class="admin-name">مرحباً، <?php echo $_SESSION['admin_name'] ?? 'مدير النظام'; ?></span>
                    <a href="../index.php" target="_blank" class="view-store">عرض المتجر</a>
                </div>
            </header>

            <!-- محتوى الصفحة -->
            <div class="page-content">
                <div class="page-header">
                    <a href="product-edit.php" class="btn btn-primary">➕ إضافة منتج جديد</a>
                </div>

                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
                <?php endif; ?>

                <?php if(isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="table-container">
                    <?php if(!empty($products)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>اسم المنتج</th>
                                <th>السعر (ريال يمني)</th>
                                <th>المخزون</th>
                                <th>مميز</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($products as $product): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo number_format($product['price_yer']); ?></td>
                                <td><?php echo $product['stock_quantity'] ?? 0; ?></td>
                                <td><?php echo $product['featured'] ? 'نعم' : 'لا'; ?></td>
                                <td><?php echo date('Y-m-d', strtotime($product['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="btn-edit">تعديل</a>
                                        <a href="products.php?delete=<?php echo $product['id']; ?>" class="btn-delete" onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟ سيتم حذف جميع الطلبات والصور المرتبطة به.')">حذف</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-products">
                        <p>لا توجد منتجات في المتجر حالياً.</p>
                        <p><a href="product-edit.php">اضغط هنا لإضافة منتج جديد</a></p>
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
                if(!confirm('هل أنت متأكد من الحذف؟ سيتم حذف المنتج وجميع الطلبات والصور المرتبطة به. لا يمكن التراجع عن هذا الإجراء.')) {
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