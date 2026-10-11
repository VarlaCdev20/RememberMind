@if($campo === 'estado')
    <x-ui.status-badge :estado="$valor" />
@elseif($modulo === 'alertas' && $campo === 'detalle')
    <x-ui.status-badge :estado="$valor" :variant="match($valor) { 'CRITICA', 'ALTA' => 'danger', 'MEDIA' => 'warning', 'BAJA' => 'info', default => 'neutral' }" />
@elseif(in_array($campo, ['principal', 'emergencia', 'requiere_medico', 'requiere_derivacion'], true))
    {{ $valor === null ? 'Sin registrar' : ($valor ? 'Sí' : 'No') }}
@elseif(in_array($campo, ['fecha', 'fecha_fin', 'programada', 'ingreso', 'salida', 'validacion', 'dia_jornada'], true))
    {{ $valor ? \Carbon\Carbon::parse($valor)->format($campo === 'dia_jornada' || in_array($modulo, ['jornadas', 'documentacion'], true) ? 'd/m/Y' : 'd/m/Y H:i') : 'Sin registrar' }}
@else
    {{ $valor === null || $valor === '' ? 'Sin registrar' : $valor }}
@endif
