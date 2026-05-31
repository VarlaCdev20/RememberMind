<div
    class="min-h-screen bg-fondo-panel px-4 py-5 text-titulo sm:px-6 lg:px-8"
    x-data="{
        confirmar(id, accion) {
            const opciones = {
                asistio: {
                    metodo: 'marcarAsistio',
                    title: '¿Confirmar asistencia?',
                    text: 'El registro quedará marcado como asistencia cumplida.',
                    icon: 'success',
                    confirmButtonText: 'Sí, confirmar'
                },
                noAsistio: {
                    metodo: 'marcarNoAsistio',
                    title: '¿Marcar como no asistió?',
                    text: 'La asistencia quedará registrada como ausencia y se conservará en el historial.',
                    icon: 'warning',
                    confirmButtonText: 'Sí, registrar ausencia'
                },
                justificar: {
                    metodo: 'justificarAusencia',
                    title: '¿Justificar ausencia?',
                    text: 'El registro quedará marcado como justificado.',
                    icon: 'info',
                    confirmButtonText: 'Sí, justificar'
                },
                reprogramar: {
                    metodo: 'marcarReprogramado',
                    title: '¿Marcar como reprogramado?',
                    text: 'La asistencia quedará registrada como reprogramada.',
                    icon: 'question',
                    confirmButtonText: 'Sí, reprogramar'
                }
            };

            const config = opciones[accion];
            if (!config) return;

            if (!window.SwalAmandita) {
                if (confirm(config.title)) $wire[config.metodo](id);
                return;
            }

            window.SwalAmandita.fire({
                title: config.title,
                text: config.text,
                icon: config.icon,
                showCancelButton: true,
                confirmButtonText: config.confirmButtonText,
                cancelButtonText: 'Cancelar',
                customClass: { popup: 'rounded-[1.5rem]' }
            }).then((result) => {
                if (result.isConfirmed) $wire[config.metodo](id);
            });
        }
    }"
