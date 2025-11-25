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
$BASE_URL = BASE_URL ?? '';
$current_uri = $_SERVER['REQUEST_URI'] ?? '/org/dashboard'; 

$is_documents_open = str_contains($current_uri, '/org/documents/');
$is_review_open = str_contains($current_uri, '/org/review/');
$is_organization_open = str_contains($current_uri, '/org/members/') || str_contains($current_uri, '/org/departments') || str_contains($current_uri, '/org/roles');
$is_reports_open = str_contains($current_uri, '/org/reports/');
// --- END FIX ---

include 'sidebar.php'; // Include the existing sidebar structure 
?>

<div class="ml-64 p-8 maestro-bg min-h-screen relative"> 
    <h1 class="text-3xl font-bold text-green-400 mb-6 tracking-wide">
        Organization Dashboard
    </h1>

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
    
    <div id="draggable-map" class="dashboard-card p-4 absolute top-8 right-8 z-10 w-96 shadow-2xl cursor-grab">
        <div id="map-header" class="p-2 -mx-4 -mt-4 mb-2 rounded-t-lg bg-gray-700 hover:bg-gray-600 transition duration-150 ease-in-out cursor-grab active:cursor-grabbing">
            <h2 class="text-lg font-semibold text-gray-200 flex items-center gap-2">
                <i class="fa-solid fa-arrows-alt-v text-red-400"></i> Calapan Mindoro State University
            </h2>
        </div>
        
        <div class="overflow-hidden rounded-lg">
            <iframe 
                src="https://maps.google.com/maps?q=Calapan%20Mindoro%20State%20University&z=15&output=embed" 
                width="100%" 
                height="200" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
    <script>
        function makeElementDraggable(element, handle) {
            let isDragging = false;
            let offset = { x: 0, y: 0 };
            
            // Ensure the element can be positioned absolutely/fixed
            element.style.position = 'absolute';

            handle.addEventListener('mousedown', (e) => {
                isDragging = true;
                // Calculate offset between mouse position and element's top-left corner
                offset.x = e.clientX - element.getBoundingClientRect().left;
                offset.y = e.clientY - element.getBoundingClientRect().top;
                
                element.style.cursor = 'grabbing';
                e.preventDefault(); // Prevent text selection while dragging
            });

            document.addEventListener('mousemove', (e) => {
                if (!isDragging) return;

                // Calculate new position
                let newX = e.clientX - offset.x;
                let newY = e.clientY - offset.y;
                
                // Apply new position
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
            makeElementDraggable(mapElement, mapHandle);
        }
    </script>
</div>