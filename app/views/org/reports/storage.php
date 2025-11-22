<?php 
// Note: Helper functions are assumed to be handled by app/views/org/layout_start.php.

// --- 1. Page Specific Variables ---
$title = 'Document Analytics - Maestro UI';

// Data provided by the user and mock extensions (retained for page content)
$document_stats = [
    'total' => 340,
    'approved' => 210,
    'rejected' => 30,
    'pending' => 100,
];

$document_type_breakdown = [
    ['type' => 'Reports', 'total' => 120, 'avg_review_time' => '1.5 days', 'approval_rate' => 85],
    ['type' => 'Policies', 'total' => 80, 'avg_review_time' => '2.1 days', 'approval_rate' => 70],
    ['type' => 'Legal', 'total' => 40, 'avg_review_time' => '0.8 days', 'approval_rate' => 95],
    ['type' => 'Proposals', 'total' => 100, 'avg_review_time' => '3.5 days', 'approval_rate' => 60],
];

// Calculated stats
$overall_approval_rate = $document_stats['total'] > 0 ? round(($document_stats['approved'] / $document_stats['total']) * 100) : 0;

// The redundant sidebar logic is removed.

// --- 2. TEMPLATE INCLUSION ---
include 'app/views/org/layout_start.php'; 
include 'app/views/org/sidebar.php'; 
?>

<div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white" x-data="{}">
    
    <h1 class="text-3xl font-bold text-green-400 mb-8 tracking-wide">
        Document Analytics
    </h1>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
        
        <div class="dashboard-card bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex flex-col justify-between">
            <p class="text-sm text-gray-400 uppercase tracking-wider">Total Documents</p>
            <p class="text-3xl font-bold text-green-400 mt-1"><?= $document_stats['total'] ?></p>
        </div>

        <div class="dashboard-card bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex flex-col justify-between">
            <p class="text-sm text-gray-400 uppercase tracking-wider">Approved</p>
            <p class="text-3xl font-bold text-green-500 mt-1"><?= $document_stats['approved'] ?></p>
        </div>

        <div class="dashboard-card bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex flex-col justify-between">
            <p class="text-sm text-gray-400 uppercase tracking-wider">Pending</p>
            <p class="text-3xl font-bold text-yellow-500 mt-1"><?= $document_stats['pending'] ?></p>
        </div>
        
        <div class="dashboard-card bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex flex-col justify-between">
            <p class="text-sm text-gray-400 uppercase tracking-wider">Rejected</p>
            <p class="text-3xl font-bold text-red-500 mt-1"><?= $document_stats['rejected'] ?></p>
        </div>
    </div>


    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-4">
            <h2 class="text-xl font-semibold text-green-300 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-list-ul text-lg"></i> Breakdown by Document Type
            </h2>
            
            <div class="overflow-x-auto rounded-xl border border-green-800 shadow-2xl shadow-green-900/10">
                <table class="w-full text-left">
                    <thead class="bg-green-900/40 text-gray-200 uppercase text-sm tracking-wider">
                        <tr>
                            <th class="p-4 border-b border-green-800">Document Type</th>
                            <th class="p-4 border-b border-green-800 text-center">Total Volume</th>
                            <th class="p-4 border-b border-green-800 text-center">Approval Rate</th>
                            <th class="p-4 border-b border-green-800 text-center">Avg. Review Time</th>
                            <th class="p-4 border-b border-green-800 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-[#0f1511] text-gray-300">
                        <?php foreach($document_type_breakdown as $item): ?>
                        <tr class="border-b border-green-800 hover:bg-green-700/10 transition">
                            <td class="p-4 font-medium text-green-200"><?= $item['type'] ?></td>
                            <td class="p-4 text-center font-bold text-blue-400"><?= $item['total'] ?></td>
                            <td class="p-4 text-center">
                                <span class="font-medium <?= $item['approval_rate'] > 75 ? 'text-green-400' : 'text-yellow-400' ?>">
                                    <?= $item['approval_rate'] ?>%
                                </span>
                            </td>
                            <td class="p-4 text-center text-gray-300"><?= $item['avg_review_time'] ?></td>
                            <td class="p-4 text-center">
                                <button class="text-green-400 hover:text-green-200 hover:underline transition text-sm">
                                    View Trend
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-2xl h-full space-y-6">
                
                <h2 class="text-xl font-bold text-green-300 flex items-center gap-2 border-b border-green-800/50 pb-2">
                    <i class="fa-solid fa-chart-pie text-lg"></i> Overall Status Distribution
                </h2>
                
                <div class="h-40 flex items-center justify-center text-gray-500 border border-dashed border-green-800/50 rounded-lg">
                    Pie Chart Placeholder (Approved vs. Pending vs. Rejected)
                </div>
                
                <h3 class="text-md font-semibold text-gray-400 uppercase border-t border-green-800/50 pt-4">Key Document Insights</h3>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-bolt text-blue-400 mt-1"></i>
                        <span>Fastest: **Legal** documents have the fastest average review time (0.8 days).</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-exclamation-triangle text-red-400 mt-1"></i>
                        <span>Bottleneck: **Proposals** have the slowest approval rate (60%).</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-percent text-green-400 mt-1"></i>
                        <span>Total Approval Rate: **<?= $overall_approval_rate ?>%**</span>
                    </li>
                </ul>
                
            </div>
        </div>
    </div> </div>

<?php 
// --- 3. TEMPLATE END ---
include 'app/views/org/layout_end.php';
?>