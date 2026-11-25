<div class="container page-wrap">
    <?= view('partials/crumbs', ['items' => ['قائمة المفضلة' => null]]) ?>
    <h1 class="page-title">قائمة المفضلة <small>(<?= count($items) ?>)</small></h1>
    <?php if ($items): ?>
        <div class="products-grid"><?php foreach ($items as $p) echo view('partials/product-card', ['p' => $p]); ?></div>
        <?php if (!customer()): ?><p class="muted center" style="margin-top:18px"><a href="<?= url('/account/login') ?>">سجّل دخولك</a> علشان تحفظ المفضلة على حسابك.</p><?php endif; ?>
    <?php else: ?>
        <div class="card empty"><span class="empty-ico"><?= icon('heart', 34) ?></span><h3>قائمة المفضلة فاضية</h3><p>اضغط على القلب في أي منتج علشان تحفظه هنا.</p><a href="<?= url('/shop') ?>" class="btn btn-primary">تصفح المنتجات</a></div>
    <?php endif; ?>
</div>
