<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sessioncheck.php';
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

$invoicesPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $invoicesPerPage;

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM invoices");
$totalStmt->execute();
$totalInvoices = (int)$totalStmt->fetchColumn();
$totalPages = ceil($totalInvoices / $invoicesPerPage);

$stmt = $pdo->prepare("
    SELECT 
        invoices.id, 
        invoices.product_id, 
        products.name AS product_name,
        invoices.status,
        invoices.created_at
    FROM invoices
    JOIN products ON invoices.product_id = products.id
    ORDER BY invoices.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $invoicesPerPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);