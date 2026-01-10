<?php
$configPath = $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

if (!file_exists($configPath)) {
    header('Location: /installer');
    exit;
}

$config = include $configPath;

if (empty($config['installed']) || $config['installed'] === false) {
    header('Location: /installer');
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$requestUri = $uri;
$scriptName = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$path = '/' . ltrim(str_replace($scriptName, '', $requestUri), '/');
$segments = explode('/', trim($path, '/'));
$firstSegment = $segments[0] ?? '';
$ignore = ['dashboard', 'includes', 'downloads'];

if (in_array($firstSegment, $ignore)) {
    $filePath = $_SERVER['DOCUMENT_ROOT'] . $path . '.php';
    if (file_exists($filePath)) {
        require $filePath;
        exit;
    }
    http_response_code(404);
    $_SERVER['REDIRECT_STATUS'] = '404';
    require 'errors.php';
    exit;
}

$storeEnabled = true;
try {
    $stmt = $pdo->query("SELECT store_enabled FROM app_settings LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result !== false && isset($result['store_enabled'])) {
        $storeEnabled = (bool)$result['store_enabled'];
    }
} catch (Exception $e) {
    $storeEnabled = true;
}

if ($path === '/api/verify/license' || $path === '') {
    require 'api/licenses-verify/index.php';
    exit;
}

if ($path === '/' || $path === '') {
    if ($storeEnabled) {
        require 'store/categorys.php';
    } else {
        http_response_code(502);
        $_SERVER['REDIRECT_STATUS'] = '502';
        require 'errors.php';
    }
    exit;
}

if ($path === '/crons/cron' || $path === '') {
    require 'vendor/cron.php';
    exit;
}

if (preg_match('#^/(\d+)/products$#', $path, $matches)) {
    if ($storeEnabled) {
        $_GET['id'] = (int)$matches[1];
        require 'store/category.php';
    } else {
        http_response_code(502);
        $_SERVER['REDIRECT_STATUS'] = '502';
        require 'errors.php';
    }
    exit;
}

if (preg_match('#^/(\d+)/products/(\d+)$#', $path, $matches)) {
    if ($storeEnabled) {
        $_GET['id'] = (int)$matches[1];
        $_GET['productId'] = (int)$matches[2];
        require 'store/product.php';
    } else {
        http_response_code(502);
        $_SERVER['REDIRECT_STATUS'] = '502';
        require 'errors.php';
    }
    exit;
}

if (preg_match('#^/invoices/(\d+)$#', $path, $matches)) {
    $_GET['id'] = (int)$matches[1];
    require 'store/invoices/index.php';
    exit;
}

if (preg_match('#^/invoices/pay/(\d+)$#', $path, $matches)) {
    $_GET['id'] = (int)$matches[1];
    require 'store/invoices/pay.php';
    exit;
}

if ($path === '/dashboard/me/connect' || $path === '') {
    require 'includes/dashboard/me/connect.php';
    exit;
}

if ($path === '/login/social' || $path === '') {
    require 'includes/social-login.php';
    exit;
}

http_response_code(404);
$_SERVER['REDIRECT_STATUS'] = '404';
require 'errors.php';