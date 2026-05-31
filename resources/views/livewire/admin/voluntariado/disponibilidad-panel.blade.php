<div
    class="min-h-screen bg-[#F8F3ED]/45 px-4 py-5 text-[#2F3E5C] sm:px-6 lg:px-8"
    x-data="{
        confirmarNoDisponible(id) {
            const titulo = '¿Marcar como no disponible?';
            const texto = 'El registro no será eliminado. Se conservará como historial operativo.';

            if (!window.SwalAmandita) {
                if (confirm(titulo)) $wire.marcarNoDisponible(id);
                return;
            }

            window.SwalAmandita.fire({
                title: titulo,
                text: texto,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, marcar',
                cancelButtonText: 'Cancelar',
                customClass: { popup: 'rounded-[1.5rem]' }
            }).then((result) => {
                if (result.isConfirmed) $wire.marcarNoDisponible(id);
            });
        },
        confirmarReactivar(id) {
            const titulo = '¿Reactivar disponibilidad?';
            const texto = 'El horario volverá a figurar como disponible para el flujo de asignaciones.';

            if (!window.SwalAmandita) {
                if (confirm(titulo)) $wire.reactivarDisponibilidad(id);
                return;
            }

            window.SwalAmandita.fire({
                title: titulo,
                text: texto,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, reactivar',
                cancelButtonText: 'Cancelar',
                customClass: { popup: 'rounded-[1.5rem]' }
            }).then((result) => {
                if (result.isConfirmed) $wire.reactivarDisponibilidad(id);
            });
        }
    }"
