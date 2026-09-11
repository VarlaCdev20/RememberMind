{{-- CABECERA INSTITUCIONAL CLÍNICA COMPACTA Y ACCIONES PRINCIPALES --}}
<div class="rounded-3xl border border-borde bg-fondo-panel p-5 shadow-panel">
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
                        {{ $adultoMayor->nombres }} {{ $adultoMayor->ap_paterno }} {{ $adultoMayor->ap_materno }}
                    </h1>
                    <span class="rounded-full bg-fondo-card px-2.5 py-0.5 text-[10px] font-bold uppercase text-apoyo border border-borde">
                        {{ $adultoMayor->cod_am }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase border {{ in_array($adultoMayor->estadoTexto ?? $adultoMayor->estado, ['ACTIVO', 'ACTIVA']) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ in_array($adultoMayor->estadoTexto ?? $adultoMayor->estado, ['ACTIVO', 'ACTIVA']) ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        Estado: {{ $adultoMayor->estadoTexto ?? $adultoMayor->estado }}
                    </span>
                </div>

                <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-parrafo">
                    <span><strong>{{ \Carbon\Carbon::parse($adultoMayor->fecha_nac)->age }} años</strong> ({{ \Carbon\Carbon::parse($adultoMayor->fecha_nac)->format('d/m/Y') }})</span>
                    <span class="text-apoyo">·</span>
                    <span>CI: <strong>{{ $adultoMayor->ci }}</strong></span>
                    <span class="text-apoyo">·</span>
                    <span>Habitación: <strong>{{ $adultoMayor->habitacion->codigo ?? $adultoMayor->habitacion->numero ?? 'S/H' }}</strong></span>
                    <span class="text-apoyo">·</span>
                    <span>Cama: <strong>{{ $adultoMayor->cama->codigo ?? $adultoMayor->cama->numero ?? 'S/C' }}</strong></span>
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
                </div>
            </div>
        </div>

        {{-- ACCIONES PRINCIPALES (ÚNICA UBICACIÓN EN TODA LA FICHA) --}}
        <div class="flex flex-wrap items-center gap-2 pt-2 lg:pt-0 shrink-0">
            <button type="button"
                    wire:click="abrirModalSignos"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-3.5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition">
                <i class="ph-bold ph-heartbeat text-base"></i>
                <span>Registrar signos</span>
            </button>

            <button type="button"
                    wire:click="abrirModalMedicacion"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-3.5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition">
                <i class="ph-bold ph-pill text-base"></i>
                <span>Administrar medicación</span>
            </button>

            <button type="button"
                    wire:click="abrirModalSeguimiento"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-3.5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-sky-700 transition">
                <i class="ph-bold ph-clipboard-text text-base"></i>
                <span>Registrar seguimiento</span>
            </button>

            <button type="button"
                    wire:click="abrirModalIncidente"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-3.5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-rose-700 transition">
                <i class="ph-bold ph-warning-octagon text-base"></i>
                <span>Reportar incidente</span>
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
