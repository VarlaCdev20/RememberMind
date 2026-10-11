@php
    // Una respuesta antigua con BOM quedó cacheada bajo el id del bundle de Livewire.
    // Cambiar la URL fuerza a descargar el bundle íntegro en todos los layouts.
    $scriptRoute = app(\Livewire\Mechanisms\FrontendAssets\FrontendAssets::class)->javaScriptRoute;
    $scriptUrl = url($scriptRoute->uri).'?rm_ui=20261003';
@endphp
@livewireScripts(['url' => $scriptUrl])
