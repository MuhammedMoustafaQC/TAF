<div class="toolbar">
    <form method="get" class="filters">
        <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="ابحث باسم المنتج...">
        <select name="category" data-autosubmit>
            <option value="">كل الأقسام</option>
            <?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)($_GET['category'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
        <select name="brand" data-autosubmit>
            <option value="">كل الماركات</option>
            <?php foreach ($brands as $b): ?><option value="<?= (int)$b['id'] ?>" <?= (int)($_GET['brand'] ?? 0) === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?>
        </select>
        <select name="status" data-autosubmit>
            <?php foreach (['' => 'كل الحالات', 'active' => 'ظاهر', 'hidden' => 'مخفي', 'low' => 'مخزون قليل'] as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($_GET['status'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-light btn-sm"><?= icon('search', 16) ?> بحث</button>
    </form>
    <a href="<?= url('/admin/products/new') ?>" class="btn btn-primary"><?= icon('plus', 18) ?> إضافة منتج</a>
</div>

<section class="panel panel-flush">
    <?php if ($products): ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>المنتج</th><th>القسم</th><th>الماركة</th><th>السعر</th><th>المخزون</th><th>المبيعات</th><th>مميز</th><th>ظاهر</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td>
                        <div class="prod-cell">
                            <img src="<?= e(img($p['image'])) ?>" alt="">
                            <div>
                                <a href="<?= url('/admin/products/' . $p['id'] . '/edit') ?>"><b><?= e($p['name']) ?></b></a>
                                <span class="cell-tags">
                                    <?php if ($p['badge']): ?><small class="badge badge-primary"><?= e($p['badge']) ?></small><?php endif; ?>
                                    <?php if ((int)($p['installment_months'] ?? 0) > 0): ?><small class="badge badge-info">تقسيط <?= (int)$p['installment_months'] ?> شهور</small><?php endif; ?>
                                    <?php if ((int)($p['rating_count'] ?? 0) > 0): ?><small class="badge badge-warning">★ <?= e(number_format((float)$p['rating_avg'], 1)) ?> (<?= (int)$p['rating_count'] ?>)</small><?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </td>
                    <td><?= e($p['category_name'] ?? '—') ?></td>
                    <td><?= e($p['brand_name'] ?? '—') ?></td>
                    <td><b><?= money($p['price']) ?></b><?php if ($p['old_price'] > $p['price']): ?><br><del class="muted small"><?= money($p['old_price']) ?></del><?php endif; ?></td>
                    <td><span class="badge <?= $p['stock'] <= 0 ? 'badge-danger' : ($p['stock'] <= 5 ? 'badge-warning' : 'badge-success') ?>"><?= (int)$p['stock'] ?></span></td>
                    <td><?= number_format($p['sales_count']) ?></td>
                    <td>
                        <form action="<?= url('/admin/products/' . $p['id'] . '/toggle') ?>" method="post" data-toggle-form>
                            <?= csrf_field() ?><input type="hidden" name="field" value="is_featured">
                            <button class="switch <?= $p['is_featured'] ? 'on' : '' ?>" aria-label="مميز" title="يظهر في الأكثر مبيعاً"><i></i></button>
                        </form>
                    </td>
                    <td>
                        <form action="<?= url('/admin/products/' . $p['id'] . '/toggle') ?>" method="post" data-toggle-form>
                            <?= csrf_field() ?><input type="hidden" name="field" value="is_active">
                            <button class="switch <?= $p['is_active'] ? 'on' : '' ?>" aria-label="ظاهر في الموقع"><i></i></button>
                        </form>
                    </td>
                    <td class="actions">
                        <a href="<?= url('/product/' . $p['slug']) ?>" target="_blank" class="icon-btn" title="عرض في الموقع"><?= icon('eye', 18) ?></a>
                        <a href="<?= url('/admin/products/' . $p['id'] . '/edit') ?>" class="icon-btn" title="تعديل"><?= icon('edit', 18) ?></a>
                        <form action="<?= url('/admin/products/' . $p['id'] . '/delete') ?>" method="post" data-confirm="متأكد إنك عايز تحذف المنتج ده؟">
                            <?= csrf_field() ?><button class="icon-btn icon-btn-danger" title="حذف"><?= icon('trash', 18) ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="empty-state"><?= icon('box', 40) ?><p>مفيش منتجات مطابقة.</p><a href="<?= url('/admin/products/new') ?>" class="btn btn-primary btn-sm">إضافة منتج</a></div>
    <?php endif; ?>
</section>
