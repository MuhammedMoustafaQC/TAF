<?php /** @var array $post */ $link = url('/blog/' . $post['slug']); ?>
<article class="post-card">
    <a href="<?= $link ?>" class="post-img"><img src="<?= e(img($post['image'])) ?>" alt="" loading="lazy"></a>
    <div class="post-body">
        <h3><a href="<?= $link ?>"><?= e($post['title']) ?></a></h3>
        <a href="<?= $link ?>" class="post-more">اقرأ المزيد</a>
    </div>
</article>
