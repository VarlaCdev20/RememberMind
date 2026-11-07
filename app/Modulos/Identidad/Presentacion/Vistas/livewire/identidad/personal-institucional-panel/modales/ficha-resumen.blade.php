@php
    $titular = $fichaPersonal['usuario'];
    $personal = $titular->personal;
    $horario = $fichaPersonal['horario'];
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
            'Género' => match ($personal?->genero) {'F' => 'Femenino', 'M' => 'Masculino', default => $personal?->genero},
            'País emisor' => $personal?->pais_documento,
            'Expedido en' => $personal?->expedicion_documento,
        ]],
        'laboral' => ['titulo' => 'Información laboral', 'icono' => 'ph-briefcase', 'datos' => [
            'Profesión o función' => $personal?->profesion,
            'Fecha de ingreso' => $personal?->fecha_ingreso?->format('d/m/Y'),
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
                    @if($clave === 'laboral')
                        @can('asignaciones_personal.ver')
                            <div class="rm-staff-record__schedule">
                                <x-ui.section-header title="Horarios" icon="ph-calendar-blank" :level="4" />
                                @if($horario)
                                    <p class="rm-help">Última asignación activa</p>
                                    <p class="font-semibold">{{ $horario->area?->nombre ?? 'Área sin registrar' }} · {{ $horario->jornada?->turno?->nombre ?? 'Turno sin registrar' }}</p>
                                    <p class="rm-help">{{ $horario->jornada?->fecha_jornada?->format('d/m/Y') }}</p>
                                @else
                                    <p class="rm-help">Sin asignaciones activas.</p>
                                @endif
                                <x-ui.action-button variant="secondary" class="rm-staff-record__action" wire:click="abrirHorariosDesdeFicha" icono="ph-arrow-right">Consultar horarios</x-ui.action-button>
                            </div>
                        @endcan
                    @endif
                </x-ui.section-card>
            @endforeach

            <x-ui.section-card title="Documentación" icon="ph-folder-open" class="rm-staff-record__documents">
                <p class="rm-staff-record__counts">
                    {{ $archivos->count() }} {{ $archivos->count() === 1 ? 'archivo registrado' : 'archivos registrados' }}
                    @if($ampliacionDisponible) · {{ $pendientes->count() }} {{ $pendientes->count() === 1 ? 'pendiente de entrega' : 'pendientes de entrega' }} @endif
                </p>
                <div @class(['rm-staff-record__document-lists' => $pendientes->isNotEmpty() && $archivos->isNotEmpty()])>
                    @if($pendientes->isNotEmpty())
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
                    @endif
                    @if($archivos->isNotEmpty())
                        <ul class="rm-staff-record__list" aria-label="Archivos registrados">
                            @foreach($archivos->take(3) as $archivo)
                                <li class="rm-staff-record__file">
                                    <span><i class="ph ph-file-text" aria-hidden="true"></i> {{ $archivo->nombre }}</span>
                                    <x-ui.status-badge :estado="$archivo->estado" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <a class="rm-btn rm-btn-secondary rm-staff-record__action" href="{{ route('admin.personal.documentacion') }}"><i class="ph-bold ph-folder-open" aria-hidden="true"></i> Abrir documentación laboral</a>
            </x-ui.section-card>
        </div>
    </div>

    <x-slot:footer>
        <x-ui.action-button variant="secondary" class="min-h-[44px]" wire:click="cerrarFichaPersonal">Cerrar ficha</x-ui.action-button>
    </x-slot:footer>
</x-ui.modal-livewire>
