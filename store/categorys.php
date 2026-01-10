<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/store/categorys.php'; ?>

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

    <div class="container mx-auto px-4 py-12">
        <?php if (empty($categorys)): ?>
          <div class="flex items-center justify-center min-h-[85vh]">
              <div class="bg-gray-800 p-8 rounded-lg shadow-md text-center max-w-md w-full">
                  <p class="mb-4 text-4xl">😕</p>
                  <p class="mb-2 text-gray-200 text-xl font-semibold">No categorys found.</p>
                  <p class="text-gray-400">Please check back later or contact support if this is unexpected.</p>
              </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
                <?php foreach ($categorys as $category): ?>
                    <div class="bg-gray-800 rounded-lg overflow-hidden shadow-lg flex flex-col justify-between transition-transform hover:scale-105">
                        <?php if (!empty($category['image_url'])): ?>
                            <img src="<?= htmlspecialchars($category['image_url']) ?>" alt="<?= htmlspecialchars($category['name']) ?>" class="w-full h-48 object-cover">
                        <?php endif; ?>
                        <div class="p-4 flex-1 flex flex-col">
                            <h2 class="text-2xl font-semibold mb-1"><?= htmlspecialchars($category['name']) ?></h2>
                            <span class="py-1 text-sm rounded-full">
                              <?= $Parsedown->text($category['description']) ?>
                            </span>
                            <a href="/<?= urlencode($category['id']) ?>/products" class="block text-center py-2 bg-indigo-600 hover:bg-indigo-700 rounded text-white font-semibold transition-colors">
                                View Products
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>

</body>
</html>