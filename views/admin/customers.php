<div class="toolbar">
    <form method="get" class="filters">
        <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="ابحث بالاسم أو الموبايل أو الإيميل">
        <button class="btn btn-light btn-sm"><?= icon('search', 16) ?> بحث</button>
    </form>
    <span class="muted small"><?= count($customers) ?> عميل</span>
</div>

<section class="panel panel-flush">
    <?php if ($customers): ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>العميل</th><th>الموبايل</th><th>المحافظة</th><th>الطلبات</th><th>إجمالي المشتريات</th><th>تاريخ التسجيل</th></tr></thead>
            <tbody>
            <?php foreach ($customers as $c): ?>
                <tr class="row-link" data-href="<?= url('/admin/customers/' . $c['id']) ?>">
                    <td><div class="prod-cell"><span class="avatar avatar-sm"><?= e(mb_substr($c['name'], 0, 1)) ?></span><div><a href="<?= url('/admin/customers/' . $c['id']) ?>"><b><?= e($c['name']) ?></b></a><?php if ($c['email']): ?><small class="muted" dir="ltr"><?= e($c['email']) ?></small><?php endif; ?></div></div></td>
                    <td dir="ltr" class="ta-r"><?= e($c['phone']) ?></td>
                    <td><?= e($c['governorate'] ?: '—') ?></td>
                    <td><?= (int)$c['orders_count'] ?></td>
                    <td><b><?= money($c['total_spent']) ?></b></td>
                    <td><small><?= e(ar_date($c['created_at'], false)) ?></small></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="empty-state"><?= icon('user', 40) ?><p>مفيش عملاء مسجلين لسه. أول ما عميل يعمل حساب من الموقع هيظهر هنا.</p></div>
    <?php endif; ?>
</section>
