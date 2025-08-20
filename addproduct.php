 
<?php
session_start();
include('include/connected.php');
include("file/hedar.php");
// تحقق من اتصال قاعدة البيانات
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// تعريف المتغيرات
$proname = $proprice = $prosection = $prodecrip = $prosize = $prounv = '';
$errors = array();

if(isset($_POST['proadd'])) {
    // تنظيف المدخلات
    $proname = mysqli_real_escape_string($conn, $_POST['proname']);
    $proprice = mysqli_real_escape_string($conn, $_POST['proprice']);
    $prosection = mysqli_real_escape_string($conn, $_POST['prosection']);
    $prodecrip = mysqli_real_escape_string($conn, $_POST['prodecrip']);
    $prosize = mysqli_real_escape_string($conn, $_POST['prosize']);
    $prounv = mysqli_real_escape_string($conn, $_POST['prounv']);
    
    // التحقق من الصورة
    if(isset($_FILES['proimg']) && $_FILES['proimg']['error'] == 0) {
        $imageNAME = $_FILES['proimg']['name'];
        $imageTMP = $_FILES['proimg']['tmp_name'];
        $imageEXT = strtolower(pathinfo($imageNAME, PATHINFO_EXTENSION));
        $allowedEXT = array('jpg', 'jpeg', 'png', 'gif');
        
        if(!in_array($imageEXT, $allowedEXT)) {
            $errors[] = "امتداد الصورة غير مسموح به. المسموح: JPG, JPEG, PNG, GIF";
        }
    } else {
        $errors[] = "يجب اختيار صورة للمنتج";
    }
    
    // التحقق من الحقول الفارغة
    if(empty($proname)) $errors[] = "اسم المنتج مطلوب";
    if(empty($proprice)) $errors[] = "سعر المنتج مطلوب";
    if(empty($prosection)) $errors[] = "القسم مطلوب";
    if(empty($prodecrip)) $errors[] = "وصف المنتج مطلوب";
    if(empty($prosize)) $errors[] = "الحجم مطلوب";
    if(empty($prounv)) $errors[] = "حالة التوفر مطلوبة";
    
    // إذا لم تكن هناك أخطاء
    if(empty($errors)) {
        $proimg = uniqid() . "_" . $imageNAME;
        $uploadPath = "../uploads/img/" . $proimg;
        
        if(move_uploaded_file($imageTMP, $uploadPath)) {
         // استبدال الاستعلام الخطأ بهذا:
$query = "INSERT INTO product (proname, proimg, proprice, prosection, prodecrip, prosize, prounv) 
          VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            
            if($stmt) {
                mysqli_stmt_bind_param($stmt, "ssdssss", $proname, $proimg, $proprice, $prosection, $prodecrip, $prosize, $prounv);
                $result = mysqli_stmt_execute($stmt);
                
                if($result) {
                    echo'<script>alert("تمت إضافة المنتج بنجاح");</script>';
                    // إعادة تعيين المتغيرات بعد الإضافة
                    $proname = $proprice = $prosection = $prodecrip = $prosize = $prounv = '';
                } else {
                    echo'<script>alert("حدث خطأ أثناء إضافة المنتج: ' . mysqli_error($conn) . '");</script>';
                }
                mysqli_stmt_close($stmt);
            } else {
                echo'<script>alert("حدث خطأ في إعداد الاستعلام: ' . mysqli_error($conn) . '");</script>';
            }
        } else {
            echo'<script>alert("حدث خطأ أثناء رفع الصورة");</script>';
        }
    } else {
        // عرض جميع الأخطاء
        $errorMessage = implode("\\n", $errors);
        echo'<script>alert("'.$errorMessage.'");</script>';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة منتجات</title>
    <link rel="stylesheet" href="style.css">
    <style>
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
   <center>
    <main>
        <div class="form_product">
            <h1>إضافة المنتجات</h1>
            <form action="addproduct.php" method="post" enctype="multipart/form-data">
                <label for="name">عنوان المنتج</label>
                <input type="text" name="proname" id="name" value="<?php echo htmlspecialchars($proname); ?>" required>

                <label for="file">صورة المنتج</label>
                <input type="file" name="proimg" id="file" required accept="image/*">

                <label for="price">سعر المنتج</label>
                <input type="number" name="proprice" id="price" value="<?php echo htmlspecialchars($proprice); ?>" required min="0" step="0.01">

                <label for="description">تفاصيل المنتج</label>
                <textarea name="prodecrip" id="description" required><?php echo htmlspecialchars($prodecrip); ?></textarea>

                <label for="size">الأحجام المتوفرة</label>
                <input type="text" name="prosize" id="size" value="<?php echo htmlspecialchars($prosize); ?>" required>

                <label for="unv">حالة التوفر</label>
                <select name="prounv" id="unv" required>
                    <option value="">-- اختر الحالة --</option>
                    <option value="متوفر" <?php echo ($prounv == 'متوفر') ? 'selected' : ''; ?>>متوفر</option>
                    <option value="غير متوفر" <?php echo ($prounv == 'غير متوفر') ? 'selected' : ''; ?>>غير متوفر</option>
                </select>

                <div>
                    <label for="form_control">الأقسام</label>
                  <select name="prosection" id="form_control" required>
    <option value="">-- اختر القسم --</option>
    <?php
    $query = "SELECT * FROM section";
    $result = mysqli_query($conn, $query);
    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            echo '<option value="'.$row['section_id'].'" '.($prosection == $row['section_id'] ? 'selected' : '').'>'.$row['sectionname'].'</option>';
        }
    }
    ?>
</select>
                </div>
                <br>
                
                <button class="button" type="submit" name="proadd">إضافة المنتج الآن</button>
            </form>
        </div>
    </main>
   </center>
</body>
</html>