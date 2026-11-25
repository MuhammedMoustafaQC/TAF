<?php $pageUrl = fn($slug) => $slug === 'services' ? '/services' : '/page/' . $slug; ?>
<section class="panel panel-flush">
    <div class="panel-head pad"><h2>صفحات الموقع الثابتة</h2><span class="muted small">بتظهر في الفوتر وقائمة الموقع</span></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>الصفحة</th><th>الرابط</th><th>آخر تعديل</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($pages as $p): ?>
                <tr class="row-link" data-href="<?= url('/admin/pages/' . $p['id'] . '/edit') ?>">
                    <td><a href="<?= url('/admin/pages/' . $p['id'] . '/edit') ?>"><b><?= e($p['title']) ?></b></a></td>
                    <td><span dir="ltr" class="muted"><?= e($pageUrl($p['slug'])) ?></span></td>
                    <td><small><?= e(ar_date($p['updated_at'])) ?></small></td>
                    <td class="actions">
                        <a href="<?= url($pageUrl($p['slug'])) ?>" target="_blank" class="icon-btn" title="عرض في الموقع"><?= icon('eye', 18) ?></a>
                        <a href="<?= url('/admin/pages/' . $p['id'] . '/edit') ?>" class="icon-btn" title="تعديل"><?= icon('edit', 18) ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (!$pages): ?><div class="empty-state"><p>مفيش صفحات.</p></div><?php endif; ?>
</section>
