<?php $pageUrl = $p['slug'] === 'services' ? '/services' : '/page/' . $p['slug']; ?>
<form method="post" class="form-layout">
    <?= csrf_field() ?>
    <section class="panel">
        <h2>محتوى الصفحة</h2>
        <label class="field"><span>العنوان *</span><input type="text" name="title" value="<?= e($p['title']) ?>" required class="<?= inv($errors, 'title') ?>"><?= err($errors, 'title') ?></label>
        <label class="field"><span>الرابط</span><input type="text" value="<?= e($pageUrl) ?>" readonly dir="ltr" class="is-readonly"></label>
        <label class="field">
            <span>المحتوى <small class="muted">(سطر يبدأ بـ ## يبقى عنوان، وكل سطر تاني فقرة)</small></span>
            <textarea name="content" rows="18"><?= e($p['content']) ?></textarea>
        </label>
    </section>
    <div class="stack">
        <div class="sticky-actions">
            <button class="btn btn-primary btn-block btn-lg"><?= icon('check', 18) ?> حفظ الصفحة</button>
            <a href="<?= url($pageUrl) ?>" target="_blank" class="btn btn-light btn-block"><?= icon('eye', 18) ?> عرض في الموقع</a>
            <a href="<?= url('/admin/pages') ?>" class="btn btn-ghost btn-block">رجوع للصفحات</a>
        </div>
    </div>
</form>
