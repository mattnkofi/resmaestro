<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

$events = $events ?? []; 
$BASE_URL = BASE_URL ?? '/maestro';
$current_uri = $_SERVER['REQUEST_URI'] ?? '/org/events'; 

if (!function_exists('html_escape')) {
    function html_escape($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('csrf_field')) {
    function csrf_field() { echo '<input type="hidden" name="csrf_token" value="' . ($_SESSION['csrf_token'] ?? 'MOCK_CSRF_TOKEN') . '">'; }
}

// Function to format DateTime for Google Calendar (YYYYMMDDTHHMMSS)
function gcal_date_format($datetime_string) {
    return date('Ymd\THis', strtotime($datetime_string));
}

// Include the necessary sidebar styles and structure
include 'sidebar.php'; 
?>
<style>
    /* Ensure Poppins font is applied correctly across Maestro UI */
    body { font-family: 'Poppins', sans-serif; }
    .maestro-bg { background-color: #0b0f0c; }
    
    /* Styling to make the embedded calendar look integrated */
    .gcal-iframe {
        border-radius: 0.5rem; /* Match Maestro UI rounded corners */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
    }
</style>
<body class="maestro-bg text-white font-poppins" x-data="{ addModalOpen: false }" @keydown.escape="addModalOpen = false">

<div class="ml-64 p-8 maestro-bg min-h-screen relative"> 
    <div class="flex justify-between items-center mb-6 border-b border-green-800/50 pb-3">
        <h1 class="text-3xl font-bold text-green-400 tracking-wide">
            <i class="fa-solid fa-calendar-days mr-2"></i> Event Manager
        </h1>
        <button @click="addModalOpen = true" class="bg-yellow-600 hover:bg-yellow-500 px-4 py-2 rounded-lg text-sm font-medium transition shadow-md">
            <i class="fa-solid fa-plus-circle mr-2"></i> Add New Event
        </button>
    </div>

    <?php if (function_exists('flash_alert')) flash_alert(); ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-4">
            <h2 class="text-xl font-bold text-yellow-400">Calendar and Events</h2>
            
            <div class="bg-green-950/50 p-3 rounded-xl border border-green-800 shadow-xl" style="height: 600px;">
                <iframe 
                    src="https://calendar.google.com/calendar/embed?src=en.philippines%23holiday%40group.v.calendar.google.com&ctz=Asia%2FManila&hl=en" 
                    class="gcal-iframe"
                    width="100%" 
                    height="100%" 
                    frameborder="0" 
                    scrolling="no"
                    title="Google Calendar Embed">
                </iframe>
                <p class="text-xs text-gray-500 mt-8 text-center">Check calendar to align events with holidays.</p>
            </div>
        </div>

        <div class="space-y-4">
            <h2 class="text-xl font-bold text-green-300">Upcoming Maestro Events</h2>
            
            <div class="space-y-3">
                <?php if (empty($events)): ?>
                    <div class="p-4 text-center text-gray-500 bg-green-950/20 rounded-xl border border-green-800">
                        <i class="fa-solid fa-calendar-xmark text-4xl mb-3 text-red-500"></i>
                        <p class="text-lg">No events scheduled in Maestro database.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($events as $event): 
                        $announcer_name = html_escape(trim($event['fname'] . ' ' . $event['lname']));

                        // 1. Format Dates for GCal (YYYYMMDDTHHMMSS)
                        $start_time_gcal = gcal_date_format($event['start_time']);
                        $end_time_gcal = gcal_date_format($event['end_time']);
                        
                        // 2. Build URL Parameters for the external link
                        $gcal_params = http_build_query([
                            'text'     => $event['title'],
                            'dates'    => "{$start_time_gcal}/{$end_time_gcal}",
                            'details'  => $event['description'] . "\n\n---\nPosted by: " . $announcer_name,
                            'location' => $event['location'],
                            'action'   => 'TEMPLATE', 
                        ]);

                        // 3. Construct Final Google Calendar URL
                        $google_calendar_url = "https://calendar.google.com/calendar/render?{$gcal_params}";
                    ?>
                    <div class="bg-green-950/50 rounded-xl shadow-lg border border-l-4 border-green-500 p-4 flex justify-between items-start">
                        <div class="flex-1 space-y-0.5">
                            <h3 class="text-md font-bold text-green-200"><?= html_escape($event['title']) ?></h3>
                            <p class="text-xs text-gray-400">
                                <i class="fa-solid fa-clock mr-1 text-yellow-400"></i> <?= date('M d, h:i A', strtotime($event['start_time'])) ?>
                            </p>
                            <p class="text-xs text-gray-400">
                                <i class="fa-solid fa-location-dot mr-1 text-blue-400"></i> <?= html_escape($event['location']) ?>
                            </p>
                        </div>
                        
                        <a href="<?= $google_calendar_url ?>" target="_blank"
                           class="bg-yellow-700 hover:bg-yellow-600 px-3 py-1 rounded-lg transition text-sm text-black font-medium whitespace-nowrap self-center">
                            <i class="fa-brands fa-google mr-1"></i> Add
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div x-show="addModalOpen" x-cloak 
    x-transition:enter="ease-out duration-300" x-transition:leave="ease-in duration-200"
    class="fixed inset-0 z-[60] overflow-y-auto bg-maestro-bg bg-opacity-95 flex items-center justify-center p-4" 
    style="display: none;">

    <div @click.outside="addModalOpen = false" class="w-full max-w-2xl bg-[#0f1511] rounded-xl shadow-2xl border border-yellow-800">
        
        <header class="p-4 border-b border-yellow-800 flex justify-between items-center bg-sidebar-dark rounded-t-xl">
            <h3 class="text-xl font-bold text-yellow-400">
                <i class="fa-solid fa-plus-circle mr-2"></i> Schedule New Event (In Maestro)
            </h3>
            <button @click="addModalOpen = false" class="text-gray-400 hover:text-white transition">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>
        </header>

        <form method="POST" action="<?= $BASE_URL ?>/org/events/store" class="p-6 space-y-5">
            <?php csrf_field(); ?>

            <div>
                <label for="event_title" class="block text-sm font-medium mb-2 text-gray-300">Event Title <span class="text-red-500">*</span></label>
                <input type="text" id="event_title" name="title" required
                    class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-green-100"
                    placeholder="E.g., General Assembly, Department Meeting">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_time" class="block text-sm font-medium mb-2 text-gray-300">Start Date/Time <span class="text-red-500">*</span></label>
                    <input type="datetime-local" id="start_time" name="start_time" required
                        class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-green-100">
                </div>
                <div>
                    <label for="end_time" class="block text-sm font-medium mb-2 text-gray-300">End Date/Time <span class="text-red-500">*</span></label>
                    <input type="datetime-local" id="end_time" name="end_time" required
                        class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-green-100">
                </div>
            </div>

            <div>
                <label for="event_location" class="block text-sm font-medium mb-2 text-gray-300">Location</label>
                <input type="text" id="event_location" name="location" 
                    class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-green-100"
                    placeholder="E.g., MinSU Covered Court, Online via Zoom">
            </div>
            
            <div>
                <label for="event_description" class="block text-sm font-medium mb-2 text-gray-300">Description</label>
                <textarea id="event_description" name="description" rows="3"
                    class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-green-100"
                    placeholder="Details about the event, required attendees, agenda, etc."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" @click="addModalOpen = false" class="px-5 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 font-medium transition">
                    Cancel
                </button>
                <button type="submit" class="bg-yellow-700 hover:bg-yellow-600 px-5 py-2 rounded-lg font-bold text-lg transition shadow-lg">
                    <i class="fa-solid fa-calendar-plus mr-2"></i> Schedule Event
                </button>
            </div>
        </form>
    </div>
</div>

</body>