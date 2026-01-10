<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/store/invoices/pay.php'; ?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($meta['favicon']) ?>" />
    <title>Pay Invoice #<?= htmlspecialchars($invoice['id']) ?></title>

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

  <div class="max-w-md w-full bg-gray-800 rounded-md shadow-lg p-6">
    <h1 class="text-2xl font-semibold mb-4">Pay Invoice #<?= htmlspecialchars($invoice['id']) ?></h1>
    <p class="mb-2"><span class="font-semibold">Product:</span> <?= htmlspecialchars($invoice['name']) ?></p>
    <p class="mb-6"><span class="font-semibold">Amount:</span> <?= htmlspecialchars($currencyIcon) ?><?= htmlspecialchars($invoice['price']) ?> <?= htmlspecialchars($currency) ?></p>
    <form method="post" class="space-y-4">
      <?php if ($stripeAvailable): ?>
        <div class="flex items-center space-x-4">
          <input type="radio" id="stripe" name="payment_provider" value="stripe" class="form-radio" <?= $defaultPaymentMethod === 'stripe' ? 'checked' : '' ?>>
          <label for="stripe" class="text-gray-300">Pay with Stripe</label>
        </div>
      <?php endif; ?>
      <?php if ($paypalAvailable): ?>
        <div class="flex items-center space-x-4">
          <input type="radio" id="paypal" name="payment_provider" value="paypal" class="form-radio" <?= $defaultPaymentMethod === 'paypal' ? 'checked' : '' ?>>
          <label for="paypal" class="text-gray-300">Pay with PayPal</label>
        </div>
      <?php endif; ?>
      <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 transition rounded-md py-2 font-medium">Pay Now</button>
    </form>
  </div>
</body>
</html>