<div class="rm-management-panels">
    @if(in_array($reporte['seccion'], ['resumen', 'ocupacion'], true))
        <x-ui.dashboard-data-panel :panel="['title'=>'Situación de las camas', 'type'=>'bars', 'items'=>$reporte['camas_serie'], 'empty'=>$reporte['camas'] === null ? 'Fuente no autorizada.' : 'No hay camas registradas.', 'icon'=>'ph-bed', 'subtitle'=>'Camas registradas · categorías excluyentes', 'period'=>'Actual']" />
    @endif
    @if(in_array($reporte['seccion'], ['resumen', 'alertas'], true))
        <x-ui.dashboard-data-panel :panel="['title'=>'Alertas activas por situación', 'type'=>'bars', 'items'=>$reporte['alertas_serie'], 'empty'=>$reporte['alertas'] === null ? 'Fuente no autorizada.' : 'No hay alertas activas registradas.', 'icon'=>'ph-bell', 'subtitle'=>'Alertas distintas, no cantidad de residentes', 'period'=>'Actual']" />
    @endif
</div>
