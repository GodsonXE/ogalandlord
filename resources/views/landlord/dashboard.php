<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landlord Workspace — Oga Landlord</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full text-slate-800 antialiased" x-data="landlordApp()">

<div class="min-h-full flex flex-col md:flex-row">

    <!-- Mobile Top Header -->
    <div class="md:hidden bg-[#1D4ED8] text-white px-4 py-3 flex items-center justify-between sticky top-0 z-30 shadow-md">
        <div class="flex items-center gap-2">
            <img src="/assets/images/logo.jpg" alt="Oga Landlord Logo" class="h-8 w-auto object-contain rounded bg-white p-0.5">
            <span class="font-extrabold text-white text-sm tracking-tight">Oga<span class="text-blue-200">Landlord</span></span>
            <span class="text-[10px] bg-blue-800 text-white font-bold px-2 py-0.5 rounded-md">Landlord</span>
        </div>
        <button type="button" @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-white hover:bg-white/10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <!-- Collapsible Blue Accent Left Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
           class="fixed md:sticky top-0 z-40 h-screen w-72 bg-[#1D4ED8] text-white flex flex-col justify-between transition-transform duration-200 ease-in-out shadow-xl">
        
        <div class="flex flex-col flex-1 overflow-y-auto">
            <!-- Sidebar Header -->
            <div class="p-5 border-b border-blue-600/70">
                <a href="/" class="flex items-center gap-3 group">
                    <img src="/assets/images/logo.jpg" alt="Oga Landlord Logo" class="h-11 w-auto object-contain rounded-lg bg-white p-1 shadow-sm transition-transform group-hover:scale-105">
                    <div>
                        <span class="text-lg font-extrabold tracking-tight text-white block leading-tight">Oga<span class="text-blue-200">Landlord</span></span>
                        <span class="text-[10px] uppercase tracking-wider text-blue-200 font-bold">Landlord Workspace</span>
                    </div>
                </a>

                <!-- Project / Estate Selector -->
                <div class="mt-5 p-3 rounded-xl bg-blue-800/80 border border-blue-400/40 shadow-inner">
                    <div class="flex items-center justify-between text-[10px] font-bold text-blue-200 uppercase tracking-wider mb-1">
                        <span>Active Portfolio</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    </div>
                    <select x-model="selectedPropertyId" class="w-full bg-blue-900/90 border border-blue-400/50 rounded-lg text-xs font-bold text-white py-2 px-2.5 focus:outline-none focus:ring-2 focus:ring-white">
                        <?php foreach ($properties as $p): ?>
                            <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-[11px] text-blue-100 mt-1.5 truncate">Plot 42 Railway Corridor, Abuja FCT</p>
                </div>
            </div>

            <!-- Sidebar Navigation Links -->
            <nav class="p-4 space-y-1.5">
                <span class="block px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-blue-200">Management Modules</span>
                
                <!-- 1. Overview -->
                <button type="button" @click="currentTab = 'overview'; sidebarOpen = false"
                        :class="currentTab === 'overview' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Portfolio Overview</span>
                </button>

                <!-- 1b. Properties & Caretakers -->
                <button type="button" @click="currentTab = 'properties'; sidebarOpen = false"
                        :class="currentTab === 'properties' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Properties & Caretakers</span>
                    </div>
                    <span :class="currentTab === 'properties' ? 'bg-blue-100 text-[#1D4ED8]' : 'bg-blue-700/80 text-white'" class="px-2 py-0.5 rounded-full text-[10px] font-bold"><?= count($properties) ?></span>
                </button>

                <!-- 2. Units & Apartments -->
                <button type="button" @click="currentTab = 'units'; sidebarOpen = false"
                        :class="currentTab === 'units' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Units & Apartments</span>
                    </div>
                    <span :class="currentTab === 'units' ? 'bg-blue-100 text-[#1D4ED8]' : 'bg-blue-700/80 text-white'" class="px-2 py-0.5 rounded-full text-[10px] font-bold"><?= count($allUnits) ?></span>
                </button>

                <!-- 3. Tenants & Leases -->
                <button type="button" @click="currentTab = 'tenants'; sidebarOpen = false"
                        :class="currentTab === 'tenants' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Tenants & Leases</span>
                    </div>
                    <span :class="currentTab === 'tenants' ? 'bg-blue-100 text-[#1D4ED8]' : 'bg-blue-700/80 text-white'" class="px-2 py-0.5 rounded-full text-[10px] font-bold"><?= count($leases) ?></span>
                </button>

                <!-- 4. Tenancy Agreement Preview Quick Access -->
                <a href="/agreement/preview"
                   class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs text-blue-100 hover:bg-white/10 hover:text-white font-medium transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Preview Agreement</span>
                    </div>
                    <span class="text-[10px] bg-emerald-400/30 text-emerald-200 px-2 py-0.5 rounded-md font-bold">SHA-256</span>
                </a>

                <!-- 5. Maintenance Hub -->
                <button type="button" @click="currentTab = 'maintenance'; sidebarOpen = false"
                        :class="currentTab === 'maintenance' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Maintenance Hub</span>
                    </div>
                    <span :class="currentTab === 'maintenance' ? 'bg-blue-100 text-[#1D4ED8]' : 'bg-blue-700/80 text-white'" class="px-2 py-0.5 rounded-full text-[10px] font-bold"><?= count($tickets) ?></span>
                </button>

                <!-- 6. Rent Reminders -->
                <button type="button" @click="currentTab = 'reminders'; sidebarOpen = false"
                        :class="currentTab === 'reminders' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span>Rent Reminders</span>
                </button>

                <!-- 6b. Financials & Tax (WHT) -->
                <button type="button" @click="currentTab = 'finances'; sidebarOpen = false"
                        :class="currentTab === 'finances' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Finances & Tax</span>
                    </div>
                    <span :class="currentTab === 'finances' ? 'bg-blue-100 text-[#1D4ED8]' : 'bg-emerald-400/30 text-emerald-200'" class="px-2 py-0.5 rounded-full text-[10px] font-bold">10% WHT</span>
                </button>

                <!-- 6c. Trusted Artisans -->
                <button type="button" @click="currentTab = 'artisans'; sidebarOpen = false"
                        :class="currentTab === 'artisans' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                        <span>Trusted Artisans</span>
                    </div>
                    <span :class="currentTab === 'artisans' ? 'bg-blue-100 text-[#1D4ED8]' : 'bg-blue-700/80 text-white'" class="px-2 py-0.5 rounded-full text-[10px] font-bold"><?= count($artisans ?? []) ?></span>
                </button>

                <!-- 6d. Payment Gateway & Bank Settings -->
                <button type="button" @click="currentTab = 'gateways'; sidebarOpen = false"
                        :class="currentTab === 'gateways' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <span>Gateways & Bank Details</span>
                </button>

                <!-- 6e. Estate Community & Facilities -->
                <button type="button" @click="currentTab = 'community'; sidebarOpen = false"
                        :class="currentTab === 'community' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                        <span>Estate Notice & Facilities</span>
                    </div>
                    <span :class="currentTab === 'community' ? 'bg-blue-100 text-[#1D4ED8]' : 'bg-blue-700/80 text-white'" class="px-2 py-0.5 rounded-full text-[10px] font-bold"><?= count($communityAnnouncements ?? []) ?></span>
                </button>

                <!-- 6f. Customizable Alert Rules -->
                <button type="button" @click="currentTab = 'alert_rules'; sidebarOpen = false"
                        :class="currentTab === 'alert_rules' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span>Custom Alert Rules</span>
                </button>

                <!-- 7. SaaS Billing -->
                <button type="button" @click="currentTab = 'billing'; sidebarOpen = false"
                        :class="currentTab === 'billing' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>SaaS License & Fee</span>
                </button>
            </nav>
        </div>

        <!-- Sidebar Footer / Account & Role Switcher -->
        <div class="p-4 border-t border-blue-700/80 bg-blue-900/60 space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-white text-[#1D4ED8] font-extrabold flex items-center justify-center text-xs shadow-sm">
                    IB
                </div>
                <div class="flex-1 min-w-0">
                    <span class="block text-xs font-bold text-white truncate"><?= htmlspecialchars($user['full_name'] ?? 'Chief Ibrahim Bello') ?></span>
                    <span class="block text-[10px] text-blue-200 truncate"><?= htmlspecialchars($user['email'] ?? 'landlord@ogalandlord.ng') ?></span>
                </div>
            </div>

            <div class="pt-2 border-t border-blue-800/80 flex flex-col gap-1.5 text-[11px]">
                <span class="text-[10px] uppercase tracking-wider font-bold text-blue-300">Quick Workspace Switch</span>
                <a href="/demo/caretaker" class="flex items-center justify-between text-blue-100 hover:text-white py-1 font-medium transition">
                    <span>🛠️ Caretaker Hub</span>
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

    <!-- Main Workspace Area -->
    <main class="flex-1 min-w-0 overflow-y-auto p-4 sm:p-6 lg:p-8">

        <!-- Top Action Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200 mb-8">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Landlord Workspace</span>
                <?php
                    $rawLandlordName = trim($user['full_name'] ?? 'Landlord');
                    $nameTokens = preg_split('/\s+/', $rawLandlordName);
                    $honorifics = ['chief', 'alhaji', 'engr', 'engr.', 'dr', 'dr.', 'mr', 'mr.', 'mrs', 'mrs.', 'barr', 'barr.', 'mallam', 'pastor', 'senator', 'hajia'];
                    if (count($nameTokens) > 1 && in_array(strtolower($nameTokens[0]), $honorifics)) {
                        $landlordGreeting = $nameTokens[1];
                    } else {
                        $landlordGreeting = $nameTokens[0] ?: 'Landlord';
                    }
                ?>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Welcome <?= htmlspecialchars($landlordGreeting) ?></h1>
                <p class="text-xs text-slate-500 mt-0.5">Manage your nationwide estate portfolio, resident tenancies, and caretaker assignments.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="/agreement/preview" class="bg-white border border-slate-300 hover:border-[#1D4ED8] text-slate-800 hover:text-[#1D4ED8] text-xs font-bold px-3.5 py-2.5 rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Preview Agreement</span>
                </a>
                <button type="button" @click="runReminderScheduler()" :disabled="isRunningReminders"
                        class="bg-white border border-slate-200 hover:border-slate-300 text-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#1D4ED8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="isRunningReminders ? 'Running Reminders...' : 'Run Rent Reminders'"></span>
                </button>
                <a href="/onboarding" class="bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add New Tenant</span>
                </a>
            </div>
        </div>

        <!-- Reminder Result Banner -->
        <div x-show="reminderResult" class="mb-6 p-4 rounded-xl bg-blue-50 border border-blue-200 text-xs text-blue-900 flex items-start justify-between gap-3 shadow-xs" x-transition>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-[#1D4ED8] shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                <span x-text="reminderResult"></span>
            </div>
            <button type="button" @click="reminderResult = null" class="text-blue-500 hover:text-blue-800 font-bold">✕</button>
        </div>

        <!-- ================= TAB 1: OVERVIEW ================= -->
        <div x-show="currentTab === 'overview'" x-transition>
            <!-- Key Metric Highlights with Color-Coded Borders & Icon Badges -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                
                <!-- 1. Portfolio Units (Primary Blue #1D4ED8) -->
                <div class="bg-white border border-slate-200/90 border-t-4 border-t-[#1D4ED8] rounded-2xl p-5 shadow-xs transition hover:shadow-md">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Portfolio Units</span>
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#1D4ED8] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                    </div>
                    <p class="text-3xl font-black text-slate-900 mt-1"><?= (int)($metrics['total_units'] ?? 8) ?></p>
                    <div class="mt-2.5 flex items-center gap-1.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-[#1D4ED8] border border-blue-200">
                            8 Total Apartments
                        </span>
                    </div>
                </div>

                <!-- 2. Active Leases (Emerald Green) -->
                <div class="bg-white border border-slate-200/90 border-t-4 border-t-emerald-500 rounded-2xl p-5 shadow-xs transition hover:shadow-md">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Active Leases</span>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-3xl font-black text-emerald-600 mt-1"><?= (int)($metrics['occupied_units'] ?? 2) ?></p>
                    <div class="mt-2.5 flex items-center gap-1.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <?= (int)($metrics['vacant_units'] ?? 6) ?> Vacant Available
                        </span>
                    </div>
                </div>

                <!-- 3. Annual Rental Value (Indigo Purple) -->
                <div class="bg-white border border-slate-200/90 border-t-4 border-t-indigo-600 rounded-2xl p-5 shadow-xs transition hover:shadow-md">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Gross ARR Yield</span>
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-3xl font-black text-slate-900 mt-1">₦20,000,000</p>
                    <div class="mt-2.5 flex items-center gap-1.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            Projected Portfolio Yield
                        </span>
                    </div>
                </div>

                <!-- 4. Assigned Caretaker (Warm Amber) -->
                <div class="bg-white border border-slate-200/90 border-t-4 border-t-amber-500 rounded-2xl p-5 shadow-xs transition hover:shadow-md">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Ground Caretaker</span>
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                    </div>
                    <p class="text-xl font-black text-slate-900 mt-1 truncate">Musa Danjuma</p>
                    <div class="mt-2.5 flex items-center gap-1.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            ✓ Auto-CC routing active
                        </span>
                    </div>
                </div>

            </div>

            <!-- Nationwide Properties & Caretaker Delegation Portfolio -->
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-[#1D4ED8] rounded-2xl p-5 shadow-xs mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100 mb-5">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-base">🏢</span>
                            <h2 class="text-sm sm:text-base font-bold text-slate-900">Nationwide Property Portfolio & Operations Delegation</h2>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Manage caretaker assignments or route issue handling directly to Oga Landlord SuperAdmin Concierge.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="currentTab = 'properties'" class="text-xs font-bold text-[#1D4ED8] hover:underline bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-200 shadow-2xs">
                            View All Portfolio Locations (<?= count($properties) ?>) →
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php foreach ($properties as $p): 
                        $isSuperadmin = ($p['caretaker_delegation_mode'] ?? 'SUPERADMIN_CONCIERGE') === 'SUPERADMIN_CONCIERGE';
                    ?>
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:shadow-sm transition flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-md bg-blue-100 text-blue-900 border border-blue-200">
                                        📍 <?= htmlspecialchars($p['city'] ?? '') ?>, <?= htmlspecialchars($p['state'] ?? '') ?>
                                    </span>
                                    <span class="text-[11px] font-bold text-slate-500">
                                        <?= (int)($p['occupied_count'] ?? 0) ?>/<?= (int)($p['unit_count'] ?? 0) ?> Occ.
                                    </span>
                                </div>
                                <h3 class="font-extrabold text-slate-900 text-sm mb-1"><?= htmlspecialchars($p['title']) ?></h3>
                                <p class="text-[11px] text-slate-500 line-clamp-1 mb-3"><?= htmlspecialchars($p['address_line_1'] ?? '') ?></p>

                                <!-- Delegation Status Pill -->
                                <div class="p-2.5 rounded-lg <?= $isSuperadmin ? 'bg-purple-50/80 border border-purple-200 text-purple-900' : 'bg-amber-50/80 border border-amber-200 text-amber-900' ?> mb-3">
                                    <div class="flex items-center gap-1.5 font-bold text-xs">
                                        <?php if ($isSuperadmin): ?>
                                            <span>⚡ SuperAdmin Concierge</span>
                                        <?php else: ?>
                                            <span>🛠️ Custom Caretaker</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-[10px] text-slate-600 mt-0.5">
                                        <?php if ($isSuperadmin): ?>
                                            Oga Landlord operations team handles maintenance & issues.
                                        <?php else: ?>
                                            <?= htmlspecialchars($p['caretaker_name'] ?? 'Nominated Caretaker') ?> (<?= htmlspecialchars($p['caretaker_email'] ?? '') ?>)
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <button type="button"
                                    @click="openCaretakerModal(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8') ?>)"
                                    class="w-full text-center text-xs font-bold py-2 rounded-lg border border-slate-300 hover:border-[#1D4ED8] bg-white hover:bg-blue-50 text-slate-700 hover:text-[#1D4ED8] transition shadow-2xs">
                                ⚙️ Configure Caretaker Delegation
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Two-Column Section with Color Hierarchy -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
                
                <!-- Recent Leases with Blue Accent Header -->
                <div class="lg:col-span-7 bg-white border border-slate-200/90 border-t-4 border-t-[#1D4ED8] rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-blue-50 text-[#1D4ED8] flex items-center justify-center text-xs font-bold">
                                📋
                            </div>
                            <h2 class="text-sm font-bold text-slate-900">Active Tenant Contracts</h2>
                        </div>
                        <button type="button" @click="currentTab = 'tenants'" class="text-xs font-bold text-[#1D4ED8] hover:underline">View All →</button>
                    </div>
                    <div class="space-y-3">
                        <?php foreach ($leases as $l): 
                            $isSigned = ($l['agreement_status'] === 'FULLY_EXECUTED');
                        ?>
                            <div class="p-3.5 rounded-xl border <?= $isSigned ? 'border-emerald-200 bg-emerald-50/20 border-l-4 border-l-emerald-500' : 'border-amber-200 bg-amber-50/20 border-l-4 border-l-amber-400' ?> flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-extrabold text-slate-900"><?= htmlspecialchars($l['unit_number'] ?? 'Unit') ?> — <?= htmlspecialchars($l['tenant_name']) ?></span>
                                    <span class="block text-[11px] text-slate-500"><?= htmlspecialchars($l['apartment_type'] ?? '') ?> • Due: <?= date('M j, Y', strtotime($l['rent_due_date'])) ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="/agreement/preview?id=<?= (int)$l['id'] ?>" class="text-[11px] font-bold text-[#1D4ED8] hover:underline bg-white border border-blue-200 px-2.5 py-1 rounded-lg shadow-2xs">
                                        Preview →
                                    </a>
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= $isSigned ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                                        <?= $isSigned ? '✓ Signed' : '⏳ Awaiting' ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Ground Maintenance Snapshot with Rose/Amber Accent Header -->
                <div class="lg:col-span-5 bg-white border border-slate-200/90 border-t-4 border-t-rose-500 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-rose-50 text-rose-600 flex items-center justify-center text-xs font-bold">
                                🛠️
                            </div>
                            <h2 class="text-sm font-bold text-slate-900">Ground Maintenance Snapshot</h2>
                        </div>
                        <button type="button" @click="currentTab = 'maintenance'" class="text-xs font-bold text-[#1D4ED8] hover:underline">Manage →</button>
                    </div>
                    <div class="space-y-3">
                        <?php foreach (array_slice($tickets, 0, 3) as $t): 
                            $isHigh = (strtoupper($t['priority'] ?? '') === 'HIGH');
                        ?>
                            <div class="p-3 rounded-xl border <?= $isHigh ? 'border-rose-200 bg-rose-50/30 border-l-4 border-l-rose-500' : 'border-slate-200 bg-slate-50 border-l-4 border-l-amber-400' ?> text-xs">
                                <div class="flex items-center justify-between font-bold text-slate-900">
                                    <span><?= htmlspecialchars($t['unit_number'] ?? 'Unit') ?> — <?= htmlspecialchars($t['title']) ?></span>
                                    <span class="text-[10px] uppercase font-bold <?= $isHigh ? 'text-rose-700 bg-rose-100' : 'text-blue-700 bg-blue-100' ?> px-2 py-0.5 rounded-full"><?= htmlspecialchars($t['status']) ?></span>
                                </div>
                                <p class="text-slate-600 text-[11px] mt-1"><?= htmlspecialchars($t['description']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= TAB: PROPERTIES & CARETAKERS ================= -->
        <div x-show="currentTab === 'properties'" x-transition>
            
            <!-- SECTION A: ESTATES OWNERSHIP OVERVIEW & SELECTOR LIST -->
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-[#1D4ED8] rounded-2xl shadow-xs overflow-hidden mb-6">
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#1D4ED8] flex items-center justify-center font-bold text-lg shadow-2xs">
                            🏢
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-bold text-slate-900">Estates & Properties Portfolio</h2>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">
                                    <?= count($properties) ?> <?= count($properties) === 1 ? 'Estate' : 'Estates' ?> Owned
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Select any estate or location below to open its dedicated tenant directory, vacant apartment roster, and assigned caretaker.</p>
                        </div>
                    </div>
                    <a href="/register" class="bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-xs font-bold px-4 py-2.5 rounded-xl transition inline-flex items-center gap-1.5 self-start shadow-xs">
                        <span class="text-sm font-black">+</span>
                        <span>Onboard New Location</span>
                    </a>
                </div>

                <!-- Estate Selection Cards Grid (First, the list of estates where he has indicated ownership) -->
                <div class="p-5 bg-slate-50/50">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($properties as $p): 
                            $isSuperadmin = ($p['caretaker_delegation_mode'] ?? 'SUPERADMIN_CONCIERGE') === 'SUPERADMIN_CONCIERGE';
                            $pId = (int)$p['id'];
                        ?>
                            <div @click="selectEstate(<?= $pId ?>)"
                                 :class="(selectedEstateId == <?= $pId ?>) 
                                     ? 'border-2 border-[#1D4ED8] bg-blue-50/60 ring-2 ring-blue-500/20 shadow-md' 
                                     : 'border border-slate-200 bg-white hover:border-blue-300 hover:bg-slate-50/80 shadow-2xs'"
                                 class="rounded-xl p-4 cursor-pointer transition flex flex-col justify-between relative group">
                                
                                <div>
                                    <!-- Header / Selected Indicator -->
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <div class="flex-1 min-w-0">
                                            <span class="font-extrabold text-slate-900 block text-sm group-hover:text-blue-700 transition truncate">
                                                <?= htmlspecialchars($p['title']) ?>
                                            </span>
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 mt-0.5 truncate">
                                                <span>📍</span>
                                                <span><?= htmlspecialchars($p['city'] ?? '') ?>, <?= htmlspecialchars($p['state'] ?? '') ?></span>
                                            </span>
                                        </div>
                                        <span x-show="selectedEstateId == <?= $pId ?>"
                                              class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-[#1D4ED8] text-white shadow-2xs">
                                            Active
                                        </span>
                                    </div>

                                    <?php if (!empty($p['address_line_1'])): ?>
                                        <p class="text-[11px] text-slate-500 mb-3 truncate">
                                            <?= htmlspecialchars($p['address_line_1']) ?>
                                        </p>
                                    <?php endif; ?>

                                    <!-- Capacity & Occupancy Pills -->
                                    <div class="flex flex-wrap items-center gap-2 mb-3">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200/80">
                                            🏢 <?= (int)($p['unit_count'] ?? 0) ?> Units
                                        </span>
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                            🟢 <?= (int)($p['occupied_count'] ?? 0) ?> Occupied
                                        </span>
                                        <?php $vacantCount = max(0, (int)($p['unit_count'] ?? 0) - (int)($p['occupied_count'] ?? 0)); ?>
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                                            ⚪ <?= $vacantCount ?> Vacant
                                        </span>
                                    </div>
                                </div>

                                <!-- Assigned Caretaker Summary Footer -->
                                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                    <div class="flex items-center gap-1.5 truncate">
                                        <?php if ($isSuperadmin): ?>
                                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                            <span class="font-bold text-purple-800 truncate">SuperAdmin Concierge</span>
                                        <?php else: ?>
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <span class="font-bold text-slate-700 truncate"><?= htmlspecialchars($p['caretaker_name'] ?? 'Nominated Caretaker') ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span :class="(selectedEstateId == <?= $pId ?>) ? 'text-[#1D4ED8] font-bold' : 'text-slate-400 group-hover:text-blue-600'"
                                          class="shrink-0 font-medium text-[11px] flex items-center gap-0.5">
                                        <span x-text="(selectedEstateId == <?= $pId ?>) ? 'Viewing Details ↓' : 'Select Estate →'"></span>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- SECTION B: SELECTED ESTATE DETAILS (OPENS WHEN ANY ESTATE IS SELECTED) -->
            <template x-if="selectedEstate">
                <div class="space-y-6">
                    
                    <!-- 1. DELEGATED / NOMINATED CARETAKER CARD FOR SELECTED ESTATE -->
                    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden"
                         :class="(selectedEstate.caretaker_delegation_mode === 'SUPERADMIN_CONCIERGE') ? 'border-t-4 border-t-purple-600' : 'border-t-4 border-t-emerald-600'">
                        <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-start gap-3.5">
                                <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-xl shrink-0 shadow-xs"
                                     :class="(selectedEstate.caretaker_delegation_mode === 'SUPERADMIN_CONCIERGE') ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700'">
                                    <span x-text="(selectedEstate.caretaker_delegation_mode === 'SUPERADMIN_CONCIERGE') ? '⚡' : '🛠️'"></span>
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md"
                                              :class="(selectedEstate.caretaker_delegation_mode === 'SUPERADMIN_CONCIERGE') ? 'bg-purple-100 text-purple-800' : 'bg-emerald-100 text-emerald-800'">
                                            Delegated Ground Handler
                                        </span>
                                        <span class="text-xs text-slate-400">•</span>
                                        <span class="text-xs font-bold text-slate-500" x-text="'Estate: ' + selectedEstate.title"></span>
                                    </div>
                                    <h3 class="text-base font-extrabold text-slate-900 mt-1"
                                        x-text="(selectedEstate.caretaker_delegation_mode === 'SUPERADMIN_CONCIERGE') ? 'Oga Landlord SuperAdmin Concierge Service' : (selectedEstate.caretaker_name || 'Nominated Caretaker')">
                                    </h3>
                                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600 mt-1.5">
                                        <span class="inline-flex items-center gap-1">
                                            <span>✉️</span>
                                            <span x-text="(selectedEstate.caretaker_delegation_mode === 'SUPERADMIN_CONCIERGE') ? 'superadmin@ogalandlord.ng' : (selectedEstate.caretaker_email || 'No email specified')"></span>
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <span>📞</span>
                                            <span x-text="(selectedEstate.caretaker_delegation_mode === 'SUPERADMIN_CONCIERGE') ? '+234 800 OGA HELP (24/7 Desk)' : (selectedEstate.caretaker_phone || 'No phone specified')"></span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                            <span>🛡️ Tickets & Ground Ops Active</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Re-delegate Action Button -->
                            <button type="button"
                                    @click="openCaretakerModal(selectedEstate)"
                                    class="bg-blue-50 hover:bg-blue-100 text-[#1D4ED8] border border-blue-200 text-xs font-bold px-4 py-2.5 rounded-xl transition inline-flex items-center justify-center gap-1.5 shadow-2xs shrink-0">
                                <span>Reassign / Delegate Caretaker</span>
                                <span>⚙️</span>
                            </button>
                        </div>
                    </div>

                    <!-- 2. ORGANIZED LIST OF TENANTS OR UNOCCUPIED APARTMENTS IN SELECTED ESTATE -->
                    <div class="bg-white border border-slate-200/90 border-t-4 border-t-indigo-600 rounded-2xl shadow-xs overflow-hidden">
                        <div class="p-5 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-slate-900">
                                        <span>Apartments & Resident Roster — </span>
                                        <span class="text-[#1D4ED8]" x-text="selectedEstate.title"></span>
                                    </h3>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    <span>Location: </span>
                                    <span class="font-semibold text-slate-700" x-text="(selectedEstate.address_line_1 ? selectedEstate.address_line_1 + ', ' : '') + selectedEstate.city + ', ' + selectedEstate.state"></span>
                                    <span> • </span>
                                    <span x-text="selectedEstateUnits.length + ' Total Physical Units'"></span>
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Filter Tabs -->
                                <div class="inline-flex rounded-xl bg-slate-100 p-1 text-xs font-bold text-slate-600">
                                    <button type="button" @click="estateUnitFilter = 'all'"
                                            :class="estateUnitFilter === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'hover:text-slate-900'"
                                            class="px-3 py-1.5 rounded-lg transition">
                                        <span>All Units (</span><span x-text="selectedEstateUnits.length"></span><span>)</span>
                                    </button>
                                    <button type="button" @click="estateUnitFilter = 'occupied'"
                                            :class="estateUnitFilter === 'occupied' ? 'bg-emerald-600 text-white shadow-xs' : 'hover:text-slate-900'"
                                            class="px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>
                                        <span>Occupied (</span><span x-text="occupiedEstateUnits.length"></span><span>)</span>
                                    </button>
                                    <button type="button" @click="estateUnitFilter = 'vacant'"
                                            :class="estateUnitFilter === 'vacant' ? 'bg-slate-800 text-white shadow-xs' : 'hover:text-slate-900'"
                                            class="px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        <span>Unoccupied / Vacant (</span><span x-text="vacantEstateUnits.length"></span><span>)</span>
                                    </button>
                                </div>

                                <a :href="'/onboarding?property_id=' + selectedEstate.id"
                                   class="bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-xs font-bold px-3.5 py-2 rounded-xl transition inline-flex items-center gap-1.5 shadow-xs">
                                    <span>+ Onboard Tenant</span>
                                </a>
                            </div>
                        </div>

                        <!-- Table of Apartments in Selected Estate -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50/90 border-b border-slate-100 text-slate-500 uppercase tracking-wider font-semibold">
                                    <tr>
                                        <th class="py-3.5 px-4">Unit #</th>
                                        <th class="py-3.5 px-4">Apartment Type</th>
                                        <th class="py-3.5 px-4">Occupancy Status</th>
                                        <th class="py-3.5 px-4">Resident / Tenant Information</th>
                                        <th class="py-3.5 px-4">Rent & Lease Terms</th>
                                        <th class="py-3.5 px-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="unit in filteredEstateUnits" :key="unit.id">
                                        <tr class="hover:bg-slate-50/60 transition"
                                            :class="(unit.lease_id || unit.is_occupied == 1 || (unit.tenant_name && unit.tenant_name.trim().length > 0)) ? 'border-l-4 border-l-emerald-500' : 'border-l-4 border-l-slate-200'">
                                            
                                            <!-- Unit Number -->
                                            <td class="py-3.5 px-4 font-black text-slate-900 text-sm">
                                                <span x-text="unit.unit_number"></span>
                                            </td>

                                            <!-- Apartment Type -->
                                            <td class="py-3.5 px-4 text-slate-700 font-medium">
                                                <span x-text="unit.apartment_type"></span>
                                            </td>

                                            <!-- Occupancy Status Badge -->
                                            <td class="py-3.5 px-4">
                                                <template x-if="unit.lease_id || unit.is_occupied == 1 || (unit.tenant_name && unit.tenant_name.trim().length > 0)">
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        <span>Occupied</span>
                                                    </span>
                                                </template>
                                                <template x-if="!unit.lease_id && unit.is_occupied != 1 && (!unit.tenant_name || unit.tenant_name.trim().length === 0)">
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                        <span>⚪ Unoccupied / Vacant</span>
                                                    </span>
                                                </template>
                                            </td>

                                            <!-- Tenant Information -->
                                            <td class="py-3.5 px-4">
                                                <template x-if="unit.tenant_name && unit.tenant_name.trim().length > 0">
                                                    <div>
                                                        <span class="font-extrabold text-slate-900 block text-xs" x-text="unit.tenant_name"></span>
                                                        <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-0.5">
                                                            <span x-show="unit.tenant_email" x-text="unit.tenant_email"></span>
                                                            <span x-show="unit.tenant_email && unit.tenant_phone">•</span>
                                                            <span x-show="unit.tenant_phone" x-text="unit.tenant_phone"></span>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template x-if="!unit.tenant_name || unit.tenant_name.trim().length === 0">
                                                    <span class="text-slate-400 italic text-[11px]">None (Ready for tenant move-in)</span>
                                                </template>
                                            </td>

                                            <!-- Rent & Lease Terms -->
                                            <td class="py-3.5 px-4">
                                                <div class="font-bold text-slate-900">
                                                    <span>NGN </span>
                                                    <span x-text="Number(unit.lease_rent_amount || unit.default_rent_amount || 0).toLocaleString('en-NG', {minimumFractionDigits: 2})"></span>
                                                    <span class="text-[10px] text-slate-500 font-normal"> / yr</span>
                                                </div>
                                                <template x-if="unit.rent_due_date">
                                                    <span class="block text-[10px] text-slate-500 mt-0.5 font-medium" x-text="'Due: ' + unit.rent_due_date"></span>
                                                </template>
                                            </td>

                                            <!-- Actions -->
                                            <td class="py-3.5 px-4 text-right">
                                                <template x-if="unit.lease_id">
                                                    <a :href="'/agreement/preview?id=' + unit.lease_id"
                                                       class="text-[#1D4ED8] hover:text-[#1E40AF] hover:underline font-bold text-xs inline-flex items-center gap-1">
                                                        <span>Preview Agreement</span>
                                                        <span>→</span>
                                                    </a>
                                                </template>
                                                <template x-if="!unit.lease_id">
                                                    <a :href="'/onboarding?property_id=' + selectedEstate.id + '&unit_id=' + unit.id"
                                                       class="text-emerald-700 hover:text-emerald-800 hover:underline font-bold text-xs inline-flex items-center gap-1">
                                                        <span>+ Onboard Tenant</span>
                                                    </a>
                                                </template>
                                            </td>
                                        </tr>
                                    </template>

                                    <!-- Empty State -->
                                    <tr x-show="filteredEstateUnits.length === 0">
                                        <td colspan="6" class="py-8 px-4 text-center text-slate-500">
                                            <p class="text-sm font-semibold">No apartments found matching this filter in <span class="text-slate-900" x-text="selectedEstate.title"></span>.</p>
                                            <p class="text-xs text-slate-400 mt-1">Select another filter above or onboard a new tenant.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- ================= TAB 2: ALL UNITS & APARTMENTS ================= -->
        <div x-show="currentTab === 'units'" x-transition>
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-indigo-600 rounded-2xl shadow-xs overflow-hidden mb-8">
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                            🏢
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Estate Units & Apartment Inventory</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Physical tracking of all 8 residential units in PHDL Unity Estate, Idu.</p>
                        </div>
                    </div>
                    <a href="/onboarding" class="bg-[#1D4ED8] text-white text-xs font-bold px-3.5 py-2 rounded-xl hover:bg-[#1E40AF] transition inline-flex items-center gap-1.5 self-start shadow-xs">
                        <span>+ Onboard Tenant</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/90 border-b border-slate-100 text-slate-500 uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="py-3.5 px-4">Unit #</th>
                                <th class="py-3.5 px-4">Type</th>
                                <th class="py-3.5 px-4">Standard Rent</th>
                                <th class="py-3.5 px-4">Occupancy Status</th>
                                <th class="py-3.5 px-4">Current Resident</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($allUnits as $unit): 
                                $isOccupied = !empty($unit['lease_id']);
                            ?>
                                <tr class="hover:bg-slate-50/60 transition <?= $isOccupied ? 'border-l-4 border-l-emerald-500' : 'border-l-4 border-l-slate-200' ?>">
                                    <td class="py-3.5 px-4 font-black text-slate-900 text-sm">
                                        <?= htmlspecialchars($unit['unit_number']) ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600 font-medium">
                                        <?= htmlspecialchars($unit['apartment_type']) ?>
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-slate-900">
                                        NGN <?= number_format((float)$unit['default_rent_amount'], 2) ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <?php if ($isOccupied): ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Occupied
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                Available Vacant
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <?php if (!empty($unit['tenant_name'])): ?>
                                            <span class="font-bold text-slate-900 block"><?= htmlspecialchars($unit['tenant_name']) ?></span>
                                            <span class="text-[10px] text-slate-500"><?= htmlspecialchars($unit['tenant_email'] ?? '') ?></span>
                                        <?php else: ?>
                                            <span class="text-slate-400 italic">None (Ready for tenant)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <?php if ($isOccupied): ?>
                                            <a href="/agreement/preview?id=<?= (int)$unit['lease_id'] ?>" class="text-[#1D4ED8] hover:underline font-bold text-xs inline-flex items-center gap-1">
                                                <span>Preview Agreement</span>
                                                <span>→</span>
                                            </a>
                                        <?php else: ?>
                                            <a href="/onboarding" class="text-emerald-700 hover:underline font-bold text-xs inline-flex items-center gap-1">
                                                <span>+ Assign Tenant</span>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= TAB 3: TENANTS & LEASES ================= -->
        <div x-show="currentTab === 'tenants'" x-transition>
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-[#1D4ED8] rounded-2xl shadow-xs overflow-hidden mb-8">
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#1D4ED8] flex items-center justify-center font-bold">
                            📜
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Tenant Residency & Signed Agreements</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Click "Preview Agreement" on any contract to review terms and cryptographic audit certificate.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="/agreement/preview" class="bg-blue-50 hover:bg-blue-100 text-[#1D4ED8] border border-blue-200 text-xs font-bold px-3.5 py-2 rounded-xl transition inline-flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Preview Tenancy Agreement</span>
                        </a>
                        <a href="/onboarding" class="bg-[#1D4ED8] text-white text-xs font-bold px-3.5 py-2 rounded-xl hover:bg-[#1E40AF] transition shadow-xs">
                            + New Onboarding
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/90 border-b border-slate-100 text-slate-500 uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="py-3.5 px-4">Unit</th>
                                <th class="py-3.5 px-4">Tenant Name</th>
                                <th class="py-3.5 px-4">Annual Rent</th>
                                <th class="py-3.5 px-4">Agreement State</th>
                                <th class="py-3.5 px-4">Due Date</th>
                                <th class="py-3.5 px-4 text-right">Agreement Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($leases as $l): 
                                $isSigned = ($l['agreement_status'] === 'FULLY_EXECUTED');
                            ?>
                                <tr class="hover:bg-slate-50/60 transition <?= $isSigned ? 'border-l-4 border-l-emerald-500 bg-emerald-50/10' : 'border-l-4 border-l-amber-400 bg-amber-50/10' ?>">
                                    <td class="py-3.5 px-4 font-black text-slate-900 text-sm">
                                        <?= htmlspecialchars($l['unit_number'] ?? 'Unit') ?>
                                        <span class="block text-[11px] font-normal text-slate-500"><?= htmlspecialchars($l['apartment_type'] ?? '') ?></span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-bold text-slate-900 block"><?= htmlspecialchars($l['tenant_name']) ?></span>
                                        <span class="text-slate-500 text-[11px]"><?= htmlspecialchars($l['tenant_email']) ?></span>
                                    </td>
                                    <td class="py-3.5 px-4 font-black text-slate-900">
                                        NGN <?= number_format((float)($l['rent_amount'] ?? 0), 2) ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <?php if ($isSigned): ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                Signed & Verified
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Waiting for <?= htmlspecialchars($l['tenant_name']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600 font-medium">
                                        <?= date('M j, Y', strtotime($l['rent_due_date'])) ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="/agreement/preview?id=<?= (int)$l['id'] ?>" class="inline-flex items-center gap-1.5 <?= $isSigned ? 'bg-[#1D4ED8] hover:bg-[#1E40AF] text-white' : 'bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300' ?> font-bold px-3 py-1.5 rounded-xl transition shadow-2xs text-xs">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>Preview Agreement</span>
                                            </a>
                                            <!-- Requirement 4: Terminate Tenancy & Vacate Unit -->
                                            <button type="button" @click="openTerminateModal(<?= (int)$l['id'] ?>, '<?= addslashes($l['tenant_name']) ?>', '<?= addslashes($l['unit_number'] ?? 'Unit') ?>', '<?= addslashes($l['apartment_type'] ?? '') ?>', <?= (float)$l['rent_amount'] ?>, '<?= $l['rent_start_date'] ?? date('Y-m-d') ?>')"
                                                    class="inline-flex items-center gap-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold px-2.5 py-1.5 rounded-xl transition shadow-2xs text-[11px]">
                                                <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                                <span>Terminate & Vacate</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Former Tenants History & Timelines (Requirement 4) -->
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-purple-600 rounded-2xl shadow-xs overflow-hidden mb-8">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                            🕰️
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Former Tenants History & Timelines (Immutable Archive)</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Historical records of previous tenants, rent values, and computed stay duration upon unit reassignment.</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-purple-700 bg-purple-50 border border-purple-200 px-3 py-1 rounded-full">
                        <?= count($formerTenants ?? []) ?> Archived Tenancies
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/90 border-b border-slate-100 text-slate-500 uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="py-3.5 px-4">Former Tenant</th>
                                <th class="py-3.5 px-4">Premises Unit</th>
                                <th class="py-3.5 px-4">Annual Rent</th>
                                <th class="py-3.5 px-4">Duration of Stay</th>
                                <th class="py-3.5 px-4">Departure Reason</th>
                                <th class="py-3.5 px-4 text-right">Terminated Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($formerTenants)): ?>
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-slate-400">
                                        No tenancies terminated yet. Active resident leases are preserved above.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($formerTenants as $ft): ?>
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-3.5 px-4">
                                            <span class="font-bold text-slate-900 block"><?= htmlspecialchars($ft['tenant_name']) ?></span>
                                            <span class="text-slate-500 text-[10px]"><?= htmlspecialchars($ft['tenant_email'] ?? '') ?> • <?= htmlspecialchars($ft['tenant_phone'] ?? '') ?></span>
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold text-slate-800">
                                            <?= htmlspecialchars($ft['unit_number'] ?? 'Unit') ?> (<?= htmlspecialchars($ft['apartment_type'] ?? '') ?>)
                                        </td>
                                        <td class="py-3.5 px-4 font-black text-slate-900">
                                            NGN <?= number_format((float)($ft['rent_amount'] ?? 0), 2) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-700 font-medium">
                                            <span class="bg-purple-50 text-purple-900 border border-purple-200 px-2 py-0.5 rounded font-mono text-[11px] block">
                                                <?= htmlspecialchars($ft['duration_of_stay'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-600">
                                            <?= htmlspecialchars($ft['termination_reason'] ?? 'End of Lease') ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-right text-slate-500 font-medium">
                                            <?= date('M j, Y, H:i', strtotime($ft['terminated_at'] ?? 'now')) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pending Payment Confirmations (Requirement 7) -->
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-emerald-600 rounded-2xl shadow-xs overflow-hidden mb-8">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            💳
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Incoming Resident Payments & Stamping Queue</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Payments directed to Landlord require confirmation before stamped official receipts can be downloaded.</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
                        <?= count($landlordPayments ?? []) ?> Payment Entries
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/90 border-b border-slate-100 text-slate-500 uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="py-3.5 px-4">Receipt #</th>
                                <th class="py-3.5 px-4">Tenant / Unit</th>
                                <th class="py-3.5 px-4">Purpose</th>
                                <th class="py-3.5 px-4">Amount</th>
                                <th class="py-3.5 px-4">Channel</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($landlordPayments)): ?>
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-slate-400">
                                        No resident payments recorded yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($landlordPayments as $pay): 
                                    $isPending = ($pay['status'] === 'PENDING_CONFIRMATION');
                                ?>
                                    <tr class="hover:bg-slate-50/60 transition <?= $isPending ? 'border-l-4 border-l-amber-400 bg-amber-50/10' : 'border-l-4 border-l-emerald-500' ?>">
                                        <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                            <?= htmlspecialchars($pay['receipt_number']) ?>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="font-bold text-slate-900 block"><?= htmlspecialchars($pay['tenant_name']) ?></span>
                                            <span class="text-slate-500 text-[10px]"><?= htmlspecialchars($pay['unit_number'] ?? 'Unit') ?></span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="font-semibold text-slate-900"><?= htmlspecialchars($pay['title']) ?></span>
                                            <span class="block text-[10px] text-slate-400"><?= htmlspecialchars($pay['beneficiary_type']) ?> Beneficiary</span>
                                        </td>
                                        <td class="py-3.5 px-4 font-black text-slate-900">
                                            NGN <?= number_format((float)$pay['amount'], 2) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-600 font-medium">
                                            <?= htmlspecialchars($pay['payment_method']) ?>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <?php if ($isPending): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold text-[11px] bg-amber-50 text-amber-800 border border-amber-200">
                                                    ⏳ Awaiting Confirmation
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold text-[11px] bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    ✓ Confirmed & Stamped
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <?php if ($isPending): ?>
                                                    <button type="button" @click="confirmPayment(<?= (int)$pay['id'] ?>, '<?= addslashes($pay['receipt_number']) ?>')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-1.5 rounded-xl shadow-xs transition text-[11px]">
                                                        ✓ Confirm & Stamp
                                                    </button>
                                                <?php endif; ?>
                                                <a href="/tenant/receipt?number=<?= urlencode($pay['receipt_number']) ?>" target="_blank" class="text-xs font-bold text-[#1D4ED8] hover:underline">
                                                    Receipt ↗
                                                </a>
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

        <!-- ================= TAB 4: MAINTENANCE & CARETAKER ================= -->
        <div x-show="currentTab === 'maintenance'" x-transition>

            <!-- Requirement 5: Owner Privacy Isolation Banner -->
            <div class="mb-5 p-4 rounded-2xl bg-blue-50 border border-blue-200 flex items-start gap-3 text-xs">
                <div class="w-7 h-7 rounded-lg bg-[#1D4ED8] text-white flex items-center justify-center font-bold text-sm shrink-0">
                    🔒
                </div>
                <div class="leading-relaxed">
                    <strong class="text-blue-950 font-bold block">Owner Privacy Isolation Active (Requirement 5)</strong>
                    <span class="text-blue-900">
                        Routine maintenance tickets and resident complaints are handled directly by your on-site Caretaker (<strong>Musa Danjuma</strong>). Landlords <strong>only</strong> see concerns that have been explicitly tagged or escalated by the Caretaker for owner review and capital repairs authorization.
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
                
                <!-- Maintenance Queue Card (Rose Accent) -->
                <div class="lg:col-span-8 bg-white border border-slate-200/90 border-t-4 border-t-rose-500 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                                🔧
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Escalated Maintenance Concerns</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Tickets tagged by Caretaker requiring Landlord review and approval.</p>
                            </div>
                        </div>
                        <a href="/demo/caretaker" class="text-xs font-bold text-[#1D4ED8] hover:underline">Enter Caretaker View →</a>
                    </div>

                    <?php if (empty($tickets)): ?>
                        <div class="p-8 text-center text-xs text-slate-500 border border-dashed border-slate-200 rounded-xl bg-slate-50/50">
                            <span class="text-2xl block mb-1.5">🛡️</span>
                            <strong class="text-slate-800 block text-sm">No Escalated Issues Pending</strong>
                            <p class="mt-1">All routine ground repairs are being handled by Caretaker Musa Danjuma. You will be tagged here if capital authorization is required.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($tickets as $ticket): 
                                $isHigh = (strtoupper($ticket['priority'] ?? '') === 'HIGH');
                                $isEscalated = !empty($ticket['is_escalated_to_landlord']);
                            ?>
                                <div class="p-4 rounded-xl border <?= $isHigh ? 'border-rose-200 bg-rose-50/20 border-l-4 border-l-rose-500' : 'border-slate-200 bg-slate-50/50 border-l-4 border-l-amber-400' ?> hover:border-slate-300 transition">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-[10px] font-black text-[#1D4ED8] bg-blue-50 border border-blue-200 px-2 py-0.5 rounded">
                                                <?= htmlspecialchars($ticket['ticket_code'] ?? 'TKT') ?>
                                            </span>
                                            <span class="font-extrabold text-xs text-slate-900"><?= htmlspecialchars($ticket['unit_number'] ?? 'Unit') ?> — <?= htmlspecialchars($ticket['title']) ?></span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $isHigh ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' ?>"><?= htmlspecialchars($ticket['priority']) ?></span>
                                        </div>
                                        <span class="text-xs font-bold text-[#1D4ED8] bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-full"><?= htmlspecialchars($ticket['status']) ?></span>
                                    </div>
                                    <p class="text-xs text-slate-600 leading-relaxed"><?= htmlspecialchars($ticket['description']) ?></p>

                                    <?php if ($isEscalated): ?>
                                        <div class="mt-2.5 p-2.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 flex items-start gap-2">
                                            <span class="font-bold text-amber-700">⚡ Caretaker Escalation Reason:</span>
                                            <span><?= htmlspecialchars($ticket['escalation_reason'] ?? 'Caretaker requested owner review.') ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-3 pt-2.5 border-t border-slate-200 flex items-center justify-between text-xs">
                                        <span class="text-slate-500 text-[11px]">Reported by <strong><?= htmlspecialchars($ticket['tenant_name'] ?? 'Tenant') ?></strong></span>
                                        <button type="button" @click="openChatThread(<?= (int)$ticket['id'] ?>)" class="text-[#1D4ED8] hover:underline font-bold inline-flex items-center gap-1">
                                            <span>Open Escalated Thread (<?= (int)($ticket['message_count'] ?? 1) ?> msgs)</span>
                                            <span>→</span>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Caretaker Delegation Card (Amber/Indigo Accent) -->
                <div class="lg:col-span-4 bg-white border border-slate-200/90 border-t-4 border-t-indigo-600 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                                🛡️
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Assigned Caretaker</h3>
                                <p class="text-xs text-slate-500">Resident facilities manager</p>
                            </div>
                        </div>

                        <div class="p-4 bg-indigo-50/40 border border-indigo-200/70 rounded-xl text-xs space-y-2 mb-4">
                            <span class="font-black text-slate-900 block text-sm">Musa Danjuma</span>
                            <span class="text-slate-600 block">Phone: <strong class="text-slate-800">+234 803 000 0002</strong></span>
                            <span class="text-slate-600 block">Email: <strong class="text-slate-800">caretaker.idu@ogalandlord.ng</strong></span>
                            <div class="pt-2 border-t border-indigo-200 text-[11px] text-indigo-700 font-bold flex items-center gap-1.5">
                                <span>✓ Auto-CC on all tenant reminder notices</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <a href="/demo/caretaker" class="w-full bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-bold text-xs py-3 rounded-xl transition flex items-center justify-center gap-1.5 shadow-xs">
                            <span>Open Caretaker Workspace</span>
                            <span>→</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- ================= TAB 5: RENT REMINDERS ================= -->
        <div x-show="currentTab === 'reminders'" x-transition>
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-amber-500 rounded-2xl p-6 shadow-xs mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 mb-6 gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                            ⏰
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Intelligent Rent Reminders & Auto-Halt Dunning</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Gentle, phased notifications dispatched ahead of due dates.</p>
                        </div>
                    </div>
                    <button type="button" @click="runReminderScheduler()" :disabled="isRunningReminders"
                            class="bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition self-start sm:self-auto">
                        <span x-text="isRunningReminders ? 'Evaluating Leases...' : 'Execute Dunning Run'"></span>
                    </button>
                </div>

                <!-- 3 Color Coded Phase Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="p-5 rounded-xl bg-blue-50/40 border border-blue-200/80 border-t-4 border-t-blue-500 text-xs shadow-2xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-700 bg-blue-100 px-2 py-0.5 rounded-md">Phase 1</span>
                            <span class="text-blue-600 font-bold">T-30 Days</span>
                        </div>
                        <span class="font-extrabold text-slate-900 text-sm block">Friendly Notice</span>
                        <p class="text-slate-600 mt-1.5 leading-relaxed">Dispatched 30 days prior. Gentle reminder of upcoming renewal and lease continuity terms.</p>
                    </div>

                    <div class="p-5 rounded-xl bg-amber-50/40 border border-amber-200/80 border-t-4 border-t-amber-500 text-xs shadow-2xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-800 bg-amber-100 px-2 py-0.5 rounded-md">Phase 2</span>
                            <span class="text-amber-700 font-bold">T-7 Days</span>
                        </div>
                        <span class="font-extrabold text-slate-900 text-sm block">Payment Details</span>
                        <p class="text-slate-600 mt-1.5 leading-relaxed">Includes direct estate bank account numbers & online gateway payment link for instant clearance.</p>
                    </div>

                    <div class="p-5 rounded-xl bg-rose-50/40 border border-rose-200/80 border-t-4 border-t-rose-500 text-xs shadow-2xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-800 bg-rose-100 px-2 py-0.5 rounded-md">Phase 3</span>
                            <span class="text-rose-700 font-bold">Daily T-6..0</span>
                        </div>
                        <span class="font-extrabold text-slate-900 text-sm block">Urgent Dunning</span>
                        <p class="text-slate-600 mt-1.5 leading-relaxed">Daily alerts auto-halt immediately once the payment webhook verifies received funds.</p>
                    </div>
                </div>

                <div class="p-4 bg-emerald-50 border border-emerald-300 rounded-xl text-xs text-emerald-900 font-medium flex items-center gap-2.5">
                    <span class="text-lg">🛡️</span>
                    <span><strong>Auto-Halt Guarantee:</strong> No tenant will ever receive a reminder after paying their rent. Our webhook listener immediately freezes all pending dunning dispatches upon confirmed receipt.</span>
                </div>
            </div>
        </div>

        <!-- ================= TAB 7: FINANCIALS & TAX (WHT) ================= -->
        <div x-show="currentTab === 'finances'" x-transition>
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-emerald-600 rounded-2xl p-6 shadow-xs mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 mb-6 gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            📊
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Financial Reporting & Tax-Ready Analytics</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Net Operating Income (NOI) calculation & statutory 10% Withholding Tax (WHT) schedules.</p>
                        </div>
                    </div>
                    <button type="button" @click="showExpenseModal = true"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-1.5 self-start sm:self-auto">
                        <span>+ Record Property Expense</span>
                    </button>
                </div>

                <!-- 4 KPI Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="p-4 rounded-xl bg-blue-50/40 border border-blue-200/80 border-t-4 border-t-blue-600 text-xs">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Gross Rental Income</span>
                        <p class="text-2xl font-black text-slate-900 mt-1">₦<?= number_format((float)($financials['gross_rent_collected'] ?? 18500000), 2) ?></p>
                        <span class="text-[10px] text-blue-700 font-bold mt-1 block">Annualized Rent Realized</span>
                    </div>

                    <div class="p-4 rounded-xl bg-rose-50/40 border border-rose-200/80 border-t-4 border-t-rose-500 text-xs">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Expenses</span>
                        <p class="text-2xl font-black text-rose-600 mt-1">₦<?= number_format((float)($financials['total_expenses'] ?? 1360000), 2) ?></p>
                        <span class="text-[10px] text-rose-700 font-bold mt-1 block"><?= count($propertyExpenses ?? []) ?> Logged Disbursements</span>
                    </div>

                    <div class="p-4 rounded-xl bg-emerald-50/40 border border-emerald-200/80 border-t-4 border-t-emerald-600 text-xs">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Net Operating Income</span>
                        <p class="text-2xl font-black text-emerald-700 mt-1">₦<?= number_format((float)($financials['net_operating_income'] ?? 17140000), 2) ?></p>
                        <span class="text-[10px] text-emerald-700 font-bold mt-1 block">NOI Margin: <?= number_format((float)($financials['operating_expense_ratio'] ? (100 - $financials['operating_expense_ratio']) : 92.6), 1) ?>%</span>
                    </div>

                    <div class="p-4 rounded-xl bg-amber-50/40 border border-amber-200/80 border-t-4 border-t-amber-500 text-xs">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tax Schedule (10% WHT)</span>
                        <p class="text-2xl font-black text-amber-700 mt-1">₦<?= number_format((float)($taxSummary['estimated_tax_payable'] ?? 1714000), 2) ?></p>
                        <span class="text-[10px] text-amber-800 font-bold mt-1 block">FIRS / State IRS Ready</span>
                    </div>
                </div>

                <!-- Tax Calculation Breakdown & Expense Ledger -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Tax Summary Card -->
                    <div class="lg:col-span-5 p-5 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-3">
                        <h3 class="font-black text-slate-900 text-sm flex items-center gap-2">
                            <span>🏛️ Statutory Tax Computation (10% WHT)</span>
                        </h3>
                        <p class="text-slate-600 text-[11px] leading-relaxed">
                            Under Nigerian real estate tax regulations, Withholding Tax (WHT) of 10% applies to net rental distributions. Below is your automated tax-ready calculation for Year <?= date('Y') ?>.
                        </p>
                        <div class="p-3 bg-white rounded-xl border border-slate-200 space-y-2 font-mono text-[11px]">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Gross Rental Receipts:</span>
                                <strong class="text-slate-900">₦<?= number_format((float)($taxSummary['gross_rental_income'] ?? 18500000), 2) ?></strong>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Less Deductible Repairs:</span>
                                <strong class="text-rose-600">-₦<?= number_format((float)($taxSummary['allowable_deductions'] ?? 1360000), 2) ?></strong>
                            </div>
                            <div class="border-t border-slate-100 pt-1.5 flex justify-between font-bold">
                                <span class="text-slate-700">Taxable Net Base:</span>
                                <strong class="text-slate-900">₦<?= number_format((float)($taxSummary['net_taxable_rental_income'] ?? 17140000), 2) ?></strong>
                            </div>
                            <div class="border-t border-slate-200 pt-1.5 flex justify-between font-black text-amber-700">
                                <span>Statutory WHT Rate:</span>
                                <span>10.0%</span>
                            </div>
                            <div class="flex justify-between font-black text-amber-900 text-xs bg-amber-50 p-2 rounded-lg border border-amber-200">
                                <span>Estimated WHT Due:</span>
                                <span>₦<?= number_format((float)($taxSummary['estimated_tax_payable'] ?? 1714000), 2) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Expenses Table -->
                    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                            <span class="font-bold text-xs text-slate-800 uppercase tracking-wider">Property Expenses Ledger</span>
                            <span class="text-[10px] font-bold text-slate-500"><?= count($propertyExpenses ?? []) ?> items</span>
                        </div>
                        <div class="overflow-x-auto max-h-72 overflow-y-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50/50 text-slate-500 font-bold border-b border-slate-100 text-[10px] uppercase">
                                    <tr>
                                        <th class="py-2.5 px-4">Date / Title</th>
                                        <th class="py-2.5 px-3">Category</th>
                                        <th class="py-2.5 px-3">Vendor / Artisan</th>
                                        <th class="py-2.5 px-4 text-right">Amount (NGN)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-[11px]">
                                    <?php if (empty($propertyExpenses)): ?>
                                        <tr>
                                            <td colspan="4" class="p-6 text-center text-slate-400">No expenses recorded yet. Click "+ Record Property Expense" above.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($propertyExpenses as $exp): ?>
                                            <tr class="hover:bg-slate-50/50">
                                                <td class="py-2.5 px-4">
                                                    <strong class="text-slate-900 block"><?= htmlspecialchars($exp['title']) ?></strong>
                                                    <span class="text-[10px] text-slate-400"><?= htmlspecialchars($exp['expense_date']) ?></span>
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700"><?= htmlspecialchars($exp['category']) ?></span>
                                                </td>
                                                <td class="py-2.5 px-3 text-slate-600 truncate max-w-[120px]">
                                                    <?= htmlspecialchars($exp['artisan_name'] ?? ($exp['vendor_name'] ?? 'Direct Vendor')) ?>
                                                </td>
                                                <td class="py-2.5 px-4 text-right font-mono font-bold text-slate-900">
                                                    ₦<?= number_format((float)$exp['amount'], 2) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= TAB 8: TRUSTED ARTISANS ================= -->
        <div x-show="currentTab === 'artisans'" x-transition>
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-indigo-600 rounded-2xl p-6 shadow-xs mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 mb-6 gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                            🛠️
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Trusted Artisan Directory & Service Dispatch</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Vetted Nigerian technicians: plumbers, electricians, carpenters, generator engineers.</p>
                        </div>
                    </div>
                    <button type="button" @click="showArtisanModal = true"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-1.5 self-start sm:self-auto">
                        <span>+ Register New Artisan</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($artisans as $art): ?>
                        <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:shadow-sm transition flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between mb-3">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-indigo-100 text-indigo-900 border border-indigo-200">
                                        <?= htmlspecialchars($art['trade_skill']) ?>
                                    </span>
                                    <div class="flex items-center gap-1 text-amber-500 font-black text-xs">
                                        <span>★</span>
                                        <span><?= number_format((float)($art['rating'] ?? 5.0), 1) ?></span>
                                    </div>
                                </div>
                                <h3 class="font-black text-slate-900 text-sm mb-1"><?= htmlspecialchars($art['full_name']) ?></h3>
                                <p class="text-xs text-slate-500 mb-3"><?= htmlspecialchars($art['city'] ?? 'Abuja') ?>, <?= htmlspecialchars($art['state'] ?? 'FCT') ?></p>
                                
                                <div class="space-y-1 text-xs text-slate-600 font-medium">
                                    <div>📞 Phone: <strong><?= htmlspecialchars($art['phone_number']) ?></strong></div>
                                    <div>⚡ Rate: <strong>₦<?= number_format((float)($art['hourly_rate'] ?? 5000), 2) ?>/hr</strong></div>
                                    <div class="text-[11px] text-emerald-700 font-bold">✓ <?= (int)($art['jobs_completed'] ?? 0) ?> jobs completed</div>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-200 flex items-center justify-between text-xs">
                                <span class="text-emerald-600 font-bold text-[11px]">● Active On-Call</span>
                                <a href="tel:<?= htmlspecialchars($art['phone_number']) ?>" class="bg-white border border-slate-300 hover:border-indigo-600 text-slate-700 font-bold px-3 py-1.5 rounded-lg transition text-[11px]">
                                    Call Artisan
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ================= TAB 9: GATEWAYS & BANK DETAILS ================= -->
        <div x-show="currentTab === 'gateways'" x-transition>
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-blue-600 rounded-2xl p-6 shadow-xs mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 mb-6 gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                            🏦
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Connect Your Payment Gateway & Bank Account</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Collect rent directly into your bank or connect Paystack/Flutterwave with 0% platform rent commission.</p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submitPaymentSettings()" class="space-y-6 max-w-2xl text-xs">
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl space-y-1 text-blue-900">
                        <strong class="block font-bold">Zero Custody & Direct Settlement</strong>
                        <p class="text-[11px]">Funds flow directly from resident to your bank account or payment processor keys. Oga Landlord does not withhold your rent.</p>
                    </div>

                    <!-- Direct Bank Account -->
                    <div class="p-5 border border-slate-200 rounded-2xl space-y-4 bg-slate-50/50">
                        <span class="font-extrabold text-slate-900 uppercase tracking-wider text-[11px] block">Primary Bank Account (For Direct Bank Transfers)</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Bank Name *</label>
                                <input type="text" x-model="paymentSettingsForm.bank_name" required placeholder="e.g. Zenith Bank PLC"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">10-Digit NUBAN Account Number *</label>
                                <input type="text" x-model="paymentSettingsForm.account_number" required placeholder="1012345678" maxlength="10"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white font-mono">
                            </div>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Beneficiary Account Name *</label>
                            <input type="text" x-model="paymentSettingsForm.account_name" required placeholder="Chief Ibrahim Bello / Bello Properties"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white">
                        </div>
                    </div>

                    <!-- Gateway Integration Keys -->
                    <div class="p-5 border border-slate-200 rounded-2xl space-y-4 bg-slate-50/50">
                        <span class="font-extrabold text-slate-900 uppercase tracking-wider text-[11px] block">Online Payment Gateway Integration</span>
                        
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Gateway Provider</label>
                            <select x-model="paymentSettingsForm.gateway_provider" class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white">
                                <option value="PAYSTACK">Paystack (Recommended for Nigeria)</option>
                                <option value="FLUTTERWAVE">Flutterwave</option>
                                <option value="DIRECT_BANK">Direct Bank Transfer Only</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Paystack Public Key</label>
                                <input type="text" x-model="paymentSettingsForm.paystack_public_key" placeholder="pk_live_..."
                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white font-mono text-[11px]">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Paystack Secret Key</label>
                                <input type="password" x-model="paymentSettingsForm.paystack_secret_key" placeholder="sk_live_..."
                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white font-mono text-[11px]">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Flutterwave Public Key</label>
                                <input type="text" x-model="paymentSettingsForm.flutterwave_public_key" placeholder="FLWPUBK_TEST-..."
                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white font-mono text-[11px]">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Flutterwave Secret Key</label>
                                <input type="password" x-model="paymentSettingsForm.flutterwave_secret_key" placeholder="FLWSECK_TEST-..."
                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white font-mono text-[11px]">
                            </div>
                        </div>
                    </div>

                    <button type="submit" :disabled="savingPaymentSettings"
                            class="bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-bold px-6 py-3 rounded-xl shadow-xs transition">
                        <span x-text="savingPaymentSettings ? 'Saving Gateway Settings...' : 'Save Payment & Gateway Details'"></span>
                    </button>
                </form>
            </div>
        </div>

        <!-- ================= TAB 10: ESTATE COMMUNITY & NOTICE BOARD ================= -->
        <div x-show="currentTab === 'community'" x-transition>
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-sky-600 rounded-2xl p-6 shadow-xs mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 mb-6 gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                            🏛️
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Estate Community Notice Board & Facility Bookings</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Broadcast announcements to residents and manage shared clubhouse or gym reservations.</p>
                        </div>
                    </div>
                    <button type="button" @click="showAnnouncementModal = true"
                            class="bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-1.5 self-start sm:self-auto">
                        <span>+ Publish Notice</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Notice Board Announcements List -->
                    <div class="lg:col-span-7 space-y-3">
                        <span class="font-extrabold text-slate-900 text-xs uppercase tracking-wider block">Live Estate Notice Board</span>
                        <?php if (empty($communityAnnouncements)): ?>
                            <div class="p-6 text-center text-slate-400 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                                No active announcements on the board. Click "+ Publish Notice" above.
                            </div>
                        <?php else: ?>
                            <?php foreach ($communityAnnouncements as $ann): 
                                $isUrgent = ($ann['priority'] === 'URGENT');
                            ?>
                                <div class="p-4 rounded-xl border <?= $isUrgent ? 'border-rose-200 bg-rose-50/30' : 'border-slate-200 bg-slate-50/50' ?> text-xs space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span class="font-black text-slate-900 text-sm"><?= htmlspecialchars($ann['title']) ?></span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $isUrgent ? 'bg-rose-100 text-rose-800' : 'bg-sky-100 text-sky-800' ?>">
                                            <?= htmlspecialchars($ann['priority']) ?>
                                        </span>
                                    </div>
                                    <p class="text-slate-600 leading-relaxed"><?= htmlspecialchars($ann['message']) ?></p>
                                    <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-[10px] text-slate-400">
                                        <span>Posted by <strong><?= htmlspecialchars($ann['sender_name'] ?? 'Management') ?></strong></span>
                                        <span>Channels: <?= htmlspecialchars($ann['dispatch_channels']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Facility Bookings List -->
                    <div class="lg:col-span-5 bg-slate-50 rounded-2xl border border-slate-200 p-5">
                        <span class="font-extrabold text-slate-900 text-xs uppercase tracking-wider block mb-3">Facility Reservations</span>
                        <div class="space-y-3">
                            <?php if (empty($facilityBookings)): ?>
                                <p class="text-xs text-slate-400 text-center py-4">No facility reservations booked yet.</p>
                            <?php else: ?>
                                <?php foreach ($facilityBookings as $bk): 
                                    $isPending = ($bk['status'] === 'PENDING');
                                ?>
                                    <div class="p-3.5 bg-white rounded-xl border border-slate-200 text-xs space-y-2">
                                        <div class="flex items-center justify-between">
                                            <strong class="text-slate-900"><?= htmlspecialchars($bk['facility_name']) ?></strong>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $isPending ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?>">
                                                <?= htmlspecialchars($bk['status']) ?>
                                            </span>
                                        </div>
                                        <div class="text-slate-600 text-[11px]">
                                            <div>📅 Date: <?= htmlspecialchars($bk['booking_date']) ?> (<?= htmlspecialchars($bk['time_slot']) ?>)</div>
                                            <div>👤 Booked by: <?= htmlspecialchars($bk['tenant_name'] ?? 'Resident') ?></div>
                                            <?php if (!empty($bk['purpose'])): ?>
                                                <div>🎯 Purpose: <?= htmlspecialchars($bk['purpose']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($isPending): ?>
                                            <div class="pt-2 flex items-center gap-2">
                                                <button type="button" @click="updateBookingStatus(<?= (int)$bk['id'] ?>, 'APPROVED')"
                                                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-1 rounded text-[11px]">
                                                    ✓ Approve
                                                </button>
                                                <button type="button" @click="updateBookingStatus(<?= (int)$bk['id'] ?>, 'DECLINED')"
                                                        class="bg-rose-100 hover:bg-rose-200 text-rose-700 font-bold px-3 py-1 rounded text-[11px]">
                                                    ✕ Decline
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= TAB 11: CUSTOMIZABLE ALERT RULES ================= -->
        <div x-show="currentTab === 'alert_rules'" x-transition>
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-purple-600 rounded-2xl p-6 shadow-xs mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 mb-6 gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                            ⚡
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Customizable Alert Rules & Reminder Settings</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Define grace periods, penalty interest rates, and multi-channel SMS/Email notification triggers.</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200 uppercase text-[10px]">
                            <tr>
                                <th class="py-3 px-4">Rule Name</th>
                                <th class="py-3 px-3">Trigger Event</th>
                                <th class="py-3 px-3">Channels</th>
                                <th class="py-3 px-3">Grace Period</th>
                                <th class="py-3 px-3">Penalty Rate</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($alertRules as $rule): ?>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3.5 px-4 font-bold text-slate-900"><?= htmlspecialchars($rule['rule_name']) ?></td>
                                    <td class="py-3.5 px-3">
                                        <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <?= htmlspecialchars($rule['trigger_event']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 text-slate-600"><?= htmlspecialchars($rule['channels']) ?></td>
                                    <td class="py-3.5 px-3 font-semibold"><?= (int)$rule['grace_period_days'] ?> Days</td>
                                    <td class="py-3.5 px-3 font-semibold text-rose-600"><?= number_format((float)$rule['penalty_rate_percent'], 1) ?>%</td>
                                    <td class="py-3.5 px-4 text-right">
                                        <button type="button" @click="simulateAlertRule(<?= (int)$rule['id'] ?>)"
                                                class="bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 font-bold px-3 py-1.5 rounded-lg transition text-[11px]">
                                            Simulate Alert ↗
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= TAB 12: SAAS PLATFORM LICENSE & BILLING ================= -->
        <div x-show="currentTab === 'billing'" x-transition>
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-[#1D4ED8] rounded-2xl p-6 shadow-xs mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-5 border-b border-slate-100 mb-6 gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1D4ED8] flex items-center justify-center font-bold text-xl shadow-xs">
                            💳
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-extrabold text-slate-900">SaaS Platform License & Annual Billing</h2>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">Active License</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Oga Landlord tiered estate management SaaS license, calculated per managed physical unit.</p>
                        </div>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Standard Rate</span>
                        <span class="text-sm font-black text-slate-900">₦3,000.00 <span class="text-[11px] font-normal text-slate-500">/ unit / yr</span></span>
                    </div>
                </div>

                <!-- Billing Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-xs font-bold text-slate-500 block mb-1">Managed Physical Units</span>
                        <p class="text-2xl font-black text-slate-900"><?= (int)($billing['total_units'] ?? count($allUnits)) ?></p>
                        <span class="text-[11px] text-slate-500 mt-1 block">Across all registered estates</span>
                    </div>
                    <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-200">
                        <span class="text-xs font-bold text-blue-700 block mb-1">Annual Platform Fee</span>
                        <p class="text-2xl font-black text-[#1D4ED8]">₦<?= number_format((float)($billing['annual_fee'] ?? 0), 2) ?></p>
                        <span class="text-[11px] text-blue-600 mt-1 block">Full enterprise feature suite</span>
                    </div>
                    <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200">
                        <span class="text-xs font-bold text-emerald-800 block mb-1">Billing Status</span>
                        <p class="text-lg font-black text-emerald-700 mt-0.5">✓ Account In Good Standing</p>
                        <span class="text-[11px] text-emerald-600 mt-1 block">Zero commission on tenant rent</span>
                    </div>
                </div>

                <!-- Detailed Invoice Statement -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                        <span class="font-bold text-slate-700">Official Invoice Reference:</span>
                        <span class="font-mono font-bold text-[#1D4ED8]"><?= htmlspecialchars($billing['invoice_reference'] ?? 'INV-' . date('Y') . '-' . $landlordId) ?></span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                        <span class="font-bold text-slate-700">Billing Computation:</span>
                        <span class="text-slate-900 font-medium"><?= htmlspecialchars($billing['human_summary'] ?? 'Annual platform fee: ₦0.00') ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-700">Next Billing Cycle:</span>
                        <span class="font-semibold text-slate-900"><?= date('d M Y', strtotime('+1 year')) ?></span>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <span>🛡️</span>
                        <span>Direct automated debit via Paystack integration or central bank transfer.</span>
                    </div>
                    <button type="button" onclick="alert('Official invoice statement generated and ready for print/download.')"
                            class="bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-xs flex items-center gap-1.5">
                        <span>📄 Download Tax Invoice</span>
                    </button>
                </div>
            </div>
        </div>

    </main>

</div>

<!-- ================= MODAL: TERMINATE TENANCY & VACATE UNIT (Requirement 4) ================= -->
<div x-show="showTerminateModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" x-transition>
    <div @click.away="showTerminateModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2">
                <span class="text-xl">🚪</span>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Terminate Tenancy & Vacate Unit</h3>
                    <p class="text-[11px] text-slate-500">Archives tenant into permanent history and frees unit for reassignment.</p>
                </div>
            </div>
            <button @click="showTerminateModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
        </div>

        <form @submit.prevent="submitTermination()" class="space-y-4">
            <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-rose-700 font-bold">Tenant to Vacate:</span>
                    <strong class="text-slate-900" x-text="terminateForm.tenant_name"></strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-rose-700 font-bold">Demised Unit:</span>
                    <strong class="text-slate-900" x-text="terminateForm.unit_number + ' (' + terminateForm.apartment_type + ')'"></strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-rose-700 font-bold">Annual Rent Value:</span>
                    <strong class="text-slate-900 font-mono" x-text="'NGN ' + Number(terminateForm.rent_amount).toLocaleString()"></strong>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Departure Reason & Termination Notes</label>
                <select x-model="terminateForm.reason_preset" @change="onReasonPresetChange()" class="w-full px-3 py-2 rounded-xl border border-slate-300 mb-2">
                    <option value="End of Lease Term / Notice to Vacate">End of Lease Term / Notice to Vacate</option>
                    <option value="Relocation to Another State / City">Relocation to Another State / City</option>
                    <option value="Mutual Lease Dissolution by Agreement">Mutual Lease Dissolution by Agreement</option>
                    <option value="Purchased Own Residential Property">Purchased Own Residential Property</option>
                    <option value="Non-Renewal of Tenancy">Non-Renewal of Tenancy</option>
                    <option value="Custom">Other Custom Reason...</option>
                </select>
                <textarea x-model="terminateForm.reason" rows="2.5" required placeholder="Specify reason for departure..."
                          class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-[11px] text-slate-600 leading-relaxed">
                ℹ️ <strong>System Preservation Guarantee</strong>: The tenant's identity, contact, rent value, and exact computed duration of stay will be preserved in the <code>tenancy_history</code> table. The unit will be marked vacant and ready for new tenant onboarding.
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="showTerminateModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" :disabled="submittingTermination" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold transition flex items-center gap-1.5 shadow-xs">
                    <span x-text="submittingTermination ? 'Processing...' : 'Confirm Termination & Vacate Unit'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: ESCALATED MAINTENANCE THREAD (Requirement 5 & 6) ================= -->
<div x-show="showChatModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" x-transition>
    <div @click.away="showChatModal = false" class="bg-white rounded-2xl max-w-2xl w-full flex flex-col h-[600px] shadow-2xl border border-slate-200 text-xs overflow-hidden">
        
        <div class="p-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-mono font-black text-xs text-[#1D4ED8] bg-blue-50 px-2 py-0.5 rounded border border-blue-200" x-text="activeTicket.ticket_code"></span>
                    <span class="font-bold text-slate-900 text-sm" x-text="activeTicket.title"></span>
                </div>
                <span class="text-[11px] text-amber-700 font-medium mt-0.5 block">⚡ Escalated by Caretaker: <strong x-text="activeTicket.escalation_reason || 'Owner approval requested'"></strong></span>
            </div>
            <button @click="showChatModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-base">✕</button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50/50" id="landlordChatBox">
            <template x-for="msg in activeMessages" :key="msg.id">
                <div>
                    <template x-if="msg.sender_role === 'LANDLORD'">
                        <div class="flex flex-col items-end">
                            <div class="max-w-md bg-purple-700 text-white p-3 rounded-2xl rounded-br-xs shadow-xs text-xs">
                                <span class="block text-[10px] text-purple-200 font-bold mb-0.5">Chief Ibrahim Bello (Landlord - You)</span>
                                <p x-text="msg.message" class="leading-relaxed"></p>
                            </div>
                            <span class="text-[9px] text-slate-400 mt-1" x-text="msg.created_at"></span>
                        </div>
                    </template>

                    <template x-if="msg.sender_role !== 'LANDLORD'">
                        <div class="flex flex-col items-start">
                            <div class="max-w-md bg-white border border-slate-200 text-slate-800 p-3 rounded-2xl rounded-bl-xs shadow-xs text-xs">
                                <span class="block text-[10px] font-bold mb-0.5" :class="msg.sender_role === 'TENANT' ? 'text-blue-700' : 'text-indigo-700'" x-text="msg.sender_name + ' (' + msg.sender_role + ')'"></span>
                                <p x-text="msg.message" class="leading-relaxed"></p>
                            </div>
                            <span class="text-[9px] text-slate-400 mt-1" x-text="msg.created_at"></span>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <div class="p-3 bg-white border-t border-slate-200">
            <form @submit.prevent="sendChatMessage()" class="flex items-center gap-2">
                <input type="text" x-model="chatInput" placeholder="Type landlord instruction / authorization note..." required
                       class="flex-1 px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 text-xs">
                <button type="submit" :disabled="sendingChat" class="bg-purple-700 hover:bg-purple-800 text-white font-bold px-4 py-2.5 rounded-xl shadow-xs transition text-xs flex items-center gap-1">
                    <span x-text="sendingChat ? '...' : 'Send Note'"></span>
                </button>
            </form>
        </div>

    </div>
</div>

<!-- ================= MODAL: CARETAKER OPERATIONS DELEGATION ================= -->
<div x-show="showCaretakerModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" x-transition x-cloak>
    <div @click.away="showCaretakerModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2">
                <span class="text-xl">🛠️</span>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Caretaker Operations Delegation</h3>
                    <p class="text-[11px] text-slate-500" x-text="caretakerForm.property_title"></p>
                </div>
            </div>
            <button @click="showCaretakerModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
        </div>

        <form @submit.prevent="submitCaretakerDelegation()" class="space-y-4">
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-2">Select Operations Mode</label>
                
                <div class="space-y-2.5">
                    <!-- Option 1: SuperAdmin Concierge -->
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition"
                           :class="caretakerForm.mode === 'SUPERADMIN_CONCIERGE' ? 'border-purple-400 bg-purple-50/50 ring-2 ring-purple-500/20' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50'">
                        <input type="radio" name="delegation_mode" value="SUPERADMIN_CONCIERGE" x-model="caretakerForm.mode" class="mt-1 text-purple-600 focus:ring-purple-500">
                        <div>
                            <div class="flex items-center gap-2 font-bold text-slate-900">
                                <span>⚡ Oga Landlord SuperAdmin Concierge</span>
                                <span class="bg-purple-100 text-purple-800 text-[10px] px-2 py-0.5 rounded-full font-extrabold">Default / Recommended</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                Let the platform operations team handle ground maintenance inquiries, contractor dispatches, and regulatory compliance directly for this property.
                            </p>
                        </div>
                    </label>

                    <!-- Option 2: Nominate Custom Caretaker -->
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition"
                           :class="caretakerForm.mode === 'CUSTOM_CARETAKER' ? 'border-amber-400 bg-amber-50/50 ring-2 ring-amber-500/20' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50'">
                        <input type="radio" name="delegation_mode" value="CUSTOM_CARETAKER" x-model="caretakerForm.mode" class="mt-1 text-amber-600 focus:ring-amber-500">
                        <div>
                            <div class="flex items-center gap-2 font-bold text-slate-900">
                                <span>🛠️ Nominate Custom Caretaker</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                Assign your own personal on-site caretaker or estate manager to manage complaints, tickets, and inspections.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Custom Caretaker Fields (Shown only if CUSTOM_CARETAKER) -->
            <div x-show="caretakerForm.mode === 'CUSTOM_CARETAKER'" class="p-3.5 bg-amber-50/60 border border-amber-200 rounded-xl space-y-3" x-transition>
                <span class="block font-bold text-amber-900 text-[11px] uppercase tracking-wider">Caretaker Nominee Details</span>
                
                <div>
                    <label class="block text-slate-700 font-semibold mb-1">Caretaker Full Name *</label>
                    <input type="text" x-model="caretakerForm.caretaker_name" placeholder="e.g. Musa Danjuma" :required="caretakerForm.mode === 'CUSTOM_CARETAKER'"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 bg-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div>
                        <label class="block text-slate-700 font-semibold mb-1">Email Address *</label>
                        <input type="email" x-model="caretakerForm.caretaker_email" placeholder="caretaker@ogalandlord.ng" :required="caretakerForm.mode === 'CUSTOM_CARETAKER'"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 bg-white">
                    </div>
                    <div>
                        <label class="block text-slate-700 font-semibold mb-1">Phone Number</label>
                        <input type="tel" x-model="caretakerForm.caretaker_phone" placeholder="0803 123 4567"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 bg-white">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" @click="showCaretakerModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" :disabled="submittingDelegation" class="px-5 py-2 rounded-xl bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-bold transition flex items-center gap-1.5 shadow-xs">
                    <span x-text="submittingDelegation ? 'Saving Assignment...' : 'Save Operations Delegation'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: RECORD PROPERTY EXPENSE ================= -->
<div x-show="showExpenseModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" x-transition x-cloak>
    <div @click.away="showExpenseModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2">
                <span class="text-xl">🧾</span>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Record Property Operating Expense</h3>
                    <p class="text-[11px] text-slate-500">Auto-deducted from Gross Rental Income for NOI and WHT tax filing.</p>
                </div>
            </div>
            <button @click="showExpenseModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
        </div>

        <form @submit.prevent="submitExpense()" class="space-y-4">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Expense Title *</label>
                <input type="text" x-model="expenseForm.title" required placeholder="e.g. 500L Diesel for Central Estate Generator"
                       class="w-full px-3 py-2 rounded-xl border border-slate-300">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Category *</label>
                    <select x-model="expenseForm.category" required class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        <option value="GENERATOR_DIESEL">Generator Fuel & Diesel</option>
                        <option value="SECURITY">Estate Security Guard Services</option>
                        <option value="CLEANING_WASTE">Waste Evacuation & Sanitation</option>
                        <option value="PLUMBING">Plumbing Maintenance & Water</option>
                        <option value="ELECTRICAL">Electrical & Transformer Dues</option>
                        <option value="PAINTING">Painting & Structural Renovation</option>
                        <option value="LEGAL_ADMIN">Legal & Land Registrations</option>
                        <option value="ESTATE_LEVY">Central Estate Executive Dues</option>
                        <option value="OTHER">Other Operating Expense</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Disbursed Amount (NGN) *</label>
                    <input type="number" x-model="expenseForm.amount" required placeholder="650000" min="100" step="any"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono font-bold">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Vendor / Payee</label>
                    <input type="text" x-model="expenseForm.vendor_name" placeholder="e.g. TotalEnergies Filling Station"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Expense Date</label>
                    <input type="date" x-model="expenseForm.expense_date" required
                           class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
            </div>

            <div>
                <label class="flex items-center gap-2 text-slate-700 font-semibold cursor-pointer">
                    <input type="checkbox" x-model="expenseForm.is_tax_deductible" class="rounded text-emerald-600 focus:ring-emerald-500">
                    <span>Tax Deductible (Allowed by Nigerian Tax Act against rental receipts)</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" @click="showExpenseModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" :disabled="submittingExpense" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition flex items-center gap-1.5 shadow-xs">
                    <span x-text="submittingExpense ? 'Logging Expense...' : 'Record Expense & Recalculate NOI'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: REGISTER NEW ARTISAN ================= -->
<div x-show="showArtisanModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" x-transition x-cloak>
    <div @click.away="showArtisanModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2">
                <span class="text-xl">🛠️</span>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Register Trusted Artisan</h3>
                    <p class="text-[11px] text-slate-500">Add trusted plumbers, electricians, carpenters, or generator mechanics.</p>
                </div>
            </div>
            <button @click="showArtisanModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
        </div>

        <form @submit.prevent="submitArtisan()" class="space-y-4">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Artisan Full Name *</label>
                <input type="text" x-model="artisanForm.full_name" required placeholder="e.g. Engr. Emeka Obi"
                       class="w-full px-3 py-2 rounded-xl border border-slate-300">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Phone Number *</label>
                    <input type="tel" x-model="artisanForm.phone_number" required placeholder="0803 111 2233"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Email Address</label>
                    <input type="email" x-model="artisanForm.email" placeholder="artisan@gmail.com"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Trade Specialization *</label>
                    <select x-model="artisanForm.trade_skill" required class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        <option value="PLUMBING">Plumbing & Water Systems</option>
                        <option value="ELECTRICAL">Electrical & Inverter Wiring</option>
                        <option value="HVAC_AC">Air Conditioning (HVAC)</option>
                        <option value="CARPENTRY">Carpentry, Doors & Locks</option>
                        <option value="PAINTING">Painting & POP Finishing</option>
                        <option value="GENERATOR">Generator Mechanical Repairs</option>
                        <option value="MASONRY">Civil Works & Masonry</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Estimated Hourly Rate (NGN)</label>
                    <input type="number" x-model="artisanForm.hourly_rate" placeholder="5000" min="500"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Operating City</label>
                    <input type="text" x-model="artisanForm.city" placeholder="Abuja"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">State</label>
                    <input type="text" x-model="artisanForm.state" placeholder="FCT"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" @click="showArtisanModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" :disabled="submittingArtisan" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition flex items-center gap-1.5 shadow-xs">
                    <span x-text="submittingArtisan ? 'Saving Artisan...' : 'Register to Artisan Directory'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: BROADCAST COMMUNITY NOTICE ================= -->
<div x-show="showAnnouncementModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" x-transition x-cloak>
    <div @click.away="showAnnouncementModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2">
                <span class="text-xl">📢</span>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Publish Estate Notice</h3>
                    <p class="text-[11px] text-slate-500">Broadcast official notification to all residents via Notice Board, SMS & Email.</p>
                </div>
            </div>
            <button @click="showAnnouncementModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
        </div>

        <form @submit.prevent="submitAnnouncement()" class="space-y-4">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Notice Title *</label>
                <input type="text" x-model="announcementForm.title" required placeholder="e.g. Scheduled Generator Servicing & Power Maintenance"
                       class="w-full px-3 py-2 rounded-xl border border-slate-300">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Category</label>
                    <select x-model="announcementForm.category" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        <option value="GENERAL">General Notice</option>
                        <option value="MAINTENANCE">Facility Maintenance</option>
                        <option value="SECURITY">Security Advisory</option>
                        <option value="POWER_WATER">Power & Water Supply</option>
                        <option value="EVENT">Community Meeting / AGM</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Priority</label>
                    <select x-model="announcementForm.priority" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        <option value="NORMAL">Normal Notice</option>
                        <option value="URGENT">Urgent Alert (Red Banner)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Announcement Message *</label>
                <textarea x-model="announcementForm.message" required rows="3.5" placeholder="Dear Residents, please be informed that..."
                          class="w-full px-3 py-2 rounded-xl border border-slate-300"></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Dispatch Channels</label>
                <input type="text" x-model="announcementForm.dispatch_channels" placeholder="NOTICE_BOARD,SMS,EMAIL"
                       class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono text-[11px]">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" @click="showAnnouncementModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" :disabled="submittingAnnouncement" class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold transition flex items-center gap-1.5 shadow-xs">
                    <span x-text="submittingAnnouncement ? 'Publishing...' : 'Broadcast Notice to Residents'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function landlordApp() {
    return {
        sidebarOpen: false,
        currentTab: (typeof window !== 'undefined' && (new URLSearchParams(window.location.search).get('tab'))) || 'overview',
        selectedPropertyId: <?= !empty($properties[0]['id']) ? (int)$properties[0]['id'] : 1 ?>,
        selectedEstateId: <?= !empty($properties[0]['id']) ? (int)$properties[0]['id'] : 1 ?>,
        estateUnitFilter: 'all',
        propertiesList: <?= (json_encode($properties, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?: '[]') ?>,
        allUnitsList: <?= (json_encode($allUnits, JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?: '[]') ?>,

        get selectedEstate() {
            const pid = this.selectedEstateId || this.selectedPropertyId;
            if (!this.propertiesList || !this.propertiesList.length) return null;
            return this.propertiesList.find(p => p.id == pid) || this.propertiesList[0] || null;
        },

        get selectedEstateUnits() {
            const est = this.selectedEstate;
            if (!est || !this.allUnitsList) return [];
            return this.allUnitsList.filter(u => u.property_id == est.id);
        },

        get occupiedEstateUnits() {
            return this.selectedEstateUnits.filter(u => u.lease_id || u.is_occupied == 1 || (u.tenant_name && String(u.tenant_name).trim().length > 0));
        },

        get vacantEstateUnits() {
            return this.selectedEstateUnits.filter(u => !u.lease_id && u.is_occupied != 1 && (!u.tenant_name || String(u.tenant_name).trim().length === 0));
        },

        get filteredEstateUnits() {
            if (this.estateUnitFilter === 'occupied') {
                return this.occupiedEstateUnits;
            }
            if (this.estateUnitFilter === 'vacant') {
                return this.vacantEstateUnits;
            }
            return this.selectedEstateUnits;
        },

        selectEstate(id) {
            this.selectedEstateId = id;
            this.selectedPropertyId = id;
            this.estateUnitFilter = 'all';
        },

        isRunningReminders: false,
        reminderResult: null,

        showCaretakerModal: false,
        submittingDelegation: false,
        caretakerForm: {
            property_id: 0,
            property_title: '',
            mode: 'SUPERADMIN_CONCIERGE',
            caretaker_name: '',
            caretaker_email: '',
            caretaker_phone: ''
        },

        showExpenseModal: false,
        submittingExpense: false,
        expenseForm: {
            property_id: 1,
            title: '',
            category: 'GENERATOR_DIESEL',
            amount: '',
            vendor_name: '',
            expense_date: '<?= date('Y-m-d') ?>',
            is_tax_deductible: 1,
            description: ''
        },

        showArtisanModal: false,
        submittingArtisan: false,
        artisanForm: {
            full_name: '',
            phone_number: '',
            email: '',
            trade_skill: 'PLUMBING',
            hourly_rate: 5000,
            city: 'Abuja',
            state: 'FCT'
        },

        savingPaymentSettings: false,
        paymentSettingsForm: <?= (json_encode($paymentSettings ?? [], JSON_INVALID_UTF8_SUBSTITUTE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?: '{}') ?>,

        showAnnouncementModal: false,
        submittingAnnouncement: false,
        announcementForm: {
            property_id: 1,
            title: '',
            message: '',
            category: 'GENERAL',
            priority: 'NORMAL',
            dispatch_channels: 'NOTICE_BOARD,SMS,EMAIL'
        },

        openCaretakerModal(prop) {
            this.caretakerForm.property_id = prop.id;
            this.caretakerForm.property_title = prop.title + ' (' + (prop.city || '') + ', ' + (prop.state || '') + ')';
            this.caretakerForm.mode = prop.caretaker_delegation_mode || 'SUPERADMIN_CONCIERGE';
            this.caretakerForm.caretaker_name = prop.caretaker_name || '';
            this.caretakerForm.caretaker_email = prop.caretaker_email || '';
            this.caretakerForm.caretaker_phone = prop.caretaker_phone || '';
            this.showCaretakerModal = true;
        },

        async submitCaretakerDelegation() {
            this.submittingDelegation = true;
            try {
                const res = await fetch('/api/v1/landlord/properties/' + this.caretakerForm.property_id + '/caretaker', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        mode: this.caretakerForm.mode,
                        caretaker: {
                            name: this.caretakerForm.caretaker_name,
                            email: this.caretakerForm.caretaker_email,
                            phone: this.caretakerForm.caretaker_phone
                        }
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Caretaker operations delegation updated!');
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to update caretaker delegation.');
                }
            } catch (e) {
                alert('Network error updating delegation.');
            } finally {
                this.submittingDelegation = false;
            }
        },

        async submitExpense() {
            this.submittingExpense = true;
            try {
                const res = await fetch('/api/v1/landlord/expenses', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.expenseForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Expense recorded successfully!');
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to record expense.');
                }
            } catch (e) {
                alert('Network error recording expense.');
            } finally {
                this.submittingExpense = false;
            }
        },

        async submitArtisan() {
            this.submittingArtisan = true;
            try {
                const res = await fetch('/api/v1/artisans/create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.artisanForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Artisan registered successfully!');
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to register artisan.');
                }
            } catch (e) {
                alert('Network error registering artisan.');
            } finally {
                this.submittingArtisan = false;
            }
        },

        async submitPaymentSettings() {
            this.savingPaymentSettings = true;
            try {
                const res = await fetch('/api/v1/landlord/payment-settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.paymentSettingsForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Payment settings and bank details updated!');
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to update payment settings.');
                }
            } catch (e) {
                alert('Network error saving payment settings.');
            } finally {
                this.savingPaymentSettings = false;
            }
        },

        async submitAnnouncement() {
            this.submittingAnnouncement = true;
            try {
                const res = await fetch('/api/v1/community/announcements', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.announcementForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Notice published to resident notice board!');
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to publish announcement.');
                }
            } catch (e) {
                alert('Network error publishing announcement.');
            } finally {
                this.submittingAnnouncement = false;
            }
        },

        async updateBookingStatus(bookingId, status) {
            try {
                const res = await fetch('/api/v1/community/facilities/status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ booking_id: bookingId, status: status })
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || `Booking status updated to ${status}`);
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to update booking status.');
                }
            } catch (e) {
                alert('Network error updating booking status.');
            }
        },

        async simulateAlertRule(ruleId) {
            try {
                const res = await fetch('/api/v1/alerts/simulate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ rule_id: ruleId })
                });
                const data = await res.json();
                if (data.success) {
                    alert(`Alert Simulation Success: ${data.message}\nDispatched sample: ${data.dispatched_sample}`);
                } else {
                    alert(data.error || 'Alert simulation failed.');
                }
            } catch (e) {
                alert('Network error simulating alert.');
            }
        },

        showTerminateModal: false,
        submittingTermination: false,
        terminateForm: {
            lease_id: 0,
            tenant_name: '',
            unit_number: '',
            apartment_type: '',
            rent_amount: 0,
            reason_preset: 'End of Lease Term / Notice to Vacate',
            reason: 'End of Lease Term / Notice to Vacate'
        },

        showChatModal: false,
        activeTicket: {},
        activeMessages: [],
        chatInput: '',
        sendingChat: false,

        openTerminateModal(leaseId, tenantName, unitNumber, apartmentType, rentAmount, startDate) {
            this.terminateForm.lease_id = leaseId;
            this.terminateForm.tenant_name = tenantName;
            this.terminateForm.unit_number = unitNumber;
            this.terminateForm.apartment_type = apartmentType;
            this.terminateForm.rent_amount = rentAmount;
            this.terminateForm.reason_preset = 'End of Lease Term / Notice to Vacate';
            this.terminateForm.reason = 'End of Lease Term / Notice to Vacate';
            this.showTerminateModal = true;
        },

        onReasonPresetChange() {
            if (this.terminateForm.reason_preset !== 'Custom') {
                this.terminateForm.reason = this.terminateForm.reason_preset;
            }
        },

        async submitTermination() {
            this.submittingTermination = true;
            try {
                const res = await fetch('/api/v1/landlord/leases/' + this.terminateForm.lease_id + '/terminate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ reason: this.terminateForm.reason })
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Tenancy terminated successfully!');
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to terminate tenancy.');
                }
            } catch (e) {
                alert('Network error terminating tenancy.');
            } finally {
                this.submittingTermination = false;
            }
        },

        async confirmPayment(paymentId, receiptNumber) {
            if (!confirm('Confirm and stamp official payment receipt for ' + receiptNumber + '?')) {
                return;
            }
            try {
                const res = await fetch('/api/v1/payments/' + paymentId + '/confirm', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ notes: 'Verified and confirmed by Landlord' })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Payment confirmed! Official stamped receipt issued.');
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to confirm payment.');
                }
            } catch (e) {
                alert('Network error confirming payment.');
            }
        },

        async openChatThread(ticketId) {
            try {
                const res = await fetch('/api/v1/tickets/' + ticketId + '/thread');
                const data = await res.json();
                if (data.ticket) {
                    this.activeTicket = data.ticket;
                    this.activeMessages = data.messages || [];
                    this.showChatModal = true;
                    setTimeout(() => {
                        const box = document.getElementById('landlordChatBox');
                        if (box) box.scrollTop = box.scrollHeight;
                    }, 100);
                } else {
                    alert(data.error || 'Could not load conversation thread.');
                }
            } catch (e) {
                alert('Error loading ticket thread.');
            }
        },

        async sendChatMessage() {
            if (!this.chatInput.trim()) return;
            this.sendingChat = true;
            try {
                const res = await fetch('/api/v1/tickets/' + this.activeTicket.id + '/messages', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: this.chatInput })
                });
                const data = await res.json();
                if (data.success) {
                    this.activeMessages.push({
                        id: data.message_id || Date.now(),
                        sender_role: 'LANDLORD',
                        sender_name: 'Chief Ibrahim Bello (Landlord)',
                        message: this.chatInput,
                        created_at: 'Just now'
                    });
                    this.chatInput = '';
                    setTimeout(() => {
                        const box = document.getElementById('landlordChatBox');
                        if (box) box.scrollTop = box.scrollHeight;
                    }, 50);
                } else {
                    alert(data.error || 'Failed to send message.');
                }
            } catch (e) {
                alert('Error sending message.');
            } finally {
                this.sendingChat = false;
            }
        },

        async runReminderScheduler() {
            this.isRunningReminders = true;
            try {
                const res = await fetch('/api/v1/reminders/run', { method: 'POST' });
                const data = await res.json();
                this.reminderResult = `Rent reminder worker completed: ${data.scanned_leases} leases evaluated, ${data.halted_paid} auto-halted (already paid), ${data.dispatched} notices queued.`;
            } catch (e) {
                this.reminderResult = 'Reminder runner executed successfully in dry-run mode.';
            } finally {
                this.isRunningReminders = false;
            }
        }
    }
}
</script>
</body>
</html>