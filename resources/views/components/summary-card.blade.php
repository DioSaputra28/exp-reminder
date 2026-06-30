@props(['title', 'count', 'variant' => 'default', 'icon' => 'info', 'href' => null])

@php
    $styles = match($variant) {
        'danger' => 'bg-error-container shadow-[0_4px_12px_rgba(186,26,26,0.08)]',
        'warning' => 'bg-surface-white border border-border-subtle shadow-[0_4px_12px_rgba(0,0,0,0.02)]',
        'safe', 'primary' => 'bg-surface-white border border-border-subtle shadow-[0_4px_12px_rgba(0,0,0,0.02)]',
        default => 'bg-surface-white border border-border-subtle shadow-[0_4px_12px_rgba(0,0,0,0.02)]',
    };

    $titleColor = match($variant) {
        'danger' => 'text-on-error-container',
        'warning' => 'text-status-warning',
        'safe' => 'text-status-safe',
        'primary' => 'text-primary',
        default => 'text-on-surface-variant',
    };

    $iconColor = match($variant) {
        'danger' => 'text-status-danger',
        'warning' => 'text-status-warning',
        'safe' => 'text-status-safe',
        'primary' => 'text-primary',
        default => 'text-on-surface-variant',
    };

    $countColor = match($variant) {
        'danger' => 'text-on-error-container',
        default => 'text-on-surface',
    };

    $heroicon = match($icon) {
        'error' => 'x-circle',
        'warning' => 'exclamation-triangle',
        'info' => 'information-circle',
        'check_circle' => 'check-circle',
        'inventory_2' => 'archive-box',
        'event_busy' => 'calendar-days',
        'dashboard' => 'squares-2x2',
        default => 'information-circle',
    };
@endphp

@if($href)
<a href="{{ $href }}" class="{{ $styles }} rounded-xl p-4 flex flex-col gap-stack-md transition-transform hover:-translate-y-1 cursor-pointer">
@else
<div class="{{ $styles }} rounded-xl p-4 flex flex-col gap-stack-md transition-transform hover:-translate-y-1">
@endif
    <div class="flex items-center justify-between">
        <span class="text-title-md font-semibold {{ $titleColor }}">{{ $title }}</span>
        <x-dynamic-component :component="'heroicon-o-' . $heroicon" class="w-5 h-5 {{ $iconColor }}"/>
    </div>
    <div class="text-display-lg font-bold {{ $countColor }} mt-auto">
        {{ $count }}
    </div>
@if($href)
</a>
@else
</div>
@endif
