<?php include 'sidebar.php'; // Include the existing sidebar structure ?>

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
            <p class="text-4xl font-bold mt-3">124</p>
            <p class="text-sm text-green-400 mt-1 flex items-center gap-1">
                <i class="fa-solid fa-arrow-up text-xs"></i> 12% Last Month
            </p>
        </div>

        <div class="dashboard-card p-5">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-400 uppercase tracking-wider">Pending Reviews</span>
                <i class="fa-solid fa-hourglass-half text-yellow-400 text-2xl"></i>
            </div>
            <p class="text-4xl font-bold mt-3">32</p>
            <p class="text-sm text-yellow-400 mt-1">2 are overdue</p>
        </div>

        <div class="dashboard-card p-5">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-400 uppercase tracking-wider">Approved / Noted</span>
                <i class="fa-solid fa-circle-check text-green-400 text-2xl"></i>
            </div>
            <p class="text-4xl font-bold mt-3">68</p>
            <p class="text-sm text-gray-500 mt-1">Ready for circulation</p>
        </div>

        <div class="dashboard-card p-5">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-400 uppercase tracking-wider">New Members</span>
                <i class="fa-solid fa-user-plus text-blue-400 text-2xl"></i>
            </div>
            <p class="text-4xl font-bold mt-3">4</p>
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

        <div class="lg:col-span-2">
            <div class="dashboard-card p-6 h-96 mb-6">
                <h2 class="text-xl font-semibold text-green-300 mb-4">Document Flow Over Time</h2>
                <div class="h-64 flex items-center justify-center text-gray-500 border border-dashed border-green-800 rounded-lg">

                    Document Flow Chart Placeholder
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button class="bg-green-700/40 text-green-200 p-3 rounded-lg flex items-center justify-center gap-2 hover:bg-green-600/60 transition">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span class="text-sm font-medium">Upload Document</span>
                </button>
                <button class="bg-blue-700/40 text-blue-200 p-3 rounded-lg flex items-center justify-center gap-2 hover:bg-blue-600/60 transition">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <span class="text-sm font-medium">Start Review</span>
                </button>
                <button class="bg-yellow-700/40 text-yellow-200 p-3 rounded-lg flex items-center justify-center gap-2 hover:bg-yellow-600/60 transition">
                    <i class="fa-solid fa-user-group"></i>
                    <span class="text-sm font-medium">Manage Team</span>
                </button>
                <button class="bg-gray-700/40 text-gray-300 p-3 rounded-lg flex items-center justify-center gap-2 hover:bg-gray-600/60 transition">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span class="text-sm font-medium">Advanced Search</span>
                </button>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="dashboard-card p-6 h-full">
                <h2 class="text-xl font-semibold text-green-300 mb-4 flex items-center justify-between">
                    Recent Activity
                    <i class="fa-solid fa-clock-rotate-left text-green-500 text-lg"></i>
                </h2>

                <ul class="space-y-4 max-h-96 overflow-y-auto pr-2">
                    <li class="border-l-4 border-green-500 pl-3">
                        <p class="text-sm font-medium text-green-400">Document <b>Q4 Report</b> Approved</p>
                        <p class="text-xs text-gray-500">By Admin User - 5 minutes ago</p>
                    </li>
                    <li class="border-l-4 border-blue-500 pl-3">
                        <p class="text-sm font-medium text-blue-400">Comment added to <b>Proposal 2024</b></p>
                        <p class="text-xs text-gray-500">By Jane Doe - 30 minutes ago</p>
                    </li>
                    <li class="border-l-4 border-gray-500 pl-3">
                        <p class="text-sm font-medium text-gray-300">New Document <b>Marketing Plan</b> Uploaded</p>
                        <p class="text-xs text-gray-500">By John Smith - 1 hour ago</p>
                    </li>
                    <li class="border-l-4 border-red-500 pl-3">
                        <p class="text-sm font-medium text-red-400">Document <b>Budget Draft</b> Rejected</p>
                        <p class="text-xs text-gray-500">By Reviewer 2 - 2 hours ago</p>
                    </li>
                    <li class="border-l-4 border-yellow-500 pl-3">
                        <p class="text-sm font-medium text-yellow-400">Assigned Review for <b>Policy Update</b></p>
                        <p class="text-xs text-gray-500">System Bot - Yesterday</p>
                    </li>
                    <li class="border-l-4 border-green-500 pl-3">
                        <p class="text-sm font-medium text-green-400">Document <b>HR Manual</b> Approved</p>
                        <p class="text-xs text-gray-500">By Admin User - 2 days ago</p>
                    </li>
                    <li class="border-l-4 border-blue-500 pl-3">
                        <p class="text-sm font-medium text-blue-400">Comment added to <b>SOP V1.1</b></p>
                        <p class="text-xs text-gray-500">By Jane Doe - 3 days ago</p>
                    </li>
                </ul>
            </div>
        </div>

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