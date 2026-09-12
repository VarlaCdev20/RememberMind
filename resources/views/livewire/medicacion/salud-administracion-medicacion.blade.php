<div class="space-y-6 font-sans pb-12" wire:poll.30s
     x-data="{
        relojPC: {
            hora12: '',
            horaCorta: '',
            segundosDesdeMedianoche: 0,
            init() {
                const tick = () => {
                    const now = new Date();
                    let h = now.getHours();
                    const m = String(now.getMinutes()).padStart(2, '0');
                    const s = String(now.getSeconds()).padStart(2, '0');
                    const ampm = h >= 12 ? 'PM' : 'AM';
                    const h12 = h % 12 || 12;
                    this.hora12 = `${String(h12).padStart(2, '0')}:${m}:${s} ${ampm}`;
                    this.horaCorta = `${String(h12).padStart(2, '0')}:${m} ${ampm}`;
                    this.segundosDesdeMedianoche = (now.getHours() * 3600) + (now.getMinutes() * 60) + now.getSeconds();
                };
                tick();
                setInterval(tick, 1000);
            },
            sePaso(horaStr) {
                if (!horaStr) return false;
                let [horaParte, ampm] = horaStr.trim().split(/\s+/);
                let [h, m] = horaParte.split(':').map(Number);
                if (ampm) {
                    ampm = ampm.toUpperCase();
                    if (ampm === 'PM' && h < 12) h += 12;
                    if (ampm === 'AM' && h === 12) h = 0;
                }
                const segProg = (h * 3600) + ((m || 0) * 60);
                return this.segundosDesdeMedianoche > segProg;
            },
            tiempoDiferenciaFormateado(horaStr) {
                if (!horaStr) return '00h 00m 00s';
                let [horaParte, ampm] = horaStr.trim().split(/\s+/);
                let [h, m] = horaParte.split(':').map(Number);
                if (ampm) {
                    ampm = ampm.toUpperCase();
                    if (ampm === 'PM' && h < 12) h += 12;
                    if (ampm === 'AM' && h === 12) h = 0;
                }
                const segProg = (h * 3600) + ((m || 0) * 60);
                const diffSeg = Math.abs(this.segundosDesdeMedianoche - segProg);
                const horas = Math.floor(diffSeg / 3600);
                const minutos = Math.floor((diffSeg % 3600) / 60);
                const segundos = diffSeg % 60;
                return `${String(horas).padStart(2, '0')}h ${String(minutos).padStart(2, '0')}m ${String(segundos).padStart(2, '0')}s`;
            },
            evaluarHorario(horarioStr, administrado = false) {
                if (!horarioStr) return { estado: 'Programada', diffSeg: 0, sePaso: false, texto: 'Programada' };
                if (administrado) return { estado: 'Administrada', diffSeg: 0, sePaso: false, texto: '✓ Administrada' };
                
                let [horaParte, ampm] = horarioStr.trim().split(/\s+/);
                let [h, m] = horaParte.split(':').map(Number);
                if (ampm) {
                    ampm = ampm.toUpperCase();
                    if (ampm === 'PM' && h < 12) h += 12;
                    if (ampm === 'AM' && h === 12) h = 0;
                }
                const segProg = (h * 3600) + ((m || 0) * 60);
                const diff = this.segundosDesdeMedianoche - segProg;
                const absDiff = Math.abs(diff);
                const horas = Math.floor(absDiff / 3600);
                const minutos = Math.floor((absDiff % 3600) / 60);
                const segundos = absDiff % 60;
                const tiempoHMS = `${String(horas).padStart(2, '0')}h ${String(minutos).padStart(2, '0')}m ${String(segundos).padStart(2, '0')}s`;
                
                if (diff > 0) {
                    return {
                        estado: 'Atrasada',
                        diffSeg: diff,
                        sePaso: true,
                        texto: `⚠️ Se pasó de hora por ${tiempoHMS}`
                    };
                } else if (diff >= -1800) {
                    return {
                        estado: 'Por administrar',
                        diffSeg: absDiff,
                        sePaso: false,
                        texto: diff === 0 ? '⏰ Administrar ahora' : `⏱️ Próxima en ${tiempoHMS}`
                    };
                } else {
                    return {
                        estado: 'Programada',
                        diffSeg: absDiff,
                        sePaso: false,
                        texto: `Programada (en ${tiempoHMS})`
                    };
                }
            }
        },
        init() {
            this.relojPC.init();
        }
     }">
    {{-- ========================================================================= --}}
    {{-- 1. NAVEGACIÓN Y BREADCRUMB INSTITUCIONAL                                   --}}
    {{-- ========================================================================= --}}
    @if($adulto)
        <x-residentes.navegacion-ficha :adulto="$adulto" />
    @endif

    {{-- ========================================================================= --}}
    {{-- 2. CABECERA OPERATIVA DE MEDICACIÓN                                       --}}
    {{-- ========================================================================= --}}
    <header class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-3.5">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 text-[#1E3A8A] shadow-2xs dark:border-blue-900/60 dark:bg-blue-950/50 dark:text-blue-300">
                    <i class="ph-bold ph-pill text-2xl"></i>
                </span>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl font-black tracking-tight text-[var(--rm-text-title)]">
                            Medicación
                        </h1>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                            Administración de Enfermería
                        </span>
                    </div>
                    <p class="text-xs font-semibold text-[var(--rm-text-muted)] mt-1">
                        Administración segura, a tiempo, para su bienestar
                    </p>
                </div>
            </div>

            {{-- Metadata de fecha, turno y alertas --}}
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-xs font-bold text-[var(--rm-text-title)]">
                    <i class="ph-bold ph-calendar text-blue-600 dark:text-blue-400"></i>
                    <span>{{ $fechaCabecera }}</span>
                </div>

