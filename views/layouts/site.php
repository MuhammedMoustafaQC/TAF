<?php
$siteName = setting('site_name', 'قطرات الندى');
$tagline = setting('site_tagline', 'لأنظمة معالجة المياه');
$pageTitle = isset($title) ? $title . ' | ' . $siteName : $siteName . ' ' . $tagline;
$metaDesc = $description ?? setting('meta_description');
$current = '/' . trim(substr(rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH)), strlen(base_path())), '/');
$isActive = fn(string $p) => ($p === '/' ? $current === '/' : str_starts_with($current, $p)) ? 'active' : '';
$cats = array_values(array_filter(nav_categories(), fn($c) => $c['slug'] !== 'offers'));
$phone = setting('phone');
$me = customer();
$wishCount = count(wishlist_ids());
$cartCount = cart_count();
$loc = delivery_location();
$socials = array_filter(['facebook' => setting('facebook'), 'instagram' => setting('instagram'), 'telegram' => setting('telegram'), 'tiktok' => setting('tiktok'), 'youtube' => setting('youtube')]);
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($metaDesc) ?>">
    <meta name="theme-color" content="#0C0B2B">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($metaDesc) ?>">
    <meta property="og:image" content="<?= e(img($og_image ?? 'assets/img/logo.png')) ?>">
    <link rel="icon" type="image/png" href="<?= asset('assets/img/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/site.css') ?>">
</head>
<body>
<a href="#main" class="sr-only">تخطي إلى المحتوى</a>

<div class="utility">
    <div class="container utility-inner">
        <div class="utility-perks">
            <span><?= icon('tool', 14) ?> تركيب مجاني بفنيين معتمدين</span>
            <span><?= icon('shield', 14) ?> ضمان حقيقي حتى 5 سنوات</span>
            <span><?= icon('cash', 14) ?> الدفع عند الاستلام أو بالتقسيط</span>
        </div>
        <div class="utility-links">
            <button type="button" data-open-modal="location-modal"><?= icon('pin', 14) ?> التوصيل إلى: <b data-location-label><?= $loc ? e($loc) : 'اختر محافظتك' ?></b></button>
            <a href="<?= url('/track') ?>"><?= icon('truck', 14) ?> تتبع طلبك</a>
            <?php if ($phone): ?><a href="<?= e(tel_link($phone)) ?>" dir="ltr"><?= icon('phone', 14) ?> <?= e($phone) ?></a><?php endif; ?>
        </div>
    </div>
</div>

