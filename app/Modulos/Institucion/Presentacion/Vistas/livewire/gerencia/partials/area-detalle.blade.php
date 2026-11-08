<x-ui.modal-livewire id="gerencia-area-equipo" wire:model="mostrarFicha" title="Área y equipo"
    icon="ph-buildings" max-width="4xl" close-method="cerrarFicha" surface="reading"
    class="rm-record-form-dialog rm-area-detail" :show-validation="false">
    <section class="rm-area-detail__overview" aria-labelledby="area-detalle-nombre">
        <div class="rm-area-detail__image">
            @if($puedeVerFotos && $areaActual->fotografiaPrincipal?->estado === 'ACTIVA')
                <img src="{{ route('admin.galeria.archivo', [$areaActual->fotografiaPrincipal->getKey(), 'imagen']) }}"
                    alt="{{ $areaActual->fotografiaPrincipal->texto_alternativo ?: $areaActual->fotografiaPrincipal->titulo }}">
            @else
                <i class="ph-bold ph-buildings" aria-hidden="true"></i>
            @endif
        </div>
        <div class="rm-area-detail__purpose">
            <div class="rm-area-detail__identity">
                <h3 id="area-detalle-nombre">{{ $areaActual->nombre }}</h3>
                <x-ui.status-badge :estado="$areaActual->estado" :label="in_array($areaActual->estado, ['ACTIVO', 'ACTIVA'], true) ? 'Área activa' : 'Área inactiva'" />
                @can('areas.editar')
                    <x-ui.action-button variant="secondary" icono="ph-pencil-simple" loading="editarArea" class="sm:ml-auto"
                        wire:click="editarArea('{{ $areaActual->getKey() }}')">Editar área</x-ui.action-button>
                @endcan
            </div>
            <h4>¿De qué se encarga?</h4>
            <p class="rm-area-detail__description">{{ filled($areaActual->descripcion) ? $areaActual->descripcion : 'Todavía no se registró la descripción funcional de esta área.' }}</p>
        </div>
    </section>

    <section class="rm-area-detail__team" aria-labelledby="area-detalle-equipo">
        <header class="rm-area-detail__team-heading">
            <div>
                <h4 id="area-detalle-equipo"><i class="ph-bold ph-users-three" aria-hidden="true"></i> Equipo por jornada</h4>
                <p>Personas y funciones registradas para la fecha elegida.</p>
            </div>
            @if($puedeVerEquipo)
                <x-ui.field label="Fecha del equipo" for="gerencia-area-fecha" error="fechaEquipo">
                    <input id="gerencia-area-fecha" type="date" class="rm-input" wire:model.live="fechaEquipo" required
                        aria-invalid="{{ $errors->has('fechaEquipo') ? 'true' : 'false' }}"
                        @if($errors->has('fechaEquipo')) aria-describedby="gerencia-area-fecha-error" @endif>
                </x-ui.field>
            @endif
        </header>
        @if(!$puedeVerEquipo)
            <x-ui.callout title="Consulta de equipo restringida" variant="info">La consulta requiere acceso a personal, jornadas y asignaciones.</x-ui.callout>
        @elseif($equipo !== null)
            <div class="rm-area-detail__dates" aria-label="Otras jornadas del área">
                @if($equipo['fechasArea']['anterior'])
                    <x-ui.action-button variant="ghost" icono="ph-caret-left" wire:click="$set('fechaEquipo', '{{ $equipo['fechasArea']['anterior'] }}')">Anterior con equipo · {{ \Carbon\Carbon::parse($equipo['fechasArea']['anterior'])->format('d/m/Y') }}</x-ui.action-button>
                @endif
                @if($fechaEquipo !== today()->toDateString())
                    <x-ui.action-button variant="secondary" wire:click="$set('fechaEquipo', '{{ today()->toDateString() }}')">Hoy</x-ui.action-button>
                @endif
                @if($equipo['fechasArea']['siguiente'])
                    <x-ui.action-button variant="ghost" icono="ph-caret-right" wire:click="$set('fechaEquipo', '{{ $equipo['fechasArea']['siguiente'] }}')">Siguiente con equipo · {{ \Carbon\Carbon::parse($equipo['fechasArea']['siguiente'])->format('d/m/Y') }}</x-ui.action-button>
                @endif
            </div>
            <dl class="rm-area-detail__summary">
                <div><dt>Personas asignadas</dt><dd>{{ $equipo['resumen']['personas'] }}</dd></div>
                <div><dt>Turnos</dt><dd>{{ $equipo['resumen']['turnos'] }}</dd></div>
                <div><dt>Asignaciones</dt><dd>{{ $equipo['resumen']['asignaciones'] }}</dd></div>
            </dl>
            @if(!$equipo['filas'])
                <x-ui.empty-state title="Sin asignaciones para esta fecha" description="Consulta otra jornada registrada o revisa la cobertura del equipo." />
            @else
                <ul class="rm-area-detail__people" aria-label="Personal asignado al área">
                    @foreach($equipo['filas'] as $fila)
                        <li wire:key="area-equipo-{{ $fila['cod_asignacion'] }}">
                            <x-ui.person-entity-card entity="staff" class="rm-area-detail__person">
                                <div class="rm-area-detail__worker">
                                    <span class="rm-area-detail__initial" aria-hidden="true">{{ mb_strtoupper(mb_substr($fila['persona'], 0, 1)) }}</span>
                                    <div>
                                        <h5>{{ $fila['persona'] }}</h5>
                                        <p>{{ $fila['profesion'] ?: 'Sin profesión registrada' }}</p>
                                        @if($fila['estado_personal'] !== 'ACTIVO')
                                            <x-ui.status-badge variant="warning" label="Revisar situación laboral" />
                                        @endif
                                    </div>
                                </div>
                                <div class="rm-area-detail__function">
                                    <span class="rm-area-detail__label">Función asignada</span>
                                    <p>{{ filled($fila['funcion_asignada']) ? $fila['funcion_asignada'] : 'Sin función específica registrada' }}</p>
                                    <small>Tipo de asignación · {{ $fila['tipo_asignacion'] ?: 'Sin registrar' }}</small>
                                </div>
                                <div class="rm-area-detail__shift">
                                    <strong>{{ $fila['turno'] }}</strong>
                                    <p>{{ $fila['inicio'] }} → {{ $fila['fin'] }}</p>
                                    <span>Jornada {{ mb_strtolower($fila['estado_jornada']) }}</span>
                                </div>
                            </x-ui.person-entity-card>
                        </li>
                    @endforeach
                </ul>
            @endif
        @else
            <p class="rm-help">Elige una fecha válida para consultar las asignaciones.</p>
        @endif
    </section>
    <x-slot:footer>
        @if($puedeVerEquipo && $equipo !== null)
            <a class="rm-btn rm-btn-secondary" href="{{ route('admin.cobertura-equipo.index', ['area' => $areaActual->getKey(), 'fecha' => $fechaEquipo]) }}">Ver cobertura completa</a>
        @endif
        <x-ui.action-button variant="primary" wire:click="cerrarFicha">Volver a áreas</x-ui.action-button>
    </x-slot:footer>
</x-ui.modal-livewire>
