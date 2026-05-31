@php
    $inputCls = 'w-full rounded-xl border border-[#C7B5A3]/55 bg-[#F8F3ED] px-3 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none ring-[#E27D60]/25 transition focus:border-[#E27D60]/50 focus:ring-2';
    $labelCls = 'block text-[10px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/55 mb-1.5';
    $errCls   = 'mt-1 text-[10px] font-bold text-[#E27D60]';

    $resolverColorIcono = function(string $tipo): array {
        $t = strtolower($tipo);
        $mapa = [
            'terapia'   => ['cls' => 'bg-[#5DADE2]/15 text-[#1A6A9A]', 'icon' => 'ph-hand-heart',          'linea' => 'bg-[#5DADE2]'],
            'estimulaci'=> ['cls' => 'bg-[#7A68B0]/12 text-[#5A4E8A]', 'icon' => 'ph-brain',               'linea' => 'bg-[#7A68B0]'],
            'gimnas'    => ['cls' => 'bg-[#8DA280]/15 text-[#63775B]', 'icon' => 'ph-person-simple-walk',   'linea' => 'bg-[#8DA280]'],
            'musico'    => ['cls' => 'bg-[#2F3E5C]/10 text-[#2F3E5C]', 'icon' => 'ph-music-notes',         'linea' => 'bg-[#2F3E5C]'],
            'ludot'     => ['cls' => 'bg-[#D9A05B]/15 text-[#9A6B2E]', 'icon' => 'ph-game-controller',     'linea' => 'bg-[#D9A05B]'],
            'integ'     => ['cls' => 'bg-[#E27D60]/12 text-[#C05A40]', 'icon' => 'ph-users-three',         'linea' => 'bg-[#E27D60]'],
            'recrea'    => ['cls' => 'bg-[#E27D60]/12 text-[#E27D60]', 'icon' => 'ph-smiley',              'linea' => 'bg-[#E27D60]'],
            'cultur'    => ['cls' => 'bg-[#D9A05B]/15 text-[#9A6B2E]', 'icon' => 'ph-palette',             'linea' => 'bg-[#D9A05B]'],
            'artis'     => ['cls' => 'bg-[#D9A05B]/15 text-[#9A6B2E]', 'icon' => 'ph-paint-brush',         'linea' => 'bg-[#D9A05B]'],
            'cogni'     => ['cls' => 'bg-[#7A68B0]/12 text-[#5A4E8A]', 'icon' => 'ph-lightbulb',           'linea' => 'bg-[#7A68B0]'],
            'social'    => ['cls' => 'bg-[#E27D60]/12 text-[#C05A40]', 'icon' => 'ph-users',               'linea' => 'bg-[#E27D60]'],
            'manual'    => ['cls' => 'bg-[#C7B5A3]/35 text-[#7C7168]', 'icon' => 'ph-scissors',            'linea' => 'bg-[#C7B5A3]'],
            'fis'       => ['cls' => 'bg-[#8DA280]/15 text-[#63775B]', 'icon' => 'ph-heartbeat',           'linea' => 'bg-[#8DA280]'],
            'danza'     => ['cls' => 'bg-[#E27D60]/12 text-[#E27D60]', 'icon' => 'ph-person-simple-run',   'linea' => 'bg-[#E27D60]'],
            'espiritu'  => ['cls' => 'bg-[#7A68B0]/12 text-[#5A4E8A]', 'icon' => 'ph-flower-lotus',        'linea' => 'bg-[#7A68B0]'],
        ];
        foreach ($mapa as $clave => $vals) {
            if (str_contains($t, $clave)) return $vals;
        }
        $paletas = [
            ['cls' => 'bg-[#E27D60]/12 text-[#E27D60]', 'icon' => 'ph-star',         'linea' => 'bg-[#E27D60]'],
            ['cls' => 'bg-[#8DA280]/15 text-[#63775B]', 'icon' => 'ph-leaf',         'linea' => 'bg-[#8DA280]'],
            ['cls' => 'bg-[#2F3E5C]/10 text-[#2F3E5C]', 'icon' => 'ph-circles-four','linea' => 'bg-[#2F3E5C]'],
            ['cls' => 'bg-[#D9A05B]/15 text-[#9A6B2E]', 'icon' => 'ph-sun',         'linea' => 'bg-[#D9A05B]'],
            ['cls' => 'bg-[#7A68B0]/12 text-[#5A4E8A]', 'icon' => 'ph-sparkle',     'linea' => 'bg-[#7A68B0]'],
            ['cls' => 'bg-[#C7B5A3]/35 text-[#7C7168]', 'icon' => 'ph-tag',         'linea' => 'bg-[#C7B5A3]'],
        ];
        return $paletas[abs(crc32($tipo)) % count($paletas)];
    };
