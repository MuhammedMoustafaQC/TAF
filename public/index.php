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
    ['GET',      '/',                           'site_home'],
    ['GET',      '/shop',                       'site_shop'],
    ['GET',      '/offers',                     'site_offers'],
    ['GET',      '/search',                     'site_shop'],
    ['GET',      '/category/{slug}',            'site_category'],
    ['GET',      '/brands',                     'site_brands'],
    ['GET',      '/brand/{slug}',               'site_brand'],
    ['GET',      '/product/{slug}',             'site_product'],
    ['POST',     '/product/{slug}/review',      'site_review'],
    ['GET',      '/cart',                       'site_cart'],
    ['POST',     '/cart/add',                   'site_cart_add'],
    ['POST',     '/cart/update',                'site_cart_update'],
    ['GET|POST', '/checkout',                   'site_checkout'],
    ['GET',      '/order/{code}',               'site_order_done'],
    ['GET|POST', '/track',                      'site_track'],
    ['GET|POST', '/service-request',            'site_service_request'],
    ['GET',      '/maintenance',                'site_maintenance_redirect'],
    ['GET|POST', '/installments',               'site_installments'],
    ['GET|POST', '/contact',                    'site_contact'],
    ['GET',      '/about',                      'site_about'],
    ['GET',      '/faq',                        'site_faq'],
    ['GET',      '/services',                   'site_services'],
    ['GET',      '/blog',                       'site_blog'],
    ['GET',      '/blog/{slug}',                'site_post'],
    ['GET',      '/page/{slug}',                'site_page'],
    ['POST',     '/newsletter',                 'site_newsletter'],
    ['POST',     '/location',                   'site_location'],
    ['GET',      '/wishlist',                   'site_wishlist'],
    ['POST',     '/wishlist/toggle',            'site_wishlist_toggle'],
    ['GET|POST', '/account/login',              'site_login'],
    ['GET|POST', '/account/register',           'site_register'],
    ['POST',     '/account/logout',             'site_logout'],
    ['GET|POST', '/account',                    'site_account'],

    // ---------- لوحة التحكم ----------
    ['GET|POST', '/admin/login',                'admin_login'],
    ['POST',     '/admin/logout',               'admin_logout'],
    ['GET',      '/admin',                      'admin_dashboard'],
    ['GET',      '/admin/products',             'admin_products'],
    ['GET|POST', '/admin/products/new',         'admin_product_form'],
    ['GET|POST', '/admin/products/{id}/edit',   'admin_product_form'],
    ['POST',     '/admin/products/{id}/toggle', 'admin_product_toggle'],
    ['POST',     '/admin/products/{id}/delete', 'admin_product_delete'],
    ['GET|POST', '/admin/categories',           'admin_categories'],
    ['POST',     '/admin/categories/{id}/delete', 'admin_category_delete'],
    ['GET|POST', '/admin/brands',               'admin_brands'],
    ['POST',     '/admin/brands/{id}/delete',   'admin_brand_delete'],
    ['GET',      '/admin/orders',               'admin_orders'],
    ['GET',      '/admin/orders/{id}',          'admin_order'],
    ['POST',     '/admin/orders/{id}/status',   'admin_order_status'],
    ['POST',     '/admin/orders/{id}/delete',   'admin_order_delete'],
    ['GET',      '/admin/customers',            'admin_customers'],
    ['GET',      '/admin/customers/{id}',       'admin_customer'],
    ['GET',      '/admin/reviews',              'admin_reviews'],
    ['POST',     '/admin/reviews/{id}/approve', 'admin_review_approve'],
    ['POST',     '/admin/reviews/{id}/delete',  'admin_review_delete'],
    ['GET',      '/admin/requests',             'admin_requests'],
    ['POST',     '/admin/requests/{id}/status', 'admin_request_status'],
    ['POST',     '/admin/requests/{id}/delete', 'admin_request_delete'],
    ['GET',      '/admin/installments',         'admin_installments'],
    ['POST',     '/admin/installments/{id}/status', 'admin_installment_status'],
    ['POST',     '/admin/installments/{id}/delete', 'admin_installment_delete'],
    ['GET',      '/admin/messages',             'admin_messages'],
    ['POST',     '/admin/messages/{id}/read',   'admin_message_read'],
    ['POST',     '/admin/messages/{id}/delete', 'admin_message_delete'],
    ['GET',      '/admin/subscribers',          'admin_subscribers'],
    ['POST',     '/admin/subscribers/{id}/delete', 'admin_subscriber_delete'],
    ['GET|POST', '/admin/slides',               'admin_slides'],
    ['POST',     '/admin/slides/{id}/delete',   'admin_slide_delete'],
    ['GET|POST', '/admin/banners',              'admin_banners'],
    ['POST',     '/admin/banners/{id}/delete',  'admin_banner_delete'],
    ['GET',      '/admin/posts',                'admin_posts'],
    ['GET|POST', '/admin/posts/new',            'admin_post_form'],
    ['GET|POST', '/admin/posts/{id}/edit',      'admin_post_form'],
    ['POST',     '/admin/posts/{id}/delete',    'admin_post_delete'],
    ['GET',      '/admin/pages',                'admin_pages'],
    ['GET|POST', '/admin/pages/{id}/edit',      'admin_page_form'],
    ['GET|POST', '/admin/faqs',                 'admin_faqs'],
    ['POST',     '/admin/faqs/{id}/delete',     'admin_faq_delete'],
    ['GET|POST', '/admin/settings',             'admin_settings'],
    ['GET|POST', '/admin/users',                'admin_users'],
    ['POST',     '/admin/users/{id}/delete',    'admin_user_delete'],
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
