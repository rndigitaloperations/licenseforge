<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sessioncheck.php';
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

$stmt = $pdo->query("SELECT COUNT(*) as total, SUM(status = 'valid') as valid, SUM(status = 'invalid') as invalid, SUM(status = 'suspended') as suspended FROM licenses");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>