<div class="rm-management-panels">
    @if(in_array($reporte['seccion'], ['resumen', 'ocupacion'], true))
        <x-ui.dashboard-data-panel :panel="['title'=>'Situación de las camas', 'type'=>'bars', 'items'=>$reporte['camas_serie'], 'empty'=>$reporte['camas'] === null ? 'Fuente no autorizada.' : 'No hay camas registradas.', 'icon'=>'ph-bed', 'subtitle'=>'Camas registradas · categorías excluyentes', 'period'=>'Actual']" />
    @endif
    @if(in_array($reporte['seccion'], ['resumen', 'alertas'], true))
        <x-ui.dashboard-data-panel :panel="['title'=>'Avisos documentales por situación', 'type'=>'bars', 'items'=>$reporte['avisos_serie'], 'empty'=>$reporte['avisos_documentales'] === null ? 'Consulta no disponible.' : 'No hay avisos documentales en tu ámbito.', 'icon'=>'ph-bell', 'subtitle'=>'Un aviso por solicitud y trabajador', 'period'=>'Actual']" />
    @endif
</div>
