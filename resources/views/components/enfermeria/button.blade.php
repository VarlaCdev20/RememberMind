@props([
    'variant' => 'coral',
    'size' => 'md',
    'icon' => null,
    'iconPos' => 'left',
    'loading' => null,
    'type' => 'button',
])

@php
$classes = 'enf-btn ';
$classes .= match($variant) {
    'coral' => 'enf-btn-coral',
    'secondary' => 'enf-btn-secondary',
    'ghost' => 'enf-btn-ghost',
    'danger' => 'enf-btn-danger',
    'success' => 'enf-btn-success',
    default => 'enf-btn-coral',
};

$classes .= match($size) {
    'sm' => ' enf-btn-sm',
    'lg' => ' enf-btn-lg',
    default => '',
};
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }} @if($loading) wire:loading.attr="disabled" wire:target="{{ $loading }}" @endif>
    @if($loading)
        <i class="ph-bold ph-spinner animate-spin text-sm" wire:loading wire:target="{{ $loading }}"></i>
    @endif

    @if($icon && $iconPos === 'left')
        <i class="ph-bold {{ $icon }} text-sm" @if($loading) wire:loading.remove wire:target="{{ $loading }}" @endif></i>
    @endif

    <span>{{ $slot }}</span>

    @if($icon && $iconPos === 'right')
        <i class="ph-bold {{ $icon }} text-sm" @if($loading) wire:loading.remove wire:target="{{ $loading }}" @endif></i>
    @endif
</button>
