@props([
    'search' => '',
    'searchPlaceholder' => 'Buscar...',
    'orden' => 'NOMBRE_ASC',
    'vistaModo' => 'tarjetas',
    'filtrosActivos' => [],
    'categories' => [],
    'sortOptions' => [
        ['value' => 'NOMBRE_ASC', 'label' => 'Nombre A–Z'],
        ['value' => 'NOMBRE_DESC', 'label' => 'Nombre Z–A'],
        ['value' => 'HAB_ASC', 'label' => 'Habitación'],
    ],
    'viewOptions' => [
        ['value' => 'tarjetas', 'label' => 'Grid', 'icon' => 'ph-squares-four'],
        ['value' => 'tabla', 'label' => 'Lista', 'icon' => 'ph-list-dashes'],
    ],
])

@php
    $activeFiltersMap = [];
    foreach ($filtrosActivos as $f) {
        $activeFiltersMap[$f['tipo']] = $f['valor'];
    }
    $firstCategoryType = !empty($categories) ? $categories[0]['type'] : 'alertas';
@endphp

<div
    x-data="{
        openMenu: false,
        activeType: '{{ $firstCategoryType }}',
        defaultType: '{{ $firstCategoryType }}',
        toggleMenu() {
            this.openMenu = !this.openMenu;
            if (this.openMenu && !this.activeType) {
                this.activeType = this.defaultType;
            }
        },
        selectType(type) {
            this.activeType = type;
        },
        selectValue(type, value) {
            $wire.aplicarFiltro(type, value);
            this.close();
        },
        close() {
            this.openMenu = false;
        }
    }"
    @click.outside="close()"
    @keydown.escape.window="close()"
    {{ $attributes->class(['rm-filter-toolbar']) }}
    aria-label="Barra de búsqueda y filtros"
>
    {{-- Fila Principal: Búsqueda | + Añadir Filtro | Orden | Grid/Lista --}}
    <div class="rm-filter-toolbar__main-row">
        {{-- 1. Buscador con feedback reactivo --}}
        <div class="rm-filter-toolbar__search-col">
            <x-ui.filter-search
                model="search"
                :placeholder="$searchPlaceholder"
            />
        </div>

        {{-- 2. Acciones y Controles --}}
        <div class="rm-filter-toolbar__actions-col">
            {{-- Añadir Filtro + Popovers Progresivos Flotantes Separados --}}
            <div class="rm-filter-toolbar__filter-menu-wrapper">
                <x-ui.add-filter-button :active-count="count($filtrosActivos)" />

                {{-- Popover Nivel 1: Menú de Categorías (Tarjeta Flotante Translúcida) --}}
                <div
                    x-show="openMenu"
                    x-cloak
                    x-transition:enter="transition ease-out duration-160"
                    x-transition:enter-start="opacity-0 translate-y-1.5 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-120"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-1.5 scale-95"
                    class="rm-filter-level1-card"
                    role="menu"
                    aria-label="Categorías de filtro"
                >
                    <x-ui.filter-type-menu
                        :categories="$categories"
                        :active-filters-map="$activeFiltersMap"
                    />

                    {{-- Popover Nivel 2: Panel de Valores Contiguo (Tarjeta Flotante Translúcida Separada) --}}
                    <div
                        x-show="activeType !== null"
                        x-cloak
                        x-transition:enter="transition ease-out duration-160"
                        x-transition:enter-start="opacity-0 translate-x-2 scale-95"
                        x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                        x-transition:leave="transition ease-in duration-120"
                        x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-x-2 scale-95"
                        class="rm-filter-level2-card"
                        role="region"
                        aria-label="Valores del filtro"
                    >
                        <x-ui.filter-value-popover
                            :categories="$categories"
                            :active-filters-map="$activeFiltersMap"
                        />
                    </div>
                </div>
            </div>

            {{-- 3. Selector de Orden --}}
            <x-ui.sort-select
                model="orden"
                :value="$orden"
                :options="$sortOptions"
            />

            {{-- 4. Switch Grid / Lista --}}
            <x-ui.view-toggle
                model="vistaModo"
                :value="$vistaModo"
                :options="$viewOptions"
            />
        </div>
    </div>

    {{-- Fila de Filtros Activos (solo si existe al menos 1 filtro aplicado) --}}
    <x-ui.active-filters-row :filters="$filtrosActivos" />
</div>
