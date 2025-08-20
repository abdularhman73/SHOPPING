<?php
session_start();

require '../include/connected.php';

// جلب معرّف المنتج من الرابط
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// جلب معلومات المنتج
$product = [];
if($product_id > 0) {
    $query = "SELECT p.*, s.sectionname 
              FROM product p 
              JOIN section s ON p.prosection = s.section_id 
              WHERE p.product_id = $product_id";
    $result = mysqli_query($conn, $query);
    if($result && mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
    }
}

// جلب التقييمات للمنتج
$ratings = [];
$average_rating = 0;
$user_rating = 0;
if($product_id > 0) {
    $rating_query = "SELECT r.*, u.username 
                    FROM ratings r
                    JOIN users u ON r.user_id = u.id
                    WHERE r.product_id = $product_id
                    ORDER BY r.created_at DESC";
    $rating_result = mysqli_query($conn, $rating_query);
    if($rating_result && mysqli_num_rows($rating_result) > 0) {
        while($row = mysqli_fetch_assoc($rating_result)) {
            $ratings[] = $row;
        }
    }

    // حساب متوسط التقييم
    $avg_query = "SELECT AVG(rating) as avg_rating FROM ratings WHERE product_id = $product_id";
    $avg_result = mysqli_query($conn, $avg_query);
    if($avg_result && mysqli_num_rows($avg_result) > 0) {
        $avg_row = mysqli_fetch_assoc($avg_result);
        $average_rating = round($avg_row['avg_rating'], 1);
    }

    // جلب تقييم المستخدم الحالي إذا كان مسجل الدخول
    if(isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $user_rating_query = "SELECT rating FROM ratings WHERE product_id = $product_id AND user_id = $user_id";
        $user_rating_result = mysqli_query($conn, $user_rating_query);
        if($user_rating_result && mysqli_num_rows($user_rating_result) > 0) {
            $user_rating_row = mysqli_fetch_assoc($user_rating_result);
            $user_rating = $user_rating_row['rating'];
        }
    }
}

// إذا لم يتم العثور على المنتج
if(empty($product)) {
    header('Location: ../index.php');
    exit();
}

