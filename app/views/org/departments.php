<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// --- PHP Helper Functions (CRITICAL for form/URL) ---
if (!defined('BASE_URL')) define('BASE_URL', '/maestro');
if (!function_exists('html_escape')) {
    function html_escape($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('csrf_field')) {
    function csrf_field() {
        echo '<input type="hidden" name="csrf_token" value="MOCK_CSRF_TOKEN">';
    }
}
// Mock set_value function for retaining form input after validation errors
if (!function_exists('set_value')) {
    function set_value($field) {
        // Retrieves the submitted value from the global POST array
        return $_POST[$field] ?? null; 
    }
}
// --- End Helper Functions ---

// Data variables passed from OrgController::departments()
$depts = $depts ?? [];
$potential_members = $potential_members ?? [];

// MOCKING CURRENT URI FOR SIDEBAR: 
$BASE_URL = BASE_URL ?? '';
$current_uri = $_SERVER['REQUEST_URI'] ?? '/org/departments'; 

// PHP LOGIC TO DETERMINE IF A DROPDOWN SHOULD BE OPEN
$is_documents_open = str_contains($current_uri, '/org/documents/');
$is_review_open = str_contains($current_uri, '/org/review/');
$is_organization_open = str_contains($current_uri, '/org/members/') || str_contains($current_uri, '/org/departments') || str_contains($current_uri, '/org/roles');
$is_reports_open = str_contains($current_uri, '/org/reports/');

// Aggregated Stats (Retained for display)
$total_departments = count($depts);
$total_members = array_sum(array_column($depts, 'members_count'));
$total_pending = array_sum(array_column($depts, 'pending_count'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments - Maestro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sidebar-dark': '#0f1511',
                        'maestro-bg': '#0b0f0c',
                    },
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
                        sans: ['Poppins', 'sans-serif'], 
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .maestro-bg { background-color: #0b0f0c; } 
    </style>
</head>
<body class="bg-maestro-bg text-white font-poppins" x-data="{}">

    <aside class="fixed top-0 left-0 h-full w-64 bg-[#0b0f0c] border-r border-green-900 text-white shadow-2xl flex flex-col transition-all duration-300 z-10">
        <div class="flex items-center justify-center py-6 border-b border-green-800">
            <img src="/public/maestrologo.png" alt="Logo" class="h-10 mr-8">
            <h1 class="text-2xl font-bold text-green-400 tracking-wider">MAESTRO</h1>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-3 space-y-4">

            <div>
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2 ml-2 tracking-wider">Main</h2>
                <a href="<?=$BASE_URL?>/org/dashboard" class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-700/50 transition">
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
                    <a href="<?=BASE_URL?>/org/documents/all" class="block p-2 rounded hover:bg-green-700/40 transition">All Documents</a>
                    <a href="<?=BASE_URL?>/org/documents/upload" class="block p-2 rounded hover:bg-green-700/40 transition">Upload New</a>
                    <a href="<?=BASE_URL?>/org/documents/pending" class="block p-2 rounded hover:bg-green-700/40 transition">Pending Review</a>
                    <a href="<?=BASE_URL?>/org/documents/approved" class="block p-2 rounded hover:bg-green-700/40 transition">Approved / Noted</a>
                    <a href="<?=BASE_URL?>/org/documents/rejected" class="block p-2 rounded hover:bg-green-700/40 transition">Rejected</a>
                    <a href="<?=BASE_URL?>/org/documents/archived" class="block p-2 rounded hover:bg-green-700/40 transition">Archived</a>
                </div>
            </div>

            <div x-data='{ open: <?= $is_review_open ? 'true' : 'false' ?> }' class="space-y-1">
                <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-clipboard-check w-5 text-center"></i><span>Review & Workflow</span></span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                </button>
                <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?=BASE_URL?>/org/review/queue" class="block p-2 rounded hover:bg-green-700/40 transition">Pending Reviews</a>
                    <a href="<?=BASE_URL?>/org/review/history" class="block p-2 rounded hover:bg-green-700/40 transition">Review History</a>
                    <a href="<?=BASE_URL?>/org/review/comments" class="block p-2 rounded hover:bg-green-700/40 transition">Comment Threads</a>
                </div>
            </div>

            <div x-data='{ open: <?= $is_organization_open ? 'true' : 'false' ?> }' class="space-y-1">
                <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-users w-5 text-center"></i><span>Organization</span></span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                </button>
                <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?=BASE_URL?>/org/members/list" class="block p-2 rounded hover:bg-green-700/40 transition">Members</a>
                    <a href="<?=BASE_URL?>/org/members/add" class="block p-2 rounded hover:bg-green-700/40 transition">Add Member</a>
                    <a href="<?=BASE_URL?>/org/departments" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/departments') ? 'text-green-400 font-semibold' : '' ?>">Departments</a>
                    <a href="<?=BASE_URL?>/org/roles" class="block p-2 rounded hover:bg-green-700/40 transition">Roles & Permissions</a>
                </div>
            </div>
            
            <div x-data='{ open: <?= $is_reports_open ? 'true' : 'false' ?> }' class="space-y-1">
                <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-chart-line w-5 text-center"></i><span>Reports & Analytics</span></span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                </button>
                <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?=BASE_URL?>/org/reports/overview" class="block p-2 rounded hover:bg-green-700/40 transition">Overview</a>
                    <a href="<?=BASE_URL?>/org/reports/documents" class="block p-2 rounded hover:bg-green-700/40 transition">Document Analytics</a>
                    <a href="<?=BASE_URL?>/org/reports/reviewers" class="block p-2 rounded hover:bg-green-700/40 transition">Reviewer Activity</a>
                    <a href="<?=BASE_URL?>/org/reports/reviewers" class="block p-2 rounded hover:bg-green-700/40 transition">Reviewer Activity</a>
                    <a href="<?=BASE_URL?>/org/reports/storage" class="block p-2 rounded hover:bg-green-700/40 transition">Storage Usage</a>
                </div>
            </div>

            <div class="pt-4"><h2 class="text-xs font-semibold text-gray-500 uppercase mb-2 ml-2 tracking-wider">System</h2></div>
            
            <div>
                <a href="<?=BASE_URL?>/org/settings" class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-700/30 transition">
                    <i class="fa-solid fa-gear w-5 text-center"></i>
                    <span>Settings</span>
                </a>
            </div>
        </nav>
        
        <div class="border-t border-green-800 p-3 text-xs text-gray-500 text-center">
            Maestro Organization © <?=date('Y')?>
        </div>
    </aside>

    <div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white">
        
        <h1 class="text-3xl font-bold text-green-400 mb-6 tracking-wide">
            Departments
        </h1>
        
        <?php if (function_exists('flash_alert')) flash_alert(); // Display flash messages ?>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex flex-col items-center justify-center text-center">
                <i class="fa-solid fa-building text-3xl text-blue-400/80 mb-2"></i>
                <p class="text-sm text-gray-400 uppercase tracking-wider">Total Departments</p>
                <p class="text-2xl font-bold text-blue-400 mt-1"><?= $total_departments ?></p>
            </div>
            <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex flex-col items-center justify-center text-center">
                <i class="fa-solid fa-users text-3xl text-green-400/80 mb-2"></i>
                <p class="text-sm text-gray-400 uppercase tracking-wider">Total Members</p>
                <p class="text-2xl font-bold text-green-400 mt-1"><?= $total_members ?></p>
            </div>
            <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex flex-col items-center justify-center text-center">
                <i class="fa-solid fa-file-circle-check text-3xl text-yellow-400/80 mb-2"></i>
                <p class="text-sm text-gray-400 uppercase tracking-wider">Total Pending Docs</p>
                <p class="text-2xl font-bold text-yellow-400 mt-1"><?= $total_pending ?></p>
            </div>
            <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex flex-col items-center justify-center text-center">
                <i class="fa-solid fa-file-lines text-3xl text-gray-400/80 mb-2"></i>
                <p class="text-sm text-gray-400 uppercase tracking-wider">Total Documents</p>
                <p class="text-2xl font-bold text-gray-400 mt-1"><?= array_sum(array_column($depts, 'documents_count')) ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2">
                <h2 class="text-xl font-semibold text-green-300 mb-4 border-b border-green-800/50 pb-2">Department Structure Overview</h2>

                <div class="space-y-4">
                    <?php if (!empty($depts)): ?>
                    <?php foreach($depts as $dept): 
                        $pending_count = $dept['pending_count'] ?? 0;
                        $members_count = $dept['members_count'] ?? 0;
                        $docs_count = $dept['documents_count'] ?? 0;
                        $pending_class = ((int)$pending_count > 0) ? 'text-red-400 font-semibold' : 'text-green-400';
                    ?>
                    <div class="bg-green-950/50 p-5 rounded-xl border-l-4 border-green-500 flex flex-col sm:flex-row justify-between items-start sm:items-center shadow-lg hover:bg-green-900/40 transition">
                        <div class="flex flex-col mb-3 sm:mb-0">
                            <span class="text-lg font-bold text-green-200"><?= htmlspecialchars($dept['name']) ?></span>
                            
                            <div class="text-sm text-gray-500 mt-1 space-y-1">
                                <span title="Number of members in this department" class="inline-flex items-center gap-1 text-gray-300">
                                    <i class="fa-solid fa-users text-xs text-green-500"></i> <?= $members_count ?> Members
                                </span>
                                <span title="Total documents associated with this department" class="inline-flex items-center gap-1 ml-4 text-gray-300">
                                    <i class="fa-solid fa-file-alt text-xs text-blue-500"></i> <?= $docs_count ?> Docs
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex flex-col items-start sm:items-end space-y-2">
                            <span title="Pending reviews in this department" class="inline-flex items-center gap-2 <?= $pending_class ?> bg-yellow-900/30 px-3 py-1 rounded-full text-xs">
                                <i class="fa-solid fa-hourglass-half text-sm"></i> <?= $pending_count ?> Pending
                            </span>
                            
                            <div class="flex items-center space-x-2">
                                <button class="bg-green-700 hover:bg-green-600 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                    View Members
                                </button>
                                <button class="text-blue-400 hover:text-blue-300 transition text-xs">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                </button>
                                <button class="text-red-400 hover:text-red-300 transition text-xs">
                                    <i class="fa-solid fa-trash-alt mr-1"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php else: ?>
                    <div class="p-8 text-center text-gray-500 bg-green-950/20 rounded-xl border border-green-800">
                        <i class="fa-solid fa-sitemap text-4xl mb-3 text-green-500"></i>
                        <p class="text-lg">No departments have been set up yet.</p>
                        <p class="text-sm mt-2">Use the form in the side panel to add your first department.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-8">
                
                <div class="bg-green-950/50 p-6 rounded-xl space-y-4 border border-green-800 shadow-2xl shadow-green-900/10 sticky top-4">
                    <form method="POST" action="<?=BASE_URL?>/org/departments/store" class="space-y-4">
                        <?php csrf_field(); ?>
                        <h2 class="text-xl font-bold text-yellow-400 mb-4 border-b border-green-800/50 pb-2">
                            <i class="fa-solid fa-plus-circle mr-2"></i> Create New Department
                        </h2>

                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-400 mb-1">Department Name <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" 
                                    class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-white placeholder-gray-500" 
                                    value="<?= html_escape(set_value('name') ?? '') ?>" placeholder="Department Name (e.g., Marketing)" required>
                            </div>
                            
                            <div>
                                <label for="member_ids" class="block text-sm font-medium text-gray-400 mb-1">Assign Initial Members (Optional)</label>
                                <select name="member_ids[]" id="member_ids" multiple size="4" 
                                    class="w-full bg-green-900 border border-green-800 p-3 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-white">
                                    <?php if (empty($potential_members)): ?>
                                        <option value="" disabled>No unassigned members found.</option>
                                    <?php else: ?>
                                        <?php foreach($potential_members as $member): ?>
                                            <option value="<?= $member['id'] ?>"><?= htmlspecialchars($member['fname'] . ' ' . $member['lname']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple members (only unassigned members appear).</p>
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full bg-yellow-700 px-6 py-3 rounded-xl hover:bg-yellow-600 font-bold text-lg transition shadow-lg shadow-yellow-900/40">
                            <i class="fa-solid fa-sitemap mr-2"></i> Create Department & Assign
                        </button>
                    </form>
                </div>

                <div class="bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-2xl h-full">
                    <h2 class="text-xl font-bold text-green-300 mb-4 flex items-center gap-2 border-b border-green-800/50 pb-2">
                        <i class="fa-solid fa-chart-pie text-lg text-green-500"></i> Performance Insights
                    </h2>
                    
                    <div class="space-y-4 text-center text-gray-500 py-6">
                        <i class="fa-solid fa-magnifying-glass-chart text-4xl mb-2"></i>
                        <p class="text-sm">
                            Departmental analytics are only available once members and documents have been assigned to departments in the database.
                        </p>
                        <a href="<?=BASE_URL?>/org/reports/documents" class="mt-2 block text-xs text-green-400 hover:text-green-300">View document analytics &rarr;</a>
                    </div>
                </div>
            </div>

        </div> 
    </div> 
</body>
</html>