@endphp

<div class="min-h-screen bg-[#F8F3ED]/45 px-4 py-5 text-[#2F3E5C] sm:px-6 lg:px-8"
     x-data
     @keydown.window.escape="$wire.cerrarModales()">
    <div class="mx-auto max-w-7xl space-y-6">

        {{-- ── CABECERA ─────────────────────────────────────────────────────── --}}
        <section class="overflow-hidden rounded-[1.65rem] border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 shadow-[0_20px_58px_rgba(47,62,92,0.13)] backdrop-blur-xl">
            <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
            <div class="flex flex-col gap-4 p-5 sm:p-7 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#E27D60]/25 bg-[#E27D60]/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-[#E27D60]">
                        <i class="ph-bold ph-tag text-sm"></i>
                        CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS — Catálogo
                    </span>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-[#2F3E5C] sm:text-4xl">
                        Tipos de actividades
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-[#2F3E5C]/70">
                        Catálogo institucional para clasificar actividades recreativas, cognitivas, físicas, sociales y culturales.
                    </p>
                </div>
                <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center">
                    <a href="{{ route('admin.actividades.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#C7B5A3]/55 bg-[#F8F3ED] px-4 py-2.5 text-xs font-black text-[#2F3E5C]/70 transition hover:border-[#2F3E5C]/30 hover:text-[#2F3E5C]">
                        <i class="ph-bold ph-arrow-left text-sm"></i>
                        Actividades
                    </a>
                    <button wire:click="$refresh"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#C7B5A3]/55 bg-[#F8F3ED] px-4 py-2.5 text-xs font-black text-[#2F3E5C]/70 transition hover:border-[#2F3E5C]/30 hover:text-[#2F3E5C]">
                        <i class="ph-bold ph-arrows-clockwise text-sm" wire:loading.class="animate-spin" wire:target="$refresh"></i>
                        Actualizar
                    </button>
                    @can('actividades.crear')
                    <button wire:click="abrirRegistrar"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#E27D60] px-4 py-2.5 text-xs font-black uppercase tracking-[0.12em] text-white shadow-sm transition hover:bg-[#D06B50] hover:shadow-md active:scale-95">
                        <i class="ph-bold ph-plus text-sm"></i>
                        Registrar tipo
                    </button>
                    @endcan
                </div>
            </div>
        </section>

        {{-- ── 6 MÉTRICAS ──────────────────────────────────────────────────── --}}
        <section class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
            {{-- Total --}}
            <article class="relative min-h-[105px] overflow-hidden rounded-2xl border border-[#C7B5A3]/55 bg-[#F3ECE4]/78 p-4 shadow-sm backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-[0_16px_34px_rgba(47,62,92,0.10)]">
                <div class="absolute inset-x-0 top-0 h-1 bg-[#2F3E5C]"></div>
                <div class="flex items-start justify-between gap-3">
                    <p class="text-[10px] font-black uppercase leading-snug tracking-[0.13em] text-[#2F3E5C]/55">Total en catálogo</p>
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#2F3E5C]/10 text-[#2F3E5C]">
                        <i class="ph-bold ph-tag text-lg"></i>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black leading-none text-[#2F3E5C]">{{ number_format($stats['total']) }}</p>
            </article>
            {{-- Con actividades --}}
            <article class="relative min-h-[105px] overflow-hidden rounded-2xl border border-[#C7B5A3]/55 bg-[#F3ECE4]/78 p-4 shadow-sm backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-[0_16px_34px_rgba(47,62,92,0.10)]">
                <div class="absolute inset-x-0 top-0 h-1 bg-[#8DA280]"></div>
                <div class="flex items-start justify-between gap-3">
                    <p class="text-[10px] font-black uppercase leading-snug tracking-[0.13em] text-[#2F3E5C]/55">Tipos con actividades</p>
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#8DA280]/18 text-[#63775B]">
                        <i class="ph-bold ph-check-circle text-lg"></i>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black leading-none text-[#63775B]">{{ number_format($stats['con_actividades']) }}</p>
            </article>
            {{-- Sin uso --}}
            <article class="relative min-h-[105px] overflow-hidden rounded-2xl border border-[#C7B5A3]/55 bg-[#F3ECE4]/78 p-4 shadow-sm backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-[0_16px_34px_rgba(47,62,92,0.10)]">
                <div class="absolute inset-x-0 top-0 h-1 bg-[#E27D60]"></div>
                <div class="flex items-start justify-between gap-3">
                    <p class="text-[10px] font-black uppercase leading-snug tracking-[0.13em] text-[#2F3E5C]/55">Sin actividades</p>
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#E27D60]/12 text-[#E27D60]">
                        <i class="ph-bold ph-minus-circle text-lg"></i>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black leading-none text-[#E27D60]">{{ number_format($stats['sin_actividades']) }}</p>
            </article>
            {{-- Total actividades clasificadas --}}
            <article class="relative min-h-[105px] overflow-hidden rounded-2xl border border-[#C7B5A3]/55 bg-[#F3ECE4]/78 p-4 shadow-sm backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-[0_16px_34px_rgba(47,62,92,0.10)]">
                <div class="absolute inset-x-0 top-0 h-1 bg-[#D9A05B]"></div>
                <div class="flex items-start justify-between gap-3">
                    <p class="text-[10px] font-black uppercase leading-snug tracking-[0.13em] text-[#2F3E5C]/55">Actividades clasificadas</p>
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#D9A05B]/16 text-[#9A6B2E]">
                        <i class="ph-bold ph-calendar-check text-lg"></i>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black leading-none text-[#9A6B2E]">{{ number_format($stats['total_actividades']) }}</p>
            </article>
            {{-- Más utilizado --}}
            <article class="relative min-h-[105px] overflow-hidden rounded-2xl border border-[#C7B5A3]/55 bg-[#F3ECE4]/78 p-4 shadow-sm backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-[0_16px_34px_rgba(47,62,92,0.10)]">
                <div class="absolute inset-x-0 top-0 h-1 bg-[#7A68B0]"></div>
                <div class="flex items-start justify-between gap-3">
                    <p class="text-[10px] font-black uppercase leading-snug tracking-[0.13em] text-[#2F3E5C]/55">Tipo más utilizado</p>
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#7A68B0]/12 text-[#5A4E8A]">
                        <i class="ph-bold ph-trophy text-lg"></i>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black leading-none text-[#5A4E8A]">{{ number_format($stats['mas_count']) }}</p>
                <p class="mt-1 truncate text-[10px] font-bold text-[#5A4E8A]/65">{{ $stats['mas_nombre'] }}</p>
            </article>
            {{-- Promedio --}}
            <article class="relative min-h-[105px] overflow-hidden rounded-2xl border border-[#C7B5A3]/55 bg-[#F3ECE4]/78 p-4 shadow-sm backdrop-blur-xl transition hover:-translate-y-0.5 hover:shadow-[0_16px_34px_rgba(47,62,92,0.10)]">
                <div class="absolute inset-x-0 top-0 h-1 bg-[#C7B5A3]"></div>
                <div class="flex items-start justify-between gap-3">
                    <p class="text-[10px] font-black uppercase leading-snug tracking-[0.13em] text-[#2F3E5C]/55">Promedio por tipo</p>
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#C7B5A3]/35 text-[#7C7168]">
                        <i class="ph-bold ph-chart-bar text-lg"></i>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black leading-none text-[#7C7168]">{{ $stats['promedio'] }}</p>
                <p class="mt-1 text-[10px] font-bold text-[#7C7168]/65">actividades / tipo</p>
            </article>
        </section>

        {{-- ── FILTROS ──────────────────────────────────────────────────────── --}}
        <section class="overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/65 bg-[#E6DDD3]/60 shadow-sm backdrop-blur-xl">
            <div class="border-b border-[#C7B5A3]/40 bg-[#D5C7B9]/40 px-5 py-3">
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-funnel text-[#2F3E5C]/60 text-base"></i>
                    <span class="text-[10px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]">Filtros</span>
                </div>
            </div>
            <div class="p-4">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-[220px] flex-1">
                        <label class="{{ $labelCls }}">Buscar tipo o descripción</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[#2F3E5C]/40">
                                <i class="ph-bold ph-magnifying-glass text-sm"></i>
                            </span>
                            <input wire:model.live.debounce.300ms="search"
                                   type="text" placeholder="Nombre del tipo..."
                                   class="{{ $inputCls }} pl-8" />
                        </div>
                    </div>
                    <div class="min-w-[180px]">
                        <label class="{{ $labelCls }}">Uso</label>
                        <select wire:model.live="filtroUso" class="{{ $inputCls }}">
                            <option value="">Todos</option>
                            <option value="con">Con actividades</option>
                            <option value="sin">Sin actividades</option>
                        </select>
                    </div>
                    <div>
                        <button wire:click="limpiarFiltros"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-[#C7B5A3]/55 bg-[#F8F3ED] px-3 py-2.5 text-xs font-black text-[#2F3E5C]/65 transition hover:border-[#E27D60]/40 hover:text-[#E27D60]">
                            <i class="ph-bold ph-x text-xs"></i>
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── TABLA PRINCIPAL ──────────────────────────────────────────────── --}}
        <section class="overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/65 bg-[#E6DDD3]/60 shadow-sm backdrop-blur-xl">
            <div class="border-b border-[#C7B5A3]/40 bg-[#D5C7B9]/40 px-5 py-3.5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <i class="ph-bold ph-list-bullets text-[#2F3E5C]/60 text-lg"></i>
                        <h2 class="text-sm font-black uppercase tracking-[0.15em] text-[#2F3E5C]">Catálogo de tipos</h2>
                    </div>
                    <span class="text-[10px] font-black text-[#2F3E5C]/45">
                        {{ $tipos->total() }} tipo(s)
                    </span>
                </div>
            </div>
            <div class="p-5">
                @if($tipos->isEmpty())
                    <div class="flex flex-col items-center gap-3 py-10 text-center">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#D5C7B9]/50">
                            <i class="ph-bold ph-tag text-2xl text-[#2F3E5C]/40"></i>
                        </span>
                        @if($search || $filtroUso)
                            <p class="text-sm font-bold text-[#2F3E5C]/50">No se encontraron tipos con los filtros seleccionados.</p>
                        @else
                            <p class="text-sm font-bold text-[#2F3E5C]/50">No hay tipos de actividades registrados.</p>
                            <p class="max-w-sm text-xs font-bold text-[#2F3E5C]/38">Registre el primer tipo usando el botón "Registrar tipo".</p>
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto"
                         wire:loading.class="opacity-50 transition-opacity"
                         wire:target="search,filtroUso">
                        <table class="w-full min-w-[640px] text-xs">
                            <thead>
                                <tr class="border-b border-[#C7B5A3]/40">
                                    <th class="w-10 pb-2.5"></th>
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-[#2F3E5C]/50">Tipo de actividad</th>
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-[#2F3E5C]/50">Descripción</th>
                                    <th class="pb-2.5 text-center font-black uppercase tracking-[0.12em] text-[#2F3E5C]/50">Actividades</th>
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-[#2F3E5C]/50">Estado</th>
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-[#2F3E5C]/50">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#C7B5A3]/25">
                                @foreach($tipos as $tipo)
                                    @php($vi = $resolverColorIcono($tipo->tipo))
                                    <tr wire:key="tipo-{{ $tipo->cod_tipo_act }}" class="group transition hover:bg-[#F8F3ED]/60">
                                        {{-- Ícono visual --}}
                                        <td class="py-3 pr-3">
                                            <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $vi['cls'] }}">
                                                <i class="ph-bold {{ $vi['icon'] }} text-base"></i>
                                            </span>
                                        </td>
                                        {{-- Nombre --}}
                                        <td class="py-3 pr-4">
                                            <span class="font-black text-[#2F3E5C]">{{ $tipo->tipo }}</span>
                                        </td>
                                        {{-- Descripción --}}
                                        <td class="py-3 pr-4 max-w-[240px]">
                                            @if($tipo->descripcion)
                                                <span class="line-clamp-2 text-[#2F3E5C]/60 font-bold">{{ $tipo->descripcion }}</span>
                                            @else
                                                <span class="text-[#2F3E5C]/30 italic">Sin descripción</span>
                                            @endif
                                        </td>
                                        {{-- Conteo --}}
                                        <td class="py-3 pr-4 text-center">
                                            @if($tipo->actividades_count > 0)
                                                <span class="inline-flex items-center rounded-full border border-[#8DA280]/30 bg-[#8DA280]/14 px-2.5 py-0.5 text-[10px] font-black text-[#63775B]">
                                                    {{ number_format($tipo->actividades_count) }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full border border-[#C7B5A3]/35 bg-[#D5C7B9]/40 px-2.5 py-0.5 text-[10px] font-black text-[#7C7168]">
                                                    0
                                                </span>
                                            @endif
                                        </td>
                                        {{-- Estado --}}
                                        <td class="py-3 pr-4">
                                            <span class="inline-flex items-center rounded-full border border-[#8DA280]/30 bg-[#8DA280]/14 px-2 py-0.5 text-[9px] font-black uppercase tracking-wide text-[#63775B]">
                                                Activo
                                            </span>
                                        </td>
                                        {{-- Acciones --}}
                                        <td class="py-3">
                                            <div class="flex items-center gap-1.5">
                                                {{-- Ver detalle --}}
                                                <button wire:click="abrirDetalle({{ $tipo->cod_tipo_act }})"
                                                        title="Ver detalle"
                                                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-[#2F3E5C]/15 bg-[#2F3E5C]/8 text-[#2F3E5C]/60 transition hover:border-[#2F3E5C]/30 hover:bg-[#2F3E5C]/15">
                                                    <i class="ph-bold ph-eye text-xs"></i>
                                                </button>
                                                {{-- Editar --}}
                                                @can('actividades.editar')
                                                <button wire:click="abrirEditar({{ $tipo->cod_tipo_act }})"
                                                        title="Editar"
                                                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-[#D9A05B]/25 bg-[#D9A05B]/10 text-[#9A6B2E] transition hover:border-[#D9A05B]/50 hover:bg-[#D9A05B]/20">
                                                    <i class="ph-bold ph-pencil text-xs"></i>
                                                </button>
                                                @endcan
                                                {{-- Eliminar --}}
                                                @can('actividades.editar')
                                                @if($tipo->actividades_count === 0)
                                                    <button type="button"
                                                            title="Eliminar tipo"
                                                            x-data
                                                            @click="
                                                                window.SwalAmandita.fire({
                                                                    title: '¿Eliminar tipo?',
                                                                    text: 'Se eliminará del catálogo institucional. Esta acción no se puede deshacer.',
                                                                    icon: 'warning',
                                                                    showCancelButton: true,
                                                                    confirmButtonText: 'Sí, eliminar',
                                                                    cancelButtonText: 'Cancelar'
                                                                }).then(r => { if (r.isConfirmed) $wire.eliminarTipo({{ $tipo->cod_tipo_act }}) })
                                                            "
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg border border-[#E27D60]/25 bg-[#E27D60]/10 text-[#E27D60] transition hover:border-[#E27D60]/50 hover:bg-[#E27D60]/20">
                                                        <i class="ph-bold ph-trash text-xs"></i>
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            title="No se puede eliminar: tiene actividades asociadas"
                                                            x-data
                                                            @click="
                                                                window.SwalAmandita.fire({
                                                                    icon: 'warning',
                                                                    title: 'No es posible eliminar',
                                                                    text: 'Este tipo tiene {{ $tipo->actividades_count }} actividad(es) asociada(s). Para eliminarlo, primero gestione las actividades vinculadas.',
                                                                })
                                                            "
                                                            class="flex h-7 w-7 cursor-not-allowed items-center justify-center rounded-lg border border-[#C7B5A3]/30 bg-[#C7B5A3]/15 text-[#C7B5A3] transition hover:border-[#C7B5A3]/50">
                                                        <i class="ph-bold ph-trash text-xs"></i>
                                                    </button>
                                                @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($tipos->hasPages())
                        <div class="mt-5 border-t border-[#C7B5A3]/30 pt-4">
                            {{ $tipos->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </section>

        {{-- ── NOTA INSTITUCIONAL ───────────────────────────────────────────── --}}
        <div class="flex items-start gap-3 rounded-2xl border border-[#C7B5A3]/50 bg-[#F8F3ED]/75 p-4">
            <i class="ph-bold ph-info mt-0.5 shrink-0 text-lg text-[#2F3E5C]/40"></i>
            <p class="text-xs font-bold leading-relaxed text-[#2F3E5C]/55">
                El catálogo no dispone de campo de estado en base de datos — todos los tipos se muestran como activos. Los colores e íconos son visuales calculados y no se almacenan. Para desactivar un tipo, elimínelo solo si no tiene actividades asociadas.
            </p>
        </div>

    </div>

    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL — REGISTRAR TIPO                                                  --}}
    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    @if($modalRegistrar)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-10"
             style="background: rgba(47,62,92,0.50)"
             wire:click.self="cerrarModales">
            <div class="w-full max-w-lg overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/60 bg-[#FAF7F3] shadow-2xl">
                <div class="h-1 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
                <div class="flex items-center justify-between border-b border-[#C7B5A3]/40 bg-[#E6DDD3]/50 px-5 py-4">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#E27D60]/15 text-[#E27D60]">
                            <i class="ph-bold ph-tag-simple text-lg"></i>
                        </span>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/50">Catálogo</p>
                            <h3 class="text-sm font-black text-[#2F3E5C]">Registrar tipo de actividad</h3>
                        </div>
                    </div>
                    <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-[#C7B5A3]/40 text-[#2F3E5C]/40 transition hover:border-[#E27D60]/40 hover:text-[#E27D60]">
                        <i class="ph-bold ph-x text-sm"></i>
                    </button>
                </div>
                <form wire:submit.prevent="guardarTipo" class="p-5 space-y-4">
                    <div>
                        <label class="{{ $labelCls }}">Nombre del tipo <span class="text-[#E27D60]">*</span></label>
                        <input wire:model="tipo" type="text" maxlength="50"
                               placeholder="Ej: Terapia Ocupacional"
                               class="{{ $inputCls }}" />
                        @error('tipo') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                        <p class="mt-1 text-[10px] font-bold text-[#2F3E5C]/35">Máximo 50 caracteres. Debe ser único en el catálogo.</p>
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Descripción <span class="text-[#2F3E5C]/35 normal-case tracking-normal">(opcional)</span></label>
                        <textarea wire:model="descripcion" rows="3" maxlength="1000"
                                  placeholder="Descripción breve del tipo de actividad..."
                                  class="{{ $inputCls }} resize-none"></textarea>
                        @error('descripcion') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-2.5 border-t border-[#C7B5A3]/30 pt-4">
                        <button type="button" wire:click="cerrarModales"
                                class="rounded-xl border border-[#C7B5A3]/55 bg-white px-4 py-2 text-xs font-black text-[#2F3E5C]/65 transition hover:border-[#C7B5A3] hover:text-[#2F3E5C]">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-[#E27D60] px-5 py-2 text-xs font-black text-white shadow-sm transition hover:bg-[#D06B50] active:scale-95">
                            <i class="ph-bold ph-floppy-disk text-sm"></i>
                            Registrar tipo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL — EDITAR TIPO                                                     --}}
    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    @if($modalEditar)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-10"
             style="background: rgba(47,62,92,0.50)"
             wire:click.self="cerrarModales">
            <div class="w-full max-w-lg overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/60 bg-[#FAF7F3] shadow-2xl">
                <div class="h-1 bg-gradient-to-r from-[#D9A05B] via-[#E27D60] to-[#8DA280]"></div>
                <div class="flex items-center justify-between border-b border-[#C7B5A3]/40 bg-[#E6DDD3]/50 px-5 py-4">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#D9A05B]/15 text-[#9A6B2E]">
                            <i class="ph-bold ph-pencil-simple text-lg"></i>
                        </span>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/50">Catálogo</p>
                            <h3 class="text-sm font-black text-[#2F3E5C]">Editar tipo #{{ $editandoId }}</h3>
                        </div>
                    </div>
                    <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-[#C7B5A3]/40 text-[#2F3E5C]/40 transition hover:border-[#E27D60]/40 hover:text-[#E27D60]">
                        <i class="ph-bold ph-x text-sm"></i>
                    </button>
                </div>
                <form wire:submit.prevent="actualizarTipo" class="p-5 space-y-4">
                    <div>
                        <label class="{{ $labelCls }}">Nombre del tipo <span class="text-[#E27D60]">*</span></label>
                        <input wire:model="tipo" type="text" maxlength="50"
                               class="{{ $inputCls }}" />
                        @error('tipo') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                        <p class="mt-1 text-[10px] font-bold text-[#2F3E5C]/35">La validación ignora el nombre actual del tipo (permite actualizar sin conflicto).</p>
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Descripción <span class="text-[#2F3E5C]/35 normal-case tracking-normal">(opcional)</span></label>
                        <textarea wire:model="descripcion" rows="3" maxlength="1000"
                                  class="{{ $inputCls }} resize-none"></textarea>
                        @error('descripcion') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-2.5 border-t border-[#C7B5A3]/30 pt-4">
                        <button type="button" wire:click="cerrarModales"
                                class="rounded-xl border border-[#C7B5A3]/55 bg-white px-4 py-2 text-xs font-black text-[#2F3E5C]/65 transition hover:border-[#C7B5A3] hover:text-[#2F3E5C]">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-[#D9A05B] px-5 py-2 text-xs font-black text-white shadow-sm transition hover:bg-[#C08A45] active:scale-95">
                            <i class="ph-bold ph-floppy-disk text-sm"></i>
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL — DETALLE                                                         --}}
    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    @if($modalDetalle && $detalle)
        @php($vi = $resolverColorIcono($detalle->tipo))
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-10"
             style="background: rgba(47,62,92,0.50)"
             wire:click.self="cerrarModales">
            <div class="w-full max-w-xl overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/60 bg-[#FAF7F3] shadow-2xl">
                <div class="h-1 bg-gradient-to-r from-[#8DA280] via-[#D9A05B] to-[#E27D60]"></div>
                <div class="flex items-center justify-between border-b border-[#C7B5A3]/40 bg-[#E6DDD3]/50 px-5 py-4">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $vi['cls'] }}">
                            <i class="ph-bold {{ $vi['icon'] }} text-lg"></i>
                        </span>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/50">Catálogo</p>
                            <h3 class="text-sm font-black text-[#2F3E5C]">Detalle del tipo</h3>
                        </div>
                    </div>
                    <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-[#C7B5A3]/40 text-[#2F3E5C]/40 transition hover:border-[#E27D60]/40 hover:text-[#E27D60]">
                        <i class="ph-bold ph-x text-sm"></i>
                    </button>
                </div>
                <div class="p-5 space-y-4">
                    {{-- Nombre + estado --}}
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.13em] text-[#2F3E5C]/45">Tipo de actividad</p>
                            <h4 class="mt-1 text-lg font-black text-[#2F3E5C]">{{ $detalle->tipo }}</h4>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1.5">
                            <span class="inline-flex items-center rounded-full border border-[#8DA280]/30 bg-[#8DA280]/14 px-2.5 py-1 text-[9px] font-black uppercase tracking-wide text-[#63775B]">
                                Activo
                            </span>
                            <span class="text-[10px] font-bold text-[#2F3E5C]/40">#{{ $detalle->cod_tipo_act }}</span>
                        </div>
                    </div>
                    {{-- Descripción --}}
                    <div class="rounded-xl border border-[#C7B5A3]/40 bg-[#F3ECE4]/60 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-[0.13em] text-[#2F3E5C]/45">Descripción</p>
                        <p class="mt-1 text-xs font-bold leading-relaxed text-[#2F3E5C]/70">
                            {{ $detalle->descripcion ?: 'Sin descripción registrada.' }}
                        </p>
                    </div>
                    {{-- Métricas rápidas --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-[#C7B5A3]/40 bg-[#F3ECE4]/60 px-4 py-3 text-center">
                            <p class="text-[10px] font-black uppercase tracking-[0.13em] text-[#2F3E5C]/45">Actividades asociadas</p>
                            <p class="mt-1 text-2xl font-black text-[#2F3E5C]">{{ number_format($detalle->actividades_count) }}</p>
                        </div>
                        <div class="rounded-xl border border-[#C7B5A3]/40 bg-[#F3ECE4]/60 px-4 py-3 text-center">
                            <p class="text-[10px] font-black uppercase tracking-[0.13em] text-[#2F3E5C]/45">Identificador visual</p>
                            <div class="mt-2 flex justify-center">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $vi['cls'] }}">
                                    <i class="ph-bold {{ $vi['icon'] }} text-xl"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    {{-- Últimas actividades --}}
                    @if($detalle->actividades->isNotEmpty())
                        <div>
                            <p class="mb-2 text-[10px] font-black uppercase tracking-[0.13em] text-[#2F3E5C]/45">
                                Últimas {{ $detalle->actividades->count() }} actividad(es) registradas
                            </p>
                            <div class="space-y-1.5">
                                @foreach($detalle->actividades as $act)
                                    @php($ne = \App\Models\ActividadAdulto::normalizarEstado($act->estado ?? ''))
                                    <div class="flex items-center justify-between gap-3 rounded-xl border border-[#C7B5A3]/35 bg-[#F8F3ED]/60 px-3 py-2">
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-[10px] font-black text-[#2F3E5C]">
                                                {{ optional($act->adultoMayor)->ap_paterno ?? '—' }}
                                                {{ optional($act->adultoMayor)->nombres ?? '' }}
                                            </p>
                                            <p class="text-[9px] font-bold text-[#2F3E5C]/50">
                                                {{ \Carbon\Carbon::parse($act->fecha)->format('d/m/Y') }}
                                                @if($act->hora) — {{ substr($act->hora, 0, 5) }} @endif
                                            </p>
                                        </div>
                                        <span class="shrink-0 inline-flex items-center rounded-full border px-2 py-0.5 text-[8px] font-black uppercase tracking-wide {{ $ne['clase'] }}">
                                            {{ $ne['etiqueta'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    {{-- Botones --}}
                    <div class="flex justify-end gap-2.5 border-t border-[#C7B5A3]/30 pt-4">
                        @can('actividades.editar')
                        <button type="button" wire:click="abrirEditar({{ $detalle->cod_tipo_act }})"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-[#D9A05B]/40 bg-[#D9A05B]/12 px-4 py-2 text-xs font-black text-[#9A6B2E] transition hover:bg-[#D9A05B]/25">
                            <i class="ph-bold ph-pencil text-xs"></i>
                            Editar
                        </button>
                        @endcan
                        <button type="button" wire:click="cerrarModales"
                                class="rounded-xl border border-[#C7B5A3]/55 bg-white px-4 py-2 text-xs font-black text-[#2F3E5C]/65 transition hover:border-[#C7B5A3] hover:text-[#2F3E5C]">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
