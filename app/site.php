<?php
/**
 * صفحات الموقع العامة
 */

function not_found(): never
{
    http_response_code(404);
    render('site/404', ['title' => 'الصفحة غير موجودة']);
}

/** الأقسام الرئيسية وتحت كل قسم أقسامه الفرعية */
function nav_categories(): array
{
    static $tree = null;
    if ($tree !== null) return $tree;
    $all = rows('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) AS products_count
                 FROM categories c WHERE c.is_active = 1 ORDER BY c.sort_order, c.id');
    $tree = [];
    foreach ($all as $c) if (!$c['parent_id']) $tree[$c['id']] = $c + ['children' => []];
    foreach ($all as $c) {
        if ($c['parent_id'] && isset($tree[$c['parent_id']])) {
            $tree[$c['parent_id']]['children'][] = $c;
            $tree[$c['parent_id']]['products_count'] += $c['products_count'];
        }
    }
    return $tree = array_values($tree);
}

function nav_brands(): array
{
    static $b = null;
    return $b ??= rows('SELECT * FROM brands WHERE is_active = 1 ORDER BY sort_order, id');
}

function product_select(): string
{
    return 'SELECT p.*, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name, b.slug AS brand_slug
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            LEFT JOIN brands b ON b.id = p.brand_id';
}

/** رقم القسم + أرقام أقسامه الفرعية */
function category_ids_with_children(array $ids): array
{
    $ids = array_values(array_unique(array_map('intval', $ids)));
    if (!$ids) return [];
    $in = implode(',', $ids);
    $children = array_map('intval', array_column(rows("SELECT id FROM categories WHERE parent_id IN ($in)"), 'id'));
    return array_values(array_unique(array_merge($ids, $children)));
}

function banners(string $position): array
{
    static $all = null;
    if ($all === null) {
        $all = [];
        foreach (rows('SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order, id') as $b) $all[$b['position']][] = $b;
    }
    return $all[$position] ?? [];
}

function site_home(): never
{
    $showcase = row('SELECT * FROM categories WHERE slug = ? AND is_active = 1', [setting('home_showcase_category')]);
    render('site/home', [
        'slides'      => rows('SELECT * FROM slides WHERE is_active = 1 ORDER BY sort_order, id'),
        'categories'  => nav_categories(),
        'featured'    => rows(product_select() . ' WHERE p.is_active = 1 AND p.is_featured = 1 ORDER BY p.sales_count DESC LIMIT 12'),
        'topSelling'  => rows(product_select() . ' WHERE p.is_active = 1 ORDER BY p.sales_count DESC LIMIT 12'),
        'newest'      => rows(product_select() . ' WHERE p.is_active = 1 ORDER BY p.created_at DESC, p.id DESC LIMIT 12'),
        'offers'      => rows(product_select() . ' WHERE p.is_active = 1 AND p.old_price > p.price ORDER BY (p.old_price - p.price) / p.old_price DESC LIMIT 12'),
        'plants'      => rows(product_select() . " WHERE p.is_active = 1 AND c.slug = 'ro-plants' ORDER BY p.price LIMIT 2"),
        // ترشيحات "اختار الفلتر المناسب" — لو منتج اتمسح بيتشال من الترشيحات تلقائياً
        'finder'      => array_column(rows(product_select() . " WHERE p.is_active = 1 AND p.slug IN ('ro-7-taiwan','ro-7-pump','ro-5-classic','jumbo-20-triple','water-softener','ro-7-alkaline')"), null, 'slug'),
        'showcase'    => $showcase,
        'showcaseSub' => $showcase ? rows('SELECT * FROM categories WHERE parent_id = ? AND is_active = 1 ORDER BY sort_order, id', [$showcase['id']]) : [],
        'brands'      => nav_brands(),
        'posts'       => rows('SELECT * FROM posts WHERE is_published = 1 ORDER BY created_at DESC LIMIT 4'),
        'welcome'     => setting('welcome_enabled', '1') === '1',
    ]);
}

/* ---------------- المتجر ---------------- */

function shop_filters(): array
{
    $cats = $_GET['cat'] ?? [];
    return [
        'q'     => input('q'),
        'sort'  => input('sort', 'popular'),
        'min'   => normalize_digits((string)input('min')),
        'max'   => normalize_digits((string)input('max')),
        'page'  => (int)input('page', 1),
        'cat'   => array_values(array_filter(is_array($cats) ? $cats : [$cats], 'is_string')),
        'brand' => is_string($_GET['brand'] ?? null) ? $_GET['brand'] : '',
        'stock' => input('stock') === '1',
    ];
}

function shop_query(array $f): array
{
    $where = ['p.is_active = 1'];
    $params = [];

    $catIds = $f['category_ids'] ?? [];
    if ($f['cat']) {
        $in = implode(',', array_fill(0, count($f['cat']), '?'));
        $catIds = array_merge($catIds, array_column(rows("SELECT id FROM categories WHERE slug IN ($in)", $f['cat']), 'id'));
    }
    if ($catIds) {
        $ids = category_ids_with_children($catIds);
        $where[] = 'p.category_id IN (' . implode(',', $ids) . ')';
    }
    if (!empty($f['brand_id'])) {
        $where[] = 'p.brand_id = ?';
        $params[] = $f['brand_id'];
    } elseif ($f['brand'] !== '') {
        $where[] = 'b.slug = ?';
        $params[] = $f['brand'];
    }
    if (!empty($f['offers'])) $where[] = 'p.old_price > p.price';
    if ($f['stock']) $where[] = 'p.stock > 0';
    if ($f['q'] !== '') {
        $where[] = '(p.name LIKE ? OR p.short_description LIKE ? OR c.name LIKE ? OR b.name LIKE ?)';
        array_push($params, "%{$f['q']}%", "%{$f['q']}%", "%{$f['q']}%", "%{$f['q']}%");
    }
    if ($f['min'] !== '' && is_numeric($f['min'])) {
        $where[] = 'p.price >= ?';
        $params[] = (float)$f['min'];
    }
    if ($f['max'] !== '' && is_numeric($f['max'])) {
        $where[] = 'p.price <= ?';
        $params[] = (float)$f['max'];
    }
    $sorts = [
        'popular'    => 'p.sales_count DESC',
        'newest'     => 'p.created_at DESC, p.id DESC',
        'price_asc'  => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'rating'     => 'p.rating_avg DESC, p.rating_count DESC',
    ];
    $sort = $sorts[$f['sort']] ?? $sorts['popular'];

    $perPage = 12;
    $page = max(1, $f['page']);
    $sqlWhere = ' WHERE ' . implode(' AND ', $where);
    $from = ' FROM products p LEFT JOIN categories c ON c.id = p.category_id LEFT JOIN brands b ON b.id = p.brand_id';
    $total = (int)val('SELECT COUNT(*)' . $from . $sqlWhere, $params);
    $items = rows(product_select() . $sqlWhere . " ORDER BY p.stock > 0 DESC, $sort LIMIT $perPage OFFSET " . (($page - 1) * $perPage), $params);

    return ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => max(1, (int)ceil($total / $perPage))];
}

