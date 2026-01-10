<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];

$stmt = $pdo->prepare("SELECT id, name FROM categorys ORDER BY order_id ASC");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$settingStmt = $pdo->query("SELECT app_name, contact_forum_enabled, store_enabled FROM app_settings LIMIT 1");
$settings = $settingStmt->fetch(PDO::FETCH_ASSOC);
$appName = !empty($settings['app_name']) ? $settings['app_name'] : 'LicenseForge';
$contactEnabled = isset($settings['contact_forum_enabled']) ? (int)$settings['contact_forum_enabled'] : 1;
$storeEnabled = isset($settings['store_enabled']) ? (int)$settings['store_enabled'] : 1;

$user = null;
if (!empty($_SESSION['user_id'])) {
    $userStmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['user_id']]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $gravatarHash = md5(strtolower(trim($user['email'])));
        $gravatarUrl = "https://www.gravatar.com/avatar/$gravatarHash?s=32&d=identicon";
    }
}

$supportedCurrencies = [
    'USD' => 'USD',
    'EUR' => 'EUR',
    'GBP' => 'GBP',
    'AUD' => 'AUD',
    'CAD' => 'CAD',
    'JPY' => 'JPY',
    'CHF' => 'CHF',
    'NZD' => 'NZD',
    'SEK' => 'SEK',
    'NOK' => 'NOK',
    'DKK' => 'DKK',
    'ZAR' => 'ZAR',
    'SGD' => 'SGD',
    'HKD' => 'HKD',
    'MXN' => 'MXN',
    'BRL' => 'BRL',
];

$defaultCurrency = 'USD';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['currency'])) {
    $chosenCurrency = $_POST['currency'];
    if (array_key_exists($chosenCurrency, $supportedCurrencies)) {
        $_SESSION['currency'] = $chosenCurrency;
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

$currency = $_SESSION['currency'] ?? $defaultCurrency;
?>