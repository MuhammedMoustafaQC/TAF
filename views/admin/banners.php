<?php $positions = banner_positions(); ?>
<div class="grid-2-1 grid-reverse">
    <div class="stack">
        <?php foreach ($positions as $pos => $label): ?>
        <section class="panel panel-flush">
            <div class="panel-head pad"><h2><?= e($label) ?></h2><span class="muted small" dir="ltr"><?= e($pos) ?></span></div>
            <div class="slides-list">
                <?php foreach ($grouped[$pos] ?? [] as $b): ?>
                <div class="slide-item">
                    <div class="banner-thumb" style="background: <?= e($b['bg_color'] ?: '#2B04B5') ?>">
                        <?php if ($b['image']): ?><img src="<?= e(img($b['image'])) ?>" alt=""><?php endif; ?>
                        <?php if ($b['title']): ?><span><?= e($b['title']) ?></span><?php endif; ?>
                    </div>
                    <div class="slide-info">
                        <b><?= $b['title'] ? e($b['title']) : '<span class="muted">بانر صورة بدون نص</span>' ?></b>
                        <?php if ($b['subtitle']): ?><small class="muted"><?= e($b['subtitle']) ?></small><?php endif; ?>
                        <small><?= $b['is_active'] ? '<span class="badge badge-success">ظاهر</span>' : '<span class="badge badge-muted">مخفي</span>' ?> · ترتيب <?= (int)$b['sort_order'] ?><?php if ($b['link']): ?> · <span dir="ltr"><?= e($b['link']) ?></span><?php endif; ?></small>
                    </div>
                    <div class="actions">
                        <a href="<?= url('/admin/banners?edit=' . $b['id']) ?>" class="icon-btn" title="تعديل"><?= icon('edit', 18) ?></a>
                        <form action="<?= url('/admin/banners/' . $b['id'] . '/delete') ?>" method="post" data-confirm="حذف البانر؟">
                            <?= csrf_field() ?><button class="icon-btn icon-btn-danger" title="حذف"><?= icon('trash', 18) ?></button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($grouped[$pos])): ?><div class="empty-state empty-sm"><p>مفيش بانرات في المكان ده — القسم مش هيظهر في الرئيسية.</p></div><?php endif; ?>
            </div>
        </section>
        <?php endforeach; ?>
    </div>

    <section class="panel sticky-panel">
        <h2><?= $edit && !empty($edit['id']) ? 'تعديل بانر' : 'إضافة بانر' ?></h2>
        <form method="post" enctype="multipart/form-data" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
            <label class="field"><span>مكان البانر *</span>
                <select name="position" required class="<?= inv($errors, 'position') ?>">
                    <?php foreach ($positions as $pos => $label): ?><option value="<?= e($pos) ?>" <?= ($edit['position'] ?? ($_GET['position'] ?? '')) === $pos ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
                </select>
                <?= err($errors, 'position') ?>
            </label>
            <label class="field"><span>العنوان <small class="muted">(اختياري لو الصورة فيها الكلام)</small></span><input type="text" name="title" value="<?= e($edit['title'] ?? '') ?>" class="<?= inv($errors, 'title') ?>"><?= err($errors, 'title') ?></label>
            <label class="field"><span>النص الفرعي</span><input type="text" name="subtitle" value="<?= e($edit['subtitle'] ?? '') ?>"></label>
            <div class="form-grid">
                <label class="field"><span>نص الزر</span><input type="text" name="button_text" value="<?= e($edit['button_text'] ?? '') ?>" placeholder="اطلب الآن"></label>
                <label class="field"><span>الرابط</span><input type="text" name="link" value="<?= e($edit['link'] ?? '') ?>" placeholder="/offers" dir="ltr"></label>
            </div>
            <div class="form-grid">
                <label class="field"><span>لون الخلفية</span><input type="color" name="bg_color" value="<?= e($edit['bg_color'] ?? '#2B04B5') ?>" class="color-input"></label>
                <label class="field"><span>الترتيب</span><input type="number" name="sort_order" value="<?= (int)($edit['sort_order'] ?? 0) ?>"></label>
            </div>
            <div class="field">
                <span>الصورة</span>
                <label class="upload upload-sm">
                    <img src="<?= e(img($edit['image'] ?? null)) ?>" alt="" data-preview>
                    <input type="file" name="image" accept="image/*" data-preview-input>
                    <small>اختار صورة (منتج بخلفية شفافة، أو بانر كامل بالكلام)</small>
                </label>
                <?= err($errors, 'image') ?>
            </div>
            <label class="check"><input type="checkbox" name="is_active" value="1" <?= ($edit['is_active'] ?? 1) ? 'checked' : '' ?>> ظاهر في الموقع</label>
            <div class="btn-row">
                <button class="btn btn-primary"><?= icon('check', 18) ?> حفظ</button>
                <?php if ($edit): ?><a href="<?= url('/admin/banners') ?>" class="btn btn-light">إلغاء</a><?php endif; ?>
            </div>
        </form>
    </section>
</div>
