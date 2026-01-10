<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];

$settingStmt = $pdo->query("SELECT app_name FROM app_settings LIMIT 1");
$settings = $settingStmt->fetch(PDO::FETCH_ASSOC);
$appName = !empty($settings['app_name']) ? $settings['app_name'] : 'LicenseForge';
?>

<footer style="z-index: -1;" class="bg-gray-800 fixed bottom-0 left-0 right-0 h-16 flex items-center justify-center px-6 shadow-md">
  <span class="text-sm text-white">&copy; Copyright <?= date('Y') ?> <?= htmlspecialchars($appName) ?>. All rights reserved.</span>
</footer>