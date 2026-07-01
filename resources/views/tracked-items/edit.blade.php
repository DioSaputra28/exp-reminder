@extends('layouts.app')

@section('title', 'Edit Tracking - Expired Reminder')
@section('header', 'Edit Tracking')
@section('back', route('tracked-items.index'))
@section('hide-fab', true)

@section('content')
<div class="flex flex-col gap-stack-lg max-w-2xl mx-auto">
    {{-- Messages --}}
    @if(session('error'))
        <div class="bg-error-container text-on-error-container rounded-xl p-4 flex items-center gap-stack-md">
            <x-heroicon-o-x-circle class="w-5 h-5 text-error"/>
            <span class="text-body-sm">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Product Info (readonly) --}}
    <div class="bg-surface-white rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle p-4 flex items-center gap-stack-md">
        <div class="w-14 h-14 rounded-lg bg-surface-container overflow-hidden shrink-0 flex items-center justify-center">
            @if($trackedItem->product->image)
                <img class="w-full h-full object-cover" src="{{ Storage::url($trackedItem->product->image) }}" alt="{{ $trackedItem->product->name }}"/>
            @else
                <x-heroicon-o-archive-box class="w-6 h-6 text-on-surface-variant"/>
            @endif
        </div>
        <div class="flex-1 min-w-0 break-words">
            <h3 class="text-sm md:text-title-md font-semibold text-on-surface">{{ $trackedItem->product->name }}</h3>
            <p class="text-label-md text-on-surface-variant">{{ $trackedItem->product->barcode }}</p>
        </div>
        <x-status-badge :status="$trackedItem->expiryStatus()" />
    </div>

    {{-- Edit Form --}}
    <form method="POST" action="{{ route('tracked-items.update', $trackedItem) }}" class="bg-surface-white rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle p-6 flex flex-col gap-stack-lg">
        @csrf
        @method('PUT')

        {{-- Expiry Date + Quantity (side by side) --}}
        <div class="grid grid-cols-2 gap-stack-md">
            <div class="flex flex-col gap-stack-sm">
                <label for="expiry_date" class="text-label-lg text-on-surface-variant uppercase tracking-wider">Expired *</label>
                <input
                    type="date"
                    id="expiry_date"
                    name="expiry_date"
                    value="{{ old('expiry_date', $trackedItem->expiry_date->format('Y-m-d')) }}"
                    required
                    min="{{ now()->format('Y-m-d') }}"
                    class="w-full px-3 py-2.5 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-sm text-on-surface transition-all"
                />
                @error('expiry_date')
                    <span class="text-label-md text-error">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex flex-col gap-stack-sm">
                <label for="quantity" class="text-label-lg text-on-surface-variant uppercase tracking-wider">Jumlah *</label>
                <input
                    type="number"
                    id="quantity"
                    name="quantity"
                    value="{{ old('quantity', $trackedItem->quantity) }}"
                    required
                    min="1"
                    class="w-full px-3 py-2.5 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-sm text-on-surface transition-all"
                    placeholder="1"
                />
                @error('quantity')
                    <span class="text-label-md text-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Location Fields --}}
        <div class="grid grid-cols-3 gap-stack-md">
            <div class="flex flex-col gap-stack-sm">
                <label for="rack_name" class="text-label-lg text-on-surface-variant uppercase tracking-wider">Nama Rak</label>
                <input
                    type="text"
                    id="rack_name"
                    name="rack_name"
                    value="{{ old('rack_name', $trackedItem->rack_name) }}"
                    maxlength="255"
                    class="w-full px-3 py-2.5 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-sm text-on-surface transition-all"
                    placeholder="A"
                />
                @error('rack_name')
                    <span class="text-label-md text-error">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex flex-col gap-stack-sm">
                <label for="shelf" class="text-label-lg text-on-surface-variant uppercase tracking-wider">Shel</label>
                <input
                    type="text"
                    id="shelf"
                    name="shelf"
                    value="{{ old('shelf', $trackedItem->shelf) }}"
                    maxlength="255"
                    class="w-full px-3 py-2.5 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-sm text-on-surface transition-all"
                    placeholder="1"
                />
                @error('shelf')
                    <span class="text-label-md text-error">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex flex-col gap-stack-sm">
                <label for="sequence" class="text-label-lg text-on-surface-variant uppercase tracking-wider">Urutan</label>
                <input
                    type="number"
                    id="sequence"
                    name="sequence"
                    value="{{ old('sequence', $trackedItem->sequence) }}"
                    min="1"
                    class="w-full px-3 py-2.5 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-sm text-on-surface transition-all"
                    placeholder="1"
                />
                @error('sequence')
                    <span class="text-label-md text-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Reminder Preset --}}
        <div class="flex flex-col gap-stack-md">
            <label class="text-label-lg text-on-surface-variant uppercase tracking-wider">Ingatkan Sebelum</label>
            <div class="grid grid-cols-4 gap-1.5">
                @php
                    $presets = [
                        'H-7' => '7H',
                        'H-14' => '14H',
                        'H-30' => '30H',
                        'B-1' => '1Bln',
                        'B-2' => '2Bln',
                        'B-3' => '3Bln',
                        'custom' => 'Custom',
                        'none' => 'Tidak',
                    ];
                    $currentPreset = old('remind_preset', $trackedItem->remind_at ? 'custom' : 'none');
                @endphp
                @foreach($presets as $value => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="remind_preset" value="{{ $value }}" class="peer hidden" {{ $currentPreset === $value ? 'checked' : '' }}/>
                        <div class="px-1 py-2 rounded-xl border border-border-subtle text-center text-label-md text-on-surface-variant peer-checked:bg-primary peer-checked:text-on-primary peer-checked:border-primary transition-all hover:bg-surface-container leading-tight">
                            {{ $label }}
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Custom Date --}}
        <div id="custom-date-container" class="flex flex-col gap-stack-sm {{ $currentPreset === 'custom' ? '' : 'hidden' }}">
            <label for="remind_at_custom" class="text-label-lg text-on-surface-variant uppercase tracking-wider">Tanggal Reminder Custom</label>
            <input
                type="date"
                id="remind_at_custom"
                name="remind_at_custom"
                value="{{ old('remind_at_custom', $trackedItem->remind_at?->format('Y-m-d')) }}"
                min="{{ now()->format('Y-m-d') }}"
                class="w-full px-4 py-3 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-lg text-on-surface transition-all"
            />
        </div>

        {{-- Reminder Status Info --}}
        @if($trackedItem->remind_at)
            <div class="bg-surface-container-low rounded-xl p-4 flex items-center gap-stack-md">
                <x-heroicon-m-clock class="w-5 h-5 text-on-surface-variant"/>
                <div class="flex flex-col gap-stack-sm">
                    <span class="text-body-sm text-on-surface">
                        Reminder: {{ $trackedItem->remind_at->format('d M Y') }}
                    </span>
                    <span class="text-label-md text-on-surface-variant">
                        Status: {{ ucfirst($trackedItem->reminder_status->value) }}
                        @if($trackedItem->reminder_sent_at)
                            — Terkirim {{ $trackedItem->reminder_sent_at->format('d M Y H:i') }}
                        @endif
                    </span>
                </div>
            </div>
        @endif

        {{-- Submit --}}
        <button type="submit" class="w-full py-3 px-4 bg-primary text-on-primary rounded-xl text-title-md font-semibold hover:bg-primary-container hover:text-on-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(0,92,134,0.2)]">
            Simpan Perubahan
        </button>
    </form>

    {{-- Delete --}}
    <form method="POST" action="{{ route('tracked-items.destroy', $trackedItem) }}" data-confirm="Hapus tracking ini?" class="flex justify-center">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-error text-body-sm font-semibold hover:underline">
            Hapus dari tracking
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const radios = document.querySelectorAll('input[name="remind_preset"]');
        const customContainer = document.getElementById('custom-date-container');

        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customContainer.classList.remove('hidden');
                } else {
                    customContainer.classList.add('hidden');
                }
            });
        });
    });
</script>
@endpush
