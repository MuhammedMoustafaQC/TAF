<?php $cur = $_GET['status'] ?? ''; ?>
<div class="pills">
    <a href="<?= url('/admin/requests') ?>" class="<?= $cur === '' ? 'active' : '' ?>">الكل</a>
    <?php foreach (request_statuses() as $k => [$label]): ?>
        <a href="<?= url('/admin/requests?status=' . $k) ?>" class="<?= $cur === $k ? 'active' : '' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</div>

<?php if ($requests): ?>
<div class="cards-grid">
    <?php foreach ($requests as $r): ?>
    <article class="panel req-card">
        <div class="req-head">
            <div><b><?= e($r['name']) ?></b><small class="muted"><?= e(ar_date($r['created_at'])) ?></small></div>
            <?= status_badge($r['status'], request_statuses()) ?>
        </div>
        <dl class="details details-sm">
            <dt>الخدمة</dt><dd><b><?= e($r['service_type']) ?></b></dd>
            <dt>الموبايل</dt><dd><a href="<?= e(tel_link($r['phone'])) ?>" dir="ltr"><?= e($r['phone']) ?></a></dd>
            <dt>المحافظة</dt><dd><?= e($r['governorate']) ?></dd>
            <?php if ($r['address']): ?><dt>العنوان</dt><dd><?= e($r['address']) ?></dd><?php endif; ?>
            <?php if ($r['preferred_date']): ?><dt>الميعاد المفضل</dt><dd><?= e($r['preferred_date']) ?></dd><?php endif; ?>
            <?php if ($r['notes']): ?><dt>ملاحظات</dt><dd><?= nl2br(e($r['notes'])) ?></dd><?php endif; ?>
        </dl>
        <div class="req-actions">
            <form action="<?= url('/admin/requests/' . $r['id'] . '/status') ?>" method="post" class="inline-form">
                <?= csrf_field() ?>
                <select name="status" data-autosubmit>
                    <?php foreach (request_statuses() as $k => [$label]): ?><option value="<?= e($k) ?>" <?= $r['status'] === $k ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
                </select>
            </form>
            <a href="https://wa.me/<?= e(preg_replace('/^0/', '20', preg_replace('/\D+/', '', $r['phone']))) ?>?text=<?= rawurlencode('أهلاً ' . $r['name'] . '، معاك ' . setting('site_name') . ' بخصوص طلب ' . $r['service_type']) ?>" target="_blank" rel="noopener" class="icon-btn icon-btn-wa" title="واتساب"><?= icon('whatsapp', 18) ?></a>
            <form action="<?= url('/admin/requests/' . $r['id'] . '/delete') ?>" method="post" data-confirm="حذف الطلب؟">
                <?= csrf_field() ?><button class="icon-btn icon-btn-danger" title="حذف"><?= icon('trash', 18) ?></button>
            </form>
        </div>
    </article>
    <?php endforeach; ?>
</div>
<?php else: ?>
    <section class="panel"><div class="empty-state"><?= icon('tool', 40) ?><p>مفيش طلبات صيانة هنا.</p></div></section>
<?php endif; ?>
