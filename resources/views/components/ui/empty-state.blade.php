@props([
    'icon' => null,
    'icono' => null,
    'title' => null,
    'titulo' => null,
    'description' => null,
    'texto' => null,
    'subtitle' => null,
    'color' => null,
    'compact' => false,
    'actionText' => null,
    'actionMethod' => null,
    'actionHref' => null,
    'actionIcon' => 'ph-arrow-counter-clockwise',
])

@php
    $finalIcon = $icon ?? $icono ?? 'ph-folder-open';
    $finalTitle = $title ?? $titulo ?? 'Sin resultados';
    $finalText = $description ?? $texto ?? $subtitle ?? 'No hay registros que coincidan con los criterios de búsqueda o filtros seleccionados.';
@endphp

<div {{ $attributes->class([
    'rm-empty-state',
    'rm-empty-state--compact' => $compact,
]) }} role="status">
    <div class="rm-empty-state-icon">
        <i class="ph-bold {{ $finalIcon }} text-3xl {{ $color ?? '' }}" aria-hidden="true"></i>
    </div>
    <h3 class="rm-empty-state-title">{{ $finalTitle }}</h3>
    <p class="rm-empty-state-text">{{ $finalText }}</p>

    @if(!empty($actionMethod) || !empty($actionHref) || $slot->isNotEmpty())
        <div class="rm-empty-state-action mt-3">
            @if(!empty($actionMethod))
                <button type="button" wire:click="{{ $actionMethod }}" class="rm-btn rm-btn-secondary rm-btn-sm cursor-pointer">
                    @if($actionIcon)<i class="ph-bold {{ $actionIcon }} text-sm" aria-hidden="true"></i>@endif
                    <span>{{ $actionText ?? 'Restablecer filtros' }}</span>
                </button>
            @elseif(!empty($actionHref))
                <a href="{{ $actionHref }}" class="rm-btn rm-btn-primary rm-btn-sm">
                    @if($actionIcon)<i class="ph-bold {{ $actionIcon }} text-sm" aria-hidden="true"></i>@endif
                    <span>{{ $actionText }}</span>
                </a>
            @else
                {{ $slot }}
            @endif
        </div>
    @endif
</div>
