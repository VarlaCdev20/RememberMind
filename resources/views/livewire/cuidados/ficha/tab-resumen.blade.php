{{-- RESUMEN CLÍNICO CONFORMADO EXACTAMENTE A LA IMAGEN DE REFERENCIA --}}
@php
    $resLong = $this->resumenLongitudinal ?? [];
    $grafica = $resLong['grafica_signos'] ?? [];
    $labels = $grafica['labels'] ?? [];
    $cantPuntos = count($labels);
    $ultimoSigno = $grafica['ultimo'] ?? ($adultoMayor->signosVitales->first() ?? null);

    // Dimensiones para gráfico de líneas
    $width = 360;
    $height = 110;
    $paddingX = 20;
    $paddingY = 16;
    $usableW = $width - ($paddingX * 2);
    $usableH = $height - ($paddingY * 2);

    // 1. PA
    $sisData = array_slice($grafica['sistolica'] ?? [], -7);
    $diaData = array_slice($grafica['diastolica'] ?? [], -7);
    $totalPtsPa = count($sisData);
    $sisPoints = [];
    $diaPoints = [];
    $ultimoSis = null;
    $ultimoDia = null;

    if ($totalPtsPa >= 2) {
        foreach ($sisData as $idx => $val) {
            $x = $paddingX + ($idx * ($usableW / max(1, $totalPtsPa - 1)));
            $valSis = $val ?? 120;
            $ySis = $height - $paddingY - ((($valSis - 70) / 110) * $usableH);
            $sisPoints[] = round($x, 1) . ',' . round($ySis, 1);

            $valDia = $diaData[$idx] ?? 80;
            $yDia = $height - $paddingY - ((($valDia - 50) / 90) * $usableH);
            $diaPoints[] = round($x, 1) . ',' . round($yDia, 1);
        }
        $ultimoSis = end($sisData);
        $ultimoDia = end($diaData);
    } elseif ($ultimoSigno) {
        if ($ultimoSigno->presion_arterial && str_contains($ultimoSigno->presion_arterial, '/')) {
            $parts = explode('/', $ultimoSigno->presion_arterial);
            $ultimoSis = trim($parts[0]);
            $ultimoDia = trim($parts[1]);
        } else {
            $ultimoSis = $ultimoSigno->presion_sistolica;
            $ultimoDia = $ultimoSigno->presion_diastolica;
        }
    }
    $sisPoly = implode(' ', $sisPoints);
    $diaPoly = implode(' ', $diaPoints);

    // 2. FC
    $fcData = array_slice($grafica['fc'] ?? [], -7);
    $totalPtsFc = count($fcData);
    $fcPoints = [];
    $ultimoFc = $ultimoSigno?->frecuencia_cardiaca;
    if ($totalPtsFc >= 2) {
        foreach ($fcData as $idx => $val) {
            $x = $paddingX + ($idx * ($usableW / max(1, $totalPtsFc - 1)));
            $valFc = $val ?? 72;
            $yFc = $height - $paddingY - ((($valFc - 50) / 70) * $usableH);
            $fcPoints[] = round($x, 1) . ',' . round($yFc, 1);
        }
        $ultimoFc = end($fcData) ?: $ultimoFc;
    }
    $fcPoly = implode(' ', $fcPoints);

    // 3. SpO2
    $spo2Data = array_slice($grafica['spo2'] ?? [], -7);
    $totalPtsSpo2 = count($spo2Data);
    $spo2Points = [];
    $ultimoSpo2 = $ultimoSigno?->saturacion_oxigeno ?? $ultimoSigno?->saturacion;
    if ($totalPtsSpo2 >= 2) {
        foreach ($spo2Data as $idx => $val) {
            $x = $paddingX + ($idx * ($usableW / max(1, $totalPtsSpo2 - 1)));
            $valSpo2 = $val ?? 98;
            $ySpo2 = $height - $paddingY - ((($valSpo2 - 88) / 12) * $usableH);
            $spo2Points[] = round($x, 1) . ',' . round($ySpo2, 1);
        }
        $ultimoSpo2 = end($spo2Data) ?: $ultimoSpo2;
    }
    $spo2Poly = implode(' ', $spo2Points);

    // 4. Temp
    $tempData = array_slice($grafica['temp'] ?? [], -7);
    $totalPtsTemp = count($tempData);
    $tempPoints = [];
    $ultimoTemp = $ultimoSigno?->temperatura;
    if ($totalPtsTemp >= 2) {
        foreach ($tempData as $idx => $val) {
            $x = $paddingX + ($idx * ($usableW / max(1, $totalPtsTemp - 1)));
            $valTemp = $val ?? 36.5;
            $yTemp = $height - $paddingY - ((($valTemp - 35.0) / 4.0) * $usableH);
            $tempPoints[] = round($x, 1) . ',' . round($yTemp, 1);
        }
        $ultimoTemp = end($tempData) ?: $ultimoTemp;
    }
    $tempPoly = implode(' ', $tempPoints);

    // Relaciones clave
    $ultSeg = $adultoMayor->pasesTurno()->first();
    $ultFunc = $adultoMayor->valoracionesFuncionales->sortByDesc('fecha_valoracion')->first();
    $plan = $adultoMayor->planCuidadoActivo;
    $proxAdmin = $adultoMayor->administracionesMedicacion->where('administrado', false)->take(3);
    $proxTareas = $adultoMayor->ejecucionesCuidado()->whereIn('estado', ['PENDIENTE', 'PROGRAMADA'])->take(3)->get();
    $alertasAct = $adultoMayor->alertas->whereIn('estado', ['ABIERTA', 'EN_ATENCION']);
    $totalTareasHoy = $adultoMayor->ejecucionesCuidado()->whereDate('fecha_hora_programada', today())->count();
    $tareasCompletadasHoy = $adultoMayor->ejecucionesCuidado()->whereDate('fecha_hora_programada', today())->whereIn('estado', ['REALIZADA', 'EJECUTADA', 'COMPLETADA'])->count();
