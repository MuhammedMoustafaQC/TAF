<?php
/** @var array $p */
$off = discount_percent($p);
$inStock = (int)$p['stock'] > 0;
$link = url('/product/' . $p['slug']);
$hl = product_highlight($p);
$wished = in_wishlist((int)$p['id']);
?>
<article class="pcard<?= $inStock ? '' : ' is-out' ?>">
    <div class="pcard-media">
        <a href="<?= $link ?>" class="pcard-img" tabindex="-1">
            <img src="<?= e(img($p['image'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy" width="300" height="300">
            <img src="<?= asset('assets/img/logo-mark.png') ?>" alt="" class="pcard-wm" width="18" height="28" loading="lazy">
        </a>
        <button type="button" class="pcard-wish<?= $wished ? ' on' : '' ?>" data-wish="<?= (int)$p['id'] ?>" aria-label="أضف للمفضلة" aria-pressed="<?= $wished ? 'true' : 'false' ?>"><?= icon('heart', 20) ?></button>
        <?php if (!$inStock): ?><span class="pcard-out">نفد</span><?php elseif (!empty($p['badge'])): ?><span class="pcard-badge"><?= e($p['badge']) ?></span><?php endif; ?>
        <?php if ($inStock): ?>
        <form action="<?= url('/cart/add') ?>" method="post" data-ajax-cart class="pcard-addf">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
            <button type="submit" class="pcard-add" aria-label="أضف للسلة" title="أضف للسلة"><?= icon('plus', 22) ?></button>
        </form>
        <?php endif; ?>
    </div>
    <div class="pcard-body">
        <div class="pcard-rate"><?= icon('star', 14, 'star-on') ?> <b><?= $p['rating_count'] ? e(number_format((float)$p['rating_avg'], 1)) : '0' ?></b> <small>(<?= (int)$p['rating_count'] ?>)</small></div>
        <h3 class="pcard-title"><a href="<?= $link ?>"><?= e($p['name']) ?></a></h3>
        <?php if ($inStock): ?>
            <div class="pcard-price">
                <b><?= number_format($p['price']) ?></b> <small>ج.م</small>
                <?php if ($off): ?><del><?= number_format($p['old_price']) ?></del> <span class="pcard-off"><?= $off ?>% خصم</span><?php endif; ?>
            </div>
            <?php if ($hl): ?><div class="pcard-hl"><?= icon($hl[0], 15) ?> <?= e($hl[1]) ?></div><?php endif; ?>
        <?php else: ?>
            <div class="pcard-na">غير متوفر حالياً</div>
        <?php endif; ?>
        <?php if (!empty($p['is_express'])): ?>
            <div class="pcard-ship"><span class="exp"><?= icon('bolt', 12) ?> اكسبريس</span><span class="day"><?= e(setting('express_label', 'بكرة')) ?></span></div>
        <?php endif; ?>
    </div>
</article>
