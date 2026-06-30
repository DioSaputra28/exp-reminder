@extends('layouts.app')

@section('title', 'Edit Produk - Admin')
@section('header', 'Edit Produk')
@section('back', route('admin.products.index'))
@section('hide-fab', true)

@push('styles')
    @vite('resources/js/scanner.js')
@endpush

@section('content')
<div class="flex flex-col gap-stack-lg max-w-2xl mx-auto">
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="bg-surface-white rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle p-6 flex flex-col gap-stack-lg">
        @csrf
        @method('PUT')

        {{-- Product Image --}}
        <div class="flex flex-col gap-stack-sm">
            <label class="text-label-lg text-on-surface-variant uppercase tracking-wider">Foto Produk</label>
            <div class="w-full h-40 rounded-xl border-2 border-dashed border-border-subtle bg-surface-container-low flex flex-col items-center justify-center gap-stack-md cursor-pointer hover:border-primary transition-colors relative overflow-hidden" id="image-preview-container">
                @if($product->image)
                    <img class="absolute inset-0 w-full h-full object-cover" id="image-preview" src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"/>
                    <x-heroicon-o-photo class="w-8 h-8 text-outline hidden" id="image-placeholder-icon"/>
                    <span class="text-body-sm text-on-surface-variant hidden" id="image-placeholder-text">JPG, PNG, atau WebP (max 2MB)</span>
                @else
                    <img class="absolute inset-0 w-full h-full object-cover hidden" id="image-preview" alt="Preview"/>
                    <x-heroicon-o-photo class="w-8 h-8 text-outline" id="image-placeholder-icon"/>
                    <span class="text-body-sm text-on-surface-variant" id="image-placeholder-text">JPG, PNG, atau WebP (max 2MB)</span>
                @endif
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="absolute inset-0 opacity-0 cursor-pointer" onchange="previewImage(this)"/>
            </div>
            @error('image')
                <span class="text-label-md text-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Product Name --}}
        <div class="flex flex-col gap-stack-sm">
            <label for="name" class="text-label-lg text-on-surface-variant uppercase tracking-wider">Nama Produk *</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $product->name) }}"
                required
                class="w-full px-4 py-3 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-lg text-on-surface transition-all"
            />
            @error('name')
                <span class="text-label-md text-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Barcode --}}
        <x-barcode-input name="barcode" :value="old('barcode', $product->barcode)" label="Barcode *" />

        {{-- Category --}}
        <div class="flex flex-col gap-stack-sm">
            <label for="category_id" class="text-label-lg text-on-surface-variant uppercase tracking-wider">Kategori</label>
            <select
                id="category_id"
                name="category_id"
                class="w-full px-4 py-3 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-lg text-on-surface transition-all"
            >
                <option value="">Tanpa Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id')
                <span class="text-label-md text-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Shelf Life --}}
        <div class="flex flex-col gap-stack-sm">
            <label for="shelf_life_days" class="text-label-lg text-on-surface-variant uppercase tracking-wider">Masa Simpan (hari)</label>
            <input
                type="number"
                id="shelf_life_days"
                name="shelf_life_days"
                value="{{ old('shelf_life_days', $product->shelf_life_days) }}"
                min="1"
                class="w-full px-4 py-3 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-lg text-on-surface transition-all"
                placeholder="Contoh: 180"
            />
            @error('shelf_life_days')
                <span class="text-label-md text-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Submit --}}
        <button type="submit" class="w-full py-3 px-4 bg-primary text-on-primary rounded-xl text-title-md font-semibold hover:bg-primary-container hover:text-on-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(0,92,134,0.2)]">
            Simpan Perubahan
        </button>
    </form>
</div>
@endsection

@push('scripts')
<x-barcode-scanner-scripts />
<script>
    function previewImage(input) {
        const preview = document.getElementById('image-preview');
        const icon = document.getElementById('image-placeholder-icon');
        const text = document.getElementById('image-placeholder-text');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if (icon) icon.classList.add('hidden');
                if (text) text.classList.add('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
