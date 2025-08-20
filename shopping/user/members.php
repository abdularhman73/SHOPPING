<?php
session_start();
include('../include/connected.php');

// التحقق من أن المدير مسجل الدخول (صلاحيات الوصول)
if (!isset($_SESSION['admin_id'])) {
   
}

// ------ معالجة حذف العضو ------
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']); // التأكد من أن القيمة رقمية
    $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: members.php?success=تم حذف العضو بنجاح");
    exit();
}

// ------ البحث عن أعضاء (إذا وجدت أداة بحث) ------
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$query = "SELECT * FROM user";
if (!empty($search)) {
    $query .= " WHERE username LIKE '%$search%' OR email LIKE '%$search%'";
}
$query .= " ORDER BY id DESC"; // عرض أحدث الأعضاء أولاً
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - إدارة الأعضاء</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            direction: rtl;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .search-box {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
        }
        .search-box input {
            padding: 10px;
            width: 60%;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-box button {
            padding: 10px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            margin-right: 5px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .action-btn {
            padding: 5px 10px;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
        }
        .delete-btn {
            background: #f44336;
        }
        .edit-btn {
            background: #2196F3;
            margin-left: 5px;
        }
        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 15px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="adminpanel.php" class="back-btn">العودة للوحة التحكم</a>
        <h1>إدارة الأعضاء المسجلين</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-msg"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <div class="search-box">
            <form method="GET" action="members.php">
                <input type="text" name="search" placeholder="ابحث باسم المستخدم أو البريد..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">بحث</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>اسم المستخدم</th>
                    <th>البريد الإلكتروني</th>
                    <th>تاريخ التسجيل</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo $row['created_at'] ?? 'غير محدد'; ?></td>
                    <td>
                        <a href="?delete=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('هل أنت متأكد من حذف هذا العضو؟')">حذف</a>
                        <a href="edit_member.php?id=<?php echo $row['id']; ?>" class="action-btn edit-btn">تعديل</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>