<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sessioncheck.php';
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

$opensslKey = $config['openssl_key'];

function encrypt($plaintext, $key) {
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($ivLength);
    $encrypted = openssl_encrypt($plaintext, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function decrypt($ciphertext_base64, $key) {
    $data = base64_decode($ciphertext_base64);
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $ivLength);
    $ciphertext = substr($data, $ivLength);
    return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, 0, $iv);
}

$providers = ['stripe', 'paypal'];
$loginProviders = ['discord', 'google'];
$analyticsProviders = ['google', 'cloudflare'];

$success = '';
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = 'Settings have been saved successfully!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_payments') {
    foreach ($providers as $provider) {
        $enabled = isset($_POST["{$provider}_enabled"]) ? 1 : 0;
        $sandbox = isset($_POST["{$provider}_sandbox_enabled"]) ? 1 : 0;
        $rawClientId = trim($_POST["{$provider}_client_id"] ?? '');
        $rawClientSecret = trim($_POST["{$provider}_client_secret"] ?? '');

        $clientIdEncrypted = $rawClientId !== '' ? encrypt($rawClientId, $opensslKey) : '';
        $clientSecretEncrypted = $rawClientSecret !== '' ? encrypt($rawClientSecret, $opensslKey) : '';

        $stmt = $pdo->prepare("
         INSERT INTO payments (provider, enabled, client_id, client_secret, sandbox)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            provider = VALUES(provider),
            enabled = VALUES(enabled),
            client_id = VALUES(client_id),
            client_secret = VALUES(client_secret),
            sandbox = VALUES(sandbox)
      ");
      $stmt->execute([
          $provider,
          $enabled,
          $clientIdEncrypted,
          $clientSecretEncrypted,
          $sandbox
      ]);
    }

    header("Location: /dashboard/settings.php?tab=payments&success=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_license_settings') {
    $licensePrefix = trim($_POST['license_key_prefix'] ?? '');
    $licenseLength = trim($_POST['license_key_length'] ?? '');

    if ($licenseLength === '') {
        die('License length is required.');
    }

    $licensePrefixValue = $licensePrefix !== '' ? $licensePrefix : null;

    $stmt = $pdo->query("SELECT 1 FROM settings LIMIT 1");
    $rowExists = $stmt->fetch() !== false;

    if ($rowExists) {
        $stmt = $pdo->prepare("UPDATE settings SET license_prefix = ?, license_length = ?");
    } else {
        $stmt = $pdo->prepare("INSERT INTO settings (license_prefix, license_length) VALUES (?, ?)");
    }

    $stmt->execute([$licensePrefixValue, $licenseLength]);

    header("Location: /dashboard/settings.php?tab=licenses&success=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_analytics_settings') {
    foreach ($analyticsProviders as $provider) {
        $enabled = isset($_POST["{$provider}_enabled"]) ? 1 : 0;
        $rawMeasurementId = trim($_POST["{$provider}_measurement_id"] ?? '');

        $measurementIdEncrypted = $rawMeasurementId !== '' ? encrypt($rawMeasurementId, $opensslKey) : '';

        $stmt = $pdo->prepare("
         INSERT INTO analytics (provider, enabled, measurement_id)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE
            provider = VALUES(provider),
            enabled = VALUES(enabled),
            measurement_id = VALUES(measurement_id)
      ");
      $stmt->execute([
          $provider,
          $enabled,
          $measurementIdEncrypted
      ]);
    }

    header("Location: /dashboard/settings.php?tab=analytics&success=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_recaptcha_settings') {
    $recaptchaProvider = trim($_POST['recaptcha_provider'] ?? '');
    $rawSiteKey = trim($_POST['recaptcha_site_key'] ?? '');
    $rawSecretKey = trim($_POST['recaptcha_secret_key'] ?? '');

    $siteKeyEncrypted = $rawSiteKey !== '' ? encrypt($rawSiteKey, $opensslKey) : '';
    $secretKeyEncrypted = $rawSecretKey !== '' ? encrypt($rawSecretKey, $opensslKey) : '';

    $stmt = $pdo->query("SELECT 1 FROM settings LIMIT 1");
    $rowExists = $stmt->fetch() !== false;

    if ($rowExists) {
        $stmt = $pdo->prepare("UPDATE settings SET recaptcha_provider = ?, recaptcha_site_key = ?, recaptcha_secret_key = ?");
        $stmt->execute([$recaptchaProvider, $siteKeyEncrypted, $secretKeyEncrypted]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO settings (recaptcha_provider, recaptcha_site_key, recaptcha_secret_key) VALUES (?, ?, ?)");
        $stmt->execute([$recaptchaProvider, $siteKeyEncrypted, $secretKeyEncrypted]);
    }

    header("Location: /dashboard/settings.php?tab=recaptcha&success=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_email_settings') {
    $loginDedection = isset($_POST['login_detection_enabled']) ? 1 : 0;

    $stmt = $pdo->query("SELECT 1 FROM email_settings LIMIT 1");
    $rowExists = $stmt->fetch() !== false;

    if ($rowExists) {
        $stmt = $pdo->prepare("UPDATE email_settings SET login_detection_enabled = ?");
    } else {
        $stmt = $pdo->prepare("INSERT INTO email_settings (login_detection_enabled) VALUES (?)");
    }

    $stmt->execute([$loginDedection]);

    header("Location: /dashboard/settings.php?tab=emails&success=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_login') {
    foreach ($loginProviders as $loginProvider) {
        $enabled = isset($_POST["{$loginProvider}_enabled"]) ? 1 : 0;
        $rawClientId = trim($_POST["{$loginProvider}_client_id"] ?? '');
        $rawClientSecret = trim($_POST["{$loginProvider}_client_secret"] ?? '');

        $clientIdEncrypted = $rawClientId !== '' ? encrypt($rawClientId, $opensslKey) : '';
        $clientSecretEncrypted = $rawClientSecret !== '' ? encrypt($rawClientSecret, $opensslKey) : '';

        $stmt = $pdo->prepare("
         INSERT INTO social_logins (provider, enabled, client_id, client_secret)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            provider = VALUES(provider),
            enabled = VALUES(enabled),
            client_id = VALUES(client_id),
            client_secret = VALUES(client_secret)
      ");
      $stmt->execute([
          $loginProvider,
          $enabled,
          $clientIdEncrypted,
          $clientSecretEncrypted
      ]);
    }

    header("Location: /dashboard/settings.php?tab=login&success=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_app_settings') {
    $appName = $_POST['app_name'] ?? 'LicenseForge';
    $storeEnabled = isset($_POST['store_enabled']) ? 1 : 0;
    $contactForumEnabled = isset($_POST['contact_forum_enabled']) ? 1 : 0;
    $contactForumSendTo = $_POST['contact_forum_to_email'] ?? null;

    $stmt = $pdo->query("SELECT 1 FROM app_settings LIMIT 1");
    $rowExists = $stmt->fetch() !== false;

    if ($rowExists) {
        $stmt = $pdo->prepare("UPDATE app_settings SET app_name = ?, store_enabled = ?, contact_forum_enabled = ?, contact_forum_to_email = ?");
    } else {
        $stmt = $pdo->prepare("INSERT INTO app_settings (app_name, store_enabled, contact_forum_enabled, contact_forum_to_email) VALUES (?, ?, ?, ?)");
    }

    $stmt->execute([$appName, $storeEnabled, $contactForumEnabled, $contactForumSendTo]);

    header("Location: /dashboard/settings.php?tab=app&success=1");
    exit;
}

$stmt = $pdo->query("SELECT * FROM payments");
$paymentConfigs = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $payment) {
    $payment['client_id'] = !empty($payment['client_id']) ? decrypt($payment['client_id'], $opensslKey) : '';
    $payment['client_secret'] = !empty($payment['client_secret']) ? decrypt($payment['client_secret'], $opensslKey) : '';
    $paymentConfigs[$payment['provider']] = $payment;
}

$stmt = $pdo->query("SELECT license_prefix, license_length FROM settings LIMIT 1");
$licenseConfig = $stmt->fetch(PDO::FETCH_ASSOC);

$licenseConfigs = [
    'license_key_prefix' => $licenseConfig['license_prefix'] ?? '',
    'license_key_length' => $licenseConfig['license_length'] ?? ''
];

$stmt = $pdo->query("SELECT * FROM analytics");
$analyticsConfigs = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $analytic) {
    $analytic['measurement_id'] = !empty($analytic['measurement_id']) ? decrypt($analytic['measurement_id'], $opensslKey) : '';
    $analyticsConfigs[$analytic['provider']] = $analytic;
}

$stmt = $pdo->query("SELECT recaptcha_provider, recaptcha_site_key, recaptcha_secret_key FROM settings LIMIT 1");
$recaptchaConfig = $stmt->fetch(PDO::FETCH_ASSOC);

$recaptchaConfigs = [
    'recaptcha_provider' => $recaptchaConfig['recaptcha_provider'] ?? 'disabled',
    'recaptcha_site_key' => !empty($recaptchaConfig['recaptcha_site_key']) ? decrypt($recaptchaConfig['recaptcha_site_key'], $opensslKey) : '',
    'recaptcha_secret_key' => !empty($recaptchaConfig['recaptcha_secret_key']) ? decrypt($recaptchaConfig['recaptcha_secret_key'], $opensslKey) : ''
];

$stmt = $pdo->query("SELECT login_detection_enabled FROM email_settings LIMIT 1");
$emailConfig = $stmt->fetch(PDO::FETCH_ASSOC);

$emailConfigs = [
    'login_detection_enabled' => $emailConfig['login_detection_enabled'] ?? ''
];

$stmt = $pdo->query("SELECT * FROM social_logins");
$loginConfigs = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $login) {
    $login['client_id'] = !empty($login['client_id']) ? decrypt($login['client_id'], $opensslKey) : '';
    $login['client_secret'] = !empty($login['client_secret']) ? decrypt($login['client_secret'], $opensslKey) : '';
    $loginConfigs[$login['provider']] = $login;
}

$stmt = $pdo->query("SELECT app_name, store_enabled, contact_forum_enabled, contact_forum_to_email FROM app_settings LIMIT 1");
$appConfig = $stmt->fetch(PDO::FETCH_ASSOC);

$appConfigs = [
    'app_name' => $appConfig['app_name'] ?? 'LicenseForge',
    'store_enabled' => $appConfig['store_enabled'] ?? 1,
    'contact_forum_enabled' => $appConfig['contact_forum_enabled'] ?? 0,
    'contact_forum_to_email' => $appConfig['contact_forum_to_email'] ?? 'info@example.com'
];
?>