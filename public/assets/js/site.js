/* قطرات الندى — سكريبت الموقع */
(function () {
    'use strict';
    const $ = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

    /* ---------- قائمة الموبايل ---------- */
    const drawer = $('#drawer');
    const toggleDrawer = (open) => {
        if (!drawer) return;
        drawer.classList.toggle('open', open);
        drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.style.overflow = open ? 'hidden' : '';
    };
    $$('[data-open-drawer]').forEach((b) => b.addEventListener('click', () => toggleDrawer(true)));
    $$('[data-close-drawer]').forEach((b) => b.addEventListener('click', () => toggleDrawer(false)));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') toggleDrawer(false); });

    /* ---------- الهيدر + زر لأعلى ---------- */
    const header = $('#header');
    const toTop = $('#toTop');
    const onScroll = () => {
        const y = window.scrollY;
        header && header.classList.toggle('is-stuck', y > 10);
        toTop && toTop.classList.toggle('show', y > 600);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
    toTop && toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

    /* ---------- السلايدر ---------- */
    $$('[data-slider]').forEach((slider) => {
        const slides = $$('[data-slide]', slider);
        const dots = $$('[data-dot]', slider);
        if (slides.length < 2) return;
        let i = 0, timer;
        const go = (n) => {
            slides[i].classList.remove('is-active');
            dots[i] && dots[i].classList.remove('is-active');
            i = (n + slides.length) % slides.length;
            slides[i].classList.add('is-active');
            dots[i] && dots[i].classList.add('is-active');
        };
        const play = () => { clearInterval(timer); timer = setInterval(() => go(i + 1), 6000); };
        $('[data-next]', slider)?.addEventListener('click', () => { go(i + 1); play(); });
        $('[data-prev]', slider)?.addEventListener('click', () => { go(i - 1); play(); });
        dots.forEach((d) => d.addEventListener('click', () => { go(+d.dataset.dot); play(); }));
        // سحب باللمس
        let x0 = null;
        slider.addEventListener('touchstart', (e) => { x0 = e.touches[0].clientX; }, { passive: true });
        slider.addEventListener('touchend', (e) => {
            if (x0 === null) return;
            const dx = e.changedTouches[0].clientX - x0;
            if (Math.abs(dx) > 50) { go(dx > 0 ? i + 1 : i - 1); play(); }
            x0 = null;
        });
        slider.addEventListener('mouseenter', () => clearInterval(timer));
        slider.addEventListener('mouseleave', play);
        play();
    });

    /* ---------- رسائل منبثقة ---------- */
    const toast = (msg, type = 'success', withLink = false) => {
        const wrap = $('#toasts');
        if (!wrap) return;
        const el = document.createElement('div');
        el.className = 'toast ' + type;
        el.innerHTML = '<svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' +
            (type === 'error' ? '<path d="M18 6 6 18M6 6l12 12"/>' : '<path d="M20 6 9 17l-5-5"/>') + '</svg>';
        const span = document.createElement('span');
        span.textContent = msg;
        el.appendChild(span);
        if (withLink) {
            const a = document.createElement('a');
            a.href = window.APP.cartUrl;
            a.textContent = 'عرض السلة ←';
            el.appendChild(a);
        }
        wrap.appendChild(el);
        setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; }, 3200);
        setTimeout(() => el.remove(), 3600);
    };

    /* ---------- إضافة للسلة بدون تحميل الصفحة ---------- */
    let submitter = null;
    document.addEventListener('click', (e) => {
        const b = e.target.closest('button[type=submit]');
        if (b) submitter = b;
    });
    $$('form[data-ajax-cart]').forEach((form) => {
        form.addEventListener('submit', async (e) => {
            const sub = e.submitter || submitter;
            if (sub && sub.hasAttribute('data-no-ajax') && form.contains(sub)) return; // اشتري الآن
            e.preventDefault();
            const btn = form.querySelector('button[type=submit]:not([data-no-ajax])');
            btn && (btn.disabled = true);
            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await res.json();
                if (data.ok) {
                    $$('[data-cart-count]').forEach((c) => {
                        c.textContent = data.count;
                        c.hidden = !data.count;
                        c.classList.remove('bump'); void c.offsetWidth; c.classList.add('bump');
                    });
                    toast(data.message, 'success', true);
                } else {
                    toast(data.message || 'حصلت مشكلة، حاول تاني.', 'error');
                }
            } catch (err) {
                form.submit();
            } finally {
                btn && (btn.disabled = false);
            }
        });
    });

    /* ---------- أزرار الكمية ---------- */
    $$('[data-qty]').forEach((box) => {
        const input = $('input', box);
        $$('[data-step]', box).forEach((b) => b.addEventListener('click', () => {
            const min = +input.min || 0;
            const max = +input.max || 99;
            const v = Math.min(max, Math.max(min, (+input.value || 0) + +b.dataset.step));
            if (v !== +input.value) {
                input.value = v;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }));
    });
    let qtyTimer;
    $$('[data-autosubmit-qty]').forEach((inp) => inp.addEventListener('change', () => {
        clearTimeout(qtyTimer);
        qtyTimer = setTimeout(() => inp.form.submit(), 500);
    }));
    $$('[data-autosubmit]').forEach((s) => s.addEventListener('change', () => s.form.submit()));

    /* ---------- معرض الصور ---------- */
    $$('[data-gallery]').forEach((g) => {
        const main = $('[data-gallery-main]', g);
        $$('.gallery-thumbs button', g).forEach((b) => b.addEventListener('click', () => {
            main.src = b.dataset.src;
            $$('.gallery-thumbs button', g).forEach((x) => x.classList.toggle('active', x === b));
        }));
    });

    /* ---------- التبويبات ---------- */
    $$('[data-tabs]').forEach((t) => {
        $$('[data-tab]', t).forEach((b) => b.addEventListener('click', () => {
            $$('[data-tab]', t).forEach((x) => x.classList.toggle('active', x === b));
            $$('[data-panel]', t).forEach((p) => p.classList.toggle('active', p.dataset.panel === b.dataset.tab));
        }));
    });

    /* ---------- عداد الأرقام ---------- */
    const counters = $$('[data-count]');
    if (counters.length && 'IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((en) => {
                if (!en.isIntersecting) return;
                io.unobserve(en.target);
                const el = en.target;
                const target = +el.dataset.count;
                const prefix = el.textContent.trim().startsWith('+') ? '+' : '';
                const t0 = performance.now();
                const step = (t) => {
                    const p = Math.min(1, (t - t0) / 1400);
                    el.textContent = prefix + Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString('en-US');
                    if (p < 1) requestAnimationFrame(step);
                };
                requestAnimationFrame(step);
            });
        }, { threshold: .4 });
        counters.forEach((c) => io.observe(c));
    }
})();
