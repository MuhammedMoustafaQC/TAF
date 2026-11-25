<?php
/**
 * دوال مساعدة عامة
 */

function config(?string $key = null, $default = null)
{
    static $cfg = null;
    if ($cfg === null) $cfg = require __DIR__ . '/config.php';
    if ($key === null) return $cfg;
    return $cfg[$key] ?? $default;
}

/** تهريب النصوص قبل طباعتها في HTML */
function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_path(): string
{
    static $base = null;
    if ($base !== null) return $base;
    $cfg = config('base_path');
    if ($cfg !== null) return $base = rtrim($cfg, '/');

    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $dir = rtrim($dir, '/');
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    // لو الموقع شغال من فولدر public عن طريق .htaccess في الجذر
    if (str_ends_with($dir, '/public') && !str_starts_with($uri, $dir)) {
        $dir = substr($dir, 0, -strlen('/public'));
    }
    return $base = $dir;
}

function url(string $path = '/'): string
{
    if (preg_match('#^(https?:)?//#', $path) || str_starts_with($path, '#')
        || str_starts_with($path, 'tel:') || str_starts_with($path, 'mailto:')) {
        return $path;
    }
    $path = '/' . ltrim($path, '/');
    // ترميز الأجزاء العربية في الرابط
    $parts = explode('?', $path, 2);
    $encoded = implode('/', array_map(fn($s) => preg_match('/[^\x20-\x7e]/', $s) ? rawurlencode($s) : $s, explode('/', $parts[0])));
    return base_path() . $encoded . (isset($parts[1]) ? '?' . $parts[1] : '');
}

function asset(string $path): string
{
    $file = __DIR__ . '/../public/' . ltrim($path, '/');
    $v = is_file($file) ? '?v=' . filemtime($file) : '';
    return url($path) . $v;
}

function img(?string $path, string $fallback = 'assets/img/placeholder.svg'): string
{
    $path = $path ?: $fallback;
    if (preg_match('#^https?://#', $path)) return $path;
    return url($path);
}

function redirect(string $to): never
{
    header('Location: ' . url($to));
    exit;
}

function back(string $fallback = '/'): never
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($ref && parse_url($ref, PHP_URL_HOST) === $host) {
        header('Location: ' . $ref);
        exit;
    }
    redirect($fallback);
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function input(string $key, $default = '')
{
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $v;
}

function wants_json(): bool
{
    return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
        || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
}

function json_out(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/* ---------------- الإعدادات ---------------- */

function setting(string $key, $default = '')
{
    static $all = null;
    if ($all === null) {
        $all = [];
        foreach (rows('SELECT key, value FROM settings') as $r) $all[$r['key']] = $r['value'];
    }
    return ($all[$key] ?? '') !== '' ? $all[$key] : $default;
}

function save_setting(string $key, string $value): void
{
    q('INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value', [$key, $value]);
}

/* ---------------- العرض ---------------- */

function view(string $name, array $data = []): string
{
    extract($data, EXTR_SKIP);
    ob_start();
    require __DIR__ . '/../views/' . $name . '.php';
    return ob_get_clean();
}

function render(string $name, array $data = [], string $layout = 'site'): never
{
    $content = view($name, $data);
    echo view('layouts/' . $layout, $data + ['content' => $content]);
    exit;
}

function money($amount): string
{
    $n = (float)$amount;
    $s = number_format($n, fmod($n, 1) ? 2 : 0);
    return $s . "\u{00A0}ج.م";
}

function discount_percent(array $p): int
{
    if (empty($p['old_price']) || $p['old_price'] <= $p['price']) return 0;
    return (int)round(100 - ($p['price'] / $p['old_price']) * 100);
}

function ar_date(?string $dt, bool $time = true): string
{
    if (!$dt) return '';
    $ts = strtotime($dt);
    $d = date('Y/m/d', $ts);
    return $time ? $d . ' — ' . date('g:i', $ts) . ' ' . (date('A', $ts) === 'AM' ? 'ص' : 'م') : $d;
}

function slugify(string $text): string
{
    $text = mb_strtolower(trim($text));
    $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text);
    return trim($text, '-') ?: 'item';
}

