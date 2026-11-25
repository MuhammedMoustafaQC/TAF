<?php if ($slides): ?>
<section class="hero" data-slider>
    <div class="hero-track">
        <?php foreach ($slides as $i => $s): $hasText = trim((string)$s['title']) !== ''; ?>
        <div class="hero-slide<?= $i === 0 ? ' is-active' : '' ?><?= $hasText ? '' : ' hero-img-only' ?>" data-slide>
            <?php if ($hasText): ?>
            <div class="container hero-inner">
                <div class="hero-text">
                    <h1><?= e($s['title']) ?></h1>
                    <?php if ($s['subtitle']): ?><p><?= e($s['subtitle']) ?></p><?php endif; ?>
                    <?php if ($s['button_text']): ?><a href="<?= e(url($s['button_link'] ?: '/shop')) ?>" class="hero-cta"><?= e($s['button_text']) ?></a><?php endif; ?>
                </div>
                <div class="hero-media"><?php if ($s['image']): ?><img src="<?= e(img($s['image'])) ?>" alt="" <?= $i ? 'loading="lazy"' : 'fetchpriority="high"' ?>><?php endif; ?></div>
            </div>
            <?php else: ?>
                <a href="<?= e(url($s['button_link'] ?: '/shop')) ?>"><img src="<?= e(img($s['image'])) ?>" alt="" class="hero-full"></a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (count($slides) > 1): ?>
        <button class="hero-arrow prev" data-prev aria-label="السابق"><?= icon('chevron-r', 24) ?></button>
        <button class="hero-arrow next" data-next aria-label="التالي"><?= icon('chevron-l', 24) ?></button>
    <?php endif; ?>
</section>
<?php if (count($slides) > 1): ?>
<div class="hero-dots" data-dots>
    <?php foreach ($slides as $i => $s): ?><button class="<?= $i === 0 ? 'is-active' : '' ?>" data-dot="<?= $i ?>" aria-label="شريحة <?= $i + 1 ?>"></button><?php endforeach; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<section class="section section-cats">
    <div class="container">
        <h2 class="sec-title only-mobile">أفضل التصنيفات</h2>
        <div class="cat-strip">
            <div class="carousel" data-carousel data-autoplay>
                <button class="car-btn car-prev" data-car-prev aria-label="السابق"><?= icon('chevron-r', 22) ?></button>
                <div class="car-track" data-car-track>
                    <?php foreach (array_reverse($categories) as $c): ?>
                    <a href="<?= url($c['slug'] === 'offers' ? '/offers' : '/category/' . $c['slug']) ?>" class="cat-circle">
                        <span class="cc-img<?= $c['slug'] === 'offers' ? ' cc-sale' : '' ?>"><img src="<?= e(img($c['image'])) ?>" alt="" loading="lazy"></span>
                        <b><?= e($c['name']) ?></b>
                    </a>
                    <?php endforeach; ?>
                </div>
                <button class="car-btn car-next" data-car-next aria-label="التالي"><?= icon('chevron-l', 22) ?></button>
            </div>
        </div>
    </div>
</section>

<?php if ($featured): ?>
<section class="section">
    <div class="container">
        <?= view('partials/section-head', ['title' => 'منتجات مميزة', 'more' => '/shop?sort=popular']) ?>
        <?= view('partials/product-carousel', ['products' => $featured]) ?>
    </div>
</section>
<?php endif; ?>

<?php foreach (banners('flash') as $b): ?><section class="bn-full"><?= view('partials/banner', ['b' => $b, 'variant' => 'flash']) ?></section><?php endforeach; ?>

<?php if ($promo = banners('promo')): ?>
<section class="section section-tight">
    <div class="promo-row">
        <?php foreach (array_slice($promo, 0, 3) as $b) echo view('partials/banner', ['b' => $b, 'variant' => 'promo']); ?>
    </div>
</section>
<?php endif; ?>

<?php if ($topSelling): ?>
<section class="section">
    <div class="container">
        <?= view('partials/section-head', ['title' => 'المنتجات الأكثر مبيعاً', 'more' => '/shop?sort=popular']) ?>
        <?= view('partials/product-carousel', ['products' => $topSelling]) ?>
    </div>
</section>
<?php endif; ?>

