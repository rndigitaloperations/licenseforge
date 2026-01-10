<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sessioncheck.php';
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$opensslKey = $config['openssl_key'];
$meta = $config['meta'];
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

function encryptLicense($plaintext, $key) {
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($ivLength);
    $encrypted = openssl_encrypt($plaintext, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

$purchasesPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $purchasesPerPage;

$errorMessage = '';

$usersStmt = $pdo->query("SELECT id, name FROM users ORDER BY name ASC");
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

$productsStmt = $pdo->query("SELECT id, name FROM products ORDER BY name ASC");
$products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        $product_id = (int)($_POST['product_id'] ?? 0);
        $suspended = isset($_POST['suspended']) ? 'true' : 'false';
        $nextpay_day = null;
        $order_id = 'MANUAL-' . strtoupper(uniqid());

        if ($user_id > 0 && $product_id > 0) {
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE user_id = ? AND product_id = ?");
            $checkStmt->execute([$user_id, $product_id]);
            $alreadyExists = $checkStmt->fetchColumn();

            if ($alreadyExists > 0) {
              $_SESSION['errorMessage'] = 'User already owns this product.';
            }

            if ($alreadyExists == 0) {
                $stmt = $pdo->prepare("INSERT INTO purchases (user_id, product_id, order_id, suspended, nextpay_day) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $product_id, $order_id, $suspended, $nextpay_day]);

                $stmt = $pdo->prepare("INSERT INTO invoices (user_id, product_id, status, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$user_id, $product_id, "paid"]);

                $licenseKey = strtoupper(bin2hex(random_bytes(16)));
                $encryptedLicenseKey = encryptLicense($licenseKey, $opensslKey);

                $stmt = $pdo->prepare("INSERT INTO licenses (user_id, license_key, product_id, status, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$user_id, $encryptedLicenseKey, $product_id, "valid"]);
            }

            header("Location: /dashboard/purchases.php?page=$page");
            exit;
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("SELECT user_id, product_id FROM purchases WHERE id = ?");
            $stmt->execute([$id]);
            $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($purchase) {
                $user_id = $purchase['user_id'];
                $product_id = $purchase['product_id'];

                $stmt = $pdo->prepare("DELETE FROM invoices WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$user_id, $product_id]);

                $stmt = $pdo->prepare("DELETE FROM purchases WHERE id = ?");
                $stmt->execute([$id]);
            }

            header("Location: /dashboard/purchases.php?page=$page");
            exit;
        }
    }

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $newUser_id = (int)($_POST['user_id'] ?? 0);
        $newProduct_id = (int)($_POST['product_id'] ?? 0);
        $newSuspended = isset($_POST['suspended']) ? 'true' : 'false';

        if ($id > 0 && $newUser_id > 0 && $newProduct_id > 0) {
            $stmt = $pdo->prepare("SELECT user_id, product_id FROM purchases WHERE id = ?");
            $stmt->execute([$id]);
            $old = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($old) {
                $oldUser = $old['user_id'];
                $oldProduct = $old['product_id'];
                $stmt = $pdo->prepare("UPDATE invoices SET user_id = ?, product_id = ? WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$newUser_id, $newProduct_id, $oldUser, $oldProduct]);
            }

            $stmt = $pdo->prepare("UPDATE purchases SET user_id = ?, product_id = ?, suspended = ? WHERE id = ?");
            $stmt->execute([$newUser_id, $newProduct_id, $newSuspended, $id]);
            header("Location: /dashboard/purchases.php?page=$page");
            exit;
        }
    }
}

$totalStmt = $pdo->query("SELECT COUNT(*) FROM purchases");
$totalPurchases = (int)$totalStmt->fetchColumn();
$totalPages = ceil($totalPurchases / $purchasesPerPage);

$stmt = $pdo->prepare("
    SELECT p.id, p.user_id, u.name AS user_name, p.product_id, pr.name AS product_name, p.order_id, p.suspended, p.created_at, CASE WHEN p.order_id LIKE 'MANUAL-%' THEN 'None' ELSE p.nextpay_day END nextpay_day
    FROM purchases p
    JOIN users u ON p.user_id = u.id
    JOIN products pr ON p.product_id = pr.id
    ORDER BY p.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $purchasesPerPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>