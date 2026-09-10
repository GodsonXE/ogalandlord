<?php
// Ensure backend variables are defined with safe fallbacks
$managedLeases = $managedLeases ?? [];
$estateUnits = $estateUnits ?? [];
$assignedProperties = $assignedProperties ?? [];
$tickets = $tickets ?? [];
$property = $property ?? ($assignedProperties[0] ?? [
    'title' => 'PHDL Unity Estate, Idu',
    'address_line_1' => 'Plot 42 Railway Corridor, Idu Industrial',
    'city' => 'Abuja',
    'state' => 'FCT'
]);

$signedCount = count(array_filter($managedLeases, fn($l) => ($l['agreement_status'] ?? '') === 'FULLY_EXECUTED'));
$pendingCount = count(array_filter($managedLeases, fn($l) => ($l['agreement_status'] ?? '') !== 'FULLY_EXECUTED'));
$totalTenants = count($managedLeases);

$artisans = $artisans ?? [];
$announcements = $announcements ?? [];
$facilityBookings = $facilityBookings ?? [];
$propertyExpenses = $propertyExpenses ?? [];
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caretaker Hub — Oga Landlord</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="h-full text-slate-800 antialiased" x-data="caretakerApp()">

<div class="min-h-full flex flex-col md:flex-row">

    <!-- Mobile Header -->
    <div class="md:hidden bg-[#1D4ED8] text-white px-4 py-3 flex items-center justify-between sticky top-0 z-30 shadow-md">
        <div class="flex items-center gap-2">
            <img src="/assets/images/logo.jpg" alt="Oga Landlord Logo" class="h-8 w-auto object-contain rounded bg-white p-0.5">
            <span class="font-extrabold text-white text-sm tracking-tight">Oga<span class="text-blue-200">Landlord</span></span>
            <span class="text-[10px] bg-blue-800 text-white font-bold px-2 py-0.5 rounded-md">Caretaker</span>
        </div>
        <button type="button" @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-white hover:bg-white/10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <!-- Collapsible Blue Accent Left Sidebar (#1D4ED8) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
           class="fixed md:sticky top-0 z-40 h-screen w-72 bg-[#1D4ED8] text-white flex flex-col justify-between transition-transform duration-200 ease-in-out shadow-xl">
        
        <div class="flex flex-col flex-1 overflow-y-auto">
            <!-- Header & Brand -->
            <div class="p-5 border-b border-blue-600/70">
                <a href="/" class="flex items-center gap-3 group">
                    <img src="/assets/images/logo.jpg" alt="Oga Landlord Logo" class="h-11 w-auto object-contain rounded-lg bg-white p-1 shadow-sm transition-transform group-hover:scale-105">
                    <div>
                        <span class="text-lg font-extrabold tracking-tight text-white block leading-tight">Oga<span class="text-blue-200">Landlord</span></span>
                        <span class="text-[10px] uppercase tracking-wider text-blue-200 font-bold">Caretaker Operations</span>
                    </div>
                </a>

                <!-- Assigned Estate & Nationwide Selector -->
                <div class="mt-5 p-3 rounded-xl bg-blue-800/80 border border-blue-400/40 shadow-inner">
                    <div class="flex items-center justify-between text-[10px] font-bold text-blue-200 uppercase tracking-wider mb-1.5">
                        <span>Nationwide Estates</span>
                        <span class="bg-blue-600 text-white px-1.5 py-0.2 rounded text-[9px]"><?= count($assignedProperties) ?> Estates</span>
                    </div>
                    <select onchange="window.location.href = '/caretaker' + (this.value > 0 ? '?property_id=' + this.value : '')"
                            class="w-full bg-blue-900/90 border border-blue-400/50 rounded-lg text-xs font-bold text-white py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-white">
                        <option value="0" <?= empty($_GET['property_id']) ? 'selected' : '' ?>>All Locations (<?= count($assignedProperties) ?> Estates)</option>
                        <?php foreach ($assignedProperties as $ap): ?>
                            <option value="<?= (int)$ap['id'] ?>" <?= (!empty($_GET['property_id']) && (int)$_GET['property_id'] === (int)$ap['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ap['title']) ?> (<?= htmlspecialchars($ap['city']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="block text-[11px] text-emerald-300 font-medium mt-1.5">✓ <?= $totalTenants ?> Managed Tenants</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5">
                <span class="block px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-blue-200">Operations Hub</span>
                
                <!-- 1. Tenancy Agreements & Tenants List (Opens Tenants Directory First) -->
                <button type="button" @click="currentTab = 'agreements'; sidebarOpen = false"
                        :class="currentTab === 'agreements' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Tenancy Agreements</span>
                    </div>
                    <span :class="currentTab === 'agreements' ? 'bg-blue-100 text-[#1D4ED8]' : 'bg-blue-700/80 text-white'" class="px-2 py-0.5 rounded-full text-[10px] font-bold"><?= $totalTenants ?></span>
                </button>

                <!-- 2. Maintenance Tickets -->
                <button type="button" @click="currentTab = 'tickets'; sidebarOpen = false"
                        :class="currentTab === 'tickets' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Maintenance Tasks</span>
                    </div>
                    <span :class="currentTab === 'tickets' ? 'bg-blue-100 text-[#1D4ED8]' : 'bg-blue-700/80 text-white'" class="px-2 py-0.5 rounded-full text-[10px] font-bold"><?= count($tickets) ?></span>
                </button>

                <!-- 3. Estate Units -->
                <button type="button" @click="currentTab = 'estate'; sidebarOpen = false"
                        :class="currentTab === 'estate' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Estate Units</span>
                    </div>
                    <span :class="currentTab === 'estate' ? 'bg-blue-100 text-[#1D4ED8]' : 'bg-blue-700/80 text-white'" class="px-2 py-0.5 rounded-full text-[10px] font-bold"><?= count($estateUnits) ?: 8 ?></span>
                </button>

                <!-- 4. Auto-CC Notices -->
                <button type="button" @click="currentTab = 'notices'; sidebarOpen = false"
                        :class="currentTab === 'notices' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Auto-CC Notices</span>
                </button>

                <!-- 5. Resident Payments & Stamping Queue (Requirement 7) -->
                <button type="button" @click="currentTab = 'payments'; sidebarOpen = false"
                        :class="currentTab === 'payments' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>Resident Payments</span>
                    </div>
                    <span :class="currentTab === 'payments' ? 'bg-blue-100 text-[#1D4ED8]' : 'bg-blue-700/80 text-white'" class="px-2 py-0.5 rounded-full text-[10px] font-bold"><?= count($caretakerPayments ?? []) ?></span>
                </button>

                <!-- 6. Estate Notice Board & Broadcasts -->
                <button type="button" @click="currentTab = 'notices_board'; sidebarOpen = false"
                        :class="currentTab === 'notices_board' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <span class="text-sm">📢</span>
                        <span>Estate Notice Board</span>
                    </div>
                    <span :class="currentTab === 'notices_board' ? 'bg-blue-100 text-[#1D4ED8]' : 'bg-blue-700/80 text-white'" class="px-2 py-0.5 rounded-full text-[10px] font-bold"><?= count($announcements) ?></span>
                </button>

                <!-- 7. Shared Facility Bookings -->
                <button type="button" @click="currentTab = 'facilities'; sidebarOpen = false"
                        :class="currentTab === 'facilities' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <span class="text-sm">🏊</span>
                        <span>Facility Bookings</span>
                    </div>
                    <span :class="currentTab === 'facilities' ? 'bg-blue-100 text-[#1D4ED8]' : 'bg-blue-700/80 text-white'" class="px-2 py-0.5 rounded-full text-[10px] font-bold"><?= count($facilityBookings) ?></span>
                </button>
            </nav>
        </div>

        <!-- Sidebar Footer & Role Switcher -->
        <div class="p-4 border-t border-blue-700/80 bg-blue-900/60 space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-white text-[#1D4ED8] font-black flex items-center justify-center text-xs shadow-sm">
                    MD
                </div>
                <div class="flex-1 min-w-0">
                    <span class="block text-xs font-bold text-white truncate">Musa Danjuma</span>
                    <span class="block text-[10px] text-blue-200 truncate">caretaker.idu@ogalandlord.ng</span>
                </div>
            </div>

            <div class="pt-2 border-t border-blue-800/80 flex flex-col gap-1.5 text-[11px]">
                <span class="text-[10px] uppercase tracking-wider font-bold text-blue-300">Switch Workspace</span>
                <a href="/demo/landlord" class="flex items-center justify-between text-blue-100 hover:text-white py-1 font-medium transition">
                    <span>👔 Landlord Dashboard</span>
                    <span>→</span>
                </a>
                <a href="/demo/superadmin" class="flex items-center justify-between text-blue-100 hover:text-white py-1 font-medium transition">
                    <span>⚡ SuperAdmin Command</span>
                    <span>→</span>
                </a>
                <a href="/logout" class="flex items-center justify-between text-rose-300 hover:text-rose-100 py-1 font-semibold transition mt-0.5">
                    <span>🚪 Sign Out</span>
                    <span>✕</span>
                </a>
            </div>
        </div>

    </aside>

    <!-- Content Area -->
    <main class="flex-1 min-w-0 overflow-y-auto p-4 sm:p-6 lg:p-8">
        
        <!-- Top Workspace Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200 mb-8">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Property Operations</span>
                    <span class="text-slate-300">•</span>
                    <span class="text-xs font-bold text-[#1D4ED8]">Caretaker Hub</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1"><?= htmlspecialchars($property['title'] ?? 'PHDL Unity Estate, Idu') ?></h1>
                <p class="text-xs text-slate-500 mt-0.5"><?= htmlspecialchars($property['address_line_1'] ?? 'Plot 42 Railway Corridor, Idu Industrial') ?>, <?= htmlspecialchars($property['city'] ?? 'Abuja') ?> (<?= htmlspecialchars($property['state'] ?? 'FCT') ?>)</p>
            </div>
            
            <div class="flex items-center gap-2">
                <button type="button" @click="currentTab = 'agreements'"
                        class="bg-white border border-slate-300 hover:border-[#1D4ED8] text-slate-800 hover:text-[#1D4ED8] text-xs font-bold px-3.5 py-2 rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-[#1D4ED8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Tenancy Agreements (<?= $totalTenants ?>)</span>
                </button>
                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Caretaker Active</span>
                </span>
            </div>
        </div>

        <?php
        $selectedPropertyId = !empty($_GET['property_id']) ? (int)$_GET['property_id'] : 0;
        $currentSelectedProp = null;
        if ($selectedPropertyId > 0) {
            foreach ($assignedProperties as $ap) {
                if ((int)$ap['id'] === $selectedPropertyId) {
                    $currentSelectedProp = $ap;
                    break;
                }
            }
        }
        ?>

        <!-- Nationwide Estate & Location Filter Bar -->
        <div class="mb-6 p-4 bg-white border border-slate-200/90 border-t-4 border-t-[#1D4ED8] rounded-2xl shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#1D4ED8] flex items-center justify-center font-bold text-base shrink-0">
                    📍
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-800">Nationwide Estate Filter</span>
                        <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-blue-100 text-[#1D4ED8]">
                            <?= count($assignedProperties) ?> Estates
                        </span>
                    </div>
                    <span class="text-[11px] text-slate-500 block">Filter operations and tenants by assigned property</span>
                </div>
            </div>

            <!-- Dropdown Menu to Decongest the Area -->
            <div class="relative w-full md:w-auto" x-data="{ open: false, query: '' }" @click.away="open = false" @keydown.escape="open = false">
                <!-- Dropdown Trigger Button -->
                <button type="button"
                        @click="open = !open"
                        class="w-full md:w-auto md:min-w-[340px] flex items-center justify-between gap-3 px-4 py-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 transition shadow-xs focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/30">
                    <div class="flex items-center gap-2 truncate text-left">
                        <span class="w-2 h-2 rounded-full <?= $currentSelectedProp ? 'bg-emerald-500' : 'bg-blue-600' ?> shrink-0"></span>
                        <span class="truncate">
                            <?php if ($currentSelectedProp): ?>
                                📍 <?= htmlspecialchars($currentSelectedProp['title']) ?> <span class="text-slate-500 text-[11px] font-normal">(<?= htmlspecialchars($currentSelectedProp['city']) ?>)</span>
                            <?php else: ?>
                                🌐 All Locations (<?= count($assignedProperties) ?> Estates)
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0 text-slate-400">
                        <span class="text-[10px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-slate-200 text-slate-700">
                            <?= $currentSelectedProp ? 'Active' : 'All' ?>
                        </span>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </button>

                <!-- Dropdown Menu Panel -->
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     x-cloak
                     class="absolute z-50 left-0 md:left-auto md:right-0 mt-2 w-full md:w-96 bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden text-xs">
                    
                    <!-- Search / Quick Filter Input inside Dropdown -->
                    <div class="p-2.5 bg-slate-50 border-b border-slate-100">
                        <div class="relative">
                            <input type="text"
                                   x-model="query"
                                   placeholder="Search <?= count($assignedProperties) ?> estates or cities..."
                                   class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-[#1D4ED8] focus:ring-1 focus:ring-[#1D4ED8]"
                                   @click.stop>
                            <svg class="w-3.5 h-3.5 absolute left-2.5 top-2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Scrollable List of Estates -->
                    <div class="max-h-72 overflow-y-auto divide-y divide-slate-50 py-1">
                        <!-- 'All Locations' Option -->
                        <a href="/caretaker"
                           x-show="!query || 'all locations'.includes(query.toLowerCase())"
                           class="flex items-center justify-between px-3.5 py-2.5 hover:bg-blue-50/70 transition <?= empty($_GET['property_id']) ? 'bg-blue-50 font-bold text-[#1D4ED8]' : 'text-slate-700' ?>">
                            <div class="flex items-center gap-2">
                                <span class="text-sm">🌐</span>
                                <div>
                                    <div class="font-bold">All Locations (<?= count($assignedProperties) ?> Estates)</div>
                                    <div class="text-[10px] text-slate-400">View aggregate tickets and leases across all estates</div>
                                </div>
                            </div>
                            <?php if (empty($_GET['property_id'])): ?>
                                <span class="text-[#1D4ED8] font-bold text-sm">✓</span>
                            <?php endif; ?>
                        </a>

                        <!-- Individual Estates -->
                        <?php foreach ($assignedProperties as $ap): 
                            $isSelected = (!empty($_GET['property_id']) && (int)$_GET['property_id'] === (int)$ap['id']);
                            $searchKey = strtolower($ap['title'] . ' ' . $ap['city'] . ' ' . ($ap['state'] ?? ''));
                        ?>
                            <a href="/caretaker?property_id=<?= (int)$ap['id'] ?>"
                               x-show="!query || '<?= addslashes($searchKey) ?>'.includes(query.toLowerCase())"
                               class="flex items-center justify-between px-3.5 py-2 hover:bg-slate-50 transition <?= $isSelected ? 'bg-blue-50/80 font-bold text-[#1D4ED8]' : 'text-slate-700' ?>">
                                <div class="flex items-center gap-2 min-w-0 pr-2">
                                    <span class="text-xs shrink-0">📍</span>
                                    <div class="min-w-0">
                                        <div class="truncate font-semibold text-slate-800 <?= $isSelected ? 'text-[#1D4ED8] font-bold' : '' ?>">
                                            <?= htmlspecialchars($ap['title']) ?>
                                        </div>
                                        <div class="text-[10px] text-slate-400 truncate">
                                            <?= htmlspecialchars($ap['city']) ?><?= !empty($ap['state']) ? ' (' . htmlspecialchars($ap['state']) . ')' : '' ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($isSelected): ?>
                                    <span class="text-[#1D4ED8] font-bold text-sm shrink-0">✓</span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Dropdown Footer -->
                    <div class="px-3 py-2 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400">
                        <span>Total: <strong><?= count($assignedProperties) ?> Estates</strong></span>
                        <a href="/caretaker" class="text-[#1D4ED8] font-bold hover:underline">Reset to All</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= TAB 1: TENANCY AGREEMENTS & TENANTS DIRECTORY ================= -->
        <div x-show="currentTab === 'agreements'" x-transition class="space-y-6">
            
            <!-- Context Header & Legal Note -->
            <div class="bg-blue-50/70 border border-blue-200/80 rounded-2xl p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-extrabold uppercase text-[#1D4ED8] tracking-wider">Tenant Directory & Legal Documents</span>
                        <span class="text-[10px] bg-blue-100 text-[#1D4ED8] font-bold px-2 py-0.5 rounded-md">RBAC Enforced</span>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 mt-1">Managed Tenants & Agreement Execution Status</h2>
                    <p class="text-xs text-slate-600 mt-1 max-w-3xl leading-relaxed">
                        Select any tenant below to preview their signed legal tenancy agreement or inspect their pending draft agreement before move-in. 
                        <strong>Important:</strong> Under the Evidence Act 2011, Caretakers and Admins cannot access the resident signature portal or sign for tenants.
                    </p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs font-bold text-slate-700 bg-white border border-slate-200 px-3 py-2 rounded-xl">
                        🔒 Portal Access: <span class="text-rose-600 font-extrabold">Tenants Only</span>
                    </span>
                </div>
            </div>

            <!-- Summary KPI Cards with Color-Coded Hierarchy -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- 1. Assigned Tenants (Primary Blue #1D4ED8) -->
                <div class="bg-white border border-slate-200/90 border-t-4 border-t-[#1D4ED8] rounded-2xl p-5 shadow-xs transition hover:shadow-md">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Assigned Tenants</span>
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#1D4ED8] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-3xl font-black text-slate-900 mt-1"><?= $totalTenants ?></p>
                    <div class="mt-2.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-[#1D4ED8] border border-blue-200">
                            Active Estate Directory
                        </span>
                    </div>
                </div>

                <!-- 2. Signed Agreements (Emerald Green) -->
                <div class="bg-white border border-slate-200/90 border-t-4 border-t-emerald-500 rounded-2xl p-5 shadow-xs transition hover:shadow-md">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Signed & Stamped</span>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-3xl font-black text-emerald-600 mt-1"><?= $signedCount ?></p>
                    <div class="mt-2.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            ✓ Ready for Key Handover
                        </span>
                    </div>
                </div>

                <!-- 3. Awaiting Signature (Warm Amber) -->
                <div class="bg-white border border-slate-200/90 border-t-4 border-t-amber-500 rounded-2xl p-5 shadow-xs transition hover:shadow-md">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Unsigned Drafts</span>
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-3xl font-black text-amber-600 mt-1"><?= $pendingCount ?></p>
                    <div class="mt-2.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            ⏳ Keys Withheld
                        </span>
                    </div>
                </div>

                <!-- 4. Total Estate Units (Indigo Purple) -->
                <div class="bg-white border border-slate-200/90 border-t-4 border-t-indigo-600 rounded-2xl p-5 shadow-xs transition hover:shadow-md">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Estate Units</span>
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                    </div>
                    <p class="text-3xl font-black text-slate-900 mt-1"><?= count($estateUnits) ?: 8 ?></p>
                    <div class="mt-2.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            <?= count($estateUnits) - $totalTenants ?> Available Vacant
                        </span>
                    </div>
                </div>

            </div>

            <!-- Interactive Filters & Search Controls -->
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <!-- Status Filter Tabs -->
                <div class="flex items-center gap-2 overflow-x-auto">
                    <button type="button" @click="statusFilter = 'ALL'"
                            :class="statusFilter === 'ALL' ? 'bg-[#1D4ED8] text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition">
                        All Tenants (<?= $totalTenants ?>)
                    </button>
                    <button type="button" @click="statusFilter = 'SIGNED'"
                            :class="statusFilter === 'SIGNED' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-300"></span>
                        <span>Signed (<?= $signedCount ?>)</span>
                    </button>
                    <button type="button" @click="statusFilter = 'PENDING'"
                            :class="statusFilter === 'PENDING' ? 'bg-amber-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                            class="px-3.5 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-300"></span>
                        <span>Not Signed (<?= $pendingCount ?>)</span>
                    </button>
                </div>

                <!-- Live Search Box -->
                <div class="relative w-full sm:w-72">
                    <input type="text" x-model="searchQuery" placeholder="Search tenant name or unit..."
                           class="w-full text-xs pl-9 pr-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1D4ED8] bg-slate-50/50">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Tenants List Table with Blue Accent Header Stripe -->
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-[#1D4ED8] rounded-2xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/90 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="py-3.5 px-4">Resident / Tenant</th>
                                <th class="py-3.5 px-4">Unit Assigned</th>
                                <th class="py-3.5 px-4">Agreed Rent</th>
                                <th class="py-3.5 px-4">Agreement Status</th>
                                <th class="py-3.5 px-4">Audit Certificate</th>
                                <th class="py-3.5 px-4 text-right">Caretaker Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($managedLeases as $l): 
                                $isSigned = ($l['agreement_status'] ?? '') === 'FULLY_EXECUTED';
                                $filterType = $isSigned ? 'SIGNED' : 'PENDING';
                                $tenantName = $l['tenant_name'] ?? 'Tenant';
                                $unitNum = $l['unit_number'] ?? 'Unit';
                                $initials = strtoupper(substr($tenantName, 0, 1) . (strpos($tenantName, ' ') !== false ? substr($tenantName, strpos($tenantName, ' ') + 1, 1) : ''));
                            ?>
                                <tr x-show="matchesFilter('<?= $filterType ?>', '<?= strtolower(addslashes($tenantName . ' ' . $unitNum)) ?>')"
                                    class="hover:bg-slate-50/60 transition <?= $isSigned ? 'border-l-4 border-l-emerald-500 bg-emerald-50/10' : 'border-l-4 border-l-amber-400 bg-amber-50/10' ?>">
                                    
                                    <!-- Tenant Profile -->
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full <?= $isSigned ? 'bg-blue-100 text-[#1D4ED8]' : 'bg-amber-100 text-amber-800' ?> font-bold flex items-center justify-center text-xs shrink-0 shadow-xs">
                                                <?= $initials ?>
                                            </div>
                                            <div>
                                                <span class="font-bold text-slate-900 block text-sm"><?= htmlspecialchars($tenantName) ?></span>
                                                <span class="text-slate-500 text-[11px] block"><?= htmlspecialchars($l['tenant_email'] ?? '') ?></span>
                                                <span class="text-slate-400 text-[10px] block">Tel: <?= htmlspecialchars($l['tenant_phone'] ?? '+234...') ?></span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Unit & Property -->
                                    <td class="py-4 px-4">
                                        <span class="font-extrabold text-slate-900 text-xs block"><?= htmlspecialchars($unitNum) ?></span>
                                        <span class="text-slate-500 text-[11px] block"><?= htmlspecialchars($l['apartment_type'] ?? 'Apartment') ?></span>
                                        <span class="text-slate-400 text-[10px] block"><?= htmlspecialchars($l['property_title'] ?? 'PHDL Unity Estate') ?></span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-50 text-[#1D4ED8] border border-blue-200 mt-0.5">
                                            📍 <?= htmlspecialchars($l['city'] ?? 'Abuja') ?>, <?= htmlspecialchars($l['state'] ?? 'FCT') ?>
                                        </span>
                                    </td>

                                    <!-- Agreed Rent -->
                                    <td class="py-4 px-4">
                                        <span class="font-black text-slate-900 text-xs block">NGN <?= number_format((float)($l['rent_amount'] ?? 0), 2) ?></span>
                                        <span class="text-slate-400 text-[10px] block">Annual Rent</span>
                                        <span class="text-slate-500 text-[10px] block mt-0.5">Due: <?= date('M j, Y', strtotime($l['rent_due_date'] ?? 'now')) ?></span>
                                    </td>

                                    <!-- Agreement Status (Signed vs Not Signed) -->
                                    <td class="py-4 px-4">
                                        <?php if ($isSigned): ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                <span>Signed & Verified</span>
                                            </span>
                                            <span class="block text-[10px] text-emerald-700 font-semibold mt-1">
                                                Signed: <?= !empty($l['tenant_signed_at']) ? date('M j, Y', strtotime($l['tenant_signed_at'])) : 'Executed' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>Not Signed (Pending)</span>
                                            </span>
                                            <span class="block text-[10px] text-amber-700 font-semibold mt-1">
                                                Awaiting Tenant Signature
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Cryptographic Audit Hash -->
                                    <td class="py-4 px-4">
                                        <?php if ($isSigned && !empty($l['document_sha256_hash'])): ?>
                                            <span class="font-mono text-[10px] text-slate-600 bg-slate-100 px-2 py-1 rounded border border-slate-200 block truncate max-w-[150px]" title="<?= htmlspecialchars($l['document_sha256_hash']) ?>">
                                                SHA: <?= substr($l['document_sha256_hash'], 0, 12) ?>...
                                            </span>
                                            <span class="text-emerald-600 text-[10px] font-semibold block mt-0.5">✓ Certified Authentic</span>
                                        <?php else: ?>
                                            <span class="text-slate-400 text-[11px] italic">Unsealed — Awaiting Tenant</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Action (Preview) -->
                                    <td class="py-4 px-4 text-right">
                                        <div class="flex flex-col items-end gap-1">
                                            <?php if ($isSigned): ?>
                                                <a href="/agreement/preview?id=<?= (int)$l['id'] ?>"
                                                   class="bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-bold text-xs px-3.5 py-1.5 rounded-xl shadow-xs transition inline-flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    <span>Preview Agreement</span>
                                                </a>
                                                <span class="text-[10px] text-emerald-600 font-medium">Ready for key handover</span>
                                            <?php else: ?>
                                                <a href="/agreement/preview?id=<?= (int)$l['id'] ?>"
                                                   class="bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 font-bold text-xs px-3.5 py-1.5 rounded-xl shadow-xs transition inline-flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    <span>Preview Draft Terms</span>
                                                </a>
                                                <span class="text-[10px] text-amber-700 font-medium">Caretakers cannot sign</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ================= TAB 2: MAINTENANCE TASKS ================= -->
        <div x-show="currentTab === 'tickets'" x-transition class="space-y-4">
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-rose-500 rounded-2xl p-5 shadow-xs flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                        🛠️
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Ground Maintenance & Caretaker Issue Queue</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Chat directly with tenants, resolve issues, or escalate capital repairs to Landlord.</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-[#1D4ED8] bg-blue-50 border border-blue-200 px-3 py-1.5 rounded-xl">
                    <?= count($tickets) ?> Active Requests
                </span>
            </div>

            <?php if (empty($tickets)): ?>
                <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center text-xs text-slate-500 shadow-xs">
                    <span class="text-3xl block mb-2">🎉</span>
                    <strong class="text-slate-800 text-sm block">No active maintenance complaints</strong>
                    <p class="mt-1">All tenant concerns in your assigned estate are currently resolved.</p>
                </div>
            <?php else: ?>
                <?php foreach ($tickets as $t): 
                    $isEscalated = ((int)($t['is_escalated_to_landlord'] ?? 0) === 1);
                    $isOpen = in_array(strtoupper($t['status'] ?? 'OPEN'), ['OPEN', 'IN_REVIEW', 'IN_PROGRESS', 'ESCALATED_TO_LANDLORD'], true);
                    $isHigh = in_array(strtoupper($t['priority'] ?? ''), ['HIGH', 'EMERGENCY'], true);
                ?>
                    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 hover:border-slate-300 transition shadow-xs">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                    <span class="font-mono text-xs font-black text-[#1D4ED8] bg-blue-50 border border-blue-200 px-2 py-0.5 rounded">
                                        <?= htmlspecialchars($t['ticket_code'] ?? 'TKT') ?>
                                    </span>
                                    <span class="font-bold text-slate-900 text-xs"><?= htmlspecialchars($t['unit_number'] ?? 'Unit') ?></span>
                                    <span class="text-slate-300">•</span>
                                    <span class="text-slate-600 font-medium text-xs"><?= htmlspecialchars($t['tenant_name'] ?? 'Tenant') ?> (Tel: <?= htmlspecialchars($t['tenant_phone'] ?? '+234...') ?>)</span>
                                    <span class="text-[11px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded"><?= htmlspecialchars($t['category'] ?? 'General') ?></span>
                                    <span class="text-slate-300">•</span>
                                    <span class="text-[10px] font-bold text-blue-800 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded">📍 <?= htmlspecialchars($t['property_city'] ?? 'Abuja') ?>, <?= htmlspecialchars($t['property_state'] ?? 'FCT') ?></span>
                                </div>
                                <h3 class="text-base font-bold text-slate-900"><?= htmlspecialchars($t['title']) ?></h3>
                                <p class="text-xs sm:text-sm text-slate-600 mt-1 leading-relaxed"><?= htmlspecialchars($t['description']) ?></p>

                                <?php if (!empty($t['photo_url'])): ?>
                                    <div class="mt-2.5 flex items-center gap-2.5 p-2 bg-slate-100/90 rounded-xl border border-slate-200 w-fit">
                                        <img src="<?= htmlspecialchars($t['photo_url']) ?>" alt="Photo evidence" class="w-10 h-10 rounded-lg object-cover border border-slate-300">
                                        <div class="text-[11px]">
                                            <span class="font-bold text-slate-800 block">Attached Photo Evidence</span>
                                            <a href="<?= htmlspecialchars($t['photo_url']) ?>" target="_blank" class="text-[10px] text-[#1D4ED8] hover:underline font-bold">Inspect Image ↗</a>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Artisan Dispatch Status -->
                                <div class="mt-2.5 flex items-center gap-2 text-xs flex-wrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 font-medium">
                                        <span>👷 Artisan:</span>
                                        <strong class="text-slate-900"><?= !empty($t['artisan_name']) ? htmlspecialchars($t['artisan_name']) . ' (' . htmlspecialchars($t['artisan_trade'] ?? 'Artisan') . ')' : 'Unassigned' ?></strong>
                                    </span>
                                    <?php if (!empty($t['work_status'])): ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $t['work_status'] === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-[#1D4ED8]' ?>">
                                            Work: <?= htmlspecialchars($t['work_status']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($t['estimated_cost']) && (float)$t['estimated_cost'] > 0): ?>
                                        <span class="text-[10px] font-semibold text-slate-500">
                                            Est: ₦<?= number_format((float)$t['estimated_cost'], 2) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($isEscalated): ?>
                                    <div class="mt-2.5 p-2.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 flex items-start gap-2">
                                        <span class="font-bold text-amber-700 shrink-0">⚡ Escalated to Landlord:</span>
                                        <span><?= htmlspecialchars($t['escalation_reason'] ?? 'Owner review requested') ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-2 text-[11px] text-blue-700 font-medium flex items-center gap-1.5">
                                        <span>🔒 Direct Caretaker Channel (Landlord cannot view until escalated)</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="flex flex-col sm:items-end gap-2 shrink-0">
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?= $isHigh ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($isOpen ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200') ?>">
                                    <?= htmlspecialchars($t['status'] ?? 'OPEN') ?>
                                </span>
                                <span class="text-[10px] text-slate-400"><?= date('M j, Y, H:i', strtotime($t['created_at'] ?? 'now')) ?></span>
                            </div>
                        </div>

                        <div class="mt-4 pt-3.5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-2">
                            <span class="text-slate-500 font-medium">
                                💬 <?= (int)($t['message_count'] ?? 1) ?> messages in thread
                            </span>
                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" @click="openArtisanDispatchModal(<?= (int)$t['id'] ?>, '<?= addslashes($t['ticket_code'] ?? '') ?>', '<?= addslashes($t['title']) ?>', <?= (int)($t['artisan_id'] ?? 0) ?>, '<?= addslashes($t['work_status'] ?? 'ASSIGNED') ?>', <?= (float)($t['estimated_cost'] ?? 0) ?>, <?= (float)($t['actual_cost'] ?? 0) ?>)"
                                        class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold px-3 py-1.5 rounded-xl border border-indigo-200 transition inline-flex items-center gap-1">
                                    <span>🔧 Dispatch / Update Artisan</span>
                                </button>
                                <button type="button" @click="openChatThread(<?= (int)$t['id'] ?>)" class="bg-blue-50 hover:bg-blue-100 text-[#1D4ED8] font-bold px-3 py-1.5 rounded-xl border border-blue-200 transition inline-flex items-center gap-1">
                                    <span>💬 Open Chat</span>
                                </button>
                                <?php if (!$isEscalated): ?>
                                    <button type="button" @click="openEscalateModal(<?= (int)$t['id'] ?>, '<?= addslashes($t['ticket_code'] ?? '') ?>', '<?= addslashes($t['title']) ?>')" class="bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold px-3 py-1.5 rounded-xl border border-amber-300 transition inline-flex items-center gap-1">
                                        <span>⚡ Tag / Escalate to Landlord</span>
                                    </button>
                                <?php endif; ?>
                                <?php if ($isOpen): ?>
                                    <button type="button" @click="markResolved(<?= (int)$t['id'] ?>)" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold px-3 py-1.5 rounded-xl border border-emerald-200 transition inline-flex items-center gap-1">
                                        <span>✓ Mark Resolved</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ================= TAB 3: ESTATE UNITS OVERVIEW ================= -->
        <div x-show="currentTab === 'estate'" x-transition class="bg-white border border-slate-200/90 border-t-4 border-t-indigo-600 rounded-2xl p-6 shadow-xs">
            <div class="flex items-center justify-between mb-5 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                        🏢
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Estate Units & Apartment Inventory</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Physical inspection status and occupancy distribution for PHDL Unity Estate, Idu.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <?php foreach ($estateUnits as $u): 
                    $occ = !empty($u['tenant_name']);
                ?>
                    <div class="p-4 rounded-xl border <?= $occ ? 'bg-white border-slate-200 border-t-2 border-t-emerald-500' : 'bg-slate-50/70 border-dashed border-slate-300' ?>">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-extrabold text-slate-900 text-sm"><?= htmlspecialchars($u['unit_number']) ?></span>
                            <?php if ($occ): ?>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Occupied</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-200 text-slate-600">Vacant</span>
                            <?php endif; ?>
                        </div>
                        <span class="text-slate-500 block text-[11px]"><?= htmlspecialchars($u['apartment_type']) ?></span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-50 text-[#1D4ED8] border border-blue-200 mt-1">
                            📍 <?= htmlspecialchars($u['property_city'] ?? 'Abuja') ?>, <?= htmlspecialchars($u['property_state'] ?? 'FCT') ?>
                        </span>
                        
                        <div class="mt-3 pt-2.5 border-t border-slate-100">
                            <?php if ($occ): ?>
                                <span class="font-bold text-slate-800 block truncate"><?= htmlspecialchars($u['tenant_name']) ?></span>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="text-[10px] <?= ($u['agreement_status'] ?? '') === 'FULLY_EXECUTED' ? 'text-emerald-600 font-bold' : 'text-amber-600 font-bold' ?>">
                                        <?= ($u['agreement_status'] ?? '') === 'FULLY_EXECUTED' ? '✓ Signed' : '⏳ Pending' ?>
                                    </span>
                                    <a href="/agreement/preview?id=<?= (int)$u['lease_id'] ?>" class="text-[10px] font-bold text-[#1D4ED8] hover:underline">Preview →</a>
                                </div>
                            <?php else: ?>
                                <span class="text-slate-400 italic text-[11px]">Ready for tenant assignment</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ================= TAB 4: AUTO-CC NOTICES ================= -->
        <div x-show="currentTab === 'notices'" x-transition class="bg-white border border-slate-200/90 border-t-4 border-t-[#1D4ED8] rounded-2xl p-6 shadow-xs">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#1D4ED8] flex items-center justify-center font-bold">
                    📨
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Automated CC Routing Rule</h2>
                    <p class="text-xs text-slate-500">Real-time ground notifications</p>
                </div>
            </div>
            <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                As the assigned caretaker for PHDL Unity Estate, Idu, all automated notices dispatched to tenants (including rent reminder emails at T-30, T-7, daily dunning notices, and official payment receipts) automatically add <code>caretaker.idu@ogalandlord.ng</code> in the carbon copy (CC) recipients.
            </p>
            <div class="p-4 bg-blue-50/60 border border-blue-200 rounded-xl text-xs text-blue-900 font-medium flex items-center gap-2.5">
                <svg class="w-5 h-5 text-[#1D4ED8] shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>Zero manual follow-ups required. The platform ensures you are synchronized with tenant billing and compliance in real-time.</span>
            </div>
        </div>

        <!-- ================= TAB 5: RESIDENT PAYMENTS & REMITTANCE QUEUE ================= -->
        <div x-show="currentTab === 'payments'" x-transition class="space-y-4">
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-emerald-500 rounded-2xl p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
                        💳
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Estate Remittances & Resident Payments Queue</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Direct bank transfer and online transactions across your assigned properties.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-xl">
                        <?= count($caretakerPayments ?? []) ?> Transactions Logged
                    </span>
                </div>
            </div>

            <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/90 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="py-3.5 px-4">Receipt Ref</th>
                                <th class="py-3.5 px-4">Tenant / Unit</th>
                                <th class="py-3.5 px-4">Purpose</th>
                                <th class="py-3.5 px-4">Amount</th>
                                <th class="py-3.5 px-4">Channel & Beneficiary</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($caretakerPayments)): ?>
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-xs text-slate-500">
                                        No resident payments recorded yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($caretakerPayments as $cp): 
                                    $isConfirmed = ($cp['status'] === 'CONFIRMED');
                                    $isPending = ($cp['status'] === 'PENDING_CONFIRMATION');
                                ?>
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-4 px-4 font-mono font-bold text-slate-900">
                                            <?= htmlspecialchars($cp['receipt_number']) ?>
                                            <?php if ($isConfirmed): ?>
                                                <span class="block text-[10px] text-emerald-600 font-sans font-semibold mt-0.5">✓ Certified Stamped</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="font-bold text-slate-900 block"><?= htmlspecialchars($cp['tenant_name'] ?? 'Tenant') ?></span>
                                            <span class="text-slate-500 text-[11px] block"><?= htmlspecialchars($cp['unit_number'] ?? 'Unit') ?> • <?= htmlspecialchars($cp['property_title'] ?? '') ?></span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="font-semibold text-slate-800 uppercase text-[11px] bg-slate-100 px-2 py-0.5 rounded">
                                                <?= htmlspecialchars($cp['title'] ?? str_replace('_', ' ', $cp['payment_type'] ?? 'Payment')) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 font-black text-slate-900">
                                            NGN <?= number_format((float)$cp['amount'], 2) ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="font-bold text-slate-700 block"><?= htmlspecialchars($cp['payment_method']) ?></span>
                                            <span class="text-[10px] text-slate-500 block">Beneficiary: <?= htmlspecialchars($cp['beneficiary_type']) ?></span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <?php if ($isConfirmed): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                    ✓ Confirmed
                                                </span>
                                            <?php elseif ($isPending): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                    ⏳ Pending Verification
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                                    <?= htmlspecialchars($cp['status']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <?php if ($isPending): ?>
                                                    <button type="button" @click="confirmPayment(<?= (int)$cp['id'] ?>)"
                                                            class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-3 py-1.5 rounded-xl shadow-xs transition">
                                                        ✓ Verify & Stamp
                                                    </button>
                                                <?php else: ?>
                                                    <a href="/tenant/receipt?number=<?= urlencode($cp['receipt_number']) ?>" target="_blank"
                                                       class="bg-blue-50 hover:bg-blue-100 text-[#1D4ED8] font-bold text-xs px-3 py-1.5 rounded-xl border border-blue-200 transition inline-flex items-center gap-1">
                                                        <span>📜 Receipt</span>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= TAB 6: ESTATE NOTICE BOARD ================= -->
        <div x-show="currentTab === 'notices_board'" x-transition class="space-y-6">
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-sky-600 rounded-2xl p-6 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-lg">
                            📢
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Estate Digital Notice Board & Resident Broadcasts</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Post community updates, utility notifications, or security announcements to all residents.</p>
                        </div>
                    </div>
                    <button type="button" @click="showAnnouncementModal = true"
                            class="bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-1.5 self-start sm:self-auto">
                        <span>+ Broadcast Notice</span>
                    </button>
                </div>

                <div class="mt-6 space-y-4">
                    <?php if (empty($announcements)): ?>
                        <div class="p-8 text-center text-xs text-slate-400 border border-dashed border-slate-200 rounded-2xl bg-slate-50">
                            No announcements posted yet. Click "+ Broadcast Notice" to post to residents.
                        </div>
                    <?php else: ?>
                        <?php foreach ($announcements as $ann): 
                            $isUrgent = ($ann['priority'] === 'URGENT');
                        ?>
                            <div class="p-5 rounded-2xl border <?= $isUrgent ? 'border-rose-200 bg-rose-50/30 border-l-4 border-l-rose-500' : 'border-slate-200 bg-slate-50/50 border-l-4 border-l-sky-500' ?> text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($ann['title']) ?></h3>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?= $isUrgent ? 'bg-rose-100 text-rose-800' : 'bg-sky-100 text-sky-800' ?>">
                                        <?= htmlspecialchars($ann['priority']) ?>
                                    </span>
                                </div>
                                <p class="text-slate-600 leading-relaxed"><?= htmlspecialchars($ann['message']) ?></p>
                                <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between text-[10px] text-slate-400">
                                    <span>Posted by: <strong><?= htmlspecialchars($ann['sender_name'] ?? 'Caretaker') ?> (<?= htmlspecialchars($ann['sender_role'] ?? 'CARETAKER') ?>)</strong></span>
                                    <span><?= date('M j, Y, H:i', strtotime($ann['created_at'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ================= TAB 7: FACILITY RESERVATIONS QUEUE ================= -->
        <div x-show="currentTab === 'facilities'" x-transition class="space-y-6">
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-amber-500 rounded-2xl p-6 shadow-xs">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
                        🏊
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Shared Facility Reservation Queue</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Review and manage tenant booking requests for Clubhouse, Pool, Tennis Court, and Gym.</p>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    <?php if (empty($facilityBookings)): ?>
                        <div class="p-8 text-center text-xs text-slate-400 border border-dashed border-slate-200 rounded-2xl bg-slate-50">
                            No facility bookings registered yet.
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($facilityBookings as $bk): 
                                $isPending = ($bk['status'] === 'PENDING');
                                $isApproved = ($bk['status'] === 'APPROVED');
                            ?>
                                <div class="p-5 rounded-2xl border border-slate-200 bg-white text-xs space-y-3 shadow-xs">
                                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                        <strong class="text-slate-900 text-sm"><?= htmlspecialchars($bk['facility_name']) ?></strong>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?= $isApproved ? 'bg-emerald-100 text-emerald-800' : ($isPending ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') ?>">
                                            <?= htmlspecialchars($bk['status']) ?>
                                        </span>
                                    </div>
                                    <div class="space-y-1 text-slate-600 text-[11px]">
                                        <div>👤 Resident: <strong><?= htmlspecialchars($bk['tenant_name'] ?? 'Resident') ?></strong></div>
                                        <div>📅 Date: <strong><?= htmlspecialchars($bk['booking_date']) ?></strong> (<?= htmlspecialchars($bk['time_slot']) ?>)</div>
                                        <div>👥 Expected Guests: <strong><?= (int)($bk['guest_count'] ?? 1) ?> Persons</strong></div>
                                        <?php if (!empty($bk['purpose'])): ?>
                                            <div>🎯 Purpose: <?= htmlspecialchars($bk['purpose']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="pt-2.5 border-t border-slate-100 flex items-center justify-end gap-2">
                                        <?php if ($isPending): ?>
                                            <button type="button" @click="updateBookingStatus(<?= (int)$bk['id'] ?>, 'DECLINED')"
                                                    class="px-3 py-1.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs transition">
                                                ✕ Decline
                                            </button>
                                            <button type="button" @click="updateBookingStatus(<?= (int)$bk['id'] ?>, 'APPROVED')"
                                                    class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-xs">
                                                ✓ Approve Booking
                                            </button>
                                        <?php else: ?>
                                            <span class="text-[11px] font-medium text-slate-400">
                                                Status: <strong><?= htmlspecialchars($bk['status']) ?></strong>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </main>

    <!-- ================= INTERACTIVE TICKET CHAT MODAL ================= -->
    <div x-show="showChatModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;">
        <div @click.away="showChatModal = false" class="bg-white rounded-3xl max-w-2xl w-full overflow-hidden shadow-2xl border border-slate-200 flex flex-col max-h-[90vh]">
            
            <!-- Modal Header -->
            <div class="p-5 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#1D4ED8] text-white flex items-center justify-center font-bold text-sm">
                        💬
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-black text-blue-300" x-text="activeTicket?.ticket_code"></span>
                            <span class="text-slate-400 text-xs" x-text="activeTicket?.unit_number"></span>
                        </div>
                        <h3 class="text-base font-bold text-white truncate max-w-md" x-text="activeTicket?.title"></h3>
                    </div>
                </div>
                <button type="button" @click="showChatModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg">✕</button>
            </div>

            <!-- Escalation Notice Banner -->
            <template x-if="activeTicket?.is_escalated_to_landlord == 1">
                <div class="p-3 bg-amber-50 border-b border-amber-200 text-xs text-amber-900 flex items-center justify-between">
                    <span class="font-semibold">⚡ Escalated to Landlord — Landlord is active in this discussion thread.</span>
                    <span class="text-[10px] text-amber-700 italic" x-text="activeTicket?.escalation_reason"></span>
                </div>
            </template>
            <template x-if="activeTicket?.is_escalated_to_landlord != 1">
                <div class="p-2.5 bg-blue-50/70 border-b border-blue-200/80 text-[11px] text-blue-800 flex items-center justify-between">
                    <span>🔒 Private Caretaker Channel — Landlord cannot see these messages unless escalated.</span>
                    <button type="button" @click="openEscalateModal(activeTicket.id, activeTicket.ticket_code, activeTicket.title); showChatModal = false;" class="text-[11px] font-bold text-amber-800 hover:underline">
                        Tag Landlord →
                    </button>
                </div>
            </template>

            <!-- Chat Message Thread Body -->
            <div class="p-5 flex-1 overflow-y-auto space-y-4 bg-slate-50/50" id="caretakerChatThread">
                <template x-if="activeMessages.length === 0">
                    <div class="text-center py-8 text-xs text-slate-400">
                        No messages yet. Send a note to the tenant below.
                    </div>
                </template>

                <template x-for="msg in activeMessages" :key="msg.id">
                    <div :class="msg.sender_role === 'CARETAKER' ? 'flex flex-col items-end' : 'flex flex-col items-start'">
                        <div class="flex items-center gap-1.5 mb-1 text-[10px]">
                            <span class="font-bold text-slate-700" x-text="msg.sender_name"></span>
                            <span :class="{
                                'bg-blue-100 text-[#1D4ED8]': msg.sender_role === 'CARETAKER',
                                'bg-emerald-100 text-emerald-800': msg.sender_role === 'TENANT',
                                'bg-amber-100 text-amber-800': msg.sender_role === 'LANDLORD'
                            }" class="px-1.5 py-0.5 rounded font-black text-[9px]" x-text="msg.sender_role"></span>
                            <span class="text-slate-400" x-text="msg.created_at"></span>
                        </div>
                        <div :class="msg.sender_role === 'CARETAKER' ? 'bg-[#1D4ED8] text-white rounded-2xl rounded-tr-none' : 'bg-white border border-slate-200 text-slate-800 rounded-2xl rounded-tl-none shadow-xs'"
                             class="p-3 max-w-[85%] text-xs leading-relaxed">
                            <p class="whitespace-pre-wrap" x-text="msg.message"></p>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Chat Input Box -->
            <form @submit.prevent="sendChatMessage()" class="p-3.5 bg-white border-t border-slate-200 flex items-center gap-2 shrink-0">
                <input type="text" x-model="newMessageText" placeholder="Type response or status update to tenant..."
                       class="flex-1 text-xs px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1D4ED8] bg-slate-50"
                       :disabled="isSendingMessage">
                <button type="submit" :disabled="isSendingMessage || !newMessageText.trim()"
                        class="bg-[#1D4ED8] hover:bg-blue-700 disabled:opacity-50 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition shadow-xs">
                    <span x-show="!isSendingMessage">Send</span>
                    <span x-show="isSendingMessage">...</span>
                </button>
            </form>
        </div>
    </div>

    <!-- ================= ESCALATE TO LANDLORD MODAL ================= -->
    <div x-show="showEscalateModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;">
        <div @click.away="showEscalateModal = false" class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200">
            <div class="p-5 bg-amber-500 text-slate-950 flex items-center justify-between border-b border-amber-600">
                <div class="flex items-center gap-2.5 font-bold">
                    <span>⚡ Tag & Escalate Complaint to Landlord</span>
                </div>
                <button type="button" @click="showEscalateModal = false" class="text-slate-900 hover:text-white font-bold">✕</button>
            </div>
            <div class="p-6 space-y-4 text-xs">
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 leading-relaxed">
                    <strong>Notice:</strong> Complaints are isolated between tenant and caretaker by default. Escalating this ticket grants the Landlord visibility to review the problem and join the resolution thread.
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ticket Reference</label>
                    <p class="font-mono text-xs text-slate-600 bg-slate-100 p-2.5 rounded-xl border border-slate-200">
                        <span x-text="escalateTicketCode"></span>: <span x-text="escalateTicketTitle"></span>
                    </p>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Escalation Reason & Budget Need *</label>
                    <textarea x-model="escalationReason" rows="3" placeholder="e.g. Requires approval for capital expenditure above ₦50,000 to replace mainline water valve..."
                              class="w-full text-xs p-3 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1D4ED8] bg-slate-50"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showEscalateModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold">Cancel</button>
                    <button type="button" @click="submitEscalation()" :disabled="!escalationReason.trim()" class="bg-amber-600 hover:bg-amber-700 text-white font-bold px-4 py-2 rounded-xl shadow-xs transition disabled:opacity-50">
                        Confirm Escalation
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= ARTISAN DISPATCH & WORK ORDER MODAL ================= -->
    <div x-show="showArtisanDispatchModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;">
        <div @click.away="showArtisanDispatchModal = false" class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200 text-xs">
            <div class="p-5 bg-indigo-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2 font-bold text-sm">
                    <span>🔧 Dispatch Artisan & Issue Work Order</span>
                </div>
                <button type="button" @click="showArtisanDispatchModal = false" class="text-white hover:text-slate-200 font-bold">✕</button>
            </div>
            <form @submit.prevent="submitArtisanDispatch()" class="p-6 space-y-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ticket Reference</label>
                    <p class="font-mono text-xs text-slate-600 bg-slate-100 p-2.5 rounded-xl border border-slate-200">
                        <span x-text="artisanForm.ticket_code"></span>: <span x-text="artisanForm.ticket_title"></span>
                    </p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Select Trusted Artisan</label>
                    <select x-model="artisanForm.artisan_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8]">
                        <option value="">-- Choose Artisan from Directory --</option>
                        <?php foreach ($artisans as $art): ?>
                            <option value="<?= (int)$art['id'] ?>">
                                <?= htmlspecialchars($art['full_name']) ?> (<?= htmlspecialchars($art['trade']) ?> - ★ <?= htmlspecialchars((string)($art['rating'] ?? '4.8')) ?>) • Tel: <?= htmlspecialchars($art['phone_number'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Estimated Cost (NGN)</label>
                        <input type="number" step="500" min="0" x-model="artisanForm.estimated_cost" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 font-bold text-slate-900">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Work Status</label>
                        <select x-model="artisanForm.work_status" required class="w-full px-3 py-2 rounded-xl border border-slate-300 font-bold">
                            <option value="ASSIGNED">ASSIGNED (Work order dispatched)</option>
                            <option value="IN_PROGRESS">IN_PROGRESS (Artisan on-site)</option>
                            <option value="COMPLETED">COMPLETED (Repairs concluded)</option>
                        </select>
                    </div>
                </div>

                <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-[11px] text-blue-900 leading-relaxed">
                    📱 The artisan will receive work notification with property and unit coordinates. Status changes sync in real-time.
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showArtisanDispatchModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold">Cancel</button>
                    <button type="submit" :disabled="submittingArtisan" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-xl shadow-xs transition">
                        <span x-text="submittingArtisan ? 'Dispatching...' : 'Dispatch Artisan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= BROADCAST ANNOUNCEMENT MODAL ================= -->
    <div x-show="showAnnouncementModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;">
        <div @click.away="showAnnouncementModal = false" class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200 text-xs">
            <div class="p-5 bg-sky-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2 font-bold text-sm">
                    <span>📢 Broadcast Community Announcement</span>
                </div>
                <button type="button" @click="showAnnouncementModal = false" class="text-white hover:text-slate-200 font-bold">✕</button>
            </div>
            <form @submit.prevent="submitAnnouncement()" class="p-6 space-y-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Headline / Notice Title</label>
                    <input type="text" x-model="announcementForm.title" required placeholder="e.g. Scheduled Generator Maintenance & Power Outage Notice"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Urgency Priority</label>
                    <select x-model="announcementForm.priority" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                        <option value="GENERAL">General Notice</option>
                        <option value="IMPORTANT">Important</option>
                        <option value="URGENT">Urgent (Immediate Resident Attention)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Detailed Message to Residents</label>
                    <textarea x-model="announcementForm.message" rows="4" required placeholder="Provide clear operational details, schedules, impact, or security guidance..."
                              class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500"></textarea>
                </div>

                <div class="p-3 bg-sky-50 border border-sky-200 rounded-xl text-[11px] text-sky-900 leading-relaxed">
                    🔔 This announcement will appear immediately on the Resident Notice Board for <?= htmlspecialchars($property['title'] ?? 'Estate') ?>.
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showAnnouncementModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold">Cancel</button>
                    <button type="submit" :disabled="submittingAnnouncement" class="bg-sky-600 hover:bg-sky-700 text-white font-bold px-5 py-2 rounded-xl shadow-xs transition">
                        <span x-text="submittingAnnouncement ? 'Publishing...' : 'Broadcast to Residents'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function caretakerApp() {
    return {
        sidebarOpen: false,
        currentTab: new URLSearchParams(window.location.search).get('tab') || 'agreements',
        statusFilter: 'ALL',
        searchQuery: '',
        showChatModal: false,
        showEscalateModal: false,
        showArtisanDispatchModal: false,
        showAnnouncementModal: false,
        submittingArtisan: false,
        submittingAnnouncement: false,
        escalateTicketId: null,
        escalateTicketCode: '',
        escalateTicketTitle: '',
        escalationReason: '',
        activeTicket: null,
        activeMessages: [],
        newMessageText: '',
        isSendingMessage: false,

        artisanForm: {
            ticket_id: 0,
            ticket_code: '',
            ticket_title: '',
            artisan_id: '',
            work_status: 'ASSIGNED',
            estimated_cost: 0,
            actual_cost: 0
        },

        announcementForm: {
            property_id: <?= (int)($property['id'] ?? 1) ?>,
            title: '',
            message: '',
            priority: 'GENERAL'
        },

        matchesFilter(statusType, searchableText) {
            if (this.statusFilter !== 'ALL' && this.statusFilter !== statusType) {
                return false;
            }
            if (this.searchQuery && this.searchQuery.trim() !== '') {
                return searchableText.includes(this.searchQuery.toLowerCase().trim());
            }
            return true;
        },

        openArtisanDispatchModal(ticketId, code, title, artisanId, workStatus, estCost, actCost) {
            this.artisanForm.ticket_id = ticketId;
            this.artisanForm.ticket_code = code;
            this.artisanForm.ticket_title = title;
            this.artisanForm.artisan_id = artisanId || '';
            this.artisanForm.work_status = workStatus || 'ASSIGNED';
            this.artisanForm.estimated_cost = estCost || 0;
            this.artisanForm.actual_cost = actCost || 0;
            this.showArtisanDispatchModal = true;
        },

        async submitArtisanDispatch() {
            if (!this.artisanForm.artisan_id) {
                alert('Please select an artisan from the directory.');
                return;
            }
            this.submittingArtisan = true;
            try {
                const res = await fetch('/api/v1/artisans/assign', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        ticket_id: this.artisanForm.ticket_id,
                        artisan_id: this.artisanForm.artisan_id,
                        estimated_cost: this.artisanForm.estimated_cost
                    })
                });
                const data = await res.json();
                if (data.success) {
                    if (this.artisanForm.work_status && this.artisanForm.work_status !== 'ASSIGNED') {
                        await fetch('/api/v1/artisans/status', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                ticket_id: this.artisanForm.ticket_id,
                                work_status: this.artisanForm.work_status,
                                actual_cost: this.artisanForm.actual_cost
                            })
                        });
                    }
                    alert('Artisan dispatched successfully! Work order assigned.');
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to dispatch artisan');
                }
            } catch (e) {
                alert('Network error dispatching artisan: ' + e.message);
            } finally {
                this.submittingArtisan = false;
            }
        },

        async submitAnnouncement() {
            if (!this.announcementForm.title.trim() || !this.announcementForm.message.trim()) {
                alert('Please fill in both title and message.');
                return;
            }
            this.submittingAnnouncement = true;
            try {
                const res = await fetch('/api/v1/community/announcements', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.announcementForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert('Community announcement broadcast successfully to residents!');
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to publish announcement');
                }
            } catch (e) {
                alert('Network error publishing announcement: ' + e.message);
            } finally {
                this.submittingAnnouncement = false;
            }
        },

        async updateBookingStatus(bookingId, status) {
            const verb = status === 'APPROVED' ? 'approve' : 'decline';
            if (!confirm(`Are you sure you want to ${verb} this facility reservation?`)) return;
            try {
                const res = await fetch('/api/v1/community/facilities/status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ booking_id: bookingId, status: status })
                });
                const data = await res.json();
                if (data.success) {
                    alert(`Facility reservation ${status.toLowerCase()}!`);
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to update reservation');
                }
            } catch (e) {
                alert('Network error updating reservation: ' + e.message);
            }
        },

        async openChatThread(ticketId) {
            try {
                const res = await fetch(`/api/v1/tickets/${ticketId}/thread`);
                const data = await res.json();
                if (data.success) {
                    this.activeTicket = data.ticket;
                    this.activeMessages = data.messages || [];
                    this.showChatModal = true;
                    this.$nextTick(() => {
                        const el = document.getElementById('caretakerChatThread');
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                } else {
                    alert(data.error || 'Failed to load ticket thread');
                }
            } catch (e) {
                alert('Error fetching chat thread: ' + e.message);
            }
        },

        async sendChatMessage() {
            if (!this.newMessageText.trim() || !this.activeTicket) return;
            this.isSendingMessage = true;
            try {
                const res = await fetch(`/api/v1/tickets/${this.activeTicket.id}/messages`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: this.newMessageText })
                });
                const data = await res.json();
                if (data.success) {
                    this.activeMessages.push(data.message);
                    this.newMessageText = '';
                    this.$nextTick(() => {
                        const el = document.getElementById('caretakerChatThread');
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                } else {
                    alert(data.error || 'Failed to send message');
                }
            } catch (e) {
                alert('Error sending message: ' + e.message);
            } finally {
                this.isSendingMessage = false;
            }
        },

        openEscalateModal(id, code, title) {
            this.escalateTicketId = id;
            this.escalateTicketCode = code;
            this.escalateTicketTitle = title;
            this.escalationReason = '';
            this.showEscalateModal = true;
        },

        async submitEscalation() {
            if (!this.escalationReason.trim() || !this.escalateTicketId) return;
            try {
                const res = await fetch(`/api/v1/caretaker/tickets/${this.escalateTicketId}/escalate`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ reason: this.escalationReason })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Ticket successfully escalated to Landlord! The Landlord now has visibility.');
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to escalate ticket');
                }
            } catch (e) {
                alert('Error escalating ticket: ' + e.message);
            }
        },

        async markResolved(ticketId) {
            if (!confirm('Mark this maintenance ticket as RESOLVED?')) return;
            try {
                const res = await fetch(`/api/v1/tickets/${ticketId}/status`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ status: 'RESOLVED' })
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to update status');
                }
            } catch (e) {
                alert('Error updating status: ' + e.message);
            }
        },

        async confirmPayment(paymentId) {
            if (!confirm('Verify and stamp this resident payment receipt?')) return;
            try {
                const res = await fetch(`/api/v1/payments/${paymentId}/confirm`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    alert('Payment successfully confirmed! Official receipt stamped.');
                    window.location.reload();
                } else {
                    alert(data.error || 'Confirmation failed');
                }
            } catch (e) {
                alert('Error confirming payment: ' + e.message);
            }
        }
    }
}
</script>
</body>
</html>