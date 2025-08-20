<?php
session_start();
require '../include/connected.php';

// التحقق من صلاحيات المشرف
if(!isset($_SESSION['admin_email'])) {
   // header('Location: admin.php');
    exit();
}

// معالجة إضافة قسم جديد
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['secadd'])) {
    $sectionname = trim($_POST['sectionname']);
    
    if(empty($sectionname)) {
        $_SESSION['error'] = "يجب إدخال اسم القسم";
    } elseif(strlen($sectionname) > 50) {
        $_SESSION['error'] = "اسم القسم يجب أن لا يتجاوز 50 حرفاً";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO section (sectionname) VALUES (?)");
            $stmt->execute([$sectionname]);
            $_SESSION['success'] = "تمت إضافة القسم بنجاح";
            header('Location: adminpanel.php');
            exit();
        } catch(PDOException $e) {
            $_SESSION['error'] = "خطأ في إضافة القسم: " . $e->getMessage();
        }
    }
}

// جلب الأقسام
$sections = [];
try {
    $stmt = $conn->query("SELECT * FROM section ORDER BY id");
    $sections = $stmt->fetchAll();
} catch(PDOException $e) {
    $_SESSION['error'] = "خطأ في جلب الأقسام: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم</title>
    <style>
        /* التنسيقات الأصلية مع إضافة للتنبيهات */
        .alert {
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="sidebar_container">
        <!-- القائمة الجانبية (نفسها) -->
    </div>
    
    <div class="content_sec">
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form method="post">
            <label for="section">إضافة قسم جديد:</label>
            <input type="text" name="sectionname" id="section" required maxlength="50">
            <button type="submit" name="secadd">إضافة قسم</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم القسم</th>
                    <th>تاريخ الإضافة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($sections as $section): ?>
                <tr>
                    <td><?= $section['id'] ?></td>
                    <td><?= htmlspecialchars($section['sectionname']) ?></td>
                    <td><?= $section['created_at'] ?></td>
                    <td>
                        <form method="post" action="delete_section.php" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $section['id'] ?>">
                            <button type="submit" class="delet" 
                                onclick="return confirm('هل أنت متأكد من حذف هذا القسم؟')">
                                حذف
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>