@extends('layouts.app')

@section('title', 'Kategori - Admin')
@section('header', 'Kategori')
@section('back', route('dashboard'))
@section('hide-fab', true)

@section('content')
<div class="flex flex-col gap-stack-lg">
    {{-- Page Header --}}
    <div class="flex flex-col gap-stack-sm">
        <h2 class="text-headline-lg font-bold text-on-surface">Kategori Produk</h2>
        <p class="text-body-sm text-on-surface-variant">Kelola kategori untuk data barang.</p>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="bg-status-safe/10 text-status-safe rounded-xl p-4 flex items-center gap-stack-md">
            <x-heroicon-o-check-circle class="w-5 h-5 text-status-safe"/>
            <span class="text-body-sm">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Add Category Form --}}
    <form method="POST" action="{{ route('admin.categories.store') }}" class="bg-surface-white rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle p-4 flex gap-stack-md items-end">
        @csrf
        <div class="flex-1 flex flex-col gap-stack-sm">
            <label for="name" class="text-label-lg text-on-surface-variant uppercase tracking-wider">Nama Kategori Baru</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                required
                class="w-full px-4 py-3 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-lg text-on-surface transition-all"
                placeholder="Contoh: Minuman"
            />
            @error('name')
                <span class="text-label-md text-error">{{ $message }}</span>
            @enderror
        </div>
        <button type="submit" class="px-4 py-3 bg-primary text-on-primary rounded-xl text-body-sm font-semibold hover:bg-primary-container hover:text-on-primary-container transition-all active:scale-95 whitespace-nowrap">
            <x-heroicon-o-plus class="w-5 h-5 inline-block align-middle"/>
            Tambah
        </button>
    </form>

    {{-- Categories List --}}
    <div class="bg-surface-white rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle overflow-hidden">
        @forelse($categories as $category)
            <div class="flex items-center gap-stack-md p-4 border-b border-surface-container-low last:border-b-0 hover:bg-surface-bright transition-colors" id="category-{{ $category->id }}">
                {{-- Icon --}}
                <div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center shrink-0">
                    <x-heroicon-o-tag class="w-5 h-5 text-on-primary-fixed"/>
                </div>

                {{-- Edit Form (inline) --}}
                <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="flex-1 flex items-center gap-stack-md min-w-0">
                    @csrf
                    @method('PUT')
                    <input
                        type="text"
                        name="name"
                        value="{{ $category->name }}"
                        class="flex-1 px-3 py-2 rounded-lg border border-transparent hover:border-border-subtle focus:border-primary focus:ring-1 focus:ring-primary text-body-sm text-on-surface bg-transparent transition-all min-w-0"
                    />
                    <span class="text-label-md text-on-surface-variant whitespace-nowrap">{{ $category->products_count }} produk</span>
                    <button type="submit" class="text-primary hover:bg-surface-container-low p-2 rounded-full transition-colors" title="Simpan">
                        <x-heroicon-o-check class="w-5 h-5"/>
                    </button>
                </form>

                {{-- Delete --}}
                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Hapus kategori {{ $category->name }}?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-error hover:bg-error-container p-2 rounded-full transition-colors" title="Hapus">
                        <x-heroicon-o-trash class="w-5 h-5"/>
                    </button>
                </form>
            </div>
        @empty
            <div class="p-6 flex flex-col items-center gap-stack-md">
                <x-heroicon-o-tag class="w-12 h-12 text-outline"/>
                <p class="text-body-sm text-on-surface-variant text-center">Belum ada kategori. Tambahkan di atas.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