function site_shop(): never
{
    $f = shop_filters();
    $title = $f['q'] !== '' ? 'نتائج البحث عن: ' . $f['q'] : 'كل المنتجات';
    render('site/shop', ['title' => $title, 'heading' => $title, 'filters' => $f, 'result' => shop_query($f), 'base' => $f['q'] !== '' ? '/search' : '/shop']);
}

function site_offers(): never
{
    $f = shop_filters();
    render('site/shop', ['title' => 'تخفيضات', 'heading' => 'التخفيضات والعروض', 'filters' => $f, 'result' => shop_query($f + ['offers' => true]), 'base' => '/offers', 'is_offers' => true]);
}

function site_category(string $slug): never
{
    $cat = row('SELECT * FROM categories WHERE slug = ? AND is_active = 1', [$slug]);
    if (!$cat) not_found();
    if ($slug === 'offers') redirect('/offers');
    $f = shop_filters();
    $parent = $cat['parent_id'] ? row('SELECT * FROM categories WHERE id = ?', [$cat['parent_id']]) : null;
    render('site/shop', [
        'title' => $cat['name'], 'heading' => $cat['name'], 'filters' => $f, 'category' => $cat, 'parent' => $parent,
        'result' => shop_query($f + ['category_ids' => [$cat['id']]]), 'base' => '/category/' . $cat['slug'],
        'description' => $cat['description'],
    ]);
}

