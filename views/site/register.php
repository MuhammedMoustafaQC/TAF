<?php $f = fn($k) => e($form[$k] ?? ''); ?>
<div class="container page-wrap">
    <form method="post" class="card form-card auth-card" novalidate>
        <?= csrf_field() ?>
        <h1 class="center">إنشاء حساب جديد</h1>
        <p class="muted center">تابع طلباتك واحفظ المفضلة واطلب أسرع.</p>
        <div class="form-grid">
            <label class="field"><span>الاسم بالكامل *</span><input type="text" name="name" value="<?= $f('name') ?>" required autocomplete="name" class="<?= inv($errors, 'name') ?>"><?= err($errors, 'name') ?></label>
            <label class="field"><span>رقم الموبايل *</span><input type="tel" name="phone" value="<?= $f('phone') ?>" placeholder="01xxxxxxxxx" required dir="ltr" autocomplete="tel" inputmode="tel" class="<?= inv($errors, 'phone') ?>"><?= err($errors, 'phone') ?></label>
            <label class="field"><span>البريد الإلكتروني</span><input type="email" name="email" value="<?= $f('email') ?>" placeholder="اختياري" dir="ltr" autocomplete="email" class="<?= inv($errors, 'email') ?>"><?= err($errors, 'email') ?></label>
            <label class="field"><span>كلمة المرور *</span><input type="password" name="password" required minlength="6" autocomplete="new-password" dir="ltr" class="<?= inv($errors, 'password') ?>"><?= err($errors, 'password') ?></label>
        </div>
        <button class="btn btn-primary btn-lg btn-block">إنشاء الحساب</button>
        <p class="auth-alt">عندك حساب؟ <a href="<?= url('/account/login') ?>"><b>سجّل دخول</b></a></p>
    </form>
</div>
