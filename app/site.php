<?php
/**
 * صفحات الموقع العامة
 */

function not_found(): never
{
    http_response_code(404);
    render('site/404', ['title' => 'الصفحة غير موجودة']);
}

function nav_categories(): array
{
    static $c = null;
    return $c ??= rows('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) AS products_count
                        FROM categories c WHERE c.is_active = 1 ORDER BY c.sort_order, c.id');
}

function product_select(): string
{
    return 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p LEFT JOIN categories c ON c.id = p.category_id';
}

function site_home(): never
{
    render('site/home', [
        'slides'       => rows('SELECT * FROM slides WHERE is_active = 1 ORDER BY sort_order, id'),
        'categories'   => nav_categories(),
        'featured'     => rows(product_select() . ' WHERE p.is_active = 1 AND p.is_featured = 1 ORDER BY p.sales_count DESC LIMIT 8'),
        'offers'       => rows(product_select() . ' WHERE p.is_active = 1 AND p.old_price > p.price ORDER BY (p.old_price - p.price) / p.old_price DESC LIMIT 4'),
        'latest'       => rows(product_select() . ' WHERE p.is_active = 1 ORDER BY p.id DESC LIMIT 4'),
        'testimonials' => rows('SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order, id'),
        'faqs'         => rows('SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order, id LIMIT 6'),
    ]);
}

function shop_query(array $opts): array
{
    $where = ['p.is_active = 1'];
    $params = [];
    if (!empty($opts['category_id'])) {
        $where[] = 'p.category_id = ?';
        $params[] = $opts['category_id'];
    }
    if (!empty($opts['offers'])) $where[] = 'p.old_price > p.price';
    $q = trim((string)($opts['q'] ?? ''));
    if ($q !== '') {
        $where[] = '(p.name LIKE ? OR p.short_description LIKE ? OR c.name LIKE ?)';
        array_push($params, "%$q%", "%$q%", "%$q%");
    }
    if (($opts['min'] ?? '') !== '' && is_numeric($opts['min'])) {
        $where[] = 'p.price >= ?';
        $params[] = (float)$opts['min'];
    }
    if (($opts['max'] ?? '') !== '' && is_numeric($opts['max'])) {
        $where[] = 'p.price <= ?';
        $params[] = (float)$opts['max'];
    }
    $sorts = [
        'popular'    => 'p.sales_count DESC',
        'newest'     => 'p.id DESC',
        'price_asc'  => 'p.price ASC',
        'price_desc' => 'p.price DESC',
    ];
    $sort = $sorts[$opts['sort'] ?? ''] ?? $sorts['popular'];

    $perPage = 12;
    $page = max(1, (int)($opts['page'] ?? 1));
    $sqlWhere = ' WHERE ' . implode(' AND ', $where);
    $total = (int)val('SELECT COUNT(*) FROM products p LEFT JOIN categories c ON c.id = p.category_id' . $sqlWhere, $params);
    $items = rows(product_select() . $sqlWhere . " ORDER BY p.stock > 0 DESC, $sort LIMIT $perPage OFFSET " . (($page - 1) * $perPage), $params);

    return ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => max(1, (int)ceil($total / $perPage))];
}

function shop_filters(): array
{
    return [
        'q'    => input('q'),
        'sort' => input('sort', 'popular'),
        'min'  => normalize_digits((string)input('min')),
        'max'  => normalize_digits((string)input('max')),
        'page' => (int)input('page', 1),
    ];
}

function site_shop(): never
{
    $f = shop_filters();
    $result = shop_query($f);
    $title = $f['q'] !== '' ? 'نتائج البحث عن: ' . $f['q'] : 'كل المنتجات';
    render('site/shop', ['title' => $title, 'heading' => $title, 'filters' => $f, 'result' => $result, 'category' => null]);
}

function site_offers(): never
{
    $f = shop_filters();
    $result = shop_query($f + ['offers' => true]);
    render('site/shop', ['title' => 'العروض والخصومات', 'heading' => 'العروض والخصومات', 'filters' => $f, 'result' => $result, 'category' => null, 'is_offers' => true]);
}

function site_category(string $slug): never
{
    $cat = row('SELECT * FROM categories WHERE slug = ? AND is_active = 1', [$slug]);
    if (!$cat) not_found();
    $f = shop_filters();
    $result = shop_query($f + ['category_id' => $cat['id']]);
    render('site/shop', ['title' => $cat['name'], 'heading' => $cat['name'], 'filters' => $f, 'result' => $result, 'category' => $cat]);
}

