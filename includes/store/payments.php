<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$opensslKey = $config['openssl_key'];

$stripeSecretKey = null;
$paypalClientId = null;
$paypalClientSecret = null;
$paypalSandbox = 0;
$currency = $_SESSION['currency'] ?? 'USD';
$domain_or_ip = $_COOKIE['product_domain_or_ip'] ?? null;

$settingStmt = $pdo->query("SELECT app_name FROM app_settings LIMIT 1");
$settings = $settingStmt->fetch(PDO::FETCH_ASSOC);

function decrypt($data, $key) {
    $data = base64_decode($data);
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $ivLength);
    $ciphertext = substr($data, $ivLength);
    return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, 0, $iv);
}

function encryptLicense($plaintext, $key) {
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($ivLength);
    $encrypted = openssl_encrypt($plaintext, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function generateLicenseKey($length, $prefix = null) {
    $key = strtoupper(bin2hex(random_bytes($length)));
    if ($prefix !== null && $prefix !== '') {
        return $prefix . '-' . $key;
    }
    return $key;
}

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
    $domain_or_ip = $_COOKIE['product_domain_or_ip'] ?? null;
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

$paymentSuccess = false;
$showPaymentSuccess = false;

if (isset($_GET['session_id'])) {
    $session_id = $_GET['session_id'];
    $session = stripeGet("https://api.stripe.com/v1/checkout/sessions/$session_id", $stripeSecretKey);
    if ($session && $session['payment_status'] === 'paid') {
        if (isset($session['metadata']['product_id']) && (int)$session['metadata']['product_id'] === $product['id']) {
            if (isset($_SESSION['user_id'], $_SESSION['loggedIn']) && $_SESSION['loggedIn']) {
                $userId = $_SESSION['user_id'];
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$userId, $product['id']]);
                if ($stmt->fetchColumn() == 0) {
                    $order_id = $session['payment_intent'] ?? $session_id;
                    $stmt = $pdo->prepare("INSERT INTO purchases (user_id, product_id, order_id, created_at, suspended, nextpay_day) VALUES (?, ?, ?, NOW(), 'false', DATE_ADD(NOW(), INTERVAL 1 MONTH))");
                    $stmt->execute([$userId, $product['id'], $order_id]);
                    $stmt = $pdo->prepare("INSERT INTO invoices (user_id, product_id, status, created_at) VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$userId, $product['id'], "paid"]);
                    $settingsStmt = $pdo->query("SELECT license_prefix, license_length FROM settings LIMIT 1");
                    $settings = $settingsStmt->fetch(PDO::FETCH_ASSOC);
                    $licensePrefix = $settings['license_prefix'] ?? null;
                    $licenseLength = isset($settings['license_length']) && is_numeric($settings['license_length']) ? (int)$settings['license_length'] : 16;
                    $licenseKey = generateLicenseKey($licenseLength, $licensePrefix);
                    $encryptedLicenseKey = encryptLicense($licenseKey, $opensslKey);
                    $stmt = $pdo->prepare("INSERT INTO licenses (user_id, domain_or_ip, license_key, product_id, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$userId, $domain_or_ip, $encryptedLicenseKey, $product['id'], "valid"]);
                }
                setcookie('product_domain_or_ip', '', time() - 3600, '/');
                $paymentSuccess = true;
                $showPaymentSuccess = true;
            }
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
                if (isset($_SESSION['user_id'], $_SESSION['loggedIn']) && $_SESSION['loggedIn']) {
                    $userId = $_SESSION['user_id'];
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$userId, $product['id']]);
                    if ($stmt->fetchColumn() == 0) {
                        $stmt = $pdo->prepare("INSERT INTO purchases (user_id, product_id, order_id, created_at, suspended, nextpay_day) VALUES (?, ?, ?, NOW(), 'false', DATE_ADD(NOW(), INTERVAL 1 MONTH))");
                        $stmt->execute([$userId, $product['id'], $orderId]);
                        $stmt = $pdo->prepare("INSERT INTO invoices (user_id, product_id, status, created_at) VALUES (?, ?, ?, NOW())");
                        $stmt->execute([$userId, $product['id'], "paid"]);
                        $settingsStmt = $pdo->query("SELECT license_prefix, license_length FROM settings LIMIT 1");
                        $settings = $settingsStmt->fetch(PDO::FETCH_ASSOC);
                        $licensePrefix = $settings['license_prefix'] ?? null;
                        $licenseLength = isset($settings['license_length']) && is_numeric($settings['license_length']) ? (int)$settings['license_length'] : 16;
                        $licenseKey = generateLicenseKey($licenseLength, $licensePrefix);
                        $encryptedLicenseKey = encryptLicense($licenseKey, $opensslKey);
                        $stmt = $pdo->prepare("INSERT INTO licenses (user_id, domain_or_ip, license_key, product_id, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                        $stmt->execute([$userId, $domain_or_ip, $encryptedLicenseKey, $product['id'], "valid"]);
                    }
                    setcookie('product_domain_or_ip', '', time() - 3600, '/');
                    $paymentSuccess = true;
                    $showPaymentSuccess = true;
                }
            }
        }
        curl_close($ch);
    }
}

$price = (float)$product['price'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_now']) && isset($_POST['payment_provider'])) {
    if (!isset($_SESSION['user_id'], $_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
        header("Location: /login?redirect={$_SERVER['REQUEST_URI']}");
        exit;
    }

    $provider = $_POST['payment_provider'];

    if ($provider === 'stripe') {
        $session = stripePost('https://api.stripe.com/v1/checkout/sessions', $stripeSecretKey, [
            'mode' => 'payment',
            'success_url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}",
            'metadata[product_id]' => $product['id'],
            'line_items[0][price_data][currency]' => strtolower($currency),
            'line_items[0][price_data][product_data][name]' => $product['name'],
            'line_items[0][price_data][unit_amount]' => (int)($price * 100),
            'line_items[0][quantity]' => 1,
        ]);
        if ($session && isset($session['id']) && isset($session['url'])) {
            header('Location: ' . $session['url']);
            exit;
        }
    } elseif ($provider === 'paypal') {
        $accessToken = paypalGetAccessToken($paypalClientId, $paypalClientSecret, $paypalSandbox);
        if ($accessToken) {
            $successUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
            $cancelUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
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