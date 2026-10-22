<x-ui.modal-livewire wire:model="significadoAbierto" title="¿Qué significa este resultado?" subtitle="Explicación de la evaluación cognitiva" icon="ph-book-open" maxWidth="md" surface="reading" closeMethod="cerrarSignificado" :showValidation="false">
    <p class="rm-orion-note">{{ $residente->nombre_completo }} · Resultado disponible de {{ mb_strtolower($consultaEnfermeria['area_principal']) }}</p>
    <div class="rm-orion-reading"><section class="rm-orion-reading-summary"><h3>En palabras sencillas</h3><p>{{ $consultaEnfermeria['titulo'] }}</p><p>{{ $consultaEnfermeria['resumen'] }}</p></section>
        <section class="rm-orion-reading-support"><h3>Qué información lo respalda</h3>
            @forelse(array_slice($fuentesResultado, 0, 3) as $f)<article><strong>{{ $f['nombre'] }}</strong><p>{{ $f['fuente'] }} · {{ $f['fecha'] ?? 'Fecha no conservada' }}</p><p>{{ $texto($f['uso']) }}</p>@unless($simulacion)<button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirRegistro(@js($f['id']), @js($f['area']))">Ver evidencia</button>@endunless</article>
            @empty<p>No se encontraron evidencias conservadas para esta área.</p>@endforelse
        </section>
        <section class="rm-orion-reading-limits"><h3>Qué no puede concluirse</h3><p>{{ $texto($informe['limite_global']) }}</p><ul>@foreach($informe['areas']['COG-MEM']['limites'] as $limite)<li>{{ $texto($limite) }}</li>@endforeach</ul></section>
        @if($simulacion)
        <details class="rm-expert-source"><summary>Requisitos considerados en la evaluación</summary><p>Información considerada hasta: {{ $narrativa['fecha_corte'] ?? 'Fecha no conservada' }}</p><dl class="rm-orion-definition">@foreach($narrativa['requisitos'] ?? [] as $requisito)<div><dt>{{ $requisito['nombre'] }} · {{ $requisito['estado'] }}</dt><dd>{{ $texto($requisito['razon']) }}</dd>@foreach($requisito['fuentes'] as $f)<dd>{{ $texto($f) }}</dd>@endforeach</div>@endforeach</dl></details>
        @endif
        <section><h3>Información de la evaluación</h3><dl class="rm-cognitive-meta"><div><dt>Área cognitiva</dt><dd>{{ $consultaEnfermeria['area_principal'] }}</dd></div><div><dt>Fecha</dt><dd>{{ $datos['evaluacion']['fecha'] ?? 'Sin evaluación' }}</dd></div><div><dt>Versión</dt><dd>{{ $datos['evaluacion']['version'] ?? 'No disponible' }}</dd></div><div><dt>Estado</dt><dd>{{ $consultaEnfermeria['estado_principal'] }}</dd></div><div><dt>Cobertura</dt><dd>{{ count($fuentesResultado) }} fuentes conservadas en el área</dd></div></dl></section>
    </div>
    <x-slot:footer><button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirConsultaResultado('registro')">Ver evidencias</button><button type="button" class="rm-btn rm-btn-primary" wire:click="cerrarSignificado">Entendido</button></x-slot:footer>
</x-ui.modal-livewire>

