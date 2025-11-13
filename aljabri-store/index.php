<?php
session_start();
include 'includes/config.php';

// التحقق من اتصال قاعدة البيانات
if (!$conn) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

// جلب إعدادات المتجر
$settings = getStoreSettings($conn);

// التحقق من وجود عمود status في جدول المنتجات
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'status'");
$has_status_column = mysqli_num_rows($check_column) > 0;

// جلب المنتجات المميزة مع معالجة الأخطاء
$featured_products = [];

// بناء الاستعلام بناءً على وجود العمود status
if ($has_status_column) {
    $sql = "SELECT * FROM products WHERE featured = 1 AND stock_quantity > 0 AND status = 'active' ORDER BY created_at DESC LIMIT 8";
} else {
    $sql = "SELECT * FROM products WHERE featured = 1 AND stock_quantity > 0 ORDER BY created_at DESC LIMIT 8";
}

$result = mysqli_query($conn, $sql);

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $featured_products[] = $row;
        }
    }
} else {
    error_log("خطأ في استعلام المنتجات المميزة: " . mysqli_error($conn));
}

// إذا لم توجد منتجات مميزة، جلب أحدث المنتجات
if (empty($featured_products)) {
    if ($has_status_column) {
        $sql = "SELECT * FROM products WHERE stock_quantity > 0 AND status = 'active' ORDER BY created_at DESC LIMIT 6";
    } else {
        $sql = "SELECT * FROM products WHERE stock_quantity > 0 ORDER BY created_at DESC LIMIT 6";
    }
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $featured_products[] = $row;
        }
    }
}

