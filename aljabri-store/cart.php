<?php
session_start();
include 'includes/config.php';

// جلب إعدادات المتجر
$settings = getStoreSettings($conn);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سلة التسوق - <?php echo $settings['store_name']; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .cart-section {
            padding: 2rem 0;
            min-height: 70vh;
        }
        
        .cart-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .cart-items {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
        }
        
        .cart-item:hover {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 100px;
            height: 100px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-left: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e9ecef;
            overflow: hidden;
        }
        
        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        
        .item-price {
            color: #4CAF50;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .item-total {
            color: #e74c3c;
            font-weight: bold;
            margin-top: 0.5rem;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0.5rem 0;
        }
        
        .quantity-btn {
            background: #f8f9fa;
            border: 1px solid #ddd;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .quantity-btn:hover {
            background: #e9ecef;
            transform: scale(1.1);
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 0.5rem;
            font-weight: bold;
        }
        
        .remove-btn {
            background: #ff4757;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .remove-btn:hover {
            background: #e84118;
            transform: translateY(-2px);
        }
        
        .cart-summary {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .summary-details {
            margin: 1rem 0;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .summary-row.total {
            border-top: 2px solid #eee;
            font-weight: bold;
            font-size: 1.3rem;
            color: #4CAF50;
            padding-top: 1rem;
        }
        
        .checkout-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        
        .btn {
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: #4CAF50;
            color: white;
        }
        
        .btn-primary:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .empty-cart h3 {
            color: #666;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        
        .empty-cart p {
            color: #888;
            margin-bottom: 2rem;
        }
        
        .cart-item-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 0.5rem;
        }
        
        .loading {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
        
        .currency-selector {
            margin-bottom: 1rem;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
        }
        
        @media (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .cart-item {
                flex-direction: column;
                text-align: center;
                padding: 1.5rem 0;
            }
            
            .item-image {
                margin-left: 0;
                margin-bottom: 1rem;
            }
            
            .quantity-controls {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="cart-section">
        <div class="container">
            <h1>🛒 سلة التسوق</h1>
            
            <div class="cart-content">
                <div class="cart-items" id="cartItems">
                    <div class="loading">
                        <p>جاري تحميل سلة التسوق...</p>
                    </div>
                </div>
                
                <div class="cart-summary">
                    <h3>📋 ملخص الطلب</h3>
                    
                    <!-- محول العملة -->
                    <select class="currency-selector" id="currencySelector" onchange="updateCartPrices()">
                        <option value="YER">ريال يمني</option>
                        <option value="SAR">ريال سعودي</option>
                        <option value="USD">دولار أمريكي</option>
                    </select>
                    
                    <div class="summary-details">
                        <div class="summary-row">
                            <span>المجموع الجزئي:</span>
                            <span id="subtotal">0 ريال يمني</span>
                        </div>
                        <div class="summary-row">
                            <span>رسوم التوصيل:</span>
                            <span id="shipping">0 ريال يمني</span>
                        </div>
                        <div class="summary-row total">
                            <span>المجموع الكلي:</span>
                            <span id="total">0 ريال يمني</span>
                        </div>
                    </div>
                    
                    <div class="checkout-actions">
                        <button class="btn btn-primary" onclick="proceedToCheckout()">
                            💳 إتمام الشراء
                        </button>
                        <a href="products.php" class="btn btn-secondary">
                            🛍️ مواصلة التسوق
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // متغيرات عامة
        let currentCurrency = 'YER';
        let exchangeRates = {
            'YER': 1,
            'SAR': 0.016, // مثال: 1 ريال يمني = 0.016 ريال سعودي
            'USD': 0.004  // مثال: 1 ريال يمني = 0.004 دولار
        };

        // تحميل عربة التسوق عند فتح الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            loadCart();
            loadCurrencyPreference();
        });

        // تحميل السلة مع الأسعار الحقيقية
        async function loadCart() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const cartItems = document.getElementById('cartItems');
            
            if (cart.length === 0) {
                showEmptyCart();
                return;
            }

            // عرض تحميل
            cartItems.innerHTML = '<div class="loading"><p>جاري تحميل المنتجات...</p></div>';

            try {
                // جلب الأسعار الحقيقية من السيرفر
                const productIds = cart.map(item => item.id);
                const response = await fetch('get_cart_products.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ product_ids: productIds })
                });

                const productsData = await response.json();
                
                if (productsData.success) {
                    displayCartWithRealPrices(cart, productsData.products);
                } else {
                    throw new Error('فشل في جلب بيانات المنتجات');
                }
            } catch (error) {
                console.error('Error:', error);
                displayCartWithLocalPrices(cart);
            }
        }

        // عرض السلة بالأسعار الحقيقية
        function displayCartWithRealPrices(cart, products) {
            const cartItems = document.getElementById('cartItems');
            let html = '';
            let subtotal = 0;

            cart.forEach((item, index) => {
                const product = products.find(p => p.id == item.id);
                if (product) {
                    const price = getPriceInCurrency(product, currentCurrency);
                    const itemTotal = price * item.quantity;
                    subtotal += itemTotal;
                    
                    html += `
                        <div class="cart-item">
                            <div class="item-image">
                                ${getProductImage(product)}
                            </div>
                            <div class="item-details">
                                <div class="item-name">${product.name}</div>
                                <div class="item-price">
                                    ${formatPrice(price, currentCurrency)} 
                                    <small style="color: #666; font-size: 0.9rem;">× ${item.quantity}</small>
                                </div>
                                <div class="item-total">
                                    الإجمالي: ${formatPrice(itemTotal, currentCurrency)}
                                </div>
                                <div class="cart-item-actions">
                                    <div class="quantity-controls">
                                        <button class="quantity-btn" onclick="updateQuantity(${index}, ${item.quantity - 1})">-</button>
                                        <input type="number" class="quantity-input" value="${item.quantity}" 
                                               onchange="updateQuantity(${index}, parseInt(this.value))" min="1">
                                        <button class="quantity-btn" onclick="updateQuantity(${index}, ${item.quantity + 1})">+</button>
                                    </div>
                                    <button class="remove-btn" onclick="removeFromCart(${index})">
                                        🗑️ حذف
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                }
            });

            cartItems.innerHTML = html;
            updateSummary(subtotal);
        }

        // عرض السلة بالأسعار المحلية (إذا فشل الاتصال بالسيرفر)
        function displayCartWithLocalPrices(cart) {
            const cartItems = document.getElementById('cartItems');
            let html = '';
            let subtotal = 0;

            cart.forEach((item, index) => {
                const price = item.price || 0;
                const itemTotal = price * item.quantity;
                subtotal += itemTotal;
                
                html += `
                    <div class="cart-item">
                        <div class="item-image">
                            <span>🖼️</span>
                        </div>
                        <div class="item-details">
                            <div class="item-name">${item.name}</div>
                            <div class="item-price">
                                ${formatPrice(price, currentCurrency)}
                                <small style="color: #666; font-size: 0.9rem;">× ${item.quantity}</small>
                            </div>
                            <div class="item-total">
                                الإجمالي: ${formatPrice(itemTotal, currentCurrency)}
                            </div>
                            <div class="cart-item-actions">
                                <div class="quantity-controls">
                                    <button class="quantity-btn" onclick="updateQuantity(${index}, ${item.quantity - 1})">-</button>
                                    <input type="number" class="quantity-input" value="${item.quantity}" 
                                           onchange="updateQuantity(${index}, parseInt(this.value))" min="1">
                                    <button class="quantity-btn" onclick="updateQuantity(${index}, ${item.quantity + 1})">+</button>
                                </div>
                                <button class="remove-btn" onclick="removeFromCart(${index})">
                                    🗑️ حذف
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            cartItems.innerHTML = html;
            updateSummary(subtotal);
        }

        // دالة مساعدة للحصول على السعر بالعملة المحددة
        function getPriceInCurrency(product, currency) {
            const prices = {
                'YER': product.price_yer || product.price || 0,
                'SAR': product.price_sar || (product.price_yer * exchangeRates.SAR) || 0,
                'USD': product.price_usd || (product.price_yer * exchangeRates.USD) || 0
            };
            return prices[currency] || 0;
        }

        // دالة مساعدة لتنسيق السعر
        function formatPrice(price, currency) {
            const symbols = {
                'YER': 'ريال يمني',
                'SAR': 'ريال سعودي',
                'USD': 'دولار'
            };
            return `${price.toLocaleString()} ${symbols[currency]}`;
        }

        // دالة مساعدة للحصول على صورة المنتج
        function getProductImage(product) {
            if (product.main_image) {
                return `<img src="assets/uploads/${product.main_image}" alt="${product.name}" 
                           onerror="this.style.display='none'; this.parentElement.innerHTML='🖼️'">`;
            }
            return '🖼️';
        }

        // تحديث الكمية
        function updateQuantity(index, newQuantity) {
            if (newQuantity < 1) return;
            
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            cart[index].quantity = newQuantity;
            localStorage.setItem('cart', JSON.stringify(cart));
            loadCart();
        }

        // حذف من السلة
        function removeFromCart(index) {
            if (confirm('هل أنت متأكد من حذف هذا المنتج من السلة؟')) {
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                cart.splice(index, 1);
                localStorage.setItem('cart', JSON.stringify(cart));
                loadCart();
                updateCartCount();
            }
        }

        // تحديث الملخص
        function updateSummary(subtotal) {
            const shipping = subtotal > 50000 ? 0 : 5000; // توصيل مجاني فوق 50,000
            const total = subtotal + shipping;

            document.getElementById('subtotal').textContent = formatPrice(subtotal, currentCurrency);
            document.getElementById('shipping').textContent = formatPrice(shipping, currentCurrency);
            document.getElementById('total').textContent = formatPrice(total, currentCurrency);
        }

        // تحديث عداد السلة
        function updateCartCount() {
            const cartCount = document.getElementById('cartCount');
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
            if (cartCount) {
                cartCount.textContent = totalItems;
            }
        }

        // تحديث الأسعار حسب العملة
        function updateCartPrices() {
            const selector = document.getElementById('currencySelector');
            currentCurrency = selector.value;
            localStorage.setItem('selectedCurrency', currentCurrency);
            loadCart();
        }

        // تحميل تفضيل العملة
        function loadCurrencyPreference() {
            const savedCurrency = localStorage.getItem('selectedCurrency') || 'YER';
            const selector = document.getElementById('currencySelector');
            selector.value = savedCurrency;
            currentCurrency = savedCurrency;
        }

        // عرض سلة فارغة
        function showEmptyCart() {
            const cartItems = document.getElementById('cartItems');
            cartItems.innerHTML = `
                <div class="empty-cart">
                    <h3>سلة التسوق فارغة</h3>
                    <p>لم تقم بإضافة أي منتجات إلى سلة التسوق بعد</p>
                    <a href="products.php" class="btn btn-primary">تصفح المنتجات</a>
                </div>
            `;
            updateSummary(0, 0, 0);
        }

        // إتمام الشراء
        async function proceedToCheckout() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            
            if (cart.length === 0) {
                alert('❌ سلة التسوق فارغة. الرجاء إضافة منتجات قبل إتمام الشراء.');
                return;
            }

            try {
                // التحقق من توفر المنتجات والأسعار
                const productIds = cart.map(item => item.id);
                const response = await fetch('get_cart_products.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ product_ids: productIds })
                });

                const productsData = await response.json();
                
                if (!productsData.success) {
                    throw new Error('فشل في التحقق من المنتجات');
                }

                // حفظ بيانات السلة في الجلسة
                const saveResponse = await fetch('save_cart_session.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        cart: cart,
                        currency: currentCurrency
                    })
                });

                const saveData = await saveResponse.json();
                
                if (saveData.success) {
                    // الانتقال إلى صفحة الدفع
                    window.location.href = 'checkout.php';
                } else {
                    alert('❌ حدث خطأ في حفظ بيانات السلة. الرجاء المحاولة مرة أخرى.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('❌ حدث خطأ في معالجة الطلب. الرجاء المحاولة مرة أخرى.');
            }
        }

        // تحديث عداد السلة عند التحميل
        updateCartCount();
    </script>
</body>
</html>