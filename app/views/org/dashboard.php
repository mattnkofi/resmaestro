<<?php 
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


<div class="ml-64 p-8 maestro-bg min-h-screen">
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

    <div class="dashboard-card p-6 mb-6">
        <h2 class="text-xl font-semibold text-green-300 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-map-location-dot text-green-500"></i> Office Locations
        </h2>
        <div id="office-map" class="h-64 bg-gray-900 rounded-lg">
            </div>
        <p class="text-sm text-gray-500 mt-3">Showing offices relative to your current location (requires Google Maps API and geolocation).</p>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    </div>

</div>

<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyACBACrU8lDJanwGFnev_3vFPmIshZEsUs&callback=initMap">
</script>

<script>
    function initMap() {
        // Default coordinates for the map center (e.g., New York City)
        const defaultCenter = { lat: 40.7128, lng: -74.0060 };
        
        // Example office locations (add your actual office Lat/Lng here)
        const officeLocations = [
            { lat: 40.7580, lng: -73.9855, title: 'HQ - Times Square' },
            { lat: 34.0522, lng: -118.2437, title: 'West Coast Office' }
        ];

        // Create the map instance
        const map = new google.maps.Map(document.getElementById('office-map'), {
            zoom: 10,
            center: defaultCenter,
            // You can customize the map style here if needed
            mapId: "DEMO_MAP_ID" // Use a Map ID for custom styling from the Cloud Console
        });
        
        // Add markers for each office location
        officeLocations.forEach(location => {
            new google.maps.Marker({
                position: { lat: location.lat, lng: location.lng },
                map,
                title: location.title
            });
        });
        
        // OPTIONAL: Add current user location (requires enabling the Geolocation API)
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    
                    // Add a marker for the user's current location
                    new google.maps.Marker({
                        position: pos,
                        map,
                        title: 'Your Location',
                        icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png' // Different color marker
                    });
                    
                    // Center the map on the user's location
                    map.setCenter(pos);
                },
                () => {
                    console.log("Geolocation failed or permission denied.");
                }
            );
        }
    }
</script>