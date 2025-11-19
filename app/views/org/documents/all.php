<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Documents - Maestro UI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Poppins Font Import -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        /* Custom scrollbar styling for a cleaner dark theme look */
        .document-table-container::-webkit-scrollbar {
            width: 8px;
        }
        .document-table-container::-webkit-scrollbar-thumb {
            background-color: #047857; /* Emerald-600 for the thumb */
            border-radius: 4px;
        }
        .document-table-container::-webkit-scrollbar-track {
            background-color: #0f1511; /* Darker track */
    }
        /* Explicitly apply Poppins via standard CSS */
        body { font-family: 'Poppins', sans-serif; }

        /* Sidebar Custom Styles */
        .maestro-bg { background-color: #0b0f0c; } 
        .green-primary { color: #10b981; } 
        .green-secondary { color: #34d399; } 
        .dark-bg-subtle { background-color: #0d1310; } 
        .dashboard-card {
            background-color: rgba(16, 185, 129, 0.05);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.1);
        }
    </style>
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
</head>

<body class="bg-maestro-bg text-white font-poppins" 
    x-data="{ 
        modalOpen: false, 
        currentDoc: { id: 0, title: '', file_name: '', status: '', submitter: '', type: '', created_at: '' },
        setDoc(doc) { 
            this.currentDoc = doc; 
            this.modalOpen = true; 
        }
    }" 
    @keydown.escape="modalOpen = false">

    <?php 
    // MOCKING CURRENT URI FOR DEMONSTRATION: 
    // For "All Documents" page:
    $current_uri = $_SERVER['REQUEST_URI'] ?? '/org/documents/all'; 

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
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <h1 class="text-3xl font-bold text-green-400 mb-4 sm:mb-0">All Documents</h1>
            <a href="<?=BASE_URL?>/org/documents/upload" class="bg-green-700 hover:bg-green-600 px-5 py-2.5 rounded-xl text-lg font-medium transition shadow-lg shadow-green-900/40">
                <i class="fa-solid fa-plus mr-2"></i> Upload Document
            </a>
        </div>

        <div class="overflow-x-auto document-table-container rounded-xl border border-green-800 shadow-2xl shadow-green-900/10">
            <table class="w-full text-left">
                <thead class="bg-green-900/40 text-gray-200 uppercase text-sm tracking-wider">
                    <tr>
                        <th class="p-4 border-b border-green-800">Title</th>
                        <th class="p-4 border-b border-green-800">Type</th>
                        <th class="p-4 border-b border-green-800">Status</th>
                        <th class="p-4 border-b border-green-800 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-[#0f1511] text-gray-300">

                                        <?php 
                    $docs = $docs ?? [];
                    if (!empty($docs)):
                    foreach($docs as $doc): 
                        // Safely extract data, assuming objects/arrays
                        $doc_id = $doc['id'] ?? $doc->id ?? 0;
                        $doc_title = $doc['title'] ?? $doc->title ?? '';
                        $doc_file_name = $doc['file_name'] ?? $doc->file_name ?? '';
                        $doc_status = $doc['status'] ?? $doc->status ?? '';
                        $doc_type = $doc['type'] ?? $doc->type ?? '';
                        $submitter = html_escape(($doc['fname'] ?? $doc->fname ?? '') . ' ' . ($doc['lname'] ?? $doc->lname ?? ''));

                        // Determine status color/class dynamically
                        $status_class = match ($doc_status) {
                            'Approved' => 'text-green-400',
                            'Pending Review' => 'text-yellow-400',
                            'Rejected' => 'text-red-500',
                            default => 'text-gray-400',
                        };
                        // Ensure data passed to JS is correctly escaped/quoted
                        $js_doc = json_encode([
                            'id' => $doc_id, 
                            'title' => $doc_title, 
                            'file_name' => $doc_file_name, 
                            'status' => $doc_status, 
                            'submitter' => $submitter,
                            'type' => $doc_type,
                            'created_at' => $doc['created_at'] ?? $doc->created_at ?? ''
                        ]);
                    ?>
                    <tr class="border-b border-green-800 hover:bg-green-700/10 transition">
                        <td class="p-4 font-medium text-green-200"><?= html_escape($doc_title) ?></td>
                        <td class="p-4"><?= html_escape($doc_type) ?></td>
                        <td class="p-4 font-semibold <?= $status_class ?>"><?= html_escape($doc_status) ?></td>
                        <td class="p-4 text-center">
                                                        <button @click="setDoc(<?= html_escape($js_doc) ?>)" class="text-yellow-400 hover:text-yellow-200 hover:underline transition font-medium mr-4">
                                <i class="fa-solid fa-pen-to-square mr-1"></i> Review
                            </button>
                        </td>
                    </tr>
                    </tr>
                    <?php endforeach; 
                    else: ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500">
                            <i class="fa-solid fa-file-alt text-4xl mb-3 text-green-500"></i>
                            <p class="text-lg">No documents found in the system.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                                    </tbody>
            </table>
        </div>
    </div>

    <style>
        /* MODAL FIX: Defines the grid layout for the internal review panel */
        .review-content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr; /* 2/3 viewer, 1/3 sidebar */
            height: 100%; 
            gap: 1rem;
            padding: 1rem; 
        }
    </style>
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
                        <p class="text-sm text-gray-400">Submitted On: <span x-text="currentDoc.created_at ? new Date(currentDoc.created_at).toLocaleDateString('en-US') : 'N/A'"></span></p>
                    </div>

                    <h5 class="text-md font-bold text-green-300">Update Status:</h5>
                    <div class="space-y-4">
                        
                        <form method="POST" :action="'<?= BASE_URL ?>/org/documents/update_status'">
                            <?php csrf_field(); // <--- FIX: Added CSRF field ?>
                            <input type="hidden" name="document_id" :value="currentDoc.id">
                            <input type="hidden" name="new_status" value="Approved">
                            <input type="hidden" name="document_title" :value="currentDoc.title">

                            <button type="submit" class="w-full bg-green-700 hover:bg-green-600 px-5 py-2 rounded-lg font-medium transition"
                                :disabled="currentDoc.status === 'Approved' || currentDoc.status === 'Archived'"
                                :class="currentDoc.status === 'Approved' || currentDoc.status === 'Archived' ? 'opacity-50 cursor-not-allowed' : ''">
                                <i class="fa-solid fa-thumbs-up mr-2"></i> Approve
                            </button>
                        </form>

                        <form method="POST" :action="'<?= BASE_URL ?>/org/documents/update_status'">
                            <?php csrf_field(); // <--- FIX: Added CSRF field ?>
                            <input type="hidden" name="document_id" :value="currentDoc.id">
                            <input type="hidden" name="new_status" value="Rejected">
                            <input type="hidden" name="document_title" :value="currentDoc.title">
                            
                            <button type="submit" class="w-full bg-red-700 hover:bg-red-600 px-5 py-2 rounded-lg font-medium transition"
                                :disabled="currentDoc.status === 'Rejected' || currentDoc.status === 'Archived'"
                                :class="currentDoc.status === 'Rejected' || currentDoc.status === 'Archived' ? 'opacity-50 cursor-not-allowed' : ''">
                                <i class="fa-solid fa-thumbs-down mr-2"></i> Reject
                            </button>
                        </form>

                        <form method="POST" :action="'<?= BASE_URL ?>/org/documents/update_status'">
                            <?php csrf_field(); // <--- FIX: Added CSRF field ?>
                            <input type="hidden" name="document_id" :value="currentDoc.id">
                            <input type="hidden" name="new_status" value="Archived">
                            <input type="hidden" name="document_title" :value="currentDoc.title">

                            <button type="submit" onclick="return confirm('Confirm archive?')"
                                class="w-full bg-gray-700 hover:bg-gray-600 px-5 py-2 rounded-lg font-medium transition"
                                :disabled="currentDoc.status === 'Archived'"
                                :class="currentDoc.status === 'Archived' ? 'opacity-50 cursor-not-allowed' : ''">
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