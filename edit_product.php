<?php
session_start();
include('../include/connected.php');

// تحقق من صلاحيات المدير
if(!isset($_SESSION['admin_logged_in'])) {
}

$errors = [];
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// جلب بيانات المنتج الحالية
$product = [];
if($product_id > 0) {
    $query = "SELECT * FROM product WHERE product_id = $product_id";
    $result = mysqli_query($conn, $query);
    if($result && mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
    } else {
        header('Location: product.php');
        exit;
    }
} else {
    header('Location: product.php');
    exit;
}

// جلب الأقسام
$sections = [];
$query = "SELECT * FROM section ORDER BY sectionname";
$result = mysqli_query($conn, $query);
if($result && mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $sections[] = $row;
    }
}

if(isset($_POST['update_product'])) {
    $proname = mysqli_real_escape_string($conn, $_POST['proname']);
    $proprice = mysqli_real_escape_string($conn, $_POST['proprice']);
    $prosection = mysqli_real_escape_string($conn, $_POST['prosection']);
    $prodecrip = mysqli_real_escape_string($conn, $_POST['prodecrip']);
    $prosize = mysqli_real_escape_string($conn, $_POST['prosize']);
    $prounv = mysqli_real_escape_string($conn, $_POST['prounv']);
    
    // التحقق من الحقول الفارغة
    if(empty($proname)) $errors[] = "اسم المنتج مطلوب";
    if(empty($proprice)) $errors[] = "سعر المنتج مطلوب";
    if(empty($prosection)) $errors[] = "القسم مطلوب";
    if(empty($prodecrip)) $errors[] = "وصف المنتج مطلوب";
    if(empty($prosize)) $errors[] = "الحجم مطلوب";
    if(empty($prounv)) $errors[] = "حالة التوفر مطلوبة";
    
    // معالجة صورة جديدة إذا تم تحميلها
    $proimg = $product['proimg'];
    if(isset($_FILES['proimg']) && $_FILES['proimg']['error'] == 0) {
        $imageNAME = $_FILES['proimg']['name'];
        $imageTMP = $_FILES['proimg']['tmp_name'];
        $imageEXT = strtolower(pathinfo($imageNAME, PATHINFO_EXTENSION));
        $allowedEXT = array('jpg', 'jpeg', 'png', 'gif');
        
        if(!in_array($imageEXT, $allowedEXT)) {
            $errors[] = "امتداد الصورة غير مسموح به. المسموح: JPG, JPEG, PNG, GIF";
        } else {
            $proimg = uniqid() . "_" . $imageNAME;
            $uploadPath = "../../uploads/img/" . $proimg;
            
            if(!move_uploaded_file($imageTMP, $uploadPath)) {
                $errors[] = "حدث خطأ أثناء رفع الصورة";
            } else {
                // حذف الصورة القديمة إذا تم رفع صورة جديدة بنجاح
                $oldImagePath = "../../uploads/img/" . $product['proimg'];
                if(file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
        }
    }
    
    if(empty($errors)) {
        $query = "UPDATE product SET 
                  proname = ?, 
                  proimg = ?, 
                  proprice = ?, 
                  prosection = ?, 
                  prodecrip = ?, 
                  prosize = ?, 
                  prounv = ? 
                  WHERE product_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if($stmt) {
            mysqli_stmt_bind_param($stmt, "ssdssssi", $proname, $proimg, $proprice, $prosection, $prodecrip, $prosize, $prounv, $product_id);
            $result = mysqli_stmt_execute($stmt);
            
            if($result) {
                $_SESSION['message'] = "تم تحديث المنتج بنجاح";
                header('Location: product.php');
                exit;
            } else {
                $errors[] = "حدث خطأ أثناء تحديث المنتج: " . mysqli_error($conn);
            }
        } else {
            $errors[] = "حدث خطأ في إعداد الاستعلام: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل المنتج</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* أنماط عامة */
{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background-color: #f5f5f5;
    color: #333;
}

.container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

/* أنماط الرأس */
.admin-header {
    background-color: #343a40;
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.admin-header .logo h1 {
    font-size: 24px;
}

.admin-nav ul {
    list-style: none;
    display: flex;
}

.admin-nav li {
    margin-left: 15px;
}

.admin-nav a {
    color: white;
    text-decoration: none;
    padding: 5px 10px;
    border-radius: 3px;
    transition: background-color 0.3s;
}

.admin-nav a:hover {
    background-color: #495057;
}

/* أنماط النماذج */
.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-group textarea {
    min-height: 100px;
}

/* الأزرار */
.btn {
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background-color: #0069d9;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

.btn-info {
    background-color: #17a2b8;
    color: white;
}

.btn-info:hover {
    background-color: #138496;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}

/* التنبيهات */
.alert {
    padding: 10px 15px;
    margin-bottom: 15px;
    border-radius: 4px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* الجداول */
.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.table th,
.table td {
    padding: 12px 15px;
    text-align: right;
    border-bottom: 1px solid #ddd;
}

.table th {
    background-color: #f8f9fa;
    font-weight: bold;
}

.table tr:hover {
    background-color: #f5f5f5;
}

/* إحصائيات لوحة التحكم */
.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background-color: white;
    border-radius: 5px;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
    text-align: center;
}

.stat-card h3 {
    color: #6c757d;
    margin-bottom: 10px;
}

.stat-card p {
    font-size: 24px;
    font-weight: bold;
    color: #343a40;
    margin-bottom: 15px;
}

.stat-card a {
    color: #007bff;
    text-decoration: none;
}

/* صفحة تسجيل الدخول */
.login-container {
    max-width: 400px;
    margin: 50px auto;
    padding: 30px;
    background-color: white;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    text-align: center;
}

.login-container h1 {
    margin-bottom: 20px;
    color: #343a40;
}
    </style>
</head>
<body>
  
    
    <div class="container">
        <h1>تعديل المنتج: <?php echo htmlspecialchars($product['proname']); ?></h1>
        
        <?php if(!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form action="edit_product.php?id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">اسم المنتج</label>
                <input type="text" name="proname" id="name" value="<?php echo htmlspecialchars($product['proname']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="file">صورة المنتج الحالية</label>
                <img src="../../uploads/img/<?php echo $product['proimg']; ?>" alt="<?php echo htmlspecialchars($product['proname']); ?>" width="100">
                <br>
                <label>تحميل صورة جديدة (اختياري)</label>
                <input type="file" name="proimg" id="file" accept="image/*">
            </div>
            
            <div class="form-group">
                <label for="price">سعر المنتج</label>
                <input type="number" name="proprice" id="price" value="<?php echo htmlspecialchars($product['proprice']); ?>" required min="0" step="0.01">
            </div>
            
            <div class="form-group">
                <label for="description">وصف المنتج</label>
                <textarea name="prodecrip" id="description" required><?php echo htmlspecialchars($product['prodecrip']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="size">الأحجام المتوفرة</label>
                <input type="text" name="prosize" id="size" value="<?php echo htmlspecialchars($product['prosize']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="unv">حالة التوفر</label>
                <select name="prounv" id="unv" required>
                    <option value="متوفر" <?php echo ($product['prounv'] == 'متوفر') ? 'selected' : ''; ?>>متوفر</option>
                    <option value="غير متوفر" <?php echo ($product['prounv'] == 'غير متوفر') ? 'selected' : ''; ?>>غير متوفر</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="form_control">القسم</label>
                <select name="prosection" id="form_control" required>
                    <?php foreach($sections as $section): ?>
                        <option value="<?php echo $section['section_id']; ?>" <?php echo ($product['prosection'] == $section['section_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($section['sectionname']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" name="update_product" class="btn btn-primary">حفظ التعديلات</button>
            <a href="product.php" class="btn btn-secondary">إلغاء</a>
        </form>
    </div>
</body>
</html>