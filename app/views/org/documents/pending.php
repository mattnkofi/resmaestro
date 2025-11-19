<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Documents - Maestro UI</title>
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

        /* MODAL FIX: Defines the grid layout for the internal review panel */
        .review-content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr; /* 2/3 viewer, 1/3 sidebar */
            height: calc(100% - 4rem); /* Deduct header/footer space if needed, setting to 100% for inner div is generally safer with flex/grid */
            gap: 1rem;
            padding: 1rem; /* Added padding to the internal grid container */
        }
    </style>
</head>
<body class="bg-maestro-bg text-white font-poppins" 
    x-data="{ 
        modalOpen: false, 
        approveModalOpen: false, 
        rejectModalOpen: false,
        currentDoc: { id: 0, title: '', file_name: '', status: '', submitter: '', type: '' },
        setDoc(doc) { 
            this.currentDoc = doc; 
            this.modalOpen = true; 
        }
    }" 
    @keydown.escape="modalOpen = false">

    <?php 
    // MOCKING CURRENT URI FOR DEMONSTRATION: 
    // For "Pending Documents" page:
    $current_uri = $_SERVER['REQUEST_URI'] ?? '/org/documents/pending'; 

    // PHP LOGIC TO DETERMINE IF A DROPDOWN SHOULD BE OPEN
    $is_documents_open = str_contains($current_uri, '/org/documents/');
    $is_review_open = str_contains($current_uri, '/org/review/');
    $is_organization_open = str_contains($current_uri, '/org/members/') || str_contains($current_uri, '/org/departments') || str_contains($current_uri, '/org/roles');
    $is_reports_open = str_contains($current_uri, '/org/reports/');
    
    // --- START OF CLEANUP ---
    $q = $q ?? '';
    $type = $type ?? '';

    // FIX: Ensure $docs is initialized from controller
    $docs = $docs ?? []; 

    // Mock BASE_URL and html_escape if not provided
    if (!defined('BASE_URL')) define('BASE_URL', '/maestro');
    if (!function_exists('html_escape')) {
        function html_escape($str) {
            return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
        }
    }
    // --- END OF CLEANUP ---
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
        
        <h1 class="text-3xl font-bold text-yellow-400 mb-6 tracking-wide">
            Pending Documents
        </h1>

        <?php if (function_exists('flash_alert')) flash_alert(); // ADDED: Display Toast/flash messages ?>

        <form method="GET" action="<?= BASE_URL ?>/org/documents/pending">
            <div class="flex flex-col md:flex-row gap-4 mb-6">
                
                <input type="text" name="q" placeholder="Search by title, author, or date..." 
                        value="<?= html_escape($q) ?>"
                        class="w-full md:w-1/3 bg-green-900 border border-green-800 p-3 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 transition placeholder-gray-500 text-green-100">
                
                <select name="type" class="w-full md:w-1/6 bg-green-900 border border-green-800 p-3 rounded-xl text-green-100">
                    <option value="">Filter by Type</option>
                    <?php 
                    $doc_types = ['Finance', 'Budget', 'Accomplishment', 'Proposal', 'Legal', 'Other'];
                    foreach ($doc_types as $doc_type): ?>
                        <option value="<?= html_escape(strtolower($doc_type)) ?>" 
                            <?= (strtolower($doc_type) === strtolower($type)) ? 'selected' : '' ?>>
                            <?= $doc_type ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="bg-yellow-700 hover:bg-yellow-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-yellow-900/40">
                    <i class="fa-solid fa-filter mr-2"></i> Apply Filters
                </button>
                
                <?php if (!empty($q) || !empty($type)): ?>
                    <a href="<?= BASE_URL ?>/org/documents/pending" class="bg-gray-700 hover:bg-gray-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-gray-900/40">
                        <i class="fa-solid fa-xmark mr-2"></i> Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>


        <div class="overflow-x-auto rounded-xl border border-green-800 shadow-2xl shadow-green-900/10">
            <table class="w-full text-left">
                
                <thead class="bg-yellow-900/40 text-gray-200 uppercase text-sm tracking-wider">
                    <tr>
                        <th class="p-4 border-b border-green-800">Title</th>
                        <th class="p-4 border-b border-green-800">Submitted By</th>
                        <th class="p-4 border-b border-green-800">Submission Date</th>
                        <th class="p-4 border-b border-green-800">Days Pending</th>
                        <th class="p-4 border-b border-green-800 text-center">Actions</th>
                    </tr>
                </thead>
                
                <tbody class="bg-[#0f1511] text-gray-300">
    
    <?php 
    $docs = $docs ?? [];
    foreach($docs as $doc): 
        // CRITICAL FIX 1: Safely extract ALL data fields regardless of object or array return type
        $doc_id = $doc->id ?? $doc['id'] ?? 0;
        $doc_title = $doc->title ?? $doc['title'] ?? 'Document';
        $doc_file_name = $doc->file_name ?? $doc['file_name'] ?? '';
        $doc_status = $doc->status ?? $doc['status'] ?? 'Pending Review';
        $doc_fname = $doc->fname ?? $doc['fname'] ?? '';
        $doc_lname = $doc->lname ?? $doc['lname'] ?? '';
        $doc_type = $doc->type ?? $doc['type'] ?? '';
        $doc_created_at = $doc->created_at ?? $doc['created_at'] ?? 'now';
        
        $submit_time = strtotime($doc_created_at);
        $days_pending = round((time() - $submit_time) / (60 * 60 * 24));
        
        $row_class = $days_pending > 7 ? 'bg-red-900/10 hover:bg-red-900/20' : 'hover:bg-green-700/10';
        $days_class = $days_pending > 7 ? 'text-red-400 font-bold' : 'text-yellow-400';
        $display_date = date('M d, Y', $submit_time);
    ?>
    
    <tr class="border-b border-green-800 transition <?= $row_class ?>">
        <td class="p-4 font-medium text-green-200"><?= html_escape($doc_title) ?></td>
        <td class="p-4"><?= html_escape($doc_fname . ' ' . $doc_lname) ?></td>
        <td class="p-4 text-sm text-gray-400"><?= $display_date ?></td>
        <td class="p-4 <?= $days_class ?>"><?= $days_pending ?></td>
        <td class="p-4 text-center">
            <button @click="setDoc({ 
                id: <?= $doc_id ?>, 
                title: '<?= addslashes(html_escape($doc_title)) ?>', 
                file_name: '<?= addslashes(html_escape($doc_file_name)) ?>', 
                status: '<?= addslashes(html_escape($doc_status)) ?>', 
                submitter: '<?= addslashes(html_escape($doc_fname . ' ' . $doc_lname)) ?>',
                type: '<?= addslashes(html_escape($doc_type)) ?>'
            })" 
                class="text-yellow-400 hover:text-yellow-200 hover:underline transition font-medium mr-4">
                <i class="fa-solid fa-pen-to-square mr-1"></i> Review
            </button>
            <button class="text-gray-500 hover:text-gray-300 transition text-sm">
                <i class="fa-solid fa-clock mr-1"></i> Remind
            </button>
        </td>
    </tr>
    <?php endforeach; ?>

    <?php if (empty($docs)): ?>
    <tr>
        <td colspan="5" class="p-8 text-center text-gray-500">
            <i class="fa-solid fa-check-circle text-4xl mb-3 text-green-500"></i>
            <p class="text-lg">No documents are currently pending review!</p>
        </td>
    </tr>
    <?php endif; ?>
    
