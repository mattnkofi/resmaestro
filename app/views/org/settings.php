<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Settings - Maestro UI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Poppins Font Import -->
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
<!-- Applying font-poppins explicitly to the body tag -->
<body class="bg-maestro-bg text-white font-poppins" x-data="{}">

    <?php 
    // MOCKING CURRENT URI FOR DEMONSTRATION: 
    // For "Settings" page:
    $current_uri = $_SERVER['REQUEST_URI'] ?? '/org/settings'; 

    // PHP LOGIC TO DETERMINE IF A DROPDOWN SHOULD BE OPEN
    $is_documents_open = str_contains($current_uri, '/org/documents/');
    $is_review_open = str_contains($current_uri, '/org/review/');
    $is_organization_open = str_contains($current_uri, '/org/members/') || str_contains($current_uri, '/org/departments') || str_contains($current_uri, '/org/roles');
    $is_reports_open = str_contains($current_uri, '/org/reports/');
    ?>

    <!-- START SIDEBAR CONTENT -->
    <aside class="fixed top-0 left-0 h-full w-64 bg-[#0b0f0c] border-r border-green-900 text-white shadow-2xl flex flex-col transition-all duration-300 z-10">
        <div class="flex items-center justify-center py-6 border-b border-green-800">
            <!-- Placeholder for logo image -->
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

            <!-- Documents Dropdown -->
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
                    <a href="<?=BASE_URL?>/org/documents/pending" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/pending') ? 'text-green-400 font-semibold' : '' ?>">Pending Review</a>
                    <a href="<?=BASE_URL?>/org/documents/approved" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/approved') ? 'text-green-400 font-semibold' : '' ?>">Approved / Noted</a>
                    <a href="<?=BASE_URL?>/org/documents/rejected" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/rejected') ? 'text-green-400 font-semibold' : '' ?>">Rejected</a>
                    <a href="<?=BASE_URL?>/org/documents/archived" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/archived') ? 'text-green-400 font-semibold' : '' ?>">Archived</a>
                </div>
            </div>

            <!-- Review & Workflow Dropdown -->
            <div x-data='{ open: <?= $is_review_open ? 'true' : 'false' ?> }' class="space-y-1">
                <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-clipboard-check w-5 text-center"></i>
                        <span>Review & Workflow</span>
                    </span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                </button>
                <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?=BASE_URL?>/org/review/queue" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/review/queue') ? 'text-green-400 font-semibold' : '' ?>">Pending Reviews</a>
                    <a href="<?=BASE_URL?>/org/review/history" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/review/history') ? 'text-green-400 font-semibold' : '' ?>">Review History</a>
                    <a href="<?=BASE_URL?>/org/review/comments" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/review/comments') ? 'text-green-400 font-semibold' : '' ?>">Comment Threads</a>
                </div>
            </div>

            <!-- Organization Dropdown -->
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

            <!-- Reports Dropdown -->
            <div x-data='{ open: <?= $is_reports_open ? 'true' : 'false' ?> }' class="space-y-1">
                <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-chart-line w-5 text-center"></i>
                        <span>Reports & Analytics</span>
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
                    <a href="<?=BASE_URL?>/org/settings" class="block px-4 py-2 hover:bg-green-700/30 transition">Settings</a>
                    <a href="<?=BASE_URL?>/logout" class="block px-4 py-2 text-red-400 hover:bg-red-700/30 rounded-b-lg transition">Logout</a>
                </div>
            </div>
        </div>

        <div class="border-t border-green-800 p-3 text-xs text-gray-500 text-center">
            Maestro Organization © <?=date('Y')?>
        </div>
    </aside>
    <!-- END SIDEBAR CONTENT -->

    <!-- Main Content Area -->
    <div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white">
        
        <h1 class="text-3xl font-bold text-green-400 mb-8 tracking-wide">
            Settings
        </h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column: General & Notification Settings (2/3 width) -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- General Settings Form -->
                <form class="bg-green-950/50 p-8 rounded-xl space-y-6 border border-green-800 shadow-2xl shadow-green-900/10">
                    
                    <h2 class="text-xl font-semibold text-green-300 mb-4 border-b border-green-800/50 pb-2 flex items-center gap-3">
                        <i class="fa-solid fa-gear text-lg"></i> General Organization Settings
                    </h2>
                    
                    <!-- Organization Name -->
                    <div>
                        <label for="org_name" class="block text-sm font-medium mb-2 text-gray-300">Organization Name</label>
                        <input type="text" id="org_name" value="Maestro Organization"
                               class="w-full p-3 rounded-lg bg-green-900 border border-green-800 focus:ring-green-500 focus:border-green-500 text-green-100" required>
                    </div>

                    <!-- Base URL/Domain -->
                    <div>
                        <label for="base_url" class="block text-sm font-medium mb-2 text-gray-300">System Domain / Base URL</label>
                        <input type="text" id="base_url" value="https://maestro-docs.com"
                               class="w-full p-3 rounded-lg bg-green-900 border border-green-800 focus:ring-green-500 focus:border-green-500 text-green-100" readonly>
                        <p class="mt-1 text-xs text-gray-500">Contact IT support to change the core domain.</p>
                    </div>

                    <!-- Default Language -->
                    <div>
                        <label for="language" class="block text-sm font-medium mb-2 text-gray-300">Default Language</label>
                        <select id="language" class="w-full p-3 rounded-lg bg-green-900 border border-green-800 focus:ring-green-500 focus:border-green-500 text-green-100">
                            <option>English (US)</option>
                            <option>Spanish</option>
                            <option>French</option>
                        </select>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="bg-green-700 px-6 py-3 rounded-xl hover:bg-green-600 font-bold text-lg transition shadow-lg shadow-green-900/40">
                            <i class="fa-solid fa-save mr-2"></i> Save General Settings
                        </button>
                    </div>
                </form>

                <!-- Notification Settings Form -->
                <form class="bg-green-950/50 p-8 rounded-xl space-y-6 border border-green-800 shadow-2xl shadow-green-900/10">
                    
                    <h2 class="text-xl font-semibold text-green-300 mb-4 border-b border-green-800/50 pb-2 flex items-center gap-3">
                        <i class="fa-solid fa-bell text-lg"></i> Notification Preferences
                    </h2>
                    
                    <!-- Email Notifications -->
                    <div>
                        <label for="email_notifications" class="block text-sm font-medium mb-2 text-gray-300">Global Email Notifications</label>
                        <select id="email_notifications" class="w-full p-3 rounded-lg bg-green-900 border border-green-800 focus:ring-green-500 focus:border-green-500 text-green-100">
                            <option>Enabled (All Alerts)</option>
                            <option>Disabled (Critical Only)</option>
                            <option>Off</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Controls system-wide notification emails (e.g., overdue reviews).</p>
                    </div>

                    <!-- Push Notifications (Mock Toggle) -->
                    <div class="flex justify-between items-center bg-green-900/30 p-3 rounded-lg border border-green-800">
                        <label class="text-sm font-medium text-gray-300">Browser Push Notifications</label>
                        <div x-data="{ enabled: true }" class="flex items-center">
                            <button @click="enabled = !enabled" :class="enabled ? 'bg-green-600' : 'bg-gray-600'"
                                    class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <span :class="enabled ? 'translate-x-5' : 'translate-x-0'"
                                      class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="bg-green-700 px-6 py-3 rounded-xl hover:bg-green-600 font-bold text-lg transition shadow-lg shadow-green-900/40">
                            <i class="fa-solid fa-save mr-2"></i> Update Notification Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Column: Security & Action Panel (1/3 width) -->
            <div class="lg:col-span-1 space-y-8">
                
                <!-- Security Policy Panel -->
                <div class="bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-2xl shadow-red-900/10">
                    <h2 class="text-xl font-semibold text-red-400 mb-4 flex items-center gap-3 border-b border-green-800/50 pb-2">
                        <i class="fa-solid fa-lock text-lg"></i> Security & Policy
                    </h2>
                    
                    <ul class="space-y-3 text-sm">
                        <!-- Password Policy -->
                        <li class="flex justify-between items-center pb-2 border-b border-green-900/30">
                            <span class="text-gray-300">Password Length</span>
                            <span class="font-medium text-yellow-400">Min. 12 Characters</span>
                        </li>
                        
                        <!-- 2FA Status -->
                        <li class="flex justify-between items-center pb-2 border-b border-green-900/30">
                            <span class="text-gray-300">Two-Factor Authentication (2FA)</span>
                            <span class="font-bold text-green-400 flex items-center gap-2">
                                Mandatory <i class="fa-solid fa-check-circle text-sm"></i>
                            </span>
                        </li>
                        
                        <!-- Session Timeout -->
                        <li class="flex justify-between items-center">
                            <span class="text-gray-300">Session Timeout</span>
                            <span class="font-medium text-blue-400">30 Minutes (Inactivity)</span>
                        </li>
                    </ul>
                    <button class="w-full bg-red-700/40 text-red-300 p-3 rounded-xl hover:bg-red-700/60 transition mt-6">
                        Manage Security Policies
                    </button>
                </div>
                
                <!-- System Action Panel -->
                <div class="bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-2xl shadow-green-900/10">
                    <h2 class="text-xl font-semibold text-blue-400 mb-4 flex items-center gap-3 border-b border-green-800/50 pb-2">
                        <i class="fa-solid fa-undo text-lg"></i> Advanced Actions
                    </h2>
                    
                    <button class="w-full bg-blue-700/40 text-blue-300 p-3 rounded-xl hover:bg-blue-700/60 transition mb-3">
                        <i class="fa-solid fa-history mr-2"></i> System Activity Log
                    </button>
                    
                    <button class="w-full bg-gray-700/40 text-gray-300 p-3 rounded-xl hover:bg-gray-700/60 transition">
                        <i class="fa-solid fa-file-export mr-2"></i> Export All Data
                    </button>
                </div>
                
            </div>

        </div> <!-- End Grid -->

    </div> <!-- End Main Content Area -->

</body>
</html>
