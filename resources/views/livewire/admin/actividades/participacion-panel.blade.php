@php
    $inputCls = 'w-full rounded-xl border border-borde-suave bg-fondo-app px-3 py-2.5 text-sm font-bold text-titulo outline-none ring-[#E27D60]/25 transition focus:border-borde-focus focus:ring-2';
    $labelCls = 'block text-[10px] font-black uppercase tracking-[0.15em] text-apoyo mb-1.5';
    $errCls   = 'mt-1 text-[10px] font-bold text-boton-acento';
@endphp

<div class="min-h-screen bg-fondo-panel px-4 py-5 text-titulo sm:px-6 lg:px-8"
     x-data
     @keydown.window.escape="$wire.cerrarModales()">
    <div class="mx-auto max-w-7xl space-y-6">

        {{-- ── CABECERA ─────────────────────────────────────────────────────── --}}
        <section class="overflow-hidden rounded-[1.65rem] border border-borde-suave bg-fondo-panel shadow-[0_20px_58px_rgba(47,62,92,0.13)] backdrop-blur-xl">
            <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
            <div class="flex flex-col gap-4 p-5 sm:p-7 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-borde-focus bg-estado-peligroBg px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-boton-acento">
                        <i class="ph-bold ph-users-four text-sm"></i>
                        CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS — Participación
                    </span>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-titulo sm:text-4xl">
                        Participación en actividades
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-apoyo">
                        Consulta y gestión institucional de adultos mayores vinculados a actividades programadas.
                    </p>
                </div>
                <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center">
                    <a href="{{ route('admin.actividades.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-app px-4 py-2.5 text-xs font-black text-apoyo transition hover:border-borde-fuerte hover:text-titulo">
                        <i class="ph-bold ph-calendar-check text-sm"></i>
                        Ver actividades
                    </a>
                    <button wire:click="$refresh"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-app px-4 py-2.5 text-xs font-black text-apoyo transition hover:border-borde-fuerte hover:text-titulo">
                        <i class="ph-bold ph-arrows-clockwise text-sm" wire:loading.class="animate-spin" wire:target="$refresh"></i>
                        Actualizar
                    </button>
                    @can('actividades.crear')
                    <button wire:click="abrirRegistrar"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 py-2.5 text-xs font-black uppercase tracking-[0.12em] text-inverso shadow-sm transition hover:bg-fondo-panel hover:shadow-md active:scale-95">
                        <i class="ph-bold ph-plus text-sm"></i>
                        Registrar participación
                    </button>
                    @endcan
                </div>
            </div>
        </section>

        {{-- ── 8 MÉTRICAS ──────────────────────────────────────────────────── --}}
        @php
            $metricas = [
                ['label' => 'Participaciones',      'valor' => $stats['total'],            'icono' => 'ph-clipboard-text',  'tono' => 'azul'],
                ['label' => 'Adultos participantes', 'valor' => $stats['adultos_distintos'],'icono' => 'ph-user-check',      'tono' => 'verde'],
                ['label' => 'Tipos utilizados',      'valor' => $stats['tipos_distintos'],  'icono' => 'ph-tag',             'tono' => 'dorado'],
                ['label' => 'Programadas',           'valor' => $stats['programadas'],      'icono' => 'ph-clock',           'tono' => 'dorado'],
                ['label' => 'Realizadas',            'valor' => $stats['realizadas'],       'icono' => 'ph-check-circle',    'tono' => 'verde'],
                ['label' => 'Canceladas',            'valor' => $stats['canceladas'],       'icono' => 'ph-x-circle',        'tono' => 'terracota'],
                ['label' => 'Hoy',                   'valor' => $stats['hoy'],              'icono' => 'ph-sun',             'tono' => 'dorado'],
                ['label' => 'Próximas programadas',  'valor' => $stats['proximas'],         'icono' => 'ph-calendar-dots',   'tono' => 'violeta'],
            ];
            $tonoClases = [
                'azul'     => ['icono' => 'bg-fondo-panel text-titulo',  'valor' => 'text-titulo',  'linea' => 'bg-boton-principal'],
                'verde'    => ['icono' => 'bg-estado-exitoBg text-estado-exito',  'valor' => 'text-estado-exito',  'linea' => 'bg-estado-exitoBg'],
                'terracota'=> ['icono' => 'bg-estado-peligroBg text-boton-acento',  'valor' => 'text-boton-acento',  'linea' => 'bg-boton-acento'],
                'dorado'   => ['icono' => 'bg-estado-advertenciaBg text-estado-advertencia',  'valor' => 'text-estado-advertencia',  'linea' => 'bg-estado-advertenciaBg'],
                'violeta'  => ['icono' => 'bg-fondo-panel text-parrafo',  'valor' => 'text-parrafo',  'linea' => 'bg-fondo-panel'],
            ];
        @endphp
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($metricas as $m)
                @php
                    $tono = $tonoClases[$m['tono']];
                @endphp
                <article class="relative min-h-[110px] overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm backdrop-blur-xl transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_16px_34px_rgba(47,62,92,0.11)]">
                    <div class="absolute inset-x-0 top-0 h-1 {{ $tono['linea'] }}"></div>
                    <div class="flex items-start justify-between gap-3">
                        <p class="max-w-[11rem] text-[10px] font-black uppercase leading-snug tracking-[0.13em] text-apoyo">{{ $m['label'] }}</p>
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $tono['icono'] }}">
                            <i class="ph-bold {{ $m['icono'] }} text-lg"></i>
                        </span>
                    </div>
                    <p class="mt-4 text-3xl font-black leading-none {{ $tono['valor'] }}">{{ number_format($m['valor']) }}</p>
                </article>
            @endforeach
        </section>

        {{-- ── FILTROS ──────────────────────────────────────────────────────── --}}
        <section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm backdrop-blur-xl">
            <div class="border-b border-borde-suave bg-fondo-panel px-5 py-3">
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-funnel text-apoyo text-base"></i>
                    <span class="text-[10px] font-black uppercase tracking-[0.15em] text-titulo">Filtros y búsqueda</span>
                </div>
            </div>
            <div class="p-4">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-[180px] flex-1">
                        <label class="{{ $labelCls }}">Buscar adulto mayor</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-apoyo">
                                <i class="ph-bold ph-magnifying-glass text-sm"></i>
                            </span>
                            <input wire:model.live.debounce.300ms="search"
                                   type="text" placeholder="Nombre o apellido..."
                                   class="{{ $inputCls }} pl-8" />
                        </div>
                    </div>
                    <div class="min-w-[160px] flex-1">
                        <label class="{{ $labelCls }}">Tipo de actividad</label>
                        <select wire:model.live="filtroTipo" class="{{ $inputCls }}">
                            <option value="">Todos los tipos</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo->cod_tipo_act }}">{{ $tipo->tipo }}</option>
                            @endforeach
                        </select>
                    </div>
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
                    <div class="min-w-[140px]">
                        <label class="{{ $labelCls }}">Desde</label>
                        <input wire:model.live="filtroFechaDesde" type="date" class="{{ $inputCls }}" />
                    </div>
                    <div class="min-w-[140px]">
                        <label class="{{ $labelCls }}">Hasta</label>
                        <input wire:model.live="filtroFechaHasta" type="date" class="{{ $inputCls }}" />
                    </div>
                    <div>
                        <button wire:click="limpiarFiltros"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-borde-suave bg-fondo-app px-3 py-2.5 text-xs font-black text-apoyo transition hover:border-borde-focus hover:text-boton-acento">
                            <i class="ph-bold ph-x text-xs"></i>
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── TABLA PRINCIPAL ──────────────────────────────────────────────── --}}
        <section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm backdrop-blur-xl">
            <div class="border-b border-borde-suave bg-fondo-panel px-5 py-3.5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <i class="ph-bold ph-users-four text-apoyo text-lg"></i>
                        <h2 class="text-sm font-black uppercase tracking-[0.15em] text-titulo">Registro de participaciones</h2>
                    </div>
                    <span class="text-[10px] font-black text-apoyo">
                        {{ $participaciones->total() }} registro(s)
                    </span>
                </div>
            </div>
            <div class="p-5">
                @if($participaciones->isEmpty())
                    <div class="flex flex-col items-center gap-3 py-10 text-center">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-fondo-panel">
                            <i class="ph-bold ph-users-four text-2xl text-apoyo"></i>
                        </span>
                        @if($search || $filtroTipo || $filtroEstado || $filtroFechaDesde || $filtroFechaHasta)
                            <p class="text-sm font-bold text-apoyo">No se encontraron participaciones con los filtros seleccionados.</p>
                        @else
                            <p class="text-sm font-bold text-apoyo">No hay participaciones registradas.</p>
                            <p class="max-w-sm text-xs font-bold text-apoyo">Registre la primera participación usando el botón "Registrar participación".</p>
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto"
                         wire:loading.class="opacity-50 transition-opacity"
                         wire:target="search,filtroTipo,filtroEstado,filtroFechaDesde,filtroFechaHasta">
                        <table class="w-full min-w-[800px] text-xs">
                            <thead>
                                <tr class="border-b border-borde-suave">
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Adulto mayor</th>
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Tipo actividad</th>
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Fecha</th>
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Hora</th>
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Estado</th>
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Observación</th>
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Actualización</th>
                                    <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#C7B5A3]/25">
                                @foreach($participaciones as $p)
                                    @php
                                        $ne = \App\Models\ActividadAdulto::normalizarEstado($p->estado ?? '');
                                    @endphp
                                    <tr wire:key="part-{{ $p->cod_act_adul }}" class="group transition hover:bg-fondo-panel">
                                        <td class="py-3 pr-4">
                                            <div class="min-w-0">
                                                <p class="font-black text-titulo">
                                                    {{ optional($p->adultoMayor)->ap_paterno ?? '—' }}
                                                    {{ optional($p->adultoMayor)->nombres ?? '' }}
                                                </p>
                                                @if($p->adultoMayor)
                                                    <p class="text-[9px] font-bold text-apoyo">{{ $p->cod_am }}</p>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3 pr-4 text-apoyo">
                                            {{ optional($p->tipoActividad)->tipo ?? '—' }}
                                        </td>
                                        <td class="py-3 pr-4 text-apoyo whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($p->fecha)->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 pr-4 text-apoyo">
                                            {{ $p->hora ? substr($p->hora, 0, 5) : '—' }}
                                        </td>
                                        <td class="py-3 pr-4">
                                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[9px] font-black uppercase tracking-wide {{ $ne['clase'] }}">
                                                {{ $ne['etiqueta'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 pr-4 max-w-[140px] truncate text-apoyo">
                                            {{ $p->obs ? mb_substr($p->obs, 0, 40) . (mb_strlen($p->obs) > 40 ? '…' : '') : '—' }}
                                        </td>
                                        <td class="py-3 pr-4 text-apoyo whitespace-nowrap">
                                            {{ $p->updated_at ? $p->updated_at->format('d/m/Y') : '—' }}
                                        </td>
                                        <td class="py-3">
                                            <div class="flex items-center gap-1.5">
                                                {{-- Ver detalle --}}
                                                <button wire:click="abrirDetalle({{ $p->cod_act_adul }})"
                                                        title="Ver detalle"
                                                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-borde-fuerte bg-fondo-panel text-apoyo transition hover:border-borde-fuerte hover:bg-fondo-panel">
                                                    <i class="ph-bold ph-eye text-xs"></i>
                                                </button>
                                                {{-- Editar --}}
                                                @can('actividades.editar')
                                                @if(strtoupper($p->estado) !== 'CANCELADA')
                                                <button wire:click="abrirEditar({{ $p->cod_act_adul }})"
                                                        title="Editar participación"
                                                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia transition hover:border-estado-advertenciaBorde hover:bg-estado-advertenciaBg">
                                                    <i class="ph-bold ph-pencil text-xs"></i>
                                                </button>
                                                @endif
                                                @endcan
                                                {{-- Cancelar --}}
                                                @can('actividades.anular')
                                                @if(strtoupper($p->estado) !== 'CANCELADA')
                                                <button type="button"
                                                        title="Cancelar participación"
                                                        x-data
                                                        @click="
                                                            window.SwalAmandita.fire({
                                                                title: '¿Cancelar participación?',
                                                                text: 'La participación no será eliminada. Se conservará como registro institucional.',
                                                                icon: 'warning',
                                                                showCancelButton: true,
                                                                confirmButtonText: 'Sí, cancelar',
                                                                cancelButtonText: 'No'
                                                            }).then(r => { if (r.isConfirmed) $wire.cancelarParticipacion({{ $p->cod_act_adul }}) })
                                                        "
                                                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-borde-focus bg-estado-peligroBg text-boton-acento transition hover:border-borde-focus hover:bg-estado-peligroBg">
                                                    <i class="ph-bold ph-x-circle text-xs"></i>
                                                </button>
                                                @endif
                                                @endcan
                                                {{-- Ir a ficha --}}
                                                @if($p->adultoMayor)
                                                <a href="{{ route('admin.adultos-mayores.show', ['adulto_mayor' => $p->cod_am]) }}"
                                                   title="Ver ficha del adulto mayor"
                                                   class="flex h-7 w-7 items-center justify-center rounded-lg border border-estado-exitoBorde bg-estado-exitoBg text-estado-exito transition hover:border-estado-exitoBorde hover:bg-estado-exitoBg">
                                                    <i class="ph-bold ph-arrow-square-out text-xs"></i>
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($participaciones->hasPages())
                        <div class="mt-5 border-t border-borde-suave pt-4">
                            {{ $participaciones->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </section>

        {{-- ── PARTICIPACIÓN POR TIPO (ANALÍTICA) ──────────────────────────── --}}
        @if($participacionPorTipo->isNotEmpty())
        <section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm backdrop-blur-xl">
            <div class="border-b border-borde-suave bg-fondo-panel px-5 py-3.5">
                <div class="flex items-center gap-2.5">
                    <i class="ph-bold ph-chart-bar text-boton-acento text-lg"></i>
                    <h2 class="text-sm font-black uppercase tracking-[0.15em] text-titulo">Distribución por tipo de actividad</h2>
                </div>
            </div>
            <div class="p-5 space-y-3">
                @foreach($participacionPorTipo as $fila)
                    @php
                        $pct = $maximo > 0 ? round(($fila->total / $maximo) * 100) : 0;
                    @endphp
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between gap-4">
                            <span class="min-w-0 truncate text-xs font-black text-titulo">{{ $fila->tipo ?? 'Sin tipo asignado' }}</span>
                            <span class="shrink-0 text-xs font-black text-apoyo">{{ number_format($fila->total) }}</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-fondo-panel">
                            <div class="h-2 rounded-full bg-gradient-to-r from-[#E27D60] to-[#D9A05B] transition-all duration-700"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- ── NOTA INSTITUCIONAL ───────────────────────────────────────────── --}}
        <div class="flex items-start gap-3 rounded-2xl border border-borde-suave bg-fondo-panel p-4">
            <i class="ph-bold ph-info mt-0.5 shrink-0 text-lg text-apoyo"></i>
            <p class="text-xs font-bold leading-relaxed text-apoyo">
                La participación institucional usa <strong>actividades_adulto</strong> como base de datos. Cada registro representa la vinculación de un adulto mayor a un tipo de actividad.
                Para una gestión avanzada de estados (confirmado, invitado, en espera), se recomienda crear una tabla <em>participaciones_actividades</em> en una fase futura.
            </p>
        </div>

    </div>

    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL — REGISTRAR PARTICIPACIÓN                                         --}}
    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    @if($modalRegistrar)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8"
             style="background: rgba(47,62,92,0.50)"
             wire:click.self="cerrarModales">
            <div class="w-full max-w-xl overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl">
                <div class="h-1 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
                <div class="flex items-center justify-between border-b border-borde-suave bg-fondo-panel px-5 py-4">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-peligroBg text-boton-acento">
                            <i class="ph-bold ph-user-plus text-lg"></i>
                        </span>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-apoyo">Participación</p>
                            <h3 class="text-sm font-black text-titulo">Registrar participación</h3>
                        </div>
                    </div>
                    <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave text-apoyo transition hover:border-borde-focus hover:text-boton-acento">
                        <i class="ph-bold ph-x text-sm"></i>
                    </button>
                </div>
                <form wire:submit.prevent="guardarParticipacion" class="p-5 space-y-4">
                    <div>
                        <label class="{{ $labelCls }}">Adulto mayor <span class="text-boton-acento">*</span></label>
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
                    <div>
                        <label class="{{ $labelCls }}">Tipo de actividad <span class="text-boton-acento">*</span></label>
                        <select wire:model="codTipoAct" class="{{ $inputCls }}">
                            <option value="">Seleccione un tipo...</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo->cod_tipo_act }}">{{ $tipo->tipo }}</option>
                            @endforeach
                        </select>
                        @error('codTipoAct') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $labelCls }}">Fecha <span class="text-boton-acento">*</span></label>
                            <input wire:model="fecha" type="date" class="{{ $inputCls }}" />
                            @error('fecha') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Hora <span class="text-boton-acento">*</span></label>
                            <input wire:model="hora" type="time" class="{{ $inputCls }}" />
                            @error('hora') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Estado <span class="text-boton-acento">*</span></label>
                        <select wire:model="estado" class="{{ $inputCls }}">
                            <option value="PROGRAMADA">Programada</option>
                            <option value="REALIZADA">Realizada</option>
                            <option value="COMPLETADA">Completada</option>
                            <option value="REPROGRAMADA">Reprogramada</option>
                            <option value="CANCELADA">Cancelada</option>
                        </select>
                        @error('estado') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Observaciones <span class="text-apoyo normal-case tracking-normal">(opcional)</span></label>
                        <textarea wire:model="obs" rows="3" maxlength="2000"
                                  placeholder="Notas sobre la participación..."
                                  class="{{ $inputCls }} resize-none"></textarea>
                        @error('obs') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    <p class="text-[10px] font-bold text-apoyo leading-relaxed">
                        El sistema validará que no exista una participación duplicada del mismo adulto mayor, tipo, fecha y hora.
                    </p>
                    <div class="flex justify-end gap-2.5 border-t border-borde-suave pt-4">
                        <button type="button" wire:click="cerrarModales"
                                class="rounded-xl border border-borde-suave bg-fondo-card px-4 py-2 text-xs font-black text-apoyo transition hover:border-borde-suave hover:text-titulo">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-5 py-2 text-xs font-black text-inverso shadow-sm transition hover:bg-fondo-panel active:scale-95">
                            <i class="ph-bold ph-floppy-disk text-sm"></i>
                            Registrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL — EDITAR PARTICIPACIÓN                                             --}}
    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    @if($modalEditar)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8"
             style="background: rgba(47,62,92,0.50)"
             wire:click.self="cerrarModales">
            <div class="w-full max-w-xl overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl">
                <div class="h-1 bg-gradient-to-r from-[#D9A05B] via-[#E27D60] to-[#8DA280]"></div>
                <div class="flex items-center justify-between border-b border-borde-suave bg-fondo-panel px-5 py-4">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-advertenciaBg text-estado-advertencia">
                            <i class="ph-bold ph-pencil-simple text-lg"></i>
                        </span>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-apoyo">Participación</p>
                            <h3 class="text-sm font-black text-titulo">Editar participación #{{ $editandoId }}</h3>
                        </div>
                    </div>
                    <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave text-apoyo transition hover:border-borde-focus hover:text-boton-acento">
                        <i class="ph-bold ph-x text-sm"></i>
                    </button>
                </div>
                <form wire:submit.prevent="actualizarParticipacion" class="p-5 space-y-4">
                    <div>
                        <label class="{{ $labelCls }}">Adulto mayor <span class="text-boton-acento">*</span></label>
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
                    <div>
                        <label class="{{ $labelCls }}">Tipo de actividad <span class="text-boton-acento">*</span></label>
                        <select wire:model="codTipoAct" class="{{ $inputCls }}">
                            <option value="">Seleccione un tipo...</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo->cod_tipo_act }}">{{ $tipo->tipo }}</option>
                            @endforeach
                        </select>
                        @error('codTipoAct') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $labelCls }}">Fecha <span class="text-boton-acento">*</span></label>
                            <input wire:model="fecha" type="date" class="{{ $inputCls }}" />
                            @error('fecha') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Hora <span class="text-boton-acento">*</span></label>
                            <input wire:model="hora" type="time" class="{{ $inputCls }}" />
                            @error('hora') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Estado <span class="text-boton-acento">*</span></label>
                        <select wire:model="estado" class="{{ $inputCls }}">
                            <option value="PROGRAMADA">Programada</option>
                            <option value="REALIZADA">Realizada</option>
                            <option value="COMPLETADA">Completada</option>
                            <option value="REPROGRAMADA">Reprogramada</option>
                            <option value="CANCELADA">Cancelada</option>
                        </select>
                        @error('estado') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Observaciones <span class="text-apoyo normal-case tracking-normal">(opcional)</span></label>
                        <textarea wire:model="obs" rows="3" maxlength="2000"
                                  class="{{ $inputCls }} resize-none"></textarea>
                        @error('obs') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-2.5 border-t border-borde-suave pt-4">
                        <button type="button" wire:click="cerrarModales"
                                class="rounded-xl border border-borde-suave bg-fondo-card px-4 py-2 text-xs font-black text-apoyo transition hover:border-borde-suave hover:text-titulo">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-estado-advertenciaBg px-5 py-2 text-xs font-black text-inverso shadow-sm transition hover:bg-fondo-panel active:scale-95">
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
            $ne    = \App\Models\ActividadAdulto::normalizarEstado($detalle->estado ?? '');
            $am    = $detalle->adultoMayor;
            $edad  = $am?->fecha_nac ? \Carbon\Carbon::parse($am->fecha_nac)->age : null;
        @endphp
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8"
             style="background: rgba(47,62,92,0.50)"
             wire:click.self="cerrarModales">
            <div class="w-full max-w-xl overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl">
                <div class="h-1 bg-gradient-to-r from-[#8DA280] via-[#D9A05B] to-[#E27D60]"></div>
                <div class="flex items-center justify-between border-b border-borde-suave bg-fondo-panel px-5 py-4">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                            <i class="ph-bold ph-users-four text-lg"></i>
                        </span>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-apoyo">Participación</p>
                            <h3 class="text-sm font-black text-titulo">Detalle #{{ $detalle->cod_act_adul }}</h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-wide {{ $ne['clase'] }}">
                            {{ $ne['etiqueta'] }}
                        </span>
                        <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave text-apoyo transition hover:border-borde-focus hover:text-boton-acento">
                            <i class="ph-bold ph-x text-sm"></i>
                        </button>
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    {{-- Adulto Mayor --}}
                    <div class="rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-[0.13em] text-apoyo">Adulto mayor</p>
                        <p class="mt-1 text-sm font-black text-titulo">
                            {{ $am?->ap_paterno ?? '—' }}
                            {{ $am?->ap_materno ?? '' }}
                            {{ $am?->nombres ?? '' }}
                        </p>
                        <div class="mt-1 flex items-center gap-3 text-[10px] font-bold text-apoyo">
                            <span>{{ $detalle->cod_am }}</span>
                            @if($edad !== null)
                                <span>·</span>
                                <span>{{ $edad }} años</span>
                            @endif
                        </div>
                    </div>
                    {{-- Actividad --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.13em] text-apoyo">Tipo de actividad</p>
                            <p class="mt-1 text-sm font-bold text-titulo">
                                {{ optional($detalle->tipoActividad)->tipo ?? '—' }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.13em] text-apoyo">Fecha y hora</p>
                            <p class="mt-1 text-sm font-bold text-titulo">
                                {{ \Carbon\Carbon::parse($detalle->fecha)->format('d/m/Y') }}
                            </p>
                            @if($detalle->hora)
                                <p class="text-xs font-bold text-apoyo">{{ substr($detalle->hora, 0, 5) }} hrs.</p>
                            @endif
                        </div>
                    </div>
                    {{-- Observación --}}
                    <div class="rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-[0.13em] text-apoyo">Observaciones</p>
                        <p class="mt-1 text-xs font-bold leading-relaxed text-apoyo">
                            {{ $detalle->obs ?: 'Sin observaciones registradas.' }}
                        </p>
                    </div>
                    {{-- Timestamps --}}
                    @if($detalle->created_at || $detalle->updated_at)
                        <div class="flex flex-wrap gap-4 text-[10px] font-bold text-apoyo">
                            @if($detalle->created_at)
                                <span>Registrado: {{ $detalle->created_at->format('d/m/Y H:i') }}</span>
                            @endif
                            @if($detalle->updated_at && $detalle->created_at && $detalle->updated_at->ne($detalle->created_at))
                                <span>Editado: {{ $detalle->updated_at->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                    @endif
                    {{-- Botones --}}
                    <div class="flex flex-wrap justify-end gap-2.5 border-t border-borde-suave pt-4">
                        @if($am)
                        <a href="{{ route('admin.adultos-mayores.show', ['adulto_mayor' => $detalle->cod_am]) }}"
                           class="inline-flex items-center gap-1.5 rounded-xl border border-estado-exitoBorde bg-estado-exitoBg px-4 py-2 text-xs font-black text-estado-exito transition hover:bg-estado-exitoBg">
                            <i class="ph-bold ph-user text-xs"></i>
                            Ver ficha
                        </a>
                        @endif
                        @can('actividades.editar')
                        @if(strtoupper($detalle->estado) !== 'CANCELADA')
                        <button type="button" wire:click="abrirEditar({{ $detalle->cod_act_adul }})"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg px-4 py-2 text-xs font-black text-estado-advertencia transition hover:bg-estado-advertenciaBg">
                            <i class="ph-bold ph-pencil text-xs"></i>
                            Editar
                        </button>
                        @endif
                        @endcan
                        <button type="button" wire:click="cerrarModales"
                                class="rounded-xl border border-borde-suave bg-fondo-card px-4 py-2 text-xs font-black text-apoyo transition hover:border-borde-suave hover:text-titulo">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
