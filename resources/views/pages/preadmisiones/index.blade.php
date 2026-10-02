<x-sistema-layout>
    <div class="rm-page-layout">
        <x-ui.page-header titulo="Preadmisiones" subtitulo="Revisión de solicitudes antes de la admisión formal." :breadcrumb="[['label' => 'Inicio', 'url' => route('dashboard')], ['label' => 'Preadmisiones']]" />
        <form method="GET" action="{{ route('admin.preadmisiones.index') }}" class="rm-card rm-card-soft rm-card-body rm-search-form">
            <div class="rm-field">
                <label class="rm-label" for="estado-preadmision">Estado de la solicitud</label>
                <select id="estado-preadmision" name="estado" class="rm-select">
                    <option value="">Todos los estados</option>
                    @foreach(['PENDIENTE', 'APROBADA', 'RECHAZADA', 'ADMITIDA'] as $estado)
                        <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rm-btn rm-btn-primary">Filtrar</button>
            @if(request()->filled('estado'))<a class="rm-btn rm-btn-ghost" href="{{ route('admin.preadmisiones.index') }}">Limpiar</a>@endif
        </form>
        <section class="rm-section" aria-labelledby="lista-preadmisiones-titulo">
            <div class="rm-section-heading"><h2 id="lista-preadmisiones-titulo">Solicitudes</h2></div>
            @if($preadmisiones->isEmpty())
                <x-ui.empty-state icono="ph-clipboard-text" :titulo="request()->filled('estado') ? 'No hay solicitudes con ese estado' : 'Todavía no hay preadmisiones'" :texto="request()->filled('estado') ? 'Prueba con otro estado o limpia el filtro.' : 'Las solicitudes registradas aparecerán aquí para su revisión.'" />
            @else
                <div class="rm-table-container rm-table-scroll">
                    <table class="rm-table">
                        <thead><tr><th scope="col">Postulante</th><th scope="col">Fecha de solicitud</th><th scope="col">Prioridad registrada</th><th scope="col">Estado</th></tr></thead>
                        <tbody>
                            @foreach($preadmisiones as $preadmision)
                                <tr><td><strong>{{ trim($preadmision->nombres.' '.$preadmision->apellido_paterno.' '.$preadmision->apellido_materno) }}</strong></td><td>{{ $preadmision->fecha_solicitud?->format('d/m/Y H:i') ?? '—' }}</td><td>{{ $preadmision->prioridad ?: '—' }}</td><td><x-ui.status-badge :estado="$preadmision->estado" /></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $preadmisiones->links() }}
            @endif
        </section>
    </div>
</x-sistema-layout>
