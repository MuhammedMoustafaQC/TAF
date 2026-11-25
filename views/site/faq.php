<div class="page-hero page-hero-sm">
    <div class="container">
        <nav class="breadcrumb"><a href="<?= url('/') ?>">الرئيسية</a> <span>/</span> <b>الأسئلة الشائعة</b></nav>
        <h1>الأسئلة الشائعة</h1>
        <p>إجابات لأكتر الأسئلة اللي بتوصلنا عن الفلاتر والتركيب والصيانة.</p>
    </div>
</div>

<section class="section section-tight">
    <div class="container narrow">
        <div class="faq-list">
            <?php foreach ($faqs as $i => $f): ?>
            <details class="faq" <?= $i === 0 ? 'open' : '' ?>>
                <summary><?= e($f['question']) ?> <?= icon('chevron-d', 18) ?></summary>
                <div class="faq-a"><?= nl2p($f['answer']) ?></div>
            </details>
            <?php endforeach; ?>
        </div>
        <div class="card info-card info-card-brand center mt">
            <h3>لسه عندك سؤال؟</h3>
            <p>فريقنا جاهز يرد عليك في أي وقت خلال مواعيد العمل.</p>
            <div class="btn-row center">
                <a href="<?= e(wa_link('عندي سؤال')) ?>" class="btn btn-wa" target="_blank" rel="noopener"><?= icon('whatsapp', 18) ?> واتساب</a>
                <a href="<?= url('/contact') ?>" class="btn btn-white">تواصل معنا</a>
            </div>
        </div>
    </div>
</section>
