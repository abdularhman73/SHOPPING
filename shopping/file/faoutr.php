<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تذييل الصفحة</title>
    <style>
        .footer-container {
            background-color: #333;
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-top: 30px;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        
        .footer-section {
            flex: 1;
            min-width: 250px;
            margin: 10px;
        }
        
        .footer-section h3 {
            margin-bottom: 15px;
            color: #f5aa2e;
        }
        
        .footer-section ul {
            list-style: none;
            padding: 0;
        }
        
        .footer-section ul li {
            margin-bottom: 8px;
        }
        
        .footer-section ul li a {
            color: #ddd;
            text-decoration: none;
        }
        
        .footer-section ul li a:hover {
            color: #f5aa2e;
        }
        
        .social-links a {
            display: inline-block;
            margin: 0 5px;
            color: white;
            font-size: 20px;
        }
        
        .copyright {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #444;
        }
    </style>
</head>
<body>
    <footer class="footer-container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>عن المتجر</h3>
                <p>متجرنا يقدم أفضل المنتجات بجودة عالية وأسعار منافسة. نسعى دائماً لرضا عملائنا.</p>
            </div>
            
            <div class="footer-section">
                
                <ul>
                    <li><a href="index.php">الصفحة الرئيسية</a></li>
                    <li><a href=".php">جميع المنتجات</a></li>
                    <li><a href="about.php">من نحن</a></li>
                    <li><a href="contact.php">اتصل بنا</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
              
                <ul>
                    <li><a href="faq.php">الأسئلة الشائعة</a></li>
                    <li><a href="shipping.php">الشحن والتوصيل</a></li>
                    <li><a href="admin/returns.php">سياسة الإرجاع</a></li>
                    <li><a href="privacy.php">سياسة الخصوصية</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>تواصل معنا</h3>
                <p>البريد الإلكتروني: info@example.com</p>
                <p>الهاتف: +966 12 345 6789</p>
                <div class="social-links">
                    <a href="../privacy.php"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        
       <div class="footer-container">
        <div class="google-maps">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d124232.78247953366!2d44.03628142545785!3d13.333196255355848!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x161c04ffcdcfc705%3A0x9cf9d344d08cd16f!2z2KfZhNmF2LTYp9mI2YTYqdiMINin2YTZitmO2YXZjtmG!5e0!3m2!1sar!2s!4v1740864616500!5m2!1sar!2s" width="50%" height="150" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        <div class="copy">
        <p>عبدالرحمن حسن المشولي.k &copy; 2025 حقول النشر المحفوظه.</p>
        </div>
    </div>
    </footer>
    
    <script src="js/cart.js"></script>
</body>
</html>