<div class="rm-management-workspace space-y-4">
    @if(!$mostrarFormulario)@error('estado')<p class="rm-alert rm-alert-danger" role="alert">{{ $message }}</p>@enderror@endif
    @include('institucion::livewire.identidad.partials.areas-coleccion', ['modoEquipo'=>true])
    @if($mostrarFicha && $areaActual)
    @include('institucion::livewire.gerencia.partials.area-detalle')
    @endif
    @include('institucion::livewire.identidad.partials.areas-formulario', ['modoEquipo' => true])
</div>
