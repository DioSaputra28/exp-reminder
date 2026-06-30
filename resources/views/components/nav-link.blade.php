@props(['href', 'active' => false, 'icon', 'label'])

<a href="{{ $href }}"
   class="flex flex-col items-center justify-center {{ $active ? 'bg-secondary-container text-on-secondary-container rounded-xl' : 'text-on-surface-variant hover:bg-surface-container rounded-xl' }} px-3 py-1 active:scale-95 transition-transform duration-200">
    <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-6 h-6"/>
    <span class="text-label-md mt-1">{{ $label }}</span>
</a>