// معالجة إرسال التقييم
if(isset($_POST['submit_rating']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $review = isset($_POST['review']) ? mysqli_real_escape_string($conn, $_POST['review']) : '';
    
    if($rating > 0 && $rating <= 5) {
        // التحقق إذا كان المستخدم قد قيم المنتج مسبقاً
        $check_query = "SELECT * FROM ratings WHERE user_id = $user_id AND product_id = $product_id";
        $check_result = mysqli_query($conn, $check_query);
        
        if(mysqli_num_rows($check_result) > 0) {
            // تحديث التقييم الموجود
            $update_query = "UPDATE ratings SET rating = $rating, review = '$review', created_at = NOW() 
                            WHERE user_id = $user_id AND product_id = $product_id";
            mysqli_query($conn, $update_query);
        } else {
            // إضافة تقييم جديد
            $insert_query = "INSERT INTO ratings (user_id, product_id, rating, review, created_at)
                            VALUES ($user_id, $product_id, $rating, '$review', NOW())";
            mysqli_query($conn, $insert_query);
        }
        
        header("Location: product_details.php?id=$product_id");
        exit();
    }
}

// معالجة إضافة المنتج للسلة
if(isset($_POST['add_to_cart']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if($quantity > 0 && $quantity <= 10) {
        // التحقق من وجود المنتج في السلة
        $check_query = "SELECT * FROM cart WHERE product_id = $product_id AND user_id = $user_id";
        $check_result = mysqli_query($conn, $check_query);
        
        if(mysqli_num_rows($check_result) > 0) {
            // تحديث الكمية إذا كان المنتج موجوداً
            $update_query = "UPDATE cart SET quantity = quantity + $quantity 
                           WHERE product_id = $product_id AND user_id = $user_id";
            mysqli_query($conn, $update_query);
        } else {
            // إضافة منتج جديد للسلة
            $insert_query = "INSERT INTO cart (user_id, product_id, quantity, added_at)
                            VALUES ($user_id, $product_id, $quantity, NOW())";
            mysqli_query($conn, $insert_query);
        }
        
        $_SESSION['cart_message'] = 'تمت إضافة المنتج إلى السلة بنجاح';
        header("Location: product_details.php?id=$product_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['proname']) ?> - تفاصيل المنتج</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        /* أنماط عامة */
        body {
            font-family: 'Tahoma', Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* أنماط تفاصيل المنتج */
        .product-details {
            display: flex;
            flex-wrap: wrap;
            margin: 20px 0;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .product-images {
            flex: 1;
            min-width: 300px;
            padding: 15px;
            text-align: center;
        }
        
        .product-images img {
            max-width: 100%;
            max-height: 500px;
            object-fit: contain;
            border-radius: 8px;
        }
        
        .product-info {
            flex: 1;
            min-width: 300px;
            padding: 15px;
        }
        
        .product-info h1 {
            color: #c73754;
            margin-bottom: 20px;
            font-size: 28px;
        }
        
        .product-meta {
            margin-bottom: 15px;
        }
        
        .section-link {
            color: #e69100;
            text-decoration: none;
        }
        
        .section-link:hover {
            text-decoration: underline;
        }
        
        .price {
            font-size: 24px;
            color: #e69100;
            font-weight: bold;
            margin: 15px 0;
        }
        
        .availability {
            margin: 15px 0;
        }
        
        .available {
            color: green;
            font-weight: bold;
        }
        
        .unavailable {
            color: red;
            font-weight: bold;
        }
        
        .sizes, .description {
            margin: 20px 0;
        }
        
        .sizes h3, .description h3 {
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        /* أنماط إضافة للسلة */
        .add-to-cart {
            margin: 25px 0;
        }
        
        .qty_input {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }
        
        .qty_input button {
            background: #c73754;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 4px;
        }
        
        .qty_input input {
            width: 60px;
            height: 30px;
            text-align: center;
            margin: 0 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .addto_cart {
            background: #e69100;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
        }
        
        .addto_cart:hover {
            background: #c73754;
        }
        
        .addto_cart i {
            margin-left: 8px;
        }
        
        /* أنماط التقييمات */
        .rating-section {
            margin-top: 40px;
            padding: 25px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .rating-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .average-rating {
            display: flex;
            align-items: center;
        }
        
        .average-rating .stars {
            color: #FFD700;
            font-size: 24px;
            margin-left: 10px;
        }
        
        .average-rating .rating-text {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .rating-count {
            color: #777;
        }
        
        .rating-form {
            margin: 25px 0;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        
        .rating-form h3 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .rating-stars {
            direction: ltr;
            margin-bottom: 15px;
        }
        
        .rating-stars input {
            display: none;
        }
        
        .rating-stars label {
            color: #ddd;
            font-size: 28px;
            cursor: pointer;
            margin: 0 3px;
            transition: color 0.2s;
        }
        
        .rating-stars input:checked ~ label {
            color: #FFD700;
        }
        
        .rating-stars label:hover,
        .rating-stars label:hover ~ label {
            color: #FFD700;
        }
        
        .review-textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 15px;
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }
        
        .submit-rating {
            background: #e69100;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        .submit-rating:hover {
            background: #c73754;
        }
        
        .login-to-rate {
            text-align: center;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 6px;
            margin: 20px 0;
        }
        
        .login-link {
            color: #e69100;
            text-decoration: none;
            font-weight: bold;
        }
        
        .login-link:hover {
            text-decoration: underline;
        }
        
        .reviews-list {
            margin-top: 30px;
        }
        
        .reviews-list h3 {
            margin-bottom: 20px;
            color: #2c3e50;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .review-item {
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            align-items: center;
        }
        
        .review-user {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .review-rating {
            color: #FFD700;
            font-size: 16px;
        }
        
        .review-date {
            color: #777;
            font-size: 14px;
        }
        
        .review-content {
            margin-top: 10px;
            line-height: 1.6;
        }
        
        .no-reviews {
            text-align: center;
            padding: 30px;
            color: #777;
        }
        
        /* رسائل التنبيه */
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .alert-success {
            background-color: #4CAF50;
        }
        
        .alert-error {
            background-color: #f44336;
        }
        
        .close-alert {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }
        
        /* التكيف مع الشاشات الصغيرة */
        @media (max-width: 768px) {
            .product-details {
                flex-direction: column;
            }
            
            .product-images, .product-info {
                min-width: 100%;
            }
            
            .rating-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .average-rating {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- الهيدر -->
    <header>
        <div class="logo">
            <h1>Shopping</h1>
            <img src="img/shopping7.jpg" alt="Logo">
        </div>
        <div class="search">
            <div class="search_bar">
                <form action="search.php" method="get">
                    <input type="text" class="search_input" name="search" placeholder="ادخل كلمة البحث">
                    <button class="button_search" name="btn_search">البحث</button>
                </form>
            </div>
        </div>
    </header>
    
    <!-- القائمة العلوية -->
    <nav>
        <div class="social">
            <ul>
                <li><a href="https://www.facebook.com/profile.php?id=61553336953926" target="_blank"><i class="fa-brands fa-facebook"></i></a></li>
                <li><a href="" target="_blank"><i class="fa-brands fa-facebook-messenger"></i></a></li>
                <li><a href="" target="_blank"><i class="fa-brands fa-square-instagram"></i></a></li>
                <li><a href="https://youtube.com/@abdularhman-r7g?si=qut7q3hbTGfGYrnw" target="_blank"><i class="fa-brands fa-youtube"></i></a></li>
                <li><a href="" target="_blank"><i class="fa-brands fa-telegram"></i></a></li>
                <li><a href="https://www.snapchat.com/add/user862739487?share_id=LWvhTzkWytk&locale=ar-YE" target="_blank"><i class="fa-brands fa-snapchat"></i></a></li>
            </ul>
        </div>
    </nav>
    
    <!-- قائمة الأقسام -->
    <div class="section">
        <ul>
            <li><a href="index.php">الصفحة الرئيسية</a></li>
            <?php
            $query = "SELECT * FROM section";
            $result = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<li><a href="section.php?section='.$row['sectionname'].'">'.$row['sectionname'].'</a></li>';
            }
            ?>
        </ul>
    </div>
    
    <!-- أيقونة السلة -->
    <div class="cart-icon">
        <a href="cart.php">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count">
                <?php 
                if(isset($_SESSION['user_id'])) {
                    $count = $conn->query("SELECT SUM(quantity) FROM cart WHERE user_id = ".$_SESSION['user_id'])->fetch_row()[0] ?? 0;
                    echo $count;
                } else {
                    echo '0';
                }
                ?>
            </span>
        </a>
    </div>

    <!-- رسائل التنبيه -->
    <?php if(isset($_SESSION['cart_message'])): ?>
        <div class="container">
            <div class="alert alert-success">
                <?= $_SESSION['cart_message'] ?>
                <button class="close-alert" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
        </div>
        <?php unset($_SESSION['cart_message']); ?>
    <?php endif; ?>

    <main class="container">
        <!-- تفاصيل المنتج -->
        <div class="product-details">
            <div class="product-images">
                <img src="../uploads/img/<?= htmlspecialchars($product['proimg']) ?>" 
                     alt="<?= htmlspecialchars($product['proname']) ?>"
                     onerror="this.src='../img/default-product.jpg'">
            </div>
            
            <div class="product-info">
                <h1><?= htmlspecialchars($product['proname']) ?></h1>
                
                <div class="product-meta">
                    <div class="section">
                        القسم: <a href="products.php?section=<?= $product['prosection'] ?>" class="section-link"><?= htmlspecialchars($product['sectionname']) ?></a>
                    </div>
                    
                    <div class="price">
                        السعر: <span><?= number_format($product['proprice'], 2) ?> ر.س</span>
                    </div>
                    
                    <div class="availability">
                        الحالة: 
                        <span class="<?= $product['prounv'] == 'متوفر' ? 'available' : 'unavailable' ?>">
                            <?= $product['prounv'] ?>
                        </span>
                    </div>
                </div>
                
                <?php if(!empty($product['prosize'])): ?>
                <div class="sizes">
                    <h3>الأحجام المتوفرة:</h3>
                    <p><?= htmlspecialchars($product['prosize']) ?></p>
                </div>
                <?php endif; ?>
                
                <div class="description">
                    <h3>وصف المنتج:</h3>
                    <p><?= nl2br(htmlspecialchars($product['prodecrip'])) ?></p>
                </div>
                
                <?php if($product['prounv'] == 'متوفر'): ?>
                <div class="add-to-cart">
                    <form method="post">
                        <input type="hidden" name="add_to_cart" value="1">
                        <div class="qty_input">
                            <button type="button" class="qty_count_mins" onclick="changeQuantity(-1)">-</button>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="10">
                            <button type="button" class="qty_count_add" onclick="changeQuantity(1)">+</button>
                        </div>
                        <button type="submit" class="addto_cart">
                            <i class="fa-solid fa-cart-plus"></i> أضف إلى السلة
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- قسم التقييمات -->
        <div class="rating-section">
            <div class="rating-header">
                <div class="average-rating">
                    <span class="rating-text">التقييم العام: <?= $average_rating ?>/5</span>
                    <div class="stars">
                        <?php
                        $full_stars = floor($average_rating);
                        $half_star = ($average_rating - $full_stars) >= 0.5;
                        
                        for($i = 1; $i <= 5; $i++) {
                            if($i <= $full_stars) {
                                echo '<i class="fas fa-star"></i>';
                            } elseif($half_star && $i == $full_stars + 1) {
                                echo '<i class="fas fa-star-half-alt"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="rating-count">
                    (<?= count($ratings) ?> تقييمات)
                </div>
            </div>
            
            <?php if(isset($_SESSION['user_id'])): ?>
            <div class="rating-form">
                <h3>أضف تقييمك</h3>
                <form method="post">
                    <div class="rating-stars">
                        <input type="radio" id="star5" name="rating" value="5" <?= $user_rating == 5 ? 'checked' : '' ?>>
                        <label for="star5" title="ممتاز"><i class="fas fa-star"></i></label>
                        
                        <input type="radio" id="star4" name="rating" value="4" <?= $user_rating == 4 ? 'checked' : '' ?>>
                        <label for="star4" title="جيد جداً"><i class="fas fa-star"></i></label>
                        
                        <input type="radio" id="star3" name="rating" value="3" <?= $user_rating == 3 ? 'checked' : '' ?>>
                        <label for="star3" title="جيد"><i class="fas fa-star"></i></label>
                        
                        <input type="radio" id="star2" name="rating" value="2" <?= $user_rating == 2 ? 'checked' : '' ?>>
                        <label for="star2" title="مقبول"><i class="fas fa-star"></i></label>
                        
                        <input type="radio" id="star1" name="rating" value="1" <?= $user_rating == 1 ? 'checked' : '' ?>>
                        <label for="star1" title="سيء"><i class="fas fa-star"></i></label>
                    </div>
                    <textarea name="review" class="review-textarea" placeholder="شاركنا تجربتك مع هذا المنتج..."><?= isset($user_review) ? htmlspecialchars($user_review) : '' ?></textarea>
                    <button type="submit" name="submit_rating" class="submit-rating">إرسال التقييم</button>
                </form>
            </div>
            <?php else: ?>
            <div class="login-to-rate">
                <p>يجب <a href="user/login.php?redirect=product_details.php?id=<?= $product_id ?>" class="login-link">تسجيل الدخول</a> لتتمكن من تقييم المنتج</p>
            </div>
            <?php endif; ?>
            
            <div class="reviews-list">
                <h3>آراء العملاء</h3>
                <?php if(!empty($ratings)): ?>
                    <?php foreach($ratings as $review): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <span class="review-user"><?= htmlspecialchars($review['username']) ?></span>
                            <span class="review-rating">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <?php if($i <= $review['rating']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </span>
                        </div>
                        <div class="review-date">
                            <?= date('Y-m-d H:i', strtotime($review['created_at'])) ?>
                        </div>
                        <?php if(!empty($review['review'])): ?>
                        <div class="review-content">
                            <?= nl2br(htmlspecialchars($review['review'])) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-reviews">
                        <p>لا توجد تقييمات لهذا المنتج بعد. كن أول من يقيم!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
    // إدارة الكمية
    function changeQuantity(change) {
        const input = document.getElementById('quantity');
        let newValue = parseInt(input.value) + change;
        
        if(newValue < parseInt(input.min)) newValue = parseInt(input.min);
        if(newValue > parseInt(input.max)) newValue = parseInt(input.max);
        
        input.value = newValue;
    }
    
    // إغلاق رسائل التنبيه بعد 5 ثواني
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.display = 'none';
        });
    }, 5000);
    
    // تحديد النجوم عند التحميل إذا كان هناك تقييم مستخدم
    document.addEventListener('DOMContentLoaded', function() {
        <?php if($user_rating > 0): ?>
        document.getElementById('star<?= $user_rating ?>').checked = true;
        <?php endif; ?>
    });
    </script>
</body>
</html>