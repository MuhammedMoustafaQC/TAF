<?php
$years = (int)setting('stat_years', 10);
$customers = (int)setting('stat_customers', 0);
$bigCat = $categories[0] ?? null;
$restCats = array_values(array_filter(array_slice($categories, 1), fn($c) => $c['slug'] !== 'offers'));
$finderData = [];
foreach ($finder as $slug => $fp) {
    $finderData[$slug] = ['name' => $fp['name'], 'url' => url('/product/' . $slug), 'image' => img($fp['image']), 'price' => money($fp['price'])];
}
$stages = [
    ['ترسيب PP', 'بيحجز الرمل والصدأ لحد 5 ميكرون'],
    ['كربون حبيبي', 'بيمتص الكلور والروائح'],
    ['كربون بلوك', 'تنقية أدق للمواد العضوية'],
    ['ممبرين RO', 'بيشيل لحد 99% من الأملاح الذائبة'],
    ['كربون نهائي', 'بيحسّن الطعم بعد الخزان'],
    ['معادن', 'بيرجّع المعادن المفيدة'],
    ['ألكالاين', 'بيظبط الـ pH للمياه'],
];
?>

<?php if ($slides): ?>
<section class="hero" data-slider aria-roledescription="carousel" aria-label="عروض رئيسية">
    <div class="hero-ripples" aria-hidden="true"><span></span><span></span><span></span></div>
    <div class="hero-track">
        <?php foreach ($slides as $i => $s): $hasText = trim((string)$s['title']) !== ''; ?>
        <div class="hero-slide<?= $i === 0 ? ' is-active' : '' ?>" data-slide aria-roledescription="slide" aria-label="<?= $i + 1 ?> من <?= count($slides) ?>">
            <?php if ($hasText): ?>
            <div class="container hero-inner">
                <div class="hero-text">
                    <span class="hero-kicker"><i></i> <?= e(setting('site_name')) ?> · <?= e(setting('site_tagline')) ?></span>
                    <?= $i === 0 ? '<h1>' : '<h2 class="h1">' ?><?= e($s['title']) ?><?= $i === 0 ? '</h1>' : '</h2>' ?>
                    <?php if ($s['subtitle']): ?><p><?= e($s['subtitle']) ?></p><?php endif; ?>
                    <div class="hero-cta">
                        <?php if ($s['button_text']): ?><a href="<?= e(url($s['button_link'] ?: '/shop')) ?>" class="btn btn-aqua btn-lg"><?= e($s['button_text']) ?> <?= icon('arrow-l', 18) ?></a><?php endif; ?>
                        <a href="#finder" class="btn btn-ghost-light btn-lg">اختار الفلتر المناسب</a>
                    </div>
                </div>
                <div class="hero-visual"><?php if ($s['image']): ?><img src="<?= e(img($s['image'])) ?>" alt="" <?= $i ? 'loading="lazy"' : 'fetchpriority="high"' ?>><?php endif; ?></div>
            </div>
            <?php else: ?>
                <a href="<?= e(url($s['button_link'] ?: '/shop')) ?>"><img src="<?= e(img($s['image'])) ?>" alt="<?= e($s['subtitle'] ?? '') ?>" class="hero-full"></a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="container hero-bar">
        <div class="hero-stats">
            <div><b>+<?= $years ?></b><span>سنة في معالجة المياه</span></div>
            <div><b>+<?= number_format($customers) ?></b><span>بيت وشركة بيشربوا مياه نقية</span></div>
            <div><b>27</b><span>محافظة بنوصلها</span></div>
            <div><b>5</b><span>سنين ضمان على الفلاتر</span></div>
        </div>
        <?php if (count($slides) > 1): ?>
        <div class="hero-nav">
            <div class="hero-progress" data-dots>
                <?php foreach ($slides as $i => $s): ?><button class="<?= $i === 0 ? 'is-active' : '' ?>" data-dot="<?= $i ?>" aria-label="الشريحة <?= $i + 1 ?>"></button><?php endforeach; ?>
            </div>
            <div class="hero-arrows">
                <button data-prev aria-label="السابق"><?= icon('chevron-r', 18) ?></button>
                <button data-next aria-label="التالي"><?= icon('chevron-l', 18) ?></button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<section class="trust" aria-label="مميزات الشراء">
    <div class="container trust-grid">
        <div class="trust-item"><span class="ti-ico"><?= icon('tool', 22) ?></span><div><b>تركيب مجاني</b><small>بفنيين معتمدين في معادك</small></div></div>
        <div class="trust-item"><span class="ti-ico"><?= icon('shield', 22) ?></span><div><b>ضمان حقيقي</b><small>حتى 5 سنين وقطع غيار أصلية</small></div></div>
        <div class="trust-item"><span class="ti-ico"><?= icon('truck', 22) ?></span><div><b>توصيل لكل مصر</b><small>شحن مجاني فوق <?= money(setting('free_shipping_min', 0)) ?></small></div></div>
        <div class="trust-item"><span class="ti-ico"><?= icon('cash', 22) ?></span><div><b>ادفع براحتك</b><small>عند الاستلام أو قسّط على <?= (int)setting('installment_months', 4) ?> شهور</small></div></div>
    </div>
