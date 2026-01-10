<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

if (!empty($_SESSION['loggedIn'])) {
    header('Location: dashboard.php');
    exit;
}

$config = include 'config.php';
$opensslKey = $config['openssl_key'];
$meta = $config['meta'];

$settingsStmt = $pdo->prepare("SELECT * FROM settings LIMIT 1");
$settingsStmt->execute();
$settings = $settingsStmt->fetch(PDO::FETCH_ASSOC);

$recaptchaProvider = $settings['recaptcha_provider'] ?? 'disabled';
$recaptchaSiteKey = !empty($settings['recaptcha_site_key']) ? decrypt($settings['recaptcha_site_key'], $opensslKey) : '';
$recaptchaSecretKey = !empty($settings['recaptcha_secret_key']) ? decrypt($settings['recaptcha_secret_key'], $opensslKey) : '';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
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
    } elseif (!$name || !$username || !$password) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Username already taken.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, 'user')");
            $inserted = $stmt->execute([$name, $username, $email, $hashedPassword]);
            if ($inserted) {
                $success = 'Registration successful! You can now <a href="/login.php" class="text-indigo-500 hover:underline">log in</a>.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
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