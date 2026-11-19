@php
    $petersen = $perfilInstrumental['petersen_institucional'] ?? null;
    $resumenPerfil = $petersen ? \App\Modulos\ApoyoDecision\Presentacion\Soporte\ResumenPetersenVisible::presentar($petersen, false) : null;
    $patrones = ['PET-01' => 'amnésico de dominio único', 'PET-02' => 'amnésico multidominio', 'PET-03' => 'no amnésico de dominio único', 'PET-04' => 'no amnésico multidominio'];
    $tituloPerfil = isset($patrones[$petersen['patron'] ?? '']) ? 'Perfil compatible con posible DCL '.$patrones[$petersen['patron']] : ($resumenPerfil['titulo'] ?? $consultaEnfermeria['titulo'] ?? 'Sin evaluación experta disponible');
    $dominiosInforme = ['COG-MEM' => 'Memoria', 'COG-ATE' => 'Atención', 'COG-EJE' => 'Funciones ejecutivas', 'COG-LEN' => 'Lenguaje', 'COG-VIS' => 'Capacidad visuoespacial'];
    $estadosInforme = ['A' => 'Alteración sustentada', 'P' => 'Rendimiento esperado sustentado', 'U' => 'Sin medición interpretable', 'I' => 'No interpretable', 'X' => 'Información contradictoria'];
    $serieInforme = collect($seriesInformeEnfermeria)->firstWhere('id', $serieComparacion) ?? ($seriesInformeEnfermeria[0] ?? null);
@endphp
<section class="rm-card rm-expert-report__result" id="expert-nursing-today" aria-labelledby="expert-nursing-today-title">
    <span class="rm-expert-report__result-icon" aria-hidden="true"><i class="ph ph-brain"></i></span>
    <div>
        <p class="rm-expert-eyebrow">{{ $petersen ? 'Resultado orientativo de la consulta actual' : 'Resultado de la evaluación disponible' }}</p>
        <h2 id="expert-nursing-today-title">{{ $tituloPerfil }}</h2>
        <p class="rm-expert-prose">{{ $resumenPerfil['explicacion'] ?? $consultaEnfermeria['resumen'] ?? 'No hay un resultado verificable para esta consulta.' }}</p>
        @if($petersen)<p class="rm-expert-report__limit">Requiere interpretación profesional; no constituye un diagnóstico médico confirmado. Este cálculo aún no está conservado como evaluación histórica completa.</p>@endif
        @if($consultaEnfermeria)
            <div class="rm-expert-report__actions">
                <button type="button" class="rm-btn rm-btn-primary" wire:click="abrirSignificado" wire:loading.attr="disabled" wire:target="abrirSignificado" aria-haspopup="dialog"><i class="ph ph-book-open" aria-hidden="true"></i>Entender resultado</button>
                <button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirConsulta('registro')" wire:loading.attr="disabled" wire:target="abrirConsulta" aria-haspopup="dialog"><i class="ph ph-files" aria-hidden="true"></i>Ver evidencias<i class="ph ph-sidebar" aria-hidden="true"></i></button>
                <a class="rm-btn rm-btn-secondary" href="#expert-nursing-fundamento" @click="$nextTick(() => { const detalle = document.getElementById('expert-nursing-fundamento'); detalle.open = true; detalle.querySelector('summary').focus(); })"><i class="ph ph-tree-structure" aria-hidden="true"></i>Ver fundamento</a>
            </div>
        @endif
    </div>
</section>