<?php if ($newest): ?>
<section class="section">
    <div class="container">
        <?= view('partials/section-head', ['title' => 'المنتجات الجديدة', 'more' => '/shop?sort=newest']) ?>
        <?= view('partials/product-carousel', ['products' => $newest]) ?>
    </div>
</section>
<?php endif; ?>

<?php foreach (banners('wide') as $b): ?><section class="bn-full"><?= view('partials/banner', ['b' => $b, 'variant' => 'wide']) ?></section><?php endforeach; ?>

<?php if ($showcase && $showcaseSub): ?>
<section class="section">
    <div class="container">
        <?= view('partials/section-head', ['title' => $showcase['name'], 'more' => '/category/' . $showcase['slug']]) ?>
        <div class="carousel" data-carousel>
            <button class="car-btn car-prev" data-car-prev aria-label="السابق"><?= icon('chevron-r', 22) ?></button>
            <div class="car-track" data-car-track>
                <?php foreach ($showcaseSub as $sc): ?>
                <a href="<?= url('/category/' . $sc['slug']) ?>" class="sub-tile">
                    <span class="st-ico"><?= icon('squares', 30) ?></span>
                    <?php if ($sc['image']): ?><img src="<?= e(img($sc['image'])) ?>" alt="" loading="lazy"><?php endif; ?>
                    <b><?= e($sc['name']) ?></b>
                </a>
                <?php endforeach; ?>
            </div>
            <button class="car-btn car-next" data-car-next aria-label="التالي"><?= icon('chevron-l', 22) ?></button>
        </div>
    </div>
</section>
<?php endif; ?>

<?php foreach (banners('brand') as $b): ?><section class="bn-full"><?= view('partials/banner', ['b' => $b, 'variant' => 'brand']) ?></section><?php endforeach; ?>

<?php if ($brands): ?>
<section class="section">
    <div class="container">
        <?= view('partials/section-head', ['title' => 'تسوق حسب الماركة', 'more' => '/brands']) ?>
        <div class="carousel carousel-brands" data-carousel data-autoplay>
            <div class="car-track" data-car-track>
                <?php foreach ($brands as $br): ?>
                <a href="<?= url('/brand/' . $br['slug']) ?>" class="brand-logo" title="<?= e($br['name']) ?>">
                    <?php if ($br['logo']): ?><img src="<?= e(img($br['logo'])) ?>" alt="<?= e($br['name']) ?>" loading="lazy"><?php else: ?><span><?= e($br['name']) ?></span><?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($duo = banners('duo')): ?>
<section class="section section-tight">
    <div class="duo-row">
        <?php foreach (array_slice($duo, 0, 2) as $b) echo view('partials/banner', ['b' => $b, 'variant' => 'duo']); ?>
    </div>
</section>
<?php endif; ?>

<?php if ($posts): ?>
<section class="section">
    <div class="container">
        <?= view('partials/section-head', ['title' => 'أحدث المقالات والأخبار', 'more' => '/blog']) ?>
        <div class="posts-grid">
            <?php foreach ($posts as $post) echo view('partials/post-card', ['post' => $post]); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section about-home">
    <div class="container about-grid">
        <div class="about-text">
            <?php $at = setting('about_title', setting('site_name')); $parts = explode(' - ', $at, 2); ?>
            <h2><?= e($parts[0]) ?><?php if (isset($parts[1])): ?> - <span><?= e($parts[1]) ?></span><?php endif; ?></h2>
            <p><?= e(strtok(setting('about_text'), "\n")) ?> <a href="<?= url('/about') ?>" class="read-more">إقرأ المزيد...</a></p>
            <div class="about-stats">
                <div><b data-count="<?= (int)setting('stat_years', 10) ?>">+<?= (int)setting('stat_years', 10) ?></b><span>سنوات خبرة</span></div>
                <div><b data-count="<?= (int)setting('stat_customers', 0) ?>">+<?= (int)setting('stat_customers', 0) ?></b><span>عميل راضٍ</span></div>
                <div><b>24/7</b><span>دعم فني</span></div>
            </div>
        </div>
        <div class="about-media">
            <div class="about-badge"><span><?= icon('shield', 24) ?></span><div><b>ضمان حقيقي 100%</b><small>على كافة المنتجات</small></div></div>
            <div class="about-img"><img src="<?= asset('assets/img/products/ro-7.svg') ?>" alt="" loading="lazy"></div>
        </div>
    </div>
</section>
