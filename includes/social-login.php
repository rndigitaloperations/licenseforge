<?php
session_start();

$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/PHPMailer/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/PHPMailer/src/SMTP.php';
require $_SERVER['DOCUMENT_ROOT'] . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!empty($_SESSION['loggedIn'])) {
    header('Location: dashboard.php');
    exit;
}

$opensslKey = $config['openssl_key'];

$settingStmt = $pdo->query("SELECT app_name FROM app_settings LIMIT 1");
$settings = $settingStmt->fetch(PDO::FETCH_ASSOC);
$appName = !empty($settings['app_name']) ? $settings['app_name'] : 'LicenseForge';

$stmt = $pdo->query("SELECT * FROM social_logins");
$loginConfigs = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $login) {
    $login['client_id'] = !empty($login['client_id']) ? decrypt($login['client_id'], $opensslKey) : '';
    $login['client_secret'] = !empty($login['client_secret']) ? decrypt($login['client_secret'], $opensslKey) : '';
    $loginConfigs[$login['provider']] = $login;
}

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

session_start();

if (isset($_GET['code']) && isset($_GET['provider']) && in_array($_GET['provider'], ['discord', 'google'])) {
    $provider = $_GET['provider'];
    $code = $_GET['code'];
    $clientId = $loginConfigs[$provider]['client_id'];
    $clientSecret = $loginConfigs[$provider]['client_secret'];
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $redirectUri = $protocol . $host . '/login/social?provider=' . $provider;

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
                $stmt = $pdo->prepare("SELECT id, email, name FROM users WHERE discord_id = ?");
                $stmt->execute([$discordId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    header("Location: /login?error=no_account_linked");
                    exit;
                }

                $_SESSION['loggedIn'] = true;
                $_SESSION['user_id'] = $user['id'];

                $loginIP = $_SERVER['REMOTE_ADDR'];
                $userAgent = $_SERVER['HTTP_USER_AGENT'];
                $locationData = json_decode(file_get_contents("http://ip-api.com/json/"), true);
                $locationString = $locationData && $locationData['status'] === 'success' ? $locationData['city'] . ', ' . $locationData['country'] : 'Unknown Location';
                $resetUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/login?redirect=/dashboard/me/account-settings';
                $stmtEmailSettings = $pdo->prepare("SELECT login_detection_enabled FROM email_settings LIMIT 1");
                $stmtEmailSettings->execute();
                $emailSettings = $stmtEmailSettings->fetch(PDO::FETCH_ASSOC);
                if ($emailSettings && $emailSettings['login_detection_enabled'] == 1) {
                    $smtp = $config['smtp'];
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = $smtp['host'];
                        $mail->SMTPAuth = true;
                        $mail->Username = $smtp['username'];
                        $mail->Password = $smtp['password'];
                        $mail->SMTPSecure = $smtp['secure'];
                        $mail->Port = $smtp['port'];
                        $mail->CharSet = 'UTF-8';
                        $mail->setFrom($smtp['mail'], $appName);
                        $mail->addAddress($user['email'], $user['name']);
                        $mail->isHTML(true);
                        $mail->Subject = 'New Login Detected';
                        $mailBody = '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #f4f4f7;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      color: #333;
    }
    .email-container {
      max-width: 600px;
      margin: 30px auto;
      background-color: #ffffff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .email-header {
      background-color: #4F46E5;
      color: white;
      padding: 30px;
      text-align: center;
    }
    .email-header h1 {
      margin: 0;
      font-size: 24px;
    }
    .email-content {
      padding: 30px;
    }
    .email-content p {
      font-size: 16px;
      line-height: 1.6;
      margin: 0 0 16px;
    }
    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
    }
    .info-table th, .info-table td {
      text-align: left;
      padding: 12px 15px;
      border-bottom: 1px solid #eee;
      background-color: #f9f9fb;
    }
    .cta-button {
      display: inline-block;
      padding: 12px 24px;
      background-color: #4F46E5;
      color: white !important;
      text-decoration: none;
      border-radius: 6px;
      font-weight: 600;
      margin: 20px 0;
    }
    .email-footer {
      text-align: center;
      padding: 20px;
      font-size: 14px;
      color: #999;
      border-top: 1px solid #eee;
    }
    .email-footer p {
      margin: 5px 0;
    }
  </style>
</head>
<body>
  <div class="email-container">
    <div class="email-header">
      <h1>New Login Detected</h1>
    </div>
    <div class="email-content">
      <p>Dear ' . htmlspecialchars($user['name']) . ',</p>
      <p>We noticed a new login to your account. If this was you, you can safely ignore this email.</p>
      <table class="info-table">
        <tr>
          <th>IP</th>
          <td>' . htmlspecialchars($loginIP) . '</td>
        </tr>
        <tr>
          <th>Location</th>
          <td>' . htmlspecialchars($locationString) . '</td>
        </tr>
        <tr>
          <th>Browser</th>
          <td>' . htmlspecialchars($userAgent) . '</td>
        </tr>
      </table>
      <p>If you didn\'t log in, please reset your password immediately to protect your account.</p>
      <a href="' . $resetUrl . '" class="cta-button">Reset Password</a>
    </div>
    <div class="email-footer">
      <p>&copy; Copyright ' . date('Y') . ' ' . htmlspecialchars($appName) . '. All rights reserved.</p>
      <p>Need help? Contact our support team.</p>
    </div>
  </div>
</body>
</html>';
                        $mail->Body = $mailBody;
                        $mail->send();
                    } catch (Exception $e) {
                    }
                }
                header("Location: /dashboard");
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
                $stmt = $pdo->prepare("SELECT id, email, name FROM users WHERE google_id = ?");
                $stmt->execute([$googleId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    header("Location: /login?error=no_account_linked");
                    exit;
                }

                $_SESSION['loggedIn'] = true;
                $_SESSION['user_id'] = $user['id'];

                $loginIP = $_SERVER['REMOTE_ADDR'];
                $userAgent = $_SERVER['HTTP_USER_AGENT'];
                $locationData = json_decode(file_get_contents("http://ip-api.com/json/"), true);
                $locationString = $locationData && $locationData['status'] === 'success' ? $locationData['city'] . ', ' . $locationData['country'] : 'Unknown Location';
                $resetUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/login?redirect=/dashboard/me/account-settings';
                $stmtEmailSettings = $pdo->prepare("SELECT login_detection_enabled FROM email_settings LIMIT 1");
                $stmtEmailSettings->execute();
                $emailSettings = $stmtEmailSettings->fetch(PDO::FETCH_ASSOC);
                if ($emailSettings && $emailSettings['login_detection_enabled'] == 1) {
                    $smtp = $config['smtp'];
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = $smtp['host'];
                        $mail->SMTPAuth = true;
                        $mail->Username = $smtp['username'];
                        $mail->Password = $smtp['password'];
                        $mail->SMTPSecure = $smtp['secure'];
                        $mail->Port = $smtp['port'];
                        $mail->CharSet = 'UTF-8';
                        $mail->setFrom($smtp['mail'], $appName);
                        $mail->addAddress($user['email'], $user['name']);
                        $mail->isHTML(true);
                        $mail->Subject = 'New Login Detected';
                        $mailBody = '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #f4f4f7;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      color: #333;
    }
    .email-container {
      max-width: 600px;
      margin: 30px auto;
      background-color: #ffffff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .email-header {
      background-color: #4F46E5;
      color: white;
      padding: 30px;
      text-align: center;
    }
    .email-header h1 {
      margin: 0;
      font-size: 24px;
    }
    .email-content {
      padding: 30px;
    }
    .email-content p {
      font-size: 16px;
      line-height: 1.6;
      margin: 0 0 16px;
    }
    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
    }
    .info-table th, .info-table td {
      text-align: left;
      padding: 12px 15px;
      border-bottom: 1px solid #eee;
      background-color: #f9f9fb;
    }
    .cta-button {
      display: inline-block;
      padding: 12px 24px;
      background-color: #4F46E5;
      color: white !important;
      text-decoration: none;
      border-radius: 6px;
      font-weight: 600;
      margin: 20px 0;
    }
    .email-footer {
      text-align: center;
      padding: 20px;
      font-size: 14px;
      color: #999;
      border-top: 1px solid #eee;
    }
    .email-footer p {
      margin: 5px 0;
    }
  </style>
