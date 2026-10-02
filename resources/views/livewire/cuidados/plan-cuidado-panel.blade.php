<!-- rm-filter-bar -->
<div class="space-y-6">
<x-validation-errors />
    <div class="flex flex-col gap-4 rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-[var(--rm-text-secondary)]">Enfermería</p>
            <h1 class="text-2xl font-bold text-[var(--rm-text-primary)]">Plan de cuidado</h1>
            <p class="text-sm font-semibold text-[var(--rm-text-secondary)]">Planes activos, tareas vinculadas y estado de cuidado por adulto mayor.</p>
        </div>
        @can('planes_cuidado.crear')
        <button type="button" wire:click="abrirCrear" class="rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] px-4 py-2 text-xs font-bold uppercase tracking-wider text-white shadow-sm">
            Crear plan
        </button>
        @endcan
    </div>

    <x-ui.filter-bar class="mb-4">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
            {{-- Buscador Principal formato alertas --}}
            <div class="lg:col-span-8 relative flex items-center">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </span>
                <input type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Buscar adulto mayor..."
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]" />
                @if($search !== '')
                    <button type="button"
                        wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] cursor-pointer"
                        title="Limpiar búsqueda">
                        <i class="ph-bold ph-x-circle text-base"></i>
                    </button>
                @endif
            </div>

            {{-- Filtro Estado --}}
            <div class="lg:col-span-4">
                <select wire:model.live="filtroEstado"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer">
                    <option value="">Todos los estados</option>
                    <option value="BORRADOR">Borrador</option>
                    <option value="ACTIVO">Activo</option>
                    <option value="CERRADO">Cerrado</option>
                </select>
            </div>
        </div>

        {{-- Chips de filtros activos formato alertas --}}
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
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-warning-soft)] border border-[var(--rm-warning)] text-[11px] font-bold text-[var(--rm-warning)]">
                            <span>Estado: {{ $filtroEstado }}</span>
                            <button type="button" wire:click="$set('filtroEstado', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-2.5">
                    <button type="button"
                        wire:click="$set('search', ''); $set('filtroEstado', '');"
                        class="inline-flex items-center gap-1 rounded-xl bg-[var(--rm-primary-soft)] hover:bg-[var(--rm-primary)] hover:text-white text-[var(--rm-primary)] border border-[var(--rm-primary)]/30 py-1 px-2.5 text-xs font-bold transition cursor-pointer">
                        <i class="ph-bold ph-arrow-counter-clockwise"></i>
                        <span>Limpiar filtros</span>
                    </button>
                </div>
            </div>
        @endif
    </x-ui.filter-bar>

    <div class="grid gap-4 lg:grid-cols-2">
        @forelse($planes as $plan)
            <article class="rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-[var(--rm-text-primary)]">{{ $plan->adultoMayor?->nombres }} {{ $plan->adultoMayor?->ap_paterno }}</h2>
                        <p class="text-xs font-bold uppercase tracking-wider text-[var(--rm-text-secondary)]">{{ $plan->tipo_plan }} v{{ $plan->version }} / {{ $plan->nivel_cuidado }}</p>
                    </div>
                    <span class="rounded-full border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-3 py-1 text-xs font-bold text-[var(--rm-text-body)]">{{ $plan->estado }}</span>
                </div>
                <p class="mt-3 text-sm font-semibold text-[var(--rm-text-secondary)]">{{ $plan->resumen ?: 'Sin resumen clínico registrado.' }}</p>
                <div class="mt-4 grid gap-3 text-xs font-bold text-[var(--rm-text-body)] sm:grid-cols-3">
                    <div class="rounded-xl bg-[var(--rm-surface-soft)] p-3"><span class="block text-[var(--rm-text-secondary)]">Inicio</span>{{ optional($plan->fecha_inicio)->format('d/m/Y') }}</div>
                    <div class="rounded-xl bg-[var(--rm-surface-soft)] p-3"><span class="block text-[var(--rm-text-secondary)]">Fin</span>{{ optional($plan->fecha_fin)->format('d/m/Y') ?: 'Vigente' }}</div>
                    <div class="rounded-xl bg-[var(--rm-surface-soft)] p-3"><span class="block text-[var(--rm-text-secondary)]">Tareas activas</span>{{ $plan->tareas_activas_count }}</div>
                </div>
                @if($plan->estado === 'BORRADOR') @can('planes_cuidado.editar')<x-secondary-button wire:click="editarBorrador('{{ $plan->cod_plan }}')">Editar / activar borrador</x-secondary-button>@endcan @endif
                @if($plan->estado === 'ACTIVO')
                    @can('planes_cuidado.cerrar')
                    <button type="button" wire:click="cerrarPlan('{{ $plan->cod_plan }}')" class="mt-4 rounded-xl border border-[var(--rm-border)] px-4 py-2 text-xs font-bold text-[var(--rm-text-body)]">Cerrar plan</button>
                    @endcan
                @endif
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-[var(--rm-border)] bg-[var(--rm-surface)] p-10 text-center text-sm font-bold text-[var(--rm-text-secondary)] lg:col-span-2">No hay planes de cuidado registrados.</div>
        @endforelse
    </div>

    {{ $planes->links() }}

    @if($modalForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <form wire:submit.prevent="guardar" class="w-full max-w-3xl space-y-4 rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-6 shadow-xl">
                <div class="flex items-center justify-between"><h2 class="text-lg font-bold text-[var(--rm-text-primary)]">Crear plan de cuidado</h2><button type="button" wire:click="cerrarModales" class="text-sm font-bold text-[var(--rm-text-secondary)]">Cerrar</button></div>
                <div class="grid gap-3 md:grid-cols-2">
                    <select wire:model="codAm" class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-3 py-2 text-sm"><option value="">Adulto mayor</option>@foreach($adultos as $adulto)<option value="{{ $adulto->cod_residente }}">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</option>@endforeach</select>
                    <select wire:model="tipoPlan" class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-3 py-2 text-sm"><option value="INICIAL">Inicial</option><option value="AJUSTE">Ajuste</option><option value="REEVALUACION">Reevaluación</option></select>
                    <select wire:model="nivelCuidado" class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-3 py-2 text-sm"><option value="PREVENTIVO">Preventivo</option><option value="ESTANDAR">Estándar</option><option value="INTENSIVO">Intensivo</option><option value="PALIATIVO">Paliativo</option></select>
                    <select wire:model="estadoPlan" class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-3 py-2 text-sm"><option value="BORRADOR">Borrador</option><option value="ACTIVO">Activo</option></select>
                    <input type="date" wire:model="fechaInicio" class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-3 py-2 text-sm">
                    <input type="date" wire:model="fechaFin" class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-3 py-2 text-sm">
                </div>
                <textarea wire:model="resumen" rows="4" placeholder="Resumen del plan" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-3 py-2 text-sm"></textarea>
                <div class="flex justify-end gap-2"><button type="button" wire:click="cerrarModales" class="rounded-xl border border-[var(--rm-border)] px-4 py-2 text-xs font-bold">Cancelar</button><button type="submit" class="rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] px-4 py-2 text-xs font-bold text-white">Guardar</button></div>
            </form>
        </div>
    @endif
</div>
