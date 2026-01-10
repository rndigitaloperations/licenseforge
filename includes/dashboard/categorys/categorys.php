<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sessioncheck.php';
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/Parsedown.php';
$Parsedown = new Parsedown();

$categorysPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $categorysPerPage;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $image_url = $_POST['image_url'] ?? '';
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';

        if (!empty($name)) {
            $stmt = $pdo->prepare("INSERT INTO categorys (image_url, name, description) VALUES (?, ?, ?)");
            $stmt->execute([$image_url, $name, $description]);
            header("Location: /dashboard/categorys.php?page=$page");
            exit;
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM categorys WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: /dashboard/categorys.php?page=$page");
            exit;
        }
    }

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $newImage_url = $_POST['image_url'] ?? '';
        $newName = $_POST['name'] ?? '';
        $newDescription = $_POST['description'] ?? '';

        if ($id > 0 && !empty($newName)) {
            $stmt = $pdo->prepare("UPDATE categorys SET image_url = ?, name = ?, description = ?WHERE id = ?");
            $stmt->execute([$newImage_url, $newName, $newDescription, $id]);
            header("Location: /dashboard/categorys.php?page=$page");
            exit;
        }
    }

    if ($action === 'reorder') {
        $ordersJson = $_POST['orders'] ?? '[]';
        $orders = json_decode($ordersJson, true);
        foreach ($orders as $order) {
          $id = (int)$order['id'];
          $order_id = (int)$order['order_id'];
          $stmt = $pdo->prepare("UPDATE categorys SET order_id = ? WHERE id = ?");
          $stmt->execute([$order_id, $id]);
      }
        echo json_encode(['success' => true]);
        exit;
    }
}

$totalStmt = $pdo->query("SELECT COUNT(*) FROM categorys");
$totalCategorys = (int)$totalStmt->fetchColumn();
$totalPages = ceil($totalCategorys / $categorysPerPage);

$stmt = $pdo->prepare("SELECT id, image_url, name, description, created_at FROM categorys ORDER BY order_id ASC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $categorysPerPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$categorys = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>