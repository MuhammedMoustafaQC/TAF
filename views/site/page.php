<div class="container page-wrap narrow">
    <?= view('partials/crumbs', ['items' => [$page['title'] => null]]) ?>
    <article class="card page-card">
        <h1 style="color:var(--cyan-700);font-weight:700"><?= e($page['title']) ?></h1>
        <div class="prose"><?= rich_text($page['content']) ?></div>
        <?php if ($page['slug'] === 'services'): ?>
            <div class="btn-row"><a href="<?= url('/service-request') ?>" class="btn btn-primary"><?= icon('tool', 18) ?> اطلب خدمة</a><a href="<?= e(wa_link('عايز أستفسر عن خدماتكم')) ?>" class="btn btn-wa" target="_blank" rel="noopener"><?= icon('whatsapp', 18) ?> واتساب</a></div>
        <?php endif; ?>
        <p class="muted small" style="margin-top:24px">آخر تحديث: <?= e(ar_date($page['updated_at'], false)) ?></p>
    </article>
    <div class="center" style="margin-top:18px"><a href="<?= url('/') ?>" class="btn btn-outline btn-sm">العودة للرئيسية</a></div>
</div>
