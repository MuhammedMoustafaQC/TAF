<?php /** @var string $title  @var ?string $more */ ?>
<div class="sec-head">
    <h2><?= e($title) ?></h2>
    <?php if (!empty($more)): ?><a href="<?= e(url($more)) ?>" class="see-all">مشاهدة الكل <?= icon('arrow-l', 14) ?></a><?php endif; ?>
</div>
