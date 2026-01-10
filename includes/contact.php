<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/GoogleAuthenticator.php';
require $_SERVER['DOCUMENT_ROOT'] . '/PHPMailer/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/PHPMailer/src/SMTP.php';
require $_SERVER['DOCUMENT_ROOT'] . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$opensslKey = $config['openssl_key'];
$meta = $config['meta'];
$smtp = $config['smtp'];

$settingsStmt = $pdo->prepare("SELECT * FROM settings LIMIT 1");
$settingsStmt->execute();
$settings = $settingsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

$appSettingsStmt = $pdo->prepare("SELECT * FROM app_settings LIMIT 1");
$appSettingsStmt->execute();
$appSettings = $appSettingsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

$recaptchaProvider = $settings['recaptcha_provider'] ?? 'disabled';
$recaptchaSiteKey = !empty($settings['recaptcha_site_key']) ? decrypt($settings['recaptcha_site_key'], $opensslKey) : '';
$recaptchaSecretKey = !empty($settings['recaptcha_secret_key']) ? decrypt($settings['recaptcha_secret_key'], $opensslKey) : '';

$error = '';
$success = '';

if (!isset($appSettings['contact_forum_enabled']) || (int)$appSettings['contact_forum_enabled'] === 0) {
    http_response_code(502);
    $_SERVER['REDIRECT_STATUS'] = '502';
    require $_SERVER['DOCUMENT_ROOT'] . '/errors.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '(No Subject)';
    $message = $_POST['message'] ?? '';
    $captchaValid = true;

    if ($recaptchaProvider !== 'disabled') {
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
    } elseif (!empty($name) && !empty($email) && !empty($message)) {
        $toEmail = $appSettings['contact_forum_to_email'];

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

            $mail->setFrom($email, $name);
            $mail->addAddress($toEmail);

            $mail->isHTML(true);
            $mail->Subject = htmlspecialchars($subject);

            $mailBody = '
<!DOCTYPE html>
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
  </style>
</head>
<body>
  <div class="email-container">
    <div class="email-header">
      <h1>' . htmlspecialchars($subject) . '</h1>
    </div>
    <div class="email-content">
      <table class="info-table">
        <tr>
          <th>Name</th>
          <td>' . htmlspecialchars($name) . '</td>
        </tr>
        <tr>
          <th>Email</th>
          <td>' . htmlspecialchars($email) . '</td>
        </tr>
        <tr>
          <th>Message</th>
          <td>' . nl2br(htmlspecialchars($message)) . '</td>
        </tr>
      </table>
    </div>
  </div>
</body>
</html>';

            $mail->Body = $mailBody;
            $mail->send();
            $success = 'Your message has been sent successfully!';
        } catch (Exception $e) {
            $error = 'There was an error sending your message.';
        }
    } else {
        $error = 'Please fill in all fields.';
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