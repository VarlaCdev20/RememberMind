<x-ui.resident-summary-drawer model="mostrarPanelDetalle" title="Resumen del residente" subtitle="Resumen de enfermería" close-method="cerrarPanelDetalle">
    @if($detalleResidente)
        @if($drawerPaso === 'resident-summary')
        @include('livewire.cuidados.partials.resident-summary')
        @endif
    @endif
    <x-slot:footer>
        @if($detalleResidente)
            @if($drawerPaso === 'resident-summary')
                <div class="rm-resident-directory__footer-actions">
                    @if($cuidadoActual && isset($cuidadoActual['form']) && $this->puedeRegistrar() && auth()->user()->can($cuidadoActual['create']))
                        <button id="resident-register-trigger" type="button" class="rm-btn-primary" wire:click="abrirCuidadoSeleccionado">Registrar {{ mb_strtolower($cuidadoActual['label']) }}</button>
                    @elseif($cuidadoActual && ($destinoCuidado = $this->destinoCuidadoSeleccionado()))
                        <a class="rm-btn-primary" href="{{ $destinoCuidado }}">Abrir {{ mb_strtolower($cuidadoActual['label']) }}</a>
                    @elseif(! $cuidadoActual && $this->puedeRegistrar())
                        <button id="resident-register-trigger" type="button" class="rm-btn-primary" wire:click="mostrarSelectorRegistro">+ Registrar</button>
                    @endif
                    @can('enfermeria.ver_ficha_paciente')
                        <a class="rm-btn-secondary" href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $detalleResidente['cod_residente']]) }}">Ver ficha clínica <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>
                    @endcan
                    @if($puedeConsultarExperto)
                        <a class="rm-btn rm-btn-secondary min-h-11 whitespace-normal" href="{{ route('admin.enfermeria.pacientes.resultados-experto', ['residente' => $detalleResidente['cod_residente']]) }}"><i class="ph ph-brain" aria-hidden="true"></i> Sistema experto</a>
                    @endif
                </div>
            @endif
        @endif
    </x-slot:footer>
</x-ui.resident-summary-drawer>
