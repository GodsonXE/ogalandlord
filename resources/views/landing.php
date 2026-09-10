<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oga Landlord — Multi-Location Property, Rent & Tenancy Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif; }
        .hero-gradient {
            background-color: #1D49CA;
            background: #1D49CA;
        }
        .hero-mesh {
            background-image: radial-gradient(rgba(255, 255, 255, 0.10) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        .glass-card {
            background: rgba(10, 25, 47, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.20);
        }
        .glass-card-light {
            background: rgba(255, 255, 255, 0.90);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.9);
        }
        .glow-orb-1 {
            background: radial-gradient(circle, rgba(255, 255, 255, 0.12) 0%, rgba(29, 73, 202, 0) 70%);
        }
        .glow-orb-2 {
            background: radial-gradient(circle, rgba(147, 197, 253, 0.20) 0%, rgba(29, 73, 202, 0) 70%);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased selection:bg-blue-100 selection:text-blue-800" x-data="{ openDashMenu: false, activeFeatureTab: 'rent' }">

<!-- 1. LANDLORDNG STYLE FIXED HEADER -->
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-100 transition-all duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        
        <!-- Logo -->
        <a href="/" class="flex items-center gap-3 group">
            <img src="/assets/images/logo.jpg" alt="Oga Landlord Logo" class="h-11 w-auto object-contain transition-transform group-hover:scale-105 rounded-md shadow-xs">
            <div class="flex flex-col">
                <span class="text-xl font-black tracking-tight text-slate-950">Oga<span class="text-[#1D4ED8]">Landlord</span></span>
                <span class="text-[9px] uppercase tracking-widest text-slate-400 font-bold">Property & Tenancy Engine</span>
            </div>
        </a>

        <!-- Navigation Links -->
        <nav class="hidden lg:flex items-center gap-7 text-xs font-bold text-slate-600">
            <a href="#rent-collection" class="hover:text-[#1D4ED8] transition-colors">Rent & Payments</a>
            <a href="#maintenance" class="hover:text-[#1D4ED8] transition-colors">Maintenance & Artisans</a>
            <a href="#tenants" class="hover:text-[#1D4ED8] transition-colors">Tenant KYC & E-Sign</a>
            <a href="#finances" class="hover:text-[#1D4ED8] transition-colors">Financials & Tax</a>
            <a href="#community" class="hover:text-[#1D4ED8] transition-colors">Estate & Community</a>
            <a href="#alerts" class="hover:text-[#1D4ED8] transition-colors">Alerts & Rules</a>
        </nav>

        <!-- Header Action CTAs -->
        <div class="flex items-center gap-2.5">
            <a href="/login" class="text-xs font-bold text-slate-700 hover:text-[#1D4ED8] px-3.5 py-2.5 rounded-xl border border-slate-200 hover:border-slate-300 transition">
                Sign In
            </a>

            <a href="/register" class="bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-xs font-extrabold px-4 py-2.5 rounded-xl shadow-xs transition hidden sm:inline-flex items-center gap-1.5">
                <span>Start Free Trial</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>

            <!-- Sandbox Quick Access Dropdown -->
            <div class="relative">
                <button type="button" @click="openDashMenu = !openDashMenu" @click.away="openDashMenu = false"
                        class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold px-3.5 py-2.5 rounded-xl transition inline-flex items-center gap-1.5">
                    <span>4 Roles Sandbox</span>
                    <svg class="w-3.5 h-3.5 transition-transform" :class="openDashMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openDashMenu" x-transition
                     class="absolute right-0 mt-2 w-64 bg-white border border-slate-200 rounded-2xl shadow-2xl py-2 z-50 text-xs">
                    <div class="px-4 py-1.5 text-[10px] uppercase tracking-wider font-extrabold text-slate-400">1-Click Test Play</div>
                    <a href="/demo/landlord" class="flex items-center gap-2.5 px-4 py-2.5 text-slate-700 hover:bg-blue-50 hover:text-[#1D4ED8] font-bold transition">
                        <span class="text-base">👔</span>
                        <div>
                            <div>Landlord Dashboard</div>
                            <span class="text-[10px] text-slate-400 font-normal">Income, Artisans, Alert Rules</span>
                        </div>
                    </a>
                    <a href="/demo/caretaker" class="flex items-center gap-2.5 px-4 py-2.5 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 font-bold transition">
                        <span class="text-base">🛠️</span>
                        <div>
                            <div>Caretaker Hub</div>
                            <span class="text-[10px] text-slate-400 font-normal">Nationwide Estates, Dispatch</span>
                        </div>
                    </a>
                    <a href="/demo/tenant" class="flex items-center gap-2.5 px-4 py-2.5 text-emerald-800 bg-emerald-50/70 hover:bg-emerald-100 font-extrabold transition">
                        <span class="text-base">🧪</span>
                        <div>
                            <div>Resident Portal (Demo)</div>
                            <span class="text-[10px] text-emerald-600 font-semibold">Photo Tickets, Notice Board, Dues</span>
                        </div>
                    </a>
                    <a href="/demo/superadmin" class="flex items-center gap-2.5 px-4 py-2.5 text-slate-700 hover:bg-purple-50 hover:text-purple-600 font-bold transition">
                        <span class="text-base">⚡</span>
                        <div>
                            <div>SuperAdmin Control</div>
                            <span class="text-[10px] text-slate-400 font-normal">Full Landlord Edit, Audit</span>
                        </div>
                    </a>
                    <div class="border-t border-slate-100 my-1"></div>
                    <a href="/register" class="flex items-center gap-2 px-4 py-2 text-[#1D4ED8] hover:bg-blue-50 font-black">
                        <span>➕ Register New Landlord</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- 2. LANDLORDNG STYLE RADIANT HERO SECTION -->
<section class="relative overflow-hidden hero-gradient hero-mesh text-white pt-16 pb-24 md:pt-24 md:pb-32 bg-[#1D49CA]" style="background-color: #1D49CA;">
    <!-- Ambient Glow Orbs -->
    <div class="absolute -top-32 left-1/4 w-96 h-96 glow-orb-1 rounded-full pointer-events-none filter blur-3xl"></div>
    <div class="absolute top-1/3 right-10 w-96 h-96 glow-orb-2 rounded-full pointer-events-none filter blur-3xl"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        
        <!-- Live Pulse Pill Badge -->
        <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full text-xs font-bold bg-slate-900/90 text-slate-100 border border-blue-400/40 backdrop-blur-md mb-8 shadow-lg shadow-black/30">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
            <span>Now Live across Nigeria — Web, Mobile &amp; Multi-Estate Operations</span>
        </div>

        <!-- Hero Main Headline -->
        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight text-white leading-[1.08] max-w-5xl mx-auto drop-shadow-sm">
            One Platform. Every Property. <br class="hidden sm:inline">
            <span class="bg-gradient-to-r from-sky-400 via-blue-300 to-cyan-300 bg-clip-text text-transparent">Zero Chaos.</span>
        </h1>

        <!-- Hero Subtitle -->
        <p class="mt-7 text-base sm:text-xl text-slate-100 max-w-3xl mx-auto font-normal leading-relaxed">
            Automate rent collection with SMS/email reminders, connect your Paystack or direct bank account, dispatch trusted artisans, verify tenant KYC, and view tax-ready financial analytics.
        </p>

        <!-- Primary Action CTAs -->
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4 max-w-xl mx-auto">
            <a href="/register" class="w-full sm:w-auto bg-white hover:bg-slate-100 text-[#1D4ED8] font-black text-sm px-8 py-4 rounded-xl shadow-2xl hover:scale-[1.02] transition-all duration-200 flex items-center justify-center gap-2 border border-white/20">
                <span>Start Free Trial (5 Units) →</span>
            </a>
            <a href="/demo/tenant" class="w-full sm:w-auto bg-slate-900/85 hover:bg-slate-800 text-white font-extrabold text-sm px-7 py-4 rounded-xl transition border border-blue-400/40 backdrop-blur-md flex items-center justify-center gap-2 shadow-lg shadow-black/30">
                <span>🧪 Test-Play Tenant Portal</span>
            </a>
            <a href="/login" class="w-full sm:w-auto text-slate-200 hover:text-white font-bold text-xs px-5 py-4 transition flex items-center justify-center gap-1.5">
                <span>Existing User? Login</span>
            </a>
        </div>

        <!-- Floating Glass Metric Badges & Interactive Dashboard Mockup Preview -->
        <div class="mt-16 max-w-5xl mx-auto relative">
            
            <!-- Left Floating Badge (0% Commission) -->
            <div class="hidden lg:flex items-center gap-3 bg-[#0A192F]/95 backdrop-blur-xl px-5 py-3.5 rounded-2xl absolute -left-12 top-14 shadow-2xl z-20 text-left border border-blue-400/40 animate-bounce" style="animation-duration: 4s;">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg font-black border border-emerald-500/30">
                    ₦
                </div>
                <div>
                    <div class="text-xs font-black text-white">0% Rent Commission</div>
                    <div class="text-[11px] text-slate-300 font-medium">Direct Paystack &amp; Bank Settled</div>
                </div>
            </div>

            <!-- Right Floating Badge (Instant SMS Alerts) -->
            <div class="hidden lg:flex items-center gap-3 bg-[#0A192F]/95 backdrop-blur-xl px-5 py-3.5 rounded-2xl absolute -right-12 top-28 shadow-2xl z-20 text-left border border-blue-400/40 animate-bounce" style="animation-duration: 5s;">
                <div class="w-10 h-10 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center text-lg font-black border border-sky-500/30">
                    ⚡
                </div>
                <div>
                    <div class="text-xs font-black text-white">Automated SMS & In-App</div>
                    <div class="text-[11px] text-slate-300 font-medium">Auto-Halts Upon Payment</div>
                </div>
            </div>

            <!-- Interactive Platform Showcase Window -->
            <div class="bg-[#0A192F]/95 backdrop-blur-2xl rounded-3xl p-5 sm:p-7 shadow-2xl border border-blue-500/30 text-left overflow-hidden">
                <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-5">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                        <span class="text-xs font-bold text-slate-100 ml-2">Oga Landlord Executive Suite — Real-time Snapshot</span>
                    </div>
                    <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">System Active</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Stat 1 -->
                    <div class="bg-[#061124]/90 border border-blue-500/20 rounded-2xl p-4 shadow-inner">
                        <div class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Annual Rent Realized</div>
                        <div class="text-2xl font-black text-white mt-1.5">₦18,500,000</div>
                        <div class="text-xs text-emerald-400 font-semibold mt-1">↑ 100% Reconciled</div>
                    </div>
                    <!-- Stat 2 -->
                    <div class="bg-[#061124]/90 border border-blue-500/20 rounded-2xl p-4 shadow-inner">
                        <div class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Active Artisans</div>
                        <div class="text-2xl font-black text-white mt-1.5">5 Verified</div>
                        <div class="text-xs text-blue-300 font-semibold mt-1">Plumbing, Power &amp; HVAC</div>
                    </div>
                    <!-- Stat 3 -->
                    <div class="bg-[#061124]/90 border border-blue-500/20 rounded-2xl p-4 shadow-inner">
                        <div class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Resident KYC</div>
                        <div class="text-2xl font-black text-white mt-1.5">100% Verified</div>
                        <div class="text-xs text-sky-300 font-semibold mt-1">NIN, BVN &amp; Employer</div>
                    </div>
                    <!-- Stat 4 -->
                    <div class="bg-[#061124]/90 border border-blue-500/20 rounded-2xl p-4 shadow-inner">
                        <div class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Tax Summary (WHT)</div>
                        <div class="text-2xl font-black text-white mt-1.5">₦1,714,000</div>
                        <div class="text-xs text-amber-300 font-semibold mt-1">10% WHT Schedule Ready</div>
                    </div>
                </div>

                <!-- Showcase Bottom Strip -->
                <div class="mt-5 pt-4 border-t border-slate-700/60 flex flex-wrap items-center justify-between text-xs text-slate-300 gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-400 font-bold">●</span>
                        <span>Latest: <strong class="text-white">Amara Okafor</strong> rent confirmed (Unit 1A — PHDL Unity Estate)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="/demo/landlord" class="text-sky-300 hover:text-white font-bold transition">Open Landlord Demo →</a>
                        <span class="text-white/20">|</span>
                        <a href="/demo/tenant" class="text-emerald-300 hover:text-emerald-200 font-bold transition">Open Resident Demo →</a>
                    </div>
                </div>
            </div>

        </div>

        <!-- 4-Stat Strip Under Hero (Matching landlordng style) -->
        <div class="mt-14 pt-10 border-t border-white/15 grid grid-cols-2 md:grid-cols-4 gap-6 text-center max-w-4xl mx-auto">
            <div>
                <div class="text-2xl sm:text-3xl font-black text-white">Free Starter</div>
                <div class="text-xs text-slate-300 font-medium mt-1">Up to 5 units forever</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-black text-white">Real-Time</div>
                <div class="text-xs text-slate-300 font-medium mt-1">Payment Tracking</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-black text-white">Automated</div>
                <div class="text-xs text-slate-300 font-medium mt-1">SMS, Email &amp; Push</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-black text-white">99.9%</div>
                <div class="text-xs text-slate-300 font-medium mt-1">Uptime &amp; Security</div>
            </div>
        </div>

    </div>
</section>

<!-- 3. NATIONWIDE TRUST & CITIES MARQUEE STRIP -->
<div class="bg-slate-900 text-slate-400 py-6 border-y border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-xs font-bold uppercase tracking-widest text-center">
            <span class="text-slate-300">Operational Across All Nigerian Real Estate Hubs</span>
            <div class="flex flex-wrap items-center justify-center gap-6 sm:gap-8 text-slate-200">
                <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#1D4ED8]"></span> Abuja FCT</span>
                <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#1D4ED8]"></span> Lagos (Island & Mainland)</span>
                <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#1D4ED8]"></span> Port Harcourt</span>
                <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#1D4ED8]"></span> Ibadan</span>
                <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#1D4ED8]"></span> Enugu</span>
                <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#1D4ED8]"></span> Kano</span>
            </div>
        </div>
    </div>
</div>

<!-- 4. CORE FEATURE PILLARS (THE 6 REQUESTED MODULES) -->
<section class="py-20 lg:py-28 bg-white" id="features">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-xs font-black tracking-widest uppercase text-[#1D4ED8] bg-blue-50 border border-blue-100 px-3 py-1 rounded-full">
                Built for Nigerian Landlords & Estates
            </span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 tracking-tight mt-4">
                Everything You Need to Run Your Properties With Precision
            </h2>
            <p class="text-slate-600 font-medium text-base sm:text-lg mt-4">
                Say goodbye to fragmented WhatsApp chats, forgotten rent dues, unverified artisans, and messy paper receipts.
            </p>
        </div>

        <!-- 6 Feature Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <!-- Pillar 1: Rent Collection & Payment Tracking -->
            <div id="rent-collection" class="bg-slate-50 rounded-3xl p-8 border border-slate-200 hover:border-blue-300 hover:shadow-xl transition flex flex-col justify-between group">
                <div>
                    <div class="w-14 h-14 rounded-2xl bg-blue-100 text-[#1D4ED8] flex items-center justify-center text-2xl font-black mb-6 group-hover:scale-110 transition-transform">
                        💳
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-3">Rent Collection & Payment Tracking</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">
                        Collect rent directly into your bank or Paystack account. Zero middlemen holding your funds.
                    </p>
                    <ul class="space-y-3 text-xs font-semibold text-slate-700">
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Automated reminders</strong> via SMS, email & in-app alerts at T-7, T-3, and due date.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Connect your payment gateway:</strong> Paystack, Flutterwave, or direct bank transfer.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Real-time payment confirmation:</strong> Instant QR-stamped PDF receipts.</span>
                        </li>
                    </ul>
                </div>
                <div class="mt-8 pt-6 border-t border-slate-200/80">
                    <a href="/demo/landlord" class="text-xs font-black text-[#1D4ED8] hover:text-blue-900 flex items-center gap-1.5">
                        <span>View Rent Collection Sandbox →</span>
                    </a>
                </div>
            </div>

            <!-- Pillar 2: Maintenance Request Management -->
            <div id="maintenance" class="bg-slate-50 rounded-3xl p-8 border border-slate-200 hover:border-blue-300 hover:shadow-xl transition flex flex-col justify-between group">
                <div>
                    <div class="w-14 h-14 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-2xl font-black mb-6 group-hover:scale-110 transition-transform">
                        🛠️
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-3">Maintenance Request Management</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">
                        From tenant issue reporting to dispatching vetted artisans and tracking invoices.
                    </p>
                    <ul class="space-y-3 text-xs font-semibold text-slate-700">
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Photo-based issue reporting:</strong> Tenants attach pictures of leakages or faults.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Assign requests to trusted artisans:</strong> Plumbers, electricians, HVAC & carpenters.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Status tracking from report to resolution:</strong> Track estimated vs actual repair costs.</span>
                        </li>
                    </ul>
                </div>
                <div class="mt-8 pt-6 border-t border-slate-200/80">
                    <a href="/demo/tenant" class="text-xs font-black text-indigo-700 hover:text-indigo-900 flex items-center gap-1.5">
                        <span>Try Photo Reporting as Tenant →</span>
                    </a>
                </div>
            </div>

            <!-- Pillar 3: Tenant Management & Records -->
            <div id="tenants" class="bg-slate-50 rounded-3xl p-8 border border-slate-200 hover:border-blue-300 hover:shadow-xl transition flex flex-col justify-between group">
                <div>
                    <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl font-black mb-6 group-hover:scale-110 transition-transform">
                        📋
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-3">Tenant Management & Records</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">
                        Invitation-only onboarding with identity verification and legally binding electronic leases.
                    </p>
                    <ul class="space-y-3 text-xs font-semibold text-slate-700">
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Digital tenant onboarding & KYC:</strong> Collect NIN, BVN, employer & guarantor data.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Lease document storage & e-signatures:</strong> Legally valid, SHA-256 tamper-proof seals.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Tenant communication hub:</strong> Scoped message threads between tenant and caretaker.</span>
                        </li>
                    </ul>
                </div>
                <div class="mt-8 pt-6 border-t border-slate-200/80">
                    <a href="/agreement/preview" class="text-xs font-black text-emerald-700 hover:text-emerald-900 flex items-center gap-1.5">
                        <span>Preview E-Sign Tenancy Agreement →</span>
                    </a>
                </div>
            </div>

            <!-- Pillar 4: Financial Reporting & Analytics -->
            <div id="finances" class="bg-slate-50 rounded-3xl p-8 border border-slate-200 hover:border-blue-300 hover:shadow-xl transition flex flex-col justify-between group">
                <div>
                    <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl font-black mb-6 group-hover:scale-110 transition-transform">
                        📊
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-3">Financial Reporting & Analytics</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">
                        Real-time Net Operating Income (NOI) with Withholding Tax calculations ready for filing.
                    </p>
                    <ul class="space-y-3 text-xs font-semibold text-slate-700">
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Income & expense tracking per property:</strong> Log generator fuel, security, and repairs.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Automated monthly & annual reports:</strong> Cashflow, occupancy rates, and yields.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Tax-ready financial summaries:</strong> WHT @ 10% schedule and deductible expense ledger.</span>
                        </li>
                    </ul>
                </div>
                <div class="mt-8 pt-6 border-t border-slate-200/80">
                    <a href="/demo/landlord" class="text-xs font-black text-amber-700 hover:text-amber-900 flex items-center gap-1.5">
                        <span>View Financial Analytics Tab →</span>
                    </a>
                </div>
            </div>

            <!-- Pillar 5: Estate & Community Management -->
            <div id="community" class="bg-slate-50 rounded-3xl p-8 border border-slate-200 hover:border-blue-300 hover:shadow-xl transition flex flex-col justify-between group">
                <div>
                    <div class="w-14 h-14 rounded-2xl bg-sky-100 text-sky-700 flex items-center justify-center text-2xl font-black mb-6 group-hover:scale-110 transition-transform">
                        🏛️
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-3">Estate & Community Management</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">
                        Keep residents informed and streamline shared facilities without WhatsApp drama.
                    </p>
                    <ul class="space-y-3 text-xs font-semibold text-slate-700">
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Bulk communication to residents:</strong> Instant broadcasts via SMS, email & dashboard.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Digital notice board & announcements:</strong> Security updates, power schedules & AGM notices.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Facility booking management:</strong> Reserve clubhouse, tennis court, pool or gym with approval.</span>
                        </li>
                    </ul>
                </div>
                <div class="mt-8 pt-6 border-t border-slate-200/80">
                    <a href="/demo/tenant" class="text-xs font-black text-sky-700 hover:text-sky-900 flex items-center gap-1.5">
                        <span>Check Community Notice Board →</span>
                    </a>
                </div>
            </div>

            <!-- Pillar 6: Notifications & Alerts -->
            <div id="alerts" class="bg-slate-50 rounded-3xl p-8 border border-slate-200 hover:border-blue-300 hover:shadow-xl transition flex flex-col justify-between group">
                <div>
                    <div class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center text-2xl font-black mb-6 group-hover:scale-110 transition-transform">
                        🔔
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-3">Notifications & Alerts</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">
                        Custom alert rules that adapt to your leases, grace periods, and overdue penalties.
                    </p>
                    <ul class="space-y-3 text-xs font-semibold text-slate-700">
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Instant SMS & push notifications:</strong> Sent on lease signing, payments, and tickets.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Customizable alert rules:</strong> Configure grace periods (e.g. 7 days) and late penalty rates.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span><strong>Smart rent due reminders:</strong> Auto-halts reminder sequence once payment is confirmed.</span>
                        </li>
                    </ul>
                </div>
                <div class="mt-8 pt-6 border-t border-slate-200/80">
                    <a href="/demo/landlord" class="text-xs font-black text-purple-700 hover:text-purple-900 flex items-center gap-1.5">
                        <span>Configure Alert Rules in Sandbox →</span>
                    </a>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- 5. INTERACTIVE 4-ROLE SANDBOX SECTION -->
<section class="py-20 bg-slate-100 border-t border-slate-200/80" id="roles">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="text-xs font-black tracking-widest uppercase text-[#1D4ED8] bg-blue-100/60 px-3 py-1 rounded-full">
                Interactive Testing Playground
            </span>
            <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight mt-3">
                Experience Oga Landlord From Every Perspective
            </h2>
            <p class="text-slate-600 text-sm sm:text-base font-medium mt-3">
                Click any role below to launch directly into an active, fully-populated demo environment. No password required.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Role 1: Landlord -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#1D4ED8] flex items-center justify-center text-xl mb-4 font-bold">
                        👔
                    </div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-[#1D4ED8] mb-1">Portfolio Owner</div>
                    <h4 class="text-lg font-black text-slate-900">Landlord Workspace</h4>
                    <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                        Manage properties, monitor NOI & tax, dispatch artisans, set payment gateways, and configure automated alert rules.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100">
                    <a href="/demo/landlord" class="w-full bg-[#1D4ED8] hover:bg-blue-800 text-white font-black text-xs py-2.5 px-4 rounded-xl flex items-center justify-center gap-1.5 transition">
                        <span>Launch Landlord Portal</span>
                    </a>
                </div>
            </div>

            <!-- Role 2: Caretaker -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mb-4 font-bold">
                        🛠️
                    </div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-indigo-600 mb-1">Estate Supervisor</div>
                    <h4 class="text-lg font-black text-slate-900">Caretaker Hub</h4>
                    <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                        Handle maintenance tickets, assign artisans, publish community notice board announcements, and approve facilities.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100">
                    <a href="/demo/caretaker" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs py-2.5 px-4 rounded-xl flex items-center justify-center gap-1.5 transition">
                        <span>Launch Caretaker Hub</span>
                    </a>
                </div>
            </div>

            <!-- Role 3: Tenant (Highlighted Test Play) -->
            <div class="bg-white rounded-2xl p-6 border-2 border-emerald-500 shadow-md flex flex-col justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0 bg-emerald-500 text-white text-[9px] font-black uppercase px-3 py-1 rounded-bl-lg">
                    Recommended
                </div>
                <div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mb-4 font-bold">
                        🧪
                    </div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-emerald-600 mb-1">Resident Test-Play</div>
                    <h4 class="text-lg font-black text-slate-900">Tenant Module</h4>
                    <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                        Photo-based complaints, notice board, facility reservations, receipt downloads, and lease agreement review.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100">
                    <a href="/demo/tenant" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs py-2.5 px-4 rounded-xl flex items-center justify-center gap-1.5 transition">
                        <span>Launch Tenant Test Play</span>
                    </a>
                </div>
            </div>

            <!-- Role 4: SuperAdmin -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl mb-4 font-bold">
                        ⚡
                    </div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-purple-600 mb-1">Full Editable Rights</div>
                    <h4 class="text-lg font-black text-slate-900">SuperAdmin Center</h4>
                    <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                        Full editable access to all landlords, set global alert rules, approve tenant KYC, and run deep estate audits.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100">
                    <a href="/demo/superadmin" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-black text-xs py-2.5 px-4 rounded-xl flex items-center justify-center gap-1.5 transition">
                        <span>Launch SuperAdmin</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. LANDLORD ONBOARDING CALL TO ACTION -->
<section class="py-20 bg-gradient-to-br from-blue-900 via-[#1D4ED8] to-indigo-950 text-white relative overflow-hidden">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <h2 class="text-3xl sm:text-5xl font-black tracking-tight">
            Start Managing Your Properties Like a Pro Today
        </h2>
        <p class="mt-5 text-base sm:text-lg text-blue-100 max-w-2xl mx-auto">
            Self-service onboarding in 2 minutes. Add your units, invite your residents, set your bank or Paystack details, and automate rent collection.
        </p>
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/register" class="w-full sm:w-auto bg-white text-[#1D4ED8] hover:bg-blue-50 font-black text-sm px-8 py-4 rounded-xl shadow-xl transition">
                Register as Landlord Now →
            </a>
            <a href="/demo/landlord" class="w-full sm:w-auto glass-card text-white hover:bg-white/10 font-bold text-sm px-8 py-4 rounded-xl transition">
                Explore Demo First
            </a>
        </div>
        <div class="mt-8 text-xs text-blue-200">
            No credit card required • 100% Free Starter Plan up to 5 units
        </div>
    </div>
</section>

<!-- 7. LANDLORDNG STYLE DARK FOOTER -->
<footer class="bg-slate-950 text-slate-400 text-xs py-14 border-t border-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-12">
            
            <div class="md:col-span-1">
                <div class="flex items-center gap-3 mb-4">
                    <img src="/assets/images/logo.jpg" alt="Oga Landlord" class="h-9 w-auto rounded">
                    <span class="text-lg font-black text-white">Oga<span class="text-[#1D4ED8]">Landlord</span></span>
                </div>
                <p class="text-slate-500 text-xs leading-relaxed mb-4">
                    The nationwide property, rent collection, and tenancy management operating system for Nigerian landlords and residents.
                </p>
                <div class="text-[11px] text-slate-500">
                    RC: 1982341 • Federal Republic of Nigeria
                </div>
            </div>

            <div>
                <h5 class="text-xs font-black uppercase tracking-wider text-slate-200 mb-4">Core Modules</h5>
                <ul class="space-y-2.5">
                    <li><a href="#rent-collection" class="hover:text-white transition">Rent Collection & Gateway</a></li>
                    <li><a href="#maintenance" class="hover:text-white transition">Maintenance & Artisans</a></li>
                    <li><a href="#tenants" class="hover:text-white transition">Digital KYC & E-Sign</a></li>
                    <li><a href="#finances" class="hover:text-white transition">Financials & Tax (WHT)</a></li>
                    <li><a href="#community" class="hover:text-white transition">Estate Notice Board</a></li>
                    <li><a href="#alerts" class="hover:text-white transition">Automated Alert Rules</a></li>
                </ul>
            </div>

            <div>
                <h5 class="text-xs font-black uppercase tracking-wider text-slate-200 mb-4">Portals & Sandboxes</h5>
                <ul class="space-y-2.5">
                    <li><a href="/register" class="text-blue-400 font-bold hover:underline">Landlord Registration</a></li>
                    <li><a href="/login" class="hover:text-white transition">Universal Sign In</a></li>
                    <li><a href="/demo/landlord" class="hover:text-white transition">Landlord Dashboard Demo</a></li>
                    <li><a href="/demo/tenant" class="text-emerald-400 font-bold hover:underline">Tenant Test-Play</a></li>
                    <li><a href="/demo/caretaker" class="hover:text-white transition">Caretaker Hub Demo</a></li>
                    <li><a href="/demo/superadmin" class="hover:text-white transition">SuperAdmin Console</a></li>
                </ul>
            </div>

            <div>
                <h5 class="text-xs font-black uppercase tracking-wider text-slate-200 mb-4">Contact & Support</h5>
                <p class="text-slate-500 text-xs leading-relaxed mb-3">
                    Support Desk: Plot 42 Railway Corridor, Idu Industrial, Abuja FCT.<br>
                    Email: support@ogalandlord.ng<br>
                    WhatsApp Helpline: +234 803 000 0001
                </p>
                <div class="flex items-center gap-2 text-[10px] text-slate-500">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Systems Operational (99.9% Uptime)</span>
                </div>
            </div>

        </div>

        <div class="border-t border-slate-900 pt-8 flex flex-col sm:flex-row items-center justify-between text-slate-600 text-[11px] gap-4">
            <div>
                © 2026 Oga Landlord Technologies Ltd. All rights reserved. Built with precision for Nigerian Real Estate.
            </div>
            <div class="flex items-center gap-5">
                <a href="#" class="hover:text-slate-400">Terms of Tenancy</a>
                <a href="#" class="hover:text-slate-400">Privacy & KYC Policy</a>
                <a href="#" class="hover:text-slate-400">Security Standards</a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>