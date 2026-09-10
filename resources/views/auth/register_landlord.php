<?php
$pdo = $pdo ?? (isset($db) ? $db->getPdo() : (new \App\Infrastructure\Database\Connection())->getPdo());
$abujaEstates = $pdo->query("SELECT id, title, district, zone_region, estate_category, market_availability FROM estates_directory WHERE state = 'Abuja' ORDER BY title ASC")->fetchAll(\PDO::FETCH_ASSOC);
$lagosEstates = $pdo->query("SELECT id, title, district, zone_region, estate_category, market_availability FROM estates_directory WHERE state = 'Lagos' ORDER BY title ASC")->fetchAll(\PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Landlord Registration & Property Onboarding — Oga Landlord</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full flex flex-col font-sans text-slate-800 antialiased selection:bg-blue-600 selection:text-white" x-data="registerApp()">

    <!-- Top Navigation Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                <img src="/assets/images/logo.jpg" alt="Oga Landlord Logo" class="h-10 w-auto object-contain rounded-lg border border-slate-100 shadow-xs">
                <div>
                    <span class="text-base font-extrabold tracking-tight text-slate-900 block leading-tight">Oga<span class="text-[#1D4ED8]">Landlord</span></span>
                    <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block">Landlord Onboarding</span>
                </div>
            </a>
            <div class="flex items-center gap-3 text-xs">
                <span class="text-slate-500 hidden sm:inline">Already have an account?</span>
                <a href="/login" class="bg-white hover:bg-slate-50 text-slate-800 font-bold px-3.5 py-2 rounded-xl border border-slate-300 shadow-xs transition">
                    Sign In
                </a>
                <a href="/demo/tenant" class="bg-blue-50 hover:bg-blue-100 text-[#1D4ED8] font-bold px-3.5 py-2 rounded-xl border border-blue-200 transition inline-flex items-center gap-1.5">
                    <span>🧪 Test Play Tenant</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Form Container -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        <!-- Header Titles -->
        <div class="text-center max-w-2xl mx-auto mb-8">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-[#1D4ED8] border border-blue-200 mb-3">
                <span>👔 Self-Service Landlord Portal</span>
                <span class="text-slate-300">•</span>
                <span>Nationwide Multi-Location Support</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Create Landlord Account & List Properties</h1>
            <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed">
                Register as a landlord from anywhere in Nigeria, list your estates and apartments, and designate whether your own caretaker or the Oga Landlord SuperAdmin Concierge handles ground maintenance.
            </p>
        </div>

        <!-- Tenant Notice Alert Banner (Enforces Invitation-Only rule with grace) -->
        <div class="mb-8 bg-amber-50/90 border border-amber-200/90 rounded-2xl p-4 sm:p-5 text-xs text-amber-900 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-sm shrink-0">
                    ℹ️
                </div>
                <div>
                    <strong class="font-bold text-amber-950 block text-sm">Are you a resident or tenant?</strong>
                    <p class="text-amber-800 mt-0.5 leading-relaxed">
                        Tenant accounts are created <strong>by invitation only</strong> when your landlord adds your profile. You cannot self-register as a tenant. If you were invited, please check your email or log in directly.
                    </p>
                </div>
            </div>
            <div class="shrink-0 flex items-center gap-2">
                <a href="/login?role=tenant" class="bg-amber-600 hover:bg-amber-700 text-white font-bold px-3.5 py-2 rounded-xl transition text-xs shadow-xs inline-block text-center">
                    Tenant Sign In
                </a>
                <a href="/demo/tenant" class="bg-white hover:bg-amber-100 text-amber-900 border border-amber-300 font-bold px-3.5 py-2 rounded-xl transition text-xs shadow-xs inline-block text-center">
                    Test Play Tenant Mode
                </a>
            </div>
        </div>

        <!-- Error Feedback Box -->
        <?php if (!empty($errorMessage)): ?>
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-center gap-3">
                <span class="text-lg">⚠️</span>
                <span><?= htmlspecialchars($errorMessage) ?></span>
            </div>
        <?php endif; ?>

        <!-- Signup Form -->
        <form method="POST" action="/register" class="space-y-6">

            <!-- SECTION 1: LANDLORD PERSONAL PROFILE -->
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-[#1D4ED8] rounded-2xl p-6 shadow-xs">
                <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#1D4ED8] flex items-center justify-center font-bold text-sm">
                        1
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Landlord Account Information</h2>
                        <p class="text-xs text-slate-500">Your master credentials to access the portfolio dashboard.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">Full Legal Name *</label>
                        <input type="text" name="full_name" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                               placeholder="e.g. Chief Babatunde Adeleke"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1D4ED8] bg-slate-50/50">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Official Email Address *</label>
                        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               placeholder="e.g. landlord@myproperty.ng"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1D4ED8] bg-slate-50/50">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Phone Number (WhatsApp Active) *</label>
                        <input type="text" name="phone_number" required value="<?= htmlspecialchars($_POST['phone_number'] ?? '+234') ?>"
                               placeholder="e.g. +2348031234567"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1D4ED8] bg-slate-50/50">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Password *</label>
                        <input type="password" name="password" required minlength="6" placeholder="Minimum 6 characters"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1D4ED8] bg-slate-50/50">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Confirm Password *</label>
                        <input type="password" name="password_confirmation" required minlength="6" placeholder="Confirm your password"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1D4ED8] bg-slate-50/50">
                    </div>
                </div>
            </div>

            <!-- SECTION 2: INITIAL PROPERTY & MULTI-LOCATION ONBOARDING -->
            <div class="bg-white border border-slate-200/90 border-t-4 border-t-indigo-600 rounded-2xl p-6 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                            2
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">List Your Initial Property / Location</h2>
                            <p class="text-xs text-slate-500">Onboard one or multiple properties across different estates and Nigerian locations. Use the <span class="font-bold text-indigo-600">+</span> button to add more locations.</p>
                        </div>
                    </div>
                    
                    <!-- Plus Sign '+' Button in Header -->
                    <button type="button" @click="addProperty()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-extrabold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition self-start">
                        <span class="text-base font-black leading-none">+</span>
                        <span>Add Another Property / Estate</span>
                    </button>
                </div>

                <!-- Dynamic Property / Estate Cards -->
                <div class="space-y-6">
                    <template x-for="(prop, pIdx) in properties" :key="prop.id">
                        <div class="p-5 rounded-2xl border-2 transition relative bg-slate-50/40"
                             :class="prop.selectedEstate ? 'border-emerald-300 shadow-xs' : 'border-indigo-100'">
                            
                            <!-- Card Header -->
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-200/80">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-black flex items-center justify-center shadow-2xs" x-text="pIdx + 1"></span>
                                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800" x-text="'Property / Location #' + (pIdx + 1)"></h3>
                                    <template x-if="prop.query">
                                        <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-200" x-text="prop.query"></span>
                                    </template>
                                </div>

                                <template x-if="properties.length > 1">
                                    <button type="button" @click="removeProperty(pIdx)"
                                            class="inline-flex items-center gap-1 text-[11px] font-bold text-red-600 hover:text-red-800 hover:bg-red-50 px-2.5 py-1 rounded-lg transition">
                                        <span>✕ Remove Location</span>
                                    </button>
                                </template>
                            </div>

                            <!-- Property Fields Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <!-- Step 2a: State Where Property is Located -->
                                <div class="sm:col-span-2">
                                    <label class="block font-bold text-slate-800 mb-1 flex items-center justify-between">
                                        <span>1. State Where Property is Located *</span>
                                        <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                                            Step 1 Required
                                        </span>
                                    </label>
                                    <select :name="'properties[' + pIdx + '][state]'" x-model="prop.state" @change="onPropStateChange(prop)" required
                                            class="w-full text-xs font-semibold px-3.5 py-2.5 border-2 border-indigo-200 rounded-xl focus:outline-none focus:border-[#1D4ED8] bg-white text-slate-900 shadow-xs">
                                        <option value="" disabled>-- Select State Where Property is Located --</option>
                                        <option value="Abuja">Abuja (Federal Capital Territory) — 51 Verified Estates</option>
                                        <option value="Lagos">Lagos State — 42 Verified Estates</option>
                                        <option disabled>────────── OTHER NIGERIAN STATES ──────────</option>
                                        <?php
                                        $otherStates = [
                                            'Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa', 'Benue', 'Borno',
                                            'Cross River', 'Delta', 'Ebonyi', 'Edo', 'Ekiti', 'Enugu', 'Gombe',
                                            'Imo', 'Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Kogi', 'Kwara',
                                            'Nasarawa', 'Niger', 'Ogun', 'Ondo', 'Osun', 'Oyo', 'Plateau',
                                            'Rivers', 'Sokoto', 'Taraba', 'Yobe', 'Zamfara'
                                        ];
                                        foreach ($otherStates as $st):
                                        ?>
                                            <option value="<?= $st ?>"><?= $st ?> State</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-1.5">
                                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span>Selecting <strong>Abuja</strong> or <strong>Lagos</strong> enables live Database auto-suggestions from our 93+ verified estates.</span>
                                    </p>
                                </div>

                                <!-- Step 2b: Next Option - Estate Name / Property Title -->
                                <div class="sm:col-span-2 relative" x-show="prop.state" x-transition>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block font-bold text-slate-800">
                                            <span x-text="isDirState(prop.state) ? '2. Estate Name *' : '2. Property / Estate Title *'"></span>
                                        </label>
                                        <template x-if="isDirState(prop.state)">
                                            <span class="text-[10px] font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                                <span>🔍</span>
                                                <span x-text="'Live ' + prop.state + ' Database Search'"></span>
                                            </span>
                                        </template>
                                    </div>

                                    <!-- Estate Search / Input -->
                                    <div class="relative">
                                        <input type="text"
                                               :name="'properties[' + pIdx + '][property_title]'"
                                               required
                                               autocomplete="off"
                                               x-model="prop.query"
                                               @input="searchPropEstates(prop)"
                                               @focus="if ((prop.query || '').trim().length >= 1) prop.showSuggestions = true"
                                               @click.away="prop.showSuggestions = false"
                                               @keydown.escape="prop.showSuggestions = false"
                                               @keydown.arrow-down.prevent="navigatePropSuggestions(prop, 1)"
                                               @keydown.arrow-up.prevent="navigatePropSuggestions(prop, -1)"
                                               @keydown.enter.prevent="selectHighlightedProp(prop)"
                                               :placeholder="getPropPlaceholder(prop)"
                                               class="w-full text-xs px-3.5 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1D4ED8] bg-white shadow-xs transition"
                                               :class="prop.selectedEstate ? 'border-emerald-500 ring-1 ring-emerald-500/30' : (isDirState(prop.state) ? 'border-blue-400' : 'border-slate-200')">

                                        <!-- Status Indicator Icon -->
                                        <div class="absolute right-3 top-3 flex items-center gap-2 pointer-events-none">
                                            <template x-if="prop.selectedEstate">
                                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center">✓</span>
                                            </template>
                                            <template x-if="!prop.selectedEstate && isDirState(prop.state) && (prop.query || '').trim().length >= 1">
                                                <span class="text-[10px] font-bold text-slate-400" x-text="prop.filteredEstates.length + ' found'"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Live Suggestion Dropdown (Opens on typing the first alphabet) -->
                                    <div x-show="prop.showSuggestions && prop.filteredEstates.length > 0 && isDirState(prop.state)"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden max-h-72 overflow-y-auto">
                                        <div class="px-3 py-2 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-bold">
                                            <span>Suggested Estates in <span class="text-blue-600" x-text="prop.state"></span> (Type to filter)</span>
                                            <span class="text-[10px] text-slate-400" x-text="prop.filteredEstates.length + ' match(es)'"></span>
                                        </div>
                                        <ul class="divide-y divide-slate-100">
                                            <template x-for="(est, idx) in prop.filteredEstates" :key="est.id || est.title">
                                                <li @click="selectPropEstate(prop, est)"
                                                    @mouseenter="prop.highlightIndex = idx"
                                                    class="px-4 py-2.5 cursor-pointer transition flex items-center justify-between gap-3 text-xs"
                                                    :class="prop.highlightIndex === idx ? 'bg-blue-50/80 text-blue-900' : 'hover:bg-slate-50 text-slate-800'">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="font-bold truncate text-slate-900 flex items-center gap-2">
                                                            <span x-text="est.title"></span>
                                                            <template x-if="est.estate_category">
                                                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 shrink-0" x-text="est.estate_category"></span>
                                                            </template>
                                                        </div>
                                                        <div class="text-[11px] text-slate-500 mt-0.5 truncate flex items-center gap-2">
                                                            <span class="font-medium text-slate-700" x-text="est.district"></span>
                                                            <span class="text-slate-300">•</span>
                                                            <span x-text="est.zone_region"></span>
                                                        </div>
                                                    </div>
                                                    <div class="shrink-0 flex items-center gap-1.5">
                                                        <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60" x-text="est.market_availability || 'Sale & Rent'"></span>
                                                        <span class="text-slate-400 text-xs">↵</span>
                                                    </div>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>

                                    <!-- Feedback Box 1: Verified Directory Estate Selected -->
                                    <template x-if="prop.selectedEstate && isDirState(prop.state)">
                                        <div class="mt-2 p-2.5 rounded-xl bg-emerald-50/80 border border-emerald-200 text-emerald-900 flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-base">🛡️</span>
                                                <div>
                                                    <strong class="font-bold block" x-text="'Verified ' + prop.state + ' Estate: ' + prop.selectedEstate.title"></strong>
                                                    <span class="text-[11px] text-emerald-700" x-text="(prop.selectedEstate.estate_category || 'Residential') + ' • ' + prop.selectedEstate.district + ' (' + prop.selectedEstate.zone_region + ')'"></span>
                                                </div>
                                            </div>
                                            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">Connected</span>
                                        </div>
                                    </template>

                                    <!-- Feedback Box 2: New Estate Inputted (Not in DB -> Will be Added to Lagos/Abuja DB) -->
                                    <template x-if="isDirState(prop.state) && (prop.query || '').trim().length >= 1 && !isKnownPropEstate(prop)">
                                        <div class="mt-2 p-3 rounded-xl bg-blue-50/90 border border-blue-200 text-blue-900 flex items-start gap-2.5 shadow-xs">
                                            <span class="text-base shrink-0">✨</span>
                                            <div class="flex-1">
                                                <div class="font-bold flex items-center gap-1.5">
                                                    <span>New Estate Detected:</span>
                                                    <span class="underline decoration-blue-400" x-text="'&ldquo;' + prop.query.trim() + '&rdquo;'"></span>
                                                </div>
                                                <p class="text-[11px] text-blue-700 mt-0.5 leading-relaxed">
                                                    This estate does not exist in our current database. Upon completing your registration, it will be <strong>automatically added to the current database of estates for <span x-text="prop.state"></span> precisely</strong>.
                                                </p>
                                            </div>
                                            <span class="shrink-0 text-[10px] font-black uppercase text-blue-800 bg-blue-100 px-2 py-0.5 rounded-full">Auto-Add On Submit</span>
                                        </div>
                                    </template>
                                </div>

                                <!-- Step 2c: Street Address Line -->
                                <div class="sm:col-span-2">
                                    <label class="block font-bold text-slate-700 mb-1">Street Address Line *</label>
                                    <input type="text" :name="'properties[' + pIdx + '][address_line_1]'" x-model="prop.addressLine" required
                                           placeholder="e.g. Plot 14 Admiralty Way, Off Freedom Way"
                                           class="w-full text-xs px-3.5 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1D4ED8] bg-white">
                                </div>

                                <!-- Step 2d: City / District -->
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">District / Suburb / City *</label>
                                    <input type="text" :name="'properties[' + pIdx + '][city]'" x-model="prop.city" required
                                           placeholder="e.g. Ikoyi, Lekki, Maitama, Gwarinpa, Ikeja"
                                           class="w-full text-xs px-3.5 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1D4ED8] bg-white">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Apartment Type *</label>
                                    <select :name="'properties[' + pIdx + '][apartment_type]'" x-model="prop.aptType" class="w-full text-xs px-3.5 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1D4ED8] bg-white">
                                        <option value="1-Bedroom Flat (Mini Flat)">1-Bedroom Flat (Mini Flat)</option>
                                        <option value="2-Bedroom Apartment">2-Bedroom Apartment</option>
                                        <option value="3-Bedroom Apartment">3-Bedroom Apartment</option>
                                        <option value="4-Bedroom Semi-Detached Duplex">4-Bedroom Semi-Detached Duplex</option>
                                        <option value="5-Bedroom Detached House">5-Bedroom Detached House</option>
                                        <option value="Penthouse Luxury Suite">Penthouse Luxury Suite</option>
                                        <option value="Studio Self-Contain">Studio Self-Contain</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Number of Units (Capacity) *</label>
                                    <input type="number" :name="'properties[' + pIdx + '][units_count]'" x-model="prop.unitsCount" min="1" max="100" required
                                           class="w-full text-xs px-3.5 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1D4ED8] bg-white">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Default Annual Rent Benchmark (NGN) *</label>
                                    <input type="number" :name="'properties[' + pIdx + '][default_rent_amount]'" x-model="prop.rentAmount" step="50000" min="100000" required
                                           class="w-full text-xs px-3.5 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1D4ED8] bg-white">
                                </div>

                                <!-- Ground Operations & Caretaker Delegation for this Property -->
                                <div class="sm:col-span-2 mt-2 pt-3 border-t border-slate-200/70">
                                    <label class="block font-bold text-slate-800 mb-2">Ground Operations & Caretaker Delegation for this Location</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        <!-- Choice A: SuperAdmin Concierge -->
                                        <label class="flex items-start gap-2.5 p-3 rounded-xl border transition cursor-pointer"
                                               :class="prop.caretakerMode === 'SUPERADMIN_CONCIERGE' ? 'border-[#1D4ED8] bg-blue-50/50' : 'border-slate-200 hover:border-slate-300 bg-white'">
                                            <input type="radio" :name="'properties[' + pIdx + '][caretaker_delegation_mode]'" value="SUPERADMIN_CONCIERGE" x-model="prop.caretakerMode" class="mt-0.5 text-[#1D4ED8] focus:ring-[#1D4ED8]">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-extrabold text-slate-900 text-xs">⚡ Oga Landlord SuperAdmin Concierge (Recommended)</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-blue-100 text-[#1D4ED8]">Platform Handled</span>
                                                </div>
                                                <p class="text-slate-600 mt-1 leading-relaxed text-[11px]">
                                                    Let the Oga Landlord central operations team handle maintenance ticketing, physical vendor coordination, and tenant supervision.
                                                </p>
                                            </div>
                                        </label>

                                        <!-- Choice B: Custom Caretaker -->
                                        <label class="flex items-start gap-2.5 p-3 rounded-xl border transition cursor-pointer"
                                               :class="prop.caretakerMode === 'CUSTOM_CARETAKER' ? 'border-emerald-500 bg-emerald-50/40' : 'border-slate-200 hover:border-slate-300 bg-white'">
                                            <input type="radio" :name="'properties[' + pIdx + '][caretaker_delegation_mode]'" value="CUSTOM_CARETAKER" x-model="prop.caretakerMode" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-extrabold text-slate-900 text-xs">🛠️ Nominate My Own Caretaker</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-emerald-100 text-emerald-800">Custom Staff</span>
                                                </div>
                                                <p class="text-slate-600 mt-1 leading-relaxed text-[11px]">
                                                    Designate your trusted on-site caretaker or estate facility manager to receive tickets and manage tasks.
                                                </p>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Expandable Caretaker Inputs if Custom Caretaker chosen -->
                                    <div x-show="prop.caretakerMode === 'CUSTOM_CARETAKER'" x-transition class="mt-3 pt-3 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-3 bg-white p-3 rounded-xl border border-slate-200">
                                        <div>
                                            <label class="block font-bold text-slate-700 mb-1 text-[11px]">Caretaker Full Name *</label>
                                            <input type="text" :name="'properties[' + pIdx + '][caretaker_name]'" x-model="prop.caretakerName" :required="prop.caretakerMode === 'CUSTOM_CARETAKER'"
                                                   placeholder="e.g. Musa Danjuma" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1D4ED8]">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-700 mb-1 text-[11px]">Caretaker Email *</label>
                                            <input type="email" :name="'properties[' + pIdx + '][caretaker_email]'" x-model="prop.caretakerEmail" :required="prop.caretakerMode === 'CUSTOM_CARETAKER'"
                                                   placeholder="e.g. caretaker@property.ng" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1D4ED8]">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-700 mb-1 text-[11px]">Caretaker Phone *</label>
                                            <input type="text" :name="'properties[' + pIdx + '][caretaker_phone]'" x-model="prop.caretakerPhone" :required="prop.caretakerMode === 'CUSTOM_CARETAKER'"
                                                   placeholder="e.g. +2348030000000" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1D4ED8]">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Prominent '+' Button to Add Another Property -->
                <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" @click="addProperty()"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl font-extrabold text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border-2 border-dashed border-indigo-300 flex items-center justify-center gap-2.5 transition shadow-xs hover:border-indigo-400">
                        <span class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-black shadow-2xs">+</span>
                        <span>+ Add Another Property / Location Across Nigeria</span>
                    </button>
                    <span class="hidden sm:inline text-xs text-slate-400 font-medium" x-text="properties.length + ' location(s) currently onboarded'"></span>
                </div>

                <!-- Fallback hidden inputs for backwards compatibility with single-property submissions -->
                <input type="hidden" name="property_title" :value="properties[0]?.query || ''">
                <input type="hidden" name="state" :value="properties[0]?.state || 'Abuja'">
                <input type="hidden" name="address_line_1" :value="properties[0]?.addressLine || ''">
                <input type="hidden" name="city" :value="properties[0]?.city || ''">
                <input type="hidden" name="apartment_type" :value="properties[0]?.aptType || ''">
                <input type="hidden" name="units_count" :value="properties[0]?.unitsCount || 4">
                <input type="hidden" name="default_rent_amount" :value="properties[0]?.rentAmount || 3500000">
                <input type="hidden" name="caretaker_delegation_mode" :value="properties[0]?.caretakerMode || 'SUPERADMIN_CONCIERGE'">
                <input type="hidden" name="caretaker_name" :value="properties[0]?.caretakerName || ''">
                <input type="hidden" name="caretaker_email" :value="properties[0]?.caretakerEmail || ''">
                <input type="hidden" name="caretaker_phone" :value="properties[0]?.caretakerPhone || ''">
            </div>

            <!-- Submit Button & Disclaimer -->
            <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-[11px] text-slate-500 text-center sm:text-left">
                    By registering, you agree to Oga Landlord Terms of Service and Nigerian Evidence Act 2011 compliance.
                </p>
                <button type="submit" class="w-full sm:w-auto bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-extrabold text-sm px-8 py-3 rounded-xl shadow-md transition flex items-center justify-center gap-2">
                    <span>Create Account & List Property →</span>
                </button>
            </div>

        </form>

    </main>

    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-400">
        Oga Landlord — Intelligent Property & Tenancy Management System
    </footer>

    <script>
    function registerApp() {
        return {
            properties: [
                {
                    id: 1,
                    state: '<?= htmlspecialchars($_POST['state'] ?? 'Abuja') ?>',
                    query: '<?= htmlspecialchars($_POST['property_title'] ?? '') ?>',
                    addressLine: '<?= htmlspecialchars($_POST['address_line_1'] ?? '') ?>',
                    city: '<?= htmlspecialchars($_POST['city'] ?? 'Maitama') ?>',
                    aptType: '<?= htmlspecialchars($_POST['apartment_type'] ?? '2-Bedroom Apartment') ?>',
                    unitsCount: <?= (int)($_POST['units_count'] ?? 4) ?>,
                    rentAmount: <?= (float)($_POST['default_rent_amount'] ?? 3500000) ?>,
                    caretakerMode: '<?= htmlspecialchars($_POST['caretaker_delegation_mode'] ?? 'SUPERADMIN_CONCIERGE') ?>',
                    caretakerName: '<?= htmlspecialchars($_POST['caretaker_name'] ?? '') ?>',
                    caretakerEmail: '<?= htmlspecialchars($_POST['caretaker_email'] ?? '') ?>',
                    caretakerPhone: '<?= htmlspecialchars($_POST['caretaker_phone'] ?? '+234') ?>',
                    selectedEstate: null,
                    filteredEstates: [],
                    showSuggestions: false,
                    highlightIndex: -1,
                }
            ],
            directory: {
                'Abuja': <?= json_encode($abujaEstates, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                'Lagos': <?= json_encode($lagosEstates, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
            },

            init() {
                this.properties.forEach(p => {
                    this.syncInitialEstate(p);
                });
            },

            syncInitialEstate(prop) {
                if (!this.isDirState(prop.state)) return;
                const q = (prop.query || '').trim().toLowerCase();
                if (q.length >= 1) {
                    const list = this.directory[prop.state] || [];
                    const match = list.find(e => e.title.toLowerCase() === q);
                    if (match) {
                        prop.selectedEstate = match;
                    }
                }
            },

            addProperty() {
                const nextId = this.properties.length > 0 ? Math.max(...this.properties.map(p => p.id)) + 1 : 1;
                const lastState = this.properties[this.properties.length - 1]?.state || 'Abuja';
                const defaultState = (lastState === 'Abuja') ? 'Lagos' : 'Abuja';
                const defaultCity = (defaultState === 'Lagos') ? 'Ikoyi' : 'Maitama';
                this.properties.push({
                    id: nextId,
                    state: defaultState,
                    query: '',
                    addressLine: '',
                    city: defaultCity,
                    aptType: '2-Bedroom Apartment',
                    unitsCount: 4,
                    rentAmount: 3500000,
                    caretakerMode: 'SUPERADMIN_CONCIERGE',
                    caretakerName: '',
                    caretakerEmail: '',
                    caretakerPhone: '+234',
                    selectedEstate: null,
                    filteredEstates: [],
                    showSuggestions: false,
                    highlightIndex: -1,
                });
            },

            removeProperty(index) {
                if (this.properties.length > 1) {
                    this.properties.splice(index, 1);
                }
            },

            isDirState(state) {
                return state === 'Abuja' || state === 'Lagos';
            },

            onPropStateChange(prop) {
                prop.query = '';
                prop.selectedEstate = null;
                prop.filteredEstates = [];
                prop.showSuggestions = false;
                prop.highlightIndex = -1;
                if (prop.state === 'Abuja') {
                    prop.city = 'Maitama';
                } else if (prop.state === 'Lagos') {
                    prop.city = 'Ikoyi';
                }
            },

            getPropPlaceholder(prop) {
                if (prop.state === 'Abuja') {
                    return 'Start typing Abuja estate name (e.g. Brains and Hammers, Sun City, PHDL Unity)...';
                }
                if (prop.state === 'Lagos') {
                    return 'Start typing Lagos estate name (e.g. Banana Island, 1004, Chevy View, Richmond Gate)...';
                }
                return 'Enter property or estate title (e.g. Palm View Luxury Apartments)';
            },

            searchPropEstates(prop) {
                if (!this.isDirState(prop.state)) {
                    prop.showSuggestions = false;
                    prop.filteredEstates = [];
                    return;
                }

                const q = (prop.query || '').trim().toLowerCase();
                if (q.length < 1) {
                    prop.filteredEstates = [];
                    prop.showSuggestions = false;
                    prop.selectedEstate = null;
                    prop.highlightIndex = -1;
                    return;
                }

                if (prop.selectedEstate && prop.selectedEstate.title.toLowerCase() !== q) {
                    prop.selectedEstate = null;
                }

                const list = this.directory[prop.state] || [];
                const prefixMatches = [];
                const substringMatches = [];
                const districtMatches = [];

                for (let i = 0; i < list.length; i++) {
                    const item = list[i];
                    const tLower = item.title.toLowerCase();
                    const dLower = (item.district || '').toLowerCase();

                    if (tLower.startsWith(q)) {
                        prefixMatches.push(item);
                    } else if (tLower.includes(q)) {
                        substringMatches.push(item);
                    } else if (dLower.includes(q)) {
                        districtMatches.push(item);
                    }
                }

                prop.filteredEstates = [...prefixMatches, ...substringMatches, ...districtMatches].slice(0, 15);
                prop.showSuggestions = prop.filteredEstates.length > 0;
                prop.highlightIndex = -1;

                const exact = list.find(e => e.title.toLowerCase() === q);
                if (exact) {
                    prop.selectedEstate = exact;
                }
            },

            selectPropEstate(prop, est) {
                prop.selectedEstate = est;
                prop.query = est.title;
                prop.showSuggestions = false;
                prop.highlightIndex = -1;

                if (est.district) {
                    prop.city = est.district;
                }
                if (!prop.addressLine || prop.addressLine.trim() === '') {
                    prop.addressLine = 'Plot 1, Road 2, ' + est.title + (est.district ? ', ' + est.district : '');
                }
            },

            isKnownPropEstate(prop) {
                const q = (prop.query || '').trim().toLowerCase();
                if (!q || !this.isDirState(prop.state)) return true;
                const list = this.directory[prop.state] || [];
                return list.some(e => e.title.toLowerCase() === q);
            },

            navigatePropSuggestions(prop, dir) {
                if (!prop.showSuggestions || prop.filteredEstates.length === 0) return;
                const total = prop.filteredEstates.length;
                prop.highlightIndex = (prop.highlightIndex + dir + total) % total;
            },

            selectHighlightedProp(prop) {
                if (prop.showSuggestions && prop.highlightIndex >= 0 && prop.highlightIndex < prop.filteredEstates.length) {
                    this.selectPropEstate(prop, prop.filteredEstates[prop.highlightIndex]);
                }
            }
        };
    }
    </script>
</body>
</html>