/** اقتراحات البحث الفوري (JSON) */
function site_search_suggest(): never
{
    $term = trim((string)input('q'));
    $items = [];
    if (mb_strlen($term) >= 2) {
        foreach (rows(product_select() . ' WHERE p.is_active = 1 AND (p.name LIKE ? OR c.name LIKE ? OR b.name LIKE ?) ORDER BY p.stock > 0 DESC, p.sales_count DESC LIMIT 6', ["%$term%", "%$term%", "%$term%"]) as $p) {
            $items[] = ['name' => $p['name'], 'url' => url('/product/' . $p['slug']), 'image' => img($p['image']), 'price' => money($p['price']), 'category' => $p['category_name']];
        }
    }
    json_out(['ok' => true, 'items' => $items, 'all' => url('/search?q=' . urlencode($term))]);
}

function site_brands(): never
{
    render('site/brands', [
        'title'  => 'الماركات',
        'brands' => rows('SELECT b.*, (SELECT COUNT(*) FROM products p WHERE p.brand_id = b.id AND p.is_active = 1) products_count FROM brands b WHERE b.is_active = 1 ORDER BY sort_order, id'),
    ]);
}

function site_brand(string $slug): never
{
    $brand = row('SELECT * FROM brands WHERE slug = ? AND is_active = 1', [$slug]);
    if (!$brand) not_found();
    $f = shop_filters();
    render('site/shop', [
        'title' => 'منتجات ' . $brand['name'], 'heading' => 'منتجات ' . $brand['name'], 'filters' => $f, 'brand' => $brand,
        'result' => shop_query($f + ['brand_id' => $brand['id']]), 'base' => '/brand/' . $brand['slug'],
    ]);
}

function site_product(string $slug): never
{
    $p = row(product_select() . ' WHERE p.slug = ? AND p.is_active = 1', [$slug]);
    if (!$p) not_found();
    q('UPDATE products SET views = views + 1 WHERE id = ?', [$p['id']]);
    $related = rows(product_select() . ' WHERE p.is_active = 1 AND p.id != ? AND p.category_id IS ? ORDER BY p.stock > 0 DESC, p.sales_count DESC LIMIT 8', [$p['id'], $p['category_id']]);
    if (count($related) < 6) {
        $in = implode(',', array_map('intval', array_merge([$p['id']], array_column($related, 'id'))));
        $related = array_merge($related, rows(product_select() . " WHERE p.is_active = 1 AND p.id NOT IN ($in) ORDER BY p.is_featured DESC, p.sales_count DESC LIMIT " . (8 - count($related))));
    }
    $parent = null;
    if ($p['category_id']) {
        $cat = row('SELECT * FROM categories WHERE id = ?', [$p['category_id']]);
        if ($cat && $cat['parent_id']) $parent = row('SELECT * FROM categories WHERE id = ?', [$cat['parent_id']]);
    }
    $gallery = array_values(array_filter(array_merge([$p['image']], json_decode($p['gallery'] ?? '[]', true) ?: [])));
    render('site/product', [
        'title'       => $p['name'],
        'description' => $p['short_description'],
        'og_image'    => $p['image'],
        'p'           => $p,
        'parent'      => $parent,
        'gallery'     => $gallery,
        'specs'       => parse_specs($p['specs']),
        'faqs'        => parse_faqs($p['faqs']),
        'reviews'     => rows('SELECT * FROM reviews WHERE product_id = ? AND is_approved = 1 ORDER BY id DESC LIMIT 20', [$p['id']]),
        'related'     => $related,
        'errors'      => $_SESSION['_review_errors'] ?? [],
    ]);
}

function site_review(string $slug): never
{
    $p = row('SELECT id, slug FROM products WHERE slug = ? AND is_active = 1', [$slug]);
    if (!$p) not_found();
    $c = customer();
    $data = [
        'product_id'  => $p['id'],
        'customer_id' => $c['id'] ?? null,
        'name'        => $c['name'] ?? input('name'),
        'rating'      => max(1, min(5, (int)input('rating', 5))),
        'comment'     => mb_substr((string)input('comment'), 0, 1000),
    ];
    $errors = [];
    if (mb_strlen($data['name']) < 2) $errors['name'] = 'اكتب اسمك.';
    if (mb_strlen($data['comment']) < 3) $errors['comment'] = 'اكتب رأيك في المنتج.';
    if ($errors) {
        $_SESSION['_review_errors'] = $errors;
        redirect('/product/' . $p['slug'] . '#reviews');
    }
    unset($_SESSION['_review_errors']);
    insert('reviews', $data);
    flash('success', 'شكراً لتقييمك! هيظهر بعد مراجعته.');
    redirect('/product/' . $p['slug'] . '#reviews');
}

