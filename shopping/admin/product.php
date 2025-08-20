<?php
session_start();

// اتصال قاعدة البيانات
$host = "localhost";
$username = "root";
$password = "";
$dbname = "shopping";

$conn = mysqli_connect($host, $username, $password, $dbname);
if (!$conn) {
    die("فشل الاتصال: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// عملية الحذف
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    // التحقق من وجود المنتج أولاً
    $check_sql = "SELECT product_id FROM product WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        // حذف المنتج
        $delete_sql = "DELETE FROM product WHERE product_id = ?";
        $stmt = mysqli_prepare($conn, $delete_sql);
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "تم حذف المنتج بنجاح";
        } else {
            $_SESSION['error'] = "خطأ في الحذف: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "المنتج غير موجود";
    }
    
    header("Location: product.php");
    exit();
}

// جلب جميع المنتجات
$products = [];
$sql = "SELECT * FROM product ORDER BY product_id";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("خطأ في الاستعلام: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8f9fa;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .product-img {
            max-width: 80px;
            height: auto;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="text-center mb-4">إدارة المنتجات</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>الصورة</th>
                        <th>الاسم</th>
                        <th>السعر</th>
                        <th>الحجم</th>
                        <th>المتاح</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $row['product_id'] ?></td>
                                <td>
                                    <?php if (!empty($row['proimg'])): ?>
                                        <img src="../uploads/img/<?= $row['proimg'] ?>" class="product-img">
                                    <?php else: ?>
                                        <span class="text-muted">بدون صورة</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['proname']) ?></td>
                                <td><?= number_format($row['proprice'], 2) ?> ر.س</td>
                                <td><?= $row['prosize'] ?? '-' ?></td>
                                <td><?= $row['prounv'] ?></td>
                                <td>
                                    <a href="edit_product.php?id=<?= $row['product_id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> تعديل
                                    </a>
                                    <a href="product.php?id=<?= $row['product_id'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">
                                        <i class="fas fa-trash"></i> حذف
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-box-open fa-3x mb-3"></i>
                                <p>لا توجد منتجات مسجلة</p>
                                <a href="add_product.php" class="btn btn-success">
                                    <i class="fas fa-plus"></i> إضافة منتج جديد
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>

<?php
mysqli_close($conn);
?>