<?php
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

$opensslKey = $config['openssl_key'];

function decryptAnalytics($ciphertext_base64, $key) {
    $data = base64_decode($ciphertext_base64);
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $ivLength);
    $ciphertext = substr($data, $ivLength);
    return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, 0, $iv);
}

$stmt = $pdo->prepare("SELECT measurement_id, enabled FROM analytics WHERE provider = 'google'");
$stmt->execute();

if ($stmt && $row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $measurementIdEncrypted = $row['measurement_id'];
    $enabled = $row['enabled'];

    if ($enabled) {
        $measurementId = decryptAnalytics($measurementIdEncrypted, $opensslKey);
        echo <<<HTML
<script async src="https://www.googletagmanager.com/gtag/js?id=$measurementId"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '$measurementId');
</script>
HTML;
    }
}

$stmt = $pdo->prepare("SELECT measurement_id, enabled FROM analytics WHERE provider = 'cloudflare'");
$stmt->execute();

if ($stmt && $row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $measurementIdEncrypted = $row['measurement_id'];
    $enabled = $row['enabled'];

    if ($enabled) {
        $measurementId = decryptAnalytics($measurementIdEncrypted, $opensslKey);
        echo <<<HTML
<script defer src='https://static.cloudflareinsights.com/beacon.min.js' data-cf-beacon='{"token": "$measurementId"}'></script>
HTML;
    }
}
?>