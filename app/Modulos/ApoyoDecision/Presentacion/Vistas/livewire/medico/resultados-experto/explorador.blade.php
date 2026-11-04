@php($narrativa = $informe['narrativa'])

<section class="rm-card rm-decision-result" aria-labelledby="decision-resultado-titulo">
    <div class="rm-decision-section-heading"><span class="rm-expert-eyebrow">{{ 'Evaluación histórica seleccionada' }}</span><span class="rm-decision-state">{{ $datos['detalle']['estado'] }}</span></div>
    <h2 id="decision-resultado-titulo">{{ $narrativa['conclusion'] }}</h2>
    <p class="rm-expert-prose">{{ $narrativa['resumen'] }}</p>
    <p class="rm-decision-result-limit"><i class="ph ph-info" aria-hidden="true"></i>{{ $informe['limite_global'] }}</p>
    <div class="rm-decision-toolbar">
        <button type="button" wire:click="abrirSignificado" class="rm-btn rm-btn-primary"><i class="ph ph-info" aria-hidden="true"></i>¿Qué significa este resultado?</button>
        <button type="button" wire:click="abrirConsulta" class="rm-btn rm-btn-secondary">¿Por qué este resultado?</button>
        @if($enfermeria)<button type="button" wire:click="abrirConsulta('cuidados')" class="rm-btn rm-btn-secondary"><i class="ph ph-heart" aria-hidden="true"></i>Consultar cuidados</button>@endif
    </div>
</section>

    <section class="rm-card rm-decision-panel" aria-labelledby="decision-perfil-titulo">
        <h2 id="decision-perfil-titulo" class="rm-expert-section-title">Perfil cognitivo</h2>
        <p class="text-sm text-rm-secondary">Cinco áreas. Cada estado conserva el alcance de su evaluación; un área sin datos no se considera preservada.</p>
        <div class="rm-decision-domain-grid">
            @foreach($informe['areas'] as $codigo => $area)
                <button type="button" class="rm-decision-domain" wire:click="seleccionarCriterio(@js($codigo))" wire:loading.attr="disabled"
                    aria-pressed="{{ $criterio === $codigo ? 'true' : 'false' }}" aria-controls="decision-desglose" wire:key="decision-area-{{ $codigo }}">
                    <i class="ph {{ $area['icono'] }}" aria-hidden="true"></i><strong>{{ $area['nombre'] }}</strong>
                    <span>{{ $area['estado'] }}</span>
                    <small>{{ count($datos['perfil'][$codigo]['evidencias']) }} evidencias conservadas</small>
                    <span class="rm-decision-domain-link">Explorar área <i class="ph ph-arrow-right" aria-hidden="true"></i></span>
                </button>
            @endforeach
        </div>
        <p class="rm-decision-profile-caption">Estados por área; no son puntuaciones comparables ni porcentajes de deterioro.</p>
    </section>
<div class="rm-decision-area-layout">
    <section class="rm-card rm-decision-panel rm-decision-area-detail" aria-labelledby="decision-area-titulo" aria-live="polite">
        <div class="rm-decision-area-heading"><span class="rm-decision-area-icon"><i class="ph {{ $informe['actual']['icono'] }}" aria-hidden="true"></i></span>
            <div><p class="rm-expert-eyebrow">Área seleccionada</p><h2 id="decision-area-titulo">{{ $informe['actual']['nombre'] }}</h2></div>
            <span class="rm-decision-state">{{ $informe['actual']['estado'] }}</span>
        </div>
        <p class="rm-expert-prose">{{ $informe['actual']['conclusion'] }}</p>
        <p class="text-sm text-rm-secondary">{{ $informe['actual']['significado'] }}</p>
        <div class="rm-decision-toolbar">
            <button type="button" wire:click="abrirConsulta('registro')" class="rm-btn rm-btn-secondary"><i class="ph ph-files" aria-hidden="true"></i>Ver evidencias del área</button>
            <button type="button" wire:click="irASeccion('decision-desglose')" class="rm-btn rm-btn-secondary">Ver subcomponentes</button>
        </div>
    </section>
    <section class="rm-card rm-decision-panel" aria-labelledby="decision-contexto-titulo">
        <h2 id="decision-contexto-titulo" class="rm-expert-section-title">Contexto y autonomía</h2>
        <p class="rm-expert-prose">{{ $narrativa['autonomia']['resumen'] }}</p>
        <dl class="rm-decision-definition">
            <div><dt>Evaluabilidad del área seleccionada</dt><dd>{{ $datos['detalle']['evaluabilidad'] ?? 'Sin evaluación conservada' }}</dd></div>
            <div><dt>Factores que limitan la interpretación</dt><dd>{{ $datos['detalle']['modificador'] ?? 'No consta una evaluación suficiente para descartar factores agudos o confusores.' }}</dd></div>
            <div><dt>Resultado y diagnóstico</dt><dd>El resultado experto apoya la valoración profesional. El diagnóstico médico se registra por separado.</dd></div>
        </dl>
        @foreach($informe['actual']['limites'] as $limite)<p class="rm-decision-note">{{ $limite }}</p>@endforeach
    </section>
