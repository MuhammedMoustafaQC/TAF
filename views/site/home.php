<?php
$stages = [
    ['شمعة الترسيب PP', 'بتشيل الرمل والصدأ والرواسب الكبيرة.'],
    ['كربون حبيبي GAC', 'بيمتص الكلور والروائح والطعم غير المستحب.'],
    ['كربون بلوك CTO', 'تنقية أدق للكلور والمواد العضوية.'],
    ['ممبرين RO', 'قلب الفلتر — بيشيل لحد 99% من الأملاح والمعادن الثقيلة.'],
    ['كربون نهائي T33', 'بيحسن طعم المياه بعد الخزان.'],
    ['مرحلة المعادن', 'بتضيف معادن مفيدة للجسم.'],
    ['ألكالاين / الأشعة', 'بتظبط الـ pH وبتقضي على البكتيريا.'],
];
?>

<?php if ($slides): ?>
<section class="hero" data-slider>
    <div class="hero-track">
        <?php foreach ($slides as $i => $s): ?>
        <div class="hero-slide<?= $i === 0 ? ' is-active' : '' ?>" data-slide>
            <div class="container hero-inner">
                <div class="hero-text">
                    <span class="hero-kicker"><?= icon('drop', 16) ?> <?= e(setting('site_name')) ?></span>
                    <h1><?= e($s['title']) ?></h1>
                    <?php if ($s['subtitle']): ?><p><?= e($s['subtitle']) ?></p><?php endif; ?>
                    <div class="hero-btns">
                        <?php if ($s['button_text']): ?><a href="<?= e(url($s['button_link'] ?: '/shop')) ?>" class="btn btn-white btn-lg"><?= e($s['button_text']) ?> <?= icon('chevron-l', 18) ?></a><?php endif; ?>
                        <a href="<?= e(wa_link('السلام عليكم، عايز أستفسر')) ?>" class="btn btn-ghost btn-lg" target="_blank" rel="noopener"><?= icon('whatsapp', 20) ?> كلمنا واتساب</a>
                    </div>
                </div>
                <div class="hero-media">
                    <?php if ($s['image']): ?><img src="<?= e(img($s['image'])) ?>" alt="" <?= $i ? 'loading="lazy"' : 'fetchpriority="high"' ?>><?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (count($slides) > 1): ?>
    <button class="hero-arrow prev" data-prev aria-label="السابق"><?= icon('chevron-r', 24) ?></button>
    <button class="hero-arrow next" data-next aria-label="التالي"><?= icon('chevron-l', 24) ?></button>
    <div class="hero-dots">
        <?php foreach ($slides as $i => $s): ?><button class="<?= $i === 0 ? 'is-active' : '' ?>" data-dot="<?= $i ?>" aria-label="شريحة <?= $i + 1 ?>"></button><?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="hero-wave" aria-hidden="true"><svg viewBox="0 0 1440 70" preserveAspectRatio="none"><path d="M0 35c160 30 320 40 480 20S800 0 960 8s320 40 480 30v32H0z"/></svg></div>
</section>
<?php endif; ?>

<section class="section">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">تسوق حسب القسم</span><h2>أقسام المنتجات</h2></div>
            <a href="<?= url('/shop') ?>" class="link-more">كل المنتجات <?= icon('chevron-l', 16) ?></a>
        </div>
        <div class="cat-grid">
            <?php foreach ($categories as $c): ?>
            <a href="<?= url('/category/' . $c['slug']) ?>" class="cat-card">
                <span class="cat-img"><img src="<?= e(img($c['image'])) ?>" alt="" loading="lazy"></span>
                <b><?= e($c['name']) ?></b>
                <small><?= (int)$c['products_count'] ?> منتج</small>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($featured): ?>
<section class="section section-tint">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">اختيارات عملائنا</span><h2>الأكثر مبيعاً</h2></div>
            <a href="<?= url('/shop?sort=popular') ?>" class="link-more">شوف الكل <?= icon('chevron-l', 16) ?></a>
        </div>
        <div class="products-grid">
            <?php foreach ($featured as $p) echo view('partials/product-card', ['p' => $p]); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="container promo-grid">
        <a href="<?= url('/maintenance') ?>" class="promo promo-indigo">
            <div>
                <span class="eyebrow eyebrow-light">خدمة مجانية</span>
                <h3>تحليل مياه مجاني لبيتك</h3>
                <p>اعرف نسبة الأملاح في مياهك واختار الفلتر المناسب مع فنيين متخصصين.</p>
                <span class="btn btn-white btn-sm">احجز دلوقتي <?= icon('chevron-l', 16) ?></span>
            </div>
            <img src="<?= asset('assets/img/products/tds.svg') ?>" alt="" loading="lazy">
        </a>
        <a href="<?= url('/category/cartridges') ?>" class="promo promo-cyan">
            <div>
                <span class="eyebrow eyebrow-light">قطع غيار أصلية</span>
                <h3>غيّر شمعاتك في معادها</h3>
                <p>طقم الشمعات الأصلية بسعر مميز، مع التركيب في البيت.</p>
                <span class="btn btn-white btn-sm">تسوق الشمعات <?= icon('chevron-l', 16) ?></span>
            </div>
            <img src="<?= asset('assets/img/products/cartridges-set.svg') ?>" alt="" loading="lazy">
        </a>
    </div>
</section>