function site_product(string $slug): never
{
    $p = row(product_select() . ' WHERE p.slug = ? AND p.is_active = 1', [$slug]);
    if (!$p) not_found();
    q('UPDATE products SET views = views + 1 WHERE id = ?', [$p['id']]);
    $related = rows(product_select() . ' WHERE p.is_active = 1 AND p.id != ? AND p.category_id IS ? ORDER BY p.sales_count DESC LIMIT 4', [$p['id'], $p['category_id']]);
    if (count($related) < 4) {
        $ids = array_merge([$p['id']], array_column($related, 'id'));
        $in = implode(',', array_map('intval', $ids));
        $related = array_merge($related, rows(product_select() . " WHERE p.is_active = 1 AND p.id NOT IN ($in) ORDER BY p.is_featured DESC, p.sales_count DESC LIMIT " . (4 - count($related))));
    }
    $gallery = array_values(array_filter(array_merge([$p['image']], json_decode($p['gallery'] ?? '[]', true) ?: [])));
    render('site/product', [
        'title'       => $p['name'],
        'description' => $p['short_description'],
        'og_image'    => $p['image'],
        'p'           => $p,
        'gallery'     => $gallery,
        'specs'       => parse_specs($p['specs']),
        'related'     => $related,
    ]);
}

/* ---------------- السلة ---------------- */

function site_cart(): never
{
    render('site/cart', ['title' => 'سلة المشتريات', 'cart' => cart_details()]);
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
    $newQty = min((cart()[$id] ?? 0) + $qty, (int)$p['stock']);
    cart_set($id, $newQty);
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
    cart_details(); // ضبط الكميات حسب المخزون
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
                // تأكيد المخزون داخل المعاملة
                foreach ($cart['items'] as $it) {
                    $stock = (int)val('SELECT stock FROM products WHERE id = ?', [$it['product']['id']]);
                    if ($stock < $it['qty']) throw new RuntimeException('الكمية المطلوبة من "' . $it['product']['name'] . '" مش متاحة دلوقتي.');
                }
                do {
                    $code = 'QN' . date('ymd') . random_int(1000, 9999);
                } while (val('SELECT COUNT(*) FROM orders WHERE code = ?', [$code]));

                $orderId = insert('orders', $data + [
                    'code'     => $code,
                    'subtotal' => $cart['subtotal'],
                    'shipping' => $cart['shipping'],
                    'total'    => $cart['total'],
                    'status'   => 'new',
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

    render('site/checkout', [
        'title'  => 'إتمام الطلب',
        'cart'   => $cart,
        'errors' => $errors,
        'form'   => $_POST,
    ]);
}

function load_order(string $code): ?array
{
    $o = row('SELECT * FROM orders WHERE code = ?', [$code]);
    if ($o) $o['items'] = rows('SELECT * FROM order_items WHERE order_id = ?', [$o['id']]);
    return $o;
}

function site_order_done(string $code): never
{
    // صفحة النجاح تظهر بس لصاحب الطلب في نفس الجلسة
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
        if ($o && $phone !== '' && $clean($o['phone']) === $clean($phone)) {
            $order = $o;
        } else {
            $error = 'مفيش طلب بالبيانات دي. اتأكد من رقم الطلب ورقم الموبايل.';
        }
    }
    render('site/track', ['title' => 'تتبع طلبك', 'order' => $order, 'error' => $error, 'code' => $code, 'phone' => $phone]);
}

/* ---------------- الصيانة / التواصل ---------------- */

function site_maintenance(): never
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
            flash('success', 'تم استلام طلب الصيانة ✔ هنكلمك في أقرب وقت لتأكيد الموعد.');
            redirect('/maintenance');
        }
    }
    render('site/maintenance', ['title' => 'طلب صيانة / تركيب', 'errors' => $errors, 'form' => $_POST]);
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
    render('site/contact', ['title' => 'تواصل معنا', 'errors' => $errors, 'form' => $_POST]);
}

function site_about(): never
{
    render('site/about', [
        'title'        => 'من نحن',
        'testimonials' => rows('SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order, id'),
    ]);
}

function site_faq(): never
{
    render('site/faq', ['title' => 'الأسئلة الشائعة', 'faqs' => rows('SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order, id')]);
}