/* ---------------- السلة ---------------- */

function site_cart(): never
{
    render('site/cart', ['title' => 'سلة التسوق', 'cart' => cart_details()]);
}

function cart_response(string $message): never
{
    if (wants_json()) {
        $d = cart_details();
        json_out(['ok' => true, 'message' => $message, 'count' => cart_count(), 'subtotal' => money($d['subtotal']), 'total' => money($d['total'])]);
    }
    flash('success', $message);
    back('/cart');
}

function site_cart_add(): never
{
    $id = (int)input('product_id');
    $qty = max(1, (int)input('qty', 1));
    $p = row('SELECT id, name, stock FROM products WHERE id = ? AND is_active = 1', [$id]);
    if (!$p) {
        wants_json() ? json_out(['ok' => false, 'message' => 'المنتج غير متاح.'], 404) : not_found();
    }
    if ((int)$p['stock'] <= 0) {
        if (wants_json()) json_out(['ok' => false, 'message' => 'المنتج غير متوفر حالياً.'], 422);
        flash('error', 'المنتج غير متوفر حالياً.');
        back();
    }
    cart_set($id, min((cart()[$id] ?? 0) + $qty, (int)$p['stock']));
    if (input('buy_now')) redirect('/checkout');
    cart_response('تمت إضافة "' . $p['name'] . '" للسلة');
}

function site_cart_update(): never
{
    $items = $_POST['qty'] ?? [];
    if (is_array($items)) {
        foreach ($items as $id => $qty) cart_set((int)$id, (int)$qty);
    }
    if ($rid = (int)input('remove')) cart_set($rid, 0);
    cart_details();
    cart_response('تم تحديث السلة');
}

/* ---------------- إتمام الطلب ---------------- */

function site_checkout(): never
{
    $cart = cart_details();
    if (!$cart['items']) {
        flash('error', 'السلة فاضية، ضيف منتجات الأول.');
        redirect('/cart');
    }
    $c = customer();
    $errors = [];
    if (is_post()) {
        $data = [
            'customer_name'  => input('customer_name'),
            'phone'          => normalize_digits(input('phone')),
            'phone2'         => normalize_digits(input('phone2')),
            'governorate'    => input('governorate'),
            'city'           => input('city'),
            'address'        => input('address'),
            'notes'          => input('notes'),
            'payment_method' => input('payment_method', 'cod'),
        ];
        if (mb_strlen($data['customer_name']) < 3) $errors['customer_name'] = 'اكتب الاسم بالكامل.';
        if (!valid_phone($data['phone'])) $errors['phone'] = 'رقم الموبايل غير صحيح.';
        if ($data['phone2'] !== '' && !valid_phone($data['phone2'])) $errors['phone2'] = 'رقم الموبايل الإضافي غير صحيح.';
        if (!in_array($data['governorate'], governorates(), true)) $errors['governorate'] = 'اختار المحافظة.';
        if (mb_strlen($data['city']) < 2) $errors['city'] = 'اكتب المدينة / المنطقة.';
        if (mb_strlen($data['address']) < 5) $errors['address'] = 'اكتب العنوان بالتفصيل.';
        if (!isset(payment_methods()[$data['payment_method']])) $data['payment_method'] = 'cod';

        if (!$errors) {
            $pdo = db();
            $pdo->beginTransaction();
            try {
                foreach ($cart['items'] as $it) {
                    $stock = (int)val('SELECT stock FROM products WHERE id = ?', [$it['product']['id']]);
                    if ($stock < $it['qty']) throw new RuntimeException('الكمية المطلوبة من "' . $it['product']['name'] . '" مش متاحة دلوقتي.');
                }
                do {
                    $code = 'QN' . date('ymd') . random_int(1000, 9999);
                } while (val('SELECT COUNT(*) FROM orders WHERE code = ?', [$code]));

                $orderId = insert('orders', $data + [
                    'code'        => $code,
                    'customer_id' => $c['id'] ?? null,
                    'subtotal'    => $cart['subtotal'],
                    'shipping'    => $cart['shipping'],
                    'total'       => $cart['total'],
                    'status'      => 'new',
                ]);
                foreach ($cart['items'] as $it) {
                    insert('order_items', [
                        'order_id'     => $orderId,
                        'product_id'   => $it['product']['id'],
                        'product_name' => $it['product']['name'],
                        'price'        => $it['product']['price'],
                        'qty'          => $it['qty'],
                        'total'        => $it['total'],
                    ]);
                    q('UPDATE products SET stock = stock - ?, sales_count = sales_count + ? WHERE id = ?', [$it['qty'], $it['qty'], $it['product']['id']]);
                }
                // حفظ العنوان في حساب العميل لو مكانش متسجل
                if ($c && !$c['address']) {
                    update('customers', (int)$c['id'], ['governorate' => $data['governorate'], 'city' => $data['city'], 'address' => $data['address']]);
                }
                $pdo->commit();
            } catch (RuntimeException $ex) {
                $pdo->rollBack();
                flash('error', $ex->getMessage());
                redirect('/cart');
            }
            cart_clear();
            $_SESSION['my_orders'][] = $code;
            redirect('/order/' . $code);
        }
    }

    $form = is_post() ? $_POST : [
        'customer_name' => $c['name'] ?? '',
        'phone'         => $c['phone'] ?? '',
        'governorate'   => $c['governorate'] ?? delivery_location(),
        'city'          => $c['city'] ?? '',
        'address'       => $c['address'] ?? '',
    ];
    render('site/checkout', ['title' => 'إتمام الطلب', 'cart' => $cart, 'errors' => $errors, 'form' => $form]);
}

