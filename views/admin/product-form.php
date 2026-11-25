<form method="post" enctype="multipart/form-data" class="form-layout">
    <?= csrf_field() ?>
    <div class="stack">
        <section class="panel">
            <h2>بيانات المنتج</h2>
            <div class="form-grid">
                <label class="field span-2"><span>اسم المنتج *</span><input type="text" name="name" value="<?= e($p['name']) ?>" required class="<?= inv($errors, 'name') ?>"><?= err($errors, 'name') ?></label>
                <label class="field"><span>القسم</span>
                    <select name="category_id">
                        <option value="">بدون قسم</option>
                        <?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)$p['category_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
                    </select>
                </label>
                <label class="field"><span>الماركة</span>
                    <select name="brand_id">
                        <option value="">بدون ماركة</option>
                        <?php foreach ($brands as $b): ?><option value="<?= (int)$b['id'] ?>" <?= (int)($p['brand_id'] ?? 0) === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?>
                    </select>
                </label>
                <label class="field"><span>رابط المنتج (Slug)</span><input type="text" name="slug" value="<?= e($p['slug']) ?>" placeholder="بيتعمل تلقائياً من الاسم"></label>
                <label class="field span-2"><span>وصف مختصر</span><input type="text" name="short_description" value="<?= e($p['short_description']) ?>" maxlength="200" placeholder="سطر واحد بيظهر تحت اسم المنتج"></label>
                <label class="field span-2"><span>الوصف الكامل</span><textarea name="description" rows="6"><?= e($p['description']) ?></textarea></label>
                <label class="field span-2"><span>المواصفات <small class="muted">(كل سطر: الخاصية: القيمة)</small></span><textarea name="specs" rows="6" placeholder="عدد المراحل: 7 مراحل&#10;سعة الخزان: 12 لتر"><?= e($p['specs']) ?></textarea></label>
                <label class="field span-2"><span>الأسئلة الشائعة عن المنتج <small class="muted">(كل سؤال في سطر وتحته الإجابة، وسيب سطر فاضي بين كل سؤال والتاني)</small></span><textarea name="faqs" rows="7" placeholder="هل التركيب مجاني؟&#10;أيوه، التركيب مجاني داخل نطاق الخدمة.&#10;&#10;كل قد إيه بغير الشمعات؟&#10;كل 3 لـ 6 شهور."><?= e($p['faqs'] ?? '') ?></textarea></label>
            </div>
        </section>

        <section class="panel">
            <h2>الصور</h2>
            <?= err($errors, 'image') ?>
            <div class="form-grid">
                <div class="field">
                    <span>الصورة الرئيسية</span>
                    <label class="upload">
                        <img src="<?= e(img($p['image'])) ?>" alt="" data-preview>
                        <input type="file" name="image" accept="image/*" data-preview-input>
                        <small>اضغط لاختيار صورة (JPG / PNG / WEBP — أقصى حجم 4MB)</small>
                    </label>
                </div>
                <div class="field">
                    <span>صور إضافية (معرض)</span>
                    <?php if ($gallery): ?>
                    <div class="gallery-edit">
                        <?php foreach ($gallery as $g): ?>
                        <label class="g-item">
                            <img src="<?= e(img($g)) ?>" alt="">
                            <span><input type="checkbox" name="remove_gallery[]" value="<?= e($g) ?>"> حذف</span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="gallery[]" accept="image/*" multiple class="file-input">
                </div>
            </div>
        </section>
    </div>

    <div class="stack">
        <section class="panel">
            <h2>السعر والمخزون</h2>
            <label class="field"><span>السعر (ج.م) *</span><input type="number" name="price" value="<?= e($p['price']) ?>" step="0.01" min="0" required class="<?= inv($errors, 'price') ?>"><?= err($errors, 'price') ?></label>
            <label class="field"><span>السعر قبل الخصم</span><input type="number" name="old_price" value="<?= e($p['old_price']) ?>" step="0.01" min="0" placeholder="اختياري — لعرض خصم"></label>
            <label class="field"><span>الكمية المتاحة</span><input type="number" name="stock" value="<?= e($p['stock']) ?>" min="0"></label>
            <label class="field"><span>التقسيط (عدد الشهور) <small class="muted">0 = بدون تقسيط</small></span><input type="number" name="installment_months" value="<?= (int)($p['installment_months'] ?? 0) ?>" min="0" max="60"></label>
        </section>
        <section class="panel">
            <h2>عرض المنتج</h2>
            <label class="field"><span>شارة على المنتج</span><input type="text" name="badge" value="<?= e($p['badge']) ?>" placeholder="مثلاً: الأكثر مبيعاً / جديد"></label>
            <label class="field"><span>الضمان</span><input type="text" name="warranty" value="<?= e($p['warranty']) ?>" placeholder="مثلاً: ضمان 5 سنوات"></label>
            <label class="check"><input type="checkbox" name="is_active" value="1" <?= $p['is_active'] ? 'checked' : '' ?>> ظاهر في الموقع</label>
            <label class="check"><input type="checkbox" name="is_featured" value="1" <?= $p['is_featured'] ? 'checked' : '' ?>> منتج مميز (يظهر في الرئيسية)</label>
            <label class="check"><input type="checkbox" name="is_express" value="1" <?= ($p['is_express'] ?? 1) ? 'checked' : '' ?>> توصيل سريع (شارة اكسبريس)</label>
        </section>
        <div class="sticky-actions">
            <button class="btn btn-primary btn-block btn-lg"><?= icon('check', 18) ?> حفظ المنتج</button>
            <?php if ($p['id']): ?>
                <a href="<?= url('/product/' . $p['slug']) ?>" target="_blank" class="btn btn-light btn-block"><?= icon('eye', 18) ?> عرض في الموقع</a>
            <?php endif; ?>
            <a href="<?= url('/admin/products') ?>" class="btn btn-ghost btn-block">رجوع للمنتجات</a>
        </div>
    </div>
</form>
<?php if ($p['id']): ?>
<form action="<?= url('/admin/products/' . $p['id'] . '/delete') ?>" method="post" data-confirm="متأكد إنك عايز تحذف المنتج ده نهائياً؟" class="danger-zone">
    <?= csrf_field() ?>
    <button class="btn btn-danger-outline btn-sm"><?= icon('trash', 16) ?> حذف المنتج</button>
</form>
<?php endif; ?>
