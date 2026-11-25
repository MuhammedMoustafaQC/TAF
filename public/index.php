<?php
/**
 * قطرات الندى — نقطة الدخول الوحيدة للموقع (Front Controller)
 */

// مع سيرفر PHP المدمج: قدّم الملفات الموجودة مباشرة (صور، CSS، JS)
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if ($file !== __DIR__ . '/' && is_file($file)) return false;
}

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/site.php';
require __DIR__ . '/../app/admin.php';

$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$base = base_path();
if ($base !== '' && str_starts_with($path, $base)) $path = substr($path, strlen($base));
$path = '/' . trim($path, '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$routes = [
    // ---------- الموقع ----------
    ['GET',      '/',                         'site_home'],
    ['GET',      '/shop',                     'site_shop'],
    ['GET',      '/offers',                   'site_offers'],
    ['GET',      '/search',                   'site_shop'],
    ['GET',      '/category/{slug}',          'site_category'],
    ['GET',      '/product/{slug}',           'site_product'],
    ['GET',      '/cart',                     'site_cart'],
    ['POST',     '/cart/add',                 'site_cart_add'],
    ['POST',     '/cart/update',              'site_cart_update'],
    ['GET|POST', '/checkout',                 'site_checkout'],
    ['GET',      '/order/{code}',             'site_order_done'],
    ['GET|POST', '/track',                    'site_track'],
    ['GET|POST', '/maintenance',              'site_maintenance'],
    ['GET|POST', '/contact',                  'site_contact'],
    ['GET',      '/about',                    'site_about'],
    ['GET',      '/faq',                      'site_faq'],

    // ---------- لوحة التحكم ----------
    ['GET|POST', '/admin/login',              'admin_login'],
    ['POST',     '/admin/logout',             'admin_logout'],
    ['GET',      '/admin',                    'admin_dashboard'],
    ['GET',      '/admin/products',           'admin_products'],
    ['GET|POST', '/admin/products/new',       'admin_product_form'],
    ['GET|POST', '/admin/products/{id}/edit', 'admin_product_form'],
    ['POST',     '/admin/products/{id}/toggle', 'admin_product_toggle'],
    ['POST',     '/admin/products/{id}/delete', 'admin_product_delete'],
    ['GET|POST', '/admin/categories',         'admin_categories'],
    ['POST',     '/admin/categories/{id}/delete', 'admin_category_delete'],
    ['GET',      '/admin/orders',             'admin_orders'],
    ['GET',      '/admin/orders/{id}',        'admin_order'],
    ['POST',     '/admin/orders/{id}/status', 'admin_order_status'],
    ['POST',     '/admin/orders/{id}/delete', 'admin_order_delete'],
    ['GET',      '/admin/requests',           'admin_requests'],
    ['POST',     '/admin/requests/{id}/status', 'admin_request_status'],
    ['POST',     '/admin/requests/{id}/delete', 'admin_request_delete'],
    ['GET',      '/admin/messages',           'admin_messages'],
    ['POST',     '/admin/messages/{id}/read', 'admin_message_read'],
    ['POST',     '/admin/messages/{id}/delete', 'admin_message_delete'],
    ['GET|POST', '/admin/slides',             'admin_slides'],
    ['POST',     '/admin/slides/{id}/delete', 'admin_slide_delete'],
    ['GET|POST', '/admin/testimonials',       'admin_testimonials'],
    ['POST',     '/admin/testimonials/{id}/delete', 'admin_testimonial_delete'],
    ['GET|POST', '/admin/faqs',               'admin_faqs'],
    ['POST',     '/admin/faqs/{id}/delete',   'admin_faq_delete'],
    ['GET|POST', '/admin/settings',           'admin_settings'],
    ['GET|POST', '/admin/users',              'admin_users'],
    ['POST',     '/admin/users/{id}/delete',  'admin_user_delete'],
];

foreach ($routes as [$methods, $pattern, $handler]) {
    $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern) . '$#u';
    if (!preg_match($regex, $path, $m)) continue;
    if (!in_array($method, explode('|', $methods), true)) {
        http_response_code(405);
        exit('Method Not Allowed');
    }
    if ($method === 'POST') verify_csrf();
    $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
    $handler(...$params);
    exit;
}

not_found();
