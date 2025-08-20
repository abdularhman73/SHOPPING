<?php
session_start();
require 'include/connected.php';

if (!isset($_SESSION['user_id'])) {
    echo '<script>alert("يرجى تسجيل الدخول أولاً لإضافة المنتج إلى السلة");
          window.location.href="user/login.php"</script>';
    exit();
}

$user_id = $_SESSION['user_id'];
if($user_id <= 0){
    echo '<script>alert("مستخدم غير صحيح");
          window.location.href="user/login.php";</script>';
    exit();
}

// جلب المنتجات
$products = [];
$query = "SELECT p.*, s.sectionname 
          FROM product p
          LEFT JOIN section s ON p.prosection = s.section_id
          WHERE p.prounv = 'متوفر'
          ORDER BY p.product_id DESC LIMIT 8";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}

function getImagePath($imageName) {
    $basePaths = ['uploads/img/', '../uploads/img/', 'img/'];
    foreach ($basePaths as $basePath) {
        if (file_exists($basePath . $imageName)) {
            return $basePath . $imageName;
        }
    }
    return 'img/default-product.jpg';
}

include('file/hedar.php');
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>أحدث المنتجات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .section-title {
            text-align: center;
            color: #2c3e50;
            margin: 20px 0;
            font-size: 24px;
        }
        
        .products-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        
        .Product {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .Product:hover {
            transform: translateY(-5px);
        }
        
        .Product-img {
            position: relative;
            height: 200px;
            overflow: hidden;
        }
        
        .Product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .Product:hover .Product-img img {
            transform: scale(1.05);
        }
        
        .unvailable {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .Product-info {
            padding: 15px;
        }
        
        .Product_section a {
            color: #7f8c8d;
            font-size: 14px;
            text-decoration: none;
        }
        
        .Product_section a:hover {
            color: #3498db;
        }
        
        .Product_name a {
            color: #2c3e50;
            font-weight: bold;
            text-decoration: none;
            display: block;
            margin: 10px 0;
        }
        
        .Product_price {
            color: #27ae60;
            font-weight: bold;
            font-size: 18px;
        }
        
        .Product_description a {
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            margin-top: 10px;
        }
        
        .qty_input {
            margin-top: 15px;
        }
        
        .add-to-cart-form {
            display: flex;
            flex-direction: column;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        
        .qty-btn {
            background: #3498db;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
        }
        
        .qty-btn:hover {
            background: #2980b9;
        }
        
        .qty-input {
            width: 50px;
            height: 30px;
            text-align: center;
            margin: 0 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .addto_cart {
            background: #27ae60;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .addto_cart:hover {
            background: #219653;
        }
        
        .addto_cart:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    
    <h2 class="section-title">أحدث المنتجات</h2>
    <div class="products-container">
        <?php foreach ($products as $product): 
            $section_name = $product['sectionname'] ?? 'غير محدد';
            $imageUrl = getImagePath($product['proimg'] ?? '');
            $is_available = ($product['prounv'] ?? '') == 'متوفر';
            $max_quantity = min($product['proqty'] ?? 7, 7);
        ?>
        <div class="Product">
            <div class="Product-img">
                <a href="product_details.php?id=<?= htmlspecialchars($product['product_id']) ?>">
                    <img src="<?= htmlspecialchars($imageUrl) ?>" 
                         alt="<?= htmlspecialchars($product['proname'] ?? '') ?>" 
                         onerror="this.src='img/default-product.jpg'">
                </a>
                <?php if (!$is_available): ?>
                    <span class="unvailable">غير متوفر</span>
                <?php endif; ?>
            </div>
        
            <div class="Product-info">
                <div class="Product_section">
                    <a href="products.php?section=<?= htmlspecialchars($product['prosection'] ?? '') ?>">
                        <?= htmlspecialchars($section_name) ?>
                    </a>
                </div>
                <div class="Product_name">
                    <a href="product_details.php?id=<?= htmlspecialchars($product['product_id']) ?>">
                        <?= htmlspecialchars($product['proname'] ?? '') ?>
                    </a>
                </div>
                <div class="Product_price">السعر: <?= number_format($product['proprice'] ?? 0, 2) ?> ر.س</div>
                <div class="Product_description">
                    <a href="product_details.php?id=<?= htmlspecialchars($product['product_id']) ?>">
                        <i class="fas fa-eye"></i> تفاصيل المنتج
                    </a>
                </div>
                <br>
                <div class="qty_input">
                    <form action="cart.php" method="POST" class="add-to-cart-form">
                        <div class="quantity-control">
                            <button type="button" class="qty-btn minus">-</button>
                            <input type="number" name="quantity" value="1" min="1" 
                                   max="<?= $max_quantity ?>" class="qty-input">
                            <button type="button" class="qty-btn plus">+</button>
                        </div>
                        
                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                        <input type="hidden" name="h_name" value="<?= htmlspecialchars($product['proname']) ?>">
                        <input type="hidden" name="h_price" value="<?= $product['proprice'] ?>">
                        <input type="hidden" name="h_img" value="<?= htmlspecialchars($product['proimg']) ?>">
                        
                        <button type="submit" name="add" class="addto_cart" <?= !$is_available ? 'disabled' : '' ?>>
                            <i class="fas fa-cart-plus"></i>
                            <?= $is_available ? 'أضف إلى السلة' : 'غير متوفر' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.quantity-control').forEach(control => {
            const minus = control.querySelector('.minus');
            const plus = control.querySelector('.plus');
            const input = control.querySelector('.qty-input');
            const max = parseInt(input.getAttribute('max'));
            
            minus.addEventListener('click', () => {
                if (parseInt(input.value) > 1) {
                    input.value = parseInt(input.value) - 1;
                }
            });
            
            plus.addEventListener('click', () => {
                if (parseInt(input.value) < max) {
                    input.value = parseInt(input.value) + 1;
                }
            });
        });
    });
    </script>

</body>
</html>