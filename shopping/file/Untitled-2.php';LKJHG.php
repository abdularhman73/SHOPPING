<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "shopping";

// إنشاء الاتصال
$conn = mysqli_connect($host, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn) {
    echo "اتصال ناجح في قاعدة البيانات";
} else {
    echo "لم يتم اتصال ناجح في قاعدة البيانات: " . mysqli_connect_error();
}

?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الصفحة الرئيسية</title>
    <link rel="stylesheet" href="../style.css">
     <link rel="stylesheet" href="./style.css">
      <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
            <img src="img\shopping7.jpg" alt="Logo">
        </div>
        <div class="search">
            <div class="search_bar">
                <form action="" method="get">
                    <input type="text" class="search_input" name="search" placeholder="ادخل كلمة البحث">
                    <button class="button_search" name="btn_search">البحث</button>
                </form>
            </div>
        </div>
    </header>
    
    <nav>
        <div class="social">
            <ul>
                <li><a href="" target="_blank"><i class="fa-brands fa-facebook"></i></a></li>
                <li><a href="" target="_blank"><i class="fa-brands fa-facebook-messenger"></i></a></li>
                <li><a href="" target="_blank"><i class="fa-brands fa-square-instagram"></i></a></li>
                <li><a href="" target="_blank"><i class="fa-brands fa-youtube"></i></a></li>
                <li><a href="" target="_blank"><i class="fa-brands fa-telegram"></i></a></li>
            </ul>
        </div>
       </nav>
        <div class="section">
            <ul>
                <li><a href="index.php">الصفحة الرئيسية</a></li>
                <?php
                $query="SELECT * FROM  section";
                $result=mysqli_query($conn, $query);
                 while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                     <li><a href="section.php?section=<?php echo $row['sectionname'];?>">
                    <?php echo $row['sectionname'];?></a></li>
                <?php
                 }
                 ?>

        </div>
    
    <div class="last-post">
        <h4>المنتجات الحديثة55</h4>
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
</n>
</html>