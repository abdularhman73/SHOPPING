<?php 
session_start();
include('file/hedar.php'); 
require 'include/connected.php';

// تحقق من وجود مستخدم مسجل الدخول
if(!isset($_SESSION['user_id'])) {
    header("Location: user/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// معالجة إضافة منتج للسلة
if(isset($_POST['add'])){
    // التحقق من البيانات المدخلة
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $productname = isset($_POST['h_name']) ? mysqli_real_escape_string($conn, $_POST['h_name']) : '';
    $productprice = isset($_POST['h_price']) ? floatval($_POST['h_price']) : 0;
    $productimg = isset($_POST['h_img']) ? mysqli_real_escape_string($conn, $_POST['h_img']) : '';
    $productquantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // التحقق من صحة البيانات
    if($product_id > 0 && !empty($productname) && $productprice > 0 && !empty($productimg) && $productquantity > 0) {
        
        // التحقق من وجود المنتج بالفعل في سلة المستخدم
        $check_query = "SELECT * FROM cart WHERE product_id = ? AND user_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ii", $product_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0){
            echo '<script>alert("المنتج مضاف مسبقاً إلى سلة التسوق")</script>';
        } else {
            // إضافة المنتج للسلة
            $insert_query = "INSERT INTO cart (product_id, name, price, img, quantity, user_id) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("isdsii", $product_id, $productname, $productprice, $productimg, $productquantity, $user_id);
            
            if($stmt->execute()){
                echo '<script>alert("تمت إضافة المنتج إلى السلة بنجاح")</script>';
            } else {
                echo '<script>alert("حدث خطأ أثناء إضافة المنتج إلى السلة")</script>';
            }
        }
        $stmt->close();
    } else {
        echo '<script>alert("بيانات المنتج غير صالحة")</script>';
    }
}

// معالجة حذف منتج من السلة
if(isset($_POST['delete_c'])){
    $ID = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if($ID > 0){
        $query = "DELETE FROM cart WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $ID, $user_id);
        
        if($stmt->execute()){
            echo '<script>alert("تم حذف المنتج من السلة بنجاح")</script>';
        } else {
            echo '<script>alert("حدث خطأ أثناء حذف المنتج من السلة")</script>';
        }
        $stmt->close();
    }
}

// معالجة تحديث الكمية
if(isset($_POST['update_quantity'])){
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $new_quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if($product_id > 0 && $new_quantity > 0){
        $update_q = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($update_q);
        $stmt->bind_param("iii", $new_quantity, $product_id, $user_id);
        
        if($stmt->execute()){
            echo '<script>alert("تم تحديث الكمية بنجاح")</script>';
        } else {
            echo '<script>alert("حدث خطأ أثناء تحديث الكمية")</script>';
        }
        $stmt->close();
    }
}

// جلب محتويات سلة المستخدم
$query = "SELECT * FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total = 0;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سلة التسوق</title>
    <style>
        body {
            font-family: 'Tahoma', Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .container {
            width: 90%;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .cont_head {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .cont_head img {
            width: 50px;
            margin-left: 15px;
        }
        
        .cont_head h1 {
            color: #2c3e50;
            margin: 0;
        }
        
        .cart_table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .cart_table th {
            background-color: #2c3e50;
            color: white;
            padding: 12px;
            text-align: center;
        }
        
        .cart_table td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        
        .cart_table img {
            width: 80px;
            height: auto;
            border-radius: 4px;
        }
        
        .cart_table input[type="number"] {
            width: 50px;
            text-align: center;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .remove, .edit {
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .remove {
            background-color: #e74c3c;
        }
        
        .remove:hover {
            background-color: #c0392b;
        }
        
        .edit {
            background-color: #3498db;
        }
        
        .edit:hover {
            background-color: #2980b9;
        }
        
        .cart_total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
            margin-top: 20px;
        }
        
        .cart_total h3 {
            margin: 0;
            font-size: 18px;
        }
        
        .cart_total h3 span {
            margin-left: 10px;
            font-weight: normal;
        }
        
        .checkout-btn {
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .checkout-btn:hover {
            background-color: #219653;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="cont_head">
            <img src="img/shopping6.jpg">
            <h1>سلة التسوق</h1>
        </div>

        <table class="cart_table">
            <tr>
                <th>صورة المنتج</th>
                <th>رقم المنتج</th>
                <th>اسم المنتج</th>
                <th>الكمية</th>
                <th>السعر</th>
                <th>الاجمالي</th>
                <th>حذف</th>
                <th>تعديل</th>
            </tr>
            <?php
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    $price = floatval($row['price']);
                    $quantity = intval($row['quantity']);
                    $subtotal = $price * $quantity;
                    $total += $subtotal;
            ?>
            <tr>
                <td><img src="uploads/img/<?php echo htmlspecialchars($row['img']); ?>"></td>
                <td><h3><?php echo $row['product_id']; ?></h3></td>
                <td><h3><?php echo htmlspecialchars($row['name']); ?></h3></td>
                <td><input type="number" value="<?php echo $quantity; ?>" disabled></td>
                <td><h3><?php echo number_format($price, 2); ?> ر.س</h3></td> 
                <td><h3><?php echo number_format($subtotal, 2); ?> ر.س</h3></td>

                <td>
                    <form action="cart.php" method="post">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button class="remove" type="submit" name="delete_c">
                            <i class="fa-solid fa-trash"></i> حذف
                        </button>
                    </form>
                </td>
                
                <td>
                    <form action="cart.php" method="post">
                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>"> 
                        <input type="number" name="quantity" value="<?php echo $row['quantity']; ?>" min="1" max="10">
                        <button class="edit" type="submit" name="update_quantity">
                            <i class="fa-solid fa-pen-to-square"></i> تعديل
                        </button>
                    </form>
                </td>
            </tr>
            <?php
                }
            } else {
                echo '<tr><td colspan="8" style="text-align:center;padding:20px;">سلة التسوق فارغة</td></tr>';
            }
            ?>
        </table>
        
        <?php if($result->num_rows > 0): ?>
        <div class="cart_total">
            <h3><?php echo number_format($total, 2); ?> ر.س <span>المجموع الكلي</span></h3>
            <a href="user/login.php" class="checkout-btn">اتمام الطلب</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
