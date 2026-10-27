<x-sistema-layout>
<div class="rm-management-workspace space-y-4">
    <x-ui.collection-header title="Residentes" subtitle="Identidad, estado administrativo y alojamiento registrado" icon="ph-users-three"><x-slot:actions>@if($verAlojamiento)<a href="{{ route('admin.habitaciones.index') }}" class="rm-btn-secondary">Ver ocupación</a>@endif</x-slot:actions></x-ui.collection-header>
    <div class="rm-management-overview"><x-ui.metric-card label="Residentes del filtro" :value="$resumen['total']" icon="ph-users" /><x-ui.metric-card label="Con alojamiento registrado" :value="$resumen['con_cama'] ?? 'Consulta restringida'" icon="ph-bed" /><x-ui.metric-card label="Sin alojamiento registrado" :value="$resumen['sin_cama'] ?? 'Consulta restringida'" icon="ph-house" /></div>
    <form method="GET" action="{{ route('admin.residentes.index') }}" class="rm-filter-bar rm-management-tabs">
        <input type="hidden" name="vista" value="{{ $vista }}">
        <x-ui.field label="Buscar residente" for="gerencia-residente-buscar"><input id="gerencia-residente-buscar" class="rm-input" type="search" name="buscar" value="{{ request('buscar') }}" maxlength="120" placeholder="Nombre y apellidos"></x-ui.field>
        <x-ui.field label="Estado administrativo" for="gerencia-residente-estado"><select id="gerencia-residente-estado" class="rm-input" name="estado"><option value="">Todos</option>@foreach($estados as $estado)<option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ str_replace('_', ' ', $estado) }}</option>@endforeach</select></x-ui.field>
        @if($verAlojamiento)
        <x-ui.field label="Alojamiento" for="gerencia-residente-alojamiento"><select id="gerencia-residente-alojamiento" class="rm-input" name="alojamiento"><option value="">Todos</option><option value="con_cama" @selected(request('alojamiento') === 'con_cama')>Con cama</option><option value="sin_cama" @selected(request('alojamiento') === 'sin_cama')>Sin cama registrada</option></select></x-ui.field>
        <x-ui.field label="Piso" for="gerencia-residente-piso"><select id="gerencia-residente-piso" class="rm-input" name="piso"><option value="">Todos</option>@foreach($pisos as $piso)<option value="{{ $piso }}" @selected(request('piso') == $piso)>{{ $piso }}</option>@endforeach</select></x-ui.field>
        <x-ui.field label="Habitación" for="gerencia-residente-habitacion"><select id="gerencia-residente-habitacion" class="rm-input" name="habitacion"><option value="">Todas</option>@foreach($habitaciones as $habitacion)<option value="{{ $habitacion->getKey() }}" @selected(request('habitacion') === $habitacion->getKey())>{{ $habitacion->codigo }}</option>@endforeach</select></x-ui.field>
        @endif
        <button type="submit" class="rm-btn-primary">Aplicar</button><a href="{{ route('admin.residentes.index') }}" class="rm-btn-secondary">Limpiar</a>
    </form>
    <nav aria-label="Presentación de residentes" class="rm-management-actions">@foreach(['tarjetas'=>'Tarjetas','tabla'=>'Tabla'] as $opcion=>$texto)<a href="{{ route('admin.residentes.index', array_merge(request()->except(['page','vista']), ['vista'=>$opcion])) }}" class="{{ $vista === $opcion ? 'rm-btn-primary' : 'rm-btn-secondary' }}" @if($vista === $opcion) aria-current="page" @endif>{{ $texto }}</a>@endforeach</nav>
    @if($residentes->isEmpty())<x-ui.empty-state title="No hay residentes en esta consulta" description="Prueba otro nombre o restablece los filtros. El ingreso de un residente se realiza mediante admisión formal." />
    @elseif($vista === 'tarjetas')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" role="list" aria-label="Residentes de la consulta">
        @foreach($residentes as $residente)
        <x-ui.resident-card :resident="$residente" variant="management" :show-location="$verAlojamiento" :location="$verAlojamiento ? null : 'Consulta restringida'" primary-label="Ver ficha" :primary-href="route('admin.residentes.show', $residente)"><x-slot:status><span class="rm-badge">{{ str_replace('_', ' ', $residente->estado) }}</span></x-slot:status></x-ui.resident-card>
        @endforeach
    </div>
    @else
    <x-ui.section-card title="Residentes de la consulta" icon="ph-table"><div class="rm-table-scroll"><table class="rm-table"><caption class="sr-only">Identidad y situación administrativa</caption><thead><tr><th scope="col">Residente</th><th scope="col">Edad</th><th scope="col">Ubicación</th><th scope="col">Estado administrativo</th><th scope="col">Acción</th></tr></thead><tbody>@foreach($residentes as $residente)<tr><th scope="row">{{ $residente->nombre_completo }}</th><td>{{ $residente->fecha_nacimiento ? $residente->fecha_nacimiento->age.' años' : 'Sin registrar' }}</td><td>@if($verAlojamiento){{ $residente->ocupacionActiva?->cama?->habitacion?->codigo ?: 'Sin alojamiento registrado' }}@if($residente->ocupacionActiva?->cama) · {{ $residente->ocupacionActiva->cama->codigo }}@endif @else Consulta restringida @endif</td><td>{{ str_replace('_', ' ', $residente->estado) }}</td><td><a class="rm-btn-secondary" href="{{ route('admin.residentes.show', $residente) }}">Ver ficha<span class="sr-only"> de {{ $residente->nombre_completo }}</span></a></td></tr>@endforeach</tbody></table></div></x-ui.section-card>
    @endif
    <x-ui.paginacion :paginator="$residentes" label="residentes" />
</div>
</x-sistema-layout>
