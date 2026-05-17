{{-- TAB HISTORIAL — Bitácora Institucional Real --}}

<section
    x-show="tab === 'historial'"
    x-transition.opacity.duration.250ms
    x-data="{
        filtroEvento: 'todos',
        buscarBitacora: '',
        mostrarDetalles: {},
        toggleDetalle(id) { this.mostrarDetalles[id] = !this.mostrarDetalles[id]; }
    }"
    class="space-y-4"
>
    {{-- PHP: preparar bitácora defensivamente --}}
    @php
        $bitacoraLista = isset($bitacora) && is_iterable($bitacora) ? collect($bitacora) : collect();
        $eventosFiltroLista = isset($eventosFiltro) && is_iterable($eventosFiltro) ? collect($eventosFiltro) : collect();
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
                        Bitácora del expediente
                    </h2>
                    <p class="mt-1 text-xs font-bold leading-5 text-[#2F3E5C]/55">
                        Registro cronológico de todas las acciones sobre la ficha, observaciones, atenciones, actividades y documentos.
                    </p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <span class="rounded-full border border-[#C7B5A3] bg-[#D5C7B9]/50 px-3 py-1 text-[10px] font-black text-[#2F3E5C]/60">
                        {{ $bitacoraLista->count() }} {{ $bitacoraLista->count() === 1 ? 'registro' : 'registros' }}
                    </span>
                    <span class="rounded-full border border-[#8DA280]/30 bg-[#8DA280]/10 px-3 py-1 text-[10px] font-black text-[#617453]">
                        Activa
                    </span>
                </div>
            </div>
        </div>

        {{-- Indicadores de resumen --}}
        <div class="grid gap-3 p-5 sm:grid-cols-2 xl:grid-cols-5">
            @foreach([
                ['label' => 'Familiares',    'count' => $totalFamiliares,    'color' => 'text-[#617453]'],
                ['label' => 'Observaciones', 'count' => $totalObservaciones, 'color' => 'text-[#566189]'],
                ['label' => 'Atenciones',    'count' => $totalAtenciones,    'color' => 'text-[#7A5C49]'],
                ['label' => 'Actividades',   'count' => $totalActividades,   'color' => 'text-[#9B6D4C]'],
                ['label' => 'Documentos',    'count' => $totalDocumentos,    'color' => 'text-[#2F3E5C]'],
            ] as $ind)
                <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">{{ $ind['label'] }}</p>
                    <p class="mt-2 text-2xl font-black {{ $ind['color'] }}">{{ $ind['count'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Filtros --}}
        <div class="flex flex-wrap items-center gap-2 border-t border-[#D5C7B9] px-5 py-3">
            <span class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/45 mr-1">Filtrar:</span>

            <button
                type="button"
                @click="filtroEvento = 'todos'"
                :class="filtroEvento === 'todos' ? 'bg-[#2F3E5C] text-white' : 'bg-[#D5C7B9]/70 text-[#2F3E5C]/60 hover:bg-[#C7B5A3]'"
                class="rounded-full px-3 py-1 text-[10px] font-black transition"
            >
                Todos
            </button>

            @foreach($eventosFiltroLista as $ev)
                <button
                    type="button"
                    @click="filtroEvento = '{{ $ev['valor'] }}'"
                    :class="filtroEvento === '{{ $ev['valor'] }}' ? 'bg-[#2F3E5C] text-white' : 'bg-[#D5C7B9]/70 text-[#2F3E5C]/60 hover:bg-[#C7B5A3]'"
                    class="rounded-full px-3 py-1 text-[10px] font-black transition"
                >
                    {{ $ev['etiqueta'] }}
                </button>
            @endforeach

            {{-- Buscador --}}
            <div class="ml-auto flex items-center gap-2 rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/60 px-3 py-1.5">
                <i class="ph-bold ph-magnifying-glass text-xs text-[#2F3E5C]/45"></i>
                <input
                    type="text"
                    x-model="buscarBitacora"
                    placeholder="Buscar en bitácora..."
                    class="bg-transparent text-xs font-bold text-[#2F3E5C] outline-none placeholder:text-[#2F3E5C]/35 w-40"
                >
            </div>
        </div>
    </section>

    {{-- Línea de tiempo real --}}
    <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 p-5 shadow-[0_12px_28px_rgba(47,62,92,0.08)]">

        @if($bitacoraLista->isEmpty())
            {{-- Estado vacío --}}
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#D5C7B9]/50 text-[#2F3E5C]/30">
                    <i class="ph-bold ph-clock-clockwise text-3xl"></i>
                </div>
                <p class="mt-4 text-sm font-black text-[#2F3E5C]/50">Sin eventos registrados aún</p>
                <p class="mt-1 text-xs font-bold text-[#2F3E5C]/35">
                    Los eventos aparecerán aquí a medida que se registren observaciones, atenciones y actualizaciones.
                </p>
            </div>
        @else
            <div class="relative space-y-3">
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
                    >
                        {{-- Ícono lateral --}}
                        <div class="absolute -left-[38px] top-4 hidden h-10 w-10 items-center justify-center rounded-2xl {{ $colorBg }} {{ $colorText }} sm:flex">
                            <i class="{{ $icono }}"></i>
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex-1 min-w-0">
                                {{-- Badges --}}
                                <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-black {{ $colorBadge }}">
                                        {{ $etiqueta }}
                                    </span>
                                    <span class="rounded-full bg-[#2F3E5C]/8 px-2 py-0.5 text-[10px] font-black text-[#2F3E5C]/55">
                                        {{ $modulo }}
                                    </span>
                                </div>

                                {{-- Descripción --}}
                                <p class="text-sm font-bold leading-5 text-[#2F3E5C]">
                                    {{ $descripcion ?: 'Sin descripción.' }}
                                </p>

                                {{-- Meta --}}
                                <p class="mt-1.5 text-[11px] font-bold text-[#2F3E5C]/45">
                                    <i class="ph-bold ph-user-circle mr-0.5"></i> {{ $causer }}
                                    <span class="mx-1.5 opacity-40">·</span>
                                    <i class="ph-bold ph-clock mr-0.5"></i>
                                    <span title="{{ $fechaExacta }}">{{ $fechaRelativa }}</span>
                                </p>
                            </div>

                            {{-- Botón detalles --}}
                            @if(!empty($properties))
                                <button
                                    type="button"
                                    @click="toggleDetalle('{{ $logId }}')"
                                    class="shrink-0 rounded-xl bg-[#D5C7B9]/60 px-2.5 py-1.5 text-[10px] font-black text-[#2F3E5C]/55 transition hover:bg-[#C7B5A3]"
                                >
                                    <i class="ph-bold ph-caret-down transition-transform" :class="mostrarDetalles['{{ $logId }}'] ? 'rotate-180' : ''"></i>
                                    Detalles
                                </button>
                            @endif
                        </div>

                        {{-- Panel de detalles expandible --}}
                        @if(!empty($properties))
                            <div
                                x-show="mostrarDetalles['{{ $logId }}']"
                                x-transition
                                class="mt-3 rounded-xl border border-[#C7B5A3]/50 bg-[#D5C7B9]/40 p-3"
                            >
                                <p class="mb-2 text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/45">
                                    Datos del evento
                                </p>
                                <dl class="space-y-1 text-xs font-bold text-[#2F3E5C]/70">
                                    @foreach($properties as $key => $value)
                                        @if(!is_array($value) && !is_null($value) && $value !== '')
                                            <div class="flex gap-2">
                                                <dt class="text-[#2F3E5C]/40 capitalize min-w-[80px]">{{ str_replace('_', ' ', $key) }}:</dt>
                                                <dd>{{ is_bool($value) ? ($value ? 'Sí' : 'No') : $value }}</dd>
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
</section>
            

        {{-- MODALES FULL SCREEN PREPARADOS --}}
        