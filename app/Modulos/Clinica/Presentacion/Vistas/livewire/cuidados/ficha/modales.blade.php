{{-- Ventanas de la ficha; se conserva el ámbito y el estado del componente padre. --}}
@if(!$consultaEnfermeria)
    @include('clinica::livewire.cuidados.ficha.modales.selector-registro')
    @include('clinica::livewire.cuidados.ficha.modales.signos')
    @include('clinica::livewire.cuidados.ficha.modales.medicacion')
    @include('clinica::livewire.cuidados.ficha.modales.tarea')
    @include('clinica::livewire.cuidados.ficha.modales.seguimiento')
    @include('clinica::livewire.cuidados.ficha.modales.incidente')
    @include('clinica::livewire.cuidados.ficha.modales.atender-alerta')
    @include('clinica::livewire.cuidados.ficha.modales.cerrar-alerta')
@endif
@include('clinica::livewire.cuidados.ficha.modales.exportar')
@include('clinica::livewire.cuidados.ficha.modales.expediente')
@include('clinica::livewire.cuidados.ficha.modales.familia')
