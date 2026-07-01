<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'Expired Reminder')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;500;600;700&display=swap" rel="stylesheet"/>

    @stack('styles')
</head>
<body class="bg-background text-on-background min-h-screen font-sans antialiased flex flex-col pt-14 pb-[68px]">
    {{-- Top App Bar --}}
    <header class="fixed top-0 w-full z-50 bg-surface-white shadow-sm flex items-center justify-between px-margin-mobile h-14 max-w-full">
        <div class="flex items-center gap-stack-md">
            @hasSection('back')
                <a href="@yield('back')" class="text-on-surface-variant hover:bg-surface-container-low transition-colors active:opacity-80 p-2 -ml-2 rounded-full flex items-center justify-center">
                    <x-heroicon-o-arrow-left class="w-6 h-6"/>
                </a>
            @endif
            <h1 class="text-headline-lg-mobile font-semibold text-primary tracking-tight">@yield('header', 'Expired Reminder')</h1>
        </div>
        {{-- Desktop Nav --}}
        <nav class="hidden md:flex items-center gap-stack-lg">
            <a class="text-title-md {{ request()->routeIs('dashboard*') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }} transition-colors" href="{{ route('dashboard') }}">Dashboard</a>
            <a class="text-title-md {{ request()->routeIs('tracked-items*') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }} transition-colors" href="{{ route('tracked-items.index') }}">Expired</a>
            <a class="text-title-md {{ request()->routeIs('scan*') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }} transition-colors" href="{{ route('scan.index') }}">Scan</a>
            @if(auth()->user()->isAdmin())
                <a class="text-title-md {{ request()->routeIs('admin.products*') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }} transition-colors" href="{{ route('admin.products.index') }}">Barang</a>
            @else
                <a class="text-title-md {{ request()->routeIs('products.index') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }} transition-colors" href="{{ route('products.index') }}">Produk</a>
            @endif
            <a class="text-title-md {{ request()->routeIs('profile*') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }} transition-colors" href="{{ route('profile.edit') }}">Account</a>
        </nav>
        <div class="flex items-center gap-stack-sm">
            @yield('header-actions')
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex-1 w-full max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop py-stack-md">
        @yield('content')
    </main>

    {{-- Bottom Navigation (Mobile Only) --}}
    <nav class="md:hidden fixed bottom-0 w-full z-50 bg-surface-white border-t border-border-subtle shadow-lg flex justify-around items-center px-2 pb-safe" style="height: 64px;">
        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('dashboard*') ? 'text-primary' : 'text-on-surface-variant' }} w-14 py-1 active:scale-95 transition-transform duration-200">
            <x-heroicon-o-squares-2x2 class="w-6 h-6"/>
            <span class="text-label-md mt-0.5">Dashboard</span>
        </a>

        {{-- Tab 2: Expired --}}
        <a href="{{ route('tracked-items.index') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('tracked-items*') ? 'text-primary' : 'text-on-surface-variant' }} w-14 py-1 active:scale-95 transition-transform duration-200">
            <x-heroicon-o-calendar-days class="w-6 h-6"/>
            <span class="text-label-md mt-0.5">Expired</span>
        </a>

        {{-- Center Scan Button (prominent) --}}
        <div class="flex flex-col items-center justify-center -mt-6 w-14">
            <a href="{{ route('scan.index') }}"
               class="w-14 h-14 bg-primary text-on-primary rounded-full shadow-[0_4px_20px_rgba(0,92,134,0.4)] flex items-center justify-center active:scale-90 transition-all hover:bg-primary-container hover:shadow-[0_6px_28px_rgba(0,92,134,0.5)] border-4 border-surface-white"
               style="box-shadow: 0 4px 20px rgba(0,92,134,0.4), 0 0 0 4px #ffffff;">
                <x-heroicon-o-qr-code class="w-7 h-7"/>
            </a>
            <span class="text-label-md text-primary font-bold mt-1.5">Scan</span>
        </div>

        {{-- Tab 4: Barang (admin) / Produk (user) --}}
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.products.index') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('admin.products*') ? 'text-primary' : 'text-on-surface-variant' }} w-14 py-1 active:scale-95 transition-transform duration-200">
                <x-heroicon-o-archive-box class="w-6 h-6"/>
                <span class="text-label-md mt-0.5">Barang</span>
            </a>
        @else
            <a href="{{ route('products.index') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('products.index') ? 'text-primary' : 'text-on-surface-variant' }} w-14 py-1 active:scale-95 transition-transform duration-200">
                <x-heroicon-o-archive-box class="w-6 h-6"/>
                <span class="text-label-md mt-0.5">Produk</span>
            </a>
        @endif

        {{-- Account --}}
        <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('profile*') ? 'text-primary' : 'text-on-surface-variant' }} w-14 py-1 active:scale-95 transition-transform duration-200">
            <x-heroicon-o-user-circle class="w-6 h-6"/>
            <span class="text-label-md mt-0.5">Account</span>
        </a>
    </nav>

    {{-- FAB hidden since scan is in nav --}}
    @hasSection('hide-fab')
    @else
    @endif

    {{-- Confirmation Modal --}}
    <div id="confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeConfirmModal()"></div>
        <div class="relative bg-surface-white rounded-2xl shadow-[0_12px_48px_rgba(0,0,0,0.15)] p-6 max-w-sm w-full flex flex-col gap-stack-lg animate-fade-in">
            <div class="flex flex-col gap-stack-sm text-center">
                <div class="w-12 h-12 bg-error-container/30 rounded-full flex items-center justify-center mx-auto">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-error"/>
                </div>
                <h3 class="text-title-md font-semibold text-on-surface">Konfirmasi</h3>
                <p id="confirm-modal-message" class="text-body-sm text-on-surface-variant"></p>
            </div>
            <div class="flex gap-stack-md">
                <button type="button" onclick="closeConfirmModal()" class="flex-1 py-2.5 px-4 rounded-xl border border-border-subtle text-body-sm font-semibold text-on-surface-variant hover:bg-surface-container transition-all">
                    Batal
                </button>
                <button id="confirm-modal-btn" type="button" class="flex-1 py-2.5 px-4 rounded-xl bg-error text-on-error text-body-sm font-semibold hover:opacity-90 transition-all active:scale-95 shadow-[0_4px_12px_rgba(198,40,40,0.2)]">
                    Hapus
                </button>
            </div>
        </div>
    </div>

    <script>
    let pendingForm = null;

    function showConfirmModal(message, confirmText) {
        document.getElementById('confirm-modal-message').textContent = message;
        if (confirmText) {
            document.getElementById('confirm-modal-btn').textContent = confirmText;
        }
        document.getElementById('confirm-modal').classList.remove('hidden');
    }

    function closeConfirmModal() {
        document.getElementById('confirm-modal').classList.add('hidden');
        pendingForm = null;
    }

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-confirm]');
        if (!btn || btn.tagName !== 'BUTTON' && btn.tagName !== 'A') return;
        if (btn.getAttribute('data-confirm') === 'false') return;

        e.preventDefault();
        pendingForm = btn.closest('form') || btn;
        showConfirmModal(btn.getAttribute('data-confirm'), btn.getAttribute('data-confirm-text') || 'Konfirmasi');
    });

    document.addEventListener('submit', function(e) {
        const form = e.target;
        const message = form.getAttribute('data-confirm');
        if (!message) return;

        e.preventDefault();
        pendingForm = form;
        showConfirmModal(message, 'Hapus');
    });

    document.getElementById('confirm-modal-btn').addEventListener('click', function() {
        const form = pendingForm;
        closeConfirmModal();
        if (!form) return;

        if (form.tagName === 'FORM') {
            form.removeAttribute('data-confirm');
            const buttons = form.querySelectorAll('[data-confirm]');
            buttons.forEach(b => b.setAttribute('data-confirm', 'false'));
            form.requestSubmit();
        } else if (form.href) {
            window.location.href = form.href;
        }
    });
    </script>

    @stack('scripts')
</body>
</html>
