<?php
$cats = nav_categories();
$base = $category ? '/category/' . $category['slug'] : (!empty($is_offers) ? '/offers' : ($filters['q'] !== '' ? '/search' : '/shop'));
$qs = function (array $over) use ($filters) {
    $q = array_filter(array_merge(['q' => $filters['q'], 'sort' => $filters['sort'], 'min' => $filters['min'], 'max' => $filters['max']], $over), fn($v) => $v !== '' && $v !== null);
    if (($q['sort'] ?? '') === 'popular') unset($q['sort']);
    if (($q['page'] ?? 1) == 1) unset($q['page']);
    return $q ? '?' . http_build_query($q) : '';
};
?>
<div class="page-hero">
    <div class="container">
        <nav class="breadcrumb"><a href="<?= url('/') ?>">الرئيسية</a> <span>/</span>
            <?php if ($category || !empty($is_offers) || $filters['q'] !== ''): ?><a href="<?= url('/shop') ?>">المنتجات</a> <span>/</span><?php endif; ?>
            <b><?= e($heading) ?></b>
        </nav>
        <h1><?= e($heading) ?></h1>
        <?php if ($category && $category['description']): ?><p><?= e($category['description']) ?></p><?php endif; ?>
    </div>
</div>

<section class="section section-tight">
    <div class="container shop-layout">
        <aside class="shop-side">
            <details class="side-box" open>
                <summary>الأقسام <?= icon('chevron-d', 18) ?></summary>
                <ul class="side-cats">
                    <li><a href="<?= url('/shop') ?>" class="<?= !$category && empty($is_offers) ? 'active' : '' ?>">كل المنتجات</a></li>
                    <?php foreach ($cats as $c): ?>
                        <li><a href="<?= url('/category/' . $c['slug']) ?>" class="<?= $category && $category['id'] == $c['id'] ? 'active' : '' ?>"><?= e($c['name']) ?> <small><?= (int)$c['products_count'] ?></small></a></li>
                    <?php endforeach; ?>
                    <li><a href="<?= url('/offers') ?>" class="<?= !empty($is_offers) ? 'active' : '' ?>">العروض والخصومات</a></li>
                </ul>
            </details>
            <form class="side-box" method="get" action="<?= url($base) ?>">
                <?php if ($filters['q'] !== ''): ?><input type="hidden" name="q" value="<?= e($filters['q']) ?>"><?php endif; ?>
                <input type="hidden" name="sort" value="<?= e($filters['sort']) ?>">
                <div class="side-title">السعر (ج.م)</div>
                <div class="price-range">
                    <input type="number" name="min" min="0" placeholder="من" value="<?= e($filters['min']) ?>" inputmode="numeric">
                    <span>—</span>
                    <input type="number" name="max" min="0" placeholder="إلى" value="<?= e($filters['max']) ?>" inputmode="numeric">
                </div>
                <button class="btn btn-primary btn-sm btn-block"><?= icon('filter', 16) ?> تطبيق</button>
                <?php if ($filters['min'] !== '' || $filters['max'] !== ''): ?>
                    <a class="clear-link" href="<?= url($base) . e($qs(['min' => '', 'max' => ''])) ?>">مسح الفلتر</a>
                <?php endif; ?>
            </form>
            <a href="<?= url('/maintenance') ?>" class="side-promo">
                <?= icon('tool', 28) ?>
                <b>محتار تختار؟</b>
                <span>احجز تحليل مياه مجاني والفني هيرشحلك الأنسب.</span>
            </a>
        </aside>

        <div class="shop-main">
            <div class="shop-bar">
                <span><b><?= (int)$result['total'] ?></b> منتج</span>
                <form method="get" action="<?= url($base) ?>" class="sort-form">
                    <?php foreach (['q', 'min', 'max'] as $k): if ($filters[$k] !== ''): ?><input type="hidden" name="<?= $k ?>" value="<?= e($filters[$k]) ?>"><?php endif; endforeach; ?>
                    <label for="sort">ترتيب حسب</label>
                    <select name="sort" id="sort" data-autosubmit>
                        <?php foreach (['popular' => 'الأكثر مبيعاً', 'newest' => 'الأحدث', 'price_asc' => 'السعر: من الأقل', 'price_desc' => 'السعر: من الأعلى'] as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $filters['sort'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <?php if ($result['items']): ?>
                <div class="products-grid products-grid-3">
                    <?php foreach ($result['items'] as $p) echo view('partials/product-card', ['p' => $p]); ?>
                </div>
                <?php if ($result['pages'] > 1): ?>
                <nav class="pagination">
                    <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
                        <a href="<?= url($base) . e($qs(['page' => $i])) ?>" class="<?= $i === $result['page'] ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty">
                    <?= icon('search', 48) ?>
                    <h3>مفيش منتجات مطابقة</h3>
                    <p>جرّب كلمة بحث تانية أو شيل فلتر السعر.</p>
                    <a href="<?= url('/shop') ?>" class="btn btn-primary">كل المنتجات</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
