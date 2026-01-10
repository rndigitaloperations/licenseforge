<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sessioncheck.php';
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

$purchasesPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $purchasesPerPage;

$totalStmt = $pdo->query("SELECT COUNT(*) FROM purchases");
$totalPurchases = (int)$totalStmt->fetchColumn();
$totalPages = ceil($totalPurchases / $purchasesPerPage);

$stmt = $pdo->prepare("
    SELECT 
        purchases.id, 
        purchases.product_id, 
        products.name AS product_name,
        purchases.order_id,
        purchases.suspended,
        purchases.created_at,
        CASE 
            WHEN purchases.order_id LIKE 'MANUAL-%' THEN 'None'
            ELSE purchases.nextpay_day
        END nextpay_day
    FROM purchases
    JOIN products ON purchases.product_id = products.id
    ORDER BY purchases.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $purchasesPerPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>