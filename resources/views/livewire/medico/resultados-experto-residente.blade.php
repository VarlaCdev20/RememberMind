<div class="rm-expert-results space-y-5 text-rm-primary" x-data="{}"
    x-on:expert-trace-opened.window="$nextTick(() => document.getElementById('drawer-trazabilidadabierta-title')?.focus())"
    x-on:expert-trace-closed.window="$nextTick(() => $refs.traceTrigger.focus())">
    <header class="rm-expert-heading">
        <div class="min-w-0">
            <a class="rm-expert-back" href="{{ $enfermeria ? route('admin.enfermeria.pacientes', ['residente' => $codResidente]) : route('admin.medico.residente.ficha', ['adulto' => $codResidente]) }}"><i class="ph ph-arrow-left" aria-hidden="true"></i> {{ $enfermeria ? 'Volver a Mis residentes' : 'Volver a la ficha' }}</a>
            <h1>Resultados del sistema experto</h1>
            <p class="text-rm-secondary">Expediente del residente / Evaluación cognitiva</p>
        </div>
        <div class="rm-expert-actions">
            <button type="button" x-ref="traceTrigger" wire:click="verTrazabilidad" wire:loading.attr="disabled" @disabled(!$datos || !$datos['detalle']['traza']) class="rm-btn rm-btn-secondary"><i class="ph ph-tree-structure" aria-hidden="true"></i> Ver trazabilidad completa</button>
            @unless($enfermeria)
                <button type="button" disabled class="rm-btn rm-btn-secondary" aria-describedby="acciones-expertas-pendientes"><i class="ph ph-file-arrow-down" aria-hidden="true"></i> Exportar reporte</button>
                <button type="button" disabled class="rm-btn rm-btn-primary" aria-describedby="acciones-expertas-pendientes"><i class="ph ph-plus" aria-hidden="true"></i> Nueva evaluación</button>
            @endunless
        </div>
    </header>
    @unless($enfermeria)<p id="acciones-expertas-pendientes" class="text-xs text-rm-secondary">Exportación y nueva evaluación pendientes de un flujo institucional autorizado.</p>@endunless

    @if(app()->environment('testing'))
        <div class="rm-expert-test-note" role="note"><i class="ph ph-flask" aria-hidden="true"></i><span>Entorno de prueba · datos artificiales · sin validación clínica. @if($datos && $datos['prueba_tecnica']) Inferencia técnica ejecutada y conservada en una base separada. @endif</span></div>
    @endif
    @if($enfermeria)
        <p class="rm-expert-role-note" role="note"><i class="ph ph-lock-simple" aria-hidden="true"></i><span><strong>Consulta de Enfermería</strong> · Solo lectura de resultados, evidencias e historial del residente asignado.</span></p>
    @endif
    <div wire:loading role="status" class="text-sm">Consultando información guardada…</div>
    @if($errorLectura)
        <x-ui.section-card title="No se pudo consultar la evaluación" icon="ph-warning-circle">
            <p>La información no está disponible en este momento. Vuelva a intentar la consulta.</p>
            <button type="button" wire:click="reintentar" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary mt-4">Reintentar</button>
        </x-ui.section-card>
    @elseif($datos)
        @if(!$datos['evaluacion'])
            <div class="rm-alert rm-alert-info" role="status"><div><strong>Sin evaluación experta disponible</strong><p>Este residente todavía no cuenta con una evaluación generada para consulta mediante conocimiento autorizado.@if($datos['conocimiento_documental']) Las definiciones de O.R.I.O.N. ya están cargadas; la carga no genera por sí sola una conclusión clínica.@endif</p></div></div>
        @endif
        <section class="rm-card rm-expert-context" aria-label="Contexto del residente">
            <div class="rm-expert-context-grid">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="rm-expert-avatar" aria-hidden="true"><i class="ph ph-user text-2xl"></i></span>
                    <div class="min-w-0"><h2 class="text-lg font-bold break-words">{{ $datos['residente']['nombre'] }}</h2><p class="text-sm text-rm-secondary break-all">Código: {{ $datos['residente']['codigo'] }}</p></div>
                </div>
                <dl><dt class="text-sm text-rm-secondary"><i class="ph ph-user" aria-hidden="true"></i> Edad actual</dt><dd class="font-semibold">{{ $datos['residente']['edad'] !== null ? $datos['residente']['edad'].' años' : 'No disponible' }}</dd></dl>
                <dl><dt class="text-sm text-rm-secondary"><i class="ph ph-calendar-blank" aria-hidden="true"></i> Fecha de evaluación</dt><dd class="font-semibold">{{ $datos['evaluacion']['fecha'] ?? 'Sin evaluación disponible' }}</dd></dl>
                <dl class="min-w-0"><dt class="text-sm text-rm-secondary"><i class="ph ph-gear" aria-hidden="true"></i> Versión del conocimiento</dt><dd class="font-semibold break-words">{{ $datos['evaluacion']['version'] ?? 'Sin versión utilizada' }}</dd></dl>
            </div>
            @if($datos['evaluacion'])
                <p class="text-xs text-rm-secondary mt-2 break-words">Estado de ejecución registrado: {{ $datos['evaluacion']['estado'] }}@if($datos['evaluacion']['solicitante']) · Solicitante: {{ $datos['evaluacion']['solicitante'] }}@endif</p>
            @endif
        </section>

        <div class="rm-expert-workspace">
            <x-ui.section-card title="Perfil cognitivo" subtitle="Seleccione un área" icon="ph-brain" class="rm-expert-profile min-w-0">
                <nav class="rm-expert-criteria" aria-label="Áreas cognitivas">
                    @foreach($datos['perfil'] as $codigo => $area)
                        <button type="button" wire:key="criterio-{{ $codigo }}" wire:click="seleccionarCriterio(@js($codigo))" wire:loading.attr="disabled" aria-pressed="{{ $criterio === $codigo ? 'true' : 'false' }}" aria-controls="detalle-cognitivo"
                            class="rm-expert-criterion rm-expert-tone-{{ \App\Backend\Modulos\SistemaExperto\Presentacion\LenguajeResultadosExperto::presentacion($area['resultado']['codigo'] ?? null)['tono'] }} {{ $criterio === $codigo ? 'is-selected' : '' }} text-left min-w-0">
                            <span class="rm-expert-criterion-heading"><span class="rm-expert-area-icon"><i class="ph {{ $area['icono'] }}" aria-hidden="true"></i></span><span class="min-w-0"><span class="block font-semibold">{{ $area['nombre'] }}</span><span class="block text-xs text-rm-secondary mt-1">{{ $codigo }}</span></span><i class="ph ph-caret-right" aria-hidden="true"></i></span>
                            <span class="rm-expert-status">{{ $area['estado'] }}</span>
                            <span class="block text-xs text-rm-secondary mt-2">{{ $area['activacion'] }}</span>
                        </button>
                    @endforeach
                </nav>
            </x-ui.section-card>

            <section id="detalle-cognitivo" class="rm-card rm-expert-detail space-y-4 min-w-0" aria-labelledby="titulo-cognitivo" aria-live="polite" aria-atomic="false">
                @php($detalle = $datos['detalle'])
                @php($presentacion = \App\Backend\Modulos\SistemaExperto\Presentacion\LenguajeResultadosExperto::presentacion($detalle['resultado']['codigo'] ?? null))
                @if($datos['preparacion_memoria'])
                    @php($preparacion = $datos['preparacion_memoria'])
                    <x-ui.section-card title="Preparación de la valoración de memoria" subtitle="Datos disponibles y requisitos pendientes" icon="ph-list-checks">
                        <p class="text-sm leading-relaxed">Controles cognitivos vigentes hasta hoy: {{ $preparacion['controles'] }}. La observación registrada se conserva; todavía no se ha generado una valoración integrada de aprendizaje y memoria.</p>
                        @if($preparacion['aplicaciones'] !== null)
                            <p class="text-sm text-rm-secondary mt-3">Aplicaciones de instrumentos registradas: {{ $preparacion['aplicaciones'] }}. Estas aplicaciones no acreditan por sí solas un componente de memoria autorizado.</p>
                            @foreach($preparacion['instrumentos'] as $instrumento)<p class="text-sm mt-2 break-words">{{ $instrumento }}</p>@endforeach
                        @endif
                        <h3 class="font-semibold mt-4">Para completar la valoración</h3>
                        <ul class="list-disc pl-5 space-y-2 text-sm mt-3">
                            @foreach($preparacion['pendientes'] as $pendiente)<li>{{ $pendiente }}</li>@endforeach
                        </ul>
                        <p class="text-xs text-rm-secondary mt-4 break-words">{{ $preparacion['version'] }} · propuesta investigada, pendiente de validación profesional. Esta consulta no ejecuta inferencia ni emite un resultado clínico.</p>
                    </x-ui.section-card>
                @endif
                @if($datos['conocimiento_documental'])
                    <x-ui.section-card title="Conocimiento disponible" subtitle="{{ $datos['conocimiento_documental']['version'] }} · definición documental" icon="ph-books">
                        <h3 class="font-semibold">{{ $datos['conocimiento_documental']['nombre'] }}</h3>
                        <p class="text-sm leading-relaxed mt-2">{{ $datos['conocimiento_documental']['definicion'] }}</p>
                        <p class="text-sm text-rm-secondary mt-3">La definición orienta la revisión del área. Todavía no hay reglas clínicas y mapeos completos para generar un resultado con los datos de este expediente.</p>
                        <details class="mt-3"><summary class="cursor-pointer min-h-11 text-sm font-medium">Fuente y límites documentados</summary><p class="text-xs text-rm-secondary break-words mt-2">{{ $datos['conocimiento_documental']['procedencia'] }}</p></details>
                    </x-ui.section-card>
                @endif
                @if($datos['registros_cognitivos'])
                    <x-ui.section-card title="Registros cognitivos disponibles" subtitle="Últimos cinco controles vigentes del expediente" icon="ph-clipboard-text">
                        <p class="text-sm text-rm-secondary mb-4">Estos datos fueron registrados en el seguimiento del residente. Aún no han sido incorporados a una evaluación del sistema experto. Un indicador no marcado no demuestra ausencia de dificultad.</p>
                        <div class="space-y-3">
                            @foreach($datos['registros_cognitivos'] as $registro)
                                <details class="rounded-xl border border-rm bg-rm-surface-soft p-4 min-w-0" @if($loop->first) open @endif>
                                    <summary class="cursor-pointer min-h-11 font-semibold break-words">{{ $registro['fecha'] }} · {{ $registro['autor'] }}</summary>
                                    <p class="text-xs text-rm-secondary break-all mt-2">Registro: {{ $registro['codigo'] }}</p>
                                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                                        @foreach($registro['mediciones'] as $medicion)
                                            <div class="min-w-0"><dt class="text-sm text-rm-secondary">{{ $medicion['nombre'] }}</dt><dd class="text-sm font-semibold break-words">{{ $medicion['valor'] }}</dd></div>
                                        @endforeach
                                    </dl>
                                    <p class="text-sm mt-4 whitespace-pre-line break-words"><strong>Observación registrada:</strong> {{ $registro['observacion'] ?: 'Sin observación registrada' }}</p>
                                </details>
                            @endforeach
                        </div>
                    </x-ui.section-card>
                @endif
                @if($detalle['integridad'])
                    <div class="rm-alert rm-alert-danger" role="alert"><div><strong>No se publica el resultado: integridad pendiente</strong><p>El fundamento conservado está incompleto o contiene datos incompatibles. Consulte los avisos de esta evaluación.</p></div></div>
                @endif
                <div class="rm-expert-detail-heading rm-expert-tone-{{ $presentacion['tono'] }}"><span class="rm-expert-area-icon"><i class="ph {{ $detalle['icono'] }}" aria-hidden="true"></i></span><div class="min-w-0"><h2 id="titulo-cognitivo">{{ $detalle['nombre'] }}</h2><p class="text-sm text-rm-secondary">{{ $criterio }} · {{ $detalle['activacion'] }}</p></div><span class="rm-expert-review-state"><i class="ph ph-lock-simple" aria-hidden="true"></i> Revisión profesional aún no habilitada</span></div>
                <div class="rm-expert-panels">
                    <x-ui.section-card title="Resultado del sistema experto" icon="ph-list-checks" class="rm-expert-result rm-expert-tone-{{ $presentacion['tono'] }}">
                        <p class="rm-expert-conclusion">{{ $presentacion['resumen'] ?? $detalle['estado'] }}</p>
                        <p class="text-sm text-rm-secondary mt-3">{{ $detalle['evaluabilidad'] ?? 'No hay una evaluación guardada para esta área.' }}</p>
                        @if($detalle['modificador'])<p class="text-sm mt-3">{{ $detalle['modificador'] }}</p>@endif
                    </x-ui.section-card>
                    <x-ui.section-card title="Interpretación del sistema experto" icon="ph-file-text" class="rm-expert-interpretation">
                        <p class="text-sm leading-relaxed">{{ $detalle['interpretacion'] }}</p>
                        @if(!$detalle['autorizado'])<p class="text-sm text-rm-secondary mt-3">La publicación del conocimiento de esta versión requiere un contrato institucional aprobado.</p>@endif
                    </x-ui.section-card>
                    <x-ui.section-card title="¿Qué sustenta este resultado?" icon="ph-lightbulb" class="rm-expert-foundation-summary">
                        <ul class="text-sm space-y-2">
                            @forelse(array_filter($detalle['evidencias'], fn ($e) => $e['papel'] === 'Soporte de inferencia') as $e)
                                <li class="flex gap-2"><span aria-hidden="true">•</span><span>{{ $e['nombre'] }} · {{ $e['fuente'] }}</span></li>
                            @empty
                                <li>No se conserva soporte directo de un resultado integrado para esta área.</li>
                            @endforelse
                        </ul>
                        <p class="text-xs text-rm-secondary mt-3">El contexto y la compuerta de evaluabilidad no se presentan como hallazgos clínicos.</p>
                    </x-ui.section-card>
                    <x-ui.section-card title="Avisos y observaciones" icon="ph-warning" class="rm-expert-notices">
                        <ul class="text-sm space-y-2 list-disc pl-4">
                            @if(!$datos['evaluacion'])<li>No hay evaluaciones clínicas guardadas para este residente.</li>@endif
                            @if($detalle['motivo'])<li>{{ $detalle['motivo'] }}</li>@endif
                            @foreach($detalle['integridad'] as $aviso)<li>{{ $aviso }}</li>@endforeach
                            @if(!$detalle['autorizado'])<li>Conocimiento sin publicación clínica habilitada para esta área y versión.</li>@endif
                            <li>La revisión profesional no tiene un contrato de persistencia aprobado.</li>
                        </ul>
                    </x-ui.section-card>
                </div>
                @include('livewire.medico.resultados-experto.evidencias')
                @if($datos['informe_tecnico'])
                    @php($informe = $datos['informe_tecnico'])
                    <x-ui.section-card title="Verificación de la prueba técnica" subtitle="Informe generado al ejecutar el caso sintético de QA" icon="ph-check-circle">
                        <details><summary class="min-h-11 py-3 cursor-pointer font-semibold">Consultar respuestas y comprobaciones técnicas</summary>
                        <p class="text-sm text-rm-secondary">Este artefacto de pruebas conserva las entradas del caso artificial. No es una firma profesional ni completa la instantánea clínica del historial institucional.</p>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mt-4">
                            <div><dt class="text-rm-secondary">Fecha de corte de QA</dt><dd class="font-medium break-words">{{ $informe['fecha_corte'] }}</dd></div>
                            <div><dt class="text-rm-secondary">Fecha de la fuente artificial</dt><dd class="font-medium">{{ $informe['fecha_fuente'] }}</dd></div>
                            <div><dt class="text-rm-secondary">Instrumento y versión</dt><dd class="font-medium">{{ $informe['instrumento'] }} · {{ $informe['version_instrumento'] }}</dd></div>
                            <div><dt class="text-rm-secondary">Componente y método técnicos</dt><dd class="font-medium break-words">{{ $informe['componente'] }} · {{ $informe['metodo'] }}</dd></div>
                        </dl>
                        <h3 class="font-semibold mt-5">Respuestas originales del caso de pruebas</h3>
                        <dl class="space-y-3 mt-3 text-sm">
                            @foreach($informe['respuestas'] as $respuesta)<div><dt>{{ $respuesta['pregunta'] }}</dt><dd class="font-medium">{{ $respuesta['valor'] }}</dd></div>@endforeach
                        </dl>
                        <p class="text-sm mt-4 break-words">Patrón exacto → {{ $informe['valor_fuente'] }} → mapeo {{ $informe['mapeo'] }} → valor semántico {{ $informe['valor_semantico'] }}.</p>
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4 text-sm">
                            @foreach($informe['verificaciones'] as $nombre => $correcta)<li class="flex items-start gap-2"><i class="ph {{ $correcta ? 'ph-check-circle' : 'ph-warning-circle' }}" aria-hidden="true"></i><span>{{ $nombre }} · {{ $correcta ? 'Correcto' : 'Revisar' }}</span></li>@endforeach
                        </ul>
                        <p class="text-sm text-rm-secondary mt-4">Contexto artificial: componente y condiciones de aplicación comprobados técnicamente; revisión de confusores simulada para probar la compuerta. No acredita una valoración clínica real.</p>
                        </details>
                    </x-ui.section-card>
                @endif
                @include('livewire.medico.resultados-experto.fundamento')
                @unless($enfermeria)
                <x-ui.section-card title="Revisión profesional" icon="ph-user-circle">
                    <p id="revision-no-disponible" class="text-sm text-rm-secondary mb-4">Estas acciones están pendientes de un contrato institucional de registro. No se guardan revisiones ni observaciones desde esta pantalla.</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['Confirmar concordancia' => 'ph-check', 'Registrar discrepancia' => 'ph-x', 'Solicitar más evidencia' => 'ph-magnifying-glass', 'Solicitar reevaluación' => 'ph-arrow-clockwise', 'Agregar observación' => 'ph-chat-text'] as $accion => $icono)
                            <button type="button" disabled class="rm-btn {{ $loop->first ? 'rm-btn-primary' : 'rm-btn-secondary' }} min-h-11 whitespace-normal" aria-describedby="revision-no-disponible"><i class="ph {{ $icono }}" aria-hidden="true"></i>{{ $accion }}</button>
                        @endforeach
                    </div>
                    <label for="observacion-profesional" class="block text-sm font-medium mt-4 mb-2">Comentario del profesional · registro no habilitado</label>
                    <textarea id="observacion-profesional" disabled rows="2" class="rm-input w-full" placeholder="Escriba aquí sus observaciones clínicas, el motivo de una discrepancia o la información que considera necesaria" aria-describedby="revision-no-disponible"></textarea>
                </x-ui.section-card>
                @endunless
            </section>
        </div>
        @include('livewire.medico.resultados-experto.historial')
    @endif

    @if($datos)
        <x-ui.drawer-livewire wire:model="trazabilidadAbierta" title="Trazabilidad completa" subtitle="{{ $datos['detalle']['nombre'] }} · {{ $datos['evaluacion']['version'] ?? 'Sin evaluación' }}" badge="CONSULTA DE TRAZA GUARDADA" icon="ph-tree-structure" size="xl" closeMethod="cerrarTrazabilidad">
            @if($trazabilidadAbierta)
                @php($detalle = $datos['detalle'])
                <dl class="text-sm space-y-2 mb-4"><div><dt class="font-semibold">Ejecución</dt><dd class="break-all">{{ $datos['evaluacion']['id'] ?? 'Sin evaluación' }}</dd></div><div><dt class="font-semibold">Fecha de evaluación</dt><dd>{{ $datos['evaluacion']['fecha'] ?? 'No disponible' }}</dd></div></dl>
                @include('livewire.medico.resultados-experto.fundamento', ['abierto' => true])
            @endif
        </x-ui.drawer-livewire>
    @endif

</div>
