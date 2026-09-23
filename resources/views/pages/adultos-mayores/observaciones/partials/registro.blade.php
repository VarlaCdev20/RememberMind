<article class="rounded-lg border border-borde bg-fondo-panel p-4">
<h2 class="font-bold">{{ $observacion->tipo_obs }} — {{ $observacion->trashed() ? 'ANULADA' : 'VIGENTE' }}</h2>
<p>{{ $observacion->fecha?->format('d/m/Y') }}</p><p class="whitespace-pre-wrap">{{ $observacion->descripcion }}</p>
@if(!$observacion->trashed())
@can('observaciones.editar')
<details><summary class="cursor-pointer">Corregir nota</summary>
<form method="POST" action="{{ route('admin.adultos-mayores.observaciones.update', [$adulto_mayor, $observacion]) }}" class="space-y-3">@csrf @method('PATCH')
<label>Tipo<x-input name="tipo_obs" value="{{ $observacion->tipo_obs }}" required /></label>
<label class="block">Descripción<textarea class="block w-full" name="descripcion" required>{{ $observacion->descripcion }}</textarea></label><x-button>Guardar corrección</x-button></form></details>
@endcan
@can('observaciones.anular')<form method="POST" action="{{ route('admin.adultos-mayores.observaciones.destroy', [$adulto_mayor, $observacion]) }}" onsubmit="return confirm('¿Anular esta nota conservando el historial?')">@csrf @method('DELETE')<x-danger-button type="submit">Anular</x-danger-button></form>@endcan
@else
@can('observaciones.editar')<form method="POST" action="{{ route('admin.adultos-mayores.observaciones.restore', [$adulto_mayor, $observacion->cod_obs_adul]) }}">@csrf @method('PATCH')<x-secondary-button type="submit">Restaurar</x-secondary-button></form>@endcan
@endif
</article>
