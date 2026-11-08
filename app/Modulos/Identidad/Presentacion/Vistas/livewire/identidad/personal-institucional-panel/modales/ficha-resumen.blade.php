@php
    $titular = $fichaPersonal['usuario'];
    $personal = $titular->personal;
    $asignaciones = $fichaPersonal['asignaciones'];
    $archivos = $fichaPersonal['archivos'];
    $pendientes = $fichaPersonal['pendientes'];
    $secciones = [
        'identidad' => ['titulo' => 'Identidad y contacto', 'icono' => 'ph-identification-card', 'datos' => [
            'Correo' => $titular->correo,
            'Celular' => $personal?->telefono,
            'Dirección' => $personal?->direccion,
            'Número de documento' => $personal?->numero_documento,
            'Tipo de documento' => $personal?->tipo_documento,
            'Fecha de nacimiento' => $personal?->fecha_nacimiento?->format('d/m/Y'),
            'Edad' => $fichaPersonal['edad'] !== null ? $fichaPersonal['edad'].' años' : null,
            'Género' => match ($personal?->genero) {'F' => 'Femenino', 'M' => 'Masculino', default => $personal?->genero},
            'País emisor' => $personal?->pais_documento,
            'Expedido en' => $personal?->expedicion_documento,
        ]],
        'laboral' => ['titulo' => 'Información laboral', 'icono' => 'ph-briefcase', 'datos' => [
            'Profesión o función' => $personal?->profesion,
            'Especialidad' => $personal?->especialidad,
            'Fecha de ingreso' => $personal?->fecha_ingreso?->format('d/m/Y'),
            'Tiempo desde el ingreso' => $fichaPersonal['antiguedad'],
            'Alta de la cuenta' => $titular->fecha_hora_alta?->format('d/m/Y H:i'),
            'Matrícula profesional' => $personal?->matricula_profesional,
            'Observaciones' => $personal?->observacion,
        ]],
    ];
@endphp

