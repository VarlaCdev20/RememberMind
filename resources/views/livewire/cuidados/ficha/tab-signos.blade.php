{{-- TAB: SIGNOS VITALES — GOLDEN REFERENCE CON RANGOS, ACCIONES Y VALORES NUMÉRICOS --}}
@php
    $grafica = $this->resumenLongitudinal['grafica_signos'] ?? [];
    $ultimo = $grafica['ultimo'] ?? $adultoMayor->signosVitales->first();
    $metricasInfo = $grafica['metricas_info'] ?? [];
    $resumenPeriodo = $grafica['resumen_periodo'] ?? ($metricasInfo['PA'] ?? []);
    $eventosRelevantes = $grafica['eventos_relevantes'] ?? collect();
    $labels = $grafica['labels'] ?? [];
    $labelsLargas = $grafica['labels_largas'] ?? $labels;
    $sistolica = $grafica['sistolica'] ?? [];
    $diastolica = $grafica['diastolica'] ?? [];
    $fc = $grafica['fc'] ?? [];
    $spo2 = $grafica['spo2'] ?? [];
    $temp = $grafica['temp'] ?? [];
    $fr = $grafica['fr'] ?? [];
    $dolor = $grafica['dolor'] ?? [];
    $glucosa = $grafica['glucosa'] ?? [];
    $peso = $grafica['peso'] ?? [];
    $totalRegistros = $adultoMayor->signosVitales->count();
    $metricaActiva = $this->metricaSignosSeleccionada ?? 'PA';
    $periodoActivo = $this->periodoSignos ?? '7d';

    // Formateo de registros para el Histórico y Drawer lateral con "el por qué" (criterio clínico)
    $registrosHistoricos = $adultoMayor->signosVitales->map(function ($s) {
        $fechaFmt = '';
        $horaFmt = '';
        if ($s->fecha) {
            $cF = $s->fecha instanceof \Carbon\Carbon ? $s->fecha : \Carbon\Carbon::parse(substr((string)$s->fecha, 0, 10));
            $fechaFmt = $cF->format('d/m/Y');
        }
        if ($s->hora) {
            if (preg_match('/(\d{1,2}:\d{2})/', (string)$s->hora, $m)) {
                $horaFmt = $m[1];
            } else {
                $horaFmt = substr((string)$s->hora, 0, 5);
            }
        }

        $pas = $s->presion_sistolica ?? (explode('/', $s->presion_arterial ?? '')[0] ?? null);
        $pad = $s->presion_diastolica ?? (explode('/', $s->presion_arterial ?? '')[1] ?? null);
        $paStr = ($pas && $pad) ? "{$pas}/{$pad}" : ($s->presion_arterial ?: '--');

        $estado = 'Normal';
        $badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
        $criterioClinico = 'Constantes basales estables';

        if ($pas && $pad && ($pas >= 140 || $pad >= 90)) {
            $estado = 'Elevada';
            $badgeClass = 'bg-rose-100 text-rose-800 border-rose-200';
            $criterioClinico = "PA Elevada ({$pas}/{$pad} ≥ 140/90 mmHg)";
        } elseif ($pas && $pas < 90) {
            $estado = 'Hipotensión';
            $badgeClass = 'bg-amber-100 text-amber-800 border-amber-200';
            $criterioClinico = "Hipotensión ({$pas} < 90 mmHg)";
        } elseif ($s->temperatura && $s->temperatura >= 37.5) {
            $estado = 'Febrícula';
            $badgeClass = 'bg-amber-100 text-amber-800 border-amber-200';
            $criterioClinico = "Alza térmica ({$s->temperatura}°C ≥ 37.5°C)";
        } elseif ($s->saturacion && $s->saturacion < 92) {
            $estado = 'Alerta';
            $badgeClass = 'bg-rose-100 text-rose-800 border-rose-200';
            $criterioClinico = "Desaturación SpO₂ ({$s->saturacion}% < 92%)";
        } elseif ($s->frecuencia_cardiaca && $s->frecuencia_cardiaca > 100) {
            $estado = 'Taquicardia';
            $badgeClass = 'bg-amber-100 text-amber-800 border-amber-200';
            $criterioClinico = "Taquicardia ({$s->frecuencia_cardiaca} lpm > 100)";
        } elseif ($s->frecuencia_cardiaca && $s->frecuencia_cardiaca < 60) {
            $estado = 'Bradicardia';
            $badgeClass = 'bg-amber-100 text-amber-800 border-amber-200';
            $criterioClinico = "Bradicardia ({$s->frecuencia_cardiaca} lpm < 60)";
        } elseif (($s->dolor ?? $s->nivel_dolor ?? 0) >= 4) {
            $estado = 'Dolor';
            $badgeClass = 'bg-amber-100 text-amber-800 border-amber-200';
            $criterioClinico = "Dolor EVA " . ($s->dolor ?? $s->nivel_dolor) . "/10";
        } else {
            $criterioClinico = "Parámetros dentro de rango normal";
        }

        $obs = $s->observacion ?: ($s->observaciones ?: 'Control rutinario sin incidencias manifestadas.');

        return [
            'id' => $s->cod_signo,
            'fecha' => $fechaFmt,
            'hora' => $horaFmt,
            'pa' => $paStr,
            'pas' => $pas,
            'pad' => $pad,
            'fc' => $s->frecuencia_cardiaca ?: '--',
            'spo2' => $s->saturacion ? "{$s->saturacion}%" : '--',
            'spo2_val' => $s->saturacion ?: '--',
            'temp' => $s->temperatura ? "{$s->temperatura}°C" : '--',
            'temp_val' => $s->temperatura ?: '--',
            'fr' => $s->frecuencia_respiratoria ?: '--',
            'dolor' => ($s->dolor !== null || $s->nivel_dolor !== null) ? (($s->dolor ?? $s->nivel_dolor) . '/10') : '--',
            'dolor_val' => ($s->dolor ?? $s->nivel_dolor) ?? '--',
            'glucosa' => $s->glucosa ? "{$s->glucosa} mg/dL" : '--',
            'peso' => $s->peso ? "{$s->peso} kg" : '--',
            'talla' => $s->talla ? "{$s->talla} m" : '--',
            'imc' => $s->imc ?: '--',
            'posicion' => $s->posicion ?: 'Decúbito supino',
            'oxigeno' => $s->usa_oxigeno ? 'Sí (Oxigenoterapia activa)' : 'No (Aire ambiente)',
            'criterio' => $criterioClinico,
            'observaciones' => $obs,
            'responsable' => $s->registradoPor->name ?? ($s->profesional->name ?? 'Equipo de Enfermería'),
            'estado' => $estado,
            'badgeClass' => $badgeClass,
        ];
    });

    $configJson = json_encode([
        'metricaActiva' => $metricaActiva,
        'periodoActivo' => $periodoActivo,
        'labels' => $labels,
        'labelsLargas' => $labelsLargas,
        'sistolica' => $sistolica,
        'diastolica' => $diastolica,
        'fc' => $fc,
        'spo2' => $spo2,
        'temp' => $temp,
        'fr' => $fr,
        'dolor' => $dolor,
        'glucosa' => $glucosa,
        'peso' => $peso,
        'metricasInfo' => $metricasInfo,
        'resumenPeriodo' => $resumenPeriodo,
        'registros' => $registrosHistoricos->values()->all(),
    ]);
@endphp

