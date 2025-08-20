<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "shopping";

// إنشاء الاتصال
$conn = mysqli_connect($host, $username, $password, $dbname);

// التحقق من الاتصال
if (!$conn) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

// جلب الأقسام من قاعدة البيانات
$sections = [];
$query = "SELECT * FROM section ORDER BY section_id";
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sections[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الصفحة الرئيسية</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
        <link rel="stylesheet" href="./assets/css/style.css">
            <link rel="stylesheet" href="./style.css">
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
                    <input type="text" class="search_input" name="search" placeholder="ادخل كلمة البحث">
                    <button class="button_search" name="btn_search">البحث</button>
                </form>
            </div>
        </div>
    </header>
    
    <nav class="categories-nav">
        <ul>
            <?php foreach ($section as $section): ?>
                <li><a href="products.php?section=<?= $section['section_id'] ?>">
                    <?= $section['sectionname'] ?>
                </a></li>
            <?php endforeach; ?>
            <li><a href=" adminpanel.php">لوحة التحكم</a></li>
        </ul>
    </nav>
    
    <nav>
        <div class="social">
           <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> الرئيسية</a></li>
                <li><a href="admin/product.php"><i class="fas fa-box"></i> المنتجات</a></li>
                <li><a href="sections.php"><i class="fas fa-list"></i> الأقسام</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> الطلبات</a></li>
                <li><a href="admin/users.php"><i class="fas fa-users"></i> المستخدمين</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
            </ul>
        </div>
    </nav>
    
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
                        <img src="../uploads/img/<?= $product['proimg'] ?>" alt="<?= $product['proname'] ?>">
                    </span>
                </a>
            </li>
            <?php
                endwhile;
            else:
                echo "<p>لا توجد منتجات متاحة حالياً</p>";
            endif;
            ?>
        </ul>
        <div class="cart">
            <ul>
                <li><a href="signup.php"><i class="fa-solid fa-user"></i></a></li>
                <li><a href="cart.php"><i class="fas fa-shopping-cart"></i></a></li>
                <span class="cart-count">1</span>
            </ul>
        </div>
    </div>
</body>
</html>