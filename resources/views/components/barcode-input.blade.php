@props(['name' => 'barcode', 'value' => '', 'required' => true, 'label' => 'Barcode *', 'placeholder' => '8992802120012'])

<div class="flex flex-col gap-stack-sm">
    <label for="{{ $name }}" class="text-label-lg text-on-surface-variant uppercase tracking-wider">{{ $label }}</label>
    <div class="flex gap-stack-sm">
        <input
            type="text"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ $value }}"
            {{ $required ? 'required' : '' }}
            inputmode="numeric"
            class="flex-1 px-4 py-3 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-lg text-on-surface transition-all"
            placeholder="{{ $placeholder }}"
        />
        <button
            type="button"
            onclick="openBarcodeScanner('{{ $name }}')"
            class="px-4 py-3 bg-primary-fixed text-on-primary-fixed rounded-xl hover:bg-primary hover:text-on-primary transition-all active:scale-95 flex items-center gap-1"
            title="Scan dengan kamera"
        >
            <x-heroicon-o-qr-code class="w-5 h-5"/>
        </button>
    </div>
    @error($name)
        <span class="text-label-md text-error">{{ $message }}</span>
    @enderror

    {{-- Inline Scanner Modal --}}
    <div id="scanner-modal-{{ $name }}" class="hidden fixed inset-0 z-[100] bg-on-surface/80 flex items-center justify-center p-margin-mobile">
        <div class="bg-surface-white rounded-2xl w-full max-w-sm overflow-hidden shadow-xl flex flex-col">
            <div class="flex items-center justify-between p-4 border-b border-border-subtle">
                <h3 class="text-title-md font-semibold text-on-surface">Scan Barcode</h3>
                <button type="button" onclick="closeBarcodeScanner('{{ $name }}')" class="text-on-surface-variant hover:bg-surface-container-low p-2 rounded-full transition-colors">
                    <x-heroicon-o-x-mark class="w-5 h-5"/>
                </button>
            </div>
            <div id="scanner-region-{{ $name }}" class="w-full aspect-[4/3] bg-on-surface"></div>
            <div class="p-4">
                <p id="scanner-status-{{ $name }}" class="text-body-sm text-on-surface-variant text-center">Arahkan kamera ke barcode...</p>
            </div>
        </div>
    </div>
</div>
