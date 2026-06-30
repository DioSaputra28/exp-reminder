@extends('layouts.guest')

@section('title', 'Admin Login - Expired Reminder')

@section('content')
<div class="w-full max-w-sm flex flex-col items-center gap-stack-lg">
    {{-- Logo / Brand --}}
    <div class="flex flex-col items-center gap-stack-md">
        <div class="w-16 h-16 bg-primary rounded-2xl flex items-center justify-center shadow-lg">
            <x-heroicon-o-shield-check class="w-8 h-8 text-on-primary"/>
        </div>
        <h1 class="text-headline-lg font-bold text-primary tracking-tight">Admin Panel</h1>
        <p class="text-body-sm text-on-surface-variant text-center">Masuk dengan akun administrator.</p>
    </div>

    {{-- Error Message --}}
    @if(session('error'))
        <div class="w-full bg-error-container text-on-error-container rounded-xl p-4 flex items-center gap-stack-md">
            <x-heroicon-o-x-circle class="w-5 h-5 text-error"/>
            <span class="text-body-sm">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Login Form --}}
    <form method="POST" action="{{ route('admin.login.submit') }}" class="w-full bg-surface-white rounded-xl shadow-[0_4px_24px_rgba(0,0,0,0.04)] border border-border-subtle p-6 flex flex-col gap-stack-lg">
        @csrf

        {{-- Email --}}
        <div class="flex flex-col gap-stack-sm">
            <label for="email" class="text-label-lg text-on-surface-variant uppercase tracking-wider">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                class="w-full px-4 py-3 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-lg text-on-surface transition-all"
                placeholder="admin@example.com"
            />
            @error('email')
                <span class="text-label-md text-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Password --}}
        <div class="flex flex-col gap-stack-sm">
            <label for="password" class="text-label-lg text-on-surface-variant uppercase tracking-wider">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                class="w-full px-4 py-3 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-lg text-on-surface transition-all"
                placeholder="••••••••"
            />
            @error('password')
                <span class="text-label-md text-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Remember Me --}}
        <label class="flex items-center gap-stack-md cursor-pointer">
            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-border-subtle text-primary focus:ring-primary"/>
            <span class="text-body-sm text-on-surface-variant">Ingat saya</span>
        </label>

        {{-- Submit --}}
        <button type="submit" class="w-full py-3 px-4 bg-primary text-on-primary rounded-xl text-title-md font-semibold hover:bg-primary-container hover:text-on-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(0,92,134,0.2)]">
            Login
        </button>
    </form>

    {{-- Back to user login --}}
    <a href="{{ route('login') }}" class="text-label-lg text-outline hover:text-primary transition-colors">
        ← Kembali ke login karyawan
    </a>
</div>
@endsection
