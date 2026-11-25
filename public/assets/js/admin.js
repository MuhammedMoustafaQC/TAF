/* قطرات الندى — سكريبت لوحة التحكم */
(function () {
    'use strict';
    const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

    // القائمة الجانبية في الموبايل
    $$('[data-side-open]').forEach((b) => b.addEventListener('click', () => document.body.classList.add('side-open')));
    $$('[data-side-close]').forEach((b) => b.addEventListener('click', () => document.body.classList.remove('side-open')));

    // تأكيد قبل الحذف
    $$('form[data-confirm]').forEach((f) => f.addEventListener('submit', (e) => {
        if (!confirm(f.dataset.confirm)) e.preventDefault();
    }));

    // إرسال تلقائي عند تغيير القائمة
    $$('[data-autosubmit]').forEach((s) => s.addEventListener('change', () => s.form.submit()));

    // صف الجدول كله رابط
    $$('tr[data-href]').forEach((tr) => tr.addEventListener('click', (e) => {
        if (e.target.closest('a, button, input, select, form')) return;
        window.location = tr.dataset.href;
    }));

    // معاينة الصورة قبل الرفع
    $$('[data-preview-input]').forEach((inp) => inp.addEventListener('change', () => {
        const img = inp.closest('label').querySelector('[data-preview]');
        const f = inp.files && inp.files[0];
        if (img && f) img.src = URL.createObjectURL(f);
    }));

    // سويتش الظهور / التمييز بدون تحميل الصفحة
    $$('form[data-toggle-form]').forEach((f) => f.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = f.querySelector('.switch');
        btn.disabled = true;
        try {
            const res = await fetch(f.action, { method: 'POST', body: new FormData(f), headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            if (data.ok) btn.classList.toggle('on', !!data.value);
        } catch (err) {
            f.submit();
        } finally {
            btn.disabled = false;
        }
    }));
})();