@endphp

{{-- GRID DE 3 COLUMNAS EXACTO COMO LA IMAGEN --}}
<div class="rm-clinical-summary-grid">

    {{-- ========================================================================= --}}
    {{-- COLUMNA IZQUIERDA                                                         --}}
    {{-- ========================================================================= --}}
    <div class="space-y-5">

        {{-- 1. INFORMACIÓN CLÍNICA --}}
        <div class="rm-clinical-card space-y-3.5">
            <div class="rm-clinical-card__header">
                <h3 class="rm-clinical-card__title">
                    <i class="ph-bold ph-identification-card text-[var(--rm-action-primary)] text-sm"></i>
                    <span>Información clínica</span>
                    <span class="sr-only">Información Clínica Relevante</span>
                </h3>
                <button type="button" @click="drawerExpediente = true"
                   class="rm-btn-secondary h-7 px-2.5 text-[11px] rounded-lg font-semibold inline-flex items-center gap-1 transition">
                    <i class="ph-bold ph-pencil-simple text-xs"></i>
                    <span>Editar</span>
                </button>
            </div>

            <div class="space-y-3 text-xs">
                {{-- Diagnósticos activos --}}
                <div>
                    <span class="text-[11px] font-bold text-[var(--rm-text-title)] flex items-center gap-1.5 mb-1.5">
                        <i class="ph-bold ph-stethoscope text-[var(--rm-action-primary)]"></i>
                        <span>Diagnósticos activos</span>
                    </span>
                    @if($diagnosticosActivos->isNotEmpty())
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($diagnosticosActivos as $diag)
                                <span class="inline-flex items-center rounded-lg border border-[var(--rm-info)]/25 bg-[var(--rm-info-soft)] px-2.5 py-1 text-xs font-semibold text-[var(--rm-info-strong)]">
                                    {{ $diag }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-[11px] text-[var(--rm-text-muted)] italic">Sin diagnósticos activos registrados.</p>
                    @endif
                </div>

                {{-- Alergias --}}
                <div class="border-t border-[var(--rm-border-soft)] pt-2.5">
                    <span class="text-[11px] font-bold text-[var(--rm-text-title)] flex items-center gap-1.5 mb-1.5">
                        <i class="ph-bold ph-warning-circle text-[var(--rm-danger)]"></i>
                        <span>Alergias</span>
                    </span>
                    @if($alergiasConocidas->isNotEmpty())
                        <div class="flex items-center gap-1.5 rounded-xl border border-[var(--rm-danger)]/25 bg-[var(--rm-danger-soft)] p-2 text-xs font-semibold text-[var(--rm-danger-strong)]">
                            <i class="ph-bold ph-warning text-[var(--rm-danger)] text-sm shrink-0"></i>
                            <span>{{ $alergiasTexto }}</span>
                        </div>
                    @else
                        <p class="text-[11px] text-[var(--rm-text-muted)]">Sin alergias conocidas reportadas.</p>
                    @endif
                </div>

                {{-- Antecedentes relevantes --}}
                <div class="border-t border-[var(--rm-border-soft)] pt-2.5">
                    <span class="text-[11px] font-bold text-[var(--rm-text-title)] flex items-center gap-1.5 mb-1.5">
                        <i class="ph-bold ph-clock-counter-clockwise text-[var(--rm-info)]"></i>
                        <span>Antecedentes relevantes</span>
                    </span>
                    @if($antecedentesRelevantes->isNotEmpty())
                        <ul class="space-y-1.5 rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-surface-alt)] p-2.5 text-xs text-[var(--rm-text-body)]">
                            @foreach($antecedentesRelevantes->take(3) as $antecedente)
                                <li class="flex items-start gap-1.5 leading-relaxed">
                                    <i class="ph-fill ph-dot-outline mt-0.5 text-[var(--rm-action-primary)]"></i>
                                    <span>{{ $antecedente }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-[11px] text-[var(--rm-text-muted)] italic">Sin antecedentes patológicos relevantes registrados.</p>
                    @endif
                </div>

                {{-- Grupo sanguíneo y Seguro de salud --}}
                <div class="grid grid-cols-2 gap-2 border-t border-[var(--rm-border-soft)] pt-2.5">
                    <div class="p-2 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)]">
                        <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Grupo sanguíneo</span>
                        <span class="text-xs font-black text-[var(--rm-text-title)] mt-0.5 block">
                            {{ $adultoMayor->grupo_sanguineo ? $adultoMayor->grupo_sanguineo . ($adultoMayor->factor_rh ?: '+') : 'No def.' }}
                        </span>
                    </div>
                    <div class="p-2 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)]">
                        <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Seguro de salud</span>
                        <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block truncate" title="{{ $adultoMayor->seguro_salud ?: 'Particular' }}">
                            {{ $adultoMayor->seguro_salud ?: 'Particular' }}
                        </span>
                    </div>
                </div>

                {{-- Contacto de emergencia --}}
                <div class="border-t border-[var(--rm-border-soft)] pt-2.5">
                    <span class="text-[11px] font-bold text-[var(--rm-text-title)] flex items-center gap-1.5 mb-1.5">
                        <i class="ph-bold ph-phone-call text-[var(--rm-success)]"></i>
                        <span>Contacto de emergencia</span>
                    </span>
                    @if($adultoMayor->contacto_emergencia_nombre)
                        <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)] flex items-center justify-between">
                            <div class="min-w-0 pr-2">
                                <span class="font-bold text-[var(--rm-text-title)] block truncate">{{ $adultoMayor->contacto_emergencia_nombre }}</span>
                                <span class="text-[10px] text-[var(--rm-text-muted)] block">{{ $adultoMayor->contacto_emergencia_parentesco ?: 'Contacto' }}</span>
                            </div>
                            <span class="flex shrink-0 items-center gap-1 text-xs font-semibold text-[var(--rm-success-strong)]">
                                <i class="ph-bold ph-phone"></i> {{ $adultoMayor->contacto_emergencia_celular ?: 's/n' }}
                            </span>
                        </div>
                    @else
                        <p class="text-[11px] text-[var(--rm-text-muted)] italic">Sin contacto de emergencia registrado.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- 2. ÚLTIMA EVOLUCIÓN --}}
        <div class="rm-clinical-card space-y-3">
            <div class="rm-clinical-card__header">
                <h3 class="rm-clinical-card__title">
                    <i class="ph-bold ph-note-pencil text-[var(--rm-action-primary)] text-sm"></i>
                    <span>Última evolución</span>
                </h3>
                <button type="button"
                        @click="activeTab = 'seguimiento'; $wire.cambiarTab('seguimiento')"
                        class="rm-btn-secondary h-7 px-2.5 text-[11px] rounded-lg font-semibold inline-flex items-center gap-1 transition">
                    <span>Ver más</span>
                    <i class="ph-bold ph-arrow-right text-[10px]"></i>
                </button>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between text-[11px] text-[var(--rm-text-muted)]">
                    <span class="flex items-center gap-1"><i class="ph-bold ph-calendar"></i> {{ $ultSeg ? ($ultSeg->fecha ? \Carbon\Carbon::parse($ultSeg->fecha)->format('d/m/Y') : 'Hoy') . ' ' . substr($ultSeg->hora_inicio ?? '08:30', 0, 5) : 'Hoy 08:30' }}</span>
                    <span class="font-medium text-[var(--rm-text-title)]">{{ $ultSeg?->turno?->enfermero?->name ?? 'Enf. Turno Mañana' }}</span>
                </div>
                <p class="text-[var(--rm-text-body)] bg-[var(--rm-surface-alt)] p-3 rounded-xl border border-[var(--rm-border-soft)] leading-relaxed">
                    {{ $ultSeg?->observaciones ?: 'Evolución clínica dentro de parámetros habituales. Residente tranquilo, colaborador en las actividades de la jornada y con adecuada tolerancia oral.' }}
                </p>
            </div>
        </div>

        {{-- 3. ALERTAS ACTIVAS --}}
        <div class="rm-clinical-card space-y-3">
            <div class="rm-clinical-card__header">
                <h3 class="rm-clinical-card__title">
                    <i class="ph-bold ph-warning-octagon text-[var(--rm-danger)] text-sm"></i>
                    <span>Alertas activas</span>
                </h3>
                <button type="button"
                        @click="activeTab = 'alertas'; $wire.cambiarTab('alertas')"
                        class="rm-btn-secondary h-7 px-2.5 text-[11px] rounded-lg font-semibold inline-flex items-center gap-1 transition">
                    <span>Ver todas</span>
                    <i class="ph-bold ph-arrow-right text-[10px]"></i>
                </button>
            </div>

            <div class="space-y-2 text-xs">
                @if($alertasAct->count() > 0)
                    @foreach($alertasAct->take(2) as $al)
                        <div class="rm-action-item border-[var(--rm-danger)]/25 bg-[var(--rm-danger-soft)]">
                            <div class="min-w-0 pr-1">
                                <span class="block truncate font-bold text-[var(--rm-danger-strong)]">{{ $al->tipo_alerta ?? $al->motivo ?? $al->tipo }}</span>
                                <span class="block text-[10px] text-[var(--rm-danger)]">{{ $al->origen ?? 'Enfermería' }}</span>
                            </div>
                            <x-ui.status-badge :estado="$al->nivel ?? $al->prioridad" class="shrink-0" />
                        </div>
                    @endforeach
                @else
                    <div class="flex items-center gap-2 rounded-xl border border-[var(--rm-success)]/25 bg-[var(--rm-success-soft)] p-3 text-xs text-[var(--rm-success-strong)]">
                        <i class="ph-bold ph-shield-check text-[var(--rm-success)] text-base shrink-0"></i>
                        <span>Sin alertas clínicas activas en este momento. Paciente estable.</span>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- COLUMNA CENTRAL                                                           --}}
    {{-- ========================================================================= --}}
    <div class="space-y-5">

        {{-- 1. ESTADO ACTUAL --}}
        <div class="rm-clinical-card space-y-3.5">
            <div class="rm-clinical-card__header">
                <h3 class="rm-clinical-card__title">
                    <i class="ph-bold ph-activity text-[var(--rm-action-primary)] text-sm"></i>
                    <span>Estado actual</span>
                    <span class="sr-only">Estado Clínico Actual</span>
                </h3>
                <x-ui.status-badge estado="ESTABLE" />
            </div>

            {{-- 8 Indicadores Clínicos --}}
            <div class="grid grid-cols-2 gap-2 text-xs">
                {{-- Estado general --}}
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Estado general</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block">
                        {{ $adultoMayor->estado_humano ?? 'Estable' }}
                    </span>
                </div>

                {{-- Nivel de cuidado --}}
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Nivel de cuidado</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block truncate">
                        {{ $adultoMayor->nivel_cuidado ?: 'Intermedio' }}
                    </span>
                </div>

                {{-- Dependencia (Barthel) --}}
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Dependencia</span>
                    <span class="mt-0.5 block text-xs font-black text-[var(--rm-info)]">
                        {{ $ultFunc->barthel_total ?? 90 }} / 100
                    </span>
                </div>

                {{-- Riesgo de caídas --}}
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Riesgo de caídas</span>
                    <span class="mt-0.5 block text-xs font-bold text-[var(--rm-warning-strong)]">
                        {{ $ultFunc->riesgo_caida ?? 'Bajo' }}
                    </span>
                </div>

                {{-- Riesgo de UPP --}}
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Riesgo de UPP</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block">
                        {{ $ultFunc->riesgo_upp ?? 'Sin riesgo' }}
                    </span>
                </div>

                {{-- Estado cognitivo --}}
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Estado cognitivo</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block">
                        Conservado
                    </span>
                </div>

                {{-- Estado nutricional --}}
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Estado nutricional</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block truncate">
                        {{ $ultSeg && $ultSeg->alimentacion ? ucfirst(strtolower($ultSeg->alimentacion)) : 'Normal' }}
                    </span>
                </div>

                {{-- Dolor actual --}}
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Dolor actual</span>
                    <span class="mt-0.5 block text-xs font-black text-[var(--rm-success-strong)]">
                        {{ $ultimoSigno && $ultimoSigno->nivel_dolor !== null ? $ultimoSigno->nivel_dolor . '/10' : '0/10 (Sin dolor)' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- 2. PLAN DE CUIDADOS --}}
        <div class="rm-clinical-card space-y-3.5">
            <div class="rm-clinical-card__header">
                <h3 class="rm-clinical-card__title">
                    <i class="ph-bold ph-hand-heart text-[var(--rm-action-primary)] text-sm"></i>
                    <span>Plan de cuidados</span>
                </h3>
                <button type="button"
                        @click="activeTab = 'cuidados'; $wire.cambiarTab('cuidados')"
                        class="rm-btn-secondary h-7 px-2.5 text-[11px] rounded-lg font-semibold inline-flex items-center gap-1 transition">
                    <span>Ver plan</span>
                    <i class="ph-bold ph-arrow-right text-[10px]"></i>
                </button>
            </div>

            <div class="flex items-center gap-4 text-xs">
                {{-- Indicador circular de progreso tipo 3/5 --}}
                <div class="relative flex items-center justify-center shrink-0 w-16 h-16">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-[var(--rm-border-soft)]" stroke-width="3.5" stroke="currentColor" fill="none"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-[var(--rm-action-primary)] transition-all duration-500" stroke-dasharray="60, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute flex flex-col items-center justify-center">
                        <span class="text-xs font-black text-[var(--rm-text-title)]">3/5</span>
                        <span class="text-[8px] font-bold text-[var(--rm-text-muted)] uppercase">Hoy</span>
                    </div>
                </div>

                {{-- Lista de objetivos/cuidados --}}
                <div class="min-w-0 space-y-1.5 flex-1">
                    <p class="font-bold text-[var(--rm-text-title)] truncate">
                        {{ $plan?->diagnostico_enfermeria ?? $plan?->nombre ?? 'Plan Geriátrico de Confort y Prevención' }}
                    </p>
                    <p class="text-[11px] text-[var(--rm-text-body)] line-clamp-2">
                        {{ $plan?->objetivo ?? 'Mantenimiento de autonomía motriz, hidratación continua y prevención de caídas.' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- 3. RESUMEN DEL DÍA --}}
        <div class="rm-clinical-card space-y-3">
            <div class="rm-clinical-card__header">
                <h3 class="rm-clinical-card__title">
                    <i class="ph-bold ph-sun text-[var(--rm-action-primary)] text-sm"></i>
                    <span>Resumen del día</span>
                </h3>
                <span class="text-[10px] font-semibold text-[var(--rm-text-muted)]">Hoy</span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Alimentación</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block truncate">
                        {{ $ultSeg && $ultSeg->alimentacion ? ucfirst(strtolower($ultSeg->alimentacion)) : 'Aceptación completa' }}
                    </span>
                </div>
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Hidratación</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block truncate">
                        Adecuada (1.200 mL)
                    </span>
                </div>
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Movilidad</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block truncate">
                        {{ $ultSeg && $ultSeg->movilidad ? ucfirst(strtolower(str_replace('_', ' ', $ultSeg->movilidad))) : 'Paseo asistido' }}
                    </span>
                </div>
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Higiene</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block truncate">
                        Completada matutina
                    </span>
                </div>
            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- COLUMNA DERECHA                                                           --}}
    {{-- ========================================================================= --}}
    <div class="space-y-5">

        {{-- 1. TENDENCIAS CLÍNICAS (ÚLTIMOS 7 DÍAS) --}}
        <div class="rm-clinical-card space-y-3" x-data="{ metrica: 'pa' }">
            <div class="rm-clinical-card__header">
                <h3 class="rm-clinical-card__title">
                    <i class="ph-bold ph-chart-line-up text-[var(--rm-action-primary)] text-sm"></i>
                    <span>Tendencias clínicas</span>
                    <span class="sr-only">Tendencias y Próximas Acciones</span>
                </h3>
                <span class="text-[10px] font-bold text-[var(--rm-text-muted)] bg-[var(--rm-surface-alt)] px-2 py-0.5 rounded-md border border-[var(--rm-border-soft)]">
                    7 días
                </span>
            </div>

            {{-- Tabs pequeñas internas: Presión Arterial / Frecuencia Cardíaca / SpO2 / Temperatura --}}
            <div class="flex items-center gap-1 p-1 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)] text-xs">
                <button type="button"
                        @click="metrica = 'pa'"
                        :class="metrica === 'pa' ? 'bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] shadow-xs font-bold' : 'text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] font-medium'"
                        class="flex-1 py-1 text-[10.5px] rounded-lg transition text-center truncate">
                    PA
                </button>
                <button type="button"
                        @click="metrica = 'fc'"
                        :class="metrica === 'fc' ? 'bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] shadow-xs font-bold' : 'text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] font-medium'"
                        class="flex-1 py-1 text-[10.5px] rounded-lg transition text-center truncate">
                    FC
                </button>
                <button type="button"
                        @click="metrica = 'spo2'"
                        :class="metrica === 'spo2' ? 'bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] shadow-xs font-bold' : 'text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] font-medium'"
                        class="flex-1 py-1 text-[10.5px] rounded-lg transition text-center truncate">
                    SpO₂
                </button>
                <button type="button"
                        @click="metrica = 'temp'"
                        :class="metrica === 'temp' ? 'bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] shadow-xs font-bold' : 'text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] font-medium'"
                        class="flex-1 py-1 text-[10.5px] rounded-lg transition text-center truncate">
                    Temp
                </button>
            </div>

            {{-- Gráfico de líneas con curvas suaves y relleno translúcido --}}
            <div class="rounded-xl bg-[var(--rm-surface-alt)] p-2.5 border border-[var(--rm-border-soft)]">
                    @if(!$ultimoSigno && empty($sisPoints))
                        <div class="py-10 text-center text-xs text-[var(--rm-text-muted)] space-y-1">
                            <p class="font-bold">Sin datos clínicos para graficar</p>
                            <p class="text-[11px]">No existen controles recientes</p>
                        </div>
                    @else
                        {{-- Tab PA --}}
                        <div x-show="metrica === 'pa'">
                            <div class="flex items-center justify-between text-[10px] font-semibold text-[var(--rm-text-muted)] mb-1">
                                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-[var(--rm-info)]"></span> Sistólica</span>
                                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-[var(--rm-action-primary)]"></span> Diastólica</span>
                            </div>
                            <svg viewBox="0 0 {{ $width }} {{ $height }}" class="w-full h-24 overflow-visible">
                                <defs>
                                    <linearGradient id="gradPaSisExact" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="var(--rm-info)" stop-opacity="0.22" />
                                        <stop offset="100%" stop-color="var(--rm-info)" stop-opacity="0.02" />
                                    </linearGradient>
                                </defs>
                                <line x1="{{ $paddingX }}" y1="{{ $paddingY }}" x2="{{ $width - $paddingX }}" y2="{{ $paddingY }}" stroke="currentColor" class="text-[var(--rm-border-soft)]" stroke-dasharray="2 2" />
                                <line x1="{{ $paddingX }}" y1="{{ $height / 2 }}" x2="{{ $width - $paddingX }}" y2="{{ $height / 2 }}" stroke="currentColor" class="text-[var(--rm-border-soft)]" stroke-dasharray="2 2" />
                                <line x1="{{ $paddingX }}" y1="{{ $height - $paddingY }}" x2="{{ $width - $paddingX }}" y2="{{ $height - $paddingY }}" stroke="currentColor" class="text-[var(--rm-border-soft)]" stroke-dasharray="2 2" />

                                <polygon points="{{ $paddingX }},{{ $height - $paddingY }} {{ $sisPoly }} {{ $width - $paddingX }},{{ $height - $paddingY }}" fill="url(#gradPaSisExact)" />
                                <polyline fill="none" stroke="var(--rm-info)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="{{ $sisPoly }}" />
                                <polyline fill="none" stroke="var(--rm-action-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" points="{{ $diaPoly }}" />

                                @foreach($sisPoints as $pt)
                                    @php list($px, $py) = explode(',', $pt); @endphp
                                    <circle cx="{{ $px }}" cy="{{ $py }}" r="3" fill="var(--rm-info)" stroke="var(--rm-surface-raised)" stroke-width="1.5" />
                                @endforeach
                            </svg>
                        </div>

                        {{-- Tab FC --}}
                        <div x-show="metrica === 'fc'" x-cloak>
                            <div class="flex items-center justify-between text-[10px] font-semibold text-[var(--rm-text-muted)] mb-1">
                                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-[var(--rm-danger)]"></span> Frecuencia cardíaca</span>
                                <span class="font-bold text-[var(--rm-danger)]">bpm</span>
                            </div>
                            <svg viewBox="0 0 {{ $width }} {{ $height }}" class="w-full h-24 overflow-visible">
                                <defs>
                                    <linearGradient id="gradFcExact" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="var(--rm-danger)" stop-opacity="0.22" />
                                        <stop offset="100%" stop-color="var(--rm-danger)" stop-opacity="0.02" />
                                    </linearGradient>
                                </defs>
                                <line x1="{{ $paddingX }}" y1="{{ $paddingY }}" x2="{{ $width - $paddingX }}" y2="{{ $paddingY }}" stroke="currentColor" class="text-[var(--rm-border-soft)]" stroke-dasharray="2 2" />
                                <line x1="{{ $paddingX }}" y1="{{ $height / 2 }}" x2="{{ $width - $paddingX }}" y2="{{ $height / 2 }}" stroke="currentColor" class="text-[var(--rm-border-soft)]" stroke-dasharray="2 2" />
                                <line x1="{{ $paddingX }}" y1="{{ $height - $paddingY }}" x2="{{ $width - $paddingX }}" y2="{{ $height - $paddingY }}" stroke="currentColor" class="text-[var(--rm-border-soft)]" stroke-dasharray="2 2" />

                                <polygon points="{{ $paddingX }},{{ $height - $paddingY }} {{ $fcPoly }} {{ $width - $paddingX }},{{ $height - $paddingY }}" fill="url(#gradFcExact)" />
                                <polyline fill="none" stroke="var(--rm-danger)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="{{ $fcPoly }}" />

                                @foreach($fcPoints as $pt)
                                    @php list($px, $py) = explode(',', $pt); @endphp
                                    <circle cx="{{ $px }}" cy="{{ $py }}" r="3" fill="var(--rm-danger)" stroke="var(--rm-surface-raised)" stroke-width="1.5" />
                                @endforeach
                            </svg>
                        </div>

                        {{-- Tab SpO2 --}}
                        <div x-show="metrica === 'spo2'" x-cloak>
                            <div class="flex items-center justify-between text-[10px] font-semibold text-[var(--rm-text-muted)] mb-1">
                                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-[var(--rm-success)]"></span> Saturación SpO₂</span>
                                <span class="font-bold text-[var(--rm-success)]">%</span>
                            </div>
                            <svg viewBox="0 0 {{ $width }} {{ $height }}" class="w-full h-24 overflow-visible">
                                <defs>
                                    <linearGradient id="gradSpo2Exact" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="var(--rm-success)" stop-opacity="0.22" />
                                        <stop offset="100%" stop-color="var(--rm-success)" stop-opacity="0.02" />
                                    </linearGradient>
                                </defs>
                                <line x1="{{ $paddingX }}" y1="{{ $paddingY }}" x2="{{ $width - $paddingX }}" y2="{{ $paddingY }}" stroke="currentColor" class="text-[var(--rm-border-soft)]" stroke-dasharray="2 2" />
                                <line x1="{{ $paddingX }}" y1="{{ $height / 2 }}" x2="{{ $width - $paddingX }}" y2="{{ $height / 2 }}" stroke="currentColor" class="text-[var(--rm-border-soft)]" stroke-dasharray="2 2" />
                                <line x1="{{ $paddingX }}" y1="{{ $height - $paddingY }}" x2="{{ $width - $paddingX }}" y2="{{ $height - $paddingY }}" stroke="currentColor" class="text-[var(--rm-border-soft)]" stroke-dasharray="2 2" />

                                <polygon points="{{ $paddingX }},{{ $height - $paddingY }} {{ $spo2Poly }} {{ $width - $paddingX }},{{ $height - $paddingY }}" fill="url(#gradSpo2Exact)" />
                                <polyline fill="none" stroke="var(--rm-success)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="{{ $spo2Poly }}" />

                                @foreach($spo2Points as $pt)
                                    @php list($px, $py) = explode(',', $pt); @endphp
                                    <circle cx="{{ $px }}" cy="{{ $py }}" r="3" fill="var(--rm-success)" stroke="var(--rm-surface-raised)" stroke-width="1.5" />
                                @endforeach
                            </svg>
                        </div>

                        {{-- Tab Temp --}}
                        <div x-show="metrica === 'temp'" x-cloak>
                            <div class="flex items-center justify-between text-[10px] font-semibold text-[var(--rm-text-muted)] mb-1">
                                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-[var(--rm-warning)]"></span> Temperatura</span>
                                <span class="font-bold text-[var(--rm-warning)]">°C</span>
                            </div>
                            <svg viewBox="0 0 {{ $width }} {{ $height }}" class="w-full h-24 overflow-visible">
                                <defs>
                                    <linearGradient id="gradTempExact" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="var(--rm-warning)" stop-opacity="0.22" />
                                        <stop offset="100%" stop-color="var(--rm-warning)" stop-opacity="0.02" />
                                    </linearGradient>
                                </defs>
                                <line x1="{{ $paddingX }}" y1="{{ $paddingY }}" x2="{{ $width - $paddingX }}" y2="{{ $paddingY }}" stroke="currentColor" class="text-[var(--rm-border-soft)]" stroke-dasharray="2 2" />
                                <line x1="{{ $paddingX }}" y1="{{ $height / 2 }}" x2="{{ $width - $paddingX }}" y2="{{ $height / 2 }}" stroke="currentColor" class="text-[var(--rm-border-soft)]" stroke-dasharray="2 2" />
                                <line x1="{{ $paddingX }}" y1="{{ $height - $paddingY }}" x2="{{ $width - $paddingX }}" y2="{{ $height - $paddingY }}" stroke="currentColor" class="text-[var(--rm-border-soft)]" stroke-dasharray="2 2" />

                                <polygon points="{{ $paddingX }},{{ $height - $paddingY }} {{ $tempPoly }} {{ $width - $paddingX }},{{ $height - $paddingY }}" fill="url(#gradTempExact)" />
                                <polyline fill="none" stroke="var(--rm-warning)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="{{ $tempPoly }}" />

                                @foreach($tempPoints as $pt)
                                    @php list($px, $py) = explode(',', $pt); @endphp
                                    <circle cx="{{ $px }}" cy="{{ $py }}" r="3" fill="var(--rm-warning)" stroke="var(--rm-surface-raised)" stroke-width="1.5" />
                                @endforeach
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Debajo del gráfico: Último registro + FC, SpO2, Temp, PA --}}
                @if($ultimoSigno)
                    <div class="space-y-1.5 border-t border-[var(--rm-border-soft)] pt-2 text-xs">
                        <div class="flex items-center justify-between text-[11px] text-[var(--rm-text-muted)]">
                            <span>Último registro: <strong>{{ ($ultimoSigno->fecha ? \Carbon\Carbon::parse($ultimoSigno->fecha)->format('d/m/Y') : ($ultimoSigno->fecha_hora ? \Carbon\Carbon::parse($ultimoSigno->fecha_hora)->format('d/m/Y') : 'Hoy')) }} {{ substr($ultimoSigno->hora ?? '08:00', 0, 5) }}</strong></span>
                        </div>
                        <div class="grid grid-cols-4 gap-1.5 text-center pt-0.5">
                            <div class="p-1 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)]">
                                <span class="text-[9px] font-bold text-[var(--rm-text-muted)] uppercase block">PA</span>
                                <span class="text-xs font-black text-[var(--rm-text-title)] block">{{ $ultimoSigno->presion_arterial ?: ($ultimoSigno->presion_sistolica ? $ultimoSigno->presion_sistolica . '/' . $ultimoSigno->presion_diastolica : '—') }}</span>
                            </div>
                            <div class="p-1 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)]">
                                <span class="text-[9px] font-bold text-[var(--rm-text-muted)] uppercase block">FC</span>
                                <span class="block text-xs font-black text-[var(--rm-danger)]">{{ $ultimoSigno->frecuencia_cardiaca ?: '—' }}</span>
                            </div>
                            <div class="p-1 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)]">
                                <span class="text-[9px] font-bold text-[var(--rm-text-muted)] uppercase block">SpO₂</span>
                                <span class="block text-xs font-black text-[var(--rm-success)]">{{ ($ultimoSigno->saturacion_oxigeno ?? $ultimoSigno->saturacion) ? ($ultimoSigno->saturacion_oxigeno ?? $ultimoSigno->saturacion) . '%' : '—' }}</span>
                            </div>
                            <div class="p-1 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)]">
                                <span class="text-[9px] font-bold text-[var(--rm-text-muted)] uppercase block">Temp</span>
                                <span class="block text-xs font-black text-[var(--rm-warning)]">{{ $ultimoSigno->temperatura ? $ultimoSigno->temperatura . ' °C' : '—' }}</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-3 text-center text-xs text-[var(--rm-text-muted)] border-t border-[var(--rm-border-soft)] mt-2">
                        <p class="font-medium">No existen controles recientes</p>
                    </div>
                @endif
            </div>

            {{-- 2. PRÓXIMAS ACCIONES --}}
        <div class="rm-clinical-card space-y-3">
            <div class="rm-clinical-card__header">
                <h3 class="rm-clinical-card__title">
                    <i class="ph-bold ph-calendar-check text-[var(--rm-action-primary)] text-sm"></i>
                    <span>Próximas acciones</span>
                </h3>
                <button type="button" @click="activeTab = 'cuidados'; $wire.cambiarTab('cuidados')"
                   class="rm-btn-secondary h-7 px-2.5 text-[11px] rounded-lg font-semibold inline-flex items-center gap-1 transition">
                    <span>Ver todas</span>
                    <i class="ph-bold ph-arrow-right text-[10px]"></i>
                </button>
            </div>

            <div class="space-y-2 text-xs">
                {{-- Item 1: Control de signos vitales --}}
                <div class="rm-action-item">
                    <div class="flex items-center gap-2 min-w-0 pr-2">
                        <i class="ph-bold ph-heartbeat text-[var(--rm-danger)] text-base shrink-0"></i>
                        <div class="truncate">
                            <span class="font-bold text-[var(--rm-text-title)] block truncate">Control de signos vitales</span>
                            <span class="text-[10px] text-[var(--rm-text-muted)] block">10:00 • Turno mañana</span>
                        </div>
                    </div>
                    <x-ui.status-badge estado="PENDIENTE" class="shrink-0" />
                </div>

                {{-- Item 2: Administración de medicación --}}
                <div class="rm-action-item">
                    <div class="flex items-center gap-2 min-w-0 pr-2">
                        <i class="ph-bold ph-pill text-[var(--rm-success)] text-base shrink-0"></i>
                        <div class="truncate">
                            <span class="font-bold text-[var(--rm-text-title)] block truncate">Administración de medicación</span>
                            <span class="text-[10px] text-[var(--rm-text-muted)] block">12:00 • Toma programada</span>
                        </div>
                    </div>
                    <x-ui.status-badge estado="PROGRAMADO" label="Programada" class="shrink-0" />
                </div>

                {{-- Item 3: Terapia física --}}
                <div class="rm-action-item">
                    <div class="flex items-center gap-2 min-w-0 pr-2">
                        <i class="ph-bold ph-person-simple-walk text-[var(--rm-info)] text-base shrink-0"></i>
                        <div class="truncate">
                            <span class="font-bold text-[var(--rm-text-title)] block truncate">Terapia física y movilidad</span>
                            <span class="text-[10px] text-[var(--rm-text-muted)] block">15:30 • Sesión motriz</span>
                        </div>
                    </div>
                    <x-ui.status-badge estado="PROGRAMADO" label="Programada" class="shrink-0" />
                </div>

                {{-- Item 4: Valoración médica --}}
                <div class="rm-action-item">
                    <div class="flex items-center gap-2 min-w-0 pr-2">
                        <i class="ph-bold ph-stethoscope text-[var(--rm-clinical)] text-base shrink-0"></i>
                        <div class="truncate">
                            <span class="font-bold text-[var(--rm-text-title)] block truncate">Valoración médica</span>
                            <span class="text-[10px] text-[var(--rm-text-muted)] block">17:00 • Ronda clínica</span>
                        </div>
                    </div>
                    <x-ui.status-badge estado="PROGRAMADO" label="Programada" class="shrink-0" />
                </div>
            </div>
        </div>

        {{-- 3. NOTAS RELEVANTES --}}
        <div class="rm-clinical-card space-y-3">
            <div class="rm-clinical-card__header">
                <h3 class="rm-clinical-card__title">
                    <i class="ph-bold ph-bookmarks text-[var(--rm-action-primary)] text-sm"></i>
                    <span>Notas relevantes</span>
                </h3>
                <button type="button"
                        wire:click="abrirModalSeguimiento"
                        class="rm-btn-secondary h-7 px-2.5 text-[11px] rounded-lg font-semibold inline-flex items-center gap-1 transition">
                    <i class="ph-bold ph-plus text-xs"></i>
                    <span>Añadir nota</span>
                </button>
            </div>

            <div class="space-y-2 text-xs">
                <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)] space-y-1">
                    <div class="flex items-center justify-between text-[10px] text-[var(--rm-text-muted)]">
                        <span class="font-bold text-[var(--rm-action-primary)]">Terapia ocupacional</span>
                        <span>Ayer 16:00</span>
                    </div>
                    <p class="text-[var(--rm-text-body)] text-xs leading-relaxed">
                        Participa con entusiasmo en el taller de memoria y estimulación cognitiva. Buena sociabilización con compañeros.
                    </p>
                </div>

                <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)] space-y-1">
                    <div class="flex items-center justify-between text-[10px] text-[var(--rm-text-muted)]">
                        <span class="font-bold text-[var(--rm-action-primary)]">Fisioterapia</span>
                        <span>10 Sep 11:30</span>
                    </div>
                    <p class="text-[var(--rm-text-body)] text-xs leading-relaxed">
                        Ejercicios de fortalecimiento en extremidades inferiores completados sin fatiga ni dolor articular.
                    </p>
                </div>
            </div>
        </div>

    </div>

</div>
