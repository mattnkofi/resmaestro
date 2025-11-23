<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviewer Reports - Maestro UI</title>
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
    // For "Reviewer Reports" page:
    $current_uri = $_SERVER['REQUEST_URI'] ?? '/org/reports/reviewers'; 

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
        
        <h1 class="text-3xl font-bold text-yellow-400 mb-6 tracking-wide">
            Reviewer Reports
        </h1>

        <?php
        // Mock data for the reviewer report table
        $reviewer_data = [
            ['name' => 'Kimberly Nicole De Leon', 'reviewed' => 58, 'approved' => 50, 'rejected' => 8, 'avg_time' => '1.2 days'],
            ['name' => 'Matt Justine Martin', 'reviewed' => 45, 'approved' => 30, 'rejected' => 15, 'avg_time' => '1.8 days'],
            ['name' => 'Aron Luigee Jordan', 'reviewed' => 70, 'approved' => 65, 'rejected' => 5, 'avg_time' => '0.9 days'],
            ['name' => 'Shirin Chisty Deliverio', 'reviewed' => 32, 'approved' => 28, 'rejected' => 4, 'avg_time' => '1.5 days'],
        ];

        // Stats Calculation
        $total_reviews = array_sum(array_column($reviewer_data, 'reviewed'));
        $total_approved = array_sum(array_column($reviewer_data, 'approved'));
        $approval_rate = $total_reviews > 0 ? round(($total_approved / $total_reviews) * 100) : 0;
        ?>

        <!-- NEW: Performance Summary Bar -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Card 1: Total Reviews -->
            <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 uppercase tracking-wider">Total Reviews Done</p>
                    <p class="text-3xl font-bold text-green-400 mt-1"><?= $total_reviews ?></p>
                </div>
                <i class="fa-solid fa-clipboard-check text-4xl text-green-700/50"></i>
            </div>

            <!-- Card 2: Average Approval Rate -->
            <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 uppercase tracking-wider">Avg. Approval Rate</p>
                    <p class="text-3xl font-bold text-blue-400 mt-1"><?= $approval_rate ?>%</p>
                </div>
                <i class="fa-solid fa-thumbs-up text-4xl text-blue-700/50"></i>
            </div>

            <!-- Card 3: Fastest Reviewer -->
            <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 uppercase tracking-wider">Fastest Reviewer (Avg)</p>
                    <p class="text-lg font-bold text-yellow-400 mt-1">John Smith</p>
                    <p class="text-xs text-gray-500">~0.9 Days</p>
                </div>
                <i class="fa-solid fa-gauge-high text-4xl text-yellow-700/50"></i>
            </div>
        </div>
        <!-- END NEW: Performance Summary Bar -->

        <!-- Main Content Grid: Table (2/3) + Chart/Insights (1/3) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column: Reviewer Performance Table (2/3 width) -->
            <div class="lg:col-span-2 space-y-4">
                <h2 class="text-xl font-semibold text-yellow-300 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-table text-lg"></i> Detailed Reviewer Performance
                </h2>
                
                <div class="overflow-x-auto rounded-xl border border-green-800 shadow-2xl shadow-green-900/10">
                    <table class="w-full text-left">
                        <thead class="bg-yellow-900/40 text-gray-200 uppercase text-sm tracking-wider">
                            <tr>
                                <th class="p-4 border-b border-green-800">Reviewer</th>
                                <th class="p-4 border-b border-green-800 text-center">Total Reviewed</th>
                                <th class="p-4 border-b border-green-800 text-center">Approved (%)</th>
                                <th class="p-4 border-b border-green-800 text-center">Rejected (%)</th>
                                <th class="p-4 border-b border-green-800 text-center">Avg. Review Time</th>
                                <th class="p-4 border-b border-green-800 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[#0f1511] text-gray-300">
                            <?php foreach($reviewer_data as $reviewer): 
                                $total = $reviewer['reviewed'];
                                $approved_pct = $total > 0 ? round(($reviewer['approved'] / $total) * 100) : 0;
                                $rejected_pct = 100 - $approved_pct;
                            ?>
                            <tr class="border-b border-green-800 hover:bg-green-700/10 transition">
                                <td class="p-4 font-medium text-green-200 flex items-center gap-2">
                                    <i class="fa-solid fa-user-circle text-lg text-blue-400"></i>
                                    <?= $reviewer['name'] ?>
                                </td>
                                <td class="p-4 text-center font-bold text-yellow-400"><?= $total ?></td>
                                <td class="p-4 text-center">
                                    <span class="font-medium text-green-400"><?= $approved_pct ?>%</span> (<?= $reviewer['approved'] ?>)
                                </td>
                                <td class="p-4 text-center">
                                    <span class="font-medium text-red-400"><?= $rejected_pct ?>%</span> (<?= $reviewer['rejected'] ?>)
                                </td>
                                <td class="p-4 text-center text-gray-300"><?= $reviewer['avg_time'] ?></td>
                                <td class="p-4 text-center">
                                    <button class="text-yellow-400 hover:text-yellow-200 hover:underline transition text-sm">
                                        View Detail
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Column: Chart and Insights (1/3 width) -->
            <div class="lg:col-span-1">
                <div class="bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-2xl h-full space-y-6">
                    
                    <h2 class="text-xl font-bold text-yellow-300 flex items-center gap-2 border-b border-green-800/50 pb-2">
                        <i class="fa-solid fa-chart-area text-lg"></i> Review Volume Trend
                    </h2>
                    
                    <!-- Chart Placeholder -->
                    <div class="h-40 flex items-center justify-center text-gray-500 border border-dashed border-green-800/50 rounded-lg">
                        Line Chart Placeholder (Reviews per Month)
                    </div>
                    
                    <h3 class="text-md font-semibold text-gray-400 uppercase border-t border-green-800/50 pt-4">Key Insights</h3>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-bell text-red-400 mt-1"></i>
                            <span>Rejection Rate High: **Justine Martin** has the highest rejection percentage (33%).</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-tachometer-alt text-green-400 mt-1"></i>
                            <span>Efficiency Leader: **John Smith** maintains the fastest average turnaround time.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-chart-simple text-blue-400 mt-1"></i>
                            <span>Volume Leader: **John Smith** has reviewed the most documents (70).</span>
                        </li>
                    </ul>
                    
                </div>
            </div>
        </div> <!-- End Main Content Grid -->
    </div>
</body>
</html>