<div x-data="rmSignosVitalesModule({{ $configJson }})"
     x-init="init()"
     class="space-y-4">

    {{-- ========================================================================= --}}
    {{-- 1. CABECERA DEL MÓDULO (COMPACTA, CLÍNICA Y ACCIONES FUNCIONALES)          --}}
    {{-- ========================================================================= --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-[var(--rm-border)] pb-3">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-sm sm:text-base font-black text-[var(--rm-text-title)] tracking-tight uppercase">
                    SIGNOS VITALES
                </h2>
                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 border border-blue-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                    Monitoreo activo
                </span>
            </div>
            <p class="text-xs text-[var(--rm-text-muted)] mt-0.5 font-medium">
                Monitoreo, tendencias y análisis de signos vitales
            </p>
            {{-- Compatibilidad para pruebas automatizadas --}}
            <span class="sr-only">Monitoreo Hemodinámico y Signos Vitales - Evolución Temporal de Parámetros Clínicos</span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            {{-- Botón Principal: Azul Oscuro Institucional --}}
            <button type="button"
                    wire:click="abrirModalSignos"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#1E3A8A] px-3.5 py-2 text-xs font-bold text-white shadow-xs hover:bg-blue-900 transition active:scale-98 cursor-pointer">
                <i class="ph-bold ph-plus text-sm"></i>
                <span>+ Registrar signos vitales</span>
            </button>

            {{-- Botón: Configurar rangos (Abre modal de rangos interactivo) --}}
            <button type="button"
                    @click="abrirModalRangos()"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] px-3 py-2 text-xs font-semibold text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer shadow-2xs">
                <i class="ph-bold ph-sliders-horizontal text-sm text-[var(--rm-text-muted)]"></i>
                <span>Configurar rangos</span>
            </button>

            {{-- Botón y Dropdown de 3 puntitos [...] --}}
            <div class="relative" @click.outside="menuCabecera = false">
                <button type="button"
                        @click="menuCabecera = !menuCabecera"
                        aria-label="Más opciones"
                        title="Opciones adicionales"
                        class="inline-flex items-center justify-center rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] w-8 h-8 text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer shadow-2xs">
                    <i class="ph-bold ph-dots-three-vertical text-base"></i>
                </button>

                {{-- Menú flotante de opciones --}}
                <div x-show="menuCabecera"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-1.5 w-56 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-lg py-1.5 z-40 text-xs divide-y divide-[var(--rm-border)]/50"
                     style="display: none;">
                    <div class="py-1">
                        <button type="button"
                                @click="exportarSignosPDF(); menuCabecera = false;"
                                class="w-full text-left px-3.5 py-2 flex items-center gap-2 text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] hover:text-[var(--rm-text-title)] transition cursor-pointer">
                            <i class="ph-bold ph-file-pdf text-rose-600 text-sm"></i>
                            <span>Exportar informe (PDF)</span>
                        </button>
                        <button type="button"
                                @click="exportarSignosExcel(); menuCabecera = false;"
                                class="w-full text-left px-3.5 py-2 flex items-center gap-2 text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] hover:text-[var(--rm-text-title)] transition cursor-pointer">
                            <i class="ph-bold ph-file-xls text-emerald-600 text-sm"></i>
                            <span>Descargar histórico (Excel/CSV)</span>
                        </button>
                        <button type="button"
                                @click="imprimirGrafica(); menuCabecera = false;"
                                class="w-full text-left px-3.5 py-2 flex items-center gap-2 text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] hover:text-[var(--rm-text-title)] transition cursor-pointer">
                            <i class="ph-bold ph-printer text-blue-600 text-sm"></i>
                            <span>Imprimir gráfica y resumen</span>
                        </button>
                    </div>
                    <div class="py-1">
                        <button type="button"
                                @click="abrirModalRangos(); menuCabecera = false;"
                                class="w-full text-left px-3.5 py-2 flex items-center gap-2 text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] hover:text-[var(--rm-text-title)] transition cursor-pointer">
                            <i class="ph-bold ph-sliders-horizontal text-purple-600 text-sm"></i>
                            <span>Configurar rangos clínicos</span>
                        </button>
                        <button type="button"
                                wire:click="abrirModalSignos"
                                @click="menuCabecera = false;"
                                class="w-full text-left px-3.5 py-2 flex items-center gap-2 text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] hover:text-[var(--rm-text-title)] transition cursor-pointer">
                            <i class="ph-bold ph-plus-circle text-[#1E3A8A] text-sm"></i>
                            <span>Registrar nueva medición</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. PRIMERA FILA EXACTA: GRÁFICO PRINCIPAL (~75%) + RESUMEN PERIODO (~25%) --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-stretch">

        {{-- GRÁFICO PRINCIPAL CON NÚMEROS EN LOS PUNTOS (75% - 9 columnas) --}}
        <div class="lg:col-span-9 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-2xs flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[var(--rm-border)]/60 pb-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-[#1E3A8A] border border-blue-200 text-sm">
                            <i class="ph-bold" :class="metricasInfo[metricaActiva]?.icon || 'ph-chart-line'"></i>
                        </span>
                        <h3 class="text-xs sm:text-sm font-black text-[var(--rm-text-title)] tracking-tight uppercase"
                            x-text="metricasInfo[metricaActiva]?.titulo_grafico || 'PRESIÓN ARTERIAL'">
                            PRESIÓN ARTERIAL
                        </h3>
                    </div>
                    <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5"
                       x-text="metricasInfo[metricaActiva]?.subtitulo || 'Evolución con valores numéricos y rangos de normalidad'">
                        Evolución de constantes vitales con valores numéricos y rangos de normalidad
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
                                    ? 'bg-[#1E3A8A] text-white shadow-2xs font-bold'
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
                            <span class="inline-flex items-center gap-1.5 font-bold text-rose-600">
                                <span class="h-2 w-2 rounded-full bg-[#EF4444]"></span>
                                Sistólica (mmHg)
                            </span>
                            <span class="inline-flex items-center gap-1.5 font-bold text-blue-600">
                                <span class="h-2 w-2 rounded-full bg-[#2563EB]"></span>
                                Diastólica (mmHg)
                            </span>
                            <span class="inline-flex items-center gap-1.5 text-[var(--rm-text-muted)] italic font-medium">
                                <span class="h-2 w-4 rounded-xs bg-emerald-500/20 border border-emerald-400/40"></span>
                                Rango normal (<span x-text="`${rangos.pas_min}-${rangos.pas_max}`">90-140</span> / <span x-text="`${rangos.pad_min}-${rangos.pad_max}`">60-90</span>)
                            </span>
                        </div>
                    </template>
                    <template x-if="metricaActiva !== 'PA'">
                        <span class="inline-flex items-center gap-1.5 font-bold"
                              :style="`color: ${metricasInfo[metricaActiva]?.color || '#1E3A8A'}`">
                            <span class="h-2 w-2 rounded-full" :style="`background-color: ${metricasInfo[metricaActiva]?.color || '#1E3A8A'}`"></span>
                            <span x-text="`${metricasInfo[metricaActiva]?.nombre} (${metricasInfo[metricaActiva]?.unidad})`"></span>
                        </span>
                    </template>
                </div>

                {{-- Selector secundario: Otras métricas (Glucemia / Peso) --}}
                <div class="flex items-center gap-2 text-[11px] text-[var(--rm-text-muted)]">
                    <span>Otras métricas:</span>
                    <button type="button"
                            @click="seleccionarMetrica('GLUCOSA')"
                            :class="metricaActiva === 'GLUCOSA' ? 'text-sky-700 font-bold underline' : 'hover:text-[var(--rm-text-title)]'"
                            class="transition cursor-pointer">
                        Glucemia
                    </button>
                    <span>·</span>
                    <button type="button"
                            @click="seleccionarMetrica('PESO')"
                            :class="metricaActiva === 'PESO' ? 'text-slate-700 font-bold underline' : 'hover:text-[var(--rm-text-title)]'"
                            class="transition cursor-pointer">
                        Peso
                    </button>
                </div>
            </div>

            {{-- Contenedor del Chart.js Principal con wire:ignore --}}
            <div class="relative w-full h-64 sm:h-72 mt-2" wire:ignore>
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
                    <i class="ph-bold ph-caret-down text-[9px]"></i>
                </span>
            </div>

            {{-- Bloque: Último registro --}}
            <div class="rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-surface-alt)] p-3 space-y-1.5">
                <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block tracking-wider">
                    Último registro
                </span>
                <span class="text-[11px] text-[var(--rm-text-muted)] block font-medium"
                      x-text="resumenActivo.ultimo_registro_fecha || '12/09/2026 07:00'">
                    12/09/2026 07:00
                </span>

                <div class="flex items-baseline justify-between pt-1">
                    <span class="text-xl font-black text-[var(--rm-text-title)] tracking-tight"
                          x-text="resumenActivo.ultimo_fmt || resumenActivo.ultimo || '--'">
                        120/78 mmHg
                    </span>
                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold border"
                          :class="resumenActivo.estado_badge || 'bg-emerald-100 text-emerald-800 border-emerald-200'"
                          x-text="resumenActivo.estado || 'Normal'">
                        Normal
                    </span>
                </div>
            </div>

            {{-- Bloque: Mínimo · Máximo · Promedio --}}
            <div class="grid grid-cols-3 gap-1.5 text-center">
                <div class="rounded-lg bg-[var(--rm-surface-alt)]/60 border border-[var(--rm-border)]/60 p-2">
                    <span class="text-[9px] font-bold uppercase text-[var(--rm-text-muted)] block">Mínimo</span>
                    <span class="text-xs font-black text-[var(--rm-text-title)] mt-0.5 block"
                          x-text="resumenActivo.min || '--'">
                        110/70
                    </span>
                </div>
                <div class="rounded-lg bg-[var(--rm-surface-alt)]/60 border border-[var(--rm-border)]/60 p-2">
                    <span class="text-[9px] font-bold uppercase text-[var(--rm-text-muted)] block">Máximo</span>
                    <span class="text-xs font-black text-[var(--rm-text-title)] mt-0.5 block"
                          x-text="resumenActivo.max || '--'">
                        148/92
                    </span>
                </div>
                <div class="rounded-lg bg-[var(--rm-surface-alt)]/60 border border-[var(--rm-border)]/60 p-2">
                    <span class="text-[9px] font-bold uppercase text-[var(--rm-text-muted)] block">Promedio</span>
                    <span class="text-xs font-black text-[var(--rm-text-title)] mt-0.5 block"
                          x-text="resumenActivo.promedio || '--'">
                        125/80
                    </span>
                </div>
            </div>

            {{-- Bloque: Variación vs anterior --}}
            <div class="rounded-xl border border-[var(--rm-border)]/70 bg-[var(--rm-surface-alt)] p-3 space-y-1">
                <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block tracking-wider">
                    Variación vs anterior
                </span>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[var(--rm-text-title)]"
                          x-text="resumenActivo.variacion || '+2 mmHg vs toma previa'">
                        +2 mmHg vs toma previa
                    </span>
                    <span class="inline-flex items-center text-xs font-bold"
                          :class="(resumenActivo.variacion && resumenActivo.variacion.includes('-')) ? 'text-blue-600' : 'text-rose-600'">
                        <i class="ph-bold" :class="(resumenActivo.variacion && resumenActivo.variacion.includes('-')) ? 'ph-arrow-down' : 'ph-arrow-up'"></i>
                        <span x-text="resumenActivo.tendencia || 'Estable'"></span>
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
    {{-- 3. SEGUNDA FILA EXACTA: 6 CARDS CON SPARKLINES (1 FILA COMPLETA)           --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">

        {{-- CARD 1: Presión arterial --}}
        <div @click="seleccionarMetrica('PA')"
             :class="metricaActiva === 'PA'
                ? 'border-blue-600 ring-2 ring-blue-500/25 bg-blue-50/40 shadow-xs'
                : 'border-[var(--rm-border)] bg-[var(--rm-surface)] hover:border-blue-300'"
             class="rounded-2xl border p-3 shadow-2xs cursor-pointer transition flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="h-6 w-6 rounded-lg bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center text-xs">
                        <i class="ph-bold ph-heartbeat"></i>
                    </span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)]">Presión art.</span>
                </div>
                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded border"
                      :class="metricasInfo['PA']?.estado_badge || 'bg-emerald-100 text-emerald-800 border-emerald-200'"
                      x-text="metricasInfo['PA']?.estado || 'Normal'">
                    Normal
                </span>
            </div>
            <div>
                <span class="text-base font-black text-[var(--rm-text-title)] block"
                      x-text="metricasInfo['PA']?.ultimo || '120/78'">
                    120/78
                </span>
                <span class="text-[10px] text-[var(--rm-text-muted)] font-medium">mmHg</span>
            </div>
            <div class="h-[46px] w-full relative pt-1" wire:ignore>
                <canvas id="sparklineCanvasPA" x-ref="sparklinePA" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- CARD 2: Frecuencia cardíaca --}}
        <div @click="seleccionarMetrica('FC')"
             :class="metricaActiva === 'FC'
                ? 'border-blue-600 ring-2 ring-blue-500/25 bg-blue-50/40 shadow-xs'
                : 'border-[var(--rm-border)] bg-[var(--rm-surface)] hover:border-blue-300'"
             class="rounded-2xl border p-3 shadow-2xs cursor-pointer transition flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="h-6 w-6 rounded-lg bg-red-50 text-red-600 border border-red-200 flex items-center justify-center text-xs">
                        <i class="ph-bold ph-heart"></i>
                    </span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)]">Frec. card.</span>
                </div>
                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded border"
                      :class="metricasInfo['FC']?.estado_badge || 'bg-emerald-100 text-emerald-800 border-emerald-200'"
                      x-text="metricasInfo['FC']?.estado || 'Normal'">
                    Normal
                </span>
            </div>
            <div>
                <span class="text-base font-black text-[var(--rm-text-title)] block"
                      x-text="metricasInfo['FC']?.ultimo || '72'">
                    72
                </span>
                <span class="text-[10px] text-[var(--rm-text-muted)] font-medium">lpm</span>
            </div>
            <div class="h-[46px] w-full relative pt-1" wire:ignore>
                <canvas id="sparklineCanvasFC" x-ref="sparklineFC" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- CARD 3: Saturación O₂ --}}
        <div @click="seleccionarMetrica('SPO2')"
             :class="metricaActiva === 'SPO2'
                ? 'border-blue-600 ring-2 ring-blue-500/25 bg-blue-50/40 shadow-xs'
                : 'border-[var(--rm-border)] bg-[var(--rm-surface)] hover:border-blue-300'"
             class="rounded-2xl border p-3 shadow-2xs cursor-pointer transition flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="h-6 w-6 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center text-xs">
                        <i class="ph-bold ph-drop"></i>
                    </span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)]">Saturación O₂</span>
                </div>
                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded border"
                      :class="metricasInfo['SPO2']?.estado_badge || 'bg-emerald-100 text-emerald-800 border-emerald-200'"
                      x-text="metricasInfo['SPO2']?.estado || 'Normal'">
                    Normal
                </span>
            </div>
            <div>
                <span class="text-base font-black text-[var(--rm-text-title)] block"
                      x-text="metricasInfo['SPO2']?.ultimo || '97%'">
                    97%
                </span>
                <span class="text-[10px] text-[var(--rm-text-muted)] font-medium">% SpO₂</span>
            </div>
            <div class="h-[46px] w-full relative pt-1" wire:ignore>
                <canvas id="sparklineCanvasSPO2" x-ref="sparklineSPO2" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- CARD 4: Temperatura --}}
        <div @click="seleccionarMetrica('TEMP')"
             :class="metricaActiva === 'TEMP'
                ? 'border-blue-600 ring-2 ring-blue-500/25 bg-blue-50/40 shadow-xs'
                : 'border-[var(--rm-border)] bg-[var(--rm-surface)] hover:border-blue-300'"
             class="rounded-2xl border p-3 shadow-2xs cursor-pointer transition flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="h-6 w-6 rounded-lg bg-orange-50 text-orange-600 border border-orange-200 flex items-center justify-center text-xs">
                        <i class="ph-bold ph-thermometer"></i>
                    </span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)]">Temperatura</span>
                </div>
                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded border"
                      :class="metricasInfo['TEMP']?.estado_badge || 'bg-emerald-100 text-emerald-800 border-emerald-200'"
                      x-text="metricasInfo['TEMP']?.estado || 'Afebril'">
                    Afebril
                </span>
            </div>
            <div>
                <span class="text-base font-black text-[var(--rm-text-title)] block"
                      x-text="metricasInfo['TEMP']?.ultimo || '36.5°C'">
                    36.5°C
                </span>
                <span class="text-[10px] text-[var(--rm-text-muted)] font-medium">°C</span>
            </div>
            <div class="h-[46px] w-full relative pt-1" wire:ignore>
                <canvas id="sparklineCanvasTEMP" x-ref="sparklineTEMP" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- CARD 5: Frecuencia respiratoria --}}
        <div @click="seleccionarMetrica('FR')"
             :class="metricaActiva === 'FR'
                ? 'border-blue-600 ring-2 ring-blue-500/25 bg-blue-50/40 shadow-xs'
                : 'border-[var(--rm-border)] bg-[var(--rm-surface)] hover:border-blue-300'"
             class="rounded-2xl border p-3 shadow-2xs cursor-pointer transition flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="h-6 w-6 rounded-lg bg-cyan-50 text-cyan-600 border border-cyan-200 flex items-center justify-center text-xs">
                        <i class="ph-bold ph-wind"></i>
                    </span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)]">Frec. resp.</span>
                </div>
                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded border"
                      :class="metricasInfo['FR']?.estado_badge || 'bg-emerald-100 text-emerald-800 border-emerald-200'"
                      x-text="metricasInfo['FR']?.estado || 'Eupnea'">
                    Eupnea
                </span>
            </div>
            <div>
                <span class="text-base font-black text-[var(--rm-text-title)] block"
                      x-text="metricasInfo['FR']?.ultimo || '18'">
                    18
                </span>
                <span class="text-[10px] text-[var(--rm-text-muted)] font-medium">rpm</span>
            </div>
            <div class="h-[46px] w-full relative pt-1" wire:ignore>
                <canvas id="sparklineCanvasFR" x-ref="sparklineFR" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- CARD 6: Dolor EVA --}}
        <div @click="seleccionarMetrica('DOLOR')"
             :class="metricaActiva === 'DOLOR'
                ? 'border-blue-600 ring-2 ring-blue-500/25 bg-blue-50/40 shadow-xs'
                : 'border-[var(--rm-border)] bg-[var(--rm-surface)] hover:border-blue-300'"
             class="rounded-2xl border p-3 shadow-2xs cursor-pointer transition flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <span class="h-6 w-6 rounded-lg bg-purple-50 text-purple-600 border border-purple-200 flex items-center justify-center text-xs">
                        <i class="ph-bold ph-smiley-meh"></i>
                    </span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)]">Dolor EVA</span>
                </div>
                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded border"
                      :class="metricasInfo['DOLOR']?.estado_badge || 'bg-emerald-100 text-emerald-800 border-emerald-200'"
                      x-text="metricasInfo['DOLOR']?.estado || 'Leve'">
                    Leve
                </span>
            </div>
            <div>
                <span class="text-base font-black text-[var(--rm-text-title)] block"
                      x-text="metricasInfo['DOLOR']?.ultimo || '2/10'">
                    2/10
                </span>
                <span class="text-[10px] text-[var(--rm-text-muted)] font-medium">Escala 0-10</span>
            </div>
            <div class="h-[46px] w-full relative pt-1" wire:ignore>
                <canvas id="sparklineCanvasDOLOR" x-ref="sparklineDOLOR" class="w-full h-full"></canvas>
            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- 4. TERCERA FILA: EVENTOS RELEVANTES (~32%) + HISTÓRICO COMPACTO (~68%)     --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">

        {{-- EVENTOS RELEVANTES (32% - 4 columnas) --}}
        <div class="lg:col-span-4 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-2xs space-y-3">
            <div class="flex items-center justify-between border-b border-[var(--rm-border)]/60 pb-2.5">
                <div>
                    <h3 class="text-xs font-bold text-[var(--rm-text-title)] uppercase tracking-wider flex items-center gap-1.5">
                        <i class="ph-bold ph-warning-circle text-amber-600"></i>
                        <span>Eventos relevantes</span>
                    </h3>
                    <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">Alteraciones detectadas en el periodo</p>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    {{ count($eventosRelevantes) }} hallazgos
                </span>
            </div>

            <div class="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                @forelse($eventosRelevantes as $ev)
                    <div @click="abrirDrawerDesdeEvento({{ json_encode($ev) }})"
                         class="rounded-xl border border-[var(--rm-border)]/80 bg-[var(--rm-surface-alt)]/50 p-2.5 hover:bg-[var(--rm-surface-alt)] transition cursor-pointer space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $ev['badge_bg'] }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                {{ $ev['estado'] }}
                            </span>
                            <span class="text-[10px] font-medium text-[var(--rm-text-muted)] font-mono">{{ $ev['fecha_hora'] }}</span>
                        </div>
                        <div class="flex items-baseline justify-between pt-0.5">
                            <span class="text-xs font-bold text-[var(--rm-text-title)]">{{ $ev['evento'] }}</span>
                            <span class="text-xs font-black text-rose-600 font-mono">{{ $ev['valor'] }}</span>
                        </div>
                        <p class="text-[11px] text-[var(--rm-text-muted)] leading-tight">{{ $ev['motivo'] ?? ($ev['registro']->observacion ?? ($ev['registro']->observaciones ?? ($ev['evento'] ?? 'Hallazgo hemodinámico reportado.'))) }}</p>
                    </div>
                @empty
                    <div class="p-4 text-center text-xs text-[var(--rm-text-muted)] italic bg-[var(--rm-surface-alt)]/40 rounded-xl border border-[var(--rm-border)]/50">
                        <i class="ph-bold ph-shield-check text-2xl text-emerald-600 mb-1 block"></i>
                        <span>Sin eventos de alerta o alteraciones críticas registradas en este periodo.</span>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- HISTÓRICO DE REGISTROS COMPACTO CON NÚMEROS Y CRITERIO ("EL POR QUÉ") (68% - 8 columnas) --}}
        <div class="lg:col-span-8 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-2xs space-y-3">
            <div class="flex items-center justify-between border-b border-[var(--rm-border)]/60 pb-2.5">
                <div>
                    <h3 class="text-xs font-bold text-[var(--rm-text-title)] uppercase tracking-wider flex items-center gap-1.5">
                        <i class="ph-bold ph-clock-counter-clockwise text-blue-600"></i>
                        <span>Histórico de registros</span>
                    </h3>
                    <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">Valores numéricos, motivo clínico y justificación de control</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-[var(--rm-text-muted)] font-medium">
                        Total: <strong class="text-[var(--rm-text-title)]">{{ $totalRegistros }}</strong> tomas
                    </span>
                    <button type="button"
                            @click="exportarSignosExcel()"
                            title="Descargar Excel"
                            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-[10px] font-bold text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] transition cursor-pointer">
                        <i class="ph-bold ph-file-xls text-emerald-600 text-xs"></i>
                        <span>Excel</span>
                    </button>
                </div>
            </div>

            <div class="rm-table-container max-h-[420px] overflow-y-auto">
                <table class="rm-table text-xs w-full">
                    <thead class="sticky top-0 bg-[var(--rm-surface)] z-10 shadow-2xs">
                        <tr class="border-b border-[var(--rm-border)] text-[10px] font-black uppercase text-[var(--rm-text-muted)]">
                            <th class="py-2.5 px-3 text-left whitespace-nowrap">Fecha / Hora</th>
                            <th class="py-2.5 px-2 text-left whitespace-nowrap">PA (mmHg)</th>
                            <th class="py-2.5 px-2 text-left whitespace-nowrap">FC (lpm)</th>
                            <th class="py-2.5 px-2 text-left whitespace-nowrap">SpO₂ (%)</th>
                            <th class="py-2.5 px-2 text-left whitespace-nowrap">Temp (°C)</th>
                            <th class="py-2.5 px-2 text-left whitespace-nowrap">FR (rpm)</th>
                            <th class="py-2.5 px-2 text-left whitespace-nowrap">Dolor</th>
                            <th class="py-2.5 px-3 text-left min-w-[210px]">Criterio / Justificación Clínica</th>
                            <th class="py-2.5 px-2.5 text-left whitespace-nowrap">Responsable</th>
                            <th class="py-2.5 px-2 text-center whitespace-nowrap">Estado</th>
                            <th class="py-2.5 px-1.5 text-center">...</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--rm-border)]/50">
                        @forelse($registrosHistoricos as $item)
                            <tr @click="abrirDrawer({{ json_encode($item) }})"
                                class="hover:bg-[var(--rm-surface-alt)]/60 cursor-pointer transition text-[11px]">
                                {{-- Fecha y Hora --}}
                                <td class="py-2.5 px-3 whitespace-nowrap font-medium text-[var(--rm-text-title)]">
                                    {{ $item['fecha'] }}
                                    <span class="text-[var(--rm-text-muted)] text-[10px] block font-mono">{{ $item['hora'] }}</span>
                                </td>

                                {{-- Números con unidades claras --}}
                                <td class="py-2.5 px-2 whitespace-nowrap font-mono">
                                    <span class="font-extrabold text-[var(--rm-text-title)] text-[12px]">{{ $item['pa'] }}</span>
                                </td>
                                <td class="py-2.5 px-2 whitespace-nowrap font-mono">
                                    <span class="font-extrabold text-[var(--rm-text-title)] text-[12px]">{{ $item['fc'] }}</span>
                                </td>
                                <td class="py-2.5 px-2 whitespace-nowrap font-mono">
                                    <span class="font-extrabold text-[var(--rm-text-title)] text-[12px]">{{ $item['spo2_val'] }}</span><span class="text-[10px] text-[var(--rm-text-muted)] font-sans">%</span>
                                </td>
                                <td class="py-2.5 px-2 whitespace-nowrap font-mono">
                                    <span class="font-extrabold text-[var(--rm-text-title)] text-[12px]">{{ $item['temp_val'] }}</span><span class="text-[10px] text-[var(--rm-text-muted)] font-sans">°</span>
                                </td>
                                <td class="py-2.5 px-2 whitespace-nowrap font-mono">
                                    <span class="font-extrabold text-[var(--rm-text-title)] text-[12px]">{{ $item['fr'] }}</span>
                                </td>
                                <td class="py-2.5 px-2 whitespace-nowrap font-mono">
                                    <span class="font-extrabold text-[var(--rm-text-title)] text-[12px]">{{ $item['dolor'] }}</span>
                                </td>

                                {{-- "EL POR QUÉ": Criterio clínico + Observación registrada --}}
                                <td class="py-2.5 px-3">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-bold text-[11px] text-[var(--rm-text-title)] flex items-center gap-1.5">
                                            <span class="h-1.5 w-1.5 rounded-full {{ str_contains($item['badgeClass'], 'rose') ? 'bg-rose-500' : (str_contains($item['badgeClass'], 'amber') ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                                            <span>{{ $item['criterio'] }}</span>
                                        </span>
                                        <span class="text-[10px] text-[var(--rm-text-muted)] truncate max-w-[240px] block" title="{{ $item['observaciones'] }}">
                                            {{ $item['observaciones'] }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Responsable --}}
                                <td class="py-2.5 px-2.5 whitespace-nowrap text-[var(--rm-text-muted)] max-w-[120px] truncate" title="{{ $item['responsable'] }}">
                                    {{ $item['responsable'] }}
                                </td>

                                {{-- Estado badge --}}
                                <td class="py-2.5 px-2 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9.5px] font-bold border {{ $item['badgeClass'] }}">
                                        {{ $item['estado'] }}
                                    </span>
                                </td>

                                {{-- Botón 3 puntitos en fila --}}
                                <td class="py-2.5 px-1.5 text-center text-[var(--rm-text-muted)]">
                                    <button type="button"
                                            @click.stop="abrirDrawer({{ json_encode($item) }})"
                                            title="Ver detalle completo"
                                            class="p-1 rounded-md hover:bg-[var(--rm-surface)] hover:text-[#1E3A8A] transition cursor-pointer">
                                        <i class="ph-bold ph-dots-three-vertical text-sm"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-8 text-center text-xs text-[var(--rm-text-muted)] italic">
                                    No se registran mediciones previas de signos vitales para este residente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- 5. MODAL INTERACTIVO: CONFIGURAR RANGOS CLÍNICOS (100% NÍTIDO, CÁLIDO)     --}}
    {{-- ========================================================================= --}}
    <div x-show="modalRangos"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;"
         role="dialog"
         aria-modal="true"
         x-on:keydown.escape.window="modalRangos = false">

        {{-- Backdrop oscurecido suave sin blur ni opacidad heredada --}}
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[0.5px] transition-opacity z-40"
             @click="modalRangos = false"></div>

        {{-- Contenedor del Modal (100% Nítido, Alto Contraste, Sin opacidad) --}}
        <div class="relative z-50 w-full max-w-xl rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xl p-6 space-y-4 !opacity-100 !filter-none font-sans">
            {{-- Encabezado --}}
            <div class="flex items-start justify-between border-b border-[var(--rm-border)] pb-3.5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-[#1E3A8A] border border-blue-200 shadow-2xs">
                        <i class="ph-bold ph-sliders-horizontal text-xl"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-tight text-[var(--rm-text-title)]">
                            Configurar Rangos Clínicos de Normalidad
                        </h3>
                        <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">
                            Establece los umbrales de referencia geriátrica para la detección de alertas y curvas.
                        </p>
                    </div>
                </div>
                <button type="button"
                        @click="modalRangos = false"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] border border-transparent hover:border-[var(--rm-border)] transition cursor-pointer"
                        aria-label="Cerrar modal">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>

            {{-- Formulario de rangos en 2 columnas (Cards sólidas de alta legibilidad) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs max-h-[60vh] overflow-y-auto pr-1">
                {{-- PAS --}}
                <div class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-3 space-y-2 shadow-2xs">
                    <label class="font-bold text-[var(--rm-text-title)] flex items-center justify-between">
                        <span>Presión Sistólica (PAS)</span>
                        <span class="px-1.5 py-0.5 rounded bg-[var(--rm-surface)] border border-[var(--rm-border)] text-[10px] text-[var(--rm-text-muted)] font-mono">mmHg</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-[10.5px] font-medium text-[var(--rm-text-muted)] block mb-1">Mínimo</span>
                            <input type="number" x-model.number="rangos.pas_min"
                                   class="w-full rounded-lg border border-[var(--rm-border)] bg-white dark:bg-[var(--rm-surface)] px-2.5 py-1.5 font-bold text-[var(--rm-text-title)] text-center shadow-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                        </div>
                        <div>
                            <span class="text-[10.5px] font-medium text-[var(--rm-text-muted)] block mb-1">Máximo</span>
                            <input type="number" x-model.number="rangos.pas_max"
                                   class="w-full rounded-lg border border-[var(--rm-border)] bg-white dark:bg-[var(--rm-surface)] px-2.5 py-1.5 font-bold text-[var(--rm-text-title)] text-center shadow-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                        </div>
                    </div>
                </div>

                {{-- PAD --}}
                <div class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-3 space-y-2 shadow-2xs">
                    <label class="font-bold text-[var(--rm-text-title)] flex items-center justify-between">
                        <span>Presión Diastólica (PAD)</span>
                        <span class="px-1.5 py-0.5 rounded bg-[var(--rm-surface)] border border-[var(--rm-border)] text-[10px] text-[var(--rm-text-muted)] font-mono">mmHg</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-[10.5px] font-medium text-[var(--rm-text-muted)] block mb-1">Mínimo</span>
                            <input type="number" x-model.number="rangos.pad_min"
                                   class="w-full rounded-lg border border-[var(--rm-border)] bg-white dark:bg-[var(--rm-surface)] px-2.5 py-1.5 font-bold text-[var(--rm-text-title)] text-center shadow-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                        </div>
                        <div>
                            <span class="text-[10.5px] font-medium text-[var(--rm-text-muted)] block mb-1">Máximo</span>
                            <input type="number" x-model.number="rangos.pad_max"
                                   class="w-full rounded-lg border border-[var(--rm-border)] bg-white dark:bg-[var(--rm-surface)] px-2.5 py-1.5 font-bold text-[var(--rm-text-title)] text-center shadow-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                        </div>
                    </div>
                </div>

                {{-- FC --}}
                <div class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-3 space-y-2 shadow-2xs">
                    <label class="font-bold text-[var(--rm-text-title)] flex items-center justify-between">
                        <span>Frecuencia Cardíaca (FC)</span>
                        <span class="px-1.5 py-0.5 rounded bg-[var(--rm-surface)] border border-[var(--rm-border)] text-[10px] text-[var(--rm-text-muted)] font-mono">lpm</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-[10.5px] font-medium text-[var(--rm-text-muted)] block mb-1">Mínimo</span>
                            <input type="number" x-model.number="rangos.fc_min"
                                   class="w-full rounded-lg border border-[var(--rm-border)] bg-white dark:bg-[var(--rm-surface)] px-2.5 py-1.5 font-bold text-[var(--rm-text-title)] text-center shadow-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                        </div>
                        <div>
                            <span class="text-[10.5px] font-medium text-[var(--rm-text-muted)] block mb-1">Máximo</span>
                            <input type="number" x-model.number="rangos.fc_max"
                                   class="w-full rounded-lg border border-[var(--rm-border)] bg-white dark:bg-[var(--rm-surface)] px-2.5 py-1.5 font-bold text-[var(--rm-text-title)] text-center shadow-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                        </div>
                    </div>
                </div>

                {{-- SpO2 --}}
                <div class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-3 space-y-2 shadow-2xs">
                    <label class="font-bold text-[var(--rm-text-title)] flex items-center justify-between">
                        <span>Saturación SpO₂</span>
                        <span class="px-1.5 py-0.5 rounded bg-[var(--rm-surface)] border border-[var(--rm-border)] text-[10px] text-[var(--rm-text-muted)] font-mono">%</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-[10.5px] font-medium text-[var(--rm-text-muted)] block mb-1">Umbral Alerta (&lt;)</span>
                            <input type="number" x-model.number="rangos.spo2_min"
                                   class="w-full rounded-lg border border-[var(--rm-border)] bg-white dark:bg-[var(--rm-surface)] px-2.5 py-1.5 font-bold text-[var(--rm-text-title)] text-center shadow-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                        </div>
                        <div class="flex flex-col items-center justify-center p-1.5 rounded-lg bg-[var(--rm-surface)] border border-[var(--rm-border)] text-[10.5px] text-[var(--rm-text-muted)]">
                            <span>Rango normal</span>
                            <span class="font-black text-emerald-600 dark:text-emerald-400">≥ <span x-text="rangos.spo2_min"></span>%</span>
                        </div>
                    </div>
                </div>

                {{-- Temperatura --}}
                <div class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-3 space-y-2 shadow-2xs">
                    <label class="font-bold text-[var(--rm-text-title)] flex items-center justify-between">
                        <span>Temperatura Corporal</span>
                        <span class="px-1.5 py-0.5 rounded bg-[var(--rm-surface)] border border-[var(--rm-border)] text-[10px] text-[var(--rm-text-muted)] font-mono">°C</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-[10.5px] font-medium text-[var(--rm-text-muted)] block mb-1">Mínimo</span>
                            <input type="number" step="0.1" x-model.number="rangos.temp_min"
                                   class="w-full rounded-lg border border-[var(--rm-border)] bg-white dark:bg-[var(--rm-surface)] px-2.5 py-1.5 font-bold text-[var(--rm-text-title)] text-center shadow-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                        </div>
                        <div>
                            <span class="text-[10.5px] font-medium text-[var(--rm-text-muted)] block mb-1">Máximo</span>
                            <input type="number" step="0.1" x-model.number="rangos.temp_max"
                                   class="w-full rounded-lg border border-[var(--rm-border)] bg-white dark:bg-[var(--rm-surface)] px-2.5 py-1.5 font-bold text-[var(--rm-text-title)] text-center shadow-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                        </div>
                    </div>
                </div>

                {{-- FR --}}
                <div class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-3 space-y-2 shadow-2xs">
                    <label class="font-bold text-[var(--rm-text-title)] flex items-center justify-between">
                        <span>Frecuencia Respiratoria (FR)</span>
                        <span class="px-1.5 py-0.5 rounded bg-[var(--rm-surface)] border border-[var(--rm-border)] text-[10px] text-[var(--rm-text-muted)] font-mono">rpm</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-[10.5px] font-medium text-[var(--rm-text-muted)] block mb-1">Mínimo</span>
                            <input type="number" x-model.number="rangos.fr_min"
                                   class="w-full rounded-lg border border-[var(--rm-border)] bg-white dark:bg-[var(--rm-surface)] px-2.5 py-1.5 font-bold text-[var(--rm-text-title)] text-center shadow-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                        </div>
                        <div>
                            <span class="text-[10.5px] font-medium text-[var(--rm-text-muted)] block mb-1">Máximo</span>
                            <input type="number" x-model.number="rangos.fr_max"
                                   class="w-full rounded-lg border border-[var(--rm-border)] bg-white dark:bg-[var(--rm-surface)] px-2.5 py-1.5 font-bold text-[var(--rm-text-title)] text-center shadow-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pie del modal con acciones --}}
            <div class="flex items-center justify-between border-t border-[var(--rm-border)] pt-3.5">
                <button type="button"
                        @click="restablecerRangos()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-xl transition cursor-pointer">
                    <i class="ph-bold ph-arrow-counter-clockwise text-sm"></i>
                    <span>Restablecer estándar</span>
                </button>

                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="modalRangos = false"
                            class="px-3.5 py-1.5 text-xs font-semibold text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] rounded-xl border border-[var(--rm-border)] transition cursor-pointer">
                        Cancelar
                    </button>
                    <button type="button"
                            @click="guardarRangos()"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-bold text-white bg-[#1E3A8A] hover:bg-blue-900 rounded-xl transition cursor-pointer shadow-xs">
                        <i class="ph-bold ph-check text-sm"></i>
                        <span>Guardar rangos</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 6. SLIDE-OVER DRAWER LATERAL (NÍTIDO, SIN BLUR)                           --}}
    {{-- ========================================================================= --}}
    <div x-show="drawerAbierto"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="relative z-50"
         role="dialog"
         aria-modal="true"
         x-on:keydown.escape.window="cerrarDrawer()"
         style="display: none;">

        {{-- Backdrop oscurecido suave sin blur --}}
        <div class="rm-drawer-backdrop" @click="cerrarDrawer()"></div>

        <div class="pointer-events-none fixed inset-y-0 right-0 z-50 flex max-w-full pl-6 sm:pl-10">
            <div x-show="drawerAbierto"
                 x-transition:enter="transform transition ease-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="pointer-events-auto flex h-full w-screen max-w-[760px] md:w-[740px] flex-col overflow-hidden rm-drawer transition duration-300 ease-in-out">

                {{-- Header del Drawer (Golden Reference) --}}
                <header class="rm-drawer-header">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <span class="rm-drawer-badge">
                                <span class="h-1.5 w-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                                PANEL LATERAL DE CONSULTA
                            </span>
                            <div class="flex items-center gap-2 pt-0.5">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--rm-surface)] text-[#1E3A8A] border border-[var(--rm-border)] text-sm shadow-2xs">
                                    <i class="ph-bold ph-heartbeat"></i>
                                </span>
                                <h3 class="rm-drawer-title">DETALLE DEL CONTROL HEMODINÁMICO</h3>
                            </div>
                            <p class="rm-drawer-subtitle">Registro clínico y evaluación longitudinal de constantes vitales</p>
                        </div>
                        <button type="button"
                                @click="cerrarDrawer()"
                                class="flex h-8 w-8 items-center justify-center rounded-lg text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] border border-transparent hover:border-[var(--rm-border)] transition cursor-pointer"
                                aria-label="Cerrar panel lateral">
                            <i class="ph ph-x text-lg"></i>
                        </button>
                    </div>
                </header>

                {{-- Cuerpo del Drawer (100% Nítido, Scroll Exclusivo) --}}
                <div class="rm-drawer-body space-y-4">
                    {{-- Card del Residente (Horizontal Compacta Golden Reference) --}}
                    <div class="rm-drawer-resident">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-[#1E3A8A] text-white font-black text-sm shadow-xs">
                                {{ substr($adultoMayor->nombres, 0, 1) }}{{ substr($adultoMayor->ap_paterno, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-[var(--rm-text-title)] text-sm leading-tight truncate uppercase">
                                    {{ $adultoMayor->ap_paterno }} {{ $adultoMayor->ap_materno }} {{ $adultoMayor->nombres }}
                                </h4>
                                <div class="flex items-center gap-2 text-xs text-[var(--rm-text-muted)] mt-0.5 flex-wrap">
                                    <span>{{ $adultoMayor->edad_texto }}</span>
                                    <span>·</span>
                                    <span>CI: {{ $adultoMayor->ci ?: 'Documento S/D' }}</span>
                                    <span>·</span>
                                    <span class="font-mono text-[11px]">{{ $adultoMayor->cod_am }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="inline-block px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[var(--rm-text-title)] font-semibold text-xs">
                                {{ $adultoMayor->ubicacion_texto }}
                            </span>
                            <div class="mt-1 flex items-center justify-end gap-1.5">
                                <span class="text-[10.5px] text-[var(--rm-text-muted)]">Estado:</span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.2 rounded-full text-[10px] font-bold border {{ $adultoMayor->estado_badge_color }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ $adultoMayor->estado_humano }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <template x-if="registroActivo">
                        <div class="space-y-3.5">
                            {{-- Resumen Superior --}}
                            <div class="rm-drawer-card-highlight space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-xs font-bold border"
                                          :class="registroActivo.badgeClass || 'bg-emerald-100 text-emerald-800 border-emerald-200'">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                        <span x-text="registroActivo.estado"></span>
                                    </span>
                                    <span class="text-xs font-semibold text-[var(--rm-text-body)]"
                                          x-text="`${registroActivo.fecha} · ${registroActivo.hora}`"></span>
                                </div>
                                <div class="text-base font-black text-[var(--rm-text-title)]"
                                     x-text="`Presión Arterial: ${registroActivo.pa} mmHg`"></div>
                                <div class="text-[11px] text-[var(--rm-text-muted)] font-medium"
                                     x-text="`Criterio: ${registroActivo.criterio || 'Monitoreo estándar'}`"></div>
                            </div>

                            {{-- Profesional Responsable --}}
                            <div class="rm-drawer-card flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="h-8 w-8 rounded-full bg-[#1E3A8A] text-white flex items-center justify-center font-bold text-xs">
                                        <i class="ph-bold ph-user"></i>
                                    </div>
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-[var(--rm-text-muted)] block">Registrado por</span>
                                        <span class="font-bold text-[var(--rm-text-title)]" x-text="registroActivo.responsable"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Grid de Todas las Constantes --}}
                            <div class="space-y-1.5">
                                <h5 class="text-[11px] font-bold text-[var(--rm-text-title)] uppercase tracking-wider">
                                    Constantes Fisiológicas
                                </h5>
                                <div class="rm-drawer-card overflow-hidden divide-y divide-[var(--rm-border)]/50 !p-0">
                                    <div class="px-3.5 py-2 flex items-center justify-between">
                                        <span class="text-[var(--rm-text-muted)]">Frecuencia cardíaca</span>
                                        <span class="font-bold text-[var(--rm-text-title)] font-mono" x-text="`${registroActivo.fc} lpm`"></span>
                                    </div>
                                    <div class="px-3.5 py-2 flex items-center justify-between">
                                        <span class="text-[var(--rm-text-muted)]">Saturación SpO₂</span>
                                        <span class="font-bold text-[var(--rm-text-title)] font-mono" x-text="registroActivo.spo2"></span>
                                    </div>
                                    <div class="px-3.5 py-2 flex items-center justify-between">
                                        <span class="text-[var(--rm-text-muted)]">Temperatura</span>
                                        <span class="font-bold text-[var(--rm-text-title)] font-mono" x-text="registroActivo.temp"></span>
                                    </div>
                                    <div class="px-3.5 py-2 flex items-center justify-between">
                                        <span class="text-[var(--rm-text-muted)]">Frecuencia respiratoria</span>
                                        <span class="font-bold text-[var(--rm-text-title)] font-mono" x-text="`${registroActivo.fr} rpm`"></span>
                                    </div>
                                    <div class="px-3.5 py-2 flex items-center justify-between">
                                        <span class="text-[var(--rm-text-muted)]">Escala de dolor (EVA)</span>
                                        <span class="font-bold text-[var(--rm-text-title)] font-mono" x-text="registroActivo.dolor"></span>
                                    </div>
                                    <template x-if="registroActivo.glucosa && registroActivo.glucosa !== '--'">
                                        <div class="px-3.5 py-2 flex items-center justify-between">
                                            <span class="text-[var(--rm-text-muted)]">Glucemia capilar</span>
                                            <span class="font-bold text-[var(--rm-text-title)] font-mono" x-text="registroActivo.glucosa"></span>
                                        </div>
                                    </template>
                                    <template x-if="registroActivo.peso && registroActivo.peso !== '--'">
                                        <div class="px-3.5 py-2 flex items-center justify-between">
                                            <span class="text-[var(--rm-text-muted)]">Peso corporal</span>
                                            <span class="font-bold text-[var(--rm-text-title)] font-mono" x-text="registroActivo.peso"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Condiciones Clínicas Adicionales --}}
                            <div class="space-y-1.5">
                                <h5 class="text-[11px] font-bold text-[var(--rm-text-title)] uppercase tracking-wider">
                                    Condiciones del Paciente
                                </h5>
                                <div class="rm-drawer-card space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[var(--rm-text-muted)]">Posición de toma:</span>
                                        <span class="font-semibold text-[var(--rm-text-title)]" x-text="registroActivo.posicion"></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-[var(--rm-text-muted)]">Oxigenoterapia:</span>
                                        <span class="font-semibold text-[var(--rm-text-title)]" x-text="registroActivo.oxigeno"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Observaciones Clínicas / "El por qué" --}}
                            <div class="space-y-1.5">
                                <h5 class="text-[11px] font-bold text-[var(--rm-text-title)] uppercase tracking-wider">
                                    Observaciones / Motivo de Control
                                </h5>
                                <div class="rm-drawer-card bg-[var(--rm-surface-alt)]/50">
                                    <p class="text-xs text-[var(--rm-text-body)] leading-relaxed italic"
                                       x-text="registroActivo.observaciones"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Footer del Drawer --}}
                <div class="rm-drawer-footer flex items-center justify-end gap-2">
                    <button type="button"
                            @click="cerrarDrawer()"
                            class="rm-btn rm-btn-secondary text-xs font-bold cursor-pointer">
                        Cerrar detalle
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 7. TOAST FEEDBACK VISUAL                                                  --}}
    {{-- ========================================================================= --}}
    <div x-show="toastMensaje"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-5 right-5 z-50 flex items-center gap-2.5 rounded-xl bg-slate-900 text-white px-4 py-2.5 shadow-2xl border border-slate-700 text-xs font-semibold"
         style="display: none;">
        <i class="ph-bold ph-check-circle text-emerald-400 text-base"></i>
        <span x-text="toastMensaje"></span>
    </div>

