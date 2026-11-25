<div class="grid-2-1 grid-reverse">
    <section class="panel panel-flush">
        <div class="panel-head pad"><h2>كل الأقسام</h2></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>القسم</th><th>المنتجات</th><th>الترتيب</th><th>الحالة</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($categories as $c): ?>
                    <tr>
                        <td><div class="prod-cell<?= $c['parent_id'] ? ' is-child' : '' ?>"><img src="<?= e(img($c['image'])) ?>" alt=""><div><b><?= $c['parent_id'] ? '<span class="muted">↳</span> ' : '' ?><?= e($c['name']) ?></b><small class="muted"><?= $c['parent_id'] ? 'فرعي من: ' . e($c['parent_name']) : 'قسم رئيسي' ?> · <span dir="ltr">/category/<?= e($c['slug']) ?></span></small></div></div></td>
                        <td><?= (int)$c['products_count'] ?></td>
                        <td><?= (int)$c['sort_order'] ?></td>
                        <td><?= $c['is_active'] ? '<span class="badge badge-success">ظاهر</span>' : '<span class="badge badge-muted">مخفي</span>' ?></td>
                        <td class="actions">
                            <a href="<?= url('/admin/categories?edit=' . $c['id']) ?>" class="icon-btn" title="تعديل"><?= icon('edit', 18) ?></a>
                            <form action="<?= url('/admin/categories/' . $c['id'] . '/delete') ?>" method="post" data-confirm="حذف القسم؟ المنتجات اللي فيه هتفضل موجودة بس بدون قسم.">
                                <?= csrf_field() ?><button class="icon-btn icon-btn-danger" title="حذف"><?= icon('trash', 18) ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <h2><?= $edit && !empty($edit['id']) ? 'تعديل قسم' : 'إضافة قسم جديد' ?></h2>
        <form method="post" enctype="multipart/form-data" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
            <label class="field"><span>اسم القسم *</span><input type="text" name="name" value="<?= e($edit['name'] ?? '') ?>" required class="<?= inv($errors, 'name') ?>"><?= err($errors, 'name') ?></label>
            <label class="field"><span>القسم الرئيسي</span>
                <select name="parent_id" class="<?= inv($errors, 'parent_id') ?>">
                    <option value="">— قسم رئيسي —</option>
                    <?php foreach ($parents as $pc): if ((int)$pc['id'] === (int)($edit['id'] ?? 0)) continue; ?>
                        <option value="<?= (int)$pc['id'] ?>" <?= (int)($edit['parent_id'] ?? 0) === (int)$pc['id'] ? 'selected' : '' ?>><?= e($pc['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= err($errors, 'parent_id') ?>
            </label>
            <label class="field"><span>الرابط (Slug)</span><input type="text" name="slug" value="<?= e($edit['slug'] ?? '') ?>" placeholder="تلقائي" dir="ltr"></label>
            <label class="field"><span>وصف</span><textarea name="description" rows="3"><?= e($edit['description'] ?? '') ?></textarea></label>
            <label class="field"><span>الترتيب</span><input type="number" name="sort_order" value="<?= (int)($edit['sort_order'] ?? 0) ?>"></label>
            <div class="field">
                <span>صورة القسم</span>
                <label class="upload upload-sm">
                    <img src="<?= e(img($edit['image'] ?? null)) ?>" alt="" data-preview>
                    <input type="file" name="image" accept="image/*" data-preview-input>
                    <small>اختار صورة</small>
                </label>
                <?= err($errors, 'image') ?>
            </div>
            <label class="check"><input type="checkbox" name="is_active" value="1" <?= ($edit['is_active'] ?? 1) ? 'checked' : '' ?>> ظاهر في الموقع</label>
            <div class="btn-row">
                <button class="btn btn-primary"><?= icon('check', 18) ?> حفظ</button>
                <?php if ($edit): ?><a href="<?= url('/admin/categories') ?>" class="btn btn-light">إلغاء</a><?php endif; ?>
            </div>
        </form>
    </section>
</div>
