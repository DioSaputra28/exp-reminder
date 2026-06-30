@extends('layouts.app')

@section('title', 'Scan Barcode - Expired Reminder')
@section('header', 'Scan Barcode')
@section('back', route('dashboard'))
@section('hide-fab', true)

@push('styles')
    @vite('resources/js/scanner.js')
@endpush

@section('content')
<div class="flex flex-col gap-stack-lg max-w-lg mx-auto">
    {{-- Scanner Container --}}
    <div class="bg-surface-white rounded-xl shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-border-subtle overflow-hidden">
        {{-- Camera Scanner --}}
        <div class="w-full aspect-square bg-on-surface relative flex items-center justify-center max-h-[280px]">
            <div id="scanner-region" class="w-full h-full"></div>
            <div id="scanner-loading" class="absolute inset-0 flex flex-col items-center justify-center gap-stack-md text-surface-white">
                <x-heroicon-o-qr-code class="w-10 h-10 animate-pulse"/>
                <p class="text-body-sm">Mengarahkan kamera...</p>
            </div>
        </div>

        {{-- Scanner Status --}}
        <div class="p-3 flex flex-col gap-stack-md">
            <div id="scanner-status" class="text-body-sm text-on-surface-variant text-center">
                Arahkan kamera ke barcode produk.
            </div>

            {{-- Single Result --}}
            <div id="result-found" class="hidden bg-status-safe/10 rounded-xl p-3 flex items-center gap-2.5">
                <x-heroicon-o-check-circle class="w-5 h-5 text-status-safe shrink-0"/>
                <div class="flex-1 min-w-0">
                    <p class="text-body-sm font-semibold text-on-surface truncate" id="result-product-name"></p>
                    <p class="text-label-md text-on-surface-variant" id="result-product-barcode"></p>
                </div>
            </div>

            {{-- Not Found --}}
            <div id="result-not-found" class="hidden bg-status-warning/10 rounded-xl p-3 flex flex-col gap-stack-md">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-information-circle class="w-5 h-5 text-status-warning shrink-0"/>
                    <p class="text-body-sm text-on-surface">Produk tidak ditemukan.</p>
                </div>
                <a id="result-request-link" href="#" class="text-label-lg text-primary hover:underline">
                    → Ajukan permintaan produk baru
                </a>
            </div>

            {{-- Multiple Results --}}
            <div id="result-multiple" class="hidden flex flex-col gap-stack-md">
                <p class="text-body-sm text-on-surface-variant">Beberapa produk ditemukan, pilih salah satu:</p>
                <div id="result-multiple-list" class="flex flex-col gap-1.5"></div>
            </div>
        </div>
    </div>

    {{-- Manual Input --}}
    <div class="bg-surface-white rounded-xl shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-border-subtle p-3 flex flex-col gap-stack-md">
        <div>
            <h3 class="text-body-lg font-semibold text-on-surface">Cari Produk</h3>
            <p class="text-body-sm text-on-surface-variant">Cari berdasarkan nama atau barcode.</p>
        </div>
        <form id="manual-form" class="flex gap-2">
            <input
                type="text"
                id="manual-query"
                placeholder="Nama produk atau barcode..."
                class="flex-1 px-3 py-2.5 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-sm text-on-surface transition-all"
            />
            <button type="submit" class="px-3 py-2.5 bg-primary text-on-primary rounded-xl text-body-sm font-semibold hover:bg-primary-container hover:text-on-primary-container transition-all active:scale-95 whitespace-nowrap">
                Cari
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const scannerStatus = document.getElementById('scanner-status');
        const scannerLoading = document.getElementById('scanner-loading');
        const resultFound = document.getElementById('result-found');
        const resultNotFound = document.getElementById('result-not-found');
        const resultMultiple = document.getElementById('result-multiple');
        const resultMultipleList = document.getElementById('result-multiple-list');
        const resultProductName = document.getElementById('result-product-name');
        const resultProductBarcode = document.getElementById('result-product-barcode');
        const resultRequestLink = document.getElementById('result-request-link');
        const manualForm = document.getElementById('manual-form');
        const manualQuery = document.getElementById('manual-query');

        let isProcessing = false;

        function hideAllResults() {
            resultFound.classList.add('hidden');
            resultNotFound.classList.add('hidden');
            resultMultiple.classList.add('hidden');
        }

        async function lookupProduct(query) {
            if (isProcessing) return;
            isProcessing = true;
            hideAllResults();
            scannerStatus.textContent = 'Mencari produk...';

            try {
                const response = await fetch('{{ route("scan.lookup") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ barcode: query }),
                });

                const data = await response.json();

                if (data.found === true) {
                    // Single product found
                    resultFound.classList.remove('hidden');
                    resultProductName.textContent = data.product.name;
                    resultProductBarcode.textContent = data.product.barcode;
                    scannerStatus.textContent = 'Produk ditemukan! Mengalihkan...';
                    setTimeout(() => { window.location.href = data.redirect; }, 1000);

                } else if (data.found === 'multiple') {
                    // Multiple products found — show selection
                    resultMultipleList.innerHTML = '';
                    data.products.forEach(product => {
                        const div = document.createElement('a');
                        div.href = product.redirect;
                        div.className = 'flex items-center gap-2.5 px-3 py-2 bg-surface-container-low rounded-xl hover:bg-surface-container transition-colors';
                        div.innerHTML = `
                            <div class="w-9 h-9 rounded-lg bg-surface-container overflow-hidden shrink-0 flex items-center justify-center">
                                ${product.image
                                    ? `<img class="w-full h-full object-cover" src="${product.image}" alt="${product.name}"/>`
                                    : `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-on-surface-variant"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>`
                                }
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-body-sm font-semibold text-on-surface truncate">${product.name}</p>
                                <p class="text-label-md text-on-surface-variant">${product.barcode}</p>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-on-surface-variant shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                        `;
                        resultMultipleList.appendChild(div);
                    });
                    resultMultiple.classList.remove('hidden');
                    scannerStatus.textContent = data.products.length + ' produk ditemukan.';
                    isProcessing = false;

                } else {
                    // Not found
                    resultNotFound.classList.remove('hidden');
                    resultRequestLink.href = data.redirect;
                    scannerStatus.textContent = '"' + query + '" tidak ditemukan.';
                    isProcessing = false;
                }
            } catch (error) {
                scannerStatus.textContent = 'Terjadi kesalahan. Coba lagi.';
                isProcessing = false;
            }
        }

        // Manual form submit
        manualForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = manualQuery.value.trim();
            if (query) lookupProduct(query);
        });

        // Camera scanner init
        if (typeof window.Html5Qrcode !== 'undefined') {
            try {
                const html5QrCode = new window.Html5Qrcode('scanner-region');
                html5QrCode.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 220, height: 120 } },
                    (decodedText) => {
                        if (!isProcessing) {
                            html5QrCode.pause(true);
                            lookupProduct(decodedText).then(() => {
                                if (!isProcessing) {
                                    setTimeout(() => html5QrCode.resume(), 2000);
                                }
                            });
                        }
                    },
                    () => {}
                ).then(() => {
                    scannerLoading.classList.add('hidden');
                }).catch(() => showCameraError());
            } catch (e) {
                showCameraError();
            }
        } else {
            showCameraError();
        }

        function showCameraError() {
            scannerLoading.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-surface-white"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M12 18.75H4.5a2.25 2.25 0 0 1-2.25-2.25V9m12.841 9.091L16.5 19.5m-1.409-1.409c.407-.407.659-.97.659-1.591v-9a2.25 2.25 0 0 0-2.25-2.25h-9c-.621 0-1.184.252-1.591.659m12.182 12.182L2.909 5.909M1.5 4.5l1.409 1.409" /></svg>
                <p class="text-body-sm text-surface-white text-center px-4">Kamera tidak tersedia.<br>Gunakan pencarian di bawah.</p>
            `;
        }
    });
</script>
@endpush
