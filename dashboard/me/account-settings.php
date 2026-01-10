<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/dashboard/me/account.php';
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/x-icon" href="<?=$meta['favicon'] ?? ''?>" />
    <title><?=$meta['title'] ?? 'Account Settings'?></title>
    <meta name="description" content="<?=$meta['description'] ?? ''?>" />
    <meta name="keywords" content="<?=$meta['keywords'] ?? ''?>" />

    <meta name="twitter:card" content="<?=$meta['twitter']['card'] ?? ''?>" />
    <meta name="twitter:site" content="<?=$meta['twitter']['site'] ?? ''?>" />
    <meta name="twitter:title" content="<?=$meta['twitter']['title'] ?? ''?>" />
    <meta name="twitter:description" content="<?=$meta['twitter']['description'] ?? ''?>" />
    <meta name="twitter:image" content="<?=$meta['twitter']['image'] ?? ''?>" />

    <meta property="og:type" content="<?=$meta['opengraph']['type'] ?? ''?>" />
    <meta property="og:url" content="<?=$meta['opengraph']['url'] ?? ''?>" />
    <meta property="og:title" content="<?=$meta['opengraph']['title'] ?? ''?>" />
    <meta property="og:description" content="<?=$meta['opengraph']['description'] ?? ''?>" />
    <meta property="og:site_name" content="<?=$meta['opengraph']['site_name'] ?? ''?>" />
    <meta property="og:image" content="<?=$meta['opengraph']['image']['url'] ?? ''?>" />
    <meta property="og:image:type" content="<?=$meta['opengraph']['image']['type'] ?? ''?>" />
    <meta property="og:image:width" content="<?=$meta['opengraph']['image']['width'] ?? ''?>" />
    <meta property="og:image:height" content="<?=$meta['opengraph']['image']['height'] ?? ''?>" />

    <link href="/includes/css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex flex-col">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/dashboard/me/navbar.php'; ?>
<div class="flex flex-1">
    <main class="flex-1 p-8 md:ml-64 max-w-5xl mx-auto">
        <h1 class="text-4xl font-bold mb-6">Account Settings</h1>

        <div class="mb-6 flex space-x-4 border-b border-gray-700 pb-2">
            <a href="?tab=general" class="<?=$activeTab === 'general' ? 'text-white border-b-2 border-indigo-500' : 'text-gray-400 hover:text-white'?> px-3 py-1">General</a>
            <a href="?tab=connections" class="<?=$activeTab === 'connections' ? 'text-white border-b-2 border-indigo-500' : 'text-gray-400 hover:text-white'?> px-3 py-1">Connections</a>
            <a href="?tab=security" class="<?=$activeTab === 'security' ? 'text-white border-b-2 border-indigo-500' : 'text-gray-400 hover:text-white'?> px-3 py-1">Security</a>
        </div>

        <?php if ($activeTab === 'general'): ?>
        <div class="bg-gray-800 p-6 rounded shadow mb-8">
            <?php if (isset($_GET['updated'])): ?>
                <div class="mb-4 p-3 bg-green-600 rounded">Your changes have been saved.</div>
            <?php endif; ?>
            <form method="POST" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300">Name</label>
                    <input type="text" name="name" id="name" value="<?=$user['name']?>" required class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded px-3 py-2">
                </div>
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-300">Username</label>
                    <input type="text" name="username" id="username" value="<?=$user['username']?>" required class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded px-3 py-2">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300">Email</label>
                    <input type="email" name="email" id="email" value="<?=$user['email']?>" required class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded px-3 py-2">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300">Password <span class="text-gray-400">(leave blank to keep unchanged)</span></label>
                    <input type="password" name="password" id="password" placeholder="New password (optional)" class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded px-3 py-2">
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded">Save Changes</button>
            </form>
        </div>
        <?php endif; ?>

        <?php if ($activeTab === 'connections'): ?>
        <div class="bg-gray-800 p-6 rounded shadow grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($socialConnections as $key => $conn): ?>
                <div class="flex items-center bg-gray-700 rounded p-4 space-x-4">
                    <img src="<?=$conn['logo']?>" alt="<?=$conn['displayName']?> logo" class="w-7 h-6">
                    <div class="flex-1">
                        <p class="text-lg font-semibold"><?=$conn['displayName']?></p>
                        <?php if ($conn['connected']): ?>
                            <p class="text-sm text-green-400 font-semibold">Connected</p>
                        <?php elseif ($conn['enabled'] == 1): ?>
                            <p class="text-sm text-gray-300">Not connected yet</p>
                        <?php else: ?>
                            <p class="text-sm text-gray-300">This provider is currently disabled by an admin.</p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <?php if ($conn['connected']): ?>
                            <form method="POST">
                                <input type="hidden" name="provider" value="<?=$key?>">
                                <input type="hidden" name="disconnect_provider" value="1">
                                <button type="submit" class="inline-block bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded">Disconnect</button>
                            </form>
                        <?php else: ?>
                            <?php if ($conn['enabled'] == 1): ?>
                                <a href="<?=$conn['loginUrl']?>" class="inline-block bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded">Connect</a>
                            <?php else: ?>
                                <button disabled class="inline-block bg-gray-600 cursor-not-allowed text-gray-400 px-4 py-2 rounded">Connect</button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($activeTab === 'security'): ?>
        <div class="bg-gray-800 p-6 rounded shadow">
            <h2 class="text-2xl font-semibold mb-4">Two-Factor Authentication (2FA)</h2>
            <?php if (!empty($error)): ?>
                <div class="mb-4 p-3 bg-red-600 rounded"><?=$error?></div>
            <?php endif; ?>
            <?php if (empty($user['two_factor_enabled'])): ?>
                <?php if (!empty($user['two_factor_secret'])): ?>
                    <div class="mb-4 p-4 bg-gray-700 rounded">
                        <?php
                        $issuer = $appName;
                        $provisioningUri = getProvisioningUri($user['username'], $issuer, $user['two_factor_secret']);
                        $qrUrl = getQrCodeUrl($provisioningUri);
                        ?>
                        <img src="<?=$qrUrl?>" alt="QR Code" class="mb-4 w-40 h-40" />
                        <code class="block mb-4 text-green-400"><?=$user['two_factor_secret']?></code>
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="toggle_2fa" value="enable" />
                            <label for="2fa_code" class="text-sm">Enter the code:</label>
                            <input type="text" name="2fa_code" id="2fa_code" required class="block w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                            <div class="flex gap-4">
                                <button type="submit" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded">Enable 2FA</button>
                            </form>
                            <form method="POST" class="ml-2">
                                <input type="hidden" name="toggle_2fa" value="disable" />
                                <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded">Cancel</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="toggle_2fa" value="enable" />
                        <button type="submit" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded">Setup 2FA</button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <div class="mb-4 p-4 bg-gray-700 rounded">
                    <p class="mb-2 text-green-400 font-semibold">Two-Factor Authentication is enabled</p>
                    <form method="POST">
                        <input type="hidden" name="toggle_2fa" value="disable" />
                        <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded">Disable 2FA</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>