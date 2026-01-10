<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/dashboard/me/purchases.php'; ?>

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
<body class="bg-gray-900 text-gray-100 min-h-screen flex flex-col">

   <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/dashboard/me/navbar.php'; ?>

    <div class="flex flex-1">
        <main class="flex-1 p-8 md:ml-64">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-4xl font-bold">Purchases</h1>
            </div>

            <div class="bg-gray-800 p-6 rounded shadow-lg mb-8">
                <h2 class="text-2xl font-semibold mb-4">All Purchases</h2>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto border-collapse">
                        <thead>
                            <tr class="bg-gray-700">
                                <th class="px-4 py-2 border">ID</th>
                                <th class="px-4 py-2 border">Product</th>
                                <th class="px-4 py-2 border">Order ID</th>
                                <th class="px-4 py-2 border">Suspended</th>
                                <th class="px-4 py-2 border">Purchased on</th>
                                <th class="px-4 py-2 border">Nextpay Day</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($purchases as $purchase): ?>
                            <tr class="bg-gray-800 hover:bg-gray-700">
                                <td class="px-4 py-2 border"><?= htmlspecialchars($purchase['id']) ?></td>
                                <td class="px-4 py-2 border"><?= htmlspecialchars($purchase['product_name']) ?></td>
                                <td class="px-4 py-2 border"><?= htmlspecialchars($purchase['order_id']) ?></td>
                                <td class="px-4 py-2 border"><?= $purchase['suspended'] === 'true' ? 'Yes' : 'No' ?></td>
                                <td class="px-4 py-2 border"><?= htmlspecialchars($purchase['created_at']) ?></td>
                                <td class="px-4 py-2 border"><?= htmlspecialchars($purchase['nextpay_day']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($purchases)): ?>
                            <tr>
                                <td colspan="7" class="px-4 py-2 border text-center">No purchases found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex justify-center space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded">Previous</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="<?= $i === $page ? 'bg-indigo-600' : 'bg-gray-700 hover:bg-gray-600' ?> text-white px-3 py-1 rounded"><?= $i ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

</body>
</html>