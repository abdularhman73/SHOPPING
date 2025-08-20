<?php
session_start();
include('../include/connected.php');

// التحقق من تسجيل دخول المدير
if(!isset($_SESSION['admin_email'])) {
    header('Location: admin.php');
    exit();
}

// جلب بيانات المنتج للتعديل
if(isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $query = "SELECT * FROM product WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($stmt);
    
    if(!$product) {
        echo '<script>alert("المنتج غير موجود"); window.location.href="adminpanel.php";</script>';
        exit();
    }
}

// معالجة تحديث المنتج
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $product_id = intval($_POST['product_id']);
    $proname = mysqli_real_escape_string($conn, $_POST['proname']);
    $proprice = floatval($_POST['proprice']);
    $prosection = intval($_POST['prosection']);
    $prodecrip = mysqli_real_escape_string($conn, $_POST['prodecrip']);
    $prosize = mysqli_real_escape_string($conn, $_POST['prosize']);
    $prounv = mysqli_real_escape_string($conn, $_POST['prounv']);
    
    // معالجة صورة المنتج إذا تم تحميل صورة جديدة
    if(!empty($_FILES['proimg']['name'])) {
        $imageNAME = $_FILES['proimg']['name'];
        $imageTMP = $_FILES['proimg']['tmp_name'];
        $proimg = rand(0, 5000). "_" . $imageNAME;
        move_uploaded_file($imageTMP, "../uploads/img/".$proimg);
        
        // حذف الصورة القديمة
        if(!empty($product['proimg']) && file_exists("../uploads/img/".$product['proimg'])) {
            unlink("../uploads/img/".$product['proimg']);
        }
    } else {
        $proimg = $product['proimg'];
    }
    
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
    mysqli_stmt_bind_param($stmt, "ssdisssi", $proname, $proimg, $proprice, $prosection, $prodecrip, $prosize, $prounv, $product_id);
    
    if(mysqli_stmt_execute($stmt)) {
        echo '<script>alert("تم تحديث المنتج بنجاح"); window.location.href="adminpanel.php";</script>';
    } else {
        echo '<script>alert("حدث خطأ أثناء التحديث");</script>';
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
        
        .edit-product-container {
            max-width: 800px;
            margin: 30px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 18px;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        textarea {
            height: 120px;
            resize: vertical;
        }
        
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin: 15px 0;
            display: block;
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 5px;
        }
        
        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .btn-update {
            background-color: #28a745;
            color: white;
        }
        
        .btn-cancel {
            background-color: #dc3545;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="edit-product-container">
        <h1>تعديل المنتج</h1>
        
        <form action="editproduct.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
            
            <div class="form-group">
                <label for="proname">اسم المنتج:</label>
                <input type="text" id="proname" name="proname" value="<?php echo htmlspecialchars($product['proname']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="proimg">صورة المنتج:</label>
                <?php if(!empty($product['proimg'])): ?>
                    <img src="../uploads/img/<?php echo $product['proimg']; ?>" class="image-preview" id="imagePreview">
                <?php else: ?>
                    <img src="../uploads/img/default.jpg" class="image-preview" id="imagePreview">
                <?php endif; ?>
                <input type="file" id="proimg" name="proimg" onchange="previewImage(this)">
            </div>
            
            <div class="form-group">
                <label for="proprice">سعر المنتج:</label>
                <input type="number" id="proprice" name="proprice" step="0.01" min="0" value="<?php echo $product['proprice']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="prosection">القسم:</label>
                <select id="prosection" name="prosection" required>
                    <option value="">اختر القسم</option>
                    <?php
                    $sections_query = "SELECT * FROM section";
                    $sections_result = mysqli_query($conn, $sections_query);
                    while($section = mysqli_fetch_assoc($sections_result)): ?>
                        <option value="<?php echo $section['section_id']; ?>" <?php echo ($section['section_id'] == $product['prosection']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($section['sectionname']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="prodecrip">وصف المنتج:</label>
                <textarea id="prodecrip" name="prodecrip" required><?php echo htmlspecialchars($product['prodecrip']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="prosize">الأحجام المتوفرة:</label>
                <input type="text" id="prosize" name="prosize" value="<?php echo htmlspecialchars($product['prosize']); ?>">
            </div>
            
            <div class="form-group">
                <label for="prounv">حالة التوفر:</label>
                <select id="prounv" name="prounv" required>
                    <option value="متوفر" <?php echo ($product['prounv'] == 'متوفر') ? 'selected' : ''; ?>>متوفر</option>
                    <option value="غير متوفر" <?php echo ($product['prounv'] == 'غير متوفر') ? 'selected' : ''; ?>>غير متوفر</option>
                    <option value="قريبا" <?php echo ($product['prounv'] == 'قريبا') ? 'selected' : ''; ?>>قريباً</option>
                </select>
            </div>
            
            <div class="buttons">
                <button type="button" class="btn btn-cancel" onclick="window.location.href='adminpanel.php'">إلغاء</button>
                <button type="submit" class="btn btn-update" name="update_product">تحديث المنتج</button>
            </div>
        </form>
    </div>
    
    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const file = input.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            
            if(file) {
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>