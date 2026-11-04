@use('App\Modulos\Alertas\Aplicacion\Formato\LenguajeAlerta')
<div class="rm-management-workspace space-y-4">
    <x-ui.collection-header title="Alertas" subtitle="Supervisión de pendientes y seguimiento del equipo" icon="ph-bell-ringing" />
    <div class="rm-management-overview" aria-label="Alertas pendientes en el ámbito autorizado">
        @foreach([['urgentes','Prioridad alta o crítica',$conteos['criticas']+$conteos['altas']],['sin_responsable','Sin responsable registrado',$conteos['sin_responsable']],['fuera_plazo','Con límite registrado superado',$conteos['vencidas']]] as [$filtro,$nombre,$cantidad])
        <button type="button" class="rm-card p-4 text-left" wire:click="filtrarResumen('{{ $filtro }}')" wire:loading.attr="disabled"><span class="block rm-help">{{ $nombre }}</span><strong class="text-2xl">{{ $cantidad }}</strong><span class="block rm-help">Ver alertas</span></button>
        @endforeach
    </div>
    <p class="rm-help">Los tres conteos consideran alertas activas de tu ámbito antes de los filtros de lista. Pueden referirse a la misma alerta; no se suman.</p>
    <x-ui.filter-bar>
        <div class="rm-management-tabs">
            <x-ui.field label="Buscar alertas" for="gerencia-alertas-buscar"><input id="gerencia-alertas-buscar" class="rm-input" type="search" wire:model.live.debounce.300ms="search" placeholder="Residente, habitación o motivo" maxlength="250"></x-ui.field>
            <x-ui.field label="Estado" for="gerencia-alertas-estado"><select id="gerencia-alertas-estado" class="rm-input" wire:model.live="filtroEstado">@foreach(['ACTIVAS'=>'Activas','POR_ATENDER'=>'Por atender','EN_ATENCION'=>'En atención','ATENDIDA'=>'Atendidas, por cerrar','CERRADA'=>'Cerradas','ANULADA'=>'Anuladas','ANTERIORES'=>'Registros anteriores','CON_LIMITE_SUPERADO'=>'Con límite superado',''=>'Todos'] as $valor=>$etiqueta)<option value="{{ $valor }}">{{ $etiqueta }}</option>@endforeach</select></x-ui.field>
            <x-ui.field label="Prioridad registrada" for="gerencia-alertas-prioridad"><select id="gerencia-alertas-prioridad" class="rm-input" wire:model.live="filtroNivel">@foreach([''=>'Todas','PRIORITARIAS'=>'Críticas y altas activas','CRITICO'=>'Crítica','ALTO'=>'Alta','MEDIO'=>'Media','BAJO'=>'Baja'] as $valor=>$etiqueta)<option value="{{ $valor }}">{{ $etiqueta }}</option>@endforeach</select></x-ui.field>
            <button type="button" class="rm-btn-secondary" wire:click="limpiarFiltros">Limpiar</button>
        </div>
        <details class="mt-3" @if($filtroOrigen || $filtroResponsabilidad || $fechaDesde || $fechaHasta || $filtroEspecialidad) open @endif><summary>Origen, responsable y fechas</summary><div class="rm-management-tabs mt-3">
            <x-ui.field label="Especialidad vinculada" for="gerencia-alertas-especialidad"><select id="gerencia-alertas-especialidad" class="rm-input" wire:model.live="filtroEspecialidad"><option value="">Todas las autorizadas</option>@foreach($especialidadesDisponibles as $clave=>$nombre)<option value="{{ $clave }}">{{ $nombre }}</option>@endforeach<option value="SIN_VINCULO">Sin vínculo verificable</option></select></x-ui.field>
            <x-ui.field label="Origen registrado" for="gerencia-alertas-origen"><select id="gerencia-alertas-origen" class="rm-input" wire:model.live="filtroOrigen"><option value="">Todos</option>@foreach($origenesDisponibles as $origenDisponible)<option value="{{ $origenDisponible }}">{{ str_replace('_',' ', $origenDisponible) }}</option>@endforeach</select></x-ui.field>
            <x-ui.field label="Responsabilidad" for="gerencia-alertas-responsable"><select id="gerencia-alertas-responsable" class="rm-input" wire:model.live="filtroResponsabilidad"><option value="">Cualquier responsable</option><option value="SIN_RESPONSABLE">Sin responsable registrado</option></select></x-ui.field>
            <x-ui.field label="Desde" for="gerencia-alertas-desde"><input id="gerencia-alertas-desde" class="rm-input" type="date" wire:model.live="fechaDesde">@error('fechaDesde')<p role="alert">{{ $message }}</p>@enderror</x-ui.field>
            <x-ui.field label="Hasta" for="gerencia-alertas-hasta"><input id="gerencia-alertas-hasta" class="rm-input" type="date" wire:model.live="fechaHasta">@error('fechaHasta')<p role="alert">{{ $message }}</p>@enderror</x-ui.field>
        </div></details>
    </x-ui.filter-bar>
    <div wire:loading.delay role="status" class="rm-help">Consultando alertas…</div>
    <section class="rm-card p-4" aria-labelledby="gerencia-alertas-listado" x-ref="contextoCaso" tabindex="-1">
        <h2 id="gerencia-alertas-listado" class="rm-section-title">Alertas de la consulta</h2><p class="rm-help">{{ $alertas->total() }} resultados con los filtros actuales. Gerencia consulta y supervisa; la atención se registra desde el rol competente.</p>
        @if($alertas->isEmpty())<x-ui.empty-state title="No hay alertas en esta consulta" description="Puedes cambiar los filtros para revisar otros estados. No se crean alertas para completar el resumen." />
        @else<div class="rm-table-scroll"><table class="rm-table"><caption class="sr-only">Alertas, responsables y seguimiento</caption><thead><tr><th scope="col">Situación y residente</th><th scope="col">Prioridad</th><th scope="col">Estado</th><th scope="col">Responsable</th><th scope="col">Fecha registrada</th><th scope="col">Acción</th></tr></thead><tbody>@foreach($alertas as $alerta)<tr wire:key="gerencia-alerta-{{ $alerta->getKey() }}"><th scope="row"><strong>{{ $alerta->residente?->nombre_completo ?: 'Sin residente identificado' }}</strong><small class="block">{{ LenguajeAlerta::tipo($alerta->tipo) }}</small></th><td>{{ LenguajeAlerta::prioridad($alerta->prioridad) }}</td><td>{{ LenguajeAlerta::estado($alerta->estado) }}</td><td>{{ LenguajeAlerta::responsable($alerta->responsable) }}</td><td>{{ $alerta->fecha_hora?->format('d/m/Y H:i') ?: 'Sin registrar' }}</td><td><button type="button" class="rm-btn-secondary" wire:click="verDetalle('{{ $alerta->getKey() }}')" wire:loading.attr="disabled">Ver detalle<span class="sr-only"> de {{ $alerta->residente?->nombre_completo }}</span></button></td></tr>@endforeach</tbody></table></div>@endif
        <x-ui.paginacion :paginator="$alertas" mode="livewire" label="alertas" />
    </section>
    @include('livewire.alertas.modales.detalle')
    @include('livewire.alertas.modales.drawer-ubicacion')
    @include('livewire.alertas.modales.drawer-graficos')
</div>
