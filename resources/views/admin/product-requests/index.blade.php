@extends('layouts.app')

@section('title', 'Permintaan Produk - Admin')
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
    @if(session('error'))
        <div class="bg-error-container text-on-error-container rounded-xl p-4 flex items-center gap-stack-md">
            <x-heroicon-o-x-circle class="w-5 h-5 text-error"/>
            <span class="text-body-sm">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col gap-stack-sm">
        <h2 class="text-headline-lg font-bold text-on-surface">Permintaan Produk</h2>
        <p class="text-body-sm text-on-surface-variant">Review dan kelola permintaan penambahan produk dari karyawan.</p>
    </div>

    {{-- Requests List --}}
    <section class="flex flex-col gap-stack-md">
        @forelse($requests as $req)
            <div class="bg-surface-white rounded-xl p-4 shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle flex flex-col gap-stack-md">
                {{-- Header --}}
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-title-md font-semibold text-on-surface truncate">{{ $req->name }}</h3>
                        <p class="text-label-md text-on-surface-variant flex items-center gap-1 mt-1">
                            <x-heroicon-o-qr-code class="w-4 h-4 shrink-0"/>
                            {{ $req->barcode }}
                        </p>
                        <p class="text-label-md text-outline mt-1 flex items-center gap-1">
                            <x-heroicon-o-user class="w-4 h-4 shrink-0"/>
                            {{ $req->user->name }}
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

                <span class="text-label-md text-outline">{{ $req->created_at->format('d M Y, H:i') }}</span>

                {{-- Actions (only for pending) --}}
                @if($req->isPending())
                    <div class="flex gap-stack-md pt-stack-sm border-t border-border-subtle">
                        {{-- Approve --}}
                        <form method="POST" action="{{ route('admin.product-requests.approve', $req) }}" class="flex-1">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="w-full py-2 px-4 bg-status-safe text-white rounded-xl text-body-sm font-semibold hover:opacity-90 transition-all active:scale-95">
                                Setujui
                            </button>
                        </form>

                        {{-- Reject (with modal-like inline form) --}}
                        <div class="flex-1" x-data="{ showReject: false }">
                            <button type="button" onclick="this.closest('div').querySelector('form').classList.toggle('hidden')" class="w-full py-2 px-4 bg-error text-on-error rounded-xl text-body-sm font-semibold hover:opacity-90 transition-all active:scale-95">
                                Tolak
                            </button>
                            <form method="POST" action="{{ route('admin.product-requests.reject', $req) }}" class="hidden mt-stack-md flex flex-col gap-stack-sm">
                                @csrf
                                @method('PUT')
                                <textarea
                                    name="rejection_reason"
                                    required
                                    rows="2"
                                    class="w-full px-3 py-2 rounded-lg border border-border-subtle text-body-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary resize-none"
                                    placeholder="Alasan penolakan..."
                                ></textarea>
                                <button type="submit" class="px-4 py-2 bg-error text-on-error rounded-lg text-label-lg font-bold hover:opacity-90 transition-all">
                                    Konfirmasi Tolak
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Rejection reason (for rejected) --}}
                @if($req->rejection_reason)
                    <div class="bg-error-container/50 rounded-lg p-3 flex items-start gap-stack-md">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-error mt-0.5 shrink-0"/>
                        <p class="text-body-sm text-on-error-container">{{ $req->rejection_reason }}</p>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-surface-white rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle p-6 flex flex-col items-center gap-stack-md">
                <x-heroicon-o-document-text class="w-12 h-12 text-outline"/>
                <p class="text-body-sm text-on-surface-variant text-center">Tidak ada permintaan produk.</p>
            </div>
        @endforelse
    </section>
</div>
@endsection
