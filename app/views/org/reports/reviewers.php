<?php 
// NOTE: Helper functions are assumed to be handled by app/views/org/layout_start.php.

// --- 1. Page Specific Variables ---
$title = 'Reviewer Reports - Maestro UI';

// Mock data for the reviewer report table (retained from original file)
$reviewer_data = [
    ['name' => 'Kimberly Nicole De Leon', 'reviewed' => 58, 'approved' => 50, 'rejected' => 8, 'avg_time' => '1.2 days'],
    ['name' => 'Matt Justine Martin', 'reviewed' => 45, 'approved' => 30, 'rejected' => 15, 'avg_time' => '1.8 days'],
    ['name' => 'Aron Luigee Jordan', 'reviewed' => 70, 'approved' => 65, 'rejected' => 5, 'avg_time' => '0.9 days'],
    ['name' => 'Shirin Chisty Deliverio', 'reviewed' => 32, 'approved' => 28, 'rejected' => 4, 'avg_time' => '1.5 days'],
];

// Stats Calculation (retained from original file)
$total_reviews = array_sum(array_column($reviewer_data, 'reviewed'));
$total_approved = array_sum(array_column($reviewer_data, 'approved'));
$approval_rate = $total_reviews > 0 ? round(($total_approved / $total_reviews) * 100) : 0;

// The redundant sidebar logic is removed.

// --- 2. TEMPLATE INCLUSION ---
include 'app/views/org/layout_start.php'; 
include 'app/views/org/sidebar.php'; 
?>

<div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white" x-data="{}">
    
    <h1 class="text-3xl font-bold text-yellow-400 mb-6 tracking-wide">
        Reviewer Reports
    </h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400 uppercase tracking-wider">Total Reviews Done</p>
                <p class="text-3xl font-bold text-green-400 mt-1"><?= $total_reviews ?></p>
            </div>
            <i class="fa-solid fa-clipboard-check text-4xl text-green-700/50"></i>
        </div>

        <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400 uppercase tracking-wider">Avg. Approval Rate</p>
                <p class="text-3xl font-bold text-blue-400 mt-1"><?= $approval_rate ?>%</p>
            </div>
            <i class="fa-solid fa-thumbs-up text-4xl text-blue-700/50"></i>
        </div>

        <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400 uppercase tracking-wider">Fastest Reviewer (Avg)</p>
                <p class="text-lg font-bold text-yellow-400 mt-1">John Smith</p>
                <p class="text-xs text-gray-500">~0.9 Days</p>
            </div>
            <i class="fa-solid fa-gauge-high text-4xl text-yellow-700/50"></i>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-4">
            <h2 class="text-xl font-semibold text-yellow-300 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-table text-lg"></i> Detailed Reviewer Performance
            </h2>
            
            <div class="overflow-x-auto rounded-xl border border-green-800 shadow-2xl shadow-green-900/10">
                <table class="w-full text-left">
                    <thead class="bg-yellow-900/40 text-gray-200 uppercase text-sm tracking-wider">
                        <tr>
                            <th class="p-4 border-b border-green-800">Reviewer</th>
                            <th class="p-4 border-b border-green-800 text-center">Total Reviewed</th>
                            <th class="p-4 border-b border-green-800 text-center">Approved (%)</th>
                            <th class="p-4 border-b border-green-800 text-center">Rejected (%)</th>
                            <th class="p-4 border-b border-green-800 text-center">Avg. Review Time</th>
                            <th class="p-4 border-b border-green-800 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-[#0f1511] text-gray-300">
                        <?php foreach($reviewer_data as $reviewer): 
                            $total = $reviewer['reviewed'];
                            $approved_pct = $total > 0 ? round(($reviewer['approved'] / $total) * 100) : 0;
                            $rejected_pct = 100 - $approved_pct;
                        ?>
                        <tr class="border-b border-green-800 hover:bg-green-700/10 transition">
                            <td class="p-4 font-medium text-green-200 flex items-center gap-2">
                                <i class="fa-solid fa-user-circle text-lg text-blue-400"></i>
                                <?= $reviewer['name'] ?>
                            </td>
                            <td class="p-4 text-center font-bold text-yellow-400"><?= $total ?></td>
                            <td class="p-4 text-center">
                                <span class="font-medium text-green-400"><?= $approved_pct ?>%</span> (<?= $reviewer['approved'] ?>)
                            </td>
                            <td class="p-4 text-center">
                                <span class="font-medium text-red-400"><?= $rejected_pct ?>%</span> (<?= $reviewer['rejected'] ?>)
                            </td>
                            <td class="p-4 text-center text-gray-300"><?= $reviewer['avg_time'] ?></td>
                            <td class="p-4 text-center">
                                <button class="text-yellow-400 hover:text-yellow-200 hover:underline transition text-sm">
                                    View Detail
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
                
                <h2 class="text-xl font-bold text-yellow-300 flex items-center gap-2 border-b border-green-800/50 pb-2">
                    <i class="fa-solid fa-chart-area text-lg"></i> Review Volume Trend
                </h2>
                
                <div class="h-40 flex items-center justify-center text-gray-500 border border-dashed border-green-800/50 rounded-lg">
                    Line Chart Placeholder (Reviews per Month)
                </div>
                
                <h3 class="text-md font-semibold text-gray-400 uppercase border-t border-green-800/50 pt-4">Key Insights</h3>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-bell text-red-400 mt-1"></i>
                        <span>Rejection Rate High: **Justine Martin** has the highest rejection percentage (33%).</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-tachometer-alt text-green-400 mt-1"></i>
                        <span>Efficiency Leader: **John Smith** maintains the fastest average turnaround time.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-chart-simple text-blue-400 mt-1"></i>
                        <span>Volume Leader: **John Smith** has reviewed the most documents (70).</span>
                    </li>
                </ul>
                
            </div>
        </div>
    </div> </div>

<?php 
// --- 3. TEMPLATE END ---
include 'app/views/org/layout_end.php';
?>