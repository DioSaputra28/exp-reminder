@extends('layouts.app')

@section('title', 'Kelola User - Admin')
@section('header', 'Kelola User')
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

    {{-- Stats --}}
    <section class="grid grid-cols-3 gap-gutter">
        <div class="bg-surface-white rounded-xl p-4 shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle flex flex-col items-center gap-stack-sm">
            <span class="text-display-lg font-bold text-on-surface">{{ $stats['total'] }}</span>
            <span class="text-label-md text-on-surface-variant text-center">Total User</span>
        </div>
        <div class="bg-surface-white rounded-xl p-4 shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle flex flex-col items-center gap-stack-sm">
            <span class="text-display-lg font-bold text-status-safe">{{ $stats['active'] }}</span>
            <span class="text-label-md text-on-surface-variant text-center">Aktif 30h</span>
        </div>
        <div class="bg-surface-white rounded-xl p-4 shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle flex flex-col items-center gap-stack-sm">
            <span class="text-display-lg font-bold text-primary">{{ $stats['telegram_linked'] }}</span>
            <span class="text-label-md text-on-surface-variant text-center">Telegram</span>
        </div>
    </section>

    {{-- User List --}}
    <section class="flex flex-col gap-stack-md">
        @foreach($users as $user)
            <div class="bg-surface-white rounded-xl p-4 shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle flex flex-col gap-stack-md {{ !$user->is_active ? 'opacity-60' : '' }}">
                {{-- User Info --}}
                <div class="flex items-center gap-stack-md">
                    <div class="w-10 h-10 rounded-full bg-surface-container overflow-hidden shrink-0 flex items-center justify-center">
                        @if($user->avatar)
                            <img class="w-full h-full object-cover" src="{{ $user->avatar }}" alt="{{ $user->name }}"/>
                        @else
                        <x-heroicon-o-user class="w-5 h-5 text-on-surface-variant"/>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-stack-sm">
                            <h3 class="text-body-sm font-semibold text-on-surface truncate">{{ $user->name }}</h3>
                            @if(!$user->is_active)
                                <span class="bg-error-container text-on-error-container text-label-md px-2 py-0.5 rounded-full">Nonaktif</span>
                            @endif
                        </div>
                        <p class="text-label-md text-on-surface-variant truncate">{{ $user->email }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-stack-sm shrink-0">
                        <span class="text-label-lg font-bold {{ $user->role === \App\Enums\UserRole::Admin ? 'text-primary' : 'text-on-surface-variant' }}">
                            {{ ucfirst($user->role->value) }}
                        </span>
                        <span class="text-label-md text-outline">{{ $user->tracked_items_count }} items</span>
                    </div>
                </div>

                {{-- Meta --}}
                <div class="flex items-center gap-stack-lg text-label-md text-outline">
                    <span class="flex items-center gap-1">
                        <x-heroicon-o-paper-airplane class="w-4 h-4 shrink-0"/>
                        {{ $user->telegram_user_id ? 'Linked' : 'Not linked' }}
                    </span>
                    <span class="flex items-center gap-1">
                        <x-heroicon-m-clock class="w-4 h-4 shrink-0"/>
                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                    </span>
                </div>

                {{-- Actions --}}
                @if($user->id !== auth()->id())
                    <div class="flex gap-stack-md pt-stack-sm border-t border-border-subtle">
                        {{-- Toggle Active --}}
                        <form method="POST" action="{{ route('admin.users.toggle', $user) }}" data-confirm="Nonaktifkan akun {{ $user->name }}?">
                            @csrf
                            @method('PUT')
                            @if($user->is_active)
                                <button type="submit" class="px-3 py-1.5 bg-error-container text-on-error-container rounded-lg text-label-lg font-bold hover:opacity-90 transition-all active:scale-95" data-confirm-text="Nonaktifkan">
                                    Nonaktifkan
                                </button>
                            @else
                                <button type="submit" class="px-3 py-1.5 bg-status-safe/10 text-status-safe rounded-lg text-label-lg font-bold hover:opacity-90 transition-all active:scale-95">
                                    Aktifkan
                                </button>
                            @endif
                        </form>

                        {{-- Change Role --}}
                        <form method="POST" action="{{ route('admin.users.role', $user) }}">
                            @csrf
                            @method('PUT')
                            @if($user->role === \App\Enums\UserRole::User)
                                <input type="hidden" name="role" value="admin"/>
                                <button type="submit" data-confirm="Jadikan {{ $user->name }} sebagai Admin?" data-confirm-text="Promosikan" class="px-3 py-1.5 bg-primary-fixed text-on-primary-fixed rounded-lg text-label-lg font-bold hover:opacity-90 transition-all active:scale-95">
                                    → Admin
                                </button>
                            @else
                                <input type="hidden" name="role" value="user"/>
                                <button type="submit" data-confirm="Ubah {{ $user->name }} menjadi User?" data-confirm-text="Turunkan" class="px-3 py-1.5 bg-surface-container text-on-surface-variant rounded-lg text-label-lg font-bold hover:opacity-90 transition-all active:scale-95">
                                    → User
                                </button>
                            @endif
                        </form>
                    </div>
                @endif
            </div>
        @endforeach
    </section>

    {{-- Pagination --}}
    @if($users->hasPages())
        <div class="mt-stack-md">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
