<div class="rm-management-workspace space-y-4">
    @error('estado')<p class="rm-alert rm-alert-danger" role="alert">{{ $message }}</p>@enderror
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
    @if($mostrarFormulario)
    <x-ui.modal-livewire id="gerencia-area-formulario" wire:model="mostrarFormulario" :title="$isEdit ? 'Editar datos del área' : 'Registrar área'" max-width="md" close-method="cerrarFormulario" :dismiss-on-escape="false">
        <form wire:submit="guardarArea" class="space-y-4">
            <x-ui.field label="Nombre del área" for="gerencia-area-nombre"><input id="gerencia-area-nombre" class="rm-input" wire:model="nombre" maxlength="80" required>@error('nombre')<p role="alert">{{ $message }}</p>@enderror</x-ui.field>
            <x-ui.field label="Descripción funcional (opcional)" for="gerencia-area-descripcion"><textarea id="gerencia-area-descripcion" class="rm-input" wire:model="descripcion" rows="3"></textarea>@error('descripcion')<p role="alert">{{ $message }}</p>@enderror</x-ui.field>
            <p class="rm-help">Estado actual: {{ in_array($estado,['ACTIVO','ACTIVA'],true) ? 'Activa' : 'Inactiva' }}. La edición de datos conserva la situación del área.</p>
            <div class="rm-management-actions"><button type="submit" class="rm-btn-primary" wire:loading.attr="disabled">Guardar datos</button><button type="button" class="rm-btn-secondary" wire:click="cerrarFormulario" wire:confirm="¿Descartar los cambios sin guardar?">Cancelar</button></div>
            <p wire:loading wire:target="guardarArea" role="status">Guardando el área…</p>
        </form>
    </x-ui.modal-livewire>
    @endif
</div>
