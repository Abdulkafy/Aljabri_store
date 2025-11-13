<?php
session_start();
include 'includes/config.php';

// الحصول على معرف المنتج من الرابط
$product_id = $_GET['id'] ?? 0;
$product = [];
$images = [];

if($product_id) {
    // جلب بيانات المنتج
    $sql = "SELECT * FROM products WHERE id = $product_id";
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
    }

    // جلب صور المنتج الإضافية
    $sql_images = "SELECT * FROM product_images WHERE product_id = $product_id";
    $result_images = mysqli_query($conn, $sql_images);
    if(mysqli_num_rows($result_images) > 0) {
        while($row = mysqli_fetch_assoc($result_images)) {
            $images[] = $row;
        }
    }
}

if(empty($product)) {
    die("المنتج غير موجود");
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - الجابري ستور</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="product-details">
        <div class="container">
            <div class="product-details-content">
                <!-- معرض الصور -->
                <div class="product-gallery">
                    <div class="main-image">
                        <img id="mainImage" src="assets/uploads/<?php echo $product['main_image']; ?>" alt="<?php echo $product['name']; ?>">
                    </div>
                    <div class="image-thumbnails">
                        <?php
                        // إضافة الصورة الرئيسية كأول صورة مصغرة
                        echo '<img src="assets/uploads/'.$product['main_image'].'" alt="'.$product['name'].'" class="thumbnail active" onclick="changeImage(this)">';
                        
                        // إضافة الصور الإضافية
                        foreach($images as $image) {
                            echo '<img src="assets/uploads/'.$image['image_path'].'" alt="'.$product['name'].'" class="thumbnail" onclick="changeImage(this)">';
                        }
                        ?>
                    </div>
                </div>

                <!-- معلومات المنتج -->
                <div class="product-info">
                    <h1><?php echo $product['name']; ?></h1>
                    
                    <div class="product-meta">
                        <span class="stock-status <?php echo $product['stock_quantity'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                            <?php echo $product['stock_quantity'] > 0 ? 'متوفر في المخزون' : 'غير متوفر'; ?>
                        </span>
                    </div>

                    <div class="product-price">
                        <span class="price-yer"><?php echo $product['price_yer']; ?> ريال يمني</span>
                        <span class="price-sar"><?php echo $product['price_sar']; ?> ريال سعودي</span>
                        <span class="price-usd">$<?php echo $product['price_usd']; ?></span>
                    </div>

                    <div class="product-description">
                        <h3>وصف المنتج</h3>
                        <p><?php echo nl2br($product['description']); ?></p>
                    </div>

                    <?php if($product['stock_quantity'] > 0): ?>
                    <div class="product-actions">
                        <div class="quantity-selector">
                            <label for="quantity">الكمية:</label>
                            <div class="quantity-controls">
                                <button type="button" onclick="decreaseQuantity()">-</button>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                                <button type="button" onclick="increaseQuantity()">+</button>
                            </div>
                        </div>
                        <button class="btn btn-primary add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                            إضافة إلى السلة
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="out-of-stock-message">
                        <p>هذا المنتج غير متوفر حالياً</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        function changeImage(element) {
            // تحديث الصورة الرئيسية
            document.getElementById('mainImage').src = element.src;
            
            // تحديث الصور المصغرة النشطة
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            element.classList.add('active');
        }

        function increaseQuantity() {
            const quantityInput = document.getElementById('quantity');
            const maxQuantity = <?php echo $product['stock_quantity']; ?>;
            if(parseInt(quantityInput.value) < maxQuantity) {
                quantityInput.value = parseInt(quantityInput.value) + 1;
            }
        }

        function decreaseQuantity() {
            const quantityInput = document.getElementById('quantity');
            if(parseInt(quantityInput.value) > 1) {
                quantityInput.value = parseInt(quantityInput.value) - 1;
            }
        }
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html>