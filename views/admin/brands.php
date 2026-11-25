<div class="grid-2-1 grid-reverse">
    <section class="panel panel-flush">
        <div class="panel-head pad"><h2>كل الماركات</h2><span class="muted small">بتظهر في "تسوق حسب الماركة" وفي صفحة كل منتج</span></div>
        <?php if ($brands): ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>الماركة</th><th>المنتجات</th><th>الترتيب</th><th>الحالة</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($brands as $b): ?>
                    <tr>
                        <td><div class="prod-cell"><img src="<?= e(img($b['logo'])) ?>" alt="" class="logo-thumb"><div><b><?= e($b['name']) ?></b><small class="muted" dir="ltr">/brand/<?= e($b['slug']) ?></small></div></div></td>
                        <td><a href="<?= url('/admin/products?brand=' . $b['id']) ?>"><?= (int)$b['products_count'] ?></a></td>
                        <td><?= (int)$b['sort_order'] ?></td>
                        <td><?= $b['is_active'] ? '<span class="badge badge-success">ظاهرة</span>' : '<span class="badge badge-muted">مخفية</span>' ?></td>
                        <td class="actions">
                            <a href="<?= url('/brand/' . $b['slug']) ?>" target="_blank" class="icon-btn" title="عرض في الموقع"><?= icon('eye', 18) ?></a>
                            <a href="<?= url('/admin/brands?edit=' . $b['id']) ?>" class="icon-btn" title="تعديل"><?= icon('edit', 18) ?></a>
                            <form action="<?= url('/admin/brands/' . $b['id'] . '/delete') ?>" method="post" data-confirm="حذف الماركة؟ المنتجات هتفضل موجودة بس بدون ماركة.">
                                <?= csrf_field() ?><button class="icon-btn icon-btn-danger" title="حذف"><?= icon('trash', 18) ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state"><?= icon('award', 40) ?><p>مفيش ماركات لسه. ضيف أول ماركة.</p></div>
        <?php endif; ?>
    </section>

    <section class="panel">
        <h2><?= $edit && !empty($edit['id']) ? 'تعديل ماركة' : 'إضافة ماركة' ?></h2>
        <form method="post" enctype="multipart/form-data" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
            <label class="field"><span>اسم الماركة *</span><input type="text" name="name" value="<?= e($edit['name'] ?? '') ?>" required class="<?= inv($errors, 'name') ?>"><?= err($errors, 'name') ?></label>
            <label class="field"><span>الرابط (Slug)</span><input type="text" name="slug" value="<?= e($edit['slug'] ?? '') ?>" placeholder="تلقائي" dir="ltr"></label>
            <label class="field"><span>وصف مختصر</span><textarea name="description" rows="3"><?= e($edit['description'] ?? '') ?></textarea></label>
            <label class="field"><span>الترتيب</span><input type="number" name="sort_order" value="<?= (int)($edit['sort_order'] ?? 0) ?>"></label>
            <div class="field">
                <span>اللوجو <small class="muted">(يفضل PNG بخلفية شفافة)</small></span>
                <label class="upload upload-sm upload-logo">
                    <img src="<?= e(img($edit['logo'] ?? null)) ?>" alt="" data-preview>
                    <input type="file" name="logo" accept="image/*" data-preview-input>
                    <small>اختار صورة</small>
                </label>
                <?= err($errors, 'logo') ?>
            </div>
            <label class="check"><input type="checkbox" name="is_active" value="1" <?= ($edit['is_active'] ?? 1) ? 'checked' : '' ?>> ظاهرة في الموقع</label>
            <div class="btn-row">
                <button class="btn btn-primary"><?= icon('check', 18) ?> حفظ</button>
                <?php if ($edit): ?><a href="<?= url('/admin/brands') ?>" class="btn btn-light">إلغاء</a><?php endif; ?>
            </div>
        </form>
    </section>
</div>
