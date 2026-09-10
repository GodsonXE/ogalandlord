<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Restricted — Oga Landlord</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex items-center justify-center p-4">
<div class="max-w-md w-full bg-white border border-slate-200 rounded-2xl p-8 text-center shadow-sm">
    <a href="/">
        <img src="/assets/images/logo.jpg" alt="Oga Landlord" class="h-14 mx-auto mb-6 object-contain">
    </a>
    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 mb-3">
        Access Restricted (403)
    </span>
    <h1 class="text-xl font-bold text-slate-900 mb-2">Role Permission Required</h1>
    <p class="text-sm text-slate-500 mb-6">Your current active session does not have permission to view this workspace. Switch to an authorized account below:</p>

    <!-- 1-Click Role Switchers -->
    <div class="space-y-2 mb-6 text-left">
        <a href="/demo/landlord" class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-[#1D4ED8] hover:bg-blue-50/50 transition">
            <div>
                <span class="block text-xs font-bold text-slate-900">👔 Chief Ibrahim Bello</span>
                <span class="text-[11px] text-slate-500">Switch to Landlord Workspace</span>
            </div>
            <span class="text-xs font-semibold text-[#1D4ED8]">Enter →</span>
        </a>
        <a href="/demo/caretaker" class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-amber-500 hover:bg-amber-50/50 transition">
            <div>
                <span class="block text-xs font-bold text-slate-900">🛠️ Musa Danjuma</span>
                <span class="text-[11px] text-slate-500">Switch to Caretaker Hub</span>
            </div>
            <span class="text-xs font-semibold text-amber-600">Enter →</span>
        </a>
        <a href="/demo/superadmin" class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-slate-800 hover:bg-slate-50 transition">
            <div>
                <span class="block text-xs font-bold text-slate-900">⚡ Alhaji Farouk Al-Mansur</span>
                <span class="text-[11px] text-slate-500">Switch to SuperAdmin Center</span>
            </div>
            <span class="text-xs font-semibold text-slate-800">Enter →</span>
        </a>
    </div>

    <div class="flex flex-col sm:flex-row gap-2">
        <a href="/login?switch=1" class="w-full bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-medium text-xs py-2.5 rounded-lg transition shadow-sm">
            Sign In with Password
        </a>
        <a href="/" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs py-2.5 rounded-lg transition">
            Return to Homepage
        </a>
    </div>
</div>
</body>
</html>