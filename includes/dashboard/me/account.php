<?php
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sessioncheck.php';
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/GoogleAuthenticator.php';

$opensslKey = $config['openssl_key'];
$meta = $config['meta'];

$settingStmt = $pdo->query("SELECT app_name FROM app_settings LIMIT 1");
$settings = $settingStmt->fetch(PDO::FETCH_ASSOC);
$appName = !empty($settings['app_name']) ? $settings['app_name'] : 'LicenseForge';
$activeTab = $_GET['tab'] ?? 'general';

$method = 'aes-256-cbc';

function encrypt($plaintext, $key) {
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($ivLength);
    $encrypted = openssl_encrypt($plaintext, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function decrypt($encrypted, $key) {
    $data = base64_decode($encrypted);
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $ivLength);
    $ciphertext = substr($data, $ivLength);
    return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, 0, $iv);
}

$userId = $_SESSION['user_id'];
$error = null;

$stmt = $pdo->query("SELECT * FROM social_logins");
$loginConfigs = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $login) {
    $login['client_id'] = !empty($login['client_id']) ? decrypt($login['client_id'], $opensslKey) : '';
    $login['client_secret'] = !empty($login['client_secret']) ? decrypt($login['client_secret'], $opensslKey) : '';
    $loginConfigs[$login['provider']] = $login;
}

$providers = ['discord', 'google'];
$socialConnections = [];
foreach ($providers as $provider) {
    $connected = false;
    $enabled = $loginConfigs[$provider]['enabled'] ?? 0;
    $userColumn = $provider . '_id';
    $stmt = $pdo->prepare("SELECT $userColumn FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $connected = !empty($stmt->fetchColumn());

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $clientId = urlencode($loginConfigs[$provider]['client_id'] ?? '');
    $redirectUri = $protocol . $host . "/dashboard/me/connect?provider=$provider";

    if ($provider === 'discord') {
        $scope = urlencode('identify email');
        $loginUrl = "https://discord.com/api/oauth2/authorize?client_id=$clientId&redirect_uri=$redirectUri&response_type=code&scope=$scope";
        $logo = 'https://cdn.ricardoneud.com/products/LicenseForge/login/discord.png';
        $displayName = 'Discord';
    } elseif ($provider === 'google') {
        $scope = urlencode('openid email profile');
        $loginUrl = "https://accounts.google.com/o/oauth2/v2/auth?client_id=$clientId&redirect_uri=$redirectUri&response_type=code&scope=$scope";
        $logo = 'https://cdn.ricardoneud.com/products/LicenseForge/login/google.png';
        $displayName = 'Google';
    }

    $socialConnections[$provider] = [
        'enabled' => $enabled,
        'connected' => $connected,
        'loginUrl' => $loginUrl,
        'logo' => $logo,
        'displayName' => $displayName
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_2fa'])) {
        $stmt = $pdo->prepare("SELECT two_factor_secret FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $encryptedSecret = $stmt->fetchColumn();
        $secret = $encryptedSecret ? decrypt($encryptedSecret, $opensslKey) : null;

        if ($_POST['toggle_2fa'] === 'enable') {
            if (!$secret) {
                $secret = $ga->createSecret();
                $encryptedSecret = encrypt($secret, $opensslKey);
                $stmt = $pdo->prepare("UPDATE users SET two_factor_secret = ? WHERE id = ?");
                $stmt->execute([$encryptedSecret, $userId]);
                header("Location: /dashboard/me/account-settings.php?tab=security&setup_2fa=1");
                exit;
            }
            $userCode = trim($_POST['2fa_code'] ?? '');
            if ($ga->verifyCode($secret, $userCode, 2)) {
                $stmt = $pdo->prepare("UPDATE users SET two_factor_enabled = 1 WHERE id = ?");
                $stmt->execute([$userId]);
                header("Location: /dashboard/me/account-settings.php?tab=security&enabled=1");
                exit;
            } else {
                $error = 'Invalid 2FA code';
            }
        } elseif ($_POST['toggle_2fa'] === 'disable') {
            $stmt = $pdo->prepare("UPDATE users SET two_factor_enabled = 0, two_factor_secret = NULL WHERE id = ?");
            $stmt->execute([$userId]);
            header("Location: /dashboard/me/account-settings.php?tab=security&disabled=1");
            exit;
        }
    } elseif (isset($_POST['disconnect_provider'])) {
        $provider = $_POST['provider'];
        $userColumn = $provider . '_id';
        $stmt = $pdo->prepare("UPDATE users SET $userColumn = NULL WHERE id = ?");
        $stmt->execute([$userId]);
        header("Location: /dashboard/me/account-settings.php?tab=connections");
        exit;
    } else {
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($name) || empty($username)) {
            die('Name and username are required.');
        }

        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $username, $email, $hashedPassword, $userId]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $username, $email, $userId]);
        }

        header("Location: /dashboard/me/account-settings.php?updated=1");
        exit;
    }
}

$stmt = $pdo->prepare("SELECT name, username, email, two_factor_enabled, two_factor_secret FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && $user['two_factor_secret']) {
    $user['two_factor_secret'] = decrypt($user['two_factor_secret'], $opensslKey);
}

if (!$user) {
    die('User not found.');
}

if (strpos($user['email'], '@') === false && strpos($user['username'], '@') !== false) {
    $tmp = $user['email'];
    $user['email'] = $user['username'];
    $user['username'] = $tmp;
}
?>