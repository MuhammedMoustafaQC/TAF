<?php
$siteName = setting('site_name', 'قطرات الندى');
$tagline = setting('site_tagline', 'لأنظمة معالجة المياه');
$pageTitle = isset($title) ? $title . ' | ' . $siteName : $siteName . ' ' . $tagline;
$metaDesc = $description ?? setting('meta_description');
$current = '/' . trim(substr(rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH)), strlen(base_path())), '/');
$isActive = fn(string $p) => ($p === '/' ? $current === '/' : str_starts_with($current, $p)) ? 'active' : '';
$cats = nav_categories();
$phone = setting('phone');
$me = customer();
$wishCount = count(wishlist_ids());
$loc = delivery_location();
$navPosts = rows('SELECT title, slug FROM posts WHERE is_published = 1 ORDER BY created_at DESC LIMIT 4');
$socials = array_filter(['facebook' => setting('facebook'), 'instagram' => setting('instagram'), 'telegram' => setting('telegram'), 'tiktok' => setting('tiktok'), 'youtube' => setting('youtube')]);
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
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/site.css') ?>">
</head>
<body>

<header class="header" id="header">
    <div class="container header-inner">
        <button class="icon-btn menu-toggle" data-open-drawer aria-label="القائمة"><?= icon('menu', 24) ?></button>

        <a href="<?= url('/') ?>" class="brand" aria-label="<?= e($siteName) ?>">
            <img src="<?= asset('assets/img/logo-mark.png') ?>" alt="" width="36" height="55">
            <span class="brand-text"><strong><?= e($siteName) ?></strong><small><?= e($tagline) ?></small></span>
        </a>

        <button class="location-btn" data-open-modal="location-modal" type="button">
            <?= icon('pin', 22) ?>
            <span><small>التوصيل إلى</small><b data-location-label><?= $loc ? e($loc) : 'اختر موقعك' ?></b></span>
        </button>

        <form class="search" action="<?= url('/search') ?>" method="get" role="search">
            <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="ابحث عن فلتر 7 مراحل..." aria-label="بحث">
            <button type="submit" aria-label="بحث"><?= icon('search', 18) ?></button>
        </form>

        <nav class="header-icons" aria-label="حسابي">
            <a href="<?= url($me ? '/account' : '/account/login') ?>" class="hicon hide-md" title="<?= $me ? 'حسابي' : 'تسجيل الدخول' ?>" aria-label="حسابي">
                <?= icon($me ? 'user' : 'user-plus', 24) ?>
            </a>
            <a href="<?= url('/wishlist') ?>" class="hicon" title="المفضلة" aria-label="المفضلة">
                <?= icon('heart', 24, 'filled') ?>
                <span class="hcount" data-wish-count <?= $wishCount ? '' : 'hidden' ?>><?= $wishCount ?></span>
            </a>
            <a href="<?= url('/cart') ?>" class="hicon" title="عربة التسوق" aria-label="عربة التسوق">
                <?= icon('cart', 24, 'filled') ?>
                <span class="hcount" data-cart-count <?= cart_count() ? '' : 'hidden' ?>><?= cart_count() ?></span>
            </a>
        </nav>
    </div>

    <nav class="nav" aria-label="القائمة الرئيسية">
        <ul class="container nav-list">
            <li class="has-drop has-mega">
                <a href="<?= url('/shop') ?>" class="<?= $isActive('/shop') ?: $isActive('/category') ?>">كل المنتجات <?= icon('chevron-d', 14) ?></a>
                <div class="dropdown mega">
                    <?php foreach ($cats as $c): if ($c['slug'] === 'offers') continue; ?>
                        <div class="mega-col">
                            <a href="<?= url('/category/' . $c['slug']) ?>" class="mega-head">
                                <img src="<?= e(img($c['image'])) ?>" alt="" loading="lazy"><span><?= e($c['name']) ?></span>
                            </a>
                            <?php foreach ($c['children'] as $ch): ?><a href="<?= url('/category/' . $ch['slug']) ?>" class="mega-link"><?= e($ch['name']) ?></a><?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    <a href="<?= url('/shop') ?>" class="mega-all">تصفح كل المنتجات <?= icon('chevron-l', 16) ?></a>
                </div>
            </li>
            <li class="has-drop">
                <a href="<?= url('/offers') ?>" class="<?= $isActive('/offers') ?>">تخفيضات <?= icon('chevron-d', 14) ?></a>
                <div class="dropdown">
                    <a href="<?= url('/offers') ?>">كل التخفيضات</a>
                    <?php foreach ($cats as $c): if ($c['slug'] === 'offers') continue; ?>
                        <a href="<?= url('/offers?cat[]=' . urlencode($c['slug'])) ?>">عروض <?= e($c['name']) ?></a>
                    <?php endforeach; ?>
                </div>
            </li>
            <li><a href="<?= url('/services') ?>" class="<?= $isActive('/services') ?>">خدماتنا</a></li>
            <li><a href="<?= url('/service-request') ?>" class="<?= $isActive('/service-request') ?>">طلب خدمة</a></li>
            <li class="has-drop">
                <a href="<?= url('/blog') ?>" class="<?= $isActive('/blog') ?>">المدونة <?= icon('chevron-d', 14) ?></a>
                <div class="dropdown">
                    <?php foreach ($navPosts as $np): ?><a href="<?= url('/blog/' . $np['slug']) ?>"><?= e($np['title']) ?></a><?php endforeach; ?>
                    <a href="<?= url('/blog') ?>" class="drop-all">كل المقالات</a>
                </div>
            </li>
            <li><a href="<?= url('/contact') ?>" class="<?= $isActive('/contact') ?>">اتصل بنا</a></li>
            <li><a href="<?= url('/installments') ?>" class="<?= $isActive('/installments') ?>">التقسيط</a></li>
        </ul>
    </nav>
