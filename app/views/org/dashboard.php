<?php 
// Variables are now expected to be defined by the controller before inclusion.
// We define safe defaults here for development safety if variables are not passed.
$stats = $stats ?? [
    'total_documents' => 0, 
    'pending_reviews' => 0, 
    'approved_documents' => 0, 
    'new_members' => 0
];

$recent_activity = $recent_activity ?? [];

if (!defined('BASE_URL')) define('BASE_URL', '/maestro');
$BASE_URL = $BASE_URL ?? '';
$current_uri = $_SERVER['REQUEST_URI'] ?? '/org/dashboard'; 

$is_documents_open = str_contains($current_uri, '/org/documents/');
$is_review_open = str_contains($current_uri, '/org/review/');
$is_organization_open = str_contains($current_uri, '/org/members/') || str_contains($current_uri, '/org/departments') || str_contains($current_uri, '/org/roles');
$is_reports_open = str_contains($current_uri, '/org/reports/');
// --- END FIX ---

// --- NEW PHP LOGIC (Announcements) ---
$announcements = $announcements ?? []; // Data passed from controller
$current_user_id = (int)($_SESSION['user_id'] ?? 0);
$current_user_role = $_SESSION['user_role'] ?? '';
$admin_roles = ['Administrator', 'President', 'Adviser'];
$can_manage_announcements = in_array($current_user_role, $admin_roles);
// --- END NEW PHP LOGIC ---

include 'sidebar.php'; // Include the existing sidebar structure 
?>
<body class="maestro-bg text-white" x-data="{ 
    BASE_URL: '<?= $BASE_URL ?>',
    isModalOpen: false, 
    isDeleteModalOpen: false,
    modalTitle: 'Post New Announcement',
    modalAction: 'store', // 'store' or 'update'
    currentAnnouncement: { id: 0, title: '', content: '' },
    isMapVisible: true, // <--- NEW: Map Visibility State
    
    openCreateModal() {
        this.modalTitle = 'Post New Announcement';
        this.modalAction = 'store';
        this.currentAnnouncement = { id: 0, title: '', content: '' };
        this.isModalOpen = true;
    },
    openEditModal(announcement) {
        this.modalTitle = 'Edit Announcement';
        this.modalAction = 'update';
        this.currentAnnouncement = { ...announcement };
        this.isModalOpen = true;
    },
    openDeleteModal(announcement) {
        this.currentAnnouncement = { ...announcement };
        this.isDeleteModalOpen = true;
    },
    canEditDelete() {
        // Check if current user has admin roles
        return <?= $can_manage_announcements ? 'true' : 'false' ?>;
    }
}" @keydown.escape="isModalOpen = false; isDeleteModalOpen = false; isMapVisible = false">

