<?php
// Solo lo carga el servidor de pruebas desechable; no es una ruta de la aplicación.
config(['cache.default'=>'array']);
\Illuminate\Support\Facades\Http::preventStrayRequests();
\Illuminate\Support\Facades\Http::fake([
    'nominatim.openstreetmap.org/search*'=>\Illuminate\Support\Facades\Http::response([['lat'=>'-16.5','lon'=>'-68.1']]),
    'nominatim.openstreetmap.org/reverse*'=>\Illuminate\Support\Facades\Http::response(['address'=>[
        'country_code'=>'bo','country'=>'Bolivia','state'=>'La Paz','city'=>'La Paz','suburb'=>'BARRIO DE PRUEBA','road'=>'CALLE SINTÉTICA','house_number'=>'120',
    ]]),
]);
