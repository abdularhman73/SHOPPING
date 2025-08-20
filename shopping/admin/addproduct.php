<?php
session_start();
require_once __DIR__ . '/../include/connected.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/login.php");
    exit();
}

// التحقق من اتصال قاعدة البيانات
if (!$conn) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

$errors = [];
$success = false;
$uploadDir = '../uploads/img/';

// إنشاء مجلد التحميل إذا لم يكن موجوداً
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        $errors[] = "تعذر إنشاء مجلد التحميل";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['proadd'])) {
    // تنظيف المدخلات
    $proname = mysqli_real_escape_string($conn, $_POST['proname']);
    $proprice = floatval($_POST['proprice']);
    $prosection = intval($_POST['prosection']);
    $prodecrip = mysqli_real_escape_string($conn, $_POST['prodecrip']);
    $prosize = mysqli_real_escape_string($conn, $_POST['prosize']);
    $prounv = mysqli_real_escape_string($conn, $_POST['prounv']);
    
    // معالجة الصورة
    $proimg = '';
    if (isset($_FILES['proimg']) && $_FILES['proimg']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['proimg']['tmp_name'];
        $fileName = $_FILES['proimg']['name'];
        $fileSize = $_FILES['proimg']['size'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        // إنشاء اسم فريد للصورة
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;
        
        // التحقق من الامتدادات المسموحة
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedExtensions)) {
            // التحقق من حجم الصورة (2MB كحد أقصى)
            if ($fileSize <= 2 * 1024 * 1024) {
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $proimg = $newFileName;
                } else {
                    $errors[] = "حدث خطأ أثناء رفع الملف";
                }
            } else {
                $errors[] = "حجم الصورة كبير جداً. الحد الأقصى 2MB";
            }
        } else {
            $errors[] = "نوع الملف غير مسموح به. المسموح: JPG, JPEG, PNG, GIF";
        }
    } else {
        $errors[] = "يجب اختيار صورة للمنتج";
    }
    
    // التحقق من الحقول الفارغة
    if (empty($proname)) $errors[] = "اسم المنتج مطلوب";
    if (empty($proprice)) $errors[] = "سعر المنتج مطلوب";
    if (empty($prosection)) $errors[] = "القسم مطلوب";
    if (empty($prodecrip)) $errors[] = "وصف المنتج مطلوب";
    if (empty($prosize)) $errors[] = "الحجم مطلوب";
    if (empty($prounv)) $errors[] = "حالة التوفر مطلوبة";
    
    // إذا لم تكن هناك أخطاء
    if (empty($errors)) {
        $query = "INSERT INTO product (proname, proimg, proprice, prosection, prodecrip, prosize, prounv) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("ssdssss", $proname, $proimg, $proprice, $prosection, $prodecrip, $prosize, $prounv);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "تمت إضافة المنتج بنجاح";
                header("Location: product.php");
                exit();
            } else {
                $errors[] = "خطأ في إضافة المنتج: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = "خطأ في إعداد الاستعلام: " . $conn->error;
        }
    }
}

