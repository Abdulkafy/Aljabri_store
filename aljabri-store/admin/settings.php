<?php
// بداية الجلسة
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// الاتصال بقاعدة البيانات - استخدام include_once
include_once '../includes/config.php';

// جلب الإعدادات الحالية
$settings = getStoreSettings($conn);

// الحصول على الأعمدة الموجودة في الجدول
$existing_columns = [];
$check_columns = "SHOW COLUMNS FROM store_settings";
$columns_result = mysqli_query($conn, $check_columns);
if ($columns_result) {
    while ($row = mysqli_fetch_assoc($columns_result)) {
        $existing_columns[] = $row['Field'];
    }
}

// معالجة رفع الشعار
$logo_message = '';
$logo_success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['store_logo'])) {
    $logo_result = handleLogoUpload($_FILES['store_logo'], $conn);
    $logo_message = $logo_result['message'];
    $logo_success = $logo_result['success'];
}

// تحديث الإعدادات
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['settings'])) {
    foreach ($_POST['settings'] as $key => $value) {
        $value = mysqli_real_escape_string($conn, $value);
        
        // التحقق مما إذا كان العمود موجوداً قبل التحديث
        if (in_array($key, $existing_columns)) {
            $sql = "UPDATE store_settings SET $key = '$value' WHERE id = 1";
            mysqli_query($conn, $sql);
        }
        // إذا لم يكن العمود موجوداً، نتجاهله بدلاً من إظهار خطأ
    }
    
    header('Location: settings.php?success=تم حفظ الإعدادات بنجاح');
    exit;
}