</div>

<div class="rm-decision-reference-lower">
<details class="rm-card rm-decision-panel rm-decision-supplement" id="decision-desglose" aria-labelledby="decision-desglose-titulo">
    <summary><h2 id="decision-desglose-titulo" class="rm-expert-section-title">Desglose por dominios</h2><i class="ph ph-caret-down" aria-hidden="true"></i></summary>
    @foreach($informe['areas'] as $codigo => $area)
        <details class="rm-decision-accordion" wire:key="desglose-{{ $codigo }}-{{ $datos['evaluacion']['id'] ?? 'actual' }}" @if($criterio === $codigo) open @endif>
            <summary><i class="ph {{ $area['icono'] }}" aria-hidden="true"></i><strong>{{ $area['nombre'] }}</strong><span>{{ $area['estado'] }}</span><i class="ph ph-caret-down" aria-hidden="true"></i></summary>
            <div class="rm-decision-accordion-body">
                <p class="rm-expert-prose">{{ $area['conclusion'] }}</p>
                @if(isset($perfilInstrumental['dominios'][$codigo]))
                    <p class="rm-decision-note">Subcomponentes del contrato actual. Esta consulta al corte no completa ni modifica la evaluación histórica seleccionada.</p>
                    <ul class="rm-decision-component-list">
                        @foreach($perfilInstrumental['dominios'][$codigo]['componentes'] as $componente)
                            <li><strong>{{ $componente['nombre'] ?? 'Componente del contrato' }}</strong>
                                <span>{{ ($componente['estado_medicion'] ?? null) === 'MEDICIONES_DISPONIBLES' ? 'Mediciones normalizadas disponibles; no equivalen a un resultado clínico integrado.' : 'Sin medición normalizada disponible.' }}</span></li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-rm-secondary">No se conserva un desglose evaluable de subcomponentes para esta selección. No se trasladan resultados de otras áreas.</p>
                @endif
                <button type="button" wire:click="seleccionarCriterio(@js($codigo))" class="rm-btn rm-btn-secondary">Seleccionar {{ $area['nombre'] }}</button>
                @if($criterio === $codigo)<button type="button" wire:click="abrirConsulta('registro')" class="rm-btn rm-btn-secondary">Ver evidencias del área</button>@endif
            </div>
        </details>
    @endforeach
</details>
<section class="rm-card rm-decision-panel" aria-labelledby="decision-fundamento-resumen-titulo">
    <div class="rm-decision-section-heading">
        <h2 id="decision-fundamento-resumen-titulo" class="rm-expert-section-title">Fundamento del resultado</h2>
        <button type="button" wire:click="abrirConsulta('registro')" class="rm-btn rm-btn-secondary">Ver todas las evidencias <i class="ph ph-arrow-right" aria-hidden="true"></i></button>
    </div>
    <p class="rm-decision-note">Referencias conservadas. El valor original histórico solo se muestra cuando forma parte de la instantánea.</p>
    <div class="rm-decision-evidence-preview-wrap">
        <table class="rm-decision-evidence-preview">
            <thead><tr><th scope="col">Fuente</th><th scope="col">Evidencia</th><th scope="col">Uso</th><th scope="col">Fecha</th></tr></thead>
            <tbody>
                @forelse(array_slice(array_values($datos['detalle']['evidencias'] ?? []), 0, 5) as $referencia)
                    <tr><th scope="row">{{ $referencia['fuente'] ?? 'Fuente registrada' }}</th><td>{{ $referencia['nombre'] ?? 'Evidencia registrada' }}</td><td>{{ $referencia['papel'] ?? 'No disponible' }}</td><td>{{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($referencia['fecha'] ?? null) }}</td></tr>
                @empty
                    <tr><td colspan="4">Sin evidencias conservadas para este resultado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
</div>

@include('apoyodecision::livewire.medico.resultados-experto.historial')

