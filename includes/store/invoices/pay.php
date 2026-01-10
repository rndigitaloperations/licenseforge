<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];
$opensslKey = $config['openssl_key'];
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

function decrypt($data, $key) {
    $data = base64_decode($data);
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $ivLength);
    $ciphertext = substr($data, $ivLength);
    return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, 0, $iv);
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
        <a href="/dashboard/me/invoices.php" class="no-underline border-2 border-gray-300 text-gray-300 inline-block rounded-lg px-4 py-2 mx-2 hover:bg-gray-700 hover:text-white hover:border-gray-700">
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

function showInvoiceAlreadyPaid() {
    include $_SERVER['DOCUMENT_ROOT'] . '/includes/dashboard/me/navbar.php';

    echo <<<HTML
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/x-icon" href="{$meta['favicon']}" />
    <title>Invoice Already Paid</title>

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
        Invoice Already Paid
      </div>
      <p class="py-6">
        This invoice has already been paid. Thank you for your payment!
      </p>
      <div class="pb-6">
        <a href="/dashboard/me/invoices.php" class="no-underline border-2 border-gray-300 text-gray-300 inline-block rounded-lg px-4 py-2 mx-2 hover:bg-gray-700 hover:text-white hover:border-gray-700">
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

function showNoPaymentMethods() {
    include $_SERVER['DOCUMENT_ROOT'] . '/includes/dashboard/me/navbar.php';

    echo <<<HTML
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/x-icon" href="{$meta['favicon']}" />
    <title>No Payment Methods Available</title>

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
        Payment Unavailable
      </div>
      <p class="py-6">
        Unfortunately, there are no payment methods available at this time. Please try again later or contact support for assistance.
      </p>
      <div class="pb-6">
        <a href="/dashboard/me/invoices.php" class="no-underline border-2 border-gray-300 text-gray-300 inline-block rounded-lg px-4 py-2 mx-2 hover:bg-gray-700 hover:text-white hover:border-gray-700">
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

$stmt = $pdo->prepare("SELECT i.id, i.user_id, i.product_id, i.status, i.created_at, p.price, p.name FROM invoices i JOIN products p ON i.product_id = p.id WHERE i.id = ?");
$stmt->execute([$_GET['id']]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
    showInvoiceNotFound();
}

$price = (float)$invoice['price'];
$product = ['id' => $invoice['product_id'], 'name' => $invoice['name']];

if ($invoice['status'] === 'paid') {
    showInvoiceAlreadyPaid();
}

$stripeSecretKey = null;
$paypalClientId = null;
$paypalClientSecret = null;
$paypalSandbox = 0;
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

$settingStmt = $pdo->query("SELECT app_name FROM app_settings LIMIT 1");
$settings = $settingStmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT provider, client_id, client_secret, sandbox FROM payments WHERE provider IN ('stripe','paypal')");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($row['provider'] === 'stripe') {
        $stripeSecretKey = decrypt($row['client_secret'], $opensslKey);
    } elseif ($row['provider'] === 'paypal') {
        $paypalClientId = decrypt($row['client_id'], $opensslKey);
        $paypalClientSecret = decrypt($row['client_secret'], $opensslKey);
        $paypalSandbox = (int)$row['sandbox'];
    }
}

$stripeAvailable = !empty($stripeSecretKey);
$paypalAvailable = !empty($paypalClientId) && !empty($paypalClientSecret);

if (!$stripeAvailable && !$paypalAvailable) {
    showNoPaymentMethods();
}

$defaultPaymentMethod = $stripeAvailable ? 'stripe' : 'paypal';

function stripePost($url, $secretKey, $postFields) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_USERPWD => $secretKey . ':',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postFields),
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    return json_decode($response, true);
}

function stripeGet($url, $secretKey) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_USERPWD => $secretKey . ':',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    return json_decode($response, true);
}

function paypalGetAccessToken($clientId, $clientSecret, $sandbox = 0) {
    $url = $sandbox ? "https://api-m.sandbox.paypal.com/v1/oauth2/token" : "https://api-m.paypal.com/v1/oauth2/token";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => "grant_type=client_credentials",
        CURLOPT_USERPWD => $clientId . ':' . $clientSecret,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Accept-Language: en_US']
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    $data = json_decode($response, true);
    return $data['access_token'] ?? false;
}

function paypalCreateOrder($accessToken, $product, $price, $returnUrl, $cancelUrl, $sandbox = 0) {
    global $settings;
    $url = $sandbox ? "https://api-m.sandbox.paypal.com/v2/checkout/orders" : "https://api-m.paypal.com/v2/checkout/orders";
    $currency = $_SESSION['currency'] ?? 'USD';
    $appName = !empty($settings['app_name']) ? $settings['app_name'] : 'LicenseForge';

    $postData = [
        'intent' => 'CAPTURE',
        'purchase_units' => [[
            'amount' => [
                'currency_code' => $currency,
                'value' => number_format($price, 2, '.', '')
            ],
            'description' => $product['name']
        ]],
        'application_context' => [
            'brand_name' => $appName,
            'return_url' => $returnUrl,
            'cancel_url' => $cancelUrl
        ]
    ];
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($postData),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ]
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    return json_decode($response, true);
}

