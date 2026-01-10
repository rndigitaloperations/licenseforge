<?php

header('Content-Type: application/json');

$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

$opensslKey = $config['openssl_key'];

function decryptLicenses($encrypted, $key) {
    $data = base64_decode($encrypted);
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $ivLength);
    $ciphertext = substr($data, $ivLength);
    return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, 0, $iv);
}

function isIpAddress($value) {
    return filter_var($value, FILTER_VALIDATE_IP) !== false;
}

$inputKey = $_GET['license_key'] ?? null;

if (!$inputKey) {
    http_response_code(400);
    echo json_encode(['error' => 'No license key provided']);
    exit;
}

$stmt = $pdo->query("SELECT * FROM licenses");
$found = false;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $decrypted = decryptLicenses($row['license_key'], $opensslKey);
    if ($decrypted === $inputKey) {
        $domainOrIpRaw = $row['domain_or_ip'] ?? null;
        $productId = $row['product_id'] ?? null;
        $status = $row['status'];
        $domainOrIpArray = [];

        if ($domainOrIpRaw !== null) {
            if (isIpAddress($domainOrIpRaw)) {
                $domainOrIpArray[] = $domainOrIpRaw;
            } else {
                $domain = strtolower($domainOrIpRaw);
                $domainOrIpArray[] = $domain;
                if (strpos($domain, 'www.') !== 0) {
                    $withWww = 'www.' . $domain;
                    $domainOrIpArray[] = $withWww;
                }
            }
        } else {
            $domainOrIpArray[] = 'None';
        }

        if ($productId !== null) {
            $productStmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
            $productStmt->execute([$productId]);
            $product = $productStmt->fetchColumn();
            $product = $product ?: 'None';
        } else {
            $product = 'None';
        }

        echo json_encode([
            'domain_or_ip' => $domainOrIpArray,
            'status' => $status,
            'product' => $product
        ]);
        $found = true;
        break;
    }
}

if (!$found) {
    http_response_code(404);
    echo json_encode(['error' => 'License key not found']);
}
?>