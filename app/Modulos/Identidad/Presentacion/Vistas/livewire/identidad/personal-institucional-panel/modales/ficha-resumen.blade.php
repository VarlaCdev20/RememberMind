@php
    $titular = $fichaPersonal['usuario'];
    $personal = $titular->personal;
    $horario = $fichaPersonal['horario'];
    $archivos = $fichaPersonal['archivos'];
    $pendientes = $fichaPersonal['pendientes'];
    $secciones = [
        'Identidad y contacto' => [
            'Correo' => $titular->correo,
            'Celular' => $personal?->telefono,
            'Dirección' => $personal?->direccion,
            'Fecha de nacimiento' => $personal?->fecha_nacimiento?->format('d/m/Y'),
            'Género' => match ($personal?->genero) {'F' => 'Femenino', 'M' => 'Masculino', default => $personal?->genero},
            'Número de documento' => $personal?->numero_documento,
            'Tipo de documento' => $personal?->tipo_documento,
            'País emisor' => $personal?->pais_documento,
            'Expedido en' => $personal?->expedicion_documento,
        ],
        'Información laboral' => [
            'Profesión o función' => $personal?->profesion,
            'Fecha de ingreso' => $personal?->fecha_ingreso?->format('d/m/Y'),
            'Alta de la cuenta' => $titular->fecha_hora_alta?->format('d/m/Y H:i'),
            'Matrícula profesional' => $personal?->matricula_profesional,
            'Observaciones' => $personal?->observacion,
        ],
    ];
@endphp

<x-ui.modal-livewire id="gerencia-personal-resumen" wire:model="modalFichaAbierto"
    title="Ficha del trabajador" icon="ph-identification-card" max-width="xl"
    close-method="cerrarFichaPersonal" :show-validation="false">
    <header class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <x-ui.resident-card-identity :name="$titular->name" :code="$titular->getKey()" :photo="$titular->foto" :secondary="$titular->roles->pluck('name')->join(' · ')" mode="row" />
        <div class="flex flex-wrap gap-3">
            <div class="space-y-1"><p class="rm-help">Estado laboral</p><x-ui.status-badge :estado="$personal?->estado ?? ''" :label="$personal ? null : 'Sin ficha laboral'" /></div>
            <div class="space-y-1"><p class="rm-help">Acceso a la cuenta</p><x-ui.status-badge :estado="$titular->estado" /></div>
        </div>
    </header>

    <div class="grid min-w-0 gap-3 md:grid-cols-2">
        @foreach($secciones as $titulo => $datos)
            <x-ui.section-card :title="$titulo">
                <dl class="grid grid-cols-[minmax(0,2fr)_minmax(0,3fr)] gap-x-3 gap-y-2 text-sm">
                    @foreach($datos as $etiqueta => $valor)
                        <dt class="rm-help">{{ $etiqueta }}</dt>
                        <dd class="min-w-0 break-words font-semibold">{{ filled($valor) ? $valor : 'Sin registrar' }}</dd>
                    @endforeach
                </dl>
            </x-ui.section-card>
        @endforeach

        @can('asignaciones_personal.ver')
            <x-ui.section-card title="Horarios" icon="ph-calendar-blank">
                @if($horario)
                    <p class="rm-help">Última asignación activa</p>
                    <p class="break-words font-semibold">{{ $horario->area?->nombre ?? 'Área sin registrar' }} · {{ $horario->jornada?->turno?->nombre ?? 'Turno sin registrar' }}</p>
                    <p class="rm-help">{{ $horario->jornada?->fecha_jornada?->format('d/m/Y') }}</p>
                @else
                    <p class="rm-help">Sin asignaciones activas.</p>
                @endif
                <x-ui.action-button variant="ghost" wire:click="abrirHorariosDesdeFicha" icono="ph-arrow-right">Consultar horarios</x-ui.action-button>
            </x-ui.section-card>
        @endcan

        <x-ui.section-card title="Documentación" icon="ph-folder-open">
            <p class="rm-help">
                {{ $archivos->count() }} {{ $archivos->count() === 1 ? 'archivo registrado' : 'archivos registrados' }}
                @if($ampliacionDisponible) · {{ $pendientes->count() }} {{ $pendientes->count() === 1 ? 'pendiente de entrega' : 'pendientes de entrega' }} @endif
            </p>
            @if($pendientes->isNotEmpty())
                <ul class="mt-2 space-y-2">
                    @foreach($pendientes->take(4) as $documento)
                        <li class="break-words text-sm"><strong>{{ $documento['nombre'] }}</strong><br><span class="rm-help">{{ $documento['estado'] }} @if($documento['limite']) · Plazo {{ $documento['limite'] }} @endif</span></li>
                    @endforeach
                </ul>
            @endif
            @if($archivos->isNotEmpty())
                <ul class="mt-2 space-y-2">
                    @foreach($archivos->take(3) as $archivo)
                        <li class="flex flex-wrap items-center justify-between gap-2 text-sm">
                            <span class="min-w-0 break-words">{{ $archivo->nombre }}</span><x-ui.status-badge :estado="$archivo->estado" />
                        </li>
                    @endforeach
                </ul>
            @endif
            <a class="rm-btn rm-btn-secondary mt-3" href="{{ route('admin.personal.documentacion') }}">Abrir documentación laboral</a>
        </x-ui.section-card>
    </div>

    <x-slot:footer>
        <x-ui.action-button variant="secondary" wire:click="cerrarFichaPersonal">Cerrar ficha</x-ui.action-button>
    </x-slot:footer>
</x-ui.modal-livewire>
