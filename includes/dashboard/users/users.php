<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sessioncheck.php';
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

$roles = ['admin', 'user'];

$usersPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $usersPerPage;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = in_array($_POST['role'] ?? '', $roles) ? $_POST['role'] : 'user';

        if (!empty($name) && !empty($username) && !empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $username, $email, $hashedPassword, $role]);
            header("Location: /dashboard/users.php?page=$page");
            exit;
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: /dashboard/users.php?page=$page");
            exit;
        }
    }

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $newName = trim($_POST['name'] ?? '');
        $newUsername = trim($_POST['username'] ?? '');
        $newEmail = trim($_POST['email'] ?? '');
        $newPassword = $_POST['password'] ?? '';
        $newRole = in_array($_POST['role'] ?? '', $roles) ? $_POST['role'] : 'user';

        if ($id > 0 && !empty($newName) && !empty($newUsername)) {
            if (!empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, email = ?, password = ?, role = ? WHERE id = ?");
                $stmt->execute([$newName, $newUsername, $newEmail, $hashedPassword, $newRole, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, email = ?, role = ? WHERE id = ?");
                $stmt->execute([$newName, $newUsername, $newEmail, $newRole, $id]);
            }

            header("Location: /dashboard/users.php?page=$page");
            exit;
        }
    }
}

$totalStmt = $pdo->query("SELECT COUNT(*) FROM users");
$totalUsers = (int)$totalStmt->fetchColumn();
$totalPages = ceil($totalUsers / $usersPerPage);

$stmt = $pdo->prepare("SELECT id, name, username, email, role, protected, created_at FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $usersPerPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>