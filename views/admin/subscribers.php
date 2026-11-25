<div class="toolbar">
    <span class="muted"><?= count($subscribers) ?> مشترك في النشرة البريدية</span>
    <?php if ($subscribers): ?><a href="<?= url('/admin/subscribers?export=csv') ?>" class="btn btn-primary btn-sm"><?= icon('external', 16) ?> تصدير CSV</a><?php endif; ?>
</div>

<section class="panel panel-flush">
    <?php if ($subscribers): ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>#</th><th>البريد الإلكتروني</th><th>تاريخ الاشتراك</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($subscribers as $i => $s): ?>
                <tr>
                    <td class="muted"><?= count($subscribers) - $i ?></td>
                    <td><a href="mailto:<?= e($s['email']) ?>" dir="ltr"><?= e($s['email']) ?></a></td>
                    <td><small><?= e(ar_date($s['created_at'])) ?></small></td>
                    <td class="actions">
                        <form action="<?= url('/admin/subscribers/' . $s['id'] . '/delete') ?>" method="post" data-confirm="حذف المشترك؟">
                            <?= csrf_field() ?><button class="icon-btn icon-btn-danger" title="حذف"><?= icon('trash', 18) ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="empty-state"><?= icon('mail', 40) ?><p>لسه مفيش مشتركين. فورم "انضم لمجتمعنا" في فوتر الموقع بيضيفهم هنا.</p></div>
    <?php endif; ?>
</section>