<header class="header" id="header">
    <div class="container header-inner">
        <button class="icon-btn menu-toggle" data-open-drawer aria-label="القائمة"><?= icon('menu', 24) ?></button>

        <a href="<?= url('/') ?>" class="brand" aria-label="<?= e($siteName) ?> — الرئيسية">
            <img src="<?= asset('assets/img/logo-mark.png') ?>" alt="" width="34" height="52">
            <span class="brand-text"><strong><?= e($siteName) ?></strong><small><?= e($tagline) ?></small></span>
        </a>

        <nav class="nav" aria-label="القائمة الرئيسية">
            <ul class="nav-list">
                <li class="mega-wrap" data-menu>
                    <button type="button" aria-expanded="false" class="<?= $isActive('/shop') ?: $isActive('/category') ?>">المنتجات <?= icon('chevron-d', 14) ?></button>
                    <div class="mega">
                        <div class="container mega-inner">
                            <?php foreach (array_slice($cats, 0, 4) as $c): ?>
                            <div class="mega-col">
                                <a href="<?= url('/category/' . $c['slug']) ?>" class="mega-head">
                                    <img src="<?= e(img($c['image'])) ?>" alt="" loading="lazy">
                                    <span><?= e($c['name']) ?><small><?= (int)$c['products_count'] ?> منتج</small></span>
                                </a>
                                <?php foreach ($c['children'] as $ch): ?><a href="<?= url('/category/' . $ch['slug']) ?>" class="mega-link"><?= e($ch['name']) ?></a><?php endforeach; ?>
                            </div>
                            <?php endforeach; ?>
                            <div class="mega-promo">
                                <b>محتار تختار فلتر؟</b>
                                <p>جاوب على 4 أسئلة سريعة وهنرشحلك الأنسب لمياه بيتك.</p>
                                <a href="<?= url('/#finder') ?>" class="btn btn-aqua btn-sm">ابدأ الاختيار</a>
                            </div>
                            <?php if (count($cats) > 4): ?>
                            <div class="mega-col" style="grid-column: 1 / -2; flex-direction: row; flex-wrap: wrap; gap: 8px; border-top: 1px solid var(--line); padding-top: 18px;">
                                <?php foreach (array_slice($cats, 4) as $c): ?><a href="<?= url('/category/' . $c['slug']) ?>" class="chip"><?= e($c['name']) ?></a><?php endforeach; ?>
                                <a href="<?= url('/shop') ?>" class="link-arrow" style="margin-inline-start:auto">كل المنتجات <?= icon('arrow-l', 16) ?></a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
                <li><a href="<?= url('/offers') ?>" class="nav-sale <?= $isActive('/offers') ?>">العروض</a></li>
                <li class="has-drop" data-menu>
                    <button type="button" aria-expanded="false" class="<?= $isActive('/service') ?: $isActive('/installments') ?>">الخدمات <?= icon('chevron-d', 14) ?></button>
                    <div class="drop">
                        <a href="<?= url('/service-request') ?>"><?= icon('tool', 18) ?><span>طلب صيانة أو تركيب<small>فني يوصلك في معادك</small></span></a>
                        <a href="<?= url('/#finder') ?>"><?= icon('drop', 18) ?><span>اختار الفلتر المناسب<small>4 أسئلة وترشيح فوري</small></span></a>
                        <a href="<?= url('/installments') ?>"><?= icon('cash', 18) ?><span>التقسيط<small>من غير بنك ومن غير تعقيد</small></span></a>
                        <a href="<?= url('/services') ?>"><?= icon('award', 18) ?><span>كل خدماتنا</span></a>
                    </div>
                </li>
                <li><a href="<?= url('/category/ro-plants') ?>" class="<?= $isActive('/category/ro-plants') ?>">للشركات والمصانع</a></li>
                <li><a href="<?= url('/blog') ?>" class="<?= $isActive('/blog') ?>">المدونة</a></li>
                <li><a href="<?= url('/contact') ?>" class="<?= $isActive('/contact') ?>">تواصل معنا</a></li>
            </ul>
        </nav>

        <div class="header-actions">
            <button type="button" class="hbtn" data-open-search aria-label="بحث"><?= icon('search', 22) ?></button>
            <a href="<?= url($me ? '/account' : '/account/login') ?>" class="hbtn hide-md" aria-label="<?= $me ? 'حسابي' : 'تسجيل الدخول' ?>" title="<?= $me ? 'حسابي' : 'تسجيل الدخول' ?>"><?= icon('user', 22) ?></a>
            <a href="<?= url('/wishlist') ?>" class="hbtn hide-md" aria-label="المفضلة" title="المفضلة"><?= icon('heart', 22) ?><span class="hcount" data-wish-count <?= $wishCount ? '' : 'hidden' ?>><?= $wishCount ?></span></a>
            <a href="<?= url('/cart') ?>" class="cart-pill hide-md" aria-label="السلة"><?= icon('bag', 18) ?> <span class="lbl">السلة</span> <span class="hcount" data-cart-count><?= $cartCount ?></span></a>
            <a href="<?= url('/cart') ?>" class="hbtn menu-toggle" aria-label="السلة"><?= icon('bag', 22) ?><span class="hcount" data-cart-count <?= $cartCount ? '' : 'hidden' ?>><?= $cartCount ?></span></a>
        </div>
    </div>
</header>