>
    <div class="mx-auto max-w-7xl space-y-5">
        <section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-[0_16px_46px_rgba(47,62,92,0.12)] backdrop-blur-xl">
            <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
            <div class="flex flex-col gap-4 p-4 sm:p-5 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-borde-focus bg-estado-peligroBg px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-boton-acento">
                        <i class="ph-bold ph-clipboard-text text-sm"></i>
                        Voluntariado
                    </span>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-titulo sm:text-3xl">Asistencia</h1>
                    <p class="mt-1 max-w-2xl text-sm font-bold leading-relaxed text-apoyo">
                        Control de asistencia, cumplimiento, ausencias y tiempo colaborado por voluntarios.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @can('asistencia.ver')
                        <button
                            type="button"
                            wire:click="abrirCrear"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 text-[11px] font-black uppercase tracking-wider text-inverso shadow-[0_8px_18px_rgba(226,125,96,0.22)] transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95"
                        >
                            <i class="ph-bold ph-plus-circle text-sm"></i>
                            Registrar asistencia
                        </button>
                    @endcan

                    <a href="{{ $linksCabecera['asignaciones'] }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-4 text-[11px] font-black uppercase tracking-wider text-titulo shadow-sm transition hover:border-borde-focus hover:text-boton-acento active:scale-95">
                        <i class="ph-bold ph-handshake text-sm"></i>
                        Ver asignaciones
                    </a>

                    <a href="{{ $linksCabecera['resumen'] }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-4 text-[11px] font-black uppercase tracking-wider text-titulo shadow-sm transition hover:bg-fondo-panel active:scale-95">
                        <i class="ph-bold ph-arrow-left text-sm"></i>
                        Volver al resumen
                    </a>
                </div>
            </div>
        </section>

        @php
            $tonoClases = [
                'azul' => ['icono' => 'bg-fondo-panel text-titulo', 'valor' => 'text-titulo', 'linea' => 'bg-boton-principal'],
                'verde' => ['icono' => 'bg-estado-exitoBg text-estado-exito', 'valor' => 'text-estado-exito', 'linea' => 'bg-estado-exitoBg'],
                'terracota' => ['icono' => 'bg-estado-peligroBg text-boton-acento', 'valor' => 'text-boton-acento', 'linea' => 'bg-boton-acento'],
                'dorado' => ['icono' => 'bg-estado-advertenciaBg text-estado-advertencia', 'valor' => 'text-estado-advertencia', 'linea' => 'bg-estado-advertenciaBg'],
            ];
            $estadoClases = [
                'Pendiente' => 'bg-estado-advertenciaBg text-estado-advertencia border-estado-advertenciaBorde',
                'Asistió' => 'bg-estado-exitoBg text-estado-exito border-estado-exitoBorde',
                'No asistió' => 'bg-estado-peligroBg text-boton-acento border-borde-focus',
                'Tarde' => 'bg-estado-advertenciaBg text-estado-advertencia border-estado-advertenciaBorde',
                'Justificado' => 'bg-fondo-panel text-titulo border-borde-fuerte',
                'Cancelado' => 'bg-fondo-panel text-meta border-borde-suave',
                'Reprogramado' => 'bg-fondo-panel text-titulo border-borde-fuerte',
            ];
            $asignacionClases = [
                'Programada' => 'bg-fondo-panel text-titulo border-borde-fuerte',
                'Confirmada' => 'bg-estado-exitoBg text-estado-exito border-estado-exitoBorde',
                'En curso' => 'bg-estado-advertenciaBg text-estado-advertencia border-estado-advertenciaBorde',
                'Cumplida' => 'bg-estado-exitoBg text-parrafo border-estado-exitoBorde',
                'Cancelada' => 'bg-estado-peligroBg text-boton-acento border-borde-focus',
                'Reprogramada' => 'bg-fondo-panel text-titulo border-borde-fuerte',
            ];
        @endphp

        <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 2xl:grid-cols-8">
            @foreach($metricas as $metrica)
                @php
                    $tono = $tonoClases[$metrica['tono']] ?? $tonoClases['azul'];
                @endphp
                <article class="relative min-h-[100px] overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel p-3.5 shadow-sm backdrop-blur-xl transition duration-300 hover:-translate-y-0.5 hover:border-borde-focus">
                    <div class="absolute inset-x-0 top-0 h-1 {{ $tono['linea'] }}"></div>
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-[9px] font-black uppercase leading-snug tracking-[0.14em] text-apoyo">{{ $metrica['label'] }}</p>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $tono['icono'] }}">
                            <i class="ph-bold {{ $metrica['icono'] }} text-base"></i>
                        </span>
                    </div>
                    <p class="mt-2 text-2xl font-black leading-none {{ $tono['valor'] }}">{{ is_numeric($metrica['valor']) ? number_format($metrica['valor']) : $metrica['valor'] }}</p>
                    <p class="mt-1 truncate text-[9px] font-bold text-apoyo">{{ $metrica['subtitulo'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="rounded-[1.35rem] border border-borde-suave bg-fondo-panel p-3.5 shadow-sm backdrop-blur-xl">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[1.35fr_0.85fr_0.85fr_0.85fr_0.9fr_0.8fr_auto_auto]">
                <label class="block">
                    <span class="mb-1 block text-[9px] font-black uppercase tracking-widest text-apoyo">Buscar voluntario</span>
                    <span class="relative block">
                        <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-apoyo"></i>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Voluntario, CI, actividad..." class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-panel pl-10 pr-3 text-xs font-bold text-titulo outline-none transition placeholder:text-apoyo focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                    </span>
                </label>

                <label class="block">
                    <span class="mb-1 block text-[9px] font-black uppercase tracking-widest text-apoyo">Fecha</span>
                    <input type="date" wire:model.live="fechaFiltro" class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                </label>

                <label class="block">
                    <span class="mb-1 block text-[9px] font-black uppercase tracking-widest text-apoyo">Desde</span>
                    <input type="date" wire:model.live="fechaDesde" class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                </label>

                <label class="block">
                    <span class="mb-1 block text-[9px] font-black uppercase tracking-widest text-apoyo">Hasta</span>
                    <input type="date" wire:model.live="fechaHasta" class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                </label>

                <label class="block">
                    <span class="mb-1 block text-[9px] font-black uppercase tracking-widest text-apoyo">Estado</span>
                    <select wire:model.live="estadoFiltro" class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                        <option value="">Todos</option>
                        @foreach($estados as $estadoOpcion)
                            <option value="{{ $estadoOpcion }}">{{ $estadoOpcion }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="mb-1 block text-[9px] font-black uppercase tracking-widest text-apoyo">Turno</span>
                    <select wire:model.live="turnoFiltro" class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                        <option value="">Todos</option>
                        @foreach($turnos as $turno)
                            <option value="{{ $turno }}">{{ $turno }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="flex items-end">
                    <button type="button" wire:click="$refresh" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-boton-principal px-4 text-[11px] font-black uppercase tracking-wider text-inverso transition hover:bg-boton-acento active:scale-95 xl:w-auto">
                        <i class="ph-bold ph-magnifying-glass"></i>
                        Buscar
                    </button>
                </div>

                <div class="flex items-end">
                    <button type="button" wire:click="limpiarFiltros" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-4 text-[11px] font-black uppercase tracking-wider text-titulo transition hover:bg-fondo-panel active:scale-95 xl:w-auto">
                        <i class="ph-bold ph-broom"></i>
                        Limpiar
                    </button>
                </div>
            </div>
        </section>

        <section class="grid gap-5 xl:grid-cols-[0.95fr_1.75fr]">
            <aside class="space-y-5">
                <div class="rounded-[1.45rem] border border-borde-suave bg-fondo-panel p-4 shadow-sm backdrop-blur-xl">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-[0.18em] text-boton-acento">Asignaciones pendientes</span>
                            <h2 class="mt-0.5 text-base font-black text-titulo">Pendientes de asistencia</h2>
                        </div>
                        <i class="ph-bold ph-clock-countdown text-2xl text-estado-advertencia"></i>
                    </div>

                    <div class="space-y-2.5">
                        @forelse($pendientes as $pendiente)
                            @php
                                $asignacionClase = $asignacionClases[$pendiente->estado_normalizado] ?? $asignacionClases['Programada'];
                            @endphp
                            <article class="rounded-2xl border border-borde-suave bg-fondo-panel p-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-black text-titulo">{{ $pendiente->voluntario_nombre ?: 'Voluntario' }}</p>
                                        <p class="mt-0.5 truncate text-[10px] font-bold text-apoyo">{{ $pendiente->adulto_nombre ?: 'Apoyo institucional' }}</p>
                                    </div>
                                    <span class="rounded-full border px-2 py-0.5 text-[8px] font-black uppercase tracking-wider {{ $asignacionClase }}">{{ $pendiente->estado_normalizado }}</span>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-1.5 text-[10px] font-black">
                                    <span class="rounded-full bg-fondo-panel px-2 py-1 text-titulo">{{ $pendiente->fecha_texto }}</span>
                                    <span class="rounded-full bg-estado-advertenciaBg px-2 py-1 text-estado-advertencia">{{ $pendiente->turno }}</span>
                                    <span class="rounded-full bg-estado-exitoBg px-2 py-1 text-estado-exito">{{ $pendiente->horario_programado }}</span>
                                </div>
                                @can('asistencia.ver')
                                    <div class="mt-3 flex justify-end">
                                        <button type="button" wire:click="abrirCrearDesdeAsignacion({{ $pendiente->cod_asig_vol }})" class="inline-flex h-8 items-center justify-center gap-1.5 rounded-xl bg-boton-principal px-3 text-[9px] font-black uppercase tracking-wider text-inverso transition hover:bg-boton-acento">
                                            <i class="ph-bold ph-clipboard-text"></i>
                                            Registrar asistencia
                                        </button>
                                    </div>
                                @endcan
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-borde-suave bg-fondo-panel p-6 text-center">
                                <i class="ph-bold ph-check-circle text-4xl text-apoyo"></i>
                                <h3 class="mt-3 text-sm font-black text-titulo">No existen asignaciones pendientes de asistencia.</h3>
                                <p class="mt-1 text-xs font-bold text-apoyo">Las asignaciones programadas ya tienen control registrado o aún no vencen.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-[1.45rem] border border-borde-suave bg-fondo-panel p-4 shadow-sm backdrop-blur-xl">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-[0.18em] text-boton-acento">Detalle</span>
                            <h2 class="mt-0.5 text-base font-black text-titulo">Asistencia seleccionada</h2>
                        </div>
                        @if($detalleAsistencia)
                            <button type="button" wire:click="cerrarDetalle" class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave bg-fondo-panel text-titulo transition hover:bg-boton-acento hover:text-inverso">
                                <i class="ph-bold ph-x"></i>
                            </button>
                        @endif
                    </div>

                    @if($detalleAsistencia)
                        @php
                            $detalleClase = $estadoClases[$detalleAsistencia->estado_normalizado] ?? $estadoClases['Pendiente'];
                        @endphp
                        <div class="space-y-3">
                            <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-3">
                                <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Voluntario</p>
                                <p class="mt-1 text-sm font-black text-titulo">{{ $detalleAsistencia->voluntario_nombre ?: 'Voluntario' }}</p>
                                <p class="mt-0.5 text-[11px] font-bold text-apoyo">CI {{ $detalleAsistencia->numero_documento ?: 'No registrado' }}</p>
                            </div>

                            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
                                <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-3">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Asignación / apoyo</p>
                                    <p class="mt-1 text-xs font-black text-titulo">{{ $detalleAsistencia->adulto_nombre ?: 'Apoyo institucional' }}</p>
                                    <p class="mt-0.5 text-[11px] font-bold text-apoyo">{{ $detalleAsistencia->cod_asig_vol ? 'Asignación #' . $detalleAsistencia->cod_asig_vol : 'Sin vínculo directo guardado' }}</p>
                                </div>
                                <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-3">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Tiempo colaborado</p>
                                    <p class="mt-1 text-xs font-black text-titulo">{{ $detalleAsistencia->tiempo_colaborado }}</p>
                                    <p class="mt-0.5 text-[11px] font-bold text-apoyo">{{ substr((string) $detalleAsistencia->hora_entrada, 0, 5) ?: '--:--' }} a {{ substr((string) $detalleAsistencia->hora_salida, 0, 5) ?: '--:--' }}</p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-wider {{ $detalleClase }}">{{ $detalleAsistencia->estado_normalizado }}</span>
                                <span class="rounded-full bg-fondo-panel px-2.5 py-1 text-[9px] font-black uppercase tracking-wider text-titulo">{{ $detalleAsistencia->fecha_texto }}</span>
                                <span class="rounded-full bg-estado-advertenciaBg px-2.5 py-1 text-[9px] font-black uppercase tracking-wider text-estado-advertencia">{{ $detalleAsistencia->turno }}</span>
                            </div>

                            @if($detalleAsistencia->actividad_realizada || $detalleAsistencia->observaciones)
                                <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-3">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Registro</p>
                                    <p class="mt-1 text-xs font-bold leading-relaxed text-apoyo">{{ $detalleAsistencia->actividad_realizada ?: 'Sin actividad registrada.' }}</p>
                                    @if($detalleAsistencia->observaciones)
                                        <p class="mt-2 text-xs font-bold leading-relaxed text-apoyo">{{ $detalleAsistencia->observaciones }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-borde-suave bg-fondo-panel p-6 text-center">
                            <i class="ph-bold ph-sidebar-simple text-4xl text-apoyo"></i>
                            <h3 class="mt-3 text-sm font-black text-titulo">Selecciona una asistencia.</h3>
                            <p class="mt-1 text-xs font-bold text-apoyo">Aquí verás el detalle, tiempos y observaciones del registro.</p>
                        </div>
                    @endif
                </div>
            </aside>

            <div class="rounded-[1.45rem] border border-borde-suave bg-fondo-panel p-4 shadow-sm backdrop-blur-xl">
                <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-[0.18em] text-boton-acento">Listado principal</span>
                        <h2 class="mt-0.5 text-base font-black text-titulo">Asistencias registradas</h2>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-borde-suave bg-fondo-panel px-3 py-1 text-[10px] font-black uppercase tracking-wider text-apoyo">
                        <i class="ph-bold ph-list-bullets"></i>
                        {{ $registros->total() }} registros
                    </span>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-borde-suave">
                    <table class="min-w-[1060px] w-full divide-y divide-[#C7B5A3]/45 text-left">
                        <thead class="bg-fondo-panel">
                            <tr>
                                <th class="px-3 py-3 text-[10px] font-black uppercase tracking-widest text-apoyo">Voluntario</th>
                                <th class="px-3 py-3 text-[10px] font-black uppercase tracking-widest text-apoyo">Asignación / apoyo</th>
                                <th class="px-3 py-3 text-[10px] font-black uppercase tracking-widest text-apoyo">Fecha</th>
                                <th class="px-3 py-3 text-[10px] font-black uppercase tracking-widest text-apoyo">Horario programado</th>
                                <th class="px-3 py-3 text-[10px] font-black uppercase tracking-widest text-apoyo">Llegada / salida</th>
                                <th class="px-3 py-3 text-[10px] font-black uppercase tracking-widest text-apoyo">Tiempo</th>
                                <th class="px-3 py-3 text-[10px] font-black uppercase tracking-widest text-apoyo">Estado</th>
                                <th class="px-3 py-3 text-[10px] font-black uppercase tracking-widest text-apoyo">Registrado por</th>
                                <th class="px-3 py-3 text-right text-[10px] font-black uppercase tracking-widest text-apoyo">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#C7B5A3]/35 bg-fondo-panel">
                            @forelse($registros as $registro)
                                @php
                                    $estadoClase = $estadoClases[$registro->estado_normalizado] ?? $estadoClases['Pendiente'];
                                @endphp
                                <tr class="transition hover:bg-fondo-panel">
                                    <td class="px-3 py-3 align-top">
                                        <p class="max-w-[170px] truncate text-xs font-black text-titulo">{{ $registro->voluntario_nombre ?: 'Voluntario' }}</p>
                                        <p class="mt-0.5 text-[10px] font-bold text-apoyo">CI {{ $registro->numero_documento ?: 'No registrado' }}</p>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <p class="max-w-[190px] truncate text-xs font-black text-titulo">{{ $registro->adulto_nombre ?: 'Apoyo institucional' }}</p>
                                        <p class="mt-0.5 max-w-[190px] truncate text-[10px] font-bold text-apoyo">{{ $registro->actividad_realizada ?: ($registro->cod_asig_vol ? 'Asignación #' . $registro->cod_asig_vol : 'Sin asignación directa') }}</p>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <p class="text-xs font-black text-titulo">{{ $registro->fecha_texto }}</p>
                                        <p class="mt-0.5 text-[10px] font-bold text-apoyo">{{ $registro->dia_semana }}</p>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <p class="max-w-[150px] truncate text-xs font-black text-titulo">{{ $registro->horario_programado }}</p>
                                        <p class="mt-0.5 text-[10px] font-bold text-estado-advertencia">{{ $registro->turno }}</p>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <p class="text-xs font-black text-titulo">{{ $registro->hora_entrada ? substr((string) $registro->hora_entrada, 0, 5) : '--:--' }}</p>
                                        <p class="mt-0.5 text-[10px] font-bold text-apoyo">Salida {{ $registro->hora_salida ? substr((string) $registro->hora_salida, 0, 5) : '--:--' }}</p>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <p class="text-xs font-black text-titulo">{{ $registro->tiempo_colaborado }}</p>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <span class="inline-flex rounded-full border px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $estadoClase }}">{{ $registro->estado_normalizado }}</span>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <p class="text-[10px] font-black uppercase tracking-wider text-apoyo">{{ $registro->registrado_por }}</p>
                                        @if($registro->observaciones)
                                            <p class="mt-1 max-w-[160px] truncate text-[10px] font-bold text-apoyo">{{ $registro->observaciones }}</p>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <div class="flex justify-end gap-1.5">
                                            <button type="button" wire:click="verDetalle({{ $registro->cod_asis_vol }})" class="flex h-8 w-8 items-center justify-center rounded-xl bg-fondo-panel text-titulo transition hover:bg-boton-principal hover:text-inverso" title="Ver detalle">
                                                <i class="ph-bold ph-eye"></i>
                                            </button>
                                            @can('asistencia.ver')
                                                <button type="button" wire:click="editar({{ $registro->cod_asis_vol }})" class="flex h-8 w-8 items-center justify-center rounded-xl bg-estado-advertenciaBg text-estado-advertencia transition hover:bg-estado-advertenciaBg hover:text-inverso" title="Editar">
                                                    <i class="ph-bold ph-pencil-simple"></i>
                                                </button>
                                                <button type="button" @click="confirmar({{ $registro->cod_asis_vol }}, 'asistio')" class="flex h-8 w-8 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito transition hover:bg-estado-exitoBg hover:text-inverso" title="Marcar asistió">
                                                    <i class="ph-bold ph-check-circle"></i>
                                                </button>
                                                <button type="button" @click="confirmar({{ $registro->cod_asis_vol }}, 'noAsistio')" class="flex h-8 w-8 items-center justify-center rounded-xl bg-estado-peligroBg text-boton-acento transition hover:bg-boton-acento hover:text-inverso" title="Marcar no asistió">
                                                    <i class="ph-bold ph-user-minus"></i>
                                                </button>
                                                <button type="button" @click="confirmar({{ $registro->cod_asis_vol }}, 'justificar')" class="flex h-8 w-8 items-center justify-center rounded-xl bg-fondo-panel text-titulo transition hover:bg-boton-principal hover:text-inverso" title="Justificar">
                                                    <i class="ph-bold ph-note-pencil"></i>
                                                </button>
                                                <button type="button" @click="confirmar({{ $registro->cod_asis_vol }}, 'reprogramar')" class="flex h-8 w-8 items-center justify-center rounded-xl bg-estado-advertenciaBg text-estado-advertencia transition hover:bg-estado-advertenciaBg hover:text-inverso" title="Reprogramar">
                                                    <i class="ph-bold ph-arrows-clockwise"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-10">
                                        <div class="mx-auto max-w-md rounded-2xl border border-dashed border-borde-suave bg-fondo-panel p-7 text-center">
                                            <i class="ph-bold ph-clipboard-text text-4xl text-apoyo"></i>
                                            <h3 class="mt-3 text-sm font-black text-titulo">
                                                @if($fechaFiltro)
                                                    No hay asistencias registradas para la fecha seleccionada.
                                                @elseif($voluntarioFiltro)
                                                    Este voluntario aún no tiene asistencias registradas.
                                                @elseif($search || $fechaDesde || $fechaHasta || $estadoFiltro || $turnoFiltro)
                                                    No se encontraron asistencias con los filtros aplicados.
                                                @else
                                                    No hay asistencias registradas.
                                                @endif
                                            </h3>
                                            <p class="mt-1 text-xs font-bold text-apoyo">Registra la asistencia desde una asignación pendiente o el botón superior.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($registros->hasPages())
                    <div class="mt-4">
                        {{ $registros->links() }}
                    </div>
                @endif
            </div>
        </section>
    </div>

    @if($mostrarFormulario)
        <div class="fixed inset-0 z-[90] flex items-center justify-center px-4 py-6">
            <div class="absolute inset-0 bg-fondo-panel backdrop-blur-sm" wire:click="cerrarFormulario"></div>
            <section class="relative flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-[1.5rem] border border-borde-suave bg-fondo-app shadow-[0_24px_70px_rgba(47,62,92,0.28)]">
                <header class="flex items-start justify-between gap-4 border-b border-borde-suave bg-fondo-panel px-5 py-4">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-boton-acento">{{ $isEdit ? 'Editar control' : 'Nuevo control' }}</span>
                        <h2 class="mt-1 text-xl font-black text-titulo">{{ $isEdit ? 'Editar asistencia' : 'Registrar asistencia' }}</h2>
                        <p class="mt-1 text-xs font-bold text-apoyo">Registre el cumplimiento de asistencia del voluntario según su asignación programada.</p>
                    </div>
                    <button type="button" wire:click="cerrarFormulario" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-borde-suave bg-fondo-panel text-titulo transition hover:bg-boton-acento hover:text-inverso">
                        <i class="ph-bold ph-x"></i>
                    </button>
                </header>

                <div class="flex-1 overflow-y-auto p-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block sm:col-span-2">
                            <span class="mb-1.5 block text-[9px] font-black uppercase tracking-widest text-apoyo">Asignación programada</span>
                            <select wire:model.live="asignacionContexto" class="w-full rounded-xl border border-borde-suave bg-fondo-card/75 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                                <option value="">Sin asignación seleccionada</option>
                                @foreach($asignacionesFormulario as $asignacion)
                                    <option value="{{ $asignacion->cod_asig_vol }}">
                                        #{{ $asignacion->cod_asig_vol }} · {{ $asignacion->fecha_texto }} · {{ $asignacion->voluntario_nombre }} · {{ $asignacion->adulto_nombre ?: 'Apoyo institucional' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block sm:col-span-2">
                            <span class="mb-1.5 block text-[9px] font-black uppercase tracking-widest text-apoyo">Voluntario</span>
                            <select wire:model.live="cod_vol" class="w-full rounded-xl border border-borde-suave bg-fondo-card/75 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                                <option value="">Seleccionar voluntario</option>
                                @foreach($voluntariosActivos as $voluntario)
                                    <option value="{{ $voluntario->cod_vol }}">{{ $voluntario->nombre }}{{ $voluntario->numero_documento ? ' · CI ' . $voluntario->numero_documento : '' }}</option>
                                @endforeach
                            </select>
                            @error('cod_vol') <p class="mt-1 text-[10px] font-bold text-boton-acento">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="mb-1.5 block text-[9px] font-black uppercase tracking-widest text-apoyo">Fecha</span>
                            <input type="date" wire:model.live="fecha" class="w-full rounded-xl border border-borde-suave bg-fondo-card/75 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('fecha') <p class="mt-1 text-[10px] font-bold text-boton-acento">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="mb-1.5 block text-[9px] font-black uppercase tracking-widest text-apoyo">Estado</span>
                            <select wire:model.live="estado" class="w-full rounded-xl border border-borde-suave bg-fondo-card/75 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                                @foreach($estados as $estadoOpcion)
                                    <option value="{{ $estadoOpcion }}">{{ $estadoOpcion }}</option>
                                @endforeach
                            </select>
                            @error('estado') <p class="mt-1 text-[10px] font-bold text-boton-acento">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="mb-1.5 block text-[9px] font-black uppercase tracking-widest text-apoyo">Hora de llegada</span>
                            <input type="time" wire:model="hora_entrada" class="w-full rounded-xl border border-borde-suave bg-fondo-card/75 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('hora_entrada') <p class="mt-1 text-[10px] font-bold text-boton-acento">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="mb-1.5 block text-[9px] font-black uppercase tracking-widest text-apoyo">Hora de salida</span>
                            <input type="time" wire:model="hora_salida" class="w-full rounded-xl border border-borde-suave bg-fondo-card/75 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('hora_salida') <p class="mt-1 text-[10px] font-bold text-boton-acento">{{ $message }}</p> @enderror
                        </label>

                        <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-3 sm:col-span-2">
                            <span class="block text-[9px] font-black uppercase tracking-widest text-apoyo">Programación asociada</span>
                            <p class="mt-1 text-sm font-black text-titulo">{{ $programacionFormulario['horario'] }}</p>
                            <p class="mt-0.5 text-[11px] font-bold text-apoyo">{{ $programacionFormulario['turno'] }} · {{ $programacionFormulario['asignacion'] }}</p>
                        </div>

                        @if($advertenciaAsignacion)
                            <div class="sm:col-span-2 rounded-2xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-3">
                                <div class="flex items-start gap-2">
                                    <i class="ph-bold ph-warning-circle mt-0.5 text-lg text-estado-advertencia"></i>
                                    <div>
                                        <p class="text-xs font-black text-estado-advertencia">Asignación por revisar</p>
                                        <p class="mt-0.5 text-[11px] font-bold leading-relaxed text-apoyo">{{ $advertenciaAsignacion }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <label class="block sm:col-span-2">
                            <span class="mb-1.5 block text-[9px] font-black uppercase tracking-widest text-apoyo">Actividad realizada</span>
                            <textarea wire:model="actividad_realizada" rows="3" class="w-full rounded-xl border border-borde-suave bg-fondo-card/75 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15"></textarea>
                            @error('actividad_realizada') <p class="mt-1 text-[10px] font-bold text-boton-acento">{{ $message }}</p> @enderror
                        </label>

                        <label class="block sm:col-span-2">
                            <span class="mb-1.5 block text-[9px] font-black uppercase tracking-widest text-apoyo">Motivo / observación</span>
                            <textarea wire:model="observaciones" rows="3" class="w-full rounded-xl border border-borde-suave bg-fondo-card/75 px-3 py-2.5 text-sm font-bold text-titulo outline-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15"></textarea>
                            @error('observaciones') <p class="mt-1 text-[10px] font-bold text-boton-acento">{{ $message }}</p> @enderror
                        </label>
                    </div>
                </div>

                <footer class="flex flex-col-reverse gap-2 border-t border-borde-suave bg-fondo-panel px-5 py-4 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="cerrarFormulario" class="inline-flex h-10 items-center justify-center rounded-xl border border-borde-suave bg-fondo-panel px-4 text-[11px] font-black uppercase tracking-wider text-titulo transition hover:bg-fondo-panel">
                        Cancelar
                    </button>
                    <button type="button" wire:click="guardar" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-boton-principal px-5 text-[11px] font-black uppercase tracking-wider text-inverso shadow-[0_8px_18px_rgba(47,62,92,0.18)] transition hover:bg-boton-acento active:scale-95">
                        <i class="ph-bold ph-floppy-disk"></i>
                        {{ $isEdit ? 'Guardar cambios' : 'Registrar asistencia' }}
                    </button>
                </footer>
            </section>
        </div>
    @endif
</div>
