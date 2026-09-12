<div class="rm-pilot-enfermeria rm-page-layout space-y-5 font-sans">
    @php
        $esMedico = auth()->user()?->hasAnyRole(['SUPERADMINISTRADOR', 'MEDICO GENERAL/GERIATRA']);
        $puedeCrear = $esMedico && (
            auth()->user()?->hasRole('SUPERADMINISTRADOR')
            || auth()->user()?->can('medicacion.crear')
        );
        $puedeEditar = $esMedico && (
            auth()->user()?->hasRole('SUPERADMINISTRADOR')
            || auth()->user()?->can('medicacion.editar')
        );
        $puedeSuspender = $esMedico && (
            auth()->user()?->hasRole('SUPERADMINISTRADOR')
            || auth()->user()?->can('medicacion.suspender')
        );
        $puedeAdministrar = auth()->user()?->hasRole('ENFERMEROS')
            && auth()->user()?->can('administracion_medicacion.registrar');

        $hayFiltros = trim($search) !== ''
            || $filtroEstado !== 'EN_CURSO'
            || $filtroVia !== '';
    @endphp

    @if($adulto)
        <x-residentes.navegacion-ficha :adulto="$adulto" />
    @endif

    {{-- CABECERA --}}
    <section
        class="overflow-hidden rounded-[1.6rem] border border-[var(--rm-border)]/65 bg-[var(--rm-surface)] shadow-sm">
        <div class="flex flex-col gap-4 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <div
                    class="mb-2 inline-flex items-center gap-2 rounded-full border border-[var(--rm-border)]/70 bg-[var(--rm-bg-app)] px-3 py-1 text-[10px] font-black uppercase tracking-wider text-[var(--rm-text-muted)]">
                    <i class="ph-fill ph-pill text-[var(--rm-primary)]"></i>
                    Medicación
                </div>

                <h1 class="text-2xl font-black tracking-tight text-[var(--rm-text-body)] sm:text-3xl">
                    Prescripciones y seguimiento
                </h1>

                <p class="mt-1 max-w-3xl text-xs font-semibold leading-relaxed text-[var(--rm-text-muted)] sm:text-sm">
                    @if($adulto)
                        Tratamiento farmacológico de
                        <span class="font-black text-[var(--rm-text-body)]">{{ $adulto->nombre_completo }}</span>.
                    @else
                        Consulte órdenes médicas vigentes y seleccione un residente para revisar su agenda de hoy.
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if($adulto)
                    <button type="button" wire:click="verUbicacion('{{ $adulto->cod_am }}')"
                        class="inline-flex h-10 items-center gap-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] px-3.5 text-xs font-bold text-[var(--rm-text-muted)] transition hover:bg-[var(--rm-bg-app)] hover:text-[var(--rm-text-body)] active:scale-95">
                        <i class="ph-bold ph-map-pin text-[var(--rm-primary)]"></i>
                        Ubicación
                    </button>
                @endif

                @if($puedeCrear)
                    <button type="button"
                        wire:click="abrirNuevaPrescripcion({{ $adulto ? "'" . $adulto->cod_am . "'" : 'null' }})"
                        class="inline-flex h-10 items-center gap-2 rounded-xl bg-[var(--rm-primary)] px-4 text-xs font-black text-inverso shadow-sm transition hover:-translate-y-0.5 hover:shadow-md active:scale-95">
                        <i class="ph-bold ph-plus-circle text-base"></i>
                        Nueva prescripción
                    </button>
                @endif
            </div>
        </div>

        @if($adulto)
            <div
                class="grid gap-3 border-t border-[var(--rm-border)]/60 bg-[var(--rm-bg-app)]/60 px-5 py-3 sm:grid-cols-2 sm:px-6 lg:grid-cols-4">
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-user-circle text-[var(--rm-primary)]"></i>
                    <div class="min-w-0">
                        <p class="text-[9px] font-black uppercase tracking-wider text-[var(--rm-text-muted)]">Residente</p>
                        <p class="truncate text-[11px] font-black text-[var(--rm-text-body)]">{{ $adulto->nombre_completo }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-bed text-[var(--rm-primary)]"></i>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-wider text-[var(--rm-text-muted)]">Ubicación</p>
                        <p class="text-[11px] font-black text-[var(--rm-text-body)]">{{ $adulto->ubicacion_texto }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-calendar-check text-estado-exito"></i>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-wider text-[var(--rm-text-muted)]">Agenda
                            pendiente</p>
                        <p class="text-[11px] font-black text-[var(--rm-text-body)]">{{ $agendaStats['pendientes'] }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <i
                        class="ph-bold ph-warning-circle {{ $agendaStats['vencidas'] > 0 ? 'text-boton-acento' : 'text-[var(--rm-text-muted)]' }}"></i>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-wider text-[var(--rm-text-muted)]">Vencidas hoy
                        </p>
                        <p
                            class="text-[11px] font-black {{ $agendaStats['vencidas'] > 0 ? 'text-boton-acento' : 'text-[var(--rm-text-body)]' }}">
                            {{ $agendaStats['vencidas'] }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </section>

    {{-- MENSAJES --}}
    @if(session()->has('mensaje_exito'))
        <div class="flex items-start gap-3 rounded-2xl border border-estado-exitoBorde bg-estado-exitoBg p-4 text-estado-exito shadow-sm"
            x-data x-transition>
            <i class="ph-fill ph-check-circle mt-0.5 text-xl"></i>
            <p class="flex-1 text-xs font-bold">{{ session('mensaje_exito') }}</p>
        </div>
    @endif

    {{-- RESUMEN --}}
    <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <article
            class="rounded-2xl border border-estado-exitoBorde bg-[var(--rm-surface)] p-4 shadow-sm transition hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <span class="text-[9px] font-black uppercase tracking-wider text-[var(--rm-text-muted)]">Activas</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                    <i class="ph-bold ph-check-circle"></i>
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-[var(--rm-text-body)]">{{ $stats['activas'] }}</p>
            <p class="mt-0.5 text-[10px] font-semibold text-[var(--rm-text-muted)]">Tratamientos activos</p>
        </article>

        <article
            class="rounded-2xl border border-estado-advertenciaBorde bg-[var(--rm-surface)] p-4 shadow-sm transition hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <span class="text-[9px] font-black uppercase tracking-wider text-[var(--rm-text-muted)]">Pausa /
                    revisión</span>
                <span
                    class="flex h-8 w-8 items-center justify-center rounded-xl bg-estado-advertenciaBg text-estado-advertencia">
                    <i class="ph-bold ph-magnifying-glass"></i>
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-[var(--rm-text-body)]">{{ $stats['revision'] }}</p>
            <p class="mt-0.5 text-[10px] font-semibold text-[var(--rm-text-muted)]">Órdenes temporalmente detenidas</p>
        </article>

        <article
            class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-sm transition hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <span
                    class="text-[9px] font-black uppercase tracking-wider text-[var(--rm-text-muted)]">Suspendidas</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-estado-peligroBg text-boton-acento">
                    <i class="ph-bold ph-stop-circle"></i>
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-[var(--rm-text-body)]">{{ $stats['suspendidas'] }}</p>
            <p class="mt-0.5 text-[10px] font-semibold text-[var(--rm-text-muted)]">Interrumpidas por decisión médica
            </p>
        </article>

        <article
            class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-sm transition hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <span
                    class="text-[9px] font-black uppercase tracking-wider text-[var(--rm-text-muted)]">Histórico</span>
                <span
                    class="flex h-8 w-8 items-center justify-center rounded-xl bg-[var(--rm-bg-app)] text-[var(--rm-text-muted)]">
                    <i class="ph-bold ph-archive"></i>
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-[var(--rm-text-body)]">{{ $stats['finalizadas'] }}</p>
            <p class="mt-0.5 text-[10px] font-semibold text-[var(--rm-text-muted)]">Finalizadas o archivadas</p>
        </article>
    </section>

    <div class="grid gap-5 xl:grid-cols-[290px_minmax(0,1fr)]">
        {{-- LATERAL --}}
        <aside class="space-y-4">
            <section class="rounded-2xl border border-[var(--rm-border)]/65 bg-[var(--rm-surface)] p-4 shadow-sm">
                <label class="mb-2 block text-[9px] font-black uppercase tracking-widest text-[var(--rm-text-muted)]">
                    Residente
                </label>

                <div class="relative">
                    <i
                        class="ph-bold ph-user-circle absolute left-3 top-1/2 -translate-y-1/2 text-[var(--rm-text-muted)]"></i>
                    <select wire:model.live="cod_am"
                        class="w-full appearance-none rounded-xl border border-[var(--rm-border)] bg-[var(--rm-bg-app)] py-2.5 pl-9 pr-9 text-xs font-bold text-[var(--rm-text-body)] outline-none transition focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/15">
                        <option value="">Todos los residentes activos</option>
                        @foreach($adultosDisponibles as $ad)
                            <option value="{{ $ad->cod_am }}">
                                {{ $ad->nombre_completo }}
                            </option>
                        @endforeach
                    </select>
                    <i
                        class="ph-bold ph-caret-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[var(--rm-text-muted)]"></i>
                </div>
            </section>

            @if($adulto)
                <section class="rounded-2xl border border-[var(--rm-border)]/65 bg-[var(--rm-surface)] p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[var(--rm-primary)] text-sm font-black text-inverso">
                            {{ mb_strtoupper(mb_substr($adulto->nombres ?? 'R', 0, 1) . mb_substr($adulto->ap_paterno ?? '', 0, 1)) }}
                        </div>

                        <div class="min-w-0">
                            <h3 class="truncate text-sm font-black text-[var(--rm-text-body)]">
                                {{ $adulto->nombre_completo }}</h3>
                            <p class="mt-0.5 text-[10px] font-semibold text-[var(--rm-text-muted)]">
                                {{ $adulto->edad_texto }}
                                @if($adulto->ci) · CI {{ $adulto->ci }} @endif
                            </p>
                        </div>
                    </div>

                    @if(filled($adulto->alergias))
                        <div class="mt-3 rounded-xl border border-[var(--rm-border)]-focus bg-estado-peligroBg p-3">
                            <div class="flex items-start gap-2">
                                <i class="ph-fill ph-warning-octagon mt-0.5 text-boton-acento"></i>
                                <div class="min-w-0">
                                    <p class="text-[9px] font-black uppercase tracking-wider text-boton-acento">Alergias</p>
                                    <p
                                        class="mt-1 break-words text-[10px] font-bold leading-relaxed text-[var(--rm-text-body)]">
                                        {{ $adulto->alergias }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($puedeCrear)
                        <button type="button" wire:click="abrirNuevaPrescripcion('{{ $adulto->cod_am }}')"
                            class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[var(--rm-primary)] px-3 py-2.5 text-xs font-black text-inverso transition hover:-translate-y-0.5 active:scale-95">
                            <i class="ph-bold ph-plus-circle"></i>
                            Prescribir
                        </button>
                    @endif
                </section>

                {{-- AGENDA --}}
                <section
                    class="overflow-hidden rounded-2xl border border-[var(--rm-border)]/65 bg-[var(--rm-surface)] shadow-sm">
                    <div class="flex items-center justify-between border-b border-[var(--rm-border)]/60 px-4 py-3">
                        <div>
                            <h3 class="text-[10px] font-black uppercase tracking-wider text-[var(--rm-text-body)]">
                                Agenda de hoy
                            </h3>
                            <p class="mt-0.5 text-[9px] font-semibold text-[var(--rm-text-muted)]">
                                Dosis programadas
                            </p>
                        </div>
                        <i class="ph-bold ph-clock-countdown text-lg text-[var(--rm-primary)]"></i>
                    </div>

                    <div class="max-h-[430px] space-y-2 overflow-y-auto p-3">
                        @forelse($agenda as $toma)
                            @php
                                $agendaClase = match ($toma['estado']) {
                                    'ADMINISTRADA' => 'border-estado-exitoBorde bg-estado-exitoBg',
                                    'OMITIDA', 'VENCIDA' => 'border-[var(--rm-border)]-focus bg-estado-peligroBg',
                                    'PROXIMA' => 'border-[var(--rm-primary)]/35 bg-[var(--rm-primary)]/10',
                                    default => 'border-[var(--rm-border)] bg-[var(--rm-bg-app)]',
                                };

                                $agendaTexto = match ($toma['estado']) {
                                    'ADMINISTRADA' => 'text-estado-exito',
                                    'OMITIDA', 'VENCIDA' => 'text-boton-acento',
                                    'PROXIMA' => 'text-[var(--rm-primary)]',
                                    default => 'text-[var(--rm-text-muted)]',
                                };
                            @endphp

                            <article class="rounded-xl border p-3 transition hover:-translate-y-0.5 {{ $agendaClase }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-[11px] font-black text-[var(--rm-text-body)]">
                                            {{ $toma['medicacion']->nombre_medicamento }}
                                        </p>
                                        <p class="mt-0.5 text-[9px] font-semibold text-[var(--rm-text-muted)]">
                                            {{ $toma['medicacion']->dosis }} · {{ $toma['medicacion']->via_administracion }}
                                        </p>
                                    </div>

                                    <time class="shrink-0 text-sm font-black text-[var(--rm-text-body)]">
                                        {{ $toma['hora'] }}
                                    </time>
                                </div>

                                <div class="mt-2 flex items-center justify-between gap-2">
                                    <span class="text-[9px] font-black uppercase tracking-wider {{ $agendaTexto }}">
                                        {{ $toma['estado'] === 'PROXIMA' ? 'PRÓXIMA' : $toma['estado'] }}
                                    </span>

                                    @if($puedeAdministrar && in_array($toma['estado'], ['PROXIMA', 'VENCIDA'], true))
                                        <button type="button" x-data x-on:click="$dispatch('abrirModalAdministracion', {
                                                            cod_am: '{{ $toma['medicacion']->cod_am }}',
                                                            cod_med_adulto: '{{ $toma['medicacion']->cod_med_adulto }}',
                                                            hora_programada: '{{ $toma['hora'] }}'
                                                        })"
                                            class="inline-flex items-center gap-1 rounded-lg bg-[var(--rm-primary)] px-2 py-1 text-[9px] font-black text-inverso transition active:scale-95">
                                            <i class="ph-bold ph-check-square"></i>
                                            Registrar
                                        </button>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="py-6 text-center">
                                <div
                                    class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--rm-bg-app)] text-[var(--rm-text-muted)]">
                                    <i class="ph-bold ph-calendar-x text-xl"></i>
                                </div>
                                <p class="mt-2 text-[10px] font-bold text-[var(--rm-text-muted)]">
                                    No hay dosis programadas para hoy.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </section>
            @else
                <section
                    class="rounded-2xl border border-dashed border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 text-center shadow-sm">
                    <i class="ph-bold ph-hand-pointing text-2xl text-[var(--rm-text-muted)]/60"></i>
                    <p class="mt-2 text-xs font-bold text-[var(--rm-text-muted)]">
                        Seleccione un residente para revisar su agenda diaria y contexto farmacológico.
                    </p>
                </section>
            @endif
        </aside>

        {{-- CONTENIDO PRINCIPAL --}}
        <main class="min-w-0 space-y-4">
            {{-- FILTROS --}}
            <section class="rounded-2xl border border-[var(--rm-border)]/65 bg-[var(--rm-surface)] p-4 shadow-sm">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xs font-black uppercase tracking-wider text-[var(--rm-text-body)]">Buscar
                            prescripciones</h2>
                        <p class="mt-0.5 text-[9px] font-semibold text-[var(--rm-text-muted)]">
                            Busque por medicamento, dosis, frecuencia o prescriptor.
                        </p>
                    </div>

                    @if($hayFiltros)
                        <button type="button" wire:click="limpiarFiltros"
                            class="inline-flex items-center gap-1 text-[10px] font-black text-boton-acento transition hover:underline">
                            <i class="ph-bold ph-x-circle"></i>
                            Restablecer
                        </button>
                    @endif
                </div>

                <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_180px_180px]">
                    <div class="relative">
                        <i
                            class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-[var(--rm-text-muted)]"></i>
                        <input type="search" wire:model.live.debounce.300ms="search"
                            placeholder="Ej.: Enalapril, 10 mg..."
                            class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-bg-app)] py-2.5 pl-9 pr-3 text-xs font-bold text-[var(--rm-text-body)] outline-none transition placeholder:font-semibold placeholder:text-[var(--rm-text-muted)]/70 focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/15">
                    </div>

                    <select wire:model.live="filtroEstado"
                        class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-bg-app)] px-3 py-2.5 text-xs font-bold text-[var(--rm-text-body)] outline-none transition focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/15">
                        <option value="EN_CURSO">En curso</option>
                        <option value="TODOS">Todos los estados</option>
                        <option value="ACTIVO">Activo</option>
                        <option value="PAUSADO">Pausado</option>
                        <option value="EN REVISION">En revisión</option>
                        <option value="SUSPENDIDO">Suspendido</option>
                        <option value="FINALIZADO">Finalizado</option>
                        <option value="ARCHIVADO">Archivado</option>
                    </select>

                    <select wire:model.live="filtroVia"
                        class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-bg-app)] px-3 py-2.5 text-xs font-bold text-[var(--rm-text-body)] outline-none transition focus:border-[var(--rm-primary)] focus:ring-2 focus:ring-boton-principal/15">
                        <option value="">Todas las vías</option>
                        @foreach($viasDisponibles as $via)
                            <option value="{{ $via }}">{{ $via }}</option>
                        @endforeach
                    </select>
                </div>
            </section>

            {{-- LISTADO --}}
            <section
                class="overflow-hidden rounded-2xl border border-[var(--rm-border)]/65 bg-[var(--rm-surface)] shadow-sm">
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-b border-[var(--rm-border)]/60 px-4 py-3 sm:px-5">
                    <div>
                        <h2 class="text-xs font-black uppercase tracking-wider text-[var(--rm-text-body)]">
                            {{ $adulto ? 'Tratamiento farmacológico' : 'Prescripciones registradas' }}
                        </h2>
                        <p class="mt-0.5 text-[9px] font-semibold text-[var(--rm-text-muted)]">
                            {{ $medicaciones->total() }} resultado{{ $medicaciones->total() === 1 ? '' : 's' }}
                        </p>
                    </div>

                    <div wire:loading.flex wire:target="search,filtroEstado,filtroVia,cod_am"
                        class="items-center gap-2 text-[10px] font-bold text-[var(--rm-primary)]">
                        <i class="ph-bold ph-spinner animate-spin"></i>
                        Actualizando
                    </div>
                </div>

                @if($medicaciones->isEmpty())
                    <div class="p-10 text-center">
                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--rm-bg-app)] text-[var(--rm-text-muted)]">
                            <i class="ph-bold ph-pill text-3xl"></i>
                        </div>
                        <h3 class="mt-3 text-sm font-black text-[var(--rm-text-body)]">Sin prescripciones para mostrar</h3>
                        <p class="mx-auto mt-1 max-w-md text-xs font-semibold text-[var(--rm-text-muted)]">
                            @if($hayFiltros)
                                No se encontraron resultados con los filtros actuales.
                            @elseif($adulto)
                                Este residente no tiene medicación en curso.
                            @else
                                No existen órdenes médicas en curso para los residentes visibles.
                            @endif
                        </p>

                        @if($puedeCrear && !$hayFiltros)
                            <button type="button"
                                wire:click="abrirNuevaPrescripcion({{ $adulto ? "'" . $adulto->cod_am . "'" : 'null' }})"
                                class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[var(--rm-primary)] px-4 py-2.5 text-xs font-black text-inverso transition active:scale-95">
                                <i class="ph-bold ph-plus-circle"></i>
                                Crear prescripción
                            </button>
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[850px] text-left">
                            <thead class="border-b border-[var(--rm-border)]/60 bg-[var(--rm-bg-app)]">
                                <tr class="text-[9px] font-black uppercase tracking-widest text-[var(--rm-text-muted)]">
                                    @if(!$adulto)
                                        <th class="px-4 py-3 sm:px-5">Residente</th>
                                    @endif
                                    <th class="px-4 py-3 sm:px-5">Medicamento</th>
                                    <th class="px-4 py-3 sm:px-5">Pauta</th>
                                    <th class="px-4 py-3 sm:px-5">Vigencia</th>
                                    <th class="px-4 py-3 sm:px-5">Estado</th>
                                    <th class="px-4 py-3 text-right sm:px-5">Acciones</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-[var(--rm-border)]/50">
                                @foreach($medicaciones as $med)
                                    @php
                                        $estado = strtoupper((string) $med->estado);
                                        $esActiva = in_array($estado, ['ACTIVO', 'ACTIVA', 'VIGENTE'], true);
                                        $esEnCurso = in_array($estado, ['ACTIVO', 'ACTIVA', 'VIGENTE', 'PAUSADO', 'EN REVISION'], true);

                                        $estadoClase = match ($estado) {
                                            'ACTIVO', 'ACTIVA', 'VIGENTE' => 'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito',
                                            'PAUSADO' => 'border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia',
                                            'EN REVISION' => 'border-[var(--rm-primary)]/25 bg-[var(--rm-primary)]/10 text-[var(--rm-primary)]',
                                            'SUSPENDIDO' => 'border-[var(--rm-border)]-focus bg-estado-peligroBg text-boton-acento',
                                            'FINALIZADO', 'ARCHIVADO' => 'border-[var(--rm-border)] bg-[var(--rm-bg-app)] text-[var(--rm-text-muted)]',
                                            default => 'border-[var(--rm-border)] bg-[var(--rm-bg-app)] text-[var(--rm-text-muted)]',
                                        };

                                        $hora = $med->hora_programada?->format('H:i');
                                    @endphp

                                    <tr class="transition hover:bg-[var(--rm-bg-app)]/45 {{ !$esEnCurso ? 'opacity-80' : '' }}">
                                        @if(!$adulto)
                                            <td class="px-4 py-4 align-top sm:px-5">
                                                <p class="max-w-[180px] truncate text-xs font-black text-[var(--rm-text-body)]">
                                                    {{ $med->adultoMayor?->nombre_completo ?? 'Residente no disponible' }}
                                                </p>
                                                <p class="mt-1 text-[9px] font-semibold text-[var(--rm-text-muted)]">
                                                    {{ $med->adultoMayor?->ubicacion_texto ?? 'Sin ubicación' }}
                                                </p>
                                            </td>
                                        @endif

                                        <td class="px-4 py-4 align-top sm:px-5">
                                            <div class="flex items-start gap-2.5">
                                                <span
                                                    class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[var(--rm-primary)]/10 text-[var(--rm-primary)]">
                                                    <i class="ph-fill ph-pill"></i>
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="font-black text-[var(--rm-text-body)]">
                                                        {{ $med->nombre_medicamento }}</p>
                                                    <p class="mt-0.5 text-[10px] font-bold text-[var(--rm-text-muted)]">
                                                        {{ $med->dosis }} · {{ $med->via_administracion }}
                                                    </p>

                                                    @if($med->es_prn)
                                                        <span
                                                            class="mt-1.5 inline-flex items-center gap-1 rounded-md border border-estado-advertenciaBorde bg-estado-advertenciaBg px-2 py-0.5 text-[9px] font-black text-estado-advertencia">
                                                            <i class="ph-bold ph-first-aid-kit"></i>
                                                            PRN
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-4 py-4 align-top sm:px-5">
                                            <p class="text-[11px] font-black text-[var(--rm-text-body)]">
                                                {{ $med->frecuencia }}
                                            </p>

                                            @if($med->es_prn)
                                                <p
                                                    class="mt-1 max-w-[250px] text-[9px] font-semibold leading-relaxed text-[var(--rm-text-muted)]">
                                                    {{ \Illuminate\Support\Str::limit($med->condicion_prn ?: 'Condición PRN no registrada', 70) }}
                                                </p>
                                                @if($med->intervalo_horas)
                                                    <p class="mt-1 text-[9px] font-black text-estado-advertencia">
                                                        Intervalo mínimo: {{ $med->intervalo_horas }} h
                                                    </p>
                                                @endif
                                            @else
                                                <p class="mt-1 text-[9px] font-semibold text-[var(--rm-text-muted)]">
                                                    <i class="ph-bold ph-clock"></i>
                                                    {{ $hora ? 'Inicio ' . $hora : 'Sin hora programada' }}
                                                    @if($med->intervalo_horas)
                                                        · cada {{ $med->intervalo_horas }} h
                                                    @endif
                                                </p>
                                            @endif
                                        </td>

                                        <td class="px-4 py-4 align-top sm:px-5">
                                            <p class="text-[10px] font-black text-[var(--rm-text-body)]">
                                                {{ $med->fecha_inicio?->format('d/m/Y') ?? 'Sin inicio' }}
                                            </p>
                                            <p class="mt-1 text-[9px] font-semibold text-[var(--rm-text-muted)]">
                                                @if($med->fecha_fin)
                                                    hasta {{ $med->fecha_fin->format('d/m/Y') }}
                                                @else
                                                    sin fecha final
                                                @endif
                                            </p>
                                        </td>

                                        <td class="px-4 py-4 align-top sm:px-5">
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-wider {{ $estadoClase }}">
                                                <i
                                                    class="ph-bold {{ $esActiva ? 'ph-check-circle' : ($estado === 'EN REVISION' ? 'ph-magnifying-glass' : ($estado === 'PAUSADO' ? 'ph-pause-circle' : 'ph-circle')) }}"></i>
                                                {{ $estado }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-4 align-top sm:px-5">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if($puedeEditar && $esEnCurso)
                                                    <button type="button"
                                                        wire:click="editarMedicacion('{{ $med->cod_med_adulto }}')"
                                                        wire:loading.attr="disabled" wire:target="editarMedicacion"
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface)] text-[var(--rm-text-muted)] transition hover:border-[var(--rm-primary)]/35 hover:bg-[var(--rm-primary)]/10 hover:text-[var(--rm-primary)] active:scale-90 disabled:opacity-50"
                                                        title="Editar prescripción">
                                                        <i class="ph-bold ph-pencil-simple"></i>
                                                    </button>
                                                @endif

                                                @if($puedeSuspender && $esEnCurso)
                                                    <button type="button"
                                                        wire:click="suspenderMedicamento('{{ $med->cod_med_adulto }}')"
                                                        wire:confirm="¿Suspender la medicación {{ $med->nombre_medicamento }}?"
                                                        wire:loading.attr="disabled" wire:target="suspenderMedicamento"
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-[var(--rm-border)]-focus bg-estado-peligroBg text-boton-acento transition hover:bg-boton-acento hover:text-inverso active:scale-90 disabled:opacity-50"
                                                        title="Suspender medicación">
                                                        <i class="ph-bold ph-stop-circle"></i>
                                                    </button>

                                                    <button type="button"
                                                        wire:click="finalizarMedicamento('{{ $med->cod_med_adulto }}')"
                                                        wire:confirm="¿Finalizar el tratamiento {{ $med->nombre_medicamento }}?"
                                                        wire:loading.attr="disabled" wire:target="finalizarMedicamento"
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface)] text-[var(--rm-text-muted)] transition hover:border-estado-exitoBorde hover:bg-estado-exitoBg hover:text-estado-exito active:scale-90 disabled:opacity-50"
                                                        title="Finalizar tratamiento">
                                                        <i class="ph-bold ph-check-square-offset"></i>
                                                    </button>
                                                @endif

                                                @if(!$puedeEditar && !$puedeSuspender)
                                                    <span class="text-[9px] font-semibold text-[var(--rm-text-muted)]">Solo
                                                        lectura</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($medicaciones->hasPages())
                        <div class="border-t border-[var(--rm-border)]/60 px-4 py-3 sm:px-5">
                            {{ $medicaciones->links() }}
                        </div>
                    @endif
                @endif
            </section>

            <div class="flex items-start gap-3 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-bg-app)] p-4">
                <i class="ph-fill ph-shield-check mt-0.5 text-lg text-[var(--rm-primary)]"></i>
                <p class="text-[10px] font-semibold leading-relaxed text-[var(--rm-text-muted)]">
                    Las órdenes médicas y sus cambios se validan nuevamente en servidor. La administración de dosis
                    corresponde al flujo de Enfermería y se registra desde la agenda cuando existe una ocurrencia
                    programada.
                </p>
            </div>
        </main>
    </div>

    {{-- MODALES --}}
    <livewire:medicacion.medicacion-adulto-modal />
    <livewire:medicacion.administracion-medicacion-modal />

    {{-- Drawer de ubicación ya existente en el proyecto --}}
    @include('livewire.alertas.modales.drawer-ubicacion')
</div>