function unique_slug(string $table, string $text, ?int $ignoreId = null): string
{
    $base = slugify($text);
    $slug = $base;
    $i = 2;
    while (val("SELECT COUNT(*) FROM $table WHERE slug = ? AND id != ?", [$slug, $ignoreId ?? 0])) {
        $slug = $base . '-' . $i++;
    }
    return $slug;
}

function wa_link(string $text = ''): string
{
    $num = preg_replace('/\D+/', '', setting('whatsapp', ''));
    return 'https://wa.me/' . $num . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

function tel_link(string $phone): string
{
    return 'tel:' . preg_replace('/[^\d+]/', '', $phone);
}

function nl2p(?string $text): string
{
    $out = '';
    foreach (preg_split('/\R+/', trim((string)$text)) as $line) {
        if ($line !== '') $out .= '<p>' . e($line) . '</p>';
    }
    return $out;
}

function parse_specs(?string $specs): array
{
    $out = [];
    foreach (preg_split('/\R/', (string)$specs) as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $parts = preg_split('/\s*:\s*/u', $line, 2);
        $out[] = [$parts[0], $parts[1] ?? ''];
    }
    return $out;
}

/* ---------------- الجلسة / CSRF / الرسائل ---------------- */

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $t = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($t) || !hash_equals(csrf_token(), $t)) {
        if (wants_json()) json_out(['ok' => false, 'message' => 'انتهت صلاحية الصفحة، حدّث الصفحة وحاول تاني.'], 419);
        flash('error', 'انتهت صلاحية الصفحة، حاول تاني.');
        back();
    }
}

function flash(string $type, ?string $msg = null)
{
    if ($msg !== null) {
        $_SESSION['_flash'][$type] = $msg;
        return null;
    }
    $m = $_SESSION['_flash'][$type] ?? null;
    unset($_SESSION['_flash'][$type]);
    return $m;
}

function old(string $key, $default = '')
{
    return $_SESSION['_old'][$key] ?? $default;
}

function keep_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

/* ---------------- المدير ---------------- */

function admin(): ?array
{
    static $a = false;
    if ($a !== false) return $a;
    $id = $_SESSION['admin_id'] ?? null;
    return $a = $id ? row('SELECT id, name, username, last_login FROM admins WHERE id = ?', [$id]) : null;
}

function require_admin(): void
{
    if (!admin()) {
        $_SESSION['admin_intended'] = $_SERVER['REQUEST_URI'] ?? null;
        redirect('/admin/login');
    }
}

/* ---------------- السلة ---------------- */

function cart(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    return array_sum(cart());
}

function cart_set(int $productId, int $qty): void
{
    if ($qty <= 0) unset($_SESSION['cart'][$productId]);
    else $_SESSION['cart'][$productId] = min($qty, 99);
}

function cart_clear(): void
{
    unset($_SESSION['cart']);
}

/** تفاصيل السلة من قاعدة البيانات */
function cart_details(): array
{
    $items = [];
    $subtotal = 0;
    $cart = cart();
    if ($cart) {
        $ids = array_map('intval', array_keys($cart));
        $in = implode(',', array_fill(0, count($ids), '?'));
        $products = rows("SELECT * FROM products WHERE is_active = 1 AND id IN ($in)", $ids);
        foreach ($products as $p) {
            $qty = min((int)$cart[$p['id']], max(0, (int)$p['stock']));
            if ($qty <= 0) {
                cart_set((int)$p['id'], 0);
                continue;
            }
            if ($qty !== (int)$cart[$p['id']]) cart_set((int)$p['id'], $qty);
            $line = $p['price'] * $qty;
            $subtotal += $line;
            $items[] = ['product' => $p, 'qty' => $qty, 'total' => $line];
        }
        // حذف أي منتجات اتمسحت أو اتوقفت
        $found = array_column(array_column($items, 'product'), 'id');
        foreach ($ids as $id) if (!in_array($id, $found)) cart_set($id, 0);
    }
    $fee = (float)setting('shipping_fee', 0);
    $freeMin = (float)setting('free_shipping_min', 0);
    $shipping = (!$items || ($freeMin > 0 && $subtotal >= $freeMin)) ? 0 : $fee;
    return [
        'items'    => $items,
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'total'    => $subtotal + $shipping,
        'free_min' => $freeMin,
    ];
}

