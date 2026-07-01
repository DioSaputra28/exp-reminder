@extends('layouts.app')

@section('title', 'Data Barang - Admin')
@section('header', 'Data Barang')
@section('hide-fab', true)

@section('content')
<div class="flex flex-col gap-stack-lg">
    {{-- Success Message --}}
    @if(session('success'))
        <div class="bg-status-safe/10 text-status-safe rounded-xl p-4 flex items-center gap-stack-md">
            <x-heroicon-o-check-circle class="w-5 h-5 text-status-safe"/>
            <span class="text-body-sm">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Search Bar --}}
    <form method="GET" action="{{ route('admin.products.index') }}" class="relative">
        <x-heroicon-o-magnifying-glass class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant"/>
        <input
            class="w-full pl-12 pr-4 py-3 rounded-full border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-lg text-on-surface shadow-[0_2px_4px_rgba(0,0,0,0.02)] transition-all"
            placeholder="Cari produk..."
            type="text"
            name="search"
            value="{{ request('search') }}"
        />
    </form>

    {{-- Category Filter --}}
    <div class="flex gap-2 overflow-x-auto pb-1 -mx-margin-mobile px-margin-mobile">
        <a href="{{ route('admin.products.index', ['search' => request('search')]) }}"
           class="px-3 py-1.5 rounded-full text-label-lg whitespace-nowrap transition-colors {{ !request('category') ? 'bg-primary text-on-primary' : 'bg-surface-white border border-border-subtle text-on-surface-variant hover:bg-surface-container' }}">
            Semua
        </a>
        @foreach($categories as $category)
            <a href="{{ route('admin.products.index', ['category' => $category->id, 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-full text-label-lg whitespace-nowrap transition-colors {{ request('category') == $category->id ? 'bg-primary text-on-primary' : 'bg-surface-white border border-border-subtle text-on-surface-variant hover:bg-surface-container' }}">
                {{ $category->name }}
            </a>
        @endforeach
    </div>

    {{-- Products Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-gutter">
        @forelse($products as $product)
            <div class="bg-surface-white rounded-xl p-4 flex flex-col gap-stack-md shadow-[0_4px_12px_rgba(0,0,0,0.04)] hover:shadow-[0_8px_24px_rgba(0,0,0,0.08)] transition-shadow border border-border-subtle group">
                {{-- Product Image --}}
                <div class="w-full h-32 rounded-lg bg-surface-container-low overflow-hidden relative flex items-center justify-center">
                    @if($product->image)
                        <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"/>
                    @else
                        <x-heroicon-o-archive-box class="w-12 h-12 text-outline"/>
                    @endif
                </div>

                {{-- Product Info --}}
                <div>
                    <h3 class="text-title-md font-semibold text-on-surface mb-stack-sm truncate">{{ $product->name }}</h3>
                    <div class="flex items-center text-on-surface-variant text-body-sm gap-2">
                        <x-heroicon-o-qr-code class="w-4 h-4 shrink-0"/>
                        <span>{{ $product->barcode }}</span>
                    </div>
                    @if($product->category)
                        <div class="flex items-center text-on-surface-variant text-label-md gap-1 mt-1">
                            <x-heroicon-o-tag class="w-4 h-4 shrink-0"/>
                            <span>{{ $product->category->name }}</span>
                        </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="mt-auto flex justify-between items-center pt-stack-sm border-t border-border-subtle">
                    @if($product->shelf_life_days)
                        <span class="px-3 py-1 rounded-full bg-status-safe/10 text-status-safe text-label-md">{{ $product->shelf_life_days }} hari</span>
                    @else
                        <span></span>
                    @endif
                    <div class="flex gap-1">
                        <a href="{{ route('admin.products.edit', $product) }}" class="text-primary hover:bg-surface-container-low p-2 rounded-full transition-colors">
                            <x-heroicon-o-pencil class="w-5 h-5"/>
                        </a>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Hapus produk {{ $product->name }}?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-error hover:bg-error-container p-2 rounded-full transition-colors">
                                <x-heroicon-o-trash class="w-5 h-5"/>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-surface-white rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle p-6 flex flex-col items-center gap-stack-md">
                <x-heroicon-o-archive-box class="w-12 h-12 text-outline"/>
                <p class="text-body-sm text-on-surface-variant text-center">Belum ada produk. Tambahkan produk baru.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
        <div class="mt-stack-md">
            {{ $products->links() }}
        </div>
    @endif
</div>

{{-- Add Product FAB --}}
<a href="{{ route('admin.products.create') }}" class="fixed bottom-[88px] md:bottom-8 right-margin-mobile md:right-margin-desktop w-14 h-14 bg-primary text-on-primary rounded-2xl shadow-[0_12px_24px_rgba(0,92,134,0.25)] flex items-center justify-center hover:bg-primary-container hover:text-on-primary-container transition-all hover:-translate-y-1 active:scale-95 z-40">
        <x-heroicon-o-plus class="w-7 h-7"/>
</a>
@endsection