</section>

<?php if ($bigCat): ?>
<section class="section">
    <div class="container">
        <?= view('partials/section-head', ['eyebrow' => 'تسوّق حسب الاحتياج', 'title' => 'كل اللي بيتك محتاجه لمياه نقية', 'more' => '/shop', 'moreLabel' => 'كل المنتجات']) ?>
        <div class="bento">
            <a href="<?= url('/category/' . $bigCat['slug']) ?>" class="bento-card is-big">
                <div>
                    <h3><?= e($bigCat['name']) ?></h3>
                    <p><?= e($bigCat['description']) ?></p>
                    <?php if ($bigCat['children']): ?><div class="bento-subs"><?php foreach ($bigCat['children'] as $ch): ?><span><?= e($ch['name']) ?></span><?php endforeach; ?></div><?php endif; ?>
                </div>
                <span class="go" aria-hidden="true"><?= icon('arrow-l', 18) ?></span>
                <img src="<?= e(img($bigCat['image'])) ?>" alt="" loading="lazy">
            </a>
            <?php foreach (array_slice($restCats, 0, 4) as $k => $c): ?>
            <a href="<?= url('/category/' . $c['slug']) ?>" class="bento-card<?= $k === 3 ? ' is-dark' : '' ?>">
                <div><h3><?= e($c['name']) ?></h3><small><?= (int)$c['products_count'] ?> منتج</small></div>
                <span class="go" aria-hidden="true"><?= icon('arrow-l', 16) ?></span>
                <img src="<?= e(img($c['image'])) ?>" alt="" loading="lazy">
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section section-mist">
    <div class="container" data-tabs>
        <?php ob_start(); ?>
        <div class="tabs-bar" role="tablist" aria-label="مجموعات المنتجات">
            <button role="tab" class="active" data-tab="best" aria-selected="true">الأكثر مبيعاً</button>
            <button role="tab" data-tab="new" aria-selected="false">وصل حديثاً</button>
            <?php if ($offers): ?><button role="tab" data-tab="deals" aria-selected="false">عروض حالية</button><?php endif; ?>
        </div>
        <?php $tabsBar = ob_get_clean(); ?>
        <?= view('partials/section-head', ['eyebrow' => 'اختيارات عملائنا', 'title' => 'منتجات مختارة بعناية', 'aside' => $tabsBar]) ?>
        <div class="tab-pane active" data-panel="best" role="tabpanel"><?= view('partials/product-carousel', ['products' => $topSelling]) ?></div>
        <div class="tab-pane" data-panel="new" role="tabpanel"><?= view('partials/product-carousel', ['products' => $newest]) ?></div>
        <?php if ($offers): ?><div class="tab-pane" data-panel="deals" role="tabpanel"><?= view('partials/product-carousel', ['products' => $offers]) ?></div><?php endif; ?>
    </div>
</section>

<?php if ($flash = banners('flash')): ?>
<section class="section-sm"><div class="container"><?= view('partials/banner', ['b' => $flash[0], 'variant' => 'flash']) ?></div></section>
<?php endif; ?>