// جلب الأقسام لعرضها في القائمة المنسدلة
$sections = [];
$sections_query = "SELECT * FROM section";
$sections_result = mysqli_query($conn, $sections_query);
if ($sections_result && mysqli_num_rows($sections_result) > 0) {
    while ($section = mysqli_fetch_assoc($sections_result)) {
        $sections[] = $section;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة منتج جديد</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <style></style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .form-title {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 28px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        .form-control:focus {
            border-color: #4CAF50;
            outline: none;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn-submit {
            background: #4CAF50;
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            width: 100%;
            transition: background 0.3s;
        }
        
        .btn-submit:hover {
            background: #45a049;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .file-input {
            display: none;
        }
        
        .file-label {
            display: block;
            padding: 12px;
            background: #f8f9fa;
            border: 2px dashed #ddd;
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-label:hover {
            border-color: #4CAF50;
            background: #e8f5e9;
        }
        
        .file-name {
            margin-top: 8px;
            font-size: 14px;
            color: #666;
        }
        
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background-color: #f0f0f0;
}

header {
    height: 90px;
    width: 100%;
    background-color: brown;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
}

.logo h1 {
    color: #fff;
    font-size: 60px;
    font-weight: 900;
    text-shadow: 3px 3px 3px red;
}

.logo img {
    width: 40px;
    height: 40px;
    border-radius: 10px;
}

.search {
    margin-top: 20px;
}

.search_input {
    padding: 6px;
    color: #000;
    width: 150px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.button_search {
    padding: 5px 10px;
    background-color: blue;
    border-radius: 5px;
    color: white;
    cursor: pointer;
}

nav {
    width: 100%;
    height: 50px;
    background-color: aliceblue;
    border-bottom: 3px solid wheat;
}

.social ul {
    list-style: none;
    margin: 20px;
}

.social ul li {
    float: left;
    padding: 5px 10px;
}

.social ul li a {
    color: rgb(87, 86, 86);
}

.social ul li a:hover {
    color: rgb(103, 103, 192);
}

.section ul {
    list-style: none;
}

.section ul li {
    float: right;
    padding: 5px 3px;
}

.section ul li a {
    text-decoration: none;
    font-size: 20px;
    color: #000;
    padding: 5px 3px;
    border-radius: 5px;
}

.section ul li a:hover { 
    background-color: #f5aa2e;
    color: #f2e9e9;
}

.last-post {
    padding: 20px;
    background-color: white;
    border-radius: 4px;
}

.last-post h4 {
    float: right;
    color: #080808;
    font-size: 20px;
    padding-left: 5px;
}

.last-post ul {
    list-style-type: none;
    margin: 2px;
    padding: 3px;
}

.last-post li {
    margin-right: 15px;
}

.last-post .span-img img {
    float: right;
    width: 30px;
    height: 30px;
    margin-left: 10px;
    border-radius: 15px;
}

main {
    display: flex;
    justify-content: center;
    padding: 20px;
}

.Product {
    position: relative;
    width: 300px;
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    text-align: center;
    padding: 15px;
    margin: 10px;
}

.Product-img img {
    width: 100%;
    height: auto;
    border-radius: 10px;
}

.qty_input {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 10px 0;
}

.qty_count_mins,
.qty_count_add {
    padding: 5px 10px;
    border: none;
    background-color: #f5aa2e;
    color: white;
    cursor: pointer;
}

.addto_cart {
    padding: 10px 15px;
    background-color: blue;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.addto_cart:hover {
    background-color: darkblue;
}
.unvailable {
    position: absolute;
    top: 20px;
    left: 15px;
    transform: rotate(-25deg);
    width: 100px;
    text-align: center;
    font-size: 18px;
    font-weight: bold;
    color: black;
    padding: 5px 5px;
    background-color: red;
    border: 1px solid rgba(136, 37, 37, 0.964);
}
.footer-container{
    position: fixed;
    bottom: 0;
    background-color: #ccc;
    width: 100%;
    height: 150px;
    padding: 4px;
}
.google-maps{
    float: left;
    width: 60%;
    height: 150px;
}
.copy{
    float: right;
    font-size: 18px;
    margin-top: 15px;
}
/*start prodect css*/
.from_product{
    width: 70%;
    margin: 5px;
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
}
h1{
   padding: 10px; 
}

label{
    display: block;
    margin-bottom: 5px;
   font-size: 25px;
}

input{
    width: 80%;
    padding: 12px;
    margin-bottom: 10px;
    border: 1px solid white;
    border-radius: 4px;
}
button{
    width: 90%;
    padding: 10px;
    margin-bottom: 15px;
    background-color: #007bff;
    border: none;
    cursor: pointer;
    font-size: 28px;
}
button:hover{
    background-color: #011c38;
    color: white;
}
#from_control{
    width: 80%;
    padding: 12px;
    margin-bottom: 10px;
    border: 1px solid white;
    border-radius: 4px;
} 
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

/*end prodect css*/

    
    </style>
</head>
<body>
    <?php include('../file/hedar.php'); ?>
    
    <div class="form-container">
        <h1 class="form-title">إضافة منتج جديد</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="proname">اسم المنتج</label>
                <input type="text" id="proname" name="proname" class="form-control" required 
                       value="<?= isset($_POST['proname']) ? htmlspecialchars($_POST['proname']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="proimg">صورة المنتج</label>
                <input type="file" id="proimg" name="proimg" class="file-input" required accept="image/*">
                <label for="proimg" class="file-label">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 24px; color: #4CAF50;"></i><br>
                    <span>انقر لرفع صورة المنتج</span>
                    <div id="file-name" class="file-name"></div>
                </label>
            </div>
            
            <div class="form-group">
                <label for="proprice">سعر المنتج</label>
                <input type="number" id="proprice" name="proprice" class="form-control" 
                       step="0.01" min="0" required 
                       value="<?= isset($_POST['proprice']) ? htmlspecialchars($_POST['proprice']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="prosection">القسم</label>
                <select id="prosection" name="prosection" class="form-control" required>
                    <option value="">-- اختر القسم --</option>
                    <?php if (!empty($sections)): ?>
                        <?php foreach ($sections as $section): ?>
                            <option value="<?= $section['section_id'] ?>" 
                                <?= (isset($_POST['prosection']) && $_POST['prosection'] == $section['section_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($section['sectionname']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>لا توجد أقسام متاحة</option>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="prodecrip">وصف المنتج</label>
                <textarea id="prodecrip" name="prodecrip" class="form-control" required><?= 
                    isset($_POST['prodecrip']) ? htmlspecialchars($_POST['prodecrip']) : '' 
                ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="prosize">الأحجام المتوفرة</label>
                <input type="text" id="prosize" name="prosize" class="form-control" required 
                       value="<?= isset($_POST['prosize']) ? htmlspecialchars($_POST['prosize']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="prounv">حالة التوفر</label>
                <select id="prounv" name="prounv" class="form-control" required>
                    <option value="">-- اختر الحالة --</option>
                    <option value="متوفر" <?= (isset($_POST['prounv']) && $_POST['prounv'] == 'متوفر') ? 'selected' : '' ?>>متوفر</option>
                    <option value="غير متوفر" <?= (isset($_POST['prounv']) && $_POST['prounv'] == 'غير متوفر') ? 'selected' : '' ?>>غير متوفر</option>
                </select>
            </div>
            
            <button type="submit" name="proadd" class="btn-submit">
                <i class="fas fa-plus-circle"></i> إضافة المنتج
            </button>
        </form>
    </div>
    
    
    <script>
        // عرض اسم الملف المختار
        document.getElementById('proimg').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'لم يتم اختيار ملف';
            document.getElementById('file-name').textContent = 'الملف المختار: ' + fileName;
        });
    </script>
</body>
</html>