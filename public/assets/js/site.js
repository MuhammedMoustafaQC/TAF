/* قطرات الندى — سكريبت الموقع (v3) */
(function () {
    'use strict';
    const $ = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
    const store = {
        get(k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
        set(k, v) { try { localStorage.setItem(k, v); } catch (e) { /* تجاهل */ } },
    };
    const post = (url, data) => fetch(url, {
        method: 'POST', body: data, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    }).then((r) => r.json());
    const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const lockScroll = (on) => { document.body.style.overflow = on ? 'hidden' : ''; };

    /* ---------- رسائل ---------- */
    const toast = (msg, type = 'success', link = null) => {
        const wrap = $('#toasts');
        if (!wrap) return;
        const el = document.createElement('div');
        el.className = 'toast ' + type;
        el.setAttribute('role', 'status');
        el.innerHTML = '<svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">' +
            (type === 'error' ? '<path d="M18 6 6 18M6 6l12 12"/>' : '<path d="M20 6 9 17l-5-5"/>') + '</svg>';
        const span = document.createElement('span');
        span.textContent = msg;
        el.appendChild(span);
        if (link) { const a = document.createElement('a'); a.href = link.href; a.textContent = link.text; el.appendChild(a); }
        wrap.appendChild(el);
        setTimeout(() => { el.style.transition = 'opacity .3s, transform .3s'; el.style.opacity = '0'; el.style.transform = 'translateY(8px)'; }, 3400);
        setTimeout(() => el.remove(), 3800);
    };
    const bump = (sel, count) => $$(sel).forEach((c) => {
        c.textContent = count;
        if (!c.closest('.cart-pill')) c.hidden = !count;
        c.classList.remove('bump'); void c.offsetWidth; c.classList.add('bump');
    });

    /* ---------- الهيدر ---------- */
    const header = $('#header');
    const toTop = $('#toTop');
    const onScroll = () => {
        const y = window.scrollY;
        header && header.classList.toggle('scrolled', y > 8);
        toTop && toTop.classList.toggle('show', y > 900);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
    toTop && toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

    // القوايم المنسدلة (للمس والكيبورد)
    const closeMenus = (except) => $$('[data-menu].open').forEach((m) => { if (m !== except) { m.classList.remove('open'); $('button', m)?.setAttribute('aria-expanded', 'false'); } });
    $$('[data-menu] > button').forEach((b) => b.addEventListener('click', (e) => {
        e.stopPropagation();
        const li = b.parentElement;
        const open = !li.classList.contains('open');
        closeMenus(li);
        li.classList.toggle('open', open);
        b.setAttribute('aria-expanded', open ? 'true' : 'false');
    }));
    document.addEventListener('click', (e) => { if (!e.target.closest('[data-menu]')) closeMenus(); });

    /* ---------- القائمة الجانبية ---------- */
    const drawer = $('#drawer');
    const setDrawer = (open) => {
        if (!drawer) return;
        drawer.classList.toggle('open', open);
        drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
        lockScroll(open);
    };
    $$('[data-open-drawer]').forEach((b) => b.addEventListener('click', () => setDrawer(true)));
    $$('[data-close-drawer], [data-close-drawer-link]').forEach((b) => b.addEventListener('click', () => setDrawer(false)));

    /* ---------- البحث ---------- */
    const sp = $('#search-panel');
    const sInput = $('#search-q');
    const sList = $('[data-suggest]');
    const setSearch = (open) => {
        if (!sp) return;
        sp.classList.toggle('open', open);
        sp.setAttribute('aria-hidden', open ? 'false' : 'true');
        lockScroll(open);
        if (open) setTimeout(() => sInput && sInput.focus(), 120);
    };
    $$('[data-open-search]').forEach((b) => b.addEventListener('click', () => setSearch(true)));
    $$('[data-close-search]').forEach((b) => b.addEventListener('click', () => setSearch(false)));
    let sTimer, sReq = 0, sActive = -1;
    const renderSuggest = (d) => {
        if (!d.items.length) { sList.innerHTML = '<p class="suggest-empty">مفيش نتايج مطابقة. جرّب كلمة تانية.</p>'; return; }
        sList.innerHTML = d.items.map((it) => '<a href="' + esc(it.url) + '"><img src="' + esc(it.image) + '" alt=""><span>' + esc(it.name) +
            (it.category ? '<small>' + esc(it.category) + '</small>' : '') + '</span><b>' + esc(it.price) + '</b></a>').join('') +
            '<a href="' + esc(d.all) + '" style="grid-template-columns:1fr"><span class="link-arrow">كل النتايج</span></a>';
        sActive = -1;
    };
    sInput && sInput.addEventListener('input', () => {
        clearTimeout(sTimer);
        const q = sInput.value.trim();
        if (q.length < 2) { sList.innerHTML = '<p class="suggest-empty">اكتب حرفين على الأقل علشان تظهر النتائج.</p>'; return; }
        sTimer = setTimeout(async () => {
            const id = ++sReq;
            try {
                const r = await fetch(sList.dataset.endpoint + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } });
                const d = await r.json();
                if (id === sReq) renderSuggest(d);
            } catch (e) { /* البحث العادي لسه شغال */ }
        }, 220);
    });
    sInput && sInput.addEventListener('keydown', (e) => {
        const links = $$('a', sList);
        if (!links.length || !['ArrowDown', 'ArrowUp', 'Enter'].includes(e.key)) return;
        if (e.key === 'Enter' && sActive >= 0) { e.preventDefault(); links[sActive].click(); return; }
        if (e.key === 'Enter') return;
        e.preventDefault();
        sActive = (sActive + (e.key === 'ArrowDown' ? 1 : -1) + links.length) % links.length;
        links.forEach((l, i) => l.classList.toggle('active', i === sActive));
    });

    /* ---------- المودال ---------- */
    const openModal = (m) => { if (!m) return; setDrawer(false); m.classList.add('open'); m.setAttribute('aria-hidden', 'false'); lockScroll(true); setTimeout(() => $('select, button', m)?.focus(), 100); };
    const closeModal = (m) => {
        m.classList.remove('open'); m.setAttribute('aria-hidden', 'true'); lockScroll(false);
        if (m.hasAttribute('data-welcome')) store.set('qn_welcome_v1', '1');
    };
    $$('[data-open-modal]').forEach((b) => b.addEventListener('click', (e) => { e.preventDefault(); openModal(document.getElementById(b.dataset.openModal)); }));
    $$('.modal').forEach((m) => $$('[data-close-modal]', m).forEach((b) => b.addEventListener('click', () => closeModal(m))));
    const welcome = $('[data-welcome]');
    if (welcome && !store.get('qn_welcome_v1')) setTimeout(() => openModal(welcome), 900);
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        setDrawer(false); setSearch(false); closeMenus(); setSheet(false);
        $$('.modal.open').forEach(closeModal);
        $$('[data-lightbox].open').forEach((l) => l.classList.remove('open'));
    });
    $$('[data-location-form]').forEach((f) => f.addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const d = await post(f.action, new FormData(f));
            if (d.ok) {
                $$('[data-location-label]').forEach((l) => { l.textContent = d.governorate || 'اختر محافظتك'; });
                closeModal(f.closest('.modal'));
                if ($('#buy-form') || $('.pi-box')) location.reload(); else toast('التوصيل إلى ' + d.governorate);
            }
        } catch (err) { f.submit(); }
    }));

    /* ---------- الهيرو ---------- */
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
            if (dots[i]) { void dots[i].offsetWidth; dots[i].classList.add('is-active'); }
        };
        const play = () => { clearInterval(timer); timer = setInterval(() => go(i + 1), 6500); };
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
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) play();
    });

    /* ---------- الكاروسيل (RTL) ---------- */
    const initCarousel = (car) => {
        const track = $('[data-car-track]', car);
        const prev = $('[data-car-prev]', car);
        const next = $('[data-car-next]', car);
        if (!track) return;
        const step = () => Math.max(track.clientWidth * 0.75, 220);
        next && next.addEventListener('click', () => track.scrollBy({ left: -step() }));
        prev && prev.addEventListener('click', () => track.scrollBy({ left: step() }));
        const update = () => {
            const max = track.scrollWidth - track.clientWidth;
            const pos = Math.abs(track.scrollLeft);
            if (prev) { prev.disabled = pos < 4; prev.hidden = max <= 4; }
            if (next) { next.disabled = pos > max - 4; next.hidden = max <= 4; }
        };
        track.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
        car._update = update;
    };
    $$('[data-carousel]').forEach(initCarousel);

    /* ---------- التبويبات ---------- */
    $$('[data-tabs]').forEach((t) => {
        $$('[data-tab]', t).forEach((b) => b.addEventListener('click', () => {
            $$('[data-tab]', t).forEach((x) => { x.classList.toggle('active', x === b); x.setAttribute('aria-selected', x === b ? 'true' : 'false'); });
            $$('[data-panel]', t).forEach((p) => {
                const on = p.dataset.panel === b.dataset.tab;
                p.classList.toggle('active', on);
                if (on) $$('[data-carousel]', p).forEach((c) => c._update && c._update());
            });
        }));
    });

    /* ---------- اختار الفلتر المناسب ---------- */
    $$('[data-finder]').forEach((f) => {
        let products = {};
        try { products = JSON.parse(f.dataset.products || '{}'); } catch (e) { products = {}; }
        const order = ['source', 'tds', 'pressure', 'family'];
        const qs = order.map((k) => $('[data-q="' + k + '"]', f));
        const bars = $$('.finder-steps i', f);
        const result = $('[data-result]', f);
        const ans = {};
        let step = 0;
        const show = (n) => {
            step = n;
            qs.forEach((q, i) => q && q.classList.toggle('active', i === n));
            result.classList.toggle('active', n >= qs.length);
            bars.forEach((b, i) => b.classList.toggle('done', i < n || n >= qs.length));
        };
        const pick = () => {
            const has = (s) => products[s] ? s : null;
            const first = Object.keys(products)[0];
            if (ans.source === 'well') return [has('jumbo-20-triple') || has('water-softener') || first, 'مياه الآبار غالباً فيها رواسب وعسر عالي. ابدأ بفلتر جامبو للبيت كله يحمي المواسير والسخانات، وبعده فلتر شرب في المطبخ.'];
            if (ans.pressure === 'weak') return [has('ro-7-pump') || first, 'ضغط المياه الضعيف بيقلل إنتاج الفلتر. الموديل ده فيه موتور رفع ضغط علشان الإنتاج يفضل ثابت حتى في الأدوار العالية.'];
            if (ans.tds === 'high' || ans.family === 'large') return [has('ro-7-taiwan') || first, 'نسبة أملاح عالية أو استهلاك كبير محتاجين 7 مراحل بممبرين 75 جالون — بيشيل لحد 99% من الأملاح ويكفي الأسرة الكبيرة.'];
            if (ans.tds === 'low' && ans.family === 'small') return [has('ro-5-classic') || first, 'مياهك كويسة نسبياً والاستهلاك بسيط، فـ 5 مراحل كفاية تماماً وبسعر اقتصادي.'];
            if (ans.tds === 'mid') return [has('ro-7-alkaline') || has('ro-7-taiwan') || first, 'نسبة أملاح متوسطة: 7 مراحل مع مرحلة ألكالاين بتنقي المياه وترجعلها المعادن المفيدة وتظبط الـ pH.'];
            return [has('ro-7-taiwan') || first, 'الاختيار الأكثر أماناً لأغلب البيوت المصرية: 7 مراحل بمكونات أصلية، وفنينا هيقيسلك الأملاح وقت التركيب.'];
        };
        const showResult = () => {
            const [slug, why] = pick();
            const p = products[slug];
            if (!p) { $('[data-r-why]', f).textContent = 'كلمنا على واتساب وهنرشحلك الأنسب.'; show(qs.length); return; }
            $('[data-r-img]', f).src = p.image;
            $('[data-r-img]', f).alt = p.name;
            $('[data-r-name]', f).textContent = p.name;
            $('[data-r-price]', f).textContent = p.price;
            $('[data-r-why]', f).textContent = why;
            $('[data-r-link]', f).href = p.url;
            show(qs.length);
        };
        qs.forEach((q, i) => {
            $$('.finder-opt', q).forEach((o) => o.addEventListener('click', () => {
                ans[order[i]] = o.dataset.v;
                if (i + 1 < qs.length) show(i + 1); else showResult();
            }));
            $('[data-back]', q)?.addEventListener('click', () => show(Math.max(0, i - 1)));
        });
        $('[data-restart]', f)?.addEventListener('click', () => show(0));
        show(0);
    });

    /* ---------- السلة ---------- */
    $$('form[data-ajax-cart]').forEach((form) => {
        form.addEventListener('submit', async (e) => {
            if (e.submitter && e.submitter.hasAttribute('data-no-ajax')) return; // اشتري الآن
            e.preventDefault();
            const btn = e.submitter || form.querySelector('button[type=submit]');
            btn && (btn.disabled = true);
            try {
                const d = await post(form.action, new FormData(form));
                if (d.ok) {
                    bump('[data-cart-count]', d.count);
                    toast(d.message, 'success', { href: window.APP.cartUrl, text: 'عرض السلة' });
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

    /* ---------- فلاتر المتجر ---------- */
    $$('[data-filter-form]').forEach((f) => f.addEventListener('submit', () => {
        $$('input', f).forEach((i) => { if ((i.type === 'number' || i.type === 'hidden') && i.value === '') i.disabled = true; });
    }));
    $$('[data-sort-select]').forEach((s) => s.addEventListener('change', () => { window.location = s.value; }));
    const side = $('#shop-filters');
    let sheetBg = null;
    function setSheet(open) {
        if (!side) return;
        side.classList.toggle('open', open);
        if (open && !sheetBg) {
            sheetBg = document.createElement('div');
            sheetBg.className = 'sheet-backdrop';
            sheetBg.addEventListener('click', () => setSheet(false));
            document.body.appendChild(sheetBg);
        } else if (!open && sheetBg) { sheetBg.remove(); sheetBg = null; }
        lockScroll(open);
    }
    $$('[data-toggle-filters]').forEach((b) => b.addEventListener('click', () => setSheet(true)));
    $$('[data-close-filters]').forEach((b) => b.addEventListener('click', () => setSheet(false)));

    /* ---------- معرض الصور ---------- */
    $$('[data-gallery]').forEach((g) => {
        const main = $('[data-gallery-main]', g);
        $$('.gallery-thumbs button', g).forEach((b) => b.addEventListener('click', () => {
            main.src = b.dataset.src;
            $$('.gallery-thumbs button', g).forEach((x) => x.classList.toggle('active', x === b));
        }));
        const lb = $('[data-lightbox]');
        $('[data-zoom]', g)?.addEventListener('click', () => { if (!lb) return; $('img', lb).src = main.src; lb.classList.add('open'); });
        lb && lb.addEventListener('click', () => lb.classList.remove('open'));
    });

    /* ---------- صفحة المنتج: التنقل بين الأقسام + الشراء الثابت ---------- */
    const subnav = $('[data-subnav]');
    if (subnav && 'IntersectionObserver' in window) {
        const links = $$('a', subnav);
        const io = new IntersectionObserver((entries) => entries.forEach((en) => {
            if (!en.isIntersecting) return;
            links.forEach((l) => l.classList.toggle('active', l.getAttribute('href') === '#' + en.target.id));
        }), { rootMargin: '-40% 0px -55% 0px' });
        links.forEach((l) => { const s = $(l.getAttribute('href')); s && io.observe(s); });
    }
    const sticky = $('[data-sticky-buy]');
    const buyForm = $('#buy-form') || $('.pi-price');
    if (sticky && buyForm) {
        const check = () => sticky.classList.toggle('show', buyForm.getBoundingClientRect().bottom < 0);
        window.addEventListener('scroll', check, { passive: true });
        check();
    }
    $$('[data-buy-now]').forEach((b) => b.addEventListener('click', () => {
        const btn = $('#buy-form [name=buy_now]');
        if (btn) btn.click(); else window.scrollTo({ top: 0, behavior: 'smooth' });
    }));

    /* ---------- مشاركة ---------- */
    $$('[data-share]').forEach((b) => b.addEventListener('click', async () => {
        const data = { title: b.dataset.title || document.title, url: location.href };
        if (navigator.share) { try { await navigator.share(data); } catch (e) { /* ألغى */ } return; }
        try { await navigator.clipboard.writeText(location.href); toast('اتنسخ رابط المنتج'); } catch (e) { toast('انسخ الرابط من شريط العنوان'); }
    }));

    /* ---------- عداد العروض (لحد آخر اليوم) ---------- */
    $$('[data-countdown]').forEach((c) => {
        const h = $('[data-h]', c), m = $('[data-m]', c), s = $('[data-s]', c);
        const pad = (n) => String(n).padStart(2, '0');
        const tick = () => {
            const now = new Date(), end = new Date(now);
            end.setHours(23, 59, 59, 999);
            let d = Math.max(0, Math.floor((end - now) / 1000));
            h.textContent = pad(Math.floor(d / 3600)); d %= 3600;
            m.textContent = pad(Math.floor(d / 60));
            s.textContent = pad(d % 60);
        };
        tick(); setInterval(tick, 1000);
    });

    /* ---------- عداد الأرقام ---------- */
    const counters = $$('[data-count]');
    if (counters.length && 'IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const io = new IntersectionObserver((entries) => entries.forEach((en) => {
            if (!en.isIntersecting) return;
            io.unobserve(en.target);
            const el = en.target, target = +el.dataset.count, prefix = el.textContent.trim().startsWith('+') ? '+' : '';
            const t0 = performance.now();
            const tick = (t) => {
                const p = Math.min(1, (t - t0) / 1400);
                el.textContent = prefix + Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString('en-US');
                if (p < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        }), { threshold: .5 });
        counters.forEach((c) => io.observe(c));
    }

    $$('[data-autohide]').forEach((a) => setTimeout(() => { a.style.transition = 'opacity .4s'; a.style.opacity = '0'; setTimeout(() => a.remove(), 400); }, 5000));
})();
