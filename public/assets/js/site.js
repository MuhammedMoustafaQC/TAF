/* قطرات الندى — سكريبت الموقع (v2) */
(function () {
    'use strict';
    const $ = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
    const store = {
        get(k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
        set(k, v) { try { localStorage.setItem(k, v); } catch (e) { /* تجاهل */ } },
    };
    const post = (url, data) => fetch(url, {
        method: 'POST', body: data,
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    }).then((r) => r.json());

    /* ---------- رسائل منبثقة ---------- */
    const toast = (msg, type = 'success', link = null) => {
        const wrap = $('#toasts');
        if (!wrap) return;
        const el = document.createElement('div');
        el.className = 'toast ' + type;
        el.innerHTML = '<svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' +
            (type === 'error' ? '<path d="M18 6 6 18M6 6l12 12"/>' : '<path d="M20 6 9 17l-5-5"/>') + '</svg>';
        const span = document.createElement('span');
        span.textContent = msg;
        el.appendChild(span);
        if (link) {
            const a = document.createElement('a');
            a.href = link.href;
            a.textContent = link.text;
            el.appendChild(a);
        }
        wrap.appendChild(el);
        setTimeout(() => { el.style.transition = 'opacity .3s'; el.style.opacity = '0'; }, 3200);
        setTimeout(() => el.remove(), 3600);
    };
    const bump = (sel, count) => $$(sel).forEach((c) => {
        c.textContent = count;
        c.hidden = !count;
        c.classList.remove('bump'); void c.offsetWidth; c.classList.add('bump');
    });

    /* ---------- القائمة الجانبية ---------- */
    const drawer = $('#drawer');
    const setDrawer = (open) => {
        if (!drawer) return;
        drawer.classList.toggle('open', open);
        drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.style.overflow = open ? 'hidden' : '';
    };
    $$('[data-open-drawer]').forEach((b) => b.addEventListener('click', () => setDrawer(true)));
    $$('[data-close-drawer]').forEach((b) => b.addEventListener('click', () => setDrawer(false)));

    /* ---------- المودال ---------- */
    const openModal = (m) => { if (!m) return; setDrawer(false); m.classList.add('open'); m.setAttribute('aria-hidden', 'false'); document.body.style.overflow = 'hidden'; };
    const closeModal = (m) => {
        m.classList.remove('open'); m.setAttribute('aria-hidden', 'true'); document.body.style.overflow = '';
        if (m.hasAttribute('data-welcome')) store.set('qn_welcome_v1', '1');
    };
    $$('[data-open-modal]').forEach((b) => b.addEventListener('click', () => openModal(document.getElementById(b.dataset.openModal))));
    $$('.modal').forEach((m) => $$('[data-close-modal]', m).forEach((b) => b.addEventListener('click', () => closeModal(m))));
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        setDrawer(false);
        $$('.modal.open').forEach(closeModal);
        $$('[data-lightbox].open').forEach((l) => l.classList.remove('open'));
    });
    const welcome = $('[data-welcome]');
    if (welcome && !store.get('qn_welcome_v1')) setTimeout(() => openModal(welcome), 600);

    // اختيار موقع التوصيل
    $$('[data-location-form]').forEach((f) => f.addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const d = await post(f.action, new FormData(f));
            if (d.ok) {
                $$('[data-location-label]').forEach((l) => { l.textContent = d.governorate || 'اختر موقعك'; });
                closeModal(f.closest('.modal'));
                toast('التوصيل إلى ' + d.governorate);
            }
        } catch (err) { f.submit(); }
    }));

    /* ---------- الهيدر + لأعلى ---------- */
    const toTop = $('#toTop');
    const sticky = $('[data-sticky-buy]');
    const buyForm = $('#buy-form');
    const onScroll = () => {
        const y = window.scrollY;
        toTop && toTop.classList.toggle('show', y > 700);
        if (sticky && buyForm) sticky.classList.toggle('show', buyForm.getBoundingClientRect().bottom < 0);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
    toTop && toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    $$('[data-buy-now]').forEach((b) => b.addEventListener('click', () => {
        const btn = buyForm && buyForm.querySelector('[name=buy_now]');
        if (btn) btn.click(); else buyForm && buyForm.scrollIntoView({ behavior: 'smooth' });
    }));

    /* ---------- السلايدر ---------- */
    $$('[data-slider]').forEach((slider) => {
        const slides = $$('[data-slide]', slider);
        const dots = $$('[data-dot]', slider.nextElementSibling && slider.nextElementSibling.matches('[data-dots]') ? slider.nextElementSibling : slider);
        if (slides.length < 2) return;
        let i = 0, timer;
        const go = (n) => {
            slides[i].classList.remove('is-active'); dots[i] && dots[i].classList.remove('is-active');
            i = (n + slides.length) % slides.length;
            slides[i].classList.add('is-active'); dots[i] && dots[i].classList.add('is-active');
        };
        const play = () => { clearInterval(timer); timer = setInterval(() => go(i + 1), 6000); };
        $('[data-next]', slider)?.addEventListener('click', () => { go(i + 1); play(); });
        $('[data-prev]', slider)?.addEventListener('click', () => { go(i - 1); play(); });
        dots.forEach((d) => d.addEventListener('click', () => { go(+d.dataset.dot); play(); }));
        let x0 = null;
        slider.addEventListener('touchstart', (e) => { x0 = e.touches[0].clientX; }, { passive: true });
        slider.addEventListener('touchend', (e) => {
            if (x0 === null) return;
            const dx = e.changedTouches[0].clientX - x0;
            if (Math.abs(dx) > 50) { go(dx > 0 ? i + 1 : i - 1); play(); }
            x0 = null;
        });
        play();
    });

    /* ---------- الكاروسيل (RTL) ---------- */
    $$('[data-carousel]').forEach((car) => {
        const track = $('[data-car-track]', car);
        const prev = $('[data-car-prev]', car);
        const next = $('[data-car-next]', car);
        if (!track) return;
        const step = () => Math.max(track.clientWidth * 0.8, 200);
        // في RTL: التالي = ناحية الشمال = scrollLeft بالسالب
        next && next.addEventListener('click', () => track.scrollBy({ left: -step() }));
        prev && prev.addEventListener('click', () => track.scrollBy({ left: step() }));
        const update = () => {
            const max = track.scrollWidth - track.clientWidth;
            const pos = Math.abs(track.scrollLeft);
            if (prev) prev.disabled = pos < 4;
            if (next) next.disabled = pos > max - 4;
            if (max <= 4) { prev && (prev.hidden = true); next && (next.hidden = true); }
        };
        track.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
        if (car.hasAttribute('data-autoplay')) {
            let t = setInterval(() => {
                const max = track.scrollWidth - track.clientWidth;
                if (max <= 4) return;
                if (Math.abs(track.scrollLeft) > max - 4) track.scrollTo({ left: 0 });
                else track.scrollBy({ left: -Math.min(step(), 180) });
            }, 3000);
            car.addEventListener('mouseenter', () => clearInterval(t));
            car.addEventListener('touchstart', () => clearInterval(t), { passive: true });
        }
    });

    /* ---------- إضافة للسلة ---------- */
    $$('form[data-ajax-cart]').forEach((form) => {
        form.addEventListener('submit', async (e) => {
            if (e.submitter && e.submitter.hasAttribute('data-no-ajax')) return; // شراء الآن
            e.preventDefault();
            const btn = e.submitter || form.querySelector('button[type=submit]');
            btn && (btn.disabled = true);
            try {
                const d = await post(form.action, new FormData(form));
                if (d.ok) {
                    bump('[data-cart-count]', d.count);
                    toast(d.message, 'success', { href: window.APP.cartUrl, text: 'عرض السلة ←' });
                } else toast(d.message || 'حصلت مشكلة، حاول تاني.', 'error');
            } catch (err) { form.submit(); }
            finally { btn && (btn.disabled = false); }
        });
    });

    /* ---------- المفضلة ---------- */
    document.addEventListener('click', async (e) => {
        const b = e.target.closest('[data-wish]');
        if (!b) return;
        e.preventDefault();
        const fd = new FormData();
        fd.append('_csrf', window.APP.csrf);
        fd.append('product_id', b.dataset.wish);
        try {
            const d = await post(window.APP.wishUrl, fd);
            if (!d.ok) return;
            $$('[data-wish="' + b.dataset.wish + '"]').forEach((x) => { x.classList.toggle('on', d.on); x.setAttribute('aria-pressed', d.on ? 'true' : 'false'); });
            bump('[data-wish-count]', d.count);
            toast(d.message);
        } catch (err) { toast('حصلت مشكلة، حاول تاني.', 'error'); }
    });

    /* ---------- الكمية ---------- */
    $$('[data-qty]').forEach((box) => {
        const input = $('input', box);
        $$('[data-step]', box).forEach((b) => b.addEventListener('click', () => {
            const min = +input.min || 0, max = +input.max || 99;
            const v = Math.min(max, Math.max(min, (+input.value || 0) + +b.dataset.step));
            if (v !== +input.value) { input.value = v; input.dispatchEvent(new Event('change', { bubbles: true })); }
        }));
    });
    let qtyTimer;
    $$('[data-autosubmit-qty]').forEach((inp) => inp.addEventListener('change', () => { clearTimeout(qtyTimer); qtyTimer = setTimeout(() => inp.form.submit(), 500); }));
    $$('[data-autosubmit]').forEach((s) => s.addEventListener('change', () => (s.form.requestSubmit ? s.form.requestSubmit() : s.form.submit())));
    // ما نبعتش حقول فاضية في رابط الفلترة
    $$('[data-filter-form]').forEach((f) => f.addEventListener('submit', () => {
        $$('input', f).forEach((i) => { if ((i.type === 'number' || i.type === 'hidden') && i.value === '') i.disabled = true; });
    }));

    /* ---------- فلاتر المتجر ---------- */
    $$('[data-filter-all]').forEach((all) => all.addEventListener('change', () => {
        if (!all.checked) return;
        const f = all.closest('form');
        $$('input[name="cat[]"]', f).forEach((c) => { c.checked = false; });
        f.requestSubmit ? f.requestSubmit() : f.submit();
    }));
    $$('[data-sort-select]').forEach((s) => s.addEventListener('change', () => { window.location = s.value; }));
    const side = $('#shop-filters');
    $$('[data-toggle-filters]').forEach((b) => b.addEventListener('click', (e) => { e.stopPropagation(); side && side.classList.toggle('open'); }));
    document.addEventListener('click', (e) => { if (side && side.classList.contains('open') && !side.contains(e.target)) side.classList.remove('open'); });

    /* ---------- معرض الصور ---------- */
    $$('[data-gallery]').forEach((g) => {
        const main = $('[data-gallery-main]', g);
        $$('.gallery-thumbs button', g).forEach((b) => b.addEventListener('click', () => {
            main.src = b.dataset.src;
            $$('.gallery-thumbs button', g).forEach((x) => x.classList.toggle('active', x === b));
        }));
        const lb = $('[data-lightbox]');
        $('[data-zoom]', g)?.addEventListener('click', () => {
            if (!lb) return;
            $('img', lb).src = main.src;
            lb.classList.add('open');
        });
        lb && lb.addEventListener('click', () => lb.classList.remove('open'));
    });

    /* ---------- التبويبات ---------- */
    $$('[data-tabs]').forEach((t) => {
        const show = (name) => {
            $$('[data-tab]', t).forEach((x) => x.classList.toggle('active', x.dataset.tab === name));
            $$('[data-panel]', t).forEach((p) => p.classList.toggle('active', p.dataset.panel === name));
        };
        $$('[data-tab]', t).forEach((b) => b.addEventListener('click', () => show(b.dataset.tab)));
        $$('[data-scroll]', t).forEach((b) => b.addEventListener('click', () => $(b.dataset.scroll)?.scrollIntoView({ behavior: 'smooth', block: 'start' })));
        $$('[data-open-tab]').forEach((a) => a.addEventListener('click', () => show(a.dataset.openTab)));
        if (location.hash === '#reviews' && $('[data-panel="reviews"]', t)) show('reviews');
    });

    /* ---------- مشاركة ---------- */
    $$('[data-share]').forEach((b) => b.addEventListener('click', async () => {
        const data = { title: b.dataset.title || document.title, url: location.href };
        if (navigator.share) { try { await navigator.share(data); } catch (e) { /* ألغى */ } return; }
        try { await navigator.clipboard.writeText(location.href); toast('اتنسخ رابط المنتج'); } catch (e) { prompt('انسخ الرابط', location.href); }
    }));

    /* ---------- عداد العروض (لحد آخر اليوم) ---------- */
    $$('[data-countdown]').forEach((c) => {
        const h = $('[data-h]', c), m = $('[data-m]', c), s = $('[data-s]', c);
        const pad = (n) => String(n).padStart(2, '0');
        const tick = () => {
            const now = new Date();
            const end = new Date(now); end.setHours(23, 59, 59, 999);
            let diff = Math.max(0, Math.floor((end - now) / 1000));
            h.textContent = pad(Math.floor(diff / 3600)); diff %= 3600;
            m.textContent = pad(Math.floor(diff / 60));
            s.textContent = pad(diff % 60);
        };
        tick(); setInterval(tick, 1000);
    });

    /* ---------- عداد الأرقام ---------- */
    const counters = $$('[data-count]');
    if (counters.length && 'IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => entries.forEach((en) => {
            if (!en.isIntersecting) return;
            io.unobserve(en.target);
            const el = en.target, target = +el.dataset.count, prefix = el.textContent.trim().startsWith('+') ? '+' : '';
            const t0 = performance.now();
            const step = (t) => {
                const p = Math.min(1, (t - t0) / 1400);
                el.textContent = prefix + Math.round(target * (1 - Math.pow(1 - p, 3)));
                if (p < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        }), { threshold: .4 });
        counters.forEach((c) => io.observe(c));
    }

    $$('[data-autohide]').forEach((a) => setTimeout(() => { a.style.transition = 'opacity .4s'; a.style.opacity = '0'; setTimeout(() => a.remove(), 400); }, 5000));
})();
