{{--
 Componente: ui/action-button
 Variantes: primary, secondary, ghost, success, danger, info, icon
 Tamaños: sm (38px), md (44px default), lg (48px)
 Consume el contrato de tokens V2: --rm-button-*
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'icono' => null,
    'tipo' => 'button',
    'loading' => null,
])

@php
    $baseClass = "rm-btn";

    $variantClass = match($variant) {
        'primary' => "rm-btn-primary",
        'secondary' => "rm-btn-secondary",
        'ghost' => "rm-btn-ghost",
        'success' => "rm-btn-success",
        'danger' => "rm-btn-danger",
        'info' => "rm-btn-info",
        'icon' => "rm-btn-icon",
        'clinical' => "rm-btn-clinical",
        'accent' => "rm-btn-accent",
        'navy' => "rm-btn-secondary", // retrocompatibilidad con alias navy
        default => "rm-btn-primary",
    };

    $sizeClass = match($size) {
        'sm' => ($variant === 'icon' ? 'rm-btn-icon-sm' : 'rm-btn-sm'),
        'lg' => 'rm-btn-lg',
        default => ($variant === 'icon' ? '' : 'rm-btn-md'),
    };
@endphp

<button type="{{ $tipo }}" {{ $attributes->merge(['class' => "{$baseClass} {$variantClass} {$sizeClass}"]) }} @if($loading) wire:loading.attr="disabled" @endif>
    @if($icono)
        <i class="ph-bold {{ $icono }} {{ $variant === 'icon' ? 'text-base' : 'mr-1.5 text-sm sm:text-base' }}"></i>
    @endif
    {{ $slot }}
</button>
