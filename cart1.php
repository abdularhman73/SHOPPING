<?php 
session_start();
include('file/hedar.php'); 
 
if(isset($_POST['add'])){
    // Initialize variables and check if POST values exist
    $productname = isset($_POST['h_name']) ? $_POST['h_name'] : '';
    $productprice = isset($_POST['h_price']) ? $_POST['h_price'] : '';
    $productimg = isset($_POST['h_img']) ? $_POST['h_img'] : '';
    $productquantity = isset($_POST['h_quantity']) ? $_POST['h_quantity'] : 1; // Default to 1 if not set

    // Check if product already exists
    $add_cart = "SELECT * FROM cart WHERE name='".mysqli_real_escape_string($conn, $productname)."'";
    $result = mysqli_query($conn, $add_cart);
    
    if(mysqli_num_rows($result) > 0){
        echo '<script>alert("لم يتم الاضافة - المنتج مضاف مسبقاً")</script>';
    } else {
        $insert_cart = "INSERT INTO cart (name, price, img, quantity) VALUES(
            '".mysqli_real_escape_string($conn, $productname)."',
            '".mysqli_real_escape_string($conn, $productprice)."',
            '".mysqli_real_escape_string($conn, $productimg)."',
            '".mysqli_real_escape_string($conn, $productquantity)."'
        )";
        
        if(mysqli_query($conn, $insert_cart)){
            echo '<script>alert("تمت اضافة المنتج بنجاح")</script>';
        } else {
            echo '<script>alert("لم تتم اضافة المنتج إلى السلة - حدث خطأ ما")</script>';
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
<html lang="en">
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
            <img src="img\shopping6.jpg">
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
            $query = "SELECT * FROM cart";
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
                <td><h3><?php echo $row['id']; ?></h3></td>
                <td><h3><?php echo $row['name']; ?></h3></td>
                <td><input type="number" value="<?php echo $quantity; ?>"></td>
                <td><h3>$<?php echo number_format($price, 2); ?></h3></td> 
                <td><h3>$<?php echo number_format($subtotal, 2); ?></h3></td>
                <td><a href="cart.php"><button class="remove">حذف<i class="fa-solid fa-trash"></i></button></a></td>
                <td><a href=""><button class="remove">تعديل<i class="fa-solid fa-pen-to-square"></i></button></a></td>
            </tr>
            <?php
                }
            }
            ?>
        </table>
        <div class="cart_total">
            <h6>$<?php echo number_format($total, 2); ?><span id="total">المجموع</span></h6>
            <button type="submit" class="remove"><a href="login.php"><h2>اتمام الطلب</h2></a></button>
        </div>
    </div>
</body>
</html>