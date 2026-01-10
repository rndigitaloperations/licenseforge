<?php
session_start();

$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/GoogleAuthenticator.php';
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
$meta = $config['meta'];
$smtp = $config['smtp'];
$appSettingStmt = $pdo->query("SELECT app_name FROM app_settings LIMIT 1");
$appSettings = $appSettingStmt->fetch(PDO::FETCH_ASSOC);
$appName = !empty($appSettings['app_name']) ? $appSettings['app_name'] : 'LicenseForge';
$settingsStmt = $pdo->prepare("SELECT * FROM settings LIMIT 1");
$settingsStmt->execute();
$settings = $settingsStmt->fetch(PDO::FETCH_ASSOC);
$recaptchaProvider = $settings['recaptcha_provider'] ?? 'disabled';
$recaptchaSiteKey = !empty($settings['recaptcha_site_key']) ? decrypt($settings['recaptcha_site_key'], $opensslKey) : '';
$recaptchaSecretKey = !empty($settings['recaptcha_secret_key']) ? decrypt($settings['recaptcha_secret_key'], $opensslKey) : '';
$error = '';
$showOnly2FAField = false;
$username = '';
$password = '';
$redirect = isset($_GET['redirect']) && !empty($_GET['redirect']) ? $_GET['redirect'] : 'dashboard.php';

if (filter_var($redirect, FILTER_VALIDATE_URL) || str_starts_with($redirect, '//')) {
    $redirect = 'dashboard.php';
}

if (isset($_SESSION['temp_user'])) {
    $showOnly2FAField = true;
    $username = $_SESSION['temp_user']['username'];
    $password = $_SESSION['temp_user']['password'];
}

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
    $enabled = $loginConfigs[$provider]['enabled'] ?? 0;
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $clientId = urlencode($loginConfigs[$provider]['client_id'] ?? '');
    $redirectUri = $protocol . $host . "/login/social?provider=$provider";
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
        'loginUrl' => $loginUrl,
        'logo' => $logo,
        'displayName' => $displayName
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $userCode = $_POST['2fa_code'] ?? '';
    $captchaValid = true;
    if ($recaptchaProvider !== 'disabled' && !$showOnly2FAField) {
        $captchaResponse = $_POST['g-recaptcha-response'] ?? $_POST['cf-turnstile-response'] ?? $_POST['h-captcha-response'] ?? $_POST['mtcaptcha-verifiedtoken'] ?? '';
        if (empty($captchaResponse)) {
            $captchaValid = false;
        } else {
            $verifyUrl = '';
            $postFields = [];
            if (in_array($recaptchaProvider, ['recaptcha_v2', 'recaptcha_v3'])) {
                $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
                $postFields = [
                    'secret' => $recaptchaSecretKey,
                    'response' => $captchaResponse,
                    'remoteip' => $_SERVER['REMOTE_ADDR']
                ];
            } elseif ($recaptchaProvider === 'cloudflare_turnstile') {
                $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
                $postFields = [
                    'secret' => $recaptchaSecretKey,
                    'response' => $captchaResponse,
                    'remoteip' => $_SERVER['REMOTE_ADDR']
                ];
            } elseif ($recaptchaProvider === 'hcaptcha') {
                $verifyUrl = 'https://hcaptcha.com/siteverify';
                $postFields = [
                    'secret' => $recaptchaSecretKey,
                    'response' => $captchaResponse,
                    'remoteip' => $_SERVER['REMOTE_ADDR']
                ];
            } elseif ($recaptchaProvider === 'mtcaptcha') {
                $verifyUrl = 'https://service.mtcaptcha.com/mtcv1/api/checktoken?privatekey=' . urlencode($recaptchaSecretKey) . '&token=' . urlencode($captchaResponse);
            }
            $ch = curl_init($verifyUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (!empty($postFields)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
            }
            $response = curl_exec($ch);
            curl_close($ch);
            $responseData = json_decode($response, true);
            $captchaValid = isset($responseData['success']) && $responseData['success'] === true;
        }
    }

    if (!$captchaValid) {
        $error = 'CAPTCHA validation failed.';
    } elseif (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            if ($user['two_factor_enabled']) {
                if (!empty($userCode)) {
                    $ga = new GoogleAuthenticator();
                    $secret = decrypt($user['two_factor_secret'], $opensslKey);
                    if ($ga->verifyCode($secret, $userCode, 2)) {
                        $_SESSION['loggedIn'] = true;
                        $_SESSION['user_id'] = $user['id'];
                        unset($_SESSION['temp_user']);
                        header('Location: ' . $redirect);
                        exit;
                    } else {
                        $error = 'Invalid 2FA code.';
                        $_SESSION['temp_user'] = ['username' => $username, 'password' => $password];
                        $showOnly2FAField = true;
                    }
                } else {
                    $_SESSION['temp_user'] = ['username' => $username, 'password' => $password];
                    $showOnly2FAField = true;
                }
            } else {
                $_SESSION['loggedIn'] = true;
                $_SESSION['user_id'] = $user['id'];
                $loginIP = $_SERVER['REMOTE_ADDR'];
                $userAgent = $_SERVER['HTTP_USER_AGENT'];
                $locationData = json_decode(file_get_contents("http://ip-api.com/json/"), true);
                $locationString = $locationData && $locationData['status'] === 'success' ? $locationData['city'] . ', ' . $locationData['country'] : 'Unknown Location';
                $resetUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/login?redirect=/dashboard/me/account-settings';
                $stmt = $pdo->prepare("SELECT login_detection_enabled FROM email_settings LIMIT 1");
                $stmt->execute();
                $emailSettings = $stmt->fetch(PDO::FETCH_ASSOC);
                header('Location: ' . $redirect);
                if ($emailSettings && $emailSettings['login_detection_enabled'] == 1) {
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
                        $mail->setFrom($smtp['mail'], $meta['title']);
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
                    exit;
                }
            }
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter username and password.';
    }
}

function decrypt($encrypted, $key) {
    $data = base64_decode($encrypted);
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $ivLength);
    $ciphertext = substr($data, $ivLength);
    return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, 0, $iv);
}
?>