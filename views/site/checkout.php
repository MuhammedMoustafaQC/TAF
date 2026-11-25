<?php $f = fn($k, $d = '') => e($form[$k] ?? $d); ?>
<div class="container page-wrap">
    <?= view('partials/steps', ['step' => 2]) ?>
    <form method="post" class="checkout-layout" novalidate>
        <?= csrf_field() ?>
        <div class="card form-card">
            <h3><?= icon('truck', 20) ?> تفاصيل الشحن</h3>
            <?php if (!customer()): ?><p class="muted small">عندك حساب؟ <a href="<?= url('/account/login') ?>">سجّل دخولك</a> علشان نملا بياناتك تلقائياً وتتابع طلباتك.</p><?php endif; ?>
            <?php if ($errors): ?><div class="alert alert-error">من فضلك راجع البيانات اللي باللون الأحمر.</div><?php endif; ?>
            <div class="form-grid">
                <label class="field span-2"><span>الاسم بالكامل *</span><input type="text" name="customer_name" value="<?= $f('customer_name') ?>" required autocomplete="name" class="<?= inv($errors, 'customer_name') ?>"><?= err($errors, 'customer_name') ?></label>
                <label class="field"><span>رقم الموبايل *</span><input type="tel" name="phone" value="<?= $f('phone') ?>" required placeholder="01xxxxxxxxx" dir="ltr" autocomplete="tel" inputmode="tel" class="<?= inv($errors, 'phone') ?>"><?= err($errors, 'phone') ?></label>
                <label class="field"><span>رقم موبايل إضافي</span><input type="tel" name="phone2" value="<?= $f('phone2') ?>" placeholder="اختياري" dir="ltr" inputmode="tel" class="<?= inv($errors, 'phone2') ?>"><?= err($errors, 'phone2') ?></label>
                <label class="field"><span>المحافظة *</span>
                    <select name="governorate" required class="<?= inv($errors, 'governorate') ?>">
                        <option value="">اختار المحافظة</option>
                        <?php foreach (governorates() as $g): ?><option <?= ($form['governorate'] ?? '') === $g ? 'selected' : '' ?>><?= e($g) ?></option><?php endforeach; ?>
                    </select><?= err($errors, 'governorate') ?>
                </label>
                <label class="field"><span>المدينة / المنطقة *</span><input type="text" name="city" value="<?= $f('city') ?>" required class="<?= inv($errors, 'city') ?>"><?= err($errors, 'city') ?></label>
                <label class="field span-2"><span>العنوان بالتفصيل *</span><textarea name="address" rows="2" required placeholder="الشارع، رقم العمارة، الدور، الشقة، علامة مميزة" autocomplete="street-address" class="<?= inv($errors, 'address') ?>"><?= $f('address') ?></textarea><?= err($errors, 'address') ?></label>
                <label class="field span-2"><span>ملاحظات</span><textarea name="notes" rows="2" placeholder="مثلاً: ميعاد مناسب للتوصيل أو التركيب"><?= $f('notes') ?></textarea></label>
            </div>
            <h3><?= icon('cash', 20) ?> طريقة الدفع</h3>
            <div class="pay-options">
                <?php $pm = $form['payment_method'] ?? 'cod'; foreach (payment_methods() as $k => $label): ?>
                <label class="pay-option">
                    <input type="radio" name="payment_method" value="<?= e($k) ?>" <?= $pm === $k ? 'checked' : '' ?>>
                    <span><b><?= e($label) ?></b><small><?= $k === 'cod' ? 'ادفع كاش للمندوب لما الطلب يوصلك.' : 'حوّل على رقم ' . e(setting('wallet_number')) . ' وابعت صورة التحويل على واتساب.' ?></small></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <aside class="summary card">
            <h3>طلبك</h3>
            <ul class="mini-items">
                <?php foreach ($cart['items'] as $it): ?>
                <li><img src="<?= e(img($it['product']['image'])) ?>" alt="" loading="lazy"><span><?= e($it['product']['name']) ?> <small>× <?= (int)$it['qty'] ?></small></span><b><?= money($it['total']) ?></b></li>
                <?php endforeach; ?>
            </ul>
            <div class="sum-row"><span>إجمالي المنتجات</span><b><?= money($cart['subtotal']) ?></b></div>
            <div class="sum-row"><span>الشحن</span><b><?= $cart['shipping'] > 0 ? money($cart['shipping']) : '<span class="ok">مجاني</span>' ?></b></div>
            <div class="sum-row sum-total"><span>الإجمالي</span><b><?= money($cart['total']) ?></b></div>
            <button type="submit" class="btn btn-primary btn-lg btn-block"><?= icon('check', 20) ?> تأكيد الطلب</button>
            <p class="muted small center" style="margin:10px 0 0">بعد التأكيد هيتواصل معاك فريقنا لتأكيد ميعاد التوصيل.</p>
        </aside>
    </form>
</div>
