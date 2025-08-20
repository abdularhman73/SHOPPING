<?php 
session_start();

// 1. تضمين ملف الاتصال بقاعدة البيانات
require_once('include/connected.php');

// 2. التحقق من اتصال قاعدة البيانات وإعادة الاتصال إذا لزم الأمر
if(!$conn || !$conn->ping()) {
    include('include/connected.php');
    if(!$conn) {
        die("فشل الاتصال بقاعدة البيانات");
    }
}

// 3. تضمين رأس الصفحة
include('file/hedar.php');
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض المنتجات حسب القسم</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* أنماط CSS الخاصة بك هنا (نفس الموجود في النسخة السابقة) */
        .products-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; padding: 20px; }
        .product-card { border: 1px solid #ddd; padding: 15px; border-radius: 8px; text-align: center; transition: transform 0.3s, box-shadow 0.3s; }
        /* بقية الأنماط... */
    </style>
</head>
<body>
    <main>
        <?php
        if(isset($_GET['section'])) {
            // 4. التحقق مرة أخرى من الاتصال قبل الاستخدام
            if(!$conn->ping()) {
                include('include/connected.php');
            }
            
            try {
                // 5. استخدام التحضير المسبق (Prepared Statement) بدلاً من mysqli_real_escape_string
                $stmt = $conn->prepare("SELECT section_id, section_description FROM section WHERE sectionname = ?");
                $stmt->bind_param("s", $_GET['section']);
                $stmt->execute();
                $section_result = $stmt->get_result();
                
                if($section_result->num_rows > 0) {
                    $section_row = $section_result->fetch_assoc();
                    $section_id = $section_row['section_id'];
                    $section_description = $section_row['section_description'];
                    
                    echo '<div class="section-title">';
                    echo '<h1>قسم: ' . htmlspecialchars($_GET['section']) . '</h1>';
                    if(!empty($section_description)) {
                        echo '<p>' . htmlspecialchars($section_description) . '</p>';
                    }
                    echo '</div>';
                    
                    // 6. استعلام المنتجات باستخدام التحضير المسبق
                    $product_stmt = $conn->prepare("SELECT * FROM product WHERE prosection = ? AND prounv != 'غير متوفر' ORDER BY proname ASC");
                    $product_stmt->bind_param("i", $section_id);
                    $product_stmt->execute();
                    $result = $product_stmt->get_result();
                    
                    if($result->num_rows > 0) {
                        echo '<div class="products-list">';
                        
                        while($row = $result->fetch_assoc()) {
                            $product_id = $row['product_id'] ?? 0;
                            $proname = htmlspecialchars($row['proname'] ?? '');
                            $proimg = htmlspecialchars($row['proimg'] ?? 'default.jpg');
                            $proprice = number_format($row['proprice'] ?? 0.00, 2);
                            $prounv = $row['prounv'] ?? '0';
                            $prodesc = htmlspecialchars(substr($row['prodesc'] ?? '', 0, 50) . '...');
                            
                            echo '
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="../uploads/img/'.$proimg.'" alt="'.$proname.'" loading="lazy">
                                    '.($prounv == 'غير متوفر' ? '<span class="unavailable">غير متوفر</span>' : '').'
                                </div>
                                <div class="product-info">
                                    <h3><a href="product.php?id='.$product_id.'">'.$proname.'</a></h3>
                                    <p>'.$prodesc.'</p>
                                    <div class="price">'.$proprice.' ر.س</div>
                                    <a href="product_details.php?id='.$product_id.'" class="details-link">
                                        <i class="fa-solid fa-eye"></i> تفاصيل المنتج
                                    </a>
                                    <div class="actions">
                                        <button class="add-to-cart" data-id="'.$product_id.'">
                                            <i class="fa-solid fa-cart-plus"></i> أضف إلى السلة
                                        </button>
                                    </div>
                                </div>
                            </div>';
                        }
                        
                        echo '</div>';
                    } else {
                        echo '<div class="no-results">
                                <i class="fa-solid fa-search" style="font-size: 40px; margin-bottom: 15px;"></i><br>
                                لا توجد منتجات متاحة في القسم المحدد
                              </div>';
                    }
                } else {
                    echo '<div class="no-results">
                            <i class="fa-solid fa-exclamation-triangle" style="font-size: 40px; margin-bottom: 15px;"></i><br>
                            القسم غير موجود
                          </div>';
                }
            } catch (Exception $e) {
                echo '<div class="no-results">
                        <i class="fa-solid fa-exclamation-circle" style="font-size: 40px; margin-bottom: 15px;"></i><br>
                        حدث خطأ في استرجاع البيانات: ' . htmlspecialchars($e->getMessage()) . '
                      </div>';
            }
        } else {
            echo '<div class="no-results">
                    <i class="fa-solid fa-exclamation-triangle" style="font-size: 40px; margin-bottom: 15px;"></i><br>
                    لم يتم تحديد قسم
                  </div>';
        }
        ?>
    </main>
    
    <?php include('file/faoutr.php'); ?>
    
    <script>
    // إضافة عناصر إلى السلة
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('تمت إضافة المنتج إلى السلة بنجاح');
                    // تحديث عداد السلة إذا كان موجوداً
                    if(typeof updateCartCount === 'function') {
                        updateCartCount();
                    }
                } else {
                    alert(data.message || 'حدث خطأ أثناء إضافة المنتج إلى السلة');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ في الاتصال بالخادم');
            });
        });
    });
    </script>
</body>
</html>