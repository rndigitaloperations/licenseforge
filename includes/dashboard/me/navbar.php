<?php
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$meta = $config['meta'];

$settingStmt = $pdo->query("SELECT app_name FROM app_settings LIMIT 1");
$settings = $settingStmt->fetch(PDO::FETCH_ASSOC);
$appName = !empty($settings['app_name']) ? $settings['app_name'] : 'LicenseForge';

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function isActive($linkPath, $currentPath) {
    return rtrim($linkPath, '/') === rtrim($currentPath, '/');
}

$supportedCurrencies = [
    'USD' => 'USD',
    'EUR' => 'EUR',
    'GBP' => 'GBP',
    'AUD' => 'AUD',
    'CAD' => 'CAD',
    'JPY' => 'JPY',
    'CHF' => 'CHF',
    'NZD' => 'NZD',
    'SEK' => 'SEK',
    'NOK' => 'NOK',
    'DKK' => 'DKK',
    'ZAR' => 'ZAR',
    'SGD' => 'SGD',
    'HKD' => 'HKD',
    'MXN' => 'MXN',
    'BRL' => 'BRL',
];

$defaultCurrency = 'USD';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['currency'])) {
    $chosenCurrency = $_POST['currency'];
    if (array_key_exists($chosenCurrency, $supportedCurrencies)) {
        $_SESSION['currency'] = $chosenCurrency;
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

$currency = $_SESSION['currency'] ?? $defaultCurrency;
?>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/vendor/analytics.php'; ?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<nav class="bg-gray-800 fixed top-0 left-0 right-0 h-16 flex items-center px-6 shadow-md z-40">
  <button id="menu-toggle" class="text-white mr-4 md:hidden focus:outline-none" aria-label="Toggle sidebar">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
  </button>
  <div class="text-2xl font-bold tracking-wide text-white"><?= htmlspecialchars($appName) ?></div>
</nav>

<div class="h-16"></div>

<aside id="sidebar" class="fixed top-16 left-0 bottom-0 w-64 bg-gray-800 shadow-lg flex flex-col p-6 space-y-4 overflow-y-auto transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-30">
  <div>
    <button class="w-full flex justify-between items-center text-white font-semibold mb-2 focus:outline-none">
      <span>Main</span>
      <svg class="w-4 h-4 transition-transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>
    <div class="space-y-1 ml-2">
      <a href="/dashboard/me/" class="block px-4 py-2 rounded hover:bg-indigo-600 transition-colors <?= isActive('/dashboard/me/', $currentPath) ? 'bg-indigo-700 font-semibold' : '' ?>"><i class="fa fa-home mr-2"></i>Dashboard</a>
    </div>
  </div>

  <div>
    <button class="w-full flex justify-between items-center text-white font-semibold mb-2 focus:outline-none">
      <span>Store</span>
      <svg class="w-4 h-4 transition-transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>
    <div class="space-y-1 ml-2">
      <a href="/dashboard/me/licenses" class="block px-4 py-2 rounded hover:bg-indigo-600 transition-colors <?= isActive('/dashboard/me/licenses', $currentPath) ? 'bg-indigo-700 font-semibold' : '' ?>"><i class="fa fa-key mr-2"></i>Licenses</a>
      <a href="/dashboard/me/purchases" class="block px-4 py-2 rounded hover:bg-indigo-600 transition-colors <?= isActive('/dashboard/me/purchases', $currentPath) ? 'bg-indigo-700 font-semibold' : '' ?>"><i class="fa fa-shopping-cart mr-2"></i>Purchases</a>
      <a href="/dashboard/me/invoices" class="block px-4 py-2 rounded hover:bg-indigo-600 transition-colors <?= ($currentPath === '/dashboard/me/invoices' || preg_match('#^/invoices/\w+#', $currentPath) || preg_match('#^/invoices/pay/\w+#', $currentPath)) ? 'bg-indigo-700 font-semibold' : '' ?>"><i class="fa fa-file-invoice-dollar mr-2"></i>Invoices</a>
    </div>
  </div>

  <div>
    <button class="w-full flex justify-between items-center text-white font-semibold mb-2 focus:outline-none">
      <span>Account</span>
      <svg class="w-4 h-4 transition-transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>
    <div class="space-y-1 ml-2">
      <a href="/dashboard/me/account-settings" class="block px-4 py-2 rounded hover:bg-indigo-600 transition-colors <?= isActive('/dashboard/me/account-settings', $currentPath) ? 'bg-indigo-700 font-semibold' : '' ?>"><i class="fa fa-cog mr-2"></i>Settings</a>
    </div>
  </div>

  <div class="h-[650px]"></div>
  <form method="post" class="text-white">
    <label for="currency" class="mr-2 whitespace-nowrap">Currency:</label>
    <select name="currency" onchange="this.form.submit()" class="bg-gray-700 text-white rounded px-2 py-1 focus:outline-none">
      <?php foreach ($supportedCurrencies as $code => $label): ?>
        <option value="<?= htmlspecialchars($code) ?>" <?= ($currency === $code) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
  <a href="/" class="block px-4 py-2 bg-indigo-600 rounded hover:bg-indigo-600 transition-colors"><i class="fa fa-store mr-2"></i>Store</a>
  <a href="/logout.php" class="px-4 py-2 bg-red-600 rounded hover:bg-red-700 transition-colors font-semibold"><i class="fa fa-sign-out-alt mr-2"></i>Log Out</a>
</aside>

<script src="/includes/js/sidebar.js"></script>