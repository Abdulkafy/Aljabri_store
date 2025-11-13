<?php
if($_FILES) {
    $target_dir = "assets/uploads/";
    $target_file = $target_dir . basename($_FILES["file"]["name"]);
    
    if(move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        echo "✅ رفع الملف ناجح";
    } else {
        echo "❌ فشل في رفع الملف";
    }
}
?>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="file">
    <input type="submit" value="رفع">
</form>