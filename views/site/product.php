<?php
$off = discount_percent($p);
$inStock = (int)$p['stock'] > 0;
$low = $inStock && (int)$p['stock'] <= 5;
$months = (int)$p['installment_months'];
$wished = in_wishlist((int)$p['id']);
$waText = 'عايز أستفسر عن: ' . $p['name'] . ' — ' . money($p['price']) . "\n" . url('/product/' . $p['slug']);
$loc = delivery_location();
$keySpecs = array_slice($specs, 0, 4);
?>
<div class="container page-wrap" style="padding-bottom:0">
    <nav class="crumbs" aria-label="مسار الصفحة">
        <a href="<?= url('/') ?>">الرئيسية</a> <?= icon('chevron-l', 13) ?>
        <?php if ($parent): ?><a href="<?= url('/category/' . $parent['slug']) ?>"><?= e($parent['name']) ?></a> <?= icon('chevron-l', 13) ?><?php endif; ?>
        <?php if ($p['category_name']): ?><a href="<?= url('/category/' . $p['category_slug']) ?>"><?= e($p['category_name']) ?></a> <?= icon('chevron-l', 13) ?><?php endif; ?>
        <b><?= e($p['name']) ?></b>
    </nav>

    <div class="product-top">
        <div class="gallery<?= count($gallery) > 1 ? '' : ' single' ?>" data-gallery>
            <div class="gallery-main" data-zoom>
                <img src="<?= e(img($gallery[0] ?? null)) ?>" alt="<?= e($p['name']) ?>" data-gallery-main width="600" height="600">
                <div class="pcard-tags">
                    <?php if ($off): ?><span class="tag tag-sale">وفّر <?= $off ?>%</span><?php endif; ?>
                    <?php if ($p['badge']): ?><span class="tag"><?= e($p['badge']) ?></span><?php endif; ?>
                </div>
                <button type="button" class="gallery-zoom" aria-label="تكبير الصورة"><?= icon('maximize', 18) ?></button>
            </div>
            <?php if (count($gallery) > 1): ?>
            <div class="gallery-thumbs">
                <?php foreach ($gallery as $i => $g): ?><button type="button" class="<?= $i === 0 ? 'active' : '' ?>" data-src="<?= e(img($g)) ?>" aria-label="صورة <?= $i + 1 ?>"><img src="<?= e(img($g)) ?>" alt="" loading="lazy"></button><?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <div class="pi-head">
                <span class="pi-brand"><?php if ($p['brand_name']): ?>الماركة: <a href="<?= url('/brand/' . $p['brand_slug']) ?>"><?= e($p['brand_name']) ?></a><?php elseif ($p['category_name']): ?><?= e($p['category_name']) ?><?php endif; ?></span>
                <div class="pi-actions">
                    <button type="button" data-wish="<?= (int)$p['id'] ?>" class="<?= $wished ? 'on' : '' ?>" aria-label="أضف للمفضلة" aria-pressed="<?= $wished ? 'true' : 'false' ?>"><?= icon('heart', 20) ?></button>
                    <button type="button" data-share data-title="<?= e($p['name']) ?>" aria-label="مشاركة"><?= icon('share', 19) ?></button>
                </div>
            </div>
            <h1><?= e($p['name']) ?></h1>
            <a href="#reviews" class="pi-rating"><?= stars((float)$p['rating_avg'], 15) ?> <?= $p['rating_count'] ? e(number_format((float)$p['rating_avg'], 1)) . ' · ' . (int)$p['rating_count'] . ' تقييم' : 'لسه مفيش تقييمات — قيّمه' ?></a>
            <?php if ($p['short_description']): ?><p class="pi-short"><?= e($p['short_description']) ?></p><?php endif; ?>

            <div class="pi-price">
                <span class="now"><?= number_format($p['price']) ?><small>ج.م</small></span>
                <?php if ($off): ?><del><?= number_format($p['old_price']) ?> ج.م</del><span class="saved">وفّرت <?= number_format($p['old_price'] - $p['price']) ?> ج.م</span><?php endif; ?>
                <span class="vat">السعر شامل الضريبة<?= $p['warranty'] ? ' · ' . e($p['warranty']) : '' ?></span>
            </div>

            <div class="pi-stock<?= $inStock ? ($low ? ' low' : '') : ' out' ?>">
                <span class="dot"></span>
                <?php if (!$inStock): ?><b>غير متوفر حالياً</b>
                <?php elseif ($low): ?><b>باقي <?= (int)$p['stock'] ?> قطع بس</b> <span class="muted">— اطلب بدري</span>
                <?php else: ?><b>متوفر</b> <span class="muted">وجاهز للشحن</span><?php endif; ?>
            </div>

            <?php if ($inStock): ?>
            <form action="<?= url('/cart/add') ?>" method="post" class="buy-row" data-ajax-cart id="buy-form">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                <div class="qty" data-qty>
                    <button type="button" data-step="1" aria-label="زيادة"><?= icon('plus', 16) ?></button>
                    <input type="number" name="qty" id="qty" value="1" min="1" max="<?= (int)$p['stock'] ?>" aria-label="الكمية" inputmode="numeric">
                    <button type="button" data-step="-1" aria-label="تقليل"><?= icon('minus', 16) ?></button>
                </div>
                <button type="submit" class="btn btn-primary"><?= icon('bag', 18) ?> أضف للسلة</button>
                <button type="submit" name="buy_now" value="1" class="btn btn-dark" data-no-ajax>اشتري الآن</button>
            </form>
            <?php else: ?>
            <a href="<?= e(wa_link('عايز أعرف إمتى هيتوفر: ' . $p['name'])) ?>" class="btn btn-wa btn-lg btn-block" target="_blank" rel="noopener"><?= icon('whatsapp', 20) ?> بلّغني لما يتوفر</a>
            <?php endif; ?>
            <div class="pi-help">محتاج مساعدة في الاختيار؟ <a href="<?= e(wa_link($waText)) ?>" class="wa" target="_blank" rel="noopener"><?= icon('whatsapp', 16) ?> كلّم خبير مياه</a></div>

            <div class="pi-box">
                <span class="bx-ico"><?= icon('truck', 22) ?></span>
                <div><b>التوصيل <?= $loc ? 'إلى ' . e($loc) : '' ?> <?= e(delivery_estimate($loc)) ?></b><small><?= (float)setting('free_shipping_min', 0) > 0 ? 'شحن مجاني للطلبات فوق ' . money(setting('free_shipping_min')) : 'رسوم الشحن ' . money(setting('shipping_fee', 0)) ?></small></div>
                <a href="#" data-open-modal="location-modal"><?= $loc ? 'تغيير' : 'اختار محافظتك' ?></a>
            </div>
            <?php if ($months > 0): ?>
            <div class="pi-box">
                <span class="bx-ico"><?= icon('cash', 22) ?></span>
                <div><b><?= number_format(ceil($p['price'] / $months)) ?> ج.م شهرياً لمدة <?= $months ?> شهور</b><small>بنفس سعر الكاش ومن غير بنك</small></div>
                <a href="<?= url('/installments?product=' . $p['id']) ?>">قدّم طلب</a>
            </div>
            <?php endif; ?>

            <div class="pi-perks">
                <div><?= icon('tool', 18) ?> تركيب مجاني في البيت</div>
                <div><?= icon('shield', 18) ?> منتج أصلي بضمان مكتوب</div>
                <div><?= icon('rotate', 18) ?> إرجاع خلال 14 يوم</div>
                <div><?= icon('cash', 18) ?> الدفع عند الاستلام</div>
            </div>
        </div>
    </div>
