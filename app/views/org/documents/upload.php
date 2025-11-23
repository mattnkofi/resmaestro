<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Document - Maestro</title>
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
                    // Ensure Poppins is available as a custom utility class
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
                        // Overwriting 'sans' to ensure Poppins is the default
                        sans: ['Poppins', 'sans-serif'], 
                    }
                }
            }
        }
    </script>
    <style>
        /* Explicitly apply Poppins via standard CSS */
        body { font-family: 'Poppins', sans-serif; }
        
        .maestro-bg { background-color: #0b0f0c; } 
        .green-primary { color: #10b981; } 
        .green-secondary { color: #34d399; } 
        .dark-bg-subtle { background-color: #0d1310; } 
    </style>
</head>
<body class="bg-maestro-bg text-white font-poppins" x-data="{}">

    <?php 
    // MOCKING CURRENT URI FOR DEMONSTRATION: 
    // For "Upload Document" page:
    $current_uri = $_SERVER['REQUEST_URI'] ?? '/org/documents/upload'; 

    // PHP LOGIC TO DETERMINE IF A DROPDOWN SHOULD BE OPEN
    $is_documents_open = str_contains($current_uri, '/org/documents/');
    $is_review_open = str_contains($current_uri, '/org/review/');
    $is_organization_open = str_contains($current_uri, '/org/members/') || str_contains($current_uri, '/org/departments') || str_contains($current_uri, '/org/roles');
    $is_reports_open = str_contains($current_uri, '/org/reports/');

    if (function_exists('flash_alert')) flash_alert();

    $reviewers = $reviewers ?? [];
    $recent_uploads = $recent_uploads ?? [];
    $mock_user_name = $_SESSION['user_name'] ?? 'User Name'; 
    $mock_user_role = $_SESSION['user_role'] ?? 'Organization Admin';
    ?>


    <aside class="fixed top-0 left-0 h-full w-64 bg-[#0b0f0c] border-r border-green-900 text-white shadow-2xl flex flex-col transition-all duration-300 z-10">
        <div class="flex items-center justify-center py-6 border-b border-green-800">
            <img src="/public/maestrologo.png" alt="Logo" class="h-10 mr-8">
            <h1 class="text-2xl font-bold text-green-400 tracking-wider">MAESTRO</h1>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-3 space-y-4">

            <!<div>
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
                <h2 class="text-xs text-gray-500 uppercase mb-2 ml-2 tracking-wider font-semibold">System</h2>
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
        
        <h1 class="text-3xl font-bold text-green-400 mb-8">Upload New Document</h1>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <div>
                <form action="<?=BASE_URL?>/org/documents/store" method="POST" enctype="multipart/form-data" 
                    class="bg-green-950/50 p-8 rounded-xl space-y-6 max-w-2xl border border-green-800 shadow-2xl shadow-green-900/10">

                    <div>
                        <label for="title" class="block mb-2 text-sm font-medium text-gray-300">Document Title</label>
                        <input type="text" id="title" name="title" 
                               class="w-full bg-green-900 border border-green-800 p-3 rounded-lg focus:ring-green-500 focus:border-green-500 transition placeholder-gray-500 text-green-100" 
                               placeholder="e.g., Q4 Financial Report, Internal Policy Update v2.0" required>
                    </div>

                    <div>
                        <label for="type" class="block mb-2 text-sm font-medium text-gray-300">Document Type</label>
                        <select id="type" name="type" 
                                class="w-full bg-green-900 border border-green-800 p-3 rounded-lg focus:ring-green-500 focus:border-green-500 transition text-green-100">
                            <option value="Finance">Financial Report</option>
                            <option value="Budget">Budgetary Report</option>
                            <option value="Accomplishment">Accomplishment Report</option>
                            <option value="Proposal">Project Proposal</option>
                            <option value="Legal">Legal Document</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label for="file" class="block mb-2 text-sm font-medium text-gray-300">Select File (PDF, DOCX, XLSX, JPG)</label>
                        <input type="file" id="file" name="document_file" required
                               class="w-full text-sm text-green-100 file:mr-4 file:py-2.5 file:px-4 
                                      file:rounded-lg file:border-0 file:text-sm file:font-semibold
                                      file:bg-green-700 file:text-white hover:file:bg-green-600 transition duration-150">
                    </div>

                    <div>
                        <label for="tags" class="block mb-2 text-sm font-medium text-gray-300">Tags / Keywords (comma-separated)</label>
                        <input type="text" id="tags" name="tags" 
                               class="w-full bg-green-900 border border-green-800 p-3 rounded-lg focus:ring-green-500 focus:border-green-500 transition placeholder-gray-500 text-green-100" 
                               placeholder="e.g., Q4, Finance, 2024, Report">
                    </div>

                    <div>
                        <label for="reviewer" class="block mb-2 text-sm font-medium text-gray-300">Assign Reviewer (Optional)</label>
                        <select id="reviewer" name="reviewer" 
                                class="w-full bg-green-900 border border-green-800 p-3 rounded-lg focus:ring-green-500 focus:border-green-500 transition text-green-100">
                            <option value="">No Reviewer Assigned</option>
                            <?php 
                            // Loop over the $reviewers array passed from the controller
                            $reviewers = $reviewers ?? [];
                            if (!empty($reviewers)): 
                                foreach($reviewers as $reviewer): ?>
                                    <option value="<?= $reviewer['id'] ?>">
                                        <?= htmlspecialchars($reviewer['fname'] . ' ' . $reviewer['lname']) ?> (<?= htmlspecialchars($reviewer['email']) ?>)
                                    </option>
                                <?php endforeach; 
                            endif; ?>
                        </select>
                    </div>

                    <div>
                        <label for="description" class="block mb-2 text-sm font-medium text-gray-300">Description / Summary</label>
                        <textarea id="description" name="description" 
                                  class="w-full bg-green-900 border border-green-800 p-3 rounded-lg focus:ring-green-500 focus:border-green-500 transition placeholder-gray-500 text-green-100" 
                                  rows="4" placeholder="Provide a brief summary and context for this document, including any important notes for reviewers."></textarea>
                    </div>
                    
                    <div class="pt-2">
                        <button type="submit" class="w-full bg-green-700 px-6 py-3 rounded-xl hover:bg-green-600 font-bold text-lg transition shadow-lg shadow-green-900/40">
                            <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Upload Document
                        </button>
                    </div>
                </form>
            </div>

            <div class="space-y-8">
                <div class="bg-green-950/50 p-8 rounded-xl border border-green-800 shadow-2xl shadow-green-900/10">
                    <h2 class="text-xl font-bold text-green-300 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-green-500"></i> Upload Guidelines
                    </h2>
                    <ul class="list-disc list-inside space-y-2 text-gray-300">
                        <li>File size limit: 25MB per document.</li>
                        <li>Supported formats: PDF, DOCX, XLSX, JPG, PNG.</li>
                        <li>Ensure document titles are descriptive and unique.</li>
                        <li>Add relevant tags for easy search and categorization.</li>
                        <li>Assign a reviewer if the document requires immediate attention.</li>
                        <li>All uploads are subject to review by organization admins.</li>
                    </ul>
                    <p class="text-sm text-gray-500 mt-4">
                        Having trouble? Contact support for assistance.
                    </p>
                </div>

                <div class="bg-green-950/50 p-8 rounded-xl border border-green-800 shadow-2xl shadow-green-900/10">
                    <h2 class="text-xl font-bold text-green-300 mb-5 flex items-center gap-2"> 
                        <i class="fa-solid fa-history text-green-500"></i> Your Recent Uploads / Drafts
                    </h2>
                    <ul class="space-y-3 text-gray-300">
                        <?php 
                        $recent_uploads = $recent_uploads ?? []; 
                        if (empty($recent_uploads)): ?>
                            <div class="p-4 text-center border border-dashed border-green-700/50 rounded-lg text-gray-500">
                                <i class="fa-solid fa-file-circle-exclamation text-2xl mb-2"></i>
                                <p class="text-sm">No recent uploads or drafts found.</p>
                                <p class="text-xs mt-1">Start by uploading your first document above.</p>
                            </div>
                        <?php else: 
                            foreach ($recent_uploads as $upload):
                                $icon = match($upload['type'] ?? '') { 
                                    'report' => 'fa-file-excel text-green-400',
                                    'policy' => 'fa-file-word text-blue-400',
                                    'legal' => 'fa-file-gavel text-red-400',
                                    'proposal' => 'fa-file-alt text-yellow-400',
                                    default => 'fa-file text-gray-400',
                                };
                                $status_color = match($upload['status'] ?? '') {
                                    'Pending Review' => 'text-yellow-400',
                                    'Approved' => 'text-green-400',
                                    'Draft' => 'text-gray-400',
                                    default => 'text-red-400',
                                };
                            ?>
                            <li class="flex justify-between items-center bg-green-900/30 p-3 rounded-lg hover:bg-green-900/50 transition">
                                <span><i class="fa-solid <?= $icon ?> mr-2"></i> <?= htmlspecialchars($upload['title']) ?></span>
                                <span class="text-sm <?= $status_color ?>"><?= htmlspecialchars($upload['status']) ?></span>
                            </li>
                            <?php endforeach; 
                        endif; ?>
                    </ul>
                    <a href="<?=BASE_URL?>/org/documents/all" class="mt-4 inline-block text-green-400 hover:text-green-300 text-sm">View all my documents <i class="fa-solid fa-arrow-right ml-1"></i></a>
                </div>
            </div>

        </div> </div> </body>
</html>