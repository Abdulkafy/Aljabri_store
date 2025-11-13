<?php
// بداية الجلسة
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// الاتصال بقاعدة البيانات
include '../includes/config.php';

// جلب إعدادات المتجر
$settings = getStoreSettings($conn);

// معالجة رفع الملفات
$upload_message = '';
$upload_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploaded_file'])) {
    $upload_result = handleFileUpload($_FILES['uploaded_file'], $conn);
    $upload_message = $upload_result['message'];
    $upload_success = $upload_result['success'];
}

// دالة معالجة رفع الملفات
function handleFileUpload($file, $conn) {
    // الإعدادات
    $upload_dir = '../assets/uploads/';
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // إنشاء المجلد إذا لم يكن موجوداً
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // معلومات الملف
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    
    // التحقق من وجود أخطاء في الرفع
    if ($file_error !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => getUploadErrorMessage($file_error)
        ];
    }
    
    // التحقق من حجم الملف
    if ($file_size > $max_size) {
        return [
            'success' => false,
            'message' => 'حجم الملف كبير جداً. الحد الأقصى 5MB.'
        ];
    }
    
    // الحصول على امتداد الملف
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // التحقق من نوع الملف
    if (!in_array($file_ext, $allowed_types)) {
        return [
            'success' => false,
            'message' => 'نوع الملف غير مسموح. الأنواع المسموحة: ' . implode(', ', $allowed_types)
        ];
    }
    
    // إنشاء اسم فريد للملف
    $new_file_name = generateUniqueFileName($file_name, $upload_dir);
    $destination = $upload_dir . $new_file_name;
    
    // نقل الملف إلى المجلد المطلوب
    if (move_uploaded_file($file_tmp, $destination)) {
        // إذا كانت صورة منتج، تحديث قاعدة البيانات
        if (isset($_POST['product_id']) && !empty($_POST['product_id'])) {
            updateProductImage($_POST['product_id'], $new_file_name, $conn);
        }
        
        return [
            'success' => true,
            'message' => 'تم رفع الملف بنجاح: ' . $new_file_name,
            'file_name' => $new_file_name
        ];
    } else {
        return [
            'success' => false,
            'message' => 'فشل في رفع الملف. يرجى المحاولة مرة أخرى.'
        ];
    }
}

// دالة للحصول على رسائل الخطأ
function getUploadErrorMessage($error_code) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'حجم الملف أكبر من المسموح به في الخادم.',
        UPLOAD_ERR_FORM_SIZE => 'حجم الملف أكبر من المسموح به في النموذج.',
        UPLOAD_ERR_PARTIAL => 'تم رفع جزء من الملف فقط.',
        UPLOAD_ERR_NO_FILE => 'لم يتم اختيار أي ملف.',
        UPLOAD_ERR_NO_TMP_DIR => 'المجلد المؤقت غير موجود.',
        UPLOAD_ERR_CANT_WRITE => 'فشل في كتابة الملف على القرص.',
        UPLOAD_ERR_EXTENSION => 'رفع الملف متوقف بسبب امتداد غير مسموح.'
    ];
    
    return $errors[$error_code] ?? 'حدث خطأ غير معروف أثناء رفع الملف.';
}

// دالة لإنشاء اسم فريد للملف
function generateUniqueFileName($original_name, $upload_dir) {
    $extension = pathinfo($original_name, PATHINFO_EXTENSION);
    $name = pathinfo($original_name, PATHINFO_FILENAME);
    
    // تنظيف اسم الملف
    $clean_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
    $clean_name = substr($clean_name, 0, 100); // تقليل الطول
    
    $counter = 1;
    $new_name = $clean_name . '.' . $extension;
    
    // التأكد من أن الاسم فريد
    while (file_exists($upload_dir . $new_name)) {
        $new_name = $clean_name . '_' . $counter . '.' . $extension;
        $counter++;
    }
    
    return $new_name;
}

// دالة لتحديث صورة المنتج في قاعدة البيانات
function updateProductImage($product_id, $image_name, $conn) {
    $product_id = intval($product_id);
    $image_name = mysqli_real_escape_string($conn, $image_name);
    
    $sql = "UPDATE products SET main_image = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $image_name, $product_id);
    
    if (mysqli_stmt_execute($stmt)) {
        return true;
    }
    return false;
}

// جلب المنتجات لربط الصور
$products = [];
$products_result = mysqli_query($conn, "SELECT id, name FROM products ORDER BY name ASC");
if ($products_result && mysqli_num_rows($products_result) > 0) {
    while ($row = mysqli_fetch_assoc($products_result)) {
        $products[] = $row;
    }
}