// تنظيف البيانات قبل العرض
function cleanData($data) {
    if (is_array($data)) {
        return array_map('cleanData', $data);
    }
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

// تنظيف الإعدادات
$settings = cleanData($settings);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $settings['welcome_message']; ?>">
    <title><?php echo $settings['store_name']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-color: <?php echo $settings['primary_color']; ?>;
            --secondary-color: <?php echo $settings['secondary_color']; ?>;
            --accent-color: #FF6B35;
            --text-dark: #2C3E50;
            --text-light: #6C757D;
            --bg-light: #F8F9FA;
            --border-color: #E9ECEF;
            --shadow: 0 4px 15px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Cairo', Tahoma, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background: var(--bg-light);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* شريط الإعلانات */
        .announcement-bar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 10px 0;
            overflow: hidden;
            position: relative;
        }

        .announcement-content {
            display: flex;
            animation: scrollAnnouncement 30s linear infinite;
            white-space: nowrap;
        }

        .announcement-content span {
            padding: 0 40px;
            font-weight: 600;
            font-size: 14px;
        }

        @keyframes scrollAnnouncement {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }

        /* الهيدر */
        .header {
            background: white;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            flex-wrap: wrap;
        }

        .logo h1 {
            color: var(--primary-color);
            font-size: 28px;
            font-weight: 700;
        }

        .logo-image {
            max-height: 50px;
            width: auto;
        }

        .nav {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .nav ul {
            display: flex;
            list-style: none;
            gap: 30px;
            flex-wrap: wrap;
        }

        .nav a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 16px;
            transition: var(--transition);
            padding: 8px 0;
            position: relative;
        }

        .nav a:hover {
            color: var(--primary-color);
        }

        .nav a::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: var(--transition);
        }

        .nav a:hover::after {
            width: 100%;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .currency-selector select {
            padding: 8px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: white;
            font-size: 14px;
            cursor: pointer;
        }

        .cart-icon a {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 600;
            padding: 8px 15px;
            border-radius: 6px;
            transition: var(--transition);
            background: var(--bg-light);
        }

        .cart-icon a:hover {
            background: var(--primary-color);
            color: white;
        }

        /* قسم الهيرو */
        .hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }

        .hero .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }

        .hero-content h2 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .hero-features {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .hero-features h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .hero-features p {
            font-size: 1rem;
            opacity: 0.8;
        }

        /* الأزرار */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            justify-content: center;
        }

        .btn-primary {
            background: white;
            color: var(--primary-color);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
        }

        .btn-secondary:hover {
            background: white;
            color: var(--primary-color);
        }

        /* قسم المنتجات المميزة */
        .featured-products {
            padding: 80px 0;
            background: white;
        }

        .featured-products h2 {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 50px;
            color: var(--text-dark);
            position: relative;
        }

        .featured-products h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            right: 50%;
            transform: translateX(50%);
            width: 80px;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        /* بطاقة المنتج */
        .product-card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
            position: relative;
            border: 1px solid var(--border-color);
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .product-image {
            position: relative;
            height: 250px;
            overflow: hidden;
            background: var(--bg-light);
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--accent-color);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 2;
        }

        .product-info {
            padding: 25px;
        }

        .product-info h3 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text-dark);
            line-height: 1.4;
        }

        .product-description {
            color: var(--text-light);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price {
            margin-bottom: 20px;
        }

        .price-yer, .price-sar, .price-usd {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .product-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-add-to-cart {
            background: var(--primary-color);
            color: white;
            border: none;
        }

        .btn-add-to-cart:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-details {
            background: transparent;
            color: var(--text-dark);
            border: 2px solid var(--border-color);
        }

        .btn-details:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        /* لا توجد منتجات */
        .no-products {
            text-align: center;
            padding: 60px 20px;
            background: var(--bg-light);
            border-radius: 15px;
            margin: 40px 0;
        }

        .no-products h3 {
            font-size: 1.8rem;
            color: var(--text-light);
            margin-bottom: 15px;
        }

        .no-products p {
            color: var(--text-light);
            margin-bottom: 25px;
        }

        /* معلومات المتجر */
        .store-info {
            background: var(--bg-light);
            padding: 60px 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }

        .info-item {
            background: white;
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
        }

        .info-item:hover {
            transform: translateY(-5px);
        }

        .info-item h3 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .info-item p {
            color: var(--text-light);
            line-height: 1.8;
        }

        .payment-methods {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }

        .payment-methods span {
            background: var(--primary-color);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* الفوتر */
        .footer {
            background: var(--text-dark);
            color: white;
            padding: 60px 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h3 {
            color: var(--primary-color);
            font-size: 1.3rem;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .footer-section p {
            color: #B0B7C3;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 10px;
        }

        .footer-section ul li a {
            color: #B0B7C3;
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-section ul li a:hover {
            color: var(--primary-color);
            padding-right: 5px;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #34495E;
            color: #B0B7C3;
        }

        /* تحسينات التجاوب */
        @media (max-width: 768px) {
            .header .container {
                flex-direction: column;
                gap: 15px;
            }

            .nav ul {
                gap: 15px;
                justify-content: center;
            }

            .hero .container {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 30px;
            }

            .hero-content h2 {
                font-size: 2rem;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
            }

            .header-actions {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .nav ul {
                gap: 10px;
            }
            
            .nav a {
                font-size: 14px;
            }
            
            .hero-content h2 {
                font-size: 1.8rem;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
        }

        /* إشعارات */
        .notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary-color);
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 10000;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.5s ease, fadeOut 0.5s ease 2.5s forwards;
        }

        @keyframes slideIn {
            from { top: -100px; opacity: 0; }
            to { top: 20px; opacity: 1; }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; visibility: hidden; }
        }

        /* تحسينات إضافية */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .out-of-stock {
            opacity: 0.7;
            position: relative;
        }

        .out-of-stock::after {
            content: 'نفذت الكمية';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255,0,0,0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- الإعلان المتحرك -->
    <div class="announcement-bar">
        <div class="announcement-content">
            <?php
            $announcements = explode('،', $settings['announcement_text']);
            foreach($announcements as $announcement) {
                if(!empty(trim($announcement))) {
                    echo '<span>✨ ' . trim($announcement) . '</span>';
                }
            }
            ?>
        </div>
    </div>

    <!-- الهيدر -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <?php
                $logo_path = "assets/images/logo.png";
                $logo_exists = false;
                
                if (!empty($settings['store_logo'])) {
                    $possible_logo = "assets/images/" . $settings['store_logo'];
                    if (file_exists($possible_logo)) {
                        $logo_path = $possible_logo;
                        $logo_exists = true;
                    }
                }
                
                if (!$logo_exists) {
                    $logo_files = glob("assets/images/logo.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);
                    if (!empty($logo_files)) {
                        $logo_path = $logo_files[0];
                        $logo_exists = true;
                    }
                }
                ?>
                
                <?php if ($logo_exists): ?>
                    <a href="index.php" class="logo-link">
                        <img src="<?php echo $logo_path; ?>" alt="<?php echo $settings['store_name']; ?>" 
                             class="logo-image" style="max-height: 50px;"
                             onerror="this.style.display='none';">
                    </a>
                <?php else: ?>
                    <h1>
                        <i class="fas fa-store"></i> 
                        <?php echo $settings['store_name']; ?>
                    </h1>
                <?php endif; ?>
            </div>
            
            <nav class="nav">
                <ul>
                    <li><a href="index.php"><i class="fas fa-home"></i> الرئيسية</a></li>
                    <li><a href="products.php"><i class="fas fa-box"></i> المنتجات</a></li>
                    <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> عربة التسوق</a></li>
                    <li><a href="#contact"><i class="fas fa-phone"></i> اتصل بنا</a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <div class="currency-selector">
                    <select id="currencySelector">
                        <option value="YER">ريال يمني</option>
                        <option value="SAR">ريال سعودي</option>
                        <option value="USD">دولار أمريكي</option>
                    </select>
                </div>
                <div class="cart-icon">
                    <a href="cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        سلة التسوق <span id="cartCount">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- قسم الهيرو -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h2>مرحباً بكم في <?php echo $settings['store_name']; ?></h2>
                <p><?php echo $settings['welcome_message']; ?></p>
                <a href="products.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i>
                    تسوق الآن
                </a>
            </div>
            <div class="hero-image">
                <div class="hero-features">
                    <h3>متجر إلكتروني متكامل</h3>
                    <p>تصميم احترافي • دفع آمن • توصيل سريع</p>
                </div>
            </div>
        </div>
    </section>

    <!-- المنتجات المميزة -->
    <section class="featured-products">
        <div class="container">
            <h2>🛍️ المنتجات المميزة</h2>
            <?php if(!empty($featured_products)): ?>
            <div class="products-grid">
                <?php foreach($featured_products as $product): 
                    $is_out_of_stock = ($product['stock_quantity'] ?? 0) <= 0;
                ?>
                <div class="product-card <?php echo $is_out_of_stock ? 'out-of-stock' : ''; ?>">
                    <div class="product-image">
                        <?php
                        $image_src = "assets/images/placeholder.jpg";
                        if(!empty($product['main_image'])) {
                            $possible_image = "assets/uploads/" . $product['main_image'];
                            if(file_exists($possible_image)) {
                                $image_src = $possible_image;
                            }
                        }
                        ?>
                        <img src="<?php echo $image_src; ?>" alt="<?php echo $product['name']; ?>" 
                             onerror="this.src='assets/images/placeholder.jpg'">
                        <?php if(($product['featured'] ?? 0) == 1): ?>
                        <div class="product-badge">مميز</div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><?php echo $product['name']; ?></h3>
                        <p class="product-description">
                            <?php 
                            $description = !empty($product['description']) ? 
                                $product['description'] : 
                                'منتج متميز من متجرنا';
                            echo mb_substr($description, 0, 80, 'UTF-8') . 
                                (mb_strlen($description, 'UTF-8') > 80 ? '...' : '');
                            ?>
                        </p>
                        <div class="product-price">
                            <span class="price-yer"><?php echo number_format($product['price_yer'] ?? 0); ?> ريال يمني</span>
                            <span class="price-sar" style="display: none;"><?php echo number_format($product['price_sar'] ?? 0); ?> ريال سعودي</span>
                            <span class="price-usd" style="display: none;">$<?php echo number_format($product['price_usd'] ?? 0, 2); ?></span>
                        </div>
                        <div class="product-actions">
                            <button class="btn btn-add-to-cart add-to-cart" 
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product-name="<?php echo $product['name']; ?>"
                                    data-product-price="<?php echo $product['price_yer'] ?? 0; ?>"
                                    <?php echo $is_out_of_stock ? 'disabled' : ''; ?>>
                                <i class="fas fa-cart-plus"></i>
                                <?php echo $is_out_of_stock ? 'نفذت الكمية' : 'إضافة إلى السلة'; ?>
                            </button>
                            <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-details">
                                <i class="fas fa-eye"></i>
                                التفاصيل
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-products">
                <h3>لا توجد منتجات مميزة حالياً</h3>
                <p>سيتم إضافة منتجات جديدة قريباً</p>
                <a href="products.php" class="btn btn-primary">
                    <i class="fas fa-boxes"></i>
                    تصفح جميع المنتجات
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- معلومات المتجر -->
    <section class="store-info" id="contact">
        <div class="container">
            <div class="info-grid">
                <div class="info-item">
                    <h3><i class="fas fa-map-marker-alt"></i> عنوان المتجر</h3>
                    <p><?php echo $settings['store_address']; ?></p>
                </div>
                <div class="info-item">
                    <h3><i class="fas fa-credit-card"></i> طرق الدفع</h3>
                    <div class="payment-methods">
                        <span>كريمي جوال</span>
                        <span>جيب</span>
                        <span>ون كاش</span>
                        <span>فلوسك</span>
                        <span>جوالي</span>
                        <span>كاش</span>
                    </div>
                </div>
                <div class="info-item">
                    <h3><i class="fas fa-phone"></i> اتصل بنا</h3>
                    <p><i class="fas fa-phone"></i> هاتف: <?php echo $settings['store_phone']; ?></p>
                    <p><i class="fab fa-whatsapp"></i> واتساب: <?php echo $settings['store_whatsapp']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-store"></i> <?php echo $settings['store_name']; ?></h3>
                    <p><?php echo $settings['welcome_message']; ?></p>
                </div>
                <div class="footer-section">
                    <h3><i class="fas fa-link"></i> روابط سريعة</h3>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-home"></i> الرئيسية</a></li>
                        <li><a href="products.php"><i class="fas fa-box"></i> المنتجات</a></li>
                        <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> عربة التسوق</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3><i class="fas fa-info-circle"></i> معلومات الاتصال</h3>
                    <p><i class="fas fa-phone"></i> <?php echo $settings['store_phone']; ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo $settings['store_address']; ?></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo $settings['store_name']; ?>. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>

    <script>
        // إدارة العملة
        document.addEventListener('DOMContentLoaded', function() {
            const currencySelector = document.getElementById('currencySelector');
            const savedCurrency = localStorage.getItem('selectedCurrency') || 'YER';
            currencySelector.value = savedCurrency;
            updatePrices(savedCurrency);
            
            currencySelector.addEventListener('change', function() {
                const selectedCurrency = this.value;
                localStorage.setItem('selectedCurrency', selectedCurrency);
                updatePrices(selectedCurrency);
            });
            
            updateCartCount();
            
            // إضافة إلى السلة
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', function() {
                    if (this.disabled) return;
                    
                    const productId = this.getAttribute('data-product-id');
                    const productName = this.getAttribute('data-product-name');
                    const productPrice = this.getAttribute('data-product-price');
                    
                    addToCart(productId, productName, productPrice, this);
                });
            });
        });

        function updatePrices(currency) {
            const priceElements = document.querySelectorAll('.product-price');
            
            priceElements.forEach(element => {
                const yerPrice = element.querySelector('.price-yer');
                const sarPrice = element.querySelector('.price-sar');
                const usdPrice = element.querySelector('.price-usd');
                
                if (yerPrice) yerPrice.style.display = 'none';
                if (sarPrice) sarPrice.style.display = 'none';
                if (usdPrice) usdPrice.style.display = 'none';
                
                switch(currency) {
                    case 'YER':
                        if (yerPrice) yerPrice.style.display = 'block';
                        break;
                    case 'SAR':
                        if (sarPrice) sarPrice.style.display = 'block';
                        break;
                    case 'USD':
                        if (usdPrice) usdPrice.style.display = 'block';
                        break;
                }
            });
        }

        function addToCart(productId, productName, productPrice, button) {
            // عرض حالة التحميل
            const originalHTML = button.innerHTML;
            button.innerHTML = '<div class="loading-spinner"></div>';
            button.disabled = true;
            
            setTimeout(() => {
                let cart = JSON.parse(localStorage.getItem('cart')) || [];
                const existingItem = cart.find(item => item.id == productId);
                
                if (existingItem) {
                    existingItem.quantity += 1;
                } else {
                    cart.push({
                        id: productId,
                        quantity: 1,
                        name: productName,
                        price: parseFloat(productPrice),
                        timestamp: new Date().getTime()
                    });
                }
                
                localStorage.setItem('cart', JSON.stringify(cart));
                updateCartCount();
                
                // استعادة الزر
                button.innerHTML = originalHTML;
                button.disabled = false;
                
                showNotification('تمت إضافة المنتج إلى سلة التسوق بنجاح!');
            }, 500);
        }

        function updateCartCount() {
            const cartCount = document.getElementById('cartCount');
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const totalItems = cart.reduce((total, item) => total + (item.quantity || 0), 0);
            cartCount.textContent = totalItems;
        }

        function showNotification(message) {
            // إزالة أي إشعارات سابقة
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(notification => notification.remove());
            
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.innerHTML = `
                <i class="fas fa-check-circle"></i>
                ${message}
            `;
            document.body.appendChild(notification);
            
            // الإزالة التلقائية بعد 3 ثوان
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 3000);
        }

        // تحسينات تفاعلية
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                if (!this.classList.contains('out-of-stock')) {
                    this.style.transform = 'translateY(-10px)';
                }
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // إدارة حالة التحميل للصور
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('load', function() {
                this.style.opacity = '1';
            });
            
            img.addEventListener('error', function() {
                if (!this.hasAttribute('data-fallback-handled')) {
                    this.src = 'assets/images/placeholder.jpg';
                    this.setAttribute('data-fallback-handled', 'true');
                }
            });
        });
    </script>
</body>
</html>