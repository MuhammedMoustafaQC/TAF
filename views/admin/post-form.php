<form method="post" enctype="multipart/form-data" class="form-layout">
    <?= csrf_field() ?>
    <section class="panel">
        <h2>المقال</h2>
        <label class="field"><span>العنوان *</span><input type="text" name="title" value="<?= e($p['title']) ?>" required class="<?= inv($errors, 'title') ?>"><?= err($errors, 'title') ?></label>
        <label class="field"><span>الرابط (Slug)</span><input type="text" name="slug" value="<?= e($p['slug']) ?>" placeholder="بيتعمل تلقائياً من العنوان"></label>
        <label class="field"><span>ملخص قصير <small class="muted">(بيظهر في كارت المقال)</small></span><textarea name="excerpt" rows="2" maxlength="300"><?= e($p['excerpt']) ?></textarea></label>
        <label class="field">
            <span>المحتوى * <small class="muted">(سطر يبدأ بـ ## يبقى عنوان، وكل سطر تاني فقرة)</small></span>
            <textarea name="content" rows="16" class="<?= inv($errors, 'content') ?>" placeholder="مقدمة المقال...&#10;## عنوان فرعي&#10;فقرة تحت العنوان..."><?= e($p['content']) ?></textarea>
            <?= err($errors, 'content') ?>
        </label>
    </section>

    <div class="stack">
        <section class="panel">
            <h2>صورة المقال</h2>
            <label class="upload">
                <img src="<?= e(img($p['image'])) ?>" alt="" data-preview>
                <input type="file" name="image" accept="image/*" data-preview-input>
                <small>اضغط لاختيار صورة (JPG / PNG / WEBP — أقصى حجم 4MB)</small>
            </label>
            <?= err($errors, 'image') ?>
            <label class="check"><input type="checkbox" name="is_published" value="1" <?= $p['is_published'] ? 'checked' : '' ?>> منشور في الموقع</label>
        </section>
        <div class="sticky-actions">
            <button class="btn btn-primary btn-block btn-lg"><?= icon('check', 18) ?> حفظ المقال</button>
            <?php if ($p['id']): ?>
                <a href="<?= url('/blog/' . $p['slug']) ?>" target="_blank" class="btn btn-light btn-block"><?= icon('eye', 18) ?> عرض في الموقع</a>
            <?php endif; ?>
            <a href="<?= url('/admin/posts') ?>" class="btn btn-ghost btn-block">رجوع للمدونة</a>
        </div>
    </div>
</form>
<?php if ($p['id']): ?>
<form action="<?= url('/admin/posts/' . $p['id'] . '/delete') ?>" method="post" data-confirm="حذف المقال نهائياً؟" class="danger-zone">
    <?= csrf_field() ?>
    <button class="btn btn-danger-outline btn-sm"><?= icon('trash', 16) ?> حذف المقال</button>
</form>
<?php endif; ?>