<section class="section" id="finder">
    <div class="container finder">
        <div>
            <span class="eyebrow">اختار الفلتر المناسب</span>
            <h2>مش لازم تكون خبير مياه. جاوب على 4 أسئلة وإحنا نرشحلك.</h2>
            <p class="muted">كل بيت مياهه مختلفة: نسبة الأملاح، الضغط، وعدد الأفراد. الترشيح ده مبني على نفس الأسئلة اللي بيسألها فنيينا في الزيارة.</p>
            <div class="tds-scale" aria-label="مقياس نسبة الأملاح الذائبة TDS">
                <span class="search-label">مقياس الأملاح الذائبة (TDS) — جزء في المليون</span>
                <div class="tds-bar" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
                <div class="tds-labels">
                    <span><b>0–150</b>ممتازة</span>
                    <span><b>150–300</b>جيدة</span>
                    <span><b>300–700</b>محتاجة فلتر</span>
                    <span><b>+700</b>لازم RO</span>
                </div>
            </div>
        </div>
        <div class="finder-card" data-finder data-products='<?= e(json_encode($finderData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>' data-service="<?= url('/service-request') ?>">
            <div class="finder-steps" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
            <div class="finder-q active" data-q="source">
                <h3>مصدر المياه في بيتك إيه؟</h3>
                <p>ده بيحدد نوع الشوائب اللي محتاجين نتعامل معاها.</p>
                <div class="finder-opts">
                    <button type="button" class="finder-opt" data-v="city"><?= icon('home', 20) ?><span>مياه الحنفية العادية<small>شبكة المياه الحكومية</small></span></button>
                    <button type="button" class="finder-opt" data-v="well"><?= icon('drop', 20) ?><span>بئر أو مياه جوفية<small>رواسب وعسر أعلى</small></span></button>
                    <button type="button" class="finder-opt" data-v="unknown"><?= icon('help', 20) ?><span>مش متأكد</span></button>
                </div>
            </div>
            <div class="finder-q" data-q="tds">
                <h3>تعرف نسبة الأملاح (TDS) في المياه؟</h3>
                <p>لو مش عارف، فنيينا بيقيسوها مجاناً في الزيارة.</p>
                <div class="finder-opts">
                    <button type="button" class="finder-opt" data-v="low"><span>أقل من 300<small>جزء في المليون</small></span></button>
                    <button type="button" class="finder-opt" data-v="mid"><span>من 300 لـ 700<small>جزء في المليون</small></span></button>
                    <button type="button" class="finder-opt" data-v="high"><span>أكتر من 700<small>جزء في المليون</small></span></button>
                    <button type="button" class="finder-opt" data-v="unknown"><span>مش عارف</span></button>
                </div>
                <button type="button" class="finder-back" data-back><?= icon('chevron-r', 16) ?> رجوع</button>
            </div>
            <div class="finder-q" data-q="pressure">
                <h3>ضغط المياه عندك عامل إزاي؟</h3>
                <p>الضغط الضعيف بيقلل إنتاج الفلتر لو مفيش موتور.</p>
                <div class="finder-opts">
                    <button type="button" class="finder-opt" data-v="good"><?= icon('check', 20) ?><span>كويس</span></button>
                    <button type="button" class="finder-opt" data-v="weak"><?= icon('alert', 20) ?><span>ضعيف أو دور عالي</span></button>
                </div>
                <button type="button" class="finder-back" data-back><?= icon('chevron-r', 16) ?> رجوع</button>
            </div>
            <div class="finder-q" data-q="family">
                <h3>كام فرد في البيت؟</h3>
                <p>علشان نختار سعة الإنتاج المناسبة يومياً.</p>
                <div class="finder-opts">
                    <button type="button" class="finder-opt" data-v="small"><?= icon('user', 20) ?><span>من 1 لـ 3</span></button>
                    <button type="button" class="finder-opt" data-v="medium"><?= icon('users', 20) ?><span>من 4 لـ 6</span></button>
                    <button type="button" class="finder-opt" data-v="large"><?= icon('users', 20) ?><span>أكتر من 6</span></button>
                </div>
                <button type="button" class="finder-back" data-back><?= icon('chevron-r', 16) ?> رجوع</button>
            </div>
            <div class="finder-result" data-result aria-live="polite">
                <span class="eyebrow">ترشيحنا ليك</span>
                <div class="fr-product">
                    <img src="" alt="" data-r-img>
                    <div><b data-r-name></b><span class="price" data-r-price></span></div>
                </div>
                <p class="fr-why" data-r-why></p>
                <div class="btn-row" style="margin-top:14px">
                    <a href="#" class="btn btn-primary" data-r-link>شوف التفاصيل <?= icon('arrow-l', 16) ?></a>
                    <a href="<?= url('/service-request') ?>" class="btn btn-outline">اطلب تحليل مياه مجاني</a>
                </div>
                <button type="button" class="finder-back" data-restart><?= icon('rotate', 15) ?> ابدأ من جديد</button>
            </div>
        </div>
    </div>
