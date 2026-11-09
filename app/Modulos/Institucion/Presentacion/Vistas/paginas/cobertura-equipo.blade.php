@inject('visibilidad', 'App\Modulos\Identidad\Presentacion\Navegacion\VisibilidadNavegacion')
@php
    $puedeVerPlanificacion = $visibilidad->puedeVerRuta('admin.turnos-asignaciones.index');
    $contextoPlanificacion = ['fecha' => $fecha, 'area' => $filtros['area'] ?? null, 'turno' => $filtros['turno'] ?? null];
@endphp

<x-sistema-layout>
    <div class="rm-management-workspace rm-coverage space-y-4">
        <x-ui.collection-header title="Cobertura del equipo" subtitle="Consulta las asignaciones registradas por fecha, área y turno" icon="ph-users-three">
            @if($puedeVerPlanificacion)
                <x-slot:actions><a class="rm-btn-secondary" href="{{ route('admin.turnos-asignaciones.index', $contextoPlanificacion) }}" wire:navigate>Ver planificación</a></x-slot:actions>
            @endif
        </x-ui.collection-header>

        <x-ui.filter-bar as="form" class="rm-coverage__filters" method="GET" :action="route('admin.cobertura-equipo.index')" x-data="{ modo: $el.elements.namedItem('modo').value }">
            <x-ui.field label="Consultar" for="cobertura-modo" error="modo">
                <select class="rm-input" id="cobertura-modo" name="modo" x-model="modo" @error('modo') aria-invalid="true" aria-describedby="cobertura-modo-error" @enderror>
                    <option value="fecha" @selected(! $vigente)>Por fecha</option>
                    <option value="vigente" @selected($vigente)>Horario vigente</option>
                </select>
            </x-ui.field>
            <x-ui.field label="Fecha de jornada" for="cobertura-fecha" error="fecha">
                <input class="rm-input" id="cobertura-fecha" name="fecha" type="date" value="{{ $fecha }}" :disabled="modo === 'vigente'" @error('fecha') aria-invalid="true" aria-describedby="cobertura-fecha-error" @enderror>
            </x-ui.field>
            <x-ui.field label="Área" for="cobertura-area" error="area">
                <select class="rm-input" id="cobertura-area" name="area" @error('area') aria-invalid="true" aria-describedby="cobertura-area-error" @enderror>
                    <option value="">Todas las áreas</option>
                    @foreach($areas as $area)<option value="{{ $area->cod_area }}" @selected(($filtros['area'] ?? '') === $area->cod_area)>{{ $area->nombre }}</option>@endforeach
                </select>
            </x-ui.field>
            <x-ui.field label="Turno" for="cobertura-turno" error="turno">
                <select class="rm-input" id="cobertura-turno" name="turno" @error('turno') aria-invalid="true" aria-describedby="cobertura-turno-error" @enderror>
                    <option value="">Todos los turnos</option>
                    @foreach($turnos as $turno)<option value="{{ $turno->cod_turno }}" @selected(($filtros['turno'] ?? '') === $turno->cod_turno)>{{ $turno->nombre }}</option>@endforeach
                </select>
            </x-ui.field>
            <x-ui.field label="Estado de jornada" for="cobertura-estado" error="estado">
                <select class="rm-input" id="cobertura-estado" name="estado" :disabled="modo === 'vigente'" @error('estado') aria-invalid="true" aria-describedby="cobertura-estado-error" @enderror>
                    <option value="" x-text="modo === 'vigente' ? 'Jornadas abiertas' : 'Todos los estados'">{{ $vigente ? 'Jornadas abiertas' : 'Todos los estados' }}</option>
                    @foreach($estadosJornada as $estado)<option value="{{ $estado }}" @selected(($filtros['estado'] ?? '') === $estado)>{{ ucfirst($estado) }}</option>@endforeach
                </select>
            </x-ui.field>
            <div class="rm-coverage__actions">
                <button class="rm-btn-primary" type="submit">Aplicar</button>
                <a class="rm-btn-secondary" href="{{ route('admin.cobertura-equipo.index') }}">Limpiar</a>
            </div>
        </x-ui.filter-bar>

        @unless($errors->any())
            <x-ui.section-card title="Equipo registrado" icon="ph-table">
                <p class="rm-help mb-2">
                    {{ $vigente ? 'Jornadas abiertas con horario vigente al '.$ahora->format('d/m/Y H:i').'.' : 'Fecha de jornada '.\Carbon\Carbon::parse($fecha)->format('d/m/Y').'.' }}
                    Las asignaciones registradas no acreditan asistencia ni suficiencia de personal.
                </p>
                @if($filas)
                    <p class="rm-help mb-2 md:hidden" id="cobertura-desplazamiento">Desliza la tabla para consultar todas las columnas.</p>
                    <x-ui.table-surface class="rm-coverage__table" role="region" tabindex="0" aria-label="Asignaciones del equipo">
                        <table class="rm-table">
                            <caption class="sr-only">{{ $vigente ? 'Asignaciones en la ventana vigente' : 'Asignaciones de la jornada '.$fecha }}</caption>
                            <thead><tr><th scope="col">Área</th><th scope="col">Turno y horario</th><th scope="col">Persona y función</th><th scope="col">Situación</th></tr></thead>
                            <tbody>
                                @foreach($filas as $fila)
                                    <tr>
                                        <td>{{ $fila['area'] }}</td>
                                        <td><strong>{{ $fila['turno'] }}</strong><small class="block">{{ $fila['inicio'] }} → {{ $fila['fin'] }}</small></td>
                                        <td><strong>{{ $fila['persona'] }}</strong><small class="block">{{ $fila['funcion_asignada'] ?: 'Función sin registrar' }}</small></td>
                                        <td>
                                            {{ $fila['situacion'] }}<small class="block">Jornada {{ mb_strtolower($fila['estado_jornada']) }}</small>
                                            <details class="rm-coverage__detail">
                                                <summary aria-label="Ver detalle de {{ $fila['persona'] }}">Ver detalle</summary>
                                                <dl>
                                                    <div><dt>Fecha de jornada</dt><dd>{{ $fila['fecha_jornada_texto'] }}</dd></div>
                                                    <div><dt>Profesión</dt><dd>{{ $fila['profesion'] ?: 'Sin registrar' }}</dd></div>
                                                    <div><dt>Tipo de asignación</dt><dd>{{ $fila['tipo_asignacion'] ?: 'Sin registrar' }}</dd></div>
                                                    <div><dt>Fuente</dt><dd>Asignación de personal vinculada a una jornada</dd></div>
                                                </dl>
                                                @if($puedeVerPlanificacion)
                                                    <a class="rm-btn-secondary" href="{{ route('admin.turnos-asignaciones.index', ['fecha' => $fila['fecha_jornada'], 'area' => $fila['cod_area'], 'turno' => $fila['cod_turno']]) }}" wire:navigate>Ver en planificación</a>
                                                @endif
                                            </details>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-ui.table-surface>
                @else
                    <x-ui.empty-state titulo="Sin asignaciones en esta consulta" texto="Prueba otra fecha o ajusta los filtros." icono="ph-calendar-blank" />
                @endif
            </x-ui.section-card>
        @endunless
    </div>
</x-sistema-layout>
