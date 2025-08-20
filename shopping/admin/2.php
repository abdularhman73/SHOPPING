<?php
session_start();
require_once '../include/connected.php';

// التحقق من صلاحيات المدير
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
   
}

// معالجة عمليات الإدارة
if(isset($_GET['action'])) {
    $action = $_GET['action'];
    $user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    switch($action) {
        case 'delete':
            if($user_id > 0) {
                $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $_SESSION['message'] = "تم حذف العضو بنجاح";
            }
            break;
            
        case 'toggle_status':
            if($user_id > 0) {
                $stmt = $conn->prepare("UPDATE user SET is_active = NOT is_active WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $_SESSION['message'] = "تم تغيير حالة العضو بنجاح";
            }
            break;
    }
    


}

// جلب قائمة الأعضاء
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM user WHERE username LIKE ? OR email LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
$search_term = "%$search%";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssii", $search_term, $search_term, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// حساب العدد الكلي للأعضاء للترقيم الصفحي
$count_query = "SELECT COUNT(*) as total FROM user WHERE username LIKE ? OR email LIKE ?";
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param("ss", $search_term, $search_term);
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_members = $total_row['total'];
$total_pages = ceil($total_members / $limit);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الأعضاء</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Tahoma', Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .header h1 {
            color: #333;
            margin: 0;
        }
        
        .search-box {
            display: flex;
            align-items: center;
        }
        
        .search-box input {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-left: 10px;
            width: 250px;
        }
        
        .search-box button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .members-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .members-table th, .members-table td {
            padding: 12px 15px;
            text-align: right;
            border-bottom: 1px solid #ddd;
        }
        
        .members-table th {
            background-color: #2c3e50;
            color: white;
        }
        
        .members-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .status-active {
            color: green;
        }
        
        .status-inactive {
            color: red;
        }
        
        .action-btn {
            padding: 5px 10px;
            margin: 0 3px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            color: white;
            text-decoration: none;
            font-size: 14px;
        }
        
        .edit-btn {
            background: #3498db;
        }
        
        .delete-btn {
            background: #e74c3c;
        }
        
        .toggle-btn {
            background: #f39c12;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .pagination a {
            padding: 8px 16px;
            margin: 0 4px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
            border-radius: 4px;
        }
        
        .pagination a.active {
            background: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }
        
        .pagination a:hover:not(.active) {
            background: #ddd;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: white;
        }
        
        .alert-success {
            background-color: #4CAF50;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-box {
                margin-top: 10px;
                width: 100%;
            }
            
            .search-box input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include('admin_header.php'); ?>
    
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-users"></i> إدارة الأعضاء</h1>
            
            <form class="search-box" method="get" action="">
                <input type="text" name="search" placeholder="ابحث بأسم المستخدم أو البريد" value="<?= htmlspecialchars($search) ?>">
                <button type="submit"><i class="fas fa-search"></i> بحث</button>
            </form>
        </div>
        
        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['message'] ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="members-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم المستخدم</th>
                        <th>البريد الإلكتروني</th>
                        <th>تاريخ التسجيل</th>
                        <th>الحالة</th>
                        <th>الصلاحيات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                        <?php $counter = ($page - 1) * $limit + 1; ?>
                        <?php while($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                            <td class="<?= $user['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                <?= $user['is_active'] ? 'نشط' : 'غير نشط' ?>
                            </td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td>
                                <a href="edit_member.php?id=<?= $user['id'] ?>" class="action-btn edit-btn">
                                    <i class="fas fa-edit"></i> تعديل
                                </a>
                                <a href="members.php?action=toggle_status&id=<?= $user['id'] ?>" class="action-btn toggle-btn">
                                    <i class="fas fa-power-off"></i> تغيير
                                </a>
                                <a href="members.php?action=delete&id=<?= $user['id'] ?>" class="action-btn delete-btn" onclick="return confirm('هل أنت متأكد من حذف هذا العضو؟')">
                                    <i class="fas fa-trash"></i> حذف
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center;">لا توجد أعضاء مسجلين</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?>
                <a href="members.php?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">&laquo; السابق</a>
            <?php endif; ?>
            
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <a href="members.php?page=<?= $i ?>&search=<?= urlencode($search) ?>" <?= $i == $page ? 'class="active"' : '' ?>>
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if($page < $total_pages): ?>
                <a href="members.php?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">التالي &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
    // إخفاء رسالة التنبيه بعد 5 ثواني
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if(alert) {
            alert.style.display = 'none';
        }
    }, 5000);
    </script>
</body>
</html>