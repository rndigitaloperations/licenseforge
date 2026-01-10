<?php

session_start();

include $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

if (empty($_SESSION['loggedIn'])) {
    header('Location: /login.php');
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: /login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: /login.php');
    exit;
}

$role = $user['role'];
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($role === 'admin') {
} elseif ($role === 'user') {
    if ($currentPath === '/dashboard' || strpos($currentPath, '/dashboard/') === 0) {
        if (strpos($currentPath . '/', '/dashboard/me/') !== 0) {
            header('Location: /dashboard/me');
            exit;
        }
    }
} else {
    header('HTTP/1.1 403 Forbidden');
    echo 'Access denied.';
    exit;
}

?>