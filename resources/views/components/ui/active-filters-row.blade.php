@props([
    'filters' => [],
    'clearAction' => 'limpiarFiltrosActivos',
])

@if(count($filters) > 0)
    <div {{ $attributes->class(['rm-active-filters-row animate-fade-in']) }} aria-label="Filtros activos aplicados">
        <div class="rm-active-filters-row__heading">
            <span class="rm-active-filters-row__label">Filtros activos:</span>
        </div>
        <div class="rm-active-filters-row__chips">
            @foreach($filters as $filter)
                <x-ui.active-filter-chip
                    :type="$filter['tipo']"
                    :label="$filter['label']"
                    :icon="$filter['icono'] ?? null"
                    :tone="$filter['tono'] ?? null"
                    :key="'active-filter-'.$filter['tipo'].'-'.$filter['valor']"
                />
            @endforeach
            <x-ui.clear-all-filters :action="$clearAction" />
        </div>
    </div>
@endif