<?php $cur = $_GET['status'] ?? ''; $all = array_sum($counts); ?>
<div class="pills">
    <a href="<?= url('/admin/orders') ?>" class="<?= $cur === '' ? 'active' : '' ?>">الكل <em><?= $all ?></em></a>
    <?php foreach (order_statuses() as $k => [$label]): ?>
        <a href="<?= url('/admin/orders?status=' . $k) ?>" class="<?= $cur === $k ? 'active' : '' ?>"><?= e($label) ?> <em><?= (int)($counts[$k] ?? 0) ?></em></a>
    <?php endforeach; ?>
</div>

<div class="toolbar">
    <form method="get" class="filters">
        <?php if ($cur): ?><input type="hidden" name="status" value="<?= e($cur) ?>"><?php endif; ?>
        <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="رقم الطلب، اسم العميل أو الموبايل">
        <button class="btn btn-light btn-sm"><?= icon('search', 16) ?> بحث</button>
    </form>
</div>

<section class="panel panel-flush">
    <?php if ($orders): ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>رقم الطلب</th><th>العميل</th><th>المحافظة</th><th>القطع</th><th>الإجمالي</th><th>الدفع</th><th>الحالة</th><th>التاريخ</th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr class="row-link" data-href="<?= url('/admin/orders/' . $o['id']) ?>">
                    <td><a href="<?= url('/admin/orders/' . $o['id']) ?>" dir="ltr"><b><?= e($o['code']) ?></b></a></td>
                    <td><?= e($o['customer_name']) ?><br><small class="muted" dir="ltr"><?= e($o['phone']) ?></small></td>
                    <td><?= e($o['governorate']) ?><br><small class="muted"><?= e($o['city']) ?></small></td>
                    <td><?= (int)$o['items_count'] ?></td>
                    <td><b><?= money($o['total']) ?></b></td>
                    <td><small><?= e(payment_methods()[$o['payment_method']] ?? ($o['payment_method'] === 'wallet' ? 'محفظة' : 'عند الاستلام')) ?></small></td>
                    <td><?= status_badge($o['status'], order_statuses()) ?></td>
                    <td><small><?= e(ar_date($o['created_at'])) ?></small></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pages > 1): ?>
    <nav class="pager">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="<?= url('/admin/orders?' . http_build_query(array_filter(['status' => $cur, 'q' => $_GET['q'] ?? '', 'page' => $i]))) ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </nav>
    <?php endif; ?>
    <?php else: ?>
        <div class="empty-state"><?= icon('bag', 40) ?><p>مفيش طلبات هنا.</p></div>
    <?php endif; ?>
</section>
