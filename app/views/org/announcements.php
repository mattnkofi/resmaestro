<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$announcements = $announcements ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcement Management - Maestro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .maestro-bg { background-color: #0b0f0c; } 
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: { colors: { 'sidebar-dark': '#0f1511', 'maestro-bg': '#0b0f0c' } }
            }
        }
    </script>
</head>
<body class="bg-maestro-bg text-white font-poppins" 
    x-data="{ 
        BASE_URL: '<?= BASE_URL ?>',
        isModalOpen: false, 
        confirmDelete: { id: 0, title: '' }
    }">

    <?php 
        $current_uri = $_SERVER['REQUEST_URI'] ?? '/org/announcements'; 
        // Logic to include sidebar here or ensure it's loaded by a wrapper layout
        include 'sidebar.php'; 
    ?>

    <div class="ml-64 p-8 bg-maestro-bg min-h-screen">
        <h1 class="text-3xl font-bold text-yellow-400 mb-6 tracking-wide">
            Organization Announcements
        </h1>
        <p class="text-gray-400 mb-6">Manage all public announcements displayed on the dashboard.</p>

        <?php if (function_exists('flash_alert')) flash_alert(); ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Create New Announcement -->
            <div class="lg:col-span-1">
                <div class="bg-green-950/50 p-6 rounded-xl space-y-4 border border-green-800 shadow-2xl shadow-green-900/10 sticky top-4">
                    <h2 class="text-xl font-bold text-green-300 mb-4">
                        <i class="fa-solid fa-bullhorn mr-2"></i> Post New Announcement
                    </h2>

                    <form method="POST" action="<?= BASE_URL ?>/org/announcements/store" class="space-y-4">
                        <?php csrf_field(); ?>

                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-400">Title</label>
                            <input type="text" id="title" name="title" required maxlength="255"
                                class="w-full p-2 bg-green-900 border border-green-800 rounded-lg text-white">
                        </div>

                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-400">Content</label>
                            <textarea id="content" name="content" rows="4" required
                                class="w-full p-2 bg-green-900 border border-green-800 rounded-lg text-white"></textarea>
                        </div>
                        
                        <div>
                            <label for="expires_at" class="block text-sm font-medium text-gray-400">Expiration Date (Optional)</label>
                            <input type="date" id="expires_at" name="expires_at"
                                class="w-full p-2 bg-green-900 border border-green-800 rounded-lg text-white">
                        </div>

                        <div class="flex items-center space-x-3">
                            <input type="checkbox" id="is_active" name="is_active" checked
                                class="h-4 w-4 text-green-600 bg-gray-900 border-gray-700 rounded focus:ring-green-500">
                            <label for="is_active" class="text-sm font-medium text-green-300">Publish Immediately</label>
                        </div>
                        
                        <button type="submit" class="w-full bg-green-700 px-6 py-3 rounded-xl hover:bg-green-600 font-bold transition shadow-lg">
                            <i class="fa-solid fa-paper-plane mr-2"></i> Post Announcement
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column: List Existing Announcements -->
            <div class="lg:col-span-2">
                <h2 class="text-xl font-semibold text-green-300 mb-4 border-b border-green-800/50 pb-2">
                    Existing Announcements (<?= count($announcements) ?>)
                </h2>

                <div class="space-y-4">
                    <?php if (empty($announcements)): ?>
                        <div class="p-6 text-center text-gray-500 bg-green-950/20 rounded-xl border border-green-800">
                            <i class="fa-solid fa-bell-slash text-4xl mb-3 text-yellow-500"></i>
                            <p class="text-lg">No announcements currently posted.</p>
                        </div>
                    <?php else: 
                        foreach ($announcements as $announcement):
                            $is_active = $announcement['is_active'] == 1 && (empty($announcement['expires_at']) || strtotime($announcement['expires_at']) > time());
                            $badge_class = $is_active ? 'bg-green-700' : 'bg-gray-700';
                            $badge_text = $is_active ? 'Active' : 'Inactive / Expired';
                            $created_by = htmlspecialchars($announcement['fname'] . ' ' . $announcement['lname']);
                    ?>
                    <div class="bg-green-950/50 p-4 rounded-xl border-l-4 border-green-500 shadow-lg flex justify-between items-start">
                        <div class="w-full pr-4">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="text-lg font-bold text-white"><?= htmlspecialchars($announcement['title']) ?></h3>
                                <span class="text-xs font-semibold py-1 px-3 rounded-full <?= $badge_class ?>"><?= $badge_text ?></span>
                            </div>
                            <p class="text-sm text-gray-300 mb-2 whitespace-pre-wrap"><?= htmlspecialchars($announcement['content']) ?></p>
                            <p class="text-xs text-gray-500">
                                Posted by: <?= $created_by ?> | On: <?= date('M d, Y', strtotime($announcement['created_at'])) ?>
                            </p>
                        </div>
                        <div class="flex flex-col space-y-2">
                            <!-- Delete Button -->
                            <button @click="confirmDelete.id = <?= $announcement['id'] ?>; confirmDelete.title = '<?= htmlspecialchars($announcement['title'], ENT_QUOTES) ?>'; isModalOpen = true;"
                                class="text-red-400 hover:text-red-300 text-sm p-1">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            <!-- NOTE: Edit functionality is complex and omitted for brevity, focusing on core feature -->
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="isModalOpen" x-cloak x-transition.opacity class="fixed inset-0 bg-black bg-opacity-75 z-50 flex justify-center items-center p-4">
        <div x-show="isModalOpen" x-transition.scale.duration.300ms @click.outside="isModalOpen = false" class="bg-green-950/95 border border-red-700 rounded-xl p-6 w-full max-w-sm shadow-2xl text-center">
            <i class="fa-solid fa-triangle-exclamation text-4xl text-red-500 mb-4"></i>
            <h3 class="text-xl font-bold text-white mb-2">Confirm Deletion</h3>
            <p class="text-gray-300 mb-4">Are you sure you want to delete the announcement: <strong x-text="confirmDelete.title"></strong>?</p>
            <form method="POST" :action="BASE_URL + '/org/announcements/delete'" class="flex justify-center gap-4 mt-4">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" :value="confirmDelete.id">
                <input type="hidden" name="title" :value="confirmDelete.title">
                <button type="button" @click="isModalOpen = false" class="bg-gray-600 hover:bg-gray-500 px-4 py-2 rounded-lg text-white font-semibold transition">Cancel</button>
                <button type="submit" class="bg-red-700 px-4 py-2 rounded-lg hover:bg-red-600 font-bold text-white transition">Delete</button>
            </form>
        </div>
    </div>
</body>
</html>