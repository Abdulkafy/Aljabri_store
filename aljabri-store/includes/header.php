<?php
// جلب إعدادات المتجر
$settings = getStoreSettings($conn);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $settings['store_name'] ?? 'الجابري ستور'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-color: <?php echo $settings['primary_color'] ?? '#FF6B35'; ?>;
            --secondary-color: <?php echo $settings['secondary_color'] ?? '#2C3E50'; ?>;
        }
    </style>
</head>
<body>
    <!-- الإعلان المتحرك -->
    <div class="announcement-bar">
        <div class="announcement-content">
            <?php
            $announcements = explode('،', $settings['announcement_text'] ?? '🔥 عروض خاصة - تخفيضات تصل إلى 50% 🔥');
            foreach($announcements as $announcement) {
                echo '<span>' . trim($announcement) . '</span>';
            }
            ?>
        </div>
    </div>

    <!-- الهيدر -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <h1><?php echo $settings['store_name'] ?? 'الجابري ستور'; ?></h1>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="index.php">الرئيسية</a></li>
                    <li><a href="products.php">المنتجات</a></li>
                    <li><a href="cart.php">عربة التسوق</a></li>
                    <li><a href="#contact">اتصل بنا</a></li>
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
                    <a href="cart.php">🛒 <span id="cartCount">0</span></a>
                </div>
            </div>
        </div>
    </header>

    <main>