<?php if ($offers): ?>
<section class="section section-tint">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">لفترة محدودة</span><h2>عروض وخصومات</h2></div>
            <a href="<?= url('/offers') ?>" class="link-more">كل العروض <?= icon('chevron-l', 16) ?></a>
        </div>
        <div class="products-grid">
            <?php foreach ($offers as $p) echo view('partials/product-card', ['p' => $p]); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section stages">
    <div class="container">
        <div class="section-head center">
            <div><span class="eyebrow">إزاي بننقي مياهك؟</span><h2>مراحل التنقية السبعة</h2>
            <p class="lead">كل نقطة مياه بتعدي على 7 مراحل علشان توصلك نقية وصحية وطعمها حلو.</p></div>
        </div>
        <ol class="stages-list">
            <?php foreach ($stages as $i => [$name, $text]): ?>
            <li class="stage">
                <span class="stage-num"><?= $i + 1 ?></span>
                <b><?= e($name) ?></b>
                <small><?= e($text) ?></small>
            </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>

<section class="section why">
    <div class="container why-grid">
        <div class="why-text">
            <span class="eyebrow">ليه قطرات الندى؟</span>
            <h2>خبرة في معالجة المياه تقدر تثق فيها</h2>
            <p class="lead"><?= e(strtok(setting('about_text'), "\n")) ?></p>
            <ul class="checks">
                <li><?= icon('check', 18) ?> منتجات أصلية بضمان مكتوب</li>
                <li><?= icon('check', 18) ?> تركيب مجاني بأيدي فنيين مدربين</li>
                <li><?= icon('check', 18) ?> متابعة دورية وتذكير بمعاد تغيير الشمعات</li>
                <li><?= icon('check', 18) ?> توصيل سريع لكل محافظات مصر</li>
            </ul>
            <a href="<?= url('/about') ?>" class="btn btn-primary">اعرف أكتر عننا</a>
        </div>
        <div class="stats">
            <div class="stat"><b data-count="<?= (int)setting('stat_years', 10) ?>">+<?= (int)setting('stat_years', 10) ?></b><span>سنة خبرة</span></div>
            <div class="stat"><b data-count="<?= (int)setting('stat_customers', 0) ?>">+<?= number_format((int)setting('stat_customers', 0)) ?></b><span>عميل سعيد</span></div>
            <div class="stat"><b data-count="<?= (int)setting('stat_governorates', 27) ?>"><?= (int)setting('stat_governorates', 27) ?></b><span>محافظة بنوصلها</span></div>
            <div class="stat"><b data-count="<?= (int)setting('stat_technicians', 0) ?>">+<?= (int)setting('stat_technicians', 0) ?></b><span>فني متخصص</span></div>
        </div>
    </div>
</section>

<?php if ($latest): ?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">وصل حديثاً</span><h2>أحدث المنتجات</h2></div>
            <a href="<?= url('/shop?sort=newest') ?>" class="link-more">شوف الكل <?= icon('chevron-l', 16) ?></a>
        </div>
        <div class="products-grid">
            <?php foreach ($latest as $p) echo view('partials/product-card', ['p' => $p]); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($testimonials): ?>
<section class="section section-tint">
    <div class="container">
        <div class="section-head center">
            <div><span class="eyebrow">آراء عملائنا</span><h2>بيقولوا إيه عننا؟</h2></div>
        </div>
        <div class="testimonials">
            <?php foreach ($testimonials as $t): ?>
            <figure class="testimonial card">
                <span class="t-quote"><?= icon('quote', 26) ?></span>
                <div class="stars" aria-label="<?= (int)$t['rating'] ?> من 5">
                    <?php for ($i = 1; $i <= 5; $i++): ?><span class="<?= $i <= $t['rating'] ? 'on' : '' ?>"><?= icon('star', 16) ?></span><?php endfor; ?>
                </div>
                <blockquote><?= e($t['content']) ?></blockquote>
                <figcaption><span class="avatar"><?= e(mb_substr($t['name'], 0, 1)) ?></span><span><b><?= e($t['name']) ?></b><?php if ($t['city']): ?><small><?= e($t['city']) ?></small><?php endif; ?></span></figcaption>
            </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($faqs): ?>
<section class="section">
    <div class="container faq-wrap">
        <div>
            <span class="eyebrow">عندك سؤال؟</span>
            <h2>الأسئلة الشائعة</h2>
            <p class="lead">جمعنا أكتر الأسئلة اللي بتوصلنا. لو سؤالك مش موجود كلمنا على طول.</p>
            <div class="faq-cta">
                <a href="<?= e(wa_link('عندي سؤال عن الفلاتر')) ?>" class="btn btn-wa" target="_blank" rel="noopener"><?= icon('whatsapp', 18) ?> اسأل على واتساب</a>
                <a href="<?= url('/faq') ?>" class="btn btn-outline">كل الأسئلة</a>
            </div>
        </div>
        <div class="faq-list">
            <?php foreach ($faqs as $i => $f): ?>
            <details class="faq" <?= $i === 0 ? 'open' : '' ?>>
                <summary><?= e($f['question']) ?> <?= icon('chevron-d', 18) ?></summary>
                <div class="faq-a"><?= nl2p($f['answer']) ?></div>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="cta-band">
    <div class="container cta-inner">
        <div>
            <h2>محتاج صيانة أو تغيير شمعات؟</h2>
            <p>احجز زيارة فني في المعاد اللي يناسبك، وهنكلمك نأكد معاك.</p>
        </div>
        <div class="cta-btns">
            <a href="<?= url('/maintenance') ?>" class="btn btn-white btn-lg"><?= icon('tool', 20) ?> احجز صيانة</a>
            <a href="<?= e(tel_link(setting('phone'))) ?>" class="btn btn-ghost btn-lg"><?= icon('phone', 20) ?> اتصل بينا</a>
        </div>
    </div>
</section>
