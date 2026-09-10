<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Restricted — Tenant Signature Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="h-full flex items-center justify-center p-4 text-slate-800 antialiased">

<div class="max-w-lg w-full bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden">
    
    <!-- Top Accent Bar with Primary Color #1D4ED8 -->
    <div class="h-2 bg-[#1D4ED8]"></div>

    <div class="p-6 sm:p-8">
        <!-- Logo and Badge -->
        <div class="flex items-center justify-between mb-6">
            <a href="/" class="flex items-center gap-2">
                <img src="/assets/images/logo.jpg" alt="Logo" class="h-10 w-auto object-contain rounded p-0.5 border border-slate-100 shadow-xs">
                <span class="font-extrabold text-slate-900 text-base">Oga<span class="text-[#1D4ED8]">Landlord</span></span>
            </a>
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                <svg class="w-3.5 h-3.5 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                Access Restricted
            </span>
        </div>

        <!-- Lock Icon & Header -->
        <div class="text-center sm:text-left mb-6">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 border border-blue-200 flex items-center justify-center text-[#1D4ED8] mb-4 shadow-inner">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Tenant Signature Portal is Restricted</h1>
            <p class="text-xs text-slate-500 mt-1">Caretakers and Administrators cannot access the resident signature portal.</p>
        </div>

        <!-- Role Context Box -->
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-6">
            <div class="flex items-center justify-between text-xs pb-2.5 border-b border-slate-200">
                <span class="text-slate-500 font-medium">Logged-in User</span>
                <span class="font-bold text-slate-900"><?= htmlspecialchars($currentUser['full_name'] ?? 'Musa Danjuma') ?></span>
            </div>
            <div class="flex items-center justify-between text-xs pt-2.5">
                <span class="text-slate-500 font-medium">Current Role</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-extrabold bg-blue-100 text-[#1D4ED8]">
                    <?= htmlspecialchars($currentUser['role'] ?? 'CARETAKER') ?>
                </span>
            </div>
        </div>

        <!-- Compliance & Legal Explanation -->
        <div class="space-y-2.5 text-xs text-slate-600 leading-relaxed mb-6">
            <p>
                <strong>Legal Protection & Role Separation:</strong> Pursuant to the <em>Evidence Act 2011</em> and digital signature integrity protocols, only the registered Tenant is authorized to execute signatures inside this portal.
            </p>
            <p>
                Caretakers, Landlords, and Administrators are strictly prevented from submitting or forging tenant signatures. However, you have complete authority to <strong>inspect and preview</strong> the signed agreements, track signing statuses, and review completion certificates from your management console.
            </p>
        </div>

        <!-- Action CTAs -->
        <div class="flex flex-col gap-2.5">
            <?php if (($currentUser['role'] ?? '') === 'CARETAKER'): ?>
                <a href="/caretaker?tab=agreements" class="w-full text-center bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-bold text-xs py-3 px-4 rounded-xl shadow-md transition">
                    📋 Open Tenancy Agreements & Tenants List →
                </a>
                <a href="/caretaker" class="w-full text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs py-2.5 px-4 rounded-xl transition">
                    Return to Caretaker Hub
                </a>
            <?php elseif (($currentUser['role'] ?? '') === 'SUPERADMIN'): ?>
                <a href="/admin" class="w-full text-center bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-bold text-xs py-3 px-4 rounded-xl shadow-md transition">
                    ← Return to SuperAdmin Control Center
                </a>
                <a href="/agreement/preview" class="w-full text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs py-2.5 px-4 rounded-xl transition">
                    Preview Signed Agreements Audit
                </a>
            <?php else: ?>
                <a href="/landlord" class="w-full text-center bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-bold text-xs py-3 px-4 rounded-xl shadow-md transition">
                    ← Return to Landlord Dashboard
                </a>
            <?php endif; ?>

            <a href="/logout" class="w-full text-center text-rose-600 hover:text-rose-800 text-[11px] font-semibold py-1.5 transition">
                Sign Out (To Sign In as Tenant)
            </a>
        </div>

    </div>

    <!-- Security Footer -->
    <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 text-[11px] text-slate-400 flex items-center justify-between">
        <span>Oga Landlord RBAC Protection</span>
        <span>Evidence Act 2011 Compliant</span>
    </div>

</div>

</body>
</html>
