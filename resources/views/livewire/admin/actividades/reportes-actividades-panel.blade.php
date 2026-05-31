<div class="min-h-screen bg-[#F8F3ED]/45 px-4 py-5 text-[#2F3E5C] sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-5">

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- CABECERA                                                           --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <section class="overflow-hidden rounded-[1.65rem] border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 shadow-[0_20px_58px_rgba(47,62,92,0.13)] backdrop-blur-xl">
            <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
            <div class="p-5 sm:p-7">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                    <div class="min-w-0">
                        <span class="inline-flex items-center gap-2 rounded-full border border-[#E27D60]/25 bg-[#E27D60]/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-[#E27D60]">
                            <i class="ph-bold ph-chart-bar text-sm"></i>
                            CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS — Reportes
                        </span>
                        <h1 class="mt-3 text-3xl font-black tracking-tight text-[#2F3E5C] sm:text-4xl">
                            Reportes de actividades
                        </h1>
                        <p class="mt-1.5 max-w-2xl text-sm font-bold leading-relaxed text-[#2F3E5C]/70">
                            Generación de evidencia institucional sobre actividades, participación y cumplimiento.
                        </p>
                    </div>

                    <div class="flex shrink-0 flex-wrap items-center gap-2">

                        {{-- Vista previa --}}
                        <a
                            href="{{ $urlPreview }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-[#2F3E5C]/20 bg-[#2F3E5C]/8 px-3.5 py-2 text-xs font-black text-[#2F3E5C]/75 transition hover:bg-[#2F3E5C]/15 hover:text-[#2F3E5C]"
                        >
                            <i class="ph-bold ph-eye text-sm"></i>
                            Vista previa
                        </a>

                        {{-- PDF --}}
                        @can('reportes.exportar_pdf')
                            <a
                                href="{{ $urlPdf }}"
                                x-data
                                @click.prevent="
                                    @if($stats['total'] === 0)
                                        window.SwalAmandita && window.SwalAmandita.fire({
                                            icon: 'warning',
                                            title: 'Sin datos para exportar',
                                            text: 'No hay actividades con los filtros seleccionados.',
                                        });
                                    @else
                                        window.location.href = '{{ $urlPdf }}';
                                    @endif
                                "
                                class="inline-flex items-center gap-2 rounded-xl border border-[#E27D60]/35 bg-[#E27D60]/10 px-3.5 py-2 text-xs font-black text-[#E27D60] transition hover:bg-[#E27D60]/20"
                            >
                                <i class="ph-bold ph-file-pdf text-sm"></i>
                                Exportar PDF
                            </a>
                        @endcan

                        {{-- Excel --}}
                        @can('reportes.exportar_pdf')
                            <a
                                href="{{ $urlExcel }}"
                                x-data
                                @click.prevent="
                                    @if($stats['total'] === 0)
                                        window.SwalAmandita && window.SwalAmandita.fire({
                                            icon: 'warning',
                                            title: 'Sin datos para exportar',
                                            text: 'No hay actividades con los filtros seleccionados.',
                                        });
                                    @else
                                        window.location.href = '{{ $urlExcel }}';
                                    @endif
                                "
                                class="inline-flex items-center gap-2 rounded-xl border border-[#8DA280]/35 bg-[#8DA280]/10 px-3.5 py-2 text-xs font-black text-[#63775B] transition hover:bg-[#8DA280]/20"
                            >
                                <i class="ph-bold ph-file-xls text-sm"></i>
                                Exportar Excel
                            </a>
                        @endcan

                        {{-- Limpiar filtros --}}
                        @if($hasFiltros)
                            <button
                                type="button"
                                wire:click="limpiarFiltros"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-[#C7B5A3]/55 bg-[#F8F3ED]/80 px-3.5 py-2 text-xs font-black text-[#2F3E5C]/60 transition hover:bg-[#E6DDD3]"
                            >
                                <i class="ph-bold ph-x text-xs"></i>
                                Limpiar filtros
                            </button>
                        @endif

                    </div>
                </div>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- FILTROS                                                            --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <section class="overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/65 bg-[#E6DDD3]/65 shadow-sm backdrop-blur-xl">
            <div class="border-b border-[#C7B5A3]/35 bg-[#D5C7B9]/40 px-5 py-3">
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-funnel text-[#2F3E5C]/55 text-sm"></i>
                    <h2 class="text-xs font-black uppercase tracking-[0.15em] text-[#2F3E5C]">Filtros de reporte</h2>
                    @if($hasFiltros)
                        <span class="ml-1 rounded-full bg-[#D9A05B]/25 px-2 py-0.5 text-[9px] font-black text-[#9A6B2E]">Activos</span>
                    @endif
                </div>
            </div>
            <div class="p-4 sm:p-5">
                <div class="flex flex-wrap items-end gap-3">

                    {{-- Buscar adulto --}}
                    <div class="min-w-0 flex-1 basis-44">
                        <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/55">Buscar adulto mayor</label>
                        <div class="relative">
                            <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-[#2F3E5C]/35"></i>
                            <input
                                type="text"
                                wire:model.live.debounce.400ms="buscar"
                                placeholder="Nombre o apellido..."
                                class="w-full rounded-xl border border-[#C7B5A3]/55 bg-[#F8F3ED]/80 py-2 pl-8 pr-3 text-xs font-bold text-[#2F3E5C] placeholder-[#2F3E5C]/35 focus:border-[#D9A05B]/60 focus:outline-none focus:ring-2 focus:ring-[#D9A05B]/20"
                            >
                        </div>
                    </div>

                    {{-- Tipo --}}
                    <div class="basis-40">
                        <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/55">Tipo de actividad</label>
                        <select
                            wire:model.live="filtroTipo"
                            class="w-full rounded-xl border border-[#C7B5A3]/55 bg-[#F8F3ED]/80 py-2 px-3 text-xs font-bold text-[#2F3E5C] focus:border-[#D9A05B]/60 focus:outline-none focus:ring-2 focus:ring-[#D9A05B]/20"
                        >
                            <option value="">Todos los tipos</option>
                            @foreach($tipos as $t)
                                <option value="{{ $t->cod_tipo_act }}">{{ $t->tipo }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Estado --}}
                    <div class="basis-36">
                        <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/55">Estado</label>
                        <select
                            wire:model.live="filtroEstado"
                            class="w-full rounded-xl border border-[#C7B5A3]/55 bg-[#F8F3ED]/80 py-2 px-3 text-xs font-bold text-[#2F3E5C] focus:border-[#D9A05B]/60 focus:outline-none focus:ring-2 focus:ring-[#D9A05B]/20"
                        >
                            <option value="">Todos los estados</option>
                            <option value="PROGRAMADA">Programada / Pendiente</option>
                            <option value="REALIZADA">Realizada / Cumplida</option>
                            <option value="CANCELADA">Cancelada / Anulada</option>
                            <option value="REPROGRAMADA">Reprogramada</option>
                        </select>
                    </div>

                    {{-- Fecha desde --}}
                    <div class="basis-36">
                        <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/55">Fecha desde</label>
                        <input
                            type="date"
                            wire:model.live="fechaDesde"
                            class="w-full rounded-xl border border-[#C7B5A3]/55 bg-[#F8F3ED]/80 py-2 px-3 text-xs font-bold text-[#2F3E5C] focus:border-[#D9A05B]/60 focus:outline-none focus:ring-2 focus:ring-[#D9A05B]/20"
                        >
                    </div>

                    {{-- Fecha hasta --}}
                    <div class="basis-36">
                        <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/55">Fecha hasta</label>
                        <input
                            type="date"
                            wire:model.live="fechaHasta"
                            class="w-full rounded-xl border border-[#C7B5A3]/55 bg-[#F8F3ED]/80 py-2 px-3 text-xs font-bold text-[#2F3E5C] focus:border-[#D9A05B]/60 focus:outline-none focus:ring-2 focus:ring-[#D9A05B]/20"
                        >
                    </div>

                    {{-- Limpiar --}}
                    <div class="shrink-0">
                        <button
                            type="button"
                            wire:click="limpiarFiltros"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-[#C7B5A3]/55 bg-[#F8F3ED]/80 px-3.5 py-2 text-xs font-black text-[#2F3E5C]/60 transition hover:bg-[#E6DDD3]"
                        >
                            <i class="ph-bold ph-x text-xs"></i>
                            Limpiar
                        </button>
                    </div>

                </div>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- MÉTRICAS (8 cards)                                                 --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-4" wire:loading.class="opacity-60">

            {{-- Total --}}
            <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-[#2F3E5C]/18 bg-[#E6DDD3]/70 p-4 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#2F3E5C]/60">Total actividades</p>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-[#2F3E5C]/10">
                        <i class="ph-bold ph-calendar-blank text-xs text-[#2F3E5C]"></i>
                    </span>
                </div>
                <p class="mt-2 text-3xl font-black tracking-tight text-[#2F3E5C]">{{ number_format($stats['total']) }}</p>
                <p class="mt-0.5 text-[10px] font-bold text-[#2F3E5C]/50">Registradas en el sistema</p>
            </div>

            {{-- Programadas --}}
            <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-[#D9A05B]/28 bg-[#D9A05B]/8 p-4 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#9A6B2E]/80">Programadas</p>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-[#D9A05B]/20">
                        <i class="ph-bold ph-clock text-xs text-[#9A6B2E]"></i>
                    </span>
                </div>
                <p class="mt-2 text-3xl font-black tracking-tight text-[#9A6B2E]">{{ number_format($stats['programadas']) }}</p>
                <p class="mt-0.5 text-[10px] font-bold text-[#9A6B2E]/65">Pendientes de realizarse</p>
            </div>

            {{-- Realizadas --}}
            <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-[#8DA280]/28 bg-[#8DA280]/10 p-4 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#63775B]/80">Realizadas</p>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-[#8DA280]/20">
                        <i class="ph-bold ph-check-circle text-xs text-[#63775B]"></i>
                    </span>
                </div>
                <p class="mt-2 text-3xl font-black tracking-tight text-[#63775B]">{{ number_format($stats['realizadas']) }}</p>
                <p class="mt-0.5 text-[10px] font-bold text-[#63775B]/65">Completadas / cumplidas</p>
            </div>

            {{-- Canceladas --}}
            <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-[#E27D60]/22 bg-[#E27D60]/8 p-4 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#E27D60]/80">Canceladas</p>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-[#E27D60]/16">
                        <i class="ph-bold ph-x-circle text-xs text-[#E27D60]"></i>
                    </span>
                </div>
                <p class="mt-2 text-3xl font-black tracking-tight text-[#E27D60]">{{ number_format($stats['canceladas']) }}</p>
                <p class="mt-0.5 text-[10px] font-bold text-[#E27D60]/65">No realizadas / anuladas</p>
            </div>

            {{-- Reprogramadas --}}
            <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-[#7A68B0]/22 bg-[#7A68B0]/8 p-4 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#5A4E8A]/80">Reprogramadas</p>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-[#7A68B0]/16">
                        <i class="ph-bold ph-arrows-clockwise text-xs text-[#5A4E8A]"></i>
                    </span>
                </div>
                <p class="mt-2 text-3xl font-black tracking-tight text-[#5A4E8A]">{{ number_format($stats['reprogramadas']) }}</p>
                <p class="mt-0.5 text-[10px] font-bold text-[#5A4E8A]/65">Pendientes de nueva fecha</p>
            </div>

            {{-- Adultos --}}
            <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-[#8DA280]/28 bg-[#8DA280]/10 p-4 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#63775B]/80">Adultos vinculados</p>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-[#8DA280]/20">
                        <i class="ph-bold ph-users text-xs text-[#63775B]"></i>
                    </span>
                </div>
                <p class="mt-2 text-3xl font-black tracking-tight text-[#63775B]">{{ number_format($stats['adultos']) }}</p>
                <p class="mt-0.5 text-[10px] font-bold text-[#63775B]/65">Con actividades en el período</p>
            </div>

            {{-- Tipos --}}
            <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-[#2F3E5C]/18 bg-[#E6DDD3]/70 p-4 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#2F3E5C]/60">Tipos utilizados</p>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-[#2F3E5C]/10">
                        <i class="ph-bold ph-tag text-xs text-[#2F3E5C]"></i>
                    </span>
                </div>
                <p class="mt-2 text-3xl font-black tracking-tight text-[#2F3E5C]">{{ number_format($stats['tipos']) }}</p>
                <p class="mt-0.5 text-[10px] font-bold text-[#2F3E5C]/50">Tipos de actividad distintos</p>
            </div>

            {{-- Periodo --}}
            <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-[#D9A05B]/28 bg-[#D9A05B]/8 p-4 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#9A6B2E]/80">Período</p>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-[#D9A05B]/20">
                        <i class="ph-bold ph-calendar-dots text-xs text-[#9A6B2E]"></i>
                    </span>
                </div>
                <p class="mt-2 text-sm font-black leading-tight text-[#9A6B2E]">{{ $stats['periodo'] }}</p>
                <p class="mt-0.5 text-[10px] font-bold text-[#9A6B2E]/65">Rango de fechas seleccionado</p>
            </div>

        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- GRÁFICOS CSS — DISTRIBUCIÓN POR ESTADO + POR MES                  --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        @if($chartEstado['data'] || $chartMes['data'])
            <div class="grid gap-5 lg:grid-cols-2">

                {{-- Distribución por estado --}}
                @if(!empty($chartEstado['data']))
                    <section class="overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/65 bg-[#E6DDD3]/65 shadow-sm">
                        <div class="border-b border-[#C7B5A3]/35 bg-[#D5C7B9]/40 px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <i class="ph-bold ph-chart-donut text-[#2F3E5C]/55 text-sm"></i>
                                <h3 class="text-xs font-black uppercase tracking-[0.15em] text-[#2F3E5C]">Distribución por estado</h3>
                            </div>
                        </div>
                        <div class="space-y-3 p-5">
                            @foreach($chartEstado['data'] as $item)
                                @php
                                    $pct = $chartEstado['maximo'] > 0
                                        ? round($item['total'] / $chartEstado['maximo'] * 100)
                                        : 0;
                                @endphp
                                <div class="flex items-center gap-3">
                                    <span class="w-24 shrink-0 text-right text-[11px] font-bold text-[#2F3E5C]/70">{{ $item['label'] }}</span>
                                    <div class="min-w-0 flex-1 overflow-hidden rounded-full bg-[#D5C7B9]/40 h-2.5">
                                        <div
                                            class="h-full rounded-full transition-all duration-500"
                                            style="width: {{ $pct }}%; background-color: {{ $item['color'] }};"
                                        ></div>
                                    </div>
                                    <span class="w-8 shrink-0 text-right text-xs font-black text-[#2F3E5C]">{{ $item['total'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Actividades por mes --}}
                @if(!empty($chartMes['data']))
                    <section class="overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/65 bg-[#E6DDD3]/65 shadow-sm">
                        <div class="border-b border-[#C7B5A3]/35 bg-[#D5C7B9]/40 px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <i class="ph-bold ph-chart-bar text-[#2F3E5C]/55 text-sm"></i>
                                <h3 class="text-xs font-black uppercase tracking-[0.15em] text-[#2F3E5C]">
                                    Actividades por mes
                                    @if(!$fechaDesde && !$fechaHasta)
                                        <span class="ml-1 text-[#2F3E5C]/45">({{ $chartMes['anio'] }})</span>
                                    @endif
                                </h3>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="flex items-end gap-1.5 overflow-x-auto pb-2" style="min-height: 100px;">
                                @foreach($chartMes['data'] as $item)
                                    @php
                                        $height = $chartMes['maximo'] > 0
                                            ? max(8, round($item['total'] / $chartMes['maximo'] * 80))
                                            : 8;
                                    @endphp
                                    <div class="flex flex-1 min-w-[28px] flex-col items-center gap-1">
                                        <span class="text-[9px] font-black text-[#2F3E5C]/60">{{ $item['total'] }}</span>
                                        <div
                                            class="w-full rounded-t-md bg-[#7A68B0]/70 transition-all duration-500"
                                            style="height: {{ $height }}px;"
                                            title="{{ $item['label'] }}: {{ $item['total'] }}"
                                        ></div>
                                        <span class="text-[9px] font-bold text-[#2F3E5C]/55">{{ $item['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif

            </div>
        @endif

        {{-- Top tipos --}}
        @if(!empty($chartTipo['data']))
            <section class="overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/65 bg-[#E6DDD3]/65 shadow-sm">
                <div class="border-b border-[#C7B5A3]/35 bg-[#D5C7B9]/40 px-5 py-3.5">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-ranking text-[#2F3E5C]/55 text-sm"></i>
                        <h3 class="text-xs font-black uppercase tracking-[0.15em] text-[#2F3E5C]">Tipos más utilizados</h3>
                    </div>
                </div>
                <div class="space-y-2.5 p-5">
                    @foreach($chartTipo['data'] as $i => $item)
                        @php
                            $pct = $chartTipo['maximo'] > 0
                                ? round($item['total'] / $chartTipo['maximo'] * 100)
                                : 0;
                            $barColor = match($i % 5) {
                                0 => '#7A68B0', 1 => '#8DA280', 2 => '#D9A05B',
                                3 => '#E27D60', default => '#2F3E5C',
                            };
                        @endphp
                        <div class="flex items-center gap-3">
                            <span class="w-5 shrink-0 text-center text-[10px] font-black text-[#2F3E5C]/40">{{ $i + 1 }}</span>
                            <span class="w-40 min-w-0 shrink-0 truncate text-[11px] font-bold text-[#2F3E5C]/75">{{ $item['label'] }}</span>
                            <div class="min-w-0 flex-1 overflow-hidden rounded-full bg-[#D5C7B9]/40 h-2">
                                <div
                                    class="h-full rounded-full"
                                    style="width: {{ $pct }}%; background-color: {{ $barColor }};"
                                ></div>
                            </div>
                            <span class="w-8 shrink-0 text-right text-xs font-black text-[#2F3E5C]">{{ $item['total'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- VISTA PREVIA                                                       --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <section class="overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/65 bg-[#E6DDD3]/65 shadow-md">
            <div class="flex items-center justify-between border-b border-[#C7B5A3]/35 bg-[#D5C7B9]/40 px-5 py-3.5">
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-table text-[#2F3E5C]/55 text-sm"></i>
                    <h2 class="text-xs font-black uppercase tracking-[0.15em] text-[#2F3E5C]">Vista previa</h2>
                    <span class="ml-1 rounded-full bg-[#2F3E5C]/12 px-2 py-0.5 text-[9px] font-black text-[#2F3E5C]/55">Últimos 15 registros</span>
                </div>
                <a
                    href="{{ $urlPreview }}"
                    class="inline-flex items-center gap-1.5 text-[11px] font-black text-[#9A6B2E] hover:underline"
                >
                    <i class="ph-bold ph-arrow-square-out text-xs"></i>
                    Ver completo
                </a>
            </div>

            <div class="w-full overflow-x-auto" wire:loading.class="opacity-50">
                <table class="min-w-[700px] w-full border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-[#C7B5A3]/30 bg-[#D5C7B9]/25">
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/50">Adulto mayor</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/50">Tipo de actividad</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/50">Fecha</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/50">Hora</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/50">Estado</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/50">Observación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#C7B5A3]/20">
                        @forelse($preview as $r)
                            @php
                                $norm = \App\Models\ActividadAdulto::normalizarEstado($r->estado);
                                $am   = optional($r->adultoMayor);
                                $tipo = optional($r->tipoActividad);
                            @endphp
                            <tr class="bg-[#F8F3ED]/40 hover:bg-[#E6DDD3]/50 transition">
                                <td class="px-4 py-3">
                                    <p class="max-w-[160px] truncate text-xs font-black text-[#2F3E5C]">
                                        {{ $am->ap_paterno }} {{ $am->ap_materno }}, {{ $am->nombres }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="max-w-[130px] truncate text-xs font-bold text-[#2F3E5C]/75">{{ $tipo->tipo ?? '—' }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs font-bold text-[#2F3E5C]/70">
                                    {{ $r->fecha?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-xs font-bold text-[#2F3E5C]/70">
                                    {{ $r->hora ? substr($r->hora, 0, 5) : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-black {{ $norm['clase'] }}">
                                        {{ $norm['etiqueta'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="max-w-[150px] truncate text-[11px] font-bold text-[#2F3E5C]/55" title="{{ $r->obs }}">
                                        {{ $r->obs ? \Illuminate\Support\Str::limit($r->obs, 45) : '—' }}
                                    </p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#D5C7B9]/50">
                                            <i class="ph-bold ph-table text-xl text-[#2F3E5C]/30"></i>
                                        </span>
                                        @if($buscar || $filtroTipo || $filtroEstado || $fechaDesde || $fechaHasta)
                                            <p class="text-sm font-black text-[#2F3E5C]/55">No hay actividades para los filtros seleccionados.</p>
                                            <button wire:click="limpiarFiltros" class="text-xs font-black text-[#D9A05B] hover:underline">Limpiar filtros</button>
                                        @else
                                            <p class="text-sm font-black text-[#2F3E5C]/55">No hay actividades registradas.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- REPORTES DISPONIBLES                                               --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <section class="overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/65 bg-[#E6DDD3]/65 shadow-sm">
            <div class="border-b border-[#C7B5A3]/35 bg-[#D5C7B9]/40 px-5 py-3.5">
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-export text-[#E27D60] text-sm"></i>
                    <h2 class="text-xs font-black uppercase tracking-[0.15em] text-[#2F3E5C]">Reportes disponibles</h2>
                </div>
            </div>
            <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">

                {{-- A. Reporte general --}}
                <div class="flex flex-col gap-3 overflow-hidden rounded-2xl border border-[#2F3E5C]/18 bg-[#F8F3ED]/75 p-4 shadow-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#2F3E5C]/10">
                        <i class="ph-bold ph-file-text text-[#2F3E5C] text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-black text-[#2F3E5C]">Reporte general</p>
                        <p class="mt-0.5 text-[10px] font-bold leading-relaxed text-[#2F3E5C]/55">Resumen institucional por período, estado y tipo de actividad.</p>
                    </div>
                    <div class="mt-auto flex flex-wrap gap-1.5">
                        <a href="{{ $urlPreview }}" class="rounded-lg border border-[#C7B5A3]/55 px-2.5 py-1 text-[10px] font-black text-[#2F3E5C]/65 hover:bg-[#E6DDD3]">
                            <i class="ph-bold ph-eye mr-0.5"></i>Vista previa
                        </a>
                        @can('reportes.exportar_pdf')
                            <a href="{{ $urlPdf }}" class="rounded-lg border border-[#E27D60]/30 bg-[#E27D60]/8 px-2.5 py-1 text-[10px] font-black text-[#E27D60] hover:bg-[#E27D60]/15">
                                <i class="ph-bold ph-file-pdf mr-0.5"></i>PDF
                            </a>
                            <a href="{{ $urlExcel }}" class="rounded-lg border border-[#8DA280]/30 bg-[#8DA280]/8 px-2.5 py-1 text-[10px] font-black text-[#63775B] hover:bg-[#8DA280]/15">
                                <i class="ph-bold ph-file-xls mr-0.5"></i>Excel
                            </a>
                        @endcan
                    </div>
                </div>

                {{-- B. Participación --}}
                <div class="flex flex-col gap-3 overflow-hidden rounded-2xl border border-[#8DA280]/25 bg-[#F8F3ED]/75 p-4 shadow-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#8DA280]/14">
                        <i class="ph-bold ph-users text-[#63775B] text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-black text-[#2F3E5C]">Participación</p>
                        <p class="mt-0.5 text-[10px] font-bold leading-relaxed text-[#2F3E5C]/55">Adultos mayores vinculados a actividades registradas.</p>
                    </div>
                    <div class="mt-auto flex flex-wrap gap-1.5">
                        @php
                            $filtrosConRealizada = array_filter(array_merge(
                                collect($this->buildFiltros())->except(['estado'])->all(),
                                ['estado' => 'REALIZADA']
                            ));
                        @endphp
                        <a href="{{ route('admin.reportes.actividades.preview', $filtrosConRealizada) }}" class="rounded-lg border border-[#C7B5A3]/55 px-2.5 py-1 text-[10px] font-black text-[#2F3E5C]/65 hover:bg-[#E6DDD3]">
                            <i class="ph-bold ph-eye mr-0.5"></i>Vista previa
                        </a>
                        @can('reportes.exportar_pdf')
                            <a href="{{ route('admin.reportes.actividades.excel', $filtrosConRealizada) }}" class="rounded-lg border border-[#8DA280]/30 bg-[#8DA280]/8 px-2.5 py-1 text-[10px] font-black text-[#63775B] hover:bg-[#8DA280]/15">
                                <i class="ph-bold ph-file-xls mr-0.5"></i>Excel
                            </a>
                        @endcan
                    </div>
                </div>

                {{-- C. Por tipo --}}
                <div class="flex flex-col gap-3 overflow-hidden rounded-2xl border border-[#D9A05B]/25 bg-[#F8F3ED]/75 p-4 shadow-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#D9A05B]/14">
                        <i class="ph-bold ph-tag text-[#9A6B2E] text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-black text-[#2F3E5C]">Por tipo de actividad</p>
                        <p class="mt-0.5 text-[10px] font-bold leading-relaxed text-[#2F3E5C]/55">Distribución según clasificación institucional de actividades.</p>
                    </div>
                    <div class="mt-auto flex flex-wrap gap-1.5">
                        <a href="{{ route('admin.actividades.tipos') }}" class="rounded-lg border border-[#C7B5A3]/55 px-2.5 py-1 text-[10px] font-black text-[#2F3E5C]/65 hover:bg-[#E6DDD3]">
                            <i class="ph-bold ph-list-bullets mr-0.5"></i>Ver tipos
                        </a>
                        @can('reportes.exportar_pdf')
                            <a href="{{ $urlExcel }}" class="rounded-lg border border-[#D9A05B]/30 bg-[#D9A05B]/8 px-2.5 py-1 text-[10px] font-black text-[#9A6B2E] hover:bg-[#D9A05B]/15">
                                <i class="ph-bold ph-file-xls mr-0.5"></i>Excel
                            </a>
                        @endcan
                    </div>
                </div>

                {{-- D. Cumplimiento --}}
                <div class="flex flex-col gap-3 overflow-hidden rounded-2xl border border-[#7A68B0]/22 bg-[#F8F3ED]/75 p-4 shadow-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#7A68B0]/12">
                        <i class="ph-bold ph-chart-line text-[#5A4E8A] text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-black text-[#2F3E5C]">Cumplimiento</p>
                        <p class="mt-0.5 text-[10px] font-bold leading-relaxed text-[#2F3E5C]/55">Actividades realizadas, pendientes, canceladas o reprogramadas.</p>
                    </div>
                    <div class="mt-auto flex flex-wrap gap-1.5">
                        <a href="{{ route('admin.actividades.asistencia') }}" class="rounded-lg border border-[#C7B5A3]/55 px-2.5 py-1 text-[10px] font-black text-[#2F3E5C]/65 hover:bg-[#E6DDD3]">
                            <i class="ph-bold ph-clipboard-text mr-0.5"></i>Asistencia
                        </a>
                        @can('reportes.exportar_pdf')
                            <a href="{{ $urlPdf }}" class="rounded-lg border border-[#7A68B0]/30 bg-[#7A68B0]/8 px-2.5 py-1 text-[10px] font-black text-[#5A4E8A] hover:bg-[#7A68B0]/15">
                                <i class="ph-bold ph-file-pdf mr-0.5"></i>PDF
                            </a>
                        @endcan
                    </div>
                </div>

            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- BLOQUE INFORMATIVO                                                 --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div class="flex items-start gap-3 rounded-2xl border border-[#C7B5A3]/50 bg-[#F8F3ED]/70 p-4">
            <i class="ph-bold ph-info mt-0.5 shrink-0 text-base text-[#2F3E5C]/35"></i>
            <div class="min-w-0">
                <p class="text-[11px] font-black text-[#2F3E5C]/55">Acerca de estos reportes</p>
                <p class="mt-0.5 text-[11px] font-bold leading-relaxed text-[#2F3E5C]/45">
                    Los reportes incluyen datos de la tabla <span class="font-black">actividades_adulto</span>. Los filtros seleccionados
                    se conservan en los enlaces de exportación PDF y Excel. La exportación requiere el permiso
                    <span class="font-black">reportes.exportar_pdf</span>.
                    Los registros se muestran sin incluir actividades eliminadas (soft delete).
                </p>
            </div>
        </div>

    </div>
</div>
