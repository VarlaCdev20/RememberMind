@can('atenciones.crear')
<details class="rounded-lg border border-borde bg-fondo-panel p-4"><summary class="cursor-pointer font-bold">Registrar atención</summary>
<form method="POST" action="{{ route('admin.adultos-mayores.atenciones.store', $adulto_mayor) }}" class="mt-4 space-y-3">@csrf
<label>Fecha<x-input type="date" name="fecha" value="{{ old('fecha', today()->format('Y-m-d')) }}" required /></label>
<label>Hora<x-input type="time" name="hora" value="{{ old('hora', now()->format('H:i')) }}" required /></label>
<label>Tipo<select name="cod_tipo_aten" required><option value="">Seleccione</option>@foreach($tiposAtencion as $tipo)<option value="{{ $tipo->cod_tipo_aten }}">{{ $tipo->nombre }}</option>@endforeach</select></label>
<label>Estado<select name="estado">@foreach(['PENDIENTE','REALIZADA','FINALIZADA','CANCELADA'] as $estado)<option>{{ $estado }}</option>@endforeach</select></label>
<label class="block">Observaciones<textarea class="block w-full rounded-lg" name="obs" maxlength="1000">{{ old('obs') }}</textarea></label>
<x-button>Registrar</x-button></form></details>
@endcan
