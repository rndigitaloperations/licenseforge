<?php
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sessioncheck.php';
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/GoogleAuthenticator.php';

$opensslKey = $config['openssl_key'];

$settingStmt = $pdo->query("SELECT app_name FROM app_settings LIMIT 1");
$settings = $settingStmt->fetch(PDO::FETCH_ASSOC);
$appName = !empty($settings['app_name']) ? $settings['app_name'] : 'LicenseForge';

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

$stmt = $pdo->query("SELECT * FROM social_logins");
$loginConfigs = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $login) {
    $login['client_id'] = !empty($login['client_id']) ? decrypt($login['client_id'], $opensslKey) : '';
    $login['client_secret'] = !empty($login['client_secret']) ? decrypt($login['client_secret'], $opensslKey) : '';
    $loginConfigs[$login['provider']] = $login;
}

function httpPost($url, $data, $headers = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function httpGet($url, $headers = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

if (isset($_GET['code']) && isset($_GET['provider']) && in_array($_GET['provider'], ['discord', 'google'])) {
    $provider = $_GET['provider'];
    $code = $_GET['code'];
    $clientId = $loginConfigs[$provider]['client_id'];
    $clientSecret = $loginConfigs[$provider]['client_secret'];

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $redirectUri = $protocol . $host . '/dashboard/me/connect?provider=' . $provider;

    if ($provider === 'discord') {
        $tokenResponse = httpPost('https://discord.com/api/oauth2/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri
        ], ['Content-Type: application/x-www-form-urlencoded']);

        $accessToken = $tokenResponse['access_token'] ?? null;
        if ($accessToken) {
            $userResponse = httpGet('https://discord.com/api/users/@me', [
                "Authorization: Bearer $accessToken"
            ]);

            if (isset($userResponse['id'])) {
                $discordId = $userResponse['id'];
                $stmt = $pdo->prepare("SELECT id FROM users WHERE discord_id = ?");
                $stmt->execute([$discordId]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($existing && $existing['id'] != $_SESSION['user_id']) {
                    header("Location: /dashboard/me/account-settings?tab=connections&error=discord_already_linked");
                    exit;
                }

                $stmt = $pdo->prepare("UPDATE users SET discord_id = ? WHERE id = ?");
                $stmt->execute([$discordId, $_SESSION['user_id']]);
                header("Location: /dashboard/me/account-settings?tab=connections");
                exit;
            }
        }
    }

    if ($provider === 'google') {
        $tokenResponse = httpPost('https://oauth2.googleapis.com/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri
        ], ['Content-Type: application/x-www-form-urlencoded']);

        $accessToken = $tokenResponse['access_token'] ?? null;
        if ($accessToken) {
            $userResponse = httpGet("https://www.googleapis.com/oauth2/v2/userinfo?access_token=$accessToken");

            if (isset($userResponse['id'])) {
                $googleId = $userResponse['id'];
                $stmt = $pdo->prepare("SELECT id FROM users WHERE google_id = ?");
                $stmt->execute([$googleId]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($existing && $existing['id'] != $_SESSION['user_id']) {
                    header("Location: /dashboard/me/account-settings?tab=connections&error=google_already_linked");
                    exit;
                }

                $stmt = $pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?");
                $stmt->execute([$googleId, $_SESSION['user_id']]);
                header("Location: /dashboard/me/account-settings?tab=connections");
                exit;
            }
        }
    }
}