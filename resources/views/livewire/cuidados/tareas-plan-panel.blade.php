<div class="space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-titulo">Tareas de cuidado</h1>
            <p class="mt-1 text-sm text-apoyo">Programa cuidados, registra resultados y conserva la trazabilidad de cada cambio.</p>
        </div>
        @can('tareas.crear')<x-button wire:click="abrirCrear">Crear tarea</x-button>@endcan
    </div>

    @if(session('mensaje'))
        <div role="status" class="rounded-xl border border-estado-exitoBorde bg-estado-exitoBg px-4 py-3 text-sm font-semibold text-estado-exito">{{ session('mensaje') }}</div>
    @endif

    <section class="rounded-2xl border border-borde bg-fondo-panel p-4 shadow-sm">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <label class="space-y-1 text-xs font-bold text-apoyo">Buscar
                <input wire:model.live.debounce.300ms="search" class="rm-input w-full text-sm" placeholder="Tarea o residente">
            </label>
            <label class="space-y-1 text-xs font-bold text-apoyo">Estado
                <select wire:model.live="filtroEstado" class="rm-select w-full text-sm">
                    <option value="">Todos</option>
                    @foreach(['PENDIENTE' => 'Pendiente', 'EN_PROCESO' => 'En proceso', 'REALIZADA' => 'Realizada', 'OMITIDA' => 'Omitida', 'REPROGRAMADA' => 'Reprogramada'] as $valor => $etiqueta)
                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-1 text-xs font-bold text-apoyo">Turno
                <select wire:model.live="filtroTurno" class="rm-select w-full text-sm">
                    <option value="">Todos</option>
                    @foreach($turnos as $turno)<option value="{{ $turno->cod_turno }}">{{ $turno->nombre }}</option>@endforeach
                </select>
            </label>
            <label class="space-y-1 text-xs font-bold text-apoyo">Área
                <select wire:model.live="filtroArea" class="rm-select w-full text-sm">
                    <option value="">Todas</option>
                    @foreach(['SIGNOS','MEDICACION','MOVILIDAD','COGNITIVO','ALIMENTACION','HIDRATACION','HIGIENE','SUEÑO','SEGURIDAD','EMOCIONAL','FAMILIAR','REEVALUACION'] as $area)
                        <option value="{{ $area }}">{{ ucfirst(mb_strtolower(str_replace('_', ' ', $area))) }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </section>

    <div class="space-y-3">
        @forelse($tareas as $tarea)
            <article wire:key="tarea-{{ $tarea->cod_tarea }}" class="rounded-2xl border border-borde bg-fondo-panel p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-bold text-titulo">{{ $tarea->titulo }}</h2>
                            <span class="rounded-full border border-borde-suave bg-fondo-base px-2 py-0.5 text-[10px] font-bold text-apoyo">{{ ucfirst(mb_strtolower(str_replace('_', ' ', $tarea->estado))) }}</span>
                            <span class="rounded-full border border-borde-suave bg-fondo-base px-2 py-0.5 text-[10px] font-bold text-apoyo">Prioridad {{ mb_strtolower($tarea->prioridad) }}</span>
                        </div>
                        <a class="mt-1 inline-block text-sm font-semibold text-boton-principal hover:underline" href="{{ route('admin.adultos-mayores.show', $tarea->cod_am) }}">{{ $tarea->adultoMayor?->nombres }} {{ $tarea->adultoMayor?->ap_paterno }}</a>
                    </div>
                    @if($tarea->puedeCompletarse())
                        @can('tareas.registrar_resultado')<x-secondary-button wire:click="abrirResultado('{{ $tarea->cod_tarea }}')">Registrar resultado</x-secondary-button>@endcan
                    @endif
                </div>

                <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-2 lg:grid-cols-4">
                    <div><dt class="text-xs font-bold text-apoyo">Programación</dt><dd class="text-titulo">{{ $tarea->fecha_programada?->format('d/m/Y') }} {{ $tarea->hora_programada ? substr($tarea->hora_programada, 0, 5) : 'Sin hora' }}</dd></div>
                    <div><dt class="text-xs font-bold text-apoyo">Turno</dt><dd class="text-titulo">{{ $tarea->turno?->nombre ?? 'Sin turno' }}</dd></div>
                    <div><dt class="text-xs font-bold text-apoyo">Área</dt><dd class="text-titulo">{{ ucfirst(mb_strtolower(str_replace('_', ' ', $tarea->area))) }}</dd></div>
                    <div><dt class="text-xs font-bold text-apoyo">Responsable</dt><dd class="text-titulo">{{ $tarea->responsable?->name ?? 'Sin asignar' }}</dd></div>
                </dl>
                @if($tarea->descripcion)<p class="mt-3 whitespace-pre-wrap text-sm text-apoyo">{{ $tarea->descripcion }}</p>@endif
                @if($tarea->resultado)<p class="mt-3 text-sm text-titulo"><strong>Resultado:</strong> {{ $tarea->resultado }}</p>@endif
                @if($tarea->motivo_omision)<p class="mt-2 text-sm text-estado-advertencia"><strong>Motivo:</strong> {{ $tarea->motivo_omision }}</p>@endif
                @if($tarea->observacion)<p class="mt-2 text-sm text-apoyo"><strong>Observación:</strong> {{ $tarea->observacion }}</p>@endif
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-borde bg-fondo-panel p-8 text-center text-sm text-apoyo">No hay tareas que coincidan con los filtros seleccionados.</div>
        @endforelse
    </div>
    {{ $tareas->links() }}

    <x-dialog-modal wire:model="modalForm">
        <x-slot name="title">Nueva tarea de cuidado</x-slot>
        <x-slot name="content">
            <div class="max-h-[65vh] space-y-4 overflow-y-auto pr-1">
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="space-y-1 text-xs font-bold text-apoyo sm:col-span-2">Plan activo *
                        <select class="rm-select w-full text-sm" wire:model.live="codPlan"><option value="">Seleccione un plan</option>@foreach($planes as $plan)<option value="{{ $plan->cod_plan }}">{{ $plan->adultoMayor?->nombres }} {{ $plan->adultoMayor?->ap_paterno }} · {{ $plan->tipo_plan }} (v{{ $plan->version }})</option>@endforeach</select>
                        @error('codPlan')<span class="block text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-apoyo">Turno *
                        <select class="rm-select w-full text-sm" wire:model="codTurno"><option value="">Seleccione</option>@foreach($turnos as $turno)<option value="{{ $turno->cod_turno }}">{{ $turno->nombre }} · {{ $turno->horario }}</option>@endforeach</select>
                        @error('codTurno')<span class="block text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-apoyo">Responsable
                        <select class="rm-select w-full text-sm" wire:model="responsableId"><option value="">Sin responsable específico</option>@foreach($usuarios as $usuario)<option value="{{ $usuario->cod_usu }}">{{ $usuario->nombres }} {{ $usuario->ap_paterno }}</option>@endforeach</select>
                        @error('responsableId')<span class="block text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-apoyo">Área *
                        <select class="rm-select w-full text-sm" wire:model="area"><option value="">Seleccione</option>@foreach(['SIGNOS','MEDICACION','MOVILIDAD','COGNITIVO','ALIMENTACION','HIDRATACION','HIGIENE','SUEÑO','SEGURIDAD','EMOCIONAL','FAMILIAR','REEVALUACION'] as $valor)<option value="{{ $valor }}">{{ ucfirst(mb_strtolower(str_replace('_', ' ', $valor))) }}</option>@endforeach</select>
                        @error('area')<span class="block text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-apoyo">Prioridad *
                        <select class="rm-select w-full text-sm" wire:model="prioridad">@foreach(['BAJA' => 'Baja','NORMAL' => 'Normal','ALTA' => 'Alta','URGENTE' => 'Urgente'] as $valor => $etiqueta)<option value="{{ $valor }}">{{ $etiqueta }}</option>@endforeach</select>
                        @error('prioridad')<span class="block text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-apoyo sm:col-span-2">Título *
                        <input class="rm-input w-full text-sm" wire:model="titulo" maxlength="200" placeholder="Ej.: Cambiar posición y revisar integridad de la piel">
                        @error('titulo')<span class="block text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-apoyo sm:col-span-2">Indicaciones
                        <textarea class="rm-input w-full text-sm" rows="3" wire:model="descripcion" maxlength="2000" placeholder="Describa cómo debe realizarse el cuidado y qué debe observarse."></textarea>
                        @error('descripcion')<span class="block text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-apoyo">Fecha *
                        <input class="rm-input w-full text-sm" type="date" wire:model="fechaProgramada">
                        @error('fechaProgramada')<span class="block text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-apoyo">Hora
                        <input class="rm-input w-full text-sm" type="time" wire:model="horaProgramada">
                        @error('horaProgramada')<span class="block text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-apoyo sm:col-span-2">Frecuencia
                        <input class="rm-input w-full text-sm" wire:model="frecuencia" maxlength="100" placeholder="Ej.: Cada 2 horas durante el turno">
                        @error('frecuencia')<span class="block text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                </div>
            </div>
        </x-slot>
        <x-slot name="footer"><x-secondary-button wire:click="cerrarModales">Cancelar</x-secondary-button><x-button class="ms-3" wire:click="guardarTarea" wire:loading.attr="disabled"><span wire:loading.remove wire:target="guardarTarea">Guardar tarea</span><span wire:loading wire:target="guardarTarea">Guardando…</span></x-button></x-slot>
    </x-dialog-modal>

    <x-dialog-modal wire:model="modalResultado">
        <x-slot name="title">Registrar resultado de la tarea</x-slot>
        <x-slot name="content">
            <div class="space-y-4">
                <label class="space-y-1 text-xs font-bold text-apoyo">Resultado *
                    <select class="rm-select w-full text-sm" wire:model.live="estadoTarea"><option value="REALIZADA">Realizada</option>@can('tareas.omitir')<option value="OMITIDA">Omitida</option>@endcan<option value="REPROGRAMADA">Reprogramada</option></select>
                    @error('estadoTarea')<span class="block text-xs text-estado-peligro">{{ $message }}</span>@enderror
                </label>
                @if($estadoTarea === 'REALIZADA')
                    <label class="space-y-1 text-xs font-bold text-apoyo">Resultado clínico *
                        <textarea class="rm-input w-full text-sm" rows="3" wire:model="resultado" maxlength="2000" placeholder="Describa el cuidado realizado y la respuesta del residente."></textarea>
                        @error('resultado')<span class="block text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                @endif
                @if(in_array($estadoTarea, ['OMITIDA', 'REPROGRAMADA']))
                    <label class="space-y-1 text-xs font-bold text-apoyo">{{ $estadoTarea === 'REPROGRAMADA' ? 'Motivo de reprogramación' : 'Motivo de omisión' }} *
                        <textarea class="rm-input w-full text-sm" rows="3" wire:model="motivoOmision" maxlength="1000" placeholder="Explique la causa y las medidas adoptadas."></textarea>
                        @error('motivoOmision')<span class="block text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                @endif
                @if($estadoTarea === 'REPROGRAMADA')
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="space-y-1 text-xs font-bold text-apoyo">Nueva fecha *
                            <input class="rm-input w-full text-sm" type="date" min="{{ today()->format('Y-m-d') }}" wire:model="fechaProgramada">
                            @error('fechaProgramada')<span class="block text-xs text-estado-peligro">{{ $message }}</span>@enderror
                        </label>
                        <label class="space-y-1 text-xs font-bold text-apoyo">Nueva hora *
                            <input class="rm-input w-full text-sm" type="time" wire:model="horaProgramada">
                            @error('horaProgramada')<span class="block text-xs text-estado-peligro">{{ $message }}</span>@enderror
                        </label>
                    </div>
                @endif
                <label class="space-y-1 text-xs font-bold text-apoyo">Observaciones adicionales
                    <textarea class="rm-input w-full text-sm" rows="3" wire:model="observacion" maxlength="2000" placeholder="Registre novedades relevantes para el siguiente turno."></textarea>
                    @error('observacion')<span class="block text-xs text-estado-peligro">{{ $message }}</span>@enderror
                </label>
            </div>
        </x-slot>
        <x-slot name="footer"><x-secondary-button wire:click="cerrarModales">Cancelar</x-secondary-button><x-button class="ms-3" wire:click="guardarResultado" wire:loading.attr="disabled"><span wire:loading.remove wire:target="guardarResultado">Guardar resultado</span><span wire:loading wire:target="guardarResultado">Guardando…</span></x-button></x-slot>
    </x-dialog-modal>
</div>
