<?php /** @var int $step */ $labels = [1 => 'سلة التسوق', 2 => 'تفاصيل الشحن', 3 => 'الدفع والتأكيد']; ?>
<ol class="steps">
    <?php foreach ($labels as $n => $l): ?>
        <li class="<?= $n < $step ? 'done' : ($n === $step ? 'current' : '') ?>"><span><?= $n < $step ? icon('check', 16) : $n ?></span><?= e($l) ?></li>
    <?php endforeach; ?>
</ol>
