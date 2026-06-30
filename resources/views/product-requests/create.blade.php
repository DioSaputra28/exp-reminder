@extends('layouts.app')

@section('title', 'Ajukan Produk Baru - Expired Reminder')
@section('header', 'Ajukan Produk')
@section('back', route('product-requests.index'))
@section('hide-fab', true)

@push('styles')
    @vite('resources/js/scanner.js')
@endpush

@section('content')
<div class="flex flex-col gap-stack-lg max-w-2xl mx-auto">
    {{-- Messages --}}
    @if(session('error'))
        <div class="bg-error-container text-on-error-container rounded-xl p-4 flex items-center gap-stack-md">
            <x-heroicon-o-x-circle class="w-5 h-5 text-error"/>
            <span class="text-body-sm">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Form --}}
    <form method="POST" action="{{ route('product-requests.store') }}" enctype="multipart/form-data" class="bg-surface-white rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-border-subtle p-6 flex flex-col gap-stack-lg">
        @csrf

        {{-- Product Image --}}
        <div class="flex flex-col gap-stack-sm">
            <label class="text-label-lg text-on-surface-variant uppercase tracking-wider">Foto Produk (Opsional)</label>
            <div class="w-full h-36 rounded-xl border-2 border-dashed border-border-subtle bg-surface-container-low flex flex-col items-center justify-center gap-stack-md cursor-pointer hover:border-primary transition-colors relative overflow-hidden">
                <x-heroicon-o-photo class="w-7 h-7 text-outline" id="image-placeholder-icon"/>
                <span class="text-body-sm text-on-surface-variant" id="image-placeholder-text">JPG, PNG, atau WebP (max 2MB)</span>
                <img class="absolute inset-0 w-full h-full object-cover hidden" id="image-preview" alt="Preview"/>
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
                value="{{ old('name') }}"
                required
                class="w-full px-4 py-3 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-lg text-on-surface transition-all"
                placeholder="Contoh: Teh Pucuk Harum 500ml"
            />
            @error('name')
                <span class="text-label-md text-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Barcode --}}
        <x-barcode-input name="barcode" :value="old('barcode', $barcode)" label="Barcode * (8-13 digit)" />

        {{-- Description --}}
        <div class="flex flex-col gap-stack-sm">
            <label for="description" class="text-label-lg text-on-surface-variant uppercase tracking-wider">Keterangan (Opsional)</label>
            <textarea
                id="description"
                name="description"
                rows="3"
                class="w-full px-4 py-3 rounded-xl border border-border-subtle bg-surface-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-body-lg text-on-surface transition-all resize-none"
                placeholder="Deskripsi singkat produk..."
            >{{ old('description') }}</textarea>
            @error('description')
                <span class="text-label-md text-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Info --}}
        <div class="bg-surface-container-low rounded-xl p-4 flex items-start gap-stack-md">
            <x-heroicon-o-information-circle class="w-5 h-5 text-on-surface-variant mt-0.5 shrink-0"/>
            <p class="text-body-sm text-on-surface-variant">Permintaan akan dikirim ke admin untuk di-review. Anda akan mendapat notifikasi saat permintaan disetujui atau ditolak.</p>
        </div>

        {{-- Submit --}}
        <button type="submit" class="w-full py-3 px-4 bg-primary text-on-primary rounded-xl text-title-md font-semibold hover:bg-primary-container hover:text-on-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(0,92,134,0.2)]">
            Kirim Permintaan
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
                icon.classList.add('hidden');
                text.classList.add('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
