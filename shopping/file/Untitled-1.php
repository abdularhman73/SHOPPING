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
    <link rel="stylesheet" href="./style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
        <div class="section">
            <ul>
                <li><a href="sopping/admin/adminpanel.php">الصفحة الرئيسية</a></li>
                <li><a href="#">عطور</a></li>
                <li><a href="#">مجوهرات</a></li>
                <li><a href="#">الكترونيات</a></li>
                
                    <?php
                    $query="SELECT * from section";
                    $result=mysqli_query($conn,$query);
                    while($row=mysqli_fetch_assoc($result)){
                        ?>
                        <li><a href="#"><?php echo $rew['secionname'];?></a></li>
                <?Php
                    }
                    ?>
            </ul>
        </div>
    </nav>
    <div class="last-post">
        <h4>المنتجات الحديثة</h4>
        <ul>
            <li><a href="product1.html">
                <span class="span-img">
                    <img src="img\shopping8.jpg"alt="Product 1">
                </span>
            </a></li>
            <li><a href="product2.html">
                <span class="span-img">
                    <img src="img\shopping5.jpg" alt="Product 2">
                </span>
            </a></li>
            <li><a href="product3.html">
                <span class="span-img">
                    <img src="img\shopping12.jpg"alt="Product 3">
                </span>
            </a></li>
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