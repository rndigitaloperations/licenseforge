<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sessioncheck.php';
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

$userId = $_SESSION['user_id'] ?? null;

$stmt = $pdo->prepare("SELECT 
    COUNT(*) as total, 
    SUM(status = 'valid') as valid, 
    SUM(status = 'invalid') as invalid, 
    SUM(status = 'suspended') as suspended 
    FROM licenses WHERE user_id = ?");
$stmt->execute([$userId]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>