</section>

<?php if ($promo = banners('promo')): ?>
<section class="section-sm" style="padding-top:0"><div class="container promo-row"><?php foreach (array_slice($promo, 0, 3) as $b) echo view('partials/banner', ['b' => $b, 'variant' => 'promo']); ?></div></section>
<?php endif; ?>

<section class="section">
    <div class="container">
        <?= view('partials/section-head', ['eyebrow' => 'خدمة ما بعد البيع', 'title' => 'معاك من أول تحليل المياه لحد آخر شمعة', 'more' => '/services', 'moreLabel' => 'كل خدماتنا']) ?>
        <div class="services">
            <a href="<?= url('/service-request') ?>" class="service"><span class="s-ico"><?= icon('tool', 24) ?></span><h3>تركيب احترافي</h3><p>فني معتمد بيركّب ويختبر الفلتر ويقيس الأملاح قبل وبعد قدامك.</p><span class="link-arrow">احجز تركيب <?= icon('arrow-l', 15) ?></span></a>
            <a href="<?= url('/service-request') ?>" class="service"><span class="s-ico"><?= icon('rotate', 24) ?></span><h3>صيانة وتغيير شمعات</h3><p>بنفكرك بمعاد التغيير، والفني بيجيلك بقطع غيار أصلية.</p><span class="link-arrow">اطلب صيانة <?= icon('arrow-l', 15) ?></span></a>
            <a href="<?= url('/service-request') ?>" class="service"><span class="s-ico"><?= icon('drop', 24) ?></span><h3>تحليل مياه مجاني</h3><p>قياس TDS والعسر في بيتك وترشيح الحل المناسب من غير أي التزام.</p><span class="link-arrow">احجز التحليل <?= icon('arrow-l', 15) ?></span></a>
            <a href="<?= url('/installments') ?>" class="service"><span class="s-ico"><?= icon('cash', 24) ?></span><h3>تقسيط بسعر الكاش</h3><p><?= e(setting('installment_note', 'قسّط مشترياتك بسهولة')) ?> من غير بنك.</p><span class="link-arrow">قدّم طلب <?= icon('arrow-l', 15) ?></span></a>
        </div>
    </div>
</section>

<?php foreach (banners('wide') as $b): ?><section class="section-sm" style="padding-top:0"><div class="container"><?= view('partials/banner', ['b' => $b, 'variant' => 'wide']) ?></div></section><?php endforeach; ?>

<section class="section section-ink">
    <div class="container">
        <div class="sec-head on-dark">
            <div>
                <span class="eyebrow">إزاي بننقي مياهك</span>
                <h2>7 مراحل من الحنفية لكوبايتك</h2>
                <p style="color:#9DA2C8">كل مرحلة ليها دور محدد، وقلب الفلتر هو الممبرين في المرحلة الرابعة.</p>
            </div>
            <a href="<?= url('/category/filters-7-stages') ?>" class="btn btn-ghost-light">فلاتر 7 مراحل <?= icon('arrow-l', 16) ?></a>
        </div>
        <ol class="journey" style="list-style:none;padding:0;margin:0">
            <?php foreach ($stages as $i => [$name, $text]): ?>
            <li class="jstep<?= $i === 3 ? ' core' : '' ?>"><span class="n"><?= $i + 1 ?></span><b><?= e($name) ?></b><small><?= e($text) ?></small></li>
            <?php endforeach; ?>
        </ol>
        <div class="journey-readout" aria-label="مثال توضيحي لقراءة جهاز الأملاح">
            <div class="readout"><b>650<small>ppm</small></b><span>مثال لقراءة<br>مياه الحنفية</span></div>
            <span class="readout-arrow"><?= icon('arrow-l', 26) ?></span>
            <div class="readout after"><b>45<small>ppm</small></b><span>بعد فلتر<br>7 مراحل</span></div>
        </div>
    </div>
</section>

