<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/Parsedown.php';
$Parsedown = new Parsedown();
$config = include 'config.php';
$meta = $config['meta'];
$catStmt = $pdo->query("SELECT id, name FROM categorys ORDER BY order_id ASC");
$categorys = $catStmt->fetchAll(PDO::FETCH_ASSOC);
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? ORDER BY order_id ASC");
$stmt->execute([$id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currencyIcons = [
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£',
    'AUD' => '$',
    'CAD' => '$',
    'JPY' => '¥',
    'CHF' => 'CHF',
    'NZD' => '$',
    'SEK' => 'kr',
    'NOK' => 'kr',
    'DKK' => 'kr',
    'ZAR' => 'R',
    'SGD' => '$',
    'HKD' => '$',
    'MXN' => 'Mex$',
    'BRL' => 'R$',
];

$currency = $_SESSION['currency'] ?? 'USD';

$currencyIcon = $currencyIcons[$currency] ?? '';
?>