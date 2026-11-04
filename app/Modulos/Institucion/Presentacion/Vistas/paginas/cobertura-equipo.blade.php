<x-sistema-layout>
<div class="rm-management-workspace space-y-4">
    <x-ui.collection-header title="Cobertura del equipo" subtitle="Consulta las asignaciones registradas por fecha, área y turno" icon="ph-users-three"><x-slot:actions><a class="rm-btn-secondary" href="{{ route('admin.turnos-asignaciones.index') }}" wire:navigate>Ver planificación</a></x-slot:actions></x-ui.collection-header>
    <form class="rm-filter-bar rm-management-tabs" method="GET" action="{{ route('admin.cobertura-equipo.index') }}">
        <x-ui.field label="Fecha de jornada" for="cobertura-fecha"><input class="rm-input" id="cobertura-fecha" name="fecha" type="date" value="{{ $fecha }}">@error('fecha')<p role="alert">{{ $message }}</p>@enderror</x-ui.field>
        <x-ui.field label="Área" for="cobertura-area"><select class="rm-input" id="cobertura-area" name="area"><option value="">Todas las áreas</option>@foreach($areas as $area)<option value="{{ $area->cod_area }}" @selected(($filtros['area'] ?? '') === $area->cod_area)>{{ $area->nombre }}</option>@endforeach</select></x-ui.field>
        <x-ui.field label="Turno" for="cobertura-turno"><select class="rm-input" id="cobertura-turno" name="turno"><option value="">Todos los turnos</option>@foreach($turnos as $turno)<option value="{{ $turno->cod_turno }}" @selected(($filtros['turno'] ?? '') === $turno->cod_turno)>{{ $turno->nombre }}</option>@endforeach</select></x-ui.field>
        <button class="rm-btn-primary" type="submit">Aplicar</button><a class="rm-btn-secondary" href="{{ route('admin.cobertura-equipo.index') }}">Limpiar</a>
    </form>
    <x-ui.callout variant="info" title="Asignación y asistencia son distintas"><p>Las filas proceden de asignaciones guardadas para la fecha de jornada. No acreditan presencia física ni suficiencia de dotación. Los turnos nocturnos muestran su fecha de finalización.</p></x-ui.callout>
    <x-ui.section-card title="Equipo registrado" icon="ph-table">
        @if($filas)<div class="rm-table-scroll"><table class="rm-table"><caption class="sr-only">Asignaciones de la jornada {{ $fecha }}</caption><thead><tr><th scope="col">Área</th><th scope="col">Turno y horario</th><th scope="col">Persona y función</th><th scope="col">Situación</th></tr></thead><tbody>@foreach($filas as $fila)<tr><td>{{ $fila['area'] }}</td><td><strong>{{ $fila['turno'] }}</strong><small class="block">{{ $fila['inicio'] }} → {{ $fila['fin'] }}</small></td><td><strong>{{ $fila['persona'] }}</strong><small class="block">{{ $fila['funcion'] }}</small></td><td>{{ $fila['situacion'] }}<small class="block">Jornada {{ mb_strtolower($fila['estado_jornada']) }}</small></td></tr>@endforeach</tbody></table></div>@else<x-ui.empty-state titulo="Sin asignaciones en esta consulta" texto="Prueba otra fecha o filtro. Esto no demuestra por sí solo falta de personal." icono="ph-calendar-blank" />@endif
    </x-ui.section-card>
</div>
</x-sistema-layout>
