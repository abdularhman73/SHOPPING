<?php
session_start();

include('../include/connected.php');
if(isset($_SESSION['user_id'])){
    echo '<script>alert("انت مسجل في المتجر بالفعل");</script>';
}

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // التحقق من وجود المستخدم
    $user_query = "SELECT * FROM user WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $user_query);
    
    if($result === false) {
        die('<script>alert("حدث خطأ في الاستعلام: ' . mysqli_error($conn) . '");</script>');
    }

    if(mysqli_num_rows($result) > 0) {
        echo '<script>alert("انت مسجل في المتجر بالفعل قم بتسجيل الدخول مباشره");</script>';
    } else {
        // تسجيل المستخدم الجديد
        $query = "INSERT INTO user(username, email, password) VALUES ('$username', '$email', '$password')";
        $insert_result = mysqli_query($conn, $query);
        
        if($insert_result) {
            echo '<script>alert("تم تسجيلك في الموقع بنجاح قم بتسجيل الدخول مباشر");</script>';
        } else {
            echo '<script>alert("حدث خطأ في التسجيل: ' . mysqli_error($conn) . '");</script>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل المستخدم</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .user_container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .input-signup {
            margin-bottom: 15px;
        }
        .input-signup input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #4cae4c;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="user_container">
        <h2>تسجيل الدخول الجديد</h2>
        <form action="signup.php" method="post">
            <div class="input-signup">
                <input type="text" name="username" placeholder="ادخل اسم المستخدم" required>
            </div>
            <div class="input-signup">
                <input type="email" name="email" placeholder="ادخل البريد الاكتروني" required>
            </div>
            <div class="input-signup">
                <input type="password" name="password" placeholder="ادخل الرمز السري" required>
            </div>
            <button type="submit" class="btn">تسجيل الان</button>
        </form>
        <div class="footer">
            <p>لديك حساب بالفعل <a href="login.php">الدخول</a></p>
        </div>
    </div>
</body>
</html>