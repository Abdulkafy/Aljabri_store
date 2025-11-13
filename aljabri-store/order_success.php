<?php
// بداية الجلسة - التحقق إذا كانت الجلسة لم تبدأ بعد
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/config.php';

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// جلب بيانات الطلب
$order = [];
$order_items = [];
if ($order_id > 0) {
    // جلب بيانات الطلب الأساسية
    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    
    // جلب عناصر الطلب - بدون حقل image
    if ($order) {
        $items_sql = "SELECT oi.*, p.name as product_name 
                     FROM order_items oi 
                     LEFT JOIN products p ON oi.product_id = p.id 
                     WHERE oi.order_id = ?";
        $items_stmt = mysqli_prepare($conn, $items_sql);
        mysqli_stmt_bind_param($items_stmt, 'i', $order_id);
        mysqli_stmt_execute($items_stmt);
        $items_result = mysqli_stmt_get_result($items_stmt);
        
        while ($item = mysqli_fetch_assoc($items_result)) {
            $order_items[] = $item;
        }
        
        // تفريغ الجلسة بعد تأكيد الطلب
        unset($_SESSION['cart']);
        unset($_SESSION['checkout_data']);
    }
}

// جلب إعدادات المتجر
$settings = [];
$settings_sql = "SELECT * FROM store_settings LIMIT 1";
$settings_result = mysqli_query($conn, $settings_sql);
if($settings_result && mysqli_num_rows($settings_result) > 0) {
    $settings = mysqli_fetch_assoc($settings_result);
} else {
    // قيم افتراضية إذا لم توجد إعدادات
    $settings = [
        'store_name' => 'متجر الجابري',
        'store_phone' => 'غير محدد',
        'store_email' => 'غير محدد'
    ];
}

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
            'service_code' => '999228',
            'icon' => 'fas fa-mobile-alt',
            'color' => '#25D366'
        ],
        'jeeb' => [
            'name' => 'جيب',
            'account' => '514350',
            'icon' => 'fas fa-wallet',
            'color' => '#FF6B35'
        ],
        'onecash' => [
            'name' => 'ون كاش',
            'account' => '110909',
            'icon' => 'fas fa-money-bill-wave',
            'color' => '#007BFF'
        ],
        'fulousk' => [
            'name' => 'فلوسك',
            'account' => '159365',
            'icon' => 'fas fa-coins',
            'color' => '#28A745'
        ],
        'jawwali' => [
            'name' => 'جوالي',
            'account' => '115533',
            'icon' => 'fas fa-sim-card',
            'color' => '#FFC107'
        ],
        'mobayl_money' => [
            'name' => 'موبايل موني',
            'account' => '984138',
            'icon' => 'fas fa-mobile',
            'color' => '#8E44AD'
        ],
        'cash' => [
            'name' => 'الدفع عند الاستلام',
            'account' => '034253',
            'icon' => 'fas fa-hand-holding-usd',
            'color' => '#6C757D'
        ]
    ]
];