function load_order(string $code): ?array
{
    $o = row('SELECT * FROM orders WHERE code = ?', [$code]);
    if ($o) $o['items'] = rows('SELECT oi.*, p.image, p.slug FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE order_id = ?', [$o['id']]);
    return $o;
}

function site_order_done(string $code): never
{
    if (!in_array($code, $_SESSION['my_orders'] ?? [], true)) redirect('/track?code=' . urlencode($code));
    $o = load_order($code);
    if (!$o) not_found();
    render('site/order-done', ['title' => 'تم استلام طلبك', 'order' => $o]);
}

function site_track(): never
{
    $order = null;
    $error = null;
    $code = strtoupper(trim(normalize_digits((string)input('code'))));
    $phone = normalize_digits((string)input('phone'));
    if (is_post() || ($code !== '' && $phone !== '')) {
        $o = $code !== '' ? load_order($code) : null;
        $clean = fn($p) => substr(preg_replace('/\D+/', '', $p), -10);
        if ($o && $phone !== '' && $clean($o['phone']) === $clean($phone)) $order = $o;
        else $error = 'مفيش طلب بالبيانات دي. اتأكد من رقم الطلب ورقم الموبايل.';
    }
    render('site/track', ['title' => 'تتبع طلبك', 'order' => $order, 'error' => $error, 'code' => $code, 'phone' => $phone]);
}

/* ---------------- الخدمات / التقسيط / التواصل ---------------- */

function site_maintenance_redirect(): never
{
    header('Location: ' . url('/service-request'), true, 301);
    exit;
}

function site_service_request(): never
{
    $errors = [];
    if (is_post()) {
        $data = [
            'name'           => input('name'),
            'phone'          => normalize_digits(input('phone')),
            'governorate'    => input('governorate'),
            'address'        => input('address'),
            'service_type'   => input('service_type'),
            'preferred_date' => input('preferred_date'),
            'notes'          => input('notes'),
        ];
        if (mb_strlen($data['name']) < 3) $errors['name'] = 'اكتب الاسم.';
        if (!valid_phone($data['phone'])) $errors['phone'] = 'رقم الموبايل غير صحيح.';
        if (!in_array($data['governorate'], governorates(), true)) $errors['governorate'] = 'اختار المحافظة.';
        if (!in_array($data['service_type'], service_types(), true)) $errors['service_type'] = 'اختار نوع الخدمة.';
        if ($data['preferred_date'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['preferred_date'])) $data['preferred_date'] = '';
        if (!$errors) {
            insert('service_requests', $data);
            flash('success', 'تم استلام طلبك ✔ فريقنا هيتواصل معاك في أقرب وقت.');
            redirect('/service-request');
        }
    }
    $c = customer();
    $form = is_post() ? $_POST : ['name' => $c['name'] ?? '', 'phone' => $c['phone'] ?? '', 'governorate' => $c['governorate'] ?? delivery_location(), 'address' => $c['address'] ?? ''];
    render('site/service-request', ['title' => 'طلب خدمة صيانة أو تركيب', 'errors' => $errors, 'form' => $form]);
}

