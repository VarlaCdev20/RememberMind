{{-- TAB 2: SIGNOS VITALES (HERO CARD + GRÁFICA INTERACTIVA + HISTORIAL CRONOLÓGICO) --}}
<div class="space-y-6">
    {{-- Cabecera del Módulo de Signos Vitales --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-[var(--rm-border)] pb-3">
        <div>
            <h2 class="text-sm font-bold text-[var(--rm-text-title)] flex items-center gap-2">
                <i class="ph-bold ph-heartbeat text-blue-600 text-base"></i>
                <span>Monitoreo Hemodinámico y Signos Vitales</span>
            </h2>
            <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">Control evolutivo de constantes vitales y parámetros fisiológicos.</p>
        </div>

        {{-- ÚNICO BOTÓN DEL TAB (ABRE EL MODAL CENTRALIZADO) --}}
        <button type="button"
                wire:click="abrirModalSignos"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition self-start sm:self-auto">
            <i class="ph-bold ph-plus text-sm"></i>
            <span>Registrar signos</span>
        </button>
    </div>

    {{-- HERO CARD: ÚLTIMO CONTROL DESTACADO --}}
    @php
        $ultimo = $this->resumenLongitudinal['grafica_signos']['ultimo'] ?? $adultoMayor->signosVitales->first();
    @endphp

    @if($ultimo)
        <div class="rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-[var(--rm-border)] pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 border border-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Último Control Hemodinámico
                    </span>
                    <span class="text-xs text-[var(--rm-text-body)] font-medium">
                        {{ $ultimo->fecha ? \Carbon\Carbon::parse($ultimo->fecha)->format('d/m/Y') : '' }} · {{ substr($ultimo->hora ?? '00:00', 0, 5) }}
                    </span>
                </div>
                <div class="text-xs text-[var(--rm-text-muted)]">
                    Registrado por: <strong class="text-[var(--rm-text-body)]">{{ $ultimo->profesional->name ?? 'Equipo de Enfermería' }}</strong>
                </div>
            </div>

            {{-- Grid de 8 Variables Fisiológicas --}}
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
                {{-- 1. PA --}}
                <div class="rounded-2xl bg-[var(--rm-surface-alt)] p-3 border border-[var(--rm-border)] text-center">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Presión Art.</span>
                    <span class="text-base font-black text-[var(--rm-text-title)] mt-1 block">
                        {{ $ultimo->presion_arterial ?: '--' }}
                    </span>
                    <span class="text-[10px] text-[var(--rm-text-muted)]">mmHg</span>
                </div>

                {{-- 2. FC --}}
                <div class="rounded-2xl bg-[var(--rm-surface-alt)] p-3 border border-[var(--rm-border)] text-center">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Frec. Cardaca</span>
                    <span class="text-base font-black text-[var(--rm-text-title)] mt-1 block">
                        {{ $ultimo->frecuencia_cardiaca ?: '--' }}
                    </span>
                    <span class="text-[10px] text-[var(--rm-text-muted)]">lpm</span>
                </div>

                {{-- 3. FR --}}
                <div class="rounded-2xl bg-[var(--rm-surface-alt)] p-3 border border-[var(--rm-border)] text-center">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Frec. Resp.</span>
                    <span class="text-base font-black text-[var(--rm-text-title)] mt-1 block">
                        {{ $ultimo->frecuencia_respiratoria ?: '--' }}
                    </span>
                    <span class="text-[10px] text-[var(--rm-text-muted)]">rpm</span>
                </div>

                {{-- 4. Temperatura --}}
                <div class="rounded-2xl bg-[var(--rm-surface-alt)] p-3 border border-[var(--rm-border)] text-center">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Temperatura</span>
                    <span class="text-base font-black text-[var(--rm-text-title)] mt-1 block">
                        {{ $ultimo->temperatura ? $ultimo->temperatura . '°' : '--' }}
                    </span>
                    <span class="text-[10px] text-[var(--rm-text-muted)]">°C</span>
                </div>

                {{-- 5. SpO2 --}}
                <div class="rounded-2xl bg-[var(--rm-surface-alt)] p-3 border border-[var(--rm-border)] text-center">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Saturación O2</span>
                    <span class="text-base font-black text-[var(--rm-text-title)] mt-1 block">
                        {{ $ultimo->saturacion ? $ultimo->saturacion . '%' : '--' }}
                    </span>
                    <span class="text-[10px] text-[var(--rm-text-muted)]">SpO2</span>
                </div>

                {{-- 6. Glucosa --}}
                <div class="rounded-2xl bg-[var(--rm-surface-alt)] p-3 border border-[var(--rm-border)] text-center">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Glucemia</span>
                    <span class="text-base font-black text-[var(--rm-text-title)] mt-1 block">
                        {{ $ultimo->glucosa ?: '--' }}
                    </span>
                    <span class="text-[10px] text-[var(--rm-text-muted)]">mg/dL</span>
                </div>

                {{-- 7. Peso --}}
                <div class="rounded-2xl bg-[var(--rm-surface-alt)] p-3 border border-[var(--rm-border)] text-center">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Peso Corporal</span>
                    <span class="text-base font-black text-[var(--rm-text-title)] mt-1 block">
                        {{ $ultimo->peso ? $ultimo->peso . ' kg' : '--' }}
                    </span>
                    <span class="text-[10px] text-[var(--rm-text-muted)]">kg</span>
                </div>

                {{-- 8. Dolor --}}
                <div class="rounded-2xl bg-[var(--rm-surface-alt)] p-3 border border-[var(--rm-border)] text-center">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Dolor (EVA)</span>
                    <span class="text-base font-black {{ ($ultimo->nivel_dolor ?? 0) >= 4 ? 'text-amber-700' : 'text-emerald-700' }} mt-1 block">
                        {{ $ultimo->nivel_dolor !== null ? $ultimo->nivel_dolor . '/10' : '--' }}
                    </span>
                    <span class="text-[10px] text-[var(--rm-text-muted)]">Escala 0-10</span>
                </div>
            </div>

            @if($ultimo->observaciones)
                <div class="mt-3 rounded-xl bg-[var(--rm-surface-alt)] p-3 border border-[var(--rm-border)] text-xs text-[var(--rm-text-body)]">
                    <strong>Observaciones:</strong> {{ $ultimo->observaciones }}
                </div>
            @endif
        </div>
    @else
        <div class="rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-8 text-center">
            <i class="ph-bold ph-heartbeat text-3xl text-[var(--rm-text-muted)] mb-2 block"></i>
            <p class="text-sm font-bold text-[var(--rm-text-title)]">No existen controles de signos vitales registrados.</p>
            <p class="text-xs text-[var(--rm-text-muted)] mt-1">Utilice el botón superior para ingresar la primera medición del residente.</p>
        </div>
    @endif

    {{-- SECCIÓN DE GRÁFICA EVOLUTIVA SELECCIONABLE CON DATOS REALES --}}
    @php
        $grafica = $this->resumenLongitudinal['grafica_signos'] ?? null;
        $totalPuntos = $grafica ? count($grafica['labels']) : 0;
    @endphp

    <div class="rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[var(--rm-border)] pb-3">
            <div>
                <h3 class="text-xs font-bold text-[var(--rm-text-title)] flex items-center gap-1.5">
                    <i class="ph-bold ph-chart-line-up text-blue-600"></i>
                    <span>Evolución Temporal de Parámetros Clínicos</span>
                </h3>
                <p class="text-[11px] text-[var(--rm-text-muted)]">Visualice la trayectoria hemodinámica según la métrica seleccionada.</p>
            </div>

            {{-- Selectores de Métrica --}}
            <div class="flex flex-wrap items-center gap-1.5">
                @foreach([
                    'PA' => 'Presión Arterial',
                    'FC' => 'Frec. Cardaca',
                    'TEMP' => 'Temperatura',
                    'SPO2' => 'SpO2',
                    'GLUCOSA' => 'Glucosa'
                ] as $metKey => $metLabel)
                    <button type="button"
                            wire:click="setMetricaSignos('{{ $metKey }}')"
                            class="rounded-lg px-2.5 py-1 text-xs font-bold transition {{ $metricaSignosSeleccionada === $metKey ? 'bg-blue-600 text-white shadow-sm' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-body)] hover:bg-borde' }}">
                        {{ $metLabel }}
                    </button>
                @endforeach
            </div>
        </div>

        @if($totalPuntos >= 2)
            @php
                $labels = array_slice($grafica['labels'], -10);
                $ptsCount = count($labels);
                $svgW = 600;
                $svgH = 160;
                $padX = 40;
                $padY = 25;
                $drawW = $svgW - ($padX * 2);
                $drawH = $svgH - ($padY * 2);

                $ptsA = [];
                $ptsB = [];
                $unidades = '';
                $leyendaA = '';
                $leyendaB = '';
                $colorA = '#2563eb';
                $colorB = '#14b8a6';

                if ($metricaSignosSeleccionada === 'PA') {
                    $unidades = 'mmHg';
                    $leyendaA = 'Sistólica';
                    $leyendaB = 'Diastólica';
                    $minVal = 50;
                    $maxVal = 190;
                    $dataA = array_slice($grafica['sistolica'], -10);
                    $dataB = array_slice($grafica['diastolica'], -10);
                } elseif ($metricaSignosSeleccionada === 'FC') {
                    $unidades = 'lpm';
                    $leyendaA = 'Frecuencia Cardaca';
                    $minVal = 40;
                    $maxVal = 140;
                    $dataA = array_slice($grafica['fc'], -10);
                    $dataB = [];
                    $colorA = '#f43f5e';
                } elseif ($metricaSignosSeleccionada === 'TEMP') {
                    $unidades = '°C';
                    $leyendaA = 'Temperatura';
                    $minVal = 35;
                    $maxVal = 40;
                    $dataA = array_slice($grafica['temp'], -10);
                    $dataB = [];
                    $colorA = '#d97706';
                } elseif ($metricaSignosSeleccionada === 'SPO2') {
                    $unidades = '%';
                    $leyendaA = 'Saturación SpO2';
                    $minVal = 80;
                    $maxVal = 100;
                    $dataA = array_slice($grafica['spo2'], -10);
                    $dataB = [];
                    $colorA = '#10b981';
                } else { // GLUCOSA
                    $unidades = 'mg/dL';
                    $leyendaA = 'Glucosa';
                    $minVal = 60;
                    $maxVal = 220;
                    $dataA = array_slice($grafica['glucosa'], -10);
                    $dataB = [];
                    $colorA = '#8b5cf6';
                }

                foreach ($dataA as $i => $v) {
                    $valA = $v ?? (($minVal + $maxVal) / 2);
                    $x = $padX + ($i * ($drawW / max(1, $ptsCount - 1)));
                    $y = $svgH - $padY - ((($valA - $minVal) / max(1, $maxVal - $minVal)) * $drawH);
                    $ptsA[] = round($x, 1) . ',' . round($y, 1);
                }

                if (!empty($dataB)) {
                    foreach ($dataB as $i => $v) {
                        $valB = $v ?? (($minVal + $maxVal) / 2);
                        $x = $padX + ($i * ($drawW / max(1, $ptsCount - 1)));
                        $y = $svgH - $padY - ((($valB - $minVal) / max(1, $maxVal - $minVal)) * $drawH);
                        $ptsB[] = round($x, 1) . ',' . round($y, 1);
                    }
                }

                $polyA = implode(' ', $ptsA);
                $polyB = implode(' ', $ptsB);
            @endphp

            <div class="rounded-2xl bg-[var(--rm-surface-alt)] p-4 border border-[var(--rm-border)]">
                <div class="flex items-center justify-between mb-2 text-xs">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1.5 font-bold" style="color: {{ $colorA }};">
                            <span class="h-2 w-2 rounded-full" style="background-color: {{ $colorA }};"></span>
                            {{ $leyendaA }} ({{ $unidades }})
                        </span>
                        @if($leyendaB)
                            <span class="flex items-center gap-1.5 font-bold" style="color: {{ $colorB }};">
                                <span class="h-2 w-2 rounded-full" style="background-color: {{ $colorB }};"></span>
                                {{ $leyendaB }} ({{ $unidades }})
                            </span>
                        @endif
                    </div>
                    <span class="text-[11px] text-[var(--rm-text-muted)] font-medium">Últimas {{ $ptsCount }} tomas registradas</span>
                </div>

                <svg viewBox="0 0 {{ $svgW }} {{ $svgH }}" class="w-full h-44 overflow-visible">
                    {{-- Cuadrícula horizontal --}}
                    <line x1="{{ $padX }}" y1="{{ $padY }}" x2="{{ $svgW - $padX }}" y2="{{ $padY }}" stroke="#e2e8f0" stroke-dasharray="3 3" />
                    <line x1="{{ $padX }}" y1="{{ $svgH / 2 }}" x2="{{ $svgW - $padX }}" y2="{{ $svgH / 2 }}" stroke="#e2e8f0" stroke-dasharray="3 3" />
                    <line x1="{{ $padX }}" y1="{{ $svgH - $padY }}" x2="{{ $svgW - $padX }}" y2="{{ $svgH - $padY }}" stroke="#e2e8f0" stroke-dasharray="3 3" />

                    {{-- Polilíneas --}}
                    <polyline fill="none" stroke="{{ $colorA }}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="{{ $polyA }}" />
                    @if($polyB)
                        <polyline fill="none" stroke="{{ $colorB }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" points="{{ $polyB }}" />
                    @endif

                    {{-- Puntos --}}
                    @foreach($ptsA as $idx => $pt)
                        @php list($px, $py) = explode(',', $pt); @endphp
                        <circle cx="{{ $px }}" cy="{{ $py }}" r="4" fill="{{ $colorA }}" stroke="#ffffff" stroke-width="2" />
                    @endforeach

                    @if(!empty($ptsB))
                        @foreach($ptsB as $idx => $pt)
                            @php list($px, $py) = explode(',', $pt); @endphp
                            <circle cx="{{ $px }}" cy="{{ $py }}" r="3" fill="{{ $colorB }}" stroke="#ffffff" stroke-width="1.5" />
                        @endforeach
                    @endif
                </svg>

                <div class="mt-2 flex items-center justify-between text-[11px] text-[var(--rm-text-muted)] font-medium border-t border-[var(--rm-border)] pt-2">
                    <span>{{ reset($labels) }}</span>
                    <span>Evolución cronológica</span>
                    <span>{{ end($labels) }}</span>
                </div>
            </div>
        @else
            <div class="rounded-2xl bg-[var(--rm-surface-alt)] p-6 border border-[var(--rm-border)] text-center">
                <i class="ph-bold ph-chart-line text-2xl text-[var(--rm-text-muted)] mb-2 block"></i>
                <p class="text-xs font-bold text-[var(--rm-text-body)]">No existen suficientes registros para mostrar una tendencia.</p>
                <p class="text-[11px] text-[var(--rm-text-muted)] mt-1">Se requieren al menos 2 mediciones reales para graficar la evolución de {{ $metricaSignosSeleccionada }}.</p>
            </div>
        @endif
    </div>

    {{-- HISTORIAL CRONOLÓGICO COMPLETO --}}
    <div class="rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm space-y-3">
        <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
            <h3 class="text-xs font-bold text-[var(--rm-text-title)] flex items-center gap-1.5">
                <i class="ph-bold ph-clock-counter-clockwise text-blue-600"></i>
                <span>Historial Cronológico de Mediciones</span>
            </h3>
            <span class="text-xs text-[var(--rm-text-muted)] font-medium">Total: {{ $adultoMayor->signosVitales->count() }} registros</span>
        </div>

        <div class="rm-table-container">
            <table class="rm-table text-xs">
                <thead>
                    <tr class="border-b border-[var(--rm-border)] text-[10px] font-black uppercase text-[var(--rm-text-muted)]">
                        <th class="py-2.5 px-3">Fecha/Hora</th>
                        <th class="py-2.5 px-3">PA (mmHg)</th>
                        <th class="py-2.5 px-3">FC (lpm)</th>
                        <th class="py-2.5 px-3">FR (rpm)</th>
                        <th class="py-2.5 px-3">Temp (°C)</th>
                        <th class="py-2.5 px-3">SpO2 (%)</th>
                        <th class="py-2.5 px-3">Glucosa</th>
                        <th class="py-2.5 px-3">Dolor</th>
                        <th class="py-2.5 px-3">Observaciones</th>
                        <th class="py-2.5 px-3">Responsable</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde/50">
                    @forelse($adultoMayor->signosVitales as $sig)
                        <tr class="hover:bg-[var(--rm-surface-alt)]/60 transition">
                            <td class="py-2.5 px-3 whitespace-nowrap font-medium text-[var(--rm-text-title)]">
                                {{ $sig->fecha ? \Carbon\Carbon::parse($sig->fecha)->format('d/m/Y') : '' }}
                                <span class="text-[var(--rm-text-muted)] block text-[10px]">{{ substr($sig->hora ?? '00:00', 0, 5) }}</span>
                            </td>
                            <td class="py-2.5 px-3 font-bold text-[var(--rm-text-title)] whitespace-nowrap">{{ $sig->presion_arterial ?: '--' }}</td>
                            <td class="py-2.5 px-3 whitespace-nowrap">{{ $sig->frecuencia_cardiaca ?: '--' }}</td>
                            <td class="py-2.5 px-3 whitespace-nowrap">{{ $sig->frecuencia_respiratoria ?: '--' }}</td>
                            <td class="py-2.5 px-3 whitespace-nowrap">{{ $sig->temperatura ? $sig->temperatura . '°' : '--' }}</td>
                            <td class="py-2.5 px-3 whitespace-nowrap">{{ $sig->saturacion ? $sig->saturacion . '%' : '--' }}</td>
                            <td class="py-2.5 px-3 whitespace-nowrap">{{ $sig->glucosa ?: '--' }}</td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                @if($sig->nivel_dolor !== null)
                                    <span class="font-bold {{ $sig->nivel_dolor >= 4 ? 'text-amber-700' : 'text-emerald-700' }}">
                                        {{ $sig->nivel_dolor }}/10
                                    </span>
                                @else
                                    <span class="text-[var(--rm-text-muted)]">--</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 max-w-[200px] truncate text-[var(--rm-text-body)]" title="{{ $sig->observaciones }}">
                                {{ $sig->observaciones ?: '--' }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap text-[var(--rm-text-muted)] font-medium">
                                {{ $sig->profesional->name ?? 'Enfermería' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-6 text-center text-xs text-[var(--rm-text-muted)] italic">
                                No se registran mediciones previas para este residente.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
