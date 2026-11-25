<?php
$max = max(100, max(array_column($chart, 'sum')));
// خطوة "لطيفة" لخطوط الشبكة (1، 2، 2.5، 5 × 10^n) — 4 فترات
$raw = $max / 4;
$mag = 10 ** floor(log10($raw));
$step = $mag * (($raw / $mag) <= 1 ? 1 : (($raw / $mag) <= 2 ? 2 : (($raw / $mag) <= 2.5 ? 2.5 : (($raw / $mag) <= 5 ? 5 : 10))));
$niceMax = $step * 4;
$grid = [$niceMax, $step * 3, $step * 2, $step, 0];
$sum14 = array_sum(array_column($chart, 'sum'));
$cnt14 = array_sum(array_column($chart, 'count'));
$totalStatus = max(1, array_sum($statusCounts));
$fmtShort = fn($n) => $n >= 1000 ? rtrim(rtrim(number_format($n / 1000, 1), '0'), '.') . 'k' : number_format($n);
?>

<?php if (password_verify(config('default_admin')['password'], (string)val('SELECT password_hash FROM admins WHERE id = ?', [admin()['id']]))): ?>
<div class="alert alert-warning">
    <?= icon('shield', 18) ?> انت لسه بتستخدم كلمة المرور الافتراضية. <a href="<?= url('/admin/users') ?>">غيّرها دلوقتي</a> علشان تأمّن لوحة التحكم.
</div>
<?php endif; ?>

<div class="kpis">
    <a href="<?= url('/admin/orders') ?>" class="kpi">
        <span class="kpi-ico"><?= icon('bag', 22) ?></span>
        <div><small>طلبات النهارده</small><b><?= number_format($stats['orders_today']) ?></b></div>
    </a>
    <div class="kpi">
        <span class="kpi-ico kpi-cyan"><?= icon('cash', 22) ?></span>
        <div><small>مبيعات الشهر</small><b><?= money($stats['revenue_month']) ?></b></div>
    </div>
    <a href="<?= url('/admin/orders?status=new') ?>" class="kpi">
        <span class="kpi-ico kpi-warn"><?= icon('clock', 22) ?></span>
        <div><small>طلبات جديدة محتاجة تأكيد</small><b><?= number_format($stats['orders_new']) ?></b></div>
    </a>
    <a href="<?= url('/admin/requests?status=new') ?>" class="kpi">
        <span class="kpi-ico kpi-green"><?= icon('tool', 22) ?></span>
        <div><small>طلبات صيانة جديدة</small><b><?= number_format($stats['requests_new']) ?></b></div>
    </a>
</div>

<div class="mini-kpis">
    <div><small>إجمالي الطلبات</small><b><?= number_format($stats['orders_total']) ?></b></div>
    <div><small>إيرادات الطلبات المسلّمة</small><b><?= money($stats['revenue_total']) ?></b></div>
    <a href="<?= url('/admin/products') ?>"><small>المنتجات</small><b><?= number_format($stats['products']) ?></b></a>
    <a href="<?= url('/admin/products?status=low') ?>"><small>مخزون قليل (5 أو أقل)</small><b class="<?= $stats['low_stock'] ? 'text-danger' : '' ?>"><?= number_format($stats['low_stock']) ?></b></a>
    <a href="<?= url('/admin/messages') ?>"><small>رسائل غير مقروءة</small><b><?= number_format($stats['messages_new']) ?></b></a>
</div>