/* ---------------- رفع الصور ---------------- */

/**
 * يرفع صورة من $_FILES ويرجع المسار النسبي (uploads/xxx.jpg) أو null
 * @throws RuntimeException
 */
function upload_image(?array $file): ?string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('حصلت مشكلة في رفع الصورة.');
    if ($file['size'] > config('upload_max_size')) throw new RuntimeException('حجم الصورة أكبر من المسموح (4 ميجا).');

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime]) || !@getimagesize($file['tmp_name'])) {
        throw new RuntimeException('نوع الملف غير مدعوم. استخدم JPG أو PNG أو WEBP.');
    }
    $dir = config('upload_dir');
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $name = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('تعذر حفظ الصورة.');
    }
    return 'uploads/' . $name;
}

/** يعيد ترتيب مصفوفة $_FILES لعدة ملفات */
function files_array(string $key): array
{
    $f = $_FILES[$key] ?? null;
    if (!$f || !is_array($f['name'])) return [];
    $out = [];
    foreach ($f['name'] as $i => $n) {
        $out[] = ['name' => $n, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
    }
    return $out;
}

function delete_upload(?string $path): void
{
    if ($path && str_starts_with($path, 'uploads/')) {
        $full = __DIR__ . '/../public/' . $path;
        if (is_file($full)) @unlink($full);
    }
}

/* ---------------- بيانات ثابتة ---------------- */

function governorates(): array
{
    return [
        'القاهرة', 'الجيزة', 'الإسكندرية', 'القليوبية', 'الشرقية', 'الدقهلية', 'الغربية', 'المنوفية', 'البحيرة',
        'كفر الشيخ', 'دمياط', 'بورسعيد', 'الإسماعيلية', 'السويس', 'الفيوم', 'بني سويف', 'المنيا', 'أسيوط',
        'سوهاج', 'قنا', 'الأقصر', 'أسوان', 'البحر الأحمر', 'الوادي الجديد', 'مطروح', 'شمال سيناء', 'جنوب سيناء',
    ];
}

function order_statuses(): array
{
    return [
        'new'       => ['جديد', 'info'],
        'confirmed' => ['تم التأكيد', 'primary'],
        'shipped'   => ['جاري التوصيل', 'warning'],
        'delivered' => ['تم التسليم', 'success'],
        'cancelled' => ['ملغي', 'danger'],
    ];
}

function request_statuses(): array
{
    return [
        'new'       => ['جديد', 'info'],
        'scheduled' => ['تم تحديد موعد', 'primary'],
        'done'      => ['تم التنفيذ', 'success'],
        'cancelled' => ['ملغي', 'danger'],
    ];
}

function service_types(): array
{
    return ['تركيب فلتر جديد', 'تغيير شمعات', 'صيانة فلتر', 'تحليل مياه مجاني', 'استشارة محطة تحلية'];
}

function payment_methods(): array
{
    $m = ['cod' => 'الدفع عند الاستلام'];
    if (setting('enable_wallet', '1') === '1') $m['wallet'] = 'فودافون كاش / إنستاباي';
    return $m;
}

function status_badge(string $status, array $map): string
{
    [$label, $tone] = $map[$status] ?? [$status, 'muted'];
    return '<span class="badge badge-' . e($tone) . '">' . e($label) . '</span>';
}

function valid_phone(string $phone): bool
{
    $p = preg_replace('/[\s\-]/', '', strtr($phone, ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']));
    return (bool)preg_match('/^(\+?20)?0?1[0125]\d{8}$/', $p) || (bool)preg_match('/^0\d{8,10}$/', $p);
}

function normalize_digits(string $s): string
{
    return strtr($s, ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);
}

/** أيقونات SVG مدمجة */
function icon(string $name, int $size = 20, string $class = ''): string
{
    static $icons = [
        'cart'      => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>',
        'search'    => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'phone'     => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/>',
        'mail'      => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
        'clock'     => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'pin'       => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'menu'      => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'x'         => '<path d="M18 6 6 18M6 6l12 12"/>',
        'truck'     => '<path d="M10 17h4V5H2v12h3"/><path d="M20 17h2v-3.3a1 1 0 0 0-.2-.6l-3.5-4.4a1 1 0 0 0-.8-.4H14"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>',
        'tool'      => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.8-3.8a6 6 0 0 1-7.9 7.9l-6.9 6.9a2.1 2.1 0 0 1-3-3l6.9-6.9a6 6 0 0 1 7.9-7.9l-3.8 3.8z"/>',
        'shield'    => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
        'cash'      => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/>',
        'drop'      => '<path d="M12 2.7s-7 7.4-7 12.3a7 7 0 0 0 14 0c0-4.9-7-12.3-7-12.3z"/>',
        'check'     => '<path d="M20 6 9 17l-5-5"/>',
        'star'      => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/>',
        'whatsapp'  => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/><g transform="translate(7.3 7.3) scale(.4)" stroke-width="4.5"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></g>',
        'facebook'  => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
        'instagram' => '<rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>',
        'youtube'   => '<path d="M22.5 6.4a2.8 2.8 0 0 0-2-2C18.9 4 12 4 12 4s-6.9 0-8.5.4a2.8 2.8 0 0 0-2 2A29 29 0 0 0 1 12a29 29 0 0 0 .5 5.6 2.8 2.8 0 0 0 2 2C5.1 20 12 20 12 20s6.9 0 8.5-.4a2.8 2.8 0 0 0 2-2A29 29 0 0 0 23 12a29 29 0 0 0-.5-5.6z"/><path d="m9.8 15.5 5.7-3.5-5.7-3.5z"/>',
        'tiktok'    => '<path d="M16 3a5 5 0 0 0 5 5v3a8 8 0 0 1-5-1.7V15a6 6 0 1 1-6-6v3.2a2.8 2.8 0 1 0 2.8 2.8V3z"/>',
        'chevron-l' => '<path d="m15 18-6-6 6-6"/>',
        'chevron-r' => '<path d="m9 18 6-6-6-6"/>',
        'chevron-d' => '<path d="m6 9 6 6 6-6"/>',
        'arrow-up'  => '<path d="M12 19V5M5 12l7-7 7 7"/>',
        'plus'      => '<path d="M12 5v14M5 12h14"/>',
        'minus'     => '<path d="M5 12h14"/>',
        'trash'     => '<path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>',
        'edit'      => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
        'eye'       => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'home'      => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
        'grid'      => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
        'box'       => '<path d="M21 16V8a2 2 0 0 0-1-1.7l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.7l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.7z"/><path d="M3.3 7 12 12l8.7-5M12 22V12"/>',
        'tag'       => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L2 12V2h10l8.6 8.6a2 2 0 0 1 0 2.8z"/><path d="M7 7h.01"/>',
        'bag'       => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18M16 10a4 4 0 0 1-8 0"/>',
        'users'     => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
        'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
        'image'     => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/>',
        'message'   => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        'help'      => '<circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3M12 17h.01"/>',
        'logout'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'user'      => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'chart'     => '<path d="M3 3v18h18"/><path d="M18 17V9M13 17V5M8 17v-3"/>',
        'calendar'  => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'external'  => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>',
        'quote'     => '<path d="M3 21c3 0 7-1 7-8V5H3v7h4c0 4-2 5-4 5zM14 21c3 0 7-1 7-8V5h-7v7h4c0 4-2 5-4 5z"/>',
        'award'     => '<circle cx="12" cy="8" r="6"/><path d="M15.5 12.9 17 22l-5-3-5 3 1.5-9.1"/>',
        'filter'    => '<path d="M22 3H2l8 9.5V19l4 2v-8.5z"/>',
        'printer'   => '<path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/>',
    ];
    $paths = $icons[$name] ?? $icons['drop'];
    return '<svg class="icon ' . e($class) . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
}

/** رسالة خطأ تحت الحقل */
function err(array $errors, string $key): string
{
    return isset($errors[$key]) ? '<small class="field-err">' . e($errors[$key]) . '</small>' : '';
}

/** class للحقل اللي فيه خطأ */
function inv(array $errors, string $key): string
{
    return isset($errors[$key]) ? ' is-invalid' : '';
}