<div class="rm-expert-report__columns">
    <section class="rm-card rm-expert-report__panel" aria-labelledby="nursing-domain-heading">
        <header><div><h2 id="nursing-domain-heading">Áreas cognitivas</h2><p>Estado de la información disponible</p></div><i class="ph ph-chart-polar" aria-hidden="true"></i></header>
        <div class="rm-expert-report__domains">
            @foreach($dominiosInforme as $codigo => $nombre)
                @php($estadoArea = $petersen ? ($estadosInforme[$petersen['dominios'][$codigo]['estado'] ?? 'U'] ?? 'No interpretable') : ($informe['areas'][$codigo]['estado'] ?? 'Sin resultado publicado'))
                <button type="button" wire:key="nursing-domain-{{ $codigo }}" wire:click="seleccionarCriterio(@js($codigo))" wire:loading.attr="disabled" wire:target="seleccionarCriterio" aria-pressed="{{ $criterio === $codigo ? 'true' : 'false' }}">
                    <span>{{ $nombre }}</span><strong>{{ $estadoArea }}</strong><i class="ph ph-caret-right" aria-hidden="true"></i>
                </button>
            @endforeach
        </div>
        <p class="rm-expert-report__caption">La comparación radar requiere cinco medidas con una escala y referencia equivalentes. Los estados de las áreas no se convierten en puntuaciones.</p>
    </section>
    <section class="rm-card rm-expert-report__panel" aria-labelledby="nursing-sources-heading">
        <header><h2 id="nursing-sources-heading">Fuentes del análisis</h2><i class="ph ph-folder-open" aria-hidden="true"></i></header>
        <div class="rm-expert-report__sources">
            <div><i class="ph ph-clipboard-text" aria-hidden="true"></i><span>Mediciones cognitivas</span>@if($consultaEnfermeria)<button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirConsulta('registro')" wire:loading.attr="disabled" aria-haspopup="dialog">Consultar<span class="sr-only"> mediciones cognitivas</span><i class="ph ph-arrow-right" aria-hidden="true"></i></button>@endif</div>
            <div><i class="ph ph-house-line" aria-hidden="true"></i><span>Funcionamiento y contexto</span><a class="rm-btn rm-btn-secondary" href="#expert-nursing-fundamento" @click="document.getElementById('expert-nursing-fundamento').open = true">Consultar<span class="sr-only"> funcionamiento y contexto</span><i class="ph ph-arrow-right" aria-hidden="true"></i></a></div>
            <div><i class="ph ph-hand-heart" aria-hidden="true"></i><span>Cuidados vigentes</span>@if($consultaEnfermeria)<button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirConsulta('cuidados')" wire:loading.attr="disabled" aria-haspopup="dialog">Consultar<span class="sr-only"> cuidados vigentes</span><i class="ph ph-arrow-right" aria-hidden="true"></i></button>@else<a class="rm-btn rm-btn-secondary" href="{{ route('admin.enfermeria.agenda') }}">Ver agenda</a>@endif</div>
        </div>
        <p class="rm-expert-report__caption">Cada fuente conserva su fecha y autor. Las observaciones cotidianas se consultan por separado de las mediciones instrumentales.</p>
        <a class="rm-expert-report__text-link" href="{{ route('admin.enfermeria.pacientes', ['residente' => $codResidente]) }}">Abrir ficha del residente<i class="ph ph-arrow-right" aria-hidden="true"></i></a>
    </section>
</div>

<section class="rm-card rm-expert-report__panel" aria-labelledby="nursing-evolution-heading" x-data="{ tabla: false }">
    <header class="rm-expert-report__evolution-header">
        <div><h2 id="nursing-evolution-heading">Evolución de evaluaciones</h2><p>Mediciones del área de {{ mb_strtolower($dominiosInforme[$criterio]) }} · Mismo instrumento, versión y referencia</p></div>
        <div class="rm-expert-report__actions">
            @if($consultaEnfermeria)<button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirEvolucion" wire:loading.attr="disabled" wire:target="abrirEvolucion" aria-haspopup="dialog"><i class="ph ph-arrows-out" aria-hidden="true"></i>Ampliar evolución</button>@endif
        </div>
    </header>
    @if($errorInstrumental)
        <div class="rm-expert-report__empty" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i><div><strong>No fue posible consultar las mediciones.</strong><p>Esto no significa que no existan registros.</p><button type="button" class="rm-btn rm-btn-secondary" wire:click="reintentar">Reintentar</button></div></div>
    @elseif($serieInforme)
        <div class="rm-expert-report__chart-toolbar">
            <label for="nursing-report-measure">Instrumento y medida</label>
            <select id="nursing-report-measure" class="rm-select" wire:change="cambiarComparacion('serie', $event.target.value)">@foreach($seriesInformeEnfermeria as $serie)<option value="{{ $serie['id'] }}" @selected($serieInforme['id'] === $serie['id'])>{{ $serie['nombre'] }} · {{ $serie['version'] }}</option>@endforeach</select>
            <button type="button" class="rm-btn rm-btn-secondary" @click="tabla = !tabla" :aria-expanded="tabla" aria-controls="nursing-report-values"><i class="ph ph-table" aria-hidden="true"></i>Ver tabla</button>
        </div>
        <div x-data="Object.assign(rmCognitiveTimeline(@js($serieInforme['puntos']), @js('nursing-report-'.$this->getId().'-'.$serieInforme['id']), @js($serieInforme['unidad']), true), { activo: {{ count($serieInforme['puntos']) - 1 }} })" wire:key="nursing-report-timeline-{{ hash('sha256', json_encode($serieInforme)) }}">
            <div class="rm-expert-report__timeline">
                <div class="rm-expert-report__plot"><canvas x-ref="canvas" role="img" aria-label="Evolución de {{ $serieInforme['nombre'] }} en {{ $serieInforme['unidad'] }}. Los botones por fecha y la tabla muestran los mismos datos."></canvas></div>
                <aside class="rm-expert-report__selection" aria-live="polite"><p class="rm-expert-eyebrow">Registro seleccionado</p><strong><span x-text="puntoActivo?.valor"></span> <small>{{ $serieInforme['unidad'] }}</small></strong><p x-text="puntoActivo ? new Date(puntoActivo.fecha).toLocaleString('es') : ''"></p><p x-text="puntoActivo?.autor"></p><button type="button" class="rm-btn rm-btn-secondary" @click="$wire.verMedicion(puntoActivo.id)" x-show="puntoActivo?.id"><i class="ph ph-file-text" aria-hidden="true"></i>Ver registro</button></aside>
            </div>
            <div class="rm-cognitive-point-buttons" role="group" aria-label="Seleccionar una medición por fecha">@foreach($serieInforme['puntos'] as $indice => $punto)<button type="button" class="rm-btn rm-btn-secondary" @click="seleccionar({{ $indice }})" :aria-pressed="activo === {{ $indice }}">{{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($punto['fecha']) }} · {{ $punto['valor'] }}</button>@endforeach</div>
        </div>
        <div id="nursing-report-values" x-show="tabla" x-cloak class="rm-table-container"><table class="rm-table rm-decision-table"><caption>Mediciones de {{ $serieInforme['nombre'] }} · {{ $serieInforme['unidad'] }}</caption><thead><tr><th scope="col">Fecha</th><th scope="col">Valor</th><th scope="col">Profesional</th></tr></thead><tbody>@foreach($serieInforme['puntos'] as $punto)<tr><td data-label="Fecha">{{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($punto['fecha']) }}</td><td data-label="Valor">{{ $punto['valor'] }} {{ $serieInforme['unidad'] }}</td><td data-label="Profesional">{{ $punto['autor'] }}</td></tr>@endforeach</tbody></table></div>
        <p class="rm-expert-report__caption">Referencia: {{ $serieInforme['referencia'] }}. Fuente actual del instrumento; no reconstruye una evaluación histórica. Una diferencia numérica no implica por sí sola mejoría o empeoramiento.</p>
    @else
        <div class="rm-expert-report__empty"><i class="ph ph-chart-line-up" aria-hidden="true"></i><div><strong>Sin mediciones comparables para esta área</strong><p>La evolución aparecerá cuando existan registros instrumentales normalizados compatibles. Los controles cotidianos se consultan en el seguimiento.</p></div></div>
    @endif
