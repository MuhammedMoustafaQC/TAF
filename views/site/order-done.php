<section class="section">
    <div class="container narrow">
        <div class="card success-card">
            <span class="success-ico"><?= icon('check', 44) ?></span>
            <h1>شكراً يا <?= e(explode(' ', $order['customer_name'])[0]) ?>، طلبك وصلنا!</h1>
            <p class="lead">رقم طلبك: <b class="order-code" dir="ltr"><?= e($order['code']) ?></b></p>
            <p>هيتواصل معاك فريق <?= e(setting('site_name')) ?> على رقم <b dir="ltr"><?= e($order['phone']) ?></b> لتأكيد الطلب وميعاد التوصيل.</p>
            <?php if ($order['payment_method'] === 'wallet'): ?>
                <div class="alert alert-info">طريقة الدفع: فودافون كاش / إنستاباي — حوّل مبلغ <b><?= money($order['total']) ?></b> على رقم <b dir="ltr"><?= e(setting('wallet_number')) ?></b> وابعت صورة التحويل على واتساب مع رقم الطلب.</div>
            <?php endif; ?>

            <table class="table order-table">
                <thead><tr><th>المنتج</th><th>الكمية</th><th>الإجمالي</th></tr></thead>
                <tbody>
                <?php foreach ($order['items'] as $it): ?>
                    <tr><td><?= e($it['product_name']) ?></td><td><?= (int)$it['qty'] ?></td><td><?= money($it['total']) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr><td colspan="2">الشحن</td><td><?= $order['shipping'] > 0 ? money($order['shipping']) : 'مجاني' ?></td></tr>
                    <tr class="sum-total"><td colspan="2">الإجمالي</td><td><?= money($order['total']) ?></td></tr>
                </tfoot>
            </table>

            <div class="btn-row center">
                <a href="<?= e(wa_link('السلام عليكم، عملت طلب رقم ' . $order['code'])) ?>" class="btn btn-wa" target="_blank" rel="noopener"><?= icon('whatsapp', 18) ?> تابع طلبك على واتساب</a>
                <a href="<?= url('/track?code=' . urlencode($order['code']) . '&phone=' . urlencode($order['phone'])) ?>" class="btn btn-outline"><?= icon('truck', 18) ?> تتبع الطلب</a>
                <a href="<?= url('/shop') ?>" class="btn btn-light">كمّل تسوق</a>
            </div>
        </div>
    </div>
</section>
