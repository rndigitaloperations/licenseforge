<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/PHPMailer/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/PHPMailer/src/SMTP.php';
require $_SERVER['DOCUMENT_ROOT'] . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';
$showForm = true;

$config = include 'config.php';
$meta = $config['meta'];
$smtp = $config['smtp'];
$opensslKey = $config['openssl_key'];

$settingStmt = $pdo->query("SELECT app_name FROM app_settings LIMIT 1");
$settings = $settingStmt->fetch(PDO::FETCH_ASSOC);
$appName = !empty($settings['app_name']) ? $settings['app_name'] : 'LicenseForge';

$user = null;
if (!empty($_SESSION['user_id'])) {
    $userStmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['user_id']]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
}

function encrypt($plaintext, $key) {
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($ivLength);
    $encrypted = openssl_encrypt($plaintext, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

$resetMode = isset($_GET['code']) && !empty($_GET['code']);

if ($resetMode) {
    $code = $_GET['code'];
    $stmt = $pdo->prepare("SELECT * FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$code]);
    $tokenData = $stmt->fetch();

    if (!$tokenData) {
        $error = 'This reset link has expired or is invalid.';
        $showForm = false;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        if (!$password || !$confirmPassword) {
            $error = 'Please fill in both password fields.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $tokenData['user_id']]);
            $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
            $stmt->execute([$code]);
            $success = 'Password has been reset. <a href="/login.php" class="text-indigo-500 hover:underline">Log in</a>.';
            $showForm = false;
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!$email) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 300);
            $stmt = $pdo->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $token, $expiresAt]);

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = $smtp['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $smtp['username'];
                $mail->Password = $smtp['password'];
                $mail->SMTPSecure = $smtp['secure'];
                $mail->Port = $smtp['port'];;
                $mail->CharSet = 'UTF-8';

                $mail->setFrom($smtp['mail'], $meta['title']);
                $mail->addAddress($email, $user['name']);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';

                $resetUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/forgot-password.php?code=' . $token;

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
      <h1>Password Reset Request</h1>
    </div>
    <div class="email-content">
      <p>Dear ' . htmlspecialchars($user['name']) . ',</p>
      <p>We have received a request to reset your password. Click the button below to reset it. This link will expire in 5 minutes for your security.</p>
      <p>If you did not request this password reset, you can safely ignore this email.</p>
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
                $success = 'A password reset link has been sent to your email.';
            } catch (Exception $e) {
                $error = 'Mailer Error: ' . $mail->ErrorInfo;
            }
        } else {
            $error = 'Email address not found.';
        }
    }
}
?>