</section>

<details id="expert-nursing-fundamento" class="rm-card rm-expert-report__foundation">
    <summary><i class="ph ph-tree-structure" aria-hidden="true"></i><span>Fundamento y límites del análisis<small>Áreas cognitivas, condiciones y fuentes disponibles</small></span><i class="ph ph-caret-down" aria-hidden="true"></i></summary>
    @if($petersen)
        <p>Información considerada hasta el {{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($petersen['fecha_corte'] ?? null) }}. Resultado orientativo de la consulta actual.</p>
        <div class="rm-expert-report__columns">
            <div><h3>Áreas cognitivas</h3>@foreach($dominiosInforme as $codigo => $nombre)@php($dominio = $petersen['dominios'][$codigo] ?? ['estado' => 'U', 'valoraciones' => []])<article class="rm-expert-operational-record"><strong>{{ $nombre }} · {{ $estadosInforme[$dominio['estado']] ?? 'No interpretable' }}</strong>@foreach($dominio['valoraciones'] as $valoracion)<p>{{ $valoracion['justificacion'] }}</p>@endforeach</article>@endforeach</div>
            <div><h3>Condiciones para clasificar</h3>@foreach(['C01' => 'Cambio corroborado', 'C02' => 'Alteración objetiva', 'C03' => 'Autonomía instrumental', 'C04' => 'Otras causas revisadas', 'C05' => 'Calidad de la evidencia'] as $codigo => $nombre)@php($requisito = $petersen['requisitos'][$codigo] ?? ['estado' => 'U', 'valoraciones' => []])<article class="rm-expert-operational-record"><strong>{{ $nombre }} · {{ ['S' => 'Sustentado', 'N' => 'No acreditado', 'U' => 'Pendiente', 'X' => 'Contradictorio'][$requisito['estado']] ?? 'Pendiente' }}</strong>@if($codigo === 'C02')<p>Se obtiene del estado de las cinco áreas cognitivas.</p>@endif @foreach($requisito['valoraciones'] as $valoracion)<p>{{ $valoracion['justificacion'] }}</p>@endforeach</article>@endforeach</div>
        </div>
    @elseif($consultaEnfermeria)
        <p>{{ $texto($informe['limite_global']) }}</p>
        <p>Alcance del resultado disponible: {{ $consultaEnfermeria['area_principal'] }}.</p>
    @endif
    @if($consultaEnfermeria)<div class="rm-expert-report__actions"><button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirDetalleArea" wire:loading.attr="disabled" aria-haspopup="dialog">Ver detalle del área</button><button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirConsulta('registro')" wire:loading.attr="disabled" aria-haspopup="dialog">Consultar fuentes y fundamento</button></div>@endif
</details>
