<?php
$current_uri = $_SERVER['REQUEST_URI'] ?? '/org/dashboard'; 
$BASE_URL = BASE_URL ?? '';

$is_documents_open = str_contains($current_uri, '/org/documents/');
$is_review_open = str_contains($current_uri, '/org/review/');

$is_organization_open = str_contains($current_uri, '/org/members/') || str_contains($current_uri, '/org/departments') || str_contains($current_uri, '/org/roles');
$is_reports_open = str_contains($current_uri, '/org/reports/');

function is_active_sub_link($current_uri, $target_uri, $base_url) {

    return str_contains($current_uri, $base_url . $target_uri) ? 'text-green-400 font-semibold' : '';
}

function is_active_menu_sidebar($current_uri, $target_uri, $base_url) {
    if (str_contains($target_uri, '/org/settings') && (str_contains($current_uri, '/org/settings') || str_contains($current_uri, '/org/profile'))) {
        return 'text-green-400 font-semibold bg-green-900/40';
    }
    
    if ($current_uri == $base_url . $target_uri) {
        return 'text-green-400 font-semibold bg-green-900/40';
    }
    
    return '';
}
?>

<aside class="fixed top-0 left-0 h-full w-64 bg-[#0b0f0c] border-r border-green-900 text-white shadow-2xl flex flex-col transition-all duration-300 z-10">
    <div class="flex items-center justify-center py-6 border-b border-green-800">
        <img src="/public/maestrologo.png" alt="Logo" class="h-10 mr-8">
        <h1 class="text-2xl font-bold text-green-400 tracking-wider">MAESTRO</h1>
    </div>

    <nav class="flex-1 overflow-y-auto px-4 py-3 space-y-4">
        <div>
            <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2 ml-2 tracking-wider">Main</h2>
            <a href="<?= $BASE_URL ?>/org/dashboard" class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-700/50 transition <?= is_active_menu_sidebar($current_uri, '/org/dashboard', $BASE_URL) ?>">
                <i class="fa-solid fa-gauge w-5 text-center"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <div x-data='{ open: <?= $is_documents_open ? 'true' : 'false' ?> }' class="space-y-1">
            <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                <span class="flex items-center gap-3"><i class="fa-solid fa-file-lines w-5 text-center"></i><span>Documents</span></span>
                <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
            </button>
            <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                <a href="<?= $BASE_URL ?>/org/documents/all" class="block p-2 rounded hover:bg-green-700/40 transition <?= is_active_sub_link($current_uri, '/org/documents/all', $BASE_URL) ?>">All Documents</a>
                <a href="<?= $BASE_URL ?>/org/documents/upload" class="block p-2 rounded hover:bg-green-700/40 transition <?= is_active_sub_link($current_uri, '/org/documents/upload', $BASE_URL) ?>">Upload New</a>
                <a href="<?= $BASE_URL ?>/org/documents/pending" class="block p-2 rounded hover:bg-green-700/40 transition <?= is_active_sub_link($current_uri, '/org/documents/pending', $BASE_URL) ?>">Pending Review</a>
                <a href="<?= $BASE_URL ?>/org/documents/approved" class="block p-2 rounded hover:bg-green-700/40 transition <?= is_active_sub_link($current_uri, '/org/documents/approved', $BASE_URL) ?>">Approved / Noted</a>
                <a href="<?= $BASE_URL ?>/org/documents/rejected" class="block p-2 rounded hover:bg-green-700/40 transition <?= is_active_sub_link($current_uri, '/org/documents/rejected', $BASE_URL) ?>">Rejected</a>
                <a href="<?= $BASE_URL ?>/org/documents/archived" class="block p-2 rounded hover:bg-green-700/40 transition <?= is_active_sub_link($current_uri, '/org/documents/archived', $BASE_URL) ?>">Archived</a>
            </div>
        </div>

        <div x-data='{ open: <?= $is_review_open ? 'true' : 'false' ?> }' class="space-y-1">
            <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                <span class="flex items-center gap-3"><i class="fa-solid fa-clipboard-check w-5 text-center"></i><span>Reviews</span></span>
                <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
            </button>
            <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                <a href="<?= $BASE_URL ?>/org/review/queue" class="block p-2 rounded hover:bg-green-700/40 transition <?= is_active_sub_link($current_uri, '/org/review/queue', $BASE_URL) ?>">Pending Reviews</a>
                <a href="<?= $BASE_URL ?>/org/review/history" class="block p-2 rounded hover:bg-green-700/40 transition <?= is_active_sub_link($current_uri, '/org/review/history', $BASE_URL) ?>">Review History</a>
                <a href="<?= $BASE_URL ?>/org/review/comments" class="block p-2 rounded hover:bg-green-700/40 transition <?= is_active_sub_link($current_uri, '/org/review/comments', $BASE_URL) ?>">Comment Threads</a>
            </div>
        </div>

        <div x-data='{ open: <?= $is_organization_open ? 'true' : 'false' ?> }' class="space-y-1">
            <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                <span class="flex items-center gap-3"><i class="fa-solid fa-users w-5 text-center"></i><span>Organization</span></span>
                <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
            </button>
            <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                <a href="<?= $BASE_URL ?>/org/members/list" class="block p-2 rounded hover:bg-green-700/40 transition <?= is_active_sub_link($current_uri, '/org/members/list', $BASE_URL) ?>">Members</a>
                <a href="<?= $BASE_URL ?>/org/members/add" class="block p-2 rounded hover:bg-green-700/40 transition <?= is_active_sub_link($current_uri, '/org/members/add', $BASE_URL) ?>">Add Member</a>
                <a href="<?= $BASE_URL ?>/org/departments" class="block p-2 rounded hover:bg-green-700/40 transition <?= is_active_sub_link($current_uri, '/org/departments', $BASE_URL) ?>">Departments</a>
                <a href="<?= $BASE_URL ?>/org/roles" class="block p-2 rounded hover:bg-green-700/40 transition <?= is_active_sub_link($current_uri, '/org/roles', $BASE_URL) ?>">Roles & Permissions</a>
            </div>
        </div>

        <div x-data='{ open: <?= $is_reports_open ? 'true' : 'false' ?> }' class="space-y-1">
            <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                <span class="flex items-center gap-3"><i class="fa-solid fa-chart-line w-5 text-center"></i><span>Reports & Analytics</span></span>
                <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
            </button>
            <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                <a href="<?= $BASE_URL ?>/org/reports/overview" class="block p-2 rounded hover:bg-green-700/40 transition <?= is_active_sub_link($current_uri, '/org/reports/overview', $BASE_URL) ?>">Overview</a>
                <a href="<?= $BASE_URL ?>/org/reports/documents" class="block p-2 rounded hover:bg-green-700/40 transition <?= is_active_sub_link($current_uri, '/org/reports/documents', $BASE_URL) ?>">Document Analytics</a>
                <a href="<?= $BASE_URL ?>/org/reports/reviewers" class="block p-2 rounded hover:bg-green-700/40 transition <?= is_active_sub_link($current_uri, '/org/reports/reviewers', $BASE_URL) ?>">Reviewer Activity</a>
                <a href="<?= $BASE_URL ?>/org/reports/storage" class="block p-2 rounded hover:bg-green-700/40 transition <?= is_active_sub_link($current_uri, '/org/reports/storage', $BASE_URL) ?>">Storage Usage</a>
            </div>
        </div>

        <div class="pt-4"><h2 class="text-xs font-semibold text-gray-500 uppercase mb-2 ml-2 tracking-wider">System</h2></div>
        
        <div>
            <a href="<?= $BASE_URL ?>/org/settings" class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-700/30 transition <?= is_active_menu_sidebar($current_uri, '/org/settings', $BASE_URL) ?>">
                <i class="fa-solid fa-gear w-5 text-center"></i>
                <span>Settings</span>
            </a>
        </div>

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
                <a href="<?= $BASE_URL ?>/org/profile" class="block px-4 py-2 hover:bg-green-700/30 rounded-t-lg transition">View Profile</a>
                <a href="<?= $BASE_URL ?>/org/settings" class="block px-4 py-2 hover:bg-green-700/30 transition">Settings</a>
                <a href="<?= $BASE_URL ?>/logout" class="block px-4 py-2 text-red-400 hover:bg-red-700/30 rounded-b-lg transition">Logout</a>
            </div>
        </div>
    </div>
    
    <div class="border-t border-green-800 p-3 text-xs text-gray-500 text-center">
        Maestro Organization © <?= date('Y') ?>
    </div>
</aside>