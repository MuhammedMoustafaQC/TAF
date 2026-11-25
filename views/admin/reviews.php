<?php $cur = $_GET['status'] ?? ''; ?>
<div class="pills">
    <a href="<?= url('/admin/reviews') ?>" class="<?= $cur === '' ? 'active' : '' ?>">الكل <em><?= $counts['all'] ?></em></a>
    <a href="<?= url('/admin/reviews?status=pending') ?>" class="<?= $cur === 'pending' ? 'active' : '' ?>">منتظرة الموافقة <em><?= $counts['pending'] ?></em></a>
    <a href="<?= url('/admin/reviews?status=approved') ?>" class="<?= $cur === 'approved' ? 'active' : '' ?>">منشورة <em><?= $counts['approved'] ?></em></a>
</div>

<?php if ($reviews): ?>
<div class="stack">
    <?php foreach ($reviews as $r): ?>
    <article class="panel review <?= $r['is_approved'] ? 'is-read' : 'is-new' ?>">
        <div class="req-head">
            <div class="review-who">
                <span class="avatar avatar-sm"><?= e(mb_substr($r['name'], 0, 1)) ?></span>
                <div>
                    <b><?= e($r['name']) ?></b>
                    <small class="muted"><?= e(ar_date($r['created_at'])) ?></small>
                </div>
                <span class="stars-txt" aria-label="<?= (int)$r['rating'] ?> من 5"><?= str_repeat('★', (int)$r['rating']) ?><span><?= str_repeat('★', 5 - (int)$r['rating']) ?></span></span>
                <?= $r['is_approved'] ? '<span class="badge badge-success">منشور</span>' : '<span class="badge badge-warning">منتظر الموافقة</span>' ?>
            </div>
            <div class="actions">
                <form action="<?= url('/admin/reviews/' . $r['id'] . '/approve') ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="btn <?= $r['is_approved'] ? 'btn-light' : 'btn-primary' ?> btn-sm"><?= $r['is_approved'] ? 'إخفاء' : icon('check', 16) . ' نشر' ?></button>
                </form>
                <form action="<?= url('/admin/reviews/' . $r['id'] . '/delete') ?>" method="post" data-confirm="حذف التقييم؟">
                    <?= csrf_field() ?><button class="icon-btn icon-btn-danger" title="حذف"><?= icon('trash', 18) ?></button>
                </form>
            </div>
        </div>
        <?php if ($r['product_name']): ?>
        <a class="review-product" href="<?= url('/product/' . $r['product_slug']) ?>" target="_blank"><img src="<?= e(img($r['product_image'])) ?>" alt=""> <?= e($r['product_name']) ?></a>
        <?php endif; ?>
        <?php if ($r['comment']): ?><p class="msg-body"><?= nl2br(e($r['comment'])) ?></p><?php endif; ?>
    </article>
    <?php endforeach; ?>
</div>
<?php else: ?>
    <section class="panel"><div class="empty-state"><?= icon('star', 40) ?><p>مفيش تقييمات هنا.</p></div></section>
<?php endif; ?>
