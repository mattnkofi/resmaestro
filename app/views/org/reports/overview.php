<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Overview - Maestro UI</title>
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
        
        /* Enhanced Card Style for Reports */
        .dashboard-card {
            transition: all 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-2px); 
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.15); /* Stronger subtle green glow */
        }
    </style>
</head>
<!-- Applying font-poppins explicitly to the body tag -->
<body class="bg-maestro-bg text-white font-poppins" x-data="{}">

    <?php 
    // MOCKING CURRENT URI FOR DEMONSTRATION: 
    // For "Reports Overview" page:
    $current_uri = $_SERVER['REQUEST_URI'] ?? '/org/reports/overview'; 

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
                        <span>Reviews</span>
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

            <!-- Reports Dropdown (This will be open) -->
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
            Reports Overview
        </h1>

        <!-- Top Row: Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

            <!-- Document Stats Card -->
            <div class="dashboard-card bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-xl">
                <h2 class="text-xl font-semibold text-green-300 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice text-2xl"></i> Document Stats
                </h2>
                <div class="space-y-2 text-lg">
                    <p class="flex justify-between items-center">
                        <span><i class="fa-solid fa-circle-check text-green-500 mr-2"></i> Approved:</span>
                        <span class="font-bold text-green-400">140</span>
                    </p>
                    <p class="flex justify-between items-center">
                        <span><i class="fa-solid fa-hourglass-half text-yellow-500 mr-2"></i> Pending:</span>
                        <span class="font-bold text-yellow-400">24</span>
                    </p>
                    <p class="flex justify-between items-center">
                        <span><i class="fa-solid fa-circle-xmark text-red-500 mr-2"></i> Rejected:</span>
                        <span class="font-bold text-red-400">12</span>
                    </p>
                </div>
                <a href="<?=BASE_URL?>/org/reports/documents" class="mt-4 block text-sm text-green-400 hover:text-green-300">View detailed document analytics &rarr;</a>
            </div>

            <!-- Storage Usage Card -->
            <div class="dashboard-card bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-xl">
                <h2 class="text-xl font-semibold text-green-300 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-database text-2xl"></i> Storage Usage
                </h2>
                
                <div class="w-full bg-green-900 rounded-full h-4 mb-2">
                    <div class="bg-green-500 h-4 rounded-full transition-all duration-700" style="width:70%"></div>
                </div>
                
                <p class="text-sm mt-2 text-gray-300 font-semibold">7.0 GB / 10.0 GB used</p>
                <p class="text-xs text-gray-500">70% of organizational quota used.</p>
                
                <a href="<?=BASE_URL?>/org/reports/storage" class="mt-4 block text-sm text-green-400 hover:text-green-300">Manage storage &rarr;</a>
            </div>

            <!-- Reviewer Activity Summary Card -->
            <div class="dashboard-card bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-xl">
                <h2 class="text-xl font-semibold text-green-300 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-user-check text-2xl"></i> Reviewer Activity
                </h2>
                <div class="space-y-2 text-lg">
                    <p class="flex justify-between">
                        <span>Reviews Completed:</span>
                        <span class="font-bold text-green-400">180</span>
                    </p>
                    <p class="flex justify-between">
                        <span>Avg. Review Time:</span>
                        <span class="font-bold text-yellow-400">1.5 Days</span>
                    </p>
                    <p class="flex justify-between">
                        <span>Active Reviewers:</span>
                        <span class="font-bold text-blue-400">15</span>
                    </p>
                </div>
                <a href="<?=BASE_URL?>/org/reports/reviewers" class="mt-4 block text-sm text-green-400 hover:text-green-300">View reviewer metrics &rarr;</a>
            </div>
            
        </div>
        
        <!-- Bottom Row: Chart and Top Performers -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Document Trend Chart Placeholder (2/3 width) -->
            <div class="lg:col-span-2 dashboard-card bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-xl h-96">
                <h2 class="text-xl font-semibold text-green-300 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-chart-bar text-2xl"></i> Approval/Rejection Trends (Last 6 Months)
                </h2>
                <div class="h-64 flex items-center justify-center text-gray-500 border border-dashed border-green-800/50 rounded-lg">
                    Document Flow Chart Placeholder (e.g., using D3.js or Recharts)
                </div>
            </div>

            <!-- Top Reviewers Card (1/3 width) -->
            <div class="lg:col-span-1 dashboard-card bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-xl h-96">
                <h2 class="text-xl font-semibold text-green-300 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-star text-2xl text-yellow-400"></i> Top Reviewers (Volume)
                </h2>
                <ul class="space-y-3">
                    <?php
                    $top_reviewers = [
                        ['name' => 'Admin User', 'count' => 55],
                        ['name' => 'Jane Doe', 'count' => 48],
                        ['name' => 'John Smith', 'count' => 37],
                        ['name' => 'Ellaine Cordero', 'count' => 31],
                        ['name' => 'Reviewer 5', 'count' => 22],
                    ];
                    foreach($top_reviewers as $reviewer): ?>
                    <li class="flex justify-between items-center p-3 bg-green-900/30 rounded-lg hover:bg-green-900/50 transition">
                        <span class="font-medium text-green-200"><?= $reviewer['name'] ?></span>
                        <span class="text-sm text-green-400 font-bold"><?= $reviewer['count'] ?> Reviews</span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="<?=BASE_URL?>/org/reports/reviewers" class="mt-4 block text-sm text-green-400 hover:text-green-300">Full reviewer leaderboard &rarr;</a>
            </div>

        </div>


    </div>

</body>
</html>