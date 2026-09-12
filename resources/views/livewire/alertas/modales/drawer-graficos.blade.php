@if($drawerGrafico && $adultoDrawer)
@php
    $ultSigno = $signosDrawer->first();
    $historialSignos = $signosDrawer->take(8);
    
    $puntos = $signosDrawer->take(8)->reverse()->values();
    $labels = $puntos->map(fn($s) => $s->fecha ? $s->fecha->format('d/m') . ($s->hora ? ' ' . substr($s->hora, 0, 5) : '') : 'Control')->values()->toArray();
    $sisData = $puntos->map(fn($s) => (float)($s->presion_sistolica ?? 120))->values()->toArray();
    $diaData = $puntos->map(fn($s) => (float)($s->presion_diastolica ?? 80))->values()->toArray();
    $fcData = $puntos->map(fn($s) => (float)($s->frecuencia_cardiaca ?? 75))->values()->toArray();
    $spo2Data = $puntos->map(fn($s) => (float)($s->saturacion ?? 96))->values()->toArray();
    $tempData = $puntos->map(fn($s) => (float)($s->temperatura ?? 36.5))->values()->toArray();
@endphp
<div class="fixed inset-0 z-50 overflow-hidden font-sans"
     role="dialog"
     aria-modal="true"
     aria-labelledby="drawer-graficos-title"
     x-data="{
        tab: 'presion',
        labels: @js($labels),
        sisData: @js($sisData),
        diaData: @js($diaData),
        fcData: @js($fcData),
        spo2Data: @js($spo2Data),
        tempData: @js($tempData),
        init() {
            this.$watch('tab', () => this.renderCurrentChart());
            this.$nextTick(() => {
                this.renderCurrentChart();
            });
            window.RMCharts?.onThemeChange(() => {
                this.renderCurrentChart();
            });
        },
        renderCurrentChart() {
            if (typeof Chart === 'undefined' || typeof window.RMCharts === 'undefined' || !window.RMCharts.presets) {
                setTimeout(() => this.renderCurrentChart(), 60);
                return;
            }
            const canvas = document.getElementById('chart-drawer-evolucion');
            if (!canvas) return;

            const isDark = window.RMCharts.isDark();
            const gridColor = window.RMCharts.getCss('--rm-chart-grid') || (isDark ? 'rgba(255,255,255,0.08)' : 'rgba(224,212,198,0.40)');
            const axisTextColor = window.RMCharts.getCss('--rm-chart-axis-text') || (isDark ? '#94A3B8' : '#64748B');

            let datasets = [];
            let yMin = undefined;
            let yMax = undefined;
            let yStep = undefined;
            let unit = '';

            if (this.tab === 'presion') {
                unit = 'mmHg';
                datasets = [
                    {
                        label: 'Sistólica',
                        data: this.sisData,
                        borderColor: '#F43F5E',
                    },
                    {
                        label: 'Diastólica',
                        data: this.diaData,
                        borderColor: '#0EA5E9',
                    }
                ];
                yMin = 40;
                yMax = 190;
                yStep = 20;
            } else if (this.tab === 'pulso') {
                unit = 'lpm';
                datasets = [{
                    label: 'Frecuencia Cardíaca',
                    data: this.fcData,
                    borderColor: '#F97316',
                }];
                yMin = 40;
                yMax = 140;
                yStep = 20;
            } else if (this.tab === 'spo2') {
                unit = '%';
                datasets = [{
                    label: 'Saturación SpO2',
                    data: this.spo2Data,
                    borderColor: '#14B8A6',
                }];
                yMin = 80;
                yMax = 100;
                yStep = 5;
            } else if (this.tab === 'temp') {
                unit = '°C';
                datasets = [{
                    label: 'Temperatura',
                    data: this.tempData,
                    borderColor: '#F59E0B',
                }];
                yMin = 34.5;
                yMax = 40.5;
                yStep = 1;
            }

            const customOptions = {
                _showPoints: true,
                layout: {
                    padding: { top: 8, right: 14, bottom: 4, left: 6 }
                },
                scales: {
                    x: {
                        display: true,
                        grid: {
                            color: gridColor,
                            drawBorder: false,
                        },
                        ticks: {
                            color: axisTextColor,
                            font: { family: 'Inter, system-ui, sans-serif', size: 10, weight: '600' },
                            maxRotation: 0,
                        }
                    },
                    y: {
                        display: true,
                        min: yMin,
                        max: yMax,
                        grid: {
                            color: gridColor,
                            drawBorder: false,
                        },
                        ticks: {
                            color: axisTextColor,
                            font: { family: 'Inter, system-ui, sans-serif', size: 10, weight: '600' },
                            stepSize: yStep,
                            callback: function(val) {
                                return val + ' ' + unit;
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        enabled: true,
                        backgroundColor: isDark ? 'rgba(15, 23, 42, 0.94)' : 'rgba(30, 41, 59, 0.94)',
                        titleColor: '#FFFFFF',
                        bodyColor: '#F8FAFC',
                        borderColor: isDark ? 'rgba(255, 255, 255, 0.12)' : 'rgba(0, 0, 0, 0.08)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        padding: 10,
                        titleFont: { family: 'Inter, system-ui, sans-serif', size: 11, weight: '700' },
                        bodyFont: { family: 'Inter, system-ui, sans-serif', size: 11, weight: '500' },
                        displayColors: true,
                        boxPadding: 4,
                        callbacks: {
                            label: function(context) {
                                const val = context.parsed?.y !== undefined ? context.parsed.y : context.raw;
                                return ' ' + context.dataset.label + ': ' + val + ' ' + unit;
                            }
                        }
                    }
                }
            };

            const cfg = window.RMCharts.presets.sparkline(
                this.labels,
                datasets,
                null,
                customOptions
            );

            window.RMCharts.init('chart-drawer-evolucion', canvas, cfg, () => this.renderCurrentChart());
        }
     }"
     x-on:keydown.escape.window="$wire.cerrarDrawer()">
    <!-- Backdrop oscuro con blur suave -->
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity"
         wire:click="cerrarDrawer"></div>

    <!-- Contenedor Deslizante Lateral (Barra Derecha) -->
    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
        <div class="pointer-events-auto flex h-full w-screen max-w-[660px] transform flex-col overflow-hidden rm-drawer transition duration-300 ease-in-out">
            
            <!-- Encabezado del Drawer -->
            <div class="rm-drawer-header">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--rm-surface-alt)] text-[var(--rm-primary)] border border-[var(--rm-border)] shadow-xs">
                        <i class="ph-bold ph-chart-line-up text-xl"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-indigo-100 text-indigo-800 border border-indigo-200 shadow-xs flex items-center gap-1">
                                <i class="ph-bold ph-sidebar"></i> Panel Lateral de Consulta
                            </span>
                        </div>
                        <h3 id="drawer-graficos-title" class="rm-modal-title text-base font-bold text-[var(--rm-text-title)]">
                            Gráficos de Evolución Clínica
                        </h3>
                        <p class="text-xs text-[var(--rm-text-muted)]">Tendencias y monitoreo fisiológico continuo</p>
                    </div>
                </div>
                <button type="button"
                    wire:click="cerrarDrawer"
                    aria-label="Cerrar panel de gráficos"
                    class="rm-btn-icon text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] transition cursor-pointer">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>

            <!-- Cuerpo del Drawer con Scroll -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3.5">
                
                <!-- 1. Tarjeta del Residente y Ubicación Física -->
                <div class="flex items-center justify-between p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--rm-primary)] text-white font-bold text-sm shadow-xs">
                            {{ substr($adultoDrawer->nombres, 0, 1) }}{{ substr($adultoDrawer->ap_paterno, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-[var(--rm-text-title)] text-sm leading-tight">
                                {{ $adultoDrawer->ap_paterno }} {{ $adultoDrawer->ap_materno }} {{ $adultoDrawer->nombres }}
                            </h4>
                            <div class="flex items-center gap-2 text-xs text-[var(--rm-text-muted)] mt-0.5">
                                <span>{{ $adultoDrawer->edad_texto }}</span>
                                @if($adultoDrawer->ci)
                                    <span>•</span>
                                    <span>CI: {{ $adultoDrawer->ci }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="text-right text-xs">
                        <span class="inline-block px-2.5 py-1 rounded-lg bg-[var(--rm-surface)] border border-[var(--rm-border)] text-[var(--rm-text-title)] font-semibold">
                            {{ $adultoDrawer->ubicacion_texto }}
                        </span>
                        <div class="mt-1 flex items-center justify-end gap-1.5">
                            <span class="text-[11px] text-[var(--rm-text-muted)]">Estado:</span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold border {{ $adultoDrawer->estado_badge_color }}">
                                {{ $adultoDrawer->estado_humano }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 2. KPIs del Último Control Registrado con Micrográficos -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] flex items-center gap-1.5">
                            <i class="ph-bold ph-heartbeat text-base text-[var(--rm-accent)]"></i>
                            Último Control de Signos Vitales
                        </span>
                        <span class="text-[11px] text-[var(--rm-text-muted)] font-mono">
                            {{ $ultSigno ? ($ultSigno->fecha ? $ultSigno->fecha->format('d/m/Y') : '') . ' ' . ($ultSigno->hora ? substr($ultSigno->hora, 0, 5) : '') : 'Sin registros recientes' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        {{-- Presión Arterial --}}
                        <div class="p-2.5 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)] shadow-xs flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block">Presión Art.</span>
                                <div class="text-sm font-bold text-[var(--rm-danger-action)] font-mono mt-0.5">
                                    {{ $ultSigno && $ultSigno->presion_sistolica ? $ultSigno->presion_sistolica.'/'.$ultSigno->presion_diastolica : '--/--' }}
                                    <span class="text-[10px] font-normal text-[var(--rm-text-muted)]">mmHg</span>
                                </div>
                            </div>
                            @if(count($sisData) >= 2)
                                <div class="mt-2 h-7 w-full">
                                    <x-ui.sparkline :data="$sisData" :labels="$labels" color="danger" height="28px" />
                                </div>
                            @endif
                        </div>

                        {{-- Frecuencia Cardíaca --}}
                        <div class="p-2.5 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)] shadow-xs flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block">Pulso / F.C.</span>
                                <div class="text-sm font-bold text-[var(--rm-accent-action)] font-mono mt-0.5">
                                    {{ $ultSigno && $ultSigno->frecuencia_cardiaca ? $ultSigno->frecuencia_cardiaca : '--' }}
                                    <span class="text-[10px] font-normal text-[var(--rm-text-muted)]">lpm</span>
                                </div>
                            </div>
                            @if(count($fcData) >= 2)
                                <div class="mt-2 h-7 w-full">
                                    <x-ui.sparkline :data="$fcData" :labels="$labels" color="terracota" height="28px" />
                                </div>
                            @endif
                        </div>

                        {{-- Saturación SpO2 --}}
                        <div class="p-2.5 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)] shadow-xs flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block">SpO2</span>
                                <div class="text-sm font-bold text-[var(--rm-info-action)] font-mono mt-0.5">
                                    {{ $ultSigno && $ultSigno->saturacion ? $ultSigno->saturacion : '--' }}
                                    <span class="text-[10px] font-normal text-[var(--rm-text-muted)]">%</span>
                                </div>
                            </div>
                            @if(count($spo2Data) >= 2)
                                <div class="mt-2 h-7 w-full">
                                    <x-ui.sparkline :data="$spo2Data" :labels="$labels" color="info" height="28px" />
                                </div>
                            @endif
                        </div>

                        {{-- Temperatura --}}
                        <div class="p-2.5 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)] shadow-xs flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block">Temperatura</span>
                                <div class="text-sm font-bold text-[var(--rm-text-title)] font-mono mt-0.5">
                                    {{ $ultSigno && $ultSigno->temperatura ? $ultSigno->temperatura : '--' }}
                                    <span class="text-[10px] font-normal text-[var(--rm-text-muted)]">°C</span>
                                </div>
                            </div>
                            @if(count($tempData) >= 2)
                                <div class="mt-2 h-7 w-full">
                                    <x-ui.sparkline :data="$tempData" :labels="$labels" color="warning" height="28px" />
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 3. Selector de Pestañas Interactivas de Gráficos -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] flex items-center gap-1.5">
                            <i class="ph-bold ph-chart-line text-base text-[var(--rm-primary)]"></i>
                            Curva de Tendencia Temporal
                        </span>

                        <div class="flex items-center gap-1 bg-[var(--rm-surface-alt)] p-1 rounded-lg border border-[var(--rm-border)]">
                            <button type="button"
                                @click="tab = 'presion'"
                                :class="tab === 'presion' ? 'bg-[var(--rm-surface)] text-[var(--rm-danger-action)] font-bold shadow-xs' : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)]'"
                                class="px-2.5 py-1 rounded-md text-[11px] transition cursor-pointer">
                                Presión
                            </button>
                            <button type="button"
                                @click="tab = 'pulso'"
                                :class="tab === 'pulso' ? 'bg-[var(--rm-surface)] text-[var(--rm-accent-action)] font-bold shadow-xs' : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)]'"
                                class="px-2.5 py-1 rounded-md text-[11px] transition cursor-pointer">
                                Pulso
                            </button>
                            <button type="button"
                                @click="tab = 'spo2'"
                                :class="tab === 'spo2' ? 'bg-[var(--rm-surface)] text-[var(--rm-info-action)] font-bold shadow-xs' : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)]'"
                                class="px-2.5 py-1 rounded-md text-[11px] transition cursor-pointer">
                                SpO2
                            </button>
                            <button type="button"
                                @click="tab = 'temp'"
                                :class="tab === 'temp' ? 'bg-[var(--rm-surface)] text-[#8F5C00] font-bold shadow-xs' : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)]'"
                                class="px-2.5 py-1 rounded-md text-[11px] transition cursor-pointer">
                                Temp.
                            </button>
                        </div>
                    </div>

                    @if($signosDrawer->count() >= 2)
                        <div class="rm-chart-card rm-chart-glass p-4 rounded-2xl relative w-full overflow-hidden">
                            <!-- Leyendas Dinámicas Contextuales -->
                            <div class="flex items-center justify-between text-[11px] text-[var(--rm-text-muted)] mb-3 font-mono">
                                <template x-if="tab === 'presion'">
                                    <div class="flex items-center justify-between w-full">
                                        <div class="flex items-center gap-3">
                                            <span class="flex items-center gap-1.5 text-[var(--rm-danger-action)] font-bold">
                                                <span class="inline-block w-2.5 h-2.5 rounded-full bg-[var(--rm-danger-action)]"></span>
                                                Sistólica (mmHg)
                                            </span>
                                            <span class="flex items-center gap-1.5 text-[var(--rm-info-action)] font-bold">
                                                <span class="inline-block w-2.5 h-2.5 rounded-full bg-[var(--rm-info-action)]"></span>
                                                Diastólica (mmHg)
                                            </span>
                                        </div>
                                        <span class="text-[10px] text-[var(--rm-text-muted)]">Norma: &lt; 120/80 mmHg</span>
                                    </div>
                                </template>
                                <template x-if="tab === 'pulso'">
                                    <div class="flex items-center justify-between w-full">
                                        <span class="flex items-center gap-1.5 text-[var(--rm-accent-action)] font-bold">
                                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-[var(--rm-accent-action)]"></span>
                                            Frecuencia Cardíaca (lpm)
                                        </span>
                                        <span class="text-[10px] text-[var(--rm-text-muted)]">Norma: 60 - 100 lpm</span>
                                    </div>
                                </template>
                                <template x-if="tab === 'spo2'">
                                    <div class="flex items-center justify-between w-full">
                                        <span class="flex items-center gap-1.5 text-[var(--rm-info-action)] font-bold">
                                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-[var(--rm-info-action)]"></span>
                                            Saturación de Oxígeno (%)
                                        </span>
                                        <span class="text-[10px] text-[var(--rm-text-muted)]">Norma: 95% - 100% (Crítico &lt; 90%)</span>
                                    </div>
                                </template>
                                <template x-if="tab === 'temp'">
                                    <div class="flex items-center justify-between w-full">
                                        <span class="flex items-center gap-1.5 text-[#8F5C00] font-bold">
                                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-[#C27D00]"></span>
                                            Temperatura Corporal (°C)
                                        </span>
                                        <span class="text-[10px] text-[var(--rm-text-muted)]">Norma: 36.5°C - 37.5°C</span>
                                    </div>
                                </template>
                            </div>

                            <!-- Canvas para Gráfico Dinámico con Movimiento y Relleno Translúcido -->
                            <div class="w-full h-44 relative" wire:ignore>
                                <canvas id="chart-drawer-evolucion"></canvas>
                            </div>
                        </div>
                    @else
                        <div class="py-8 text-center border border-dashed border-[var(--rm-border)] rounded-xl text-xs text-[var(--rm-text-muted)] bg-[var(--rm-surface)]">
                            Se requieren al menos 2 controles de signos vitales para graficar la curva de evolución.
                        </div>
                    @endif
                </div>

                <!-- 4. Tabla de Historial Clínico Reciente -->
                <div class="space-y-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] flex items-center gap-1.5">
                        <i class="ph ph-table text-base text-[var(--rm-primary)]"></i>
                        Historial de Controles Recientes
                    </span>

                    <div class="rm-table-container">
                        <table class="rm-table text-xs">
                            <thead class="rm-table-header">
                                <tr>
                                    <th>Fecha / Hora</th>
                                    <th>P.A.</th>
                                    <th>Pulso</th>
                                    <th>SpO2</th>
                                    <th>Temp.</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historialSignos as $s)
                                    <tr class="rm-table-row">
                                        <td class="rm-table-cell font-mono py-2 px-2.5">
                                            {{ ($s->fecha ? $s->fecha->format('d/m/Y') : '') . ' ' . ($s->hora ? substr($s->hora, 0, 5) : '') }}
                                        </td>
                                        <td class="rm-table-cell font-mono font-bold py-2 px-2.5 text-[var(--rm-danger-action)]">
                                            {{ $s->presion_sistolica ? $s->presion_sistolica.'/'.$s->presion_diastolica : '-' }}
                                        </td>
                                        <td class="rm-table-cell font-mono font-bold py-2 px-2.5 text-[var(--rm-accent-action)]">
                                            {{ $s->frecuencia_cardiaca ? $s->frecuencia_cardiaca.' lpm' : '-' }}
                                        </td>
                                        <td class="rm-table-cell font-mono font-bold py-2 px-2.5 text-[var(--rm-info-action)]">
                                            {{ $s->saturacion ? $s->saturacion.'%' : '-' }}
                                        </td>
                                        <td class="rm-table-cell font-mono py-2 px-2.5">
                                            {{ $s->temperatura ? $s->temperatura.'°C' : '-' }}
                                        </td>
                                        <td class="rm-table-cell py-2 px-2.5">
                                            <span class="rm-badge rm-badge-success text-[10px]">
                                                Registrado
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-[var(--rm-text-muted)]">
                                            No hay registros de signos vitales disponibles para este residente.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pie del Drawer -->
            <div class="rm-drawer-footer flex items-center justify-between">
                <button type="button"
                    wire:click="verUbicacion('{{ $adultoDrawer->cod_am }}')"
                    class="rm-btn rm-btn-sm rm-btn-secondary cursor-pointer">
                    <i class="ph ph-bed text-base"></i>
                    <span>Ver Ubicación y Cama</span>
                </button>

                <button type="button"
                    wire:click="cerrarDrawer"
                    class="rm-btn rm-btn-sm rm-btn-ghost cursor-pointer">
                    Cerrar Panel
                </button>
            </div>
        </div>
    </div>
</div>
@endif
