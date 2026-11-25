<?php /** @var string $title  @var ?string $eyebrow @var ?string $sub @var ?string $more @var ?string $moreLabel @var ?string $aside */ ?>
<div class="sec-head">
    <div>
        <?php if (!empty($eyebrow)): ?><span class="eyebrow"><?= e($eyebrow) ?></span><?php endif; ?>
        <h2><?= e($title) ?></h2>
        <?php if (!empty($sub)): ?><p><?= e($sub) ?></p><?php endif; ?>
    </div>
    <?php if (!empty($aside)): ?><?= $aside ?><?php elseif (!empty($more)): ?><a href="<?= e(url($more)) ?>" class="link-arrow"><?= e($moreLabel ?? 'عرض الكل') ?> <?= icon('arrow-l', 16) ?></a><?php endif; ?>
</div>
