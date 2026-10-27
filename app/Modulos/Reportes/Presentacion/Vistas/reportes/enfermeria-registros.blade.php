<table class="rm-data-table rm-table w-full text-left text-sm">
    <caption class="sr-only">Registros del período seleccionado</caption>
    <thead><tr><th scope="col">Fecha y hora</th><th scope="col">Residente</th><th scope="col">Registro</th><th scope="col">Estado / resultado</th><th scope="col">Detalle</th><th scope="col">Profesional / responsable</th></tr></thead>
    <tbody>
    @forelse($filas as $fila)
        <tr><td>{{ $fila['fecha']->format('d/m/Y H:i') }}<br><small>{{ $fila['fecha_tipo'] }}</small></td><td>{{ $fila['residente'] }}</td><td>{{ $fila['tipo'] }}</td><td>{{ $fila['estado'] }}</td><td>{{ $fila['detalle'] }}</td><td>{{ $fila['profesional'] }}</td></tr>
    @empty
        <tr><td colspan="6">No hay registros para los filtros aplicados.</td></tr>
    @endforelse
    </tbody>
</table>
