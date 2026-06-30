@extends('layouts.app')

@section('title', 'Produk - Expired Reminder')
@section('header', 'Expired Reminder')
@section('hide-fab', true)

@section('content')
<div class="flex flex-col gap-stack-lg">
    {{-- Search Bar --}}
    <form method="GET" action="{{ route('products.index') }}" id="search-form" class="relative">
        <x-heroicon-o-magnifying-glass class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant"/>
        <input
            class="w-full pl-12 pr-4 py-2.5 rounded-full border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-sm text-on-surface shadow-[0_2px_4px_rgba(0,0,0,0.02)] transition-all"
            placeholder="Cari nama atau barcode..."
            type="text"
            name="search"
            value="{{ request('search') }}"
        />
        @if(request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}"/>
        @endif
    </form>

    {{-- Category Filter --}}
    <div class="flex gap-2 overflow-x-auto pb-1 -mx-margin-mobile px-margin-mobile">
        <a href="{{ route('products.index', ['search' => request('search')]) }}"
           class="px-3 py-1.5 rounded-full text-label-lg whitespace-nowrap transition-colors {{ !request('category') ? 'bg-primary text-on-primary' : 'bg-surface-white border border-border-subtle text-on-surface-variant hover:bg-surface-container' }}">
            Semua
        </a>
        @foreach($categories as $category)
            <a href="{{ route('products.index', ['category' => $category->id, 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-full text-label-lg whitespace-nowrap transition-colors {{ request('category') == $category->id ? 'bg-primary text-on-primary' : 'bg-surface-white border border-border-subtle text-on-surface-variant hover:bg-surface-container' }}">
                {{ $category->name }}
            </a>
        @endforeach
    </div>

    {{-- Products Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-gutter" id="products-grid">
        @forelse($products as $product)
            <div class="bg-surface-white rounded-xl p-3 flex flex-col gap-stack-md shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-border-subtle">
                <div class="w-full aspect-square rounded-lg bg-surface-container-low overflow-hidden flex items-center justify-center">
                    @if($product->image)
                        <img class="w-full h-full object-cover" src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"/>
                    @else
                        <x-heroicon-o-archive-box class="w-10 h-10 text-outline"/>
                    @endif
                </div>
                <div>
                    <p class="text-body-sm font-semibold text-on-surface leading-tight line-clamp-2">{{ $product->name }}</p>
                    <p class="text-label-md text-on-surface-variant mt-1">{{ $product->barcode }}</p>
                    @if($product->category)
                        <p class="text-label-md text-outline mt-0.5">{{ $product->category->name }}</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full bg-surface-white rounded-xl shadow-[0_2px_8px_rgba(0,0,0,0.03)] border border-border-subtle p-5 flex flex-col items-center gap-stack-md">
                <x-heroicon-o-archive-box class="w-9 h-9 text-outline"/>
                <p class="text-body-sm text-on-surface-variant text-center">Belum ada produk.</p>
            </div>
        @endforelse
    </div>

    {{-- Sentinel: triggers infinite scroll --}}
    @if($products->hasMorePages())
        <div id="scroll-sentinel" class="flex justify-center py-4">
            <div class="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
        </div>
    @endif
</div>

{{-- FAB: user → request, admin → create product --}}
@if(auth()->user()->isAdmin())
    <a href="{{ route('admin.products.create') }}" class="fixed bottom-[88px] md:bottom-8 right-margin-mobile md:right-margin-desktop w-14 h-14 bg-primary text-on-primary rounded-2xl shadow-[0_12px_24px_rgba(0,92,134,0.25)] flex items-center justify-center hover:bg-primary-container hover:text-on-primary-container transition-all hover:-translate-y-1 active:scale-95 z-40">
        <x-heroicon-o-plus class="w-7 h-7"/>
    </a>
@else
    <a href="{{ route('product-requests.create') }}" class="fixed bottom-[88px] md:bottom-8 right-margin-mobile md:right-margin-desktop w-14 h-14 bg-primary text-on-primary rounded-2xl shadow-[0_12px_24px_rgba(0,92,134,0.25)] flex items-center justify-center hover:bg-primary-container hover:text-on-primary-container transition-all hover:-translate-y-1 active:scale-95 z-40">
        <x-heroicon-o-plus class="w-7 h-7"/>
    </a>
@endif
@endsection

@push('scripts')
<script>
(function() {
    const grid = document.getElementById('products-grid');
    const sentinel = document.getElementById('scroll-sentinel');

    if (!sentinel) return; // No more pages

    let nextPage = {{ $products->hasMorePages() ? $products->currentPage() + 1 : 'null' }};
    let loading = false;

    const baseUrl = '{{ route("products.index") }}';
    const params = new URLSearchParams({
        @if(request('search')) search: '{{ request('search') }}', @endif
        @if(request('category')) category: '{{ request('category') }}', @endif
    });

    const observer = new IntersectionObserver((entries) => {
        if (!entries[0].isIntersecting || loading || !nextPage) return;

        loading = true;
        params.set('page', nextPage);

        fetch(`${baseUrl}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.html) {
                grid.insertAdjacentHTML('beforeend', data.html);
            }

            nextPage = data.next_page;
            loading = false;

            if (!nextPage) {
                sentinel.remove(); // No more pages — remove spinner
            }
        })
        .catch(() => {
            loading = false;
        });
    }, { rootMargin: '200px' }); // Load 200px before hitting bottom

    observer.observe(sentinel);
})();
</script>
@endpush
