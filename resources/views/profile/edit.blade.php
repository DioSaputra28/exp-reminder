@extends('layouts.app')

@section('title', 'Profil - Expired Reminder')
@section('header', 'Profile Pengguna')
@section('back', route('dashboard'))
@section('hide-fab', true)

@section('content')
<div class="flex flex-col gap-stack-lg max-w-md mx-auto">
    {{-- Messages --}}
    @if(session('success'))
        <div class="bg-status-safe/10 text-status-safe rounded-xl p-4 flex items-center gap-stack-md">
            <x-heroicon-o-check-circle class="w-5 h-5 text-status-safe"/>
            <span class="text-body-sm">{{ session('success') }}</span>
        </div>
    @endif

    {{-- User Profile Section --}}
    <section class="flex flex-col items-center pt-stack-md">
        <div class="w-24 h-24 rounded-full border-4 border-surface-white shadow-sm overflow-hidden bg-surface-container flex items-center justify-center">
            @if($user->avatar)
                <img class="w-full h-full object-cover" src="{{ $user->avatar }}" alt="{{ $user->name }}"/>
            @else
                <x-heroicon-o-user-circle class="w-10 h-10 text-on-surface-variant"/>
            @endif
        </div>
        <h2 class="mt-stack-md text-title-md font-semibold text-on-surface tracking-tight">{{ $user->name }}</h2>
        <p class="text-body-sm text-on-surface-variant">{{ $user->email }}</p>
        <div class="mt-stack-sm flex items-center gap-2 bg-surface-container-low px-4 py-1.5 rounded-full">
            <span class="w-2 h-2 rounded-full {{ $user->hasTelegramLinked() ? 'bg-status-safe' : 'bg-outline' }}"></span>
            <span class="text-label-md text-on-surface-variant uppercase tracking-wider">{{ ucfirst($user->role->value) }}</span>
        </div>
    </section>

    {{-- Telegram Setup Form --}}
    <form method="POST" action="{{ route('profile.update') }}" class="bg-surface-white rounded-xl shadow-[0_4px_24px_rgba(0,0,0,0.02)] border border-border-subtle overflow-hidden flex flex-col">
        @csrf
        @method('PUT')

        <div class="p-4 border-b border-border-subtle flex items-center gap-stack-md">
            <div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center">
                <x-heroicon-o-paper-airplane class="w-5 h-5 text-on-primary-fixed"/>
            </div>
            <div>
                <h3 class="text-body-lg font-medium text-on-surface">Telegram Notification</h3>
                <p class="text-label-md text-on-surface-variant">
                    @if($user->hasTelegramLinked())
                        <span class="text-status-safe">● Terhubung</span>
                    @else
                        <span class="text-outline">○ Belum terhubung</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="p-4 flex flex-col gap-stack-md">
            {{-- Telegram User ID --}}
            <div class="flex flex-col gap-stack-sm">
                <label for="telegram_user_id" class="text-label-lg text-on-surface-variant uppercase tracking-wider">Telegram User ID</label>
                <input
                    type="text"
                    id="telegram_user_id"
                    name="telegram_user_id"
                    value="{{ old('telegram_user_id', $user->telegram_user_id) }}"
                    inputmode="numeric"
                    class="w-full px-4 py-3 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-lg text-on-surface transition-all"
                    placeholder="Contoh: 123456789"
                />
                @error('telegram_user_id')
                    <span class="text-label-md text-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Instructions --}}
            <div class="bg-surface-container-low rounded-xl p-4 flex flex-col gap-stack-md">
                <p class="text-body-sm text-on-surface font-medium">Cara mendapatkan Telegram User ID:</p>
                <ol class="text-body-sm text-on-surface-variant list-decimal list-inside flex flex-col gap-stack-sm">
                    <li>Buka Telegram, cari <span class="font-semibold text-on-surface">@userinfobot</span></li>
                    <li>Kirim pesan apa saja ke bot tersebut</li>
                    <li>Bot akan membalas dengan User ID kamu</li>
                    <li>Salin angka ID tersebut ke kolom di atas</li>
                </ol>
                <div class="mt-stack-sm pt-stack-sm border-t border-border-subtle flex flex-col gap-stack-md">
                    <div class="flex items-start gap-1.5 text-label-md text-status-warning">
                        <x-heroicon-o-exclamation-triangle class="w-4 h-4 shrink-0 mt-0.5"/>
                        <p>Jalankan <strong>/start</strong> di bot kami sebelum mengatur reminder.</p>
                    </div>
                    @if(config('services.telegram.bot_url'))
                        <a href="{{ config('services.telegram.bot_url') }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-[#2AABEE] text-white rounded-xl text-body-sm font-semibold hover:opacity-90 transition-all active:scale-95">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L7.88 13.65l-2.962-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.194 1.006.131.83.95l.47-.041z"/>
                            </svg>
                            Buka Bot Telegram
                        </a>
                    @endif
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" class="w-full py-3 px-4 bg-primary text-on-primary rounded-xl text-title-md font-semibold hover:bg-primary-container hover:text-on-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(0,92,134,0.2)]">
                Simpan
            </button>
        </div>
    </form>

    {{-- Admin Links --}}
    @if($user->isAdmin())
    <a href="{{ route('admin.users.index') }}" class="bg-surface-white rounded-xl shadow-[0_4px_24px_rgba(0,0,0,0.02)] border border-border-subtle overflow-hidden flex items-center justify-between p-4 hover:bg-surface-container-low transition-colors group">
        <div class="flex items-center gap-stack-md">
            <div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center group-hover:scale-105 transition-transform">
                <x-heroicon-o-users class="w-5 h-5 text-on-primary-fixed"/>
            </div>
            <div class="flex flex-col">
                <span class="text-body-lg font-medium text-on-surface">Kelola User</span>
                <span class="text-label-md text-on-surface-variant">Statistik &amp; manage pengguna</span>
            </div>
        </div>
        <x-heroicon-o-chevron-right class="w-5 h-5 text-outline group-hover:translate-x-1 transition-transform"/>
    </a>
    <a href="{{ route('admin.product-requests.index') }}" class="bg-surface-white rounded-xl shadow-[0_4px_24px_rgba(0,0,0,0.02)] border border-border-subtle overflow-hidden flex items-center justify-between p-4 hover:bg-surface-container-low transition-colors group">
        <div class="flex items-center gap-stack-md">
            <div class="w-10 h-10 rounded-full bg-secondary-fixed flex items-center justify-center group-hover:scale-105 transition-transform">
                <x-heroicon-o-inbox class="w-5 h-5 text-on-secondary-fixed-variant"/>
            </div>
            <div class="flex flex-col">
                <span class="text-body-lg font-medium text-on-surface">Permintaan Produk</span>
                <span class="text-label-md text-on-surface-variant">Review &amp; setujui permintaan</span>
            </div>
        </div>
        <x-heroicon-o-chevron-right class="w-5 h-5 text-outline group-hover:translate-x-1 transition-transform"/>
    </a>
    @endif

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}" class="bg-surface-white rounded-xl shadow-[0_4px_24px_rgba(0,0,0,0.02)] border border-border-subtle overflow-hidden">
        @csrf
        <button type="submit" class="flex items-center gap-stack-md w-full p-4 hover:bg-error-container transition-colors active:bg-error-container">
            <div class="w-10 h-10 rounded-full bg-surface-container-highest flex items-center justify-center">
                <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5 text-status-danger"/>
            </div>
            <span class="text-body-lg text-status-danger font-bold">Logout</span>
        </button>
    </form>
</div>
@endsection
