@php
    $filtrosKardex = [
        ['campo' => 'filtroKardexBusqueda', 'valor' => $filtroKardexBusqueda, 'label' => 'Búsqueda: '.$filtroKardexBusqueda],
        ['campo' => 'filtroKardexEstado', 'valor' => $filtroKardexEstado, 'label' => 'Estado: '.match($filtroKardexEstado) { 'RETRASADA' => 'Retrasadas', 'PENDIENTE' => 'Pendientes', 'PROXIMA' => 'Próximas', default => 'Administradas' }],
        ['campo' => 'filtroKardexResidente', 'valor' => $filtroKardexResidente, 'label' => 'Residente: '.($residentes->firstWhere('cod_residente', $filtroKardexResidente)?->nombre_completo ?? $filtroKardexResidente)],
        ['campo' => 'filtroKardexHorario', 'valor' => $filtroKardexHorario, 'label' => 'Horario: '.$filtroKardexHorario],
        ['campo' => 'filtroKardexVia', 'valor' => $filtroKardexVia, 'label' => 'Vía: '.ucfirst(strtolower($filtroKardexVia))],
        ['campo' => 'filtroKardexPrn', 'valor' => $filtroKardexPrn, 'label' => 'PRN: '.($filtroKardexPrn === 'PRN' ? 'Según necesidad' : 'Horario fijo')],
        ['campo' => 'filtroKardexFecha', 'valor' => $filtroKardexFecha !== now()->toDateString() ? $filtroKardexFecha : '', 'label' => 'Fecha: '.$filtroKardexFecha],
    ];
    $filtrosKardex = array_values(array_filter($filtrosKardex, fn ($filtro) => trim($filtro['valor']) !== ''));
@endphp
<x-ui.filter-bar class="rm-medication-workspace__filters" x-data="{ masFiltros: false }" aria-label="Filtros del Kardex">
    <div class="rm-medication-workspace__filter-primary">
        <div class="rm-medication-workspace__search">
            <label for="medication-search" class="sr-only">Buscar residente o medicamento</label>
            <i class="ph-bold ph-magnifying-glass" aria-hidden="true"></i>
            <input id="medication-search" type="search" wire:model.live.debounce.300ms="filtroKardexBusqueda" placeholder="Buscar residente o medicamento..." autocomplete="off">
        </div>
        <div>
            <label for="medication-state" class="sr-only">Estado de la dosis</label>
            <select id="medication-state" wire:model.live="filtroKardexEstado">
                <option value="">Todos los estados</option><option value="PROXIMA">Próximas</option><option value="PENDIENTE">Pendientes</option><option value="RETRASADA">Retrasadas</option><option value="ADMINISTRADA">Administradas</option>
            </select>
        </div>
        <div>
            <label for="medication-resident" class="sr-only">Residente</label>
            <select id="medication-resident" wire:model.live="filtroKardexResidente">
                <option value="">Todos los residentes</option>
                @foreach($residentes as $res)<option value="{{ $res->cod_residente }}">{{ $res->nombres }} {{ $res->apellido_paterno }}</option>@endforeach
            </select>
        </div>
        <button type="button" class="rm-btn rm-btn-secondary" @click="masFiltros = !masFiltros" :aria-expanded="masFiltros" aria-controls="medication-more-filters"><i class="ph-bold ph-faders" aria-hidden="true"></i><span>Más filtros</span></button>
    </div>
    <div id="medication-more-filters" x-show="masFiltros" x-cloak class="rm-medication-workspace__filter-extra">
        <div>
            <label for="medication-schedule">Horario</label>
            <select id="medication-schedule" wire:model.live="filtroKardexHorario">
                <option value="">Cualquier horario</option><option value="MANANA">Mañana (06:00 - 13:00)</option><option value="TARDE">Tarde (13:00 - 19:00)</option><option value="NOCHE">Noche (19:00 - 06:00)</option>
                @foreach(['07:00','08:00','12:00','16:00','20:00'] as $horaFiltro)<option value="{{ $horaFiltro }}">{{ $horaFiltro }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="medication-route">Vía de administración</label>
            <select id="medication-route" wire:model.live="filtroKardexVia">
                <option value="">Vía (Todas)</option>
                @foreach(['ORAL'=>'Oral','SUBLINGUAL'=>'Sublingual','INTRAVENOSA'=>'Intravenosa','INTRAMUSCULAR'=>'Intramuscular','SUBCUTANEA'=>'Subcutánea','TOPICA'=>'Tópica','INHALATORIA'=>'Inhalatoria'] as $codigoVia=>$nombreVia)<option value="{{ $codigoVia }}">{{ $nombreVia }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="medication-prn">Tipo de indicación</label>
            <select id="medication-prn" wire:model.live="filtroKardexPrn"><option value="">PRN (Todos)</option><option value="PRN">Según necesidad (PRN)</option><option value="FIJO">Horario fijo</option></select>
        </div>
        <div><label for="medication-date">Fecha de visualización</label><input id="medication-date" type="date" wire:model.live="filtroKardexFecha"></div>
    </div>
    @if(count($filtrosKardex) > 0)
        <div class="rm-active-filters-row" aria-label="Filtros activos aplicados">
            <span class="rm-active-filters-row__label">Filtros activos:</span>
            <div class="rm-active-filters-row__chips">
                @foreach($filtrosKardex as $filtro)
                    <x-ui.active-filter-chip :type="$filtro['campo']" :label="$filtro['label']" tone="green" :on-remove="'limpiarFiltro(\''.$filtro['campo'].'\')'" />
                @endforeach
                <span class="rm-medication-workspace__count">{{ count($dosisHoy) }} coincidentes</span>
                <x-ui.clear-all-filters label="Limpiar filtros" action="resetFilters" />
            </div>
        </div>
    @endif
</x-ui.filter-bar>
