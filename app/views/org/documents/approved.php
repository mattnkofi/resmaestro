<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Documents - Maestro</title>
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
        /* This style block ensures the Poppins font is applied globally */
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-maestro-bg text-white font-poppins" x-data="{}">

    <?php 
    $current_uri = $_SERVER['REQUEST_URI'] ?? '/org/documents/approved'; 

    // PHP LOGIC TO DETERMINE IF A DROPDOWN SHOULD BE OPEN
    $is_documents_open = str_contains($current_uri, '/org/documents/');
    $is_review_open = str_contains($current_uri, '/org/review/');
    $is_organization_open = str_contains($current_uri, '/org/members/') || str_contains($current_uri, '/org/departments') || str_contains($current_uri, '/org/roles');
    $is_reports_open = str_contains($current_uri, '/org/reports/');

    // FIX: Initialize filter variables (passed from OrgController)
    $q = $q ?? '';
    $type = $type ?? '';

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
                    <a href="<?=BASE_URL?>/org/documents/approved" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/approved') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Approved</a>
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
                    <a href="<?=BASE_URL?>/org/events" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/events') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Events</a>
                    <a href="<?=BASE_URL?>/org/members/list" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/members/list') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Members</a>
                    <a href="<?=BASE_URL?>/org/members/add" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/members/add') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Add Member</a>
                    <a href="<?=BASE_URL?>/org/departments" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/departments') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Departments</a>
                </div>
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
            Approved Documents
        </h1>

        <?php if (function_exists('flash_alert')) flash_alert(); // ADDED: Display Toast/flash messages ?>

        <form method="GET" action="<?= BASE_URL ?>/org/documents/approved">
            <div class="flex flex-col md:flex-row gap-4 mb-8">
                <input type="text" name="q" placeholder="Search by title or approver..." 
                       value="<?= html_escape($q) ?>"
                       class="w-full md:w-1/3 bg-green-900 border border-green-800 p-3 rounded-xl focus:ring-green-500 focus:border-green-500 transition placeholder-gray-500 text-green-100">
                
                <select name="type" class="w-full md:w-1/6 bg-green-900 border border-green-800 p-3 rounded-xl text-green-100">
                    <option value="">Filter by Type</option>
                    <?php 
                    $doc_types = ['Report', 'Policy', 'Legal', 'Project Proposal', 'HR Document', 'Marketing'];
                    foreach ($doc_types as $doc_type): ?>
                        <option value="<?= html_escape(strtolower($doc_type)) ?>" 
                            <?= (strtolower($doc_type) === strtolower($type)) ? 'selected' : '' ?>>
                            <?= $doc_type ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="bg-green-700 hover:bg-green-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-green-900/40">
                    <i class="fa-solid fa-filter mr-2"></i> Apply Filters
                </button>
                
                <?php if (!empty($q) || !empty($type)): ?>
                    <a href="<?= BASE_URL ?>/org/documents/approved" class="bg-gray-700 hover:bg-gray-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-gray-900/40">
                        <i class="fa-solid fa-xmark mr-2"></i> Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
        <div class="space-y-4">
    <?php
    // Use the real data passed from the controller
    $docs = $approved_docs ?? [];
    
    foreach($docs as $doc): 
        $approved_date = date('M d, Y', strtotime($doc['created_at']));
    ?>
   <div class="bg-green-950/50 p-5 rounded-xl border-l-4 border-green-500 flex flex-col md:flex-row justify-between items-start md:items-center shadow-lg hover:bg-green-900/40 transition">
        <div class="flex flex-col mb-2 md:mb-0">
            <span class="text-lg font-semibold text-green-200"><?= html_escape($doc['title']) ?></span>
            <span class="text-sm text-gray-400">Type: <?= html_escape(ucwords($doc['type'])) ?></span>
        </div>
        <div class="flex items-center space-x-6">
            <div class="text-right hidden sm:block">
                <span class="block text-sm text-green-400 font-medium">Approval Date: <?= $approved_date ?></span>
            </div>
            <a href="<?=BASE_URL?>/public/uploads/documents/<?= urlencode($doc['file_name']) ?>" 
               target="_blank" 
               class="bg-green-700 hover:bg-green-600 px-4 py-2 rounded-lg text-sm transition">
                <i class="fa-solid fa-download mr-1"></i> Download
            </a>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($docs)): ?>
    <div class="p-8 text-center text-gray-500 bg-green-950/50 rounded-xl border border-green-800">
        <i class="fa-solid fa-check-circle text-4xl mb-3 text-green-500"></i>
        <p class="text-lg">No documents match the current filters or have been approved yet.</p>
    </div>
    <?php endif; ?>
</div>

    </div>

</body>
</html>