<div class="space-y-6 pb-8">

        {{-- BARRA DE FILTROS UNIFICADA: ESTRUCTURA DE EMERGENCIAS + VISTA DE INCIDENTES --}}
    <div class="rounded-2xl bg-[#DED1C3] dark:bg-[#2C2723] border border-[#C7B9AA] dark:border-[#423B34] p-3 text-xs shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2.5 items-center">
            {{-- Buscador Principal --}}
            <div class="lg:col-span-6 relative">
                <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-[#677084] dark:text-[#9A9084]"></i>
                <input wire:model.live.debounce.300ms="busqueda"
                       type="text"
                       placeholder="Buscar paciente por nombre o CI..."
                       class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#26221F] py-2 pl-9 pr-8 text-xs font-medium text-[#304060] dark:text-[#F3EAE1] placeholder-[#677084] dark:placeholder-[#8C8276] focus:border-[#A35A44] focus:outline-none">
                @if(!empty())
                    <button type="button"
                            wire:click="limpiarFiltro('busqueda')"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#677084] hover:text-[#A35A44] transition"
                            title="Limpiar búsqueda">
                        <i class="ph-bold ph-x-circle text-base"></i>
                    </button>
                @endif
            </div>

            {{-- Filtro Nivel de Alerta Clínica --}}
            <div class="lg:col-span-3">
                <select wire:model.live="filtroAlerta"
                        class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#4E463E] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-medium text-[#304060] dark:text-[#E8DFD5] focus:border-[#A35A44] focus:outline-none">
                    <option value="">Todas las alertas (Global)</option>
                    <option value="critico">🚨 Críticos</option>
                    <option value="advertencia">⚠️ Advertencia</option>
                    <option value="normal">✅ Normales</option>
                    <option value="sin_dato">⚪ Sin datos</option>
                </select>
            </div>

            {{-- Filtro Registro de Hoy --}}
            <div class="lg:col-span-3">
                <select wire:model.live="filtroRegistro"
                        class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#4E463E] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-medium text-[#304060] dark:text-[#E8DFD5] focus:border-[#A35A44] focus:outline-none">
                    <option value="">Todos los registros</option>
                    <option value="con_registro_hoy">Con registro hoy</option>
                    <option value="sin_registro_hoy">Sin registro hoy ({{ $kpiSinRegistroHoy }})</option>
                </select>
            </div>
        </div>

        {{-- Fila de chips de filtros activos --}}
        @php
            $hasFiltrosActivos = !empty($busqueda) || !empty($filtroAlerta) || !empty($filtroRegistro);
        @endphp
        @if($hasFiltrosActivos)
            <div class="mt-2.5 pt-2.5 border-t border-[#C7B9AA]/60 dark:border-[#423B34] flex flex-wrap items-center justify-between gap-2">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-[11px] font-bold text-[#677084] dark:text-[#9A9084] flex items-center gap-1">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>
                    @if(!empty($busqueda))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#F0E8DE] dark:bg-[#211E1B] border border-[#C7B9AA] dark:border-[#4E463E] text-[11px] font-semibold text-[#304060] dark:text-[#F3EAE1]">
                            <span>Búsqueda: "{{ Str::limit($busqueda, 18) }}"</span>
                            <button type="button" wire:click="limpiarFiltro('busqueda')" class="hover:text-[#A35A44]"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($filtroAlerta))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg {{ $filtroAlerta === 'critico' ? 'bg-[#C85D52]/15 border border-[#C85D52]/30 text-[#8C2C22] dark:text-[#FFA399]' : ($filtroAlerta === 'advertencia' ? 'bg-[#D2A45E]/15 border border-[#D2A45E]/30 text-[#8C6422] dark:text-[#E2BD7E]' : 'bg-[#71876A]/15 border border-[#71876A]/30 text-[#495B44] dark:text-[#9FB897]') }} text-[11px] font-bold">
                            <span>Alerta: {{ ucfirst($filtroAlerta) }}</span>
                            <button type="button" wire:click="limpiarFiltro('filtroAlerta')" class="hover:text-[#A35A44]"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($filtroRegistro))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#304060]/10 dark:bg-[#F3EAE1]/10 border border-[#C7B9AA] text-[11px] font-bold text-[#304060] dark:text-[#F3EAE1]">
                            <span>Registro: {{ $filtroRegistro === 'con_registro_hoy' ? 'Con registro hoy' : 'Sin registro hoy' }}</span>
                            <button type="button" wire:click="limpiarFiltro('filtroRegistro')" class="hover:text-[#A35A44]"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                </div>
                <button wire:click="limpiarFiltros"
                        class="inline-flex items-center gap-1 rounded-xl bg-[#A35A44]/15 hover:bg-[#A35A44]/25 text-[#A35A44] dark:text-[#D58C79] py-1 px-2.5 text-xs font-bold transition">
                    <i class="ph-bold ph-arrow-counter-clockwise"></i> Limpiar filtros
                </button>
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
