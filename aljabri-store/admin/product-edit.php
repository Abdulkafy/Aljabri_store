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

// إنشاء جدول الصور إذا لم يكن موجوداً
$create_images_table = "
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_main TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";
mysqli_query($conn, $create_images_table);

// دالة للتحقق من وجود صورة رئيسية
function has_main_image($conn, $product_id) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM product_images WHERE product_id = $product_id AND is_main = 1");
    if($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'] > 0;
    }
    return false;
}

// دالة لتعيين أول صورة كرئيسية
function set_first_image_as_main($conn, $product_id) {
    $result = mysqli_query($conn, "SELECT id FROM product_images WHERE product_id = $product_id ORDER BY id ASC LIMIT 1");
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        mysqli_query($conn, "UPDATE product_images SET is_main = 1 WHERE id = {$row['id']}");
    }
}

// جلب بيانات المنتج إذا كان هناك معرف
$product = [];
$product_id = $_GET['id'] ?? 0;

if($product_id){
    $result = mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id");
    if($result && mysqli_num_rows($result) > 0){
        $product = mysqli_fetch_assoc($result);
    }
}

// جلب صور المنتج إذا كان هناك معرف منتج
$product_images = [];
if($product_id) {
    $result = mysqli_query($conn, "SELECT * FROM product_images WHERE product_id = $product_id ORDER BY is_main DESC, id ASC");
    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $product_images[] = $row;
        }
    }
}

// معالجة رفع الصور
if(isset($_FILES['images']) && $product_id) {
    $upload_dir = "../../assets/uploads/";
    
    // إنشاء مجلد التحميل إذا لم يكن موجوداً
    if(!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    foreach($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            $file_name = time() . '_' . uniqid() . '_' . basename($_FILES['images']['name'][$key]);
            $file_path = $upload_dir . $file_name;
            
            if(move_uploaded_file($tmp_name, $file_path)) {
                // تحديد إذا كانت هذه الصورة الرئيسية (أول صورة يتم رفعها)
                $is_main = ($key == 0 && !has_main_image($conn, $product_id)) ? 1 : 0;
                
                $insert_sql = "INSERT INTO product_images (product_id, image_path, is_main) 
                              VALUES ($product_id, '$file_name', $is_main)";
                mysqli_query($conn, $insert_sql);
            }
        }
    }
    
    if(isset($_FILES['images']) && count($_FILES['images']['tmp_name']) > 0) {
        header("Location: product-edit.php?id=$product_id&success=تم رفع الصور بنجاح");
        exit;
    }
}

// معالجة حذف الصور
if(isset($_GET['delete_image']) && $product_id) {
    $image_id = intval($_GET['delete_image']);
    
    // جلب معلومات الصورة قبل الحذف
    $result = mysqli_query($conn, "SELECT image_path, is_main FROM product_images WHERE id = $image_id AND product_id = $product_id");
    if($result && mysqli_num_rows($result) > 0) {
        $image_data = mysqli_fetch_assoc($result);
        
        // حذف الملف من السيرفر
        $file_path = "../../assets/uploads/" . $image_data['image_path'];
        if(file_exists($file_path)) {
            unlink($file_path);
        }
        
        // حذف السجل من قاعدة البيانات
        mysqli_query($conn, "DELETE FROM product_images WHERE id = $image_id");
        
        // إذا كانت الصورة المحذوفة هي الرئيسية، جعل أول صورة أخرى رئيسية
        if($image_data['is_main']) {
            set_first_image_as_main($conn, $product_id);
        }
        
        header("Location: product-edit.php?id=$product_id&success=تم حذف الصورة بنجاح");
        exit;
    }
}

// معالجة تعيين صورة رئيسية
if(isset($_GET['set_main']) && $product_id) {
    $image_id = intval($_GET['set_main']);
    
    // إلغاء تعيين جميع الصور كرئيسية
    mysqli_query($conn, "UPDATE product_images SET is_main = 0 WHERE product_id = $product_id");
    
    // تعيين الصورة المحددة كرئيسية
    mysqli_query($conn, "UPDATE product_images SET is_main = 1 WHERE id = $image_id AND product_id = $product_id");
    
    header("Location: product-edit.php?id=$product_id&success=تم تعيين الصورة الرئيسية بنجاح");
    exit;
}

