<div class="container page-wrap">
    <?= view('partials/steps', ['step' => 1]) ?>
    <?php if (!$cart['items']): ?>
        <div class="card empty narrow" style="margin-inline:auto">
            <span class="empty-ico"><?= icon('cart', 34) ?></span>
            <h2>سلة التسوق الخاصة بك فارغة!</h2>
            <p>يبدو أنك لم تقم بإضافة أي منتجات للسلة حتى الآن. ابحث عن منتجاتك المفضلة واستمتع بتجربة تسوق فريدة.</p>
            <a href="<?= url('/shop') ?>" class="btn btn-primary">ابدأ التسوق الآن</a>
        </div>
    <?php else: ?>
    <div class="cart-layout">
        <form action="<?= url('/cart/update') ?>" method="post" class="cart-items card">
            <?= csrf_field() ?>
            <?php foreach ($cart['items'] as $it): $p = $it['product']; ?>
            <div class="cart-row">
                <a href="<?= url('/product/' . $p['slug']) ?>" class="cart-img"><img src="<?= e(img($p['image'])) ?>" alt="" loading="lazy"></a>
                <div class="cart-name">
                    <a href="<?= url('/product/' . $p['slug']) ?>"><?= e($p['name']) ?></a>
                    <small><?= money($p['price']) ?> للقطعة</small>
                </div>
                <div class="qty qty-sm" data-qty>
                    <button type="button" data-step="1" aria-label="زيادة"><?= icon('plus', 15) ?></button>
                    <input type="number" name="qty[<?= (int)$p['id'] ?>]" value="<?= (int)$it['qty'] ?>" min="0" max="<?= (int)$p['stock'] ?>" aria-label="الكمية" data-autosubmit-qty inputmode="numeric">
                    <button type="button" data-step="-1" aria-label="تقليل"><?= icon('minus', 15) ?></button>
                </div>
                <b class="cart-total"><?= money($it['total']) ?></b>
                <button type="submit" name="remove" value="<?= (int)$p['id'] ?>" class="icon-btn icon-del" aria-label="حذف" title="حذف"><?= icon('trash', 18) ?></button>
            </div>
            <?php endforeach; ?>
            <div class="cart-actions">
                <a href="<?= url('/shop') ?>" class="btn btn-outline btn-sm"><?= icon('chevron-r', 16) ?> كمّل تسوق</a>
                <button type="submit" class="btn btn-light btn-sm">تحديث السلة</button>
            </div>
        </form>
        <aside class="summary card">
            <h3>ملخص الطلب</h3>
            <div class="sum-row"><span>إجمالي المنتجات</span><b><?= money($cart['subtotal']) ?></b></div>
            <div class="sum-row"><span>الشحن</span><b><?= $cart['shipping'] > 0 ? money($cart['shipping']) : '<span class="ok">مجاني</span>' ?></b></div>
            <?php if ($cart['free_min'] > 0 && $cart['shipping'] > 0): ?>
                <div class="ship-hint">فاضلك <?= money($cart['free_min'] - $cart['subtotal']) ?> وتاخد شحن مجاني<div class="bar"><i style="width: <?= min(100, round($cart['subtotal'] / $cart['free_min'] * 100)) ?>%"></i></div></div>
            <?php endif; ?>
            <div class="sum-row sum-total"><span>الإجمالي</span><b><?= money($cart['total']) ?></b></div>
            <a href="<?= url('/checkout') ?>" class="btn btn-primary btn-lg btn-block">متابعة الشراء <?= icon('chevron-l', 18) ?></a>
            <p class="muted small center" style="margin:10px 0 0"><?= icon('lock', 14) ?> الدفع عند الاستلام — بياناتك في أمان</p>
        </aside>
    </div>
    <?php endif; ?>
</div>
