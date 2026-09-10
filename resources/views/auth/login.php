<?php
/** @var App\Domain\Auth\Services\AuthService|null $auth */
$authInstance = $auth ?? ($this->auth ?? null);
$currentUser = $authInstance ? $authInstance->currentUser() : null;
$presetRole = strtolower($_GET['role'] ?? ($currentUser['role'] ?? 'landlord'));
$presetEmail = match($presetRole) {
    'caretaker', 'admin' => 'caretaker.idu@ogalandlord.ng',
    'superadmin' => 'superadmin@ogalandlord.ng',
    'tenant' => 'amara.okafor@example.com',
    default => 'landlord@ogalandlord.ng',
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Oga Landlord</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex items-center justify-center p-4">

<div class="max-w-md w-full my-8" x-data="loginForm('<?= $presetEmail ?>', '<?= $presetRole ?>')">

    <!-- Card -->
    <div class="bg-white border border-slate-200/90 border-t-4 border-t-[#1D4ED8] rounded-2xl p-6 sm:p-8 shadow-sm">
        
        <!-- Logo & Header -->
        <div class="text-center mb-6">
            <a href="/" class="inline-block group mb-3">
                <img src="/assets/images/logo.jpg" alt="Oga Landlord Logo" class="h-16 w-auto mx-auto object-contain transition-transform group-hover:scale-105">
            </a>
            <h1 class="text-2xl font-extrabold text-slate-900">Sign In to Platform</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Access your property management workspace</p>
        </div>

        <!-- Active Session Switch Notice -->
        <?php if ($currentUser): ?>
            <div class="mb-5 p-3.5 rounded-xl bg-blue-50/80 border border-blue-200 text-xs text-blue-900 flex items-center justify-between">
                <div>
                    <span class="font-bold">Active Session:</span>
                    <span class="block text-slate-700"><?= htmlspecialchars($currentUser['full_name']) ?> (<?= htmlspecialchars($currentUser['role']) ?>)</span>
                </div>
                <a href="<?= $auth->redirectPathForRole($currentUser['role']) ?>" class="bg-[#1D4ED8] text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-[#1E40AF] transition">
                    Dashboard →
                </a>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs font-medium text-rose-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['logged_out'])): ?>
            <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-700">
                You have been safely signed out.
            </div>
        <?php endif; ?>

        <!-- Role Quick Switch Tabs -->
        <div class="mb-5">
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Select User Role</label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 p-1 bg-slate-100 rounded-xl text-xs font-semibold">
                <button type="button" @click="selectRole('landlord', 'landlord@ogalandlord.ng')"
                        :class="selectedRole === 'landlord' ? 'bg-white text-[#1D4ED8] shadow-sm font-bold border border-blue-200' : 'text-slate-600 hover:text-slate-900'"
                        class="py-2 rounded-lg transition text-center">
                    👔 Landlord
                </button>
                <button type="button" @click="selectRole('caretaker', 'caretaker.idu@ogalandlord.ng')"
                        :class="selectedRole === 'caretaker' ? 'bg-white text-indigo-700 shadow-sm font-bold border border-indigo-200' : 'text-slate-600 hover:text-slate-900'"
                        class="py-2 rounded-lg transition text-center">
                    🛠️ Caretaker
                </button>
                <button type="button" @click="selectRole('superadmin', 'superadmin@ogalandlord.ng')"
                        :class="selectedRole === 'superadmin' ? 'bg-white text-purple-700 shadow-sm font-bold border border-purple-200' : 'text-slate-600 hover:text-slate-900'"
                        class="py-2 rounded-lg transition text-center">
                    ⚡ SuperAdmin
                </button>
                <button type="button" @click="selectRole('tenant', 'amara.okafor@example.com')"
                        :class="selectedRole === 'tenant' ? 'bg-white text-emerald-700 shadow-sm font-bold border border-emerald-200' : 'text-slate-600 hover:text-slate-900'"
                        class="py-2 rounded-lg transition text-center">
                    🏠 Tenant <span class="text-[9px] bg-emerald-100 text-emerald-800 px-1 py-0.5 rounded font-bold">Test</span>
                </button>
            </div>
        </div>

        <!-- Form -->
        <form action="/login" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Email Address</label>
                <input type="email" name="email" x-model="email" required placeholder="you@ogalandlord.ng"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8] text-sm transition">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Password</label>
                    <span class="text-xs text-slate-400">Default: password123</span>
                </div>
                <input type="password" name="password" x-model="password" required placeholder="••••••••"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8] text-sm transition">
            </div>

            <button type="submit"
                    class="w-full mt-2 bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-bold text-sm py-3 rounded-xl shadow-xs transition flex items-center justify-center gap-2">
                <span>Sign In to Selected Workspace</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </form>

        <!-- Landlord Self-Registration Callout -->
        <div class="mt-5 p-3.5 rounded-xl bg-blue-50/70 border border-blue-200/90 text-center">
            <p class="text-xs font-semibold text-slate-800">New Landlord? Onboard properties nationwide</p>
            <a href="/register" class="mt-2 inline-flex items-center justify-center gap-1.5 w-full bg-white hover:bg-[#1D4ED8] hover:text-white text-[#1D4ED8] border border-blue-300 font-bold text-xs py-2 px-3 rounded-lg transition shadow-xs">
                <span>Create Landlord Account & List Property →</span>
            </a>
            <p class="text-[10px] text-slate-400 mt-1.5">Tenants join strictly by Landlord digital invitation</p>
        </div>

        <!-- Quick 1-Click Demo Direct Logins -->
        <div class="mt-6 pt-5 border-t border-slate-100">
            <span class="block text-[11px] uppercase tracking-wider font-bold text-slate-400 text-center mb-3">Or 1-Click Instant Dashboard Launch</span>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                <!-- Landlord (Blue) -->
                <a href="/demo/landlord"
                   class="p-3 rounded-xl border border-slate-200 border-t-4 border-t-[#1D4ED8] hover:bg-blue-50/50 text-center transition block group shadow-xs">
                    <span class="block font-bold text-slate-800 group-hover:text-[#1D4ED8]">Landlord</span>
                    <span class="text-[10px] text-[#1D4ED8] font-bold">Launch →</span>
                </a>

                <!-- Caretaker (Indigo) -->
                <a href="/demo/caretaker"
                   class="p-3 rounded-xl border border-slate-200 border-t-4 border-t-indigo-600 hover:bg-indigo-50/50 text-center transition block group shadow-xs">
                    <span class="block font-bold text-slate-800 group-hover:text-indigo-700">Caretaker</span>
                    <span class="text-[10px] text-indigo-700 font-bold">Launch →</span>
                </a>

                <!-- SuperAdmin (Purple) -->
                <a href="/demo/superadmin"
                   class="p-3 rounded-xl border border-slate-200 border-t-4 border-t-purple-600 hover:bg-purple-50/50 text-center transition block group shadow-xs">
                    <span class="block font-bold text-slate-800 group-hover:text-purple-700">SuperAdmin</span>
                    <span class="text-[10px] text-purple-700 font-bold">Launch →</span>
                </a>

                <!-- Tenant (Emerald) - Interactive Test Play -->
                <a href="/demo/tenant"
                   class="p-3 rounded-xl border border-slate-200 border-t-4 border-t-emerald-600 hover:bg-emerald-50/50 text-center transition block group shadow-xs relative">
                    <span class="block font-bold text-slate-800 group-hover:text-emerald-700">Tenant</span>
                    <span class="inline-block mt-0.5 text-[9px] bg-emerald-100 text-emerald-800 font-extrabold px-1.5 py-0.2 rounded-full uppercase">Test Play →</span>
                </a>
            </div>
        </div>

    </div>

    <!-- Back to landing -->
    <div class="text-center mt-6">
        <a href="/" class="text-xs text-slate-500 hover:text-slate-800 transition inline-flex items-center gap-1 font-medium">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Return to Homepage
        </a>
    </div>

</div>

<script>
function loginForm(initialEmail, initialRole) {
    return {
        email: initialEmail,
        password: 'password123',
        selectedRole: initialRole,
        selectRole(role, email) {
            this.selectedRole = role;
            this.email = email;
            this.password = 'password123';
        }
    }
}
</script>
</body>
</html>