{{--

    Vista: Mi turno (Dashboard de Enfermería) - Versión Refinada UX/UI

    RememberMind - Módulo de Enfermería

    Estética Editorial Clínica Cálida: Outfit, Paleta profunda RememberMind (#304060, #63775B, #C98A17, #D62828, #E6DDD3, #F0E8DE)

    Foto del Hero verificada en asset('storage/imagenes/ENFERMERIA/manos.png')

--}}



<div class="space-y-3.5 sm:space-y-4 font-outfit" wire:poll.60s="refrescarTurno">



    {{-- ========================================================= --}}

    {{-- 1. ZONA SUPERIOR: HERO COMPACTO (8 COLS) + ESTADO (4 COLS) --}}

    {{-- ========================================================= --}}

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-3.5 items-stretch">



        {{-- HERO PRINCIPAL COMPACTO Y EDITORIAL (8 COLS) --}}

        <div class="group lg:col-span-8 rounded-[16px] border border-[#D5CABE] hover:border-[#304060]/60 dark:border-[#383C3D] dark:hover:border-[#52585A] bg-[#F0E8DE] dark:bg-[#222527] shadow-[0_3px_12px_rgba(47,62,92,0.06)] hover:shadow-[0_8px_20px_rgba(47,62,92,0.12)] overflow-hidden flex flex-col sm:flex-row min-h-[142px] lg:h-[148px] transition-all duration-200 ease-out">



            {{-- LADO IZQUIERDO: TEXTO CLÍNICO REFINADO (64%) --}}

            <div class="w-full sm:w-[64%] p-3.5 sm:p-4 flex flex-col justify-between z-10">

                <div>

                    {{-- LÍNEA SUPERIOR DE FECHA, JORNADA Y MODO OPERATIVO --}}

                    <div class="flex flex-wrap items-center gap-1.5 text-[11px] font-bold text-[#677084] dark:text-[#A6B2C8]">

                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#304060]/12 text-[#304060] dark:bg-[#304060]/30 dark:text-[#A6B2C8] font-black uppercase tracking-wider text-[10px]">

                            <i class="ph-bold ph-sun text-xs"></i>

                            <span>{{ $dashboard['jornada']['nombre'] ?? ($dashboard['turno']['periodo'] ?? 'Turno en curso') }}</span>

                        </span>

                        <span class="text-[#967B66]/60 dark:text-[#C4BCB3]/50">•</span>

                        <span class="capitalize">{{ $dashboard['jornada']['fecha_humana'] ?? \Carbon\Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM') }}</span>



                        {{-- BADGES DE MODO: EN TURNO vs FUERA DE TURNO --}}

                        @if(($dashboard['modo'] ?? '') === 'EN_TURNO')

                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#63775B]/20 text-[#63775B] dark:bg-[#63775B]/30 dark:text-[#8DA280] font-black uppercase tracking-wider text-[9.5px] border border-[#63775B]/30">

                                <i class="ph-bold ph-check-circle text-xs"></i>

                                <span>MI TURNO / ACTIVO</span>

                            </span>

                        @else

                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#C98A17]/20 text-[#C98A17] dark:bg-[#C98A17]/30 dark:text-[#F3B740] font-black uppercase tracking-wider text-[9.5px] border border-[#C98A17]/30">

                                <i class="ph-bold ph-clock text-xs"></i>

                                <span>FUERA DE TURNO</span>

                            </span>

                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#304060]/12 text-[#304060] dark:bg-[#304060]/30 dark:text-[#A6B2C8] font-black uppercase tracking-wider text-[9px] border border-[#304060]/20">

                                <i class="ph-bold ph-eye text-xs"></i>

                                <span>MODO CONSULTA / SOLO LECTURA</span>

                            </span>

                        @endif

                    </div>



                    <h1 class="mt-1 text-lg sm:text-[20px] font-black text-[#304060] dark:text-[#F0E8DE] tracking-tight leading-tight">

                        Buenos días, <span class="font-extrabold text-[#243B6B] dark:text-white">{{ $dashboard['usuario']['nombres'] ?? (Auth::user()->nombres ?? 'Elena') }}</span>

                    </h1>



                    {{-- LEMA MOTIVACIONAL CLÍNICO --}}

                    <p class="mt-0.5 text-xs font-semibold text-[#63775B] dark:text-[#9BB391] italic flex items-center gap-1">

                        <span class="text-[#C98A17] font-black text-sm not-italic">“</span>Tu labor hace la diferencia<span class="text-[#C98A17] font-black text-sm not-italic">”</span>

                    </p>

                </div>



                {{-- PIE OPERATIVO COMPACTO --}}

                <div class="pt-2 border-t border-[#D5CABE]/40 dark:border-[#383C3D]/60 flex items-center gap-3 text-[11px] font-semibold text-[#677084] dark:text-[#A6B2C8]">

                    <div class="flex items-center gap-1 text-[#304060] dark:text-[#A6B2C8] font-bold">

                        <i class="ph-bold ph-clock text-xs"></i>

                        <span>{{ $dashboard['jornada']['hora_inicio'] ?? '07:00' }} - {{ $dashboard['jornada']['hora_fin'] ?? '15:00' }}</span>

                    </div>

                    <span class="text-[#D5CABE] dark:text-[#383C3D]">|</span>

                    <div class="flex items-center gap-1 text-[#63775B] dark:text-[#8DA280] font-bold">

                        <i class="ph-bold ph-users text-xs"></i>

                        <span>{{ count($dashboard['residentes'] ?? []) }} residentes {{ ($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'cubiertos' : 'asignados' }}</span>

                    </div>

                </div>

            </div>



            {{-- LADO DERECHO: FOTOGRAFÍA INSTITUCIONAL REAL DE MANOS (36%) --}}

            <div class="w-full sm:w-[36%] relative min-h-[120px] sm:min-h-full overflow-hidden shrink-0 border-t sm:border-t-0 sm:border-l border-[#D5CABE]/50 dark:border-[#383C3D] bg-[#E0D5C9]">

                <img src="{{ asset('storage/imagenes/ENFERMERIA/manos.png') }}"

                     alt="Atención y cuidado humano en RememberMind"

                     class="w-full h-full object-cover object-center transform group-hover:scale-105 transition-transform duration-500 ease-out"

                     loading="eager"

                     onerror="this.onerror=null; this.src='{{ asset('images/dashboard/manos.png') }}';">



                {{-- Transición sutil en el borde izquierdo para fundir armónicamente --}}

                <div class="absolute inset-y-0 left-0 w-6 bg-gradient-to-r from-[#F0E8DE] to-transparent dark:from-[#222527] pointer-events-none hidden sm:block"></div>

            </div>

        </div>



        {{-- PANEL ESTADO GENERAL (4 COLS) --}}

        <div class="lg:col-span-4 rounded-[16px] border border-[#D5CABE] dark:border-[#383C3D] bg-[#F0E8DE] dark:bg-[#222527] shadow-[0_3px_12px_rgba(47,62,92,0.06)] overflow-hidden flex flex-col justify-between min-h-[142px] lg:h-[148px] transition-all duration-200 ease-out p-3.5 sm:p-4">

            @php

                $alertaCritica = $dashboard['alerta_critica'] ?? null;

            @endphp



            @if($alertaCritica)

                {{-- CASO CON ALERTA CRÍTICA: ROJO CLÍNICO #D62828 --}}

                <div>

                    {{-- Encabezado con Icono, Título y Badge Crítica --}}

                    <div class="flex items-center justify-between border-b border-[#D62828]/25 dark:border-[#D62828]/40 pb-2">

                        <div class="flex items-center gap-1.5">

                            <span class="relative flex h-6 w-6 items-center justify-center rounded bg-[#D62828] text-white shadow-2xs shrink-0">

                                <i class="ph-bold ph-warning-octagon text-sm"></i>

                            </span>

                            <div>

                                <h2 class="text-[11px] font-black uppercase tracking-wider text-[#D62828] dark:text-[#FF7A7A] leading-tight">

                                    ALERTA CRÍTICA

                                </h2>

                                <p class="text-[9.5px] font-semibold text-[#D62828]/80 dark:text-[#FF7A7A]/80 leading-tight">

                                    Requiere atención inmediata

                                </p>

                            </div>

                        </div>

                        <span class="rounded bg-[#D62828] px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-white shadow-2xs shrink-0">

                            CRÍTICA

                        </span>

                    </div>



                    {{-- Residente y Motivo --}}

                    <div class="mt-2 rounded-lg border border-[#D62828]/25 bg-[#FBF8F5]/60 dark:bg-[#2B2A27]/60 p-2 text-center">

                        <p class="text-xs font-bold text-[#D62828] dark:text-[#FF7A7A] truncate">

                            {{ $alertaCritica['residente_nombre'] ?? ($alertaCritica['residente'] ?? 'Residente asignado') }}

                        </p>

                        <p class="text-[10.5px] font-semibold text-[#677084] dark:text-[#C4BCB3] truncate">

                            {{ $alertaCritica['titulo'] ?? 'Atención prioritaria requerida' }}

                        </p>

                    </div>

                </div>



                {{-- Pie con Acción y Tiempo --}}

                <div class="pt-1.5 border-t border-[#D62828]/20 flex items-center justify-between text-[11px] text-[#D62828] dark:text-[#FF7A7A] font-bold">

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

                    <div class="flex items-center justify-between border-b border-[#8DA280]/35 dark:border-[#49453F] pb-2">

                        <div class="flex items-center gap-1.5">

                            <span class="flex h-6 w-6 items-center justify-center rounded bg-[#8DA280]/25 dark:bg-[#9BB391]/25 text-[#63775B] dark:text-[#9BB391] border border-[#8DA280]/40 shadow-2xs shrink-0">

                                <i class="ph-bold ph-shield-check text-sm"></i>

                            </span>

                            <div>

                                <h2 class="text-[11px] font-black uppercase tracking-wider text-[#304060] dark:text-[#F2EBE3]">

                                    ESTADO GENERAL

                                </h2>

                                <p class="text-[9.5px] font-semibold text-[#63775B] dark:text-[#9BB391]">

                                    Sector asignado

                                </p>

                            </div>

                        </div>

                        <span class="rounded bg-[#63775B] px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-white shadow-2xs shrink-0">

                            ESTABLE

                        </span>

                    </div>



                    {{-- Mensaje clínico central --}}

                    <div class="mt-2 rounded-lg border border-[#D5CABE]/40 dark:border-[#49453F] bg-[#FBF8F5]/70 dark:bg-[#201E1C]/60 p-2 text-center">

                        <p class="text-xs font-bold text-[#304060] dark:text-[#F2EBE3]">

                            Sin alertas activas en tu turno

                        </p>

                        <p class="mt-0.5 text-[10px] font-medium text-[#677084] dark:text-[#C4BCB3]">

                            Todos los residentes asignados se encuentran con signos dentro de rango.

                        </p>

                    </div>

                </div>



                {{-- Línea inferior con "Monitoreo al día" y hora --}}

                <div class="pt-1.5 border-t border-[#8DA280]/30 dark:border-[#49453F] flex items-center justify-between text-[11px] text-[#63775B] dark:text-[#9BB391] font-semibold">

                    <span class="inline-flex items-center gap-1">

                        <i class="ph-bold ph-check-circle text-xs text-[#63775B]"></i>

                        <span>Monitoreo al día</span>

                    </span>

                    <span class="font-bold text-[#304060] dark:text-[#F0E8DE]">{{ now()->format('H:i') }} hrs</span>

                </div>

            @endif

        </div>



    </div>



    {{-- ========================================================= --}}

    {{-- 2. FRANJA COMPACTA DE KPIS (CÁPSULAS CON COLORES MÁS OSCUROS) --}}

    {{-- ========================================================= --}}

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-2.5 sm:gap-3">



        {{-- 1. TOTAL REGISTRO (#304060 - Azul profundo) --}}

        <a href="{{ Route::has('admin.enfermeria.alertas') ? route('admin.enfermeria.alertas') : '#' }}"

           class="group relative overflow-hidden rounded-[14px] p-3 sm:p-3.5 transition-all duration-200 ease-out hover:-translate-y-0.5 bg-[#F0E8DE] dark:bg-[#222527] border border-[#D5CABE] hover:border-[#304060] dark:border-[#383C3D] dark:hover:border-[#304060] shadow-[0_2px_8px_rgba(47,62,92,0.06)] hover:shadow-[0_6px_16px_rgba(47,62,92,0.12)] border-l-[4px] border-l-[#304060] flex flex-col justify-between h-[88px] sm:h-[92px]"

           aria-label="Ver todas las alertas registradas en el sistema">

            <div class="flex items-center justify-between gap-1.5">

                <span class="text-[11px] font-black tracking-wider uppercase text-[#304060] dark:text-[#A6B2C8]">

                    TOTAL REGISTRO

                </span>

                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#304060]/12 dark:bg-[#304060]/25 text-[#304060] dark:text-[#A6B2C8] transition-transform duration-200 group-hover:scale-105">

                    <i class="ph-bold ph-bell-simple text-base"></i>

                </span>

            </div>

            <div class="flex items-baseline justify-between gap-2 z-10">

                <div class="text-[26px] sm:text-[28px] font-black leading-none tracking-tight text-[#304060] dark:text-white">

                    {{ $dashboard['kpis']['total_registro']['numero'] ?? 4 }}

                </div>

                <div class="text-[11px] font-medium text-[#677084] dark:text-[#A6B2C8] truncate">

                    {{ $dashboard['kpis']['total_registro']['texto'] ?? 'Alertas en sistema' }}

                </div>

            </div>

            <div class="absolute bottom-1 right-2 pointer-events-none opacity-40 group-hover:opacity-80 transition-opacity">

                <svg width="34" height="16" viewBox="0 0 34 16" fill="none" class="text-[#304060] dark:text-[#A6B2C8]">

                    <path d="M2 14C12 14 18 10 24 5C28 2 31 2 33 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />

                </svg>

            </div>

        </a>



        {{-- 2. CRÍTICAS Y ALTAS (#D62828 - Rojo clínico oscuro) --}}

        <a href="{{ Route::has('admin.enfermeria.alertas') ? route('admin.enfermeria.alertas') : '#' }}"

           class="group relative overflow-hidden rounded-[14px] p-3 sm:p-3.5 transition-all duration-200 ease-out hover:-translate-y-0.5 bg-[#F0E8DE] dark:bg-[#222527] border border-[#D5CABE] hover:border-[#D62828] dark:border-[#383C3D] dark:hover:border-[#D62828] shadow-[0_2px_8px_rgba(214,40,40,0.06)] hover:shadow-[0_6px_16px_rgba(214,40,40,0.12)] border-l-[4px] border-l-[#D62828] flex flex-col justify-between h-[88px] sm:h-[92px]"

           aria-label="Ver alertas críticas y altas">

            <div class="flex items-center justify-between gap-1.5">

                <span class="text-[11px] font-black tracking-wider uppercase text-[#D62828] dark:text-[#FF7A7A]">

                    CRÍTICAS Y ALTAS

                </span>

                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#D62828]/12 dark:bg-[#D62828]/25 text-[#D62828] dark:text-[#FF7A7A] transition-transform duration-200 group-hover:scale-105">

                    <i class="ph-bold ph-warning-octagon text-base"></i>

                </span>

            </div>

            <div class="flex items-baseline justify-between gap-2 z-10">

                <div class="text-[26px] sm:text-[28px] font-black leading-none tracking-tight text-[#D62828] dark:text-[#FF7A7A]">

                    {{ $dashboard['kpis']['criticas_altas']['numero'] ?? 0 }}

                </div>

                <div class="text-[11px] font-medium text-[#677084] dark:text-[#A6B2C8] truncate">

                    {{ $dashboard['kpis']['criticas_altas']['texto'] ?? '0 críticas · 0 altas' }}

                </div>

            </div>

            <div class="absolute bottom-1 right-2 pointer-events-none opacity-40 group-hover:opacity-80 transition-opacity">

                <svg width="34" height="16" viewBox="0 0 34 16" fill="none" class="text-[#D62828] dark:text-[#FF7A7A]">

                    <path d="M2 14C12 14 18 10 24 5C28 2 31 2 33 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />

                </svg>

            </div>

        </a>



        {{-- 3. POR ATENDER (#C98A17 - Ámbar marcado) --}}

        <a href="{{ Route::has('admin.enfermeria.alertas') ? route('admin.enfermeria.alertas') : '#' }}"

           class="group relative overflow-hidden rounded-[14px] p-3 sm:p-3.5 transition-all duration-200 ease-out hover:-translate-y-0.5 bg-[#F0E8DE] dark:bg-[#222527] border border-[#D5CABE] hover:border-[#C98A17] dark:border-[#383C3D] dark:hover:border-[#C98A17] shadow-[0_2px_8px_rgba(201,138,23,0.06)] hover:shadow-[0_6px_16px_rgba(201,138,23,0.12)] border-l-[4px] border-l-[#C98A17] flex flex-col justify-between h-[88px] sm:h-[92px]"

           aria-label="Ver alertas abiertas por atender">

            <div class="flex items-center justify-between gap-1.5">

                <span class="text-[11px] font-black tracking-wider uppercase text-[#C98A17] dark:text-[#F3B740]">

                    POR ATENDER

                </span>

                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#C98A17]/12 dark:bg-[#C98A17]/25 text-[#C98A17] dark:text-[#F3B740] transition-transform duration-200 group-hover:scale-105">

                    <i class="ph-bold ph-clock text-base"></i>

                </span>

            </div>

            <div class="flex items-baseline justify-between gap-2 z-10">

                <div class="text-[26px] sm:text-[28px] font-black leading-none tracking-tight text-[#C98A17] dark:text-[#F3B740]">

                    {{ $dashboard['kpis']['por_atender']['numero'] ?? 4 }}

                </div>

                <div class="text-[11px] font-medium text-[#677084] dark:text-[#A6B2C8] truncate">

                    {{ $dashboard['kpis']['por_atender']['texto'] ?? 'Abiertas sin atención' }}

                </div>

            </div>

            <div class="absolute bottom-1 right-2 pointer-events-none opacity-40 group-hover:opacity-80 transition-opacity">

                <svg width="34" height="16" viewBox="0 0 34 16" fill="none" class="text-[#C98A17] dark:text-[#F3B740]">

                    <path d="M2 14C12 14 18 10 24 5C28 2 31 2 33 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />

                </svg>

            </div>

        </a>



        {{-- 4. EN ATENCIÓN (#243B6B - Azul intenso) --}}

        <a href="{{ Route::has('admin.enfermeria.alertas') ? route('admin.enfermeria.alertas') : '#' }}"

           class="group relative overflow-hidden rounded-[14px] p-3 sm:p-3.5 transition-all duration-200 ease-out hover:-translate-y-0.5 bg-[#F0E8DE] dark:bg-[#222527] border border-[#D5CABE] hover:border-[#243B6B] dark:border-[#383C3D] dark:hover:border-[#243B6B] shadow-[0_2px_8px_rgba(36,59,107,0.06)] hover:shadow-[0_6px_16px_rgba(36,59,107,0.12)] border-l-[4px] border-l-[#243B6B] flex flex-col justify-between h-[88px] sm:h-[92px]"

           aria-label="Ver alertas en curso de atención">

            <div class="flex items-center justify-between gap-1.5">

                <span class="text-[11px] font-black tracking-wider uppercase text-[#243B6B] dark:text-[#8BB4F8]">

                    EN ATENCIÓN

                </span>

                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#243B6B]/12 dark:bg-[#243B6B]/25 text-[#243B6B] dark:text-[#8BB4F8] transition-transform duration-200 group-hover:scale-105">

                    <i class="ph-bold ph-first-aid text-base"></i>

                </span>

            </div>

            <div class="flex items-baseline justify-between gap-2 z-10">

                <div class="text-[26px] sm:text-[28px] font-black leading-none tracking-tight text-[#243B6B] dark:text-[#8BB4F8]">

                    {{ $dashboard['kpis']['en_atencion']['numero'] ?? 0 }}

                </div>

                <div class="text-[11px] font-medium text-[#677084] dark:text-[#A6B2C8] truncate">

                    {{ $dashboard['kpis']['en_atencion']['texto'] ?? 'Protocolo en curso' }}

                </div>

            </div>

            <div class="absolute bottom-1 right-2 pointer-events-none opacity-40 group-hover:opacity-80 transition-opacity">

                <svg width="34" height="16" viewBox="0 0 34 16" fill="none" class="text-[#243B6B] dark:text-[#8BB4F8]">

                    <path d="M2 14C12 14 18 10 24 5C28 2 31 2 33 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />

                </svg>

            </div>

        </a>



        {{-- 5. RESUELTAS (#63775B - Verde salvia oscuro) --}}

        <a href="{{ Route::has('admin.enfermeria.alertas') ? route('admin.enfermeria.alertas') : '#' }}"

           class="group relative overflow-hidden rounded-[14px] p-3 sm:p-3.5 transition-all duration-200 ease-out hover:-translate-y-0.5 bg-[#F0E8DE] dark:bg-[#222527] border border-[#D5CABE] hover:border-[#63775B] dark:border-[#383C3D] dark:hover:border-[#63775B] shadow-[0_2px_8px_rgba(99,119,91,0.06)] hover:shadow-[0_6px_16px_rgba(99,119,91,0.12)] border-l-[4px] border-l-[#63775B] flex flex-col justify-between h-[88px] sm:h-[92px]"

           aria-label="Ver alertas resueltas e historial">

            <div class="flex items-center justify-between gap-1.5">

                <span class="text-[11px] font-black tracking-wider uppercase text-[#63775B] dark:text-[#8DA280]">

                    RESUELTAS

                </span>

                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#63775B]/12 dark:bg-[#63775B]/25 text-[#63775B] dark:text-[#8DA280] transition-transform duration-200 group-hover:scale-105">

                    <i class="ph-bold ph-check-circle text-base"></i>

                </span>

            </div>

            <div class="flex items-baseline justify-between gap-2 z-10">

                <div class="text-[26px] sm:text-[28px] font-black leading-none tracking-tight text-[#63775B] dark:text-[#8DA280]">

                    {{ $dashboard['kpis']['resueltas']['numero'] ?? 0 }}

                </div>

                <div class="text-[11px] font-medium text-[#677084] dark:text-[#A6B2C8] truncate">

                    {{ $dashboard['kpis']['resueltas']['texto'] ?? 'Historial conservado' }}

                </div>

            </div>

            <div class="absolute bottom-1 right-2 pointer-events-none opacity-40 group-hover:opacity-80 transition-opacity">

                <svg width="34" height="16" viewBox="0 0 34 16" fill="none" class="text-[#63775B] dark:text-[#8DA280]">

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

        <section class="lg:col-span-7 rounded-[16px] border border-[#D5CABE] hover:border-[#304060]/60 dark:border-[#383C3D] dark:hover:border-[#52585A] bg-[#F0E8DE] dark:bg-[#222527] p-3.5 sm:p-4 shadow-[0_3px_12px_rgba(47,62,92,0.06)] hover:shadow-[0_8px_20px_rgba(47,62,92,0.12)] transition-all duration-200 ease-out" aria-label="Agenda operativa de hoy">

            {{-- Cabecera con Ícono Contextual y Enlace a Agenda Completa --}}

            <div class="flex items-center justify-between gap-2 border-b border-[#D5CABE]/40 dark:border-[#383C3D] pb-2.5">

                <div class="flex items-center gap-2">

                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#304060]/12 text-[#304060] dark:bg-[#304060]/30 dark:text-[#A6B2C8] shrink-0">

                        <i class="ph-bold ph-calendar-check text-base"></i>

                    </span>

                    <div>

                        <h2 class="text-sm sm:text-base font-black text-[#304060] dark:text-[#F0E8DE] tracking-tight leading-tight">

                            {{ ($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Actividad del turno en curso' : 'Agenda de hoy' }}

                        </h2>

                        <p class="text-[11px] font-semibold text-[#677084] dark:text-[#A6B2C8]">

                            {{ ($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Intervenciones programadas del turno activo (Solo lectura)' : 'Intervenciones programadas en tu guardia' }}

                        </p>

                    </div>

                </div>

                <a href="{{ Route::has('admin.enfermeria.agenda') ? route('admin.enfermeria.agenda') : '#' }}"

                   class="group/link text-[11px] font-extrabold text-[#304060] dark:text-[#A6B2C8] hover:text-[#C98A17] dark:hover:text-[#F3B740] flex items-center gap-1 transition-colors duration-200">

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

                            $badgeClase = 'bg-[#63775B]/15 text-[#63775B] dark:text-[#8DA280] border border-[#63775B]/30';

                            $iconoEstado = 'ph-check-circle text-[#63775B]';

                            $bordeLeft = 'border-l-[3.5px] border-l-[#63775B]';

                        } elseif (str_contains($estado, 'retras') || str_contains($estado, 'crit')) {

                            $badgeClase = 'bg-[#D62828]/15 text-[#D62828] dark:text-[#FF7A7A] border border-[#D62828]/30';

                            $iconoEstado = 'ph-warning-circle text-[#D62828]';

                            $bordeLeft = 'border-l-[3.5px] border-l-[#D62828]';

                        } else {

                            $badgeClase = 'bg-[#C98A17]/15 text-[#C98A17] dark:text-[#F3B740] border border-[#C98A17]/30';

                            $iconoEstado = 'ph-clock text-[#C98A17]';

                            $bordeLeft = 'border-l-[3.5px] border-l-[#C98A17]';

                        }

                    @endphp

                    <div class="group/item flex items-center justify-between gap-2.5 rounded-xl border border-[#D5CABE]/40 dark:border-[#383C3D] bg-[#FBF8F5]/70 dark:bg-[#2A2D2E]/60 p-2.5 hover:bg-[#FBF8F5] dark:hover:bg-[#2A2D2E] hover:shadow-xs transition-all duration-150 {{ $bordeLeft }}">

                        <div class="flex items-center gap-2.5 min-w-0">

                            {{-- Hora en fuente monoespaciada tabular --}}

                            <span class="font-mono text-xs font-black text-[#304060] dark:text-[#A6B2C8] shrink-0 bg-[#304060]/10 dark:bg-[#304060]/25 px-1.5 py-0.5 rounded">

                                {{ $evento['hora'] ?? '08:00' }}

                            </span>



                            {{-- Ícono por Tipo de Evento --}}

                            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-[#F0E8DE] dark:bg-[#222527] border border-[#D5CABE]/40 dark:border-[#383C3D] shrink-0">

                                @if(str_contains(strtolower($evento['tipo'] ?? ''), 'medic'))

                                    <i class="ph-bold ph-pill text-xs text-[#243B6B]"></i>

                                @elseif(str_contains(strtolower($evento['tipo'] ?? ''), 'signo') || str_contains(strtolower($evento['tipo'] ?? ''), 'vital'))

                                    <i class="ph-bold ph-heartbeat text-xs text-[#D62828]"></i>

                                @else

                                    <i class="ph-bold ph-stethoscope text-xs text-[#63775B]"></i>

                                @endif

                            </span>



                            {{-- Detalle del Evento --}}

                            <div class="min-w-0">

                                <div class="text-xs font-bold text-[#304060] dark:text-[#F0E8DE] truncate">

                                    {{ $evento['accion'] ?? ($evento['tarea'] ?? ($evento['titulo'] ?? 'Control de enfermería')) }}

                                </div>

                                <div class="text-[11px] font-semibold text-[#677084] dark:text-[#A6B2C8] truncate">

                                    {{ $evento['residente'] ?? ($evento['residente_nombre'] ?? 'Residente') }}

                                    @if(!empty($evento['ubicacion']))

                                        <span class="text-[#967B66] dark:text-[#C4BCB3]">· {{ $evento['ubicacion'] }}</span>

                                    @elseif(!empty($evento['habitacion']))

                                        <span class="text-[#967B66] dark:text-[#C4BCB3]">· {{ $evento['habitacion'] }}</span>

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

                    <div class="py-8 text-center text-xs font-semibold text-[#677084] dark:text-[#A6B2C8]">

                        No hay intervenciones programadas para el resto de este turno.

                    </div>

                @endforelse

            </div>

        </section>



        {{-- PROGRESO DEL TURNO Y DISTRIBUCIÓN (5 COLS): GRÁFICAS INTEGRADAS Y CONTRASTADAS --}}

        <section class="lg:col-span-5 rounded-[16px] border border-[#D5CABE] hover:border-[#304060]/60 dark:border-[#383C3D] dark:hover:border-[#52585A] bg-[#F0E8DE] dark:bg-[#222527] p-3.5 sm:p-4 shadow-[0_3px_12px_rgba(47,62,92,0.06)] hover:shadow-[0_8px_20px_rgba(47,62,92,0.12)] space-y-3 transition-all duration-200 ease-out" aria-label="Progreso del turno">

            {{-- Título con Ícono Contextual --}}

            <div class="flex items-center justify-between border-b border-[#D5CABE]/40 dark:border-[#383C3D] pb-2">

                <div class="flex items-center gap-2">

                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#63775B]/15 text-[#63775B] dark:text-[#8DA280] shrink-0">

                        <i class="ph-bold ph-chart-donut text-base"></i>

                    </span>

                    <div>

                        <h2 class="text-sm sm:text-base font-black text-[#304060] dark:text-[#F0E8DE] tracking-tight leading-tight">

                            {{ ($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Progreso del turno en curso' : 'Progreso del turno' }}

                        </h2>

                        <p class="text-[11px] font-semibold text-[#677084] dark:text-[#A6B2C8]">

                            {{ ($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Cumplimiento del turno activo' : 'Cumplimiento de tareas operativas' }}

                        </p>

                    </div>

                </div>

                @if(($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO')

                    <span class="rounded bg-[#304060]/15 dark:bg-[#304060]/30 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-[#304060] dark:text-[#A6B2C8]">

                        Solo lectura

                    </span>

                @endif

            </div>



            {{-- Bloque Donut: Grosor Óptimo y Colores Vivos --}}

            <div class="rounded-xl border border-[#D5CABE]/40 dark:border-[#383C3D] bg-[#FBF8F5]/70 dark:bg-[#2A2D2E]/60 p-3 shadow-2xs">

                @php

                    $pctCumplimiento = $dashboard['estado_tareas']['porcentaje'] ?? ($dashboard['progreso']['cumplimiento'] ?? 0);
                    $totalTareasProg = (int)($dashboard['estado_tareas']['total'] ?? ($dashboard['progreso']['total'] ?? 0));

                @endphp

                <div class="flex items-center justify-between text-xs font-extrabold text-[#304060] dark:text-[#F0E8DE] mb-1">

                    <span>Cumplimiento Global</span>

                    <span class="text-[#63775B] dark:text-[#8DA280] font-black text-sm">{{ $pctCumplimiento }}%</span>

                </div>



                <div class="h-36 sm:h-40 relative w-full flex items-center justify-center">

                    <canvas id="graficoCumplimientoTurno"></canvas>

                    {{-- Centro Sólido y Limpio del Donut --}}

                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">

                        <span class="text-xl font-black text-[#304060] dark:text-[#F0E8DE] leading-none">{{ $pctCumplimiento }}%</span>

                        <span class="text-[9px] font-black uppercase tracking-wider text-[#677084] dark:text-[#A6B2C8] mt-0.5">{{ ($totalTareasProg ?? 0) > 0 ? 'Completado' : 'Sin tareas' }}</span>

                    </div>

                </div>



                {{-- Leyenda y Totales de Alta Jerarquía --}}

                <div class="mt-2 flex items-center justify-around text-[11px] font-bold text-[#677084] dark:text-[#A6B2C8] border-t border-[#D5CABE]/30 dark:border-[#383C3D] pt-1.5">

                    <span class="flex items-center gap-1.5">

                        <span class="h-2.5 w-2.5 rounded-full bg-[#63775B]"></span>

                        <span>Realizadas</span>

                    </span>

                    <span class="flex items-center gap-1.5">

                        <span class="h-2.5 w-2.5 rounded-full bg-[#C98A17]"></span>

                        <span>Pendientes</span>

                    </span>

                    <span class="flex items-center gap-1.5">

                        <span class="h-2.5 w-2.5 rounded-full bg-[#D62828]"></span>

                        <span>Con retraso</span>

                    </span>

                </div>

            </div>



            {{-- Bloque Distribución de Cuidados --}}

            <div class="rounded-xl border border-[#D5CABE]/40 dark:border-[#383C3D] bg-[#FBF8F5]/70 dark:bg-[#2A2D2E]/60 p-3 shadow-2xs">

                <div class="flex items-center justify-between text-xs font-extrabold text-[#304060] dark:text-[#F0E8DE] mb-1">

                    <span>Distribución de Cuidados</span>

                    <span class="text-[10px] text-[#677084] dark:text-[#A6B2C8] font-bold uppercase tracking-wider">{{ ($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Información del turno activo en modo consulta' : 'Activas' }}</span>

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

        <section class="lg:col-span-8 rounded-[16px] border border-[#D5CABE] hover:border-[#304060]/60 dark:border-[#383C3D] dark:hover:border-[#52585A] bg-[#F0E8DE] dark:bg-[#222527] p-3.5 sm:p-4 shadow-[0_3px_12px_rgba(47,62,92,0.06)] hover:shadow-[0_8px_20px_rgba(47,62,92,0.12)] transition-all duration-200 ease-out flex flex-col justify-between" aria-label="Residentes asignados en mi turno">

            {{-- Cabecera con Ícono Contextual + Enlace Textual con Chevron --}}

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 border-b border-[#D5CABE]/40 dark:border-[#383C3D] pb-2.5">

                <div class="flex items-center gap-2">

                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#304060]/12 text-[#304060] dark:bg-[#304060]/30 dark:text-[#A6B2C8] shrink-0">

                        <i class="ph-bold ph-users-three text-base"></i>

                    </span>

                    <div>

                        <h2 class="text-sm sm:text-base font-black text-[#304060] dark:text-[#F0E8DE] tracking-tight leading-tight">

                            {{ ($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Residentes del turno en curso' : 'Residentes de mi turno' }}

                        </h2>

                        <p class="text-[11px] font-semibold text-[#677084] dark:text-[#A6B2C8]">

                            {{ ($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Personas actualmente cubiertas por el equipo de guardia' : 'Personas asignadas a tu cuidado directo' }}

                        </p>

                    </div>

                </div>

                <a href="{{ Route::has('admin.enfermeria.residentes') ? route('admin.enfermeria.residentes') : (Route::has('admin.enfermeria.pacientes') ? route('admin.enfermeria.pacientes') : '#') }}"

                   class="group/link text-[11px] font-extrabold text-[#304060] dark:text-[#A6B2C8] hover:text-[#C98A17] dark:hover:text-[#F3B740] flex items-center gap-1 transition-colors duration-200 self-start sm:self-auto">

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

                       class="group/res block rounded-xl border border-[#D5CABE]/40 dark:border-[#383C3D] bg-[#FBF8F5]/70 dark:bg-[#2A2D2E]/60 p-2.5 hover:bg-[#FBF8F5] dark:hover:bg-[#2A2D2E] hover:border-[#304060]/60 dark:hover:border-[#A6B2C8]/60 hover:shadow-xs transition-all duration-150">

                        <div class="flex items-center justify-between gap-3">

                            <div class="flex items-center gap-3 min-w-0">

                                {{-- Avatar o Iniciales --}}

                                <div class="relative shrink-0">

                                    @if(!empty($residente['foto']))

                                        <img src="{{ asset($residente['foto']) }}"

                                             alt="{{ $residente['nombre_completo'] ?? ($residente['nombre'] ?? 'Residente') }}"

                                             class="h-10 w-10 rounded-full object-cover border border-[#D5CABE] dark:border-[#52585A]">

                                    @else

                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#304060]/12 dark:bg-[#304060]/30 text-[#304060] dark:text-[#A6B2C8] font-black text-xs border border-[#304060]/25">

                                            {{ $residente['iniciales'] ?? strtoupper(substr($residente['nombre_completo'] ?? ($residente['nombre'] ?? 'R'), 0, 2)) }}

                                        </div>

                                    @endif

                                </div>



                                {{-- Información Clínica: Nombre + Edad/Ubicación + Chips --}}

                                <div class="min-w-0">

                                    <div class="text-xs sm:text-[13px] font-bold text-[#304060] dark:text-[#F0E8DE] truncate group-hover/res:text-[#C98A17] dark:group-hover/res:text-[#F3B740] transition-colors">

                                        {{ $residente['nombre_completo'] ?? ($residente['nombre'] ?? 'Residente') }}

                                    </div>

                                    <div class="text-[11px] font-medium text-[#677084] dark:text-[#A6B2C8] truncate">

                                        {{ $residente['edad'] ?? '80 años' }} ·

                                        <span class="font-bold text-[#304060] dark:text-[#E6DDD3]">

                                            {{ $residente['ubicacion'] ?? ($residente['cama_texto'] ?? 'Hab. 102 · Cama A') }}

                                        </span>

                                    </div>



                                    {{-- Chips Clínicos: Estado / Supervisión / Movilidad / Alertas --}}

                                    <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[10px]">

                                        <span class="px-1.5 py-0.2 rounded font-extrabold uppercase bg-[#63775B]/15 text-[#63775B] dark:text-[#8DA280]">
                                        {{-- Estado de seguimiento operativo vs institucional --}}
                                        @php
                                            $estSeg = $residente['estado_seguimiento'] ?? ($residente['estado_label'] ?? 'ESTABLE');
                                            $segColor = match(strtoupper(trim((string)$estSeg))) {
                                                'CRÍTICO', 'CRITICO', 'REQUIERE_ATENCION' => 'bg-[#D62828]/15 text-[#D62828] dark:text-[#FF7A7A]',
                                                'VIGILANCIA' => 'bg-[#C98A17]/15 text-[#C98A17] dark:text-[#F3B740]',
                                                default => 'bg-[#63775B]/15 text-[#63775B] dark:text-[#8DA280]',
                                            };
                                        @endphp
                                        <span class="px-1.5 py-0.5 rounded font-extrabold uppercase {{ $segColor }}" title="Estado de seguimiento">
                                            {{ $estSeg }}
                                        </span>

                                        <span class="px-1.5 py-0.5 rounded font-bold bg-[#F0E8DE] dark:bg-[#222527] border border-[#D5CABE]/50 dark:border-[#383C3D] text-[#677084] dark:text-[#A6B2C8]" title="Nivel de supervisión">
                                            {{ $residente['supervision_label'] ?? 'Supervisión moderada' }}
                                        </span>

                                        @if(!empty($residente['estado_institucional'] ?? $residente['estado_operacional']))
                                            <span class="px-1.5 py-0.5 rounded font-bold bg-[#304060]/10 dark:bg-[#304060]/25 text-[#304060] dark:text-[#A6B2C8] uppercase" title="Estado institucional">
                                                Institucional: {{ $residente['estado_institucional'] ?? $residente['estado_operacional'] }}
                                            </span>
                                        @endif

                                        @if(!empty($residente['turno_actual']))

                                            <span class="px-1.5 py-0.2 rounded font-bold bg-[#C98A17]/15 text-[#C98A17] dark:text-[#F3B740]">

                                                {{ $residente['turno_actual'] }}

                                            </span>

                                        @endif

                                        @if(!empty($residente['responsable_texto']))

                                            <span class="px-1.5 py-0.5 rounded font-bold bg-[#304060]/12 dark:bg-[#304060]/35 text-[#304060] dark:text-[#A6B2C8] inline-flex items-center gap-1">

                                                <i class="ph-bold ph-user-check text-[11px] text-[#63775B]"></i>

                                                <span>{{ $residente['responsable_texto'] }}</span>

                                            </span>

                                        @endif

                                        <span class="px-1.5 py-0.2 rounded font-bold bg-[#F0E8DE] dark:bg-[#222527] border border-[#D5CABE]/50 dark:border-[#383C3D] text-[#677084] dark:text-[#A6B2C8]">

                                            {{ $residente['movilidad_label'] ?? 'Movilidad asistida' }}

                                        </span>



                                        @if($alertCnt > 0)

                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded font-black text-[#D62828] bg-[#D62828]/12 dark:bg-[#D62828]/25">

                                                <i class="ph-bold ph-bell-ringing text-[10px]"></i>

                                                <span>{{ $alertCnt }} {{ $alertCnt === 1 ? 'alerta' : 'alertas' }}</span>

                                            </span>

                                        @else

                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded font-semibold text-[#63775B] bg-[#63775B]/12 dark:bg-[#63775B]/25">

                                                <i class="ph-bold ph-check text-[10px]"></i>

                                                <span>Sin alertas</span>

                                            </span>

                                        @endif

                                    </div>

                                </div>

                            </div>



                            {{-- Chevron de Navegación --}}

                            <div class="hidden sm:flex items-center justify-center pl-2">

                                <i class="ph-bold ph-caret-right text-sm text-[#967B66] dark:text-[#C4BCB3] opacity-50 group-hover/res:opacity-100 group-hover/res:translate-x-0.5 transition-all duration-150"></i>

                            </div>

                        </div>

                    </a>

                @empty

                    <div class="py-6 text-center text-xs font-semibold text-[#677084] dark:text-[#A6B2C8]">

                        No tienes residentes asignados directamente en este sector.

                    </div>

                @endforelse

            </div>

        </section>



        {{-- ALERTAS RECIENTES (4 COLS): STREAM DE NOTIFICACIONES CLÍNICAS --}}

        <section class="lg:col-span-4 rounded-[16px] border border-[#D5CABE] hover:border-[#304060]/60 dark:border-[#383C3D] dark:hover:border-[#52585A] bg-[#F0E8DE] dark:bg-[#222527] p-3.5 sm:p-4 shadow-[0_3px_12px_rgba(47,62,92,0.06)] hover:shadow-[0_8px_20px_rgba(47,62,92,0.12)] transition-all duration-200 ease-out flex flex-col justify-between" aria-label="Alertas recientes del turno">

            {{-- Cabecera con Ícono Contextual + Enlace Textual con Chevron --}}

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 border-b border-[#D5CABE]/40 dark:border-[#383C3D] pb-2.5">

                <div class="flex items-center gap-2">

                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#D62828]/12 text-[#D62828] dark:bg-[#D62828]/25 dark:text-[#FF7A7A] shrink-0">

                        <i class="ph-bold ph-bell-ringing text-base"></i>

                    </span>

                    <div>

                        <h2 class="text-sm sm:text-base font-black text-[#304060] dark:text-[#F0E8DE] tracking-tight leading-tight">

                            Alertas recientes

                        </h2>

                        <p class="text-[11px] font-semibold text-[#677084] dark:text-[#A6B2C8]">

                            Notificaciones clínicas de atención

                        </p>

                    </div>

                </div>

                <a href="{{ Route::has('admin.enfermeria.alertas') ? route('admin.enfermeria.alertas') : '#' }}"

                   class="group/link text-[11px] font-extrabold text-[#D62828] dark:text-[#FF7A7A] hover:underline flex items-center gap-1 transition-colors duration-200 self-start sm:self-auto">

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

                            $bordeLeft = 'border-l-[3.5px] border-l-[#D62828] bg-[#FBF8F5]/70 hover:bg-[#FFF5F5] dark:bg-[#2A2D2E]/60 dark:hover:bg-[rgba(214,40,40,0.15)]';

                            $badgeStyle = 'bg-[#D62828] text-white';

                            $iconoAlerta = 'ph-warning-octagon text-[#D62828]';

                        } elseif ($prioridad === 'MEDIA' || $prioridad === 'MEDIO') {

                            $bordeLeft = 'border-l-[3.5px] border-l-[#C98A17] bg-[#FBF8F5]/70 hover:bg-[#FDF9F2] dark:bg-[#2A2D2E]/60 dark:hover:bg-[#382C22]/60';

                            $badgeStyle = 'bg-[#C98A17] text-white';

                            $iconoAlerta = 'ph-warning text-[#C98A17]';

                        } else {

                            $bordeLeft = 'border-l-[3.5px] border-l-[#304060] bg-[#FBF8F5]/70 hover:bg-[#EEF1FA] dark:bg-[#2A2D2E]/60 dark:hover:bg-[#252A38]/60';

                            $badgeStyle = 'bg-[#304060] text-white';

                            $iconoAlerta = 'ph-info text-[#304060]';

                        }

                    @endphp

                    <a href="{{ Route::has('admin.enfermeria.alertas') ? route('admin.enfermeria.alertas') : '#' }}"

                       class="group/alerta block rounded-xl border border-[#D5CABE]/40 dark:border-[#383C3D] p-2.5 hover:-translate-y-0.5 transition-all duration-150 {{ $bordeLeft }}">

                        <div class="flex items-start justify-between gap-1.5">

                            <div class="flex items-center gap-1.5 min-w-0">

                                <i class="ph-bold {{ $iconoAlerta }} text-xs shrink-0"></i>

                                <h3 class="text-xs font-bold text-[#304060] dark:text-[#F0E8DE] truncate">

                                    {{ $alerta['titulo'] ?? ($alerta['motivo'] ?? 'Notificación') }}

                                </h3>

                            </div>

                            <span class="rounded px-1.5 py-0.2 text-[8.5px] font-black uppercase tracking-wider shrink-0 {{ $badgeStyle }}">

                                {{ $prioridad }}

                            </span>

                        </div>



                        <div class="mt-1 flex items-center justify-between text-[10.5px] text-[#677084] dark:text-[#A6B2C8]">

                            <span class="truncate font-semibold">{{ $alerta['residente'] ?? ($alerta['residente_nombre'] ?? '') }}</span>

                            <div class="flex items-center gap-1 shrink-0 font-bold">

                                <span>{{ $alerta['tiempo_relativo'] ?? ($alerta['tiempo'] ?? 'Hace 10m') }}</span>

                                <i class="ph-bold ph-caret-right text-[9px] text-[#967B66]/60 opacity-0 group-hover/alerta:opacity-100 group-hover/alerta:translate-x-0.5 transition-all duration-150"></i>

                            </div>

                        </div>

                    </a>

                @empty

                    <div class="py-6 text-center text-xs font-semibold text-[#677084] dark:text-[#A6B2C8]">

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

                text: isDark ? '#E6DDD3' : '#304060',

                subtext: isDark ? '#A6B2C8' : '#677084',

                grid: isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(47, 62, 92, 0.06)',

                border: isDark ? '#222527' : '#F0E8DE',

                completadas: '#63775B', // Verde salvia oscuro

                pendientes: '#C98A17',  // Ámbar

                retrasadas: '#D62828',  // Rojo clínico

                medicacion: '#304060',  // Azul profundo principal

                signos: '#243B6B',      // Azul intenso

                higiene: '#63775B',     // Verde salvia

                movilizacion: '#C98A17' // Ámbar

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

                                    backgroundColor: isDark ? '#1A1C1D' : '#FFFFFF',

                                    titleColor: colors.text,

                                    bodyColor: colors.text,

                                    borderColor: isDark ? '#383C3D' : '#D5CABE',

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

                                    backgroundColor: isDark ? '#1A1C1D' : '#FFFFFF',

                                    titleColor: colors.text,

                                    bodyColor: colors.text,

                                    borderColor: isDark ? '#383C3D' : '#D5CABE',

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