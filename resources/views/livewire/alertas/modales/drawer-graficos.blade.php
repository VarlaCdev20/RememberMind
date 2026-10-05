@if($drawerGrafico && $adultoDrawer)
@php
    $ultSigno = $signosDrawer->first();
    $historialSignos = $signosDrawer->take(8);

    $puntos = $signosDrawer->take(8)->reverse()->values();
    $labels = $puntos->map(fn($s) => $s->fecha ? $s->fecha->format('d/m') . ($s->hora ? ' ' . substr($s->hora, 0, 5) : '') : 'Control')->values()->toArray();
    $sisData = $puntos->map(fn($s) => $s->presion_sistolica !== null ? (float)$s->presion_sistolica : null)->values()->toArray();
    $diaData = $puntos->map(fn($s) => $s->presion_diastolica !== null ? (float)$s->presion_diastolica : null)->values()->toArray();
    $fcData = $puntos->map(fn($s) => $s->frecuencia_cardiaca !== null ? (float)$s->frecuencia_cardiaca : null)->values()->toArray();
    $spo2Data = $puntos->map(fn($s) => $s->saturacion !== null ? (float)$s->saturacion : null)->values()->toArray();
    $tempData = $puntos->map(fn($s) => $s->temperatura !== null ? (float)$s->temperatura : null)->values()->toArray();

    $alergiasTexto = collect($adultoDrawer->alergias ?? [])
        ->map(function ($alergia) {
            if (is_string($alergia)) {
                return trim($alergia);
            }
            $sustancia = trim((string) ($alergia->sustancia ?? ''));
            $reaccion = trim((string) ($alergia->reaccion ?? ''));
            return $sustancia !== '' ? $sustancia.($reaccion !== '' ? " ({$reaccion})" : '') : null;
        })
        ->filter()
        ->implode(', ');

    $diagnosticosTexto = collect($adultoDrawer->diagnosticos ?? [])
        ->map(fn($diagnostico) => trim((string) ($diagnostico->diagnostico ?? $diagnostico->nombre ?? $diagnostico->descripcion ?? '')))
        ->filter()
        ->implode(', ');

    $registrosTimeline = collect();
    if ($adultoDrawer->valoracionesEnfermeria && $adultoDrawer->valoracionesEnfermeria->count()) {
        foreach ($adultoDrawer->valoracionesEnfermeria->take(3) as $val) {
            $registrosTimeline->push([
                'fecha' => $val->created_at ? $val->created_at->format('d/m/Y H:i') : 'Fecha no registrada',
                'tipo' => 'Enfermería',
                'profesional' => $val->profesional->name ?? ($val->registradoPor->name ?? 'No registrado'),
                'resumen' => $val->observaciones ?: ($val->diagnostico_enfermeria ?: 'Sin resumen registrado.'),
                'estado' => $val->estado ?? 'Registrado',
                'badge' => 'bg-blue-100 text-blue-800 border-blue-200',
            ]);
        }
    }
    if ($registrosTimeline->isEmpty() && $adultoDrawer->seguimientosDiarios && $adultoDrawer->seguimientosDiarios->count()) {
        foreach ($adultoDrawer->seguimientosDiarios->take(3) as $seg) {
            $registrosTimeline->push([
                'fecha' => $seg->created_at ? $seg->created_at->format('d/m/Y H:i') : 'Fecha no registrada',
                'tipo' => 'Seguimiento',
                'profesional' => $seg->profesional->name ?? 'No registrado',
                'resumen' => $seg->observacion ?: 'Sin resumen registrado.',
                'estado' => $seg->estado ?? 'Registrado',
                'badge' => 'bg-blue-100 text-blue-800 border-blue-200',
            ]);
        }
    }
@endphp

<x-ui.drawer-livewire
    wire:model="drawerGrafico"
    title="Gráficos de Evolución Clínica"
    subtitle="Monitoreo hemodinámico, curva de tendencia y registro clínico longitudinal"
    badge="PANEL LATERAL DE CONSULTA"
    icon="ph-chart-line-up"
    size="xl"
    close-method="cerrarDrawer"
