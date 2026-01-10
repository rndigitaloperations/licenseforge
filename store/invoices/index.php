<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/store/invoices/index.php'; ?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($meta['favicon']) ?>" />
    <title>View Invoice #<?= htmlspecialchars($invoice['id']) ?></title>

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
<body class="bg-gray-900 text-gray-100 min-h-screen flex items-center justify-center p-6">

  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/dashboard/me/navbar.php'; ?>
  
  <div class="max-w-md w-full bg-gray-800 rounded-md shadow-lg p-6 space-y-4">
    <h1 class="text-2xl font-semibold">Invoice #<?= htmlspecialchars($invoice['id']) ?></h1>

    <div>
      <p class="text-sm text-gray-400 mb-1">Product</p>
      <p class="text-lg font-medium"><?= htmlspecialchars($productName) ?></p>
    </div>

    <div>
      <p class="text-sm text-gray-400 mb-1">Amount</p>
      <p class="text-lg font-medium"><?= htmlspecialchars($currencyIcon) ?><?= htmlspecialchars($productPrice) ?> <?= htmlspecialchars($currency) ?></p>
    </div>

    <div>
      <p class="text-sm text-gray-400 mb-1">Status</p>
      <?= renderStatusBadge($invoice['status']) ?>
    </div>

    <div>
      <p class="text-sm text-gray-400 mb-1">Invoiced At</p>
      <p class="text-sm"><?= htmlspecialchars($invoice['created_at']) ?></p>
    </div>

    <?php if ($invoice['status'] === 'unpaid'): ?>
      <a href="/invoices/pay/<?= urlencode($invoice['id']) ?>" class="block w-full mt-4 bg-blue-600 hover:bg-blue-700 transition rounded-md py-2 text-center font-medium">
        Pay Now
      </a>
    <?php endif; ?>
  </div>
</body>
</html>