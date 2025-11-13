// إدارة صفحة الدفع
document.addEventListener('DOMContentLoaded', function() {
    loadOrderSummary();
    
    // إرسال النموذج
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        e.preventDefault();
        processOrder();
    });
});

// تحميل ملخص الطلب
function loadOrderSummary() {
    const orderSummary = document.getElementById('orderSummary');
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    if(cart.length === 0) {
        orderSummary.innerHTML = '<p>لا توجد عناصر في السلة</p>';
        return;
    }
    
    let summaryHTML = '';
    let subtotal = 0;
    
    cart.forEach(item => {
        const itemTotal = (item.price || 10000) * item.quantity;
        subtotal += itemTotal;
        
        summaryHTML += `
            <div class="order-item">
                <span>منتج ${item.id} × ${item.quantity}</span>
                <span>${itemTotal.toLocaleString()} ريال يمني</span>
            </div>
        `;
    });
    
    const shipping = subtotal > 50000 ? 0 : 2000;
    const total = subtotal + shipping;
    
    summaryHTML += `
        <div class="order-totals">
            <div class="order-row">
                <span>المجموع الجزئي:</span>
                <span>${subtotal.toLocaleString()} ريال يمني</span>
            </div>
            <div class="order-row">
                <span>رسوم التوصيل:</span>
                <span>${shipping.toLocaleString()} ريال يمني</span>
            </div>
            <div class="order-row total">
                <span>المجموع الكلي:</span>
                <span>${total.toLocaleString()} ريال يمني</span>
            </div>
        </div>
    `;
    
    orderSummary.innerHTML = summaryHTML;
}

// معالجة الطلب
function processOrder() {
    const formData = new FormData(document.getElementById('checkoutForm'));
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    if(cart.length === 0) {
        alert('سلة التسوق فارغة');
        return;
    }
    
    // جمع بيانات الطلب
    const orderData = {
        customer: {
            name: formData.get('fullName'),
            phone: formData.get('phone'),
            email: formData.get('email'),
            address: formData.get('address')
        },
        paymentMethod: formData.get('paymentMethod'),
        items: cart,
        orderDate: new Date().toISOString()
    };
    
    // في تطبيق حقيقي، ستقوم بإرسال البيانات إلى الخادم
    console.log('بيانات الطلب:', orderData);
    
    // محاكاة عملية الدفع الناجحة
    alert('تم استلام طلبك بنجاح! سنتصل بك قريباً لتأكيد التفاصيل.');
    
    // تفريغ السلة
    localStorage.removeItem('cart');
    updateCartCount();
    
    // إعادة التوجيه إلى الصفحة الرئيسية
    window.location.href = 'index.php?order=success';
}