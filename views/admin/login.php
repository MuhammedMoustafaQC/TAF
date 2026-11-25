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
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
</head>
<body class="login-page">
<div class="login-shell">
    <aside class="login-brand">
        <img src="<?= asset('assets/img/logo-white.png') ?>" alt="<?= e(setting('site_name')) ?>" width="96" height="125">
        <div>
            <h2>كل طلب وكل عميل وكل فلتر، <em>في مكان واحد.</em></h2>
            <p>تابع الطلبات والتقسيط والصيانة، وحدّث المنتجات والعروض في دقايق.</p>
        </div>
        <small>© <?= date('Y') ?> <?= e(setting('site_name')) ?> <?= e(setting('site_tagline')) ?></small>
    </aside>

    <main class="login-main">
        <div class="login-box">
            <h1>تسجيل الدخول</h1>
            <p class="muted">ادخل بيانات حسابك علشان تدير الموقع.</p>
            <?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
            <form method="post" class="form">
                <?= csrf_field() ?>
                <label class="field"><span>اسم المستخدم</span><input type="text" name="username" id="username" required autofocus autocomplete="username" dir="ltr" value="<?= e($_POST['username'] ?? '') ?>"></label>
                <label class="field"><span>كلمة المرور</span><input type="password" name="password" id="password" required autocomplete="current-password" dir="ltr"></label>
                <button class="btn btn-primary btn-block btn-lg">دخول</button>
            </form>
            <a href="<?= url('/') ?>" class="back-link"><?= icon('chevron-r', 16) ?> الرجوع للموقع</a>
        </div>
    </main>
</div>
</body>
</html>
