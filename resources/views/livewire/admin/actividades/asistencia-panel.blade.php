@php
    /**
     * Deriva el resultado institucional de asistencia a partir del campo estado.
     * No existe campo asistio / hora_llegada. La asistencia se infiere del estado.
     */
    $resultado = function (string $estado): array {
        return match (strtoupper(trim($estado))) {
            'REALIZADA', 'COMPLETADA', 'FINALIZADA' => [
                'texto' => 'Asistió / cumplida',
                'clase' => 'border-[#8DA280]/30 bg-[#8DA280]/14 text-[#63775B]',
                'icon'  => 'ph-check-circle',
            ],
            'PROGRAMADA', 'PENDIENTE' => [
                'texto' => 'Pendiente',
                'clase' => 'border-[#D9A05B]/30 bg-[#D9A05B]/12 text-[#9A6B2E]',
                'icon'  => 'ph-clock',
            ],
            'CANCELADA', 'ANULADA' => [
                'texto' => 'No realizada',
                'clase' => 'border-[#E27D60]/25 bg-[#E27D60]/10 text-[#E27D60]',
                'icon'  => 'ph-x-circle',
            ],
            'REPROGRAMADA' => [
                'texto' => 'Reprogramada',
                'clase' => 'border-[#7A68B0]/30 bg-[#7A68B0]/10 text-[#5A4E8A]',
                'icon'  => 'ph-arrows-clockwise',
            ],
            default => [
                'texto' => 'Sin resultado',
                'clase' => 'border-[#C7B5A3]/40 bg-[#D5C7B9]/40 text-[#7C7168]',
                'icon'  => 'ph-minus',
            ],
        };
    };
@endphp

<div
    class="min-h-screen bg-[#F8F3ED]/45 px-4 py-5 text-[#2F3E5C] sm:px-6 lg:px-8"
    x-data
    @keydown.window.escape="$wire.cerrarModales()"
