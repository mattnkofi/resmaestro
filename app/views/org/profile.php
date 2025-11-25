<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
$user = $user ?? []; 
$admin_roles = ['President', 'Adviser'];
$can_manage_org = function_exists('has_permission') && has_permission($admin_roles);

if (!function_exists('is_admin_or_manager')) {
    function is_admin_or_manager() {
        $admin_roles = ['Administrator', 'President', 'Adviser'];
        $current_role = $_SESSION['user_role'] ?? '';
        return in_array($current_role, $admin_roles);
    }
}
$is_admin_analytics = is_admin_or_manager();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Maestro UI</title>
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
                    // Applying Poppins font family
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
                        sans: ['Poppins', 'sans-serif'], 
                    }
                }
            }
        }
    </script>
    <style>
        /* Explicitly apply Poppins via standard CSS */
        body { font-family: 'Poppins', sans-serif; }
        
        /* Sidebar Custom Styles (for consistency) */
        .maestro-bg { background-color: #0b0f0c; } 
    </style>
</head>
<body class="bg-maestro-bg text-white font-poppins" x-data="{ leaveDeptModalOpen: false, currentDept: '<?= html_escape($user['dept_name'] ?? 'N/A') ?>', currentDeptId: '<?= html_escape($user['dept_id'] ?? 0) ?>' }" @keydown.escape="leaveDeptModalOpen = false">

    <?php 
    $current_uri = $_SERVER['REQUEST_URI'] ?? '/org/profile'; 

    $is_documents_open = str_contains($current_uri, '/org/documents/');
    $is_review_open = str_contains($current_uri, '/org/review/');
    $is_organization_open = str_contains($current_uri, '/org/members/') || str_contains($current_uri, '/org/departments') || str_contains($current_uri, '/org/roles');
    $is_reports_open = str_contains($current_uri, '/org/reports/');
    
    $user_name = $_SESSION['user_name'] ?? 'User Name';
    $first_initial = strtoupper(substr($user_name, 0, 1));
    ?>

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
                
                <?php if ($is_admin_analytics): ?>
                <a href="<?=BASE_URL?>/org/analytics" class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-700/50 transition 
                    <?= str_contains($current_uri, '/org/analytics') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">
                    <i class="fa-solid fa-chart-line w-5 text-center"></i>
                    <span>Analytics</span>
                </a>
                <?php endif; ?>
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
                    <a href="<?=BASE_URL?>/org/profile" class="block px-4 py-2 hover:bg-green-700/30 rounded-t-lg transition">View Profile</a>
                    <a href="<?=BASE_URL?>/logout" class="block px-4 py-2 text-red-400 hover:bg-red-700/30 rounded-b-lg transition">Logout</a>
                </div>
            </div>
        </div>

        <div class="border-t border-green-800 p-3 text-xs text-gray-500 text-center">
            Maestro Organization © <?=date('Y')?>
        </div>
    </aside>
    <div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white">
        
        <h1 class="text-3xl font-bold text-green-400 mb-6 tracking-wide">
            My Profile & Security
        </h1>

        <?php if (function_exists('flash_alert')) flash_alert(); // Display flash messages ?>

        <div class="bg-green-950/50 p-8 rounded-xl border border-green-800 shadow-2xl shadow-green-900/10 max-w-4xl mx-auto">
            
            <div class="flex items-center gap-6 mb-8 border-b border-green-800/50 pb-6">
                <img src="https://placehold.co/32x32/0b0f0c/10b981?text=<?= $first_initial ?>" 
                     class="w-24 h-24 rounded-full border-2 border-green-700 object-cover" 
                     alt="Profile Picture">
                <div>
                    <h2 class="text-2xl font-bold text-green-200"><?= html_escape(trim(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''))); ?></h2>
                    <p class="text-gray-400"><?= html_escape($user['email'] ?? 'N/A') ?></p>
                    <p class="text-sm text-yellow-400 mt-1">Role: <?= html_escape($_SESSION['user_role'] ?? 'N/A') ?></p>
                </div>
            </div>
            
            <form method="POST" action="<?= BASE_URL ?>/org/profile/update" class="space-y-6">
                <?php csrf_field(); ?>

                <h3 class="text-xl font-semibold text-yellow-400 mb-4 border-b border-green-800/50 pb-2">
                    <i class="fa-solid fa-user-edit mr-2"></i> Update Personal Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="fname" class="block text-sm font-medium mb-2 text-gray-300">First Name</label>
                        <input type="text" id="fname" name="fname" value="<?= html_escape($user['fname'] ?? '') ?>"
                               class="w-full p-3 rounded-lg bg-green-900 border border-green-800 focus:ring-green-500 focus:border-green-500 text-green-100" required>
                    </div>
                    <div>
                        <label for="lname" class="block text-sm font-medium mb-2 text-gray-300">Last Name</label>
                        <input type="text" id="lname" name="lname" value="<?= html_escape($user['lname'] ?? '') ?>"
                               class="w-full p-3 rounded-lg bg-green-900 border border-green-800 focus:ring-green-500 focus:border-green-500 text-green-100" required>
                    </div>
                </div>

                <h3 class="text-xl font-semibold text-red-400 mb-4 border-b border-green-800/50 pb-2 pt-4">
                    <i class="fa-solid fa-key mr-2"></i> Change Password (Optional)
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="new_password" class="block text-sm font-medium mb-2 text-gray-300">New Password (Min. 8 Chars)</label>
                        <input type="password" id="new_password" name="new_password" minlength="8"
                            placeholder="Leave blank to keep current"
                            class="w-full p-3 rounded-lg bg-green-900 border border-green-800 focus:ring-red-500 focus:border-red-500 text-red-100 placeholder-gray-500">
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium mb-2 text-gray-300">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" minlength="8"
                            placeholder="Re-enter new password"
                            class="w-full p-3 rounded-lg bg-green-900 border border-green-800 focus:ring-red-500 focus:border-red-500 text-red-100 placeholder-gray-500">
                    </div>
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="w-full bg-green-700 px-6 py-3 rounded-xl hover:bg-green-600 font-bold text-lg transition shadow-lg shadow-green-900/40">
                        <i class="fa-solid fa-save mr-2"></i> Save Profile Changes
                    </button>
                </div>
            </form>
            
            <h3 class="text-xl font-semibold text-red-400 mb-4 border-b border-green-800/50 pb-2 pt-6">
                <i class="fa-solid fa-right-from-bracket mr-2"></i> Department Management
            </h3>

            <?php if (!empty($user['dept_id'])): ?>
                <div class="bg-red-950/30 p-4 rounded-lg border border-red-800 text-red-100 flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="font-semibold">Current Department: <span class="text-red-300" x-text="currentDept"></span></p>
                        <p class="text-sm text-gray-400">If you leave, you will be 'Unassigned' and lose department-specific access.</p>
                    </div>
                    <button type="button" @click="leaveDeptModalOpen = true" 
                        class="bg-red-700 px-4 py-2 rounded-lg hover:bg-red-600 font-semibold transition shadow-md whitespace-nowrap">
                        <i class="fa-solid fa-door-open mr-2"></i> Leave Department
                    </button>
                </div>
            <?php else: ?>
                <div class="bg-green-900/30 p-4 rounded-lg border border-green-700 text-green-100">
                    <p class="font-semibold">You are currently <b>Unassigned</b> to any department.</p>
                    <p class="text-sm text-gray-400">Contact an Organization Administrator to be assigned to a group.</p>
                </div>
            <?php endif; ?>

        </div> 
    </div>

    <div x-show="leaveDeptModalOpen" x-cloak 
        x-transition:enter="ease-out duration-300"
        x-transition:leave="ease-in duration-200"
        class="fixed inset-0 z-50 overflow-y-auto bg-maestro-bg bg-opacity-95 flex items-center justify-center" 
        style="display: none;">

        <div @click.outside="leaveDeptModalOpen = false" class="w-full max-w-md mx-auto bg-[#151a17] rounded-xl shadow-2xl border border-red-800 p-6">
            
            <div class="text-center">
                <i class="fa-solid fa-person-walking-arrow-loop-left text-4xl text-red-500 mb-4"></i>
                <h3 class="text-xl font-bold text-white mb-2">Confirm Leave Department</h3>
                
                <p class="text-gray-400 mb-6">
                    Are you sure you want to leave <strong class="text-red-400" x-text="currentDept"></strong>?
                    <span class="block text-sm text-red-400 font-semibold mt-1">This action cannot be undone by you and requires an Admin to re-assign you.</span>
                </p>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/org/profile/leave-department" class="space-y-4">
                <?php csrf_field(); ?>
                <input type="hidden" name="user_id" value="<?= $user['id'] ?? 0 ?>">
                <input type="hidden" name="confirm_leave" value="1">
                
                <div class="flex justify-center gap-4">
                    <button type="button" @click="leaveDeptModalOpen = false" class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition w-full">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="bg-red-700 hover:bg-red-600 text-white font-medium px-4 py-2 rounded-lg transition w-full">
                        <i class="fa-solid fa-door-open mr-1"></i> Confirm Leave
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>