// معالجة حفظ المنتج
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price_yer = floatval($_POST['price_yer']);
    $price_sar = floatval($_POST['price_sar']);
    $price_usd = floatval($_POST['price_usd']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    if($product_id){
        // تحديث المنتج
        $sql = "UPDATE products SET 
                name = '$name',
                description = '$description',
                price_yer = $price_yer,
                price_sar = $price_sar,
                price_usd = $price_usd,
                stock_quantity = $stock_quantity,
                featured = $featured,
                updated_at = NOW()
                WHERE id = $product_id";
    } else {
        // إضافة منتج جديد
        $sql = "INSERT INTO products (name, description, price_yer, price_sar, price_usd, stock_quantity, featured) 
                VALUES ('$name', '$description', $price_yer, $price_sar, $price_usd, $stock_quantity, $featured)";
    }
    
    if(mysqli_query($conn, $sql)){
        if(!$product_id){
            $product_id = mysqli_insert_id($conn);
        }
        header('Location: products.php?success=' . ($product_id ? 'تم تحديث المنتج بنجاح' : 'تم إضافة المنتج بنجاح'));
        exit;
    } else {
        $error = "خطأ في حفظ المنتج: " . mysqli_error($conn);
    }
}

// إعدادات ثابتة للمتجر (بدون الاعتماد على قاعدة البيانات)
$settings = [
    'store_name' => 'الجابري ستور',
    'primary_color' => '#FF6B35',
    'secondary_color' => '#2C3E50'
];

