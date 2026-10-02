<div>
    @if($alertas->isEmpty())
        <x-ui.empty-state icono="ph-bell-slash" titulo="No hay alertas abiertas" texto="Las alertas que requieran seguimiento aparecerán aquí hasta su resolución." />
    @else
        <div class="rm-table-container rm-table-scroll">
            <table class="rm-table">
                <thead><tr><th scope="col">Residente</th><th scope="col">Alerta</th><th scope="col">Prioridad</th><th scope="col">Estado</th><th scope="col">Registrada</th></tr></thead>
                <tbody>
                    @foreach($alertas as $alerta)
                        <tr>
                            <td>{{ $alerta->residente ? trim($alerta->residente->nombres.' '.$alerta->residente->apellido_paterno) : $alerta->cod_residente }}</td>
                            <td><strong>{{ $alerta->titulo }}</strong><small class="rm-cell-meta">{{ $alerta->descripcion }}</small></td>
                            <td>{{ $alerta->prioridad ?: 'Sin prioridad registrada' }}</td>
                            <td><x-ui.status-badge :estado="$alerta->estado" /></td>
                            <td>{{ $alerta->fecha_hora?->format('d/m/Y H:i') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