// دالة معالجة رفع الشعار
function handleLogoUpload($file, $conn) {
    $upload_dir = '../assets/images/';
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
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
            'message' => 'حجم الشعار كبير جداً. الحد الأقصى 2MB.'
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
    
    // اسم ثابت للشعار
    $new_file_name = 'logo.' . $file_ext;
    $destination = $upload_dir . $new_file_name;
    
    // حذف الشعار القديم إذا كان موجوداً
    $old_logos = glob($upload_dir . 'logo.*');
    foreach ($old_logos as $old_logo) {
        if (is_file($old_logo)) {
            unlink($old_logo);
        }
    }
    
    // نقل الملف إلى المجلد المطلوب
    if (move_uploaded_file($file_tmp, $destination)) {
        // تحديث الإعداد في قاعدة البيانات
        $sql = "UPDATE store_settings SET store_logo = '$new_file_name' WHERE id = 1";
        mysqli_query($conn, $sql);
        
        return [
            'success' => true,
            'message' => 'تم رفع الشعار بنجاح وتحديثه في المتجر.',
            'file_name' => $new_file_name
        ];
    } else {
        return [
            'success' => false,
            'message' => 'فشل في رفع الشعار. يرجى المحاولة مرة أخرى.'
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
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإعدادات - <?php echo htmlspecialchars($settings['store_name']); ?></title>
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
        
        .settings-form {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
            font-size: 1.3rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #495057;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-actions {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #e9ecef;
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
            margin: 0 10px;
        }
        
        .btn:hover {
            background: #e55a2b;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
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
        
        .logo-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            border: 2px dashed #dee2e6;
            text-align: center;
            margin-bottom: 25px;
        }
        
        .logo-preview {
            max-width: 200px;
            max-height: 100px;
            margin: 15px auto;
            display: block;
        }
        
        .logo-upload-area {
            border: 2px dashed #adb5bd;
            border-radius: 8px;
            padding: 30px 20px;
            text-align: center;
            margin: 20px 0;
            transition: all 0.3s ease;
        }
        
        .logo-upload-area:hover {
            border-color: var(--primary-color);
            background: #f0f8ff;
        }
        
        .current-logo {
            margin: 15px 0;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .coming-soon {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 10px 15px;
            margin-top: 10px;
            color: #856404;
            font-size: 0.85rem;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- الشريط الجانبي -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo htmlspecialchars($settings['store_name']); ?></h2>
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
                        <a href="settings.php" class="active">
                            <i class="fas fa-cog"></i>
                            الإعدادات
                        </a>
                    </li>
                    <li>
                        <a href="uploads.php">
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
                    <h1><i class="fas fa-cog"></i> إعدادات المتجر</h1>
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
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($logo_message): ?>
                    <div class="alert <?php echo $logo_success ? 'alert-success' : 'alert-error'; ?>">
                        <i class="fas <?php echo $logo_success ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                        <?php echo htmlspecialchars($logo_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="settings-form" enctype="multipart/form-data">
                    <!-- قسم الشعار -->
                    <div class="form-section">
                        <h3><i class="fas fa-image"></i> شعار المتجر</h3>
                        
                        <div class="logo-section">
                            <?php if (!empty($settings['store_logo']) && file_exists('../assets/images/' . $settings['store_logo'])): ?>
                                <div class="current-logo">
                                    <strong>الشعار الحالي:</strong>
                                    <img src="../assets/images/<?php echo htmlspecialchars($settings['store_logo']); ?>" 
                                         alt="شعار المتجر" 
                                         class="logo-preview"
                                         onerror="this.style.display='none'">
                                </div>
                            <?php else: ?>
                                <p style="color: #6c757d;">لا يوجد شعار مرفوع حالياً</p>
                            <?php endif; ?>
                            
                            <div class="logo-upload-area">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #6c757d; margin-bottom: 10px;"></i>
                                <p>اسحب وأفلت الشعار هنا أو انقر لاختياره</p>
                                <input type="file" id="store_logo" name="store_logo" accept=".jpg,.jpeg,.png,.gif,.webp,.svg" style="display: none;">
                                <button type="button" class="btn" onclick="document.getElementById('store_logo').click()">
                                    <i class="fas fa-folder-open"></i>
                                    اختر شعار
                                </button>
                                <div style="margin-top: 10px;">
                                    <small style="color: #6c757d;">الأنواع المسموحة: JPG, PNG, GIF, WEBP, SVG (الحد الأقصى: 2MB)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <!-- الإعدادات العامة -->
                        <div class="form-section">
                            <h3><i class="fas fa-store"></i> الإعدادات العامة</h3>
                            
                            <div class="form-group">
                                <label for="store_name">اسم المتجر</label>
                                <input type="text" id="store_name" name="settings[store_name]" value="<?php echo htmlspecialchars($settings['store_name'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="primary_color">اللون الأساسي</label>
                                <input type="color" id="primary_color" name="settings[primary_color]" value="<?php echo htmlspecialchars($settings['primary_color'] ?? '#FF6B35'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="secondary_color">اللون الثانوي</label>
                                <input type="color" id="secondary_color" name="settings[secondary_color]" value="<?php echo htmlspecialchars($settings['secondary_color'] ?? '#2C3E50'); ?>">
                            </div>
                        </div>

                        <!-- معلومات المتجر -->
                        <div class="form-section">
                            <h3><i class="fas fa-info-circle"></i> معلومات المتجر</h3>
                            
                            <div class="form-group">
                                <label for="store_address">عنوان المتجر</label>
                                <textarea id="store_address" name="settings[store_address]"><?php echo htmlspecialchars($settings['store_address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="store_phone">رقم الهاتف</label>
                                <input type="text" id="store_phone" name="settings[store_phone]" value="<?php echo htmlspecialchars($settings['store_phone'] ?? ''); ?>">
                            </div>
                            
                            <?php if (in_array('store_whatsapp', $existing_columns)): ?>
                            <div class="form-group">
                                <label for="store_whatsapp">رقم الواتساب</label>
                                <input type="text" id="store_whatsapp" name="settings[store_whatsapp]" value="<?php echo htmlspecialchars($settings['store_whatsapp'] ?? ''); ?>">
                            </div>
                            <?php endif; ?>
                            
                            <?php if (in_array('store_email', $existing_columns)): ?>
                            <div class="form-group">
                                <label for="store_email">البريد الإلكتروني</label>
                                <input type="email" id="store_email" name="settings[store_email]" value="<?php echo htmlspecialchars($settings['store_email'] ?? ''); ?>">
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- التحكم في الصور -->
                        <div class="form-section">
                            <h3><i class="fas fa-images"></i> التحكم في الصور</h3>
                            
                            <?php if (in_array('product_image_width', $existing_columns)): ?>
                            <div class="form-group">
                                <label for="product_image_width">عرض صور المنتجات (بكسل)</label>
                                <input type="number" id="product_image_width" name="settings[product_image_width]" 
                                       value="<?php echo htmlspecialchars($settings['product_image_width'] ?? '300'); ?>" min="100" max="800">
                            </div>
                            <?php else: ?>
                            <div class="coming-soon">
                                <i class="fas fa-info-circle"></i> خاصية عرض صور المنتجات قيد التطوير
                            </div>
                            <?php endif; ?>
                            
                            <?php if (in_array('product_image_height', $existing_columns)): ?>
                            <div class="form-group">
                                <label for="product_image_height">ارتفاع صور المنتجات (بكسل)</label>
                                <input type="number" id="product_image_height" name="settings[product_image_height]" 
                                       value="<?php echo htmlspecialchars($settings['product_image_height'] ?? '300'); ?>" min="100" max="800">
                            </div>
                            <?php else: ?>
                            <div class="coming-soon">
                                <i class="fas fa-info-circle"></i> خاصية ارتفاع صور المنتجات قيد التطوير
                            </div>
                            <?php endif; ?>
                            
                            <?php if (in_array('image_quality', $existing_columns)): ?>
                            <div class="form-group">
                                <label for="image_quality">جودة الصور (%)</label>
                                <input type="range" id="image_quality" name="settings[image_quality]" 
                                       value="<?php echo htmlspecialchars($settings['image_quality'] ?? '85'); ?>" min="50" max="100" step="5">
                                <output for="image_quality" id="quality_output"><?php echo htmlspecialchars($settings['image_quality'] ?? '85'); ?>%</output>
                            </div>
                            <?php else: ?>
                            <div class="coming-soon">
                                <i class="fas fa-info-circle"></i> خاصية جودة الصور قيد التطوير
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- الإعلانات والإشعارات -->
                        <div class="form-section">
                            <h3><i class="fas fa-bullhorn"></i> الإعلانات والإشعارات</h3>
                            
                            <?php if (in_array('announcement_text', $existing_columns)): ?>
                            <div class="form-group">
                                <label for="announcement_text">نص الإعلان المتحرك</label>
                                <textarea id="announcement_text" name="settings[announcement_text]" placeholder="أضف إعلانات متعددة مفصولة بفاصلة"><?php echo htmlspecialchars($settings['announcement_text'] ?? ''); ?></textarea>
                                <small>يمكن استخدام فواصل للفصل بين عدة إعلانات</small>
                            </div>
                            <?php else: ?>
                            <div class="coming-soon">
                                <i class="fas fa-info-circle"></i> خاصية الإعلانات المتحركة قيد التطوير
                            </div>
                            <?php endif; ?>
                            
                            <?php if (in_array('welcome_message', $existing_columns)): ?>
                            <div class="form-group">
                                <label for="welcome_message">رسالة الترحيب</label>
                                <textarea id="welcome_message" name="settings[welcome_message]" placeholder="رسالة ترحيبية تظهر في الصفحة الرئيسية"><?php echo htmlspecialchars($settings['welcome_message'] ?? ''); ?></textarea>
                            </div>
                            <?php else: ?>
                            <div class="coming-soon">
                                <i class="fas fa-info-circle"></i> خاصية رسائل الترحيب قيد التطوير
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn">
                            <i class="fas fa-save"></i>
                            حفظ الإعدادات
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // تحديث قيمة جودة الصور
        const qualitySlider = document.getElementById('image_quality');
        if (qualitySlider) {
            const qualityOutput = document.getElementById('quality_output');
            qualitySlider.addEventListener('input', function() {
                qualityOutput.textContent = this.value + '%';
            });
        }

        // عرض معاينة الشعار عند اختياره
        document.getElementById('store_logo').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const fileName = file.name;
                
                // التحقق من حجم الملف
                if (file.size > 2 * 1024 * 1024) {
                    alert('حجم الشعار كبير جداً. الحد الأقصى 2MB.');
                    this.value = '';
                    return;
                }
                
                // التحقق من نوع الملف
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
                if (!allowedTypes.includes(file.type)) {
                    alert('نوع الملف غير مسموح. يرجى اختيار صورة بصيغة مدعومة.');
                    this.value = '';
                    return;
                }
                
                // عرض معاينة
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewArea = document.querySelector('.logo-upload-area');
                    let imgPreview = previewArea.querySelector('.logo-preview');
                    
                    if (!imgPreview) {
                        imgPreview = document.createElement('img');
                        imgPreview.className = 'logo-preview';
                        previewArea.insertBefore(imgPreview, previewArea.querySelector('button'));
                    }
                    
                    imgPreview.src = e.target.result;
                    imgPreview.style.display = 'block';
                    
                    // تحديث النص
                    const textElement = previewArea.querySelector('p');
                    textElement.textContent = 'شعار جديد: ' + fileName;
                }
                reader.readAsDataURL(file);
            }
        });

        // سحب وإفلات الشعار
        const logoUploadArea = document.querySelector('.logo-upload-area');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            logoUploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            logoUploadArea.addEventListener(eventName, () => {
                logoUploadArea.style.borderColor = 'var(--primary-color)';
                logoUploadArea.style.background = '#f0f8ff';
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            logoUploadArea.addEventListener(eventName, () => {
                logoUploadArea.style.borderColor = '#adb5bd';
                logoUploadArea.style.background = '';
            }, false);
        });

        logoUploadArea.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            document.getElementById('store_logo').files = files;
            
            // تشغيل حدث change يدوياً
            const event = new Event('change');
            document.getElementById('store_logo').dispatchEvent(event);
        });
    </script>
</body>
</html>