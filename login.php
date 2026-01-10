<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/login.php'; ?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($meta['favicon']) ?>" />
    <title><?= htmlspecialchars($meta['title']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta['description']) ?>" />
    <meta name="keywords" content="<?= htmlspecialchars($meta['keywords']) ?>" />

    <meta name="twitter:card" content="<?= htmlspecialchars($meta['twitter']['card']) ?>" />
    <meta name="twitter:site" content="<?= htmlspecialchars($meta['twitter']['site']) ?>" />
    <meta name="twitter:title" content="<?= htmlspecialchars($meta['twitter']['title']) ?>" />
    <meta name="twitter:description" content="<?= htmlspecialchars($meta['twitter']['description']) ?>" />
    <meta name="twitter:image" content="<?= htmlspecialchars($meta['twitter']['image']) ?>" />

    <meta property="og:type" content="<?= htmlspecialchars($meta['opengraph']['type']) ?>" />
    <meta property="og:url" content="<?= htmlspecialchars($meta['opengraph']['url']) ?>" />
    <meta property="og:title" content="<?= htmlspecialchars($meta['opengraph']['title']) ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($meta['opengraph']['description']) ?>" />
    <meta property="og:site_name" content="<?= htmlspecialchars($meta['opengraph']['site_name']) ?>" />
    <meta property="og:image" content="<?= htmlspecialchars($meta['opengraph']['image']['url']) ?>" />
    <meta property="og:image:type" content="<?= htmlspecialchars($meta['opengraph']['image']['type']) ?>" />
    <meta property="og:image:width" content="<?= htmlspecialchars($meta['opengraph']['image']['width']) ?>" />
    <meta property="og:image:height" content="<?= htmlspecialchars($meta['opengraph']['image']['height']) ?>" />

    <link href="/includes/css/output.css" rel="stylesheet">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/recaptcha/recaptcha.php'; ?>
</head>
<body class="bg-gray-900 text-gray-100 flex items-center justify-center min-h-screen">

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>

<div class="w-full max-w-md p-8 space-y-6 bg-gray-800 rounded-lg shadow-lg">
    <h2 class="text-3xl font-bold text-center">Login</h2>

    <?php if ($error): ?>
        <div class="bg-red-600 text-white px-4 py-2 rounded">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!$showOnly2FAField): ?>
        <form id="loginForm" method="POST" class="space-y-6">
            <div>
                <label for="username" class="block mb-2 font-semibold">Username</label>
                <input type="text" id="username" name="username" required autofocus class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="password" class="font-semibold">Password</label>
                    <a href="/forgot-password.php" class="text-sm text-indigo-500 hover:underline">Forgot Password</a>
                </div>
                <input type="password" id="password" name="password" required class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/recaptcha/load.php'; ?>
            <button type="submit" class="w-full py-2 font-bold rounded bg-indigo-600 hover:bg-indigo-700 transition-colors">
                Log In
            </button>
        </form>
        <div class="flex flex-col space-y-3 mt-4">
            <?php foreach ($socialConnections as $provider => $config): ?>
                <?php if ($config['enabled']): ?>
                    <a href="<?= $config['loginUrl'] ?>" class="flex items-center justify-center py-2 px-4 font-bold rounded bg-gray-700 hover:bg-gray-600 transition-colors">
                        <img src="<?= $config['logo'] ?>" alt="<?= $config['displayName'] ?>" class="w-6 h-6 mr-2">
                        Login with <?= $config['displayName'] ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <p class="mt-4 text-center text-gray-400">
            Don't have an account yet?
            <a href="/register.php" class="text-indigo-500 hover:underline">Register</a>
        </p>
    <?php else: ?>
        <form method="POST" class="space-y-6">
            <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
            <input type="hidden" name="password" value="<?= htmlspecialchars($password) ?>">
            <div>
                <label for="2fa_code" class="block mb-2 font-semibold">2FA Code</label>
                <input type="text" id="2fa_code" name="2fa_code" required autofocus class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" class="w-full py-2 font-bold rounded bg-indigo-600 hover:bg-indigo-700 transition-colors">
                Verify 2FA
            </button>
        </form>
    <?php endif; ?>
</div>

   <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>

</body>
</html>