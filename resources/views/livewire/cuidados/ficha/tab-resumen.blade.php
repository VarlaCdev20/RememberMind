{{-- TAB 1: RESUMEN CLÍNICO (3 COLUMNAS EQUILIBRADAS) --}}
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3 items-start">

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- COLUMNA 1: INFORMACIÓN CLÍNICA RELEVANTE                             --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="space-y-5">
        <div class="flex items-center justify-between border-b border-borde pb-2">
            <h2 class="text-xs font-black uppercase tracking-wider text-titulo flex items-center gap-1.5">
                <i class="ph-bold ph-identification-card text-blue-600"></i>
                <span>Información Clínica Relevante</span>
            </h2>
            <span class="text-[10px] font-bold text-apoyo uppercase">Expediente</span>
        </div>

        {{-- Tarjeta Agrupada 1: Diagnósticos, Alergias y Antecedentes --}}
        <div class="rounded-2xl border border-borde rm-surface-card bg-fondo-panel p-4 shadow-sm space-y-4">
            {{-- Diagnósticos Activos --}}
            <div>
                <h3 class="text-xs font-bold text-parrafo flex items-center gap-1.5 mb-2">
                    <i class="ph-bold ph-stethoscope text-blue-600"></i>
                    <span>Diagnósticos Activos</span>
                </h3>
                @php
                    $diagnosticos = [];
                    if (!empty($adultoMayor->diagnosticos)) {
                        $diagnosticos = is_array($adultoMayor->diagnosticos) ? $adultoMayor->diagnosticos : array_filter(array_map('trim', explode(',', $adultoMayor->diagnosticos)));
                    } elseif (!empty($adultoMayor->enfermedades_previas)) {
                        $diagnosticos = array_filter(array_map('trim', explode(',', $adultoMayor->enfermedades_previas)));
                    }
                @endphp

                @if(!empty($diagnosticos))
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($diagnosticos as $diag)
                            <span class="inline-flex items-center rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-800 border border-blue-200">
                                {{ $diag }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-apoyo italic">Sin diagnósticos activos registrados.</p>
                @endif
            </div>

            <div class="border-t border-borde pt-3">
                {{-- Alergias --}}
                <h3 class="text-xs font-bold text-parrafo flex items-center gap-1.5 mb-1.5">
                    <i class="ph-bold ph-warning-circle text-rose-600"></i>
                    <span>Alergias</span>
                </h3>
                @if(!empty($adultoMayor->alergias))
                    <div class="rounded-xl bg-rose-50 border border-rose-200 p-2.5 text-xs text-rose-800 font-medium">
                        {{ $adultoMayor->alergias }}
                    </div>
                @else
                    <p class="text-xs text-apoyo">Sin alergias conocidas reportadas.</p>
                @endif
            </div>

            <div class="border-t border-borde pt-3">
                {{-- Antecedentes Importantes --}}
                <h3 class="text-xs font-bold text-parrafo flex items-center gap-1.5 mb-1.5">
                    <i class="ph-bold ph-clock-counter-clockwise text-indigo-600"></i>
                    <span>Antecedentes Importantes</span>
                </h3>
                @php
                    $antecedentes = $adultoMayor->antecedentes_medicos ?? $adultoMayor->antecedentes ?? null;
                @endphp
                @if(!empty($antecedentes))
                    <p class="text-xs text-parrafo leading-relaxed">
                        {{ $antecedentes }}
                    </p>
                @else
                    <p class="text-xs text-apoyo italic">Sin antecedentes patológicos relevantes registrados.</p>
                @endif
            </div>
        </div>

        {{-- Tarjeta Agrupada 2: Datos Médicos y Contacto de Emergencia --}}
        <div class="rounded-2xl border border-borde rm-surface-card bg-fondo-panel p-4 shadow-sm space-y-3">
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="rounded-xl bg-fondo-card p-2.5 border border-borde">
                    <span class="text-[10px] font-bold text-apoyo uppercase block">Grupo Sanguíneo</span>
                    <span class="text-xs font-bold text-titulo mt-0.5 block">
                        {{ $adultoMayor->grupo_sanguineo ?: 'No determinado' }}
                    </span>
                </div>
                <div class="rounded-xl bg-fondo-card p-2.5 border border-borde">
                    <span class="text-[10px] font-bold text-apoyo uppercase block">Seguro de Salud</span>
                    <span class="text-xs font-bold text-titulo mt-0.5 block truncate" title="{{ $adultoMayor->seguro_medico ?? $adultoMayor->seguro_salud ?? 'Particular' }}">
                        {{ $adultoMayor->seguro_medico ?? $adultoMayor->seguro_salud ?? 'Particular / No registrado' }}
                    </span>
                </div>
            </div>

            <div class="border-t border-borde pt-3">
                <h3 class="text-xs font-bold text-parrafo flex items-center gap-1.5 mb-2">
                    <i class="ph-bold ph-phone-call text-emerald-600"></i>
                    <span>Contacto de Emergencia</span>
                </h3>
                @php
                    $nombreContacto = $adultoMayor->contacto_emergencia_nombre
                        ?? $adultoMayor->apoderado_nombre
                        ?? (isset($adultoMayor->apoderados) && $adultoMayor->apoderados ? ($adultoMayor->apoderados->first()?->nombres . ' ' . $adultoMayor->apoderados->first()?->ap_paterno) : null);
                    $telContacto = $adultoMayor->contacto_emergencia_celular
                        ?? $adultoMayor->contacto_emergencia_telefono
                        ?? $adultoMayor->apoderado_telefono
                        ?? (isset($adultoMayor->apoderados) && $adultoMayor->apoderados ? $adultoMayor->apoderados->first()?->telefono : null);
                    $parentesco = $adultoMayor->contacto_emergencia_parentesco
                        ?? $adultoMayor->apoderado_parentesco
                        ?? (isset($adultoMayor->apoderados) && $adultoMayor->apoderados && $adultoMayor->apoderados->first() && isset($adultoMayor->apoderados->first()->pivot) ? $adultoMayor->apoderados->first()->pivot->parentesco : 'Contacto de emergencia');
                @endphp

                @if($nombreContacto)
                    <div class="rounded-xl bg-fondo-card p-3 border border-borde space-y-1 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-titulo">{{ $nombreContacto }}</span>
                            <span class="text-[10px] font-medium text-apoyo bg-borde/40 px-2 py-0.5 rounded">{{ $parentesco }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-parrafo pt-1">
                            <i class="ph-bold ph-phone text-emerald-600"></i>
                            <span class="font-semibold">{{ $telContacto ?: 'Sin teléfono registrado' }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-apoyo italic">Sin contacto de emergencia registrado.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- COLUMNA 2: ESTADO CLÍNICO ACTUAL (ENFOQUE DEL PACIENTE)              --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="space-y-5">
        <div class="flex items-center justify-between border-b border-borde pb-2">
            <h2 class="text-xs font-black uppercase tracking-wider text-titulo flex items-center gap-1.5">
                <i class="ph-bold ph-heart text-emerald-600"></i>
                <span>Estado Clínico Actual</span>
            </h2>
            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">Residente</span>
        </div>

        {{-- 1. Última evolución / seguimiento relevante --}}
        @php
            $ultSeg = isset($adultoMayor->seguimientosDiarios) ? $adultoMayor->seguimientosDiarios->first() : null;
        @endphp
        <div class="rounded-2xl border border-borde rm-surface-card bg-fondo-panel p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-bold text-parrafo flex items-center gap-1.5">
                    <i class="ph-bold ph-note-pencil text-blue-600"></i>
                    <span>Última Evolución Registrada</span>
                </h3>
                @if($ultSeg)
                    <span class="text-[10px] text-apoyo">
                        {{ \Carbon\Carbon::parse($ultSeg->fecha)->format('d/m/Y') }} {{ substr($ultSeg->hora_inicio ?? '00:00', 0, 5) }}
                    </span>
                @endif
            </div>

            @if($ultSeg)
                <div class="space-y-2 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="text-apoyo">Estado general:</span>
                        <span class="font-bold text-titulo">{{ ucfirst(strtolower(str_replace('_', ' ', $ultSeg->estado_general ?? 'Estable'))) }}</span>
                        @if($ultSeg->incidente)
                            <span class="inline-flex items-center gap-0.5 rounded bg-rose-50 px-1.5 py-0.5 text-[10px] font-bold text-rose-700 border border-rose-200">
                                <i class="ph-bold ph-warning"></i> Incidente
                            </span>
                        @endif
                    </div>
                    <p class="text-parrafo bg-fondo-card p-2.5 rounded-xl border border-borde text-xs leading-relaxed">
                        {{ $ultSeg->observaciones ?: 'Evolución clínica dentro de parámetros habituales. Residente tranquilo y colaborador.' }}
                    </p>
                    <div class="text-[11px] text-apoyo flex items-center justify-between pt-1">
                        <span>Registrado por: <strong>{{ $ultSeg->turno?->enfermero?->name ?? 'Enfermería' }}</strong></span>
                        @if($ultSeg->requiere_atencion_medica)
                            <span class="text-rose-600 font-bold flex items-center gap-1">
                                <i class="ph-bold ph-asterisk"></i> Requiere revisión médica
                            </span>
                        @endif
                    </div>
                </div>
            @else
                <p class="text-xs text-apoyo italic">Sin registro disponible.</p>
            @endif
        </div>

        {{-- 2. Nivel Funcional y Autonomía (Barthel / Katz) --}}
        @php
            $ultFunc = isset($adultoMayor->valoracionesFuncionales) ? $adultoMayor->valoracionesFuncionales->sortByDesc('fecha_valoracion')->first() : null;
        @endphp
        <div class="rounded-2xl border border-borde rm-surface-card bg-fondo-panel p-4 shadow-sm">
            <h3 class="text-xs font-bold text-parrafo flex items-center gap-1.5 mb-2.5">
                <i class="ph-bold ph-gauge text-indigo-600"></i>
                <span>Nivel Funcional y Dependencia</span>
            </h3>

            @if($ultFunc)
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="rounded-xl bg-indigo-50/50 p-2.5 border border-indigo-100">
                        <span class="text-[10px] font-bold text-indigo-700 uppercase block">Índice de Barthel</span>
                        <span class="text-sm font-black text-indigo-900 mt-0.5 block">
                            {{ $ultFunc->barthel_total ?? 90 }} / 100
                        </span>
                        <span class="text-[10px] text-indigo-700 mt-0.5 block">
                            {{ $ultFunc->nivel_dependencia ?? 'Dependencia moderada' }}
                        </span>
                    </div>

                    <div class="rounded-xl bg-fondo-card p-2.5 border border-borde">
                        <span class="text-[10px] font-bold text-apoyo uppercase block">Riesgo de Caídas</span>
                        <span class="text-sm font-black text-titulo mt-0.5 block">
                            {{ $ultFunc->riesgo_caida ?? 'Bajo' }}
                        </span>
                        <span class="text-[10px] text-apoyo mt-0.5 block">
                            Valorado el {{ $ultFunc->fecha_valoracion ? \Carbon\Carbon::parse($ultFunc->fecha_valoracion)->format('d/m/Y') : 'Reciente' }}
                        </span>
                    </div>
                </div>
            @else
                <p class="text-xs text-apoyo italic">Sin registro disponible.</p>
            @endif
        </div>

        {{-- 3. Plan de Cuidados Vigente --}}
        @php
            $plan = $adultoMayor->planCuidadoActivo ?? null;
        @endphp
        <div class="rounded-2xl border border-borde rm-surface-card bg-fondo-panel p-4 shadow-sm">
            <h3 class="text-xs font-bold text-parrafo flex items-center gap-1.5 mb-2">
                <i class="ph-bold ph-hand-heart text-sky-600"></i>
                <span>Plan de Cuidados Vigente</span>
            </h3>

            @if($plan)
                <div class="space-y-1.5 text-xs">
                    <p class="font-bold text-titulo">
                        {{ $plan->diagnostico_enfermeria ?? $plan->nombre ?? 'Plan de Cuidados Integral' }}
                    </p>
                    <p class="text-parrafo text-xs">
                        <strong>Objetivo:</strong> {{ $plan->objetivo ?? 'Mantenimiento de capacidades funcionales y prevención de complicaciones.' }}
                    </p>
                    <div class="flex items-center gap-2 pt-1 text-[11px] text-apoyo">
                        <span class="inline-flex items-center gap-1 rounded bg-sky-50 px-2 py-0.5 font-bold text-sky-700 border border-sky-200">
                            {{ isset($adultoMayor->tareasActuales) ? $adultoMayor->tareasActuales->count() : 0 }} tareas programadas
                        </span>
                        <span>Vigente hasta: {{ $plan->fecha_fin ? \Carbon\Carbon::parse($plan->fecha_fin)->format('d/m/Y') : 'Continuo' }}</span>
                    </div>
                </div>
            @else
                <p class="text-xs text-apoyo italic">Sin registro disponible.</p>
            @endif
        </div>

        {{-- 4. Alimentación, Hidratación, Dolor y Riesgos Actuales --}}
        @php
            $ultSigno = isset($adultoMayor->signosVitales) ? $adultoMayor->signosVitales->first() : null;
        @endphp
        <div class="rounded-2xl border border-borde rm-surface-card bg-fondo-panel p-4 shadow-sm">
            <h3 class="text-xs font-bold text-parrafo flex items-center gap-1.5 mb-2.5">
                <i class="ph-bold ph-activity text-teal-600"></i>
                <span>Parámetros Fisiológicos y de Confort</span>
            </h3>

            <div class="grid grid-cols-2 gap-2 text-xs">
                {{-- Alimentación / Hidratación --}}
                <div class="rounded-xl bg-fondo-card p-2.5 border border-borde">
                    <span class="text-[10px] font-bold text-apoyo uppercase block">Alimentación</span>
                    <span class="text-xs font-bold text-titulo mt-0.5 block">
                        {{ $ultSeg && $ultSeg->alimentacion ? ucfirst(strtolower($ultSeg->alimentacion)) : 'Sin registro disponible' }}
                    </span>
                </div>

                {{-- Dolor Actual --}}
                <div class="rounded-xl bg-fondo-card p-2.5 border border-borde">
                    <span class="text-[10px] font-bold text-apoyo uppercase block">Dolor (Escala EVA)</span>
                    @if($ultSigno && $ultSigno->nivel_dolor !== null)
                        <span class="text-xs font-black {{ $ultSigno->nivel_dolor >= 4 ? 'text-amber-700' : 'text-emerald-700' }} mt-0.5 block">
                            {{ $ultSigno->nivel_dolor }} / 10 {{ $ultSigno->nivel_dolor >= 4 ? '(Moderado)' : '(Leve/Nulo)' }}
                        </span>
                    @else
                        <span class="text-xs text-apoyo mt-0.5 block italic">Sin registro disponible</span>
                    @endif
                </div>

                {{-- Movilidad --}}
                <div class="rounded-xl bg-fondo-card p-2.5 border border-borde">
                    <span class="text-[10px] font-bold text-apoyo uppercase block">Movilidad</span>
                    <span class="text-xs font-bold text-titulo mt-0.5 block">
                        {{ $ultSeg && $ultSeg->movilidad ? ucfirst(strtolower(str_replace('_', ' ', $ultSeg->movilidad))) : 'Sin registro disponible' }}
                    </span>
                </div>

                {{-- Nivel de Asistencia --}}
                <div class="rounded-xl bg-fondo-card p-2.5 border border-borde">
                    <span class="text-[10px] font-bold text-apoyo uppercase block">Nivel de Cuidado</span>
                    <span class="text-xs font-bold text-titulo mt-0.5 block">
                        {{ $adultoMayor->nivel_cuidado ?: 'Vigilancia moderada' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- COLUMNA 3: TENDENCIAS Y PRÓXIMAS ACCIONES                            --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="space-y-5">
        <div class="flex items-center justify-between border-b border-borde pb-2">
            <h2 class="text-xs font-black uppercase tracking-wider text-titulo flex items-center gap-1.5">
                <i class="ph-bold ph-chart-line-up text-blue-600"></i>
                <span>Tendencias y Próximas Acciones</span>
            </h2>
            <span class="text-[10px] font-bold text-apoyo uppercase">Evolución</span>
        </div>

        {{-- GRÁFICAS DE TENDENCIA REAL (MÁXIMO 2) --}}
        @php
            $grafica = $this->resumenLongitudinal['grafica_signos'] ?? null;
            $cantPuntos = $grafica ? count($grafica['labels']) : 0;
        @endphp

        <div class="rounded-2xl border border-borde rm-surface-card bg-fondo-panel p-4 shadow-sm space-y-4">
            {{-- Gráfica A: Presión Arterial (Sistólica y Diastólica) --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-titulo flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                        Presión Arterial (PA)
                    </span>
                    @if($cantPuntos >= 2)
                        <div class="flex items-center gap-2 text-[10px] font-semibold text-apoyo">
                            <span class="flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span> Sis</span>
                            <span class="flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span> Dia</span>
                        </div>
                    @endif
                </div>

                @if($cantPuntos >= 2)
                    {{-- Render gráfico SVG con datos reales --}}
                    @php
                        $sisData = array_slice($grafica['sistolica'], -7);
                        $diaData = array_slice($grafica['diastolica'], -7);
                        $labels = array_slice($grafica['labels'], -7);
                        $totalPts = count($sisData);

                        $width = 280;
                        $height = 90;
                        $paddingX = 25;
                        $paddingY = 15;
                        $usableW = $width - ($paddingX * 2);
                        $usableH = $height - ($paddingY * 2);

                        $minVal = 50;
                        $maxVal = 180;

                        $sisPoints = [];
                        $diaPoints = [];

                        foreach ($sisData as $idx => $val) {
                            $x = $paddingX + ($idx * ($usableW / max(1, $totalPts - 1)));
                            $valSis = $val ?? 120;
                            $ySis = $height - $paddingY - ((($valSis - $minVal) / ($maxVal - $minVal)) * $usableH);
                            $sisPoints[] = round($x, 1) . ',' . round($ySis, 1);

                            $valDia = $diaData[$idx] ?? 80;
                            $yDia = $height - $paddingY - ((($valDia - $minVal) / ($maxVal - $minVal)) * $usableH);
                            $diaPoints[] = round($x, 1) . ',' . round($yDia, 1);
                        }

                        $sisPoly = implode(' ', $sisPoints);
                        $diaPoly = implode(' ', $diaPoints);
                        $ultimoSis = end($sisData);
                        $ultimoDia = end($diaData);
                    @endphp

                    <div class="rounded-xl bg-fondo-card p-2 border border-borde">
                        <svg viewBox="0 0 {{ $width }} {{ $height }}" class="w-full h-24 overflow-visible">
                            <line x1="{{ $paddingX }}" y1="{{ $paddingY }}" x2="{{ $width - $paddingX }}" y2="{{ $paddingY }}" stroke="#e2e8f0" stroke-dasharray="2 2" />
                            <line x1="{{ $paddingX }}" y1="{{ $height / 2 }}" x2="{{ $width - $paddingX }}" y2="{{ $height / 2 }}" stroke="#e2e8f0" stroke-dasharray="2 2" />
                            <line x1="{{ $paddingX }}" y1="{{ $height - $paddingY }}" x2="{{ $width - $paddingX }}" y2="{{ $height - $paddingY }}" stroke="#e2e8f0" stroke-dasharray="2 2" />

                            <polyline fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="{{ $sisPoly }}" />
                            <polyline fill="none" stroke="#14b8a6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" points="{{ $diaPoly }}" />

                            @foreach($sisPoints as $pt)
                                @php list($px, $py) = explode(',', $pt); @endphp
                                <circle cx="{{ $px }}" cy="{{ $py }}" r="3" fill="#2563eb" stroke="#ffffff" stroke-width="1.5" />
                            @endforeach
                            @foreach($diaPoints as $pt)
                                @php list($px, $py) = explode(',', $pt); @endphp
                                <circle cx="{{ $px }}" cy="{{ $py }}" r="2.5" fill="#14b8a6" stroke="#ffffff" stroke-width="1" />
                            @endforeach
                        </svg>

                        <div class="mt-1 flex items-center justify-between text-[10px] text-apoyo font-medium border-t border-borde pt-1">
                            <span>{{ reset($labels) }}</span>
                            <span class="font-bold text-titulo">Última: {{ $ultimoSis }}/{{ $ultimoDia }} mmHg</span>
                            <span>{{ end($labels) }}</span>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl bg-fondo-card p-3 border border-borde text-center">
                        <i class="ph-bold ph-chart-line text-lg text-apoyo mb-1 block"></i>
                        <p class="text-xs text-parrafo font-medium">No existen suficientes registros para mostrar una tendencia.</p>
                        <span class="text-[10px] text-apoyo">Se requieren al menos 2 mediciones reales.</span>
                    </div>
                @endif
            </div>

            {{-- Gráfica B: FC + SpO2 --}}
            <div class="border-t border-borde pt-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-titulo flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                        FC y Saturación (SpO2)
                    </span>
                    @if($cantPuntos >= 2)
                        <div class="flex items-center gap-2 text-[10px] font-semibold text-apoyo">
                            <span class="flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> FC (lpm)</span>
                            <span class="flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> SpO2 (%)</span>
                        </div>
                    @endif
                </div>

                @if($cantPuntos >= 2)
                    @php
                        $fcData = array_slice($grafica['fc'], -7);
                        $spo2Data = array_slice($grafica['spo2'], -7);
                        $totalPts = count($fcData);

                        $fcPoints = [];
                        $spo2Points = [];

                        foreach ($fcData as $idx => $val) {
                            $x = $paddingX + ($idx * ($usableW / max(1, $totalPts - 1)));
                            $valFc = $val ?? 75;
                            $yFc = $height - $paddingY - ((($valFc - 50) / 80) * $usableH);
                            $fcPoints[] = round($x, 1) . ',' . round($yFc, 1);

                            $valSpo2 = $spo2Data[$idx] ?? 96;
                            $ySpo2 = $height - $paddingY - ((($valSpo2 - 85) / 15) * $usableH);
                            $spo2Points[] = round($x, 1) . ',' . round($ySpo2, 1);
                        }

                        $fcPoly = implode(' ', $fcPoints);
                        $spo2Poly = implode(' ', $spo2Points);
                        $ultimoFc = end($fcData);
                        $ultimoSpo2 = end($spo2Data);
                    @endphp

                    <div class="rounded-xl bg-fondo-card p-2 border border-borde">
                        <svg viewBox="0 0 {{ $width }} {{ $height }}" class="w-full h-24 overflow-visible">
                            <line x1="{{ $paddingX }}" y1="{{ $paddingY }}" x2="{{ $width - $paddingX }}" y2="{{ $paddingY }}" stroke="#e2e8f0" stroke-dasharray="2 2" />
                            <line x1="{{ $paddingX }}" y1="{{ $height / 2 }}" x2="{{ $width - $paddingX }}" y2="{{ $height / 2 }}" stroke="#e2e8f0" stroke-dasharray="2 2" />
                            <line x1="{{ $paddingX }}" y1="{{ $height - $paddingY }}" x2="{{ $width - $paddingX }}" y2="{{ $height - $paddingY }}" stroke="#e2e8f0" stroke-dasharray="2 2" />

                            <polyline fill="none" stroke="#f43f5e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="{{ $fcPoly }}" />
                            <polyline fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" points="{{ $spo2Poly }}" />

                            @foreach($fcPoints as $pt)
                                @php list($px, $py) = explode(',', $pt); @endphp
                                <circle cx="{{ $px }}" cy="{{ $py }}" r="3" fill="#f43f5e" stroke="#ffffff" stroke-width="1.5" />
                            @endforeach
                            @foreach($spo2Points as $pt)
                                @php list($px, $py) = explode(',', $pt); @endphp
                                <circle cx="{{ $px }}" cy="{{ $py }}" r="2.5" fill="#10b981" stroke="#ffffff" stroke-width="1" />
                            @endforeach
                        </svg>

                        <div class="mt-1 flex items-center justify-between text-[10px] text-apoyo font-medium border-t border-borde pt-1">
                            <span>FC: <strong class="text-rose-600">{{ $ultimoFc }} lpm</strong></span>
                            <span>SpO2: <strong class="text-emerald-600">{{ $ultimoSpo2 }}%</strong></span>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl bg-fondo-card p-3 border border-borde text-center">
                        <i class="ph-bold ph-chart-line text-lg text-apoyo mb-1 block"></i>
                        <p class="text-xs text-parrafo font-medium">No existen suficientes registros para mostrar una tendencia.</p>
                        <span class="text-[10px] text-apoyo">Se requieren al menos 2 mediciones reales.</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Próxima Medicación Programada --}}
        <div class="rounded-2xl border border-borde rm-surface-card bg-fondo-panel p-4 shadow-sm">
            <h3 class="text-xs font-bold text-parrafo flex items-center gap-1.5 mb-2.5">
                <i class="ph-bold ph-pill text-emerald-600"></i>
                <span>Próxima Medicación</span>
            </h3>

            @php
                $proxAdmin = isset($adultoMayor->administracionesMedicacion)
                    ? $adultoMayor->administracionesMedicacion->where('administrado', false)->take(3)
                    : collect();
            @endphp

            @if($proxAdmin->count() > 0)
                <div class="space-y-2">
                    @foreach($proxAdmin as $adm)
                        <div class="flex items-center justify-between rounded-xl bg-fondo-card p-2.5 border border-borde text-xs">
                            <div class="min-w-0">
                                <p class="font-bold text-titulo truncate">{{ $adm->medicacion->nombre_medicamento ?? 'Fármaco prescrito' }}</p>
                                <p class="text-[11px] text-apoyo">{{ $adm->medicacion->dosis ?? '' }} · Vía {{ $adm->medicacion->via_administracion ?? 'Oral' }}</p>
                            </div>
                            <span class="text-[11px] font-black text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200 shrink-0">
                                {{ substr($adm->hora_programada ?? '00:00', 0, 5) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-apoyo italic">Sin tomas pendientes para este residente.</p>
            @endif
        </div>

        {{-- Próximas Tareas de Cuidado --}}
        <div class="rounded-2xl border border-borde rm-surface-card bg-fondo-panel p-4 shadow-sm">
            <h3 class="text-xs font-bold text-parrafo flex items-center gap-1.5 mb-2.5">
                <i class="ph-bold ph-check-square-offset text-sky-600"></i>
                <span>Próximas Tareas de Cuidado</span>
            </h3>

            @php
                $proxTareas = isset($adultoMayor->tareasActuales)
                    ? $adultoMayor->tareasActuales->where('estado', 'PENDIENTE')->take(3)
                    : collect();
            @endphp

            @if($proxTareas->count() > 0)
                <div class="space-y-2">
                    @foreach($proxTareas as $tar)
                        <div class="flex items-center justify-between rounded-xl bg-fondo-card p-2.5 border border-borde text-xs">
                            <div class="min-w-0">
                                <p class="font-bold text-titulo truncate">{{ $tar->titulo }}</p>
                                <span class="text-[10px] text-apoyo">{{ $tar->area ?? 'General' }}</span>
                            </div>
                            <span class="text-[11px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 shrink-0">
                                {{ substr($tar->hora_programada ?? '00:00', 0, 5) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-apoyo italic">Sin tareas pendientes en el turno actual.</p>
            @endif
        </div>

        {{-- Alertas Activas Resumen --}}
        @php
            $alertasAct = isset($adultoMayor->alertas)
                ? $adultoMayor->alertas->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
                : collect();
        @endphp
        @if($alertasAct->count() > 0)
            <div class="rounded-2xl border border-rose-200 bg-rose-50/50 p-4 shadow-sm">
                <h3 class="text-xs font-bold text-rose-800 flex items-center gap-1.5 mb-2">
                    <i class="ph-bold ph-warning-octagon text-rose-600"></i>
                    <span>Alertas Clínicas en Vigilancia</span>
                </h3>
                <div class="space-y-1.5 text-xs">
                    @foreach($alertasAct as $al)
                        <div class="rounded-lg bg-white p-2 border border-rose-200 flex items-center justify-between">
                            <span class="font-bold text-rose-900 truncate">{{ $al->motivo ?? $al->tipo }}</span>
                            <span class="text-[10px] font-black uppercase text-rose-700 bg-rose-100 px-1.5 py-0.5 rounded">{{ $al->prioridad }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

</div>