<!-- البحث -->
<div class="search-panel" id="search-panel" aria-hidden="true" role="dialog" aria-label="البحث">
    <div class="search-backdrop" data-close-search></div>
    <div class="search-sheet">
        <div class="container">
            <form class="search-form" action="<?= url('/search') ?>" method="get" role="search" data-search-form>
                <?= icon('search', 26) ?>
                <input type="search" name="q" id="search-q" placeholder="دوّر على فلتر، شمعة، ممبرين..." autocomplete="off" aria-label="بحث">
                <button type="button" class="icon-btn" data-close-search aria-label="إغلاق"><?= icon('x', 22) ?></button>
            </form>
            <div class="search-body">
                <div>
                    <span class="search-label">الأكثر بحثاً</span>
                    <div class="chips">
                        <?php foreach (['فلتر 7 مراحل', 'شمعات', 'ممبرين', 'موتور', 'جامبو', 'سوفتنر', 'مبرد مياه'] as $t): ?><a class="chip" href="<?= url('/search?q=' . urlencode($t)) ?>"><?= e($t) ?></a><?php endforeach; ?>
                    </div>
                    <span class="search-label" style="margin-top:22px">الأقسام</span>
                    <div class="chips">
                        <?php foreach ($cats as $c): ?><a class="chip" href="<?= url('/category/' . $c['slug']) ?>"><?= e($c['name']) ?></a><?php endforeach; ?>
                    </div>
                </div>
                <div>
                    <span class="search-label">نتائج سريعة</span>
                    <div class="suggest-list" data-suggest data-endpoint="<?= url('/search/suggest') ?>"><p class="suggest-empty">اكتب حرفين على الأقل علشان تظهر النتائج.</p></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- قائمة الموبايل -->
<div class="drawer" id="drawer" aria-hidden="true">
    <div class="drawer-backdrop" data-close-drawer></div>
    <aside class="drawer-panel" aria-label="القائمة">
        <div class="drawer-head">
            <a href="<?= url('/') ?>" class="brand">
                <img src="<?= asset('assets/img/logo-mark.png') ?>" alt="" width="28" height="43">
                <span class="brand-text"><strong><?= e($siteName) ?></strong><small><?= e($tagline) ?></small></span>
            </a>
            <button class="icon-btn" data-close-drawer aria-label="إغلاق"><?= icon('x', 22) ?></button>
        </div>
        <button class="drawer-loc" data-open-modal="location-modal" type="button"><?= icon('pin', 18) ?> التوصيل إلى: <b data-location-label><?= $loc ? e($loc) : 'اختر محافظتك' ?></b></button>
        <nav class="drawer-nav">
            <a href="<?= url('/') ?>"><?= icon('home', 18) ?> الرئيسية</a>
            <details>
                <summary><?= icon('grid', 18) ?> المنتجات <?= icon('chevron-d', 16) ?></summary>
                <div class="drawer-cats">
                    <a href="<?= url('/shop') ?>">كل المنتجات</a>
                    <?php foreach ($cats as $c): ?>
                        <a href="<?= url('/category/' . $c['slug']) ?>"><?= e($c['name']) ?></a>
                        <?php foreach ($c['children'] as $ch): ?><a href="<?= url('/category/' . $ch['slug']) ?>" class="sub"><?= e($ch['name']) ?></a><?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </details>
            <a href="<?= url('/offers') ?>"><?= icon('tag', 18) ?> العروض</a>
            <a href="<?= url('/#finder') ?>" data-close-drawer-link><?= icon('drop', 18) ?> اختار الفلتر المناسب</a>
            <a href="<?= url('/service-request') ?>"><?= icon('tool', 18) ?> طلب صيانة أو تركيب</a>
            <a href="<?= url('/installments') ?>"><?= icon('cash', 18) ?> التقسيط</a>
            <a href="<?= url('/category/ro-plants') ?>"><?= icon('box', 18) ?> للشركات والمصانع</a>
            <a href="<?= url('/blog') ?>"><?= icon('book', 18) ?> المدونة</a>
            <a href="<?= url('/track') ?>"><?= icon('truck', 18) ?> تتبع طلبك</a>
            <a href="<?= url('/wishlist') ?>"><?= icon('heart', 18) ?> المفضلة</a>
            <a href="<?= url('/contact') ?>"><?= icon('phone', 18) ?> تواصل معنا</a>
        </nav>
    </aside>
</div>

<main id="main">
    <?php if ($m = flash('success')): ?>
        <div class="container"><div class="alert alert-success" role="status" data-autohide><?= icon('check', 18) ?> <?= e($m) ?></div></div>
    <?php endif; ?>
    <?php if ($m = flash('error')): ?>
        <div class="container"><div class="alert alert-error" role="alert"><?= icon('x', 18) ?> <?= e($m) ?></div></div>
    <?php endif; ?>

    <?= $content ?>
</main>

