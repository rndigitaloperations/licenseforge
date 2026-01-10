<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/store/category.php'; ?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($meta['favicon']) ?>" />
    <title><?= htmlspecialchars($meta['title']) ?> - Store</title>
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
<body class="bg-gray-900 text-gray-100 min-h-screen">

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>

<div class="container mx-auto px-4 py-12 flex flex-col lg:flex-row lg:space-x-8 space-y-8 lg:space-y-0">
    <aside class="w-full lg:w-64 bg-gray-800 rounded-lg p-6 sticky top-24 h-fit self-start shadow-lg">
        <h2 class="text-2xl font-bold mb-6 border-b border-gray-700 pb-2">Categories</h2>
        <nav class="flex flex-col space-y-3">
            <?php foreach ($categorys as $category): ?>
                <a href="/<?= $category['id'] ?>/products" class="block px-4 py-2 rounded hover:bg-indigo-600 transition-colors <?= ($category['id'] == $id) ? 'bg-indigo-700 font-semibold' : 'text-gray-300' ?>">
                    <?= htmlspecialchars($category['name']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <main class="flex-1">
        <?php if (empty($products)): ?>
            <div class="flex items-center justify-center min-h-[75vh]" style="margin-right: 100px;">
                <div class="bg-gray-800 p-8 rounded-lg shadow-md text-center max-w-md w-full">
                    <p class="mb-4 text-4xl">😕</p>
                    <p class="mb-2 text-gray-200 text-xl font-semibold">No products found.</p>
                    <p class="text-gray-400">Please check back later or contact support if this is unexpected.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($products as $product): ?>
                    <div class="bg-gray-800 rounded-lg overflow-hidden shadow-lg flex flex-col justify-between transition-transform hover:scale-105">
                        <?php if (!empty($product['image_url'])): ?>
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-48 object-cover">
                        <?php endif; ?>
                        <div class="p-4 flex-1 flex flex-col">
                            <h2 class="text-2xl font-semibold mb-1"><?= htmlspecialchars($product['name']) ?></h2>
                            <p class="text-indigo-400 font-semibold mb-3 text-lg"><?= ($product['price'] == 0) ? 'Free' : htmlspecialchars($currencyIcon) . number_format($product['price'], 2) ?></p>
                            <div class="flex justify-between items-center mb-4">
                                <span class="px-3 py-1 text-sm rounded-full <?= $product['status'] === 'available' ? 'bg-green-600' : ($product['status'] === 'unavailable' ? 'bg-red-600' : 'bg-yellow-600') ?>">
                                    <?= ucfirst($product['status']) ?>
                                </span>
                                <span class="text-sm text-gray-300"><?= ucfirst($product['type']) ?></span>
                            </div>
                            <a href="/<?= $id ?>/products/<?= urlencode($product['id']) ?>" class="block text-center py-2 bg-indigo-600 hover:bg-indigo-700 rounded text-white font-semibold transition-colors">
                                View
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>

</body>
</html>