// معالجة بيانات العنوان
$full_address = '';
if ($order) {
    $address_parts = [];
    
    if (!empty($order['customer_address'])) {
        $address_parts[] = $order['customer_address'];
    }
    if (!empty($order['customer_city'])) {
        $address_parts[] = $order['customer_city'];
    }
    if (!empty($order['customer_area'])) {
        $address_parts[] = $order['customer_area'];
    }
    if (!empty($order['customer_street'])) {
        $address_parts[] = $order['customer_street'];
    }
    if (!empty($order['customer_building'])) {
        $address_parts[] = 'مبنى ' . $order['customer_building'];
    }
    if (!empty($order['customer_apartment'])) {
        $address_parts[] = 'شقة ' . $order['customer_apartment'];
    }
    
    $full_address = implode('، ', $address_parts);
    if (empty($full_address)) {
        $full_address = 'لم يتم تحديد عنوان';
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلب ناجح - <?php echo htmlspecialchars($settings['store_name']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .order-success {
            padding: 80px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .success-message {
            background: white;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }

        .success-icon {
            font-size: 80px;
            margin-bottom: 30px;
            color: #28a745;
        }

        .success-message h1 {
            color: #2d3748;
            margin-bottom: 15px;
            font-size: 2.5rem;
        }

        .success-message p {
            color: #718096;
            font-size: 1.2rem;
            margin-bottom: 40px;
        }

        .order-details {
            background: #f7fafc;
            padding: 30px;
            border-radius: 15px;
            margin: 30px 0;
            text-align: right;
        }

        .order-details h3 {
            color: #2d3748;
            margin-bottom: 25px;
            font-size: 1.5rem;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-row span:first-child {
            color: #718096;
            font-weight: 500;
        }

        .detail-row strong {
            color: #2d3748;
            font-size: 1.1rem;
        }

        .status {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .status.pending {
            background: #fffaf0;
            color: #dd6b20;
            border: 1px solid #fed7aa;
        }

        .status.confirmed {
            background: #e6fffa;
            color: #234e52;
            border: 1px solid #81e6d9;
        }

        .status.shipped {
            background: #ebf8ff;
            color: #1a365d;
            border: 1px solid #90cdf4;
        }

        .status.delivered {
            background: #f0fff4;
            color: #276749;
            border: 1px solid #9ae6b4;
        }

        .status.cancelled {
            background: #fff5f5;
            color: #c53030;
            border: 1px solid #fc8181;
        }

        .success-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #4299e1;
            color: white;
        }

        .btn-primary:hover {
            background: #3182ce;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #38a169;
            color: white;
        }

        .btn-success:hover {
            background: #2f855a;
            transform: translateY(-2px);
        }

        .error-message {
            background: white;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            margin: 0 auto;
        }

        .error-message h2 {
            color: #e53e3e;
            margin-bottom: 15px;
        }

        .error-message p {
            color: #718096;
            margin-bottom: 30px;
        }

        /* تنسيقات الفاتورة */
        .invoice-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 30px 0;
            text-align: right;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .store-info h2 {
            color: #2d3748;
            margin: 0 0 10px 0;
            font-size: 1.8rem;
        }

        .store-info p {
            color: #718096;
            margin: 5px 0;
        }

        .invoice-title {
            color: #4299e1;
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
        }

        .invoice-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .customer-info, .order-info {
            background: #f7fafc;
            padding: 20px;
            border-radius: 10px;
        }

        .info-section h4 {
            color: #2d3748;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #edf2f7;
        }

        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .info-label {
            color: #718096;
            font-weight: 500;
        }

        .info-value {
            color: #2d3748;
            font-weight: 600;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }

        .items-table th {
            background: #4299e1;
            color: white;
            padding: 15px;
            text-align: right;
            font-weight: 600;
        }

        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            text-align: right;
        }

        .items-table tr:hover {
            background: #f7fafc;
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #e2e8f0;
            background: #f7fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #718096;
        }

        .product-name {
            font-weight: 600;
            color: #2d3748;
        }

        .total-section {
            background: #f7fafc;
            padding: 25px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            font-size: 1.1rem;
        }

        .total-row.grand-total {
            border-top: 2px solid #4299e1;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 1.3rem;
            font-weight: bold;
            color: #2d3748;
        }

        .payment-method-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #f7fafc;
            border-radius: 20px;
            border: 2px solid #e2e8f0;
            font-weight: 600;
        }

        /* تنسيقات معلومات الدفع */
        .payment-info-section {
            background: #fffaf0;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-right: 4px solid #f6ad55;
        }

        .payment-info-section h4 {
            color: #dd6b20;
            margin-bottom: 15px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .payment-details {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #fed7aa;
        }

        .payment-account {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #fed7aa;
        }

        .payment-account:last-child {
            border-bottom: none;
        }

        .account-type {
            font-weight: 600;
            color: #744210;
        }

        .account-number {
            font-family: monospace;
            font-size: 1.1rem;
            color: #e53e3e;
            direction: ltr;
            font-weight: bold;
        }

        .copy-btn {
            background: #4299e1;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
            margin-right: 10px;
        }

        .copy-btn:hover {
            background: #3182ce;
        }

        .service-code-badge {
            background: #f6ad55;
            color: #744210;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            margin-top: 10px;
            display: inline-block;
        }

        /* تنسيقات الطباعة */
        @media print {
            body * {
                visibility: hidden;
            }
            .invoice-section, .invoice-section * {
                visibility: visible;
            }
            .invoice-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                box-shadow: none;
                margin: 0;
                padding: 20px;
            }
            .no-print {
                display: none !important;
            }
            .success-message {
                box-shadow: none;
                margin: 0;
            }
            .btn {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            .success-message {
                padding: 30px 20px;
                margin: 20px;
            }

            .success-message h1 {
                font-size: 2rem;
            }

            .order-details {
                padding: 20px;
            }

            .success-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .invoice-details {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .invoice-header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .items-table {
                font-size: 0.9rem;
            }

            .product-info {
                flex-direction: column;
                text-align: center;
                gap: 8px;
            }

            .payment-account {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .payment-account .copy-btn {
                align-self: flex-end;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="order-success">
        <div class="container">
            <?php if ($order): ?>
                <div class="success-message">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h1>شكراً لك على طلبك!</h1>
                    <p>تم استلام طلبك بنجاح وسيتم توصيله في أقرب وقت ممكن</p>
                    
                    <!-- تفاصيل الطلب الأساسية -->
                    <div class="order-details">
                        <h3>ملخص الطلب</h3>
                        <div class="detail-row">
                            <span>رقم الطلب:</span>
                            <strong><?php echo $order['order_number'] ?? '#' . $order['id']; ?></strong>
                        </div>
                        <div class="detail-row">
                            <span>الاسم:</span>
                            <span><?php echo htmlspecialchars($order['customer_name']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span>الهاتف:</span>
                            <span><?php echo htmlspecialchars($order['customer_phone']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span>المجموع الكلي:</span>
                            <strong><?php echo number_format($order['total']); ?> ريال يمني</strong>
                        </div>
                        <div class="detail-row">
                            <span>طريقة الدفع:</span>
                            <span>
                                <?php 
                                $payment_method = $order['payment_method'] ?? 'cash';
                                $payment_info = $store_owner['wallets'][$payment_method] ?? $store_owner['wallets']['cash'];
                                ?>
                                <span class="payment-method-badge" style="border-color: <?php echo $payment_info['color']; ?>">
                                    <i class="<?php echo $payment_info['icon']; ?>" style="color: <?php echo $payment_info['color']; ?>"></i>
                                    <?php echo $payment_info['name']; ?>
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span>حالة الطلب:</span>
                            <span class="status <?php echo $order['status'] ?? 'pending'; ?>">
                                <?php 
                                $status_text = [
                                    'pending' => 'قيد الانتظار',
                                    'confirmed' => 'تم التأكيد',
                                    'shipped' => 'قيد الشحن',
                                    'delivered' => 'تم التوصيل',
                                    'cancelled' => 'ملغي'
                                ];
                                echo $status_text[$order['status'] ?? 'pending'];
                                ?>
                            </span>
                        </div>
                    </div>
                           <!-- رابط التواصل مع الإدارة -->
<div class="whatsapp-section" style="background: #25D366; padding: 20px; border-radius: 10px; margin: 20px 0; text-align: center;">
    <h4 style="color: white; margin-bottom: 15px;">
        <i class="fab fa-whatsapp"></i> تواصل مع الإدارة عبر الواتساب
    </h4>
    <p style="color: white; margin-bottom: 15px;">
        للاستفسار عن طلبك أو تأكيد الدفع، يمكنك التواصل معنا مباشرة
    </p>
    <a href="https://wa.me/967775577773?text=<?php echo urlencode('مرحباً، لدي استفسار عن طلبي رقم ' . ($order['order_number'] ?? '#' . $order['id'])); ?>" 
       target="_blank" 
       class="whatsapp-btn" 
       style="display: inline-flex; align-items: center; gap: 10px; background: white; color: #25D366; padding: 12px 25px; border-radius: 25px; text-decoration: none; font-weight: bold; transition: all 0.3s ease;">
        <i class="fab fa-whatsapp" style="font-size: 1.2rem;"></i>
        تواصل عبر الواتساب
    </a>
</div>

                    <!-- معلومات الدفع الإضافية -->
                    <?php if ($payment_method !== 'cash'): ?>
                    <div class="payment-info-section">
                        <h4><i class="fas fa-credit-card"></i> معلومات التحويل</h4>
                        <div class="payment-details">
                            <div class="payment-account">
                                <span class="account-type">اسم صاحب الحساب:</span>
                                <span class="account-number"><?php echo htmlspecialchars($store_owner['name']); ?></span>
                            </div>
                            <?php if ($payment_method === 'kareemi'): ?>
                                <?php foreach ($payment_info['accounts'] as $currency => $account): ?>
                                <div class="payment-account">
                                    <span class="account-type">كريمي (<?php echo $currency; ?>):</span>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span class="account-number"><?php echo $account; ?></span>
                                        <button class="copy-btn" onclick="copyToClipboard('<?php echo $account; ?>')">
                                            <i class="fas fa-copy"></i> نسخ
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <div class="payment-account">
                                    <span class="account-type">كود الخدمة:</span>
                                    <span class="service-code-badge"><?php echo $payment_info['service_code']; ?></span>
                                </div>
                            <?php else: ?>
                                <div class="payment-account">
                                    <span class="account-type">رقم الحساب:</span>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span class="account-number"><?php echo $payment_info['account']; ?></span>
                                        <button class="copy-btn" onclick="copyToClipboard('<?php echo $payment_info['account']; ?>')">
                                            <i class="fas fa-copy"></i> نسخ
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="payment-account">
                                <span class="account-type">رقم التواصل:</span>
                                <span class="account-number"><?php echo $store_owner['phone']; ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- الفاتورة -->
                    <div class="invoice-section" id="invoice">
                        <div class="invoice-header">
                            <div class="store-info">
                                <h2><?php echo htmlspecialchars($settings['store_name']); ?></h2>
                                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($settings['store_phone'] ?? 'غير محدد'); ?></p>
                                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($settings['store_email'] ?? 'غير محدد'); ?></p>
                            </div>
                            <div class="invoice-meta">
                                <h1 class="invoice-title">فاتورة شراء</h1>
                                <p>تاريخ الإصدار: <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
                            </div>
                        </div>

                        <div class="invoice-details">
                            <div class="customer-info">
                                <h4>معلومات العميل</h4>
                                <div class="info-row">
                                    <span class="info-label">الاسم:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">الهاتف:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($order['customer_phone']); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">البريد الإلكتروني:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($order['customer_email'] ?? 'غير محدد'); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">العنوان:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($full_address); ?></span>
                                </div>
                            </div>

                            <div class="order-info">
                                <h4>معلومات الطلب</h4>
                                <div class="info-row">
                                    <span class="info-label">رقم الفاتورة:</span>
                                    <span class="info-value"><?php echo $order['order_number'] ?? '#' . $order['id']; ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">تاريخ الطلب:</span>
                                    <span class="info-value"><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">طريقة الدفع:</span>
                                    <span class="info-value">
                                        <span class="payment-method-badge" style="border-color: <?php echo $payment_info['color']; ?>">
                                            <i class="<?php echo $payment_info['icon']; ?>" style="color: <?php echo $payment_info['color']; ?>"></i>
                                            <?php echo $payment_info['name']; ?>
                                        </span>
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">حالة الدفع:</span>
                                    <span class="info-value">
                                        <span class="status <?php echo $order['payment_status'] ?? 'pending'; ?>">
                                            <?php echo ($order['payment_method'] == 'cash') ? 'الدفع عند الاستلام' : 'بانتظار التحويل'; ?>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- عناصر الطلب -->
                        <h4>المنتجات المطلوبة</h4>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>المنتج</th>
                                    <th>السعر</th>
                                    <th>الكمية</th>
                                    <th>المجموع</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="product-info">
                                            <div class="product-image">
                                                <i class="fas fa-box"></i>
                                            </div>
                                            <span class="product-name"><?php echo htmlspecialchars($item['product_name'] ?? 'منتج'); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo number_format($item['unit_price'] ?? 0); ?> ريال</td>
                                    <td><?php echo $item['quantity'] ?? 0; ?></td>
                                    <td><?php echo number_format($item['total_price'] ?? 0); ?> ريال</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- المجموع النهائي -->
                        <div class="total-section">
                            <div class="total-row">
                                <span>المجموع الفرعي:</span>
                                <span><?php echo number_format($order['total']); ?> ريال</span>
                            </div>
                            <div class="total-row">
                                <span>رسوم التوصيل:</span>
                                <span>0 ريال</span>
                            </div>
                            <div class="total-row">
                                <span>الخصم:</span>
                                <span>0 ريال</span>
                            </div>
                            <div class="total-row grand-total">
                                <span>المجموع الكلي:</span>
                                <span><?php echo number_format($order['total']); ?> ريال يمني</span>
                            </div>
                        </div>

                        <!-- ملاحظات -->
                        <?php if (!empty($order['customer_notes'])): ?>
                        <div class="notes-section" style="margin-top: 20px; padding: 15px; background: #fffaf0; border-radius: 8px; border-right: 4px solid #f6ad55;">
                            <h5 style="margin: 0 0 10px 0; color: #dd6b20;"><i class="fas fa-sticky-note"></i> ملاحظات العميل:</h5>
                            <p style="margin: 0; color: #744210;"><?php echo htmlspecialchars($order['customer_notes']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="success-actions">
                        <button onclick="window.print()" class="btn btn-success">
                            <i class="fas fa-print"></i>
                            طباعة الفاتورة
                        </button>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i>
                            مواصلة التسوق
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-home"></i>
                            العودة للرئيسية
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="error-message">
                    <h2>لم يتم العثور على الطلب</h2>
                    <p>يرجى التحقق من رقم الطلب أو الاتصال بالدعم</p>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i>
                        العودة للرئيسية
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
        // تفريغ سلة التسوق بعد تأكيد الطلب
        document.addEventListener('DOMContentLoaded', function() {
            // مسح السلة من localStorage
            localStorage.removeItem('cart');
            
            // تحديث عداد السلة
            updateCartCount();
            
            // إضافة تأثيرات عند التحميل
            const successMessage = document.querySelector('.success-message');
            if (successMessage) {
                successMessage.style.opacity = '0';
                successMessage.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    successMessage.style.transition = 'all 0.8s ease';
                    successMessage.style.opacity = '1';
                    successMessage.style.transform = 'translateY(0)';
                }, 100);
            }
        });

        // تحديث عداد السلة
        function updateCartCount() {
            const cartCount = document.getElementById('cartCount');
            if (cartCount) {
                cartCount.textContent = '0';
            }
        }

        // نسخ الرقم إلى الحافظة
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // إظهار رسالة نجاح
                const originalText = event.target.textContent;
                event.target.innerHTML = '<i class="fas fa-check"></i> تم النسخ!';
                event.target.style.background = '#27ae60';
                
                setTimeout(() => {
                    event.target.innerHTML = '<i class="fas fa-copy"></i> نسخ';
                    event.target.style.background = '#4299e1';
                }, 2000);
            }).catch(function(err) {
                console.error('Failed to copy: ', err);
                alert('تعذر نسخ الرقم، يرجى نسخه يدوياً');
            });
        }

        // إرسال طلب إضافي لتفريغ السلة من السيرفر
        fetch('clear_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('تم تفريغ سلة التسوق:', data);
        })
        .catch(error => {
            console.error('خطأ في تفريغ السلة:', error);
        });
        // محاولة إرسال إشعار واتساب تلقائي بعد تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    // انتظر ثانيتين ثم حاول إرسال الإشعار
    setTimeout(function() {
        sendAutoWhatsAppNotification();
    }, 2000);
});

function sendAutoWhatsAppNotification() {
    // إرسال طلب خفي لإشعار الواتساب
    fetch('send_auto_notification.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            order_id: <?php echo $order['id']; ?>,
            customer_name: '<?php echo $order['customer_name']; ?>',
            customer_phone: '<?php echo $order['customer_phone']; ?>',
            total: <?php echo $order['total']; ?>,
            payment_method: '<?php echo $order['payment_method']; ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('تم إرسال إشعار الواتساب:', data);
    })
    .catch(error => {
        console.error('خطأ في إرسال الإشعار:', error);
    });
}
    </script>
</body>
</html>