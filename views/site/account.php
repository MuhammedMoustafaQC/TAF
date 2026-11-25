<?php $f = fn($k) => e($_POST[$k] ?? ($c[$k] ?? '')); ?>
<div class="container page-wrap">
    <?= view('partials/crumbs', ['items' => ['حسابي' => null]]) ?>
    <div class="account-layout">
        <aside class="card account-nav">
            <div class="who"><span class="avatar"><?= e(mb_substr($c['name'], 0, 1)) ?></span><span><b><?= e($c['name']) ?></b><small dir="ltr"><?= e($c['phone']) ?></small></span></div>
            <a href="<?= url('/account') ?>" class="<?= $tab === 'orders' ? 'active' : '' ?>"><?= icon('bag', 18) ?> طلباتي</a>
            <a href="<?= url('/account?tab=profile') ?>" class="<?= $tab === 'profile' ? 'active' : '' ?>"><?= icon('settings', 18) ?> إعدادات الحساب</a>
            <a href="<?= url('/wishlist') ?>"><?= icon('heart', 18) ?> قائمة المفضلة</a>
            <a href="<?= url('/cart') ?>"><?= icon('cart', 18) ?> عربة التسوق</a>
            <form action="<?= url('/account/logout') ?>" method="post"><?= csrf_field() ?><button><?= icon('logout', 18) ?> تسجيل الخروج</button></form>
        </aside>
        <div>
            <?php if ($tab === 'profile'): ?>
            <form method="post" class="card form-card" novalidate>
                <?= csrf_field() ?>
                <h2>إعدادات الحساب</h2>
                <div class="form-grid">
                    <label class="field"><span>الاسم بالكامل</span><input type="text" name="name" value="<?= $f('name') ?>" required class="<?= inv($errors, 'name') ?>"><?= err($errors, 'name') ?></label>
                    <label class="field"><span>رقم الموبايل</span><input type="tel" value="<?= e($c['phone']) ?>" dir="ltr" disabled></label>
                    <label class="field"><span>البريد الإلكتروني</span><input type="email" name="email" value="<?= $f('email') ?>" dir="ltr" class="<?= inv($errors, 'email') ?>"><?= err($errors, 'email') ?></label>
                    <label class="field"><span>المحافظة</span>
                        <select name="governorate"><option value="">اختار المحافظة</option><?php foreach (governorates() as $g): ?><option <?= ($_POST['governorate'] ?? $c['governorate']) === $g ? 'selected' : '' ?>><?= e($g) ?></option><?php endforeach; ?></select>
                    </label>
                    <label class="field"><span>المدينة / المنطقة</span><input type="text" name="city" value="<?= $f('city') ?>"></label>
                    <label class="field span-2"><span>العنوان بالتفصيل</span><textarea name="address" rows="2"><?= $f('address') ?></textarea></label>
                </div>
                <h3><?= icon('lock', 18) ?> تغيير كلمة المرور <small class="muted">(اختياري)</small></h3>
                <div class="form-grid">
                    <label class="field"><span>كلمة المرور الحالية</span><input type="password" name="current_password" autocomplete="current-password" dir="ltr" class="<?= inv($errors, 'current_password') ?>"><?= err($errors, 'current_password') ?></label>
                    <label class="field"><span>كلمة المرور الجديدة</span><input type="password" name="new_password" autocomplete="new-password" dir="ltr" class="<?= inv($errors, 'new_password') ?>"><?= err($errors, 'new_password') ?></label>
                </div>
                <button class="btn btn-primary"><?= icon('check', 18) ?> حفظ</button>
            </form>
            <?php else: ?>
            <h2>طلباتي</h2>
            <?php if ($orders): ?>
            <div class="order-list">
                <?php foreach ($orders as $o): ?>
                <div class="card order-item">
                    <div>
                        <b dir="ltr"><?= e($o['code']) ?></b> <?= status_badge($o['status'], order_statuses()) ?>
                        <div class="oi-meta"><span><?= icon('calendar', 14) ?> <?= e(ar_date($o['created_at'], false)) ?></span><span><?= icon('box', 14) ?> <?= (int)$o['items_count'] ?> قطعة</span><span><?= icon('cash', 14) ?> <?= money($o['total']) ?></span></div>
                    </div>
                    <a href="<?= url('/track?code=' . urlencode($o['code']) . '&phone=' . urlencode($o['phone'])) ?>" class="btn btn-light btn-sm">تفاصيل</a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <div class="card empty"><span class="empty-ico"><?= icon('bag', 34) ?></span><h3>لسه معملتش أي طلبات</h3><a href="<?= url('/shop') ?>" class="btn btn-primary">ابدأ التسوق</a></div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
