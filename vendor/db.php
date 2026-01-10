<?php
$config = require $_SERVER['DOCUMENT_ROOT'] . '/config.php';

try {
    $mysqlConfig = $config['mysql'];
    $dsn = "mysql:host={$mysqlConfig['host']};dbname={$mysqlConfig['dbname']};charset={$mysqlConfig['charset']}";
    $pdo = new PDO($dsn, $mysqlConfig['username'], $mysqlConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}