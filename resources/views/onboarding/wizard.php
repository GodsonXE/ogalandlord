<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$db = new \App\Infrastructure\Database\Connection();
$pdo = $db->getPdo();
$currentLandlordId = (int)($_SESSION['user_id'] ?? 2);
$isSuperAdmin = (($_SESSION['user_role'] ?? '') === 'SUPERADMIN');

if ($isSuperAdmin) {
    $existingProperties = $pdo->query("SELECT id, title, city, state, address_line_1 FROM properties ORDER BY title ASC")->fetchAll(\PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT id, title, city, state, address_line_1 FROM properties WHERE landlord_id = :lid ORDER BY title ASC");
    $stmt->execute(['lid' => $currentLandlordId]);
    $existingProperties = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    if (empty($existingProperties)) {
        $existingProperties = $pdo->query("SELECT id, title, city, state, address_line_1 FROM properties ORDER BY title ASC")->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Tenant — Oga Landlord</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50/60 text-slate-800 antialiased min-h-screen">

<div class="max-w-2xl mx-auto px-4 py-8 md:py-16" x-data="onboardingWizard()">

    <!-- Human Header -->
    <header class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <a href="/landlord" class="text-xs font-semibold text-slate-500 hover:text-[#1D4ED8] transition-colors inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Landlord Dashboard
            </a>
            <img src="/assets/images/logo.jpg" alt="Oga Landlord Logo" class="h-10 w-auto object-contain">
        </div>
        <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">Add a new tenant</h1>
        <p class="text-slate-500 mt-1 text-xs sm:text-sm">Set up residency details and choose how you want to handle the lease agreement.</p>
        
        <!-- Step Progress Bar -->
        <div class="flex items-center gap-2 mt-6">
            <template x-for="i in 4" :key="i">
                <div class="h-1.5 flex-1 rounded-full transition-all duration-300"
                     :class="step >= i ? 'bg-[#1D4ED8]' : 'bg-slate-200'"></div>
            </template>
        </div>
        <div class="flex justify-between text-xs text-slate-400 font-medium mt-2">
            <span>Tenant Details</span>
            <span>Apartment</span>
            <span>Emergency Contact</span>
            <span>Agreement</span>
        </div>
    </header>

    <!-- Wizard Card -->
    <main class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-6 md:p-8">

        <!-- STEP 1: Personal Info -->
        <section x-show="step === 1" x-transition.opacity>
            <h2 class="text-lg font-semibold text-slate-900 mb-5">Who is moving in?</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Full Name</label>
                    <input type="text" x-model="form.tenant_name" placeholder="e.g. Amara Okafor" 
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-sm transition">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Email Address</label>
                        <input type="email" x-model="form.tenant_email" placeholder="amara@example.com"
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-sm transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Phone Number</label>
                        <input type="tel" x-model="form.tenant_phone" placeholder="+234 803 123 4567"
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-sm transition">
                    </div>
                </div>
            </div>
        </section>

        <!-- STEP 2: Property, Apartment Type & Room Count -->
        <section x-show="step === 2" x-transition.opacity>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Which property & apartment are they taking?</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Landlords can manage multiple estates across various cities.</p>
                </div>
                <div class="inline-flex p-1 bg-slate-100 rounded-xl text-xs font-semibold">
                    <button type="button" @click="isNewLocation = false" 
                            :class="!isNewLocation ? 'bg-white text-[#1D4ED8] shadow-xs' : 'text-slate-600'"
                            class="px-3 py-1.5 rounded-lg transition">
                        Existing Property
                    </button>
                    <button type="button" @click="isNewLocation = true" 
                            :class="isNewLocation ? 'bg-white text-[#1D4ED8] shadow-xs' : 'text-slate-600'"
                            class="px-3 py-1.5 rounded-lg transition">
                        + New Location
                    </button>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Select Existing Property -->
                <div x-show="!isNewLocation" class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Select Managed Estate / Location</label>
                    <select x-model="form.property_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8] text-sm transition font-medium">
                        <template x-for="p in properties" :key="p.id">
                            <option :value="p.id" x-text="`${p.title} (${p.city}, ${p.state})`"></option>
                        </template>
                    </select>
                </div>

                <!-- Onboard New Location Card -->
                <div x-show="isNewLocation" class="p-4 rounded-xl bg-blue-50/40 border border-blue-200 space-y-3" x-transition>
                    <div class="flex items-center justify-between pb-2 border-b border-blue-100">
                        <span class="text-xs font-bold text-[#1D4ED8] uppercase tracking-wider">Onboard New Property / Location</span>
                        <span class="text-[10px] bg-blue-100 text-[#1D4ED8] font-bold px-2 py-0.5 rounded">Multi-City Expansion</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Property / Estate Title</label>
                        <input type="text" x-model="form.new_property_title" placeholder="e.g. Bello Crest Heights, Lekki" 
                               class="w-full px-3.5 py-2 rounded-lg border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">City</label>
                            <input type="text" x-model="form.new_property_city" placeholder="e.g. Lekki / Maitama" 
                                   class="w-full px-3.5 py-2 rounded-lg border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">State</label>
                            <input type="text" x-model="form.new_property_state" placeholder="e.g. Lagos / FCT" 
                                   class="w-full px-3.5 py-2 rounded-lg border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Street Address</label>
                            <input type="text" x-model="form.new_property_address" placeholder="e.g. 14 Admiralty Way" 
                                   class="w-full px-3.5 py-2 rounded-lg border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]">
                        </div>
                    </div>
                </div>

                <!-- Unit Number & Apartment Type -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Unit / Flat No.</label>
                        <input type="text" x-model="form.unit_number" placeholder="e.g. Unit 4B" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8] text-sm transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Apartment Type</label>
                        <select x-model="form.apartment_type" 
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8] text-sm transition">
                            <option value="2-Bedroom Apartment">2-Bedroom Apartment</option>
                            <option value="1-Bedroom Flat / Mini Flat">1-Bedroom Flat / Mini Flat</option>
                            <option value="3-Bedroom Flat / Apartment">3-Bedroom Flat / Apartment</option>
                            <option value="4-Bedroom Semi-Detached Duplex">4-Bedroom Semi-Detached Duplex</option>
                            <option value="5-Bedroom Fully-Detached Duplex">5-Bedroom Fully-Detached Duplex</option>
                            <option value="Studio / Self-Contain">Studio / Self-Contain</option>
                            <option value="Penthouse Luxury Suite">Penthouse Luxury Suite</option>
                            <option value="Commercial Office / Store">Commercial Office / Store</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Number of Rooms</label>
                        <div class="flex items-center gap-2">
                            <input type="number" min="1" max="20" x-model="form.rooms_count" placeholder="2" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8] text-sm transition font-bold">
                            <span class="text-xs text-slate-500 shrink-0 font-medium">Rooms</span>
                        </div>
                    </div>
                </div>

                <!-- Rent Amount & Due Date -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Agreed Annual Rent (₦)</label>
                        <input type="number" x-model="form.rent_amount" placeholder="2500000" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8] text-sm transition font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Rent Due Date</label>
                        <input type="date" x-model="form.rent_due_date" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8] text-sm transition">
                    </div>
                </div>
            </div>
        </section>

        <!-- STEP 3: Emergency Contact -->
        <section x-show="step === 3" x-transition.opacity>
            <h2 class="text-lg font-semibold text-slate-900 mb-5">Emergency contact details</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Contact Person Name</label>
                    <input type="text" x-model="form.emergency_name" placeholder="e.g. Dr. Emeka Okafor" 
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-sm transition">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Relationship</label>
                        <input type="text" x-model="form.emergency_relationship" placeholder="Brother / Mother / Colleague" 
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-sm transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Phone Number</label>
                        <input type="tel" x-model="form.emergency_phone" placeholder="+234 802 000 0000" 
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 text-sm transition">
                    </div>
                </div>
            </div>
        </section>

        <!-- STEP 4: Agreement Mode Engine -->
        <section x-show="step === 4" x-transition.opacity>
            <h2 class="text-lg font-semibold text-slate-900 mb-2">How should we handle the lease agreement?</h2>
            <p class="text-sm text-slate-500 mb-6">Select the option that matches your current workflow.</p>

            <div class="space-y-3">
                <!-- Mode 1: Generate and Sign -->
                <label class="flex items-start p-4 border rounded-xl cursor-pointer transition"
                       :class="form.agreement_mode === 'GENERATE_AND_SIGN' ? 'border-slate-900 bg-slate-50/50' : 'border-slate-200 hover:border-slate-300'">
                    <input type="radio" name="agreement_mode" value="GENERATE_AND_SIGN" x-model="form.agreement_mode" class="mt-1 text-slate-900 focus:ring-slate-900">
                    <div class="ml-3.5">
                        <span class="block text-sm font-semibold text-slate-900">Generate & send digital signing link</span>
                        <span class="block text-xs text-slate-500 mt-0.5">We will compile the tenancy agreement and text or email a verified digital signing link to the tenant.</span>
                    </div>
                </label>

                <!-- Mode 2: Upload Existing -->
                <label class="flex items-start p-4 border rounded-xl cursor-pointer transition"
                       :class="form.agreement_mode === 'UPLOAD_EXISTING' ? 'border-slate-900 bg-slate-50/50' : 'border-slate-200 hover:border-slate-300'">
                    <input type="radio" name="agreement_mode" value="UPLOAD_EXISTING" x-model="form.agreement_mode" class="mt-1 text-slate-900 focus:ring-slate-900">
                    <div class="ml-3.5">
                        <span class="block text-sm font-semibold text-slate-900">Upload an already signed paper agreement</span>
                        <span class="block text-xs text-slate-500 mt-0.5">Attach a scanned PDF or photo of your physical contract for secure archival.</span>
                    </div>
                </label>

                <!-- Mode 3: Skip Agreement -->
                <label class="flex items-start p-4 border rounded-xl cursor-pointer transition"
                       :class="form.agreement_mode === 'SKIP_AGREEMENT' ? 'border-slate-900 bg-slate-50/50' : 'border-slate-200 hover:border-slate-300'">
                    <input type="radio" name="agreement_mode" value="SKIP_AGREEMENT" x-model="form.agreement_mode" class="mt-1 text-slate-900 focus:ring-slate-900">
                    <div class="ml-3.5">
                        <span class="block text-sm font-semibold text-slate-900">Skip agreement & start tracking rent</span>
                        <span class="block text-xs text-slate-500 mt-0.5">Directly open the financial ledger and schedule automatic reminders without a contract document.</span>
                    </div>
                </label>
            </div>
        </section>

        <!-- Navigation Buttons -->
        <footer class="flex items-center justify-between mt-8 pt-6 border-t border-slate-100">
            <button type="button" x-show="step > 1" @click="step--"
                    class="text-sm font-medium text-slate-600 hover:text-slate-900 px-4 py-2 rounded-lg transition">
                Previous step
            </button>
            <div class="ml-auto">
                <button type="button" x-show="step < 4" @click="step++"
                        class="bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-medium text-sm px-6 py-2.5 rounded-lg transition shadow-sm">
                    Continue
                </button>
                <button type="button" x-show="step === 4" @click="submitOnboarding()"
                        class="bg-[#1D4ED8] hover:bg-[#1E40AF] text-white font-medium text-sm px-7 py-2.5 rounded-lg transition shadow-sm flex items-center gap-2">
                    <span x-show="!isSubmitting">Complete Tenant Setup</span>
                    <span x-show="isSubmitting">Setting up...</span>
                </button>
            </div>
        </footer>

    </main>

    <!-- Success Feedback Modal -->
    <div x-show="isDone" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-end sm:items-center justify-center p-4 z-50" x-transition.opacity>
        <div class="bg-white w-full max-w-md rounded-2xl p-6 shadow-xl">
            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 class="text-xl font-bold text-slate-900" x-text="completionTitle"></h3>
            <p class="text-sm text-slate-600 mt-2" x-text="completionMessage"></p>
            <div class="mt-6 flex flex-col gap-2">
                <a :href="signingLink" x-show="signingLink" class="w-full text-center bg-slate-900 text-white text-sm font-medium py-2.5 rounded-lg">Copy Signing Link</a>
                <button @click="window.location.reload()" class="w-full text-center bg-slate-100 hover:bg-slate-200 text-slate-800 text-sm font-medium py-2.5 rounded-lg">Add Another Tenant</button>
            </div>
        </div>
    </div>

