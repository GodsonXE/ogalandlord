<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Tenancy Agreement — Oga Landlord</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Reenie+Beanie&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-signature { font-family: 'Reenie Beanie', cursive; font-size: 2.2rem; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen">

<div class="max-w-xl mx-auto px-4 py-8 md:py-16" x-data="signingPortal()">

    <!-- Document Header -->
    <header class="text-center mb-8">
        <a href="/" class="inline-block mb-3">
            <img src="/assets/images/logo.jpg" alt="Logo" class="h-14 w-auto mx-auto object-contain">
        </a>
        <div class="block">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-[#1D4ED8] border border-blue-200 mb-3">
                Secure Digital Signing Link
            </span>
        </div>
        <h1 class="text-2xl md:text-3xl font-bold text-slate-900">Review & Sign Agreement</h1>
        <p class="text-sm text-slate-500 mt-1">For <span class="font-medium text-slate-800">Unit 1A, PHDL Unity Estate, Idu</span></p>
    </header>

    <!-- Tenancy Summary Details -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 mb-6 shadow-sm">
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-400 uppercase font-semibold">Tenant</span>
                <p class="text-sm font-semibold text-slate-900 mt-0.5">Amara Okafor</p>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-semibold">Agreed Rent</span>
                <p class="text-sm font-semibold text-slate-900 mt-0.5">₦2,500,000 / Year</p>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-semibold">Duration</span>
                <p class="text-slate-700 mt-0.5">12 Months</p>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-semibold">Status</span>
                <p class="text-amber-700 font-medium mt-0.5">Waiting for your signature</p>
            </div>
        </div>
    </div>

    <!-- Interactive Signing Box -->
    <main class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-slate-900">Your Signature</h2>
            <!-- Tab switches: Draw or Type -->
            <div class="flex bg-slate-100 p-1 rounded-lg text-xs font-medium">
                <button type="button" @click="mode = 'draw'" :class="mode === 'draw' ? 'bg-white shadow text-slate-900' : 'text-slate-500'" class="px-3 py-1 rounded-md transition">Draw</button>
                <button type="button" @click="mode = 'type'" :class="mode === 'type' ? 'bg-white shadow text-slate-900' : 'text-slate-500'" class="px-3 py-1 rounded-md transition">Type</button>
            </div>
        </div>

        <!-- Draw Mode Canvas -->
        <div x-show="mode === 'draw'">
            <div class="border border-slate-300 rounded-lg relative bg-slate-50/50 touch-none">
                <canvas id="signatureCanvas" class="w-full h-44 cursor-crosshair"></canvas>
                <button type="button" @click="clearCanvas()" class="absolute top-2 right-2 text-xs font-medium text-slate-400 hover:text-slate-700 bg-white/80 px-2 py-1 rounded">Clear</button>
            </div>
            <p class="text-xs text-slate-400 mt-2 text-center">Use your finger or mouse to draw your signature inside the box above.</p>
        </div>

        <!-- Type Mode -->
        <div x-show="mode === 'type'">
            <input type="text" x-model="typedSignature" placeholder="Type your legal full name"
                   class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 text-lg focus:outline-none focus:border-slate-900">
            <div class="mt-3 p-4 bg-slate-50 border border-slate-200 rounded-lg text-center">
                <span class="text-xs text-slate-400 uppercase font-semibold block mb-1">Preview</span>
                <span class="font-signature text-slate-900 block" x-text="typedSignature || 'Your Signature Here'"></span>
            </div>
        </div>

        <!-- Legal Consent Confirmation -->
        <div class="mt-6 pt-5 border-t border-slate-100 flex items-start gap-3">
            <input type="checkbox" id="consent" x-model="agreedToTerms" class="mt-1 text-slate-900 rounded focus:ring-slate-900">
            <label for="consent" class="text-xs text-slate-600 leading-relaxed cursor-pointer">
                I agree to the terms of this Tenancy Agreement and acknowledge that my digital signature holds the same legal validity as a handwritten signature under the Electronic Transactions Act.
            </label>
        </div>

        <!-- Submit Button -->
        <button type="button" @click="submitSignature()" :disabled="!agreedToTerms || isSubmitting"
                class="w-full mt-6 bg-[#1D4ED8] disabled:bg-slate-300 hover:bg-[#1E40AF] text-white font-medium text-sm py-3 rounded-lg transition shadow-sm">
            <span x-show="!isSubmitting">Accept & Apply Digital Signature</span>
            <span x-show="isSubmitting">Generating Certificate of Completion...</span>
        </button>
    </main>

    <!-- Certificate of Completion Output Modal -->
    <div x-show="isSigned" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50" x-transition.opacity>
        <div class="bg-white max-w-md w-full rounded-2xl p-6 shadow-xl text-center">
            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-900">Agreement Signed & Verified</h3>
            <p class="text-xs text-slate-500 mt-1">A Certificate of Completion has been generated and stamped with an immutable cryptographic hash.</p>
            
            <div class="my-5 p-3.5 bg-slate-50 border border-slate-200 rounded-lg text-left text-xs font-mono break-all text-slate-600">
                <span class="block font-semibold text-slate-800 mb-1 font-sans text-xs">SHA-256 Stamp:</span>
                <span x-text="docHash"></span>
            </div>

            <button @click="window.location.reload()" class="w-full bg-[#1D4ED8] hover:bg-[#1E40AF] text-white text-sm font-medium py-2.5 rounded-lg transition">Return to Summary</button>
        </div>
    </div>

</div>

<script>
function signingPortal() {
    return {
        mode: 'draw',
        typedSignature: '',
        agreedToTerms: false,
        isSubmitting: false,
        isSigned: false,
        docHash: '',
        canvas: null,
        ctx: null,
        drawing: false,
        init() {
            this.canvas = document.getElementById('signatureCanvas');
            this.ctx = this.canvas.getContext('2d');
            this.resizeCanvas();
            this.setupDrawing();
        },
        resizeCanvas() {
            this.canvas.width = this.canvas.offsetWidth;
            this.canvas.height = this.canvas.offsetHeight;
            this.ctx.lineWidth = 2.5;
            this.ctx.lineCap = 'round';
            this.ctx.strokeStyle = '#0f172a';
        },
        setupDrawing() {
            const start = (e) => {
                this.drawing = true;
                const rect = this.canvas.getBoundingClientRect();
                const x = (e.clientX || (e.touches && e.touches[0].clientX)) - rect.left;
                const y = (e.clientY || (e.touches && e.touches[0].clientY)) - rect.top;
                this.ctx.beginPath();
                this.ctx.moveTo(x, y);
            };
            const draw = (e) => {
                if (!this.drawing) return;
                e.preventDefault();
                const rect = this.canvas.getBoundingClientRect();
                const x = (e.clientX || (e.touches && e.touches[0].clientX)) - rect.left;
                const y = (e.clientY || (e.touches && e.touches[0].clientY)) - rect.top;
                this.ctx.lineTo(x, y);
                this.ctx.stroke();
            };
            const stop = () => { this.drawing = false; };

            this.canvas.addEventListener('mousedown', start);
            this.canvas.addEventListener('mousemove', draw);
            window.addEventListener('mouseup', stop);

            this.canvas.addEventListener('touchstart', start);
            this.canvas.addEventListener('touchmove', draw);
            window.addEventListener('touchend', stop);
        },
        clearCanvas() {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        },
        async submitSignature() {
            this.isSubmitting = true;
            const sigData = this.mode === 'draw' ? this.canvas.toDataURL() : this.typedSignature;
            
            try {
                const params = new URLSearchParams(window.location.search);
                const res = await fetch('/api/v1/signing/execute', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        token: params.get('token'),
                        signature_type: this.mode.toUpperCase(),
                        signature_data: sigData
                    })
                });
                const data = await res.json();
                if (data.document_hash) {
                    this.docHash = data.document_hash;
                    this.isSigned = true;
                } else {
                    alert(data.error || 'Failed to apply signature.');
                }
            } catch (e) {
                alert('Could not submit signature. Please check your connection.');
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}
</script>
</body>
</html>