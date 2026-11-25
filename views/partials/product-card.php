<?php
/** @var array $p */
$off = discount_percent($p);
$inStock = (int)$p['stock'] > 0;
$link = url('/product/' . $p['slug']);
$wished = in_wishlist((int)$p['id']);
$months = (int)($p['installment_months'] ?? 0);
?>
<article class="pcard<?= $inStock ? '' : ' is-out' ?>">
    <div class="pcard-media">
        <a href="<?= $link ?>" class="pcard-img" tabindex="-1" aria-hidden="true"><img src="<?= e(img($p['image'])) ?>" alt="" loading="lazy" width="300" height="300"></a>
        <div class="pcard-tags">
            <?php if (!$inStock): ?><span class="tag tag-out">نفدت الكمية</span><?php endif; ?>
            <?php if ($off && $inStock): ?><span class="tag tag-sale">وفّر <?= $off ?>%</span><?php endif; ?>
            <?php if (!empty($p['badge']) && $inStock): ?><span class="tag"><?= e($p['badge']) ?></span><?php endif; ?>
        </div>
        <button type="button" class="pcard-wish<?= $wished ? ' on' : '' ?>" data-wish="<?= (int)$p['id'] ?>" aria-label="أضف للمفضلة" aria-pressed="<?= $wished ? 'true' : 'false' ?>"><?= icon('heart', 18) ?></button>
        <?php if ($inStock): ?>
        <form action="<?= url('/cart/add') ?>" method="post" data-ajax-cart class="pcard-quick">
            <?= csrf_field() ?><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
            <button type="submit" class="btn"><?= icon('bag', 18) ?> أضف للسلة</button>
        </form>
        <form action="<?= url('/cart/add') ?>" method="post" data-ajax-cart class="pcard-add-m">
            <?= csrf_field() ?><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
            <button type="submit" aria-label="أضف <?= e($p['name']) ?> للسلة"><?= icon('plus', 20) ?></button>
        </form>
        <?php endif; ?>
    </div>
    <div class="pcard-body">
        <div class="pcard-meta">
            <?php if (!empty($p['category_name'])): ?><a href="<?= url('/category/' . $p['category_slug']) ?>"><?= e($p['category_name']) ?></a><?php else: ?><span></span><?php endif; ?>
            <?php if ((int)$p['rating_count'] > 0): ?><span class="pcard-rate"><?= icon('star', 13, 'star-on') ?> <?= e(number_format((float)$p['rating_avg'], 1)) ?> <small>(<?= (int)$p['rating_count'] ?>)</small></span><?php endif; ?>
        </div>
        <h3 class="pcard-title"><a href="<?= $link ?>"><?= e($p['name']) ?></a></h3>
        <div class="pcard-price">
            <b><?= number_format($p['price']) ?><small>ج.م</small></b>
            <?php if ($off): ?><del><?= number_format($p['old_price']) ?></del><?php endif; ?>
        </div>
        <div class="pcard-foot">
            <?php if (!$inStock): ?>
                <span class="pcard-na">غير متوفر حالياً</span>
            <?php else: ?>
                <?php if ($months > 0): ?><span><?= icon('cash', 14) ?> أو <?= number_format(ceil($p['price'] / $months)) ?> ج.م/شهر</span><?php elseif (!empty($p['warranty'])): ?><span><?= icon('shield', 14) ?> <?= e($p['warranty']) ?></span><?php endif; ?>
                <?php if (!empty($p['is_express'])): ?><span class="exp"><?= icon('bolt', 14, 'filled') ?> توصيل <?= e(setting('express_label', 'بكرة')) ?></span><?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</article>
