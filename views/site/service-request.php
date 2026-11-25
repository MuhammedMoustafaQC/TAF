<?php $f = fn($k, $d = '') => e($form[$k] ?? $d); ?>
<div class="container page-wrap narrow">
    <?= view('partials/crumbs', ['items' => ['طلب خدمة' => null]]) ?>
    <form method="post" class="card form-card" novalidate>
        <?= csrf_field() ?>
        <h1>طلب خدمة صيانة أو تركيب</h1>
        <p>يرجى ملء النموذج أدناه لطلب خدمة صيانة أو تركيب، وسيقوم فريقنا بالتواصل معك في أقرب وقت ممكن.</p>
        <div class="form-grid">
            <label class="field"><span>الاسم بالكامل</span><input type="text" name="name" value="<?= $f('name') ?>" placeholder="أدخل اسمك" required autocomplete="name" class="<?= inv($errors, 'name') ?>"><?= err($errors, 'name') ?></label>
            <label class="field"><span>رقم الهاتف</span><input type="tel" name="phone" value="<?= $f('phone') ?>" placeholder="01xxxxxxxxx" required dir="ltr" inputmode="tel" class="<?= inv($errors, 'phone') ?>"><?= err($errors, 'phone') ?></label>
            <label class="field"><span>المحافظة</span>
                <select name="governorate" required class="<?= inv($errors, 'governorate') ?>">
                    <option value="">اختار المحافظة</option>
                    <?php foreach (governorates() as $g): ?><option <?= ($form['governorate'] ?? '') === $g ? 'selected' : '' ?>><?= e($g) ?></option><?php endforeach; ?>
                </select><?= err($errors, 'governorate') ?>
            </label>
            <label class="field"><span>نوع الخدمة</span>
                <select name="service_type" required class="<?= inv($errors, 'service_type') ?>">
                    <?php foreach (service_types() as $s): ?><option <?= ($form['service_type'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
                </select><?= err($errors, 'service_type') ?>
            </label>
            <label class="field"><span>الميعاد المناسب (اختياري)</span><input type="date" name="preferred_date" value="<?= $f('preferred_date') ?>" min="<?= date('Y-m-d') ?>"></label>
            <label class="field span-2"><span>العنوان بالتفصيل</span><textarea name="address" rows="3" placeholder="أدخل عنوانك بالتفصيل لسهولة الوصول إليك"><?= $f('address') ?></textarea></label>
            <label class="field span-2"><span>ملاحظات</span><textarea name="notes" rows="2" placeholder="نوع الفلتر الحالي، آخر مرة غيرت فيها الشمعات..."><?= $f('notes') ?></textarea></label>
        </div>
        <div class="center"><button class="btn btn-primary btn-lg">إرسال الطلب</button></div>
        <div class="form-sep">
            <p>أو يمكنك التواصل معنا مباشرة عبر واتساب</p>
            <a href="<?= e(wa_link('عايز أطلب خدمة صيانة / تركيب')) ?>" class="btn btn-wa" target="_blank" rel="noopener"><?= icon('whatsapp', 18) ?> تواصل عبر واتساب</a>
        </div>
    </form>
</div>
