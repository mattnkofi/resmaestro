<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles & Permissions - Maestro UI (Read-Only)</title>
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
        /* Custom scrollbar for consistency with the existing green/gray theme */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #0f1511; 
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #1a3626; 
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #10b981; 
        }
    </style>
</head>
<body class="bg-maestro-bg text-white font-poppins" x-data="{}">

    <?php 
    // MOCKING CURRENT URI FOR DEMONSTRATION: 
    // For "Roles" page:
    $current_uri = $_SERVER['REQUEST_URI'] ?? '/org/roles'; 

    // PHP LOGIC TO DETERMINE IF A DROPDOWN SHOULD BE OPEN
    $is_documents_open = str_contains($current_uri, '/org/documents/');
    $is_review_open = str_contains($current_uri, '/org/review/');
    $is_organization_open = str_contains($current_uri, '/org/members/') || str_contains($current_uri, '/org/departments') || str_contains($current_uri, '/org/roles');
    $is_reports_open = str_contains($current_uri, '/org/reports/');
    ?>

    <aside class="fixed top-0 left-0 h-full w-64 bg-[#0b0f0c] border-r border-green-900 text-white shadow-2xl flex flex-col transition-all duration-300 z-10">
        <div class="flex items-center justify-center py-6 border-b border-green-800">
            <img src="/public/maestrologo.png" alt="Logo" class="h-10 mr-8">
            <h1 class="text-2xl font-bold text-green-400 tracking-wider">MAESTRO</h1>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-3 space-y-4 custom-scrollbar">

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
                    <a href="<?=BASE_URL?>/org/documents/all" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/all') ? 'text-green-400 font-semibold' : '' ?>">All Documents</a>
                    <a href="<?=BASE_URL?>/org/documents/upload" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/upload') ? 'text-green-400 font-semibold' : '' ?>">Upload New</a>
                    <a href="<?=BASE_URL?>/org/documents/approved" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/approved') ? 'text-green-400 font-semibold' : '' ?>">Approved / Noted</a>
                    <a href="<?=BASE_URL?>/org/documents/rejected" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/rejected') ? 'text-green-400 font-semibold' : '' ?>">Rejected</a>
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
                    <a href="<?=BASE_URL?>/org/members/list" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/members/list') ? 'text-green-400 font-semibold' : '' ?>">Members</a>
                    <a href="<?=BASE_URL?>/org/members/add" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/members/add') ? 'text-green-400 font-semibold' : '' ?>">Add Member</a>
                    <a href="<?=BASE_URL?>/org/departments" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/departments') ? 'text-green-400 font-semibold' : '' ?>">Departments</a>
                    <a href="<?=BASE_URL?>/org/roles" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/roles') ? 'text-green-400 font-semibold' : '' ?>">Roles & Permissions</a>
                </div>
            </div>

            <div x-data='{ open: <?= $is_reports_open ? 'true' : 'false' ?> }' class="space-y-1">
                <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-chart-line w-5 text-center"></i>
                        <span>Analytics</span>
                    </span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                </button>
                <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?=BASE_URL?>/org/reports/overview" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/reports/overview') ? 'text-green-400 font-semibold' : '' ?>">Overview</a>
                    <a href="<?=BASE_URL?>/org/reports/documents" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/reports/documents') ? 'text-green-400 font-semibold' : '' ?>">Document Analytics</a>
                    <a href="<?=BASE_URL?>/org/reports/reviewers" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/reports/reviewers') ? 'text-green-400 font-semibold' : '' ?>">Reviewer Activity</a>
                    <a href="<?=BASE_URL?>/org/reports/storage" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/reports/storage') ? 'text-green-400 font-semibold' : '' ?>">Storage Usage</a>
                </div>
            </div>

            <div class="pt-4">
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2 ml-2 tracking-wider">System</h2>
            </div>
            
            <div>
                <a href="<?=BASE_URL?>/org/settings" class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-700/30 transition <?= str_contains($current_uri, '/org/settings') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">
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
                    <a href="<?=BASE_URL?>/org/profile" class="block px-4 py-2 hover:bg-green-700/30 rounded-t-lg transition">View Profile</a>
                    <a href="<?=BASE_URL?>/org/settings" class="block px-4 py-2 hover:bg-green-700/30 transition">Settings</a>
                    <a href="<?=BASE_URL?>/logout" class="block px-4 py-2 text-red-400 hover:bg-red-700/30 rounded-b-lg transition">Logout</a>
                </div>
            </div>
        </div>

        <div class="border-t border-green-800 p-3 text-xs text-gray-500 text-center">
            Maestro Organization © <?=date('Y')?>
        </div>
    </aside>

    <div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white">
        <h1 class="text-3xl font-bold text-green-400 mb-8 tracking-wide">
            Roles & Permissions Overview
        </h1>

        <div class="bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-xl mb-8">
            <h2 class="text-xl font-semibold text-green-300 flex items-center gap-3">
                <i class="fa-solid fa-users-gear text-2xl"></i> Defined Organizational Roles
            </h2>
            <p class="text-sm text-gray-500 mt-1">Below are the established roles and their high-level access permissions within the organization.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <?php
            // Mock data for the roles list (retained for static design)
            $roles = [
                ['name' => 'President', 'description' => 'Highest authority. Full control over users, documents, and system settings.', 'access' => 'Full Access'],
                ['name' => 'Adviser', 'description' => 'Executive oversight. Full control over users, documents, and system settings.', 'access' => 'Full Access'],
                ['name' => 'Secretary', 'description' => 'Document uploading, resubmission, management, and viewing of rejected documents.', 'access' => 'Upload & Manage'],
                ['name' => 'Executive Member', 'description' => 'Can approve/reject documents. This is a unique, unshareable role within each department.', 'access' => 'Exclusive Reviewer'],
                ['name' => 'Treasurer', 'description' => 'Can view Organization lists (Members, Departments) and the main Reports overview (financial/budget).', 'access' => 'View Org & Basic Reports'],
                ['name' => 'General Member', 'description' => 'Can upload/resubmit documents and view their status. Can only view/download Approved documents.', 'access' => 'Read-Only + Upload/Resubmit'],
            ];

            foreach($roles as $role): 
                // Set all borders and access text to standard green/gray colors
                $border_color = 'border-green-800';
                $text_color = 'text-gray-400';
            ?>
            <div class="bg-green-950/50 p-5 rounded-xl border-l-4 <?= $border_color ?> flex flex-col shadow-lg hover:bg-green-900/10 transition duration-200">
                <h3 class="text-lg font-semibold text-green-200 mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-user-tag text-green-400"></i> <?= $role['name'] ?>
                </h3>
                <p class="text-sm text-gray-500 mb-4 flex-grow"><?= $role['description'] ?></p>
                
                <div class="flex justify-between items-center pt-3 border-t border-green-800/50">
                    <span class="text-sm font-medium text-green-400"><i class="fa-solid fa-shield-alt mr-1"></i> Access: </span>
                    <span class="text-sm font-medium <?= $text_color ?>"><?= $role['access'] ?></span>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($roles)): ?>
            <div class="lg:col-span-3 p-10 text-center text-gray-500 bg-green-950/20 rounded-xl border border-green-800 flex flex-col items-center justify-center">
                <i class="fa-solid fa-lock text-5xl mb-4 text-green-500"></i>
                <p class="text-xl">No roles have been defined for this organization.</p>
            </div>
            <?php endif; ?>
        </div>

        <div class="mt-10 bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-xl">
            <h2 class="text-xl font-semibold text-green-300 mb-4 flex items-center gap-3 border-b border-green-800/50 pb-3">
                <i class="fa-solid fa-list-check text-2xl"></i> Detailed Permissions Matrix
            </h2>
            <p class="text-sm text-gray-400 mb-6">Explore the granular permissions for each functional area within Maestro. Click on a category to expand.</p>

            <div class="space-y-4">
                <div x-data="{ open: false }" class="bg-green-900/30 rounded-lg border border-green-800">
                    <button @click="open = !open" class="w-full flex justify-between items-center p-4 text-green-200 font-semibold hover:bg-green-700/40 transition rounded-lg">
                        <span><i class="fa-solid fa-file-invoice mr-3"></i> Document Management (Admin/Full Access)</span>
                        <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                    </button>
                    <div x-show="open" x-transition.duration.300ms class="px-6 py-4 bg-green-950/40 border-t border-green-800 rounded-b-lg">
                        <ul class="space-y-2 text-gray-300 text-sm">
                            <li class="flex justify-between items-center pb-1"><span>View All Documents</span> <i class="fa-solid fa-check text-green-500"></i></li>
                            <li class="flex justify-between items-center pb-1"><span>Upload/Edit Any Document</span> <i class="fa-solid fa-check text-green-500"></i></li>
                            <li class="flex justify-between items-center pb-1"><span>Archive/Delete Documents</span> <i class="fa-solid fa-check text-green-500"></i></li>
                            <li class="flex justify-between items-center pb-1"><span>Manage Document Types</span> <i class="fa-solid fa-check text-green-500"></i></li>
                            <li class="flex justify-between items-center pb-1"><span>Configure Review Workflows</span> <i class="fa-solid fa-check text-green-500"></i></li>
                        </ul>
                    </div>
                </div>

                <div x-data="{ open: false }" class="bg-green-900/30 rounded-lg border border-green-800">
                    <button @click="open = !open" class="w-full flex justify-between items-center p-4 text-green-200 font-semibold hover:bg-green-700/40 transition rounded-lg">
                        <span><i class="fa-solid fa-user-group mr-3"></i> User & Organization Management (Admin/Full Access)</span>
                        <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                    </button>
                    <div x-show="open" x-transition.duration.300ms class="px-6 py-4 bg-green-950/40 border-t border-green-800 rounded-b-lg">
                        <ul class="space-y-2 text-gray-300 text-sm">
                            <li class="flex justify-between items-center pb-1"><span>Manage Members</span> <i class="fa-solid fa-check text-green-500"></i></li>
                            <li class="flex justify-between items-center pb-1"><span>Assign/Edit Roles</span> <i class="fa-solid fa-check text-green-500"></i></li>
                            <li class="flex justify-between items-center pb-1"><span>Manage Departments</span> <i class="fa-solid fa-check text-green-500"></i></li>
                            <li class="flex justify-between items-center pb-1"><span>Create Custom Roles</span> <i class="fa-solid fa-check text-green-500"></i></li>
                        </ul>
                    </div>
                </div>

                <div x-data="{ open: false }" class="bg-green-900/30 rounded-lg border border-green-800">
                    <button @click="open = !open" class="w-full flex justify-between items-center p-4 text-green-200 font-semibold hover:bg-green-700/40 transition rounded-lg">
                        <span><i class="fa-solid fa-chart-pie mr-3"></i> Analytics & System Settings (Admin/Full Access)</span>
                        <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                    </button>
                    <div x-show="open" x-transition.duration.300ms class="px-6 py-4 bg-green-950/40 border-t border-green-800 rounded-b-lg">
                        <ul class="space-y-2 text-gray-300 text-sm">
                            <li class="flex justify-between items-center pb-1"><span>View All Reports</span> <i class="fa-solid fa-check text-green-500"></i></li>
                            <li class="flex justify-between items-center pb-1"><span>Access System Settings</span> <i class="fa-solid fa-check text-green-500"></i></li>
                            <li class="flex justify-between items-center pb-1"><span>Manage Integrations</span> <i class="fa-solid fa-check text-green-500"></i></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>