<footer class="footer">
    <div class="container">
        <section class="nl-band">
            <div>
                <h3>نصايح المياه والعروض الحصرية، في إيميلك.</h3>
                <p>رسالة واحدة في الشهر: مواعيد تغيير الشمعات، عروض قبل أي حد، ونصايح من فنيينا.</p>
            </div>
            <form action="<?= url('/newsletter') ?>" method="post" class="nl-form">
                <?= csrf_field() ?>
                <div class="nl-input">
                    <input type="email" name="email" id="nl-email" placeholder="بريدك الإلكتروني" required aria-label="البريد الإلكتروني">
                    <button class="btn btn-aqua btn-sm">اشترك</button>
                </div>
                <label class="nl-agree"><input type="checkbox" name="agree" value="1" id="nl-agree" required> أوافق على <a href="<?= url('/page/privacy') ?>">سياسة الخصوصية</a> والشروط.</label>
            </form>
        </section>

        <div class="footer-grid">
            <div class="f-brand">
                <img src="<?= asset('assets/img/logo-white.png') ?>" alt="<?= e($siteName) ?>" width="64" height="84" loading="lazy">
                <p><?= e(strtok(setting('about_text'), "\n")) ?></p>
                <div class="f-contact">
                    <?php if ($phone): ?><a href="<?= e(tel_link($phone)) ?>" dir="ltr"><?= icon('phone', 16) ?> <?= e($phone) ?></a><?php endif; ?>
                    <?php if ($em = setting('email')): ?><a href="mailto:<?= e($em) ?>"><?= icon('mail', 16) ?> <?= e($em) ?></a><?php endif; ?>
                    <?php if ($ad = setting('address')): ?><span><?= icon('pin', 16) ?> <?= e($ad) ?></span><?php endif; ?>
                </div>
                <div class="socials">
                    <?php foreach ($socials as $net => $link): ?><a href="<?= e($link) ?>" target="_blank" rel="noopener" aria-label="<?= e($net) ?>"><?= icon($net, 18) ?></a><?php endforeach; ?>
                    <a href="<?= e(wa_link()) ?>" target="_blank" rel="noopener" aria-label="واتساب"><?= icon('whatsapp', 18) ?></a>
                </div>
            </div>
            <div>
                <h4>المتجر</h4>
                <ul>
                    <?php foreach ($cats as $c): ?><li><a href="<?= url('/category/' . $c['slug']) ?>"><?= e($c['name']) ?></a></li><?php endforeach; ?>
                    <li><a href="<?= url('/offers') ?>">العروض</a></li>
                </ul>
            </div>
            <div>
                <h4>الخدمات</h4>
                <ul>
                    <li><a href="<?= url('/service-request') ?>">طلب صيانة أو تركيب</a></li>
                    <li><a href="<?= url('/installments') ?>">التقسيط</a></li>
                    <li><a href="<?= url('/#finder') ?>">اختار الفلتر المناسب</a></li>
                    <li><a href="<?= url('/services') ?>">كل خدماتنا</a></li>
                    <li><a href="<?= url('/brands') ?>">الماركات</a></li>
                </ul>
            </div>
            <div>
                <h4>الشراء</h4>
                <ul>
                    <li><a href="<?= url('/page/how-to-buy') ?>">كيفية الشراء</a></li>
                    <li><a href="<?= url('/page/payment-methods') ?>">أنظمة السداد</a></li>
                    <li><a href="<?= url('/page/shipping') ?>">الشحن والتركيب</a></li>
                    <li><a href="<?= url('/page/returns') ?>">الإرجاع والاستبدال</a></li>
                    <li><a href="<?= url('/faq') ?>">الأسئلة الشائعة</a></li>
                </ul>
            </div>
            <div>
                <h4>حسابي</h4>
                <ul>
                    <li><a href="<?= url($me ? '/account' : '/account/login') ?>"><?= $me ? 'حسابي وطلباتي' : 'تسجيل الدخول' ?></a></li>
                    <li><a href="<?= url('/cart') ?>">سلة التسوق</a></li>
                    <li><a href="<?= url('/wishlist') ?>">المفضلة</a></li>
                    <li><a href="<?= url('/track') ?>">تتبع طلبك</a></li>
                    <li><a href="<?= url('/about') ?>">من نحن</a></li>
                    <li><a href="<?= url('/blog') ?>">المدونة</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <span>© <?= date('Y') ?> <?= e(setting('company_name', $siteName)) ?><?php if ($tax = setting('tax_number')): ?> · س.ض <?= e($tax) ?><?php endif; ?></span>
            <span class="fb-links"><a href="<?= url('/page/privacy') ?>">سياسة الخصوصية</a><a href="<?= url('/page/terms') ?>">الشروط والأحكام</a></span>
            <span class="pay-badges" aria-label="طرق الدفع"><span>كاش</span><span>فودافون كاش</span><span>إنستاباي</span><span>تقسيط</span></span>
        </div>
    </div>
