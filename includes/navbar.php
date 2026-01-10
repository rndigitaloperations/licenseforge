<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/navagation/navbar.php';
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/analytics.php';
?>

<nav class="bg-gray-800 fixed top-0 left-0 right-0 h-16 flex items-center justify-between px-6 shadow-md z-40 hidden md:flex">
  <div class="flex items-center space-x-4">
    <div class="text-2xl font-bold tracking-wide text-white"><?= htmlspecialchars($appName) ?></div>
  </div>
  <div class="flex items-center space-x-2 relative text-white">
    <?php if ($storeEnabled): ?>
      <a href="/" class="block px-4 py-2 rounded hover:bg-indigo-600 transition-colors">Home</a>
      <div class="relative group">
        <button class="flex items-center space-x-2 px-4 py-2 rounded hover:bg-indigo-600 transition-colors text-white focus:outline-none">
          Store
          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        <div class="absolute right-0 mt-2 w-40 bg-gray-800 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-50">
          <?php foreach ($categories as $category): ?>
            <a href="/<?= urlencode($category['id']) ?>/products" class="block px-4 py-2 hover:bg-indigo-600 transition-colors whitespace-nowrap"><?= htmlspecialchars($category['name']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($contactEnabled): ?>
      <a href="/contact" class="block px-4 py-2 rounded hover:bg-indigo-600 transition-colors">Contact us</a>
    <?php endif; ?>

    <?php if ($user): ?>
      <div class="relative group">
        <button class="flex items-center space-x-2 px-4 py-2 rounded hover:bg-indigo-600 transition-colors text-white focus:outline-none">
          <img src="<?= $gravatarUrl ?>" alt="Avatar" class="w-8 h-8 rounded-full">
          <span><?= htmlspecialchars($user['username']) ?></span>
          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8l7 7 7-7" />
          </svg>
        </button>
        <div class="absolute right-0 mt-2 w-56 bg-gray-800 rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-50 flex flex-col">
          <a href="/dashboard/me/" class="block px-4 py-2 hover:bg-indigo-600 transition-colors">Dashboard</a>
          <a href="/logout.php" class="block px-4 py-2 hover:bg-indigo-600 transition-colors">Logout</a>

          <form method="post" class="px-4 py-2 hover:bg-indigo-600 transition-colors text-white flex justify-between items-center" style="border-top: 1px solid #4c51bf;">
            <label for="currency" class="mr-2 whitespace-nowrap">Currency:</label>
            <select id="currency" name="currency" onchange="this.form.submit()" class="bg-gray-700 text-white rounded px-2 py-1 focus:outline-none">
              <?php foreach ($supportedCurrencies as $code => $label): ?>
                <option value="<?= htmlspecialchars($code) ?>" <?= ($currency === $code) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>
      </div>
    <?php else: ?>
      <form method="post" class="text-white ml-4">
        <select name="currency" onchange="this.form.submit()" class="bg-gray-700 text-white rounded px-2 py-1 focus:outline-none">
          <?php foreach ($supportedCurrencies as $code => $label): ?>
            <option value="<?= htmlspecialchars($code) ?>" <?= ($currency === $code) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
          <?php endforeach; ?>
        </select>
      </form>

      <a href="/register.php" class="block px-4 py-2 rounded hover:bg-indigo-600 transition-colors">Register</a>
      <a href="/login.php" class="block px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 transition-colors font-semibold">Login</a>
    <?php endif; ?>
  </div>
</nav>

<nav class="bg-gray-800 fixed top-0 left-0 right-0 h-16 flex items-center justify-between px-6 shadow-md z-40 md:hidden">
  <div class="flex items-center space-x-4">
    <div class="text-2xl font-bold tracking-wide text-white"><?= htmlspecialchars($meta['title']) ?></div>

    <?php if (!$user): ?>
      <form method="post" class="text-white">
        <select name="currency" onchange="this.form.submit()" class="bg-gray-700 text-white rounded px-2 py-1 focus:outline-none">
          <?php foreach ($supportedCurrencies as $code => $label): ?>
            <option value="<?= htmlspecialchars($code) ?>" <?= ($currency === $code) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    <?php endif; ?>
  </div>
  <button id="menu-toggle" class="text-white focus:outline-none" aria-label="Toggle sidebar">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
  </button>
  <div id="menu" class="hidden absolute top-16 left-0 right-0 bg-gray-800 shadow-md z-50">
    <div class="flex flex-col">
      <?php if ($storeEnabled): ?>
      <a href="/" class="block px-4 py-2 text-white hover:bg-indigo-600 transition-colors">Home</a>
      <div class="relative">
        <button id="store-dropdown-toggle" class="flex items-center space-x-2 px-4 py-2 text-white hover:bg-indigo-600 transition-colors w-full text-left">
          Store
          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        <div id="store-dropdown" class="hidden bg-gray-800 rounded-md shadow-lg z-50">
          <?php foreach ($categories as $category): ?>
            <a href="/<?= urlencode($category['id']) ?>/products" class="block px-4 py-2 hover:bg-indigo-600 transition-colors whitespace-nowrap"><?= htmlspecialchars($category['name']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
      <?php if ($contactEnabled): ?>
        <a href="/contact" class="block px-4 py-2 text-white hover:bg-indigo-600 transition-colors">Contact us</a>
      <?php endif; ?>
      <?php if ($user): ?>
        <div class="relative">
          <button id="user-dropdown-toggle" class="flex items-center space-x-2 px-4 py-2 text-white hover:bg-indigo-600 transition-colors w-full text-left">
            <img src="<?= $gravatarUrl ?>" alt="Avatar" class="w-8 h-8 rounded-full">
            <span><?= htmlspecialchars($user['username']) ?></span>
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8l7 7 7-7" />
            </svg>
          </button>
          <div id="user-dropdown" class="hidden bg-gray-800 rounded-md shadow-lg z-50 flex flex-col">
            <a href="/dashboard/me/" class="block px-4 py-2 hover:bg-indigo-600 transition-colors">Dashboard</a>
            <a href="/logout.php" class="block px-4 py-2 hover:bg-indigo-600 transition-colors">Logout</a>

            <form method="post" class="px-4 py-2 hover:bg-indigo-600 transition-colors text-white flex justify-between items-center border-t border-indigo-600">
              <label for="currency-mobile" class="mr-2 whitespace-nowrap">Currency:</label>
              <select id="currency-mobile" name="currency" onchange="this.form.submit()" class="bg-gray-700 text-white rounded px-2 py-1 focus:outline-none">
                <?php foreach ($supportedCurrencies as $code => $label): ?>
                  <option value="<?= htmlspecialchars($code) ?>" <?= ($currency === $code) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </div>
        </div>
      <?php else: ?>
        <form method="post" class="text-white px-4 py-2">
          <select name="currency" onchange="this.form.submit()" class="bg-gray-700 text-white rounded px-2 py-1 focus:outline-none">
            <?php foreach ($supportedCurrencies as $code => $label): ?>
              <option value="<?= htmlspecialchars($code) ?>" <?= ($currency === $code) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
            <?php endforeach; ?>
          </select>
        </form>
        <a href="/register.php" class="block px-4 py-2 rounded hover:bg-indigo-600 transition-colors">Register</a>
        <a href="/login.php" class="block px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 transition-colors font-semibold">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="h-16"></div>

<script src="/includes/js/navbar.js"> </script>