// جلب الملفات المرفوعة مسبقاً
$uploaded_files = [];
$upload_dir = '../assets/uploads/';
if (is_dir($upload_dir)) {
    $files = scandir($upload_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && !is_dir($upload_dir . $file)) {
            $uploaded_files[] = [
                'name' => $file,
                'size' => filesize($upload_dir . $file),
                'date' => filemtime($upload_dir . $file)
            ];
        }
    }
    
    // ترتيب الملفات من الأحدث إلى الأقدم
    usort($uploaded_files, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الملفات - <?php echo $settings['store_name']; ?></title>
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
        
        .upload-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .upload-area:hover, .upload-area.dragover {
            border-color: var(--primary-color);
            background: #f0f8ff;
        }
        
        .upload-area i {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 15px;
        }
        
        .upload-area h3 {
            color: #495057;
            margin-bottom: 10px;
        }
        
        .upload-area p {
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: #e55a2b;
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }
        
        .form-group select, .form-group input[type="file"] {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 1rem;
        }
        
        .files-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .file-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .file-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .file-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .file-name {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
            word-break: break-all;
        }
        
        .file-size {
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .file-actions {
            margin-top: 10px;
            display: flex;
            gap: 5px;
            justify-content: center;
        }
        
        .file-preview {
            max-width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        
        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            margin-top: 10px;
            overflow: hidden;
            display: none;
        }
        
        .progress-fill {
            height: 100%;
            background: var(--primary-color);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
            }
            
            .files-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
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
                        <a href="uploads.php" class="active">
                            <i class="fas fa-file-upload"></i>
                            الملفات
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
                    <h1><i class="fas fa-file-upload"></i> إدارة الملفات</h1>
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
                <?php if ($upload_message): ?>
                    <div class="alert <?php echo $upload_success ? 'alert-success' : 'alert-error'; ?>">
                        <i class="fas <?php echo $upload_success ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                        <?php echo $upload_message; ?>
                    </div>
                <?php endif; ?>

                <!-- قسم رفع الملفات -->
                <div class="upload-section">
                    <h2><i class="fas fa-cloud-upload-alt"></i> رفع ملف جديد</h2>
                    
                    <form id="uploadForm" method="POST" enctype="multipart/form-data">
                        <div class="upload-area" id="uploadArea">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <h3>اسحب وأفلت الملفات هنا</h3>
                            <p>أو انقر لاختيار الملفات</p>
                            <input type="file" id="fileInput" name="uploaded_file" style="display: none;" accept=".jpg,.jpeg,.png,.gif,.webp,.svg">
                            <button type="button" class="btn" onclick="document.getElementById('fileInput').click()">
                                <i class="fas fa-folder-open"></i>
                                اختر ملف
                            </button>
                            <div class="progress-bar" id="progressBar">
                                <div class="progress-fill" id="progressFill"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="product_id"><i class="fas fa-link"></i> ربط بصورة منتج (اختياري)</label>
                            <select id="product_id" name="product_id">
                                <option value="">-- اختر منتج --</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['id']; ?>"><?php echo $product['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn" id="uploadBtn">
                            <i class="fas fa-upload"></i>
                            رفع الملف
                        </button>
                    </form>
                    
                    <div style="margin-top: 15px;">
                        <p><small><i class="fas fa-info-circle"></i> الأنواع المسموحة: JPG, PNG, GIF, WEBP, SVG (الحجم الأقصى: 5MB)</small></p>
                    </div>
                </div>

                <!-- قسم الملفات المرفوعة -->
                <div class="upload-section">
                    <h2><i class="fas fa-files"></i> الملفات المرفوعة</h2>
                    
                    <?php if (!empty($uploaded_files)): ?>
                        <div class="files-grid">
                            <?php foreach ($uploaded_files as $file): 
                                $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                                $is_image = in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                $file_url = '../assets/uploads/' . $file['name'];
                                $file_size = formatFileSize($file['size']);
                            ?>
                                <div class="file-card">
                                    <?php if ($is_image): ?>
                                        <img src="<?php echo $file_url; ?>" alt="<?php echo $file['name']; ?>" class="file-preview" onerror="this.style.display='none'">
                                    <?php else: ?>
                                        <div class="file-icon">
                                            <i class="fas fa-file"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="file-name"><?php echo $file['name']; ?></div>
                                    <div class="file-size"><?php echo $file_size; ?></div>
                                    
                                    <div class="file-actions">
                                        <a href="<?php echo $file_url; ?>" target="_blank" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.8rem;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-outline" style="padding: 6px 12px; font-size: 0.8rem;" onclick="copyFileUrl('<?php echo $file_url; ?>')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="text-align: center; color: #6c757d; padding: 40px;">
                            <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                            لا توجد ملفات مرفوعة بعد
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // دالة لتنسيق حجم الملف
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // دالة نسخ رابط الملف
        function copyFileUrl(url) {
            navigator.clipboard.writeText(url).then(function() {
                alert('تم نسخ رابط الملف: ' + url);
            }, function() {
                alert('فشل في نسخ الرابط');
            });
        }

        // إدارة سحب وإفلات الملفات
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const progressBar = document.getElementById('progressBar');
        const progressFill = document.getElementById('progressFill');

        // منع السلوك الافتراضي لمنع فتح الملف في المتصفح
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // إضافة تأثيرات للسحب
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.remove('dragover');
            }, false);
        });

        // معالجة إسقاط الملفات
        uploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            updateFileName();
        }

        // تحديث اسم الملف المعروض
        fileInput.addEventListener('change', updateFileName);

        function updateFileName() {
            if (fileInput.files.length > 0) {
                const fileName = fileInput.files[0].name;
                uploadArea.querySelector('h3').textContent = 'ملف مرفوع: ' + fileName;
            }
        }

        // محاكاة شريط التقدم (للتجربة)
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const file = fileInput.files[0];
            if (!file) {
                e.preventDefault();
                alert('يرجى اختيار ملف أولاً');
                return;
            }

            // عرض شريط التقدم
            progressBar.style.display = 'block';
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 10;
                if (progress >= 100) {
                    progress = 100;
                    clearInterval(interval);
                }
                progressFill.style.width = progress + '%';
            }, 100);
        });
    </script>

    <?php
    // دالة مساعدة لتنسيق حجم الملف
    function formatFileSize($bytes) {
        if ($bytes == 0) return '0 Bytes';
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        return number_format(($bytes / pow($k, $i)), 2) . ' ' . $sizes[$i];
    }
    ?>
</body>
</html>