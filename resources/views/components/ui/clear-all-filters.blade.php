@props([
    'label' => 'Limpiar todos',
    'action' => 'limpiarFiltrosActivos',
])

<button
    type="button"
    wire:click="{{ $action }}"
    {{ $attributes->class(['rm-clear-all-filters-btn']) }}
    aria-label="{{ $label }}"
    title="Limpiar todos los filtros aplicados"
>
    <i class="ph-bold ph-arrow-counter-clockwise rm-clear-all-filters-btn__icon" aria-hidden="true"></i>
    <span>{{ $label }}</span>
</button>