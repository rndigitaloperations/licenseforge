<?php
include $_SERVER['DOCUMENT_ROOT'] . '/includes/dashboard/categorys/categorys.php';
?>

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
                <h1 class="text-4xl font-bold">Categorys</h1>
                <button id="open-create-btn" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded">Create New Categorys</button>
            </div>

            <div class="bg-gray-800 p-6 rounded shadow-lg mb-8">
                <h2 class="text-2xl font-semibold mb-4">All Categorys</h2>
                <div class="overflow-x-auto">
                    <table id="category-table" class="w-full table-auto border-collapse">
                        <thead>
                            <tr class="bg-gray-700 cursor-move">
                                <th class="px-4 py-2 border w-10"></th>
                                <th class="px-4 py-2 border">ID</th>
                                <th class="px-4 py-2 border">Name</th>
                                <th class="px-4 py-2 border">Description</th>
                                <th class="px-4 py-2 border">Created At</th>
                                <th class="px-4 py-2 border">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorys as $category): ?>
                            <tr class="bg-gray-800 hover:bg-gray-700" data-id="<?= htmlspecialchars($category['id']) ?>">
                                <td class="px-4 py-2 border cursor-move drag-handle" title="Drag to reorder">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 hover:text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16" />
                                 </svg>
                                </td>
                                <td class="px-4 py-2 border"><?= htmlspecialchars($category['id']) ?></td>
                                <td class="px-4 py-2 border"><?= htmlspecialchars($category['name']) ?></td>
                                <td class="px-4 py-2 border"><?= $Parsedown->text($category['description']) ?></td>
                                <td class="px-4 py-2 border"><?= htmlspecialchars($category['created_at']) ?></td>
                                <td class="px-4 py-2 border space-x-2">
                                    <button 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded edit-btn" 
                                        data-id="<?= htmlspecialchars($category['id']) ?>"
                                        data-image_url="<?= htmlspecialchars($category['image_url']) ?>"
                                        data-name="<?= htmlspecialchars($category['name']) ?>"
                                        data-description="<?= htmlspecialchars($category['description']) ?>"
                                    >Edit</button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="action" value="delete" />
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($category['id']) ?>" />
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($categorys)): ?>
                            <tr>
                                <td colspan="6" class="px-4 py-2 border text-center">No categorys found.</td>
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
                <button id="save-order" class="mt-4 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded hidden">Save Order</button>
            </div>
        </main>
    </div>

    <div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-xl font-semibold mb-4 text-gray-100">Edit Category</h3>
            <form method="POST" id="edit-form" class="space-y-4">
                <input type="hidden" name="action" value="edit" />
                <input type="hidden" name="id" id="edit-id" />
                <div>
                    <label for="edit-image_url" class="block mb-1">Image URL</label>
                    <input type="text" id="edit-image_url" name="image_url" required class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                    <label for="edit-name" class="block mb-1">Name</label>
                    <input type="text" id="edit-name" name="name" required class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                    <label for="edit-description" class="block mb-1">Description</label>
                    <textarea id="edit-description" name="description" required class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
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
            <h3 class="text-xl font-semibold mb-4 text-gray-100">Create New Category</h3>
            <form method="POST" id="create-form" class="space-y-4">
                <input type="hidden" name="action" value="create" />
                <div>
                    <label for="create-image_url" class="block mb-1">Image URL</label>
                    <input type="text" id="create-image_url" name="image_url" required class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                    <label for="create-name" class="block mb-1">Name</label>
                    <input type="text" id="create-name" name="name" required class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                    <label for="create-description" class="block mb-1">Description</label>
                    <textarea id="create-description" name="description" required class="w-full px-3 py-2 bg-gray-700 rounded text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="create-cancel" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Create Category</button>
                </div>
            </form>
        </div>
    </div>
    <script src="/includes/dashboard/categorys/reorder.js"></script>
    <script src="/includes/dashboard/categorys/modals.js"></script>

</body>
</html>