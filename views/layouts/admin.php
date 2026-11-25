<?php
$me = admin();
$current = '/' . trim(substr(rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH)), strlen(base_path())), '/');
$nav = [
    ['/admin',              'chart',    'الرئيسية',          null],
    ['/admin/orders',       'bag',      'الطلبات',           $badges['orders'] ?? 0],
    ['/admin/installments', 'cash',     'طلبات التقسيط',     $badges['installments'] ?? 0],
    ['/admin/requests',     'tool',     'طلبات الصيانة',     $badges['requests'] ?? 0],
    ['/admin/customers',    'user',     'العملاء',           null],
    ['/admin/messages',     'message',  'رسائل العملاء',     $badges['messages'] ?? 0],
    ['—', '', 'المتجر', null],
    ['/admin/products',     'box',      'المنتجات',          null],
    ['/admin/categories',   'grid',     'الأقسام',           null],
    ['/admin/brands',       'award',    'الماركات',          null],
    ['/admin/reviews',      'star',     'التقييمات',         $badges['reviews'] ?? 0],
    ['—', '', 'محتوى الموقع', null],
    ['/admin/slides',       'image',    'السلايدر',          null],
    ['/admin/banners',      'tag',      'البانرات',          null],
    ['/admin/posts',        'quote',    'المدونة',           null],
    ['/admin/pages',        'edit',     'الصفحات',           null],
    ['/admin/faqs',         'help',     'الأسئلة الشائعة',   null],
    ['/admin/subscribers',  'mail',     'المشتركين',         null],
    ['—', '', 'الإعدادات', null],
    ['/admin/settings',     'settings', 'إعدادات الموقع',    null],
    ['/admin/users',        'users',    'المديرين',          null],
];
$active = fn($p) => $p === '/admin' ? $current === '/admin' : str_starts_with($current, $p);
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title ?? 'لوحة التحكم') ?> | لوحة تحكم <?= e(setting('site_name')) ?></title>
    <link rel="icon" type="image/png" href="<?= asset('assets/img/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
</head>
<body class="admin">
<aside class="sidebar" id="sidebar">
    <a href="<?= url('/admin') ?>" class="side-brand">
        <img src="<?= asset('assets/img/logo-mark.png') ?>" alt="" width="34" height="52">
        <span><b><?= e(setting('site_name')) ?></b><small>لوحة التحكم</small></span>
    </a>
    <nav class="side-nav">
        <?php foreach ($nav as [$href, $ico, $label, $count]): ?>
            <?php if ($href === '—'): ?>
                <div class="side-sep"><?= e($label) ?></div>
            <?php else: ?>
                <a href="<?= url($href) ?>" class="<?= $active($href) ? 'active' : '' ?>">
                    <?= icon($ico, 19) ?> <span><?= e($label) ?></span>
                    <?php if ($count): ?><em class="count"><?= (int)$count ?></em><?php endif; ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
    <div class="side-foot">
        <a href="<?= url('/') ?>" target="_blank" rel="noopener"><?= icon('external', 18) ?> <span>عرض الموقع</span></a>
    </div>
</aside>
<div class="side-backdrop" data-side-close></div>

<div class="main">
    <header class="topbar">
        <button class="icon-btn menu-btn" data-side-open aria-label="القائمة"><?= icon('menu', 22) ?></button>
        <h1><?= e($title ?? '') ?></h1>
        <div class="top-user">
            <span class="avatar"><?= e(mb_substr($me['name'] ?? 'م', 0, 1)) ?></span>
            <span class="hide-sm"><b><?= e($me['name'] ?? '') ?></b><small><?= e($me['username'] ?? '') ?></small></span>
            <form action="<?= url('/admin/logout') ?>" method="post">
                <?= csrf_field() ?>
                <button class="icon-btn" title="تسجيل الخروج" aria-label="تسجيل الخروج"><?= icon('logout', 20) ?></button>
            </form>
        </div>
    </header>

    <div class="content">
        <?php if ($m = flash('success')): ?><div class="alert alert-success"><?= icon('check', 18) ?> <?= e($m) ?></div><?php endif; ?>
        <?php if ($m = flash('error')): ?><div class="alert alert-error"><?= icon('x', 18) ?> <?= e($m) ?></div><?php endif; ?>
        <?= $content ?>
    </div>
</div>

<script src="<?= asset('assets/js/admin.js') ?>" defer></script>
</body>
</html>
