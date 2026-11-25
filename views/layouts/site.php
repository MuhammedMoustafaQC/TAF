<?php
$siteName = setting('site_name', 'قطرات الندى');
$tagline = setting('site_tagline', 'لأنظمة معالجة المياه');
$pageTitle = isset($title) ? $title . ' | ' . $siteName : $siteName . ' ' . $tagline;
$metaDesc = $description ?? setting('meta_description');
$current = '/' . trim(substr(rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH)), strlen(base_path())), '/');
$isActive = fn(string $p) => ($p === '/' ? $current === '/' : str_starts_with($current, $p)) ? 'active' : '';
$cats = nav_categories();
$phone = setting('phone');
$socials = array_filter(['facebook' => setting('facebook'), 'instagram' => setting('instagram'), 'tiktok' => setting('tiktok'), 'youtube' => setting('youtube')]);
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($metaDesc) ?>">
    <meta name="theme-color" content="#2B04B5">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($metaDesc) ?>">
    <meta property="og:image" content="<?= e(img($og_image ?? 'assets/img/logo.png')) ?>">
    <link rel="icon" type="image/png" href="<?= asset('assets/img/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/site.css') ?>">
</head>
<body>

<?php if ($ann = setting('announcement')): ?>
<div class="announce"><div class="container"><?= icon('drop', 16) ?> <span><?= e($ann) ?></span></div></div>
<?php endif; ?>

<div class="topbar">
    <div class="container topbar-inner">
        <div class="topbar-info">
            <?php if ($phone): ?><a href="<?= e(tel_link($phone)) ?>"><?= icon('phone', 15) ?> <span dir="ltr"><?= e($phone) ?></span></a><?php endif; ?>
            <?php if ($em = setting('email')): ?><a href="mailto:<?= e($em) ?>" class="hide-sm"><?= icon('mail', 15) ?> <?= e($em) ?></a><?php endif; ?>
            <?php if ($wh = setting('working_hours')): ?><span class="hide-md"><?= icon('clock', 15) ?> <?= e($wh) ?></span><?php endif; ?>
        </div>
        <div class="topbar-links">
            <a href="<?= url('/track') ?>"><?= icon('truck', 15) ?> تتبع طلبك</a>
            <span class="socials">
                <?php foreach ($socials as $net => $link): ?>
                    <a href="<?= e($link) ?>" target="_blank" rel="noopener" aria-label="<?= e($net) ?>"><?= icon($net, 15) ?></a>
                <?php endforeach; ?>
            </span>
        </div>
    </div>
</div>

<header class="header" id="header">
    <div class="container header-inner">
        <button class="icon-btn menu-toggle" data-open-drawer aria-label="القائمة"><?= icon('menu', 24) ?></button>

        <a href="<?= url('/') ?>" class="brand" aria-label="<?= e($siteName) ?>">
            <img src="<?= asset('assets/img/logo-mark.png') ?>" alt="" width="42" height="64">
            <span class="brand-text">
                <strong><?= e($siteName) ?></strong>
                <small><?= e($tagline) ?></small>
            </span>
        </a>

        <form class="search" action="<?= url('/search') ?>" method="get" role="search">
            <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="دوّر على فلتر، شمعة، قطعة غيار..." aria-label="بحث">
            <button type="submit" aria-label="بحث"><?= icon('search', 20) ?></button>
        </form>

        <div class="header-actions">
            <a href="<?= e(wa_link('السلام عليكم، عايز أستفسر عن الفلاتر')) ?>" class="hdr-wa hide-md" target="_blank" rel="noopener">
                <?= icon('whatsapp', 22) ?>
                <span><small>اطلب على واتساب</small><b dir="ltr"><?= e($phone) ?></b></span>
            </a>
            <a href="<?= url('/cart') ?>" class="icon-btn cart-btn" aria-label="السلة">
                <?= icon('cart', 24) ?>
                <span class="cart-count" data-cart-count <?= cart_count() ? '' : 'hidden' ?>><?= cart_count() ?></span>
            </a>
        </div>
    </div>

    <nav class="nav">
        <div class="container">
            <ul class="nav-list">
                <li><a href="<?= url('/') ?>" class="<?= $isActive('/') ?>">الرئيسية</a></li>
                <li class="has-drop">
                    <a href="<?= url('/shop') ?>" class="<?= $isActive('/shop') ?: $isActive('/category') ?>">المنتجات <?= icon('chevron-d', 16) ?></a>
                    <div class="dropdown">
                        <?php foreach ($cats as $c): ?>
                            <a href="<?= url('/category/' . $c['slug']) ?>">
                                <img src="<?= e(img($c['image'])) ?>" alt="" loading="lazy">
                                <span><?= e($c['name']) ?><small><?= (int)$c['products_count'] ?> منتج</small></span>
                            </a>
                        <?php endforeach; ?>
                        <a href="<?= url('/shop') ?>" class="drop-all">كل المنتجات <?= icon('chevron-l', 16) ?></a>
                    </div>
                </li>
                <?php foreach (array_slice($cats, 0, 3) as $c): ?>
                    <li class="hide-lg"><a href="<?= url('/category/' . $c['slug']) ?>" class="<?= $isActive('/category/' . $c['slug']) ?>"><?= e($c['name']) ?></a></li>
                <?php endforeach; ?>
                <li><a href="<?= url('/offers') ?>" class="<?= $isActive('/offers') ?> nav-hot">العروض</a></li>
                <li><a href="<?= url('/maintenance') ?>" class="<?= $isActive('/maintenance') ?>">طلب صيانة</a></li>
                <li><a href="<?= url('/about') ?>" class="<?= $isActive('/about') ?>">من نحن</a></li>
                <li><a href="<?= url('/contact') ?>" class="<?= $isActive('/contact') ?>">تواصل معنا</a></li>
            </ul>
        </div>
    </nav>
