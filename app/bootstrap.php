<?php
declare(strict_types=1);

require __DIR__ . '/helpers.php';
require __DIR__ . '/db.php';

date_default_timezone_set(config('timezone', 'Africa/Cairo'));
mb_internal_encoding('UTF-8');

if (config('debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_name('qatarat_sess');
session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 14,
    'path'     => '/',
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

set_exception_handler(function (Throwable $e) {
    error_log((string)$e);
    http_response_code(500);
    if (config('debug')) {
        echo '<pre>' . e((string)$e) . '</pre>';
    } else {
        echo '<!doctype html><meta charset="utf-8"><div style="font-family:sans-serif;text-align:center;padding:60px" dir="rtl"><h1>حصل خطأ غير متوقع</h1><p>من فضلك حاول تاني بعد شوية.</p></div>';
    }
});
