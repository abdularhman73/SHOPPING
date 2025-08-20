<?php
session_start();
include('../../include/connected.php');

// تحقق من صلاحيات المدير
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: adminpanel.php');
    exit;
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($product_id > 0) {
    // جلب معلومات المنتج لحذف الصورة
    $query = "SELECT proimg FROM product WHERE product_id = $product_id";
    $result = mysqli_query($conn, $query);
    
    if($result && mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
        $imagePath = "../../uploads/img/" . $product['proimg'];
        
        // حذف المنتج من قاعدة البيانات
        $query = "DELETE FROM product WHERE product_id = $product_id";
        $result = mysqli_query($conn, $query);
        
        if($result) {
            // حذف صورة المنتج إذا تم حذف المنتج بنجاح
            if(file_exists($imagePath)) {
                unlink($imagePath);
            }
            $_SESSION['message'] = "تم حذف المنتج بنجاح";
        } else {
            $_SESSION['message'] = "حدث خطأ أثناء حذف المنتج: " . mysqli_error($conn);
        }
    }
}

header('Location: products.php');
exit;
?>