<?php
$cats = nav_categories();
$selected = $filters['cat'];
if (!empty($category)) $selected[] = $category['slug'];
$qs = function (array $over) use ($filters) {
    $q = array_merge(['q' => $filters['q'], 'sort' => $filters['sort'], 'min' => $filters['min'], 'max' => $filters['max'], 'cat' => $filters['cat'], 'stock' => $filters['stock'] ? '1' : ''], $over);
    $q = array_filter($q, fn($v) => $v !== '' && $v !== null && $v !== []);
    if (($q['sort'] ?? '') === 'popular') unset($q['sort']);
    if (($q['page'] ?? 1) == 1) unset($q['page']);
    return $q ? '?' . http_build_query($q) : '';
};
$total = (int)$result['total'];
?>
<div class="container page-wrap">
    <div class="crumbs crumbs-pill">
        <a href="<?= url('/') ?>"><?= icon('home', 15) ?> الرئيسية</a> <?= icon('chevron-l', 14) ?>
        <?php if (!empty($parent)): ?><a href="<?= url('/category/' . $parent['slug']) ?>"><?= e($parent['name']) ?></a> <?= icon('chevron-l', 14) ?><?php endif; ?>
        <?php if (!empty($category) || !empty($brand) || !empty($is_offers) || $filters['q'] !== ''): ?><a href="<?= url('/shop') ?>">كل المنتجات</a> <?= icon('chevron-l', 14) ?><?php endif; ?>
        <b><?= e($heading) ?></b>
    </div>

    <div class="shop-layout">
        <aside class="shop-side" id="shop-filters">
            <form method="get" action="<?= url($base) ?>" data-filter-form>
                <?php if ($filters['q'] !== ''): ?><input type="hidden" name="q" value="<?= e($filters['q']) ?>"><?php endif; ?>
                <?php if ($filters['sort'] !== 'popular'): ?><input type="hidden" name="sort" value="<?= e($filters['sort']) ?>"><?php endif; ?>
                <div class="fgroup">
                    <label class="fcheck all"><input type="checkbox" data-filter-all <?= !$selected ? 'checked' : '' ?>> كل المنتجات</label>
                </div>
                <?php foreach ($cats as $c): if ($c['slug'] === 'offers') continue;
                    $open = in_array($c['slug'], $selected, true) || array_intersect(array_column($c['children'], 'slug'), $selected); ?>
                <details class="fgroup" <?= $open || count($cats) <= 6 ? 'open' : '' ?>>
                    <summary><?= e($c['name']) ?> <?= icon('chevron-d', 16) ?></summary>
                    <div class="fopts">
                        <label class="fcheck"><input type="checkbox" name="cat[]" value="<?= e($c['slug']) ?>" <?= in_array($c['slug'], $selected, true) ? 'checked' : '' ?> data-autosubmit> كل منتجات <?= e($c['name']) ?></label>
                        <?php foreach ($c['children'] as $ch): ?>
                            <label class="fcheck child"><input type="checkbox" name="cat[]" value="<?= e($ch['slug']) ?>" <?= in_array($ch['slug'], $selected, true) ? 'checked' : '' ?> data-autosubmit> <?= e($ch['name']) ?></label>
                        <?php endforeach; ?>
                    </div>
                </details>
                <?php endforeach; ?>
                <details class="fgroup" open>
                    <summary>السعر (ج.م) <?= icon('chevron-d', 16) ?></summary>
                    <div class="fopts">
                        <div class="fprice">
                            <input type="number" name="min" min="0" placeholder="من" value="<?= e($filters['min']) ?>" inputmode="numeric" aria-label="أقل سعر">
                            <input type="number" name="max" min="0" placeholder="إلى" value="<?= e($filters['max']) ?>" inputmode="numeric" aria-label="أعلى سعر">
                        </div>
                        <label class="fcheck"><input type="checkbox" name="stock" value="1" <?= $filters['stock'] ? 'checked' : '' ?> data-autosubmit> المتوفر فقط</label>
                        <button class="btn btn-primary btn-sm btn-block">تطبيق</button>
                    </div>
                </details>
            </form>
        </aside>

        <div class="shop-main">
            <div class="shop-top">
                <h1><?= e($heading) ?> <small>(<?= $total ?> منتج)</small></h1>
                <div class="sort-form">
                    <button type="button" class="btn btn-light btn-sm filters-toggle" data-toggle-filters><?= icon('filter', 16) ?> فلترة</button>
                    <label for="sort">ترتيب</label>
                    <select id="sort" data-sort-select>
                        <?php foreach (['popular' => 'الأكثر مبيعاً', 'newest' => 'الأحدث', 'price_asc' => 'السعر: من الأقل', 'price_desc' => 'السعر: من الأعلى', 'rating' => 'الأعلى تقييماً'] as $k => $v): ?>
                            <option value="<?= e(url($base) . $qs(['sort' => $k, 'page' => 1])) ?>" <?= $filters['sort'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php if (!empty($description)): ?><p class="muted"><?= e($description) ?></p><?php endif; ?>

            <?php if ($result['items']): ?>
                <div class="products-grid products-grid-3">
                    <?php foreach ($result['items'] as $p) echo view('partials/product-card', ['p' => $p]); ?>
                </div>
                <?php if ($result['pages'] > 1): ?>
                <nav class="pagination" aria-label="الصفحات">
                    <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
                        <a href="<?= e(url($base) . $qs(['page' => $i])) ?>" class="<?= $i === $result['page'] ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="card empty">
                    <span class="empty-ico"><?= icon('search', 34) ?></span>
                    <h3>مفيش منتجات مطابقة</h3>
                    <p>جرّب كلمة بحث تانية أو غيّر الفلاتر.</p>
                    <a href="<?= url('/shop') ?>" class="btn btn-primary">كل المنتجات</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
