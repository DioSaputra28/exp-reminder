@props(['status'])

@php
    $config = match($status) {
        'expired' => ['bg' => 'bg-status-danger/10', 'text' => 'text-status-danger', 'label' => 'Expired'],
        'expiring_soon' => ['bg' => 'bg-status-warning/10', 'text' => 'text-status-warning', 'label' => 'Segera'],
        'safe' => ['bg' => 'bg-status-safe/10', 'text' => 'text-status-safe', 'label' => 'Aman'],
        default => ['bg' => 'bg-surface-container', 'text' => 'text-on-surface-variant', 'label' => ucfirst($status)],
    };
@endphp

<span class="{{ $config['bg'] }} {{ $config['text'] }} text-label-lg font-bold px-2 py-0.5 rounded-full whitespace-nowrap">
    {{ $config['label'] }}
</span>