>
    <div x-data="{
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
            }, 'alertas-drawer');
        },
        renderCurrentChart() {
            if (typeof Chart === 'undefined') {
                setTimeout(() => this.renderCurrentChart(), 80);
                return;
            }
            const canvas = document.getElementById('chart-drawer-evolucion');
            if (!canvas) return;

            const api = window.RMCharts;
            const css = (token) => api.getCss(token);
            const fill = (tone, opacity = '--rm-line-area-opacity') => api.hexToRgba(api.color(tone), api.number(opacity, .12));
            const gridColor = css('--rm-chart-grid');
            const axisTextColor = css('--rm-chart-axis-text');
            const surfaceRaised = css('--rm-chart-tooltip-bg');
            const tooltipText = css('--rm-chart-tooltip-text');

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
                        borderColor: api.color('clinical'),
                        backgroundColor: fill('clinical'),
                        fill: true,
                    },
                    {
                        label: 'Diastólica',
                        data: this.diaData,
                        borderColor: api.color('reference'),
                        backgroundColor: fill('reference', '--rm-line-area-secondary-opacity'),
                        fill: true,
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
                    borderColor: api.color('clinical'),
                    backgroundColor: fill('clinical'),
                    fill: true,
                }];
                yMin = 40;
                yMax = 140;
                yStep = 20;
            } else if (this.tab === 'spo2') {
                unit = '%';
                datasets = [{
                    label: 'Saturación SpO₂',
                    data: this.spo2Data,
                    borderColor: api.color('clinical'),
                    backgroundColor: fill('clinical'),
                    fill: true,
                }];
                yMin = 80;
                yMax = 100;
                yStep = 5;
            } else if (this.tab === 'temp') {
                unit = '°C';
                datasets = [{
                    label: 'Temperatura',
                    data: this.tempData,
                    borderColor: api.color('clinical'),
                    backgroundColor: fill('clinical'),
                    fill: true,
                }];
                yMin = 34.5;
                yMax = 40.5;
                yStep = 1;
            }

            datasets = datasets.map(dataset => ({
                ...dataset,
                borderWidth: api.number('--rm-line-stroke-width', 3),
                pointRadius: api.number('--rm-line-dot-size', 5) / 2,
                pointHoverRadius: api.number('--rm-line-dot-size', 5),
                tension: .38,
            }));

            api.init('alertas-drawer-evolucion', canvas, {
                type: 'line',
                data: {
                    labels: this.labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : api.number('--rm-chart-line-enter-duration', 650),
                        easing: api.getCss('--rm-chart-js-easing')
                    },
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        x: {
                            display: true,
                            grid: { color: gridColor, drawBorder: false },
                            ticks: {
                                color: axisTextColor,
                                font: { family: 'Outfit, Nunito Sans, system-ui, sans-serif', size: 10, weight: '600' },
                                maxRotation: 0,
                            }
                        },
                        y: {
                            display: true,
                            min: yMin,
                            max: yMax,
                            grid: { color: gridColor, drawBorder: false },
                            ticks: {
                                color: axisTextColor,
                                font: { family: 'Outfit, Nunito Sans, system-ui, sans-serif', size: 10, weight: '600' },
                                stepSize: yStep,
                                callback: (val) => val + ' ' + unit
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        datalabels: {
                            display: true,
                            align: 'top',
                            offset: 3,
                            borderRadius: 4,
                            padding: 2,
                            backgroundColor: surfaceRaised,
                            borderColor: (ctx) => ctx.dataset.borderColor,
                            borderWidth: 1,
                            color: (ctx) => ctx.dataset.borderColor,
                            font: { size: 9.5, weight: 'bold', family: 'Outfit, Nunito Sans, sans-serif' },
                            formatter: (v) => v !== null && v !== undefined ? v : ''
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: surfaceRaised,
                            titleColor: tooltipText,
                            bodyColor: tooltipText,
                            padding: 10,
                            cornerRadius: 8,
                            titleFont: { family: 'Outfit, Nunito Sans, sans-serif', size: 11, weight: '700' },
                            bodyFont: { family: 'Outfit, Nunito Sans, sans-serif', size: 11, weight: '500' },
                            callbacks: {
                                label: (c) => ` ${c.dataset.label}: ${c.parsed.y} ${unit}`
                            }
                        }
                    }
                }
            });
        }
    }" class="space-y-4">
        {{-- CARD DEL RESIDENTE --}}
        <div class="p-3.5 rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] flex items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-3 min-w-0">
                @if($adultoDrawer->foto_url)
                    <img src="{{ $adultoDrawer->foto_url }}" alt="{{ $adultoDrawer->nombre_completo }}" class="h-11 w-11 shrink-0 rounded-xl object-cover border border-[var(--rm-border-soft)] shadow-2xs" />
                @else
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[var(--rm-action-primary)] text-white font-bold text-sm shadow-2xs">
                        {{ substr($adultoDrawer->nombres, 0, 1) }}{{ substr($adultoDrawer->ap_paterno, 0, 1) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <h3 class="font-bold text-[var(--rm-text-primary)] text-sm leading-tight truncate uppercase">
                        {{ $adultoDrawer->ap_paterno }} {{ $adultoDrawer->ap_materno }} {{ $adultoDrawer->nombres }}
                    </h3>
                    <div class="flex items-center gap-2 text-xs text-[var(--rm-text-secondary)] mt-0.5 flex-wrap">
                        <span>{{ $adultoDrawer->edad_texto }}</span>
                        <span>•</span>
                        <span>CI: {{ $adultoDrawer->ci ?: 'Documento S/D' }}</span>
                        <span>•</span>
                        <span class="font-mono text-[11px]">{{ $adultoDrawer->cod_residente }}</span>
                    </div>
                </div>
            </div>

            <div class="text-right shrink-0">
                <span class="inline-block px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] text-[var(--rm-text-primary)] font-semibold text-xs">
                    {{ $adultoDrawer->ubicacion_texto }}
                </span>
                <div class="mt-1 flex items-center justify-end gap-1.5">
                    <span class="text-[10.5px] text-[var(--rm-text-secondary)]">Estado:</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $adultoDrawer->estado_badge_color }}">
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        {{ $adultoDrawer->estado_humano }}
                    </span>
                </div>
            </div>
        </div>

        {{-- RESUMEN DE CONSTANTES VITALES --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
            <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] shadow-2xs">
                <span class="text-[10px] font-bold uppercase text-[var(--rm-text-secondary)] block">Presión</span>
                <p class="font-bold text-[var(--rm-text-primary)] text-sm mt-0.5 font-mono">
                    {{ $ultSigno?->presion_sistolica ? $ultSigno->presion_sistolica.'/'.$ultSigno->presion_diastolica : ($ultSigno?->presion_arterial ?: '--') }}
                </p>
                <span class="text-[10px] text-[var(--rm-text-secondary)]">mmHg</span>
            </div>
            <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] shadow-2xs">
                <span class="text-[10px] font-bold uppercase text-[var(--rm-text-secondary)] block">Pulso</span>
                <p class="font-bold text-[var(--rm-text-primary)] text-sm mt-0.5 font-mono">
                    {{ $ultSigno?->frecuencia_cardiaca ?: '--' }}
                </p>
                <span class="text-[10px] text-[var(--rm-text-secondary)]">lpm</span>
            </div>
            <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] shadow-2xs">
                <span class="text-[10px] font-bold uppercase text-[var(--rm-text-secondary)] block">SpO₂</span>
                <p class="font-bold text-[var(--rm-text-primary)] text-sm mt-0.5 font-mono">
                    {{ $ultSigno?->saturacion ? $ultSigno->saturacion.'%' : '--' }}
                </p>
                <span class="text-[10px] text-[var(--rm-text-secondary)]">saturación</span>
            </div>
            <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] shadow-2xs">
                <span class="text-[10px] font-bold uppercase text-[var(--rm-text-secondary)] block">Temp.</span>
                <p class="font-bold text-[var(--rm-text-primary)] text-sm mt-0.5 font-mono">
                    {{ $ultSigno?->temperatura ? $ultSigno->temperatura.'°C' : '--' }}
                </p>
                <span class="text-[10px] text-[var(--rm-text-secondary)]">grados</span>
            </div>
        </div>

        {{-- SELECTOR DE VARIABLE Y GRÁFICO --}}
        <div class="p-4 rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] space-y-3 shadow-2xs">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[var(--rm-border-soft)] pb-2.5">
                <div class="flex items-center gap-2">
                    <button type="button" @click="tab = 'presion'" :class="tab === 'presion' ? 'bg-[var(--rm-action-primary)] text-white' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)]'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition">PA</button>
                    <button type="button" @click="tab = 'pulso'" :class="tab === 'pulso' ? 'bg-[var(--rm-action-primary)] text-white' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)]'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition">Pulso</button>
                    <button type="button" @click="tab = 'spo2'" :class="tab === 'spo2' ? 'bg-[var(--rm-action-primary)] text-white' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)]'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition">SpO₂</button>
                    <button type="button" @click="tab = 'temp'" :class="tab === 'temp' ? 'bg-[var(--rm-action-primary)] text-white' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)]'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition">Temp</button>
                </div>
                <span class="text-[11px] text-[var(--rm-text-secondary)] font-mono">Tendencia últimos controles</span>
            </div>

            <div class="relative h-48 w-full">
                <canvas id="chart-drawer-evolucion"></canvas>
            </div>
        </div>

        {{-- DETALLE CLÍNICO Y REGISTRO --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="p-3.5 rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] space-y-2 shadow-2xs">
                <div class="flex items-center justify-between border-b border-[var(--rm-border-soft)] pb-1.5">
                    <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[var(--rm-text-primary)]">
                        <i class="ph-bold ph-stethoscope text-[var(--rm-action-primary)] text-base"></i>
                        <span>Detalle Clínico</span>
                    </span>
                    <span class="text-[10px] font-bold text-[var(--rm-action-primary)] bg-[var(--rm-action-primary-soft)] px-1.5 py-0.5 rounded border border-[var(--rm-action-primary)]/30">
                        Diagnóstico activo
                    </span>
                </div>
                <div class="space-y-1.5 text-xs pt-1">
                    <div class="flex justify-between items-center">
                        <span class="text-[var(--rm-text-secondary)]">Estado general:</span>
                        <span class="text-[var(--rm-text-primary)] font-medium">{{ $adultoDrawer->estado_humano ?? 'Sin evaluación registrada' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[var(--rm-text-secondary)]">Nivel de cuidado:</span>
                        <span class="text-[var(--rm-text-primary)] font-medium">{{ $adultoDrawer->planCuidadoActivo?->nivel_cuidado ?: 'Sin nivel registrado' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[var(--rm-text-secondary)]">Diagnóstico ppal:</span>
                        <span class="text-[var(--rm-text-primary)] font-semibold truncate max-w-[170px]" title="{{ $diagnosticosTexto ?: 'Sin diagnóstico principal' }}">
                            {{ $diagnosticosTexto ?: 'Sin diagnóstico principal' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[var(--rm-text-secondary)]">Alergias:</span>
                        <span class="text-[var(--rm-danger)] font-bold truncate max-w-[170px]" title="{{ $alergiasTexto ?: 'Sin alergias' }}">
                            {{ $alergiasTexto ?: 'Sin alergias conocidas' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] space-y-2 shadow-2xs">
                <div class="flex items-center justify-between border-b border-[var(--rm-border-soft)] pb-1.5">
                    <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[var(--rm-text-primary)]">
                        <i class="ph-bold ph-clock-counter-clockwise text-[var(--rm-action-primary)] text-base"></i>
                        <span>Registro Clínico</span>
                    </span>
                    @if(\Illuminate\Support\Facades\Route::has('admin.cuidados.pacientes.ficha'))
                        <a href="{{ route('admin.cuidados.pacientes.ficha', $adultoDrawer->cod_residente) }}"
                           class="text-[10px] font-bold text-[var(--rm-action-primary)] hover:underline">
                            Ver todos →
                        </a>
                    @else
                        <span class="text-[10px] text-[var(--rm-text-secondary)] font-bold">Recientes</span>
                    @endif
                </div>

                <div class="space-y-2 pt-1 max-h-40 overflow-y-auto">
                    @forelse($registrosTimeline as $reg)
                        <div class="p-2 rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] space-y-0.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[11px] text-[var(--rm-text-primary)]">
                                    {{ $reg['tipo'] }} · <span class="text-[var(--rm-text-secondary)] font-normal">{{ $reg['profesional'] }}</span>
                                </span>
                                <span class="text-[10px] font-mono text-[var(--rm-text-secondary)]">{{ $reg['fecha'] }}</span>
                            </div>
                            <p class="text-[11px] text-[var(--rm-text-primary)] line-clamp-2 leading-tight">
                                {{ $reg['resumen'] }}
                            </p>
                        </div>
                    @empty
                        <p class="text-xs text-[var(--rm-text-secondary)] italic py-2 text-center">Sin registros recientes</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- HISTORIAL DE CONTROLES --}}
        <div class="space-y-2 pt-1">
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[var(--rm-text-primary)]">
                    <i class="ph-bold ph-table text-[var(--rm-action-primary)] text-base"></i>
                    <span>Historial de Controles Recientes</span>
                </span>
                <span class="text-[10.5px] text-[var(--rm-text-secondary)] font-mono">Últimos {{ count($historialSignos) }} registros</span>
            </div>

            <div class="rounded-xl border border-[var(--rm-border-soft)] overflow-hidden shadow-2xs">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[var(--rm-surface-soft)] text-[10.5px] font-bold uppercase tracking-wider text-[var(--rm-text-secondary)] border-b border-[var(--rm-border-soft)]">
                        <tr>
                            <th class="p-2">Fecha/Hora</th>
                            <th class="p-2">PA</th>
                            <th class="p-2">Pulso</th>
                            <th class="p-2">SpO₂</th>
                            <th class="p-2">Temp</th>
                            <th class="p-2">Registrado por</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--rm-border-soft)]/60 bg-[var(--rm-surface)]">
                        @forelse($historialSignos as $s)
                            <tr class="hover:bg-[var(--rm-surface-soft)]/60 transition">
                                <td class="p-2 font-mono text-[10.5px] whitespace-nowrap">
                                    {{ ($s->fecha ? $s->fecha->format('d/m/Y') : '') . ' ' . ($s->hora ? substr($s->hora, 0, 5) : '') }}
                                </td>
                                <td class="p-2 font-mono font-bold text-[var(--rm-text-primary)] whitespace-nowrap">
                                    {{ $s->presion_sistolica ? $s->presion_sistolica.'/'.$s->presion_diastolica : ($s->presion_arterial ?: '--') }}
                                </td>
                                <td class="p-2 font-mono whitespace-nowrap">
                                    {{ $s->frecuencia_cardiaca ? $s->frecuencia_cardiaca.' lpm' : '--' }}
                                </td>
                                <td class="p-2 font-mono whitespace-nowrap">
                                    {{ $s->saturacion ? $s->saturacion.'%' : '--' }}
                                </td>
                                <td class="p-2 font-mono whitespace-nowrap">
                                    {{ $s->temperatura ? $s->temperatura.'°C' : '--' }}
                                </td>
                                <td class="p-2 text-[10.5px] text-[var(--rm-text-secondary)] truncate max-w-[110px]" title="{{ $s->registradoPor->name ?? 'Profesional no registrado' }}">
                                    {{ $s->registradoPor->name ?? 'Profesional no registrado' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-4 text-center text-xs text-[var(--rm-text-secondary)] italic">
                                    No hay registros de signos vitales disponibles.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <div class="flex w-full flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2">
                <button type="button"
                        wire:click="verUbicacion('{{ $adultoDrawer->cod_residente }}')"
                        class="rm-btn rm-btn-secondary rm-btn-sm cursor-pointer shadow-2xs">
                    <i class="ph-bold ph-bed text-base"></i>
                    <span>Ver ubicación y cama</span>
                </button>

                @if(\Illuminate\Support\Facades\Route::has('admin.cuidados.pacientes.ficha'))
                    <a href="{{ route('admin.cuidados.pacientes.ficha', $adultoDrawer->cod_residente) }}"
                       class="rm-btn rm-btn-ghost rm-btn-sm cursor-pointer">
                        <i class="ph-bold ph-user-circle text-base"></i>
                        <span>Ficha médica</span>
                    </a>
                @endif
            </div>

            <div class="flex items-center gap-2">
                @if(isset($alertaId) && $alertaId)
                    <button type="button"
                            wire:click="atenderAlerta('{{ $alertaId }}')"
                            class="rm-btn rm-btn-primary rm-btn-sm cursor-pointer shadow-2xs">
                        <i class="ph-bold ph-plus-circle text-base"></i>
                        <span>Registrar atención</span>
                    </button>
                @endif

                <button type="button"
                        wire:click="cerrarDrawer"
                        class="rm-btn rm-btn-ghost rm-btn-sm cursor-pointer">
                    Cerrar panel
                </button>
            </div>
        </div>
    </x-slot:footer>
</x-ui.drawer-livewire>
@endif
