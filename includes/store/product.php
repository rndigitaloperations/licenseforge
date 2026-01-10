<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/Parsedown.php';
$Parsedown = new Parsedown();

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];

$productId = isset($_GET['productId']) ? (int)$_GET['productId'] : 0;
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($productId <= 0) {
    http_response_code(404);
    echo "Invalid product ID.";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND category_id = ?");
$stmt->execute([$productId, $id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/errors.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM payments WHERE enabled = 1 ORDER BY provider ASC");
$stmt->execute();
$enabledPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

function findDownloadFile($productName) {
    $downloadDir = $_SERVER['DOCUMENT_ROOT'] . '/downloads/';
    $extensions = ['zip', 'tar', 'tar.gz', 'rar', '7z'];
    foreach ($extensions as $ext) {
        $file = $downloadDir . $productName . '.' . $ext;
        if (file_exists($file)) return '/downloads/' . $productName . '.' . $ext;
    }
    return false;
}

$price = (float)$product['price'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['domain_or_ip']) && !empty($_POST['domain_or_ip'])) {
    setcookie('product_domain_or_ip', $_POST['domain_or_ip'], time() + (30 * 24 * 60 * 60), '/');
    $_COOKIE['product_domain_or_ip'] = $_POST['domain_or_ip'];
}

$userHasProduct = false;
$userHasProductSuspended = false;

if ($price == 0 || $price == 0.00 || (isset($_SESSION['user_id'], $_SESSION['loggedIn']) && $_SESSION['loggedIn'])) {
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT suspended FROM purchases WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $product['id']]);
    $purchase = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($purchase) {
        $userHasProduct = true;
        $userHasProductSuspended = ($purchase['suspended'] === 'true');
    }
}

if ($userHasProduct) {
    $downloadLink = findDownloadFile($product['name']);
}

$currencyIcons = [
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£',
    'AUD' => '$',
    'CAD' => '$',
    'JPY' => '¥',
    'CHF' => 'CHF',
    'NZD' => '$',
    'SEK' => 'kr',
    'NOK' => 'kr',
    'DKK' => 'kr',
    'ZAR' => 'R',
    'SGD' => '$',
    'HKD' => '$',
    'MXN' => 'Mex$',
    'BRL' => 'R$',
];

$currency = $_SESSION['currency'] ?? 'USD';

$currencyIcon = $currencyIcons[$currency] ?? '';

?>