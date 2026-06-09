<div class="space-y-6 pb-8">

    {{-- ══════════════════════════════════════════════════════
         ENCABEZADO
    ══════════════════════════════════════════════════════ --}}
    <div class="flex flex-col gap-4 border-b border-borde pb-5 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-boton-acento/10 text-boton-acento shadow-sm">
                <i class="ph-fill ph-stethoscope text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-titulo">
                    @if($seccion === 'valoraciones') Valoraciones Médicas Pendientes
                    @elseif($seccion === 'decisiones') Decisiones de Admisión
                    @else Panel Médico General
                    @endif
                </h2>
                <p class="text-sm font-semibold text-apoyo"
                   x-data="{ hora: '' }"
                   x-init="
                       const fmt = () => {
                           const n = new Date();
                           hora = n.toLocaleDateString('es-BO', { weekday:'long', day:'numeric', month:'long', year:'numeric' })
                               + ' · ' + n.toLocaleTimeString('es-BO', { hour:'2-digit', minute:'2-digit' });
                       };
                       fmt(); setInterval(fmt, 30000);
                   "
                   x-text="hora">
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.medico.pacientes.observacion') }}"
               class="rm-btn-secondary h-9 px-4 flex items-center gap-1.5 text-xs">
                <i class="ph-bold ph-users-three text-sm"></i> Seguimiento
            </a>
            <button onclick="window.location.reload()"
                    class="rm-btn-secondary h-9 px-4 flex items-center gap-1.5 text-xs">
                <i class="ph-bold ph-arrows-clockwise text-sm"></i>
                <span class="hidden sm:inline">Actualizar</span>
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         KPIs — 6 TARJETAS
    ══════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        {{-- 1. Residentes activos --}}
        <a href="{{ route('admin.medico.pacientes.observacion') }}"
           class="group flex flex-col gap-2 rounded-[22px] border border-borde bg-fondo-card p-4 shadow-sm hover:border-estado-info hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-infoBg text-estado-info group-hover:scale-110 transition">
                    <i class="ph-bold ph-users text-lg"></i>
                </div>
                <i class="ph-bold ph-arrow-up-right text-xs text-apoyo opacity-0 group-hover:opacity-100 transition"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-titulo">{{ $totalResidentes }}</div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-apoyo">Residentes activos</div>
            </div>
        </a>

        {{-- 2. Pendientes valoración --}}
        <div class="flex flex-col gap-2 rounded-[22px] border {{ $pendientesValoracion > 0 ? 'border-estado-advertencia bg-estado-advertenciaBg/30' : 'border-borde bg-fondo-card' }} p-4 shadow-sm transition">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $pendientesValoracion > 0 ? 'bg-estado-advertencia text-white animate-pulse' : 'bg-estado-advertenciaBg text-estado-advertencia' }}">
                    <i class="ph-bold ph-hourglass text-lg"></i>
                </div>
                @if($pendientesValoracion > 0)
                <span class="rounded-full bg-estado-advertencia px-1.5 py-0.5 text-[10px] font-black text-white">URGENTE</span>
                @endif
            </div>
            <div>
                <div class="text-2xl font-black {{ $pendientesValoracion > 0 ? 'text-estado-advertencia' : 'text-titulo' }}">{{ $pendientesValoracion }}</div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-apoyo">Pend. valoración</div>
            </div>
        </div>

        {{-- 3. Seguimiento activo --}}
        <div class="flex flex-col gap-2 rounded-[22px] border border-borde bg-fondo-card p-4 shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                <i class="ph-bold ph-user-check text-lg"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-titulo">{{ $enSeguimientoActivo }}</div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-apoyo">En seguimiento</div>
            </div>
        </div>

        {{-- 4. Alertas críticas --}}
        <div class="flex flex-col gap-2 rounded-[22px] border {{ $alertasCriticas > 0 ? 'border-estado-error bg-estado-errorBg/20' : 'border-borde bg-fondo-card' }} p-4 shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $alertasCriticas > 0 ? 'bg-estado-error text-white' : 'bg-fondo-panel text-apoyo' }}">
                <i class="ph-bold ph-warning-circle text-lg"></i>
            </div>
            <div>
                <div class="text-2xl font-black {{ $alertasCriticas > 0 ? 'text-estado-error' : 'text-titulo' }}">{{ $alertasCriticas }}</div>
                <div class="text-[10px] font-bold uppercase tracking-wider {{ $alertasCriticas > 0 ? 'text-estado-error' : 'text-apoyo' }}">Alertas signos</div>
            </div>
        </div>

        {{-- 5. Notas hoy --}}
        <div class="flex flex-col gap-2 rounded-[22px] border border-borde bg-fondo-card p-4 shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento">
                <i class="ph-bold ph-note-pencil text-lg"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-titulo">{{ $notasHoy }}</div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-apoyo">Notas hoy</div>
            </div>
        </div>

        {{-- 6. Medicación activa --}}
        <div class="flex flex-col gap-2 rounded-[22px] border border-borde bg-fondo-card p-4 shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                <i class="ph-bold ph-pill text-lg"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-titulo">{{ $totalMedicacionActiva }}</div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-apoyo">Medicación activa</div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         FILA 1 DE GRÁFICOS: Edad+Género | Diagnósticos
    ══════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

        {{-- Gráfico 1: Distribución por Edad y Género --}}
        <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-infoBg text-estado-info">
                        <i class="ph-bold ph-users text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-titulo">Distribución por Edad y Género</h3>
                        <p class="text-[10px] text-apoyo">Residentes activos en el centro</p>
                    </div>
                </div>
                <span class="rounded-full bg-fondo-panel px-2.5 py-1 text-[10px] font-bold text-apoyo">{{ $totalResidentes }} total</span>
            </div>
            <div wire:ignore
                 x-data="graficoEdad(@js($chartEdad))"
                 x-init="init()">
                <canvas x-ref="canvas" style="height:220px"></canvas>
            </div>
        </div>

        {{-- Gráfico 2: Prevalencia de Diagnósticos --}}
        <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-errorBg text-estado-error">
                    <i class="ph-bold ph-heartbeat text-base"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-titulo">Prevalencia de Diagnósticos</h3>
                    <p class="text-[10px] text-apoyo">Nº de pacientes con cada condición crónica</p>
                </div>
            </div>
            <div wire:ignore
                 x-data="graficoDiagnosticos(@js($chartDiagnosticos))"
                 x-init="init()">
                <canvas x-ref="canvas" style="height:220px"></canvas>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         FILA 2 DE GRÁFICOS: Tendencia Signos Vitales | Dependencia
    ══════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

        {{-- Gráfico 3: Tendencia de signos vitales (30 días) --}}
        <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm lg:col-span-2">
            <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento">
                        <i class="ph-bold ph-chart-line text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-titulo">Tendencia de Signos Vitales</h3>
                        <p class="text-[10px] text-apoyo">Promedios diarios — últimos 30 días</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 text-[10px] font-bold">
                    <span class="flex items-center gap-1"><span class="inline-block h-2 w-4 rounded bg-[#C9654E]"></span> PA Sist.</span>
                    <span class="flex items-center gap-1"><span class="inline-block h-2 w-4 rounded bg-[#3F7D5A]"></span> SpO2</span>
                    <span class="flex items-center gap-1"><span class="inline-block h-2 w-4 rounded bg-[#E9A05F]"></span> Glucosa</span>
                </div>
            </div>
            <div wire:ignore
                 x-data="graficoTendencia(@js($chartTendencia))"
                 x-init="init()">
                <canvas x-ref="canvas" style="height:240px"></canvas>
            </div>
            {{-- Valores de referencia --}}
            <div class="mt-3 grid grid-cols-3 gap-2 border-t border-borde pt-3">
                <div class="rounded-lg bg-fondo-panel px-2 py-1.5 text-center">
                    <div class="text-[9px] font-bold text-apoyo uppercase tracking-wider">PA Normal</div>
                    <div class="text-xs font-black text-titulo">90-140 mmHg</div>
                </div>
                <div class="rounded-lg bg-fondo-panel px-2 py-1.5 text-center">
                    <div class="text-[9px] font-bold text-apoyo uppercase tracking-wider">SpO2 Normal</div>
                    <div class="text-xs font-black text-titulo">≥ 95%</div>
                </div>
                <div class="rounded-lg bg-fondo-panel px-2 py-1.5 text-center">
                    <div class="text-[9px] font-bold text-apoyo uppercase tracking-wider">Glucosa Normal</div>
                    <div class="text-xs font-black text-titulo">70-100 mg/dL</div>
                </div>
            </div>
        </div>

        {{-- Gráfico 4: Nivel de Dependencia Funcional --}}
        <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                    <i class="ph-bold ph-person text-base"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-titulo">Dependencia Funcional</h3>
                    <p class="text-[10px] text-apoyo">Clasificación Barthel</p>
                </div>
            </div>
            @if(!empty($chartDependencia['labels']))
            <div wire:ignore
                 x-data="graficoDependencia(@js($chartDependencia))"
                 x-init="init()">
                <canvas x-ref="canvas" style="height:180px"></canvas>
            </div>
            {{-- Leyenda manual --}}
            <div class="mt-3 space-y-1">
                @php
                    $depColors = ['Independiente'=>'#3F7D5A','Dependencia leve'=>'#5B7C9D','Dependencia moderada'=>'#E9A05F','Dependencia severa'=>'#D9795F','Dependencia total'=>'#C9654E'];
                    $depTotal  = array_sum($chartDependencia['values']);
                @endphp
                @foreach($chartDependencia['labels'] as $i => $lbl)
                @php $val = $chartDependencia['values'][$i] ?? 0; @endphp
                <div class="flex items-center justify-between text-[10px]">
                    <div class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full" style="background:{{ $depColors[$lbl] ?? '#90AFCB' }}"></span>
                        <span class="font-semibold text-apoyo">{{ $lbl }}</span>
                    </div>
                    <span class="font-black text-titulo">{{ $val }}</span>
                </div>
                @endforeach
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <i class="ph-bold ph-person text-2xl text-apoyo"></i>
                <p class="mt-2 text-xs text-apoyo italic">Sin valoraciones Barthel registradas</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         FILA 3 DE GRÁFICOS: IMC | Estados | Notas por tipo
    ══════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

        {{-- Gráfico 5: Distribución IMC --}}
        <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-advertenciaBg text-estado-advertencia">
                    <i class="ph-bold ph-scales text-base"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-titulo">Distribución IMC</h3>
                    <p class="text-[10px] text-apoyo">Índice de Masa Corporal</p>
                </div>
            </div>
            @if(array_sum($chartImc['values'] ?? []) > 0)
            <div wire:ignore
                 x-data="graficoImc(@js($chartImc))"
                 x-init="init()">
                <canvas x-ref="canvas" style="height:180px"></canvas>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-10">
                <i class="ph-bold ph-scales text-2xl text-apoyo"></i>
                <p class="mt-2 text-xs text-apoyo italic text-center">Sin datos de peso/talla</p>
            </div>
            @endif
        </div>

        {{-- Gráfico 6: Distribución de Estados --}}
        <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento">
                    <i class="ph-bold ph-chart-pie text-base"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-titulo">Estado de Residentes</h3>
                    <p class="text-[10px] text-apoyo">Distribución por estado actual</p>
                </div>
            </div>
            @if(!empty($chartEstados['labels']))
            <div wire:ignore
                 x-data="graficoEstados(@js($chartEstados))"
                 x-init="init()">
                <canvas x-ref="canvas" style="height:180px"></canvas>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-10">
                <p class="text-xs text-apoyo italic">Sin datos de estado</p>
            </div>
            @endif
        </div>

        {{-- Gráfico 7: Notas por Tipo --}}
        <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                    <i class="ph-bold ph-note text-base"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-titulo">Notas por Tipo</h3>
                    <p class="text-[10px] text-apoyo">Distribución de notas SOAP</p>
                </div>
            </div>
            <div wire:ignore
                 x-data="graficoNotasTipo(@js($chartNotasTipo))"
                 x-init="init()">
                <canvas x-ref="canvas" style="height:180px"></canvas>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         ALERTAS CLÍNICAS ACTIVAS
    ══════════════════════════════════════════════════════ --}}
    @if(count($alertasPacientes) > 0)
    <div class="rounded-[24px] border-2 border-estado-error bg-estado-errorBg/10 p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-error text-white">
                    <i class="ph-bold ph-warning-circle text-base"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-estado-error">Alertas Clínicas Activas</h3>
                    <p class="text-[10px] text-apoyo">Pacientes con signos vitales fuera de rango</p>
                </div>
            </div>
            <a href="{{ route('admin.medico.pacientes.observacion') }}"
               class="text-[10px] font-bold text-estado-info hover:underline">
                Ver todos <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($alertasPacientes as $alerta)
            <div class="flex items-center gap-3 rounded-xl border border-estado-error/20 bg-fondo-card p-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-estado-error text-white font-black text-xs">
                    <i class="ph-bold ph-warning"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-xs font-bold text-titulo">{{ $alerta['nombre'] }}</div>
                    <div class="text-[10px] font-bold text-estado-error">{{ $alerta['tipo'] }}</div>
                    <div class="text-[10px] text-apoyo">
                        @if($alerta['pa'] !== '—') PA: {{ $alerta['pa'] }} @endif
                        @if($alerta['sat']) · SpO2: {{ $alerta['sat'] }}% @endif
                    </div>
                </div>
                <a href="{{ route('admin.medico.paciente.ficha', $alerta['cod_am']) }}"
                   class="shrink-0 flex h-7 w-7 items-center justify-center rounded-lg bg-estado-infoBg text-estado-info hover:bg-estado-info hover:text-white transition">
                    <i class="ph-bold ph-folder-open text-xs"></i>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════
         COLA DE VALORACIONES PENDIENTES
    ══════════════════════════════════════════════════════ --}}
    @if($valoracionesPendientes->count() > 0)
    <div class="rounded-[24px] border border-estado-advertencia bg-estado-advertenciaBg/20 p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-advertencia text-white">
                    <i class="ph-bold ph-first-aid text-base"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-estado-advertencia">Cola de Valoraciones Médicas</h3>
                    <p class="text-[10px] text-apoyo">{{ $valoracionesPendientes->count() }} paciente(s) esperando evaluación del médico</p>
                </div>
            </div>
            <span class="rounded-full bg-estado-advertencia px-3 py-1 text-xs font-black text-white">
                {{ $valoracionesPendientes->count() }} pendiente{{ $valoracionesPendientes->count() != 1 ? 's' : '' }}
            </span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-estado-advertencia/30 bg-fondo-card">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-fondo-panel text-[10px] font-bold uppercase tracking-wider text-apoyo">
                    <tr>
                        <th class="px-5 py-3">Paciente</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3">Motivo de ingreso</th>
                        <th class="px-5 py-3">Procedencia</th>
                        <th class="px-5 py-3">Desde</th>
                        <th class="px-5 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde/50">
                    @foreach($valoracionesPendientes as $pac)
                    @php
                        $estadoColor = match($pac->estado?->estado) {
                            'VALORACION_MEDICA'           => 'bg-estado-advertenciaBg text-estado-advertencia',
                            'PENDIENTE_VALORACION_MEDICA' => 'bg-estado-errorBg text-estado-error',
                            'DECISION_ADMISION'           => 'bg-estado-infoBg text-estado-info',
                            default => 'bg-fondo-panel text-apoyo',
                        };
                    @endphp
                    <tr class="hover:bg-estado-advertenciaBg/10 transition-colors">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-estado-advertencia/10 font-black text-estado-advertencia text-xs">
                                    {{ substr($pac->nombres, 0, 1) }}{{ substr($pac->ap_paterno, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-titulo text-xs">{{ $pac->nombres }} {{ $pac->ap_paterno }}</div>
                                    <div class="text-[10px] text-apoyo">
                                        {{ $pac->fecha_nac ? \Carbon\Carbon::parse($pac->fecha_nac)->age . ' años' : '—' }} · CI: {{ $pac->ci ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-black uppercase tracking-wider {{ $estadoColor }}">
                                {{ str_replace('_', ' ', $pac->estado?->estado ?? 'SIN ESTADO') }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="max-w-[180px] truncate text-xs font-semibold text-titulo">
                                {{ $pac->motivo_ingreso ?? 'No especificado' }}
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="text-xs text-apoyo">{{ $pac->procedencia_ingreso ?? '—' }}</div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="text-[10px] text-apoyo">
                                {{ $pac->created_at ? $pac->created_at->diffForHumans() : '—' }}
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-center gap-1.5">
                                @if(in_array($pac->estado?->estado, ['VALORACION_MEDICA', 'PENDIENTE_VALORACION_MEDICA']))
                                <button wire:click="iniciarValoracionMedica('{{ $pac->cod_am }}')"
                                        class="h-8 px-3 rounded-lg bg-estado-advertencia text-white text-[10px] font-black uppercase tracking-wide hover:bg-estado-advertencia/80 transition whitespace-nowrap">
                                    <i class="ph-bold ph-stethoscope mr-1"></i>Valorar
                                </button>
                                @elseif($pac->estado?->estado === 'DECISION_ADMISION')
                                <button wire:click="abrirDecisionAdmision('{{ $pac->cod_am }}')"
                                        class="h-8 px-3 rounded-lg bg-estado-exito text-white text-[10px] font-black uppercase tracking-wide hover:bg-estado-exito/80 transition whitespace-nowrap">
                                    <i class="ph-bold ph-check-circle mr-1"></i>Dictamen
                                </button>
                                @endif
                                <a href="{{ route('admin.medico.paciente.ficha', $pac->cod_am) }}"
                                   class="h-8 w-8 rounded-lg bg-estado-infoBg text-estado-info hover:bg-estado-info hover:text-white transition flex items-center justify-center">
                                    <i class="ph-bold ph-folder-open text-sm"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="flex items-center gap-3 rounded-[24px] border border-estado-exito bg-estado-exitoBg/20 p-4">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
            <i class="ph-bold ph-check-circle text-lg"></i>
        </div>
        <div>
            <div class="text-sm font-black text-titulo">Sin valoraciones pendientes</div>
            <div class="text-xs text-apoyo">No hay pacientes esperando evaluación médica en este momento.</div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════
         NOTAS MÉDICAS RECIENTES
    ══════════════════════════════════════════════════════ --}}
    <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento">
                    <i class="ph-bold ph-note-pencil text-base"></i>
                </div>
                <h3 class="text-sm font-black text-titulo">Últimas Notas de Evolución SOAP</h3>
            </div>
            <span class="text-[10px] font-bold text-apoyo">Hoy: {{ $notasHoy }}</span>
        </div>

        @if(count($notasRecientes) > 0)
        <div class="space-y-2">
            @foreach($notasRecientes as $nota)
            @php
                $tipoColor = match($nota['tipo']) {
                    'URGENCIA'      => 'bg-estado-errorBg text-estado-error',
                    'INGRESO'       => 'bg-estado-infoBg text-estado-info',
                    'EGRESO'        => 'bg-fondo-panel text-apoyo',
                    'INTERCONSULTA' => 'bg-boton-acento/10 text-boton-acento',
                    'PROCEDIMIENTO' => 'bg-estado-advertenciaBg text-estado-advertencia',
                    default         => 'bg-estado-exitoBg text-estado-exito',
                };
            @endphp
            <div class="flex gap-3 rounded-2xl border border-borde/60 bg-fondo-panel px-4 py-3 hover:bg-fondo-card transition">
                <div class="shrink-0 pt-0.5">
                    <span class="inline-flex rounded-lg px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $tipoColor }}">
                        {{ $nota['tipo'] }}
                    </span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold text-titulo">{{ $nota['paciente'] }}</span>
                        <span class="text-[10px] text-apoyo">{{ $nota['fecha'] }} {{ $nota['hora'] }}</span>
                    </div>
                    @if($nota['valoracion'])
                    <p class="mt-0.5 text-[10px] text-apoyo truncate">
                        <span class="font-bold text-boton-acento">A:</span> {{ $nota['valoracion'] }}
                    </p>
                    @endif
                    @if($nota['plan'])
                    <p class="text-[10px] text-apoyo truncate">
                        <span class="font-bold text-estado-exito">P:</span> {{ $nota['plan'] }}
                    </p>
                    @endif
                    <p class="text-[10px] text-meta">Dr. {{ $nota['medico'] }}</p>
                </div>
                <a href="{{ route('admin.medico.paciente.ficha', $nota['cod_am']) }}"
                   class="shrink-0 flex h-7 w-7 items-center justify-center rounded-lg text-apoyo hover:bg-estado-infoBg hover:text-estado-info transition">
                    <i class="ph-bold ph-arrow-right text-xs"></i>
                </a>
            </div>
            @endforeach
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-10 text-center">
            <i class="ph-bold ph-note-pencil text-3xl text-apoyo"></i>
            <p class="mt-2 text-sm font-bold text-titulo">Sin notas registradas</p>
            <p class="text-xs text-apoyo">Las notas de evolución aparecerán aquí.</p>
        </div>
        @endif
    </div>

    @livewire('admin.medico.valoracion-medica-modal')
    @livewire('admin.medico.decision-admision-modal')

    {{-- ══════════════════════════════════════════════════════
         SCRIPTS DE GRÁFICOS CHART.JS + ALPINE.JS
    ══════════════════════════════════════════════════════ --}}
    @script
<script>
    // ── Paleta de colores institucional RememberMind ──────────────
    const RM = {
        azulProfundo : '#293A59',
        azulClinico  : '#5B7C9D',
        azulClaro    : '#90AFCB',
        verdeSalud   : '#3F7D5A',
        verdeSuave   : '#7FA587',
        terracota    : '#D9795F',
        naranja      : '#E9A05F',
        salmon       : '#E28B70',
        morado       : '#9B8AC7',
        danger       : '#C9654E',
        neutro       : '#737785',
        neutroCard   : '#F4EEE7',
        borde        : 'rgba(91,98,115,0.09)',
    };

    // Opciones base compartidas
    const BASE_OPTS = {
        responsive          : true,
        maintainAspectRatio : false,
        plugins: {
            datalabels: { display: false },
            legend    : {
                labels: { color: '#5B6273', font: { size: 11, weight: '600' }, padding: 12, boxWidth: 12 }
            },
            tooltip: {
                backgroundColor : '#293A59',
                titleColor      : '#FFF8F1',
                bodyColor       : '#D8CDC0',
                padding         : 10,
                cornerRadius    : 10,
            }
        },
    };

    // ── 1. Edad por Género ────────────────────────────────────────
    Alpine.data('graficoEdad', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartEdad', (d) => { this.chart?.destroy(); this.draw(d); });
        },
        draw(d) {
            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'bar',
                data: {
                    labels  : d.labels,
                    datasets: [
                        {
                            label           : 'Masculino',
                            data            : d.masculino,
                            backgroundColor : RM.azulClinico,
                            borderRadius    : 8,
                            borderWidth     : 0,
                        },
                        {
                            label           : 'Femenino',
                            data            : d.femenino,
                            backgroundColor : RM.terracota,
                            borderRadius    : 8,
                            borderWidth     : 0,
                        },
                    ],
                },
                options: {
                    ...BASE_OPTS,
                    plugins: {
                        ...BASE_OPTS.plugins,
                        legend  : { position: 'top', labels: { ...BASE_OPTS.plugins.legend.labels } },
                        datalabels: {
                            display : true,
                            anchor  : 'end',
                            align   : 'top',
                            color   : RM.neutro,
                            font    : { weight: 'bold', size: 10 },
                            formatter: (v) => v > 0 ? v : '',
                        },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: RM.neutro, font: { size: 11 } } },
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, color: RM.neutro, font: { size: 10 } },
                            grid : { color: RM.borde },
                        },
                    },
                },
            });
        },
    }));

    // ── 2. Diagnósticos (barras horizontales) ─────────────────────
    Alpine.data('graficoDiagnosticos', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartDiagnosticos', (d) => { this.chart?.destroy(); this.draw(d); });
        },
        draw(d) {
            const colores = [
                RM.azulProfundo, RM.azulClinico, RM.verdeSalud, RM.morado,
                RM.terracota,    RM.naranja,     RM.salmon,     RM.danger,
                RM.verdeSuave,   RM.azulClaro,
            ];
            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'bar',
                data: {
                    labels  : d.labels,
                    datasets: [{
                        label           : 'Nº pacientes',
                        data            : d.values,
                        backgroundColor : colores.slice(0, d.labels.length),
                        borderRadius    : 6,
                        borderWidth     : 0,
                    }],
                },
                options: {
                    ...BASE_OPTS,
                    indexAxis: 'y',
                    plugins: {
                        ...BASE_OPTS.plugins,
                        legend: { display: false },
                        datalabels: {
                            display  : true,
                            anchor   : 'end',
                            align    : 'end',
                            color    : RM.neutro,
                            font     : { weight: 'bold', size: 10 },
                            formatter: (v) => v > 0 ? v : '',
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, color: RM.neutro, font: { size: 10 } },
                            grid : { color: RM.borde },
                        },
                        y: { grid: { display: false }, ticks: { color: RM.neutro, font: { size: 10 } } },
                    },
                },
            });
        },
    }));

    // ── 3. Tendencia signos vitales (30 días) ─────────────────────
    Alpine.data('graficoTendencia', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartTendencia', (d) => { this.chart?.destroy(); this.draw(d); });
        },
        draw(d) {
            if (!d.labels || d.labels.length === 0) {
                const ctx = this.$refs.canvas.getContext('2d');
                ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                ctx.font = '12px Inter, sans-serif';
                ctx.fillStyle = RM.neutro;
                ctx.textAlign = 'center';
                ctx.fillText('Sin datos de signos vitales en los últimos 30 días', ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }
            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'line',
                data: {
                    labels  : d.labels,
                    datasets: [
                        {
                            label           : 'PA Sistólica (mmHg)',
                            data            : d.pa,
                            borderColor     : RM.danger,
                            backgroundColor : 'rgba(201,101,78,0.07)',
                            fill            : true,
                            tension         : 0.4,
                            borderWidth     : 2.5,
                            pointRadius     : 3,
                            pointBackgroundColor: RM.danger,
                            yAxisID         : 'y',
                        },
                        {
                            label           : 'Glucosa (mg/dL)',
                            data            : d.gluc,
                            borderColor     : RM.naranja,
                            backgroundColor : 'rgba(0,0,0,0)',
                            fill            : false,
                            tension         : 0.4,
                            borderWidth     : 2,
                            borderDash      : [5, 3],
                            pointRadius     : 3,
                            pointBackgroundColor: RM.naranja,
                            yAxisID         : 'y',
                        },
                        {
                            label           : 'SpO2 (%)',
                            data            : d.sat,
                            borderColor     : RM.verdeSalud,
                            backgroundColor : 'rgba(63,125,90,0.07)',
                            fill            : true,
                            tension         : 0.4,
                            borderWidth     : 2.5,
                            pointRadius     : 3,
                            pointBackgroundColor: RM.verdeSalud,
                            yAxisID         : 'y1',
                        },
                    ],
                },
                options: {
                    ...BASE_OPTS,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { ...BASE_OPTS.plugins, legend: { position: 'top', labels: { ...BASE_OPTS.plugins.legend.labels } } },
                    scales: {
                        x : { grid: { display: false }, ticks: { color: RM.neutro, font: { size: 10 } } },
                        y : {
                            position: 'left',
                            beginAtZero: false,
                            ticks: { color: RM.danger, font: { size: 10 } },
                            grid : { color: RM.borde },
                            title: { display: true, text: 'mmHg / mg/dL', color: RM.neutro, font: { size: 9 } },
                        },
                        y1: {
                            position: 'right',
                            min : 80, max: 100,
                            ticks: { color: RM.verdeSalud, font: { size: 10 } },
                            grid : { drawOnChartArea: false },
                            title: { display: true, text: 'SpO2 %', color: RM.verdeSalud, font: { size: 9 } },
                        },
                    },
                },
            });
        },
    }));

    // ── 4. Dependencia funcional Barthel (doughnut) ───────────────
    Alpine.data('graficoDependencia', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartDependencia', (d) => { this.chart?.destroy(); this.draw(d); });
        },
        draw(d) {
            const colMap = {
                'Independiente'       : RM.verdeSalud,
                'Dependencia leve'    : RM.azulClinico,
                'Dependencia moderada': RM.naranja,
                'Dependencia severa'  : RM.terracota,
                'Dependencia total'   : RM.danger,
            };
            const colors = d.labels.map(l => colMap[l] || RM.azulClaro);
            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'doughnut',
                data: {
                    labels  : d.labels,
                    datasets: [{ data: d.values, backgroundColor: colors, borderColor: RM.neutroCard, borderWidth: 3, hoverOffset: 8 }],
                },
                options: {
                    ...BASE_OPTS,
                    cutout : '65%',
                    plugins: {
                        ...BASE_OPTS.plugins,
                        legend: { display: false },
                    },
                },
            });
        },
    }));

    // ── 5. IMC (barras) ───────────────────────────────────────────
    Alpine.data('graficoImc', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartImc', (d) => { this.chart?.destroy(); this.draw(d); });
        },
        draw(d) {
            const colors = [RM.azulClinico, RM.verdeSalud, RM.naranja, RM.terracota, RM.danger];
            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'bar',
                data: {
                    labels  : d.labels,
                    datasets: [{ label: 'Pacientes', data: d.values, backgroundColor: colors, borderRadius: 8, borderWidth: 0 }],
                },
                options: {
                    ...BASE_OPTS,
                    plugins: {
                        ...BASE_OPTS.plugins,
                        legend: { display: false },
                        datalabels: {
                            display  : true,
                            anchor   : 'end',
                            align    : 'top',
                            color    : RM.neutro,
                            font     : { weight: 'bold', size: 10 },
                            formatter: (v) => v > 0 ? v : '',
                        },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: RM.neutro, font: { size: 9 } } },
                        y: { beginAtZero: true, ticks: { stepSize: 1, color: RM.neutro }, grid: { color: RM.borde } },
                    },
                },
            });
        },
    }));

    // ── 6. Estado de residentes (doughnut) ────────────────────────
    Alpine.data('graficoEstados', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartEstados', (d) => { this.chart?.destroy(); this.draw(d); });
        },
        draw(d) {
            const palette = [
                RM.verdeSalud, RM.azulClinico, RM.verdeSuave, RM.morado,
                RM.naranja, RM.terracota, RM.danger, RM.salmon, RM.azulClaro,
            ];
            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'doughnut',
                data: {
                    labels  : d.labels,
                    datasets: [{
                        data            : d.values,
                        backgroundColor : d.labels.map((_, i) => palette[i % palette.length]),
                        borderColor     : RM.neutroCard,
                        borderWidth     : 3,
                        hoverOffset     : 8,
                    }],
                },
                options: {
                    ...BASE_OPTS,
                    cutout : '60%',
                    plugins: {
                        ...BASE_OPTS.plugins,
                        legend: {
                            position: 'bottom',
                            labels  : { ...BASE_OPTS.plugins.legend.labels, boxWidth: 10, padding: 6, font: { size: 9 } }
                        },
                    },
                },
            });
        },
    }));

    // ── 7. Notas por tipo de nota (barras) ────────────────────────
    Alpine.data('graficoNotasTipo', (initial) => ({
        chart: null,
        init() {
            this.draw(initial);
            this.$watch('$wire.chartNotasTipo', (d) => { this.chart?.destroy(); this.draw(d); });
        },
        draw(d) {
            const colors = [RM.verdeSalud, RM.azulClinico, RM.azulProfundo, RM.morado, RM.danger, RM.terracota];
            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'bar',
                data: {
                    labels  : d.labels,
                    datasets: [{ label: 'Notas', data: d.values, backgroundColor: colors, borderRadius: 8, borderWidth: 0 }],
                },
                options: {
                    ...BASE_OPTS,
                    plugins: {
                        ...BASE_OPTS.plugins,
                        legend: { display: false },
                        datalabels: {
                            display  : true,
                            anchor   : 'end',
                            align    : 'top',
                            color    : RM.neutro,
                            font     : { weight: 'bold', size: 10 },
                            formatter: (v) => v > 0 ? v : '',
                        },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: RM.neutro, font: { size: 9 } } },
                        y: { beginAtZero: true, ticks: { stepSize: 1, color: RM.neutro }, grid: { color: RM.borde } },
                    },
                },
            });
        },
    }));
</script>
    @endscript
</div>
