<table class="rm-data-table rm-table w-full text-left text-sm{{ ($interactivo ?? false) ? ' rm-nursing-report__table' : '' }}">
    <caption class="sr-only">Registros del período seleccionado</caption>
    <thead><tr><th scope="col">Fecha y hora</th><th scope="col">Residente</th><th scope="col">Registro</th><th scope="col">Estado / resultado</th><th scope="col">Detalle</th><th scope="col">Profesional / responsable</th></tr></thead>
    <tbody>
    @forelse($filas as $fila)
        <tr><td data-label="Fecha y hora">{{ $fila['fecha']->format($fila['categoria'] === 'SUENO' ? 'd/m/Y' : 'd/m/Y H:i') }}<br><small>{{ $fila['fecha_tipo'] }}</small></td><td data-label="Residente">{{ $fila['residente'] }}</td><td data-label="Registro">{{ $fila['tipo'] }}</td><td data-label="Estado / resultado">{{ $fila['estado'] }}</td><td data-label="Detalle">{{ $fila['detalle'] }}</td><td data-label="Profesional / responsable">{{ $fila['profesional'] }}</td></tr>
    @empty
        <tr><td colspan="6">No hay registros para los filtros aplicados.</td></tr>
    @endforelse
    </tbody>
</table>
