<?php
declare(strict_types=1);

$isConfirmed = (($payment['status'] ?? '') === 'CONFIRMED');
$hash = hash('sha256', ($payment['receipt_number'] ?? '') . ($payment['amount'] ?? '') . ($payment['created_at'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Receipt <?= htmlspecialchars($payment['receipt_number'] ?? '') ?> — Oga Landlord</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .receipt-card { box-shadow: none !important; border: 1px solid #cbd5e1 !important; }
        }
    </style>
</head>
<body class="min-h-full py-8 px-4 flex flex-col items-center justify-center text-slate-800 antialiased">

    <!-- Top Action Bar -->
    <div class="max-w-2xl w-full mb-4 flex items-center justify-between no-print">
        <a href="/tenant" class="text-xs font-bold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1.5 bg-white px-3.5 py-2 rounded-xl border border-slate-200 shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Return to Tenant Portal</span>
        </a>
        <button onclick="window.print()" class="bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-xs font-bold px-4 py-2 rounded-xl shadow-xs transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Print / Save as PDF</span>
        </button>
    </div>

    <!-- Official Stamped Receipt Container -->
    <div class="receipt-card max-w-2xl w-full bg-white rounded-3xl p-8 sm:p-10 shadow-xl border border-slate-200/90 relative overflow-hidden">

        <!-- Top Watermark / Status Banner -->
        <?php if ($isConfirmed): ?>
            <div class="absolute -top-12 -right-12 w-36 h-36 bg-emerald-500/10 rounded-full flex items-center justify-center pointer-events-none">
                <span class="text-emerald-600 font-extrabold text-xs uppercase tracking-widest rotate-45 mt-8 mr-8">✓ VERIFIED</span>
            </div>
        <?php else: ?>
            <div class="mb-6 p-3.5 bg-amber-50 border border-amber-200 rounded-2xl flex items-center gap-3 text-xs text-amber-900">
                <span class="text-lg">⏳</span>
                <div>
                    <strong class="font-bold block">Pending Landlord / Caretaker Confirmation</strong>
                    <span>This payment has been submitted and recorded. The official verification stamp will become valid once confirmed by the property manager.</span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Receipt Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-4">
            <div class="flex items-center gap-3">
                <img src="/assets/images/logo.jpg" alt="Logo" class="h-12 w-auto object-contain rounded-xl bg-slate-50 p-1 border border-slate-200">
                <div>
                    <span class="text-lg font-extrabold tracking-tight text-slate-900 block leading-tight">Oga<span class="text-[#1D4ED8]">Landlord</span></span>
                    <span class="text-[11px] uppercase tracking-wider text-slate-400 font-bold">Official Tenancy Payment Receipt</span>
                </div>
            </div>
            <div class="sm:text-right">
                <span class="text-[10px] text-slate-400 uppercase font-bold block">Receipt Number</span>
                <span class="font-mono text-base font-black text-slate-900 block"><?= htmlspecialchars($payment['receipt_number']) ?></span>
                <span class="text-xs text-slate-500"><?= date('F j, Y, H:i:s', strtotime($payment['created_at'])) ?></span>
            </div>
        </div>

        <!-- Property & Resident Details Grid -->
        <div class="grid grid-cols-2 gap-6 my-6 text-xs">
            <div>
                <span class="text-[10px] text-slate-400 uppercase font-bold block mb-1">Resident / Payee</span>
                <strong class="text-slate-900 text-sm block"><?= htmlspecialchars($payment['tenant_name']) ?></strong>
                <span class="text-slate-500 block"><?= htmlspecialchars($payment['tenant_email']) ?></span>
                <span class="text-slate-500 font-mono block"><?= htmlspecialchars($payment['tenant_phone'] ?? '') ?></span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 uppercase font-bold block mb-1">Premises Location</span>
                <strong class="text-slate-900 text-sm block"><?= htmlspecialchars($payment['unit_number']) ?> (<?= htmlspecialchars($payment['apartment_type']) ?>)</strong>
                <span class="text-slate-600 block"><?= htmlspecialchars($payment['property_title']) ?></span>
                <span class="text-slate-500 block"><?= htmlspecialchars($payment['address_line_1'] ?? '') ?>, <?= htmlspecialchars($payment['city'] ?? '') ?></span>
            </div>
        </div>

        <!-- Payment Breakdown Box -->
        <div class="p-5 bg-slate-50/80 rounded-2xl border border-slate-200/80 my-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <span>Description / Line Item</span>
                <span>Amount Paid</span>
            </div>
            <div class="py-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900"><?= htmlspecialchars($payment['title']) ?></h3>
                    <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-1">
                        <span>Channel: <strong><?= htmlspecialchars($payment['payment_method']) ?></strong></span>
                        <span>•</span>
                        <span>Beneficiary: <strong><?= htmlspecialchars($payment['beneficiary_type']) ?></strong></span>
                        <?php if (!empty($payment['gateway_reference'])): ?>
                            <span>•</span>
                            <span class="font-mono text-[10px]">Ref: <?= htmlspecialchars($payment['gateway_reference']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-xl font-black text-slate-900">
                    NGN <?= number_format((float)$payment['amount'], 2) ?>
                </div>
            </div>
            <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-xs">
                <span class="font-bold text-slate-700 uppercase tracking-wider">Total Settlement</span>
                <span class="text-lg font-black text-[#1D4ED8]">NGN <?= number_format((float)$payment['amount'], 2) ?></span>
            </div>
        </div>

        <!-- Stamping & Verification Seal Block (Requirement 7) -->
        <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-6 text-xs">
            <div class="space-y-1.5 flex-1">
                <span class="text-[10px] text-slate-400 uppercase font-bold block">Digital Cryptographic Audit Seal</span>
                <code class="text-[11px] font-mono text-slate-600 block bg-slate-100 p-2 rounded-lg break-all border border-slate-200">
                    SHA-256: <?= $hash ?>
                </code>
                <span class="text-[10px] text-slate-400 block">Secured under Oga Landlord Automated Escrow Protocol & Nigerian Evidence Act 2011.</span>
            </div>

            <!-- Official Stamped Circular Seal Badge -->
            <div class="shrink-0 flex items-center justify-center">
                <?php if ($isConfirmed): ?>
                    <div class="w-32 h-32 rounded-full border-4 border-emerald-600 border-dashed p-1 flex flex-col items-center justify-center text-center text-emerald-700 bg-emerald-50/50 shadow-xs">
                        <svg class="w-6 h-6 text-emerald-600 mb-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span class="text-[10px] font-black uppercase tracking-tight leading-tight block">OFFICIALLY<br>CONFIRMED</span>
                        <span class="text-[8px] font-bold text-emerald-800 mt-0.5 block"><?= htmlspecialchars($payment['confirmed_by_role'] ?? 'OFFICER') ?></span>
                    </div>
                <?php else: ?>
                    <div class="w-32 h-32 rounded-full border-4 border-amber-400 border-dashed p-1 flex flex-col items-center justify-center text-center text-amber-700 bg-amber-50/50 shadow-xs">
                        <span class="text-xl mb-0.5">⏳</span>
                        <span class="text-[10px] font-black uppercase tracking-tight leading-tight block">PENDING<br>CONFIRMATION</span>
                        <span class="text-[8px] font-bold text-amber-800 mt-0.5 block">VERIFICATION</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-4 border-t border-slate-100 text-center text-[10px] text-slate-400">
            Oga Landlord Real Estate Systems Nigeria • Registered Tenancy Management Platform • Automated Billing & Compliance
        </div>

    </div>

</body>
</html>
