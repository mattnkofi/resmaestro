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
        echo '<input type="hidden" name="csrf_token" value="' . ($_SESSION['csrf_token'] ?? 'MOCK_CSRF_TOKEN') . '">';
    }
}
if (!function_exists('set_value')) {
    function set_value($field) {
        return $_POST[$field] ?? null; 
    }
}
// --- End Helper Functions ---

$depts = $depts ?? [];
$potential_members = $potential_members ?? [];

$BASE_URL = BASE_URL ?? '';
$current_uri = $_SERVER['REQUEST_URI'] ?? '/org/departments'; 

$is_documents_open = str_contains($current_uri, '/org/documents/');
$is_review_open = str_contains($current_uri, '/org/review/');
$is_organization_open = str_contains($current_uri, '/org/members/') || str_contains($current_uri, '/org/departments') || str_contains($current_uri, '/org/roles');
$is_reports_open = str_contains($current_uri, '/org/reports/');

$total_departments = count($depts);
$total_members = array_sum(array_column($depts, 'members_count'));
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
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
<body class="bg-maestro-bg text-white font-poppins" 
    x-data="{
        BASE_URL: '<?= $BASE_URL ?>',
        editModalOpen: false,
        deleteModalOpen: false,
        currentDept: { id: 0, name: '', members_count: 0 },

        openEditModal(dept) {
            this.currentDept = { ...dept };
            this.editModalOpen = true;
        },

        openDeleteModal(dept) {
            this.currentDept = { ...dept };
            this.deleteModalOpen = true;
        }
    }">

    <aside class="fixed top-0 left-0 h-full w-64 bg-[#0b0f0c] border-r border-green-900 text-white shadow-2xl flex flex-col transition-all duration-300 z-10">
        <div class="flex items-center justify-center py-6 border-b border-green-800">
            <img src="/public/maestrologo.png" alt="Logo" class="h-10 mr-8">
            <h1 class="text-2xl font-bold text-green-400 tracking-wider">MAESTRO</h1>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-3 space-y-4">
            <div>
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2 ml-2 tracking-wider">Main</h2>
                <a href="<?= $BASE_URL ?>/org/dashboard" class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-700/50 transition">
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
                    <a href="<?= $BASE_URL ?>/org/documents/all" class="block p-2 rounded hover:bg-green-700/40 transition">All Documents</a>
                    <a href="<?= $BASE_URL ?>/org/documents/upload" class="block p-2 rounded hover:bg-green-700/40 transition">Upload New</a>
                    <a href="<?= $BASE_URL ?>/org/documents/pending" class="block p-2 rounded hover:bg-green-700/40 transition">Pending Review</a>
                    <a href="<?= $BASE_URL ?>/org/documents/approved" class="block p-2 rounded hover:bg-green-700/40 transition">Approved / Noted</a>
                    <a href="<?= $BASE_URL ?>/org/documents/rejected" class="block p-2 rounded hover:bg-green-700/40 transition">Rejected</a>
                    <a href="<?= $BASE_URL ?>/org/documents/archived" class="block p-2 rounded hover:bg-green-700/40 transition">Archived</a>
                </div>
            </div>

            <div x-data='{ open: <?= $is_review_open ? 'true' : 'false' ?> }' class="space-y-1">
                <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-clipboard-check w-5 text-center"></i>
                        <span>Reviews</span>
                    </span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                </button>
                <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?= $BASE_URL ?>/org/review/queue" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/review/queue') ? 'text-green-400 font-semibold' : '' ?>">Pending Reviews</a>
                    <a href="<?= $BASE_URL ?>/org/review/history" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/review/history') ? 'text-green-400 font-semibold' : '' ?>">Review History</a>
                    <a href="<?= $BASE_URL ?>/org/review/comments" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/review/comments') ? 'text-green-400 font-semibold' : '' ?>">Comment Threads</a>
                </div>
            </div>

            <div x-data='{ open: <?= $is_organization_open ? 'true' : 'false' ?> }' class="space-y-1">
                <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-users w-5 text-center"></i><span>Organization</span></span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                </button>
                <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?= $BASE_URL ?>/org/members/list" class="block p-2 rounded hover:bg-green-700/40 transition">Members</a>
                    <a href="<?= $BASE_URL ?>/org/members/add" class="block p-2 rounded hover:bg-green-700/40 transition">Add Member</a>
                    <a href="<?= $BASE_URL ?>/org/departments" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/departments') ? 'text-green-400 font-semibold' : '' ?>">Departments</a>
                    <a href="<?= $BASE_URL ?>/org/roles" class="block p-2 rounded hover:bg-green-700/40 transition">Roles & Permissions</a>
                </div>
            </div>

            <div x-data='{ open: <?= $is_reports_open ? 'true' : 'false' ?> }' class="space-y-1">
                <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-chart-line w-5 text-center"></i><span>Reports & Analytics</span></span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                </button>
                <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?= $BASE_URL ?>/org/reports/overview" class="block p-2 rounded hover:bg-green-700/40 transition">Overview</a>
                    <a href="<?= $BASE_URL ?>/org/reports/documents" class="block p-2 rounded hover:bg-green-700/40 transition">Document Analytics</a>
                    <a href="<?= $BASE_URL ?>/org/reports/reviewers" class="block p-2 rounded hover:bg-green-700/40 transition">Reviewer Activity</a>
                    <a href="<?= $BASE_URL ?>/org/reports/reviewers" class="block p-2 rounded hover:bg-green-700/40 transition">Reviewer Activity</a>
                    <a href="<?= $BASE_URL ?>/org/reports/storage" class="block p-2 rounded hover:bg-green-700/40 transition">Storage Usage</a>
                </div>
            </div>

            <div class="pt-4"><h2 class="text-xs font-semibold text-gray-500 uppercase mb-2 ml-2 tracking-wider">System</h2></div>
            
            <div>
                <a href="<?= $BASE_URL ?>/org/settings" class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-700/30 transition">
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

    <div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white">
        
        <h1 class="text-3xl font-bold text-green-400 mb-6 tracking-wide">
            Departments
        </h1>
        
        <?php if (function_exists('flash_alert')) flash_alert(); ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
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
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2">
                <h2 class="text-xl font-semibold text-green-300 mb-4 border-b border-green-800/50 pb-2">Department Structure Overview</h2>

                <div class="space-y-4">
                    <?php if (!empty($depts)): ?>
                    <?php foreach($depts as $dept): 
                        $dept_id = $dept['id'];
                        $members_count = $dept['members_count'] ?? 0;
                        $assigned_members = $dept['assigned_members'] ?? [];
                        $js_assigned_members = html_escape(json_encode($assigned_members));
                        $js_dept_name = html_escape($dept['name'], ENT_QUOTES | ENT_QUOTES); 
                    ?>
                    <div x-data="{ 
                                membersOpen: false, 
                                members: <?= $js_assigned_members ?>, 
                                loading: false, 
                                deptId: <?= $dept_id ?>,
                                membersCount: <?= $members_count ?>,
                                currentDept: { id: <?= $dept_id ?>, name: '<?= $js_dept_name ?>', members_count: <?= $members_count ?> },

                                openEditModal(dept) {
                                    this.currentDept = { ...dept };
                                    this.editModalOpen = true;
                                },
                                
                                openDeleteModal(dept) {
                                    this.currentDept = { ...dept };
                                    this.deleteModalOpen = true;
                                },
                                
                                toggleMembers() {
                                    this.membersOpen = !this.membersOpen;
                                },
                            }" 
                        class="bg-green-950/50 p-5 rounded-xl border-l-4 border-green-500 flex flex-col shadow-lg hover:bg-green-900/40 transition">
                        
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                            <div class="flex flex-col mb-3 sm:mb-0">
                                <span class="text-lg font-bold text-green-200"><?= htmlspecialchars($dept['name']) ?></span>
                                
                                <div class="text-sm text-gray-500 mt-1 space-y-1">
                                    <span title="Number of members in this department" class="inline-flex items-center gap-1 text-gray-300">
                                        <i class="fa-solid fa-users text-xs text-green-500"></i> <?= $members_count ?> Members
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex flex-col items-start sm:items-end space-y-2">
                                <div class="flex items-center space-x-2">
                                    <button @click="toggleMembers()"
                                            :disabled="membersCount === 0"
                                            class="bg-green-700 hover:bg-green-600 px-3 py-1.5 rounded-lg text-xs font-medium transition"
                                            :class="membersCount === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                                        <span x-text="membersOpen ? 'Hide Members' : 'View Members'">View Members</span>
                                    </button>
                                    
                                    <button @click="openEditModal({ id: deptId, name: '<?= $js_dept_name ?>', members_count: <?= $members_count ?> })"
                                            class="text-blue-400 hover:text-blue-300 transition text-xs">
                                        <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                    </button>
                                    
                                    <button @click="openDeleteModal({ id: deptId, name: '<?= $js_dept_name ?>', members_count: <?= $members_count ?> })"
                                            class="text-red-400 hover:text-red-300 transition text-xs">
                                        <i class="fa-solid fa-trash-alt mr-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div x-show="membersOpen" x-transition.duration.300ms class="mt-4 border-t border-green-800 pt-4">
                            <h4 class="text-sm font-semibold text-gray-400 mb-2">Assigned Members:</h4>
                            
                            <div x-show="members.length > 0" class="flex flex-wrap gap-2">
                                <template x-for="member in members" :key="member.id">
                                    <span class="bg-green-800/50 text-xs text-green-200 px-3 py-1 rounded-full shadow-md" 
                                            x-text="member.fname + ' ' + member.lname"></span>
                                </template>
                            </div>
                            <div x-show="members.length === 0" class="text-gray-500 text-sm">No members currently assigned to this department.</div>
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
                    <form method="POST" action="<?= $BASE_URL ?>/org/departments/store" class="space-y-4">
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
                        <a href="<?= $BASE_URL ?>/org/reports/documents" class="mt-2 block text-xs text-green-400 hover:text-green-300">View document analytics &rarr;</a>
                    </div>
                </div>
            </div>

        </div> 
    </div> 

    <div x-show="editModalOpen" 
        x-transition:enter="ease-out duration-300"
        x-transition:leave="ease-in duration-200"
        class="fixed inset-0 z-50 overflow-y-auto bg-maestro-bg bg-opacity-95 flex items-center justify-center" 
        style="display: none;">

        <div @click.outside="editModalOpen = false" class="w-full max-w-lg mx-auto bg-[#0f1511] rounded-xl shadow-2xl border border-green-800">
            
            <header class="p-4 border-b border-green-800 flex justify-between items-center bg-sidebar-dark rounded-t-xl">
                <h3 class="text-xl font-bold text-green-300">
                    <i class="fa-solid fa-pen-to-square mr-2"></i> Edit Department
                </h3>
                <button @click="editModalOpen = false" class="text-gray-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </header>

            <form method="POST" action="<?= $BASE_URL ?>/org/departments/update" class="p-6 space-y-5">
                <?php csrf_field(); ?>
                <input type="hidden" name="dept_id" :value="currentDept.id">

                <div>
                    <label for="edit_name" class="block text-sm font-medium mb-2 text-gray-300">Department Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_name" name="name" :value="currentDept.name" required
                        class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-green-500 focus:border-green-500 text-green-100">
                </div>
                
                <p class="text-sm text-gray-500">
                    Warning: Changing the department name will affect all associated members and documents.
                </p>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="editModalOpen = false" class="px-5 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 font-medium transition">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-700 hover:bg-blue-600 px-5 py-2 rounded-lg font-medium transition shadow-lg">
                        <i class="fa-solid fa-save mr-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="deleteModalOpen" 
        x-transition:enter="ease-out duration-300"
        x-transition:leave="ease-in duration-200"
        class="fixed inset-0 z-50 overflow-y-auto bg-maestro-bg bg-opacity-95 flex items-center justify-center" 
        style="display: none;">

        <div @click.outside="deleteModalOpen = false" class="w-full max-w-md mx-auto bg-[#0f1511] rounded-xl shadow-2xl border border-red-800">
            
            <header class="p-4 border-b border-red-800 flex justify-between items-center bg-sidebar-dark rounded-t-xl">
                <h3 class="text-xl font-bold text-red-400">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i> Confirm Deletion
                </h3>
                <button @click="deleteModalOpen = false" class="text-gray-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </header>

            <form method="POST" action="<?= $BASE_URL ?>/org/departments/delete" class="p-6 space-y-5">
                <?php csrf_field(); ?>
                <input type="hidden" name="dept_id" :value="currentDept.id">
                <input type="hidden" name="dept_name" :value="currentDept.name">
                
                <p class="text-gray-300">
                    Are you sure you want to delete the department: 
                    <span class="font-semibold text-red-300" x-text="currentDept.name"></span>?
                </p>
                <p class="text-sm text-yellow-500">
                    This action is permanent and will **unassign <span x-text="currentDept.members_count"></span> members**.
                </p>
                
                <div class="pt-2 pb-2">
                    <div class="g-recaptcha" 
                         data-sitekey="6Lea6BQsAAAAAMTRESFdJPnJOXJp-xundMb3Bxef"
                         data-theme="dark">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="deleteModalOpen = false" class="px-5 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 font-medium transition">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="bg-red-700 hover:bg-red-600 px-5 py-2 rounded-lg font-medium transition shadow-lg">
                        <i class="fa-solid fa-trash mr-2"></i> Delete Permanently
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>