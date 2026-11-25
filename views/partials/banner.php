<?php
/** @var array $b  @var string $variant */
$href = $b['link'] ? url($b['link']) : null;
$tag = $href ? 'a' : 'div';
$imageOnly = trim((string)$b['title']) === '' && $b['image'];
$bg = preg_match('/^#[0-9a-fA-F]{3,8}$/', (string)$b['bg_color']) ? $b['bg_color'] : '#2B04B5';
?>
<<?= $tag ?> <?= $href ? 'href="' . e($href) . '"' : '' ?> class="banner banner-<?= e($variant) ?><?= $imageOnly ? ' banner-img-only' : '' ?>" style="--bn: <?= e($bg) ?>">
    <?php if ($imageOnly): ?>
        <img src="<?= e(img($b['image'])) ?>" alt="" loading="lazy">
    <?php else: ?>
        <div class="bn-text">
            <?php if ($variant === 'flash'): ?><span class="bn-kicker"><?= icon('bolt', 16) ?> LIMITED TIME OFFER</span><?php endif; ?>
            <h3><?= e($b['title']) ?></h3>
            <?php if ($b['subtitle']): ?><p><?= e($b['subtitle']) ?></p><?php endif; ?>
            <?php if ($variant === 'flash'): ?>
                <div class="countdown" data-countdown aria-label="ينتهي العرض خلال">
                    <span><b data-h>00</b><small>ساعة</small></span><span><b data-m>00</b><small>دقيقة</small></span><span><b data-s>00</b><small>ثانية</small></span>
                </div>
            <?php endif; ?>
            <?php if ($b['button_text']): ?><span class="bn-btn"><?= e($b['button_text']) ?></span><?php endif; ?>
        </div>
        <?php if ($b['image']): ?><div class="bn-media"><img src="<?= e(img($b['image'])) ?>" alt="" loading="lazy"></div><?php endif; ?>
    <?php endif; ?>
</<?= $tag ?>>
