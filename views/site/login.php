<div class="container page-wrap">
    <form method="post" class="card form-card auth-card">
        <?= csrf_field() ?>
        <h1 class="center">تسجيل الدخول</h1>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <div class="form-grid">
            <label class="field"><span>رقم الموبايل</span><input type="tel" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="01xxxxxxxxx" required dir="ltr" autocomplete="tel" inputmode="tel" autofocus></label>
            <label class="field"><span>كلمة المرور</span><input type="password" name="password" required autocomplete="current-password" dir="ltr"></label>
        </div>
        <button class="btn btn-primary btn-lg btn-block">دخول</button>
        <p class="auth-alt">معندكش حساب؟ <a href="<?= url('/account/register') ?>"><b>أنشئ حساب جديد</b></a></p>
        <p class="auth-alt small">نسيت كلمة المرور؟ <a href="<?= e(wa_link('نسيت كلمة المرور لحسابي على الموقع')) ?>" target="_blank" rel="noopener">كلمنا على واتساب</a></p>
    </form>
</div>
