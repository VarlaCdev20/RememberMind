@if($modalResultadoCierre && $resultadoCierre !== [])
    <x-ui.modal-livewire id="modalResultadoCierreAlerta" wire:model="modalResultadoCierre" maxWidth="md" closeMethod="cerrarResultadoCierre">
        <x-slot name="title">Resultado del cierre</x-slot>

        <x-ui.resultado-operacion-clinica
            variant="success"
            :resident="$resultadoCierre['residente']"
            title="Alerta cerrada"
            :message="'La alerta de '.$resultadoCierre['residente'].' fue cerrada. El historial de atención permanece registrado.'"
            :date-time="$resultadoCierre['fecha_hora']"
        />

        <x-slot name="footer">
            <x-ui.action-button variant="ghost" size="sm" wire:click="cerrarResultadoCierre">Volver a alertas</x-ui.action-button>
            @can('enfermeria.ver_ficha_paciente')
                <a class="rm-btn rm-btn-sm rm-btn-primary" href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $resultadoCierre['cod_residente']]) }}">Volver al residente</a>
            @endcan
        </x-slot>
    </x-ui.modal-livewire>
@endif