<x-ui.modal-livewire id="gerencia-personal-resumen" wire:model="modalFichaAbierto"
    title="Ficha del trabajador" icon="ph-identification-card" max-width="5xl" surface="reading" class="rm-staff-record-dialog"
    close-method="cerrarFichaPersonal" :show-validation="false">
    <div class="rm-staff-record">
        <header class="rm-staff-record__profile">
            <x-ui.resident-card-identity :name="$titular->name" :code="$titular->getKey()" :photo="$titular->foto" :secondary="$titular->roles->pluck('name')->join(' · ')" mode="row" />
            <dl class="rm-staff-record__statuses">
                <div><dt>Estado laboral</dt><dd><x-ui.status-badge :estado="$personal?->estado ?? ''" :label="$personal ? null : 'Sin ficha laboral'" /></dd></div>
                <div><dt>Acceso a la cuenta</dt><dd><x-ui.status-badge :estado="$titular->estado" /></dd></div>
            </dl>
        </header>

        <dl class="rm-staff-record__overview">
            @can('asignaciones_personal.ver')
                <div><dt>Asignaciones de hoy y próximas</dt><dd>{{ $fichaPersonal['totalAsignaciones'] }}</dd></div>
            @endcan
            <div><dt>Obligatorios sin archivo</dt><dd>{{ $fichaPersonal['faltantesObligatorios'] }}</dd></div>
            <div><dt>Recibidos para revisión</dt><dd>{{ $fichaPersonal['porRevisar'] }}</dd></div>
        </dl>

        <div class="rm-staff-record__sections">
            @foreach($secciones as $clave => $seccion)
                <x-ui.section-card :title="$seccion['titulo']" :icon="$seccion['icono']">
                    <dl class="rm-staff-record__fields">
                        @foreach($seccion['datos'] as $etiqueta => $valor)
                            <div @class([
                                'rm-staff-record__field--wide' => in_array($etiqueta, ['Dirección', 'Profesión o función', 'Observaciones'], true),
                                'rm-staff-record__field--primary' => in_array($etiqueta, ['Correo', 'Celular', 'Número de documento', 'Profesión o función'], true),
                            ])>
                                <dt>{{ $etiqueta }}</dt>
                                <dd @class(['rm-staff-record__empty' => blank($valor)])>{{ filled($valor) ? $valor : 'Sin registrar' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </x-ui.section-card>
            @endforeach

            @can('asignaciones_personal.ver')
                <x-ui.section-card title="Horarios" icon="ph-calendar-blank" class="rm-staff-record__schedule">
                    <p class="rm-help">Hoy y próximas jornadas abiertas</p>
                    <ul class="rm-staff-record__list">
                        @forelse($asignaciones as $asignacion)
                            <li class="rm-staff-record__file">
                                <div>
                                    <strong>{{ $asignacion->jornada?->fecha_jornada?->format('d/m/Y') }} · {{ $asignacion->area?->nombre ?? 'Área sin registrar' }}</strong>
                                    <p>{{ $asignacion->jornada?->turno?->nombre ?? 'Turno sin registrar' }}
                                        @if($asignacion->hora_inicio && $asignacion->hora_fin)
                                            · {{ substr($asignacion->hora_inicio, 0, 5) }}–{{ substr($asignacion->hora_fin, 0, 5) }}
                                        @endif
                                    </p>
                                    @if($asignacion->funcion)<p class="rm-help">Función {{ $asignacion->funcion }}</p>@endif
                                </div>
                            </li>
                        @empty
                            <li class="rm-help">Sin asignaciones para hoy o próximas jornadas abiertas.</li>
                        @endforelse
                    </ul>
                    @if($fichaPersonal['totalAsignaciones'] > $asignaciones->count())
                        <p class="rm-help">Mostrando {{ $asignaciones->count() }} de {{ $fichaPersonal['totalAsignaciones'] }} asignaciones.</p>
                    @endif
                    <x-ui.action-button variant="secondary" class="rm-staff-record__action" wire:click="abrirHorariosDesdeFicha" icono="ph-arrow-right">Consultar horarios</x-ui.action-button>
                </x-ui.section-card>
            @endcan

            <x-ui.section-card title="Documentación" icon="ph-folder-open" class="rm-staff-record__documents">
                <p class="rm-staff-record__counts">
                    {{ $archivos->count() }} {{ $archivos->count() === 1 ? 'archivo registrado' : 'archivos registrados' }}
                    @if($ampliacionDisponible) · {{ $pendientes->count() }} {{ $pendientes->count() === 1 ? 'pendiente de entrega' : 'pendientes de entrega' }} @endif
                </p>
                <div class="rm-staff-record__document-lists">
                    <section aria-label="Requisitos documentales del trabajador">
                        <h4 class="rm-staff-record__subheading">Requisitos del perfil</h4>
                        <ul class="rm-staff-record__list">
                            @foreach($fichaPersonal['checklist'] as $requisito)
                                <li class="rm-staff-record__file">
                                    <div>
                                        <strong>{{ $requisito['nombre'] }}</strong>
                                        <p class="rm-help">{{ $requisito['obligatorio'] ? 'Obligatorio' : 'Archivo complementario' }}</p>
                                        @if($requisito['documento']?->fecha_vencimiento)
                                            <p class="rm-help">Vencimiento {{ $requisito['documento']->fecha_vencimiento->format('d/m/Y') }}</p>
                                        @endif
                                    </div>
                                    @if($requisito['conflicto'])
                                        <x-ui.status-badge label="Revisar archivos vigentes" variant="warning" />
                                    @elseif(!$requisito['cargado'])
                                        <x-ui.status-badge label="Sin entregar" variant="warning" />
                                    @else
                                        <x-ui.status-badge :estado="$requisito['estado']" />
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </section>
                    <section aria-label="Solicitudes pendientes y archivos registrados">
                        <div>
                            @if($pendientes->isNotEmpty())
                                <h4 class="rm-staff-record__subheading">Solicitudes con plazo</h4>
                                <ul class="rm-staff-record__list" aria-label="Pendientes de entrega">
                                    @foreach($pendientes->take(4) as $documento)
                                        <li class="rm-staff-record__pending">
                                            <i class="ph-bold ph-clock" aria-hidden="true"></i>
                                            <div>
                                                <strong>{{ $documento['nombre'] }}</strong>
                                                <p>{{ $documento['estado'] }}</p>
                                                @if($documento['limite'])<p class="rm-staff-record__deadline">Plazo {{ $documento['limite'] }}</p>@endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                @if($pendientes->count() > 4)
                                    <p class="rm-help">Mostrando 4 de {{ $pendientes->count() }} solicitudes pendientes.</p>
                                @endif
                            @endif
                            @if($archivos->isNotEmpty())
                                <h4 class="rm-staff-record__subheading">Archivos del expediente</h4>
                                <ul class="rm-staff-record__list" aria-label="Archivos registrados">
                                    @foreach($archivos->take(3) as $archivo)
                                        <li class="rm-staff-record__file">
                                            <span><i class="ph ph-file-text" aria-hidden="true"></i> {{ $archivo->nombre }}</span>
                                            <x-ui.status-badge :estado="$archivo->estado" />
                                        </li>
                                    @endforeach
                                </ul>
                                @if($archivos->count() > 3)
                                    <p class="rm-help">Mostrando 3 de {{ $archivos->count() }} archivos.</p>
                                @endif
                            @endif
                            @if($archivos->isEmpty() && $pendientes->isEmpty())
                                <p class="rm-help">El expediente todavía no tiene archivos ni solicitudes con plazo.</p>
                            @endif
                        </div>
                    </section>
                </div>
                <a class="rm-btn rm-btn-secondary rm-staff-record__action" href="{{ $fichaPersonal['urlDocumentacion'] }}"><i class="ph-bold ph-folder-open" aria-hidden="true"></i> Abrir documentación laboral</a>
            </x-ui.section-card>

            @if($fichaPersonal['movimientos']->isNotEmpty())
                <x-ui.section-card title="Últimos cambios laborales" icon="ph-clock-counter-clockwise" class="rm-staff-record__documents">
                    <ol class="rm-staff-record__list">
                        @foreach($fichaPersonal['movimientos'] as $movimiento)
                            <li class="rm-staff-record__file">
                                <div>
                                    <strong>{{ $movimiento->fecha_hora_efecto->format('d/m/Y H:i') }}</strong>
                                    <p>{{ $movimiento->motivo }}</p>
                                </div>
                                <span><x-ui.status-badge :estado="$movimiento->estado_anterior" /> → <x-ui.status-badge :estado="$movimiento->estado_nuevo" /></span>
                            </li>
                        @endforeach
                    </ol>
                </x-ui.section-card>
            @endif
        </div>
    </div>

    <x-slot:footer>
        <x-ui.action-button variant="secondary" class="min-h-[44px]" wire:click="cerrarFichaPersonal">Cerrar ficha</x-ui.action-button>
    </x-slot:footer>
</x-ui.modal-livewire>