</footer>

<a href="<?= e(wa_link('السلام عليكم، عايز أستفسر عن الفلاتر')) ?>" class="wa-float" target="_blank" rel="noopener" aria-label="تواصل على واتساب"><?= icon('whatsapp', 28) ?></a>
<button class="to-top" id="toTop" aria-label="لأعلى"><?= icon('arrow-up', 20) ?></button>

<nav class="bottom-nav" aria-label="تنقل سريع">
    <a href="<?= url('/') ?>" class="<?= $isActive('/') ?>"><?= icon('home', 22) ?><span>الرئيسية</span></a>
    <a href="<?= url('/shop') ?>" class="<?= $isActive('/shop') ?: $isActive('/category') ?>"><?= icon('grid', 22) ?><span>المنتجات</span></a>
    <a href="<?= url('/wishlist') ?>" class="<?= $isActive('/wishlist') ?>"><?= icon('heart', 22) ?><span>المفضلة</span><span class="hcount" data-wish-count <?= $wishCount ? '' : 'hidden' ?>><?= $wishCount ?></span></a>
    <a href="<?= url($me ? '/account' : '/account/login') ?>" class="<?= $isActive('/account') ?>"><?= icon('user', 22) ?><span>حسابي</span></a>
    <a href="<?= e(wa_link()) ?>" class="wa" target="_blank" rel="noopener"><?= icon('whatsapp', 22) ?><span>واتساب</span></a>
</nav>

<div class="modal" id="location-modal" aria-hidden="true" role="dialog" aria-labelledby="loc-title">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-card">
        <button class="icon-btn modal-x" data-close-modal aria-label="إغلاق"><?= icon('x', 20) ?></button>
        <span class="modal-ico"><?= icon('pin', 26) ?></span>
        <h3 id="loc-title">التوصيل لأي محافظة؟</h3>
        <p class="muted">هنوريك مدة التوصيل المتوقعة في صفحات المنتجات ونجهز بيانات الشحن.</p>
        <form action="<?= url('/location') ?>" method="post" data-location-form>
            <?= csrf_field() ?>
            <select name="governorate" id="loc-gov" required aria-label="المحافظة">
                <option value="">اختار المحافظة</option>
                <?php foreach (governorates() as $g): ?><option <?= $loc === $g ? 'selected' : '' ?>><?= e($g) ?></option><?php endforeach; ?>
            </select>
            <button class="btn btn-primary btn-block">حفظ</button>
        </form>
    </div>
</div>

<?php if (setting('welcome_enabled', '0') === '1'): ?>
<div class="modal" id="welcome-modal" aria-hidden="true" role="dialog" aria-labelledby="wel-title" data-welcome>
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-card welcome-card">
        <img src="<?= asset('assets/img/logo.png') ?>" alt="<?= e($siteName) ?>" width="90" height="118">
        <h3 id="wel-title"><?= e(setting('welcome_title', 'مرحباً بكم في ' . $siteName)) ?></h3>
        <div class="muted"><?= nl2p(setting('welcome_text')) ?></div>
        <button class="btn btn-primary btn-lg" data-close-modal>ابدأ التسوق</button>
    </div>
</div>
<?php endif; ?>

<div class="toast-wrap" id="toasts" aria-live="polite"></div>

<script>window.APP = { csrf: <?= json_encode(csrf_token()) ?>, cartUrl: <?= json_encode(url('/cart')) ?>, wishUrl: <?= json_encode(url('/wishlist/toggle')) ?> };</script>
<script src="<?= asset('assets/js/site.js') ?>" defer></script>
</body>
</html>
