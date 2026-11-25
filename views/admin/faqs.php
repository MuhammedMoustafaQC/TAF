<div class="grid-2-1 grid-reverse">
    <section class="panel panel-flush">
        <div class="panel-head pad"><h2>الأسئلة الشائعة</h2></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>السؤال</th><th>الإجابة</th><th>الترتيب</th><th>الحالة</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($items as $f): ?>
                    <tr>
                        <td><b><?= e($f['question']) ?></b></td>
                        <td class="clip"><?= e($f['answer']) ?></td>
                        <td><?= (int)$f['sort_order'] ?></td>
                        <td><?= $f['is_active'] ? '<span class="badge badge-success">ظاهر</span>' : '<span class="badge badge-muted">مخفي</span>' ?></td>
                        <td class="actions">
                            <a href="<?= url('/admin/faqs?edit=' . $f['id']) ?>" class="icon-btn" title="تعديل"><?= icon('edit', 18) ?></a>
                            <form action="<?= url('/admin/faqs/' . $f['id'] . '/delete') ?>" method="post" data-confirm="حذف السؤال؟">
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
        <h2><?= $edit && !empty($edit['id']) ? 'تعديل سؤال' : 'إضافة سؤال' ?></h2>
        <form method="post" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
            <label class="field"><span>السؤال *</span><input type="text" name="question" value="<?= e($edit['question'] ?? '') ?>" required class="<?= inv($errors, 'question') ?>"><?= err($errors, 'question') ?></label>
            <label class="field"><span>الإجابة *</span><textarea name="answer" rows="5" required class="<?= inv($errors, 'answer') ?>"><?= e($edit['answer'] ?? '') ?></textarea><?= err($errors, 'answer') ?></label>
            <label class="field"><span>الترتيب</span><input type="number" name="sort_order" value="<?= (int)($edit['sort_order'] ?? 0) ?>"></label>
            <label class="check"><input type="checkbox" name="is_active" value="1" <?= ($edit['is_active'] ?? 1) ? 'checked' : '' ?>> ظاهر في الموقع</label>
            <div class="btn-row">
                <button class="btn btn-primary"><?= icon('check', 18) ?> حفظ</button>
                <?php if ($edit): ?><a href="<?= url('/admin/faqs') ?>" class="btn btn-light">إلغاء</a><?php endif; ?>
            </div>
        </form>
    </section>
</div>
