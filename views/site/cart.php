<div class="page-hero page-hero-sm">
    <div class="container">
        <nav class="breadcrumb"><a href="<?= url('/') ?>">الرئيسية</a> <span>/</span> <b>سلة المشتريات</b></nav>
        <h1>سلة المشتريات</h1>
    </div>
</div>

<section class="section section-tight">
    <div class="container">
        <?php if (!$cart['items']): ?>
            <div class="empty">
                <?= icon('cart', 56) ?>
                <h3>السلة فاضية</h3>
                <p>لسه مضفتش أي منتجات. تصفح الفلاتر والعروض واختار اللي يناسبك.</p>
                <a href="<?= url('/shop') ?>" class="btn btn-primary">ابدأ التسوق</a>
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
                        <button type="button" data-step="1" aria-label="زيادة"><?= icon('plus', 16) ?></button>
                        <input type="number" name="qty[<?= (int)$p['id'] ?>]" value="<?= (int)$it['qty'] ?>" min="0" max="<?= (int)$p['stock'] ?>" aria-label="الكمية" data-autosubmit-qty inputmode="numeric">
                        <button type="button" data-step="-1" aria-label="تقليل"><?= icon('minus', 16) ?></button>
                    </div>
                    <b class="cart-total"><?= money($it['total']) ?></b>
                    <button type="submit" name="remove" value="<?= (int)$p['id'] ?>" class="icon-btn icon-btn-danger" aria-label="حذف" title="حذف"><?= icon('trash', 18) ?></button>
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
                <?php if ($cart['free_min'] > 0 && $cart['shipping'] > 0): $left = $cart['free_min'] - $cart['subtotal']; ?>
                    <div class="ship-hint">
                        <span>فاضلك <?= money($left) ?> وتاخد شحن مجاني</span>
                        <div class="bar"><i style="width: <?= min(100, round($cart['subtotal'] / $cart['free_min'] * 100)) ?>%"></i></div>
                    </div>
                <?php endif; ?>
                <div class="sum-row sum-total"><span>الإجمالي</span><b><?= money($cart['total']) ?></b></div>
                <a href="<?= url('/checkout') ?>" class="btn btn-primary btn-lg btn-block">إتمام الطلب <?= icon('chevron-l', 18) ?></a>
                <p class="muted small center"><?= icon('shield', 14) ?> الدفع عند الاستلام — بياناتك في أمان</p>
            </aside>
        </div>
        <?php endif; ?>
    </div>
</section>
