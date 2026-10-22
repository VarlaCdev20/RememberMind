<div class="rm-expert-results text-rm-primary {{ $psicologia ? 'rm-expert-results--psychology' : ($enfermeria ? 'rm-expert-results--nursing' : 'rm-expert-results--medicine') }}" x-data="{ consultaVisible: @entangle('consultaAbierta'), vistaClinica: @js($psicologia ? 'resumen' : 'mapa') }" :class="{ 'has-consulta': consultaVisible && !@js($enfermeria) }"
    x-on:orion-navigate.window="vistaClinica = 'resumen'; $nextTick(() => { const section = document.getElementById($event.detail.seccion); if (section) { if (section.tagName === 'DETAILS') section.open = true; let parent = section.parentElement?.closest('details'); while (parent) { parent.open = true; parent = parent.parentElement?.closest('details'); } section.scrollIntoView({ block: 'start' }); section.querySelector('summary, h2')?.focus({ preventScroll: true }); } })"
    x-on:expert-trace-opened.window="$nextTick(() => document.getElementById('drawer-trazabilidadabierta-title')?.focus())"
    x-on:expert-trace-closed.window="$nextTick(() => $refs.traceTrigger?.focus())">
    @if($cohorteEntrenamiento ?? false)
        <aside class="rm-orion-note mb-3" aria-label="Procedencia de los datos">
            <strong>Residente de entrenamiento.</strong> Datos generados para verificar el funcionamiento del sistema. Puede consultar sus fuentes y ejecutar el análisis; los resultados no pertenecen a una persona real.
        </aside>
    @endif
    @if($enfermeria)
        @include('apoyodecision::livewire.medico.resultados-experto.enfermeria')
    @else
    <nav class="rm-expert-breadcrumb" aria-label="Ruta de navegación">
        <a href="{{ $psicologia ? route('admin.psicologia.dashboard') : route('admin.medico.residente.ficha', ['adulto' => $codResidente]) }}">{{ $psicologia ? 'Psicología' : 'Ficha del residente' }}</a>
        <i class="ph ph-caret-right" aria-hidden="true"></i>
        <span>Evaluación cognitiva</span>
        <i class="ph ph-caret-right" aria-hidden="true"></i>
        <span aria-current="page">{{ $residente->nombre_completo }}</span>
    </nav>
    <header class="rm-expert-heading rm-expert-page-heading">
        <div class="min-w-0"><p class="rm-expert-eyebrow">Sistema experto · Consulta del residente</p><h1>{{ $psicologia ? 'Evaluación cognitiva y funcional' : $residente->nombre_completo }}</h1><p class="rm-expert-subtitle">{{ $psicologia ? 'Resultados y evidencias disponibles' : (($residente->edad !== null ? $residente->edad.' años · ' : '').$residente->ubicacion_formateada) }}</p></div>
        <div class="rm-expert-heading-actions">
            @if($psicologia)
                <button type="button" wire:click="irASeccion('fuentes-disponibles')" wire:loading.attr="disabled" class="rm-btn rm-btn-primary"><i class="ph ph-folder-open" aria-hidden="true"></i>Ver registros y evidencias</button>
                <button type="button" @if($datos['evaluacion'] ?? null) wire:click="irASeccion('orion-evolucion')" @else disabled title="Disponible cuando exista una evaluación guardada" @endif class="rm-btn rm-btn-secondary"><i class="ph ph-clock-counter-clockwise" aria-hidden="true"></i>Historial</button>
                <button type="button" @if(($datos['evaluacion'] ?? null) && isset($revisiones['PSICOLOGIA']['estado'], $revisiones['MEDICINA']['estado'])) wire:click="irASeccion('orion-revision-profesional')" @else disabled title="Disponible cuando exista una evaluación clínica completa y conservada" @endif class="rm-btn rm-btn-secondary"><i class="ph ph-users" aria-hidden="true"></i>Revisión profesional</button>
            @else
            @if($datos && $informe)
                <button type="button" wire:click="abrirConsulta('registro')" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary"><i class="ph ph-files" aria-hidden="true"></i>Ver evidencias</button>
                @if($datos['evaluacion'] ?? null)
                    <button type="button" wire:click="irASeccion('orion-evolucion')" class="rm-btn rm-btn-secondary"><i class="ph ph-clock-counter-clockwise" aria-hidden="true"></i>Ver historial</button>
                @endif
                @if(isset($revisiones['PSICOLOGIA']['estado'], $revisiones['MEDICINA']['estado']))
                    <button type="button" wire:click="irASeccion('orion-revision-profesional')" class="rm-btn rm-btn-secondary"><i class="ph ph-users" aria-hidden="true"></i>Revisión profesional</button>
                @endif
            @endif
            @can('consultarGobierno', \App\Models\VersionModeloExperto::class)
                <a href="{{ route('admin.sistema-experto.reglas') }}" class="rm-btn rm-btn-secondary"><i class="ph ph-scales" aria-hidden="true"></i>Consultar reglas</a>
            @endcan
            <a class="rm-btn rm-btn-secondary" href="{{ $psicologia ? route('admin.psicologia.dashboard') : route('admin.medico.residente.ficha', ['adulto' => $codResidente]) }}"><i class="ph ph-arrow-left" aria-hidden="true"></i>Volver</a>
            @endif
        </div>
    </header>
    <p wire:loading role="status" class="text-sm">Consultando la valoración y sus fuentes…</p>

    @if($psicologia)
    <nav class="rm-expert-reading-nav" aria-label="Secciones de la consulta cognitiva"><a class="rm-btn rm-btn-secondary" href="{{ route('admin.sistema-experto.residentes') }}">Residentes</a><button type="button" wire:click="abrirDetalleArea" class="rm-btn rm-btn-secondary">Área seleccionada</button><button type="button" wire:click="irASeccion('fuentes-disponibles')" class="rm-btn rm-btn-secondary">Evidencias y fuentes</button></nav>
    @else
    <nav class="rm-expert-local-tabs rm-expert-clinical-tabs" aria-label="Secciones de la consulta cognitiva">
        <button type="button" @click="vistaClinica = 'mapa'" :aria-pressed="vistaClinica === 'mapa'"><i class="ph ph-graph" aria-hidden="true"></i>Conexiones</button>
        <button type="button" @click="vistaClinica = 'resumen'" :aria-pressed="vistaClinica === 'resumen'"><i class="ph ph-brain" aria-hidden="true"></i>Resumen y análisis</button>
        <button type="button" wire:click="irASeccion('fuentes-disponibles')"><i class="ph ph-files" aria-hidden="true"></i>Fuentes originales</button>
        @if($datos['evaluacion'] ?? null)<button type="button" wire:click="irASeccion('orion-evolucion')"><i class="ph ph-clock-counter-clockwise" aria-hidden="true"></i>Historial conservado</button>@endif
        @if(auth()->user()->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A']))<a href="{{ route('admin.sistema-experto.residentes') }}"><i class="ph ph-users-three" aria-hidden="true"></i>Cambiar residente</a>@endif
    </nav>
    <div x-show="vistaClinica === 'mapa'">
        @include('apoyodecision::livewire.medico.resultados-experto.conexiones-residente')
    </div>
    @endif
    <div class="rm-orion-workspace mt-3" x-show="vistaClinica === 'resumen'" x-cloak>
        {{-- Columna Principal: Informe Cognitivo --}}
        <div class="space-y-4 min-w-0">
            @if($psicologia && ! isset($perfilInstrumental['petersen_institucional']))
            <section class="rm-card rm-expert-context" aria-label="Contexto del residente">
                <div class="rm-expert-context-grid">
                    <div class="rm-expert-person min-w-0"><span class="rm-expert-avatar" aria-hidden="true">@if($residente->foto)<img src="{{ \Illuminate\Support\Facades\Storage::url($residente->foto) }}" alt="" loading="lazy" decoding="async">@else<i class="ph ph-user" aria-hidden="true"></i>@endif</span><div class="min-w-0"><h2 class="rm-expert-resident-name">{{ $residente->nombre_completo }}</h2><p class="text-sm">{{ $residente->edad !== null ? $residente->edad.' años' : 'Edad no disponible' }} · {{ $residente->ubicacion_formateada }}</p><small>{{ $residente->cod_residente }}</small></div></div>
                    @if($datos['evaluacion'] ?? null)
                        <dl><dt>Fecha de la evaluación histórica</dt><dd>{{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($datos['evaluacion']['fecha']) }}</dd></dl>
                        <dl><dt>Profesional solicitante</dt><dd>{{ $datos['evaluacion']['solicitante'] ?? 'No disponible' }}</dd></dl>
                        <dl><dt>{{ $simulacion ? 'Estado del resultado técnico' : 'Revisión profesional' }}</dt><dd>{{ $estadoRevision ?? 'Sin valoración cognitiva disponible para revisión' }}</dd></dl>
                    @else
                        <dl><dt>Fecha de la consulta actual</dt><dd>{{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($perfilInstrumental['petersen_institucional']['fecha_corte'] ?? null, 'No disponible') }}</dd></dl>
                        <dl><dt>Evaluación histórica</dt><dd>No hay una evaluación clínica conservada para esta consulta.</dd></dl>
                        <dl><dt>Revisión profesional</dt><dd>Disponible después de conservar una evaluación clínica completa.</dd></dl>
                    @endif
                </div>
            </section>
            @endif

            @if($datos && $informe && ! $psicologia)
                @if(! ($datos['evaluacion'] ?? null))<aside class="rm-expert-disclosure" role="note"><i class="ph ph-info" aria-hidden="true"></i><div><strong>Disponibilidad de conocimiento</strong><p>No hay una evaluación histórica publicable en esta consulta. Los controles y registros disponibles conservan su propio alcance.</p></div></aside>@endif
                @include('apoyodecision::livewire.medico.resultados-experto.dominios')
            @endif
            @if(isset($perfilInstrumental['petersen_institucional']))
                @include('apoyodecision::livewire.medico.resultados-experto.petersen-institucional')
            @elseif($informe && $psicologia)
                @php($areasGrafico = collect($informe['areas'])->map(fn ($area) => ['nombre' => $area['nombre'], 'estado' => $area['estado'], 'grupo' => $area['evaluada'] ? 'disponible' : 'sin_resultado'])->values()->all())
                @include('apoyodecision::livewire.medico.resultados-experto.grafico-areas', [
                    'graficoId' => 'medicina-cobertura-dominios',
                    'graficoTitulo' => 'Cobertura de las cinco áreas cognitivas',
                    'graficoSubtitulo' => 'Resultados publicados en la evaluación consultada; las observaciones generales no se cuentan como resultados.',
                ])
            @endif

            @if($resumenFuentes && ! $psicologia)
                <section class="rm-card rm-orion-panel rm-expert-available-records" aria-labelledby="registros-disponibles-titulo">
                    <div class="rm-expert-followup-heading">
                        <div><p class="rm-expert-eyebrow">Expediente consultado</p><h2 id="registros-disponibles-titulo" class="rm-expert-section-title">Seguimiento y registros disponibles</h2></div>
                        <button type="button" wire:click="irASeccion('fuentes-disponibles')" class="rm-btn rm-btn-primary"><i class="ph ph-files" aria-hidden="true"></i>Ver registros del seguimiento</button>
                    </div>
                    <p class="rm-orion-note">Los controles y observaciones sirven para conocer cambios cotidianos, aunque todavía no permitan clasificar el perfil cognitivo. Cada registro conserva su fecha y autor.</p>
                    <dl class="rm-expert-source-summary__counts mt-3" aria-label="Registros disponibles por tipo">
                        <div><dt>Controles cognitivos</dt><dd>{{ $resumenFuentes['controles'] === null ? 'No consultados' : $resumenFuentes['controles'] }}</dd></div>
                        <div><dt>Observaciones por componente</dt><dd>{{ $resumenFuentes['observacionales'] }}</dd></div>
                        <div><dt>Aplicaciones funcionales</dt><dd>{{ $resumenFuentes['funcionales'] }}</dd></div>
                        <div><dt>Aplicaciones instrumentales</dt><dd>{{ $resumenFuentes['instrumentales'] }}</dd></div>
                    </dl>
                    @php($senalesDelSeguimiento = collect($hallazgosObservacionales['resumen'] ?? [])->filter(fn ($item) => ($item['estado'] ?? null) === 'CUMPLE'))
                    @if($senalesDelSeguimiento->isNotEmpty())
                        <div class="rm-orion-petersen-observations" aria-labelledby="senales-seguimiento-titulo">
                            <div class="rm-orion-petersen-observations__heading"><span class="rm-orion-petersen-observations__icon" aria-hidden="true"><i class="ph ph-clipboard-text"></i></span><div><h3 id="senales-seguimiento-titulo">Señales observadas en el seguimiento</h3><p>Revise cada hallazgo junto con su control original.</p></div></div>
                            <ul class="rm-orion-petersen-observations__list">
                                @foreach($senalesDelSeguimiento as $codigoRegla => $senal)
                                    <li><span class="rm-orion-petersen-observations__item-icon" aria-hidden="true"><i class="ph ph-note-pencil"></i></span><div><h4>{{ $senal['nombre'] ?? \MotorExperto\dominio\conocimiento\reglas\EvaluadorObservaciones::REGLAS[$codigoRegla]['nombre'] }}</h4>@if($senal['hallazgo'])<p>{{ $senal['hallazgo'] }}</p>@endif</div></li>
                                @endforeach
                            </ul>
                            <div class="rm-orion-petersen-observations__footer"><p><i class="ph ph-info" aria-hidden="true"></i><span>Estos hechos observados no sustituyen mediciones instrumentales ni establecen un diagnóstico.</span></p><button type="button" wire:click="irASeccion('hallazgos-observacionales')" class="rm-btn rm-btn-secondary">Ver controles y reglas</button></div>
                        </div>
                    @endif
                    @if(($datos['registros_cognitivos'] ?? []) !== [])
                        <details class="rm-expert-supplement mt-3">
                            <summary>Ver controles y cambios observados</summary>
                            <p class="rm-orion-note mt-2">Estos son controles recientes; el apartado de fuentes permite consultar el seguimiento completo.</p>
                            <div class="space-y-3 mt-3">
                                @foreach($datos['registros_cognitivos'] as $registro)
                                    <article class="rm-expert-source">
                                        <h3 class="font-semibold">Control del {{ $registro['fecha'] }}</h3>
                                        <p class="text-sm text-rm-secondary">Registrado por {{ $registro['autor'] }}</p>
                                        @if($registro['observacion'])<p class="rm-expert-prose mt-2">{{ $registro['observacion'] }}</p>@endif
                                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
                                            @foreach($registro['mediciones'] as $medicion)
                                                <div><dt class="text-sm text-rm-secondary">{{ $medicion['nombre'] }}</dt><dd>{{ $medicion['valor'] }}</dd></div>
                                            @endforeach
                                        </dl>
                                    </article>
                                @endforeach
                            </div>
                        </details>
                    @else
                        <p class="rm-orion-note mt-3">No hay controles cognitivos recientes disponibles para este resumen. Consulte las fuentes para revisar otros registros.</p>
                    @endif
                </section>
            @endif

            {{-- Acción central: Evaluación cognitiva y análisis de información disponible --}}
            <section class="rm-card rm-expert-analysis-card p-5 rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border)] shadow-sm space-y-4" aria-label="Evaluación cognitiva y análisis de evidencia">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)]">
                                <i class="ph ph-brain text-base"></i>
                            </span>
                            <h2 class="text-base font-bold text-[var(--rm-text-primary)]">{{ $psicologia ? 'Actualizar la consulta' : 'Evaluación cognitiva' }}</h2>
                            @unless($psicologia)<span class="rm-expert-analysis-state">Consulta de fuentes disponibles</span>@endunless
                        </div>
                        <p class="text-xs text-[var(--rm-text-secondary)] max-w-2xl leading-relaxed">
                            {{ $psicologia ? 'Vuelve a analizar los registros disponibles. La consulta no modifica las valoraciones originales.' : 'Consulta y análisis de información disponible (pruebas aplicadas, observaciones, valoraciones originales, IADL y condiciones registradas). El motor ejecutará las reglas aplicables sin modificar los registros originales.' }}
                        </p>
                    </div>
                    <div class="shrink-0 flex items-center gap-2">
                        <button type="button"
                                wire:click="analizarEvidencia"
                                wire:loading.attr="disabled"
                                class="px-4 py-2.5 rounded-xl font-bold text-xs bg-[var(--rm-action-primary)] hover:bg-[var(--rm-success)] text-white shadow-sm transition flex items-center gap-2 cursor-pointer disabled:opacity-50">
                            <i class="ph ph-lightning text-sm" wire:loading.remove wire:target="analizarEvidencia"></i>
                            <i class="ph ph-spinner animate-spin text-sm" wire:loading wire:target="analizarEvidencia"></i>
                            <span>Analizar evidencia</span>
                        </button>
                    </div>
                </div>

                @if($analisisEjecutado)
                    <div role="status" aria-live="polite" class="p-3 rounded-xl bg-[var(--rm-action-primary-soft)]/40 border border-[var(--rm-action-primary)]/30 flex items-start gap-2.5 text-xs text-[var(--rm-text-primary)]">
                        <i class="ph ph-info text-base text-[var(--rm-action-primary)] shrink-0 mt-0.5"></i>
                        <div>
                            <strong class="font-bold">Resultado calculado para esta consulta:</strong>
                            <span class="text-[var(--rm-text-secondary)]"> Se utilizó la información disponible hasta el {{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($fechaAnalisis) }}. Este cálculo aún no está guardado como evaluación histórica completa y no modifica los registros originales.</span>
                        </div>
                    </div>
                @endif

                @error('analisis')
                    <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700 flex items-center gap-2">
                        <i class="ph ph-warning-circle text-base"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </section>

            @if(($datos['evaluacion'] ?? null) && $informe)
                @include('apoyodecision::livewire.medico.resultados-experto.explorador')
            @endif
            <details id="fuentes-disponibles" class="rm-card rm-orion-panel rm-expert-sources-section" aria-labelledby="fuentes-disponibles-title">
            <summary class="rm-expert-sources-section__heading">
                <div>
                    <h2 id="fuentes-disponibles-title" tabindex="-1" class="rm-expert-section-title">Fuentes y mediciones disponibles</h2>
                    <p>Abra para consultar registros originales, mediciones y reglas observacionales.</p>
                </div>
                <span class="rm-expert-sources-section__current">Consulta del expediente actual</span>
            </summary>
            <p class="rm-expert-sources-section__scope">Datos actuales del expediente; no alteran ni completan la evaluación histórica seleccionada.</p>
            @if($resumenFuentes)
                @include('apoyodecision::livewire.medico.resultados-experto.resumen-fuentes')
            @endif
            <details id="hallazgos-observacionales" class="rm-expert-sources-section__detail" @if($analisisEjecutado) open @endif>
                <summary>Consultar hallazgos observacionales y sus reglas</summary>
            @if($errorObservacional)
                <x-ui.section-card title="No se pudieron consultar los hallazgos observacionales" icon="ph-warning-circle"><p>Vuelva a intentar la consulta.</p><button type="button" wire:click="reintentar" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary mt-3">Reintentar</button></x-ui.section-card>
            @elseif($hallazgosObservacionales)
                @include('apoyodecision::livewire.medico.resultados-experto.hallazgos-observacionales')
                @if($revisionesObservacionales)
                    @include('apoyodecision::livewire.medico.resultados-experto.revision-profesional', ['revisiones' => $revisionesObservacionales, 'esRevisionObservacional' => true])
                @endif
            @endif
            </details>
            <details id="aplicaciones-instrumentales" class="rm-expert-sources-section__detail">
                <summary>Consultar aplicaciones, respuestas y componentes</summary>
            @if($errorInstrumental)
                <x-ui.section-card title="No se pudieron consultar las mediciones instrumentales" icon="ph-warning-circle"><p>Vuelva a intentar la consulta.</p><button type="button" wire:click="reintentar" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary mt-3">Reintentar</button></x-ui.section-card>
            @elseif($capturaInstrumental && $capturaInstrumental['disponible'])
                @include('apoyodecision::livewire.medico.resultados-experto.perfil-instrumental')
                @include('apoyodecision::livewire.medico.resultados-experto.captura-instrumental')
            @else
                <p class="rm-orion-note">No hay aplicaciones instrumentales con respuestas disponibles para consultar en este momento.</p>
            @endif
            </details>
            </details>
            @if($errorLectura)
                <x-ui.section-card title="No se pudo consultar la evaluación" icon="ph-warning-circle">
                    <p>La información no está disponible en este momento. Vuelva a intentar la consulta.</p>
                    <button type="button" wire:click="reintentar" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary mt-4">Reintentar</button>
                </x-ui.section-card>
            @elseif(($datos['evaluacion'] ?? null) && $informe)
                @php($narrativa = $informe['narrativa'])
                <section class="rm-card rm-orion-panel rm-expert-report-preview" aria-labelledby="informe-clinico-titulo">
                    <div class="rm-expert-report-preview__heading">
                        <div>
                            <p class="rm-expert-eyebrow">Informe de la evaluación consultada</p>
                            <h2 id="informe-clinico-titulo" class="rm-expert-section-title">Lectura clínica y fundamentos</h2>
                        </div>
                        <span class="rm-expert-report-preview__icon" aria-hidden="true"><i class="ph ph-file-text"></i></span>
                    </div>
                    <p class="rm-expert-prose">{{ $narrativa['resumen'] }}</p>
                    @if(!$narrativa['completa'] && count($narrativa['faltantes']) > 0)
                        <p class="rm-expert-report-preview__pending"><i class="ph ph-info" aria-hidden="true"></i><span><strong>Información por completar:</strong> {{ $narrativa['faltantes'][0]['titulo'] }}</span></p>
                    @endif
                    <div class="rm-expert-report-preview__footer">
                        <p>Consulte las funciones cognitivas, las fuentes, la autonomía y los límites de esta evaluación en el informe completo.</p>
                        <button type="button" wire:click="abrirInformeCompleto" wire:loading.attr="disabled" wire:target="abrirInformeCompleto" class="rm-btn rm-btn-primary"><i class="ph ph-book-open" aria-hidden="true"></i>Leer informe completo</button>
                    </div>
                </section>
                @if(isset($revisiones['PSICOLOGIA']['estado'], $revisiones['MEDICINA']['estado']))
                    @include('apoyodecision::livewire.medico.resultados-experto.revision-profesional', ['esRevisionObservacional' => false])
                @endif
                @if($puedeAuditar && $datos['evaluacion'])<div><button type="button" x-ref="traceTrigger" wire:click="verTrazabilidad" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary">Consultar auditoría técnica</button></div>@endif
                <p class="text-sm text-rm-secondary">{{ $informe['limite_global'] }} {{ $informe['limite_revision'] }}</p>
                @if($simulacion)
                    <p class="text-sm text-rm-secondary" role="note">{{ $informe['procedencia_visible'] }}</p>
                @endif
            @endif
        </div>


    </div>

    @include('apoyodecision::livewire.medico.resultados-experto.consulta')

    @if($puedeAuditar && $datos && $trazabilidadAbierta)
        <x-ui.drawer-livewire wire:model="trazabilidadAbierta" title="Auditoría técnica de la evaluación" size="xl" closeMethod="cerrarTrazabilidad">
            <p class="text-sm break-words">Evaluación {{ $datos['evaluacion']['id'] ?? 'No disponible' }} · Versión {{ $datos['evaluacion']['version'] ?? 'No disponible' }}</p>
            @php($detalle = $datos['detalle'])
            @if($simulacion)
                @include('apoyodecision::livewire.medico.resultados-experto.traza-simulacion', ['abierto' => true])
                @include('apoyodecision::livewire.medico.resultados-experto.instantanea-simulacion')
            @else
                @include('livewire.medico.resultados-experto.fundamento', ['abierto' => true])
            @endif
        </x-ui.drawer-livewire>
    @endif
    @endif
</div>
