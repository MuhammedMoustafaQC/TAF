<?php $at = setting('about_title', setting('site_name')); $parts = explode(' - ', $at, 2); ?>
<div class="container page-wrap">
    <?= view('partials/crumbs', ['items' => ['من نحن' => null]]) ?>
</div>
<section class="about-home" style="padding-top:20px">
    <div class="container about-grid">
        <div class="about-text">
            <h1><?= e($parts[0]) ?><?php if (isset($parts[1])): ?> - <span style="color:var(--cyan-600)"><?= e($parts[1]) ?></span><?php endif; ?></h1>
            <div class="prose"><?= nl2p(setting('about_text')) ?></div>
            <div class="about-stats">
                <div><b data-count="<?= (int)setting('stat_years', 10) ?>">+<?= (int)setting('stat_years', 10) ?></b><span>سنوات خبرة</span></div>
                <div><b data-count="<?= (int)setting('stat_customers', 0) ?>">+<?= (int)setting('stat_customers', 0) ?></b><span>عميل راضٍ</span></div>
                <div><b>24/7</b><span>دعم فني</span></div>
            </div>
            <div class="btn-row"><a href="<?= url('/shop') ?>" class="btn btn-primary">تصفح المنتجات</a><a href="<?= url('/contact') ?>" class="btn btn-outline">تواصل معنا</a></div>
        </div>
        <div class="about-media">
            <div class="about-badge"><span><?= icon('shield', 24) ?></span><div><b>ضمان حقيقي 100%</b><small>على كافة المنتجات</small></div></div>
            <div class="about-img" style="background:#fff;border:1px solid var(--line)"><img src="<?= asset('assets/img/logo.png') ?>" alt="<?= e(setting('site_name')) ?>" style="width:60%;filter:none"></div>
        </div>
    </div>
</section>
