<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// --- PHP Helper Functions ---
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

// Data variables passed from controller
$members_data = $members ?? [];
$departments = $departments ?? [];
$roles = $roles ?? [];
$q = $q ?? '';
$selected_role = $_GET['role'] ?? '';

$BASE_URL = BASE_URL ?? '';
$current_uri = $_SERVER['REQUEST_URI'] ?? '/org/members/list';

// Sidebar state
$is_documents_open = str_contains($current_uri, '/org/documents/');
$is_review_open = str_contains($current_uri, '/org/review/');
$is_organization_open = str_contains($current_uri, '/org/members/') || str_contains($current_uri, '/org/departments') || str_contains($current_uri, '/org/roles');
$is_reports_open = str_contains($current_uri, '/org/reports/');

// Stats Calculation
$total_members = count($members_data);
$active_reviewers = 0;
$departments_seen = [];
$reviewer_roles = ['Administrator', 'Reviewer', 'Adviser', 'President'];

foreach ($members_data as $member) {
    $role_name = $member['role_name'] ?? '';
    $dept_name = $member['dept_name'] ?? 'N/A';
    if (in_array($role_name, $reviewer_roles)) $active_reviewers++;
    if ($dept_name !== 'N/A' && !in_array($dept_name, $departments_seen)) $departments_seen[] = $dept_name;
}
$total_departments = count($departments_seen);