<section class="rm-card rm-decision-panel" aria-labelledby="decision-razonamiento-titulo" x-data="{ tab: 'explicacion' }" wire:key="razonamiento-{{ $criterio }}-{{ $datos['evaluacion']['id'] ?? 'vacio' }}">
    <h2 id="decision-razonamiento-titulo" class="rm-expert-section-title">¿Cómo se obtuvo este resultado?</h2>
    <div class="rm-decision-tabs" role="group" aria-label="Explorar razonamiento">
        @foreach(['explicacion' => 'Explicación', 'grafo' => 'Proceso de evaluación', 'reglas' => 'Reglas', 'evidencias' => 'Evidencias', 'conocimiento' => 'Relación entre conceptos'] as $tab => $label)
            <button type="button" @click="tab = @js($tab)" :aria-pressed="tab === @js($tab)" class="rm-btn rm-btn-secondary">{{ $label }}</button>
        @endforeach
    </div>
    <div x-show="tab === 'explicacion'">
        <p class="rm-expert-prose">{{ $informe['actual']['por_que'] }}</p>
        <ol class="rm-decision-steps"><li>Registro original</li><li>Interpretación del dato</li><li>Evidencia</li><li>Requisito evaluado</li><li>Regla aplicada</li><li>Conclusión de la regla</li><li>Resultado</li></ol>
        <p class="rm-decision-note">El recorrido muestra vínculos conservados cuando existen. Una condición no cumplida no demuestra un resultado contrario.</p>
    </div>
    <div x-show="tab === 'grafo'" x-cloak>
        @if($exploracion)@include('apoyodecision::livewire.medico.resultados-experto.grafo', ['grafo' => $exploracion['inferencia'], 'tipo' => 'inferencia', 'titulo' => 'Caja de cristal · evaluación histórica'])
        @else<p class="rm-decision-empty">La consulta técnica del grafo requiere autorización específica. El acceso a resultados no concede acceso a fuentes restringidas.</p>@endif
    </div>
    <div x-show="tab === 'conocimiento'" x-cloak>
        @if($exploracion)@include('apoyodecision::livewire.medico.resultados-experto.grafo', ['grafo' => $exploracion['semantico'], 'tipo' => 'semantico', 'titulo' => 'Estructura del conocimiento'])
        @else<p class="rm-decision-empty">La consulta del catálogo semántico requiere autorización específica.</p>@endif
    </div>
    <div x-show="tab === 'reglas'" x-cloak>
        @if($exploracion)
            @include('apoyodecision::livewire.medico.resultados-experto.reglas-tabla')
        @elseif(! $puedeAuditar)
            <p class="rm-decision-empty">El detalle técnico de las reglas requiere permiso de auditoría. Esto no significa que las reglas del sistema estén deshabilitadas.</p>
        @elseif($datos['prueba_tecnica'] ?? false)
            <p class="rm-decision-empty">Esta ejecución se conserva bajo un contrato de prueba separado y no ofrece aquí una traza clínica de reglas.</p>
        @else
            <p class="rm-decision-empty">No hay una traza de reglas conservada para la evaluación histórica seleccionada. La consulta actual del expediente no completa datos históricos ausentes.</p>
        @endif
    </div>
    <div x-show="tab === 'evidencias'" x-cloak>
        <p class="rm-decision-note">Evidencias conservadas en esta evaluación y área. El conteo representa cobertura documental; no mide deterioro.</p>
        <button type="button" wire:click="abrirConsulta('registro')" class="rm-btn rm-btn-secondary">Abrir datos y evidencias</button>
        @php($cobertura = ['etiquetas' => ['Soporte de inferencia', 'Contexto', 'Otras evidencias'], 'valores' => [count(array_filter($datos['detalle']['evidencias'], fn ($e) => $e['papel'] === 'Soporte de inferencia')), count(array_filter($datos['detalle']['evidencias'], fn ($e) => $e['rol'] === 'CONTEXTUAL')), count(array_filter($datos['detalle']['evidencias'], fn ($e) => $e['papel'] !== 'Soporte de inferencia' && $e['rol'] !== 'CONTEXTUAL'))]])
        @if(array_sum($cobertura['valores']) > 0)
            <div class="rm-chart-card mt-3" wire:key="cobertura-{{ $criterio }}-{{ $datos['evaluacion']['id'] }}" x-data="rmDecisionCoverage(@js($cobertura), @js('decision-cobertura-'.$this->getId().'-'.$criterio.'-'.$datos['evaluacion']['id']))">
                <h3 class="rm-chart-title">Distribución de evidencias conservadas</h3>
                <p class="rm-chart-subtitle">Fuente: evaluación seleccionada · {{ $datos['evaluacion']['fecha'] }} · Unidad: evidencias</p>
                <div class="rm-chart-body is-sm" wire:ignore><canvas x-ref="canvas" role="img" aria-label="Cantidad de evidencias por uso. Valores consultables debajo."></canvas></div>
                <dl class="rm-decision-counts">@foreach($cobertura['etiquetas'] as $i => $etiqueta)<div><dt>{{ $etiqueta }}</dt><dd>{{ $cobertura['valores'][$i] }}</dd></div>@endforeach</dl>
            </div>
        @else<p class="rm-decision-empty">No hay evidencias históricas para graficar. La ausencia de datos no equivale a cero dificultad.</p>@endif
    </div>
</section>
