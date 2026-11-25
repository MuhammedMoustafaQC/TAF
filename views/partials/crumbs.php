<?php /** @var array $items  [label => url|null] */ ?>
<nav class="crumbs">
    <a href="<?= url('/') ?>">الرئيسية</a>
    <?php foreach ($items as $label => $link): ?>
        <?= icon('chevron-l', 14) ?> <?php if ($link): ?><a href="<?= e(url($link)) ?>"><?= e($label) ?></a><?php else: ?><b><?= e($label) ?></b><?php endif; ?>
    <?php endforeach; ?>
</nav>