</head>
<body>
  <div class="email-container">
    <div class="email-header">
      <h1>New Login Detected</h1>
    </div>
    <div class="email-content">
      <p>Dear ' . htmlspecialchars($user['name']) . ',</p>
      <p>We noticed a new login to your account. If this was you, you can safely ignore this email.</p>
      <table class="info-table">
        <tr>
          <th>IP</th>
          <td>' . htmlspecialchars($loginIP) . '</td>
        </tr>
        <tr>
          <th>Location</th>
          <td>' . htmlspecialchars($locationString) . '</td>
        </tr>
        <tr>
          <th>Browser</th>
          <td>' . htmlspecialchars($userAgent) . '</td>
        </tr>
      </table>
      <p>If you didn\'t log in, please reset your password immediately to protect your account.</p>
      <a href="' . $resetUrl . '" class="cta-button">Reset Password</a>
    </div>
    <div class="email-footer">
      <p>&copy; Copyright ' . date('Y') . ' ' . htmlspecialchars($appName) . '. All rights reserved.</p>
      <p>Need help? Contact our support team.</p>
    </div>
  </div>
</body>
</html>';
                        $mail->Body = $mailBody;
                        $mail->send();
                    } catch (Exception $e) {
                    }
                }
                header("Location: /dashboard");
                exit;
            }
        }
    }
}