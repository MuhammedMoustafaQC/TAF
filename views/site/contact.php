<?php $f = fn($k, $d = '') => e($form[$k] ?? $d); ?>
<div class="page-hero page-hero-sm">
    <div class="container">
        <nav class="breadcrumb"><a href="<?= url('/') ?>">الرئيسية</a> <span>/</span> <b>تواصل معنا</b></nav>
        <h1>تواصل معنا</h1>
        <p>عندك سؤال أو استفسار؟ إحنا موجودين علشانك.</p>
    </div>
</div>

<section class="section section-tight">
    <div class="container two-col">
        <aside class="info-stack">
            <div class="contact-cards">
                <a href="<?= e(tel_link(setting('phone'))) ?>" class="card contact-card"><span class="f-ico"><?= icon('phone', 22) ?></span><div><small>اتصل بينا</small><b dir="ltr"><?= e(setting('phone')) ?></b></div></a>
                <a href="<?= e(wa_link()) ?>" class="card contact-card" target="_blank" rel="noopener"><span class="f-ico f-ico-wa"><?= icon('whatsapp', 22) ?></span><div><small>واتساب</small><b>ابعتلنا رسالة</b></div></a>
                <?php if ($em = setting('email')): ?><a href="mailto:<?= e($em) ?>" class="card contact-card"><span class="f-ico"><?= icon('mail', 22) ?></span><div><small>البريد الإلكتروني</small><b><?= e($em) ?></b></div></a><?php endif; ?>
                <?php if ($ad = setting('address')): ?><div class="card contact-card"><span class="f-ico"><?= icon('pin', 22) ?></span><div><small>العنوان</small><b><?= e($ad) ?></b></div></div><?php endif; ?>
                <?php if ($wh = setting('working_hours')): ?><div class="card contact-card"><span class="f-ico"><?= icon('clock', 22) ?></span><div><small>مواعيد العمل</small><b><?= e($wh) ?></b></div></div><?php endif; ?>
            </div>
        </aside>

        <form method="post" class="card form-card" novalidate>
            <?= csrf_field() ?>
            <h3>ابعتلنا رسالة</h3>
            <div class="form-grid">
                <label class="field"><span>الاسم *</span><input type="text" name="name" value="<?= $f('name') ?>" required class="<?= inv($errors, 'name') ?>"><?= err($errors, 'name') ?></label>
                <label class="field"><span>رقم الموبايل</span><input type="tel" name="phone" value="<?= $f('phone') ?>" dir="ltr" placeholder="01xxxxxxxxx" inputmode="tel" class="<?= inv($errors, 'phone') ?>"><?= err($errors, 'phone') ?></label>
                <label class="field"><span>البريد الإلكتروني</span><input type="email" name="email" value="<?= $f('email') ?>" dir="ltr" class="<?= inv($errors, 'email') ?>"><?= err($errors, 'email') ?></label>
                <label class="field"><span>الموضوع</span><input type="text" name="subject" value="<?= $f('subject') ?>"></label>
                <label class="field span-2"><span>رسالتك *</span><textarea name="message" rows="5" required class="<?= inv($errors, 'message') ?>"><?= $f('message') ?></textarea><?= err($errors, 'message') ?></label>
            </div>
            <button class="btn btn-primary btn-lg btn-block"><?= icon('message', 20) ?> إرسال</button>
        </form>
    </div>
</section>
