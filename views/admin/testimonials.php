<div class="grid-2-1 grid-reverse">
    <section class="panel panel-flush">
        <div class="panel-head pad"><h2>آراء العملاء</h2></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>العميل</th><th>الرأي</th><th>التقييم</th><th>الحالة</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($items as $t): ?>
                    <tr>
                        <td><b><?= e($t['name']) ?></b><br><small class="muted"><?= e($t['city']) ?></small></td>
                        <td class="clip"><?= e($t['content']) ?></td>
                        <td><span class="stars-txt"><?= str_repeat('★', (int)$t['rating']) ?><span><?= str_repeat('★', 5 - (int)$t['rating']) ?></span></span></td>
                        <td><?= $t['is_active'] ? '<span class="badge badge-success">ظاهر</span>' : '<span class="badge badge-muted">مخفي</span>' ?></td>
                        <td class="actions">
                            <a href="<?= url('/admin/testimonials?edit=' . $t['id']) ?>" class="icon-btn" title="تعديل"><?= icon('edit', 18) ?></a>
                            <form action="<?= url('/admin/testimonials/' . $t['id'] . '/delete') ?>" method="post" data-confirm="حذف؟">
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
        <h2><?= $edit && !empty($edit['id']) ? 'تعديل رأي' : 'إضافة رأي عميل' ?></h2>
        <form method="post" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
            <div class="form-grid">
                <label class="field"><span>اسم العميل *</span><input type="text" name="name" value="<?= e($edit['name'] ?? '') ?>" required class="<?= inv($errors, 'name') ?>"><?= err($errors, 'name') ?></label>
                <label class="field"><span>المدينة</span><input type="text" name="city" value="<?= e($edit['city'] ?? '') ?>"></label>
            </div>
            <label class="field"><span>الرأي *</span><textarea name="content" rows="4" required class="<?= inv($errors, 'content') ?>"><?= e($edit['content'] ?? '') ?></textarea><?= err($errors, 'content') ?></label>
            <div class="form-grid">
                <label class="field"><span>التقييم</span>
                    <select name="rating"><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>" <?= (int)($edit['rating'] ?? 5) === $i ? 'selected' : '' ?>><?= $i ?> نجوم</option><?php endfor; ?></select>
                </label>
                <label class="field"><span>الترتيب</span><input type="number" name="sort_order" value="<?= (int)($edit['sort_order'] ?? 0) ?>"></label>
            </div>
            <label class="check"><input type="checkbox" name="is_active" value="1" <?= ($edit['is_active'] ?? 1) ? 'checked' : '' ?>> ظاهر في الموقع</label>
            <div class="btn-row">
                <button class="btn btn-primary"><?= icon('check', 18) ?> حفظ</button>
                <?php if ($edit): ?><a href="<?= url('/admin/testimonials') ?>" class="btn btn-light">إلغاء</a><?php endif; ?>
            </div>
        </form>
    </section>
</div>
