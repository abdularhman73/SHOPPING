<?php
include('file/hedar.php');

// تفعيل عرض الأخطاء للمساعدة في التصحيح
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(isset($_GET['btn_search'])) {
    // التحقق من اتصال قاعدة البيانات أولاً
    if (!$conn) {
        die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
    }

    $search = mysqli_real_escape_string($conn, $_GET['search']);
    
    // طباعة كلمة البحث للتأكد من استلامها
    echo "<script>console.log('كلمة البحث: ".$search."')</script>";
    
    $query = "SELECT * FROM product WHERE 
              prodecrip LIKE '%$search%' 
              OR proname LIKE '%$search%' 
              OR product_id LIKE '%$search%' 
              OR proprice LIKE '%$search%'";
    
    // طباعة الاستعلام للتأكد من صحته
    echo "<script>console.log('استعلام SQL: ".$query."')</script>";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        // عرض خطأ MySQL إذا فشل الاستعلام
        die("خطأ في الاستعلام: " . mysqli_error($conn));
    }
    
    if(mysqli_num_rows($result) > 0) {
        echo '<div class="products-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">';
        
        while($row = mysqli_fetch_assoc($result)) {
            // التحقق من وجود المفاتيح في المصفوفة
            $product_id = isset($row['product_id']) ? $row['product_id'] : (isset($row['id']) ? $row['id'] : '0');
            $proname = isset($row['proname']) ? $row['proname'] : '';
            $proimg = isset($row['proimg']) ? $row['proimg'] : 'default.jpg';
            $proprice = isset($row['proprice']) ? $row['proprice'] : '0.00';
            $prounv = isset($row['prounv']) ? $row['prounv'] : '0';
            
            echo '
            <div class="product-card" style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
                <div class="product-image">
                    <img src="uploads/img/'.$proimg.'" alt="'.$proname.'" style="width: 100%; height: 200px; object-fit: cover;">
                    '.($prounv == 1 ? '<span style="background: red; color: white; padding: 3px 8px; border-radius: 3px;">غير متوفر</span>' : '').'
                </div>
                <div class="product-info" style="margin-top: 10px;">
                    <h3 style="margin: 5px 0;"><a href="product.php?id='.$product_id.'">'.$proname.'</a></h3>
                    <div class="price" style="font-weight: bold; color: #e63946; margin: 5px 0;">'.$proprice.' ر.س</div>
                    <a href="product_details.php?id='.$product_id.'" style="display: inline-block; margin: 5px 0; color: #007bff;">
                        <i class="fa-solid fa-eye"></i> تفاصيل المنتج
                    </a>
                    <div class="actions" style="margin-top: 10px;">
                        <button class="add-to-cart" data-id="'.$product_id.'" style="background: #007bff; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">
                            <i class="fa-solid fa-cart-plus"></i> أضف إلى السلة
                        </button>
                    </div>
                </div>
            </div>';
        }
        
        echo '</div>';
    } else {
        echo '<div class="no-results" style="text-align: center; padding: 50px; font-size: 18px;">
                <i class="fa-solid fa-search" style="font-size: 50px; color: #ccc; margin-bottom: 20px;"></i><br>
                لم يتم العثور على أي منتج مطابق لبحثك "'.htmlspecialchars($search).'"
              </div>';
    }
}

include("file/faoutr.php");
?>