<x-ui.modal-livewire wire:model="detalleAreaAbierto" :title="'Detalle de '.$informe['actual']['nombre']" subtitle="Subcomponentes y resultados disponibles" maxWidth="lg" surface="reading" closeMethod="cerrarDetalleArea" :showValidation="false">
    <p class="rm-orion-note">{{ $residente->nombre_completo }} · {{ $informe['actual']['estado'] }} · {{ $datos['evaluacion']['fecha'] ?? 'Sin evaluación histórica' }}</p>
    <p>Los subcomponentes y mediciones consultados al corte actual no completan ni sustituyen los valores originales de una evaluación histórica.</p>
    @if($errorInstrumental)<p role="alert">No fue posible verificar el desglose instrumental. Cierra esta ventana y vuelve a intentarlo.</p>@else
    @forelse($consultaEnfermeria['componentes'][$criterio] ?? [] as $c)
        <div class="rm-cognitive-accordion" x-data="{ expanded: false }" wire:key="area-component-{{ $criterio }}-{{ $c['id'] }}"><h3><button type="button" :aria-expanded="expanded" aria-controls="component-{{ $c['id'] }}" @click="expanded = !expanded"><span>{{ $c['nombre'] }}</span><small>{{ $c['estado'] }}</small><i class="ph ph-caret-down" :class="{ 'is-expanded': expanded }" aria-hidden="true"></i></button></h3>
            <div id="component-{{ $c['id'] }}" x-show="expanded" x-collapse x-cloak><div class="rm-orion-reading"><p>{{ $c['definicion'] }}</p>
                @forelse($c['mediciones'] as $m)<dl class="rm-cognitive-meta"><div><dt>Fuente</dt><dd>Aplicación instrumental actual</dd></div><div><dt>Fecha</dt><dd>{{ $m['fecha'] }}</dd></div><div><dt>Resultado</dt><dd>{{ $m['valor'] !== null ? round($m['valor'], 3).' · '.$m['unidad'] : 'Sin valor normalizado disponible' }}</dd></div><div><dt>Método</dt><dd>{{ $m['metodo'] }}</dd></div><div><dt>Referencia aplicada</dt><dd>{{ is_string($m['referencia']) ? $m['referencia'] : 'Referencia estructurada del instrumento' }}</dd></div></dl>@empty<p>Sin medición instrumental disponible para este componente.</p>@endforelse
                @foreach($c['observaciones'] as $o)<p>{{ $o['texto'] }} · {{ $o['fecha'] }} · {{ $o['autor'] }}</p>@endforeach
                <p class="rm-orion-note">{{ $c['limite'] }}</p>@if($c['mediciones'])<button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirRegistroComponente(@js($c['id']))">Ver registro asociado</button>@endif
            </div></div>
        </div>
    @empty<p class="rm-orion-empty">No hay un desglose de subcomponentes autorizado disponible para esta área. La ausencia de mediciones no equivale a un resultado conservado.</p>@endforelse
    @endif
    <x-slot:footer><button type="button" class="rm-btn rm-btn-primary" wire:click="cerrarDetalleArea">Volver al informe</button></x-slot:footer>
</x-ui.modal-livewire>

@include('apoyodecision::livewire.medico.resultados-experto.enfermeria-evolucion')

