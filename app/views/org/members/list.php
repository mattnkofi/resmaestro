<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members List - Maestro UI</title>
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

    <?php 
    // MOCKING CURRENT URI FOR DEMONSTRATION: 
    $BASE_URL = BASE_URL ?? '';
    $current_uri = $_SERVER['REQUEST_URI'] ?? '/org/members/list'; 

    // PHP LOGIC TO DETERMINE IF A DROPDOWN SHOULD BE OPEN
    $is_documents_open = str_contains($current_uri, '/org/documents/');
    $is_review_open = str_contains($current_uri, '/org/review/');
    $is_organization_open = str_contains($current_uri, '/org/members/') || str_contains($current_uri, '/org/departments') || str_contains($current_uri, '/org/roles');
    $is_reports_open = str_contains($current_uri, '/org/reports/');

    // Dynamic data variables passed from controller
    $members_data = $members ?? [];
    $q = $q ?? '';

    // Stats Calculation
    $total_members = count($members_data);
    $active_reviewers = 0;
    $departments_seen = [];
    $reviewer_roles = ['Administrator', 'Reviewer']; 
    
    foreach ($members_data as $member) {
        $role_name = $member['role_name'] ?? '';
        $dept_name = $member['dept_name'] ?? 'N/A';
        
        if (in_array($role_name, $reviewer_roles)) {
            $active_reviewers++;
        }
        if ($dept_name !== 'N/A' && !in_array($dept_name, $departments_seen)) {
            $departments_seen[] = $dept_name;
        }
    }
    $total_departments = count($departments_seen);

    // Mock Activity Data (Kept minimal as placeholder)
    $member_activity = [
        ['name' => 'Aron Luigee Jordan', 'action' => 'updated permissions', 'time' => '10 min ago'],
        ['name' => 'Kimberly Nicole De Leon', 'action' => 'uploaded Marketing Plan', 'time' => '1 hour ago'],
    ];
    ?>

    <aside class="fixed top-0 left-0 h-full w-64 bg-[#0b0f0c] border-r border-green-900 text-white shadow-2xl flex flex-col transition-all duration-300 z-10">
        <div class="flex items-center justify-center py-6 border-b border-green-800">
            <img src="/public/maestrologo.png" alt="Logo" class="h-10 mr-8">
            <h1 class="text-2xl font-bold text-green-400 tracking-wider">MAESTRO</h1>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-3 space-y-4">

            <div>
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2 ml-2 tracking-wider">Main</h2>
                <a href="<?=$BASE_URL?>/org/dashboard" class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-700/50 transition
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
                    <a href="<?=$BASE_URL?>/org/documents/all" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/all') ? 'text-green-400 font-semibold' : '' ?>">All Documents</a>
                    <a href="<?=$BASE_URL?>/org/documents/upload" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/upload') ? 'text-green-400 font-semibold' : '' ?>">Upload New</a>
                    <a href="<?=$BASE_URL?>/org/documents/pending" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/pending') ? 'text-green-400 font-semibold' : '' ?>">Pending Review</a>
                    <a href="<?=$BASE_URL?>/org/documents/approved" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/approved') ? 'text-green-400 font-semibold' : '' ?>">Approved / Noted</a>
                    <a href="<?=$BASE_URL?>/org/documents/rejected" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/rejected') ? 'text-green-400 font-semibold' : '' ?>">Rejected</a>
                    <a href="<?=$BASE_URL?>/org/documents/archived" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/archived') ? 'text-green-400 font-semibold' : '' ?>">Archived</a>
                </div>
            </div>

            <div x-data='{ open: <?= $is_review_open ? 'true' : 'false' ?> }' class="space-y-1">
                <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-clipboard-check w-5 text-center"></i>
                        <span>Review & Workflow</span>
                    </span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                </button>
                <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?=$BASE_URL?>/org/review/queue" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/review/queue') ? 'text-green-400 font-semibold' : '' ?>">Pending Reviews</a>
                    <a href="<?=$BASE_URL?>/org/review/history" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/review/history') ? 'text-green-400 font-semibold' : '' ?>">Review History</a>
                    <a href="<?=$BASE_URL?>/org/review/comments" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/review/comments') ? 'text-green-400 font-semibold' : '' ?>">Comment Threads</a>
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
                    <a href="<?=$BASE_URL?>/org/members/list" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/members/list') ? 'text-green-400 font-semibold' : '' ?>">Members</a>
                    <a href="<?=$BASE_URL?>/org/members/add" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/members/add') ? 'text-green-400 font-semibold' : '' ?>">Add Member</a>
                    <a href="<?=$BASE_URL?>/org/departments" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/departments') ? 'text-green-400 font-semibold' : '' ?>">Departments</a>
                    <a href="<?=$BASE_URL?>/org/roles" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/roles') ? 'text-green-400 font-semibold' : '' ?>">Roles & Permissions</a>
                </div>
            </div>

            <div x-data='{ open: <?= $is_reports_open ? 'true' : 'false' ?> }' class="space-y-1">
                <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-chart-line w-5 text-center"></i>
                        <span>Reports & Analytics</span>
                    </span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                </button>
                <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?=$BASE_URL?>/org/reports/overview" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/reports/overview') ? 'text-green-400 font-semibold' : '' ?>">Overview</a>
                    <a href="<?=$BASE_URL?>/org/reports/documents" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/reports/documents') ? 'text-green-400 font-semibold' : '' ?>">Document Analytics</a>
                    <a href="<?=$BASE_URL?>/org/reports/reviewers" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/reports/reviewers') ? 'text-green-400 font-semibold' : '' ?>">Reviewer Activity</a>
                    <a href="<?=$BASE_URL?>/org/reports/storage" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/reports/storage') ? 'text-green-400 font-semibold' : '' ?>">Storage Usage</a>
                </div>
            </div>

            <div class="pt-4">
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2 ml-2 tracking-wider">System</h2>
            </div>
            
            <div>
                <a href="<?=$BASE_URL?>/org/settings" class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-700/30 transition <?= str_contains($current_uri, '/org/settings') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">
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
                    <a href="<?=$BASE_URL?>/org/profile" class="block px-4 py-2 hover:bg-green-700/30 rounded-t-lg transition">View Profile</a>
                    <a href="<?=$BASE_URL?>/org/settings" class="block px-4 py-2 hover:bg-green-700/30 transition">Settings</a>
                    <a href="<?=$BASE_URL?>/logout" class="block px-4 py-2 text-red-400 hover:bg-red-700/30 rounded-b-lg transition">Logout</a>
                </div>
            </div>
        </div>

        <div class="border-t border-green-800 p-3 text-xs text-gray-500 text-center">
            Maestro Organization © <?=date('Y')?>
        </div>
    </aside>
    <div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <h1 class="text-3xl font-bold text-green-400 mb-4 sm:mb-0">Organization Members</h1>
            <a href="<?=$BASE_URL?>/org/members/add" class="bg-green-700 hover:bg-green-600 px-5 py-2.5 rounded-xl text-lg font-medium transition shadow-lg shadow-green-900/40">
                <i class="fa-solid fa-user-plus mr-2"></i> Add New Member
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 uppercase tracking-wider">Total Members</p>
                    <p class="text-3xl font-bold text-green-400 mt-1"><?= $total_members ?></p>
                </div>
                <i class="fa-solid fa-users text-4xl text-green-700/50"></i>
            </div>

            <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 uppercase tracking-wider">Active Reviewers</p>
                    <p class="text-3xl font-bold text-yellow-400 mt-1"><?= $active_reviewers ?></p>
                </div>
                <i class="fa-solid fa-user-check text-4xl text-yellow-700/50"></i>
            </div>

            <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 uppercase tracking-wider">Departments</p>
                    <p class="text-3xl font-bold text-blue-400 mt-1"><?= $total_departments ?></p>
                </div>
                <i class="fa-solid fa-building text-4xl text-blue-700/50"></i>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 space-y-4">
                
                <form method="GET" action="<?=$BASE_URL?>/org/members/list" class="flex flex-col md:flex-row gap-4">
                    <input type="text" name="q" placeholder="Search by name or email..." 
                           value="<?= htmlspecialchars($q) ?>"
                           class="w-full md:w-1/2 bg-green-900/50 border border-green-800 p-3 rounded-xl focus:ring-green-500 focus:border-green-500 transition placeholder-gray-500 text-white">
                    <select name="role" class="w-full md:w-1/4 bg-green-900/50 border border-green-800 p-3 rounded-xl text-white">
                        <option value="">Filter by Role</option>
                        </select>
                    <button type="submit" class="bg-green-700 hover:bg-green-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-green-900/40">
                        <i class="fa-solid fa-filter mr-2"></i> Apply Filters
                    </button>
                </form>

                <div class="space-y-4">
                    <?php if (!empty($members_data)): ?>
                    <?php foreach($members_data as $member): 
                        $full_name = htmlspecialchars(trim($member['fname'] . ' ' . $member['lname']));
                        // Use N/A if department or role tables are missing in the DB
                        $role_name = htmlspecialchars($member['role_name'] ?? 'No Role');
                        $dept_name = htmlspecialchars($member['dept_name'] ?? 'No Department');
                        $email = htmlspecialchars($member['email']);
                        
                        // Set role color based on role name
                        $role_color = match ($role_name) {
                            'Administrator' => 'text-red-400',
                            'Reviewer' => 'text-yellow-400',
                            default => 'text-gray-400',
                        };
                    ?>
                    <div class="bg-green-950/50 p-5 rounded-xl border-l-4 border-green-500 flex flex-col sm:flex-row justify-between items-start sm:items-center shadow-lg hover:bg-green-900/40 transition">
                        <div class="flex flex-col mb-2 sm:mb-0">
                            <span class="text-lg font-semibold text-green-200"><?= $full_name ?></span>
                            <span class="text-sm text-gray-500"><?= $dept_name ?> | <?= $email ?></span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="text-sm font-medium <?= $role_color ?>"><?= $role_name ?></span>
                            <button class="text-green-400 hover:text-green-300 transition text-sm">
                                <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php else: ?>
                    <div class="p-8 text-center text-gray-500 bg-green-950/20 rounded-xl border border-green-800">
                        <i class="fa-solid fa-user-group text-4xl mb-3 text-green-500"></i>
                        <p class="text-lg">No members found in the organization.</p>
                        <a href="<?=$BASE_URL?>/org/members/add" class="mt-2 text-green-400 hover:text-green-300 inline-block">Click here to add a new member.</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-2xl h-full">
                    <h2 class="text-xl font-semibold text-green-300 mb-4 flex items-center justify-between border-b border-green-800/50 pb-2">
                        <i class="fa-solid fa-bolt text-lg"></i> Recent Member Activity
                    </h2>
                    
                    <ul class="space-y-4 max-h-[700px] overflow-y-auto pr-2">
                        <?php if (!empty($member_activity)): ?>
                        <?php foreach ($member_activity as $activity): ?>
                        <li class="border-l-4 border-blue-500 pl-3">
                            <p class="text-sm font-medium text-blue-400"><?= $activity['name'] ?> <span class="text-gray-300"><?= $activity['action'] ?></span></p>
                            <p class="text-xs text-gray-500 mt-0.5"><?= $activity['time'] ?></p>
                        </li>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <div class="text-center text-gray-500 py-6">
                            <i class="fa-solid fa-clock-rotate-left text-3xl mb-2"></i>
                            <p class="text-sm">No recent activity recorded.</p>
                        </div>
                        <?php endif; ?>
                    </ul>
                    
                    <a href="<?=$BASE_URL?>/org/reports/reviewers" class="mt-4 block text-sm text-green-400 hover:text-green-300">View detailed activity reports &rarr;</a>
                </div>
            </div>

        </div> </div>

</body>
</html>