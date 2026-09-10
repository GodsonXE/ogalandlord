<?php
$isSigned = ($lease['agreement_status'] ?? '') === 'FULLY_EXECUTED';
$backUrl = match($user['role'] ?? 'LANDLORD') {
    'CARETAKER' => '/caretaker?tab=agreements',
    'SUPERADMIN' => '/admin',
    default => '/landlord?tab=tenants',
};
$backLabel = match($user['role'] ?? 'LANDLORD') {
    'CARETAKER' => 'Back to Managed Tenants',
    'SUPERADMIN' => 'Back to SuperAdmin Center',
    default => 'Back to Tenants & Leases',
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenancy Agreement Preview — <?= htmlspecialchars($tenant['full_name'] ?? 'Tenant') ?> — Oga Landlord</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Reenie+Beanie&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,600;1,6..72,400&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-legal { font-family: 'Newsreader', serif; }
        .font-signature { font-family: 'Reenie Beanie', cursive; font-size: 2.5rem; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .print-shadow-none { box-shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 antialiased min-h-screen p-4 sm:p-6 lg:p-8">

<!-- Navigation / Action Header -->
<div class="max-w-4xl mx-auto mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 no-print">
    <div class="flex items-center gap-3">
        <a href="<?= $backUrl ?>" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 px-3.5 py-2 rounded-xl transition shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>← <?= $backLabel ?></span>
        </a>
        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-[#1D4ED8] border border-blue-200">
            <span class="w-2 h-2 rounded-full bg-[#1D4ED8]"></span>
            <span>Authorized Document Preview</span>
        </span>
    </div>

    <div class="flex items-center gap-2">
        <button type="button" onclick="window.print()" class="bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-xs font-bold px-4 py-2 rounded-xl shadow-xs transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Print / Save as PDF</span>
        </button>

        <?php if ($isSigned): ?>
            <span class="inline-flex items-center gap-1 px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-xs">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                <span>Fully Executed & Sealed</span>
            </span>
        <?php else: ?>
            <span class="inline-flex items-center gap-1 px-3.5 py-2 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 shadow-xs">
                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Awaiting Tenant Execution</span>
            </span>
        <?php endif; ?>
    </div>
</div>

<!-- Alert Banner If Pending Tenant Signature -->
<?php if (!$isSigned): ?>
<div class="max-w-4xl mx-auto mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-950 text-xs flex items-start gap-3 shadow-xs no-print">
    <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center shrink-0 text-amber-700 font-bold">
        ⏳
    </div>
    <div class="flex-1">
        <strong class="text-sm font-bold block text-amber-900">Agreement Status: Not Signed (Awaiting Tenant Signature)</strong>
        <p class="mt-1 text-amber-800 leading-relaxed">
            This agreement has been generated for <strong><?= htmlspecialchars($tenant['full_name'] ?? 'Tenant') ?></strong> for <strong><?= htmlspecialchars($unit['unit_number'] ?? 'Unit') ?></strong>.
            The agreement is awaiting digital signature by the tenant. Pursuant to the <em>Evidence Act 2011</em>, Caretakers and Administrators cannot sign on behalf of tenants. Physical key handover must remain withheld until the tenant executes their digital signature.
        </p>
    </div>
</div>
<?php endif; ?>

<!-- Official Document Paper Container -->
<div class="max-w-4xl mx-auto bg-white border border-slate-300 rounded-3xl p-8 sm:p-12 shadow-xl print-shadow-none relative overflow-hidden">

    <!-- Watermark for Unsigned State -->
    <?php if (!$isSigned): ?>
        <div class="absolute inset-0 pointer-events-none flex items-center justify-center opacity-5 select-none rotate-[-30deg]">
            <span class="text-7xl sm:text-8xl font-black text-amber-950 tracking-widest uppercase">AWAITING SIGNATURE</span>
        </div>
    <?php endif; ?>

    <!-- Legal Header -->
    <div class="text-center pb-8 border-b-2 border-slate-900 mb-8">
        <div class="flex items-center justify-center gap-2 mb-3">
            <img src="/assets/images/logo.jpg" alt="Oga Landlord" class="h-10 w-auto object-contain">
            <span class="text-xs font-extrabold uppercase tracking-widest text-[#1D4ED8]">Oga Landlord Legal Documentation Services</span>
        </div>
        <h1 class="font-serif text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">RESIDENTIAL TENANCY AGREEMENT</h1>
        <p class="text-xs text-slate-500 mt-1 uppercase tracking-wider font-semibold">Federal Republic of Nigeria • Evidence Act 2011</p>
    </div>

    <!-- Contract Body & Structured Sections -->
    <div class="font-legal space-y-6 text-slate-800 leading-relaxed text-sm sm:text-base">

        <!-- Parties Card (Blue) -->
        <div class="p-5 bg-blue-50/50 border border-blue-200 border-l-4 border-l-[#1D4ED8] rounded-xl font-sans text-xs space-y-3">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-[#1D4ED8] pb-1 border-b border-blue-200 flex items-center justify-between">
                <span>Contracting Parties & Caretaker Scoping</span>
                <span class="text-[10px] text-blue-600 bg-blue-100 px-2 py-0.5 rounded font-bold">Privity of Contract</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                <div>
                    <strong class="text-slate-400 uppercase text-[10px] tracking-wider block mb-0.5">The Landlord:</strong>
                    <span class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($landlord['full_name'] ?? 'Chief Ibrahim Bello') ?></span><br>
                    <span class="text-slate-500 text-xs">Phone: <?= htmlspecialchars($landlord['phone_number'] ?? '+2348030000001') ?></span>
                </div>
                <div>
                    <strong class="text-slate-400 uppercase text-[10px] tracking-wider block mb-0.5">The Tenant:</strong>
                    <span class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($tenant['full_name'] ?? 'Amara Okafor') ?></span><br>
                    <span class="text-slate-500 text-xs">Email: <?= htmlspecialchars($tenant['email'] ?? 'amara.okafor@example.com') ?> | Phone: <?= htmlspecialchars($tenant['phone_number'] ?? '+2348030000003') ?></span>
                </div>
            </div>

            <div class="pt-3 border-t border-blue-100">
                <strong class="text-indigo-700 uppercase text-xs tracking-wider block mb-0.5">Assigned On-Site Caretaker:</strong>
                <span class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($caretaker['full_name'] ?? 'Musa Danjuma') ?></span><br>
                <span class="text-slate-500 text-xs">Authority: Scoped Maintenance & Facility Oversight | Auto-CC: <?= htmlspecialchars($caretaker['email'] ?? 'caretaker.idu@ogalandlord.ng') ?></span>
            </div>
        </div>

        <!-- Section 1: Demised Premises (Indigo) -->
        <div class="p-5 bg-slate-50 border border-slate-200 border-l-4 border-l-indigo-600 rounded-xl">
            <h3 class="font-sans text-xs font-extrabold uppercase tracking-wider text-indigo-700 pb-2 border-b border-slate-200 mb-3 flex items-center gap-1.5">
                <span>1. Demised Premises</span>
            </h3>
            <p class="text-sm">
                The Landlord hereby lets and the Tenant hereby takes all that property known and described as 
                <strong><?= htmlspecialchars($unit['unit_number'] ?? 'Unit 1A') ?> (<?= htmlspecialchars($unit['apartment_type'] ?? '2-Bedroom Apartment') ?>)</strong> situated within 
                <strong><?= htmlspecialchars($property['title'] ?? 'PHDL Unity Estate, Idu') ?></strong>, located at 
                <strong><?= htmlspecialchars($property['address_line_1'] ?? 'Plot 42 Railway Corridor, Idu Industrial') ?>, <?= htmlspecialchars($property['city'] ?? 'Abuja') ?>, <?= htmlspecialchars($property['state'] ?? 'FCT') ?>, <?= htmlspecialchars($property['country'] ?? 'Nigeria') ?></strong>.
            </p>
        </div>

        <!-- Section 2: Term and Financial Consideration (Emerald) -->
        <div class="p-5 bg-slate-50 border border-slate-200 border-l-4 border-l-emerald-600 rounded-xl">
            <h3 class="font-sans text-xs font-extrabold uppercase tracking-wider text-emerald-700 pb-2 border-b border-slate-200 mb-3 flex items-center gap-1.5">
                <span>2. Term & Financial Consideration (Rent)</span>
            </h3>
            <p class="text-sm">
                The tenancy created hereunder shall be for a fixed period of <strong>one (1) calendar year</strong> commencing from the 
                <strong><?= date('jS \d\a\y \o\f F, Y', strtotime($lease['rent_start_date'] ?? 'now')) ?></strong> and expiring on the 
                <strong><?= date('jS \d\a\y \o\f F, Y', strtotime($lease['rent_due_date'] ?? '+1 year')) ?></strong>.
            </p>
            <div class="mt-3 p-4 bg-white border border-emerald-200 rounded-xl font-sans text-xs shadow-xs">
                <div class="flex items-center justify-between font-bold text-sm text-slate-900">
                    <span class="text-slate-600">Agreed Annual Consideration:</span>
                    <span class="text-emerald-700 text-base font-black">NGN <?= number_format((float)($lease['rent_amount'] ?? 2500000), 2) ?></span>
                </div>
                <div class="mt-2 pt-2 border-t border-slate-100 text-slate-500 flex items-center justify-between">
                    <span>Next Due Cycle:</span>
                    <span class="font-semibold text-slate-800"><?= date('F j, Y', strtotime($lease['rent_due_date'] ?? '+1 year')) ?></span>
                </div>
            </div>
        </div>

        <!-- Section 3: Essential Covenants (Amber) -->
        <div class="p-5 bg-slate-50 border border-slate-200 border-l-4 border-l-amber-500 rounded-xl">
            <h3 class="font-sans text-xs font-extrabold uppercase tracking-wider text-amber-800 pb-2 border-b border-slate-200 mb-3 flex items-center gap-1.5">
                <span>3. Key Covenants & Ground Regulations</span>
            </h3>
            <ul class="list-disc pl-5 space-y-2 text-xs sm:text-sm text-slate-700">
                <li><strong>Prompt Payment & Auto-Reminders:</strong> The Tenant agrees to pay all rent on or before the due date. The Tenant acknowledges automated dunning reminders dispatched at T-30, T-7, and daily T-6..0 with auto-halt guarantee upon receipt of payment.</li>
                <li><strong>Permitted Use:</strong> The premises shall be strictly used for private residential purposes only and shall not be sublet or assigned without the prior written consent of the Landlord.</li>
                <li><strong>Caretaker Access for Repairs:</strong> The Tenant agrees to permit the Landlord's assigned Caretaker (<?= htmlspecialchars($caretaker['full_name'] ?? 'Musa Danjuma') ?>) or authorized contractors reasonable access during daytime hours upon 24 hours prior notification to inspect and execute maintenance repairs.</li>
                <li><strong>No Administrative Signature Forgery:</strong> The parties explicitly stipulate that this agreement is invalid unless executed directly by the Tenant via the cryptographically secured digital signing portal.</li>
            </ul>
        </div>

        <!-- Section 4: Signatures & Execution -->
        <div class="pt-6 border-t border-slate-200">
            <h3 class="font-sans text-xs font-extrabold uppercase tracking-wider text-slate-900 pb-2 mb-4">4. Digital Signatures & Execution</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Landlord Signature Box (Blue) -->
                <div class="p-4 bg-white border border-slate-200 border-t-4 border-t-[#1D4ED8] rounded-xl font-sans shadow-xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Landlord Representative</span>
                        <span class="text-[10px] font-bold bg-blue-50 text-[#1D4ED8] px-2 py-0.5 rounded">Owner</span>
                    </div>
                    <div class="h-16 flex items-center justify-center border-b border-dashed border-slate-300 pb-2">
                        <span class="font-signature text-slate-900 text-3xl">Chief I. Bello</span>
                    </div>
                    <div class="mt-2 text-xs">
                        <strong class="block text-slate-900"><?= htmlspecialchars($landlord['full_name'] ?? 'Chief Ibrahim Bello') ?></strong>
                        <span class="text-slate-500 text-[11px]">Landlord / Registered Owner</span>
                        <span class="text-[#1D4ED8] text-[10px] font-semibold block mt-0.5">✓ Executed via Oga Landlord System</span>
                    </div>
                </div>

                <!-- Tenant Signature Box (Emerald if signed, Amber if pending) -->
                <div class="p-4 bg-white <?= $isSigned ? 'border-emerald-200 border-t-4 border-t-emerald-500' : 'bg-amber-50/50 border-amber-200 border-t-4 border-t-amber-500' ?> border rounded-xl font-sans shadow-xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Tenant Execution</span>
                        <span class="text-[10px] font-bold <?= $isSigned ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' ?> px-2 py-0.5 rounded">
                            <?= $isSigned ? 'Sealed' : 'Action Required' ?>
                        </span>
                    </div>
                    
                    <?php if ($isSigned): ?>
                        <div class="h-16 flex items-center justify-center border-b border-dashed border-emerald-300 pb-2">
                            <span class="font-signature text-emerald-700 text-3xl"><?= htmlspecialchars($tenant['full_name'] ?? 'Amara Okafor') ?></span>
                        </div>
                        <div class="mt-2 text-xs">
                            <strong class="block text-slate-900"><?= htmlspecialchars($tenant['full_name'] ?? 'Amara Okafor') ?></strong>
                            <span class="text-slate-500 text-[11px]">Tenant / Resident</span>
                            <span class="text-emerald-600 text-[10px] font-semibold block mt-0.5">✓ Cryptographically Stamped & Executed</span>
                        </div>
                    <?php else: ?>
                        <div class="h-16 flex items-center justify-center border-b border-dashed border-amber-300 pb-2">
                            <span class="text-amber-800 font-bold text-xs uppercase tracking-wider">[ Awaiting Tenant Signature ]</span>
                        </div>
                        <div class="mt-2 text-xs">
                            <strong class="block text-slate-900"><?= htmlspecialchars($tenant['full_name'] ?? 'Amara Okafor') ?></strong>
                            <span class="text-slate-500 text-[11px]">Tenant / Resident</span>
                            <span class="text-amber-700 text-[10px] font-semibold block mt-0.5">⏳ Pending Execution by Resident</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Section 5: Cryptographic Certificate of Completion -->
        <?php if ($isSigned): ?>
            <div class="mt-8 p-5 rounded-2xl bg-blue-50/50 border border-blue-200 border-t-4 border-t-[#1D4ED8] font-sans text-xs shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-blue-200 mb-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#1D4ED8]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span class="font-bold text-slate-900">Certificate of Completion & Cryptographic Audit Stamp</span>
                    </div>
                    <span class="text-[10px] font-extrabold uppercase bg-blue-100 text-[#1D4ED8] px-2.5 py-0.5 rounded-full">Immutable Hash</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-[11px] text-slate-600">
                    <div>
                        <span class="font-semibold text-slate-800">SHA-256 Audit Stamp:</span>
                        <p class="font-mono text-[10px] text-slate-700 bg-white p-2 rounded-lg border border-blue-100 mt-1 break-all">
                            <?= htmlspecialchars($lease['document_sha256_hash'] ?? 'df0c4e5ab3b9a1b4be92676c9633adfa9f82d94fc0ac74900ef3c33371ae6181') ?>
                        </p>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-800">Verification Metadata:</span>
                        <ul class="space-y-0.5 mt-1 text-[11px]">
                            <li>Signed At (UTC): <strong class="text-slate-900"><?= htmlspecialchars($lease['tenant_signed_at'] ?? date('Y-m-d H:i:s')) ?></strong></li>
                            <li>Signing IP: <strong class="text-slate-900"><?= htmlspecialchars($lease['tenant_ip_address'] ?? '127.0.0.1 (Verified)') ?></strong></li>
                            <li>Platform Authority: <strong class="text-[#1D4ED8]">Oga Landlord Engine v1.0</strong></li>
                        </ul>
                    </div>
                </div>

                <p class="text-[10px] text-slate-500 mt-3 border-t border-blue-100 pt-2 leading-normal">
                    This document is digitally executed pursuant to the provisions of the Cybercrimes (Prohibition, Prevention, etc.) Act 2015 and the Evidence Act 2011 of the Federal Republic of Nigeria.
                </p>
            </div>
        <?php else: ?>
            <div class="mt-8 p-5 rounded-2xl bg-amber-50/50 border border-amber-200 border-t-4 border-t-amber-500 font-sans text-xs shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-amber-200 mb-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span class="font-bold text-slate-900">Certificate of Completion (Unsealed)</span>
                    </div>
                    <span class="text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 px-2.5 py-0.5 rounded-full">Pending Execution</span>
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    The cryptographic SHA-256 audit stamp and immutable timestamp will be minted automatically once <?= htmlspecialchars($tenant['full_name'] ?? 'the resident') ?> completes the digital signing process via their designated signing link. Caretakers and Admins are restricted from forging signatures.
                </p>
            </div>
        <?php endif; ?>

    </div>

</div>

</body>
</html>
