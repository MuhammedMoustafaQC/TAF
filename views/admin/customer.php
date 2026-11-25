<div class="toolbar">
    <a href="<?= url('/admin/customers') ?>" class="btn btn-light btn-sm"><?= icon('chevron-r', 16) ?> كل العملاء</a>
    <div class="btn-row">
        <a href="<?= e(admin_wa_link($c['phone'], 'أهلاً ' . $c['name'] . '، معاك ' . setting('site_name'))) ?>" target="_blank" rel="noopener" class="btn btn-wa btn-sm"><?= icon('whatsapp', 16) ?> واتساب</a>
        <a href="<?= e(tel_link($c['phone'])) ?>" class="btn btn-light btn-sm"><?= icon('phone', 16) ?> اتصال</a>
    </div>
</div>

<div class="grid-2-1">
    <div class="stack">
        <section class="panel panel-flush">
            <div class="panel-head pad"><h2>طلبات العميل</h2><span class="muted small"><?= count($orders) ?> طلب · <?= money($spent) ?></span></div>
            <?php if ($orders): ?>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>رقم الطلب</th><th>القطع</th><th>الإجمالي</th><th>الحالة</th><th>التاريخ</th></tr></thead>
                    <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr class="row-link" data-href="<?= url('/admin/orders/' . $o['id']) ?>">
                            <td><a href="<?= url('/admin/orders/' . $o['id']) ?>" dir="ltr"><b><?= e($o['code']) ?></b></a></td>
                            <td><?= (int)$o['items_count'] ?></td>
                            <td><b><?= money($o['total']) ?></b></td>
                            <td><?= status_badge($o['status'], order_statuses()) ?></td>
                            <td><small><?= e(ar_date($o['created_at'])) ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="empty-state"><?= icon('bag', 36) ?><p>العميل ده لسه ماطلبش حاجة.</p></div>
            <?php endif; ?>
        </section>
        <?php if ($wishlist): ?>
        <section class="panel">
            <h2><?= icon('star', 18) ?> المفضلة (<?= count($wishlist) ?>)</h2>
            <ul class="list">
                <?php foreach ($wishlist as $w): ?>
                <li><img src="<?= e(img($w['image'])) ?>" alt=""><a href="<?= url('/admin/products/' . $w['id'] . '/edit') ?>"><?= e($w['name']) ?></a><small class="muted"><?= money($w['price']) ?></small></li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>
    </div>

    <section class="panel">
        <div class="customer-head">
            <span class="avatar avatar-lg"><?= e(mb_substr($c['name'], 0, 1)) ?></span>
            <div><b><?= e($c['name']) ?></b><small class="muted">عميل من <?= e(ar_date($c['created_at'], false)) ?></small></div>
        </div>
        <dl class="details">
            <dt>الموبايل</dt><dd><a href="<?= e(tel_link($c['phone'])) ?>" dir="ltr"><?= e($c['phone']) ?></a></dd>
            <dt>الإيميل</dt><dd><?= $c['email'] ? '<a href="mailto:' . e($c['email']) . '" dir="ltr">' . e($c['email']) . '</a>' : '—' ?></dd>
            <dt>المحافظة</dt><dd><?= e($c['governorate'] ?: '—') ?><?= $c['city'] ? ' — ' . e($c['city']) : '' ?></dd>
            <dt>العنوان</dt><dd><?= e($c['address'] ?: '—') ?></dd>
            <dt>آخر دخول</dt><dd><?= e($c['last_login'] ? ar_date($c['last_login']) : '—') ?></dd>
        </dl>
    </section>
</div>
