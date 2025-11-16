<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments List - Maestro UI</title>
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
    // For "Departments" page:
    $current_uri = $_SERVER['REQUEST_URI'] ?? '/org/departments'; 

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

            <!-- Organization Dropdown (This will be open) -->
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
        
        <h1 class="text-3xl font-bold text-green-400 mb-6 tracking-wide">
            Departments
        </h1>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <input type="text" placeholder="Search departments..." 
                class="w-full md:w-1/3 bg-green-900/50 border border-green-800 p-3 rounded-xl focus:ring-green-500 focus:border-green-500 transition placeholder-gray-500 text-white mb-4 sm:mb-0">
            <button class="bg-green-700 hover:bg-green-600 px-5 py-2.5 rounded-xl font-medium transition shadow-lg shadow-green-900/40">
                <i class="fa-solid fa-plus mr-2"></i> Add New Department
            </button>
        </div>

        <?php
        // Mock data for the department list
        $departments = [
            ['name' => 'Executive Department', 'members' => 3, 'documents' => 12, 'pending' => 1],
            ['name' => 'Finance Department', 'members' => 10, 'documents' => 45, 'pending' => 4],
            ['name' => 'Operations Department', 'members' => 25, 'documents' => 98, 'pending' => 8],
            ['name' => 'Legal Department', 'members' => 5, 'documents' => 22, 'pending' => 0],
            ['name' => 'Human Resources (HR)', 'members' => 8, 'documents' => 30, 'pending' => 2],
            ['name' => 'Marketing & Sales', 'members' => 15, 'documents' => 60, 'pending' => 3],
        ];

        // Stats Calculation
        $total_depts = count($departments);
        $total_members_in_depts = array_sum(array_column($departments, 'members'));
        $total_documents_in_depts = array_sum(array_column($departments, 'documents'));
        ?>
        
        <!-- NEW: Department Statistics Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Card 1: Total Departments -->
            <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 uppercase tracking-wider">Total Departments</p>
                    <p class="text-3xl font-bold text-green-400 mt-1"><?= $total_depts ?></p>
                </div>
                <i class="fa-solid fa-sitemap text-4xl text-green-700/50"></i>
            </div>

            <!-- Card 2: Total Members Managed -->
            <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 uppercase tracking-wider">Total Members</p>
                    <p class="text-3xl font-bold text-blue-400 mt-1"><?= $total_members_in_depts ?></p>
                </div>
                <i class="fa-solid fa-users text-4xl text-blue-700/50"></i>
            </div>

            <!-- Card 3: Total Documents Managed -->
            <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 uppercase tracking-wider">Total Documents</p>
                    <p class="text-3xl font-bold text-yellow-400 mt-1"><?= $total_documents_in_depts ?></p>
                </div>
                <i class="fa-solid fa-file-lines text-4xl text-yellow-700/50"></i>
            </div>
        </div>
        <!-- END NEW: Department Statistics Summary -->

        <!-- Main Content Grid: Department List (2/3) + Analytics/Detail (1/3) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column: Departments List (2/3 width) -->
            <div class="lg:col-span-2 space-y-4">
                <?php
                foreach($departments as $dept): 
                ?>
                <div class="bg-green-950/50 p-5 rounded-xl border-l-4 border-green-500 flex flex-col sm:flex-row justify-between items-start sm:items-center shadow-lg hover:bg-green-900/40 transition">
                    <div class="flex flex-col mb-2 sm:mb-0">
                        <span class="text-lg font-semibold text-green-200"><i class="fa-solid fa-building mr-2"></i> <?= $dept['name'] ?></span>
                        <div class="text-sm text-gray-500 mt-1 space-x-4">
                            <span title="Number of members in this department" class="inline-flex items-center gap-1">
                                <i class="fa-solid fa-users text-xs"></i> <?= $dept['members'] ?> Members
                            </span>
                            <span title="Total documents associated with this department" class="inline-flex items-center gap-1">
                                <i class="fa-solid fa-file-alt text-xs"></i> <?= $dept['documents'] ?> Docs
                            </span>
                            <span title="Pending reviews in this department" class="inline-flex items-center gap-1 <?= $dept['pending'] > 5 ? 'text-red-400 font-semibold' : 'text-yellow-400' ?>">
                                <i class="fa-solid fa-hourglass-half text-xs"></i> <?= $dept['pending'] ?> Pending
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button class="bg-green-700 hover:bg-green-600 px-3 py-1.5 rounded-lg text-sm font-medium transition">
                            View Members
                        </button>
                        <button class="text-green-400 hover:text-green-300 transition text-sm">
                            <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                        </button>
                        <button class="text-red-400 hover:text-red-300 transition text-sm">
                            <i class="fa-solid fa-trash-alt mr-1"></i> Delete
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- No Data Placeholder -->
                <?php if (empty($departments)): ?>
                <div class="p-8 text-center text-gray-500 bg-green-950/20 rounded-xl border border-green-800">
                    <i class="fa-solid fa-sitemap text-4xl mb-3 text-green-500"></i>
                    <p class="text-lg">No departments have been set up yet.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Departmental Metrics & Leaderboard (1/3 width) -->
            <div class="lg:col-span-1">
                <div class="bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-2xl h-full">
                    <h2 class="text-xl font-bold text-green-300 mb-4 flex items-center gap-2 border-b border-green-800/50 pb-2">
                        <i class="fa-solid fa-chart-pie text-lg text-green-500"></i> Performance Insights
                    </h2>
                    
                    <div class="space-y-4">
                        <!-- Metric 1: Highest Document Volume -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-400 uppercase mb-1">Highest Document Volume</h3>
                            <p class="text-lg font-bold text-green-200">Operations Dept. (98 Docs)</p>
                            <p class="text-xs text-gray-500">Highest output volume in the last quarter.</p>
                        </div>
                        
                        <!-- Metric 2: Fastest Review Time -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-400 uppercase mb-1 border-t border-green-900/50 pt-3">Fastest Average Review</h3>
                            <p class="text-lg font-bold text-blue-400">Legal Department</p>
                            <p class="text-xs text-gray-500">Average review time: 1.2 days.</p>
                        </div>
                        
                        <!-- Metric 3: Departmental Performance Chart Placeholder -->
                        <div class="border-t border-green-900/50 pt-3">
                            <h3 class="text-sm font-semibold text-gray-400 uppercase mb-2">Pending Review Distribution</h3>
                            <div class="h-32 flex items-center justify-center text-gray-500 border border-dashed border-green-800/50 rounded-lg">
                                Pie Chart Placeholder
                            </div>
                            <a href="<?=BASE_URL?>/org/reports/documents" class="mt-2 block text-xs text-green-400 hover:text-green-300">View document analytics &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>

        </div> <!-- End Main Content Grid -->

    </div>

</body>
</html>
