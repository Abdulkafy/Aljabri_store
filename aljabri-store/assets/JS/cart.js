// إدارة سلة التسوق
document.addEventListener('DOMContentLoaded', function() {
    loadCartItems();
    updateCartSummary();
});

// تحميل عناصر السلة
function loadCartItems() {
    const cartItemsContainer = document.getElementById('cartItems');
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    if(cart.length === 0) {
        cartItemsContainer.innerHTML = `
            <div class="empty-cart">
                <p>سلة التسوق فارغة</p>
                <a href="products.php" class="btn">ابدأ التسوق</a>
            </div>
        `;
        document.getElementById('checkoutBtn').style.display = 'none';
        return;
    }
    
    let cartHTML = '';
    cart.forEach(item => {
        // في تطبيق حقيقي، ستجلب بيانات المنتج من قاعدة البيانات
        cartHTML += `
            <div class="cart-item" data-product-id="${item.id}">
                <div class="item-image">
                    <img src="assets/uploads/product-image.jpg" alt="Product Image">
                </div>
                <div class="item-details">
                    <h4>اسم المنتج ${item.id}</h4>
                    <p class="item-price">${(item.price * item.quantity).toLocaleString()} ريال يمني</p>
                </div>
                <div class="item-quantity">
                    <button onclick="updateQuantity(${item.id}, -1)">-</button>
                    <span>${item.quantity}</span>
                    <button onclick="updateQuantity(${item.id}, 1)">+</button>
                </div>
                <div class="item-actions">
                    <button onclick="removeFromCart(${item.id})" class="btn-remove">✕</button>
                </div>
            </div>
        `;
    });
    
    cartItemsContainer.innerHTML = cartHTML;
}

// تحديث الكمية
function updateQuantity(productId, change) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    const item = cart.find(item => item.id === productId);
    
    if(item) {
        item.quantity += change;
        
        if(item.quantity <= 0) {
            cart = cart.filter(item => item.id !== productId);
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        loadCartItems();
        updateCartSummary();
        updateCartCount();
    }
}

// إزالة من السلة
function removeFromCart(productId) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    cart = cart.filter(item => item.id !== productId);
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCartItems();
    updateCartSummary();
    updateCartCount();
}

// تحديث ملخص السلة
function updateCartSummary() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    let subtotal = 0;
    
    cart.forEach(item => {
        // في تطبيق حقيقي، ستجلب السعر من قاعدة البيانات
        subtotal += (item.price || 10000) * item.quantity;
    });
    
    const shipping = subtotal > 50000 ? 0 : 2000; // توصيل مجاني فوق 50,000
    const total = subtotal + shipping;
    
    document.getElementById('subtotal').textContent = subtotal.toLocaleString() + ' ريال يمني';
    document.getElementById('shipping').textContent = shipping.toLocaleString() + ' ريال يمني';
    document.getElementById('total').textContent = total.toLocaleString() + ' ريال يمني';
}
// في ملف cart.js - تحديث دالة إتمام الشراء
function proceedToCheckout() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    if (cart.length === 0) {
        alert('سلة التسوق فارغة');
        return;
    }

    // حفظ بيانات السلة في الجلسة إذا لزم الأمر
    fetch('save_cart_session.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ cart: cart })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'checkout.php';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.location.href = 'checkout.php';
    });
}

// تحديث زر إتمام الشراء في صفحة السلة
document.addEventListener('DOMContentLoaded', function() {
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            proceedToCheckout();
        });
    }
    // تحديث دالة إضافة إلى السلة للتحقق من المخزون
async function addToCart(productId, productName, productPrice) {
    try {
        // التحقق من المخزون أولاً
        const response = await fetch('check_stock.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                product_id: productId,
                quantity: 1
            })
        });

        const stockData = await response.json();
        
        if (!stockData.available) {
            showNotification('❌ ' + stockData.message, 'error');
            return;
        }

        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        const existingItem = cart.find(item => item.id == productId);
        
        if (existingItem) {
            // التحقق من المخزون للكمية الجديدة
            const newQuantity = existingItem.quantity + 1;
            const stockCheck = await checkStockAvailability(productId, newQuantity);
            
            if (!stockCheck.available) {
                showNotification('❌ ' + stockCheck.message, 'error');
                return;
            }
            
            existingItem.quantity = newQuantity;
        } else {
            cart.push({
                id: productId,
                quantity: 1,
                name: productName,
                price: parseFloat(productPrice)
            });
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        
        showNotification('✅ تمت إضافة المنتج إلى سلة التسوق بنجاح!');
        
    } catch (error) {
        console.error('Error:', error);
        showNotification('❌ حدث خطأ في إضافة المنتج', 'error');
    }
}

// دالة للتحقق من توفر المخزون
async function checkStockAvailability(productId, quantity) {
    try {
        const response = await fetch('check_stock.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                product_id: productId,
                quantity: quantity
            })
        });
        return await response.json();
    } catch (error) {
        return { available: false, message: 'خطأ في التحقق من المخزون' };
    }
}
});