>
    <div class="mx-auto max-w-7xl space-y-5">
        <section class="overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/70 bg-[#E6DDD3]/72 shadow-[0_16px_46px_rgba(47,62,92,0.12)] backdrop-blur-xl">
            <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
            <div class="flex flex-col gap-4 p-4 sm:p-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#E27D60]/25 bg-[#E27D60]/10 px-3 py-1 text-xs font-black uppercase tracking-[0.15em] text-[#E27D60]">
                        <i class="ph-bold ph-calendar-dots text-sm"></i>
                        Voluntariado
                    </span>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-[#2F3E5C] sm:text-3xl">Disponibilidad</h1>
                    <p class="mt-1 max-w-2xl text-sm font-bold leading-relaxed text-[#2F3E5C]/70">
                        Planificación de días, fechas, turnos y horarios disponibles de los voluntarios.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @can('voluntarios.crear')
                        <button
                            type="button"
                            wire:click="abrirCrear"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#E27D60] px-4 text-xs font-black uppercase tracking-wide text-white shadow-[0_8px_18px_rgba(226,125,96,0.22)] transition hover:-translate-y-0.5 hover:bg-[#D96F58] active:scale-95"
                        >
                            <i class="ph-bold ph-plus-circle text-sm"></i>
                            Registrar disponibilidad
                        </button>
                    @endcan

                    <a href="{{ $linksCabecera['voluntarios'] }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-[#C7B5A3]/80 bg-[#F3ECE4]/78 px-4 text-xs font-black uppercase tracking-wide text-[#2F3E5C] shadow-sm transition hover:border-[#E27D60]/45 hover:text-[#E27D60] active:scale-95">
                        <i class="ph-bold ph-users-three text-sm"></i>
                        Ver voluntarios
                    </a>

                    <a href="{{ $linksCabecera['resumen'] }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-[#C7B5A3]/80 bg-[#D5C7B9]/70 px-4 text-xs font-black uppercase tracking-wide text-[#2F3E5C] shadow-sm transition hover:bg-[#C7B5A3]/80 active:scale-95">
                        <i class="ph-bold ph-arrow-left text-sm"></i>
                        Volver al resumen
                    </a>
                </div>
            </div>
        </section>

        @php
            $tonoClases = [
                'azul' => ['icono' => 'bg-[#2F3E5C]/10 text-[#2F3E5C]', 'valor' => 'text-[#2F3E5C]', 'linea' => 'bg-[#2F3E5C]'],
                'verde' => ['icono' => 'bg-[#8DA280]/18 text-[#63775B]', 'valor' => 'text-[#63775B]', 'linea' => 'bg-[#8DA280]'],
                'terracota' => ['icono' => 'bg-[#E27D60]/12 text-[#E27D60]', 'valor' => 'text-[#E27D60]', 'linea' => 'bg-[#E27D60]'],
                'dorado' => ['icono' => 'bg-[#D9A05B]/16 text-[#9A6B2E]', 'valor' => 'text-[#9A6B2E]', 'linea' => 'bg-[#D9A05B]'],
            ];
            $estadoClases = [
                'Disponible' => 'bg-[#8DA280]/18 text-[#63775B] border-[#8DA280]/25',
                'No disponible' => 'bg-[#D5C7B9]/72 text-[#7C7168] border-[#C7B5A3]/50',
                'Pendiente' => 'bg-[#D9A05B]/16 text-[#9A6B2E] border-[#D9A05B]/25',
                'Suspendido' => 'bg-[#E27D60]/10 text-[#E27D60] border-[#E27D60]/20',
            ];
        @endphp

        <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            @foreach($metricas as $metrica)
                @php
                    $tono = $tonoClases[$metrica['tono']] ?? $tonoClases['azul'];
                @endphp
                <article class="relative min-h-[104px] overflow-hidden rounded-2xl border border-[#C7B5A3]/55 bg-[#F3ECE4]/78 p-3.5 shadow-sm backdrop-blur-xl transition duration-300 hover:-translate-y-0.5 hover:border-[#E27D60]/35">
                    <div class="absolute inset-x-0 top-0 h-1 {{ $tono['linea'] }}"></div>
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-xs font-black uppercase leading-snug tracking-[0.12em] text-[#2F3E5C]/60">{{ $metrica['label'] }}</p>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $tono['icono'] }}">
                            <i class="ph-bold {{ $metrica['icono'] }} text-base"></i>
                        </span>
                    </div>
                    <p class="mt-2 text-2xl font-black leading-none {{ $tono['valor'] }}">{{ number_format($metrica['valor']) }}</p>
                    <p class="mt-1 truncate text-xs font-bold text-[#2F3E5C]/55">{{ $metrica['subtitulo'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="rounded-[1.35rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/75 p-3.5 shadow-sm backdrop-blur-xl">
            <div class="grid gap-3 lg:grid-cols-[1.3fr_0.75fr_0.75fr_0.75fr_0.75fr_auto_auto]">
                <label class="block">
                    <span class="mb-1 block text-xs font-black uppercase tracking-widest text-[#2F3E5C]/55">Buscar voluntario</span>
                    <span class="relative block">
                        <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-[#2F3E5C]/42"></i>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nombre, apellido o CI..." class="h-10 w-full rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 pl-10 pr-3 text-xs font-bold text-[#2F3E5C] outline-none transition placeholder:text-[#2F3E5C]/40 focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                    </span>
                </label>

                <label class="block">
                    <span class="mb-1 block text-xs font-black uppercase tracking-widest text-[#2F3E5C]/55">Fecha</span>
                    <input type="date" wire:model.live="fechaFiltro" class="h-10 w-full rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 px-3 text-xs font-bold text-[#2F3E5C] outline-none focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                </label>

                <label class="block">
                    <span class="mb-1 block text-xs font-black uppercase tracking-widest text-[#2F3E5C]/55">Día</span>
                    <select wire:model.live="diaFiltro" class="h-10 w-full rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 px-3 text-xs font-bold text-[#2F3E5C] outline-none focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                        <option value="">Todos</option>
                        @foreach($diasSemana as $dia)
                            <option value="{{ $dia }}">{{ $dia }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="mb-1 block text-xs font-black uppercase tracking-widest text-[#2F3E5C]/55">Turno</span>
                    <select wire:model.live="turnoFiltro" class="h-10 w-full rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 px-3 text-xs font-bold text-[#2F3E5C] outline-none focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                        <option value="">Todos</option>
                        @foreach($turnos as $turno)
                            <option value="{{ $turno }}">{{ $turno }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="mb-1 block text-xs font-black uppercase tracking-widest text-[#2F3E5C]/55">Estado</span>
                    <select wire:model.live="estadoFiltro" class="h-10 w-full rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 px-3 text-xs font-bold text-[#2F3E5C] outline-none focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                        <option value="">Todos</option>
                        @foreach($estados as $estado)
                            <option value="{{ $estado }}">{{ $estado }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="flex items-end">
                    <button type="button" wire:click="$refresh" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-[#2F3E5C] px-4 text-xs font-black uppercase tracking-wide text-white transition hover:bg-[#E27D60] active:scale-95 lg:w-auto">
                        <i class="ph-bold ph-magnifying-glass"></i>
                        Buscar
                    </button>
                </div>

                <div class="flex items-end">
                    <button type="button" wire:click="limpiarFiltros" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl border border-[#C7B5A3]/70 bg-[#D5C7B9]/70 px-4 text-xs font-black uppercase tracking-wide text-[#2F3E5C] transition hover:bg-[#C7B5A3]/80 active:scale-95 lg:w-auto">
                        <i class="ph-bold ph-broom"></i>
                        Limpiar
                    </button>
                </div>
            </div>
        </section>

        <section class="grid gap-5 xl:grid-cols-[1.9fr_1fr]">
            <div class="rounded-[1.45rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/75 p-4 shadow-sm backdrop-blur-xl">
                <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <span class="text-xs font-black uppercase tracking-[0.15em] text-[#E27D60]">Calendario de disponibilidad</span>
                        <h2 class="mt-0.5 text-base font-black text-[#2F3E5C]">{{ $rangoSemana }}</h2>
                    </div>
                    <div class="flex gap-1.5">
                        <button type="button" wire:click="semanaAnterior" class="flex h-9 w-9 items-center justify-center rounded-xl border border-[#C7B5A3]/60 bg-[#E6DDD3]/70 text-[#2F3E5C] transition hover:bg-[#C7B5A3]/80" title="Semana anterior">
                            <i class="ph-bold ph-caret-left"></i>
                        </button>
                        <button type="button" wire:click="irHoy" class="inline-flex h-9 items-center justify-center rounded-xl border border-[#C7B5A3]/60 bg-[#E6DDD3]/70 px-3 text-xs font-black uppercase tracking-wide text-[#2F3E5C] transition hover:bg-[#C7B5A3]/80">
                            Hoy
                        </button>
                        <button type="button" wire:click="semanaSiguiente" class="flex h-9 w-9 items-center justify-center rounded-xl border border-[#C7B5A3]/60 bg-[#E6DDD3]/70 text-[#2F3E5C] transition hover:bg-[#C7B5A3]/80" title="Semana siguiente">
                            <i class="ph-bold ph-caret-right"></i>
                        </button>
                    </div>
                </div>

                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4 2xl:grid-cols-7">
                    @foreach($calendario as $dia)
                        <article class="min-h-[168px] rounded-2xl border border-[#C7B5A3]/48 bg-[#E6DDD3]/48 p-3">
                            <header class="mb-2 flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="text-xs font-black uppercase tracking-wider text-[#2F3E5C]">{{ $dia['dia'] }}</h3>
                                    <p class="text-xs font-bold text-[#2F3E5C]/55">{{ $dia['fecha']->format('d/m') }}</p>
                                </div>
                                <span class="rounded-full bg-[#2F3E5C]/8 px-2 py-0.5 text-xs font-black text-[#2F3E5C]/60">{{ $dia['items']->count() }}</span>
                            </header>

                            <div class="space-y-1.5">
                                @forelse($dia['items']->take(4) as $item)
                                    @php
                                        $estadoClase = $estadoClases[$item->estado_operativo] ?? $estadoClases['Disponible'];
                                    @endphp
                                    <button type="button" wire:click="editar({{ $item->cod_hor_vol }})" class="block w-full rounded-xl border px-2.5 py-2 text-left text-xs transition hover:-translate-y-0.5 hover:shadow-sm {{ $estadoClase }}">
                                        <span class="block font-black leading-tight">{{ substr((string) $item->hora_inicio, 0, 5) }} - {{ substr((string) $item->hora_fin, 0, 5) }}</span>
                                        <span class="mt-0.5 block truncate font-bold">{{ $item->nombre_voluntario ?: 'Voluntario' }}</span>
                                        <span class="mt-1 inline-flex rounded-full bg-white/55 px-1.5 py-0.5 font-black uppercase tracking-wider">{{ $item->turno }}</span>
                                    </button>
                                @empty
                                    <div class="rounded-xl border border-dashed border-[#C7B5A3]/60 bg-[#F3ECE4]/45 p-4 text-center">
                                        <p class="text-xs font-bold leading-relaxed text-[#2F3E5C]/55">Sin disponibilidad registrada</p>
                                    </div>
                                @endforelse

                                @if($dia['items']->count() > 4)
                                    <p class="pt-1 text-center text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50">
                                        +{{ $dia['items']->count() - 4 }} horarios más
                                    </p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <aside class="rounded-[1.45rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/75 p-4 shadow-sm backdrop-blur-xl">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div>
                        <span class="text-xs font-black uppercase tracking-[0.15em] text-[#E27D60]">Listado breve</span>
                        <h2 class="mt-0.5 text-base font-black text-[#2F3E5C]">Horarios registrados</h2>
                    </div>
                    <i class="ph-bold ph-list-checks text-2xl text-[#63775B]"></i>
                </div>

                <div class="space-y-2.5">
                    @forelse($registros as $registro)
                        @php
                            $estadoClase = $estadoClases[$registro->estado_operativo] ?? $estadoClases['Disponible'];
                        @endphp
                        <article class="rounded-2xl border border-[#C7B5A3]/45 bg-[#E6DDD3]/48 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-black text-[#2F3E5C]">{{ $registro->nombre_voluntario ?: 'Voluntario' }}</p>
                                    <p class="mt-0.5 text-xs font-bold text-[#2F3E5C]/55">{{ $registro->dia_semana }} · {{ $registro->fecha_referencia }}</p>
                                </div>
                                <span class="rounded-full border px-2 py-0.5 text-xs font-black uppercase tracking-wide {{ $estadoClase }}">{{ $registro->estado_operativo }}</span>
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs font-black">
                                <span class="rounded-full bg-[#2F3E5C]/8 px-2 py-1 text-[#2F3E5C]">{{ substr((string) $registro->hora_inicio, 0, 5) }} - {{ substr((string) $registro->hora_fin, 0, 5) }}</span>
                                <span class="rounded-full bg-[#D9A05B]/14 px-2 py-1 text-[#9A6B2E]">{{ $registro->turno }}</span>
                                <span class="rounded-full bg-[#8DA280]/14 px-2 py-1 text-[#63775B]">{{ (int) $registro->asignaciones_activas_count > 0 ? 'Asignado' : 'Sin asignación' }}</span>
                            </div>

                            @if($registro->observaciones)
                                <p class="mt-2 line-clamp-2 text-xs font-bold leading-relaxed text-[#2F3E5C]/55">{{ str_replace('[NO DISPONIBLE]', '', $registro->observaciones) }}</p>
                            @endif

                            <div class="mt-3 flex justify-end gap-1.5">
                                @can('voluntarios.editar')
                                    <button type="button" wire:click="editar({{ $registro->cod_hor_vol }})" class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#D9A05B]/14 text-[#9A6B2E] transition hover:bg-[#D9A05B] hover:text-white" title="Editar">
                                        <i class="ph-bold ph-pencil-simple"></i>
                                    </button>
                                    @if($registro->estado_operativo === 'No disponible')
                                        <button type="button" @click="confirmarReactivar({{ $registro->cod_hor_vol }})" class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#8DA280]/18 text-[#63775B] transition hover:bg-[#8DA280] hover:text-white" title="Reactivar">
                                            <i class="ph-bold ph-check-circle"></i>
                                        </button>
                                    @else
                                        <button type="button" @click="confirmarNoDisponible({{ $registro->cod_hor_vol }})" class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#E27D60]/10 text-[#E27D60] transition hover:bg-[#E27D60] hover:text-white" title="Marcar no disponible">
                                            <i class="ph-bold ph-prohibit"></i>
                                        </button>
                                    @endif
                                @endcan
                                <a href="{{ route('admin.voluntariado.asignaciones.index', ['voluntario' => $registro->cod_vol]) }}" class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#2F3E5C]/8 text-[#2F3E5C] transition hover:bg-[#2F3E5C] hover:text-white" title="Ver asignaciones">
                                    <i class="ph-bold ph-handshake"></i>
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-[#C7B5A3]/70 bg-[#E6DDD3]/38 p-7 text-center">
                            <i class="ph-bold ph-calendar-blank text-4xl text-[#2F3E5C]/25"></i>
                            <h3 class="mt-3 text-sm font-black text-[#2F3E5C]">
                                {{ $search || $diaFiltro || $turnoFiltro || $estadoFiltro ? 'No se encontraron voluntarios disponibles con los filtros seleccionados.' : 'No hay disponibilidad registrada para esta semana.' }}
                            </h3>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/55">Registra horarios disponibles para preparar el flujo hacia asignaciones.</p>
                        </div>
                    @endforelse
                </div>

                @if($registros->hasPages())
                    <div class="mt-4">
                        {{ $registros->links() }}
                    </div>
                @endif
            </aside>
        </section>
    </div>

    @if($mostrarFormulario)
        <div class="fixed inset-0 z-[90] flex items-center justify-center px-4 py-6">
            <div class="absolute inset-0 bg-[#2F3E5C]/55 backdrop-blur-sm" wire:click="cerrarFormulario"></div>
            <section class="relative flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-[1.5rem] border border-[#C7B5A3]/70 bg-[#F3ECE4] shadow-[0_24px_70px_rgba(47,62,92,0.28)]">
                <header class="flex items-start justify-between gap-4 border-b border-[#C7B5A3]/60 bg-[#E6DDD3]/88 px-5 py-4">
                    <div>
                        <span class="text-xs font-black uppercase tracking-[0.15em] text-[#E27D60]">{{ $isEdit ? 'Editar horario' : 'Nuevo horario' }}</span>
                        <h2 class="mt-1 text-xl font-black text-[#2F3E5C]">{{ $isEdit ? 'Editar disponibilidad' : 'Registrar disponibilidad' }}</h2>
                        <p class="mt-1 text-xs font-bold text-[#2F3E5C]/58">Defina el día y horario disponible del voluntario.</p>
                    </div>
                    <button type="button" wire:click="cerrarFormulario" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-[#C7B5A3]/60 bg-[#D5C7B9]/70 text-[#2F3E5C] transition hover:bg-[#E27D60] hover:text-white">
                        <i class="ph-bold ph-x"></i>
                    </button>
                </header>

                <div class="flex-1 overflow-y-auto p-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block sm:col-span-2">
                            <span class="mb-1.5 block text-xs font-black uppercase tracking-widest text-[#2F3E5C]/55">Voluntario</span>
                            <select wire:model="cod_vol" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white/75 px-3 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                                <option value="">Seleccionar voluntario</option>
                                @foreach($voluntariosActivos as $voluntario)
                                    <option value="{{ $voluntario->cod_vol }}">{{ $voluntario->nombre }}{{ $voluntario->numero_documento ? ' · CI ' . $voluntario->numero_documento : '' }}</option>
                                @endforeach
                            </select>
                            @error('cod_vol') <p class="mt-1 text-xs font-bold text-[#E27D60]">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black uppercase tracking-widest text-[#2F3E5C]/55">Día</span>
                            <select wire:model="dia_semana" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white/75 px-3 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                                <option value="">Seleccionar día</option>
                                @foreach($diasSemana as $dia)
                                    <option value="{{ $dia }}">{{ $dia }}</option>
                                @endforeach
                            </select>
                            @error('dia_semana') <p class="mt-1 text-xs font-bold text-[#E27D60]">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black uppercase tracking-widest text-[#2F3E5C]/55">Turno calculado</span>
                            <div class="flex h-11 items-center rounded-xl border border-[#C7B5A3]/60 bg-[#E6DDD3]/70 px-3 text-sm font-black text-[#2F3E5C]/70">
                                {{ $hora_inicio ? ($hora_inicio < '12:00' ? 'Mañana' : ($hora_inicio < '18:00' ? 'Tarde' : 'Noche')) : 'Flexible' }}
                            </div>
                        </label>

                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black uppercase tracking-widest text-[#2F3E5C]/55">Hora inicio</span>
                            <input type="time" wire:model.live="hora_inicio" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white/75 px-3 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('hora_inicio') <p class="mt-1 text-xs font-bold text-[#E27D60]">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black uppercase tracking-widest text-[#2F3E5C]/55">Hora fin</span>
                            <input type="time" wire:model="hora_fin" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white/75 px-3 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('hora_fin') <p class="mt-1 text-xs font-bold text-[#E27D60]">{{ $message }}</p> @enderror
                        </label>

                        <label class="block sm:col-span-2">
                            <span class="mb-1.5 block text-xs font-black uppercase tracking-widest text-[#2F3E5C]/55">Observación</span>
                            <textarea wire:model="observaciones" rows="4" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white/75 px-3 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15"></textarea>
                            @error('observaciones') <p class="mt-1 text-xs font-bold text-[#E27D60]">{{ $message }}</p> @enderror
                        </label>
                    </div>
                </div>

                <footer class="flex flex-col-reverse gap-2 border-t border-[#C7B5A3]/60 bg-[#E6DDD3]/88 px-5 py-4 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="cerrarFormulario" class="inline-flex h-10 items-center justify-center rounded-xl border border-[#C7B5A3]/70 bg-[#D5C7B9]/70 px-4 text-xs font-black uppercase tracking-wide text-[#2F3E5C] transition hover:bg-[#C7B5A3]/80">
                        Cancelar
                    </button>
                    <button type="button" wire:click="guardar" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#2F3E5C] px-5 text-xs font-black uppercase tracking-wide text-white shadow-[0_8px_18px_rgba(47,62,92,0.18)] transition hover:bg-[#E27D60] active:scale-95">
                        <i class="ph-bold ph-floppy-disk"></i>
                        {{ $isEdit ? 'Guardar cambios' : 'Registrar disponibilidad' }}
                    </button>
                </footer>
            </section>
        </div>
    @endif
</div>
