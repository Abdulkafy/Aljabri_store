<?php
session_start();
include 'includes/config.php';

// جلب إعدادات المتجر
$settings = getStoreSettings($conn);

// جلب جميع المنتجات
$products = [];
$sql = "SELECT * FROM products WHERE stock_quantity > 0 ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

if($result && mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المنتجات - <?php echo $settings['store_name']; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-color: <?php echo $settings['primary_color']; ?>;
            --secondary-color: <?php echo $settings['secondary_color']; ?>;
        }
        
        .products-page {
            padding: 40px 0;
            min-height: 60vh;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .product-card {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .product-image {
            height: 200px;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.05);
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-info h3 {
            color: var(--secondary-color);
            margin-bottom: 10px;
            font-size: 18px;
            line-height: 1.4;
        }
        
        .product-description {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
            font-size: 14px;
        }
        
        .product-price {
            margin-bottom: 15px;
        }
        
        .product-price span {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .price-yer {
            color: var(--primary-color);
            font-size: 18px;
        }
        
        .price-sar, .price-usd {
            color: var(--accent-color);
            font-size: 14px;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-secondary {
            background: var(--secondary-color);
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .btn-secondary:hover {
            background: var(--accent-color);
        }
        
        .no-products {
            text-align: center;
            padding: 60px 20px;
            grid-column: 1 / -1;
        }
        
        .no-products h3 {
            color: var(--secondary-color);
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="products-page">
        <div class="container">
            <div class="page-header">
                <h1>جميع المنتجات</h1>
                <p>اكتشف أحدث منتجاتنا المتاحة</p>
            </div>

            <div class="products-grid">
                <?php if(!empty($products)): ?>
                    <?php foreach($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="assets/uploads/<?php echo !empty($product['main_image']) ? $product['main_image'] : 'placeholder.jpg'; ?>" 
                                 alt="<?php echo $product['name']; ?>" 
                                 onerror="this.src='assets/images/placeholder.jpg'">
                        </div>
                        <div class="product-info">
                            <h3><?php echo $product['name']; ?></h3>
                            <p class="product-description">
                                <?php 
                                $description = !empty($product['description']) ? $product['description'] : 'منتج متميز من متجرنا';
                                echo substr($description, 0, 100) . (strlen($description) > 100 ? '...' : '');
                                ?>
                            </p>
                            <p class="product-price">
                                <span class="price-yer"><?php echo number_format($product['price_yer']); ?> ريال يمني</span>
                                <span class="price-sar" style="display: none;"><?php echo number_format($product['price_sar']); ?> ريال سعودي</span>
                                <span class="price-usd" style="display: none;">$<?php echo number_format($product['price_usd'], 2); ?></span>
                            </p>
                            <div class="product-meta">
                                <span class="stock-status in-stock">
                                    متوفر (<?php echo $product['stock_quantity']; ?>)
                                </span>
                            </div>
                            <div class="product-actions">
                                <button class="btn add-to-cart" data-product-id="<?php echo $product['id']; ?>">إضافة إلى السلة</button>
                                <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn-secondary">عرض التفاصيل</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <h3>لا توجد منتجات متاحة حالياً</h3>
                        <p>سنقوم بإضافة منتجات جديدة قريباً</p>
                        <a href="index.php" class="btn">العودة إلى الرئيسية</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="product-stock">
    <?php if ($product['stock_quantity'] > 10): ?>
        <span class="stock-available">🟢 متوفر</span>
    <?php elseif ($product['stock_quantity'] > 0): ?>
        <span class="stock-low">🟡 آخر <?php echo $product['stock_quantity']; ?> قطع</span>
    <?php else: ?>
        <span class="stock-out">🔴 غير متوفر</span>
    <?php endif; ?>
</div>
    </section>

    <?php include 'includes/footer.php'; ?>

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
            
            // تحديث عداد السلة
            updateCartCount();
            
            // إضافة إلى السلة
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    addToCart(productId);
                });
            });
        });

        function updatePrices(currency) {
            const priceElements = document.querySelectorAll('.product-price');
            
            priceElements.forEach(element => {
                const yerPrice = element.querySelector('.price-yer');
                const sarPrice = element.querySelector('.price-sar');
                const usdPrice = element.querySelector('.price-usd');
                
                // إخفاء جميع الأسعار أولاً
                yerPrice.style.display = 'none';
                sarPrice.style.display = 'none';
                usdPrice.style.display = 'none';
                
                // إظهار السعر المختار فقط
                switch(currency) {
                    case 'YER':
                        yerPrice.style.display = 'block';
                        break;
                    case 'SAR':
                        sarPrice.style.display = 'block';
                        break;
                    case 'USD':
                        usdPrice.style.display = 'block';
                        break;
                }
            });
        }

        function addToCart(productId) {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            const existingItem = cart.find(item => item.id == productId);
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: productId,
                    quantity: 1,
                    name: 'منتج ' + productId,
                    price: 10000
                });
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            showNotification('تمت إضافة المنتج إلى سلة التسوق');
        }

        function updateCartCount() {
            const cartCount = document.getElementById('cartCount');
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
            cartCount.textContent = totalItems;
        }

        function showNotification(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: var(--primary-color);
                color: white;
                padding: 15px 25px;
                border-radius: 5px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                z-index: 1000;
                font-weight: bold;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
</body>
</html>