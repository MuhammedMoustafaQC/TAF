<div class="toolbar">
    <span class="muted"><?= count($posts) ?> مقال</span>
    <a href="<?= url('/admin/posts/new') ?>" class="btn btn-primary"><?= icon('plus', 18) ?> مقال جديد</a>
</div>

<section class="panel panel-flush">
    <?php if ($posts): ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>المقال</th><th>الحالة</th><th>المشاهدات</th><th>التاريخ</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($posts as $p): ?>
                <tr>
                    <td><div class="prod-cell"><img src="<?= e(img($p['image'])) ?>" alt=""><div><a href="<?= url('/admin/posts/' . $p['id'] . '/edit') ?>"><b><?= e($p['title']) ?></b></a><?php if ($p['excerpt']): ?><small class="muted clip"><?= e($p['excerpt']) ?></small><?php endif; ?></div></div></td>
                    <td><?= $p['is_published'] ? '<span class="badge badge-success">منشور</span>' : '<span class="badge badge-muted">مسودة</span>' ?></td>
                    <td><?= number_format((int)$p['views']) ?></td>
                    <td><small><?= e(ar_date($p['created_at'], false)) ?></small></td>
                    <td class="actions">
                        <a href="<?= url('/blog/' . $p['slug']) ?>" target="_blank" class="icon-btn" title="عرض في الموقع"><?= icon('eye', 18) ?></a>
                        <a href="<?= url('/admin/posts/' . $p['id'] . '/edit') ?>" class="icon-btn" title="تعديل"><?= icon('edit', 18) ?></a>
                        <form action="<?= url('/admin/posts/' . $p['id'] . '/delete') ?>" method="post" data-confirm="حذف المقال نهائياً؟">
                            <?= csrf_field() ?><button class="icon-btn icon-btn-danger" title="حذف"><?= icon('trash', 18) ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="empty-state"><?= icon('quote', 40) ?><p>مفيش مقالات لسه.</p><a href="<?= url('/admin/posts/new') ?>" class="btn btn-primary btn-sm">اكتب أول مقال</a></div>
    <?php endif; ?>
</section>
