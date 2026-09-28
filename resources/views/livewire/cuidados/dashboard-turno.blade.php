{{--

    Vista: Mi turno (Dashboard de Enfermería) - Versión Refinada UX/UI

    RememberMind - Módulo de Enfermería

    Estética Editorial Clínica Cálida: Outfit, Paleta profunda RememberMind (var(--rm-clinical), var(--rm-action-primary), #C98A17, #D62828, var(--rm-surface), var(--rm-surface))

    Foto del Hero verificada en asset('storage/imagenes/ENFERMERIA/manos.png')

--}}



<div class="space-y-3.5 sm:space-y-4 font-sans" wire:poll.60s="refrescarTurno">



    {{-- ========================================================= --}}

    {{-- 1. ZONA SUPERIOR: HERO COMPACTO (8 COLS) + ESTADO (4 COLS) --}}

    {{-- ========================================================= --}}

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-3.5 items-stretch">



        {{-- HERO PRINCIPAL COMPACTO Y EDITORIAL (8 COLS) --}}

        <div class="group lg:col-span-8 rounded-2xl border border-[var(--rm-border)] hover:border-[var(--rm-clinical)]/60 dark:border-[var(--rm-border)] dark:hover:border-[var(--rm-border)] bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] shadow-sm hover:shadow-md overflow-hidden flex flex-col sm:flex-row min-h-[142px] lg:h-[148px] transition-all duration-200 ease-out">



            {{-- LADO IZQUIERDO: TEXTO CLÍNICO REFINADO (64%) --}}

            <div class="w-full sm:w-[64%] p-3.5 sm:p-4 flex flex-col justify-between z-10">

                <div>

                    {{-- LÍNEA SUPERIOR DE FECHA, JORNADA Y MODO OPERATIVO --}}

                    <div class="flex flex-wrap items-center gap-1.5 text-[11px] font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">

                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[var(--rm-clinical-soft)] text-[var(--rm-clinical)] dark:bg-[var(--rm-clinical-soft)] dark:text-[var(--rm-text-muted)] font-black uppercase tracking-wider text-[10px]">

                            <i class="ph-bold ph-sun text-xs"></i>

                            <span>{{ $dashboard['jornada']['nombre'] ?? ($dashboard['turno']['periodo'] ?? 'Turno en curso') }}</span>

                        </span>

                        <span class="text-[var(--rm-text-muted)]/60 dark:text-[var(--rm-text-muted)]/50">•</span>

                        <span class="capitalize">{{ $dashboard['jornada']['fecha_humana'] ?? \Carbon\Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM') }}</span>



                        {{-- BADGES DE MODO: EN TURNO vs FUERA DE TURNO --}}

                        @if(($dashboard['modo'] ?? '') === 'EN_TURNO')

                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] dark:bg-[var(--rm-action-primary)]/30 dark:text-[var(--rm-action-primary)] font-black uppercase tracking-wider text-[9.5px] border border-[var(--rm-action-primary)]/30">

                                <i class="ph-bold ph-check-circle text-xs"></i>

                                <span>MI TURNO / ACTIVO</span>

                            </span>

                        @else

                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[var(--rm-surface)] text-[var(--rm-warning)] border border-[[var(--rm-warning)] dark:bg-[var(--rm-warning-soft)] dark:text-[var(--rm-warning)] dark:border-[var(--rm-warning)]/40 font-black uppercase tracking-wider text-[9.5px]">

                                <i class="ph-bold ph-clock text-xs"></i>

                                <span>FUERA DE TURNO</span>

                            </span>

                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[var(--rm-clinical-soft)] text-[var(--rm-clinical)] dark:bg-[var(--rm-clinical-soft)] dark:text-[var(--rm-text-muted)] font-black uppercase tracking-wider text-[9px] border border-[var(--rm-clinical)]/20">

                                <i class="ph-bold ph-eye text-xs"></i>

                                <span>MODO CONSULTA / SOLO LECTURA</span>

                            </span>

                        @endif

                    </div>



                    <h1 class="mt-1 text-lg sm:text-[20px] font-black text-[var(--rm-clinical)] dark:text-[var(--rm-surface)] tracking-tight leading-tight">

                        Buenos días, <span class="font-extrabold text-[var(--rm-clinical)] dark:text-white">{{ $dashboard['usuario']['nombres'] ?? (Auth::user()->nombres ?? 'Elena') }}</span>

                    </h1>



                    {{-- LEMA MOTIVACIONAL CLÍNICO --}}

                    <p class="mt-0.5 text-xs font-semibold text-[var(--rm-action-primary)] dark:text-[var(--rm-action-primary)] italic flex items-center gap-1">

                        <span class="text-[var(--rm-warning)] font-black text-sm not-italic">“</span>Tu labor hace la diferencia<span class="text-[var(--rm-warning)] font-black text-sm not-italic">”</span>

                    </p>

                </div>



                {{-- PIE OPERATIVO COMPACTO --}}

                <div class="pt-2 border-t border-[var(--rm-border)]/40 dark:border-[var(--rm-border)]/60 flex items-center gap-3 text-[11px] font-semibold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">

                    <div class="flex items-center gap-1 text-[var(--rm-clinical)] dark:text-[var(--rm-text-muted)] font-bold">

                        <i class="ph-bold ph-clock text-xs"></i>

                        <span>{{ $dashboard['jornada']['hora_inicio'] ?? '07:00' }} - {{ $dashboard['jornada']['hora_fin'] ?? '15:00' }}</span>

                    </div>

                    <span class="text-[[var(--rm-text-primary)] dark:text-[var(--rm-text-body)]">|</span>

                    <div class="flex items-center gap-1 text-[var(--rm-action-primary)] dark:text-[var(--rm-action-primary)] font-bold">

                        <i class="ph-bold ph-users text-xs"></i>

                        <span>{{ count($dashboard['residentes'] ?? []) }} residentes {{ ($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'cubiertos' : 'asignados' }}</span>

                    </div>

                </div>

            </div>



            {{-- LADO DERECHO: FOTOGRAFÍA INSTITUCIONAL REAL DE MANOS (36%) --}}

            <div class="w-full sm:w-[36%] relative min-h-[120px] sm:min-h-full overflow-hidden shrink-0 border-t sm:border-t-0 sm:border-l border-[var(--rm-border)]/50 dark:border-[var(--rm-border)] bg-[[var(--rm-surface-soft)]">

                <img src="{{ asset('storage/imagenes/ENFERMERIA/manos.png') }}"

                     alt="Atención y cuidado humano en RememberMind"

                     class="w-full h-full object-cover object-center transform group-hover:scale-105 transition-transform duration-500 ease-out"

                     loading="eager"

                     onerror="this.onerror=null; this.src='{{ asset('images/dashboard/manos.png') }}';">



                {{-- Transición sutil en el borde izquierdo para fundir armónicamente --}}

                <div class="absolute inset-y-0 left-0 w-6 bg-gradient-to-r from-[var(--rm-surface)] to-transparent dark:from-[[var(--rm-surface-soft)] pointer-events-none hidden sm:block"></div>

            </div>

        </div>



        {{-- PANEL ESTADO GENERAL (4 COLS) --}}

        <div class="lg:col-span-4 rounded-2xl border border-[var(--rm-border)] dark:border-[var(--rm-border)] bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] shadow-sm overflow-hidden flex flex-col justify-between min-h-[142px] lg:h-[148px] transition-all duration-200 ease-out p-3.5 sm:p-4">

            @php

                $alertaCritica = $dashboard['alerta_critica'] ?? null;

            @endphp



            @if($alertaCritica)

                {{-- CASO CON ALERTA CRÍTICA: ROJO CLÍNICO #D62828 --}}

                <div>

                    {{-- Encabezado con Icono, Título y Badge Crítica --}}

                    <div class="flex items-center justify-between border-b border-rose-200/80 dark:border-rose-900/60 pb-2">

                        <div class="flex items-center gap-1.5">

                            <span class="relative flex h-6 w-6 items-center justify-center rounded-lg bg-rose-100 dark:bg-rose-900/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60 shadow-xs shrink-0">

                                <i class="ph-bold ph-warning-octagon text-sm"></i>

                            </span>

                            <div>

                                <h2 class="text-[11px] font-black uppercase tracking-wider text-rose-800 dark:text-rose-200 leading-tight">

                                    ALERTA CRÍTICA

                                </h2>

                                <p class="text-[9.5px] font-semibold text-rose-700/80 dark:text-rose-300/80 leading-tight">

                                    Requiere atención inmediata

                                </p>

                            </div>

                        </div>

                        <span class="rounded-md bg-rose-100 dark:bg-rose-900/60 text-rose-800 dark:text-rose-200 border border-rose-200/80 dark:border-rose-800/60 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider shadow-xs shrink-0">

                            CRÍTICA

                        </span>

                    </div>



                    {{-- Residente y Motivo --}}

                    <div class="mt-2 rounded-xl border border-rose-200/70 bg-white/70 dark:bg-[var(--rm-surface)]/80 dark:border-rose-900/40 p-2 text-center">

                        <p class="text-xs font-black text-rose-900 dark:text-rose-200 truncate">

                            {{ $alertaCritica['residente_nombre'] ?? ($alertaCritica['residente'] ?? 'Residente asignado') }}

                        </p>

                        <p class="text-[10.5px] font-semibold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] truncate">

                            {{ $alertaCritica['titulo'] ?? 'Atención prioritaria requerida' }}

                        </p>

                    </div>

                </div>



                {{-- Pie con Acción y Tiempo --}}

                <div class="pt-1.5 border-t border-rose-200/60 dark:border-rose-900/50 flex items-center justify-between text-[11px] text-rose-800 dark:text-rose-300 font-bold">

                    <span>{{ $alertaCritica['tiempo_relativo'] ?? ($alertaCritica['tiempo'] ?? 'Hace unos momentos') }}</span>

                    <a href="{{ Route::has('admin.enfermeria.alertas') ? route('admin.enfermeria.alertas') : '#' }}"

                       class="hover:underline flex items-center gap-1">

                        <span>Atender ahora</span>

                        <i class="ph-bold ph-arrow-right text-[10px]"></i>

                    </a>

                </div>

            @else

                {{-- CASO SIN ALERTAS: REFERENCIA EXACTA "ESTADO GENERAL" ESTABLE --}}

                <div>

                    {{-- Encabezado: Icono, Estado General, Badge Estable --}}

                    <div class="flex items-center justify-between border-b border-[var(--rm-action-primary)]/35 dark:border-[var(--rm-border)] pb-2">

                        <div class="flex items-center gap-1.5">

                            <span class="flex h-6 w-6 items-center justify-center rounded bg-[var(--rm-action-primary)]/25 dark:bg-[[var(--rm-surface-soft)]/25 text-[var(--rm-action-primary)] dark:text-[var(--rm-action-primary)] border border-[var(--rm-action-primary)]/40 shadow-2xs shrink-0">

                                <i class="ph-bold ph-shield-check text-sm"></i>

                            </span>

                            <div>

                                <h2 class="text-[11px] font-black uppercase tracking-wider text-[var(--rm-clinical)] dark:text-[[var(--rm-text-primary)]">

                                    ESTADO GENERAL

                                </h2>

                                <p class="text-[9.5px] font-semibold text-[var(--rm-action-primary)] dark:text-[var(--rm-action-primary)]">

                                    Sector asignado

                                </p>

                            </div>

                        </div>

                        <span class="rounded bg-[var(--rm-action-primary)] px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-white shadow-2xs shrink-0">

                            ESTABLE

                        </span>

                    </div>



                    {{-- Mensaje clínico central --}}

                    <div class="mt-2 rounded-lg border border-[var(--rm-border)]/40 dark:border-[var(--rm-border)] bg-[var(--rm-surface)]/70 dark:bg-[var(--rm-surface)]/60 p-2 text-center">

                        <p class="text-xs font-bold text-[var(--rm-clinical)] dark:text-[[var(--rm-text-primary)]">

                            Sin alertas activas en tu turno

                        </p>

                        <p class="mt-0.5 text-[10px] font-medium text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">

                            Todos los residentes asignados se encuentran con signos dentro de rango.

                        </p>

                    </div>

                </div>



                {{-- Línea inferior con "Monitoreo al día" y hora --}}

                <div class="pt-1.5 border-t border-[var(--rm-action-primary)]/30 dark:border-[var(--rm-border)] flex items-center justify-between text-[11px] text-[var(--rm-action-primary)] dark:text-[var(--rm-action-primary)] font-semibold">

                    <span class="inline-flex items-center gap-1">

                        <i class="ph-bold ph-check-circle text-xs text-[var(--rm-action-primary)]"></i>

                        <span>Monitoreo al día</span>

                    </span>

                    <span class="font-bold text-[var(--rm-clinical)] dark:text-[var(--rm-surface)]">{{ now()->format('H:i') }} hrs</span>

                </div>

            @endif

        </div>



    </div>



    {{-- ========================================================= --}}

    {{-- 2. FRANJA COMPACTA DE KPIS (CÁPSULAS CON COLORES MÁS OSCUROS) --}}

    {{-- ========================================================= --}}

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-2.5 sm:gap-3">



        {{-- 1. TOTAL REGISTRO (var(--rm-clinical) - Azul profundo) --}}

        <a href="{{ Route::has('admin.enfermeria.alertas') ? route('admin.enfermeria.alertas') : '#' }}"

           class="group relative overflow-hidden rounded-2xl p-3 sm:p-3.5 transition-all duration-200 ease-out hover:-translate-y-0.5 bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)] hover:border-[var(--rm-clinical)] dark:border-[var(--rm-border)] dark:hover:border-[var(--rm-clinical)] shadow-[0_2px_8px_rgba(47,62,92,0.06)] hover:shadow-[0_6px_16px_rgba(47,62,92,0.12)] border-l-[3.5px] border-l-[var(--rm-clinical)] flex flex-col justify-between h-[88px] sm:h-[92px]"

           aria-label="Ver todas las alertas registradas en el sistema">

            <div class="flex items-center justify-between gap-1.5">

                <span class="text-[11px] font-black tracking-wider uppercase text-[var(--rm-clinical)] dark:text-[var(--rm-text-muted)]">

                    TOTAL REGISTRO

                </span>

                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--rm-clinical-soft)] dark:bg-[var(--rm-clinical-soft)] text-[var(--rm-clinical)] dark:text-[var(--rm-text-muted)] transition-transform duration-200 group-hover:scale-105">

                    <i class="ph-bold ph-bell-simple text-base"></i>

                </span>

            </div>

            <div class="flex items-baseline justify-between gap-2 z-10">

                <div class="text-[26px] sm:text-[28px] font-black leading-none tracking-tight text-[var(--rm-clinical)] dark:text-white">

                    {{ $dashboard['kpis']['total_registro']['numero'] ?? 4 }}

                </div>

                <div class="text-[11px] font-medium text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] truncate">

                    {{ $dashboard['kpis']['total_registro']['texto'] ?? 'Alertas en sistema' }}

                </div>

            </div>

            <div class="absolute bottom-1 right-2 pointer-events-none opacity-40 group-hover:opacity-80 transition-opacity">

                <svg width="34" height="16" viewBox="0 0 34 16" fill="none" class="text-[var(--rm-clinical)] dark:text-[var(--rm-text-muted)]">

                    <path d="M2 14C12 14 18 10 24 5C28 2 31 2 33 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />

                </svg>

            </div>

        </a>



        {{-- 2. CRÍTICAS Y ALTAS (#D62828 - Rojo clínico oscuro) --}}

        <a href="{{ Route::has('admin.enfermeria.alertas') ? route('admin.enfermeria.alertas') : '#' }}"

           class="group relative overflow-hidden rounded-2xl p-3 sm:p-3.5 transition-all duration-200 ease-out hover:-translate-y-0.5 bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)] hover:border-rose-300 dark:border-[var(--rm-border)] dark:hover:border-rose-800 shadow-[0_2px_8px_rgba(163,90,68,0.06)] hover:shadow-[0_6px_16px_rgba(163,90,68,0.12)] border-l-[3.5px] border-l-rose-600 dark:border-l-rose-500 flex flex-col justify-between h-[88px] sm:h-[92px]"

           aria-label="Ver alertas críticas y altas">

            <div class="flex items-center justify-between gap-1.5">

                <span class="text-[11px] font-black tracking-wider uppercase text-rose-800 dark:text-rose-200">

                    CRÍTICAS Y ALTAS

                </span>

                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-100 dark:bg-rose-900/50 text-rose-700 dark:text-rose-300 transition-transform duration-200 group-hover:scale-105 border border-rose-200/60 dark:border-rose-800/50">

                    <i class="ph-bold ph-warning-octagon text-base"></i>

                </span>

            </div>

            <div class="flex items-baseline justify-between gap-2 z-10">

                <div class="text-[26px] sm:text-[28px] font-black leading-none tracking-tight text-rose-800 dark:text-rose-200">

                    {{ $dashboard['kpis']['criticas_altas']['numero'] ?? 0 }}

                </div>

                <div class="text-[11px] font-medium text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] truncate">

                    {{ $dashboard['kpis']['criticas_altas']['texto'] ?? '0 críticas · 0 altas' }}

                </div>

            </div>

            <div class="absolute bottom-1 right-2 pointer-events-none opacity-40 group-hover:opacity-80 transition-opacity">

                <svg width="34" height="16" viewBox="0 0 34 16" fill="none" class="text-rose-600/60 dark:text-rose-400/60">

                    <path d="M2 14C12 14 18 10 24 5C28 2 31 2 33 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />

                </svg>

            </div>

        </a>



        {{-- 3. POR ATENDER (#C98A17 - Ámbar marcado) --}}

        <a href="{{ Route::has('admin.enfermeria.alertas') ? route('admin.enfermeria.alertas') : '#' }}"

           class="group relative overflow-hidden rounded-2xl p-3 sm:p-3.5 transition-all duration-200 ease-out hover:-translate-y-0.5 bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)] hover:border-[var(--rm-warning)] dark:border-[var(--rm-border)] dark:hover:border-[var(--rm-warning)] shadow-sm hover:shadow-md border-l-[3.5px] border-l-[var(--rm-warning)] flex flex-col justify-between h-[88px] sm:h-[92px]"

           aria-label="Ver alertas abiertas por atender">

            <div class="flex items-center justify-between gap-1.5">

                <span class="text-[11px] font-black tracking-wider uppercase text-[var(--rm-warning)] dark:text-[var(--rm-warning)]">

                    POR ATENDER

                </span>

                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--rm-warning-soft)] dark:bg-[var(--rm-warning)]/30 text-[var(--rm-warning)] dark:text-[var(--rm-warning)] transition-transform duration-200 group-hover:scale-105 border border-[[var(--rm-warning)]/60 dark:border-[var(--rm-warning)]/40">

                    <i class="ph-bold ph-clock text-base"></i>

                </span>

            </div>

            <div class="flex items-baseline justify-between gap-2 z-10">

                <div class="text-[26px] sm:text-[28px] font-black leading-none tracking-tight text-[var(--rm-warning)] dark:text-[var(--rm-warning)]">

                    {{ $dashboard['kpis']['por_atender']['numero'] ?? 4 }}

                </div>

                <div class="text-[11px] font-medium text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] truncate">

                    {{ $dashboard['kpis']['por_atender']['texto'] ?? 'Abiertas sin atención' }}

                </div>

            </div>

            <div class="absolute bottom-1 right-2 pointer-events-none opacity-40 group-hover:opacity-80 transition-opacity">

                <svg width="34" height="16" viewBox="0 0 34 16" fill="none" class="text-[var(--rm-warning)]/60 dark:text-[var(--rm-warning)]/60">

                    <path d="M2 14C12 14 18 10 24 5C28 2 31 2 33 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />

                </svg>

            </div>

        </a>



        {{-- 4. EN ATENCIÓN (#243B6B - Azul intenso) --}}

        <a href="{{ Route::has('admin.enfermeria.alertas') ? route('admin.enfermeria.alertas') : '#' }}"

           class="group relative overflow-hidden rounded-2xl p-3 sm:p-3.5 transition-all duration-200 ease-out hover:-translate-y-0.5 bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)] hover:border-[var(--rm-clinical)] dark:border-[var(--rm-border)] dark:hover:border-[var(--rm-clinical)] shadow-sm hover:shadow-md border-l-[3.5px] border-l-[var(--rm-clinical)] flex flex-col justify-between h-[88px] sm:h-[92px]"

           aria-label="Ver alertas en curso de atención">

            <div class="flex items-center justify-between gap-1.5">

                <span class="text-[11px] font-black tracking-wider uppercase text-[var(--rm-clinical)] dark:text-[var(--rm-clinical-soft)]">

                    EN ATENCIÓN

                </span>

                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--rm-clinical)]/12 dark:bg-[var(--rm-clinical)]/25 text-[var(--rm-clinical)] dark:text-[var(--rm-clinical-soft)] transition-transform duration-200 group-hover:scale-105">

                    <i class="ph-bold ph-first-aid text-base"></i>

                </span>

            </div>

            <div class="flex items-baseline justify-between gap-2 z-10">

                <div class="text-[26px] sm:text-[28px] font-black leading-none tracking-tight text-[var(--rm-clinical)] dark:text-[var(--rm-clinical-soft)]">

                    {{ $dashboard['kpis']['en_atencion']['numero'] ?? 0 }}

                </div>

                <div class="text-[11px] font-medium text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] truncate">

                    {{ $dashboard['kpis']['en_atencion']['texto'] ?? 'Protocolo en curso' }}

                </div>

            </div>

            <div class="absolute bottom-1 right-2 pointer-events-none opacity-40 group-hover:opacity-80 transition-opacity">

                <svg width="34" height="16" viewBox="0 0 34 16" fill="none" class="text-[var(--rm-clinical)] dark:text-[var(--rm-clinical-soft)]">

                    <path d="M2 14C12 14 18 10 24 5C28 2 31 2 33 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />

                </svg>

            </div>

        </a>



        {{-- 5. RESUELTAS (var(--rm-action-primary) - Verde salvia oscuro) --}}

        <a href="{{ Route::has('admin.enfermeria.alertas') ? route('admin.enfermeria.alertas') : '#' }}"

           class="group relative overflow-hidden rounded-2xl p-3 sm:p-3.5 transition-all duration-200 ease-out hover:-translate-y-0.5 bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)] hover:border-[var(--rm-action-primary)] dark:border-[var(--rm-border)] dark:hover:border-[var(--rm-action-primary)] shadow-[0_2px_8px_rgba(99,119,91,0.06)] hover:shadow-[0_6px_16px_rgba(99,119,91,0.12)] border-l-[3.5px] border-l-[var(--rm-action-primary)] flex flex-col justify-between h-[88px] sm:h-[92px]"

           aria-label="Ver alertas resueltas e historial">

            <div class="flex items-center justify-between gap-1.5">

                <span class="text-[11px] font-black tracking-wider uppercase text-[var(--rm-action-primary)] dark:text-[var(--rm-action-primary)]">

                    RESUELTAS

                </span>

                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--rm-action-primary)]/12 dark:bg-[var(--rm-action-primary)]/25 text-[var(--rm-action-primary)] dark:text-[var(--rm-action-primary)] transition-transform duration-200 group-hover:scale-105">

                    <i class="ph-bold ph-check-circle text-base"></i>

                </span>

            </div>

            <div class="flex items-baseline justify-between gap-2 z-10">

                <div class="text-[26px] sm:text-[28px] font-black leading-none tracking-tight text-[var(--rm-action-primary)] dark:text-[var(--rm-action-primary)]">

                    {{ $dashboard['kpis']['resueltas']['numero'] ?? 0 }}

                </div>

                <div class="text-[11px] font-medium text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] truncate">

                    {{ $dashboard['kpis']['resueltas']['texto'] ?? 'Historial conservado' }}

                </div>

            </div>

            <div class="absolute bottom-1 right-2 pointer-events-none opacity-40 group-hover:opacity-80 transition-opacity">

                <svg width="34" height="16" viewBox="0 0 34 16" fill="none" class="text-[var(--rm-action-primary)] dark:text-[var(--rm-action-primary)]">

                    <path d="M2 14C12 14 18 10 24 5C28 2 31 2 33 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />

                </svg>

            </div>

        </a>



    </div>



    {{-- ========================================================= --}}

    {{-- 3. ZONA CENTRAL: AGENDA DE HOY (7 COLS) + PROGRESO (5 COLS) --}}

    {{-- ========================================================= --}}

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-3.5 items-start">



        {{-- AGENDA DE HOY (7 COLS): CORAZÓN OPERATIVO PROTAGONISTA --}}

        <section class="lg:col-span-7 rounded-2xl border border-[var(--rm-border)] hover:border-[var(--rm-clinical)]/60 dark:border-[var(--rm-border)] dark:hover:border-[var(--rm-border)] bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] p-3.5 sm:p-4 shadow-sm hover:shadow-md transition-all duration-200 ease-out" aria-label="Agenda operativa de hoy">

            {{-- Cabecera con Ícono Contextual y Enlace a Agenda Completa --}}

            <div class="flex items-center justify-between gap-2 border-b border-[var(--rm-border)]/40 dark:border-[var(--rm-border)] pb-2.5">

                <div class="flex items-center gap-2">

                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--rm-clinical-soft)] text-[var(--rm-clinical)] dark:bg-[var(--rm-clinical-soft)] dark:text-[var(--rm-text-muted)] shrink-0">

                        <i class="ph-bold ph-calendar-check text-base"></i>

                    </span>

                    <div>

                        <h2 class="text-sm sm:text-base font-black text-[var(--rm-clinical)] dark:text-[var(--rm-surface)] tracking-tight leading-tight">

                            {{ ($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Actividad del turno en curso' : 'Agenda de hoy' }}

                        </h2>

                        <p class="text-[11px] font-semibold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">

                            {{ ($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Intervenciones programadas del turno activo (Solo lectura)' : 'Intervenciones programadas en tu guardia' }}

                        </p>

                    </div>

                </div>

                <a href="{{ Route::has('admin.enfermeria.agenda') ? route('admin.enfermeria.agenda') : '#' }}"

                   class="group/link text-[11px] font-extrabold text-[var(--rm-clinical)] dark:text-[var(--rm-text-muted)] hover:text-[var(--rm-warning)] dark:hover:text-[var(--rm-warning)] flex items-center gap-1 transition-colors duration-200">

                    <span>Ver agenda completa</span>

                    <i class="ph-bold ph-arrow-right text-[10px] group-hover/link:translate-x-0.5 transition-transform duration-200"></i>

                </a>

            </div>



            {{-- LISTA DE EVENTOS CLÍNICOS REFINADA CON DESPLAZAMIENTO SUAVE --}}

            <div class="mt-2.5 space-y-2 max-h-[360px] overflow-y-auto pr-1 custom-scrollbar">

                @php

                    $agendaItems = $dashboard['agenda'] ?? ($dashboard['agenda_hoy'] ?? []);

                @endphp

                @forelse($agendaItems as $evento)

                    @php

                        $estado = strtolower($evento['estado'] ?? 'pendiente');

                        if (str_contains($estado, 'complet') || str_contains($estado, 'realiz')) {

                            $badgeClase = 'bg-[var(--rm-surface-soft)] text-[var(--rm-action-primary)] border border-[[var(--rm-success-soft)] dark:bg-[var(--rm-action-primary)]/25 dark:text-[var(--rm-action-primary)] dark:border-[var(--rm-action-primary)]';

                            $iconoEstado = 'ph-check-circle text-[var(--rm-action-primary)] dark:text-[var(--rm-action-primary)]';

                            $bordeLeft = 'border-l-[3.5px] border-l-[var(--rm-action-primary)]';

                        } elseif (str_contains($estado, 'retras') || str_contains($estado, 'crit')) {

                            $badgeClase = 'bg-rose-100 text-rose-800 border border-rose-200/80 dark:bg-rose-900/50 dark:text-rose-200 dark:border-rose-800/60';

                            $iconoEstado = 'ph-warning-circle text-rose-700 dark:text-rose-300';

                            $bordeLeft = 'border-l-[3.5px] border-l-rose-500 dark:border-l-rose-400';

                        } else {

                            $badgeClase = 'bg-[var(--rm-surface)] text-[var(--rm-warning)] border border-[[var(--rm-warning)] dark:bg-[var(--rm-warning-soft)] dark:text-[var(--rm-warning)] dark:border-[var(--rm-warning)]/40';

                            $iconoEstado = 'ph-clock text-[var(--rm-warning)] dark:text-[var(--rm-warning)]';

                            $bordeLeft = 'border-l-[3.5px] border-l-[var(--rm-warning)]';

                        }

                    @endphp

                    <div class="group/item flex items-center justify-between gap-2.5 rounded-xl border border-[var(--rm-border)]/40 dark:border-[var(--rm-border)] bg-[var(--rm-surface)]/70 dark:bg-[var(--rm-surface)]/80 p-2.5 hover:bg-[var(--rm-surface)] dark:hover:bg-[var(--rm-surface)] hover:shadow-xs transition-all duration-150 {{ $bordeLeft }}">

                        <div class="flex items-center gap-2.5 min-w-0">

                            {{-- Hora en fuente monoespaciada tabular --}}

                            <span class="font-mono text-xs font-black text-[var(--rm-clinical)] dark:text-[var(--rm-text-muted)] shrink-0 bg-[var(--rm-clinical-soft)] dark:bg-[var(--rm-clinical-soft)] px-1.5 py-0.5 rounded">

                                {{ $evento['hora'] ?? '08:00' }}

                            </span>



                            {{-- Ícono por Tipo de Evento --}}

                            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)]/40 dark:border-[var(--rm-border)] shrink-0">

                                @if(str_contains(strtolower($evento['tipo'] ?? ''), 'medic'))

                                    <i class="ph-bold ph-pill text-xs text-[var(--rm-clinical)]"></i>

                                @elseif(str_contains(strtolower($evento['tipo'] ?? ''), 'signo') || str_contains(strtolower($evento['tipo'] ?? ''), 'vital'))

                                    <i class="ph-bold ph-heartbeat text-xs text-rose-600 dark:text-rose-400"></i>

                                @else

                                    <i class="ph-bold ph-stethoscope text-xs text-[var(--rm-action-primary)]"></i>

                                @endif

                            </span>



                            {{-- Detalle del Evento --}}

                            <div class="min-w-0">

                                <div class="text-xs font-bold text-[var(--rm-clinical)] dark:text-[var(--rm-surface)] truncate">

                                    {{ $evento['accion'] ?? ($evento['tarea'] ?? ($evento['titulo'] ?? 'Control de enfermería')) }}

                                </div>

                                <div class="text-[11px] font-semibold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] truncate">

                                    {{ $evento['residente'] ?? ($evento['residente_nombre'] ?? 'Residente') }}

                                    @if(!empty($evento['ubicacion']))

                                        <span class="text-[var(--rm-text-muted)] dark:text-[var(--rm-text-muted)]">· {{ $evento['ubicacion'] }}</span>

                                    @elseif(!empty($evento['habitacion']))

                                        <span class="text-[var(--rm-text-muted)] dark:text-[var(--rm-text-muted)]">· {{ $evento['habitacion'] }}</span>

                                    @endif

                                </div>

                            </div>

                        </div>



                        {{-- Badge de Estado Clínico --}}

                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider shrink-0 {{ $badgeClase }}">

                            <i class="ph-bold {{ $iconoEstado }} text-[11px]"></i>

                            <span>{{ $evento['estado'] ?? 'Pendiente' }}</span>

                        </span>

                    </div>

                @empty

                    <div class="py-8 text-center text-xs font-semibold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">

                        No hay intervenciones programadas para el resto de este turno.

                    </div>

                @endforelse

            </div>

        </section>



        {{-- PROGRESO DEL TURNO Y DISTRIBUCIÓN (5 COLS): GRÁFICAS INTEGRADAS Y CONTRASTADAS --}}

        <section class="lg:col-span-5 rounded-2xl border border-[var(--rm-border)] hover:border-[var(--rm-clinical)]/60 dark:border-[var(--rm-border)] dark:hover:border-[var(--rm-border)] bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] p-3.5 sm:p-4 shadow-sm hover:shadow-md space-y-3 transition-all duration-200 ease-out" aria-label="Progreso del turno">

            {{-- Título con Ícono Contextual --}}

            <div class="flex items-center justify-between border-b border-[var(--rm-border)]/40 dark:border-[var(--rm-border)] pb-2">

                <div class="flex items-center gap-2">

                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] dark:text-[var(--rm-action-primary)] shrink-0">

                        <i class="ph-bold ph-chart-donut text-base"></i>

                    </span>

                    <div>

                        <h2 class="text-sm sm:text-base font-black text-[var(--rm-clinical)] dark:text-[var(--rm-surface)] tracking-tight leading-tight">

                            {{ ($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Progreso del turno en curso' : 'Progreso del turno' }}

                        </h2>

                        <p class="text-[11px] font-semibold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">

                            {{ ($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Cumplimiento del turno activo' : 'Cumplimiento de tareas operativas' }}

                        </p>

                    </div>

                </div>

                @if(($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO')

                    <span class="rounded bg-[var(--rm-clinical-soft)] dark:bg-[var(--rm-clinical-soft)] px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-[var(--rm-clinical)] dark:text-[var(--rm-text-muted)]">

                        Solo lectura

                    </span>

                @endif

            </div>



            {{-- Bloque Donut: Grosor Óptimo y Colores Vivos --}}

            <div class="rounded-xl border border-[var(--rm-border)]/40 dark:border-[var(--rm-border)] bg-[var(--rm-surface)]/70 dark:bg-[var(--rm-surface)]/80 p-3 shadow-2xs">

                @php

                    $pctCumplimiento = $dashboard['estado_tareas']['porcentaje'] ?? ($dashboard['progreso']['cumplimiento'] ?? 0);
                    $totalTareasProg = (int)($dashboard['estado_tareas']['total'] ?? ($dashboard['progreso']['total'] ?? 0));

                @endphp

                <div class="flex items-center justify-between text-xs font-extrabold text-[var(--rm-clinical)] dark:text-[var(--rm-surface)] mb-1">

                    <span>Cumplimiento Global</span>

                    <span class="text-[var(--rm-action-primary)] dark:text-[var(--rm-action-primary)] font-black text-sm">{{ $pctCumplimiento }}%</span>

                </div>



                <div class="h-36 sm:h-40 relative w-full flex items-center justify-center">

                    <canvas id="graficoCumplimientoTurno"></canvas>

                    {{-- Centro Sólido y Limpio del Donut --}}

                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">

                        <span class="text-xl font-black text-[var(--rm-clinical)] dark:text-[var(--rm-surface)] leading-none">{{ $pctCumplimiento }}%</span>

                        <span class="text-[9px] font-black uppercase tracking-wider text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] mt-0.5">{{ ($totalTareasProg ?? 0) > 0 ? 'Completado' : 'Sin tareas' }}</span>

                    </div>

                </div>



                {{-- Leyenda y Totales de Alta Jerarquía --}}

                <div class="mt-2 flex items-center justify-around text-[11px] font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] border-t border-[var(--rm-border)]/30 dark:border-[var(--rm-border)] pt-1.5">

                    <span class="flex items-center gap-1.5">

                        <span class="h-2.5 w-2.5 rounded-full bg-[var(--rm-action-primary)]"></span>

                        <span>Realizadas</span>

                    </span>

                    <span class="flex items-center gap-1.5">

                        <span class="h-2.5 w-2.5 rounded-full bg-[var(--rm-warning)]"></span>

                        <span>Pendientes</span>

                    </span>

                    <span class="flex items-center gap-1.5">

                        <span class="h-2.5 w-2.5 rounded-full bg-[var(--rm-danger)]"></span>

                        <span>Con retraso</span>

                    </span>

                </div>

            </div>



            {{-- Bloque Distribución de Cuidados --}}

            <div class="rounded-xl border border-[var(--rm-border)]/40 dark:border-[var(--rm-border)] bg-[var(--rm-surface)]/70 dark:bg-[var(--rm-surface)]/80 p-3 shadow-2xs">

                <div class="flex items-center justify-between text-xs font-extrabold text-[var(--rm-clinical)] dark:text-[var(--rm-surface)] mb-1">

                    <span>Distribución de Cuidados</span>

                    <span class="text-[10px] text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] font-bold uppercase tracking-wider">{{ ($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Información del turno activo en modo consulta' : 'Activas' }}</span>

                </div>

                <div class="h-36 sm:h-40 relative w-full flex items-center justify-center">

                    <canvas id="graficoDistribucionTurno"></canvas>

                </div>

            </div>

        </section>



    </div>



    {{-- ========================================================= --}}

    {{-- 4. ZONA INFERIOR: RESIDENTES (8 COLS) + ALERTAS (4 COLS) --}}

    {{-- ========================================================= --}}

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-3.5 items-start">



        {{-- RESIDENTES DE MI TURNO (8 COLS): FORMATO EDITORIAL CLÍNICO --}}

        <section class="lg:col-span-8 rounded-2xl border border-[var(--rm-border)] hover:border-[var(--rm-clinical)]/60 dark:border-[var(--rm-border)] dark:hover:border-[var(--rm-border)] bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] p-3.5 sm:p-4 shadow-sm hover:shadow-md transition-all duration-200 ease-out flex flex-col justify-between" aria-label="Residentes asignados en mi turno">

            {{-- Cabecera con Ícono Contextual + Enlace Textual con Chevron --}}

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 border-b border-[var(--rm-border)]/40 dark:border-[var(--rm-border)] pb-2.5">

                <div class="flex items-center gap-2">

                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--rm-clinical-soft)] text-[var(--rm-clinical)] dark:bg-[var(--rm-clinical-soft)] dark:text-[var(--rm-text-muted)] shrink-0">

                        <i class="ph-bold ph-users-three text-base"></i>

                    </span>

                    <div>

                        <h2 class="text-sm sm:text-base font-black text-[var(--rm-clinical)] dark:text-[var(--rm-surface)] tracking-tight leading-tight">

                            {{ ($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Residentes del turno en curso' : 'Residentes de mi turno' }}

                        </h2>

                        <p class="text-[11px] font-semibold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">

                            {{ ($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Personas actualmente cubiertas por el equipo de guardia' : 'Personas asignadas a tu cuidado directo' }}

                        </p>

                    </div>

                </div>

                <a href="{{ Route::has('admin.enfermeria.residentes') ? route('admin.enfermeria.residentes') : (Route::has('admin.enfermeria.pacientes') ? route('admin.enfermeria.pacientes') : '#') }}"

                   class="group/link text-[11px] font-extrabold text-[var(--rm-clinical)] dark:text-[var(--rm-text-muted)] hover:text-[var(--rm-warning)] dark:hover:text-[var(--rm-warning)] flex items-center gap-1 transition-colors duration-200 self-start sm:self-auto">

                    <span>Ver todos</span>

                    <i class="ph-bold ph-arrow-right text-[10px] group-hover/link:translate-x-0.5 transition-transform duration-200"></i>

                </a>

            </div>



            {{-- LISTA DE RESIDENTES CON BARRA DESPLAZABLE SUAVE (custom-scrollbar) --}}

            <div class="mt-2.5 space-y-2 max-h-[380px] overflow-y-auto pr-1 custom-scrollbar">

                @php

                    $residentesList = $dashboard['residentes'] ?? [];

                @endphp

                @forelse($residentesList as $residente)

                    @php

                        $alertCnt = $residente['alertas_count'] ?? 0;

                        $resId = $residente['cod_residente'] ?? ($residente['id'] ?? ($residente['cod_adulto_mayor'] ?? ''));

                        $rutaFicha = '#';

                        if (Route::has('admin.enfermeria.pacientes') && !empty($resId)) {
                            $rutaFicha = route('admin.enfermeria.pacientes', ['residente' => $resId]);
                        } elseif (Route::has('admin.enfermeria.residentes') && !empty($resId)) {
                            $rutaFicha = route('admin.enfermeria.residentes', ['residente' => $resId]);
                        }

                    @endphp

                    <a href="{{ $rutaFicha }}"

                       class="group/res block rounded-xl border border-[var(--rm-border)]/40 dark:border-[var(--rm-border)] bg-[var(--rm-surface)]/70 dark:bg-[var(--rm-surface)]/80 p-2.5 hover:bg-[var(--rm-surface)] dark:hover:bg-[var(--rm-surface)] hover:border-[var(--rm-clinical)]/60 dark:hover:border-[[var(--rm-clinical-soft)]/60 hover:shadow-xs transition-all duration-150">

                        <div class="flex items-center justify-between gap-3">

                            <div class="flex items-center gap-3 min-w-0">

                                {{-- Avatar o Iniciales --}}

                                <div class="relative shrink-0">

                                    @if(!empty($residente['foto']))

                                        <img src="{{ asset($residente['foto']) }}"

                                             alt="{{ $residente['nombre_completo'] ?? ($residente['nombre'] ?? 'Residente') }}"

                                             class="h-10 w-10 rounded-full object-cover border border-[var(--rm-border)] dark:border-[var(--rm-border)]">

                                    @else

                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--rm-clinical-soft)] dark:bg-[var(--rm-clinical-soft)] text-[var(--rm-clinical)] dark:text-[var(--rm-text-muted)] font-black text-xs border border-[var(--rm-clinical)]/25">

                                            {{ $residente['iniciales'] ?? strtoupper(substr($residente['nombre_completo'] ?? ($residente['nombre'] ?? 'R'), 0, 2)) }}

                                        </div>

                                    @endif

                                </div>



                                {{-- Información Clínica: Nombre + Edad/Ubicación + Chips --}}

                                <div class="min-w-0">

                                    <div class="text-xs sm:text-[13px] font-bold text-[var(--rm-clinical)] dark:text-[var(--rm-surface)] truncate group-hover/res:text-[var(--rm-warning)] dark:group-hover/res:text-[var(--rm-warning)] transition-colors">

                                        {{ $residente['nombre_completo'] ?? ($residente['nombre'] ?? 'Residente') }}

                                    </div>

                                    <div class="text-[11px] font-medium text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] truncate">

                                        {{ $residente['edad'] ?? '80 años' }} ·

                                        <span class="font-bold text-[var(--rm-clinical)] dark:text-[[var(--rm-text-primary)]">

                                            {{ $residente['ubicacion'] ?? ($residente['cama_texto'] ?? 'Hab. 102 · Cama A') }}

                                        </span>

                                    </div>



                                    {{-- Chips Clínicos: Estado / Supervisión / Movilidad / Alertas --}}

                                    <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[10px]">

                                        {{-- Estado de seguimiento operativo vs institucional --}}
                                        @php
                                            $estSeg = $residente['estado_seguimiento'] ?? ($residente['estado_label'] ?? 'ESTABLE');
                                            $segColor = match(strtoupper(trim((string)$estSeg))) {
                                                'CRÍTICO', 'CRITICO', 'REQUIERE_ATENCION' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-200 border border-rose-200/80 dark:border-rose-800/60',
                                                'VIGILANCIA' => 'bg-[var(--rm-surface)] text-[var(--rm-warning)] border border-[[var(--rm-warning)] dark:bg-[var(--rm-warning-soft)] dark:text-[var(--rm-warning)] dark:border-[var(--rm-warning)]/40',
                                                default => 'bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] dark:text-[var(--rm-action-primary)]',
                                            };
                                        @endphp
                                        <span class="px-1.5 py-0.5 rounded font-extrabold uppercase {{ $segColor }}" title="Estado de seguimiento">
                                            {{ $estSeg }}
                                        </span>

                                        <span class="px-1.5 py-0.5 rounded font-bold bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)]/50 dark:border-[var(--rm-border)] text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]" title="Nivel de supervisión">
                                            {{ $residente['supervision_label'] ?? 'Supervisión moderada' }}
                                        </span>

                                        @if(!empty($residente['estado_institucional'] ?? $residente['estado_operacional']))
                                            <span class="px-1.5 py-0.5 rounded font-bold bg-[var(--rm-clinical-soft)] dark:bg-[var(--rm-clinical-soft)] text-[var(--rm-clinical)] dark:text-[var(--rm-text-muted)] uppercase" title="Estado institucional">
                                                Institucional: {{ $residente['estado_institucional'] ?? $residente['estado_operacional'] }}
                                            </span>
                                        @endif

                                        @if(!empty($residente['turno_actual']))

                                            <span class="px-1.5 py-0.2 rounded font-bold bg-[var(--rm-surface)] text-[var(--rm-warning)] border border-[[var(--rm-warning)] dark:bg-[var(--rm-warning-soft)] dark:text-[var(--rm-warning)] dark:border-[var(--rm-warning)]/40">

                                                {{ $residente['turno_actual'] }}

                                            </span>

                                        @endif

                                        @if(!empty($residente['responsable_texto']))

                                            <span class="px-1.5 py-0.5 rounded font-bold bg-[var(--rm-clinical-soft)] dark:bg-[var(--rm-clinical)]/35 text-[var(--rm-clinical)] dark:text-[var(--rm-text-muted)] inline-flex items-center gap-1">

                                                <i class="ph-bold ph-user-check text-[11px] text-[var(--rm-action-primary)]"></i>

                                                <span>{{ $residente['responsable_texto'] }}</span>

                                            </span>

                                        @endif

                                        <span class="px-1.5 py-0.2 rounded font-bold bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)]/50 dark:border-[var(--rm-border)] text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">

                                            {{ $residente['movilidad_label'] ?? 'Movilidad asistida' }}

                                        </span>



                                        @if($alertCnt > 0)

                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded font-black text-rose-800 dark:text-rose-200 bg-rose-100 dark:bg-rose-900/50 border border-rose-200/80 dark:border-rose-800/60">

                                                <i class="ph-bold ph-bell-ringing text-[10px] text-rose-600 dark:text-rose-400"></i>

                                                <span>{{ $alertCnt }} {{ $alertCnt === 1 ? 'alerta' : 'alertas' }}</span>

                                            </span>

                                        @else

                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded font-semibold text-[var(--rm-action-primary)] dark:text-[var(--rm-action-primary)] bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-action-primary)]/25 border border-[[var(--rm-success-soft)]/60 dark:border-[var(--rm-action-primary)]/40">

                                                <i class="ph-bold ph-check text-[10px]"></i>

                                                <span>Sin alertas</span>

                                            </span>

                                        @endif

                                    </div>

                                </div>

                            </div>



                            {{-- Chevron de Navegación --}}

                            <div class="hidden sm:flex items-center justify-center pl-2">

                                <i class="ph-bold ph-caret-right text-sm text-[var(--rm-text-muted)] dark:text-[var(--rm-text-muted)] opacity-50 group-hover/res:opacity-100 group-hover/res:translate-x-0.5 transition-all duration-150"></i>

                            </div>

                        </div>

                    </a>

                @empty

                    <div class="py-6 text-center text-xs font-semibold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">

                        No tienes residentes asignados directamente en este sector.

                    </div>

                @endforelse

            </div>

        </section>



        {{-- ALERTAS RECIENTES (4 COLS): STREAM DE NOTIFICACIONES CLÍNICAS --}}

        <section class="lg:col-span-4 rounded-2xl border border-[var(--rm-border)] hover:border-[var(--rm-clinical)]/60 dark:border-[var(--rm-border)] dark:hover:border-[var(--rm-border)] bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] p-3.5 sm:p-4 shadow-sm hover:shadow-md transition-all duration-200 ease-out flex flex-col justify-between" aria-label="Alertas recientes del turno">

            {{-- Cabecera con Ícono Contextual + Enlace Textual con Chevron --}}

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 border-b border-[var(--rm-border)]/40 dark:border-[var(--rm-border)] pb-2.5">

                <div class="flex items-center gap-2">

                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/60 shrink-0">

                        <i class="ph-bold ph-bell-ringing text-base"></i>

                    </span>

                    <div>

                        <h2 class="text-sm sm:text-base font-black text-[var(--rm-clinical)] dark:text-[var(--rm-surface)] tracking-tight leading-tight">

                            Alertas recientes

                        </h2>

                        <p class="text-[11px] font-semibold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">

                            Notificaciones clínicas de atención

                        </p>

                    </div>

                </div>

                <a href="{{ Route::has('admin.enfermeria.alertas') ? route('admin.enfermeria.alertas') : '#' }}"

                   class="group/link text-[11px] font-extrabold text-rose-700 dark:text-rose-300 hover:text-rose-800 dark:hover:text-rose-200 hover:underline flex items-center gap-1 transition-colors duration-200 self-start sm:self-auto">

                    <span>Ver todas</span>

                    <i class="ph-bold ph-arrow-right text-[10px] group-hover/link:translate-x-0.5 transition-transform duration-200"></i>

                </a>

            </div>



            {{-- Lista de Alertas con Barra de Desplazamiento Suave --}}

            <div class="mt-2.5 space-y-2 max-h-[380px] overflow-y-auto pr-1 custom-scrollbar">

                @php

                    $alertasRecientes = $dashboard['alertas_recientes'] ?? ($dashboard['alertas'] ?? []);

                @endphp

                @forelse($alertasRecientes as $alerta)

                    @php

                        $prioridad = strtoupper($alerta['prioridad'] ?? ($alerta['nivel'] ?? 'MEDIA'));

                        if ($prioridad === 'CRITICA' || $prioridad === 'CRÍTICA' || $prioridad === 'CRITICO' || $prioridad === 'ALTA' || $prioridad === 'ALTO') {

                            $bordeLeft = 'border-l-[3.5px] border-l-rose-500 bg-rose-50/70 hover:bg-rose-100/70 dark:bg-rose-950/30 dark:hover:bg-rose-900/40';

                            $badgeStyle = 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200 border border-rose-200/80 dark:border-rose-800/60';

                            $iconoAlerta = 'ph-warning-octagon text-rose-600 dark:text-rose-400';

                        } elseif ($prioridad === 'MEDIA' || $prioridad === 'MEDIO') {

                            $bordeLeft = 'border-l-[3.5px] border-l-[var(--rm-warning)] bg-[var(--rm-surface)]/60 hover:bg-[var(--rm-surface)] dark:bg-[var(--rm-warning-soft)] dark:hover:bg-[var(--rm-warning)]/25';

                            $badgeStyle = 'bg-[var(--rm-surface)] text-[var(--rm-warning)] border border-[[var(--rm-warning)] dark:bg-[var(--rm-warning-soft)] dark:text-[var(--rm-warning)] dark:border-[var(--rm-warning)]/40';

                            $iconoAlerta = 'ph-warning text-[var(--rm-warning)] dark:text-[var(--rm-warning)]';

                        } else {

                            $bordeLeft = 'border-l-[3.5px] border-l-[var(--rm-clinical)] bg-[var(--rm-surface-soft)]/60 hover:bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-clinical-soft)] dark:hover:bg-[var(--rm-clinical-soft)]';

                            $badgeStyle = 'bg-[var(--rm-clinical-soft)] text-[var(--rm-clinical)] dark:bg-[var(--rm-clinical-soft)] dark:text-[var(--rm-text-secondary)] border border-[var(--rm-clinical)]/20 dark:border-[var(--rm-clinical)]/40';

                            $iconoAlerta = 'ph-info text-[var(--rm-clinical)] dark:text-[var(--rm-text-secondary)]';

                        }

                    @endphp

                    <a href="{{ Route::has('admin.enfermeria.alertas') ? route('admin.enfermeria.alertas') : '#' }}"

                       class="group/alerta block rounded-xl border border-[var(--rm-border)]/40 dark:border-[var(--rm-border)] p-2.5 hover:-translate-y-0.5 transition-all duration-150 {{ $bordeLeft }}">

                        <div class="flex items-start justify-between gap-1.5">

                            <div class="flex items-center gap-1.5 min-w-0">

                                <i class="ph-bold {{ $iconoAlerta }} text-xs shrink-0"></i>

                                <h3 class="text-xs font-bold text-[var(--rm-clinical)] dark:text-[var(--rm-surface)] truncate">

                                    {{ $alerta['titulo'] ?? ($alerta['motivo'] ?? 'Notificación') }}

                                </h3>

                            </div>

                            <span class="rounded px-1.5 py-0.2 text-[8.5px] font-black uppercase tracking-wider shrink-0 {{ $badgeStyle }}">

                                {{ $prioridad }}

                            </span>

                        </div>



                        <div class="mt-1 flex items-center justify-between text-[10.5px] text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">

                            <span class="truncate font-semibold">{{ $alerta['residente'] ?? ($alerta['residente_nombre'] ?? '') }}</span>

                            <div class="flex items-center gap-1 shrink-0 font-bold">

                                <span>{{ $alerta['tiempo_relativo'] ?? ($alerta['tiempo'] ?? 'Hace 10m') }}</span>

                                <i class="ph-bold ph-caret-right text-[9px] text-[var(--rm-text-muted)]/60 opacity-0 group-hover/alerta:opacity-100 group-hover/alerta:translate-x-0.5 transition-all duration-150"></i>

                            </div>

                        </div>

                    </a>

                @empty

                    <div class="py-6 text-center text-xs font-semibold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">

                        No hay alertas pendientes en tu turno.

                    </div>

                @endforelse

            </div>

        </section>



    </div>



</div>



{{-- SCRIPT PARA GRÁFICOS CHART.JS REFINADOS Y REACTIVOS --}}

@push('scripts')

<script>

    document.addEventListener('DOMContentLoaded', function () {

        let cumplimientoChart = null;

        let distribucionChart = null;



        function getChartColors(isDark) {

            return {

                text: isDark ? 'var(--rm-surface)' : 'var(--rm-clinical)',

                subtext: isDark ? '#A6B2C8' : 'var(--rm-text-secondary)',

                grid: isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(47, 62, 92, 0.06)',

                border: isDark ? '#2D2924' : 'var(--rm-surface)',

                completadas: 'var(--rm-action-primary)', // Verde salvia oscuro

                pendientes: '#D2A45E',  // Ámbar

                retrasadas: 'var(--rm-danger)',  // Rojo clínico

                medicacion: 'var(--rm-clinical)',  // Azul profundo principal

                signos: '#71876A',      // Azul intenso

                higiene: 'var(--rm-action-primary)',     // Verde salvia

                movilizacion: '#D2A45E' // Ámbar

            };

        }



        function initOrUpdateCharts() {

            if (typeof Chart === 'undefined') return;



            const isDark = document.documentElement.classList.contains('dark') || document.documentElement.getAttribute('data-theme') === 'dark';

            const colors = getChartColors(isDark);



            // 1. Gráfico de Cumplimiento (Donut)

            const ctxCumplimiento = document.getElementById('graficoCumplimientoTurno');

            if (ctxCumplimiento) {

                const completadas = {{ $dashboard['estado_tareas']['realizadas'] ?? ($dashboard['progreso']['completadas'] ?? 0) }};

                const pendientes = {{ $dashboard['estado_tareas']['pendientes'] ?? ($dashboard['progreso']['pendientes'] ?? 0) }};

                const retrasadas = {{ $dashboard['estado_tareas']['retrasadas'] ?? ($dashboard['progreso']['retrasadas'] ?? 0) }};



                if (cumplimientoChart) {

                    cumplimientoChart.data.datasets[0].borderColor = colors.border;

                    cumplimientoChart.data.datasets[0].backgroundColor = [colors.completadas, colors.pendientes, colors.retrasadas];

                    cumplimientoChart.update();

                } else {

                    cumplimientoChart = new Chart(ctxCumplimiento, {

                        type: 'doughnut',

                        data: {

                            labels: ['Realizadas', 'Pendientes', 'Con retraso'],

                            datasets: [{

                                data: [completadas, pendientes, retrasadas],

                                backgroundColor: [colors.completadas, colors.pendientes, colors.retrasadas],

                                borderWidth: 3,

                                borderColor: colors.border,

                                hoverOffset: 4

                            }]

                        },

                        options: {

                            responsive: true,

                            maintainAspectRatio: false,

                            cutout: '62%',

                            plugins: {

                                legend: { display: false },

                                tooltip: {

                                    backgroundColor: isDark ? '#1A1C1D' : 'var(--rm-surface)',

                                    titleColor: colors.text,

                                    bodyColor: colors.text,

                                    borderColor: isDark ? '#494139' : '#D5CABE',

                                    borderWidth: 1,

                                    padding: 10,

                                    boxPadding: 4,

                                    usePointStyle: true,

                                    callbacks: {

                                        label: function(context) {

                                            return ` ${context.label}: ${context.raw} tareas`;

                                        }

                                    }

                                }

                            }

                        }

                    });

                }

            }



            // 2. Gráfico de Distribución (Barras horizontales)

            const ctxDistribucion = document.getElementById('graficoDistribucionTurno');

            if (ctxDistribucion) {

                const distMed = {{ (int)($dashboard['distribucion']['medicacion'] ?? 0) }};

                const distSignos = {{ (int)($dashboard['distribucion']['signos'] ?? 0) }};

                const distHigiene = {{ (int)($dashboard['distribucion']['higiene'] ?? 0) }};

                const distMov = {{ (int)($dashboard['distribucion']['movilizacion'] ?? 0) }};



                if (distribucionChart) {

                    distribucionChart.options.scales.x.grid.color = colors.grid;

                    distribucionChart.options.scales.x.ticks.color = colors.subtext;

                    distribucionChart.options.scales.y.ticks.color = colors.text;

                    distribucionChart.data.datasets[0].backgroundColor = [colors.medicacion, colors.signos, colors.higiene, colors.movilizacion];

                    distribucionChart.update();

                } else {

                    distribucionChart = new Chart(ctxDistribucion, {

                        type: 'bar',

                        data: {

                            labels: ['Medicación', 'Signos', 'Higiene', 'Movilidad'],

                            datasets: [{

                                data: [distMed, distSignos, distHigiene, distMov],

                                backgroundColor: [colors.medicacion, colors.signos, colors.higiene, colors.movilizacion],

                                borderRadius: 5,

                                barThickness: 14

                            }]

                        },

                        options: {

                            indexAxis: 'y',

                            responsive: true,

                            maintainAspectRatio: false,

                            scales: {

                                x: {

                                    grid: { color: colors.grid, drawBorder: false },

                                    ticks: { color: colors.subtext, font: { family: 'Outfit', size: 10, weight: '600' }, stepSize: 1 }

                                },

                                y: {

                                    grid: { display: false, drawBorder: false },

                                    ticks: { color: colors.text, font: { family: 'Outfit', size: 11, weight: '700' } }

                                }

                            },

                            plugins: {

                                legend: { display: false },

                                tooltip: {

                                    backgroundColor: isDark ? '#1A1C1D' : 'var(--rm-surface)',

                                    titleColor: colors.text,

                                    bodyColor: colors.text,

                                    borderColor: isDark ? '#494139' : '#D5CABE',

                                    borderWidth: 1,

                                    padding: 10,

                                    boxPadding: 4,

                                    callbacks: {

                                        label: function(context) {

                                            return ` Acciones programadas: ${context.raw}`;

                                        }

                                    }

                                }

                            }

                        }

                    });

                }

            }

        }



        initOrUpdateCharts();



        // Escuchar cambios de tema (dark mode)

        window.addEventListener('remembermind:theme-changed', function() {

            setTimeout(initOrUpdateCharts, 30);

        });



        const observer = new MutationObserver(function(mutations) {

            mutations.forEach(function(mutation) {

                if (mutation.attributeName === 'class' || mutation.attributeName === 'data-theme') {

                    initOrUpdateCharts();

                }

            });

        });

        observer.observe(document.documentElement, { attributes: true });

    });

</script>

@endpush