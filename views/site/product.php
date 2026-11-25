<?php
$off = discount_percent($p);
$inStock = (int)$p['stock'] > 0;
$months = (int)$p['installment_months'] ?: 0;
$wished = in_wishlist((int)$p['id']);
$waText = 'عايز أطلب: ' . $p['name'] . ' — ' . money($p['price']) . "\n" . url('/product/' . $p['slug']);
$phone = setting('phone');
?>
<div class="container page-wrap">
    <nav class="crumbs">
        <a href="<?= url('/') ?>">الرئيسية</a> <?= icon('chevron-l', 14) ?>
        <?php if ($parent): ?><a href="<?= url('/category/' . $parent['slug']) ?>"><?= e($parent['name']) ?></a> <?= icon('chevron-l', 14) ?><?php endif; ?>
        <?php if ($p['category_name']): ?><a href="<?= url('/category/' . $p['category_slug']) ?>"><?= e($p['category_name']) ?></a> <?= icon('chevron-l', 14) ?><?php endif; ?>
        <b><?= e($p['name']) ?></b>
    </nav>

    <div class="product-wrap">
        <div class="gallery" data-gallery>
            <div class="gallery-main" data-zoom>
                <img src="<?= e(img($gallery[0] ?? null)) ?>" alt="<?= e($p['name']) ?>" data-gallery-main width="600" height="600">
                <button type="button" class="gallery-zoom" aria-label="تكبير الصورة"><?= icon('maximize', 18) ?></button>
            </div>
            <?php if (count($gallery) > 1): ?>
            <div class="gallery-thumbs">
                <?php foreach ($gallery as $i => $g): ?>
                    <button type="button" class="<?= $i === 0 ? 'active' : '' ?>" data-src="<?= e(img($g)) ?>" aria-label="صورة <?= $i + 1 ?>"><img src="<?= e(img($g)) ?>" alt="" loading="lazy"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <div class="pi-top">
                <div class="pi-chips">
                    <?php if ($p['category_name']): ?><a href="<?= url('/category/' . $p['category_slug']) ?>" class="chip"><?= e($p['category_name']) ?></a><?php endif; ?>
                    <?php if ($p['is_featured']): ?><span class="chip chip-gold"><?= icon('star', 12, 'filled') ?> مميز</span><?php endif; ?>
                    <?php if ($p['badge'] && !$p['is_featured']): ?><span class="chip chip-gold"><?= e($p['badge']) ?></span><?php endif; ?>
                </div>
                <div class="pi-actions">
                    <button type="button" data-wish="<?= (int)$p['id'] ?>" class="<?= $wished ? 'on' : '' ?>" aria-label="أضف للمفضلة" aria-pressed="<?= $wished ? 'true' : 'false' ?>"><?= icon('heart', 24) ?></button>
                    <button type="button" data-share data-title="<?= e($p['name']) ?>" aria-label="مشاركة"><?= icon('share', 22) ?></button>
                </div>
            </div>

            <h1><?= e($p['name']) ?></h1>
            <?php if ($p['brand_name']): ?><div class="pi-brand">الماركة: <a href="<?= url('/brand/' . $p['brand_slug']) ?>"><?= e($p['brand_name']) ?></a></div><?php endif; ?>
            <?php if ($p['rating_count']): ?><a href="#reviews" class="pi-brand" data-open-tab="reviews"><?= stars((float)$p['rating_avg']) ?> <small class="muted">(<?= (int)$p['rating_count'] ?> تقييم)</small></a><?php endif; ?>

            <div class="pi-price">
                <span class="now"><?= number_format($p['price']) ?> <small>ج.م</small></span>
                <?php if ($off): ?>
                    <span class="was">بدلاً من <del><?= number_format($p['old_price']) ?> ج.م</del></span>
                    <span class="saved">وفرت <?= number_format($p['old_price'] - $p['price']) ?> ج.م</span>
                <?php endif; ?>
            </div>

            <?php if ($months > 0): ?>
            <a href="<?= url('/installments?product=' . $p['id']) ?>" class="pi-inst">
                <?= icon('cash', 26) ?>
                <span>قسّطه على <?= $months ?> شهور: ادفع <b><?= number_format(ceil($p['price'] / $months)) ?> ج.م</b> شهرياً بنفس سعر الكاش — <u>قدّم طلب تقسيط</u></span>
            </a>
            <?php endif; ?>

            <div class="pi-stock">
                <?php if ($inStock): ?>
                    <span class="in"><?= icon('check', 18) ?> متوفر حالياً</span> <span class="muted">(<?= (int)$p['stock'] ?> قطعة)</span>
                    <?php if ((int)$p['stock'] <= 5): ?><span class="limited"><?= icon('alert', 13) ?> كمية محدودة جداً</span><?php endif; ?>
                <?php else: ?>
                    <span class="outs">غير متوفر حالياً</span>
                <?php endif; ?>
            </div>

            <?php if ($inStock): ?>
            <form action="<?= url('/cart/add') ?>" method="post" class="buy-row" data-ajax-cart id="buy-form">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                <div class="qty" data-qty>
                    <button type="button" data-step="1" aria-label="زيادة"><?= icon('plus', 16) ?></button>
                    <input type="number" name="qty" value="1" min="1" max="<?= (int)$p['stock'] ?>" aria-label="الكمية" inputmode="numeric">
                    <button type="button" data-step="-1" aria-label="تقليل"><?= icon('minus', 16) ?></button>
                </div>
                <button type="submit" class="btn btn-add"><?= icon('cart', 20, 'filled') ?> إضافة إلى السلة</button>
                <button type="submit" name="buy_now" value="1" class="btn btn-buy" data-no-ajax><?= icon('bolt', 18) ?> شراء الآن</button>
            </form>
            <?php else: ?>
            <a href="<?= e(wa_link('عايز أعرف إمتى هيتوفر: ' . $p['name'])) ?>" class="btn btn-wa btn-lg btn-block" target="_blank" rel="noopener" style="margin-bottom:18px"><?= icon('whatsapp', 20) ?> بلّغني لما يتوفر</a>
            <?php endif; ?>

            <div class="pi-features">
                <div><span><?= icon('truck', 26) ?></span>شحن سريع</div>
                <div><span><?= icon('cash', 26) ?></span>دفع عند الاستلام</div>
                <div><span><?= icon('rotate', 26) ?></span><a href="<?= url('/page/returns') ?>">سياسة الإرجاع</a></div>
                <div><span><?= icon('lock', 26) ?></span>بياناتك آمنة</div>
            </div>
        </div>

        <aside class="product-side">
            <div class="side-trust">
                <div><span><?= icon('rotate', 22) ?></span>تقدر ترجّع المنتج بسهولة</div>
                <div><span><?= icon('shield', 22) ?></span>منتج موثوق وآمن 100%</div>
                <div><span><?= icon('award', 22) ?></span><?= $p['warranty'] ? e($p['warranty']) : 'شهادة ضمان مع المنتج' ?></div>
                <div><span><?= icon('certificate', 22) ?></span>منتجات أصلية معتمدة</div>
                <div><span><?= icon('headset', 22) ?></span>مركز خدمة وصيانة متخصص</div>
            </div>
            <div class="side-sales">
                <h3>تواصل مع فريق المبيعات للاستفسار عن المنتج</h3>
                <a href="<?= e(wa_link($waText)) ?>" target="_blank" rel="noopener" class="wa"><?= icon('chat', 20) ?> محادثة فورية</a>
                <?php if ($ms = setting('messenger')): ?><a href="<?= e($ms) ?>" target="_blank" rel="noopener"><?= icon('messenger', 20) ?> ماسنجر</a><?php endif; ?>
                <a href="<?= e(wa_link($waText)) ?>" target="_blank" rel="noopener" class="wa"><?= icon('whatsapp', 20) ?> واتساب</a>
                <a href="<?= e(tel_link($phone)) ?>"><?= icon('phone', 20) ?> اتصل الآن</a>
            </div>
        </aside>
    </div>

    <div class="tabs" data-tabs id="reviews">
        <div class="tab-list" role="tablist">
            <button role="tab" class="active" data-tab="desc">الوصف</button>
            <?php if ($specs): ?><button role="tab" data-tab="specs">المواصفات</button><?php endif; ?>
            <?php if ($faqs): ?><button type="button" data-scroll="#product-faq"><?= icon('help', 16) ?> الأسئلة الشائعة</button><?php endif; ?>
            <button role="tab" data-tab="reviews">التقييمات (<?= (int)$p['rating_count'] ?>)</button>
        </div>
        <div class="tab-panel active" data-panel="desc">
            <h2><?= e($p['name']) ?></h2>
            <div class="prose"><?= rich_text($p['description'] ?: $p['short_description']) ?></div>
        </div>
        <?php if ($specs): ?>
        <div class="tab-panel" data-panel="specs">
            <table class="specs"><?php foreach ($specs as [$k, $v]): ?><tr><th><?= e($k) ?></th><td><?= e($v) ?></td></tr><?php endforeach; ?></table>
        </div>
        <?php endif; ?>
        <div class="tab-panel" data-panel="reviews">
            <div class="reviews-summary">
                <b><?= $p['rating_count'] ? e(number_format((float)$p['rating_avg'], 1)) : '0' ?></b>
                <div><?= stars((float)$p['rating_avg'], 18) ?><br><small class="muted"><?= (int)$p['rating_count'] ?> تقييم</small></div>
            </div>
            <?php foreach ($reviews as $r): ?>
            <div class="review">
                <div class="review-head"><b><?= e($r['name']) ?></b> <?= stars((float)$r['rating']) ?> <small><?= e(ar_date($r['created_at'], false)) ?></small></div>
                <?php if ($r['comment']): ?><p><?= e($r['comment']) ?></p><?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php if (!$reviews): ?><p class="muted">لسه مفيش تقييمات للمنتج ده. كن أول من يقيّمه!</p><?php endif; ?>

            <form action="<?= url('/product/' . $p['slug'] . '/review') ?>" method="post" class="review-form">
                <?= csrf_field() ?>
                <h3>اكتب تقييمك</h3>
                <div class="rate-pick" role="radiogroup" aria-label="التقييم">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" name="rating" value="<?= $i ?>" id="rate<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?>><label for="rate<?= $i ?>" title="<?= $i ?>"><?= icon('star', 26) ?></label>
                    <?php endfor; ?>
                </div>
                <div class="form-grid" style="margin-top:12px">
                    <?php if (!customer()): ?>
                        <label class="field"><span>الاسم</span><input type="text" name="name" required class="<?= inv($errors, 'name') ?>"><?= err($errors, 'name') ?></label>
                    <?php endif; ?>
                    <label class="field span-2"><span>رأيك في المنتج</span><textarea name="comment" rows="3" required class="<?= inv($errors, 'comment') ?>"></textarea><?= err($errors, 'comment') ?></label>
                </div>
                <button class="btn btn-primary">إرسال التقييم</button>
            </form>
        </div>
    </div>

    <?php if ($faqs): ?>
    <section class="faq-block" id="product-faq">
        <h2><?= icon('help', 22) ?> الأسئلة الشائعة</h2>
        <?php foreach ($faqs as $i => [$q, $a]): ?>
            <details class="acc" <?= $i === 0 ? 'open' : '' ?>><summary><?= e($q) ?> <?= icon('chevron-d', 18) ?></summary><div class="acc-a"><?= nl2p($a) ?></div></details>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if ($related): ?>
    <section class="related">
        <div class="related-head"><h2>قد يعجبك أيضاً</h2></div>
        <?= view('partials/product-carousel', ['products' => $related]) ?>
    </section>
    <?php endif; ?>
</div>

<div class="sticky-buy" data-sticky-buy>
    <div class="container">
        <img src="<?= e(img($p['image'])) ?>" alt="">
        <div class="sb-info"><b><?= e($p['name']) ?></b><span><?= number_format($p['price']) ?> جنيه</span></div>
        <?php if ($inStock): ?>
            <button type="button" class="btn btn-primary" data-buy-now><?= icon('cart', 18, 'filled') ?> اشتري الآن</button>
        <?php else: ?>
            <a href="<?= e(wa_link($waText)) ?>" class="btn btn-wa" target="_blank" rel="noopener"><?= icon('whatsapp', 18) ?> استفسر</a>
        <?php endif; ?>
    </div>
</div>
<div class="lightbox" data-lightbox><img src="" alt=""></div>
