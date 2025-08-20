<body>
    <?php include('../file/hedar.php'); ?>
    
    <main class="container">
        <h1 class="page-title">إتمام الشراء</h1>
        
        <?php if(isset($_SESSION['checkout_error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['checkout_error']; unset($_SESSION['checkout_error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="checkout-container">
            <div class="checkout-steps">
                <div class="step active">1. معلومات الشحن</div>
                <div class="step">2. الدفع</div>
                <div class="step">3. التأكيد</div>
            </div>
            
            <form method="post">
                <div class="row">
                    <div class="col-md-8">
                        <h3>معلومات الشحن</h3>
                        <div class="form-group">
                            <label>الاسم الكامل</label>
                            <input type="text" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>عنوان الشحن</label>
                            <textarea name="shipping_address" required><?= htmlspecialchars($user['address']) ?></textarea>
                        </div>
                        
                        <h3>طريقة الدفع</h3>
                        <div class="payment-methods">
                            <label>
                                <input type="radio" name="payment_method" value="credit_card" checked>
                                بطاقة ائتمان
                            </label>
                            <label>
                                <input type="radio" name="payment_method" value="cash_on_delivery">
                                الدفع عند الاستلام
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="order-summary">
                            <h3>ملخص الطلب</h3>
                            <ul>
                                <?php foreach($cart_items as $item): ?>
                                <li>
                                    <?= htmlspecialchars($item['proname']) ?> 
                                    × <?= $item['quantity'] ?>
                                    <span><?= number_format($item['subtotal'], 2) ?> ر.س</span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="total">
                                <strong>الإجمالي:</strong>
                                <span><?= number_format($total, 2) ?> ر.س</span>
                            </div>
                            <button type="submit" name="place_order" class="btn btn-primary btn-block">
                                تأكيد الطلب
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>
    
    <?php include('file/faoutr.php'); ?>
</body>