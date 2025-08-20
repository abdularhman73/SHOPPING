
<?php
// بداية الجلسة (مرة واحدة فقط)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// المسار الصحيح لملف الاتصال
$connectionFile = __DIR__ . '/../include/connected.php';
if (!file_exists($connectionFile)) {
    die("ملف الاتصال بقاعدة البيانات غير موجود: " . $connectionFile);
}
require_once($connectionFile);

if (!isset($_SESSION['user_id'])) {
    echo '<script>alert("يرجى تسجيل الدخول أولاً لإضافة المنتج إلى السلة"); window.location.href="user/login.php";</script>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الصفحة الرئيسية</title>
    <!-- توحيد روابط CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        /* أنماط إضافية لقائمة الأقسام */
        .categories-nav {
            background-color: #c73754ff;
            padding: 10px 0;
            margin-bottom: 20px;
        }
        .categories-nav ul {
            display: flex;
            justify-content: center;
            list-style: none;
            flex-wrap: wrap;
            margin: 0;
            padding: 0;
        }
        .categories-nav li {
            margin: 0 10px;
        }
        .categories-nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 5px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .categories-nav a:hover {
            background-color: #e69100;
        }
        
        /* تحسينات للعرض على الأجهزة الصغيرة */
        @media (max-width: 768px) {
            .categories-nav ul {
                flex-direction: column;
                align-items: center;
            }
            .categories-nav li {
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <h1>Shopping</h1>
            <img src="img/shopping7.jpg" alt="Logo">
        </div>
        <div class="search">
            <div class="search_bar">
                <form action="search.php" method="get">
                    <input type="text" class="search_input" name="search" placeholder="ادخل كلمة البحث" required>
                    <button type="submit" class="button_search" name="btn_search">البحث</button>
                </form>
            </div>
        </div>
    </header>
    
    <nav>
        <div class="social">
            <ul>
                <li><a href="https://www.facebook.com/profile.php?id=61553336953926" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-facebook"></i></a></li>
                <li><a href="#" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-facebook-messenger"></i></a></li>
                <li><a href="#" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-square-instagram"></i></a></li>
                <li><a href="https://youtube.com/@abdularhman-r7g?si=qut7q3hbTGfGYrnw" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-youtube"></i></a></li>
                <li><a href="#" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-telegram"></i></a></li>
                <li><a href="https://www.snapchat.com/add/user862739487?share_id=LWvhTzkWytk&locale=ar-YE" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-snapchat"></i></a></li>
            </ul>
        </div>
    </nav>
    
    <div class="section">
        <ul>
            <li><a href="index.php">الصفحة الرئيسية</a></li>
            <?php
            // جلب الأقسام من قاعدة البيانات
            $query = "SELECT * FROM section";
            $result = mysqli_query($conn, $query);
            
            if ($result && mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)):
            ?>
            <li>
                <a href="section.php?section=<?= htmlspecialchars($row['sectionname']) ?>">
                    <?= htmlspecialchars($row['sectionname']) ?>
                </a>
            </li>
            <?php
                endwhile;
            else:
                echo "<li>لا توجد أقسام متاحة</li>";
            endif;
            ?>
        </ul>
    </div>
    
    <div class="user-cart-section">
        <ul>
            <li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="user/logout.php"><i class="fa-solid fa-user"></i> حسابي</a>
                <?php else: ?>
                    <a href="user/login.php"><i class="fa-solid fa-user"></i> تسجيل الدخول</a>
                <?php endif; ?>
            </li>
            
            <li class="cart-icon">
                <a href="cart.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">
                        <?php 
                        if(isset($_SESSION['user_id'])) {
                            $count_query = "SELECT SUM(quantity) FROM cart WHERE user_id = ?";
                            $stmt = $conn->prepare($count_query);
                            $stmt->bind_param("i", $_SESSION['user_id']);
                            $stmt->execute();
                            $count = $stmt->get_result()->fetch_row()[0] ?? 0;
                            echo $count;
                            $stmt->close();
                        } else {
                            echo '0';
                        }
                        ?>
                    </span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="last-post">
        <h4>المنتجات الحديثة</h4>
        <ul>
            <?php
            // جلب آخر 3 منتجات مضافة
            $product_query = "SELECT * FROM product ORDER BY product_id DESC LIMIT 3";
            $product_result = mysqli_query($conn, $product_query);
            
            if ($product_result && mysqli_num_rows($product_result) > 0):
                while ($product = mysqli_fetch_assoc($product_result)):
            ?>
            <li>
                <a href="product_details.php?id=<?= $product['product_id'] ?>">
                    <span class="span-img">
                        <img src="../uploads/img/<?= htmlspecialchars($product['proimg']) ?>" 
                             alt="<?= htmlspecialchars($product['proname']) ?>"
                             loading="lazy">
                    </span>
                </a>
            </li>
            <?php
                endwhile;
            else:
                echo "<li>لا توجد منتجات متاحة حالياً</li>";
            endif;
            
            // إغلاق اتصال قاعدة البيانات
            mysqli_close($conn);
            ?>
        </ul>
    </div>
</body>
</html>