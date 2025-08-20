<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>متجرنا</title>
    <link rel="stylesheet" href="assets/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">متجرنا</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">المنتجات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">من نحن</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">اتصل بنا</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user me-1"></i> حسابي
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="cart.php">
                                <i class="fas fa-shopping-cart me-1"></i> السلة
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count">
                                    <?= $_SESSION['cart_count'] ?? 0 ?>
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i> تسجيل خروج
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i> تسجيل دخول
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus me-1"></i> تسجيل جديد
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>