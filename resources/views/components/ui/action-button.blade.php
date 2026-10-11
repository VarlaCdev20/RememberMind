{{--
 Componente: ui/action-button
 Variantes: primary, secondary, ghost, success, danger, info, icon
 Tamaños: sm (36px), md (44px default), lg (48px)
 Consume el contrato de tokens V2: --rm-button-*
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'icono' => null,
    'tipo' => 'button',
    'loading' => null,
    'iconVariant' => 'default',
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
        'lg' => ($variant === 'icon' ? 'rm-btn-icon-lg' : 'rm-btn-lg'),
        default => ($variant === 'icon' ? '' : 'rm-btn-md'),
    };
    $iconTone = in_array($iconVariant, ['default', 'ghost', 'soft', 'critical'], true) ? $iconVariant : 'default';
    $isLoading = $loading === true;
    $loadingTarget = is_string($loading) && $loading !== '' ? $loading : null;
    $accessibleLabel = $attributes->get('aria-label') ?: $attributes->get('title');
    if ($variant === 'icon' && !$accessibleLabel && trim(strip_tags((string) $slot)) === '') {
        throw new \InvalidArgumentException('El botón de solo icono requiere aria-label o title.');
    }
@endphp

<button type="{{ $tipo }}" {{ $attributes->class(["{$baseClass} {$variantClass} {$sizeClass}", 'rm-btn-icon--'.$iconTone => $variant === 'icon', 'is-loading' => $isLoading]) }} @if($variant === 'icon' && !$attributes->has('aria-label')) aria-label="{{ $accessibleLabel ?: trim(strip_tags((string) $slot)) }}" @endif @if($isLoading) disabled aria-busy="true" @endif @if($loadingTarget) wire:loading.class="is-loading" wire:loading.attr="disabled" wire:target="{{ $loadingTarget }}" @endif>
    @if($isLoading || $loadingTarget)<span class="rm-btn__spinner {{ $loadingTarget ? 'rm-btn__spinner--wire' : '' }}" aria-hidden="true" @if($loadingTarget) wire:loading wire:target="{{ $loadingTarget }}" @endif></span>@endif
    @if($icono)
        <i class="ph-bold {{ $icono }}" aria-hidden="true"></i>
    @endif
    {{ $slot }}
</button>
