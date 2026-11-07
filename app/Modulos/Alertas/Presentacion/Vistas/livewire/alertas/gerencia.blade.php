@use('App\Modulos\Alertas\Aplicacion\Formato\LenguajeAlerta')
<div class="rm-management-workspace rm-manager-alerts space-y-4">
    <div class="rm-manager-alerts__main space-y-4">
    <x-ui.collection-header title="Alertas" subtitle="Supervisión de pendientes y seguimiento del equipo" icon="ph-bell-ringing" />
    <div class="rm-management-overview rm-manager-alerts__overview" aria-label="Resumen de alertas">
        @foreach([
            ['criticas', 'Críticas activas', 'ph-warning', 'critical'],
            ['altas', 'Altas activas', 'ph-warning-circle', 'high'],
            ['medias', 'Medias activas', 'ph-info', 'medium'],
            ['cerradas', 'Cerradas', 'ph-check-circle', 'closed'],
        ] as [$filtro, $nombre, $icono, $tono])
            <button type="button" class="rm-card rm-manager-alerts__metric" data-alert-summary="{{ $filtro }}" data-tone="{{ $tono }}" wire:click="filtrarResumen('{{ $filtro }}')" wire:loading.attr="disabled">
                <span class="rm-manager-alerts__metric-icon" aria-hidden="true"><i class="ph-bold {{ $icono }}"></i></span>
                <span class="rm-manager-alerts__metric-copy"><span>{{ $nombre }}</span><strong>{{ $conteos[$filtro] }}</strong></span>
                <i class="ph ph-arrow-right" aria-hidden="true"></i>
            </button>
        @endforeach
    </div>
    <x-ui.filter-bar>
        <div class="rm-manager-alerts__filters">
            <x-ui.field label="Buscar alertas" for="gerencia-alertas-buscar"><input id="gerencia-alertas-buscar" class="rm-input" type="search" wire:model.live.debounce.300ms="search" placeholder="Residente, habitación o motivo" maxlength="250"></x-ui.field>
            <x-ui.field label="Estado" for="gerencia-alertas-estado"><select id="gerencia-alertas-estado" class="rm-input" wire:model.live="filtroEstado">@foreach(['ACTIVAS'=>'Activas','POR_ATENDER'=>'Por atender','EN_ATENCION'=>'En atención','ATENDIDA'=>'Atendidas, por cerrar','CERRADA'=>'Cerradas','ANULADA'=>'Anuladas','ANTERIORES'=>'Registros anteriores','CON_LIMITE_SUPERADO'=>'Con límite superado',''=>'Todos'] as $valor=>$etiqueta)<option value="{{ $valor }}">{{ $etiqueta }}</option>@endforeach</select></x-ui.field>
            <x-ui.field label="Prioridad registrada" for="gerencia-alertas-prioridad"><select id="gerencia-alertas-prioridad" class="rm-input" wire:model.live="filtroNivel">@foreach([''=>'Todas','PRIORITARIAS'=>'Críticas y altas activas','CRITICO'=>'Crítica','ALTO'=>'Alta','MEDIO'=>'Media','BAJO'=>'Baja'] as $valor=>$etiqueta)<option value="{{ $valor }}">{{ $etiqueta }}</option>@endforeach</select></x-ui.field>
            <button type="button" class="rm-btn-secondary" wire:click="limpiarFiltros">Limpiar</button>
        </div>
        <details class="rm-manager-alerts__advanced" @if($filtroOrigen || $filtroResponsabilidad || $fechaDesde || $fechaHasta || $filtroEspecialidad) open @endif><summary><i class="ph ph-sliders-horizontal" aria-hidden="true"></i>Más filtros<span class="rm-help">Origen, responsable y fechas</span><i class="ph ph-caret-down" aria-hidden="true"></i></summary><div class="rm-management-tabs mt-3">
            <x-ui.field label="Especialidad vinculada" for="gerencia-alertas-especialidad"><select id="gerencia-alertas-especialidad" class="rm-input" wire:model.live="filtroEspecialidad"><option value="">Todas las autorizadas</option>@foreach($especialidadesDisponibles as $clave=>$nombre)<option value="{{ $clave }}">{{ $nombre }}</option>@endforeach<option value="SIN_VINCULO">Sin vínculo verificable</option></select></x-ui.field>
            <x-ui.field label="Origen registrado" for="gerencia-alertas-origen"><select id="gerencia-alertas-origen" class="rm-input" wire:model.live="filtroOrigen"><option value="">Todos</option>@foreach($origenesDisponibles as $origenDisponible)<option value="{{ $origenDisponible }}">{{ str_replace('_',' ', $origenDisponible) }}</option>@endforeach</select></x-ui.field>
            <x-ui.field label="Responsabilidad" for="gerencia-alertas-responsable"><select id="gerencia-alertas-responsable" class="rm-input" wire:model.live="filtroResponsabilidad"><option value="">Cualquier responsable</option><option value="SIN_RESPONSABLE">Sin responsable registrado</option></select></x-ui.field>
            <x-ui.field label="Desde" for="gerencia-alertas-desde"><input id="gerencia-alertas-desde" class="rm-input" type="date" wire:model.live="fechaDesde">@error('fechaDesde')<p role="alert">{{ $message }}</p>@enderror</x-ui.field>
            <x-ui.field label="Hasta" for="gerencia-alertas-hasta"><input id="gerencia-alertas-hasta" class="rm-input" type="date" wire:model.live="fechaHasta">@error('fechaHasta')<p role="alert">{{ $message }}</p>@enderror</x-ui.field>
        </div></details>
    </x-ui.filter-bar>
    <div wire:loading.delay role="status" class="rm-help">Consultando alertas…</div>
    <section class="rm-card rm-manager-alerts__list" aria-labelledby="gerencia-alertas-listado" x-ref="contextoCaso" tabindex="-1">
        <header class="rm-manager-alerts__list-heading"><div><h2 id="gerencia-alertas-listado" class="rm-section-title">Alertas de la consulta <span class="rm-badge rm-badge-neutral">{{ $alertas->total() }}</span></h2><p class="rm-help">Gerencia consulta y supervisa el seguimiento del equipo.</p></div><span class="rm-badge rm-badge-neutral"><i class="ph ph-eye" aria-hidden="true"></i>Consulta</span></header>
        @if($alertas->isEmpty())<x-ui.empty-state title="No hay alertas en esta consulta" description="Puedes cambiar los filtros para revisar otros estados. No se crean alertas para completar el resumen." />
        @else
            <ul class="rm-manager-alerts__items" aria-label="Resultados de alertas">
                @foreach($alertas as $alerta)
                    @php
                        $tonoPrioridad = match ($alerta->prioridad) {
                            'CRITICO', 'CRITICA' => 'critical',
                            'ALTO', 'ALTA' => 'high',
                            'MEDIO', 'MEDIA' => 'medium',
                            default => 'neutral',
                        };
                        $tonoTarjeta = match ($alerta->estado) {
                            'CERRADA' => 'closed',
                            'ANULADA' => 'neutral',
                            default => $tonoPrioridad,
                        };
                    @endphp
                    <li class="rm-manager-alerts__item" data-tone="{{ $tonoTarjeta }}" wire:key="gerencia-alerta-{{ $alerta->getKey() }}">
                        <span class="rm-manager-alerts__metric-icon" aria-hidden="true"><i class="ph-bold {{ match ($tonoTarjeta) { 'closed' => 'ph-check-circle', 'critical', 'high' => 'ph-warning', default => 'ph-bell' } }}"></i></span>
                        <div class="rm-manager-alerts__identity">
                            <h3>{{ LenguajeAlerta::tipo($alerta->tipo) }}</h3>
                            <p>{{ $alerta->residente?->nombre_completo ?: 'Sin residente identificado' }}</p>
                            <div class="rm-manager-alerts__meta">
                                <span><i class="ph ph-user" aria-hidden="true"></i> {{ LenguajeAlerta::responsable($alerta->responsable) }}</span>
                                <span><i class="ph ph-calendar" aria-hidden="true"></i> {{ $alerta->fecha_hora?->format('d/m/Y H:i') ?: 'Fecha sin registrar' }}</span>
                            </div>
                        </div>
                        <div class="rm-manager-alerts__tracking">
                            <x-ui.status-badge :label="LenguajeAlerta::estado($alerta->estado)" :variant="$tonoTarjeta === 'closed' ? 'success' : 'neutral'" />
                            <span class="rm-manager-alerts__priority" data-tone="{{ $tonoPrioridad }}">{{ LenguajeAlerta::prioridad($alerta->prioridad) }}</span>
                        </div>
                        <button type="button" class="rm-btn rm-btn-secondary" wire:click="verDetalle('{{ $alerta->getKey() }}')" wire:loading.attr="disabled">
                            <i class="ph ph-eye" aria-hidden="true"></i>Ver detalle<span class="sr-only"> de {{ $alerta->residente?->nombre_completo }}</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
        <x-ui.paginacion :paginator="$alertas" mode="livewire" label="alertas" />
    </section>
    </div>
    @include('livewire.alertas.modales.detalle', ['detalleAcoplado' => true])
    @include('livewire.alertas.modales.drawer-ubicacion')
    @include('livewire.alertas.modales.drawer-graficos')
</div>
