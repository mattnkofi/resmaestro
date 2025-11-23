<?php 
// PHP Helper Functions setup (assuming existence outside this file)
if (!defined('BASE_URL')) define('BASE_URL', '/maestro');
if (!function_exists('html_escape')) {
    function html_escape($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('csrf_field')) {
    function csrf_field() { echo '<input type="hidden" name="csrf_token" value="MOCK_CSRF_TOKEN">'; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Documents - Maestro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
        .review-content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr; /* 2/3 viewer, 1/3 sidebar */
            height: 100%; 
            gap: 1rem;
            padding: 1rem; 
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
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
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
        commentText: '', /* ADDED: State for new comment input */
        
        archiveModalOpen: false,
        docToArchive: { id: 0, title: '' },

        getFileExtension(fileName) {
            return fileName ? fileName.split('.').pop().toLowerCase() : '';
        },
        isImage(fileName) {
            const ext = this.getFileExtension(fileName);
            return ['jpg', 'jpeg', 'png'].includes(ext);
        },
        isPDF(fileName) {
            return this.getFileExtension(fileName) === 'pdf';
        },
        toSentenceCase(str) {
            if (!str) return '';
            return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
        },
        getDocViewerURL(fileName) {
            if (!fileName) return '';
            const publicUrl = '<?= BASE_URL ?>/public/uploads/documents/' + fileName;
            const absoluteUrl = window.location.origin + publicUrl;

            if (this.isPDF(fileName)) {
                return publicUrl;
            }
            if (this.isImage(fileName)) {
                return publicUrl;
            }
            
            return 'https://docs.google.com/gview?url=' + encodeURIComponent(absoluteUrl) + '&embedded=true';
        },
        
        setDoc(doc) { 
            this.currentDoc = doc; 
            this.commentText = ''; /* Clears comment on new doc review */
            this.modalOpen = true; 
        },
        setArchiveDoc(doc) {
            this.docToArchive = { id: doc.id, title: doc.title };
            this.modalOpen = false;
            this.archiveModalOpen = true;
        }
    }" 
    @keydown.escape="modalOpen = false; archiveModalOpen = false;">

    <?php 
    $current_uri = $_SERVER['REQUEST_URI'] ?? '/org/documents/all'; 

    $is_documents_open = str_contains($current_uri, '/org/documents/');
    $is_review_open = str_contains($current_uri, '/org/review/');
    $is_organization_open = str_contains($current_uri, '/org/members/') || str_contains($current_uri, '/org/departments') || str_contains($current_uri, '/org/roles');
    $is_reports_open = str_contains($current_uri, '/org/reports/');

    $q = $q ?? '';
    $status = $status ?? '';
    
    $docs = $docs ?? [];
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
                    <a href="<?=BASE_URL?>/org/documents/approved" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/approved') ? 'text-green-400 font-semibold' : '' ?>">Approved / Noted</a>
                    <a href="<?=BASE_URL?>/org/documents/rejected" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/rejected') ? 'text-green-400 font-semibold' : '' ?>">Rejected</a>
                    <a href="<?=BASE_URL?>/org/documents/archived" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/archived') ? 'text-green-400 font-semibold' : '' ?>">Archived</a>
                </div>
            </div>

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
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <h1 class="text-3xl font-bold text-green-400 mb-4 sm:mb-0">All Documents</h1>
            <a href="<?=BASE_URL?>/org/documents/upload" class="bg-green-700 hover:bg-green-600 px-5 py-2.5 rounded-xl text-lg font-medium transition shadow-lg shadow-green-900/40">
                <i class="fa-solid fa-plus mr-2"></i> Upload Document
            </a>
        </div>

        <form method="GET" action="<?= BASE_URL ?>/org/documents/all">
            <div class="flex flex-col md:flex-row gap-4 mb-8 items-center">
                
                <input type="text" name="q" value="<?= html_escape($q) ?>"
                        placeholder="Search by title or description..." 
                        class="w-full md:w-1/3 bg-green-900/50 border border-green-800 p-3 rounded-xl focus:ring-green-500 focus:border-green-500 transition placeholder-gray-500 text-white">
                
                <select name="status" class="w-full md:w-1/6 bg-green-900/50 border border-green-800 p-3 rounded-xl text-white">
                    <option value="">Filter by Status (All)</option>
                    <?php 
                    $doc_statuses = ['Pending Review', 'Approved', 'Rejected', 'Archived'];
                    foreach ($doc_statuses as $doc_status_item): 
                        $val = $doc_status_item;
                        $is_selected = ($status === $val);
                    ?>
                        <option value="<?= html_escape($val) ?>" <?= $is_selected ? 'selected' : '' ?>>
                            <?= $doc_status_item ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="bg-green-700 hover:bg-green-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-green-900/40">
                    <i class="fa-solid fa-filter mr-2"></i> Apply Filters
                </button>
                
                <?php if (!empty($q) || !empty($status)): ?>
                    <a href="<?= BASE_URL ?>/org/documents/all" class="bg-gray-700 hover:bg-gray-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-gray-900/40">
                        <i class="fa-solid fa-xmark mr-2"></i> Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>

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
                    $doc_id = $doc['id'] ?? $doc->id ?? 0;
                    $doc_title = $doc['title'] ?? $doc->title ?? '';
                    $doc_file_name = $doc['file_name'] ?? $doc->file_name ?? '';
                    $doc_status = $doc['status'] ?? $doc->status ?? '';
                    $doc_type = $doc['type'] ?? $doc->type ?? '';
                    $submitter = html_escape(($doc['fname'] ?? $doc->fname ?? '') . ' ' . ($doc['lname'] ?? $doc->lname ?? ''));
                    $doc_created_at = $doc['created_at'] ?? $doc->created_at ?? '';

                    $doc_created_at_display = 'N/A';
                    if (!empty($doc_created_at)) {
                        try {
                            $date_obj = new DateTime($doc_created_at);
                            $date_obj->setTimezone(new DateTimeZone('Asia/Manila')); 
                            $doc_created_at_display = $date_obj->format('M d, Y');
                        } catch (\Exception $e) {
                            $doc_created_at_display = date('M d, Y', strtotime($doc_created_at)); // Fallback
                        }
                    }

                    $status_class = match ($doc_status) {
                        'Approved' => 'text-green-400',
                        'Pending Review' => 'text-yellow-400',
                        'Rejected' => 'text-red-500',
                        default => 'text-gray-400',
                    };

                    $js_doc = json_encode([
                        'id' => $doc_id, 
                        'title' => $doc_title, 
                        'file_name' => $doc_file_name, 
                        'status' => $doc_status, 
                        'submitter' => $submitter,
                        'type' => $doc_type,
                        'created_at' => $doc_created_at_display
                    ]);
                ?>
                    <tr class="border-b border-green-800 hover:bg-green-700/10 transition">
                        <td class="p-4 font-medium text-green-200"><?= html_escape($doc_title) ?></td>
                        <td class="p-4"><?= html_escape($doc_type) ?></td>
                        <td class="p-4 font-semibold <?= $status_class ?>"><?= html_escape($doc_status) ?></td>
                        <td class="p-4 text-center">
                            <button @click="setDoc(<?= html_escape($js_doc) ?>)"
                                class="font-medium mr-4 transition"
                                :class="currentDoc.status === 'Pending Review' ? 'text-yellow-400 hover:text-yellow-200' : 'text-green-400 hover:text-green-200'">
                                <i class="fa-solid" :class="currentDoc.status === 'Pending Review' ? 'fa-pen-to-square' : 'fa-eye'"></i> 
                                <span x-text="currentDoc.status === 'Pending Review' ? 'Review Now' : 'View Details'">View Details</span>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; 
                    else: ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500">
                            <i class="fa-solid fa-file-alt text-4xl mb-3 text-green-500"></i>
                            <p class="text-lg">No documents found matching your criteria.</p>
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
                    <h4 class="text-lg font-semibold text-gray-400 mb-3">Document Content 
                        <span x-text="'(' + getFileExtension(currentDoc.file_name).toUpperCase() + ' Viewer)'" class="text-yellow-400"></span>
                    </h4>
                    
                    <div x-show="isImage(currentDoc.file_name)" 
                         class="w-full flex-1 border border-gray-700 rounded-lg bg-gray-900 flex items-center justify-center overflow-hidden">
                        <img :src="'<?= BASE_URL ?>/public/uploads/documents/' + currentDoc.file_name" 
                             :alt="'Viewing ' + currentDoc.title"
                             class="max-w-full max-h-full object-contain">
                    </div>

                    <div x-show="!isImage(currentDoc.file_name)" class="w-full flex-1 flex flex-col">
                        <iframe 
                            :src="getDocViewerURL(currentDoc.file_name)" 
                            class="w-full flex-1 border border-gray-700 rounded-lg bg-gray-900" 
                            frameborder="0"
                            allowfullscreen>
                        </iframe>

                        <template x-if="!isPDF(currentDoc.file_name)">
                            <div class="mt-2 text-center text-sm text-gray-500">
                                If the document viewer is blank or fails, use the direct download:
                                <a :href="'<?= BASE_URL ?>/public/uploads/documents/' + currentDoc.file_name" download
                                   class="text-yellow-400 hover:text-yellow-200 underline">
                                    <i class="fa-solid fa-download mr-1"></i> Direct Download (<span x-text="toSentenceCase(getFileExtension(currentDoc.file_name))"></span>)
                                </a>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="pl-4 space-y-6 flex flex-col">
                    <h4 class="text-lg font-semibold text-gray-400">Review Details & Status</h4>

                    <div class="bg-green-950/50 p-4 rounded-lg border border-green-800">
                        <p class="text-sm text-gray-400">Status: <span :class="currentDoc.status === 'Approved' ? 'text-green-300' : (currentDoc.status === 'Rejected' ? 'text-red-300' : 'text-yellow-300')" x-text="toSentenceCase(currentDoc.status)"></span></p>
                        <p class="text-sm text-gray-400">Type: <span x-text="toSentenceCase(currentDoc.type)"></span></p>
                        <p class="text-sm text-gray-400">Submitted By: <span x-text="currentDoc.submitter"></span></p>
                        <p class="text-sm text-gray-400">Submitted On: <span x-text="currentDoc.created_at ? new Date(currentDoc.created_at).toLocaleDateString('en-US') : 'N/A'"></span></p>
                    </div>

                    <template x-if="currentDoc.status === 'Pending Review'">
                        <div>
                            <div class="space-y-3">
                                <h5 class="text-md font-bold text-green-300 flex justify-between items-center border-t border-green-800/50 pt-4">
                                    Add Review Comment
                                    <a :href="'<?= BASE_URL ?>/org/review/comments/' + currentDoc.id" 
                                        class="text-xs text-yellow-400 hover:underline" target="_blank">
                                        <i class="fa-solid fa-comments mr-1"></i> View All Comments
                                    </a>
                                </h5>

                                <form method="POST" :action="'<?= BASE_URL ?>/org/review/add_comment'" class="space-y-2">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="document_id" :value="currentDoc.id">
                                    
                                    <textarea name="comment_text" rows="3" placeholder="Enter your feedback here..." 
                                        x-model="commentText"
                                        class="w-full bg-gray-900 border border-green-800 p-2 rounded-lg text-sm text-white focus:ring-green-500 focus:border-green-500"></textarea>
                                    
                                    <button type="submit" 
                                        :disabled="commentText.trim().length === 0"
                                        :class="commentText.trim().length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-600'"
                                        class="w-full bg-green-700 px-3 py-1 rounded-lg text-sm font-medium transition">
                                        <i class="fa-solid fa-comment-dots mr-1"></i> Post Comment
                                    </button>
                                </form>
                            </div>

                            <h5 class="text-md font-bold text-green-300 border-t border-green-800/50 pt-4">Update Status:</h5>
                            <div class="space-y-4">
                                
                                <form method="POST" :action="'<?= BASE_URL ?>/org/documents/update_status'">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="document_id" :value="currentDoc.id">
                                    <input type="hidden" name="new_status" value="Approved">
                                    <input type="hidden" name="document_title" :value="currentDoc.title">

                                    <button type="submit" class="w-full bg-green-700 hover:bg-green-600 px-5 py-2 rounded-lg font-medium transition">
                                        <i class="fa-solid fa-thumbs-up mr-2"></i> Approve
                                    </button>
                                </form>

                                <form method="POST" :action="'<?= BASE_URL ?>/org/documents/update_status'">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="document_id" :value="currentDoc.id">
                                    <input type="hidden" name="new_status" value="Rejected">
                                    <input type="hidden" name="document_title" :value="currentDoc.title">
                                    
                                    <button type="submit" class="w-full bg-red-700 hover:bg-red-600 px-5 py-2 rounded-lg font-medium transition">
                                        <i class="fa-solid fa-thumbs-down mr-2"></i> Reject
                                    </button>
                                </form>

                                <button type="button" 
                                    @click="setArchiveDoc(currentDoc)" 
                                    class="w-full bg-gray-700 hover:bg-gray-600 px-5 py-2 rounded-lg font-medium transition">
                                    <i class="fa-solid fa-box-archive mr-2"></i> Archive
                                </button>
                            </div>
                        </div>
                        </template>
                    
                    <template x-if="currentDoc.status !== 'Pending Review'">
                        <div class="space-y-4 border-t border-green-800/50 pt-4">
                            <p class="text-lg font-semibold text-gray-500">No review actions available for this status.</p>
                            <button type="button" 
                                @click="setArchiveDoc(currentDoc)" 
                                class="w-full bg-gray-700 hover:bg-gray-600 px-5 py-2 rounded-lg font-medium transition"
                                :disabled="currentDoc.status === 'Archived'"
                                :class="currentDoc.status === 'Archived' ? 'opacity-50 cursor-not-allowed' : ''">
                                <i class="fa-solid fa-box-archive mr-2"></i> Archive Document
                            </button>
                            
                            <a :href="'<?= BASE_URL ?>/public/uploads/documents/' + currentDoc.file_name" download
                               class="w-full inline-block text-center bg-green-700 hover:bg-green-600 px-5 py-2 rounded-lg font-medium transition">
                                <i class="fa-solid fa-download mr-1"></i> Download File
                            </a>
                        </div>
                    </template>
                    
                    <button @click="modalOpen = false" class="w-full text-gray-500 hover:text-gray-300 transition mt-4">Close Review</button>
                </div>
            </div>

        </div>
    </div>
    <div x-show="archiveModalOpen" 
        x-transition:enter="ease-out duration-300"
        x-transition:leave="ease-in duration-200"
        class="fixed inset-0 z-[60] overflow-y-auto bg-maestro-bg bg-opacity-90 flex items-center justify-center" 
        style="display: none;">

        <div @click.outside="archiveModalOpen = false" class="w-full max-w-md mx-auto bg-[#0f1511] rounded-xl shadow-2xl border border-red-800">
            
            <header class="p-4 border-b border-red-800 flex justify-between items-center bg-sidebar-dark">
                <h3 class="text-xl font-bold text-red-400">Confirm Archive</h3>
                <button @click="archiveModalOpen = false" class="text-gray-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </header>

            <div class="p-6 space-y-4">
                <p class="text-gray-300">
                    Are you sure you want to <b>Archive</b> the document: 
                    <span class="font-semibold text-yellow-300" x-text="docToArchive.title"></span>?
                </p>
                <p class="text-sm text-gray-500">
                    Archiving will remove this document from all active lists. It can be viewed and unarchived later from the "Archived" section.
                </p>

                <div class="flex justify-end gap-3 pt-4">
                    <button @click="archiveModalOpen = false" class="px-5 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 font-medium transition">
                        Cancel
                    </button>

                    <form method="POST" :action="'<?= BASE_URL ?>/org/documents/update_status'">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="document_id" :value="docToArchive.id">
                        <input type="hidden" name="new_status" value="Archived">
                        <input type="hidden" name="document_title" :value="docToArchive.title">

                        <button type="submit" class="bg-red-700 hover:bg-red-600 px-5 py-2 rounded-lg font-medium transition shadow-lg shadow-red-900/40">
                            <i class="fa-solid fa-box-archive mr-2"></i> Confirm Archive
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </body>
</html>