<?php if ($plants): ?>
<section class="section">
    <div class="container">
        <div class="b2b">
            <div class="b2b-text">
                <span class="eyebrow">للشركات والمصانع</span>
                <h2>محطات تنقية وتحلية مصممة على احتياجك</h2>
                <p class="muted">للمكاتب والمطاعم والمدارس والمستشفيات والمصانع: بندرس المياه، ونصمم المحطة، ونركّب ونشغّل وندرّب فريقك.</p>
                <ul>
                    <li><?= icon('check', 18) ?> دراسة وتحليل مياه في الموقع</li>
                    <li><?= icon('check', 18) ?> سعات من 400 لـ 1000 جالون يومياً وأكتر</li>
                    <li><?= icon('check', 18) ?> عقود صيانة دورية وقطع غيار متوفرة</li>
                </ul>
                <div class="btn-row">
                    <a href="<?= url('/contact') ?>" class="btn btn-primary">اطلب عرض سعر</a>
                    <a href="<?= url('/category/ro-plants') ?>" class="btn btn-outline">شوف المحطات</a>
                </div>
            </div>
            <div class="b2b-media">
                <img src="<?= e(img($plants[0]['image'])) ?>" alt="<?= e($plants[0]['name']) ?>" loading="lazy">
                <div class="b2b-chip"><small>تبدأ من</small><b><?= money($plants[0]['price']) ?></b></div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php foreach (banners('brand') as $b): ?><section class="section-sm" style="padding-top:0"><div class="container"><?= view('partials/banner', ['b' => $b, 'variant' => 'brand']) ?></div></section><?php endforeach; ?>

<?php if ($brands): ?>
<section class="section-sm">
    <div class="container">
        <?= view('partials/section-head', ['eyebrow' => 'وكلاء وموزعين معتمدين', 'title' => 'ماركات بنثق فيها', 'more' => '/brands', 'moreLabel' => 'كل الماركات']) ?>
        <div class="brands-strip">
            <?php foreach (array_slice($brands, 0, 6) as $br): ?>
            <a href="<?= url('/brand/' . $br['slug']) ?>" class="brand-logo" title="<?= e($br['name']) ?>">
                <?php if ($br['logo']): ?><img src="<?= e(img($br['logo'])) ?>" alt="<?= e($br['name']) ?>" loading="lazy"><?php else: ?><b><?= e($br['name']) ?></b><?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($duo = banners('duo')): ?>
<section class="section-sm"><div class="container duo-row"><?php foreach (array_slice($duo, 0, 2) as $b) echo view('partials/banner', ['b' => $b, 'variant' => 'duo']); ?></div></section>
<?php endif; ?>

<?php if ($posts): ?>
<section class="section">
    <div class="container">
        <?= view('partials/section-head', ['eyebrow' => 'دليل المياه', 'title' => 'اعرف أكتر عن مياه بيتك', 'more' => '/blog', 'moreLabel' => 'كل المقالات']) ?>
        <div class="posts-grid"><?php foreach (array_slice($posts, 0, 3) as $post) echo view('partials/post-card', ['post' => $post]); ?></div>
    </div>
</section>
<?php endif; ?>

<section class="section section-mist">
    <div class="container about-grid">
        <div class="about-text">
            <?php $parts = explode(' - ', setting('about_title', setting('site_name')), 2); ?>
            <span class="eyebrow">من نحن</span>
            <h2><?= e($parts[0]) ?><?php if (isset($parts[1])): ?> — <span><?= e($parts[1]) ?></span><?php endif; ?></h2>
            <p><?= e(strtok(setting('about_text'), "\n")) ?></p>
            <a href="<?= url('/about') ?>" class="link-arrow">اعرف قصتنا <?= icon('arrow-l', 16) ?></a>
            <div class="about-stats">
                <div><b data-count="<?= $years ?>">+<?= $years ?></b><span>سنة خبرة</span></div>
                <div><b data-count="<?= $customers ?>">+<?= number_format($customers) ?></b><span>عميل</span></div>
                <div><b>24/7</b><span>دعم فني</span></div>
            </div>
        </div>
        <div class="about-media">
            <div class="about-img"><img src="<?= asset('assets/img/logo-white.png') ?>" alt="<?= e(setting('site_name')) ?>" loading="lazy"></div>
            <div class="about-badge"><span><?= icon('shield', 22) ?></span><div><b>ضمان حقيقي مكتوب</b><small>على كل الفلاتر وقطع الغيار</small></div></div>
        </div>
    </div>
</section>