function site_installments(): never
{
    $errors = [];
    $product = ($pid = (int)input('product')) ? row('SELECT id, name, price, image, slug FROM products WHERE id = ? AND is_active = 1', [$pid]) : null;
    if (is_post()) {
        $data = [
            'name'         => input('name'),
            'phone'        => normalize_digits(input('phone')),
            'governorate'  => input('governorate'),
            'job'          => input('job'),
            'product_id'   => $product['id'] ?? null,
            'product_name' => $product['name'] ?? input('product_name'),
            'months'       => (int)input('months'),
            'notes'        => input('notes'),
        ];
        if (mb_strlen($data['name']) < 3) $errors['name'] = 'اكتب الاسم بالكامل.';
        if (!valid_phone($data['phone'])) $errors['phone'] = 'رقم الموبايل غير صحيح.';
        if (!in_array($data['governorate'], governorates(), true)) $errors['governorate'] = 'اختار المحافظة.';
        if (mb_strlen((string)$data['product_name']) < 2) $errors['product_name'] = 'اكتب المنتج اللي عايز تقسطه.';
        if (!in_array($data['months'], [3, 4, 6, 12], true)) $errors['months'] = 'اختار مدة التقسيط.';
        if (!$errors) {
            insert('installment_requests', $data);
            flash('success', 'تم استلام طلب التقسيط ✔ هنتواصل معاك خلال 24 ساعة لاستكمال الإجراءات.');
            redirect('/installments');
        }
    }
    $c = customer();
    $form = is_post() ? $_POST : ['name' => $c['name'] ?? '', 'phone' => $c['phone'] ?? '', 'governorate' => $c['governorate'] ?? delivery_location(), 'months' => setting('installment_months', 4)];
    render('site/installments', ['title' => 'تقديم طلب تقسيط', 'errors' => $errors, 'form' => $form, 'product' => $product]);
}

function site_contact(): never
{
    $errors = [];
    if (is_post()) {
        $data = [
            'name'    => input('name'),
            'phone'   => normalize_digits(input('phone')),
            'email'   => input('email'),
            'subject' => input('subject'),
            'message' => input('message'),
        ];
        if (mb_strlen($data['name']) < 2) $errors['name'] = 'اكتب الاسم.';
        if ($data['phone'] === '' && $data['email'] === '') $errors['phone'] = 'اكتب رقم موبايل أو إيميل للتواصل.';
        if ($data['phone'] !== '' && !valid_phone($data['phone'])) $errors['phone'] = 'رقم الموبايل غير صحيح.';
        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'الإيميل غير صحيح.';
        if (mb_strlen($data['message']) < 5) $errors['message'] = 'اكتب رسالتك.';
        if (!$errors) {
            insert('messages', $data);
            flash('success', 'شكراً لتواصلك معانا ✔ هنرد عليك في أقرب وقت.');
            redirect('/contact');
        }
    }
    render('site/contact', ['title' => 'اتصل بنا', 'errors' => $errors, 'form' => $_POST]);
}

function site_about(): never
{
    render('site/about', ['title' => 'من نحن']);
}

function site_faq(): never
{
    render('site/faq', ['title' => 'الأسئلة الشائعة', 'faqs' => rows('SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order, id')]);
}

function site_services(): never
{
    site_page('services');
}

/* ---------------- المدونة والصفحات ---------------- */

function site_blog(): never
{
    $page = max(1, (int)input('page', 1));
    $total = (int)val('SELECT COUNT(*) FROM posts WHERE is_published = 1');
    render('site/blog', [
        'title' => 'المدونة',
        'posts' => rows('SELECT * FROM posts WHERE is_published = 1 ORDER BY created_at DESC LIMIT 9 OFFSET ' . (($page - 1) * 9)),
        'page'  => $page,
        'pages' => max(1, (int)ceil($total / 9)),
    ]);
}

