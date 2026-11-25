<?php $cur = $_GET['status'] ?? ''; ?>
<div class="pills">
    <a href="<?= url('/admin/installments') ?>" class="<?= $cur === '' ? 'active' : '' ?>">الكل <em><?= array_sum($counts) ?></em></a>
    <?php foreach (installment_statuses() as $k => [$label]): ?>
        <a href="<?= url('/admin/installments?status=' . $k) ?>" class="<?= $cur === $k ? 'active' : '' ?>"><?= e($label) ?> <em><?= (int)($counts[$k] ?? 0) ?></em></a>
    <?php endforeach; ?>
</div>

<?php if ($requests): ?>
<div class="cards-grid">
    <?php foreach ($requests as $r): ?>
    <article class="panel req-card">
        <div class="req-head">
            <div><b><?= e($r['name']) ?></b><small class="muted"><?= e(ar_date($r['created_at'])) ?></small></div>
            <?= status_badge($r['status'], installment_statuses()) ?>
        </div>
        <dl class="details details-sm">
            <dt>المنتج</dt><dd><?php if ($r['product_slug']): ?><a href="<?= url('/product/' . $r['product_slug']) ?>" target="_blank"><b><?= e($r['product_name']) ?></b></a><?php else: ?><b><?= e($r['product_name'] ?: '—') ?></b><?php endif; ?></dd>
            <?php if ($r['product_price']): ?><dt>السعر</dt><dd><?= money($r['product_price']) ?></dd><?php endif; ?>
            <?php if ($r['months']): ?><dt>مدة التقسيط</dt><dd><?= (int)$r['months'] ?> شهور<?php if ($r['product_price']): ?> <small class="muted">(≈ <?= money(ceil($r['product_price'] / $r['months'])) ?> شهرياً)</small><?php endif; ?></dd><?php endif; ?>
            <dt>الموبايل</dt><dd><a href="<?= e(tel_link($r['phone'])) ?>" dir="ltr"><?= e($r['phone']) ?></a></dd>
            <dt>المحافظة</dt><dd><?= e($r['governorate'] ?: '—') ?></dd>
            <?php if ($r['job']): ?><dt>الوظيفة</dt><dd><?= e($r['job']) ?></dd><?php endif; ?>
            <?php if ($r['notes']): ?><dt>ملاحظات</dt><dd><?= nl2br(e($r['notes'])) ?></dd><?php endif; ?>
        </dl>
        <div class="req-actions">
            <form action="<?= url('/admin/installments/' . $r['id'] . '/status') ?>" method="post" class="inline-form">
                <?= csrf_field() ?>
                <select name="status" data-autosubmit aria-label="حالة الطلب">
                    <?php foreach (installment_statuses() as $k => [$label]): ?><option value="<?= e($k) ?>" <?= $r['status'] === $k ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
                </select>
            </form>
            <a href="<?= e(admin_wa_link($r['phone'], 'أهلاً ' . $r['name'] . '، معاك ' . setting('site_name') . ' بخصوص طلب التقسيط' . ($r['product_name'] ? ' لـ ' . $r['product_name'] : ''))) ?>" target="_blank" rel="noopener" class="icon-btn icon-btn-wa" title="واتساب"><?= icon('whatsapp', 18) ?></a>
            <form action="<?= url('/admin/installments/' . $r['id'] . '/delete') ?>" method="post" data-confirm="حذف طلب التقسيط؟">
                <?= csrf_field() ?><button class="icon-btn icon-btn-danger" title="حذف"><?= icon('trash', 18) ?></button>
            </form>
        </div>
    </article>
    <?php endforeach; ?>
</div>
<?php else: ?>
    <section class="panel"><div class="empty-state"><?= icon('cash', 40) ?><p>مفيش طلبات تقسيط هنا.</p></div></section>
<?php endif; ?>
