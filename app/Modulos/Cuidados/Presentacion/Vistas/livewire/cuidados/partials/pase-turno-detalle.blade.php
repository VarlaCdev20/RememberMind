@php($legible = fn ($texto) => \App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($texto))
<section class="rm-handoff__identity" aria-label="Residente y relevo">
    <div><span class="rm-caption">Residente</span><h3>{{ $paseSeleccionado->residente?->nombre_completo ?? 'Residente no disponible' }}</h3><p>{{ $paseSeleccionado->residente?->ubicacion_formateada ?? 'Ubicación no disponible' }}</p></div>
    <x-ui.status-badge :estado="$paseSeleccionado->estado" />
</section>
<dl class="rm-handoff__facts">
    <div><dt>Profesional saliente</dt><dd>{{ $paseSeleccionado->personalSaliente?->usuario?->name ?? 'Profesional no disponible' }}</dd></div>
    <div><dt>{{ $paseSeleccionado->fecha_hora_recepcion ? 'Recibido por' : 'Receptor designado' }}</dt><dd>{{ $paseSeleccionado->personalEntrante?->usuario?->name ?? 'Pendiente de asignación' }}</dd></div>
    <div><dt>Jornada saliente</dt><dd>{{ $paseSeleccionado->jornadaSaliente?->turno?->nombre ?? 'No disponible' }} · {{ $paseSeleccionado->jornadaSaliente?->fecha_jornada?->format('d/m/Y') }}</dd></div>
    <div><dt>Jornada entrante</dt><dd>{{ $paseSeleccionado->jornadaEntrante?->turno?->nombre ?? 'No disponible' }} · {{ $paseSeleccionado->jornadaEntrante?->fecha_jornada?->format('d/m/Y') }}</dd></div>
    <div><dt>Registro / entrega</dt><dd>{{ $paseSeleccionado->fecha_hora?->format('d/m/Y H:i') ?? 'Fecha no disponible' }}</dd></div>
    @if($paseSeleccionado->fecha_hora_recepcion)<div><dt>Recepción confirmada</dt><dd>{{ $paseSeleccionado->fecha_hora_recepcion->format('d/m/Y H:i') }}</dd></div>@endif
</dl>
@foreach(['estado_general' => 'Estado general descrito', 'resumen' => 'Resumen de la jornada', 'pendientes' => 'Pendientes comunicados', 'vigilancia' => 'Puntos de vigilancia', 'recomendacion' => 'Recomendaciones de cuidado'] as $campo => $titulo)
    <section class="rm-card rm-handoff__note"><h3>{{ $titulo }}</h3><p>{{ $campo === 'pendientes' ? (\App\Modulos\Cuidados\Presentacion\Formato\TextoPaseTurno::pendientes($paseSeleccionado->$campo) ?: 'Sin información registrada.') : ($legible($paseSeleccionado->$campo) ?: 'Sin información registrada.') }}</p></section>
@endforeach
@if($paseSeleccionado->observacion_recepcion)<section class="rm-card rm-handoff__note"><h3>Observación de recepción</h3><p>{{ $legible($paseSeleccionado->observacion_recepcion) }}</p></section>@endif