<div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-xs font-bold text-[var(--rm-text-title)]">
                    <i class="ph-bold ph-clock text-amber-600 dark:text-amber-400"></i>
                    <span class="sr-only">{{ $turnoCabecera }}</span>
                    <span>Turno de mañana · 07:00 AM – 03:00 PM</span>
                </div>

                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 text-xs font-bold text-emerald-800 dark:text-emerald-300 shadow-2xs">
                    <i class="ph-bold ph-desktop text-emerald-600 animate-pulse"></i>
                    <span>Hora actual PC: <strong x-text="relojPC.hora12" class="font-mono font-black text-emerald-900 dark:text-emerald-200"></strong></span>
                </div>

                <button type="button"
                        wire:click="setFiltro('por_administrar')"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 hover:bg-rose-100 transition cursor-pointer text-xs font-extrabold dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-300">
                    <i class="ph-bold ph-bell-ringing text-rose-600 animate-bounce"></i>
                    <span>Alertas ({{ $kpiPorAdministrar }})</span>
                </button>
            </div>
        </div>

        {{-- Recordatorio de principio funcional operativo --}}
        <div class="mt-4 flex items-start gap-2.5 p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-xs text-[var(--rm-text-body)]">
            <i class="ph-bold ph-shield-check text-[#1E3A8A] text-base shrink-0 mt-0.5 dark:text-blue-400"></i>
            <div class="leading-relaxed">
                <span class="font-bold text-[var(--rm-text-title)]">Módulo operativo de Enfermería:</span>
                <span class="text-[var(--rm-text-muted)] ml-1">
                    Permite consultar prescripciones, verificar dosis/vía/horario, registrar administración u omisión con motivo y aplicar PRN según indicación médica. No permite modificar ni suspender tratamientos médicos.
                </span>
            </div>
        </div>
    </header>

    {{-- ========================================================================= --}}
    {{-- 3. EXACTAMENTE 5 KPIS SUPERIORES COMPACTOS                                --}}
    {{-- ========================================================================= --}}
    <section class="grid grid-cols-2 lg:grid-cols-5 gap-3.5">
        {{-- KPI 1: Por administrar ahora (ROJO cuando vencidas o ahora) --}}
        <div class="rounded-2xl border p-4 shadow-2xs transition {{ $kpiPorAdministrar > 0 ? 'bg-rose-50/70 border-rose-300 dark:bg-rose-950/30 dark:border-rose-900/60' : 'bg-[var(--rm-surface)] border-[var(--rm-border)]' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Por administrar ahora</span>
                <i class="ph-bold ph-warning-circle text-lg {{ $kpiPorAdministrar > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400' }}"></i>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-3xl font-black tracking-tight {{ $kpiPorAdministrar > 0 ? 'text-rose-700 dark:text-rose-300' : 'text-[var(--rm-text-title)]' }}">
                    {{ $kpiPorAdministrar }}
                </span>
                <span class="text-[11px] font-semibold text-rose-700 dark:text-rose-400">Dosis en horario</span>
            </div>
            <p class="mt-1 text-[10.5px] text-[var(--rm-text-muted)]">Atrasadas o pendientes ahora</p>
        </div>

        {{-- KPI 2: Próximas dosis (próximas 2 horas) --}}
        <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4 shadow-2xs dark:border-amber-900/50 dark:bg-amber-950/25">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">Próximas dosis</span>
                <i class="ph-bold ph-clock-countdown text-lg text-amber-600 dark:text-amber-400"></i>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-3xl font-black tracking-tight text-amber-900 dark:text-amber-200">
                    {{ $kpiProximas }}
                </span>
                <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400">Próximas 2 h</span>
            </div>
            <p class="mt-1 text-[10.5px] text-amber-800/80 dark:text-amber-300/80">Ventana de administración</p>
        </div>

        {{-- KPI 3: Administradas hoy --}}
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 shadow-2xs dark:border-emerald-900/50 dark:bg-emerald-950/25">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Administradas hoy</span>
                <i class="ph-bold ph-check-circle text-lg text-emerald-600 dark:text-emerald-400"></i>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-3xl font-black tracking-tight text-emerald-900 dark:text-emerald-200">
                    {{ $kpiAdminHoyTotal }}
                </span>
                <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">registradas</span>
            </div>
            <p class="mt-1 text-[10.5px] text-emerald-800/80 dark:text-emerald-300/80">Tomas con firma y hora</p>
        </div>

        {{-- KPI 4: Omitidas / atrasadas --}}
        <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Omitidas / atrasadas</span>
                <i class="ph-bold ph-x-circle text-lg {{ $kpiOmitidasAtrasadas > 0 ? 'text-amber-600' : 'text-slate-400' }}"></i>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-3xl font-black tracking-tight {{ $kpiOmitidasAtrasadas > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-[var(--rm-text-title)]' }}">
                    {{ $kpiOmitidasAtrasadas }}
                </span>
                <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400">justificadas</span>
            </div>
            <p class="mt-1 text-[10.5px] text-[var(--rm-text-muted)]">Requiere motivo clínico</p>
        </div>

        {{-- KPI 5: Adherencia hoy (Indicador circular SVG 89%) --}}
        <div class="col-span-2 lg:col-span-1 rounded-2xl border border-blue-200 bg-blue-50/40 p-4 shadow-2xs dark:border-blue-900/50 dark:bg-blue-950/25 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900 dark:text-blue-300">Adherencia hoy</span>
                <div class="mt-1.5 flex items-baseline gap-1.5">
                    <span class="text-2xl font-black text-blue-950 dark:text-blue-100">{{ $kpiAdherenciaPct }}%</span>
                </div>
                <p class="text-[10.5px] font-bold text-blue-800 dark:text-blue-300 mt-0.5">{{ $kpiAdherenciaTexto }}</p>
            </div>

            {{-- SVG circular progress --}}
            <div class="relative flex items-center justify-center shrink-0">
                <svg class="w-14 h-14 transform -rotate-90" viewBox="0 0 36 36">
                    <path class="text-blue-100 dark:text-blue-900/50" stroke-width="3.5" stroke="currentColor" fill="none"
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    <path class="text-[#1E3A8A] dark:text-blue-400 transition-all duration-1000 ease-out"
                          stroke-dasharray="100"
                          stroke-dashoffset="{{ 100 - $kpiAdherenciaPct }}"
                          stroke-linecap="round"
                          stroke-width="3.5"
                          stroke="currentColor"
                          fill="none"
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                </svg>
                <i class="ph-bold ph-chart-donut absolute text-xs text-[#1E3A8A] dark:text-blue-300"></i>
            </div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- 4. ALERTA PRIORITARIA DE ADMINISTRACIÓN (BANNER DOBLE CON REGLAS HORARIAS)  --}}
    {{-- ========================================================================= --}}
    @if(!$alertaInterruptivaMinimizada && $kpiPorAdministrar > 0)
        <section class="rounded-2xl border-2 border-rose-300 bg-rose-50/90 p-4 sm:p-5 shadow-sm dark:border-rose-900 dark:bg-rose-950/40">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                {{-- Lado izquierdo: Alerta urgente actual --}}
                <div class="flex items-start gap-3.5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-600 text-white shadow-2xs animate-pulse">
                        <i class="ph-bold ph-warning text-2xl"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2 py-0.5 rounded-md text-[10.5px] font-black uppercase tracking-wider bg-rose-600 text-white">
                                ALERTA PRIORITARIA
                            </span>
                            <span class="px-2 py-0.5 rounded-md text-[10.5px] font-black uppercase tracking-wider bg-rose-800 text-white">
                                ALERTA CLÍNICA DE ADMINISTRACIÓN
                            </span>
                            <h2 class="text-sm sm:text-base font-black text-rose-950 dark:text-rose-100 flex items-center gap-1.5 flex-wrap">
                                <span>Es hora de administrar {{ $itemAlertaInmediata['medicamento'] }} {{ $itemAlertaInmediata['dosis'] }} — {{ \Carbon\Carbon::parse("2000-01-01 {$itemAlertaInmediata['horario']}")->format('h:i A') }}</span>
                                <span class="sr-only">{{ $itemAlertaInmediata['horario'] }}</span>
                            </h2>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-rose-200/90 text-rose-950 border border-rose-300 font-mono text-[11px] font-black dark:bg-rose-900/60 dark:text-rose-100 dark:border-rose-700">
                                <i class="ph-bold ph-clock text-rose-700 animate-pulse"></i>
                                <span>Atraso en tiempo real:</span>
                                <strong x-text="relojPC.tiempoDiferenciaFormateado('{{ $itemAlertaInmediata['horario'] }}')" class="tracking-wide"></strong>
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-rose-800 dark:text-rose-300 mt-1">
                            La dosis está pendiente de administración. No se bloquean emergencias ni funciones clínicas urgentes.
                        </p>
                    </div>
                </div>

                {{-- Acciones de la alerta izquierda + Alerta derecha integrada --}}
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Banner derecho: Próxima administración en cuenta regresiva --}}
                    <div class="flex items-center gap-2.5 px-3 py-2 rounded-xl bg-amber-100/80 border border-amber-300 text-amber-900 text-xs font-bold dark:bg-amber-950/60 dark:border-amber-800 dark:text-amber-200">
                        <i class="ph-bold ph-clock-countdown text-amber-700 text-base dark:text-amber-400"></i>
                        <div>
                            <div class="text-[10.5px] uppercase tracking-wider text-amber-800 dark:text-amber-300">Próxima en {{ $itemProxima['minutos'] ?? 25 }} min</div>
                            <div class="font-extrabold text-amber-950 dark:text-amber-100 flex items-center gap-1.5">
                                <span>{{ $itemProxima['medicamento'] }} {{ $itemProxima['dosis'] }} — {{ \Carbon\Carbon::parse("2000-01-01 {$itemProxima['horario']}")->format('h:i A') }}</span>
                                <span class="sr-only">{{ $itemProxima['horario'] }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Botón Atender ahora --}}
                    <button type="button"
                            wire:click="abrirDrawerDetalle('{{ $itemAlertaInmediata['id'] }}', '{{ $itemAlertaInmediata['horario'] }}', 'administrar')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs font-extrabold transition cursor-pointer shadow-sm">
                        <i class="ph-bold ph-check-circle text-base"></i>
                        <span>Atender ahora</span>
                    </button>

                    {{-- Botón Justificar demora --}}
                    <button type="button"
                            wire:click="abrirModalDemora('{{ $itemAlertaInmediata['id'] }}', '{{ $itemAlertaInmediata['horario'] }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl border border-rose-300 bg-white/90 hover:bg-white text-rose-800 text-xs font-bold transition cursor-pointer dark:bg-rose-900/40 dark:border-rose-800 dark:text-rose-200">
                        <i class="ph-bold ph-clock-counter-clockwise"></i>
                        <span>Justificar demora</span>
                    </button>

                    {{-- Minimizar temporalmente para emergencias --}}
                    <button type="button"
                            wire:click="minimizarAlerta"
                            title="Minimizar para atender emergencias"
                            class="p-2 rounded-xl text-rose-700 hover:bg-rose-200/60 transition text-sm cursor-pointer dark:text-rose-300">
                        <i class="ph-bold ph-minus"></i>
                    </button>
                </div>
            </div>
        </section>
    @elseif($alertaInterruptivaMinimizada && $kpiPorAdministrar > 0)
        {{-- Barra minimizada no bloqueante --}}
        <div class="flex items-center justify-between p-2.5 px-4 rounded-xl bg-rose-50 border border-rose-300 text-rose-900 text-xs font-bold dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-300">
            <div class="flex items-center gap-2 font-bold text-rose-950 dark:text-rose-100 flex-wrap">
                <span class="h-2 w-2 rounded-full bg-rose-600 animate-ping"></span>
                <span>Alerta pendiente: Es hora de administrar {{ $itemAlertaInmediata['medicamento'] }} — {{ \Carbon\Carbon::parse("2000-01-01 {$itemAlertaInmediata['horario']}")->format('h:i A') }}</span>
                <span class="sr-only">{{ $itemAlertaInmediata['horario'] }}</span>
                <span class="font-mono text-xs px-2 py-0.5 rounded bg-rose-200 text-rose-900 font-black dark:bg-rose-900 dark:text-rose-100">
                    Atraso en tiempo real: <strong x-text="relojPC.tiempoDiferenciaFormateado('{{ $itemAlertaInmediata['horario'] }}')"></strong>
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="restaurarAlerta" class="underline hover:text-rose-950 text-xs font-extrabold cursor-pointer">
                    Restaurar alerta
                </button>
                <button type="button" wire:click="abrirDrawerDetalle('{{ $itemAlertaInmediata['id'] }}', '{{ $itemAlertaInmediata['horario'] }}', 'administrar')" class="px-2.5 py-1 rounded-lg bg-rose-600 text-white text-[11px] font-bold cursor-pointer">
                    Atender
                </button>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 5. CHIPS DE FILTROS                                                       --}}
    {{-- ========================================================================= --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
        <button type="button"
                wire:click="setFiltro('por_administrar')"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-bold transition cursor-pointer {{ $filtroActivo === 'por_administrar' ? 'bg-rose-600 text-white shadow-2xs' : 'bg-[var(--rm-surface)] border border-[var(--rm-border)] text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)]' }}">
            <span>Por administrar</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10.5px] {{ $filtroActivo === 'por_administrar' ? 'bg-rose-700 text-white' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }}">
                {{ $conteoPorAdmin }}
            </span>
        </button>

        <button type="button"
                wire:click="setFiltro('proximas')"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-bold transition cursor-pointer {{ $filtroActivo === 'proximas' ? 'bg-amber-600 text-white shadow-2xs' : 'bg-[var(--rm-surface)] border border-[var(--rm-border)] text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)]' }}">
            <span>Próximas</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10.5px] {{ $filtroActivo === 'proximas' ? 'bg-amber-700 text-white' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' }}">
                {{ $conteoProximas }}
            </span>
        </button>

        <button type="button"
                wire:click="setFiltro('administradas')"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-bold transition cursor-pointer {{ $filtroActivo === 'administradas' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-[var(--rm-surface)] border border-[var(--rm-border)] text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)]' }}">
            <span>Administradas</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10.5px] {{ $filtroActivo === 'administradas' ? 'bg-emerald-700 text-white' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' }}">
                {{ $conteoAdminHoy }}
            </span>
        </button>

        <button type="button"
                wire:click="setFiltro('prn')"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-bold transition cursor-pointer {{ $filtroActivo === 'prn' ? 'bg-[#1E3A8A] text-white shadow-2xs' : 'bg-[var(--rm-surface)] border border-[var(--rm-border)] text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)]' }}">
            <span>PRN</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10.5px] {{ $filtroActivo === 'prn' ? 'bg-blue-800 text-white' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' }}">
                {{ $conteoPrn }}
            </span>
        </button>

        <button type="button"
                wire:click="setFiltro('suspendidas')"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-bold transition cursor-pointer {{ $filtroActivo === 'suspendidas' ? 'bg-slate-700 text-white shadow-2xs' : 'bg-[var(--rm-surface)] border border-[var(--rm-border)] text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)]' }}">
            <span>Suspendidas</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10.5px] {{ $filtroActivo === 'suspendidas' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                {{ $conteoSuspendidas }}
            </span>
        </button>

        <button type="button"
                wire:click="setFiltro('todos')"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-bold transition cursor-pointer {{ $filtroActivo === 'todos' ? 'bg-slate-800 text-white shadow-2xs' : 'bg-[var(--rm-surface)] border border-[var(--rm-border)] text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)]' }}">
            <span>Todos</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10.5px] {{ $filtroActivo === 'todos' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                {{ $itemsPriorizados->count() }}
            </span>
        </button>
    </div>

    {{-- ========================================================================= --}}
    {{-- 6. TABLA PRINCIPAL PRIORIZADA (ORDEN ESTRICTO OBLIGATORIO)                --}}
    {{-- ========================================================================= --}}
    <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-[11px] font-extrabold uppercase tracking-wider text-[var(--rm-text-muted)]">
                        <th class="py-3 px-4">Medicamento</th>
                        <th class="py-3 px-3">Dosis</th>
                        <th class="py-3 px-3">Vía</th>
                        <th class="py-3 px-3">Horario</th>
                        <th class="py-3 px-3">Indicación</th>
                        <th class="py-3 px-3">Estado actual</th>
                        <th class="py-3 px-3">Última administración</th>
                        <th class="py-3 px-3">Responsable</th>
                        <th class="py-3 px-4 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--rm-border)]">
                    @forelse($itemsFiltrados as $item)
                        <tr class="transition-colors hover:bg-slate-50/70 dark:hover:bg-slate-800/40 cursor-pointer {{ $item['fila_tint'] }}"
                            wire:click="abrirDrawerDetalle('{{ $item['id'] }}', '{{ $item['horario'] }}')">
                            {{-- 1. Medicamento --}}
                            <td class="py-3.5 px-4 font-bold text-[var(--rm-text-title)]">
                                <div class="flex items-center gap-2">
                                    <span>{{ $item['medicamento'] }}</span>
                                    @if($item['prioridad'] === 1)
                                        <span class="h-2 w-2 rounded-full bg-rose-600 animate-ping"></span>
                                    @endif
                                </div>
                            </td>

                            {{-- 2. Dosis --}}
                            <td class="py-3.5 px-3 font-semibold text-[var(--rm-text-body)]">
                                {{ $item['dosis'] }}
                            </td>

                            {{-- 3. Vía --}}
                            <td class="py-3.5 px-3 font-medium text-[var(--rm-text-body)]">
                                {{ $item['via'] }}
                            </td>

                            {{-- 4. Horario --}}
                            <td class="py-3.5 px-3">
                                @php
                                    $h12 = \Carbon\Carbon::parse("2000-01-01 {$item['horario']}")->format('h:i A');
                                @endphp
                                <div class="flex items-center gap-1.5">
                                    <span class="font-extrabold text-[var(--rm-text-title)] text-xs font-mono">
                                        {{ $h12 }}
                                    </span>
                                    <span class="text-[10px] text-[var(--rm-text-muted)] font-mono font-medium">({{ $item['horario'] }})</span>
                                </div>
                                <span class="inline-block mt-0.5 px-1.5 py-0.2 rounded text-[9.5px] font-bold"
                                      :class="relojPC.sePaso('{{ $item['horario'] }}') && {{ $item['prioridad'] !== 5 ? 'true' : 'false' }} ? 'bg-rose-100 text-rose-800 animate-pulse' : 'text-[var(--rm-text-muted)]'"
                                      x-text="relojPC.evaluarHorario('{{ $item['horario'] }}', {{ $item['prioridad'] === 5 ? 'true' : 'false' }}).texto">
                                </span>
                            </td>

                            {{-- 5. Indicación --}}
                            <td class="py-3.5 px-3 text-[var(--rm-text-muted)] max-w-xs truncate">
                                {{ $item['indicacion'] }}
                            </td>

                            {{-- 6. Estado actual --}}
                            <td class="py-3.5 px-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10.5px] font-bold border {{ $item['badge_color'] }}">
                                    @if($item['prioridad'] === 1)
                                        <i class="ph-bold ph-warning-circle text-rose-600"></i>
                                    @elseif($item['prioridad'] === 2)
                                        <i class="ph-bold ph-clock text-amber-600"></i>
                                    @elseif($item['prioridad'] === 3)
                                        <i class="ph-bold ph-clock-countdown text-amber-600"></i>
                                    @elseif($item['prioridad'] === 4)
                                        <i class="ph-bold ph-circle text-blue-600"></i>
                                    @elseif($item['prioridad'] === 5)
                                        <i class="ph-bold ph-check text-emerald-600"></i>
                                    @endif
                                    <span>{{ $item['estado_label'] }}</span>
                                </span>
                            </td>

                            {{-- 7. Última administración --}}
                            <td class="py-3.5 px-3 text-[var(--rm-text-muted)] font-medium">
                                {{ $item['ultima_admin'] }}
                            </td>

                            {{-- 8. Responsable --}}
                            <td class="py-3.5 px-3 text-[var(--rm-text-body)] font-medium">
                                {{ $item['responsable'] }}
                            </td>

                            {{-- 9. Acción --}}
                            <td class="py-3.5 px-4 text-right" onclick="event.stopPropagation()">
                                @if($item['es_atrasado'] || $item['es_ahora'])
                                    <button type="button"
                                            wire:click="abrirDrawerDetalle('{{ $item['id'] }}', '{{ $item['horario'] }}', 'administrar')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs font-extrabold transition cursor-pointer shadow-2xs">
                                        <i class="ph-bold ph-check-square"></i>
                                        <span>Administrar</span>
                                    </button>
                                @elseif($item['es_proxima'] || $item['es_programada'])
                                    <button type="button"
                                            wire:click="abrirDrawerDetalle('{{ $item['id'] }}', '{{ $item['horario'] }}', 'detalle')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:bg-[var(--rm-surface)] text-[var(--rm-text-title)] text-xs font-bold transition cursor-pointer shadow-2xs">
                                        <i class="ph-bold ph-eye"></i>
                                        <span>Ver detalle</span>
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1 text-emerald-700 font-bold text-xs dark:text-emerald-400">
                                        <i class="ph-bold ph-check-circle text-base"></i>
                                        <span>Registrado</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-xs text-[var(--rm-text-muted)]">
                                No se encontraron medicamentos para el filtro seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- 7. AGENDA DE ADMINISTRACIÓN DE HOY (TIMELINE HORIZONTAL CRONOLÓGICO)        --}}
    {{-- ========================================================================= --}}
    <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-2xs">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-sm font-black uppercase tracking-wider text-[var(--rm-text-title)]">
                    Agenda de administración de hoy
                </h3>
                <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">
                    Cronograma horario interactivo con estado semántico de tomas
                </p>
            </div>
            <div class="flex items-center gap-3 text-[11px] font-bold text-[var(--rm-text-muted)]">
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Administrado</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-rose-500"></span> Atrasado / Pendiente</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-amber-500"></span> Próximo</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-blue-400"></span> Programado</span>
            </div>
        </div>

        {{-- Timeline horizontal desplazable --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 pt-2">
            @foreach($agendaTimeline as $slot)
                <div class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-3 flex flex-col justify-between min-h-[110px]">
                    <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-1.5 mb-2">
                        @php
                        $slotH12 = \Carbon\Carbon::parse("2000-01-01 {$slot['hora']}")->format('h:i A');
                    @endphp
                    <span class="text-xs font-black text-[var(--rm-text-title)] font-mono">{{ $slotH12 }} <span class="sr-only">{{ $slot['hora'] }}</span></span>
                        <i class="ph-bold ph-clock text-xs text-[var(--rm-text-muted)]"></i>
                    </div>

                    <div class="space-y-1.5 flex-1">
                        @forelse($slot['medicamentos'] as $medSlot)
                            <div class="flex items-center justify-between p-1.5 rounded-lg text-[11px] font-bold {{ $medSlot['color'] }}">
                                <span class="truncate pr-1">{{ $medSlot['nombre'] }}</span>
                                <i class="ph-bold {{ $medSlot['icono'] }} shrink-0"></i>
                            </div>
                        @empty
                            <span class="text-[10px] text-[var(--rm-text-muted)] italic block mt-2">Sin tomas</span>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- 8. MEDICAMENTOS PRN (A DEMANDA) — BLOQUE SEPARADO CON VALIDACIONES          --}}
    {{-- ========================================================================= --}}
    <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-black uppercase tracking-wider text-[var(--rm-text-title)]">
                        Medicamentos PRN (a demanda)
                    </h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300">
                        Bajo criterio clínico
                    </span>
                </div>
                <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">
                    Administración bajo criterio clínico según prescripción médica vigente
                </p>
            </div>

            <span class="text-[11px] font-semibold text-[var(--rm-text-muted)]">
                Requiere evaluación de dolor / síntoma e intervalo mínimo
            </span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-[var(--rm-border)]">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-[11px] font-extrabold uppercase tracking-wider text-[var(--rm-text-muted)]">
                        <th class="py-2.5 px-4">Medicamento</th>
                        <th class="py-2.5 px-3">Dosis</th>
                        <th class="py-2.5 px-3">Vía</th>
                        <th class="py-2.5 px-3">Indicación clínica</th>
                        <th class="py-2.5 px-3">Frecuencia máxima</th>
                        <th class="py-2.5 px-3">Última administración</th>
                        <th class="py-2.5 px-4 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--rm-border)] bg-[var(--rm-surface)]">
                    @foreach($prnList as $prn)
                        <tr class="hover:bg-[var(--rm-surface-alt)] transition">
                            <td class="py-3 px-4 font-bold text-[var(--rm-text-title)]">
                                {{ $prn['medicamento'] }}
                            </td>
                            <td class="py-3 px-3 font-semibold text-[var(--rm-text-body)]">
                                {{ $prn['dosis'] }}
                            </td>
                            <td class="py-3 px-3 font-medium text-[var(--rm-text-body)]">
                                {{ $prn['via'] }}
                            </td>
                            <td class="py-3 px-3 text-[var(--rm-text-muted)]">
                                {{ $prn['indicacion'] }}
                            </td>
                            <td class="py-3 px-3 font-semibold text-[var(--rm-text-title)]">
                                {{ $prn['frecuencia_maxima'] }}
                            </td>
                            <td class="py-3 px-3 text-[var(--rm-text-muted)] font-medium">
                                {{ $prn['ultima_admin'] }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button type="button"
                                        wire:click="abrirModalPrn('{{ $prn['id'] }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-200 text-[#1E3A8A] hover:bg-blue-100 text-xs font-extrabold transition cursor-pointer dark:bg-blue-950/40 dark:border-blue-900 dark:text-blue-300">
                                    <i class="ph-bold ph-plus-circle"></i>
                                    <span>Registrar administración</span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- 9. HISTORIAL DE ADMINISTRACIÓN (BLOQUE INFERIOR CON LINK VER TODOS)         --}}
    {{-- ========================================================================= --}}
    <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-2xs">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-sm font-black uppercase tracking-wider text-[var(--rm-text-title)]">
                    Historial de administración
                </h3>
                <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">
                    Últimas tomas registradas con resultado y profesional responsable
                </p>
            </div>

            <button type="button"
                    wire:click="abrirHistorial"
                    class="inline-flex items-center gap-1 text-xs font-extrabold text-[#1E3A8A] hover:underline cursor-pointer dark:text-blue-400">
                <span>Ver todos</span>
                <i class="ph-bold ph-arrow-right"></i>
            </button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-[var(--rm-border)]">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-[11px] font-extrabold uppercase tracking-wider text-[var(--rm-text-muted)]">
                        <th class="py-2.5 px-4">Fecha / Hora</th>
                        <th class="py-2.5 px-3">Medicamento</th>
                        <th class="py-2.5 px-3">Dosis</th>
                        <th class="py-2.5 px-3">Vía</th>
                        <th class="py-2.5 px-3">Resultado</th>
                        <th class="py-2.5 px-3">Administrado por</th>
                        <th class="py-2.5 px-4">Observaciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--rm-border)] bg-[var(--rm-surface)]">
                    @forelse($historialList as $hist)
                        <tr class="hover:bg-[var(--rm-surface-alt)] transition">
                            <td class="py-3 px-4 font-bold text-[var(--rm-text-title)] whitespace-nowrap">
                                {{ $hist['fecha_hora'] }}
                            </td>
                            <td class="py-3 px-3 font-bold text-[var(--rm-text-title)]">
                                {{ $hist['medicamento'] }}
                            </td>
                            <td class="py-3 px-3 font-semibold text-[var(--rm-text-body)]">
                                {{ $hist['dosis'] }}
                            </td>
                            <td class="py-3 px-3 font-medium text-[var(--rm-text-body)]">
                                {{ $hist['via'] }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-extrabold border {{ $hist['es_administrado'] ? 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800' }}">
                                    <i class="ph-bold {{ $hist['es_administrado'] ? 'ph-check' : 'ph-x' }}"></i>
                                    <span>{{ $hist['resultado'] }}</span>
                                </span>
                            </td>
                            <td class="py-3 px-3 text-[var(--rm-text-body)] font-medium">
                                {{ $hist['administrado_por'] }}
                            </td>
                            <td class="py-3 px-4 text-[var(--rm-text-muted)] max-w-sm truncate">
                                {{ $hist['observaciones'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-xs text-[var(--rm-text-muted)]">
                                Sin registros de administración en el historial.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- 10. PANEL LATERAL DERECHO (DRAWER CANÓNICO NÍTIDO `rm-drawer`)            --}}
    {{-- ========================================================================= --}}
    @if($drawerAbierto)
        <div class="fixed inset-0 z-50 overflow-hidden" role="dialog" aria-modal="true">
            {{-- Backdrop suave 0-1px blur --}}
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[0.5px] transition-opacity duration-200"
                 wire:click="cerrarDrawer"></div>

            <div class="fixed inset-y-0 right-0 flex max-w-full pl-6">
                <div class="w-screen max-w-[740px] border-l border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xl flex flex-col justify-between transform transition-transform duration-300 ease-in-out">

                    {{-- 1. Sticky Header --}}
                    <div class="sticky top-0 z-20 border-b border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-700 dark:text-blue-400">
                                PANEL LATERAL DE CONSULTA
                            </span>
                            <h2 class="text-lg font-black text-[var(--rm-text-title)] mt-0.5">
                                {{ $drawerPaso === 'administrar' ? 'Confirmar administración de dosis' : 'Detalle de medicación' }}
                            </h2>
                        </div>
                        <button type="button"
                                wire:click="cerrarDrawer"
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] hover:bg-slate-200 dark:hover:bg-slate-800 transition cursor-pointer text-lg font-bold">
                            ✕
                        </button>
                    </div>

                    {{-- 2. Scrollable Body --}}
                    <div class="flex-1 overflow-y-auto p-6 space-y-6">
                        @if($drawerPaso === 'detalle')
                            {{-- BLOQUE 1: Ficha del Medicamento --}}
                            <div class="rounded-2xl border border-blue-200 bg-blue-50/40 p-4.5 dark:border-blue-900/60 dark:bg-blue-950/20">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white font-bold text-lg">
                                            <i class="ph-bold ph-pill"></i>
                                        </span>
                                        <div>
                                            <h3 class="text-base font-black text-[var(--rm-text-title)]">
                                                {{ $medDetalle['nombre'] ?? 'Medicamento' }}
                                            </h3>
                                            <p class="text-xs text-[var(--rm-text-muted)] font-semibold">
                                                {{ $medDetalle['presentacion'] ?? 'Comprimidos 1 g' }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300">
                                        Estado: {{ $medDetalle['estado'] ?? 'Activo' }}
                                    </span>
                                </div>
                            </div>

                            {{-- BLOQUE 2: Datos de Prescripción (Solo lectura) --}}
                            <div class="space-y-3">
                                <h4 class="text-xs font-black uppercase tracking-wider text-[var(--rm-text-title)]">
                                    Datos de prescripción
                                </h4>
                                <div class="grid grid-cols-2 gap-3 text-xs">
                                    <div class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-3">
                                        <span class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)]">Presentación</span>
                                        <p class="font-black text-[var(--rm-text-title)] mt-0.5">{{ $medDetalle['presentacion'] ?? 'Comprimidos' }}</p>
                                    </div>
                                    <div class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-3">
                                        <span class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)]">Vía de administración</span>
                                        <p class="font-black text-[var(--rm-text-title)] mt-0.5">{{ $medDetalle['via'] ?? 'Oral' }}</p>
                                    </div>
                                    <div class="col-span-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-3">
                                        <span class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)]">Indicación médica</span>
                                        <p class="font-black text-[var(--rm-text-title)] mt-0.5">{{ $medDetalle['indicacion'] ?? 'Dolor leve a moderado' }}</p>
                                    </div>
                                    <div class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-3">
                                        <span class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)]">Prescriptor</span>
                                        <p class="font-black text-[var(--rm-text-title)] mt-0.5">{{ $medDetalle['prescriptor'] ?? 'Dr. Carlos Méndez' }}</p>
                                    </div>
                                    <div class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-3">
                                        <span class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)]">Última administración</span>
                                        <p class="font-black text-[var(--rm-text-title)] mt-0.5">{{ $medDetalle['ultima_admin'] ?? '11/09 20:00 · Carla Gómez' }}</p>
                                    </div>
                                    <div class="col-span-2 rounded-xl border border-amber-200 bg-amber-50/60 p-3 dark:border-amber-900 dark:bg-amber-950/30">
                                        <span class="text-[10.5px] font-bold uppercase text-amber-800 dark:text-amber-300">Próxima dosis</span>
                                        <div class="flex items-center justify-between mt-0.5">
                                            <p class="font-black text-amber-950 dark:text-amber-100 text-sm">{{ $medDetalle['proxima_dosis'] ?? 'Hoy, 08:00' }}</p>
                                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-rose-600 text-white">
                                                Atrasado 12 min
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- BLOQUE 3: Precauciones y Alertas Clínicas (Datos reales) --}}
                            <div class="space-y-3">
                                <h4 class="text-xs font-black uppercase tracking-wider text-[var(--rm-text-title)]">
                                    Precauciones y alertas
                                </h4>
                                <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-4 space-y-3 text-xs">
                                    <div class="flex items-start gap-2.5">
                                        <i class="ph-bold ph-shield-warning text-amber-600 text-base shrink-0 mt-0.5"></i>
                                        <div>
                                            <span class="font-bold text-[var(--rm-text-title)]">Alergias del residente:</span>
                                            <p class="text-[var(--rm-text-muted)] mt-0.5">{{ $medDetalle['alergias'] ?? 'Sin alergias medicamentosas registradas' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2.5 border-t border-[var(--rm-border)] pt-2.5">
                                        <i class="ph-bold ph-scales text-blue-600 text-base shrink-0 mt-0.5"></i>
                                        <div>
                                            <span class="font-bold text-[var(--rm-text-title)]">Máximo diario prescrito:</span>
                                            <p class="text-[var(--rm-text-muted)] mt-0.5">{{ $medDetalle['maximo_diario'] ?? '4 g / 24 horas' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2.5 border-t border-[var(--rm-border)] pt-2.5">
                                        <i class="ph-bold ph-heartbeat text-rose-600 text-base shrink-0 mt-0.5"></i>
                                        <div>
                                            <span class="font-bold text-[var(--rm-text-title)]">Controles previos requeridos:</span>
                                            <p class="text-[var(--rm-text-muted)] mt-0.5">{{ $medDetalle['controles_previos'] ?? 'Verificar nivel de dolor (EVA) y constantes basales' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- BLOQUE 4: Regla de Seguridad del Sistema --}}
                            <div class="rounded-2xl border border-rose-200 bg-rose-50/40 p-4 text-xs dark:border-rose-900/50 dark:bg-rose-950/20">
                                <div class="flex items-center gap-2 text-rose-800 dark:text-rose-300 font-extrabold mb-1">
                                    <i class="ph-bold ph-lock-key text-base"></i>
                                    <span>Regla de seguridad RememberMind</span>
                                </div>
                                <p class="text-[var(--rm-text-muted)] leading-relaxed">
                                    Si una administración programada supera el margen permitido: se genera alerta visible en enfermería, se destaca como atrasada y se solicita atención inmediata o justificación de demora. El sistema <strong>nunca registrará una administración de forma automática</strong>.
                                </p>
                            </div>
                        @else
                            {{-- PASO: FORMULARIO DE CONFIRMACIÓN DE ADMINISTRACIÓN --}}
                            <div class="space-y-4 text-xs">
                                {{-- Resumen solo lectura del tratamiento --}}
                                <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-4 space-y-2">
                                    <div class="text-[10px] font-black uppercase text-blue-700 tracking-wider">Modo Solo Lectura · Prescripción Médica</div>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div><span class="text-[var(--rm-text-muted)]">Residente:</span> <strong class="text-[var(--rm-text-title)] block">{{ $adulto->nombre_completo }}</strong></div>
                                        <div><span class="text-[var(--rm-text-muted)]">Medicamento:</span> <strong class="text-[var(--rm-text-title)] block">{{ $medDetalle['nombre'] ?? 'Paracetamol' }}</strong></div>
                                        <div><span class="text-[var(--rm-text-muted)]">Dosis / Vía:</span> <strong class="text-[var(--rm-text-title)] block">{{ $medDetalle['dosis'] ?? '1 g' }} · {{ $medDetalle['via'] ?? 'Oral' }}</strong></div>
                                        <div><span class="text-[var(--rm-text-muted)]">Horario programado:</span> <strong class="text-[var(--rm-text-title)] block">{{ $selectedHora ?? '08:00' }}</strong></div>
                                    </div>
                                </div>

                                {{-- Registro operativo de Enfermería --}}
                                <div class="space-y-3">
                                    <label class="font-bold text-[var(--rm-text-title)] block">Resultado de la toma:</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <button type="button"
                                                wire:click="$set('adminAdministrado', true)"
                                                class="p-3 rounded-xl border font-bold text-center transition cursor-pointer {{ $adminAdministrado ? 'bg-emerald-600 text-white border-emerald-700 shadow-2xs' : 'bg-[var(--rm-surface)] border-[var(--rm-border)] text-[var(--rm-text-body)]' }}">
                                            ✓ Administrado
                                        </button>
                                        <button type="button"
                                                wire:click="$set('adminAdministrado', false)"
                                                class="p-3 rounded-xl border font-bold text-center transition cursor-pointer {{ !$adminAdministrado ? 'bg-rose-600 text-white border-rose-700 shadow-2xs' : 'bg-[var(--rm-surface)] border-[var(--rm-border)] text-[var(--rm-text-body)]' }}">
                                            ✕ Omitido / No administrado
                                        </button>
                                    </div>

                                    <div>
                                        <label class="font-bold text-[var(--rm-text-title)] block mb-1">Hora real de administración:</label>
                                        <input type="time"
                                               wire:model="adminHoraReal"
                                               class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-2.5 text-xs font-bold text-[var(--rm-text-title)] focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    @if(!$adminAdministrado)
                                        <div>
                                            <label class="font-bold text-rose-700 dark:text-rose-400 block mb-1">Motivo de no administración (Obligatorio mín. 5 caracteres):</label>
                                            <textarea wire:model="adminMotivoOmision"
                                                      rows="2"
                                                      placeholder="Ej: Residente rechaza toma por náuseas, se avisa a médico de guardia..."
                                                      class="w-full rounded-xl border border-rose-300 bg-[var(--rm-surface)] p-2.5 text-xs text-[var(--rm-text-title)] focus:ring-2 focus:ring-rose-500"></textarea>
                                            @error('adminMotivoOmision') <span class="text-rose-600 text-[11px] font-bold">{{ $message }}</span> @enderror
                                        </div>
                                    @endif

                                    <div>
                                        <label class="font-bold text-[var(--rm-text-title)] block mb-1">Observación clínica:</label>
                                        <input type="text"
                                               wire:model="adminObservacion"
                                               placeholder="Tolerancia, ingesta de líquidos, observaciones..."
                                               class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-2.5 text-xs text-[var(--rm-text-title)]">
                                    </div>
                                </div>

                                {{-- Checklist de Validaciones Críticas (5 Correctos) --}}
                                <div class="rounded-2xl border border-blue-200 bg-blue-50/50 p-4 space-y-2 dark:border-blue-900/60 dark:bg-blue-950/30">
                                    <span class="text-[11px] font-black uppercase text-[#1E3A8A] dark:text-blue-300 block mb-2">
                                        Validaciones críticas de seguridad (5 Correctos):
                                    </span>
                                    <label class="flex items-center gap-2 cursor-pointer font-medium text-[var(--rm-text-title)]">
                                        <input type="checkbox" wire:model="checkResidente" class="rounded text-blue-600">
                                        <span>✓ Residente correcto: {{ $adulto->nombre_completo }}</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer font-medium text-[var(--rm-text-title)]">
                                        <input type="checkbox" wire:model="checkMedicamento" class="rounded text-blue-600">
                                        <span>✓ Medicamento correcto: {{ $medDetalle['nombre'] ?? 'Paracetamol' }}</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer font-medium text-[var(--rm-text-title)]">
                                        <input type="checkbox" wire:model="checkDosis" class="rounded text-blue-600">
                                        <span>✓ Dosis correcta: {{ $medDetalle['dosis'] ?? '1 g' }}</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer font-medium text-[var(--rm-text-title)]">
                                        <input type="checkbox" wire:model="checkVia" class="rounded text-blue-600">
                                        <span>✓ Vía correcta: {{ $medDetalle['via'] ?? 'Oral' }}</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer font-medium text-[var(--rm-text-title)]">
                                        <input type="checkbox" wire:model="checkHorario" class="rounded text-blue-600">
                                        <span>✓ Horario correcto: {{ $selectedHora ?? '08:00' }}</span>
                                    </label>
                                    @error('checklist') <p class="text-rose-600 font-bold text-[11px] mt-1">{{ $message }}</p> @enderror
                                    @error('administracion_error') <p class="text-rose-600 font-bold text-[11px] mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- 3. Sticky Footer --}}
                    <div class="sticky bottom-0 z-20 border-t border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 flex items-center justify-between gap-3">
                        <button type="button"
                                wire:click="abrirHistorial"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:bg-[var(--rm-surface)] text-xs font-bold text-[var(--rm-text-title)] transition cursor-pointer shadow-2xs">
                            <i class="ph-bold ph-clock-counter-clockwise"></i>
                            <span>Ver historial completo</span>
                        </button>

                        @if($drawerPaso === 'detalle')
                            <button type="button"
                                    wire:click="pasarAAdministrar"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs font-extrabold transition cursor-pointer shadow-sm">
                                <i class="ph-bold ph-check-square text-base"></i>
                                <span>Administrar ahora</span>
                            </button>
                        @else
                            <div class="flex items-center gap-2">
                                <button type="button"
                                        wire:click="$set('drawerPaso', 'detalle')"
                                        class="px-3.5 py-2.5 rounded-xl border border-[var(--rm-border)] text-xs font-bold text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] cursor-pointer">
                                    Volver
                                </button>
                                <button type="button"
                                        wire:click="confirmarAdministracion"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs font-extrabold transition cursor-pointer shadow-sm">
                                    <i class="ph-bold ph-check text-base"></i>
                                    <span>Confirmar administración</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 11. MODAL / SUB-PANEL DE ADMINISTRACIÓN PRN                               --}}
    {{-- ========================================================================= --}}
    @if($modalPrnAbierto)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[0.5px]" wire:click="cerrarModalPrn"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-6 shadow-2xl space-y-4 text-xs">
                    <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                        <div>
                            <span class="text-[10px] font-black uppercase text-blue-700 tracking-wider">Protocolo PRN</span>
                            <h3 class="text-base font-black text-[var(--rm-text-title)]">
                                Registrar administración condicional
                            </h3>
                        </div>
                        <button type="button" wire:click="cerrarModalPrn" class="text-lg font-bold text-[var(--rm-text-muted)] cursor-pointer">✕</button>
                    </div>

                    <div class="rounded-xl bg-blue-50/60 p-3 border border-blue-200 text-xs dark:bg-blue-950/30 dark:border-blue-900 space-y-1">
                        <div class="font-black text-blue-950 dark:text-blue-100">{{ $prnMedData['nombre'] }} {{ $prnMedData['dosis'] }} ({{ $prnMedData['via'] }})</div>
                        <div class="text-[var(--rm-text-muted)]">Indicación prescrita: {{ $prnMedData['indicacion'] }}</div>
                        <div class="text-[10.5px] font-bold text-blue-800 dark:text-blue-300">Intervalo mínimo: Cada {{ $prnMedData['intervalo_horas'] }} horas</div>
                    </div>

                    <div>
                        <label class="font-bold text-[var(--rm-text-title)] block mb-1">Motivo clínico de uso PRN:</label>
                        <input type="text" wire:model="prnMotivo" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-2 text-xs">
                        @error('prnMotivo') <span class="text-rose-600 font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="font-bold text-[var(--rm-text-title)] block mb-1">Valoración previa del síntoma:</label>
                        <input type="text" wire:model="prnValoracionPrevia" placeholder="Ej: Dolor articular agudo" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-2 text-xs">
                        @error('prnValoracionPrevia') <span class="text-rose-600 font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="font-bold text-[var(--rm-text-title)] block mb-1">Intensidad del síntoma (Escala 0 - 10): {{ $prnIntensidad }}/10</label>
                        <input type="range" min="0" max="10" wire:model.live="prnIntensidad" class="w-full">
                    </div>

                    @error('prn_error')
                        <div class="p-2 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-bold">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-[var(--rm-border)]">
                        <button type="button" wire:click="cerrarModalPrn" class="px-4 py-2 rounded-xl border border-[var(--rm-border)] font-bold cursor-pointer">Cancelar</button>
                        <button type="button" wire:click="confirmarPrn" class="px-5 py-2 rounded-xl bg-[#1E3A8A] text-white font-extrabold cursor-pointer">Confirmar PRN</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 12. MODAL PARA JUSTIFICAR DEMORA                                          --}}
    {{-- ========================================================================= --}}
    @if($modalDemoraAbierto)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[0.5px]" wire:click="cerrarModalDemora"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-6 shadow-2xl space-y-4 text-xs">
                    <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                        <h3 class="text-base font-black text-[var(--rm-text-title)]">Justificar demora de toma</h3>
                        <button type="button" wire:click="cerrarModalDemora" class="text-lg font-bold text-[var(--rm-text-muted)] cursor-pointer">✕</button>
                    </div>

                    <p class="text-[var(--rm-text-muted)] leading-relaxed">
                        Indique el motivo clínico por el cual la dosis no se administró inmediatamente a las {{ $demoraHora }}.
                    </p>

                    <div>
                        <textarea wire:model="motivoDemora" rows="3" placeholder="Ej: Residente en sesión de kinesiología / en ayunas por analítica..." class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-2.5 text-xs"></textarea>
                        @error('motivoDemora') <span class="text-rose-600 font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-[var(--rm-border)]">
                        <button type="button" wire:click="cerrarModalDemora" class="px-4 py-2 rounded-xl border border-[var(--rm-border)] font-bold cursor-pointer">Cancelar</button>
                        <button type="button" wire:click="guardarDemora" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-extrabold cursor-pointer">Guardar justificación</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 13. MODAL DE HISTORIAL COMPLETO                                           --}}
    {{-- ========================================================================= --}}
    @if($modalHistorialAbierto)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[0.5px]" wire:click="cerrarHistorial"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-3xl rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-6 shadow-2xl space-y-4 text-xs max-h-[85vh] flex flex-col justify-between">
                    <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                        <div>
                            <span class="text-[10px] font-black uppercase text-blue-700 tracking-wider">Auditoría Farmacológica</span>
                            <h3 class="text-base font-black text-[var(--rm-text-title)]">Historial completo de administraciones</h3>
                        </div>
                        <button type="button" wire:click="cerrarHistorial" class="text-lg font-bold text-[var(--rm-text-muted)] cursor-pointer">✕</button>
                    </div>

                    <div class="overflow-y-auto flex-1 space-y-2 pr-1">
                        @foreach($historialList as $itemH)
                            <div class="p-3 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <strong class="text-sm text-[var(--rm-text-title)]">{{ $itemH['medicamento'] }}</strong>
                                        <span class="text-xs text-[var(--rm-text-muted)]">({{ $itemH['dosis'] }} - {{ $itemH['via'] }})</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $itemH['es_administrado'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                            {{ $itemH['resultado'] }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-[var(--rm-text-muted)] mt-1">{{ $itemH['observaciones'] }}</p>
                                </div>
                                <div class="text-right text-[11px] shrink-0">
                                    <span class="font-bold text-[var(--rm-text-title)] block">{{ $itemH['fecha_hora'] }}</span>
                                    <span class="text-[var(--rm-text-muted)]">{{ $itemH['administrado_por'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-end pt-3 border-t border-[var(--rm-border)]">
                        <button type="button" wire:click="cerrarHistorial" class="px-5 py-2 rounded-xl bg-slate-800 text-white font-bold cursor-pointer">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
