<?php
// Define BASE_URL safely in case the helper wasn't fully loaded yet
if (!defined('BASE_URL')) define('BASE_URL', '/maestro');
$BASE_URL = BASE_URL ?? '';
$current_uri = $_SERVER['REQUEST_URI'] ?? '/org/dashboard'; 

// --- FIX: Define Sidebar Link State Variables ---
$is_documents_open = str_contains($current_uri, '/org/documents/');
$is_review_open = str_contains($current_uri, '/org/review/');
$is_organization_open = str_contains($current_uri, '/org/members/') 
                        || str_contains($current_uri, '/org/departments') 
                        || str_contains($current_uri, '/org/roles');
$is_reports_open = str_contains($current_uri, '/org/reports/');
$is_announcements_open = str_contains($current_uri, '/org/announcements'); // New flag
// --- END FIX ---

// Define roles here (copied from OrgController for visibility check)
$CAN_MANAGE_ANNOUNCEMENTS = ['President', 'Adviser', 'Secretary'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Maestro</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Apply Poppins font globally */
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* Subtle background for main body for consistency with the dark theme */
        .maestro-bg {
            background-color: #0b0f0c; /* Existing sidebar color */
        }

        /* Consistent green color palette */
        .green-primary { color: #10b981; } /* Tailwind green-500 for strong accent */
        .green-secondary { color: #34d399; } /* Tailwind green-400 for text */
        .dark-bg-subtle { background-color: #0d1310; } /* Slightly darker background than sidebar for main content */

        /* Enhanced Card Style for Dashboard (similar to the first code block's 'card') */
        .dashboard-card {
            background-color: rgba(16, 185, 129, 0.05); /* Very subtle green tint */
            border: 1px solid rgba(16, 185, 129, 0.2); /* Faint green border */
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-2px); /* Subtle lift on hover */
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.1); /* Subtle green glow */
        }
    </style>
</head>
<body class="maestro-bg text-white">

    <aside class="fixed top-0 left-0 h-full w-64 bg-[#0b0f0c] border-r border-green-900 text-white shadow-2xl flex flex-col transition-all duration-300 z-10">
        <div class="flex items-center justify-center py-6 border-b border-green-800">
            <img src="/public/maestrologo.png" alt="Logo" class="h-10 mr-8">
            <h1 class="text-2xl font-bold text-green-400 tracking-wider">MAESTRO</h1>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-3 space-y-4">

            <div>
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2 ml-2 tracking-wider">Main</h2>
                <a href="<?=BASE_URL?>/org/dashboard" class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-700/50 transition
                    <?= $current_uri == BASE_URL.'/org/dashboard' ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">
                    <i class="fa-solid fa-gauge w-5 text-center"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <div x-data='{ open: <?= $is_documents_open ? 'true' : 'false' ?> }' class="space-y-1">
                <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-file-lines w-5 text-center"></i>
                        <span>Documents</span>
                    </span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                </button>
                <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?=BASE_URL?>/org/documents/all" class="block p-2 rounded hover:bg-green-700/40 transition">All Documents</a>
                    <a href="<?=BASE_URL?>/org/documents/department_review" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/department_review') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Dept. Documents</a>
                    <a href="<?=BASE_URL?>/org/documents/upload" class="block p-2 rounded hover:bg-green-700/40 transition">Upload New</a>
                    <a href="<?=BASE_URL?>/org/documents/approved" class="block p-2 rounded hover:bg-green-700/40 transition">Approved / Noted</a>
                    <a href="<?=BASE_URL?>/org/documents/rejected" class="block p-2 rounded hover:bg-green-700/40 transition">Rejected</a>
                </div>
            </div>

            <div x-data='{ open: <?= $is_organization_open ? 'true' : 'false' ?> }' class="space-y-1">
                <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-users w-5 text-center"></i>
                        <span>Organization</span>
                    </span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                </button>
                <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?=BASE_URL?>/org/members/list" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/members/list') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Members</a>
                    <a href="<?=BASE_URL?>/org/members/add" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/members/add') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Add Member</a>
                    <a href="<?=BASE_URL?>/org/departments" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/departments') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Departments</a>
                    <a href="<?=BASE_URL?>/org/roles" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/roles') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Roles & Permissions</a>
                </div>
            </div>

            <div class="pt-4">
                <h2 class="text-xs text-gray-500 uppercase mb-2 ml-2 tracking-wider font-semibold">System</h2>
            </div>
            
            <?php if (function_exists('has_permission') && has_permission($CAN_MANAGE_ANNOUNCEMENTS)): ?>
                <a href="<?=BASE_URL?>/org/announcements" class="flex items-center gap-3 p-3 rounded-lg hover:bg-yellow-700/50 transition
                    <?= $is_announcements_open ? 'text-yellow-400 font-semibold bg-yellow-900/40' : '' ?>">
                    <i class="fa-solid fa-bullhorn w-5 text-center <?= $is_announcements_open ? 'text-yellow-400' : '' ?>"></i>
                    <span class="<?= $is_announcements_open ? 'text-yellow-400' : '' ?>">Announcements</span>
                </a>
            <?php endif; ?>

        </nav>

        <div class="border-t border-green-800 px-4 py-4">
            <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                <button @click="open = !open" class="flex items-center justify-between w-full p-2 bg-green-900/30 rounded-lg hover:bg-green-700/40 transition">
                    <div class="flex items-center gap-3">
                        <img src="https://placehold.co/32x32/0b0f0c/10b981?text=U" alt="User" class="h-8 w-8 rounded-full border-2 border-green-600 ring-1 ring-green-400 object-cover">
                        <div class="text-left">
                            <p class="text-sm font-semibold text-green-300 truncate max-w-[100px]"><?= $_SESSION['user_name'] ?? 'User Name' ?></p>
                            <p class="text-xs text-gray-400"><?= $_SESSION['user_role'] ?? 'Organization Admin' ?></p>
                        </div>
                    </div>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs text-gray-400 ml-2"></i>
                </button>

                <div x-show="open" x-transition.duration.200ms class="absolute bottom-full mb-3 left-0 w-full bg-[#151a17] border border-green-700 rounded-lg shadow-2xl text-sm z-20">
                    <a href="<?=BASE_URL?>/org/profile" class="block px-4 py-2 hover:bg-green-700/30 rounded-t-lg transition">View Profile</a>
                    <a href="<?=BASE_URL?>/logout" class="block px-4 py-2 text-red-400 hover:bg-red-700/30 rounded-b-lg transition">Logout</a>
                </div>
            </div>
        </div>

        <div class="border-t border-green-800 p-3 text-xs text-gray-500 text-center">
            Maestro Organization © <?=date('Y')?>
        </div>
    </aside>

</body>
</html>