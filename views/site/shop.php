<?php
$cats = array_values(array_filter(nav_categories(), fn($c) => $c['slug'] !== 'offers'));
$selected = $filters['cat'];
if (!empty($category)) $selected[] = $category['slug'];
$qs = function (array $over) use ($filters) {
    $q = array_merge(['q' => $filters['q'], 'sort' => $filters['sort'], 'min' => $filters['min'], 'max' => $filters['max'], 'cat' => $filters['cat'], 'stock' => $filters['stock'] ? '1' : ''], $over);
    $q = array_filter($q, fn($v) => $v !== '' && $v !== null && $v !== []);
    if (($q['sort'] ?? '') === 'popular') unset($q['sort']);
    if (($q['page'] ?? 1) == 1) unset($q['page']);
    return $q ? '?' . http_build_query($q) : '';
};
$catNames = [];
foreach ($cats as $c) { $catNames[$c['slug']] = $c['name']; foreach ($c['children'] as $ch) $catNames[$ch['slug']] = $ch['name']; }
$chips = [];
foreach ($filters['cat'] as $s) if (isset($catNames[$s])) $chips[] = [$catNames[$s], $qs(['cat' => array_values(array_diff($filters['cat'], [$s])), 'page' => 1])];
if ($filters['min'] !== '' || $filters['max'] !== '') $chips[] = ['السعر: ' . ($filters['min'] ?: '0') . ' – ' . ($filters['max'] ?: '∞'), $qs(['min' => '', 'max' => '', 'page' => 1])];
if ($filters['stock']) $chips[] = ['المتوفر فقط', $qs(['stock' => '', 'page' => 1])];
$total = (int)$result['total'];
?>
<div class="page-head">
    <div class="container">
        <nav class="crumbs" aria-label="مسار الصفحة">
            <a href="<?= url('/') ?>">الرئيسية</a> <?= icon('chevron-l', 13) ?>
            <?php if (!empty($parent)): ?><a href="<?= url('/category/' . $parent['slug']) ?>"><?= e($parent['name']) ?></a> <?= icon('chevron-l', 13) ?><?php endif; ?>
            <?php if (!empty($category) || !empty($brand) || !empty($is_offers) || $filters['q'] !== ''): ?><a href="<?= url('/shop') ?>">المنتجات</a> <?= icon('chevron-l', 13) ?><?php endif; ?>
            <b><?= e($heading) ?></b>
        </nav>
        <h1><?= e($heading) ?></h1>
        <p><?= !empty($description) ? e($description) : ($total . ' منتج') ?></p>
    </div>
</div>

<div class="container page-wrap">
    <div class="shop-layout">
        <aside class="shop-side" id="shop-filters" aria-label="الفلاتر">
            <div class="side-head">فلترة المنتجات <button type="button" class="icon-btn" data-close-filters aria-label="إغلاق"><?= icon('x', 20) ?></button></div>
            <form method="get" action="<?= url($base) ?>" data-filter-form>
                <?php if ($filters['q'] !== ''): ?><input type="hidden" name="q" value="<?= e($filters['q']) ?>"><?php endif; ?>
                <?php if ($filters['sort'] !== 'popular'): ?><input type="hidden" name="sort" value="<?= e($filters['sort']) ?>"><?php endif; ?>
                <?php foreach ($cats as $c): $open = in_array($c['slug'], $selected, true) || array_intersect(array_column($c['children'], 'slug'), $selected); ?>
                <details class="fgroup" <?= $open || !$selected ? 'open' : '' ?>>
                    <summary><?= e($c['name']) ?> <?= icon('chevron-d', 16) ?></summary>
                    <div class="fopts">
                        <label class="fcheck"><input type="checkbox" name="cat[]" value="<?= e($c['slug']) ?>" <?= in_array($c['slug'], $selected, true) ? 'checked' : '' ?> data-autosubmit> الكل <small><?= (int)$c['products_count'] ?></small></label>
                        <?php foreach ($c['children'] as $ch): ?>
                            <label class="fcheck child"><input type="checkbox" name="cat[]" value="<?= e($ch['slug']) ?>" <?= in_array($ch['slug'], $selected, true) ? 'checked' : '' ?> data-autosubmit> <?= e($ch['name']) ?> <small><?= (int)$ch['products_count'] ?></small></label>
                        <?php endforeach; ?>
                    </div>
                </details>
                <?php endforeach; ?>
                <details class="fgroup" open>
                    <summary>السعر (ج.م) <?= icon('chevron-d', 16) ?></summary>
                    <div class="fopts">
                        <div class="fprice">
                            <input type="number" name="min" id="f-min" min="0" placeholder="من" value="<?= e($filters['min']) ?>" inputmode="numeric" aria-label="أقل سعر">
                            <input type="number" name="max" id="f-max" min="0" placeholder="إلى" value="<?= e($filters['max']) ?>" inputmode="numeric" aria-label="أعلى سعر">
                        </div>
                        <label class="fcheck"><input type="checkbox" name="stock" value="1" <?= $filters['stock'] ? 'checked' : '' ?> data-autosubmit> المتوفر فقط</label>
                        <button class="btn btn-dark btn-sm btn-block" style="margin-top:6px">تطبيق</button>
                    </div>
                </details>
            </form>
        </aside>

        <div class="shop-main">
            <div class="shop-toolbar">
                <button type="button" class="btn btn-outline btn-sm filters-toggle" data-toggle-filters><?= icon('filter', 16) ?> فلترة</button>
                <div class="active-chips">
                    <?php if ($chips): foreach ($chips as [$label, $q]): ?><a href="<?= e(url($base) . $q) ?>" class="chip"><?= e($label) ?> <?= icon('x', 14) ?></a><?php endforeach; ?>
                        <a href="<?= url($base) ?><?= $filters['q'] !== '' ? '?q=' . urlencode($filters['q']) : '' ?>" class="clear">مسح الكل</a>
                    <?php else: ?><span class="muted small"><?= $total ?> منتج</span><?php endif; ?>
                </div>
                <div class="sort-form">
                    <label for="sort">ترتيب حسب</label>
                    <select id="sort" data-sort-select>
                        <?php foreach (['popular' => 'الأكثر مبيعاً', 'newest' => 'الأحدث', 'price_asc' => 'السعر: من الأقل', 'price_desc' => 'السعر: من الأعلى', 'rating' => 'الأعلى تقييماً'] as $k => $v): ?>
                            <option value="<?= e(url($base) . $qs(['sort' => $k, 'page' => 1])) ?>" <?= $filters['sort'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php if ($result['items']): ?>
                <div class="products-grid products-grid-3"><?php foreach ($result['items'] as $p) echo view('partials/product-card', ['p' => $p]); ?></div>
                <?php if ($result['pages'] > 1): ?>
                <nav class="pagination" aria-label="الصفحات"><?php for ($i = 1; $i <= $result['pages']; $i++): ?><a href="<?= e(url($base) . $qs(['page' => $i])) ?>" class="<?= $i === $result['page'] ? 'active' : '' ?>" <?= $i === $result['page'] ? 'aria-current="page"' : '' ?>><?= $i ?></a><?php endfor; ?></nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="card empty">
                    <span class="empty-ico"><?= icon('search', 30) ?></span>
                    <h3>مفيش منتجات مطابقة</h3>
                    <p>جرّب كلمة بحث تانية أو شيل بعض الفلاتر.</p>
                    <a href="<?= url('/shop') ?>" class="btn btn-dark">كل المنتجات</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
