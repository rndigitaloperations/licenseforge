<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/dashboard/me/stats.php'; ?>

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex flex-col">

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/dashboard/me/navbar.php'; ?>

    <main class="flex-1 p-8 w-full md:ml-64 md:w-auto">
        <h1 class="text-4xl font-bold mb-4">Welcome to <?= htmlspecialchars($appName) ?>!</h1>
        <p class="mb-4">Easily renew, update, and track your licenses anytime, anywhere with a simple and efficient process.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mb-12">
            <div class="bg-gray-800 rounded-lg p-6 text-center shadow">
                <h2 class="text-3xl font-bold"><?= (int)$stats['total'] ?></h2>
                <p class="text-gray-400 mt-1">Total Licenses</p>
            </div>
            <div class="bg-green-700 rounded-lg p-6 text-center shadow">
                <h2 class="text-3xl font-bold"><?= (int)$stats['valid'] ?></h2>
                <p class="text-gray-200 mt-1">Valid Licenses</p>
            </div>
            <div class="bg-red-700 rounded-lg p-6 text-center shadow">
                <h2 class="text-3xl font-bold"><?= (int)$stats['invalid'] ?></h2>
                <p class="text-gray-200 mt-1">Invalid Licenses</p>
            </div>
            <div class="bg-yellow-600 rounded-lg p-6 text-center shadow">
                <h2 class="text-3xl font-bold"><?= (int)$stats['suspended'] ?></h2>
                <p class="text-gray-200 mt-1">Suspended Licenses</p>
            </div>
        </div>

        <section class="bg-gray-800 p-6 rounded-lg shadow">
            <h2 class="text-2xl font-semibold mb-6 text-center">Licenses Added Last 30 Days by Status</h2>
            <canvas id="licensesChart" height="120"></canvas>
        </section>
    </main>

    <script src="/includes/dashboard/me/chart-stats.php"></script>

</body>
</html>