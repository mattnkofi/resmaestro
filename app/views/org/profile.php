<?php 
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

$title = 'Profile - Maestro UI';

include 'app/views/org/layout_start.php'; 
include 'app/views/org/sidebar.php'; 
?>

<div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white" x-data="{}">
    
    <h1 class="text-3xl font-bold text-green-400 mb-8 tracking-wide">
        Settings
    </h1>

    <div class="bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-2xl shadow-green-900/10 mb-8 max-w-3xl">
        <div class="flex items-center gap-6">
            <img src="https://placehold.co/96x96/0b0f0c/10b981?text=MJ" 
                 class="w-24 h-24 rounded-full border-2 border-green-700 object-cover" 
                 alt="Profile Picture">
            <div>
                <h2 class="text-2xl font-bold text-green-200">Matt Justine Martin</h2>
                <p class="text-gray-400">Organization Admin</p>
                <button class="mt-3 bg-green-700 px-4 py-2 rounded-xl hover:bg-green-600 font-medium transition">
                    <i class="fa-solid fa-user-edit mr-2"></i> Edit Profile
                </button>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-8">
            
            <form class="bg-green-950/50 p-8 rounded-xl space-y-6 border border-green-800 shadow-2xl shadow-green-900/10">
                
                <h2 class="text-xl font-semibold text-green-300 mb-4 border-b border-green-800/50 pb-2 flex items-center gap-3">
                    <i class="fa-solid fa-gear text-lg"></i> General Organization Settings
                </h2>
                
                <div>
                    <label for="org_name" class="block text-sm font-medium mb-2 text-gray-300">Organization Name</label>
                    <input type="text" id="org_name" value="Maestro Organization"
                           class="w-full p-3 rounded-lg bg-green-900 border border-green-800 focus:ring-green-500 focus:border-green-500 text-green-100" required>
                </div>

                <div>
                    <label for="base_url" class="block text-sm font-medium mb-2 text-gray-300">System Domain / Base URL</label>
                    <input type="text" id="base_url" value="https://maestro-docs.com"
                           class="w-full p-3 rounded-lg bg-green-900 border border-green-800 focus:ring-green-500 focus:border-green-500 text-green-100" readonly>
                    <p class="mt-1 text-xs text-gray-500">Contact IT support to change the core domain.</p>
                </div>

                <div>
                    <label for="language" class="block text-sm font-medium mb-2 text-gray-300">Default Language</label>
                    <select id="language" class="w-full p-3 rounded-lg bg-green-900 border border-green-800 focus:ring-green-500 focus:border-green-500 text-green-100">
                        <option>English (US)</option>
                        <option>Spanish</option>
                        <option>French</option>
                    </select>
                </div>

                <div class="pt-2">
                    <button type="submit" class="bg-green-700 px-6 py-3 rounded-xl hover:bg-green-600 font-bold text-lg transition shadow-lg shadow-green-900/40">
                        <i class="fa-solid fa-save mr-2"></i> Save General Settings
                    </button>
                </div>
            </form>

            <form class="bg-green-950/50 p-8 rounded-xl space-y-6 border border-green-800 shadow-2xl shadow-green-900/10">
                
                <h2 class="text-xl font-semibold text-green-300 mb-4 border-b border-green-800/50 pb-2 flex items-center gap-3">
                    <i class="fa-solid fa-bell text-lg"></i> Notification Preferences
                </h2>
                
                <div>
                    <label for="email_notifications" class="block text-sm font-medium mb-2 text-gray-300">Global Email Notifications</label>
                    <select id="email_notifications" class="w-full p-3 rounded-lg bg-green-900 border border-green-800 focus:ring-green-500 focus:border-green-500 text-green-100">
                        <option>Enabled (All Alerts)</option>
                        <option>Disabled (Critical Only)</option>
                        <option>Off</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Controls system-wide notification emails (e.g., overdue reviews).</p>
                </div>

                <div class="flex justify-between items-center bg-green-900/30 p-3 rounded-lg border border-green-800">
                    <label class="text-sm font-medium text-gray-300">Browser Push Notifications</label>
                    <div x-data="{ enabled: true }" class="flex items-center">
                        <button @click="enabled = !enabled" :class="enabled ? 'bg-green-600' : 'bg-gray-600'"
                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <span :class="enabled ? 'translate-x-5' : 'translate-x-0'"
                                  class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="bg-green-700 px-6 py-3 rounded-xl hover:bg-green-600 font-bold text-lg transition shadow-lg shadow-green-900/40">
                        <i class="fa-solid fa-save mr-2"></i> Update Notification Settings
                    </button>
                </div>
            </form>
        </div>

        <div class="lg:col-span-1 space-y-8">
            
            <div class="bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-2xl shadow-red-900/10">
                <h2 class="text-xl font-semibold text-red-400 mb-4 flex items-center gap-3 border-b border-green-800/50 pb-2">
                    <i class="fa-solid fa-lock text-lg"></i> Security & Policy
                </h2>
                
                <ul class="space-y-3 text-sm">
                    <li class="flex justify-between items-center pb-2 border-b border-green-900/30">
                        <span class="text-gray-300">Password Length</span>
                        <span class="font-medium text-yellow-400">Min. 12 Characters</span>
                    </li>
                    
                    <li class="flex justify-between items-center pb-2 border-b border-green-900/30">
                        <span class="text-gray-300">Two-Factor Authentication (2FA)</span>
                        <span class="font-bold text-green-400 flex items-center gap-2">
                            Mandatory <i class="fa-solid fa-check-circle text-sm"></i>
                        </span>
                    </li>
                    
                    <li class="flex justify-between items-center">
                        <span class="text-gray-300">Session Timeout</span>
                        <span class="font-medium text-blue-400">30 Minutes (Inactivity)</span>
                    </li>
                </ul>
                <button class="w-full bg-red-700/40 text-red-300 p-3 rounded-xl hover:bg-red-700/60 transition mt-6">
                    Manage Security Policies
                </button>
            </div>
            
            <div class="bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-2xl shadow-green-900/10">
                <h2 class="text-xl font-semibold text-blue-400 mb-4 flex items-center gap-3 border-b border-green-800/50 pb-2">
                    <i class="fa-solid fa-undo text-lg"></i> Advanced Actions
                </h2>
                
                <button class="w-full bg-blue-700/40 text-blue-300 p-3 rounded-xl hover:bg-blue-700/60 transition mb-3">
                    <i class="fa-solid fa-history mr-2"></i> System Activity Log
                </button>
                
                <button class="w-full bg-gray-700/40 text-gray-300 p-3 rounded-xl hover:bg-gray-700/60 transition">
                    <i class="fa-solid fa-file-export mr-2"></i> Export All Data
                </button>
            </div>
            
        </div>

    </div> </div> <?php 
// --- 3. TEMPLATE END ---
include 'app/views/org/layout_end.php';
?>