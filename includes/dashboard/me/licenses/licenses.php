<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sessioncheck.php';
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

$opensslKey = $config['openssl_key'];
$method = 'aes-256-cbc';

$settingsStmt = $pdo->query("SELECT license_prefix, license_length FROM settings LIMIT 1");
$settings = $settingsStmt->fetch(PDO::FETCH_ASSOC);

$licensePrefix = $settings['license_prefix'] ?? null;
$licenseLength = isset($settings['license_length']) && is_numeric($settings['license_length']) ? (int)$settings['license_length'] : 16;

function encryptLicense($plaintext, $key) {
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($ivLength);
    $encrypted = openssl_encrypt($plaintext, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function decryptLicense($encrypted, $key) {
    $data = base64_decode($encrypted);
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $ivLength);
    $ciphertext = substr($data, $ivLength);
    return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, 0, $iv);
}

function generateLicenseKey($length, $prefix = null) {
    $key = strtoupper(bin2hex(random_bytes($length)));
    if ($prefix !== null && $prefix !== '') {
        return $prefix . '-' . $key;
    }
    return $key;
}

$userId = $_SESSION['user_id'] ?? null;

$productStmt = $pdo->query("SELECT id, name, domain_or_ip FROM products ORDER BY name ASC");
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

$licensesPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $licensesPerPage;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $newDomain_or_ip = $_POST['domain_or_ip'] ?? null;

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE licenses SET domain_or_ip = ? WHERE id = ?");
            $stmt->execute([$newDomain_or_ip, $id]);
            header("Location: /dashboard/me/licenses.php?page=$page");
            exit;
        }
    }

    if ($action === 'rotate') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmtCheck = $pdo->prepare("SELECT user_id FROM licenses WHERE id = ?");
            $stmtCheck->execute([$id]);
            $licenseOwner = $stmtCheck->fetchColumn();

            if ($licenseOwner == $userId) {
                $newLicenseKey = generateLicenseKey($licenseLength, $licensePrefix);
                $encryptedLicenseKey = encryptLicense($newLicenseKey, $opensslKey);
                $stmt = $pdo->prepare("UPDATE licenses SET license_key = ? WHERE id = ?");
                $stmt->execute([$encryptedLicenseKey, $id]);
            }
        }
        header("Location: /dashboard/me/licenses.php?page=$page");
        exit;
    }
}

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM licenses WHERE user_id = ?");
$totalStmt->execute([$userId]);
$totalLicenses = (int)$totalStmt->fetchColumn();
$totalPages = ceil($totalLicenses / $licensesPerPage);

$stmt = $pdo->prepare("SELECT l.id, l.domain_or_ip, l.license_key, l.status, l.created_at, p.name AS product_name, p.domain_or_ip AS product_domain_or_ip_force_check, l.product_id FROM licenses l LEFT JOIN products p ON l.product_id = p.id WHERE l.user_id = ? ORDER BY l.created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $userId, PDO::PARAM_INT);
$stmt->bindValue(2, $licensesPerPage, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$licenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($licenses as &$license) {
    $license['license_key'] = decryptLicense($license['license_key'], $opensslKey);
}
unset($license);
?>