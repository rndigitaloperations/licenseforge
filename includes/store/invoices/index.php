<?php
include $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sessioncheck.php';
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];

$invoiceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->execute([$invoiceId]);
$invoice = $stmt->fetch();

if (!$invoice) {
    showInvoiceNotFound();
}

$productStmt = $pdo->prepare("SELECT name, price FROM products WHERE id = ?");
$productStmt->execute([$invoice['product_id']]);
$product = $productStmt->fetch();

$productName = $product ? $product['name'] : 'Unknown';
$productPrice = $product ? $product['price'] : '0.00';

function renderStatusBadge($status) {
    $base = 'inline-block px-2 py-1 rounded-full text-xs font-semibold ';
    return match($status) {
        'unpaid' => '<span class="' . $base . 'bg-yellow-500 text-yellow-900">Unpaid</span>',
        'paid' => '<span class="' . $base . 'bg-green-600 text-green-100">Paid</span>',
        default => '<span class="' . $base . 'bg-gray-600 text-gray-100">Unknown</span>'
    };
}

function showInvoiceNotFound() {
    include $_SERVER['DOCUMENT_ROOT'] . '/includes/dashboard/me/navbar.php';

    echo <<<HTML
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/x-icon" href="{$meta['favicon']}" />
    <title>Invoice Not Found</title>

    <meta name="description" content="{$meta['description']}" />
    <meta name="keywords" content="{$meta['keywords']}" />

    <meta name="twitter:card" content="{$meta['twitter']['card']}" />
    <meta name="twitter:site" content="{$meta['twitter']['site']}" />
    <meta name="twitter:title" content="{$meta['twitter']['title']}" />
    <meta name="twitter:description" content="{$meta['twitter']['description']}" />
    <meta name="twitter:image" content="{$meta['twitter']['image']}" />

    <meta property="og:type" content="{$meta['opengraph']['type']}" />
    <meta property="og:url" content="{$meta['opengraph']['url']}" />
    <meta property="og:title" content="{$meta['opengraph']['title']}" />
    <meta property="og:description" content="{$meta['opengraph']['description']}" />
    <meta property="og:site_name" content="{$meta['opengraph']['site_name']}" />
    <meta property="og:image" content="{$meta['opengraph']['image']['url']}" />
    <meta property="og:image:type" content="{$meta['opengraph']['image']['type']}" />
    <meta property="og:image:width" content="{$meta['opengraph']['image']['width']}" />
    <meta property="og:image:height" content="{$meta['opengraph']['image']['height']}" />

    <link href="/includes/css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-900 text-gray-100 font-sans leading-normal tracking-normal">

  <div class="container w-full md:max-w-3xl mx-auto pt-20">
    <div class="w-full px-4 md:px-6 text-xl leading-normal">
      <div class="font-sans font-bold break-normal pt-6 pb-2 text-4xl">
        Invoice Not Found
      </div>
      <p class="py-6">
        We're sorry, but the invoice you are looking for does not exist or has been moved.
      </p>
      <div class="pb-6">
        <a href="/" class="no-underline border-2 border-gray-300 text-gray-300 inline-block rounded-lg px-4 py-2 mx-2 hover:bg-gray-700 hover:text-white hover:border-gray-700">
          Return to Homepage
        </a>
      </div>
    </div>
  </div>
</body>
</html>
HTML;
    exit;
}

$currency = $_SESSION['currency'] ?? 'USD';

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

$currencyIcon = $currencyIcons[$currency] ?? '';