</header>

<!-- قائمة الموبايل -->
<div class="drawer" id="drawer" aria-hidden="true">
    <div class="drawer-backdrop" data-close-drawer></div>
    <aside class="drawer-panel">
        <div class="drawer-head">
            <a href="<?= url('/') ?>" class="brand">
                <img src="<?= asset('assets/img/logo-mark.png') ?>" alt="" width="34" height="52">
                <span class="brand-text"><strong><?= e($siteName) ?></strong><small><?= e($tagline) ?></small></span>
            </a>
            <button class="icon-btn" data-close-drawer aria-label="إغلاق"><?= icon('x', 22) ?></button>
        </div>
        <form class="search search-drawer" action="<?= url('/search') ?>" method="get">
            <input type="search" name="q" placeholder="ابحث عن منتج..." aria-label="بحث">
            <button type="submit" aria-label="بحث"><?= icon('search', 18) ?></button>
        </form>
        <nav class="drawer-nav">
            <a href="<?= url('/') ?>"><?= icon('home', 18) ?> الرئيسية</a>
            <a href="<?= url('/shop') ?>"><?= icon('grid', 18) ?> كل المنتجات</a>
            <div class="drawer-cats">
                <?php foreach ($cats as $c): ?><a href="<?= url('/category/' . $c['slug']) ?>"><?= e($c['name']) ?></a><?php endforeach; ?>
            </div>
            <a href="<?= url('/offers') ?>"><?= icon('tag', 18) ?> العروض</a>
            <a href="<?= url('/maintenance') ?>"><?= icon('tool', 18) ?> طلب صيانة / تركيب</a>
            <a href="<?= url('/track') ?>"><?= icon('truck', 18) ?> تتبع طلبك</a>
            <a href="<?= url('/about') ?>"><?= icon('drop', 18) ?> من نحن</a>
            <a href="<?= url('/faq') ?>"><?= icon('help', 18) ?> الأسئلة الشائعة</a>
            <a href="<?= url('/contact') ?>"><?= icon('message', 18) ?> تواصل معنا</a>
        </nav>
        <div class="drawer-foot">
            <a href="<?= e(tel_link($phone)) ?>" class="btn btn-outline btn-block"><?= icon('phone', 18) ?> <span dir="ltr"><?= e($phone) ?></span></a>
        </div>
    </aside>
</div>

<main id="main">
    <?php if ($m = flash('success')): ?>
        <div class="container"><div class="alert alert-success"><?= icon('check', 18) ?> <?= e($m) ?></div></div>
    <?php endif; ?>
    <?php if ($m = flash('error')): ?>
        <div class="container"><div class="alert alert-error"><?= icon('x', 18) ?> <?= e($m) ?></div></div>
    <?php endif; ?>

    <?= $content ?>
</main>

