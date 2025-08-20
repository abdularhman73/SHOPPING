<?php
// تفعيل عرض الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);

// بدء الجلسة
session_start();

// الاتصال بقاعدة البيانات (بدل connected.php)
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'shopping';

$conn = mysqli_connect($host, $user, $password, $dbname);
if (!$conn) {
    die('فشل الاتصال بقاعدة البيانات: ' . mysqli_connect_error());
}

// حذف القسم عند وجود ?delete=id في الرابط
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $query = "DELETE FROM section WHERE section_id = $id";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "تم حذف القسم بنجاح";
    } else {
        $_SESSION['error'] = "فشل في حذف القسم: " . mysqli_error($conn);
    }
    header("Location: adminpanel.php");
    exit();
}

// إضافة قسم جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['secadd'])) {
    $sectionname = trim($_POST['sectionname'] ?? '');

    if (empty($sectionname)) {
        $_SESSION['error'] = "الحقل فارغ، الرجاء إدخال اسم القسم.";
    } elseif (strlen($sectionname) > 50) {
        $_SESSION['error'] = "اسم القسم طويل جدًا. الحد الأقصى 50 حرف.";
    } else {
        $sectionname = mysqli_real_escape_string($conn, $sectionname);
        $query = "INSERT INTO section (sectionname) VALUES ('$sectionname')";
        $result = mysqli_query($conn, $query);

        if ($result) {
            $_SESSION['success'] = "تمت إضافة القسم بنجاح";
            header('Location:adminpanel.php');
            exit();
        } else {
            $_SESSION['error'] = "حدث خطأ أثناء الإضافة: " . mysqli_error($conn);
        }
    }
}

// جلب الأقسام
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
    <title>لوحة التحكم الإدارية</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        body { font-family: Arial; background-color: #fff; }
        .sidebar_container { display: flex; min-height: 100vh; }
        .sidebar { background-color: #ccc; width: 280px; height: 100vh; position: fixed; right: 0; overflow-y: auto; }
        .sidebar h1 { text-align: center; padding: 20px; }
        .sidebar ul li { padding: 15px; border-bottom: 1px solid #999; text-align: right; }
        .sidebar ul li a { color: black; display: block; }
        .sidebar ul li a:hover { background: #a19fa3; color: red; }
        .content_sec { margin-right: 280px; padding: 30px; width: calc(100% - 280px); }
        input[type="text"] { width: 100%; padding: 10px; margin: 10px 0; }
        .add { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .add:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
        .delet { background: red; color: white; padding: 8px 12px; border: none; cursor: pointer; }
        .delet:hover { background: #c00; }
        .alert { padding: 10px; margin-bottom: 20px; color: white; text-align: center; }
        .alert.success { background-color: green; }
        .alert.error { background-color: red; }
    </style>
</head>
<body>
<div class="sidebar_container">
    <div class="sidebar">
        <h1>لوحة التحكم</h1>
        <ul>
            <li><a href="../index.php"><i class="fas fa-home"></i> الصفحة الرئيسية</a></li>
            <li><a href="product.php"><i class="fas fa-tshirt"></i> المنتجات</a></li>
            <li><a href="addproduct.php"><i class="fas fa-plus-circle"></i> إضافة منتج</a></li>
            <li><a href="../user/members.php"><i class="fas fa-users"></i> الأعضاء</a></li>
            <li><a href="../cart.php"><i class="fas fa-shopping-cart"></i> الطلبات</a></li>
            <li><a class="text-danger" href="../index.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
        </ul>
    </div>

    <div class="content_sec">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form method="post" action="">
            <label>إضافة قسم جديد</label>
            <input type="text" name="sectionname" maxlength="50" required>
            <button type="submit" name="secadd" class="add">إضافة القسم</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>الرقم التسلسلي</th>
                    <th>اسم القسم</th>
                    <th>حذف القسم</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sections as $section): ?>
                <tr>
                    <td><?= $section['section_id'] ?></td>
                    <td><?= $section['sectionname'] ?></td>
                    <td>
                        <a href="adminpanel.php?delete=<?= $section['section_id'] ?>" onclick="return confirm('هل أنت متأكد من حذف هذا القسم؟')">
                            <button type="button" class="delet">حذف القسم</button>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        });
    }, 4000);
</script>
</body>
</html>