<div class="grid-2-1">
    <section class="panel">
        <div class="panel-head">
            <div>
                <h2>المبيعات — آخر 14 يوم</h2>
                <p class="muted small"><?= money($sum14) ?> من <?= number_format($cnt14) ?> طلب (بدون الطلبات الملغية)</p>
            </div>
        </div>
        <div class="chart" role="img" aria-label="رسم بياني للمبيعات اليومية خلال آخر 14 يوم">
            <div class="chart-grid">
                <?php foreach ($grid as $g): ?><div><span><?= $fmtShort($g) ?></span></div><?php endforeach; ?>
            </div>
            <div class="chart-bars">
                <?php foreach ($chart as $i => $d): $h = $niceMax ? $d['sum'] / $niceMax * 100 : 0; ?>
                <div class="bar-col" tabindex="0">
                    <div class="bar" style="height: <?= max($d['sum'] > 0 ? 2 : 0, round($h, 2)) ?>%"></div>
                    <div class="tip"><b><?= e($d['label']) ?></b><span><?= money($d['sum']) ?></span><span><?= (int)$d['count'] ?> طلب</span></div>
                    <span class="bar-label"><?= $i % 2 === 1 || $i === 13 ? e($d['label']) : '' ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <details class="table-view">
            <summary>عرض كجدول</summary>
            <table class="table table-sm">
                <thead><tr><th>اليوم</th><th>عدد الطلبات</th><th>المبيعات</th></tr></thead>
                <tbody><?php foreach (array_reverse($chart) as $d): ?><tr><td><?= e($d['date']) ?></td><td><?= (int)$d['count'] ?></td><td><?= money($d['sum']) ?></td></tr><?php endforeach; ?></tbody>
            </table>
        </details>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>حالة الطلبات</h2></div>
        <ul class="status-bars">
            <?php foreach (order_statuses() as $k => [$label, $tone]): $c = $statusCounts[$k] ?? 0; ?>
            <li>
                <a href="<?= url('/admin/orders?status=' . $k) ?>">
                    <span class="sb-label"><?= status_badge($k, order_statuses()) ?></span>
                    <span class="sb-track"><i class="tone-<?= e($tone) ?>" style="width: <?= $c ? max(2, round($c / $totalStatus * 100, 1)) : 0 ?>%"></i></span>
                    <b><?= number_format($c) ?></b>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <div class="quick-actions">
            <a href="<?= url('/admin/products/new') ?>" class="btn btn-primary btn-sm"><?= icon('plus', 16) ?> منتج جديد</a>
            <a href="<?= url('/admin/settings') ?>" class="btn btn-light btn-sm"><?= icon('settings', 16) ?> الإعدادات</a>
        </div>
    </section>
</div>

<div class="grid-2-1">
    <section class="panel">
        <div class="panel-head"><h2>أحدث الطلبات</h2><a href="<?= url('/admin/orders') ?>" class="link">كل الطلبات</a></div>
        <?php if ($recent): ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>رقم الطلب</th><th>العميل</th><th>المحافظة</th><th>الإجمالي</th><th>الحالة</th><th>التاريخ</th></tr></thead>
                <tbody>
                <?php foreach ($recent as $o): ?>
                    <tr class="row-link" data-href="<?= url('/admin/orders/' . $o['id']) ?>">
                        <td><a href="<?= url('/admin/orders/' . $o['id']) ?>" dir="ltr"><b><?= e($o['code']) ?></b></a></td>
                        <td><?= e($o['customer_name']) ?><br><small class="muted" dir="ltr"><?= e($o['phone']) ?></small></td>
                        <td><?= e($o['governorate']) ?></td>
                        <td><b><?= money($o['total']) ?></b></td>
                        <td><?= status_badge($o['status'], order_statuses()) ?></td>
                        <td><small><?= e(ar_date($o['created_at'])) ?></small></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state"><?= icon('bag', 36) ?><p>لسه مفيش طلبات. أول ما عميل يطلب هيظهر هنا.</p></div>
        <?php endif; ?>
    </section>

    <div class="stack">
        <section class="panel">
            <div class="panel-head"><h2>الأكثر مبيعاً</h2></div>
            <ul class="list">
                <?php foreach ($top as $i => $p): ?>
                <li>
                    <span class="rank"><?= $i + 1 ?></span>
                    <img src="<?= e(img($p['image'])) ?>" alt="">
                    <a href="<?= url('/admin/products/' . $p['id'] . '/edit') ?>"><?= e($p['name']) ?></a>
                    <small class="muted"><?= number_format($p['sales_count']) ?> مبيعات</small>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php if ($lowStock): ?>
        <section class="panel">
            <div class="panel-head"><h2><?= icon('box', 18) ?> مخزون قرب يخلص</h2></div>
            <ul class="list">
                <?php foreach ($lowStock as $p): ?>
                <li>
                    <img src="<?= e(img($p['image'])) ?>" alt="">
                    <a href="<?= url('/admin/products/' . $p['id'] . '/edit') ?>"><?= e($p['name']) ?></a>
                    <span class="badge <?= $p['stock'] <= 0 ? 'badge-danger' : 'badge-warning' ?>"><?= (int)$p['stock'] ?> قطعة</span>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>
        <?php if ($requests): ?>
        <section class="panel">
            <div class="panel-head"><h2><?= icon('tool', 18) ?> طلبات صيانة جديدة</h2><a href="<?= url('/admin/requests') ?>" class="link">الكل</a></div>
            <ul class="list">
                <?php foreach ($requests as $r): ?>
                <li><span><b><?= e($r['name']) ?></b> — <?= e($r['service_type']) ?><br><small class="muted"><?= e($r['governorate']) ?> · <span dir="ltr"><?= e($r['phone']) ?></span></small></span></li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>
    </div>
</div>
