<?php
http_response_code(200);
$success = false;
$error = '';
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];
try {
    require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT @@session.time_zone AS session_tz, @@system_time_zone AS system_tz");
    $tz = $stmt->fetch(PDO::FETCH_ASSOC);
    $mysqlTz = $tz['session_tz'] === 'SYSTEM' ? $tz['system_tz'] : $tz['session_tz'];
    try {
        $phpTz = new DateTimeZone($mysqlTz);
    } catch (Exception $e) {
        $phpTz = new DateTimeZone('UTC');
    }
    $today = new DateTime('today', $phpTz);
    $stmt = $pdo->query("SELECT user_id, product_id, created_at, nextpay_day, suspended, order_id FROM purchases WHERE order_id NOT LIKE 'MANUAL-%'");
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($purchases as $purchase) {
        $userId = $purchase['user_id'];
        $productId = $purchase['product_id'];
        $createdAt = $purchase['created_at'];
        $nextpayDay = new DateTime($purchase['nextpay_day'], $phpTz);
        $currentSuspended = $purchase['suspended'];
        $productTypeStmt = $pdo->prepare("SELECT type FROM products WHERE id = ?");
        $productTypeStmt->execute([$productId]);
        $productType = $productTypeStmt->fetchColumn();
        $checkUnpaidInvoiceStmt = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE user_id = ? AND product_id = ? AND status = 'unpaid'");
        $checkUnpaidInvoiceStmt->execute([$userId, $productId]);
        $hasUnpaidInvoice = $checkUnpaidInvoiceStmt->fetchColumn() > 0;
        if ($hasUnpaidInvoice) {
            if ($currentSuspended !== 'true') {
                $updatePurchaseSuspendStmt = $pdo->prepare("UPDATE purchases SET suspended = 'true' WHERE user_id = ? AND product_id = ?");
                $updatePurchaseSuspendStmt->execute([$userId, $productId]);
            }
            $updateLicenseStmt = $pdo->prepare("UPDATE licenses SET status = 'suspended' WHERE user_id = ? AND product_id = ? AND created_at = ?");
            $updateLicenseStmt->execute([$userId, $productId, $createdAt]);
            continue;
        } else {
            if ($currentSuspended !== 'false') {
                $updatePurchaseSuspendStmt = $pdo->prepare("UPDATE purchases SET suspended = 'false' WHERE user_id = ? AND product_id = ?");
                $updatePurchaseSuspendStmt->execute([$userId, $productId]);
            }
        }
        $checkInvoiceStmt = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE user_id = ? AND product_id = ? AND created_at = ? AND status = 'unpaid'");
        $checkInvoiceStmt->execute([$userId, $productId, $createdAt]);
        $unpaidInvoiceCount = $checkInvoiceStmt->fetchColumn();
        if ($unpaidInvoiceCount == 0 && strtolower($productType) === 'monthly') {
            if ($nextpayDay <= $today) {
                $insertInvoiceStmt = $pdo->prepare("INSERT INTO invoices (user_id, product_id, status, created_at) VALUES (?, ?, 'unpaid', ?)");
                $insertInvoiceStmt->execute([$userId, $productId, $nextpayDay->format('Y-m-d H:i:s')]);
                if ($currentSuspended !== 'true') {
                    $updatePurchaseSuspendStmt = $pdo->prepare("UPDATE purchases SET suspended = 'true' WHERE user_id = ? AND product_id = ?");
                    $updatePurchaseSuspendStmt->execute([$userId, $productId]);
                }
                $updateLicenseStmt = $pdo->prepare("UPDATE licenses SET status = 'suspended' WHERE user_id = ? AND product_id = ? AND created_at = ?");
                $updateLicenseStmt->execute([$userId, $productId, $createdAt]);
            }
            $checkAllPaidStmt = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE user_id = ? AND status != 'paid'");
            $checkAllPaidStmt->execute([$userId]);
            $notPaidCount = $checkAllPaidStmt->fetchColumn();
            if ($notPaidCount == 0) {
                $updateLicensesStmt = $pdo->prepare("UPDATE licenses SET status = 'valid' WHERE user_id = ? AND created_at = ? AND status = 'suspended'");
                $updateLicensesStmt->execute([$userId, $createdAt]);
                if ($currentSuspended !== 'false') {
                    $updatePurchaseSuspendStmt = $pdo->prepare("UPDATE purchases SET suspended = 'false' WHERE user_id = ? AND product_id = ?");
                    $updatePurchaseSuspendStmt->execute([$userId, $productId]);
                }
            }
        }
    }
    $count = $pdo->query("SELECT COUNT(*) FROM cron_status")->fetchColumn();

    if ($count > 0) {
        $pdo->exec("UPDATE cron_status SET last_run = NOW() WHERE id = 1");
    } else {
        $pdo->exec("INSERT INTO cron_status (id, last_run) VALUES (1, NOW())");
    }
    $success = true;
} catch (Exception $e) {
    http_response_code(500);
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($meta['favicon']) ?>" />
    <title><?= htmlspecialchars($meta['title']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta['description']) ?>" />
    <meta name="keywords" content="<?= htmlspecialchars($meta['keywords']) ?>" />
    
    <meta name="twitter:card" content="<?= htmlspecialchars($meta['twitter']['card']) ?>" />
    <meta name="twitter:site" content="<?= htmlspecialchars($meta['twitter']['site']) ?>" />
    <meta name="twitter:title" content="<?= htmlspecialchars($meta['twitter']['title']) ?>" />
    <meta name="twitter:description" content="<?= htmlspecialchars($meta['twitter']['description']) ?>" />
    <meta name="twitter:image" content="<?= htmlspecialchars($meta['twitter']['image']) ?>" />

    <meta property="og:type" content="<?= htmlspecialchars($meta['opengraph']['type']) ?>" />
    <meta property="og:url" content="<?= htmlspecialchars($meta['opengraph']['url']) ?>" />
    <meta property="og:title" content="<?= htmlspecialchars($meta['opengraph']['title']) ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($meta['opengraph']['description']) ?>" />
    <meta property="og:site_name" content="<?= htmlspecialchars($meta['opengraph']['site_name']) ?>" />
    <meta property="og:image" content="<?= htmlspecialchars($meta['opengraph']['image']['url']) ?>" />
    <meta property="og:image:type" content="<?= htmlspecialchars($meta['opengraph']['image']['type']) ?>" />
    <meta property="og:image:width" content="<?= htmlspecialchars($meta['opengraph']['image']['width']) ?>" />
    <meta property="og:image:height" content="<?= htmlspecialchars($meta['opengraph']['image']['height']) ?>" />

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex items-center justify-center p-6">
  <div class="max-w-md w-full bg-gray-800 rounded-md shadow-lg p-6 space-y-4">
    <?php if ($success): ?>
      <h1 class="text-2xl font-semibold text-green-400">Cronjob Executed</h1>
      <ul class="list-disc list-inside text-sm space-y-1 text-green-200">
        <li>Suspended unpaid licenses</li>
        <li>Generated new invoices for due monthly payments</li>
        <li>Reactivated valid licenses after payment</li>
      </ul>
      <p class="text-sm text-green-500">All operations finished successfully.</p>
    <?php else: ?>
      <h1 class="text-2xl font-semibold text-red-400">Execution Failed</h1>
      <p class="text-sm text-red-300 break-words"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
  </div>

</body>
</html>