</tbody>

            </table>
        </div>

    </div>
    
    <div x-show="modalOpen" 
        x-transition:enter="ease-out duration-300"
        x-transition:leave="ease-in duration-200"
        class="fixed inset-0 z-50 overflow-y-auto bg-maestro-bg bg-opacity-95 flex items-center justify-center" 
        style="display: none;">

        <div @click.outside="modalOpen = false" class="w-full max-w-7xl mx-auto bg-[#0f1511] rounded-xl shadow-2xl border border-green-800 h-[90vh] flex flex-col">
            
            <header class="p-4 border-b border-green-800 flex justify-between items-center bg-sidebar-dark" :class="currentDoc.status === 'Approved' ? 'border-green-500' : 'border-yellow-500'">
                <h3 class="text-xl font-bold" x-text="'Review: ' + currentDoc.title">Review: Document Title</h3>
                <button @click="modalOpen = false" class="text-gray-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </header>

            <div class="review-content-grid flex-1 overflow-y-auto"> 
                
                <div class="pr-4 border-r border-green-800 flex flex-col">
                    <h4 class="text-lg font-semibold text-gray-400 mb-3">Document Content (PDF Viewer)</h4>
                    <iframe 
                        :src="'<?= BASE_URL ?>/public/uploads/documents/' + currentDoc.file_name" 
                        class="w-full flex-1 border border-gray-700 rounded-lg bg-gray-900" 
                        frameborder="0">
                    </iframe>
                </div>

                <div class="pl-4 space-y-6 flex flex-col">
                    <h4 class="text-lg font-semibold text-gray-400">Review Details & Status</h4>

                    <div class="bg-green-950/50 p-4 rounded-lg border border-green-800">
                        <p class="text-sm text-gray-400">Status: <span :class="currentDoc.status === 'Approved' ? 'text-green-300' : 'text-yellow-300'" x-text="currentDoc.status"></span></p>
                        <p class="text-sm text-gray-400">Type: <span x-text="currentDoc.type"></span></p>
                        <p class="text-sm text-gray-400">Submitted By: <span x-text="currentDoc.submitter"></span></p>
                    </div>

                    <h5 class="text-md font-bold text-green-300">Update Status:</h5>

                    <div class="space-y-4">
                        
                        <form method="POST" :action="'<?= BASE_URL ?>/org/documents/update_status'">
                            <?php csrf_field(); // <--- FIX: Added CSRF field ?>
                            <input type="hidden" name="document_id" :value="currentDoc.id">
                            <input type="hidden" name="new_status" value="Approved">
                            <input type="hidden" name="document_title" :value="currentDoc.title">

                            <button type="submit" class="w-full bg-green-700 hover:bg-green-600 px-5 py-2 rounded-lg font-medium transition"
                                :disabled="currentDoc.status === 'Approved'"
                                :class="currentDoc.status === 'Approved' ? 'opacity-50 cursor-not-allowed' : ''">
                                <i class="fa-solid fa-thumbs-up mr-2"></i> Approve
                            </button>
                        </form>

                        <form method="POST" :action="'<?= BASE_URL ?>/org/documents/update_status'">
                            <?php csrf_field(); // <--- FIX: Added CSRF field ?>
                            <input type="hidden" name="document_id" :value="currentDoc.id">
                            <input type="hidden" name="new_status" value="Rejected">
                            <input type="hidden" name="document_title" :value="currentDoc.title">
                            
                            <button type="submit" class="w-full bg-red-700 hover:bg-red-600 px-5 py-2 rounded-lg font-medium transition"
                                :disabled="currentDoc.status === 'Rejected'"
                                :class="currentDoc.status === 'Rejected' ? 'opacity-50 cursor-not-allowed' : ''">
                                <i class="fa-solid fa-thumbs-down mr-2"></i> Reject
                            </button>
                        </form>

                        <form method="POST" :action="'<?= BASE_URL ?>/org/documents/update_status'">
                            <?php csrf_field(); // <--- FIX: Added CSRF field ?>
                            <input type="hidden" name="document_id" :value="currentDoc.id">
                            <input type="hidden" name="new_status" value="Archived">
                            <input type="hidden" name="document_title" :value="currentDoc.title">

                            <button type="submit" onclick="return confirm('Confirm archive?')"
                                class="w-full bg-gray-700 hover:bg-gray-600 px-5 py-2 rounded-lg font-medium transition">
                                <i class="fa-solid fa-box-archive mr-2"></i> Archive
                            </button>
                        </form>
                    </div>
                    
                    <button @click="modalOpen = false" class="w-full text-gray-500 hover:text-gray-300 transition mt-4">Close Review</button>

                </div>
            </div>

        </div>
    </div>
</body>
</html>