if (isset($_GET['session_id'])) {
    $session_id = $_GET['session_id'];
    $session = stripeGet("https://api.stripe.com/v1/checkout/sessions/$session_id", $stripeSecretKey);
    if ($session && $session['payment_status'] === 'paid') {
        if (isset($session['metadata']['product_id']) && (int)$session['metadata']['product_id'] === $product['id']) {
            $stmt = $pdo->prepare("UPDATE invoices SET status = 'paid' WHERE id = ?");
            $stmt->execute([$invoice['id']]);

            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE user_id = ? AND product_id = ? AND status = 'unpaid'");
            $stmtCheck->execute([$invoice['user_id'], $invoice['product_id']]);
            $unpaidCount = (int)$stmtCheck->fetchColumn();

            if ($unpaidCount === 0) {
                $stmtLicenses = $pdo->prepare("UPDATE licenses SET status = 'valid' WHERE user_id = ? AND product_id = ? AND status = 'suspended'");
                $stmtLicenses->execute([$invoice['user_id'], $invoice['product_id']]);

                $nextPayDateTime = (new DateTime())->modify('+1 month')->format('Y-m-d H:i:s');

                $stmtPurchases = $pdo->prepare("UPDATE purchases SET suspended = 'false', nextpay_day = ? WHERE user_id = ? AND product_id = ? AND suspended = 'true'");
                $stmtPurchases->execute([$nextPayDateTime, $invoice['user_id'], $invoice['product_id']]);
            }

            header('Location: /invoices/' . $invoice['id']);
            exit;
        }
    }
}

if (isset($_GET['token']) && isset($_GET['PayerID'])) {
    $accessToken = paypalGetAccessToken($paypalClientId, $paypalClientSecret, $paypalSandbox);
    if ($accessToken) {
        $orderId = $_GET['token'];
        $payerId = $_GET['PayerID'];
        $paypalApiBaseUrl = $paypalSandbox ? "https://api-m.sandbox.paypal.com" : "https://api-m.paypal.com";
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "$paypalApiBaseUrl/v2/checkout/orders/$orderId/capture",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]
        ]);
        $response = curl_exec($ch);
        if (!curl_errno($ch)) {
            $data = json_decode($response, true);
            if (isset($data['status']) && $data['status'] === 'COMPLETED') {
                $stmt = $pdo->prepare("UPDATE invoices SET status = 'paid' WHERE id = ?");
                $stmt->execute([$invoice['id']]);

                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE user_id = ? AND product_id = ? AND status = 'unpaid'");
                $stmtCheck->execute([$invoice['user_id'], $invoice['product_id']]);
                $unpaidCount = (int)$stmtCheck->fetchColumn();

                if ($unpaidCount === 0) {
                    $stmtLicenses = $pdo->prepare("UPDATE licenses SET status = 'valid' WHERE user_id = ? AND product_id = ? AND status = 'suspended'");
                    $stmtLicenses->execute([$invoice['user_id'], $invoice['product_id']]);

                    $nextPayDateTime = (new DateTime())->modify('+1 month')->format('Y-m-d H:i:s');

                    $stmtPurchases = $pdo->prepare("UPDATE purchases SET suspended = 'false', nextpay_day = ? WHERE user_id = ? AND product_id = ? AND suspended = 'true'");
                    $stmtPurchases->execute([$nextPayDateTime, $invoice['user_id'], $invoice['product_id']]);
                }

                header('Location: /invoices/' . $invoice['id']);
                exit;
            }
        }
        curl_close($ch);
    }
}

$stmt = $pdo->prepare("SELECT i.id, i.user_id, i.product_id, i.status, i.created_at, p.price, p.name FROM invoices i JOIN products p ON i.product_id = p.id WHERE i.id = ?");
$stmt->execute([$_GET['id']]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
    showInvoiceNotFound();
}

if ($invoice['status'] === 'paid') {
    showInvoiceAlreadyPaid();
}

$price = (float)$invoice['price'];
$product = ['id' => $invoice['product_id'], 'name' => $invoice['name']];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_provider'])) {
    if (!isset($_SESSION['user_id'], $_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
        header("Location: /login?redirect=/invoices/pay/" . urlencode($invoice['id']));
        exit;
    }

    $provider = $_POST['payment_provider'];

    if ($provider === 'stripe' && $stripeAvailable) {
        $session = stripePost('https://api.stripe.com/v1/checkout/sessions', $stripeSecretKey, [
            'mode' => 'payment',
            'success_url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/invoices/pay/{$invoice['id']}?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/invoices/pay/{$invoice['id']}",
            'metadata' => ['product_id' => $product['id']],
            'line_items[0][price_data][currency]' => strtolower($currency),
            'line_items[0][price_data][product_data][name]' => $product['name'],
            'line_items[0][price_data][unit_amount]' => (int)($price * 100),
            'line_items[0][quantity]' => 1,
        ]);
        if ($session && isset($session['id']) && isset($session['url'])) {
            header('Location: ' . $session['url']);
            exit;
        }
    } elseif ($provider === 'paypal' && $paypalAvailable) {
        $accessToken = paypalGetAccessToken($paypalClientId, $paypalClientSecret, $paypalSandbox);
        if ($accessToken) {
            $successUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/invoices/pay/{$invoice['id']}";
            $cancelUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/invoices/pay/{$invoice['id']}";
            $order = paypalCreateOrder($accessToken, $product, $price, $successUrl, $cancelUrl, $paypalSandbox);
            if ($order && isset($order['links'])) {
                foreach ($order['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        header('Location: ' . $link['href']);
                        exit;
                    }
                }
            }
        }
    }
}
?>