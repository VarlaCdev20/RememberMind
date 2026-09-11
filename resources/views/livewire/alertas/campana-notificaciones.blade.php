<div
    wire:poll.12s="verificarAlertas"
    x-data="{ abierto: false }"
    @click.outside="abierto = false"
    @alerta-nueva.window="
        const info = Array.isArray($event.detail) ? $event.detail[0] : $event.detail;
        if (window.SwalToast) {
            window.SwalToast.fire({
                icon: info.nivel === 'CRITICO' ? 'error' : (info.nivel === 'ALTO' ? 'warning' : 'info'),
                title: info.titulo,
                text: info.mensaje,
                timer: 6000
            });
        }
    "
    @alerta-atendida.window="
        if (window.SwalToast) {
            window.SwalToast.fire({
                icon: 'success',
                title: 'Alerta en atención',
                timer: 3000
            });
        }
    "
    @alerta-cerrada.window="
        if (window.SwalToast) {
            window.SwalToast.fire({
                icon: 'success',
                title: 'Alerta cerrada con éxito',
                timer: 3000
            });
        }
    "
    class="relative inline-block text-left"
>
    {{-- Botón Campana --}}
    <button
        type="button"
        @click="abierto = !abierto"
        class="rm-btn-icon relative transition-all duration-200"
        :class="{ 'bg-fondo-hover ring-2 ring-boton-acento/40': abierto }"
        aria-label="Notificaciones y Alertas"
        title="Alertas y Notificaciones Clínicas"
    >
        <i class="ph-bold ph-bell text-lg transition-transform" :class="{ 'rotate-12': {{ $conteoAbiertas > 0 ? 'true' : 'false' }} }"></i>

        @if($conteoAbiertas > 0)
            <span class="absolute -top-1 -right-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-600 px-1 text-[9px] font-black text-white shadow-sm ring-2 ring-fondo-card {{ $alertas->where('nivel', 'CRITICO')->isNotEmpty() ? 'animate-bounce' : 'animate-pulse' }}">
                {{ $conteoAbiertas > 99 ? '99+' : $conteoAbiertas }}
            </span>
        @endif
    </button>

    {{-- Panel Desplegable de Notificaciones --}}
    <div
        x-show="abierto"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        style="display: none;"
        class="dropdown-institucional absolute right-0 mt-3 w-88 sm:w-[460px] rounded-2xl border border-borde bg-fondo-card/95 backdrop-blur-xl shadow-2xl z-50 overflow-hidden"
    >
        {{-- Encabezado del Panel --}}
        <div class="flex items-center justify-between border-b border-borde/70 px-4 py-3 bg-fondo-panel/80">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-red-500/10 text-red-600 dark:bg-red-950/60 dark:text-red-400">
                    <i class="ph-bold ph-bell-ringing text-base"></i>
                </span>
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-titulo">Notificaciones Clínicas</h3>
                    <p class="text-[10px] text-apoyo font-semibold">Ubicación, diagnóstico y seguimiento</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider {{ $conteoAbiertas > 0 ? 'bg-red-500/15 text-red-600 border border-red-500/30' : 'bg-green-500/15 text-green-600 border border-green-500/30' }}">
                    {{ $conteoAbiertas }} pendientes
                </span>
            </div>
        </div>

        {{-- Lista de Notificaciones --}}
        <div class="max-h-[32rem] overflow-y-auto divide-y divide-borde/40 scrollbar-thin">
            {{-- Recordatorios de Medicación --}}
            @foreach($recordatoriosMedicacion as $recordatorio)
                @php
                    $vencida = $recordatorio['estado'] === 'VENCIDA';
                    $adultoRec = $recordatorio['adulto'] ?? null;
                    $codAmRec = $recordatorio['medicacion']?->cod_am ?? $adultoRec?->cod_am;
                    $habNombre = $adultoRec?->habitacion?->nombre ?? ($adultoRec?->habitacion?->codigo ?? ($adultoRec?->cod_habitacion ? 'Hab. '.$adultoRec?->cod_habitacion : 'Sin habitación'));
                    $camaCodigo = $adultoRec?->cama?->codigo ?? ($adultoRec?->cod_cama ? 'Cama '.$adultoRec?->cod_cama : 'Sin cama');
                @endphp
                <div class="flex flex-col gap-2 p-3.5 transition hover:bg-fondo-hover/60 border-l-4 {{ $vencida ? 'border-l-red-500 bg-red-500/[0.03]' : 'border-l-amber-500 bg-amber-500/[0.02]' }}">
                    {{-- Cabecera con Paciente y Estado --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/60 font-black text-[10px] text-blue-700 dark:text-blue-300">
                                    {{ substr($adultoRec?->nombres ?? 'A', 0, 1) }}{{ substr($adultoRec?->ap_paterno ?? 'M', 0, 1) }}
                                </span>
                                <p class="truncate text-xs font-bold text-titulo">
                                    {{ $adultoRec?->nombres }} {{ $adultoRec?->ap_paterno }}
                                </p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-md border px-2 py-0.5 text-[9px] font-black uppercase {{ $vencida ? 'border-red-500/40 bg-red-500/15 text-red-600' : 'border-amber-500/40 bg-amber-500/15 text-amber-600' }}">
                            <i class="ph-bold ph-pill mr-0.5"></i> {{ $vencida ? 'Vencida' : 'Próxima' }}
                        </span>
                    </div>

                    {{-- Dónde está el paciente --}}
                    <div class="flex items-center gap-1.5 text-[10px] font-bold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/40 px-2 py-1 rounded-md border border-blue-200/60 dark:border-blue-900/40">
                        <i class="ph-bold ph-map-pin text-xs"></i>
                        <span>{{ $habNombre }}</span>
                        <span>·</span>
                        <i class="ph-bold ph-bed text-xs"></i>
                        <span>{{ $camaCodigo }}</span>
                    </div>

                    {{-- Cuál es su problema --}}
                    <div class="rounded-lg bg-fondo-panel/70 p-2 text-xs border border-borde/50 space-y-0.5">
                        <p class="font-bold text-titulo truncate">
                            <i class="ph-bold ph-prescription mr-1 text-boton-principal"></i>
                            {{ $recordatorio['medicacion']->nombre_medicamento }} · {{ $recordatorio['medicacion']->dosis }}
                        </p>
                        <p class="text-[10px] text-apoyo font-semibold flex items-center gap-1">
                            <i class="ph-bold ph-clock text-[10px]"></i>
                            Programada: {{ $recordatorio['hora'] }}
                        </p>
                    </div>

                    {{-- Botones de Acción --}}
                    <div class="flex items-center justify-between gap-1.5 pt-1 border-t border-borde/30">
                        <div class="flex items-center gap-1.5">
                            @if($codAmRec)
                                <button type="button"
                                   wire:click="verGraficos('{{ $codAmRec }}')"
                                   class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-[10px] font-bold bg-boton-principal hover:bg-boton-principalHover text-inverso shadow-sm transition active:scale-95 cursor-pointer"
                                   title="Ver gráficos clínicos en barra lateral">
                                    <i class="ph-bold ph-chart-line-up"></i>
                                    <span>Gráficos</span>
                                </button>
                                <button type="button"
                                   wire:click="verUbicacion('{{ $codAmRec }}')"
                                   class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-[10px] font-bold bg-fondo-card hover:bg-fondo-hover text-titulo border border-borde shadow-sm transition active:scale-95 cursor-pointer"
                                   title="Ver ubicación y ficha en barra lateral">
                                    <i class="ph-bold ph-bed text-boton-acento"></i>
                                    <span>Ubicación</span>
                                </button>
                            @endif
                        </div>
                        <a href="{{ route('admin.salud-seguimiento.administracion', ['adulto' => $recordatorio['medicacion']->cod_am]) }}"
                           class="inline-flex items-center gap-1 rounded-lg bg-boton-principal px-2.5 py-1 text-[10px] font-bold text-white shadow-sm transition hover:opacity-90 active:scale-95">
                            <i class="ph-bold ph-check-circle"></i>
                            <span>Registrar</span>
                        </a>
                    </div>
                </div>
            @endforeach

            {{-- Alertas Clínicas --}}
            @forelse($alertas as $alerta)
                @php
                    $esCritica = $alerta->nivel === 'CRITICO';
                    $esAlta = $alerta->nivel === 'ALTO';

                    $badgeNivel = match($alerta->nivel) {
                        'CRITICO' => 'bg-red-600 text-white border-red-700 shadow-sm',
                        'ALTO' => 'bg-amber-500 text-white border-amber-600 shadow-sm',
                        'MEDIO' => 'bg-boton-principal text-inverso border-boton-principal',
                        default => 'bg-slate-600 text-white border-slate-700',
                    };

                    $bordeIzquierdo = match($alerta->nivel) {
                        'CRITICO' => 'border-l-4 border-l-red-600 bg-red-500/[0.04] dark:bg-red-950/20',
                        'ALTO' => 'border-l-4 border-l-amber-500 bg-amber-500/[0.03] dark:bg-amber-950/20',
                        'MEDIO' => 'border-l-4 border-l-blue-500 bg-blue-500/[0.02]',
                        default => 'border-l-4 border-l-slate-400',
                    };

                    $badgeEstado = $alerta->estado === 'EN_ATENCION'
                        ? 'bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30'
                        : 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20';

                    $habNombre = $alerta->adultoMayor?->habitacion?->nombre ?? ($alerta->adultoMayor?->habitacion?->codigo ?? ($alerta->adultoMayor?->cod_habitacion ? 'Hab. '.$alerta->adultoMayor?->cod_habitacion : 'Sin habitación'));
                    $camaCodigo = $alerta->adultoMayor?->cama?->codigo ?? ($alerta->adultoMayor?->cod_cama ? 'Cama '.$alerta->adultoMayor?->cod_cama : 'Sin cama');
                    $habUbicacion = $alerta->adultoMayor?->habitacion?->ubicacion;
                @endphp

                <div class="p-3.5 transition hover:bg-fondo-hover/60 flex flex-col gap-2.5 {{ $bordeIzquierdo }}">
                    {{-- 1. Encabezado: Paciente y Estado/Severidad --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl font-black text-[10px] {{ $esCritica ? 'bg-red-100 text-red-700 dark:bg-red-900/60 dark:text-red-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200' }}">
                                    {{ substr($alerta->adultoMayor?->nombres ?? 'A', 0, 1) }}{{ substr($alerta->adultoMayor?->ap_paterno ?? 'M', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-titulo truncate">
                                        {{ $alerta->adultoMayor?->nombres }} {{ $alerta->adultoMayor?->ap_paterno }} {{ $alerta->adultoMayor?->ap_materno }}
                                    </p>
                                    <p class="text-[10px] text-apoyo font-mono font-semibold">
                                        ID: {{ $alerta->cod_am }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="rounded-md px-2 py-0.5 text-[9px] font-black uppercase tracking-wider border {{ $badgeNivel }} {{ $esCritica ? 'animate-pulse' : '' }}">
                                {{ $alerta->nivel }}
                            </span>
                            <span class="rounded-md px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $badgeEstado }}">
                                {{ $alerta->estado === 'EN_ATENCION' ? 'EN ATENCIÓN' : 'ABIERTA' }}
                            </span>
                        </div>
                    </div>

                    {{-- 2. DÓNDE ESTÁ EL PACIENTE (Ubicación física precisa) --}}
                    <div class="flex items-center flex-wrap gap-1.5 text-[11px] font-bold text-parrafo bg-fondo-panel px-2.5 py-1.5 rounded-lg border border-borde shadow-sm">
                        <i class="ph-bold ph-door text-xs text-apoyo"></i>
                        <span>{{ $habNombre }}</span>
                        <span class="text-blue-400">·</span>
                        <i class="ph-bold ph-bed text-xs text-boton-acento"></i>
                        <span>{{ $camaCodigo }}</span>
                        @if($habUbicacion)
                            <span class="text-[10px] text-apoyo font-normal">({{ $habUbicacion }})</span>
                        @endif
                    </div>

                    {{-- 3. CUÁL ES SU PROBLEMA (Diagnóstico y Motivo Clínico) --}}
                    <div class="space-y-1">
                        <div class="flex items-center gap-1.5 text-xs font-black {{ $esCritica ? 'text-red-600 dark:text-red-400' : 'text-titulo' }}">
                            <i class="ph-bold ph-warning-octagon text-sm {{ $esCritica ? 'text-red-600' : 'text-amber-500' }}"></i>
                            <span>{{ $alerta->tipo_alerta }}</span>
                            <span class="text-[10px] text-apoyo font-semibold ml-auto">{{ $alerta->origen }}</span>
                        </div>

                        <div class="rounded-lg bg-fondo-panel/80 p-2.5 border border-borde/60 text-xs text-parrafo leading-relaxed font-medium">
                            {{ $alerta->motivo }}
                        </div>
                    </div>

                    {{-- 4. TIEMPO Y BOTONES DE ACCIÓN MEJORADOS --}}
                    <div class="flex items-center justify-between gap-1.5 pt-1.5 border-t border-borde/40 text-[10px]">
                        <span class="text-meta font-medium flex items-center gap-1">
                            <i class="ph-bold ph-clock text-xs"></i>
                            {{ $alerta->created_at?->diffForHumans() }}
                        </span>

                        <div class="flex items-center flex-wrap gap-1.5">
                            {{-- Botón 1: Gráfico Clínico del paciente --}}
                            <button
                                type="button"
                                wire:click="verGraficos('{{ $alerta->cod_am }}')"
                                class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 font-bold bg-boton-principal hover:bg-boton-principalHover text-inverso shadow-sm transition active:scale-95 text-[10px] cursor-pointer"
                                title="Ver gráficos clínicos en barra lateral"
                            >
                                <i class="ph-bold ph-chart-line-up text-xs"></i>
                                <span>Gráficos</span>
                            </button>

                            {{-- Botón 2: Ficha y Ubicación del paciente --}}
                            <button
                                type="button"
                                wire:click="verUbicacion('{{ $alerta->cod_am }}')"
                                class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 font-bold text-titulo bg-fondo-panel hover:bg-fondo-hover border border-borde shadow-sm transition active:scale-95 text-[10px] cursor-pointer"
                                title="Ver ubicación y ficha en barra lateral"
                            >
                                <i class="ph-bold ph-bed text-xs text-boton-acento"></i>
                                <span>Ubicación</span>
                            </button>

                            {{-- Botón 3: Atender (si está ABIERTA) --}}
                            @if($puedeAtender && $alerta->estado === 'ABIERTA')
                                <button
                                    type="button"
                                    wire:click="abrirAtender('{{ $alerta->cod_alerta }}')"
                                    class="inline-flex items-center gap-1 rounded-lg bg-boton-principal hover:bg-boton-principalHover px-2.5 py-1 font-bold text-inverso shadow-sm transition active:scale-95 text-[10px]"
                                    title="Iniciar atención de la alerta"
                                >
                                    <i class="ph-bold ph-stethoscope text-xs"></i>
                                    <span>Atender</span>
                                </button>
                            @endif

                            {{-- Botón 4: Cerrar (si está ABIERTA o EN_ATENCION) --}}
                            @if($puedeCerrar && in_array($alerta->estado, ['ABIERTA', 'EN_ATENCION']))
                                <button
                                    type="button"
                                    wire:click="abrirCerrar('{{ $alerta->cod_alerta }}')"
                                    class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 px-2.5 py-1 font-bold text-white shadow-sm transition active:scale-95 text-[10px]"
                                    title="Resolver y cerrar alerta"
                                >
                                    <i class="ph-bold ph-check text-xs"></i>
                                    <span>Cerrar</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                @if($recordatoriosMedicacion->isEmpty())
                    <div class="p-8 text-center text-apoyo">
                        <div class="mx-auto mb-2.5 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                            <i class="ph-bold ph-check-circle text-2xl"></i>
                        </div>
                        <p class="text-xs font-bold text-titulo">Todo en orden</p>
                        <p class="text-[11px] text-apoyo mt-0.5">No hay alertas clínicas activas ni tomas de medicación pendientes.</p>
                    </div>
                @endif
            @endforelse
        </div>

        {{-- Pie del Panel --}}
        <div class="border-t border-borde/70 p-3 bg-fondo-panel/70 flex items-center justify-between gap-2">
            <a
                href="{{ route('admin.alertas-clinicas.index') }}"
                class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-fondo-card px-3 py-2 text-xs font-bold text-titulo shadow-sm border border-borde transition hover:bg-fondo-hover hover:border-borde-focus active:scale-95"
            >
                <i class="ph-bold ph-list-bullets text-xs"></i>
                <span>Ver Panel General de Alertas</span>
                <i class="ph-bold ph-arrow-right text-xs"></i>
            </a>
        </div>
    </div>

    {{-- Modal Atender Alerta --}}
    @if($modalAtencion)
        @php
            $alertaAct = $alertas->firstWhere('cod_alerta', $alertaIdAccion);
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl border border-borde bg-fondo-card p-5 shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-borde">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-fondo-app text-boton-principal border border-borde">
                            <i class="ph-bold ph-stethoscope text-base"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-titulo">Atender Alerta</h3>
                            <span class="text-[10px] font-mono text-apoyo">#{{ $alertaIdAccion }}</span>
                        </div>
                    </div>
                    <button type="button" wire:click="cerrarModales" class="text-meta hover:text-titulo p-1">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>

                @if($alertaAct)
                    <div class="rounded-xl bg-fondo-panel/80 p-3 border border-borde/60 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-titulo">{{ $alertaAct->adultoMayor?->nombres }} {{ $alertaAct->adultoMayor?->ap_paterno }}</span>
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded {{ $alertaAct->nivel === 'CRITICO' ? 'bg-red-600 text-white' : 'bg-amber-500 text-white' }}">{{ $alertaAct->nivel }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[10px] font-bold text-blue-700 dark:text-blue-300">
                            <i class="ph-bold ph-door"></i>
                            <span>{{ $alertaAct->adultoMayor?->habitacion?->nombre ?? ($alertaAct->adultoMayor?->habitacion?->codigo ?? 'Habitación') }}</span>
                            <span>·</span>
                            <i class="ph-bold ph-bed"></i>
                            <span>{{ $alertaAct->adultoMayor?->cama?->codigo ?? 'Cama' }}</span>
                        </div>
                        <p class="text-[11px] text-parrafo leading-relaxed font-medium">
                            <span class="font-bold">{{ $alertaAct->tipo_alerta }}:</span> {{ $alertaAct->motivo }}
                        </p>
                        <div class="pt-1 flex items-center gap-2">
                            <button type="button" wire:click="verGraficos('{{ $alertaAct->cod_am }}')" class="inline-flex items-center gap-1 text-[10px] font-bold text-boton-principal hover:underline cursor-pointer">
                                <i class="ph-bold ph-chart-line-up"></i> Ver Gráficos Clínicos
                            </button>
                            <span>·</span>
                            <button type="button" wire:click="verUbicacion('{{ $alertaAct->cod_am }}')" class="inline-flex items-center gap-1 text-[10px] font-bold text-boton-acento hover:underline cursor-pointer">
                                <i class="ph-bold ph-bed"></i> Ficha y Ubicación
                            </button>
                        </div>
                    </div>
                @endif

                <div class="space-y-2">
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-wider text-apoyo">Acción o Intervención Inicial *</span>
                        <textarea
                            wire:model="accionTomada"
                            placeholder="Describa la atención inmediata realizada al paciente..."
                            rows="3"
                            class="rm-input mt-1.5 w-full rounded-xl p-2.5 text-xs bg-fondo-app border border-borde text-titulo placeholder-apoyo focus:border-boton-principal outline-none"
                        ></textarea>
                    </label>
                </div>

                <div class="flex justify-end gap-2 border-t border-borde pt-3">
                    <button
                        type="button"
                        wire:click="cerrarModales"
                        class="rounded-xl border border-borde px-3.5 py-2 text-xs font-bold text-titulo hover:bg-fondo-hover transition cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        wire:click="atenderAlerta('{{ $alertaIdAccion }}')"
                        class="rounded-xl bg-boton-principal px-4 py-2 text-xs font-bold text-white hover:opacity-90 shadow-sm transition cursor-pointer"
                    >
                        Confirmar Atención
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Cerrar Alerta --}}
    @if($modalCierre)
        @php
            $alertaAct = $alertas->firstWhere('cod_alerta', $alertaIdAccion);
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl border border-borde bg-fondo-card p-5 shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-borde">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-600">
                            <i class="ph-bold ph-check-circle text-base"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-titulo">Cerrar y Archivar Alerta</h3>
                            <span class="text-[10px] font-mono text-apoyo">#{{ $alertaIdAccion }}</span>
                        </div>
                    </div>
                    <button type="button" wire:click="cerrarModales" class="text-meta hover:text-titulo p-1">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>

                @if($alertaAct)
                    <div class="rounded-xl bg-fondo-panel/80 p-3 border border-borde/60 space-y-1.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-titulo">{{ $alertaAct->adultoMayor?->nombres }} {{ $alertaAct->adultoMayor?->ap_paterno }}</span>
                            <span class="text-[10px] font-bold text-blue-700 dark:text-blue-300">
                                {{ $alertaAct->adultoMayor?->habitacion?->nombre ?? 'Habitación' }} · {{ $alertaAct->adultoMayor?->cama?->codigo ?? 'Cama' }}
                            </span>
                        </div>
                        <p class="text-[11px] text-parrafo leading-relaxed font-medium">
                            <span class="font-bold">{{ $alertaAct->tipo_alerta }}:</span> {{ $alertaAct->motivo }}
                        </p>
                    </div>
                @endif

                <div class="space-y-2">
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-wider text-apoyo">Observación de Cierre / Resolución *</span>
                        <textarea
                            wire:model="observacionCierre"
                            placeholder="Describa cómo se resolvió la alerta clínica..."
                            rows="3"
                            class="rm-input mt-1.5 w-full rounded-xl p-2.5 text-xs bg-fondo-app border border-borde text-titulo placeholder-apoyo focus:border-boton-acento outline-none"
                        ></textarea>
                    </label>
                </div>

                <div class="flex justify-end gap-2 border-t border-borde pt-3">
                    <button
                        type="button"
                        wire:click="cerrarModales"
                        class="rounded-xl border border-borde px-3.5 py-2 text-xs font-bold text-titulo hover:bg-fondo-hover transition cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        wire:click="cerrarAlerta('{{ $alertaIdAccion }}')"
                        class="rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-2 text-xs font-bold text-white shadow-sm transition cursor-pointer"
                    >
                        Confirmar Cierre
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Paneles Laterales Desplegables (Drawers) --}}
    @include('livewire.alertas.modales.drawer-graficos')
    @include('livewire.alertas.modales.drawer-ubicacion')
</div>