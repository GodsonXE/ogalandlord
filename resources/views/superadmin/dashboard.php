<?php
$allTenants = $allTenants ?? [];
$allLandlords = $allLandlords ?? [];
$allAlertRules = $allAlertRules ?? [];
$allEstates = $allEstates ?? [];
$allArtisans = $allArtisans ?? [];
$allAnnouncements = $allAnnouncements ?? [];
$allBookings = $allBookings ?? [];
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperAdmin Control Center — Oga Landlord</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="h-full text-slate-800 antialiased" x-data="superAdminApp()">

<div class="min-h-full flex flex-col md:flex-row">

    <!-- Mobile Top Header -->
    <div class="md:hidden bg-[#1D4ED8] text-white px-4 py-3 flex items-center justify-between sticky top-0 z-30 shadow-md">
        <div class="flex items-center gap-2">
            <img src="/assets/images/logo.jpg" alt="Oga Landlord Logo" class="h-8 w-auto object-contain rounded bg-white p-0.5">
            <span class="font-extrabold text-white text-sm tracking-tight">Oga<span class="text-blue-200">Landlord</span></span>
            <span class="text-[10px] bg-blue-900 text-white font-bold px-2 py-0.5 rounded-md">SuperAdmin</span>
        </div>
        <button type="button" @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-white hover:bg-white/10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <!-- Collapsible Blue Accent Left Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
           class="fixed md:sticky top-0 z-40 h-screen w-72 bg-[#1D4ED8] text-white flex flex-col justify-between transition-transform duration-200 ease-in-out shadow-xl">
        
        <div class="flex flex-col flex-1 overflow-y-auto">
            <!-- Header -->
            <div class="p-5 border-b border-blue-600/70">
                <a href="/" class="flex items-center gap-3 group">
                    <img src="/assets/images/logo.jpg" alt="Oga Landlord Logo" class="h-11 w-auto object-contain rounded-lg bg-white p-1 shadow-sm transition-transform group-hover:scale-105">
                    <div>
                        <span class="text-lg font-extrabold tracking-tight text-white block leading-tight">Oga<span class="text-blue-200">Landlord</span></span>
                        <span class="text-[10px] uppercase tracking-wider text-blue-200 font-bold">SuperAdmin Center</span>
                    </div>
                </a>

                <!-- Platform Status -->
                <div class="mt-5 p-3 rounded-xl bg-blue-800/80 border border-blue-400/40 shadow-inner">
                    <span class="block text-[10px] uppercase tracking-wider font-bold text-blue-200">System Governance</span>
                    <span class="block font-bold text-xs text-white mt-0.5">Platform Controller</span>
                    <span class="block text-[11px] text-emerald-300 font-semibold mt-1">● 100% Operational & Verified</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5">
                <span class="block px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-blue-200">System Administration</span>
                
                <!-- 1. Telemetry -->
                <button type="button" @click="currentTab = 'telemetry'; sidebarOpen = false"
                        :class="currentTab === 'telemetry' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Platform Telemetry</span>
                </button>

                <!-- 2. Tenants Directory (Review, Export CSV/PDF, Upload) -->
                <button type="button" @click="currentTab = 'tenants'; sidebarOpen = false"
                        :class="currentTab === 'tenants' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Tenants Directory</span>
                    </div>
                    <span class="text-[10px] bg-blue-900/60 text-blue-100 px-2 py-0.5 rounded-md font-bold"><?= count($allTenants ?? []) ?></span>
                </button>

                <!-- 3. Landlords Directory & Multi-Estate Management -->
                <button type="button" @click="currentTab = 'landlords'; sidebarOpen = false"
                        :class="currentTab === 'landlords' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Landlords & Estates</span>
                    </div>
                    <span class="text-[10px] bg-blue-900/60 text-blue-100 px-2 py-0.5 rounded-md font-bold"><?= count($allLandlords ?? []) ?></span>
                </button>

                <!-- 2. Tenancy Agreement Governance Preview -->
                <a href="/agreement/preview"
                   class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs text-blue-100 hover:bg-white/10 hover:text-white font-medium transition text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Tenancy Agreements</span>
                    </div>
                    <span class="text-[10px] bg-emerald-400/30 text-emerald-200 px-2 py-0.5 rounded-md font-bold">Audit Preview</span>
                </a>

                <!-- 3. SaaS Billing Engine -->
                <button type="button" @click="currentTab = 'saas'; sidebarOpen = false"
                        :class="currentTab === 'saas' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>SaaS Billing Engine</span>
                </button>

                <!-- 4. Dunning Scheduler -->
                <button type="button" @click="currentTab = 'dunning'; sidebarOpen = false"
                        :class="currentTab === 'dunning' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Dunning Scheduler Worker</span>
                </button>

                <!-- 5. Deep Estate Operations Audit -->
                <button type="button" @click="currentTab = 'estate_ops'; sidebarOpen = false"
                        :class="currentTab === 'estate_ops' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <span class="text-sm">🏢</span>
                        <span>Estate Operations Audit</span>
                    </div>
                    <span class="text-[10px] bg-blue-900/60 text-blue-100 px-2 py-0.5 rounded-md font-bold"><?= count($allEstates) ?></span>
                </button>

                <!-- 6. Global Alert Rules -->
                <button type="button" @click="currentTab = 'alert_rules'; sidebarOpen = false"
                        :class="currentTab === 'alert_rules' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <span class="text-sm">⚡</span>
                        <span>Global Alert Rules</span>
                    </div>
                    <span class="text-[10px] bg-blue-900/60 text-blue-100 px-2 py-0.5 rounded-md font-bold"><?= count($allAlertRules) ?></span>
                </button>

                <!-- 7. Resident KYC Verification -->
                <button type="button" @click="currentTab = 'kyc_queue'; sidebarOpen = false"
                        :class="currentTab === 'kyc_queue' ? 'bg-white text-[#1D4ED8] font-extrabold shadow-md' : 'text-blue-100 hover:bg-white/10 hover:text-white font-medium'"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition text-left">
                    <div class="flex items-center gap-3">
                        <span class="text-sm">🛡️</span>
                        <span>Resident KYC Queue</span>
                    </div>
                    <span class="text-[10px] bg-blue-900/60 text-blue-100 px-2 py-0.5 rounded-md font-bold"><?= count($allTenants) ?></span>
                </button>
            </nav>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-blue-700/80 bg-blue-900/60 space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-white text-[#1D4ED8] font-extrabold flex items-center justify-center text-xs shadow-sm">
                    FA
                </div>
                <div class="flex-1 min-w-0">
                    <span class="block text-xs font-bold text-white truncate"><?= htmlspecialchars($user['full_name'] ?? 'Alhaji Farouk Al-Mansur') ?></span>
                    <span class="block text-[10px] text-blue-200 truncate"><?= htmlspecialchars($user['email'] ?? 'superadmin@ogalandlord.ng') ?></span>
                </div>
            </div>

            <div class="pt-2 border-t border-blue-800/80 flex flex-col gap-1.5 text-[11px]">
                <span class="text-[10px] uppercase tracking-wider font-bold text-blue-300">Quick Workspace Switch</span>
                <a href="/demo/landlord" class="flex items-center justify-between text-blue-100 hover:text-white py-1 font-medium transition">
                    <span>👔 Landlord Dashboard</span>
                    <span>→</span>
                </a>
                <a href="/demo/caretaker" class="flex items-center justify-between text-blue-100 hover:text-white py-1 font-medium transition">
                    <span>🛠️ Caretaker Operations</span>
                    <span>→</span>
                </a>
                <a href="/logout" class="flex items-center justify-between text-rose-300 hover:text-rose-100 py-1 font-semibold transition mt-0.5">
                    <span>🚪 Sign Out</span>
                    <span>✕</span>
                </a>
            </div>
        </div>

    </aside>

    <!-- Main Content -->
    <main class="flex-1 min-w-0 overflow-y-auto p-4 sm:p-6 lg:p-8">
        
        <header class="mb-8 pb-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">System Administration</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-0.5">Platform Command Center</h1>
                <p class="text-xs text-slate-500 mt-0.5">Manage platform health, inspect SaaS subscription billing, and monitor dunning services.</p>
            </div>
            <a href="/agreement/preview" class="bg-white border border-slate-300 hover:border-[#1D4ED8] text-slate-800 hover:text-[#1D4ED8] text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-2 self-start sm:self-auto">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Audit Tenancy Agreements</span>
            </a>
        </header>

        <!-- TAB 1: TELEMETRY -->
        <div x-show="currentTab === 'telemetry'" x-transition>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <!-- Landlords (Blue) -->
                <div class="bg-white border border-slate-200/90 border-t-4 border-t-[#1D4ED8] rounded-2xl p-5 shadow-xs hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1D4ED8] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-blue-50 text-[#1D4ED8] border border-blue-100 uppercase">Accounts</span>
                    </div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Landlords</span>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1"><?= (int)($metrics['landlords_count'] ?? 1) ?></p>
                    <span class="text-xs text-slate-500 mt-1 block truncate">Chief Ibrahim Bello</span>
                </div>

                <!-- Managed Estates (Indigo) -->
                <div class="bg-white border border-slate-200/90 border-t-4 border-t-indigo-600 rounded-2xl p-5 shadow-xs hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-100 uppercase">Estates</span>
                    </div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Managed Estates</span>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1"><?= (int)($metrics['properties_count'] ?? 1) ?></p>
                    <span class="text-xs text-slate-500 mt-1 block truncate">PHDL Unity Estate, Idu</span>
                </div>

                <!-- Total Units (Emerald) -->
                <div class="bg-white border border-slate-200/90 border-t-4 border-t-emerald-500 rounded-2xl p-5 shadow-xs hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-100 uppercase">Capacity</span>
                    </div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Estate Units</span>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1"><?= (int)($metrics['units_count'] ?? 8) ?></p>
                    <span class="text-xs text-slate-500 mt-1 block truncate">Across all active portfolios</span>
                </div>

                <!-- Annual SaaS ARR (Purple) -->
                <div class="bg-white border border-slate-200/90 border-t-4 border-t-purple-600 rounded-2xl p-5 shadow-xs hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-100 uppercase">SaaS ARR</span>
                    </div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Annual Platform ARR</span>
                    <p class="text-2xl sm:text-3xl font-extrabold text-purple-700 mt-1">₦<?= number_format((float)($billing['annual_fee'] ?? 24000), 2) ?></p>
                    <span class="text-xs text-purple-600 font-semibold mt-1 block">8 Units × ₦3,000/yr</span>
                </div>
            </div>

            <!-- Platform System Status Card -->
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-[#1D4ED8] rounded-2xl p-6 shadow-xs mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-[#1D4ED8] border border-blue-100 mb-2">
                            <span>System Status</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Governance & Security Infrastructure</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Real-time health of RBAC policies, digital signing gates, and automated rent schedules.</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 font-bold text-xs border border-emerald-200 self-start sm:self-auto">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>All Systems Healthy</span>
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-5">
                    <div class="p-4 rounded-xl bg-blue-50/50 border border-blue-100 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-[#1D4ED8] flex items-center justify-center shrink-0 font-bold text-xs">
                            🔒
                        </div>
                        <div>
                            <strong class="text-xs font-bold text-slate-900 block">RBAC Enforcement</strong>
                            <span class="text-[11px] text-slate-600 block mt-0.5">Strict HTTP 403 on Caretaker signature access attempts.</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-100 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 font-bold text-xs">
                            🛡️
                        </div>
                        <div>
                            <strong class="text-xs font-bold text-slate-900 block">SHA-256 Stamp Engine</strong>
                            <span class="text-[11px] text-slate-600 block mt-0.5">Immutable audit hashes generated on tenant execution.</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-amber-50/50 border border-amber-100 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 font-bold text-xs">
                            🛑
                        </div>
                        <div>
                            <strong class="text-xs font-bold text-slate-900 block">Auto-Halt Dunning</strong>
                            <span class="text-[11px] text-slate-600 block mt-0.5">Instant dunning suppression upon rent payment record.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: SAAS BILLING -->
        <div x-show="currentTab === 'saas'" x-transition class="bg-white border border-slate-200/90 border-t-4 border-t-purple-600 rounded-2xl p-6 shadow-xs mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-100">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200 mb-2">
                        <span>SaaS Engine</span>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900">Dynamic Annual SaaS Billing Engine</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Calculates licensing fee based on active managed units across landlord portfolios.</p>
                </div>
                <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-lg text-xs font-extrabold self-start sm:self-auto">
                    Annual Licensing
                </span>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <!-- Unit Count (Blue) -->
                <div class="p-4 rounded-xl bg-blue-50/50 border border-blue-200">
                    <span class="text-[10px] uppercase font-bold text-[#1D4ED8] tracking-wider block">Managed Portfolio</span>
                    <strong class="text-xl font-black text-slate-900 block mt-1"><?= (int)($billing['total_units'] ?? 8) ?> Units</strong>
                    <span class="text-xs text-slate-500 block mt-0.5">Chief Ibrahim Bello</span>
                </div>

                <!-- Rate per Unit (Indigo) -->
                <div class="p-4 rounded-xl bg-indigo-50/50 border border-indigo-200">
                    <span class="text-[10px] uppercase font-bold text-indigo-700 tracking-wider block">Billing Tier</span>
                    <strong class="text-xl font-black text-slate-900 block mt-1">₦3,000.00</strong>
                    <span class="text-xs text-slate-500 block mt-0.5">Per unit per year</span>
                </div>

                <!-- Calculated ARR (Purple) -->
                <div class="p-4 rounded-xl bg-purple-50/70 border border-purple-300">
                    <span class="text-[10px] uppercase font-bold text-purple-700 tracking-wider block">Total Projected ARR</span>
                    <strong class="text-xl font-black text-purple-800 block mt-1">₦<?= number_format((float)($billing['annual_fee'] ?? 24000), 2) ?></strong>
                    <span class="text-xs text-purple-600 font-semibold block mt-0.5">Annual invoice value</span>
                </div>
            </div>

            <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl space-y-3 text-xs">
                <h4 class="font-bold text-slate-900 text-sm">Automated Invoice Breakdown</h4>
                <div class="flex justify-between py-1.5 border-b border-slate-200">
                    <span class="text-slate-600">Landlord Account:</span>
                    <strong class="text-slate-900">Chief Ibrahim Bello (PHDL Unity Estate, Idu)</strong>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-200">
                    <span class="text-slate-600">Model Computation:</span>
                    <span class="font-medium text-slate-800">8 Units × ₦3,000 / Unit / Year</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-200">
                    <span class="text-slate-600">Billing Cycle:</span>
                    <span class="font-medium text-slate-800">Annual (Renews January 1st)</span>
                </div>
                <div class="pt-2 flex justify-between text-base font-extrabold">
                    <span class="text-slate-900">Total Calculated Annual Invoice:</span>
                    <span class="text-purple-700">₦<?= number_format((float)($billing['annual_fee'] ?? 24000), 2) ?></span>
                </div>
            </div>
        </div>

        <!-- TAB 3: DUNNING SCHEDULER -->
        <div x-show="currentTab === 'dunning'" x-transition class="bg-white border border-slate-200/90 border-t-4 border-t-amber-500 rounded-2xl p-6 shadow-xs mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-100">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 mb-2">
                        <span>Background Worker</span>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900">Automated Dunning Scheduler Worker</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Executes phased rent reminders across T-30, T-7, and daily T-6..0 with strict auto-halt on payment.</p>
                </div>
                <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-lg text-xs font-extrabold self-start sm:self-auto">
                    Cron / Background Job
                </span>
            </div>

            <!-- Dunning Phases Breakdown Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <!-- Phase 1: Advance Notice (Blue) -->
                <div class="p-4 rounded-xl bg-blue-50/50 border border-blue-200">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold text-[#1D4ED8]">Phase 1: Advance</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-100 text-[#1D4ED8]">T-30</span>
                    </div>
                    <span class="text-xs font-semibold text-slate-800 block">30 Days Prior</span>
                    <p class="text-[11px] text-slate-600 mt-1">Gentle notice and banking details dispatched to tenant.</p>
                </div>

                <!-- Phase 2: Approaching (Amber) -->
                <div class="p-4 rounded-xl bg-amber-50/50 border border-amber-200">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold text-amber-700">Phase 2: Upcoming</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800">T-7</span>
                    </div>
                    <span class="text-xs font-semibold text-slate-800 block">7 Days Prior</span>
                    <p class="text-[11px] text-slate-600 mt-1">Follow-up reminder with Caretaker and Landlord auto-CC.</p>
                </div>

                <!-- Phase 3: Daily Urgent (Rose) -->
                <div class="p-4 rounded-xl bg-rose-50/50 border border-rose-200">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold text-rose-700">Phase 3: Urgent</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-rose-100 text-rose-800">T-6..0</span>
                    </div>
                    <span class="text-xs font-semibold text-slate-800 block">Daily Dunning</span>
                    <p class="text-[11px] text-slate-600 mt-1">Daily urgent notice. Automatically halts the instant payment is recorded.</p>
                </div>
            </div>

            <!-- Auto-Halt Guarantee Banner (Emerald) -->
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">
                        🛡️
                    </div>
                    <div>
                        <strong class="text-emerald-900 block font-bold">Auto-Halt Guarantee Active</strong>
                        <span class="text-emerald-700">Once tenant payment is verified, all further dunning is suppressed with zero spam.</span>
                    </div>
                </div>
                <span class="text-[10px] font-bold uppercase bg-emerald-200/80 text-emerald-800 px-2.5 py-1 rounded-md shrink-0">
                    Live Protected
                </span>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" @click="runScheduler()" :disabled="isRunning"
                        class="bg-[#1D4ED8] hover:bg-[#1E40AF] disabled:bg-slate-300 text-white font-bold text-xs px-5 py-3 rounded-xl transition shadow-xs flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="isRunning ? 'Processing Dunning Queue...' : 'Trigger Scheduled Dunning Run (Dry-Run)'"></span>
                </button>
            </div>

            <div x-show="logOutput" class="mt-4 p-4 bg-slate-900 text-emerald-400 font-mono text-xs rounded-xl overflow-x-auto" x-transition>
                <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-800 text-slate-400 text-[10px] uppercase">
                    <span>Dunning Execution Telemetry Log</span>
                    <button type="button" @click="logOutput = null" class="text-slate-400 hover:text-white">Clear</button>
                </div>
                <pre x-text="logOutput"></pre>
            </div>
        </div>

        <!-- TAB 4: TENANTS DIRECTORY (REVIEW, EXPORT CSV/PDF, UPLOAD CSV) -->
        <div x-show="currentTab === 'tenants'" x-transition class="space-y-6">
            <!-- Header & Action Card (Color-Coded Blue) -->
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-[#1D4ED8] rounded-2xl p-6 shadow-xs">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-[#1D4ED8] border border-blue-200 mb-2">
                            <span>Platform Governance</span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900">Tenants & Residents Directory</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Absolute review and governance across all managed properties, locations, and apartment types.</p>
                    </div>

                    <!-- Global Actions Toolbar -->
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="/admin/export/tenants/csv" 
                           class="inline-flex items-center gap-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold px-3.5 py-2.5 rounded-xl shadow-xs transition">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Export CSV</span>
                        </a>

                        <a href="/admin/export/tenants/pdf" target="_blank"
                           class="inline-flex items-center gap-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold px-3.5 py-2.5 rounded-xl shadow-xs transition">
                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Export PDF Audit</span>
                        </a>

                        <button type="button" @click="openUploadModal('tenants')"
                                class="inline-flex items-center gap-1.5 bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span>Upload Tenants (CSV)</span>
                        </button>

                        <a href="/admin/templates/tenants.csv" 
                           class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 hover:text-[#1D4ED8] px-2 py-1 transition">
                            <span>Template</span>
                        </a>
                    </div>
                </div>

                <!-- Real-time Filter & Search Bar -->
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="relative w-full sm:w-80">
                        <input type="text" x-model="tenantSearch" placeholder="Search by name, email, property, unit..."
                               class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8]">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <div class="flex items-center gap-2 self-start sm:self-auto text-xs">
                        <span class="text-slate-500 font-medium">Status:</span>
                        <select x-model="tenantStatusFilter" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]">
                            <option value="ALL">All Statuses (<?= count($allTenants ?? []) ?>)</option>
                            <option value="FULLY_EXECUTED">Fully Executed / Signed</option>
                            <option value="PENDING_TENANT_SIGNATURE">Pending Signature</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tenants Review Data Table -->
            <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 text-slate-600 uppercase tracking-wider font-extrabold border-b border-slate-200">
                                <th class="p-3.5">Resident / Tenant</th>
                                <th class="p-3.5">Property & Location</th>
                                <th class="p-3.5">Unit & Type</th>
                                <th class="p-3.5">Rooms</th>
                                <th class="p-3.5">Annual Rent</th>
                                <th class="p-3.5">Agreement Status</th>
                                <th class="p-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($allTenants)): ?>
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-400">
                                        No tenants registered yet. Upload a CSV or onboard via the Landlord wizard.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($allTenants as $t): ?>
                                    <tr class="hover:bg-slate-50/60 transition"
                                        x-show="matchesTenant(<?= htmlspecialchars(json_encode($t), ENT_QUOTES, 'UTF-8') ?>)">
                                        <td class="p-3.5">
                                            <div class="font-bold text-slate-900"><?= htmlspecialchars($t['full_name'] ?? '') ?></div>
                                            <div class="text-[11px] text-slate-500"><?= htmlspecialchars($t['email'] ?? '') ?> • <?= htmlspecialchars($t['phone_number'] ?? '') ?></div>
                                        </td>
                                        <td class="p-3.5">
                                            <span class="font-semibold text-slate-800 block"><?= htmlspecialchars($t['property_title'] ?? '') ?></span>
                                            <span class="text-[11px] text-slate-500"><?= htmlspecialchars($t['city'] ?? '') ?>, <?= htmlspecialchars($t['state'] ?? '') ?></span>
                                        </td>
                                        <td class="p-3.5">
                                            <span class="font-bold text-slate-800 block"><?= htmlspecialchars($t['unit_number'] ?? 'N/A') ?></span>
                                            <span class="text-[11px] text-slate-500"><?= htmlspecialchars($t['apartment_type'] ?? '2-Bedroom Apartment') ?></span>
                                        </td>
                                        <td class="p-3.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                <?= (int) ($t['rooms_count'] ?? 2) ?> Rooms
                                            </span>
                                        </td>
                                        <td class="p-3.5">
                                            <div class="font-extrabold text-slate-900">₦<?= number_format((float) ($t['rent_amount'] ?? 0), 2) ?></div>
                                            <div class="text-[10px] text-slate-400 font-medium">Due: <?= htmlspecialchars($t['rent_due_date'] ?? 'N/A') ?></div>
                                        </td>
                                        <td class="p-3.5">
                                            <?php if (($t['agreement_status'] ?? '') === 'FULLY_EXECUTED'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span>●</span> Fully Executed
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    <span>○</span> Pending Signature
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-3.5 text-right">
                                            <a href="/agreement/preview?id=<?= (int) ($t['lease_id'] ?? 1) ?>" 
                                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-[#1D4ED8] hover:text-white text-slate-700 text-xs font-bold transition">
                                                <span>Audit</span>
                                                <span>→</span>
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

        <!-- TAB 5: LANDLORDS DIRECTORY & MULTI-ESTATE PORTFOLIOS -->
        <div x-show="currentTab === 'landlords'" x-transition class="space-y-6">
            <!-- Header & Action Card (Color-Coded Emerald) -->
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-emerald-600 rounded-2xl p-6 shadow-xs">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-2">
                            <span>Portfolio Governance</span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900">Landlords & Real Estate Portfolios</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Multi-location portfolio telemetry, unit capacity counts, and annual run-rates across Nigeria.</p>
                    </div>

                    <!-- Global Actions Toolbar -->
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="/admin/export/landlords/csv" 
                           class="inline-flex items-center gap-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold px-3.5 py-2.5 rounded-xl shadow-xs transition">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Export CSV</span>
                        </a>

                        <a href="/admin/export/landlords/pdf" target="_blank"
                           class="inline-flex items-center gap-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold px-3.5 py-2.5 rounded-xl shadow-xs transition">
                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Export PDF Audit</span>
                        </a>

                        <button type="button" @click="openUploadModal('landlords')"
                                class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span>Upload Landlords (CSV)</span>
                        </button>

                        <a href="/admin/templates/landlords.csv" 
                           class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 hover:text-emerald-600 px-2 py-1 transition">
                            <span>Template</span>
                        </a>
                    </div>
                </div>

                <!-- Real-time Filter & Search Bar -->
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="relative w-full sm:w-80">
                        <input type="text" x-model="landlordSearch" placeholder="Search by landlord name, email, estate..."
                               class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Landlords Review Data Table -->
            <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 text-slate-600 uppercase tracking-wider font-extrabold border-b border-slate-200">
                                <th class="p-3.5">Landlord Identity</th>
                                <th class="p-3.5">Managed Estates (Multi-Location)</th>
                                <th class="p-3.5">Total Capacity</th>
                                <th class="p-3.5">Active Tenants</th>
                                <th class="p-3.5">Portfolio ARR</th>
                                <th class="p-3.5">SaaS Tier Status</th>
                                <th class="p-3.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($allLandlords)): ?>
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-400">
                                        No landlords registered yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($allLandlords as $l): ?>
                                    <tr class="hover:bg-slate-50/60 transition"
                                        x-show="matchesLandlord(<?= htmlspecialchars(json_encode($l), ENT_QUOTES, 'UTF-8') ?>)">
                                        <td class="p-3.5">
                                            <div class="font-bold text-slate-900"><?= htmlspecialchars($l['full_name'] ?? '') ?></div>
                                            <div class="text-[11px] text-slate-500"><?= htmlspecialchars($l['email'] ?? '') ?> • <?= htmlspecialchars($l['phone_number'] ?? '') ?></div>
                                        </td>
                                        <td class="p-3.5">
                                            <div class="flex items-center gap-1.5 mb-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-[#1D4ED8]">
                                                    <?= (int) ($l['properties_count'] ?? 0) ?> Locations
                                                </span>
                                            </div>
                                            <div class="text-[11px] text-slate-600 truncate max-w-xs font-medium" title="<?= htmlspecialchars($l['estates_list'] ?? '') ?>">
                                                <?= htmlspecialchars($l['estates_list'] ?? 'No properties') ?>
                                            </div>
                                        </td>
                                        <td class="p-3.5 font-bold text-slate-800">
                                            <?= (int) ($l['total_units'] ?? 0) ?> Units
                                        </td>
                                        <td class="p-3.5 font-bold text-slate-800">
                                            <?= (int) ($l['active_leases'] ?? 0) ?> Tenancies
                                        </td>
                                        <td class="p-3.5">
                                            <div class="font-extrabold text-slate-900">₦<?= number_format((float) ($l['total_annual_rent_roll'] ?? 0), 2) ?></div>
                                            <div class="text-[10px] text-slate-400 font-medium">Annual Rent Roll</div>
                                        </td>
                                        <td class="p-3.5">
                                            <?php if ((int)($l['total_units'] ?? 0) <= 10): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    10+ Units Free Tier
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                    Pro Enterprise
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-3.5 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button" @click="openEditLandlordModal(<?= htmlspecialchars(json_encode($l), ENT_QUOTES, 'UTF-8') ?>)"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-200 transition shadow-2xs">
                                                    <span>✏️ Edit</span>
                                                </button>
                                                <a href="/demo/landlord" 
                                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-[#1D4ED8] hover:text-white text-slate-700 text-xs font-bold transition">
                                                    <span>Impersonate →</span>
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

        <!-- ================= TAB 6: DEEP ESTATE OPERATIONS AUDIT ================= -->
        <div x-show="currentTab === 'estate_ops'" x-transition class="space-y-6">
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-indigo-600 rounded-2xl p-6 shadow-xs">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 mb-2">
                            <span>Deep Operations Inspection</span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900">Estate Operations & Facility Lifecycle Reviewer</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Inspect physical facilities, maintenance work orders, artisans, expenses, and notices across any property in Nigeria.</p>
                    </div>

                    <!-- Estate Selector -->
                    <div class="flex items-center gap-3">
                        <label class="text-xs font-bold text-slate-700 shrink-0">Select Estate:</label>
                        <select x-model="selectedEstateId" @change="loadEstateOps()"
                                class="px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            <?php foreach ($allEstates as $est): ?>
                                <option value="<?= (int)$est['id'] ?>">
                                    <?= htmlspecialchars($est['title']) ?> (<?= htmlspecialchars($est['city'] ?? '') ?>, <?= htmlspecialchars($est['state'] ?? '') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Estate Meta strip -->
                <template x-if="estateOps">
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Landlord / Owner</span>
                            <strong class="text-slate-900" x-text="estateOps.property?.landlord_name || 'Chief Ibrahim Bello'"></strong>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Location</span>
                            <span class="text-slate-800 font-medium" x-text="`${estateOps.property?.city || 'Abuja'}, ${estateOps.property?.state || 'FCT'}`"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Active Tickets</span>
                            <span class="font-extrabold text-rose-600" x-text="`${(estateOps.tickets || []).length} Work Orders`"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Facility Bookings</span>
                            <span class="font-extrabold text-amber-600" x-text="`${(estateOps.facility_bookings || []).length} Reservations`"></span>
                        </div>
                    </div>
                </template>

                <!-- Detailed Sub-Sections -->
                <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Tickets & Maintenance -->
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                            <h3 class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                                <span>🛠️</span> Maintenance Issues & Artisan Orders
                            </h3>
                            <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded" x-text="`${(estateOps?.tickets || []).length} Recorded`"></span>
                        </div>
                        <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                            <template x-if="!estateOps || (estateOps.tickets || []).length === 0">
                                <p class="text-xs text-slate-400 py-4 text-center italic">No maintenance tickets logged for this estate.</p>
                            </template>
                            <template x-for="t in (estateOps?.tickets || [])" :key="t.id">
                                <div class="p-3 bg-white rounded-xl border border-slate-200 text-xs space-y-1 shadow-2xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-mono font-bold text-[#1D4ED8]" x-text="t.ticket_code"></span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                              :class="t.status === 'RESOLVED' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                              x-text="t.status"></span>
                                    </div>
                                    <strong class="text-slate-900 block" x-text="t.title"></strong>
                                    <p class="text-slate-500 text-[11px]" x-text="t.description"></p>
                                    <div class="flex items-center justify-between pt-1 border-t border-slate-100 text-[10px] text-slate-400">
                                        <span x-text="`Resident: ${t.tenant_name || 'Tenant'}`"></span>
                                        <span x-text="t.photo_url ? '📷 Has Photo' : 'No photo'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Expenses & Financials -->
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                            <h3 class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                                <span>💰</span> Operating Expenses & Outflows
                            </h3>
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded" x-text="`${(estateOps?.expenses || []).length} Logged`"></span>
                        </div>
                        <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                            <template x-if="!estateOps || (estateOps.expenses || []).length === 0">
                                <p class="text-xs text-slate-400 py-4 text-center italic">No operational expenses logged for this estate.</p>
                            </template>
                            <template x-for="exp in (estateOps?.expenses || [])" :key="exp.id">
                                <div class="p-3 bg-white rounded-xl border border-slate-200 text-xs flex items-center justify-between shadow-2xs">
                                    <div>
                                        <strong class="text-slate-900 block" x-text="exp.title"></strong>
                                        <span class="text-[10px] text-slate-500" x-text="`${exp.category} • ${exp.expense_date}`"></span>
                                    </div>
                                    <span class="font-black text-slate-900 text-xs" x-text="`₦${Number(exp.amount).toLocaleString()}`"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= TAB 7: GLOBAL ALERT RULES ================= -->
        <div x-show="currentTab === 'alert_rules'" x-transition class="space-y-6">
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-sky-600 rounded-2xl p-6 shadow-xs">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200 mb-2">
                            <span>Automation & Dunning Rules</span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900">Platform & Estate Smart Alert Rules</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Automated rent payment reminders via SMS, email, in-app; customizable grace periods and penalty rules.</p>
                    </div>

                    <button type="button" @click="triggerSimulateAlert()" :disabled="simulatingAlert"
                            class="bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-2">
                        <span x-text="simulatingAlert ? 'Simulating...' : '⚡ Test Alert Rule Simulation'"></span>
                    </button>
                </div>

                <!-- Simulation Result Modal/Alert -->
                <template x-if="simulationResult">
                    <div class="mt-4 p-4 bg-slate-900 text-emerald-400 rounded-2xl font-mono text-xs space-y-2 border border-slate-800">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-2 text-slate-400">
                            <span>📡 Real-Time Multi-Channel Alert Payload Dispatched</span>
                            <button type="button" @click="simulationResult = null" class="text-slate-400 hover:text-white">✕</button>
                        </div>
                        <div class="space-y-1 text-[11px]">
                            <div><strong class="text-blue-300">Channels:</strong> <span x-text="simulationResult.simulated_channels?.join(', ')"></span></div>
                            <div><strong class="text-blue-300">Tenant Target:</strong> <span x-text="simulationResult.tenant_name"></span> (<span x-text="simulationResult.tenant_phone"></span>)</div>
                            <div><strong class="text-blue-300">Auto-CC Caretaker:</strong> <span x-text="simulationResult.caretaker_email"></span></div>
                            <div><strong class="text-blue-300">SMS Gateway Payload:</strong> <span class="text-white" x-text="simulationResult.payload?.sms_body"></span></div>
                        </div>
                    </div>
                </template>

                <!-- Rules Table -->
                <div class="mt-6 bg-white border border-slate-200 rounded-2xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-600 uppercase font-extrabold border-b border-slate-200">
                                    <th class="p-3.5">Trigger Event</th>
                                    <th class="p-3.5">Scope</th>
                                    <th class="p-3.5">Target Role</th>
                                    <th class="p-3.5">Channel</th>
                                    <th class="p-3.5">Grace Period</th>
                                    <th class="p-3.5">Penalty Rate</th>
                                    <th class="p-3.5">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (empty($allAlertRules)): ?>
                                    <tr>
                                        <td colspan="7" class="p-8 text-center text-slate-400">No alert rules configured yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($allAlertRules as $ar): ?>
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="p-3.5 font-bold text-slate-900">
                                                <?= htmlspecialchars($ar['event_type']) ?>
                                            </td>
                                            <td class="p-3.5">
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded <?= $ar['scope'] === 'GLOBAL' ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-[#1D4ED8]' ?>">
                                                    <?= htmlspecialchars($ar['scope']) ?>
                                                </span>
                                            </td>
                                            <td class="p-3.5 font-medium text-slate-700"><?= htmlspecialchars($ar['recipient_role']) ?></td>
                                            <td class="p-3.5">
                                                <span class="inline-flex items-center gap-1 font-bold text-slate-800">
                                                    <?= htmlspecialchars($ar['channel']) ?>
                                                </span>
                                            </td>
                                            <td class="p-3.5 font-medium text-slate-700"><?= (int)($ar['grace_period_days'] ?? 0) ?> Days</td>
                                            <td class="p-3.5 font-bold text-rose-600"><?= number_format((float)($ar['penalty_rate_percent'] ?? 0), 1) ?>% / mo</td>
                                            <td class="p-3.5">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    ✓ ACTIVE
                                                </span>
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

        <!-- ================= TAB 8: TENANT KYC QUEUE ================= -->
        <div x-show="currentTab === 'kyc_queue'" x-transition class="space-y-6">
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-emerald-600 rounded-2xl p-6 shadow-xs">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-2">
                            <span>Identity Compliance</span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900">Resident Digital Onboarding & KYC Identity Verification</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Approve government identity (NIN/BVN), verified employment, and guarantor references.</p>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-600 uppercase font-extrabold border-b border-slate-200">
                                    <th class="p-3.5">Resident</th>
                                    <th class="p-3.5">NIN / BVN Number</th>
                                    <th class="p-3.5">Employer & Role</th>
                                    <th class="p-3.5">Guarantor Referee</th>
                                    <th class="p-3.5">KYC Status</th>
                                    <th class="p-3.5 text-right">SuperAdmin Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (empty($allTenants)): ?>
                                    <tr>
                                        <td colspan="6" class="p-8 text-center text-slate-400">No tenants pending KYC verification.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($allTenants as $t): ?>
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="p-3.5">
                                                <strong class="text-slate-900 block"><?= htmlspecialchars($t['full_name'] ?? '') ?></strong>
                                                <span class="text-[11px] text-slate-500"><?= htmlspecialchars($t['email'] ?? '') ?> • <?= htmlspecialchars($t['phone_number'] ?? '') ?></span>
                                            </td>
                                            <td class="p-3.5">
                                                <span class="font-mono font-bold text-slate-800 bg-slate-100 px-2 py-1 rounded border border-slate-200 block w-fit">
                                                    NIN: <?= htmlspecialchars($t['kyc_nin'] ?? '54109821092') ?>
                                                </span>
                                            </td>
                                            <td class="p-3.5 text-slate-700">
                                                <strong class="block text-slate-800"><?= htmlspecialchars($t['employer_name'] ?? 'Central Bank of Nigeria (CBN)') ?></strong>
                                                <span class="text-[11px] text-slate-500"><?= htmlspecialchars($t['occupation'] ?? 'Systems Engineer') ?></span>
                                            </td>
                                            <td class="p-3.5 text-slate-700">
                                                <strong class="block text-slate-800"><?= htmlspecialchars($t['guarantor_name'] ?? 'Barrister Chidi Okafor') ?></strong>
                                                <span class="text-[11px] text-slate-500"><?= htmlspecialchars($t['guarantor_phone'] ?? '+234 803 999 8877') ?></span>
                                            </td>
                                            <td class="p-3.5">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    🛡️ <?= htmlspecialchars($t['kyc_status'] ?? 'VERIFIED') ?>
                                                </span>
                                            </td>
                                            <td class="p-3.5 text-right">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    <button type="button" @click="verifyKyc(<?= (int)$t['id'] ?>, 'VERIFIED')"
                                                            class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                                                        ✓ Verify
                                                    </button>
                                                    <button type="button" @click="verifyKyc(<?= (int)$t['id'] ?>, 'REJECTED')"
                                                            class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs transition">
                                                        ✕ Flag
                                                    </button>
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
        </div>

    </main>

</div>

<!-- CSV UPLOAD MODAL -->
<div x-show="uploadModalOpen" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-transition.opacity>
    <div class="bg-white w-full max-w-lg rounded-2xl p-6 shadow-2xl border border-slate-200" @click.outside="uploadModalOpen = false">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div>
                <span class="text-xs font-bold text-[#1D4ED8] uppercase tracking-wider block">SuperAdmin Bulk Ingestion</span>
                <h3 class="text-lg font-extrabold text-slate-900 mt-0.5" x-text="uploadType === 'tenants' ? 'Upload Tenants Directory CSV' : 'Upload Landlords Directory CSV'"></h3>
            </div>
            <button type="button" @click="uploadModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">✕</button>
        </div>

        <div class="space-y-4">
            <p class="text-xs text-slate-600">
                Select a standard RFC 4180 CSV file. You can download the official format template below:
            </p>

            <div class="flex items-center gap-2">
                <a :href="uploadType === 'tenants' ? '/admin/templates/tenants.csv' : '/admin/templates/landlords.csv'"
                   class="inline-flex items-center gap-1.5 text-xs text-[#1D4ED8] font-bold hover:underline">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download Official CSV Sample Template</span>
                </a>
            </div>

            <!-- Drag / Select File Box -->
            <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:border-[#1D4ED8] transition bg-slate-50/50">
                <input type="file" id="csvFileInput" accept=".csv,text/csv" @change="handleFileSelect($event)" class="hidden">
                <label for="csvFileInput" class="cursor-pointer block">
                    <div class="w-12 h-12 mx-auto rounded-full bg-blue-50 text-[#1D4ED8] flex items-center justify-center mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-800 block">Click to browse or drop CSV here</span>
                    <span class="text-[11px] text-slate-500 block mt-1" x-text="uploadFile ? uploadFile.name : 'Only .csv format is supported'"></span>
                </label>
            </div>

            <!-- Upload Result Alert -->
            <template x-if="uploadResult">
                <div :class="uploadResult.success ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800'"
                     class="p-4 rounded-xl border text-xs space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <span x-text="uploadResult.success ? '✓ Ingestion Successful!' : '✕ Ingestion Error'"></span>
                    </div>
                    <div x-show="uploadResult.success && uploadResult.summary">
                        <span x-text="`${uploadResult.summary.inserted} record(s) inserted, ${uploadResult.summary.skipped_duplicates} duplicates skipped.`"></span>
                        <div class="text-[11px] text-emerald-600 mt-1 font-medium">Refreshing directory...</div>
                    </div>
                    <div x-show="!uploadResult.success">
                        <span x-text="uploadResult.error"></span>
                    </div>
                </div>
            </template>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" @click="uploadModalOpen = false"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                    Cancel
                </button>
                <button type="button" @click="submitUpload()" :disabled="!uploadFile || isUploading"
                        class="bg-[#1D4ED8] hover:bg-[#1E40AF] disabled:bg-slate-300 text-white text-xs font-bold px-5 py-2 rounded-xl shadow-xs transition flex items-center gap-2">
                    <span x-show="!isUploading">Upload & Process</span>
                    <span x-show="isUploading">Ingesting Data...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================= EDIT LANDLORD PROFILE MODAL ================= -->
<div x-show="showEditLandlordModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-transition.opacity style="display: none;">
    <div class="bg-white w-full max-w-lg rounded-3xl p-6 shadow-2xl border border-slate-200 text-xs" @click.outside="showEditLandlordModal = false">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div>
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider block">SuperAdmin Authority</span>
                <h3 class="text-lg font-extrabold text-slate-900 mt-0.5">Edit Landlord Profile & Account Info</h3>
            </div>
            <button type="button" @click="showEditLandlordModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">✕</button>
        </div>

        <form @submit.prevent="submitLandlordUpdate()" class="space-y-4">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Full Legal Name</label>
                <input type="text" x-model="editLandlordForm.full_name" required
                       class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Email Address</label>
                    <input type="email" x-model="editLandlordForm.email" required
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Phone Number</label>
                    <input type="text" x-model="editLandlordForm.phone_number" required
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Company / Entity Name</label>
                    <input type="text" x-model="editLandlordForm.company_name" placeholder="e.g. Bello Holdings Ltd"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">SaaS Rate / Unit (NGN)</label>
                    <input type="number" step="100" min="0" x-model="editLandlordForm.rate_per_unit" required
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 font-bold text-slate-900">
                </div>
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                <div>
                    <strong class="text-slate-900 block text-xs">Account Status</strong>
                    <span class="text-[11px] text-slate-500">Activate or deactivate landlord access to the platform.</span>
                </div>
                <select x-model="editLandlordForm.is_active" class="px-3 py-1.5 rounded-lg border border-slate-300 font-bold text-xs">
                    <option value="1">Active</option>
                    <option value="0">Suspended</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" @click="showEditLandlordModal = false"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                    Cancel
                </button>
                <button type="submit" :disabled="submittingLandlordUpdate"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-5 py-2 rounded-xl shadow-xs transition">
                    <span x-text="submittingLandlordUpdate ? 'Saving Changes...' : 'Save Landlord Changes'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function superAdminApp() {
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab') || 'telemetry';

    return {
        sidebarOpen: false,
        currentTab: initialTab,
        isRunning: false,
        logOutput: null,

        // Filtering
        tenantSearch: '',
        tenantStatusFilter: 'ALL',
        landlordSearch: '',

        // CSV Upload Modal
        uploadModalOpen: false,
        uploadType: 'tenants',
        uploadFile: null,
        isUploading: false,
        uploadResult: null,

        // Edit Landlord Modal
        showEditLandlordModal: false,
        submittingLandlordUpdate: false,
        editLandlordForm: {
            id: null,
            full_name: '',
            email: '',
            phone_number: '',
            company_name: '',
            is_active: 1,
            rate_per_unit: 3000
        },

        // Estate Operations Reviewer
        selectedEstateId: <?= !empty($allEstates[0]['id']) ? (int)$allEstates[0]['id'] : 1 ?>,
        estateOps: null,

        // Alerts & Simulation
        simulatingAlert: false,
        simulationResult: null,

        init() {
            this.loadEstateOps();
        },

        async loadEstateOps() {
            if (!this.selectedEstateId) return;
            try {
                const res = await fetch(`/api/v1/superadmin/estates/operations?property_id=${this.selectedEstateId}`);
                const data = await res.json();
                this.estateOps = data;
            } catch (e) {
                console.error('Failed to load estate operations:', e);
            }
        },

        openEditLandlordModal(landlord) {
            this.editLandlordForm = {
                id: landlord.id,
                full_name: landlord.full_name || '',
                email: landlord.email || '',
                phone_number: landlord.phone_number || '',
                company_name: landlord.company_name || '',
                is_active: landlord.is_active !== undefined ? landlord.is_active : 1,
                rate_per_unit: landlord.rate_per_unit || 3000
            };
            this.showEditLandlordModal = true;
        },

        async submitLandlordUpdate() {
            this.submittingLandlordUpdate = true;
            try {
                const res = await fetch('/api/v1/superadmin/landlords/update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.editLandlordForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert('Landlord profile updated successfully by SuperAdmin!');
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to update landlord.');
                }
            } catch (e) {
                alert('Network error updating landlord: ' + e.message);
            } finally {
                this.submittingLandlordUpdate = false;
            }
        },

        async verifyKyc(tenantId, status) {
            const verb = status === 'VERIFIED' ? 'approve and verify' : 'flag';
            if (!confirm(`Are you sure you want to ${verb} KYC credentials for this resident?`)) return;
            try {
                const res = await fetch('/api/v1/superadmin/kyc/verify', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tenant_id: tenantId, status: status })
                });
                const data = await res.json();
                if (data.success) {
                    alert(`Tenant KYC status set to: ${status}!`);
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to update KYC.');
                }
            } catch (e) {
                alert('Network error updating KYC: ' + e.message);
            }
        },

        async triggerSimulateAlert() {
            this.simulatingAlert = true;
            this.simulationResult = null;
            try {
                const res = await fetch('/api/v1/reminders/simulate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ event_type: 'RENT_DUE_T7' })
                });
                const data = await res.json();
                if (data.success) {
                    this.simulationResult = data;
                } else {
                    alert(data.error || 'Failed to simulate alert.');
                }
            } catch (e) {
                alert('Error simulating alert: ' + e.message);
            } finally {
                this.simulatingAlert = false;
            }
        },

        matchesTenant(t) {
            if (this.tenantStatusFilter !== 'ALL' && t.agreement_status !== this.tenantStatusFilter) {
                return false;
            }
            if (!this.tenantSearch) return true;
            const q = this.tenantSearch.toLowerCase();
            return (t.full_name || '').toLowerCase().includes(q) ||
                   (t.email || '').toLowerCase().includes(q) ||
                   (t.property_title || '').toLowerCase().includes(q) ||
                   (t.unit_number || '').toLowerCase().includes(q) ||
                   (t.city || '').toLowerCase().includes(q);
        },

        matchesLandlord(l) {
            if (!this.landlordSearch) return true;
            const q = this.landlordSearch.toLowerCase();
            return (l.full_name || '').toLowerCase().includes(q) ||
                   (l.email || '').toLowerCase().includes(q) ||
                   (l.estates_list || '').toLowerCase().includes(q);
        },

        openUploadModal(type) {
            this.uploadType = type;
            this.uploadFile = null;
            this.uploadResult = null;
            this.uploadModalOpen = true;
        },

        handleFileSelect(e) {
            const file = e.target.files[0];
            if (file) {
                this.uploadFile = file;
            }
        },

        async submitUpload() {
            if (!this.uploadFile) {
                alert('Please select a CSV file.');
                return;
            }
            this.isUploading = true;
            this.uploadResult = null;

            try {
                const formData = new FormData();
                formData.append('file', this.uploadFile);
                const url = this.uploadType === 'tenants' ? '/admin/upload/tenants' : '/admin/upload/landlords';
                const res = await fetch(url, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.uploadResult = { success: true, summary: data.summary };
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    this.uploadResult = { success: false, error: data.error || 'Upload failed' };
                }
            } catch (err) {
                this.uploadResult = { success: false, error: 'Connection error during CSV ingestion.' };
            } finally {
                this.isUploading = false;
            }
        },

        async runScheduler() {
            this.isRunning = true;
            try {
                const res = await fetch('/api/v1/reminders/run', { method: 'POST' });
                const data = await res.json();
                this.logOutput = JSON.stringify(data, null, 2);
            } catch (e) {
                this.logOutput = 'Error executing scheduler.';
            } finally {
                this.isRunning = false;
            }
        }
    }
}
</script>
</body>
</html>