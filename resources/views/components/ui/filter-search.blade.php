@props([
    'model' => 'search',
    'placeholder' => 'Buscar...',
    'label' => 'Buscar',
    'debounce' => '300ms',
])

<div {{ $attributes->class(['rm-filter-search']) }}>
    <label for="filter-search-input" class="sr-only">{{ $label }}</label>
    <div class="rm-filter-search__control">
        <span class="rm-filter-search__icon" aria-hidden="true">
            <i class="ph-bold ph-magnifying-glass"></i>
        </span>
        <input
            id="filter-search-input"
            type="search"
            wire:model.live.debounce.{{ $debounce }}="{{ $model }}"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            class="rm-filter-search__input"
        >
        @if(!empty($this->{$model} ?? null))
            <button
                type="button"
                wire:click="$set('{{ $model }}', '')"
                class="rm-filter-search__clear"
                title="Limpiar búsqueda"
                aria-label="Limpiar búsqueda"
            >
                <i class="ph-bold ph-x" aria-hidden="true"></i>
            </button>
        @endif
    </div>
</div>