<div class="ml-64 p-8 maestro-bg min-h-screen relative"> 
    <h1 class="text-3xl font-bold text-green-400 mb-6 tracking-wide">
        Organization Dashboard
    </h1>

    <?php if (function_exists('flash_alert')) flash_alert(); // Display flash alerts ?>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">
        <div class="dashboard-card p-5">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-400 uppercase tracking-wider">Total Documents</span>
                <i class="fa-solid fa-file-lines text-green-400 text-2xl"></i>
            </div>
            <p class="text-4xl font-bold mt-3"><?= $stats['total_documents'] ?></p>
            <p class="text-sm text-green-400 mt-1 flex items-center gap-1">
                <i class="fa-solid fa-arrow-up text-xs"></i> 12% Last Month
            </p>
        </div>

        <div class="dashboard-card p-5">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-400 uppercase tracking-wider">Pending Reviews</span>
                <i class="fa-solid fa-hourglass-half text-yellow-400 text-2xl"></i>
            </div>
            <p class="text-4xl font-bold mt-3"><?= $stats['pending_reviews'] ?></p>
            <p class="text-sm text-yellow-400 mt-1">2 are overdue</p>
        </div>

        <div class="dashboard-card p-5">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-400 uppercase tracking-wider">Approved / Noted</span>
                <i class="fa-solid fa-circle-check text-green-400 text-2xl"></i>
            </div>
            <p class="text-4xl font-bold mt-3"><?= $stats['approved_documents'] ?></p>
            <p class="text-sm text-gray-500 mt-1">Ready for circulation</p>
        </div>

        <div class="dashboard-card p-5">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-400 uppercase tracking-wider">New Members</span>
                <i class="fa-solid fa-user-plus text-blue-400 text-2xl"></i>
            </div>
            <p class="text-4xl font-bold mt-3"><?= $stats['new_members'] ?></p>
            <p class="text-sm text-blue-400 mt-1 flex items-center gap-1">
                <i class="fa-solid fa-arrow-up text-xs"></i> +1 Since Last Week
            </p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 gap-6">
        
        <div>
            <div class="flex justify-between items-center mb-4 border-b border-green-800/50 pb-3">
                <h2 class="text-2xl font-bold text-yellow-400">
                    <i class="fa-solid fa-bullhorn mr-2"></i> Announcements
                </h2>
                <button @click="openCreateModal()" class="bg-green-700 hover:bg-green-600 px-4 py-2 rounded-lg text-sm font-medium transition shadow-md">
                    <i class="fa-solid fa-plus-circle mr-2"></i> New Post
                </button>
            </div>
            
            <div class="space-y-4">
                <?php if (empty($announcements)): ?>
                    <div class="p-8 text-center text-gray-500 bg-green-950/20 rounded-xl border border-green-800">
                        <i class="fa-solid fa-bell-slash text-4xl mb-3 text-red-500"></i>
                        <p class="text-lg">No announcements posted yet.</p>
                        <p class="text-sm mt-2">Be the first to share an update!</p>
                    </div>
                <?php else: 
                    foreach ($announcements as $announcement):
                        $announcer_name = html_escape(trim(($announcement['fname'] ?? 'Unknown') . ' ' . ($announcement['lname'] ?? 'User')));
                        $announcer_dept = html_escape($announcement['dept_name'] ?? 'Unassigned');
                        $posted_date = date('M d, Y H:i', strtotime($announcement['created_at']));
                        $announcement_data = json_encode([
                            'id' => $announcement['id'],
                            'title' => $announcement['title'],
                            'content' => $announcement['content'],
                            'user_id' => $announcement['user_id']
                        ]);
                    ?>
                    <div class="bg-green-950/50 rounded-xl shadow-xl border border-green-800 transition duration-300 hover:border-green-500">
                        <div class="p-4 border-l-4 border-yellow-500 bg-green-900/50 rounded-t-xl flex justify-between items-center">
                            <h3 class="text-lg font-bold text-green-200"><?= html_escape($announcement['title']) ?></h3>
                            
                            <div x-show="canEditDelete()" class="flex space-x-3 text-sm">
                                <button @click="openEditModal(<?= html_escape($announcement_data) ?>)" 
                                        class="text-yellow-400 hover:text-yellow-300 transition">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                <button @click="openDeleteModal(<?= html_escape($announcement_data) ?>)" 
                                        class="text-red-400 hover:text-red-300 transition">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>

                        <div class="p-4">
                            <p class="text-gray-300 mb-4 whitespace-pre-wrap"><?= html_escape($announcement['content']) ?></p>
                        </div>
                        
                        <div class="bg-green-900/30 p-3 rounded-b-xl flex justify-between items-center text-xs text-gray-400 border-t border-green-800">
                            <div class="flex space-x-4">
                                <span title="Posted by"><i class="fa-solid fa-user-tag mr-1 text-green-400"></i> <?= $announcer_name ?></span>
                                <span title="Department"><i class="fa-solid fa-building mr-1 text-blue-400"></i> <?= $announcer_dept ?></span>
                            </div>
                            <span title="Date Posted"><i class="fa-solid fa-clock mr-1 text-yellow-400"></i> <?= $posted_date ?></span>
                        </div>
                    </div>
                    <?php endforeach;
                endif; ?>
            </div>
        </div>
        
    </div>
    
    <div id="draggable-map" 
         x-show="isMapVisible" 
         x-transition
         class="p-2 fixed bottom-8 right-8 z-50 w-80 h-80 shadow-2xl cursor-grab 
                bg-green-950/90 border border-green-700 rounded-xl transition duration-300 
                hover:shadow-green-700/50 hover:border-green-600 hover:scale-[1.01] transform-gpu">
        
        <div id="map-header" 
             class="p-2 -mx-2 -mt-2 mb-2 rounded-t-lg bg-green-800/80 hover:bg-green-800 
                    transition duration-150 ease-in-out cursor-grab active:cursor-grabbing flex justify-between items-center">
            <h2 class="text-sm font-bold text-white flex items-center">
                <span><i class="fa-solid fa-location-dot text-red-400 mr-1"></i> MinSU Campus</span>
                <i class="fa-solid fa-arrows-up-down-left-right text-xs text-gray-300 ml-2"></i>
            </h2>
             <button @click.stop="isMapVisible = false" 
                    class="text-gray-300 hover:text-white transition duration-150 p-1 -mr-1" title="Hide Map">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <div class="overflow-hidden rounded-lg" style="height: calc(100% - 40px);"> 
            <iframe 
                src="https://maps.google.com/maps?q=Calapan%20Mindoro%20State%20University&z=15&output=embed" 
                width="100%" 
                height="100%" 
                style="border:0; filter: grayscale(50%) brightness(0.8);" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
    <script>
        function makeElementDraggable(element, handle) {
            let isDragging = false;
            let offsetX, offsetY;

            handle.addEventListener('mousedown', (e) => {
                isDragging = true;

                // Get viewport coordinates relative to the element's top-left corner
                const rect = element.getBoundingClientRect();
                
                // Calculate offset relative to the element's current top-left corner
                offsetX = e.clientX - rect.left;
                offsetY = e.clientY - rect.top;

                // Anchor position using left/top, clearing initial right/bottom anchoring
                element.style.left = rect.left + 'px';
                element.style.top = rect.top + 'px';
                element.style.right = 'auto'; 
                element.style.bottom = 'auto'; 
                
                element.style.cursor = 'grabbing';
                e.preventDefault();
            });

            document.addEventListener('mousemove', (e) => {
                if (!isDragging) return;

                // Calculate new position by subtracting the stored offset
                let newX = e.clientX - offsetX;
                let newY = e.clientY - offsetY;
                
                element.style.left = newX + 'px';
                element.style.top = newY + 'px';
            });

            document.addEventListener('mouseup', () => {
                if (isDragging) {
                    isDragging = false;
                    element.style.cursor = 'grab';
                }
            });
        }

        const mapElement = document.getElementById('draggable-map');
        const mapHandle = document.getElementById('map-header'); // Use the custom header as the drag handle

        if (mapElement && mapHandle) {
            mapElement.style.position = 'fixed';
            makeElementDraggable(mapElement, mapHandle);
        }
    </script>

    
    <div x-show="isModalOpen" x-cloak 
        x-transition:enter="ease-out duration-300" x-transition:leave="ease-in duration-200"
        class="fixed inset-0 z-[60] overflow-y-auto bg-maestro-bg bg-opacity-95 flex items-center justify-center p-4" 
        style="display: none;">

        <div @click.outside="isModalOpen = false" class="w-full max-w-2xl bg-[#0f1511] rounded-xl shadow-2xl border border-green-800">
            
            <header class="p-4 border-b border-green-800 flex justify-between items-center bg-sidebar-dark rounded-t-xl">
                <h3 class="text-xl font-bold text-green-300" x-text="modalTitle"></h3>
                <button @click="isModalOpen = false" class="text-gray-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </header>

            <form method="POST" :action="BASE_URL + '/org/announcements/' + modalAction" class="p-6 space-y-5">
                <?php 
                if (!function_exists('csrf_field')) { 
                    echo '<input type="hidden" name="csrf_token" value="' . ($_SESSION['csrf_token'] ?? 'MOCK_CSRF_TOKEN') . '">'; 
                } else {
                    csrf_field();
                }
                ?>
                <input type="hidden" name="id" :value="currentAnnouncement.id" x-show="modalAction === 'update'">

                <div>
                    <label for="modal_title" class="block text-sm font-medium mb-2 text-gray-300">Title <span class="text-red-500">*</span></label>
                    <input type="text" id="modal_title" name="title" x-model="currentAnnouncement.title" required
                        class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-green-500 focus:border-green-500 text-green-100">
                </div>
                
                <div>
                    <label for="modal_content" class="block text-sm font-medium mb-2 text-gray-300">Content <span class="text-red-500">*</span></label>
                    <textarea id="modal_content" name="content" x-model="currentAnnouncement.content" rows="6" required
                        class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-green-500 focus:border-green-500 text-green-100"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="isModalOpen = false" class="px-5 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 font-medium transition">
                        Cancel
                    </button>
                    <button type="submit" class="bg-green-700 hover:bg-green-600 px-5 py-2 rounded-lg font-medium transition shadow-lg">
                        <i class="fa-solid fa-save mr-2"></i> <span x-text="modalAction === 'store' ? 'Post Announcement' : 'Save Changes'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="isDeleteModalOpen" x-cloak 
        x-transition:enter="ease-out duration-300" x-transition:leave="ease-in duration-200"
        class="fixed inset-0 z-[70] overflow-y-auto bg-maestro-bg bg-opacity-95 flex items-center justify-center p-4" 
        style="display: none;">

        <div @click.outside="isDeleteModalOpen = false" class="w-full max-w-md bg-[#0f1511] rounded-xl shadow-2xl border border-red-800">
            
            <header class="p-4 border-b border-red-800 flex justify-between items-center bg-sidebar-dark rounded-t-xl">
                <h3 class="text-xl font-bold text-red-400">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i> Confirm Deletion
                </h3>
                <button @click="isDeleteModalOpen = false" class="text-gray-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </header>

            <div class="p-6 space-y-4">
                <p class="text-gray-300">
                    Are you sure you want to delete the announcement: 
                    <span class="font-semibold text-red-300" x-text="'\"' + currentAnnouncement.title + '\"'"></span>?
                </p>
                <p class="text-sm text-gray-500">
                    This action cannot be undone.
                </p>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="isDeleteModalOpen = false" class="px-5 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 font-medium transition">
                        Cancel
                    </button>
                    <form method="POST" :action="BASE_URL + '/org/announcements/delete'">
                        <?php 
                        if (!function_exists('csrf_field')) { 
                            echo '<input type="hidden" name="csrf_token" value="' . ($_SESSION['csrf_token'] ?? 'MOCK_CSRF_TOKEN') . '">'; 
                        } else {
                            csrf_field();
                        }
                        ?>
                        <input type="hidden" name="id" :value="currentAnnouncement.id">
                        <button type="submit" class="bg-red-700 hover:bg-red-600 px-5 py-2 rounded-lg font-medium transition shadow-lg">
                            <i class="fa-solid fa-trash mr-2"></i> Delete Post
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>