<section class="features-strip">
    <div class="container features-grid">
        <div class="feature"><span class="f-ico"><?= icon('truck', 26) ?></span><div><b>توصيل لكل المحافظات</b><small>شحن مجاني فوق <?= money(setting('free_shipping_min', 0)) ?></small></div></div>
        <div class="feature"><span class="f-ico"><?= icon('tool', 26) ?></span><div><b>تركيب مجاني</b><small>بأيدي فنيين متخصصين</small></div></div>
        <div class="feature"><span class="f-ico"><?= icon('shield', 26) ?></span><div><b>ضمان حقيقي</b><small>وقطع غيار أصلية</small></div></div>
        <div class="feature"><span class="f-ico"><?= icon('cash', 26) ?></span><div><b>الدفع عند الاستلام</b><small>أو فودافون كاش / إنستاباي</small></div></div>
    </div>
</section>

<footer class="footer">
    <div class="footer-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none"><path d="M0 40c120 26 240 40 360 26S600 10 720 14s240 40 360 44 240-18 360-34v56H0z"/></svg>
    </div>
    <div class="container footer-grid">
        <div class="footer-about">
            <a href="<?= url('/') ?>" class="footer-logo"><img src="<?= asset('assets/img/logo-white.png') ?>" alt="<?= e($siteName) ?>" width="120" height="157" loading="lazy"></a>
            <p><?= e(strtok(setting('about_text'), "\n")) ?></p>
            <div class="socials socials-lg">
                <?php foreach ($socials as $net => $link): ?>
                    <a href="<?= e($link) ?>" target="_blank" rel="noopener" aria-label="<?= e($net) ?>"><?= icon($net, 18) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div>
            <h4>روابط سريعة</h4>
            <ul>
                <li><a href="<?= url('/shop') ?>">كل المنتجات</a></li>
                <li><a href="<?= url('/offers') ?>">العروض</a></li>
                <li><a href="<?= url('/maintenance') ?>">طلب صيانة / تركيب</a></li>
                <li><a href="<?= url('/track') ?>">تتبع طلبك</a></li>
                <li><a href="<?= url('/faq') ?>">الأسئلة الشائعة</a></li>
                <li><a href="<?= url('/about') ?>">من نحن</a></li>
            </ul>
        </div>
        <div>
            <h4>الأقسام</h4>
            <ul>
                <?php foreach ($cats as $c): ?><li><a href="<?= url('/category/' . $c['slug']) ?>"><?= e($c['name']) ?></a></li><?php endforeach; ?>
            </ul>
        </div>
        <div>
            <h4>تواصل معنا</h4>
            <ul class="contact-list">
                <?php if ($phone): ?><li><?= icon('phone', 18) ?> <a href="<?= e(tel_link($phone)) ?>" dir="ltr"><?= e($phone) ?></a></li><?php endif; ?>
                <?php if ($p2 = setting('phone2')): ?><li><?= icon('phone', 18) ?> <a href="<?= e(tel_link($p2)) ?>" dir="ltr"><?= e($p2) ?></a></li><?php endif; ?>
                <li><?= icon('whatsapp', 18) ?> <a href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">راسلنا على واتساب</a></li>
                <?php if ($em = setting('email')): ?><li><?= icon('mail', 18) ?> <a href="mailto:<?= e($em) ?>"><?= e($em) ?></a></li><?php endif; ?>
                <?php if ($ad = setting('address')): ?><li><?= icon('pin', 18) ?> <span><?= e($ad) ?></span></li><?php endif; ?>
                <?php if ($wh = setting('working_hours')): ?><li><?= icon('clock', 18) ?> <span><?= e($wh) ?></span></li><?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <span>© <?= date('Y') ?> <?= e($siteName) ?> <?= e($tagline) ?> — جميع الحقوق محفوظة</span>
            <span class="pay-methods"><?= icon('cash', 16) ?> الدفع عند الاستلام · فودافون كاش · إنستاباي</span>
        </div>
    </div>
</footer>

<div class="floating">
    <a href="<?= e(wa_link('السلام عليكم، عايز أستفسر عن الفلاتر')) ?>" class="fab fab-wa" target="_blank" rel="noopener" aria-label="واتساب"><?= icon('whatsapp', 28) ?></a>
    <a href="<?= e(tel_link($phone)) ?>" class="fab fab-call" aria-label="اتصل بنا"><?= icon('phone', 24) ?></a>
    <button class="fab fab-top" id="toTop" aria-label="لأعلى"><?= icon('arrow-up', 22) ?></button>
</div>

<div class="toast-wrap" id="toasts" aria-live="polite"></div>

<script>window.APP = { csrf: <?= json_encode(csrf_token()) ?>, cartUrl: <?= json_encode(url('/cart')) ?> };</script>
<script src="<?= asset('assets/js/site.js') ?>" defer></script>
</body>
</html>
