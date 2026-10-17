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
            <div class="rm-clinical-chart-tabs flex items-center gap-1 p-1 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)] text-xs">
                <button type="button"
                        @click="metrica = 'pa'" :aria-pressed="metrica === 'pa'"
                        :class="metrica === 'pa' ? 'bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] shadow-xs font-bold' : 'text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] font-medium'"
                        class="flex-1 py-1 text-[10.5px] rounded-lg transition text-center truncate">
                    PA
                </button>
                <button type="button"
                        @click="metrica = 'fc'" :aria-pressed="metrica === 'fc'"
                        :class="metrica === 'fc' ? 'bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] shadow-xs font-bold' : 'text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] font-medium'"
                        class="flex-1 py-1 text-[10.5px] rounded-lg transition text-center truncate">
                    FC
                </button>
                <button type="button"
                        @click="metrica = 'spo2'" :aria-pressed="metrica === 'spo2'"
                        :class="metrica === 'spo2' ? 'bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] shadow-xs font-bold' : 'text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] font-medium'"
                        class="flex-1 py-1 text-[10.5px] rounded-lg transition text-center truncate">
                    SpO₂
                </button>
                <button type="button"
                        @click="metrica = 'temp'" :aria-pressed="metrica === 'temp'"
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
                                <span class="font-bold text-[var(--rm-danger-strong)]">bpm</span>
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
                                <span class="font-bold text-[var(--rm-success-strong)]">%</span>
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
                                <span class="font-bold text-[var(--rm-warning-strong)]">°C</span>
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
                            <span>Último registro: <strong>{{ $ultimoSigno->fecha_hora?->format('d/m/Y H:i') ?? (($ultimoSigno->fecha ? \Carbon\Carbon::parse($ultimoSigno->fecha)->format('d/m/Y') : 'Fecha no registrada').' '.substr((string) ($ultimoSigno->hora ?? ''), 0, 5)) }}</strong></span>
                        </div>
                        <div class="grid grid-cols-4 gap-1.5 text-center pt-0.5">
                            <div class="p-1 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)]">
                                <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">PA</span>
                                <span class="text-xs font-bold text-[var(--rm-text-title)] block">{{ $ultimoSigno->presion_arterial ?: ($ultimoSigno->presion_sistolica ? $ultimoSigno->presion_sistolica . '/' . $ultimoSigno->presion_diastolica : '—') }}</span>
                            </div>
                            <div class="p-1 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)]">
                                <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">FC</span>
                                <span class="block text-xs font-bold text-[var(--rm-danger-strong)]">{{ $ultimoSigno->frecuencia_cardiaca ?: '—' }}</span>
                            </div>
                            <div class="p-1 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)]">
                                <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">SpO₂</span>
                                <span class="block text-xs font-bold text-[var(--rm-success-strong)]">{{ ($ultimoSigno->saturacion_oxigeno ?? $ultimoSigno->saturacion) ? ($ultimoSigno->saturacion_oxigeno ?? $ultimoSigno->saturacion) . '%' : '—' }}</span>
                            </div>
                            <div class="p-1 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)]">
                                <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Temp</span>
                                <span class="block text-xs font-bold text-[var(--rm-warning-strong)]">{{ $ultimoSigno->temperatura ? $ultimoSigno->temperatura . ' °C' : '—' }}</span>
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
                @foreach($proxTareas as $tarea)
                    <div class="rm-action-item">
                        <div class="flex items-center gap-2 min-w-0 pr-2">
                            <i class="ph-bold ph-hand-heart text-[var(--rm-info-strong)] text-base shrink-0"></i>
                            <div class="truncate">
                                <span class="font-bold text-[var(--rm-text-title)] block truncate">{{ $tarea->intervencion?->nombre ?: 'Intervención de cuidado' }}</span>
                                <span class="text-[10px] text-[var(--rm-text-muted)] block">{{ $tarea->fecha_hora_programada->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                        <x-ui.status-badge :estado="$tarea->estado" class="shrink-0" />
                    </div>
                @endforeach

                @foreach($proxAdmin as $administracion)
                    <div class="rm-action-item">
                        <div class="flex items-center gap-2 min-w-0 pr-2">
                            <i class="ph-bold ph-pill text-[var(--rm-success-strong)] text-base shrink-0"></i>
                            <div class="truncate">
                                <span class="font-bold text-[var(--rm-text-title)] block truncate">{{ $administracion->medicacion?->medicamento?->nombre ?: 'Medicación prescrita' }}</span>
                                <span class="text-[10px] text-[var(--rm-text-muted)] block">{{ $administracion->fecha_hora_programada->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                        <x-ui.status-badge :estado="$administracion->estado ?: 'PROGRAMADA'" class="shrink-0" />
                    </div>
                @endforeach

                @foreach($proxActividades as $actividad)
                    <div class="rm-action-item">
                        <div class="flex items-center gap-2 min-w-0 pr-2">
                            <i class="ph-bold ph-calendar-check text-[var(--rm-action-primary)] text-base shrink-0"></i>
                            <div class="truncate">
                                <span class="font-bold text-[var(--rm-text-title)] block truncate">{{ $actividad->nombre }}</span>
                                <span class="text-[10px] text-[var(--rm-text-muted)] block">{{ $actividad->fecha_hora->format('d/m/Y H:i') }}{{ $actividad->lugar ? ' · '.$actividad->lugar : '' }}</span>
                            </div>
                        </div>
                        <x-ui.status-badge :estado="$actividad->estado" class="shrink-0" />
                    </div>
                @endforeach

                @if($proxTareas->isEmpty() && $proxAdmin->isEmpty() && $proxActividades->isEmpty())
                    <p class="rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-surface-alt)] p-3 text-center text-[var(--rm-text-muted)]">
                        No existen acciones futuras programadas.
                    </p>
                @endif
            </div>
        </div>

        {{-- 3. NOTAS RELEVANTES --}}
        <div class="rm-clinical-card space-y-3">
            <div class="rm-clinical-card__header">
                <h3 class="rm-clinical-card__title">
                    <i class="ph-bold ph-bookmarks text-[var(--rm-action-primary)] text-sm"></i>
                    <span>Notas relevantes</span>
                </h3>
                @if(!$consultaEnfermeria)
<button type="button"
                        wire:click="abrirModalSeguimiento"
                        class="rm-btn-secondary h-7 px-2.5 text-[11px] rounded-lg font-semibold inline-flex items-center gap-1 transition">
                    <i class="ph-bold ph-plus text-xs"></i>
                    <span>Añadir nota</span>
                </button>
@endif
            </div>

            <div class="space-y-2 text-xs">
                @forelse($notasRelevantes as $nota)
                    <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)] space-y-1">
                        <div class="flex items-center justify-between gap-2 text-[10px] text-[var(--rm-text-muted)]">
                            <span class="font-bold text-[var(--rm-action-primary)]">{{ $nota->tipo_nota ?: 'Nota clínica' }}</span>
                            <span class="shrink-0">{{ $nota->fecha_hora?->format('d/m/Y H:i') ?? 'Fecha no registrada' }}</span>
                        </div>
                        <p class="text-[var(--rm-text-body)] text-xs leading-relaxed">{{ $nota->contenido }}</p>
                    </div>
                @empty
                    <p class="rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-surface-alt)] p-3 text-center text-[var(--rm-text-muted)]">
                        No existen notas clínicas vigentes.
                    </p>
                @endforelse
            </div>
        </div>

    </div>
