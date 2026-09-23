@can('observaciones.crear')
<details class="rounded-lg border border-borde bg-fondo-panel p-4"><summary class="cursor-pointer font-bold">Registrar nota</summary>
<form method="POST" action="{{ route('admin.adultos-mayores.observaciones.store', $adulto_mayor) }}" class="mt-4 space-y-3">@csrf
<input type="hidden" name="cod_est_adul" value="{{ $adulto_mayor->cod_est_adul }}">
<label>Fecha<x-input type="date" name="fecha" value="{{ old('fecha', today()->format('Y-m-d')) }}" required /></label>
<label>Tipo<x-input name="tipo_obs" value="{{ old('tipo_obs', 'GENERAL') }}" maxlength="80" required /></label>
<label class="block">Descripción<textarea class="block w-full rounded-lg" name="descripcion" minlength="5" maxlength="1000" required>{{ old('descripcion') }}</textarea></label>
<x-button>Registrar</x-button></form></details>
@endcan