<x-ui.drawer-livewire wire:model="consultaAbierta" title="Registros y evidencias" subtitle="Información disponible para el área seleccionada" :badge="false" icon="ph-notebook" size="lg" surface="reading" closeMethod="cerrarConsulta">
    <p class="rm-orion-note">{{ $residente->nombre_completo }} · {{ $informe['actual']['nombre'] }} · {{ $datos['evaluacion']['fecha'] ?? 'Sin evaluación histórica' }}</p>
    <div class="rm-orion-tabs" role="group" aria-label="Secciones de consulta">@foreach(['interpretacion' => 'Interpretación', 'registro' => 'Registros', 'cuidados' => 'Cuidados'] as $clave => $label)<button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirConsulta(@js($clave))" aria-pressed="{{ $pestanaConsulta === $clave ? 'true' : 'false' }}">{{ $label }}</button>@endforeach</div>
    @if($pestanaConsulta === 'interpretacion')<div class="rm-orion-reading"><h3>{{ $informe['actual']['estado'] }}</h3><p>{{ $texto($informe['actual']['significado']) }}</p><p>{{ $texto($informe['actual']['por_que']) }}</p><p class="rm-orion-note">{{ $texto($informe['limite_global']) }}</p></div>
    @elseif($pestanaConsulta === 'registro')
        @if($errorInstrumental)<p role="alert">No fue posible consultar las mediciones instrumentales. No se confirma su ausencia.</p>
        @elseif($registroComponente)<section class="rm-expert-source"><h3 class="rm-expert-section-title">{{ $registroComponente['nombre'] }}</h3><p>Dato fuente actual. No sustituye el valor original de una inferencia histórica.</p>
            @foreach($registroComponente['mediciones'] as $medicion)<dl class="rm-cognitive-meta">@foreach(['Instrumento' => $medicion['instrumento'], 'Versión' => $medicion['version'], 'Fecha' => $medicion['fecha'], 'Profesional' => $medicion['autor'], 'Resultado normalizado' => $medicion['valor'] !== null ? round($medicion['valor'], 3).' · '.$medicion['unidad'] : 'No disponible', 'Valor bruto documentado' => $medicion['valor_bruto'], 'Referencia' => is_string($medicion['referencia']) ? $medicion['referencia'] : 'Referencia estructurada del instrumento'] as $nombre => $valor)<div><dt>{{ $nombre }}</dt><dd>{{ $valor ?? 'No registrado' }}</dd></div>@endforeach</dl>@endforeach
        </section>@endif
        @if($simulacion)<p class="rm-orion-note">Resultado de demostración. Sus referencias no se abren como registros clínicos actuales.</p>
            <h3 class="rm-expert-section-title">Información utilizada</h3>
            @forelse($narrativa['funciones'][$criterio]['fuentes_legibles'] ?? [] as $f)<article class="rm-expert-source"><h4>{{ $f['titulo'] }}</h4><p>{{ $f['fecha'] }}</p><ul>@foreach($f['datos'] as $dato)<li>{{ $dato }}</li>@endforeach</ul><p>{{ $texto($f['uso']) }}</p></article>@empty<p>No hay referencias conservadas para el área de esta demostración.</p>@endforelse
        @else @include('apoyodecision::livewire.medico.resultados-experto.evidencias') @endif
        <section class="rm-expert-source mt-4"><h3 class="rm-expert-section-title">Diagnóstico médico registrado</h3><p>Se presenta separado del resultado de la evaluación.</p>
            @if($puedeVerDiagnosticos)@forelse($diagnosticosMedicos as $diagnostico)<article><strong>{{ $diagnostico->nombre }}</strong><p>{{ $diagnostico->fecha_hora?->format('d/m/Y H:i') }} · {{ trim(($diagnostico->personal?->nombres ?? '').' '.($diagnostico->personal?->apellido_paterno ?? '')) }}</p></article>@empty<p>No hay diagnósticos médicos registrados.</p>@endforelse @else<p>No dispone de permiso para consultar diagnósticos médicos.</p>@endif
        </section>
        @if(!empty($datos['registros_cognitivos']))
        <section class="mt-4"><h3 class="rm-expert-section-title">Controles cognitivos registrados</h3><p>Observaciones actuales del expediente. No equivalen a un resultado experto ni completan una evaluación histórica.</p>
            @foreach($datos['registros_cognitivos'] as $control)<details class="rm-expert-source"><summary>{{ $control['fecha'] }} · {{ $control['autor'] }}</summary><dl class="rm-cognitive-meta">@foreach($control['mediciones'] as $medicion)<div><dt>{{ $medicion['nombre'] }}</dt><dd>{{ $medicion['valor'] }}</dd></div>@endforeach</dl><p>{{ $control['observacion'] ?: 'Sin observaciones adicionales.' }}</p></details>@endforeach
        </section>
        @endif
    @elseif($pestanaConsulta === 'cuidados')
        <h3 class="rm-expert-section-title">Plan de cuidados actual</h3><p>Esta información es de consulta. Para registrar la ejecución de un cuidado utiliza la agenda de tu turno.</p>
        @if($puedeVerCuidados)
            @php($todosCuidados = $cuidadosPendientesHoy->concat($otrosCuidadosVigentes))
            @forelse($todosCuidados->groupBy('estado') as $estado => $items)
                <section class="rm-orion-care-group" data-state="{{ $estado }}"><h4>{{ ['PENDIENTE' => 'Pendientes de hoy', 'REALIZADA' => 'Realizados hoy', 'NO_REALIZADA' => 'No realizados hoy', 'CANCELADA' => 'Cancelados hoy', 'SIN_OCURRENCIA_HOY' => 'Otros cuidados vigentes'][$estado] }}<span>{{ $items->count() }}</span></h4>
                    @foreach($items as $item)<article class="rm-orion-care-row"><div><strong>{{ $item['intervencion']->nombre }}</strong><p>{{ $item['intervencion']->descripcion }}</p><small>{{ $item['plan']->nombre }} · {{ $item['hora'] ?? 'Sin tarea programada hoy' }}</small>@if($estado === 'NO_REALIZADA')<p>Motivo: {{ $item['ejecucion']?->motivo_omision ?? 'No registrado' }}</p>@endif<button type="button" class="rm-btn rm-btn-secondary" wire:click="verCuidado(@js($item['clave']))">Ver detalle del cuidado</button></div></article>@endforeach
                </section>
            @empty<p class="rm-orion-empty">Este residente no tiene intervenciones activas disponibles para esta consulta.</p>@endforelse
            <a href="{{ route('admin.enfermeria.agenda', ['residente' => $codResidente]) }}" class="rm-btn rm-btn-primary rm-cognitive-agenda"><i class="ph ph-calendar" aria-hidden="true"></i>Ir a mi agenda de turno</a>
        @else<p>No dispone de autorización para consultar los cuidados.</p>@endif
    @endif
