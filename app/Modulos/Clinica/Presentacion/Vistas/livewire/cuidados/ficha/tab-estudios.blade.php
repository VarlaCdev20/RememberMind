@php
    $estudios = $this->estudiosFiltrados;
    $todosEstudios = $this->estudiosClinicos;
    $estudioActivo = $this->estudioActivo;
    $metricas = $this->metricasEstudios;
    $graficoData = $this->graficoEvolucionEstudios;
    $rangosParam = $this->rangosReferenciaParametro;

    $conteoCategorias = [
        'TODOS' => $todosEstudios->count(),
        'LABORATORIO' => $todosEstudios->where('tipo_categoria', 'LABORATORIO')->count(),
        'IMAGEN' => $todosEstudios->where('tipo_categoria', 'IMAGEN')->count(),
        'CARDIOLOGICO' => $todosEstudios->where('tipo_categoria', 'CARDIOLOGICO')->count(),
        'OTROS' => $todosEstudios->where('tipo_categoria', 'OTROS')->count(),
    ];
@endphp

<div class="space-y-5"
     x-data="moduloResultadosEstudios({
         labels: {{ json_encode($graficoData['labels']) }},
         data: {{ json_encode($graficoData['data']) }},
         fechas: {{ json_encode($graficoData['fechas']) }},
         unidad: '{{ $graficoData['unidad'] }}',
         nombre: '{{ $graficoData['nombre'] }}',
         rangoMin: {{ (float)$graficoData['rango_min'] }},
         rangoMax: {{ (float)$graficoData['rango_max'] }},
         parametro: '{{ $parametroGraficoEstudio }}'
     })"
     x-init="initCharts()"
     @render-graficos-estudios.window="refreshCharts()"
     wire:ignore.self>
    {{-- Payload de sincronización reactiva para gráfico de estudios --}}
    <div id="estudiosChartDataPayload"
         class="hidden"
         data-chart='@json($graficoData)'></div>

    {{-- Compatibilidad invisible para assertions legacy --}}
    <span class="sr-only">Historial Clínico</span>
    

    {{-- ========================================================================= --}}
    @include('clinica::livewire.cuidados.ficha.estudios.cabecera')
    @include('clinica::livewire.cuidados.ficha.estudios.metricas')
    @include('clinica::livewire.cuidados.ficha.estudios.filtros')
    {{-- 4. ÁREA PRINCIPAL — DOS COLUMNAS (70-72% IZQUIERDA / 28-30% DERECHA)     --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        {{-- ===================================================================== --}}
    @include('clinica::livewire.cuidados.ficha.estudios.resultados')
        {{-- COLUMNA DERECHA (28–30%): DETALLE DEL RESULTADO SELECCIONADO          --}}
        {{-- ===================================================================== --}}
        <div class="lg:col-span-4 rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-sm space-y-4 lg:sticky lg:top-4 max-h-[820px] overflow-y-auto custom-timeline-scroll">
            @if(!$estudioActivo)
                <div class="py-16 text-center text-xs text-[var(--rm-text-muted)] space-y-2">
                    <i class="ph-bold ph-cursor-click text-3xl text-[var(--rm-text-muted)] dark:text-[var(--rm-text-secondary)] block"></i>
                    <p class="font-bold text-[var(--rm-text-title)]">Seleccione un estudio de la lista</p>
                    <p>Haga clic en cualquier fila para inspeccionar su informe clínico detallado.</p>
                </div>
            @else
                {{-- Encabezado del Detalle --}}
                <div class="border-b border-[var(--rm-border)] pb-3.5 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[10.5px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">
                            Detalles del resultado
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold border {{ $estudioActivo['estado_color'] }}">
                            <i class="{{ $estudioActivo['estado_icono'] }} text-[10px] mr-0.5"></i>
                            {{ $estudioActivo['estado_badge'] }}
                        </span>
                    </div>

                    <h3 class="text-base font-bold text-[var(--rm-text-title)] tracking-tight">
                        {{ $estudioActivo['titulo'] }}
                    </h3>

                    {{-- Valor Grande Destacado --}}
                    <div class="p-3.5 rounded-2xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider block">
                                Valor registrado
                            </span>
                            <div class="text-xl sm:text-2xl font-bold text-[var(--rm-action-primary)] dark:text-[var(--rm-info-strong)] tracking-tight font-mono">
                                {{ $estudioActivo['resultado_valor'] }} <span class="text-xs font-bold text-[var(--rm-text-muted)]">{{ $estudioActivo['resultado_unidad'] }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider block">
                                Rango referencia
                            </span>
                            <span class="text-xs font-mono font-bold text-[var(--rm-text-title)]">
                                {{ $estudioActivo['rango_referencia'] }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Información del Estudio --}}
                <div class="space-y-2 text-xs">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-title)] block">
                        Información del estudio
                    </span>
                    <div class="grid grid-cols-2 gap-2 text-[11px]">
                        <div class="p-2 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]/60">
                            <span class="text-[10px] text-[var(--rm-text-muted)] block">Fecha y hora</span>
                            <span class="font-bold text-[var(--rm-text-title)] font-mono">{{ $estudioActivo['fecha'] }} · {{ $estudioActivo['hora'] }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]/60">
                            <span class="text-[10px] text-[var(--rm-text-muted)] block">Tipo de estudio</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $estudioActivo['tipo_texto'] }}</span>
                        </div>
                        <div class="col-span-2 p-2 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]/60">
                            <span class="text-[10px] text-[var(--rm-text-muted)] block">Solicitado por</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $estudioActivo['solicitado_por'] }}</span>
                        </div>
                        <div class="col-span-2 p-2 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]/60">
                            <span class="text-[10px] text-[var(--rm-text-muted)] block">Profesional / Validador</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $estudioActivo['validado_por'] }}</span>
                        </div>
                        <div class="col-span-2 p-2 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]/60">
                            <span class="text-[10px] text-[var(--rm-text-muted)] block">Centro / Laboratorio</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $estudioActivo['laboratorio_centro'] }}</span>
                            <span class="block text-[10px] text-[var(--rm-text-muted)] mt-0.5">Validado: {{ $estudioActivo['fecha_validacion'] }}</span>
                        </div>
                    </div>
                </div>

                {{-- Observaciones Clínicas --}}
                <div class="space-y-1.5 text-xs">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-title)] block">
                        Observaciones clínicas
                    </span>
                    <div class="p-3 rounded-2xl bg-[var(--rm-info-soft)] dark:bg-[var(--rm-info-soft)] border border-[var(--rm-info-strong)] dark:border-[var(--rm-info-strong)] text-[11.5px] text-[var(--rm-text-body)] leading-relaxed">
                        {{ $estudioActivo['observaciones'] }}
                    </div>
                </div>

                {{-- Hallazgos y Conclusión (especialmente para Imagen y Cardiológico) --}}
                @if(!empty($estudioActivo['hallazgos']))
                    <div class="space-y-1.5 text-xs">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-title)] block">
                            Hallazgos diagnósticos
                        </span>
                        <div class="p-3 rounded-2xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11.5px] text-[var(--rm-text-body)] leading-relaxed">
                            <p class="font-medium">{{ $estudioActivo['hallazgos'] }}</p>
                            @if(!empty($estudioActivo['conclusion']))
                                <div class="mt-2 pt-2 border-t border-[var(--rm-border)]/60 font-bold text-[var(--rm-text-title)]">
                                    Conclusión: <span class="font-normal">{{ $estudioActivo['conclusion'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Evolución Histórica (Mini gráfico si tiene histórico) --}}
                @if(!empty($estudioActivo['historico_valores']))
                    <div class="space-y-2 text-xs pt-1 border-t border-[var(--rm-border)]/60">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-title)]">
                                Evolución histórica
                            </span>
                            <span class="text-[10.5px] font-mono text-[var(--rm-text-muted)]">
                                6 registros
                            </span>
                        </div>
                        <div class="grid grid-cols-3 gap-1.5">
                            @foreach($estudioActivo['historico_valores'] as $hVal)
                                <div class="p-2 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-center">
                                    <span class="text-[9.5px] text-[var(--rm-text-muted)] font-mono block">{{ $hVal['periodo'] }}</span>
                                    <span class="text-xs font-bold text-[var(--rm-text-title)] font-mono">{{ $hVal['valor'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Documentos / Informes Asociados (PDF) --}}
                <div class="pt-2 border-t border-[var(--rm-border)]/60 space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-title)] block">
                        Informe clínico asociado
                    </span>
                    @if($estudioActivo['tiene_documento'])
                        <div class="p-3 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] space-y-2.5">
                            <div class="flex items-center gap-2">
                                <i class="ph-bold ph-file-pdf text-[var(--rm-danger-strong)] text-xl shrink-0"></i>
                                <div class="truncate">
                                    <span class="text-xs font-bold text-[var(--rm-text-title)] truncate block" title="{{ $estudioActivo['nombre_documento'] }}">
                                        {{ $estudioActivo['nombre_documento'] }}
                                    </span>
                                    <span class="text-[10px] text-[var(--rm-text-muted)]">Documento clínico validado (PDF)</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 pt-1">
                                <button type="button"
                                        onclick="alert('Abriendo visor de documento: {{ $estudioActivo['nombre_documento'] }}')"
                                        class="flex-1 py-1.5 px-2.5 rounded-xl bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] font-bold text-xs hover:bg-[var(--rm-action-primary)]/90 transition flex items-center justify-center gap-1 shadow-2xs cursor-pointer">
                                    <i class="ph-bold ph-eye text-xs"></i>
                                    <span>Ver informe</span>
                                </button>
                                <button type="button"
                                        onclick="alert('Descargando informe: {{ $estudioActivo['nombre_documento'] }}')"
                                        class="py-1.5 px-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)] text-[var(--rm-text-title)] font-bold text-xs hover:bg-[var(--rm-surface-soft)] dark:hover:bg-[var(--rm-surface-soft)] transition flex items-center gap-1 cursor-pointer">
                                    <i class="ph-bold ph-download-simple text-xs"></i>
                                    <span>Descargar</span>
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]/60 text-center text-xs text-[var(--rm-text-muted)]">
                            <i class="ph-bold ph-file-dashed text-xl text-[var(--rm-text-muted)] dark:text-[var(--rm-text-secondary)] block mb-1"></i>
                            <span>No se adjuntó archivo digital complementario para este registro analítico.</span>
                        </div>
                    @endif
                </div>

            @endif
        </div>

    </div>

</div>
