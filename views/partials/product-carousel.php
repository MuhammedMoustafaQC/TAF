<?php /** @var array $products */ if (!$products) return; ?>
<div class="carousel" data-carousel>
    <button class="car-btn car-prev" data-car-prev aria-label="السابق"><?= icon('chevron-r', 22) ?></button>
    <div class="car-track" data-car-track>
        <?php foreach ($products as $p): ?><div class="car-item"><?= view('partials/product-card', ['p' => $p]) ?></div><?php endforeach; ?>
    </div>
    <button class="car-btn car-next" data-car-next aria-label="التالي"><?= icon('chevron-l', 22) ?></button>
</div>
