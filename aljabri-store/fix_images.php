<?php
// fix_images.php - حل سريع لمشكلة الصور
echo "<h2>🔧 إصلاح مشكلة الصور في متجر الجابري</h2>";

// 1. إنشاء المجلدات المطلوبة
$folders = [
    'assets/uploads',
    'assets/images',
    'assets/css',
    'assets/js'
];

foreach($folders as $folder) {
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
        echo "<p style='color: green;'>✅ تم إنشاء مجلد: <strong>$folder</strong></p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ مجلد <strong>$folder</strong> موجود بالفعل</p>";
    }
}

// 2. تحميل صورة بديلة من الإنترنت
$placeholder_url = "https://via.placeholder.com/300x200/4CAF50/FFFFFF?text=جارٍ+التحميل";
$placeholder_path = "assets/images/placeholder.jpg";

$image_data = @file_get_contents($placeholder_url);
if ($image_data) {
    file_put_contents($placeholder_path, $image_data);
    echo "<p style='color: green;'>✅ تم تحميل الصورة البديلة</p>";
} else {
    // إذا فشل التحميل، أنشئ صورة SVG بديلة
    $svg_content = '<svg width="300" height="200" xmlns="http://www.w3.org/2000/svg">
        <rect width="100%" height="100%" fill="#f0f0f0"/>
        <rect x="10" y="10" width="280" height="180" fill="none" stroke="#ccc" stroke-width="2" stroke-dasharray="5,5"/>
        <text x="50%" y="45%" text-anchor="middle" font-family="Arial" font-size="20" fill="#666">🖼️</text>
        <text x="50%" y="60%" text-anchor="middle" font-family="Arial" font-size="16" fill="#666">لا توجد صورة</text>
        <text x="50%" y="75%" text-anchor="middle" font-family="Arial" font-size="12" fill="#999">No Image</text>
    </svg>';
    
    file_put_contents("assets/images/placeholder.svg", $svg_content);
    echo "<p style='color: orange;'>⚠️ تم إنشاء صورة SVG بديلة</p>";
}

// 3. إنشاء ملف CSS أساسي
$css_content = '/* ملف التنسيق الأساسي */
:root {
    --primary-color: #4CAF50;
    --secondary-color: #FF9800;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, Tahoma, sans-serif;
    line-height: 1.6;
    color: #333;
    background: #f8f9fa;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

/* الهيدر */
.header {
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 1rem 0;
}

.header .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo h1 {
    color: var(--primary-color);
}

.nav ul {
    list-style: none;
    display: flex;
    gap: 2rem;
}

.nav a {
    text-decoration: none;
    color: #333;
    font-weight: bold;
}

/* المنتجات */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.product-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s ease;
}

.product-card:hover {
    transform: translateY(-5px);
}

.product-image {
    width: 100%;
    height: 200px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-info {
    padding: 1rem;
}

.product-info h3 {
    color: #333;
    margin-bottom: 0.5rem;
}

.product-price {
    font-size: 1.2rem;
    font-weight: bold;
    color: var(--primary-color);
    margin: 0.5rem 0;
}

.product-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.btn {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    flex: 1;
}

.btn-secondary {
    background: #6c757d;
}

/* الهيرو */
.hero {
    background: linear-gradient(135deg, var(--primary-color), #45a049);
    color: white;
    padding: 4rem 0;
    margin: 2rem 0;
}

.hero .container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: center;
}

/* البارات */
.announcement-bar {
    background: var(--secondary-color);
    color: white;
    padding: 0.5rem 0;
    overflow: hidden;
}

.announcement-content {
    display: flex;
    animation: scroll 20s linear infinite;
    white-space: nowrap;
}

.announcement-content span {
    margin: 0 2rem;
    font-weight: bold;
}

@keyframes scroll {
    0% { transform: translateX(100%); }
    100% { transform: translateX(-100%); }
}

/* الفوتر */
.footer {
    background: #333;
    color: white;
    padding: 2rem 0;
    margin-top: 3rem;
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.footer-bottom {
    border-top: 1px solid #555;
    padding-top: 1rem;
    margin-top: 2rem;
    text-align: center;
}';

file_put_contents("assets/css/style.css", $css_content);
echo "<p style='color: green;'>✅ تم إنشاء ملف التنسيق CSS</p>";

// 4. إنشاء ملف JS أساسي
$js_content = '// ملف الجافاسكربت الأساسي
console.log("متجر الجابري - جاهز للتشغيل");';

file_put_contents("assets/js/main.js", $js_content);
echo "<p style='color: green;'>✅ تم إنشاء ملف الجافاسكربت</p>";

echo "<hr>";
echo "<h3 style='color: green;'>🎉 تم إصلاح المشكلة بنجاح!</h3>";
echo "<p>الآن يمكنك <a href='index.php' style='color: blue;'>العودة للصفحة الرئيسية</a> لمشاهدة النتائج</p>";

// 5. عرض معاينة للصورة البديلة
if(file_exists("assets/images/placeholder.jpg")) {
    echo "<h4>معاينة الصورة البديلة:</h4>";
    echo "<img src='assets/images/placeholder.jpg' alt='صورة بديلة' style='border: 2px solid #ccc; max-width: 300px;'>";
} elseif(file_exists("assets/images/placeholder.svg")) {
    echo "<h4>معاينة الصورة البديلة (SVG):</h4>";
    echo "<div style='border: 2px solid #ccc; width: 300px; height: 200px;'>";
    echo file_get_contents("assets/images/placeholder.svg");
    echo "</div>";
}
?>