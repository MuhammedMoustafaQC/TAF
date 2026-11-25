<?php
/**
 * لوحة التحكم
 */

function admin_render(string $view, array $data = []): never
{
    render('admin/' . $view, $data + [
        'badges' => [
            'orders'   => (int)val("SELECT COUNT(*) FROM orders WHERE status = 'new'"),
            'requests' => (int)val("SELECT COUNT(*) FROM service_requests WHERE status = 'new'"),
            'messages' => (int)val('SELECT COUNT(*) FROM messages WHERE is_read = 0'),
            'installments' => (int)val("SELECT COUNT(*) FROM installment_requests WHERE status = 'new'"),
            'reviews'  => (int)val('SELECT COUNT(*) FROM reviews WHERE is_approved = 0'),
        ],
    ], 'admin');
}

function find_or_404(string $table, $id): array
{
    $r = row("SELECT * FROM $table WHERE id = ?", [(int)$id]);
    if (!$r) {
        http_response_code(404);
        flash('error', 'العنصر غير موجود.');
        redirect('/admin');
    }
    return $r;
}

/* ---------------- الدخول ---------------- */

function admin_login(): never
{
    if (admin()) redirect('/admin');
    $error = null;
    if (is_post()) {
        $_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'] ?? [], fn($t) => $t > time() - 900);
        if (count($_SESSION['login_attempts']) >= 5) {
            $error = 'محاولات كتير غلط. استنى 15 دقيقة وحاول تاني.';
        } else {
            $a = row('SELECT * FROM admins WHERE username = ?', [input('username')]);
            if ($a && password_verify((string)input('password'), $a['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = (int)$a['id'];
                unset($_SESSION['login_attempts']);
                q('UPDATE admins SET last_login = ? WHERE id = ?', [date('Y-m-d H:i:s'), $a['id']]);
                $to = $_SESSION['admin_intended'] ?? null;
                unset($_SESSION['admin_intended']);
                // رجوع للصفحة المطلوبة — مسارات داخلية بس (منع التحويل لموقع خارجي)
                if ($to && preg_match('#^/(?![/\\\\])#', $to) && str_contains($to, '/admin') && !str_contains($to, '/admin/login')) {
                    header('Location: ' . $to);
                    exit;
                }
                redirect('/admin');
            }
            $_SESSION['login_attempts'][] = time();
            $error = 'اسم المستخدم أو كلمة المرور غلط.';
        }
    }
    echo view('admin/login', ['error' => $error]);
    exit;
}

function admin_logout(): never
{
    unset($_SESSION['admin_id']);
    session_regenerate_id(true);
    redirect('/admin/login');
}

/* ---------------- الرئيسية ---------------- */

function admin_dashboard(): never
{
    require_admin();
    $today = date('Y-m-d');
    $monthStart = date('Y-m-01');
    $notCancelled = "status != 'cancelled'";

    $stats = [
        'orders_today'   => (int)val("SELECT COUNT(*) FROM orders WHERE date(created_at) = ?", [$today]),
        'revenue_month'  => (float)val("SELECT COALESCE(SUM(total),0) FROM orders WHERE $notCancelled AND date(created_at) >= ?", [$monthStart]),
        'revenue_total'  => (float)val("SELECT COALESCE(SUM(total),0) FROM orders WHERE status = 'delivered'"),
        'orders_new'     => (int)val("SELECT COUNT(*) FROM orders WHERE status = 'new'"),
        'orders_total'   => (int)val('SELECT COUNT(*) FROM orders'),
        'products'       => (int)val('SELECT COUNT(*) FROM products'),
        'low_stock'      => (int)val('SELECT COUNT(*) FROM products WHERE is_active = 1 AND stock <= 5'),
        'requests_new'   => (int)val("SELECT COUNT(*) FROM service_requests WHERE status = 'new'"),
        'messages_new'   => (int)val('SELECT COUNT(*) FROM messages WHERE is_read = 0'),
        'installments_new' => (int)val("SELECT COUNT(*) FROM installment_requests WHERE status = 'new'"),
        'reviews_pending'  => (int)val('SELECT COUNT(*) FROM reviews WHERE is_approved = 0'),
        'customers'        => (int)val('SELECT COUNT(*) FROM customers'),
    ];

    // مبيعات آخر 14 يوم
    $chart = [];
    $raw = [];
    foreach (rows("SELECT date(created_at) d, COUNT(*) c, SUM(total) s FROM orders WHERE $notCancelled AND date(created_at) >= ? GROUP BY d", [date('Y-m-d', strtotime('-13 days'))]) as $r) {
        $raw[$r['d']] = $r;
    }
    for ($i = 13; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $chart[] = ['date' => $d, 'label' => date('d/m', strtotime($d)), 'count' => (int)($raw[$d]['c'] ?? 0), 'sum' => (float)($raw[$d]['s'] ?? 0)];
    }

    $statusCounts = [];
    foreach (rows('SELECT status, COUNT(*) c FROM orders GROUP BY status') as $r) $statusCounts[$r['status']] = (int)$r['c'];

    admin_render('dashboard', [
        'title'        => 'الرئيسية',
        'stats'        => $stats,
        'chart'        => $chart,
        'statusCounts' => $statusCounts,
        'recent'       => rows('SELECT * FROM orders ORDER BY id DESC LIMIT 8'),
        'top'          => rows('SELECT id, name, image, price, sales_count, stock FROM products ORDER BY sales_count DESC LIMIT 5'),
        'lowStock'     => rows('SELECT id, name, image, stock FROM products WHERE is_active = 1 AND stock <= 5 ORDER BY stock ASC LIMIT 5'),
        'requests'     => rows("SELECT * FROM service_requests WHERE status = 'new' ORDER BY id DESC LIMIT 5"),
        'installments' => rows("SELECT * FROM installment_requests WHERE status = 'new' ORDER BY id DESC LIMIT 5"),
    ]);
}

/* ---------------- المنتجات ---------------- */

function admin_products(): never
{
    require_admin();
    $where = ['1=1'];
    $params = [];
    if ($s = input('q')) {
        $where[] = 'p.name LIKE ?';
        $params[] = "%$s%";
    }
    if ($c = (int)input('category')) {
        $where[] = '(p.category_id = ? OR p.category_id IN (SELECT id FROM categories WHERE parent_id = ?))';
        array_push($params, $c, $c);
    }
    if ($b = (int)input('brand')) {
        $where[] = 'p.brand_id = ?';
        $params[] = $b;
    }
    $st = input('status');
    if ($st === 'active') $where[] = 'p.is_active = 1';
    if ($st === 'hidden') $where[] = 'p.is_active = 0';
    if ($st === 'low') $where[] = 'p.stock <= 5';

    admin_render('products', [
        'title'      => 'المنتجات',
        'products'   => rows('SELECT p.*, c.name category_name, b.name brand_name FROM products p LEFT JOIN categories c ON c.id = p.category_id LEFT JOIN brands b ON b.id = p.brand_id WHERE ' . implode(' AND ', $where) . ' ORDER BY p.id DESC', $params),
        'categories' => admin_category_options(),
        'brands'     => rows('SELECT id, name FROM brands ORDER BY sort_order, id'),
    ]);
}

function admin_product_form(?string $id = null): never
{
    require_admin();
    $p = $id ? find_or_404('products', $id) : [
        'id' => null, 'name' => '', 'category_id' => null, 'brand_id' => null, 'short_description' => '', 'description' => '', 'specs' => '',
        'faqs' => '', 'price' => '', 'old_price' => '', 'stock' => 10, 'image' => '', 'gallery' => '[]', 'badge' => '', 'warranty' => '',
        'installment_months' => 0, 'is_express' => 1, 'is_featured' => 0, 'is_active' => 1, 'slug' => '',
    ];
    $gallery = json_decode($p['gallery'] ?? '[]', true) ?: [];
    $errors = [];

    if (is_post()) {
        $data = [
            'name'              => input('name'),
            'category_id'       => (int)input('category_id') ?: null,
            'brand_id'          => (int)input('brand_id') ?: null,
            'short_description' => input('short_description'),
            'description'       => input('description'),
            'specs'             => input('specs'),
            'faqs'              => input('faqs'),
            'installment_months' => max(0, min(60, (int)normalize_digits((string)input('installment_months')))),
            'is_express'        => input('is_express') ? 1 : 0,
            'price'             => (float)normalize_digits((string)input('price')),
            'old_price'         => input('old_price') !== '' ? (float)normalize_digits((string)input('old_price')) : null,
            'stock'             => (int)normalize_digits((string)input('stock')),
            'badge'             => input('badge'),
            'warranty'          => input('warranty'),
            'is_featured'       => input('is_featured') ? 1 : 0,
            'is_active'         => input('is_active') ? 1 : 0,
        ];
        if (mb_strlen($data['name']) < 2) $errors['name'] = 'اكتب اسم المنتج.';
        if ($data['price'] <= 0) $errors['price'] = 'اكتب سعر صحيح.';
        if ($data['old_price'] !== null && $data['old_price'] <= $data['price']) $data['old_price'] = null;

        $slugInput = trim((string)input('slug'));
        $data['slug'] = unique_slug('products', $slugInput !== '' ? $slugInput : $data['name'], $p['id'] ? (int)$p['id'] : null);

        // حذف صور من المعرض
        $remove = (array)($_POST['remove_gallery'] ?? []);
        foreach ($remove as $r) {
            if (($k = array_search($r, $gallery, true)) !== false) {
                delete_upload($gallery[$k]);
                unset($gallery[$k]);
            }
        }
        try {
            if ($main = upload_image($_FILES['image'] ?? null)) {
                if ($p['image']) delete_upload($p['image']);
                $data['image'] = $main;
            }
            foreach (files_array('gallery') as $f) {
                if ($g = upload_image($f)) $gallery[] = $g;
            }
        } catch (RuntimeException $ex) {
            $errors['image'] = $ex->getMessage();
        }
        $data['gallery'] = json_encode(array_values($gallery), JSON_UNESCAPED_UNICODE);

        if (!$errors) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            if ($p['id']) {
                update('products', (int)$p['id'], $data);
                flash('success', 'تم حفظ التعديلات.');
            } else {
                $newId = insert('products', $data);
                flash('success', 'تمت إضافة المنتج.');
                redirect('/admin/products/' . $newId . '/edit');
            }
            redirect('/admin/products/' . $p['id'] . '/edit');
        }
        $p = array_merge($p, $data);
    }

    admin_render('product-form', [
        'title'      => $p['id'] ? 'تعديل منتج' : 'إضافة منتج',
        'p'          => $p,
        'gallery'    => $gallery,
        'errors'     => $errors,
        'categories' => admin_category_options(),
        'brands'     => rows('SELECT id, name FROM brands ORDER BY sort_order, id'),
    ]);
}

function admin_product_toggle(string $id): never
{
    require_admin();
    $p = find_or_404('products', $id);
    $field = input('field') === 'is_featured' ? 'is_featured' : 'is_active';
    q("UPDATE products SET $field = 1 - $field WHERE id = ?", [$p['id']]);
    if (wants_json()) json_out(['ok' => true, 'value' => (int)val("SELECT $field FROM products WHERE id = ?", [$p['id']])]);
    back('/admin/products');
}

function admin_product_delete(string $id): never
{
    require_admin();
    $p = find_or_404('products', $id);
    delete_upload($p['image']);
    foreach (json_decode($p['gallery'] ?? '[]', true) ?: [] as $g) delete_upload($g);
    q('DELETE FROM products WHERE id = ?', [$p['id']]);
    flash('success', 'تم حذف المنتج.');
    redirect('/admin/products');
}

/* ---------------- الأقسام ---------------- */

function admin_categories(): never
{
    require_admin();
    $edit = ($eid = (int)input('edit')) ? find_or_404('categories', $eid) : null;
    $errors = [];
    if (is_post()) {
        $id = (int)input('id') ?: null;
        $cur = $id ? find_or_404('categories', $id) : null;
        $parent = (int)input('parent_id') ?: null;
        // القسم الرئيسي لازم يكون قسم رئيسي (مستوى واحد) ومش نفس القسم
        if ($parent && ($parent === $id || !val('SELECT COUNT(*) FROM categories WHERE id = ? AND parent_id IS NULL', [$parent]))) $parent = null;
        if ($parent && $id && val('SELECT COUNT(*) FROM categories WHERE parent_id = ?', [$id])) {
            $errors['parent_id'] = 'القسم ده فيه أقسام فرعية، مينفعش يبقى قسم فرعي.';
        }
        $data = [
            'name'        => input('name'),
            'parent_id'   => $parent,
            'description' => input('description'),
            'sort_order'  => (int)input('sort_order'),
            'is_active'   => input('is_active') ? 1 : 0,
        ];
        if (mb_strlen($data['name']) < 2) $errors['name'] = 'اكتب اسم القسم.';
        $data['slug'] = unique_slug('categories', input('slug') ?: $data['name'], $id);
        try {
            if ($img = upload_image($_FILES['image'] ?? null)) {
                if ($cur) delete_upload($cur['image']);
                $data['image'] = $img;
            }
        } catch (RuntimeException $ex) {
            $errors['image'] = $ex->getMessage();
        }
        if (!$errors) {
            $id ? update('categories', $id, $data) : insert('categories', $data);
            flash('success', 'تم حفظ القسم.');
            redirect('/admin/categories');
        }
        $edit = array_merge($cur ?? [], $data, ['id' => $id]);
    }
    admin_render('categories', [
        'title'      => 'الأقسام',
        'edit'       => $edit,
        'errors'     => $errors,
        'categories' => admin_category_tree(),
        'parents'    => rows('SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY sort_order, id'),
    ]);
}

function admin_category_delete(string $id): never
{
    require_admin();
    $c = find_or_404('categories', $id);
    delete_upload($c['image']);
    q('DELETE FROM categories WHERE id = ?', [$c['id']]);
    flash('success', 'تم حذف القسم (المنتجات اللي كانت فيه بقت بدون قسم).');
    redirect('/admin/categories');
}

/* ---------------- الطلبات ---------------- */

function admin_orders(): never
{
    require_admin();
    $where = ['1=1'];
    $params = [];
    if (($st = input('status')) && isset(order_statuses()[$st])) {
        $where[] = 'status = ?';
        $params[] = $st;
    }
    if ($s = input('q')) {
        $where[] = '(code LIKE ? OR customer_name LIKE ? OR phone LIKE ?)';
        array_push($params, "%$s%", "%$s%", '%' . normalize_digits($s) . '%');
    }
    $perPage = 20;
    $page = max(1, (int)input('page', 1));
    $sqlWhere = implode(' AND ', $where);
    $total = (int)val("SELECT COUNT(*) FROM orders WHERE $sqlWhere", $params);
    $counts = [];
    foreach (rows('SELECT status, COUNT(*) c FROM orders GROUP BY status') as $r) $counts[$r['status']] = (int)$r['c'];

    admin_render('orders', [
        'title'  => 'الطلبات',
        'orders' => rows("SELECT o.*, (SELECT SUM(qty) FROM order_items WHERE order_id = o.id) items_count FROM orders o WHERE $sqlWhere ORDER BY id DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage), $params),
        'page'   => $page,
        'pages'  => max(1, (int)ceil($total / $perPage)),
        'total'  => $total,
        'counts' => $counts,
    ]);
}

function admin_order(string $id): never
{
    require_admin();
    $o = find_or_404('orders', $id);
    $o['items'] = rows('SELECT oi.*, p.image, p.slug FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?', [$o['id']]);
    $history = (int)val('SELECT COUNT(*) FROM orders WHERE phone = ? AND id != ?', [$o['phone'], $o['id']]);
    admin_render('order', ['title' => 'طلب رقم ' . $o['code'], 'o' => $o, 'history' => $history]);
}

function admin_order_status(string $id): never
{
    require_admin();
    $o = find_or_404('orders', $id);
    $new = input('status');
    if (!isset(order_statuses()[$new])) back();

    $pdo = db();
    $pdo->beginTransaction();
    // إرجاع / خصم المخزون عند الإلغاء أو التراجع عنه
    if ($o['status'] !== 'cancelled' && $new === 'cancelled') {
        foreach (rows('SELECT product_id, qty FROM order_items WHERE order_id = ?', [$o['id']]) as $it) {
            q('UPDATE products SET stock = stock + ?, sales_count = MAX(0, sales_count - ?) WHERE id = ?', [$it['qty'], $it['qty'], $it['product_id']]);
        }
    } elseif ($o['status'] === 'cancelled' && $new !== 'cancelled') {
        foreach (rows('SELECT product_id, qty FROM order_items WHERE order_id = ?', [$o['id']]) as $it) {
            q('UPDATE products SET stock = MAX(0, stock - ?), sales_count = sales_count + ? WHERE id = ?', [$it['qty'], $it['qty'], $it['product_id']]);
        }
    }
    update('orders', (int)$o['id'], ['status' => $new, 'admin_notes' => input('admin_notes'), 'updated_at' => date('Y-m-d H:i:s')]);
    $pdo->commit();
    flash('success', 'تم تحديث الطلب.');
    redirect('/admin/orders/' . $o['id']);
}

function admin_order_delete(string $id): never
{
    require_admin();
    $o = find_or_404('orders', $id);
    q('DELETE FROM orders WHERE id = ?', [$o['id']]);
    flash('success', 'تم حذف الطلب.');
    redirect('/admin/orders');
}

/* ---------------- طلبات الصيانة ---------------- */

function admin_requests(): never
{
    require_admin();
    $where = '1=1';
    $params = [];
    if (($st = input('status')) && isset(request_statuses()[$st])) {
        $where = 'status = ?';
        $params[] = $st;
    }
    admin_render('requests', [
        'title'    => 'طلبات الصيانة والتركيب',
        'requests' => rows("SELECT * FROM service_requests WHERE $where ORDER BY id DESC LIMIT 200", $params),
    ]);
}

function admin_request_status(string $id): never
{
    require_admin();
    $r = find_or_404('service_requests', $id);
    if (isset(request_statuses()[$s = input('status')])) update('service_requests', (int)$r['id'], ['status' => $s]);
    flash('success', 'تم تحديث حالة الطلب.');
    back('/admin/requests');
}

function admin_request_delete(string $id): never
{
    require_admin();
    $r = find_or_404('service_requests', $id);
    q('DELETE FROM service_requests WHERE id = ?', [$r['id']]);
    flash('success', 'تم الحذف.');
    back('/admin/requests');
}

/* ---------------- الرسائل ---------------- */

function admin_messages(): never
{
    require_admin();
    admin_render('messages', ['title' => 'رسائل العملاء', 'messages' => rows('SELECT * FROM messages ORDER BY is_read ASC, id DESC LIMIT 200')]);
}

function admin_message_read(string $id): never
{
    require_admin();
    $m = find_or_404('messages', $id);
    update('messages', (int)$m['id'], ['is_read' => $m['is_read'] ? 0 : 1]);
    back('/admin/messages');
}

function admin_message_delete(string $id): never
{
    require_admin();
    $m = find_or_404('messages', $id);
    q('DELETE FROM messages WHERE id = ?', [$m['id']]);
    flash('success', 'تم حذف الرسالة.');
    back('/admin/messages');
}

/* ---------------- السلايدر / الأسئلة ---------------- */

function admin_slides(): never
{
    require_admin();
    $edit = ($eid = (int)input('edit')) ? find_or_404('slides', $eid) : null;
    $errors = [];
    if (is_post()) {
        $id = (int)input('id') ?: null;
        $cur = $id ? find_or_404('slides', $id) : null;
        $data = [
            'title'       => input('title'),
            'subtitle'    => input('subtitle'),
            'button_text' => input('button_text'),
            'button_link' => input('button_link'),
            'sort_order'  => (int)input('sort_order'),
            'is_active'   => input('is_active') ? 1 : 0,
        ];
        try {
            if ($img = upload_image($_FILES['image'] ?? null)) {
                if ($cur) delete_upload($cur['image']);
                $data['image'] = $img;
            }
        } catch (RuntimeException $ex) {
            $errors['image'] = $ex->getMessage();
        }
        // العنوان اختياري — مسموح بشريحة صورة بس
        if ($data['title'] === '' && empty($data['image']) && empty($cur['image']) && !isset($errors['image'])) {
            $errors['title'] = 'اكتب عنوان أو ارفع صورة للشريحة.';
        }
        if (!$errors) {
            $id ? update('slides', $id, $data) : insert('slides', $data);
            flash('success', 'تم حفظ الشريحة.');
            redirect('/admin/slides');
        }
        $edit = array_merge($cur ?? [], $data, ['id' => $id]);
    }
    admin_render('slides', ['title' => 'السلايدر الرئيسي', 'edit' => $edit, 'errors' => $errors, 'slides' => rows('SELECT * FROM slides ORDER BY sort_order, id')]);
}

function admin_slide_delete(string $id): never
{
    require_admin();
    $s = find_or_404('slides', $id);
    delete_upload($s['image']);
    q('DELETE FROM slides WHERE id = ?', [$s['id']]);
    flash('success', 'تم حذف الشريحة.');
    redirect('/admin/slides');
}

function admin_faqs(): never
{
    require_admin();
    $edit = ($eid = (int)input('edit')) ? find_or_404('faqs', $eid) : null;
    $errors = [];
    if (is_post()) {
        $id = (int)input('id') ?: null;
        if ($id) find_or_404('faqs', $id);
        $data = [
            'question'   => input('question'),
            'answer'     => input('answer'),
            'sort_order' => (int)input('sort_order'),
            'is_active'  => input('is_active') ? 1 : 0,
        ];
        if (mb_strlen($data['question']) < 3) $errors['question'] = 'اكتب السؤال.';
        if (mb_strlen($data['answer']) < 3) $errors['answer'] = 'اكتب الإجابة.';
        if (!$errors) {
            $id ? update('faqs', $id, $data) : insert('faqs', $data);
            flash('success', 'تم الحفظ.');
            redirect('/admin/faqs');
        }
        $edit = $data + ['id' => $id];
    }
    admin_render('faqs', ['title' => 'الأسئلة الشائعة', 'edit' => $edit, 'errors' => $errors, 'items' => rows('SELECT * FROM faqs ORDER BY sort_order, id')]);
}

function admin_faq_delete(string $id): never
{
    require_admin();
    $f = find_or_404('faqs', $id);
    q('DELETE FROM faqs WHERE id = ?', [$f['id']]);
    flash('success', 'تم الحذف.');
    redirect('/admin/faqs');
}

/* ---------------- الإعدادات ---------------- */

function settings_fields(): array
{
    $cats = [];
    foreach (rows('SELECT slug, name FROM categories WHERE parent_id IS NULL ORDER BY sort_order, id') as $c) $cats[$c['slug']] = $c['name'];
    return [
        'بيانات الموقع' => [
            'site_name'        => ['اسم الموقع', 'text'],
            'site_tagline'     => ['الشعار المختصر', 'text'],
            'meta_description' => ['وصف الموقع لمحركات البحث', 'textarea'],
            'company_name'     => ['الاسم القانوني للشركة (يظهر في الفوتر)', 'text'],
            'tax_number'       => ['رقم التسجيل الضريبي (اختياري)', 'text'],
        ],
        'بيانات التواصل' => [
            'phone'         => ['رقم التليفون', 'text'],
            'phone2'        => ['رقم تليفون إضافي', 'text'],
            'whatsapp'      => ['رقم واتساب (بالصيغة الدولية مثل 2010xxxxxxxx)', 'text'],
            'email'         => ['البريد الإلكتروني', 'text'],
            'address'       => ['العنوان', 'text'],
            'working_hours' => ['مواعيد العمل', 'text'],
        ],
        'السوشيال ميديا' => [
            'facebook'  => ['فيسبوك', 'url'],
            'instagram' => ['إنستجرام', 'url'],
            'telegram'  => ['تيليجرام', 'url'],
            'messenger' => ['ماسنجر (رابط m.me)', 'url'],
            'tiktok'    => ['تيك توك', 'url'],
            'youtube'   => ['يوتيوب', 'url'],
        ],
        'الشحن والدفع والتقسيط' => [
            'shipping_fee'       => ['مصاريف الشحن (ج.م)', 'number'],
            'free_shipping_min'  => ['شحن مجاني للطلبات أكبر من (ج.م) — 0 لإلغائه', 'number'],
            'express_label'      => ['نص شارة التوصيل السريع (مثلاً: بكرة)', 'text'],
            'enable_wallet'      => ['تفعيل الدفع بفودافون كاش / إنستاباي', 'checkbox'],
            'wallet_number'      => ['رقم المحفظة / إنستاباي', 'text'],
            'installment_months' => ['عدد شهور التقسيط الافتراضي', 'number'],
            'installment_note'   => ['نص التقسيط في صفحة المنتج', 'text'],
        ],
        'رسالة الترحيب' => [
            'welcome_enabled' => ['إظهار رسالة الترحيب لأول زيارة', 'checkbox'],
            'welcome_title'   => ['عنوان رسالة الترحيب', 'text'],
            'welcome_text'    => ['نص رسالة الترحيب', 'textarea'],
        ],
        'الصفحة الرئيسية و"من نحن"' => [
            'home_showcase_category' => ['القسم اللي أقسامه الفرعية تظهر في الرئيسية', 'select', $cats],
            'about_title'            => ['عنوان قسم "من نحن"', 'text'],
            'about_text'             => ['نبذة "من نحن"', 'textarea'],
            'stat_years'             => ['سنين الخبرة', 'number'],
            'stat_customers'         => ['عدد العملاء', 'number'],
        ],
    ];
}

function admin_settings(): never
{
    require_admin();
    if (is_post()) {
        foreach (settings_fields() as $fields) {
            foreach ($fields as $key => $f) {
                [$label, $type] = $f;
                $v = $type === 'checkbox' ? (input($key) ? '1' : '0') : (string)input($key);
                if ($type === 'number') $v = (string)(0 + (float)normalize_digits($v));
                if ($type === 'select' && !isset($f[2][$v])) $v = '';
                save_setting($key, $v);
            }
        }
        flash('success', 'تم حفظ الإعدادات.');
        redirect('/admin/settings');
    }
    $values = [];
    foreach (rows('SELECT key, value FROM settings') as $r) $values[$r['key']] = $r['value'];
    admin_render('settings', ['title' => 'إعدادات الموقع', 'groups' => settings_fields(), 'values' => $values]);
}

/* ---------------- المديرين ---------------- */

function admin_users(): never
{
    require_admin();
    $errors = [];
    if (is_post()) {
        $action = input('action');
        if ($action === 'password') {
            $me = row('SELECT * FROM admins WHERE id = ?', [admin()['id']]);
            $new = (string)input('new_password');
            if (!password_verify((string)input('current_password'), $me['password_hash'])) $errors['current_password'] = 'كلمة المرور الحالية غلط.';
            if (mb_strlen($new) < 8) $errors['new_password'] = 'كلمة المرور لازم تكون 8 حروف على الأقل.';
            if ($new !== (string)input('confirm_password')) $errors['confirm_password'] = 'كلمتين المرور مش متطابقين.';
            if (!$errors) {
                update('admins', (int)$me['id'], ['password_hash' => password_hash($new, PASSWORD_DEFAULT), 'name' => input('name') ?: $me['name']]);
                flash('success', 'تم تحديث بيانات حسابك.');
                redirect('/admin/users');
            }
        } elseif ($action === 'create') {
            $username = strtolower((string)input('username'));
            if (mb_strlen((string)input('name')) < 2) $errors['name'] = 'اكتب الاسم.';
            if (!preg_match('/^[a-z0-9_.]{3,30}$/', $username)) $errors['username'] = 'اسم المستخدم بالإنجليزي (حروف وأرقام) من 3 لـ 30 حرف.';
            elseif (val('SELECT COUNT(*) FROM admins WHERE username = ?', [$username])) $errors['username'] = 'اسم المستخدم موجود قبل كده.';
            if (mb_strlen((string)input('password')) < 8) $errors['password'] = 'كلمة المرور 8 حروف على الأقل.';
            if (!$errors) {
                insert('admins', ['name' => input('name'), 'username' => $username, 'password_hash' => password_hash((string)input('password'), PASSWORD_DEFAULT)]);
                flash('success', 'تمت إضافة المدير.');
                redirect('/admin/users');
            }
        }
    }
    admin_render('users', [
        'title'  => 'المديرين وكلمة المرور',
        'users'  => rows('SELECT id, name, username, last_login, created_at FROM admins ORDER BY id'),
        'errors' => $errors,
        'default_pw' => password_verify(config('default_admin')['password'], (string)val('SELECT password_hash FROM admins WHERE id = ?', [admin()['id']])),
    ]);
}

function admin_user_delete(string $id): never
{
    require_admin();
    $u = find_or_404('admins', $id);
    if ((int)$u['id'] === (int)admin()['id']) {
        flash('error', 'مينفعش تمسح حسابك وانت داخل بيه.');
    } elseif ((int)val('SELECT COUNT(*) FROM admins') <= 1) {
        flash('error', 'لازم يفضل مدير واحد على الأقل.');
    } else {
        q('DELETE FROM admins WHERE id = ?', [$u['id']]);
        flash('success', 'تم حذف المدير.');
    }
    redirect('/admin/users');
}

/* ---------------- دوال مساعدة للوحة التحكم ---------------- */

/** الأقسام مرتبة: الرئيسي وتحته الفرعي (للقوائم المنسدلة) */
function admin_category_options(): array
{
    $out = [];
    foreach (admin_category_tree() as $c) {
        $out[] = ['id' => $c['id'], 'name' => ($c['parent_id'] ? '— ' : '') . $c['name']];
    }
    return $out;
}

/** كل الأقسام مرتبة كشجرة (رئيسي ثم فروعه) مع عدد المنتجات */
function admin_category_tree(): array
{
    $all = rows('SELECT c.*, p.name parent_name, (SELECT COUNT(*) FROM products x WHERE x.category_id = c.id) products_count
                 FROM categories c LEFT JOIN categories p ON p.id = c.parent_id ORDER BY c.sort_order, c.id');
    $byId = array_column($all, null, 'id');
    $children = [];
    foreach ($all as $c) if ($c['parent_id']) $children[$c['parent_id']][] = $c;
    $out = [];
    foreach ($all as $c) {
        if ($c['parent_id'] && isset($byId[$c['parent_id']])) continue;
        $out[] = $c;
        foreach ($children[$c['id']] ?? [] as $ch) $out[] = $ch;
    }
    return $out;
}

function admin_wa_link(string $phone, string $text = ''): string
{
    $num = preg_replace('/^0/', '20', preg_replace('/\D+/', '', normalize_digits($phone)));
    return 'https://wa.me/' . $num . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

function banner_positions(): array
{
    return [
        'flash' => 'بانر العروض العريض بعد المنتجات المميزة',
        'promo' => 'بانرات العروض الثلاثة',
        'wide'  => 'بانر عريض بعد المنتجات الجديدة',
        'brand' => 'بانر الماركة',
        'duo'   => 'بانرين جنب بعض قبل المدونة',
    ];
}

/* ---------------- الماركات ---------------- */

function admin_brands(): never
{
    require_admin();
    $edit = ($eid = (int)input('edit')) ? find_or_404('brands', $eid) : null;
    $errors = [];
    if (is_post()) {
        $id = (int)input('id') ?: null;
        $cur = $id ? find_or_404('brands', $id) : null;
        $data = [
            'name'        => input('name'),
            'description' => input('description'),
            'sort_order'  => (int)input('sort_order'),
            'is_active'   => input('is_active') ? 1 : 0,
        ];
        if (mb_strlen($data['name']) < 2) $errors['name'] = 'اكتب اسم الماركة.';
        $data['slug'] = unique_slug('brands', input('slug') ?: $data['name'], $id);
        try {
            if ($img = upload_image($_FILES['logo'] ?? null)) {
                if ($cur) delete_upload($cur['logo']);
                $data['logo'] = $img;
            }
        } catch (RuntimeException $ex) {
            $errors['logo'] = $ex->getMessage();
        }
        if (!$errors) {
            $id ? update('brands', $id, $data) : insert('brands', $data);
            flash('success', 'تم حفظ الماركة.');
            redirect('/admin/brands');
        }
        $edit = array_merge($cur ?? [], $data, ['id' => $id]);
    }
    admin_render('brands', [
        'title'  => 'الماركات',
        'edit'   => $edit,
        'errors' => $errors,
        'brands' => rows('SELECT b.*, (SELECT COUNT(*) FROM products p WHERE p.brand_id = b.id) products_count FROM brands b ORDER BY sort_order, id'),
    ]);
}

function admin_brand_delete(string $id): never
{
    require_admin();
    $b = find_or_404('brands', $id);
    delete_upload($b['logo']);
    q('DELETE FROM brands WHERE id = ?', [$b['id']]);
    flash('success', 'تم حذف الماركة (المنتجات بقت بدون ماركة).');
    redirect('/admin/brands');
}

/* ---------------- العملاء ---------------- */

function admin_customers(): never
{
    require_admin();
    $where = '1=1';
    $params = [];
    if ($s = input('q')) {
        $where = '(c.name LIKE ? OR c.phone LIKE ? OR c.email LIKE ?)';
        array_push($params, "%$s%", '%' . normalize_digits($s) . '%', "%$s%");
    }
    admin_render('customers', [
        'title'     => 'العملاء',
        'customers' => rows("SELECT c.*,
                (SELECT COUNT(*) FROM orders o WHERE o.customer_id = c.id) orders_count,
                (SELECT COALESCE(SUM(total), 0) FROM orders o WHERE o.customer_id = c.id AND o.status != 'cancelled') total_spent
            FROM customers c WHERE $where ORDER BY c.id DESC LIMIT 300", $params),
    ]);
}

function admin_customer(string $id): never
{
    require_admin();
    $c = find_or_404('customers', $id);
    admin_render('customer', [
        'title'    => 'العميل: ' . $c['name'],
        'c'        => $c,
        'orders'   => rows('SELECT o.*, (SELECT SUM(qty) FROM order_items WHERE order_id = o.id) items_count FROM orders o WHERE customer_id = ? ORDER BY id DESC', [$c['id']]),
        'wishlist' => rows('SELECT p.id, p.name, p.image, p.price FROM wishlist w JOIN products p ON p.id = w.product_id WHERE w.customer_id = ? ORDER BY w.created_at DESC', [$c['id']]),
        'spent'    => (float)val("SELECT COALESCE(SUM(total), 0) FROM orders WHERE customer_id = ? AND status != 'cancelled'", [$c['id']]),
    ]);
}

/* ---------------- التقييمات ---------------- */

function admin_reviews(): never
{
    require_admin();
    $f = input('status');
    $where = $f === 'pending' ? 'r.is_approved = 0' : ($f === 'approved' ? 'r.is_approved = 1' : '1=1');
    $counts = ['all' => (int)val('SELECT COUNT(*) FROM reviews'), 'pending' => (int)val('SELECT COUNT(*) FROM reviews WHERE is_approved = 0'), 'approved' => (int)val('SELECT COUNT(*) FROM reviews WHERE is_approved = 1')];
    admin_render('reviews', [
        'title'   => 'تقييمات المنتجات',
        'reviews' => rows("SELECT r.*, p.name product_name, p.slug product_slug, p.image product_image FROM reviews r LEFT JOIN products p ON p.id = r.product_id WHERE $where ORDER BY r.is_approved ASC, r.id DESC LIMIT 300"),
        'counts'  => $counts,
    ]);
}

function admin_review_approve(string $id): never
{
    require_admin();
    $r = find_or_404('reviews', $id);
    update('reviews', (int)$r['id'], ['is_approved' => $r['is_approved'] ? 0 : 1]);
    refresh_rating((int)$r['product_id']);
    flash('success', $r['is_approved'] ? 'تم إخفاء التقييم.' : 'تم نشر التقييم.');
    back('/admin/reviews');
}

function admin_review_delete(string $id): never
{
    require_admin();
    $r = find_or_404('reviews', $id);
    q('DELETE FROM reviews WHERE id = ?', [$r['id']]);
    refresh_rating((int)$r['product_id']);
    flash('success', 'تم حذف التقييم.');
    back('/admin/reviews');
}

/* ---------------- طلبات التقسيط ---------------- */

function admin_installments(): never
{
    require_admin();
    $where = '1=1';
    $params = [];
    if (($st = input('status')) && isset(installment_statuses()[$st])) {
        $where = 'i.status = ?';
        $params[] = $st;
    }
    $counts = [];
    foreach (rows('SELECT status, COUNT(*) c FROM installment_requests GROUP BY status') as $r) $counts[$r['status']] = (int)$r['c'];
    admin_render('installments', [
        'title'    => 'طلبات التقسيط',
        'requests' => rows("SELECT i.*, p.slug product_slug, p.price product_price FROM installment_requests i LEFT JOIN products p ON p.id = i.product_id WHERE $where ORDER BY i.id DESC LIMIT 200", $params),
        'counts'   => $counts,
    ]);
}

function admin_installment_status(string $id): never
{
    require_admin();
    $r = find_or_404('installment_requests', $id);
    if (isset(installment_statuses()[$s = input('status')])) update('installment_requests', (int)$r['id'], ['status' => $s]);
    flash('success', 'تم تحديث حالة طلب التقسيط.');
    back('/admin/installments');
}

function admin_installment_delete(string $id): never
{
    require_admin();
    $r = find_or_404('installment_requests', $id);
    q('DELETE FROM installment_requests WHERE id = ?', [$r['id']]);
    flash('success', 'تم الحذف.');
    back('/admin/installments');
}

/* ---------------- المشتركين في النشرة ---------------- */

function admin_subscribers(): never
{
    require_admin();
    if (input('export') === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="subscribers-' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['email', 'subscribed_at']);
        foreach (rows('SELECT email, created_at FROM subscribers ORDER BY id') as $r) fputcsv($out, [$r['email'], $r['created_at']]);
        fclose($out);
        exit;
    }
    admin_render('subscribers', ['title' => 'المشتركين في النشرة البريدية', 'subscribers' => rows('SELECT * FROM subscribers ORDER BY id DESC')]);
}

function admin_subscriber_delete(string $id): never
{
    require_admin();
    $s = find_or_404('subscribers', $id);
    q('DELETE FROM subscribers WHERE id = ?', [$s['id']]);
    flash('success', 'تم حذف المشترك.');
    redirect('/admin/subscribers');
}

/* ---------------- البانرات ---------------- */

function admin_banners(): never
{
    require_admin();
    $edit = ($eid = (int)input('edit')) ? find_or_404('banners', $eid) : null;
    $errors = [];
    if (is_post()) {
        $id = (int)input('id') ?: null;
        $cur = $id ? find_or_404('banners', $id) : null;
        $color = (string)input('bg_color');
        $data = [
            'position'    => input('position'),
            'title'       => input('title'),
            'subtitle'    => input('subtitle'),
            'button_text' => input('button_text'),
            'link'        => input('link'),
            'bg_color'    => preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#2B04B5',
            'sort_order'  => (int)input('sort_order'),
            'is_active'   => input('is_active') ? 1 : 0,
        ];
        if (!isset(banner_positions()[$data['position']])) $errors['position'] = 'اختار مكان البانر.';
        try {
            if ($img = upload_image($_FILES['image'] ?? null)) {
                if ($cur) delete_upload($cur['image']);
                $data['image'] = $img;
            }
        } catch (RuntimeException $ex) {
            $errors['image'] = $ex->getMessage();
        }
        if ($data['title'] === '' && empty($data['image']) && empty($cur['image']) && !isset($errors['image'])) {
            $errors['title'] = 'اكتب عنوان أو ارفع صورة للبانر.';
        }
        if (!$errors) {
            $id ? update('banners', $id, $data) : insert('banners', $data);
            flash('success', 'تم حفظ البانر.');
            redirect('/admin/banners');
        }
        $edit = array_merge($cur ?? [], $data, ['id' => $id]);
    }
    $grouped = [];
    foreach (rows('SELECT * FROM banners ORDER BY sort_order, id') as $b) $grouped[$b['position']][] = $b;
    admin_render('banners', ['title' => 'البانرات', 'edit' => $edit, 'errors' => $errors, 'grouped' => $grouped]);
}

function admin_banner_delete(string $id): never
{
    require_admin();
    $b = find_or_404('banners', $id);
    delete_upload($b['image']);
    q('DELETE FROM banners WHERE id = ?', [$b['id']]);
    flash('success', 'تم حذف البانر.');
    redirect('/admin/banners');
}

/* ---------------- المدونة ---------------- */

function admin_posts(): never
{
    require_admin();
    admin_render('posts', ['title' => 'المدونة', 'posts' => rows('SELECT * FROM posts ORDER BY created_at DESC, id DESC')]);
}

function admin_post_form(?string $id = null): never
{
    require_admin();
    $p = $id ? find_or_404('posts', $id) : ['id' => null, 'title' => '', 'slug' => '', 'excerpt' => '', 'content' => '', 'image' => '', 'is_published' => 1];
    $errors = [];
    if (is_post()) {
        $data = [
            'title'        => input('title'),
            'excerpt'      => input('excerpt'),
            'content'      => input('content'),
            'is_published' => input('is_published') ? 1 : 0,
        ];
        if (mb_strlen($data['title']) < 3) $errors['title'] = 'اكتب عنوان المقال.';
        if (mb_strlen($data['content']) < 10) $errors['content'] = 'اكتب محتوى المقال.';
        $slugInput = trim((string)input('slug'));
        $data['slug'] = unique_slug('posts', $slugInput !== '' ? $slugInput : $data['title'], $p['id'] ? (int)$p['id'] : null);
        try {
            if ($img = upload_image($_FILES['image'] ?? null)) {
                if ($p['image']) delete_upload($p['image']);
                $data['image'] = $img;
            }
        } catch (RuntimeException $ex) {
            $errors['image'] = $ex->getMessage();
        }
        if (!$errors) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            if ($p['id']) {
                update('posts', (int)$p['id'], $data);
                flash('success', 'تم حفظ المقال.');
                redirect('/admin/posts/' . $p['id'] . '/edit');
            }
            $newId = insert('posts', $data);
            flash('success', 'تم نشر المقال.');
            redirect('/admin/posts/' . $newId . '/edit');
        }
        $p = array_merge($p, $data);
    }
    admin_render('post-form', ['title' => $p['id'] ? 'تعديل مقال' : 'مقال جديد', 'p' => $p, 'errors' => $errors]);
}

function admin_post_delete(string $id): never
{
    require_admin();
    $p = find_or_404('posts', $id);
    delete_upload($p['image']);
    q('DELETE FROM posts WHERE id = ?', [$p['id']]);
    flash('success', 'تم حذف المقال.');
    redirect('/admin/posts');
}

/* ---------------- الصفحات الثابتة ---------------- */

function admin_pages(): never
{
    require_admin();
    admin_render('pages', ['title' => 'الصفحات', 'pages' => rows('SELECT * FROM pages ORDER BY id')]);
}

function admin_page_form(string $id): never
{
    require_admin();
    $p = find_or_404('pages', $id);
    $errors = [];
    if (is_post()) {
        $data = ['title' => input('title'), 'content' => input('content'), 'updated_at' => date('Y-m-d H:i:s')];
        if (mb_strlen($data['title']) < 2) $errors['title'] = 'اكتب عنوان الصفحة.';
        if (!$errors) {
            update('pages', (int)$p['id'], $data);
            flash('success', 'تم حفظ الصفحة.');
            redirect('/admin/pages/' . $p['id'] . '/edit');
        }
        $p = array_merge($p, $data);
    }
    admin_render('page-form', ['title' => 'تعديل صفحة: ' . $p['title'], 'p' => $p, 'errors' => $errors]);
}
