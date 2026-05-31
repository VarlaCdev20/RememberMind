@php
    $inputCls = 'w-full rounded-xl border border-[#C7B5A3]/55 bg-[#F8F3ED] px-3 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none ring-[#E27D60]/25 transition focus:border-[#E27D60]/50 focus:ring-2';
    $labelCls = 'block text-[10px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/55 mb-1.5';
    $errCls   = 'mt-1 text-[10px] font-bold text-[#E27D60]';
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
                        <i class="ph-bold ph-calendar-check text-sm"></i>
                        CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS — Área de Actividades
                    </span>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-[#2F3E5C] sm:text-4xl">
                        Actividades institucionales
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-[#2F3E5C]/70">
                        Gestión global de actividades dirigidas a adultos mayores: registro, seguimiento y control.
                    </p>
                </div>
                <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-3 rounded-2xl border border-[#C7B5A3]/55 bg-[#F3ECE4]/68 px-4 py-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#2F3E5C] text-white shadow-sm">
                            <i class="ph-bold ph-calendar-check text-lg"></i>
                        </span>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-[#2F3E5C]/52">Total</p>
                            <p class="text-2xl font-black leading-none text-[#2F3E5C]">{{ number_format($stats['total']) }}</p>
                        </div>
                    </div>
                    <button wire:click="abrirRegistrar"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#E27D60] px-4 py-2.5 text-xs font-black uppercase tracking-[0.12em] text-white shadow-sm transition hover:bg-[#D06B50] hover:shadow-md active:scale-95">
                        <i class="ph-bold ph-plus text-sm"></i>
                        Nueva actividad
                    </button>
                </div>
            </div>
        </section>

        {{-- ── 8 MÉTRICAS ──────────────────────────────────────────────────── --}}
        @php
            $metricas = [
                ['label' => 'Total actividades',     'valor' => $stats['total'],            'icono' => 'ph-calendar-blank',     'tono' => 'azul'],
                ['label' => 'Programadas',            'valor' => $stats['programadas'],      'icono' => 'ph-clock',              'tono' => 'dorado'],
                ['label' => 'Realizadas',             'valor' => $stats['realizadas'],       'icono' => 'ph-check-circle',       'tono' => 'verde'],
                ['label' => 'Canceladas',             'valor' => $stats['canceladas'],       'icono' => 'ph-x-circle',           'tono' => 'terracota'],
                ['label' => 'Reprogramadas',          'valor' => $stats['reprogramadas'],    'icono' => 'ph-arrow-counter-clockwise', 'tono' => 'violeta'],
                ['label' => 'Hoy',                    'valor' => $stats['hoy'],              'icono' => 'ph-sun',                'tono' => 'dorado'],
                ['label' => 'Próximas 7 días',        'valor' => $stats['proximas7'],        'icono' => 'ph-calendar-dots',      'tono' => 'verde'],
                ['label' => 'Adultos con actividades','valor' => $stats['adultos_distintos'],'icono' => 'ph-users',              'tono' => 'azul'],
            ];
            $tonoClases = [
                'azul'     => ['icono' => 'bg-[#2F3E5C]/10 text-[#2F3E5C]',  'valor' => 'text-[#2F3E5C]', 'linea' => 'bg-[#2F3E5C]'],
                'verde'    => ['icono' => 'bg-[#8DA280]/18 text-[#63775B]',   'valor' => 'text-[#63775B]', 'linea' => 'bg-[#8DA280]'],
                'terracota'=> ['icono' => 'bg-[#E27D60]/12 text-[#E27D60]',   'valor' => 'text-[#E27D60]', 'linea' => 'bg-[#E27D60]'],
                'dorado'   => ['icono' => 'bg-[#D9A05B]/16 text-[#9A6B2E]',   'valor' => 'text-[#9A6B2E]', 'linea' => 'bg-[#D9A05B]'],
                'violeta'  => ['icono' => 'bg-[#7A68B0]/12 text-[#5A4E8A]',   'valor' => 'text-[#5A4E8A]', 'linea' => 'bg-[#7A68B0]'],
            ];
        @endphp
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($metricas as $m)
                @php
                    $tono = $tonoClases[$m['tono']];
                @endphp
                <article class="relative min-h-[110px] overflow-hidden rounded-2xl border border-[#C7B5A3]/55 bg-[#F3ECE4]/78 p-4 shadow-sm backdrop-blur-xl transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_16px_34px_rgba(47,62,92,0.11)]">
                    <div class="absolute inset-x-0 top-0 h-1 {{ $tono['linea'] }}"></div>
                    <div class="flex items-start justify-between gap-3">
                        <p class="max-w-[11rem] text-[10px] font-black uppercase leading-snug tracking-[0.13em] text-[#2F3E5C]/55">{{ $m['label'] }}</p>
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $tono['icono'] }}">
                            <i class="ph-bold {{ $m['icono'] }} text-lg"></i>
                        </span>
                    </div>
                    <p class="mt-4 text-3xl font-black leading-none {{ $tono['valor'] }}">{{ number_format($m['valor']) }}</p>
                </article>
            @endforeach
        </section>

        {{-- ── FILTROS Y BÚSQUEDA ───────────────────────────────────────────── --}}
        <section class="overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/65 bg-[#E6DDD3]/60 shadow-sm backdrop-blur-xl">
            <div class="border-b border-[#C7B5A3]/40 bg-[#D5C7B9]/40 px-5 py-3">
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-funnel text-[#2F3E5C]/60 text-base"></i>
                    <span class="text-[10px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]">Filtros y búsqueda</span>
                </div>
            </div>
            <div class="p-4">
                <div class="flex flex-wrap gap-3 items-end">
                    {{-- Búsqueda --}}
                    <div class="min-w-[180px] flex-1">
                        <label class="{{ $labelCls }}">Buscar adulto mayor</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[#2F3E5C]/40">
                                <i class="ph-bold ph-magnifying-glass text-sm"></i>
                            </span>
                            <input wire:model.live.debounce.300ms="search"
                                   type="text" placeholder="Nombre o apellido..."
                                   class="{{ $inputCls }} pl-8" />
                        </div>
                    </div>
                    {{-- Tipo --}}
                    <div class="min-w-[160px] flex-1">
                        <label class="{{ $labelCls }}">Tipo de actividad</label>
                        <select wire:model.live="filtroTipo" class="{{ $inputCls }}">
                            <option value="">Todos los tipos</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo->cod_tipo_act }}">{{ $tipo->tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Estado --}}
                    <div class="min-w-[140px] flex-1">
                        <label class="{{ $labelCls }}">Estado</label>
                        <select wire:model.live="filtroEstado" class="{{ $inputCls }}">
                            <option value="">Todos los estados</option>
                            <option value="PROGRAMADA">Programada</option>
                            <option value="REALIZADA">Realizada</option>
                            <option value="COMPLETADA">Completada</option>
                            <option value="CANCELADA">Cancelada</option>
                            <option value="REPROGRAMADA">Reprogramada</option>
                        </select>
                    </div>
                    {{-- Fecha desde --}}
                    <div class="min-w-[140px]">
                        <label class="{{ $labelCls }}">Desde</label>
                        <input wire:model.live="filtroFechaDesde" type="date" class="{{ $inputCls }}" />
                    </div>
                    {{-- Fecha hasta --}}
                    <div class="min-w-[140px]">
                        <label class="{{ $labelCls }}">Hasta</label>
                        <input wire:model.live="filtroFechaHasta" type="date" class="{{ $inputCls }}" />
                    </div>
                    {{-- Limpiar --}}
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

        {{-- ── TABLA DE ACTIVIDADES ─────────────────────────────────────────── --}}
        @php /** @var \Illuminate\Pagination\LengthAwarePaginator $actividades */ @endphp
        <section class="overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/65 bg-[#E6DDD3]/60 shadow-sm backdrop-blur-xl">
            <div class="border-b border-[#C7B5A3]/40 bg-[#D5C7B9]/40 px-5 py-3.5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <i class="ph-bold ph-list-bullets text-[#2F3E5C]/60 text-lg"></i>
                        <h2 class="text-sm font-black uppercase tracking-[0.15em] text-[#2F3E5C]">Listado de actividades</h2>
                    </div>
                    <span class="text-[10px] font-black text-[#2F3E5C]/45">
                        {{ $actividades->total() }} registro(s)
                    </span>
                </div>
            </div>
            <div class="p-5">
                @if($actividades->isEmpty())
                    <div class="flex flex-col items-center gap-3 py-10 text-center">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#D5C7B9]/50">
                            <i class="ph-bold ph-calendar-blank text-2xl text-[#2F3E5C]/40"></i>
                        </span>
                        <p class="text-sm font-bold text-[#2F3E5C]/50">No se encontraron actividades con los filtros aplicados.</p>
                    </div>
                @else
                    <div class="overflow-x-auto" wire:loading.class="opacity-50 transition-opacity"
                         wire:target="search,filtroTipo,filtroEstado,filtroFechaDesde,filtroFechaHasta">
                        <table class="w-full min-w-[700px] text-xs">
                            <thead>
                                <tr class="border-b border-[#C7B5A3]/40">
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-[#2F3E5C]/50">#</th>
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-[#2F3E5C]/50">Adulto mayor</th>
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-[#2F3E5C]/50">Tipo actividad</th>
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-[#2F3E5C]/50">Fecha</th>
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-[#2F3E5C]/50">Hora</th>
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-[#2F3E5C]/50">Estado</th>
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-[#2F3E5C]/50">Observación</th>
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-[#2F3E5C]/50">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#C7B5A3]/25">
                                @foreach($actividades as $actividad)
                                    @php
                                        $ne = \App\Models\ActividadAdulto::normalizarEstado($actividad->estado ?? '');
                                    @endphp
                                    <tr wire:key="act-{{ $actividad->cod_act_adul }}" class="group transition hover:bg-[#F8F3ED]/60">
                                        <td class="py-3 pr-3 font-bold text-[#2F3E5C]/40">{{ $actividad->cod_act_adul }}</td>
                                        <td class="py-3 pr-4 font-bold text-[#2F3E5C]">
                                            {{ optional($actividad->adultoMayor)->ap_paterno ?? '—' }}
                                            {{ optional($actividad->adultoMayor)->nombres ?? '' }}
                                        </td>
                                        <td class="py-3 pr-4 text-[#2F3E5C]/70">
                                            {{ optional($actividad->tipoActividad)->tipo ?? '—' }}
                                        </td>
                                        <td class="py-3 pr-4 text-[#2F3E5C]/65">
                                            {{ \Carbon\Carbon::parse($actividad->fecha)->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 pr-4 text-[#2F3E5C]/65">
                                            {{ $actividad->hora ? substr($actividad->hora, 0, 5) : '—' }}
                                        </td>
                                        <td class="py-3 pr-4">
                                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[9px] font-black uppercase tracking-wide {{ $ne['clase'] }}">
                                                {{ $ne['etiqueta'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 pr-4 max-w-[160px] truncate text-[#2F3E5C]/55">
                                            {{ $actividad->obs ? mb_substr($actividad->obs, 0, 45) . (mb_strlen($actividad->obs) > 45 ? '…' : '') : '—' }}
                                        </td>
                                        <td class="py-3">
                                            <div class="flex items-center gap-1.5">
                                                <button wire:click="abrirDetalle({{ $actividad->cod_act_adul }})"
                                                        title="Ver detalle"
                                                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-[#2F3E5C]/15 bg-[#2F3E5C]/8 text-[#2F3E5C]/60 transition hover:border-[#2F3E5C]/30 hover:bg-[#2F3E5C]/15">
                                                    <i class="ph-bold ph-eye text-xs"></i>
                                                </button>
                                                <button wire:click="abrirEditar({{ $actividad->cod_act_adul }})"
                                                        title="Editar"
                                                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-[#D9A05B]/25 bg-[#D9A05B]/10 text-[#9A6B2E] transition hover:border-[#D9A05B]/50 hover:bg-[#D9A05B]/20">
                                                    <i class="ph-bold ph-pencil text-xs"></i>
                                                </button>
                                                @if(strtoupper($actividad->estado) !== 'CANCELADA')
                                                    <button type="button"
                                                            title="Cancelar actividad"
                                                            x-data
                                                            @click="
                                                                window.SwalAmandita.fire({
                                                                    title: '¿Cancelar actividad?',
                                                                    text: 'El estado cambiará a CANCELADA. Esta acción quedará registrada en el historial.',
                                                                    icon: 'warning',
                                                                    showCancelButton: true,
                                                                    confirmButtonText: 'Sí, cancelar',
                                                                    cancelButtonText: 'No'
                                                                }).then(r => { if (r.isConfirmed) $wire.cancelarActividad({{ $actividad->cod_act_adul }}) })
                                                            "
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg border border-[#E27D60]/25 bg-[#E27D60]/10 text-[#E27D60] transition hover:border-[#E27D60]/50 hover:bg-[#E27D60]/20">
                                                        <i class="ph-bold ph-x-circle text-xs"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($actividades->hasPages())
                        <div class="mt-5 border-t border-[#C7B5A3]/30 pt-4">
                            {{ $actividades->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </section>

    </div>

    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL — REGISTRAR ACTIVIDAD                                             --}}
    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    @if($modalRegistrar)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8"
             style="background: rgba(47,62,92,0.50)"
             wire:click.self="cerrarModales">
            <div class="w-full max-w-xl overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/60 bg-[#FAF7F3] shadow-2xl">
                <div class="h-1 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-[#C7B5A3]/40 bg-[#E6DDD3]/50 px-5 py-4">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#E27D60]/15 text-[#E27D60]">
                            <i class="ph-bold ph-calendar-plus text-lg"></i>
                        </span>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/50">Actividades</p>
                            <h3 class="text-sm font-black text-[#2F3E5C]">Registrar actividad</h3>
                        </div>
                    </div>
                    <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-[#C7B5A3]/40 text-[#2F3E5C]/40 transition hover:border-[#E27D60]/40 hover:text-[#E27D60]">
                        <i class="ph-bold ph-x text-sm"></i>
                    </button>
                </div>
                {{-- Formulario --}}
                <form wire:submit.prevent="guardarActividad" class="p-5 space-y-4">
                    {{-- Adulto Mayor --}}
                    <div>
                        <label class="{{ $labelCls }}">Adulto mayor <span class="text-[#E27D60]">*</span></label>
                        <select wire:model="codAm" class="{{ $inputCls }}">
                            <option value="">Seleccione un adulto mayor...</option>
                            @foreach($adultos as $adulto)
                                <option value="{{ $adulto->cod_am }}">
                                    {{ $adulto->ap_paterno }} {{ $adulto->ap_materno ?? '' }}, {{ $adulto->nombres }}
                                </option>
                            @endforeach
                        </select>
                        @error('codAm') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    {{-- Tipo de Actividad --}}
                    <div>
                        <label class="{{ $labelCls }}">Tipo de actividad <span class="text-[#E27D60]">*</span></label>
                        <select wire:model="codTipoAct" class="{{ $inputCls }}">
                            <option value="">Seleccione un tipo...</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo->cod_tipo_act }}">{{ $tipo->tipo }}</option>
                            @endforeach
                        </select>
                        @error('codTipoAct') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    {{-- Fecha y Hora --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $labelCls }}">Fecha <span class="text-[#E27D60]">*</span></label>
                            <input wire:model="fecha" type="date" class="{{ $inputCls }}" />
                            @error('fecha') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Hora <span class="text-[#E27D60]">*</span></label>
                            <input wire:model="hora" type="time" class="{{ $inputCls }}" />
                            @error('hora') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    {{-- Estado --}}
                    <div>
                        <label class="{{ $labelCls }}">Estado <span class="text-[#E27D60]">*</span></label>
                        <select wire:model="estado" class="{{ $inputCls }}">
                            <option value="PROGRAMADA">Programada</option>
                            <option value="REALIZADA">Realizada</option>
                            <option value="COMPLETADA">Completada</option>
                            <option value="REPROGRAMADA">Reprogramada</option>
                            <option value="CANCELADA">Cancelada</option>
                        </select>
                        @error('estado') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    {{-- Observaciones --}}
                    <div>
                        <label class="{{ $labelCls }}">Observaciones <span class="text-[#2F3E5C]/35 normal-case tracking-normal">(opcional)</span></label>
                        <textarea wire:model="obs" rows="3" maxlength="2000"
                                  placeholder="Notas adicionales sobre la actividad..."
                                  class="{{ $inputCls }} resize-none"></textarea>
                        @error('obs') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    {{-- Botones --}}
                    <div class="flex justify-end gap-2.5 border-t border-[#C7B5A3]/30 pt-4">
                        <button type="button" wire:click="cerrarModales"
                                class="rounded-xl border border-[#C7B5A3]/55 bg-white px-4 py-2 text-xs font-black text-[#2F3E5C]/65 transition hover:border-[#C7B5A3] hover:text-[#2F3E5C]">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-[#E27D60] px-5 py-2 text-xs font-black text-white shadow-sm transition hover:bg-[#D06B50] active:scale-95">
                            <i class="ph-bold ph-floppy-disk text-sm"></i>
                            Registrar actividad
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL — EDITAR ACTIVIDAD                                                --}}
    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    @if($modalEditar)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8"
             style="background: rgba(47,62,92,0.50)"
             wire:click.self="cerrarModales">
            <div class="w-full max-w-xl overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/60 bg-[#FAF7F3] shadow-2xl">
                <div class="h-1 bg-gradient-to-r from-[#D9A05B] via-[#E27D60] to-[#8DA280]"></div>
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-[#C7B5A3]/40 bg-[#E6DDD3]/50 px-5 py-4">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#D9A05B]/15 text-[#9A6B2E]">
                            <i class="ph-bold ph-pencil-simple text-lg"></i>
                        </span>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/50">Actividades</p>
                            <h3 class="text-sm font-black text-[#2F3E5C]">Editar actividad #{{ $editandoId }}</h3>
                        </div>
                    </div>
                    <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-[#C7B5A3]/40 text-[#2F3E5C]/40 transition hover:border-[#E27D60]/40 hover:text-[#E27D60]">
                        <i class="ph-bold ph-x text-sm"></i>
                    </button>
                </div>
                {{-- Formulario --}}
                <form wire:submit.prevent="actualizarActividad" class="p-5 space-y-4">
                    {{-- Adulto Mayor --}}
                    <div>
                        <label class="{{ $labelCls }}">Adulto mayor <span class="text-[#E27D60]">*</span></label>
                        <select wire:model="codAm" class="{{ $inputCls }}">
                            <option value="">Seleccione un adulto mayor...</option>
                            @foreach($adultos as $adulto)
                                <option value="{{ $adulto->cod_am }}">
                                    {{ $adulto->ap_paterno }} {{ $adulto->ap_materno ?? '' }}, {{ $adulto->nombres }}
                                </option>
                            @endforeach
                        </select>
                        @error('codAm') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    {{-- Tipo de Actividad --}}
                    <div>
                        <label class="{{ $labelCls }}">Tipo de actividad <span class="text-[#E27D60]">*</span></label>
                        <select wire:model="codTipoAct" class="{{ $inputCls }}">
                            <option value="">Seleccione un tipo...</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo->cod_tipo_act }}">{{ $tipo->tipo }}</option>
                            @endforeach
                        </select>
                        @error('codTipoAct') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    {{-- Fecha y Hora --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $labelCls }}">Fecha <span class="text-[#E27D60]">*</span></label>
                            <input wire:model="fecha" type="date" class="{{ $inputCls }}" />
                            @error('fecha') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Hora <span class="text-[#E27D60]">*</span></label>
                            <input wire:model="hora" type="time" class="{{ $inputCls }}" />
                            @error('hora') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    {{-- Estado --}}
                    <div>
                        <label class="{{ $labelCls }}">Estado <span class="text-[#E27D60]">*</span></label>
                        <select wire:model="estado" class="{{ $inputCls }}">
                            <option value="PROGRAMADA">Programada</option>
                            <option value="REALIZADA">Realizada</option>
                            <option value="COMPLETADA">Completada</option>
                            <option value="REPROGRAMADA">Reprogramada</option>
                            <option value="CANCELADA">Cancelada</option>
                        </select>
                        @error('estado') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    {{-- Observaciones --}}
                    <div>
                        <label class="{{ $labelCls }}">Observaciones <span class="text-[#2F3E5C]/35 normal-case tracking-normal">(opcional)</span></label>
                        <textarea wire:model="obs" rows="3" maxlength="2000"
                                  placeholder="Notas adicionales sobre la actividad..."
                                  class="{{ $inputCls }} resize-none"></textarea>
                        @error('obs') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    {{-- Botones --}}
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
        @php
            $ne = \App\Models\ActividadAdulto::normalizarEstado($detalle->estado ?? '');
        @endphp
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8"
             style="background: rgba(47,62,92,0.50)"
             wire:click.self="cerrarModales">
            <div class="w-full max-w-lg overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/60 bg-[#FAF7F3] shadow-2xl">
                <div class="h-1 bg-gradient-to-r from-[#8DA280] via-[#D9A05B] to-[#E27D60]"></div>
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-[#C7B5A3]/40 bg-[#E6DDD3]/50 px-5 py-4">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#8DA280]/15 text-[#63775B]">
                            <i class="ph-bold ph-calendar-check text-lg"></i>
                        </span>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/50">Actividades</p>
                            <h3 class="text-sm font-black text-[#2F3E5C]">Detalle de actividad</h3>
                        </div>
                    </div>
                    <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-[#C7B5A3]/40 text-[#2F3E5C]/40 transition hover:border-[#E27D60]/40 hover:text-[#E27D60]">
                        <i class="ph-bold ph-x text-sm"></i>
                    </button>
                </div>
                {{-- Contenido --}}
                <div class="p-5 space-y-4">
                    {{-- Estado badge --}}
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/45">Actividad #{{ $detalle->cod_act_adul }}</span>
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-wide {{ $ne['clase'] }}">
                            {{ $ne['etiqueta'] }}
                        </span>
                    </div>
                    {{-- Adulto Mayor --}}
                    <div class="rounded-xl border border-[#C7B5A3]/40 bg-[#F3ECE4]/60 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-[0.13em] text-[#2F3E5C]/45">Adulto mayor</p>
                        <p class="mt-1 text-sm font-black text-[#2F3E5C]">
                            {{ optional($detalle->adultoMayor)->ap_paterno ?? '—' }}
                            {{ optional($detalle->adultoMayor)->ap_materno ?? '' }}
                            {{ optional($detalle->adultoMayor)->nombres ?? '' }}
                        </p>
                        @if($detalle->adultoMayor)
                            <p class="text-[10px] font-bold text-[#2F3E5C]/50">{{ $detalle->cod_am }}</p>
                        @endif
                    </div>
                    {{-- Tipo + Programación --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-[#C7B5A3]/40 bg-[#F3ECE4]/60 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.13em] text-[#2F3E5C]/45">Tipo de actividad</p>
                            <p class="mt-1 text-sm font-bold text-[#2F3E5C]">
                                {{ optional($detalle->tipoActividad)->tipo ?? '—' }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-[#C7B5A3]/40 bg-[#F3ECE4]/60 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.13em] text-[#2F3E5C]/45">Fecha y hora</p>
                            <p class="mt-1 text-sm font-bold text-[#2F3E5C]">
                                {{ \Carbon\Carbon::parse($detalle->fecha)->format('d/m/Y') }}
                            </p>
                            @if($detalle->hora)
                                <p class="text-xs font-bold text-[#2F3E5C]/60">{{ substr($detalle->hora, 0, 5) }} hrs.</p>
                            @endif
                        </div>
                    </div>
                    {{-- Observaciones --}}
                    <div class="rounded-xl border border-[#C7B5A3]/40 bg-[#F3ECE4]/60 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-[0.13em] text-[#2F3E5C]/45">Observaciones</p>
                        <p class="mt-1 text-xs font-bold leading-relaxed text-[#2F3E5C]/70">
                            {{ $detalle->obs ?: 'Sin observaciones registradas.' }}
                        </p>
                    </div>
                    {{-- Timestamps --}}
                    @if($detalle->created_at)
                        <div class="flex items-center gap-4 text-[10px] font-bold text-[#2F3E5C]/35">
                            <span>Registrado: {{ $detalle->created_at->format('d/m/Y H:i') }}</span>
                            @if($detalle->updated_at && $detalle->updated_at->ne($detalle->created_at))
                                <span>Editado: {{ $detalle->updated_at->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                    @endif
                    {{-- Botones --}}
                    <div class="flex justify-end gap-2.5 border-t border-[#C7B5A3]/30 pt-4">
                        <button type="button" wire:click="abrirEditar({{ $detalle->cod_act_adul }})"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-[#D9A05B]/40 bg-[#D9A05B]/12 px-4 py-2 text-xs font-black text-[#9A6B2E] transition hover:bg-[#D9A05B]/25">
                            <i class="ph-bold ph-pencil text-xs"></i>
                            Editar
                        </button>
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