</div>

<nav class="subnav" aria-label="أقسام صفحة المنتج" data-subnav>
    <div class="container">
        <a href="#desc" class="active">الوصف</a>
        <?php if ($specs): ?><a href="#specs">المواصفات</a><?php endif; ?>
        <?php if ($faqs): ?><a href="#faq">الأسئلة الشائعة</a><?php endif; ?>
        <a href="#reviews">التقييمات (<?= (int)$p['rating_count'] ?>)</a>
    </div>
</nav>

<div class="container">
    <section class="p-section" id="desc">
        <h2>عن المنتج</h2>
        <div>
            <div class="prose"><?= rich_text($p['description'] ?: $p['short_description']) ?></div>
            <?php if ($keySpecs): ?>
            <div class="chips" style="margin-top:18px"><?php foreach ($keySpecs as [$k, $v]): ?><span class="chip" style="cursor:default"><span class="muted"><?= e($k) ?>:</span> <?= e($v) ?></span><?php endforeach; ?></div>
            <?php endif; ?>
        </div>
    </section>
    <?php if ($specs): ?>
    <section class="p-section" id="specs">
        <h2>المواصفات الفنية</h2>
        <table class="specs"><?php foreach ($specs as [$k, $v]): ?><tr><th scope="row"><?= e($k) ?></th><td><?= e($v) ?></td></tr><?php endforeach; ?></table>
    </section>
    <?php endif; ?>
    <?php if ($faqs): ?>
    <section class="p-section" id="faq">
        <h2>الأسئلة الشائعة</h2>
        <div><?php foreach ($faqs as $i => [$qq, $a]): ?><details class="acc" <?= $i === 0 ? 'open' : '' ?>><summary><?= e($qq) ?> <?= icon('plus', 18) ?></summary><div class="acc-a"><?= nl2p($a) ?></div></details><?php endforeach; ?></div>
    </section>
    <?php endif; ?>
    <section class="p-section" id="reviews">
        <h2>تقييمات العملاء</h2>
        <div>
            <div class="reviews-summary">
                <b><?= e(number_format((float)$p['rating_avg'], 1)) ?></b>
                <div><?= stars((float)$p['rating_avg'], 18) ?><br><small class="muted"><?= (int)$p['rating_count'] ?> تقييم من عملاء اشتروا المنتج</small></div>
            </div>
            <?php foreach ($reviews as $r): ?>
            <div class="review">
                <div class="review-head"><b><?= e($r['name']) ?></b> <?= stars((float)$r['rating'], 14) ?> <small><?= e(ar_date($r['created_at'], false)) ?></small></div>
                <?php if ($r['comment']): ?><p><?= e($r['comment']) ?></p><?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php if (!$reviews): ?><p class="muted">لسه مفيش تقييمات للمنتج ده. لو جربته، شاركنا رأيك.</p><?php endif; ?>
            <form action="<?= url('/product/' . $p['slug'] . '/review') ?>" method="post" class="review-form">
                <?= csrf_field() ?>
                <h3>اكتب تقييمك</h3>
                <div class="rate-pick" role="radiogroup" aria-label="التقييم من 5">
                    <?php for ($i = 5; $i >= 1; $i--): ?><input type="radio" name="rating" value="<?= $i ?>" id="rate<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?>><label for="rate<?= $i ?>" title="<?= $i ?> من 5"><?= icon('star', 26) ?></label><?php endfor; ?>
                </div>
                <div class="form-grid" style="margin-top:14px">
                    <?php if (!customer()): ?><label class="field"><span>الاسم</span><input type="text" name="name" id="rv-name" required class="<?= inv($errors, 'name') ?>"><?= err($errors, 'name') ?></label><?php endif; ?>
                    <label class="field span-2"><span>رأيك في المنتج</span><textarea name="comment" id="rv-comment" rows="3" required class="<?= inv($errors, 'comment') ?>"></textarea><?= err($errors, 'comment') ?></label>
                </div>
                <button class="btn btn-dark">إرسال التقييم</button>
                <small class="muted" style="display:block;margin-top:10px">التقييمات بتظهر بعد مراجعتها.</small>
            </form>
        </div>
    </section>

    <?php if ($related): ?>
    <section class="related">
        <?= view('partials/section-head', ['eyebrow' => 'منتجات مشابهة', 'title' => 'ممكن يعجبك كمان']) ?>
        <?= view('partials/product-carousel', ['products' => $related]) ?>
    </section>
    <?php endif; ?>
</div>

<div class="sticky-buy" data-sticky-buy>
    <div class="container">
        <img src="<?= e(img($p['image'])) ?>" alt="">
        <div class="sb-info"><b><?= e($p['name']) ?></b><span><?= money($p['price']) ?><?= $months > 0 ? ' · أو ' . number_format(ceil($p['price'] / $months)) . ' ج.م/شهر' : '' ?></span></div>
        <?php if ($inStock): ?>
            <button type="button" class="btn btn-primary" data-buy-now><?= icon('bag', 18) ?> اشتري الآن</button>
        <?php else: ?>
            <a href="<?= e(wa_link($waText)) ?>" class="btn btn-wa" target="_blank" rel="noopener"><?= icon('whatsapp', 18) ?> استفسر</a>
        <?php endif; ?>
    </div>
</div>
<div class="lightbox" data-lightbox role="dialog" aria-label="صورة مكبرة"><img src="" alt=""></div>
