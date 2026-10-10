<article class="rounded-lg border border-borde bg-fondo-panel p-4">
<h2 class="font-bold">{{ $atencion->tipoAtencion?->nombre }} — {{ $atencion->estado }}</h2>
<p>{{ \Carbon\Carbon::parse($atencion->fecha)->format('d/m/Y') }} {{ $atencion->hora }}</p><p class="whitespace-pre-wrap">{{ $atencion->obs }}</p>
@if($atencion->estado !== 'ANULADO')
@can('atenciones.editar')
<details><summary class="cursor-pointer">Editar atención</summary>
<form method="POST" action="{{ route('admin.adultos-mayores.atenciones.update', [$adulto_mayor, $atencion]) }}" class="space-y-3">@csrf @method('PATCH')
<label>Estado<select name="estado">@foreach(['PENDIENTE','REALIZADA','FINALIZADA','CANCELADA'] as $estado)<option @selected($estado === $atencion->estado)>{{ $estado }}</option>@endforeach</select></label>
<label class="block">Observaciones<textarea class="block w-full" name="obs">{{ $atencion->obs }}</textarea></label><x-button>Guardar</x-button></form></details>
@endcan
@can('atenciones.anular')<form method="POST" action="{{ route('admin.adultos-mayores.atenciones.destroy', [$adulto_mayor, $atencion]) }}" onsubmit="return confirm('¿Anular esta atención conservando el historial?')">@csrf @method('DELETE')<x-danger-button type="submit">Anular</x-danger-button></form>@endcan
@else
@can('atenciones.editar')<form method="POST" action="{{ route('admin.adultos-mayores.atenciones.restore', [$adulto_mayor, $atencion]) }}">@csrf @method('PATCH')<x-secondary-button type="submit">Restaurar como pendiente</x-secondary-button></form>@endcan
@endif
</article>
