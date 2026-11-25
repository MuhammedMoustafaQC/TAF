<div class="grid-2-1 grid-reverse">
    <section class="panel panel-flush">
        <div class="panel-head pad"><h2>شرائح السلايدر</h2><span class="muted small">بتظهر أول الصفحة الرئيسية</span></div>
        <div class="slides-list">
            <?php foreach ($slides as $s): ?>
            <div class="slide-item">
                <div class="slide-thumb"><img src="<?= e(img($s['image'])) ?>" alt=""></div>
                <div class="slide-info">
                    <b><?= e($s['title']) ?></b>
                    <small class="muted"><?= e($s['subtitle']) ?></small>
                    <small><?= $s['is_active'] ? '<span class="badge badge-success">ظاهرة</span>' : '<span class="badge badge-muted">مخفية</span>' ?> · ترتيب <?= (int)$s['sort_order'] ?><?php if ($s['button_text']): ?> · زر: <?= e($s['button_text']) ?> <span dir="ltr">(<?= e($s['button_link']) ?>)</span><?php endif; ?></small>
                </div>
                <div class="actions">
                    <a href="<?= url('/admin/slides?edit=' . $s['id']) ?>" class="icon-btn" title="تعديل"><?= icon('edit', 18) ?></a>
                    <form action="<?= url('/admin/slides/' . $s['id'] . '/delete') ?>" method="post" data-confirm="حذف الشريحة؟">
                        <?= csrf_field() ?><button class="icon-btn icon-btn-danger" title="حذف"><?= icon('trash', 18) ?></button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (!$slides): ?><div class="empty-state"><p>مفيش شرائح. ضيف أول شريحة.</p></div><?php endif; ?>
        </div>
    </section>

    <section class="panel">
        <h2><?= $edit && !empty($edit['id']) ? 'تعديل شريحة' : 'إضافة شريحة' ?></h2>
        <form method="post" enctype="multipart/form-data" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
            <label class="field"><span>العنوان *</span><input type="text" name="title" value="<?= e($edit['title'] ?? '') ?>" required class="<?= inv($errors, 'title') ?>"><?= err($errors, 'title') ?></label>
            <label class="field"><span>النص</span><textarea name="subtitle" rows="3"><?= e($edit['subtitle'] ?? '') ?></textarea></label>
            <div class="form-grid">
                <label class="field"><span>نص الزر</span><input type="text" name="button_text" value="<?= e($edit['button_text'] ?? '') ?>" placeholder="تسوق الآن"></label>
                <label class="field"><span>رابط الزر</span><input type="text" name="button_link" value="<?= e($edit['button_link'] ?? '') ?>" placeholder="/shop" dir="ltr"></label>
            </div>
            <label class="field"><span>الترتيب</span><input type="number" name="sort_order" value="<?= (int)($edit['sort_order'] ?? 0) ?>"></label>
            <div class="field">
                <span>الصورة <small class="muted">(يفضل PNG بخلفية شفافة)</small></span>
                <label class="upload upload-sm upload-dark">
                    <img src="<?= e(img($edit['image'] ?? null)) ?>" alt="" data-preview>
                    <input type="file" name="image" accept="image/*" data-preview-input>
                    <small>اختار صورة</small>
                </label>
                <?= err($errors, 'image') ?>
            </div>
            <label class="check"><input type="checkbox" name="is_active" value="1" <?= ($edit['is_active'] ?? 1) ? 'checked' : '' ?>> ظاهرة</label>
            <div class="btn-row">
                <button class="btn btn-primary"><?= icon('check', 18) ?> حفظ</button>
                <?php if ($edit): ?><a href="<?= url('/admin/slides') ?>" class="btn btn-light">إلغاء</a><?php endif; ?>
            </div>
        </form>
    </section>
</div>
