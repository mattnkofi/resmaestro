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

$user_name = $_SESSION['user_name'] ?? 'User Name';
$first_initial = strtoupper(substr($user_name, 0, 1));
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
    x-data="{ BASE_URL: '<?= $BASE_URL ?>', isModalOpen: false, modalDept: {}, actionType: '' }">

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

            <div class="space-y-1">
                <div class="w-full flex items-center justify-between p-3 rounded-lg bg-green-900/10 text-green-300">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-file-lines w-5 text-center"></i>
                        <span><b>Documents</b></span>
                    </span>
                </div>
                <div class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?=BASE_URL?>/org/documents/all" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/all') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">All Documents</a>
                    <a href="<?=BASE_URL?>/org/documents/department_review" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/department_review') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Dept. Documents</a>
                    <a href="<?=BASE_URL?>/org/documents/upload" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/upload') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Upload New</a>
                    <a href="<?=BASE_URL?>/org/documents/approved" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/approved') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Approved / Noted</a>
                    <a href="<?=BASE_URL?>/org/documents/rejected" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/rejected') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Rejected</a>
                </div>

                <div class="h-4"></div>

                <div class="w-full flex items-center justify-between p-3 rounded-lg bg-green-900/10 text-green-300">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-users w-5 text-center"></i>
                        <span><b>Organization</b></span>
                    </span>
                </div>
                <div class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?=BASE_URL?>/org/members/list" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/members/list') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Members</a>
                    <a href="<?=BASE_URL?>/org/members/add" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/members/add') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Add Member</a>
                    <a href="<?=BASE_URL?>/org/departments" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/departments') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Departments</a>
                </div>
            </div>
            
            <div class="pt-4">
                <h2 class="text-xs text-gray-500 uppercase mb-2 ml-2 tracking-wider font-semibold">System</h2>
            </div>
        </nav>

        <div class="border-t border-green-800 px-4 py-4">
            <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                <button @click="open = !open" class="flex items-center justify-between w-full p-2 bg-green-900/30 rounded-lg hover:bg-green-700/40 transition">
                    <div class="flex items-center gap-3">
                        <img src="https://placehold.co/32x32/0b0f0c/10b981?text=<?= $first_initial ?>" alt="User" class="h-8 w-8 rounded-full border-2 border-green-600 ring-1 ring-green-400 object-cover">
                        <div class="text-left">
                            <p class="text-sm font-semibold text-green-300 truncate max-w-[100px]"><?= $_SESSION['user_name'] ?? 'User Name' ?></p>
                            <p class="text-xs text-gray-400"><?= $_SESSION['user_role'] ?? 'Organization Admin' ?></p>
                        </div>
                    </div>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs text-gray-400 ml-2"></i>
                </button>

                <div x-show="open" x-transition.duration.200ms class="absolute bottom-full mb-3 left-0 w-full bg-[#151a17] border border-green-700 rounded-lg shadow-2xl text-sm z-20">
                    <a href="<?= $BASE_URL ?>/org/profile" class="block px-4 py-2 hover:bg-green-700/30 rounded-t-lg transition">View Profile</a>
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
                                deptName: '<?= html_escape($dept['name']) ?>',
                                membersCount: <?= $members_count ?>,
                                
                                toggleMembers() {
                                    this.membersOpen = !this.membersOpen;
                                },
                                openEditModal() {
                                    modalDept = { id: this.deptId, name: this.deptName, membersCount: this.membersCount };
                                    actionType = 'edit';
                                    isModalOpen = true;
                                },
                                openDeleteModal() {
                                    modalDept = { id: this.deptId, name: this.deptName, membersCount: this.membersCount };
                                    actionType = 'delete';
                                    isModalOpen = true;
                                }
                            }" 
                        class="bg-green-950/50 p-5 rounded-xl border-l-4 border-green-500 flex flex-col shadow-lg hover:bg-green-900/40 transition">
                        
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                            <div class="flex flex-col mb-3 sm:mb-0">
                                <span class="text-lg font-bold text-green-200"><?= htmlspecialchars($dept['name']) ?></span>
                                
                                <div class="text-sm text-gray-500 mt-1 space-y-1">
                                    <span title="Number of members in this department" class="inline-flex items-center gap-1 text-gray-300">
                                        <i class="fa-solid fa-users text-xs text-green-500"></i> <span x-text="membersCount"><?= $members_count ?></span> Members
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <button @click="toggleMembers()"
                                        :disabled="membersCount === 0"
                                        class="bg-green-700 hover:bg-green-600 px-3 py-1.5 rounded-lg text-xs font-medium transition"
                                        :class="membersCount === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                                    <span x-text="membersOpen ? 'Hide Members' : 'View Members'">View Members</span>
                                </button>

                                 <button @click="openEditModal()"
                                        class="bg-yellow-700 hover:bg-yellow-600 px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center gap-1">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                
                                <button @click="openDeleteModal()"
                                        class="bg-red-700 hover:bg-red-600 px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center gap-1">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>

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
                        
                        <button type="submit" class="w-full bg-yellow-700 px-6 py-3 rounded-xl hover:bg-yellow-600 font-bold text-lg transition shadow-lg shadow-yellow-900/40">
                            <i class="fa-solid fa-sitemap mr-2"></i> Create Department & Assign
                        </button>
                    </form>
                </div>
            </div>
        </div> 
    </div> 

    <div x-show="isModalOpen" x-cloak x-transition.opacity class="fixed inset-0 bg-black bg-opacity-75 z-50 flex justify-center items-center p-4">
        <div x-show="isModalOpen" x-transition.scale.duration.300ms @click.outside="isModalOpen = false" class="bg-green-950/95 border border-green-700 rounded-xl p-8 w-full max-w-lg shadow-2xl">
            
            <div x-show="actionType === 'edit'">
                <h3 class="text-2xl font-bold text-yellow-400 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Department
                </h3>
                <form method="POST" :action="BASE_URL + '/org/departments/update'" class="space-y-4">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="dept_id" :value="modalDept.id">
                    
                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-gray-400 mb-1">Department Name <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_name" name="name" :value="modalDept.name" required
                            class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-white">
                    </div>
                    
                    <p class="text-sm text-gray-500 border-t border-green-800 pt-2">
                        To add/remove members, go to the <a :href="BASE_URL + '/org/members/list'" class="text-blue-400 hover:text-blue-300 underline">Members List</a> page.
                    </p>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="isModalOpen = false" class="bg-gray-600 hover:bg-gray-500 px-4 py-2 rounded-lg text-white font-semibold transition">Cancel</button>
                        <button type="submit" class="bg-yellow-700 hover:bg-yellow-600 px-4 py-2 rounded-lg text-white font-semibold transition">Save Changes</button>
                    </div>
                </form>
            </div>

            <div x-show="actionType === 'delete'">
                <h3 class="text-2xl font-bold text-red-400 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> Confirm Deletion
                </h3>
                <form method="POST" :action="BASE_URL + '/org/departments/delete'">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="dept_id" :value="modalDept.id">
                    
                    <p class="text-white mb-4">
                        Are you sure you want to delete the department 
                        <strong class="text-red-300" x-text="modalDept.name"></strong>?
                    </p>
                    
                    <p class="text-sm text-orange-400 font-semibold" x-show="modalDept.membersCount > 0">
                        <i class="fa-solid fa-users-slash"></i> NOTE: This department has 
                        <span x-text="modalDept.membersCount"></span> members. 
                        Deleting it will set their department to <b>Unassigned</b>.
                    </p>
                    <p x-show="modalDept.membersCount === 0" class="text-sm text-green-400 mb-4">
                        This department has no members. Safe to delete.
                    </p>
                    
                    <div class="bg-gray-800 p-4 rounded-lg border border-red-700/50 mb-6">
                        <label for="verification_code" class="block text-sm font-bold text-red-400 mb-2">
                            Type the verification code to confirm:
                        </label>
                        <div class="flex items-center gap-4">
                            <div class="text-2xl font-mono px-4 py-2 bg-red-900 rounded-lg select-none">
                                <span class="text-red-100">
                                    <?= $_SESSION['dept_delete_code'] ?? 'N/A' ?>
                                </span>
                            </div>
                            <input type="text" id="verification_code" name="verification_code" 
                                class="w-full p-3 bg-red-900/50 border border-red-700 rounded-lg focus:ring-red-500 focus:border-red-500 text-white placeholder-gray-500 text-lg font-mono tracking-widest" 
                                placeholder="Enter code" required autocomplete="off">
                        </div>
                        <p class="text-xs text-gray-400 mt-2">The code changes every time you try to delete.</p>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="isModalOpen = false" class="bg-gray-600 hover:bg-gray-500 px-4 py-2 rounded-lg text-white font-semibold transition">Cancel</button>
                        <button type="submit" class="bg-red-700 px-4 py-2 rounded-lg hover:bg-red-600 font-bold text-white transition">Delete Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </body>
</html>