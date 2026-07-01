@extends('layouts.guest')

@section('title', 'Login - Expired Reminder')

@section('content')
<div class="w-full max-w-sm flex flex-col items-center gap-stack-lg">
    {{-- Logo / Brand --}}
    <div class="flex flex-col items-center gap-stack-md">
        <div class="w-16 h-16 bg-primary rounded-2xl flex items-center justify-center shadow-lg">
            <x-heroicon-o-archive-box class="w-8 h-8 text-on-primary"/>
        </div>
        <h1 class="text-headline-lg font-bold text-primary tracking-tight">Expired Reminder</h1>
        <p class="text-body-sm text-on-surface-variant text-center">Pantau tanggal kedaluwarsa barang minimarket kamu dengan mudah.</p>
    </div>

    {{-- Error Message --}}
    @if(session('error'))
        <div class="w-full bg-error-container text-on-error-container rounded-xl p-4 flex items-center gap-stack-md">
            <x-heroicon-o-x-circle class="w-5 h-5 text-error"/>
            <span class="text-body-sm">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Login Card --}}
    <div class="w-full bg-surface-white rounded-xl shadow-[0_4px_24px_rgba(0,0,0,0.04)] border border-border-subtle p-6 flex flex-col gap-stack-lg">
        <a href="{{ route('auth.google') }}"
           class="flex items-center justify-center gap-stack-md w-full py-3 px-4 bg-primary text-on-primary rounded-xl text-title-md font-semibold hover:bg-primary-container hover:text-on-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(0,92,134,0.2)]">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Login dengan Google
        </a>

        <div class="flex items-center gap-stack-md">
            <div class="flex-1 h-px bg-border-subtle"></div>
            <span class="text-label-md text-outline uppercase tracking-wider">Karyawan</span>
            <div class="flex-1 h-px bg-border-subtle"></div>
        </div>

        <p class="text-body-sm text-on-surface-variant text-center">
            Gunakan akun Google yang terdaftar di toko untuk masuk ke sistem.
        </p>
    </div>


</div>
@endsection
