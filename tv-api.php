<?php
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');

/*
 * 1. خواندن مسیر درخواست
 * مثال:
 * /tv-api.php/song/11210778
 */

$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/tv-api.php';

$path = '';

if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] !== '') {
    $path = $_SERVER['PATH_INFO'];
} else {
    $pos = strpos($uri, $script);

    if ($pos !== false) {
        $path = substr($uri, $pos + strlen($script));
    }
}

$path = trim(parse_url($path, PHP_URL_PATH), '/');
$parts = ($path === '') ? array() : explode('/', $path);

$page = isset($parts[0]) ? strtolower($parts[0]) : '';
$q1   = isset($parts[1]) ? $parts[1] : '';
$q2   = isset($parts[2]) ? $parts[2] : '';

/*
 * 2. انتقال پارامترها به ساختار API اصلی
 */

$_GET['p']  = $page;
$_GET['q1'] = $q1;
$_GET['q2'] = $q2;

$_REQUEST['p']  = $page;
$_REQUEST['q1'] = $q1;
$_REQUEST['q2'] = $q2;

/*
 * 3. تست ساده خود endpoint
 */

if ($page === '') {
    echo json_encode(array(
        'status' => 'ok',
        'endpoint' => 'tv-api',
        'message' => 'TV API is working'
    ));
    exit;
}

/*
 * 4. بارگذاری فایل‌های اصلی Hamrahang
 */

$root = dirname(__DIR__);

define('HA_BOOT', 1);

require_once $root . '/head_inc.php';
require_once $root . '/db.php';
require_once $root . '/auth.php';
require_once $root . '/int/h_api.php';

/*
 * کنترل نهایی؛ اگر هیچ مسیری پاسخ نداد.
 */
echo json_encode(array(
    'error' => 'route_not_handled'
));
?>