function site_post(string $slug): never
{
    $post = row('SELECT * FROM posts WHERE slug = ? AND is_published = 1', [$slug]);
    if (!$post) not_found();
    q('UPDATE posts SET views = views + 1 WHERE id = ?', [$post['id']]);
    render('site/post', [
        'title'       => $post['title'],
        'description' => $post['excerpt'],
        'og_image'    => $post['image'],
        'post'        => $post,
        'more'        => rows('SELECT * FROM posts WHERE is_published = 1 AND id != ? ORDER BY created_at DESC LIMIT 3', [$post['id']]),
        'products'    => rows(product_select() . ' WHERE p.is_active = 1 AND p.is_featured = 1 ORDER BY p.sales_count DESC LIMIT 4'),
    ]);
}

function site_page(string $slug): never
{
    $page = row('SELECT * FROM pages WHERE slug = ?', [$slug]);
    if (!$page) not_found();
    render('site/page', ['title' => $page['title'], 'page' => $page]);
}

/* ---------------- النشرة / الموقع ---------------- */

function site_newsletter(): never
{
    $email = strtolower(trim((string)input('email')));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'اكتب إيميل صحيح.');
    } elseif (!input('agree')) {
        flash('error', 'لازم توافق على سياسة الخصوصية والشروط.');
    } else {
        q('INSERT OR IGNORE INTO subscribers (email, created_at) VALUES (?, ?)', [$email, date('Y-m-d H:i:s')]);
        flash('success', 'تم اشتراكك في النشرة البريدية ✔');
    }
    back('/');
}

function site_location(): never
{
    $g = input('governorate');
    if (in_array($g, governorates(), true)) {
        setcookie('delivery_gov', $g, ['expires' => time() + 86400 * 365, 'path' => '/', 'samesite' => 'Lax']);
        $_COOKIE['delivery_gov'] = $g;
    }
    if (wants_json()) json_out(['ok' => true, 'governorate' => delivery_location()]);
    back('/');
}

/* ---------------- المفضلة ---------------- */

function site_wishlist(): never
{
    $ids = wishlist_ids();
    $items = $ids ? rows(product_select() . ' WHERE p.is_active = 1 AND p.id IN (' . implode(',', $ids) . ') ORDER BY p.name') : [];
    render('site/wishlist', ['title' => 'قائمة المفضلة', 'items' => $items]);
}

function site_wishlist_toggle(): never
{
    $id = (int)input('product_id');
    if (!val('SELECT COUNT(*) FROM products WHERE id = ?', [$id])) {
        wants_json() ? json_out(['ok' => false], 404) : back();
    }
    $on = !in_wishlist($id);
    if ($c = customer()) {
        $on ? q('INSERT OR IGNORE INTO wishlist (customer_id, product_id, created_at) VALUES (?, ?, ?)', [$c['id'], $id, date('Y-m-d H:i:s')])
            : q('DELETE FROM wishlist WHERE customer_id = ? AND product_id = ?', [$c['id'], $id]);
    } else {
        $list = array_map('intval', $_SESSION['wishlist'] ?? []);
        $list = $on ? array_values(array_unique([...$list, $id])) : array_values(array_diff($list, [$id]));
        $_SESSION['wishlist'] = $list;
    }
    $msg = $on ? 'اتضاف للمفضلة' : 'اتشال من المفضلة';
    if (wants_json()) {
        $count = customer() ? (int)val('SELECT COUNT(*) FROM wishlist WHERE customer_id = ?', [customer()['id']]) : count($_SESSION['wishlist'] ?? []);
        json_out(['ok' => true, 'on' => $on, 'message' => $msg, 'count' => $count]);
    }
    flash('success', $msg);
    back('/wishlist');
}

/* ---------------- حساب العميل ---------------- */

function login_customer(array $c): void
{
    session_regenerate_id(true);
    $_SESSION['customer_id'] = (int)$c['id'];
    q('UPDATE customers SET last_login = ? WHERE id = ?', [date('Y-m-d H:i:s'), $c['id']]);
    // نقل مفضلة الزائر للحساب
    foreach (array_map('intval', $_SESSION['wishlist'] ?? []) as $pid) {
        q('INSERT OR IGNORE INTO wishlist (customer_id, product_id, created_at) SELECT ?, id, ? FROM products WHERE id = ?', [$c['id'], date('Y-m-d H:i:s'), $pid]);
    }
    unset($_SESSION['wishlist']);
}

function customer_redirect(): never
{
    $to = $_SESSION['customer_intended'] ?? null;
    unset($_SESSION['customer_intended']);
    if ($to && preg_match('#^/(?![/\\\\])#', $to) && !str_contains($to, '/account/login')) {
        header('Location: ' . $to);
        exit;
    }
    redirect('/account');
}

