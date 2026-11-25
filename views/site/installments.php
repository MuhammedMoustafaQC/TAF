<?php $f = fn($k, $d = '') => e($form[$k] ?? $d); $months = (int)($form['months'] ?? 4); ?>
<div class="container page-wrap narrow">
    <?= view('partials/crumbs', ['items' => ['التقسيط' => null]]) ?>
    <form method="post" class="card form-card" novalidate>
        <?= csrf_field() ?>
        <h1>تقديم طلب تقسيط</h1>
        <p><?= e(setting('installment_note', 'قسّط مشترياتك بسهولة من غير بنك ومن غير تعقيد.')) ?> املا البيانات وفريقنا هيتواصل معاك خلال 24 ساعة لاستكمال الإجراءات.</p>
        <?php if ($product): ?>
            <input type="hidden" name="product" value="<?= (int)$product['id'] ?>">
            <div class="inst-product">
                <img src="<?= e(img($product['image'])) ?>" alt="">
                <div><b><?= e($product['name']) ?></b><br><span class="muted">السعر: <?= money($product['price']) ?></span></div>
            </div>
        <?php endif; ?>
        <div class="form-grid">
            <label class="field"><span>الاسم بالكامل *</span><input type="text" name="name" value="<?= $f('name') ?>" required class="<?= inv($errors, 'name') ?>"><?= err($errors, 'name') ?></label>
            <label class="field"><span>رقم الموبايل *</span><input type="tel" name="phone" value="<?= $f('phone') ?>" placeholder="01xxxxxxxxx" required dir="ltr" inputmode="tel" class="<?= inv($errors, 'phone') ?>"><?= err($errors, 'phone') ?></label>
            <label class="field"><span>المحافظة *</span>
                <select name="governorate" required class="<?= inv($errors, 'governorate') ?>">
                    <option value="">اختار المحافظة</option>
                    <?php foreach (governorates() as $g): ?><option <?= ($form['governorate'] ?? '') === $g ? 'selected' : '' ?>><?= e($g) ?></option><?php endforeach; ?>
                </select><?= err($errors, 'governorate') ?>
            </label>
            <label class="field"><span>الوظيفة</span><input type="text" name="job" value="<?= $f('job') ?>" placeholder="اختياري"></label>
            <?php if (!$product): ?>
            <label class="field span-2"><span>المنتج اللي عايز تقسطه *</span><input type="text" name="product_name" value="<?= $f('product_name') ?>" placeholder="مثلاً: فلتر 7 مراحل" class="<?= inv($errors, 'product_name') ?>"><?= err($errors, 'product_name') ?></label>
            <?php endif; ?>
            <div class="field span-2">
                <span>مدة التقسيط *</span>
                <div class="inst-plans">
                    <?php foreach ([3, 4, 6, 12] as $m): ?>
                    <label class="inst-plan"><input type="radio" name="months" value="<?= $m ?>" <?= $months === $m ? 'checked' : '' ?>><span><b><?= $m ?></b><small>شهور<?php if ($product): ?><br><?= number_format(ceil($product['price'] / $m)) ?> ج.م/شهر<?php endif; ?></small></span></label>
                    <?php endforeach; ?>
                </div>
                <?= err($errors, 'months') ?>
            </div>
            <label class="field span-2"><span>ملاحظات</span><textarea name="notes" rows="2"><?= $f('notes') ?></textarea></label>
        </div>
        <div class="center"><button class="btn btn-primary btn-lg">إرسال طلب التقسيط</button></div>
        <p class="muted small center" style="margin-top:14px">مش هنطلب منك أي مستندات هنا — فريقنا هيشرحلك المطلوب لما يتواصل معاك.</p>
    </form>
</div>
