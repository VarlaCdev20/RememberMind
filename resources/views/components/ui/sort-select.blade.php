@props([
    'model' => 'orden',
    'value' => 'NOMBRE_ASC',
    'options' => [
        ['value' => 'NOMBRE_ASC', 'label' => 'Nombre A–Z'],
        ['value' => 'NOMBRE_DESC', 'label' => 'Nombre Z–A'],
        ['value' => 'HAB_ASC', 'label' => 'Habitación'],
    ],
])

<div {{ $attributes->class(['rm-sort-select']) }}>
    <label for="sort-select-control" class="sr-only">Ordenar resultados</label>
    <div class="rm-sort-select__control">
        <span class="rm-sort-select__icon" aria-hidden="true">
            <i class="ph-bold ph-arrows-down-up"></i>
        </span>
        <select
            id="sort-select-control"
            wire:model.live="{{ $model }}"
            class="rm-sort-select__select"
            aria-label="Ordenar resultados"
        >
            @foreach($options as $option)
                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
        <span class="rm-sort-select__caret" aria-hidden="true">
            <i class="ph-bold ph-caret-down"></i>
        </span>
    </div>
</div>