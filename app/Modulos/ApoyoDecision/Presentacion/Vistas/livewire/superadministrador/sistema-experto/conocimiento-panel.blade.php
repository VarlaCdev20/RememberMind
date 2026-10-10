<div class="space-y-6 text-rm-primary">
    <x-ui.page-header title="Revisión del sistema experto" subtitle="Conocimiento versionado y trazabilidad técnica" icon="ph-graph" eyebrow="Gestión del sistema" context="Solo consulta" />

    <div class="rm-alert rm-alert-info" role="status">
      <div>
        <strong>Activación clínica pendiente.</strong>
        <p>Las demostraciones utilizan datos artificiales en memoria. No consultan residentes, no guardan evaluaciones y no generan diagnósticos, tratamientos ni alertas.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
        <x-ui.section-card title="Versiones del conocimiento" subtitle="El estado activo no acredita aprobación clínica.">
            @forelse($versiones as $v)
                <button type="button" wire:click="seleccionarVersion(@js($v->cod_version_modelo))" wire:loading.attr="disabled"
                    class="rm-btn rm-btn-secondary w-full min-h-11 mb-3 text-left whitespace-normal" aria-pressed="{{ $version === $v->cod_version_modelo ? 'true' : 'false' }}">
                    <span class="min-w-0 break-words"><strong class="block">{{ $v->nombre }}</strong><span class="text-sm">{{ $v->codigo_version }} · {{ $v->estado }}</span></span>
                </button>
            @empty
                <x-ui.empty-state compact title="Aún no hay conocimiento cargado" description="Las 23 tablas expertas están disponibles. La carga de conocimiento requiere sus contratos y aprobación institucional." icon="ph-graph" />
            @endforelse
            @if($versiones->hasPages()){{ $versiones->links() }}@endif
        </x-ui.section-card>

        <x-ui.section-card title="Explorar el conocimiento" subtitle="Seleccione una versión para revisar sus conceptos y reglas." class="xl:col-span-2 min-w-0">
            <div wire:loading role="status" class="text-sm mb-3">Actualizando consulta…</div>
            @if($errorConocimiento)
                <div class="rm-alert rm-alert-danger" role="alert"><div><strong>Inconsistencia del conocimiento</strong><p>{{ $errorConocimiento }}</p></div></div>
            @elseif($version === null)
                <x-ui.empty-state compact title="Seleccione una versión" description="Podrá consultar nodos, relaciones, variables, dominios, mapeos y reglas de esa versión." />
            @else
                <p class="text-sm mb-4">Versión: <strong>{{ $version }}</strong>. Validación semántica pendiente de contratos aprobados. Esta consulta no certifica la validez clínica del contenido.</p>
                @foreach([
                    'nodos_semanticos' => 'Nodos y conceptos', 'relaciones_semanticas' => 'Relaciones y procedencia',
                    'variables_expertas' => 'Variables', 'dominios_valores_expertos' => 'Dominios de valores',
                    'valores_semanticos' => 'Valores y aprobación', 'fuentes_datos_expertas' => 'Fuentes operacionales',
                    'mapeos_variables_fuente' => 'Mapeos de variables', 'mapeos_valores_fuente' => 'Mapeos exactos y aprobación',
                    'criterios_dominios_resultado' => 'Dominios de resultados por criterio', 'reglas_expertas' => 'Reglas',
                    'condiciones_regla_experta' => 'Condiciones positivas', 'consecuencias_regla_experta' => 'Consecuencias',
                ] as $tabla => $titulo)
                    <details class="border-b border-rm py-2">
                        <summary class="min-h-11 py-3 cursor-pointer font-semibold">{{ $titulo }} <span class="text-sm font-normal">({{ count($filas[$tabla] ?? []) }})</span></summary>
                        <div class="space-y-3 py-3">
                            @forelse($filas[$tabla] ?? [] as $fila)
                                <dl class="rounded-xl bg-rm-surface-soft p-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                    @foreach($fila as $campo => $valor)
                                        <div class="min-w-0"><dt class="text-rm-secondary">{{ str_replace('_', ' ', $campo) }}</dt><dd class="break-words font-medium">{{ $valor ?? 'No definido' }}</dd></div>
                                    @endforeach
                                </dl>
                            @empty
                                <p class="text-sm text-rm-secondary">No hay {{ mb_strtolower($titulo) }} en esta versión.</p>
                            @endforelse
                        </div>
                    </details>
                @endforeach
            @endif
        </x-ui.section-card>
    </div>

    <x-ui.section-card title="Criterios preparados para formalización" subtitle="Manifiestos inactivos en memoria · no cargados en la base de datos">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($candidatos as $candidato)
                <details class="rounded-xl border border-rm p-4 min-w-0">
                    <summary class="min-h-11 cursor-pointer font-semibold">{{ $candidato['codigo'] }} · {{ $candidato['nombre'] }} · Inactivo</summary>
                    <p class="text-sm my-3">{{ $candidato['motivo'] }}</p>
                    <p class="text-sm text-rm-secondary">Estado documental: {{ $candidato['estado_documental'] }}. Subcomponentes candidatos:</p>
                    <ul class="list-disc pl-5 my-3 text-sm space-y-2">
                        @foreach($candidato['subcomponentes_candidatos'] as $codigo => $nombre)<li>{{ $codigo }} · {{ $nombre }}</li>@endforeach
                    </ul>
                    <p class="text-sm text-rm-secondary">Fuente: {{ $candidato['fuente'] }} · D-108 / D-135.</p>
                </details>
            @endforeach
        </div>
    </x-ui.section-card>

    <x-ui.section-card title="Demostración técnica COG-MEM" subtitle="Caso artificial · sin guardar · sin validación clínica">
        <p class="text-sm text-rm-secondary mb-4">Seleccione un caso para seguir el resultado hasta sus reglas, condiciones, mapeos y fuentes artificiales. Los literales de prueba no son una escala médica.</p>
        <div class="flex flex-wrap gap-2 mb-4" aria-label="Casos técnicos">
            @foreach(\App\Backend\Modulos\SistemaExperto\Servicios\DemostracionCOGMEM::CASOS as $codigo => $nombre)
                <button type="button" wire:click="seleccionarCaso('{{ $codigo }}')" wire:loading.attr="disabled"
                    class="rm-btn {{ $caso === $codigo ? 'rm-btn-primary' : 'rm-btn-secondary' }} min-h-11 whitespace-normal" aria-pressed="{{ $caso === $codigo ? 'true' : 'false' }}">{{ $nombre }}</button>
            @endforeach
        </div>
        @if($errorDemo)
            <div class="rm-alert rm-alert-danger" role="alert"><div><strong>Inconsistencia de cobertura detectada</strong><p>{{ $errorDemo }}</p><p>El motor rechaza el caso artificial. No emite un resultado favorable por defecto.</p></div></div>
        @elseif($demo)
            <div class="rounded-xl bg-rm-surface-soft p-4 mb-4" aria-live="polite">
                <p><strong>Evaluabilidad:</strong> {{ $demo['evaluabilidad']['estado'] }}</p>
                <p><strong>Resultado técnico:</strong> {{ $demo['resultado']['codigo'] ?? 'No emitido: contexto insuficiente.' }}</p>
                @if($demo['evaluabilidad']['modificador'])<p><strong>Modificador de interpretación:</strong> {{ $demo['evaluabilidad']['modificador'] }}</p>@endif
                @if($demo['evaluabilidad']['contexto']['faltantes'])
                    <p class="mt-2 font-semibold">Contexto pendiente de verificar:</p>
                    <ul class="list-disc pl-5 text-sm">@foreach($demo['evaluabilidad']['contexto']['faltantes'] as $motivo)<li>{{ ['componente_validado' => 'Verificar el componente específico de memoria.', 'condiciones_aplicacion_conocidas' => 'Documentar las condiciones de aplicación.', 'confusores_revisados' => 'Completar la revisión de factores que pueden afectar la interpretación.'][$motivo] ?? $motivo }}</li>@endforeach</ul>
                @endif
                <p class="text-sm mt-2">Versión artificial: {{ $demo['traza']['version'] }} · Evaluación: {{ $demo['traza']['evaluacion'] }}</p>
            </div>
            @foreach($demo['traza']['reglas'] as $regla)
                <details class="border-b border-rm py-2">
                    <summary class="min-h-11 py-3 cursor-pointer font-semibold">Regla {{ $regla['regla'] }} · {{ $regla['estado'] }}</summary>
                    @foreach($regla['condiciones'] as $condicion)
                        <div class="py-3 text-sm space-y-2 break-words">
                            <p><strong>Condición {{ $condicion['condicion'] }}</strong> · {{ $condicion['estado'] }}</p>
                            <p>Variable {{ $condicion['variable'] }} · Valor exigido {{ $condicion['valor'] }}</p>
                            @foreach(array_filter($demo['traza']['rutas'], fn ($ruta) => $ruta['condicion'] === $condicion['condicion']) as $ruta)
                                <p>Evidencia {{ $ruta['evidencia'] }} → variable {{ $ruta['variable'] }} / valor {{ $ruta['valor'] }} → mapeos {{ $ruta['mapeo_variable'] }} / {{ $ruta['mapeo_valor'] }} → {{ $ruta['tabla'] }} / {{ $ruta['registro'] }}</p>
                            @endforeach
                            @if(empty($condicion['soportes']))<p class="text-rm-secondary">Sin evidencia que satisfaga esta condición.</p>@endif
                        </div>
                    @endforeach
                </details>
            @endforeach
        @endif
    </x-ui.section-card>
</div>
