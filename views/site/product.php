<?php
$off = discount_percent($p);
$inStock = (int)$p['stock'] > 0;
?>
<div class="container">
    <nav class="breadcrumb breadcrumb-plain">
        <a href="<?= url('/') ?>">الرئيسية</a> <span>/</span>
        <a href="<?= url('/shop') ?>">المنتجات</a> <span>/</span>
        <?php if ($p['category_name']): ?><a href="<?= url('/category/' . $p['category_slug']) ?>"><?= e($p['category_name']) ?></a> <span>/</span><?php endif; ?>
        <b><?= e($p['name']) ?></b>
    </nav>
</div>

<section class="section section-tight">
    <div class="container product-layout">
        <div class="gallery" data-gallery>
            <div class="gallery-main">
                <img src="<?= e(img($gallery[0] ?? null)) ?>" alt="<?= e($p['name']) ?>" data-gallery-main width="600" height="600">
                <?php if ($off): ?><span class="tag tag-sale tag-lg">خصم <?= $off ?>%</span><?php endif; ?>
            </div>
            <?php if (count($gallery) > 1): ?>
            <div class="gallery-thumbs">
                <?php foreach ($gallery as $i => $g): ?>
                    <button type="button" class="<?= $i === 0 ? 'active' : '' ?>" data-src="<?= e(img($g)) ?>"><img src="<?= e(img($g)) ?>" alt="" loading="lazy"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <?php if ($p['badge']): ?><span class="tag tag-brand"><?= e($p['badge']) ?></span><?php endif; ?>
            <h1><?= e($p['name']) ?></h1>
            <?php if ($p['short_description']): ?><p class="lead"><?= e($p['short_description']) ?></p><?php endif; ?>

            <div class="price price-lg">
                <span class="price-now"><?= money($p['price']) ?></span>
                <?php if ($off): ?>
                    <del class="price-old"><?= money($p['old_price']) ?></del>
                    <span class="save">وفّر <?= money($p['old_price'] - $p['price']) ?></span>
                <?php endif; ?>
            </div>

            <ul class="pi-meta">
                <li><?= icon('box', 18) ?> الحالة: <?= $inStock ? '<b class="ok">متوفر</b>' . ((int)$p['stock'] <= 5 ? ' <small class="warn">(باقي ' . (int)$p['stock'] . ' قطع بس)</small>' : '') : '<b class="no">غير متوفر حالياً</b>' ?></li>
                <?php if ($p['warranty']): ?><li><?= icon('shield', 18) ?> الضمان: <b><?= e($p['warranty']) ?></b></li><?php endif; ?>
                <?php if ($p['category_name']): ?><li><?= icon('tag', 18) ?> القسم: <a href="<?= url('/category/' . $p['category_slug']) ?>"><?= e($p['category_name']) ?></a></li><?php endif; ?>
            </ul>

            <?php if ($inStock): ?>
            <form action="<?= url('/cart/add') ?>" method="post" class="buy-box" data-ajax-cart>
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                <div class="qty" data-qty>
                    <button type="button" data-step="1" aria-label="زيادة"><?= icon('plus', 18) ?></button>
                    <input type="number" name="qty" value="1" min="1" max="<?= (int)$p['stock'] ?>" aria-label="الكمية" inputmode="numeric">
                    <button type="button" data-step="-1" aria-label="تقليل"><?= icon('minus', 18) ?></button>
                </div>
                <button type="submit" class="btn btn-primary btn-lg"><?= icon('cart', 20) ?> أضف للسلة</button>
                <button type="submit" name="buy_now" value="1" class="btn btn-accent btn-lg" data-no-ajax>اشتري الآن</button>
            </form>
            <?php endif; ?>

            <a href="<?= e(wa_link('عايز أطلب: ' . $p['name'] . ' — ' . money($p['price']) . "\n" . url('/product/' . $p['slug']))) ?>" class="btn btn-wa btn-block btn-lg" target="_blank" rel="noopener"><?= icon('whatsapp', 20) ?> اطلب على واتساب</a>

            <div class="trust">
                <div><?= icon('tool', 22) ?><span>تركيب مجاني</span></div>
                <div><?= icon('truck', 22) ?><span>توصيل لكل المحافظات</span></div>
                <div><?= icon('cash', 22) ?><span>الدفع عند الاستلام</span></div>
                <div><?= icon('shield', 22) ?><span>منتج أصلي بضمان</span></div>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <div class="tabs" data-tabs>
            <div class="tab-list" role="tablist">
                <button role="tab" class="active" data-tab="desc">الوصف</button>
                <?php if ($specs): ?><button role="tab" data-tab="specs">المواصفات</button><?php endif; ?>
                <button role="tab" data-tab="ship">التوصيل والتركيب</button>
            </div>
            <div class="tab-panel active" data-panel="desc">
                <div class="prose"><?= nl2p($p['description'] ?: $p['short_description']) ?></div>
            </div>
            <?php if ($specs): ?>
            <div class="tab-panel" data-panel="specs">
                <table class="specs">
                    <?php foreach ($specs as [$k, $v]): ?><tr><th><?= e($k) ?></th><td><?= e($v) ?></td></tr><?php endforeach; ?>
                </table>
            </div>
            <?php endif; ?>
            <div class="tab-panel" data-panel="ship">
                <div class="prose">
                    <p>بنوصل لكل محافظات مصر خلال 1 إلى 4 أيام عمل. مصاريف الشحن <?= money(setting('shipping_fee', 0)) ?><?php if ((float)setting('free_shipping_min', 0) > 0): ?>، والشحن مجاني للطلبات فوق <?= money(setting('free_shipping_min')) ?><?php endif; ?>.</p>
                    <p>التركيب مجاني للفلاتر داخل نطاق الخدمة، والفني بيشرحلك طريقة الاستخدام والصيانة.</p>
                    <p>تقدر تدفع كاش عند الاستلام أو تحويل فودافون كاش / إنستاباي.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($related): ?>
<section class="section section-tint">
    <div class="container">
        <div class="section-head"><div><span class="eyebrow">ممكن يعجبك كمان</span><h2>منتجات مشابهة</h2></div></div>
        <div class="products-grid">
            <?php foreach ($related as $r) echo view('partials/product-card', ['p' => $r]); ?>
        </div>
    </div>
</section>
<?php endif; ?>
