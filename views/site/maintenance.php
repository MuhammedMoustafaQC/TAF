<?php $f = fn($k, $d = '') => e($form[$k] ?? $d); ?>
<div class="page-hero page-hero-sm">
    <div class="container">
        <nav class="breadcrumb"><a href="<?= url('/') ?>">الرئيسية</a> <span>/</span> <b>طلب صيانة / تركيب</b></nav>
        <h1>احجز صيانة أو تركيب</h1>
        <p>املا البيانات وفريقنا الفني هيتواصل معاك لتأكيد الميعاد.</p>
    </div>
</div>

<section class="section section-tight">
    <div class="container two-col">
        <form method="post" class="card form-card" novalidate>
            <?= csrf_field() ?>
            <div class="form-grid">
                <label class="field">
                    <span>الاسم *</span>
                    <input type="text" name="name" value="<?= $f('name') ?>" required autocomplete="name" class="<?= inv($errors, 'name') ?>">
                    <?= err($errors, 'name') ?>
                </label>
                <label class="field">
                    <span>رقم الموبايل *</span>
                    <input type="tel" name="phone" value="<?= $f('phone') ?>" required dir="ltr" placeholder="01xxxxxxxxx" inputmode="tel" class="<?= inv($errors, 'phone') ?>">
                    <?= err($errors, 'phone') ?>
                </label>
                <label class="field">
                    <span>نوع الخدمة *</span>
                    <select name="service_type" required class="<?= inv($errors, 'service_type') ?>">
                        <option value="">اختار الخدمة</option>
                        <?php foreach (service_types() as $s): ?><option <?= ($form['service_type'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
                    </select>
                    <?= err($errors, 'service_type') ?>
                </label>
                <label class="field">
                    <span>المحافظة *</span>
                    <select name="governorate" required class="<?= inv($errors, 'governorate') ?>">
                        <option value="">اختار المحافظة</option>
                        <?php foreach (governorates() as $g): ?><option <?= ($form['governorate'] ?? '') === $g ? 'selected' : '' ?>><?= e($g) ?></option><?php endforeach; ?>
                    </select>
                    <?= err($errors, 'governorate') ?>
                </label>
                <label class="field span-2">
                    <span>العنوان</span>
                    <input type="text" name="address" value="<?= $f('address') ?>" placeholder="المدينة، المنطقة، الشارع">
                </label>
                <label class="field">
                    <span>الميعاد المناسب</span>
                    <input type="date" name="preferred_date" value="<?= $f('preferred_date') ?>" min="<?= date('Y-m-d') ?>">
                </label>
                <label class="field span-2">
                    <span>تفاصيل إضافية</span>
                    <textarea name="notes" rows="4" placeholder="نوع الفلتر الحالي، آخر مرة غيرت فيها الشمعات..."><?= $f('notes') ?></textarea>
                </label>
            </div>
            <button class="btn btn-primary btn-lg btn-block"><?= icon('calendar', 20) ?> احجز الزيارة</button>
        </form>

        <aside class="info-stack">
            <div class="card info-card">
                <h3>خدماتنا الفنية</h3>
                <ul class="checks">
                    <li><?= icon('check', 18) ?> تركيب الفلاتر الجديدة مجاناً</li>
                    <li><?= icon('check', 18) ?> تغيير الشمعات والممبرين</li>
                    <li><?= icon('check', 18) ?> صيانة وإصلاح كل أنواع الفلاتر</li>
                    <li><?= icon('check', 18) ?> تحليل مياه مجاني وقياس الأملاح</li>
                    <li><?= icon('check', 18) ?> تصميم وتركيب محطات التحلية</li>
                </ul>
            </div>
            <div class="card info-card info-card-brand">
                <h3>مستعجل؟</h3>
                <p>كلمنا على طول وهنظبطلك أقرب ميعاد.</p>
                <a href="<?= e(tel_link(setting('phone'))) ?>" class="btn btn-white btn-block"><?= icon('phone', 18) ?> <span dir="ltr"><?= e(setting('phone')) ?></span></a>
                <a href="<?= e(wa_link('عايز أحجز صيانة للفلتر')) ?>" class="btn btn-wa btn-block" target="_blank" rel="noopener"><?= icon('whatsapp', 18) ?> واتساب</a>
            </div>
        </aside>
    </div>
</section>
