<div class="space-y-5">
<h1 class="text-2xl font-bold text-titulo">Asignaciones de turno y ocupación</h1>
<x-validation-errors />@if(session('mensaje'))<p role="status">{{ session('mensaje') }}</p>@endif
@can('turnos.asignar')<x-button wire:click="abrirCrear">Asignar residente</x-button>@endcan
<div class="flex gap-3"><label>Buscar<x-input wire:model.live.debounce.300ms="search" /></label>
<label>Estado<select wire:model.live="filtroEstado"><option value="">Todos</option><option>ACTIVA</option><option>FINALIZADA</option><option>ANULADA</option></select></label></div>
@forelse($asignaciones as $asignacion)
<article class="rounded-lg border border-borde bg-fondo-panel p-4" wire:key="asignacion-{{ $asignacion->cod_asignacion }}">
<a class="font-bold underline" href="{{ route('admin.adultos-mayores.show', $asignacion->cod_residente) }}">{{ $asignacion->residente?->nombres }} {{ $asignacion->residente?->apellido_paterno }}</a>
<p>{{ $asignacion->jornada?->turno?->nombre }} — {{ $asignacion->personal?->usuario?->name }}</p>
<p>Habitación {{ $asignacion->residente?->ocupacionActiva?->cama?->habitacion?->codigo ?? 'Sin habitación' }} / cama {{ $asignacion->residente?->ocupacionActiva?->cama?->codigo ?? 'Sin cama' }}</p>
<p>{{ $asignacion->jornada?->fecha_jornada?->format('d/m/Y') }} — {{ $asignacion->estado }}</p>
<p>{{ $asignacion->observacion }}</p>
@if($asignacion->estado === 'ACTIVA') @can('turnos.finalizar')<x-secondary-button wire:click="finalizarAsignacion('{{ $asignacion->cod_asignacion }}')" wire:confirm="¿Finalizar esta asignación?">Finalizar</x-secondary-button>@endcan @endif
</article>
@empty<p>No hay asignaciones.</p>@endforelse
{{ $asignaciones->links() }}
<x-dialog-modal wire:model="modalForm">
<x-slot name="title">Asignar residente a turno y cama</x-slot>
<x-slot name="content"><div class="space-y-3"><x-validation-errors />
<label class="block">Adulto mayor<select wire:model="codResidente" class="block w-full"><option value="">Seleccione</option>@foreach($adultos as $adulto)<option value="{{ $adulto->cod_residente }}">{{ $adulto->nombres }} {{ $adulto->apellido_paterno }}</option>@endforeach</select></label>
<label class="block">Turno<select wire:model="codTurno"><option value="">Seleccione</option>@foreach($turnos as $turno)<option value="{{ $turno->cod_turno }}">{{ $turno->nombre }}</option>@endforeach</select></label>
<label class="block">Responsable<select wire:model="codEnfermero"><option value="">Seleccione</option>@foreach($enfermeros as $u)<option value="{{ $u->cod_usuario }}">{{ $u->name }}</option>@endforeach</select></label>
<p class="text-sm text-texto-secundario">La habitación y cama provienen de la admisión formal y no se modifican desde esta pantalla.</p>
<label class="block">Fecha de inicio<x-input type="date" wire:model="fechaInicio" /></label>
<label class="block">Supervisión<select wire:model="nivelSupervision">@foreach(['MINIMO','ESTANDAR','INTENSIVO','CRITICO'] as $n)<option>{{ $n }}</option>@endforeach</select></label>
<label class="block">Motivo<textarea class="block w-full" wire:model="motivoAsignacion"></textarea></label>
</div></x-slot>
<x-slot name="footer"><x-secondary-button wire:click="cerrarModales">Cancelar</x-secondary-button><x-button wire:click="guardar" wire:loading.attr="disabled">Guardar asignación</x-button></x-slot>
</x-dialog-modal>
</div>