</header>

<!-- قائمة الموبايل -->
<div class="drawer" id="drawer" aria-hidden="true">
    <div class="drawer-backdrop" data-close-drawer></div>
    <aside class="drawer-panel">
        <div class="drawer-head">
            <a href="<?= url('/') ?>" class="brand">
                <img src="<?= asset('assets/img/logo-mark.png') ?>" alt="" width="30" height="46">
                <span class="brand-text"><strong><?= e($siteName) ?></strong><small><?= e($tagline) ?></small></span>
            </a>
            <button class="icon-btn" data-close-drawer aria-label="إغلاق"><?= icon('x', 22) ?></button>
        </div>
        <button class="drawer-loc" data-open-modal="location-modal" type="button"><?= icon('pin', 18) ?> التوصيل إلى: <b data-location-label><?= $loc ? e($loc) : 'اختر موقعك' ?></b></button>
        <nav class="drawer-nav">
            <a href="<?= url('/') ?>"><?= icon('home', 18) ?> الرئيسية</a>
            <details>
                <summary><?= icon('grid', 18) ?> كل المنتجات <?= icon('chevron-d', 16) ?></summary>
                <div class="drawer-cats">
                    <a href="<?= url('/shop') ?>">كل المنتجات</a>
                    <?php foreach ($cats as $c): if ($c['slug'] === 'offers') continue; ?>
                        <a href="<?= url('/category/' . $c['slug']) ?>"><?= e($c['name']) ?></a>
                        <?php foreach ($c['children'] as $ch): ?><a href="<?= url('/category/' . $ch['slug']) ?>" class="sub">— <?= e($ch['name']) ?></a><?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </details>
            <a href="<?= url('/offers') ?>"><?= icon('tag', 18) ?> تخفيضات</a>
            <a href="<?= url('/services') ?>"><?= icon('award', 18) ?> خدماتنا</a>
            <a href="<?= url('/service-request') ?>"><?= icon('tool', 18) ?> طلب خدمة</a>
            <a href="<?= url('/installments') ?>"><?= icon('cash', 18) ?> التقسيط</a>
            <a href="<?= url('/blog') ?>"><?= icon('book', 18) ?> المدونة</a>
            <a href="<?= url('/track') ?>"><?= icon('truck', 18) ?> تتبع طلبك</a>
            <a href="<?= url('/contact') ?>"><?= icon('phone', 18) ?> اتصل بنا</a>
            <a href="<?= url($me ? '/account' : '/account/login') ?>"><?= icon('user', 18) ?> <?= $me ? 'حسابي' : 'تسجيل الدخول / حساب جديد' ?></a>
        </nav>
    </aside>
</div>

<main id="main">
    <?php if ($m = flash('success')): ?>
        <div class="container"><div class="alert alert-success" data-autohide><?= icon('check', 18) ?> <?= e($m) ?></div></div>
    <?php endif; ?>
    <?php if ($m = flash('error')): ?>
        <div class="container"><div class="alert alert-error"><?= icon('x', 18) ?> <?= e($m) ?></div></div>
    <?php endif; ?>

    <?= $content ?>
</main>

