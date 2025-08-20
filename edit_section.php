<?php
session_start();
include('../include/connected.php');

// التحقق من صلاحيات المدير
if(!isset($_SESSION['admin_logged_in'])) {
   

}

// جلب بيانات القسم المطلوب تعديله
$section_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$section = [];
$errors = [];

if($section_id > 0) {
    $query = "SELECT * FROM section WHERE section_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $section_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($result && mysqli_num_rows($result) > 0) {
        $section = mysqli_fetch_assoc($result);
    } else {
        $errors[] = "القسم غير موجود";
        header("Location: sections.php");
        exit();
    }
    mysqli_stmt_close($stmt);
}

// معالجة تحديث البيانات
if(isset($_POST['update_section'])) {
    $section_name = trim($_POST['section_name']);
    $section_description = trim($_POST['section_description']);
    $section_id = (int)$_POST['section_id'];
    
    // التحقق من الحقول الفارغة
    if(empty($section_name)) {
        $errors[] = "اسم القسم مطلوب";
    }
    
    // معالجة رفع الصورة الجديدة (إذا تم تحميلها)
    $image_name = $section['image']; // الاحتفاظ بالصورة القديمة إذا لم يتم تغييرها
    
    if(isset($_FILES['section_img']) && $_FILES['section_img']['error'] == 0) {
        $image_ext = strtolower(pathinfo($_FILES['section_img']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if(in_array($image_ext, $allowed_ext)) {
            // حذف الصورة القديمة إذا كانت موجودة
            if(!empty($section['image'])) {
                $old_image_path = '../uploads/sections/' . $section['image'];
                if(file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
            
            // رفع الصورة الجديدة
            $image_name = uniqid() . '.' . $image_ext;
            $upload_path = '../uploads/sections/' . $image_name;
            
            if(!move_uploaded_file($_FILES['section_img']['tmp_name'], $upload_path)) {
                $errors[] = "حدث خطأ أثناء رفع الصورة";
                $image_name = $section['image']; // العودة للصورة القديمة في حالة الخطأ
            }
        } else {
            $errors[] = "امتداد الصورة غير مسموح به";
        }
    }
    
    if(empty($errors)) {
        $query = "UPDATE section SET sectionname = ?, description = ?, image = ? WHERE section_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if($stmt) {
            mysqli_stmt_bind_param($stmt, "sssi", $section_name, $section_description, $image_name, $section_id);
            $result = mysqli_stmt_execute($stmt);
            
            if($result) {
                $_SESSION['success_message'] = "تم تحديث القسم بنجاح";
                header("Location: sections.php");
                exit();
            } else {
                $errors[] = "حدث خطأ أثناء التحديث: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
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
    <title>تعديل القسم</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
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
        .current-image {
            max-width: 200px;
            max-height: 200px;
            display: block;
            margin: 10px 0;
            border: 1px solid #ddd;
            padding: 5px;
        }
        .btn {
            padding: 8px 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background: #2980b9;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <?php include('../include/admin_header.php'); ?>
    
    <div class="container">
        <h1>تعديل القسم: <?= htmlspecialchars($section['sectionname'] ?? '') ?></h1>
        
        <?php if(!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form action="edit_section.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="section_id" value="<?= $section_id ?>">
            
            <div class="form-group">
                <label for="section_name">اسم القسم</label>
                <input type="text" id="section_name" name="section_name" 
                       value="<?= htmlspecialchars($section['sectionname'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="section_description">وصف القسم</label>
                <textarea id="section_description" name="section_description"><?= 
                    htmlspecialchars($section['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label>الصورة الحالية</label>
                <?php if(!empty($section['image'])): ?>
                    <img src="../uploads/sections/<?= htmlspecialchars($section['image']) ?>" 
                         class="current-image" alt="صورة القسم الحالية">
                <?php else: ?>
                    <p>لا توجد صورة حالية</p>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="section_img">تغيير الصورة (اختياري)</label>
                <input type="file" id="section_img" name="section_img" accept="image/*">
                <small>اترك الحقل فارغاً للحفاظ على الصورة الحالية</small>
            </div>
            
            <div class="form-group">
                <button type="submit" name="update_section" class="btn">حفظ التعديلات</button>
                <a href="sections.php" class="btn" style="background: #6c757d;">إلغاء</a>
            </div>
        </form>
    </div>
</body>
</html>