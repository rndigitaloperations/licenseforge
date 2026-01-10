<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/dashboard/health/health.php'; ?>

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
    <link href="/includes/css/health.css" rel="stylesheet">
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex flex-col">

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/dashboard/navbar.php'; ?>

    <main class="flex-1 p-8 w-full md:ml-64 md:w-auto">
        <div class="flex items-center justify-between mb-8">
          <h1 class="text-4xl font-bold"><?= $appName ?> Health</h1>
          <button
            onclick="location.reload();"
            class="ml-4 p-2 rounded-lg hover:bg-gray-700 transition duration-200"
            title="Refresh">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-200" stroke="currentColor">
                <path d="M8.54636 19.7673C10.9455 20.8316 13.803 20.7738 16.2499 19.361C20.3154 17.0138 21.7084 11.8153 19.3612 7.74983L19.1112 7.31682M4.63826 16.25C2.29105 12.1845 3.68399 6.98595 7.74948 4.63874C10.1965 3.22597 13.0539 3.16816 15.4531 4.23253M2.49316 16.3336L5.22521 17.0657L5.95727 14.3336M18.0424 9.66565L18.7744 6.9336L21.5065 7.66565" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        </div>
        <div class="custom-dashboard-row mb-6">
            <div class="bg-gray-800 p-6 rounded-lg shadow">
                <h2 class="text-2xl font-semibold mb-4">Application Version</h2>
                <p class="mb-2">Current Version: <strong><?= $appVersion ?></strong></p>
                <p>
                    Status:
                    <strong class="<?= $appVersion === $latestVersion ? 'text-green-400' : 'text-red-400' ?>">
                        <?= $appVersion === $latestVersion
                            ? 'Up to date'
                            : "You are running $appVersion but the latest version is $latestVersion" ?>
                    </strong>
                </p>
            </div>
            <div
                class="bg-gray-800 p-6 rounded-lg shadow cursor-pointer"
                onclick="toggleCronPopup()">
                <h2 class="text-2xl font-semibold mb-4">Cronjob Status</h2>
                <div id="cronStatus">
                    <?php if (!is_null($lastRun)): ?>
                        <?php
                            $statusOk = $minutesAgo <= 5;
                            $statusColor = $statusOk ? 'text-green-400' : 'text-red-400';
                        ?>
                        <p class="<?= $statusColor ?>">
                            Last Cron Run:
                            <strong><?= $lastRun->format('Y-m-d H:i:s') ?></strong>
                            (<?= $minutesAgo ?> minutes ago)
                        </p>
                        <p>
                        Status:
                        <strong class="<?= $statusColor = $statusOk ? 'text-green-400' : 'text-red-400' ?>">
                            <?= $statusOk ? 'OK' : 'Not Running' ?>
                        </strong>
                    </p>
                        <p>
                            Click Me for Instructions
                       </p>
                    </p>
                    <?php else: ?>
                        <p class="text-red-400">Cron has never run.</p>
                        <p>
                            Click Me for Instructions
                       </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <div
        id="cronPopup"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
        <div class="bg-gray-800 p-6 rounded-2xl max-w-2xl w-10px mx-4">
            <button
                class="text-gray-400 hover:text-gray-200 float-right text-2xl leading-none"
                onclick="toggleCronPopup()"
            >&times;</button>
            <h2 class="text-2xl font-semibold mb-4">Cronjob Setup Instructions</h2>
            <p>Set one of the following cronjobs on your server:</p>
            <div class="bg-gray-700 p-4 rounded-lg mt-4">
                <p class="mb-2 font-semibold">Option 1: Via cURL</p>
                <pre class="whitespace-pre-wrap break-all">*/5 * * * * curl -s https://<?= htmlspecialchars($host) ?>/crons/cron > /dev/null 2>&1</pre>
            </div>
            <div class="bg-gray-700 p-4 rounded-lg mt-4">
                <p class="mb-2 font-semibold">Option 2: Via PHP CLI (Recommended)</p>
                <pre class="whitespace-pre-wrap break-all">*/5 * * * * php /path/to/your/install/vendor/cron.php > /dev/null 2>&1</pre>
            </div>
            <p class="text-gray-400 italic mt-4">
                Replace the PHP path with your actual server path.
            </p>
        </div>
    </div>

    <script src="/includes/dashboard/health/health.js"></script>

</body>
</html>