<footer class="footer">
    <div class="container">
        <section class="newsletter">
            <div class="nl-company">
                <img src="<?= asset('assets/img/logo-white.png') ?>" alt="<?= e($siteName) ?>" width="70" height="92" loading="lazy">
                <b><?= e(setting('company_name', $siteName)) ?></b>
                <?php if ($tax = setting('tax_number')): ?><small>رقم التسجيل الضريبي: <?= e($tax) ?></small><?php endif; ?>
                <small><?= e(setting('address')) ?></small>
            </div>
            <div class="nl-form">
                <h3>انضم إلى مجتمع <?= e($siteName) ?></h3>
                <p>اشترك في النشرة البريدية لتكون أول من يعلم عن العروض الحصرية وأحدث المنتجات ونصائح خبراء المياه.</p>
                <form action="<?= url('/newsletter') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="nl-input">
                        <input type="email" name="email" placeholder="أدخل بريدك الإلكتروني..." required aria-label="البريد الإلكتروني" dir="rtl">
                        <button aria-label="اشتراك"><?= icon('chevron-l', 20) ?></button>
                    </div>
                    <label class="nl-agree"><input type="checkbox" name="agree" value="1" required> أوافق على <a href="<?= url('/page/privacy') ?>">سياسة الخصوصية</a> والشروط والأحكام.</label>
                </form>
            </div>
            <div class="nl-app">
                <b>كلمنا مباشرة</b>
                <small>فريق المبيعات جاهز يرد على استفساراتك</small>
                <div class="nl-btns">
                    <a href="<?= e(wa_link('السلام عليكم، عايز أستفسر')) ?>" target="_blank" rel="noopener"><?= icon('whatsapp', 20) ?><span><small>راسلنا على</small>واتساب</span></a>
                    <a href="<?= e(tel_link($phone)) ?>"><?= icon('phone', 20) ?><span><small>اتصل بنا</small><span dir="ltr"><?= e($phone) ?></span></span></a>
                </div>
            </div>
        </section>

        <div class="footer-grid">
            <div>
                <h4><?= e($siteName) ?></h4>
                <ul>
                    <li><a href="<?= url('/') ?>">الرئيسية</a></li>
                    <li><a href="<?= url('/about') ?>">من نحن</a></li>
                    <li><a href="<?= url('/offers') ?>">تخفيضات</a></li>
                    <li><a href="<?= url('/blog') ?>">المدونة والأخبار</a></li>
                    <li><a href="<?= url('/brands') ?>">الماركات</a></li>
                </ul>
            </div>
            <div>
                <h4>المنتجات</h4>
                <ul>
                    <?php foreach ($cats as $c): if ($c['slug'] === 'offers') continue; ?><li><a href="<?= url('/category/' . $c['slug']) ?>"><?= e($c['name']) ?></a></li><?php endforeach; ?>
                </ul>
            </div>
            <div>
                <h4>الشراء من <?= e($siteName) ?></h4>
                <ul>
                    <li><a href="<?= url('/page/payment-methods') ?>">أنظمة السداد</a></li>
                    <li><a href="<?= url('/page/how-to-buy') ?>">كيفية الشراء</a></li>
                    <li><a href="<?= url('/page/returns') ?>">سياسة الإرجاع والاستبدال</a></li>
                    <li><a href="<?= url('/page/shipping') ?>">سياسة الشحن والتركيب</a></li>
                </ul>
            </div>
            <div>
                <h4>حسابي</h4>
                <ul>
                    <li><a href="<?= url($me ? '/account?tab=profile' : '/account/login') ?>"><?= $me ? 'إعدادات الحساب' : 'تسجيل الدخول' ?></a></li>
                    <li><a href="<?= url('/cart') ?>">عربة التسوق</a></li>
                    <li><a href="<?= url('/account') ?>">طلباتي</a></li>
                    <li><a href="<?= url('/wishlist') ?>">قائمة المفضلة</a></li>
                    <li><a href="<?= url('/track') ?>">تتبع طلبك</a></li>
                </ul>
            </div>
            <div>
                <h4>خدمة العملاء</h4>
                <ul>
                    <li><a href="<?= url('/services') ?>">خدماتنا</a></li>
                    <li><a href="<?= url('/service-request') ?>">طلب خدمة</a></li>
                    <li><a href="<?= url('/installments') ?>">التقسيط</a></li>
                    <li><a href="<?= url('/faq') ?>">الأسئلة الشائعة</a></li>
                    <li><a href="<?= url('/contact') ?>">اتصل بنا</a></li>
                </ul>
            </div>
            <div>
                <h4>تواصل معنا</h4>
                <ul class="contact-list">
                    <?php if ($phone): ?><li><span class="ci"><?= icon('phone', 16) ?></span><a href="<?= e(tel_link($phone)) ?>" dir="ltr"><?= e($phone) ?></a></li><?php endif; ?>
                    <?php if ($em = setting('email')): ?><li><span class="ci"><?= icon('mail', 16) ?></span><a href="mailto:<?= e($em) ?>"><?= e($em) ?></a></li><?php endif; ?>
                    <?php if ($ad = setting('address')): ?><li><span class="ci"><?= icon('pin', 16) ?></span><span><?= e($ad) ?></span></li><?php endif; ?>
                </ul>
                <div class="socials">
                    <?php foreach ($socials as $net => $link): ?>
                        <a href="<?= e($link) ?>" target="_blank" rel="noopener" aria-label="<?= e($net) ?>"><?= icon($net, 20) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <span>© <?= date('Y') ?> <?= e($siteName) ?>. جميع الحقوق محفوظة.</span>
            <span class="fb-links"><a href="<?= url('/page/privacy') ?>">سياسة الخصوصية</a><i>|</i><a href="<?= url('/page/terms') ?>">الشروط والأحكام</a><i>|</i><a href="<?= url('/page/returns') ?>">الإرجاع والاستبدال</a></span>
            <span class="pay-badges"><span>كاش</span><span>فودافون كاش</span><span>إنستاباي</span></span>
        </div>
    </div>
