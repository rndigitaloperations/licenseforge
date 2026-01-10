<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sessioncheck.php';
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

$statuses = ['available', 'unavailable', 'discontinued'];
$domainIpLockStatuses = ['force', 'optional'];
$productTypes = ['one-time', 'monthly'];

$categoryStmt = $pdo->query("SELECT id, name FROM categorys ORDER BY name ASC");
$categorys = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

$productsPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $productsPerPage;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $image_url = $_POST['image_url'] ?? '';
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $status = in_array($_POST['status'] ?? '', $statuses) ? $_POST['status'] : 'available';
        $domain_or_ip = in_array($_POST['domain-or-ip'] ?? '', $domainIpLockStatuses) ? $_POST['domain-or-ip'] : 'force';
        $price = floatval(str_replace(',', '.', $_POST['price'] ?? '0'));
        $type = in_array($_POST['type'] ?? '', $productTypes) ? $_POST['type'] : 'one-time';
        $category_id = (int)($_POST['category_id'] ?? 0);

        if (!empty($name) && $price >= 0) {
            $stmt = $pdo->prepare("INSERT INTO products (image_url, name, description, price, status, domain_or_ip, type, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$image_url, $name, $description, $price, $status, $domain_or_ip, $type, $category_id]);
            header("Location: /dashboard/products.php?page=$page");
            exit;
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: /dashboard/products.php?page=$page");
            exit;
        }
    }

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $newImage_url = $_POST['image_url'] ?? '';
        $newName = $_POST['name'] ?? '';
        $newDescription = $_POST['description'] ?? '';
        $newStatus = in_array($_POST['status'] ?? '', $statuses) ? $_POST['status'] : 'available';
        $newDomain_or_ip = in_array($_POST['domain-or-ip'] ?? '', $domainIpLockStatuses) ? $_POST['domain-or-ip'] : 'force';
        $newPrice = floatval(str_replace(',', '.', $_POST['price'] ?? '0'));
        $newType = in_array($_POST['type'] ?? '', $productTypes) ? $_POST['type'] : 'one-time';
        $newCategory_id = (int)($_POST['category_id'] ?? 0);

        if ($id > 0 && !empty($newName) && $newPrice >= 0) {
            $stmt = $pdo->prepare("UPDATE products SET image_url = ?, name = ?, description = ?, price = ?, status = ?, domain_or_ip = ?, type = ?, category_id = ? WHERE id = ?");
            $stmt->execute([$newImage_url, $newName, $newDescription, $newPrice, $newStatus, $newDomain_or_ip, $newType, $newCategory_id, $id]);
            header("Location: /dashboard/products.php?page=$page");
            exit;
        }
    }

    if ($action === 'reorder') {
        $ordersJson = $_POST['orders'] ?? '[]';
        $orders = json_decode($ordersJson, true);
        foreach ($orders as $order) {
          $id = (int)$order['id'];
          $order_id = (int)$order['order_id'];
          $stmt = $pdo->prepare("UPDATE products SET order_id = ? WHERE id = ?");
          $stmt->execute([$order_id, $id]);
      }
        echo json_encode(['success' => true]);
        exit;
    }
}

$totalStmt = $pdo->query("SELECT COUNT(*) FROM products");
$totalProducts = (int)$totalStmt->fetchColumn();
$totalPages = ceil($totalProducts / $productsPerPage);

$stmt = $pdo->prepare("SELECT p.id, p.image_url, p.name, p.description, p.price, p.status, p.domain_or_ip, p.type, p.created_at, p.category_id, c.name AS category_name FROM products p LEFT JOIN categorys c ON p.category_id = c.id ORDER BY p.order_id ASC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $productsPerPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>