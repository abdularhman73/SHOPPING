<?php
session_start();

// تضمين ملف الاتصال بقاعدة البيانات
require_once '../include/connected.php';

// التحقق من أن المستخدم مسجل دخول
if (isset($_SESSION['user_id'])) {
    // تسجيل وقت الخروج (اختياري)
    $user_id = $_SESSION['user_id'];
    $logout_time = date('Y-m-d H:i:s');
    
    // يمكنك تحديث وقت الخروج في قاعدة البيانات إذا كنت تريد تسجيله
    // $stmt = $conn->prepare("UPDATE users SET last_logout = ? WHERE id = ?");
    // $stmt->bind_param("si", $logout_time, $user_id);
    // $stmt->execute();

    // تدمير جميع بيانات الجلسة
    $_SESSION = array();

    // إذا كنت تريد حذف كوكي الجلسة أيضاً
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // تدمير الجلسة نهائياً
    session_destroy();

    // إرسال رسالة نجاح وإعادة التوجيه
    echo '<script>
        alert("تم تسجيل الخروج بنجاح");
        window.location.href = "login.php";
    </script>';
    exit();
} else {
    // إذا لم يكن مسجلاً دخولاً
    echo '<script>
        alert("لم تقم بتسجيل الدخول بعد");
        window.location.href = "login.php";
    </script>';
    exit();
}
?>