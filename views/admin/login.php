<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>تسجيل الدخول | لوحة تحكم <?= e(setting('site_name')) ?></title>
    <link rel="icon" type="image/png" href="<?= asset('assets/img/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
</head>
<body class="login-page">
    <div class="login-box">
        <img src="<?= asset('assets/img/logo.png') ?>" alt="<?= e(setting('site_name')) ?>" class="login-logo" width="140" height="183">
        <h1>لوحة التحكم</h1>
        <p class="muted">سجّل دخولك علشان تدير الموقع</p>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" class="form">
            <?= csrf_field() ?>
            <label class="field"><span>اسم المستخدم</span><input type="text" name="username" required autofocus autocomplete="username" dir="ltr" value="<?= e($_POST['username'] ?? '') ?>"></label>
            <label class="field"><span>كلمة المرور</span><input type="password" name="password" required autocomplete="current-password" dir="ltr"></label>
            <button class="btn btn-primary btn-block btn-lg">دخول</button>
        </form>
        <a href="<?= url('/') ?>" class="back-link">← الرجوع للموقع</a>
    </div>
</body>
</html>
