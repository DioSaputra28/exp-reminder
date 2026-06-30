@extends('layouts.app')

@section('title', $title . ' - Expired Reminder')
@section('header', 'Expired Reminder')
@section('back', route('dashboard'))

@section('content')
<div class="flex flex-col gap-stack-lg">
    {{-- Page Header --}}
    <div class="flex flex-col gap-stack-sm">
        <h2 class="text-headline-lg font-bold text-on-surface">{{ $title }}</h2>
        <p class="text-body-sm text-on-surface-variant">{{ $items->count() }} barang ditemukan.</p>
    </div>

    {{-- Summary Cards --}}
    <section class="grid grid-cols-2 gap-gutter">
        @if($status === 'expired')
            <div class="bg-surface-white rounded-xl p-4 shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle flex flex-col gap-stack-sm">
                <span class="text-label-md text-on-surface-variant">Critical Action</span>
                <div class="flex items-center gap-2">
                    <span class="text-display-lg font-bold text-status-danger">{{ $items->count() }}</span>
                    <span class="text-body-sm text-on-surface-variant leading-tight">Items<br/>Expired</span>
                </div>
            </div>
        @elseif($status === 'expiring_soon')
            <div class="bg-surface-white rounded-xl p-4 shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle flex flex-col gap-stack-sm">
                <span class="text-label-md text-on-surface-variant">Attention Needed</span>
                <div class="flex items-center gap-2">
                    <span class="text-display-lg font-bold text-status-warning">{{ $items->count() }}</span>
                    <span class="text-body-sm text-on-surface-variant leading-tight">Approaching<br/>Expiry</span>
                </div>
            </div>
        @else
            <div class="bg-surface-white rounded-xl p-4 shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle flex flex-col gap-stack-sm">
                <span class="text-label-md text-on-surface-variant">All Clear</span>
                <div class="flex items-center gap-2">
                    <span class="text-display-lg font-bold text-status-safe">{{ $items->count() }}</span>
                    <span class="text-body-sm text-on-surface-variant leading-tight">Items<br/>Safe</span>
                </div>
            </div>
        @endif
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
                    default => 'text-on-surface',
                };
            @endphp
            <div class="{{ $cardBg }} rounded-xl p-3 shadow-[0_1px_4px_rgba(0,0,0,0.05)] border flex items-center gap-2.5 active:scale-[0.98] transition-transform cursor-pointer">
                <div class="w-10 h-10 rounded-lg bg-surface-container overflow-hidden shrink-0 flex items-center justify-center">
                    @if($item->product->image)
                        <img class="w-full h-full object-cover" src="{{ Storage::url($item->product->image) }}" alt="{{ $item->product->name }}"/>
                    @else
                        <x-heroicon-o-archive-box class="w-5 h-5 {{ $textMuted }}"/>
                    @endif
                </div>
                <div class="flex flex-col flex-grow min-w-0">
                    <p class="text-body-sm font-semibold {{ $textMain }} truncate leading-tight">{{ $item->product->name }}</p>
                    <div class="flex flex-col gap-0 mt-0.5">
                        <span class="{{ $textMuted }} text-label-md flex items-center gap-1">
                            <x-heroicon-m-calendar-days class="w-3 h-3 shrink-0"/>
                            {{ $item->expiry_date->format('d M Y') }}
                        </span>
                        @if($item->remind_at)
                            <span class="{{ $textMuted }} text-label-md flex items-center gap-1">
                                <x-heroicon-m-bell class="w-3 h-3 shrink-0"/>
                                {{ $item->remind_at->format('d M Y') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-surface-white rounded-xl shadow-[0_2px_8px_rgba(0,0,0,0.03)] border border-border-subtle p-5 flex flex-col items-center gap-stack-md">
                <x-heroicon-o-check-circle class="w-9 h-9 text-outline"/>
                <p class="text-body-sm text-on-surface-variant text-center">Tidak ada barang dalam kategori ini.</p>
            </div>
        @endforelse
    </section>
</div>
@endsection
