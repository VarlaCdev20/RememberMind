    {{-- 2. PRIMERA FILA EXACTA: GRÁFICO PRINCIPAL (~75%) + RESUMEN PERIODO (~25%) --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-stretch">

        {{-- GRÁFICO PRINCIPAL CON NÚMEROS EN LOS PUNTOS (75% - 9 columnas) --}}
        <div class="lg:col-span-9 rm-chart-card rm-chart-glass flex flex-col justify-between">
            <div class="rm-chart-header flex-col sm:flex-row sm:items-center">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--rm-info-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-info-strong)] text-sm">
                            <i class="ph-bold" :class="metricasInfo[metricaActiva]?.icon || 'ph-chart-line'"></i>
                        </span>
                        <h3 class="rm-chart-title"
                            x-text="metricasInfo[metricaActiva]?.titulo_grafico || 'PRESIÓN ARTERIAL'">
                            PRESIÓN ARTERIAL
                        </h3>
                    </div>
                    <p class="rm-chart-subtitle"
                       x-text="metricasInfo[metricaActiva]?.subtitulo || 'Evolución con valores registrados e interpretación del servidor'">
                        Evolución de constantes vitales con valores registrados e interpretación del servidor
                    </p>
                </div>

                {{-- Selector de Periodos [24h] [7 días] [30 días] [3 meses] [Personalizado] --}}
                <div class="flex items-center gap-1 bg-[var(--rm-surface-alt)] p-1 rounded-xl border border-[var(--rm-border)]/80 self-start sm:self-auto">
                    @foreach([
                        '24h' => '24h',
                        '7d' => '7 días',
                        '30d' => '30 días',
                        '3m' => '3 meses',
                        'personalizado' => 'Personalizado',
                    ] as $perKey => $perLabel)
                        <button type="button"
                                @click="cambiarPeriodo('{{ $perKey }}')"
                                :class="periodoActivo === '{{ $perKey }}'
                                    ? 'bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] shadow-2xs font-bold'
                                    : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface)] font-medium'"
                                class="px-2.5 py-1 text-[11px] rounded-lg transition cursor-pointer">
                            {{ $perLabel }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Leyenda interactiva superior del gráfico --}}
            <div class="flex flex-wrap items-center justify-between gap-2 pt-2.5 px-1 text-[11px]">
                <div class="flex items-center gap-4">
                    <template x-if="metricaActiva === 'PA'">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 font-bold text-[var(--rm-chart-2)]">
                                <span class="h-2 w-2 rounded-full bg-[var(--rm-chart-2)]"></span>
                                Sistólica (mmHg)
                            </span>
                            <span class="inline-flex items-center gap-1.5 font-bold text-[var(--rm-chart-4)]">
                                <span class="h-2 w-2 rounded-full bg-[var(--rm-chart-4)]"></span>
                                Diastólica (mmHg)
                            </span>
                            <span class="text-[var(--rm-text-muted)]">Interpretación del servidor en cada toma</span>
                        </div>
                    </template>
                    <template x-if="metricaActiva !== 'PA'">
                        <span class="inline-flex items-center gap-1.5 font-bold"
                              class="text-[var(--rm-chart-clinical-500)]">
                            <span class="h-2 w-2 rounded-full bg-[var(--rm-chart-clinical-500)]"></span>
                            <span x-text="`${metricasInfo[metricaActiva]?.nombre} (${metricasInfo[metricaActiva]?.unidad})`"></span>
                        </span>
                    </template>
                </div>

                {{-- Selector secundario: Otras métricas (Glucemia / Peso) --}}
                <div class="flex items-center gap-2 text-[11px] text-[var(--rm-text-muted)]">
                    <span>Otras métricas:</span>
                    <button type="button"
                            @click="seleccionarMetrica('GLUCOSA')"
                            :class="metricaActiva === 'GLUCOSA' ? 'text-[var(--rm-info-strong)] font-bold underline' : 'hover:text-[var(--rm-text-title)]'"
                            class="transition cursor-pointer">
                        Glucemia
                    </button>
                    <span>·</span>
                    <button type="button"
                            @click="seleccionarMetrica('PESO')"
                            :class="metricaActiva === 'PESO' ? 'text-[var(--rm-text-primary)] font-bold underline' : 'hover:text-[var(--rm-text-title)]'"
                            class="transition cursor-pointer">
                        Peso
                    </button>
                </div>
            </div>

            {{-- Contenedor del Chart.js Principal con wire:ignore --}}
            <div class="rm-chart-body is-lg relative w-full h-64 sm:h-72 mt-2" wire:ignore>
                <canvas id="signosVitalesMainCanvas" x-ref="mainChartCanvas" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- PANEL RESUMEN DEL PERÍODO (25% - 3 columnas) --}}
        <div class="lg:col-span-3 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-2xs flex flex-col justify-between space-y-3.5">
            <div class="flex items-center justify-between border-b border-[var(--rm-border)]/60 pb-2.5">
                <h3 class="text-xs font-bold text-[var(--rm-text-title)] uppercase tracking-wider">
                    RESUMEN DEL PERÍODO
                </h3>
                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[var(--rm-text-muted)] bg-[var(--rm-surface-alt)] px-2 py-0.5 rounded-md border border-[var(--rm-border)]">
                    <span x-text="periodoActivo === '24h' ? '24h' : (periodoActivo === '7d' ? '7 días' : (periodoActivo === '30d' ? '30 días' : '3 meses'))"></span>
                    <i class="ph-bold ph-caret-down text-[10px]"></i>
                </span>
            </div>

            {{-- Bloque: Último registro --}}
            <div class="rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-surface-alt)] p-3 space-y-1.5">
                <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block tracking-wider">
                    Último registro
                </span>
                <span class="text-[11px] text-[var(--rm-text-muted)] block font-medium"
                      x-text="resumenActivo.ultimo_registro_fecha || 'Sin registros'">
                    Sin registros
                </span>

                <div class="flex items-baseline justify-between pt-1">
                    <span class="text-xl font-bold text-[var(--rm-text-title)] tracking-tight"
                          x-text="resumenActivo.ultimo_fmt || resumenActivo.ultimo || '--'">—</span>
                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold border"
                          :class="resumenActivo.estado_badge || 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border-[var(--rm-border)]'"
                          x-text="resumenActivo.estado || 'Sin datos'">
                        Sin datos
                    </span>
                </div>
            </div>

            {{-- Bloque: Mínimo · Máximo · Promedio --}}
            <div class="grid grid-cols-3 gap-1.5 text-center">
                <div class="rounded-lg bg-[var(--rm-surface-alt)]/60 border border-[var(--rm-border)]/60 p-2">
                    <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block">Mínimo</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block"
                          x-text="resumenActivo.min || '--'">—</span>
                </div>
                <div class="rounded-lg bg-[var(--rm-surface-alt)]/60 border border-[var(--rm-border)]/60 p-2">
                    <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block">Máximo</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block"
                          x-text="resumenActivo.max || '--'">—</span>
                </div>
                <div class="rounded-lg bg-[var(--rm-surface-alt)]/60 border border-[var(--rm-border)]/60 p-2">
                    <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block">Promedio</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block"
                          x-text="resumenActivo.promedio || '--'">—</span>
                </div>
            </div>

            {{-- Bloque: Variación vs anterior --}}
            <div class="rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-surface-alt)] p-3 space-y-1">
                <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block tracking-wider">
                    Variación vs anterior
                </span>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[var(--rm-text-title)]"
                          x-text="resumenActivo.variacion || 'Sin registros comparables'">—</span>
                    <span class="inline-flex items-center text-xs font-bold"
                          :class="(resumenActivo.variacion && resumenActivo.variacion.includes('-')) ? 'text-[var(--rm-info-strong)]' : 'text-[var(--rm-danger-strong)]'">
                        <i class="ph-bold" :class="(resumenActivo.variacion && resumenActivo.variacion.includes('-')) ? 'ph-arrow-down' : 'ph-arrow-up'"></i>
                        <span x-text="resumenActivo.tendencia || 'Datos insuficientes'"></span>
                    </span>
                </div>
            </div>

            {{-- Botón CTA inferior: Reporte detallado --}}
            <button type="button"
                    @click="exportarSignosPDF()"
                    class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] py-2 text-xs font-bold text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer">
                <i class="ph-bold ph-file-text text-sm text-[var(--rm-text-muted)]"></i>
                <span>Generar reporte clínico PDF</span>
            </button>
        </div>

    </div>

    {{-- ========================================================================= --}}
