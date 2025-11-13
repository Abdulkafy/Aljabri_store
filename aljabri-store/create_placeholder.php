<?php
// create_placeholder.php - إنشاء صورة بديلة تلقائياً

// المسار الذي سيتم حفظ الصورة فيه
$placeholder_path = "assets/images/placeholder.jpg";

// تأكد من وجود المجلد
if (!is_dir("assets/images")) {
    mkdir("assets/images", 0777, true);
}

// إنشاء صورة بديلة
$width = 400;
$height = 400;

// إنشاء صورة جديدة
$image = imagecreate($width, $height);

// تخصيص الألوان
$background_color = imagecolorallocate($image, 240, 240, 240); // خلفية رمادية فاتحة
$border_color = imagecolorallocate($image, 200, 200, 200);     // إطار رمادي
$text_color = imagecolorallocate($image, 150, 150, 150);       // نص رمادي

// رسم خلفية
imagefill($image, 0, 0, $background_color);

// رسم إطار
imagerectangle($image, 5, 5, $width-6, $height-6, $border_color);

// إضافة نص
$text = "No Image";
$font_size = 5;
$text_width = imagefontwidth($font_size) * strlen($text);
$text_height = imagefontheight($font_size);
$x = ($width - $text_width) / 2;
$y = ($height - $text_height) / 2;

// كتابة النص
imagestring($image, $font_size, $x, $y, $text, $text_color);

// حفظ الصورة
if (imagejpeg($image, $placeholder_path, 85)) {
    echo "<h2 style='color: green;'>✅ تم إنشاء الصورة البديلة بنجاح!</h2>";
    echo "<p>تم حفظ الصورة في: <strong>$placeholder_path</strong></p>";
    echo "<img src='$placeholder_path' alt='صورة بديلة' style='border: 2px solid #ccc; margin: 10px;'>";
} else {
    echo "<h2 style='color: red;'>❌ فشل في إنشاء الصورة البديلة</h2>";
}

// تحرير الذاكرة
imagedestroy($image);

echo "<br><a href='test_images.php'>الذهاب لفحص الصور</a> | <a href='index.php'>العودة للرئيسية</a>";
?>