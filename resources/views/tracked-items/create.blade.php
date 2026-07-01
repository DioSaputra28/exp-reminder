@extends('layouts.app')

@section('title', 'Tambah Tracking - Expired Reminder')
@section('header', 'Tambah Tracking')
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
    @if(session('warning'))
        <div class="bg-status-warning/10 text-status-warning rounded-xl p-4 flex items-center gap-stack-md">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-status-warning"/>
            <span class="text-body-sm">{{ session('warning') }}</span>
        </div>
    @endif

    {{-- Pre-filled product card --}}
    @if($selectedProduct)
        <div class="bg-surface-white rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle p-4 flex items-center gap-stack-md">
            <div class="w-14 h-14 rounded-lg bg-surface-container overflow-hidden shrink-0 flex items-center justify-center">
                @if($selectedProduct->image)
                    <img class="w-full h-full object-cover" src="{{ Storage::url($selectedProduct->image) }}" alt="{{ $selectedProduct->name }}"/>
                @else
                    <x-heroicon-o-archive-box class="w-6 h-6 text-on-surface-variant"/>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-title-md font-semibold text-on-surface truncate">{{ $selectedProduct->name }}</h3>
                <p class="text-label-md text-on-surface-variant">{{ $selectedProduct->barcode }}</p>
                @if($selectedProduct->category)
                    <p class="text-label-md text-outline">{{ $selectedProduct->category->name }}</p>
                @endif
            </div>
            <x-heroicon-o-check-circle class="w-5 h-5 text-status-safe"/>
        </div>
    @endif

    {{-- Form --}}
    <form method="POST" action="{{ route('tracked-items.store') }}" class="bg-surface-white rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle p-6 flex flex-col gap-stack-lg">
        @csrf

        {{-- Product Select (hidden if pre-filled) --}}
        @if($selectedProduct)
            <input type="hidden" name="product_id" value="{{ $selectedProduct->id }}"/>
        @else
            <div class="flex flex-col gap-stack-sm">
                <label for="product_id" class="text-label-lg text-on-surface-variant uppercase tracking-wider">Pilih Produk *</label>
                <select
                    id="product_id"
                    name="product_id"
                    required
                    class="w-full px-4 py-3 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-lg text-on-surface transition-all"
                >
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} ({{ $product->barcode }})
                        </option>
                    @endforeach
                </select>
                @error('product_id')
                    <span class="text-label-md text-error">{{ $message }}</span>
                @enderror
            </div>
        @endif

        {{-- Expiry Date + Quantity (side by side) --}}
        <div class="grid grid-cols-2 gap-stack-md">
            <div class="flex flex-col gap-stack-sm">
                <label for="expiry_date" class="text-label-lg text-on-surface-variant uppercase tracking-wider">Expired *</label>
                <input
                    type="date"
                    id="expiry_date"
                    name="expiry_date"
                    value="{{ old('expiry_date') }}"
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
                    value="{{ old('quantity', 1) }}"
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
                    value="{{ old('rack_name') }}"
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
                    value="{{ old('shelf') }}"
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
                    value="{{ old('sequence') }}"
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
                @endphp
                @foreach($presets as $value => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="remind_preset" value="{{ $value }}" class="peer hidden" {{ old('remind_preset', 'H-7') === $value ? 'checked' : '' }}/>
                        <div class="px-1 py-2 rounded-xl border border-border-subtle text-center text-label-md text-on-surface-variant peer-checked:bg-primary peer-checked:text-on-primary peer-checked:border-primary transition-all hover:bg-surface-container leading-tight">
                            {{ $label }}
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Custom Date (shown via JS when custom is selected) --}}
        <div id="custom-date-container" class="flex flex-col gap-stack-sm {{ old('remind_preset') === 'custom' ? '' : 'hidden' }}">
            <label for="remind_at_custom" class="text-label-lg text-on-surface-variant uppercase tracking-wider">Tanggal Reminder Custom</label>
            <input
                type="date"
                id="remind_at_custom"
                name="remind_at_custom"
                value="{{ old('remind_at_custom') }}"
                min="{{ now()->format('Y-m-d') }}"
                class="w-full px-4 py-3 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-lg text-on-surface transition-all"
            />
        </div>

        {{-- Telegram Warning --}}
        @unless(auth()->user()->hasTelegramLinked())
            <div class="bg-status-warning/10 rounded-xl p-4 flex items-start gap-stack-md">
                <x-heroicon-o-information-circle class="w-5 h-5 text-status-warning mt-0.5"/>
                <div class="flex flex-col gap-stack-sm">
                    <p class="text-body-sm text-on-surface">Telegram belum terhubung.</p>
                    <p class="text-label-md text-on-surface-variant">Reminder akan disimpan tapi notifikasi tidak akan terkirim. <a href="{{ route('profile.edit') }}" class="text-primary underline">Set Telegram ID →</a></p>
                </div>
            </div>
        @endunless

        {{-- Submit --}}
        <button type="submit" class="w-full py-3 px-4 bg-primary text-on-primary rounded-xl text-title-md font-semibold hover:bg-primary-container hover:text-on-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(0,92,134,0.2)]">
            Simpan Tracking
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
