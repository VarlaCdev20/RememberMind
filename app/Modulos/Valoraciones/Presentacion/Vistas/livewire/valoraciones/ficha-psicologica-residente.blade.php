<div class="space-y-6 text-rm-primary" x-data="{ tabActiva: @entangle('tab') }">
    {{-- 1. Cabecera Institucional --}}
    <header class="rm-expert-heading">
        <div class="min-w-0">
            <p class="rm-expert-eyebrow">Psicología / Residentes / {{ $residente->nombre_completo }}</p>
            <h1 class="text-xl font-bold tracking-tight">Ficha psicológica del residente</h1>
            <p class="text-xs text-[var(--rm-text-secondary)] mt-0.5">Seguimiento psicométrico, afectivo, funcional y articulación con el sistema experto Apoyo a la decisión</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.psicologia.evaluaciones') }}" class="rm-btn rm-btn-secondary text-xs">
                <i class="ph ph-arrow-left mr-1" aria-hidden="true"></i>Volver a residentes
            </a>
        </div>
    </header>

    {{-- Banner de Notificación de Éxito --}}
    @if($mensajeExito)
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center justify-between gap-3 shadow-2xs" role="alert">
            <div class="flex items-center gap-2.5">
                <i class="ph-bold ph-check-circle text-base text-emerald-600 shrink-0"></i>
                <span class="font-medium">{{ $mensajeExito }}</span>
            </div>
            <button type="button" wire:click="$set('mensajeExito', null)" class="text-emerald-700 hover:text-emerald-900 cursor-pointer">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>
    @endif

    {{-- 2. Contexto e Identidad del Residente --}}
    <section class="rm-card p-5 rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border)] shadow-sm space-y-4" aria-label="Contexto del residente">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5 min-w-0">
                <span class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] font-bold text-base shrink-0 shadow-2xs">
                    {{ mb_strtoupper(mb_substr($residente->nombres, 0, 1).mb_substr($residente->apellido_paterno ?? '', 0, 1)) }}
                </span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-[var(--rm-text-primary)] truncate">{{ $residente->nombre_completo }}</h2>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                            {{ $residente->estado }}
                        </span>
                    </div>
                    <p class="text-xs text-[var(--rm-text-secondary)] mt-0.5">
                        {{ $residente->edad !== null ? $residente->edad.' años' : 'Edad no registrada' }} · {{ $residente->ubicacion_formateada }} · CI: {{ $residente->numero_documento ?? 'No registrado' }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <a href="{{ route('admin.psicologia.residente.resultados-experto', $residente) }}" class="rm-btn rm-btn-primary text-xs shadow-2xs">
                    <i class="ph ph-brain mr-1.5 text-sm" aria-hidden="true"></i>Informe Apoyo a la decisión
                </a>
                @if($puedeValorarPetersen)
                    <a href="{{ route('admin.sistema-experto.petersen-original', $residente) }}" class="rm-btn rm-btn-secondary text-xs">
                        <i class="ph ph-scales mr-1.5 text-sm" aria-hidden="true"></i>Juicios Petersen
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- 3. Barra de Pestañas Didácticas --}}
    <nav class="flex items-center gap-2 sm:gap-4 border-b border-[var(--rm-border)] text-xs font-bold overflow-x-auto pb-1" aria-label="Pestañas de la ficha psicológica">
        @php
            $pestanas = [
                ['id' => 'resumen', 'label' => 'Resumen', 'icono' => 'ph-squares-four'],
                ['id' => 'cognitiva', 'label' => 'Cognitiva', 'icono' => 'ph-brain'],
                ['id' => 'afectiva', 'label' => 'Afectiva y emocional', 'icono' => 'ph-heart'],
                ['id' => 'funcionamiento', 'label' => 'Funcionamiento (IADL)', 'icono' => 'ph-person-simple-walk'],
                ['id' => 'entorno', 'label' => 'Entorno y red social', 'icono' => 'ph-users-four'],
                ['id' => 'seguimiento', 'label' => 'Sesiones y seguimiento', 'icono' => 'ph-calendar-check'],
                ['id' => 'informes', 'label' => 'Informes y resultados', 'icono' => 'ph-file-text'],
                ['id' => 'orion', 'label' => 'Resultados Apoyo a la decisión', 'icono' => 'ph-sparkle'],
            ];
        @endphp

        @foreach($pestanas as $p)
            <button type="button"
                    wire:click="cambiarTab('{{ $p['id'] }}')"
                    class="pb-2.5 px-1.5 transition-colors relative flex items-center gap-1.5 whitespace-nowrap cursor-pointer {{ ($tab === $p['id'] || ($p['id'] === 'seguimiento' && $tab === 'sesiones')) ? 'border-b-2 border-[var(--rm-action-primary)] text-[var(--rm-action-primary)]' : 'text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)]' }}">
                <i class="ph {{ $p['icono'] }} text-sm"></i>
                <span>{{ $p['label'] }}</span>
            </button>
        @endforeach
    </nav>

    {{-- 4. Contenido por Pestaña --}}
    <div class="space-y-4">
        {{-- ==========================================
             PESTAÑA 1: RESUMEN PSICOLÓGICO
             ========================================== --}}
        @if($tab === 'resumen')
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] space-y-1">
                    <p class="text-[11px] font-medium text-[var(--rm-text-secondary)]">Pruebas instrumentales</p>
                    <p class="text-xl font-bold text-[var(--rm-text-primary)]">{{ $totalInstrumentos }}</p>
                    <p class="text-[10px] text-[var(--rm-text-secondary)]">Baterías registradas</p>
                </div>
                <div class="p-3.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] space-y-1">
                    <p class="text-[11px] font-medium text-[var(--rm-text-secondary)]">Valoraciones clínicas</p>
                    <p class="text-xl font-bold text-[var(--rm-text-primary)]">{{ $totalValoraciones }}</p>
                    <p class="text-[10px] text-[var(--rm-text-secondary)]">Formulaciones profesionales</p>
                </div>
                <div class="p-3.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] space-y-1">
                    <p class="text-[11px] font-medium text-[var(--rm-text-secondary)]">Controles IADL</p>
                    <p class="text-xl font-bold text-[var(--rm-text-primary)]">{{ $puedeConsultarIadl ? $totalIadl : 'Sin acceso' }}</p>
                    <p class="text-[10px] text-[var(--rm-text-secondary)]">Actividades observadas</p>
                </div>
                <div class="p-3.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] space-y-1">
                    <p class="text-[11px] font-medium text-[var(--rm-text-secondary)]">Sesiones realizadas</p>
                    <p class="text-xl font-bold text-[var(--rm-text-primary)]">{{ $totalSesiones }}</p>
                    <p class="text-[10px] text-[var(--rm-text-secondary)]">Intervenciones registradas</p>
                </div>
            </div>

            {{-- ==========================================
                 SECCIÓN: TRAYECTORIA Y TENDENCIAS LONGITUDINALES
                 ========================================== --}}
            <x-ui.section-card title="Trayectoria y tendencias longitudinales" subtitle="Evolución temporal de evaluaciones, afecto y capacidad funcional" icon="ph-chart-line-up">
                <div class="space-y-4">
                    {{-- Barra de Controles: Métrica y Período --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[var(--rm-border)]">
                        {{-- Selector de Dimensión Clínica --}}
                        <div class="flex items-center gap-1.5 p-1 rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border)] text-xs overflow-x-auto">
                            <button type="button"
                                    wire:click="cambiarMetricaTendencia('cognitivo')"
                                    class="px-2.5 py-1 rounded-lg font-semibold transition-colors flex items-center gap-1.5 whitespace-nowrap {{ $metricaTendencia === 'cognitivo' ? 'bg-[var(--rm-surface)] text-[var(--rm-action-primary)] shadow-2xs border border-[var(--rm-border)]' : 'text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)]' }}">
                                <i class="ph ph-brain"></i>
                                <span>Rendimiento cognitivo</span>
                            </button>
                            <button type="button"
                                    wire:click="cambiarMetricaTendencia('afectivo')"
                                    class="px-2.5 py-1 rounded-lg font-semibold transition-colors flex items-center gap-1.5 whitespace-nowrap {{ $metricaTendencia === 'afectivo' ? 'bg-[var(--rm-surface)] text-[var(--rm-action-primary)] shadow-2xs border border-[var(--rm-border)]' : 'text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)]' }}">
                                <i class="ph ph-heart"></i>
                                <span>Estado afectivo</span>
                            </button>
                            <button type="button"
                                    wire:click="cambiarMetricaTendencia('iadl')"
                                    class="px-2.5 py-1 rounded-lg font-semibold transition-colors flex items-center gap-1.5 whitespace-nowrap {{ $metricaTendencia === 'iadl' ? 'bg-[var(--rm-surface)] text-[var(--rm-action-primary)] shadow-2xs border border-[var(--rm-border)]' : 'text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)]' }}">
                                <i class="ph ph-person-simple-walk"></i>
                                <span>Autonomía IADL</span>
                            </button>
                        </div>

                        {{-- Selector de Período Temporal --}}
                        <div class="flex items-center gap-1 text-xs self-end sm:self-auto">
                            <span class="text-[11px] text-[var(--rm-text-secondary)] mr-1">Período:</span>
                            @foreach([3 => '3M', 6 => '6M', 12 => '1A', 0 => 'Todo'] as $meses => $etiqueta)
                                <button type="button"
                                        wire:click="cambiarPeriodoTendencia({{ $meses }})"
                                        class="px-2 py-0.5 rounded-md font-mono text-[11px] font-bold transition {{ $periodoMeses === $meses ? 'bg-[var(--rm-action-primary)] text-white shadow-2xs' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)] border border-[var(--rm-border)]' }}">
                                    {{ $etiqueta }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    @if($datosTendencia === null)
                        <p class="rm-explorador__nota" role="status">No tienes permiso para consultar esta fuente de información. Selecciona otra dimensión.</p>
                    @else
                    {{-- Síntesis Clínica del Período --}}
                    <div class="p-3.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-[var(--rm-text-primary)]">Interpretación clínica del intervalo:</span>
                                @if($datosTendencia['resumen']['direccion'] === 'MEJORIA' || $datosTendencia['resumen']['direccion'] === 'REDUCCION_SINTOMAS')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Evolución favorable
                                    </span>
                                @elseif($datosTendencia['resumen']['direccion'] === 'ESTABLE')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Estabilidad longitudinal
                                    </span>
                                @elseif($datosTendencia['resumen']['direccion'] === 'DESCENSO' || $datosTendencia['resumen']['direccion'] === 'INCREMENTO_SINTOMAS')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        Atención / Seguimiento
                                    </span>
                                @elseif($datosTendencia['resumen']['direccion'] === 'BASAL')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                        Medición basal
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Sin datos en el período
                                    </span>
                                @endif
                            </div>
                            <p class="text-[var(--rm-text-secondary)] leading-relaxed">
                                {{ $datosTendencia['resumen']['interpretacion'] }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button"
                                    wire:click="toggleTablaTendencia"
                                    class="rm-btn rm-btn-secondary text-xs">
                                <i class="ph {{ $mostrarTablaTendencia ? 'ph-chart-line' : 'ph-table' }} mr-1"></i>
                                {{ $mostrarTablaTendencia ? 'Ocultar tabla' : 'Ver tabla de datos' }}
                            </button>
                        </div>
                    </div>

                    {{-- Visualización Gráfica o Estado Límite --}}
                    @if(!$datosTendencia['tieneDatos'])
                        <div class="py-10 text-center space-y-2 border border-dashed border-[var(--rm-border)] rounded-2xl bg-[var(--rm-surface)]/50">
                            <i class="ph ph-chart-line-up text-3xl text-[var(--rm-text-secondary)]/50"></i>
                            <p class="text-xs text-[var(--rm-text-secondary)] max-w-md mx-auto">
                                No se registran evaluaciones para esta dimensión en el intervalo seleccionado.
                            </p>
                            @if($metricaTendencia === 'cognitivo')
                                <a href="{{ route('admin.psicologia.evaluacion.cognitiva') }}" class="rm-btn rm-btn-secondary text-xs mt-2">
                                    <i class="ph ph-plus mr-1"></i>Aplicar prueba cognitiva
                                </a>
                            @elseif($metricaTendencia === 'afectivo')
                                <button type="button" wire:click="cambiarTab('afectiva')" class="rm-btn rm-btn-secondary text-xs mt-2">
                                    <i class="ph ph-plus mr-1"></i>Registrar valoración afectiva
                                </button>
                            @else
                                <button type="button" wire:click="cambiarTab('funcionamiento')" class="rm-btn rm-btn-secondary text-xs mt-2">
                                    <i class="ph ph-plus mr-1"></i>Registrar observación IADL
                                </button>
                            @endif
                        </div>
                    @elseif(!$datosTendencia['suficienteParaTendencia'])
                        <div class="p-6 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] space-y-2 text-center">
                            <i class="ph ph-clock-countdown text-3xl text-[var(--rm-action-primary)]"></i>
                            <h4 class="font-bold text-sm text-[var(--rm-text-primary)]">Medición Basal Registrada</h4>
                            <p class="text-xs text-[var(--rm-text-secondary)] max-w-lg mx-auto">
                                Se cuenta con 1 registro formal ({{ $datosTendencia['resumen']['primerFecha'] }}). Para trazar una trayectoria temporal continua y calcular pendiente clínica se requieren al menos dos puntos de medición.
                            </p>
                        </div>
                    @else
                        {{-- Gráfico Interactivo RMCharts --}}
                        <div wire:ignore
                             x-data="{
                                chartKey: 'residente-tendencia-{{ $residente->getKey() }}',
                                datos: @js($datosTendencia),
                                init() {
                                    this.renderChart();
                                    window.RMCharts?.onThemeChange(() => this.renderChart(), this.chartKey, this.$el);
                                    this.$watch('datos', () => this.renderChart());
                                },
                                renderChart() {
                                    if (typeof Chart === 'undefined' || !window.RMCharts?.presets) {
                                        setTimeout(() => this.renderChart(), 80);
                                        return;
                                    }
                                    const canvas = this.$refs.chartCanvas;
                                    if (!canvas || !this.datos.tieneDatos || !this.datos.suficienteParaTendencia) return;

                                    const tone = this.datos.datasets[0]?.tone || 'cognitive';
                                    const customOptions = {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                display: this.datos.datasets.length > 1,
                                                position: 'bottom',
                                                labels: {
                                                    boxWidth: 10,
                                                    padding: 12,
                                                    font: { size: 11, weight: '600' }
                                                }
                                            },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(ctx) {
                                                        const ds = ctx.dataset;
                                                        const val = ctx.parsed.y;
                                                        return ` ${ds.label}: ${val}${ds.max ? ' / ' + ds.max : ''}`;
                                                    }
                                                }
                                            }
                                        },
                                        scales: {
                                            x: {
                                                grid: { display: false }
                                            },
                                            y: {
                                                beginAtZero: true,
                                                max: this.datos.datasets[0]?.max || undefined,
                                                ticks: { precision: 0 }
                                            }
                                        }
                                    };

                                    const config = window.RMCharts.presets.semantic(
                                        'line',
                                        tone,
                                        this.datos.labels,
                                        this.datos.datasets,
                                        customOptions
                                    );

                                    window.RMCharts.init(this.chartKey, canvas, config, () => this.renderChart());
                                }
                             }"
                             x-effect="datos = @js($datosTendencia); renderChart();"
                             class="relative w-full h-64 sm:h-72 p-2 rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                            <canvas x-ref="chartCanvas" aria-label="Evolución longitudinal de {{ $metricaTendencia }}" role="img"></canvas>
                        </div>
                    @endif

                    {{-- Tabla Complementaria Accesible (WCAG 2.1 AA) --}}
                    @if($mostrarTablaTendencia && !empty($datosTendencia['tablaDetalle']))
                        <div class="mt-3 overflow-x-auto rounded-xl border border-[var(--rm-border)]">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)] border-b border-[var(--rm-border)]">
                                    <tr>
                                        <th class="p-2.5">Fecha y hora</th>
                                        <th class="p-2.5">Indicador / Batería</th>
                                        <th class="p-2.5">Resultado</th>
                                        <th class="p-2.5">Evaluador</th>
                                        <th class="p-2.5">Detalle / Interpretación</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[var(--rm-border)]">
                                    @foreach($datosTendencia['tablaDetalle'] as $fila)
                                        <tr class="hover:bg-[var(--rm-surface-soft)]/50 transition">
                                            <td class="p-2.5 font-mono text-[11px] text-[var(--rm-text-secondary)]">{{ $fila['fecha_hora'] }}</td>
                                            <td class="p-2.5 font-semibold text-[var(--rm-text-primary)]">{{ $fila['indicador'] }}</td>
                                            <td class="p-2.5 font-mono font-bold">{{ $fila['valor'] }}</td>
                                            <td class="p-2.5 text-[var(--rm-text-secondary)]">{{ $fila['evaluador'] }}</td>
                                            <td class="p-2.5 text-[var(--rm-text-secondary)]">{{ $fila['detalle'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    @endif
                </div>
            </x-ui.section-card>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Estado Cognitivo Reciente --}}
                <x-ui.section-card title="Estado cognitivo reciente" subtitle="Última batería instrumental aplicada" icon="ph-brain">
                    @if($ultimaEvaluacionCognitiva)
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <strong class="font-bold text-sm text-[var(--rm-text-primary)]">{{ $ultimaEvaluacionCognitiva->instrumento->nombre ?? 'Test aplicado' }}</strong>
                                <span class="px-2 py-0.5 rounded bg-[var(--rm-surface-soft)] font-mono text-[10px] font-bold text-[var(--rm-text-secondary)] border border-[var(--rm-border)]">
                                    {{ $ultimaEvaluacionCognitiva->fecha_hora?->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <p class="text-[var(--rm-text-secondary)]">
                                Puntaje obtenido: <strong class="text-[var(--rm-text-primary)]">{{ $ultimaEvaluacionCognitiva->puntaje_total ?? 'Sin puntaje' }}</strong> / {{ $ultimaEvaluacionCognitiva->puntaje_maximo ?? '-' }} pts.
                            </p>
                            @if($ultimaEvaluacionCognitiva->evaluador?->usuario)
                                <p class="text-[11px] text-[var(--rm-text-secondary)]">
                                    Aplicado por: {{ $ultimaEvaluacionCognitiva->evaluador->usuario->nombre_completo ?? 'Personal autorizado' }}
                                </p>
                            @endif
                        </div>
                    @else
                        <p class="text-xs text-[var(--rm-text-secondary)] italic">Sin evaluaciones instrumentales cognitivas recientes registradas.</p>
                    @endif
                    <div class="pt-3 border-t border-[var(--rm-border)] flex items-center gap-2">
                        <button type="button" wire:click="abrirEvaluacion('ARE_COG')" class="rm-btn rm-btn-secondary text-xs">
                            <i class="ph ph-plus mr-1"></i>Aplicar prueba cognitiva
                        </button>
                    </div>
                </x-ui.section-card>

                {{-- Estado Afectivo Reciente --}}
                <x-ui.section-card title="Estado afectivo y conductual" subtitle="Última valoración psicológica documentada" icon="ph-heart">
                    @if($ultimaValoracionPsicologica)
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[var(--rm-text-primary)]">
                                    Ánimo: {{ $ultimaValoracionPsicologica->estado_animo ?? 'No documentado' }}
                                </span>
                                <span class="px-2 py-0.5 rounded bg-[var(--rm-surface-soft)] font-mono text-[10px] font-bold text-[var(--rm-text-secondary)] border border-[var(--rm-border)]">
                                    {{ $ultimaValoracionPsicologica->fecha_hora?->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            @if($ultimaValoracionPsicologica->ansiedad)
                                <p class="text-[var(--rm-text-secondary)]">Ansiedad: <span class="font-medium text-[var(--rm-text-primary)]">{{ $ultimaValoracionPsicologica->ansiedad }}</span></p>
                            @endif
                            @if($ultimaValoracionPsicologica->conclusion)
                                <p class="text-[var(--rm-text-secondary)] line-clamp-2">
                                    «{{ $ultimaValoracionPsicologica->conclusion }}»
                                </p>
                            @endif
                        </div>
                    @else
                        <p class="text-xs text-[var(--rm-text-secondary)] italic">Sin valoraciones clínicas afectivas documentadas.</p>
                    @endif
                    <div class="pt-3 border-t border-[var(--rm-border)] flex items-center gap-2">
                        <button type="button" wire:click="cambiarTab('afectiva')" class="rm-btn rm-btn-secondary text-xs">
                            <i class="ph ph-heart mr-1"></i>Ir a Valoración Afectiva
                        </button>
                    </div>
                </x-ui.section-card>
            </div>

        {{-- ==========================================
             PESTAÑA 2: ÁREA COGNITIVA
             ========================================== --}}
        @elseif($tab === 'cognitiva')
            <div class="space-y-4">
                <div class="p-4 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div class="space-y-1">
                        <strong class="font-bold text-[var(--rm-text-primary)] flex items-center gap-2">
                            <i class="ph ph-brain text-base text-[var(--rm-action-primary)]"></i>
                            Baterías cognitivas y vinculación con Apoyo a la decisión
                        </strong>
                        <p class="text-[var(--rm-text-secondary)] max-w-2xl">
                            Las pruebas aplicadas en esta sección alimentan los 5 dominios neurocognitivos (Memoria, Atención, Ejecutivas, Lenguaje, Visuoespacial) y los juicios originales de Petersen.
                        </p>
                    </div>
                    <button type="button" wire:click="abrirEvaluacion('ARE_COG')" class="rm-btn rm-btn-primary text-xs shrink-0">
                        <i class="ph ph-plus mr-1"></i>Explorar instrumentos cognitivos
                    </button>
                </div>

                <x-ui.section-card title="Pruebas cognitivas aplicadas" subtitle="Historial de mediciones estandarizadas" icon="ph-list-checks">
                    @if($evaluacionesCognitivas->isEmpty())
                        <p class="text-xs text-[var(--rm-text-secondary)] italic py-2">No hay pruebas cognitivas aplicadas para este residente.</p>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-[var(--rm-border)]">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)] border-b border-[var(--rm-border)]">
                                    <tr>
                                        <th class="p-3">Instrumento</th>
                                        <th class="p-3">Fecha</th>
                                        <th class="p-3">Puntaje</th>
                                        <th class="p-3">Evaluador</th>
                                        <th class="p-3">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[var(--rm-border)]">
                                    @foreach($evaluacionesCognitivas as $app)
                                        <tr class="hover:bg-[var(--rm-surface-soft)]/50 transition">
                                            <td class="p-3 font-semibold text-[var(--rm-text-primary)]">{{ $app->instrumento->nombre ?? 'Test' }}</td>
                                            <td class="p-3 text-[var(--rm-text-secondary)]">{{ $app->fecha_hora?->format('d/m/Y H:i') }}</td>
                                            <td class="p-3 font-mono font-bold">{{ $app->puntaje_total ?? '-' }} / {{ $app->puntaje_maximo ?? '-' }}</td>
                                            <td class="p-3 text-[var(--rm-text-secondary)]">{{ $app->evaluador->usuario->nombre_completo ?? 'Personal' }}</td>
                                            <td class="p-3">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                    {{ $app->estado }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-ui.section-card>

                @if(!empty($chartCognitiva))
                    <x-ui.section-card title="Perfil de rendimiento instrumental" subtitle="Puntajes obtenidos vs. puntaje máximo por batería" icon="ph-chart-bar">
                        <div wire:ignore
                             x-data="{
                                chartKey: 'residente-chart-cognitivo-{{ $residente->getKey() }}',
                                init() {
                                    this.render();
                                    window.RMCharts?.onThemeChange(() => this.render(), this.chartKey, this.$el);
                                },
                                render() {
                                    if (typeof Chart === 'undefined' || !window.RMCharts?.presets) {
                                        setTimeout(() => this.render(), 80);
                                        return;
                                    }
                                    const canvas = this.$refs.chartCog;
                                    if (!canvas) return;
                                    const labels = @js($chartCognitiva['labels']);
                                    const datasets = [
                                        {
                                            label: 'Puntaje obtenido',
                                            data: @js($chartCognitiva['puntajes']),
                                            backgroundColor: '#876FAD',
                                            borderColor: '#876FAD',
                                            borderRadius: 8,
                                        },
                                        {
                                            label: 'Puntaje máximo',
                                            data: @js($chartCognitiva['maximos']),
                                            backgroundColor: '#ded8cb',
                                            borderColor: '#ded8cb',
                                            borderRadius: 8,
                                        }
                                    ];
                                    const config = window.RMCharts.presets.bar(labels, datasets, {
                                        scales: {
                                            y: { beginAtZero: true, ticks: { precision: 0 } }
                                        }
                                    });
                                    window.RMCharts.init(this.chartKey, canvas, config, () => this.render());
                                }
                             }"
                             class="relative w-full h-64 p-2">
                            <canvas x-ref="chartCog" role="img" aria-label="Perfil de pruebas cognitivas"></canvas>
                        </div>
                    </x-ui.section-card>
                @endif

                @if($controlesCognitivos->isNotEmpty())
                    <x-ui.section-card title="Observaciones y controles longitudinales" subtitle="Corroboración de cambio con respecto al nivel basal (C01)" icon="ph-clock-counter-clockwise">
                        <div class="space-y-2">
                            @foreach($controlesCognitivos as $cc)
                                <div class="p-3 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface)] text-xs space-y-1">
                                    <div class="flex items-center justify-between text-[11px] text-[var(--rm-text-secondary)]">
                                        <span class="font-bold text-[var(--rm-text-primary)]">{{ $cc->fecha_hora?->format('d/m/Y H:i') }}</span>
                                        <span class="font-mono text-[10px]">{{ $cc->estado }}</span>
                                    </div>
                                    <p class="text-[var(--rm-text-primary)]">{{ $cc->observacion }}</p>
                                </div>
                            @endforeach
                        </div>
                    </x-ui.section-card>
                @endif
            </div>

        {{-- ==========================================
             PESTAÑA 3: ÁREA AFECTIVA Y EMOCIONAL
             ========================================== --}}
        @elseif($tab === 'afectiva')
            <div class="space-y-4">
                <div class="p-4 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div class="space-y-1">
                        <strong class="font-bold text-[var(--rm-text-primary)] flex items-center gap-2">
                            <i class="ph ph-heart text-base text-[var(--rm-action-primary)]"></i>
                            Evaluación afectiva, del ánimo y descarte diferencial (C04)
                        </strong>
                        <p class="text-[var(--rm-text-secondary)] max-w-2xl">
                            Documenta síntomas depresivos, ansiedad, apatía y alteraciones conductuales. Esta información aporta contexto clínico y respalda la evaluación diferencial ante Apoyo a la decisión
                        </p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if(auth()->user()?->hasRole('PSICOLOGO/A') && auth()->user()?->checkPermissionTo('valoraciones_psicologicas.crear', 'web'))
                        <button type="button" wire:click="alternarFormularioAfectivo" class="rm-btn rm-btn-primary text-xs">
                            <i class="ph {{ $mostrarFormAfectiva ? 'ph-x' : 'ph-plus' }} mr-1"></i>
                            {{ $mostrarFormAfectiva ? 'Cancelar formulario' : 'Nueva valoración afectiva' }}
                        </button>
                        @endif
                        <a href="{{ route('admin.psicologia.evaluacion.afectiva') }}" class="rm-btn rm-btn-secondary text-xs"><i class="ph ph-book-open mr-1" aria-hidden="true"></i>Consultar recursos del área</a>
                    </div>
                </div>

                {{-- Formulario Desplegable de Valoración Afectiva --}}
                @if($confirmarDescarteAfectiva)
                <div role="alert" class="rm-explorador__nota"><div><p>Hay una valoración afectiva sin registrar. Puedes seguir completándola o descartar los cambios.</p><div class="flex flex-wrap gap-3 mt-3"><button type="button" wire:click="conservarValoracionAfectiva" class="rm-btn rm-btn-secondary">Seguir completando</button><button type="button" wire:click="descartarValoracionAfectiva" class="rm-btn rm-btn-danger">Descartar cambios</button></div></div></div>
                @endif
                @if($mostrarFormAfectiva)
                    <div class="p-5 rounded-2xl border border-[var(--rm-action-primary)]/40 bg-[var(--rm-surface)] shadow-sm space-y-4 text-xs">
                        <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-2.5">
                            <h3 class="font-bold text-sm text-[var(--rm-text-primary)] flex items-center gap-2">
                                <i class="ph ph-pencil-line text-[var(--rm-action-primary)]"></i>
                                Formulación clínica del estado afectivo
                            </h3>
                            <span class="text-[11px] text-[var(--rm-text-secondary)]">Registro vinculado a la atención profesional</span>
                        </div>

                        <form wire:submit.prevent="guardarValoracionAfectiva" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                                <div>
                                    <label for="afectiva-estado_animo" class="block font-semibold text-[var(--rm-text-primary)] mb-1">Estado de ánimo *</label>
                                    <x-ui.selector id="afectiva-estado_animo" label="Estado de ánimo" wire:model="formAfectiva.estado_animo"><option value="">Selecciona lo observado</option>
                                        <option value="EUTIMICO">Eutímico / Estable</option>
                                        <option value="DEPRESIVO">Depresivo / Triste</option>
                                        <option value="ANSIOSO">Ansioso / Inquieto</option>
                                        <option value="LABIL">Lábil / Cambiante</option>
                                        <option value="IRRITABLE">Irritable / Hostil</option>
                                    </x-ui.selector>
@error('formAfectiva.estado_animo')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="afectiva-afecto" class="block font-semibold text-[var(--rm-text-primary)] mb-1">Afecto *</label>
                                    <x-ui.selector id="afectiva-afecto" label="Afecto" wire:model="formAfectiva.afecto"><option value="">Selecciona lo observado</option>
                                        <option value="ADECUADO">Adecuado al contexto</option>
                                        <option value="APLANADO">Aplanado</option>
                                        <option value="EMBOTADO">Embotado / Restringido</option>
                                        <option value="INCONGRUENTE">Incongruente</option>
                                    </x-ui.selector>
@error('formAfectiva.afecto')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="afectiva-ansiedad" class="block font-semibold text-[var(--rm-text-primary)] mb-1">Ansiedad *</label>
                                    <x-ui.selector id="afectiva-ansiedad" label="Ansiedad" wire:model="formAfectiva.ansiedad"><option value="">Selecciona lo observado</option>
                                        <option value="AUSENTE">Ausente</option>
                                        <option value="LEVE">Leve</option>
                                        <option value="MODERADA">Moderada</option>
                                        <option value="SEVERA">Severa</option>
                                    </x-ui.selector>
@error('formAfectiva.ansiedad')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="afectiva-apatia" class="block font-semibold text-[var(--rm-text-primary)] mb-1">Apatía *</label>
                                    <x-ui.selector id="afectiva-apatia" label="Apatía" wire:model="formAfectiva.apatia"><option value="">Selecciona lo observado</option>
                                        <option value="AUSENTE">Ausente</option>
                                        <option value="LEVE">Leve</option>
                                        <option value="MODERADA">Moderada</option>
                                        <option value="SEVERA">Severa</option>
                                    </x-ui.selector>
@error('formAfectiva.apatia')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div>
                                <label for="afectiva-conducta" class="block font-semibold text-[var(--rm-text-primary)] mb-1">Conducta observada</label>
                                <input type="text" id="afectiva-conducta" wire:model="formAfectiva.conducta" placeholder="Describe la conducta observada durante la atención." class="rm-input w-full">
                            </div>

                            <div>
                                <label for="afectiva-conclusion" class="block font-semibold text-[var(--rm-text-primary)] mb-1">Conclusión e interpretación clínica * (Sustento C04)</label>
                                <textarea id="afectiva-conclusion" wire:model="formAfectiva.conclusion" rows="3" placeholder="Documenta tu conclusión y las limitaciones de la valoración." class="rm-textarea w-full" required></textarea>
                                @error('formAfectiva.conclusion') <span class="text-red-600 text-[11px]">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="afectiva-recomendacion" class="block font-semibold text-[var(--rm-text-primary)] mb-1">Recomendaciones o plan de apoyo</label>
                                <input type="text" id="afectiva-recomendacion" wire:model="formAfectiva.recomendacion" placeholder="Documenta el seguimiento indicado por el profesional." class="rm-input w-full">
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-[var(--rm-border)]">
                                <button type="button" wire:click="alternarFormularioAfectivo" class="rm-btn rm-btn-secondary text-xs">Cancelar</button>
                                <button type="submit" wire:target="guardarValoracionAfectiva" class="rm-btn rm-btn-primary text-xs">Guardar valoración afectiva</button>
                            </div>
                        </form>
                    </div>
                @endif

                <x-ui.section-card title="Valoraciones afectivas y conductuales registradas" subtitle="Formulaciones clínicas profesionales" icon="ph-clipboard-text">
                    @if($evaluacionesAfectivas->isEmpty())
                        <p class="text-xs text-[var(--rm-text-secondary)] italic py-2">No se han registrado valoraciones afectivas para este residente.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($evaluacionesAfectivas as $va)
                                <div class="p-4 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] space-y-2 text-xs">
                                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[var(--rm-border)]/60 pb-2">
                                        <div class="flex items-center gap-2 font-bold text-[var(--rm-text-primary)]">
                                            <i class="ph ph-calendar-blank text-sm text-[var(--rm-action-primary)]"></i>
                                            <span>{{ $va->fecha_hora?->format('d/m/Y H:i') }}</span>
                                            <span class="text-[var(--rm-text-secondary)] font-normal">· Por: {{ $va->personal?->nombres ?? 'Personal' }} {{ $va->personal?->apellido_paterno ?? '' }}</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            {{ $va->estado }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1 text-[11px]">
                                        <div><span class="text-[var(--rm-text-secondary)]">Ánimo:</span> <strong class="text-[var(--rm-text-primary)]">{{ $va->estado_animo ?? 'No doc.' }}</strong></div>
                                        <div><span class="text-[var(--rm-text-secondary)]">Afecto:</span> <strong class="text-[var(--rm-text-primary)]">{{ $va->afecto ?? 'No doc.' }}</strong></div>
                                        <div><span class="text-[var(--rm-text-secondary)]">Ansiedad:</span> <strong class="text-[var(--rm-text-primary)]">{{ $va->ansiedad ?? 'No doc.' }}</strong></div>
                                        <div><span class="text-[var(--rm-text-secondary)]">Apatía:</span> <strong class="text-[var(--rm-text-primary)]">{{ $va->apatia ?? 'No doc.' }}</strong></div>
                                    </div>
                                    @if($va->conclusion)
                                        <div class="pt-2 border-t border-[var(--rm-border)]/40">
                                            <p class="font-medium text-[var(--rm-text-primary)]">Conclusión profesional:</p>
                                            <p class="text-[var(--rm-text-secondary)] mt-0.5 leading-relaxed">{{ $va->conclusion }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-ui.section-card>

                @if(!empty($chartAfectiva))
                    <x-ui.section-card title="Distribución longitudinal del afecto y ánimo" subtitle="Proporción de estados afectivos documentados" icon="ph-chart-pie">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-6 p-2">
                            <div wire:ignore
                                 x-data="{
                                    chartKey: 'residente-chart-afectivo-{{ $residente->getKey() }}',
                                    init() {
                                        this.render();
                                        window.RMCharts?.onThemeChange(() => this.render(), this.chartKey, this.$el);
                                    },
                                    render() {
                                        if (typeof Chart === 'undefined' || !window.RMCharts?.presets) {
                                            setTimeout(() => this.render(), 80);
                                            return;
                                        }
                                        const canvas = this.$refs.chartAf;
                                        if (!canvas) return;
                                        const labels = @js($chartAfectiva['labels']);
                                        const data = @js($chartAfectiva['data']);
                                        const colors = @js($chartAfectiva['colors']);
                                        const config = window.RMCharts.presets.doughnut(labels, data, colors, {
                                            cutout: '70%'
                                        });
                                        window.RMCharts.init(this.chartKey, canvas, config, () => this.render());
                                    }
                                 }"
                                 class="relative w-44 h-44 shrink-0 flex items-center justify-center">
                                <canvas x-ref="chartAf" role="img" aria-label="Distribución afectiva"></canvas>
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none">
                                    <span class="text-xl font-black text-[var(--rm-text-primary)] leading-tight">{{ $chartAfectiva['total'] }}</span>
                                    <span class="text-[10px] text-[var(--rm-text-secondary)] font-medium">registros</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3 flex-1 text-xs">
                                @foreach($chartAfectiva['labels'] as $idx => $lbl)
                                    <div class="p-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $chartAfectiva['colors'][$idx] }}"></span>
                                            <span class="font-medium text-[var(--rm-text-primary)]">{{ $lbl }}</span>
                                        </div>
                                        <strong class="font-bold text-[var(--rm-text-primary)]">{{ $chartAfectiva['data'][$idx] }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </x-ui.section-card>
                @endif
            </div>

        {{-- ==========================================
             PESTAÑA 4: ÁREA FUNCIONAMIENTO (IADL)
             ========================================== --}}
        @elseif($tab === 'funcionamiento')
            <div class="space-y-4">
                <div class="p-4 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div class="space-y-1">
                        <strong class="font-bold text-[var(--rm-text-primary)] flex items-center gap-2">
                            <i class="ph ph-person-simple-walk text-base text-[var(--rm-action-primary)]"></i>
                            Autonomía instrumental y actividades cotidianas (IADL)
                        </strong>
                        <p class="text-[var(--rm-text-secondary)] max-w-2xl">
                            Diferencia la capacidad del residente de la oportunidad real en la residencia. Es la fuente fundamental para acreditar el requisito C03 de Petersen.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
<p class="text-sm text-rm-secondary">Consulta de IADL. El registro se realiza en su circuito profesional autorizado.</p>
                        <a href="{{ route('admin.psicologia.evaluacion.funcionamiento') }}" class="rm-btn rm-btn-secondary text-xs">
                            <i class="ph ph-clipboard mr-1"></i>Explorar instrumentos funcionales
                        </a>
                    </div>
                </div>

                {{-- Consulta del circuito IADL --}}


                <x-ui.section-card title="Observaciones IADL registradas" subtitle="Actividades complejas de la vida diaria" icon="ph-list-checks">
                    @if($controlesIadl->isEmpty())
                        <p class="text-xs text-[var(--rm-text-secondary)] italic py-2">No se han registrado observaciones de actividades instrumentales IADL.</p>
                    @else
                        <div class="space-y-2.5">
                            @foreach($controlesIadl as $iadl)
                                <div class="p-3.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] text-xs space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-sm text-[var(--rm-text-primary)]">{{ $iadl->actividad }}</span>
                                        <span class="text-[10px] font-mono text-[var(--rm-text-secondary)]">{{ $iadl->fecha_hora?->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px] text-[var(--rm-text-secondary)] pt-1">
                                        <div>Desempeño: <strong class="text-[var(--rm-text-primary)]">{{ $iadl->desempeno }}</strong></div>
                                        <div>Oportunidad: <strong class="text-[var(--rm-text-primary)]">{{ $iadl->oportunidad }}</strong></div>
                                        <div>Ayuda: <strong class="text-[var(--rm-text-primary)]">{{ $iadl->tipo_ayuda }}</strong></div>
                                        <div>Causa: <strong class="text-[var(--rm-text-primary)]">{{ $iadl->causa_ayuda }}</strong></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-ui.section-card>

                <p class="rm-explorador__nota">Se conservan los desempeños observados originales. No se convierten en porcentajes ni se presentan como una escala aplicada.</p>
            </div>

        {{-- ==========================================
             PESTAÑA 5: ENTORNO Y RED SOCIAL
             ========================================== --}}
        @elseif($tab === 'entorno')
            <div class="space-y-4">
                <div class="p-4 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] text-xs space-y-1">
                    <strong class="font-bold text-[var(--rm-text-primary)] flex items-center gap-2">
                        <i class="ph ph-users-four text-base text-[var(--rm-action-primary)]"></i>
                        Red social, vinculación familiar y adaptación institucional
                    </strong>
                    <p class="text-[var(--rm-text-secondary)]">
                        Registra la calidad de los vínculos familiares, la percepción de soledad y la adaptación a la convivencia residencial.
                    </p>
                </div>

                <x-ui.section-card title="Contactos familiares y de referencia" subtitle="Personas de apoyo autorizadas" icon="ph-address-book">
                    @if(!$puedeConsultarContactos)
                        <p class="text-xs text-[var(--rm-text-secondary)]" role="status">Tu cuenta no tiene permiso para consultar contactos y sus vínculos. Las notas de contexto autorizadas se conservan por separado.</p>
                    @elseif($contactosFamiliares->isEmpty())
                        <p class="text-xs text-[var(--rm-text-secondary)] italic py-2">No hay contactos familiares vinculados para este residente.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($contactosFamiliares as $contacto)
                                <div class="p-3.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] text-xs space-y-1">
                                    <div class="flex items-center justify-between">
                                        <strong class="font-bold text-[var(--rm-text-primary)]">{{ $contacto->nombre_completo }}</strong>
                                        <span class="text-[10px] px-2 py-0.5 rounded bg-blue-50 text-blue-800 font-semibold border border-blue-200">
                                            {{ $contacto->pivot->parentesco ?? 'Familiar' }}
                                        </span>
                                    </div>
                                    <p class="text-[var(--rm-text-secondary)]">Teléfono: {{ $contacto->telefono ?? 'No registrado' }}</p>
                                    @if($contacto->pivot->responsable_principal)
                                        <span class="inline-block text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 mt-1">
                                            Responsable principal
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-ui.section-card>
            </div>

        {{-- ==========================================
             PESTAÑA 6: SESIONES Y SEGUIMIENTO
             ========================================== --}}
        @elseif($tab === 'seguimiento' || $tab === 'sesiones')
            <div class="space-y-4">
                <div class="p-4 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div class="space-y-1">
                        <strong class="font-bold text-[var(--rm-text-primary)] flex items-center gap-2">
                            <i class="ph ph-calendar-check text-base text-[var(--rm-action-primary)]"></i>
                            Atenciones y sesiones psicológicas individuales
                        </strong>
                        <p class="text-[var(--rm-text-secondary)] max-w-2xl">
                            Registro de intervenciones, entrevistas de seguimiento y acompañamiento psicológico formal en el expediente.
                        </p>
                    </div>
                    @if(auth()->user()?->hasRole('PSICOLOGO/A') && auth()->user()?->checkPermissionTo('atenciones.crear', 'web'))
                    <button type="button" wire:click="$toggle('mostrarFormSesion')" class="rm-btn rm-btn-primary text-xs shrink-0">
                        <i class="ph {{ $mostrarFormSesion ? 'ph-x' : 'ph-plus' }} mr-1"></i>
                        {{ $mostrarFormSesion ? 'Cancelar sesión' : 'Registrar nueva sesión' }}
                    </button>
                    @endif
                </div>

                {{-- Formulario Desplegable de Sesión --}}
                @if($mostrarFormSesion)
                    <div class="p-5 rounded-2xl border border-[var(--rm-action-primary)]/40 bg-[var(--rm-surface)] shadow-sm space-y-4 text-xs">
                        <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-2.5">
                            <h3 class="font-bold text-sm text-[var(--rm-text-primary)] flex items-center gap-2">
                                <i class="ph ph-pencil-line text-[var(--rm-action-primary)]"></i>
                                Documentar sesión de atención psicológica
                            </h3>
                            <span class="text-[11px] text-[var(--rm-text-secondary)]">Persistencia en <code>atenciones</code></span>
                        </div>

                        <form wire:submit.prevent="guardarSesion" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-[var(--rm-text-primary)] mb-1">Tipo de atención *</label>
                                    <select wire:model="formSesion.tipo_atencion" class="rm-select w-full">
                                        <option value="SEGUIMIENTO">Seguimiento psicológico individual</option>
                                        <option value="VALORACION_INICIAL">Primera evaluación / Ingreso</option>
                                        <option value="INTERVENCION_CRISIS">Intervención en crisis / Conductual</option>
                                        <option value="ENTREVISTA_FAMILIAR">Entrevista con informante familiar</option>
                                        <option value="ESTIMULACION_COGNITIVA">Sesión de estimulación cognitiva</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-semibold text-[var(--rm-text-primary)] mb-1">Motivo de la sesión *</label>
                                    <input type="text" wire:model="formSesion.motivo" placeholder="Ej: Duelo familiar reciente / Acompañamiento conductual" class="rm-input w-full" required>
                                </div>
                            </div>

                            <div>
                                <label class="block font-semibold text-[var(--rm-text-primary)] mb-1">Observaciones y notas de la sesión</label>
                                <textarea wire:model="formSesion.observacion" rows="3" placeholder="Detalle las intervenciones realizadas, respuesta del residente y pautas acordadas..." class="rm-textarea w-full"></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-[var(--rm-border)]">
                                <button type="button" wire:click="$set('mostrarFormSesion', false)" class="rm-btn rm-btn-secondary text-xs">Cancelar</button>
                                <button type="submit" class="rm-btn rm-btn-primary text-xs">Guardar sesión</button>
                            </div>
                        </form>
                    </div>
                @endif

                @if(!empty($chartSeguimiento))
                    <x-ui.section-card title="Frecuencia mensual de intervenciones" subtitle="Distribución temporal de sesiones realizadas" icon="ph-chart-line-up">
                        <div wire:ignore
                             x-data="{
                                chartKey: 'residente-chart-sesiones-{{ $residente->getKey() }}',
                                init() {
                                    this.render();
                                    window.RMCharts?.onThemeChange(() => this.render(), this.chartKey, this.$el);
                                },
                                render() {
                                    if (typeof Chart === 'undefined' || !window.RMCharts?.presets) {
                                        setTimeout(() => this.render(), 80);
                                        return;
                                    }
                                    const canvas = this.$refs.chartSes;
                                    if (!canvas) return;
                                    const labels = @js($chartSeguimiento['labels']);
                                    const datasets = [
                                        {
                                            label: 'Sesiones realizadas',
                                            data: @js($chartSeguimiento['data']),
                                            backgroundColor: '#4a7054',
                                            borderColor: '#4a7054',
                                            borderRadius: 8,
                                        }
                                    ];
                                    const config = window.RMCharts.presets.bar(labels, datasets, {
                                        scales: {
                                            y: { beginAtZero: true, ticks: { precision: 0 } }
                                        }
                                    });
                                    window.RMCharts.init(this.chartKey, canvas, config, () => this.render());
                                }
                             }"
                             class="relative w-full h-56 p-2">
                            <canvas x-ref="chartSes" role="img" aria-label="Frecuencia de sesiones de psicología"></canvas>
                        </div>
                    </x-ui.section-card>
                @endif

                <x-ui.section-card title="Sesiones e intervenciones psicológicas" subtitle="Atenciones longitudinales registradas" icon="ph-calendar-check">
                    @if($sesionesPsicologia->isEmpty())
                        <p class="text-xs text-[var(--rm-text-secondary)] italic py-2">No hay atenciones o sesiones psicológicas registradas aún.</p>
                    @else
                        <div class="space-y-2.5">
                            @foreach($sesionesPsicologia as $atencion)
                                <div class="p-3.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] text-xs space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-sm text-[var(--rm-text-primary)]">{{ is_object($atencion->tipo_atencion) ? ($atencion->tipo_atencion->nombre ?? 'Atención') : $atencion->tipo_atencion }}</span>
                                        <span class="text-[10px] font-mono text-[var(--rm-text-secondary)]">{{ $atencion->fecha_hora?->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <p class="text-[var(--rm-text-secondary)]">Profesional: {{ $atencion->personal?->nombres ?? 'Psicología' }} {{ $atencion->personal?->apellido_paterno ?? '' }}</p>
                                    @if($atencion->motivo)
                                        <p class="text-[var(--rm-text-primary)]">Motivo: {{ $atencion->motivo }}</p>
                                    @endif
                                    @if($atencion->observacion)
                                        <p class="text-[var(--rm-text-secondary)] italic text-[11px] border-t border-[var(--rm-border)]/50 pt-1">«{{ $atencion->observacion }}»</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-ui.section-card>
            </div>

        {{-- ==========================================
             PESTAÑA 7: INFORMES Y RESULTADOS
             ========================================== --}}
        @elseif($tab === 'informes')
            <div class="space-y-4">
                <x-ui.section-card title="Historial consolidado de valoraciones" subtitle="Conclusiones firmadas y trazabilidad clínica" icon="ph-file-text">
                    @if($informesPsicologicos->isEmpty())
                        <p class="text-xs text-[var(--rm-text-secondary)] italic py-2">Sin informes psicológicos formalmente registrados.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($informesPsicologicos as $inf)
                                <div class="p-4 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] text-xs space-y-2">
                                    <div class="flex items-center justify-between border-b border-[var(--rm-border)]/60 pb-2">
                                        <strong class="font-bold text-[var(--rm-text-primary)]">Valoración del {{ $inf->fecha_hora?->format('d/m/Y H:i') }}</strong>
                                        <span class="text-[11px] text-[var(--rm-text-secondary)]">Firmado por: {{ $inf->personal?->nombres }} {{ $inf->personal?->apellido_paterno }}</span>
                                    </div>
                                    <p class="text-[var(--rm-text-primary)] leading-relaxed">{{ $inf->conclusion ?? 'Sin conclusión textual registrada.' }}</p>
                                    @if($inf->recomendacion)
                                        <div class="p-2.5 rounded-lg bg-[var(--rm-surface-soft)] text-[11px] space-y-0.5">
                                            <span class="font-bold text-[var(--rm-text-primary)]">Recomendaciones:</span>
                                            <p class="text-[var(--rm-text-secondary)]">{{ $inf->recomendacion }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-ui.section-card>
            </div>

        {{-- ==========================================
             PESTAÑA 8: RESULTADOS Apoyo a la decisión
             ========================================== --}}
        @elseif($tab === 'orion')
            <div class="space-y-4">
                {{-- Banner Contextual Apoyo a la decisión --}}
                <div class="p-5 rounded-2xl bg-gradient-to-r from-[var(--rm-action-primary-soft)] via-[var(--rm-surface)] to-[var(--rm-surface-soft)] border border-[var(--rm-action-primary)]/20 shadow-xs space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[var(--rm-action-primary)] text-white shadow-2xs">
                                    Motor Experto Apoyo a la decisión
                                </span>
                                <span class="text-[11px] font-semibold text-[var(--rm-text-secondary)]">Criterios diagnósticos Petersen</span>
                            </div>
                            <h2 class="text-base font-bold text-[var(--rm-text-primary)]">Articulación neurocognitiva y juicios clínicos originales</h2>
                            <p class="text-xs text-[var(--rm-text-secondary)] max-w-2xl leading-relaxed">
                                El sistema experto cognitivo de RememberMind evalúa en memoria (Nivel 1, <span class="font-mono">persistido = false</span>) la concurrencia de deterioro neurocognitivo y criterios Petersen, integrando baterías psicométricas, observación conductual e historia afectiva.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <a href="{{ route('admin.psicologia.residente.resultados-experto', $residente) }}" class="rm-btn rm-btn-primary text-xs shadow-2xs">
                                <i class="ph ph-brain mr-1.5 text-sm" aria-hidden="true"></i>Ver informe narrativo completo
                            </a>
                            @if($puedeValorarPetersen)
                                <a href="{{ route('admin.sistema-experto.petersen-original', $residente) }}" class="rm-btn rm-btn-secondary text-xs">
                                    <i class="ph ph-scales mr-1.5 text-sm" aria-hidden="true"></i>Registrar juicio Petersen
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Matriz de los 5 Criterios Petersen --}}
                <x-ui.section-card title="Criterios clínicos de Petersen para deterioro cognitivo leve" subtitle="Estado de los juicios originales e inmutables documentados para el residente" icon="ph-scales">
                    @php
                        $criteriosDef = [
                            'C01' => [
                                'titulo' => 'C01 · Queja cognitiva confirmada',
                                'descripcion' => 'Presencia de queja de pérdida subjetiva de memoria u otro dominio cognitivo, corroborada por un informante fidedigno o familiar.',
                                'dominio' => 'Queja subjetiva / Anamnesis',
                            ],
                            'C02' => [
                                'titulo' => 'C02 · Deterioro cognitivo objetivo',
                                'descripcion' => 'Rendimiento anormal en uno o más dominios neurocognitivos (memoria, funciones ejecutivas, lenguaje, visuoespacial o atención) según baremos.',
                                'dominio' => 'Baterías psicométricas (MMSE, MoCA, etc.)',
                            ],
                            'C03' => [
                                'titulo' => 'C03 · Preservación de actividades funcionales (IADL)',
                                'descripcion' => 'Conservación básica de la independencia en actividades instrumentales de la vida diaria o afectación mínima sin demencia.',
                                'dominio' => 'Escalas funcionales / Controles IADL',
                            ],
                            'C04' => [
                                'titulo' => 'C04 · Ausencia de demencia / Criterio diferencial',
                                'descripcion' => 'Función cognitiva general esencialmente preservada; descarte explícito de causa atribuible únicamente a depresión, afecto o delirium.',
                                'dominio' => 'Valoración afectiva / GDS Yesavage',
                            ],
                            'C05' => [
                                'titulo' => 'C05 · Juicio clínico global interdisciplinario',
                                'descripcion' => 'Conclusión clínica integrada del profesional tras ponderar todos los elementos y exclusiones pertinentes.',
                                'dominio' => 'Síntesis diagnóstica formal',
                            ],
                        ];
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                        @foreach($criteriosDef as $codCriterio => $info)
                            @php
                                $juicio = $juiciosPetersen->firstWhere('elemento', $codCriterio);
                            @endphp
                            <div class="p-4 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] flex flex-col justify-between gap-3 text-xs shadow-2xs">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between gap-2 border-b border-[var(--rm-border)]/60 pb-2">
                                        <span class="font-bold text-[var(--rm-text-primary)] text-xs">{{ $info['titulo'] }}</span>
                                        @if($juicio)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $juicio->estado_original === 'S' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($juicio->estado_original === 'N' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200') }}">
                                                {{ $juicio->estado_original === 'S' ? 'CUMPLE' : ($juicio->estado_original === 'N' ? 'NO CUMPLE' : 'EVALUADO') }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)] border border-[var(--rm-border)]">
                                                PENDIENTE
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[var(--rm-text-secondary)] text-[11px] leading-relaxed">{{ $info['descripcion'] }}</p>
                                    <p class="text-[10px] text-[var(--rm-action-primary)] font-semibold flex items-center gap-1">
                                        <i class="ph ph-link"></i>{{ $info['dominio'] }}
                                    </p>
                                </div>

                                <div class="pt-2 border-t border-[var(--rm-border)]/50">
                                    @if($juicio)
                                        <div class="space-y-1">
                                            <div class="flex items-center justify-between text-[10px] text-[var(--rm-text-secondary)]">
                                                <span>{{ $juicio->fecha_hora?->format('d/m/Y H:i') }}</span>
                                                <span class="font-medium text-[var(--rm-text-primary)]">{{ $juicio->personal?->nombres }}</span>
                                            </div>
                                            <p class="text-[11px] text-[var(--rm-text-primary)] italic line-clamp-2">«{{ $juicio->justificacion }}»</p>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] text-[var(--rm-text-secondary)] italic">Sin registro original</span>
                                            @if($puedeValorarPetersen)
                                                <a href="{{ route('admin.sistema-experto.petersen-original', $residente) }}" class="text-[10px] text-[var(--rm-action-primary)] font-bold hover:underline">
                                                    Documentar &rarr;
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.section-card>

                {{-- Baterías instrumentales que alimentan Apoyo a la decisión --}}
                <x-ui.section-card title="Baterías e instrumentos aportantes al perfil neurocognitivo" subtitle="Pruebas psicométricas recientes del expediente del residente" icon="ph-clipboard-text">
                    @if($instrumentosCognitivosOrion->isEmpty())
                        <div class="py-6 text-center text-xs text-[var(--rm-text-secondary)] italic">
                            <i class="ph ph-notepad text-2xl text-[var(--rm-text-secondary)]/50 mb-1 block"></i>
                            No hay baterías cognitivas registradas en este período.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="border-b border-[var(--rm-border)] text-[var(--rm-text-secondary)] font-semibold text-[11px]">
                                        <th class="py-2.5 px-3">Fecha</th>
                                        <th class="py-2.5 px-3">Instrumento</th>
                                        <th class="py-2.5 px-3">Puntuación / Resultado</th>
                                        <th class="py-2.5 px-3">Evaluador</th>
                                        <th class="py-2.5 px-3">Aporte Apoyo a la decisión</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[var(--rm-border)]">
                                    @foreach($instrumentosCognitivosOrion as $app)
                                        <tr class="hover:bg-[var(--rm-surface-soft)]/50 transition-colors">
                                            <td class="py-2 px-3 font-mono text-[11px] text-[var(--rm-text-secondary)]">
                                                {{ $app->fecha_hora?->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="py-2 px-3 font-medium text-[var(--rm-text-primary)]">
                                                {{ $app->instrumento?->nombre ?? $app->instrumento?->codigo ?? 'Instrumento' }}
                                            </td>
                                            <td class="py-2 px-3">
                                                <span class="font-bold text-[var(--rm-text-primary)]">{{ $app->puntuacion_total ?? '-' }}</span>
                                                @if($app->interpretacion)
                                                    <span class="text-[10px] text-[var(--rm-text-secondary)] block">{{ $app->interpretacion }}</span>
                                                @endif
                                            </td>
                                            <td class="py-2 px-3 text-[11px] text-[var(--rm-text-secondary)]">
                                                {{ $app->evaluador?->usuario?->name ?? 'Profesional' }}
                                            </td>
                                            <td class="py-2 px-3">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    Criterio C02 (Objetivo)
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-ui.section-card>

                {{-- Aviso Ético y Regulatorio --}}
                <div class="p-4 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] text-xs text-[var(--rm-text-secondary)] flex items-start gap-3">
                    <i class="ph ph-shield-check text-lg text-[var(--rm-action-primary)] shrink-0 mt-0.5"></i>
                    <div class="space-y-1">
                        <strong class="font-bold text-[var(--rm-text-primary)]">Gobernanza clínica y apoyo a la decisión</strong>
                        <p class="leading-relaxed">
                            Apoyo a la decisión es un motor de apoyo a la decisión clínica (CDSS) que procesa evidencia objetiva e inmutable según protocolos institucionales aprobados. Toda conclusión diagnóstica y terapéutica es de exclusiva responsabilidad del profesional tratante y del equipo interdisciplinario de RememberMind.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal de captura instrumental de área unificado --}}
    @livewire('valoraciones.evaluacion-geriatrica-area-modal', key('modal-area-psicologia-'.$residente->getKey()))
</div>
