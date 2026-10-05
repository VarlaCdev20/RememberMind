@props([
    'type',
    'label',
    'icon' => null,
    'tone' => null,
    'onRemove' => null,
])

@php
    $effectiveTone = $tone ?: match ($type) {
        'alertas' => 'red',
        'habitacion' => 'blue',
        'medicacion' => 'violet',
        'cuidados' => 'green',
        'estado' => 'amber',
        default => 'green',
    };
@endphp

<span {{ $attributes->class(['rm-active-filter-chip', 'rm-active-filter-chip--'.$effectiveTone, 'animate-fade-in']) }}>
    @if($icon)
        <i class="ph-bold {{ $icon }} rm-active-filter-chip__icon" aria-hidden="true"></i>
    @endif
    <span class="rm-active-filter-chip__label">{{ $label }}</span>
    <button
        type="button"
        @if($onRemove)
            wire:click="{{ $onRemove }}"
        @else
            wire:click="removerFiltro('{{ $type }}')"
        @endif
        class="rm-active-filter-chip__remove"
        aria-label="Eliminar filtro {{ $label }}"
        title="Eliminar este filtro"
    >
        <i class="ph-bold ph-x" aria-hidden="true"></i>
    </button>
</span>