<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/Parsedown.php';
$Parsedown = new Parsedown();
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];
$stmt = $pdo->prepare("SELECT * FROM categorys ORDER BY order_id ASC");
$stmt->execute();
$categorys = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>