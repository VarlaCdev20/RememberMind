<div class="space-y-6 pb-8">

    {{-- ══════════════════════════════════════════════════════════════
         ENCABEZADO
    ══════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col gap-4 border-b border-borde pb-5 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#C9654E]/10 text-[#C9654E] shadow-sm">
                <i class="ph-fill ph-heartbeat text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-titulo">Monitor de Signos Vitales</h2>
                <p class="text-sm font-semibold text-apoyo">Monitoreo clínico en tiempo real · Presión arterial, FC, SpO₂, temperatura y glucosa</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.medico.dashboard') }}" class="rm-btn-secondary h-9 px-4 flex items-center gap-1.5 text-xs">
                <i class="ph-bold ph-arrow-left text-sm"></i>
                <span class="hidden sm:inline">Dashboard</span>
            </a>
            <button wire:click="$refresh" class="rm-btn-secondary h-9 w-9 flex items-center justify-center">
                <i class="ph-bold ph-arrows-clockwise text-sm" wire:loading.class="animate-spin"></i>
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         KPIs DE MONITOREO
    ══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">

        {{-- 1. Críticos --}}
        <button wire:click="setFiltro('critico')"
                class="group flex flex-col gap-2 rounded-[22px] border p-4 shadow-sm text-left transition
                       {{ $filtroAlerta === 'critico'
                          ? 'border-[#C9654E] bg-[#C9654E]/10 ring-2 ring-[#C9654E]/30'
                          : ($kpiCriticos > 0 ? 'border-[#C9654E]/50 bg-[#C9654E]/5 hover:border-[#C9654E]' : 'border-borde bg-fondo-card hover:border-[#C9654E]/50') }}">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $kpiCriticos > 0 ? 'bg-[#C9654E] text-white' : 'bg-fondo-panel text-apoyo' }} transition">
                    <i class="ph-fill ph-warning-circle text-lg {{ $kpiCriticos > 0 ? 'animate-pulse' : '' }}"></i>
                </div>
                @if($kpiCriticos > 0)
                <span class="rounded-full bg-[#C9654E] px-1.5 py-0.5 text-[9px] font-black text-white">CRÍTICO</span>
                @endif
            </div>
            <div>
                <div class="text-2xl font-black {{ $kpiCriticos > 0 ? 'text-[#C9654E]' : 'text-titulo' }}">{{ $kpiCriticos }}</div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-apoyo">Alertas críticas</div>
            </div>
        </button>

        {{-- 2. Advertencia --}}
        <button wire:click="setFiltro('advertencia')"
                class="group flex flex-col gap-2 rounded-[22px] border p-4 shadow-sm text-left transition
                       {{ $filtroAlerta === 'advertencia'
                          ? 'border-estado-advertencia bg-estado-advertenciaBg ring-2 ring-estado-advertencia/30'
                          : ($kpiAdvertencia > 0 ? 'border-estado-advertencia/40 bg-fondo-card hover:border-estado-advertencia' : 'border-borde bg-fondo-card hover:border-estado-advertencia/40') }}">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $kpiAdvertencia > 0 ? 'bg-estado-advertenciaBg text-estado-advertencia' : 'bg-fondo-panel text-apoyo' }}">
                    <i class="ph-fill ph-warning text-lg"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black {{ $kpiAdvertencia > 0 ? 'text-estado-advertencia' : 'text-titulo' }}">{{ $kpiAdvertencia }}</div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-apoyo">Advertencia</div>
            </div>
        </button>

        {{-- 3. Normales --}}
        <button wire:click="setFiltro('normal')"
                class="group flex flex-col gap-2 rounded-[22px] border p-4 shadow-sm text-left transition
                       {{ $filtroAlerta === 'normal'
                          ? 'border-estado-exito bg-estado-exitoBg ring-2 ring-estado-exito/30'
                          : 'border-borde bg-fondo-card hover:border-estado-exito/50' }}">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                    <i class="ph-fill ph-check-circle text-lg"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black text-estado-exito">{{ $kpiNormales }}</div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-apoyo">Normales</div>
            </div>
        </button>

        {{-- 4. PA media --}}
        <div class="flex flex-col gap-2 rounded-[22px] border border-borde bg-fondo-card p-4 shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#9B8AC7]/15 text-[#9B8AC7]">
                <i class="ph-fill ph-activity text-lg"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-titulo">
                    {{ $kpiPaMedia ? round($kpiPaMedia) : '—' }}
                    @if($kpiPaMedia)<span class="text-sm font-semibold text-apoyo">mmHg</span>@endif
                </div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-apoyo">PA sistólica media</div>
            </div>
        </div>

        {{-- 5. FC media --}}
        <div class="flex flex-col gap-2 rounded-[22px] border border-borde bg-fondo-card p-4 shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#D9795F]/15 text-[#D9795F]">
                <i class="ph-fill ph-heart text-lg"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-titulo">
                    {{ $kpiFcMedia ? round($kpiFcMedia) : '—' }}
                    @if($kpiFcMedia)<span class="text-sm font-semibold text-apoyo">bpm</span>@endif
                </div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-apoyo">FC media</div>
            </div>
        </div>

        {{-- 6. SpO2 media --}}
        <div class="flex flex-col gap-2 rounded-[22px] border border-borde bg-fondo-card p-4 shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-infoBg text-estado-info">
                <i class="ph-fill ph-drop text-lg"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-titulo">
                    {{ $kpiSatMedia ? number_format($kpiSatMedia, 1) : '—' }}
                    @if($kpiSatMedia)<span class="text-sm font-semibold text-apoyo">%</span>@endif
                </div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-apoyo">SpO₂ media</div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         BARRA DE FILTROS + BUSCADOR
    ══════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-wrap items-center gap-3">
        {{-- Buscador --}}
        <div class="relative flex-1 min-w-[220px] max-w-sm">
            <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-apoyo text-sm"></i>
            <input wire:model.live.debounce.300ms="busqueda"
                   type="text"
                   placeholder="Buscar paciente por nombre o CI..."
                   class="w-full rounded-xl border border-borde bg-fondo-card py-2.5 pl-10 pr-4 text-sm font-semibold text-titulo placeholder-apoyo outline-none transition focus:border-borde-focus">
        </div>
        {{-- Filtro rápido activo --}}
        @if($filtroAlerta)
        <button wire:click="setFiltro('{{ $filtroAlerta }}')"
                class="flex items-center gap-1.5 rounded-xl border border-[#C9654E]/40 bg-[#C9654E]/10 px-3 py-2 text-xs font-bold text-[#C9654E]">
            <i class="ph-bold ph-x text-xs"></i>
            Filtro: {{ ucfirst($filtroAlerta) }}
        </button>
        @endif
        {{-- Alerta sin registros hoy --}}
        @if($kpiSinRegistroHoy > 0)
        <div class="flex items-center gap-1.5 rounded-xl border border-estado-advertencia/30 bg-estado-advertenciaBg px-3 py-2 text-xs font-bold text-estado-advertencia">
            <i class="ph-fill ph-clock text-sm"></i>
            {{ $kpiSinRegistroHoy }} paciente{{ $kpiSinRegistroHoy > 1 ? 's' : '' }} sin registro hoy
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         LEYENDA DE RANGOS
    ══════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-wrap gap-4 rounded-2xl border border-borde bg-fondo-panel px-5 py-3 text-[10px] font-semibold">
        <div class="flex items-center gap-1.5 text-apoyo uppercase tracking-wider font-black">
            <i class="ph-bold ph-info text-sm"></i> Rangos normales:
        </div>
        <div class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-[#9B8AC7]"></span> PA sistólica: 90–140 mmHg</div>
        <div class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-[#D9795F]"></span> FC: 60–100 bpm</div>
        <div class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-estado-info"></span> SpO₂: ≥ 95%</div>
        <div class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-estado-exito"></span> Temp: 36.0–37.5°C</div>
        <div class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-[#E9A05F]"></span> Glucosa: 70–180 mg/dL</div>
        <div class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-[#C9654E]"></span> FR: 12–20 rpm</div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         TABLA MATRICIAL DE SIGNOS VITALES
    ══════════════════════════════════════════════════════════════ --}}
    <div class="rounded-[24px] border border-borde bg-fondo-card shadow-sm overflow-hidden">
        @if($pacientesFiltrados->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs whitespace-nowrap">
                <thead class="bg-fondo-panel text-[10px] font-bold uppercase tracking-wider text-apoyo">
                    <tr>
                        <th class="px-4 py-3 min-w-[170px]">Paciente</th>
                        <th class="px-3 py-3 text-center min-w-[100px]">
                            <div class="flex items-center justify-center gap-1">
                                <i class="ph-bold ph-activity text-[#9B8AC7]"></i> PA (mmHg)
                            </div>
                        </th>
                        <th class="px-3 py-3 text-center min-w-[75px]">
                            <div class="flex items-center justify-center gap-1">
                                <i class="ph-bold ph-heart text-[#D9795F]"></i> FC (bpm)
                            </div>
                        </th>
                        <th class="px-3 py-3 text-center min-w-[70px]">
                            <div class="flex items-center justify-center gap-1">
                                <i class="ph-bold ph-wind text-[#C9654E]"></i> FR (rpm)
                            </div>
                        </th>
                        <th class="px-3 py-3 text-center min-w-[75px]">
                            <div class="flex items-center justify-center gap-1">
                                <i class="ph-bold ph-thermometer text-estado-exito"></i> Temp (°C)
                            </div>
                        </th>
                        <th class="px-3 py-3 text-center min-w-[75px]">
                            <div class="flex items-center justify-center gap-1">
                                <i class="ph-bold ph-drop text-estado-info"></i> SpO₂ (%)
                            </div>
                        </th>
                        <th class="px-3 py-3 text-center min-w-[80px]">
                            <div class="flex items-center justify-center gap-1">
                                <i class="ph-bold ph-drop-half text-[#E9A05F]"></i> Glucosa
                            </div>
                        </th>
                        <th class="px-3 py-3 text-center min-w-[65px]">IMC</th>
                        <th class="px-3 py-3 text-center min-w-[85px]">Últ. registro</th>
                        <th class="px-3 py-3 text-center min-w-[90px]">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde/40">
                    @foreach($pacientesFiltrados as $pac)
                    @php
                        $sv = $ultimosSignos[$pac->cod_am] ?? null;
                        $edad = $pac->fecha_nac ? \Carbon\Carbon::parse($pac->fecha_nac)->age : '—';

                        // Valores extraídos
                        $sist  = $sv?->presion_sistolica;
                        $diast = $sv?->presion_diastolica;
                        $fc    = $sv?->frecuencia_cardiaca;
                        $fr    = $sv?->frecuencia_respiratoria;
                        $temp  = $sv?->temperatura ? (float)$sv->temperatura : null;
                        $sat   = $sv?->saturacion;
                        $gluc  = $sv?->glucosa ? (float)$sv->glucosa : null;
                        $imc   = $sv?->imc ? (float)$sv->imc : null;

                        // Niveles de alerta por signo
                        $lvlPA   = \App\Livewire\Clinica\SignosVitalesPanel::alertaPA($sist, $diast);
                        $lvlFC   = \App\Livewire\Clinica\SignosVitalesPanel::alertaFC($fc);
                        $lvlFR   = \App\Livewire\Clinica\SignosVitalesPanel::alertaFR($fr);
                        $lvlTemp = \App\Livewire\Clinica\SignosVitalesPanel::alertaTemp($temp);
                        $lvlSat  = \App\Livewire\Clinica\SignosVitalesPanel::alertaSat($sat);
                        $lvlGluc = \App\Livewire\Clinica\SignosVitalesPanel::alertaGluc($gluc);
                        $lvlGlobal = $sv ? \App\Livewire\Clinica\SignosVitalesPanel::nivelGlobal($sist, $diast, $fc, $fr, $temp, $sat, $gluc) : 'sin_dato';

                        // Función de clases de celda
                        $celdaClass = fn(string $lvl) => match($lvl) {
                            'critico'     => 'bg-[#C9654E]/15 text-[#C9654E] font-black',
                            'advertencia' => 'bg-estado-advertenciaBg text-estado-advertencia font-bold',
                            'normal'      => 'text-titulo font-semibold',
                            default       => 'text-apoyo italic',
                        };

                        // Indicador fila
                        $filaIndicador = match($lvlGlobal) {
                            'critico'     => 'border-l-4 border-[#C9654E] bg-[#C9654E]/5',
                            'advertencia' => 'border-l-4 border-estado-advertencia bg-estado-advertenciaBg/20',
                            'normal'      => '',
                            default       => '',
                        };

                        $horaReg = $sv ? \Carbon\Carbon::parse($sv->fecha)->diffForHumans() : null;
                        $esHoy   = $sv && (string)$sv->fecha === $hoy;
                    @endphp
                    <tr class="hover:bg-fondo-panel/60 transition-colors {{ $filaIndicador }}">
                        {{-- Paciente --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full
                                            {{ $lvlGlobal === 'critico' ? 'bg-[#C9654E] text-white' :
                                               ($lvlGlobal === 'advertencia' ? 'bg-estado-advertenciaBg text-estado-advertencia' :
                                               'bg-estado-infoBg text-estado-info') }}
                                            text-xs font-black">
                                    {{ substr($pac->nombres, 0, 1) }}{{ substr($pac->ap_paterno, 0, 1) }}
                                    @if($lvlGlobal === 'critico')
                                    <span class="absolute -top-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-[#C9654E] ring-2 ring-fondo-card animate-ping"></span>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-titulo leading-tight">{{ $pac->nombres }} {{ $pac->ap_paterno }}</div>
                                    <div class="text-[10px] text-apoyo">{{ $edad }} años · {{ $pac->genero === 'F' ? 'F' : 'M' }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- PA --}}
                        <td class="px-3 py-3 text-center">
                            <div class="inline-flex flex-col items-center">
                                @if($sist && $diast)
                                <span class="rounded-lg px-2 py-0.5 text-xs {{ $celdaClass($lvlPA) }}">
                                    {{ $sist }}/{{ $diast }}
                                </span>
                                @if($lvlPA !== 'normal' && $lvlPA !== 'sin_dato')
                                <span class="mt-0.5 text-[9px] font-black {{ $lvlPA === 'critico' ? 'text-[#C9654E]' : 'text-estado-advertencia' }}">
                                    {{ $sist >= 180 ? 'HTA II' : ($sist >= 160 ? 'HTA I' : ($sist < 90 ? 'HIPOT' : 'Pre-HTA')) }}
                                </span>
                                @endif
                                @else
                                <span class="text-[10px] text-apoyo">—</span>
                                @endif
                            </div>
                        </td>

                        {{-- FC --}}
                        <td class="px-3 py-3 text-center">
                            @if($fc)
                            <span class="rounded-lg px-2 py-0.5 text-xs {{ $celdaClass($lvlFC) }}">{{ $fc }}</span>
                            @else
                            <span class="text-[10px] text-apoyo">—</span>
                            @endif
                        </td>

                        {{-- FR --}}
                        <td class="px-3 py-3 text-center">
                            @if($fr)
                            <span class="rounded-lg px-2 py-0.5 text-xs {{ $celdaClass($lvlFR) }}">{{ $fr }}</span>
                            @else
                            <span class="text-[10px] text-apoyo">—</span>
                            @endif
                        </td>

                        {{-- Temperatura --}}
                        <td class="px-3 py-3 text-center">
                            @if($temp)
                            <span class="rounded-lg px-2 py-0.5 text-xs {{ $celdaClass($lvlTemp) }}">{{ number_format($temp, 1) }}</span>
                            @else
                            <span class="text-[10px] text-apoyo">—</span>
                            @endif
                        </td>

                        {{-- SpO2 --}}
                        <td class="px-3 py-3 text-center">
                            @if($sat)
                            <span class="rounded-lg px-2 py-0.5 text-xs {{ $celdaClass($lvlSat) }}">{{ $sat }}%</span>
                            @else
                            <span class="text-[10px] text-apoyo">—</span>
                            @endif
                        </td>

                        {{-- Glucosa --}}
                        <td class="px-3 py-3 text-center">
                            @if($gluc)
                            <div class="inline-flex flex-col items-center">
                                <span class="rounded-lg px-2 py-0.5 text-xs {{ $celdaClass($lvlGluc) }}">{{ number_format($gluc, 0) }}</span>
                                <span class="text-[9px] text-apoyo">mg/dL</span>
                            </div>
                            @else
                            <span class="text-[10px] text-apoyo">—</span>
                            @endif
                        </td>

                        {{-- IMC --}}
                        <td class="px-3 py-3 text-center">
                            @if($imc)
                            @php
                                $imcLabel = $imc < 18.5 ? 'Bajo' : ($imc < 25 ? 'Normal' : ($imc < 30 ? 'Sobrep.' : 'Obesidad'));
                                $imcColor = $imc < 18.5 ? 'text-estado-advertencia' : ($imc < 25 ? 'text-estado-exito' : ($imc < 30 ? 'text-[#E9A05F]' : 'text-[#C9654E]'));
                            @endphp
                            <div class="flex flex-col items-center">
                                <span class="font-bold text-xs text-titulo">{{ number_format($imc, 1) }}</span>
                                <span class="text-[9px] {{ $imcColor }} font-bold">{{ $imcLabel }}</span>
                            </div>
                            @else
                            <span class="text-[10px] text-apoyo">—</span>
                            @endif
                        </td>

                        {{-- Último registro --}}
                        <td class="px-3 py-3 text-center">
                            @if($sv)
                            <div class="flex flex-col items-center">
                                <span class="text-[10px] font-bold {{ $esHoy ? 'text-estado-exito' : 'text-apoyo' }}">
                                    {{ $esHoy ? 'Hoy' : \Carbon\Carbon::parse($sv->fecha)->format('d/m/Y') }}
                                </span>
                                @if($sv->hora)
                                <span class="text-[9px] text-apoyo">{{ substr((string)$sv->hora, 0, 5) }}</span>
                                @endif
                            </div>
                            @else
                            <div class="flex flex-col items-center">
                                <span class="text-[10px] font-bold text-[#C9654E]">Sin reg.</span>
                                <span class="text-[9px] text-apoyo">registrar</span>
                            </div>
                            @endif
                        </td>

                        {{-- Acciones --}}
                        <td class="px-3 py-3">
                            <div class="flex items-center justify-center gap-1.5">
                                <button wire:click="abrirRegistroSignos('{{ $pac->cod_am }}')"
                                        title="Registrar nuevos signos vitales"
                                        class="h-7 px-2 rounded-lg
                                               {{ $esHoy ? 'bg-fondo-panel text-apoyo hover:bg-[#C9654E]/10 hover:text-[#C9654E]' : 'bg-[#C9654E]/10 text-[#C9654E] hover:bg-[#C9654E] hover:text-white' }}
                                               transition text-[10px] font-black flex items-center gap-1">
                                    <i class="ph-bold ph-plus text-xs"></i>
                                    Reg.
                                </button>
                                <button wire:click="abrirFicha('{{ $pac->cod_am }}')"
                                        title="Ver ficha clínica"
                                        class="h-7 w-7 rounded-lg bg-estado-infoBg text-estado-info hover:bg-estado-info hover:text-white transition flex items-center justify-center">
                                    <i class="ph-bold ph-folder-open text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-fondo-panel text-apoyo">
                <i class="ph-bold ph-heartbeat text-3xl"></i>
            </div>
            <h3 class="mt-4 text-base font-bold text-titulo">Sin registros para el filtro activo</h3>
            <p class="mt-1 text-sm text-apoyo">
                @if($filtroAlerta) No hay pacientes con nivel "{{ $filtroAlerta }}" actualmente.
                @elseif($busqueda) No se encontró ningún paciente con "{{ $busqueda }}".
                @else No hay pacientes activos con signos vitales registrados.
                @endif
            </p>
            @if($filtroAlerta || $busqueda)
            <button wire:click="$set('filtroAlerta', ''); $set('busqueda', '')"
                    class="mt-4 rm-btn-secondary px-4 py-2 text-xs">
                Limpiar filtros
            </button>
            @endif
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         GRÁFICOS: TENDENCIA 7 DÍAS + DISTRIBUCIÓN PA
    ══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
        {{-- Tendencia 7 días (Design System Translúcido) --}}
        <div class="lg:col-span-3 rm-chart-card rm-chart-glass">
            <div class="rm-chart-header border-b border-[var(--rm-border)] pb-3 mb-3">
                <div>
                    <h3 class="rm-chart-title">Tendencia últimos 7 días</h3>
                    <p class="rm-chart-subtitle">Promedios diarios de PA sistólica, FC y SpO₂</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--rm-surface-alt)] text-[var(--rm-primary)] border border-[var(--rm-border)]">
                    <i class="ph-bold ph-trend-up text-base"></i>
                </div>
            </div>
            <div wire:ignore class="h-56 w-full">
                <canvas id="chartTendenciaSV"></canvas>
            </div>
        </div>

        {{-- Distribución PA sistólica (Design System Translúcido Grueso) --}}
        <div class="lg:col-span-2 rm-chart-card rm-chart-glass">
            <div class="rm-chart-header border-b border-[var(--rm-border)] pb-3 mb-3">
                <div>
                    <h3 class="rm-chart-title">Distribución PA</h3>
                    <p class="rm-chart-subtitle">Clasificación sistólica actual</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--rm-surface-alt)] text-[#D9745B] border border-[var(--rm-border)]">
                    <i class="ph-bold ph-chart-donut text-base"></i>
                </div>
            </div>
            <div wire:ignore class="h-56 w-full">
                <canvas id="chartDistPA"></canvas>
            </div>
        </div>
    </div>

    {{-- Referencia de valores --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6 text-center">
        @foreach([
            ['label'=>'Hipotensión', 'range'=>'PA < 90', 'color'=>'text-estado-info', 'bg'=>'bg-estado-infoBg', 'icon'=>'ph-arrow-down'],
            ['label'=>'Normal', 'range'=>'PA 90–139', 'color'=>'text-estado-exito', 'bg'=>'bg-estado-exitoBg', 'icon'=>'ph-check'],
            ['label'=>'Pre-HTA', 'range'=>'PA 140–159', 'color'=>'text-[#E9A05F]', 'bg'=>'bg-[#E9A05F]/10', 'icon'=>'ph-warning'],
            ['label'=>'HTA Grado I', 'range'=>'PA 160–179', 'color'=>'text-estado-advertencia', 'bg'=>'bg-estado-advertenciaBg', 'icon'=>'ph-warning-circle'],
            ['label'=>'HTA Grado II', 'range'=>'PA ≥ 180', 'color'=>'text-[#C9654E]', 'bg'=>'bg-[#C9654E]/10', 'icon'=>'ph-warning-diamond'],
            ['label'=>'SpO₂ crítica', 'range'=>'< 88%', 'color'=>'text-[#C9654E]', 'bg'=>'bg-[#C9654E]/10', 'icon'=>'ph-drop-slash'],
        ] as $ref)
        <div class="flex flex-col items-center gap-1 rounded-xl border border-borde bg-fondo-card p-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $ref['bg'] }} {{ $ref['color'] }}">
                <i class="ph-bold {{ $ref['icon'] }} text-sm"></i>
            </div>
            <div class="text-[10px] font-black text-titulo">{{ $ref['label'] }}</div>
            <div class="text-[9px] text-apoyo">{{ $ref['range'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- MODAL de registro --}}
    @livewire('clinica.registro-signos-vitales-modal')

    @script
    <script>
const rmDatosa9b5f3c2c94b = @js($chartTendencia7d);
const rmDatos2e40af881bbb = @js($chartDistPA);
{!! file_get_contents(resource_path('frontend/scripts/modules/livewire-clinica-signos-vitales-panel.js')) !!}
</script>
    @endscript

    <div x-data="signosVitalesCharts" x-init="init()"></div>
</div>