>
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
                            <i class="ph-bold ph-clipboard-text text-sm"></i>
                            CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS — Asistencia
                        </span>
                        <h1 class="mt-3 text-3xl font-black tracking-tight text-[#2F3E5C] sm:text-4xl">
                            Asistencia a actividades
                        </h1>
                        <p class="mt-1.5 max-w-2xl text-sm font-bold leading-relaxed text-[#2F3E5C]/70">
                            Control institucional del cumplimiento de actividades programadas para adultos mayores.
                        </p>
                        <p class="mt-1.5 inline-flex items-center gap-1.5 text-[11px] font-bold text-[#2F3E5C]/45">
                            <i class="ph-bold ph-info text-xs"></i>
                            La asistencia se consolida actualmente desde el estado de cada actividad registrada.
                        </p>
                    </div>

                    <div class="flex shrink-0 flex-wrap items-center gap-2 sm:flex-nowrap">
                        <button
                            type="button"
                            wire:click="$refresh"
                            class="inline-flex items-center gap-2 rounded-xl border border-[#C7B5A3]/55 bg-[#F8F3ED]/80 px-3.5 py-2 text-xs font-black text-[#2F3E5C]/70 transition hover:bg-[#E6DDD3] hover:text-[#2F3E5C]"
                        >
                            <i class="ph-bold ph-arrows-clockwise text-sm"></i>
                            Actualizar
                        </button>
                        <a
                            href="{{ route('admin.actividades.participacion') }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-[#D9A05B]/40 bg-[#D9A05B]/10 px-3.5 py-2 text-xs font-black text-[#9A6B2E] transition hover:bg-[#D9A05B]/20"
                        >
                            <i class="ph-bold ph-users text-sm"></i>
                            Ver participación
                        </a>
                        <a
                            href="{{ route('admin.actividades.reportes') }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-[#7A68B0]/35 bg-[#7A68B0]/10 px-3.5 py-2 text-xs font-black text-[#5A4E8A] transition hover:bg-[#7A68B0]/18"
                        >
                            <i class="ph-bold ph-chart-bar text-sm"></i>
                            Ver reportes
                        </a>
                    </div>

                </div>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- MÉTRICAS (8 cards)                                                 --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-4">

            {{-- Total --}}
            <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-[#2F3E5C]/18 bg-[#E6DDD3]/70 p-4 shadow-sm backdrop-blur-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#2F3E5C]/60">Total registros</p>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-[#2F3E5C]/10">
                        <i class="ph-bold ph-clipboard-text text-xs text-[#2F3E5C]"></i>
                    </span>
                </div>
                <p class="mt-2 text-3xl font-black tracking-tight text-[#2F3E5C]">{{ number_format($stats['total']) }}</p>
                <p class="mt-0.5 text-[10px] font-bold text-[#2F3E5C]/50">Actividades en el sistema</p>
            </div>

            {{-- Pendientes --}}
            <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-[#D9A05B]/28 bg-[#D9A05B]/8 p-4 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#9A6B2E]/80">Pendientes</p>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-[#D9A05B]/20">
                        <i class="ph-bold ph-clock text-xs text-[#9A6B2E]"></i>
                    </span>
                </div>
                <p class="mt-2 text-3xl font-black tracking-tight text-[#9A6B2E]">{{ number_format($stats['pendientes']) }}</p>
                <p class="mt-0.5 text-[10px] font-bold text-[#9A6B2E]/65">Programadas / sin resultado</p>
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

            {{-- Hoy --}}
            <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-[#D9A05B]/28 bg-[#D9A05B]/8 p-4 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#9A6B2E]/80">Actividades hoy</p>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-[#D9A05B]/20">
                        <i class="ph-bold ph-calendar-check text-xs text-[#9A6B2E]"></i>
                    </span>
                </div>
                <p class="mt-2 text-3xl font-black tracking-tight text-[#9A6B2E]">{{ number_format($stats['hoy']) }}</p>
                <p class="mt-0.5 text-[10px] font-bold text-[#9A6B2E]/65">Programadas para hoy</p>
            </div>

            {{-- Adultos con realizadas --}}
            <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-[#8DA280]/28 bg-[#8DA280]/10 p-4 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#63775B]/80">Adultos activos</p>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-[#8DA280]/20">
                        <i class="ph-bold ph-users text-xs text-[#63775B]"></i>
                    </span>
                </div>
                <p class="mt-2 text-3xl font-black tracking-tight text-[#63775B]">{{ number_format($stats['adultos_realizados']) }}</p>
                <p class="mt-0.5 text-[10px] font-bold text-[#63775B]/65">Con actividades realizadas</p>
            </div>

            {{-- Tipos con cumplimiento --}}
            <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-[#7A68B0]/22 bg-[#7A68B0]/8 p-4 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#5A4E8A]/80">Tipos cumplidos</p>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-[#7A68B0]/16">
                        <i class="ph-bold ph-star text-xs text-[#5A4E8A]"></i>
                    </span>
                </div>
                <p class="mt-2 text-3xl font-black tracking-tight text-[#5A4E8A]">{{ number_format($stats['tipos_cumplidos']) }}</p>
                <p class="mt-0.5 text-[10px] font-bold text-[#5A4E8A]/65">Tipos con al menos una realizada</p>
            </div>

        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TABLA + FILTROS                                                    --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <section class="overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/65 bg-[#E6DDD3]/65 shadow-md backdrop-blur-xl">

            {{-- Barra de filtros --}}
            <div class="border-b border-[#C7B5A3]/35 bg-[#D5C7B9]/40 px-5 py-4">
                <div class="flex flex-wrap items-end gap-3">

                    {{-- Buscar adulto --}}
                    <div class="min-w-0 flex-1 basis-48">
                        <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/60">Adulto mayor</label>
                        <div class="relative">
                            <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-[#2F3E5C]/35"></i>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="search"
                                placeholder="Buscar por nombre..."
                                class="w-full rounded-xl border border-[#C7B5A3]/55 bg-[#F8F3ED]/80 py-2 pl-8 pr-3 text-xs font-bold text-[#2F3E5C] placeholder-[#2F3E5C]/35 focus:border-[#D9A05B]/60 focus:outline-none focus:ring-2 focus:ring-[#D9A05B]/20"
                            >
                        </div>
                    </div>

                    {{-- Tipo --}}
                    <div class="basis-40">
                        <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/60">Tipo</label>
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
                        <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/60">Estado</label>
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
                        <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/60">Desde</label>
                        <input
                            type="date"
                            wire:model.live="filtroFechaDesde"
                            class="w-full rounded-xl border border-[#C7B5A3]/55 bg-[#F8F3ED]/80 py-2 px-3 text-xs font-bold text-[#2F3E5C] focus:border-[#D9A05B]/60 focus:outline-none focus:ring-2 focus:ring-[#D9A05B]/20"
                        >
                    </div>

                    {{-- Fecha hasta --}}
                    <div class="basis-36">
                        <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/60">Hasta</label>
                        <input
                            type="date"
                            wire:model.live="filtroFechaHasta"
                            class="w-full rounded-xl border border-[#C7B5A3]/55 bg-[#F8F3ED]/80 py-2 px-3 text-xs font-bold text-[#2F3E5C] focus:border-[#D9A05B]/60 focus:outline-none focus:ring-2 focus:ring-[#D9A05B]/20"
                        >
                    </div>

                    {{-- Limpiar --}}
                    <div class="shrink-0">
                        <button
                            type="button"
                            wire:click="limpiarFiltros"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-[#C7B5A3]/55 bg-[#F8F3ED]/80 px-3.5 py-2 text-xs font-black text-[#2F3E5C]/60 transition hover:bg-[#E6DDD3] hover:text-[#2F3E5C]"
                        >
                            <i class="ph-bold ph-x text-xs"></i>
                            Limpiar
                        </button>
                    </div>

                </div>
            </div>

            {{-- Tabla --}}
            <div class="w-full overflow-x-auto" wire:loading.class="opacity-50">
                <table class="min-w-[900px] w-full border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-[#C7B5A3]/35 bg-[#D5C7B9]/30">
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.12em] text-[#2F3E5C]/55">Adulto mayor</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.12em] text-[#2F3E5C]/55">Tipo de actividad</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.12em] text-[#2F3E5C]/55">Fecha</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.12em] text-[#2F3E5C]/55">Hora</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.12em] text-[#2F3E5C]/55">Estado</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.12em] text-[#2F3E5C]/55">Resultado</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.12em] text-[#2F3E5C]/55">Observación</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-[0.12em] text-[#2F3E5C]/55">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#C7B5A3]/22">

                        @if($registros->isEmpty())
                            <tr>
                                <td colspan="8" class="py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#D5C7B9]/50">
                                            <i class="ph-bold ph-clipboard-text text-2xl text-[#2F3E5C]/30"></i>
                                        </span>
                                        @if($search || $filtroTipo || $filtroEstado || $filtroFechaDesde || $filtroFechaHasta)
                                            <p class="text-sm font-black text-[#2F3E5C]/55">No se encontraron registros con los filtros seleccionados.</p>
                                            <button wire:click="limpiarFiltros" class="text-xs font-black text-[#D9A05B] hover:underline">Limpiar filtros</button>
                                        @else
                                            <p class="text-sm font-black text-[#2F3E5C]/55">No hay actividades registradas para controlar asistencia.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @else
                            @foreach($registros as $r)
                                @php
                                    $estadoNorm  = \App\Models\ActividadAdulto::normalizarEstado($r->estado);
                                    $res         = $resultado($r->estado);
                                    $estadoUpper = strtoupper($r->estado);
                                    $esRealizada = in_array($estadoUpper, ['REALIZADA', 'COMPLETADA', 'FINALIZADA']);
                                    $esCancelada = in_array($estadoUpper, ['CANCELADA', 'ANULADA']);
                                    $am          = optional($r->adultoMayor);
                                    $tipo        = optional($r->tipoActividad);
                                @endphp
                                <tr wire:key="row-{{ $r->cod_act_adul }}" class="bg-[#F8F3ED]/40 transition hover:bg-[#E6DDD3]/50">

                                    {{-- Adulto mayor --}}
                                    <td class="px-4 py-3">
                                        <p class="max-w-[160px] truncate text-xs font-black text-[#2F3E5C]">
                                            {{ $am->ap_paterno }} {{ $am->ap_materno }}, {{ $am->nombres }}
                                        </p>
                                        <p class="text-[10px] font-bold text-[#2F3E5C]/45">{{ $r->cod_am }}</p>
                                    </td>

                                    {{-- Tipo --}}
                                    <td class="px-4 py-3">
                                        <p class="max-w-[130px] truncate text-xs font-bold text-[#2F3E5C]/80">
                                            {{ $tipo->tipo ?? '—' }}
                                        </p>
                                    </td>

                                    {{-- Fecha --}}
                                    <td class="px-4 py-3 text-xs font-bold text-[#2F3E5C]/70">
                                        {{ $r->fecha?->format('d/m/Y') ?? '—' }}
                                    </td>

                                    {{-- Hora --}}
                                    <td class="px-4 py-3 text-xs font-bold text-[#2F3E5C]/70">
                                        {{ $r->hora ? substr($r->hora, 0, 5) : '—' }}
                                    </td>

                                    {{-- Estado --}}
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-black {{ $estadoNorm['clase'] }}">
                                            {{ $estadoNorm['etiqueta'] }}
                                        </span>
                                    </td>

                                    {{-- Resultado institucional --}}
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 text-[10px] font-black {{ $res['clase'] }}">
                                            <i class="ph-bold {{ $res['icon'] }} text-[10px]"></i>
                                            {{ $res['texto'] }}
                                        </span>
                                    </td>

                                    {{-- Observación --}}
                                    <td class="px-4 py-3">
                                        <p class="max-w-[140px] truncate text-[11px] font-bold text-[#2F3E5C]/55" title="{{ $r->obs }}">
                                            {{ $r->obs ? \Illuminate\Support\Str::limit($r->obs, 40) : '—' }}
                                        </p>
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1">

                                            {{-- Ver detalle --}}
                                            <button
                                                type="button"
                                                wire:click="abrirDetalle({{ $r->cod_act_adul }})"
                                                title="Ver detalle"
                                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-[#2F3E5C]/18 bg-[#2F3E5C]/6 text-[#2F3E5C]/60 transition hover:bg-[#2F3E5C]/14 hover:text-[#2F3E5C]"
                                            >
                                                <i class="ph-bold ph-eye text-xs"></i>
                                            </button>

                                            {{-- Marcar realizada (si no lo está ya) --}}
                                            @can('actividades.editar')
                                                @if(!$esRealizada && !$esCancelada)
                                                    <button
                                                        type="button"
                                                        title="Marcar como realizada"
                                                        x-data
                                                        @click="window.SwalAmandita && window.SwalAmandita.fire({
                                                            icon: 'question',
                                                            title: '¿Marcar actividad como realizada?',
                                                            text: 'Se registrará el cumplimiento institucional de esta actividad.',
                                                            showCancelButton: true,
                                                            confirmButtonText: 'Sí, realizada',
                                                            cancelButtonText: 'Cancelar',
                                                        }).then(r => r.isConfirmed && $wire.marcarRealizada({{ $r->cod_act_adul }}))"
                                                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-[#8DA280]/30 bg-[#8DA280]/12 text-[#63775B] transition hover:bg-[#8DA280]/25"
                                                    >
                                                        <i class="ph-bold ph-check text-xs"></i>
                                                    </button>
                                                @endif
                                            @endcan

                                            {{-- Marcar cancelada (si no lo está ya) --}}
                                            @can('actividades.anular')
                                                @if(!$esCancelada)
                                                    <button
                                                        type="button"
                                                        title="Marcar como cancelada"
                                                        x-data
                                                        @click="window.SwalAmandita && window.SwalAmandita.fire({
                                                            icon: 'warning',
                                                            title: '¿Cancelar actividad?',
                                                            text: 'La actividad quedará como no realizada, conservando el registro institucional.',
                                                            showCancelButton: true,
                                                            confirmButtonText: 'Sí, cancelar',
                                                            cancelButtonText: 'No',
                                                        }).then(r => r.isConfirmed && $wire.marcarCancelada({{ $r->cod_act_adul }}))"
                                                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-[#E27D60]/25 bg-[#E27D60]/8 text-[#E27D60] transition hover:bg-[#E27D60]/18"
                                                    >
                                                        <i class="ph-bold ph-x text-xs"></i>
                                                    </button>
                                                @endif
                                            @endcan

                                            {{-- Registrar resultado (incluye reprogramar) --}}
                                            @can('actividades.editar')
                                                <button
                                                    type="button"
                                                    wire:click="abrirResultado({{ $r->cod_act_adul }})"
                                                    title="Registrar resultado"
                                                    class="flex h-7 w-7 items-center justify-center rounded-lg border border-[#D9A05B]/35 bg-[#D9A05B]/10 text-[#9A6B2E] transition hover:bg-[#D9A05B]/22"
                                                >
                                                    <i class="ph-bold ph-pencil-simple text-xs"></i>
                                                </button>
                                            @endcan

                                        </div>
                                    </td>

                                </tr>
                            @endforeach
                        @endif

                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($registros->hasPages())
                <div class="border-t border-[#C7B5A3]/30 bg-[#D5C7B9]/30 px-5 py-3.5">
                    {{ $registros->links() }}
                </div>
            @endif

        </section>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- BLOQUE INFORMATIVO — ALCANCE DE ASISTENCIA                         --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div class="flex items-start gap-3 rounded-2xl border border-[#D9A05B]/30 bg-[#D9A05B]/6 p-4">
            <i class="ph-bold ph-info mt-0.5 shrink-0 text-base text-[#9A6B2E]"></i>
            <div class="min-w-0">
                <p class="text-[11px] font-black text-[#9A6B2E]">Alcance de asistencia — control institucional actual</p>
                <p class="mt-0.5 text-[11px] font-bold leading-relaxed text-[#2F3E5C]/55">
                    Actualmente el sistema registra el cumplimiento de actividades mediante el estado de la actividad.
                    Para un control más detallado de asistencia individual —asistió, no asistió, tarde o justificado—
                    se recomienda incorporar una tabla específica de asistencia de adultos mayores a actividades en una fase posterior.
                </p>
            </div>
        </div>

    </div>{{-- /max-w-7xl --}}


    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL DETALLE                                                            --}}
    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    @if($modalDetalle && $detalle)
        @php
            $dAm    = optional($detalle->adultoMayor);
            $dTipo  = optional($detalle->tipoActividad);
            $dNorm  = \App\Models\ActividadAdulto::normalizarEstado($detalle->estado);
            $dRes   = $resultado($detalle->estado);
            $dEdad  = $dAm->fecha_nac
                ? \Carbon\Carbon::parse($dAm->fecha_nac)->age . ' años'
                : '—';
        @endphp
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog" aria-modal="true"
            wire:click.self="cerrarModales"
        >
            <div class="absolute inset-0 bg-[#2F3E5C]/40 backdrop-blur-sm"></div>
            <div class="relative w-full max-w-2xl max-h-[85vh] overflow-y-auto overflow-x-hidden rounded-[1.45rem] border border-[#C7B5A3]/70 bg-[#F8F3ED] shadow-2xl">

                {{-- Gradiente superior --}}
                <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>

                {{-- Encabezado modal --}}
                <div class="flex items-start justify-between p-5 sm:p-6">
                    <div>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-[#2F3E5C]/18 bg-[#2F3E5C]/8 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/60">
                            <i class="ph-bold ph-clipboard-text text-xs"></i>
                            Detalle de actividad
                        </span>
                        <h2 class="mt-2 text-xl font-black text-[#2F3E5C]">
                            {{ $dAm->ap_paterno }} {{ $dAm->ap_materno }}
                            @if($dAm->nombres), {{ $dAm->nombres }}@endif
                        </h2>
                        <p class="text-xs font-bold text-[#2F3E5C]/50">
                            {{ $detalle->cod_am }} · {{ $dEdad }}
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="cerrarModales"
                        class="ml-4 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-[#C7B5A3]/55 bg-[#E6DDD3]/60 text-[#2F3E5C]/55 transition hover:bg-[#D5C7B9]"
                    >
                        <i class="ph-bold ph-x text-sm"></i>
                    </button>
                </div>

                <div class="space-y-4 px-5 pb-6 sm:px-6">

                    {{-- Tipo + Estado + Resultado --}}
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-xl border border-[#C7B5A3]/45 bg-[#E6DDD3]/50 p-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/50">Tipo de actividad</p>
                            <p class="mt-1 text-sm font-black text-[#2F3E5C]">{{ $dTipo->tipo ?? '—' }}</p>
                        </div>
                        <div class="rounded-xl border border-[#C7B5A3]/45 bg-[#E6DDD3]/50 p-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/50">Estado actual</p>
                            <p class="mt-1.5">
                                <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-black {{ $dNorm['clase'] }}">
                                    {{ $dNorm['etiqueta'] }}
                                </span>
                            </p>
                        </div>
                        <div class="rounded-xl border border-[#C7B5A3]/45 bg-[#E6DDD3]/50 p-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/50">Resultado institucional</p>
                            <p class="mt-1.5">
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 text-[10px] font-black {{ $dRes['clase'] }}">
                                    <i class="ph-bold {{ $dRes['icon'] }} text-[10px]"></i>
                                    {{ $dRes['texto'] }}
                                </span>
                            </p>
                        </div>
                    </div>

                    {{-- Fecha + Hora --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-[#C7B5A3]/45 bg-[#E6DDD3]/50 p-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/50">Fecha</p>
                            <p class="mt-1 text-sm font-black text-[#2F3E5C]">
                                {{ $detalle->fecha?->format('d/m/Y') ?? '—' }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-[#C7B5A3]/45 bg-[#E6DDD3]/50 p-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/50">Hora</p>
                            <p class="mt-1 text-sm font-black text-[#2F3E5C]">
                                {{ $detalle->hora ? substr($detalle->hora, 0, 5) : '—' }}
                            </p>
                        </div>
                    </div>

                    {{-- Observación --}}
                    <div class="rounded-xl border border-[#C7B5A3]/45 bg-[#E6DDD3]/50 p-3">
                        <p class="text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/50">Observación</p>
                        <p class="mt-1 text-sm font-bold leading-relaxed text-[#2F3E5C]/80">
                            {{ $detalle->obs ?: 'Sin observaciones registradas.' }}
                        </p>
                    </div>

                    {{-- Metadatos --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-[#C7B5A3]/40 bg-[#D5C7B9]/30 p-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/45">Fecha de registro</p>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/65">
                                {{ $detalle->created_at?->format('d/m/Y H:i') ?? '—' }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-[#C7B5A3]/40 bg-[#D5C7B9]/30 p-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.1em] text-[#2F3E5C]/45">Última actualización</p>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/65">
                                {{ $detalle->updated_at?->format('d/m/Y H:i') ?? '—' }}
                            </p>
                        </div>
                    </div>

                    {{-- Acciones del modal --}}
                    <div class="flex items-center justify-between border-t border-[#C7B5A3]/30 pt-4">
                        <button
                            type="button"
                            wire:click="cerrarModales"
                            class="rounded-xl border border-[#C7B5A3]/55 bg-[#E6DDD3]/70 px-4 py-2 text-xs font-black text-[#2F3E5C]/65 transition hover:bg-[#D5C7B9]"
                        >
                            Cerrar
                        </button>
                        @can('actividades.editar')
                            <button
                                type="button"
                                wire:click="abrirResultado({{ $detalle->cod_act_adul }})"
                                class="inline-flex items-center gap-2 rounded-xl border border-[#D9A05B]/45 bg-[#D9A05B]/15 px-4 py-2 text-xs font-black text-[#9A6B2E] transition hover:bg-[#D9A05B]/25"
                            >
                                <i class="ph-bold ph-pencil-simple text-xs"></i>
                                Registrar resultado
                            </button>
                        @endcan
                    </div>

                </div>
            </div>
        </div>
    @endif


    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL REGISTRAR RESULTADO                                                --}}
    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    @if($modalResultado)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog" aria-modal="true"
            wire:click.self="cerrarModales"
        >
            <div class="absolute inset-0 bg-[#2F3E5C]/40 backdrop-blur-sm"></div>
            <div class="relative w-full max-w-lg max-h-[85vh] overflow-y-auto overflow-x-hidden rounded-[1.45rem] border border-[#C7B5A3]/70 bg-[#F8F3ED] shadow-2xl">

                <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>

                <div class="flex items-start justify-between p-5 sm:p-6">
                    <div>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-[#D9A05B]/35 bg-[#D9A05B]/12 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-[0.15em] text-[#9A6B2E]">
                            <i class="ph-bold ph-pencil-simple text-xs"></i>
                            Registrar resultado
                        </span>
                        <h2 class="mt-2 text-xl font-black text-[#2F3E5C]">Registrar resultado de actividad</h2>
                        <p class="mt-0.5 text-xs font-bold text-[#2F3E5C]/50">
                            Actualice el estado y la observación del registro de actividad.
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="cerrarModales"
                        class="ml-4 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-[#C7B5A3]/55 bg-[#E6DDD3]/60 text-[#2F3E5C]/55 transition hover:bg-[#D5C7B9]"
                    >
                        <i class="ph-bold ph-x text-sm"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardarResultado" class="space-y-4 px-5 pb-6 sm:px-6">

                    {{-- Estado --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-black text-[#2F3E5C]">
                            Estado <span class="text-[#E27D60]">*</span>
                        </label>
                        <select
                            wire:model="estado"
                            @class([
                                'w-full rounded-xl border bg-[#F8F3ED]/80 px-3 py-2.5 text-sm font-bold text-[#2F3E5C]',
                                'focus:border-[#D9A05B]/60 focus:outline-none focus:ring-2 focus:ring-[#D9A05B]/20',
                                'border-[#E27D60]/60' => $errors->has('estado'),
                                'border-[#C7B5A3]/55' => !$errors->has('estado'),
                            ])
                        >
                            <option value="PROGRAMADA">Programada — pendiente de realizarse</option>
                            <option value="REALIZADA">Realizada — actividad cumplida</option>
                            <option value="CANCELADA">Cancelada — no se realizó</option>
                            <option value="REPROGRAMADA">Reprogramada — nueva fecha pendiente</option>
                        </select>
                        @error('estado')
                            <p class="mt-1 text-[11px] font-bold text-[#E27D60]">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Observación --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-black text-[#2F3E5C]">
                            Observación
                            <span class="ml-1 text-[10px] font-bold text-[#2F3E5C]/45">(opcional)</span>
                        </label>
                        <textarea
                            wire:model="obs"
                            rows="4"
                            placeholder="Notas adicionales sobre el resultado de la actividad..."
                            @class([
                                'w-full resize-none rounded-xl border bg-[#F8F3ED]/80 px-3 py-2.5 text-sm font-bold text-[#2F3E5C]',
                                'placeholder-[#2F3E5C]/35 focus:border-[#D9A05B]/60 focus:outline-none focus:ring-2 focus:ring-[#D9A05B]/20',
                                'border-[#E27D60]/60' => $errors->has('obs'),
                                'border-[#C7B5A3]/55' => !$errors->has('obs'),
                            ])
                        ></textarea>
                        @error('obs')
                            <p class="mt-1 text-[11px] font-bold text-[#E27D60]">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nota: sin campos inventados --}}
                    <p class="text-[10px] font-bold leading-relaxed text-[#2F3E5C]/40">
                        <i class="ph-bold ph-info mr-1"></i>
                        Solo se pueden modificar el estado y la observación. La asistencia individual detallada
                        (asistió, tarde, justificado) requiere una tabla específica futura.
                    </p>

                    {{-- Botones --}}
                    <div class="flex items-center justify-between border-t border-[#C7B5A3]/30 pt-4">
                        <button
                            type="button"
                            wire:click="cerrarModales"
                            class="rounded-xl border border-[#C7B5A3]/55 bg-[#E6DDD3]/70 px-5 py-2 text-xs font-black text-[#2F3E5C]/65 transition hover:bg-[#D5C7B9]"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl border border-[#D9A05B]/55 bg-[#D9A05B]/20 px-5 py-2 text-xs font-black text-[#9A6B2E] transition hover:bg-[#D9A05B]/35"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-70"
                        >
                            <i class="ph-bold ph-floppy-disk text-sm"></i>
                            <span wire:loading.remove wire:target="guardarResultado">Guardar resultado</span>
                            <span wire:loading wire:target="guardarResultado">Guardando...</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    @endif

</div>
