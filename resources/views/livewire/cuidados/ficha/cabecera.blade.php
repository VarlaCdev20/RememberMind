{{-- CABECERA INSTITUCIONAL CLÍNICA COMPACTA Y ACCIONES PRINCIPALES --}}
<div class="rounded-3xl border rm-page-header-card p-4 sm:p-5 shadow-sm">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        {{-- Datos Primarios del Residente --}}
        <div class="flex items-start gap-4 min-w-0">
            {{-- Avatar / Foto --}}
            @if($adultoMayor->foto && \Illuminate\Support\Facades\Storage::disk('public')->exists($adultoMayor->foto))
                <img src="{{ asset('storage/' . $adultoMayor->foto) }}"
                     alt="{{ $adultoMayor->nombres }}"
                     class="h-16 w-16 rounded-2xl object-cover border-2 border-borde shadow-sm shrink-0">
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-800 border-2 border-blue-200 font-black text-xl shadow-sm shrink-0">
                    {{ strtoupper(substr($adultoMayor->nombres, 0, 1) . substr($adultoMayor->ap_paterno, 0, 1)) }}
                </div>
            @endif

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl font-bold tracking-tight text-titulo">
                        {{ $adultoMayor->nombre_completo }}
                    </h1>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10.5px] font-semibold border {{ $adultoMayor->estado_badge_color }}">
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        Estado: {{ $adultoMayor->estado_humano }}
                    </span>
                </div>

                <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-parrafo">
                    <span><strong>{{ $adultoMayor->edad_texto }}</strong> ({{ $adultoMayor->fecha_nac ? \Carbon\Carbon::parse($adultoMayor->fecha_nac)->format('d/m/Y') : 'Sin fecha nac.' }})</span>
                    <span class="text-apoyo">·</span>
                    <span>CI: <strong>{{ $adultoMayor->ci ?? 'No registrado' }}</strong></span>
                    <span class="text-apoyo">·</span>
                    <span>Habitación: <strong>{{ $adultoMayor->habitacion_texto }}</strong></span>
                    <span class="text-apoyo">·</span>
                    <span>Cama: <strong>{{ $adultoMayor->cama_texto }}</strong></span>
                </div>

                {{-- Badges clínicos esenciales: Alergias, Nivel de cuidado, Riesgos --}}
                <div class="mt-2.5 flex flex-wrap items-center gap-2">
                    {{-- Alergias relevantes --}}
                    @if(!empty($adultoMayor->alergias))
                        <span class="inline-flex items-center gap-1 rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 border border-rose-200" title="Alergias conocidas">
                            <i class="ph-bold ph-warning"></i> Alergias: {{ $adultoMayor->alergias }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-lg bg-slate-50 px-2 py-0.5 text-[11px] font-medium text-slate-600 border border-slate-200">
                            <i class="ph-bold ph-check"></i> Sin alergias conocidas
                        </span>
                    @endif

                    {{-- Nivel de cuidado --}}
                    <span class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 border border-blue-200">
                        <i class="ph-bold ph-shield-check"></i> Cuidado: {{ $adultoMayor->nivel_cuidado ?? 'Vigilancia moderada' }}
                    </span>

                    {{-- Riesgos importantes --}}
                    @php
                        $ultFunc = $adultoMayor->valoracionesFuncionales->sortByDesc('fecha_valoracion')->first();
                        $riesgoCaida = $ultFunc->riesgo_caida ?? 'Bajo';
                    @endphp
                    @if($riesgoCaida && in_array(strtoupper($riesgoCaida), ['ALTO', 'MEDIO', 'MODERADO']))
                        <span class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 border border-amber-200">
                            <i class="ph-bold ph-person-simple-walk"></i> Caídas: {{ $riesgoCaida }}
                        </span>
                    @endif

                    {{-- Alertas activas badge rápido --}}
                    @php
                        $activasCab = $adultoMayor->alertas->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->count();
                    @endphp
                    @if($activasCab > 0)
                        <span class="inline-flex items-center gap-1 rounded-lg bg-rose-100 px-2.5 py-1 text-xs font-bold text-rose-800 border border-rose-300 animate-pulse">
                            <i class="ph-bold ph-bell-ringing"></i> {{ $activasCab }} {{ $activasCab === 1 ? 'alerta activa' : 'alertas activas' }}
                        </span>
                    @endif

                    <span class="inline-flex items-center gap-1 rounded-lg border border-borde bg-fondo-card px-2.5 py-1 text-xs font-bold text-parrafo">
                        <i class="ph-bold ph-map-pin"></i> {{ str_replace('_', ' ', $adultoMayor->estado_operativo ?: 'EN_CENTRO') }}
                    </span>
                    @foreach($adultoMayor->dispositivosActivos as $dispositivo)
                        <span class="inline-flex items-center gap-1 rounded-lg border border-borde bg-fondo-card px-2.5 py-1 text-xs font-bold text-parrafo">
                            <i class="ph-bold ph-first-aid-kit"></i> {{ str_replace('_', ' ', $dispositivo->tipo) }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

                {{-- ACCIONES UNIFICADAS ENFERMERIA (REGLA 6 DESIGN SYSTEM) --}}
        <div class="flex flex-wrap items-center gap-2 pt-2 lg:pt-0 shrink-0">
            {{-- DROPDOWN UNIFICADO: + REGISTRAR --}}
            <div class="relative" x-data="{ openRegistrar: false }" @click.outside="openRegistrar = false">
                <button type="button"
                        @click="openRegistrar = !openRegistrar"
                        class="rm-btn-primary px-3.5 py-2 text-xs font-bold shadow-sm inline-flex items-center gap-2">
                    <i class="ph-bold ph-plus-circle text-base"></i>
                    <span>+ REGISTRAR</span>
                    <i class="ph-bold ph-caret-down text-xs transition-transform" :class="openRegistrar ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="openRegistrar"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                     x-cloak
                     class="absolute right-0 mt-1.5 w-64 rounded-xl border border-borde bg-fondo-panel p-1.5 shadow-xl z-50 divide-y divide-borde/40 text-xs">
                    <div class="p-1 text-[11px] font-bold uppercase tracking-wider text-apoyo">
                        Registro Clínico y Cuidado
                    </div>
                    <div class="py-1 space-y-0.5">
                        <button type="button"
                                wire:click="abrirModalSignos"
                                @click="openRegistrar = false"
                                class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-titulo hover:bg-fondo-card font-medium transition">
                            <i class="ph-bold ph-heartbeat text-rose-500 text-sm"></i>
                            <span>Registrar signos (Signos vitales)</span>
                        </button>
                        <a href="{{ route('admin.enfermeria.registros', ['adulto' => $adultoMayor->cod_am, 'categoria' => 'DOLOR']) }}"
                           class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-titulo hover:bg-fondo-card font-medium transition">
                            <i class="ph-bold ph-smiley-sad text-amber-500 text-sm"></i>
                            <span>Escala de dolor</span>
                        </a>
                        <a href="{{ route('admin.enfermeria.registros', ['adulto' => $adultoMayor->cod_am, 'categoria' => 'ALIMENTACION']) }}"
                           class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-titulo hover:bg-fondo-card font-medium transition">
                            <i class="ph-bold ph-fork-knife text-emerald-500 text-sm"></i>
                            <span>Alimentación y nutrición</span>
                        </a>
                        <a href="{{ route('admin.enfermeria.registros', ['adulto' => $adultoMayor->cod_am, 'categoria' => 'HIDRATACION']) }}"
                           class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-titulo hover:bg-fondo-card font-medium transition">
                            <i class="ph-bold ph-drop text-sky-500 text-sm"></i>
                            <span>Hidratación</span>
                        </a>
                        <a href="{{ route('admin.enfermeria.registros', ['adulto' => $adultoMayor->cod_am, 'categoria' => 'ELIMINACION']) }}"
                           class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-titulo hover:bg-fondo-card font-medium transition">
                            <i class="ph-bold ph-toilet text-indigo-500 text-sm"></i>
                            <span>Eliminación (Diuresis / Deposición)</span>
                        </a>
                        <a href="{{ route('admin.enfermeria.registros', ['adulto' => $adultoMayor->cod_am, 'categoria' => 'HIGIENE']) }}"
                           class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-titulo hover:bg-fondo-card font-medium transition">
                            <i class="ph-bold ph-sparkle text-teal-500 text-sm"></i>
                            <span>Higiene y confort</span>
                        </a>
                        <a href="{{ route('admin.enfermeria.registros', ['adulto' => $adultoMayor->cod_am, 'categoria' => 'MOVILIDAD']) }}"
                           class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-titulo hover:bg-fondo-card font-medium transition">
                            <i class="ph-bold ph-person-simple-walk text-blue-500 text-sm"></i>
                            <span>Movilidad y cambios posturales</span>
                        </a>
                        <a href="{{ route('admin.enfermeria.registros', ['adulto' => $adultoMayor->cod_am, 'categoria' => 'SUENO']) }}"
                           class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-titulo hover:bg-fondo-card font-medium transition">
                            <i class="ph-bold ph-moon-stars text-violet-500 text-sm"></i>
                            <span>Sueño y descanso</span>
                        </a>
                        <a href="{{ route('admin.enfermeria.registros', ['adulto' => $adultoMayor->cod_am, 'categoria' => 'PROCEDIMIENTO']) }}"
                           class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-titulo hover:bg-fondo-card font-medium transition">
                            <i class="ph-bold ph-bandaids text-cyan-500 text-sm"></i>
                            <span>Procedimientos de enfermería</span>
                        </a>
                        <a href="{{ route('admin.enfermeria.registros', ['adulto' => $adultoMayor->cod_am, 'categoria' => 'LESION']) }}"
                           class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-titulo hover:bg-fondo-card font-medium transition">
                            <i class="ph-bold ph-shield-warning text-orange-500 text-sm"></i>
                            <span>Lesiones y curaciones (UPP)</span>
                        </a>
                        <button type="button"
                                wire:click="abrirModalIncidente"
                                @click="openRegistrar = false"
                                class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 font-bold transition">
                            <i class="ph-bold ph-warning-octagon text-rose-600 text-sm"></i>
                            <span>Reportar incidente / Caídas</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- 2 ACCIONES RAPIDAS PRINCIPALES --}}
            <button type="button"
                    wire:click="abrirModalMedicacion"
                    wire:loading.attr="disabled"
                    class="rm-btn-secondary px-3 py-2 text-xs font-semibold">
                <i class="ph-bold ph-pill text-emerald-600 text-sm"></i>
                <span>Administrar medicación</span>
            </button>

            <button type="button"
                    wire:click="abrirModalSeguimiento"
                    wire:loading.attr="disabled"
                    class="rm-btn-secondary px-3 py-2 text-xs font-semibold">
                <i class="ph-bold ph-clipboard-text text-sky-600 text-sm"></i>
                <span>Registrar seguimiento</span>
            </button>
        </div>
    </div>

    {{-- BARRA DE PESTAÑAS (ORDEN ESTRICTO DE 7 PESTAÑAS) --}}
    <div class="mt-6 border-t border-borde pt-3 flex overflow-x-auto gap-2 text-xs font-medium text-apoyo no-scrollbar">
        {{-- 1. Resumen clínico --}}
        <button type="button"
                @click="activeTab = 'resumen'; $wire.cambiarTab('resumen')"
                :class="activeTab === 'resumen' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'bg-fondo-card text-parrafo hover:bg-borde/50'"
                class="flex items-center gap-2 rounded-xl px-4 py-2.5 transition whitespace-nowrap">
            <i class="ph-bold ph-clipboard text-sm"></i>
            <span>1. Resumen clínico</span>
        </button>

        {{-- 2. Signos vitales --}}
        <button type="button"
                @click="activeTab = 'signos'; $wire.cambiarTab('signos')"
                :class="activeTab === 'signos' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'bg-fondo-card text-parrafo hover:bg-borde/50'"
                class="flex items-center gap-2 rounded-xl px-4 py-2.5 transition whitespace-nowrap">
            <i class="ph-bold ph-heartbeat text-sm"></i>
            <span>2. Signos vitales</span>
            <span class="rounded-full px-1.5 py-0.2 text-[10px] font-bold"
                  :class="activeTab === 'signos' ? 'bg-white/20 text-white' : 'bg-fondo-panel text-apoyo border border-borde'">
                {{ $adultoMayor->signosVitales->count() }}
            </span>
        </button>

        {{-- 3. Medicación --}}
        <button type="button"
                @click="activeTab = 'medicacion'; $wire.cambiarTab('medicacion')"
                :class="activeTab === 'medicacion' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'bg-fondo-card text-parrafo hover:bg-borde/50'"
                class="flex items-center gap-2 rounded-xl px-4 py-2.5 transition whitespace-nowrap">
            <i class="ph-bold ph-pill text-sm"></i>
            <span>3. Medicación</span>
            <span class="rounded-full px-1.5 py-0.2 text-[10px] font-bold"
                  :class="activeTab === 'medicacion' ? 'bg-white/20 text-white' : 'bg-fondo-panel text-apoyo border border-borde'">
                {{ $adultoMayor->medicaciones->count() }}
            </span>
        </button>

        {{-- 4. Cuidados --}}
        <button type="button"
                @click="activeTab = 'cuidados'; $wire.cambiarTab('cuidados')"
                :class="(activeTab === 'cuidados' || activeTab === 'cuidado') ? 'bg-blue-600 text-white font-bold shadow-sm' : 'bg-fondo-card text-parrafo hover:bg-borde/50'"
                class="flex items-center gap-2 rounded-xl px-4 py-2.5 transition whitespace-nowrap">
            <i class="ph-bold ph-hand-heart text-sm"></i>
            <span>4. Cuidados</span>
            <span class="rounded-full px-1.5 py-0.2 text-[10px] font-bold"
                  :class="(activeTab === 'cuidados' || activeTab === 'cuidado') ? 'bg-white/20 text-white' : 'bg-fondo-panel text-apoyo border border-borde'">
                {{ $adultoMayor->tareasActuales->count() }}
            </span>
        </button>

        {{-- 5. Seguimiento --}}
        <button type="button"
                @click="activeTab = 'seguimiento'; $wire.cambiarTab('seguimiento')"
                :class="activeTab === 'seguimiento' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'bg-fondo-card text-parrafo hover:bg-borde/50'"
                class="flex items-center gap-2 rounded-xl px-4 py-2.5 transition whitespace-nowrap">
            <i class="ph-bold ph-note-pencil text-sm"></i>
            <span>5. Seguimiento</span>
            <span class="rounded-full px-1.5 py-0.2 text-[10px] font-bold"
                  :class="activeTab === 'seguimiento' ? 'bg-white/20 text-white' : 'bg-fondo-panel text-apoyo border border-borde'">
                {{ $adultoMayor->seguimientosDiarios->count() }}
            </span>
        </button>

        {{-- 6. Alertas --}}
        <button type="button"
                @click="activeTab = 'alertas'; $wire.cambiarTab('alertas')"
                :class="activeTab === 'alertas' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'bg-fondo-card text-parrafo hover:bg-borde/50'"
                class="flex items-center gap-2 rounded-xl px-4 py-2.5 transition whitespace-nowrap">
            <i class="ph-bold ph-warning-circle text-sm"></i>
            <span>6. Alertas</span>
            @php $totalAlertasAct = $adultoMayor->alertas->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->count(); @endphp
            @if($totalAlertasAct > 0)
                <span class="rounded-full px-1.5 py-0.2 text-[10px] font-black bg-rose-500 text-white">
                    {{ $totalAlertasAct }}
                </span>
            @else
                <span class="rounded-full px-1.5 py-0.2 text-[10px] font-bold"
                      :class="activeTab === 'alertas' ? 'bg-white/20 text-white' : 'bg-fondo-panel text-apoyo border border-borde'">
                    0
                </span>
            @endif
        </button>

        {{-- 7. Historial 360° --}}
        <button type="button"
                @click="activeTab = 'historial'; $wire.cambiarTab('historial')"
                :class="activeTab === 'historial' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'bg-fondo-card text-parrafo hover:bg-borde/50'"
                class="flex items-center gap-2 rounded-xl px-4 py-2.5 transition whitespace-nowrap">
            <i class="ph-bold ph-clock-counter-clockwise text-sm"></i>
            <span>7. Historial 360°</span>
        </button>
    </div>
</div>
