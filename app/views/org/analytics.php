<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

// --- PHP Helper Functions ---
if (!defined('BASE_URL')) define('BASE_URL', '/maestro');
if (!function_exists('html_escape')) {
    function html_escape($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('is_admin_or_manager')) {
    function is_admin_or_manager() { 
        $admin_roles = ['Administrator', 'President', 'Adviser'];
        $current_role = $_SESSION['user_role'] ?? '';
        return in_array($current_role, $admin_roles);
    }
}
// --- End Helper Functions ---

$BASE_URL = BASE_URL ?? '';
$current_uri = $_SERVER['REQUEST_URI'] ?? '/org/analytics'; 

$is_documents_open = str_contains($current_uri, '/org/documents/');
$is_organization_open = str_contains($current_uri, '/org/members/') || str_contains($current_uri, '/org/departments') || str_contains($current_uri, '/org/roles');

$user_name = $_SESSION['user_name'] ?? 'User Name';
$first_initial = strtoupper(substr($user_name, 0, 1));

if (!is_admin_or_manager()) {
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Maestro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    // KEEPING ORIGINAL COLORS
                    colors: { 'sidebar-dark': '#0f1511', 'maestro-bg': '#0b0f0c' },
                    fontFamily: { poppins: ['Poppins', 'sans-serif'], sans: ['Poppins', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>body { font-family: 'Poppins', sans-serif; } .maestro-bg { background-color: #0b0f0c; }</style>
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
                
                <a href="<?=BASE_URL?>/org/analytics" class="flex items-center gap-3 p-3 rounded-lg transition <?= str_contains($current_uri, '/org/analytics') ? 'text-green-400 font-semibold bg-green-900/40' : 'hover:bg-green-700/50' ?>">
                    <i class="fa-solid fa-chart-line w-5 text-center"></i>
                    <span>Analytics</span>
                </a>
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

        <div class="border-t border-green-800 p-3 text-xs text-gray-500 text-center">Maestro Organization © <?=date('Y')?></div>
    </aside>

    <div class="ml-64 p-10 bg-maestro-bg min-h-screen text-white">
        
        <h1 class="text-3xl font-bold text-green-400 mb-6 tracking-wide">
            Organization Analytics
        </h1>

        <?php if (function_exists('flash_alert')) flash_alert(); ?>

        <div class="space-y-8 max-w-6xl mx-auto">

            <div class="bg-gray-800/50 p-6 rounded-xl border border-gray-700 shadow-lg">
                <h2 class="text-xl font-semibold text-white mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-download text-yellow-400"></i> Data Export & Reporting Console
                </h2>
                <p class="text-gray-400">
                    Select a dataset to generate and export. All reports are produced in <b>CSV format</b> and access is reserved for Administrators and Executives.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="bg-green-950/40 p-6 rounded-xl border border-green-700/70 shadow-2xl shadow-green-900/20 flex flex-col justify-between transition-all duration-300 hover:shadow-green-700/30 hover:border-green-600">
                    <div>
                        <div class="flex items-center mb-4">
                            <i class="fa-solid fa-users text-4xl text-green-400 mr-4"></i>
                            <h3 class="text-xl font-bold text-green-300">Member Records Export</h3>
                        </div>
                        <p class="text-sm text-gray-300 mb-4">
                            Generates a detailed CSV file containing all user accounts, including personal data, roles, and department assignments.
                        </p>
                        <ul class="list-disc list-inside text-xs text-gray-400 ml-4 mb-6">
                            <li><b>Fields:</b> Name, Email, Department, Role, Status.</li>
                            <li><b>Security:</b> Administrator access only.</li>
                        </ul>
                    </div>
                    <a href="<?= BASE_URL ?>/org/analytics/download/members" class="w-full bg-green-600 px-6 py-3 rounded-full hover:bg-green-500 font-bold text-white text-lg transition text-center shadow-md hover:shadow-lg">
                        <i class="fa-solid fa-file-csv mr-2"></i> Generate Member CSV
                    </a>
                </div>
                
                <div class="bg-yellow-950/40 p-6 rounded-xl border border-yellow-700/70 shadow-2xl shadow-yellow-900/20 flex flex-col justify-between transition-all duration-300 hover:shadow-yellow-700/30 hover:border-yellow-600">
                    <div>
                        <div class="flex items-center mb-4">
                            <i class="fa-solid fa-file-invoice text-4xl text-yellow-400 mr-4"></i>
                            <h3 class="text-xl font-bold text-yellow-300">Document Records Export</h3>
                        </div>
                        <p class="text-sm text-gray-300 mb-4">
                            Generates a comprehensive CSV file detailing the lifecycle of all documents in the system.
                        </p>
                        <ul class="list-disc list-inside text-xs text-gray-400 ml-4 mb-6">
                            <li><b>Fields:</b> Title, Type, Submission Date, Current Status, Reviewer.</li>
                            <li><b>Security:</b> Administrator access only.</li>
                        </ul>
                    </div>
                    <a href="<?= BASE_URL ?>/org/analytics/download/documents" class="w-full bg-yellow-600 px-6 py-3 rounded-full hover:bg-yellow-500 font-bold text-gray-900 text-lg transition text-center shadow-md hover:shadow-lg">
                        <i class="fa-solid fa-file-csv mr-2"></i> Generate Document CSV
                    </a>
                </div>
            </div>

            <p class="text-xs text-gray-500 pt-2 max-w-full text-center">
                Note: Exported data is an up-to-the-minute snapshot and includes all active records.
            </p>
        </div> 
    </div>
</body>
</html>