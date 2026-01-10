<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/forgot-password.php'; ?>

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
</head>
<body class="bg-gray-900 text-gray-100 flex items-center justify-center min-h-screen">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>

<div class="w-full max-w-md p-8 space-y-6 bg-gray-800 rounded-lg shadow-lg">
    <h2 class="text-2xl font-semibold text-center"><?= htmlspecialchars($meta['title']) ?></h2>
    <?php if ($error): ?>
        <p class="text-red-400 bg-red-900 p-2 rounded"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p class="text-green-400 bg-green-900 p-2 rounded"><?= $success ?></p>
    <?php endif; ?>

    <?php if ($showForm): ?>
        <?php if ($resetMode): ?>
            <form action="?code=<?= htmlspecialchars($code) ?>" method="POST" class="space-y-4">
                <div>
                    <label for="password" class="block mb-1">New Password</label>
                    <input id="password" name="password" type="password" required minlength="8"
                        class="w-full px-3 py-2 rounded bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                    <label for="confirm_password" class="block mb-1">Confirm Password</label>
                    <input id="confirm_password" name="confirm_password" type="password" required minlength="8"
                        class="w-full px-3 py-2 rounded bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
                <button type="submit"
                    class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 rounded text-white font-semibold">Reset Password</button>
            </form>
        <?php else: ?>
            <form action="" method="POST" class="space-y-4">
                <div>
                    <label for="email" class="block mb-1">Email Address</label>
                    <input id="email" name="email" type="email" required
                        class="w-full px-3 py-2 rounded bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
                <button type="submit"
                    class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 rounded text-white font-semibold">Send Reset Link</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>