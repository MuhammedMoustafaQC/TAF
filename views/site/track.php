<?php
$steps = ['new' => 'تم استلام الطلب', 'confirmed' => 'تم التأكيد', 'shipped' => 'جاري التوصيل', 'delivered' => 'تم التسليم'];
$keys = array_keys($steps);
?>
<div class="page-hero page-hero-sm">
    <div class="container">
        <nav class="breadcrumb"><a href="<?= url('/') ?>">الرئيسية</a> <span>/</span> <b>تتبع طلبك</b></nav>
        <h1>تتبع طلبك</h1>
        <p>اكتب رقم الطلب ورقم الموبايل اللي طلبت بيه علشان تعرف حالة طلبك.</p>
    </div>
</div>

<section class="section section-tight">
    <div class="container narrow">
        <form method="post" class="card form-card track-form">
            <?= csrf_field() ?>
            <div class="form-grid">
                <label class="field"><span>رقم الطلب</span><input type="text" name="code" value="<?= e($code) ?>" placeholder="QN..." dir="ltr" required></label>
                <label class="field"><span>رقم الموبايل</span><input type="tel" name="phone" value="<?= e($phone) ?>" placeholder="01xxxxxxxxx" dir="ltr" required inputmode="tel"></label>
            </div>
            <button class="btn btn-primary btn-block"><?= icon('search', 18) ?> تتبع</button>
            <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        </form>

        <?php if ($order): $pos = array_search($order['status'], $keys, true); ?>
        <div class="card track-result">
            <div class="track-head">
                <div><small>رقم الطلب</small><b dir="ltr"><?= e($order['code']) ?></b></div>
                <div><small>تاريخ الطلب</small><b><?= e(ar_date($order['created_at'], false)) ?></b></div>
                <div><small>الإجمالي</small><b><?= money($order['total']) ?></b></div>
            </div>
            <?php if ($order['status'] === 'cancelled'): ?>
                <div class="alert alert-error">الطلب ده اتلغى. لو محتاج مساعدة كلمنا على واتساب.</div>
            <?php else: ?>
            <ol class="timeline">
                <?php foreach ($steps as $k => $label): $i = array_search($k, $keys, true); ?>
                    <li class="<?= $i < $pos ? 'done' : ($i === $pos ? 'current' : '') ?>"><span><?= $i <= $pos ? icon('check', 16) : $i + 1 ?></span><b><?= e($label) ?></b></li>
                <?php endforeach; ?>
            </ol>
            <?php endif; ?>
            <ul class="mini-items">
                <?php foreach ($order['items'] as $it): ?>
                    <li><span><?= e($it['product_name']) ?> <small>× <?= (int)$it['qty'] ?></small></span><b><?= money($it['total']) ?></b></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</section>
