<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/dashboard/settings.php';
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($meta['favicon']) ?>" />
    <title><?= htmlspecialchars($meta['title']) ?> - Settings</title>
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
    
    <link rel="stylesheet" href="../includes/css/toggle.css">
    <link href="/includes/css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex flex-col">

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/dashboard/navbar.php'; ?>

    <div class="flex flex-1">
        <main class="flex-1 p-8 md:ml-64">
            <?php if ($success): ?>
            <div id="success-message" class="bg-green-600 text-white px-4 py-2 rounded">
                <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>
            <div class="flex justify-between items-center mb-4">
              <h1 class="text-4xl font-bold">Settings</h1>
            </div>
            <div class="tabs">
                <ul class="tab-list flex border-b border-gray-700">
                    <li class="tab active" data-tab="payments">Payments</li>
                    <li class="tab" data-tab="licenses">Licenses</li>
                    <li class="tab" data-tab="analytics">Analytics</li>
                    <li class="tab" data-tab="recaptcha">Recaptcha</li>
                    <li class="tab" data-tab="emails">Emails</li>
                    <li class="tab" data-tab="login">Login</li>
                    <li class="tab" data-tab="app">App</li>
                </ul>
                </div>
                <div class="tab-content">
                    <div id="payments" class="tab-pane active">
                        <form method="POST" class="space-y-8 bg-gray-800 p-6 rounded shadow-lg">
                            <input type="hidden" name="action" value="save_payments" />
                            <div class="bg-gray-700 p-5 rounded-lg flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <img src="/images/stripe.png" alt="Stripe" class="h-8 w-8" />
                                        <h3 class="text-xl font-bold">Stripe</h3>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="stripe_enabled" <?= (!empty($paymentConfigs['stripe']['enabled']) ? 'checked' : '') ?> />
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="password-wrapper">
                                    <p class="font-bold">Client ID</p>
                                    <input type="password" name="stripe_client_id" placeholder="Client ID" value="<?= htmlspecialchars($paymentConfigs['stripe']['client_id'] ?? '') ?>" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" autocomplete="new-password" />
                                    <span class="toggle-password" data-target="stripe_client_id">👁️</span>
                                </div>
                                <div class="password-wrapper">
                                    <p class="font-bold">Client Secret</p>
                                    <input type="password" name="stripe_client_secret" placeholder="Client Secret" value="<?= htmlspecialchars($paymentConfigs['stripe']['client_secret'] ?? '') ?>" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" autocomplete="new-password" />
                                    <span class="toggle-password" data-target="stripe_client_secret">👁️</span>
                                </div>
                            </div>
                            <div class="bg-gray-700 p-5 rounded-lg flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <img src="/images/paypal.webp" alt="PayPal" class="h-8 w-8" />
                                        <h3 class="text-xl font-bold">PayPal</h3>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="paypal_enabled" <?= (!empty($paymentConfigs['paypal']['enabled']) ? 'checked' : '') ?> />
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="password-wrapper">
                                    <p class="font-bold">Client ID</p>
                                    <input type="password" name="paypal_client_id" placeholder="Client ID" value="<?= htmlspecialchars($paymentConfigs['paypal']['client_id'] ?? '') ?>" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" autocomplete="new-password" />
                                    <span class="toggle-password" data-target="paypal_client_id">👁️</span>
                                </div>
                                <div class="password-wrapper">
                                    <p class="font-bold">Client Secret</p>
                                    <input type="password" name="paypal_client_secret" placeholder="Client Secret" value="<?= htmlspecialchars($paymentConfigs['paypal']['client_secret'] ?? '') ?>" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" autocomplete="new-password" />
                                    <span class="toggle-password" data-target="paypal_client_secret">👁️</span>
                                </div>
                                <div class="password-wrapper">
                                    <p class="font-bold">Sandbox Mode</p>
                                    <label class="switch">
                                        <input type="checkbox" name="paypal_sandbox_enabled" <?= (!empty($paymentConfigs['paypal']['sandbox']) ? 'checked' : '') ?> />
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="mt-4 bg-amber-100 text-amber-800 dark:bg-yellow-900 dark:text-yellow-100 border-l-4 border-amber-500 p-4 rounded flex items-start gap-2 text-sm">
                                <span class="text-xl">⚠️</span>
                                <div>
                                    <strong>Note:</strong> PayPal sandbox mode must be enabled manually. We can't detect if the credentials are for live or sandbox like Stripe can.
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded font-semibold">Save</button>
                            </div>
                        </form>
                    </div>

                    <div id="licenses" class="tab-pane">
                        <form method="POST" class="space-y-8 bg-gray-800 p-6 rounded shadow-lg">
                            <input type="hidden" name="action" value="save_license_settings" />
                            <div class="bg-gray-700 p-5 rounded-lg flex flex-col gap-4">
                                <div class="flex flex-col gap-4">
                                    <div class="password-wrapper">
                                        <p class="font-bold">License Key Prefix</p>
                                        <input type="text" name="license_key_prefix" placeholder="License Key Prefix" value="<?= htmlspecialchars($licenseConfigs['license_key_prefix'] ?? '') ?>" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" />
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank for no prefix</p>
                                    </div>
                                    <div class="password-wrapper">
                                        <p class="font-bold">License Key Length</p>
                                        <input type="number" name="license_key_length" placeholder="License Key Length" value="<?= htmlspecialchars(isset($license['license_key_length']) && $license['license_key_length'] !== '' ? $license['license_key_length'] : '16') ?>" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" required />
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded font-semibold">Save</button>
                            </div>
                        </form>
                    </div>

                    <div id="analytics" class="tab-pane active">
                        <form method="POST" class="space-y-8 bg-gray-800 p-6 rounded shadow-lg">
                            <input type="hidden" name="action" value="save_analytics_settings" />
                            <div class="bg-gray-700 p-5 rounded-lg flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <img src="/images/GAnalytics.png" alt="Google" class="h-8 w-8" />
                                        <h3 class="text-xl font-bold">Google Analytics</h3>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="google_enabled" <?= (!empty($analyticsConfigs['google']['enabled']) ? 'checked' : '') ?> />
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="password-wrapper">
                                    <p class="font-bold">Measurement ID</p>
                                    <input type="password" name="google_measurement_id" placeholder="Measurement ID" value="<?= htmlspecialchars($analyticsConfigs['google']['measurement_id'] ?? '') ?>" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" autocomplete="new-password" />
                                    <span class="toggle-password" data-target="google_measurement_id">👁️</span>
                                </div>
                            </div>
                            <div class="bg-gray-700 p-5 rounded-lg flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <img src="/images/Cloudflare.png" alt="Cloudflare" class="h-8 w-8" />
                                        <h3 class="text-xl font-bold">Cloudflare Analytics</h3>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="cloudflare_enabled" <?= (!empty($analyticsConfigs['cloudflare']['enabled']) ? 'checked' : '') ?> />
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="password-wrapper">
                                    <p class="font-bold">Token</p>
                                    <input type="password" name="cloudflare_measurement_id" placeholder="Token" value="<?= htmlspecialchars($analyticsConfigs['cloudflare']['measurement_id'] ?? '') ?>" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" autocomplete="new-password" />
                                    <span class="toggle-password" data-target="cloudflare_measurement_id">👁️</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded font-semibold">Save</button>
                            </div>
                        </form>
                    </div>

                    <div id="recaptcha" class="tab-pane active">
                        <form method="POST" class="space-y-8 bg-gray-800 p-6 rounded shadow-lg">
                            <input type="hidden" name="action" value="save_recaptcha_settings" />
                            <div class="bg-gray-700 p-5 rounded-lg flex flex-col gap-4">
                                <label for="recaptcha_provider" class="font-bold">Select Provider</label>
                                <div class="flex items-center justify-between">
                                    <select name="recaptcha_provider" id="recaptcha_provider" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600 text-gray-100">
                                        <option value="disabled" <?= (!empty($recaptchaConfigs['recaptcha_provider']) && $recaptchaConfigs['recaptcha_provider'] === 'disabled') ? 'selected' : '' ?>>Disabled</option>
                                        <option value="recaptcha_v2" <?= (!empty($recaptchaConfigs['recaptcha_provider']) && $recaptchaConfigs['recaptcha_provider'] === 'recaptcha_v2') ? 'selected' : '' ?>>Recaptcha v2</option>
                                        <option value="recaptcha_v3" <?= (!empty($recaptchaConfigs['recaptcha_provider']) && $recaptchaConfigs['recaptcha_provider'] === 'recaptcha_v3') ? 'selected' : '' ?>>Recaptcha v3</option>
                                        <option value="cloudflare_turnstile" <?= (!empty($recaptchaConfigs['recaptcha_provider']) && $recaptchaConfigs['recaptcha_provider'] === 'cloudflare_turnstile') ? 'selected' : '' ?>>Cloudflare Turnstile</option>
                                        <option value="hcaptcha" <?= (!empty($recaptchaConfigs['recaptcha_provider']) && $recaptchaConfigs['recaptcha_provider'] === 'hcaptcha') ? 'selected' : '' ?>>hCaptcha</option>
                                        <option value="mtcaptcha" <?= (!empty($recaptchaConfigs['recaptcha_provider']) && $recaptchaConfigs['recaptcha_provider'] === 'mtcaptcha') ? 'selected' : '' ?>>MTCaptcha</option>
                                    </select>
                                </div>
                                <div class="password-wrapper">
                                    <p class="font-bold">Site Key</p>
                                    <input type="password" name="recaptcha_site_key" placeholder="Site Key" value="<?= htmlspecialchars(isset($recaptchaConfigs['recaptcha_site_key']) && $recaptchaConfigs['recaptcha_site_key'] !== '' ? $recaptchaConfigs['recaptcha_site_key'] : '') ?>" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" autocomplete="new-password" />
                                    <span class="toggle-password" data-target="recaptcha_site_key">👁️</span>
                                </div>
                                <div class="password-wrapper">
                                    <p class="font-bold">Secret Key</p>
                                    <input type="password" name="recaptcha_secret_key" placeholder="Secret Key" value="<?= htmlspecialchars(isset($recaptchaConfigs['recaptcha_secret_key']) && $recaptchaConfigs['recaptcha_secret_key'] !== '' ? $recaptchaConfigs['recaptcha_secret_key'] : '') ?>" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" autocomplete="new-password" />
                                    <span class="toggle-password" data-target="recaptcha_secret_key">👁️</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded font-semibold">Save</button>
                            </div>
                        </form>
                    </div>

                    <div id="emails" class="tab-pane">
                        <form method="POST" class="space-y-8 bg-gray-800 p-6 rounded shadow-lg">
                            <input type="hidden" name="action" value="save_email_settings" />
                            <div class="bg-gray-700 p-5 rounded-lg flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <h3 class="text-xl font-bold">Login Detection</h3>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="login_detection_enabled" <?= (!empty($emailConfigs['login_detection_enabled']) ? 'checked' : '') ?> />
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <h3 class="text-xl font-bold">Password Reset Notifications</h3>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="password_reset_notifications_enabled" checked disabled />
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <p>Password resets are mandatory and cannot be disabled</p>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded font-semibold">Save</button>
                            </div>
                        </form>
                    </div>

                    <div id="login" class="tab-pane active">
                        <form method="POST" class="space-y-8 bg-gray-800 p-6 rounded shadow-lg">
                            <input type="hidden" name="action" value="save_login" />
                            <div class="bg-gray-700 p-5 rounded-lg flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <img src="/images/discord.png" alt="Discord" class="h-8 w-8" />
                                        <h3 class="text-xl font-bold">Discord</h3>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="discord_enabled" <?= (!empty($loginConfigs['discord']['enabled']) ? 'checked' : '') ?> />
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="password-wrapper">
                                    <p class="font-bold">Client ID</p>
                                    <input type="password" name="discord_client_id" placeholder="Client ID" value="<?= htmlspecialchars($loginConfigs['discord']['client_id'] ?? '') ?>" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" autocomplete="new-password" />
                                    <span class="toggle-password" data-target="discord_client_id">👁️</span>
                                </div>
                                <div class="password-wrapper">
                                    <p class="font-bold">Client Secret</p>
                                    <input type="password" name="discord_client_secret" placeholder="Client Secret" value="<?= htmlspecialchars($loginConfigs['discord']['client_secret'] ?? '') ?>" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" autocomplete="new-password" />
                                    <span class="toggle-password" data-target="discord_client_secret">👁️</span>
                                </div>
                            </div>
                            <div class="bg-gray-700 p-5 rounded-lg flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <img src="/images/google.png" alt="Google" class="h-8 w-8" />
                                        <h3 class="text-xl font-bold">Google</h3>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="google_enabled" <?= (!empty($loginConfigs['google']['enabled']) ? 'checked' : '') ?> />
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="password-wrapper">
                                    <p class="font-bold">Client ID</p>
                                    <input type="password" name="google_client_id" placeholder="Client ID" value="<?= htmlspecialchars($loginConfigs['google']['client_id'] ?? '') ?>" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" autocomplete="new-password" />
                                    <span class="toggle-password" data-target="google_client_id">👁️</span>
                                </div>
                                <div class="password-wrapper">
                                    <p class="font-bold">Client Secret</p>
                                    <input type="password" name="google_client_secret" placeholder="Client Secret" value="<?= htmlspecialchars($loginConfigs['google']['client_secret'] ?? '') ?>" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" autocomplete="new-password" />
                                    <span class="toggle-password" data-target="google_client_secret">👁️</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded font-semibold">Save</button>
                            </div>
                        </form>
                    </div>

                    <div id="app" class="tab-pane">
                        <form method="POST" class="space-y-8 bg-gray-800 p-6 rounded shadow-lg">
                            <input type="hidden" name="action" value="save_app_settings" />
                            <div class="bg-gray-700 p-5 rounded-lg flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <h3 class="text-xl font-bold">Configuration</h3>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-4">
                                    <div>
                                        <p class="font-bold">App Name</p>
                                        <input type="text" name="app_name" placeholder="App Name" value="<?= htmlspecialchars($appConfigs['app_name'] ?? 'LicenseForge') ?>" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" required />
                                    </div>
                                </div>
                                <div class="flex flex-col gap-4">
                                    <div>
                                        <p class="font-bold">Contact Forum Send to Email</p>
                                        <input type="text" name="contact_forum_to_email" placeholder="Contact Forum Send to Email" value="<?= htmlspecialchars($appConfigs['contact_forum_to_email'] ?? 'info@example.com') ?>" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" required />
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">You must provide the email to avoid errors in the contact forum</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-700 p-5 rounded-lg flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <h3 class="text-xl font-bold">Enable/Disable Functions</h3>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <h3 class="text-xl font-bold">Store</h3>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="store_enabled" <?= (!empty($appConfigs['store_enabled']) ? 'checked' : '') ?> />
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <h3 class="text-xl font-bold">Contact Forum</h3>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="contact_forum_enabled" <?= (!empty($appConfigs['contact_forum_enabled']) ? 'checked' : '') ?> />
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded font-semibold">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../includes/js/password-toggle.js" defer></script>
    <script src="../includes/js/settings-tabs.js" defer></script>
    <script src="../includes/js/success-error_messages.js" defer></script>

</body>
</html>