function site_login(): never
{
    if (customer()) redirect('/account');
    $error = null;
    if (is_post()) {
        $_SESSION['c_attempts'] = array_filter($_SESSION['c_attempts'] ?? [], fn($t) => $t > time() - 900);
        if (count($_SESSION['c_attempts']) >= 8) {
            $error = 'محاولات كتير غلط. استنى شوية وحاول تاني.';
        } else {
            $phone = normalize_digits((string)input('phone'));
            $c = row('SELECT * FROM customers WHERE phone = ?', [$phone]);
            if ($c && password_verify((string)input('password'), $c['password_hash'])) {
                unset($_SESSION['c_attempts']);
                login_customer($c);
                flash('success', 'أهلاً ' . explode(' ', $c['name'])[0] . '! سجلت دخولك بنجاح.');
                customer_redirect();
            }
            $_SESSION['c_attempts'][] = time();
            $error = 'رقم الموبايل أو كلمة المرور غلط.';
        }
    }
    render('site/login', ['title' => 'تسجيل الدخول', 'error' => $error]);
}

function site_register(): never
{
    if (customer()) redirect('/account');
    $errors = [];
    if (is_post()) {
        $data = [
            'name'  => input('name'),
            'phone' => normalize_digits((string)input('phone')),
            'email' => strtolower((string)input('email')),
        ];
        $pw = (string)input('password');
        if (mb_strlen($data['name']) < 3) $errors['name'] = 'اكتب الاسم بالكامل.';
        if (!valid_phone($data['phone'])) $errors['phone'] = 'رقم الموبايل غير صحيح.';
        elseif (val('SELECT COUNT(*) FROM customers WHERE phone = ?', [$data['phone']])) $errors['phone'] = 'الرقم ده متسجل قبل كده — سجّل دخول.';
        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'الإيميل غير صحيح.';
        if (mb_strlen($pw) < 6) $errors['password'] = 'كلمة المرور 6 حروف على الأقل.';
        if (!$errors) {
            $id = insert('customers', $data + ['password_hash' => password_hash($pw, PASSWORD_DEFAULT), 'governorate' => delivery_location()]);
            login_customer(['id' => $id]);
            flash('success', 'أهلاً بيك في ' . setting('site_name') . '! حسابك اتعمل بنجاح.');
            customer_redirect();
        }
    }
    render('site/register', ['title' => 'إنشاء حساب', 'errors' => $errors, 'form' => $_POST]);
}

function site_logout(): never
{
    unset($_SESSION['customer_id']);
    session_regenerate_id(true);
    redirect('/');
}

function site_account(): never
{
    require_customer();
    $c = customer();
    $errors = [];
    if (is_post()) {
        $data = [
            'name'        => input('name'),
            'email'       => strtolower((string)input('email')),
            'governorate' => input('governorate'),
            'city'        => input('city'),
            'address'     => input('address'),
        ];
        if (mb_strlen($data['name']) < 3) $errors['name'] = 'اكتب الاسم بالكامل.';
        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'الإيميل غير صحيح.';
        if ($data['governorate'] !== '' && !in_array($data['governorate'], governorates(), true)) $data['governorate'] = '';
        $newPw = (string)input('new_password');
        if ($newPw !== '') {
            $hash = (string)val('SELECT password_hash FROM customers WHERE id = ?', [$c['id']]);
            if (!password_verify((string)input('current_password'), $hash)) $errors['current_password'] = 'كلمة المرور الحالية غلط.';
            elseif (mb_strlen($newPw) < 6) $errors['new_password'] = 'كلمة المرور 6 حروف على الأقل.';
            else $data['password_hash'] = password_hash($newPw, PASSWORD_DEFAULT);
        }
        if (!$errors) {
            update('customers', (int)$c['id'], $data);
            flash('success', 'تم حفظ بياناتك.');
            redirect('/account?tab=profile');
        }
    }
    $orders = rows('SELECT o.*, (SELECT SUM(qty) FROM order_items WHERE order_id = o.id) items_count FROM orders o WHERE customer_id = ? OR phone = ? ORDER BY id DESC', [$c['id'], $c['phone']]);
    render('site/account', ['title' => 'حسابي', 'c' => $c, 'orders' => $orders, 'errors' => $errors, 'tab' => $errors ? 'profile' : input('tab', 'orders')]);
}
