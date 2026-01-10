<?php 
include $_SERVER['DOCUMENT_ROOT'] . '/includes/store/product.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/store/payments.php';
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  
  <link rel="icon" type="image/x-icon" href="<?=htmlspecialchars($meta['favicon'])?>" />
  <title><?=htmlspecialchars($meta['title'])?> - Store</title>
  <meta name="description" content="<?=htmlspecialchars($meta['description'])?>" />
  <meta name="keywords" content="<?=htmlspecialchars($meta['keywords'])?>" />

  <meta name="twitter:card" content="<?=htmlspecialchars($meta['twitter']['card'])?>" />
  <meta name="twitter:site" content="<?=htmlspecialchars($meta['twitter']['site'])?>" />
  <meta name="twitter:title" content="<?=htmlspecialchars($meta['twitter']['title'])?>" />
  <meta name="twitter:description" content="<?=htmlspecialchars($meta['twitter']['description'])?>" />
  <meta name="twitter:image" content="<?=htmlspecialchars($meta['twitter']['image'])?>" />

  <meta property="og:type" content="<?=htmlspecialchars($meta['opengraph']['type'])?>" />
  <meta property="og:url" content="<?=htmlspecialchars($meta['opengraph']['url'])?>" />
  <meta property="og:title" content="<?=htmlspecialchars($meta['opengraph']['title'])?>" />
  <meta property="og:description" content="<?=htmlspecialchars($meta['opengraph']['description'])?>" />
  <meta property="og:site_name" content="<?=htmlspecialchars($meta['opengraph']['site_name'])?>" />
  <meta property="og:image" content="<?=htmlspecialchars($meta['opengraph']['image']['url'])?>" />
  <meta property="og:image:type" content="<?=htmlspecialchars($meta['opengraph']['image']['type'])?>" />
  <meta property="og:image:width" content="<?=htmlspecialchars($meta['opengraph']['image']['width'])?>" />
  <meta property="og:image:height" content="<?=htmlspecialchars($meta['opengraph']['image']['height'])?>" />

  <link href="/includes/css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">

  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>

  <div class="container mx-auto px-4 py-16 max-w-4xl relative">

    <button 
      onclick="window.location.href='/<?=htmlspecialchars($product['category_id'])?>/products'"
      class="-mt-16 mb-6 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded shadow"
    >
      Go Back
    </button>

    <div class="flex flex-col md:flex-row md:space-x-10 items-start">

      <div class="md:w-1/2 w-full mb-6 md:mb-0 bg-gray-800 border border-gray-700 rounded p-4 flex items-center justify-center h-64 relative">
        <?php if (!empty($product['image_url'])): ?>
          <img 
            src="<?=htmlspecialchars($product['image_url'])?>" 
            alt="<?=htmlspecialchars($product['name'])?>" 
            class="max-w-full max-h-full object-contain" 
          />
        <?php else: ?>
          <span class="text-gray-500">No image available</span>
        <?php endif; ?>
      </div>

      <div class="md:w-1/2 w-full flex flex-col justify-start">
        <h1 class="text-4xl font-bold mb-4 flex items-center gap-3">
          <?=htmlspecialchars($product['name'])?>
        </h1>

        <p class="text-indigo-400 font-semibold text-2xl mb-4">
          <?= ($product['price'] == 0) ? 'Free' : htmlspecialchars($currencyIcon) . number_format($product['price'], 2) ?>
        </p>

        <?php if ($showPaymentSuccess): ?>
          <div class="bg-green-700 p-6 rounded text-center text-white font-semibold text-xl">
            Payment successful! Your purchase has been recorded.
            <br/>
            Processing...
          </div>
          <script>
            setTimeout(() => {
              const url = new URL(window.location.href);
              url.searchParams.delete('session_id');
              url.searchParams.delete('token');
              url.searchParams.delete('PayerID');
              window.location.href = url.toString();
            }, 5000);
          </script>
        <?php else: ?>
          <p class="mb-18 whitespace-pre-line"><?= $Parsedown->text($product['description']) ?></p>

          <?php if ($userHasProductSuspended): ?>
            <button
              disabled
              class="mt-4 w-full px-6 py-3 bg-red-600 rounded font-semibold text-white cursor-not-allowed"
            >
              You are suspended from this product
            </button>
            <p class="mt-3 text-red-400 text-sm">
              This is because your invoice has not been paid. If you pay it before it's too late, you will automatically regain access to the product.
            </p>
          <?php elseif ($userHasProduct && isset($product['status']) && strtolower($product['status']) !== 'discontinued' && isset($_SESSION['user_id'], $_SESSION['loggedIn']) && $_SESSION['loggedIn']): ?>
            <a
              href="<?= $downloadUrl ?>"
              download
              class="mt-4 w-full px-6 py-3 bg-green-600 rounded font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2"
            >
              Download Now
            </a>
          <?php elseif (isset($product['status']) && strtolower($product['status']) === 'available'): ?>
            <?php if ($price == 0 || $price == 0.00): ?>
              <a
                href="<?= $downloadUrl ?>"
                download
                class="mt-4 w-full px-6 py-3 bg-green-600 rounded font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2"
              >
                Download Now
              </a>
            <?php elseif (count($enabledPayments) > 0): ?>
              <form method="POST" class="mt-4">
                <?php if (isset($product['domain_or_ip']) && $product['domain_or_ip'] === 'force'): ?>
                  <div class="mb-4">
                    <label for="domain_or_ip" class="block text-white font-semibold mb-2">
                      Domain or IP Address: <span class="text-red-500">*</span>
                    </label>
                    <input 
                      type="text" 
                      id="domain_or_ip" 
                      name="domain_or_ip" 
                      value="<?= isset($_COOKIE['product_domain_or_ip']) ? htmlspecialchars($_COOKIE['product_domain_or_ip']) : '' ?>"
                      placeholder="example.com or 192.168.1.1"
                      required
                      class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent"
                    />
                    <p class="mt-1 text-gray-400 text-sm">Enter your domain name or IP address</p>
                  </div>
                <?php endif; ?>
                
                <fieldset class="mb-4">
                  <legend class="text-white font-semibold mb-2">Choose Payment Gateway:</legend>
                  <div class="flex flex-col space-y-2">
                    <?php foreach ($enabledPayments as $index => $payment): ?>
                      <label class="inline-flex items-center cursor-pointer">
                        <input 
                          type="radio" 
                          name="payment_provider" 
                          value="<?=htmlspecialchars($payment['provider'])?>" 
                          <?= $index === 0 ? 'checked' : '' ?> 
                          class="form-radio text-indigo-600"
                        />
                        <span class="ml-2 text-gray-200"><?=htmlspecialchars(ucfirst($payment['provider']))?></span>
                      </label>
                    <?php endforeach; ?>
                  </div>
                </fieldset>

                <button
                  type="submit"
                  name="buy_now"
                  class="w-full px-6 py-3 bg-indigo-600 rounded font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2"
                >
                  Buy Now for <?=htmlspecialchars($currencyIcon)?><?=number_format($price, 2)?>
                </button>
              </form>
            <?php else: ?>
              <p class="text-red-500">No payment methods available at the moment.</p>
            <?php endif; ?>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>

</body>
</html>