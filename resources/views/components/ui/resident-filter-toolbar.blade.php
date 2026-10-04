@props(['search', 'filtroRapido', 'filtroHabitacion', 'orden', 'vistaModo', 'stats', 'habitaciones'])
<div class="rm-resident-directory__filters" aria-label="Buscar y filtrar residentes">
    <div class="rm-resident-directory__filter-search">
        <label for="resident-directory-search">Buscar residentes</label>
        <div class="rm-resident-directory__filter-search-control">
            <i class="ph-bold ph-magnifying-glass" aria-hidden="true"></i>
            <input id="resident-directory-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar entre mis residentes..." autocomplete="off">
        </div>
    </div>
    <div class="rm-resident-directory__filter-bottom">
        <div class="rm-resident-directory__filter-chips" role="group" aria-label="Estado de alertas">
            <button type="button" wire:click="$set('filtroRapido', 'TODOS')" aria-pressed="{{ $filtroRapido === 'TODOS' ? 'true' : 'false' }}" class="rm-resident-directory__filter-chip {{ $filtroRapido === 'TODOS' ? 'is-active' : '' }}">Todos ({{ $stats['total'] }})</button>
            <button type="button" wire:click="$set('filtroRapido', 'CON_ALERTAS')" aria-pressed="{{ $filtroRapido === 'CON_ALERTAS' ? 'true' : 'false' }}" class="rm-resident-directory__filter-chip {{ $filtroRapido === 'CON_ALERTAS' ? 'is-active' : '' }}">Con alertas ({{ $stats['con_alertas'] }})</button>
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
        <div class="rm-resident-directory__filter-view" role="group" aria-label="Vista de residentes">
            <button type="button" wire:click="$set('vistaModo', 'tarjetas')" aria-label="Ver tarjetas" aria-pressed="{{ $vistaModo === 'tarjetas' ? 'true' : 'false' }}" class="{{ $vistaModo === 'tarjetas' ? 'is-active' : '' }}"><i class="ph-bold ph-squares-four" aria-hidden="true"></i></button>
            <button type="button" wire:click="$set('vistaModo', 'tabla')" aria-label="Ver listado" aria-pressed="{{ $vistaModo === 'tabla' ? 'true' : 'false' }}" class="{{ $vistaModo === 'tabla' ? 'is-active' : '' }}"><i class="ph-bold ph-list-dashes" aria-hidden="true"></i></button>
        </div>
    </div>
</div>