</x-ui.drawer-livewire>

@if($cuidadoDetalle)
<x-ui.drawer-livewire wire:model="cuidadoSeleccionado" title="Detalle del cuidado" :subtitle="$residente->nombre_completo" :badge="false" size="lg" surface="reading" closeMethod="cerrarCuidado">
    <div class="rm-orion-reading"><h3>{{ $cuidadoDetalle['intervencion']->nombre }}</h3><dl class="rm-orion-definition">
        @foreach(['Descripción' => $cuidadoDetalle['intervencion']->descripcion, 'Objetivo documentado' => $cuidadoDetalle['intervencion']->objetivo_especifico, 'Plan de origen' => $cuidadoDetalle['plan']->nombre, 'Estado' => ['PENDIENTE' => 'Pendiente', 'REALIZADA' => 'Realizado', 'NO_REALIZADA' => 'No realizado', 'CANCELADA' => 'Cancelado', 'SIN_OCURRENCIA_HOY' => 'Sin tarea programada hoy'][$cuidadoDetalle['estado']], 'Horario' => $cuidadoDetalle['hora'], 'Periodicidad' => $cuidadoDetalle['programacion']?->frecuencia, 'Observaciones' => $cuidadoDetalle['ejecucion']?->observacion, 'Ejecución' => $cuidadoDetalle['ejecucion']?->fecha_hora_ejecucion?->format('d/m/Y H:i'), 'Motivo de omisión' => $cuidadoDetalle['ejecucion']?->motivo_omision] as $etiqueta => $valor)<div><dt>{{ $etiqueta }}</dt><dd>{{ $valor ?: 'No registrado' }}</dd></div>@endforeach
    </dl><p class="rm-orion-note">Esta información es de consulta. Para registrar la ejecución de un cuidado utiliza la agenda de tu turno.</p><button type="button" class="rm-btn rm-btn-secondary" wire:click="cerrarCuidado">Volver a cuidados</button></div>
</x-ui.drawer-livewire>
@endif