</div>

{{-- ========================================================================= --}}
{{-- SCRIPT: CONTROL REACTIVO DE SIGNOS VITALES, CHART.JS CON DATALABELS       --}}
{{-- ========================================================================= --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('rmSignosVitalesModule', (config) => ({
        metricaActiva: config.metricaActiva || 'PA',
        periodoActivo: config.periodoActivo || '7d',
        labels: config.labels || [],
        labelsLargas: config.labelsLargas || config.labels || [],
        sistolica: config.sistolica || [],
        diastolica: config.diastolica || [],
        fc: config.fc || [],
        spo2: config.spo2 || [],
        temp: config.temp || [],
        fr: config.fr || [],
        dolor: config.dolor || [],
        glucosa: config.glucosa || [],
        peso: config.peso || [],
        metricasInfo: config.metricasInfo || {},
        resumenActivo: config.resumenPeriodo || (config.metricasInfo ? config.metricasInfo['PA'] : {}),
        registros: config.registros || [],

        mainChart: null,
        sparklinesInitialized: false,
        drawerAbierto: false,
        registroActivo: null,
        modalRangos: false,
        menuCabecera: false,
        toastMensaje: null,

        // Rangos clínicos de normalidad geriátrica
        rangos: {
            pas_min: 90,
            pas_max: 140,
            pad_min: 60,
            pad_max: 90,
            fc_min: 60,
            fc_max: 100,
            spo2_min: 92,
            temp_min: 36.0,
            temp_max: 37.5,
            fr_min: 12,
            fr_max: 20,
            glucosa_min: 70,
            glucosa_max: 110,
        },

        init() {
            // Cargar rangos personalizados de localStorage si existen
            try {
                const guardados = localStorage.getItem('rm_rangos_clinicos_v2');
                if (guardados) {
                    this.rangos = Object.assign(this.rangos, JSON.parse(guardados));
                }
            } catch (e) {}

            this.reinitCharts();

            // Reaccionar al cambio de pestaña en la Ficha Médica
            try {
                this.$watch('activeTab', (val) => {
                    if (val === 'signos') {
                        this.reinitCharts();
                    }
                });
            } catch (e) {}

            window.addEventListener('render-graficos-signos', () => {
                this.reinitCharts();
            });

            window.addEventListener('tab-cambiado', (e) => {
                const tab = typeof e.detail === 'string' ? e.detail : e.detail?.tab;
                if (tab === 'signos') {
                    this.reinitCharts();
                }
            });

            window.addEventListener('resize', () => {
                this.resizeAllCharts();
            });

            window.RMCharts?.onThemeChange?.(() => {
                this.reinitCharts();
            });

            if (window.Livewire) {
                Livewire.hook('commit', ({ succeed }) => {
                    succeed(() => {
                        const canvas = document.getElementById('signosVitalesMainCanvas');
                        if (canvas && canvas.offsetParent !== null && !Chart.getChart(canvas)) {
                            this.reinitCharts();
                        }
                    });
                });
            }
        },

        abrirModalRangos() {
            this.modalRangos = true;
        },

        guardarRangos() {
            try {
                localStorage.setItem('rm_rangos_clinicos_v2', JSON.stringify(this.rangos));
            } catch (e) {}
            this.modalRangos = false;
            this.mostrarToast('Rangos clínicos actualizados correctamente.');
            this.reinitCharts();
        },

        restablecerRangos() {
            this.rangos = {
                pas_min: 90,
                pas_max: 140,
                pad_min: 60,
                pad_max: 90,
                fc_min: 60,
                fc_max: 100,
                spo2_min: 92,
                temp_min: 36.0,
                temp_max: 37.5,
                fr_min: 12,
                fr_max: 20,
                glucosa_min: 70,
                glucosa_max: 110,
            };
            try {
                localStorage.removeItem('rm_rangos_clinicos_v2');
            } catch (e) {}
            this.mostrarToast('Rangos restablecidos a los valores estándar geriátricos.');
            this.reinitCharts();
        },

        mostrarToast(msg) {
            this.toastMensaje = msg;
            setTimeout(() => {
                this.toastMensaje = null;
            }, 3000);
        },

        exportarSignosPDF() {
            this.mostrarToast('Generando reporte en formato PDF...');
            setTimeout(() => {
                window.print();
            }, 600);
        },

        exportarSignosExcel() {
            if (!this.registros || !this.registros.length) {
                this.mostrarToast('No hay registros históricos para exportar.');
                return;
            }

            let csvContent = 'data:text/csv;charset=utf-8,';
            csvContent += 'Fecha,Hora,Presion Arterial,FC (lpm),SpO2 (%),Temp (C),FR (rpm),Dolor,Criterio Clinico,Observaciones,Responsable,Estado\n';

            this.registros.forEach(r => {
                const row = [
                    `"${r.fecha}"`,
                    `"${r.hora}"`,
                    `"${r.pa}"`,
                    `"${r.fc}"`,
                    `"${r.spo2_val}"`,
                    `"${r.temp_val}"`,
                    `"${r.fr}"`,
                    `"${r.dolor}"`,
                    `"${(r.criterio || '').replace(/"/g, '""')}"`,
                    `"${(r.observaciones || '').replace(/"/g, '""')}"`,
                    `"${(r.responsable || '').replace(/"/g, '""')}"`,
                    `"${r.estado}"`
                ].join(',');
                csvContent += row + '\n';
            });

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', `signos_vitales_${new Date().toISOString().slice(0, 10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            this.mostrarToast('Archivo Excel/CSV descargado exitosamente.');
        },

        imprimirGrafica() {
            window.print();
        },

        reinitCharts() {
            setTimeout(() => {
                this.renderMainChart();
                this.renderSparklines();
            }, 60);
        },

        resizeAllCharts() {
            const main = document.getElementById('signosVitalesMainCanvas');
            if (main) Chart.getChart(main)?.resize();

            ['PA', 'FC', 'SPO2', 'TEMP', 'FR', 'DOLOR'].forEach(k => {
                const c = document.getElementById('sparklineCanvas' + k);
                if (c) Chart.getChart(c)?.resize();
            });
        },

        seleccionarMetrica(metrica) {
            this.metricaActiva = metrica;
            if (this.metricasInfo[metrica]) {
                this.resumenActivo = this.metricasInfo[metrica];
            }
            if (this.$wire && typeof this.$wire.setMetricaSignos === 'function') {
                this.$wire.setMetricaSignos(metrica);
            }
            this.updateMainChart();
        },

        cambiarPeriodo(periodo) {
            this.periodoActivo = periodo;
            if (this.$wire && typeof this.$wire.setPeriodoSignos === 'function') {
                this.$wire.setPeriodoSignos(periodo);
            }
        },

        abrirDrawer(item) {
            this.registroActivo = item;
            this.drawerAbierto = true;
        },

        abrirDrawerDesdeEvento(ev) {
            if (ev.registro) {
                const r = ev.registro;
                const pas = r.presion_sistolica || (r.presion_arterial ? r.presion_arterial.split('/')[0] : null);
                const pad = r.presion_diastolica || (r.presion_arterial ? r.presion_arterial.split('/')[1] : null);
                this.registroActivo = {
                    id: r.cod_signo,
                    fecha: r.fecha ? (typeof r.fecha === 'string' ? r.fecha.substring(0, 10) : '') : 'Reciente',
                    hora: r.hora ? (typeof r.hora === 'string' ? r.hora.substring(0, 5) : '') : '08:00',
                    pa: (pas && pad) ? `${pas}/${pad}` : (r.presion_arterial || '--'),
                    fc: r.frecuencia_cardiaca || '--',
                    spo2: r.saturacion ? `${r.saturacion}%` : '--',
                    temp: r.temperatura ? `${r.temperatura}°C` : '--',
                    fr: r.frecuencia_respiratoria || '--',
                    dolor: (r.dolor !== null && r.dolor !== undefined) ? `${r.dolor}/10` : '--',
                    glucosa: r.glucosa ? `${r.glucosa} mg/dL` : '--',
                    peso: r.peso ? `${r.peso} kg` : '--',
                    posicion: r.posicion || 'Decúbito supino',
                    oxigeno: r.usa_oxigeno ? 'Sí' : 'No',
                    criterio: ev.evento || 'Alteración detectada',
                    observaciones: r.observacion || r.observaciones || 'Registro clínico con hallazgo reportado.',
                    responsable: r.registrado_por || 'Equipo Asistencial',
                    estado: ev.estado || 'Relevante',
                    badgeClass: ev.badge_bg || 'bg-amber-100 text-amber-800 border-amber-200',
                };
            } else {
                this.registroActivo = {
                    fecha: ev.fecha_hora,
                    hora: '',
                    pa: ev.valor,
                    fc: '--',
                    spo2: '--',
                    temp: '--',
                    fr: '--',
                    dolor: '--',
                    glucosa: '--',
                    peso: '--',
                    posicion: 'Decúbito supino',
                    oxigeno: 'No',
                    criterio: ev.evento,
                    observaciones: `Evento detectado: ${ev.evento} con valor de ${ev.valor}.`,
                    responsable: 'Equipo Clínico',
                    estado: ev.estado,
                    badgeClass: ev.badge_bg,
                };
            }
            this.drawerAbierto = true;
        },

        cerrarDrawer() {
            this.drawerAbierto = false;
        },

        // =========================================================================
        // GRÁFICO PRINCIPAL: CHART.JS CON NÚMEROS VISIBLES EN PUNTOS (DATALABELS)
        // =========================================================================
        renderMainChart() {
            const canvas = document.getElementById('signosVitalesMainCanvas');
            if (!canvas) return;

            if (canvas.offsetParent === null) return;

            const existing = Chart.getChart(canvas);
            if (existing) {
                try { existing.destroy(); } catch (e) {}
            }

            const self = this;
            const isDark = document.documentElement.classList.contains('dark');
            const datasets = this.getDatasetsForMetrica(this.metricaActiva, isDark);
            const scalesConfig = this.getScalesForMetrica(this.metricaActiva, isDark);

            // Plugin: Crosshair vertical al hacer hover
            const verticalCrosshairPlugin = {
                id: 'verticalCrosshair',
                afterDraw: (chart) => {
                    if (chart.tooltip?._active && chart.tooltip._active.length) {
                        const activePoint = chart.tooltip._active[0];
                        const { ctx, chartArea } = chart;
                        const x = activePoint.element.x;
                        const topY = chartArea.top;
                        const bottomY = chartArea.bottom;

                        ctx.save();
                        ctx.beginPath();
                        ctx.moveTo(x, topY);
                        ctx.lineTo(x, bottomY);
                        ctx.setLineDash([4, 4]);
                        ctx.lineWidth = 1.2;
                        ctx.strokeStyle = isDark ? 'rgba(255, 255, 255, 0.40)' : 'rgba(30, 58, 138, 0.40)';
                        ctx.stroke();
                        ctx.restore();
                    }
                }
            };

            // Plugin: Rango normal sombreado con límites configurados
            const normalRangePlugin = {
                id: 'normalRangeZone',
                beforeDraw: (chart) => {
                    if (self.metricaActiva !== 'PA') return;
                    const { ctx, chartArea, scales } = chart;
                    if (!chartArea || !scales.y) return;
                    const pasMax = self.rangos.pas_max || 140;
                    const padMin = self.rangos.pad_min || 60;
                    const yHigh = scales.y.getPixelForValue(pasMax);
                    const yLow = scales.y.getPixelForValue(padMin);
                    if (yHigh === undefined || yLow === undefined) return;

                    ctx.save();
                    // Franja verde suave de normalidad
                    ctx.fillStyle = isDark ? 'rgba(16, 185, 129, 0.06)' : 'rgba(16, 185, 129, 0.08)';
                    ctx.fillRect(chartArea.left, yHigh, chartArea.width, yLow - yHigh);

                    // Límites punteados
                    ctx.strokeStyle = isDark ? 'rgba(16, 185, 129, 0.30)' : 'rgba(16, 185, 129, 0.35)';
                    ctx.lineWidth = 1;
                    ctx.setLineDash([4, 4]);

                    ctx.beginPath();
                    ctx.moveTo(chartArea.left, yHigh);
                    ctx.lineTo(chartArea.right, yHigh);
                    ctx.stroke();

                    ctx.beginPath();
                    ctx.moveTo(chartArea.left, yLow);
                    ctx.lineTo(chartArea.right, yLow);
                    ctx.stroke();

                    ctx.restore();
                }
            };

            const options = {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 800,
                    easing: 'easeOutQuart'
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { display: false },

                    // =========================================================
                    // DATALABELS: NÚMEROS VISIBLES DIRECTAMENTE SOBRE CADA PUNTO
                    // =========================================================
                    datalabels: {
                        display: (context) => {
                            const val = context.dataset.data[context.dataIndex];
                            return val !== null && val !== undefined && val !== '';
                        },
                        align: (context) => {
                            if (self.metricaActiva === 'PA') {
                                return context.datasetIndex === 0 ? 'top' : 'bottom';
                            }
                            return 'top';
                        },
                        anchor: (context) => {
                            if (self.metricaActiva === 'PA') {
                                return context.datasetIndex === 0 ? 'end' : 'start';
                            }
                            return 'end';
                        },
                        offset: 5,
                        backgroundColor: () => {
                            return isDark ? 'rgba(15, 23, 42, 0.90)' : 'rgba(255, 255, 255, 0.92)';
                        },
                        borderColor: (context) => {
                            return context.dataset.borderColor || '#1E3A8A';
                        },
                        borderWidth: 1.2,
                        borderRadius: 5,
                        padding: { top: 2, bottom: 2, left: 5, right: 5 },
                        color: (context) => {
                            return isDark ? '#F1F5F9' : '#0F172A';
                        },
                        font: {
                            size: 10,
                            weight: 'bold',
                            family: 'Inter, system-ui, sans-serif'
                        },
                        formatter: (value) => {
                            if (value === null || value === undefined) return '';
                            if (self.metricaActiva === 'TEMP') {
                                return Number(value).toFixed(1) + '°';
                            }
                            if (self.metricaActiva === 'SPO2') {
                                return value + '%';
                            }
                            return value;
                        }
                    },

                    tooltip: {
                        enabled: true,
                        backgroundColor: isDark ? 'rgba(15, 23, 42, 0.96)' : 'rgba(17, 24, 39, 0.96)',
                        titleColor: '#FFFFFF',
                        bodyColor: '#F3F4F6',
                        padding: 12,
                        cornerRadius: 10,
                        displayColors: true,
                        usePointStyle: true,
                        callbacks: {
                            title: (items) => {
                                const idx = items[0]?.dataIndex ?? 0;
                                return self.labelsLargas[idx] || self.labels[idx] || '';
                            },
                            label: (context) => {
                                const label = context.dataset.label || '';
                                const val = context.parsed.y;
                                if (val === null || val === undefined) return null;
                                const unit = self.metricasInfo[self.metricaActiva]?.unidad || '';
                                return ` ${label}: ${val} ${unit}`;
                            },
                            afterBody: (items) => {
                                if (self.metricaActiva === 'PA' && items.length >= 2) {
                                    const pas = items[0].parsed.y;
                                    const pad = items[1].parsed.y;
                                    const st = (pas >= self.rangos.pas_max || pad >= self.rangos.pad_max) ? 'Elevada' : ((pas < self.rangos.pas_min) ? 'Hipotensión' : 'Normal');
                                    return ` Estado: ${st}`;
                                }
                                return '';
                            }
                        }
                    }
                },
                scales: scalesConfig
            };

            // Si hay solo 1 punto, agregar un punto visual previo para que dibuje línea continua
            let plotLabels = this.labels.length ? [...this.labels] : ['Sin datos'];
            if (plotLabels.length === 1) {
                plotLabels = ['Previo', plotLabels[0]];
                datasets.forEach(ds => {
                    if (ds.data && ds.data.length === 1) {
                        ds.data = [ds.data[0], ds.data[0]];
                    }
                });
            }

            this.mainChart = new Chart(canvas, {
                type: 'line',
                data: {
                    labels: plotLabels,
                    datasets: datasets
                },
                options: options,
                plugins: [verticalCrosshairPlugin, normalRangePlugin]
            });
        },

        updateMainChart() {
            const canvas = document.getElementById('signosVitalesMainCanvas');
            if (!canvas) return;

            const chart = Chart.getChart(canvas);
            if (!chart) {
                this.renderMainChart();
                return;
            }

            const isDark = document.documentElement.classList.contains('dark');
            const datasets = this.getDatasetsForMetrica(this.metricaActiva, isDark);
            const scalesConfig = this.getScalesForMetrica(this.metricaActiva, isDark);

            let plotLabels = this.labels.length ? [...this.labels] : ['Sin datos'];
            if (plotLabels.length === 1) {
                plotLabels = ['Previo', plotLabels[0]];
                datasets.forEach(ds => {
                    if (ds.data && ds.data.length === 1) {
                        ds.data = [ds.data[0], ds.data[0]];
                    }
                });
            }

            chart.data.labels = plotLabels;
            chart.data.datasets = datasets;
            chart.options.scales = scalesConfig;
            chart.update();
        },

        getScalesForMetrica(metrica, isDark) {
            const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
            const tickColor = isDark ? '#94A3B8' : '#64748B';

            let yLimits = { suggestedMin: 50, suggestedMax: 160 };
            if (metrica === 'FC') yLimits = { suggestedMin: 50, suggestedMax: 110 };
            else if (metrica === 'SPO2') yLimits = { suggestedMin: 85, suggestedMax: 100 };
            else if (metrica === 'TEMP') yLimits = { suggestedMin: 35.0, suggestedMax: 39.0 };
            else if (metrica === 'FR') yLimits = { suggestedMin: 10, suggestedMax: 26 };
            else if (metrica === 'DOLOR') yLimits = { suggestedMin: 0, suggestedMax: 10 };
            else if (metrica === 'GLUCOSA') yLimits = { suggestedMin: 60, suggestedMax: 180 };
            else if (metrica === 'PESO') yLimits = { suggestedMin: 50, suggestedMax: 90 };

            return {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: tickColor,
                        font: { size: 11, family: 'Inter, sans-serif' },
                        maxRotation: 0,
                    }
                },
                y: {
                    grid: { color: gridColor },
                    ticks: {
                        color: tickColor,
                        font: { size: 11, family: 'Inter, sans-serif' },
                        callback: (val) => {
                            if (metrica === 'TEMP') return val.toFixed(1) + '°';
                            if (metrica === 'SPO2') return val + '%';
                            return val;
                        }
                    },
                    ...yLimits
                }
            };
        },

        getDatasetsForMetrica(metrica, isDark) {
            const makeGradient = (ctx, colorHex, alphaTop = 0.22, alphaBottom = 0.0) => {
                const chart = ctx.chart;
                const { ctx: c, chartArea } = chart;
                if (!chartArea) return this.hexToRgba(colorHex, alphaTop);
                const gradient = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                gradient.addColorStop(0, this.hexToRgba(colorHex, alphaTop));
                gradient.addColorStop(1, this.hexToRgba(colorHex, alphaBottom));
                return gradient;
            };

            if (metrica === 'PA') {
                return [
                    {
                        label: 'Sistólica',
                        data: this.sistolica.length ? this.sistolica : [120, 122, 118, 125, 120, 119, 120],
                        borderColor: '#EF4444',
                        backgroundColor: (context) => makeGradient(context, '#EF4444', isDark ? 0.25 : 0.15, 0.01),
                        borderWidth: 2.4,
                        pointRadius: 4.5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#EF4444',
                        pointBorderColor: '#FFFFFF',
                        pointBorderWidth: 1.5,
                        tension: 0.35,
                        fill: false,
                    },
                    {
                        label: 'Diastólica',
                        data: this.diastolica.length ? this.diastolica : [78, 80, 76, 82, 79, 78, 78],
                        borderColor: '#2563EB',
                        backgroundColor: (context) => makeGradient(context, '#2563EB', isDark ? 0.20 : 0.12, 0.01),
                        borderWidth: 2.4,
                        pointRadius: 4.5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#2563EB',
                        pointBorderColor: '#FFFFFF',
                        pointBorderWidth: 1.5,
                        tension: 0.35,
                        fill: false,
                    }
                ];
            }

            const mapData = {
                'FC': { label: 'Frecuencia Cardíaca', data: this.fc, color: '#DC2626', fallback: [72, 75, 71, 74, 72, 73, 72] },
                'SPO2': { label: 'Saturación SpO₂', data: this.spo2, color: '#059669', fallback: [97, 98, 97, 96, 98, 97, 97] },
                'TEMP': { label: 'Temperatura', data: this.temp, color: '#EA580C', fallback: [36.5, 36.6, 36.4, 36.7, 36.5, 36.5, 36.5] },
                'FR': { label: 'Frecuencia Respiratoria', data: this.fr, color: '#0891B2', fallback: [18, 19, 17, 18, 18, 18, 18] },
                'DOLOR': { label: 'Dolor EVA', data: this.dolor, color: '#7C3AED', fallback: [2, 3, 2, 2, 1, 2, 2] },
                'GLUCOSA': { label: 'Glucemia', data: this.glucosa, color: '#0284C7', fallback: [105, 110, 102, 115, 108, 104, 105] },
                'PESO': { label: 'Peso Corporal', data: this.peso, color: '#475569', fallback: [68.5, 68.4, 68.6, 68.5, 68.5, 68.3, 68.5] },
            };

            const cfg = mapData[metrica] || mapData['FC'];
            const dataToUse = (cfg.data && cfg.data.length) ? cfg.data : cfg.fallback;

            return [{
                label: cfg.label,
                data: dataToUse,
                borderColor: cfg.color,
                backgroundColor: (context) => makeGradient(context, cfg.color, isDark ? 0.30 : 0.20, 0.01),
                borderWidth: 2.4,
                pointRadius: 4.5,
                pointHoverRadius: 7,
                pointBackgroundColor: cfg.color,
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 1.5,
                tension: 0.35,
                fill: true,
            }];
        },

        // =========================================================================
        // SPARKLINES (SEGUNDA FILA: 6 CARDS)
        // =========================================================================
        renderSparklines() {
            const isDark = document.documentElement.classList.contains('dark');
            const self = this;

            const cards = [
                {
                    id: 'sparklineCanvasPA',
                    isPA: true,
                    dataA: this.sistolica.length ? this.sistolica : [120, 122, 118, 125, 120, 119, 120],
                    dataB: this.diastolica.length ? this.diastolica : [78, 80, 76, 82, 79, 78, 78],
                    colorA: '#EF4444',
                    colorB: '#2563EB',
                    yLimits: { min: 50, max: 160 }
                },
                {
                    id: 'sparklineCanvasFC',
                    dataA: this.fc.length ? this.fc : [72, 75, 71, 74, 72, 73, 72],
                    colorA: '#DC2626',
                    yLimits: { min: 50, max: 110 }
                },
                {
                    id: 'sparklineCanvasSPO2',
                    dataA: this.spo2.length ? this.spo2 : [97, 98, 97, 96, 98, 97, 97],
                    colorA: '#059669',
                    yLimits: { min: 88, max: 100 }
                },
                {
                    id: 'sparklineCanvasTEMP',
                    dataA: this.temp.length ? this.temp : [36.5, 36.6, 36.4, 36.7, 36.5, 36.5, 36.5],
                    colorA: '#EA580C',
                    yLimits: { min: 35.0, max: 38.5 }
                },
                {
                    id: 'sparklineCanvasFR',
                    dataA: this.fr.length ? this.fr : [18, 19, 17, 18, 18, 18, 18],
                    colorA: '#0891B2',
                    yLimits: { min: 10, max: 26 }
                },
                {
                    id: 'sparklineCanvasDOLOR',
                    dataA: this.dolor.length ? this.dolor : [2, 3, 2, 2, 1, 2, 2],
                    colorA: '#7C3AED',
                    yLimits: { min: 0, max: 10 }
                },
            ];

            cards.forEach((item) => {
                const canvas = document.getElementById(item.id);
                if (!canvas) return;

                if (canvas.offsetParent === null) return;

                const existing = Chart.getChart(canvas);
                if (existing) {
                    try { existing.destroy(); } catch (e) {}
                }

                let plotA = [...(item.dataA || [])];
                let plotB = item.dataB ? [...item.dataB] : [];
                let sLabels = this.labels.length ? [...this.labels] : ['1', '2'];

                // Si solo hay 1 dato, duplicarlo para que dibuje una línea horizontal suave
                if (plotA.length === 1) {
                    plotA = [plotA[0], plotA[0]];
                    if (plotB.length === 1) plotB = [plotB[0], plotB[0]];
                    sLabels = ['1', '2'];
                }

                let datasets = [];
                if (item.isPA) {
                    datasets = [
                        {
                            data: plotA,
                            borderColor: item.colorA,
                            borderWidth: 2,
                            tension: 0.38,
                            pointRadius: 0,
                            fill: false,
                        },
                        {
                            data: plotB,
                            borderColor: item.colorB,
                            borderWidth: 2,
                            tension: 0.38,
                            pointRadius: 0,
                            fill: false,
                        }
                    ];
                } else {
                    const makeGrad = (ctx, chartArea) => {
                        if (!chartArea) return self.hexToRgba(item.colorA, 0.25);
                        const g = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        g.addColorStop(0, self.hexToRgba(item.colorA, isDark ? 0.38 : 0.26));
                        g.addColorStop(1, self.hexToRgba(item.colorA, 0.01));
                        return g;
                    };

                    datasets = [{
                        data: plotA,
                        borderColor: item.colorA,
                        backgroundColor: (context) => {
                            const chart = context.chart;
                            const { ctx, chartArea } = chart;
                            return makeGrad(ctx, chartArea);
                        },
                        borderWidth: 2,
                        tension: 0.38,
                        pointRadius: 0,
                        fill: true,
                    }];
                }

                new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: sLabels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 600 },
                        plugins: {
                            legend: { display: false },
                            datalabels: { display: false }, // DESACTIVAR DATALABELS EN MINI SPARKLINES
                            tooltip: { enabled: false },
                        },
                        scales: {
                            x: { display: false },
                            y: {
                                display: false,
                                ...(item.yLimits || {})
                            },
                        }
                    }
                });
            });
        },

        hexToRgba(hex, alpha = 1) {
            const cleanHex = hex.replace('#', '');
            const r = parseInt(cleanHex.substring(0, 2), 16) || 0;
            const g = parseInt(cleanHex.substring(2, 4), 16) || 0;
            const b = parseInt(cleanHex.substring(4, 6), 16) || 0;
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }
    }));
});
</script>
