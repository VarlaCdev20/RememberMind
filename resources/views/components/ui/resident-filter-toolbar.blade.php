@props(['search', 'filtroRapido', 'filtroHabitacion', 'orden', 'vistaModo', 'stats', 'habitaciones'])
<x-ui.filter-bar as="div" class="rm-resident-directory__filters" aria-label="Buscar y filtrar residentes">
    <div class="rm-resident-directory__filter-search">
        <label for="resident-directory-search">Buscar residentes</label>
        <div class="rm-resident-directory__filter-search-control">
            <i class="ph-bold ph-magnifying-glass" aria-hidden="true"></i>
            <input id="resident-directory-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar entre mis residentes..." autocomplete="off">
        </div>
    </div>
    <div class="rm-resident-directory__filter-bottom">
        <div class="rm-resident-directory__filter-chips" role="group" aria-label="Estado de alertas">
            <x-ui.filter-chip label="Todos" :count="$stats['total']" :active="$filtroRapido === 'TODOS'" action="$set('filtroRapido', 'TODOS')" />
            <x-ui.filter-chip label="Con alertas" :count="$stats['con_alertas']" :active="$filtroRapido === 'CON_ALERTAS'" action="$set('filtroRapido', 'CON_ALERTAS')" />
        </div>
        <div class="rm-resident-directory__filter-selects">
            <label for="resident-directory-room">Habitación</label>
            <select id="resident-directory-room" wire:model.live="filtroHabitacion">
                <option value="">Todas las habitaciones</option>
                @foreach($habitaciones as $habitacion)<option value="{{ $habitacion }}">{{ $habitacion }}</option>@endforeach
            </select>
            <label for="resident-directory-order">Orden</label>
            <select id="resident-directory-order" wire:model.live="orden">
                <option value="NOMBRE_ASC">Nombre A–Z</option>
                <option value="NOMBRE_DESC">Nombre Z–A</option>
                <option value="HAB_ASC">Habitación asc.</option>
                <option value="HAB_DESC">Habitación desc.</option>
            </select>
        </div>
        <x-ui.view-toggle model="vistaModo" :value="$vistaModo" label="Vista de residentes" :options="[
            ['value' => 'tarjetas', 'label' => 'Ver tarjetas', 'icon' => 'ph-squares-four'],
            ['value' => 'tabla', 'label' => 'Ver listado', 'icon' => 'ph-list-dashes'],
        ]" />
    </div>
</x-ui.filter-bar>
