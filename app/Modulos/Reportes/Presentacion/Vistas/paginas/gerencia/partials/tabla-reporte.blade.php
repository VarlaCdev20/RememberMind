<div class="rm-table-scroll">
    <table class="rm-table">
        <caption class="sr-only">Indicadores gerenciales agregados de la consulta</caption>
        <thead><tr><th scope="col">Indicador</th><th scope="col">Valor</th><th scope="col">Unidad</th><th scope="col">Referencia</th></tr></thead>
        <tbody>@foreach($reporte['filas'] as $fila)<tr><th scope="row">{{ $fila['indicador'] }}</th><td>{{ $fila['valor'] ?? 'No disponible' }}</td><td>{{ $fila['unidad'] }}</td><td>{{ $fila['referencia'] }}@if($fila['valor'] === null)<small class="block">{{ $fila['calidad'] }}</small>@endif</td></tr>@endforeach</tbody>
    </table>
</div>
