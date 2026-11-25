<?php /** @var array $post */ $link = url('/blog/' . $post['slug']); ?>
<article class="post-card">
    <a href="<?= $link ?>" class="post-img" tabindex="-1" aria-hidden="true"><img src="<?= e(img($post['image'])) ?>" alt="" loading="lazy"></a>
    <div class="post-body">
        <time datetime="<?= e(substr($post['created_at'], 0, 10)) ?>"><?= e(ar_date($post['created_at'], false)) ?></time>
        <h3><a href="<?= $link ?>"><?= e($post['title']) ?></a></h3>
        <?php if (!empty($post['excerpt'])): ?><p><?= e($post['excerpt']) ?></p><?php endif; ?>
    </div>
</article>
