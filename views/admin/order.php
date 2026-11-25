<?php $waText = 'أهلاً ' . $o['customer_name'] . '، معاك ' . setting('site_name') . ' بخصوص طلبك رقم ' . $o['code']; ?>
<div class="toolbar no-print">
    <a href="<?= url('/admin/orders') ?>" class="btn btn-light btn-sm"><?= icon('chevron-r', 16) ?> كل الطلبات</a>
    <div class="btn-row">
        <a href="https://wa.me/<?= e(preg_replace('/^0/', '20', preg_replace('/\D+/', '', $o['phone']))) ?>?text=<?= rawurlencode($waText) ?>" target="_blank" rel="noopener" class="btn btn-wa btn-sm"><?= icon('whatsapp', 16) ?> واتساب العميل</a>
        <a href="<?= e(tel_link($o['phone'])) ?>" class="btn btn-light btn-sm"><?= icon('phone', 16) ?> اتصال</a>
        <button type="button" class="btn btn-light btn-sm" onclick="window.print()"><?= icon('printer', 16) ?> طباعة الفاتورة</button>
    </div>
</div>

<div class="grid-2-1">
    <div class="stack">
        <section class="panel invoice">
            <div class="invoice-head">
                <div class="invoice-brand">
                    <img src="<?= asset('assets/img/logo-mark.png') ?>" alt="" width="34" height="52">
                    <div><b><?= e(setting('site_name')) ?></b><small><?= e(setting('site_tagline')) ?> · <span dir="ltr"><?= e(setting('phone')) ?></span></small></div>
                </div>
                <div class="invoice-meta">
                    <b dir="ltr"><?= e($o['code']) ?></b>
                    <small><?= e(ar_date($o['created_at'])) ?></small>
                    <?= status_badge($o['status'], order_statuses()) ?>
                </div>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>المنتج</th><th>السعر</th><th>الكمية</th><th>الإجمالي</th></tr></thead>
                    <tbody>
                    <?php foreach ($o['items'] as $it): ?>
                        <tr>
                            <td><div class="prod-cell"><img src="<?= e(img($it['image'])) ?>" alt="" class="no-print"><div><?php if ($it['slug']): ?><a href="<?= url('/product/' . $it['slug']) ?>" target="_blank"><?= e($it['product_name']) ?></a><?php else: ?><?= e($it['product_name']) ?><?php endif; ?></div></div></td>
                            <td><?= money($it['price']) ?></td>
                            <td>× <?= (int)$it['qty'] ?></td>
                            <td><b><?= money($it['total']) ?></b></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3">إجمالي المنتجات</td><td><?= money($o['subtotal']) ?></td></tr>
                        <tr><td colspan="3">الشحن</td><td><?= $o['shipping'] > 0 ? money($o['shipping']) : 'مجاني' ?></td></tr>
                        <tr class="total-row"><td colspan="3">الإجمالي المطلوب</td><td><?= money($o['total']) ?></td></tr>
                    </tfoot>
                </table>
            </div>
        </section>
        <?php if ($o['notes']): ?>
        <section class="panel"><h2>ملاحظات العميل</h2><p><?= nl2br(e($o['notes'])) ?></p></section>
        <?php endif; ?>
    </div>

    <div class="stack">
        <section class="panel">
            <h2>بيانات العميل</h2>
            <dl class="details">
                <dt>الاسم</dt><dd><?= e($o['customer_name']) ?></dd>
                <dt>الموبايل</dt><dd><a href="<?= e(tel_link($o['phone'])) ?>" dir="ltr"><?= e($o['phone']) ?></a><?php if ($history): ?> <small class="badge badge-info">عميل متكرر (<?= $history ?> طلبات سابقة)</small><?php endif; ?></dd>
                <?php if ($o['phone2']): ?><dt>موبايل إضافي</dt><dd><a href="<?= e(tel_link($o['phone2'])) ?>" dir="ltr"><?= e($o['phone2']) ?></a></dd><?php endif; ?>
                <dt>المحافظة</dt><dd><?= e($o['governorate']) ?> — <?= e($o['city']) ?></dd>
                <dt>العنوان</dt><dd><?= e($o['address']) ?></dd>
                <dt>الدفع</dt><dd><?= $o['payment_method'] === 'wallet' ? 'فودافون كاش / إنستاباي' : 'الدفع عند الاستلام' ?></dd>
            </dl>
        </section>
        <section class="panel no-print">
            <h2>تحديث حالة الطلب</h2>
            <form action="<?= url('/admin/orders/' . $o['id'] . '/status') ?>" method="post" class="form">
                <?= csrf_field() ?>
                <div class="status-pick">
                    <?php foreach (order_statuses() as $k => [$label, $tone]): ?>
                        <label class="tone-<?= e($tone) ?>"><input type="radio" name="status" value="<?= e($k) ?>" <?= $o['status'] === $k ? 'checked' : '' ?>><span><?= e($label) ?></span></label>
                    <?php endforeach; ?>
                </div>
                <label class="field"><span>ملاحظات داخلية</span><textarea name="admin_notes" rows="3" placeholder="مثلاً: اتأكد مع العميل، ميعاد التركيب..."><?= e($o['admin_notes']) ?></textarea></label>
                <button class="btn btn-primary btn-block"><?= icon('check', 18) ?> حفظ</button>
                <p class="muted small">عند إلغاء الطلب الكمية بترجع للمخزون تلقائياً.</p>
            </form>
        </section>
        <form action="<?= url('/admin/orders/' . $o['id'] . '/delete') ?>" method="post" data-confirm="حذف الطلب نهائياً؟ (لو عايز ترجع الكمية للمخزون غيّر الحالة لملغي الأول)" class="no-print">
            <?= csrf_field() ?>
            <button class="btn btn-danger-outline btn-sm btn-block"><?= icon('trash', 16) ?> حذف الطلب</button>
        </form>
    </div>
</div>
