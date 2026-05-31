{{-- TAB HISTORIAL — Historial y Trazabilidad Institucional --}}
<section
    x-show="tab === 'historial'"
    x-transition.opacity.duration.250ms
    x-data="{
        activeSection: 'estados',
        filtroEvento: 'todos',
        buscarBitacora: '',
        mostrarDetalles: {},
        toggleDetalle(id) { this.mostrarDetalles[id] = !this.mostrarDetalles[id]; }
    }"
    class="space-y-6"
>
    @php
        use Carbon\Carbon;
        $bitacoraLista = isset($bitacora) && is_iterable($bitacora) ? collect($bitacora) : collect();
        $eventosFiltroLista = isset($eventosFiltro) && is_iterable($eventosFiltro) ? collect($eventosFiltro) : collect();
        $historialLista = isset($historialEstados) && is_iterable($historialEstados) ? collect($historialEstados) : collect();

        // Obtener último cambio de estado
        $ultimoCambio = $historialLista->first();
        $ultimoCambioFecha = $ultimoCambio ? Carbon::parse($ultimoCambio->fecha_cambio)->format('d/m/Y') : 'Sin cambios';
        $ultimoCambioResponsable = $ultimoCambio ? ($ultimoCambio->cambiadoPor?->name ?? 'Sistema') : 'Sistema';
        $estadoActualText = optional($adulto->estado)->estado ?? 'ACTIVO';
    @endphp

    {{-- Encabezado del tab --}}
    <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
        <div class="h-1 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A27C] to-[#8DA280]"></div>
        <div class="border-b border-[#D5C7B9] px-5 py-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <span class="text-[11px] font-black uppercase tracking-[0.18em] text-[#9A7B60]">
                        Trazabilidad institucional
                    </span>
                    <h2 class="mt-1 text-lg font-black text-[#2F3E5C]">
                        Historial y Trazabilidad
                    </h2>
                    <p class="mt-1 text-xs font-bold leading-5 text-[#2F3E5C]/55">
                        Registro institucional de cambios, estados y acciones relevantes del adulto mayor.
                    </p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <span class="rounded-full border border-[#C7B5A3] bg-[#D5C7B9]/50 px-3 py-1 text-[10px] font-black text-[#2F3E5C]/60">
                        {{ $historialLista->count() }} {{ $historialLista->count() === 1 ? 'cambio de estado' : 'cambios de estado' }}
                    </span>
                    <span class="rounded-full border border-[#8DA280]/30 bg-[#8DA280]/10 px-3 py-1 text-[10px] font-black text-[#617453]">
                        Bitácora Activa
                    </span>
                </div>
            </div>
        </div>

        {{-- Indicadores de trazabilidad --}}
        <div class="grid gap-3 p-5 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Total de cambios de estado --}}
            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">Cambios de Estado</p>
                <p class="mt-2 text-2xl font-black text-[#E27D60]">
                    {{ $historialLista->count() }}
                </p>
            </div>

            {{-- Estado actual --}}
            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">Estado Actual</p>
                <div class="mt-2 flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-emerald-50 border border-emerald-200/50 px-2.5 py-0.5 text-xs font-black text-emerald-800 uppercase">
                        {{ $estadoActualText }}
                    </span>
                </div>
            </div>

            {{-- Último cambio de estado --}}
            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">Último Cambio</p>
                <p class="mt-2 text-sm font-black text-[#2F3E5C] truncate">
                    {{ $ultimoCambioFecha }}
                </p>
            </div>

            {{-- Usuario responsable del último cambio --}}
            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">Responsable del Cambio</p>
                <p class="mt-2 text-sm font-black text-[#617453] truncate">
                    {{ $ultimoCambioResponsable }}
                </p>
            </div>
        </div>
    </section>

    {{-- Switcher de sección local --}}
    <div class="flex border-b border-[#CBBBAA]/30 pb-2">
        <div class="flex gap-2 bg-[#EBE3DB]/40 p-1 rounded-xl border border-[#CBBBAA]/30">
            <button 
                type="button"
                @click="activeSection = 'estados'"
                :class="activeSection === 'estados' ? 'bg-[#2F3E5C] text-white shadow-xs' : 'text-[#2F3E5C] hover:bg-[#2F3E5C]/5'"
                class="px-4 py-1.5 text-xs font-black rounded-lg transition active:scale-95 flex items-center gap-1.5"
            >
                <i class="ph-bold ph-git-commit text-sm"></i>
                Cambios de Estado
            </button>
            <button 
                type="button"
                @click="activeSection = 'bitacora'"
                :class="activeSection === 'bitacora' ? 'bg-[#2F3E5C] text-white shadow-xs' : 'text-[#2F3E5C] hover:bg-[#2F3E5C]/5'"
                class="px-4 py-1.5 text-xs font-black rounded-lg transition active:scale-95 flex items-center gap-1.5"
            >
                <i class="ph-bold ph-clock-clockwise text-sm"></i>
                Bitácora de Acciones
            </button>
        </div>
    </div>

    {{-- SECCIÓN A: CAMBIOS DE ESTADO --}}
    <div x-show="activeSection === 'estados'" class="space-y-4">
        <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 p-6 shadow-[0_12px_28px_rgba(47,62,92,0.08)]">
            @if($historialLista->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#D5C7B9]/50 text-[#2F3E5C]/30">
                        <i class="ph-bold ph-git-commit text-3xl"></i>
                    </div>
                    <p class="mt-4 text-sm font-black text-[#2F3E5C]/50">Sin cambios de estado registrados.</p>
                    <p class="mt-1 text-xs font-bold text-[#2F3E5C]/35">No se han registrado cambios de estado institucionales en la ficha de este adulto mayor.</p>
                </div>
            @else
                <div class="relative space-y-6">
                    {{-- Línea vertical --}}
                    <div class="absolute left-5 top-2 hidden h-[calc(100%-16px)] w-px bg-[#CBBBAA]/60 sm:block"></div>

                    @foreach($historialLista as $hist)
                        @php
                            $fechaFormateada = $hist->fecha_cambio ? Carbon::parse($hist->fecha_cambio)->format('d/m/Y H:i') : 'N/D';
                            $estAnt = $hist->estadoAnteriorRelacion?->estado ?? 'Desconocido';
                            $estNue = $hist->estadoNuevoRelacion?->estado ?? 'Desconocido';
                            $respName = $hist->cambiadoPor?->name ?? 'Sistema';
                        @endphp
                        <article class="relative rounded-[20px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-5 sm:ml-12 transition hover:shadow-[0_6px_20px_rgba(47,62,92,0.06)]">
                            {{-- Conector de línea --}}
                            <div class="absolute -left-[40px] top-6 hidden h-6 w-6 items-center justify-center rounded-full bg-[#E27D60]/20 text-[#E27D60] border border-[#E27D60]/30 sm:flex">
                                <span class="h-2.5 w-2.5 rounded-full bg-[#E27D60]"></span>
                            </div>

                            <div class="flex flex-col gap-3">
                                {{-- Fila de Estado y Fecha --}}
                                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#D5C7B9]/45 pb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-black text-slate-500 uppercase">
                                            {{ $estAnt }}
                                        </span>
                                        <i class="ph-bold ph-arrow-right text-xs text-[#2F3E5C]/40"></i>
                                        <span class="inline-flex items-center rounded bg-emerald-50 border border-emerald-200/50 px-2 py-0.5 text-xs font-black text-emerald-800 uppercase">
                                            {{ $estNue }}
                                        </span>
                                    </div>
                                    <span class="text-xs font-bold text-[#2F3E5C]/50 flex items-center gap-1">
                                        <i class="ph-bold ph-calendar"></i>
                                        {{ $fechaFormateada }}
                                    </span>
                                </div>

                                {{-- Contenido descriptivo --}}
                                <div class="space-y-1.5">
                                    <p class="text-xs font-black text-[#2F3E5C]/40 uppercase tracking-wider">Motivo del cambio</p>
                                    <p class="text-sm font-bold text-[#2F3E5C] bg-white/35 p-3 rounded-xl border border-[#D5C7B9]/40 leading-relaxed">
                                        {{ $hist->motivo ?: 'No se registró motivo.' }}
                                    </p>
                                </div>

                                @if($hist->observacion)
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-black text-[#2F3E5C]/40 uppercase tracking-wider">Observaciones adicionales</p>
                                        <p class="text-xs font-semibold text-[#2F3E5C]/75 leading-relaxed">
                                            {{ $hist->observacion }}
                                        </p>
                                    </div>
                                @endif

                                {{-- Responsable y Documento --}}
                                <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-[#D5C7B9]/20 text-[10px] font-bold text-[#2F3E5C]/45">
                                    <span class="inline-flex items-center">
                                        <i class="ph-bold ph-user-circle mr-1"></i>
                                        Registrado por: <strong class="ml-1 text-[#2F3E5C]">{{ $respName }}</strong>
                                    </span>

                                    @if($hist->documento_respaldo)
                                        <span class="inline-flex items-center text-[#E27D60]">
                                            <i class="ph-bold ph-file-text mr-1"></i>
                                            Documento de Respaldo: #{{ $hist->documento_respaldo }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    {{-- SECCIÓN B: BITÁCORA DE ACCIONES --}}
    <div x-show="activeSection === 'bitacora'" class="space-y-4" style="display: none;">
        {{-- Filtros específicos de Bitácora --}}
        <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 p-4 shadow-[0_4px_14px_rgba(47,62,92,0.04)]">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/45 mr-1">Categorías:</span>

                    <button
                        type="button"
                        @click="filtroEvento = 'todos'"
                        :class="filtroEvento === 'todos' ? 'bg-[#2F3E5C] text-white shadow-xs' : 'bg-[#D5C7B9]/60 text-[#2F3E5C]/75 hover:bg-[#C7B5A3]'"
                        class="rounded-lg px-3 py-1.5 text-[10px] font-black transition"
                    >
                        Todos
                    </button>

                    @foreach($eventosFiltroLista as $ev)
                        <button
                            type="button"
                            @click="filtroEvento = '{{ $ev['valor'] }}'"
                            :class="filtroEvento === '{{ $ev['valor'] }}' ? 'bg-[#2F3E5C] text-white shadow-xs' : 'bg-[#D5C7B9]/60 text-[#2F3E5C]/75 hover:bg-[#C7B5A3]'"
                            class="rounded-lg px-3 py-1.5 text-[10px] font-black transition"
                        >
                            {{ $ev['etiqueta'] }}
                        </button>
                    @endforeach
                </div>

                {{-- Buscador interactivo --}}
                <div class="flex items-center gap-2 rounded-xl border border-[#C7B5A3] bg-white/45 px-3 py-1.5 shrink-0">
                    <i class="ph-bold ph-magnifying-glass text-xs text-[#2F3E5C]/45"></i>
                    <input
                        type="text"
                        x-model="buscarBitacora"
                        placeholder="Buscar acción..."
                        class="bg-transparent text-xs font-bold text-[#2F3E5C] outline-none placeholder:text-[#2F3E5C]/35 w-full md:w-48"
                    >
                </div>
            </div>
        </section>

        {{-- Línea de tiempo de Bitácora --}}
        <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 p-6 shadow-[0_12px_28px_rgba(47,62,92,0.08)]">
            @if($bitacoraLista->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#D5C7B9]/50 text-[#2F3E5C]/30">
                        <i class="ph-bold ph-clock-clockwise text-3xl"></i>
                    </div>
                    <p class="mt-4 text-sm font-black text-[#2F3E5C]/50">Sin registros de trazabilidad disponibles.</p>
                    <p class="mt-1 text-xs font-bold text-[#2F3E5C]/35">No existen registros de auditoría o bitácora institucional para este adulto mayor.</p>
                </div>
            @else
                <div class="relative space-y-4">
                    {{-- Línea vertical --}}
                    <div class="absolute left-5 top-2 hidden h-[calc(100%-16px)] w-px bg-[#CBBBAA] sm:block"></div>

                    @foreach($bitacoraLista as $log)
                        @php
                            $logArr        = is_array($log) ? $log : (array) $log;
                            $logId         = $logArr['id'] ?? 'log_' . $loop->index;
                            $evento        = $logArr['evento'] ?? 'updated';
                            $etiqueta      = $logArr['etiqueta'] ?? 'Evento';
                            $descripcion   = $logArr['descripcion'] ?? '';
                            $modulo        = $logArr['modulo'] ?? 'General';
                            $causer        = $logArr['causer'] ?? 'Sistema';
                            $fechaRelativa = $logArr['fecha_relativa'] ?? '-';
                            $fechaExacta   = $logArr['fecha_exacta'] ?? '-';
                            $icono         = $logArr['icono'] ?? 'ph-bold ph-clock';
                            $colorBg       = $logArr['color_bg'] ?? 'bg-[#6873A6]/12';
                            $colorText     = $logArr['color_text'] ?? 'text-[#566189]';
                            $colorBadge    = $logArr['color_badge'] ?? 'bg-[#566189]/10 text-[#566189]';
                            $properties    = $logArr['properties'] ?? [];
                        @endphp

                        <article
                            class="relative rounded-[20px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 sm:ml-10 transition hover:shadow-[0_4px_14px_rgba(47,62,92,0.08)]"
                            x-show="
                                (filtroEvento === 'todos' || filtroEvento === '{{ $evento }}') &&
                                (buscarBitacora === '' || {{ json_encode(strtolower(strip_tags($descripcion . ' ' . $etiqueta . ' ' . $causer . ' ' . $modulo))) }}.includes(buscarBitacora.toLowerCase()))
                            "
                            x-transition
                        >
                            {{-- Ícono lateral --}}
                            <div class="absolute -left-[38px] top-4 hidden h-10 w-10 items-center justify-center rounded-2xl {{ $colorBg }} {{ $colorText }} sm:flex shadow-2xs">
                                <i class="{{ $icono }}"></i>
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div class="flex-1 min-w-0">
                                    {{-- Badges --}}
                                    <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
                                        <span class="rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $colorBadge }}">
                                            {{ $etiqueta }}
                                        </span>
                                        <span class="rounded-full bg-[#2F3E5C]/10 px-2 py-0.5 text-[9px] font-black text-[#2F3E5C]/60 uppercase">
                                            {{ $modulo }}
                                        </span>
                                    </div>

                                    {{-- Descripción --}}
                                    <p class="text-xs font-bold leading-relaxed text-[#2F3E5C]">
                                        {{ $descripcion ?: 'Sin descripción.' }}
                                    </p>

                                    {{-- Meta --}}
                                    <p class="mt-2 text-[10px] font-bold text-[#2F3E5C]/45 flex items-center gap-3">
                                        <span class="inline-flex items-center">
                                            <i class="ph-bold ph-user-circle mr-0.5 text-xs"></i> {{ $causer }}
                                        </span>
                                        <span class="inline-flex items-center">
                                            <i class="ph-bold ph-clock mr-0.5 text-xs"></i>
                                            <span title="{{ $fechaExacta }}">{{ $fechaRelativa }}</span>
                                        </span>
                                    </p>
                                </div>

                                {{-- Botón detalles --}}
                                @if(!empty($properties))
                                    <button
                                        type="button"
                                        @click="toggleDetalle('{{ $logId }}')"
                                        class="shrink-0 rounded-lg bg-[#D5C7B9]/60 px-2.5 py-1 text-[9px] font-black text-[#2F3E5C]/60 hover:bg-[#C7B5A3] transition active:scale-95 flex items-center gap-1"
                                    >
                                        <i class="ph-bold ph-caret-down transition-transform" :class="mostrarDetalles['{{ $logId }}'] ? 'rotate-180' : ''"></i>
                                        <span>Detalles</span>
                                    </button>
                                @endif
                            </div>

                            {{-- Panel de detalles expandible --}}
                            @if(!empty($properties))
                                <div
                                    x-cloak
                                    x-show="mostrarDetalles['{{ $logId }}']"
                                    x-collapse
                                    class="mt-3 rounded-xl border border-[#C7B5A3]/50 bg-white/40 p-3 shadow-inner"
                                >
                                    <p class="mb-2 text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/40">
                                        Datos del evento
                                    </p>
                                    <dl class="grid gap-2 grid-cols-1 sm:grid-cols-2 text-xs font-bold text-[#2F3E5C]/75">
                                        @foreach($properties as $key => $value)
                                            @if(!is_array($value) && !is_null($value) && $value !== '')
                                                <div class="flex gap-2 border-b border-[#D5C7B9]/20 pb-1">
                                                    <dt class="text-[#2F3E5C]/45 capitalize min-w-[100px]">{{ str_replace('_', ' ', $key) }}:</dt>
                                                    <dd class="text-[#2F3E5C] truncate">{{ is_bool($value) ? ($value ? 'Sí' : 'No') : $value }}</dd>
                                                </div>
                                            @endif
                                        @endforeach
                                    </dl>
                                </div>
                            @endif
                        </article>
                    @endforeach

                    {{-- Mensaje cuando no hay resultados del filtro --}}
                    <p
                        x-show="!document.querySelector('article[x-show]:not([style*=\'display: none\'])')"
                        class="py-8 text-center text-xs font-bold text-[#2F3E5C]/40"
                        style="display: none;"
                    >
                        No hay registros que coincidan con el filtro seleccionado.
                    </p>
                </div>

                {{-- Nota de capacidad --}}
                @if($bitacoraLista->count() >= 60)
                    <p class="mt-4 text-center text-[10px] font-bold text-[#2F3E5C]/40">
                        <i class="ph-bold ph-info mr-0.5"></i>
                        Mostrando los 60 registros más recientes. El historial completo está preservado en la base de datos.
                    </p>
                @endif
            @endif
        </section>
    </div>
</section>