</div>

<script>
function onboardingWizard() {
    const defaultProperties = <?= json_encode($existingProperties) ?>;
    return {
        step: 1,
        isSubmitting: false,
        isDone: false,
        completionTitle: 'Tenant added successfully.',
        completionMessage: '',
        signingLink: '',
        isNewLocation: false,
        properties: defaultProperties,
        form: {
            tenant_name: '',
            tenant_email: '',
            tenant_phone: '',
            property_id: defaultProperties.length > 0 ? defaultProperties[0].id : '',
            unit_id: null,
            unit_number: 'Unit 1A',
            apartment_type: '2-Bedroom Apartment',
            rooms_count: 2,
            new_property_title: '',
            new_property_address: '',
            new_property_city: '',
            new_property_state: '',
            rent_amount: '',
            rent_start_date: new Date().toISOString().split('T')[0],
            rent_due_date: new Date(Date.now() + 365*24*60*60*1000).toISOString().split('T')[0],
            emergency_name: '',
            emergency_relationship: '',
            emergency_phone: '',
            agreement_mode: 'GENERATE_AND_SIGN'
        },
        async init() {
            try {
                const res = await fetch('/api/v1/landlord/properties');
                if (res.ok) {
                    const data = await res.json();
                    if (Array.isArray(data) && data.length > 0) {
                        this.properties = data;
                        if (!this.form.property_id) {
                            this.form.property_id = data[0].id;
                        }
                    }
                }
            } catch (e) {
                // Keep defaultProperties
            }
        },
        async submitOnboarding() {
            this.isSubmitting = true;
            try {
                const res = await fetch('/api/v1/onboarding/tenants', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.form)
                });
                const result = await res.json();
                if (res.ok && result.success) {
                    this.completionMessage = result.human_message || 'Tenant added successfully.';
                    this.signingLink = result.signing_url || '';
                    this.isDone = true;
                } else {
                    alert(result.error || 'Failed to complete tenant setup. Please verify inputs.');
                }
            } catch (err) {
                alert('We could not save the tenant details. Please check your connection.');
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}
</script>
</body>
</html>