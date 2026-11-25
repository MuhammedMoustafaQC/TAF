<?php
/** @var array $p */
$off = discount_percent($p);
$inStock = (int)$p['stock'] > 0;
$link = url('/product/' . $p['slug']);
?>
<article class="card product-card<?= $inStock ? '' : ' is-out' ?>">
    <a href="<?= $link ?>" class="pc-media">
        <img src="<?= e(img($p['image'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy" width="300" height="300">
        <span class="pc-badges">
            <?php if ($off): ?><span class="tag tag-sale">خصم <?= $off ?>%</span><?php endif; ?>
            <?php if (!empty($p['badge'])): ?><span class="tag tag-brand"><?= e($p['badge']) ?></span><?php endif; ?>
        </span>
        <?php if (!$inStock): ?><span class="pc-out">نفدت الكمية</span><?php endif; ?>
    </a>
    <div class="pc-body">
        <?php if (!empty($p['category_name'])): ?>
            <a href="<?= url('/category/' . $p['category_slug']) ?>" class="pc-cat"><?= e($p['category_name']) ?></a>
        <?php endif; ?>
        <h3 class="pc-title"><a href="<?= $link ?>"><?= e($p['name']) ?></a></h3>
        <div class="price">
            <span class="price-now"><?= money($p['price']) ?></span>
            <?php if ($off): ?><del class="price-old"><?= money($p['old_price']) ?></del><?php endif; ?>
        </div>
        <div class="pc-actions">
            <?php if ($inStock): ?>
                <form action="<?= url('/cart/add') ?>" method="post" data-ajax-cart>
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                    <button class="btn btn-primary btn-sm btn-block" type="submit"><?= icon('cart', 18) ?> أضف للسلة</button>
                </form>
            <?php else: ?>
                <button class="btn btn-muted btn-sm btn-block" disabled>غير متوفر</button>
            <?php endif; ?>
            <a class="btn btn-wa btn-sm btn-icon" href="<?= e(wa_link('عايز أطلب: ' . $p['name'] . ' — ' . money($p['price']))) ?>" target="_blank" rel="noopener" aria-label="اطلب على واتساب" title="اطلب على واتساب"><?= icon('whatsapp', 18) ?></a>
        </div>
    </div>
</article>
