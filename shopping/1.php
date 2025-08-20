
<?php
include('file/hedar.php');
session_start();

// تضمين ملف الاتصال بقاعدة البيانات


if(!isset($_SESSION['user_id'])) {
    echo'<script>alert("يرجاء تسجيل الدخول اولا لاضافه المنتج الي السله"); window.location.href="user/login.php";</script>';
    exit;
}

$user_id = $_SESSION['user_id'];
if($user_id <= 0) {
    echo'<script>alert("مستخدم غير صحيح"); window.location.href="user/login.php";</script>';
    exit;
}?>
<?php
if(isset($_POST['add'])) {
    // Initialize variables and check if POST values exist
    $productname = isset($_POST['h_name']) ? $_POST['h_name'] : '';
    $productprice = isset($_POST['h_price']) ? $_POST['h_price'] : '';
    $productimg = isset($_POST['h_img']) ? $_POST['h_img'] : '';
    $productquantity = isset($_POST['h_quantity']) ? $_POST['h_quantity'] : 1;
    $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';

    // Check if product already exists
    $add_cart = "SELECT * FROM cart WHERE name=? AND user_id=?";
    $stmt = $conn->prepare($add_cart);
    $stmt->bind_param("si", $productname, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        echo '<script>alert("لم يتم الاضافة - المنتج مضاف مسبقاً")</script>';
    } else {
        if($user_id > 0) {
            $insert_cart = "INSERT INTO cart (product_id, name, price, img, quantity, user_id) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_cart);
            $stmt->bind_param("issdii", $product_id, $productname, $productprice, $productimg, $productquantity, $user_id);
            
            if($stmt->execute()) {
                echo '<script>alert("تمت اضافة المنتج بنجاح")</script>';
            } else {
                echo '<script>alert("لم تتم اضافة المنتج إلى السلة - حدث خطأ ما")</script>';
            }
        }
    }
}

if(isset($_POST['delete_c'])) {
    $ID = isset($_POST['id']) ? $_POST['id'] : 0;
    if($ID > 0) {
        $query = "DELETE FROM cart WHERE id=? AND user_id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $ID, $user_id);
        if($stmt->execute()) {
            echo '<script>alert("تم الحذف بنجاح")</script>';
        } else {
            echo '<script>alert("لم يتم الحذف")</script>';
        }
    }
}

if(isset($_POST['update_quantity'])) {
    $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : 0;
    $new_quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 1;
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : 0;

    $update_q = "UPDATE cart SET quantity = ? WHERE id = ?";
    if(!empty($user_id)) {
        $update_q .= " AND user_id = ?";
    }
    
    $stmt = $conn->prepare($update_q);
    if(!empty($user_id)) {
        $stmt->bind_param("iii", $new_quantity, $product_id, $user_id);
    } else {
        $stmt->bind_param("ii", $new_quantity, $product_id);
    }
    
    if($stmt->execute()) {
        echo '<script>alert("تم تحديث الكمية بنجاح")</script>';
    } else {
        echo '<script>alert("لم يتم تحديث الكمية  هناك خطا ما")</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سلة التسوق</title>
    <style>
        /* نفس الستايل السابق بدون تغيير */
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
        
        /* باقي الستايل كما هو */
    </style>
</head>

<body>
    <div class="container">
        <div class="cont_head">
            <img src="img/shopping6.jpg">
            <h1>programmed.k</h1>
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
            $query = "SELECT * FROM cart WHERE user_id ='$user_id'";
            $result = mysqli_query($conn, $query);
            $total = 0;
            
            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    // Convert price and quantity to numbers before multiplication
                    $price = floatval($row['price']);
                    $quantity = intval($row['quantity']);
                    $subtotal = $price * $quantity;
                    $total += $subtotal;
            ?>
            <tr>
                <td><img src="uploads/img/<?php echo $row['img']; ?>"></td>
                <td><h3><?php echo $row['product_id']; ?></h3></td>
                <td><h3><?php echo $row['name']; ?></h3></td>
                <td><input type="number" value="<?php echo $quantity; ?>"></td>
                <td><h3>$<?php echo number_format($price, 2); ?></h3></td> 
                <td><h3>$<?php echo number_format($subtotal, 2); ?></h3></td>

                <td>
                    <form action="cart.php" method="post">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button class="remove" type="submit" name="delete_c">حذف</button>
                    </form>
                </td>
                
                <td>
                    <form action="cart.php" method="post">
                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>"> 
                        <input type="number" name="quantity" value="<?php echo $row['quantity']; ?>">
                        <button class="edit" type="submit" name="update_quantity">تعديل</button>
                    </form>
                </td>
            </tr>
            <?php
                }
            }
            ?>
        </table>
        <div class="cart_total">
            <h3>$<?php echo number_format($total, 2); ?><span>المجموع</span></h3>
            <a href="user/login.php" class="checkout-btn">اتمام الطلب</a>
        </div>
    </div>
</body>
</html>