<div class="container page-wrap">
    <?= view('partials/crumbs', ['items' => ['الماركات' => null]]) ?>
    <h1 class="page-title">تسوق حسب الماركة</h1>
    <div class="brands-grid">
        <?php foreach ($brands as $b): ?>
        <a href="<?= url('/brand/' . $b['slug']) ?>" class="card brand-card">
            <?php if ($b['logo']): ?><img src="<?= e(img($b['logo'])) ?>" alt="<?= e($b['name']) ?>"><?php endif; ?>
            <b><?= e($b['name']) ?></b><small><?= (int)$b['products_count'] ?> منتج</small>
        </a>
        <?php endforeach; ?>
    </div>
</div>
