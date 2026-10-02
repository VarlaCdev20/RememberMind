<!-- rm-filter-bar -->
<div class="space-y-6 font-sans">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border)] p-5 sm:p-6 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary-ink)]">
                    <i class="ph-bold ph-user-switch text-lg"></i>
                </span>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[var(--rm-action-primary-ink)]">Jornadas asistenciales</p>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-[var(--rm-text-primary)]">Asignaciones de turno y ocupación</h1>
            <p class="text-xs text-[var(--rm-text-secondary)]">Vinculación de residentes a enfermeros responsables durante cada jornada institucional.</p>
        </div>
        @can('turnos.asignar')
            <button wire:click="abrirCrear" class="inline-flex items-center gap-2 rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition active:scale-[0.98]">
                <i class="ph-bold ph-plus-circle text-base"></i>
                <span>Asignar residente</span>
            </button>
        @endcan
    </div>

    @if(session('mensaje'))
        <div role="status" class="flex items-center gap-2 rounded-xl border border-[var(--rm-action-primary)]/40 bg-[var(--rm-action-primary-soft)] px-4 py-3 text-xs font-bold text-[var(--rm-action-primary-ink)]">
            <i class="ph-bold ph-check-circle text-base"></i>
            <span>{{ session('mensaje') }}</span>
        </div>
    @endif

        <x-ui.filter-bar class="mb-4">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
            {{-- Búsqueda textual --}}
            <div class="lg:col-span-8 relative flex items-center">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </span>
                <input type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por residente o personal..."
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]" />
                @if(!empty($search))
                    <button type="button"
                        wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] cursor-pointer"
                        title="Limpiar búsqueda">
                        <i class="ph-bold ph-x-circle text-base"></i>
                    </button>
                @endif
            </div>

            {{-- Estado --}}
            <div class="lg:col-span-4">
                <select wire:model.live="filtroEstado"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="">Todos los estados</option>
                    <option value="ACTIVA">Activa</option>
                    <option value="FINALIZADA">Finalizada</option>
                    <option value="ANULADA">Anulada</option>
                </select>
            </div>
        </div>

        @php
            $hasFiltrosActivos = !empty($search) || !empty($filtroEstado);
        @endphp
        @if($hasFiltrosActivos)
            <div class="rm-filter-bar__active">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="rm-filter-bar__active-label">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>
                    @if(!empty($search))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Búsqueda: "{{ Str::limit($search, 16) }}"</span>
                            <button type="button" wire:click="$set('search', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($filtroEstado))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Estado: {{ $filtroEstado }}</span>
                            <button type="button" wire:click="$set('filtroEstado', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                </div>
                <button type="button" wire:click="$set('search', ''); $set('filtroEstado', '')" class="rm-filter-bar__clear-btn">
                    <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i>
                    Limpiar filtros
                </button>
            </div>
        @endif
    </x-ui.filter-bar>

    <div class="space-y-3">
        @forelse($asignaciones as $asignacion)
            <article class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm hover:border-[var(--rm-border-hover)] transition" wire:key="asignacion-{{ $asignacion->cod_asignacion }}">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <a class="text-base font-bold text-[var(--rm-text-primary)] hover:text-[var(--rm-action-primary-ink)] hover:underline transition" href="{{ route('admin.adultos-mayores.show', $asignacion->cod_residente) }}">
                                {{ $asignacion->residente?->nombres }} {{ $asignacion->residente?->apellido_paterno }}
                            </a>
                            <span class="rounded-full border px-2.5 py-0.5 text-[10px] font-bold {{ $asignacion->estado === 'ACTIVA' ? 'border-[var(--rm-action-primary)]/40 bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary-ink)]' : 'border-[var(--rm-border)] bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)]' }}">
                                {{ $asignacion->estado }}
                            </span>
                            @if($asignacion->nivel_supervision)
                                <span class="rounded-full border border-[var(--rm-border-soft)] bg-[var(--rm-surface-soft)] px-2.5 py-0.5 text-[10px] font-bold text-[var(--rm-text-secondary)]">
                                    Supervisión {{ strtolower($asignacion->nivel_supervision) }}
                                </span>
                            @endif
                        </div>
                        <div class="mt-2 grid gap-2 text-xs text-[var(--rm-text-secondary)] sm:grid-cols-2 lg:grid-cols-3">
                            <div><strong class="text-[var(--rm-text-primary)]">Turno:</strong> {{ $asignacion->jornada?->turno?->nombre ?? 'Sin turno' }} · {{ $asignacion->personal?->usuario?->name ?? 'Sin asignar' }}</div>
                            <div><strong class="text-[var(--rm-text-primary)]">Ubicación:</strong> Habitación {{ $asignacion->residente?->ocupacionActiva?->cama?->habitacion?->codigo ?? 'S/H' }} / Cama {{ $asignacion->residente?->ocupacionActiva?->cama?->codigo ?? 'S/C' }}</div>
                            <div><strong class="text-[var(--rm-text-primary)]">Fecha:</strong> {{ $asignacion->jornada?->fecha_jornada?->format('d/m/Y') ?? 'N/D' }}</div>
                        </div>
                        @if($asignacion->observacion)
                            <p class="mt-2 text-xs text-[var(--rm-text-muted)]">{{ $asignacion->observacion }}</p>
                        @endif
                    </div>
                    @if($asignacion->estado === 'ACTIVA')
                        @can('turnos.finalizar')
                            <x-secondary-button wire:click="finalizarAsignacion('{{ $asignacion->cod_asignacion }}')" wire:confirm="¿Finalizar esta asignación?" class="rounded-xl border-[var(--rm-border)] text-xs font-bold shrink-0">
                                Finalizar
                            </x-secondary-button>
                        @endcan
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-[var(--rm-border)] bg-[var(--rm-surface)] p-8 text-center text-sm text-[var(--rm-text-muted)]">No hay asignaciones que coincidan con los filtros seleccionados.</div>
        @endforelse
    </div>

    {{ $asignaciones->links() }}

    <x-dialog-modal wire:model="modalForm">
        <x-slot name="title">Asignar residente a turno y cama</x-slot>
        <x-slot name="content">
            <div class="space-y-4">
                <x-validation-errors />
                <label class="block space-y-1 text-xs font-bold text-[var(--rm-text-secondary)]">Residente *
                    <select wire:model="codResidente" class="rm-select w-full text-sm">
                        <option value="">Seleccione</option>
                        @foreach($adultos as $adulto)
                            <option value="{{ $adulto->cod_residente }}">{{ $adulto->nombres }} {{ $adulto->apellido_paterno }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block space-y-1 text-xs font-bold text-[var(--rm-text-secondary)]">Turno *
                        <select wire:model="codTurno" class="rm-select w-full text-sm">
                            <option value="">Seleccione</option>
                            @foreach($turnos as $turno)
                                <option value="{{ $turno->cod_turno }}">{{ $turno->nombre }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block space-y-1 text-xs font-bold text-[var(--rm-text-secondary)]">Responsable *
                        <select wire:model="codEnfermero" class="rm-select w-full text-sm">
                            <option value="">Seleccione</option>
                            @foreach($enfermeros as $u)
                                <option value="{{ $u->cod_usuario }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <div class="rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-surface-soft)] p-3 text-xs text-[var(--rm-text-muted)]">
                    <i class="ph-bold ph-info text-sm text-[var(--rm-action-primary-ink)] inline mr-1"></i> La habitación y cama provienen de la admisión formal y no se modifican desde esta pantalla.
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block space-y-1 text-xs font-bold text-[var(--rm-text-secondary)]">Fecha de inicio *
                        <input type="date" wire:model="fechaInicio" class="rm-input w-full text-sm" />
                    </label>
                    <label class="block space-y-1 text-xs font-bold text-[var(--rm-text-secondary)]">Supervisión *
                        <select wire:model="nivelSupervision" class="rm-select w-full text-sm">
                            @foreach(['MINIMO','ESTANDAR','INTENSIVO','CRITICO'] as $n)
                                <option value="{{ $n }}">{{ ucfirst(strtolower($n)) }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <label class="block space-y-1 text-xs font-bold text-[var(--rm-text-secondary)]">Motivo *
                    <textarea class="rm-input w-full text-sm" rows="3" wire:model="motivoAsignacion" placeholder="Detalle el motivo o plan de asignación..."></textarea>
                </label>
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="cerrarModales" class="rounded-xl border-[var(--rm-border)] text-xs font-bold">Cancelar</x-secondary-button>
            <button wire:click="guardar" wire:loading.attr="disabled" class="ms-3 inline-flex items-center gap-2 rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] px-4 py-2 text-xs font-bold text-white shadow-sm transition active:scale-[0.98]">
                <span>Guardar asignación</span>
            </button>
        </x-slot>
    </x-dialog-modal>
</div>