<div class="page-hero">
    <div class="container">
        <nav class="breadcrumb"><a href="<?= url('/') ?>">الرئيسية</a> <span>/</span> <b>من نحن</b></nav>
        <h1>من نحن</h1>
        <p><?= e(setting('site_name')) ?> <?= e(setting('site_tagline')) ?></p>
    </div>
</div>

<section class="section">
    <div class="container about-grid">
        <div class="prose prose-lg">
            <span class="eyebrow">قصتنا</span>
            <h2>مياه نقية… حياة صحية</h2>
            <?= nl2p(setting('about_text')) ?>
            <div class="btn-row">
                <a href="<?= url('/shop') ?>" class="btn btn-primary">تصفح المنتجات</a>
                <a href="<?= url('/contact') ?>" class="btn btn-outline">تواصل معنا</a>
            </div>
        </div>
        <div class="about-logo">
            <img src="<?= asset('assets/img/logo.png') ?>" alt="<?= e(setting('site_name')) ?>" width="300" height="392">
        </div>
    </div>
</section>

<section class="section section-tint">
    <div class="container">
        <div class="values">
            <div class="card value"><span class="f-ico"><?= icon('award', 26) ?></span><h3>رؤيتنا</h3><p>إن كل بيت في مصر يشرب مياه نقية وصحية بسعر في متناول الجميع.</p></div>
            <div class="card value"><span class="f-ico"><?= icon('drop', 26) ?></span><h3>رسالتنا</h3><p>نقدم أفضل حلول معالجة المياه بجودة عالية وخدمة ما بعد بيع يعتمد عليها.</p></div>
            <div class="card value"><span class="f-ico"><?= icon('shield', 26) ?></span><h3>قيمنا</h3><p>الأمانة في النصيحة، الأصالة في المنتجات، والالتزام بالمواعيد.</p></div>
        </div>
        <div class="stats stats-row">
            <div class="stat"><b data-count="<?= (int)setting('stat_years', 10) ?>">+<?= (int)setting('stat_years', 10) ?></b><span>سنة خبرة</span></div>
            <div class="stat"><b data-count="<?= (int)setting('stat_customers', 0) ?>">+<?= number_format((int)setting('stat_customers', 0)) ?></b><span>عميل سعيد</span></div>
            <div class="stat"><b data-count="<?= (int)setting('stat_governorates', 27) ?>"><?= (int)setting('stat_governorates', 27) ?></b><span>محافظة بنوصلها</span></div>
            <div class="stat"><b data-count="<?= (int)setting('stat_technicians', 0) ?>">+<?= (int)setting('stat_technicians', 0) ?></b><span>فني متخصص</span></div>
        </div>
    </div>
</section>

<?php if ($testimonials): ?>
<section class="section">
    <div class="container">
        <div class="section-head center"><div><span class="eyebrow">آراء عملائنا</span><h2>ثقة عملائنا أهم حاجة عندنا</h2></div></div>
        <div class="testimonials">
            <?php foreach ($testimonials as $t): ?>
            <figure class="testimonial card">
                <span class="t-quote"><?= icon('quote', 26) ?></span>
                <div class="stars"><?php for ($i = 1; $i <= 5; $i++): ?><span class="<?= $i <= $t['rating'] ? 'on' : '' ?>"><?= icon('star', 16) ?></span><?php endfor; ?></div>
                <blockquote><?= e($t['content']) ?></blockquote>
                <figcaption><span class="avatar"><?= e(mb_substr($t['name'], 0, 1)) ?></span><span><b><?= e($t['name']) ?></b><?php if ($t['city']): ?><small><?= e($t['city']) ?></small><?php endif; ?></span></figcaption>
            </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
