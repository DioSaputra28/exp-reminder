@extends('layouts.app')

@section('title', 'Permintaan Produk - Expired Reminder')
@section('header', 'Permintaan Produk')
@section('back', route('dashboard'))
@section('hide-fab', true)

@section('content')
<div class="flex flex-col gap-stack-lg">
    {{-- Messages --}}
    @if(session('success'))
        <div class="bg-status-safe/10 text-status-safe rounded-xl p-4 flex items-center gap-stack-md">
            <x-heroicon-o-check-circle class="w-5 h-5 text-status-safe"/>
            <span class="text-body-sm">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-headline-lg font-bold text-on-surface">Permintaan Saya</h2>
            <p class="text-body-sm text-on-surface-variant">Status permintaan penambahan produk baru.</p>
        </div>
        <a href="{{ route('product-requests.create') }}" class="flex items-center gap-1.5 px-3 py-2 bg-primary text-on-primary rounded-xl text-body-sm font-semibold hover:bg-primary-container hover:text-on-primary-container transition-all active:scale-95 shrink-0">
            <x-heroicon-m-plus class="w-4 h-4"/>
            Ajukan
        </a>
    </div>

    {{-- Requests List --}}
    <section class="flex flex-col gap-stack-md">
        @forelse($requests as $req)
            <div class="bg-surface-white rounded-xl p-4 shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle flex flex-col gap-stack-md">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-title-md font-semibold text-on-surface truncate">{{ $req->name }}</h3>
                        <p class="text-label-md text-on-surface-variant flex items-center gap-1 mt-1">
                        <x-heroicon-o-qr-code class="w-4 h-4 shrink-0"/>
                            {{ $req->barcode }}
                        </p>
                    </div>
                    @php
                        $badgeConfig = match($req->status->value) {
                            'pending' => ['bg' => 'bg-status-warning/10', 'text' => 'text-status-warning', 'label' => 'Menunggu'],
                            'approved' => ['bg' => 'bg-status-safe/10', 'text' => 'text-status-safe', 'label' => 'Disetujui'],
                            'rejected' => ['bg' => 'bg-status-danger/10', 'text' => 'text-status-danger', 'label' => 'Ditolak'],
                        };
                    @endphp
                    <span class="{{ $badgeConfig['bg'] }} {{ $badgeConfig['text'] }} text-label-lg font-bold px-3 py-1 rounded-full">
                        {{ $badgeConfig['label'] }}
                    </span>
                </div>

                @if($req->description)
                    <p class="text-body-sm text-on-surface-variant">{{ $req->description }}</p>
                @endif

                @if($req->rejection_reason)
                    <div class="bg-error-container/50 rounded-lg p-3 flex items-start gap-stack-md">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-error mt-0.5 shrink-0"/>
                        <p class="text-body-sm text-on-error-container">{{ $req->rejection_reason }}</p>
                    </div>
                @endif

                <span class="text-label-md text-outline">{{ $req->created_at->format('d M Y, H:i') }}</span>
            </div>
        @empty
            <div class="bg-surface-white rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle p-6 flex flex-col items-center gap-stack-md">
                <x-heroicon-o-document-text class="w-12 h-12 text-outline"/>
                <p class="text-body-sm text-on-surface-variant text-center">Belum ada permintaan produk.</p>
            </div>
        @endforelse
    </section>
</div>
@endsection
