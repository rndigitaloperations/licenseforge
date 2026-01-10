<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/dashboard/users/users.php'; ?>

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
            <h1 class="text-4xl font-bold">Users</h1>
            <button id="open-create-btn" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded">Create New User</button>
        </div>

        <div class="bg-gray-800 p-6 rounded shadow-lg mb-8">
            <h2 class="text-2xl font-semibold mb-4">All Users</h2>
            <div class="overflow-x-auto">
                <table class="w-full table-auto border-collapse">
                    <thead>
                        <tr class="bg-gray-700">
                            <th class="px-4 py-2 border">ID</th>
                            <th class="px-4 py-2 border">Name</th>
                            <th class="px-4 py-2 border">Username</th>
                            <th class="px-4 py-2 border">Email</th>
                            <th class="px-4 py-2 border">Role</th>
                            <th class="px-4 py-2 border">Protected</th>
                            <th class="px-4 py-2 border">Created At</th>
                            <th class="px-4 py-2 border">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr class="bg-gray-800 hover:bg-gray-700">
                            <td class="px-4 py-2 border"><?= htmlspecialchars($user['id']) ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($user['name']) ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($user['username']) ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($user['role']) ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($user['protected'] == 1 ? 'Yes' : 'No') ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($user['created_at']) ?></td>
                            <td class="px-4 py-2 border space-x-2">
                                <?php if ((int)$user['protected'] !== 1): ?>
                                <div style="display: flex; gap: 0.5rem;">
                                 <button 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded edit-btn" 
                                    data-id="<?= htmlspecialchars($user['id']) ?>"
                                    data-name="<?= htmlspecialchars($user['name']) ?>"
                                    data-username="<?= htmlspecialchars($user['username']) ?>"
                                    data-email="<?= htmlspecialchars($user['email']) ?>"
                                    data-role="<?= htmlspecialchars($user['role']) ?>"
                                >Edit</button>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">Delete</button>
                                </form>
                              </div>
                            <?php else: ?>
                                <button class="bg-blue-600 text-white px-3 py-1 rounded cursor-not-allowed edit-btn" disabled>Edit</button>
                                <button class="bg-red-600 text-white px-3 py-1 rounded cursor-not-allowed" disabled>Delete</button>
                            <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-2 border text-center">No users found.</td>
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
        <h3 class="text-xl font-semibold mb-4 text-gray-100">Edit User</h3>
        <form method="POST" id="edit-form" class="space-y-4">
            <input type="hidden" name="action" value="edit" />
            <input type="hidden" name="id" id="edit-id" />
            <div>
                <label for="edit-name" class="block mb-1">Name</label>
                <input type="text" id="edit-name" name="name" required class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100" />
            </div>
            <div>
                <label for="edit-username" class="block mb-1">Username</label>
                <input type="text" id="edit-username" name="username" required class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100" />
            </div>
            <div>
                <label for="edit-email" class="block mb-1">Email</label>
                <input type="email" id="edit-email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100" />
            </div>
            <div>
                <label for="edit-password" class="block mb-1">Password (leave blank to keep unchanged)</label>
                <input type="password" id="edit-password" name="password" class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100" />
            </div>
            <div>
                <label for="edit-role" class="block mb-1">Role</label>
                <select id="edit-role" name="role" class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
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
        <h3 class="text-xl font-semibold mb-4 text-gray-100">Create New User</h3>
        <form method="POST" id="create-form" class="space-y-4">
            <input type="hidden" name="action" value="create" />
            <div>
                <label for="create-name" class="block mb-1">Name</label>
                <input type="text" id="create-name" name="name" required class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100" />
            </div>
            <div>
                <label for="create-username" class="block mb-1">Username</label>
                <input type="text" id="create-username" name="username" required class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100" />
            </div>
            <div>
                <label for="create-email" class="block mb-1">Email</label>
                <input type="email" id="create-email" name="email" required class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100" />
            </div>
            <div>
                <label for="create-password" class="block mb-1">Password</label>
                <input type="password" id="create-password" name="password" required class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100" />
            </div>
            <div>
                <label for="create-role" class="block mb-1">Role</label>
                <select id="create-role" name="role" class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100">
                    <option value="user" selected>User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" id="create-cancel" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Cancel</button>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Create User</button>
            </div>
        </form>
    </div>
</div>

<script src="/includes/dashboard/users/modals.js"></script>

</body>
</html>