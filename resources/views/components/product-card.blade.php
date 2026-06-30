@props(['product', 'showStatus' => false, 'status' => null])

<div class="bg-surface-white rounded-xl p-3 shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle flex items-center gap-gutter active:scale-[0.98] transition-transform cursor-pointer">
    {{-- Product Image --}}
    <div class="w-12 h-12 rounded-lg bg-surface-container overflow-hidden shrink-0 flex items-center justify-center">
        @if($product->image)
            <img class="w-full h-full object-cover" src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"/>
        @else
            <x-heroicon-o-archive-box class="w-6 h-6 text-on-surface-variant"/>
        @endif
    </div>

    {{-- Product Info --}}
    <div class="flex flex-col flex-grow min-w-0 gap-1">
        <h3 class="text-title-md font-semibold text-on-surface truncate">{{ $product->name }}</h3>
        <div class="flex items-center text-on-surface-variant text-label-md gap-1">
            <x-heroicon-o-qr-code class="w-4 h-4 shrink-0"/>
            <span>{{ $product->barcode }}</span>
        </div>
    </div>

    {{-- Status Badge --}}
    @if($showStatus && $status)
        <div class="shrink-0">
            <x-status-badge :status="$status" />
        </div>
    @endif
</div>
