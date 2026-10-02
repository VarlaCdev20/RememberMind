<!-- rm-filter-bar -->
<div class="space-y-6 font-sans">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border)] p-5 sm:p-6 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary-ink)]">
                    <i class="ph-bold ph-calendar-check text-lg"></i>
                </span>
                <h1 class="text-2xl font-black tracking-tight text-[var(--rm-text-primary)]">Tareas de cuidado</h1>
            </div>
            <p class="mt-1 text-xs text-[var(--rm-text-secondary)]">Programa cuidados, registra resultados y conserva la trazabilidad de cada cambio en el turno.</p>
        </div>
        @can('ejecuciones_cuidado.gestionar')
            <button wire:click="abrirCrear" class="inline-flex items-center gap-2 rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition active:scale-[0.98]">
                <i class="ph-bold ph-plus-circle text-base"></i>
                <span>Crear tarea</span>
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
            <div class="lg:col-span-3 relative flex items-center">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </span>
                <input type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar tarea o residente..."
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
            <div class="lg:col-span-3">
                <select wire:model.live="filtroEstado"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="">Todos los estados</option>
                    @foreach(['PENDIENTE' => 'Pendiente', 'EN_PROCESO' => 'En proceso', 'REALIZADA' => 'Realizada', 'OMITIDA' => 'Omitida', 'REPROGRAMADA' => 'Reprogramada'] as $valor => $etiqueta)
                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Turno --}}
            <div class="lg:col-span-3">
                <select wire:model.live="filtroTurno"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="">Todos los turnos</option>
                    @foreach($turnos as $turno)
                        <option value="{{ $turno->cod_turno }}">{{ $turno->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Área --}}
            <div class="lg:col-span-3">
                <select wire:model.live="filtroArea"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="">Todas las áreas</option>
                    @foreach(['SIGNOS','MEDICACION','MOVILIDAD','COGNITIVO','ALIMENTACION','HIDRATACION','HIGIENE','SUEÑO','SEGURIDAD','EMOCIONAL','FAMILIAR','REEVALUACION'] as $area)
                        <option value="{{ $area }}">{{ ucfirst(mb_strtolower(str_replace('_', ' ', $area))) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @php
            $hasFiltrosActivos = !empty($search) || !empty($filtroEstado) || !empty($filtroTurno) || !empty($filtroArea);
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
                    @if(!empty($filtroTurno))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Turno seleccionado</span>
                            <button type="button" wire:click="$set('filtroTurno', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($filtroArea))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Área: {{ $filtroArea }}</span>
                            <button type="button" wire:click="$set('filtroArea', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                </div>
                <button type="button" wire:click="$set('search', ''); $set('filtroEstado', ''); $set('filtroTurno', ''); $set('filtroArea', '')" class="rm-filter-bar__clear-btn">
                    <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i>
                    Limpiar filtros
                </button>
            </div>
        @endif
    </x-ui.filter-bar>

    <div class="space-y-3">
        @forelse($tareas as $tarea)
            <article wire:key="tarea-{{ $tarea->cod_ejecucion }}" class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-sm hover:border-[var(--rm-border-hover)] transition">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-bold text-[var(--rm-text-primary)]">{{ $tarea->titulo }}</h2>
                            <span class="rounded-full border border-[var(--rm-border-soft)] bg-[var(--rm-surface-soft)] px-2.5 py-0.5 text-[10px] font-bold text-[var(--rm-text-secondary)]">{{ ucfirst(mb_strtolower(str_replace('_', ' ', $tarea->estado))) }}</span>
                            <span class="rounded-full border border-[var(--rm-border-soft)] bg-[var(--rm-surface-soft)] px-2.5 py-0.5 text-[10px] font-bold text-[var(--rm-text-secondary)]">Prioridad {{ mb_strtolower($tarea->intervencion?->prioridad ?? 'normal') }}</span>
                        </div>
                        <a class="mt-1 inline-block text-sm font-semibold text-[var(--rm-action-primary-ink)] hover:underline" href="{{ route('admin.adultos-mayores.show', $tarea->cod_residente) }}">{{ $tarea->adultoMayor?->nombres }} {{ $tarea->adultoMayor?->apellido_paterno }}</a>
                    </div>
                    @if($tarea->puedeCompletarse())
                        @can('ejecuciones_cuidado.gestionar')<x-secondary-button wire:click="abrirResultado('{{ $tarea->cod_ejecucion }}')" class="rounded-xl border-[var(--rm-border)] text-xs font-bold text-[var(--rm-text-primary)]">Registrar resultado</x-secondary-button>@endcan
                    @endif
                </div>

                <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-2 lg:grid-cols-4">
                    <div><dt class="text-xs font-bold text-[var(--rm-text-muted)]">Programación</dt><dd class="font-medium text-[var(--rm-text-primary)]">{{ $tarea->fecha_hora_programada?->format('d/m/Y H:i') ?? 'Sin fecha' }}</dd></div>
                    <div><dt class="text-xs font-bold text-[var(--rm-text-muted)]">Turno</dt><dd class="font-medium text-[var(--rm-text-primary)]">{{ $tarea->turno?->nombre ?? 'Sin turno' }}</dd></div>
                    <div><dt class="text-xs font-bold text-[var(--rm-text-muted)]">Área</dt><dd class="font-medium text-[var(--rm-text-primary)]">{{ $tarea->intervencion?->plan?->area?->nombre ?? 'Sin área' }}</dd></div>
                    <div><dt class="text-xs font-bold text-[var(--rm-text-muted)]">Responsable</dt><dd class="font-medium text-[var(--rm-text-primary)]">{{ $tarea->responsable?->nombre_completo ?? 'Sin asignar' }}</dd></div>
                </dl>
                @if($tarea->descripcion)<p class="mt-3 whitespace-pre-wrap text-sm text-[var(--rm-text-secondary)]">{{ $tarea->descripcion }}</p>@endif
                @if($tarea->resultado)<p class="mt-3 text-sm text-[var(--rm-text-primary)]"><strong class="font-bold text-[var(--rm-text-primary)]">Resultado:</strong> {{ $tarea->resultado }}</p>@endif
                @if($tarea->motivo_omision)<p class="mt-2 text-sm text-[var(--rm-warning)]"><strong class="font-bold text-[var(--rm-warning)]">Motivo:</strong> {{ $tarea->motivo_omision }}</p>@endif
                @if($tarea->observacion)<p class="mt-2 text-sm text-[var(--rm-text-secondary)]"><strong class="font-bold text-[var(--rm-text-primary)]">Observación:</strong> {{ $tarea->observacion }}</p>@endif
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-[var(--rm-border)] bg-[var(--rm-surface)] p-8 text-center text-sm text-[var(--rm-text-muted)]">No hay tareas que coincidan con los filtros seleccionados.</div>
        @endforelse
    </div>
    {{ $tareas->links() }}

    <x-dialog-modal wire:model="modalForm">
        <x-slot name="title">Nueva tarea de cuidado</x-slot>
        <x-slot name="content">
            <div class="max-h-[65vh] space-y-4 overflow-y-auto pr-1">
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="space-y-1 text-xs font-bold text-[var(--rm-text-secondary)] sm:col-span-2">Plan activo *
                        <select class="rm-select w-full text-sm" wire:model.live="codPlan"><option value="">Seleccione un plan</option>@foreach($planes as $plan)<option value="{{ $plan->cod_plan }}">{{ $plan->adultoMayor?->nombres }} {{ $plan->adultoMayor?->ap_paterno }} · {{ $plan->tipo_plan }} (v{{ $plan->version }})</option>@endforeach</select>
                        @error('codPlan')<span class="block text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-[var(--rm-text-secondary)]">Turno *
                        <select class="rm-select w-full text-sm" wire:model="codTurno"><option value="">Seleccione</option>@foreach($turnos as $turno)<option value="{{ $turno->cod_turno }}">{{ $turno->nombre }} · {{ $turno->horario }}</option>@endforeach</select>
                        @error('codTurno')<span class="block text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-[var(--rm-text-secondary)]">Responsable
                        <select class="rm-select w-full text-sm" wire:model="responsableId"><option value="">Sin responsable específico</option>@foreach($usuarios as $usuario)<option value="{{ $usuario->cod_usuario }}">{{ $usuario->nombres }} {{ $usuario->ap_paterno }}</option>@endforeach</select>
                        @error('responsableId')<span class="block text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-[var(--rm-text-secondary)]">Área *
                        <select class="rm-select w-full text-sm" wire:model="area"><option value="">Seleccione</option>@foreach(['SIGNOS','MEDICACION','MOVILIDAD','COGNITIVO','ALIMENTACION','HIDRATACION','HIGIENE','SUEÑO','SEGURIDAD','EMOCIONAL','FAMILIAR','REEVALUACION'] as $valor)<option value="{{ $valor }}">{{ ucfirst(mb_strtolower(str_replace('_', ' ', $valor))) }}</option>@endforeach</select>
                        @error('area')<span class="block text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-[var(--rm-text-secondary)]">Prioridad *
                        <select class="rm-select w-full text-sm" wire:model="prioridad">@foreach(['BAJA' => 'Baja','NORMAL' => 'Normal','ALTA' => 'Alta','URGENTE' => 'Urgente'] as $valor => $etiqueta)<option value="{{ $valor }}">{{ $etiqueta }}</option>@endforeach</select>
                        @error('prioridad')<span class="block text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-[var(--rm-text-secondary)] sm:col-span-2">Título *
                        <input class="rm-input w-full text-sm" wire:model="titulo" maxlength="200" placeholder="Ej.: Cambiar posición y revisar integridad de la piel">
                        @error('titulo')<span class="block text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-[var(--rm-text-secondary)] sm:col-span-2">Indicaciones
                        <textarea class="rm-input w-full text-sm" rows="3" wire:model="descripcion" maxlength="2000" placeholder="Describa cómo debe realizarse el cuidado y qué debe observarse."></textarea>
                        @error('descripcion')<span class="block text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-[var(--rm-text-secondary)]">Fecha *
                        <input class="rm-input w-full text-sm" type="date" wire:model="fechaProgramada">
                        @error('fechaProgramada')<span class="block text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-[var(--rm-text-secondary)]">Hora
                        <input class="rm-input w-full text-sm" type="time" wire:model="horaProgramada">
                        @error('horaProgramada')<span class="block text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-[var(--rm-text-secondary)] sm:col-span-2">Frecuencia
                        <input class="rm-input w-full text-sm" wire:model="frecuencia" maxlength="100" placeholder="Ej.: Cada 2 horas durante el turno">
                        @error('frecuencia')<span class="block text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror
                    </label>
                </div>
            </div>
        </x-slot>
        <x-slot name="footer"><x-secondary-button wire:click="cerrarModales" class="rounded-xl border-[var(--rm-border)] text-xs font-bold">Cancelar</x-secondary-button><button class="ms-3 inline-flex items-center justify-center rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] px-4 py-2 text-xs font-bold text-white shadow-sm transition active:scale-[0.98]" wire:click="guardarTarea" wire:loading.attr="disabled"><span wire:loading.remove wire:target="guardarTarea">Guardar tarea</span><span wire:loading wire:target="guardarTarea">Guardando...</span></button></x-slot>
    </x-dialog-modal>

    <x-dialog-modal wire:model="modalResultado">
        <x-slot name="title">Registrar resultado de la tarea</x-slot>
        <x-slot name="content">
            <div class="space-y-4">
                <label class="space-y-1 text-xs font-bold text-[var(--rm-text-secondary)]">Resultado *
                    <select class="rm-select w-full text-sm" wire:model.live="estadoTarea"><option value="REALIZADA">Realizada</option>@can('ejecuciones_cuidado.gestionar')<option value="OMITIDA">Omitida</option>@endcan<option value="REPROGRAMADA">Reprogramada</option></select>
                    @error('estadoTarea')<span class="block text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror
                </label>
                @if($estadoTarea === 'REALIZADA')
                    <label class="space-y-1 text-xs font-bold text-[var(--rm-text-secondary)]">Resultado clínico *
                        <textarea class="rm-input w-full text-sm" rows="3" wire:model="resultado" maxlength="2000" placeholder="Describa el cuidado realizado y la respuesta del residente."></textarea>
                        @error('resultado')<span class="block text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror
                    </label>
                @endif
                @if(in_array($estadoTarea, ['OMITIDA', 'REPROGRAMADA']))
                    <label class="space-y-1 text-xs font-bold text-[var(--rm-text-secondary)]">{{ $estadoTarea === 'REPROGRAMADA' ? 'Motivo de reprogramación' : 'Motivo de omisión' }} *
                        <textarea class="rm-input w-full text-sm" rows="3" wire:model="motivoOmision" maxlength="1000" placeholder="Explique la causa y las medidas adoptadas."></textarea>
                        @error('motivoOmision')<span class="block text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror
                    </label>
                @endif
                @if($estadoTarea === 'REPROGRAMADA')
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="space-y-1 text-xs font-bold text-[var(--rm-text-secondary)]">Nueva fecha *
                            <input class="rm-input w-full text-sm" type="date" min="{{ today()->format('Y-m-d') }}" wire:model="fechaProgramada">
                            @error('fechaProgramada')<span class="block text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror
                        </label>
                        <label class="space-y-1 text-xs font-bold text-[var(--rm-text-secondary)]">Nueva hora *
                            <input class="rm-input w-full text-sm" type="time" wire:model="horaProgramada">
                            @error('horaProgramada')<span class="block text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror
                        </label>
                    </div>
                @endif
                <label class="space-y-1 text-xs font-bold text-[var(--rm-text-secondary)]">Observaciones adicionales
                    <textarea class="rm-input w-full text-sm" rows="3" wire:model="observacion" maxlength="2000" placeholder="Registre novedades relevantes para el siguiente turno."></textarea>
                    @error('observacion')<span class="block text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror
                </label>
            </div>
        </x-slot>
        <x-slot name="footer"><x-secondary-button wire:click="cerrarModales" class="rounded-xl border-[var(--rm-border)] text-xs font-bold">Cancelar</x-secondary-button><button class="ms-3 inline-flex items-center justify-center rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] px-4 py-2 text-xs font-bold text-white shadow-sm transition active:scale-[0.98]" wire:click="guardarResultado" wire:loading.attr="disabled"><span wire:loading.remove wire:target="guardarResultado">Guardar resultado</span><span wire:loading wire:target="guardarResultado">Guardando...</span></button></x-slot>
    </x-dialog-modal>
</div>