<?php
$configFile = __DIR__ . '/config.php';
if (file_exists($configFile)) {
    $config = include $configFile;
    if (!empty($config['installed'])) {
        include('errors.php');
        exit;
    }
}

$errors = [];
$success = false;
$installing = false;

$metaDefaults = [
    'title' => 'LicenseForge',
    'description' => 'LicenseForge helps you generate and manage software licenses effortlessly.',
    'keywords' => 'LicenseForge, license management, software licenses, key generator',
    'favicon' => 'https://cdn.example.com/LicenseForge.png',
    'twitter' => [
        'card' => 'summary',
        'site' => '@LicenseForge',
        'title' => 'LicenseForge - License management made easy',
        'description' => 'Quickly generate and manage software licenses with LicenseForge.',
        'image' => 'https://cdn.example.com/LicenseForge.png',
    ],
    'opengraph' => [
        'type' => 'website',
        'url' => 'https://example.com',
        'title' => 'LicenseForge - Software License Management',
        'description' => 'Powerful and simple license management for software developers.',
        'site_name' => 'LicenseForge',
        'image' => [
            'url' => 'https://cdn.example.com/LicenseForge.png',
            'type' => 'image/png',
            'width' => 100,
            'height' => 100,
        ],
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $installing = true;
    $mysql = [
        'host' => trim($_POST['mysql_host'] ?? ''),
        'dbname' => trim($_POST['mysql_dbname'] ?? ''),
        'dbport' => trim($_POST['mysql_dbport'] ?? ''),
        'username' => trim($_POST['mysql_username'] ?? ''),
        'password' => trim($_POST['mysql_password'] ?? ''),
        'charset' => trim($_POST['mysql_charset'] ?? 'utf8mb4'),
    ];

    $smtp = [
        'host' => trim($_POST['smtp_host'] ?? ''),
        'mail' => trim($_POST['smtp_mail'] ?? ''),
        'username' => trim($_POST['smtp_username'] ?? ''),
        'password' => trim($_POST['smtp_password'] ?? ''),
        'secure' => trim($_POST['smtp_secure'] ?? ''),
        'port' => (int)($_POST['smtp_port'] ?? 0),
    ];

    $openssl_key = trim($_POST['openssl_key'] ?? '');

    $meta = [
        'title' => trim($_POST['meta_title'] ?? $metaDefaults['title']),
        'description' => trim($_POST['meta_description'] ?? $metaDefaults['description']),
        'keywords' => trim($_POST['meta_keywords'] ?? $metaDefaults['keywords']),
        'favicon' => trim($_POST['meta_favicon'] ?? $metaDefaults['favicon']),
        'twitter' => [
            'card' => trim($_POST['twitter_card'] ?? $metaDefaults['twitter']['card']),
            'site' => trim($_POST['twitter_site'] ?? $metaDefaults['twitter']['site']),
            'title' => trim($_POST['twitter_title'] ?? $metaDefaults['twitter']['title']),
            'description' => trim($_POST['twitter_description'] ?? $metaDefaults['twitter']['description']),
            'image' => trim($_POST['twitter_image'] ?? $metaDefaults['twitter']['image']),
        ],
        'opengraph' => [
            'type' => trim($_POST['og_type'] ?? $metaDefaults['opengraph']['type']),
            'url' => trim($_POST['og_url'] ?? $metaDefaults['opengraph']['url']),
            'title' => trim($_POST['og_title'] ?? $metaDefaults['opengraph']['title']),
            'description' => trim($_POST['og_description'] ?? $metaDefaults['opengraph']['description']),
            'site_name' => trim($_POST['og_site_name'] ?? $metaDefaults['opengraph']['site_name']),
            'image' => [
                'url' => trim($_POST['og_image_url'] ?? $metaDefaults['opengraph']['image']['url']),
                'type' => trim($_POST['og_image_type'] ?? $metaDefaults['opengraph']['image']['type']),
                'width' => (int)($_POST['og_image_width'] ?? $metaDefaults['opengraph']['image']['width']),
                'height' => (int)($_POST['og_image_height'] ?? $metaDefaults['opengraph']['image']['height']),
            ],
        ],
    ];

    foreach ($mysql as $k => $v) {
        if ($v === '') {
            $errors[] = "MySQL $k is required";
        }
    }

    if (empty($openssl_key)) {
        $errors[] = 'OpenSSL Key is required';
    }

    if (empty($meta['title'])) {
        $errors[] = 'Meta title is required';
    }

    if (empty($meta['description'])) {
        $errors[] = 'Meta description is required';
    }

    if (!$errors) {
        try {
            $dsn = "mysql:host={$mysql['host']};port={$mysql['dbport']};charset={$mysql['charset']}";
            $pdo = new PDO($dsn, $mysql['username'], $mysql['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$mysql['dbname']}` CHARACTER SET {$mysql['charset']} COLLATE {$mysql['charset']}_general_ci");
            $pdo->exec("USE `{$mysql['dbname']}`");

            $pdo->exec("
                CREATE TABLE cron_status (
                    id INT PRIMARY KEY,
                    last_run DATETIME
                );

                CREATE TABLE `analytics` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `provider` varchar(255) NOT NULL,
                    `enabled` tinyint(1) NOT NULL,
                    `measurement_id` varchar(255) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `unique_provider` (`provider`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

                CREATE TABLE `app_settings` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `app_name` varchar(255) NOT NULL DEFAULT 'LicenseForge',
                    `store_enabled` tinyint(1) NOT NULL DEFAULT 1,
                    `contact_forum_enabled` tinyint(1) NOT NULL DEFAULT 1,
                    `contact_forum_to_email` varchar(255) DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

                CREATE TABLE `categorys` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `order_id` int(10) DEFAULT NULL,
                    `image_url` varchar(255) NOT NULL,
                    `name` varchar(100) NOT NULL,
                    `description` text DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

                CREATE TABLE `email_settings` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `login_detection_enabled` tinyint(1) DEFAULT 0,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

                CREATE TABLE `invoices` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `user_id` int(10) UNSIGNED NOT NULL,
                    `product_id` int(10) UNSIGNED NOT NULL,
                    `status` enum('paid','unpaid') NOT NULL DEFAULT 'unpaid',
                    `created_at` timestamp NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

                CREATE TABLE `licenses` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `user_id` varchar(255) DEFAULT NULL,
                    `domain_or_ip` varchar(255) DEFAULT NULL,
                    `license_key` text NOT NULL,
                    `product_id` int(11) DEFAULT NULL,
                    `status` enum('valid','invalid','suspended') NOT NULL DEFAULT 'valid',
                    `created_at` timestamp NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `license_key` (`license_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

                CREATE TABLE `password_reset_tokens` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `user_id` int(10) UNSIGNED NOT NULL,
                    `token` varchar(255) NOT NULL,
                    `expires_at` datetime NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `token_unique` (`token`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `payments` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `provider` varchar(255) NOT NULL,
                    `enabled` tinyint(1) NOT NULL DEFAULT 0,
                    `client_id` text DEFAULT NULL,
                    `client_secret` text DEFAULT NULL,
                    `sandbox` tinyint(1) NOT NULL DEFAULT 0,
                    `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `provider` (`provider`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

                CREATE TABLE `social_logins` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `provider` varchar(255) NOT NULL,
                    `enabled` tinyint(1) NOT NULL DEFAULT 0,
                    `client_id` text DEFAULT NULL,
                    `client_secret` text DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `provider` (`provider`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

                CREATE TABLE `products` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `order_id` int(11) DEFAULT NULL,
                    `category_id` varchar(255) DEFAULT NULL,
                    `image_url` varchar(255) NOT NULL,
                    `name` varchar(255) NOT NULL,
                    `description` text NOT NULL,
                    `price` decimal(10,2) NOT NULL,
                    `status` enum('available','unavailable','discontinued') NOT NULL DEFAULT 'available',
                    `domain_or_ip` enum('force', 'optional') NOT NULL DEFAULT 'force',
                    `type` enum('one-time','monthly') NOT NULL DEFAULT 'one-time',
                    `created_at` timestamp NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

                CREATE TABLE `purchases` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `user_id` int(10) UNSIGNED NOT NULL,
                    `product_id` int(10) UNSIGNED NOT NULL,
                    `order_id` varchar(100) NOT NULL,
                    `suspended` enum('true','false') NOT NULL DEFAULT 'false',
                    `created_at` datetime DEFAULT current_timestamp(),
                    `nextpay_day` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

                CREATE TABLE `settings` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `license_prefix` varchar(255) DEFAULT NULL,
                    `license_length` int(11) NOT NULL DEFAULT 16,
                    `recaptcha_provider` varchar(255) DEFAULT NULL,
                    `recaptcha_site_key` varchar(255) DEFAULT NULL,
                    `recaptcha_secret_key` varchar(255) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `unique_license` (`license_prefix`,`license_length`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

                CREATE TABLE `users` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(100) NOT NULL,
                    `username` varchar(100) NOT NULL,
                    `email` varchar(255) DEFAULT NULL,
                    `password` varchar(255) NOT NULL,
                    `role` enum('admin','user') NOT NULL DEFAULT 'user',
                    `protected` tinyint(1) DEFAULT 0,
                    `discord_id` varchar(255) DEFAULT NULL,
                    `google_id` varchar(255) DEFAULT NULL,
                    `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
                    `two_factor_secret` varchar(255) DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
            ");

            $passwordHash = password_hash($_POST['admin_password'] ?? '', PASSWORD_DEFAULT);
            if (!$passwordHash) {
                $errors[] = 'Admin password hashing failed';
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (id, name, username, email, password, role) VALUES (?, ?, ?, ?, ?, 'admin')");
                $stmt->execute([1, $_POST['admin_name'] ?? 'Admin', $_POST['admin_username'] ?? 'admin', $_POST['admin_email'] ?? 'admin@example.com', $passwordHash]);

                $configArray = [
                    'installed' => true,
                    'mysql' => $mysql,
                    'smtp' => $smtp,
                    'openssl_key' => $openssl_key,
                    'meta' => $meta,
                ];

                $configContent = '<?php return ' . var_export($configArray, true) . ';';
                if (file_put_contents($configFile, $configContent) === false) {
                    $errors[] = 'Failed to write config file';
                } else {
                    $success = true;
                }
            }
        } catch (Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Installer</title>
    <link href="/includes/css/output.css" rel="stylesheet">
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="flex w-full max-w-4xl bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="w-64 bg-gray-800 p-4">
            <ul class="space-y-2">
                <li><a href="#mysql-settings" class="block py-2 px-4 text-sm font-semibold text-gray-100 rounded-lg bg-gray-700 hover:bg-gray-600">MySQL Settings</a></li>
                <li><a href="#app-keys" class="block py-2 px-4 text-sm font-semibold text-gray-100 rounded-lg bg-gray-700 hover:bg-gray-600">OpenSSL Keys</a></li>
                <li><a href="#smtp-settings" class="block py-2 px-4 text-sm font-semibold text-gray-100 rounded-lg bg-gray-700 hover:bg-gray-600">SMTP Settings</a></li>
                <li><a href="#admin-user" class="block py-2 px-4 text-sm font-semibold text-gray-100 rounded-lg bg-gray-700 hover:bg-gray-600">Admin User</a></li>
                <li><a href="#meta-settings" class="block py-2 px-4 text-sm font-semibold text-gray-100 rounded-lg bg-gray-700 hover:bg-gray-600">Meta Settings</a></li>
            </ul>
        </div>
        <div class="flex-1 p-8">
            <?php if ($success): ?>
                <div class="flex flex-col items-center space-y-4 text-green-400">
                    <svg class="w-20 h-20 animate-bounce" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <h1 class="text-3xl font-bold">Installation Completed</h1>
                    <p class="text-center max-w-md">Your application has been installed successfully. You can now <a href="login.php" class="underline text-indigo-400">log in</a>.</p>
                </div>
            <?php elseif ($installing): ?>
                <div class="flex flex-col items-center space-y-4">
                    <svg class="w-20 h-20 text-indigo-400 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <h1 class="text-3xl font-bold">Processing Installation</h1>
                    <p>Please wait...</p>
                </div>
            <?php else: ?>
                <form method="POST" class="space-y-6">
                    <?php if ($errors): ?>
                        <div class="bg-red-700 p-4 rounded space-y-2">
                            <?php foreach ($errors as $error): ?>
                                <p><?= htmlspecialchars($error) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div id="mysql-settings" class="tab-content active">
                        <fieldset class="border border-gray-600 rounded p-4">
                            <legend class="font-semibold text-lg mb-2">MySQL Settings</legend>
                            <div class="space-y-4">
                                <input type="text" name="mysql_host" value="localhost" placeholder="MySQL Host" required class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="mysql_dbname" value="LicenseForge" placeholder="Database Name" required class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="mysql_dbport" value="3306" placeholder="Database Port" required class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="mysql_username" value="root" placeholder="Username" required class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="password" name="mysql_password" placeholder="Password" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="mysql_charset" placeholder="Charset" value="utf8mb4" required class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            </div>
                        </fieldset>
                    </div>

                    <div id="app-keys" class="tab-content">
                        <fieldset class="border border-gray-600 rounded p-4">
                            <legend class="font-semibold text-lg mb-2">OpenSSL Key</legend>
                            <div class="space-y-4">
                                <input type="text" name="openssl_key" placeholder="OpenSSL Key (can be random)" required class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            </div>
                        </fieldset>
                    </div>

                    <div id="smtp-settings" class="tab-content">
                        <fieldset class="border border-gray-600 rounded p-4">
                            <legend class="font-semibold text-lg mb-2">SMTP Settings</legend>
                            <div class="space-y-4">
                                <input type="text" name="smtp_host" value="localhost" placeholder="SMTP Host" required class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="email" name="smtp_mail" value="no-reply@example.com" placeholder="Email" required class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="smtp_username" value="no-reply@example.com" placeholder="Username" required class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="password" name="smtp_password" placeholder="Password" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <select name="smtp_secure" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                </select>
                                <input type="number" name="smtp_port" placeholder="Port" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            </div>
                        </fieldset>
                    </div>

                    <div id="admin-user" class="tab-content">
                        <fieldset class="border border-gray-600 rounded p-4">
                            <legend class="font-semibold text-lg mb-2">Admin User</legend>
                            <div class="space-y-4">
                                <input type="text" name="admin_name" placeholder="Admin Name" value="Admin" required class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="admin_username" placeholder="Admin Username" value="admin" required class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="email" name="admin_email" placeholder="Admin Email" value="admin@example.com" required class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="password" name="admin_password" placeholder="Admin Password" required class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            </div>
                        </fieldset>
                    </div>

                    <div id="meta-settings" class="tab-content">
                        <fieldset class="border border-gray-600 rounded p-4">
                            <legend class="font-semibold text-lg mb-2">Meta Settings</legend>
                            <div class="space-y-4">
                                <input type="text" name="meta_title" placeholder="Meta Title" value="<?= $metaDefaults['title'] ?>" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="meta_description" placeholder="Meta Description" value="<?= $metaDefaults['description'] ?>" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="meta_keywords" placeholder="Meta Keywords" value="<?= $metaDefaults['keywords'] ?>" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="meta_favicon" placeholder="Favicon URL" value="<?= $metaDefaults['favicon'] ?>" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            </div>
                        </fieldset>
                        <fieldset class="border border-gray-600 rounded p-4 mt-4">
                            <legend class="font-semibold text-lg mb-2">Twitter Settings</legend>
                            <div class="space-y-4">
                                <input type="text" name="twitter_card" placeholder="Twitter Card" value="<?= $metaDefaults['twitter']['card'] ?>" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="twitter_site" placeholder="Twitter Site" value="<?= $metaDefaults['twitter']['site'] ?>" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="twitter_title" placeholder="Twitter Title" value="<?= $metaDefaults['twitter']['title'] ?>" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="twitter_description" placeholder="Twitter Description" value="<?= $metaDefaults['twitter']['description'] ?>" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="twitter_image" placeholder="Twitter Image URL" value="<?= $metaDefaults['twitter']['image'] ?>" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            </div>
                        </fieldset>
                        <fieldset class="border border-gray-600 rounded p-4 mt-4">
                            <legend class="font-semibold text-lg mb-2">OpenGraph Settings</legend>
                            <div class="space-y-4">
                                <input type="text" name="og_type" placeholder="OpenGraph Type" value="<?= $metaDefaults['opengraph']['type'] ?>" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="og_url" placeholder="OpenGraph URL" value="<?= $metaDefaults['opengraph']['url'] ?>" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="og_title" placeholder="OpenGraph Title" value="<?= $metaDefaults['opengraph']['title'] ?>" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="og_description" placeholder="OpenGraph Description" value="<?= $metaDefaults['opengraph']['description'] ?>" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="og_site_name" placeholder="OpenGraph Site Name" value="<?= $metaDefaults['opengraph']['site_name'] ?>" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="og_image_url" placeholder="OpenGraph Image URL" value="<?= $metaDefaults['opengraph']['image']['url'] ?>" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="text" name="og_image_type" placeholder="OpenGraph Image Type" value="<?= $metaDefaults['opengraph']['image']['type'] ?>" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="number" name="og_image_width" placeholder="OpenGraph Image Width" value="<?= $metaDefaults['opengraph']['image']['width'] ?>" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                <input type="number" name="og_image_height" placeholder="OpenGraph Image Height" value="<?= $metaDefaults['opengraph']['image']['height'] ?>" class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            </div>
                        </fieldset>
                    </div>

                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 rounded font-semibold transition-colors">Install</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.querySelectorAll('a[href^="#"]').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.querySelector(this.getAttribute('href')).classList.add('active');
            });
        });
    </script>
</body>
</html>