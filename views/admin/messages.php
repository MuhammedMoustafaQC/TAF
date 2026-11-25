<?php if ($messages): ?>
<div class="stack">
    <?php foreach ($messages as $m): ?>
    <article class="panel msg <?= $m['is_read'] ? 'is-read' : 'is-new' ?>">
        <div class="req-head">
            <div>
                <b><?= e($m['name']) ?></b>
                <?php if (!$m['is_read']): ?><span class="badge badge-info">جديدة</span><?php endif; ?>
                <small class="muted"><?= e(ar_date($m['created_at'])) ?></small>
            </div>
            <div class="actions">
                <form action="<?= url('/admin/messages/' . $m['id'] . '/read') ?>" method="post">
                    <?= csrf_field() ?><button class="btn btn-light btn-sm"><?= $m['is_read'] ? 'تعليم كغير مقروءة' : 'تعليم كمقروءة' ?></button>
                </form>
                <form action="<?= url('/admin/messages/' . $m['id'] . '/delete') ?>" method="post" data-confirm="حذف الرسالة؟">
                    <?= csrf_field() ?><button class="icon-btn icon-btn-danger" title="حذف"><?= icon('trash', 18) ?></button>
                </form>
            </div>
        </div>
        <div class="msg-meta">
            <?php if ($m['phone']): ?><a href="<?= e(tel_link($m['phone'])) ?>" dir="ltr"><?= icon('phone', 14) ?> <?= e($m['phone']) ?></a><?php endif; ?>
            <?php if ($m['email']): ?><a href="mailto:<?= e($m['email']) ?>"><?= icon('mail', 14) ?> <?= e($m['email']) ?></a><?php endif; ?>
            <?php if ($m['subject']): ?><span><?= icon('tag', 14) ?> <?= e($m['subject']) ?></span><?php endif; ?>
        </div>
        <p class="msg-body"><?= nl2br(e($m['message'])) ?></p>
    </article>
    <?php endforeach; ?>
</div>
<?php else: ?>
    <section class="panel"><div class="empty-state"><?= icon('message', 40) ?><p>مفيش رسائل لسه.</p></div></section>
<?php endif; ?>
