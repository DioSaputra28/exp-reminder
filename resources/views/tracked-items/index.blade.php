@extends('layouts.app')

@section('title', 'Tracking Expired - Expired Reminder')
@section('header', 'Expired Reminder')

@section('content')
<div class="flex flex-col gap-stack-lg">
    {{-- Page Header --}}
    <div class="flex flex-col gap-0.5">
        <h2 class="text-headline-lg font-bold text-on-surface">Status Expired</h2>
        <p class="text-body-sm text-on-surface-variant">{{ $items->count() }} barang di-tracking.</p>
    </div>

    {{-- Messages --}}
    @if(session('success'))
        <div class="bg-status-safe/10 text-status-safe rounded-xl px-3 py-2.5 flex items-center gap-2.5">
            <x-heroicon-o-check-circle class="w-5 h-5 text-status-safe"/>
            <span class="text-body-sm">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('warning'))
        <div class="bg-status-warning/10 text-status-warning rounded-xl px-3 py-2.5 flex items-center gap-2.5">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-status-warning"/>
            <span class="text-body-sm">{{ session('warning') }}</span>
        </div>
    @endif

    {{-- Summary Cards --}}
    @php
        $expired = $items->filter(fn($i) => $i->isExpired());
        $expiringSoon = $items->filter(fn($i) => $i->isExpiringSoon());
    @endphp
    <section class="grid grid-cols-2 gap-gutter">
        <div class="bg-surface-white rounded-xl p-3 shadow-[0_2px_8px_rgba(0,0,0,0.03)] border border-border-subtle flex flex-col gap-1">
            <span class="text-label-md text-on-surface-variant">Critical</span>
            <div class="flex items-baseline gap-1.5">
                <span class="text-display-lg font-bold text-status-danger">{{ $expired->count() }}</span>
                <span class="text-body-sm text-on-surface-variant">Expired</span>
            </div>
        </div>
        <div class="bg-surface-white rounded-xl p-3 shadow-[0_2px_8px_rgba(0,0,0,0.03)] border border-border-subtle flex flex-col gap-1">
            <span class="text-label-md text-on-surface-variant">Attention</span>
            <div class="flex items-baseline gap-1.5">
                <span class="text-display-lg font-bold text-status-warning">{{ $expiringSoon->count() }}</span>
                <span class="text-body-sm text-on-surface-variant">Mendekati</span>
            </div>
        </div>
    </section>

    {{-- Tracking List --}}
    <section class="flex flex-col gap-2">
        @forelse($items as $item)
            @php
                $cardBg = match($item->expiryStatus()) {
                    'expired' => 'bg-error-container border-error-container',
                    'expiring_soon' => 'bg-[#FFF3E0] border-[#FFCC80]',
                    default => 'bg-surface-white border-border-subtle',
                };
                $textMuted = match($item->expiryStatus()) {
                    'expired' => 'text-on-error-container/70',
                    'expiring_soon' => 'text-status-warning/80',
                    default => 'text-on-surface-variant',
                };
                $textMain = match($item->expiryStatus()) {
                    'expired' => 'text-on-error-container',
                    'expiring_soon' => 'text-on-surface',
                    default => 'text-on-surface',
                };
            @endphp
            <div class="{{ $cardBg }} rounded-xl px-3 py-2.5 shadow-[0_1px_4px_rgba(0,0,0,0.05)] border flex items-center gap-2.5">
                {{-- Product Image --}}
                <div class="w-10 h-10 rounded-lg bg-surface-container overflow-hidden shrink-0 flex items-center justify-center">
                    @if($item->product->image)
                        <img class="w-full h-full object-cover" src="{{ Storage::url($item->product->image) }}" alt="{{ $item->product->name }}"/>
                    @else
                        <x-heroicon-o-archive-box class="w-5 h-5 {{ $textMuted }}"/>
                    @endif
                </div>

                {{-- Product Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-body-sm font-semibold {{ $textMain }} truncate leading-tight">{{ $item->product->name }}</p>
                    <div class="flex flex-col mt-0.5 gap-0">
                        <span class="{{ $textMuted }} text-label-md flex items-center gap-1">
                            <x-heroicon-m-calendar-days class="w-3 h-3 shrink-0"/>
                            {{ $item->expiry_date->format('d M Y') }} · {{ $item->quantity }} pcs
                        </span>
                        <span class="{{ $textMuted }} text-label-md flex items-center gap-1">
                            <x-heroicon-m-map-pin class="w-3 h-3 shrink-0"/>
                            {{ $item->rack_name ?? '-' }} · {{ $item->shelf ?? '-' }} · {{ $item->sequence ?? '-' }}
                        </span>
                        @if($item->remind_at)
                            <span class="{{ $textMuted }} text-label-md flex items-center gap-1">
                                <x-heroicon-m-bell class="w-3 h-3 shrink-0"/>
                                {{ $item->remind_at->format('d M Y') }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Actions only --}}
                <div class="shrink-0 flex items-center gap-0.5">                    <a href="{{ route('tracked-items.edit', $item) }}" class="text-primary p-1.5 rounded-full hover:bg-black/5 transition-colors">
                        <x-heroicon-o-pencil class="w-4 h-4"/>
                    </a>
                    <form method="POST" action="{{ route('tracked-items.destroy', $item) }}" data-confirm="Hapus tracking ini?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-error p-1.5 rounded-full hover:bg-black/5 transition-colors">
                            <x-heroicon-o-trash class="w-4 h-4"/>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-surface-white rounded-xl shadow-[0_2px_8px_rgba(0,0,0,0.03)] border border-border-subtle p-5 flex flex-col items-center gap-stack-md">
                <x-heroicon-o-calendar-days class="w-9 h-9 text-outline"/>
                <p class="text-body-sm text-on-surface-variant text-center">Belum ada barang yang di-tracking.</p>
                <a href="{{ route('scan.index') }}" class="px-4 py-2 bg-primary text-on-primary rounded-xl text-body-sm font-semibold hover:bg-primary-container hover:text-on-primary-container transition-all active:scale-95">
                    Scan Barcode
                </a>
            </div>
        @endforelse
    </section>
</div>
@endsection
