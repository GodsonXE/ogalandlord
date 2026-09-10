<?php
declare(strict_types=1);

$tenant = $dashboardData['tenant'] ?? [];
$lease = $dashboardData['lease'] ?? null;
$landlord = $dashboardData['landlord'] ?? null;
$caretaker = $dashboardData['caretaker'] ?? null;
$tickets = $dashboardData['tickets'] ?? [];
$payments = $dashboardData['payments'] ?? [];
$pendingBills = $dashboardData['pending_bills'] ?? [];

$isSigned = ($lease && ($lease['agreement_status'] ?? '') === 'FULLY_EXECUTED');
$hasActiveLease = ($lease !== null);

// Calculate days remaining
$daysRemaining = 0;
if ($lease && !empty($lease['rent_due_date'])) {
    $dueDate = new DateTimeImmutable($lease['rent_due_date']);
    $today = new DateTimeImmutable();
    $diff = $today->diff($dueDate);
    $daysRemaining = $dueDate > $today ? (int)$diff->days : -((int)$diff->days);
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Portal — Oga Landlord</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full text-slate-800 antialiased" x-data="tenantApp()" x-cloak>

<div class="min-h-full flex flex-col md:flex-row">

    <!-- Mobile Top Header -->
    <div class="md:hidden bg-[#1D4ED8] text-white px-4 py-3 flex items-center justify-between sticky top-0 z-30 shadow-md">
        <div class="flex items-center gap-2">
            <img src="/assets/images/logo.jpg" alt="Oga Landlord Logo" class="h-8 w-auto object-contain rounded bg-white p-0.5">
            <span class="font-extrabold text-white text-sm tracking-tight">Oga<span class="text-blue-200">Landlord</span></span>
            <span class="text-[10px] bg-emerald-600 text-white font-bold px-2 py-0.5 rounded-md">Tenant</span>
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
                        <span class="text-[10px] uppercase tracking-wider text-emerald-300 font-bold">Resident Portal</span>
                    </div>
                </a>

                <!-- Resident Residency Snapshot Badge -->
                <div class="mt-5 p-3 rounded-xl bg-blue-800/80 border border-blue-400/40 shadow-inner">
                    <div class="flex items-center justify-between text-[10px] font-bold text-blue-200 uppercase tracking-wider mb-1">
                        <span>Assigned Unit</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    </div>
                    <div class="text-sm font-black text-white">
                        <?= htmlspecialchars($lease['unit_number'] ?? 'Unit Pending') ?>
                    </div>
                    <p class="text-[11px] text-blue-200 truncate mt-0.5">
                        <?= htmlspecialchars($lease['property_title'] ?? 'Unity Estate, Abuja') ?>
                    </p>
                </div>
            </div>

            <!-- Sidebar Navigation Links -->
            <nav class="p-4 space-y-1.5">
                <span class="block px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-blue-200">Resident Services</span>
                
                <!-- 1. Overview -->
                <button type="button" @click="currentTab = 'overview'; sidebarOpen = false"
                        :class="currentTab === 'overview' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>My Residency Overview</span>
                </button>

                <!-- 2. Tenancy Agreement -->
                <button type="button" @click="currentTab = 'agreement'; sidebarOpen = false"
                        :class="currentTab === 'agreement' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Tenancy Agreement</span>
                    </div>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold <?= $isSigned ? 'bg-emerald-400 text-emerald-950' : 'bg-amber-400 text-amber-950' ?>">
                        <?= $isSigned ? 'Sealed' : 'Draft' ?>
                    </span>
                </button>

                <!-- 3. Maintenance & Concerns -->
                <button type="button" @click="currentTab = 'maintenance'; sidebarOpen = false"
                        :class="currentTab === 'maintenance' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Maintenance & Concerns</span>
                    </div>
                    <span :class="currentTab === 'maintenance' ? 'bg-blue-100 text-[#1D4ED8]' : 'bg-blue-700/80 text-white'" class="px-2 py-0.5 rounded-full text-[10px] font-bold"><?= count($tickets) ?></span>
                </button>

                <!-- 4. Payments, Levies & Bills -->
                <button type="button" @click="currentTab = 'payments'; sidebarOpen = false"
                        :class="currentTab === 'payments' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>Payments & Levies</span>
                    </div>
                    <span :class="currentTab === 'payments' ? 'bg-blue-100 text-[#1D4ED8]' : 'bg-blue-700/80 text-white'" class="px-2 py-0.5 rounded-full text-[10px] font-bold"><?= count($payments) ?></span>
                </button>

                <!-- 4b. Estate Notice Board -->
                <button type="button" @click="currentTab = 'notice_board'; sidebarOpen = false"
                        :class="currentTab === 'notice_board' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0 text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                        <span>Notice Board</span>
                    </div>
                    <span :class="currentTab === 'notice_board' ? 'bg-blue-100 text-[#1D4ED8]' : 'bg-blue-700/80 text-white'" class="px-2 py-0.5 rounded-full text-[10px] font-bold"><?= count($announcements ?? []) ?></span>
                </button>

                <!-- 4c. Facility Reservations -->
                <button type="button" @click="currentTab = 'facilities'; sidebarOpen = false"
                        :class="currentTab === 'facilities' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Facility Bookings</span>
                    </div>
                    <span :class="currentTab === 'facilities' ? 'bg-blue-100 text-[#1D4ED8]' : 'bg-blue-700/80 text-white'" class="px-2 py-0.5 rounded-full text-[10px] font-bold"><?= count($facilityBookings ?? []) ?></span>
                </button>

                <!-- 5. Profile & Household -->
                <button type="button" @click="currentTab = 'profile'; sidebarOpen = false"
                        :class="currentTab === 'profile' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>My Profile & KYC</span>
                </button>
            </nav>
        </div>

        <!-- Sidebar Footer / Account & Role Switcher -->
        <div class="p-4 border-t border-blue-700/80 bg-blue-900/60 space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-emerald-500 text-white font-extrabold flex items-center justify-center text-xs shadow-sm">
                    <?= strtoupper(substr($tenant['full_name'] ?? 'AO', 0, 2)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="block text-xs font-bold text-white truncate"><?= htmlspecialchars($tenant['full_name'] ?? 'Resident Tenant') ?></span>
                    <span class="block text-[10px] text-blue-200 truncate"><?= htmlspecialchars($tenant['email'] ?? 'amara.okafor@example.com') ?></span>
                </div>
            </div>

            <div class="pt-2 border-t border-blue-800/80 flex flex-col gap-1.5 text-[11px]">
                <span class="text-[10px] uppercase tracking-wider font-bold text-blue-300">Switch Workspace (Demo)</span>
                <a href="/demo/landlord" class="flex items-center justify-between text-blue-100 hover:text-white py-1 font-medium transition">
                    <span>👔 Landlord Workspace</span>
                    <span>→</span>
                </a>
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

        <!-- Interactive Tenant Test-Play Banner -->
        <div class="mb-6 p-4 rounded-2xl bg-gradient-to-r from-emerald-500/10 via-blue-500/10 to-purple-500/10 border border-emerald-300/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shrink-0">
                    🏠
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-black uppercase text-emerald-800 tracking-wider">Interactive Tenant Test-Play Sandbox</span>
                        <span class="bg-emerald-600 text-white text-[9px] font-extrabold px-2 py-0.5 rounded-full uppercase">Live Simulation</span>
                    </div>
                    <p class="text-xs text-slate-700 mt-0.5 leading-relaxed">
                        You are testing the tenant resident experience for <strong><?= htmlspecialchars($tenant['full_name'] ?? 'Amara Okafor') ?></strong>. Explore signed agreement preview, direct caretaker maintenance chat, online payments, and stamped receipts.
                    </p>
                </div>
            </div>

            <!-- Role Switcher Quick Links -->
            <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                <span class="text-[11px] font-bold text-slate-500 hidden lg:inline">Switch Role:</span>
                <a href="/demo/landlord" class="px-2.5 py-1.5 rounded-xl text-xs font-bold bg-white hover:bg-blue-50 text-[#1D4ED8] border border-blue-200 transition shadow-2xs">
                    👔 Landlord
                </a>
                <a href="/demo/caretaker" class="px-2.5 py-1.5 rounded-xl text-xs font-bold bg-white hover:bg-indigo-50 text-indigo-700 border border-indigo-200 transition shadow-2xs">
                    🛠️ Caretaker
                </a>
                <a href="/demo/superadmin" class="px-2.5 py-1.5 rounded-xl text-xs font-bold bg-white hover:bg-purple-50 text-purple-700 border border-purple-200 transition shadow-2xs">
                    ⚡ SuperAdmin
                </a>
            </div>
        </div>

        <!-- Top Action Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200 mb-8">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tenant Portal</span>
                    <span class="text-slate-300">•</span>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">Active Resident</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">Welcome, <?= htmlspecialchars($tenant['full_name'] ?? 'Amara Okafor') ?></h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    <?= htmlspecialchars($lease['property_title'] ?? 'PHDL Unity Estate, Idu') ?> • Unit: <strong class="text-slate-700"><?= htmlspecialchars($lease['unit_number'] ?? 'Unit 1A') ?></strong>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button" @click="openPaymentModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>Make a Payment</span>
                </button>
                <button type="button" @click="openTicketModal()" class="bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Lodge Complaint</span>
                </button>
            </div>
        </div>

        <!-- ================= TAB 1: RESIDENCY OVERVIEW ================= -->
        <div x-show="currentTab === 'overview'" x-transition class="space-y-6">

            <!-- Color-Coded KPI Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Card 1: Emerald (Residency Status) -->
                <div class="bg-white border border-slate-200 border-t-4 border-t-emerald-500 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between text-xs text-slate-500 font-semibold mb-2">
                        <span>Apartment Unit</span>
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">🏠</span>
                    </div>
                    <div class="text-2xl font-black text-slate-900">
                        <?= htmlspecialchars($lease['unit_number'] ?? 'Unit 1A') ?>
                    </div>
                    <div class="mt-2 text-xs font-medium text-emerald-700 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span><?= htmlspecialchars($lease['apartment_type'] ?? '2-Bedroom Apartment') ?></span>
                    </div>
                    <span class="block text-[11px] text-slate-400 mt-1 truncate"><?= htmlspecialchars($lease['property_title'] ?? 'Unity Estate') ?></span>
                </div>

                <!-- Card 2: Blue (Rent Countdown) -->
                <div class="bg-white border border-slate-200 border-t-4 border-t-[#1D4ED8] rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between text-xs text-slate-500 font-semibold mb-2">
                        <span>Rent Due Countdown</span>
                        <span class="w-6 h-6 rounded-lg bg-blue-50 text-[#1D4ED8] flex items-center justify-center font-bold text-xs">⏳</span>
                    </div>
                    <div class="text-2xl font-black text-slate-900">
                        <?= max(0, $daysRemaining) ?> <span class="text-sm font-semibold text-slate-500">days</span>
                    </div>
                    <div class="mt-2 text-xs text-slate-600">
                        Due Date: <strong class="text-slate-800"><?= !empty($lease['rent_due_date']) ? date('M j, Y', strtotime($lease['rent_due_date'])) : 'Oct 8, 2026' ?></strong>
                    </div>
                    <span class="block text-[11px] text-slate-400 mt-1">Annual: NGN <?= number_format((float)($lease['rent_amount'] ?? 1200000), 2) ?></span>
                </div>

                <!-- Card 3: Indigo (Assigned Caretaker) -->
                <div class="bg-white border border-slate-200 border-t-4 border-t-indigo-600 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between text-xs text-slate-500 font-semibold mb-2">
                        <span>Estate Caretaker</span>
                        <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">🛠️</span>
                    </div>
                    <div class="text-base font-bold text-slate-900 truncate">
                        <?= htmlspecialchars($caretaker['full_name'] ?? 'Musa Danjuma') ?>
                    </div>
                    <div class="mt-1 text-xs text-indigo-600 font-medium font-mono">
                        <?= htmlspecialchars($caretaker['phone_number'] ?? '+234 803 000 0002') ?>
                    </div>
                    <button type="button" @click="currentTab = 'maintenance'" class="mt-2 text-[11px] font-bold text-[#1D4ED8] hover:underline flex items-center gap-1">
                        <span>Direct Channel</span>
                        <span>→</span>
                    </button>
                </div>

                <!-- Card 4: Amber (Pending Levies & Bills) -->
                <div class="bg-white border border-slate-200 border-t-4 border-t-amber-500 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between text-xs text-slate-500 font-semibold mb-2">
                        <span>Upcoming Dues</span>
                        <span class="w-6 h-6 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs">💳</span>
                    </div>
                    <div class="text-2xl font-black text-slate-900">
                        <?= count($pendingBills) ?> <span class="text-sm font-semibold text-slate-500">items</span>
                    </div>
                    <div class="mt-2 text-xs text-amber-700 font-semibold">
                        Estate Levies & Sanitation
                    </div>
                    <button type="button" @click="currentTab = 'payments'" class="block text-[11px] font-bold text-[#1D4ED8] hover:underline mt-1">Review & Settle →</button>
                </div>

            </div>

            <!-- Two Column Overview: Tenancy Seal & Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Left: Certified Tenancy Agreement Snapshot (Emerald Accent) -->
                <div class="lg:col-span-7 bg-white border border-slate-200/90 border-t-4 border-t-emerald-600 rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                📜
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900">Certified Tenancy Agreement & Legal Seal</h2>
                                <p class="text-[11px] text-slate-500">Statutory residential contract authenticated under Nigerian Tenancy Laws.</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full <?= $isSigned ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                            <?= $isSigned ? '✓ Sealed' : '⏳ In Progress' ?>
                        </span>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200/60">
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Landlord</span>
                                <span class="font-bold text-slate-900"><?= htmlspecialchars($landlord['full_name'] ?? 'Chief Ibrahim Bello') ?></span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Property Location</span>
                                <span class="font-bold text-slate-900"><?= htmlspecialchars($lease['property_title'] ?? 'Unity Estate') ?></span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Tenancy Term</span>
                                <span class="font-medium text-slate-800"><?= !empty($lease['rent_start_date']) ? date('M j, Y', strtotime($lease['rent_start_date'])) : 'Oct 8, 2025' ?> — <?= !empty($lease['rent_due_date']) ? date('M j, Y', strtotime($lease['rent_due_date'])) : 'Oct 8, 2026' ?></span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Cryptographic Seal</span>
                                <span class="font-mono text-[10px] text-emerald-700 font-bold">SHA-256 Verified</span>
                            </div>
                        </div>

                        <div class="pt-2 flex items-center justify-between">
                            <span class="text-slate-500 text-[11px]">Audit Hash: <code class="text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded"><?= substr($lease['document_sha256_hash'] ?? 'df0c4e5ab3b9a1b4', 0, 16) ?>...</code></span>
                            <a href="/agreement/preview?id=<?= (int)($lease['id'] ?? 1) ?>" class="bg-blue-50 text-[#1D4ED8] hover:bg-blue-100 border border-blue-200 font-bold px-3 py-1.5 rounded-xl transition inline-flex items-center gap-1.5">
                                <span>Preview Signed Contract</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right: Assigned Caretaker Direct Help & Emergency Notice -->
                <div class="lg:col-span-5 bg-white border border-slate-200/90 border-t-4 border-t-blue-600 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#1D4ED8] flex items-center justify-center font-bold">
                                🛡️
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900">Assigned Caretaker & Support</h2>
                                <p class="text-[11px] text-slate-500">Your first point of contact for repairs and questions.</p>
                            </div>
                        </div>

                        <div class="p-3 bg-blue-50/60 border border-blue-200/70 rounded-xl text-xs space-y-2 mb-4">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">On-Site Manager:</span>
                                <strong class="text-slate-900"><?= htmlspecialchars($caretaker['full_name'] ?? 'Musa Danjuma') ?></strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Phone:</span>
                                <strong class="text-[#1D4ED8] font-mono"><?= htmlspecialchars($caretaker['phone_number'] ?? '+234 803 000 0002') ?></strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Email:</span>
                                <span class="text-slate-700"><?= htmlspecialchars($caretaker['email'] ?? 'caretaker.idu@ogalandlord.ng') ?></span>
                            </div>
                        </div>

                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            🔒 <strong>Privacy Assurance</strong>: Maintenance requests are resolved directly between you and your Caretaker. Landlords only see concerns if capital repairs require owner funding authorization.
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <button type="button" @click="openTicketModal()" class="w-full bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-bold py-2 rounded-xl text-xs transition flex items-center justify-center gap-1.5 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            <span>Lodge Concern to Caretaker</span>
                        </button>
                    </div>
                </div>

            </div>

        </div>

        <!-- ================= TAB 2: TENANCY AGREEMENT ================= -->
        <div x-show="currentTab === 'agreement'" x-transition class="space-y-6">
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-emerald-600 rounded-2xl p-6 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
                            📜
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Official Tenancy Agreement & Legal Covenant</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Signed residential contract between Landlord and Tenant.</p>
                        </div>
                    </div>
                    <a href="/agreement/preview?id=<?= (int)($lease['id'] ?? 1) ?>" class="bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-xs transition inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Open Document Reader</span>
                    </a>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                    <div class="space-y-4">
                        <h3 class="text-xs font-extrabold uppercase text-slate-400 tracking-wider">Premises & Terms</h3>
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 space-y-2.5">
                            <div class="flex justify-between py-1 border-b border-slate-200/50">
                                <span class="text-slate-500">Demised Premises:</span>
                                <strong class="text-slate-900"><?= htmlspecialchars($lease['unit_number'] ?? 'Unit 1A') ?> (<?= htmlspecialchars($lease['apartment_type'] ?? '2-Bedroom Apartment') ?>)</strong>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-200/50">
                                <span class="text-slate-500">Estate Property:</span>
                                <span class="text-slate-900 font-semibold"><?= htmlspecialchars($lease['property_title'] ?? 'PHDL Unity Estate') ?></span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-200/50">
                                <span class="text-slate-500">Annual Rent:</span>
                                <strong class="text-slate-900">NGN <?= number_format((float)($lease['rent_amount'] ?? 1200000), 2) ?></strong>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-200/50">
                                <span class="text-slate-500">Rent Tenure:</span>
                                <span class="text-slate-800 font-medium"><?= !empty($lease['rent_start_date']) ? date('M j, Y', strtotime($lease['rent_start_date'])) : 'Oct 8, 2025' ?> — <?= !empty($lease['rent_due_date']) ? date('M j, Y', strtotime($lease['rent_due_date'])) : 'Oct 8, 2026' ?></span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-slate-500">Legal Status:</span>
                                <span class="text-emerald-700 font-bold">✓ Fully Executed & In Force</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-xs font-extrabold uppercase text-slate-400 tracking-wider">Cryptographic Audit Certificate</h3>
                        <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/30 space-y-2.5">
                            <div class="flex items-center gap-2 text-emerald-800 font-bold mb-1">
                                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                <span>Tamper-Evident SHA-256 Digital Seal</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                This agreement is cryptographically hashed. Any modification after signature automatically invalidates the document seal.
                            </p>
                            <div class="p-2.5 bg-white border border-emerald-200 rounded-lg">
                                <span class="text-[10px] text-slate-400 uppercase font-bold block mb-1">Fingerprint (SHA-256):</span>
                                <code class="text-[11px] font-mono text-emerald-900 break-all select-all"><?= htmlspecialchars($lease['document_sha256_hash'] ?? 'df0c4e5ab3b9a1b4be92676c9633adfa9f82d94fc0ac74900ef3c33371ae6181') ?></code>
                            </div>
                            <div class="text-[11px] text-slate-500 pt-1 flex justify-between">
                                <span>Signed Timestamp: <?= !empty($lease['tenant_signed_at']) ? date('M j, Y, H:i:s', strtotime($lease['tenant_signed_at'])) : 'Verified' ?></span>
                                <span class="text-emerald-700 font-bold">Nigerian Evidence Act 2011</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= TAB 3: MAINTENANCE & CONCERNS ================= -->
        <div x-show="currentTab === 'maintenance'" x-transition class="space-y-6">

            <!-- Privacy Notice Banner (Requirement 5) -->
            <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 flex items-start gap-3 text-xs">
                <div class="w-7 h-7 rounded-lg bg-[#1D4ED8] text-white flex items-center justify-center font-bold text-sm shrink-0">
                    🔒
                </div>
                <div class="leading-relaxed">
                    <strong class="text-blue-950 font-bold block">Strict Privacy Guarantee (Caretaker-Direct Channel)</strong>
                    <span class="text-blue-900">
                        Maintenance complaints are dispatched directly to your property Caretaker (<strong><?= htmlspecialchars($caretaker['full_name'] ?? 'Musa Danjuma') ?></strong>). To avoid operational clutter, the Landlord <em>cannot</em> view routine ticket conversations unless the Caretaker explicitly tags or escalates the ticket for owner capital expenditure authorization.
                    </span>
                </div>
            </div>

            <!-- Header with Action -->
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-amber-500 rounded-2xl p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                        🛠️
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Maintenance Tickets & Incident Logs</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Interactive issue tracking directly with your Caretaker.</p>
                    </div>
                </div>
                <button type="button" @click="openTicketModal()" class="bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>+ Lodge New Concern</span>
                </button>
            </div>

            <!-- Ticket Cards List -->
            <div class="space-y-4">
                <?php if (empty($tickets)): ?>
                    <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center text-xs text-slate-500 shadow-xs">
                        <span class="text-3xl block mb-2">🎉</span>
                        <h3 class="text-sm font-bold text-slate-800">No active maintenance complaints</h3>
                        <p class="mt-1">Everything in your unit is operating normally. Click "+ Lodge New Concern" if you require assistance.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($tickets as $t): 
                        $isEscalated = ((int)($t['is_escalated_to_landlord'] ?? 0) === 1);
                        $isOpen = in_array(strtoupper($t['status'] ?? 'OPEN'), ['OPEN', 'IN_REVIEW', 'IN_PROGRESS', 'ESCALATED_TO_LANDLORD'], true);
                    ?>
                        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 hover:border-slate-300 transition shadow-xs">
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap mb-1.5">
                                        <span class="font-mono text-xs font-black text-[#1D4ED8] bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-md">
                                            <?= htmlspecialchars($t['ticket_code'] ?? 'TKT-2026-0001') ?>
                                        </span>
                                        <span class="text-[11px] font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">
                                            <?= htmlspecialchars($t['category'] ?? 'General') ?>
                                        </span>
                                        <span class="text-xs text-slate-400">• <?= date('M j, Y', strtotime($t['created_at'] ?? 'now')) ?></span>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-900"><?= htmlspecialchars($t['title']) ?></h3>
                                    <p class="text-xs text-slate-600 mt-1 leading-relaxed"><?= htmlspecialchars($t['description']) ?></p>

                                    <?php if (!empty($t['photo_url'])): ?>
                                        <div class="mt-2.5 flex items-center gap-2">
                                            <a href="<?= htmlspecialchars($t['photo_url']) ?>" target="_blank"
                                               class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-[11px] border border-blue-200 transition">
                                                <span>📷 View Issue Photo Attachment</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($isEscalated): ?>
                                        <div class="mt-2.5 p-2.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 flex items-start gap-2">
                                            <span class="text-amber-600 font-bold">⚡ Tagged to Landlord:</span>
                                            <span><?= htmlspecialchars($t['escalation_reason'] ?? 'Caretaker requested owner review.') ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="flex flex-col sm:items-end gap-2 shrink-0">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold <?= $isOpen ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' ?>">
                                        <?= htmlspecialchars($t['status'] ?? 'OPEN') ?>
                                    </span>
                                    <span class="text-[11px] text-slate-400">Assigned: <strong><?= htmlspecialchars($caretaker['full_name'] ?? 'Musa Danjuma') ?></strong></span>
                                </div>
                            </div>

                            <div class="mt-4 pt-3.5 border-t border-slate-100 flex items-center justify-between text-xs">
                                <span class="text-slate-500 font-medium">
                                    💬 <?= (int)($t['message_count'] ?? 1) ?> messages in thread
                                </span>
                                <button type="button" @click="openChatThread(<?= (int)$t['id'] ?>)" class="bg-blue-50 text-[#1D4ED8] hover:bg-blue-100 font-bold px-3.5 py-1.5 rounded-xl border border-blue-200 transition flex items-center gap-1.5">
                                    <span>Open Interactive Chat</span>
                                    <span>→</span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>

        <!-- ================= TAB 4: PAYMENTS, LEVIES & RECEIPTS ================= -->
        <div x-show="currentTab === 'payments'" x-transition class="space-y-6">

            <!-- Payment Rules Explanation (Requirement 7) -->
            <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-200 flex items-start gap-3 text-xs">
                <div class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                    💳
                </div>
                <div class="leading-relaxed">
                    <strong class="text-indigo-950 font-bold block">Payment Stamping & Verification Workflow</strong>
                    <span class="text-indigo-900">
                        • <strong>Rent Payments (Landlord)</strong>: For legal safety, Rent payments require confirmation by the Landlord or Caretaker before official stamped receipts are issued.<br>
                        • <strong>Estate Levies & Sanitation (Estate)</strong>: Facility charges and waste evacuation dues paid via Gateway (Paystack/Flutterwave) bypass individual landlord confirmation and are stamped immediately.
                    </span>
                </div>
            </div>

            <!-- Connected Landlord Payment Gateway & Bank Account Card -->
            <div class="p-5 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 text-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🏦</span>
                        <strong class="text-slate-900 font-extrabold text-sm">Official Landlord Bank Account & Settlement Gateway</strong>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-white text-[#1D4ED8] border border-blue-200 shadow-2xs">
                        <span>● Paystack & Bank Settled</span>
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 bg-white/90 backdrop-blur-xs rounded-xl border border-blue-100 font-mono text-[11px]">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Bank Name</span>
                        <strong class="text-slate-900"><?= htmlspecialchars($paymentDetails['bank_name'] ?? 'Zenith Bank PLC') ?></strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Account Number</span>
                        <strong class="text-[#1D4ED8] font-black text-sm select-all"><?= htmlspecialchars($paymentDetails['account_number'] ?? '1014529088') ?></strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Account Name</span>
                        <strong class="text-slate-900 truncate block"><?= htmlspecialchars($paymentDetails['account_name'] ?? 'Bello Real Estate Holdings') ?></strong>
                    </div>
                </div>
            </div>

            <!-- Upcoming Dues Catalog -->
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-[#1D4ED8] rounded-2xl p-5 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#1D4ED8] flex items-center justify-center font-bold">
                            📋
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Scheduled Levies & Service Charges</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Pay online or via direct bank transfer.</p>
                        </div>
                    </div>
                    <button type="button" @click="openPaymentModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-xs transition">
                        + Custom Payment
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                    <!-- Bill 1: Annual Rent -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#1D4ED8] bg-blue-50 border border-blue-200 px-2 py-0.5 rounded">Landlord Beneficiary</span>
                            <h3 class="font-extrabold text-slate-900 text-sm mt-2">Annual Tenancy Rent</h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">Due: <?= !empty($lease['rent_due_date']) ? date('M j, Y', strtotime($lease['rent_due_date'])) : 'Oct 8, 2026' ?></p>
                            <div class="mt-3 text-lg font-black text-slate-900">NGN <?= number_format((float)($lease['rent_amount'] ?? 1200000), 2) ?></div>
                        </div>
                        <button type="button" @click="selectBillAndPay('RENT', 'LANDLORD', 'Annual Tenancy Rent', <?= (float)($lease['rent_amount'] ?? 1200000) ?>)" class="mt-4 w-full bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-bold py-2 rounded-xl text-xs transition">
                            Pay Rent →
                        </button>
                    </div>

                    <!-- Bill 2: Estate Facility Levy -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded">Estate Direct (Bypassed)</span>
                            <h3 class="font-extrabold text-slate-900 text-sm mt-2">Estate Facility & Security Levy</h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">Security, streetlighting, water treatment</p>
                            <div class="mt-3 text-lg font-black text-slate-900">NGN 45,000.00</div>
                        </div>
                        <button type="button" @click="selectBillAndPay('ESTATE_LEVY', 'ESTATE', 'Estate Facility & Security Levy', 45000)" class="mt-4 w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 rounded-xl text-xs transition">
                            Pay Levy (Instant Stamp) →
                        </button>
                    </div>

                    <!-- Bill 3: Waste Evacuation -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded">Estate Direct (Bypassed)</span>
                            <h3 class="font-extrabold text-slate-900 text-sm mt-2">Waste Evacuation & Sanitation</h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">AEPB municipal waste management</p>
                            <div class="mt-3 text-lg font-black text-slate-900">NGN 12,000.00</div>
                        </div>
                        <button type="button" @click="selectBillAndPay('UTILITY_BILL', 'ESTATE', 'Waste Evacuation & Sanitation', 12000)" class="mt-4 w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 rounded-xl text-xs transition">
                            Pay Waste Dues →
                        </button>
                    </div>
                </div>
            </div>

            <!-- Payment Records & Stamped Receipts Table -->
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-emerald-600 rounded-2xl shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            📜
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Payment History & Certified Receipts</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Official receipts generated and verified on the platform.</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/90 border-b border-slate-100 text-slate-500 uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="py-3.5 px-4">Receipt #</th>
                                <th class="py-3.5 px-4">Title / Purpose</th>
                                <th class="py-3.5 px-4">Amount</th>
                                <th class="py-3.5 px-4">Channel</th>
                                <th class="py-3.5 px-4">Beneficiary</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-right">Official Receipt</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($payments)): ?>
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-slate-400">
                                        No payments submitted yet. Make your first payment above.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($payments as $p): 
                                    $isConfirmed = ($p['status'] === 'CONFIRMED');
                                ?>
                                    <tr class="hover:bg-slate-50/60 transition <?= $isConfirmed ? 'border-l-4 border-l-emerald-500' : 'border-l-4 border-l-amber-400' ?>">
                                        <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                            <?= htmlspecialchars($p['receipt_number']) ?>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="font-bold text-slate-900 block"><?= htmlspecialchars($p['title']) ?></span>
                                            <span class="text-[10px] text-slate-400"><?= date('M j, Y, H:i', strtotime($p['created_at'])) ?></span>
                                        </td>
                                        <td class="py-3.5 px-4 font-black text-slate-900">
                                            NGN <?= number_format((float)$p['amount'], 2) ?>
                                        </td>
                                        <td class="py-3.5 px-4 font-medium text-slate-600">
                                            <?= htmlspecialchars($p['payment_method']) ?>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded <?= $p['beneficiary_type'] === 'ESTATE' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-[#1D4ED8]' ?>">
                                                <?= htmlspecialchars($p['beneficiary_type']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <?php if ($isConfirmed): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold text-[11px] bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    ✓ Confirmed
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold text-[11px] bg-amber-50 text-amber-800 border border-amber-200">
                                                    ⏳ Awaiting Confirmation
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <a href="/tenant/receipt?number=<?= urlencode($p['receipt_number']) ?>" target="_blank" class="text-xs font-bold text-[#1D4ED8] hover:underline inline-flex items-center gap-1">
                                                <span>View Stamped Receipt</span>
                                                <span>↗</span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ================= TAB 5: RESIDENT PROFILE ================= -->
        <div x-show="currentTab === 'profile'" x-transition class="space-y-6">
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-indigo-600 rounded-2xl p-6 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg">
                            👤
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Resident Profile & Digital KYC Verification</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Verified resident credentials & tenancy onboarding records.</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                        <span>🛡️ Verified Resident (NIN/BVN Checked)</span>
                    </span>
                </div>

                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Full Name</span>
                        <strong class="text-slate-900 text-sm"><?= htmlspecialchars($tenant['full_name'] ?? 'Amara Okafor') ?></strong>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Portal Email</span>
                        <span class="text-slate-900 font-medium"><?= htmlspecialchars($tenant['email'] ?? 'amara.okafor@example.com') ?></span>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Registered Phone</span>
                        <span class="text-slate-900 font-mono"><?= htmlspecialchars($tenant['phone_number'] ?? '+234 803 000 0003') ?></span>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Identity Verification (KYC)</span>
                        <span class="text-emerald-700 font-bold">NIN: 5410 •••• 9021 (NIMC Verified)</span>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Employer & Occupation</span>
                        <span class="text-slate-800 font-medium"><?= htmlspecialchars($tenant['employer_name'] ?? 'Central Bank of Nigeria (CBN)') ?> — <?= htmlspecialchars($tenant['occupation'] ?? 'Systems Engineer') ?></span>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Guarantor Referee</span>
                        <span class="text-slate-800 font-medium"><?= htmlspecialchars($tenant['guarantor_name'] ?? 'Barrister Chidi Okafor') ?> (<?= htmlspecialchars($tenant['guarantor_phone'] ?? '+234 803 999 8877') ?>)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= TAB 6: ESTATE NOTICE BOARD ================= -->
        <div x-show="currentTab === 'notice_board'" x-transition class="space-y-6">
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-sky-600 rounded-2xl p-6 shadow-xs">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-lg">
                        📢
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Estate Digital Notice Board & Announcements</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Official community announcements published by your Landlord and Caretaker.</p>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    <?php if (empty($announcements)): ?>
                        <div class="p-8 text-center text-xs text-slate-400 border border-dashed border-slate-200 rounded-2xl bg-slate-50">
                            No announcements posted yet on the community notice board.
                        </div>
                    <?php else: ?>
                        <?php foreach ($announcements as $ann): 
                            $isUrgent = ($ann['priority'] === 'URGENT');
                        ?>
                            <div class="p-5 rounded-2xl border <?= $isUrgent ? 'border-rose-200 bg-rose-50/25 border-l-4 border-l-rose-500' : 'border-slate-200 bg-slate-50/50 border-l-4 border-l-sky-500' ?> text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-extrabold text-slate-900 text-sm"><?= htmlspecialchars($ann['title']) ?></h3>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?= $isUrgent ? 'bg-rose-100 text-rose-800' : 'bg-sky-100 text-sky-800' ?>">
                                        <?= htmlspecialchars($ann['priority']) ?>
                                    </span>
                                </div>
                                <p class="text-slate-600 leading-relaxed"><?= htmlspecialchars($ann['message']) ?></p>
                                <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between text-[10px] text-slate-400">
                                    <span>Broadcast by: <strong><?= htmlspecialchars($ann['sender_name'] ?? 'Estate Caretaker') ?> (<?= htmlspecialchars($ann['sender_role']) ?>)</strong></span>
                                    <span><?= date('M j, Y, H:i', strtotime($ann['created_at'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ================= TAB 7: FACILITY RESERVATIONS ================= -->
        <div x-show="currentTab === 'facilities'" x-transition class="space-y-6">
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-amber-500 rounded-2xl p-6 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
                            🏊
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Estate Shared Facility Reservations</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Book the community Clubhouse, Swimming Pool, Tennis Court, or Fitness Gym.</p>
                        </div>
                    </div>
                    <button type="button" @click="showBookingModal = true"
                            class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-1.5 self-start sm:self-auto">
                        <span>+ Book Estate Facility</span>
                    </button>
                </div>

                <div class="mt-6 space-y-4">
                    <?php if (empty($facilityBookings)): ?>
                        <div class="p-8 text-center text-xs text-slate-400 border border-dashed border-slate-200 rounded-2xl bg-slate-50">
                            You have no active facility bookings. Click "+ Book Estate Facility" to make a reservation.
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($facilityBookings as $bk): 
                                $isApproved = ($bk['status'] === 'APPROVED');
                            ?>
                                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 text-xs space-y-2 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5">
                                            <strong class="text-slate-900 text-sm"><?= htmlspecialchars($bk['facility_name']) ?></strong>
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?= $isApproved ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                                                <?= htmlspecialchars($bk['status']) ?>
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-slate-600 space-y-1">
                                            <div>📅 Date: <strong><?= htmlspecialchars($bk['booking_date']) ?></strong></div>
                                            <div>⏰ Time Slot: <strong><?= htmlspecialchars($bk['time_slot']) ?></strong></div>
                                            <div>👥 Guests: <strong><?= (int)($bk['guest_count'] ?? 1) ?> People</strong></div>
                                            <?php if (!empty($bk['purpose'])): ?>
                                                <div>🎯 Purpose: <?= htmlspecialchars($bk['purpose']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="pt-2 border-t border-slate-200 text-[10px] text-slate-400 flex justify-between">
                                        <span>Status: <?= $isApproved ? 'Confirmed by Caretaker' : 'Pending Review' ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </main>

</div>

<!-- ================= MODAL 1: LODGE MAINTENANCE CONCERN ================= -->
<div x-show="showTicketModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" x-transition>
    <div @click.away="showTicketModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2">
                <span class="text-lg">🛠️</span>
                <h3 class="text-base font-bold text-slate-900">Lodge Maintenance Concern to Caretaker</h3>
            </div>
            <button @click="showTicketModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
        </div>

        <form @submit.prevent="submitTicket()" class="space-y-4">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Issue Category</label>
                <select x-model="ticketForm.category" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8]">
                    <option value="Plumbing">Plumbing (Pipes, Taps, Toilets, Drains)</option>
                    <option value="Electrical">Electrical (Meters, Sockets, Wiring, Inverters)</option>
                    <option value="Air Conditioning">Air Conditioning / Cooling</option>
                    <option value="Structural & Carpentry">Structural, Doors, Locks & Carpentry</option>
                    <option value="Security">Security & Access Gates</option>
                    <option value="Other">Other Operational Concern</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Brief Title / Headline</label>
                <input type="text" x-model="ticketForm.title" required placeholder="e.g. Master bedroom shower tap leaking"
                       class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8]">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Detailed Description</label>
                <textarea x-model="ticketForm.description" rows="3" required placeholder="Describe what is occurring, location in the unit, and when it started..."
                          class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8]"></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Urgency Priority</label>
                <select x-model="ticketForm.priority" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8]">
                    <option value="ROUTINE">Routine (Inspect during standard hours)</option>
                    <option value="MEDIUM">Medium (Within 24-48 hours)</option>
                    <option value="HIGH">High Urgency (Water leak, power outage)</option>
                    <option value="EMERGENCY">Emergency (Severe hazard)</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Photo Evidence / Issue Attachment (Optional)</label>
                <div class="flex gap-2">
                    <input type="text" x-model="ticketForm.photo_url" placeholder="Paste image URL or attach snapshot..."
                           class="flex-1 px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8]">
                    <button type="button" @click="ticketForm.photo_url = 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=800&q=80'"
                            class="px-2.5 py-2 text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl border border-slate-200 shrink-0">
                        📸 Sample Photo
                    </button>
                </div>
                <template x-if="ticketForm.photo_url">
                    <div class="mt-2 flex items-center gap-2 text-[11px] text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200">
                        <span>📷 Photo attached: <span class="font-mono text-[10px]" x-text="ticketForm.photo_url.slice(0, 45) + '...'"></span></span>
                    </div>
                </template>
            </div>

            <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-[11px] text-blue-900 leading-relaxed">
                🔒 Handled directly by Caretaker <strong><?= htmlspecialchars($caretaker['full_name'] ?? 'Musa Danjuma') ?></strong>. The conversation thread remains private unless escalated.
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="showTicketModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" :disabled="submittingTicket" class="px-5 py-2 rounded-xl bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-bold transition flex items-center gap-1.5 shadow-xs">
                    <span x-text="submittingTicket ? 'Submitting...' : 'Dispatch Ticket to Caretaker'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL 2: INTERACTIVE CHAT THREAD ================= -->
<div x-show="showChatModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" x-transition>
    <div @click.away="showChatModal = false" class="bg-white rounded-2xl max-w-2xl w-full flex flex-col h-[600px] shadow-2xl border border-slate-200 text-xs overflow-hidden">
        
        <!-- Chat Header -->
        <div class="p-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-mono font-black text-xs text-[#1D4ED8] bg-blue-50 px-2 py-0.5 rounded border border-blue-200" x-text="activeTicket.ticket_code"></span>
                    <span class="font-bold text-slate-900 text-sm" x-text="activeTicket.title"></span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5 block">Caretaker: <strong><?= htmlspecialchars($caretaker['full_name'] ?? 'Musa Danjuma') ?></strong></span>
            </div>
            <button @click="showChatModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-base">✕</button>
        </div>

        <!-- Chat Messages Container -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50/50" id="chatThreadBox">
            <template x-for="msg in activeMessages" :key="msg.id">
                <div>
                    <!-- System / Escalation Notice -->
                    <template x-if="msg.sender_role === 'SYSTEM' || msg.sender_role === 'CARETAKER' && msg.message.includes('[ESCALATED')">
                        <div class="p-2.5 bg-amber-50 border border-amber-200 rounded-xl text-center text-[11px] text-amber-900 font-medium my-2">
                            <span x-text="msg.message"></span>
                        </div>
                    </template>

                    <!-- Regular Message: Tenant (Right) -->
                    <template x-if="msg.sender_role === 'TENANT'">
                        <div class="flex flex-col items-end">
                            <div class="max-w-md bg-[#1D4ED8] text-white p-3 rounded-2xl rounded-br-xs shadow-xs text-xs">
                                <span class="block text-[10px] text-blue-200 font-bold mb-0.5" x-text="msg.sender_name + ' (You)'"></span>
                                <p x-text="msg.message" class="leading-relaxed"></p>
                            </div>
                            <span class="text-[9px] text-slate-400 mt-1" x-text="msg.created_at"></span>
                        </div>
                    </template>

                    <!-- Regular Message: Caretaker / Landlord (Left) -->
                    <template x-if="msg.sender_role !== 'TENANT' && !msg.message.includes('[ESCALATED')">
                        <div class="flex flex-col items-start">
                            <div class="max-w-md bg-white border border-slate-200 text-slate-800 p-3 rounded-2xl rounded-bl-xs shadow-xs text-xs">
                                <span class="block text-[10px] font-bold mb-0.5" :class="msg.sender_role === 'LANDLORD' ? 'text-purple-700' : 'text-indigo-700'" x-text="msg.sender_name"></span>
                                <p x-text="msg.message" class="leading-relaxed"></p>
                            </div>
                            <span class="text-[9px] text-slate-400 mt-1" x-text="msg.created_at"></span>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Chat Reply Box -->
        <div class="p-3 bg-white border-t border-slate-200">
            <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                <input type="text" x-model="newMessage" placeholder="Type a response to your caretaker..." required
                       class="flex-1 px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8] text-xs">
                <button type="submit" :disabled="sendingMessage" class="bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-bold px-4 py-2.5 rounded-xl shadow-xs transition text-xs flex items-center gap-1">
                    <span x-text="sendingMessage ? '...' : 'Send'"></span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>
        </div>

    </div>
</div>

<!-- ================= MODAL 3: MAKE A PAYMENT ================= -->
<div x-show="showPaymentModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" x-transition>
    <div @click.away="showPaymentModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2">
                <span class="text-lg">💳</span>
                <h3 class="text-base font-bold text-slate-900">Make a Payment</h3>
            </div>
            <button @click="showPaymentModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
        </div>

        <form @submit.prevent="submitPayment()" class="space-y-4">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Payment Category & Beneficiary</label>
                <select x-model="paymentForm.payment_type" @change="onPaymentTypeChange()" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8]">
                    <option value="RENT">Annual Rent (Beneficiary: Landlord - Requires Confirmation)</option>
                    <option value="ESTATE_LEVY">Estate Facility & Security Levy (Estate - Instant Gateway Verification)</option>
                    <option value="UTILITY_BILL">Waste Evacuation / Utilities (Estate - Instant Gateway Verification)</option>
                    <option value="OTHER">Other Tenant Contribution</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Title / Description</label>
                <input type="text" x-model="paymentForm.title" required
                       class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8]">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Amount to Pay (NGN)</label>
                <input type="number" step="100" min="100" x-model="paymentForm.amount" required
                       class="w-full px-3 py-2 rounded-xl border border-slate-300 font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8]">
            </div>

            <!-- Payment Method -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Payment Channel</label>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" @click="paymentForm.payment_method = 'PAYSTACK'"
                            :class="paymentForm.payment_method === 'PAYSTACK' ? 'bg-blue-50 border-[#1D4ED8] text-[#1D4ED8] font-bold' : 'border-slate-200 text-slate-600'"
                            class="p-2.5 rounded-xl border text-center transition">
                        <span class="block font-extrabold text-xs">Paystack</span>
                        <span class="text-[10px] text-slate-400">Card / USSD</span>
                    </button>
                    <button type="button" @click="paymentForm.payment_method = 'FLUTTERWAVE'"
                            :class="paymentForm.payment_method === 'FLUTTERWAVE' ? 'bg-amber-50 border-amber-500 text-amber-900 font-bold' : 'border-slate-200 text-slate-600'"
                            class="p-2.5 rounded-xl border text-center transition">
                        <span class="block font-extrabold text-xs">Flutterwave</span>
                        <span class="text-[10px] text-slate-400">Gateway</span>
                    </button>
                    <button type="button" @click="paymentForm.payment_method = 'BANK_TRANSFER'"
                            :class="paymentForm.payment_method === 'BANK_TRANSFER' ? 'bg-emerald-50 border-emerald-600 text-emerald-900 font-bold' : 'border-slate-200 text-slate-600'"
                            class="p-2.5 rounded-xl border text-center transition">
                        <span class="block font-extrabold text-xs">Bank Transfer</span>
                        <span class="text-[10px] text-slate-400">Direct Deposit</span>
                    </button>
                </div>
            </div>

            <!-- Bank Transfer Details (If Selected) -->
            <div x-show="paymentForm.payment_method === 'BANK_TRANSFER'" class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-2.5">
                <span class="text-[11px] font-bold text-slate-700 block">Bank Account for Transfer:</span>
                <div class="p-2.5 bg-white border border-slate-200 rounded-lg text-xs space-y-1">
                    <div class="flex justify-between"><span>Bank:</span><strong class="text-slate-900">Zenith Bank Plc</strong></div>
                    <div class="flex justify-between"><span>Account Name:</span><strong class="text-slate-900">Oga Landlord / PHDL Unity Escrow</strong></div>
                    <div class="flex justify-between"><span>Account Number:</span><strong class="font-mono text-base text-[#1D4ED8]">1012345678</strong></div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Sender Bank Account Name</label>
                    <input type="text" x-model="paymentForm.bank_transfer_sender_name" placeholder="e.g. Amara Okafor"
                           class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Transfer Session ID / Narration Reference</label>
                    <input type="text" x-model="paymentForm.bank_transfer_reference" placeholder="e.g. TRF/2026/09/88219"
                           class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs">
                </div>
            </div>

            <!-- Workflow Explanation Notice -->
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-900 leading-relaxed">
                <template x-if="paymentForm.beneficiary_type === 'ESTATE' && (paymentForm.payment_method === 'PAYSTACK' || paymentForm.payment_method === 'FLUTTERWAVE')">
                    <span>⚡ <strong>Instant Verification</strong>: Estate levies paid via gateway are directly verified by the estate system and your stamped receipt will be available immediately.</span>
                </template>
                <template x-if="paymentForm.beneficiary_type === 'LANDLORD' || paymentForm.payment_method === 'BANK_TRANSFER'">
                    <span>⏳ <strong>Pending Confirmation</strong>: This payment will be submitted to the Caretaker and Landlord for verification. Once approved, your official stamped receipt is unlocked.</span>
                </template>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="showPaymentModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" :disabled="submittingPayment" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition flex items-center gap-1.5 shadow-xs">
                    <span x-text="submittingPayment ? 'Processing...' : 'Authorize & Pay'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL 4: BOOK ESTATE FACILITY ================= -->
<div x-show="showBookingModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" x-transition>
    <div @click.away="showBookingModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2">
                <span class="text-lg">🏊</span>
                <h3 class="text-base font-bold text-slate-900">Book Community Facility</h3>
            </div>
            <button @click="showBookingModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
        </div>

        <form @submit.prevent="submitBooking()" class="space-y-4">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Estate Facility</label>
                <select x-model="bookingForm.facility_name" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8]">
                    <option value="Clubhouse & Event Lawn">Clubhouse & Event Lawn</option>
                    <option value="Swimming Pool & Cabana">Swimming Pool & Cabana</option>
                    <option value="Lawn Tennis Court">Lawn Tennis Court</option>
                    <option value="Community Fitness Gym">Community Fitness Gym</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Reservation Date</label>
                    <input type="date" x-model="bookingForm.booking_date" required min="<?= date('Y-m-d') ?>"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8]">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Expected Guests</label>
                    <input type="number" min="1" max="150" x-model="bookingForm.guest_count" required
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8]">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Time Slot / Duration</label>
                <select x-model="bookingForm.time_slot" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8]">
                    <option value="08:00 - 12:00 (Morning Session)">08:00 - 12:00 (Morning Session)</option>
                    <option value="13:00 - 17:00 (Afternoon Session)">13:00 - 17:00 (Afternoon Session)</option>
                    <option value="18:00 - 22:00 (Evening / Social)">18:00 - 22:00 (Evening / Social)</option>
                    <option value="All Day (09:00 - 20:00)">All Day (09:00 - 20:00)</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Purpose / Event Details</label>
                <textarea x-model="bookingForm.purpose" rows="2.5" required placeholder="e.g. Birthday reception for 15 guests, quiet music only..."
                          class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8]"></textarea>
            </div>

            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-900 leading-relaxed">
                ℹ️ Facility bookings are subject to approval by the Estate Caretaker to avoid scheduling clashes.
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="showBookingModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit" :disabled="submittingBooking" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold transition flex items-center gap-1.5 shadow-xs">
                    <span x-text="submittingBooking ? 'Reserving...' : 'Submit Reservation Request'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function tenantApp() {
    return {
        sidebarOpen: false,
        currentTab: 'overview',
        showTicketModal: false,
        showChatModal: false,
        showPaymentModal: false,
        showBookingModal: false,
        submittingTicket: false,
        submittingPayment: false,
        submittingBooking: false,
        sendingMessage: false,
        activeTicket: {},
        activeMessages: [],
        newMessage: '',

        ticketForm: {
            category: 'Plumbing',
            title: '',
            description: '',
            priority: 'MEDIUM',
            photo_url: ''
        },

        bookingForm: {
            facility_name: 'Clubhouse & Event Lawn',
            booking_date: '<?= date('Y-m-d', strtotime('+3 days')) ?>',
            time_slot: '14:00 - 18:00',
            guest_count: 8,
            purpose: 'Family celebration with close friends'
        },

        paymentForm: {
            payment_type: 'RENT',
            beneficiary_type: 'LANDLORD',
            title: 'Annual Tenancy Rent',
            amount: <?= (float)($lease['rent_amount'] ?? 1200000) ?>,
            payment_method: 'PAYSTACK',
            bank_transfer_sender_name: '<?= addslashes($tenant['full_name'] ?? 'Amara Okafor') ?>',
            bank_transfer_reference: ''
        },

        onPaymentTypeChange() {
            if (this.paymentForm.payment_type === 'RENT') {
                this.paymentForm.beneficiary_type = 'LANDLORD';
                this.paymentForm.title = 'Annual Tenancy Rent';
                this.paymentForm.amount = <?= (float)($lease['rent_amount'] ?? 1200000) ?>;
            } else if (this.paymentForm.payment_type === 'ESTATE_LEVY') {
                this.paymentForm.beneficiary_type = 'ESTATE';
                this.paymentForm.title = 'Estate Facility & Security Levy';
                this.paymentForm.amount = 45000;
            } else if (this.paymentForm.payment_type === 'UTILITY_BILL') {
                this.paymentForm.beneficiary_type = 'ESTATE';
                this.paymentForm.title = 'Waste Evacuation & Sanitation';
                this.paymentForm.amount = 12000;
            } else {
                this.paymentForm.beneficiary_type = 'ESTATE';
                this.paymentForm.title = 'Special Tenant Contribution';
            }
        },

        selectBillAndPay(type, beneficiary, title, amount) {
            this.paymentForm.payment_type = type;
            this.paymentForm.beneficiary_type = beneficiary;
            this.paymentForm.title = title;
            this.paymentForm.amount = amount;
            this.showPaymentModal = true;
        },

        openTicketModal() {
            this.showTicketModal = true;
        },

        openPaymentModal() {
            this.showPaymentModal = true;
        },

        async submitTicket() {
            this.submittingTicket = true;
            try {
                const res = await fetch('/api/v1/tenant/tickets', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.ticketForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Ticket lodged successfully!');
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to lodge ticket.');
                }
            } catch (err) {
                alert('Network error lodging ticket.');
            } finally {
                this.submittingTicket = false;
            }
        },

        async submitBooking() {
            this.submittingBooking = true;
            try {
                const res = await fetch('/api/v1/community/facilities/book', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.bookingForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Facility reservation requested!');
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to request reservation.');
                }
            } catch (err) {
                alert('Network error requesting reservation.');
            } finally {
                this.submittingBooking = false;
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
                        const box = document.getElementById('chatThreadBox');
                        if (box) box.scrollTop = box.scrollHeight;
                    }, 100);
                } else {
                    alert(data.error || 'Could not load conversation thread.');
                }
            } catch (err) {
                alert('Error connecting to ticket thread.');
            }
        },

        async sendMessage() {
            if (!this.newMessage.trim()) return;
            this.sendingMessage = true;
            try {
                const res = await fetch('/api/v1/tickets/' + this.activeTicket.id + '/messages', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: this.newMessage })
                });
                const data = await res.json();
                if (data.success) {
                    this.activeMessages.push({
                        id: data.message_id || Date.now(),
                        sender_role: 'TENANT',
                        sender_name: '<?= addslashes($tenant['full_name'] ?? 'Amara Okafor') ?>',
                        message: this.newMessage,
                        created_at: 'Just now'
                    });
                    this.newMessage = '';
                    setTimeout(() => {
                        const box = document.getElementById('chatThreadBox');
                        if (box) box.scrollTop = box.scrollHeight;
                    }, 50);
                } else {
                    alert(data.error || 'Failed to post message.');
                }
            } catch (err) {
                alert('Network error sending message.');
            } finally {
                this.sendingMessage = false;
            }
        },

        async submitPayment() {
            this.submittingPayment = true;
            try {
                const res = await fetch('/api/v1/tenant/payments', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.paymentForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Payment recorded!');
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to record payment.');
                }
            } catch (err) {
                alert('Network error recording payment.');
            } finally {
                this.submittingPayment = false;
            }
        }
    }
}
</script>

</body>
</html>
