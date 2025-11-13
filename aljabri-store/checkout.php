<?php
session_start();
include 'includes/config.php';

// بيانات صاحب المتجر والمحافظ
$store_owner = [
    'name' => 'علي عبدالفتاح نعمان نصر',
    'phone' => '775577773',
    'wallets' => [
        'kareemi' => [
            'name' => 'كريمي جوال',
            'accounts' => [
                'ريال يمني' => '121227436',
                'دولار' => '221105258',
                'ريال سعودي' => '421115869'
            ],
            'service_code' => '999228'
        ],
        'onecash' => [
            'name' => 'ون كاش',
            'account' => '110909'
        ],
        'cash' => [
            'name' => 'كاش',
            'account' => '034253'
        ],
        'mobayl_money' => [
            'name' => 'موبايل موني',
            'account' => '984138'
        ],
        'jeeb' => [
            'name' => 'جيب',
            'account' => '514350'
        ],
        'jawwali' => [
            'name' => 'جوالي',
            'account' => '115533'
        ],
        'fulousk' => [
            'name' => 'فلوسك',
            'account' => '159365'
        ]
    ]
];

// جلب بيانات السلة من الجلسة أو POST
$cart_items = [];
$subtotal = 0;
$shipping = 0;
$total = 0;

if(isset($_POST['cart_data'])) {
    $cart_items = json_decode($_POST['cart_data'], true);
} elseif(isset($_SESSION['cart'])) {
    $cart_items = $_SESSION['cart'];
}

if(empty($cart_items)) {
    echo "
    <script>
        alert('سلة التسوق فارغة');
        window.location.href = 'products.php';
    </script>
    ";
    exit;
}

// جلب الأسعار الحقيقية للمنتجات
$cart_with_details = [];
foreach($cart_items as $item) {
    $product_id = intval($item['id']);
    $sql = "SELECT id, name, price_yer, price_sar, price_usd FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($product = mysqli_fetch_assoc($result)) {
        $cart_with_details[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price_yer' => $product['price_yer'],
            'price_sar' => $product['price_sar'],
            'price_usd' => $product['price_usd'],
            'quantity' => $item['quantity']
        ];
        
        $subtotal += $product['price_yer'] * $item['quantity'];
    }
    mysqli_stmt_close($stmt);
}

$shipping = $subtotal > 50000 ? 0 : 5000;
$total = $subtotal + $shipping;

