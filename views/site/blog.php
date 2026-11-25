<div class="container page-wrap">
    <?= view('partials/crumbs', ['items' => ['المدونة' => null]]) ?>
    <div class="sec-head"><h1 class="sec-title" style="font-size:clamp(1.4rem,3vw,2rem)">المدونة والأخبار</h1></div>
    <?php if ($posts): ?>
        <div class="posts-grid" style="grid-template-columns:repeat(auto-fill,minmax(250px,1fr))">
            <?php foreach ($posts as $post) echo view('partials/post-card', ['post' => $post]); ?>
        </div>
        <?php if ($pages > 1): ?>
        <nav class="pagination"><?php for ($i = 1; $i <= $pages; $i++): ?><a href="<?= url('/blog' . ($i > 1 ? '?page=' . $i : '')) ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a><?php endfor; ?></nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="card empty"><span class="empty-ico"><?= icon('book', 34) ?></span><h3>لسه مفيش مقالات</h3></div>
    <?php endif; ?>
</div>
