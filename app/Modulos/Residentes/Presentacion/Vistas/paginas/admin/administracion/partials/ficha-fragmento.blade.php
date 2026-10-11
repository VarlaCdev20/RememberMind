@inject('visibilidadNavegacion', 'App\Modulos\Identidad\Presentacion\Navegacion\VisibilidadNavegacion')
@php
    $ruta = 'admin.administracion.'.$modulo;
    $parametros = array_filter(array_replace($filtros, ['vista' => $vista, 'por_pagina' => $porPagina]), fn($valor,$clave) => filled($valor) && $clave !== 'detalle', ARRAY_FILTER_USE_BOTH);
@endphp
@include('pages.admin.administracion.partials.detalle-operativo')
