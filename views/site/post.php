<div class="container page-wrap">
    <?= view('partials/crumbs', ['items' => ['المدونة' => '/blog', $post['title'] => null]]) ?>
    <div class="content-layout">
        <article class="card page-card">
            <?php if ($post['image']): ?><div class="post-hero"><img src="<?= e(img($post['image'])) ?>" alt=""></div><?php endif; ?>
            <h1><?= e($post['title']) ?></h1>
            <div class="post-meta"><span><?= icon('calendar', 15) ?> <?= e(ar_date($post['created_at'], false)) ?></span><span><?= icon('eye', 15) ?> <?= (int)$post['views'] ?> مشاهدة</span></div>
            <?php if ($post['excerpt']): ?><p class="muted" style="font-size:1.08rem"><?= e($post['excerpt']) ?></p><?php endif; ?>
            <div class="prose"><?= rich_text($post['content']) ?></div>
            <div class="btn-row">
                <a href="<?= e(wa_link($post['title'] . "\n" . url('/blog/' . $post['slug']))) ?>" class="btn btn-wa btn-sm" target="_blank" rel="noopener"><?= icon('whatsapp', 16) ?> شارك على واتساب</a>
                <a href="<?= url('/service-request') ?>" class="btn btn-light btn-sm"><?= icon('tool', 16) ?> اطلب استشارة فني</a>
            </div>
        </article>
        <aside class="stack" style="display:grid;gap:18px">
            <?php if ($more): ?>
            <div class="card side-box">
                <h3>مقالات تانية</h3>
                <div class="side-links"><?php foreach ($more as $m): ?><a href="<?= url('/blog/' . $m['slug']) ?>"><img src="<?= e(img($m['image'])) ?>" alt=""><span><?= e($m['title']) ?></span></a><?php endforeach; ?></div>
            </div>
            <?php endif; ?>
            <?php if ($products): ?>
            <div class="card side-box">
                <h3>منتجات مميزة</h3>
                <div class="side-links"><?php foreach ($products as $pp): ?><a href="<?= url('/product/' . $pp['slug']) ?>"><img src="<?= e(img($pp['image'])) ?>" alt="" style="background:linear-gradient(160deg,#5AD3FF,#1DB8F2)"><span><?= e($pp['name']) ?><br><b style="color:var(--indigo)"><?= money($pp['price']) ?></b></span></a><?php endforeach; ?></div>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</div>
