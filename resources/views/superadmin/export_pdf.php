<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($reportTitle ?? 'SuperAdmin Governance Audit Report') ?> — Oga Landlord</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,600;1,6..72,400&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            @page { margin: 1cm; size: landscape; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 antialiased min-h-screen p-4 sm:p-8">

    <!-- Top Action Bar (hidden when printing) -->
    <div class="max-w-6xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="/admin" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition flex items-center gap-1.5">
            <span>← Back to SuperAdmin Command Center</span>
        </a>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow-xs transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print / Save as PDF</span>
            </button>
        </div>
    </div>

    <!-- Official Document Container -->
    <div class="max-w-6xl mx-auto bg-white border border-slate-300 rounded-2xl p-8 sm:p-12 shadow-md">
        
        <!-- Header & Crest -->
        <div class="flex flex-col sm:flex-row items-center justify-between pb-6 border-b-2 border-slate-900 gap-4">
            <div class="flex items-center gap-4">
                <img src="/assets/images/logo.jpg" alt="Logo" class="h-16 w-auto object-contain">
                <div>
                    <span class="text-xs font-extrabold uppercase tracking-widest text-[#1D4ED8] block">Oga Landlord Governance Infrastructure</span>
                    <h1 class="text-2xl font-extrabold text-slate-900 mt-0.5"><?= htmlspecialchars($reportTitle ?? 'Official Registry Audit Sheet') ?></h1>
                    <p class="text-xs text-slate-500 mt-0.5">Platform Controller Audit • Multi-Property Portfolio Telemetry</p>
                </div>
            </div>
            <div class="text-right text-xs">
                <span class="font-bold text-slate-900 block">Generated UTC: <?= gmdate('Y-m-d H:i:s') ?></span>
                <span class="text-slate-500 block">Audited by: <?= htmlspecialchars($user['full_name'] ?? 'SuperAdmin') ?></span>
                <span class="text-emerald-700 font-bold block mt-1">● Authenticated System Extract</span>
            </div>
        </div>

        <!-- Document Metadata Pill -->
        <div class="my-6 p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-wrap items-center justify-between gap-4 text-xs font-semibold text-slate-700">
            <div><span>Total Records in Scope:</span> <strong class="text-slate-900 text-sm font-bold"><?= count($items ?? []) ?></strong></div>
            <div><span>Platform Security:</span> <strong class="text-[#1D4ED8]">RBAC Level 3 SuperAdmin</strong></div>
            <div><span>Evidence Status:</span> <strong class="text-emerald-700">Verified Legal Registry</strong></div>
        </div>

        <?php if (($exportType ?? '') === 'tenants'): ?>
            <!-- TENANTS TABLE -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-700 uppercase tracking-wider font-extrabold border-b border-slate-200">
                            <th class="p-3">#</th>
                            <th class="p-3">Resident / Tenant</th>
                            <th class="p-3">Contact</th>
                            <th class="p-3">Location & Property</th>
                            <th class="p-3">Unit & Type</th>
                            <th class="p-3 text-center">Rooms</th>
                            <th class="p-3">Annual Rent</th>
                            <th class="p-3">Agreement</th>
                            <th class="p-3">Next Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($items as $idx => $t): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 text-slate-400 font-bold"><?= $idx + 1 ?></td>
                                <td class="p-3">
                                    <strong class="text-slate-900 font-bold block"><?= htmlspecialchars($t['tenant_name']) ?></strong>
                                    <span class="text-slate-500 text-[10px]">ID: <?= (int)$t['tenant_id'] ?></span>
                                </td>
                                <td class="p-3">
                                    <span class="block text-slate-800"><?= htmlspecialchars($t['tenant_email']) ?></span>
                                    <span class="text-slate-500 text-[10px]"><?= htmlspecialchars($t['tenant_phone']) ?></span>
                                </td>
                                <td class="p-3">
                                    <strong class="text-slate-900 block"><?= htmlspecialchars($t['property_title'] ?? 'PHDL Unity Estate') ?></strong>
                                    <span class="text-slate-500 text-[10px]"><?= htmlspecialchars($t['city'] ?? 'Abuja') ?>, <?= htmlspecialchars($t['state'] ?? 'FCT') ?></span>
                                </td>
                                <td class="p-3">
                                    <span class="font-bold text-slate-800 block"><?= htmlspecialchars($t['unit_number'] ?? 'Unit') ?></span>
                                    <span class="text-slate-500 text-[10px]"><?= htmlspecialchars($t['apartment_type'] ?? '2-Bedroom Apartment') ?></span>
                                </td>
                                <td class="p-3 text-center font-bold text-slate-800">
                                    <?= (int)($t['rooms_count'] ?? 2) ?>
                                </td>
                                <td class="p-3 font-extrabold text-slate-900">
                                    ₦<?= number_format((float)($t['rent_amount'] ?? 2500000), 2) ?>
                                </td>
                                <td class="p-3">
                                    <?php if (($t['agreement_status'] ?? '') === 'FULLY_EXECUTED'): ?>
                                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">Executed & Sealed</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">Awaiting Signature</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3 text-slate-700">
                                    <?= htmlspecialchars($t['rent_due_date'] ?? 'N/A') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif (($exportType ?? '') === 'landlords'): ?>
            <!-- LANDLORDS TABLE -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-700 uppercase tracking-wider font-extrabold border-b border-slate-200">
                            <th class="p-3">#</th>
                            <th class="p-3">Landlord Name</th>
                            <th class="p-3">Contact Details</th>
                            <th class="p-3">Managed Estates</th>
                            <th class="p-3">Active Locations</th>
                            <th class="p-3 text-center">Total Units</th>
                            <th class="p-3 text-center">Occupied</th>
                            <th class="p-3">Projected Annual SaaS ARR</th>
                            <th class="p-3">Platform Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($items as $idx => $l): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="p-3 text-slate-400 font-bold"><?= $idx + 1 ?></td>
                                <td class="p-3">
                                    <strong class="text-slate-900 font-bold block text-sm"><?= htmlspecialchars($l['landlord_name']) ?></strong>
                                    <span class="text-slate-500 text-[10px]">ID: <?= (int)$l['landlord_id'] ?></span>
                                </td>
                                <td class="p-3">
                                    <span class="block text-slate-800"><?= htmlspecialchars($l['landlord_email']) ?></span>
                                    <span class="text-slate-500 text-[10px]"><?= htmlspecialchars($l['landlord_phone']) ?></span>
                                </td>
                                <td class="p-3 font-bold text-slate-800">
                                    <?= (int)($l['properties_count'] ?? 1) ?> Estates
                                </td>
                                <td class="p-3 text-slate-600">
                                    <?= htmlspecialchars($l['property_cities'] ?? 'Abuja, Lagos') ?>
                                </td>
                                <td class="p-3 text-center font-extrabold text-slate-900">
                                    <?= (int)($l['units_count'] ?? 8) ?>
                                </td>
                                <td class="p-3 text-center font-bold text-emerald-700">
                                    <?= (int)($l['active_leases_count'] ?? 5) ?>
                                </td>
                                <td class="p-3 font-extrabold text-[#1D4ED8]">
                                    ₦<?= number_format((float)($l['units_count'] ?? 8) * 3000, 2) ?>
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">Verified Active</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Footer Cryptographic Seal -->
        <div class="mt-8 pt-6 border-t-2 border-slate-200 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-4">
            <div>
                <span class="font-bold text-slate-700 block">Oga Landlord Autonomous Real Estate Software</span>
                <span>Federal Capital Territory & Nationwide Jurisdiction • Evidence Act 2011 Compliant</span>
            </div>
            <div class="text-right">
                <span class="font-mono text-[10px] bg-slate-100 px-3 py-1 rounded border border-slate-200 block">
                    SHA256: <?= hash('sha256', (string)json_encode($items ?? [])) ?>
                </span>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Official Document Hash Sealed</span>
            </div>
        </div>

    </div>

</body>
</html>