$_SESSION['checkout_data'] = [
    'cart_items' => $cart_with_details,
    'subtotal' => $subtotal,
    'shipping' => $shipping,
    'total' => $total,
    'store_owner' => $store_owner
];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إتمام الطلب - الجابري ستور</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .checkout-section {
            padding: 2rem 0;
        }
        
        .checkout-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .checkout-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .form-section:last-child {
            border-bottom: none;
        }
        
        .form-section h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #555;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4CAF50;
        }
        
        .address-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .payment-method {
            display: flex;
            align-items: center;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-method:hover {
            border-color: #4CAF50;
        }
        
        .payment-method.active {
            border-color: #4CAF50;
            background: #f8fff8;
        }
        
        .payment-method input {
            margin-left: 0.5rem;
        }
        
        .payment-method span {
            flex: 1;
        }
        
        .payment-icon {
            margin-left: 0.5rem;
            font-size: 1.2rem;
        }
        
        /* أنماط جديدة لعرض معلومات الحساب */
        .wallet-info {
            display: none;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            border-right: 4px solid #4CAF50;
        }
        
        .wallet-info.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        .wallet-owner {
            background: #4CAF50;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: bold;
        }
        
        .account-details {
            background: white;
            padding: 1rem;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .account-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .account-row:last-child {
            border-bottom: none;
        }
        
        .account-type {
            font-weight: bold;
            color: #333;
        }
        
        .account-number {
            font-family: monospace;
            font-size: 1.1rem;
            color: #e74c3c;
            direction: ltr;
        }
        
        .copy-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 0.25rem 0.75rem;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.8rem;
            margin-right: 0.5rem;
        }
        
        .copy-btn:hover {
            background: #2980b9;
        }
        
        .service-code {
            background: #f39c12;
            color: white;
            padding: 0.5rem;
            border-radius: 5px;
            text-align: center;
            margin-top: 0.5rem;
            font-weight: bold;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            flex: 1;
        }
        
        .btn-primary {
            background: #4CAF50;
            color: white;
        }
        
        .btn-primary:hover {
            background: #45a049;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .order-summary {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .order-items {
            margin: 1rem 0;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-totals {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #eee;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            padding: 0.5rem 0;
        }
        
        .total-row.final-total {
            font-weight: bold;
            font-size: 1.2rem;
            color: #4CAF50;
            border-top: 1px solid #eee;
            padding-top: 1rem;
        }
        
        .product-quantity {
            color: #666;
            font-size: 0.9rem;
        }
        
        .required {
            color: #e74c3c;
        }
        
        .form-note {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.25rem;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 768px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }
            
            .address-grid {
                grid-template-columns: 1fr;
            }
            
            .payment-methods {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="checkout-section">
        <div class="container">
            <h1>إتمام الطلب</h1>
            
            <div class="checkout-content">
                <div class="checkout-form">
                    <form id="checkoutForm" action="process_order.php" method="POST">
                        <input type="hidden" name="cart_data" value='<?php echo json_encode($cart_with_details); ?>'>
                        <input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>">
                        <input type="hidden" name="shipping" value="<?php echo $shipping; ?>">
                        <input type="hidden" name="total" value="<?php echo $total; ?>">

                        <!-- أقسام معلومات العميل والعنوان تبقى كما هي -->
                        <div class="form-section">
                            <h3>معلومات العميل</h3>
                            <div class="form-group">
                                <label for="customer_name">الاسم الكامل <span class="required">*</span></label>
                                <input type="text" id="customer_name" name="customer_name" required 
                                       value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>"
                                       placeholder="أدخل الاسم الكامل">
                            </div>
                            <div class="form-group">
                                <label for="customer_phone">رقم الهاتف <span class="required">*</span></label>
                                <input type="tel" id="customer_phone" name="customer_phone" required 
                                       placeholder="مثال: 771234567"
                                       pattern="[0-9]{9}" 
                                       title="يرجى إدخال رقم هاتف صحيح (9 أرقام)">
                                <div class="form-note">يجب أن يتكون رقم الهاتف من 9 أرقام</div>
                            </div>
                            <div class="form-group">
                                <label for="customer_email">البريد الإلكتروني</label>
                                <input type="email" id="customer_email" name="customer_email"
                                       value="<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>"
                                       placeholder="example@email.com">
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>عنوان التوصيل</h3>
                            <div class="address-grid">
                                <div class="form-group">
                                    <label for="customer_city">المحافظة <span class="required">*</span></label>
                                    <select id="customer_city" name="customer_city" required>
                                        <option value="">اختر المحافظة</option>
                                        <option value="صنعاء">صنعاء</option>
                                        <option value="عدن">عدن</option>
                                        <option value="تعز">تعز</option>
                                        <option value="الحديدة">الحديدة</option>
                                        <option value="إب">إب</option>
                                        <option value="ذمار">ذمار</option>
                                        <option value="المكلا">المكلا</option>
                                        <option value="سيئون">سيئون</option>
                                        <option value="شبوة">شبوة</option>
                                        <option value="حجة">حجة</option>
                                        <option value="مأرب">مأرب</option>
                                        <option value="البيضاء">البيضاء</option>
                                        <option value="صعدة">صعدة</option>
                                        <option value="حضرموت">حضرموت</option>
                                        <option value="أخرى">أخرى</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="customer_area">المنطقة/الحي <span class="required">*</span></label>
                                    <input type="text" id="customer_area" name="customer_area" required 
                                           placeholder="اسم المنطقة أو الحي">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="customer_street">الشارع <span class="required">*</span></label>
                                <input type="text" id="customer_street" name="customer_street" required 
                                       placeholder="اسم الشارع الرئيسي">
                            </div>
                            <div class="address-grid">
                                <div class="form-group">
                                    <label for="customer_building">المبنى/المنزل</label>
                                    <input type="text" id="customer_building" name="customer_building" 
                                           placeholder="رقم أو اسم المبنى">
                                </div>
                                <div class="form-group">
                                    <label for="customer_apartment">الشقة/الطابق</label>
                                    <input type="text" id="customer_apartment" name="customer_apartment" 
                                           placeholder="رقم الشقة أو الطابق">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="customer_notes">ملاحظات التوصيل</label>
                                <textarea id="customer_notes" name="customer_notes" rows="2"
                                          placeholder="ملاحظات حول وقت التوصيل أو أي تعليمات خاصة"></textarea>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>طريقة الدفع</h3>
                            <div class="payment-methods">
                                <label class="payment-method" for="kareemi">
                                    <input type="radio" id="kareemi" name="payment_method" value="kareemi" required>
                                    <span>كريمي جوال</span>
                                    <span class="payment-icon">📱</span>
                                </label>
                                <label class="payment-method" for="jeeb">
                                    <input type="radio" id="jeeb" name="payment_method" value="jeeb" required>
                                    <span>جيب</span>
                                    <span class="payment-icon">👛</span>
                                </label>
                                <label class="payment-method" for="onecash">
                                    <input type="radio" id="onecash" name="payment_method" value="onecash" required>
                                    <span>ون كاش</span>
                                    <span class="payment-icon">💸</span>
                                </label>
                                <label class="payment-method" for="fulousk">
                                    <input type="radio" id="fulousk" name="payment_method" value="fulousk" required>
                                    <span>فلوسك</span>
                                    <span class="payment-icon">💰</span>
                                </label>
                                <label class="payment-method" for="jawwali">
                                    <input type="radio" id="jawwali" name="payment_method" value="jawwali" required>
                                    <span>جوالي</span>
                                    <span class="payment-icon">📞</span>
                                </label>
                                <label class="payment-method" for="mobayl_money">
                                    <input type="radio" id="mobayl_money" name="payment_method" value="mobayl_money" required>
                                    <span>موبايل موني</span>
                                    <span class="payment-icon">📲</span>
                                </label>
                                <label class="payment-method active" for="cash">
                                    <input type="radio" id="cash" name="payment_method" value="cash" required checked>
                                    <span>الدفع عند الاستلام</span>
                                    <span class="payment-icon">💵</span>
                                </label>
                            </div>
                            
                            <!-- عرض معلومات الحسابات حسب طريقة الدفع المختارة -->
                            <div id="walletInfo" class="wallet-info">
                                <!-- سيتم ملؤها بالجافاسكريبت -->
                            </div>
                            
                            <div class="form-note">سيتم التواصل معك لتأكيد طريقة الدفع والطلب</div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                تأكيد الطلب
                            </button>
                            <a href="cart.php" class="btn btn-secondary">العودة إلى السلة</a>
                        </div>
                    </form>
                </div>

                <div class="order-summary">
                    <h3>ملخص الطلب</h3>
                    <div class="order-items">
                        <?php foreach($cart_with_details as $item): ?>
                            <div class="order-item">
                                <div>
                                    <div><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="product-quantity">الكمية: <?php echo $item['quantity']; ?></div>
                                </div>
                                <div>
                                    <?php echo number_format($item['price_yer'] * $item['quantity']); ?> ريال
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-totals">
                        <div class="total-row">
                            <span>المجموع الجزئي:</span>
                            <span><?php echo number_format($subtotal); ?> ريال</span>
                        </div>
                        <div class="total-row">
                            <span>رسوم التوصيل:</span>
                            <span>
                                <?php 
                                if($shipping == 0) {
                                    echo '<span style="color: #4CAF50;">مجاني</span>';
                                } else {
                                    echo number_format($shipping) . ' ريال';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="total-row final-total">
                            <span>المجموع الكلي:</span>
                            <span><?php echo number_format($total); ?> ريال</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // بيانات المحافظ من PHP
        const storeOwner = <?php echo json_encode($store_owner); ?>;
        
        // تحديث عرض معلومات المحفظة عند اختيار طريقة دفع
        function updateWalletInfo(paymentMethod) {
            const walletInfo = document.getElementById('walletInfo');
            const wallet = storeOwner.wallets[paymentMethod];
            
            if (wallet && paymentMethod !== 'cash') {
                let html = `
                    <div class="wallet-owner">
                        اسم صاحب المتجر: ${storeOwner.name}
                    </div>
                    <div class="account-details">
                `;
                
                if (paymentMethod === 'kareemi') {
                    // كريمي له حسابات متعددة
                    html += `<div style="margin-bottom: 1rem; font-weight: bold; color: #2c3e50;">اختر نوع الحساب:</div>`;
                    for (const [type, account] of Object.entries(wallet.accounts)) {
                        html += `
                            <div class="account-row">
                                <span class="account-type">${type}:</span>
                                <span class="account-number">${account}</span>
                                <button class="copy-btn" onclick="copyToClipboard('${account}')">نسخ</button>
                            </div>
                        `;
                    }
                    if (wallet.service_code) {
                        html += `
                            <div class="service-code">
                                كود الخدمة: ${wallet.service_code}
                            </div>
                        `;
                    }
                } else {
                    // المحافظ الأخرى لها حساب واحد
                    html += `
                        <div class="account-row">
                            <span class="account-type">رقم الحساب:</span>
                            <span class="account-number">${wallet.account}</span>
                            <button class="copy-btn" onclick="copyToClipboard('${wallet.account}')">نسخ</button>
                        </div>
                    `;
                }
                
                html += `</div>`;
                walletInfo.innerHTML = html;
                walletInfo.classList.add('active');
            } else {
                walletInfo.classList.remove('active');
                walletInfo.innerHTML = '';
            }
        }
        
        // نسخ الرقم إلى الحافظة
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // إظهار رسالة نجاح
                const originalText = event.target.textContent;
                event.target.textContent = 'تم النسخ!';
                event.target.style.background = '#27ae60';
                
                setTimeout(() => {
                    event.target.textContent = originalText;
                    event.target.style.background = '#3498db';
                }, 2000);
            }).catch(function(err) {
                console.error('Failed to copy: ', err);
                alert('تعذر نسخ الرقم، يرجى نسخه يدوياً');
            });
        }
        
        // تحسين تجربة المستخدم لطرق الدفع
        document.querySelectorAll('.payment-method').forEach(label => {
            label.addEventListener('click', function() {
                document.querySelectorAll('.payment-method').forEach(l => {
                    l.classList.remove('active');
                });
                
                this.classList.add('active');
                
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
                
                // تحديث معلومات المحفظة
                updateWalletInfo(radio.value);
            });
        });
        
        // تهيئة أولية
        document.addEventListener('DOMContentLoaded', function() {
            const defaultPayment = document.querySelector('input[name="payment_method"]:checked');
            if (defaultPayment) {
                updateWalletInfo(defaultPayment.value);
            }
        });

        // بقية الكود يبقى كما هو...
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            if (!paymentMethod) {
                alert('الرجاء اختيار طريقة الدفع');
                return false;
            }
            
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#e74c3c';
                } else {
                    field.style.borderColor = '#4CAF50';
                }
            });
            
            if (!isValid) {
                alert('الرجاء ملء جميع الحقول المطلوبة');
                return false;
            }
            
            const phoneInput = document.getElementById('customer_phone');
            const phoneRegex = /^[0-9]{9}$/;
            if (!phoneRegex.test(phoneInput.value)) {
                alert('يرجى إدخال رقم هاتف صحيح (9 أرقام)');
                phoneInput.focus();
                return false;
            }
            
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = 'جاري معالجة الطلب...';
            submitBtn.disabled = true;
            
            this.submit();
        });

        document.querySelectorAll('input[required], select[required]').forEach(field => {
            field.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    this.style.borderColor = '#e74c3c';
                } else {
                    this.style.borderColor = '#4CAF50';
                }
            });
        });

        document.getElementById('customer_phone').addEventListener('input', function() {
            const phoneRegex = /^[0-9]{0,9}$/;
            if (!phoneRegex.test(this.value)) {
                this.value = this.value.slice(0, -1);
            }
            
            if (this.value.length === 9) {
                this.style.borderColor = '#4CAF50';
            } else {
                this.style.borderColor = '#e74c3c';
            }
        });

        window.addEventListener('beforeunload', function(e) {
            const form = document.getElementById('checkoutForm');
            const formData = new FormData(form);
            let hasData = false;
            
            for (let value of formData.values()) {
                if (value) {
                    hasData = true;
                    break;
                }
            }
            
            if (hasData) {
                e.preventDefault();
                e.returnValue = 'لديك بيانات غير محفوظة. هل تريد حقاً مغادرة الصفحة؟';
            }
        });
    </script>
</body>
</html>