{{-- Include this once at the bottom of pages that use <x-barcode-input> --}}
<script>
    let activeScanners = {};

    function openBarcodeScanner(fieldName) {
        const modal = document.getElementById('scanner-modal-' + fieldName);
        const regionId = 'scanner-region-' + fieldName;
        const statusEl = document.getElementById('scanner-status-' + fieldName);

        modal.classList.remove('hidden');

        if (typeof window.Html5Qrcode === 'undefined') {
            statusEl.textContent = 'Scanner library tidak tersedia. Input manual.';
            return;
        }

        if (activeScanners[fieldName]) {
            return;
        }

        const html5QrCode = new window.Html5Qrcode(regionId);
        activeScanners[fieldName] = html5QrCode;

        html5QrCode.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 250, height: 120 } },
            (decodedText) => {
                // Fill the input
                document.getElementById(fieldName).value = decodedText;
                statusEl.textContent = 'Barcode terdeteksi: ' + decodedText;

                // Close after short delay
                setTimeout(() => closeBarcodeScanner(fieldName), 600);
            },
            () => {}
        ).catch(() => {
            statusEl.textContent = 'Kamera tidak tersedia.';
        });
    }

    function closeBarcodeScanner(fieldName) {
        const modal = document.getElementById('scanner-modal-' + fieldName);
        modal.classList.add('hidden');

        if (activeScanners[fieldName]) {
            activeScanners[fieldName].stop().catch(() => {});
            delete activeScanners[fieldName];
        }
    }
</script>