</footer>

<a href="<?= e(wa_link('السلام عليكم، عايز أستفسر عن الفلاتر')) ?>" class="wa-float" target="_blank" rel="noopener" aria-label="واتساب"><?= icon('whatsapp', 30) ?></a>
<button class="to-top" id="toTop" aria-label="لأعلى"><?= icon('arrow-up', 20) ?></button>

<nav class="bottom-nav" aria-label="تنقل سريع">
    <a href="<?= url('/') ?>" class="<?= $isActive('/') ?>"><?= icon('home', 20) ?><span>الرئيسية</span></a>
    <button type="button" data-open-drawer><?= icon('grid', 20) ?><span>التصنيفات</span></button>
    <a href="<?= url($me ? '/account' : '/account/login') ?>" class="<?= $isActive('/account') ?>"><?= icon('user', 20) ?><span>حسابي</span></a>
    <a href="<?= e(wa_link()) ?>" target="_blank" rel="noopener"><?= icon('whatsapp', 20) ?><span>واتساب</span></a>
</nav>

<!-- اختيار موقع التوصيل -->
<div class="modal" id="location-modal" aria-hidden="true" role="dialog" aria-labelledby="loc-title">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-card">
        <button class="icon-btn modal-x" data-close-modal aria-label="إغلاق"><?= icon('x', 20) ?></button>
        <span class="modal-ico"><?= icon('pin', 28) ?></span>
        <h3 id="loc-title">اختر موقع التوصيل</h3>
        <p class="muted">علشان نوريك مدة التوصيل المتوقعة ونجهز بيانات الشحن.</p>
        <form action="<?= url('/location') ?>" method="post" data-location-form>
            <?= csrf_field() ?>
            <select name="governorate" required aria-label="المحافظة">
                <option value="">اختار المحافظة</option>
                <?php foreach (governorates() as $g): ?><option <?= $loc === $g ? 'selected' : '' ?>><?= e($g) ?></option><?php endforeach; ?>
            </select>
            <button class="btn btn-primary btn-block">حفظ</button>
        </form>
    </div>
</div>

<?php if (setting('welcome_enabled', '1') === '1'): ?>
<!-- رسالة الترحيب (أول زيارة بس) -->
<div class="modal" id="welcome-modal" aria-hidden="true" role="dialog" aria-labelledby="wel-title" data-welcome>
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-card welcome-card">
        <img src="<?= asset('assets/img/logo.png') ?>" alt="<?= e($siteName) ?>" width="110" height="144">
        <h3 id="wel-title"><?= e(setting('welcome_title', 'مرحباً بكم في ' . $siteName)) ?></h3>
        <div class="muted"><?= nl2p(setting('welcome_text')) ?></div>
        <button class="btn btn-primary btn-lg" data-close-modal>استكشاف المتجر</button>
    </div>
</div>
<?php endif; ?>

<div class="toast-wrap" id="toasts" aria-live="polite"></div>

<script>window.APP = { csrf: <?= json_encode(csrf_token()) ?>, cartUrl: <?= json_encode(url('/cart')) ?>, wishUrl: <?= json_encode(url('/wishlist/toggle')) ?> };</script>
<script src="<?= asset('assets/js/site.js') ?>" defer></script>
</body>
</html>
