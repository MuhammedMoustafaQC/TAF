<div class="container page-wrap narrow">
    <?= view('partials/crumbs', ['items' => ['الأسئلة الشائعة' => null]]) ?>
    <h1 class="page-title">الأسئلة الشائعة</h1>
    <?php foreach ($faqs as $i => $f): ?>
        <details class="acc" <?= $i === 0 ? 'open' : '' ?>><summary><?= e($f['question']) ?> <?= icon('chevron-d', 18) ?></summary><div class="acc-a"><?= nl2p($f['answer']) ?></div></details>
    <?php endforeach; ?>
    <div class="card form-card center" style="margin-top:20px">
        <h3 style="justify-content:center">لسه عندك سؤال؟</h3>
        <p class="muted">فريقنا جاهز يرد عليك خلال مواعيد العمل.</p>
        <div class="btn-row center"><a href="<?= e(wa_link('عندي سؤال')) ?>" class="btn btn-wa" target="_blank" rel="noopener"><?= icon('whatsapp', 18) ?> واتساب</a><a href="<?= url('/contact') ?>" class="btn btn-outline">اتصل بنا</a></div>
    </div>
</div>