$page_title = ($product_id && !empty($product)) ? "تعديل المنتج" : "إضافة منتج جديد";
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo $settings['store_name']; ?></title>
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
        
        .product-form {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 25px;
        }
        
        .form-section {
            margin-bottom: 25px;
        }
        
        .form-section h3 {
            color: #2C3E50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ECF0F1;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #34495E;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input {
            width: auto;
        }
        
        .form-actions {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #ECF0F1;
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
            margin: 0 5px;
        }
        
        .btn-primary {
            background: <?php echo $settings['primary_color']; ?>;
        }
        
        .btn-secondary {
            background: #34495E;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* ستايلات قسم الصور */
        .help-text {
            color: #7f8c8d;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }

        .image-preview-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }

        .preview-image {
            position: relative;
            border-radius: 5px;
            overflow: hidden;
        }

        .preview-image img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border: 2px solid #3498db;
        }

        .remove-preview {
            position: absolute;
            top: 5px;
            left: 5px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
            font-size: 12px;
        }

        .current-images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .image-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .image-item:hover {
            transform: scale(1.05);
        }

        .image-item.main-image {
            border: 3px solid #f39c12;
        }

        .image-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .image-actions {
            position: absolute;
            top: 5px;
            left: 5px;
            display: flex;
            gap: 5px;
        }

        .set-main-btn,
        .delete-image-btn {
            background: rgba(0,0,0,0.7);
            color: white;
            border: none;
            border-radius: 3px;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 12px;
            cursor: pointer;
        }

        .set-main-btn:hover {
            background: #f39c12;
        }

        .delete-image-btn:hover {
            background: #e74c3c;
        }

        .main-badge {
            background: #f39c12;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        /* تحسين التصميم للشاشات الصغيرة */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .current-images-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
            
            .image-preview-container {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
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
                    <h1><?php echo $page_title; ?></h1>
                </div>
                <div class="header-right">
                    <span class="admin-name">مرحباً، <?php echo $_SESSION['admin_name'] ?? 'مدير النظام'; ?></span>
                    <a href="../index.php" target="_blank" class="view-store">عرض المتجر</a>
                </div>
            </header>

            <!-- محتوى الصفحة -->
            <div class="page-content">
                <?php if(isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
                <?php endif; ?>

                <form method="POST" class="product-form" enctype="multipart/form-data">
                    <div class="form-grid">
                        <!-- المعلومات الأساسية -->
                        <div class="form-section">
                            <h3>المعلومات الأساسية</h3>
                            
                            <div class="form-group">
                                <label for="name">اسم المنتج *</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">وصف المنتج</label>
                                <textarea id="description" name="description" placeholder="أدخل وصفاً مفصلاً للمنتج..."><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="featured" name="featured" <?php echo ($product['featured'] ?? 0) ? 'checked' : ''; ?>>
                                <label for="featured">منتج مميز (سيظهر في الصفحة الرئيسية)</label>
                            </div>
                        </div>

                        <!-- الأسعار والمخزون -->
                        <div class="form-section">
                            <h3>الأسعار والمخزون</h3>
                            
                            <div class="form-group">
                                <label for="price_yer">السعر (ريال يمني) *</label>
                                <input type="number" id="price_yer" name="price_yer" step="0.01" min="0" value="<?php echo $product['price_yer'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="price_sar">السعر (ريال سعودي) *</label>
                                <input type="number" id="price_sar" name="price_sar" step="0.01" min="0" value="<?php echo $product['price_sar'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="price_usd">السعر (دولار أمريكي) *</label>
                                <input type="number" id="price_usd" name="price_usd" step="0.01" min="0" value="<?php echo $product['price_usd'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="stock_quantity">الكمية في المخزون *</label>
                                <input type="number" id="stock_quantity" name="stock_quantity" min="0" value="<?php echo $product['stock_quantity'] ?? 0; ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- قسم الصور -->
                    <div class="form-section">
                        <h3>صور المنتج</h3>
                        
                        <!-- رفع صور متعددة -->
                        <div class="form-group">
                            <label for="images">رفع صور المنتج</label>
                            <input type="file" id="images" name="images[]" multiple accept="image/*" 
                                   onchange="previewImages(this)">
                            <small class="help-text">يمكنك اختيار أكثر من صورة باستخدام Ctrl+Click</small>
                            
                            <!-- معاينة الصور قبل الرفع -->
                            <div id="image-preview" class="image-preview-container"></div>
                        </div>
                        
                        <!-- عرض الصور الحالية -->
                        <?php if(!empty($product_images)): ?>
                        <div class="form-group">
                            <label>الصور الحالية:</label>
                            <div class="current-images-grid">
                                <?php foreach($product_images as $image): ?>
                                <div class="image-item <?php echo $image['is_main'] ? 'main-image' : ''; ?>">
                                    <img src="../../assets/uploads/<?php echo $image['image_path']; ?>" 
                                         alt="صورة المنتج">
                                    <div class="image-actions">
                                        <?php if(!$image['is_main']): ?>
                                            <a href="?id=<?php echo $product_id; ?>&set_main=<?php echo $image['id']; ?>" 
                                               class="set-main-btn" title="تعيين كصورة رئيسية">⭐</a>
                                        <?php else: ?>
                                            <span class="main-badge">رئيسية</span>
                                        <?php endif; ?>
                                        <a href="?id=<?php echo $product_id; ?>&delete_image=<?php echo $image['id']; ?>" 
                                           class="delete-image-btn" 
                                           onclick="return confirm('هل أنت متأكد من حذف هذه الصورة؟')" 
                                           title="حذف الصورة">❌</a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">💾 حفظ المنتج</button>
                        <a href="products.php" class="btn btn-secondary">❌ إلغاء</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // معاينة الصور قبل الرفع
        function previewImages(input) {
            const previewContainer = document.getElementById('image-preview');
            previewContainer.innerHTML = '';
            
            if (input.files) {
                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'preview-image';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        
                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'remove-preview';
                        removeBtn.innerHTML = '×';
                        removeBtn.onclick = function() {
                            previewDiv.remove();
                        };
                        
                        previewDiv.appendChild(img);
                        previewDiv.appendChild(removeBtn);
                        previewContainer.appendChild(previewDiv);
                    }
                    
                    reader.readAsDataURL(file);
                });
            }
        }

        // تأكيد الحذف
        document.querySelectorAll('.delete-image-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if(!confirm('هل أنت متأكد من حذف هذه الصورة؟ لا يمكن التراجع عن هذا الإجراء.')) {
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