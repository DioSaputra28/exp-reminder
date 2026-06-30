@extends('layouts.app')

@section('title', 'Dashboard - Expired Reminder')
@section('header', 'Expired Reminder')

@section('content')
<div class="flex flex-col gap-stack-lg">
    {{-- Dashboard Header --}}
    <div class="flex flex-col gap-0.5">
        <h2 class="text-headline-lg font-bold text-on-surface">Dashboard</h2>
        <p class="text-body-sm text-on-surface-variant">Ringkasan status barang hari ini.</p>
    </div>

    {{-- Overview Cards (Bento Grid) --}}
    <section class="grid grid-cols-2 md:grid-cols-3 gap-gutter">
        {{-- Expired (Danger) --}}
        <a href="{{ route('dashboard.filtered', 'expired') }}" class="col-span-2 md:col-span-1 bg-error-container rounded-xl p-3 flex flex-col gap-stack-md shadow-[0_2px_8px_rgba(186,26,26,0.08)] transition-transform hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <span class="text-body-sm font-semibold text-on-error-container">Exp. Hari Ini</span>
                <x-heroicon-m-x-circle class="w-5 h-5 text-status-danger"/>
            </div>
            <div class="text-display-lg font-bold text-on-error-container">{{ $expiredCount }}</div>
        </a>

        {{-- Expiring Soon (Warning) --}}
        <a href="{{ route('dashboard.filtered', 'expiring_soon') }}" class="bg-surface-white rounded-xl p-3 flex flex-col gap-stack-md shadow-[0_2px_8px_rgba(0,0,0,0.03)] border border-border-subtle transition-transform hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <span class="text-body-sm font-semibold text-status-warning">7 Hari</span>
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-status-warning"/>
            </div>
            <div class="text-display-lg font-bold text-on-surface">{{ $expiringSoonCount }}</div>
        </a>

        {{-- Safe --}}
        <a href="{{ route('dashboard.filtered', 'safe') }}" class="bg-surface-white rounded-xl p-3 flex flex-col gap-stack-md shadow-[0_2px_8px_rgba(0,0,0,0.03)] border border-border-subtle transition-transform hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <span class="text-body-sm font-semibold text-primary">Aman</span>
                <x-heroicon-o-information-circle class="w-5 h-5 text-primary"/>
            </div>
            <div class="text-display-lg font-bold text-on-surface">{{ $safeCount }}</div>
        </a>
    </section>

    {{-- Warning List --}}
    <section class="flex flex-col gap-stack-md">
        <div class="flex items-center justify-between">
            <h3 class="text-body-lg font-semibold text-on-surface">Peringatan Kedaluwarsa</h3>
            @if($warnings->isNotEmpty())
                <a href="{{ route('dashboard.filtered', 'expired') }}" class="text-label-lg text-primary hover:bg-surface-container-low px-2.5 py-1 rounded-full transition-colors">Lihat Semua</a>
            @endif
        </div>

        <div class="bg-surface-white rounded-xl shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-border-subtle overflow-hidden">
            @if($warnings->isEmpty())
                <div class="px-4 py-5 flex flex-col items-center gap-stack-md">
                    <x-heroicon-o-check-circle class="w-9 h-9 text-outline"/>
                    <p class="text-body-sm text-on-surface-variant text-center">Semua barang aman!</p>
                </div>
            @else
                <div class="flex flex-col divide-y divide-surface-container-low">
                    @foreach($warnings as $item)
                        <div class="flex items-center gap-2.5 px-3 py-2 hover:bg-surface-bright transition-colors cursor-pointer">
                            {{-- Product Image --}}
                            <div class="w-10 h-10 rounded-lg bg-surface-variant flex-shrink-0 flex items-center justify-center overflow-hidden">
                                @if($item->product->image)
                                    <img class="w-full h-full object-cover" src="{{ Storage::url($item->product->image) }}" alt="{{ $item->product->name }}"/>
                                @else
                        <x-heroicon-o-archive-box class="w-5 h-5 text-on-surface-variant"/>
                                @endif
                            </div>

                            {{-- Product Info --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-body-sm font-semibold text-on-surface truncate leading-tight">{{ $item->product->name }}</p>
                                <p class="text-label-md text-on-surface-variant mt-0.5">{{ $item->product->barcode }}</p>
                            </div>
                            {{-- Status --}}
                            @php $daysLeft = now()->startOfDay()->diffInDays($item->expiry_date, false); @endphp
                            <div class="flex flex-col items-end gap-0.5 shrink-0">
                                @if($daysLeft <= 0)
                                    <span class="text-label-lg text-status-danger font-bold">{{ $daysLeft === 0 ? 'Hari ini' : abs($daysLeft).'h lalu' }}</span>
                                    <span class="bg-error-container text-on-error-container text-label-md px-2 py-0.5 rounded-full">Expired</span>
                                @else
                                    <span class="text-label-lg text-status-warning font-bold">{{ $daysLeft }}h lagi</span>
                                    <span class="bg-[#FFF3E0] text-status-warning text-label-md px-2 py-0.5 rounded-full">Segera</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
