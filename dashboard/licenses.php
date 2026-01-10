<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/dashboard/licenses/licenses.php'; ?>

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
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/dashboard/navbar.php'; ?>

    <div class="flex flex-1">
        <main class="flex-1 p-8 md:ml-64">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-4xl font-bold">Licenses</h1>
                <button id="open-create-btn" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded">Create New License</button>
            </div>

            <div class="bg-gray-800 p-6 rounded shadow-lg mb-8">
                <h2 class="text-2xl font-semibold mb-4">All Licenses</h2>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto border-collapse">
                        <thead>
                            <tr class="bg-gray-700">
                                <th class="px-4 py-2 border">ID</th>
                                <th class="px-4 py-2 border">Owner</th>
                                <th class="px-4 py-2 border">Domain/IP</th>
                                <th class="px-4 py-2 border">License Key</th>
                                <th class="px-4 py-2 border">Product</th>
                                <th class="px-4 py-2 border">Status</th>
                                <th class="px-4 py-2 border">Created At</th>
                                <th class="px-4 py-2 border">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($licenses as $license): ?>
                            <tr class="bg-gray-800 hover:bg-gray-700">
                                <td class="px-4 py-2 border"><?= htmlspecialchars($license['id']) ?></td>
                                <td class="px-4 py-2 border"><?= htmlspecialchars($license['user_name']) ?></td>
                                <td class="px-4 py-2 border font-mono"><?= htmlspecialchars(trim($license['domain_or_ip'] ?? '') !== '' ? $license['domain_or_ip'] : 'None') ?></td>
                                <td class="px-4 py-2 border font-mono"><?= htmlspecialchars($license['license_key']) ?></td>
                                <td class="px-4 py-2 border"><?= htmlspecialchars($license['product_name'] ?? 'None') ?></td>
                                <td class="px-4 py-2 border"><?= htmlspecialchars(ucfirst($license['status'])) ?></td>
                                <td class="px-4 py-2 border"><?= htmlspecialchars($license['created_at']) ?></td>
                                <td class="px-4 py-2 border space-x-2">
                                    <button
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded edit-btn"
                                        data-id="<?= htmlspecialchars($license['id']) ?>"
                                        data-owner="<?= htmlspecialchars($license['user_id']) ?>"
                                        data-domain-or-ip="<?= htmlspecialchars($license['domain_or_ip'] ?? '') ?>"
                                        data-product-id="<?= htmlspecialchars($license['product_id'] ?? 'none') ?>"
                                        data-status="<?= htmlspecialchars($license['status']) ?>"
                                    >Edit</button>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="rotate" />
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($license['id']) ?>" />
                                        <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded">Rotate Key</button>
                                    </form>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this license?');">
                                        <input type="hidden" name="action" value="delete" />
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($license['id']) ?>" />
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($licenses)): ?>
                            <tr>
                                <td colspan="8" class="px-4 py-2 border text-center">No licenses found.</td>
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

    <div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-xl font-semibold mb-4 text-gray-100">Edit License</h3>
            <form method="POST" id="edit-form" class="space-y-4">
                <input type="hidden" name="action" value="edit" />
                <input type="hidden" name="id" id="edit-id" />
                <div>
                    <label for="edit-owner" class="block mb-1">Owner</label>
                    <select id="edit-owner" name="owner" class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php foreach ($users as $user): ?>
                          <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                   <label for="edit-domain-or-ip" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Domain/IP</label>
                   <input type="text" id="edit-domain-or-ip" name="domain_or_ip" value="<?= $license['domain_or_ip'] ?>" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                   <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank for no domain/ip lock</p>
                </div>
                <div>
                    <label for="edit-product" class="block mb-1">Product (Optional)</label>
                    <select id="edit-product" name="product_id" class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="none">None</option>
                        <?php foreach ($products as $product): ?>
                          <option value="<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="edit-status" class="block mb-1">Status</label>
                    <select id="edit-status" name="status" class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>"><?= ucfirst(htmlspecialchars($s)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="edit-cancel" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div id="create-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-xl font-semibold mb-4 text-gray-100">Create New License</h3>
            <form method="POST" id="create-form" class="space-y-4">
                <input type="hidden" name="action" value="create" />
                <div>
                    <label for="create-owner" class="block mb-1">Owner</label>
                    <select id="create-owner" name="owner" class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php foreach ($users as $user): ?>
                          <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                   <label for="create-domain-or-ip" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Domain/IP</label>
                   <input type="text" name="domain_or_ip" id="domain-or-ip" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                   <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank for no domain/ip lock</p>
                </div>
                <div>
                    <label for="create-product" class="block mb-1">Product (Optional)</label>
                    <select id="create-product" name="product_id" class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="none">None</option>
                        <?php foreach ($products as $product): ?>
                          <option value="<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="create-status" class="block mb-1">Status</label>
                    <select id="create-status" name="status" class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>" <?= $s === 'valid' ? 'selected' : '' ?>><?= ucfirst(htmlspecialchars($s)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="create-cancel" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Create License</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/includes/dashboard/licenses/modals.js"></script>

</body>
</html>