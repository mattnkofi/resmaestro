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
// Mock set_value function for retaining form input after validation errors
if (!function_exists('set_value')) {
    function set_value($field) {
        // Retrieves the submitted value from the global POST array
        return $_POST[$field] ?? null; 
    }
}
// --- End Helper Functions ---

// Data variables passed from OrgController::members_add()
$departments = $departments ?? [];
$roles = $roles ?? [];

// MOCKING CURRENT URI FOR SIDEBAR: 
$BASE_URL = BASE_URL ?? '';
$current_uri = $_SERVER['REQUEST_URI'] ?? '/org/members/add'; 

// PHP LOGIC TO DETERMINE IF A DROPDOWN SHOULD BE OPEN
$is_documents_open = str_contains($current_uri, '/org/documents/');
$is_review_open = str_contains($current_uri, '/org/review/');
$is_organization_open = str_contains($current_uri, '/org/members/') || str_contains($current_uri, '/org/departments') || str_contains($current_uri, '/org/roles');
$is_reports_open = str_contains($current_uri, '/org/reports/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Existing Member - Maestro UI</title>
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
<body class="bg-maestro-bg text-white font-poppins" x-data="{}">

    <aside class="fixed top-0 left-0 h-full w-64 bg-[#0b0f0c] border-r border-green-900 text-white shadow-2xl flex flex-col transition-all duration-300 z-10">
        <div class="flex items-center justify-center py-6 border-b border-green-800">
            <img src="/public/maestrologo.png" alt="Logo" class="h-10 mr-8">
            <h1 class="text-2xl font-bold text-green-400 tracking-wider">MAESTRO</h1>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-3 space-y-4">

            <div>
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2 ml-2 tracking-wider">Main</h2>
                <a href="<?= $BASE_URL ?>/org/dashboard" class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-700/50 transition
                    <?= $current_uri == $BASE_URL.'/org/dashboard' ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">
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
                    <a href="<?= $BASE_URL ?>/org/documents/all" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/all') ? 'text-green-400 font-semibold' : '' ?>">All Documents</a>
                    <a href="<?= $BASE_URL ?>/org/documents/upload" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/upload') ? 'text-green-400 font-semibold' : '' ?>">Upload New</a>
                    <a href="<?= $BASE_URL ?>/org/documents/approved" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/approved') ? 'text-green-400 font-semibold' : '' ?>">Approved / Noted</a>
                    <a href="<?= $BASE_URL ?>/org/documents/rejected" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/rejected') ? 'text-green-400 font-semibold' : '' ?>">Rejected</a>
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
                    <a href="<?= $BASE_URL ?>/org/members/list" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/members/list') ? 'text-green-400 font-semibold' : '' ?>">Members</a>
                    <a href="<?= $BASE_URL ?>/org/members/add" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/members/add') ? 'text-green-400 font-semibold' : '' ?>">Add Member</a>
                    <a href="<?= $BASE_URL ?>/org/departments" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/departments') ? 'text-green-400 font-semibold' : '' ?>">Departments</a>
                    <a href="<?= $BASE_URL ?>/org/roles" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/roles') ? 'text-green-400 font-semibold' : '' ?>">Roles & Permissions</a>
                </div>
            </div>

            <div class="pt-4">
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2 ml-2 tracking-wider">System</h2>
            </div>
            
            <div>
                <a href="<?= $BASE_URL ?>/org/settings" class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-700/30 transition <?= str_contains($current_uri, '/org/settings') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">
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
            Add Existing Member
        </h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2">
                <form method="POST" action="<?= $BASE_URL ?>/org/members/store" class="bg-green-950/50 p-8 rounded-xl space-y-6 border border-green-800 shadow-2xl shadow-green-900/10">
                    <?php 
                    // This section shows flash alerts (like validation errors)
                    if (function_exists('flash_alert')) flash_alert(); 
                    csrf_field(); 
                    ?>
                    
                    <h2 class="text-xl font-semibold text-green-300 mb-4 border-b border-green-800/50 pb-2">Member Assignment</h2>

                    <p class="text-sm text-gray-400">Enter the <b>registered email address</b> of the user you wish to add to the organization, then assign their role and department.</p>

                    <div>
                        <label for="email" class="block text-sm font-medium mb-2 text-gray-300">Registered Email Address</label>
                        <input type="email" id="email" name="email" 
                            class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-green-500 focus:border-green-500 text-green-100" 
                            value="<?= html_escape(set_value('email') ?? '') ?>" placeholder="user@example.com" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div>
                            <label for="role_id" class="block text-sm font-medium mb-2 text-gray-300">Assign Role</label>
                            <select id="role_id" name="role_id" 
                                class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-green-500 focus:border-green-500 text-green-100" required>
                                <option value="">Select a Role</option>
                                <?php foreach($roles as $role): ?>
                                    <option value="<?= html_escape($role['id']) ?>" <?= set_value('role_id') == $role['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($roles)): ?><p class="mt-1 text-xs text-red-400">Warning: No Roles found in DB.</p><?php endif; ?>
                        </div>
                        
                        <div>
                            <label for="dept_id" class="block text-sm font-medium mb-2 text-gray-300">Assign Department</label>
                            <select id="dept_id" name="dept_id" 
                                class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-green-500 focus:border-green-500 text-green-100" required>
                                <option value="">Select a Department</option>
                                <?php foreach($departments as $dept): ?>
                                    <option value="<?= html_escape($dept['id']) ?>" <?= set_value('dept_id') == $dept['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($dept['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($departments)): ?><p class="mt-1 text-xs text-red-400">Warning: No Departments found in DB.</p><?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="pt-4">
                        <button type="submit" class="w-full bg-green-700 px-6 py-3 rounded-xl hover:bg-green-600 font-bold text-lg transition shadow-lg shadow-green-900/40">
                            <i class="fa-solid fa-user-check mr-2"></i> Add Existing Member
                        </button>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-1 space-y-8">
                
                <div class="bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-2xl shadow-green-900/10 h-full">
                    <h2 class="text-xl font-bold text-green-300 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-handshake text-green-500"></i> Organization Enrollment
                    </h2>
                    <p class="text-sm text-gray-400 mb-4">This form is for adding users who have already completed the initial signup process on the public portal.</p>
                    <ul class="list-disc list-inside space-y-2 text-gray-300 text-sm">
                        <li>The user must have an active, existing account.</li>
                        <li>This action only updates their <b>Role</b> and <b>Department</b>.</li>
                        <li>No password is changed or created by this form.</li>
                        <li>If the user does not exist, an error will be displayed.</li>
                    </ul>
                    <p class="mt-4 text-xs text-gray-500">
                        To register a brand new user, please direct them to the sign-up page (<?= $BASE_URL ?>/register).
                    </p>
                </div>

            </div>

        </div> </div> </body>
</html>