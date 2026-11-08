<div class="rm-management-workspace space-y-4">
    @if(!$mostrarFormulario)@error('estado')<p class="rm-alert rm-alert-danger" role="alert">{{ $message }}</p>@enderror@endif
    @include('institucion::livewire.identidad.partials.areas-coleccion', ['modoEquipo'=>true])
    @if($mostrarFicha && $areaActual)
    <x-ui.modal-livewire id="gerencia-area-equipo" wire:model="mostrarFicha" :title="$areaActual->nombre" subtitle="Equipo por jornada; no es una plantilla permanente ni acredita asistencia" icon="ph-users-three" max-width="lg" close-method="cerrarFicha">
        <p>{{ $areaActual->descripcion ?: 'Sin descripción registrada.' }}</p>
        <x-ui.field label="Fecha del equipo" for="gerencia-area-fecha"><input id="gerencia-area-fecha" class="rm-input" type="date" wire:model.live="fechaEquipo"></x-ui.field>
        @if($equipo === null)<x-ui.callout title="Consulta de equipo restringida" variant="info">Necesitas los permisos de jornadas y asignaciones para consultar el equipo.</x-ui.callout>
        @elseif(!$equipo['filas'])<x-ui.empty-state title="Sin asignaciones para esta fecha" description="No demuestra por sí solo falta de personal o necesidad de cobertura." />
        @else<div class="rm-table-scroll"><table class="rm-table"><caption class="sr-only">Asignaciones de la fecha seleccionada</caption><thead><tr><th scope="col">Persona y función</th><th scope="col">Jornada y turno</th><th scope="col">Situación</th></tr></thead><tbody>@foreach($equipo['filas'] as $fila)<tr><th scope="row">{{ $fila['persona'] }}<small class="block">{{ $fila['funcion'] }}</small></th><td>{{ $fila['turno'] }}<small class="block">{{ $fila['inicio'] }} → {{ $fila['fin'] }}</small></td><td>{{ $fila['situacion'] }}</td></tr>@endforeach</tbody></table></div>@endif
        <x-slot:footer><div class="rm-management-actions"><a class="rm-btn-secondary" href="{{ route('admin.cobertura-equipo.index', ['area'=>$areaActual->getKey(),'fecha'=>$fechaEquipo]) }}">Ver cobertura completa</a><button type="button" class="rm-btn-secondary" wire:click="cerrarFicha">Volver a áreas</button></div></x-slot:footer>
    </x-ui.modal-livewire>
    @endif
    @include('institucion::livewire.identidad.partials.areas-formulario', ['modoEquipo' => true])
</div>
