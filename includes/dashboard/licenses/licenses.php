<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sessioncheck.php';
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

$opensslKey = $config['openssl_key'];

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

$statuses = ['valid', 'invalid', 'suspended'];

$productStmt = $pdo->query("SELECT id, name FROM products ORDER BY name ASC");
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

$userStmt = $pdo->query("SELECT id, name FROM users ORDER BY name ASC");
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

$licensesPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $licensesPerPage;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $owner = $_POST['owner'] ?? '';
        $domain_or_ip = $_POST['domain_or_ip'] ?? null;
        $status = in_array($_POST['status'] ?? '', $statuses) ? $_POST['status'] : 'valid';
        $productId = !empty($_POST['product_id']) && $_POST['product_id'] !== 'none' ? (int)$_POST['product_id'] : null;
        $licenseKey = generateLicenseKey($licenseLength, $licensePrefix);
        $encryptedLicenseKey = encryptLicense($licenseKey, $opensslKey);

        if (!empty($owner) && is_numeric($owner)) {
            $stmt = $pdo->prepare("INSERT INTO licenses (user_id, domain_or_ip, license_key, status, product_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$owner, $domain_or_ip, $encryptedLicenseKey, $status, $productId]);
            header("Location: /dashboard/licenses.php?page=$page");
            exit;
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM licenses WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: /dashboard/licenses.php?page=$page");
            exit;
        }
    }

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $newOwner = $_POST['owner'] ?? '';
        $newDomain_or_ip = $_POST['domain_or_ip'] ?? null;
        $newStatus = $_POST['status'] ?? '';
        $productId = !empty($_POST['product_id']) && $_POST['product_id'] !== 'none' ? (int)$_POST['product_id'] : null;

        if ($id > 0 && !empty($newOwner) && is_numeric($newOwner) && in_array($newStatus, $statuses)) {
            $stmt = $pdo->prepare("UPDATE licenses SET user_id = ?, domain_or_ip = ?, status = ?, product_id = ? WHERE id = ?");
            $stmt->execute([$newOwner, $newDomain_or_ip, $newStatus, $productId, $id]);
            header("Location: /dashboard/licenses.php?page=$page");
            exit;
        }
    }

    if ($action === 'rotate') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $newLicenseKey = generateLicenseKey($licenseLength, $licensePrefix);
            $encryptedLicenseKey = encryptLicense($newLicenseKey, $opensslKey);
            $stmt = $pdo->prepare("UPDATE licenses SET license_key = ? WHERE id = ?");
            $stmt->execute([$encryptedLicenseKey, $id]);
            header("Location: /dashboard/licenses.php?page=$page");
            exit;
        }
    }
}

$totalStmt = $pdo->query("SELECT COUNT(*) FROM licenses");
$totalLicenses = (int)$totalStmt->fetchColumn();
$totalPages = ceil($totalLicenses / $licensesPerPage);

$stmt = $pdo->prepare("SELECT l.id, l.user_id, l.domain_or_ip, l.license_key, l.status, l.created_at, p.name AS product_name, l.product_id, u.name AS user_name FROM licenses l LEFT JOIN products p ON l.product_id = p.id LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $licensesPerPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$licenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($licenses as &$license) {
    $license['license_key'] = decryptLicense($license['license_key'], $opensslKey);
}
unset($license);
?>