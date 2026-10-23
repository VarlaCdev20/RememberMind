<div class="rm-expert-source-summary">
    <section class="rm-expert-source-summary__path" aria-labelledby="fuentes-ruta-title">
        <div class="rm-expert-source-summary__path-heading">
            <div>
                <p class="rm-expert-eyebrow">Ruta de lectura</p>
                <h3 id="fuentes-ruta-title">Cómo llega la información a la evaluación</h3>
            </div>
            <span>Datos registrados, sin equivalencias automáticas</span>
        </div>
        <ol class="rm-expert-source-summary__steps">
            <li>
                <span class="rm-expert-source-summary__step-icon" aria-hidden="true"><i class="ph ph-notebook"></i></span>
                <div>
                    <span class="rm-expert-source-summary__step-number">1 · Seguimiento</span>
                    <h4>Hechos observados</h4>
                    <p><strong>{{ $resumenFuentes['controles'] === null ? 'No consultados' : $resumenFuentes['controles'] }}</strong> controles cognitivos<br><strong>{{ $resumenFuentes['observacionales'] }}</strong> observaciones por componente</p>
                    <small>Describen cambios y episodios; no son puntuaciones instrumentales.</small>
                    <button type="button" wire:click="irASeccion('hallazgos-observacionales')" wire:loading.attr="disabled" wire:target="irASeccion" class="rm-btn rm-btn-secondary rm-expert-source-summary__step-action">Ver hallazgos y reglas</button>
                </div>
            </li>
            <li class="{{ $resumenFuentes['instrumentales'] === 0 ? 'is-missing' : '' }}">
                <span class="rm-expert-source-summary__step-icon" aria-hidden="true"><i class="ph ph-clipboard-text"></i></span>
                <div>
                    <span class="rm-expert-source-summary__step-number">2 · Medición cognitiva</span>
                    <h4>{{ $resumenFuentes['instrumentales'] }} {{ $resumenFuentes['instrumentales'] === 1 ? 'aplicación registrada' : 'aplicaciones registradas' }}</h4>
                    <p>{{ $resumenFuentes['instrumentales'] === 0 ? 'Aún no hay una aplicación instrumental cognitiva en esta consulta.' : 'Cada aplicación requiere revisar instrumento, versión, método y procedencia.' }}</p>
                    <button type="button" wire:click="irASeccion('aplicaciones-instrumentales')" wire:loading.attr="disabled" wire:target="irASeccion" class="rm-btn rm-btn-secondary rm-expert-source-summary__step-action">Ver aplicaciones y respuestas</button>
                </div>
            </li>
            <li>
                <span class="rm-expert-source-summary__step-icon" aria-hidden="true"><i class="ph ph-list-checks"></i></span>
                <div>
                    <span class="rm-expert-source-summary__step-number">3 · Interpretación</span>
                    <h4>Comprobar la evidencia</h4>
                    <p>La evaluación distingue fuentes admisibles, excluidas y ausentes antes de explicar sus límites.</p>
                    @if(isset($perfilInstrumental['petersen_institucional']))
                        <button type="button" wire:click="irASeccion('orion-petersen-criterios')" wire:loading.attr="disabled" wire:target="irASeccion" class="rm-btn rm-btn-secondary rm-expert-source-summary__step-action">Ver criterios evaluados</button>
                    @elseif(($datos['evaluacion'] ?? null) && $informe)
                        <button type="button" wire:click="irASeccion('orion-desglose')" wire:loading.attr="disabled" wire:target="irASeccion" class="rm-btn rm-btn-secondary rm-expert-source-summary__step-action">Ver desglose cognitivo</button>
                    @endif
                </div>
            </li>
        </ol>
        <p class="rm-expert-source-summary__path-note"><i class="ph ph-info" aria-hidden="true"></i>Esta ruta explica la lectura de los datos: los controles y observaciones no se convierten en mediciones cognitivas.</p>
    </section>

    <div class="rm-expert-source-summary__functional">
        <i class="ph ph-person-simple-walk" aria-hidden="true"></i>
        <p><strong>Autonomía y contexto funcional</strong><span>{{ $resumenFuentes['funcionales'] }} {{ $resumenFuentes['funcionales'] === 1 ? 'aplicación funcional registrada' : 'aplicaciones funcionales registradas' }}. Se revisan por separado de la medición cognitiva.</span></p>
    </div>

    <section class="rm-expert-source-summary__chart {{ $resumenFuentes['series_comparables'] ? '' : 'is-empty' }}" aria-labelledby="source-trend-title" @if($resumenFuentes['series_comparables']) x-data="{ seleccion: 0 }" x-effect="if (seleccion >= @js($resumenFuentes['series_comparables'])) seleccion = 0" @endif>
        <h3 id="source-trend-title">Cambios en mediciones comparables</h3>
        @if($resumenFuentes['series_comparables'])
            @if($resumenFuentes['series_comparables'] > 1)
                <div class="rm-expert-source-summary__selector"><label for="source-trend-series">Medición que desea comparar</label><select id="source-trend-series" class="rm-select" x-model.number="seleccion">
                    @foreach($resumenFuentes['series_comparables_lista'] as $indice => $opcion)
                        <option value="{{ $indice }}">{{ $opcion['nombre'] }} · edición {{ $opcion['version'] }}</option>
                    @endforeach
                </select></div>
            @endif
            @foreach($resumenFuentes['series_comparables_lista'] as $indice => $serieFuente)
                <template x-if="seleccion === {{ $indice }}">
                    <div class="rm-expert-source-summary__series" x-data="rmCognitiveTimeline(@js($serieFuente['puntos']), @js('fuentes-cognitivas-'.$this->getId().'-'.$serieFuente['id']), @js($serieFuente['unidad']))">
                        <p><strong>{{ $serieFuente['nombre'] }}</strong> · {{ $serieFuente['instrumento'] }} · edición {{ $serieFuente['version'] }} · {{ $serieFuente['unidad'] }}.</p>
                        <div class="rm-chart-body rm-expert-source-summary__canvas"><canvas x-ref="canvas" role="img" aria-label="Cambios de la serie instrumental seleccionada. Los valores y fechas aparecen también en la tabla siguiente."></canvas></div>
                        <div class="rm-orion-table-wrap"><table class="rm-orion-table">
                            <caption>Valores de la serie seleccionada · {{ $serieFuente['unidad'] }}</caption>
                            <thead><tr><th scope="col">Fecha</th><th scope="col">Medición</th><th scope="col">Resultado bruto</th><th scope="col">Profesional</th></tr></thead>
                            <tbody>@foreach($serieFuente['puntos'] as $punto)<tr><td data-label="Fecha">{{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($punto['fecha']) }}</td><td data-label="Medición">{{ $punto['valor'] }} {{ $serieFuente['unidad'] }}</td><td data-label="Resultado bruto">{{ $punto['resultado_bruto'] ?? 'No disponible' }}</td><td data-label="Profesional">{{ $punto['autor'] }}</td></tr>@endforeach</tbody>
                        </table></div>
                    </div>
                </template>
            @endforeach
            <p class="rm-expert-source-summary__note">Se comparan aplicaciones de la misma medida, instrumento, edición, método y referencia. El cambio numérico no establece mejoría ni deterioro clínico.</p>
        @else
            <p class="rm-expert-source-summary__note">Para mostrar una evolución se necesitan al menos dos mediciones de la misma serie en fechas distintas.</p>
        @endif
    </section>

    <details class="rm-expert-source-summary__detail">
        <summary>Ver inventario por instrumento y edición <i class="ph ph-caret-down" aria-hidden="true"></i></summary>
        <div class="rm-orion-table-wrap"><table class="rm-orion-table">
        <caption>Resumen de fuentes registradas por instrumento y edición</caption>
        <thead><tr><th scope="col">Fuente</th><th scope="col">Tipo</th><th scope="col">Aplicaciones</th><th scope="col">Registro más reciente</th></tr></thead>
        <tbody>
            @forelse($resumenFuentes['filas'] as $fila)
                <tr><th scope="row">{{ $fila['nombre'] }} <small class="rm-expert-source-summary__version">{{ $fila['version'] }}</small></th><td data-label="Tipo">{{ $fila['tipo'] }}</td><td data-label="Aplicaciones">{{ $fila['aplicaciones'] }}</td><td data-label="Registro más reciente">{{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($fila['ultima_fecha']) }}</td></tr>
            @empty
                <tr><td colspan="4">No hay aplicaciones de instrumentos u observaciones de instrumento disponibles hasta esta consulta.</td></tr>
            @endforelse
        </tbody>
        </table></div>
        <p class="rm-expert-source-summary__note">Abra los registros completos para ver respuestas, procedencia y motivos de exclusión.</p>
    </details>
</div>