// Custom roles for filter
$custom_roles = ['Adviser', 'President', 'Secretary', 'Treasurer', 'Executive Member', 'General Member'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Members - Maestro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 'sidebar-dark': '#0f1511', 'maestro-bg': '#0b0f0c' },
                    fontFamily: { poppins: ['Poppins', 'sans-serif'], sans: ['Poppins', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>body { font-family: 'Poppins', sans-serif; } .maestro-bg { background-color: #0b0f0c; }</style>
</head>
<body class="bg-maestro-bg text-white font-poppins" 
    x-data="{
        editModalOpen: false,
        deleteModalOpen: false,
        currentMember: { id: 0, fname: '', lname: '', email: '', dept_id: '', role_id: '' },
        
        openEditModal(member) {
            this.currentMember = { ...member };
            this.editModalOpen = true;
        },
        
        openDeleteModal(member) {
            this.currentMember = { ...member };
            this.deleteModalOpen = true;
        }
    }"
    @keydown.escape="editModalOpen = false; deleteModalOpen = false">

    <!-- Sidebar -->
    <aside class="fixed top-0 left-0 h-full w-64 bg-[#0b0f0c] border-r border-green-900 text-white shadow-2xl flex flex-col z-10">
        <div class="flex items-center justify-center py-6 border-b border-green-800">
            <img src="/public/maestrologo.png" alt="Logo" class="h-10 mr-8">
            <h1 class="text-2xl font-bold text-green-400 tracking-wider">MAESTRO</h1>
        </div>
        <nav class="flex-1 overflow-y-auto px-4 py-3 space-y-4">
            <div>
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2 ml-2 tracking-wider">Main</h2>
                <a href="<?=$BASE_URL?>/org/dashboard" class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-700/50 transition">
                    <i class="fa-solid fa-gauge w-5 text-center"></i><span>Dashboard</span>
                </a>
            </div>
            <div x-data='{ open: <?= $is_documents_open ? 'true' : 'false' ?> }' class="space-y-1">
                <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-file-lines w-5 text-center"></i><span>Documents</span></span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs"></i>
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
                    <span class="flex items-center gap-3"><i class="fa-solid fa-users w-5 text-center"></i><span>Organization</span></span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs"></i>
                </button>
                <div x-show="open" x-transition class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?=$BASE_URL?>/org/members/list" class="block p-2 rounded hover:bg-green-700/40 text-green-400 font-semibold">Members</a>
                    <a href="<?=$BASE_URL?>/org/members/add" class="block p-2 rounded hover:bg-green-700/40">Add Member</a>
                    <a href="<?=$BASE_URL?>/org/departments" class="block p-2 rounded hover:bg-green-700/40">Departments</a>
                    <a href="<?=$BASE_URL?>/org/roles" class="block p-2 rounded hover:bg-green-700/40">Roles & Permissions</a>
                </div>
            </div>

                        <div class="pt-4">
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2 ml-2 tracking-wider">System</h2>
            </div>

        </nav>

        <div class="border-t border-green-800 px-4 py-4">
            <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                <button @click="open = !open" class="flex items-center justify-between w-full p-2 bg-green-900/30 rounded-lg hover:bg-green-700/40 transition">
                    <div class="flex items-center gap-3">
                        <!-- Placeholder for user image -->
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

        <div class="border-t border-green-800 p-3 text-xs text-gray-500 text-center">Maestro © <?=date('Y')?></div>
    </aside>

    <!-- Main Content -->
    <div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <h1 class="text-3xl font-bold text-green-400 mb-4 sm:mb-0">Organization Members</h1>
            <a href="<?=$BASE_URL?>/org/members/add" class="bg-green-700 hover:bg-green-600 px-5 py-2.5 rounded-xl text-lg font-medium transition shadow-lg">
                <i class="fa-solid fa-user-plus mr-2"></i> Add New Member
            </a>
        </div>

        <?php if (function_exists('flash_alert')) flash_alert(); ?>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex items-center justify-between">
                <div><p class="text-sm text-gray-400 uppercase">Total Members</p><p class="text-3xl font-bold text-green-400 mt-1"><?= $total_members ?></p></div>
                <i class="fa-solid fa-users text-4xl text-green-700/50"></i>
            </div>
            <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex items-center justify-between">
                <div><p class="text-sm text-gray-400 uppercase">Active Reviewers</p><p class="text-3xl font-bold text-yellow-400 mt-1"><?= $active_reviewers ?></p></div>
                <i class="fa-solid fa-user-check text-4xl text-yellow-700/50"></i>
            </div>
            <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex items-center justify-between">
                <div><p class="text-sm text-gray-400 uppercase">Departments</p><p class="text-3xl font-bold text-blue-400 mt-1"><?= $total_departments ?></p></div>
                <i class="fa-solid fa-building text-4xl text-blue-700/50"></i>
            </div>
        </div>

        <!-- Search & Filter -->
        <form method="GET" action="<?=$BASE_URL?>/org/members/list" class="flex flex-col md:flex-row gap-4 mb-6">
            <input type="text" name="q" placeholder="Search by name or email..." value="<?= html_escape($q) ?>"
                class="w-full md:w-1/2 bg-green-900/50 border border-green-800 p-3 rounded-xl placeholder-gray-500 text-white">
            <select name="role" class="w-full md:w-1/4 bg-green-900/50 border border-green-800 p-3 rounded-xl text-white">
                <option value="">Filter by Role</option>
                <?php foreach ($custom_roles as $r): ?>
                    <option value="<?= html_escape(strtolower(str_replace(' ', '_', $r))) ?>" <?= $selected_role === strtolower(str_replace(' ', '_', $r)) ? 'selected' : '' ?>><?= html_escape($r) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-green-700 hover:bg-green-600 px-5 py-3 rounded-xl font-medium transition">
                <i class="fa-solid fa-filter mr-2"></i> Apply
            </button>
        </form>

        <!-- Members Table -->
        <div class="overflow-x-auto rounded-xl border border-green-800 shadow-2xl">
            <table class="w-full text-left">
                <thead class="bg-yellow-900/40 text-gray-200 uppercase text-sm tracking-wider">
                    <tr>
                        <th class="p-4 border-b border-green-800">Name</th>
                        <th class="p-4 border-b border-green-800">Email</th>
                        <th class="p-4 border-b border-green-800">Department</th>
                        <th class="p-4 border-b border-green-800">Role</th>
                        <th class="p-4 border-b border-green-800 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-[#0f1511] text-gray-300">
                    <?php if (!empty($members_data)): ?>
                    <?php foreach($members_data as $member):
                        $full_name = html_escape(trim($member['fname'] . ' ' . $member['lname']));
                        $role_name = html_escape($member['role_name'] ?? 'No Role');
                        $dept_name = html_escape($member['dept_name'] ?? 'No Department');
                        $email = html_escape($member['email']);
                        $role_color = match ($role_name) {
                            'Administrator', 'Adviser', 'President' => 'text-red-400',
                            'Reviewer' => 'text-yellow-400',
                            default => 'text-gray-400',
                        };
                        
                        // Prepare member data for Alpine.js
                        $js_member = html_escape(json_encode([
                            'id' => $member['id'],
                            'fname' => $member['fname'],
                            'lname' => $member['lname'],
                            'email' => $member['email'],
                            'dept_id' => $member['dept_id'] ?? '',
                            'role_id' => $member['role_id'] ?? '',
                            'full_name' => trim($member['fname'] . ' ' . $member['lname'])
                        ]));
                    ?>
                    <tr class="border-b border-green-800 transition hover:bg-green-700/10">
                        <td class="p-4 font-medium text-green-200"><?= $full_name ?></td>
                        <td class="p-4 text-sm text-gray-500"><?= $email ?></td>
                        <td class="p-4 text-gray-300"><?= $dept_name ?></td>
                        <td class="p-4 font-medium <?= $role_color ?>"><?= $role_name ?></td>
                        <td class="p-4 text-center space-x-2">
                            <button @click="openEditModal(<?= $js_member ?>)" 
                                class="text-green-400 hover:text-green-300 transition text-sm">
                                <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                            </button>
                            <button @click="openDeleteModal(<?= $js_member ?>)" 
                                class="text-red-400 hover:text-red-300 transition text-sm">
                                <i class="fa-solid fa-trash mr-1"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-500">
                            <i class="fa-solid fa-users-slash text-4xl mb-3 text-red-500"></i>
                            <p class="text-lg"><?= !empty($q) ? 'No members found matching "' . html_escape($q) . '"' : 'No members found.' ?></p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- EDIT MEMBER MODAL -->
    <div x-show="editModalOpen" 
        x-transition:enter="ease-out duration-300"
        x-transition:leave="ease-in duration-200"
        class="fixed inset-0 z-50 overflow-y-auto bg-maestro-bg bg-opacity-95 flex items-center justify-center" 
        style="display: none;">

        <div @click.outside="editModalOpen = false" class="w-full max-w-2xl mx-auto bg-[#0f1511] rounded-xl shadow-2xl border border-green-800">
            
            <header class="p-4 border-b border-green-800 flex justify-between items-center bg-sidebar-dark rounded-t-xl">
                <h3 class="text-xl font-bold text-green-300">
                    <i class="fa-solid fa-user-edit mr-2"></i> Edit Member
                </h3>
                <button @click="editModalOpen = false" class="text-gray-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </header>

            <form method="POST" action="<?= $BASE_URL ?>/org/members/update" class="p-6 space-y-5">
                <?php csrf_field(); ?>
                <input type="hidden" name="member_id" :value="currentMember.id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_fname" class="block text-sm font-medium mb-2 text-gray-300">First Name <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_fname" name="fname" x-model="currentMember.fname" required
                            class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-green-500 focus:border-green-500 text-green-100">
                    </div>
                    <div>
                        <label for="edit_lname" class="block text-sm font-medium mb-2 text-gray-300">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_lname" name="lname" x-model="currentMember.lname" required
                            class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-green-500 focus:border-green-500 text-green-100">
                    </div>
                </div>

                <div>
                    <label for="edit_email" class="block text-sm font-medium mb-2 text-gray-300">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" id="edit_email" name="email" x-model="currentMember.email" required
                        class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-green-500 focus:border-green-500 text-green-100">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_dept_id" class="block text-sm font-medium mb-2 text-gray-300">Department <span class="text-red-500">*</span></label>
                        <select id="edit_dept_id" name="dept_id" x-model="currentMember.dept_id" required
                            class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-green-500 focus:border-green-500 text-green-100">
                            <option value="">Select Department</option>
                            <?php foreach($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>"><?= html_escape($dept['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="edit_role_id" class="block text-sm font-medium mb-2 text-gray-300">Role <span class="text-red-500">*</span></label>
                        <select id="edit_role_id" name="role_id" x-model="currentMember.role_id" required
                            class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-green-500 focus:border-green-500 text-green-100">
                            <option value="">Select Role</option>
                            <?php foreach($roles as $role): ?>
                                <option value="<?= $role['id'] ?>"><?= html_escape($role['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="border-t border-green-800 pt-4">
                    <h4 class="text-md font-semibold text-yellow-400 mb-3">
                        <i class="fa-solid fa-key mr-2"></i> Reset Password (Optional)
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="new_password" class="block text-sm font-medium mb-2 text-gray-300">New Password</label>
                            <input type="password" id="new_password" name="new_password" minlength="8"
                                placeholder="Leave blank to keep current"
                                class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-green-100 placeholder-gray-500">
                        </div>
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium mb-2 text-gray-300">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" minlength="8"
                                placeholder="Re-enter new password"
                                class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-green-100 placeholder-gray-500">
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Minimum 8 characters. Leave both fields blank to keep the current password.</p>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="editModalOpen = false" class="px-5 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 font-medium transition">
                        Cancel
                    </button>
                    <button type="submit" class="bg-green-700 hover:bg-green-600 px-5 py-2 rounded-lg font-medium transition shadow-lg">
                        <i class="fa-solid fa-save mr-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DELETE CONFIRMATION MODAL -->
    <div x-show="deleteModalOpen" 
        x-transition:enter="ease-out duration-300"
        x-transition:leave="ease-in duration-200"
        class="fixed inset-0 z-[60] overflow-y-auto bg-maestro-bg bg-opacity-95 flex items-center justify-center" 
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

            <div class="p-6 space-y-4">
                <p class="text-gray-300">
                    Are you sure you want to delete the member: 
                    <span class="font-semibold text-red-300" x-text="currentMember.full_name"></span>?
                </p>
                <p class="text-sm text-gray-500">
                    This action cannot be undone. All associated data with this member will be permanently removed.
                </p>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="deleteModalOpen = false" class="px-5 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 font-medium transition">
                        Cancel
                    </button>
                    <form method="POST" action="<?= $BASE_URL ?>/org/members/delete">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="member_id" :value="currentMember.id">
                        <button type="submit" class="bg-red-700 hover:bg-red-600 px-5 py-2 rounded-lg font-medium transition shadow-lg">
                            <i class="fa-solid fa-trash mr-2"></i> Delete Member
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>